<?php
/**
  * TreeCoreSess is a mid-level class handling the session controls for TreeCore.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.1.2
  */
namespace SurvLoop\Controllers\Tree;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLSess;
use App\Models\SLSessLoops;
use App\Models\SLTokens;
use SurvLoop\Controllers\Tree\SurvData;
use SurvLoop\Controllers\Tree\TreeCore;

class TreeCoreSess extends TreeCore
{
    /*****************
    // Some More Generalized Session Processes
    *****************/
    protected function loadSessInfo($coreTbl = '')
    {
        if (!isset($this->v["currPage"])) {
            $this->survLoopInit($GLOBALS["SL"]->REQ); // not sure why this
        }
        if (isset($GLOBALS["SL"]->formTree->tree_id)) {
            return false;
        }
        if (trim($coreTbl) == '') {
            $coreTbl = $GLOBALS["SL"]->coreTbl;
        }
        
        // If we're loading a Page that doesn't even have a Core Table, 
        // then we skip all the session checks...
        $noCoreTbl = (!isset($GLOBALS["SL"]->treeRow->tree_core_table) 
            || intVal($GLOBALS["SL"]->treeRow->tree_core_table) <= 0);
        if ($noCoreTbl
            && isset($GLOBALS["SL"]->treeRow->tree_type) 
            && $GLOBALS["SL"]->treeRow->tree_type == 'Page') {
            $this->sessInfo = new SLSess;
            $this->sessID = $this->coreID = -3;
            $GLOBALS["SL"]->setClosestLoop();
            return false;
        }

        $uID = ((isset($this->v["uID"])) ? $this->v["uID"] : 0);
        $cid = 0; 
        if ($GLOBALS["SL"]->REQ->has('cid') 
            && intVal($GLOBALS["SL"]->REQ->get('cid')) > 0) {
            $cid = intVal($GLOBALS["SL"]->REQ->get('cid'));
        }
        if ($GLOBALS["SL"]->REQ->has('start') 
            && $GLOBALS["SL"]->REQ->has('new')) {
            $treeNew = 't' . $GLOBALS["SL"]->treeID 
                . 'new' . $GLOBALS["SL"]->REQ->get('new');
            if (!session()->has($treeNew)) {
                $this->createNewSess();
                $this->newCoreRow($coreTbl);
                session()->put($treeNew, time());
                session()->save();
            }
        } elseif ($GLOBALS["SL"]->REQ->has('core') 
            && intVal($GLOBALS["SL"]->REQ->get('core')) > 0) {
            $this->sessInfo = SLSess::where('sess_user_id', $uID)
                ->where('sess_tree', $GLOBALS["SL"]->sessTree) //$this->treeID)
                ->where('sess_core_id', '=', intVal($GLOBALS["SL"]->REQ->get('core')))
                ->orderBy('updated_at', 'desc')
                ->first();
            if ($this->sessInfo && isset($this->sessInfo->sess_id)) {
                $this->sessID = $this->sessInfo->sess_id;
                $this->coreID = $this->sessInfo->sess_core_id;
            }
        } elseif (isset($this->v) && $uID > 0) {
            $this->chkUserTreeSess($coreTbl, $cid);
        } else {
            $this->chkTreeSess($GLOBALS["SL"]->sessTree);
        }
        // Check for and load core record's ID
        if ($this->coreID <= 0 
            && $this->sessInfo 
            && isset($this->sessInfo->sess_core_id) 
            && intVal($this->sessInfo->sess_core_id) > 0) {
            $this->coreID = $this->sessInfo->sess_core_id;
        }
        $this->chkIfCoreIsEditable($this->coreID);
        if ($this->coreID <= 0 && $uID > 0) {
            $pastUserSess = SLSess::where('sess_user_id', $uID)
                ->where('sess_tree', $this->treeID)
                ->where('sess_core_id', '>', '0')
                ->orderBy('updated_at', 'desc')
                ->get();
            if ($pastUserSess->isNotEmpty()) {
                foreach ($pastUserSess as $pastSess) {
                    $this->chkIfCoreIsEditable($pastSess->sess_core_id);
                }
            }
        }
        if ($this->coreIDoverride > 0) {
            // should there be more permission checks here?..
            $this->coreID = $this->coreIDoverride;
        //} elseif ($this->coreID <= 0) { $this->newCoreRow($coreTbl);
        }
        if ($this->coreID > 0) {
            if (!$this->sessInfo) {
                $this->createNewSess();
            }
            $this->setSessCore($this->coreID);
            if ((!isset($this->sessInfo->sess_user_id) 
                    || intVal($this->sessInfo->sess_user_id) <= 0)
                && $uID > 0) {
                $this->sessInfo->sess_user_id = $uID;
                $msg = 'Assigning Sess#' . $this->sessID . ' to U#'
                    . $uID . ' <i>(loadSessInfo)</i>';
                $this->logAdd('session-stuff', $msg);
            }
            $chkNode = false;
            if (isset($this->sessInfo->sess_curr_node)) {
                $chkNode = SLNode::where('node_tree', $this->treeID)
                    ->where('node_id', $this->sessInfo->sess_curr_node)
                    ->first();
            }
            if (!$chkNode) {
                $this->sessInfo->sess_curr_node = 0;
                $nodeSaves = DB::table('sl_node_saves_page')
                    ->join('sl_node', 'sl_node.node_id', 
                        '=', 'sl_node_saves_page.page_save_node')
                    ->where('sl_node_saves_page.page_save_session', $this->coreID)
                    ->where('sl_node.node_tree', $this->treeID)
                    ->select('sl_node_saves_page.*')
                    ->orderBy('updated_at', 'desc')
                    ->get();
                if ($nodeSaves->isNotEmpty()) {
                    foreach ($nodeSaves as $i => $s) {
                        if ($this->sessInfo->sess_curr_node <= 0 
                            && isset($s->page_save_node) 
                            && isset($this->allNodes[$s->page_save_node])) {
                            $this->sessInfo->sess_curr_node = $s->page_save_node;
                        }
                    }
                }
                if ($this->sessInfo->sess_curr_node <= 0 
                    && isset($GLOBALS["SL"]->treeRow->tree_root)) {
                    $this->sessInfo->sess_curr_node = $GLOBALS["SL"]->treeRow->tree_root;
                }
            }
            $this->sessInfo->save();
            session()->put('lastTree', $GLOBALS["SL"]->sessTree);
            session()->put('lastTreeTime', time());
            session()->save();
            $this->chkIfCoreIsEditable();
            $GLOBALS["SL"]->loadSessLoops($this->sessID);
            
            $this->updateCurrNode($this->sessInfo->sess_curr_node);
                        
            // Initialize currNode
            if ($coreTbl != '' && isset($GLOBALS["SL"]->tblAbbr[$coreTbl])) {
                $subFld = $GLOBALS["SL"]->tblAbbr[$coreTbl] . 'submission_progress';
                if (isset($this->sessData->dataSets[$coreTbl])) {
                    $coreRec = $this->sessData->dataSets[$coreTbl][0];
                    if (isset($coreRec->{ $subFld }) && intVal($coreRec->{ $subFld }) > 0) {
                        $this->updateCurrNode($coreRec->{ $subFld });
                    }
                } elseif (isset($this->sessInfo->sess_curr_node) 
                    && intVal($this->sessInfo->sess_curr_node) > 0) {
                    $this->updateCurrNode($this->sessInfo->sess_curr_node);
                } else {
                    $this->updateCurrNode($this->rootID);
                }
            }
        } // end $this->coreID > 0
        return true;
    }
    
