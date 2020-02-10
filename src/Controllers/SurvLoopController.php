<?php
/**
  * SurvLoopController is the primary base class for SurvLoop, 
  * housing some key variables and functions.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.1
  */
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Cache;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Routing\Controller;
use Illuminate\Database\QueryException;
use App\Models\User;
use App\Models\SLDatabases;
use App\Models\SLDefinitions;
use App\Models\SLEmailed;
use App\Models\SLNode;
use App\Models\SLTree;
use App\Models\SLTables;
use App\Models\SLTokens;
use App\Models\SLUsersActivity;
use App\Models\SLSess;
use SurvLoop\Controllers\SurvLoopInstaller;
use SurvLoop\Controllers\Globals\GlobalsCache;
use SurvLoop\Controllers\Globals\Globals;

class SurvLoopController extends Controller
{
    public $isLoaded             = true;
    protected $custReport        = [];
    
    protected $dbID              = 1;
    protected $treeID            = 1;
    protected $treeFromURL       = false;
    
    protected $coreID            = -3;
    protected $corePublicID      = -3;
    protected $coreIDoverride    = -3;
    public $coreIncompletes      = [];
    protected $sessID            = 0;
    protected $sessInfo          = [];
    protected $sessLoops         = [];
    
    public $v                    = []; // contains data to be shares with views, and/or across [dispersed] functions
    
    protected $currPage          = '';
    protected $cacheKey          = '';
    protected $isFirstTimeOnPage = false;
    protected $survInitRun       = false;
    
    protected $extraTree         = [];
    public    $searcher          = null;
    
    protected function loadUserVars()
    {
        $this->v["user"]       = Auth::user();
        $this->v["uID"]        = (($this->v["user"] 
            && isset($this->v["user"]->id)) ? $this->v["user"]->id : 0);
        $this->v["isAdmin"]    = ($this->v["user"] 
            && $this->v["user"]->hasRole('administrator'));
        $this->v["isVolun"]    = ($this->v["user"] 
            && $this->v["user"]->hasRole('volunteer'));
        $this->initPowerUser();
        return true;
    }
    
    public function survLoopInit(Request $request, $currPage = '', $runExtra = true)
    {
        if (!$this->survInitRun || !isset($this->v["uID"])) {
            $this->survInitRun = true;
            $this->loadUserVars();
            $this->v["isAll"]     = $request->has('all');
            $this->v["isAlt"]     = $request->has('alt');
            $this->v["isPrint"]   = $request->has('print');
            $this->v["isExcel"]   = $request->has('excel');
            $this->v["view"]      = (($request->has('view')) 
                ? trim($request->get('view')) : '');
            $this->v["isDash"]    = false;
            $this->v["exportDir"] = 'survloop';
            $this->v["content"]   = '';
            $this->v["isOwner"]   = false;
            if (!isset($this->v["currPage"])) {
                $this->v["currPage"] = ['', ''];
            }
            if (trim($this->v["currPage"][0]) == '') {
                $this->v["currPage"][0] = $currPage;
            }
            if (trim($this->v["currPage"][0]) == '') {
                $this->v["currPage"][0] = $_SERVER["REQUEST_URI"];
                if (strpos($this->v["currPage"][0], '?') !== false) {
                    $pos = strpos($this->v["currPage"][0], '?');
                    $this->v["currPage"][0] = substr($this->v["currPage"][0], 0, $pos);
                }
            }
            
            $this->loadSlSess();
            if ($request->has('sessmsg') && trim($request->get('sessmsg')) != '') {
                session()->put('sessMsg', trim($request->get('sessmsg')));
                session()->save();
            }
            
            if (!isset($this->v["currState"])) {
                $this->v["currState"] = '';
            }
            if (!isset($this->v["yourUserInfo"])) {
                $this->v["yourUserInfo"] = [];
            }
            if (!isset($this->v["yourContact"])) {
                $this->v["yourContact"]  = [];
            }
            
            $this->loadNavMenu();
            $this->loadDbLookups($request);
            
            if ($request->has('refresh') && trim($request->get('refresh')) != '') {
                $this->checkSystemInit();
            }
            if (isset($GLOBALS["slRunUpdates"]) && $GLOBALS["slRunUpdates"]) {
                $this->v["pastUpDef"] = $this->v["pastUpArr"] 
                    = $this->v["updateList"] = [];
            }
            
            if ($this->coreIDoverride > 0) {
                $this->loadAllSessData();
            }
            
            if ($runExtra) {
                $this->initExtra($request);
                $this->loadSysSettings();
                $this->initCustViews();
            }
            $this->genCacheKey();
        }
        return true;
    }
    
