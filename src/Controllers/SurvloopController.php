<?php
/**
  * SurvloopController is the primary base class for Survloop, 
  * housing some key variables and functions.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.1
  */
namespace RockHopSoft\Survloop\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvForm;
use RockHopSoft\Survloop\Controllers\SurvloopInstaller;
use RockHopSoft\Survloop\Controllers\SurvloopControllerUtils;

class SurvloopController extends SurvloopControllerUtils
{
    
    /**
     * Initialize key Survloop variables needed for content delivery.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  string  $currPage
     * @param  boolean  $runExtra
     * @return boolean
     */
    public function survloopInit(Request $request, $currPage = '', $runExtra = true)
    {
        if (!$this->survInitRun || !isset($this->v["uID"])) {
            $this->survInitRun = true;
            $this->loadDbLookups($request);
            $this->loadSimpleVars($request);
            $this->loadUserVars();
            $this->loadCurrPage($currPage);
            $this->loadSlSess($request);
            $this->loadNavMenu();
            if ($this->coreIDoverride > 0) {
                $this->loadAllSessData();
            }
            if ($runExtra) {
                $this->initCheckUpdates($request);
                $this->initExtra($request);
                $this->loadSysSettings();
                $this->initCustViews($request);
            }
            $this->genCacheKey();
        }
        return true;
    }
    
    /**
     * Initialize the client-extension of the Survloop's TreeSurvForm class
     * which works with all branching tress.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  int  $treeID
     * @param  int  $dbID
     * @param  boolean  $slInit
     * @return boolean
     */
    protected function loadCustLoop(Request $request, $treeID = -3, $dbID = -3, $slInit = true)
    {
        if ($treeID <= 0) {
            $treeID = $this->treeID;
        }
        if ($dbID <= 0) {
            $dbID = $this->dbID;
        }
        if ($treeID <= 0) {
            return false;
        }
        $chk = SLTree::where('tree_id', $treeID)
            ->where('tree_database', $dbID)
            ->first();
        if (!$chk || !isset($chk->tree_id)) {
            return false;
        }
//echo '???<pre>'; print_r($request->all()); echo '</pre>'; exit;

        $custLoopFile = '../vendor/' 
            . $GLOBALS["SL"]->sysOpts["cust-package"] . '/src/Controllers/'
            . $GLOBALS["SL"]->sysOpts["cust-abbr"] . '.php';
        if (isset($GLOBALS["SL"]->sysOpts["cust-abbr"]) 
            && $GLOBALS["SL"]->sysOpts["cust-abbr"] != 'Survloop'
            && file_exists($custLoopFile)) {
            $eval = "\$this->custReport = new " 
                . $GLOBALS["SL"]->sysOpts["cust-vend"] . "\\" 
                . $GLOBALS["SL"]->sysOpts["cust-abbr"] . "\\Controllers\\" 
                . $GLOBALS["SL"]->sysOpts["cust-abbr"] 
                . "(\$request, -3, \$dbID, \$treeID, false, "
                . (($slInit) ? "true" : "false") . ");";
            eval($eval);
        } else {
            $this->custReport = new TreeSurvForm($request, -3, $dbID, $treeID, false, $slInit);
        }
        $currPage = '';
        if (isset($this->v["currPage"]) && sizeof($this->v["currPage"]) > 0) {
            $currPage = $this->v["currPage"][0];
        }
        if ($slInit) {
            $this->custReport->survloopInit($request, $currPage);
        } else {
            $this->custReport->authMinimalInit($request, $currPage);
        }
        return true;
    }
    
