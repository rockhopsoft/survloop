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
    
    public function __construct($isAdminPage = false)
    {
        $this->isAdminPage = $isAdminPage;
        if ($isAdminPage) {
            $this->dashPrfx = '/dash';
        }
    }
    
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
    
    public function checkHttpsDomain(Request $request)
    {
        if (isset($this->domainPath) 
            && strpos($request->fullUrl(), $this->domainPath) === false) {
            $pos1 = strpos($this->domainPath, 'https://');
            $pos2 = strpos($request->fullUrl(), 
                str_replace('https://', 'http://', $this->domainPath));
            if ($pos1 !== false && $pos2 !== false) {
                header("Location: " 
                    . str_replace('http://', 'https://', $request->fullUrl())
                );
                exit;
            }
        }
        return true;
    }
    
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
    
    public function syncDataTrees(Request $request, $dbID = 1, $treeID = 1)
    {
        $this->dbID = $dbID;
        $this->treeID = $treeID;
        $GLOBALS["SL"] = new Globals($request, $dbID, $treeID, $treeID);
        $GLOBALS["SL"]->microLog();
        return true;
    }
    
    protected function userHasTreePerms($treeOpts = 1)
    {
        if ($treeOpts%Globals::TREEOPT_ADMIN == 0) {
            return $this->isUserAdmin();
        }
        if ($treeOpts%Globals::TREEOPT_STAFF == 0) {
            return ($this->isUserStaff() || $this->isUserAdmin());
        }
        if ($treeOpts%Globals::TREEOPT_PARTNER == 0) {
            return ($this->isUserPartn() 
                || $this->isUserStaff() || $this->isUserAdmin());
        }
        if ($treeOpts%Globals::TREEOPT_VOLUNTEER == 0) {
            return ($this->isUserVolun() || $this->isUserPartn() 
                || $this->isUserStaff() || $this->isUserAdmin());
        }
        return true;
    }
    
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
    
    public function hasParamEdit(Request $request)
    {
        return ($request->has('edit') 
            && intVal($request->get('edit')) == 1);
    }
    
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
    
    public function searchRun(Request $request)
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
//echo '<pre>searchTree: '; print_r($trees); echo '</pre>'; exit;
            if ($searchTree === null || !isset($searchTree->tree_opts)) {
                $trees = SLTree::where('tree_type', 'Page')
                    ->where('tree_database', $this->dbID)
                    ->get();
                $searchTree = $this->chkSearchRunTrees($trees, $perms);
            }
            if ($searchTree !== null && isset($searchTree->tree_opts)) {
                $redir = $this->getPageDashPrefix($searchTree->tree_opts) 
                    . '/' . $searchTree->tree_slug . $params;
//echo '<br />redir: ' . $redir; exit;
                return redirect($redir);
            }
        }
        return redirect('/search' . $params);
    }
    
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
        return redirect($this->domainPath . '/');
    }
    
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
        $paramTxt = str_replace($this->domainPath . '/start/' . $tree->tree_slug, '', 
            str_replace($this->domainPath . '/dashboard/start/' . $tree->tree_slug, '', 
            $request->fullUrl()));
        if (substr($paramTxt, 0, 1) == '/') {
            $paramTxt = substr($paramTxt, 1);
        }
        if (trim($paramTxt) != '' && substr($paramTxt, 0, 1) == '?') {
            $redir .= '&' . substr($paramTxt, 1);
        }
        return redirect($this->domainPath . $redir);
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
        return true;
    }
    
    public function loadPageURLrawID(Request $request, $pageSlug = '', $cid = -3, $view = '')
    {
        return $this->loadPageURL($request, $pageSlug, $cid, $view, true);
    }
    
    protected function chkGenCacheKey($type = 'page')
    {
        if (trim($this->cacheKey) == '') {
            return $this->topGenCacheKey($type);
        }
        return $this->cacheKey;
    }
    
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
    
    protected function topSaveCache($treeID = 0, $treeType = '')
    {
        $this->chkGenCacheKey();
        $cache = new GlobalsCache;
        $cid = ((isset($GLOBALS["SL"]->coreID)) ? $GLOBALS["SL"]->coreID : 0);
        $cache->putCache($this->cacheKey, $this->pageContent, $treeType, $treeID, $cid);
        return true;
    }
    
    public function addAdmCodeToPage($pageContent)
    {
        $extra = '';
        if (Auth::user() && isset(Auth::user()->id) 
            && Auth::user()->hasRole('administrator|staff|brancher')) {
            $extra .= ' setTimeout(\'addSideNavItem('
                . '"Edit Page", "?edit=1")\', 2000); ';
        }
        if (trim($extra) != '') {
            $extra = '<script async defer type="text/javascript"> ' 
                . $extra . ' </script>';
        }
        return str_replace("</body>", $extra . "\n</body>", $pageContent);
    }
    
    public function addSessAdmCodeToPage(Request $request, $pageContent)
    {
        if (!isset($GLOBALS["SL"])) {
            $this->syncDataTrees($request, 1, 1);
        }
        return $this->addAdmCodeToPage($GLOBALS["SL"]->swapSessMsg($pageContent));
    }
    
    protected function okToLoadTree($treeOpts = 1)
    {
        return ($this->treeRightType($treeOpts) 
            && $this->userHasTreePerms($treeOpts));
    }
    
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
    
    public function isUserAdmin()
    {
        return (Auth::user() && Auth::user()->hasRole('administrator'));
    }
    
    protected function isUserStaff()
    {
        return (Auth::user() && Auth::user()->hasRole('staff'));
    }
    
    protected function isStaffOrAdmin()
    {
        return (Auth::user() && Auth::user()->hasRole('administrator|staff'));
    }
    
    protected function isUserVolun()
    {
        return (Auth::user() && Auth::user()->hasRole('volunteer'));
    }
    
    protected function isUserPartn()
    {
        return (Auth::user() && Auth::user()->hasRole('partner'));
    }

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
    
}