    protected function chkUserTreeSess($coreTbl, $cid)
    {
        $this->chkTreeSess($this->treeID);
        if ($this->sessID > 0) {
            return true;
        }
        //$recentSessTime = mktime(date('H')-2, date('i'), date('s'), date('m'), date('d'), date('Y'));
        $this->sessInfo = null;
        if ($cid > 0) {
            $this->sessInfo = SLSess::where('sess_user_id', $this->v["uID"])
                ->where('sess_tree', $GLOBALS["SL"]->sessTree) //$this->treeID)
                ->where('sess_core_id', '>', 0)
                ->where('sess_is_active', 1)
                //->where('updated_at', '>', date('Y-m-d H:i:s', $recentSessTime))
                ->orderBy('updated_at', 'desc')
                ->first();
        } else {
            $this->sessInfo = SLSess::where('sess_user_id', $this->v["uID"])
                ->where('sess_tree', $GLOBALS["SL"]->sessTree) //$this->treeID)
                ->where('sess_core_id', '=', $cid)
                ->where('sess_is_active', 1)
                ->orderBy('updated_at', 'desc')
                ->first();
        }
        if ($this->sessInfo && isset($this->sessInfo->sess_id)) {
            if ($this->isAdminUser() && $cid > 0 && $cid != $this->sessInfo->sess_core_id) {
                $this->sessInfo = new SLSess;
                $this->sessInfo->sess_user_id   = $this->v["uID"];
                $this->sessInfo->sess_tree      = $GLOBALS["SL"]->sessTree;
                $this->sessInfo->sess_core_id   = $this->coreID = $cid;
                $this->sessInfo->sess_is_active = 1;
                $this->sessInfo->save();
                $this->sessID = $this->sessInfo->sess_id;
            } elseif ($this->isAdminUser() 
                || $this->recordIsEditable($coreTbl, $this->sessInfo->sess_core_id)) {
                $this->sessID = $this->sessInfo->sess_id;
                $this->coreID = $this->sessInfo->sess_core_id;
            } else {
                $this->sessInfo = [];
            }
        }
        return true;
    }
    
    protected function chkTreeSess($treeID = -3)
    {
        if (session()->has('sessID' . $treeID)) {
            $this->sessID = intVal(session()->get('sessID' . $treeID));
        }
        if (session()->has('coreID' . $treeID)) {
            $this->coreID = intVal(session()->get('coreID' . $treeID));
        }
        if ($this->sessID > 0) {
            $this->sessInfo = SLSess::where('sess_id', $this->sessID)
                ->where('sess_is_active', 1)
                ->first();
            if ($this->sessInfo 
                && isset($this->sessInfo->sess_user_id) 
                && intVal($this->sessInfo->sess_user_id) > 0
                && isset($this->v)
                && isset($this->v["uID"])
                && intVal($this->v["uID"]) > 0
                && intVal($this->sessInfo->sess_user_id) != intVal($this->v["uID"])) {
                // Reject User Mismatch
                $this->sessInfo = null;
                $this->sessID = $this->coreID = 0;
            }
        }
        return $this->coreID;
    }
    