    protected function loadSlSess()
    {
        $slSess = null;
        if (isset($this->v["uID"])) {
            $slSess = SLSess::where('sess_user_id', $this->v["uID"])
                ->where('sess_tree', 0)
                ->where('sess_is_active', 1)
                ->first();
            if (!session()->has('slSessID') 
                || intVal(session()->get('slSessID')) == 0) {
                if (!$slSess || !isset($slSess->sess_id)) {
                    $slSess = new SLSess;
                    $slSess->sess_user_id = $this->v["uID"];
                    $slSess->sess_tree   = 0;
                    $slSess->save();
                }
                session()->put('slSessID', $slSess->sess_id);
                session()->save();
            } elseif ($slSess && isset($slSess->sess_id) 
                && !isset($slSess->sess_user_id) && $this->v["uID"] > 0) {
                $slSess->sess_user_id = $this->v["uID"];
                $slSess->save();
            }
        }
        return true;
    }
    
    protected function loadSysSettings()
    {
        $settings = SLDefinitions::where('def_set', 'Custom Settings')
            ->orderBy('def_order', 'asc')
            ->get();
        $this->v["settings"] = [];
        if ($settings->isNotEmpty()) {
            foreach ($settings as $s) {
                $this->v["settings"][$s->def_subset] = $s->def_value;
            }
        }
        return true;
    }
    
    protected function loadTreeURL($treeSlug = '')
    {
        if (trim($treeSlug) != '' 
            && $treeSlug != $GLOBALS["SL"]->treeRow->tree_slug) {
            $urlTree = SLTree::where('tree_slug', $treeSlug)
                ->first();
            if ($urlTree) {
                $this->dbID = $urlTree->tree_database;
                $this->treeID = $urlTree->tree_id;
                $this->treeFromURL = true;
            }
        }
        return true;
    }
    
    protected function loadDbLookups(Request $request)
    {
        if (!isset($GLOBALS["SL"]) || !isset($GLOBALS["SL"]->sysOpts["cust-abbr"])) {
            if (!$this->treeFromURL) {
                if (!isset($this->v["user"])) {
                    $this->v["user"] = Auth::user();
                    $this->v["uID"]  = (($this->v["user"] && isset($this->v["user"]->id)) 
                        ? $this->v["user"]->id : 0);
                }
                if ($this->v["uID"] > 0) {
                    $last = SLUsersActivity::where('user_act_user', '=', $this->v["uID"])
                        ->where('user_act_val', 'LIKE', '%;%')
                        ->where(function ($query) {
                            $query->where('user_act_curr_page', 'LIKE', '/fresh/database%')
                                ->orWhere('user_act_curr_page', 'LIKE', '/fresh/survey%')
                                ->orWhere('user_act_curr_page', 'LIKE', '/dashboard/tree/switch%')
                                ->orWhere('user_act_curr_page', 'LIKE', '/dashboard/tree/new%')
                                ->orWhere('user_act_curr_page', 'LIKE', '/dashboard/db/switch%')
                                ->orWhere('user_act_curr_page', 'LIKE', '/dashboard/db/new%');
                        })
                        ->orderBy('created_at', 'desc')
                        ->first();
                    if ($last && isset($last->user_act_val)) {
                        list($this->dbID, $this->treeID) = explode(';', $last->user_act_val);
                        $this->dbID = intVal($this->dbID);
                        $this->treeID = intVal($this->treeID);
                    }
                }
            }
            $GLOBALS["SL"] = new Globals($request, $this->dbID, $this->treeID);
            $GLOBALS["SL"]->microLog();
        }
        return true;
    }
    
    protected function loadDbFromNode(Request $request, $nID)
    {
        $node = SLNode::find($nID);
        if ($node && isset($node->node_tree)) {
            $tree = SLTree::find($node->node_tree);
            if ($tree && isset($tree->tree_database)) {
                $this->treeID = $tree->tree_id;
                $this->dbID = $tree->tree_database;
                $GLOBALS["SL"] = new Globals(
                    $request, 
                    $this->dbID, 
                    $this->treeID, 
                    $this->treeID
                );
            }
        }
        return true;
    }
    
    // Check For Basic System Setup First
    public function checkSystemInit()
    {
        if (!session()->has('chkSysInit') || $GLOBALS["SL"]->REQ->has('refresh')) {
            if (!$GLOBALS["SL"]->REQ->has('cssLoaded')
                && !file_exists('../storage/app/sys/sys2.min.js')) {
                echo '<div class="disNon"><iframe src="/css-reload" ></iframe></div>
                    <script type="text/javascript"> 
                    setTimeout("window.location=\'?cssLoaded=1\'", 2000); 
                    </script>';
                exit;
            }
            $sysChk = User::select('id')
                ->get();
            if ($sysChk->isEmpty()) {
                return $this->freshUser($GLOBALS["SL"]->REQ);
            }
            $sysChk = SLDatabases::select('db_id')
                ->where('db_user', '>', 0)
                ->get();
            if ($sysChk->isEmpty()) {
                return $this->redir('/fresh/database', true);
            }
            if (!$this->chkHasTreeOne()) {
                return $this->redir('/fresh/survey', true);
            }
            $survInst = new SurvLoopInstaller;
            $survInst->checkSysInit();
            session()->put('chkSysInit', 1);
            session()->save();
        }
        return '';
    }

