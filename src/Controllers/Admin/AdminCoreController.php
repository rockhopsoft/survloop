<?php
/**
  * AdminCoreController initializes functions for users who are logged in.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since  v0.2.5
  */
namespace RockHopSoft\Survloop\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\SLEmails;
use App\Models\SLContact;
use App\Models\SLTree;
use App\Models\SLTables;
use App\Models\User;
use RockHopSoft\Survloop\Controllers\PageLoadUtils;
use RockHopSoft\Survloop\Controllers\Admin\AdminMenu;
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use RockHopSoft\Survloop\Controllers\SurvloopController;

class AdminCoreController extends SurvloopController
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
    
    protected function admControlInit(Request $request, $currPage = '', $perms = '', $initCust = true)
    {
        if (!$this->admInitRun) {
            $this->admInitRun = true;
            $this->loadDbLookups($request);
            $this->survloopInit($request, $currPage, false);
            if (trim($perms) == '') {
                $perms = 'administrator|staff|databaser|brancher|partner|volunteer';
            }
            if ($this->v["uID"] <= 0 || !$this->v["user"]->hasRole($perms)) {
                echo view(
                    'vendor.survloop.js.inc-redirect-home', 
                    $this->v
                )->render();
                exit;
            }
            $this->survSysChecks();
            $this->initPowerUser();
            $this->v["isDash"] = true;
            if ($GLOBALS["SL"]->sysOpts["cust-abbr"] == 'survloop') {
                $GLOBALS["SL"]->sysOpts["cust-abbr"] = 'Survloop';
            }
            $this->checkCurrPage();
            $this->reloadAdmMenu();
            $this->loadSearchSuggestions();
            $this->initExtra($request);
            if ($initCust) {
                $this->initCustViews($request);
            }
            $this->logPageVisit();
            $this->clearEmpties();
        }
        return true;
    }
    
    protected function getAdmMenu($currPage = '')
    {
        $GLOBALS["SL"]->sysOpts["footer-admin"] = view(
            'vendor.survloop.inc-footer-admin'
        )->render();
        $this->admMenuData = [
            "adminNav"   => [],
            "currNavPos" => []
        ];
        $admMenu = null;
        if (isset($GLOBALS["SL"]->sysOpts["cust-abbr"])) {
            $vend = $GLOBALS["SL"]->sysOpts["cust-vend"];
            $abbr = $GLOBALS["SL"]->sysOpts["cust-abbr"];
            if ($abbr != 'Survloop') {
                $custClass = $vend . "\\" . $abbr 
                    . "\\Controllers\\" . $abbr . "AdminMenu";
                if (class_exists($custClass)) {
                    eval("\$admMenu = new " . $custClass . ";");
                }
            }
        }
        if (!$admMenu) {
            $admMenu = new AdminMenu;
        }
        if ($admMenu) {
            $this->admMenuData["adminNav"] = $admMenu->loadAdmMenu(
                $this->v["user"], 
                $currPage
            );
        }
        $this->tweakAdmMenu($currPage);
        if (!$this->getAdmMenuLoc($currPage) 
            && $currPage != '') {
            $this->getAdmMenuLoc($currPage);
        }
        return view(
            'vendor.survloop.admin.admin-menu', 
            $this->admMenuData
        )->render();
    }
    
    protected function getAdmMenuTopTabs()
    {
        $tabs = view(
            'vendor.survloop.admin.admin-menu-tabs', 
            $this->admMenuData
        )->render();
        if (trim($tabs) == '') {
            return '<div class="w100 mB15"> </div>';
        }
        $tabs = '<div id="slTopTabsWrap" class="slTopTabs">' . $tabs . '</div>';
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
        if (trim($custPage) != '' 
            && $this->v["currPage"][0] != $custPage) {
            $this->v["currPage"][0] = $custPage;
            $this->reloadAdmMenu();
        } elseif (trim($currPage) != '' 
            && $this->v["currPage"][0] != $currPage) {
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
                    if (isset($nav[4]) && is_array($nav[4]) 
                        && sizeof($nav[4]) > 0) {
                        foreach ($nav[4] as $j => $nA) {
                            if (isset($nA[0]) && $nA[0] == $currPage) {
                                $this->admMenuData["currNavPos"] = [$i, $j, -1, -1];
                            }
                            if (isset($nA[4]) && is_array($nA[4]) 
                                && sizeof($nA[4]) > 0) {
                                foreach ($nA[4] as $k => $nB) {
                                    if (isset($nB[0]) && $nB[0] == $currPage) {
                                        $this->admMenuData["currNavPos"] = [
                                            $i, 
                                            $j, 
                                            $k, 
                                            -1
                                        ];
                                    }
                                    if (isset($nB[4]) && is_array($nB[4]) 
                                        && sizeof($nB[4]) > 0) {
                                        foreach ($nB[4] as $l => $nC) {
                                            if (isset($nC[0]) && $nC[0] == $currPage) {
                                                $this->admMenuData["currNavPos"] = [
                                                    $i, 
                                                    $j, 
                                                    $k, 
                                                    $l
                                                ];
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
        if ($request->has('t') 
            && intVal($request->get('t')) > 0) {
            $tree = SLTree::find(intVal($request->get('t')));
            if ($tree && isset($tree->tree_id)) {
                return view(
                    'vendor.survloop.admin.tree.ajax-redir-edit', 
                    [ "tree" => $tree ]
                )->render();
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
        if (!session()->has('chkClearEmpties') 
            || $GLOBALS["SL"]->REQ->has('refresh')) {
            // something can be automated by default...
            session()->put('chkClearEmpties', 1);
            session()->save();
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
            $this->v["content"] = '<div class="pT20">' 
                . $this->custReport->loadNodeURL($request, $nodeSlug) 
                . '</div>';
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
    
    public function loadPageURL(Request $request, $pageSlug = '', $cid = -3, $view = '', $skipPublic = false)
    {
        $this->initLoader();
        if ($this->loader->loadTreeBySlug($request, $pageSlug, 'Page')) {
            $this->admControlInit($request, '/dash/' . $pageSlug);
            $this->loadCustLoop($request, $this->loader->treeID);

            if ($request->has('edit') 
                && intVal($request->get('edit')) == 1 
                && $this->loader->isUserAdmin()) {
                echo '<script type="text/javascript"> '
                    . 'window.location="/dashboard/page/' 
                    . $GLOBALS["SL"]->treeID . '?all=1&alt=1&refresh=1";'
                    . ' </script>';
                exit;
            }
            //$view = $this->chkPageView($view);
            $cid = $this->loader->chkPageCID($request, $cid, $skipPublic);
            if ($request->has('new') && intVal($request->get('new')) == 1) {
                $this->custReport->restartTreeSess($GLOBALS["SL"]->treeID);
            } elseif ($cid && intVal($cid) > 0) {
                $this->loader->loadPageCID($request, $GLOBALS["SL"]->treeRow, intVal($cid));
                $this->custReport->loadSessionData($GLOBALS["SL"]->coreTbl, $cid);
                if (in_array($view, ['pdf', 'full-pdf'])) {
                    return $this->custReport->byID($request, $cid, '', $request->has('ajax'));
                }
            }
            $this->custReport->chkPageToken();
            /*
            $allowCache = $this->chkPageAllowCache($request);
            if ($this->topCheckCache($request, 'page') && $allowCache) {
                return $this->addSessAdmCodeToPage($request, $this->pageContent);
            }
            */
            $this->v["content"] = $this->custReport->index($request);
            if ($request->has('ajax') && intVal($request->ajax) == 1) {
                return $this->v["content"];
            }
            /*
            $this->chkPageHideDisclaim($request, $cid);
            if (in_array($view, ['xml', 'json'])) {
                $GLOBALS["SL"]->pageView = 'public';
                $this->custReport->loadXmlMapTree($request);
                return $this->custReport->getXmlID($request, $cid, $pageSlug);
            }
            $this->pageContent = $this->custReport->index($request);
            if ($allowCache) {
                $treeID = $GLOBALS["SL"]->treeRow->tree_id;
                $treeType = strtolower($GLOBALS["SL"]->treeRow->tree_type);
                $this->topSaveCache($treeID, $treeType);
            }
            */
            $this->chkNewAdmMenuPage();
            return $this->loader->addAdmCodeToPage(
                view('vendor.survloop.master', $this->v)->render()
            );
        }
        $this->loader->loadDomain();
        return redirect($this->loader->domainPath . '/');
    }
    
    public function loadPageURLrawID(Request $request, $pageSlug = '', $cid = -3, $view = '')
    {
        return $this->loadPageURL($request, $pageSlug, $cid, $view, true);
    }
    
    public function loadPageDashboard(Request $request)
    {
        $this->admControlInit($request, '/dashboard');
        $this->initLoader();
        $prime = $this->loader->getMaxPermsPrime();
        if ($prime <= 1) {
            return $this->redir('/login');
        }
        $trees = SLTree::where('tree_type', 'Page')
            //->whereRaw("tree_opts%" . Globals::TREEOPT_HOMEPAGE . " = 0")
            //->whereRaw("tree_opts%" . $prime . " = 0")
            ->orderBy('tree_id', 'asc')
            ->get();
        if ($trees->isNotEmpty()) {
            foreach ($trees as $tree) {
                if (isset($tree->tree_opts) && $tree->tree_opts%$prime == 0
                    && $tree->tree_opts%Globals::TREEOPT_HOMEPAGE == 0) {
                    $this->loader->syncDataTrees($request, $tree->tree_database, $tree->tree_id);
                    $this->loadCustLoop($request, $tree->tree_id);
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

    protected function chkCoreTbls()
    {
        if (!session()->has('chkCoreTbls') 
            || $GLOBALS["SL"]->REQ->has('refresh')) {
            $userTbl = $GLOBALS["SL"]->loadUsrTblRow();
            $trees = SLTree::where('tree_database', $GLOBALS["SL"]->dbID)
                ->where('tree_core_table', '>', 0)
                ->get();
            if ($trees->isNotEmpty()) {
                foreach ($trees as $tree) {
                    $coreTbl = SLTables::find($tree->tree_core_table);
                    $GLOBALS["SL"]->initCoreTable($coreTbl, $userTbl);
                }
            }
            $this->allStdCondition(
                '#IsAdmin', 
                'The user is currently logged in as an administrator.'
            );
            $this->allStdCondition(
                '#IsNotAdmin', 
                'The user is not currently logged in as an administrator.'
            );
            $this->allStdCondition(
                '#IsStaff', 
                'The user is currently logged in as a staff user.'
            );
            $this->allStdCondition(
                '#IsStaffOrAdmin', 
                'The user is currently logged in as a staff or admin user.'
            );
            $this->allStdCondition(
                '#IsPartnerStaffOrAdmin', 
                'The user is currently logged in as a partner, staff, or admin user.'
            );
            $this->allStdCondition(
                '#IsPartnerStaffAdminOrOwner', 
                'The user is currently logged in as a partner, staff, '
                    . 'admin user, or the owner of core record.'
            );
            $this->allStdCondition(
                '#IsPartner', 
                'The user is currently logged in as a partner.'
            );
            $this->allStdCondition(
                '#IsVolunteer', 
                'The user is currently logged in as a volunteer.'
            );
            $this->allStdCondition(
                '#IsBrancher', 
                'The user is currently logged in as a database manager.'
            );
            $this->allStdCondition(
                '#NodeDisabled', 
                'This node is not active (for the public).'
            );
            $this->allStdCondition(
                '#IsLoggedIn', 
                'Complainant is currently logged into the system.'
            );
            $this->allStdCondition(
                '#IsNotLoggedIn', 
                'Complainant is not currently logged into the system.'
            );
            $this->allStdCondition(
                '#IsOwner', 
                'The user is currently logged is the owner of this record.'
            );
            $this->allStdCondition(
                '#IsProfileOwner', 
                'The user is currently logged in owns this user profile.'
            );
            $this->allStdCondition(
                '#IsPrintable', 
                'The current page view is intended to be printable.'
            );
            $this->allStdCondition(
                '#IsPrintInFrame', 
                'The current page view is printed into frame/ajax/widget.'
            );
            $this->allStdCondition(
                '#IsDataPermPublic', 
                'The current data permissions are set to public.'
            );
            $this->allStdCondition(
                '#IsDataPermPrivate', 
                'The current data permissions are set to private.'
            );
            $this->allStdCondition(
                '#IsDataPermSensitive', 
                'The current data permissions are set to sensitive.'
            );
            $this->allStdCondition(
                '#IsDataPermInternal', 
                'The current data permissions are set to internal.'
            );
            $this->allStdCondition(
                '#HasTokenDialogue', 
                'Current page load includes an access token dialogue.'
            );
            $this->allStdCondition(
                '#EmailVerified', 
                'Current user\'s email address has been verified.'
            );
            $this->allStdCondition(
                '#TestLink', 
                'Current page url parameters includes ?test=1.'
            );
            $this->allStdCondition(
                '#NextButton', 
                'Current page load results from clicking the survey\'s next button.'
            );
            //$this->allStdCondition('#HasUploads', 'Current core table record has associated uploads.');
            $trees = SLTree::where('tree_type', 'Page')->get();
            if ($trees->isNotEmpty()) {
                foreach ($trees as $tree) {
                    $this->v["treeAdmin"]->updateTreeOpts($tree->tree_id);
                }
            }
            session()->put('chkCoreTbls', 1);
            session()->save();
        }
        return true;
    }


}