    protected function setCoreRecUser($coreID = -3, $coreRec = NULL)
    {
        if ($coreID <= 0) {
            $coreID = $this->coreID;
        }
        if ($coreRec 
            && isset($this->v["uID"]) 
            && $this->v["uID"] > 0 
            && trim($GLOBALS["SL"]->coreTblUserFld) != '') {
            $msg = 'Assigning ' . $GLOBALS["SL"]->coreTbl . '#' . $coreID 
                . ' from U#' . $coreRec->{ $GLOBALS["SL"]->coreTblUserFld }
                . ' to U#' . $this->v["uID"] . ' <i>(setCoreRecUser)</i>';
            $this->logAdd('session-stuff', $msg);
            $coreRec->{ $GLOBALS["SL"]->coreTblUserFld } = $this->v["uID"];
            $coreRec->save();
        }
        return $coreRec;
    }
    
    public function chkCoreRecEmpty($coreID = -3, $coreRec = NULL)
    {
        return false;
    }
    
    // Check that core record is actually in-progress (editable)
    protected function chkIfCoreIsEditable($coreID = -3, $coreRec = [])
    {
        if ($coreID <= 0) {
            $coreID = $this->coreID;
        }
        if ($coreID > 0) {
            if (!$this->isAdminUser() 
                && $GLOBALS["SL"]->treeRow->tree_opts%11 == 0 // Tree allows record edits
                && !$this->recordIsEditable($GLOBALS["SL"]->coreTbl, $coreID, $coreRec)) {
                session()->forget('sessID' . $this->treeID);
                session()->forget('coreID' . $this->treeID);
                if ($this->treeID != $GLOBALS["SL"]->sessTree) {
                    session()->forget('sessID' . $GLOBALS["SL"]->sessTree);
                    session()->forget('coreID' . $GLOBALS["SL"]->sessTree);
                }
                session()->save();
                if ($this->sessInfo && isset($this->sessInfo->sess_core_id)) {
                    $this->sessInfo->update([ 'sess_is_active' => 0 ]);
                }
                $this->coreID = -3;
            } else {
                $this->coreID = $coreID;
            }
        }
        return true;
    }
    
    protected function recordIsEditable($coreTbl, $coreID, $coreRec = NULL)
    {
        return ($this->isAdminUser() || $this->recordIsIncomplete($coreTbl, $coreID, $coreRec));
    }
    
    protected function recordIsIncomplete($coreTbl, $coreID, $coreRec = NULL)
    {
        return true;
    }
    
    public function isPublished($coreTbl, $coreID, $coreRec = NULL)
    {
        return !$this->recordIsIncomplete($coreTbl, $coreID, $coreRec);
    }
    
    public function isPublishedPublic($coreTbl, $coreID, $coreRec = NULL)
    {
        return $this->isPublished($coreTbl, 
            $GLOBALS["SL"]->swapIfPublicID($coreID), $coreRec);
    }
    
    public function newCoreRow($coreTbl = '')
    {
        $coreTbl = ((trim($coreTbl) != '') ? $coreTbl : $GLOBALS["SL"]->coreTbl);
        $modelPath = $GLOBALS["SL"]->modelPath($coreTbl);
        if (trim($coreTbl) != '' && trim($modelPath) != '') {
            eval("\$recObj = new " . $modelPath . ";");
            if ($GLOBALS["SL"]->treeRow->tree_type == 'Survey') {
                $coreAbbr = $GLOBALS["SL"]->coreTblAbbr();
                $recObj->{ $coreAbbr . 'user_id' }   = ((isset($this->v["uID"])) 
                    ? $this->v["uID"] : 0);
                $recObj->{ $coreAbbr . 'ip_addy' }   = $GLOBALS["SL"]->hashIP();
                $recObj->{ $coreAbbr . 'is_mobile' } = $GLOBALS["SL"]->isMobile();
                $uniqueFld = $coreAbbr . 'unique_str';
                $recObj->{ $uniqueFld } = $this->getRandStr($coreTbl, $uniqueFld, 20);
            }
            $recObj->save();
            $this->setSessCore($recObj->getKey());
            $this->sessInfo->sess_curr_node = $this->rootID;
            $this->sessInfo->save();
            $msg = 'New Record ' . $coreTbl . '#' . $this->coreID 
                . ', Sess#' . $this->sessID . ' <i>(newCoreRow)</i>';
            $this->logAdd('session-stuff', $msg);
            $this->setCoreRecUser($this->coreID, $recObj);
            $this->sessData->loadCore($coreTbl, $this->coreID);
        }
        return $this->coreID;
    }
    
    protected function createNewSess($cid = -3)
    {
        $this->sessInfo = $GLOBALS["SL"]->createNewSess($this->treeID);
        $this->setSessCore($cid);
        $msg = 'New Session ' . $GLOBALS["SL"]->coreTbl . '#' . $this->coreID 
            . ', Sess#' . $this->sessID . ' <i>(createNewSess)</i>';
        $this->logAdd('session-stuff', $msg);
        return $this->sessID;
    }
    
    protected function setSessCore($coreID)
    {
        if ($coreID > 0) {
            $this->coreID = $this->sessInfo->sess_core_id = $coreID;
            $this->sessInfo->sess_tree = $this->treeID;
            $this->sessInfo->save();
            $this->sessID = $this->sessInfo->sess_id;
            if (session()->has('sessID' . $GLOBALS["SL"]->sessTree)) {
                session()->forget('sessID' . $GLOBALS["SL"]->sessTree);
            }
            if (session()->has('coreID' . $GLOBALS["SL"]->sessTree)) {
                session()->forget('coreID' . $GLOBALS["SL"]->sessTree);
            }
            session()->save();
            session([ 'sessID' . $GLOBALS["SL"]->sessTree => $this->sessID ]);
            session([ 'coreID' . $GLOBALS["SL"]->sessTree => $this->coreID ]);
            session()->save();
        }
        return true;
    }
    
