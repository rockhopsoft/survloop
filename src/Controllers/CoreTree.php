<?php
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\SLTree;
use App\Models\SLNode;

use SurvLoop\Controllers\CoreNode;
use SurvLoop\Controllers\SurvLoopController;

class CoreTree extends SurvLoopController
{
    
    public $treeID        = -3;
    public $treeSize      = 0;
    public $tree          = array();
    public $branches      = array();
    
    public $rootID        = false;
    public $allNodes      = array();
    public $nodeTiers     = array();
    public $nodesRawOrder = array();
    public $nodesRawIndex = array();
    
    protected $REQ        = array();
    
    protected function loadNode($nodeRow = array())
    {
        if ($nodeRow && $nodeRow->NodeID > 0) return new CoreNode($nodeRow->NodeID, $nodeRow);
        $newNode = new CoreNode();
        $newNode->nodeRow->NodeTree = $this->treeID;
        return $newNode;
    }
    
    protected function hasNode($nID = -3)
    {
        return ( $nID > 0 && isset($this->allNodes[$nID]) );
    }
    
    public function loadTree($treeIn = -3, Request $req = NULL, $loadFull = false)
    {
        if ($req && sizeof($req) > 0) $this->REQ = $req;
        if ($treeIn > 0) $this->treeID = $treeIn;
        elseif ($this->treeID <= 0) {
            $this->tree = SLTree::orderBy('TreeID', 'asc')->first();
            $this->treeID = $this->tree->TreeID;
        }
        $nodes = array();
        if ($loadFull) $nodes = SLNode::where('NodeTree', $this->treeID)
            ->get();
        else $nodes = SLNode::where('NodeTree', $this->treeID)
            ->select('NodeID', 'NodeParentID', 'NodeParentOrder')
            ->get();
        $this->treeSize = sizeof($nodes);
        foreach ($nodes as $row) {
            if ($row->NodeParentID <= 0) $this->rootID = $row->NodeID;
            $this->allNodes[$row->NodeID] = $this->loadNode($row);
        }
        $this->loadNodeTiers();
        $this->loadAllSessData();
        return true;
    }
    
    protected function loadAllSessData($coreID = -3) { }
    
