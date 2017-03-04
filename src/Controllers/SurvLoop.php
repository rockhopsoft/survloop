<?php
namespace SurvLoop\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\Models\SLNode;
use App\Models\SLTree;
use App\Models\SLDefinitions;

class SurvLoop extends Controller
{
    
    public $classExtension = 'SurvLoop';
    public $custAbbr       = 'SurvLoop';
    public $custLoop       = [];
    public $dbID           = 1;
    public $treeID         = 1;
    public $domainPath     = 'http://homestead.app';
    
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
    
    protected function loadDomain()
    {
        $appUrl = SLDefinitions::select('DefDescription')
            ->where('DefDatabase', 1)
            ->where('DefSet', 'System Settings')
            ->where('DefSubset', 'app-url')
            ->first();
        if ($appUrl && isset($appUrl->DefDescription)) {
            $this->domainPath = $appUrl->DefDescription;
        }
        return $this->domainPath;
    }
    
    protected function loadTreeByID($request, $treeID = -3)
    {
        if (intVal($treeID) > 0) {
            $tree = SLTree::find($treeID);
            if ($tree && isset($tree->TreeOpts)) {
                if ($tree->TreeOpts%3 > 0 
                    || (Auth::user() && Auth::user()->hasRole('administrator|staff|databaser|brancher|volunteer'))) {
                    $this->syncDataTrees($request, $tree->TreeDatabase, $treeID);
                    return true;
                }
            }
        }
        return false;
    }
    
