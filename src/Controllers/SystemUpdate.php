<?php
/**
  * SystemUpdate runs scripts for system updates, 
  * but should be replaced by Laravel's migrations.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Models\SLTree;
use App\Models\SLDefinitions;
use SurvLoop\Controllers\AdminController;

class SystemUpdate extends AdminController
{
    
    public function index(Request $request)
    {
        $GLOBALS["slRunUpdates"] = true;
        $this->admControlInit($request, '/dashboard/systems-update');
        $this->custReport->loadSysUpdates();
        $this->sysUpdates();
        if ($request->has('sub') && intVal($request->get('sub')) == 1) {
            $this->custReport->v["msgs"] = '<h3>System Updated!</h3>' . $this->sysUpdates(true);
            $this->custReport->v["pastUpDef"]->DefDescription = '';
            foreach ($this->custReport->v["updateList"] as $i => $u) {
                $this->custReport->v["pastUpDef"]->DefDescription .= (($i > 0) ? ';;' : '') . $u[0];
            }
            $this->custReport->v["pastUpDef"]->save();
            $this->custReport->loadSysUpdates();
            $this->sysUpdates();
        }
        $this->custReport->v["needUpdate"] = false;
        foreach ($this->custReport->v["updateList"] as $i => $u) {
            if (!$u[1]) $this->custReport->v["needUpdate"] = true;
        }
        if (isset($this->custReport->v["msgs"])) $this->v["msgs"] = $this->custReport->v["msgs"];
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
        
        /*
        $updateID = [ '2018-03-31', 'Table extension field change in the tables table' ];
        if (!$this->custReport->addSysUpdate($updateID) && $apply) {
            $msgs .= '<b>' . $updateID[0] . ':</b> ' . $updateID[1] . '<br />';
            $flds = DB::select(DB::raw("ALTER TABLE `SL_Tables` CHANGE COLUMN `TblActive` `TblExtend` INT(11) NULL"));
            $flds = DB::select(DB::raw("UPDATE `SL_Tables` SET `TblExtend`=0 WHERE 1"));
        } // end update '2018-03-31'
        */
        $updateID = [ '2018-03-27', 'Tree Type primary public is now just Survey' ];
        if (!$this->custReport->addSysUpdate($updateID) && $apply) {
            $msgs .= '<b>' . $updateID[0] . ':</b> ' . $updateID[1] . '<br />';
            SLTree::where('TreeType', 'Primary Public')->update([ 'TreeType' => 'Survey' ]);
            SLTree::where('TreeType', 'Primary Public XML')->update([ 'TreeType' => 'Survey XML' ]);
        } // end update '2018-03-27'
        
        $msgs .= $this->custReport->sysUpdatesCust($apply);
        return $msgs;
    }
    
}

