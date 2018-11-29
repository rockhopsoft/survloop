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

use App\Models\User;
use App\Models\SLDatabases;
use App\Models\SLDefinitions;
use App\Models\SLEmailed;
use App\Models\SLNode;
use App\Models\SLTree;
use App\Models\SLTables;
use App\Models\SLTokens;
use App\Models\SLUsersActivity;

use Illuminate\Support\Facades\Mail;
use SurvLoop\Controllers\SurvLoopInstaller;

use SurvLoop\Controllers\CoreGlobals;

class SurvLoopController extends Controller
{
    public $custAbbr             = 'SurvLoop';
    protected $CustReport        = [];
    
    protected $dbID              = 1;
    protected $treeID            = 1;
    protected $treeFromURL       = false;
    
    protected $coreID            = -3;
    protected $corePublicID      = -3;
    protected $coreIDoverride    = -3;
    public $coreIncompletes      = [];
    protected $sessID            = -3;
    protected $sessInfo          = [];
    protected $sessLoops         = [];
    
    public $v                    = []; // contains data to be shares with views 
    protected $REQ               = NULL; // class copy of Laravel's (Request $request)
    protected $currPage          = '';
    protected $cacheKey          = '';
    protected $isFirstTimeOnPage = false;
    protected $survInitRun       = false;
    
    protected $extraTree         = [];
    
    public function survLoopInit(Request $request, $currPage = '', $runExtra = true)
    {
        if (!$this->survInitRun) {
            $this->survInitRun = true;
            if (!$this->REQ) $this->REQ = $request;
            $this->v["user"]       = Auth::user();
            $this->v["uID"]        = (($this->v["user"] && isset($this->v["user"]->id)) ? $this->v["user"]->id : 0);
            $this->v["isAdmin"]    = ($this->v["user"] && $this->v["user"]->hasRole('administrator'));
            $this->v["isVolun"]    = ($this->v["user"] && $this->v["user"]->hasRole('volunteer'));
            $this->v["isAll"]      = $request->has('all');
            $this->v["isAlt"]      = $request->has('alt');
            $this->v["isPrint"]    = $request->has('print');
            $this->v["isExcel"]    = $request->has('excel');
            $this->v["view"]       = (($request->has('view')) ? trim($request->get('view')) : '');
            $this->v["isDash"]     = false;
            $this->v["exportDir"]  = 'survloop';
            $this->v["content"]    = '';
            $this->v["isOwner"]    = false;
            if (!isset($GLOBALS["SL"]->x["dataPerms"])) $GLOBALS["SL"]->x["dataPerms"] = 'public';
            
            if (!isset($this->v["currPage"])) $this->v["currPage"] = ['', ''];
            if (trim($this->v["currPage"][0]) == '') $this->v["currPage"][0] = $currPage;
            if (trim($this->v["currPage"][0]) == '') {
                $this->v["currPage"][0] = $_SERVER["REQUEST_URI"];
                if (strpos($this->v["currPage"][0], '?') !== false) {
                    $this->v["currPage"][0] = substr($this->v["currPage"][0], 0, strpos($this->v["currPage"][0], '?'));
                }
            }
            
            if ($this->REQ->has('sessmsg') && trim($this->REQ->get('sessmsg')) != '') {
                session()->put('sessMsg', trim($this->REQ->get('sessmsg')));
            }
            
            if (!isset($this->v["currState"]))    $this->v["currState"]    = '';
            if (!isset($this->v["yourUserInfo"])) $this->v["yourUserInfo"] = [];
            if (!isset($this->v["yourContact"]))  $this->v["yourContact"]  = [];
            
            $this->loadNavMenu();
            $this->loadDbLookups($request);
            
            if ($this->REQ->has('refresh') && trim($this->REQ->get('refresh')) != '') {
                $this->checkSystemInit();
            }
            if (isset($GLOBALS["slRunUpdates"]) && $GLOBALS["slRunUpdates"]) {
                $this->v["pastUpDef"] = $this->v["pastUpArr"] = $this->v["updateList"] = [];
            }
            
            if ($this->coreIDoverride > 0) $this->loadAllSessData();
            
            if ($runExtra) {
                $this->initExtra($request);
                $this->loadSysSettings();
                $this->initCustViews();
            }
            $this->genCacheKey();
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
            foreach ($settings as $s) $this->v["settings"][$s->DefSubset] = $s->DefValue;
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
            $GLOBALS["SL"] = new CoreGlobals($request, $this->dbID, $this->treeID);
        }
        return true;
    }
    
    protected function isUserAdmin()
    {
        return (Auth::user() && Auth::user()->hasRole('administrator'));
    }
    
    protected function isUserStaff()
    {
        return (Auth::user() && Auth::user()->hasRole('staff'));
    }
    
    protected function isUserVolun()
    {
        return (Auth::user() && Auth::user()->hasRole('volunteer'));
    }
    
