<?php
namespace SurvLoop\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use SurvLoop\Models\SLDefinitions;

class SurvLoop extends Controller
{
	
	public $classExtension 	= 'SurvLoop';
	public $custAbbr		= 'SurvLoop';
	public $custLoop		= array();
	public $treeID 			= 1;
	
	protected function loadAbbr()
	{
		$chk = SLDefinitions::select('DefDescription')
			->where('DefDatabase', 1)
			->where('DefSet', 'System Settings')
			->where('DefSubset', 'cust-abbr')
			->first();
		if ($chk && isset($chk->DefDescription))
		{
			$this->custAbbr = trim($chk->DefDescription);
		}
		return true;
	}
	
	protected function loadLoop(Request $request)
	{
		$this->loadAbbr();
		$class = "SurvLoop\\Controllers\\SurvFormTree";
		if ($this->custAbbr != 'SurvLoop')
		{
			$custClass = "app\\Http\\Controllers\\" 
				. $this->custAbbr . "\\" . $this->custAbbr . "";
			if (file_exists('../' . $custClass)) $class = ucfirst($custClass);
		}
		//echo '<br /><br /><br /><br />' . $class . '<br />'; exit;
		eval("\$this->custLoop = new " . $class . "(\$request);");
		return true;
	}
	
	public function index(Request $request, $type = '', $val = '')
	{
		$this->loadLoop($request);
		return $this->custLoop->index($request, $type, $val);
	}
	
	public function loadNodeURL(Request $request, $nodeSlug)
	{
		$this->loadLoop($request);
		return $this->custLoop->loadNodeURL($request, $nodeSlug);
	}
	
	public function testRun(Request $request)
	{
		$this->loadLoop($request);
		return $this->custLoop->testRun($request);
	}
	
	public function ajaxChecks(Request $request)
	{
		$this->loadLoop($request);
		return $this->custLoop->ajaxChecks($request);
	}
	
	public function holdSess(Request $request)
	{
		$this->loadLoop($request);
		return $this->custLoop->holdSess($request);
	}
	
	public function restartSess(Request $request)
	{
		$this->loadLoop($request);
		return $this->custLoop->restartSess($request);
	}
	
	public function sessDump(Request $request)
	{
		$this->loadLoop($request);
		return $this->custLoop->sessDump();
	}
	
	public function afterLogin(Request $request)
	{
		$this->loadLoop($request);
		return $this->custLoop->afterLogin($request);
	}
	
	
	
	protected function loadLoopReport(Request $request)
	{
		$this->loadAbbr();
		$class = "SurvLoop\\Controllers\\SurvLoopReport";
		if ($this->custAbbr != 'SurvLoop')
		{
			$custClass = "app\\Http\\Controllers\\" 
				. $this->custAbbr . "\\" . $this->custAbbr . "Report";
			if (file_exists('../' . $custClass)) $class = ucfirst($custClass);
		}
		eval("\$this->custLoop = new " . $class . "(\$request);");
		return true;
	}
	
	public function byID(Request $request, $coreID, $ComSlug = '')
	{
		$this->loadLoopReport($request);
		return $this->custLoop->byID($request, $coreID, $ComSlug);
	}
	
	public function xmlByID(Request $request, $coreID, $ComSlug = '')
	{
		$this->loadLoopReport($request);
		return $this->custLoop->xmlByID($request, $coreID, $ComSlug);
	}
	
	public function getXmlExample(Request $request)
	{
		$this->loadLoopReport($request);
		return $this->custLoop->getXmlExample($request);
	}
	
	public function genXmlSchema(Request $request)
	{
		$this->loadLoopReport($request);
		return $this->custLoop->genXmlSchema($request);
	}
	
	
	public function freshUser(Request $request)
	{
		$this->loadLoop($request);
		return $this->custLoop->freshUser($request);
	}
	
	public function freshDB(Request $request)
	{
		$this->loadLoop($request);
		return $this->custLoop->freshDB($request);
	}
	
	
	protected function loadLoopAdmin(Request $request)
	{
		$this->loadAbbr();
		$class = "SurvLoop\\Controllers\\AdminSubsController";
		eval("\$this->custLoop = new " . $class . "(\$request);");
		if ($this->custAbbr != 'SurvLoop')
		{
			$custClass = "app\\Http\\Controllers\\"
				. $this->custAbbr . "\\" . $this->custAbbr . "Admin";
			if (file_exists('../' . $custClass)) $class = ucfirst($custClass);
		}
		//echo '<br /><br /><br /><br />' . $class . '<br />'; exit;
		return true;
	}
	
	public function dashboardDefault(Request $request)
	{
		$this->loadLoopAdmin($request);
		return $this->custLoop->dashboardDefault($request);
	}
	
	public function updateProfile(Request $request)
	{
		$this->loadLoopAdmin($request);
		return $this->custLoop->updateProfile($request);
	}
	
	public function showProfile(Request $request)
	{
		$this->loadLoopAdmin($request);
		return $this->custLoop->showProfile($request);
	}
	
	public function listSubsAll(Request $request)
	{
		$this->loadLoopAdmin($request);
		return $this->custLoop->listSubsAll($request);
	}
	
	public function listSubsIncomplete(Request $request)
	{
		$this->loadLoopAdmin($request);
		return $this->custLoop->listSubsIncomplete($request);
	}
	
	public function manageEmails(Request $request)
	{
		$this->loadLoopAdmin($request);
		return $this->custLoop->manageEmails($request);
	}
	
	public function manageEmailsForm(Request $request)
	{
		$this->loadLoopAdmin($request);
		return $this->custLoop->manageEmailsForm($request);
	}
	
	
	
}

?>