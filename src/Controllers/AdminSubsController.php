<?php
namespace SurvLoop\Controllers;

use Illuminate\Http\Request;

use App\Models\SLDatabases;
use App\Models\SLFields;
use App\Models\SLTree;

use SurvLoop\Controllers\AdminController;

class AdminSubsController extends AdminController
{
    public $classExtension = 'AdminSubsController';
    
    
    public function listSubsAll(Request $request)
    {
        $this->admControlInit($request, '/dashboard/subs/all');
        return $this->printSubsListing($request);
    }
    
    public function listSubsIncomplete(Request $request)
    {
        $this->admControlInit($request, '/dashboard/subs/incomplete');
        return $this->printSubsListing($request);
    }
    
    protected function printSubsListing(Request $request)
    {
        $this->v["coreAbbr"] = $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl];
        $this->v["subsSort"] = ['created_at', 'desc'];
        $this->v["coreFlds"] = SLFields::select('FldName', 'FldEng', 'FldForeignTable')
            ->where('FldDatabase', $this->dbID)
            ->where('FldTable', $GLOBALS["SL"]->treeRow->TreeCoreTable)
            ->orderBy('FldOrd', 'asc')
            ->orderBy('FldEng', 'asc')
            ->get();
        $xtraWhere = "";
        if ($this->v["currPage"] == '/dashboard/subs/incomplete') {
            $xtraWhere = "where('" . $this->v["coreAbbr"] . "SubmissionProgress', '<>', '"
                . $GLOBALS["SL"]->treeRow->TreeLastPage . "')->";
        }
        $this->v["subsList"] = [];
        eval("\$this->v['subsList'] = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl)
            . "::" . $xtraWhere . "orderBy('" . $this->v["subsSort"][0] 
            . "', '" . $this->v["subsSort"][1] . "')->get();");
        return view('vendor.survloop.admin.submissions-list', $this->v);
    }
    
    public function dashboardDefault(Request $request)
    {
        $this->survLoopInit($request, '/dashboard');
        $chk = $this->checkSystemInit();
        if (trim($chk) != '') return $chk;
        return $this->index($request);
    }
    
}