    public function restartTreeSess($treeID)
    {
        $sessKey = 'sessID' . $treeID;
        if (session()->has($sessKey)) {
            SLSess::where('sess_id', session()->get($sessKey))
                ->where('sess_tree', $treeID)
                ->update([ 'sess_is_active' => 0 ]);
            session()->forget($sessKey);
            session()->forget('coreID' . $treeID);
            session()->save();
        }
        return true;
    }
    
    public function restartSess(Request $request)
    {
        $trees = SLTree::get();
        if ($trees->isNotEmpty()) {
            foreach ($trees as $tree) {
                $this->restartTreeSess($tree->tree_id);
            }
        }
        session()->forget('sessIDPage');
        session()->forget('coreIDPage');
        session()->flush();
        session()->save();
        $request->session()->flush();
        $request->session()->save();
        $loc = '/';
        if ($request->has('redir') && trim($request->get('redir')) != '') {
            $loc = trim($request->get('redir'));
        }
        if ($request->has('then') && trim($request->get('then')) != '') {
            $loc = trim($request->get('then'));
        }
        return '<center><h2 style="margin-top: 60px;">...Restarting Site Session...</h2>'
            . '<div style="display: none;"><iframe src="/logout"></iframe></div></center>'
            . '<script type="text/javascript"> setTimeout("window.location=\'' 
            . $loc . '\'", 1000); </script>';
        //return $this->redir('/logout', true);
    }
    
    public function chkSess($cid, $active = true)
    {
        if ($this->v["uID"] <= 0) {
            return false;
        }
        return SLSess::where('sess_core_id', $cid)
            ->where('sess_tree', $this->treeID)
            ->where('sess_user_id', $this->v["uID"])
            ->where('sess_is_active', (($active) ? 1 : 0))
            ->orderBy('updated_at', 'desc')
            ->first();
    }
    
    public function switchSess(Request $request, $cid)
    {
        $this->survLoopInit($request);
        if (!$cid || intVal($cid) <= 0) {
            return $this->redir('/my-profile');
        }
        $ownerUser = -3;
        eval("\$chkRec = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl)
            . "::find(" . intVal($cid) . ");"); 
        // session()->get('coreID' . $GLOBALS["SL"]->sessTree)
        if (!$chkRec) {
            return $this->redir('/my-profile');
        }
        if (isset($chkRec->{ $GLOBALS["SL"]->coreTblUserFld })) {
            $ownerUser = intVal($chkRec->{ $GLOBALS["SL"]->coreTblUserFld });
            if ($ownerUser != $this->v["uID"] && !$this->isAdminUser()) {
                return $this->redir('/my-profile');
            }
        } elseif (!$this->isAdminUser()) {
            return $this->redir('/my-profile');
        }
        $session = $this->chkSess($cid);
        if ($session && isset($session->sess_id)) {
            $this->sessInfo = $session;
            $this->sessID = $session->sess_id;
            $this->coreID = $cid;
        }
        $msg = 'Switch To Sess #' . $this->sessID . ', ' 
            . $GLOBALS["SL"]->coreTbl . '#' . $this->coreID
            . ' <i>(switchSess)</i>';
        if (!$this->sessInfo || !isset($this->sessInfo->sess_tree)) {
            $this->createNewSess($cid);
            $msg = 'Switch To New Sess #' . $this->sessID . ', ' 
                . $GLOBALS["SL"]->coreTbl . ' #' . $this->coreID 
                . ' <i>(switchSess)</i>';
        }
        $this->logAdd('session-stuff', $msg);
        return $this->finishSwitchSess($request, $cid);
        //return $this->redir('/afterLogin');
    }
    
    protected function chkSessRedir(Request $request, $cid)
    {
        $redir = (($GLOBALS["SL"]->treeIsAdmin) ? '/dashboard' : '') 
            . '/start-' . $cid . '/' . $GLOBALS["SL"]->treeRow->tree_slug;
        if ($GLOBALS["SL"]->REQ->has('redir')) {
            $redir = trim($GLOBALS["SL"]->REQ->redir);
        } elseif ($this->sessInfo->sess_curr_node > 0) {
            $nodeRow = SLNode::find($this->sessInfo->sess_curr_node);
            if ($nodeRow && isset($nodeRow->node_prompt_notes) 
                && trim($nodeRow->node_prompt_notes) != '') {
                $redir = (($GLOBALS["SL"]->treeIsAdmin) ? '/dash' : '')
                    . '/u/' . $GLOBALS['SL']->treeRow->tree_slug 
                    . '/' . $nodeRow->node_prompt_notes;
            }
        }
        return $redir;
    }
    
    protected function finishSwitchSess(Request $request, $cid)
    {
        if ($request->has('fromthe') && $request->get('fromthe') == 'top') {
            $this->sessInfo->sess_curr_node = $GLOBALS["SL"]->treeRow->tree_first_page;
        } elseif ($request->has('fromnode') && intVal($request->get('fromnode')) > 0) {
            $this->sessInfo->sess_curr_node = intVal($request->get('fromnode'));
        } elseif (!isset($this->sessInfo->sess_curr_node) 
            || intVal($this->sessInfo->sess_curr_node) <= 0) {
            $nodeFld = $GLOBALS["SL"]->coreTblAbbr() . 'submission_progress';
            if (isset($chkRec->{ $nodeFld }) && intVal($chkRec->{ $nodeFld }) > 0) {
                $this->sessInfo->sess_curr_node = intVal($chkRec->{ $nodeFld });
            }
        }
        $this->sessInfo->save();
        $this->setSessCore($cid);
        return $this->redir($this->chkSessRedir($request, $cid), true);
    }
    
