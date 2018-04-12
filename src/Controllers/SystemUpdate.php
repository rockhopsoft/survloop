<?php
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;

use App\Models\SLTree;
use App\Models\SLDefinitions;

class SystemUpdate extends AdminController
{
    
    public function index(Request $request)
    {
        $GLOBALS["slRunUpdates"] = true;
        $this->admControlInit($request, '/dashboard/systems-update');
        $this->CustReport->loadSysUpdates();
        $this->sysUpdates();
        if ($request->has('sub') && intVal($request->get('sub')) == 1) {
            $this->CustReport->v["msgs"] = '<h3>System Updated!</h3>' . $this->sysUpdates(true);
            $this->CustReport->v["pastUpDef"]->DefDescription = '';
            foreach ($this->CustReport->v["updateList"] as $i => $u) {
                $this->CustReport->v["pastUpDef"]->DefDescription .= (($i > 0) ? ';;' : '') . $u[0];
            }
            $this->CustReport->v["pastUpDef"]->save();
            $this->CustReport->loadSysUpdates();
            $this->sysUpdates();
        }
        $this->CustReport->v["needUpdate"] = false;
        foreach ($this->CustReport->v["updateList"] as $i => $u) {
            if (!$u[1]) $this->CustReport->v["needUpdate"] = true;
        }
        if (isset($this->CustReport->v["msgs"])) $this->v["msgs"] = $this->CustReport->v["msgs"];
        $this->v["needUpdate"] = $this->CustReport->v["needUpdate"];
        $this->v["updateList"] = $this->CustReport->v["updateList"];
        return view('vendor.survloop.admin.systems-update', $this->v);
    }
    
    protected function sysUpdates($apply = false)
    {
        $msgs = '';
        $this->CustReport->v["updateList"] = [];
        
        /* // Template for adding more updates (for now)...
        $updateID = [ '20??-0?-0?', 'Short description' ];
        if (!$this->addSysUpdate($updateID) && $apply) {
            $msgs .= '<b>' . $updateID[0] . ':</b> ' . $updateID[1] . '<br />';
            
            /////// Main update algorithm here ///////
            
        } // end update '2018-02-08'
        */
        
        /*
        $updateID = [ '2018-03-31', 'Table extension field change in the tables table' ];
        if (!$this->CustReport->addSysUpdate($updateID) && $apply) {
            $msgs .= '<b>' . $updateID[0] . ':</b> ' . $updateID[1] . '<br />';
            $flds = DB::select(DB::raw("ALTER TABLE `SL_Tables` CHANGE COLUMN `TblActive` `TblExtend` INT(11) NULL"));
            $flds = DB::select(DB::raw("UPDATE `SL_Tables` SET `TblExtend`=0 WHERE 1"));
        } // end update '2018-03-31'
        */
        $updateID = [ '2018-03-27', 'Tree Type primary public is now just Survey' ];
        if (!$this->CustReport->addSysUpdate($updateID) && $apply) {
            $msgs .= '<b>' . $updateID[0] . ':</b> ' . $updateID[1] . '<br />';
            SLTree::where('TreeType', 'Primary Public')->update([ 'TreeType' => 'Survey' ]);
            SLTree::where('TreeType', 'Primary Public XML')->update([ 'TreeType' => 'Survey XML' ]);
        } // end update '2018-03-27'
        
        $msgs .= $this->CustReport->sysUpdatesCust($apply);
        return $msgs;
    }
    
}

