<?php
namespace SurvLoop\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\Models\SLDefinitions;

class SurvLoop extends Controller
{
    
    public $classExtension = 'SurvLoop';
    public $custAbbr       = 'SurvLoop';
    public $custLoop       = array();
    public $treeID         = 1;
    
    protected function loadAbbr()
    {
        $chk = SLDefinitions::select('DefDescription')
            ->where('DefDatabase', 1)
            ->where('DefSet', 'System Settings')
            ->where('DefSubset', 'cust-abbr')
            ->first();
        if ($chk && isset($chk->DefDescription)) {
            $this->custAbbr = trim($chk->DefDescription);
        }
        return true;
    }
    
    public function loadLoop(Request $request)
    {
        $this->loadAbbr();
        $class = "SurvLoop\\Controllers\\SurvFormTree";
        if ($this->custAbbr != 'SurvLoop') {
            $custClass = $this->custAbbr . "\\Controllers\\" . $this->custAbbr . "";
            if (class_exists($custClass)) $class = $custClass;
        }
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
    
    public function retrieveUpload(Request $request, $cid = -3, $upID = '')
    {
        $this->loadLoop($request);
        return $this->custLoop->retrieveUpload($request, $cid, $upID);
    }
    
    
    
    protected function loadLoopReport(Request $request)
    {
        $this->loadAbbr();
        $class = "SurvLoop\\Controllers\\SurvLoopReport";
        if ($this->custAbbr != 'SurvLoop') {
            $custClass = $this->custAbbr . "\\Controllers\\" . $this->custAbbr . "Report";
            if (class_exists($custClass)) $class = $custClass;
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
        if ($this->custAbbr != 'SurvLoop') {
            $custClass = "App\\Http\\Controllers\\"
                . $this->custAbbr . "\\" . $this->custAbbr . "Admin";
            if (class_exists($custClass)) {
                $class = $custClass;
            } else {
                $custClass = $this->custAbbr . "\\Controllers\\" . $this->custAbbr . "Admin";
                if (class_exists($custClass)) $class = $custClass;
            }
        }
        eval("\$this->custLoop = new " . $class . "(\$request);");
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
    
    public function manageEmailsForm(Request $request, $emailID = -3)
    {
        $this->loadLoopAdmin($request);
        return $this->custLoop->manageEmailsForm($request, $emailID);
    }
    
    
}
