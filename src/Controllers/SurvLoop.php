<?php
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\File\File;

use App\Models\SLNode;
use App\Models\SLTree;
use App\Models\SLTables;
use App\Models\SLFields;
use App\Models\SLDefinitions;
use App\Models\SLSess;

use SurvLoop\Controllers\SurvLoopInstaller;

class SurvLoop extends Controller
{
    
    public $classExtension = 'SurvLoop';
    public $custAbbr       = 'SurvLoop';
    public $custLoop       = [];
    public $dbID           = 1;
    public $treeID         = 1;
    public $domainPath     = 'http://homestead.test';
    public $cacheKey       = '';
    public $pageContent    = '';
    
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
    
    public function checkHttpsDomain(Request $request)
    {
        if (isset($this->domainPath) && strpos($request->fullUrl(), $this->domainPath) === false) {
            if (strpos($this->domainPath, 'https://') !== false 
                && strpos($request->fullUrl(), str_replace('https://', 'http://', $this->domainPath)) !== false) {
                header("Location: " . str_replace('http://', 'https://', $request->fullUrl()));
                exit;
            }
        }
        return true;
    }
    
    protected function syncDataTrees(Request $request, $dbID = 1, $treeID = 1)
    {
        $this->dbID = $dbID;
        $this->treeID = $treeID;
        $GLOBALS["SL"] = new CoreGlobals($request, $dbID, $treeID, $treeID);
        return true;
    }
    
    protected function isAdmin()
    {
        return (Auth::user() && Auth::user()->hasRole('administrator'));
    }
    
    protected function loadTreeByID($request, $treeID = -3, $skipChk = false)
    {
        if (intVal($treeID) > 0) {
            $tree = SLTree::find($treeID);
            if ($tree && isset($tree->TreeOpts)) {
                if ($skipChk || $tree->TreeOpts%3 > 0 
                    || (Auth::user() && Auth::user()->hasRole('administrator|staff|databaser|volunteer'))) {
                    $this->syncDataTrees($request, $tree->TreeDatabase, $treeID);
                    return true;
                }
            }
        }
        return false;
    }
    
