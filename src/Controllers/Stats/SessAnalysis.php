<?php
/**
  * SessAnalysis runs basic queries to track the activity within a user session.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.25
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

use DB;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLNodeSaves;
use App\Models\SLNodeSavesPage;
use App\Models\SLSess;
use RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv;

class SessAnalysis
{
    private $treeID = 1;
    public $nodeTots = [];
    public $nodeSort = [];
    public $coreTots = [];
    
    public function __construct($treeID = 1)
    {
        $this->treeID = $treeID;
    }
    
    public function loadNodeTots(&$custReport = null)
    {
        if ($custReport === null) {
            return [];
        }
        $this->nodeTots = $this->nodeSort = $this->coreTots = [];
        $chk = SLNode::where('node_tree', $this->treeID)
            ->whereIn('node_type', [ 'Page', 'Loop Root' ])
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $n) {
                $tmp = new TreeNodeSurv($n->node_id, $n);
                $tmp->fillNodeRow();
                $this->nodeTots[$n->node_id] = [
                    "cmpl" => [ 0, 0 ],
                    "perc" => intVal($custReport->rawOrderPercent($n->node_id)),
                    "name" => ((isset($tmp->extraOpts["meta-title"]) 
                        && trim($tmp->extraOpts["meta-title"]) != '')
                        ? $tmp->extraOpts["meta-title"] 
                        : $n->node_prompt_notes)
                ];
                $this->nodeSort[$this->nodeTots[$n->node_id]["perc"]] = $n->node_id;
            }
        }
        ksort($this->nodeSort, 1); // SORT_NUMERIC
        return $this->nodeTots;
    }

    public function analyzeCoreSessions($coreID = -3, $allPublicIDs = [])
    {
        if ($coreID <= 0) {
            $coreID = $this->coreID;
        }
        if (!is_dir('../storage/app/anlyz')) {
            mkdir('../storage/app/anlyz');
        }
        if (!is_dir('../storage/app/anlyz/t' . $this->treeID)) {
            mkdir('../storage/app/anlyz/t' . $this->treeID);
        }
        $tree = SLTree::find($this->treeID);
        $this->coreTots = [
            "core" => $coreID,
            "node" => -3,
            "date" => 0,
            "dur"  => 0,
            "mobl" => false,
            "cmpl" => false, 
            "log"  => []
        ];
        eval("\$coreRec = " . $GLOBALS["SL"]->modelPathTblID($tree->tree_core_table) 
            . "::find(" . intVal($coreID) .");");
        if (!$coreRec || !isset($coreRec->updated_at)) {
            return $this->coreTots;
        }
        
        $this->v["dayold"] = mktime(date("H"), date("i"), date("s"), date("m"), date("d")-3, date("Y"));
        $cacheFile = '../storage/app/anlyz/t' . $this->treeID . '/c' . $coreID . '.php';
        if (!file_exists($cacheFile) 
            || strtotime($coreRec->updated_at) > $this->v["dayold"]
            || $GLOBALS["SL"]->REQ->has('refresh')) {
            if (in_array($GLOBALS["SL"]->getTblRecPublicID($coreRec), $allPublicIDs)) {
                $this->coreTots["cmpl"] = true;
            }
            $coreAbbr = $GLOBALS["SL"]->coreTblAbbr();
            if (isset($coreRec->{ $coreAbbr . 'submission_progress' })) {
                $this->coreTots["node"] = $coreRec->{ $coreAbbr . 'submission_progress' };
                $this->coreTots["date"] = strtotime($coreRec->created_at);
                if (isset($coreRec->{ $coreAbbr . 'is_mobile' }) 
                    && intVal($coreRec->{ $coreAbbr . 'is_mobile' }) == 1) {
                    $this->coreTots["mobl"] = true;
                }
            }
            $coreLog = '';
            $pages = DB::table('sl_node_saves_page')
                ->join('sl_sess', 'sl_node_saves_page.page_save_session', '=', 'sl_sess.sess_id')
                ->where('sl_sess.sess_tree', '=', $this->treeID)
                ->where('sl_sess.sess_core_id', '=', $coreID)
                ->orderBy('sl_node_saves_page.created_at', 'asc')
                ->select('sl_node_saves_page.page_save_node', 'sl_node_saves_page.created_at')
                ->distinct()
                ->get([ 'sl_sess.sess_core_id' ]);
            if ($pages->isNotEmpty()) {
                $lastCreateDate = $durMinus = 0;
                foreach ($pages as $i => $p) {
                    $dur = strtotime($p->created_at)-$this->coreTots["date"];
                    if ($dur >= 0 && isset($this->nodeTots[$p->page_save_node])) {
                        $coreLog .= ', [ ' . $dur . ', ' . $p->page_save_node . ' ]';
                        $this->coreTots["dur"] = $dur;
                        if ($lastCreateDate > 0) {
                            $lastGap = strtotime($p->created_at)-$lastCreateDate;
                            if ($lastGap > 3600) {
                                $durMinus += $lastGap;
                            }
                        } elseif ($dur > 3600) {
                            $durMinus += $dur;
                        }
                        $lastCreateDate = strtotime($p->created_at);
                    }
                }
                $this->coreTots["dur"] = $this->coreTots["dur"]-$durMinus;
                if ($this->coreTots["dur"] < 0) {
                    $this->coreTots["dur"] = 0;
                }
                if (trim($coreLog) != '') {
                    $coreLog = substr($coreLog, 1);
                }
            }
            $cacheCode = '$'.'this->coreTots = [
                "core" => ' . $coreID . ', 
                "node" => ' . $this->coreTots["node"] . ', 
                "date" => ' . ((trim($this->coreTots["date"]) != '') ? $this->coreTots["date"] : 0) . ', 
                "dur"  => ' . ((trim($this->coreTots["dur"]) != '') ? $this->coreTots["dur"] : 0) . ', 
                "mobl" => ' . (($this->coreTots["mobl"]) ? 'true' : 'false') . ', 
                "cmpl" => ' . (($this->coreTots["cmpl"]) ? 'true' : 'false') . ', 
                "log"  => [ ' . $coreLog . ' ]
                ];' . "\n";
            file_put_contents($cacheFile, $cacheCode);
        }
        eval(file_get_contents($cacheFile));
        return $this->coreTots;
    }
    
}