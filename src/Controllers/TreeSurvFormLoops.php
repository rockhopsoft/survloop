<?php
/**
  * TreeSurvFormLoops is a mid-level class with functions related to printing the loops within forms.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use SurvLoop\Controllers\TreeSurvFormVarieties;

class TreeSurvFormLoops extends TreeSurvFormVarieties
{
    protected function getTableRecLabel($tbl, $rec = [], $ind = -3)
    {
        $name = $this->getTableRecLabelCustom($tbl, $rec, $ind);
        if (trim($name) != '') return $name;
        if (file_exists(base_path('resources/views/vendor/' . strtolower($GLOBALS["SL"]->sysOpts["cust-abbr"]) 
            . '/nodes/tbl-rec-label-' . strtolower($tbl) . '.blade.php'))) {
            $name = trim(view('vendor.' . strtolower($GLOBALS["SL"]->sysOpts["cust-abbr"]) . '.nodes.tbl-rec-label-' 
                . strtolower($tbl), [ "rec" => $rec ])->render());
        } else {
            $name = $GLOBALS["SL"]->tblEng[$GLOBALS["SL"]->tblI[$tbl]] . (($ind >= 0) ? ' #' . (1+$ind) : '');
        }
        return $name;
    }
    
    protected function getTableRecLabelCustom($tbl, $rec = [], $ind = -3)
    {
        return '';
    }
    
    protected function getLoopItemLabel($loop, $itemRow = [], $itemInd = -3)
    {
        $name = $this->getLoopItemLabelCustom($loop, $itemRow, $itemInd);
        if (trim($name) != '') {
            return $name;
        }
        $name = $this->getTableRecLabelCustom($loop, $itemRow, $itemInd);
        if (trim($name) != '') {
            return $name;
        }
        if (isset($GLOBALS["SL"]->dataLoops[$loop])) {
            if ($itemInd < 0) {
                $itemRow = $this->sessData->getDataBranchRow($GLOBALS["SL"]->dataLoops[$loop]->DataLoopTable);
                $itemInd = $this->sessData->getLoopIndFromID($loop, $itemRow->getKey());
            }
            if ($itemInd >= 0) {
                return $GLOBALS["SL"]->dataLoops[$loop]->DataLoopSingular . ' #' . (1+$itemInd);
            }
        }
        return '';
    }
    
    protected function getLoopItemLabelCustom($loop, $itemRow = [], $itemInd = -3)
    {
        return '';
    }
    
    protected function getLoopItemCntLabelCustom($loop, $itemInd = -3)
    {
        return -3;
    }
    
    protected function printSetLoopNav($nID, $loopName)
    {
        $this->settingTheLoop($loopName);
        if ($this->allNodes[$nID]->isStepLoop()) {
            $this->sessData->getLoopDoneItems($loopName);
            if ($this->sessData->loopItemsNextID > 0) {
                $this->loopItemsCustBtn = '<a href="javascript:;" class="fR btn btn-lg btn-primary" '
                    . 'id="nFormNextStepItem"><i class="fa fa-arrow-circle-o-right"></i> Next ' 
                    . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular . ' Details</a>';
                $GLOBALS["SL"]->pageJAVA .= 'loopItemsNextID = ' . $this->sessData->loopItemsNextID . '; ';
            }
        }
        
        $labelFirstLet = substr(strtolower($GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular), 0, 1);
        $limitTxt = '';
        if ($GLOBALS["SL"]->closestLoop["obj"]->DataLoopMaxLimit > 0 
            && isset($this->sessData->loopItemIDs[$loopName])
            && sizeof($this->sessData->loopItemIDs[$loopName]) 
                > $GLOBALS["SL"]->closestLoop["obj"]->DataLoopWarnLimit) {
            $limitTxt .= '<div class="gry6 pT20 fPerc133">Limit of ' 
                . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopMaxLimit . ' '
                . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopPlural . '</div>';
        }
        $ret = '<div class="nPrompt"><input type="hidden" id="isLoopNav" name="loopNavRoot" value="'
            . intVal($GLOBALS['SL']->closestLoop['obj']->DataLoopRoot) . '">' 
            . (($this->allNodes[$nID]->isStepLoop()) ? '<div id="isStepLoop"></div>' : '');
        if (!$this->allNodes[$nID]->isStepLoop() && empty($this->sessData->loopItemIDs[$loopName])) {
            $ret .= '<div class="mB20"><h4>No ' . strtolower($GLOBALS["SL"]->closestLoop["obj"]->DataLoopPlural) 
                . ' added yet.</h4></div>';
        } else {
            $ret .= '<div class="p10"></div>';
        }
        if (sizeof($this->sessData->loopItemIDs[$loopName]) > 0) {
            if (!$this->allNodes[$nID]->isStepLoop() && sizeof($this->sessData->loopItemIDs[$loopName]) > 10) {
                $ret .= '<div class="mTn10 mB20">' . $this->printSetLoopNavAddBtn($nID, $loopName, $labelFirstLet) 
                    . '</div>';
            }
            foreach ($this->sessData->loopItemIDs[$loopName] as $setIndex => $loopItem) {
                $tbl = $GLOBALS["SL"]->dataLoops[$loopName]->DataLoopTable;
                $ret .= $this->printSetLoopNavRow($nID, $this->sessData->getRowById($tbl, $loopItem), $setIndex);
            }
        }
        if (!$this->allNodes[$nID]->isStepLoop()) {
            $ret .= $this->printSetLoopNavAddBtn($nID, $loopName, $labelFirstLet)
                . $limitTxt . '<div class="p20"></div>' . "\n";
            $GLOBALS["SL"]->pageJAVA .= 'currItemCnt = ' . sizeof($this->sessData->loopItemIDs[$loopName]) . '; '
                . 'maxItemCnt = ' . $GLOBALS['SL']->closestLoop["obj"]->DataLoopMaxLimit . '; ';
        }
        /* if (!$this->allNodes[$nID]->isStepLoop()) {
            $this->nextBtnOverride = 'Done Adding ' . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopPlural;
        } elseif (sizeof($this->sessData->loopItemIDs[$loopName]) == sizeof($this->sessData->loopItemIDsDone)) {
            $this->nextBtnOverride = 'Done With ' . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopPlural;
        } */
        $ret .= '</div>';
        return $ret;
    }
    
    protected function printSetLoopNavAddBtn($nID, $loopName, $labelFirstLet)
    {
        return '<button type="button" id="nFormAdd" class="btn btn-lg btn-secondary disBlo mT20'
            . (($GLOBALS["SL"]->closestLoop["obj"]->DataLoopMaxLimit == 0 || 
                sizeof($this->sessData->loopItemIDs[$loopName]) < $GLOBALS["SL"]->closestLoop["obj"]->DataLoopMaxLimit) 
                ? 'disBlo' : 'disNon')
            . '"><i class="fa fa-plus-circle"></i> Add ' . ((empty($this->sessData->loopItemIDs[$loopName])) 
                ? 'a'.((in_array($labelFirstLet, array('a', 'e', 'i', 'o', 'u'))) ? 'n' : '') : 'another') . ' ' 
            . strtolower($GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular) . '</button>' ;
    }
    
    protected function printSetLoopNavRowCustom($nID, $loopItem, $setIndex)
    {
        return '';
    }
    
    protected function printSetLoopNavRow($nID, $loopItem, $setIndex)
    {
        $ret = $this->printSetLoopNavRowCustom($nID, $loopItem, $setIndex);
        if ($ret != '') {
            return $ret;
        }
        $canEdit = true;
        $itemLabel = $this->getLoopItemLabel($GLOBALS["SL"]->closestLoop["loop"], $loopItem, $setIndex);
        if (strtolower(strip_tags($itemLabel)) == 'you') {
            //$itemLabel = 'You (' . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular . ' #' . (1+$setIndex) . ')';
            $canEdit = false;
        } /* elseif ($itemLabel != $GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular . ' #' . (1+$setIndex)) {
            $itemLabel = $itemLabel . ' (' . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular 
                . ' #' . (1+$setIndex) . ')';
        } */
        $ico = '';
        if ($this->allNodes[$nID]->isStepLoop()) {
            if ($this->sessData->loopItemsNextID > 0 && $this->sessData->loopItemsNextID == $loopItem->getKey()) {
                $ico = '<i class="fa fa-arrow-circle-o-right"></i>';
            } elseif (in_array($loopItem->getKey(), $this->sessData->loopItemIDsDone)) {
                $ico = '<i class="fa fa-check"></i>';
            } else {
                $ico = '<i class="fa fa-check gryA opac10"></i>';
            }
        }
        return view('vendor.survloop.formtree-looproot-row', [
            "nID"            => $nID,
            "setIndex"       => $setIndex,
            "itemID"         => $loopItem->getKey(),
            "itemLabel"      => $itemLabel,
            "canEdit"        => $canEdit,
            "ico"            => $ico, 
            "node"           => $this->allNodes[$nID]
            ])->render();
    }
        
}