    /**
     * Initialize key user variables tied to this core trunk object.
     *
     * @return boolean
     */
    protected function loadUserVars()
    {
        $this->v["user"] = Auth::user();
        $this->v["uID"] = 0;
        if ($this->v["user"] 
            && isset($this->v["user"]->id) 
            && intVal($this->v["user"]->id) > 0) {
            $this->v["uID"] = $this->v["user"]->id;
            if (isset($GLOBALS["SL"])) {
                $GLOBALS["SL"]->pageJAVA .= ' loggedIn = true; ';
            }
        }
        $this->v["isAdmin"] 
            = $this->v["isStaff"] 
            = $this->v["isPartner"]
            = $this->v["isVolun"]
            = 0;
        if ($this->v["user"]) {
            $this->v["isAdmin"]   = $this->v["user"]->hasRole('administrator');
            $this->v["isStaff"]   = $this->v["user"]->hasRole('staff');
            $this->v["isPartner"] = $this->v["user"]->hasRole('partner');
            $this->v["isVolun"]   = $this->v["user"]->hasRole('volunteer');
        }
        $this->loadUserVarsJava();
        $this->initPowerUser();
        return true;
    }
    
    /**
     * Initialize key user variables in Javascript.
     *
     * @return boolean
     */
    private function loadUserVarsJava()
    {
        if (isset($GLOBALS["SL"])) {
            if ($this->v["uID"] > 0) {
                $GLOBALS["SL"]->pageJAVA .= ' loggedIn = true; ';
            } else {
                $GLOBALS["SL"]->pageJAVA .= ' loggedIn = false; ';
            }
            if ($this->v["isAdmin"]) {
                $GLOBALS["SL"]->pageJAVA .= ' loggedInAdmin = true; ';
            } else {
                $GLOBALS["SL"]->pageJAVA .= ' loggedInAdmin = false; ';
            }
            if ($this->v["isStaff"]) {
                $GLOBALS["SL"]->pageJAVA .= ' loggedInStaff = true; ';
            } else {
                $GLOBALS["SL"]->pageJAVA .= ' loggedInStaff = false; ';
            }
            if ($this->v["isPartner"]) {
                $GLOBALS["SL"]->pageJAVA .= ' loggedInPartner = true; ';
            } else {
                $GLOBALS["SL"]->pageJAVA .= ' loggedInPartner = false; ';
            }
            if ($this->v["isVolun"]) {
                $GLOBALS["SL"]->pageJAVA .= ' loggedInVolun = true; ';
            } else {
                $GLOBALS["SL"]->pageJAVA .= ' loggedInVolun = false; ';
            }
        }
        return true;
    }
    
    /**
     * Initialize the simplest Survloop variables which track page loads.
     *
     * @param  string  $currPage
     * @return boolean
     */
    protected function loadCurrPage($currPage = '')
    {
        if (!isset($this->v["currPage"])) {
            $this->v["currPage"] = ['', ''];
        }
        if (trim($this->v["currPage"][0]) == '') {
            $this->v["currPage"][0] = $currPage;
        }
        if (trim($this->v["currPage"][0]) == ''
            && isset($_SERVER["REQUEST_URI"])) {
            $this->v["currPage"][0] = $_SERVER["REQUEST_URI"];
            if (strpos($this->v["currPage"][0], '?') !== false) {
                $pos = strpos($this->v["currPage"][0], '?');
                $this->v["currPage"][0] = substr($this->v["currPage"][0], 0, $pos);
            }
        }
        return true;
    }
    
    /**
     * Check for software maintenance processes and updates.
     *
     * @param  Illuminate\Http\Request  $request
     * @return boolean
     */
    protected function initCheckUpdates(Request $request)
    {
        if ($request->has('refresh') 
            && trim($request->get('refresh')) != '') {
            $this->checkSystemInit();
        }
        if (isset($GLOBALS["slRunUpdates"]) 
            && $GLOBALS["slRunUpdates"]) {
            $this->v["pastUpDef"] 
                = $this->v["pastUpArr"] 
                = $this->v["updateList"] 
                = [];
        }
        return true;
    }
    