    protected function isUserPartn()
    {
        return (Auth::user() && Auth::user()->hasRole('partner'));
    }
    
    protected function loadDbFromNode(Request $request, $nID)
    {
        $node = SLNode::find($nID);
        if ($node && isset($node->NodeTree)) {
            $tree = SLTree::find($node->NodeTree);
            if ($tree && isset($tree->TreeDatabase)) {
                $this->treeID = $tree->TreeID;
                $this->dbID = $tree->TreeDatabase;
                $GLOBALS["SL"] = new CoreGlobals($request, $this->dbID, $this->treeID, $this->treeID);
            }
        }
        return true;
    }
    
    // Check For Basic System Setup First
    public function checkSystemInit()
    {
        if (!session()->has('chkSysInit') || $this->REQ->has('refresh')) {
            $sysChk = User::select('id')
                ->get();
            if ($sysChk->isEmpty()) {
                return $this->freshUser($this->REQ);
            }
            $sysChk = SLDatabases::select('DbID')
                ->where('DbUser', '>', 0)
                ->get();
            if ($sysChk->isEmpty() && $GLOBALS["SL"]->sysOpts["cust-abbr"] != 'SurvLoop') {
                return $this->redir('/fresh/database');
            }
            if ($GLOBALS["SL"]->dbID > 0) {
                $sysChk = SLTree::select('TreeID')
                    ->where('TreeDatabase', '=', $GLOBALS["SL"]->dbID)
                    ->get();
                if ($sysChk->isEmpty()) {
                    return $this->redir('/fresh/user-experience');
                }
            }
            $survInst = new SurvLoopInstaller;
            $survInst->checkSysInit();
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
    
    protected function initCustViews()
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
    
    
    protected function getAdmMenu($currPage = '')
    {
        $this->admMenuData = [ "adminNav" => [], "currNavPos" => [] ];
        $this->admMenuData["adminNav"] = $this->loadAdmMenu();
        if ($this->classExtension == 'AdminController' && $GLOBALS["SL"]->sysOpts["cust-abbr"] != 'SurvLoop') {
            eval("\$CustAdmin = new " . $GLOBALS["SL"]->sysOpts["cust-abbr"] 
                . "\\Controllers\\" . $GLOBALS["SL"]->sysOpts["cust-abbr"] . "Admin;");
            if ($CustAdmin) {
                $CustAdmin->admControlInit($this->REQ, $currPage);
                $this->admMenuData["adminNav"] = $CustAdmin->loadAdmMenu();
            }
        }
        //if (!$this->CustReport) $this->admMenuData["adminNav"] = $this->loadAdmMenu();
        //else $this->admMenuData["adminNav"] = $this->CustReport->loadAdmMenu();
        if (!$this->getAdmMenuLoc($currPage) && $currPage != '') {
            $this->getAdmMenuLoc($currPage);
        }
        $this->tweakAdmMenu($currPage);
        return view('vendor.survloop.admin.admin-menu', $this->admMenuData);
    }
    
    protected function loadCustReport($request, $treeID = -3)
    {
        if ($treeID <= 0) $treeID = $this->treeID;
        if (isset($GLOBALS["SL"]->sysOpts["cust-abbr"]) && $GLOBALS["SL"]->sysOpts["cust-abbr"] != 'SurvLoop') {
            $eval = "\$this->CustReport = new ". $GLOBALS["SL"]->sysOpts["cust-abbr"] . "\\Controllers\\" 
                . $GLOBALS["SL"]->sysOpts["cust-abbr"] . "Report(\$request, -3, " 
                . $this->dbID . ", " . $treeID . ");";
            eval($eval);
        } else {
            $this->CustReport = new SurvLoopReport($request, -3, $this->dbID, $treeID);
        }
        $currPage = '';
        if (isset($this->v["currPage"]) && sizeof($this->v["currPage"]) > 0) $currPage = $this->v["currPage"][0];
        $this->CustReport->survLoopInit($request, $currPage);   
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
                    $GLOBALS["SL"] = new CoreGlobals($request, $dbID, $treeRow->TreeID, $treeRow->TreeID);
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
                $GLOBALS["SL"] = new CoreGlobals($request, $treeRow->TreeDatabase, $treeID, $treeID);
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
    
    
    public function extractJava($str = '', $nID = -3, $destroy = false)
    {
        if (trim($str) == '') return '';
        $tagMeat = '';
        $str = str_replace('</ script>', '</script>', $str);
        $orig = $str;
        $tag1start = strpos($str, '<script');
        if ($tag1start !== false) {
            $tag1end = strpos($str, '>', $tag1start);
            if ($tag1end !== false) {
                $tag2 = strpos($str, '</script>', $tag1end);
                if ($tag2 !== false) {
                    $tagMeat .= (($nID > 0) ? ' /* start extract from node ' . $nID . ': */ ' : '')
                        . substr($str, ($tag1end+1), ($tag2-$tag1end-1))
                        . (($nID > 0) ? ' /* end extract from node ' . $nID . ': */ ' : '');
                    $str = substr($str, 0, $tag1start) . substr($str, ($tag2+9));
                }
            }
        }
        if (!$destroy) $GLOBALS["SL"]->pageJAVA .= $tagMeat;
        return $str;
    }
    
    public function extractStyle($str = '', $nID = -3, $destroy = false)
    {
        if (trim($str) == '') return '';
        $tagMeat = '';
        $str = str_replace('</ style>', '</style>', $str);
        $orig = $str;
        $tag1start = strpos($str, '<style');
        if ($tag1start !== false) {
            $tag1end = strpos($str, '>', $tag1start);
            if ($tag1end !== false) {
                $tag2 = strpos($str, '</style>', $tag1end);
                if ($tag2 !== false) {
                    $tagMeat .= (($nID > 0) ? ' /* start extract from node ' . $nID . ': */ ' : '')
                        . substr($str, ($tag1end+1), ($tag2-$tag1end-1))
                        . (($nID > 0) ? ' /* end extract from node ' . $nID . ': */ ' : '');
                    $str = substr($str, 0, $tag1start) . substr($str, ($tag2+8));
                }
            }
        }
        return $str;
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
        } else {
            $appUrl = SLDefinitions::where('DefDatabase', 1)
                ->where('DefSet', 'System Settings')
                ->where('DefSubset', 'app-url')
                ->first();
            if ($appUrl && isset($appUrl->DefDescription)) {
                $redir = $appUrl->DefDescription . $path;
            }
        }
        if (!$js) return redirect($redir);
        else {
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
        if ($type == 'MFA') $strlen = 12;
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
        if ($type == 'Confirm Email') $hrs = 24*28;
        return date("Y-m-d H:i:s", 
            mktime(intVal(date('H'))-$hrs, date('i'), date('s'), date('m'), date('d'), date('Y')));
    }
    
    public function sendFakeEmail($emaContent, $emaSubject, $emaTo = [], $emaCC = [], $emaBCC = [], $repTo = [])
    {
        echo '<br /><br /><br /><div class="container"><h2>' . $emaSubject . '</h2>' . $emaContent 
            . '<hr><hr></div>';
    }
    
    public function sendEmail($emaContent, $emaSubject, $emaTo = [], $emaCC = [], $emaBCC = [], $repTo = [])
    {
        if (!isset($repTo[0]) || trim($repTo[0]) == '') $repTo[0] = 'info@' . $GLOBALS["SL"]->getParentDomain();
        if (!isset($repTo[1]) || trim($repTo[1]) == '') $repTo[1] = $GLOBALS["SL"]->sysOpts["site-name"];
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
        }
        elseif (trim($emailTo) != '') {
            $emaUsr = User::where('email', $emailTo)->first();
            if ($emaUsr && isset($emaUsr->name)) $emaTo[] = [$emailTo, $emaUsr->name];
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
        if (strpos($fold, '/') !== false) $subs = explode('/', $fold);
        if (sizeof($subs) > 0) {
            foreach ($subs as $sub) {
                if (trim($sub) != '') {
                    $currFold .= '/' . $sub;
                    if (!is_dir($currFold)) mkdir($currFold);
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
        if (!file_exists($fold . $file)) Storage::disk('local')->put($file, ' ');
        Storage::disk('local')->prepend($file, $content);
        return true;
    }
    
    public function logLoad($log)
    {
        $file = '../storage/app/log/' . $log . '.html';
        if ($this->v["isAdmin"] && file_exists($file)) return file_get_contents($file);
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
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) $ip = $_SERVER["HTTP_CLIENT_IP"]; // share internet
        elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) $ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; // pass from proxy
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
    
    protected function loadSysUpdates()
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
    
    protected function sysUpdatesCust($apply = false) { return ''; }
    
    
    public function printUserLnk($uID = -3)
    {
        if ($uID > 0) {
            $user = User::find($uID);
            if ($user && isset($user->id)) return $user->printUsername();
            return 'User #' . $uID;
        }
        return '';
    }
    
    protected function tblQrySlExports()
    {
        return SLTables::where('TblDatabase', 3)
            ->whereIn('TblName', ['BusRules', 'Conditions', 'ConditionsArticles', 'ConditionsNodes', 'ConditionsVals', 
                'Databases', 'DataHelpers', 'DataLinks', 'DataLoop', 'DataSubsets', 'Definitions', 'Emails', 'Fields', 
                'Images', 'Node', 'NodeResponses', 'Tables', 'Tree'])
            ->orderBy('TblOrd', 'asc')
            ->get();
    }
    
    protected function tblsInPackage()
    {
        if ($this->dbID == 3) return ['ZipAshrae', 'Zips'];
        return [];
    }
    
}
