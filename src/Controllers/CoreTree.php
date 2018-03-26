<?php
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLSess;
use App\Models\SLSessLoops;

use SurvLoop\Controllers\CoreNode;
use SurvLoop\Controllers\SurvLoopController;

class CoreTree extends SurvLoopController
{
    
    public $treeID                = -3;
    public $treeSize              = 0;
    public $tree                  = [];
    public $branches              = [];
    
    public $rootID                = false;
    public $allNodes              = [];
    public $nodeTiers             = [];
    public $nodesRawOrder         = [];
    public $nodesRawIndex         = [];
    protected $currNodeSubTier    = [];
    
    public $sessData              = [];
    
    protected $REQ                = [];
    protected $tmp                = [];
    
    protected $debugOn            = true;
    
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
    
    protected function loadTreeStart($treeIn = -3, Request $request = NULL)
    {
        if ($request && sizeof($request) > 0) $this->REQ = $request;
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
    
    public function loadTree($treeIn = -3, Request $req = NULL, $loadFull = false)
    {
        $this->loadTreeStart($treeIn, $req);
        $nodes = [];
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
    
    public function loadAllSessData($coreTbl = '', $coreID = -3) { }
    
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
        $this->nodeTiers = $this->nodesRawOrder = $this->nodesRawIndex = [];
        if ($this->rootID > 0) {
            $this->nodeTiers = [$this->rootID, $this->loadNodeTiersInner($this->rootID)];
            $this->loadRawOrder($this->nodeTiers);
        }
        return true;
    }
    
    protected function loadNodeTiersInner($nodeID = -3, $tierNest = array())
    {
        
        /// THE XML TREE IS JUST BROKEN :( No parent id 755
        
        $innerArr = $tmpArr = [];
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
        if (!isset($this->nodesRawIndex[$nID])) return -3;
        $prevNodeInd = $this->nodesRawIndex[$nID]-1;
        if ($prevNodeInd < 0 || !isset($this->nodesRawOrder[$prevNodeInd])) return -3;
        $prevNodeID = $this->nodesRawOrder[$prevNodeInd];
        return $prevNodeID;
    }
    
    // Locate next node in standard Pre-Order Traversal
    protected function nextNode($nID)
    {
        if ($nID <= 0 || !isset($this->nodesRawIndex[$nID])) return -3;
        $this->runCurrNode($nID);
        $nodeOverride = $this->moveNextOverride($nID);
        if ($nodeOverride > 0) return $nodeOverride;
        //if ($nID == $GLOBALS["SL"]->treeRow->TreeLastPage) return -37;
        $nextNodeInd = $this->nodesRawIndex[$nID]+1;
        if (!isset($this->nodesRawOrder[$nextNodeInd])) return -3;
        $nextNodeID = $this->nodesRawOrder[$nextNodeInd];
        return $nextNodeID;
    }
    
    protected function runCurrNode($nID)
    {
        return true;
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
            $this->initExtra($this->REQ);
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
            if ($endNode) $node->nodeRow->NodeParentOrder = 1+$endNode->nodeParentOrder;
        } elseif ($GLOBALS["SL"]->REQ->input('orderBefore') > 0 || $GLOBALS["SL"]->REQ->input('orderAfter') > 0) {
            $foundSibling = false;
            $sibs = SLNode::where('NodeParentID', $GLOBALS["SL"]->REQ->input('nodeParentID'))
                ->orderBy('NodeParentOrder', 'asc')
                ->select('NodeID', 'NodeParentOrder')
                ->get();
            if (sizeof($sibs) > 0) {
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
        if (sizeof($this->nodesRawOrder) == 0) return 0;
        $found = 0;
        foreach ($this->nodesRawOrder as $i => $raw) { if ($nID == $raw) $found = $i; }
        $rawPerc = round(100*($found/sizeof($this->nodesRawOrder)));
        return $this->rawOrderPercentTweak($nID, $rawPerc, $found);
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
    
    
    /*****************
    // Some More Generalized Session Processes
    *****************/
    
    protected function loadSessInfo($coreTbl)
    {
//$currRand = rand(0, 100);
//echo '<br /><br /><br />loadSessInfo ' . $currRand . ' setSessCore1 c#' . $this->coreID . ' s#' . $this->sessID . '<br />';
        if (!isset($this->v["currPage"])) $this->survLoopInit($this->REQ); // not sure why this 
        if (isset($GLOBALS["SL"]->formTree->TreeID)) return false; 
        
        // If we're loading a Page that doesn't even have a Core Table, then we skip all the session checks...
        if ((!isset($GLOBALS["SL"]->treeRow->TreeCoreTable) || intVal($GLOBALS["SL"]->treeRow->TreeCoreTable) <= 0)
            && $GLOBALS["SL"]->treeRow->TreeType == 'Page') {
            $this->sessInfo = new SLSess;
            $this->sessID = $this->coreID = -3;
            $GLOBALS["SL"]->setClosestLoop();
            return false;
        }
        if ($this->REQ->has('start') && $this->REQ->has('new') && !isset($this->tmp["startNewCreate"])) {
//echo 'loadSessInfo ' . $currRand . ' createNewSess A<br />';
            $this->createNewSess();
            $this->newCoreRow($coreTbl);
            $this->tmp["startNewCreate"] = true;
        } elseif (isset($this->v) && $this->v["uID"] > 0) {
            //$recentSessTime = mktime(date('H')-2, date('i'), date('s'), date('m'), date('d'), date('Y'));
            $this->sessInfo = SLSess::where('SessUserID', $this->v["uID"])
                ->where('SessTree', $this->treeID)
                ->where('SessCoreID', '>', 0)
                //->where('updated_at', '>', date('Y-m-d H:i:s', $recentSessTime))
                ->orderBy('updated_at', 'desc')
                ->first();
            if ($this->sessInfo && isset($this->sessInfo->SessID)) {
                if ($this->recordIsEditable($coreTbl, $this->sessInfo->SessCoreID)) {
                    $this->sessID = $this->sessInfo->SessID;
                    $this->coreID = $this->sessInfo->SessCoreID;
                } else {
                    $this->sessInfo = [];
                }
            }
        } else {
            if (session()->has('sessID' . $GLOBALS["SL"]->sessTree)) {
                $this->sessID = intVal(session()->get('sessID' . $GLOBALS["SL"]->sessTree));
            }
            if (session()->has('coreID' . $GLOBALS["SL"]->sessTree)) {
                $this->coreID = intVal(session()->get('coreID' . $GLOBALS["SL"]->sessTree));
            }
            if ($this->sessID > 0) $this->sessInfo = SLSess::find($this->sessID);
        }
        if (!$this->sessInfo || sizeof($this->sessInfo) == 0) {
//echo 'loadSessInfo ' . $currRand . ' createNewSess B<br />';
            $this->createNewSess();
        }
        $this->setSessCore($this->coreID);
//echo 'loadSessInfo ' . $currRand . ' setSessCore2 c#' . $this->coreID . ' s#' . $this->sessID . '<br />';
        
        // Check for and load core record's ID
        if ($this->coreID <= 0 && $this->sessInfo && isset($this->sessInfo->SessCoreID) 
            && intVal($this->sessInfo->SessCoreID) > 0) {
            $this->coreID = $this->sessInfo->SessCoreID;
        }
//echo 'loadSessInfo ' . $currRand . ' setSessCore3 c#' . $this->coreID . ' s#' . $this->sessID . '<br />';
        $this->chkIfCoreIsEditable();
//echo 'loadSessInfo ' . $currRand . ' setSessCore3b c#' . $this->coreID . ' s#' . $this->sessID . '<br />';
        if ($this->coreID <= 0) {
            if ($this->v["uID"] > 0) {
                $pastUserSess = SLSess::where('SessUserID', $this->v["uID"])
                    ->where('SessTree', $this->treeID)
                    ->where('SessCoreID', '>', '0')
                    ->orderBy('updated_at', 'desc')
                    ->get();
                if ($pastUserSess && sizeof($pastUserSess) > 0) {
                    foreach ($pastUserSess as $pastSess) {
                        $this->chkIfCoreIsEditable($pastSess->SessCoreID);
//echo 'loadSessInfo ' . $currRand . ' setSessCore3c.' . $pastSess->SessCoreID . ' c#' . $this->coreID . ' s#' . $this->sessID . '<br />';
                    }
                }
            }
        }
//echo 'loadSessInfo ' . $currRand . ' setSessCore4 c#' . $this->coreID . ' s#' . $this->sessID . '<br />';
        if ($this->coreIDoverride > 0) {
            // should have more permission checks here...
            $this->coreID = $this->coreIDoverride;
        } elseif ($this->coreID <= 0) {
            $this->newCoreRow($coreTbl);
        }
        $this->setSessCore($this->coreID);
//echo 'loadSessInfo ' . $currRand . ' setSessCore5 c#' . $this->coreID . ' s#' . $this->sessID . '<br />';
        $this->sessInfo->SessTree = $this->treeID;
        if ((!isset($this->sessInfo->SessUserID) || intVal($this->sessInfo->SessUserID) <= 0) && $this->v["uID"] > 0) {
            $this->sessInfo->SessUserID = $this->v["uID"];
            $this->logAdd('session-stuff', 'Assigning Sess#' . $this->sessID . ' to U#' . $this->v["uID"] 
                . ' <i>(loadSessInfo)</i>');
        }
        $chkNode = SLNode::where('NodeID', $this->sessInfo->SessCurrNode)
            ->where('NodeTree', $this->treeID)
            ->first();
        if (!$chkNode || sizeof($chkNode) == 0) {
            $this->sessInfo->SessCurrNode = 0;
            $nodeSaves = DB::table('SL_NodeSavesPage')
                ->join('SL_Node', 'SL_Node.NodeID', '=', 'SL_NodeSavesPage.PageSaveNode')
                ->where('SL_NodeSavesPage.PageSaveSession', $this->coreID)
                ->where('SL_Node.NodeTree', $this->treeID)
                ->select('SL_NodeSavesPage.*')
                ->orderBy('updated_at', 'desc')
                ->get();
            if ($nodeSaves && sizeof($nodeSaves) > 0) {
                foreach ($nodeSaves as $i => $s) {
                    if ($this->sessInfo->SessCurrNode <= 0 && isset($s->PageSaveNode) 
                        && isset($this->allNodes[$s->PageSaveNode])) {
                        $this->sessInfo->SessCurrNode = $s->PageSaveNode;
                    }
                }
            }
            if ($this->sessInfo->SessCurrNode <= 0 && isset($GLOBALS["SL"]->treeRow->TreeRoot)) {
                $this->sessInfo->SessCurrNode = $GLOBALS["SL"]->treeRow->TreeRoot;
            }
        }
        $this->sessInfo->save();
        session()->put('lastTree', $GLOBALS["SL"]->sessTree);
//echo 'loadSessInfo ' . $currRand . ' setSessCore6 c#' . $this->coreID . ' s#' . $this->sessID . '<br />';
        $this->chkIfCoreIsEditable();
        $this->updateCurrNode($this->sessInfo->SessCurrNode);
        
        $GLOBALS["SL"]->loadSessLoops($this->sessID);
        
        // Initialize currNode
        if ($coreTbl > 0 && isset($GLOBALS["SL"]->tblAbbr[$coreTbl])) {
			$subFld = $GLOBALS["SL"]->tblAbbr[$coreTbl] . 'SubmissionProgress';
			if (isset($this->sessData->dataSets[$coreTbl]) 
				&& isset($this->sessData->dataSets[$coreTbl][0])
				&& isset($this->sessData->dataSets[$coreTbl][0]->{ $subFld })
				&& intVal($this->sessData->dataSets[$coreTbl][0]->{ $subFld }) > 0) {
				$this->updateCurrNode($this->sessData->dataSets[$coreTbl][0]->{ $subFld });
			} elseif (isset($this->sessInfo->SessCurrNode) && intVal($this->sessInfo->SessCurrNode) > 0) {
				$this->updateCurrNode($this->sessInfo->SessCurrNode);
			} else {
				$this->updateCurrNode($this->rootID);
			}
		}
//echo 'loadSessInfo ' . $currRand . ' setSessCore7 c#' . $this->coreID . ' s#' . $this->sessID . '<br />';
        return true;
    }
    
    protected function setCoreRecUser($coreID = -3, $coreRec = [])
    {
        if ($coreID <= 0) $coreID = $this->coreID;
        if ($coreRec && sizeof($coreRec) > 0 && $this->v["uID"] > 0) {
            if (trim($GLOBALS["SL"]->coreTblUserFld) != '') {
                $this->logAdd('session-stuff', 'Assigning ' . $GLOBALS["SL"]->coreTbl . '#' . $coreID . ' from U#' 
                    . $coreRec->{ $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl] . 'UserID' } . ' to U#' 
                    . $this->v["uID"] . ' <i>(setCoreRecUser)</i>');
                $coreRec->{ $GLOBALS["SL"]->coreTblUserFld } = $this->v["uID"];
                $coreRec->save();
            }
        }
        return $coreRec;
    }
    
    public function chkCoreRecEmpty($coreID = -3, $coreRec = [])
    {
        return false;
    }
    
    // Check that core record is actually in-progress (editable)
    protected function chkIfCoreIsEditable($coreID = -3, $coreRec = [])
    {
//echo 'chkIfCoreIsEditable(' . $coreID . ', ' . $this->coreID . ', 11%= ' . ($GLOBALS["SL"]->treeRow->TreeOpts%11) . '<br />';
        if ($coreID <= 0) $coreID = $this->coreID;
        if ($coreID > 0) {
            if ($GLOBALS["SL"]->treeRow->TreeOpts%11 > 0 // Tree allows record edits
                && !$this->recordIsEditable($GLOBALS["SL"]->coreTbl, $coreID, $coreRec)) {
                session()->forget('coreID' . $this->treeID);
                session()->forget('coreID' . $GLOBALS["SL"]->sessTree);
                if (isset($this->sessInfo->SessCoreID)) {
                    $this->sessInfo->SessCoreID = 0;
                    $this->sessInfo->save();
                }
                $this->coreID = -3;
            } else {
                $this->coreID = $coreID;
            }
        }
        return true;
    }
    
    protected function recordIsEditable($coreTbl, $coreID, $coreRec = [])
    {
        return $this->recordIsIncomplete($coreTbl, $coreID, $coreRec);
    }
    
    protected function recordIsIncomplete($coreTbl, $coreID, $coreRec = [])
    {
        return true;
    }
    
    public function recordIsPublished($coreTbl, $coreID, $coreRec = [])
    {
        return !$this->recordIsIncomplete($coreTbl, $coreID, $coreRec);
    }
    
    protected function createNewSess($cid = -3)
    {
        $this->sessInfo = new SLSess;
        $this->sessInfo->SessUserID = $this->v["uID"];
        $this->sessInfo->SessTree = $this->treeID;
        $this->sessInfo->save();
        $this->setSessCore($cid);
        $this->logAdd('session-stuff', 'New Session ' . $GLOBALS["SL"]->coreTbl . '#' . $this->coreID . ', Sess#' 
            . $this->sessID . ' <i>(createNewSess)</i>');
        return $this->sessID;
    }
    
    public function newCoreRow($coreTbl = '')
    {
        $coreTbl = ((trim($coreTbl) != '') ? $coreTbl : $GLOBALS["SL"]->coreTbl);
        $modelPath = $GLOBALS["SL"]->modelPath($coreTbl);
        if (trim($coreTbl) != '' && trim($modelPath) != '') {
            eval("\$recObj = new " . $modelPath . ";");
            if ($GLOBALS["SL"]->treeRow->TreeType == 'Primary Public') {
                $coreAbbr = $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl];
                $recObj->{ $coreAbbr . 'UserID' } = $this->v["uID"];
                $recObj->{ $coreAbbr . 'IPaddy' } = $this->hashIP();
                $recObj->{ $coreAbbr . 'IsMobile' } = $this->isMobile();
                $recObj->{ $coreAbbr . 'UniqueStr' }
                    = $this->getRandStr($GLOBALS["SL"]->coreTbl, $coreAbbr . 'UniqueStr', 20);
            }
            $recObj->save();
            $this->sessInfo->SessCurrNode = $this->rootID;
            $this->sessInfo->save();
            $this->setSessCore($recObj->getKey());
            $this->logAdd('session-stuff', 'New Record ' . $GLOBALS["SL"]->coreTbl . '#' . $this->coreID . ', Sess#' 
                . $this->sessID . ' <i>(newCoreRow)</i>');
            $this->setCoreRecUser($this->coreID, $recObj);
        }
        return $this->coreID;
    }
    
    protected function setSessCore($coreID)
    {
        $this->coreID = $this->sessInfo->SessCoreID = $coreID;
        $this->sessInfo->save();
        $this->sessID = $this->sessInfo->SessID;
        session()->put('sessID' . $GLOBALS["SL"]->sessTree, $this->sessID);
        session()->put('coreID' . $GLOBALS["SL"]->sessTree, $this->coreID);
        return true;
    }
    
    public function restartSess(Request $request)
    {
        $trees = SLTree::get();
        if ($trees && sizeof($trees) > 0) {
            foreach ($trees as $tree) {
                SLSess::where('SessID', session()->get('sessID' . $tree->TreeID))
                    ->where('SessTree', $tree->TreeID)
                    ->delete();
                session()->forget('sessID' . $tree->TreeID);
                session()->forget('coreID' . $tree->TreeID);
            }
        }
        session()->forget('sessIDPage');
        session()->forget('coreIDPage');
        session()->flush();
        $request->session()->flush();
        $loc = '/';
        if ($request->has('then') && trim($request->get('then')) != '') $loc = trim($request->get('then'));
        return '<center><h2 style="margin-top: 60px;">...Restarting Site Session...</h2>'
            . '<div style="display: none;"><iframe src="/logout"></iframe></div></center>'
            . '<script type="text/javascript"> setTimeout("window.location=\'' . $loc . '\'", 1000); </script>';
        //return $this->redir('/logout', true);
    }
    
    public function chkSess($cid)
    {
        if ($this->v["uID"] <= 0) return false;
        return SLSess::where('SessCoreID', $cid)
            ->where('SessTree', $this->treeID)
            ->where('SessUserID', $this->v["uID"])
            ->orderBy('updated_at', 'desc')
            ->first();
    }
    
    public function switchSess($request, $cid)
    {
        $this->survLoopInit($request);
        if (!$cid || intVal($cid) <= 0) return $this->redir('/my-profile');
        $ownerUser = -3;
        eval("\$chkRec = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl)
            . "::find(" . $cid . ");"); // session()->get('coreID' . $GLOBALS["SL"]->sessTree)
        if (!$chkRec || sizeof($chkRec) == 0) return $this->redir('/my-profile');
        if (isset($chkRec->{ $GLOBALS["SL"]->coreTblUserFld })) {
            $ownerUser = intVal($chkRec->{ $GLOBALS["SL"]->coreTblUserFld });
            if ($ownerUser != $this->v["uID"]) return $this->redir('/my-profile');
        }
        $session = $this->chkSess($cid);
        if ($session && isset($session->SessID)) {
            $this->sessInfo = $session;
            $this->sessID = $session->SessID;
            $this->coreID = $cid;
        }
        if (!$this->sessInfo || !isset($this->sessInfo->SessTree)) {
//echo 'umm C<br />';
            $this->createNewSess($cid);
            $this->logAdd('session-stuff', 'Switch To New Sess#' . $this->sessID . ', ' . $GLOBALS["SL"]->coreTbl . '#' 
                . $this->coreID . ' <i>(switchSess)</i>');
        } else {
            $this->logAdd('session-stuff', 'Switch To Sess#' . $this->sessID . ', ' . $GLOBALS["SL"]->coreTbl . '#' 
                . $this->coreID . ' <i>(switchSess)</i>');
        }
        if ($request->has('fromthe') && $request->get('fromthe') == 'top') {
            $this->sessInfo->SessCurrNode = $GLOBALS["SL"]->treeRow->TreeFirstPage;
        } elseif ($request->has('fromnode') && intVal($request->get('fromnode')) > 0) {
            $this->sessInfo->SessCurrNode = intVal($request->get('fromnode'));
        } elseif (!isset($this->sessInfo->SessCurrNode) || intVal($this->sessInfo->SessCurrNode) <= 0) {
            $nodeFld = $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl] . 'SubmissionProgress';
            if (isset($chkRec->{ $nodeFld }) && intVal($chkRec->{ $nodeFld }) > 0) {
                $this->sessInfo->SessCurrNode = intVal($chkRec->{ $nodeFld });
            }
        }
        $this->sessInfo->save();
        $this->setSessCore($cid);
        if ($this->sessInfo->SessCurrNode > 0) {
            $nodeRow = SLNode::find($this->sessInfo->SessCurrNode);
            if ($nodeRow && isset($nodeRow->NodePromptNotes) && trim($nodeRow->NodePromptNotes) != '') {
                return $this->redir('/u/' . $GLOBALS['SL']->treeRow->TreeSlug . '/' . $nodeRow->NodePromptNotes, true);
            }
        }
        return $this->redir('/start/' . $GLOBALS["SL"]->treeRow->TreeSlug, true);
        //return $this->redir('/afterLogin');
    }

