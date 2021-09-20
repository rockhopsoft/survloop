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
        ini_set('max_execution_time', 180);
        $GLOBALS["slRunUpdates"] = true;
        $this->admControlInit($request, '/dashboard/systems-clean');
        $this->loadCustLoop($request);
        $step = $this->getCoreDef('System Checks', 'system-clean');
        if ($request->has('refresh')) {
            $step->def_description = 1;
            $step->save();
        }
        $this->v["step"] = intVal($step->def_description);
        if ($this->v["step"] < 1) {
            $this->v["step"] = 1;
        }
        $this->v["currStep"] = $this->v["step"];
        if ($request->has('run')
            && trim($request->get('run')) == 'clean') {
            if ($this->v["step"] < 4) {
                $chk = SLSess::select('sess_id')
                    ->get();
                $sessIDs = $GLOBALS["SL"]->resToArrIds($chk, 'sess_id');
                unset($chk);
                if ($this->v["step"] == 1) {
                    $this->sysClean1($sessIDs);
                } elseif ($this->v["step"] == 2) {
                    $this->sysClean2($sessIDs);
                } elseif ($this->v["step"] == 3) {
                    $this->sysClean3($sessIDs);
                }
                $this->v["step"]++;
            } else {
                $this->v["step"]
                    = $this->custReport->customSysClean($this->v["step"]);
            }
            $this->updateSysCleanStep();
            echo view(
                'vendor.survloop.admin.systems-clean-ajax',
                $this->v
            )->render();
            exit;
        }
        return view(
            'vendor.survloop.admin.systems-clean',
            $this->v
        )->render();
    }

    private function updateSysCleanStep()
    {
        SLDefinitions::where('def_database', 1)
            ->where('def_set', '=', 'System Checks')
            ->where('def_subset', '=', 'system-clean')
            ->update([ 'def_description' => $this->v["step"] ]);
    }

    protected function sysClean1($sessIDs)
    {
        $chk = SLSessLoops::whereNotIn('sess_loop_sess_id', $sessIDs)
            ->limit(1000)
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $s) {
                echo 'SLSessLoops ' . $s->getKey() . '<br />';
                $s->delete();
            }
        }
    }

    protected function sysClean2($sessIDs)
    {
        $chk = SLNodeSaves::whereNotIn('node_save_session', $sessIDs)
            ->limit(1000)
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $s) {
                echo 'SLNodeSaves ' . $s->getKey() . '<br />';
                $s->delete();
            }
        }
    }

    protected function sysClean3($sessIDs)
    {
        $chk = SLNodeSavesPage::whereNotIn('page_save_session', $sessIDs)
            ->limit(1000)
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $s) {
                echo 'SLNodeSavesPage ' . $s->getKey() . '<br />';
                $s->delete();
            }
        }
    }

}