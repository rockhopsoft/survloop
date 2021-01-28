<?php
/**
  * TreeSurv is a mid-level class using a standard branching tree, mostly for Survloop's surveys and pages.
  * But it does house some of the core functions to print the whole of a tree.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\User;
use App\Models\SLSess;
use App\Models\SLSessLoops;
use App\Models\SLDefinitions;
use App\Models\SLNode;
use App\Models\SLNodeSaves;
use App\Models\SLNodeSavesPage;
use App\Models\SLFields;
use App\Models\SLTokens;
use App\Models\SLUsersActivity;
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvLoops;

class TreeSurv extends TreeSurvLoops
{
    /**
     * Top-level of the algorithm which traverses the branching tree
     * which defines the generation of page.
     *
     * @return string
     */
    public function printTreePublic()
    {
        $ret = '';
        $GLOBALS["SL"]->microLog('Start printTreePublic(');
        $this->loadTree();
        $GLOBALS["SL"]->microLog('printTreePublic( after loadTree()');
        if ($GLOBALS["SL"]->treeRow->tree_type == 'Survey' && $this->coreID <= 0) {
            return $this->redir($GLOBALS["SL"]->getCurrTreeUrl(), true);
        }
        $GLOBALS["SL"]->pageJAVA .= view(
            'vendor.survloop.js.inc-check-tree-load', 
            [ "treeID" => $this->treeID ]
        )->render();
        if ($this->hasAjaxWrapPrinting()) {
            $ret .= '<div class="nodeAnchor">'
                . '<a name="maincontent" id="maincontent"></a>'
                . '</div><div id="ajaxWrap">';
        }
        if (!$this->isPage) {
            $ret .= '<div id="maincontentWrap" style="display: none;">' . "\n";
        }
        if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('node')) {
            $nodeIn = intVal($GLOBALS["SL"]->REQ->input('node'));
            if ($nodeIn > 0) {
                $this->updateCurrNode($nodeIn);
            }
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
            session()->save();
        }
        if (!isset($GLOBALS["SL"]->treeRow->tree_root)) {
            $GLOBALS["SL"]->loadGlobalTables($this->dbID, $this->treeID, $this->treeID);
        }
        if ($this->currNode() < 0 || !isset($this->allNodes[$this->currNode()])) {
            $this->updateCurrNode($GLOBALS["SL"]->treeRow->tree_root);
            //return '<h1>Sorry, Page Not Found.</h1>';
        }
        // double-check we haven't landed on a mid-page node
        if (isset($this->allNodes[$this->currNode()]) 
            && !$this->allNodes[$this->currNode()]->isPage() 
            && !$this->allNodes[$this->currNode()]->isLoopRoot()) {
            $this->updateCurrNode($this->allNodes[$this->currNode()]->getParent());
        }
        $GLOBALS["SL"]->microLog('printTreePublic( after redirect checks');

        $this->loadAncestry($this->currNode());
        $GLOBALS["SL"]->microLog('printTreePublic( after loadAncestry(');
        
        if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('step')) {
            $GLOBALS["SL"]->microLog('printTreePublic( start has posted step');
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
            $GLOBALS["SL"]->microLog('printTreePublic( before postNodePublic');
            $ret .= $this->postNodePublic($nodeIn);
            $GLOBALS["SL"]->microLog('printTreePublic( after postNodePublic');
            if ($this->REQstep == 'autoSave') {
                return 'Saved!-)';
            }
            //$this->loadAllSessData(); 
            // refresh should not bedefault, run manually where needed
            $GLOBALS["SL"]->microLog('printTreePublic( before post-step-redirect');
            if (!$this->isPage) {
                if ($this->REQstep == 'save') {
                    if ($GLOBALS["SL"]->REQ->has('popStateUrl') 
                        && trim($GLOBALS["SL"]->REQ->popStateUrl) != '') {
                        $url = $GLOBALS["SL"]->REQ->popStateUrl;
                        $url = str_replace($GLOBALS["SL"]->treeBaseSlug, '', $url);
                        $this->setNodeURL($url);
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
                                session()->save();
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
                                $GLOBALS["SL"]->closestLoop["obj"]->data_loop_table, 
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
                                $next = $this->nextNode($this->currNode(), $this->currNodeSubTier);
                                $this->updateCurrNodeNB($next);
                            }
                        }
                    }
                } // end REQstep == 'save' check
            }
            $GLOBALS["SL"]->microLog('printTreePublic( end has posted step');
        } else {
            if (trim($this->urlSlug) != '') {
                $this->pullNewNodeURL();
                if ($this->currNode() == $GLOBALS["SL"]->treeRow->tree_first_page 
                    && $GLOBALS["SL"]->REQ->has('start') 
                    && intVal($GLOBALS["SL"]->REQ->get('start')) == 1) {
                    $this->runDataManip($GLOBALS["SL"]->treeRow->tree_root);
                }
            }
            $this->checkLoopsLeft($this->currNode());
        }
        
        $GLOBALS["SL"]->microLog('printTreePublic( start moving currNode');
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
        $GLOBALS["SL"]->microLog('printTreePublic( end moving currNode');
        
        if (!$GLOBALS["SL"]->REQ->has('popStateUrl') 
            || trim($GLOBALS["SL"]->REQ->popStateUrl) == '') {
            $this->pushCurrNodeURL($this->currNode());
        }
        $this->multiRecordCheck();
        $GLOBALS["SL"]->microLog('printTreePublic( after multiRecordCheck(');
        
        $this->loadAncestry($this->currNode());
        $GLOBALS["SL"]->microLog('printTreePublic( after loadAncestry(');
        
        $this->v["nodeKidFunks"] = '';
        
        $fadeIn = ($GLOBALS['SL']->treeRow->tree_opts%Globals::TREEOPT_FADEIN == 0);
        if ($GLOBALS["SL"]->isPrintView()) {
            $fadeIn = false;
        }
        if ($fadeIn) {
            $GLOBALS["SL"]->setTreePageFadeIn($this->setTreePageFadeInDelay());
        }
        $ret .= '<div id="pageAnimWrap' . $GLOBALS['SL']->treeID 
            . '" class="w100" style="display: ' . (($fadeIn) ? 'none' : 'block') 
            . ';">';
        $goSkinny = ($GLOBALS["SL"]->treeRow->tree_opts%Globals::TREEOPT_SKINNY == 0);
        $goSkinny = (!$this->hasFrameLoad() && $goSkinny);
        if ($goSkinny) {
            $ret .= '<center><div id="skinnySurv" class="treeWrapForm">';
        } elseif (!$this->isPage) {
            $ret .= '<div id="wideSurv" class="container">';
        }
        $GLOBALS["SL"]->microLog('printTreePublic( before printNodePublic(');
        $ret .= ((trim($GLOBALS["errors"]) != '') ? $GLOBALS["errors"] : '') 
            . $this->nodeSessDump('pageStart')
            . $this->printNodePublic($this->currNode(), $this->currNodeSubTier) . "\n"
            . $this->loadProgBar() . "\n"
                // (($this->allNodes[$this->currNode()]->nodeOpts%29 > 0) ? $this->loadProgBar() : '') // not exit page?
            . $this->printCurrRecMgmt() . $this->sessDump($lastNode) . "\n";
        $GLOBALS["SL"]->microLog('printTreePublic( after printNodePublic(');
        if ($goSkinny) {
            $ret .= '</div><center> <!-- end skinnySurv -->';
        } elseif (!$this->isPage) {
            $ret .= '</div> <!-- end wideSurv -->';
        }
        $ret .= '</div> <!-- end pageAnimWrap -->';
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
            $ret .= '</div> <!-- end maincontentWrap --> ';
        }
        if ($this->hasAjaxWrapPrinting()) {
            $ret .= '</div> <!-- end ajaxWrap --> ';
        } else {
            $GLOBALS["SL"]->pageJAVA 
                .= 'if (document.getElementById("dynamicJS")) document.getElementById("dynamicJS").remove();';
            $GLOBALS["SL"]->pageAJAX 
                .= 'if (document.getElementById("maincontentWrap")) '
                    . '$("#maincontentWrap").fadeIn(50); ';
            $ret = $GLOBALS["SL"]->pullPageJsCss($ret, $this->coreID) 
                . $GLOBALS["SL"]->pageSCRIPTS;
            $GLOBALS["SL"]->pageSCRIPTS = '';
        }
        $GLOBALS["SL"]->microLog('printTreePublic( end');
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
    
    /**
     * This function is the primary front-facing 
     * controller for the user experience.
     *
     * @return string
     */
    public function index(Request $request, $type = '', $val = '')
    {
        $GLOBALS["SL"]->microLog('TreeSurv index(' . $type);
        $this->survloopInit($request, '');
        $GLOBALS["SL"]->microLog('TreeSurv index( after survLoopInit');
        $chk = $this->checkSystemInit();
        if ($chk && trim($chk) != '') {
            return $chk;
        }
        $notes = $this->indexChecks($request, $type);
        $this->v["content"] = $this->printTreePublic();
        if ($notes != '') {
            $this->v["content"] .= '<!-- ' . $notes . ' -->';
        }
        $this->loadTreePageJava();
        if ($request->has('ajax') && $request->ajax == 1) {
            // tree form ajax submission
            return $this->ajaxContentWrapCustom($this->v["content"]);
        }
        // Otherwise, Proceed Running Various Index Functions
        $this->v["currInReport"] = $this->currInReport();
        if ($type == 'testRun') {
            return $this->redir('/');
        }
        $this->v["content"] = $GLOBALS["SL"]->pullPageJsCss(
            $this->v["content"], 
            $this->coreID
        );
        return $this->indexResponse();
    }
    
    /**
     * Check, initialize, and log data needed to generate page.
     *
     * @return string
     */
    public function indexChecks(Request $request, $type = '')
    {
        $this->checkPageViewPerms();
        $notes = '';
        if (isset($GLOBALS["SL"]->pageView) 
            && trim($GLOBALS["SL"]->pageView) != '') {
            $notes .= 'pv.' . $GLOBALS["SL"]->pageView 
                . ' dp.' . $GLOBALS["SL"]->dataPerms;
        }
        if ($GLOBALS["SL"]->treeRow->tree_opts%Globals::TREEOPT_REPORT == 0) {
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
        if ($this->v["currPage"][0] != '/' && isset($this->v["uID"])) {
            $log = new SLUsersActivity;
            $log->user_act_user = $this->v["uID"];
            $log->user_act_curr_page = $this->v["currPage"][0] . ' ' . $notes;
            $log->save();
        }
        return $notes;
    }
    
    /**
     * Determine final formatting options on the generated page.
     *
     * @return string
     */
    public function indexResponse()
    {
        if ($GLOBALS["SL"]->isPdfView()) {
            return $this->v["content"];
        }
        if ($GLOBALS["SL"]->treeIsAdmin) {
            return $GLOBALS["SL"]->swapSessMsg($this->v["content"]);
        }
        return $GLOBALS["SL"]->swapSessMsg(
            view('vendor.survloop.master', $this->v)->render()
        );
    }
    
    /**
     * Load the current tree and page in the Javascript load.
     *
     * @return boolean
     */
    protected function loadTreePageJava()
    {
        $GLOBALS["SL"]->pageJAVA .= 'currTreeType = "' 
            . $GLOBALS["SL"]->treeRow->tree_type 
            . '"; setCurrPage("' . $this->v["currPage"][1] . '", "' 
            . $this->v["currPage"][0] . '", ' . $this->currNode() 
            . '); function loadPageNodes() { if (typeof chkNodeVisib === "function") { ' 
            . $this->v["javaNodes"] . ' } else { setTimeout("loadPageNodes()", 500); } '
            . 'return true; } setTimeout("loadPageNodes()", 100); ' . "\n";
        // Check if search results page
        if ($GLOBALS["SL"]->treeRow->tree_opts%31 == 0 
            && $GLOBALS["SL"]->REQ->has('s') 
            && trim($GLOBALS["SL"]->REQ->get('s')) != '') {
            if ($GLOBALS["SL"]->treeRow->tree_opts%3 == 0
                || $GLOBALS["SL"]->treeRow->tree_opts%17 == 0 
                || $GLOBALS["SL"]->treeRow->tree_opts%41 == 0
                || $GLOBALS["SL"]->treeRow->tree_opts%43 == 0) {
                $GLOBALS["SL"]->pageJAVA .= 'setTimeout(\''
                    . 'if (document.getElementById("admSrchFld")) '
                    . 'document.getElementById("admSrchFld").value=' 
                    . json_encode(trim($GLOBALS["SL"]->REQ->get('s')))
                    . '\', 10); ';
            } // else check for the main public search field? 
        }
        return true;
    }
    
    /**
     * Override the default behavior for wrapping a tree which has
     * been called through an ajax call.
     *
     * @return string
     */
    protected function ajaxContentWrapCustom($str, $nID = -3)
    {
        return $str;
    }
    
    protected function runCurrNode($nID)
    {
        //if ($nID == $GLOBALS["SL"]->treeRow->tree_root) $this->runDataManip($nID);
        return true;
    }
    
    protected function runDataManip($nID, $betweenPages = false)
    {
        $curr = $this->allNodes[$nID];
        if ($curr->isDataManip()) {
            $curr->fillNodeRow();
            $curr->nID = $nID;
            list($curr->tbl, $curr->fld, $newVal) = $curr->getManipUpdate();
            if ($curr->nodeType == 'Data Manip: New') {
                //$newObj = $this->checkNewDataRecord($tbl, $fld, $newVal);
                //if (!$newObj) {
                    $newObj = $this->sessData->newDataRecord($curr->tbl, $curr->fld, $newVal);
                    if ($newObj) {
                        $this->sessData->startTmpDataBranch($curr->tbl, $newObj->getKey());
                        $this->sessData->currSessData($curr, 'update', $newVal);
                        $manipUpdates = SLNode::where('node_tree', $this->treeID)
                            ->where('node_type', 'Data Manip: Update')
                            ->where('node_parent_id', $nID)
                            ->get();
                        if ($manipUpdates->isNotEmpty()) {
                            foreach ($manipUpdates as $nodeRow) {
                                $nID2 = $nodeRow->node_id;
                                $tmp = new TreeNodeSurv($nID2, $nodeRow);
                                $tmp->nID = $nID2;
                                $tmp->fillNodeRow();
                                list($tmp->tbl, $tmp->fld, $newVal) = $tmp->getManipUpdate();
                                $this->sessData->currSessData($tmp, 'update', $newVal);
                            }
                        }
                        if ($betweenPages) {
                            $this->sessData->endTmpDataBranch($curr->tbl);
                        }
                    }
                //}
                //$this->loadAllSessData();
            } elseif ($this->allNodes[$nID]->nodeType == 'Data Manip: Update') {
                $this->sessData->currSessData($curr, 'update', $newVal);
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
            if (isset($curr->nodeRow->node_response_set) 
                && strpos($curr->nodeRow->node_response_set, 'LoopItems:') === 0) {
                $loop = trim(str_replace('LoopItems:', '', $curr->nodeRow->node_response_set));
            }
            if ($loop != '' 
                && isset($GLOBALS["SL"]->dataLoops[$loop]) 
                && isset($GLOBALS["SL"]->dataLoops[$loop]->node_loop_table)) {
                $tbl = $GLOBALS["SL"]->dataLoops[$loop]->node_loop_table;
            } elseif (isset($curr->dataBranch) && trim($curr->dataBranch) != '') {
                $tbl = $curr->dataBranch;
            }
        }
        return [ $tbl, $fld, $newVal ];
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
                    && $newVal != ''
                    && $fld != '') {
                    $found = true;
                }
            }
            $nID = $this->allNodes[$nID]->getParent();
        }
        return $found;
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
        $GLOBALS["SL"]->microLog('loadNodeURL(' . $nodeSlug);
        if (trim($nodeSlug) != '') {
            $this->setNodeURL($nodeSlug);
        }
        return $this->index($request);
    }
    
    public function testRun(Request $request)
    {
        return $this->index($request, 'testRun');
    }
    
    public function ajaxChecksCustom(Request $request, $type = '')
    {
        return '';
    }
    
    public function ajaxChecks(Request $request, $type = '')
    {
        $this->survloopInit($request, '/ajax/' . $type);
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
        $this->survloopInit($request, '/ajadm/' . $type);
        $nID = (($request->has('nID')) ? trim($request->get('nID')) : '');
        if ($type == 'adm-menu-toggle') {
            return $this->ajaxAdmMenuToggle($request);
        } elseif ($type == 'data-set-search-results') {
            return $this->printDataSetSearchResultsAjax($request);
        } elseif ($type == 'color-pick') {
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


    /**
     * Print search results across multiple data sets.
     *
     * @param  Illuminate\Http\Request  $request
     * @return string
     */
    private function printDataSetSearchResultsAjax(Request $request)
    {
        $type = 0;
        if ($request->has('type')) {
            $type = intVal($request->get('type'));
        }
        if (sizeof($GLOBALS["SL"]->allCoreTbls) > 0) {
            foreach ($GLOBALS["SL"]->allCoreTbls as $tbl) {
                if (intVal($type) == intVal($tbl["id"])) {
                    $ret = $this->printDataSetResultsAjaxCustom($request, $tbl);
                    if (trim($ret) != '') {
                        return $ret;
                    }
                    return $this->printDataSetResultsAjax($request, $tbl);
                }
            }
        }
        return '<!-- no data set search results found -->';
    }

    /**
     * Customize search results from one data sets.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  array  $tblInfo
     * @return string
     */
    private function printDataSetResultsAjax(Request $request, $tblInfo)
    {
        return '<i>Auto-printing multi-data-set searches coming soon...</i>';
    }

    /**
     * Customize search results from one data sets.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  array  $tblInfo
     * @return string
     */
    protected function printDataSetResultsAjaxCustom(Request $request, $tblInfo)
    {
        return '';
    }

    /**
     * Customize search results from one data sets.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  array  $tblInfo
     * @return string
     */
    private function printDataSetResultsWrap(Request $request, $tblInfo)
    {
        return '<h3 class="slBlueDark">' . $tblInfo["name"] . '</h3>
            <div id="setSearchResults' . $tblInfo["name"] . '" class="w100"></div>
            <script type="text/javascript"> $(document).ready(function(){ 
                setTimeout(function() {
                    var url = "/ajax/data-set-search-results?type=' 
                        . strtolower($tblInfo["name"]) . '";
                    $("#setSearchResults' . $tblInfo["name"] . '").load(url);
                    console.log(url);
                }, 10);
            }); </script>';
    }
    
    private function ajaxAdmMenuToggle(Request $request)
    {
        $open = 0;
        if ($request->has('status')
            && strtolower(trim($request->get('status'))) == 'open') {
            $open = 1;
        }
        session()->put('admMenuOpen', $open);
        session()->save();
        return '';
    }
    
    protected function ajaxColorPicker(Request $request)
    {
        $fldName = $preSel = '';
        if ($request->has('fldName')) {
            $fldName = trim($request->get('fldName'));
        }
        if ($request->has('preSel')) {
            $preSel = '#' . trim($request->get('preSel'));
        }
        if (trim($fldName) != '') {
            $sysColors = [];
            $sysStyles = SLDefinitions::where('def_database', 1)
                ->where('def_set', 'Style Settings')
                ->orderBy('def_order')
                ->get();
            $isCustom = true;
            if ($sysStyles->isNotEmpty()) {
                foreach ($sysStyles as $i => $sty) {
                    if (strpos($sty->def_subset, 'color-') !== false 
                        && !in_array($sty->def_description, $sysColors)) {
                        $sysColors[] = $sty->def_description;
                        if ($sty->def_description == $preSel) {
                            $isCustom = false;
                        }
                    }
                }
            }
            return view(
                'vendor.survloop.forms.inc-color-picker-ajax', 
                [
                    "sysColors" => $sysColors,
                    "fldName"   => $fldName,
                    "preSel"    => $preSel,
                    "isCustom"  => $isCustom
                ]
            );
        }
        return '';
    }
    
    protected function ajaxLogLastProTip(Request $request)
    {
        if ($request->has('tree') 
            && intVal($request->get('tree')) > 0 
            && $request->has('tip') 
            && intVal($request->get('tip')) > 0) {
            $tok = $this->getProTipToken();
            $tok->tok_tok_token = intVal($request->get('tip'));
            $tok->save();
        }
        exit;
    }
    
    protected function getProTipToken()
    {
        $tok = SLTokens::where('tok_type', 'ProTip')
            ->where('tok_user_id', $this->v["uID"])
            ->where('tok_tree_id', $this->treeID)
            ->first();
        if (!$tok) {
            $tok = new SLTokens;
            $tok->tok_type      = 'ProTip';
            $tok->tok_user_id   = $this->v["uID"];
            $tok->tok_tree_id   = $this->treeID;
            $tok->tok_tok_token = 0;
            $tok->save();
        }
        return $tok;
    }
    
    protected function loadTreeProTip()
    {
        $tok = $this->getProTipToken();
        $GLOBALS["SL"]->currProTip = $tok->tok_tok_token;
        $GLOBALS["SL"]->pageJAVA .= ' lastProTip = ' . $tok->tok_tok_token . '; ';
        return true;
    }
    
    protected function changeNodeID($nID, $newID)
    {
        
    }
    
    protected function clearLostSessionHelpers()
    {
        $chk = SLSess::select('sess_id')
            ->get();
        $sessIDs = $GLOBALS["SL"]->resultsToArrIds($chk, 'sess_id');
        SLSessLoops::whereNotIn('sess_loop_sess_id', $sessIDs)
            ->limit(1000)
            ->delete();
        SLNodeSaves::whereNotIn('node_save_session', $sessIDs)
            ->limit(1000)
            ->delete();
        SLNodeSavesPage::whereNotIn('page_save_session', $sessIDs)
            ->limit(1000)
            ->delete();
        return true;
    }

    protected function setTreePageFadeInDelay()
    {
        return 1000;
    }
    
    
}
