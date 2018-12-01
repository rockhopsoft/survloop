<?php
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Mail;

use MatthiasMullie\Minify;

use App\Models\User;
use App\Models\SLDatabases;
use App\Models\SLDefinitions;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLNodeResponses;
use App\Models\SLContact;
use App\Models\SLEmails;
use App\Models\SLSess;

use SurvLoop\Controllers\SurvLoopReport;
use SurvLoop\Controllers\SystemDefinitions;
use SurvLoop\Controllers\SurvLoopController;

class AdminController extends SurvLoopController
{
    public $classExtension = 'AdminController';
    
    protected $adminNav    = [];
    protected $admMenuData = [];
    protected $pageIsAdmin = true;
    protected $admInitRun  = false;
    protected $domainPath  = '';
    
    protected function admControlInit(Request $request, $currPage = '')
    {
        if (!$this->admInitRun) {
            $this->admInitRun = true;
            $this->doublecheckSurvTables();
            $this->loadDbLookups($request);
            $this->survLoopInit($request, $currPage, false);
            if ($this->v["uID"] <= 0 || !$this->v["user"]->hasRole('administrator|staff|databaser|partner|volunteer')) {
                echo view('vendor.survloop.inc-js-redirect-home', $this->v)->render();
                exit;
            }
            $this->v["isDash"] = true;
            if ($GLOBALS["SL"]->sysOpts["cust-abbr"] == 'survloop') $GLOBALS["SL"]->sysOpts["cust-abbr"] = 'SurvLoop';
            $this->loadCustReport($request);
            $this->checkCurrPage();
            $this->reloadAdmMenu();
            $this->loadSearchSuggestions();
            $this->initExtra($request);
            $this->initCustViews();
            $this->logPageVisit();
            $this->clearEmpties();
        }
        return true;
    }
    
    protected function reloadAdmMenu()
    {
        $this->v["admMenu"] = $this->getAdmMenu($this->v["currPage"][0]);
        $this->v["belowAdmMenu"] = $this->loadBelowAdmMenu();
        return true;
    }
    
    protected function checkCurrPage()
    {
        /* if (sizeof($this->CustReport) > 0) {
            $custPage = $this->CustReport->getCurrPage();
            if (trim($custPage) != '/') $this->v["currPage"][0] = $custPage;
        } */
        return true;
    }
    
    protected function clearEmpties()
    {
        if (!session()->has('chkClearEmpties') || $GLOBALS["SL"]->REQ->has('refresh')) {
            
            session()->put('chkClearEmpties', 1);
        }
        return true;
    }
    
    protected function loadSearchSuggestions()
    {    
        $this->v["searchSuggest"] = [];
        return true;
    }
    
    public function initPowerUser($uID = -3)
    {
        return [];
    }
    
    protected function tweakAdmMenu($currPage = '')
    {
        return true; 
    }
    
    protected function loadBelowAdmMenu()
    {
        return '';
    }
    
    protected function loadTreesPagesBelowAdmMenu()
    {
        return '<div class="p20"></div>';
    }
    
