<?php
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Cache;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller;

use SurvLoop\Controllers\DatabaseLookups;

use App\Models\User;
use App\Models\SLDatabases;
use App\Models\SLDefinitions;
use App\Models\SLTree;
use App\Models\SLUsersActivity;

class SurvLoopController extends Controller
{
    protected $dbID              = 1;
    protected $treeID            = 1;
    
    protected $coreID            = -3;
    protected $coreIDoverride    = -3;
    protected $coreIncompletes   = [];
    protected $sessID            = -3;
    protected $sessInfo          = [];
    protected $sessLoops         = [];
    
    public $v                    = array(); // contains data to be shares with views 
    protected $REQ               = array(); // class copy of Laravel's (Request $request)
    protected $currPage          = '';
    protected $cacheKey          = '';
    protected $isFirstTimeOnPage = false;
    protected $survInitRun       = false;
    
    protected function survLoopInit(Request $request, $currPage = '', $runExtra = true)
    {
        if (!$this->survInitRun || !isset($GLOBALS["DB"])) {
            $this->survInitRun = true;
            if (sizeof($this->REQ) == 0) $this->REQ = $request;
            $this->v["user"]      = Auth::user();
            $this->v["isAll"]     = $request->has('all');
            $this->v["isAlt"]     = $request->has('alt');
            $this->v["isPrint"]   = $request->has('print');
            $this->v["isExcel"]   = $request->has('excel');
            $this->v["exportDir"] = 'survloop';
            $this->v["content"]   = '';
            
            $this->v["currPage"] = $currPage;
            if (trim($this->v["currPage"]) == '') {
                $this->v["currPage"] = $_SERVER["REQUEST_URI"];
                if (strpos($this->v["currPage"], '?') !== false) {
                    $this->v["currPage"] = substr($this->v["currPage"], 0, strpos($this->v["currPage"], '?'));
                }
            }
            
            if (!isset($this->v["currState"]))    $this->v["currState"] = '';
            if (!isset($this->v["yourUserInfo"])) $this->v["yourUserInfo"] = array();
            if (!isset($this->v["yourContact"]))  $this->v["yourContact"] = array();
            
            $this->loadDbLookups();
            if ($this->coreIDoverride > 0) $this->loadAllSessData();
            
            if ($runExtra) {
                $this->initExtra($request);
                $this->initCustViews();
            }
            $this->genCacheKey();
        }
        return true;
    }
    
