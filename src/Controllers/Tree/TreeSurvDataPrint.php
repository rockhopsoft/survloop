<?php
/**
  * TreeSurvDataPrint is a mid-level class using a standard branching tree, which provides
  * functions to print data from the database into a survey or page.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.13
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use Illuminate\Http\Request;
use App\Models\SLNodeResponses;
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvFormElements;

class TreeSurvDataPrint extends TreeSurvFormElements
{

    protected function nodePrintData(&$curr)
    {
        if (in_array($curr->nodeType, 
            ['Data Print', 'Data Print Row'])) {
            return $this->nodePrintDataRow($curr);

        } elseif (in_array($curr->nodeType, 
            ['Data Print Block', 'Data Print Columns'])) {
            return $this->nodePrintDataBlock($curr);

        } elseif (in_array($curr->nodeType, 
            ['Print Vert Progress'])) {
            return $this->nodePrintVertProgress($curr);

        }
        return false;
    }

    protected function nodePrintDataRow(&$curr)
    {
        if ($this->checkFldDataPerms($curr->getFldRow()) 
            && $this->checkViewDataPerms($curr->getFldRow())) {
            if ($this->shouldPrintDataRow($curr)) {
                list($deetLabel, $deetVal) = $this->nodePrintDataRowDeets($curr);
                $deetLabel = $this->customLabels($curr, $deetLabel);
                if ($curr->nodeType == 'Data Print') {
                    return [
                        '<div id="nLabel' . $curr->nIDtxt 
                            . '" class="nPrompt"><span class="slGrey mR10">' 
                            . $deetLabel . '</span>' . $deetVal . '</div>'
                    ];
                } elseif ($curr->nodeType == 'Data Print Row') {
                    return [ $deetLabel, $deetVal, $curr->nID ];
                }
            } elseif ($curr->nodeType == 'Data Print Row') {
                return [];
            }
        }
        return [];
    }

    protected function nodePrintDataRowDeets($curr)
    {
        $this->nodePrintDataRowCheckboxes($curr);
        $fldRow = $GLOBALS["SL"]->getFldRowFromFullName($curr->tbl, $curr->fld);

        $deetLabel = (($fldRow && isset($fldRow->fld_eng)) ? $fldRow->fld_eng : '');
        $deetLabel = $this->swapLabels($curr, $deetLabel);
        
        $deetVal = $GLOBALS["SL"]->printResponse(
            $curr->tbl, 
            $curr->fld, 
            $curr->sessData, 
            $fldRow
        );
        $deetVal = $this->printValCustom($curr->nID, $deetVal, $fldRow);
        if (isset($GLOBALS["SL"]->formTree->tree_id)) {
            $tID = $GLOBALS["SL"]->formTree->tree_id;
            $lab = $GLOBALS["SL"]->getFldNodeQuestion($curr->tbl, $curr->fld, $tID);
            if (trim($lab) != '') {
                $lab = $this->swapLabels($curr, $lab);
                if (strip_tags($deetLabel) != strip_tags($lab)
                    && !$GLOBALS["SL"]->isPrintView()) {
                    $deetLabel = '<a id="hidivBtn' . $curr->nIDtxt 
                        .'" class="hidivBtn slGrey" href="javascript:;">' 
                        . $deetLabel . '</a><div id="hidiv' . $curr->nIDtxt 
                        . '" class="disNon">"' . $lab . '"</div>';
                }
            }
        }
        return [ $deetLabel, $deetVal ];
    }

    protected function nodePrintDataRowCheckboxes($curr)
    {
        if (isset($GLOBALS["SL"]->formTree->tree_id) 
            && in_array($curr->nID, $this->checkboxNodes)) {
            $sessData = $this->sessData->currSessDataCheckbox($curr);
            $curr->sessData = $this->valListArr($sessData);

            /* // this was breaking checkboxes reported within a loop
            if ($this->hasCycleAncestorActive($curr->nID)) {
                $cycInd = $GLOBALS["SL"]->currCyc["cyc"][1];
                $cycInd = intVal(str_replace('cyc', '', $cycInd));
                if (isset($curr->sessData[$cycInd])) {
                    $curr->sessData = $curr->sessData[$cycInd];
                }
            }
            */
        }
        return true;
    }

    protected function shouldPrintDataRow($curr)
    {
        $printRow = true;
        if ((!is_array($curr->sessData) 
                && (!$curr->sessData || trim($curr->sessData) == ''))
            || (is_array($curr->sessData) && sizeof($curr->sessData) == 0)) {
            $printRow = false;
        } else {
            $isDefaultNonArray = (!is_array($curr->sessData) 
                && trim($curr->sessData) != '' 
                && trim($curr->sessData) == trim($curr->nodeRow->node_default));
            $isDefaultArray = (is_array($curr->sessData) 
                && trim($curr->sessData[0]) == trim($curr->nodeRow->node_default));
            if (isset($curr->nodeRow->node_default) 
                && trim($curr->nodeRow->node_default) != '' 
                && ($isDefaultNonArray || $isDefaultArray)) {
                $printRow = false;
            }
        }
        return $printRow;
    }

    protected function nodePrintDataBlock($curr)
    {
        $ret = '';
        $deets = $this->nodePrintDataBlockLoadDeets($curr);
        if (sizeof($deets) > 0) {
            $prompt = $curr->nodeRow->node_prompt_text;
            $prompt = strip_tags($this->swapLabels($curr, $prompt));
            if ($curr->nodeType == 'Data Print Block') {
                $ret .= $this->printReportDeetsBlock($deets, $prompt, $curr);
            } else {
                $colCnt = 2;
                $ret .= $this->printReportDeetsBlockCols($deets, $prompt, $colCnt, $curr);
            }
        }
        return $ret;
    }

    protected function nodePrintDataBlockLoadDeets($curr)
    {
        $deets = [];
        if (sizeof($curr->tmpSubTier[1]) > 0) {
            foreach ($curr->tmpSubTier[1] as $child) {
                $kidID = $child[0];
                if ($this->allNodes[$kidID]->nodeType == 'Data Print Row') {
                    $cust = $this->nodePrintDataBlockLoadDeetsCustom($kidID);
                    if (sizeof($cust) > 0 && sizeof($cust[0]) > 0) {
                        foreach ($cust as $custRow) {
                            if (is_array($custRow) && sizeof($custRow) > 0) {
                                $deets[] = $custRow;
                            }
                        }
                    } else { // default processing
                        $newDeet = $this->printNodePublic($kidID, $child, $curr->currVisib);
                        if (is_array($newDeet) 
                            && sizeof($newDeet) > 0 
                            && trim($newDeet[0]) != '' 
                            && trim($newDeet[1]) != '') {
                            $deets[] = $newDeet;
                        }
                    }
                } elseif ($this->allNodes[$kidID]->isDataManip()) {
                    $this->loadManipBranch($kidID, ($curr->currVisib == 1));
                    foreach ($child[1] as $gNode) {
                        if ($this->allNodes[$gNode[0]]->nodeType == 'Data Print Row') {
                            $cust = $this->nodePrintDataBlockLoadDeetsCustom($gNode[0]);
                            if (sizeof($cust) > 0 && sizeof($cust[0]) > 0) {
                                foreach ($cust as $custRow) {
                                    if (is_array($custRow) && sizeof($custRow) > 0) {
                                        $deets[] = $custRow;
                                    }
                                }
                            } else { // default processing
                                $deet = $this->printNodePublic(
                                    $gNode[0], 
                                    $gNode, 
                                    $curr->currVisib
                                );
                                if ($deet && is_array($deet) && sizeof($deet) > 0) {
                                    $deets[] = $deet;
                                }
                            }
                        }
                    }
                    $this->closeManipBranch($kidID);
                } elseif ($this->allNodes[$kidID]->isLoopCycle()) {
                    foreach ($this->nodePrintDataBlockLoopCycle($curr, $kidID, $child) 
                            as $deet) {
                        $deets[] = $deet;
                    }
                }
            }
        }
        return $deets;
    }

    protected function nodePrintDataBlockLoadDeetsCustom($nID)
    {
        return [];
    }

    protected function nodePrintDataBlockLoopCycle($curr, $kidID, $child)
    {
        $deets = [];
        list($curr->tbl, $curr->fld, $newVal) = $this->nodeBranchInfo(
            $curr->nID, 
            $this->allNodes[$kidID]
        );
        $loop = str_replace('LoopItems::', '', $this->allNodes[$kidID]->responseSet);
        $loopCycle = $this->sessData->getLoopRows($loop);
        // if this is a simple loop of a just a table's rows
        if (trim($loop) == '' 
            && isset($this->allNodes[$kidID]->dataBranch)
            && trim($this->allNodes[$kidID]->dataBranch) != '') {
            $s = sizeof($this->sessData->dataBranches);
            if ($s > 0) {
                $branch = $this->sessData->dataBranches[($s-1)];
                if (isset($branch["itemID"]) && intVal($branch["itemID"]) > 0) {
                    $loopCycle = $this->sessData->getChildRows(
                        $branch["branch"], 
                        $branch["itemID"], 
                        $this->allNodes[$kidID]->dataBranch
                    );
                }
            }
        }
        if (sizeof($child[1]) > 0 && $loopCycle && sizeof($loopCycle) > 0) {
            $GLOBALS["SL"]->currCyc["cyc"][0] = $curr->tbl;
            if ($loop != '') {
                $GLOBALS["SL"]->currCyc["cyc"][0] = $GLOBALS["SL"]->getLoopTable($loop);
            }
            foreach ($loopCycle as $i => $loopItem) {
                $GLOBALS["SL"]->currCyc["cyc"][1] = 'cyc' . $i;
                $GLOBALS["SL"]->currCyc["cyc"][2] = $loopItem->getKey();
                $this->sessData->startTmpDataBranch($curr->tbl, $loopItem->getKey());
                $label = (1+$i) . ')';
                if ($loop != '') {
                    $GLOBALS["SL"]->fakeSessLoopCycle($loop, $loopItem->getKey());
                    $label = $this->getLoopItemLabel($loop, $loopItem, $i);
                }
                foreach ($child[1] as $gNode) {
                    if ($this->allNodes[$gNode[0]]->nodeType == 'Data Print Row') {
                        $deet = $this->printNodePublic($gNode[0], $gNode, $curr->currVisib);
                        if (isset($deet[0]) 
                            && trim($deet[0]) != '' 
                            && trim($deet[1]) != '') {
                            $deet[0] = $label . ' ' . $deet[0];
                            $deets[] = $deet;
                        }
                    }
                }
                if ($loop != '') {
                    $GLOBALS["SL"]->removeFakeSessLoopCycle($loop, $loopItem->getKey());
                }
                $this->sessData->endTmpDataBranch($curr->tbl);
                $GLOBALS["SL"]->currCyc["cyc"][1] = '';
                $GLOBALS["SL"]->currCyc["cyc"][2] = -3;
            }
            $GLOBALS["SL"]->currCyc["cyc"][0] = '';
        }
        return $deets;
    }

    protected function nodePrintVertProgress($curr)
    {
        $deets = [];
        foreach ($curr->tmpSubTier[1] as $cNode) {
            $childNode = $this->allNodes[$cNode[0]];
            $childNode->nID = $cNode[0];
            if ($childNode->nodeType == 'Data Print Row') {
                list($tbl, $fld) = $childNode->getTblFld();
                $val = $this->printNodeSessDataOverride($childNode);
                if (!$val 
                    || (is_array($val) 
                        && (sizeof($val) == 0 
                            || (sizeof($val) == 1 && $val[0] == '')))) {
                    list($itemInd, $itemID) = $this->sessData->currSessDataPos($tbl);
                    if ($itemID > 0 && isset($this->sessData->dataSets[$tbl])) {
                        $tblSet = $this->sessData->dataSets[$tbl]; 
                        if (isset($tblSet[$itemInd]) > 0) {
                            $dateTime = 0;
                            if (isset($tblSet[$itemInd]->{ $fld })
                                && trim($tblSet[$itemInd]->{ $fld }) != '') {
                                $val = [$tblSet[$itemInd]->{ $fld }];
                            }
                        }
                    }
                }
                $deet = $this->customNodePrintVertProgress($childNode, $val);
                if ($deet !== null) {
                    if (sizeof($deet) != 1 || $deet[0] != 'skip row') {
                        $deets[] = $deet;
                    }
                } elseif ($val 
                    && is_array($val) 
                    && trim($val[0]) != '') { // && trim($val[0]) != '0000-00-00 00:00:00'
                    $dateTime = strtotime($val[0]);
                    if (!isset(
                        $childNode->nodeRow->node_default)
                        || trim($childNode->nodeRow->node_default) != trim($dateTime)) {
                        $fldRow = $GLOBALS["SL"]->getTblFldRow('', $tbl, $fld);
                        if ($fldRow && isset($fldRow->fld_eng)) {
                            $deets[] = [ $fldRow->fld_eng, $dateTime, $cNode[0] ];
                        }
                    }
                }
            }
        }
        $text = strip_tags($curr->nodePromptText);
        return $this->printReportDeetsVertProg($deets, $text, $curr);
    }

    /**
     * Overrides primary Survloop printing of individual nodes from 
     * surveys and site pages. This is one of the main routing hubs
     * for OpenPolice.org customizations beyond Survloop defaults.
     *
     * @param  TreeNodeSurv $curr
     * @param  string $var
     * @return array
     */
    protected function customNodePrintVertProgress(&$curr = null, $val = null)
    {
        return null;
    }


}
