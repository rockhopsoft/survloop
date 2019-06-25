<?php
/**
  * SessAnalysis runs basic queries to track the activity within a user session.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use DB;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLNodeSaves;
use App\Models\SLNodeSavesPage;
use App\Models\SLSess;
use SurvLoop\Controllers\Tree\TreeNodeSurv;

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
    
    public function loadNodeTots($custReport = null)
    {
        if ($custReport === null) {
            return [];
        }
        $this->nodeTots = $this->nodeSort = $this->coreTots = [];
        $chk = SLNode::where('NodeTree', $this->treeID)
            ->whereIn('NodeType', [ 'Page', 'Loop Root' ])
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $n) {
                $tmp = new TreeNodeSurv($n->NodeID, $n);
                $tmp->fillNodeRow();
                $this->v["nodeTots"][$n->NodeID] = [
                    "cmpl" => [ 0, 0 ],
                    "perc" => intVal($custReport->rawOrderPercent($n->NodeID)),
                    "name" => ((isset($tmp->extraOpts["meta-title"]) && trim($tmp->extraOpts["meta-title"]) != '')
                        ? $tmp->extraOpts["meta-title"] : $n->NodePromptNotes)
                ];
                $this->v["nodeSort"][$this->v["nodeTots"][$n->NodeID]["perc"]] = $n->NodeID;
            }
        }
        ksort($this->v["nodeSort"], 1); // SORT_NUMERIC
        return $this->v["nodeTots"];
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
            "dur" => 0,
            "mobl" => false,
            "cmpl" => false, 
            "log" => []
        ];
        eval("\$coreRec = " . $GLOBALS["SL"]->modelPathTblID($tree->TreeCoreTable) . "::find(" . intVal($coreID) .");");
        if (!$coreRec || !isset($coreRec->updated_at)) {
            return $this->coreTots;
        }
        
        $this->v["dayold"] = mktime(date("H"), date("i"), date("s"), date("m"), date("d")-3, date("Y"));
        $cacheFile = '../storage/app/anlyz/t' . $this->treeID . '/c' . $coreID . '.php';
        if (!file_exists($cacheFile) || strtotime($coreRec->updated_at) > $this->v["dayold"]
            || $GLOBALS["SL"]->REQ->has('refresh')) {
            if (in_array($GLOBALS["SL"]->getTblRecPublicID($coreRec), $allPublicIDs)) {
                $this->coreTots["cmpl"] = true;
            }
            $coreAbbr = $GLOBALS["SL"]->coreTblAbbr();
            if (isset($coreRec->{ $coreAbbr . 'SubmissionProgress' })) {
                $this->coreTots["node"] = $coreRec->{ $coreAbbr . 'SubmissionProgress' };
                $this->coreTots["date"] = strtotime($coreRec->created_at);
                if (isset($coreRec->{ $coreAbbr . 'IsMobile' }) && intVal($coreRec->{ $coreAbbr . 'IsMobile' }) == 1) {
                    $this->coreTots["mobl"] = true;
                }
            }
            $coreLog = '';
            $pages = DB::table('SL_NodeSavesPage')
                ->join('SL_Sess', 'SL_NodeSavesPage.PageSaveSession', '=', 'SL_Sess.SessID')
                ->where('SL_Sess.SessTree', '=', $this->treeID)
                ->where('SL_Sess.SessCoreID', '=', $coreID)
                ->orderBy('SL_NodeSavesPage.created_at', 'asc')
                ->select('SL_NodeSavesPage.PageSaveNode', 'SL_NodeSavesPage.created_at')
                ->distinct()
                ->get([ 'SL_Sess.SessCoreID' ]);
            if ($pages->isNotEmpty()) {
                $lastCreateDate = $durMinus = 0;
                foreach ($pages as $i => $p) {
                    $dur = strtotime($p->created_at)-$this->coreTots["date"];
                    if ($dur >= 0 && isset($this->v["nodeTots"][$p->PageSaveNode])) {
                        $coreLog .= ', [ ' . $dur . ', ' . $p->PageSaveNode . ' ]';
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