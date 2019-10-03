<?php
/**
  * AdminCoreController initializes functions for users who are logged in.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since  0.2.4
  */
namespace SurvLoop\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\SLEmails;
use App\Models\SLContact;
use App\Models\SLTree;
use App\Models\User;
use SurvLoop\Controllers\PageLoadUtils;
use SurvLoop\Controllers\Admin\AdminMenu;
use SurvLoop\Controllers\Globals\Globals;
use SurvLoop\Controllers\SurvLoopController;

class AdminCoreController extends SurvLoopController
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
            $this->loadDbLookups($request);
            $this->survLoopInit($request, $currPage, false);
            if (trim($perms) == '') {
                $perms = 'administrator|staff|databaser|brancher|partner|volunteer';
            }
            if ($this->v["uID"] <= 0 || !$this->v["user"]->hasRole($perms)) {
                echo view('vendor.survloop.js.inc-redirect-home', $this->v)->render();
                exit;
            }
            $this->survSysChecks();
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
        $GLOBALS["SL"]->sysOpts["footer-admin"] 
            = view('vendor.survloop.inc-footer-admin')->render();
        $this->admMenuData = [
            "adminNav"   => [],
            "currNavPos" => []
        ];
        $admMenu = null;
        if (isset($GLOBALS["SL"]->sysOpts["cust-abbr"]) 
            && $GLOBALS["SL"]->sysOpts["cust-abbr"] != 'SurvLoop') {
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
            $this->admMenuData["adminNav"] 
                = $admMenu->loadAdmMenu($this->v["user"], $currPage);
        }
        $this->tweakAdmMenu($currPage);
        if (!$this->getAdmMenuLoc($currPage) && $currPage != '') {
            $this->getAdmMenuLoc($currPage);
        }
        return view('vendor.survloop.admin.admin-menu', $this->admMenuData)
            ->render();
    }
    
    protected function getAdmMenuTopTabs()
    {
        $tabs = view(
            'vendor.survloop.admin.admin-menu-tabs', 
            $this->admMenuData
        )->render();
        if (trim($tabs) == '') {
            return '<div class="w100" style="margin-bottom: 15px;"> </div>';
        }
        $tabs = '<div id="slTopTabsWrap" class="slTopTabs"><div class="container">'
            . $tabs . '</div></div>';
        $subTabs = view(
            'vendor.survloop.admin.admin-menu-tabs-sub', 
            $this->admMenuData
        )->render();
        if (trim($subTabs) != '') {
            $tabs .= '<div class="slTopTabsSub">' . $subTabs . '</div>';
        }
        return $tabs;
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
        $custPage = ((isset($this->custReport->treeID)) 
            ? $this->custReport->initAdmMenuExtras() : '');
        if (trim($custPage) != '' && $this->v["currPage"][0] != $custPage) {
            $this->v["currPage"][0] = $custPage;
            $this->reloadAdmMenu();
        } elseif (trim($currPage) != '' && $this->v["currPage"][0] != $currPage) {
            $this->v["currPage"][0] = $currPage;
            $this->reloadAdmMenu();
        } elseif (isset($GLOBALS["SL"]->x["currPage"]) 
            && trim($GLOBALS["SL"]->x["currPage"]) != ''
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
        return ($this->admMenuData["currNavPos"][0] > -1 
            || $this->admMenuData["currNavPos"][1]  > -1
            || $this->admMenuData["currNavPos"][2]  > -1 
            || $this->admMenuData["currNavPos"][3]  > -1);
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
    
    public function admRedirEdit(Request $request)
    {
        if ($request->has('t') && intVal($request->get('t')) > 0) {
            $tree = SLTree::find(intVal($request->get('t')));
            if ($tree && isset($tree->TreeID)) {
                return view('vendor.survloop.admin.tree.ajax-redir-edit', [
                    "tree" => $tree
                ])->render();
            }
        }
        return '';
    }
    
    protected function loadSearchSuggestions()
    {    
        $this->v["searchSuggest"] = [];
        return true;
    }
    
    // Override in custom admin class
    protected function clearEmpties()
    {
        if (!session()->has('chkClearEmpties') || $GLOBALS["SL"]->REQ->has('refresh')) {
            // something can be automated by default...
            session()->put('chkClearEmpties', 1);
        }
        return true;
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
    
    //public function loadPageURL(Request $request, $pageSlug = '')
    public function loadPageURL(Request $request, $pageSlug = '', $cid = -3, $view = '')
    {
        $this->initLoader();
        if ($this->loader->loadTreeBySlug($request, $pageSlug, 'Page')) {
            $this->admControlInit($request, '/dash/' . $pageSlug);
            $this->loadCustLoop($request, $this->loader->treeID);
            if ($request->has('new') && intVal($request->get('new')) == 1) {
                $this->custReport->restartTreeSess($GLOBALS["SL"]->treeID);
            } elseif ($cid && intVal($cid) > 0) {
                $this->loader->loadPageCID($request, $GLOBALS["SL"]->treeRow, intVal($cid));
                $this->custReport->loadSessionData($GLOBALS["SL"]->coreTbl, $cid);
            }
            $this->v["content"] = $this->custReport->index($request);
            if ($request->has('edit') && intVal($request->get('edit')) == 1 
                && $this->loader->isUserAdmin()) {
                echo '<script type="text/javascript"> window.location="/dashboard/page/' 
                    . $GLOBALS["SL"]->treeID . '?all=1&alt=1&refresh=1"; </script>';
                exit;
            }
            $this->chkNewAdmMenuPage();
            return $this->loader->addAdmCodeToPage(view('vendor.survloop.master', $this->v)
                ->render());
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
        $trees = SLTree::where('TreeType', 'Page')
            //->whereRaw("TreeOpts%" . Globals::TREEOPT_HOMEPAGE . " = 0")
            //->whereRaw("TreeOpts%" . $prime . " = 0")
            ->orderBy('TreeID', 'asc')
            ->get();
        if ($trees->isNotEmpty()) {
            foreach ($trees as $tree) {
                if (isset($tree->TreeOpts) && $tree->TreeOpts%$prime == 0
                    && $tree->TreeOpts%Globals::TREEOPT_HOMEPAGE == 0) {
                    $this->loader->syncDataTrees($request, $tree->TreeDatabase, $tree->TreeID);
                    $this->loadCustLoop($request, $tree->TreeID);
                    $this->reloadAdmMenu();
                    $this->v["content"] = $this->custReport->index($request);
                    return $this->loader->addSessAdmCodeToPage(
                        $request, 
                        view('vendor.survloop.master', $this->v)->render()
                    );
                }
            }
        }
        return $this->custReport->redir('/');
    }

}