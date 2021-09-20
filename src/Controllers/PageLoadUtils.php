<?php
/**
  * PageLoadUtils assists the Survloop-level routing processes in SurvRoutes and AdminController.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers;

use DB;
use Auth;
use Cache;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use App\Models\SLNode;
use App\Models\SLTree;
use App\Models\SLDefinitions;
use App\Models\SLSess;
use RockHopSoft\Survloop\Controllers\Globals\GlobalsCache;
use RockHopSoft\Survloop\Controllers\Globals\Globals;

class PageLoadUtils extends Controller
{
    protected $isAdminPage = false;
    public $dashPrfx       = '';
    public $domainPath     = 'http://survloop.local';
    public $custAbbr       = 'Survloop';
    public $custVend       = 'RockHopSoft';
    public $custPckg       = 'rockhopsoft/survloop';
    public $dbID           = 1;
    public $treeID         = 0;
    public $cacheKey       = '';
    public $pageContent    = '';

    // This is where the client installation's extension of TreeSurvForm is loaded
    public $custLoop = null;

    /**
     * Initialize page loader and whether or not
     * this page requires user authentication.
     *
     * @return void
     */
    public function __construct($isAdminPage = false)
    {
        $this->isAdminPage = $isAdminPage;
        if ($isAdminPage) {
            $this->dashPrfx = '/dash';
        }
    }

    /**
     * Load the extension package's
     *
     * @return void
     */
    public function loadLoop(Request $request, $skipSessLoad = false, $coreID = -3)
    {
        $this->loadAbbr();
        $class = "RockHopSoft\\Survloop\\Controllers\\Tree\\TreeSurvForm";
        $custLoopFile = '../vendor/' . $this->custPckg
            . '/src/Controllers/' . $this->custAbbr . '.php';
        if ($this->custAbbr != 'Survloop' && file_exists($custLoopFile)) {
            $custClass = $this->custVend . "\\" . $this->custAbbr
                . "\\Controllers\\" . $this->custAbbr . "";
            if (class_exists($custClass)) {
                $class = $custClass;
            }
        }
        eval("\$this->custLoop = new " . $class . "("
            . "\$request, "
            . $coreID . ", "
            . $this->dbID . ", "
            . $this->treeID . ", "
            . (($skipSessLoad) ? "true" : "false")
            . ");"
        );
    }

    /**
     * Load the extension package's GLOBALS.
     * Similar to /Globals/Globals.php
     *
     * @return void
     */
    protected function loadCustomGlobals()
    {
        $this->loadAbbr();
        if (!isset($GLOBALS["CUST"])) {
            $GLOBALS["CUST"] = null;
            $custFile = '../vendor/' . $this->custPckg
                . '/src/Controllers/' . $this->custAbbr . 'Globals.php';
            if ($this->custAbbr != 'Survloop' && file_exists($custFile)) {
                $custClass = $this->custVend . "\\"
                    . $this->custAbbr . "\\Controllers\\"
                    . $this->custAbbr . "Globals";
                if (class_exists($custClass)) {
                    eval("\$GLOBALS['CUST'] = new " . $custClass . ";");
                }
            }
        }
    }

    /**
     * Load this system's root domain name or path.
     *
     * @return string
     */
    public function loadDomain()
    {
        $appUrl = SLDefinitions::select('def_description')
            ->where('def_database', 1)
            ->where('def_set', 'System Settings')
            ->where('def_subset', 'app-url')
            ->first();
        if ($appUrl && isset($appUrl->def_description)) {
            $this->domainPath = $appUrl->def_description;
        }
        return $this->domainPath;
    }

    /**
     *
     *
     * @return void
     */
    public function checkHttpsDomain(Request $request)
    {
        if (isset($this->domainPath)
            && strpos($request->fullUrl(), $this->domainPath) === false) {
            $pos1 = strpos($this->domainPath, 'https://');
            $http = str_replace('https://', 'http://', $this->domainPath);
            $pos2 = strpos($request->fullUrl(), $http);
            if ($pos1 !== false && $pos2 !== false) {
                $redir = str_replace('http://', 'https://', $request->fullUrl());
                header("Location: " . $redir);
                exit;
            }
        }
    }

    /**
     * Get the package's custom abbreviation used in the system.
     *
     * @return string
     */
    public function loadAbbr()
    {
        $chk = SLDefinitions::select('def_description')
            ->where('def_database', 1)
            ->where('def_set', 'System Settings')
            ->where('def_subset', 'cust-abbr')
            ->first();
        if ($chk && isset($chk->def_description)) {
            $this->custAbbr = trim($chk->def_description);
        }
        $chk = SLDefinitions::select('def_description')
            ->where('def_database', 1)
            ->where('def_set', 'System Settings')
            ->where('def_subset', 'cust-vend')
            ->first();
        if ($chk && isset($chk->def_description)) {
            $this->custVend = trim($chk->def_description);
        }
        $chk = SLDefinitions::select('def_description')
            ->where('def_database', 1)
            ->where('def_set', 'System Settings')
            ->where('def_subset', 'cust-package')
            ->first();
        if ($chk && isset($chk->def_description)) {
            $this->custPckg = trim($chk->def_description);
        }
        return $this->custAbbr;
    }

    /**
     * Check whether or not this user has permissions to open this tree.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  int  $dbID
     * @param  int  $treeID
     * @return void
     */
    public function syncDataTrees(Request $request, $dbID = 1, $treeID = 1)
    {
        $this->dbID = $dbID;
        $this->treeID = $treeID;
        $GLOBALS["SL"] = new Globals($request, $dbID, $treeID, $treeID);
        $GLOBALS["SL"]->microLog();
    }

    /**
     * Check whether or not this user has permissions to open this tree.
     *
     * @param  int  $treeOpts
     * @return boolean
     */
    protected function userHasTreePerms($treeOpts = 1)
    {
        if ($treeOpts%Globals::TREEOPT_ADMIN == 0) {
            return $this->isUserAdmin();
        }
        if ($treeOpts%Globals::TREEOPT_STAFF == 0) {
            return ($this->isUserStaff()
                || $this->isUserAdmin());
        }
        if ($treeOpts%Globals::TREEOPT_PARTNER == 0) {
            return ($this->isUserPartn()
                || $this->isUserStaff()
                || $this->isUserAdmin());
        }
        if ($treeOpts%Globals::TREEOPT_VOLUNTEER == 0) {
            return ($this->isUserVolun()
                || $this->isUserPartn()
                || $this->isUserStaff()
                || $this->isUserAdmin());
        }
        return true;
    }

    /**
     * Get the tree options for the current user's highest permission.
     *
     * @return integer
     */
    public function getMaxPermsPrime()
    {
        $ret = ((!Auth::user() || !isset(Auth::user()->id)) ? -1 : 0);
        if (Auth::user()) {
            if (Auth::user()->hasRole('administrator')) {
                $ret = Globals::TREEOPT_ADMIN;
            } elseif (Auth::user()->hasRole('staff|databaser|brancher')) {
                $ret = Globals::TREEOPT_STAFF;
            } elseif (Auth::user()->hasRole('partner')) {
                $ret = Globals::TREEOPT_PARTNER;
            } elseif (Auth::user()->hasRole('volunteer')) {
                $ret = Globals::TREEOPT_VOLUNTEER;
            }
        }
        return $ret;
    }

    /**
     * Get an array of the tree options for the current user's permissions.
     *
     * @return array
     */
    public function getPermOpts()
    {
        $ret = [];
        if (Auth::user() && isset(Auth::user()->id) && intVal(Auth::user()->id) > 0) {
            $list = 'administrator';
            if (Auth::user()->hasRole($list)) {
                $ret[] = Globals::TREEOPT_ADMIN;
            }
            $list .= '|staff|databaser|brancher';
            if (Auth::user()->hasRole($list)) {
                $ret[] = Globals::TREEOPT_STAFF;
            }
            $list .= '|partner';
            if (Auth::user()->hasRole($list)) {
                $ret[] = Globals::TREEOPT_PARTNER;
            }
            $list .= '|volunteer';
            if (Auth::user()->hasRole($list)) {
                $ret[] = Globals::TREEOPT_VOLUNTEER;
            }
        }
        return $ret;
    }

    /**
     * Get the URL prefix for this tree's full path.
     *
     * @param  int  $treeOpts
     * @return string
     */
    public function getPageDashPrefix($treeOpts = 1)
    {
        if ($treeOpts%Globals::TREEOPT_ADMIN == 0
            || $treeOpts%Globals::TREEOPT_STAFF == 0
            || $treeOpts%Globals::TREEOPT_PARTNER == 0
            || $treeOpts%Globals::TREEOPT_VOLUNTEER == 0) {
            return '/dash';
        }
        return '';
    }

    /**
     * Check whether or not a tree has any user level permissions.
     *
     * @param  App\Models\SLTree  $tree
     * @return boolean
     */
    public function chkNoTreePerms($tree)
    {
        if (!$tree || !isset($tree->tree_opts)) {
            return false;
        }
        return ($tree->tree_opts%Globals::TREEOPT_ADMIN > 0
            && $tree->tree_opts%Globals::TREEOPT_STAFF > 0
            && $tree->tree_opts%Globals::TREEOPT_PARTNER > 0
            && $tree->tree_opts%Globals::TREEOPT_VOLUNTEER > 0);
    }

    /**
     * Check if we should load a branching tree from it's Tree ID.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  string  $treeSlug
     * @param  string  $type
     * @return mixed
     */
    public function loadTreeByID(Request $request, $treeID = -3)
    {
        if (intVal($treeID) > 0) {
            $tree = SLTree::find($treeID);
            if ($tree && isset($tree->tree_opts)) {
                if ($this->okToLoadTree($tree->tree_opts)) {
                    $this->syncDataTrees(
                        $request,
                        $tree->tree_database,
                        $treeID
                    );
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check whether or not the current page request
     * desires to edit this page (as an Admin or Staff user).
     *
     * @param  Illuminate\Http\Request  $request
     * @return boolean
     */
    public function hasParamEdit(Request $request)
    {
        return ($request->has('edit')
            && intVal($request->get('edit')) == 1);
    }

    /**
     * Check if we should load a branching tree from it's URL slug.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  string  $treeSlug
     * @param  string  $type
     * @return mixed
     */
    public function loadTreeBySlug(Request $request, $treeSlug = '', $type = 'Survey')
    {
        if (trim($treeSlug) != '') {
            $urlTrees = SLTree::where('tree_type', $type)
                ->where('tree_slug', $treeSlug)
                ->orderBy('tree_id', 'asc')
                ->get();
            if ($urlTrees->isNotEmpty()) {
                foreach ($urlTrees as $t) {
                    if ($t
                        && isset($t->tree_opts)
                        && $this->okToLoadTree($t->tree_opts)
                        && (!isset($GLOBALS["SL"])
                            || sizeof($GLOBALS["SL"]->REQ->all()) == 0
                            || $GLOBALS["SL"]->treeID != $t->tree_id)) {
                        $this->syncDataTrees(
                            $request,
                            $t->tree_database,
                            $t->tree_id
                        );
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Run the current search request.
     *
     * @param  Illuminate\Http\Request  $request
     * @return mixed
     */
    public function searchRun(Request $request)
    {
        $params = $this->getSearchRunParams($request);
        $searchTree = null;
        if ($request->has('sDataSet')
            && intVal($request->get('sDataSet')) > 0) {
            $perms = $this->getPermOpts();
            $searchDataTbl = intVal($request->get('sDataSet'));
            $trees = SLTree::where('tree_type', 'Page')
                ->where('tree_database', $this->dbID)
                ->where('tree_core_table', $searchDataTbl)
                ->get();
            $searchTree = $this->chkSearchRunTrees($trees, $perms);
            if ($searchTree === null || !isset($searchTree->tree_opts)) {
                $trees = SLTree::where('tree_type', 'Page')
                    ->where('tree_database', $this->dbID)
                    ->get();
                $searchTree = $this->chkSearchRunTrees($trees, $perms);
            }
            if ($searchTree !== null && isset($searchTree->tree_opts)) {
                $redir = $this->getPageDashPrefix($searchTree->tree_opts)
                    . '/' . $searchTree->tree_slug . $params;
                return redirect($redir, 302);
            }
        }
        return redirect('/search' . $params, 302);
    }

    /**
     * Get the current request's search parameters.
     *
     * @param  Illuminate\Http\Request  $request
     * @return string
     */
    private function getSearchRunParams(Request $request)
    {
        $params = '?s=';
        if ($request->has('s')) {
            $params .= $request->get('s');
        }
        if ($request->has('sFilt')
            && trim($request->get('sFilt')) != '') {
            $params .= '&sFilt=' . $request->get('sFilt');
        }
        if ($request->has('sSort')
            && trim($request->get('sSort')) != '') {
            $params .= '&sSort=' . $request->get('sSort');
        }
        if ($request->has('sSortDir')
            && trim($request->get('sSortDir')) != '') {
            $params .= '&sSortDir=' . $request->get('sSortDir');
        }
        if ($request->has('sView')
            && trim($request->get('sView')) != '') {
            $params .= '&sView=' . $request->get('sView');
        }
        return $params;
    }

    /**
     * Find a search page which lines up with these permissions.
     *
     * @param  string  $treeSlug
     * @param  int  $perms
     * @return App\Models\SLTree
     */
    protected function chkSearchRunTrees($trees, $perms)
    {
        $searchTree = $searchTreeHome = null;
        if ($trees->isNotEmpty()) {
            if (sizeof($perms) > 0) {
                foreach ($perms as $perm) {
                    if ($searchTree === null) {
                        foreach ($trees as $tree) {
                            if ($searchTree === null
                                && $tree->tree_opts%$perm == 0
                                && $tree->tree_opts%Globals::TREEOPT_SEARCH == 0) {
                                if ($tree->tree_opts%Globals::TREEOPT_HOMEPAGE == 0) {
                                    $searchTreeHome = $tree;
                                } else {
                                    $searchTree = $tree;
                                }
                            }
                        }
                    }
                }
            }
            if ($searchTree === null) {
                foreach ($trees as $tree) {
                    if ($searchTree === null
                        && $tree->tree_opts%Globals::TREEOPT_SEARCH   == 0
                        && $tree->tree_opts%Globals::TREEOPT_ADMIN     > 0
                        && $tree->tree_opts%Globals::TREEOPT_STAFF     > 0
                        && $tree->tree_opts%Globals::TREEOPT_PARTNER   > 0
                        && $tree->tree_opts%Globals::TREEOPT_VOLUNTEER > 0) {
                        if ($tree->tree_opts%Globals::TREEOPT_HOMEPAGE == 0) {
                            $searchTreeHome = $tree;
                        } else {
                            $searchTree = $tree;
                        }
                    }
                }
            }
        }
        if ($searchTree === null && $searchTreeHome !== null) {
            $searchTree = $searchTreeHome;
        }
        return $searchTree;
    }

    /**
     * Check if this tree slug matches any system redirects.
     *
     * @param  string  $treeSlug
     * @return string
     */
    protected function chkPageRedir($treeSlug = '')
    {
        if (trim($treeSlug) != '') {
            $redirTree = SLTree::where('tree_slug', $treeSlug)
                ->where('tree_type', 'Redirect')
                ->orderBy('tree_id', 'asc')
                ->first();
            if ($redirTree && isset($redirTree->tree_desc)
                && trim($redirTree->tree_desc) != '') {
                $redirURL = $redirTree->tree_desc;
                if (strpos($redirURL, $this->domainPath) === false
                    && substr($redirURL, 0, 1)       != '/'
                    && strpos($redirURL, 'http://')  === false
                    && strpos($redirURL, 'https://') === false) {
                    $redirURL = '/' . $redirURL;
                }
                return $redirURL;
            }
        }
        return $treeSlug;
    }

    /**
     * Load a survey tree.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  string  $treeSlug
     * @return mixed
     */
    public function loadNodeTreeURL(Request $request, $treeSlug = '')
    {
        return $this->loadNodeTreeURLInner($request, $treeSlug);
    }

    /**
     * Edit a specific core record in a survey tree.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  int  $cid
     * @param  string  $treeSlug
     * @return mixed
     */
    public function loadNodeTreeURLedit(Request $request, $cid = -3, $treeSlug = '')
    {
        return $this->loadNodeTreeURLInner($request, $treeSlug, $cid);
    }

    /**
     * Find the survey tree to redirect within.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  string  $treeSlug
     * @param  int  $cid
     * @return mixed
     */
    public function loadNodeTreeURLInner(Request $request, $treeSlug = '', $cid = -3)
    {
        $redir = $this->chkLoginRedir($request);
        if ($redir != '') {
            echo '<html><body><script type="text/javascript"> setTimeout("window.location=\''
                . $redir . '\'", 10); </script></body></html>';
            exit;
            //return redirect($redir, 302)
            //    ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }
        $this->loadDomain();
        $this->checkHttpsDomain($request);
        if (trim($treeSlug) != '') {
            $urlTrees = SLTree::where('tree_slug', $treeSlug)
                ->get();
            if ($urlTrees->isNotEmpty()) {
                foreach ($urlTrees as $t) {
                    if ($t
                        && isset($t->tree_opts)
                        && $this->okToLoadTree($t->tree_opts)) {
                        $rootNode = SLNode::find($t->tree_first_page);
                        if ($rootNode
                            && isset($t->tree_slug)
                            && isset($rootNode->node_prompt_notes)) {
                            return $this->loadNodeTreeURLredir(
                                $request,
                                $t,
                                $rootNode,
                                $cid
                            );
                        }
                    }
                }
            }
        }
        return redirect($this->domainPath . '/', 302);
    }

    /**
     * Redirect to a specific node within a survey tree.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  App\Models\SLTree  $tree
     * @param  App\Models\SLNode  $rootNode
     * @param  int  $cid
     * @return mixed
     */
    protected function loadNodeTreeURLredir(Request $request, $tree, $rootNode, $cid = -3)
    {
        $redir = $this->dashPrfx . '/u/' . $tree->tree_slug
            . '/' . $rootNode->node_prompt_notes;
        if ($cid > 0) {
            $redir .= '?cid=' . $cid;
            $this->loadPageCID($request, $tree, $cid);
        } else {
            $redir .= '?started=1&new=' . rand(100000000, 1000000000);
            session()->forget('coreID' . $tree->tree_id);
            session()->forget('sessID' . $tree->tree_id);
        }
        $path = $this->domainPath;
        $slug = $tree->tree_slug;
        $paramTxt = str_replace($path . '/start/' . $slug, '',
            str_replace($path . '/dashboard/start/' . $slug, '',
                $request->fullUrl()));
        if (substr($paramTxt, 0, 1) == '/') {
            $paramTxt = substr($paramTxt, 1);
        }
        if (trim($paramTxt) != '' && substr($paramTxt, 0, 1) == '?') {
            $redir .= '&' . substr($paramTxt, 1);
        }
        $redir = str_replace('&new=1&', '&', $redir);
        return redirect($path . $redir, 302)
            ->header(
                'Cache-Control',
                'no-store, no-cache, must-revalidate'
            );
    }

    /**
     * Loading a core record ID identifying a specific Page Tree.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  int  $cid
     * @param  boolean  $skipPublic
     * @return int
     */
    public function chkPageCID(Request $request, $cid = 0, $skipPublic = false)
    {
        if ($cid <= 0 && $request->has('cid')) {
            $cid = intVal($request->get('cid'));
        }
        if ($cid > 0) {
            if (!$skipPublic) {
                $GLOBALS["SL"]->coreID = $GLOBALS["SL"]->swapIfPublicID($cid);
            } else {
                $GLOBALS["SL"]->coreID = intVal($cid);
            }
            $cid = $GLOBALS["SL"]->coreID;
        }
        if ($request->has('cidi')) {
            $cid = $GLOBALS["SL"]->coreID = intVal($request->get('cidi'));
        }
        return $cid;
    }

    /**
     * Load a Survloop page's core record.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  App\Models\SLTree  $tree
     * @param  int  $cid
     * @return void
     */
    public function loadPageCID(Request $request, $tree, $cid)
    {
        if ($cid > 0
            && $tree
            && isset($tree->tree_id)
            && Auth::user()
            && isset(Auth::user()->id)) {
            $sess = SLSess::where('sess_user_id', Auth::user()->id)
                ->where('sess_tree', $tree->tree_id)
                ->where('sess_core_id', $cid)
                ->where('sess_is_active', 1)
                ->orderBy('updated_at', 'desc')
                ->first();
            if (!$sess || !isset($sess->sess_id)) {
                $sess = new SLSess;
                $sess->sess_user_id   = Auth::user()->id;
                $sess->sess_tree      = $tree->tree_id;
                $sess->sess_core_id   = $cid;
                $sess->sess_is_active = 1;
                $sess->save();
            }
            if ($request->has("n")
                && intVal($request->get("n")) > 0) {
                $sess->update([
                    'sess_curr_node' => intVal($request->get("n"))
                ]);
            } elseif ($sess->sess_curr_node == -86) {
                // last session deactivate (hopefully completed)
                $sess->update([ 'sess_curr_node' => $tree->tree_root ]);
            }
            session()->put('sessID' . $tree->tree_id, $sess->sess_id);
            session()->put('coreID' . $tree->tree_id, $cid);
            session()->put('sessID' . $tree->tree_id . 'old' . $cid, $sess->sess_id);
            session()->put('coreID' . $tree->tree_id . 'old' . $cid, time());
            session()->save();
        }
    }

    /**
     * Load a Survloop page URL with a core record ID
     * — the raw ID, not the pulbic ID.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  string  $pageSlug
     * @param  int  $cid
     * @param  string  $view
     * @return mixed
     */
    public function loadPageURLrawID(Request $request, $pageSlug = '', $cid = -3, $view = '')
    {
        return $this->loadPageURL($request, $pageSlug, $cid, $view, true);
    }

    /**
     * Get this page's top-level cache key.
     *
     * @param  string  $type
     * @return string
     */
    protected function chkGenCacheKey($type = 'page')
    {
        if (trim($this->cacheKey) == '') {
            return $this->topGenCacheKey($type);
        }
        return $this->cacheKey;
    }

    /**
     * Generate a key for this page's top-level cache.
     *
     * @param  string  $type
     * @return string
     */
    protected function topGenCacheKey($type = 'page')
    {
        $sffx = '-visitor';
        if ($this->isUserAdmin()) {
            $sffx = '-admin';
        } elseif ($this->isUserStaff()) {
            $sffx = '-staff';
        } elseif ($this->isUserPartn()) {
            $sffx = '-partner';
        } elseif ($this->isUserVolun()) {
            $sffx = '-volun';
        } elseif (Auth::user() && Auth::user()->id > 0) {
            $sffx = '-user';
        }
        if (isset($GLOBALS["SL"])) {
            $sffx .= $GLOBALS["SL"]->getCacheSffxAdds();
            $GLOBALS["SL"]->cacheSffx = $sffx;

//echo 'topGenCacheKey ? ' . (($GLOBALS["SL"]->isOwner) ? 't' : 'f') . ''; exit;

        }
        $uri = str_replace('?refresh=1', '',
                str_replace('?refresh=1&', '?',
                    str_replace('&refresh=1', '', $_SERVER["REQUEST_URI"])
                )
            );
        $uri = substr($uri, 1);
        $this->cacheKey = $type . '-' . $uri . $sffx . '.html';
        return $this->cacheKey;
    }

    /**
     * Check for problems with a cache's JS and CSS dependencies.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  string  $type
     * @return boolean
     */
    public function topCheckCache(Request $request, $type = 'page')
    {
        $this->topGenCacheKey($type);
        $cache = new GlobalsCache;
        if ($request->has('refresh')) {
            $cache->forgetCache($this->cacheKey, $type);
            return false;
        }
        $content = $cache->chkCache($this->cacheKey, $type);
        if (!$this->checkCacheProblem($content, $type)) {
            $this->pageContent = $content;
            return true;
        }
        return false;
    }

    /**
     * Store a cache of this page.
     *
     * @param  int  $treeID
     * @param  string  $treeType
     * @return void
     */
    protected function topSaveCache($treeID = 0, $treeType = '')
    {
        $this->chkGenCacheKey();
        $cache = new GlobalsCache;
        $cid = ((isset($GLOBALS["SL"]->coreID)) ? $GLOBALS["SL"]->coreID : 0);
        $cache->putCache($this->cacheKey, $this->pageContent, $treeType, $treeID, $cid);
    }

    /**
     * Check for problems with a cache's JS and CSS dependencies.
     *
     * @param  string  $content
     * @param  string  $type
     * @return boolean
     */
    public function checkCacheProblem($content = '', $type = '')
    {
        if (trim(strip_tags($content)) == '') {
            return true;
        }
        $cache = new GlobalsCache;
        $problem = false;
        $pos = strpos($content, 'id="dynCss"');
        if ($pos > 0) {
            $pos = strpos($content, '/sys/dyna/', $pos);
            $pos2 = strpos($content, '"', $pos);
            if ($pos > 0 && $pos2 > 0) {
                $file = substr($content, $pos+10, $pos2-$pos-10);
                $chk = trim($cache->getCachePageCss($file));
                if ($chk == '') {
                    $problem = true;
                }
            }
        }
        $pos = strpos($content, 'id="dynJs"');
        if ($pos > 0) {
            $pos = strpos($content, '/sys/dyna/', $pos);
            $pos2 = strpos($content, '"', $pos);
            if ($pos > 0 && $pos2 > 0) {
                $file = substr($content, $pos+10, $pos2-$pos-10);
                $chk = trim($cache->getCachePageJs($file));
                if ($chk == '') {
                    $problem = true;
                }
            }
        }
        return $problem;
    }

    /**
     * Inject admin code into bottom of page content's <body>.
     *
     * @param  string  $pageContent
     * @return string
     */
    public function addAdmCodeToPage($pageContent)
    {
        $extra = '';
        if (Auth::user() && isset(Auth::user()->id)
            && Auth::user()->hasRole('administrator|staff|brancher')) {
            $extra .= ' setTimeout(\'addSideNavItem("Edit Page", "?edit=1")\', 2000); ';
        }
        if (trim($extra) != '') {
            $extra = '<script async defer type="text/javascript"> '
                . $extra . ' </script>';
        }
        return str_replace("</body>", $extra . "\n</body>", $pageContent);
    }

    /**
     * Inject session messages and admin code into page content.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  string  $pageContent
     * @return string
     */
    public function addSessAdmCodeToPage(Request $request, $pageContent)
    {
        if (!isset($GLOBALS["SL"])) {
            $this->syncDataTrees($request, 1, 1);
        }
        return $this->addAdmCodeToPage($GLOBALS["SL"]->swapSessMsg($pageContent));
    }

    /**
     * Checks whether or not it is OK to load this tree.
     *
     * @param  int  $treeOpts
     * @return boolean
     */
    protected function okToLoadTree($treeOpts = 1)
    {
        return ($this->treeRightType($treeOpts)
            && $this->userHasTreePerms($treeOpts));
    }

    /**
     * Checks whether or not a tree is public-facing or not.
     *
     * @param  int  $treeOpts
     * @return boolean
     */
    protected function treeRightType($treeOpts = 1)
    {
        if ($this->isAdminPage) {
            return ($treeOpts%Globals::TREEOPT_ADMIN    == 0
                || $treeOpts%Globals::TREEOPT_STAFF     == 0
                || $treeOpts%Globals::TREEOPT_PARTNER   == 0
                || $treeOpts%Globals::TREEOPT_VOLUNTEER == 0);
        }
        return ($treeOpts%Globals::TREEOPT_ADMIN    > 0
            && $treeOpts%Globals::TREEOPT_STAFF     > 0
            && $treeOpts%Globals::TREEOPT_PARTNER   > 0
            && $treeOpts%Globals::TREEOPT_VOLUNTEER > 0);
    }

    /**
     * Checks whether or not this current user is has Admin permissions.
     *
     * @return boolean
     */
    protected function isAdmin()
    {
        return (Auth::user() && Auth::user()->hasRole('administrator'));
    }

    /**
     * Checks whether or not this current user is has Admin permissions.
     *
     * @return boolean
     */
    public function isUserAdmin()
    {
        return $this->isAdmin();
    }

    /**
     * Checks whether or not this current user is has Staff permissions.
     *
     * @return boolean
     */
    protected function isUserStaff()
    {
        return (Auth::user() && Auth::user()->hasRole('staff'));
    }

    /**
     * Checks whether or not this current user is has Staff or Admin permissions.
     *
     * @return boolean
     */
    protected function isStaffOrAdmin()
    {
        return (Auth::user()
            && Auth::user()->hasRole('administrator|staff'));
    }

    /**
     * Checks whether or not this current user is has Volunteer permissions.
     *
     * @return boolean
     */
    protected function isUserVolun()
    {
        return (Auth::user() && Auth::user()->hasRole('volunteer'));
    }

    /**
     * Checks whether or not this current user is has Partner permissions.
     *
     * @return boolean
     */
    protected function isUserPartn()
    {
        return (Auth::user() && Auth::user()->hasRole('partner'));
    }

    /**
     * Check for basic post-login redirects passed into the login form.
     *
     * @param  string  $redir
     * @return string
     */
    protected function isRealRedir($redir)
    {
        $nonRedirs = [
            '',
            '/',
            '/home',
            '/login',
            '/register',
            '/logout',
            '/dashboard'
        ];
        $tmp = trim(str_replace($this->domainPath, '', $redir));
        return (!in_array($tmp, $nonRedirs)) && $this->urlNotResourceFile($redir);
    }

    /**
     * That that a url is not for some system resource file.
     * This helps avoid some redirect fails.
     *
     * @param  string  $str
     * @return boolean
     */
    protected function urlNotResourceFile($str)
    {
        $types = [
            'css',
            'js',
            'png',
            'jpg',
            'jpeg',
            'gif',
            'svg',
            'woff',
            'woff2'
        ];
        $str = trim($str);
        if ($str == '') {
            return false;
        }
        $qMark = strrpos($str, '?');
        if ($qMark > 0) {
            $str = substr($str, 0, $qMark);
        }
        $dot = strrpos($str, '.');
        if ($dot > 0) {
            $sffx = substr($str, $dot+1);
            if (in_array(strtolower($sffx), $types)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Determines where to redirect freshly logged in users.
     * With the upgrades to Laravel 8 & Fortify, post-login redirection was
     * moved to the top of the most popular page loads through this function.
     *
     * @param  Illuminate\Http\Request  $request
     * @return string
     */
    public function chkLoginRedir(Request $request)
    {
        if (!Auth::user()
            || !isset(Auth::user()->id)
            || intVal(Auth::user()->id) <= 0
            || (session()->has('loginChk')
                && intVal(session()->get('loginChk')) == Auth::user()->id)) {
            return '';
        }
        $this->loadDomain();
        $redir = $this->chkBasicSessRedir($request);
        if (!$this->isRealRedir($redir)) {
            $redir = '';
        }
        if ($redir != '') {
            $this->clearSessRedirs();
            session()->put('loginChk', Auth::user()->id);
            return $redir;
        }
        $this->loadLoop($request);
        $redir = $this->custLoop->afterLogin($request);
        session()->put('loginChk', Auth::user()->id);
        return $redir;
    }

    /**
     * Clear all session memory for basic Survloop logins.
     *
     * @return void
     */
    protected function clearSessRedirs()
    {
        session()->forget('previousUrl');
        session()->forget('redir2');
        session()->forget('lastTree');
        session()->forget('lastTreeTime');
        session()->forget('loginRedir');
        session()->forget('loginRedirTime');
        session()->save();
    }

    /**
     * Check for basic post-login redirects passed into the login form.
     *
     * @param  Illuminate\Http\Request  $request
     * @return string
     */
    private function chkBasicSessRedir(Request $request)
    {
        $redir = '';
        if (session()->has('previousUrl')
            && $this->isRealRedir(session()->get('previousUrl'))) {
            $redir = trim(session()->get('previousUrl'));
            $this->afterLoginSurveyRedir($redir, $request);
        } elseif (session()->has('redir2')
            && $this->isRealRedir(session()->get('redir2'))) {
            $redir = trim(session()->get('redir2'));
        }
        if (session()->has('loginRedir')
            && $this->isRealRedir(session()->get('loginRedir'))) {
            if (session()->has('lastTreeTime')
                && session()->has('loginRedirTime')) {
                if (session()->get('lastTreeTime') < session()->get('loginRedirTime')) {
                    $redir = trim(session()->get('loginRedir'));
                }
            } else {
                $redir = trim(session()->get('loginRedir'));
            }
        }
        return $redir;
    }

    /**
     * Check if being redirected back to a survey session,
     * which needs to be associated with new user ID
     *
     * @param  string  $redir
     * @param  Illuminate\Http\Request  $request
     * @return void
     */
    protected function afterLoginSurveyRedir($redir, Request $request)
    {
        if (strpos($redir, '/u/') == 0) {
            $treeSlug = substr($redir, 3);
            $pos = strpos($treeSlug, '/');
            if ($pos > 0) {
                $treeSlug = substr($treeSlug, 0, $pos);
                $chk = SLTree::where('tree_type', 'Survey')
                    ->where('tree_slug', $treeSlug)
                    ->get();
                if ($chk->isNotEmpty()) {
                    foreach ($chk as $tree) {
                        if (session()->has('sessID' . $tree->tree_id)
                            && session()->has('coreID' . $tree->tree_id)
                            && intVal(session()->get('sessID' . $tree->tree_id)) > 0) {
                            $this->afterLoginUpdateSess($tree, $request);
                        }
                    }
                }
            }
        }
    }

    /**
     * Check if being redirected back to a survey session,
     * which needs to be associated with new user ID
     *
     * @param  string  $redir
     * @param  Illuminate\Http\Request  $request
     * @return void
     */
    protected function afterLoginUpdateSess($tree, Request $request)
    {
        $sess = SLSess::find(session()->get('sessID' . $tree->tree_id));
        if ($sess && isset($sess->sess_core_id) && intVal($sess->sess_core_id) > 0) {
            $sess->sess_user_id = Auth::user()->id;
            $sess->save();
            $this->syncDataTrees($request, $tree->tree_database, $tree->tree_id);
            eval($GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl)
                . "::find(" . $sess->sess_core_id . ")->update([ '"
                . $GLOBALS["SL"]->getCoreTblUserFld()
                . "' => " . Auth::user()->id . " ]);");
        }
    }


}