    protected function chkHasTreeOne($dbID = 1)
    {
        $sysChk = SLTree::find(1);
        return ($sysChk && isset($sysChk->tree_id));
    }
    
    public function getCoreID()
    {
        return $this->coreID;
    }
    
    protected function setCurrPage($currPage = '')
    {
        $this->v["currPage"][0] = $currPage;
        return true;
    }
    
    public function getCurrPage()
    {
        return ((isset($this->v["currPage"][0])) ? $this->v["currPage"][0] : '/');
    }
    
    /**
     * Initializing a bunch of things which are not [yet] automatically 
     * set by the SurvLoop and its GUIs.
     *
     * @param  Illuminate\Http\Request  $request
     * @return boolean
     */
    protected function initExtra(Request $request)
    {
        return true;
    }
    
    protected function extraNavItems()
    {
        return '';
    }
    
    public function initCustViews()
    {
        $chk = SLDefinitions::where('def_database', 1)
            ->where('def_set', 'Blurbs')
            ->where('def_subset', 'Footer')
            ->first();
        if ($chk && isset($chk->def_description) 
            && trim($chk->def_description) != '') {
            $GLOBALS["SL"]->sysOpts["footer-master"] = $chk->def_description;
        }
        return true;
    }
    
    protected function getHighestGroupLabel()
    {
        if (Auth::user() && Auth::user()->id > 0) {
            if (Auth::user()->hasRole('administrator')) {
                return 'admin';
            } elseif (Auth::user()->hasRole('staff|databaser|brancher')) {
                return 'staff';
            } elseif (Auth::user()->hasRole('partner')) {
                return 'partner';
            } elseif (Auth::user()->hasRole('volunteer')) {
                return 'volun';
            } else {
                return 'user';
            }
        }
        return 'visitor';
    }
    
    protected function genCacheKey($baseOverride = '')
    {
        $this->cacheKey = str_replace('/', '.', $this->v["currPage"][0]);
        if ($baseOverride != '') {
            $this->cacheKey = $baseOverride;
        }
        $this->cacheKey .= '.db' . $GLOBALS["SL"]->dbID . '.tree'
            . $GLOBALS["SL"]->treeID . '.' . $this->getHighestGroupLabel();
        if ($this->v["isPrint"]) {
            $this->cacheKey .= '.print';
        }
        if ($this->v["isAll"]) {
            $this->cacheKey .= '.all';
        }
        if ($this->v["isAlt"]) {
            $this->cacheKey .= '.alt';
        }
        if ($this->v["isExcel"]) {
            $this->cacheKey .= '.excel';
        }
        return $this->cacheKey;
    }
    
    protected function checkCache($baseOverride = '')
    {
        if ($baseOverride != '') {
            $this->genCacheKey($baseOverride);
        }
        $cache = new GlobalsCache;
        if ($GLOBALS["SL"]->REQ->has('refresh')) {
            $cache->forgetCache($this->cacheKey);
        }
        $ret = $cache->chkCache($this->cacheKey);
        if ($ret != '') {
            $this->v["content"] = $ret;
            $GLOBALS["SL"]->x["pageCacheLoaded"] = true;
            return true;
        }
        $GLOBALS["SL"]->x["pageCacheLoaded"] = false;
        return false;
    }
    
    protected function saveCache()
    {
        $cache = new GlobalsCache;
        $cache->putCache($this->cacheKey, $this->v["content"]);
        return true;
    }
    
    // Is this the first time this user has visited the current page?
    protected function isPageFirstTime($currPage = '')
    {
        if (trim($currPage) == '') {
            $currPage = $this->v["currPage"][0];
        }
        $chk = SLUsersActivity::where('user_act_user', Auth::user()->id)
            ->where('user_act_curr_page', 'LIKE', '%' . $currPage)
            ->get();
        if ($chk->isNotEmpty()) {
            return false;
        }
        return true;
    }
    
    protected function logPageVisit($currPage = '', $val = '')
    {
        $log = new SLUsersActivity;
        $log->user_act_user = Auth::user()->id;
        $log->user_act_curr_page = $_SERVER["REQUEST_URI"];
        if (strlen($log->user_act_curr_page) > 255) {
            $log->user_act_curr_page = substr($log->user_act_curr_page, 0, 255);
        }
        $log->user_act_val = $val;
        $log->save();
        return true;
    }
    