    protected function loadDbLookups()
    {
        if (!isset($GLOBALS["DB"])) {
            $db = $tree = 1;
            if (isset($this->v["user"]) && intVal($this->v["user"]->id) > 0) {
                $last = SLUsersActivity::where('UserActUser', '=', $this->v["user"]->id)
                    ->where('UserActVal', 'LIKE', '%;%')
                    ->where(function ($query) {
                        $query->where(  'UserActCurrPage', 'LIKE', '/fresh/database%')
                              ->orWhere('UserActCurrPage', 'LIKE', '/fresh/user-experience%')
                              ->orWhere('UserActCurrPage', 'LIKE', '/dashboard/tree/switch%')
                              ->orWhere('UserActCurrPage', 'LIKE', '/dashboard/tree/new%')
                              ->orWhere('UserActCurrPage', 'LIKE', '/dashboard/db/switch%')
                              ->orWhere('UserActCurrPage', 'LIKE', '/dashboard/db/new%');
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($last && isset($last->UserActVal)) {
                    list($db, $tree) = explode(';', $last->UserActVal);
                    $db = intVal($db);
                    $tree = intVal($tree);
                }
            }
            $GLOBALS["DB"] = new DatabaseLookups($db, $tree);
            $this->dbID    = $db;
            $this->treeID  = $tree;
        }
        return true;
    }
    
    protected function loadCustView($view)
    {
        if (file_exists(base_path('resources/views/vendor/' 
            . $GLOBALS["DB"]->sysOpts["cust-abbr"] . '/' . $view . '.blade.php'))) {
            $view = 'vendor.' . $GLOBALS["DB"]->sysOpts["cust-abbr"] . '.' . $view;
        } elseif (file_exists(base_path('resources/views/vendor/' 
            . strtolower($GLOBALS["DB"]->sysOpts["cust-abbr"]) . '/' . $view . '.blade.php'))) {
            $view = 'vendor.' . strtolower($GLOBALS["DB"]->sysOpts["cust-abbr"]) . '.' . $view;
        } else {
            $view = 'vendor.survloop.' . $view;
        }
        return view( $view, $this->v)->render();
    }
    
    public function getCoreID()
    {
        return $this->coreID;
    }
    
    protected function setCurrPage($currPage = '')
    {
        $this->v["currPage"] = $currPage;
        return true;
    }
    
    public function getCurrPage()
    {
        return ((isset($this->v["currPage"])) ? $this->v["currPage"] : '/');
    }
    
    protected function initExtra(Request $request) { return true; }
    
    protected function initCustViews()
    {
        $views = ['nav-public', 'nav-admin', 'footer-master', 'footer-admin'];
        foreach ($views as $view) {
            $GLOBALS["DB"]->sysOpts[$view] = $this->loadCustView('inc-' . $view);
        }
        return true;
    }
    
    protected function genCacheKey($baseOverride = '')
    {
        $this->cacheKey = str_replace('/', '.', $this->v["currPage"]);
        if ($baseOverride != '')  $this->cacheKey = $baseOverride;
        $this->cacheKey .= '.db' . $GLOBALS["DB"]->dbID;
        $this->cacheKey .= '.tree' . $GLOBALS["DB"]->treeID;
        if ($this->v["isPrint"])  $this->cacheKey .= '.print';
        if ($this->v["isAll"])    $this->cacheKey .= '.all';
        if ($this->v["isAlt"])    $this->cacheKey .= '.alt';
        if ($this->v["isExcel"])  $this->cacheKey .= '.excel';
        return $this->cacheKey;
    }
    
    protected function checkCache($baseOverride = '')
    {
        if ($baseOverride != '') $this->genCacheKey($baseOverride);
        if ($this->REQ->has('refresh')) {
            Cache::forget($this->cacheKey); 
        }
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
        if (trim($currPage) == '') $currPage = $this->v["currPage"];
        $chk = SLUsersActivity::where('UserActUser', Auth::user()->id)
            ->where('UserActCurrPage', 'LIKE', '%'.$currPage)
            ->get();
        if ($chk && sizeof($chk) > 0) return false;
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
    
    
    // a few utilities...
    
    function prepExplode($delim, $str)
    {
        if (substr($str, 0, 1) == $delim) $str = substr($str, 1);
        if (substr($str, strlen($str)-1) == $delim) $str = substr($str, 0, strlen($str)-1);
        return $str;
    }
    
    function mexplode($delim, $str)
    {
        $retArr = array();
        if (strpos($str, $delim) === false) {
            $retArr[0] = $str;
        } else {
            $str = $this->prepExplode($delim, $str);
            $retArr = explode($delim, $str);
        }
        return $retArr;
    }
    
    function exportExcelOldSchool($innerTable, $inFilename = "export.xls")
    {
        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=' .$inFilename );
        echo "<table border=1>";
        echo $innerTable;
        echo "</table>";
        exit;
        return true;
    }
    
    
    
    public function freshUser(Request $request)
    {
        $this->survLoopInit($request, '/fresh/creator');
        $GLOBALS["DB"]->sysOpts["signup-instruct"] = '<h2 class="mT5 mB0">Create Admin Account</h2>';
        return view('vendor.survloop.auth.register', [
            "content" => '<div class="jumbotron mBn20"><center>
                <h1>SurvLoop Installed!</h1><p><i>ALL OUR DATA ARE BELONG</i></p>
            </center></div>'
        ]);
    }
    
    
    
    protected function getAdmMenu($currPage = '')
    {
        $this->admMenuData = [ "adminNav" => [], "currNavPos" => [] ];
        $this->admMenuData["adminNav"] = $this->loadAdmMenu();
        if ($this->classExtension == 'AdminController' && $GLOBALS["DB"]->sysOpts["cust-abbr"] != 'SurvLoop') {
            eval("\$CustAdmin = new " . $GLOBALS["DB"]->sysOpts["cust-abbr"] 
                . "\\Controllers\\" . $GLOBALS["DB"]->sysOpts["cust-abbr"] . "Admin;");
            if ($CustAdmin && sizeof($CustAdmin) > 0) {
                $CustAdmin->admControlInit($this->REQ, $currPage);
                $this->admMenuData["adminNav"] = $CustAdmin->loadAdmMenu();
            }
        }
        //if (sizeof($this->CustReport) = 0) $this->admMenuData["adminNav"] = $this->loadAdmMenu();
        //else $this->admMenuData["adminNav"] = $this->CustReport->loadAdmMenu();
        if (!$this->getAdmMenuLoc($currPage) && $currPage != '') {
            $this->getAdmMenuLoc($currPage);
        }
        $this->tweakAdmMenu($currPage);
        return view('vendor.survloop.admin.admin-menu', $this->admMenuData);
    }

    
    protected function switchDatabase($dbID = -3, $currPage = '')
    {
        if ($dbID > 0) {
            $dbRow = SLDatabases::where('DbID', $dbID)
                //->whereIn('DbUser', [ 0, $this->v["user"]->id ])
                ->first();
            if ($dbRow && $dbRow->DbID) {
                $treeRow = SLTree::where('TreeDatabase', $dbID)
                    ->where('TreeType', 'Primary Public')
                    ->first();
                if ($treeRow && isset($treeRow->TreeID)) {
                    $GLOBALS["DB"] = new DatabaseLookups($dbID, $treeRow->TreeID);
                    $this->logPageVisit($currPage, $dbID . ';' . $treeRow->TreeID);
                }
            }
            return true;
        }
        return false;
    }
    
    protected function switchTree($treeID = -3, $currPage = '')
    {
        if ($treeID > 0) {
            $treeRow = SLTree::where('TreeID', $treeID)
                ->where('TreeDatabase', $GLOBALS["DB"]->dbID)
                ->first();
            if ($treeRow && isset($treeRow->TreeID)) {
                $GLOBALS["DB"] = new DatabaseLookups($GLOBALS["DB"]->dbID, $treeID);
                $this->logPageVisit($currPage, $GLOBALS["DB"]->dbID . ';' . $treeID);
            }
            return true;
        }
        return false;
    }
    
    protected function loadLoopReportClass()
    {
        $class = "SurvLoop\\Controllers\\SurvLoopReport";
        $chk = SLDefinitions::select('DefDescription')
            ->where('DefDatabase', $this->dbID)
            ->where('DefSet', 'System Settings')
            ->where('DefSubset', 'cust-abbr')
            ->first();
        if ($chk && isset($chk->DefDescription)) {
            $custClass = trim($chk->DefDescription) . "\\Controllers\\" . trim($chk->DefDescription) . "Report";
            if (class_exists($custClass)) $class = $custClass;
        }
        return $class;
    }
    
}
