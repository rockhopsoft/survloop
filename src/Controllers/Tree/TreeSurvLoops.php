<?php
/**
  * TreeSurvLoops is a mid-level class used for logic related to survey loops.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.13
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\User;
use App\Models\SLDefinitions;
use App\Models\SLNode;
use App\Models\SLFields;
use App\Models\SLTokens;
use App\Models\SLUsersActivity;
use RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv;
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvReport;

class TreeSurvLoops extends TreeSurvReport
{
    
    protected function newLoopItem($nID = -3)
    {
        if (intVal($this->newLoopItemID) <= 0) {
            $loop = $GLOBALS["SL"]->closestLoop["obj"]->data_loop_plural;
            $this->leavingTheLoop($loop, true);
            $loopTbl = $GLOBALS["SL"]->closestLoop["obj"]->data_loop_table;
            $skipLinks = $this->newLoopItemSkipLinks($loopTbl);
            $newID = $this->sessData->createNewDataLoopItem($nID, $skipLinks);
            $this->afterCreateNewDataLoopItem($loopTbl, $newID);
            if ($newID > 0) {
                //$GLOBALS["SL"]->REQ->loopItem = $newID;
                $this->settingTheLoop(trim($loop), $newID);
            }
            $this->newLoopItemID = $newID;
        }
        return $this->newLoopItemID;
    }
    
    /**
     * Look up the record linking fields which should be skipped
     * when auto-creating a new loop item's database record.
     *
     * @return array
     */
    protected function newLoopItemSkipLinks($tbl = '')
    {
        return [];
    }
    
    protected function checkLoopsPostProcessing($newNode, $prevNode)
    {
        $backToRoot = false;
        if ($newNode <= 0) {
            $newNode = $this->nextNode($prevNode);
        }
        // First, are we leaving one of our current loops?..
        if (sizeof($GLOBALS["SL"]->sessLoops) > 0) {
            foreach ($GLOBALS["SL"]->sessLoops as $sessLoop) {
                if (isset($GLOBALS["SL"]->dataLoops[$sessLoop->sess_loop_name])) {
                    $this->checkLeavingLoop($newNode, $prevNode, $backToRoot, $sessLoop);
                }
            }
        }
        // If we haven't already tried to leave our loop, 
        // nor returned back to its root node...
        if (!$backToRoot && sizeof($GLOBALS["SL"]->dataLoops) > 0) {
            foreach ($GLOBALS["SL"]->dataLoops as $loop) {
                if (!isset($currLoops[$loop->data_loop_plural]) 
                    && isset($this->allNodes[$loop->data_loop_root])) {
                    // Then this is a new loop we weren't previously in
                    $this->checkEnteringLoop($newNode, $prevNode, $loop, $backToRoot);
                }
            }
        }
        /*
        if ($this->currNode() != $newNode) {
            return $this->checkLoopsPostProcessing($this->currNode(), $newNode);
        }
        */
        return true;
    }
    
    protected function checkLoopsLeft($currNode)
    {
        if (sizeof($GLOBALS["SL"]->sessLoops) > 0) {
            foreach ($GLOBALS["SL"]->sessLoops as $sessLoop) {
                if (isset($GLOBALS["SL"]->dataLoops[$sessLoop->sess_loop_name])) {
                    $loop = $GLOBALS["SL"]->dataLoops[$sessLoop->sess_loop_name];
                    $loopTierPath = $this->allNodes[$loop->data_loop_root]->nodeTierPath;
                    if (isset($this->allNodes[$currNode])
                        && !$this->allNodes[$currNode]->checkBranch($loopTierPath)) {
                        $this->leavingTheLoop($loop->data_loop_plural);
                    }
                }
            }
        }
        return true;
    }
    
    protected function checkLeavingLoop(&$newNode, $prevNode, &$backToRoot, $sessLoop)
    {
        $currLoops[$sessLoop->sess_loop_name] = $sessLoop->sess_loop_item_id;
        $loop = $GLOBALS["SL"]->dataLoops[$sessLoop->sess_loop_name];
        $loopTierPath = $this->allNodes[$loop->data_loop_root]->nodeTierPath;
        $plural = $loop->data_loop_plural;
        $root = $loop->data_loop_root;
        if (isset($this->allNodes[$prevNode]) 
            && isset($this->allNodes[$newNode])
            && isset($this->allNodes[$root])
            && $this->allNodes[$prevNode]->checkBranch($loopTierPath)
            && !$this->allNodes[$newNode]->checkBranch($loopTierPath)) {
            // Then we are now trying to leave this loop
            if (in_array($this->REQstep, ['back', 'exitLoopBack'])) { 
                // Then leaving the loop backwards, always allowed
                $this->leavingTheLoop($plural);
            } elseif ($this->REQstep != 'save') { 
                // Check for conditions before moving leaving forward
                $this->checkLeavingLoopMoveForward($backToRoot, $loop);
            }
        } elseif ($newNode == $root) {
            // Landing directly on the loop's root
            if ($this->skipCurrLoopRoot($loop, $newNode)) {
                $this->pushCurrNodeVisit($newNode);
                if ($this->REQstep == 'back') {
                    $this->leavingTheLoop($plural);
                    $prev = $this->getNextNonBranch($this->prevNode($root), 'prev');
                    $this->updateCurrNodeNB($prev, 'prev');
                } elseif ($this->REQstep != 'save') {
                    $this->updateCurrNodeNB($this->nextNode($root));
                }
            }
        }
        return true;
    }
    
    protected function checkLeavingLoopMoveForward(&$backToRoot, $loop)
    {
        if ($this->allNodes[$loop->data_loop_root]->isStepLoop()) {
            if (sizeof($this->sessData->loopItemIDs[$loop->data_loop_plural]) > 1) {
                $backToRoot = true;
            }
        } elseif (intVal($loop->data_loop_max_limit) == 0 
            || sizeof($this->sessData->loopItemIDs[$loop->data_loop_plural]) 
                < $loop->data_loop_max_limit) {
            // Then sure, we can add another item to this loop, 
            // back at the root node
            $backToRoot = true;
        }
        if ($backToRoot) {
            $this->updateCurrNode($loop->data_loop_root);
            $this->leavingTheLoop($loop->data_loop_plural, true);
        } else { // OK, let's allow the user to keep going outside the loop
            $this->sessInfo->sess_loop_root_just_left = $loop->data_loop_root;
            $this->sessInfo->save();
            $this->leavingTheLoop($loop->data_loop_plural);
        }
        return $backToRoot;
    }
    
    protected function checkEnteringLoop(&$newNode, $prevNode, $loop, $backToRoot)
    {
        $path = $this->allNodes[$loop->data_loop_root]->nodeTierPath;
        if (isset($this->allNodes[$prevNode]) 
            && !$this->allNodes[$prevNode]->checkBranch($path)
            && $this->allNodes[$newNode]->checkBranch($path)) {
            // Then we have just entered this loop from outside
            if ($this->allNodes[$loop->data_loop_root]->isStepLoop() 
                && (!isset($this->sessData->loopItemIDs[$loop->data_loop_plural]) 
                    || empty($this->sessData->loopItemIDs[$loop->data_loop_plural]))) {
                $this->checkEnteringLoopInactive($newNode, $loop);
            } else { // This loop is active
                $this->checkEnteringLoopActive($newNode, $loop);
            }
        }
        return true;
    }
    
    protected function checkEnteringLoopInactive(&$newNode, $loop)
    {
        $this->leavingTheLoop($loop->data_loop_plural);
        if (isset($this->REQstep) 
            && in_array($this->REQstep, ['back', 'exitLoopBack'])) {
            $prevRoot = $this->getNextNonBranch(
                $this->prevNode($loop->data_loop_root), 
                'prev'
            );
            $this->updateCurrNodeNB($prevRoot);
        } elseif (!isset($this->REQstep) || $this->REQstep != 'save') {
            $this->updateCurrNodeNB($this->nextNodeSibling($newNode));
        }
        return true;
    }
    
    protected function checkEnteringLoopActive(&$newNode, $loop)
    {
        $skipRoot = $this->skipCurrLoopRoot($loop);
        $this->settingTheLoop($loop->data_loop_plural);
        if ($newNode == $loop->data_loop_root) {
            // Then we landed directly on the loop's root node from outside, 
            // so we must be going forward not back
            if ($skipRoot) {
                $this->checkEnteringLoopActiveSkipForward($newNode, $loop);
            }
        } else {
            // Must have landed at the loop's end node from outside, 
            // so we going back not forward
            if ($skipRoot) {
                $this->checkEnteringLoopActiveSkipBackward($newNode, $loop);
            } else {
                $this->updateCurrNode($loop->data_loop_root);
            }
        }
        return true;
    }
    
    protected function checkEnteringLoopActiveSkipForward(&$newNode, $loop)
    {
        $this->pushCurrNodeVisit($newNode);
        $itemID = -3;
        if ($this->allNodes[$loop->data_loop_root]->isStepLoop()) {
            $itemID = $this->sessData->loopItemIDs[$loop->data_loop_plural][0];
        } elseif ($loop->DataLoopAutoGen == 1) {
            $itemID = $this->sessData->createNewDataLoopItem($loop->data_loop_root);
            $this->afterCreateNewDataLoopItem($loop->data_loop_plural, $itemID);
        }
        $GLOBALS["SL"]->REQ->loop = $loop->data_loop_plural;
        $GLOBALS["SL"]->REQ->loopItem = $itemID;
        $this->settingTheLoop($loop->data_loop_plural, $itemID);
        $this->updateCurrNodeNB($this->nextNode($loop->data_loop_root));
        $GLOBALS["SL"]->loadSessLoops($this->sessID);
        return true;
    }
    
    protected function checkEnteringLoopActiveSkipBackward(&$newNode, $loop)
    {
        $this->pushCurrNodeVisit($newNode);
        if ($this->allNodes[$loop->data_loop_root]->isStepLoop()) {
            $plural = $loop->data_loop_plural;
            $this->settingTheLoop($plural, $this->sessData->loopItemIDs[$plural][0]);
        }
        return true;
    }
    
    public function activateCurrLoopRoot($loop)
    {

    }
    
    protected function skipCurrLoopRoot($loop, $newNode = 0)
    {
        $skipRoot = false;
        if ($newNode <= 0) {
            $newNode = $loop->data_loop_root;
        }
        $loopCnt = sizeof($this->sessData->loopItemIDs[$loop->data_loop_plural]);
        if ($this->allNodes[$newNode]->isStepLoop()) {
            $one = ($loop->data_loop_min_limit == 1 && $loop->data_loop_max_limit == 1);
            if ($loopCnt == 1 || $one) {
                $skipRoot = true;
            }
        // } elseif ($loop->data_loop_min_limit > 0 && $loopCnt == 0) {
        //     $skipRoot = true;
        }
        return $skipRoot;
    }

    protected function nodePrintLoopCycle($curr)
    {
        $ret = '';
        list($curr->tbl, $curr->fld, $newVal) = $this->nodeBranchInfo($curr->nID);
        $loop = str_replace('LoopItems::', '', $curr->nodeRow->node_response_set);
        $loopCycle = $this->sessData->getLoopRows($loop);
        if (sizeof($curr->tmpSubTier[1]) > 0 && sizeof($loopCycle) > 0) {
            $GLOBALS["SL"]->currCyc["cyc"][0] = $GLOBALS["SL"]->getLoopTable($loop);
            foreach ($loopCycle as $i => $loopItem) {
                $GLOBALS["SL"]->currCyc["cyc"][1] = 'cyc' . $i;
                $GLOBALS["SL"]->currCyc["cyc"][2] = $loopItem->getKey();
                $this->sessData->startTmpDataBranch($curr->tbl, $loopItem->getKey());
                $GLOBALS["SL"]->fakeSessLoopCycle($loop, $loopItem->getKey());
                foreach ($curr->tmpSubTier[1] as $c => $child) {
                    if (!$this->allNodes[$child[0]]->isPage()) {
                        $ret .= $this->printNodePublic(
                            $child[0], 
                            $child, 
                            $curr->currVisib
                        );
                    }
                }
                $GLOBALS["SL"]->removeFakeSessLoopCycle($loop, $loopItem->getKey());
                $this->sessData->endTmpDataBranch($curr->tbl);
                $GLOBALS["SL"]->currCyc["cyc"][1] = '';
                $GLOBALS["SL"]->currCyc["cyc"][2] = -3;
            }
            $GLOBALS["SL"]->currCyc["cyc"][0] = '';
        }
        return $ret;
    }

    protected function nodePrintLoopSort(&$curr)
    {
        $ret = '';
        $loop = str_replace('LoopItems::', '', $curr->nodeRow->node_response_set);
        $loopCycle = $this->sessData->getLoopRows($loop);
        if (sizeof($loopCycle) > 0) {
            $GLOBALS["SL"]->pageAJAX .= '$("#sortable").sortable({ 
                axis: "y", update: function (event, ui) {
                var url = "/sortLoop/?n=' . $curr->nID 
                . '&"+$(this).sortable("serialize")+"";
                document.getElementById("hidFrameID").src=url;
            } }); $("#sortable").disableSelection();';
            $ret .= '<div class="nFld">' . $this->sortableStart($curr->nID) 
                . '<ul id="sortableN' . $curr->nID . '" class="slSortable">' . "\n";
            foreach ($loopCycle as $i => $loopItem) {
                $ret .= '<li id="item-' . $loopItem->getKey() 
                    . '" class="sortOff" onMouseOver="this.className=\'sortOn\';" '
                    . 'onMouseOut="this.className=\'sortOff\';">'
                    . '<span><i class="fa fa-sort slBlueDark"></i></span> ' 
                    . $this->getLoopItemLabel($loop, $loopItem, $i) . '</li>' . "\n";
            }
            $ret .= '</ul>' . $this->sortableEnd($curr->nID) . '</div>' . "\n";
        }
        return $ret;
    }

}