    /**
     * Load basic Survloop user session for a general site visit,
     * not for a specific survey.
     *
     * @param  Illuminate\Http\Request  $request
     * @return boolean
     */
    protected function loadSlSess(Request $request)
    {
        $slSess = null;
        if (isset($this->v["uID"]) && intVal($this->v["uID"]) > 0) {
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
            } elseif ($slSess 
                && isset($slSess->sess_id) 
                && !isset($slSess->sess_user_id)) {
                $slSess->sess_user_id = $this->v["uID"];
                $slSess->save();
            }
        }
        if ($request->has('sessmsg') 
            && trim($request->get('sessmsg')) != '') {
            session()->put('sessMsg', trim($request->get('sessmsg')));
            session()->save();
        }
        return true;
    }
    
    /**
     * Load all system settings which are customize for a client installation.
     *
     * @return boolean
     */
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
    
    /**
     * Load a survey/page tree by its slug.
     *
     * @param  string  $treeSlug
     * @return boolean
     */
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
    
    /**
     * Load all GLOBALS for the current database in focus.
     *
     * @param  Illuminate\Http\Request  $request
     * @return boolean
     */
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
    
    /**
     * Load all GLOBALS for a specific node's parent database.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  int  $nID
     * @return boolean
     */
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
    
    /**
     * Check for basic system installation and setup before most other things.
     *
     * @return string
     */
    public function checkSystemInit()
    {
        if (!session()->has('chkSysInit') 
            || $GLOBALS["SL"]->REQ->has('refresh')) {
            if (!$GLOBALS["SL"]->REQ->has('cssLoaded')
                && !file_exists('../storage/app/sys/sys2.min.js')) {
                echo '<div style="display: none;">'
                    . '<iframe src="/css-reload" ></iframe>'
                    . '</div>'
                    . '<script type="text/javascript"> '
                    . 'setTimeout("window.location=\'?cssLoaded=1\'", 2000); '
                    . '</script>';
                exit;
            }
            $survInst = new SurvloopInstaller;
            $survInst->checkSysInit();
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
            session()->put('chkSysInit', 1);
            session()->save();
        }
        return '';
    }
    
    public function initCustViews(Request $request)
    {
        $chk = SLDefinitions::where('def_database', 1)
            ->where('def_set', 'Blurbs')
            ->where('def_subset', 'Footer')
            ->first();
        if ($chk && isset($chk->def_description) 
            && trim($chk->def_description) != '') {
            $GLOBALS["SL"]->sysOpts["footer-master"] = $chk->def_description;
        }
        $this->loadCustLoop($request, 1, 1, false);
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
    
    public function freshUser(Request $request)
    {
        $this->survloopInit($request, '/fresh/creator', false);
        $GLOBALS["SL"]->sysOpts["signup-instruct"] = '<h2 class="mT5 mB0">'
            . 'Create Admin Account</h2>';
        $content = '<center><div class="treeWrapForm pT30 mBn20">
            <h1 class="slBlueDark">' . ((isset($GLOBALS["SL"]->sysOpts["site-name"])) 
                    ? $GLOBALS["SL"]->sysOpts["site-name"] : 'Survloop') 
                . ' Installed!</h1><h4>All Out Data Are Belong...</h4>
            <p>Please create the first admin super user account.</p></div></center>';
        if (isset($GLOBALS["SL"]->sysOpts["app-url"])) {
            $http = $_SERVER["HTTP_HOST"];
            if ($GLOBALS["SL"]->sysOpts["app-url"] != 'http://' . $http
                && $GLOBALS["SL"]->sysOpts["app-url"] != 'https://' . $http) {
                SLDefinitions::where('def_database', 1)
                    ->where('def_set', 'System Settings')
                    ->whereIn('def_subset', ['app-url', 'logo-url'])
                    ->update([ "def_description" =>  'http://' . $http ]);
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
    
    public function initSearcher($force = false)
    {
        if ($this->searcher === null || $force) {
            $this->loadCustSearcher();
            $this->copyUserToSearcher();
        }
        return true;
    }
    
    public function searchPrep(Request $request, $treeID = 1)
    {
        $this->loadTree($treeID);
        $this->initSearcher();
        $this->searcher->getSearchFilts();
        $this->searcher->getAllPublicCoreIDs();
        $this->chkRecsPub($request);
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
            && $GLOBALS["SL"]->sysOpts["cust-abbr"] != 'Survloop') {
            $custClass = $GLOBALS["SL"]->sysOpts["cust-vend"] . "\\" 
                . $GLOBALS["SL"]->sysOpts["cust-abbr"] . "\\Controllers\\" 
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
            echo view(
                'vendor.survloop.js.redir', 
                [ "redir" => $redir ]
            )->render();
            exit;
        }
    }
    
    protected function setNotif($msg = '', $type = 'info')
    {
        session()->put('sessMsg',     $msg);
        session()->put('sessMsgType', 'alert-' . $type);
        session()->save();
        if ($type == 'danger' && isset($GLOBALS["SL"])) {
            $GLOBALS["SL"]->x["hasSessMsgError"] = true;
        }
        return true;
    }
    
    
    // this should really be done using migrations
    protected function survSysChecks()
    {
        if (!session()->has('survSysChecks') 
            || $GLOBALS["SL"]->REQ->has('refresh')) {
            $GLOBALS["SL"]->clearOldDynascript();
            session()->put('survSysChecks', 1);
            session()->save();
        }
        $admMenuOnLoad = 1;
        if (session()->has('admMenuOpen')
            && intVal(session()->get('admMenuOpen')) == 0) {
            $admMenuOnLoad = 0;
        }
        $GLOBALS["SL"]->setAdmMenuOnLoad($admMenuOnLoad);
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
    
    public function sendEmail($emaContent, $emaSubject, $emaTo = [], 
        $emaCC = [], $emaBCC = [], $repTo = [], $attach = [])
    {
        $mail = "Illuminate\\Support\\Facades\\Mail::send(
            'vendor.survloop.emails.master', 
            [
                'emaSubj'    => \$emaSubject,
                'emaContent' => \$emaContent,
                'cssColors'  => \$GLOBALS['SL']->getCssColorsEmail()
            ], 
            function (\$m) { \$m->subject('" 
            . str_replace("'", "\\'", $emaSubject) . "')";
        if (sizeof($emaTo) > 0) {
            foreach ($emaTo as $i => $eTo) {
                $mail .= "->to('" . $eTo[0] . "'" 
                    . ((trim($eTo[1]) != '') 
                        ? ", '" . str_replace("'", "\\'", $eTo[1]) . "'" 
                        : "") 
                    . ")";
            }
        }
        if (sizeof($emaCC) > 0) {
            foreach ($emaCC as $eTo) {
                $mail .= "->cc('" . $eTo[0] . "'" 
                    . ((trim($eTo[1]) != '') 
                        ? ", '" . str_replace("'", "\\'", $eTo[1]) . "'" 
                        : "") 
                    . ")";
            }
        }
        if (sizeof($emaBCC) > 0) {
            foreach ($emaBCC as $eTo) {
                $mail .= "->bcc('" . $eTo[0] . "'" 
                    . ((trim($eTo[1]) != '') 
                        ? ", '" . str_replace("'", "\\'", $eTo[1]) . "'" 
                        : "") 
                    . ")";
            }
        }
        if (!isset($repTo[0]) || is_array($repTo[0]) || trim($repTo[0]) == '') {
            $repTo[0] = 'info@' . strtolower($GLOBALS["SL"]->getParentDomain());
        }
        if (!isset($repTo[1]) || is_array($repTo[1]) || trim($repTo[1]) == '') {
            $repTo[1] = $GLOBALS["SL"]->sysOpts["site-name"];
        }
        $mail .= "->replyTo('" . $repTo[0] . "'" 
            . ((trim($repTo[1]) != '') 
                ? ", '" . str_replace("'", "\\'", $repTo[1]) . "'" 
                : "") 
            . ")";
        $errMsg = '';
        if (sizeof($attach) > 0) {
            foreach ($attach as $a => $att) {
                if (isset($att->fileStore) 
                    && trim($att->fileStore) != ''
                    && isset($att->fileDeliver) 
                    && trim($att->fileDeliver) != '') {
                    if (!file_exists($att->fileStore)) {
                        $errMsg = 'Could not find file to attach (' . $att->fileDeliver . ')';
                        $this->setNotif($errMsg, 'danger');
                    }
                    $mail .= "->attach('" . $att->fileStore . "', [
                        'as' => '" . $att->fileDeliver . "',
                        'mime' => 'application/pdf',
                    ])";
                } elseif (is_string($att)) {
                    $mail .= "->attach('" . $att . "')";
                }
            }
        }
        $mail .= "; });";
        if ($GLOBALS["SL"]->isHomestead()) {
            echo '<br /><br /><br /><div class="container"><h2>' 
                . $emaSubject . '</h2>' . $emaContent 
                . '<hr><hr></div><pre>' . $mail . '</pre><hr><br />';
            return true;
        }
        if ($errMsg == '') {
            eval($mail);
        }
        return true;
    }
    
    // This function should be migrated to sendEmail() ...
    protected function sendNewEmailSimple($bod, $subj, $emaTo = '', 
        $emaID = -3, $tID = -3, $cID = -3, $usrTo = -3)
    {
        $eTo = [$this->getEmailTo($emaTo)];
        $this->sendEmail($bod, $subj, $eTo);
        return $this->logEmailSent($bod, $subj, $emaTo, $emaID, $tID, $cID, $usrTo);
    }
    
    protected function sendNewEmailFromCurrUser($bod, $subj, 
        $emaTo = '', $emaID = -3, $tID = -3, $cID = -3, 
        $usrTo = -3, $cc = '', $bcc = '', $attach = [])
    {
        $eTo = [$this->getEmailTo($emaTo)];
        $eCC = $eBCC = [];
        $eFrom = $this->getEmailFromCurrUser();
        $eCC[] = $this->getEmailTo($eFrom);
        if (trim($cc) != '') {
            $eCC[] = $this->getEmailTo($cc);
        }
        if (trim($bcc) != '') {
            $eBCC[] = $this->getEmailTo($bcc);
        }
        $this->sendEmail($bod, $subj, $eTo, $eCC, $eBCC, $eFrom, $attach);
        return $this->logEmailSent($bod, $subj, $emaTo, $emaID, $tID, $cID, $usrTo, $attach);
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
    
    protected function logEmailSent($bod, $subj, $emaTo = '', 
        $emaID = -3, $tID = -3, $cID = -3, $usrTo = -3, $attach = [])
    {
        $emailRec = new SLEmailed;
        $emailRec->emailed_email_id  = (($emaID > 0) ? $emaID : 0);
        $emailRec->emailed_tree      = (($tID > 0)  ? $tID  : 0);
        $emailRec->emailed_rec_id    = (($cID > 0)  ? $cID  : 0);
        $emailRec->emailed_to        = trim($emaTo);
        $emailRec->emailed_to_user   = (($usrTo > 0)  ? $usrTo  : 0);
        $emailRec->emailed_from_user = ((Auth::user() && isset(Auth::user()->id))
            ? Auth::user()->id : 0);
        $emailRec->emailed_subject   = $subj;
        $emailRec->emailed_body      = $bod;
        $emailRec->emailed_attach    = '';
        if (sizeof($attach) > 0) {
            foreach ($attach as $att) {
                if (trim($emailRec->emailed_attach) != '') {
                    $emailRec->emailed_attach .= ', ';
                }
                if (isset($att->fileStore) 
                    && trim($att->fileStore) != ''
                    && isset($att->fileDeliver) 
                    && trim($att->fileDeliver) != '') {
                    $emailRec->emailed_attach .= $att->fileDeliver;
                } elseif (is_string($att) && trim($att) != '') {
                    $pos = strrpos($att, '/');
                    if ($pos >= 0) {
                        $att = substr($att, $pos+1);
                    }
                    $emailRec->emailed_attach .= $att;
                }
            }
        }
        $emailRec->save();
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
            ->select('tbl_name', 'tbl_opts')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $tbl) {
                if ($tbl->tbl_opts%5 == 0) {
                    $ret[] = $tbl->tbl_name;
                }
            }
        }
        return $ret;
    }
    
    public function tblsInPackageCustom()
    {
        return [];
    }
    
}
