<?php
/**
  * SystemUpdate runs scripts for system updates,
  * but should be replaced by Laravel's migrations.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.5
  */
namespace RockHopSoft\Survloop\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Models\SLTree;
use App\Models\SLDefinitions;
use App\Models\SLSess;
use App\Models\SLSessLoops;
use App\Models\SLNodeSaves;
use App\Models\SLNodeSavesPage;
use RockHopSoft\Survloop\Controllers\Admin\AdminController;

class SystemUpdate extends AdminController
{

    public function index(Request $request)
    {
        $GLOBALS["slRunUpdates"] = true;
        $this->admControlInit($request, '/dashboard/systems-clean');
        $this->loadCustLoop($request);
        $step = $this->getCoreDef('System Checks', 'system-clean');
        $this->v["step"] = intVal($step->def_description);
        if ($this->v["step"] < 1) {
            $this->v["step"] = 1;
        }
        $this->v["currStep"] = $this->v["step"];
        if ($request->has('run') && trim($request->get('run')) == 'clean') {
            if ($this->v["step"] < 4) {
                $this->sysClean();
                $this->v["step"]++;
            } else {
                $this->v["step"] = $this->custReport->customSysClean($this->v["step"]);
            }
            $this->updateSysCleanStep();
            echo view('vendor.survloop.admin.systems-clean-ajax', $this->v)->render();
            exit;
        }
        return view('vendor.survloop.admin.systems-clean', $this->v)->render();
    }

    private function updateSysCleanStep()
    {
        SLDefinitions::where('def_database', 1)
            ->where('def_set', '=', 'System Checks')
            ->where('def_subset', '=', 'system-clean')
            ->update([ 'def_description' => $this->v["step"] ]);
    }

    protected function sysClean()
    {
        $chk = SLSess::select('sess_id')
            ->get();
        $sessIDs = $GLOBALS["SL"]->resultsToArrIds($chk, 'sess_id');
        unset($chk);

        if ($this->v["step"] == 1) {

            $chk = SLSessLoops::select('sess_loop_sess_id')
                ->where('sess_loop_sess_id', '>', 0)
                ->limit(10000)
                ->inRandomOrder()
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $s) {
                    if (!in_array($s->sess_loop_sess_id, $sessIDs)) {
                        $s->delete();
                    }
                }
            }

        } elseif ($this->v["step"] == 2) {

            $chk = SLNodeSaves::select('node_save_session')
                ->where('node_save_session', '>', 0)
                ->limit(10000)
                ->inRandomOrder()
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $s) {
                    if (!in_array($s->node_save_session, $sessIDs)) {
                        $s->delete();
                    }
                }
            }

        } elseif ($this->v["step"] == 3) {

            $chk = SLNodeSavesPage::select('page_save_session')
                ->where('page_save_session', '>', 0)
                ->limit(10000)
                ->inRandomOrder()
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $s) {
                    if (!in_array($s->page_save_session, $sessIDs)) {
                        $s->delete();
                    }
                }
            }

        }
        return true;
    }

}