    public function freshUser(Request $request)
    {
        $this->survLoopInit($request, '/fresh/creator');
        $GLOBALS["SL"]->sysOpts["signup-instruct"] = '<h2 class="mT5 mB0">Create Admin Account</h2>';
        $content = '<center><div class="treeWrapForm mT20 mBn20">
            <h1 class="slBlueDark">' . ((isset($GLOBALS["SL"]->sysOpts["site-name"])) 
                    ? $GLOBALS["SL"]->sysOpts["site-name"] : 'SurvLoop') 
                . ' Installed!</h1><h4>All Out Data Are Belong...</h4>
            <p>Please create the first admin super user account.</p></div></center>';
        if (isset($GLOBALS["SL"]->sysOpts["app-url"])) {
            if ($GLOBALS["SL"]->sysOpts["app-url"] != 'http://' . $_SERVER["HTTP_HOST"]
                && $GLOBALS["SL"]->sysOpts["app-url"] != 'https://' . $_SERVER["HTTP_HOST"]) {
                SLDefinitions::where('def_database', 1)
                    ->where('def_set', 'System Settings')
                    ->whereIn('def_subset', ['app-url', 'logo-url'])
                    ->update([ "def_description" =>  'http://' . $_SERVER["HTTP_HOST"] ]);
            }

        }
        return view(
            'vendor.survloop.auth.register',
            [ "content" => $content ]
        )->render();
    }
    
    protected function getRecsOneFilt($tblMdl = '', $filtFld = '', $filtIn = [], $idFld = '')
    {
        $eval = "\$recs = App\\Models\\" . $tblMdl 
            . "::whereIn('" . $filtFld . "', [ '" . implode("', '", $filtIn) . "' ])"
            . "->orderBy('created_at', 'desc')->get();";
        eval($eval);
        //echo $eval . '<br />';
        $this->v["recs"] = $recs;
        return true;
    }
    
    protected function getRecFiltTots($tblMdl = '', $filtFld = '', $filts = [], $idFld = '')
    {
        $this->v["recTots"] = [];
        if (sizeof($filts) > 0) {
            foreach ($filts as $filt) {
                eval("\$totChk = App\\Models\\" . $tblMdl 
                    . "::where('" . $filtFld . "', '" . $filt . "')"
                    . "->select('" . $idFld . "')"
                    . "->get();");
                $this->v["recTots"][$filt] = (($totChk->isNotEmpty()) ? $totChk->count() : 0);
            }
        }
        return true;
    }
    
    protected function loadCustLoop(Request $request, $treeID = -3, $dbID = -3)
    {
        if ($treeID <= 0) {
            $treeID = $this->treeID;
        }
        if ($dbID <= 0) {
            $dbID = $this->dbID;
        }
        if (isset($GLOBALS["SL"]->sysOpts["cust-abbr"]) 
            && $GLOBALS["SL"]->sysOpts["cust-abbr"] != 'SurvLoop') {
            $eval = "\$this->custReport = new " . $GLOBALS["SL"]->sysOpts["cust-abbr"] 
                . "\\Controllers\\" . $GLOBALS["SL"]->sysOpts["cust-abbr"] 
                . "(\$request, -3, \$dbID, \$treeID);";
            eval($eval);
        } else {
            $this->custReport = new TreeSurvForm($request, -3, $dbID, $treeID);
        }
        $currPage = '';
        if (isset($this->v["currPage"]) 
            && sizeof($this->v["currPage"]) > 0) {
            $currPage = $this->v["currPage"][0];
        }
        $this->custReport->survLoopInit($request, $currPage);
        return true;
    }
    
    public function initSearcher()
    {
        if ($this->searcher === null) {
            $this->loadCustSearcher();
            $this->copyUserToSearcher();
        }
        return true;
    }
    
    public function copyUserToSearcher()
    {
        if (isset($this->v["uID"])) {
            $this->searcher->v["uID"] = $this->v["uID"];
            $this->searcher->v["user"] = $this->v["user"];
            if (isset($this->v["usrInfo"])) {
                $this->searcher->v["usrInfo"] = $this->v["usrInfo"];
            }
        }
        return true;
    }
    
    public function copyUserToGlobals()
    {
        if (isset($this->v["uID"]) && isset($GLOBALS["SL"])) {
            $GLOBALS["SL"]->x["uID"] = $this->v["uID"];
            $GLOBALS["SL"]->x["user"] = $this->v["user"];
            if (isset($this->v["usrInfo"])) {
                $GLOBALS["SL"]->x["usrInfo"] = $this->v["usrInfo"];
            }
        }
        return true;
    }
    
    protected function loadCustSearcher()
    {
        $loaded = false;
        if (isset($GLOBALS["SL"]->sysOpts["cust-abbr"]) 
            && $GLOBALS["SL"]->sysOpts["cust-abbr"] != 'SurvLoop') {
            $custClass = $GLOBALS["SL"]->sysOpts["cust-abbr"] . "\\Controllers\\" 
                . $GLOBALS["SL"]->sysOpts["cust-abbr"] . "Searcher";
            if (class_exists($custClass)) {
                eval("\$this->searcher = new ". $custClass 
                    . "(" . $this->treeID . ");");
                $loaded = true;
            }
        }
        if (!$loaded) {
            $this->searcher = new Searcher($this->treeID);
        }
        $this->initSearcherXtra();
        return true;
    }
    
    public function initSearcherXtra()
    {
        return true;
    }
    
    public function getAllPublicCoreIDs($coreTbl = '')
    {
        $this->initSearcher();
        return $this->searcher->getAllPublicCoreIDs($coreTbl);
    }
    
    public function searchResultsXtra($treeID = -3)
    {
        $this->initSearcher();
        return $this->searcher->searchResultsXtra($treeID);
    }
    
    protected function copyAdmMenuToReport()
    {
        foreach (["admMenu", "belowAdmMenu"] as $copyVar) { // "uID", "user", "profileUser", "isOwner"
            if (isset($this->v[$copyVar])) {
                $this->custReport->v[$copyVar] = $this->v[$copyVar];
                unset($this->v[$copyVar]); // might as well free up this memory if we're passing to the TreeSurv
            }
        }
        return true;
    }
    
    protected function switchDatabase(Request $request, $dbID = -3, $currPage = '')
    {
        if ($dbID > 0) {
            $dbRow = SLDatabases::where('db_id', $dbID)
                //->whereIn('db_user', [ 0, $this->v["uID"] ])
                ->first();
            if ($dbRow && $dbRow->db_id) {
                $treeRow = SLTree::where('tree_database', $dbID)
                    ->where('tree_type', 'Survey')
                    ->first();
                if ($treeRow && isset($treeRow->tree_id)) {
                    $treeID = $treeRow->tree_id;
                    $GLOBALS["SL"] = new Globals($request, $dbID, $treeID, $treeID);
                    $this->logPageVisit($currPage, $dbID . ';' . $treeID);
                }
            }
            return true;
        }
        return false;
    }
    
    protected function switchTree($treeID = -3, $currPage = '', Request $request)
    {
        if ($treeID > 0) {
            $treeRow = SLTree::where('tree_id', $treeID)
                //->where('tree_database', $GLOBALS["SL"]->dbID)
                ->first();
            if ($treeRow && isset($treeRow->tree_id)) {
                $db = $treeRow->tree_database;
                $GLOBALS["SL"] = new Globals($request, $db, $treeID, $treeID);
                $dbTr = $treeRow->tree_database . ';' . $treeID;
                $this->logPageVisit($currPage, $dbTr);
            }
            return true;
        }
        return false;
    }
    
    
    public function redir($path, $js = false)
    {
        $redir = $path;
        if (isset($GLOBALS["SL"]->sysOpts["app-url"]) 
            && strpos($path, $GLOBALS["SL"]->sysOpts["app-url"]) != 0) {
            $redir = $GLOBALS["SL"]->sysOpts["app-url"] . $path;
        } else {
            $appUrl = SLDefinitions::where('def_database', 1)
                ->where('def_set', 'System Settings')
                ->where('def_subset', 'app-url')
                ->first();
            if ($appUrl && isset($appUrl->def_description) 
                && strpos($path, $appUrl->def_description) != 0) {
                $redir = $appUrl->def_description . $path;
            }
        }
        if (!$js) {
            return redirect($redir);
        } else {
            echo '<script type="text/javascript"> '
                . 'setTimeout("top.location.href=\'' . $redir . '\'", 10); '
                . '</script>';
            exit;
        }
    }
    
    protected function setNotif($msg = '', $type = 'info')
    {
        session()->put('sessMsg',     $msg);
        session()->put('sessMsgType', 'alert-' . $type);
        session()->save();
        return true;
    }
    
    
    // this should really be done using migrations
    protected function survSysChecks()
    {
        if (!session()->has('survSysChecks') || $GLOBALS["SL"]->REQ->has('refresh')) {
            $GLOBALS["SL"]->clearOldDynascript();
            session()->put('survSysChecks', 1);
            session()->save();
        }
        return true;
    }
    
    protected function loadNavMenu()
    {
        $settings = SLDefinitions::where('def_set', 'Menu Settings')
            ->where('def_subset', 'main-navigation')
            ->where('def_database', 1)
            ->orderBy('def_order', 'asc')
            ->get();
        $this->v["navMenu"] = [];
        if ($settings->isNotEmpty()) {
            foreach ($settings as $s) {
                $this->v["navMenu"][] = [
                    $s->def_value,
                    $s->def_description
                ];
            }
        }
        return true;
    }
    
    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    
    protected function createToken($type, $treeID = -3, $coreID = -3, $userID = -3)
    {
        if ($userID <= 0 && Auth::user() && isset(Auth::user()->id)) {
            $userID = Auth::user()->id;
        }
        if ($type == 'Confirm Email') {
            if ($userID > 0) {
                $tokRow = SLTokens::where('tok_type', $type)
                    ->where('tok_user_id', $userID)
                    ->first();
                if (!$tokRow || !isset($tokRow->tok_tok_token)) {
                    $tokRow = new SLTokens;
                    $tokRow->tok_type = $type;
                    $tokRow->tok_user_id = $userID;
                }
                $tokRow->tok_tok_token = $this->generateRandomString(50);
                $tokRow->save();
                return $tokRow->tok_tok_token;
            }
        } elseif ($type == 'Sensitive') {
            $token = $this->chkBasicToken($type, $treeID, $coreID, $userID);
            if (trim($token) != '') {
                return $token;
            }
            $tokRow = $this->makeBasicToken($type, $treeID, $coreID, $userID);
            return $tokRow->tok_tok_token;
        } elseif ($type == 'MFA') {
            $token = $this->chkBasicToken($type, $treeID, $coreID, $userID);
            if (trim($token) != '') {
                $tokRow = SLTokens::where('tok_type', $type)
                    ->where('tok_tree_id', $treeID)
                    ->where('tok_core_id', $coreID)
                    ->where('tok_user_id', $userID)
                    ->first();
                if ($tokRow && isset($tokRow->tok_tok_token)) {
                    $tokRow->tok_tok_token = $this->genTokenStr($type);
                    $tokRow->save();
                    return $tokRow->tok_tok_token;
                }
            }
            $tokRow = $this->makeBasicToken($type, $treeID, $coreID, $userID);
            return $tokRow->tok_tok_token;
        }
        return '';
    }
    
    protected function chkBasicToken($type, $treeID = -3, $coreID = -3, $userID = -3)
    {
        $tokRow = SLTokens::where('tok_type', $type)
            ->where('tok_tree_id', $treeID)
            ->where('tok_core_id', $coreID)
            ->where('tok_user_id', $userID)
            ->first();
        if ($tokRow && isset($tokRow->tok_tok_token)) {
            return $tokRow->tok_tok_token;
        }
        return '';
    }
    
    protected function makeBasicToken($type, $treeID = -3, $coreID = -3, $userID = -3, $strlen = 50, $delim = '-')
    {
        $tokRow = new SLTokens;
        $tokRow->tok_type = $type;
        $tokRow->tok_tree_id = $treeID;
        $tokRow->tok_core_id = $coreID;
        $tokRow->tok_user_id = $userID;
        $tokRow->tok_tok_token = $this->genTokenStr($type);
        $tokRow->save();
        return $tokRow;
    }
    
    protected function genTokenStr($type, $strlen = 50, $delim = '-')
    {
        if ($type == 'MFA') {
            $strlen = 12;
        }
        $token = $this->generateRandomString($strlen);
        if ($type == 'MFA') {
            $token = substr($token, 0, floor(strlen($token)/3)) . $delim 
                . substr($token, floor(strlen($token)/3), floor(strlen($token)/3)) . $delim
                . substr($token, floor(strlen($token)*2/3));
        }
        return $token;
    }
    
    public function tokenExpireDate($type = 'Confirm Email')
    {
        $hrs = 24*7;
        if ($type == 'Confirm Email') {
            $hrs = 24*28;
        }
        return date(
            "Y-m-d H:i:s", 
            mktime(intVal(date('H'))-$hrs, date('i'), date('s'), 
                date('m'), date('d'), date('Y'))
        );
    }
    
    public function sendEmail($emaContent, $emaSubject, $emaTo = [], $emaCC = [], $emaBCC = [], $repTo = [])
    {
        if (!isset($repTo[0]) || trim($repTo[0]) == '') {
            $repTo[0] = 'info@' . strtolower($GLOBALS["SL"]->getParentDomain());
        }
        if (!isset($repTo[1]) || trim($repTo[1]) == '') {
            $repTo[1] = $GLOBALS["SL"]->sysOpts["site-name"];
        }
        $mail = "Illuminate\\Support\\Facades\\Mail::send('vendor.survloop.emails.master', [
            'emaSubj'    => \$emaSubject,
            'emaContent' => \$emaContent,
            'cssColors'  => \$GLOBALS['SL']->getCssColorsEmail()
            ], function (\$m) { \$m->subject('" . str_replace("'", "\\'", $emaSubject) . "')";
                if (sizeof($emaTo) > 0) {
                    foreach ($emaTo as $i => $eTo) {
                        $mail .= "->to('" . $eTo[0] . "'" . ((trim($eTo[1]) != '') 
                            ? ", '" . str_replace("'", "\\'", $eTo[1]) . "'" : "") . ")";
                    }
                }
                if (sizeof($emaCC) > 0) {
                    foreach ($emaCC as $eTo) {
                        $mail .= "->cc('" . $eTo[0] . "'" . ((trim($eTo[1]) != '') 
                            ? ", '" . str_replace("'", "\\'", $eTo[1]) . "'" : "") . ")";
                    }
                }
                if (sizeof($emaBCC) > 0) {
                    foreach ($emaBCC as $eTo) {
                        $mail .= "->bcc('" . $eTo[0] . "'" . ((trim($eTo[1]) != '') 
                            ? ", '" . str_replace("'", "\\'", $eTo[1]) . "'" : "") . ")";
                    }
                }
        $mail .= "->replyTo('" . $repTo[0] . "'" . ((trim($repTo[1]) != '') 
            ? ", '" . str_replace("'", "\\'", $repTo[1]) . "'" : "") . "); });";
        if ($GLOBALS["SL"]->isHomestead()) {
            echo '<br /><br /><br /><div class="container"><h2>' 
                . $emaSubject . '</h2>' . $emaContent 
                . '<hr><hr></div><pre>' . $mail . '</pre><hr><br />';
            return true;
        }
        eval($mail);
        return true;
    }
    
    // This function should be migrated to sendEmail() ...
    protected function sendNewEmailSimple($body, $subject, $emailTo = '', 
        $emailID = -3, $treeID = -3, $coreID = -3, $userTo = -3)
    {
        $emaTo = [$this->getEmailTo($emailTo)];
        $this->sendEmail($body, $subject, $emaTo);
        return $this->logEmailSent($body, $subject, $emailTo, $emailID, $treeID, $coreID, $userTo);
    }
    
    protected function sendNewEmailFromCurrUser($body, $subject, $emailTo = '', 
        $emailID = -3, $treeID = -3, $coreID = -3, $userTo = -3, $cc = '', $bcc = '')
    {
        $emaTo = [$this->getEmailTo($emailTo)];
        $emaCC = $emaBCC = [];
        $emaFrom = $this->getEmailFromCurrUser();
        $emaCC[] = $this->getEmailTo($emaFrom);
        if (trim($cc) != '') {
            $emaCC[] = $this->getEmailTo($cc);
        }
        if (trim($bcc) != '') {
            $emaBCC[] = $this->getEmailTo($bcc);
        }
        $this->sendEmail($body, $subject, $emaTo, $emaCC, $emaBCC, $emaFrom);
        return $this->logEmailSent($body, $subject, $emailTo, $emailID, $treeID, $coreID, $userTo);
    }
    
    protected function getEmailTo($emailTo = '')
    {
        $emaTo = [];
        if (is_array($emailTo)) {
            $emaTo = $emailTo;
            $emailTo = $emailTo[1] . ' <' . $emailTo[0] . '>';
        } elseif (trim($emailTo) != '') {
            $emaUsr = User::where('email', $emailTo)->first();
            if ($emaUsr && isset($emaUsr->name)) {
                $emaTo = [ $emailTo, $emaUsr->name ];
            } else {
                $emaTo = [ $emailTo, '' ];
            }
        }
        return $emaTo;
    }
    
    protected function getEmailFromCurrUser()
    {
        $emaFrom = [];
        if (Auth::user()) {
            $emaFrom = [
                ((isset(Auth::user()->email)) ? Auth::user()->email : ''),
                ((isset(Auth::user()->name)) ? Auth::user()->name : '')
            ];
        }
        return $emaFrom;
    }
    
    protected function logEmailSent($body, $subject, $emailTo = '', 
        $emailID = -3, $treeID = -3, $coreID = -3, $userTo = -3)
    {
        $emailRec = new SLEmailed;
        $emailRec->emailed_email_id  = (($emailID > 0) ? $emailID : 0);
        $emailRec->emailed_tree      = (($treeID > 0)  ? $treeID  : 0);
        $emailRec->emailed_rec_id    = (($coreID > 0)  ? $coreID  : 0);
        $emailRec->emailed_to        = trim($emailTo);
        $emailRec->emailed_to_user   = (($userTo > 0)  ? $userTo  : 0);
        $emailRec->emailed_from_user = ((Auth::user() && isset(Auth::user()->id))
            ? Auth::user()->id : 0);
        $emailRec->emailed_Subject   = $subject;
        $emailRec->emailed_Body      = $body;
        $emailRec->save();
        return true;
    }
    
    function checkFolder($fold)
    {
        $currFold = '';
        $limit = 0;
        while (!is_dir($currFold . 'storage/app') && $limit < 9) {
            $currFold .= '../';
            $limit++;
        }
        $currFold .= 'storage/app';
        $fold = str_replace('../storage/app/', '', $fold);
        $subs = [$fold];
        if (strpos($fold, '/') !== false) {
            $subs = explode('/', $fold);
        }
        if (sizeof($subs) > 0) {
            foreach ($subs as $sub) {
                if (trim($sub) != '') {
                    $currFold .= '/' . $sub;
                    if (!is_dir($currFold)) {
                        mkdir($currFold);
                    }
                }
            }
        }
        return true;
    }
    
    public function logAdd($log, $content)
    {
        $this->checkFolder('../storage/app/log');
        $fold = '../storage/app/';
        $file = 'log/' . $log . '.html';
        $uID = ((Auth::user() && isset(Auth::user()->id)) ? Auth::user()->id : 0);
        if (!isset($GLOBALS["SL"])) {
            $GLOBALS["SL"] = new Globals(new Request, $this->dbID, $this->treeID);
        }
        $content = '<p>' . date("Y-m-d H:i:s") . ' <b>U#' . $uID . '</b> - ' . $content 
            . '<br /><span class="slGrey fPerc80">' . $GLOBALS["SL"]->hashIP()
            . '</span></p>';
        if (!file_exists($fold . $file)) {
            Storage::disk('local')->put($file, ' ');
        }
        Storage::disk('local')->prepend($file, $content);
        return true;
    }
    
    public function logLoad($log)
    {
        $file = '../storage/app/log/' . $log . '.html';
        if ($this->v["isAdmin"] && file_exists($file)) {
            return file_get_contents($file);
        }
        return '';
    }
    
    public function logPreview($log)
    {
        $ret = '';
        $all = $this->logLoad($log);
        if (trim($all) != '' && strpos($all, '</p>') !== false) {
            $logs = $GLOBALS["SL"]->mexplode('</p>', $all);
            for ($i = 0; ($i < 100 && $i < sizeof($logs)); $i++) {
                $ret .= $logs[$i] . '</p><div class="p20"></div>';
            }
        }
        return $ret;
    }
    
    public function logAddSessStuff($type)
    {
        $log = '';
        $this->logAdd('session-stuff', $log);
        return true;
    }
    
    public function getCoreDef($set, $subset, $dbID = 1)
    {
        $def = SLDefinitions::where('def_database', $dbID)
            ->where('def_set',    '=', $set)
            ->where('def_subset', '=', $subset)
            ->first();
        if (!$def || !isset($def->def_id)) {
            $def = new SLDefinitions;
            $def->def_database = $dbID;
            $def->def_set      = $set;
            $def->def_subset   = $subset;
            $def->save();
        }
        return $def;
    }
    
    public function loadSysUpdates()
    {
        $this->v["pastUpDef"] = $this->getCoreDef('System Checks', 'system-updates');
        $desc = $this->v["pastUpDef"]->def_description;
        $this->v["pastUpArr"] = $GLOBALS["SL"]->mexplode(';;', $desc);
        return true;
    }
    
    protected function addSysUpdate($updateID)
    {
        $done = in_array($updateID[0], $this->v["pastUpArr"]);
        $this->v["updateList"][] = [
            $updateID[0], 
            $done, 
            $updateID[1] 
        ];
        return $done;
    }
    
    protected function sysUpdatesCust($apply = false)
    {
        return '';
    }
    
    public function printUserLnk($uID = -3)
    {
        if ($uID > 0) {
            $user = User::find($uID);
            if ($user && isset($user->id)) {
                return $user->printUsername();
            }
            return 'User #' . $uID;
        }
        return '';
    }
    
    public function tblsInPackage()
    {
        if ($this->dbID == 3) {
            return ['zip_ashrae', 'zips'];
        }
        $ret = $this->tblsInPackageCustom();
        if (sizeof($ret) > 0) {
            return $ret;
        }
        $chk = SLTables::where('tbl_database', $this->dbID)
            ->whereRaw("tbl_opts%5 LIKE 0")
            ->select('tbl_name')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $tbl) {
                $ret[] = $tbl->tbl_name;
            }
        }
        return $ret;
    }
    
    public function tblsInPackageCustom()
    {
        return [];
    }
    
    /**
     * Load additional data related to users who are logged in.
     *
     * @param   int  $uID
     * @return  array
     */
    public function initPowerUser($uID = -3)
    {
        return true;
    }
    
}