    protected function loadDbTreeShortNames()
    {
        $dbName = ((isset($GLOBALS["SL"]->dbRow->DbName)) ? $GLOBALS["SL"]->dbRow->DbName : '');
        if (strlen($dbName) > 20 && isset($GLOBALS["SL"]->dbRow->DbName)) {
            $dbName = str_replace($GLOBALS["SL"]->dbRow->DbName, 
                str_replace('_', '', $GLOBALS["SL"]->dbRow->DbPrefix), $dbName);
        }
        $treeID = $GLOBALS["SL"]->treeRow->TreeID;
        $treeName = ((isset($GLOBALS["SL"]->treeName)) ? $GLOBALS["SL"]->treeName : '');
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
            $tree = SLTree::find(1);
            $treeID = $tree->TreeID;
            $treeName = 'Tree: ' . $tree->TreeName;
        }
        return [ $treeID, $treeName, $dbName ];
    }
    
    protected function addAdmMenuHome()
    {
        return $this->admMenuLnk('/dashboard', 'Dashboard', '<i class="fa fa-home" aria-hidden="true"></i>');
    }
    
    protected function loadAdmMenu()
    {
        $treeMenu = [
            $this->addAdmMenuHome(),
            $this->admMenuLnk('javascript:;', 'Submissions', '<i class="fa fa-star"></i>', 1, [
                $this->admMenuLnk('/dashboard/subs/all',        'All Complete'), 
                $this->admMenuLnk('/dashboard/subs/incomplete', 'Incomplete Sessions')
            ])
        ];
        return $this->addAdmMenuBasics($treeMenu);
    }
    
    protected function addAdmMenuBasics($treeMenu = [])
    {
        list($treeID, $treeLabel, $dbName) = $this->loadDbTreeShortNames();
        $treeMenu[] = $this->admMenuLnk('javascript:;', 'Site Content', 
            '<i class="fa fa-file-text-o" aria-hidden="true"></i>', 1, [
            $this->admMenuLnk('/dashboard/pages/list',     'Pages & Reports'), 
            $this->admMenuLnk('javascript:;', 'Surveys & Forms', '', 1, [
                $this->admMenuLnk('/dashboard/surveys/list', 'All Surveys'),
                $this->admMenuLnk('javascript:;', $treeLabel, '', 1, [
                    $this->admMenuLnk('/dashboard/surv-' . $treeID . '/map?all=1&alt=1', 'Full Survey Map'), 
                    $this->admMenuLnk('/dashboard/surv-' . $treeID . '/sessions',        'Session Stats'), 
                    $this->admMenuLnk('/dashboard/surv-' . $treeID . '/stats?all=1',     'Response Stats'),
                    $this->admMenuLnk('/dashboard/surv-' . $treeID . '/data',            'Data Structures'), 
                    $this->admMenuLnk('/dashboard/surv-' . $treeID . '/xmlmap',          'XML Map') 
                    ])
                ]),
            $this->admMenuLnk('/dashboard/pages/snippets', 'Content Snippets'), 
            $this->admMenuLnk('/dashboard/pages/menus',    'Navigation Menus'), 
            $this->admMenuLnk('/dashboard/images/gallery', 'Media Gallery'),
            $this->admMenuLnk('/dashboard/emails',         'Email Templates')
            ]);
        $treeMenu[] = $this->admMenuLnk('javascript:;', 'Database', '<i class="fa fa-database"></i>', 1, [
            $this->admMenuLnk('javascript:;', 'Data Tables', '', 1, [
                $this->admMenuLnk('/dashboard/db',           'Full Table List'),
                $this->admMenuLnk('/dashboard/db/addTable',  'Add A New Table'),
                $this->admMenuLnk('/dashboard/db/sortTable', 'Re-Order Tables'),
                $this->admMenuLnk('/dashboard/db/diagrams',  'Data Diagrams')
                ]), 
            $this->admMenuLnk('javascript:;', 'Data Fields', '', 1, [
                $this->admMenuLnk('/dashboard/db/all',                'Full Field Map'), 
                $this->admMenuLnk('/dashboard/db/field-matrix?alt=1', 'Field Matrix: English'),
                $this->admMenuLnk('/dashboard/db/field-matrix',       'Field Matrix: Geek'),
                $this->admMenuLnk('/dashboard/db/bus-rules',          'Business Rules')
                ]), 
            $this->admMenuLnk('/dashboard/db/definitions', 'Definition Lists'),
            $this->admMenuLnk('/dashboard/db/conds',       'Filters / Conditions'),
            $this->admMenuLnk('/dashboard/db/fieldDescs',  'Field Descriptions'), 
            $this->admMenuLnk('/dashboard/db/fieldXML',    'Field Privacy Settings'), 
            $this->admMenuLnk('/dashboard/db/workflows',   'Process Workflows'),
            $this->admMenuLnk('javascript:;',              'Export', '', 1, [
                $this->admMenuLnk('/dashboard/db/export',         'Full Database Export'),
                $this->admMenuLnk('/dashboard/sl/export/laravel', 'SurvLoop Package')
                ]),
            $this->admMenuLnk('/dashboard/db/switch', '<i class="slGrey">All Databases</i>')
            ]);
        $treeMenu[] = $this->admMenuLnk('javascript:;', 'Users', '<i class="fa fa-users"></i>', 1, [
            $this->admMenuLnk('/dashboard/users', 'All Users'),
            $this->admMenuLnkContact(false)
            ]);
        $treeMenu[] = $this->admMenuLnk('javascript:;', 'Settings', '<i class="fa fa-cogs"></i>', 1, [
            $this->admMenuLnk('javascript:;',      'System Settings', '', 1, [
                $this->admMenuLnk('/dashboard/settings#search',   'Search Engines'),
                $this->admMenuLnk('/dashboard/settings#general',  'General Settings'),
                $this->admMenuLnk('/dashboard/settings#logos',    'Logos & Fonts'),
                $this->admMenuLnk('/dashboard/settings#color',    'Colors'),
                $this->admMenuLnk('/dashboard/settings#hardcode', 'Code HTML CSS JS')
                ]),
            $this->admMenuLnk('javascript:;',       'System Logs', '', 1, [
                $this->admMenuLnk('/dashboard/logs', 'All Logs'),
                $this->admMenuLnk('/dashboard/logs/session-stuff', 'Session Stuff')
                ]),
            $this->admMenuLnk('/dashboard/systems-check',     'System Check'),
            $this->admMenuLnk('/dashboard/systems-update',    'System Updates')
            ]);
        return $treeMenu;
    }
    
    protected function admMenuLnk($url = '', $text = '', $ico = '', $opt = 1, $children = [])
    {
        return [ $url, $text, $ico, $opt, $children ];
    }
    
    protected function admMenuLnkContact($icon = true)
    {
        $cnt = $this->admMenuLnkContactCnt();
        $lnk = 'Contact Form' . (($cnt > 0) ? '<sup id="contactPush" class="red mL5">' . $cnt . '</sup> ' : '');
        $ico = (($icon) ? '<i class="fa fa-envelope-o" aria-hidden="true"></i> ' : '');
        $ret = [ '/dashboard/contact', $lnk, $ico, 1, [] ];
        return $ret;
    }
    
    protected function admMenuLnkContactCnt()
    {
        $chk = SLContact::where('ContFlag', 'Unread')
            ->select('ContID')
            ->get();
        return $chk->count();
    }
    
    public function ajaxContactTabs(Request $request)
    {
        $this->getRecFiltTots('SLContact', 'ContFlag', ['Unread', 'Read', 'Trash'], 'ContID');
        return view('vendor.survloop.admin.contact-tabs', [
            "filtStatus" => (($request->has('tab')) ? $request->get('tab') : 'unread'),
            "recTots"    => $this->v["recTots"]
        ])->render();
    }
    
    public function ajaxContact(Request $request)
    {
        $cID = (($request->has('cid')) ? $request->get('cid') : -3);
        $cRow = (($cID > 0) ? SLContact::find($cID) : []);
        $newStatus = (($request->has('status')) ? $request->get('status') : '');
        if ($cID > 0 && isset($cRow->ContID) && $newStatus != '') {
            $cRow->ContFlag = $newStatus;
            $cRow->save();
        }
        if ($cID > 0 && isset($cRow->ContID)) {
            $currTab = (($request->has('tab')) ? trim($request->get('tab')) : 'unread');
            $newRow = (($currTab == 'unread' && $newStatus != 'Unread')
                || ($currTab == 'all' && $newStatus == 'Trash')) 
                || ($currTab == 'trash' && $newStatus != 'Trash')
                ? '<div class="col-12"><i>Message moved.</i></div>' 
                : view('vendor.survloop.admin.contact-row', [ "contact" => $cRow ])->render();
            return $newRow . '<script type="text/javascript"> $(document).ready(function(){
                setTimeout( function() {
                    var tabLnk = "/ajadm/contact-tabs?tab=' . $currTab . '";
                    $( "#pageTabs" ).load( tabLnk );
                    $( "#contactPush" ).load( "/ajadm/contact-push" );
                }, 100);
            }); </script>';
        }
    }
    
    public function admRedirEdit(Request $request)
    {
        if ($request->has('t') && intVal($request->get('t')) > 0) {
            $tree = SLTree::find(intVal($request->get('t')));
            if ($tree && isset($tree->TreeID)) {
                return view('vendor.survloop.admin.tree.ajax-redir-edit', [ "tree" => $tree ])->render();
            }
        }
        return '';
    }
    
    protected function getAdmMenuLoc($currPage)
    {
        $this->admMenuData["currNavPos"] = [0, -1, -1, -1];
        if (sizeof($this->admMenuData["adminNav"]) > 0) {
            foreach ($this->admMenuData["adminNav"] as $i => $nav) {
                if (sizeof($nav) > 0) {
                    if ($nav[0] == $currPage) {
                        $this->admMenuData["currNavPos"] = [$i, -1, -1, -1];
                    }
                    if (isset($nav[4]) && is_array($nav[4]) && sizeof($nav[4]) > 0) {
                        foreach ($nav[4] as $j => $nA) {
                            if ($nA[0] == $currPage) {
                                $this->admMenuData["currNavPos"] = [$i, $j, -1, -1];
                            }
                            if (isset($nA[4]) && is_array($nA[4]) && sizeof($nA[4]) > 0) {
                                foreach ($nA[4] as $k => $nB) {
                                    if ($nB[0] == $currPage) {
                                        $this->admMenuData["currNavPos"] = [$i, $j, $k, -1];
                                    }
                                    if (isset($nB[4]) && is_array($nB[4]) && sizeof($nB[4]) > 0) {
                                        foreach ($nB[4] as $l => $nC) {
                                            if ($nC[0] == $currPage) {
                                                $this->admMenuData["currNavPos"] = [$i, $j, $k, $l];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return ($this->admMenuData["currNavPos"][0] > -1 || $this->admMenuData["currNavPos"][1] > -1
             || $this->admMenuData["currNavPos"][2] > -1 || $this->admMenuData["currNavPos"][3] > -1);
    }
    
    
    
    
    public function index(Request $request)
    {
        return $this->dashHome($request);
    }
    
    public function dashHome(Request $request)
    {
        $dashTrees = SLTree::where('TreeSlug', 'dashboard')
            ->orderBy('TreeID', 'asc')
            ->get();
        if ($dashTrees->isNotEmpty()) {
            foreach ($dashTrees as $tree) {
                if ($tree->TreeOpts%3 == 0 && $tree->TreeOpts%7 == 0) {
                    $this->syncDataTrees($request, $tree->TreeDatabase, $tree->TreeID);
                    $this->admControlInit($request);
                    $this->v["dashpage"] = $this->CustReport->index($request);
                }
            }
        }
        if (!isset($this->v["currPage"])) $this->admControlInit($request);
        if (!$this->v["user"]->hasRole('administrator|staff|databaser')) {
            if ($this->v["user"]->hasRole('volunteer')) {
                return $this->redir('/volunteer');
            }
            return $this->redir('/login');
        }
        $dbRow = SLDatabases::find(1);
        $this->v["orgMission"] = ((isset($dbRow->DbMission)) ? $dbRow->DbMission : '');
        $this->v["adminNav"] = ((isset($this->admMenuData["adminNav"])) ? $this->admMenuData["adminNav"] : []);
        return view('vendor.survloop.admin.dashboard', $this->v);
    }
    
    
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
    
    public function loadLoop(Request $request, $skipSessLoad = false)
    {
        $this->loadAbbr();
        $class = "SurvLoop\\Controllers\\SurvFormTree";
        if ($this->custAbbr != 'SurvLoop') {
            $custClass = $this->custAbbr . "\\Controllers\\" . $this->custAbbr . "";
            if (class_exists($custClass)) $class = $custClass;
        }
        $this->loadCustReport($request);
        //eval("\$this->CustReport = new " . $class . "(\$request, -3, " . $this->dbID . ", " 
        //    . $this->treeID . ", " . (($skipSessLoad) ? "true" : "false") . ");");
        return true;
    }
    
    public function loadNodeURL(Request $request, $treeSlug = '', $nodeSlug = '')
    {
        if (trim($treeSlug) != '') {
            $urlTree = SLTree::where('TreeSlug', $treeSlug)
                ->first();
            if ($urlTree && isset($urlTree->TreeID)) {
                $this->dbID = $urlTree->TreeDatabase;
                $this->treeID = $urlTree->TreeID;
            }
        }
        $this->syncDataTrees($request, $this->dbID, $this->treeID);
        $this->admControlInit($request, '/dashboard/start/' . $treeSlug);
        $this->v["content"] = '<div class="pT20">' . $this->CustReport->loadNodeURL($request, $nodeSlug) . '</div>';
        $this->v["currInReport"] = $this->CustReport->currInReport();
        return view('vendor.survloop.master', $this->v);
    }
    
    public function loadNodeTreeURLedit(Request $request, $cid = -3, $treeSlug = '')
    {
        return $this->loadNodeTreeURLinner($request, $treeSlug, $cid);
    }
    
    public function loadNodeTreeURL(Request $request, $treeSlug = '')
    {
        return $this->loadNodeTreeURLinner($request, $treeSlug);
    }
    
    public function loadNodeTreeURLinner(Request $request, $treeSlug = '', $cid = -3)
    {
        $this->loadDomain();
        if (trim($treeSlug) != '') {
            $urlTrees = SLTree::where('TreeSlug', $treeSlug)
                ->get();
            if ($urlTrees->isNotEmpty()) {
                foreach ($urlTrees as $urlTree) {
                    if (($urlTree->TreeOpts%3 == 0 && $this->isUserAdmin()) 
                        || ($urlTree->TreeOpts%17 == 0 && ($this->isUserVolun() || $this->isUserPartn() 
                            || $this->isUserStaff() || $this->isUserAdmin()))
                        || ($urlTree->TreeOpts%41 == 0 && ($this->isUserPartn() || $this->isUserAdmin()))
                        || ($urlTree->TreeOpts%43 == 0 && ($this->isUserStaff() || $this->isUserAdmin()))) {
                        $rootNode = SLNode::find($urlTree->TreeFirstPage);
                        if ($rootNode && isset($urlTree->TreeSlug) && isset($rootNode->NodePromptNotes)) {
                            $redir = '/dash/u/' . $urlTree->TreeSlug . '/' . $rootNode->NodePromptNotes 
                                . '?start=1&new=1';
                            $paramTxt = str_replace($this->domainPath . '/start' . ((intVal($cid) > 0) ? '-' . $cid :'')
                                . '/' . $urlTree->TreeSlug, '', $request->fullUrl());
                            if (substr($paramTxt, 0, 1) == '/') $paramTxt = substr($paramTxt, 1);
                            if (trim($paramTxt) != '' && substr($paramTxt, 0, 1) == '?') {
                                $redir .= '&' . substr($paramTxt, 1);
                            }
                            if (intVal($cid) > 0) {
                                $redir .= '&cid=' . $cid;
                                $sess = SLSess::where('SessUserID', Auth::user()->id)
                                    ->where('SessTree', $urlTree->TreeID)
                                    ->where('SessCoreID', $cid)
                                    ->orderBy('updated_at', 'desc')
                                    ->first();
//echo '<br /><br /><br /><pre>'; print_r($sess); echo '</pre>';
                                if (!$sess || !isset($sess->SessID)) {
                                    $sess = new SLSess;
                                    $sess->SessUserID = Auth::user()->id;
                                    $sess->SessTree   = $urlTree->TreeID;
                                    $sess->SessCoreID = $cid;
                                    $sess->save();
                                } else {
                                    $sess->updated_at = date("Y-m-d H:i:s");
                                    $sess->save();
                                }
                                if (session()->has('sessID' . $urlTree->TreeID)) {
                                    session()->put('sessID' . $urlTree->TreeID, $sess->SessID);
                                }
                                if (session()->has('coreID' . $urlTree->TreeID)) {
                                    session()->put('coreID' . $urlTree->TreeID, $cid);
                                }
                            }
//echo '<br /><br /><br />redir: ' . $redir . '<br />'; exit;
                            return redirect($this->domainPath . $redir);
                        }
                    }
                }
            }
        }
        return redirect($this->domainPath . '/dashboard');
    }
                                          
    public function loadPageURL(Request $request, $pageSlug = '')
    {
        if ($this->loadTreeBySlug($request, $pageSlug, 'Page')) {
            $this->loadLoop($request);
            $this->admControlInit($request, '/dash/' . $GLOBALS["SL"]->treeRow->TreeSlug);
            $this->v["content"] = $this->CustReport->index($request);
            $this->v["currInReport"] = $this->CustReport->currInReport();
            if ($request->has('edit') && intVal($request->get('edit')) == 1 && $this->isUserAdmin()) {
                echo '<script type="text/javascript"> window.location="/dashboard/page/' 
                    . $GLOBALS["SL"]->treeID . '?all=1&alt=1&refresh=1"; </script>';
                exit;
            }
            return $this->addAdmCodeToPage(view('vendor.survloop.master', $this->v)->render());
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
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
    
    protected function loadTreeBySlug($request, $treeSlug = '', $type = '')
    {
        if (trim($treeSlug) != '') {
            $urlTrees = [];
            if ($type == 'Page') {
                $redir = $this->chkPageRedir($treeSlug);
                if ($redir != $treeSlug) redirect($redir);
                $urlTrees = SLTree::where('TreeType', 'Page')
                    ->where('TreeSlug', $treeSlug)
                    ->orderBy('TreeID', 'asc')
                    ->get();
            } elseif ($type == 'XML') {
                $urlTrees = SLTree::where('TreeType', 'Survey XML')
                    ->where('TreeSlug', $treeSlug)
                    ->orderBy('TreeID', 'asc')
                    ->get();
            }
            if ($urlTrees->isNotEmpty()) {
                foreach ($urlTrees as $urlTree) {
                    if ($urlTree && isset($urlTree->TreeOpts) && (($urlTree->TreeOpts%3 == 0 && $this->isUserAdmin()) 
                        || ($urlTree->TreeOpts%17 == 0 && ($this->isUserVolun() || $this->isUserPartn() 
                            || $this->isUserStaff() || $this->isUserAdmin()))
                        || ($urlTree->TreeOpts%41 == 0 && ($this->isUserPartn() || $this->isUserAdmin()))
                        || ($urlTree->TreeOpts%43 == 0 && ($this->isUserStaff() || $this->isUserAdmin())))) {
                        $this->syncDataTrees($request, $urlTree->TreeDatabase, $urlTree->TreeID);
                        return true;
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
    
    protected function loadTreeByID($request, $treeID = -3)
    {
        if (intVal($treeID) > 0) {
            $tree = SLTree::find($treeID);
            if ($tree && isset($tree->TreeOpts)) {
                if (($tree->TreeOpts%3 == 0 && $this->isUserAdmin()) 
                    || ($tree->TreeOpts%17 == 0 && ($this->isUserVolun() || $this->isUserAdmin()))
                    || ($tree->TreeOpts%41 == 0 && ($this->isUserPartn() || $this->isUserAdmin()))
                    || ($tree->TreeOpts%43 == 0 && ($this->isUserStaff() || $this->isUserAdmin()))) {
                    $this->syncDataTrees($request, $tree->TreeDatabase, $treeID);
                    return true;
                }
            }
        }
        return false;
    }
    
    protected function syncDataTrees(Request $request, $dbID, $treeID)
    {
        $this->treeID = $treeID;
        if (!isset($GLOBALS["SL"]) || $GLOBALS["SL"]->treeID != $treeID) {
            $GLOBALS["SL"] = new CoreGlobals($request, $dbID, $treeID, $treeID);
            $this->dbID = $GLOBALS["SL"]->dbID;
        }
        return true;
    }
    
    protected function tmpDbSwitch($dbID = 3)
    {
        $this->v["tmpDbSwitchDb"]   = $GLOBALS["SL"]->dbID;
        $this->v["tmpDbSwitchTree"] = $GLOBALS["SL"]->treeID;
        $this->v["tmpDbSwitchREQ"]  = $GLOBALS["SL"]->REQ;
        $GLOBALS["SL"] = new CoreGlobals($this->v["tmpDbSwitchREQ"], $dbID);
        $this->dbID   = $dbID;
        $this->treeID = $GLOBALS["SL"]->treeID;
        return true;
    }

    protected function tmpDbSwitchBack()
    {
        if (isset($this->v["tmpDbSwitchDb"])) {
            $GLOBALS["SL"] = new CoreGlobals($this->v["tmpDbSwitchREQ"], 
                $this->v["tmpDbSwitchDb"], $this->v["tmpDbSwitchTree"], $this->v["tmpDbSwitchTree"]);
            $this->dbID   = $GLOBALS["SL"]->dbID;
            $this->treeID = $GLOBALS["SL"]->treeID;
            return true;
        }
        return false;
    }
    
    public function switchDB(Request $request, $dbID = -3)
    {
        $this->admControlInit($request, '/dashboard/db/switch');
        if ($dbID > 0) {
            $this->switchDatabase($request, $dbID, '/dashboard/db/switch');
            return $this->redir('/dashboard/db/all');
        }
        $this->v["myDbs"] = SLDatabases::orderBy('DbName', 'asc')
            //->whereIn('DbUser', [ 0, $this->v["user"]->id ])
            ->get();
        return view('vendor.survloop.admin.db.switch', $this->v);
    }
    
    public function switchTreeAdmin(Request $request, $treeID = -3)
    {
        $this->admControlInit($request, '/dashboard/tree/switch');
        if ($treeID > 0) {
            $this->switchTree($treeID, '/dashboard/tree/switch', $request);
            if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
                return $this->redir('/dashboard/page/' . $treeID . '?all=1&alt=1&refresh=1');
            }
            return $this->redir('/dashboard/surv-' . $treeID . '/map?all=1&alt=1&refresh=1');
        }
        $this->v["myTrees"] = SLTree::where('TreeDatabase', $GLOBALS["SL"]->dbID)
            ->where('TreeType', 'NOT LIKE', 'Survey XML')
            ->where('TreeType', 'NOT LIKE', 'Other Public XML')
            ->where('TreeType', 'NOT LIKE', 'Page')
            ->orderBy('TreeName', 'asc')
            ->get();
        $this->v["myTreeNodes"] = [];
        if ($this->v["myTrees"]->isNotEmpty()) {
            foreach ($this->v["myTrees"] as $tree) {
                $nodes = SLNode::where('NodeTree', $tree->TreeID)
                    ->select('NodeID')
                    ->get();
                $this->v["myTreeNodes"][$tree->TreeID] = $nodes->count();
            }
        }
        return view('vendor.survloop.admin.tree.switch', $this->v);
    }
    
    public function sysSettings(Request $request)
    {
        $this->admControlInit($request, '/dashboard/settings#search');
        $GLOBALS["SL"]->addAdmMenuHshoos([
            '/dashboard/settings#search',
            '/dashboard/settings#general', 
            '/dashboard/settings#logos',
            '/dashboard/settings#color',
            '/dashboard/settings#hardcode'
            ]);
        $this->reloadAdmMenu();
        $this->getCSS($request);
        $this->v["sysDef"] = new SystemDefinitions;
        $this->v["sysDef"]->prepSysSettings($request);
        $this->v["currMeta"] = [
            "title" => ((isset($GLOBALS['SL']->sysOpts['meta-title'])) ? $GLOBALS['SL']->sysOpts['meta-title'] : ''),
            "desc"  => ((isset($GLOBALS['SL']->sysOpts['meta-desc'])) ? $GLOBALS['SL']->sysOpts['meta-desc'] : ''),
            "wrds"  => ((isset($GLOBALS['SL']->sysOpts['meta-keywords'])) ?$GLOBALS['SL']->sysOpts['meta-keywords']:''),
            "img"   => ((isset($GLOBALS['SL']->sysOpts['meta-img'])) ? $GLOBALS['SL']->sysOpts['meta-img'] : ''),
            "slug"  => false, 
            "base"  => ''
            ];
        return view('vendor.survloop.admin.system-all-settings', $this->v);
    }
    
    public function sysSettingsRaw(Request $request)
    {
        $this->admControlInit($request, '/dashboard/settings-raw');
        $this->v["sysDef"] = new SystemDefinitions;
        $this->v["sysDef"]->prepSysSettings($request);
        return view('vendor.survloop.admin.systemsettings', $this->v["sysDef"]->v);
    }
    
    
    protected function blurbLoad($blurbID)
    {
        return SLDefinitions::where('DefID', $blurbID)
            ->where('DefDatabase', $this->dbID)
            ->where('DefSet', 'Blurbs')
            ->first();
    }
    
    public function blurbEdit(Request $request, $blurbID)
    {
        $this->admControlInit($request, '/dashboard/pages/list');
        $this->v["blurbRow"] = $this->blurbLoad($blurbID);
        $this->v["needsWsyiwyg"] = true;
        if ($this->v["blurbRow"]->DefIsActive <= 0 || $this->v["blurbRow"]->DefIsActive%3 > 0) {
            $GLOBALS["SL"]->pageAJAX .= ' $("#DefDescriptionID").summernote({ height: 500 }); ';
        }
        return view('vendor.survloop.admin.blurb-edit', $this->v);
    }
    
    public function blurbNew(Request $request)
    {
        if (isset($request->newBlurbName) && trim($request->newBlurbName) != '') {
            $blurb = new SLDefinitions;
            $blurb->DefDatabase = $this->dbID;
            $blurb->DefSet      = 'Blurbs';
            $blurb->DefSubset   = $request->newBlurbName;
            $blurb->save();
            return $blurb->DefID;
        }
        return -3;
    }
    
    public function blurbEditSave(Request $request)
    {
        $blurb = $this->blurbLoad($request->DefID);
        $blurb->DefSubset      = $request->DefSubset;
        $blurb->DefDescription = $request->DefDescription;
        $blurb->DefIsActive = 1;
        if ($request->has('optHardCode') && intVal($request->optHardCode) == 3) $blurb->DefIsActive *= 3;
        $blurb->save();
        return $this->redir('/dashboard/pages/snippets/' . $blurb->DefID);
    }
    
    
    public function getCSS(Request $request)
    {
        $this->survLoopInit($request, '/dashboard/settings');
        if (!is_dir('../storage/app/sys')) mkdir('../storage/app/sys');
        $this->v["sysDef"] = new SystemDefinitions;
        $css = $this->v["sysDef"]->loadCss();
        $custCSS = SLDefinitions::where('DefDatabase', $this->dbID)
            ->where('DefSet', 'Style CSS')
            ->where('DefSubset', 'main')
            ->first();
        $css["raw"] = (($custCSS && isset($custCSS->DefDescription)) ? $custCSS->DefDescription : '');
        
        $syscss = view('vendor.survloop.styles-css-1', [ "css" => $css ])->render();
        file_put_contents("../storage/app/sys/sys1.css", $syscss);
        $minifier = new Minify\CSS("../storage/app/sys/sys1.css");
        $minifier->minify("../storage/app/sys/sys1.min.css");
        
        $syscss = view('vendor.survloop.styles-css-2', [ "css" => $css ])->render();
        file_put_contents("../storage/app/sys/sys2.css", $syscss);
        $minifier = new Minify\CSS("../storage/app/sys/sys2.css");
        $minifier->minify("../storage/app/sys/sys2.min.css");
        
        $minifier = new Minify\CSS("../storage/app/sys/sys1.min.css");
        $minifier->add("../vendor/components/jqueryui/themes/base/jquery-ui.min.css");
        $minifier->add("../vendor/twbs/bootstrap/dist/css/bootstrap.min.css");
        if (isset($GLOBALS["SL"]->sysOpts["css-extra-files"]) 
            && trim($GLOBALS["SL"]->sysOpts["css-extra-files"]) != '') {
            $files = $GLOBALS["SL"]->mexplode(',', $GLOBALS["SL"]->sysOpts["css-extra-files"]);
            foreach ($files as $f) $minifier->add(trim($f));
        }
        $minifier->add("../storage/app/sys/sys2.min.css");
        //$minifier->add("../vendor/forkawesome/fork-awesome/css/fork-awesome.min.css");
        $minifier->minify("../storage/app/sys/sys-all.min.css");
        
        $scriptsjs = view('vendor.survloop.scripts-js', [ "css" => $css ])->render();
        file_put_contents("../storage/app/sys/sys.js", $scriptsjs);
        $minifier = new Minify\JS("../storage/app/sys/sys.js");
        $minifier->minify("../storage/app/sys/sys.min.js");
        
        $minifier = new Minify\JS("../vendor/components/jquery/jquery.min.js");
        $minifier->add("../vendor/components/jqueryui/jquery-ui.min.js");
        $minifier->add("../vendor/twbs/bootstrap/dist/js/bootstrap.min.js");
        $minifier->add("../vendor/wikiworldorder/survloop/src/Public/scripts-lib.js");
        $minifier->add("../storage/app/sys/sys.min.js");
        $minifier->minify("../storage/app/sys/sys-all.min.js");
        
        $log = SLDefinitions::where('DefSet', 'System Settings')
            ->where('DefSubset', 'log-css-reload')
            ->update([ 'DefDescription' => time() ]);
        return ':)';
    }
    
    protected function eng2data($name)
    {
        return preg_replace("/[^a-zA-Z0-9]+/", "", ucwords($name));
    }
    
    protected function eng2abbr($name)
    {
        $abbr = preg_replace("/[^A-Z]+/", "", $name);
        if (strlen($abbr) > 1) return $abbr;
        return substr(preg_replace("/[^a-zA-Z0-9]+/", "", $name), 0, 3);
    }
    
    protected function isCoreTbl($tblID)
    {
        $chkCore = SLTree::where('TreeCoreTable', '=', $tblID)
            ->get();
        return $chkCore->isNotEmpty();
    }
    
    
    
    public function userManage(Request $request)
    {
        $this->admControlInit($request, '/dashboard/users');
        $this->loadPrintUsers();
        return view('vendor.survloop.admin.user-manage', $this->v);
    }
    
    public function userManagePost(Request $request)
    {
        $users = User::where('name', 'NOT LIKE', 'Session#%')
            ->get();
        if ($users->isNotEmpty()) {
            $users[0]->loadRoles();
            $roles = $users[0]->roles;
            foreach ($users as $i => $usr) {
                foreach ($roles as $role) {
                    if ($request->has('user'.$usr->id) && in_array($role->DefSubset, $request->get('user'.$usr->id))) {
                        if (!$usr->hasRole($role->DefSubset)) {
                            $usr->assignRole($role->DefSubset);
                        }
                    } elseif ($usr->hasRole($role->DefSubset)) {
                        $usr->revokeRole($role->DefSubset);
                    }
                }
            }
        }
        return $this->userManage($request);
    }
    
    protected function loadPrintUsers()
    {
        $this->v["printVoluns"] = [ [], [], [], [], [], [] ]; // voluns, staff, admin
        $users = User::orderBy('name', 'asc') // where('name', 'NOT LIKE', 'Session#%')
            ->get();
        foreach ($users as $i => $usr) {
            $list = 3;
            if ($usr->hasRole('administrator'))  $list = 0;
            elseif ($usr->hasRole('databaser'))  $list = 1;
            elseif ($usr->hasRole('staff'))      $list = 2;
            elseif ($usr->hasRole('partner'))    $list = 3;
            elseif ($usr->hasRole('volunteer'))  $list = 4;
            else $list = 5;
            $this->v["printVoluns"][$list][] = $usr;
        }
        $this->v["disableAdmin"] = ((!$this->v["user"]->hasRole('administrator')) ? ' DISABLED ' : '');
        return true;
    }
    
    public function userEmailing(Request $request)
    {
        $this->admControlInit($request, '/dashboard/users/emailing');
        $this->loadPrintUsers();
        return view('vendor.survloop.admin.user-emailing', $this->v);
    }
    
    function manageEmails(Request $request)
    {
        $this->admControlInit($request, '/dashboard/emails');
        $this->v["emailList"] = SLEmails::orderBy('EmailName', 'asc')
        	->orderBy('EmailType', 'asc')
        	->get();
        $this->v["cssColors"] = $GLOBALS["SL"]->getCssColorsEmail();
        $GLOBALS["SL"]->pageAJAX .= '$(document).on("click", "a.emailLnk", function() {
            $("#emailBody"+$(this).attr("id").replace("showEmail", "")).slideToggle("fast"); });
        $(document).on("click", "#showAll", function() { $(".emailBody").slideToggle("fast"); }); ';
        return view('vendor.survloop.admin.email-manage', $this->v);
    }
    
    function manageEmailsForm(Request $request, $emailID = -3)
    {
        $this->admControlInit($request, '/dashboard/emails');
        $this->v["currEmailID"] = $emailID;
        $this->v["currEmail"] = new SLEmails;
        if ($emailID > 0) $this->v["currEmail"] = SLEmails::find($emailID);
        $this->v["needsWsyiwyg"] = true;
        $GLOBALS["SL"]->pageAJAX .= ' $("#emailBodyID").summernote({ height: 500 }); ';
        return view('vendor.survloop.admin.email-form', $this->v);
    }
    
    function manageEmailsPost(Request $request, $emailID)
    {
        if ($request->has('emailType')) {
            $currEmail = new SLEmails;
            if ($request->emailID > 0 && $request->emailID == $emailID) {
                $currEmail = SLEmails::find($request->emailID);
            }
            $currEmail->EmailType    = $request->emailType;
            $currEmail->EmailName    = $request->emailName;
            $currEmail->EmailSubject = $request->emailSubject;
            $currEmail->EmailBody    = $request->emailBody;
            $currEmail->EmailOpts    = 1;
            $currEmail->save();
        }
        return $this->redir('/dashboard/emails');
    }
    
    public function manageContact(Request $request)
    {
        $this->admControlInit($request, '/dashboard/contact');
        $status = [''];
        $this->v["recs"] = [];
        $this->getRecFiltTots('SLContact', 'ContFlag', ['Unread', 'Read', 'Trash'], 'ContID');
        $this->v["filtStatus"] = 'unread';
        if ($request->has('tab')) $this->v["filtStatus"] = trim($request->get('tab'));
        if (in_array($this->v["filtStatus"], ['', 'unread'])) {
            $this->v["recs"] = SLContact::where('ContFlag', 'Unread')
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($this->v["filtStatus"] == 'all') {
            $this->v["recs"] = SLContact::whereIn('ContFlag', ['Read', 'Unread'])
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($this->v["filtStatus"] == 'trash') {
            $this->v["recs"] = SLContact::where('ContFlag', 'Trash')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        $this->v["currPage"][1] = 'Contact Form Messages';
        $GLOBALS["SL"]->pageAJAX .= '$(".changeContStatus").change(function(){
            var cID = $(this).attr( "name" ).replace( "ContFlag", "" );
            var postUrl = "/ajadm/contact?' . ((isset($filtStatus)) ? 'tab=' . $filtStatus . '&' : '') 
                . 'cid="+cID+"&status="+$(this).val();
            $( "#wrapItem"+cID+"" ).load( postUrl );
        });';
        return view('vendor.survloop.admin.contact', $this->v);
    }
    
    public function postNodeURL(Request $request)
    {
        if ($request->has('step') && $request->has('tree') && intVal($request->tree) > 0) {
            $this->loadTreeByID($request, $request->tree);
            $this->admControlInit($request, '/dash/u/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/' . $request->nodeSlug);
            echo '<div class="pT20">' . $this->CustReport->loadNodeURL($request, $request->nodeSlug) . '</div>';
        }
        exit;
    }
    
    public function ajaxChecksAdmin(Request $request, $type = '')
    {
        $this->admControlInit($request, '/ajadm/' . $type);
        $newStatus = (($request->has('status')) ? trim($request->get('status')) : '');
        if ($type == 'contact') {
            return $this->ajaxContact($request);
        } elseif ($type == 'contact-tabs') {
            return $this->ajaxContactTabs($request);
        } elseif ($type == 'contact-push') {
            return $this->admMenuLnkContactCnt();
        } elseif ($type == 'redir-edit') {
            return $this->admRedirEdit($request);
        } elseif ($type == 'send-email') {
            return $this->ajaxSendEmail($request);
        }
        return $this->CustReport->ajaxChecks($request, $type);
    }
    
    public function ajaxSendEmail(Request $request)
    {
        $emaID = (($request->has('e') && intVal($request->get('e')) > 0) ? intVal($request->get('e')) : 0);
        $treeID = (($request->has('t') && intVal($request->get('t')) > 0) ? intVal($request->get('t')) : 1);
        $coreID = (($request->has('c') && intVal($request->get('c')) > 0) ? intVal($request->get('c')) : 0);
        $this->CustReport->loadTree($treeID);
        $emaToArr = [];
        $emaToUsrID = 0;
        $ret = $emaTo = $emaSubj = $emaCont = '';
        $currEmail = SLEmails::find($emaID);
        if ($currEmail && isset($currEmail->EmailSubject)) {
            if ($coreID > 0) {
                $this->CustReport->loadSessionData($GLOBALS["SL"]->coreTbl, $coreID);
                $emaFld = $GLOBALS["SL"]->getCoreEmailFld();
                if (isset($this->CustReport->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->{ $emaFld })) {
                    $emaTo = $this->CustReport->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->{ $emaFld };
                    $emaToArr[] = [$emaTo, ''];
                }
            }
            if ($request->has('o') && trim($request->get('o')) != '') {
                $emaToArr = [];
                $overrideEmail = $GLOBALS["SL"]->mexplode(';', $request->get('o'));
                if (sizeof($overrideEmail) > 0) {
                    $emaTo = $overrideEmail[0];
                    foreach ($overrideEmail as $ovr) $emaToArr[] = [trim($ovr), ''];
                }
            }
            if (sizeof($emaToArr) > 0) {
                foreach ($emaToArr as $j => $e) {
                    $emaToName = '';
                    $chkEma = User::where('email', $e[0])
                        ->first();
                    if (trim($e[0]) != '' && $chkEma && isset($chkEma->name)) $emaToName = $chkEma->name;
                    $emaToArr[$j][1] = $emaToName;
                }
            }
            $emaSubj = $this->CustReport->emailRecordSwap($currEmail->EmailSubject);
            $emaCont = $this->CustReport->emailRecordSwap($currEmail->EmailBody);
            $sffx = 'e' . $emaID . 't' . $treeID . 'c' . $coreID;
            $ret .= '<a id="hidivBtnMsgDeet' . $sffx . '" class="hidivBtn" href="javascript:;">Message sent to '
                . '<i class="slBlueDark">' . $emaTo . ' (' . $emaToName . ')</i>: ' . $emaSubj 
                . '"</a><div id="hidivMsgDeet' . $sffx . '" class="disNon container"><h2>' . $emaSubj . '</h2><p>' 
                . $emaCont . '</p><hr><hr></div>';
            $replyTo = [ 'info@' . $GLOBALS['SL']->getParentDomain(), $GLOBALS["SL"]->sysOpts["site-name"] ];
            if ($request->has('r') && trim($request->get('r')) != '') $replyTo[0] = trim($request->get('r'));
            if ($request->has('rn') && trim($request->get('rn')) != '') $replyTo[1] = trim($request->get('rn'));
            if (!$GLOBALS["SL"]->isHomestead()) {
                $this->CustReport->sendEmail($emaCont, $emaSubj, $emaToArr, [], [], $replyTo);
            }
            $emaToUsr = User::where('email', $emaTo)->first();
            if ($emaToUsr && isset($emaToUsr->id)) $emaToUsrID = $emaToUsr->id;
            $this->CustReport->logEmailSent($emaCont, $emaSubj, $emaTo, $emaID, $treeID, $coreID, $emaToUsrID);
        } else {
            $ret .= '<i class="red">Email template not found.</i>';
        }
        if ($request->has('l') && trim($request->get('l')) != '') {
            //$ret .= $GLOBALS["SL"]->opnAjax() . '$("#' . trim($request->get('l')) . '").fadeOut(100);' 
            //    . $GLOBALS["SL"]->clsAjax();
        }
//echo '<br />ALLO?!<br />';
        return $ret;
    }
    
    public function systemsCheck(Request $request)
    {
        $this->admControlInit($request, '/dashboard/systems-check');
        if ($request->has('testEmail') && intVal($request->get('testEmail')) == 1) {
            $this->v["testResults"] = '';
            if ($request->has('sendTest') && intVal($request->get('sendTest')) == 1
                && $request->has('emailTo') && trim($request->emailTo) != '') {
                $emaTo = trim($request->emailTo);
                $emaToArr = [ [ $emaTo, 'Test Message' ] ];
                $emaSubj = 'Email Flight Test from ' . $GLOBALS["SL"]->sysOpts["site-name"];
                $emaCont = '<p>Hi there friend,</p><p>This has been a flight test from ' 
                    . $GLOBALS["SL"]->sysOpts["site-name"] . '</p>';
                if (!$GLOBALS["SL"]->isHomestead()) {
                    //Mail::send('errors.401', [], function ($message) {
                    //    $message->to('yomojo@gmail.com')->subject('this works!'); });
                    $this->sendEmail($emaCont, $emaSubj, $emaToArr);
                }
                $this->logEmailSent($emaCont, $emaSubj, $emaTo, 0, $this->treeID, $this->coreID, $this->v["uID"]);
                $this->v["testResults"] .= '<div class="container"><h2>' . $emaSubj . '</h2>' . $emaCont 
                    . '<hr><hr><i class="slBlueDark">to ' . $emaTo . '</i></div>';
            }
            return view('vendor.survloop.admin.systems-check-email', $this->v);
        }
        $tree1 = SLTree::find(1);
        $this->v["sysChks"] = [];
        $this->v["sysChks"][] = ['Home',         '/'];
        $this->v["sysChks"][] = ['Survey Start', '/start/' . $tree1->TreeSlug . ''];
        $this->v["sysChks"][] = ['Search Empty', '/search-results/1?s='];
        $this->v["sysChks"][] = ['Search Test',  '/search-results/1?s=testing'];
        $this->v["sysChks"][] = ['XML-Example',  '/' . $tree1->TreeSlug . '-xml-example'];
        $this->v["sysChks"][] = ['XML-All',      '/' . $tree1->TreeSlug . '-xml-all'];
        $this->v["sysChks"][] = ['XML-Schema',   '/' . $tree1->TreeSlug . '-xml-schema'];
        return view('vendor.survloop.admin.systems-check', $this->v);
    }
    
    public function logsOverview(Request $request)
    {
        $this->admControlInit($request, '/dashboard/logs');
        $this->v["logs"] = [
            "session" => $this->logPreview('session-stuff')
            ];
        $this->v["phpInfo"] = $request->has('phpinfo');
        return view('vendor.survloop.admin.logs-overview', $this->v);
    }
    
    public function logsSessions(Request $request)
    {
        $this->admControlInit($request, '/dashboard/logs/session-stuff');
        $this->v["content"] .= '<h2 class="slBlueDark"><i class="fa fa-eye"></i> Logs of Session Stuff</h2>'
            . '<div class="p20">' . $this->logLoad('session-stuff') . '</div>';
        return view('vendor.survloop.master', $this->v);
    }
    
    
    public function navMenus(Request $request)
    {
        $this->admControlInit($request, '/dashboard/pages/menus');
        $this->v["cntMax"] = 25;
        if ($request->has('sub') && intVal($request->get('sub')) == 1) {
            for ($i = 0; $i < $this->v["cntMax"]; $i++) {
                if ($i < sizeof($this->v["navMenu"])) {
                    if ($request->has('mainNavTxt' . $i)) {
                        SLDefinitions::where('DefSet', 'Menu Settings')
                            ->where('DefSubset', 'main-navigation')
                            ->where('DefDatabase', 1)
                            ->where('DefOrder', $i)
                            ->update([
                                'DefValue'       => $request->get('mainNavTxt' . $i),
                                'DefDescription' => $request->get('mainNavLnk' . $i)
                            ]);
                    } else {
                        SLDefinitions::where('DefSet', 'Menu Settings')
                            ->where('DefSubset', 'main-navigation')
                            ->where('DefDatabase', 1)
                            ->where('DefOrder', $i)
                            ->delete();
                    }
                } elseif ($request->has('mainNavTxt' . $i)) {
                    $newLnk = new SLDefinitions;
                    $newLnk->DefSet         = 'Menu Settings';
                    $newLnk->DefSubset      = 'main-navigation';
                    $newLnk->DefDatabase    = 1;
                    $newLnk->DefOrder       = $i;
                    $newLnk->DefValue       = $request->get('mainNavTxt' . $i);
                    $newLnk->DefDescription = $request->get('mainNavLnk' . $i);
                    $newLnk->save();
                }
            }
            $this->loadNavMenu();
        }
        $this->v["cnt"] = 0;
        return view('vendor.survloop.admin.manage-menus', $this->v);
    }
    
    
    public function imgGallery(Request $request)
    {
        $this->admControlInit($request, '/dashboard/images/gallery');
        if ($request->has('sub') && intVal($request->get('sub')) == 1) {
            
        }
        $this->v["imgSelect"] = $GLOBALS["SL"]->getImgSelect('-3', $GLOBALS["SL"]->dbID);
        return view('vendor.survloop.admin.images-gallery', $this->v);
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
    
}
