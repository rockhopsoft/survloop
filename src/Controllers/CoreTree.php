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
    public $tree                  = array();
    public $branches              = array();
    
    public $rootID                = false;
    public $allNodes              = array();
    public $nodeTiers             = array();
    public $nodesRawOrder         = array();
    public $nodesRawIndex         = array();
    protected $currNodeDataBranch = [];
    protected $currNodeSubTier    = [];
    
    public $sessData              = [];
    
    protected $REQ                = array();
    
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
            $this->initExtra($this->REQ);
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
    
    protected function rawOrderPercent($nID)
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
    
    protected function loadsessInfo()
    {
        if (!isset($this->v["currPage"])) $this->survLoopInit($this->REQ); // not sure why this 
        if (session()->has('sessID')) $this->sessID = session()->get('sessID');
        if (session()->has('coreID')) $this->coreID = session()->get('coreID');
        if ($this->sessID > 0) {
            $this->sessInfo = SLSess::find($this->sessID);
        } elseif ($this->sessID < 0 && $this->v["user"] && $this->v["user"]->id > 0) {
            $recentSessTime = mktime(date('H')-2, date('i'), date('s'), date('m'), date('d'), date('Y'));
            $this->sessInfo = SLSess::where('SessUserID', $this->v["user"]->id)
                ->where('SessTree', $this->treeID)
                ->where('updated_at', '>', date('Y-m-d H:i:s', $recentSessTime))
                ->orderBy('updated_at', 'desc')
                ->first();
            if ($this->sessInfo && sizeof($this->sessInfo) > 0) {
                $this->sessID = $this->sessInfo->SessID;
                $this->coreID = $this->sessInfo->SessCoreID;
            }
        }
        if (!$this->sessInfo || sizeof($this->sessInfo) == 0) {
            $this->createNewSess();
        }
        session()->put('sessID', $this->sessID);
        
        // Check for and load core record's ID
        if ($this->coreID <= 0 && $this->sessInfo && isset($this->sessInfo->SessCoreID) 
            && intVal($this->sessInfo->SessCoreID) > 0) {
            $this->coreID = $this->sessInfo->SessCoreID;
        }
        if ($this->coreID <= 0) {
            if ($this->sessInfo->SessCodeID > 0) {
                $this->coreID = $this->sessInfo->SessCodeID;
            } elseif ($this->v["user"] && $this->v["user"]->id > 0) {
                $pastUserSess = SLSess::where('SessUserID', $this->v["user"]->id)
                    ->where('SessTree', $this->treeID)
                    ->where('SessCoreID', '>', '0')
                    ->orderBy('updated_at', 'desc')
                    ->first();
                if ($pastUserSess && isset($pastUserSess->SessCoreID)) {
                    $this->coreID = $pastUserSess->SessCoreID;
                }
            }
        }
        if ($this->coreID <= 0 && trim($GLOBALS["DB"]->coreTbl) != ''
            && class_exists($GLOBALS["DB"]->modelPath($GLOBALS["DB"]->coreTbl))) {
            eval("\$recObj = new " . $GLOBALS["DB"]->modelPath($GLOBALS["DB"]->coreTbl) . ";");
            $recObj->save();
            $this->coreID = $recObj->getKey();
        }
        if ($this->coreIDoverride > 0) {
            // should have more permission checks here...
            $this->coreID = $this->coreIDoverride;
        }
        session()->put('coreID', $this->coreID);
        $this->sessInfo->SessCoreID = $this->coreID;
        if ((!isset($this->sessInfo->SessUserID) || intVal($this->sessInfo->SessUserID) <= 0) 
            && isset($this->v["user"]->id) && intVal($this->v["user"]->id) > 0) {
            $this->sessInfo->SessUserID = $this->v["user"]->id;
        }
        $this->sessInfo->save();
        
        $GLOBALS["DB"]->loadSessLoops($this->sessID);
        
        // Initialize currNode
        $subFld = $GLOBALS["DB"]->tblAbbr[$GLOBALS["DB"]->coreTbl] . 'SubmissionProgress';
        if (isset($this->sessData->dataSets[$GLOBALS["DB"]->coreTbl]) 
            && isset($this->sessData->dataSets[$GLOBALS["DB"]->coreTbl][0])
            && isset($this->sessData->dataSets[$GLOBALS["DB"]->coreTbl][0]->{ $subFld })
            && intVal($this->sessData->dataSets[$GLOBALS["DB"]->coreTbl][0]->{ $subFld }) > 0) {
            $this->updateCurrNode($this->sessData->dataSets[$GLOBALS["DB"]->coreTbl][0]->{ $subFld });
        } elseif (isset($this->sessInfo->SessCurrNode) && intVal($this->sessInfo->SessCurrNode) > 0) {
            $this->updateCurrNode($this->sessInfo->SessCurrNode);
        } else {
            $this->updateCurrNode($this->rootID);
        }
        return true;
    }
    
    protected function createNewSess()
    {
        $this->sessInfo = new SLSess;
        $this->sessInfo->SessUserID = (($this->v["user"] && $this->v["user"]->id > 0) ? $this->v["user"]->id : 0);
        $this->sessInfo->SessTree = $this->treeID;
        $this->sessInfo->save();
        $this->sessID = $this->sessInfo->getKey();
        session()->put('sessID', $this->sessID);
        return $this->sessID;
    }
    
    public function restartSess(Request $request)
    {
        session()->forget('sessID');
        session()->forget('coreID');
        redirect('/logout');
    }
    
    public function chkSess($cid)
    {
        return SLSess::where('SessCoreID', $cid)
            ->where('SessTree', $this->treeID)
            ->where('SessUserID', $this->v["user"]->id)
            ->orderBy('updated_at', 'desc')
            ->first();
    }
    
    public function switchSess($request, $cid)
    {
        $this->survLoopInit($request);
        $session = $this->chkSess($cid);
        if ($session && isset($session->SessID)) {
            session()->set('sessID', $session->SessID);
            session()->set('coreID', $cid);
            $this->sessInfo = $session;
            $this->sessID = $session->SessID;
            $this->coreID = $cid;
        }
        return redirect('/afterLogin');
    }

    public function afterLogin(Request $request)
    {
        $this->survLoopInit($request, '');
        if ($this->v["user"] && $this->v["user"]->hasRole('administrator|staff')) {
            return redirect('/dashboard');
        } elseif ($this->v["user"] && $this->v["user"]->hasRole('volunteer')) {
            return redirect('/volunteer');
        } else {
            $this->loadsessInfo();
            if (!session()->has('coreID') || $this->coreID <= 0) {
                $this->coreID = $this->findUserCoreID();
                if ($this->coreID > 0) session()->put('coreID', $this->coreID);
            }
            if (isset($this->sessInfo->SessCurrNode) && intVal($this->sessInfo->SessCurrNode) > 0) {
                $this->loadTree();
                $nodeURL = $this->currNodeURL($this->sessInfo->SessCurrNode);
                if (trim($nodeURL) != '') return redirect($nodeURL);
            }
        }
        return redirect('/');
    }

    public function findUserCoreID()
    {
        $this->coreIncompletes = [];
        if (isset($this->v["user"]) && isset($this->v["user"]->id)) {
            eval("\$incompletes = " . $GLOBALS["DB"]->modelPath($GLOBALS["DB"]->coreTbl)
                . "::where('" . $GLOBALS["DB"]->coreTblUserFld . "', " . $this->v["user"]->id . ")"
                . "->orderBy('created_at', 'desc')->get();");
            if ($incompletes && sizeof($incompletes) > 0) {
                foreach ($incompletes as $i => $row) {
                    $this->coreIncompletes[] = [$row->getKey(), $row];
                }
                return $this->coreIncompletes[0][0];
            }
        }
        return -3;
    }
    
    public function multiRecordCheck()
    {
        $this->v["multipleRecords"] = '';
        $coreID = $this->findUserCoreID();
        if (!$this->coreIncompletes || sizeof($this->coreIncompletes) <= 1 || $coreID <= 0) return '';
        $this->v["multipleRecords"] = $this->multiRecordCheckIntro();
        foreach ($this->coreIncompletes as $i => $coreRow) {
            $this->v["multipleRecords"] .= $this->multiRecordCheckRow($i, $coreRow);
        }
        if (trim($this->v["multipleRecords"]) != '') {
            $this->v["multipleRecords"] = '<div class="nodeGap"></div><div class="brdDrk round5 p20">' 
                . $this->v["multipleRecords"] . '</div>';
            if (!$this->REQ->session()->has('multiRecordCheck')) {
                $GLOBALS["errors"] .= $this->v["multipleRecords"];
                $this->REQ->session()->put('multiRecordCheck', date('Y-m-d H:i:s'));
            }
        }
        return $this->v["multipleRecords"];
    }
    
    public function multiRecordCheckIntro()
    {
        return '<h1 class="mT0 mB20 slBlueDark">You Have Multiple Sessions:</h1>';
    }
    
    public function multiRecordCheckRow($i, $coreRecord)
    {
        $ret = '<div class="row mT10 mB20">
            <div class="col-md-4">
                <a class="disBlo btn btn-lg nFldBtn mB5 pB0 btn-default taL ';
                if ($coreRecord[0] == $this->coreID) {
                    $ret .= 'noPoint slBlueDark" href="javascript:;"><h2 class="mT10"><b>Current: <nobr>' 
                        . $GLOBALS["DB"]->sysOpts['core-record-singular'] . ' #' 
                        . $coreRecord[0] . '</nobr></b></h3>';
                } else {
                    $ret .= '" href="/switch/' . $coreRecord[0] . '"><h3 class="mT10">Switch To: <nobr>'
                        . $GLOBALS["DB"]->sysOpts['core-record-singular'] . ' #' 
                        . $coreRecord[0] . '</nobr></h3>';
                }
                $ret .= '</a>
                <div class="mB5 fPerc125">Started ' . date('M j, Y', strtotime($coreRecord[1]->created_at)) . '</div>
            </div>
            <div class="col-md-8">
                ' . $this->multiRecordCheckRowSummary($coreRecord) . '
                <div class="fR">
                    <a href="javascript:;" class="red" onClick="if (confirm(\'' . $this->multiRecordCheckDelWarn() 
                    . '\')) { window.location=\'/delSess/' . $coreRecord[0] 
                    . '\'; }">Delete <i class="fa fa-minus-circle" aria-hidden="true"></i></a>
                </div><div class="fC"></div>
            </div>
        </div>
        <div class="p5"></div>';
        return $ret;
    }
    
    public function multiRecordCheckRowSummary($coreRecord)
    {
        return '';
    }
    
    public function multiRecordCheckDelWarn()
    {
        return 'Are you sure you want to delete this session? Deleting it CANNOT be undone.';
    }
    
    public function delSess(Request $request, $coreID)
    {
        $this->survLoopInit($request);
        if ($this->isCoreOwner($coreID)) {
            if ($coreID != $this->coreID) $this->sessData->loadCore($coreID);
            $this->sessData->deleteEntireCore();
            if ($coreID != $this->coreID) $this->sessData->loadCore($this->coreID);
            $sess = SLSess::where('SessUserID', $this->v["user"]->id)
                ->where('SessTree', $this->treeID)
                ->where('SessCoreID', $coreID)
                ->first();
            if ($sess && isset($sess->SessID)) {
                SLSessLoops::where('SessLoopSessID', $sess->SessID)
                    ->delete();
                SLSess::find($sess->SessID)
                    ->delete();
            }
            if ($coreID != session()->get('coreID')) {
                session()->forget('sessID');
                session()->forget('coreID');
                return redirect('/');
            }
        }
        return redirect('/');
    }
    
    public function holdSess(Request $request)
    {
        return date("Y-m-d H:i:s");
    }
    
    protected function isCoreOwner($coreID = -3)
    {
        if (!$this->v["user"] || intVal($this->v["user"]->id) <= 0) return false;
        if ($coreID <= 0) $coreID = $this->coreID;
        $chk = SLSess::where('SessTree', $this->treeID)
            ->where('SessUserID', $this->v["user"]->id)
            ->where('SessCoreID', $coreID)
            ->get();
        if ($chk && sizeof($chk) > 0) return true;
        return false;
    }
    
    protected function isAdminUser()
    {
        if (!$this->v["user"] || intVal($this->v["user"]->id) <= 0) return false;
        $perm = $this->v["user"]->highestPermission();
        return (in_array($perm, ['administrator', 'staff']));
    }
    
    protected function isPublic()
    {
        return true;
    }
    
    
    
    public function currNode()
    {
        return (isset($this->sessInfo->SessCurrNode)) ? intVal($this->sessInfo->SessCurrNode) : -3;
    }
    
    // Updates currNode after running checking if this is a branch node
    protected function updateCurrNodeNB($newCurrNode = -3, $direction = 'next')
    {
        return $this->updateCurrNode($this->getNextNonBranch($newCurrNode, $direction));
    }
    
    // Updates currNode without checking if this is a branch node
    protected function updateCurrNode($nID = -3)       
    {
        if ($nID > 0) {
            $this->sessInfo->SessCurrNode = $nID;
            $this->sessInfo->save();
            if (isset($GLOBALS["DB"]->tblAbbr[$GLOBALS["DB"]->coreTbl])) {
                $this->sessData->currSessData($nID, $GLOBALS["DB"]->coreTbl, 
                    $GLOBALS["DB"]->tblAbbr[$GLOBALS["DB"]->coreTbl] . 'SubmissionProgress', 'update', $nID);
            }
            $this->currNodeSubTier = $this->loadNodeSubTier($nID);
            $this->currNodeDataBranch = $this->loadNodeDataBranch($nID);
        }
        return true;
    }
    
    protected function jumpToNodeCustom($nID) { return -3; }
    
    protected function jumpToNode($nID)
    {
        $newID = $this->jumpToNodeCustom($nID);
        if ($newID <= 0) { // nothing custom happened, check standard maneuvers
            if (intVal($this->REQ->jumpTo) > 0) $newID = intVal($this->REQ->jumpTo);
        }
        return $newID;
    }
    
    protected function nodePrintJumpToCustom($nID = -3) { return -3; }
    
    protected function nodePrintJumpTo($nID = -3)
    {
        $jumpID = $this->nodePrintJumpToCustom($nID);
        if ($jumpID <= 0) {
            if ($this->hasREQ && $this->REQ->has('afterJumpTo') && intVal($this->REQ->afterJumpTo) > 0) {
                $jumpID = intVal($this->REQ->afterJumpTo);
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
       
}