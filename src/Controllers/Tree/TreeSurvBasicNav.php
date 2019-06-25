<?php
/**
  * TreeSurvBasicNav is a mid-level class using a standard branching tree, mostly for SurvLoop's surveys and pages.
  * But it does house some of the core functions to print the whole of a tree.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers\Tree;

use DB;
use App\Models\SLNode;
use App\Models\SLNodeSavesPage;
use App\Models\SLSessLoops;
use SurvLoop\Controllers\Tree\TreeSurvProgBar;

class TreeSurvBasicNav extends TreeSurvProgBar
{
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
                                if (isset($res[2]) && $res[2]) {
                                    return 1;
                                }
                            }
                        }
                    }
                }
            }
        }
        return (($found) ? -1 : 0);
    }
    
    /*
    public function getNextPage($nID, $direction = 'next')
    {
        if ($nID == $GLOBALS["SL"]->treeRow->TreeLastPage && $direction == 'next') {
            return -37;
        }
        if (!$this->hasNode($nID)) {
            return -3;
        }
        
        
        $this->loopCnt = 0;
        while (!$this->isDisplayableNode($nID) && $this->loopCnt < 1000) {
            if ($nID == $GLOBALS["SL"]->treeRow->TreeLastPage && $direction == 'next') {
                return -37;
            }
            $nIDbranch = $this->checkBranchCondition($nID, $direction);
            if ($nID != $nIDbranch) {
                $nID = $nIDbranch; 
            } elseif ($direction == 'next') {
                $nID = $this->nextNode($nID);
            } else {
                $nID = $this->prevNode($nID);
            }
            $this->loopCnt++;
        }
        if (trim($this->loadingError) != '') {
            $ret .= '<div class="p10"><i>loadNodeSubTier() - ' . $this->loadingError . '</i></div>';
        }
        return $nID;
    }
    */
    
    public function getNextNonBranch($nID, $direction = 'next')
    {
        if ($nID == $GLOBALS["SL"]->treeRow->TreeLastPage && $direction == 'next') {
            return -37;
        }
        if (!$this->hasNode($nID)) {
            return -3;
        }
        $nIDbranch = $this->checkBranchCondition($nID, $direction);
        if ($nID != $nIDbranch) {
            $nID = $nIDbranch; 
        }
        $this->loopCnt = 0;
        while (!$this->isDisplayableNode($nID) && $this->loopCnt < 1000) {
            if ($nID == $GLOBALS["SL"]->treeRow->TreeLastPage && $direction == 'next') {
                return -37;
            }
            $nIDbranch = $this->checkBranchCondition($nID, $direction);
            if ($nID != $nIDbranch) {
                $nID = $nIDbranch; 
            } elseif ($direction == 'next') {
                $nID = $this->nextNode($nID);
            } else {
                $nID = $this->prevNode($nID);
            }
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
            if ($direction == 'next') {
                $nID = $this->nextNodeSibling($nID);
            } else {
                $nID = $this->prevNode($nID);
            }
        }
        return $nID;
    }
    
    protected function nodeIsWithinPage($nID)
    {
        $parent = $this->allNodes[$nID]->getParent();
        while ($this->hasNode($parent)) {
            if ($this->allNodes[$parent]->isPage()) {
                return true;
            }
            if ($this->allNodes[$parent]->isBranch() || $this->allNodes[$parent]->isLoopRoot()) {
                return false;
            }
            $parent = $this->allNodes[$parent]->getParent();
        }
        return false;
    }
    
    protected function hasAncestPrintBlock($nID)
    {
        $found = false;
        if (isset($this->allNodes[$nID]) && isset($this->allNodes[$nID]->parentID)) {
            $parent = $this->allNodes[$nID]->parentID;
            while ($parent > 0 && isset($this->allNodes[$parent]) && isset($this->allNodes[$parent]->parentID)) {
                if ($this->allNodes[$parent]->isDataPrint()) {
                    $found = true;
                }
                $parent = $this->allNodes[$parent]->parentID;
            }
        }
        return $found;
    }
    
    protected function isDisplayableNode($nID, $exception = '')
    {
        if (!$this->hasNode($nID) || !$this->checkNodeConditions($nID)) {
            return false;
        }
        if ($this->allNodes[$nID]->isDataManip() && !$this->nodeIsWithinPage($nID)) {
            $this->runDataManip($nID, true);
        }
        if (!$this->allNodes[$nID]->isPage() && !$this->allNodes[$nID]->isLoopRoot()) {
            return false;
        }
        if (!$this->checkParentBranchConditions($nID)) {
            return false;
        }
        return true;
    }
    
    protected function checkParentBranchConditions($nID)
    {
        $clear = true;
        $parentID = $this->allNodes[$nID]->getParent();
        while ($parentID > 0 && $clear) {
            if (!$this->checkNodeConditions($parentID)) {
                $clear = false;
            }
            $parentID = $this->allNodes[$parentID]->getParent();
        }
        return $clear;
    }
    
    protected function settingTheLoop($name, $itemID = -3, $rootJustLeft = 0)
    {
        if ($name == '') {
            return false; 
        }
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
    
    protected function afterCreateNewDataLoopItem($tbl, $itemID = -3)
    {
        return true;
    }
    
    public function pushCurrNodeURL($nID = -3)
    {
        if ($GLOBALS['SL']->treeRow->TreeType == 'Page') {
            return true;
        }
        if (intVal($nID) > 0 && isset($this->allNodes[$nID])) {
            $this->allNodes[$nID]->fillNodeRow();
            if (isset($this->allNodes[$nID]->nodeRow->NodePromptNotes) 
                && trim($this->allNodes[$nID]->nodeRow->NodePromptNotes) != '') {
                $this->pushCurrNodeVisit($nID);
                if ($this->hasREQ && ($GLOBALS["SL"]->REQ->has('ajax') || $GLOBALS["SL"]->REQ->has('frame'))) {
                    $title = $this->allNodes[$nID]->nodeRow->NodePromptText;
                    if (strpos($title, '</h1>') > 0) {
                        $title = substr($title, 0, strpos($title, '</h1>'));
                    } elseif (strpos($title, '</h2>') > 0) {
                        $title = substr($title, 0, strpos($title, '</h2>'));
                    } elseif (strpos($title, '</h3>') > 0) {
                        $title = substr($title, 0, strpos($title, '</h3>'));
                    }
                    $title = str_replace('"', '\\"', str_replace('(s)', '', strip_tags($title)));
                    $title = trim(preg_replace('/\s\s+/', ' ', $title));
                    $title = str_replace("\n", " ", $title);
                    if (trim($title) == '') {
                        $title = trim($GLOBALS['SL']->treeRow->TreeName);
                    }
                    if (strlen($title) > 40) {
                        $title = trim(substr($title, 0, 40)) . '...';
                    }
                    $this->v["currPage"]    = [];
                    $this->v["currPage"][1] = ((trim($title) != '') ? $title . ' - ' : '') 
                        . $GLOBALS["SL"]->sysOpts["site-name"];
                    $this->v["currPage"][0] = '/' . (($GLOBALS["SL"]->treeIsAdmin) ? 'dash' : 'u') . '/' 
                        . $GLOBALS["SL"]->treeRow->TreeSlug . '/' . $this->allNodes[$nID]->nodeRow->NodePromptNotes;
                    $GLOBALS["SL"]->pageJAVA .= 'setCurrPage("' . $this->v["currPage"][1] . '", "' . $this->v["currPage"][0] . '", ' . $this->currNode() . '); ';
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
    
    public function setNodeIdURL($nodeID = 0)
    {
        $chk = SLNode::find($nodeID);
        if ($chk && isset($chk->NodePromptNotes)) {
            $this->urlSlug = $chk->NodePromptNotes;
        }
        return true;
    }
    
    public function currNodeURL($nID = -3)
    {
        $curr = $this->currNode();
        if ($nID > 0) {
            $curr = $nID;
        }
        if (!isset($this->allNodes[$curr])) {
            return '';
        }
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
                    if ($loadNodeChk->isEmpty()) {
                        return false;
                    }
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
    
    protected function isStepUpload()
    {
        return (in_array($this->REQstep, ['upload', 'uploadDel', 'uploadSave']));
    }
    
}