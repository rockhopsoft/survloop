<?php
/**
  * SurvloopControllerUtils holds helper functions for the primary base class for Survloop, 
  * housing logging functions.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.18
  */
namespace RockHopSoft\Survloop\Controllers;

use Auth;
use Storage;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SLTree;
use App\Models\SLUsersActivity;
use RockHopSoft\Survloop\Controllers\Globals\GlobalsCache;
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use Illuminate\Routing\Controller;

class SurvloopControllerUtils extends Controller
{
    public $isLoaded             = true;
    protected $custReport        = [];
    
    protected $dbID              = 1;
    protected $treeID            = 0;
    protected $treeFromURL       = false;
    
    protected $coreID            = -3;
    protected $corePublicID      = -3;
    protected $coreIDoverride    = -3;
    public $coreIncompletes      = [];
    protected $sessID            = 0;
    protected $sessInfo          = [];
    protected $sessLoops         = [];
    
    public $v                    = []; 
    // contains data to be shares with views, 
    // and/or across [dispersed] functions
    
    protected $currPage          = '';
    protected $cacheKey          = '';
    protected $isFirstTimeOnPage = false;
    protected $survInitRun       = false;
    
    protected $extraTree         = [];
    public    $searcher          = null;

    
    /**
     * Initialize the simplest Survloop variables which track page loads.
     *
     * @param  Illuminate\Http\Request  $request
     * @return boolean
     */
    protected function loadSimpleVars(Request $request)
    {
        $this->v["isAll"]     = $request->has('all');
        $this->v["isAlt"]     = $request->has('alt');
        $this->v["isPrint"]   = $request->has('print');
        $this->v["isExcel"]   = $request->has('excel');
        $this->v["view"]      = '';
        if ($request->has('view')) {
            $this->v["view"] = trim($request->get('view'));
        }
        $this->v["isDash"]    = false;
        $this->v["exportDir"] = 'survloop';
        $this->v["content"]   = '';
        $this->v["isOwner"]   = false;
        if (!isset($this->v["currState"])) {
            $this->v["currState"] = '';
        }
        if (!isset($this->v["yourUserInfo"])) {
            $this->v["yourUserInfo"] = [];
        }
        if (!isset($this->v["yourContact"])) {
            $this->v["yourContact"]  = [];
        }
        return true;
    }
    
    /**
     * Check if the current user has staff or admin permissions.
     *
     * @return boolean
     */
    protected function isStaffOrAdmin()
    {
        return (isset($this->v["uID"]) 
            && $this->v["uID"] > 0
            && ((isset($this->v["isAdmin"]) && $this->v["isAdmin"])
                || (isset($this->v["isStaff"]) && $this->v["isStaff"])));
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
        $ret = '/';
        if (isset($this->v["currPage"][0])) {
            $ret = $this->v["currPage"][0];
        }
        return $ret;
    }
    
    /**
     * Initializing a bunch of things which are not [yet] automatically 
     * set by the Survloop and its GUIs.
     *
     * @param  Illuminate\Http\Request  $request
     * @return boolean
     */
    protected function initExtra(Request $request)
    {
        return true;
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
    
    protected function extraNavItems()
    {
        return '';
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
        $this->cacheKey .= '.db' . $GLOBALS["SL"]->dbID 
            . '.tree' . $GLOBALS["SL"]->treeID 
            . '.' . $this->getHighestGroupLabel();
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
    
    public function logAdd($log, $content)
    {
        $this->checkFolder('../storage/app/log');
        $fold = '../storage/app/';
        $file = 'log/' . $log . '.html';
        $uID = 0;
        if (Auth::user() && isset(Auth::user()->id)) {
            $uID = Auth::user()->id;
        }
        if (!isset($GLOBALS["SL"])) {
            $GLOBALS["SL"] = new Globals(new Request, $this->dbID, $this->treeID);
        }
        $content = '<p>' . date("Y-m-d H:i:s") . ' <b>U#' . $uID . '</b> - ' 
            . $content . '<br /><span class="slGrey fPerc80">' 
            . $GLOBALS["SL"]->hashIP(true) . '</span></p>';
        if (!file_exists($fold . $file)) {
            Storage::disk('local')->put($file, ' ');
        }
        Storage::disk('local')->prepend($file, $content);
        return true;
    }
    
    private function logLoad($log)
    {
        $file = '../storage/app/log/' . $log . '.html';
        if ($this->isStaffOrAdmin() && file_exists($file)) {
            return file_get_contents($file);
        }
        return '';
    }
    
    protected function logPreview($log)
    {
        $ret = '';
        $all = $this->logLoad($log);
        if (trim($all) != '' && strpos($all, '</p>') !== false) {
            $logs = $GLOBALS["SL"]->mexplode('</p>', $all);
            for ($i = 0; ($i < 100 && $i < sizeof($logs)); $i++) {
                $ret .= $logs[$i] . '</p><div class="p10"></div>';
            }
        }
        return $ret;
    }
    
    protected function logPreviewCore($log, $coreID = 0, $coreTbl = '')
    {
        if ($coreTbl == '') {
            $coreTbl = $GLOBALS["SL"]->coreTbl;
        }
        $match = $coreTbl . '#' . $coreID;
        $ret = '';
        $all = $this->logLoad($log);
        if (trim($all) != '' && strpos($all, '</p>') !== false) {
            $logs = $GLOBALS["SL"]->mexplode('</p>', $all);
            for ($i = 0; $i < sizeof($logs); $i++) {
                if (strpos($logs[$i], $match) !== false) {
                    $ret .= $logs[$i] . '</p><div class="p5"></div>';
                }
            }
        }
        return $ret;
    }
    
    protected function logPreviewUser($log, $userID = 0)
    {
        $match = '<b>U#' . $userID . '</b>';
        $ret = '';
        $all = $this->logLoad($log);
        if (trim($all) != '' && strpos($all, '</p>') !== false) {
            $logs = $GLOBALS["SL"]->mexplode('</p>', $all);
            for ($i = 0; $i < sizeof($logs); $i++) {
                if (strpos($logs[$i], $match) !== false) {
                    $ret .= $logs[$i] . '</p><div class="p5"></div>';
                }
            }
        }
        return $ret;
    }
    
    protected function activityPreviewUser($userID = 0)
    {
        $ret = '';
        $chk = SLUsersActivity::where('user_act_user', $userID)
            ->orderBy('created_at', 'desc')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $log) {
                $ret .= '<p>' . date("n/j/y, g:ia", strtotime($log->created_at)) 
                    . ' ' . $log->user_act_curr_page . '</p>';
            }
        }
        return $ret;
    }
    
    protected function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    protected function checkFolder($fold)
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
    
    protected function printUserLnk($uID = -3)
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

}
