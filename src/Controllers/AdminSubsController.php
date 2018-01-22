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
    
    
    public function dashboardDefault(Request $request)
    {
        $this->survLoopInit($request, '/dashboard');
        $chk = $this->checkSystemInit();
        if (trim($chk) != '') return $chk;
        return $this->index($request);
    }
    
    public function listSubsAll(Request $request)
    {
        $this->admControlInit($request, '/dashboard/subs/all');
        return $this->printSubsListing($request);
    }
    
    public function listUnpublished(Request $request)
    {
        $this->admControlInit($request, '/dashboard/subs/unpublished');
        return $this->printSubsListing($request);
    }
    
    public function listSubsIncomplete(Request $request)
    {
        $this->admControlInit($request, '/dashboard/subs/incomplete');
        return $this->printSubsListing($request);
    }
    
    protected function printSubsListing(Request $request)
    {
        $this->v["currPage"][1] = 'All Completed Submissions';
        if ($this->v["currPage"][0] == '/dashboard/subs/incomplete') $this->v["currPage"][1] = 'All Incomplete Submissions';
        $this->v["coreAbbr"] = $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl];
        $this->v["subsSort"] = ['created_at', 'desc'];
        $this->v["coreFlds"] = SLFields::select('FldName', 'FldEng', 'FldForeignTable')
            ->where('FldDatabase', $this->dbID)
            ->where('FldTable', $GLOBALS["SL"]->treeRow->TreeCoreTable)
            ->orderBy('FldOrd', 'asc')
            ->orderBy('FldEng', 'asc')
            ->get();
        $xtraWhere = "";
        if ($this->v["currPage"][0] == '/dashboard/subs/incomplete') {
            $xtraWhere = "where('" . $this->v["coreAbbr"] . "SubmissionProgress', '<>', '"
                . $GLOBALS["SL"]->treeRow->TreeLastPage . "')->";
        }
        $this->v["subsList"] = [];
        eval("\$this->v['subsList'] = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl)
            . "::" . $xtraWhere . "orderBy('" . $this->v["subsSort"][0] 
            . "', '" . $this->v["subsSort"][1] . "')->get();");
        return view('vendor.survloop.admin.submissions-list', $this->v);
    }
    
    protected function filterSubsListingOK($row = [])
    {
        return true;
    }
    
    public function printSubView(Request $request, $cid, $viewType = 'view') 
    {
        $this->v["cID"] = $this->coreID = $cid;
        $currPage = '/dashboard/subs/' . $GLOBALS["SL"]->treeID . '/' . $cid 
            . (($viewType == 'view') ? '' : '/'.$viewType);
        $this->admControlInit($request, $currPage);
        $this->CustReport->loadSessionData($GLOBALS["SL"]->coreTbl, $cid);
        if ($request->has('sub')) {
            $this->processAdminReviewTools($request);
            $this->v["admMenu"] = $this->getAdmMenu($this->v["currPage"][0]);
        }
        $this->v["viewType"] = $viewType;
        $this->v["coreRec"] = $this->CustReport->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0];
        $this->v["content"] = $this->printAdminReviewTools() . $this->CustReport->printAdminReport($cid, $viewType);
        return view('vendor.survloop.master', $this->v);
    }
    
    protected function printAdminReviewTools()
    {
        return '';
    }
    
    protected function processAdminReviewTools(Request $request)
    {
        return true;
    }
    
}