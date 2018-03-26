<?php
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use MatthiasMullie\Minify;

use App\Models\User;
use App\Models\SLDatabases;
use App\Models\SLDefinitions;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLNodeResponses;
use App\Models\SLContact;
use App\Models\SLEmails;

use SurvLoop\Controllers\SurvLoopReport;
use SurvLoop\Controllers\SurvLoopController;

class AdminController extends SurvLoopController
{
    public $classExtension = 'AdminController';
    
    protected $adminNav    = array();
    protected $admMenuData = array();
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
            if ($this->v["uID"] <= 0 || !$this->v["user"]->hasRole('administrator|staff|databaser|volunteer')) {
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
        $this->v["searchSuggest"] = array();
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
        $ret = '';
        /*
        $trees = SLTree::where('TreeDatabase', $this->dbID)
            ->where('TreeType', 'Primary Public')
            ->orderBy('TreeID', 'asc')
            ->get();
        if ($trees && sizeof($trees) > 0) {
            $ret .= '<i class="fPerc133">Form Trees:</i><br />';
            foreach ($trees as $tree) {
                if ($this->treeID == $tree->TreeID) {
                    $ret .= '<a href="/dashboard/tree-' . $tree->TreeID . '/map?all=1&alt=1" class="fPerc133"><b>' 
                        . $tree->TreeName . '</b> <i class="fa fa-pencil" aria-hidden="true"></i></a><br />';
                } else {
                    $ret .= '<a href="/dashboard/tree/switch/' . $tree->TreeID . '">' . $tree->TreeName . '</a><br />';
                }
            }
        }
        $trees = SLTree::where('TreeDatabase', $this->dbID)
            ->where('TreeType', 'Page')
            ->orderBy('TreeName', 'asc')
            ->get();
        $admPages = '';
        if ($trees && sizeof($trees) > 0) {
            $ret .= '<br /><i class="fPerc133">Site Pages:</i><br />';
            foreach ($trees as $tree) {
                $curr = '<a href="/dashboard/page/' . $tree->TreeID . '?all=1&alt=1&refresh=1">' 
                    . $tree->TreeName . '</a><br />';
                if ($this->treeID == $tree->TreeID) {
                    $curr = '<a href="/dashboard/page/' . $tree->TreeID . '?all=1&alt=1&refresh=1" class="fPerc133"><b>' 
                        . $tree->TreeName . '</b> <i class="fa fa-pencil" aria-hidden="true"></i></a><br />';
                }
                if ($tree->TreeOpts%3 == 0) $admPages .= $curr;
                else $ret .= $curr;
            }
        }
        if (trim($admPages) != '') {
            $ret .= '<br /><i class="fPerc133">Admin Pages:</i><br />' . $admPages;
        }
        */
        return $ret . '<div class="p20"></div>';
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
            $this->admMenuLnk('/dashboard/pages/list',     'Pages & Redirects'), 
            $this->admMenuLnk('/dashboard/pages/snippets', 'Content Snippets'), 
            $this->admMenuLnk('/dashboard/pages/menus',    'Navigation Menus'), 
            $this->admMenuLnk('/dashboard/images/gallery', 'Media Gallery'),
            $this->admMenuLnk('/dashboard/emails',         'Email Templates')
            ]);
        $treeMenu[] = $this->admMenuLnk('javascript:;', 'Surveys / Forms', 
            '<i class="fa fa-snowflake-o" aria-hidden="true"></i>', 1, [
            $this->admMenuLnk('/dashboard/tree-' . $treeID . '/map?all=1&alt=1', $treeLabel, '', 1, [
                $this->admMenuLnk('/dashboard/tree-' . $treeID . '/map?all=1&alt=1', 'Full Survey Map'), 
                $this->admMenuLnk('/dashboard/tree-' . $treeID . '/sessions',        'Session Stats'), 
                $this->admMenuLnk('/dashboard/tree-' . $treeID . '/stats?all=1',     'Response Stats'),
                $this->admMenuLnk('/dashboard/tree-' . $treeID . '/data',            'Data Structures'), 
                $this->admMenuLnk('/dashboard/tree-' . $treeID . '/xmlmap',          'XML Map') 
                ]), 
            $this->admMenuLnk('/dashboard/trees/list',     'All Surveys'),
            $this->admMenuLnk('/dashboard/trees/list#new', 'Add New Survey'),
            ]);
        $treeMenu[] = $this->admMenuLnk('javascript:;', 'Database', '<i class="fa fa-database"></i>', 1, [
            $this->admMenuLnk('/dashboard/db', 'All Tables', '', 1, [
                $this->admMenuLnk('/dashboard/db/addTable',  'Add A New Table'),
                $this->admMenuLnk('/dashboard/db/sortTable', 'Re-Order Tables'),
                $this->admMenuLnk('/dashboard/db/diagrams',  'Data Diagrams')
                ]), 
            $this->admMenuLnk('/dashboard/db/all', 'All Fields', '', 1, [
                $this->admMenuLnk('/dashboard/db/field-matrix?alt=1', 'Field Matrix: English'),
                $this->admMenuLnk('/dashboard/db/field-matrix',       'Field Matrix: Geek'),
                $this->admMenuLnk('/dashboard/db/bus-rules',          'Business Rules')
                ]), 
            $this->admMenuLnk('/dashboard/db/definitions',    'Definition Lists'),
            $this->admMenuLnk('/dashboard/db/conds',          'Filters / Conditions'),
            $this->admMenuLnk('/dashboard/db/fieldDescs/all', 'Field Descriptions', '', 1, [
                $this->admMenuLnk('/dashboard/db/fieldDescs', 'Empty Descriptions')
                ]), 
            $this->admMenuLnk('/dashboard/db/fieldXML',  'Field Privacy Settings'), 
            $this->admMenuLnk('/dashboard/db/workflows', 'Process Workflows'),
            $this->admMenuLnk('/dashboard/db/export',    'Export', '', 1, [
                $this->admMenuLnk('/dashboard/db/export',         'MYSQL Export'),
                $this->admMenuLnk('/dashboard/db/export/laravel', 'Export for Laravel'),
                $this->admMenuLnk('/dashboard/db/install',        'Auto-Install Here')
                ]),
            $this->admMenuLnk('/dashboard/db/switch', '<i class="slGrey">All Databases</i>')
            ]);
        $treeMenu[] = $this->admMenuLnk('javascript:;', 'Users', '<i class="fa fa-users"></i>', 1, [
            $this->admMenuLnk('/dashboard/users', 'All Users'),
            $this->admMenuLnkContact(false)
            ]);
        $treeMenu[] = $this->admMenuLnk('javascript:;', 'Settings', '<i class="fa fa-cogs"></i>', 1, [
            $this->admMenuLnk('/dashboard/settings#search',   'Search Engines'),
            $this->admMenuLnk('/dashboard/settings#general',  'General Settings'),
            $this->admMenuLnk('/dashboard/settings#color',    'Logos, Colors, Fonts'),
            $this->admMenuLnk('/dashboard/settings#hardcode', 'Code: HTML, CSS, JS'),
            $this->admMenuLnk('/dashboard/logs',              'System Logs', '', 1, [
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
        return sizeof($chk);
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
        if ($cID > 0 && isset($cRow->ContID) && $newStatus != '') {
            $cRow->ContFlag = $newStatus;
            $cRow->save();
        }
        if ($cID > 0 && isset($cRow->ContID)) {
            $currTab = (($request->has('tab')) ? trim($request->get('tab')) : 'unread');
            $newRow = (($currTab == 'unread' && $newStatus != 'Unread')
                || ($currTab == 'all' && $newStatus == 'Trash')) 
                || ($currTab == 'trash' && $newStatus != 'Trash')
                ? '<div class="col-md-12"><i>Message moved.</i></div>' 
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
        $this->admMenuData["currNavPos"] = array(0, -1, -1, -1);
        if (sizeof($this->admMenuData["adminNav"]) > 0) {
            foreach ($this->admMenuData["adminNav"] as $i => $nav) {
                if (sizeof($nav) > 0) {
                    if ($nav[0] == $currPage) {
                        $this->admMenuData["currNavPos"] = array($i, -1, -1, -1);
                    }
                    if (isset($nav[4]) && is_array($nav[4]) && sizeof($nav[4]) > 0) {
                        foreach ($nav[4] as $j => $nA) {
                            if ($nA[0] == $currPage) {
                                $this->admMenuData["currNavPos"] = array($i, $j, -1, -1);
                            }
                            if (isset($nA[4]) && is_array($nA[4]) && sizeof($nA[4]) > 0) {
                                foreach ($nA[4] as $k => $nB) {
                                    if ($nB[0] == $currPage) {
                                        $this->admMenuData["currNavPos"] = array($i, $j, $k, -1);
                                    }
                                    if (isset($nB[4]) && is_array($nB[4]) && sizeof($nB[4]) > 0) {
                                        foreach ($nB[4] as $l => $nC) {
                                            if ($nC[0] == $currPage) {
                                                $this->admMenuData["currNavPos"] = array($i, $j, $k, $l);
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
//echo '<br /><br /><br />' . $currPage . ' - '; print_r($this->admMenuData["currNavPos"]); echo '<br />';
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
        if ($dashTrees && sizeof($dashTrees) > 0) {
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
        eval("\$this->CustReport = new " . $class . "(\$request, -3, " . $this->dbID . ", " 
            . $this->treeID . ", " . (($skipSessLoad) ? "true" : "false") . ");");
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
    
    public function loadNodeTreeURL(Request $request, $treeSlug = '')
    {
        $this->loadDomain();
        if (trim($treeSlug) != '') {
            $urlTrees = SLTree::where('TreeSlug', $treeSlug)
                ->get();
            if ($urlTrees && sizeof($urlTrees) > 0) {
                foreach ($urlTrees as $urlTree) {
                    if ($urlTree->TreeOpts%3 == 0) {
                        $rootNode = SLNode::find($urlTree->TreeFirstPage);
                        if ($rootNode && isset($urlTree->TreeSlug) && isset($rootNode->NodePromptNotes)) {
                            $redir = '/dash/' . $urlTree->TreeSlug . '/' . $rootNode->NodePromptNotes;
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
            return view('vendor.survloop.master', $this->v);
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
            if ($type = 'Page') {
                $redir = $this->chkPageRedir($treeSlug);
                if ($redir != $treeSlug) return redirect($redir);
                $urlTrees = SLTree::where('TreeSlug', $treeSlug)
                    ->orderBy('TreeID', 'asc')
                    ->get();
            } elseif ($type = 'XML') {
                $urlTrees = SLTree::where('TreeType', 'Primary Public XML')
                    ->where('TreeSlug', $treeSlug)
                    ->orderBy('TreeID', 'asc')
                    ->get();
            } else {
                $urlTrees = SLTree::where('TreeType', 'Page')
                    ->where('TreeSlug', $treeSlug)
                    ->orderBy('TreeID', 'asc')
                    ->get();
            }
            if ($urlTrees && sizeof($urlTrees) > 0) {
                foreach ($urlTrees as $urlTree) {
                    if ($urlTree && isset($urlTree->TreeOpts) && $urlTree->TreeOpts%3 == 0) {
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
                if ($tree->TreeOpts%3 == 0) {
                    $this->syncDataTrees($request, $tree->TreeDatabase, $treeID);
                    return true;
                }
            }
        }
        return false;
    }
    
    protected function syncDataTrees(Request $request, $dbID, $treeID)
    {
        if (!isset($this->v["isAdmin"])) $this->v["isAdmin"] = $this->isUserAdmin();
        $this->treeID = $treeID;
        if (!isset($GLOBALS["SL"]) || $GLOBALS["SL"]->treeID != $treeID) {
            $GLOBALS["SL"] = new DatabaseLookups($request, $this->v["isAdmin"], $dbID, $treeID, $treeID);
            $this->dbID = $GLOBALS["SL"]->dbID;
        }
        return true;
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
            return $this->redir('/dashboard/tree-' . $treeID . '/map?all=1&alt=1&refresh=1');
        }
        $this->v["myTrees"] = SLTree::where('TreeDatabase', $GLOBALS["SL"]->dbID)
            ->where('TreeType', 'NOT LIKE', 'Primary Public XML')
            ->where('TreeType', 'NOT LIKE', 'Other Public XML')
            ->where('TreeType', 'NOT LIKE', 'Page')
            ->orderBy('TreeName', 'asc')
            ->get();
        $this->v["myTreeNodes"] = [];
        if ($this->v["myTrees"] && sizeof($this->v["myTrees"]) > 0) {
            foreach ($this->v["myTrees"] as $tree) {
                $nodes = SLNode::where('NodeTree', $tree->TreeID)
                    ->select('NodeID')
                    ->get();
                $this->v["myTreeNodes"][$tree->TreeID] = sizeof($nodes);
            }
        }
        return view('vendor.survloop.admin.tree.switch', $this->v);
    }
    
    public function loadCss($dbID = -3)
    {
        if ($dbID <= 0) $dbID = $this->dbID;
        $css = [];
        $cssRaw = SLDefinitions::where('DefDatabase', $dbID)
            ->where('DefSet', 'Style Settings')
            ->orderBy('DefOrder')
            ->get();
        if (!$cssRaw || sizeof($cssRaw) == 0) {
            $dbID = 1;
            $cssRaw = SLDefinitions::where('DefDatabase', $dbID)
                ->where('DefSet', 'Style Settings')
                ->orderBy('DefOrder')
                ->get();
        }
        if ($cssRaw && sizeof($cssRaw) > 0) {
            foreach ($cssRaw as $i => $c) $css[$c->DefSubset] = $c->DefDescription;
        }
        return $this->checkStyleDefs($css);
    }
    
    public function prepSysSettings(Request $request)
    {
        $this->v["settingsList"] = $this->getDefaultSys();
        $this->v["stylesList"] = $this->getDefaultStyles();
        if (!session()->has('chkSysVars') || $GLOBALS["SL"]->REQ->has('refresh')) {
            $this->checkSysDefs();
            $this->checkStyleDefs();
            session()->put('chkSysVars', 1);
        }
        $this->v["sysStyles"] = SLDefinitions::where('DefDatabase', 1)
            ->where('DefSet', 'Style Settings')
            ->orderBy('DefOrder')    
            ->get();
        $this->v["custCSS"] = SLDefinitions::where('DefDatabase', 1)
                ->where('DefSet', 'Style CSS')
                ->where('DefSubset', 'main')
                ->first();
        if (!$this->v["custCSS"] || !isset($this->v["custCSS"]->DefID)) {
            $this->v["custCSS"] = new SLDefinitions;
            $this->v["custCSS"]->DefDatabase = 1;
            $this->v["custCSS"]->DefSet      = 'Style CSS';
            $this->v["custCSS"]->DefSubset   = 'main';
            $this->v["custCSS"]->save();
        }
        $this->v["custCSSemail"] = SLDefinitions::where('DefDatabase', 1)
                ->where('DefSet', 'Style CSS')
                ->where('DefSubset', 'email')
                ->first();
        if (!$this->v["custCSSemail"] || !isset($this->v["custCSSemail"]->DefID)) {
            $this->v["custCSSemail"] = new SLDefinitions;
            $this->v["custCSSemail"]->DefDatabase = 1;
            $this->v["custCSSemail"]->DefSet      = 'Style CSS';
            $this->v["custCSSemail"]->DefSubset   = 'email';
            $this->v["custCSSemail"]->save();
        }
        $this->v["rawSettings"] = SLDefinitions::where('DefSet', 'Custom Settings')
            ->orderBy('DefOrder', 'asc')
            ->get();
        if ($request->has('sub')) {
            foreach ($GLOBALS["SL"]->sysOpts as $opt => $val) {
                if (isset($this->v["settingsList"][$opt])) {
                    $new = '';
                    if ($request->has('sys-' . $opt)) $new = $request->get('sys-' . $opt);
                    if ($opt == 'meta-title' && $request->has('pageTitle')) $new = $request->get('pageTitle');
                    elseif ($opt == 'meta-desc' && $request->has('pageDesc')) $new = $request->get('pageDesc');
                    elseif ($opt == 'meta-keywords' && $request->has('pageKey')) $new = $request->get('pageKey');
                    elseif ($opt == 'meta-img' && $request->has('pageImg')) $new = $request->get('pageImg');
                    if ($new != '') {
                        $GLOBALS["SL"]->sysOpts[$opt] = $new;
                        SLDefinitions::where('DefDatabase', 1)
                            ->where('DefSet', 'System Settings')
                            ->where('DefSubset', $opt)
                            ->update(['DefDescription' => $new]);
                    }
                }
            }
            foreach ($this->v["sysStyles"] as $opt) {
                if (isset($this->v["stylesList"][$opt->DefSubset]) && $request->has('sty-' . $opt->DefSubset)) {
                    $opt->DefDescription = $request->get('sty-' . $opt->DefSubset);
                    $opt->save();
                }
            }
            $this->v["custCSS"]->DefDescription = trim($request->get('sys-cust-css'));
            $this->v["custCSS"]->save();
            $this->v["custCSSemail"]->DefDescription = trim($request->get('sys-cust-css-email'));
            $this->v["custCSSemail"]->save();
            $this->getCSS($request);
            foreach ($this->v["rawSettings"] as $i => $s) {
                if ($request->has('setting' . $i . '')) {
                    $s->DefValue = $request->get('setting' . $i . '');
                    $s->save();
                }
            }
        }
        $tmp = [];
        if ($this->v["sysStyles"] && sizeof($this->v["sysStyles"]) > 0) {
            foreach ($this->v["sysStyles"] as $sty) {
                $tmp[$sty->DefSubset] = $sty->DefDescription;
            }
        }
        $this->v["sysStyles"] = $tmp;
        return true;
    }
    
    public function sysSettings(Request $request)
    {
        $this->admControlInit($request, '/dashboard/settings#search');
        $GLOBALS["SL"]->addAdmMenuHshoos([
            '/dashboard/settings#search',
            '/dashboard/settings#general', 
            '/dashboard/settings#color',
            '/dashboard/settings#hardcode'
            ]);
        $this->reloadAdmMenu();
        $this->prepSysSettings($request);
        $this->v["currMeta"] = [
            "title" => $GLOBALS['SL']->sysOpts['meta-title'],
            "desc"  => $GLOBALS['SL']->sysOpts['meta-desc'], 
            "wrds"  => $GLOBALS['SL']->sysOpts['meta-keywords'], 
            "img"   => $GLOBALS['SL']->sysOpts['meta-img'], 
            "slug"  => false, 
            "base"  => ''
            ];
        return view('vendor.survloop.admin.system-all-settings', $this->v);
    }
    
    public function sysSettingsRaw(Request $request)
    {
        $this->admControlInit($request, '/dashboard/settings-raw');
        $this->prepSysSettings($request);
        return view('vendor.survloop.admin.systemsettings', $this->v);
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
        if ($this->v["blurbRow"]->DefIsActive <= 0 || $this->v["blurbRow"]->DefIsActive%3 != 0) {
            $GLOBALS["SL"]->pageJAVA .= 'tinymce.init({ selector:"#DefDescriptionID" });';
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
        $css = $this->loadCss();
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
        $minifier->add("../vendor/wikiworldorder/survloop/src/Public/bootstrap/css/bootstrap.min.css");
        $minifier->add("../vendor/wikiworldorder/survloop/src/Public/bootstrap/css/bootstrap-theme.min.css");
        $minifier->add("../vendor/wikiworldorder/survloop/src/Public/jquery-ui-1.12.1/jquery-ui.min.css");
        if (isset($GLOBALS["SL"]->sysOpts["css-extra-files"]) 
            && trim($GLOBALS["SL"]->sysOpts["css-extra-files"]) != '') {
            $files = $GLOBALS["SL"]->mexplode(',', $GLOBALS["SL"]->sysOpts["css-extra-files"]);
            foreach ($files as $f) $minifier->add(trim($f));
        }
        $minifier->add("../storage/app/sys/sys2.min.css");
        //$minifier->add("../vendor/wikiworldorder/survloop/src/Public/font-awesome-4.7.0/css/font-awesome.min.css");
        $minifier->minify("../storage/app/sys/sys-all.min.css");
        
        $scriptsjs = view('vendor.survloop.scripts-js', [
            "jsXtra"         => $this->scriptsJsXtra(),
            "jqueryXtra"     => $this->scriptsJqueryXtra(),
            "jqueryXtraSrch" => $this->scriptsJqueryXtraSearch(), 
            //"spinner"        => $this->loadCustView('inc-spinner'), 
            "css"            => $css
        ])->render();
        file_put_contents("../storage/app/sys/sys.js", $scriptsjs);
        $minifier = new Minify\JS("../storage/app/sys/sys.js");
        $minifier->minify("../storage/app/sys/sys.min.js");
        
        $minifier = new Minify\JS("../vendor/wikiworldorder/survloop/src/Public/jquery-3.2.1.min.js");
        $minifier->add("../vendor/wikiworldorder/survloop/src/Public/jquery-ui-1.12.1/jquery-ui.min.js");
        $minifier->add("../vendor/wikiworldorder/survloop/src/Public/bootstrap/js/bootstrap.min.js");
        $minifier->add("../vendor/wikiworldorder/survloop/src/Public/scripts-lib.js");
        $minifier->add("../storage/app/sys/sys.min.js");
        $minifier->minify("../storage/app/sys/sys-all.min.js");
        
        $log = SLDefinitions::where('DefSet', 'System Settings')
            ->where('DefSubset', 'log-css-reload')
            ->update([ 'DefDescription' => time() ]);
        return ':)';
    }
    
    protected function checkStyleDefs($css = [])
    {
        $defaults = $this->getDefaultStyles();
        foreach ($defaults as $key => $val) {
            if (!isset($css[$key])) $css[$key] = $val[0];
            $dbID = $this->dbID;
            //if ($this->dbID == 3 && $GLOBALS["SL"]->sysOpts["cust-abbr"] == 'SurvLoop') $dbID = 1;
            $chk = SLDefinitions::where('DefDatabase', $dbID)
                ->where('DefSet', 'Style Settings')
                ->where('DefSubset', $key)
                ->first();
            if (!$chk || !isset($chk->DefSet)) {
                $cssNew = new SLDefinitions;
                $cssNew->DefDatabase = $dbID;
                $cssNew->DefSet = 'Style Settings';
                $cssNew->DefSubset = $key;
                $cssNew->DefDescription = $val[0];
                $cssNew->save();
            }
        }
        return $css;
    }
    
    protected function checkSysDefs($sys = [])
    {
        $defaults = $this->getDefaultSys();
        foreach ($defaults as $key => $val) {
            if (!isset($css[$key])) $sys[$key] = $val[1];
            $chk = SLDefinitions::where('DefDatabase', $this->dbID)
                ->where('DefSet', 'System Settings')
                ->where('DefSubset', $key)
                ->first();
            if (!$chk || !isset($chk->DefSet)) {
                $cssNew = new SLDefinitions;
                $cssNew->DefDatabase = $this->dbID;
                $cssNew->DefSet = 'System Settings';
                $cssNew->DefSubset = $key;
                $cssNew->DefDescription = $val[1];
                $cssNew->save();
            }
        }
        return $sys;
    }
    
    protected function getDefaultStyles()
    {
        return [
            'font-main'         => ['Helvetica,Arial,sans-serif', 'Universal Font Family'],
            'color-main-bg'     => ['#FFF',    'Background Color'],
            'color-main-text'   => ['#333',    'Text Color'],
            'color-main-link'   => ['#416CBD', 'Link Color'],
            'color-main-grey'   => ['#999',    'Grey Color'],
            'color-main-faint'  => ['#EDF8FF', 'Faint Color'],
            'color-main-on'     => ['#2B3493', 'Primary Color #1'],
            'color-main-off'    => ['#53F1EB', 'Primary Color #2'],
            'color-info-on'     => ['#5BC0DE', 'Info Color #1'],
            'color-info-off'    => ['#2AABD2', 'Info Color #2'],
            'color-danger-on'   => ['#EC2327', 'Danger Color #1'],
            'color-danger-off'  => ['#F38C5F', 'Danger Color #2'],
            'color-success-on'  => ['#006D36', 'Success Color #1'],
            'color-success-off' => ['#29B76F', 'Success Color #2'],
            'color-warn-on'     => ['#F0AD4E', 'Warning Color #1'],
            'color-warn-off'    => ['#EB9316', 'Warning Color #2'],
            'color-line-hr'     => ['#999',    'Horizontal Rule Color'],
            'color-field-bg'    => ['#FFF',    'Form Field BG Color'],
            'color-form-text'   => ['#333',    'Form Field Text Color'],
            'color-logo'        => ['#53F1EB', 'Primary Logo Color'],
            'color-nav-bg'      => ['#000',    'Navigation BG Color'],
            'color-nav-text'    => ['#888',    'Navigation Text Color']
            ];
    }
    
    protected function getDefaultSys()
    {
        return [
            'site-name'       => ['Installation/Site Name', 'for general reference, in English'], 
            'cust-abbr'       => ['Installation Abbreviation', 'SiteAbrv'], 
            'cust-package'    => ['Vendor Package Name', 'wikiworldorder/survloop'], 
                // for files and folder names, no spaces or special characters
            'app-url'         => ['Primary Application URL', 'http://myapp.com'], 
            'logo-url'        => ['URL Linked To Logo', '/optionally-different'], 
            'meta-title'      => ['SEO Default Meta Title', ''], 
            'meta-desc'       => ['SEO Default Meta Description', ''], 
            'meta-keywords'   => ['SEO Default Meta Keywords', ''], 
            'meta-img'        => ['SEO Default Meta Social Media Sharing Image', ''], 
            'logo-img-lrg'    => ['Large Logo Image', '/siteabrv/uploads/logo-large.png'], 
            'logo-img-md'     => ['Medium Logo Image', '/siteabrv/uploads/logo-medium.png'], 
            'logo-img-sm'     => ['Small Logo Image', '/siteabrv/uploads/logo-small.png'], 
            'shortcut-icon'   => ['Shortcut Icon Image', '/siteabrv/ico.png'],
            'spinner-code'    => ['Spinner Animation', '&lt;i class="fa-li fa fa-spinner fa-spin"&gt;&lt;/i&gt;'], 
            'google-analytic' => ['Google Analytics Tracking ID', 'UA-23427655-1'], 
            'twitter'         => ['Twitter Account', '@SurvLoop'], 
            'show-logo-title' => ['Print Site Name Next To Logo', '1 or 0'], 
            'users-create-db' => ['Users Can Create Databases', '1 or 0'], 
            'has-volunteers'  => ['Has Volunteer Users w/ Special Access', '1 or 0'], 
            'parent-company'  => ['Parent Company of This Installation', 'MegaOrg'], 
            'parent-website'  => ['Parent Company\'s Website URL', 'http://www...'], 
            'login-instruct'  => ['User Login Instructions', 'HTML'], 
            'signup-instruct' => ['New User Sign Up Instructions', 'HTML'], 
            'app-license'     => ['License Info', 'Creative Commons Attribution-ShareAlike License'], 
            'app-license-url' => ['License Info URL', 'http://creativecommons.org/licenses/by-sa/3.0/'], 
            'app-license-img' => ['License Info Image', '/survloop/uploads/creative-commons-by-sa-88x31.png'],
            'css-extra-files' => ['CSS Extra Files', 'comma separated'],
            'header-code'     => ['< head > Header Code < / head >', '&lt;div&gt;Anything&lt;/div&gt;']
            ];
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
        return ($chkCore && sizeof($chkCore) > 0);
    }
    
    protected function exportMysqlTblCoreStart($tbl)
    {
        return "CREATE TABLE IF NOT EXISTS `" 
            . $GLOBALS["SL"]->dbRow->DbPrefix . $tbl->TblName . "` ( "
            . "  `" . $tbl->TblAbbr . "ID` int(11) NOT NULL AUTO_INCREMENT, \n";
    }
    
    protected function exportMysqlTblCoreFinish($tbl)
    {
        return "  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP , \n"
            . "  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP , \n"
            . "  PRIMARY KEY (`" . $tbl->TblAbbr . "ID`) )"
            . "  ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    }
    
    protected function chkModelsFolder()
    {
        if (!file_exists('../app/Models')) mkdir('../app/Models');
        if (!file_exists('../app/Models/' . $GLOBALS["SL"]->sysOpts["cust-abbr"])) {
            mkdir('../app/Models/' . $GLOBALS["SL"]->sysOpts["cust-abbr"]);
        }
        return true;
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
        if ($users && sizeof($users) > 0) {
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
            elseif ($usr->hasRole('thirdparty')) $list = 3;
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
        $GLOBALS["SL"]->pageJAVA .= 'tinymce.init({ selector:"#emailBodyID" });';
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
            $currEmail->EmailOpts         = 1;
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
            var postUrl = "/ajadm/contact?tab={{ $filtStatus }}&cid="+cID+"&status="+$(this).val();
            $( "#wrapItem"+cID+"" ).load( postUrl );
        });';
        return view('vendor.survloop.admin.contact', $this->v);
    }
    
    public function postNodeURL(Request $request)
    {
        if ($request->has('step') && $request->has('tree') && intVal($request->tree) > 0) {
            $this->loadTreeByID($request, $request->tree);
            $this->admControlInit($request, '/dash/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/' . $request->nodeSlug);
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
        }
        return $this->CustReport->ajaxChecks($request, $type);
    }
    
    
    
    public function systemsCheck(Request $request)
    {
        $this->admControlInit($request, '/dashboard/systems-check');
        if ($request->has('testEmail') && intVal($request->get('testEmail')) == 1) {
            $this->v["testResults"] = '';
            if ($request->has('sendTest') && intVal($request->get('sendTest')) == 1) {
                
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
    
    public function getCoreDef($set, $subset, $dbID = 1)
    {
        $def = SLDefinitions::where('DefDatabase', $dbID)
            ->where('DefSet',    '=', $set)
            ->where('DefSubset', '=', $subset)
            ->first();
        if (!$def || !isset($def->DefID)) {
            $def = new SLDefinitions;
            $def->DefDatabase = $dbID;
            $def->DefSet      = $set;
            $def->DefSubset   = $subset;
            $def->save();
        }
        return $def;
    }
    
}
