<?php
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\Models\User;
use App\Models\SLDatabases;
use App\Models\SLTree;
use App\Models\SLNode;

use SurvLoop\Controllers\SurvLoopController;

class AdminController extends SurvLoopController
{
    public $classExtension = 'AdminController';
    
    protected $CustReport  = array();
    protected $adminNav    = array();
    protected $admMenuData = array();
    protected $pageIsAdmin = true;
    protected $admInitRun  = false;
    
    protected function admControlInit(Request $request, $currPage = '')
    {
        if (!$this->admInitRun) {
            $this->admInitRun = true;
            $this->survLoopInit($request, $currPage, false);
            if (!$this->v["user"] || intVal($this->v["user"]->id) <= 0
                || !$this->v["user"]->hasRole('administrator|staff|databaser|brancher|volunteer')) {
                echo view('vendor.survloop.inc-js-redirect-home', $this->v)->render();
                exit;
            }
            if (isset($GLOBALS["DB"]->sysOpts["cust-abbr"]) && $GLOBALS["DB"]->sysOpts["cust-abbr"] != 'SurvLoop') {
                eval("\$this->CustReport = new "
                    . $GLOBALS["DB"]->sysOpts["cust-abbr"] . "\\Controllers\\" 
                    . $GLOBALS["DB"]->sysOpts["cust-abbr"] . "Report(\$request);");
                $this->checkCurrPage();
            }
            list($this->v["yourUserInfo"], $this->v["yourContact"]) = $this->initPowerUser(Auth::user()->id);
            $this->v["admMenu"] = $this->getAdmMenu($this->v["currPage"]);
            $this->v["belowAdmMenu"] = '';
            $this->v["currState"] = '';
            if (isset($this->v["yourContact"]->PrsnAddressState)) {
                $this->v["currState"] = $this->v["yourContact"]->PrsnAddressState;
            }
            $this->loadSearchSuggestions();
            $this->initExtra($request);
            $this->initCustViews();
            $this->logPageVisit();
        }
        return true;
    }
    
    protected function checkCurrPage()
    {
        if (sizeof($this->CustReport) > 0) {
            $custPage = $this->CustReport->getCurrPage();
            if (trim($custPage) != '/') $this->v["currPage"] = $custPage;
        }
        return true;
    }
    
    protected function loadSearchSuggestions()
    {    
        $this->v["searchSuggest"] = array();
        return true;
    }
    
    public function initPowerUser($uID = -3)
    {
        return [[ ], [ ]];
    }
    
    protected function tweakAdmMenu($currPage = '')
    {
        return true; 
    }
    
    protected function loadDbTreeShortNames()
    {
        $treeName = ((isset($GLOBALS["DB"]->treeName)) ? $GLOBALS["DB"]->treeName : '');
        $dbName = ((isset($GLOBALS["DB"]->dbRow->DbName)) ? $GLOBALS["DB"]->dbRow->DbName : '');
        if (strlen($dbName) > 20 && isset($GLOBALS["DB"]->dbRow->DbName)) {
            $dbName = str_replace($GLOBALS["DB"]->dbRow->DbName, 
                str_replace('_', '', $GLOBALS["DB"]->dbRow->DbPrefix), $dbName);
        }
        return array($treeName, $dbName);
    }
    
    protected function loadAdmMenu()
    {
        list($treeMenu, $dbMenu) = $this->loadAdmMenuBasics();
        return [
            [
                '/dashboard',
                'Dashboard',
                1,
                []
            ], [
                'javascript:;',
                'Submissions <span class="pull-right"><i class="fa fa-star"></i></span>',
                1,
                [
                    [
                        '/dashboard/subs/all',
                        'All Complete',
                        1,
                        []
                    ], [
                        '/dashboard/subs/incomplete',
                        'Incomplete Sessions',
                        1,
                        []
                    ], [
                        '/dashboard/subs/emails',
                        'Settings & Emails',
                        1,
                        []
                    ]
                ]
            ], 
            $treeMenu,
            $dbMenu
        ];
    }
    
