<?php
/**
  * TreeCore is the bottom-level class for a standard branching tree.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use Illuminate\Http\Request;
use App\Models\SLTree;
use App\Models\SLNode;
use RockHopSoft\Survloop\Controllers\Tree\TreeNodeCore;
use RockHopSoft\Survloop\Controllers\SurvloopController;

class TreeCore extends SurvloopController
{
    public $treeID             = 0;
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
    
    protected function loadNode($nodeRow = NULL)
    {
        if ($nodeRow && isset($nodeRow->node_id) && $nodeRow->node_id > 0) {
            return new TreeNodeCore($nodeRow->node_id, $nodeRow);
        }
        $newNode = new TreeNodeCore();
        $newNode->nodeRow->node_tree = $this->treeID;
        return $newNode;
    }
    
    protected function hasNode($nID = -3)
    {
        return ( $nID > 0 && isset($this->allNodes[$nID]) );
    }
    
    /**
     * Override current page as represented in the admin menu.
     *
     * @return string
     */
    public function initAdmMenuExtras()
    {
        return '';
    }
    
    protected function loadTreeStart($treeIn = -3, Request $request = NULL)
    {
        if ($treeIn > 0) {
            $this->treeID = $treeIn;
        } elseif ($this->treeID <= 0) {
            if (intVal($GLOBALS["SL"]->treeID) > 0) {
                $this->treeID = $GLOBALS["SL"]->treeID;
            } else {
                $this->tree = SLTree::orderBy('tree_id', 'asc')
                    ->first();
                $this->treeID = $this->tree->tree_id;
            }
        }
        return $this->treeID;
    }
    
    public function loadTree($treeIn = -3, Request $request = NULL, $loadFull = false)
    {
        $this->loadTreeStart($treeIn, $request);
        $nodes = [];
        if ($loadFull) {
            $nodes = SLNode::where('node_tree', $this->treeID)
                ->get();
        } else {
            $nodes = SLNode::where('node_tree', $this->treeID)
                ->select('node_id', 'node_parent_id', 'node_parent_order')
                ->get();
        }
        $this->treeSize = $nodes->count();
        foreach ($nodes as $row) {
            if ($row->node_parent_id <= 0) {
                $this->rootID = $row->node_id;
            }
            $this->allNodes[$row->node_id] = $this->loadNode($row);
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
            $cache .= '$'.'this->nodesRawOrder = [' 
                . implode(', ', $this->nodesRawOrder) 
                . '];' . "\n" . '$'.'this->nodesRawIndex = [';
            foreach ($this->nodesRawIndex as $node => $ind) {
                $cache .= $node . ' => ' . $ind . ', ';
            }
            $cache .= '];' . "\n" . '$'.'this->nodeTiers = ' 
                . $this->loadNodeTiersCacheInner($this->nodeTiers) . ';' . "\n";
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
            $this->nodeTiers = [
                $this->rootID, 
                $this->loadNodeTiersInner($this->rootID)
            ];
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
                $innerArr[] = [
                    $nID, 
                    $this->loadNodeTiersInner($nID, $tmpTierNest)
                ];
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
        //if ($nID == $GLOBALS["SL"]->treeRow->tree_last_page) return -37;
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
        //if ($nID == $this->tree->tree_last_page) return -37;
        if (!$this->hasNode($nID) || $this->allNodes[$nID]->parentID <= 0) {
            return -3;
        }
        $nodeOverride = $this->moveNextOverride($nID);
        if ($nodeOverride > 0) {
            return $nodeOverride;
        }
        $nextSibling = SLNode::where('node_tree', $this->treeID)
            ->where('node_parent_id', $this->allNodes[$nID]->parentID)
            ->where('node_parent_order', (1+$this->allNodes[$nID]->parentOrd))
            ->select('node_id')
            ->first();
        if ($nextSibling && isset($nextSibling->node_id)) {
            return $nextSibling->node_id;
        }
        return $this->nextNodeSibling($this->allNodes[$nID]->parentID);
    }
    
    protected function treeAdminNodeManip()
    {
        if ($GLOBALS["SL"]->REQ->has('manip') 
            && $GLOBALS["SL"]->REQ->has('moveNode') 
            && $GLOBALS["SL"]->REQ->has('moveToParent') 
            && $GLOBALS["SL"]->REQ->has('moveToOrder')
            && $GLOBALS["SL"]->REQ->moveNode > 0 
            && $GLOBALS["SL"]->REQ->moveToParent > 0 
            && $GLOBALS["SL"]->REQ->moveToOrder >= 0 
            && isset($this->allNodes[$GLOBALS["SL"]->REQ->moveNode])) {
            $node = $this->allNodes[$GLOBALS["SL"]->REQ->moveNode];
            $node->fillNodeRow();
            SLNode::where('node_parent_id', $node->parentID)
                ->where('node_parent_order', '>', $node->parentOrd)
                ->decrement('node_parent_order');
            SLNode::where('node_parent_id', $GLOBALS["SL"]->REQ->moveToParent)
                ->where('node_parent_order', '>=', $GLOBALS["SL"]->REQ->moveToOrder)
                ->increment('node_parent_order');
            $node->nodeRow->node_parent_id = $GLOBALS["SL"]->REQ->moveToParent;
            $node->nodeRow->node_parent_order = $GLOBALS["SL"]->REQ->moveToOrder;
            $node->nodeRow->save();
            $this->loadTree();
            $this->initExtra($GLOBALS["SL"]->REQ);
        }
        return true;
    }
    
    protected function treeAdminNodeNew($node)
    {
        $parentID = $GLOBALS["SL"]->REQ->input('nodeParentID');
        if ($GLOBALS["SL"]->REQ->input('childPlace') == 'start') {
            SLNode::where('node_parent_id', $parentID)
                ->increment('node_parent_order');
        } elseif ($GLOBALS["SL"]->REQ->input('childPlace') == 'end') {
            $endNode = SLNode::where('node_parent_id', $parentID)
                ->orderBy('node_parent_order', 'desc')
                ->first();
            if ($endNode) {
                $node->nodeRow->node_parent_order = 1+$endNode->nodeParentOrder;
            }
        } elseif ($GLOBALS["SL"]->REQ->input('orderBefore') > 0 
            || $GLOBALS["SL"]->REQ->input('orderAfter') > 0) {
            $foundSibling = false;
            $sibs = SLNode::where('node_parent_id', $parentID)
                ->orderBy('node_parent_order', 'asc')
                ->select('node_id', 'node_parent_order')
                ->get();
            if ($sibs->isNotEmpty()) {
                foreach ($sibs as $sib) {
                    if ($sib->node_id == intVal($GLOBALS["SL"]->REQ->orderBefore)) { 
                        $node->nodeRow->node_parent_order = $sib->node_parent_order; 
                        $foundSibling = true;
                    }
                    if ($foundSibling) {
                        SLNode::where('node_id', $sib->node_id)
                            ->increment('node_parent_order');
                    }
                    if ($sib->node_id == intVal($GLOBALS["SL"]->REQ->orderAfter)) {
                        $node->nodeRow->node_parent_order = (1+$sib->node_parent_order);
                        $foundSibling = true;
                    }
                }
            }
        }
        $node->nodeRow->node_tree = $this->treeID;
        $node->nodeRow->save();
        return $node;
    }
    
    protected function treeAdminNodeDelete($nID)
    {
        if (intVal($nID) > 0 && isset($this->allNodes[$nID])) {
            SLNode::where('node_parent_id', $this->allNodes[$nID]->parentID)
                ->where('node_parent_order', '>', $this->allNodes[$nID]->parentOrd)
                ->decrement('node_parent_order');
            SLNode::find($nID)
                ->delete();
        }
        return true;
    }
    
    public function rawOrderPercent($nID)
    {
        if (!isset($this->allNodes[$nID]) || sizeof($this->nodeTiers) < 2) {
            return 0;
        }
        $this->v["percCalc"] = [
            "curr"   => 0,
            "nID"    => $nID,
            "before" => 0,
            "after"  => 0
        ];
        foreach ($this->nodeTiers[1] as $nextTier) {
            $this->rawOrderPercNextNode($nextTier[0], $nextTier[1]);
        }
        $rawPerc = 0;
        $denom = ($this->v["percCalc"]["before"]
            +$this->v["percCalc"]["after"]);
        if ($denom > 0) {
            $rawPerc = round(100*($this->v["percCalc"]["before"]/$denom));
        }
        $found = ($this->v["percCalc"]["nID"] <= 0);
        return $this->rawOrderPercentTweak($nID, $rawPerc, $found);
    }
    
    // Locate next node in standard Pre-Order Traversal
    protected function rawOrderPercNextNode($nID, $tiers = [])
    {
        if (!$this->checkNodeConditionsBasic($nID)) {
            return false;
        }
        if ($this->v["percCalc"]["nID"] == $nID) {
            $this->v["percCalc"]["nID"] = -3;
        } elseif ($this->v["percCalc"]["nID"] > 0) {
            $this->v["percCalc"]["before"]++;
        }
        if (sizeof($tiers) > 0) {
            foreach ($tiers as $nextTier) {
                $this->rawOrderPercNextNode($nextTier[0], $nextTier[1]);
            }
        }
        if ($this->v["percCalc"]["nID"] <= 0) {
            $this->v["percCalc"]["after"]++;
        }
        return true;
    }
    
    public function getTreeNodeDropdownAll($preSel = 0)
    {
        $GLOBALS["SL"]->x["nodeDropdownOpts"] = '';
        if (sizeof($this->nodesRawOrder) > 0) {
            foreach ($this->nodesRawOrder as $ind => $nID) {
                if (isset($this->allNodes[$nID])) {
                    $GLOBALS["SL"]->x["nodeDropdownOpts"] .= '<option value="' 
                        . $nID . '"' . (($preSel == $nID) ? 'SELECTED' : '') . '>'
                        . $nID . ((isset($this->allNodes[$nID]->dataStore))
                            ? ' ' . $this->allNodes[$nID]->dataStore
                            : '');
                }
            }
        }
        return $GLOBALS["SL"]->x["nodeDropdownOpts"];
    }
    
    public function getTreeNodeDropdown($nID, $tiers = [])
    {
        if ($this->v["percCalc"]["nID"] == $nID) {
            $this->v["percCalc"]["nID"] = -3;
        } elseif ($this->v["percCalc"]["nID"] > 0) {
            $this->v["percCalc"]["before"]++;
        }
        if (sizeof($tiers) > 0) {
            foreach ($tiers as $nextTier) {
                $this->rawOrderPercNextNode($nextTier[0], $nextTier[1]);
            }
        }
        if ($this->v["percCalc"]["nID"] <= 0) {
            $this->v["percCalc"]["after"]++;
        }
        return true;
    }
    
    
    /*****************
    // to be overridden by extensions of this class...
    *****************/

    protected function checkNodeConditionsBasic($nID)
    {
        if (!$this->sessData->dataSets
            || sizeof($this->sessData->dataSets) == 0) {
            return true;
        }
        return $this->checkNodeConditions($nID);
    }

    protected function checkNodeConditions($nID)
    {
        return true;
    }
    
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
        /* if ($new == -37 
            && $GLOBALS["SL"]->treeRow->tree_opts%5 == 0 
            && $new == $this->currNode()) {
            $this->leavingTheLoop('', true);
            return $GLOBALS["SL"]->treeRow->tree_root;
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
            $nodeType = $this->allNodes[$this->nodesRawOrder[$ind]]->nodeType;
            while ($ind >= 0 && $nodeType != $type) {
                $ind--;
                if (isset($this->nodesRawOrder[$ind])
                    && isset($this->allNodes[$this->nodesRawOrder[$ind]])) {
                    $nodeType = $this->allNodes[$this->nodesRawOrder[$ind]]->nodeType;
                }
            }
            if ($ind >= 0 && $nodeType == $type) {
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
            if (!isset($GLOBALS["SL"]->formTree->tree_id)) {
                if (!$this->sessInfo) {
                    $this->createNewSess();
                }
                $this->sessInfo->sess_curr_node = $nID;
                $this->sessInfo->save();
                $coreTbl = $GLOBALS["SL"]->coreTbl;
                if ($GLOBALS["SL"]->coreTblAbbr() != '' 
                    && isset($this->sessData->dataSets[$coreTbl])) {
                    $prog = $GLOBALS["SL"]->coreTblAbbr() . 'submission_progress';
                    $this->sessData->currSessDataTblFld($nID, $coreTbl, $prog, 'update', $nID);
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
            if ($this->hasREQ 
                && $GLOBALS["SL"]->REQ->has('afterJumpTo') 
                && intVal($GLOBALS["SL"]->REQ->afterJumpTo) > 0) {
                $jumpID = intVal($GLOBALS["SL"]->REQ->afterJumpTo);
            } elseif (isset($this->sessInfo->sess_after_jump_to) 
                && intVal($this->sessInfo->sess_after_jump_to) > 0) {
                $jumpID = $this->sessInfo->sess_after_jump_to; 
                $this->sessInfo->sess_after_jump_to = -3; // reset this after using it
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
        if (!isset($GLOBALS["SL"]->formTree->tree_id) 
            && isset($this->sessInfo->sess_curr_node)) {
            return intVal($this->sessInfo->sess_curr_node);
        }
        return $GLOBALS["SL"]->treeRow->tree_root;
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