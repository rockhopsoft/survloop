<?php
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\Models\User;
use App\Models\SLDatabases;
use App\Models\SLDefinitions;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLContact;
use App\Models\SLEmails;

use SurvLoop\Controllers\SurvLoopReport;
use SurvLoop\Controllers\SurvLoopController;

class AdminController extends SurvLoopController
{
    public $classExtension = 'AdminController';
    
    protected $CustReport  = array();
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
            if (!$this->v["user"] || intVal($this->v["user"]->id) <= 0
                || !$this->v["user"]->hasRole('administrator|staff|databaser|brancher|volunteer')) {
                echo view('vendor.survloop.inc-js-redirect-home', $this->v)->render();
                exit;
            }
            if ($GLOBALS["SL"]->sysOpts["cust-abbr"] == 'survloop') $GLOBALS["SL"]->sysOpts["cust-abbr"] = 'SurvLoop';
            $this->loadCustReport($request);
            $this->checkCurrPage();
            $this->v["admMenu"] = $this->getAdmMenu($this->v["currPage"]);
            $this->v["belowAdmMenu"] = $this->loadBelowAdmMenu();
            $this->loadSearchSuggestions();
            $this->initExtra($request);
            $this->initCustViews();
            $this->logPageVisit();
            $this->clearEmpties();
        }
        return true;
    }
    
    protected function loadCustReport($request)
    {
        if (isset($GLOBALS["SL"]->sysOpts["cust-abbr"]) && $GLOBALS["SL"]->sysOpts["cust-abbr"] != 'SurvLoop') {
            eval("\$this->CustReport = new ". $GLOBALS["SL"]->sysOpts["cust-abbr"] . "\\Controllers\\" 
                . $GLOBALS["SL"]->sysOpts["cust-abbr"] . "Report(\$request, -3, " 
                . $this->dbID . ", " . $this->treeID . ");");
        } else {
            $this->CustReport = new SurvLoopReport($request, -3, $this->dbID, $this->treeID);
        }
        $this->CustReport->survLoopInit($request, $this->v["currPage"]);   
    }
    
    protected function checkCurrPage()
    {
        /* if (sizeof($this->CustReport) > 0) {
            $custPage = $this->CustReport->getCurrPage();
            if (trim($custPage) != '/') $this->v["currPage"] = $custPage;
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
        $trees = SLTree::where('TreeDatabase', $this->dbID)
            ->where('TreeType', 'Primary Public')
            ->orderBy('TreeID', 'asc')
            ->get();
        if ($trees && sizeof($trees) > 0) {
            $ret .= '<i class="fPerc133">Form Trees:</i><br />';
            foreach ($trees as $tree) {
                if ($this->treeID == $tree->TreeID) {
                    $ret .= '<a href="/dashboard/tree-' . $tree->TreeID . '/map?all=1" class="fPerc133"><b>' 
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
                $curr = '<a href="/dashboard/page/' . $tree->TreeID . '?all=1&refresh=1">' 
                    . $tree->TreeName . '</a><br />';
                if ($this->treeID == $tree->TreeID) {
                    $curr = '<a href="/dashboard/page/' . $tree->TreeID . '?all=1&refresh=1" class="fPerc133"><b>' 
                        . $tree->TreeName . '</b> <i class="fa fa-pencil" aria-hidden="true"></i></a><br />';
                }
                if ($tree->TreeOpts%3 == 0) $admPages .= $curr;
                else $ret .= $curr;
            }
        }
        if (trim($admPages) != '') {
            $ret .= '<br /><i class="fPerc133">Admin Pages:</i><br />' . $admPages;
        }
        return $ret . '<br />';
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
    
    protected function loadAdmMenu()
    {
        $treeMenu = $this->loadAdmMenuBasics();
        return [
            [
                'javascript:;',
                '<i class="fa fa-star mR5"></i> Submissions',
                1,
                [
                    $this->admMenuLnk('/dashboard/subs/all',        'All Complete'), 
                    $this->admMenuLnk('/dashboard/subs/incomplete', 'Incomplete Sessions'), 
                    $this->admMenuLnk('/dashboard/emails',          'Manage Email Templates')
                ]
            ], 
            $treeMenu
        ];
    }
    
    protected function loadAdmMenuBasics()
    {
        list($treeID, $treeLabel, $dbName) = $this->loadDbTreeShortNames();
        $treeMenu = [
            'javascript:;',
            '<i class="fa fa-snowflake-o mR5"></i> Systems',
            1,
            [
                $this->admMenuLnk('/dashboard/pages/list',    '<i class="fa fa-newspaper-o mR5"></i> Pages & Content'), 
                $this->admMenuLnk('javascript:;', '<i class="fa fa-snowflake-o mR5"></i> <b>' . $treeLabel . '</b>', [
                    $this->admMenuLnk('/dashboard/tree-' . $treeID . '/map?all=1', 'Form Tree Map'), 
                    $this->admMenuLnk('/dashboard/tree-' . $treeID . '/sessions',  'Tree Session Stats'), 
                    $this->admMenuLnk('/dashboard/tree-' . $treeID . '/stats?all=1',     'Node Response Stats')
                ]), 
                $this->admMenuLnk('javascript:;', '<i class="fa fa-database mR5"></i> <b>Database: ' . $dbName . '</b>', [
                    $this->admMenuLnk('/dashboard/db',             'Database Overview'), 
                    $this->admMenuLnk('/dashboard/db/all',         'Database Design'), 
                    $this->admMenuLnk('/dashboard/db/definitions', 'Definitions'),
                    $this->admMenuLnk('/dashboard/db/conds',       'Conditions / Filters'), 
                    $this->admMenuLnk('/dashboard/db/workflows',   'Workflows')
                ]), 
                $this->admMenuLnk('/dashboard/users',         '<i class="fa fa-users mR5"></i> Users'), 
                $this->admMenuLnkContact(), 
                $this->admMenuLnk('/dashboard/systems-check', '<i class="fa fa-heartbeat mR5"></i> Check'), 
                $this->admMenuLnk('/dashboard/settings',      '<i class="fa fa-cogs mR5"></i> Settings')
            ]
        ];
        return $treeMenu;
    }
    
    protected function admMenuLnk($url = '', $text = '', $children = [], $opt = 1)
    {
        return [ $url, $text, $opt, $children ];
    }
    
    protected function admMenuLnkContact($icon = true)
    {
        $cnt = $this->admMenuLnkContactCnt();
        $lnk = (($icon) ? '<i class="fa fa-envelope-o mR5" aria-hidden="true"></i> ' : '')
            . (($cnt > 0) ? '<span id="contactPush" class="label label-danger mR5">' . $cnt . '</span> ' : '') 
            . ' Contact Form';
        return [ '/dashboard/contact', $lnk, 1, [] ];
    }
    
    protected function admMenuLnkContactCnt()
    {
        $chk = SLContact::where('ContFlag', 'Unread')
            ->select('ContID')
            ->get();
        return sizeof($chk);
    }
    
    protected function getAdmMenuLoc($currPage)
    {
        $this->admMenuData["currNavPos"] = array(0, -1, -1, -1);
        if (sizeof($this->admMenuData["adminNav"]) > 0) {  
            foreach ($this->admMenuData["adminNav"] as $i => $nav) {
                if ($nav[0] == $currPage) {
                    $this->admMenuData["currNavPos"] = array($i, -1, -1, -1);
                } elseif (sizeof($nav[3]) > 0) {
                    foreach ($nav[3] as $j => $nA) {
                        if ($nA[0] == $currPage) {
                            $this->admMenuData["currNavPos"] = array($i, $j, -1, -1);
                        } elseif (sizeof($nA[3]) > 0) {
                            foreach ($nA[3] as $k => $nB) {
                                if ($nB[0] == $currPage) {
                                    $this->admMenuData["currNavPos"] = array($i, $j, $k, -1);
                                } elseif (sizeof($nB[3]) > 0) {
                                    foreach ($nB[3] as $l => $nC) {
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
        return ($this->admMenuData["currNavPos"][0] != 0 
            || $this->admMenuData["currNavPos"][1] != 0
            || $this->admMenuData["currNavPos"][2] != 0 
            || $this->admMenuData["currNavPos"][3] != 0);
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
        if (!$this->v["user"]->hasRole('administrator|staff|databaser|brancher')) {
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
        $this->v["currInComplaint"] = $this->CustReport->currInComplaint();
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
            $this->v["content"] = '<div class="pT20">' . $this->CustReport->index($request) . '</div>';
            $this->v["currInComplaint"] = $this->CustReport->currInComplaint();
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
        $this->dbID = $dbID;
        $this->treeID = $treeID;
        if (!isset($this->v["isAdmin"])) $this->v["isAdmin"] = $this->isUserAdmin();
        $GLOBALS["SL"] = new DatabaseLookups($request, $this->v["isAdmin"], $dbID, $treeID, $treeID);
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
                return $this->redir('/dashboard/page/' . $treeID . '?all=1&refresh=1');
            }
            return $this->redir('/dashboard/tree-' . $treeID . '/map?all=1');
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
    
    public function sysSettings(Request $request)
    {
        $this->admControlInit($request, '/dashboard/settings');
        $this->v["settingsList"] = [
            'site-name'       => ['Installation/Site Name', 'for general reference, in English'], 
            'cust-abbr'       => ['Installation Abbreviation', 'eg. "SiteAbrv", for files and folder names, '
                                    . 'no spaces or special characters'], 
            'app-url'         => ['Primary Application URL', 'eg. "http://myapp.com"'], 
            'logo-url'        => ['URL Linked To Logo', 'optionally different than app url'], 
            'meta-title'      => ['SEO Default Meta Title', ''], 
            'meta-desc'       => ['SEO Default Meta Description', ''], 
            'meta-keywords'   => ['SEO Default Meta Keywords', ''], 
            'meta-img'        => ['SEO Default Meta Social Media Sharing Image', ''], 
            'logo-img-lrg'    => ['Image Large Logo', 'eg. "/siteabrv/logo-large.png"'], 
            'logo-img-md'     => ['Image Medium Logo', 'eg. "/siteabrv/logo-medium.png"'], 
            'logo-img-sm'     => ['Image Small Logo', 'eg. "/siteabrv/logo-small.png"'], 
            'shortcut-icon'   => ['Image Shortcut Icon', 'eg. "/siteabrv/ico.png"'], 
            'google-analytic' => ['Google Analytics Tracking ID', 'eg. "UA-23427655-1"'], 
            'show-logo-title' => ['Print Site Name Next To Logo', '"On" or "Off"'], 
            //'users-create-db' => ['Users Can Create Databases', '"On" or "Off"'], 
            'parent-company'  => ['Parent Company of This Installation', 'www'], 
            'parent-website'  => ['Parent Company\'s Website URL', 'www'], 
            'login-instruct'  => ['User Login Instructions', 'HTML'], 
            'signup-instruct' => ['New User Sign Up Instructions', 'HTML'], 
            'app-license'     => ['License Info', 'eg. "Creative Commons Attribution-ShareAlike License"'], 
            'app-license-url' => ['License Info URL', 'eg. "http://creativecommons.org/licenses/by-sa/3.0/"'], 
            'app-license-img' => ['License Info Image', 'eg. "/survloop/creative-commons-by-sa-88x31.png"'],
            'header-code'     => ['< head > Header Code < / head >', 'HTML']
        ];
        $this->v["stylesList"] = [
            'font-main'         => ['Universal Font Family', 'eg. "Helvetica,Arial,sans-serif"'], 
            'color-main-text'   => ['Text Color', '<span style="color: #333;">eg. "#333"</span>'], 
            'color-main-link'   => ['Link Color', '<span style="color: #2b3493;">eg. "#2b3493"</span>'], 
            'color-main-grey'   => ['Grey Color (less prominant than above Text Color)', 
                                    '<span style="color: #999;">eg. "#999"</span>'], 
            'color-main-bg'     => ['Background Color', 
                                    '<span style="color: #000;">eg. "#000"</span>'], 
            'color-nav-bg'      => ['Navigation Background Color', 
                                    '<span style="color: #000;">eg. "#000"</span>'], 
            'color-nav-text'    => ['Navigation Text Color', 
                                    '<span style="color: #FFF;">eg. "#FFF"</span>'], 
            'color-main-on'     => ['Primary Color: On', 
                                    '<span style="color: #2b3493;">eg. "#2b3493"</span>'], 
            'color-main-off'    => ['Primary Color: Off', 
                                    '<span style="color: #53f1eb;">eg. "#53f1eb"</span>'], 
            'color-main-faint'  => ['Primary Color: Faint', 
                                    '<span style="color: #edf8ff;">eg. "#edf8ff"</span>'], 
            'color-success-on'  => ['Success Color: On', // or ? #29B76F
                                    '<span style="color: #006D36;">eg. "#006D36"</span>'], 
            'color-success-off' => ['Success Color: Off', 
                                    '<span style="color: #29B76F;">eg. "#29B76F"</span>'], 
            'color-info-on'     => ['Info Color: On', 
                                    '<span style="color: #5bc0de;">eg. "#5bc0de"</span>'], 
            'color-info-off'    => ['Info Color: Off', 
                                    '<span style="color: #2aabd2;">eg. "#2aabd2"</span>'], 
            'color-warn-on'     => ['Warning Color: On', 
                                    '<span style="color: #f0ad4e;">eg. "#f0ad4e"</span>'], 
            'color-warn-off'    => ['Warning Color: Off', 
                                    '<span style="color: #eb9316;">eg. "#eb9316"</span>'], 
            'color-danger-on'   => ['Danger Color: On', 
                                    '<span style="color: #ec2327;">eg. "#ec2327"</span>'], 
            'color-danger-off'  => ['Danger Color: Off', 
                                    '<span style="color: #f38c5f;">eg. "#f38c5f"</span>'], 
            'color-line-hr'     => ['Horizontal Rule Color', 
                                    '<span style="color: #999;">eg. "#999"</span>'], 
            'color-field-bg'    => ['Form Field Background Color', 
                                    '<span style="color: #FFF;">eg. "#FFF"</span>'], 
            'color-logo'        => ['Primary Logo Color', 
                                    '<span style="color: #53f1eb;">eg. "#53f1eb"</span>']
        ];
        $this->v["sysStyles"] = SLDefinitions::where('DefDatabase', 1)
            ->where('DefSet', 'Style Settings')
            ->orderBy('DefOrder')    
            ->get();
        $this->v["custCSS"] = SLDefinitions::where('DefDatabase', 1)
                ->where('DefSet', 'Style CSS')
                ->where('DefSubset', 'main')
                ->first();
        $this->v["custCSSemail"] = SLDefinitions::where('DefDatabase', 1)
                ->where('DefSet', 'Style CSS')
                ->where('DefSubset', 'email')
                ->first();
        $this->v["rawSettings"] = SLDefinitions::where('DefSet', 'Custom Settings')
            ->orderBy('DefOrder', 'asc')
            ->get();
        if ($request->has('sub')) {
            foreach ($GLOBALS["SL"]->sysOpts as $opt => $val) {
                if (isset($this->v["settingsList"][$opt]) && $request->has('sys-' . $opt)) {
                    $GLOBALS["SL"]->sysOpts[$opt] = $request->get('sys-' . $opt);
                    SLDefinitions::where('DefDatabase', 1)
                        ->where('DefSet', 'System Settings')
                        ->where('DefSubset', $opt)
                        ->update(['DefDescription' => $GLOBALS["SL"]->sysOpts[$opt]]);
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
                if ($request->has('setting'.$i.'')) {
                    $s->DefValue = $request->get('setting'.$i.'');
                    $s->save();
                }
            }
        }
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
            $GLOBALS["SL"]->pageJAVA .= 'CKEDITOR.replace( "DefDescriptionID", {
                customConfig: "/survloop/ckeditor-config.js" } );';
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
        return $this->redir('/dashboard/blurbs/' . $blurb->DefID);
    }
    
    
    public function getCSS(Request $request)
    {
        $this->survLoopInit($request, '/dashboard/settings');
        $css = [];
        $cssRaw = SLDefinitions::where('DefDatabase', $this->dbID)
            ->where('DefSet', 'Style Settings')
            ->orderBy('DefOrder')
            ->get();
        if ($cssRaw && sizeof($cssRaw) > 0) {
            foreach ($cssRaw as $i => $c) $css[$c->DefSubset] = $c->DefDescription;
        }
        $custCSS = SLDefinitions::where('DefDatabase', 1)
            ->where('DefSet', 'Style CSS')
            ->where('DefSubset', 'main')
            ->first();
        $css["raw"] = (($custCSS && isset($custCSS->DefDescription)) ? $custCSS->DefDescription : '');
        $syscss = view('vendor.survloop.styles-css', [ "css" => $css ])->render();
        file_put_contents("../storage/app/sys.css", $syscss);
        
        file_put_contents("../storage/app/sys.min.css", $syscss);
        
        
        $scriptsjs = view('vendor.survloop.scripts-js', [
            "jsXtra"         => $this->scriptsJsXtra(),
            "jqueryXtra"     => $this->scriptsJqueryXtra(),
            "jqueryXtraSrch" => $this->scriptsJqueryXtraSearch(), 
            "spinner"        => $this->loadCustView('inc-spinner')
        ])->render();
        file_put_contents("../storage/app/sys.js", $scriptsjs);
        
        file_put_contents("../storage/app/sys.min.js", $scriptsjs);
        
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
            if ($usr->hasRole('administrator')) $list = 0;
            elseif ($usr->hasRole('databaser')) $list = 1;
            elseif ($usr->hasRole('brancher'))  $list = 2;
            elseif ($usr->hasRole('staff'))     $list = 3;
            elseif ($usr->hasRole('volunteer')) $list = 4;
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
        $GLOBALS["SL"]->pageJAVA .= 'CKEDITOR.replace( "emailBodyID", {
            customConfig: "/survloop/ckeditor-config.js" } );';
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
            $currEmail->EmailCustomSpots = 0;
            if (trim($currEmail->EmailBody) != '' 
                && strpos($currEmail->EmailBody, '[{ Custom Writing Spot }]') !== false) {
                $customSpotSplit = explode('[{ Custom Writing Spot }]', $currEmail->EmailBody);
                $currEmail->EmailCustomSpots = sizeof($customSpotSplit)-1;
            }
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
        $this->v["currPageTitle"] = 'Contact Form Messages';
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
        } elseif ($type == 'contact-tabs') {
            $this->getRecFiltTots('SLContact', 'ContFlag', ['Unread', 'Read', 'Trash'], 'ContID');
            return view('vendor.survloop.admin.contact-tabs', [
                "filtStatus" => (($request->has('tab')) ? $request->get('tab') : 'unread'),
                "recTots"    => $this->v["recTots"]
            ])->render();
        } elseif ($type == 'contact-push') {
            return $this->admMenuLnkContactCnt();
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
    
    
}
