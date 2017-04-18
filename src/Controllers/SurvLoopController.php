<?php
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Cache;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller;
use Illuminate\Database\QueryException;

use SurvLoop\Controllers\DatabaseLookups;

use App\Models\User;
use App\Models\SLDatabases;
use App\Models\SLDefinitions;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLUsersActivity;

class SurvLoopController extends Controller
{
    public $custAbbr             = 'SurvLoop';
    
    protected $dbID              = 1;
    protected $treeID            = 1;
    protected $treeFromURL       = false;
    
    protected $coreID            = -3;
    protected $coreIDoverride    = -3;
    public $coreIncompletes      = [];
    protected $sessID            = -3;
    protected $sessInfo          = [];
    protected $sessLoops         = [];
    
    public $v                    = array(); // contains data to be shares with views 
    protected $REQ               = array(); // class copy of Laravel's (Request $request)
    protected $currPage          = '';
    protected $cacheKey          = '';
    protected $isFirstTimeOnPage = false;
    protected $survInitRun       = false;
    
    protected $extraTree         = [];
    
    protected function survLoopInit(Request $request, $currPage = '', $runExtra = true)
    {
        if (!$this->survInitRun) {
            $this->survInitRun = true;
            if (sizeof($this->REQ) == 0) $this->REQ = $request;
            $this->v["user"]        = Auth::user();
            $this->v["isAdmin"]     = ($this->v["user"] && $this->v["user"]->hasRole('administrator'));
            $this->v["isAll"]       = $request->has('all');
            $this->v["isAlt"]       = $request->has('alt');
            $this->v["isPrint"]     = $request->has('print');
            $this->v["isExcel"]     = $request->has('excel');
            $this->v["exportDir"]   = 'survloop';
            $this->v["content"]     = '';
            $this->v["pageJSextra"] = '';
            $this->v["pageJStop"]   = (($this->v["user"] && $this->v["user"]->hasRole('administrator')) 
                ? 'setNavItem("Admin Dashboard", "/dashboard"); ' : '') . $this->extraNavItems();
            if (isset($this->v["user"]) && isset($this->v["user"]->id) && $this->v["user"]->id > 0) {
                $this->v["pageJStop"] .= 'setNavItem("Hi, ' . $this->v["user"]->printCasualUsername(false) 
                    . '", "/profile/' . $this->v["user"]->id . '"); ';
                $this->v["pageJStop"] .= 'setNavItem("Logout", "/logout"); ';
            } else {
                $this->v["pageJStop"] .= 'setNavItem("Login", "/login"); setNavItem("Sign Up", "/register"); ';
            }
            
            if (!isset($this->v["currPage"])) $this->v["currPage"] = $currPage;
            if (trim($this->v["currPage"]) == '') {
                $this->v["currPage"] = $_SERVER["REQUEST_URI"];
                if (strpos($this->v["currPage"], '?') !== false) {
                    $this->v["currPage"] = substr($this->v["currPage"], 0, strpos($this->v["currPage"], '?'));
                }
            }
            
            if (!isset($this->v["currState"]))    $this->v["currState"] = '';
            if (!isset($this->v["yourUserInfo"])) $this->v["yourUserInfo"] = array();
            if (!isset($this->v["yourContact"]))  $this->v["yourContact"] = array();
            
            $this->loadDbLookups($request);
            if ($this->coreIDoverride > 0) $this->loadAllSessData();
            
            if ($runExtra) {
                $this->initExtra($request);
                $this->loadSysSettings();
                $this->initCustViews();
            }
            $this->genCacheKey();
            $GLOBALS["SL"]->pageJStop .= $this->v["pageJStop"];
        }
        return true;
    }
    
    protected function loadSysSettings() 
    {
        $settings = SLDefinitions::where('DefSet', 'Custom Settings')
            ->orderBy('DefOrder', 'asc')
            ->get();
        $this->v["settings"] = [];
        if ($settings && sizeof($settings) > 0) {
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
            if ($urlTree && isset($urlTree->TreeID)) {
                $this->dbID = $urlTree->TreeDatabase;
                $this->treeID = $urlTree->TreeID;
                $this->treeFromURL = true;
            }
        }
        return true;
    }
    
