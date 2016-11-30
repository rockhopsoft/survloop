<?php
namespace SurvLoop\Controllers;

use Illuminate\Http\Request;

use SurvLoop\Models\SLDatabases;
use SurvLoop\Models\SLFields;
use SurvLoop\Models\SLTree;

use SurvLoop\Controllers\AdminController;

class AdminSubsController extends AdminController
{
	public $classExtension 		= 'AdminSubsController';
	
	
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
		$this->v["coreAbbr"] = $GLOBALS["DB"]->tblAbbr[$GLOBALS["DB"]->coreTbl];
		$this->v["subsSort"] = ['created_at', 'desc'];
		$this->v["coreFlds"] = SLFields::select('FldName', 'FldEng', 'FldForeignTable')
			->where('FldDatabase', $this->dbID)
			->where('FldTable', $GLOBALS["DB"]->treeRow->TreeCoreTable)
			->orderBy('FldOrd', 'asc')
			->orderBy('FldEng', 'asc')
			->get();
		$xtraWhere = "";
		if ($this->v["currPage"] == '/dashboard/subs/incomplete')
		{
			$xtraWhere = "where('" . $this->v["coreAbbr"] . "SubmissionProgress', '<>', '"
				. $GLOBALS["DB"]->treeRow->TreeLastPage . "')->";
		}
		$this->v["subsList"] = [];
		eval("\$this->v['subsList'] = " . $GLOBALS["DB"]->modelPath($GLOBALS["DB"]->coreTbl)
			. "::" . $xtraWhere . "orderBy('" . $this->v["subsSort"][0] 
			. "', '" . $this->v["subsSort"][1] . "')->get();");
		return view( 'vendor.survloop.admin.submissions-list', $this->v );
	}
	
	
	public function dashboardDefault(Request $request)
	{
		$this->survLoopInit($request, '/dashboard');
		// Check For Basic System Setup First
		$sysChk = SLDatabases::select('DbID')
			->where('DbUser', '>', 0)
			->get();
		if (!$sysChk || sizeof($sysChk) == 0)
		{
			return redirect('/fresh/database');
		}
		$sysChk = SLTree::select('TreeID')
			->where('TreeDatabase', '=', $GLOBALS["DB"]->dbID)
			->get();
		if (!$sysChk || sizeof($sysChk) == 0)
		{
			return redirect('/fresh/user-experience');
		}
		
		return $this->index($request);
	}
	
}

?>