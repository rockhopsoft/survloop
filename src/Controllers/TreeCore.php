<?php
/**
  * TreeCore is the bottom-level class for a standard branching tree.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use Illuminate\Http\Request;
use App\Models\SLTree;
use App\Models\SLNode;
use SurvLoop\Controllers\TreeNodeCore;
use SurvLoop\Controllers\SurvLoopController;

class TreeCore extends SurvLoopController
{
    public $treeID             = -3;
    public $treeSize           = 0;
    public $tree               = [];
    public $branches           = [];
    
    public $rootID             = false;
    public $allNodes           = [];
    public $nodeTiers          = [];
    public $nodesRawOrder      = [];
    public $nodesRawIndex      = [];
    protected $currNodeSubTier = [];
    
    public $sessData           = [];
    public $stats              = [];
    
    protected $REQ             = [];
    protected $tmp             = [];
    
    protected $debugOn         = true;
    
    protected function loadNode($nodeRow = NULL)
    {
        if ($nodeRow && isset($nodeRow->NodeID) && $nodeRow->NodeID > 0) {
            return new TreeNodeCore($nodeRow->NodeID, $nodeRow);
        }
        $newNode = new TreeNodeCore();
        $newNode->nodeRow->NodeTree = $this->treeID;
        return $newNode;
    }
    
    protected function hasNode($nID = -3)
    {
        return ( $nID > 0 && isset($this->allNodes[$nID]) );
    }
    
    protected function loadTreeStart($treeIn = -3, Request $request = NULL)
    {
        if ($treeIn > 0) {
            $this->treeID = $treeIn;
        } elseif ($this->treeID <= 0) {
            if (intVal($GLOBALS["SL"]->treeID) > 0) {
                $this->treeID = $GLOBALS["SL"]->treeID;
            } else {
                $this->tree = SLTree::orderBy('TreeID', 'asc')
                    ->first();
                $this->treeID = $this->tree->TreeID;
            }
        }
        return $this->treeID;
    }
    
    public function loadTree($treeIn = -3, Request $request = NULL, $loadFull = false)
    {
        $this->loadTreeStart($treeIn, $request);
        $nodes = [];
        if ($loadFull) {
            $nodes = SLNode::where('NodeTree', $this->treeID)
                ->get();
        } else {
            $nodes = SLNode::where('NodeTree', $this->treeID)
                ->select('NodeID', 'NodeParentID', 'NodeParentOrder')
                ->get();
        }
        $this->treeSize = $nodes->count();
        foreach ($nodes as $row) {
            if ($row->NodeParentID <= 0) {
                $this->rootID = $row->NodeID;
            }
            $this->allNodes[$row->NodeID] = $this->loadNode($row);
        }
        $this->loadNodeTiers();
        $this->loadAllSessData();
        return true;
    }
    
    public function loadAllSessData($coreTbl = '', $coreID = -3)
    {
    }
    
    public function loadNodeTiersCache()
    {
        $cache = '';
        $this->loadNodeTiers();
        if ($this->rootID > 0) {
            $cache .= '$'.'this->nodesRawOrder = [' . implode(', ', $this->nodesRawOrder) . '];' . "\n";
            $cache .= '$'.'this->nodesRawIndex = [';
            foreach ($this->nodesRawIndex as $node => $ind) {
                $cache .= $node . ' => ' . $ind . ', ';
            }
            $cache .= '];' . "\n";
            $cache .= '$'.'this->nodeTiers = ' . $this->loadNodeTiersCacheInner($this->nodeTiers) . ';' . "\n";
        }
        return $cache;
    }
    
    public function loadNodeTiersCacheInner($tier)
    {
        $cache = '[' . $tier[0] . ', [';
        if (sizeof($tier[1]) > 0) {
            foreach ($tier[1] as $i => $t) {
                if ($i > 0) {
                    $cache .= ', ';
                }
                $cache .= $this->loadNodeTiersCacheInner($t);
            }
        }
        return $cache . ']]';
    }
    
    protected function loadNodeTiers()
    {
        $this->nodeTiers = $this->nodesRawOrder = $this->nodesRawIndex = [];
        if ($this->rootID > 0) {
            $this->nodeTiers = [$this->rootID, $this->loadNodeTiersInner($this->rootID)];
            $this->loadRawOrder($this->nodeTiers);
        }
        return true;
    }
    
    protected function loadNodeTiersInner($nodeID = -3, $tierNest = [])
    {
        
        /// THE XML TREE IS JUST BROKEN :( No parent id 755
        
        $innerArr = $tmpArr = [];
        if ($nodeID > 0 && sizeof($this->allNodes) > 0) {
            foreach ($this->allNodes as $nID => $node) {
                if ($node->parentID == $nodeID) {
                    $tmpArr[$nID] = $node->parentOrd;
                }
            }
        }
        if (sizeof($tmpArr) > 0) {
            asort($tmpArr);
            foreach ($tmpArr as $nID => $parentOrder) {
                $tmpTierNest = $tierNest;
                $tmpTierNest[sizeof($tierNest)] = sizeof($innerArr);
                $this->allNodes[$nID]->nodeTierPath = $tmpTierNest;
                $innerArr[] = [$nID, $this->loadNodeTiersInner($nID, $tmpTierNest)];
            }
        }
        return $innerArr;
    }
    
    protected function loadSubTierFromPath($nodeTierPath = [])
    {
        $subTier = $this->nodeTiers;
        if (sizeof($subTier[1]) > 0 && sizeof($nodeTierPath) > 0) {
            foreach ($nodeTierPath as $i => $ind) {
                $subTier = $subTier[1][$ind];
            }
        }
        return $subTier;
    }
    
    protected function loadNodeSubTier($nID = -3)
    {
        if ($this->hasNode($nID)) {
            return $this->loadSubTierFromPath($this->allNodes[$nID]->nodeTierPath);
        }
        return [];
    }
    
    // Cache tree's standard Pre-Order Traversal
    protected function loadRawOrder($tmpSubTier)
    {
        $nID = $tmpSubTier[0];
        $this->nodesRawIndex[$nID] = sizeof($this->nodesRawOrder);
        $this->nodesRawOrder[] = $nID;
        if (sizeof($tmpSubTier[1]) > 0) {
            foreach ($tmpSubTier[1] as $deeper) {
                $this->loadRawOrder($deeper);
            }
        }
        return true;
    }

    // Locate previous node in standard Pre-Order Traversal
    public function prevNode($nID)
    {
        $nodeOverride = $this->movePrevOverride($nID);
        if ($nodeOverride > 0) {
            return $nodeOverride;
        }
        if (!isset($this->nodesRawIndex[$nID])) {
            return -3;
        }
        $prevNodeInd = $this->nodesRawIndex[$nID]-1;
        if ($prevNodeInd < 0 || !isset($this->nodesRawOrder[$prevNodeInd])) {
            return -3;
        }
        $prevNodeID = $this->nodesRawOrder[$prevNodeInd];
        return $prevNodeID;
    }
    
    // Locate next node in standard Pre-Order Traversal
    public function nextNode($nID)
    {
        if ($nID <= 0 || !isset($this->nodesRawIndex[$nID])) {
            return -3;
        }
        $this->runCurrNode($nID);
        $nodeOverride = $this->moveNextOverride($nID);
        if ($nodeOverride > 0) {
            return $nodeOverride;
        }
        //if ($nID == $GLOBALS["SL"]->treeRow->TreeLastPage) return -37;
        $nextNodeInd = $this->nodesRawIndex[$nID]+1;
        if (!isset($this->nodesRawOrder[$nextNodeInd])) {
            return -3;
        }
        $nextNodeID = $this->nodesRawOrder[$nextNodeInd];
        return $nextNodeID;
    }
    
    protected function runCurrNode($nID)
    {
        return true;
    }

    // Locate the next node, outside this node's descendants
    public function nextNodeSibling($nID)
    {
        //if ($nID == $this->tree->TreeLastPage) return -37;
        if (!$this->hasNode($nID) || $this->allNodes[$nID]->parentID <= 0) {
            return -3;
        }
        $nodeOverride = $this->moveNextOverride($nID);
        if ($nodeOverride > 0) {
            return $nodeOverride;
        }
        $nextSibling = SLNode::where('NodeTree', $this->treeID)
            ->where('NodeParentID', $this->allNodes[$nID]->parentID)
            ->where('NodeParentOrder', (1+$this->allNodes[$nID]->parentOrd))
            ->select('NodeID')
            ->first();
        if ($nextSibling && isset($nextSibling->NodeID)) {
            return $nextSibling->NodeID;
        }
        return $this->nextNodeSibling($this->allNodes[$nID]->parentID);
    }
    
    protected function treeAdminNodeManip()
    {
        if ($GLOBALS["SL"]->REQ->has('manip') && $GLOBALS["SL"]->REQ->has('moveNode') 
            && $GLOBALS["SL"]->REQ->has('moveToParent') && $GLOBALS["SL"]->REQ->has('moveToOrder')
            && $GLOBALS["SL"]->REQ->moveNode > 0 && $GLOBALS["SL"]->REQ->moveToParent > 0 
            && $GLOBALS["SL"]->REQ->moveToOrder >= 0 && isset($this->allNodes[$GLOBALS["SL"]->REQ->moveNode])) {
            $node = $this->allNodes[$GLOBALS["SL"]->REQ->moveNode];
            $node->fillNodeRow();
            SLNode::where('NodeParentID', $node->parentID)
                ->where('NodeParentOrder', '>', $node->parentOrd)
                ->decrement('NodeParentOrder');
            SLNode::where('NodeParentID', $GLOBALS["SL"]->REQ->moveToParent)
                ->where('NodeParentOrder', '>=', $GLOBALS["SL"]->REQ->moveToOrder)
                ->increment('NodeParentOrder');
            $node->nodeRow->NodeParentID = $GLOBALS["SL"]->REQ->moveToParent;
            $node->nodeRow->NodeParentOrder = $GLOBALS["SL"]->REQ->moveToOrder;
            $node->nodeRow->save();
            $this->loadTree();
            $this->initExtra($GLOBALS["SL"]->REQ);
        }
        return true;
    }
    
    protected function treeAdminNodeNew($node)
    {
        if ($GLOBALS["SL"]->REQ->input('childPlace') == 'start') {
            SLNode::where('NodeParentID', $GLOBALS["SL"]->REQ->input('nodeParentID'))
                ->increment('NodeParentOrder');
        } elseif ($GLOBALS["SL"]->REQ->input('childPlace') == 'end') {
            $endNode = SLNode::where('NodeParentID', $GLOBALS["SL"]->REQ->input('nodeParentID'))
                ->orderBy('NodeParentOrder', 'desc')
                ->first();
            if ($endNode) {
                $node->nodeRow->NodeParentOrder = 1+$endNode->nodeParentOrder;
            }
        } elseif ($GLOBALS["SL"]->REQ->input('orderBefore') > 0 || $GLOBALS["SL"]->REQ->input('orderAfter') > 0) {
            $foundSibling = false;
            $sibs = SLNode::where('NodeParentID', $GLOBALS["SL"]->REQ->input('nodeParentID'))
                ->orderBy('NodeParentOrder', 'asc')
                ->select('NodeID', 'NodeParentOrder')
                ->get();
            if ($sibs->isNotEmpty()) {
                foreach ($sibs as $sib) {
                    if ($sib->NodeID == intVal($GLOBALS["SL"]->REQ->input('orderBefore'))) { 
                        $node->nodeRow->NodeParentOrder = $sib->NodeParentOrder; 
                        $foundSibling = true;
                    }
                    if ($foundSibling) {
                        SLNode::where('NodeID', $sib->NodeID)
                            ->increment('NodeParentOrder');
                    }
                    if ($sib->NodeID == intVal($GLOBALS["SL"]->REQ->input('orderAfter'))) {
                        $node->nodeRow->NodeParentOrder = (1+$sib->NodeParentOrder);
                        $foundSibling = true;
                    }
                }
            }
        }
        $node->nodeRow->NodeTree = $this->treeID;
        $node->nodeRow->save();
        return $node;
    }
    
    protected function treeAdminNodeDelete($nID)
    {
        if (intVal($nID) > 0 && isset($this->allNodes[$nID])) {
            SLNode::where('NodeParentID', $this->allNodes[$nID]->parentID)
                ->where('NodeParentOrder', '>', $this->allNodes[$nID]->parentOrd)
                ->decrement('NodeParentOrder');
            SLNode::find($nID)->delete();
        }
        return true;
    }
    
    public function rawOrderPercent($nID)
    {
        if (sizeof($this->nodesRawOrder) == 0) {
            return 0;
        }
        $found = 0;
        foreach ($this->nodesRawOrder as $i => $raw) {
            if ($nID == $raw) {
                $found = $i;
            }
        }
        $rawPerc = round(100*($found/sizeof($this->nodesRawOrder)));
        return $this->rawOrderPercentTweak($nID, $rawPerc, $found);
    }
    
    
    /*****************
    // to be overridden by extensions of this class...
    *****************/
    
    protected function movePrevOverride($nID)
    {
        return -3;
    }
    
    protected function moveNextOverride($nID)
    {
        return -3;
    }
    
    protected function isDisplayableNode($nID)
    {
        if (!$this->hasNode($nID)) {
            return false;
        }
        return true;
    }
    
    // Updates currNode after running checking if this is a branch node
    public function updateCurrNodeNB($newCurrNode = -3, $direction = 'next')
    {
        $new = $this->getNextNonBranch($newCurrNode, $direction);
        /* if ($new == -37 && $GLOBALS["SL"]->treeRow->TreeOpts%5 == 0 && $new == $this->currNode()) {
            $this->leavingTheLoop('', true);
            return $GLOBALS["SL"]->treeRow->TreeRoot;
        } */
        return $this->updateCurrNode($new);
    }
    
    public function getNextNonBranch($nID, $direction = 'next')
    {
        return $nID;
    }
    
    public function getPrevOfType($nID, $type = 'Page')
    {
        if (isset($this->nodesRawIndex[$nID]) && isset($this->allNodes[$nID])) {
            $ind = $this->nodesRawIndex[$nID]-1;
            while ($ind >= 0 && $this->allNodes[$this->nodesRawOrder[$ind]]->nodeType != $type) {
                $ind--;
            }
            if ($ind >= 0 && $this->allNodes[$this->nodesRawOrder[$ind]]->nodeType == $type) {
                return $this->nodesRawOrder[$ind];
            }
        }
        return -3;
    }
    
    protected function leavingTheLoop($name = '', $justClearID = false)
    {
        return true;
    }
    
    // Updates currNode without checking if this is a branch node
    public function updateCurrNode($nID = -3)       
    {
        if ($nID > 0) {
            if (!isset($GLOBALS["SL"]->formTree->TreeID)) {
                if (!$this->sessInfo) {
                    $this->createNewSess();
                }
                $this->sessInfo->SessCurrNode = $nID;
                $this->sessInfo->save();
                if ($GLOBALS["SL"]->coreTblAbbr() != '' && isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl])) {
                    $this->sessData->currSessData($nID, $GLOBALS["SL"]->coreTbl, 
                        $GLOBALS["SL"]->coreTblAbbr() . 'SubmissionProgress', 'update', $nID);
                }
            }
            $this->currNodeSubTier = $this->loadNodeSubTier($nID);
            $this->loadNodeDataBranch($nID);
        }
        return true;
    }
    
    protected function jumpToNodeCustom($nID)
    {
        return -3;
    }
    
    protected function jumpToNode($nID)
    {
        $newID = $this->jumpToNodeCustom($nID);
        if ($newID <= 0) { // nothing custom happened, check standard maneuvers
            if (intVal($GLOBALS["SL"]->REQ->jumpTo) > 0) {
                $newID = intVal($GLOBALS["SL"]->REQ->jumpTo);
            }
        }
        return $newID;
    }
    
    protected function nodePrintJumpToCustom($nID = -3)
    {
        return -3;
    }
    
    protected function nodePrintJumpTo($nID = -3)
    {
        $jumpID = $this->nodePrintJumpToCustom($nID);
        if ($jumpID <= 0) {
            if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('afterJumpTo') && intVal($GLOBALS["SL"]->REQ->afterJumpTo) > 0) {
                $jumpID = intVal($GLOBALS["SL"]->REQ->afterJumpTo);
            } elseif (isset($this->sessInfo->SessAfterJumpTo) && intVal($this->sessInfo->SessAfterJumpTo) > 0) {
                $jumpID = $this->sessInfo->SessAfterJumpTo; 
                $this->sessInfo->SessAfterJumpTo = -3; // reset this after using it
                $this->sessInfo->save();
            }
        }
        return $jumpID;
    }
    
    public function currNodeURL($nID = -3)
    {
        return '';
    }
    
    public function currNode()
    {
        if (!isset($GLOBALS["SL"]->formTree->TreeID) && isset($this->sessInfo->SessCurrNode)) {
            return intVal($this->sessInfo->SessCurrNode);
        }
        return $GLOBALS["SL"]->treeRow->TreeRoot;
    }
    
    protected function getParentsAncestry($nID)
    {
        $this->v["ancestors"] = [];
        $parent = -3;
        if (isset($this->allNodes[$nID])) {
            $parent = $this->allNodes[$nID]->getParent();
        }
        while ($parent > 0 && isset($this->allNodes[$parent])) {
            $this->v["ancestors"][] = $parent;
            $parent = $this->allNodes[$parent]->getParent();
        }
        return $this->v["ancestors"];
    }
    
    protected function loadAncestry($nID)
    {
        $this->getParentsAncestry($nID);
        return $this->loadAncestXtnd($nID);
    }
    
    protected function loadAncestXtnd($nID)
    {
        return true;
    }
    
}