    protected function loadTreeBySlug(Request $request, $treeSlug = '', $page = false)
    {
        if ($this->topCheckCache($request) && (!$request->has('edit') || intVal($request->get('edit')) != 1 
            || !$this->isAdmin())) {
            return $this->addAdmCodeToPage($GLOBALS["SL"]->swapSessMsg($this->pageContent));
        }
        if (trim($treeSlug) != '') {
            $urlTrees = SLTree::where('TreeSlug', $treeSlug)
                    ->where('TreeType', (($page) ? 'Page' : 'Survey'))
                    ->orderBy('TreeID', 'asc')
                    ->get();;
            if ($page) {
                $redir = $this->chkPageRedir($treeSlug);
                if ($redir != $treeSlug) {
                    echo '<script type="text/javascript"> window.location="' . $redir . '"; </script>';
                    exit;
                }
            }
            if ($urlTrees->isNotEmpty()) {
                foreach ($urlTrees as $urlTree) {
                    if ($urlTree && isset($urlTree->TreeOpts)) {
                        if ($urlTree->TreeOpts%3 > 0 && $urlTree->TreeOpts%17 > 0 && $urlTree->TreeOpts%41 > 0
                            && $urlTree->TreeOpts%43 > 0) {
                            $this->syncDataTrees($request, $urlTree->TreeDatabase, $urlTree->TreeID);
                            return true;
                        } else { // maybe this admin tree has a public XML Tree
                            $xmlChk = SLTree::where('TreeSlug', $urlTree->TreeSlug)
                                ->where('TreeType', 'Survey XML')
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
    
    protected function chkPageRedir($treeSlug = '')
    {
        if (trim($treeSlug) != '') {
            $redirTree = SLTree::where('TreeSlug', $treeSlug)
                ->where('TreeType', 'Redirect')
                ->orderBy('TreeID', 'asc')
                ->first();
            if ($redirTree && isset($redirTree->TreeName) && trim($redirTree->TreeName) != '') {
                $redirURL = $redirTree->TreeName;
                if (strpos($redirURL, $this->domainPath) === false && substr($redirURL, 0, 1) != '/'
                    && strpos($redirURL, 'http://') === false && strpos($redirURL, 'https://') === false) {
                    $redirURL = '/' . $redirURL;
                }
                return $redirURL;
            }
        }
        return $treeSlug;
    }
    
    public function loadLoop(Request $request, $skipSessLoad = false)
    {
        $this->loadAbbr();
        $class = "SurvLoop\\Controllers\\SurvFormTree";
        if ($this->custAbbr != 'SurvLoop') {
            $custClass = $this->custAbbr . "\\Controllers\\" . $this->custAbbr . "";
            if (class_exists($custClass)) $class = $custClass;
        }
        eval("\$this->custLoop = new " . $class . "(\$request, -3, " . $this->dbID . ", " 
            . $this->treeID . ", " . (($skipSessLoad) ? "true" : "false") . ");");
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
    
    public function processEmailConfirmToken(Request $request, $token = '', $tokenB = '')
    { 
        $this->loadLoop($request);
        return $this->custLoop->processEmailConfirmToken($request, $token, $tokenB);
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
        return $this->loadNodeTreeURLInner($request, $treeSlug);
    }
    
    public function loadNodeTreeURLedit(Request $request, $cid = -3, $treeSlug = '')
    {
        return $this->loadNodeTreeURLInner($request, $treeSlug, $cid);
    }
    
    public function loadNodeTreeURLInner(Request $request, $treeSlug = '', $cid = -3)
    {
        $this->loadDomain();
        $this->checkHttpsDomain($request);
        if (trim($treeSlug) != '') {
            $urlTrees = SLTree::where('TreeSlug', $treeSlug)
                ->get();
            if ($urlTrees->isNotEmpty()) {
                foreach ($urlTrees as $urlTree) {
                    if ($urlTree && isset($urlTree->TreeOpts) && $urlTree->TreeOpts%3 > 0) {
                        $rootNode = SLNode::find($urlTree->TreeFirstPage);
                        if ($rootNode && isset($urlTree->TreeSlug) && isset($rootNode->NodePromptNotes)) {
                            $redir = '/u/' . $urlTree->TreeSlug . '/' . $rootNode->NodePromptNotes . '?start=1&new=1';
                            $paramTxt = str_replace($this->domainPath . '/start/' . $urlTree->TreeSlug, '', 
                                $request->fullUrl());
                            if (substr($paramTxt, 0, 1) == '/') $paramTxt = substr($paramTxt, 1);
                            if (trim($paramTxt) != '' && substr($paramTxt, 0, 1) == '?') {
                                $redir .= '&' . substr($paramTxt, 1);
                            }
                            if (intVal($cid) > 0) {
                                $sess = SLSess::where('SessUserID', Auth::user()->id)
                                    ->where('SessTree', $urlTree->TreeID)
                                    ->where('SessCoreID', $cid)
                                    ->orderBy('updated_at', 'desc')
                                    ->first();
                                if (!$sess || !isset($sess->SessID)) {
                                    $sess = new SLSess;
                                    $sess->SessUserID = Auth::user()->id;
                                    $sess->SessTree   = $urlTree->TreeID;
                                    $sess->SessCoreID = $cid;
                                    $sess->save();
                                }
                                if ($request->has("n") && intVal($request->get("n")) > 0) {
                                    $sess->update([ 'SessCurrNode' => intVal($request->get("n")) ]);
                                }
                                session()->put('sessID' . $urlTree->TreeID, $sess->SessID);
                                session()->put('coreID' . $urlTree->TreeID, $cid);
                            }
                            return redirect($this->domainPath . $redir);
                        }
                    }
                }
            }
        }
        return redirect($this->domainPath . '/');
    }
    
    public function loadPageURLrawID(Request $request, $pageSlug = '', $cid = -3, $view = '')
    {
        return $this->loadPageURL($request, $pageSlug, $cid, $view, true);
    }
    
    public function loadPageURL(Request $request, $pageSlug = '', $cid = -3, $view = '', $skipPublic = false)
    {
        if ($this->loadTreeBySlug($request, $pageSlug, true)) {
            if ($request->has('edit') && intVal($request->get('edit')) == 1 && $this->isAdmin()) {
                echo '<script type="text/javascript"> window.location="/dashboard/page/' 
                    . $this->treeID . '?all=1&alt=1&refresh=1"; </script>';
                exit;
            }
            $this->loadLoop($request);
            if ($cid > 0) {
                $this->custLoop->loadSessionData($GLOBALS["SL"]->coreTbl, $cid, $skipPublic);
                if ($request->has('hideDisclaim') && intVal($request->hideDisclaim) == 1) {
                    $this->custLoop->hideDisclaim = true;
                }
                $GLOBALS["SL"]->x["pageSlugSffx"] = '/read-' . $cid;
                $GLOBALS["SL"]->x["pageView"] = trim($view); // blank results in user default
                if ($GLOBALS["SL"]->x["pageView"] != '') {
                    $GLOBALS["SL"]->x["pageSlugSffx"] .= '/' . $GLOBALS["SL"]->x["pageView"];
                }
            }
            $this->pageContent = $this->custLoop->index($request);
            if ($GLOBALS["SL"]->treeRow->TreeOpts%29 > 0 && $cid <= 0) { // then simple page which can be cached
                $this->topSaveCache();
            }
            return $this->addAdmCodeToPage($GLOBALS["SL"]->swapSessMsg($this->pageContent));
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function loadPageHome(Request $request)
    {
        if ($this->topCheckCache($request) && (!$request->has('edit') || intVal($request->get('edit')) != 1 
            || !$this->isAdmin())) {
            return $this->addAdmCodeToPage($GLOBALS["SL"]->swapSessMsg($this->pageContent));
        }
        $this->loadDomain();
        $this->checkHttpsDomain($request);
        $urlTree = SLTree::where('TreeType', 'Page')
            ->orderBy('TreeID', 'asc')
            ->get();
        if ($urlTree->isNotEmpty()) {
            foreach ($urlTree as $tree) {
                if ($tree->TreeOpts%7 == 0 && $tree->TreeOpts%3 > 0 && $tree->TreeOpts%17 > 0 && $tree->TreeOpts%41 > 0
                    && $tree->TreeOpts%43 > 0) {
                    $redir = $this->chkPageRedir($tree->TreeSlug);
                    if ($redir != $tree->TreeSlug) return redirect($redir);
                    if ($request->has('edit') && intVal($request->get('edit')) == 1 && $this->isAdmin()) {
                        echo '<script type="text/javascript"> window.location="/dashboard/page/' 
                            . $tree->TreeID . '?all=1&alt=1&refresh=1"; </script>';
                        exit;
                    }
                    $this->syncDataTrees($request, $tree->TreeDatabase, $tree->TreeID);
                    $this->loadLoop($request);
                    $this->pageContent = $this->custLoop->index($request);
                    if ($tree->TreeOpts%29 > 0) { // then simple page which can be cached
                        $this->topSaveCache();
                    }
                    return $this->addAdmCodeToPage($GLOBALS["SL"]->swapSessMsg($this->pageContent));
                }
            }
        }
        
        // else Home Page not found, so let's quickly create one
        $GLOBALS["SL"] = new CoreGlobals($request, 1, 1);
        $installer = new SurvLoopInstaller;
        $installer->checkSysInit();
        return '<center><br /><br /><i>Reloading...</i><br /> <iframe src="/dashboard/css-reload" frameborder=0
            style="width: 60px; height: 60px; border: 0px none;"></iframe></center>
            <script type="text/javascript"> setTimeout("window.location=\'/\'", 2000); </script>';
    }
    
    protected function addAdmCodeToPage($pageContent)
    {
        $extra = '';
        if (Auth::user() && isset(Auth::user()->id) && Auth::user()->hasRole('administrator')) {
            $extra .= ' addTopNavItem("pencil", "?edit=1"); ';
        }
        if (trim($extra) != '') {
            $extra = '<script type="text/javascript"> ' . $extra . ' </script>';
        }
        return str_replace("</body>", $extra . "\n</body>", $pageContent);
    }
    
    public function testRun(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->testRun($request);
    }
    
    public function ajaxChecks(Request $request, $type = '')
    {
        $this->loadLoop($request);
        
        return $this->custLoop->ajaxChecks($request, $type);
    }
    
    public function ajaxChecksAdmin(Request $request, $type = '')
    {
        $this->loadLoopAdmin($request);
        return $this->custLoop->ajaxChecksAdmin($request, $type);
    }
    
    public function sortLoop(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->sortLoop($request);
    }
    
    public function showProfile(Request $request, $uname = '')
    {
        $profileTrees = SLTree::where('TreeType', 'Page')
            ->where('TreeOpts', '>=', 23) // special page for managing member profiles
            ->get();
        if ($profileTrees->isNotEmpty()) {
            foreach ($profileTrees as $tree) {
                if ($tree->TreeOpts%23 == 0) {
                    $this->syncDataTrees($request, $tree->TreeDatabase, $tree->TreeID);
                    $this->loadLoop($request);
                    $this->custLoop->setCurrUserProfile($uname);
                    return $this->custLoop->index($request);
                }
            }
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }

    public function showMyProfile(Request $request)
    {
        $this->loadDomain();
        $this->checkHttpsDomain($request);
        if (Auth::user() && isset(Auth::user()->name)) {
            return $this->showProfile($request);
            //return redirect($this->domainPath . '/profile/' . urlencode(Auth::user()->name));
        }
        return redirect($this->domainPath . '/');
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
    
    public function switchSess(Request $request, $treeID, $cid)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request);
        return $this->custLoop->switchSess($request, $cid);
    }
    
    public function delSess(Request $request, $treeID, $cid)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request);
        return $this->custLoop->delSess($request, $cid);
    }
    
    public function cpySess(Request $request, $treeID, $cid)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request);
        return $this->custLoop->cpySess($request, $cid);
    }
    
    public function afterLogin(Request $request)
    {
        if (session()->has('redir2') && trim(session()->get('redir2')) != '') {
            return redirect(trim(session()->get('redir2')));
        }
        if (session()->has('lastTree')) {
            $tree = SLTree::find(session()->get('lastTree'));
            if ($tree && isset($tree->TreeDatabase)) {
                $this->syncDataTrees($request, $tree->TreeDatabase, $tree->TreeID);
            }
        }
        if (session()->has('sessID') && session()->get('sessID') > 0) {
            
            
        }
        $this->loadLoop($request);
        return $this->custLoop->afterLogin($request);
    }
    
    public function retrieveUpload(Request $request, $treeSlug = '', $cid = -3, $upID = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->retrieveUpload($request, $cid, $upID);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    
    
    protected function loadLoopReport(Request $request, $skipSessLoad = false)
    {
        $this->loadAbbr();
        $class = "SurvLoop\\Controllers\\SurvLoopReport";
        if ($this->custAbbr != 'SurvLoop') {
            $custClass = $this->custAbbr . "\\Controllers\\" . $this->custAbbr . "Report";
            if (class_exists($custClass)) $class = $custClass;
        }
        eval("\$this->custLoop = new " . $class . "(\$request, -3, " . $this->dbID . ", " 
            . $this->treeID . ", " . (($skipSessLoad) ? "true" : "false") . ");");
        return true;
    }
    
    public function byID(Request $request, $treeSlug, $cid, $coreSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoopReport($request);
            return $this->custLoop->byID($request, $cid, $coreSlug, $request->has('ajax'));
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function fullByID(Request $request, $treeSlug, $cid, $coreSlug = '')
    {
        $GLOBALS["fullAccess"] = true;
        return $this->byID($request, $treeSlug, $cid, $coreSlug = '');
    }
        
    public function pdfByID(Request $request, $treeSlug, $cid)
    {
        $GLOBALS["SL"]->x["isPrintPDF"] = true;
        return $this->byID($request, $treeSlug, $cid);
    }
    
    public function fullPdfByID(Request $request, $treeSlug, $cid)
    {
        $GLOBALS["fullAccess"] = true;
        return $this->pdfByID($request, $treeSlug, $cid);
    }
    
    public function fullXmlByID(Request $request, $treeSlug, $cid)
    {
        $GLOBALS["fullAccess"] = true;
        return $this->xmlByID($request, $treeSlug, $cid);
    }
    
    public function tokenByID(Request $request, $pageSlug, $cid, $token)
    {
        return $this->loadPageURL($request, $pageSlug, $cid, 'token-' . trim($token));
        //return $this->byID($request, $treeSlug, $cid);
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
    
    public function userManagePost(Request $request)
    {
        $this->loadLoopAdmin($request);
        return $this->custLoop->userManagePost($request);
    }
    
    public function userManage(Request $request)
    {
        $this->loadLoopAdmin($request);
        return $this->custLoop->userManage($request);
    }
    
    public function updateProfile(Request $request)
    {
        $this->loadLoopAdmin($request);
        return $this->custLoop->updateProfile($request);
    }
    
    public function adminProfile(Request $request)
    {
        $this->loadLoopAdmin($request);
        return $this->custLoop->adminProfile($request);
    }
    
    public function systemsCheck(Request $request)
    {
        $this->loadLoopAdmin($request);
        return $this->custLoop->systemsCheck($request);
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
    
    public function printSubView(Request $request, $treeID = 1, $cid = -3)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoopAdmin($request);
        return $this->custLoop->printSubView($request, $cid);
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
    
    // SurvLoop Widgets
    
    public function ajaxMultiRecordCheck(Request $request, $treeID = 1)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->multiRecordCheck(true);
    }
    
    public function ajaxRecordFulls(Request $request, $treeID = 1)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoopReport($request, true);
        return $this->custLoop->printReports($request);
    }
    
    public function ajaxRecordPreviews(Request $request, $treeID = 1)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoopReport($request, true);
        return $this->custLoop->printReports($request, false);
    }
    
    public function ajaxEmojiTag(Request $request, $treeID = 1, $recID = -3, $defID = -3)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoopReport($request, true);
        return $this->custLoop->ajaxEmojiTag($request, $recID, $defID);
    }
    
    public function ajaxGraph(Request $request, $gType = '', $treeID = 1, $nID = -3)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoopReport($request, true);
        return $this->custLoop->ajaxGraph($request, $gType, $nID);
    }
    
    public function searchBar(Request $request, $treeID = 1)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoopReport($request, true);
        return $this->custLoop->searchBar();
    }
    
    public function searchResults(Request $request, $treeID = 1, $ajax = 0)
    {
        $this->loadTreeByID($request, $treeID, true);
        $this->loadLoopReport($request, true);
        return $this->custLoop->searchResults($request, $ajax);
    }
    
    public function searchResultsAjax(Request $request, $treeID = 1)
    {
        return $this->searchResults($request, $treeID, 1);
    }
    
    public function widgetCust(Request $request, $treeID = 1, $nID = -3)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoopReport($request, true);
        return $this->custLoop->widgetCust($request, $nID);
    }
    
    public function getSetFlds(Request $request, $treeID = 1, $rSet = '')
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoopReport($request, true);
        return $this->custLoop->getSetFlds($request, $rSet);
    }
    
    public function getUploadFile(Request $request, $abbr, $file)
    {
        $filename = '../storage/app/up/' . $abbr . '/' . $file;
        $handler = new File($filename);
        $file_time = $handler->getMTime(); // Get the last modified time for the file (Unix timestamp)
        $lifetime = 86400; // One day in seconds
        $header_etag = md5($file_time . $filename);
        $header_last_modified = gmdate('r', $file_time);
        $headers = array(
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Last-Modified'       => $header_last_modified,
            'Cache-Control'       => 'must-revalidate',
            'Expires'             => gmdate('r', $file_time + $lifetime),
            'Pragma'              => 'public',
            'Etag'                => $header_etag
        );
        
        // Is the resource cached?
        $h1 = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) 
            && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $header_last_modified);
        $h2 = (isset($_SERVER['HTTP_IF_NONE_MATCH']) 
            && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $header_etag);
        if (($h1 || $h2) && !$request->has('refresh')) {
            return Response::make('', 304, $headers); 
        }
        // File (image) is cached by the browser, so we don't have to send it again
        
