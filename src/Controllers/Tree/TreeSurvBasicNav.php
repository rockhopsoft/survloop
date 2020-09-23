<?php
/**
  * TreeSurvBasicNav is a mid-level class using a standard branching tree, mostly for Survloop's surveys and pages.
  * But it does house some of the core functions to print the whole of a tree.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.19
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use DB;
use App\Models\SLNode;
use App\Models\SLNodeSavesPage;
use App\Models\SLSessLoops;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvProgBar;

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
        if ($nID == $GLOBALS["SL"]->treeRow->tree_last_page && $direction == 'next') {
            return -37;
        }
        if (!$this->hasNode($nID)) {
            return -3;
        }
        
        
        $this->loopCnt = 0;
        while (!$this->isDisplayableNode($nID) && $this->loopCnt < 1000) {
            if ($nID == $GLOBALS["SL"]->treeRow->tree_last_page && $direction == 'next') {
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
        if ($nID == $GLOBALS["SL"]->treeRow->tree_last_page && $direction == 'next') {
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
            if ($nID == $GLOBALS["SL"]->treeRow->tree_last_page 
                && $direction == 'next') {
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
            $ret .= '<div class="p10"><i>loadNodeSubTier - ' . $this->loadingError . '</i></div>';
        }
        return $nID;
    }
    
    protected function checkBranchCondition($nID, $direction = 'next')
    {
        if (isset($this->allNodes[$nID]) 
            && $this->allNodes[$nID]->isBranch() 
            && !$this->checkNodeConditions($nID)) {
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
        if (isset($this->allNodes[$nID]) 
            && isset($this->allNodes[$nID]->parentID)) {
            $parent = $this->allNodes[$nID]->parentID;
            while ($parent > 0 
                && isset($this->allNodes[$parent]) 
                && isset($this->allNodes[$parent]->parentID)) {
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
                if (!$found && $loop->sess_loop_name == $name) {
                    $loop->sess_loop_item_id = $itemID;
                    $loop->save();
                    $found = true;
                }
            }
        }
        if (!$found) {
            $newLoop = new SLSessLoops;
            $newLoop->sess_loop_sess_id = $this->sessID;
            $newLoop->sess_loop_name    = $name;
            $newLoop->sess_loop_item_id = $itemID;
            $newLoop->save();
        }
        if ($this->sessInfo) {
            $GLOBALS["SL"]->loadSessLoops($this->sessID);
            $this->sessInfo->sess_loop_root_just_left = $rootJustLeft;
            $this->sessInfo->save();
        }
        $this->runLoopConditions();
        return true;
    }
    
    protected function leavingTheLoop($name = '', $justClearID = false)
    {
        if (sizeof($GLOBALS["SL"]->sessLoops) > 0) {
            foreach ($GLOBALS["SL"]->sessLoops as $i => $loop) {
                if ($loop->sess_loop_name == $name || $name == '') {
                    if ($justClearID) {
                        $loop->sess_loop_item_id = -3;
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
        if ($GLOBALS['SL']->treeRow->tree_type == 'Page') {
            return true;
        }
        if (intVal($nID) > 0 && isset($this->allNodes[$nID])) {
            $this->allNodes[$nID]->fillNodeRow();
            if (isset($this->allNodes[$nID]->nodeRow->node_prompt_notes) 
                && trim($this->allNodes[$nID]->nodeRow->node_prompt_notes) != '') {
                $this->pushCurrNodeVisit($nID);
                if ($this->hasREQ 
                    && ($GLOBALS["SL"]->REQ->has('ajax') || $GLOBALS["SL"]->REQ->has('frame'))) {
                    $title = $this->allNodes[$nID]->nodeRow->node_prompt_text;
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
                        $title = trim($GLOBALS['SL']->treeRow->tree_name);
                    }
                    if (strlen($title) > 40) {
                        $title = trim(substr($title, 0, 40)) . '...';
                    }
                    $this->v["currPage"]    = [];
                    $this->v["currPage"][1] = ((trim($title) != '') ? $title . ' - ' : '') 
                        . $GLOBALS["SL"]->sysOpts["site-name"];
                    $this->v["currPage"][0] = '/' . (($GLOBALS["SL"]->treeIsAdmin) ? 'dash' : 'u') 
                        . '/' . $GLOBALS["SL"]->treeRow->tree_slug 
                        . '/' . $this->allNodes[$nID]->nodeRow->node_prompt_notes;
                    $GLOBALS["SL"]->pageJAVA .= 'setCurrPage("' . $this->v["currPage"][1] 
                        . '", "' . $this->v["currPage"][0] . '", ' . $this->currNode() . '); ';
                    $GLOBALS["SL"]->pageAJAX .= 'history.pushState( {}, "' 
                        . $this->v["currPage"][1] . '", "' . $this->v["currPage"][0] . '");' 
                        . "\n" . 'document.title="' . $this->v["currPage"][1] . '";' . "\n";
                }
            }
        }
        return true;
    }
    
    public function pushCurrNodeVisit($nID)
    {
        if (isset($this->sessInfo->sess_id) && $nID > 0 && !$GLOBALS["SL"]->REQ->has('preview')) {
            $pagsSave = new SLNodeSavesPage;
            $pagsSave->page_save_session = $this->sessInfo->sess_id;
            $pagsSave->page_save_node    = $nID;
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
        if ($chk && isset($chk->node_prompt_notes)) {
            $this->urlSlug = $chk->node_prompt_notes;
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
            && trim($this->allNodes[$curr]->nodeRow->node_prompt_notes) != '') {
            return '/' . (($GLOBALS["SL"]->treeIsAdmin) ? 'dash' : 'u') 
                . '/' . $GLOBALS["SL"]->treeRow->tree_slug 
                . '/' . $this->allNodes[$curr]->nodeRow->node_prompt_notes;
        }
        return '';
    }
    
    public function pullNewNodeURL()
    {
        if (trim($this->urlSlug) != '') {
            $loadNode = SLNode::where('node_tree', $this->treeID)
                ->where('node_prompt_notes', $this->urlSlug)
                ->where(function ($query) {
                    return $query->where('node_type', 'Page')
                        ->orWhere('node_type', 'Loop Root');
                })
                ->first();
            if ($loadNode && isset($loadNode->node_id)) {
                if (!$GLOBALS["SL"]->REQ->has('preview') 
                    && !$GLOBALS["SL"]->REQ->has('popStateUrl')) {
                    $loadNodeChk = DB::table('sl_node_saves_page')
                        ->join('sl_sess', 'sl_node_saves_page.page_save_session', 
                            '=', 'sl_sess.sess_id')
                        ->where('sl_sess.sess_tree', '=', $this->treeID)
                        ->where('sl_sess.sess_core_id', '=', $this->coreID)
                        ->where('sl_node_saves_page.page_save_node', $loadNode->node_id)
                        ->get();
                    if ($loadNodeChk->isEmpty()) {
                        return false;
                    }
                }
                // perhaps upgrade to check for loop item id first?
                //$this->leavingTheLoop();
                $prevNode = $this->currNode();
                $this->updateCurrNode($loadNode->node_id);
                if (sizeof($GLOBALS["SL"]->dataLoops) > 0 
                    && sizeof($GLOBALS["SL"]->sessLoops) > 0) {
                    foreach ($GLOBALS["SL"]->sessLoops as $sessLoop) {
                        foreach ($GLOBALS["SL"]->dataLoops as $loop) {
                            if ($sessLoop->sess_loop_name == $loop->data_loop_plural
                                && isset($this->allNodes[$loop->data_loop_root])) {
                                $path = $this->allNodes[$loop->data_loop_root]->nodeTierPath;
                                if (isset($this->allNodes[$prevNode]) 
                                    && $this->allNodes[$prevNode]->checkBranch($path)
                                    && !$this->allNodes[$this->currNode()]->checkBranch($path)) {
                                    $this->leavingTheLoop($loop->data_loop_plural);
                                }
                            }
                        }
                    }
                }
                if ($loadNode->node_type == 'Loop Root') {
                    $this->checkLoopsPostProcessing($loadNode->node_id, $prevNode);
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