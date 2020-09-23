<?php
/**
  * NodeSaveSet is a helper class which preps 
  * sets of node saves for debugging purposes.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.18
  */
namespace RockHopSoft\Survloop\Controllers\Admin;

use App\Models\SLSess;
use App\Models\SLNodeSaves;

class NodeSaveSet
{
    private $treeID   = 1;
    private $coreID   = 0;

    public $mainSaves = null;
    public $userSaves = [];
    public $userIDs   = [];

    public function __construct($coreID = 0, $treeID = 1)
    {
        $this->treeID = $treeID;
        $this->coreID = $coreID;
        $this->loadSaves();
    }

    private function loadSaves()
    {
        if ($this->coreID > 0) {
            $sess = SLSess::where('sess_tree', $this->treeID)
                ->where('sess_core_id', $this->coreID)
                ->get();
            $sessIDs = $GLOBALS["SL"]->resultsToArrIds($sess, 'sess_id');
            if (sizeof($sessIDs) > 0) {
                if (isset($sess[0]->sess_user_id)) {
                    $uID = intVal($sess[0]->sess_user_id);
                    if ($uID > 0 && !in_array($uID, $this->userIDs)) {
                        $this->userIDs[] = $uID;
                    }
                }
                $this->mainSaves = new NodeSaveSetGroup($sessIDs);
                if (sizeof($this->userIDs) > 0) {
                    foreach ($this->userIDs as $uID) {
                        $sess = SLSess::where('sess_tree', $this->treeID)
                            ->where('sess_core_id', 'NOT LIKE', $this->coreID)
                            ->where('sess_user_id', $uID)
                            ->get();
                        $sessIDs = $GLOBALS["SL"]->resultsToArrIds($sess, 'sess_id');
                        $this->userSaves[$uID] = new NodeSaveSetGroup($sessIDs);
                    }
                }
            }
        }
        return true;
    }
}

class NodeSaveSetGroup
{
    public $saves = [];
    public $saveFlds  = [];

    public function __construct($sessIDs = [])
    {
        $this->saves = SLNodeSaves::whereIn('node_save_session', $sessIDs)
            ->orderBy('created_at', 'asc')
            ->orderBy('node_save_tbl_fld', 'asc')
            ->get();
        if ($this->saves->isNotEmpty()) {
            foreach ($this->saves as $save) {
                $fld = trim($save->node_save_tbl_fld);
                if ($fld != 'PAGE SAVE AUTO:') {
                    if (!isset($this->saveFlds[$fld])) {
                        $this->saveFlds[$fld] = [];
                    }
                    $loop = 0;
                    if (isset($save->node_save_loop_item_id)
                        && intVal($save->node_save_loop_item_id) > 0) {
                        $loop = intVal($save->node_save_loop_item_id);
                    }
                    if (!isset($this->saveFlds[$fld][$loop])) {
                        $this->saveFlds[$fld][$loop] = [];
                    }
                    $this->saveFlds[$fld][$loop][] = $save;
                }
            }
        }
        return true;
    }
}
