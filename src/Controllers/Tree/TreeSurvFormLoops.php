<?php
/**
  * TreeSurvFormLoops is a mid-level class with functions related to printing the loops within forms.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.1.2
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use RockHopSoft\Survloop\Controllers\Tree\TreeSurvFormVarieties;

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
    
    protected function getLoopItemNextLabelCustom($singular)
    {
        return '';
    }
    
    protected function getLoopDoneItemsCustom($loopName)
    {
        return false;
    }
    
    protected function printSetLoopNav($nID, $loopName, $desc = '')
    {
        if (!isset($GLOBALS["SL"]->closestLoop["obj"])
            || !isset($GLOBALS["SL"]->closestLoop["obj"]->data_loop_plural)) {
            return '';
        }
        $this->settingTheLoop($loopName);
        $this->printSetLoopNavInit($nID, $loopName);
        $loopRows = '';
        if ($this->v["currLoopSize"] > 0) {
            $tbl = $GLOBALS["SL"]->dataLoops[$loopName]->data_loop_table;
            foreach ($this->sessData->loopItemIDs[$loopName] as $ind => $rec) {
                $loopRec = $this->sessData->getRowById($tbl, $rec);
                $loopRows .= $this->printSetLoopNavRow($nID, $loopRec, $ind);
            }
            $desc = str_replace('[other]', 'other', $desc);
        } else {
            $desc = str_replace('[other]', '', $desc);
        }
        if (!$this->allNodes[$nID]->isStepLoop()) {
            $GLOBALS["SL"]->pageJAVA .= 'currItemCnt = ' . $this->v["currLoopSize"] 
                . '; maxItemCnt = ' . $this->v["loopMaxLimit"] . '; ';
        }
        /* if ($node->isStepLoop()) {
             && sizeof($this->sessData->loopItemIDs[$loopName]) 
                == sizeof($this->sessData->loopItemIDsDone)) {
            $this->nextBtnOverride = 'Done With ' 
                . $GLOBALS["SL"]->closestLoop["obj"]->data_loop_plural;
        } */
        return $desc . view(
            'vendor.survloop.forms.formtree-looproot-nav', 
            [
                "nID"            => $nID,
                "loopName"       => $loopName,
                "node"           => $this->allNodes[$nID],
                "addBtn"         => $this->printSetLoopNavAddBtn($nID, $loopName),
                "addingLoopItem" => $this->v["addingLoopItem"],
                "currLoopSize"   => $this->v["currLoopSize"],
                "loopMaxLimit"   => $this->v["loopMaxLimit"],
                "loopRows"       => $loopRows,
                "loop"           => $GLOBALS["SL"]->closestLoop["obj"]
            ]
        )->render();
    }

    protected function printSetLoopNavInit($nID, $loopName)
    {
        $this->getLoopLabelFirstLet($nID, $loopName);
        $loop = $GLOBALS["SL"]->closestLoop["obj"];
        $this->v["loopMaxLimit"] 
            = $this->v["currLoopSize"] 
            = $this->v["addingLoopItem"] 
            = 0;
        if (isset($loop->data_loop_max_limit)) {
            $this->v["loopMaxLimit"] = intVal($loop->data_loop_max_limit);
        }
        if (isset($this->sessData->loopItemIDs[$loopName])) {
            $this->v["currLoopSize"] = sizeof($this->sessData->loopItemIDs[$loopName]);
        }
        if ($GLOBALS["SL"]->REQ->has('addLoopItem') 
            && intVal($GLOBALS["SL"]->REQ->addLoopItem) == 1) {
            // signal from previous form to start a new row in the current set
            $this->v["addingLoopItem"] = $this->newLoopItem($nID);
            //$this->updateCurrNode($this->nextNode($this->currNode()));
        } else {
            $this->printSetLoopNavCheckAutoAdd($nID, $loopName);
        }
        if ($this->v["addingLoopItem"] > 0) {
            $GLOBALS["SL"]->pageJAVA .= ' addingLoopItem = ' . $this->v["addingLoopItem"] 
                . '; setLoopItemID(' . $this->v["addingLoopItem"] 
                . '); console.log("addingLoopItem: "+addingLoopItem+""); ';
        } elseif ($GLOBALS["SL"]->REQ->has('editLoopInd')) {
            $ind = intVal($GLOBALS["SL"]->REQ->editLoopInd);
            if ($ind < $this->v["currLoopSize"]
                && isset($this->sessData->loopItemIDs[$loopName][$ind])) {
                $GLOBALS["SL"]->pageJAVA .= ' addingLoopItem = ' 
                    . $this->sessData->loopItemIDs[$loopName][$ind] 
                    . '; console.log("editLoopInd: "+addingLoopItem+""); ';
            }
        }
        return true;
    }

    protected function printSetLoopNavCheckAutoAdd($nID, $loopName)
    {
        $loop = $GLOBALS["SL"]->closestLoop["obj"];
        $loopCnt = sizeof($this->sessData->loopItemIDs[$loopName]);
        $autoAdd = false;
        if ($this->allNodes[$nID]->isStepLoop()) {
            if ($loopCnt == 0 
                && isset($loop->data_loop_min_limit)
                && $loop->data_loop_min_limit == 1 
                && isset($loop->data_loop_max_limit)
                && $loop->data_loop_max_limit == 1) {
                $autoAdd = true;
            }
        } elseif (isset($loop->data_loop_min_limit)
            && $loop->data_loop_min_limit > 0 
            && $loopCnt == 0) {
            $autoAdd = true;
        }

        if ($autoAdd) {
            //$this->v["addingLoopItem"] = $this->newLoopItem($nID);
            $GLOBALS["SL"]->pageCSS .= ' #nFormNextBtn, '
                . '.autoAddHide { display: none; } ';
        }
        return true;
    }

    protected function getLoopLabelFirstLet($nID, $loopName)
    {
        $this->v["labelFirstLet"] = '';
        if ($this->allNodes[$nID]->isStepLoop()) {
            if (!$this->getLoopDoneItemsCustom($loopName)) {
                $this->sessData->getLoopDoneItems($loopName);
            }
            if ($this->sessData->loopItemsNextID > 0) {
                $loopSing = $GLOBALS["SL"]->closestLoop["obj"]->data_loop_singular;
                $custNext = $this->getLoopItemNextLabelCustom($loopSing);
                if ($custNext == '') {
                    $custNext = 'Next ' . $loopSing . ' Details';
                }
                $this->loopItemsCustBtn = '<a href="javascript:;" '
                    . 'class="fR btn btn-lg btn-primary" id="nFormNextStepItem">'
                    . '<i class="fa fa-arrow-circle-o-right mR5"></i> ' . $custNext . '</a>';
                $GLOBALS["SL"]->pageJAVA .= 'loopItemsNextID = ' 
                    . $this->sessData->loopItemsNextID . '; ';
                $this->v["labelFirstLet"] = strtolower(substr(strip_tags($loopSing), 0, 1));
            }
        }
        return $this->v["labelFirstLet"];
    }
    
    protected function printSetLoopNavAddBtn($nID, $loopName)
    {
        $loopRec = $GLOBALS["SL"]->closestLoop["obj"];
        $vowels = [ 'a', 'e', 'i', 'o', 'u' ];

        $itemDesc = 'another';
        if (empty($this->sessData->loopItemIDs[$loopName])) {
            $itemDesc = 'a';
            if (trim($this->v["labelFirstLet"]) == ''
                && isset($loopRec->data_loop_singular)) {
                $this->v["labelFirstLet"] = strtolower(
                    substr(strip_tags($loopRec->data_loop_singular), 0, 1)
                );
            }
            if (in_array($this->v["labelFirstLet"], $vowels)) {
                $itemDesc .= 'n';
            }
        }
        $itemDesc .= ' ' . strtolower($loopRec->data_loop_singular);
        $dis = 'disNon';
        $cnt = 0;
        if (isset($this->sessData->loopItemIDs[$loopName])) {
            $cnt = sizeof($this->sessData->loopItemIDs[$loopName]);
        }
        $max = $loopRec->data_loop_max_limit;
        if ($max == 0 || $cnt < $max) {
            $dis = 'disBlo';
        }
        if ($GLOBALS["SL"]->REQ->has('addLoopItem') 
            && intVal($GLOBALS["SL"]->REQ->addLoopItem) == 1) {
            return $GLOBALS["SL"]->spinner();
        }
        $idHref = 'href="javascript:;" id="nFormAdd"';
        if (isset($GLOBALS["SL"]->closestLoop["obj"]->data_loop_auto_gen)
            && intVal($GLOBALS["SL"]->closestLoop["obj"]->data_loop_auto_gen) == 1) {
            $idHref = 'href="?addLoopItem=1" id="nFormLoopAdd"';
        }
        $firstLet = strtolower(substr(strip_tags($itemDesc), 0, 1));
        return '<a ' . $idHref . ' class="btn btn-lg btn-primary mT15 ' . $dis 
            . '"><i class="fa fa-plus-circle"></i> Add ' 
            . $itemDesc . '</a>' ;
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
            // $itemLabel = 'You (' . $GLOBALS["SL"]->closestLoop["obj"]->data_loop_singular 
            //     . ' #' . (1+$setIndex) . ')';
            $canEdit = false;
        }
        // elseif ($itemLabel != $GLOBALS["SL"]->closestLoop["obj"]->data_loop_singular 
        //    . ' #' . (1+$setIndex)) {
        //    $itemLabel = $itemLabel . ' (' 
        //        . $GLOBALS["SL"]->closestLoop["obj"]->data_loop_singular 
        //        . ' #' . (1+$setIndex) . ')';
        // }
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