    public function loadNodeTiersCache()
    {
        $cache = '';
        $this->loadNodeTiers();
        if ($this->rootID > 0) {
            $cache .= '$'.'this->nodesRawOrder = [' . implode(', ', $this->nodesRawOrder) . '];' . "\n";
            $cache .= '$'.'this->nodesRawIndex = [';
            foreach ($this->nodesRawIndex as $node => $ind) $cache .= $node . ' => ' . $ind . ', ';
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
                if ($i > 0) $cache .= ', ';
                $cache .= $this->loadNodeTiersCacheInner($t);
            }
        }
        return $cache . ']]';
    }
    
    protected function loadNodeTiers()
    {
        $this->nodeTiers = $this->nodesRawOrder = $this->nodesRawIndex = array();
        if ($this->rootID > 0) {
            $this->nodeTiers = [$this->rootID, $this->loadNodeTiersInner($this->rootID)];
            $this->loadRawOrder($this->nodeTiers);
        }
        return true;
    }
    
    protected function loadNodeTiersInner($nodeID = -3, $tierNest = array())
    {
        $innerArr = $tmpArr = array();
        if ($nodeID > 0 && sizeof($this->allNodes) > 0) {
            foreach ($this->allNodes as $nID => $node) {
                if ($node->parentID == $nodeID) $tmpArr[$nID] = $node->parentOrd;
            }
        }
        if (sizeof($tmpArr) > 0) {
            asort($tmpArr);
            foreach ($tmpArr as $nID => $parentOrder) {
                $tmpTierNest = $tierNest;
                $tmpTierNest[sizeof($tierNest)] = sizeof($innerArr);
                $this->allNodes[$nID]->nodeTierPath = $tmpTierNest;
                $innerArr[] = array($nID, $this->loadNodeTiersInner($nID, $tmpTierNest));
            }
        }
        return $innerArr;
    }
    
    protected function loadSubTierFromPath($nodeTierPath = array())
    {
        $subTier = $this->nodeTiers;
        if (sizeof($subTier[1]) > 0 && sizeof($nodeTierPath) > 0) {
            foreach ($nodeTierPath as $i => $ind) $subTier = $subTier[1][$ind];
        }
        return $subTier;
    }
    
    protected function loadNodeSubTier($nID = -3)
    {
        if ($this->hasNode($nID)) {
            return $this->loadSubTierFromPath($this->allNodes[$nID]->nodeTierPath);
        }
        return array();
    }
    
    
    
    // Cache tree's standard Pre-Order Traversal
    protected function loadRawOrder($tmpSubTier)
    {
        $nID = $tmpSubTier[0];
        $this->nodesRawIndex[$nID] = sizeof($this->nodesRawOrder);
        $this->nodesRawOrder[] = $nID;
        if (sizeof($tmpSubTier[1]) > 0) {
            foreach ($tmpSubTier[1] as $deeper) $this->loadRawOrder($deeper);
        }
        return true;
    }

    // Locate previous node in standard Pre-Order Traversal
    protected function prevNode($nID)
    {
        $nodeOverride = $this->movePrevOverride($nID);
        if ($nodeOverride > 0) return $nodeOverride;
        
        $prevNodeInd = $this->nodesRawIndex[$nID]-1;
        if ($prevNodeInd < 0 || !isset($this->nodesRawOrder[$prevNodeInd])) return -3;
        $prevNodeID = $this->nodesRawOrder[$prevNodeInd];
        return $prevNodeID;
    }
    
    // Locate next node in standard Pre-Order Traversal
    protected function nextNode($nID)
    {
        $nodeOverride = $this->moveNextOverride($nID);
        if ($nodeOverride > 0) return $nodeOverride;
        
        //if ($nID == $GLOBALS["DB"]->treeRow->TreeLastPage) return -37;
        $nextNodeInd = $this->nodesRawIndex[$nID]+1;
        if (!isset($this->nodesRawOrder[$nextNodeInd])) return -3;
        $nextNodeID = $this->nodesRawOrder[$nextNodeInd];
        return $nextNodeID;
    }

    // Locate the next node, outside this node's descendants
    protected function nextNodeSibling($nID)
    {
        //if ($nID == $this->tree->TreeLastPage) return -37;
        if (!$this->hasNode($nID) || $this->allNodes[$nID]->parentID <= 0) return -3;
        $nodeOverride = $this->moveNextOverride($nID);
        if ($nodeOverride > 0) return $nodeOverride;
        
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
        if ($this->REQ->has('manip') && $this->REQ->has('moveNode') 
            && $this->REQ->has('moveToParent') && $this->REQ->has('moveToOrder')
            && $this->REQ->moveNode > 0 && $this->REQ->moveToParent > 0 
            && $this->REQ->moveToOrder >= 0 && isset($this->allNodes[$this->REQ->moveNode])) {
            $node = $this->allNodes[$this->REQ->moveNode];
            $node->fillNodeRow();
            SLNode::where('NodeParentID', $node->parentID)
                ->where('NodeParentOrder', '>', $node->parentOrd)
                ->decrement('NodeParentOrder');
            SLNode::where('NodeParentID', $this->REQ->moveToParent)
                ->where('NodeParentOrder', '>=', $this->REQ->moveToOrder)
                ->increment('NodeParentOrder');
            $node->nodeRow->NodeParentID = $this->REQ->moveToParent;
            $node->nodeRow->NodeParentOrder = $this->REQ->moveToOrder;
            $node->nodeRow->save();
            $this->loadTree();
            $this->initExtra($request);
        }
        return true;
    }
    
    protected function treeAdminNodeNew($node)
    {
        if ($this->REQ->input('childPlace') == 'start') {
            SLNode::where('NodeParentID', $this->REQ->input('nodeParentID'))
                ->increment('NodeParentOrder');
        } elseif ($this->REQ->input('childPlace') == 'end') {
            $endNode = SLNode::where('NodeParentID', $this->REQ->input('nodeParentID'))
                ->orderBy('NodeParentOrder', 'desc')
                ->first();
            if ($endNode) $node->nodeRow->NodeParentOrder = 1+$endNode->nodeParentOrder;
        } elseif ($this->REQ->input('orderBefore') > 0 || $this->REQ->input('orderAfter') > 0) {
            $foundSibling = false;
            $sibs = SLNode::where('NodeParentID', $this->REQ->input('nodeParentID'))
                ->orderBy('NodeParentOrder', 'asc')
                ->select('NodeID', 'NodeParentOrder')
                ->get();
            if (sizeof($sibs) > 0) {
                foreach ($sibs as $sib) {
                    if ($sib->NodeID == intVal($this->REQ->input('orderBefore'))) { 
                        $node->nodeRow->NodeParentOrder = $sib->NodeParentOrder; 
                        $foundSibling = true;
                    }
                    if ($foundSibling) {
                        SLNode::where('NodeID', $sib->NodeID)
                            ->increment('NodeParentOrder');
                    }
                    if ($sib->NodeID == intVal($this->REQ->input('orderAfter'))) {
                        $node->nodeRow->NodeParentOrder = (1+$sib->NodeParentOrder);
                        $foundSibling = true;
                    }
                }
            }
        }
        $node->nodeRow->save();
        return $node;
    }
    
    protected function treeAdminNodeDelete($nID)
    {
        if (intVal($nID) > 0 && isset($this->allNodes[$nID])) {
            SLNode::where('NodeParentID', $this->allNodes[$nID]->parentID)
                ->where('NodeParentOrder', '>', $this->allNodes[$nID]->parentOrd)
                ->decrement('NodeParentOrder');
            $this->allNodes[$nID]->nodeRow->delete();
        }
        return true;
    }
    
    /*****************
    // to be overridden by extensions of this class...
    *****************/
    
    protected function movePrevOverride($nID) { return -3; }
    protected function moveNextOverride($nID) { return -3; }
    
    protected function isDisplayableNode($nID)
    {
        if (!$this->hasNode($nID)) return false;
        return true;
    }
    
    
}
