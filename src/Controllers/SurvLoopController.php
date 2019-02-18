<?php
/**
  * SurvLoopController is the primary base class for SurvLoop, 
  * housing some key variables and functions.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
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
use SurvLoop\Controllers\Globals;

class SurvLoopController extends Controller
{
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
        $this->v["uID"]        = (($this->v["user"] && isset($this->v["user"]->id)) ? $this->v["user"]->id : 0);
        $this->v["isAdmin"]    = ($this->v["user"] && $this->v["user"]->hasRole('administrator'));
        $this->v["isVolun"]    = ($this->v["user"] && $this->v["user"]->hasRole('volunteer'));
        $this->initPowerUser();
        return true;
    }
    
    public function survLoopInit(Request $request, $currPage = '', $runExtra = true)
    {
        if (!$this->survInitRun) {
            $this->survInitRun = true;
            $this->loadUserVars();
            $this->v["isAll"]      = $request->has('all');
            $this->v["isAlt"]      = $request->has('alt');
            $this->v["isPrint"]    = $request->has('print');
            $this->v["isExcel"]    = $request->has('excel');
            $this->v["view"]       = (($request->has('view')) ? trim($request->get('view')) : '');
            $this->v["isDash"]     = false;
            $this->v["exportDir"]  = 'survloop';
            $this->v["content"]    = '';
            $this->v["isOwner"]    = false;
            if (!isset($GLOBALS["SL"]->x["dataPerms"])) {
                $GLOBALS["SL"]->x["dataPerms"] = 'public';
            }
            if (!isset($this->v["currPage"])) {
                $this->v["currPage"] = ['', ''];
            }
            if (trim($this->v["currPage"][0]) == '') {
                $this->v["currPage"][0] = $currPage;
            }
            if (trim($this->v["currPage"][0]) == '') {
                $this->v["currPage"][0] = $_SERVER["REQUEST_URI"];
                if (strpos($this->v["currPage"][0], '?') !== false) {
                    $this->v["currPage"][0] = substr($this->v["currPage"][0], 0, strpos($this->v["currPage"][0], '?'));
                }
            }
            
            $this->loadSlSess();
            if ($request->has('sessmsg') && trim($request->get('sessmsg')) != '') {
                session()->put('sessMsg', trim($request->get('sessmsg')));
            }
            
            if (!isset($this->v["currState"])) {
                $this->v["currState"]    = '';
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
                $this->v["pastUpDef"] = $this->v["pastUpArr"] = $this->v["updateList"] = [];
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
        $slSess = SLSess::where('SessUserID', $this->v["uID"])
            ->where('SessTree', 0)
            ->first();
        if (!session()->has('slSessID') || intVal(session()->get('slSessID')) == 0) {
            if (!$slSess || !isset($slSess->SessID)) {
                $slSess = new SLSess;
                $slSess->SessUserID = $this->v["uID"];
                $slSess->SessTree   = 0;
                $slSess->save();
            }
            session()->put('slSessID', $slSess->SessID);
        } elseif ($slSess && isset($slSess->SessID) && !isset($slSess->SessUserID) && $this->v["uID"] > 0) {
            $slSess->SessUserID = $this->v["uID"];
            $slSess->save();
        }
        return true;
    }
    
    protected function loadSysSettings()
    {
        $settings = SLDefinitions::where('DefSet', 'Custom Settings')
            ->orderBy('DefOrder', 'asc')
            ->get();
        $this->v["settings"] = [];
        if ($settings->isNotEmpty()) {
            foreach ($settings as $s) {
                $this->v["settings"][$s->DefSubset] = $s->DefValue;
            }
        }
        return true;
    }
    
    protected function loadTreeURL($treeSlug = '')
    {
        if (trim($treeSlug) != '' && $treeSlug != $GLOBALS["SL"]->treeRow->TreeSlug) {
            $urlTree = SLTree::where('TreeSlug', $treeSlug)
                ->first();
            if ($urlTree) {
                $this->dbID = $urlTree->TreeDatabase;
                $this->treeID = $urlTree->TreeID;
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
                    $this->v["uID"]  = (($this->v["user"] && isset($this->v["user"]->id)) ? $this->v["user"]->id : 0);
                }
                if ($this->v["uID"] > 0) {
                    $last = SLUsersActivity::where('UserActUser', '=', $this->v["uID"])
                        ->where('UserActVal', 'LIKE', '%;%')
                        ->where(function ($query) {
                            $query->where('UserActCurrPage', 'LIKE', '/fresh/database%')
                                ->orWhere('UserActCurrPage', 'LIKE', '/fresh/survey%')
                                ->orWhere('UserActCurrPage', 'LIKE', '/dashboard/tree/switch%')
                                ->orWhere('UserActCurrPage', 'LIKE', '/dashboard/tree/new%')
                                ->orWhere('UserActCurrPage', 'LIKE', '/dashboard/db/switch%')
                                ->orWhere('UserActCurrPage', 'LIKE', '/dashboard/db/new%');
                        })
                        ->orderBy('created_at', 'desc')
                        ->first();
                    if ($last && isset($last->UserActVal)) {
                        list($this->dbID, $this->treeID) = explode(';', $last->UserActVal);
                        $this->dbID = intVal($this->dbID);
                        $this->treeID = intVal($this->treeID);
                    }
                }
            }
            $GLOBALS["SL"] = new Globals($request, $this->dbID, $this->treeID);
        }
        return true;
    }
    
    protected function loadDbFromNode(Request $request, $nID)
    {
        $node = SLNode::find($nID);
        if ($node && isset($node->NodeTree)) {
            $tree = SLTree::find($node->NodeTree);
            if ($tree && isset($tree->TreeDatabase)) {
                $this->treeID = $tree->TreeID;
                $this->dbID = $tree->TreeDatabase;
                $GLOBALS["SL"] = new Globals($request, $this->dbID, $this->treeID, $this->treeID);
            }
        }
        return true;
    }
    
    // Check For Basic System Setup First
    public function checkSystemInit()
    {
        if (!session()->has('chkSysInit') || $GLOBALS["SL"]->REQ->has('refresh')) {
            $sysChk = User::select('id')
                ->get();
            if ($sysChk->isEmpty()) {
                return $this->freshUser($GLOBALS["SL"]->REQ);
            }
            $sysChk = SLDatabases::select('DbID')
                ->where('DbUser', '>', 0)
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
        }
        return '';
    }

    protected function chkHasTreeOne($dbID = 1)
    {
        $sysChk = SLTree::find(1);
        return ($sysChk && isset($sysChk->TreeID));
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
        $chk = SLDefinitions::where('DefDatabase', 1)
            ->where('DefSet', 'Blurbs')
            ->where('DefSubset', 'Footer')
            ->first();
        if ($chk && isset($chk->DefDescription) && trim($chk->DefDescription) != '') {
            $GLOBALS["SL"]->sysOpts["footer-master"] = $chk->DefDescription;
        }
        return true;
    }
    
    protected function genCacheKey($baseOverride = '')
    {
        $this->cacheKey = str_replace('/', '.', $this->v["currPage"][0]);
        if ($baseOverride != '')  $this->cacheKey = $baseOverride;
        $this->cacheKey .= '.db' . $GLOBALS["SL"]->dbID;
        $this->cacheKey .= '.tree' . $GLOBALS["SL"]->treeID;
        if ($this->v["isPrint"])  $this->cacheKey .= '.print';
        if ($this->v["isAll"])    $this->cacheKey .= '.all';
        if ($this->v["isAlt"])    $this->cacheKey .= '.alt';
        if ($this->v["isExcel"])  $this->cacheKey .= '.excel';
        return $this->cacheKey;
    }
    
    protected function checkCache($baseOverride = '')
    {
        if ($baseOverride != '') $this->genCacheKey($baseOverride);
        if ($GLOBALS["SL"]->REQ->has('refresh')) Cache::forget($this->cacheKey);
        if (Cache::store('file')->has($this->cacheKey)) {
            $this->v["content"] = Cache::store('file')->get($this->cacheKey);
            return true;
        }
        return false;
    }
    
    protected function saveCache()
    {
        Cache::store('file')->forever($this->cacheKey, $this->v["content"]);
        return true;
    }
    
    // Is this the first time this user has visited the current page?
    protected function isPageFirstTime($currPage = '')
    {
        if (trim($currPage) == '') $currPage = $this->v["currPage"][0];
        $chk = SLUsersActivity::where('UserActUser', Auth::user()->id)
            ->where('UserActCurrPage', 'LIKE', '%'.$currPage)
            ->get();
        if ($chk->isNotEmpty()) return false;
        return true;
    }
    
    protected function logPageVisit($currPage = '', $val = '')
    {
        $log = new SLUsersActivity;
        $log->UserActUser = Auth::user()->id;
        $log->UserActCurrPage = $_SERVER["REQUEST_URI"];
        if (strlen($log->UserActCurrPage) > 255) $log->UserActCurrPage = substr($log->UserActCurrPage, 0, 255);
        $log->UserActVal = $val;
        $log->save();
        return true;
    }
    
    public function freshUser(Request $request)
    {
        $this->survLoopInit($request, '/fresh/creator');
        $GLOBALS["SL"]->sysOpts["signup-instruct"] = '<h2 class="mT5 mB0">Create Admin Account</h2>';
        return view('vendor.survloop.auth.register', [
            "content" => '<div class="jumbotron mBn20"><center>
                <h1>SurvLoop Installed!</h1><p><i>ALL OUR DATA ARE BELONG</i></p>
            </center></div>'
        ]);
    }
    
    protected function getRecsOneFilt($tblMdl = '', $filtFld = '', $filtIn = [], $idFld = '')
    {
        $eval = "\$recs = App\\Models\\" . $tblMdl . "::whereIn('" . $filtFld . "', [ '" 
            . implode("', '", $filtIn) . "' ])->orderBy('created_at', 'desc')->get();";
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
                eval("\$totChk = App\\Models\\" . $tblMdl . "::where('" . $filtFld . "', '" . $filt 
                    . "')->select('" . $idFld . "')->get();");
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
        if (isset($GLOBALS["SL"]->sysOpts["cust-abbr"]) && $GLOBALS["SL"]->sysOpts["cust-abbr"] != 'SurvLoop') {
            $eval = "\$this->custReport = new ". $GLOBALS["SL"]->sysOpts["cust-abbr"] . "\\Controllers\\" 
                . $GLOBALS["SL"]->sysOpts["cust-abbr"] . "(\$request, -3, \$dbID, \$treeID);";
            eval($eval);
        } else {
            $this->custReport = new TreeSurvForm($request, -3, $dbID, $treeID);
        }
        $currPage = '';
        if (isset($this->v["currPage"]) && sizeof($this->v["currPage"]) > 0) {
            $currPage = $this->v["currPage"][0];
        }
        $this->custReport->survLoopInit($request, $currPage);
        return true;
    }
    
    public function initSearcher()
    {
        if ($this->searcher === null) {
            $this->loadCustSearcher();
        }
        return true;
    }
    
    protected function loadCustSearcher()
    {
        if (isset($GLOBALS["SL"]->sysOpts["cust-abbr"]) && $GLOBALS["SL"]->sysOpts["cust-abbr"] != 'SurvLoop') {
            $custClass = $GLOBALS["SL"]->sysOpts["cust-abbr"] . "\\Controllers\\" 
                . $GLOBALS["SL"]->sysOpts["cust-abbr"] . "Searcher";
            if (class_exists($custClass)) {
                eval("\$this->searcher = new ". $custClass . ";");
            }
        } else {
            $this->searcher = new Searcher;
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
            $dbRow = SLDatabases::where('DbID', $dbID)
                //->whereIn('DbUser', [ 0, $this->v["uID"] ])
                ->first();
            if ($dbRow && $dbRow->DbID) {
                $treeRow = SLTree::where('TreeDatabase', $dbID)
                    ->where('TreeType', 'Survey')
                    ->first();
                if ($treeRow && isset($treeRow->TreeID)) {
                    $GLOBALS["SL"] = new Globals($request, $dbID, $treeRow->TreeID, $treeRow->TreeID);
                    $this->logPageVisit($currPage, $dbID . ';' . $treeRow->TreeID);
                }
            }
            return true;
        }
        return false;
    }
    
    protected function switchTree($treeID = -3, $currPage = '', Request $request)
    {
        if ($treeID > 0) {
            $treeRow = SLTree::where('TreeID', $treeID)
                //->where('TreeDatabase', $GLOBALS["SL"]->dbID)
                ->first();
            if ($treeRow && isset($treeRow->TreeID)) {
                $GLOBALS["SL"] = new Globals($request, $treeRow->TreeDatabase, $treeID, $treeID);
                $this->logPageVisit($currPage, $treeRow->TreeDatabase . ';' . $treeID);
            }
            return true;
        }
        return false;
    }
    
    
    public function redir($path, $js = false)
    {
        $redir = $path;
        if (isset($GLOBALS["SL"]->sysOpts["app-url"]) && strpos($path, $GLOBALS["SL"]->sysOpts["app-url"]) != 0) {
            $redir = $GLOBALS["SL"]->sysOpts["app-url"] . $path;
        } else {
            $appUrl = SLDefinitions::where('DefDatabase', 1)
                ->where('DefSet', 'System Settings')
                ->where('DefSubset', 'app-url')
                ->first();
            if ($appUrl && isset($appUrl->DefDescription) && strpos($path, $appUrl->DefDescription) != 0) {
                $redir = $appUrl->DefDescription . $path;
            }
        }
        if (!$js) {
            return redirect($redir);
        } else {
            echo '<script type="text/javascript"> setTimeout("top.location.href=\'' . $redir . '\'", 10); </script>';
            exit;
        }
    }
    
    protected function setNotif($msg = '', $type = 'info')
    {
        session()->put('sessMsg',     $msg);
        session()->put('sessMsgType', 'alert-' . $type);
        return true;
    }
    
    
    // this should really be done using migrations, includes SurvLoop database changes since Feb 15, 2017
    protected function doublecheckSurvTables()
    {
        if (!session()->has('doublecheckSurvTables')) {
            $chks = [];
            $chks[] = "ALTER TABLE `SL_Tree` CHANGE `TreeRootURL` `TreeSlug` VARCHAR(255)";
            $chks[] = "ALTER TABLE `SL_Tree` ADD `TreeOpts` INT(11) DEFAULT 1 AFTER `TreeCoreTable`";
            $chks[] = "ALTER TABLE `SL_DesignTweaks` ADD `TweakUniqueStr` INT(11) DEFAULT NULL AFTER `TweakVersionAB`";
            $chks[] = "ALTER TABLE `SL_DesignTweaks` ADD `TweakIsMobile` VARCHAR(50) DEFAULT NULL AFTER "
                . "`TweakUniqueStr`";
            ob_start();
            try {
                foreach ($chks as $chk) {
                    DB::select($chk);
                }
            } catch (QueryException $e) { }
            ob_end_clean();
            session()->put('doublecheckSurvTables', 1);
        }
        return true;
    }
    
    protected function loadNavMenu()
    {
        $settings = SLDefinitions::where('DefSet', 'Menu Settings')
            ->where('DefSubset', 'main-navigation')
            ->where('DefDatabase', 1)
            ->orderBy('DefOrder', 'asc')
            ->get();
        $this->v["navMenu"] = [];
        if ($settings->isNotEmpty()) {
            foreach ($settings as $s) $this->v["navMenu"][] = [$s->DefValue, $s->DefDescription];
        }
        return true;
    }
    
    public function generateRandomString($length = 10) {
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
        if ($userID <= 0 && Auth::user() && isset(Auth::user()->id)) $userID = Auth::user()->id;
        if ($type == 'Confirm Email') {
            if ($userID > 0) {
                $tokRow = SLTokens::where('TokType', $type)
                    ->where('TokUserID', $userID)
                    ->first();
                if (!$tokRow || !isset($tokRow->TokTokToken)) {
                    $tokRow = new SLTokens;
                    $tokRow->TokType = $type;
                    $tokRow->TokUserID = $userID;
                }
                $tokRow->TokTokToken = $this->generateRandomString(50);
                $tokRow->save();
                return $tokRow->TokTokToken;
            }
        } elseif ($type == 'Sensitive') {
            $token = $this->chkBasicToken($type, $treeID, $coreID, $userID);
            if (trim($token) != '') return $token;
            $tokRow = $this->makeBasicToken($type, $treeID, $coreID, $userID);
            return $tokRow->TokTokToken;
        } elseif ($type == 'MFA') {
            $token = $this->chkBasicToken($type, $treeID, $coreID, $userID);
            if (trim($token) != '') {
                $tokRow = SLTokens::where('TokType', $type)
                    ->where('TokTreeID', $treeID)
                    ->where('TokCoreID', $coreID)
                    ->where('TokUserID', $userID)
                    ->first();
                if ($tokRow && isset($tokRow->TokTokToken)) {
                    $tokRow->TokTokToken = $this->genTokenStr($type);
                    $tokRow->save();
                    return $tokRow->TokTokToken;
                }
            }
            $tokRow = $this->makeBasicToken($type, $treeID, $coreID, $userID);
            return $tokRow->TokTokToken;
        }
        return '';
    }
    
    protected function chkBasicToken($type, $treeID = -3, $coreID = -3, $userID = -3)
    {
        $tokRow = SLTokens::where('TokType', $type)
            ->where('TokTreeID', $treeID)
            ->where('TokCoreID', $coreID)
            ->where('TokUserID', $userID)
            ->first();
        if ($tokRow && isset($tokRow->TokTokToken)) return $tokRow->TokTokToken;
        return '';
    }
    
    protected function makeBasicToken($type, $treeID = -3, $coreID = -3, $userID = -3, $strlen = 50, $delim = '-')
    {
        $tokRow = new SLTokens;
        $tokRow->TokType = $type;
        $tokRow->TokTreeID = $treeID;
        $tokRow->TokCoreID = $coreID;
        $tokRow->TokUserID = $userID;
        $tokRow->TokTokToken = $this->genTokenStr($type);
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
        return date("Y-m-d H:i:s", 
            mktime(intVal(date('H'))-$hrs, date('i'), date('s'), date('m'), date('d'), date('Y')));
    }
    
    public function sendEmail($emaContent, $emaSubject, $emaTo = [], $emaCC = [], $emaBCC = [], $repTo = [])
    {
        if ($GLOBALS["SL"]->isHomestead()) {
            echo '<br /><br /><br /><div class="container"><h2>' . $emaSubject . '</h2>' . $emaContent 
                . '<hr><hr></div>';
            return true;
        }
        if (!isset($repTo[0]) || trim($repTo[0]) == '') {
            $repTo[0] = 'info@' . $GLOBALS["SL"]->getParentDomain();
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
                        $mail .= "->to('" . $eTo[0] . "'" . ((trim($eTo[1]) != '') ? ", '" . $eTo[1] . "'" : "") . ")";
                    }
                }
                if (sizeof($emaCC) > 0) {
                    foreach ($emaCC as $eTo) {
                        $mail .= "->cc('" . $eTo[0] . "'" . ((trim($eTo[1]) != '') ? ", '" . $eTo[1] . "'" : "") . ")";
                    }
                }
                if (sizeof($emaBCC) > 0) {
                    foreach ($emaBCC as $eTo) {
                        $mail .= "->bcc('" . $eTo[0] . "'" . ((trim($eTo[1]) != '') ? ", '" . $eTo[1] . "'" : "") . ")";
                    }
                }
        $mail .= "->replyTo('" . $repTo[0] . "'" . ((trim($repTo[1]) != '') ? ", '" . $repTo[1] . "'" : "") . ")"
            . "; });";
        eval($mail);
        return true;
    }
    
    // This function should be migrated to sendEmail() ...
    protected function sendNewEmailSimple($body, $subject, $emailTo = '', 
        $emailID = -3, $treeID = -3, $coreID = -3, $userTo = -3)
    {
        $emaTo = [];
        if (is_array($emailTo)) {
            $emaTo = $emailTo;
            $emailTo = $emailTo[1] . ' <' . $emailTo[0] . '>';
        } elseif (trim($emailTo) != '') {
            $emaUsr = User::where('email', $emailTo)->first();
            if ($emaUsr && isset($emaUsr->name)) {
                $emaTo[] = [$emailTo, $emaUsr->name];
            }
        }
        if ($GLOBALS["SL"]->isHomestead()) {
            echo '<div class="container"><h2>' . $subject . '</h2>' . $body . '<hr><hr></div>';
        } else {
            $this->sendEmail($body, $subject, $emaTo);
        }
        return $this->logEmailSent($body, $subject, $emailTo, $emailID, $treeID, $coreID, $userTo);
    }
    
    protected function logEmailSent($body, $subject, $emailTo = '', 
        $emailID = -3, $treeID = -3, $coreID = -3, $userTo = -3)
    {
        $emailRec = new SLEmailed;
        $emailRec->EmailedEmailID  = (($emailID > 0) ? $emailID : 0);
        $emailRec->EmailedTree     = (($treeID > 0)  ? $treeID  : 0);
        $emailRec->EmailedRecID    = (($coreID > 0)  ? $coreID  : 0);
        $emailRec->EmailedTo       = trim($emailTo);
        $emailRec->EmailedToUser   = (($userTo > 0)  ? $userTo  : 0);
        $emailRec->EmailedFromUser = ((Auth::user() && isset(Auth::user()->id)) ? Auth::user()->id : 0);
        $emailRec->EmailedSubject  = $subject;
        $emailRec->EmailedBody     = $body;
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
        $content = '<p>' . date("Y-m-d H:i:s") . ' <b>U#' . $uID . '</b> - ' . $content 
            . '<br /><span class="slGrey fPerc80">' . $this->hashIP() . '</span></p>';
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
    
    public function getIP()
    {
        $ip = $_SERVER["REMOTE_ADDR"];
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"]; // share internet
        } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; // pass from proxy
        }
        return $ip;
    }
    
    public function hashIP()
    {
        return hash('sha512', $this->getIP());
    }
    
    public function logAddSessStuff($type)
    {
        $log = '';
        $this->logAdd('session-stuff', $log);
        return true;
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
    
    public function loadSysUpdates()
    {
        $this->v["pastUpDef"] = $this->getCoreDef('System Checks', 'system-updates');
        $this->v["pastUpArr"] = $GLOBALS["SL"]->mexplode(';;', $this->v["pastUpDef"]->DefDescription);
        return true;
    }
    
    protected function addSysUpdate($updateID)
    {
        $done = in_array($updateID[0], $this->v["pastUpArr"]);
        $this->v["updateList"][] = [ $updateID[0], $done, $updateID[1] ];
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
            return ['ZipAshrae', 'Zips'];
        }
        $ret = $this->tblsInPackageCustom();
        if (sizeof($ret) > 0) {
            return $ret;
        }
        $chk = SLTables::where('TblDatabase', $this->dbID)
            ->whereRaw("TblOpts%5 LIKE 0")
            ->select('TblName')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $tbl) {
                $ret[] = $tbl->TblName;
            }
        }
        return $ret;
    }
    
    public function tblsInPackageCustom()
    {
        return [];
    }
    
    public function initPowerUser($uID = -3)
    {
        return true;
    }
    
}