    protected function loadDbLookups(Request $request)
    {
        if (!isset($GLOBALS["SL"])) {
            if (!$this->treeFromURL) {
                if (!isset($this->v["user"])) $this->v["user"] = Auth::user();
                if (isset($this->v["user"]) && intVal($this->v["user"]->id) > 0) {
                    $last = SLUsersActivity::where('UserActUser', '=', $this->v["user"]->id)
                        ->where('UserActVal', 'LIKE', '%;%')
                        ->where(function ($query) {
                            $query->where('UserActCurrPage', 'LIKE', '/fresh/database%')
                                ->orWhere('UserActCurrPage', 'LIKE', '/fresh/user-experience%')
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
            if (!isset($this->v["isAdmin"])) {
                $this->v["isAdmin"] = (Auth::user() && Auth::user()->hasRole('administrator'));
            }
            $GLOBALS["SL"] = new DatabaseLookups($request, $this->v["isAdmin"], $this->dbID, $this->treeID);
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
                $isAdmin = ((isset($this->v["isAdmin"])) ? $this->v["isAdmin"]
                    : (Auth::user() && Auth::user()->hasRole('administrator')));
                $GLOBALS["SL"] = new DatabaseLookups($request, $isAdmin, $this->dbID, $this->treeID, $this->treeID);
            }
        }
        return true;
    }
    
    protected function loadCustView($view)
    {
        if (file_exists(base_path('resources/views/vendor/' 
            . $GLOBALS["SL"]->sysOpts["cust-abbr"] . '/' . $view . '.blade.php'))) {
            $view = 'vendor.' . $GLOBALS["SL"]->sysOpts["cust-abbr"] . '.' . $view;
        } elseif (file_exists(base_path('resources/views/vendor/' 
            . strtolower($GLOBALS["SL"]->sysOpts["cust-abbr"]) . '/' . $view . '.blade.php'))) {
            $view = 'vendor.' . strtolower($GLOBALS["SL"]->sysOpts["cust-abbr"]) . '.' . $view;
        } else {
            $view = 'vendor.survloop.' . $view;
        }
        return view($view, $this->v)->render();
    }
    
    // Check For Basic System Setup First
    public function checkSystemInit()
    {
        if (!session()->has('chkSysInit')) {
            $sysChk = User::select('id')
                ->get();
            if (!$sysChk || sizeof($sysChk) == 0) {
                return $this->freshUser($this->REQ);
            }
            $sysChk = SLDatabases::select('DbID')
                ->where('DbUser', '>', 0)
                ->get();
            if ((!$sysChk || sizeof($sysChk) == 0) && $GLOBALS["SL"]->sysOpts["cust-abbr"] != 'SurvLoop') {
                return $this->redir('/fresh/database');
            }
            if ($GLOBALS["SL"]->dbID > 0) {
                $sysChk = SLTree::select('TreeID')
                    ->where('TreeDatabase', '=', $GLOBALS["SL"]->dbID)
                    ->get();
                if (!$sysChk || sizeof($sysChk) == 0) {
                    return $this->redir('/fresh/user-experience');
                }
            }
            session()->put('chkSysInit', 1);
        }
        return '';
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
    
    protected function initExtra(Request $request)
    {
        return true;
    }
    
    protected function extraNavItems()
    {
        return true;
    }
    
    protected function initCustViews()
    {
        $views = ['footer-master', 'footer-admin'];
        foreach ($views as $view) {
            $GLOBALS["SL"]->sysOpts[$view] = $this->loadCustView('inc-' . $view);
        }
        return true;
    }
    
    protected function genCacheKey($baseOverride = '')
    {
        $this->cacheKey = str_replace('/', '.', $this->v["currPage"]);
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
        if ($GLOBALS["SL"]->REQ->has('refresh')) {
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
    
    function mexplode($delim, $str)
    {
        $ret = [];
        if (trim(str_replace($delim, '', $str)) != '') {
            if (strpos($str, $delim) === false) {
                $ret[] = $str;
            } else {
                if (substr($str, 0, 1) == $delim) $str = substr($str, 1);
                if (substr($str, strlen($str)-1) == $delim) $str = substr($str, 0, strlen($str)-1);
                $ret = explode($delim, $str);
            }
        }
        return $ret;
    }
    
    public function slugify($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
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
        $GLOBALS["SL"]->sysOpts["signup-instruct"] = '<h2 class="mT5 mB0">Create Admin Account</h2>';
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
        if ($this->classExtension == 'AdminController' && $GLOBALS["SL"]->sysOpts["cust-abbr"] != 'SurvLoop') {
            eval("\$CustAdmin = new " . $GLOBALS["SL"]->sysOpts["cust-abbr"] 
                . "\\Controllers\\" . $GLOBALS["SL"]->sysOpts["cust-abbr"] . "Admin;");
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

    
    protected function switchDatabase(Request $request, $dbID = -3, $currPage = '')
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
                    $GLOBALS["SL"] = new DatabaseLookups($request, $this->v["isAdmin"], 
                        $dbID, $treeRow->TreeID, $treeRow->TreeID);
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
                $GLOBALS["SL"] = new DatabaseLookups($request, $this->v["isAdmin"], $treeRow->TreeDatabase, $treeID, $treeID);
                $this->logPageVisit($currPage, $treeRow->TreeDatabase . ';' . $treeID);
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
    
    public function isMobile()
    {
    	return (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec'
			. '|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?'
			. '|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap'
			. '|windows (ce|phone)|xda|xiino/i', $_SERVER['HTTP_USER_AGENT'])
			|| preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av'
			. '|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb'
			. '|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw'
			. '|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8'
			. '|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit'
			. '|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)'
			. '|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji'
			. '|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga'
			. '|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)'
			. '|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf'
			. '|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil'
			. '|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380'
			. '|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc'
			. '|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01'
			. '|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)'
			. '|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61'
			. '|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', 
			substr($_SERVER['HTTP_USER_AGENT'],0,4)));
    }
    
    public function redir($path, $js = false)
    {
        $redir = $path;
        if (isset($GLOBALS["SL"]->sysOpts["app-url"])) {
            $redir = $GLOBALS["SL"]->sysOpts["app-url"] . $path;
        }
        $appUrl = SLDefinitions::where('DefDatabase', 1)
            ->where('DefSet', 'System Settings')
            ->where('DefSubset', 'app-url')
            ->first();
        if ($appUrl && isset($appUrl->DefDescription)) {
            $redir = $appUrl->DefDescription . $path;
        }
        if (!$js) return redirect($redir);
        else {
            echo '<script type="text/javascript"> window.location=\'' . $redir . '\'; </script>';
            exit;
        }
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
    
    
    public function scriptsJsXtra()
    {
        return $this->loadCustView('inc-scripts-js-xtra');
    }
    
    public function scriptsJqueryXtra()
    {
        return $this->loadCustView('inc-scripts-jquery-xtra');
    }
    
    public function scriptsJqueryXtraSearch()
    {
        return $this->loadCustView('inc-scripts-jquery-xtra-search');
    }
    
    
    protected function loadExtraTree()
    {
        $this->extraTree = new BrandedTree;
        //...
    }
    
    public function getColsWidth($sizeof)
    {
        $colW = 12;
        if ($sizeof == 2) {
            $colW = 6;
        } elseif ($sizeof == 3) {
            $colW = 4;
        } elseif ($sizeof == 4) {
            $colW = 3;
        } elseif (in_array($sizeof, [5, 6])) {
            $colW = 2;
        } elseif (in_array($sizeof, [7, 8, 9, 10, 11, 12])) {
            $colW = 1;
        }
        return $colW;
    }
    
}
