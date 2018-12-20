<?php
/**
  * TreeSurv is a mid-level class using a standard branching tree, mostly for SurvLoop's surveys and pages.
  * But it does house some of the core functions to print the whole of a tree.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\User;
use App\Models\SLDatabases;
use App\Models\SLDefinitions;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLNodeSaves;
use App\Models\SLNodeSavesPage;
use App\Models\SLNodeResponses;
use App\Models\SLFields;
use App\Models\SLSess;
use App\Models\SLSessLoops;
use App\Models\SLSessEmojis;
use App\Models\SLSearchRecDump;
use App\Models\SLContact;
use App\Models\SLConditions;
use App\Models\SLConditionsArticles;
use App\Models\SLUsersActivity;
use SurvLoop\Controllers\TreeNodeSurv;
use SurvLoop\Controllers\SurvData;
use SurvLoop\Controllers\TreeSurvAPI;
use SurvLoop\Controllers\Globals;
use SurvLoop\Controllers\TreeSurvLoad;

class TreeSurv extends TreeSurvLoad
{
    public function printTreePublic()
    {
        $ret = '';
        $this->loadTree();
        
        if ($this->hasAjaxWrapPrinting()) {
            $ret .= '<div id="ajaxWrap">';
        }
        $ret .= '<a name="maincontent" id="maincontent"></a>' . "\n";
        if (!$this->isPage) {
            $ret .= '<div id="maincontentWrap" style="display: none;">' . "\n";
        }
        if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('node') && $GLOBALS["SL"]->REQ->input('node') > 0) {
            $this->updateCurrNode($GLOBALS["SL"]->REQ->input('node'));
        }
        
        $lastNode = $this->currNode();
        if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('superHardJump')) {
            $this->updateCurrNode(intVal($GLOBALS["SL"]->REQ->superHardJump));
        }
        
        if ($this->currNode() < 0 || !isset($this->allNodes[$this->currNode()])) {
            $this->updateCurrNode($GLOBALS["SL"]->treeRow->TreeRoot);
            //return '<h1>Sorry, Page Not Found.</h1>';
        }
        
        // double-check we haven't landed on a mid-page node
        if (isset($this->allNodes[$this->currNode()]) && !$this->allNodes[$this->currNode()]->isPage() 
            && !$this->allNodes[$this->currNode()]->isLoopRoot()) {
            $this->updateCurrNode($this->allNodes[$this->currNode()]->getParent());
        }
        
        $this->loadAncestry($this->currNode());
        
        if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('step')) {
            if (!$this->sessInfo) $this->createNewSess();
            // Process form POST for all nodes, then store the data updates...
            if ($this->REQstep == 'autoSave' 
                && (!$GLOBALS["SL"]->REQ->has('chgCnt') || intVal($GLOBALS["SL"]->REQ->get('chgCnt')) <= 0)) {
                return 'No changes found to auto-save. <3';
            }
            $logTitle = 'PAGE SAVE' . (($this->REQstep == 'autoSave') ? ' AUTO' : '');
            $this->sessData->logDataSave($GLOBALS["SL"]->REQ->node, $logTitle, -3, '', '');
            $ret .= $this->postNodePublic($GLOBALS["SL"]->REQ->node);
            if ($this->REQstep == 'autoSave') return 'Saved!-)';
            $this->loadAllSessData();
            if (!$this->isPage) {
                if ($this->REQstep == 'save') {
                    if ($GLOBALS["SL"]->REQ->has('popStateUrl') && trim($GLOBALS["SL"]->REQ->popStateUrl) != '') {
                        $this->setNodeURL(str_replace($GLOBALS["SL"]->treeBaseSlug, '', 
                            $GLOBALS["SL"]->REQ->popStateUrl));
                        $this->pullNewNodeURL();
                    } else {
                        $redir1 = '';
                        if ($GLOBALS["SL"]->REQ->has('jumpTo') && trim($GLOBALS["SL"]->REQ->get('jumpTo')) != '') {
                            $redir1 = trim($GLOBALS["SL"]->REQ->get('jumpTo'));
                        }
                        if ($GLOBALS["SL"]->REQ->has('afterJumpTo') 
                            && trim($GLOBALS["SL"]->REQ->get('afterJumpTo')) != '') {
                            session()->put('redir2', trim($GLOBALS["SL"]->REQ->get('afterJumpTo')));
                        }
                        if ($redir1 != '') return $this->redir($redir1, true);
                    }
                } else {
                    $this->updateCurrNode($GLOBALS["SL"]->REQ->node);
                    $lastNode = $GLOBALS["SL"]->REQ->node;
                    // Now figure what comes next. 
                    if (!$this->isStepUpload()) { // if uploading, then don't change nodes yet
                        $jumpID = $this->jumpToNode($this->currNode());
                        if (in_array($this->REQstep, ['exitLoop', 'exitLoopBack', 'exitLoopJump']) 
                            && trim($GLOBALS["SL"]->REQ->input('loop')) != '') {
                            $this->sessData->logDataSave($this->currNode(), 
                                $GLOBALS["SL"]->closestLoop["obj"]->DataLoopTable, 
                                $GLOBALS["SL"]->REQ->input('loopItem'), $this->REQstep, $GLOBALS["SL"]->REQ->input('loop'));
                            $this->leavingTheLoop($GLOBALS["SL"]->REQ->input('loop'));
                            if ($this->REQstep == 'exitLoop') {
                                $this->updateCurrNodeNB($this->nextNodeSibling($this->currNode()));
                            } elseif ($this->REQstep == 'exitLoopBack') {
                                $prev = $this->getNextNonBranch($this->prevNode($this->currNode()), 'prev');
                                $this->updateCurrNodeNB($prev, 'prev');
                            } else {
                                $this->updateCurrNode($jumpID); // exit through jump
                            }
                        } elseif ($jumpID > 0) {
                            $this->updateCurrNode($jumpID);
                        } else { // no jumps, let's do the old back and forth...
                            if ($this->REQstep == 'back') {
                                $prev = $this->getNextNonBranch($this->prevNode($this->currNode()), 'prev');
                                $this->updateCurrNodeNB($prev, 'prev');
                            } else {
                                $this->updateCurrNodeNB($this->nextNode($this->currNode(), $this->currNodeSubTier));
                            }
                        }
                    }
                } // end REQstep == 'save' check
            }
        } elseif (trim($this->urlSlug) != '') {
            $this->pullNewNodeURL();
            if ($this->currNode() == $GLOBALS["SL"]->treeRow->TreeFirstPage 
                && $GLOBALS["SL"]->REQ->has('start') && intVal($GLOBALS["SL"]->REQ->get('start')) == 1) {
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
                    $this->updateCurrNode($this->nextNode($this->currNode(), $this->currNodeSubTier));
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
        
        if (!$GLOBALS["SL"]->REQ->has('popStateUrl') || trim($GLOBALS["SL"]->REQ->popStateUrl) == '') {
            $this->pushCurrNodeURL($this->currNode());
        }
        $this->multiRecordCheck();
        
        $this->loadAncestry($this->currNode());
        
        $this->v["nodeKidFunks"] = '';
        
        $goSkinny = (!$this->hasFrameLoad() && $GLOBALS["SL"]->treeRow->TreeOpts%Globals::TREEOPT_SKINNY == 0);
        if ($goSkinny) {
            $ret .= '<center><div id="skinnySurv" class="treeWrapForm">';
        } elseif (!$this->isPage) {
            $ret .= '<div id="wideSurv" class="container">';
        }
        $ret .= ((trim($GLOBALS["errors"]) != '') ? $GLOBALS["errors"] : '') . $this->nodeSessDump('pageStart')
            . $this->printNodePublic($this->currNode(), $this->currNodeSubTier) . "\n"
            . $this->loadProgBar() . "\n"
                // (($this->allNodes[$this->currNode()]->nodeOpts%29 > 0) ? $this->loadProgBar() : '') // not exit page?
            . $this->printCurrRecMgmt() . $this->sessDump($lastNode) . "\n";
        if ($goSkinny) {
            $ret .= '</div><center>';
        } elseif (!$this->isPage) {
            $ret .= '</div>';
        }
        if (isset($GLOBALS["SL"]->treeSettings["footer"]) && isset($GLOBALS["SL"]->treeSettings["footer"][0]) 
            && trim($GLOBALS["SL"]->treeSettings["footer"][0]) != '') {
            $this->v["footOver"] = $GLOBALS["SL"]->treeSettings["footer"][0];
        }
        if (trim($this->v["nodeKidFunks"]) != '') {
            $GLOBALS["SL"]->pageAJAX .= 'function checkAllNodeKids() { ' . $this->v["nodeKidFunks"] 
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
                .= 'if (document.getElementById("dynamicJS")) document.getElementById("dynamicJS").remove();
                    if (document.getElementById("treeJS")) document.getElementById("treeJS").remove();';
            $GLOBALS["SL"]->pageAJAX 
                .= 'if (document.getElementById("maincontentWrap")) $("#maincontentWrap").fadeIn(50); ';
            $ret = $GLOBALS["SL"]->genPageDynamicJs($ret) . $GLOBALS["SL"]->pageSCRIPTS;
            $GLOBALS["SL"]->pageSCRIPTS = '';
        }
        return $ret;
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
        if (isset($GLOBALS["SL"]->x["pageView"]) && trim($GLOBALS["SL"]->x["pageView"]) != '') {
            $notes .= 'pv.' . $GLOBALS["SL"]->x["pageView"] . ' dp.' . $GLOBALS["SL"]->x["dataPerms"];
        }
        $this->v["content"] = $this->printTreePublic() . (($notes != '') ? '<!-- ' . $notes . ' -->' : '');
        if ($this->v["currPage"][0] != '/') {
            $log = new SLUsersActivity;
            $log->UserActUser = $this->v["uID"];
            $log->UserActCurrPage = $this->v["currPage"][0] . $notes;
            $log->save();
        }
        $GLOBALS["SL"]->pageJAVA .= $this->v["javaNodes"];
        if ($request->has('ajax') && $request->ajax == 1) { // tree form ajax submission
            echo $this->ajaxContentWrapCustom($this->v["content"]);
            exit;
        }
        // Otherwise, Proceed Running Various Index Functions
        $this->v["currInReport"] = $this->currInReport();
        if ($type == 'testRun') {
            return $this->redir('/');
        }
        
        if ($GLOBALS["SL"]->treeRow->TreeOpts%31 == 0) { // search results page
            if ($GLOBALS["SL"]->REQ->has('s') && trim($GLOBALS["SL"]->REQ->get('s')) != '') {
                if ($GLOBALS["SL"]->treeRow->TreeOpts%3 == 0 || $GLOBALS["SL"]->treeRow->TreeOpts%17 == 0 
                    || $GLOBALS["SL"]->treeRow->TreeOpts%41 == 0 || $GLOBALS["SL"]->treeRow->TreeOpts%43 == 0) {
                    $GLOBALS["SL"]->pageJAVA .= 'setTimeout(\'if (document.getElementById("admSrchFld")) '
                        . 'document.getElementById("admSrchFld").value=' 
                        . json_encode(trim($GLOBALS["SL"]->REQ->get('s'))) . '\', 10); ';
                } // else check for the main public search field? 
            }
        }
        $this->v["content"] = $GLOBALS["SL"]->genPageDynamicJs($this->v["content"]); // scrape scripts
        if ($GLOBALS["SL"]->treeIsAdmin) {
            return $GLOBALS["SL"]->swapSessMsg($this->v["content"]);
        } else {
            return $GLOBALS["SL"]->swapSessMsg(view('vendor.survloop.master', $this->v)->render());
        }
    }
    
    protected function ajaxContentWrapCustom($str, $nID = -3) { return $str; }
    
    // returns 1 if nID is a conditional kid, and is true
    // returns 0 if nID is not a conditional kid
    // returns -1 if nID is a conditional kid, and is false
    protected function chkKidMapTrue($nID)
    {
        $found = false;
        if (sizeof($this->kidMaps) > 0) {
            foreach ($this->kidMaps as $parent => $kids) {
                if (sizeof($kids) > 0) {
                    foreach ($kids as $nKid => $ress) {
                        if ($nID == $nKid && sizeof($ress) > 0) {
                            $found = true;
                            foreach ($ress as $cnt => $res) {
                                if (isset($res[2]) && $res[2]) return 1;
                            }
                        }
                    }
                }
            }
        }
        return (($found) ? -1 : 0);
    }
    
    public function hasParentPage($nID)
    {
        if (isset($this->allNodes[$nID]) && isset($this->allNodes[$nID]->parentID) 
            && intVal($this->allNodes[$nID]->parentID) > 0) {
            if (isset($this->allNodes[$this->allNodes[$nID]->parentID]) 
                && $this->allNodes[$this->allNodes[$nID]->parentID]->isPage()) return true;
            else return $this->hasParentPage($this->allNodes[$nID]->parentID);
        }
        return false;
    }
    
    protected function nodeIsWithinPage($nID)
    {
        $parent = $this->allNodes[$nID]->getParent();
        while ($this->hasNode($parent)) {
            if ($this->allNodes[$parent]->isPage()) return true;
            if ($this->allNodes[$parent]->isBranch() || $this->allNodes[$parent]->isLoopRoot()) {
                return false;
            }
            $parent = $this->allNodes[$parent]->getParent();
        }
        return false;
    }
    
    protected function isDisplayableNode($nID, $exception = '')
    {
        if (!$this->hasNode($nID) || !$this->checkNodeConditions($nID)) return false;
        if ($this->allNodes[$nID]->isDataManip() && !$this->nodeIsWithinPage($nID)) {
            $this->runDataManip($nID, true);
        }
        if (!$this->allNodes[$nID]->isPage() && !$this->allNodes[$nID]->isLoopRoot()) return false;
        if (!$this->checkParentBranchConditions($nID)) return false;
        return true;
    }
    
    protected function checkParentBranchConditions($nID)
    {
        $clear = true;
        $parentID = $this->allNodes[$nID]->getParent();
        while ($parentID > 0 && $clear) {
            if (!$this->checkNodeConditions($parentID)) $clear = false;
            $parentID = $this->allNodes[$parentID]->getParent();
        }
        return $clear;
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
                                $this->sessData->currSessData($nodeRow->NodeID, $tbl, $fld, 'update', $newVal);
                            }
                        }
                        if ($betweenPages) $this->sessData->endTmpDataBranch($tbl);
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
        if (!$curr) $curr = $this->allNodes[$nID];
        if (in_array($curr->nodeType, ['Data Manip: New', 'Data Manip: Wrap'])) { // Data Manip: Update
            list($tbl, $fld, $newVal) = $curr->getManipUpdate();
            if ($curr->nodeType == 'Data Manip: Wrap') $tbl = $curr->dataBranch;
        }
        if ($curr->isLoopCycle()) {
            $loop = '';
            if (isset($curr->nodeRow->NodeResponseSet) 
                && strpos($curr->nodeRow->NodeResponseSet, 'LoopItems:') === 0) {
                $loop = trim(str_replace('LoopItems:', '', $curr->nodeRow->NodeResponseSet));
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
            if ($manipBranchRow) $this->sessData->startTmpDataBranch($tbl, $manipBranchRow->getKey());
        }
        return true;
    }
    
    protected function closeManipBranch($nID)
    {
        list($tbl, $fld, $newVal) = $this->nodeBranchInfo($nID);
        if ($tbl != '') $this->sessData->endTmpDataBranch($tbl);
        return true;
    }
    
    protected function hasParentDataManip($nID)
    {
        $found = false;
        while ($this->hasNode($nID) && !$found) {
            if ($this->allNodes[$nID]->isDataManip()) {
                list($tbl, $fld, $newVal) = $this->allNodes[$nID]->getManipUpdate();
                if ($this->allNodes[$nID]->nodeType == 'Data Manip: New' && $fld != '' && $newVal != '') {
                    $found = true;
                }
            }
            $nID = $this->allNodes[$nID]->getParent();
        }
        return $found;
    }
    
    protected function getNextNonBranch($nID, $direction = 'next')
    {
        if ($nID == $GLOBALS["SL"]->treeRow->TreeLastPage && $direction == 'next') return -37;
        if (!$this->hasNode($nID)) return -3;
        $nIDbranch = $this->checkBranchCondition($nID, $direction);
        if ($nID != $nIDbranch) $nID = $nIDbranch; 
        $this->loopCnt = 0;
        while (!$this->isDisplayableNode($nID) && $this->loopCnt < 1000) {
            if ($nID == $GLOBALS["SL"]->treeRow->TreeLastPage && $direction == 'next') return -37;
            $nIDbranch = $this->checkBranchCondition($nID, $direction);
            if ($nID != $nIDbranch) $nID = $nIDbranch; 
            elseif ($direction == 'next') $nID = $this->nextNode($nID);
            else $nID = $this->prevNode($nID);
            $this->loopCnt++;
        }
        if (trim($this->loadingError) != '') {
            $ret .= '<div class="p10"><i>loadNodeSubTier() - ' . $this->loadingError . '</i></div>';
        }
        return $nID;
    }
    
    protected function checkBranchCondition($nID, $direction = 'next')
    {
        if (isset($this->allNodes[$nID]) && $this->allNodes[$nID]->isBranch() && !$this->checkNodeConditions($nID)) {
            if ($direction == 'next') $nID = $this->nextNodeSibling($nID);
            else $nID = $this->prevNode($nID);
        }
        return $nID;
    }
    
    protected function newLoopItem($nID = -3)
    {
        if (intVal($this->newLoopItemID) <= 0) {
            $newID = $this->sessData->createNewDataLoopItem($nID);
            $this->afterCreateNewDataLoopItem($GLOBALS["SL"]->REQ->input('loop'), $newID); //$loop->DataLoopPlural
            if ($newID > 0) {
                $GLOBALS["SL"]->REQ->loopItem = $newID;
                $this->settingTheLoop(trim($GLOBALS["SL"]->REQ->input('loop')), intVal($GLOBALS["SL"]->REQ->loopItem));
            }
            $this->newLoopItemID = $nID;
        }
        return true;
    }
    
    protected function settingTheLoop($name, $itemID = -3, $rootJustLeft = -3)
    {
        if ($name == '') return false; 
        $found = false;
        if (sizeof($GLOBALS["SL"]->sessLoops) > 0) {
            foreach ($GLOBALS["SL"]->sessLoops as $loop) {
                if (!$found && $loop->SessLoopName == $name) {
                    $loop->SessLoopItemID = $itemID;
                    $loop->save();
                    $found = true;
                }
            }
        }
        if (!$found) {
            $newLoop = new SLSessLoops;
            $newLoop->SessLoopSessID = $this->sessID;
            $newLoop->SessLoopName   = $name;
            $newLoop->SessLoopItemID = $itemID;
            $newLoop->save();
        }
        if ($this->sessInfo) {
            $GLOBALS["SL"]->loadSessLoops($this->sessID);
            $this->sessInfo->SessLoopRootJustLeft = $rootJustLeft;
            $this->sessInfo->save();
        }
        $this->runLoopConditions();
        return true;
    }
    
    protected function leavingTheLoop($name = '', $justClearID = false)
    {
        if (sizeof($GLOBALS["SL"]->sessLoops) > 0) {
            foreach ($GLOBALS["SL"]->sessLoops as $i => $loop) {
                if ($loop->SessLoopName == $name || $name == '') {
                    if ($justClearID) {
                        $loop->SessLoopItemID = -3;
                        $loop->save();
                    } else {
                        $GLOBALS["SL"]->sessLoops[$i]->delete();
                        $this->sessData->leaveCurrLoop();
                    }
                }
            }
        }
        $GLOBALS["SL"]->loadSessLoops($this->sessID);
        return true;
    }
    
    protected function afterCreateNewDataLoopItem($tbl, $itemID = -3) { return true; }
    
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
                    if ($this->allNodes[$prevNode]->checkBranch($this->allNodes[$loop->DataLoopRoot]->nodeTierPath)
                        && !$this->allNodes[$newNode]->checkBranch($this->allNodes[$loop->DataLoopRoot]->nodeTierPath)){
                        // Then we are now trying to leave this loop
                        if (in_array($this->REQstep, ['back', 'exitLoopBack'])) { 
                            // Then leaving the loop backwards, always allowed
                            $this->leavingTheLoop($loop->DataLoopPlural);
                        } elseif ($this->REQstep != 'save') { // Check for conditions before moving leaving forward
                            if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop()) {
                                if (sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) > 1) {
                                    $backToRoot = true;
                                }
                            } elseif (intVal($loop->DataLoopMaxLimit) == 0 
                                || sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) 
                                    < $loop->DataLoopMaxLimit) {
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
                        $skipRoot = false;
                        if ($this->allNodes[$newNode]->isStepLoop()) {
                            if (sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) == 1 
                                || ($loop->DataLoopMinLimit == 1 && $loop->DataLoopMaxLimit == 1)) {
                                $skipRoot = true;
                            }
                        } elseif ($loop->DataLoopMinLimit > 0 
                            && sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) == 0) {
                            $skipRoot = true;
                        }
                        if ($skipRoot) {
                            $this->pushCurrNodeVisit($newNode);
                            if ($this->REQstep == 'back') {
                                $this->leavingTheLoop($loop->DataLoopPlural);
                                $prev = $this->getNextNonBranch($this->prevNode($loop->DataLoopRoot), 'prev');
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
                if (!isset($currLoops[$loop->DataLoopPlural]) && isset($this->allNodes[$loop->DataLoopRoot])) {
                    // Then this is a new loop we weren't previously in
                    $path = $this->allNodes[$loop->DataLoopRoot]->nodeTierPath;
                    if (isset($this->allNodes[$prevNode]) && !$this->allNodes[$prevNode]->checkBranch($path)
                        && $this->allNodes[$newNode]->checkBranch($path)) {
                        // Then we have just entered this loop from outside
                        if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop() 
                            && (!isset($this->sessData->loopItemIDs[$loop->DataLoopPlural]) 
                                || empty($this->sessData->loopItemIDs[$loop->DataLoopPlural]))) {
                            $this->leavingTheLoop($loop->DataLoopPlural);
                            if (isset($this->REQstep) && in_array($this->REQstep, ['back', 'exitLoopBack'])) {
                                $prevRoot = $this->getNextNonBranch($this->prevNode($loop->DataLoopRoot), 'prev');
                                $this->updateCurrNodeNB($prevRoot);
                            } elseif (!isset($this->REQstep) || $this->REQstep != 'save') {
                                $this->updateCurrNodeNB($this->nextNodeSibling($newNode));
                            }
                        } else { // This loop is active
                            $skipRoot = false;
                            if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop()) {
                                if (sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) == 1 
                                    || ($loop->DataLoopMinLimit == 1 && $loop->DataLoopMaxLimit == 1)) {
                                    $skipRoot = true;
                                }
                            } elseif ($loop->DataLoopMinLimit > 0 
                                && sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) == 0) {
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
                                        $itemID = $this->sessData->loopItemIDs[$loop->DataLoopPlural][0];
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
                                            $this->sessData->loopItemIDs[$loop->DataLoopPlural][0]);
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
    
    public function pushCurrNodeURL($nID = -3)
    {
        if ($GLOBALS['SL']->treeRow->TreeType == 'Page') return true;
        if (intVal($nID) > 0 && isset($this->allNodes[$nID])) {
            $this->allNodes[$nID]->fillNodeRow();
            if (isset($this->allNodes[$nID]->nodeRow->NodePromptNotes) 
                && trim($this->allNodes[$nID]->nodeRow->NodePromptNotes) != '') {
                $this->pushCurrNodeVisit($nID);
                if ($this->hasREQ && ($GLOBALS["SL"]->REQ->has('ajax') || $GLOBALS["SL"]->REQ->has('frame'))) {
                    $title = $this->allNodes[$nID]->nodeRow->NodePromptText;
                    if (strpos($title, '</h1>') > 0) $title = substr($title, 0, strpos($title, '</h1>'));
                    elseif (strpos($title, '</h2>') > 0) $title = substr($title, 0, strpos($title, '</h2>'));
                    elseif (strpos($title, '</h3>') > 0) $title = substr($title, 0, strpos($title, '</h3>'));
                    $title = str_replace('"', '\\"', str_replace('(s)', '', strip_tags($title)));
                    $title = trim(preg_replace('/\s\s+/', ' ', $title));
                    $title = str_replace("\n", " ", $title);
                    if (trim($title) == '') $title = trim($GLOBALS['SL']->treeRow->TreeName);
                    if (strlen($title) > 40) $title = substr($title, 0, 40) . '...';
                    $this->v["currPage"]    = [];
                    $this->v["currPage"][1] = ((trim($title) != '') ? $title . ' - ' : '') 
                        . $GLOBALS["SL"]->sysOpts["site-name"];
                    $this->v["currPage"][0] = '/' . (($GLOBALS["SL"]->treeIsAdmin) ? 'dash' : 'u') . '/' 
                        . $GLOBALS["SL"]->treeRow->TreeSlug . '/' . $this->allNodes[$nID]->nodeRow->NodePromptNotes;
                    $GLOBALS["SL"]->pageAJAX .= 'history.pushState( {}, "' . $this->v["currPage"][1] . '", '
                        . '"' . $this->v["currPage"][0] . '");' . "\n" 
                        . 'document.title="' . $this->v["currPage"][1] . '";' . "\n";
                }
            }
        }
        return true;
    }
    
    public function pushCurrNodeVisit($nID)
    {
        if (isset($this->sessInfo->SessID) && $nID > 0 && !$GLOBALS["SL"]->REQ->has('preview')) {
            $pagsSave = new SLNodeSavesPage;
            $pagsSave->PageSaveSession = $this->sessInfo->SessID;
            $pagsSave->PageSaveNode    = $nID;
            $pagsSave->save();
        }
        return true;
    }
    
    public function setNodeURL($slug = '')
    {
        $this->urlSlug = $slug;
        return true;
    }
    
    public function currNodeURL($nID = -3)
    {
        $curr = $this->currNode();
        if ($nID > 0) $curr = $nID;
        if (!isset($this->allNodes[$curr])) return '';
        $this->allNodes[$curr]->fillNodeRow();
        if (isset($this->allNodes[$curr]) && $this->allNodes[$curr]->isPage()
            && trim($this->allNodes[$curr]->nodeRow->NodePromptNotes) != '') {
            return '/' . (($GLOBALS["SL"]->treeIsAdmin) ? 'dash' : 'u') . '/' 
                . $GLOBALS["SL"]->treeRow->TreeSlug . '/' . $this->allNodes[$curr]->nodeRow->NodePromptNotes;
        }
        return '';
    }
    
    public function pullNewNodeURL()
    {
        if (trim($this->urlSlug) != '') {
            $loadNode = SLNode::where('NodeTree', $this->treeID)
                ->where('NodePromptNotes', $this->urlSlug)
                ->where(function ($query) {
                    return $query->where('NodeType', 'Page')
                        ->orWhere('NodeType', 'Loop Root');
                })
                ->first();
            if ($loadNode && isset($loadNode->NodeID)) {
                if (!$GLOBALS["SL"]->REQ->has('preview') && !$GLOBALS["SL"]->REQ->has('popStateUrl')) {
                    $loadNodeChk = DB::table('SL_NodeSavesPage')
                        ->join('SL_Sess', 'SL_NodeSavesPage.PageSaveSession', '=', 'SL_Sess.SessID')
                        ->where('SL_Sess.SessTree', '=', $this->treeID)
                        ->where('SL_Sess.SessCoreID', '=', $this->coreID)
                        ->where('SL_NodeSavesPage.PageSaveNode', $loadNode->NodeID)
                        ->get();
                    if ($loadNodeChk->isEmpty()) return false;
                }
                // perhaps upgrade to check for loop item id first?
                //$this->leavingTheLoop();
                $prevNode = $this->currNode();
                $this->updateCurrNode($loadNode->NodeID);
                if (sizeof($GLOBALS["SL"]->dataLoops) > 0 && sizeof($GLOBALS["SL"]->sessLoops) > 0) {
                    foreach ($GLOBALS["SL"]->sessLoops as $sessLoop) {
                        foreach ($GLOBALS["SL"]->dataLoops as $loop) {
                            if ($sessLoop->SessLoopName == $loop->DataLoopPlural) {
                                $path = $this->allNodes[$loop->DataLoopRoot]->nodeTierPath;
                                if ($this->allNodes[$prevNode]->checkBranch($path)
                                    && !$this->allNodes[$this->currNode()]->checkBranch($path)) {
                                    $this->leavingTheLoop($loop->DataLoopPlural);
                                }
                            }
                        }
                    }
                }
                if ($loadNode->NodeType == 'Loop Root') {
                    $this->checkLoopsPostProcessing($loadNode->NodeID, $prevNode);
                }
            }
        }
        return true;
    }
    
    
    
    /******************************************************************************************************
    
    REPORT OUTPUT
    
    ******************************************************************************************************/
    
    public function chkDeets($deets)
    {
        $new = [];
        if (sizeof($deets) > 0) {
            foreach ($deets as $i => $deet) {
                if (isset($deet[0]) && trim($deet[0]) != '') $new[] = $deet;
            }
        }
        return $new;
    }
    
    public function printReportDeetsBlock($deets, $blockName = '', $nID = -3)
    {
        $deets = $this->chkDeets($deets);
        return view('vendor.survloop.inc-report-deets', [
            "nID"       => $nID,
            "deets"     => $deets,
            "blockName" => $blockName
            ])->render();
    }
    
    public function printReportDeetsBlockCols($deets, $blockName = '', $cols = 2, $nID = -3)
    {
        $deets = $this->chkDeets($deets);
        $deetCols = $deetsTots = $deetsTotCols = [];
        $colChars = (($cols == 2) ? 37 : (($cols == 3) ? 24 : 16));
        if (sizeof($deets) > 0) {
            foreach ($deets as $i => $deet) {
                $size = 0; //strlen($deet[0]);
                if (sizeof($deet) > 1 && isset($deet[1]) && $size < strlen($deet[1])) $size = strlen($deet[1]);
                if ($size > $colChars) $size -= $colChars;
                else $size = 0;
                $size = ($size/$colChars)+2;
                if ($i == 0) $deetsTots[$i] = $size;
                else $deetsTots[$i] = $deetsTots[$i-1]+$size;
            }
            for ($c = 0; $c < $cols; $c++) {
                $deetCols[$c] = [];
                $deetsTotCols[$c] = [ (($c/$cols)*$deetsTots[sizeof($deetsTots)-1]), -3 ];
            }
            $c = $deetsTotCols[0][1] = 0;
            foreach ($deets as $i => $deet) {
                $chk = 1+$c;
                if ($chk < $cols && $deetsTotCols[$chk][1] < 0 && $deetsTotCols[$chk][0] < $deetsTots[$i]
                    && sizeof($deetCols[$c]) > 0) {
                    $deetsTotCols[$chk][1] = $i;
                    $c++;
                }
                $deetCols[$c][] = $deet;
            }
        }
        return view('vendor.survloop.inc-report-deets-cols', [
            "nID"       => $nID,
            "deetCols"  => $deetCols,
            "blockName" => $blockName,
            "colWidth"  => $GLOBALS["SL"]->getColsWidth($cols)
            ])->render();
    }
    
    public function printReportDeetsVertProg($deets, $blockName = '', $nID = -3)
    {
        $last = 0;
        if (sizeof($deets) > 0) {
            foreach ($deets as $i => $deet) {
                if (isset($deet[1]) && intVal($deet[1]) > 0) $last = $i;
            }
        }
        return view('vendor.survloop.inc-report-deets-vert-prog', [
            "nID"       => $nID,
            "deets"     => $deets,
            "blockName" => $blockName,
            "last"      => $last
            ])->render();
    }
    
    
    
    
    /******************************************************************************************************
    
    XML OUTPUT
    
    ******************************************************************************************************/
    
    protected function maxUserView()
    {
        return true;
    }
    
    private function loadXmlMapTree(Request $request)
    {
        $this->survLoopInit($request);
        if (isset($GLOBALS["SL"]->xmlTree["id"]) && empty($this->xmlMapTree)) {
            $this->xmlMapTree = new TreeSurvAPI;
            $this->xmlMapTree->loadTree($GLOBALS["SL"]->xmlTree["id"], $request, true);
        }
        return true;
    }
        
    private function getXmlTmpV($nID, $tblID = -3)
    {
        $v = [];
        if ($tblID > 0) $v["tbl"] = $GLOBALS["SL"]->tbl[$tblID];
        else $v["tbl"] = $this->xmlMapTree->getNodeTblName($nID);
        $v["tblID"]    = ((isset($GLOBALS["SL"]->tblI[$v["tbl"]])) ? $GLOBALS["SL"]->tblI[$v["tbl"]] : 0);
        $v["tblAbbr"]  = ((isset($GLOBALS["SL"]->tblAbbr[$v["tbl"]])) ? $GLOBALS["SL"]->tblAbbr[$v["tbl"]] : '');
        $v["TblOpts"]  = 1;
        if ($nID > 0 && isset($this->xmlMapTree->allNodes[$nID])) {
            $v["TblOpts"] = $this->xmlMapTree->allNodes[$nID]->nodeOpts;
        }
        $v["tblFlds"] = SLFields::select()
            ->where('FldDatabase', $this->dbID)
            ->where('FldTable', '=', $v["tblID"])
            ->orderBy('FldOrd', 'asc')
            ->orderBy('FldEng', 'asc')
            ->get();
        $v["tblFldEnum"] = $v["tblFldDefs"] = [];
        if ($v["tblFlds"]->isNotEmpty()) {
            foreach ($v["tblFlds"] as $i => $fld) {
                $v["tblFldDefs"][$fld->FldID] = [];
                if (strpos($fld->FldValues, 'Def::') !== false) {
                    $set = $GLOBALS["SL"]->def->getSet(str_replace('Def::', '', $fld->FldValues));
                    if (sizeof($set) > 0) {
                        foreach ($set as $def) $v["tblFldDefs"][$fld->FldID][] = $def->DefValue;
                    }
                } elseif (trim($fld->FldValues) != '' && strpos($fld->FldValues, ';') !== false) {
                    $v["tblFldDefs"][$fld->FldID] = explode(';', $fld->FldValues);
                }
                $v["tblFldEnum"][$fld->FldID] = (sizeof($v["tblFldDefs"][$fld->FldID]) > 0);
            }
        }
        $v["tblHelp"] = $v["tblHelpFld"] = [];
        if ($v["tblID"] > 0 && sizeof($GLOBALS["SL"]->dataHelpers) > 0) {
            foreach ($GLOBALS["SL"]->dataHelpers as $helper) {
                if ($v["tbl"] == $helper->DataHelpParentTable && $helper->DataHelpValueField
                    && !in_array($GLOBALS["SL"]->tblI[$helper->DataHelpTable], $v["tblHelp"])) {
                    $v["tblHelp"][] = $GLOBALS["SL"]->tblI[$helper->DataHelpTable];
                    $v["tblHelpFld"][$GLOBALS["SL"]->tblI[$helper->DataHelpTable]] 
                        = SLFields::where('FldTable', $GLOBALS["SL"]->tblI[$helper->DataHelpTable])
                            ->where('FldName', substr($helper->DataHelpValueField, 
                                strlen($GLOBALS["SL"]->tblAbbr[$helper->DataHelpTable])))
                            ->first();
                }
            }
        }
        return $v;
    }
    
    public function genXmlSchema(Request $request)
    {
        $this->loadXmlMapTree($request);
        if (!isset($GLOBALS["SL"]->xmlTree["coreTbl"])) return $this->redir('/');
        $this->v["nestedNodes"] = $this->genXmlSchemaNode($this->xmlMapTree->rootID, $this->xmlMapTree->nodeTiers);
        $view = view('vendor.survloop.admin.tree.xml-schema', $this->v)->render();
        return Response::make($view, '200')->header('Content-Type', 'text/xml');
    }
    
    public function genXmlSchemaNode($nID, $nodeTiers, $overV = [])
    {
        $v = [];
        if (sizeof($overV) > 0) $v = $overV;
        else $v = $this->getXmlTmpV($nID);
        $v["kids"] = '';
        if ($v["tblHelp"] && sizeof($v["tblHelp"]) > 0) {
            foreach ($v["tblHelp"] as $help) {
                $nextV = $this->getXmlTmpV(-3, $help);
                if (isset($v["tblHelpFld"][$help]->FldName)) {
                    $v["kids"] .= '<xs:element name="' . $nextV["tbl"] . '" minOccurs="0">
                        <xs:complexType mixed="true"><xs:sequence>
                            <xs:element name="' . $v["tblHelpFld"][$help]->FldName 
                            . '" minOccurs="0" maxOccurs="unbounded" />
                        </xs:sequence></xs:complexType>
                    </xs:element>' . "\n";
                }
            }
        }
        for ($i = 0; $i < sizeof($nodeTiers[1]); $i++) {
            $v["kids"] .= $this->genXmlSchemaNode($nodeTiers[1][$i][0], $nodeTiers[1][$i]);
        }
        return view('vendor.survloop.admin.tree.xml-schema-node', $v )->render();
    }
    
    public function genXmlReport(Request $request)
    {
        if (!isset($GLOBALS["SL"]->xmlTree["coreTbl"])) return $this->redir('/xml-schema');
        if (!isset($this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]]) 
            || empty($this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]])) {
            return $this->redir('/xml-schema');
        }
        $this->v["nestedNodes"] = $this->genXmlReportNode($this->xmlMapTree->rootID, $this->xmlMapTree->nodeTiers, 
            $this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]][0]);
        if (trim($this->v["nestedNodes"]) == '') return $this->redir('/xml-schema');
        $view = view('vendor.survloop.admin.tree.xml-report', $this->v)->render();
        return Response::make($view, '200')->header('Content-Type', 'text/xml');
    }
    
    public function genXmlReportNode($nID, $nodeTiers, $rec, $overV = [])
    {
        $v = [];
        if (sizeof($overV) > 0) $v = $overV;
        else $v = $this->getXmlTmpV($nID);
        $v["rec"]     = $rec;
        $v["recFlds"] = [];
        if (sizeof($v["tblFlds"]) > 0) {
            foreach ($v["tblFlds"] as $i => $fld) {
                //if (!$this->checkValEmpty($fld->FldType, $rec->{ $v["tblAbbr"] . $fld->FldName })) {
                    $v["recFlds"][$fld->FldID] = $this->genXmlFormatVal($rec, $fld, $v["tblAbbr"]);
                //}
            }
        }
        $v["kids"] = '';
        if (is_array($v["tblHelp"]) && sizeof($v["tblHelp"]) > 0) {
            foreach ($v["tblHelp"] as $help) {
                $nextV = $this->getXmlTmpV(-3, $help);
                $kidRows = $this->sessData->getChildRows($v["tbl"], $rec->getKey(), $nextV["tbl"]);
                if ($kidRows && sizeof($kidRows) > 0) {
                    if (intVal($nextV["tblID"]) > 0 && $nextV["TblOpts"]%5 > 0) {
                        $v["kids"] .= '<' . $nextV["tbl"] . '>' . "\n";
                    }
                    foreach ($kidRows as $j => $kid) {
                        if (isset($v["tblHelpFld"][$help]->FldName)) {
                            //if (!$this->checkValEmpty($kid, 
                            //    $rec->{ $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->tbl[$help]] 
                            //        . $v["tblHelpFld"][$help] })) {
                                $v["kids"] .= '<' . $v["tblHelpFld"][$help]->FldName . '>' 
                                    . $this->genXmlFormatVal($kid, $v["tblHelpFld"][$help], 
                                        $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->tbl[$help]])
                                . '</' . $v["tblHelpFld"][$help]->FldName . '>' . "\n";
                            //}
                        }
                    }
                    if (intVal($nextV["tblID"]) > 0 && $nextV["TblOpts"]%5 > 0) {
                        $v["kids"] .= '</' . $nextV["tbl"] . '>' . "\n";
                    }
                }
            }
        }
        for ($i = 0; $i < sizeof($nodeTiers[1]); $i++) {
            $tbl2 = $this->xmlMapTree->getNodeTblName($nodeTiers[1][$i][0]);
            $kidRows = $this->sessData->getChildRows($v["tbl"], $rec->getKey(), $tbl2);
            if ($kidRows && sizeof($kidRows) > 0) {
                $nextV = $this->getXmlTmpV($nodeTiers[1][$i][0]);
                if (intVal($nextV["tblID"]) > 0 && $nextV["TblOpts"]%5 > 0) {
                    $v["kids"] .= '<' . $nextV["tbl"] . '>' . "\n";
                }
                foreach ($kidRows as $j => $kid) {
                    $v["kids"] .= $this->genXmlReportNode($nodeTiers[1][$i][0], $nodeTiers[1][$i], $kid);
                }
                if (intVal($nextV["tblID"]) > 0 && $nextV["TblOpts"]%5 > 0) {
                    $v["kids"] .= '</' . $nextV["tbl"] . '>' . "\n";
                }
            }
        }
        return view('vendor.survloop.admin.tree.xml-report-node', $v )->render();
    }
    
    // FldOpts %1 XML Public Data; %7 XML Private Data; %11 XML Sensitive Data; %13 XML Internal Use Data
    public function checkViewDataPerms($fld)
    {
        if ($fld && isset($fld->FldOpts) && intVal($fld->FldOpts) > 0) {
            if ($fld->FldOpts%7 > 0 && $fld->FldOpts%11 > 0 && $fld->FldOpts%13 > 0) return true;
            if (in_array($GLOBALS["SL"]->x["pageView"], ['full', 'full-pdf', 'full-xml'])) return true;
            if ($fld->FldOpts%13 == 0 || $fld->FldOpts%11 == 0) return false;
            return true;
        }
        return false;
    }
    
    public function checkFldDataPerms($fld)
    {
        if ($fld && isset($fld->FldOpts) && intVal($fld->FldOpts) > 0) {
            if ($fld->FldOpts%7 > 0 && $fld->FldOpts%11 > 0 && $fld->FldOpts%13 > 0) return true;
            if ($GLOBALS["SL"]->x["dataPerms"] == 'internal') return true;
            elseif ($fld->FldOpts%13 == 0) return false;
            if ($fld->FldOpts%11 == 0) return ($GLOBALS["SL"]->x["dataPerms"] == 'sensitive');
            if ($fld->FldOpts%7 == 0) return in_array($GLOBALS["SL"]->x["dataPerms"], ['private', 'sensitive']);
        }
        return false;
    }
    
    public function genXmlFormatVal($rec, $fld, $abbr)
    {
        $val = false;
        if ($this->checkFldDataPerms($fld) && $this->checkViewDataPerms($fld)
            && isset($rec->{ $abbr . $fld->FldName })) {
            $val = $rec->{ $abbr . $fld->FldName };
            if (strpos($fld->FldValues, 'Def::') !== false) {
                if (intVal($val) > 0) {
                    $val = $GLOBALS["SL"]->def->getVal(str_replace('Def::', '', $fld->FldValues), $val);
                } else {
                    $val = false;
                }
            } else { // not pulling values from a definition set
                if (in_array($fld->FldType, array('INT', 'DOUBLE'))) {
                    if (intVal($val) == 0) $val = false;
                } elseif (in_array($fld->FldType, array('VARCHAR', 'TEXT'))) {
                    if (trim($val) == '') {
                        $val = false;
                    } else {
                        if ($val != htmlspecialchars($val, ENT_XML1, 'UTF-8')) {
                            $val = '<![CDATA[' . $val . ']]>'; // !in_array($val, array('Y', 'N', '?'))
                        }
                    }
                } elseif ($fld->FldType == 'DATETIME') {
                    if ($val == '0000-00-00 00:00:00' || $val == '1970-01-01 00:00:00') return '';
                    $val = str_replace(' ', 'T', $val);
                } elseif ($fld->FldType == 'DATE') {
                    if ($val == '0000-00-00' || $val == '1970-01-01') return '';
                }
            }
        }
        return $val;
    }
    
    public function checkValEmpty($fldType, $val)
    {
        $val = trim($val);
        if ($fldType == 'DATE' && ($val == '' || $val == '0000-00-00' || $val == '1970-01-01')) {
            return true;
        } elseif ($fldType == 'DATETIME' 
            && ($val == '' || $val == '0000-00-00 00:00:00' || $val == '1970-01-01 00:00:00')) {
            return true;
        }
        return false;
    }
    
    
    
    
    public function runAjaxChecks(Request $request, $over = '')
    {
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
    
    protected function isStepUpload()
    {
        return (in_array($this->REQstep, ['upload', 'uploadDel', 'uploadSave']));
    }
    
    public function loadNodeURL(Request $request, $nodeSlug = '')
    {
        if (trim($nodeSlug) != '') $this->setNodeURL($nodeSlug);
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
        if (trim($ret) != '') return $ret;
        $ret = $this->ajaxChecksSL($request, $type);
        if (trim($ret) != '') return $ret;
        return $this->index($request, 'ajaxChecks');
    }
    
    public function ajaxChecksSL(Request $request, $type = '')
    {
        $this->survLoopInit($request, '/ajadm/' . $type);
        $nID = (($request->has('nID')) ? trim($request->get('nID')) : '');
        if ($type == 'color-pick') {
            $fldName = (($request->has('fldName')) ? trim($request->get('fldName')) : '');
            $preSel = (($request->has('preSel')) ? '#' . trim($request->get('preSel')) : '');
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
                            if ($sty->DefDescription == $preSel) $isCustom = false;
                        }
                    }
                }
                return view('vendor.survloop.inc-color-picker-ajax', [
                    "sysColors" => $sysColors,
                    "fldName"   => $fldName,
                    "preSel"    => $preSel,
                    "isCustom"  => $isCustom
                ]);
            }
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
        }
        return '';
    }
    
    public function ajaxChecksCustom(Request $request, $type = '')
    {
        return '';
    }
    
    public function byID(Request $request, $coreID, $coreSlug = '', $skipWrap = false, $skipPublic = false)
    {
        $this->survLoopInit($request, '/report/' . $coreID);
        if (!$skipPublic) $coreID = $GLOBALS["SL"]->chkInPublicID($coreID);
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        if ($request->has('hideDisclaim') && intVal($request->hideDisclaim) == 1) $this->hideDisclaim = true;
        $this->v["isPublicRead"] = true;
        $this->v["content"] = $this->printFullReport();
        if ($skipWrap) return $this->v["content"];
        $this->v["footOver"] = $this->printNodePageFoot();
        return $GLOBALS["SL"]->swapSessMsg(view('vendor.survloop.master', $this->v)->render());
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
            $this->searcher->getSearchFilts();
            $this->searcher->processSearchFilts();
            if (sizeof($this->searcher->allPublicFiltIDs) > 0) {
                foreach ($this->searcher->allPublicFiltIDs as $i => $coreID) {
                    if (!isset($this->searchOpts["limit"]) || intVal($this->searchOpts["limit"]) == 0
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
        return $ret;
    }
    
    public function printReportsRecord($coreID = -3, $full = true)
    {
        if (!$this->isPublished($GLOBALS["SL"]->coreTbl, $coreID) && !$this->isCoreOwner($coreID)
            && (!$this->v["user"] || !$this->v["user"]->hasRole('administrator|staff'))) {
            return $this->unpublishedMessage($GLOBALS["SL"]->coreTbl);
        }
        if ($full) {
            return '<div class="reportWrap">' . $this->byID($GLOBALS["SL"]->REQ, $coreID, '', true, true) . '</div>';
        }
        return $this->printReportsPrev($coreID);
    }
    
    public function printReportsRecordPublic($coreID = -3, $full = true)
    {
        if (!$this->isPublishedPublic($GLOBALS["SL"]->coreTbl, $coreID) && !$this->isCoreOwnerPublic($coreID)
            && (!$this->v["user"] || !$this->v["user"]->hasRole('administrator|staff'))) {
            return $this->unpublishedMessage($GLOBALS["SL"]->coreTbl);
        }
        if ($full) {
            return '<div class="reportWrap">' . $this->byID($GLOBALS["SL"]->REQ, $coreID, '', true) . '</div>';
        }
        return $this->printReportsPrev($coreID);
    }
    
    public function printReportsPrev($coreID = -3)
    {
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        return '<div id="reportPreview' . $coreID . '" class="reportPreview">' . $this->printPreviewReport() . '</div>';
    }
    
    public function unpublishedMessage($coreTbl = '')
    {
        if ($this->corePublicID <= 0) return '<!-- -->';
        return '<div class="well well-lg">#' . $this->corePublicID . ' is no longer published.</div>';
    }
    
    public function xmlAllAccess() { return true; }
    
    public function xmlAll(Request $request)
    {
        $this->survLoopInit($request, '/' . $GLOBALS["SL"]->treeRow->TreeSlug . '-xml-all');
        if (!$this->xmlAllAccess()) return 'Sorry, access not permitted.';
        $this->loadXmlMapTree($request);
        $this->v["nestedNodes"] = '';
        $this->getAllPublicCoreIDs($GLOBALS["SL"]->xmlTree["coreTbl"]);
        if (sizeof($this->allPublicCoreIDs) > 0) {
            foreach ($this->allPublicCoreIDs as $coreID) {
                $this->loadAllSessData($GLOBALS["SL"]->xmlTree["coreTbl"], $coreID);
                if (isset($this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]]) 
                    && sizeof($this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]]) > 0) {
                    $this->v["nestedNodes"] .= $this->genXmlReportNode($this->xmlMapTree->rootID, 
                        $this->xmlMapTree->nodeTiers, $this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]][0]);
                }
            }
        }
        $view = view('vendor.survloop.admin.tree.xml-report', $this->v)->render();
        return Response::make($view, '200')->header('Content-Type', 'text/xml');
    }
    
    public function xmlByID(Request $request, $coreID, $coreSlug = '')
    {
        $this->survLoopInit($request, '/' . $GLOBALS["SL"]->treeRow->TreeSlug . '-report-xml/' . $coreID);
        $coreID = $GLOBALS["SL"]->chkInPublicID($coreID);
        $this->loadXmlMapTree($request);
        if ($GLOBALS["SL"]->xmlTree["coreTbl"] == $GLOBALS["SL"]->coreTbl) {
            $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
//echo 'version A<br />';
//echo '<pre>'; print_r($GLOBALS["SL"]->treeRow); echo '</pre>';
        } else { // XML core table is different from main tree
            $this->loadSessInfo($GLOBALS["SL"]->xmlTree["coreTbl"]);
            $this->loadAllSessData($GLOBALS["SL"]->xmlTree["coreTbl"], $coreID);
//echo 'version B<br />';
        }
//echo '<pre>'; print_r($this->sessData->dataSets); echo '</pre>';
        $this->maxUserView();
        $this->xmlMapTree->v["view"] = $GLOBALS["SL"]->x["pageView"];
        if (isset($GLOBALS["fullAccess"]) && $GLOBALS["fullAccess"] && $GLOBALS["SL"]->x["pageView"] != 'full') {
            $this->v["content"] = $this->errorDeniedFullXml();
            return view('vendor.survloop.master', $this->v);
        }
        return $this->genXmlReport($request);
    }
    
    public function getXmlExample(Request $request)
    {
        $this->survLoopInit($request, '/' . $GLOBALS["SL"]->treeRow->TreeSlug . '-xml-example');
        $coreID = 1;
        if (isset($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->xmlTree["id"] . "-example"])) {
            $coreID = intVal($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->xmlTree["id"] . "-example"]);
        } elseif (isset($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID . "-example"])) {
            $coreID = intVal($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID . "-example"]);
        }
        eval("\$chk = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->xmlTree["coreTbl"]) . "::find(" . $coreID . ");");
        if ($chk) {
            return $this->xmlByID($request, $coreID);
        }
        return $this->redir('/xml-schema');
    }
    
    protected function genRecDump($coreID)
    {
        $this->loadXmlMapTree($GLOBALS["SL"]->REQ);
        if ($GLOBALS["SL"]->xmlTree["coreTbl"] == $GLOBALS["SL"]->coreTbl) {
            $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        } else { // XML core table is different from main tree
            $this->loadSessInfo($GLOBALS["SL"]->xmlTree["coreTbl"]);
            $this->loadAllSessData($GLOBALS["SL"]->xmlTree["coreTbl"], $coreID);
        }
        if (!isset($this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]])) return false;
        $dump = $this->genRecDumpNode($this->xmlMapTree->rootID, $this->xmlMapTree->nodeTiers, 
            $this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]][0]);
        $dump .= $this->genRecDumpXtra();
