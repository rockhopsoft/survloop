<?php
/**
  * AdminTreeController is the admin class responsible for the tools to edit Survloop's tree designs.
  * (Ideally, this will eventually be replaced by Survloop-generated surveys.)
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.1
  */
namespace RockHopSoft\Survloop\Controllers\Admin;

use DB;
use Illuminate\Http\Request;
use RockHopSoft\Survloop\Controllers\SystemDefinitions;
use RockHopSoft\Survloop\Controllers\Stats\SessAnalysis;
use RockHopSoft\Survloop\Controllers\Admin\AdminController;

class AdminTreeStats extends AdminController
{
    
    public function treeStats(Request $request, $treeID = -3) 
    {
        $this->initLoader();
        $this->loader->syncDataTrees($request, -3, $treeID);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/stats?all=1');
        if (!$this->checkCache()) {
            $this->v["printTree"] = $this->v["treeAdmin"]->adminPrintFullTreeStats($request);
            $this->v["content"] = view(
                'vendor.survloop.admin.tree.treeStats', 
                $this->v
            )->render();
            $this->saveCache();
        }
        return view('vendor.survloop.master', $this->v);
    }

    public function treeSessions(Request $request, $treeID = 1, $refresh = false, $height = 720) 
    {
        $this->initLoader();
        $this->loader->syncDataTrees($request, -3, $treeID);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/sessions');
        if (!$this->checkCache() || $refresh) {
            $this->loadCustLoop($request, $treeID);
            $this->custReport->loadTree($treeID, $request);
            $this->sysDef = new SystemDefinitions;
            $this->v["css"] = $this->sysDef->loadCss();
            
            $this->v["dayold"] = mktime(date("H"), date("i"), date("s"), 
                date("m"), date("d")-3, date("Y"));
            /*
            // clear empties here
            eval("\$chk = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) 
                . "::" . $this->custReport->treeSessionsWhereExtra()
                . "where('updated_at', '<', '" . date("Y-m-d H:i:s", $this->v["dayold"]) 
                . "')->get();");
            if ($chk->isNotEmpty()) {
                foreach ($chk as $row) {
                    if ($this->custReport->chkCoreRecEmpty($row->getKey(), $row)) {
                        $row->delete();
                        //eval("\$del = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) . "::find(" 
                        //    . $row->getKey() . ")->delete();");
                    }
                }
            }
            */
            
            $analyze = new SessAnalysis($treeID);
            $this->v["nodeTots"] = $analyze->loadNodeTots($this->custReport);
            $this->v["nodeSort"] = $analyze->nodeSort;
            $this->v["coreTots"] = [];
            $this->v["allPublicCoreIDs"] = $this->custReport->getAllPublicCoreIDs();
            
            $this->v["last100ids"] = DB::table('sl_node_saves_page')
                ->join('sl_sess', 'sl_node_saves_page.page_save_session', 
                    '=', 'sl_sess.sess_id')
                ->where('sl_sess.sess_tree', '=', $treeID)
                ->where('sl_sess.sess_core_id', '>', 0)
                //->select('sl_node_saves.*', 'sl_sess.SessCoreID')
                ->orderBy('sl_sess.created_at', 'desc')
                ->select('sl_sess.sess_core_id', 'sl_sess.sess_curr_node', 'sl_sess.created_at')
                ->distinct()
                ->get([ 'sl_sess.sess_core_id' ]);

            $this->v["graph1data"] = [];
            $this->v["genTots"] = [
                // incomplete time tot, complete time tot, start date, totals by date
                "date" => [ 0, 0, 0, [] ], 
                "cmpl" => [ 0, 0 ], // incomplete (I), complete (C)
                "mobl" => [ 0, 0, [ 0, 0 ], [ 0, 0 ] ] 
                // desktop (D), mobile (M), [ DI, DC ], [ MI, MC ]
            ];
            $nodeTots = $lines = [];
            if ($this->v["last100ids"]->isNotEmpty()) {
                foreach ($this->v["last100ids"] as $i => $rec) {
                    $coreTots = $analyze->analyzeCoreSessions(
                        $rec->sess_core_id, 
                        $this->v["allPublicCoreIDs"]
                    );
                    if ($coreTots["node"] > 0 
                        && isset($this->v["nodeTots"][$coreTots["node"]])) {
                        $this->v["coreTots"][] = $coreTots;
                        $cmpl = (($coreTots["cmpl"]) ? 1 : 0);
                        $mobl = (($coreTots["mobl"]) ? 1 : 0);
                        $this->v["nodeTots"][$coreTots["node"]]["cmpl"][$cmpl]++;
                        $this->v["genTots"]["cmpl"][$cmpl]++;
                        $this->v["genTots"]["mobl"][$mobl]++;
                        $this->v["genTots"]["mobl"][(2+$mobl)][$cmpl]++;
                        $this->v["genTots"]["date"][2] = $coreTots["date"];
                        $this->v["genTots"]["date"][$cmpl] 
                            = $this->v["genTots"]["date"][$cmpl]+$coreTots["dur"];
                        $date = date("Y-m-d", $coreTots["date"]);
                        if (!isset($this->v["genTots"]["date"][3][$date])) {
                            $this->v["genTots"]["date"][3][$date] = 0;
                        }
                        $this->v["genTots"]["date"][3][$date]++;
                        $min = $coreTots["dur"]/60;
                        if ($min < 70) {
                            $perc = 100;
                            if (!$coreTots["cmpl"]) {
                                $perc = $this->v["nodeTots"][$coreTots["node"]]["perc"];
                            }
                            $this->v["graph1data"][] = [ $perc, $min ];
                        }
                    }
                }
            }
            $title = '<h3 class="mT0 mB10">Duration of Attempt by Percent Completion'
                . '</h3><div class="mTn10 mB10"><i>Based on the final page '
                . 'saved during incomplete submission attempts.</i></div>';
            $this->v["graph1print"] = view(
                'vendor.survloop.reports.graph-scatter', 
                [
                    "currGraphID" => 'treeSessScat',
                    "hgt"         => $height . 'px',
                    "dotColor"    => $this->v["css"]["color-main-on"],
                    "brdColor"    => $this->v["css"]["color-main-grey"],
                    "title"       => $title,
                    "xAxes"       => '% Complete',
                    "yAxes"       => 'Minutes',
                    "data"        => $this->v["graph1data"],
                    "css"         => $this->v["css"]
                ]
            )->render();
            
            $this->v["graph2"] = [
                "dat" => '',
                "lab" => '',
                "bg"  => '',
                "brd" => ''
            ];
            $cnt = 0;
            $currTime = $this->v["genTots"]["date"][2];
            $currDate = date("Y-m-d", $currTime);
            while ($currDate != date("Y-m-d")) {
                $cma = (($cnt > 0) ? ", " : "");
                $this->v["graph2"]["dat"] .= $cma 
                    . ((isset($this->v["genTots"]["date"][3][$currDate])) 
                        ? $this->v["genTots"]["date"][3][$currDate] : 0);
                $this->v["graph2"]["lab"] .= $cma . "\"" . $currDate . "\"";
                $this->v["graph2"]["bg"]  .= $cma . "\"" . $this->v["css"]["color-main-on"]  . "\"";
                $this->v["graph2"]["brd"] .= $cma . "\"" . $this->v["css"]["color-main-grey"] . "\"";
                $cnt++;
                $currTime += (24*60*60);
                $currDate = date("Y-m-d", $currTime);
            }
            $this->v["graph2print"] = view(
                'vendor.survloop.reports.graph-bar', 
                [
                    "currGraphID" => 'treeSessCalen',
                    "hgt"   => '380px',
                    "yAxes" => '# of Submission Attempts (Active Sessions)',
                    "title" => '<h3 class="mT0 mB10">Number of Submission Attempts by Date</h3>',
                    "graph" => $this->v["graph2"],
                    "css"   => $this->v["css"]
                ]
            )->render();
            $this->v["content"] = view(
                'vendor.survloop.admin.tree.tree-sessions-stats', 
                $this->v
            )->render();
            $this->saveCache();
        }
        $this->v["needsCharts"] = true;
        return view('vendor.survloop.master', $this->v);
    }
    
    protected function treeSessGraphDaily(Request $request, $treeID = 1)
    {
        $this->treeSessions($request, $treeID, true);
        return $this->v["graph2print"];
    }
    
    protected function treeSessGraphDurations(Request $request, $treeID = 1)
    {
        $this->treeSessions($request, $treeID, true);
        return $this->v["graph1print"];
    }


}