    public function afterLogin(Request $request)
    {
        $this->survLoopInit($request, '');
        if ($this->v["user"] && $this->v["user"]->hasRole('administrator|staff')) {
            return redirect()->intended('dashboard');
            //return $this->redir('/dashboard');
        } else {
            $sessTree = ((session()->has('sessTreeReg')) ? session()->get('sessTreeReg') : $GLOBALS["SL"]->sessTree);
            $sessInfo = [];
            $coreAbbr = $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl];
            $minute = mktime(date("H"), date("i")-1, date("s"), date('n'), date('j'), date('Y'));
            if ($minute < strtotime($this->v["user"]->created_at)) { // signed up in the past minute
                $firstUser = User::select('id')->get();
                if ($firstUser && sizeof($firstUser) == 1) {
                    $user->assignRole('administrator');
                    $this->logAdd('session-stuff', 'New System Administrator #' . $this->v["user"]->id . ' Registered');
                } elseif ($request->has('newVolunteer') && intVal($request->newVolunteer) == 1) {
                    $user->assignRole('volunteer');
                    $this->logAdd('session-stuff', 'New Volunteer #' . $this->v["user"]->id . ' Registered');
                } else {
                    $this->logAdd('session-stuff', 'New User #' . $this->v["user"]->id . ' Registered');
                }
                if (session()->has('coreID' . $sessTree)) {
                    eval("\$chkRec = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl)
                        . "::find(" . session()->get('coreID' . $sessTree) . ");");
                    if ($chkRec && isset($chkRec->{ $coreAbbr . 'IPaddy' })) {
                        if ($chkRec->{ $coreAbbr . 'IPaddy' } == $this->hashIP() 
                            && (!isset($chkRec->{ $GLOBALS["SL"]->coreTblUserFld }) 
                                || intVal($chkRec->{ $GLOBALS["SL"]->coreTblUserFld }) <= 0)) {
                            $chkRec->update([ $GLOBALS["SL"]->coreTblUserFld => $this->v["uID"] ]);
                            $this->logAdd('session-stuff', 'Assigning ' . $GLOBALS["SL"]->coreTbl . '#'
                                . $chkRec->getKey() . ' to U#' . $this->v["uID"] . ' <i>(afterLogin)</i>');
                        }
                    }
                }
                if (session()->has('sessID' . $sessTree)) {
                    $sessInfo = SLSess::find(session()->get('sessID' . $sessTree));
                    if ($sessInfo && isset($sessInfo->SessTree)) {
                        if (!isset($sessInfo->SessUserID) || intVal($sessInfo->SessUserID) <= 0) {
                            $sessInfo->update([ 'SessUserID' => $this->v["uID"] ]);
                            $this->logAdd('session-stuff', 'Assigning Sess#' . $sessInfo->getKey() . ' to U#' 
                                . $this->v["uID"] . ' <i>(afterLogin)</i>');
                        }
                    }
                }
            }
            //$this->loadSessInfo($GLOBALS["SL"]->coreTbl);
            if (!session()->has('coreID' . $sessTree) || $this->coreID <= 0) {
                $this->coreID = $this->findUserCoreID();
                if ($this->coreID > 0) {
                    session()->put('coreID' . $sessTree, $this->coreID);
                    $this->logAdd('session-stuff', 'Putting Cookie ' . $GLOBALS["SL"]->coreTbl . '#'
                        . $this->coreID . ' for U#' . $this->v["uID"] . ' <i>(afterLogin)</i>');
                }
            }
            if (sizeof($sessInfo) > 0 && isset($sessInfo->SessCurrNode) && intVal($sessInfo->SessCurrNode) > 0) {
                $this->loadTree();
                $nodeURL = $this->currNodeURL($this->sessInfo->SessCurrNode);
                if (trim($nodeURL) != '') return $this->redir($nodeURL);
            }
            return redirect()->intended('my-profile');
            //return $this->redir('/my-profile');
        }
        return redirect()->intended('/home');
        //return $this->redir('/');
    }

    public function findUserCoreID()
    {
        $this->coreIncompletes = [];
        if ($this->v["uID"] > 0) {
            eval("\$incompletes = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl)
                . "::where('" . $GLOBALS["SL"]->coreTblUserFld . "', " . $this->v["uID"] . ")"
                . "->orderBy('created_at', 'desc')->get();");
            if ($incompletes && sizeof($incompletes) > 0) {
                foreach ($incompletes as $i => $row) {
                    if ($this->recordIsIncomplete($GLOBALS["SL"]->coreTbl, $row->getKey(), $row)) {
                        $this->coreIncompletes[] = [$row->getKey(), $row];
                    }
                }
                if (sizeof($this->coreIncompletes) > 0) {
                    return $this->coreIncompletes[0][0];
                }
            }
        }
        return -3;
    }
    
    public function multiRecordCheck($oneToo = false)
    {
        $this->v["multipleRecords"] = '';
        if (trim($GLOBALS["SL"]->coreTbl) != '') {
            $coreID = $this->findUserCoreID();
            if (!$this->coreIncompletes || $coreID <= 0 || sizeof($this->coreIncompletes) <= 0
                || (sizeof($this->coreIncompletes) == 1 && !$oneToo)) return '';
            foreach ($this->coreIncompletes as $i => $coreRow) {
                $this->v["multipleRecords"] .= $this->multiRecordCheckRow($i, $coreRow);
            }
            if (trim($this->v["multipleRecords"]) != '') {
                $this->v["multipleRecords"] = '<div class="nodeGap"></div>'
                    . $this->multiRecordCheckIntro(sizeof($this->coreIncompletes))
                    . '<div id="hidivUnfinished" class="disNon brdDrk round5 p10 mTn10"><div class="p10"></div>'
                    . $this->v["multipleRecords"] . '</div>';
                /* if (!session()->has('multiRecordCheck')) {
                    $GLOBALS["errors"] .= $this->v["multipleRecords"];
                    session()->put('multiRecordCheck', date('Y-m-d H:i:s'));
                } */
            }
        }
        return $this->v["multipleRecords"];
    }
    
    public function multiRecordCheckIntro($cnt = 1)
    {
        return '<a id="hidivBtnUnfinished" class="btn btn-lg btn-primary w100 hidivBtn" href="javascript:;">' 
            . $this->v["user"]->name . ', You Have ' 
            . (($cnt == 1) ? 'An Unfinished Session' : 'Unfinished Sessions') . '</a>';
    }
    
    public function multiRecordCheckRow($i, $coreRecord)
    {
        if ($this->recordIsEditable($GLOBALS["SL"]->coreTbl, $coreRecord[1]->getKey(), $coreRecord[1])) {
            return view('vendor.survloop.unfinished-record-row', [
                "tree"     => $this->treeID,
                "cID"      => $coreRecord[1]->getKey(),
                "title"    => $this->multiRecordCheckRowTitle($coreRecord), 
                "desc"     => $this->multiRecordCheckRowSummary($coreRecord),
                "warning"  => $this->multiRecordCheckDelWarn()
            ])->render();
        }
        return '';
    }
    
    public function multiRecordCheckRowTitle($coreRecord)
    {
        $recSingVar = 'tree-' . $GLOBALS["SL"]->treeID . '-core-record-singular';
        $recName = ' #' . $coreRecord[1]->getKey();
        if (isset($GLOBALS["SL"]->sysOpts[$recSingVar])) {
            $recName = $GLOBALS["SL"]->sysOpts[$recSingVar] . $recName;
        }
        return trim($recName);
    }
    
    public function multiRecordCheckRowSummary($coreRecord)
    {
        return 'Started ' . date('M j, Y, g:ia', strtotime($coreRecord[1]->created_at));
    }
    
    public function multiRecordCheckDelWarn()
    {
        return 'Are you sure you want to delete this session? Deleting it CANNOT be undone.';
    }
    
    public function deactivateSess($treeID = 1)
    {
        $this->logAdd('session-stuff', 'Deactivate Sess#' . $this->sessID . ', Last Node#' 
            . $this->sessInfo->SessCurrNode . ' <i>(deactivateSess)</i>');
        if ($this->sessInfo->SessTree == $treeID) {
            $this->sessInfo->SessCurrNode = -86; // all outta this
            $this->sessInfo->save();
        }
        if ($this->v["uID"] > 0) {
            SLSess::where('SessUserID', $this->v["uID"])
                ->where('SessTree', $treeID)
                ->update(['SessCurrNode' => -86]); // ->where('SessCoreID', $coreID)
        }
        session()->forget('sessID' . $treeID);
        session()->forget('coreID' . $treeID);
        return true;
    }
    
    public function delSess(Request $request, $coreID)
    {
        $this->survLoopInit($request);
        if ($this->isCoreOwner($coreID) || ($this->v["user"] && $this->v["user"]->hasRole('administrator|staff'))) {
            if ($coreID != $this->coreID) $this->sessData->loadCore($GLOBALS["SL"]->coreTbl, $coreID);
            $this->sessData->deleteEntireCore();
            if ($coreID != $this->coreID) $this->sessData->loadCore($GLOBALS["SL"]->coreTbl, $this->coreID);
            $sess = false;
            if ($this->v["uID"] > 0) {
                $sess = SLSess::where('SessUserID', $this->v["uID"])
                    ->where('SessTree', $this->treeID)
                    ->where('SessCoreID', $coreID)
                    ->first();
            } elseif (session()->has('coreID' . $GLOBALS["SL"]->sessTree) 
                && $coreID == session()->get('coreID' . $GLOBALS["SL"]->sessTree)) {
                $sess = SLSess::find($coreID);
            }
            $this->logAdd('session-stuff', 'Deleting Sess#' . (($sess && isset($sess->SessID)) ? $sess->SessID : 0) 
                . ' to U#' . $this->v["uID"] . ' <i>(delSess)</i>');
            if ($sess && isset($sess->SessID)) {
                SLSessLoops::where('SessLoopSessID', $sess->SessID)
                    ->delete();
                SLSess::find($sess->SessID)
                    ->delete();
            }
            session()->forget('sessID' . $GLOBALS["SL"]->sessTree);
            session()->forget('coreID' . $GLOBALS["SL"]->sessTree);
            session()->put('sessMsg', $this->delSessMsg($coreID));
            $newCoreID = $this->findUserCoreID();
            if ($this->coreIncompletes && sizeof($this->coreIncompletes) == 1 && $newCoreID > 0) {
//echo 'umm D<br />';
                $this->createNewSess();
                $this->setSessCore($newCoreID);
            }
        }
        return $this->redir('/my-profile');
    }
    
    public function delSessMsg($coreID)
    {
        return 'You have deleted #' . $coreID . '.';
    }
    
    public function holdSess(Request $request)
    {
        return date("Y-m-d H:i:s");
    }
    
    protected function isCoreOwner($coreID = -3)
    {
        if ($coreID <= 0) $coreID = $this->coreID;
        if ($this->v["uID"] <= 0) {
            if (session()->has('coreID' . $GLOBALS["SL"]->sessTree)
                && $coreID == intVal(session()->get('coreID' . $GLOBALS["SL"]->sessTree))
                && session()->has('sessID' . $GLOBALS["SL"]->sessTree)
                && intVal(session()->get('sessID' . $GLOBALS["SL"]->sessTree)) > 0) {
                $chk = SLSess::where('SessID', session()->get('sessID' . $GLOBALS["SL"]->sessTree))
                    ->whereIn('SessTree', [$this->treeID, $GLOBALS["SL"]->sessTree])
                    ->where('SessCoreID', $coreID)
                    ->get();
                if ($chk && sizeof($chk) > 0) return true;
            }
            return false;
        }
        // else user is already logged in
        if ($coreID <= 0) $coreID = $this->coreID;
        $chk = SLSess::where('SessTree', $this->treeID)
            ->where('SessUserID', $this->v["uID"])
            ->where('SessCoreID', $coreID)
            ->get();
        if ($chk && sizeof($chk) > 0) return true;
        if (trim($GLOBALS["SL"]->coreTblUserFld) != '') {
            eval("\$chk = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) 
                . "::where('" . $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl] . "ID', " . $coreID . ")"
                . "->where('" . $GLOBALS["SL"]->coreTblUserFld . "', " . $this->v["uID"] . ")"
                . "->first();");
            if ($chk && sizeof($chk) > 0) return true;
        }
        return $this->isCoreOwnerAlt($coreID);
    }
    
    protected function isCoreOwnerAlt($coreID = -3)
    {
        return false;
    }
    
    protected function isAdminUser()
    {
        if (!isset($this->v["user"]) || $this->v["uID"] <= 0) return false;
        $perm = $this->v["user"]->highestPermission();
        return (in_array($perm, ['administrator', 'staff']));
    }
    
    protected function isPublic()
    {
        return true;
    }
    
    
    
    public function currNode()
    {
        if (!isset($GLOBALS["SL"]->formTree->TreeID) && isset($this->sessInfo->SessCurrNode)) {
            return intVal($this->sessInfo->SessCurrNode);
        }
        return $GLOBALS["SL"]->treeRow->TreeRoot;
    }
    
    // Updates currNode after running checking if this is a branch node
    protected function updateCurrNodeNB($newCurrNode = -3, $direction = 'next')
    {
        $new = $this->getNextNonBranch($newCurrNode, $direction);
        /* if ($new == -37 && $GLOBALS["SL"]->treeRow->TreeOpts%5 == 0 && $new == $this->currNode()) {
            $this->leavingTheLoop('', true);
            return $GLOBALS["SL"]->treeRow->TreeRoot;
        } */
        return $this->updateCurrNode($new);
    }
    
    protected function getNextNonBranch($nID, $direction = 'next')
    {
        return $nID;
    }
    
    protected function leavingTheLoop($name = '', $justClearID = false)
    {
        return true;
    }
    
    // Updates currNode without checking if this is a branch node
    protected function updateCurrNode($nID = -3)       
    {
        if ($nID > 0) {
            if (!isset($GLOBALS["SL"]->formTree->TreeID)) {
                $this->sessInfo->SessCurrNode = $nID;
                $this->sessInfo->save();
                if (isset($GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl])) {
                    $this->sessData->currSessData($nID, $GLOBALS["SL"]->coreTbl, 
                        $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl] . 'SubmissionProgress', 'update', $nID);
                }
            }
            $this->currNodeSubTier = $this->loadNodeSubTier($nID);
            $this->loadNodeDataBranch($nID);
        }
        return true;
    }
    
    protected function jumpToNodeCustom($nID) { return -3; }
    
    protected function jumpToNode($nID)
    {
        $newID = $this->jumpToNodeCustom($nID);
        if ($newID <= 0) { // nothing custom happened, check standard maneuvers
            if (intVal($GLOBALS["SL"]->REQ->jumpTo) > 0) $newID = intVal($GLOBALS["SL"]->REQ->jumpTo);
        }
        return $newID;
    }
    
    protected function nodePrintJumpToCustom($nID = -3) { return -3; }
    
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
    
    
    /**
     * Update the user's profile.
     *
     * @param  Request  $request
     * @return Response
     */
    public function updateProfile(Request $request)
    {
        if ($request->user()) {
            // $request->user() returns an instance of the authenticated user...
            if ($request->user()->id == $request->uID || $request->user()->hasRole('administrator')) {
                $user = User::find($request->uID);
                $user->name = $request->name;
                $user->email = $request->email;
                $user->save();
                if ($request->roles && sizeof($request->roles) > 0) {
                    foreach ($user->rolesRanked as $i => $role) {
                        if (in_array($role, $request->roles)) {
                            if (!$user->hasRole($role)) {
                                $user->assignRole($role);
                            }
                        } elseif ($user->hasRole($role)) {
                            $user->revokeRole($role);
                        }
                    }
                } else { // no roles selected, delete all that exist
                    foreach ($user->rolesRanked as $i => $role) {
                        if ($user->hasRole($role)) {
                            $user->revokeRole($role);
                        }
                    }
                }
            }
        }
        return $this->redir('/dashboard/user/'.$request->uID);
    }
    
    public function setCurrUserProfile($uname = '')
    {
        if (trim($uname) != '') {
            $this->v["profileUser"] = User::where('name', 'LIKE', urldecode($uname))->first();
            if ($this->v["profileUser"] && isset($this->v["profileUser"]->id)) {
                return true;
            }
        }
        return false;
    }
    
    public function showProfileBasics() 
    {
        if ($this->v["profileUser"] && isset($this->v["profileUser"]->id)) {
            $this->v["canEdit"] = ($this->v["user"] 
                && ($this->v["user"]->hasRole('administrator') || $this->v["user"]->id == $this->v["profileUser"]->id));
            return view('vendor.survloop.profile', $this->v);
        }
        return '<div><i>User not found.</i></div>';
    }
    
    public function genNewCorePubID($tbl = '')
    {
        if (trim($tbl) == '') $tbl = $GLOBALS["SL"]->coreTbl;
        $pubIdFld = $GLOBALS["SL"]->tblAbbr[$tbl] . 'PublicID';
        eval("\$idChk = " . $GLOBALS["SL"]->modelPath($tbl) . "::orderBy('" . $pubIdFld . "', 'desc')->first();");
        if (!$idChk || !isset($idChk->{ $pubIdFld }) || intVal($idChk->{ $pubIdFld }) <= 0) return 1;
        return (1+intVal($idChk->{ $pubIdFld }));
    }
    
}