    public function cpySess(Request $request, $cid)
    {
        $this->survLoopInit($request);
        if (!$cid || intVal($cid) <= 0) {
            return $this->redir('/my-profile');
        }
        $ownerUser = -3;
        eval("\$chkRec = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) 
            . "::find(" . intVal($cid) . ");");
        if (!$chkRec) {
            return $this->redir('/my-profile');
        }
        if (isset($chkRec->{ $GLOBALS["SL"]->coreTblUserFld })) {
            $ownerUser = intVal($chkRec->{ $GLOBALS["SL"]->coreTblUserFld });
            if ($ownerUser != $this->v["uID"] && !$this->isAdminUser()) {
                return $this->redir('/my-profile');
            }
        }
        $this->coreID = $this->deepCopyCoreRec($cid);
        $this->createNewSess($this->coreID);
        $msg = 'Creating Core Copy of #' . $cid . '. New Sess#' . $this->sessID . ', ' 
            . $GLOBALS["SL"]->coreTbl . '#' . $this->coreID . ' <i>(cpySess)</i>';
        $this->logAdd('session-stuff', $msg);
        return $this->finishSwitchSess($request, $this->coreID);
    }
    
    protected function deepCopyCoreRecCustom($cid)
    {
        return -3;
    }

    protected function deepCopyCoreRec($cid)
    {
        $newCID = $this->deepCopyCoreRecCustom($cid);
        if ($newCID <= 0) {
            $this->deepCopyCoreSkips($cid);
            $this->v["sessDataCopy"] = new SurvData;
            $this->v["sessDataCopy"]->loadCore($GLOBALS["SL"]->coreTbl, $cid);
            $this->sessData->loadCore($GLOBALS["SL"]->coreTbl);
            foreach ($this->v["sessDataCopy"]->dataSets as $tbl => $rows) {
                if (sizeof($rows) > 0 && !in_array($tbl, $this->v["sessDataCopySkips"])) {
                    $newID = $this->sessData->getNextRecID($tbl);
                    $abbr = $GLOBALS["SL"]->tblAbbr[$tbl];
                    $this->sessData->dataSets[$tbl] = [];
                    foreach ($rows as $i => $row) {
                        $newID++;
                        $this->sessData->dataSets[$tbl][$i] = $row->replicate();
                        $this->sessData->dataSets[$tbl][$i]->{ $abbr . 'id' } = $newID;
                    }
                }
            }
            if (sizeof($this->v["sessDataCopy"]->kidMap) > 0) {
                foreach ($this->v["sessDataCopy"]->kidMap as $tbl1 => $tbl2s) {
                    if (sizeof($tbl2s) > 0 && !in_array($tbl1, $this->v["sessDataCopySkips"])) {
                        foreach ($tbl2s as $tbl2 => $map) {
                            if (!in_array($tbl2, $this->v["sessDataCopySkips"])) {
                                if (!isset($map["id1"])) {
                                    if (is_array($map) && sizeof($map) > 0 && isset($map[0]["id1"])) {
                                        foreach ($map as $m) {
                                            $this->deepCopyCoreRecUpdate($tbl1, $tbl2, $m);
                                        }
                                    }
                                } else {
                                    $this->deepCopyCoreRecUpdate($tbl1, $tbl2, $map);
                                }
                            }
                        }
                    }
                }
            }
            $this->deepCopySetsClean($cid);
            foreach ($this->sessData->dataSets as $tbl => $rows) {
                if (sizeof($rows) > 0) {
                    foreach ($rows as $i => $row) {
                        $row->save();
                    }
                }
            }
            if (isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl]) 
                && isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0])) {
                $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->update([
                    $GLOBALS["SL"]->coreTblAbbr() . 'unique_str'   => '',
                    $GLOBALS["SL"]->coreTblAbbr() . 'ip_addy'      => '',
                    $GLOBALS["SL"]->coreTblAbbr() . 'tree_version' => '',
                    $GLOBALS["SL"]->coreTblAbbr() . 'unique_str'   => '',
                    $GLOBALS["SL"]->coreTblAbbr() . 'submission_progress' 
                        => $GLOBALS["SL"]->treeRow->tree_root
                ]);
            }
            $this->deepCopyFinalize($cid);
            $newCID = $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->getKey();
        }
        return $newCID;
    }
    
    protected function deepCopyCoreSkips($cid)
    {
        $this->v["sessDataCopySkips"] = [];
        return $this->v["sessDataCopySkips"];
    }
    
    protected function deepCopyCoreRecUpdate($tbl1, $tbl2, $map)
    {
        $tblKey = $GLOBALS["SL"]->getForeignLnkNameFldName($tbl1, $tbl2);
        if (trim($tblKey) != '' && $map["id1"] > 0 && $map["id2"] > 0) {
            $this->sessData->dataSets[$tbl2][$map["ind1"]]->{ $tblKey } 
                = $this->sessData->dataSets[$tbl1][$map["ind2"]]->getKey();
        } else {
            $tblKey = $GLOBALS["SL"]->getForeignLnkNameFldName($tbl2, $tbl1);
            if (trim($tblKey) != '' && $map["id1"] > 0 && $map["id2"] > 0) {
                $this->sessData->dataSets[$tbl2][$map["ind2"]]->{ $tblKey } 
                    = $this->sessData->dataSets[$tbl1][$map["ind1"]]->getKey();
            }
        }
        return true;
    }
    
    protected function deepCopySetsClean($cid)
    {
        return true;
    }
    
    protected function deepCopyFinalize($cid)
    {
        return true;
    }
    
    public function multiRecordCheck($oneToo = false)
    {
        $this->v["multipleRecords"] = '';
        if (trim($GLOBALS["SL"]->coreTbl) != '') {
            $coreID = $this->findUserCoreID();
            if ($coreID <= 0 
                || !$this->coreIncompletes 
                || sizeof($this->coreIncompletes) == 0
                || (sizeof($this->coreIncompletes) == 1 && !$oneToo)) {
                return '';
            }
            foreach ($this->coreIncompletes as $i => $coreRow) {
                $this->v["multipleRecords"] .= $this->multiRecordCheckRow($i, $coreRow);
            }
            if (trim($this->v["multipleRecords"]) != '') {
                $cnt = sizeof($this->coreIncompletes);
                $this->v["multipleRecords"] = $this->multiRecordCheckIntro($cnt)
                    . $this->v["multipleRecords"];
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
        return '<h3 class="slBlueDark">' . $this->v["user"]->name . ', You Have ' 
            . (($cnt == 1) ? 'An Unfinished Session' : 'Unfinished Sessions')
            . '</h3>';
    }
    
    public function multiRecordCheckRow($i, $coreRecord)
    {
        if ($this->recordIsEditable(
            $GLOBALS["SL"]->coreTbl, 
            $coreRecord[1]->getKey(), 
            $coreRecord[1])) {
            return view(
                'vendor.survloop.forms.unfinished-record-row', 
                [
                    "tree"    => $this->treeID,
                    "cID"     => $coreRecord[1]->getKey(),
                    "title"   => $this->multiRecordCheckRowTitle($coreRecord), 
                    "desc"    => $this->multiRecordCheckRowSummary($coreRecord),
                    "warning" => $this->multiRecordCheckDelWarn()
                ]
            )->render();
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
        $msg = 'Deactivate Sess#' . $this->sessID . ', Last Node#' 
            . $this->sessInfo->sess_curr_node . ' <i>(deactivateSess)</i>';
        $this->logAdd('session-stuff', $msg);
        if ($this->sessInfo->sess_tree == $treeID) {
            $this->sessInfo->sess_curr_node = -86; // all outta this
            $this->sessInfo->save();
        }
        if ($this->v["uID"] > 0) {
            SLSess::where('sess_user_id', $this->v["uID"])
                ->where('sess_tree', $treeID) // ->where('sess_core_id', $coreID)
                ->update([
                    'sess_curr_node' => -86,
                    'sess_is_active' => 0
                ]);
        }
        session()->forget('sessID' . $treeID);
        session()->forget('coreID' . $treeID);
        session()->save();
        return true;
    }
    
    public function delSess(Request $request, $coreID)
    {
        $this->survLoopInit($request);
        if ($this->isCoreOwner($coreID) || $this->isAdminUser()) {
            if ($coreID != $this->coreID) {
                $this->sessData->loadCore($GLOBALS["SL"]->coreTbl, $coreID);
            }
            $this->sessData->deleteEntireCore();
            if ($coreID != $this->coreID) {
                $this->sessData->loadCore($GLOBALS["SL"]->coreTbl, $this->coreID);
            }
            $core = 'coreID' . $GLOBALS["SL"]->sessTree;
            $sess = false;
            if ($this->v["uID"] > 0) {
                $sess = SLSess::where('sess_user_id', $this->v["uID"])
                    ->where('sess_tree', $this->treeID)
                    ->where('sess_core_id', $coreID)
                    ->where('sess_is_active', 1)
                    ->first();
            } elseif (session()->has($core) && $coreID == session()->get($core)) {
                $sess = SLSess::find($coreID);
            }
            $msg = 'Deleting Sess#' 
                . (($sess && isset($sess->sess_id)) ? $sess->sess_id : 0) 
                . ' to U#' . $this->v["uID"] . ' <i>(delSess)</i>';
            $this->logAdd('session-stuff', $msg);
            if ($sess && isset($sess->sess_id)) {
                //SLSessLoops::where('SessLoopSessID', $sess->sess_id)
                //    ->delete();
                SLSess::find($sess->sess_id)
                    ->update([ 'sess_is_active' => 0 ]);
            }
            session()->forget('sessID' . $GLOBALS["SL"]->sessTree);
            session()->forget('coreID' . $GLOBALS["SL"]->sessTree);
            session()->put('sessMsg', $this->delSessMsg($coreID));
            session()->save();
            $newCoreID = $this->findUserCoreID();
            if ($newCoreID > 0 
                && $this->coreIncompletes 
                && sizeof($this->coreIncompletes) == 1) {
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
    
    public function isCoreOwner($coreID = -3)
    {
        if ($coreID <= 0) {
            $coreID = $this->coreID;
        }
        $core = 'coreID' . $GLOBALS["SL"]->sessTree;
        $sess = 'sessID' . $GLOBALS["SL"]->sessTree;
        if (!isset($this->v["uID"]) || $this->v["uID"] <= 0) {
            if (session()->has($core)
                && $coreID == intVal(session()->get($core))
                && session()->has($sess)
                && intVal(session()->get($sess)) > 0) {
                $trees = [ $this->treeID, $GLOBALS["SL"]->sessTree ];
                $chk = SLSess::where('sess_id', intVal(session()->get($sess)))
                    ->whereIn('sess_tree', $trees)
                    ->where('sess_core_id', $coreID)
                    ->where('sess_is_active', 1)
                    ->get();
                if ($chk->isNotEmpty()) {
                    return true;
                }
            }
            return false;
        }
        // else user is already logged in
        if (trim($GLOBALS["SL"]->coreTblUserFld) != '') {
            $eval = "\$chk = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) 
                . "::where('" . $GLOBALS["SL"]->coreTblIdFld() . "', " . intVal($coreID) . ")"
                . "->where('" . $GLOBALS["SL"]->coreTblUserFld . "', " . $this->v["uID"] . ")"
                . "->first();";
            eval($eval);
            if ($chk) {
                return true;
            }
        }
        if (!$this->isAdminUser()) {
            $chk = SLSess::where('sess_tree', $this->treeID)
                ->where('sess_user_id', $this->v["uID"])
                ->where('sess_core_id', $coreID)
                ->where('sess_is_active', 1)
                ->get();
            if ($chk->isNotEmpty()) {
                return true;
            }
        }
        return $this->isCoreOwnerAlt($coreID);
    }
    
    protected function isCoreOwnerPublic($coreID = -3)
    {
        return $this->isCoreOwner($GLOBALS["SL"]->swapIfPublicID($coreID));
    }
    
    protected function isCoreOwnerAlt($coreID = -3)
    {
        return false;
    }
    
    public function getCurrPubID()
    {
        $tbl = $GLOBALS["SL"]->coreTbl;
        if (trim($tbl) != '' && isset($GLOBALS["SL"]->tblAbbr[$tbl])) {
            $pubFld = $GLOBALS["SL"]->tblAbbr[$tbl] . 'public_id';
            if ($GLOBALS["SL"]->tblHasPublicID() 
                && isset($this->sessData->dataSets[$tbl])
                && isset($this->sessData->dataSets[$tbl][0]->{ $pubFld })) {
                return intVal($this->sessData->dataSets[$tbl][0]->{ $pubFld });
            }
        }
        return $this->coreID;
    }
    
    protected function checkPageViewPerms()
    {
        $initPageView = $GLOBALS["SL"]->pageView;
        if ($GLOBALS["SL"]->dataPerms == '') {
            $GLOBALS["SL"]->dataPerms = 'public';
        }
        if (isset($this->v["uID"]) && $this->v["uID"] > 0 && isset($this->v["user"])) {
            if (in_array($GLOBALS["SL"]->pageView, ['', 'default'])) {
                $GLOBALS["SL"]->pageView = 'public';
                if ($this->v["user"]->hasRole('administrator|databaser|staff|partner')) {
                    $GLOBALS["SL"]->pageView = 'full';
                }
            } elseif (!$this->isCoreOwner()) {
                if (in_array($GLOBALS["SL"]->pageView, ['full', 'full-pdf', 'full-xml'])) {
                    if (!$this->v["user"]
                        ->hasRole('administrator|databaser|staff|partner')) {
                        switch ($GLOBALS["SL"]->pageView) {
                            case 'full-pdf':
                                $GLOBALS["SL"]->pageView = 'pdf';
                                break;
                            case 'full-xml':
                                $GLOBALS["SL"]->pageView = 'xml';
                                break;
                            default:
                                $GLOBALS["SL"]->pageView = 'public';
                                break;
                        }
                    }
                }
            }
            if ($this->v["user"]->hasRole('databaser')) {
                $GLOBALS["SL"]->dataPerms = 'internal';
            } elseif ($this->v["user"]->hasRole('administrator|staff') || $this->isCoreOwner()) {
                $GLOBALS["SL"]->dataPerms = 'sensitive';
            }
        } else {
            if (in_array($GLOBALS["SL"]->pageView, ['', 'default', 'full'])) {
                $GLOBALS["SL"]->pageView = 'public';
            } elseif ($GLOBALS["SL"]->pageView == 'full-pdf') {
                $GLOBALS["SL"]->pageView = 'pdf';
            } elseif ($GLOBALS["SL"]->pageView == 'full-xml') {
                $GLOBALS["SL"]->pageView = 'xml';
            }
        }
        $this->chkPageToken();
        $this->tweakPageViewPerms();
        if ($initPageView != $GLOBALS["SL"]->pageView) {
            //$this->redir('/' . $GLOBALS["SL"]->treeRow->tree_slug 
            //    . '/read-' . $this->corePublicID . '/' . $GLOBALS["SL"]->pageView);
        }
        return true;
    }
    
    /**
     * Override the default data permissions for this page load.
     *
     * @return boolean
     */
    protected function tweakPageViewPerms()
    {
        return true;
    }
    
    protected function isAdminUser()
    {
        if (!isset($this->v["user"]) || $this->v["uID"] <= 0) {
            return false;
        }
        return $this->v["user"]->hasRole('administrator|databaser|staff');
    }
    
    protected function isPublic()
    {
        return false;
    }
    
    protected function chkPageToken()
    {
        if (strlen($GLOBALS["SL"]->pageView) > 6 
            && substr($GLOBALS["SL"]->pageView, 0, 6) == 'token-') {
            $this->v["tokenIn"] = substr($GLOBALS["SL"]->pageView, 6);
            $GLOBALS["SL"]->pageView = '';
        }
        if (!isset($this->v["mfaMsg"])) {
            $this->v["mfaMsg"] = '';
        }
        if (!isset($this->v["pageToken"])) {
            $this->v["pageToken"] = [];
        }
        if (isset($this->v["tokenIn"]) && $this->v["tokenIn"] != '') {
            $this->v["mfaMsg"] = $this->processTokenAccess();
        }
        return (isset($this->v["pageToken"]) && sizeof($this->v["pageToken"]) > 0);
    }
    
    public function pageLoadHasToken()
    {
        return ( (isset($GLOBALS["SL"]->pageView) && trim($GLOBALS["SL"]->pageView) == 'token')
            || (isset($this->v["mfaMsg"]) && trim($this->v["mfaMsg"]) != '')
            || (isset($this->v["tokenIn"]) && $this->v["tokenIn"] != '')
            || (isset($this->v["pageToken"]) && sizeof($this->v["pageToken"]) > 0) );
    }
    
    protected function processTokenAccess($showLabel = 'Enter Key Code:')
    {
        if (!isset($this->v["tokenIn"]) || $this->v["tokenIn"] == '') {
            return '';
        }
        $ret = '';
        $chk = SLTokens::where('tok_type', 'Sensitive')
            ->where('tok_core_id', $this->coreID)
            ->where('tok_tok_token', $this->v["tokenIn"])
            ->orderBy('updated_at', 'desc')
            ->first();
        if ($chk && isset($chk->tok_user_id) && intVal($chk->tok_user_id) > 0) {
            $this->v["tokenUser"] = User::find($chk->tok_user_id);
            if ($this->v["tokenUser"] && isset($this->v["tokenUser"]->id)) {
                $mfaTools = true;
                $resultMsg = '';
                if ($GLOBALS["SL"]->REQ->has('sub') 
                    && $GLOBALS["SL"]->REQ->has('t2') 
                    && trim($GLOBALS["SL"]->REQ->get('t2')) != '') {
                    $time = mktime(date("H"), date("i"), date("s"), date("m"), date("d")-7, date("Y"));
                    $chk = SLTokens::where('tok_type', 'MFA')
                        ->where('tok_core_id', $this->coreID)
                        ->where('tok_tok_token', $GLOBALS["SL"]->REQ->get('t2'))
                        ->where('updated_at', '>', date("Y-m-d H:i:s", $time))
                        ->orderBy('updated_at', 'desc')
                        ->first();
                    if ($chk) {
                        Auth::login($this->v["tokenUser"]);
                        $successMsg = '<div class="alert alert-success alert-dismissible" role="alert">'
                            . '<i class="fa-li fa fa-spinner fa-spin"></i> <strong>Access Granted!</strong> '
                            . '<span class="mL10">Reloading the page...</span>'
                            . '<button type="button" class="close" data-dismiss="alert">×</button></div>';
                        session()->put('sessMsg', $successMsg);
                        session()->save();
                        $resultMsg .= $this->processTokenAccessRedirExtra() 
                            . '<script type="text/javascript"> setTimeout("window.location=\'/' 
                            . $GLOBALS["SL"]->treeRow->tree_slug . '/read-' 
                            . $this->corePublicID . '/full\'", 300); </script>';
                    } else {
                        $resultMsg .= '<div id="keySry" class="alert alert-danger mT10 mB10" '
                            . 'role="alert"><strong>Whoops!</strong> The Key Code you '
                            . 'entered didn\'t match our records or it expired. To view '
                            . 'the full details using your authorized email address, please '
                            . '<a href="?resend=access">request a new key code</a>.</div>';
                    }
                } else {
                    if ($GLOBALS["SL"]->REQ->has('resend') 
                        && trim($GLOBALS["SL"]->REQ->get('resend')) == 'access') {
                        $this->processTokenAccessEmail();
                    }
                }
                if ($mfaTools) {
                    $ret .= $this->getMfaForm($showLabel) . $resultMsg;
                } else {
                    $ret .= $resultMsg;
                }
            }
        }
        return $ret;
    }
    
    protected function getMfaForm(
        $showLabel = 'Enter Key Code:', 
        $btnText   = 'Access Full Details', 
        $btnSz     = '-lg')
    {
        return view(
            'vendor.survloop.elements.inc-sensitive-access-mfa-form', 
            [
                "cID"       => $this->coreID,
                "showLabel" => $showLabel,
                "btnText"   => $btnText,
                "btnSz"     => $btnSz, 
                "user"      => ((isset($this->v["tokenUser"])) ? $this->v["tokenUser"] : null)
            ]
        )->render();
    }
    
    protected function processTokenAccessRedirExtra()
    {
        return '';
    }
    
    protected function processTokenAccessEmail()
    {
        return '<i>Emailing here needs to be setup.</i>';
    }
    
    protected function errorDeniedFullPdf()
    {
        return '<br /><br /><center><h3>You must <a href="/login">login</a> '
            . 'to access the complete details.<br /><br />'
            . 'The public version can be found here:<br /><a href="/' 
            . $GLOBALS["SL"]->treeRow->tree_slug . '/read-' . $this->corePublicID
            . '">' . $GLOBALS["SL"]->sysOpts["app-url"] . '/complaint/read-' 
            . $this->corePublicID . '</a></h3></center>';
    }
    
}