    protected function loadTreeBySlug($request, $treeSlug = '', $page = false)
    {
        if (trim($treeSlug) != '') {
            $urlTrees = [];
            if (!$page) {
                $urlTrees = SLTree::where('TreeSlug', $treeSlug)
                    ->where('TreeType', 'Primary Public')
                    ->orderBy('TreeID', 'asc')
                    ->get();
            } else {
                $urlTrees = SLTree::where('TreeSlug', $treeSlug)
                    ->where('TreeType', 'Page')
                    ->orderBy('TreeID', 'asc')
                    ->get();
            }
            if ($urlTrees && sizeof($urlTrees) > 0) {
                foreach ($urlTrees as $urlTree) {
                    if ($urlTree && isset($urlTree->TreeOpts)) {
                        if ($urlTree->TreeOpts%3 > 0) {
                            $this->syncDataTrees($request, $urlTree->TreeDatabase, $urlTree->TreeID);
                            return true;
                        } else { // maybe this admin tree has a public XML Tree
                            $xmlChk = SLTree::where('TreeSlug', $urlTree->TreeSlug)
                                ->where('TreeType', 'Primary Public XML')
                                ->orderBy('TreeID', 'asc')
                                ->first();
                            if ($xmlChk && isset($xmlChk->TreeOpts) && $xmlChk->TreeOpts%3 > 0) {
                                $this->syncDataTrees($request, $urlTree->TreeDatabase, $urlTree->TreeID);
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
    
    public function loadLoop(Request $request)
    {
        $this->loadAbbr();
        $class = "SurvLoop\\Controllers\\SurvFormTree";
        if ($this->custAbbr != 'SurvLoop') {
            $custClass = $this->custAbbr . "\\Controllers\\" . $this->custAbbr . "";
            if (class_exists($custClass)) $class = $custClass;
        }
        eval("\$this->custLoop = new " . $class . "(\$request, -3, " . $this->dbID . ", " . $this->treeID . ");");
        return true;
    }
    
    public function index(Request $request, $type = '', $val = '')
    {
        if ($request->has('step') && $request->has('tree') && intVal($request->tree) > 0) {
            $this->loadTreeByID($request, $request->tree);
        }
        $this->loadLoop($request);
        return $this->custLoop->index($request, $type, $val);
    }
    
    public function loadNodeURL(Request $request, $treeSlug = '', $nodeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->loadNodeURL($request, $nodeSlug);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function loadNodeTreeURL(Request $request, $treeSlug = '')
    {
        $this->loadDomain();
        if (trim($treeSlug) != '') {
            $urlTrees = SLTree::where('TreeSlug', $treeSlug)
                ->get();
            if ($urlTrees && sizeof($urlTrees) > 0) {
                foreach ($urlTrees as $urlTree) {
                    if ($urlTree && isset($urlTree->TreeOpts) && $urlTree->TreeOpts%3 > 0) {
                        $rootNode = SLNode::find($urlTree->TreeFirstPage);
                        if ($rootNode && isset($urlTree->TreeSlug) && isset($rootNode->NodePromptNotes)) {
                            $redir = '/u/' . $urlTree->TreeSlug . '/' . $rootNode->NodePromptNotes;
                            return redirect($this->domainPath . $redir);
                        }
                    }
                }
            }
        }
        return redirect($this->domainPath . '/');
    }
    
    public function loadPageURL(Request $request, $pageSlug = '')
    {
        if ($this->loadTreeBySlug($request, $pageSlug, true)) {
            $this->loadLoop($request);
            return $this->custLoop->index($request);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function loadPageHome(Request $request)
    {
        $this->loadDomain();
        $urlTrees = SLTree::where('TreeType', 'Page')
            ->get();
        if ($urlTrees && sizeof($urlTrees) > 0) {
            foreach ($urlTrees as $urlTree) {
                if (isset($urlTree->TreeOpts) && $urlTree->TreeOpts%7 == 0) {
                    $this->syncDataTrees($request, $urlTree->TreeDatabase, $urlTree->TreeID);
                    $this->loadLoop($request);
                    return $this->custLoop->index($request);
                }
            }
        }
        return $this->index($request);
        //return redirect($this->domainPath . '/');
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
    
    public function sortLoop(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->sortLoop($request);
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
    
    public function switchSess(Request $request, $cid)
    {
        $this->loadLoop($request);
        return $this->custLoop->switchSess($request, $cid);
    }
    
    public function delSess(Request $request, $cid)
    {
        $this->loadLoop($request);
        return $this->custLoop->delSess($request, $cid);
    }
    
    public function afterLogin(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->afterLogin($request);
    }
    
    public function retrieveUpload(Request $request, $treeID = -3, $cid = -3, $upID = '')
    {
        if ($this->loadTreeByID($request, $treeID)) {
            $this->loadLoop($request);
            return $this->custLoop->retrieveUpload($request, $cid, $upID);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    
    
    protected function loadLoopReport(Request $request)
    {
        $this->loadAbbr();
        $class = "SurvLoop\\Controllers\\SurvLoopReport";
        if ($this->custAbbr != 'SurvLoop') {
            $custClass = $this->custAbbr . "\\Controllers\\" . $this->custAbbr . "Report";
            if (class_exists($custClass)) $class = $custClass;
        }
        eval("\$this->custLoop = new " . $class . "(\$request, -3, " . $this->dbID . ", " . $this->treeID . ");");
        return true;
    }
    
    public function byID(Request $request, $treeSlug, $cid, $ComSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoopReport($request);
            return $this->custLoop->byID($request, $cid, $ComSlug);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function xmlAll(Request $request, $treeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoopReport($request);
            return $this->custLoop->xmlAll($request);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function xmlByID(Request $request, $treeSlug, $cid)
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoopReport($request);
            return $this->custLoop->xmlByID($request, $cid);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function getXmlExample(Request $request, $treeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoopReport($request);
            return $this->custLoop->getXmlExample($request);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function genXmlSchema(Request $request, $treeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoopReport($request);
            return $this->custLoop->genXmlSchema($request);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function chkEmail(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->chkEmail($request);
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
            $custClass = "App\\Http\\Controllers\\" . $this->custAbbr . "\\" . $this->custAbbr . "Admin";
            if (class_exists($custClass)) {
                $class = $custClass;
            } else {
                $custClass = $this->custAbbr . "\\Controllers\\" . $this->custAbbr . "Admin";
                if (class_exists($custClass)) $class = $custClass;
            }
        }
        eval("\$this->custLoop = new " . $class . "(\$request, -3, " . $this->dbID . ", " . $this->treeID . ");");
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
    
    protected function syncDataTrees(Request $request, $dbID, $treeID)
    {
        $this->dbID = $dbID;
        $this->treeID = $treeID;
        $GLOBALS["SL"] = new DatabaseLookups($request, $dbID, $treeID, $treeID);
        return true;
    }
    
}