        $headers = array_merge($headers, [
            'Content-Type'   => $handler->getMimeType(),
            'Content-Length' => $handler->getSize()
        ]);
        return Response::make(file_get_contents($filename), 200, $headers);
    }
    
    protected function topGenCacheKey()
    {
        $this->cacheKey = '/cache/page-' . substr($_SERVER["REQUEST_URI"], 1) . '.html';
        return $this->cacheKey;
    }
    
    protected function topCheckCache(Request $request)
    {
        $this->topGenCacheKey();
        if ($request->has('refresh')) {
            if (file_exists($this->cacheKey)) Storage::delete($this->cacheKey);
            return false;
        }
        if (file_exists($this->cacheKey)) {
            $this->pageContent = Storage::get($this->cacheKey);
            return true;
        }
        return false;
    }
    
    protected function topSaveCache()
    {
        if (trim($this->cacheKey) == '') $this->topGenCacheKey();
        Storage::put($this->cacheKey, $this->pageContent);
        return true;
    }
    
    public function jsLoadMenu(Request $request)
    {
        $ret = '';
        if (Auth::user() && isset(Auth::user()->id)) {
            $userName = Auth::user()->name;
            if (strpos($userName, 'Session#') !== false) {
                $userName = substr(Auth::user()->email, 0, strpos(Auth::user()->email, '@'));
            }
            $ret .= "addTopNavItem('" . $userName . "', '/my-profile\" id=\"loginLnk'); ";
            $ret .= 'addSideNavItem("Logout", "/logout"); addSideNavItem("My Profile", "/my-profile"); ';
            if (Auth::user()->hasRole('administrator')) {
                $ret .= 'addTopNavItem("Dashboard", "/dashboard"); '
                    . 'addSideNavItem("Admin Dashboard", "/dashboard"); ';
            }
        } else {
            $ret .= "addTopNavItem('Sign Up', '/register\" id=\"loginLnk'); addTopNavItem('Login', '/login'); "
                . 'addSideNavItem("Login", "/login"); addSideNavItem("Sign Up", "/register"); ';
        }
        return '<script type="text/javascript"> ' . $ret . ' </script>';
    }
    
    public function timeOut(Request $request)
    {
        return view('auth.dialog-check-form-sess', [ "req" => $request ]);
    }
    
    public function getJsonSurvLoopStats(Request $request)
    {
        $this->syncDataTrees($request);
    	$types = $GLOBALS["SL"]->loadTreeNodeStatTypes();
    	$stats = [ "Date" => date("Y-m-d") ];
    	$survs = $pages = [];
    	$stats["DbTables"] = SLTables::where('TblDatabase', 1)->count();
    	$stats["DbFields"] = SLFields::where('FldDatabase', 1)->count();
    	$chk = SLTree::where('TreeType', 'Survey')
    		->where('TreeDatabase', 1)
    		->select('TreeID')
    		->get();
    	if ($chk->isNotEmpty()) {
    		foreach ($chk as $t) $survs[] = $t->TreeID;
    	}
    	$stats["Surveys"] = sizeof($survs);
    	$stats["SurveyNodes"] = SLNode::whereIn('NodeTree', $survs)->count();
    	$stats["SurveyNodesMult"] = SLNode::whereIn('NodeTree', $survs)->whereIn('NodeType', $types["choic"])->count();
    	$stats["SurveyNodesOpen"] = SLNode::whereIn('NodeTree', $survs)->whereIn('NodeType', $types["quali"])->count();
    	$stats["SurveyNodesNumb"] = SLNode::whereIn('NodeTree', $survs)->whereIn('NodeType', $types["quant"])->count();
    	$chk = SLTree::where('TreeType', 'Page')
    		->where('TreeDatabase', 1)
    		->select('TreeID')
    		->get();
    	if ($chk->isNotEmpty()) {
    		foreach ($chk as $t) $pages[] = $t->TreeID;
    	}
    	$stats["Pages"] = sizeof($pages);
    	$stats["PageNodes"] = SLNode::whereIn('NodeTree', $pages)->count();
        header('Content-Type: application/json');
        echo json_encode($stats);
        exit;
    }
    
}