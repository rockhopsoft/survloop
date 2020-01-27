<?php
/**
  * TreeSurvFormLoops is a mid-level class with functions related to printing the loops within forms.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.1.2
  */
namespace SurvLoop\Controllers\Tree;

use SurvLoop\Controllers\Tree\TreeSurvFormVarieties;

class TreeSurvFormLoops extends TreeSurvFormVarieties
{
    protected function getTableRecLabel($tbl, $rec = [], $ind = -3)
    {
        $name = $this->getTableRecLabelCustom($tbl, $rec, $ind);
        if (trim($name) != '') {
            return $name;
        }
        $viewStr = 'vendor.' . strtolower($GLOBALS["SL"]->sysOpts["cust-abbr"])
            . '.nodes.tbl-rec-label-' . strtolower($tbl);
        $fileChk = 'resources/views/' . str_replace('.', '/', $viewStr) . '.blade.php';
        if (file_exists(base_path($fileChk))) {
            $name = trim(view($viewStr, [ "rec" => $rec ])->render());
        } else {
            $name = $GLOBALS["SL"]->tblEng[$GLOBALS["SL"]->tblI[$tbl]] 
                . (($ind >= 0) ? ' #' . (1+$ind) : '');
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
            $loopRow = $GLOBALS["SL"]->dataLoops[$loop];
            if ($itemInd < 0) {
                $itemRow = $this->sessData->getDataBranchRow($loopRow->data_loop_table);
                $itemInd = $this->sessData->getLoopIndFromID($loop, $itemRow->getKey());
            }
            if ($itemInd >= 0) {
                return $loopRow->data_loop_singular . ' #' . (1+$itemInd);
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
        if (!isset($GLOBALS["SL"]->closestLoop["obj"])
            || !isset($GLOBALS["SL"]->closestLoop["obj"]->data_loop_plural)) {
            return '';
        }
        $labelFirstLet = $limitTxt = '';
        $this->settingTheLoop($loopName);
        if ($this->allNodes[$nID]->isStepLoop()) {
            $this->sessData->getLoopDoneItems($loopName);
            if ($this->sessData->loopItemsNextID > 0) {
                $loopSing = $GLOBALS["SL"]->closestLoop["obj"]->data_loop_singular;
                $this->loopItemsCustBtn = '<a href="javascript:;" '
                    . 'class="fR btn btn-lg btn-primary" id="nFormNextStepItem">'
                    . '<i class="fa fa-arrow-circle-o-right"></i> Next ' 
                    . $loopSing . ' Details</a>';
                $GLOBALS["SL"]->pageJAVA .= 'loopItemsNextID = ' 
                    . $this->sessData->loopItemsNextID . '; ';
                $labelFirstLet = strtolower(substr($loopSing, 0, 1));
            }
        }
        $maxLimit = $currLoopSize = $addingLoopItem = 0;
        if (isset($GLOBALS["SL"]->closestLoop["obj"]->data_loop_max_limit)) {
            $maxLimit = intVal($GLOBALS["SL"]->closestLoop["obj"]->data_loop_max_limit);
        }
        if (isset($this->sessData->loopItemIDs[$loopName])) {
            $currLoopSize = sizeof($this->sessData->loopItemIDs[$loopName]);
        }
        if ($GLOBALS["SL"]->REQ->has('addLoopItem') 
            && intVal($GLOBALS["SL"]->REQ->addLoopItem) == 1) {
            // signal from previous form to start a new row in the current set
            $addingLoopItem = $this->newLoopItem($nID);
            //$this->updateCurrNode($this->nextNode($this->currNode()));
            $GLOBALS["SL"]->pageJAVA .= ' addingLoopItem = ' . $addingLoopItem . '; ';
        }
        if ($maxLimit > 0
            && $currLoopSize > $GLOBALS["SL"]->closestLoop["obj"]->data_loop_warn_limit) {
            $limitTxt .= '<div class="slGrey pT20">Limit of ' . $maxLimit . ' '
                . $GLOBALS["SL"]->closestLoop["obj"]->data_loop_plural . '</div>';
        }
        $ret = '';
        if ($addingLoopItem > 0) {
            $ret .= '<div class="w100 taC pB15 mB15">' 
                . $GLOBALS["SL"]->spinner() . '</div>';
        }
        $ret .= '<div id="loopNav' . $nID . '" class="nPrompt"'
            . (($addingLoopItem > 0) ? ' style="display: none;" ' : '') . '>'
            . '<input type="hidden" id="isLoopNav" name="loopNavRoot" value="' 
            . intVal($GLOBALS['SL']->closestLoop['obj']->data_loop_root) . '">';
        if ($this->allNodes[$nID]->isStepLoop()) {
            $ret .= '<div id="isStepLoop"></div>';
        }
        if (!$this->allNodes[$nID]->isStepLoop() && $currLoopSize == 0) {
            $ret .= '<div class="pT15 pB15"><b>No ' 
                . strtolower($GLOBALS["SL"]->closestLoop["obj"]->data_loop_plural) 
                . ' added yet.</b></div>';
        } else {
            $ret .= '<div class="p15"></div>';
        }
        if ($currLoopSize > 0) {
            if (!$this->allNodes[$nID]->isStepLoop() && $currLoopSize > 10) {
                $ret .= '<div class="mTn10 mB20">' 
                    . $this->printSetLoopNavAddBtn($nID, $loopName, $labelFirstLet) 
                    . '</div>';
            }
            foreach ($this->sessData->loopItemIDs[$loopName] as $setIndex => $loopItem) {
                $tbl = $GLOBALS["SL"]->dataLoops[$loopName]->data_loop_table;
                $loopRec = $this->sessData->getRowById($tbl, $loopItem);
                $ret .= $this->printSetLoopNavRow($nID, $loopRec, $setIndex);
            }
        }
        if (!$this->allNodes[$nID]->isStepLoop()) {
            if ($maxLimit <= 0 || $currLoopSize < $maxLimit) {
                $ret .= $this->printSetLoopNavAddBtn($nID, $loopName, $labelFirstLet)
                    . $limitTxt . '<div class="p20"></div>' . "\n";
            }
            $GLOBALS["SL"]->pageJAVA .= 'currItemCnt = ' . $currLoopSize 
                . '; maxItemCnt = ' . $maxLimit . '; ';
        }
        /* if (!$this->allNodes[$nID]->isStepLoop()) {
            $this->nextBtnOverride = 'Done Adding ' . $GLOBALS["SL"]->closestLoop["obj"]->data_loop_plural;
        } elseif (sizeof($this->sessData->loopItemIDs[$loopName]) == sizeof($this->sessData->loopItemIDsDone)) {
            $this->nextBtnOverride = 'Done With ' . $GLOBALS["SL"]->closestLoop["obj"]->data_loop_plural;
        } */
        $ret .= '</div> <!-- loopNav' . $nID . ' --> ';
        return $ret;
    }
    
    protected function printSetLoopNavAddBtn($nID, $loopName, $labelFirstLet)
    {
        $loopRec = $GLOBALS["SL"]->closestLoop["obj"];
        $vowels = [ 'a', 'e', 'i', 'o', 'u' ];
        $itemDesc = 'another';
        if (empty($this->sessData->loopItemIDs[$loopName])) {
            $itemDesc = 'a' . ((in_array($labelFirstLet, $vowels)) ? 'n' : '');
        }
        $itemDesc = ' ' . strtolower($loopRec->data_loop_singular);
        $dis = 'disNon';
        $cnt = sizeof($this->sessData->loopItemIDs[$loopName]);
        $max = $loopRec->data_loop_max_limit;
        if ($max == 0 || $cnt < $max) {
            $dis = 'disBlo';
        }
        if ($GLOBALS["SL"]->REQ->has('addLoopItem') 
            && intVal($GLOBALS["SL"]->REQ->addLoopItem) == 1) {
            return '<center><br />' . $GLOBALS["SL"]->spinner() . '<br /></center>';
        }
        $idHref = 'href="javascript:;" id="nFormAdd"';
        if (isset($GLOBALS["SL"]->closestLoop["obj"]->data_loop_auto_gen)
            && intVal($GLOBALS["SL"]->closestLoop["obj"]->data_loop_auto_gen) == 1) {
            $idHref = 'href="?addLoopItem=1" id="nFormLoopAdd"';
        }
        return '<a ' . $idHref . ' class="btn btn-lg btn-secondary mT15 ' . $dis 
            . '"><i class="fa fa-plus-circle"></i> Add ' . $itemDesc . '</a>' ;
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
        $loop = $GLOBALS["SL"]->closestLoop["loop"];
        $itemLabel = $this->getLoopItemLabel($loop, $loopItem, $setIndex);
        if (strtolower(strip_tags($itemLabel)) == 'you') {
            //$itemLabel = 'You (' . $GLOBALS["SL"]->closestLoop["obj"]->data_loop_singular . ' #' . (1+$setIndex) . ')';
            $canEdit = false;
        } /* elseif ($itemLabel != $GLOBALS["SL"]->closestLoop["obj"]->data_loop_singular . ' #' . (1+$setIndex)) {
            $itemLabel = $itemLabel . ' (' . $GLOBALS["SL"]->closestLoop["obj"]->data_loop_singular 
                . ' #' . (1+$setIndex) . ')';
        } */
        $ico = '';
        if ($this->allNodes[$nID]->isStepLoop()) {
            if ($this->sessData->loopItemsNextID > 0 
                && $this->sessData->loopItemsNextID == $loopItem->getKey()) {
                $ico = '<i class="fa fa-arrow-circle-o-right"></i>';
            } elseif (in_array($loopItem->getKey(), $this->sessData->loopItemIDsDone)) {
                $ico = '<i class="fa fa-check"></i>';
            } else {
                $ico = '<i class="fa fa-check slGrey opac10"></i>';
            }
        }
        return view(
            'vendor.survloop.forms.formtree-looproot-row', 
            [
                "nID"       => $nID,
                "setIndex"  => $setIndex,
                "itemID"    => $loopItem->getKey(),
                "itemLabel" => $itemLabel,
                "canEdit"   => $canEdit,
                "ico"       => $ico, 
                "node"      => $this->allNodes[$nID]
            ]
        )->render();
    }
        
}