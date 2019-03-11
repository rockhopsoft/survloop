<?php
/**
  * AdminController is the main landing class routing to certain admin tools which 
  * requires a user to be logged in.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use Auth;
use Storage;
use Illuminate\Http\Request;
use MatthiasMullie\Minify;
use App\Models\User;
use App\Models\SLDatabases;
use App\Models\SLDefinitions;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLNodeResponses;
use App\Models\SLContact;
use App\Models\SLEmails;
use SurvLoop\Controllers\AdminMenu;
use SurvLoop\Controllers\PageLoadUtils;
use SurvLoop\Controllers\SystemDefinitions;
use SurvLoop\Controllers\SurvLoopController;

class AdminController extends SurvLoopController
{
    protected $loader      = null;
    protected $adminNav    = [];
    protected $admMenuData = [];
    protected $pageIsAdmin = true;
    protected $admInitRun  = false;
    protected $domainPath  = '';
    
    protected function initLoader()
    {
        $this->loader = new PageLoadUtils(true);
        return true;
    }
    
    protected function admControlInit(Request $request, $currPage = '', $perms = '')
    {
        if (!$this->admInitRun) {
            $this->admInitRun = true;
            $this->doublecheckSurvTables();
            $this->loadDbLookups($request);
            $this->survLoopInit($request, $currPage, false);
            if (trim($perms) == '') {
                $perms = 'administrator|staff|databaser|brancher|partner|volunteer';
            }
            if ($this->v["uID"] <= 0 || !$this->v["user"]->hasRole($perms)) {
                echo view('vendor.survloop.inc-js-redirect-home', $this->v)->render();
                exit;
            }
            $this->initPowerUser();
            $this->v["isDash"] = true;
            if ($GLOBALS["SL"]->sysOpts["cust-abbr"] == 'survloop') {
                $GLOBALS["SL"]->sysOpts["cust-abbr"] = 'SurvLoop';
            }
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
    
    protected function getAdmMenu($currPage = '')
    {
        $GLOBALS["SL"]->sysOpts["footer-admin"] = view('vendor.survloop.inc-footer-admin')->render();
        $this->admMenuData = [
            "adminNav"   => [],
            "currNavPos" => []
            ];
        $admMenu = null;
        if (isset($GLOBALS["SL"]->sysOpts["cust-abbr"]) && $GLOBALS["SL"]->sysOpts["cust-abbr"] != 'SurvLoop') {
            $custClass = $GLOBALS["SL"]->sysOpts["cust-abbr"] . "\\Controllers\\" 
                . $GLOBALS["SL"]->sysOpts["cust-abbr"] . "AdminMenu";
            if (class_exists($custClass)) {
                eval("\$admMenu = new " . $custClass . ";");
            }
        }
        if (!$admMenu) {
            $admMenu = new AdminMenu;
        }
        if ($admMenu) {
            $this->admMenuData["adminNav"] = $admMenu->loadAdmMenu($this->v["user"], $currPage);
        }
        $this->tweakAdmMenu($currPage);
        if (!$this->getAdmMenuLoc($currPage) && $currPage != '') {
            $this->getAdmMenuLoc($currPage);
        }
        return view('vendor.survloop.admin.admin-menu', $this->admMenuData)->render();
    }
    
    protected function getAdmMenuTopTabs()
    {
        $tabs = view('vendor.survloop.admin.admin-menu-tabs', $this->admMenuData)->render();
        $subTabs = view('vendor.survloop.admin.admin-menu-tabs-sub', $this->admMenuData)->render();
        return ((trim($tabs) != '') ? '<div id="slTopTabsWrap" class="slTopTabs">' . $tabs . '</div>'
            . ((trim($subTabs) != '') ? '<div class="slTopTabsSub">' . $subTabs . '</div>' : '')
            : '<div class="w100" style="margin-bottom: 15px;"> </div>');
    }
    
    protected function reloadAdmMenu()
    {
        $this->v["admMenu"]      = $this->getAdmMenu($this->v["currPage"][0]);
        $this->v["belowAdmMenu"] = $this->loadBelowAdmMenu();
        $this->v["admMenuTabs"]  = $this->getAdmMenuTopTabs();
        return true;
    }
    
    protected function chkNewAdmMenuPage($currPage = '')
    {
        if (trim($currPage) != '' && $this->v["currPage"][0] != $currPage) {
            $this->v["currPage"][0] = $currPage;
            $this->reloadAdmMenu();
        } elseif (isset($GLOBALS["SL"]->x["currPage"]) && trim($GLOBALS["SL"]->x["currPage"]) != ''
            && $this->v["currPage"][0] != $currPage) {
            $this->v["currPage"][0] = $GLOBALS["SL"]->x["currPage"];
            $this->reloadAdmMenu();
        }
        return true;
    }
    
    protected function checkCurrPage()
    {
        /* if (sizeof($this->custReport) > 0) {
            $custPage = $this->custReport->getCurrPage();
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
    
    
    public function loadNodeURL(Request $request, $treeSlug = '', $nodeSlug = '')
    {
        $this->initLoader();
        if ($this->loader->loadTreeBySlug($request, $treeSlug)) {
            $this->dbID = $this->loader->dbID;
            $this->treeID = $this->loader->treeID;
            $this->admControlInit($request, '/dashboard/start/' . $treeSlug);
            $this->loadCustLoop($request, $this->treeID);
            $this->v["content"] = '<div class="pT20">' . $this->custReport->loadNodeURL($request, $nodeSlug) . '</div>';
            $this->chkNewAdmMenuPage();
            return view('vendor.survloop.master', $this->v);
        }
        $this->loader->loadDomain();
        return redirect($this->loader->domainPath . '/');
    }
    
    public function loadNodeTreeURL(Request $request, $treeSlug = '')
    {
        $this->initLoader();
        return $this->loader->loadNodeTreeURL($request, $treeSlug);
    }
    
    public function loadNodeTreeURLedit(Request $request, $cid = -3, $treeSlug = '')
    {
        $this->initLoader();
        return $this->loader->loadNodeTreeURLedit($request, $cid, $treeSlug);
    }
    
    public function loadPageURL(Request $request, $pageSlug = '')
    {
        $this->initLoader();
        if ($this->loader->loadTreeBySlug($request, $pageSlug, 'Page')) {
            $this->admControlInit($request, '/dash/' . $pageSlug);
            $this->loadCustLoop($request, $this->loader->treeID);
            $this->v["content"] = $this->custReport->index($request);
            if ($request->has('edit') && intVal($request->get('edit')) == 1 && $this->loader->isUserAdmin()) {
                echo '<script type="text/javascript"> window.location="/dashboard/page/' 
                    . $GLOBALS["SL"]->treeID . '?all=1&alt=1&refresh=1"; </script>';
                exit;
            }
            $this->chkNewAdmMenuPage();
            return $this->loader->addAdmCodeToPage(view('vendor.survloop.master', $this->v)->render());
        }
        $this->loader->loadDomain();
        return redirect($this->loader->domainPath . '/');
    }
    
    public function loadPageDashboard(Request $request)
    {
        $this->admControlInit($request, '/dashboard');
        $this->initLoader();
        $prime = $this->loader->getMaxPermsPrime();
        if ($prime <= 1) {
            return $this->redir('/login');
        }
        $tree = SLTree::where('TreeType', 'Page')
            ->whereRaw("TreeOpts%" . Globals::TREEOPT_HOMEPAGE . " = 0")
            ->whereRaw("TreeOpts%" . $prime . " = 0")
            ->orderBy('TreeID', 'asc')
            ->first();
        if ($tree && isset($tree->TreeID)) {
            $this->loader->syncDataTrees($request, $tree->TreeDatabase, $tree->TreeID);
            $this->loadCustLoop($request, $tree->TreeID);
            $this->v["content"] = $this->custReport->index($request);
            return $this->loader->addAdmCodeToPage($GLOBALS["SL"]->swapSessMsg(
                view('vendor.survloop.master', $this->v)->render()));
        }
        return $this->custReport->redir('/');
    }
    
    protected function tmpDbSwitch($dbID = 3)
    {
        $this->v["tmpDbSwitchDb"]   = $GLOBALS["SL"]->dbID;
        $this->v["tmpDbSwitchTree"] = $GLOBALS["SL"]->treeID;
        $this->v["tmpDbSwitchREQ"]  = $GLOBALS["SL"]->REQ;
        $GLOBALS["SL"] = new Globals($this->v["tmpDbSwitchREQ"], $dbID);
        $this->dbID   = $dbID;
        $this->treeID = $GLOBALS["SL"]->treeID;
        return true;
    }

    protected function tmpDbSwitchBack()
    {
        if (isset($this->v["tmpDbSwitchDb"])) {
            $GLOBALS["SL"] = new Globals($this->v["tmpDbSwitchREQ"], 
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
        if ($request->has('refresh') && intVal($request->get('refresh')) == 2) {
            $this->initLoader();
            $chk = SLTree::whereIn('TreeType', [ 'Page', 'Survey' ])
                ->where('TreeDatabase', 1)
                ->select('TreeID', 'TreeDatabase')
                ->orderBy('TreeID', 'asc')
                ->get();
            if ($chk->isNotEmpty()) {
                $next = $curr = $found = 0;
                if ($request->has('next')) {
                    $curr = intVal($request->get('next'));
                }
                foreach ($chk as $tree) {
                    if ($curr == 0) {
                        $curr = $tree->TreeID;
                    }
                    if ($found == 1 && $next <= 0) {
                        $next = $tree->TreeID;
                    }
                    if ($tree->TreeID == $curr) {
                        $found = 1;
                        $this->loader->syncDataTrees($request, $tree->TreeDatabase, $tree->TreeID);
                        $this->loadCustLoop($request, $tree->TreeID, $tree->TreeDatabase);
                        $this->custReport->loadTree($tree->TreeID);
                        $this->custReport->loadProgBar();
                    }
                }
                $this->loader->syncDataTrees($request);
                if ($next > 0) {
                    echo '<center><div class="p20"><br /><br /><h2>Refreshing Jasascript Cache for Tree #' . $curr 
                        . '</h2></div></center><div class="p20">' . $GLOBALS["SL"]->spinner() . '</div>';
                    return $this->redir('/dashboard/settings?refresh=2&next=' . $next, true);
                }
            }
            return $this->redir('/dashboard/settings?refresh=1', true);
        }
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
        $this->admControlInit($request, '/dashboard/pages');
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
        if ($request->has('optHardCode') && intVal($request->optHardCode) == 3) {
            $blurb->DefIsActive *= 3;
        }
        $blurb->save();
        return $this->redir('/dashboard/pages/snippets/' . $blurb->DefID);
    }
    
    public function getCSS(Request $request)
    {
        $this->survLoopInit($request, '/dashboard/settings');
        if (!is_dir('../storage/app/sys')) {
            mkdir('../storage/app/sys');
        }
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
        $minifier->add("../vendor/components/jqueryui/themes/base/jquery-ui.min.css");
        $minifier->add("../vendor/twbs/bootstrap/dist/css/bootstrap.min.css");
        //$minifier->add("../vendor/forkawesome/fork-awesome/css/fork-awesome.min.css");
        if (isset($GLOBALS["SL"]->sysOpts["css-extra-files"]) 
            && trim($GLOBALS["SL"]->sysOpts["css-extra-files"]) != '') {
            $files = $GLOBALS["SL"]->mexplode(',', $GLOBALS["SL"]->sysOpts["css-extra-files"]);
            foreach ($files as $f) {
                $minifier->add(trim($f));
            }
        }
        $minifier->minify("../storage/app/sys/sys1.min.css");
        
        $syscss = view('vendor.survloop.styles-css-2', [ "css" => $css ])->render();
        file_put_contents("../storage/app/sys/sys2.css", $syscss);
        $minifier = new Minify\CSS("../storage/app/sys/sys2.css");
        $minifier->minify("../storage/app/sys/sys2.min.css");
        
        $minifier = new Minify\JS("../vendor/components/jquery/jquery.min.js");
        $minifier->add("../vendor/components/jqueryui/jquery-ui.min.js");
        $minifier->add("../vendor/twbs/bootstrap/dist/js/bootstrap.min.js");
        $minifier->add("../vendor/wikiworldorder/survloop-libraries/src/parallax.min.js");
        $minifier->add("../vendor/wikiworldorder/survloop-libraries/src/typewatch.js");
        $minifier->add("../vendor/wikiworldorder/survloop-libraries/src/copy-to-clipboard.js");
        $minifier->minify("../storage/app/sys/sys1.min.js");
        
        $treeJs = '';
        $chk = SLTree::whereIn('TreeType', ['Page', 'Survey'])
            ->select('TreeID')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $tree) {
                $treeFile = '../storage/app/sys/tree-' . $tree->TreeID . '.js';
                if (file_exists($treeFile)) {
                    $treeJs .= "\n" . 'function treeLoad' . $tree->TreeID . '() {' . "\n"
                         . str_replace("\t", "", file_get_contents($treeFile)) . "\n\t"
                         . 'return true;' . "\n" . '}' . "\n";
                }
            }
        }
        $scriptsjs = view('vendor.survloop.scripts-js', [ "css" => $css, "treeJs" => $treeJs ])->render()
            . view('vendor.survloop.scripts-js-ajax', [ "css" => $css, "treeJs" => $treeJs ])->render();
        file_put_contents("../storage/app/sys/sys2.js", $scriptsjs);
        $minifier = new Minify\JS("../storage/app/sys/sys2.js");
        $minifier->minify("../storage/app/sys/sys2.min.js");
        
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
        $this->admControlInit($request, '/dashboard/users', 'administrator|staff');
        $this->loadPrintUsers();
        return view('vendor.survloop.admin.user-manage', $this->v);
    }
    
    protected function loadPrintUsers()
    {
        $this->v["printVoluns"] = [ [], [], [], [], [], [] ]; // voluns, staff, admin
        $users = User::orderBy('name', 'asc') // where('name', 'NOT LIKE', 'Session#%')
            ->get();
        foreach ($users as $i => $usr) {
            $list = 3;
            if ($usr->hasRole('administrator')) {
                $list = 0;
            } elseif ($usr->hasRole('databaser')) {
                $list = 1;
            } elseif ($usr->hasRole('staff')) {
                $list = 2;
            } elseif ($usr->hasRole('partner')) {
                $list = 3;
            } elseif ($usr->hasRole('volunteer')) {
                $list = 4;
            } else {
                $list = 5;
            }
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
        if ($request->has('tab')) {
            $this->v["filtStatus"] = trim($request->get('tab'));
        }
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
        return view('vendor.survloop.admin.contact', $this->v)->render();
    }
    
    public function postNodeURL(Request $request)
    {
        if ($request->has('step') && $request->has('tree') && intVal($request->tree) > 0) {
            $this->initLoader();
            $this->loader->loadTreeByID($request, $request->tree);
            $this->admControlInit($request, '/dash/u/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/' . $request->nodeSlug);
            $this->loadCustLoop($request, $request->tree);
            echo '<div class="pT20">' . $this->custReport->loadNodeURL($request, $request->nodeSlug) . '</div>';
        }
        exit;
    }
    
    public function ajaxChecksAdmin(Request $request, $type = '')
    {
        $this->admControlInit($request, '/ajadm/' . $type);
        $this->loadCustLoop($request, $this->treeID);
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
        return $this->custReport->ajaxChecks($request, $type);
    }
    
    public function ajaxSendEmail(Request $request)
    {
        $emaID = (($request->has('e') && intVal($request->get('e')) > 0) ? intVal($request->get('e')) : 0);
        $treeID = (($request->has('t') && intVal($request->get('t')) > 0) ? intVal($request->get('t')) : 1);
        $coreID = (($request->has('c') && intVal($request->get('c')) > 0) ? intVal($request->get('c')) : 0);
        $this->custReport->loadTree($treeID);
        $emaToArr = [];
        $emaToUsrID = 0;
        $ret = $emaTo = $emaSubj = $emaCont = '';
        $currEmail = SLEmails::find($emaID);
        if ($currEmail && isset($currEmail->EmailSubject)) {
            if ($coreID > 0) {
                $this->custReport->loadSessionData($GLOBALS["SL"]->coreTbl, $coreID);
                $emaFld = $GLOBALS["SL"]->getCoreEmailFld();
                if (isset($this->custReport->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->{ $emaFld })) {
                    $emaTo = $this->custReport->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->{ $emaFld };
                    $emaToArr[] = [$emaTo, ''];
                }
            }
            if ($request->has('o') && trim($request->get('o')) != '') {
                $emaToArr = [];
                $overrideEmail = $GLOBALS["SL"]->mexplode(';', $request->get('o'));
                if (sizeof($overrideEmail) > 0) {
                    $emaTo = $overrideEmail[0];
                    foreach ($overrideEmail as $ovr) {
                        $emaToArr[] = [trim($ovr), ''];
                    }
                }
            }
            if (sizeof($emaToArr) > 0) {
                foreach ($emaToArr as $j => $e) {
                    $emaToName = '';
                    $chkEma = User::where('email', $e[0])
                        ->first();
                    if (trim($e[0]) != '' && $chkEma && isset($chkEma->name)) {
                        $emaToName = $chkEma->name;
                    }
                    $emaToArr[$j][1] = $emaToName;
                }
            }
            $emaSubj = $this->custReport->emailRecordSwap($currEmail->EmailSubject);
            $emaCont = $this->custReport->emailRecordSwap($currEmail->EmailBody);
            $sffx = 'e' . $emaID . 't' . $treeID . 'c' . $coreID;
            $ret .= '<a id="hidivBtnMsgDeet' . $sffx . '" class="hidivBtn" href="javascript:;">Message sent to '
                . '<i class="slBlueDark">' . $emaTo . ' (' . $emaToName . ')</i>: ' . $emaSubj 
                . '"</a><div id="hidivMsgDeet' . $sffx . '" class="disNon container"><h2>' . $emaSubj . '</h2><p>' 
                . $emaCont . '</p><hr><hr></div>';
            $replyTo = [ 'info@' . $GLOBALS['SL']->getParentDomain(), $GLOBALS["SL"]->sysOpts["site-name"] ];
            if ($request->has('r') && trim($request->get('r')) != '') {
                $replyTo[0] = trim($request->get('r'));
            }
            if ($request->has('rn') && trim($request->get('rn')) != '') {
                $replyTo[1] = trim($request->get('rn'));
            }
            if (!$GLOBALS["SL"]->isHomestead()) {
                $this->custReport->sendEmail($emaCont, $emaSubj, $emaToArr, [], [], $replyTo);
            }
            $emaToUsr = User::where('email', $emaTo)->first();
            if ($emaToUsr && isset($emaToUsr->id)) {
                $emaToUsrID = $emaToUsr->id;
            }
            $this->custReport->logEmailSent($emaCont, $emaSubj, $emaTo, $emaID, $treeID, $coreID, $emaToUsrID);
        } else {
            $ret .= '<i class="red">Email template not found.</i>';
        }
        if ($request->has('l') && trim($request->get('l')) != '') {
            //$ret .= $GLOBALS["SL"]->opnAjax() . '$("#' . trim($request->get('l')) . '").fadeOut(100);' 
            //    . $GLOBALS["SL"]->clsAjax();
        }
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
    
    public function loadLogs()
    {
        $this->v["logs"] = [
            "session" => $this->logPreview('session-stuff')
            ];
        return true;
    }
    
    public function logsOverview(Request $request)
    {
        $this->admControlInit($request, '/dashboard/logs');
        $this->loadLogs();
        $this->v["phpInfo"] = $request->has('phpinfo');
        return view('vendor.survloop.admin.logs-overview', $this->v);
    }
    
    public function logsSessions(Request $request)
    {
        $this->admControlInit($request, '/dashboard/logs/session-stuff');
        $this->loadLogs();
        return view('vendor.survloop.admin.logs-sessions', $this->v);
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
    
    
}
