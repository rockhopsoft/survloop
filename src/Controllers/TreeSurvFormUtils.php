<?php
/**
  * TreeSurvFormUtils is a mid-level class using a standard branching tree, which provides
  * lots of smaller functions used by the form generation processes (in TreeSurvForm).
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use Illuminate\Http\Request;
use App\Models\SLNodeResponses;
use SurvLoop\Controllers\TreeSurvFormLoops;

class TreeSurvFormUtils extends TreeSurvFormLoops
{
    protected function customNodePrintWrap($nID, $bladeRender = '')
    {
        return $this->printNodePublicFormStart($nID) . $bladeRender . $this->nodePrintButton($nID) 
            . $this->printNodePublicFormEnd($nID) . '<div class="fC p20"></div>';
    }

    protected function customNodePrint($nID = -3, $tmpSubTier = [], $nIDtxt = '', $nSffx = '', $currVisib = 1)
    {
        return '';
    }
    
    protected function closePrintNodePublic($nID, $nIDtxt, $curr)
    {
        return true;
    }
    
    protected function printNodePublicFormStart($nID)
    {
        if ($this->skipFormForPreview($nID)) {
            return '';
        }
        $GLOBALS["SL"]->pageJAVA .= view('vendor.survloop.formtree-form-js', [
            "currPage"       => $this->v["currPage"],
            "pageJSvalid"    => $this->pageJSvalid,
            "pageFldList"    => $this->pageFldList
            ])->render();
        $GLOBALS["SL"]->pageAJAX .= view('vendor.survloop.formtree-form-ajax', [
            "hasFixedHeader" => $this->v["hasFixedHeader"]
            ])->render();
        $ret = '';
        $loopRootJustLeft = -3;
        if (isset($this->sessInfo->SessLoopRootJustLeft) && intVal($this->sessInfo->SessLoopRootJustLeft) > 0) {
            $loopRootJustLeft = $this->sessInfo->SessLoopRootJustLeft;
            $this->sessInfo->SessLoopRootJustLeft = -3;
            $this->sessInfo->save();
        }
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
            $ret .= '<div id="isPage"></div>';
        }
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Survey' || $GLOBALS["SL"]->chkCurrTreeOpt('PAGEFORM')
            || $GLOBALS["SL"]->chkCurrTreeOpt('CONTACT')) {
            $formAction = $this->currNodeFormAction();
            $GLOBALS["SL"]->pageJAVA .= 'formActionUrl = "' . $formAction . '"; ';
            $ret .= view('vendor.survloop.formtree-form-start', [
                "nID"              => $nID, 
                "nSlug"            => $this->allNodes[$nID]->nodeRow->NodePromptNotes, 
                "currPage"         => $this->v["currPage"],
                "action"           => $formAction, 
                "abTest"           => $formAction, 
                "isAjax"           => (($GLOBALS['SL']->treeRow->TreeType == 'Page') ? 0 : 1), 
                "pageHasUpload"    => ((sizeof($this->pageHasUpload) > 0) ? 'enctype="multipart/form-data"' : ''),
                "nodePrintJumpTo"  => $this->nodePrintJumpTo($nID), 
                "loopRootJustLeft" => $loopRootJustLeft, 
                "zoomPref"         => ((isset($this->sessInfo->SessZoomPref)) 
                    ? intVal($this->sessInfo->SessZoomPref) : 0)
                ])->render();
        }
        return $ret;
    }
    
    protected function printNodePublicFormEnd($nID, $promptNotesSpecial = '')
    {
        if ($this->skipFormForPreview($nID)) {
            return '';
        }
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Survey' || $GLOBALS["SL"]->treeRow->TreeOpts%19 == 0 
            || $GLOBALS["SL"]->treeRow->TreeOpts%53 == 0) {
            return '</form>';
        }
    }
    
    protected function nodePrintButton($nID = -3, $tmpSubTier = [], $promptNotesSpecial = '', $printBack = true)
    {
        $ret = $this->customNodePrintButton($nID, $promptNotesSpecial);
        if ($ret != '') {
            return $ret;
        }
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
            return '';
        }
        $btnSize = ((in_array($this->pageCnt, [1, 2])) ? 'btn-xl' : 'btn-lg');
        
        // else print standard button variations
        $ret .= '<div class="fC"></div><div id="nodeSubBtns" class="nodeSub">';
        if (isset($this->loopItemsCustBtn) && $this->loopItemsCustBtn != '') {
            $ret .= $this->loopItemsCustBtn;
        } elseif ($this->allNodes[$nID]->nodeType != 'Page' || $this->allNodes[$nID]->nodeOpts%29 > 0) {
            $nextLabel = 'Next';
            if ($this->nodePrintJumpTo($nID) > 0
                || ($this->allNodes[$nID]->nodeType == 'Instructions' && empty($tmpSubTier[1]))) {
                $nextLabel = 'OK';
            }
            if (trim($this->nextBtnOverride) != '') {
                $nextLabel = $this->nextBtnOverride;
            }
            $itemCnt = 0;
            if (isset($GLOBALS["SL"]->closestLoop["loop"]) 
                && isset($this->sessData->loopItemIDs[$GLOBALS["SL"]->closestLoop["loop"]])) {
                $itemCnt = sizeof($this->sessData->loopItemIDs[$GLOBALS["SL"]->closestLoop["loop"]]);
            }
            if ($this->allNodes[$nID]->isStepLoop() && $itemCnt != sizeof($this->sessData->loopItemIDsDone)) {
                $ret .= '<a href="javascript:;" class="fR btn btn-primary ' . $btnSize . ' slTab nFormNext" '
                    . 'id="nFormNextBtn" ' . $GLOBALS["SL"]->tabInd() . ' ><i class="fa fa-arrow-circle-o-right"></i> '
                    . $nextLabel . '</a>';
            } else {
                $ret .= '<a href="javascript:;" class="fR btn btn-primary ' . $btnSize . ' slTab nFormNext" '
                    . 'id="nFormNextBtn" ' . $GLOBALS["SL"]->tabInd() . ' >' . $nextLabel . '</a>';
                //$ret .= '<input type="button" value="' . $nextLabel 
                //    . '" class="fR btn btn-primary ' . $btnSize . ' nFormNext" id="nFormNextBtn">';
            }
        }
        if ($this->nodePrintJumpTo($nID) <= 0 && $printBack && $GLOBALS["SL"]->treeRow->TreeFirstPage != $nID
            && ($this->allNodes[$nID]->nodeType != 'Page' || $this->allNodes[$nID]->nodeOpts%29 > 0)) {
            $ret .= '<a href="javascript:;" class="fL btn btn-secondary ' . $btnSize 
                . ' slTab nFormBack" id="nFormBack" ' . $GLOBALS["SL"]->tabInd() . ' >Back</a>';
            //$ret .= '<input type="button" value="Back" class="fL nFormBack btn btn-lg btn-secondary" id="nFormBack">';
        }
        $ret .= '<div class="clearfix p5"></div></div><div class="disNon"><input type="submit"></div>';
        return $ret; 
    }
    
    protected function currNodeFormAction()
    {
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
            $ret = '/' . $GLOBALS["SL"]->treeRow->TreeSlug;
            if ($GLOBALS["SL"]->treeIsAdmin) {
                $ret = '/dash' . $ret;
            }
            if (isset($GLOBALS["SL"]->x["pageSlugSffx"])) {
                $ret .= $GLOBALS["SL"]->x["pageSlugSffx"];
            }
            return $ret;
        }
        return (($GLOBALS["SL"]->treeIsAdmin) ? '/dash-sub' : '/sub');
    }
    
    protected function getNodeCurrSessData($nID)
    {
        $this->allNodes[$nID]->fillNodeRow();
        list($tbl, $fld) = $this->allNodes[$nID]->getTblFld();
        return $this->sessData->currSessData($nID, $tbl, $fld);
    }
    
    protected function isPromptNotesSpecial($nodePromptNotes = '')
    {
        return (substr($nodePromptNotes, 0, 1) == '[' 
            && substr($nodePromptNotes, strlen($nodePromptNotes)-1) == ']');
    }
    
    protected function printSpecial($nID, $promptNotesSpecial = '', $currNodeSessData = '')
    {
        return '';
    }
    
    protected function customNodePrintButton($nID = -3, $nodeRow = [])
    {
        return '';
    }
    
    protected function customResponses($nID, $curr)
    {
        return $curr;
    }
    
    protected function skipFormForPreview($nID)
    {
        return ($GLOBALS["SL"]->REQ->has('isPreview') && $GLOBALS["SL"]->REQ->has('ajax'));
    }
    
    protected function loadAncestXtnd($nID)
    {
        if (isset($this->v["ancestors"]) && is_array($this->v["ancestors"]) && sizeof($this->v["ancestors"]) > 0) {
            for ($i = (sizeof($this->v["ancestors"])-1); $i >= 0; $i--) {
                $parent = $this->v["ancestors"][$i];
                if (isset($this->allNodes[$parent]) && $this->allNodes[$parent]->isDataManip()) {
                    $this->loadManipBranch($parent);
                }
            }
        }
        return true;
    }
    
    protected function shouldPrintHalfGap($curr)
    {
        return (($GLOBALS["SL"]->treeRow->TreeType != 'Page' || $GLOBALS["SL"]->treeRow->TreeOpts%19 == 0 
            || $GLOBALS["SL"]->treeRow->TreeOpts%53 == 0)
            && !$curr->isPage() && !$curr->isLoopRoot() && !$curr->isLoopCycle() && !$curr->isDataManip()
            && !$curr->isLayout() && trim($GLOBALS["SL"]->currCyc["res"][1]) == '' 
            && !$this->hasSpreadsheetParent($curr->nodeID));
    }
    
    protected function isCurrDataSelected($currNodeSessData, $value, $node)
    {
        $selected = false;
        $resValCyc = $value . trim($GLOBALS["SL"]->currCyc["cyc"][1]);
        $resValCyc2 = trim($GLOBALS["SL"]->currCyc["cyc"][1]) . $value;
        if (is_array($currNodeSessData)) {
            $selected = (in_array($value, $currNodeSessData) || in_array($resValCyc, $currNodeSessData) 
                || in_array($resValCyc2, $currNodeSessData));
        } else {
            if ($node->nodeType == 'Checkbox' || $node->isDropdownTagger()) {
                $selected = (strpos(';' . $currNodeSessData . ';', ';' . $value . ';') !== false 
                    || strpos(';' . $currNodeSessData . ';', ';' . $resValCyc . ';') !== false
                    || strpos(';' . $currNodeSessData . ';', ';' . $resValCyc2 . ';') !== false);
            } else {
                $selected = ($currNodeSessData == trim($value) || $currNodeSessData == trim($resValCyc) 
                    || $currNodeSessData == trim($resValCyc2));
            }
        }
        return $selected;
    }
    
    public function sortableStart($nID)
    {
        return '';
    }
    
    public function sortableEnd($nID)
    {
        return '';
    }
    
    public function getSetFlds(Request $request, $rSet = '')
    {
        $this->survLoopInit($request);
        if (trim($rSet) == '') {
            $rSet = $GLOBALS["SL"]->coreTbl;
        }
        $preSel = (($request->has('fld')) ? trim($request->get('fld')) : '');
        return $GLOBALS["SL"]->getAllSetTblFldDrops($rSet, $preSel);
    }
    
    public function loadTableDat($curr, $currNodeSessData = [], $tmpSubTier = [])
    {
        $this->tableDat = [
            "tbl"    => '', 
            "defSet" => '', 
            "loop"   => '', 
            "rowCol" => $curr->getTblFldName(), 
            "rows"   => [], 
            "cols"   => [], 
            "blnk"   => [],
            "maxRow" => 10, 
            "req"    => [ $curr->isRequired(), false, [] ]
            ];
        if (isset($curr->nodeRow->NodeDataBranch) && trim($curr->nodeRow->NodeDataBranch) != '') {
            $this->tableDat["tbl"] = $curr->nodeRow->NodeDataBranch;
        }
        $rowSet = $curr->parseResponseSet();
        if ($rowSet["type"] == 'Definition') { // lookup id based on rowCol and currNodeSessData
            $this->tableDat["defSet"] = $rowSet["set"];
            $defs = $GLOBALS["SL"]->def->getSet($rowSet["set"]);
            if (sizeof($defs) > 0) {
                foreach ($defs as $i => $def) {
                    $this->tableDat["rows"][] = $this->addTableDatRow(-3, $def->DefValue, $def->DefID);
                }
            }
        } elseif ($rowSet["type"] == 'LoopItems') {
            $this->tableDat["loop"] = $rowSet["set"];
            $loopCycle = $this->sessData->getLoopRows($rowSet["set"]);
            if (sizeof($loopCycle) > 0) {
                $this->tableDat["tbl"] = $GLOBALS["SL"]->getLoopTable($rowSet["set"]);
                foreach ($loopCycle as $i => $loopItem) {
                    $label = $this->getLoopItemLabel($rowSet["set"], $loopItem, $i);
                    $this->tableDat["rows"][] = $this->addTableDatRow($loopItem->getKey(), $label);
                }
            }
        } elseif ($rowSet["type"] == 'Table') {
            $this->tableDat["tbl"] = $rowSet["set"];
            if (isset($this->dataSets[$this->tableDat["tbl"]]) && sizeof($this->dataSets[$this->tableDat["tbl"]]) > 0) {
                foreach ($this->dataSets[$this->tableDat["tbl"]] as $i => $tblItem) {
                    $label = $this->getTableRecLabel($rowSet["set"], $tblItem, $i);
                    $this->tableDat["rows"][] = $this->addTableDatRow($tblItem->getKey(), $label);
                }
            }
        } else { // no set, type is to just let the user add rows of the table
            $rowIDs = $this->sessData->getBranchChildRows($this->tableDat["tbl"], true);
            if (sizeof($rowIDs) > 0) {
                foreach ($rowIDs as $rowID) {
                    $this->tableDat["rows"][] = $this->addTableDatRow($rowID);
                }
            }
        }
        if (sizeof($this->tableDat["rows"]) > 0) {
            foreach ($this->tableDat["rows"] as $i => $row) {
                if ($row["leftTxt"] == strtolower($row["leftTxt"])) {
                    $this->tableDat["rows"][$i]["leftTxt"] = ucwords($row["leftTxt"]);
                }
            }
        }
        $this->tableDat["maxRow"] = sizeof($this->tableDat["rows"]);
        if (isset($curr->nodeRow->NodeCharLimit) && intVal($curr->nodeRow->NodeCharLimit) > 0) {
            $this->tableDat["maxRow"] = $curr->nodeRow->NodeCharLimit;
        }
        if (sizeof($tmpSubTier) > 0) {
            foreach ($tmpSubTier[1] as $k => $kidNode) {
                $this->tableDat["cols"][]   = $this->allNodes[$kidNode[0]];
                $this->tableDat["req"][2][] = $this->allNodes[$kidNode[0]]->isRequired();
                if ($this->allNodes[$kidNode[0]]->isRequired()) {
                    $this->tableDat["req"][1] = true;
                }
            }
        }
        return $this->tableDat;
    }
    
    public function addTableDatRow($id = -3, $leftTxt = '', $leftVal = '', $cols = [])
    {
        return [
            "id"      => $id,      // unique row ID
            "leftTxt" => $leftTxt, // displayed in the left column of this row
            "leftVal" => $leftVal, // in addition to unique row ID
            "cols"    => $cols     // filled with nested field printings
            ];
    }
    
    protected function hasParentType($nID = -3, $type = '', $types = [])
    {
        if (isset($this->allNodes[$nID]) && $this->allNodes[$nID]->parentID > 0
            && isset($this->allNodes[$this->allNodes[$nID]->parentID])) {
            $p = $this->allNodes[$this->allNodes[$nID]->parentID];
            return (isset($p->nodeType) && (($type != '' && $p->nodeType == $type) 
                || (sizeof($types) > 0 && in_array($p->nodeType, $types))));
        }
        return false;
    }
    
    protected function hasCycleAncestor($nID = -3)
    {
        if (isset($this->allNodes[$nID]) && $this->allNodes[$nID]->parentID > 0) {
            if (isset($this->allNodes[$this->allNodes[$nID]->parentID])
                && $this->allNodes[$this->allNodes[$nID]->parentID]->isLoopCycle()) {
                return true;
            }
            return $this->hasCycleAncestor($this->allNodes[$nID]->parentID);
        }
        return false;
    }
    
    protected function hasCycleAncestorActive($nID = -3)
    {
        return ($this->hasCycleAncestor($nID) && trim($GLOBALS["SL"]->currCyc["cyc"][1]) != '');
    }
    
    protected function hasSpreadsheetParent($nID = -3)
    {
        if ($this->allNodes[$nID]->parentID > 0) {
            if (isset($this->allNodes[$this->allNodes[$nID]->parentID]) 
                && $this->allNodes[$this->allNodes[$nID]->parentID]->isSpreadTbl()) {
                return true;
            }
        }
        return false;
    }
    
    protected function hasSpreadsheetParentActive($nID = -3)
    {
        return ($this->hasSpreadsheetParent($nID) && trim($GLOBALS["SL"]->currCyc["tbl"][1]) != '');
    }
    
    protected function hasActiveParentCyc($nID = -3, $tbl = '')
    {
        return (($this->hasCycleAncestorActive($nID) && $tbl == $GLOBALS["SL"]->currCyc["cyc"][0])
            || ($this->hasSpreadsheetParentActive($nID) && $tbl == $GLOBALS["SL"]->currCyc["tbl"][0]));
    }
    
    protected function chkParentCycInds($nID = -3, $tbl = '')
    {
        $itemInd = $itemID = -3;
        if ($this->hasCycleAncestorActive($nID) && $tbl == $GLOBALS["SL"]->currCyc["cyc"][0]) {
            if (intVal($GLOBALS["SL"]->currCyc["cyc"][2]) > 0) {
                $itemID = $GLOBALS["SL"]->currCyc["cyc"][2];
                $itemInd = $this->sessData->getRowInd($tbl, $itemID);
            }
        } elseif ($this->hasSpreadsheetParentActive($nID) && $tbl == $GLOBALS["SL"]->currCyc["tbl"][0]) {
            if (intVal($GLOBALS["SL"]->currCyc["tbl"][2]) > 0) {
                $itemID = $GLOBALS["SL"]->currCyc["tbl"][2];
                $itemInd = $this->sessData->getRowInd($tbl, $itemID);
            }
        }
        return [ $itemInd, $itemID ];
    }
    
    public function loadTreeNodeStats()
    {
        $GLOBALS["SL"]->resetTreeNodeStats();
        $GLOBALS["SL"]->x["qTypeStats"]["nodes"]["tot"] = sizeof($this->allNodes);
        if (sizeof($this->allNodes) > 0) {
            $loops = [];
            foreach ($this->allNodes as $nID => $node) {
                $GLOBALS["SL"]->logTreeNodeStat($node);
                if (isset($node->nodeType) 
                    && in_array($node->nodeType, ['Loop Root', 'Loop Cycle', 'Spreadsheet Table'])) {
                    $loops[] = $node->nodeID;
                }
            }
            if (sizeof($loops) > 0) {
                $GLOBALS["SL"]->x["qTypeStats"]["nodes"]["loops"] = sizeof($loops);
                foreach ($loops as $nID) {
                    $this->loadTreeNodeStatsRecursLoop($this->loadNodeSubTier($nID));
                }
            }
        }
        return true;
    }
    
    public function loadTreeNodeStatsRecursLoop($tmpSubTier = [])
    {
        if (sizeof($tmpSubTier) > 1 && sizeof($tmpSubTier[1]) > 0) {
            foreach ($tmpSubTier[1] as $childNode) {
                if (isset($this->allNodes[$childNode[0]]) && isset($this->allNodes[$childNode[0]]->nodeType)) {
                    $curr = $this->allNodes[$childNode[0]];
                    if (in_array($curr->nodeType, $this->nodeTypes) && !in_array($curr->nodeType, ['Spreadsheet Table', 
                        'User Sign Up', 'Hidden Field', 'Spambot Honey Pot'])) {
                        $GLOBALS["SL"]->x["qTypeStats"]["nodes"]["loopNodes"]++;
                    }
                }
                $this->loadTreeNodeStatsRecursLoop($childNode);
            }
        }
        return true;
    }
        
    protected function checkResponses($curr, $fldForeignTbl)
    {
        if (isset($curr->responseSet) && strpos($curr->responseSet, 'LoopItems::') !== false) {
            $loop = str_replace('LoopItems::', '', $curr->responseSet);
            $currLoopItems = $this->sessData->getLoopRows($loop);
            if (sizeof($currLoopItems) > 0) {
                foreach ($currLoopItems as $i => $row) {
                    $curr->responses[$i] = new SLNodeResponses;
                    $curr->responses[$i]->NodeResValue = $row->getKey();
                    $curr->responses[$i]->NodeResEng = $this->getLoopItemLabel($loop, $row, $i);
                }
            }
        } elseif (isset($curr->responseSet) && strpos($curr->responseSet, 'Table::') !== false) {
            $tbl = str_replace('Table::', '', $curr->responseSet);
            if (isset($this->sessData->dataSets[$tbl]) && sizeof($this->sessData->dataSets[$tbl]) > 0) {
                foreach ($this->sessData->dataSets[$tbl] as $i => $row) {
                    $recName = $this->getTableRecLabel($tbl, $row, $i);
                    if (trim($recName) != '') {
                        $curr->responses[$i] = new SLNodeResponses;
                        $curr->responses[$i]->NodeResValue = $row->getKey();
                        $curr->responses[$i]->NodeResEng = $recName;
                    }
                }
            }
        } elseif (isset($curr->responseSet) && $curr->responseSet == 'Definition::--STATES--') {
            $GLOBALS["SL"]->loadStates();
            $curr->responses = $GLOBALS["SL"]->states->stateResponses();
        } elseif (empty($curr->responses) && trim($fldForeignTbl) != '' 
            && isset($this->sessData->dataSets[$fldForeignTbl]) 
            && sizeof($this->sessData->dataSets[$fldForeignTbl]) > 0) {
            foreach ($this->sessData->dataSets[$fldForeignTbl] as $i => $row) {
                $loop = ((isset($GLOBALS["SL"]->tblLoops[$fldForeignTbl])) 
                    ? $GLOBALS["SL"]->tblLoops[$fldForeignTbl] : $fldForeignTbl);
                // what about tables with multiple loops??
                $curr->responses[$i] = new SLNodeResponses;
                $curr->responses[$i]->NodeResValue = $row->getKey();
                $curr->responses[$i]->NodeResEng = $this->getLoopItemLabel($loop, $row, $i);
            }
        }
        return $curr;
    }
    
}