//echo 'dump: <textarea style="width: 100%; height: 300px;">' . $dump . '</textarea><br />'; exit;
        $dumpRec = new SLSearchRecDump;
        $dumpRec->SchRecDmpTreeID  = $this->treeID;
        $dumpRec->SchRecDmpRecID   = $coreID;
        $dumpRec->SchRecDmpRecDump = utf8_encode($GLOBALS["SL"]->stdizeChars(str_replace('  ', ' ', trim($dump))));
        $dumpRec->save();
        return true;
    }
    
    public function genRecDumpNode($nID, $nodeTiers, $rec, $overV = [])
    {
        $ret = '';
        $v = $this->getXmlTmpV($nID);
        if (sizeof($v["tblFlds"]) > 0) {
            foreach ($v["tblFlds"] as $i => $fld) {
                $ret .= ' ' . $this->genXmlFormatVal($rec, $fld, $v["tblAbbr"]);
            }
        }
        if (sizeof($v["tblHelp"]) > 0) {
            foreach ($v["tblHelp"] as $help) {
                $nextV = $this->getXmlTmpV(-3, $help);
                $kidRows = $this->sessData->getChildRows($v["tbl"], $rec->getKey(), $nextV["tbl"]);
                if (sizeof($kidRows) > 0) {
                    foreach ($kidRows as $j => $kid) {
                        $ret .= ' ' . $this->genXmlFormatVal($kid, $v["tblHelpFld"][$help], 
                            $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->tbl[$help]]);
                    }
                }
            }
        }
        for ($i = 0; $i < sizeof($nodeTiers[1]); $i++) {
            $tbl2 = $this->xmlMapTree->getNodeTblName($nodeTiers[1][$i][0]);
            $kidRows = $this->sessData->getChildRows($v["tbl"], $rec->getKey(), $tbl2);
            if (sizeof($kidRows) > 0) {
                $nextV = $this->getXmlTmpV($nodeTiers[1][$i][0]);
                foreach ($kidRows as $j => $kid) {
                    $ret .= ' ' . $this->genRecDumpNode($nodeTiers[1][$i][0], $nodeTiers[1][$i], $kid);
                }
            }
        }
        return $ret;
    }
    
    protected function genRecDumpXtra()
    {
        return '';
    }
    
    protected function reloadStats($coreIDs = [])
    {
        return true;
    }
    
    public function retrieveUpload(Request $request, $cid = -3, $upID = '')
    {
        if ($cid <= 0) return '';
        $this->survLoopInit($request, '');
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $cid);
        return $this->retrieveUploadFile($upID);
    }
    
    public function ajaxGraph(Request $request, $gType = '', $nID = -3)
    {
        $this->survLoopInit($request, '');
        $this->v["currNode"] = new TreeNodeSurv;
        $this->v["currNode"]->fillNodeRow($nID);
        $this->v["currGraphID"] = 'nGraph' . $nID;
        if ($this->v["currNode"] && isset($this->v["currNode"]->nodeRow->NodeID) && trim($gType) != '') {
            $this->getAllPublicCoreIDs();
            $this->searcher->getSearchFilts();
            $this->searcher->processSearchFilts();
            $this->v["graphDataPts"] = $this->v["graphMath"] = $rows = $rowsFilt = [];
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
                        $lab1Fld = (($lab1Rec && isset($lab1Rec->FldName)) ? $tblAbbr . $lab1Rec->FldName : '');
                        $lab2Fld = (($lab2Rec && isset($lab2Rec->FldName)) ? $tblAbbr . $lab2Rec->FldName : '');
                        if ($tbl == $GLOBALS["SL"]->coreTbl) {
                            eval("\$rows = " . $GLOBALS["SL"]->modelPath($tbl) . "::select('" . $tblAbbr . "ID', '" 
                                . $fldName . "'" . ((trim($lab1Fld) != '') ? ", '" . $lab1Fld . "'" : "") 
                                . ((trim($lab2Fld) != '') ? ", '" . $lab2Fld . "'" : "") . ")->where('" . $fldName 
                                . "', 'NOT LIKE', '')->where('" . $fldName . "', 'NOT LIKE', 0)->whereIn('" . $tblAbbr 
                                . "ID', \$this->searcher->allPublicFiltIDs)->orderBy('" . $fldName 
                                . "', 'asc')->get();");
                        } else {
                            //eval("\$rows = " . $GLOBALS["SL"]->modelPath($tbl) . "::orderBy('" . $isBigSurvLoop[1] 
                            //    . "', '" . $isBigSurvLoop[2] . "')->get();");
                        }
                        if ($rows->isNotEmpty()) {
                            if (isset($this->v["currNode"]->extraOpts["conds"]) 
                                && strpos('#', $this->v["currNode"]->extraOpts["conds"]) !== false) {
                                $this->loadCustLoop($request);
                                foreach ($rows as $i => $row) {
                                    $this->custReport->loadAllSessData($GLOBALS["SL"]->coreTbl, $row->getKey());
                                    if ($this->custReport->chkConds($this->v["currNode"]->extraOpts["conds"])) {
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
                                    = $this->v["graphMath"]["absMax"]-$this->v["graphMath"]["absMin"];
                                foreach ($rows as $i => $row) {
                                    $lab = '';
                                    if (trim($lab1Fld) != '' && isset($row->{ $lab1Fld })) { 
                                       $lab .= (($lab1Rec->FldType == 'DOUBLE') 
                                           ? $GLOBALS["SL"]->sigFigs($row->{ $lab1Fld }) : $row->{ $lab1Fld }) . ' ';
                                       if (trim($lab2Fld) != '' && isset($row->{ $lab2Fld })) { 
                                           $lab .= (($lab2Rec->FldType == 'DOUBLE') 
                                               ? $GLOBALS["SL"]->sigFigs($row->{ $lab2Fld }) : $row->{ $lab2Fld }) .' ';
                                       }
                                    }
                                    $perc = ((1+$i)/sizeof($rows));
                                    $this->v["graphDataPts"][] = [
                                        "id"  => $row->getKey(),
                                        "val" => (($fldRec->FldType == 'DOUBLE') 
                                            ? $GLOBALS["SL"]->sigFigs($row->{ $fldName }, 4) : $row->{ $fldName }), 
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
                return view('vendor.survloop.graph-bar', $this->v);
            }
        }
        $this->v["graphFail"] = true;
        return view('vendor.survloop.graph-bar', $this->v);
    }
    
    public function printAdminReport($coreID)
    {
        $this->v["cID"] = $coreID;
        return $this->printFullReport('', true);
    }
    
    public function printFullReport($reportType = '', $isAdmin = false, $inForms = false)
    {
        return '';
    }
    
    public function printPreviewReportCustom($isAdmin = false) { return ''; }
    
    public function printPreviewReport($isAdmin = false)
    {
        $ret = $this->printPreviewReportCustom($isAdmin);
        if (trim($ret) != '') return $ret;
        $fldNames = $found = [];
        if (sizeof($this->nodesRawOrder) > 0) {
            foreach ($this->nodesRawOrder as $i => $nID) {
                if (isset($this->allNodes[$nID]) && $this->allNodes[$nID]->isRequired()) {
                    $tblFld = $this->allNodes[$nID]->getTblFld();
                    $fldNames[] = [ $tblFld[0], $tblFld[1] ];
                }
            }
        }
        if (sizeof($fldNames) > 0) {
            foreach ($fldNames as $i => $fld) {
                if (isset($this->sessData->dataSets[$fld[0]]) && sizeof($this->sessData->dataSets[$fld[0]]) > 0
                    && isset($this->sessData->dataSets[$fld[0]][0]->{ $fld[1] }) && sizeof($found) < 6) {
                    $found[] = $fld[1];
                    $ret .= '<span class="mR20">' . $this->sessData->dataSets[$fld[0]][0]->{ $fld[1] } . '</span>';
                }
            }
        }
        return $ret;
    }
    
    public function wordLimitDotDotDot($str, $wordLimit = 50)
    {
        $strs = $GLOBALS["SL"]->mexplode(' ', $str);
        if (sizeof($strs) <= $wordLimit) return $str;
        $ret = '';
        for ($i=0; $i<$wordLimit; $i++) $ret .= $strs[$i] . ' ';
        return $ret . '...';
    }
    
    public function ajaxEmojiTag(Request $request, $recID = -3, $defID = -3)
    {
        if ($recID <= 0) return '';
        $this->survLoopInit($request, '');
        if ($this->v["uID"] <= 0) return '<h4><i>Please <a href="/login">Login</a></i></h4>';
        $this->loadSessionData($GLOBALS["SL"]->coreTbl, $recID);
        $this->loadEmojiTags($defID);
        if (sizeof($GLOBALS["SL"]->treeSettings['emojis']) > 0 && $recID > 0) {
            foreach ($GLOBALS["SL"]->treeSettings['emojis'] as $i => $emo) {
                if ($emo["id"] == $defID) {
                    if (isset($this->emojiTagUsrs[$emo["id"]]) 
                        && in_array($this->v["uID"], $this->emojiTagUsrs[$emo["id"]])) {
                        SLSessEmojis::where('SessEmoRecID', $this->coreID)
                            ->where('SessEmoDefID', $emo["id"])
                            ->where('SessEmoTreeID', $this->treeID)
                            ->where('SessEmoUserID', $this->v["uID"])
                            ->delete();
                        $this->emojiTagOff($emo["id"]);
                    } else {
                        $newTag = new SLSessEmojis;
                        $newTag->SessEmoRecID  = $this->coreID;
                        $newTag->SessEmoDefID  = $emo["id"];
                        $newTag->SessEmoTreeID = $this->treeID;
                        $newTag->SessEmoUserID = $this->v["uID"];
                        $newTag->save();
                        $this->emojiTagOn($emo["id"]);
                    }
                }
            }
            $this->loadEmojiTags($defID);
        }
        $isActive = false;
        if (isset($GLOBALS["SL"]->treeSettings['emojis']) && sizeof($GLOBALS["SL"]->treeSettings["emojis"]) > 0) {
            foreach ($GLOBALS["SL"]->treeSettings["emojis"] as $emo) {
                if ($emo["id"] == $defID) {
                    if ($this->v["uID"] > 0 && isset($this->emojiTagUsrs[$defID])
                        && in_array($this->v["uID"], $this->emojiTagUsrs[$defID])) $isActive = true;
                    return view('vendor.survloop.inc-emoji-tag', [
                        "spot"     => 't' . $this->treeID . 'r' . $this->coreID, 
                        "emo"      => $emo, 
                        "cnt"      => sizeof($this->emojiTagUsrs[$defID]),
                        "isActive" => $isActive
                    ])->render();
                }
            }
        }
        return '';
    }
    
    public function emojiTagOn($defID = -3)
    {
        return true;
    }
    
    public function emojiTagOff($defID = -3)
    {
        return true;
    }
    
    protected function loadEmojiTags($defID = -3)
    {
        if (isset($GLOBALS["SL"]->treeSettings['emojis']) 
            && sizeof($GLOBALS["SL"]->treeSettings['emojis']) > 0 && $this->coreID > 0) {
            foreach ($GLOBALS["SL"]->treeSettings['emojis'] as $emo) {
                if ($defID <= 0 || $emo["id"] == $defID) {
                    $this->emojiTagUsrs[$emo["id"]] = [];
                    $chk = SLSessEmojis::where('SessEmoRecID', $this->coreID)
                        ->where('SessEmoDefID', $emo["id"])
                        ->where('SessEmoTreeID', $this->treeID)
                        ->get();
                    if ($chk->isNotEmpty()) {
                        foreach ($chk as $tag) $this->emojiTagUsrs[$emo["id"]][] = $tag->SessEmoUserID;
                    }
                }
            }
        }
        return true;
    }
    
    protected function printEmojiTags()
    {
        $ret = '';
        $this->loadEmojiTags();
        if (isset($GLOBALS["SL"]->treeSettings['emojis']) && sizeof($GLOBALS["SL"]->treeSettings['emojis']) > 0 
            && $this->coreID > 0) {
            $admPower = ($this->v["user"] && $this->v["user"]->hasRole('administrator|staff'));
            $spot = 't' . $this->treeID . 'r' . $this->coreID;
            foreach ($GLOBALS["SL"]->treeSettings['emojis'] as $emo) {
                if (!$emo["admin"] || $admPower) {
                    $GLOBALS["SL"]->pageAJAX .= '$(document).on("click", "#' . $spot . 'e' . $emo["id"] 
                        . '", function() { $("#' . $spot . 'e' . $emo["id"] . 'Tag").load("/ajax-emoji-tag/' 
                        . $this->treeID . '/' . $this->coreID . '/' . $emo["id"] . '/"); });' . "\n";
                }
            }
            $ret .= view('vendor.survloop.inc-emoji-tags', [
                "spot"     => $spot, 
                "emojis"   => $GLOBALS["SL"]->treeSettings["emojis"], 
                "users"    => $this->emojiTagUsrs,
                "uID"      => (($this->v["uID"] > 0) ? $this->v["uID"] : -3),
                "admPower" => $admPower
            ])->render();
        }
        return $ret;
    }
    
    protected function fillGlossary()
    {
        $this->v["glossaryList"] = [];
        return true;
    }
    
    protected function printGlossary()
    {
        if (!isset($this->v["glossaryList"]) || sizeof($this->v["glossaryList"]) == 0) $this->fillGlossary();
        if (sizeof($this->v["glossaryList"]) > 0) {
            $ret = '<h3 class="mT0 mB20 slBlueDark">Glossary of Terms</h3><div class="glossaryList">';
            foreach ($this->v["glossaryList"] as $i => $gloss) {
                $ret .= '<div class="row' . (($i%2 == 0) ? ' row2' : '') . ' pT15 pB15"><div class="col-md-3">' 
                    . $gloss[0] . '</div><div class="col-md-9">' . ((isset($gloss[1])) ? $gloss[1] : '') 
                    . '</div></div>';
            }
            return $ret . '</div>';
        }
        return '';
    }
    
    protected function swapSeo($str)
    {
        return str_replace('[coreID]', $this->corePublicID, str_replace('[cID]', $this->corePublicID, 
            str_replace('#1111', '#' . $this->corePublicID, $str)));
    }
    
    protected function runPageLoad($nID)
    {
        if (!$this->isPage && $GLOBALS["SL"]->treeRow->TreeOpts%13 == 0) { // report
            $GLOBALS["SL"]->sysOpts['meta-title'] = $this->swapSeo($GLOBALS["SL"]->sysOpts['meta-title']);
            $GLOBALS["SL"]->sysOpts['meta-desc'] = $this->swapSeo($GLOBALS["SL"]->sysOpts['meta-desc']);
        }
        return true;
    }
    
    protected function runPageExtra($nID) { return true; }
    
    protected function printNodePageFoot()
    {
        return (isset($GLOBALS["SL"]->sysOpts["footer-master"]) ? $GLOBALS["SL"]->sysOpts["footer-master"] : '');
    }
    
    protected function printCurrRecMgmt()
    {
        $recDesc = '';
        if (isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl]) 
            && sizeof($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl]) > 0) {
            $recDesc = trim($this->getTableRecLabel($GLOBALS["SL"]->coreTbl, 
                $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]));
        }
        return view('vendor.survloop.formfoot-record-mgmt', [
            "coreID"          => $this->coreID,
            "treeID"          => $this->treeID,
            "multipleRecords" => $this->v["multipleRecords"],
            "isUser"          => ($this->v["uID"] > 0),
            "recDesc"         => $recDesc
            ])->render();
    }
    
    protected function hasAncestPrintBlock($nID)
    {
        $found = false;
        if (isset($this->allNodes[$nID]) && isset($this->allNodes[$nID]->parentID)) {
            $parent = $this->allNodes[$nID]->parentID;
            while ($parent > 0 && isset($this->allNodes[$parent]) && isset($this->allNodes[$parent]->parentID)) {
                if ($this->allNodes[$parent]->isDataPrint()) $found = true;
                $parent = $this->allNodes[$parent]->parentID;
            }
        }
        return $found;
    }
    
    /******************************************************************************************************
    
    MAIN PUBLIC OUTPUT WHERE EVERYTHING HAPPENS: print public version of currNode
    
    ******************************************************************************************************/
    
    protected function hasAjaxWrapPrinting()
    {
        return (!$this->hasREQ && (!$GLOBALS["SL"]->REQ->has('ajax') || intVal($GLOBALS["SL"]->REQ->get('ajax')) == 0));
    }
    
    protected function hasFrameLoad()
    {
        return ($GLOBALS["SL"]->REQ->has('frame') && intVal($GLOBALS["SL"]->REQ->get('frame')) == 1);
    }
    
    protected function changeNodeID($nID, $newID)
    {
        
    }
    
    protected function errorDeniedFullPdf()
    {
        return '<br /><br /><center><h3>You are trying to access the complete details of a record which '
            . 'requires you to <a href="/login">login</a> as the owner, or an otherwise authorized user. '
            . '<br /><br />The public version of this complaint can be found here:<br />'
            . '<a href="/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/read-' . $this->coreID . '">' 
            . $GLOBALS["SL"]->sysOpts["app-url"] . '/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/read-' . $this->coreID 
            . '</a></h3></center>';
    }
    
    protected function errorDeniedFullXml()
    {
        return $this->errorDeniedFullPdf();
    }
    
    
}