    protected function loadAdmMenuBasics()
    {
        list($treeName, $dbName) = $this->loadDbTreeShortNames();
        $treeMenu = [
            'javascript:;',
            'Experience <span class="pull-right"><i class="fa fa-snowflake-o"></i></span>',
            1,
            [
                [
                    '/dashboard/tree/switch',
                    '<i>Current: ' . $treeName . '</i>',
                    1,
                    []
                ], [
                    '/dashboard/tree/map?all=1',
                    'Experience Map',
                    1,
                    []
                ], [
                    '/dashboard/tree/conds',
                    'Conditions / Filters',
                    1,
                    []
                ], [
                    '/dashboard/tree/data',
                    'Data Structures',
                    1,
                    []
                ], [
                    '/dashboard/tree/xmlmap',
                    'Data XML Map',
                    1,
                    []
                ], [
                    '/dashboard/tree/workflows',
                    'Workflows',
                    1,
                    []
                ], [
                    '/test" target="_blank',
                    'Test Experience',
                    1,
                    []
                ], [
                    '/dashboard/tree',
                    'Session Stats',
                    1,
                    []
                ],  [
                    '/dashboard/tree/stats?all=1',
                    'Response Stats',
                    1,
                    []
                ]
            ]
        ];
        $dbMenu = [
            'javascript:;',
            'Database <span class="pull-right"><i class="fa fa-database"></i></span>',
            1,
            [
                [
                    '/dashboard/db/switch',
                    '<i>Current: ' . $dbName . '</i>',
                    1,
                    []
                ], [
                    '/dashboard/db/all',
                    'Database Design',
                    1,
                    []
                ], [
                    '/dashboard/db/bus-rules',
                    'Business Rules',
                    1,
                    []
                ], [
                    '/dashboard/db/definitions',
                    'Definitions',
                    1,
                    []
                ], [
                    '/dashboard/db',
                    'Tables Overview',
                    1,
                    []
                ], [
                    '/dashboard/db/diagrams',
                    'Table Diagrams',
                    1,
                    []
                ], [
                    '/dashboard/db/field-matrix',
                    'Field Matrix',
                    1,
                    []
                ], [
                    '/dashboard/db/export',
                    'Export / Install',
                    1,
                    []
                ]
            ]
        ];
        return [$treeMenu, $dbMenu];
    }
    
