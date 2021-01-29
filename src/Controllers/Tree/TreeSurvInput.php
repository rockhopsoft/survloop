<?php
/**
  * TreeSurvInput is a mid-level class using a standard branching tree, mostly for 
  * processing the input Survloop's surveys and pages.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use Storage;
use Illuminate\Http\Request;
use App\Models\SLNode;
use App\Models\SLContact;
use App\Models\SLTokens;
use App\Models\SLUsersRoles;
use RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvInputElements;

class TreeSurvInput extends TreeSurvInputElements
{
    /**
     * Default behavior for submitting survey forms,
     * delegateing specifc saving procedures for tree nodes.
     *
     * @param  int $nID
     * @param  array $tmpSubTier
     * @return boolean
     */
    protected function postNodePublic($nID = -3, $tmpSubTier = [])
    {
        $ret = '';
        if (!$this->checkNodeConditions($nID)) {
            return '';
        }
        if (empty($tmpSubTier)) {
            $tmpSubTier = $this->loadNodeSubTier($nID);
        }
        $this->allNodes[$nID]->fillNodeRow($nID);
        $curr = $this->allNodes[$nID];
        // Copy commonly needed variables into current Node object
        $curr->nID        = $nID;
        $curr->nSffx      = $nSffx = $GLOBALS["SL"]->getCycSffx();
        $curr->nIDtxt     = $nIDtxt = trim($nID . $nSffx);
        $curr->tmpSubTier = $tmpSubTier;
        $curr->getTblFld();
        
        $this->openPostNodePublic($curr);
        if ($curr->isLayout()) {
            return $this->postNodePublicLayoutKids($curr);
        }
        if ($this->chkKidMapTrue($nID) == -1) {
            $this->closePostNodePublic($curr);
            return '';
        }
        $this->postNodePublicInit($curr);
        $ret .= $this->processSpecialPageForms($curr);
        if ($curr->isLoopSort()) { // actual storage happens with with each change /loopSort/
            return $this->postNodePublicLoopSort($curr);
        }
        if ($this->allNodes[$nID]->isPage() 
            || $this->allNodes[$nID]->isLoopRoot()) {
            $this->checkLoopRootInput($nID);
        }
        if ($curr->isDataManip()) {
            $this->loadManipBranch($nID, $curr->currVisib);
        }
        if ($curr->isLoopCycle()) {
            $ret .= $this->postNodePublicLoopCycle($curr);
        } elseif ($curr->isSpreadTbl()) {
            $ret .= $this->postNodePublicSpreadTbl($curr);
        } elseif (!$curr->isDataPrint()) {
            if (!$this->postNodePublicCustom($curr)) { 
                // then run standard post, move all this code in here:
                $this->postNodePublicStandards($curr);
            }
            if (sizeof($tmpSubTier[1]) > 0) {
                foreach ($tmpSubTier[1] as $childNode) {
                    if (!$this->allNodes[$childNode[0]]->isPage() 
                        && $this->allNodes[$childNode[0]]->nodeType != 'Layout Sub-Response') {
                        $ret .= $this->postNodePublic($childNode[0], $childNode);
                    }
                }
            }
        }
        if ($curr->isDataManip()) {
            $this->closeManipBranch($nID);
        }
        $this->closePostNodePublic($curr);
        return $ret;
    }

    protected function postNodePublicLayoutKids($curr)
    {
        $ret = '';
        if (sizeof($curr->tmpSubTier[1]) > 0) {
            foreach ($curr->tmpSubTier[1] as $child) {
                if (!$this->allNodes[$child[0]]->isPage()) {
                    $ret .= $this->postNodePublic($child[0], $child);
                }
            }
        }
        $this->closePostNodePublic($curr);
        return $ret;
    }

    protected function postNodePublicInit($curr)
    {
        $curr->hasParManip = $this->hasParentDataManip($curr->nID);
        $curr->currVisib = ($GLOBALS["SL"]->REQ->has('n' . $curr->nIDtxt . 'Visible') 
            && intVal($GLOBALS["SL"]->REQ->{ 'n' . $curr->nIDtxt . 'Visible' }) == 1);
        if (isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl])) {
            $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->update([
                "updated_at" => date("Y-m-d H:i:s")
            ]);
        }
        return true;
    }
    
    protected function openPostNodePublic(&$curr = [])
    {
        return true;
    }
    
    protected function closePostNodePublic(&$curr = [])
    {
        return true;
    }
    
    protected function postNodePublicStandards(&$curr)
    {
        $ret = '';
        if ($GLOBALS["SL"]->REQ->has('loop')
            && trim($GLOBALS["SL"]->REQ->has('loop')) != '') {
            $this->settingTheLoop(
                trim($GLOBALS["SL"]->REQ->input('loop')), 
                intVal($GLOBALS["SL"]->REQ->loopItem)
            );
        }
        if ($curr->nodeType == 'Uploads') {
            if ($this->REQstep != 'autoSave') {
                $ret .= $this->postUploadTool($curr->nID);
                $GLOBALS["SL"]->x["reloadSurvPage"] = 'upPrev' . $curr->nIDtxt;
                //$GLOBALS["SL"]->pageJAVA .= 'addHshoo("#upPrev' . $nIDtxt . '"); ';
                //$GLOBALS["SL"]->pageAJAX .= view('vendor.survloop.forms.upload-slide-to-previous-ajax', [
                //    "nIDtxt" => $nIDtxt
                //    ])->render();
            }
        } elseif ($curr->isDataManip()) {
            $param = 'dataManip' . $curr->nID;
            if ($GLOBALS["SL"]->REQ->has($param) 
                && intVal($GLOBALS["SL"]->REQ->input($param)) == 1) {
                if ($curr->currVisib) {
                    $this->runDataManip($curr->nID);
                } else {
                    $this->reverseDataManip($curr->nID);
                }
            }
        } elseif (strpos($curr->dataStore, ':') !== false) {
            $ret .= $this->postNodePublicDataStore($curr);
        }
        return $ret;
    }

    protected function postNodePublicDataStore(&$curr)
    {
        $ret = '';
        list($curr->itemInd, $curr->itemID) = $this->sessData->currSessDataPos($curr->tbl);
        $this->v["fldForeignTbl"] = $GLOBALS["SL"]->fldForeignKeyTbl(
            $curr->tbl, 
            $curr->fld
        );
        if (!$curr->isInstruct() && $curr->tbl != '' && $curr->fld != '') {
            $newVal = $this->getNodeFormFldBasic($curr->nID, $curr);
            if ($curr->nodeType == 'Checkbox' || $curr->isDropdownTagger()) {
                $this->postNodePublicCheckbox($curr, $newVal);
            } else {
                if ($curr->nodeType == 'Date' && trim($newVal) == '') {
                    // Redundancy in case JS breaks
                    $newVal = $this->getRawFormDate($curr->nIDtxt);
                }
                $this->sessData->currSessData($curr, 'update', $newVal);
            }
            $ret .= $this->postNodePublicCheckboxSubRes($curr, $newVal);
            $this->postNodePublicUpdateKidMap($curr, $newVal);
            $this->postNodePublicFldOther($curr);
        }
        return $ret;
    }

    protected function postNodePublicCheckbox(&$curr, &$newVal)
    {
        $newVal = $this->postNodeTweakNewVal($curr, $newVal);
        if (sizeof($curr->responses) == 1) { 
            // && !$GLOBALS["SL"]->isFldCheckboxHelper($fld)
            if (is_array($newVal) && sizeof($newVal) == 1) {
                $this->sessData->currSessData($curr, 'update', $newVal[0]);
            } else {
                $tmpVal = '';
                $fldRow = $GLOBALS["SL"]->getFldRowFromFullName($curr->tbl, $curr->fld);
                if (isset($fldRow->fld_default) && trim($fldRow->fld_default) != '') {
                    $tmpVal = $fldRow->fld_default;
                }
                $this->sessData->currSessData($curr, 'update', $tmpVal);
            }
        } else {
            $this->checkResponses($curr, $this->v["fldForeignTbl"]);
            $this->sessData->currSessDataCheckbox($curr, 'update', $newVal);
        }
        return true;
    }

    protected function postNodePublicCheckboxSubRes($curr, $newVal)
    {
        $ret = '';
        // Check for Layout Sub-Response between each Checkbox Response
        if ($curr->nodeType == 'Checkbox' 
            && sizeof($curr->tmpSubTier[1]) > 0 
            && sizeof($newVal) > 0) {
            foreach ($newVal as $r => $val) {
                foreach ($curr->tmpSubTier[1] as $child) {
                    if ($this->allNodes[$child[0]]->nodeType == 'Layout Sub-Response' 
                        && sizeof($child[1]) > 0) {
                        foreach ($curr->responses as $j => $res) {
                            if ($res->node_res_value == $val) {
                                $subRowIDs = $this->sessData->getRowIDsByFldVal(
                                    $curr->tbl, 
                                    [ $curr->fld => $res->node_res_value ]
                                );
                                if (sizeof($subRowIDs) > 0) {
                                    $GLOBALS["SL"]->currCyc["res"][0] = $curr->tbl;
                                    $GLOBALS["SL"]->currCyc["res"][1] = 'res' . $j;
                                    $GLOBALS["SL"]->currCyc["res"][2] = $res->node_res_value;
                                    $this->sessData->startTmpDataBranch($curr->tbl, $subRowIDs[0]);
                                    foreach ($child[1] as $k => $granNode) {
                                        $ret .= $this->postNodePublic($granNode[0], $granNode);
                                    }
                                    $this->sessData->endTmpDataBranch($curr->tbl);
                                    $GLOBALS["SL"]->currCyc["res"] = [ '', '', -3 ];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $ret;
    }

    protected function postNodePublicUpdateKidMap($curr, $newVal)
    {
        if (in_array($curr->nodeType, ['Checkbox', 'Radio']) 
            && $curr->hasShowKids 
            && isset($this->kidMaps[$curr->nID]) 
            && sizeof($this->kidMaps[$curr->nID]) > 0) {
            foreach ($this->kidMaps[$curr->nID] as $nKid => $ress) {
                $found = false;
                if (sizeof($ress) > 0) {
                    foreach ($ress as $cnt => $res) {
                        $this->kidMaps[$curr->nID][$nKid][$cnt][2] = false;
                        if ($curr->nodeType == 'Checkbox' 
                            || $curr->isDropdownTagger()) {
                            if (in_array($res[1], $newVal)) {
                                $this->kidMaps[$curr->nID][$nKid][$cnt][2] = true;
                            }
                        } else {
                            if ($res[1] == $newVal) {
                                $this->kidMaps[$curr->nID][$nKid][$cnt][2] = true;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    protected function postNodePublicFldOther(&$curr)
    {
        $curr->chkFldOther();
        if ((in_array($curr->nodeType, ['Checkbox', 'Radio']) 
                && sizeof($curr->fldHasOther) > 0)
            || in_array($curr->nodeType, ['Gender', 'Gender Not Sure'])) {
            foreach ($curr->responses as $j => $res) {
                if (in_array($j, $curr->fldHasOther)) {
                    $inFld = 'n' . $curr->nIDtxt . 'fldOther' . $j;
                    $otherVal = '';
                    if ($GLOBALS["SL"]->REQ->has($inFld)) {
                        $otherVal = $GLOBALS["SL"]->REQ->get($inFld);
                    }
                    $fldVals = [ $curr->fld => $res->node_res_value ];
                    $s = sizeof($this->sessData->dataBranches);
                    if ($s > 0 
                        && intVal($this->sessData->dataBranches[$s-1]["itemID"]) > 0) {
                        $tbl2 = $this->sessData->dataBranches[$s-1]["branch"];
                        $branchLnkFld = $GLOBALS["SL"]->getFornNameFldName(
                            $curr->tbl, 
                            $tbl2
                        );
                        if ($branchLnkFld != '') {
                            $fldVals[$branchLnkFld] = $this->sessData
                                ->dataBranches[$s-1]["itemID"];
                        }
                    }
                    $subRowIDs = $this->sessData->getRowIDsByFldVal($curr->tbl, $fldVals);
                    $branchRowID = ((sizeof($subRowIDs) > 0) ? $subRowIDs[0] : -3);
                    if ($branchRowID > 0) {
                        $GLOBALS["SL"]->currCyc["res"] = [
                            $curr->tbl, 
                            'res' . $j, 
                            $res->node_res_value
                        ];
                        $this->sessData->startTmpDataBranch($curr->tbl, $branchRowID);
                        $othNode = new TreeNodeSurv($curr->nID);
                        $othNode->nID = $curr->nID;
                        $othNode->fillNodeRow();
                        $othNode->tbl = $curr->tbl;
                        $othNode->fld = $curr->fld . '_other';
                        $othNode->hasParManip = $curr->hasParManip;
                        $this->sessData->currSessData($othNode, 'update', $otherVal);
                        $this->sessData->endTmpDataBranch($curr->tbl);
                        $GLOBALS["SL"]->currCyc["res"] = [ '', '', -3 ];
                    }
                }
            }
        }
        return true;
    }

    // Check for and process special page forms
    protected function processSpecialPageForms($curr)
    {
        $ret = '';
        if ($GLOBALS["SL"]->treeRow->tree_type == 'Page' 
            && $this->allNodes[$curr->nID]->nodeType == 'Page') {
            if ($GLOBALS["SL"]->treeRow->tree_opts%19 == 0) {
                $ret .= $this->processContactForm($curr->nID, $curr->tmpSubTier);
            }
        }
        return $ret;
    }

    
    protected function processPageForm($nID = -3, $tmpSubTier = [], $slTable = '', $dumpFld = '')
    {
        if (trim($slTable) == '') {
            return false;
        }
        eval("\$this->pageCoreRow = new App\\Models\\" . $slTable . ";");
        $extraData = $this->processPageFormInner($nID, $tmpSubTier);
        if (trim($extraData) != '' && trim($dumpFld) != '') {
            $this->pageCoreRow->{ $dumpFld } = $this->pageCoreRow->{ $dumpFld } . $extraData;
        }
        $this->pageCoreRow->save();
        return '';
    }
    
    protected function processPageFormInner($nID = -3, $tmpSubTier = [])
    {
        $extraData = '';
        $newVal = $this->getNodeFormFldBasic($nID);
        if ($newVal && !is_array($newVal) && trim($newVal) != '') {
            $found = false;
            if (isset($this->allNodes[$nID]->dataStore) 
                && trim($this->allNodes[$nID]->dataStore) != '') {
                $storeFld = trim($this->allNodes[$nID]->dataStore);
                if (strpos($storeFld, ':') !== false) {
                    $storeFld = substr($storeFld, strpos($storeFld, ':')+1);
                }
                if (sizeof($this->pageCoreFlds) > 0) {
                    foreach ($this->pageCoreFlds as $fld) {
                        if ($storeFld == $fld) {
                            $found = true;
                            $this->pageCoreRow->{ $fld } = $newVal;
                        }
                    }
                }
            }
            if (!$found) {
                $extraData .= '<p>' . $newVal . '</p>';
            }
        }
        if (sizeof($tmpSubTier[1]) > 0) {
            foreach ($tmpSubTier[1] as $childNode) {
                if (!$this->allNodes[$childNode[0]]->isPage()) {
                    $extraData .= $this->processPageFormInner($childNode[0], $childNode);
                }
            }
        }
        return $extraData;
    }


}