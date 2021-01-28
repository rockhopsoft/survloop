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
use RockHopSoft\Survloop\Controllers\Admin\AdminController;

class SystemUpdate extends AdminController
{
    
    public function index(Request $request)
    {
        $GLOBALS["slRunUpdates"] = true;
        $this->admControlInit($request, '/dashboard/systems-update');
        $this->loadCustLoop($request);
        $this->custReport->loadSysUpdates();
        $this->sysUpdates();
        if ($request->has('sub') && intVal($request->get('sub')) == 1) {
            $this->custReport->v["msgs"] = '<h3>System Updated!</h3>'
                . $this->sysUpdates(true);
            $this->custReport->v["pastUpDef"]->def_description = '';
            foreach ($this->custReport->v["updateList"] as $i => $u) {
                $this->custReport->v["pastUpDef"]->def_description 
                    .= (($i > 0) ? ';;' : '') . $u[0];
            }
            $this->custReport->v["pastUpDef"]->save();
            $this->custReport->loadSysUpdates();
            $this->sysUpdates();
        }
        $this->custReport->v["needUpdate"] = false;
        foreach ($this->custReport->v["updateList"] as $i => $u) {
            if (!$u[1]) {
                $this->custReport->v["needUpdate"] = true;
            }
        }
        if (isset($this->custReport->v["msgs"])) {
            $this->v["msgs"] = $this->custReport->v["msgs"];
        }
        $this->v["needUpdate"] = $this->custReport->v["needUpdate"];
        $this->v["updateList"] = $this->custReport->v["updateList"];
        return view('vendor.survloop.admin.systems-update', $this->v);
    }
    
    protected function sysUpdates($apply = false)
    {
        $msgs = '';
        $this->custReport->v["updateList"] = [];
        
        /* // Template for adding more updates (for now)...
        $updateID = [ '20??-0?-0?', 'Short description' ];
        if (!$this->addSysUpdate($updateID) && $apply) {
            $msgs .= '<b>' . $updateID[0] . ':</b> ' . $updateID[1] . '<br />';
            
            /////// Main update algorithm here ///////
            
        } // end update '2018-02-08'
        */
        
        $updateID = [ '2018-03-27', 'Tree Type primary public is now just Survey' ];
        if (!$this->custReport->addSysUpdate($updateID) && $apply) {
            $msgs .= '<b>' . $updateID[0] . ':</b> ' . $updateID[1] . '<br />';
            SLTree::where('tree_type', 'Primary Public')->update([ 'tree_type' => 'Survey' ]);
            SLTree::where('tree_type', 'Primary Public XML')->update([ 'tree_type' => 'Survey XML' ]);
        } // end update '2018-03-27'
        
        $msgs .= $this->custReport->sysUpdatesCust($apply);
        return $msgs;
    }
    
}

