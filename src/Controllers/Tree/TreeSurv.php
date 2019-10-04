<?php
/**
  * TreeSurv is a mid-level class using a standard branching tree, mostly for SurvLoop's surveys and pages.
  * But it does house some of the core functions to print the whole of a tree.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.0.18
  */
namespace SurvLoop\Controllers\Tree;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\User;
use App\Models\SLDefinitions;
use App\Models\SLNode;
use App\Models\SLFields;
use App\Models\SLTokens;
use App\Models\SLUsersActivity;
use SurvLoop\Controllers\Tree\TreeNodeSurv;
use SurvLoop\Controllers\Globals\Globals;
use SurvLoop\Controllers\Tree\TreeSurvReport;

class TreeSurv extends TreeSurvReport
{
    public function printTreePublic()
    {
        $ret = '';
        $this->loadTree();
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Survey' && $this->coreID <= 0) {
            return $this->redir($GLOBALS["SL"]->getCurrTreeUrl(), true);
        }
        $GLOBALS["SL"]->pageJAVA .= view('vendor.survloop.js.inc-check-tree-load', [
            "treeID" => $this->treeID
        ])->render();
        if ($this->hasAjaxWrapPrinting()) {
            $ret .= '<div id="ajaxWrap">';
        }
        $ret .= '<a name="maincontent" id="maincontent"></a>' . "\n";
        if (!$this->isPage) {
            $ret .= '<div id="maincontentWrap" style="display: none;">' . "\n";
        }
        if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('node') 
            && $GLOBALS["SL"]->REQ->input('node') > 0) {
            $this->updateCurrNode($GLOBALS["SL"]->REQ->input('node'));
        }
        
        $lastNode = $this->currNode();
        if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('superHardJump')) {
            $this->updateCurrNode(intVal($GLOBALS["SL"]->REQ->superHardJump));
        }
        if (session()->has('redirLoginSurvey') || $GLOBALS["SL"]->REQ->has('test')) {
            $next = $this->nextNode($this->currNode(), $this->currNodeSubTier);
            $this->updateCurrNodeNB($next);
            $this->setNodeIdURL($this->currNode());
            session()->forget('redirLoginSurvey');
        }
        if ($this->currNode() < 0 || !isset($this->allNodes[$this->currNode()])) {
            $this->updateCurrNode($GLOBALS["SL"]->treeRow->TreeRoot);
            //return '<h1>Sorry, Page Not Found.</h1>';
        }
        // double-check we haven't landed on a mid-page node
        if (isset($this->allNodes[$this->currNode()]) 
            && !$this->allNodes[$this->currNode()]->isPage() 
            && !$this->allNodes[$this->currNode()]->isLoopRoot()) {
            $this->updateCurrNode($this->allNodes[$this->currNode()]->getParent());
        }
        
        $this->loadAncestry($this->currNode());
        
        if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('step')) {
            if (!$this->sessInfo) {
                $this->createNewSess();
            }
            // Process form POST for all nodes, then store the data updates...
            if ($this->REQstep == 'autoSave' 
                && (!$GLOBALS["SL"]->REQ->has('chgCnt') 
                    || intVal($GLOBALS["SL"]->REQ->get('chgCnt')) <= 0)) {
                return 'No changes found to auto-save. <3';
            }
            $nodeIn = $GLOBALS["SL"]->REQ->node;
            $logTitle = 'PAGE SAVE' . (($this->REQstep == 'autoSave') ? ' AUTO' : '');
            $this->sessData->logDataSave($nodeIn, $logTitle, -3, '', '');
            $ret .= $this->postNodePublic($nodeIn);
            if ($this->REQstep == 'autoSave') {
                return 'Saved!-)';
            }
            $this->loadAllSessData();
            if (!$this->isPage) {
                if ($this->REQstep == 'save') {
                    if ($GLOBALS["SL"]->REQ->has('popStateUrl') 
                        && trim($GLOBALS["SL"]->REQ->popStateUrl) != '') {
                        $this->setNodeURL(str_replace($GLOBALS["SL"]->treeBaseSlug, '', 
                            $GLOBALS["SL"]->REQ->popStateUrl));
                        $this->pullNewNodeURL();
                    } else {
                        $redir1 = '';
                        if ($GLOBALS["SL"]->REQ->has('jumpTo')) {
                            $jump = trim($GLOBALS["SL"]->REQ->get('jumpTo'));
                            if ($jump != '') {
                                $redir1 = $jump;
                            }
                        }
                        if ($GLOBALS["SL"]->REQ->has('afterJumpTo')) {
                            $jump = trim($GLOBALS["SL"]->REQ->get('afterJumpTo'));
                            if ($jump != '') {
                                session()->put('redir2', $jump);
                            }
                        }
                        if ($redir1 != '') {
                            return $this->redir($redir1, true);
                        }
                    }
                } else {
                    $this->updateCurrNode($nodeIn);
                    $lastNode = $nodeIn;
                    // Now figure what comes next. 
                    if (!$this->isStepUpload()) { // if uploading, then don't change nodes yet
                        $jumpID = $this->jumpToNode($this->currNode());
                        $jumpArr = ['exitLoop', 'exitLoopBack', 'exitLoopJump'];
                        if (in_array($this->REQstep, $jumpArr) 
                            && trim($GLOBALS["SL"]->REQ->input('loop')) != '') {
                            $this->sessData->logDataSave(
                                $this->currNode(), 
                                $GLOBALS["SL"]->closestLoop["obj"]->DataLoopTable, 
                                $GLOBALS["SL"]->REQ->input('loopItem'), 
                                $this->REQstep, 
                                $GLOBALS["SL"]->REQ->input('loop')
                            );
                            $this->leavingTheLoop($GLOBALS["SL"]->REQ->input('loop'));
                            if ($this->REQstep == 'exitLoop') {
                                $next = $this->nextNodeSibling($this->currNode());
                                $this->updateCurrNodeNB($next);
                            } elseif ($this->REQstep == 'exitLoopBack') {
                                $prev = $this->prevNode($this->currNode());
                                $prev = $this->getNextNonBranch($prev, 'prev');
                                $this->updateCurrNodeNB($prev, 'prev');
                            } else {
                                $this->updateCurrNode($jumpID); // exit through jump
                            }
                        } elseif ($jumpID > 0) {
                            $this->updateCurrNode($jumpID);
                        } else { // no jumps, let's do the old back and forth...
                            if ($this->REQstep == 'back') {
                                $prev = $this->prevNode($this->currNode());
                                $prev = $this->getNextNonBranch($prev, 'prev');
                                $this->updateCurrNodeNB($prev, 'prev');
                            } else {
                                $next = $this->nextNode(
                                    $this->currNode(), 
                                    $this->currNodeSubTier
                                );
                                $this->updateCurrNodeNB($next);
                            }
                        }
                    }
                } // end REQstep == 'save' check
            }
        } elseif (trim($this->urlSlug) != '') {
            $this->pullNewNodeURL();
            if ($this->currNode() == $GLOBALS["SL"]->treeRow->TreeFirstPage 
                && $GLOBALS["SL"]->REQ->has('start') 
                && intVal($GLOBALS["SL"]->REQ->get('start')) == 1) {
                $this->runDataManip($GLOBALS["SL"]->treeRow->TreeRoot);
            }
        }
        
        if (!$this->isStepUpload()) {
            $this->updateCurrNodeNB($this->currNode());
            if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('step')) {
                $this->loadAllSessData();
                $this->checkLoopsPostProcessing($this->currNode(), $lastNode);
            } else {
                if (!$this->checkNodeConditions($this->currNode())) {
                    $next = $this->nextNode($this->currNode(), $this->currNodeSubTier);
                    $this->updateCurrNode($next);
                }
                $this->updateCurrNodeNB($this->currNode());
            }
            //$this->loadAllSessData();
        }
        /* if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('step')) {
            $newNodeURL = $this->currNodeURL();
            if ($newNodeURL != '') {
                echo '<script type="text/javascript"> window.location="' . $newNodeURL . '"; </script>';
                exit;
            }
        } */
        
        if (!$GLOBALS["SL"]->REQ->has('popStateUrl') 
            || trim($GLOBALS["SL"]->REQ->popStateUrl) == '') {
            $this->pushCurrNodeURL($this->currNode());
        }
        $this->multiRecordCheck();
        
        $this->loadAncestry($this->currNode());
        
        $this->v["nodeKidFunks"] = '';
        
        $goSkinny = (!$this->hasFrameLoad() 
            && $GLOBALS["SL"]->treeRow->TreeOpts%Globals::TREEOPT_SKINNY == 0);
        if ($goSkinny) {
            $ret .= '<center><div id="skinnySurv" class="treeWrapForm">';
        } elseif (!$this->isPage) {
            $ret .= '<div id="wideSurv" class="container">';
        }
        $ret .= ((trim($GLOBALS["errors"]) != '') ? $GLOBALS["errors"] : '') 
            . $this->nodeSessDump('pageStart')
            . $this->printNodePublic($this->currNode(), $this->currNodeSubTier) . "\n"
            . $this->loadProgBar() . "\n"
                // (($this->allNodes[$this->currNode()]->nodeOpts%29 > 0) ? $this->loadProgBar() : '') // not exit page?
            . $this->printCurrRecMgmt() . $this->sessDump($lastNode) . "\n";
        if ($goSkinny) {
            $ret .= '</div><center>';
        } elseif (!$this->isPage) {
            $ret .= '</div>';
        }
        if (isset($GLOBALS["SL"]->treeSettings["footer"]) 
            && isset($GLOBALS["SL"]->treeSettings["footer"][0]) 
            && trim($GLOBALS["SL"]->treeSettings["footer"][0]) != '') {
            $this->v["footOver"] = $GLOBALS["SL"]->treeSettings["footer"][0];
        }
        if (trim($this->v["nodeKidFunks"]) != '') {
            $GLOBALS["SL"]->pageAJAX .= 'function checkAllNodeKids() { ' 
                . $this->v["nodeKidFunks"] 
                /* . ' if (nodeList && nodeList.length > 0) { for (var i=0; i < nodeList.length; i++) { '
                . 'chkNodeParentVisib(nodeList[i]); } } ' */
                . ' setTimeout(function() { checkAllNodeKids(); }, 3000); }' // re-check every 3 seconds
                . ' setTimeout(function() { checkAllNodeKids(); }, 1);' . "\n";
        }
        if (!$this->isPage) {
            $ret .= '</div> <!-- end maincontentWrap -->';
        }
        if ($this->hasAjaxWrapPrinting()) {
            $ret .= '</div>';
        } else {
            $GLOBALS["SL"]->pageJAVA 
                .= 'if (document.getElementById("dynamicJS")) document.getElementById("dynamicJS").remove();';
            $GLOBALS["SL"]->pageAJAX 
                .= 'if (document.getElementById("maincontentWrap")) $("#maincontentWrap").fadeIn(50); ';
            $ret = $GLOBALS["SL"]->pullPageJsCss($ret, $this->coreID) 
                . $GLOBALS["SL"]->pageSCRIPTS;
            $GLOBALS["SL"]->pageSCRIPTS = '';
        }
        return $ret;
    }

    public function printTreeNodePublic($nID)
    {
        $ret = '';
        $this->loadTree();
        $this->updateCurrNode($nID);
        $this->loadAncestry($this->currNode());
        return $this->printNodePublic($this->currNode(), $this->currNodeSubTier);
    }
    
    // This function is the primary front-facing controller for the user experience
    public function index(Request $request, $type = '', $val = '')
    {
        $this->survLoopInit($request, '');
        $chk = $this->checkSystemInit();
        if ($chk && trim($chk) != '') {
            return $chk;
        }
        $this->checkPageViewPerms();
        if ($GLOBALS["SL"]->treeRow->TreeOpts%Globals::TREEOPT_REPORT == 0) {
            $this->fillGlossary(); // is report
        }
        //if ($this->v["uID"] > 0) $this->loadAllSessData();
        if ($type == 'ajaxChecks') {
            $this->runAjaxChecks($request);
            exit;
        }
        if (!isset($this->v["javaNodes"])) {
            $this->v["javaNodes"] = '';
        }
        $notes = '';
        if (isset($GLOBALS["SL"]->pageView) 
            && trim($GLOBALS["SL"]->pageView) != '') {
            $notes .= 'pv.' . $GLOBALS["SL"]->pageView 
                . ' dp.' . $GLOBALS["SL"]->dataPerms;
        }
        $this->v["content"] = $this->printTreePublic() 
            . (($notes != '') ? '<!-- ' . $notes . ' -->' : '');
        if ($this->v["currPage"][0] != '/') {
            $log = new SLUsersActivity;
            $log->UserActUser = $this->v["uID"];
            $log->UserActCurrPage = $this->v["currPage"][0] . $notes;
            $log->save();
        }
        $GLOBALS["SL"]->pageJAVA .= 'currTreeType = "' 
            . $GLOBALS["SL"]->treeRow->TreeType . '"; setCurrPage("'
            . $this->v["currPage"][1] . '", "' . $this->v["currPage"][0] 
            . '", ' . $this->currNode() . '); function loadPageNodes() { 
            if (typeof chkNodeVisib === "function") { ' . $this->v["javaNodes"] . ' } 
            else { setTimeout("loadPageNodes()", 500); } 
            return true; } setTimeout("loadPageNodes()", 100); ' . "\n";
        if ($request->has('ajax') && $request->ajax == 1) {
            // tree form ajax submission
            echo $this->ajaxContentWrapCustom($this->v["content"]);
            exit;
        }
        // Otherwise, Proceed Running Various Index Functions
        $this->v["currInReport"] = $this->currInReport();
        if ($type == 'testRun') {
            return $this->redir('/');
        }
        
        if ($GLOBALS["SL"]->treeRow->TreeOpts%31 == 0) { // search results page
            if ($GLOBALS["SL"]->REQ->has('s') 
                && trim($GLOBALS["SL"]->REQ->get('s')) != '') {
                if ($GLOBALS["SL"]->treeRow->TreeOpts%3 == 0
                    || $GLOBALS["SL"]->treeRow->TreeOpts%17 == 0 
                    || $GLOBALS["SL"]->treeRow->TreeOpts%41 == 0
                    || $GLOBALS["SL"]->treeRow->TreeOpts%43 == 0) {
                    $GLOBALS["SL"]->pageJAVA .= 'setTimeout(\'if (document.getElementById("admSrchFld")) '
                        . 'document.getElementById("admSrchFld").value=' 
                        . json_encode(trim($GLOBALS["SL"]->REQ->get('s')))
                        . '\', 10); ';
                } // else check for the main public search field? 
            }
        }
        $this->v["content"] 
            = $GLOBALS["SL"]->pullPageJsCss($this->v["content"], $this->coreID);
        if ($GLOBALS["SL"]->treeIsAdmin) {
            return $GLOBALS["SL"]->swapSessMsg($this->v["content"]);
        } else {
            return $GLOBALS["SL"]->swapSessMsg(
                view('vendor.survloop.master', $this->v)->render()
            );
        }
    }
    
    protected function ajaxContentWrapCustom($str, $nID = -3)
    {
        return $str;
    }
    
    protected function runCurrNode($nID)
    {
        //if ($nID == $GLOBALS["SL"]->treeRow->TreeRoot) $this->runDataManip($nID);
        return true;
    }
    
    protected function runDataManip($nID, $betweenPages = false)
    {
        if ($this->allNodes[$nID]->isDataManip()) {
            list($tbl, $fld, $newVal) = $this->allNodes[$nID]->getManipUpdate();
            if ($this->allNodes[$nID]->nodeType == 'Data Manip: New') {
                //$newObj = $this->checkNewDataRecord($tbl, $fld, $newVal);
                //if (!$newObj) {
                    $newObj = $this->sessData->newDataRecord($tbl, $fld, $newVal);
                    if ($newObj) {
                        $this->sessData->startTmpDataBranch($tbl, $newObj->getKey());
                        $this->sessData->currSessData($nID, $tbl, $fld, 'update', $newVal);
                        $manipUpdates = SLNode::where('NodeTree', $this->treeID)
                            ->where('NodeType', 'Data Manip: Update')
                            ->where('NodeParentID', $nID)
                            ->get();
                        if ($manipUpdates->isNotEmpty()) {
                            foreach ($manipUpdates as $nodeRow) {
                                $tmpNode = new TreeNodeSurv($nodeRow->NodeID, $nodeRow);
                                list($tbl, $fld, $newVal) = $tmpNode->getManipUpdate();
                                $this->sessData->currSessData(
                                    $nodeRow->NodeID, 
                                    $tbl, 
                                    $fld, 
                                    'update', 
                                    $newVal
                                );
                            }
                        }
                        if ($betweenPages) {
                            $this->sessData->endTmpDataBranch($tbl);
                        }
                    }
                //}
                //$this->loadAllSessData();
            } elseif ($this->allNodes[$nID]->nodeType == 'Data Manip: Update') {
                $this->sessData->currSessData($nID, $tbl, $fld, 'update', $newVal);
            }
        }
        return true;
    }
    
    protected function reverseDataManip($nID)
    {
        if ($this->allNodes[$nID]->isDataManip()) {
            list($tbl, $fld, $newVal) = $this->allNodes[$nID]->getManipUpdate();
            if ($this->allNodes[$nID]->nodeType == 'Data Manip: New') {
                $this->sessData->deleteDataRecord($tbl, $fld, $newVal);
                //$this->loadAllSessData();
            }
        }
        return true;
    }
    
    protected function nodeBranchInfo($nID, $curr = NULL)
    {
        $tbl = $fld = $newVal = '';
        if (!$curr) {
            $curr = $this->allNodes[$nID];
        }
        $types = [ 'Data Manip: New', 'Data Manip: Wrap' ];
        if (in_array($curr->nodeType, $types)) { // Data Manip: Update
            list($tbl, $fld, $newVal) = $curr->getManipUpdate();
            if ($curr->nodeType == 'Data Manip: Wrap') {
                $tbl = $curr->dataBranch;
            }
        }
        if ($curr->isLoopCycle()) {
            $loop = '';
            if (isset($curr->nodeRow->NodeResponseSet) 
                && strpos($curr->nodeRow->NodeResponseSet, 'LoopItems:') === 0) {
                $loop = trim(str_replace('LoopItems:', '', 
                    $curr->nodeRow->NodeResponseSet));
            }
            if ($loop != '' && isset($GLOBALS["SL"]->dataLoops[$loop]) 
                && isset($GLOBALS["SL"]->dataLoops[$loop]->DataLoopTable)) {
                $tbl = $GLOBALS["SL"]->dataLoops[$loop]->DataLoopTable;
            } elseif (isset($curr->dataBranch) && trim($curr->dataBranch) != '') {
                $tbl = $curr->dataBranch;
            }
        }
        return [$tbl, $fld, $newVal];
    }
    
    protected function loadManipBranch($nID, $force = false)
    {
        list($tbl, $fld, $newVal) = $this->nodeBranchInfo($nID);
        if ($tbl != '') {
            $manipBranchRow = $this->sessData->checkNewDataRecord($tbl, $fld, $newVal, []);
            if (!$manipBranchRow && $force) {
                $manipBranchRow = $this->sessData->newDataRecord($tbl, $fld, $newVal);
            }
            if ($manipBranchRow) {
                $this->sessData->startTmpDataBranch($tbl, $manipBranchRow->getKey());
            }
        }
        return true;
    }
    
    protected function closeManipBranch($nID)
    {
        list($tbl, $fld, $newVal) = $this->nodeBranchInfo($nID);
        if ($tbl != '') {
            $this->sessData->endTmpDataBranch($tbl);
        }
        return true;
    }
    
    protected function hasParentDataManip($nID)
    {
        $found = false;
        while ($this->hasNode($nID) && !$found) {
            if ($this->allNodes[$nID]->isDataManip()) {
                list($tbl, $fld, $newVal) = $this->allNodes[$nID]->getManipUpdate();
                if ($this->allNodes[$nID]->nodeType == 'Data Manip: New' 
                    && $fld != '' && $newVal != '') {
                    $found = true;
                }
            }
            $nID = $this->allNodes[$nID]->getParent();
        }
        return $found;
    }
    
    protected function newLoopItem($nID = -3)
    {
        if (intVal($this->newLoopItemID) <= 0) {
            $newID = $this->sessData->createNewDataLoopItem($nID);
            $loop = $GLOBALS["SL"]->REQ->input('loop');
            $this->afterCreateNewDataLoopItem($loop, $newID); //$loop->DataLoopPlural
            if ($newID > 0) {
                $GLOBALS["SL"]->REQ->loopItem = $newID;
                $this->settingTheLoop(trim($loop), intVal($newID));
            }
            $this->newLoopItemID = $nID;
        }
        return true;
    }
    
    protected function checkLoopsPostProcessing($newNode, $prevNode)
    {
        $currLoops = [];
        $backToRoot = false;
        if ($newNode <= 0) $newNode = $this->nextNode($prevNode);
        // First, are we leaving one of our current loops?..
        if (sizeof($GLOBALS["SL"]->sessLoops) > 0) {
            foreach ($GLOBALS["SL"]->sessLoops as $sessLoop) {
                if (isset($GLOBALS["SL"]->dataLoops[$sessLoop->SessLoopName])) {
                    $currLoops[$sessLoop->SessLoopName] = $sessLoop->SessLoopItemID;
                    $loop = $GLOBALS["SL"]->dataLoops[$sessLoop->SessLoopName];
                    if (isset($this->allNodes[$prevNode]) && isset($this->allNodes[$newNode])
                        && isset($this->allNodes[$loop->DataLoopRoot])
                        && $this->allNodes[$prevNode]->checkBranch(
                            $this->allNodes[$loop->DataLoopRoot]->nodeTierPath)
                        && !$this->allNodes[$newNode]->checkBranch(
                            $this->allNodes[$loop->DataLoopRoot]->nodeTierPath)) {
                        // Then we are now trying to leave this loop
                        if (in_array($this->REQstep, ['back', 'exitLoopBack'])) { 
                            // Then leaving the loop backwards, always allowed
                            $this->leavingTheLoop($loop->DataLoopPlural);
                        } elseif ($this->REQstep != 'save') { // Check for conditions before moving leaving forward
                            if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop()) {
                                if (sizeof($this->sessData->loopItemIDs[
                                    $loop->DataLoopPlural]) > 1) {
                                    $backToRoot = true;
                                }
                            } elseif (intVal($loop->DataLoopMaxLimit) == 0 
                                || sizeof($this->sessData->loopItemIDs[
                                    $loop->DataLoopPlural]) < $loop->DataLoopMaxLimit) {
                                // Then sure, we can add another item to this loop, back at the root node
                                $backToRoot = true;
                            }
                            if ($backToRoot) {
                                $this->updateCurrNode($loop->DataLoopRoot);
                                $this->leavingTheLoop($loop->DataLoopPlural, true);
                            } else { // OK, let's allow the user to keep going outside the loop
                                $this->sessInfo->SessLoopRootJustLeft = $loop->DataLoopRoot;
                                $this->sessInfo->save();
                                $this->leavingTheLoop($loop->DataLoopPlural);
                            }
                        }
                    } elseif ($newNode == $loop->DataLoopRoot) {
                        $loopCnt = sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]);
                        $skipRoot = false;
                        if ($this->allNodes[$newNode]->isStepLoop()) {
                            if ($loopCnt == 1 || ($loop->DataLoopMinLimit == 1 
                                && $loop->DataLoopMaxLimit == 1)) {
                                $skipRoot = true;
                            }
                        } elseif ($loop->DataLoopMinLimit > 0 && $loopCnt == 0) {
                            $skipRoot = true;
                        }
                        if ($skipRoot) {
                            $this->pushCurrNodeVisit($newNode);
                            if ($this->REQstep == 'back') {
                                $this->leavingTheLoop($loop->DataLoopPlural);
                                $prev = $this->getNextNonBranch(
                                    $this->prevNode($loop->DataLoopRoot), 
                                    'prev'
                                );
                                $this->updateCurrNodeNB($prev, 'prev');
                            } elseif ($this->REQstep != 'save') {
                                $this->updateCurrNodeNB($this->nextNode($loop->DataLoopRoot));
                            }
                        }
                    }
                }
            }
        }
        
        // If we haven't already tried to leave our loop, nor returned back to its root node...
        if (!$backToRoot && sizeof($GLOBALS["SL"]->dataLoops) > 0) {
            foreach ($GLOBALS["SL"]->dataLoops as $loop) {
                if (!isset($currLoops[$loop->DataLoopPlural]) 
                    && isset($this->allNodes[$loop->DataLoopRoot])) {
                    // Then this is a new loop we weren't previously in
                    $path = $this->allNodes[$loop->DataLoopRoot]->nodeTierPath;
                    if (isset($this->allNodes[$prevNode]) 
                        && !$this->allNodes[$prevNode]->checkBranch($path)
                        && $this->allNodes[$newNode]->checkBranch($path)) {
                        // Then we have just entered this loop from outside
                        if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop() 
                            && (!isset($this->sessData->loopItemIDs[$loop->DataLoopPlural]) 
                                || empty($this->sessData->loopItemIDs[$loop->DataLoopPlural]))) {
                            $this->leavingTheLoop($loop->DataLoopPlural);
                            if (isset($this->REQstep) 
                                && in_array($this->REQstep, ['back', 'exitLoopBack'])) {
                                $prevRoot = $this->getNextNonBranch(
                                    $this->prevNode($loop->DataLoopRoot), 
                                    'prev'
                                );
                                $this->updateCurrNodeNB($prevRoot);
                            } elseif (!isset($this->REQstep) || $this->REQstep != 'save') {
                                $this->updateCurrNodeNB($this->nextNodeSibling($newNode));
                            }
                        } else { // This loop is active
                            $loopCnt = sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]);
                            $skipRoot = false;
                            if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop()) {
                                if ($loopCnt == 1 || ($loop->DataLoopMinLimit == 1 
                                    && $loop->DataLoopMaxLimit == 1)) {
                                    $skipRoot = true;
                                }
                            } elseif ($loop->DataLoopMinLimit > 0 && $loopCnt == 0) {
                                $skipRoot = true;
                            }
                            $this->settingTheLoop($loop->DataLoopPlural);
                            if ($newNode == $loop->DataLoopRoot) {
                                // Then we landed directly on the loop's root node from outside, 
                                // so we must be going forward not back
                                if ($skipRoot) {
                                    $this->pushCurrNodeVisit($newNode);
                                    $itemID = -3;
                                    if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop()) {
                                        $itemID = $this->sessData->loopItemIDs[
                                            $loop->DataLoopPlural][0];
                                    } elseif ($loop->DataLoopAutoGen == 1) {
                                        $itemID = $this->sessData->createNewDataLoopItem($loop->DataLoopRoot);
                                        $this->afterCreateNewDataLoopItem($loop->DataLoopPlural, $itemID);
                                    }
                                    $GLOBALS["SL"]->REQ->loop = $loop->DataLoopPlural;
                                    $GLOBALS["SL"]->REQ->loopItem = $itemID;
                                    $this->settingTheLoop($loop->DataLoopPlural, $itemID);
                                    $this->updateCurrNodeNB($this->nextNode($loop->DataLoopRoot));
                                    $GLOBALS["SL"]->loadSessLoops($this->sessID);
                                }
                            } else {
                                // Must have landed at the loop's end node from outside, so we going back not forward
                                if ($skipRoot) {
                                    $this->pushCurrNodeVisit($newNode);
                                    if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop()) {
                                        $this->settingTheLoop($loop->DataLoopPlural, 
                                            $this->sessData->loopItemIDs[
                                                $loop->DataLoopPlural][0]);
                                    }
                                } else {
                                    $this->updateCurrNode($loop->DataLoopRoot);
                                }
                            }
                        }
                    }
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
    
    public function runAjaxChecksCustom(Request $request, $over = '')
    {
        return false;
    }
    
    public function runAjaxChecks(Request $request, $over = '')
    {
        $this->runAjaxChecksCustom($request, $over);
        if ($request->has('email') && $request->has('password')) {
            print_r($request);
            $chk = User::where('email', $request->email)
                ->get();
            if ($chk->isNotEmpty()) {
                echo 'found';
            }
            echo 'not found';
            exit;
        }
    }
    
    public function loadNodeURL(Request $request, $nodeSlug = '')
    {
        if (trim($nodeSlug) != '') {
            $this->setNodeURL($nodeSlug);
        }
        return $this->index($request);
    }
    
    public function testRun(Request $request)
    {
        return $this->index($request, 'testRun');
    }
    
    public function ajaxChecks(Request $request, $type = '')
    {
        $this->survLoopInit($request, '/ajax/' . $type);
        $ret = $this->ajaxChecksCustom($request, $type);
        if (trim($ret) != '') {
            return $ret;
        }
        $ret = $this->ajaxChecksSL($request, $type);
        if (trim($ret) != '') {
            return $ret;
        }
        return $this->index($request, 'ajaxChecks');
    }
    
    public function ajaxChecksSL(Request $request, $type = '')
    {
        $this->survLoopInit($request, '/ajadm/' . $type);
        $nID = (($request->has('nID')) ? trim($request->get('nID')) : '');
        if ($type == 'color-pick') {
            return $this->ajaxColorPicker($request);
        } elseif (substr($type, 0, 4) == 'img-') {
            $imgID = (($request->has('imgID')) ? trim($request->get('imgID')) : '');
            $presel = (($request->has('presel')) ? trim($request->get('presel')) : '');
            if ($type == 'img-sel') {
                $newUp = (($request->has('newUp')) ? trim($request->get('newUp')) : '');
                return $GLOBALS["SL"]->getImgSelect($nID, $GLOBALS["SL"]->dbID, $presel, $newUp);
            } elseif ($type == 'img-deet') {
                return $GLOBALS["SL"]->getImgDeet($imgID, $nID);
            } elseif ($type == 'img-save') {
                return $GLOBALS["SL"]->saveImgDeet($imgID, $nID);
            } elseif ($type == 'img-up') {
                return $GLOBALS["SL"]->uploadImg($nID, $presel);
            }
        } elseif ($type == 'log-pro-tip') {
            $this->ajaxLogLastProTip($request);
        }
        return '';
    }
    
    public function ajaxChecksCustom(Request $request, $type = '')
    {
        return '';
    }
    
    protected function ajaxColorPicker(Request $request)
    {
        $fldName = (($request->has('fldName')) 
            ? trim($request->get('fldName')) : '');
        $preSel = (($request->has('preSel')) 
            ? '#' . trim($request->get('preSel')) : '');
        if (trim($fldName) != '') {
            $sysColors = [];
            $sysStyles = SLDefinitions::where('DefDatabase', 1)
                ->where('DefSet', 'Style Settings')
                ->orderBy('DefOrder')
                ->get();
            $isCustom = true;
            if ($sysStyles->isNotEmpty()) {
                foreach ($sysStyles as $i => $sty) {
                    if (strpos($sty->DefSubset, 'color-') !== false 
                        && !in_array($sty->DefDescription, $sysColors)) {
                        $sysColors[] = $sty->DefDescription;
                        if ($sty->DefDescription == $preSel) {
                            $isCustom = false;
                        }
                    }
                }
            }
            return view('vendor.survloop.forms.inc-color-picker-ajax', [
                "sysColors" => $sysColors,
                "fldName"   => $fldName,
                "preSel"    => $preSel,
                "isCustom"  => $isCustom
                ]);
        }
        return '';
    }
    
    protected function ajaxLogLastProTip(Request $request)
    {
        if ($request->has('tree') && intVal($request->get('tree')) > 0 
            && $request->has('tip') && intVal($request->get('tip')) > 0) {
            $tok = $this->getProTipToken();
            $tok->TokTokToken = intVal($request->get('tip'));
            $tok->save();
        }
        exit;
    }
    
    protected function getProTipToken()
    {
        $tok = SLTokens::where('TokType', 'ProTip')
            ->where('TokUserID', $this->v["uID"])
            ->where('TokTreeID', $this->treeID)
            ->first();
        if (!$tok) {
            $tok = new SLTokens;
            $tok->TokType     = 'ProTip';
            $tok->TokUserID   = $this->v["uID"];
            $tok->TokTreeID   = $this->treeID;
            $tok->TokTokToken = 0;
            $tok->save();
        }
        return $tok;
    }
    
    protected function loadTreeProTip()
    {
        $tok = $this->getProTipToken();
        $GLOBALS["SL"]->currProTip = $tok->TokTokToken;
        $GLOBALS["SL"]->pageJAVA .= ' lastProTip = ' . $tok->TokTokToken . '; ';
        return true;
    }
    
    public function byID(Request $request, $coreID, $coreSlug = '', $skipWrap = false, $skipPublic = false)
    {
        $this->survLoopInit($request, '/report/' . $coreID);
        if (!$skipPublic) {
            $coreID = $GLOBALS["SL"]->chkInPublicID($coreID);
        }
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        if ($request->has('hideDisclaim') && intVal($request->hideDisclaim) == 1) {
            $this->hideDisclaim = true;
        }
        $this->v["isPublicRead"] = true;
        $this->v["content"] = $this->printFullReport();
        if ($skipWrap) {
            return $this->v["content"];
        }
        $this->v["footOver"] = $this->printNodePageFoot();
        return $GLOBALS["SL"]->swapSessMsg(
            view('vendor.survloop.master', $this->v)->render()
        );
    }
    
    public function printReports(Request $request, $full = true)
    {
        $this->survLoopInit($request, '/reports-full/' . $this->treeID);
        $this->loadTree();
        $ret = '';
        if ($request->has('i') && intVal($request->get('i')) > 0) {
            $ret .= $this->printReportsRecordPublic($request->get('i'), $full);
        } elseif ($request->has('ids') && trim($request->get('ids')) != '') {
            foreach ($GLOBALS["SL"]->mexplode(',', $request->get('ids')) as $id) {
                $ret .= $this->printReportsRecordPublic($id, $full);
            }
        } elseif ($request->has('rawids') && trim($request->get('rawids')) != '') {
            foreach ($GLOBALS["SL"]->mexplode(',', $request->get('rawids')) as $id) {
                $ret .= $this->printReportsRecord($id, $full);
            }
        } else {
            $this->getAllPublicCoreIDs();
            $this->initSearcher();
            $this->searcher->getSearchFilts();
            $this->searcher->processSearchFilts();
            if (sizeof($this->searcher->allPublicFiltIDs) > 0) {
                foreach ($this->searcher->allPublicFiltIDs as $i => $coreID) {
                    if (!isset($this->searchOpts["limit"]) 
                        || intVal($this->searchOpts["limit"]) == 0
                        || $i < $this->searchOpts["limit"]) {
                        if ($GLOBALS["SL"]->tblHasPublicID($GLOBALS["SL"]->coreTbl)) {
                            $ret .= $this->printReportsRecordPublic($coreID, $full);
                        } else {
                            $ret .= $this->printReportsRecord($coreID, $full);
                        }
                    }
                }
            }
        }
        if ($ret == '') {
            $ret = '<p><i class="slGrey">None found.</i></p>';
        }
        return $ret;
    }
    
    public function printReportsRecord($coreID = -3, $full = true)
    {
        if (!$this->isPublished($GLOBALS["SL"]->coreTbl, $coreID) 
            && !$this->isCoreOwner($coreID) && (!$this->v["user"] 
                || !$this->v["user"]->hasRole('administrator|staff'))) {
            return $this->unpublishedMessage($GLOBALS["SL"]->coreTbl);
        }
        if ($full) {
            return '<div class="reportWrap">' 
                . $this->byID($GLOBALS["SL"]->REQ, $coreID, '', true, true)
                . '</div>';
        }
        return $this->printReportsPrev($coreID);
    }
    
    public function printReportsRecordPublic($coreID = -3, $full = true)
    {
        if (!$this->isPublishedPublic($GLOBALS["SL"]->coreTbl, $coreID) 
            && !$this->isCoreOwnerPublic($coreID) && (!$this->v["user"] 
                || !$this->v["user"]->hasRole('administrator|staff'))) {
            return $this->unpublishedMessage($GLOBALS["SL"]->coreTbl);
        }
        if ($full) {
            return '<div class="reportWrap">' 
                . $this->byID($GLOBALS["SL"]->REQ, $coreID, '', true) 
                . '</div>';
        }
        return $this->printReportsPrev($coreID);
    }
    
    public function printReportsPrev($coreID = -3)
    {
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        return '<div id="reportPreview' . $coreID . '" class="reportPreview">' 
            . $this->printPreviewReport() . '</div>';
    }
    
    public function unpublishedMessage($coreTbl = '')
    {
        if ($this->corePublicID <= 0) {
            return '<!-- -->';
        }
        return '<div class="well well-lg">#' . $this->corePublicID 
            . ' is no longer published.</div>';
    }
    
    public function xmlAllAccess()
    {
        return true; 
    }
    
    public function xmlAll(Request $request)
    {
        $page = '/' . $GLOBALS["SL"]->treeRow->TreeSlug . '-xml-all';
        $this->survLoopInit($request, $page);
        if (!$this->xmlAllAccess()) {
            return 'Sorry, access not permitted.';
        }
        $this->loadXmlMapTree($request);
        $this->v["nestedNodes"] = '';
        $coreTbl = $GLOBALS["SL"]->xmlTree["coreTbl"];
        $this->getAllPublicCoreIDs($coreTbl);
        if (sizeof($this->allPublicCoreIDs) > 0) {
            foreach ($this->allPublicCoreIDs as $coreID) {
                $this->loadAllSessData($coreTbl, $coreID);
                if (isset($this->sessData->dataSets[$coreTbl]) 
                    && sizeof($this->sessData->dataSets[$coreTbl]) > 0) {
                    $this->v["nestedNodes"] .= $this->genXmlReportNode(
                        $this->xmlMapTree->rootID, 
                        $this->xmlMapTree->nodeTiers, 
                        $this->sessData->dataSets[$coreTbl][0]
                    );
                }
            }
        }
        $view = view('vendor.survloop.admin.tree.xml-report', $this->v)->render();
        return Response::make($view, '200')->header('Content-Type', 'text/xml');
    }
    
    public function xmlByID(Request $request, $coreID, $coreSlug = '')
    {
        $page = '/' . $GLOBALS["SL"]->treeRow->TreeSlug . '-report-xml/' . $coreID;
        $this->survLoopInit($request, $page);
        $GLOBALS["SL"]->pageView = 'public';
        $coreID = $GLOBALS["SL"]->chkInPublicID($coreID);
        $this->loadXmlMapTree($request);
        if ($GLOBALS["SL"]->xmlTree["coreTbl"] == $GLOBALS["SL"]->coreTbl) {
            $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        } else { // XML core table is different from main tree
            $this->loadSessInfo($GLOBALS["SL"]->xmlTree["coreTbl"]);
            $this->loadAllSessData($GLOBALS["SL"]->xmlTree["coreTbl"], $coreID);
        }
        return $this->getXmlID($request, $coreID, $coreSlug);
    }
    
    public function getXmlID(Request $request, $coreID, $coreSlug = '')
    {
        $this->maxUserView();
        $this->xmlMapTree->v["view"] = $GLOBALS["SL"]->pageView;
        if (isset($GLOBALS["fullAccess"]) && $GLOBALS["fullAccess"] 
            && $GLOBALS["SL"]->pageView != 'full') {
            $this->v["content"] = $this->errorDeniedFullXml();
            return view('vendor.survloop.master', $this->v);
        }
        return $this->genXmlReport($request);
    }
    
    public function getXmlExample(Request $request)
    {
        $page = '/' . $GLOBALS["SL"]->treeRow->TreeSlug . '-xml-example';
        $this->survLoopInit($request, $page);
        $coreID = 1;
        $optXmlTree = "tree-" . $GLOBALS["SL"]->xmlTree["id"] . "-example";
        $optTree = "tree-" . $GLOBALS["SL"]->treeID . "-example";
        if (isset($GLOBALS["SL"]->sysOpts[$optXmlTree])) {
            $coreID = intVal($GLOBALS["SL"]->sysOpts[$optXmlTree]);
        } elseif (isset($GLOBALS["SL"]->sysOpts[$optTree])) {
            $coreID = intVal($GLOBALS["SL"]->sysOpts[$optTree]);
        }
        eval("\$chk = " 
            . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->xmlTree["coreTbl"]) 
            . "::find(" . $coreID . ");");
        if ($chk) {
            return $this->xmlByID($request, $coreID);
        }
        return $this->redir('/xml-schema');
    }
    
    protected function reloadStats($coreIDs = [])
    {
        return true;
    }
    
    public function retrieveUpload(Request $request, $cid = -3, $upID = '')
    {
        if ($cid <= 0) {
            return '';
        }
        $this->survLoopInit($request, '');
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $cid);
        $GLOBALS["SL"]->pageView = 'sensitive';
        return $this->retrieveUploadFile($upID);
    }
    
    public function ajaxGraph(Request $request, $gType = '', $nID = -3)
    {
        $this->survLoopInit($request, '');
        $this->v["currNode"] = new TreeNodeSurv;
        $this->v["currNode"]->fillNodeRow($nID);
        $this->v["currGraphID"] = 'nGraph' . $nID;
        if ($this->v["currNode"] && trim($gType) != ''
            && isset($this->v["currNode"]->nodeRow->NodeID)) {
            $this->getAllPublicCoreIDs();
            $this->searcher->getSearchFilts();
            $this->searcher->processSearchFilts();
            $this->v["graphDataPts"] = $this->v["graphMath"] 
                = $rows = $rowsFilt = [];
            if (sizeof($this->searcher->allPublicFiltIDs) > 0) {
                if (isset($this->v["currNode"]->extraOpts["y-axis"]) 
                    && intVal($this->v["currNode"]->extraOpts["y-axis"]) > 0) {
                    $fldRec = SLFields::find($this->v["currNode"]->extraOpts["y-axis"]);
                    $lab1Rec = SLFields::find($this->v["currNode"]->extraOpts["lab1"]);
                    $lab2Rec = SLFields::find($this->v["currNode"]->extraOpts["lab2"]);
                    if ($fldRec && isset($fldRec->FldTable)) {
                        $tbl = $GLOBALS["SL"]->tbl[$fldRec->FldTable];
                        $tblAbbr = $GLOBALS["SL"]->tblAbbr[$tbl];
                        $fldName = $tblAbbr . $fldRec->FldName;
                        $lab1Fld = (($lab1Rec && isset($lab1Rec->FldName)) 
                            ? $tblAbbr . $lab1Rec->FldName : '');
                        $lab2Fld = (($lab2Rec && isset($lab2Rec->FldName)) 
                            ? $tblAbbr . $lab2Rec->FldName : '');
                        if ($tbl == $GLOBALS["SL"]->coreTbl) {
                            eval("\$rows = " . $GLOBALS["SL"]->modelPath($tbl) 
                                . "::select('" . $tblAbbr . "ID', '" . $fldName . "'" 
                                . ((trim($lab1Fld) != '') ? ", '" . $lab1Fld . "'" : "") 
                                . ((trim($lab2Fld) != '') ? ", '" . $lab2Fld . "'" : "")
                                . ")->where('" . $fldName . "', 'NOT LIKE', '')->where('"
                                . $fldName . "', 'NOT LIKE', 0)->whereIn('" . $tblAbbr 
                                . "ID', \$this->searcher->allPublicFiltIDs)->orderBy('" 
                                . $fldName . "', 'asc')->get();");
                        } else {
                            //eval("\$rows = " . $GLOBALS["SL"]->modelPath($tbl) . "::orderBy('" . $isBigSurvLoop[1] 
                            //    . "', '" . $isBigSurvLoop[2] . "')->get();");
                        }
                        if ($rows->isNotEmpty()) {
                            if (isset($this->v["currNode"]->extraOpts["conds"]) 
                                && strpos('#', $this->v["currNode"]->extraOpts["conds"]) 
                                    !== false) {
                                $this->loadCustLoop($request);
                                foreach ($rows as $i => $row) {
                                    $this->custReport->loadAllSessData(
                                        $GLOBALS["SL"]->coreTbl, 
                                        $row->getKey()
                                    );
                                    if ($this->custReport->chkConds(
                                        $this->v["currNode"]->extraOpts["conds"])) {
                                        $rowsFilt[] = $row;
                                    }
                                }
                            } else {
                                $rowsFilt = $rows;
                            }
                        }
                        if (sizeof($rowsFilt) > 0) {
                            if ($this->v["currNode"]->nodeType == 'Bar Graph') {
                                $this->v["graphMath"]["absMin"] = $rows[0]->{ $fldName };
                                $this->v["graphMath"]["absMax"] = $rows[sizeof($rows)-1]->{ $fldName };
                                $this->v["graphMath"]["absRange"] 
                                    = $this->v["graphMath"]["absMax"]
                                        -$this->v["graphMath"]["absMin"];
                                foreach ($rows as $i => $row) {
                                    $lab = '';
                                    if (trim($lab1Fld) != '' 
                                        && isset($row->{ $lab1Fld })) { 
                                        $lab .= (($lab1Rec->FldType == 'DOUBLE') 
                                            ? $GLOBALS["SL"]->sigFigs($row->{ $lab1Fld }) 
                                            : $row->{ $lab1Fld }) . ' ';
                                        if (trim($lab2Fld) != '' 
                                            && isset($row->{ $lab2Fld })) { 
                                            $lab .= (($lab2Rec->FldType == 'DOUBLE') 
                                               ? $GLOBALS["SL"]->sigFigs($row->{ $lab2Fld }) 
                                               : $row->{ $lab2Fld }) .' ';
                                        }
                                    }
                                    $perc = ((1+$i)/sizeof($rows));
                                    $this->v["graphDataPts"][] = [
                                        "id"  => $row->getKey(),
                                        "val" => (($fldRec->FldType == 'DOUBLE') 
                                            ? $GLOBALS["SL"]->sigFigs($row->{ $fldName }, 4) 
                                            : $row->{ $fldName }), 
                                        "lab" => trim($lab),
                                        "dsc" => '',
                                        "bg"  => $GLOBALS["SL"]->printColorFade( $perc, 
                                            $this->v["currNode"]->extraOpts["clr1"], 
                                            $this->v["currNode"]->extraOpts["clr2"], 
                                            $this->v["currNode"]->extraOpts["opc1"], 
                                            $this->v["currNode"]->extraOpts["opc2"] ), 
                                        "brd" => $GLOBALS["SL"]->printColorFade( $perc, 
                                            $this->v["currNode"]->extraOpts["clr1"], 
                                            $this->v["currNode"]->extraOpts["clr2"] )
                                        ];
                                }
                            }
                        }
                    }
                }
                $this->v["graph"] = [ "dat" => '', "lab" => '', "bg" => '', "brd" => '' ];
                if (sizeof($this->v["graphDataPts"]) > 0) {
                    foreach ($this->v["graphDataPts"] as $cnt => $dat) {
                        $cma = (($cnt > 0) ? ", " : "");
                        $this->v["graph"]["dat"] .= $cma . $dat["val"];
                        $this->v["graph"]["lab"] .= $cma . "\"" . $dat["lab"] . "\"";
                        $this->v["graph"]["bg"]  .= $cma . "\"" . $dat["bg"]  . "\"";
                        $this->v["graph"]["brd"] .= $cma . "\"" . $dat["brd"] . "\"";
                    }
                }
                return view('vendor.survloop.reports.graph-bar', $this->v);
            }
        }
        $this->v["graphFail"] = true;
        return view('vendor.survloop.reports.graph-bar', $this->v);
    }
    
    protected function hasAjaxWrapPrinting()
    {
        return (!$this->hasREQ && (!$GLOBALS["SL"]->REQ->has('ajax') 
            || intVal($GLOBALS["SL"]->REQ->get('ajax')) == 0));
    }
    
    protected function hasFrameLoad()
    {
        return ($GLOBALS["SL"]->REQ->has('frame') 
            && intVal($GLOBALS["SL"]->REQ->get('frame')) == 1);
    }
    
    protected function changeNodeID($nID, $newID)
    {
        
    }
    
    protected function errorDeniedFullPdf()
    {
        $url = $GLOBALS["SL"]->treeRow->TreeSlug . '/read-' . $this->coreID;
        return '<br /><br /><center><h3>You are trying to access the complete details of a record which '
            . 'requires you to <a href="/login">login</a> as the owner, or an otherwise authorized user. '
            . '<br /><br />The public version of this complaint can be found here:<br />'
            . '<a href="/' . $url . '">' . $GLOBALS["SL"]->sysOpts["app-url"] 
            . '/' . $url . '</a></h3></center>';
    }
    
    protected function errorDeniedFullXml()
    {
        return $this->errorDeniedFullPdf();
    }
    
    
}
