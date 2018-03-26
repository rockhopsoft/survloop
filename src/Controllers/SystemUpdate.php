<?php
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;

use App\Models\SLDefinitions;
use App\Models\SLNodeResponses;
use App\Models\SLNodeSaves;
use App\Models\SLNodeSavesPage;

class SystemUpdate extends AdminController
{
    
    public function index(Request $request)
    {
        $this->admControlInit($request, '/dashboard/systems-update');
        $this->v["pastUpDef"] = $this->getCoreDef('System Checks', 'system-updates');
        $this->v["pastUpArr"] = $GLOBALS["SL"]->mexplode(';;', $this->v["pastUpDef"]->DefDescription);
        $this->sysUpdates();
        if ($request->has('sub') && intVal($request->get('sub')) == 1) {
            $this->v["msgs"] = '<h3>System Updated!</h3>' . $this->sysUpdates(true);
            $this->v["pastUpDef"]->DefDescription = '';
            foreach ($this->v["updateList"] as $i => $u) {
                $this->v["pastUpDef"]->DefDescription .= (($i > 0) ? ';;' : '') . $u[0];
            }
            $this->v["pastUpDef"]->save();
            $this->sysUpdates();
        }
        $this->v["needUpdate"] = false;
        foreach ($this->v["updateList"] as $i => $u) {
            if (!$u[1]) $this->v["needUpdate"] = true;
        }
        return view('vendor.survloop.admin.systems-update', $this->v);
    }
    
    
    protected function sysUpdates($apply = false)
    {
        $msgs = '';
        $this->v["updateList"] = [];
        
        /* // Template for adding more updates (for now)...
        $updateID = [ '20??-0?-0?', 'Short description' ];
        if (!$this->addSysUpdate($updateID) && $apply) {
            $msgs .= '<b>' . $updateID[0] . ':</b> ' . $updateID[1] . '<br />';
            
            /////// Main update algorithm here ///////
            
        } // end update '2018-02-08'
        */
        
        
        
        
        return $msgs;
    }
    
    protected function addSysUpdate($updateID)
    {
        $done = in_array($updateID[0], $this->v["pastUpArr"]);
        $this->v["updateList"][] = [ $updateID[0], $done, $updateID[1] ];
        return $done;
    }
    
}