    protected function getAdmMenuLoc($currPage)
    {
        $this->admMenuData["currNavPos"] = array(0, -1, -1, -1);
        if (sizeof($this->admMenuData["adminNav"]) > 0) {
            foreach ($this->admMenuData["adminNav"] as $i => $nav) {
                if ($nav[0] == $currPage) {
                    $this->admMenuData["currNavPos"] = array($i, -1, -1, -1);
                } elseif (sizeof($nav[3]) > 0) {
                    foreach ($nav[3] as $j => $nA) {
                        if ($nA[0] == $currPage) {
                            $this->admMenuData["currNavPos"] = array($i, $j, -1, -1);
                        } elseif (sizeof($nA[3]) > 0) {
                            foreach ($nA[3] as $k => $nB) {
                                if ($nB[0] == $currPage) {
                                    $this->admMenuData["currNavPos"] = array($i, $j, $k, -1);
                                } elseif (sizeof($nB[3]) > 0) {
                                    foreach ($nB[3] as $l => $nC) {
                                        if ($nC[0] == $currPage) {
                                            $this->admMenuData["currNavPos"] = array($i, $j, $k, $l);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return ($this->admMenuData["currNavPos"][0] != 0 
            || $this->admMenuData["currNavPos"][1] != 0
            || $this->admMenuData["currNavPos"][2] != 0 
            || $this->admMenuData["currNavPos"][3] != 0);
    }
    
    
    
    
    public function index(Request $request)
    {
        return $this->dashHome($request);
    }
    
    public function dashHome(Request $request)
    {
        $this->admControlInit($request, '/dashboard');
        if (!$this->v["user"]->hasRole('administrator|staff|databaser|brancher')) {
            if ($this->v["user"]->hasRole('volunteer')) {
                return redirect('/volunteer');
            }
            return redirect('/');
        }
        $dbRow = SLDatabases::find(1);
        $this->v["orgMission"] = $dbRow->DbMission;
        $this->v["adminNav"] = $this->admMenuData["adminNav"];
        return view('vendor.survloop.admin.dashboard', $this->v);
    }
    
    
    
    public function switchDB(Request $request, $dbID = -3)
    {
        $this->admControlInit($request, '/dashboard/db/switch');
        if ($dbID > 0) {
            $this->switchDatabase($dbID, '/dashboard/db/switch');
            return redirect('/dashboard/db/all');
        }
        $this->v["myDbs"] = SLDatabases::orderBy('DbName', 'asc')
            //->whereIn('DbUser', [ 0, $this->v["user"]->id ])
            ->get();
        return view('vendor.survloop.admin.db.switch', $this->v);
    }
    
    public function switchTreeAdmin(Request $request, $treeID = -3)
    {
        $this->admControlInit($request, '/dashboard/tree/switch');
        if ($treeID > 0) {
            $this->switchTree($treeID, '/dashboard/tree/switch');
            return redirect('/dashboard/tree/map?all=1');
        }
        $this->v["myTrees"] = SLTree::where('TreeDatabase', $GLOBALS["DB"]->dbID)
            ->where('TreeType', 'NOT LIKE', 'Primary Public XML')
            ->where('TreeType', 'NOT LIKE', 'Other Public XML')
            ->orderBy('TreeName', 'asc')
            ->get();
        $this->v["myTreeNodes"] = [];
        if ($this->v["myTrees"] && sizeof($this->v["myTrees"]) > 0) {
            foreach ($this->v["myTrees"] as $tree) {
                $nodes = SLNode::where('NodeTree', $tree->TreeID)
                    ->select('NodeID')
                    ->get();
                $this->v["myTreeNodes"][$tree->TreeID] = sizeof($nodes);
            }
        }
        return view('vendor.survloop.admin.tree.switch', $this->v);
    }
    
    
    
    public function instructList(Request $request)
    {
        $this->admControlInit($request, '/dashboard/instruct');
        $this->v["instructRows"] = SLDefinitions::where('DefSet', 'Instructions')
            ->orderBy('DefSubset')
            ->get();
        return view('vendor.survloop.instructs', $this->v);
    }
    
    protected function instructLoad($instID)
    {
        return SLDefinitions::where('DefID', $instID)
            ->where('DefDatabase', $this->dbID)
            ->where('DefSet', 'Instructions')
            ->first();
    }
    
    public function instructEdit(Request $request, $instID)
    {
        $this->admControlInit($request, '/dashboard/instruct');
        $this->v["instructRow"] = $this->instructLoad($instID);
        return view('vendor.survloop.admin.volun.instructEdit', $this->v);
    }
    
    public function instructNew(Request $request)
    {
        $this->admControlInit($request, '/dashboard/instruct');
        $instruct = new SLDefinitions;
        $instruct->DefDatabase = $this->dbID;
        $instruct->DefSet      = 'Instructions';
        $instruct->DefSubset   = $request->newSpot;
        $instruct->save();
        return redirect('/dashboard/instruct/'.$instruct->DefID);
    }
    
    public function instructEditSave(Request $request)
    {
        $instruct = $this->instructLoad($request->DefID);
        $instruct->DefSubset      = $request->DefSubset;
        $instruct->DefDescription = $request->DefDescription;
        $instruct->save();
        return redirect('/dashboard/instruct/'.$instruct->DefID);
    }
    
    
    
    
    /**
     * Update the user's profile.
     *
     * @param  Request  $request
     * @return Response
     */
    public function updateProfile(Request $request)
    {
        if ($request->user()) {
            // $request->user() returns an instance of the authenticated user...
            if ($request->user()->id == $request->uID || $request->user()->hasRole('administrator')) {
                $user = User::find($request->uID);
                $user->name = $request->name;
                $user->email = $request->email;
                $user->save();
                if ($request->roles && sizeof($request->roles) > 0) {
                    foreach ($user->rolesRanked as $i => $role) {
                        if (in_array($role, $request->roles)) {
                            if (!$user->hasRole($role)) {
                                $user->assignRole($role);
                            }
                        } elseif ($user->hasRole($role)) {
                            $user->revokeRole($role);
                        }
                    }
                } else { // no roles selected, delete all that exist
                    foreach ($user->rolesRanked as $i => $role) {
                        if ($user->hasRole($role)) {
                            $user->revokeRole($role);
                        }
                    }
                }
            }
        }
        return redirect('/dashboard/user/'.$request->uID);
    }
    
    public function showProfile($uid) 
    {
        $user = User::find($uid);
        $data = [
            'profileUser' => $user,
            "currAdmPage" => '',
            "user" => Auth::user()
        ];
        return view('profile', $data );
    }
    
    
    
    
    
    public function getCSS(Request $request)
    {
        $this->survLoopInit($request);
        $css = [];
        $cssRaw = SLDefinitions::where('DefDatabase', $this->dbID)
            ->where('DefSet', 'Style Settings')
            ->orderBy('DefOrder')
            ->get();
        if ($cssRaw && sizeof($cssRaw) > 0) {
            foreach ($cssRaw as $i => $c) {
                $cssRaw[$c->DefSubset] = $c->DefDescription;
            }
        }
        Storage::put(
            public_path()."/survloop/sys.css", 
            view('vendor/survloop/inc-custom-css', [ "css" => $css ])->render()
        );
        return ;
    }
    
    
    
    
    protected function eng2data($name)
    {
        return preg_replace("/[^a-zA-Z0-9]+/", "", ucwords($name));
    }
    
    protected function eng2abbr($name)
    {
        $abbr = preg_replace("/[^A-Z]+/", "", $name);
        if (strlen($abbr) > 1) return $abbr;
        return substr(preg_replace("/[^a-zA-Z0-9]+/", "", $name), 0, 3);
    }
    
    protected function isCoreTbl($tblID)
    {
        $chkCore = SLTree::where('TreeCoreTable', '=', $tblID)
            ->get();
        return ($chkCore && sizeof($chkCore) > 0);
    }
    
    protected function exportMysqlTblCoreStart($tbl)
    {
        return "CREATE TABLE IF NOT EXISTS `" 
            . $GLOBALS["DB"]->dbRow->DbPrefix . $tbl->TblName . "` ( "
            . "  `" . $tbl->TblAbbr . "ID` int(11) NOT NULL AUTO_INCREMENT, \n";
    }
    
    protected function exportMysqlTblCoreFinish($tbl)
    {
        return "  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP , \n"
            . "  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP , \n"
            . "  PRIMARY KEY (`" . $tbl->TblAbbr . "ID`) )"
            . "  ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    }
    
    protected function chkModelsFolder()
    {
        if (!file_exists('../app/Models')) mkdir('../app/Models');
        if (!file_exists('../app/Models/' . $GLOBALS["DB"]->sysOpts["cust-abbr"])) {
            mkdir('../app/Models/' . $GLOBALS["DB"]->sysOpts["cust-abbr"]);
        }
        return true;
    }
    
    
    
    
    
    function manageEmails(Request $request)
    {
        $this->admControlInit($request, '/dashboard/subs/emails');
        $this->v["settings"] = SLDefinitions::where('DefSet', 'Custom Settings')
            ->orderBy('DefOrder', 'asc')
            ->get();
        if ($request->has('savingSettings') && sizeof($this->v["settings"]) > 0) {
            foreach ($this->v["settings"] as $i => $s) {
                if ($request->has('setting'.$i.'')) {
                    $s->DefValue = $request->get('setting'.$i.'');
                    $s->save();
                }
            }
        }
        $this->v["emailList"] = OPCzComplaintEmails::orderBy('ComEmailName', 'asc')->orderBy('ComEmailType', 'asc')->get();
        return view('vendor.OPC.admin.complaints.email-manage', $this->v);
    }
    
    function manageEmailsForm(Request $request, $emailID = -3)
    {
        $this->admControlInit($request, '/dashboard/subs/emails');
        $this->v["currEmailID"] = $emailID;
        $this->v["currEmail"] = new OPCzComplaintEmails;
        if ($emailID > 0) {
            $this->v["currEmail"] = OPCzComplaintEmails::find($emailID);
        }
        return view('vendor.OPC.admin.complaints.email-form', $this->v);
    }
    
    function manageEmailsPost(Request $request, $emailID)
    {
        if ($request->has('emailType')) {
            $currEmail = new OPCzComplaintEmails;
            if ($request->emailID > 0 && $request->emailID == $emailID) {
                $currEmail = OPCzComplaintEmails::find($request->emailID);
            }
            $currEmail->ComEmailType     = $request->emailType;
            $currEmail->ComEmailName     = $request->emailName;
            $currEmail->ComEmailSubject = $request->emailSubject;
            $currEmail->ComEmailBody     = $request->emailBody;
            $currEmail->ComEmailOpts         = 1;
            $currEmail->ComEmailCustomSpots = 0;
            if (trim($currEmail->ComEmailBody) != '' 
                && strpos($currEmail->ComEmailBody, '[{ Evaluator Message }]') !== false) {
                $customSpotSplit = explode('[{ Evaluator Message }]', $currEmail->ComEmailBody);
                $currEmail->ComEmailCustomSpots = sizeof($customSpotSplit)-1;
            }
            $currEmail->save();
        }
        return redirect('/dashboard/subs/emails');
    }
    
    
}
