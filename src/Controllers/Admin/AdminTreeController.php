<?php
/**
  * AdminTreeController is the admin class responsible for the tools to edit SurvLoop's tree designs.
  * (Ideally, this will eventually be replaced by SurvLoop-generated surveys.)
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers\Admin;

use DB;
use Illuminate\Http\Request;
use SurvLoop\Models\SLDatabases;
use SurvLoop\Models\SLTables;
use SurvLoop\Models\SLDefinitions;
use SurvLoop\Models\SLTree;
use SurvLoop\Models\SLNode;
use SurvLoop\Models\SLDataSubsets;
use SurvLoop\Models\SLDataHelpers;
use SurvLoop\Models\SLDataLinks;
use SurvLoop\Models\SLConditions;
use SurvLoop\Models\SLConditionsVals;
use SurvLoop\Models\SLConditionsArticles;
use SurvLoop\Models\SLUsersRoles;
use SurvLoop\Models\SLNodeSaves;
use SurvLoop\Models\SLNodeSavesPage;
use SurvLoop\Controllers\SessAnalysis;
use SurvLoop\Controllers\SurvLoopInstaller;
use SurvLoop\Controllers\SystemDefinitions;
use SurvLoop\Controllers\Globals\Globals;
use SurvLoop\Controllers\Tree\TreeSurvAPI;
use SurvLoop\Controllers\Tree\TreeSurvAdmin;
use SurvLoop\Controllers\Admin\AdminController;

class AdminTreeController extends AdminController
{
    
    protected function initExtra(Request $request)
    {
    	if ($this->v["uID"] > 0) {
    		$this->v["allowEdits"] = $this->v["user"]->hasRole('administrator|databaser');
        }
        $this->v["adminOverOpts"] = ((session()->has('adminOverOpts')) ? session()->get('adminOverOpts') : '');
        if (trim($this->v["currPage"][0]) == '') {
            $this->v["currPage"][0] = '/dashboard/tree';
        }
        
        if (!isset($this->v["treeClassAdmin"])) {
            $this->v["treeClassAdmin"] = new TreeSurvAdmin($request);
        }
        $this->v["treeClassAdmin"]->loadTree($GLOBALS["SL"]->treeID, $request);
        $this->initExtraCust();
        
        if (!session()->has('chkCoreTbls') || $request->has('refresh')) {
            $userTbl = $GLOBALS["SL"]->loadUsrTblRow();
            $trees = SLTree::where('TreeDatabase', $GLOBALS["SL"]->dbID)
                ->where('TreeCoreTable', '>', 0)
                ->get();
            if ($trees->isNotEmpty()) {
                foreach ($trees as $tree) {
                    $coreTbl = SLTables::find($tree->TreeCoreTable);
                    $GLOBALS["SL"]->initCoreTable($coreTbl, $userTbl);
                }
            }
            $this->allStdCondition('#IsAdmin', 'The user is currently logged in as an administrator.');
            $this->allStdCondition('#IsNotAdmin', 'The user is not currently logged in as an administrator.');
            $this->allStdCondition('#IsStaff', 'The user is currently logged in as a staff user.');
            $this->allStdCondition('#IsStaffOrAdmin', 'The user is currently logged in as a staff or admin user.');
            $this->allStdCondition('#IsPartner', 'The user is currently logged in as a partner.');
            $this->allStdCondition('#IsVolunteer', 'The user is currently logged in as a volunteer.');
            $this->allStdCondition('#IsBrancher', 'The user is currently logged in as a database manager.');
            $this->allStdCondition('#NodeDisabled', 'This node is not active (for the public).');
            $this->allStdCondition('#IsLoggedIn', 'Complainant is currently logged into the system.');
            $this->allStdCondition('#IsNotLoggedIn', 'Complainant is not currently logged into the system.');
            $this->allStdCondition('#IsOwner', 'The user is currently logged is the owner of this record.');
            $this->allStdCondition('#IsProfileOwner', 'The user is currently logged in owns this user profile.');
            $this->allStdCondition('#IsPrintable', 'The current page view is intended to be printable.');
            $this->allStdCondition('#IsPrintInFrame', 'The current page view is printed into frame/ajax/widget.');
            $this->allStdCondition('#IsDataPermPublic', 'The current data permissions are set to public.');
            $this->allStdCondition('#IsDataPermPrivate', 'The current data permissions are set to private.');
            $this->allStdCondition('#IsDataPermSensitive', 'The current data permissions are set to sensitive.');
            $this->allStdCondition('#IsDataPermInternal', 'The current data permissions are set to internal.');
            $this->allStdCondition('#HasTokenDialogue', 'Current page load includes an access token dialogue.');
            $this->allStdCondition('#EmailVerified', 'Current user\'s email address has been verified.');
            $this->allStdCondition('#TestLink', 'Current page url parameters includes ?test=1.');
            $this->allStdCondition('#NextButton', 'Current page load results from clicking the survey\'s next button.');
            //$this->allStdCondition('#HasUploads', 'Current core table record has associated uploads.');
            $trees = SLTree::where('TreeType', 'Page')->get();
            if ($trees->isNotEmpty()) {
                foreach ($trees as $tree) {
                    $this->v["treeClassAdmin"]->updateTreeOpts($tree->TreeID);
                }
            }
            session()->put('chkCoreTbls', 1);
        }
        if (!isset($GLOBALS["SL"]->treeRow->TreeRoot) || $GLOBALS["SL"]->treeRow->TreeRoot <= 0) {
            $this->createRootNode($GLOBALS["SL"]->treeRow);
        }
        set_time_limit(180);
        return true;
    }
    
    protected function createRootNode($treeRow)
    {
        if (!isset($treeRow->TreeID)) {
            return -3;
        }
        $chk = SLNode::where('NodeTree', $treeRow->TreeID)
            ->where('NodeParentID', -3)
            ->first();
        if ($chk && isset($chk->NodeID)) {
            return -3;
        }
        $newRoot = new SLNode;
        $newRoot->NodeTree     = $treeRow->TreeID;
        $newRoot->NodeParentID = -3;
        $coreTbl = ((intVal($treeRow->TreeCoreTable) > 0 && isset($GLOBALS["SL"]->tbl[$treeRow->TreeCoreTable])) 
            ? $GLOBALS["SL"]->tbl[$treeRow->TreeCoreTable] : '');
        if ($treeRow->TreeType == 'Page') {
            $newRoot->NodeType = 'Page';
            $newRoot->NodeDataBranch = $coreTbl;
            if (!isset($treeRow->TreeSlug) || trim($treeRow->TreeSlug) == '') {
                $treeRow->TreeSlug = $GLOBALS["SL"]->slugify($treeRow->TreeName);
                $treeRow->save();
            }
            $newRoot->NodePromptNotes = $treeRow->TreeSlug;
            $newRoot->NodePromptAfter = $treeRow->TreeName . '::M::::M::::M::';
            $newRoot->NodeCharLimit = -1;
        } else {
            $newRoot->NodeType = 'Data Manip: New';
            $newRoot->NodeDataBranch = $coreTbl;
        }
        $newRoot->save();
        $treeRow->TreeRoot = $newRoot->NodeID;
        if ($treeRow->TreeType == 'Page') {
            $treeRow->TreeFirstPage = $treeRow->TreeLastPage = $newRoot->NodeID;
        }
        $treeRow->save();
        if ($treeRow->TreeType == 'Page') {
            $firstReal = new SLNode;
            $firstReal->NodeTree       = $treeRow->TreeID;
            $firstReal->NodeParentID   = $newRoot->NodeID;
            $firstReal->NodeType       = 'Instructions';
            $firstReal->NodePromptText = '<h2>Welcome to ' . $treeRow->TreeName . '.</h2>' . "\n"
                . '<p>Edit this node to fill in your page! This node could be your entire page, '
                . 'or just one little component.</p>';
            $firstReal->save();
        }
        return $newRoot;
    }
    
    protected function allStdCondition($tag, $desc)
    {
        $chk = SLConditions::where('CondDatabase', 0)
            ->where('CondTag', $tag)
            ->get();
        if ($chk->isEmpty()) {
            $newCond = new SLConditions;
            $newCond->CondDatabase = 0;
            $newCond->CondTag = $tag;
            $newCond->CondDesc = $desc;
            $newCond->CondOperator = 'CUSTOM';
            $newCond->save();
        }
        return true;
    }
    
    protected function initExtraCust()
    {
        return true;
    }
    
    protected function loadBelowAdmMenu()
    {
        return $this->loadTreesPagesBelowAdmMenu();
    }
    
    public function index(Request $request, $treeID = -3)
    {
        $this->initLoader();
        $this->loader->syncDataTrees($request, -3, $treeID);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/map?all=1&alt=1');
        if (!$this->checkCache()) {
            $this->chkAllCoreTbls();
            $this->v["printTree"] = $this->v["treeClassAdmin"]->adminPrintFullTree($request);
            $this->v["ipLegal"] = view('vendor.survloop.elements.dbdesign-legal', 
                [ "sysOpts" => $GLOBALS["SL"]->sysOpts ])->render();
            $this->v["content"] = view('vendor.survloop.admin.tree.tree', $this->v)->render();
            $this->saveCache();
        }
        $treeAbout = view('vendor.survloop.admin.tree.tree-about', [ "showAbout" => false ])->render();
        $this->v["content"] = $treeAbout . $this->v["content"];
        if ($request->has('refresh')) {
            $this->v["treeClassAdmin"]->createProgBarJs();
            $GLOBALS["SL"]->pageJAVA .= 'setTimeout('
                . '"document.getElementById(\'hidFrameID\').src=\'/css-reload\'", 2000);';
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    public function adminPrintFullTreePublic(Request $request, $treeSlug = '')
    {
        $tree = SLTree::where('TreeSlug', $treeSlug)
            ->where('TreeType', 'Survey')
            ->get();
        if ($tree->isNotEmpty()) {
            foreach ($tree as $t) {
                if ($t->TreeOpts%Globals::TREEOPT_ADMIN > 0) { // no admin trees made public [for now]
                    $this->treeID = $t->TreeID;
                    $this->dbID = $t->TreeDatabase;
                    $GLOBALS["SL"] = new Globals($request, $this->dbID, $this->treeID, $this->treeID);
                }
            }
        }
        $this->survLoopInit($request, '/tree/' . $treeSlug);
        if (!$this->checkCache()) {
            $this->v["treeClassAdmin"]->loadTreeNodeStats();
            $GLOBALS["SL"]->x["hideDisabledNodes"] = true;
            $this->v["content"] = '<div class="container">';
            
            $custHeader = $GLOBALS["SL"]->getBlurb('Tree Map Header: ' . $GLOBALS["SL"]->treeRow->TreeName);
            if (trim($custHeader) != '') {
                $readMore = '<div class="p15"><a href="javascript:;" id="hidivBtnReadMore" class="hidivBtn"'
                    . '>About this map</a><div id="hidivReadMore" class="disNon">';
                if (strpos($custHeader, '[[TreeStats]]') !== false) {
                    $this->v["content"] .= str_replace('[[TreeStats]]', $GLOBALS["SL"]->printTreeNodeStats(true, true, true), $custHeader) 
                        . $readMore . view('vendor.survloop.elements.print-tree-map-desc')->render() . '</div></div>';
                } else {
                    $this->v["content"] .= $custHeader . $readMore . view('vendor.survloop.elements.print-tree-map-desc')->render() 
                        . '</div></div><div class="p10"></div>' . $GLOBALS["SL"]->printTreeNodeStats(true, true, true);
                }
                
            } else {
                $this->v["content"] .= view('vendor.survloop.elements.logo-print', [
                        "sysOpts" => $GLOBALS["SL"]->sysOpts,
                        "w100" => true
                        ])->render()
                    . '<h2>' . $GLOBALS["SL"]->treeRow->TreeName . ': Specifications</h2>'
                    . view('vendor.survloop.elements.print-tree-map-desc')->render()
                    . '<div class="p10"></div>'
                    . $GLOBALS["SL"]->printTreeNodeStats(true, true, true);
            }
            $this->v["content"] .= str_replace('Content Chunk, WYSIWYG', 'Content Chunk', 
                    $this->v["treeClassAdmin"]->adminPrintFullTree($request, true))
                . '<a name="licenseInfo"></a><div class="mT20 mB20 p20">' . view('vendor.survloop.elements.dbdesign-legal', [
                    "sysOpts" => $GLOBALS["SL"]->sysOpts
                    ])->render() . '</div></div>';
            $this->saveCache();
        }
        $this->v["isPrint"] = true;
        return view('vendor.survloop.master', $this->v);
    }
    
    public function indexPage(Request $request, $treeID)
    {
        $tree = SLTree::find($treeID);
        if (!$tree || !isset($tree->TreeName)) {
            return $this->redir('/dashboard/pages');
        }
        $this->treeID = $treeID;
        $this->dbID = $tree->TreeDatabase;
        $GLOBALS["SL"] = new Globals($request, $this->dbID, $this->treeID, $this->treeID);
        $this->admControlInit($request, '/dashboard/pages');
        if (!$this->checkCache()) {
            $this->v["printTree"] = $this->v["treeClassAdmin"]->adminPrintFullTree($request);
            $this->v["content"] = view('vendor.survloop.admin.tree.page', $this->v)->render();
            $this->saveCache();
        }
        $treeAbout = view('vendor.survloop.admin.tree.page-about', [ "showAbout" => false ])->render();
        $this->v["content"] = $treeAbout . $this->v["content"];
        return view('vendor.survloop.master', $this->v);
    }                                        
    
    public function treesList(Request $request)
    {
        $this->admControlInit($request, '/dashboard/surveys/list');
        if ($request->has('sub') && $request->has('newTreeName')) {
            $tree = new SLTree;
            $tree->TreeDatabase = $GLOBALS["SL"]->dbID;
            $tree->TreeUser = $this->v["uID"];
            $tree->TreeType = 'Survey';
            $tree->TreeName = trim($request->newTreeName);
            $tree->TreeSlug = trim($request->newTreeSlug);
            if ($tree->TreeSlug == '') {
                $tree->TreeSlug = $GLOBALS["SL"]->slugify($request->newTreeName);
            }
            $tree->TreeOpts = 1;
            if ($request->has('pageAdmOnly') && intVal($request->pageAdmOnly) == 1) {
                $tree->TreeOpts *= 3;
            }
            if ($request->has('pageStfOnly') && intVal($request->pageStfOnly) == 1) {
                $tree->TreeOpts *= Globals::TREEOPT_STAFF;
            }
            if ($request->has('pagePrtOnly') && intVal($request->pagePrtOnly) == 1) {
                $tree->TreeOpts *= Globals::TREEOPT_PARTNER;
            }
            if ($request->has('pageVolOnly') && intVal($request->pageVolOnly) == 1) {
                $tree->TreeOpts *= Globals::TREEOPT_VOLUNTEER;
            }
            $tree->save();
            $treeXML = new SLTree;
            $treeXML->TreeDatabase = $tree->TreeDatabase;
            $treeXML->TreeUser = $tree->TreeUser;
            $treeXML->TreeType = 'Survey XML';
            $treeXML->TreeName = $tree->TreeName;
            $treeXML->TreeSlug = $tree->TreeSlug;
            $treeXML->TreeOpts = $tree->TreeOpts;
            $treeXML->save();
            return $this->redir('/dashboard/surv-' . $tree->TreeID . '/map?all=1&alt=1&refresh=1');
        }
        $this->v["myTrees"] = SLTree::where('TreeDatabase', $GLOBALS["SL"]->dbID)
            ->where('TreeType', 'LIKE', 'Survey')
            ->orderBy('TreeName', 'asc')
            ->get();
        $GLOBALS["SL"]->pageAJAX .= '$(document).on("click", "#newTree", function() { '
            . '$("#newTreeForm").slideToggle("fast"); });';
        return view('vendor.survloop.admin.tree.trees', $this->v);
    }
    
    public function reportsList(Request $request)
    {
        return $this->pagesList($request, 'Report');
    }
    
    public function redirectsList(Request $request)
    {
        return $this->pagesList($request, 'Redirect');
    }
    
    public function pagesList(Request $request, $pageType = 'Page')
    {
        $this->admControlInit($request, (($pageType == 'Page') ? '/dashboard/pages' : (($pageType == 'Report') 
            ? '/dashboard/reports' : '/dashboard/redirects')));
        $this->startNewPage($request, $pageType);
        if ($pageType != 'Redirect') {
            $this->pagesRedirSaves($request);
        }
        $this->v["myRdr"] = [ "home" => [], "volun" => [], "partn" => [], "staff" => [], "admin" => [] ];
        $this->v["myPages"] = $GLOBALS["SL"]->x["pageUrls"] = $GLOBALS["SL"]->x["myRedirs"] = [];
        if ($pageType == 'Redirect') {
            $chk = SLTree::where('TreeDatabase', $GLOBALS["SL"]->dbID)
                ->where('TreeType', 'LIKE', 'Redirect')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $i => $redir) {
                    $redirUrl = '/' . (($redir->TreeOpts%Globals::TREEOPT_ADMIN == 0 
                        || $redir->TreeOpts%Globals::TREEOPT_VOLUNTEER == 0 
                        || $redir->TreeOpts%Globals::TREEOPT_PARTNER == 0 
                        || $redir->TreeOpts%Globals::TREEOPT_STAFF == 0) ? 'dash/' : '') . $redir->TreeSlug;
                    if (!isset($GLOBALS["SL"]->x["myRedirs"][$redir->TreeSlug])) {
                        $GLOBALS["SL"]->x["myRedirs"][$redir->TreeSlug] = '';
                    }
                    $GLOBALS["SL"]->x["myRedirs"][$redir->TreeSlug] .= '<br /><i class="mL5 mR5 slGreenDark">also redirects'
                        . ' from</i><a href="' . $redir->TreeDesc . '" target="_blank">' . $redir->TreeDesc . '</a>';
                    if (!in_array($redirUrl, $GLOBALS["SL"]->x["pageUrls"])) {
                        $type = (($redir->TreeOpts%Globals::TREEOPT_ADMIN == 0) 
                            ? 'admin' : (($redir->TreeOpts%Globals::TREEOPT_VOLUNTEER == 0) ? 'volun' 
                            : (($redir->TreeOpts%Globals::TREEOPT_PARTNER == 0) 
                                ? 'partn' : (($redir->TreeOpts%Globals::TREEOPT_STAFF == 0) ? 'staff' : 'home'))));
                        $this->v["myRdr"][$type][] = [ $redirUrl, $redir->TreeDesc, $redir->TreeID ];
                    }
                }
            }
        } else { // not Redirect
            $this->v["myPages"] = [];
            $chk = SLTree::where('TreeDatabase', $GLOBALS["SL"]->dbID)
               // ->whereRaw('TreeOpts%' . Globals::TREEOPT_SURVREPORT . ' ' 
               //     . (($pageType == 'Report') ? '=' : '>') . ' 0')
                ->where('TreeType', 'LIKE', 'Page')
                ->orderBy('TreeName', 'asc')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $i => $tree) {
                    if (($pageType == 'Report' && $tree->TreeOpts%Globals::TREEOPT_SURVREPORT == 0)
                        || ($pageType != 'Report' && $tree->TreeOpts%Globals::TREEOPT_SURVREPORT > 0)) {
                        $this->v["myPages"][] = $tree;
                        if ($tree->TreeOpts%Globals::TREEOPT_ADMIN == 0 && $tree->TreeOpts%Globals::TREEOPT_HOMEPAGE == 0) {
                            $GLOBALS["SL"]->x["pageUrls"][$tree->TreeID] = '/dashboard';
                        } else {
                            $GLOBALS["SL"]->x["pageUrls"][$tree->TreeID] = '/' 
                                . (($tree->TreeOpts%Globals::TREEOPT_ADMIN == 0 
                                    || $tree->TreeOpts%Globals::TREEOPT_VOLUNTEER == 0 
                                    || $tree->TreeOpts%Globals::TREEOPT_PARTNER == 0 
                                    || $tree->TreeOpts%Globals::TREEOPT_STAFF == 0) ? 'dash/' : '') . $tree->TreeSlug;
                        }
                    }
                }
            }
            $this->v["autopages"] = [ "contact" => false ];
            if (sizeof($this->v["myPages"]) > 0) {
                foreach ($this->v["myPages"] as $page) {
                    if ($page->TreeOpts%Globals::TREEOPT_CONTACT == 0) {
                        $this->v["autopages"]["contact"] = true;
                    }
                }
            }
        }
        $this->v["pageType"] = $pageType;
        return view('vendor.survloop.admin.tree.pages', $this->v);
    }
    
    protected function startNewPage(Request $request, $pageType = 'Page')
    {
        if ($request->has('sub') && $request->has('newPageName')) {
            $tree = new SLTree;
            $tree->TreeDatabase = $GLOBALS["SL"]->dbID;
            $tree->TreeUser = $this->v["uID"];
            $tree->TreeType = 'Page';
            $tree->TreeName = trim($request->newPageName);
            $tree->TreeSlug = trim($request->newPageSlug);
            if ($tree->TreeSlug == '') {
                $tree->TreeSlug = $GLOBALS["SL"]->slugify($request->newPageName);
            }
            $tree->TreeOpts = 1;
            if ($request->has('pageAdmOnly') && intVal($request->pageAdmOnly) == 1) {
                $tree->TreeOpts *= Globals::TREEOPT_ADMIN;
            }
            if ($request->has('pageStfOnly') && intVal($request->pageStfOnly) == 1) {
                $tree->TreeOpts *= Globals::TREEOPT_STAFF;
            }
            if ($request->has('pagePrtOnly') && intVal($request->pagePrtOnly) == 1) {
                $tree->TreeOpts *= Globals::TREEOPT_PARTNER;
            }
            if ($request->has('pageVolOnly') && intVal($request->pageVolOnly) == 1) {
                $tree->TreeOpts *= Globals::TREEOPT_VOLUNTEER;
            }
            if ($request->has('pageIsReport') && intVal($request->pageIsReport) == 1) {
                $tree->TreeOpts *= Globals::TREEOPT_SURVREPORT;
            }
            $tree->save();
            if ($tree->TreeOpts%Globals::TREEOPT_REPORT == 0 && $request->has('reportPageTree') 
                && intVal($request->reportPageTree) > 0) {
                $chkTree = SLTree::find($request->reportPageTree);
                if ($chkTree && isset($chkTree->TreeID)) {
                    $tree->update([ 'TreeCoreTable' => $chkTree->TreeCoreTable ]);
                    $newRoot = $this->createRootNode($tree);
                    if ($newRoot && isset($newRoot->NodeID)) {
                        $newRoot->NodeResponseSet = $request->reportPageTree;
                        $newRoot->save();
                    }
                }
            }
            echo $this->redir('/dashboard/page/' . $tree->TreeID . '?all=1&alt=1&refresh=1', true);
            exit;
        }
        return false;
    }
    
    protected function pagesRedirSaves(Request $request)
    {
        if ($request->has('subRedir') && $request->has('newRedirFrom')) {
            $tree = new SLTree;
            $tree->TreeDatabase = $GLOBALS["SL"]->dbID;
            $tree->TreeUser = $this->v["uID"];
            $tree->TreeType = 'Redirect';
            $tree->TreeDesc = trim($request->newRedirTo);
            $tree->TreeSlug = trim($request->newRedirFrom);
            $tree->TreeOpts = 1;
            if ($request->has('redirAdmOnly') && intVal($request->redirAdmOnly) == 1) {
                $tree->TreeOpts *= Globals::TREEOPT_ADMIN;
            }
            if ($request->has('redirStfOnly') && intVal($request->redirStfOnly) == 1) {
                $tree->TreeOpts *= Globals::TREEOPT_STAFF;
            }
            if ($request->has('redirPrtOnly') && intVal($request->redirPrtOnly) == 1) {
                $tree->TreeOpts *= Globals::TREEOPT_PARTNER;
            }
            if ($request->has('redirVolOnly') && intVal($request->redirVolOnly) == 1) {
                $tree->TreeOpts *= Globals::TREEOPT_VOLUNTEER;
            }
            $tree->save();
            echo $this->redir('/dashboard/redirects/list', true);
            exit;
        }
        if ($request->has('redirEdit') && intVal($request->get('redirEdit')) > 0 
            && $request->has('redirTo') && $request->has('redirFrom')) {
            $tree = SLTree::find(intVal($request->get('redirEdit')));
            if ($tree && isset($tree->TreeID)) {
                $tree->TreeDesc = trim($request->redirTo);
                $tree->TreeSlug = trim($request->redirFrom);
                $tree->save();
            }
            echo $this->redir('/dashboard/redirects/list', true);
            exit;
        }
        return false;
    }
    
    public function treeSettings(Request $request, $treeID = -3)
    {
        $this->initLoader();
        $this->loader->syncDataTrees($request, -3, $treeID);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/settings');
        if ($request->has('sub') && $request->has('TreeName') && trim($request->get('TreeName')) != '') {
            $GLOBALS["SL"]->treeRow->TreeName      = trim($request->get('TreeName'));
            $GLOBALS["SL"]->treeRow->TreeSlug      = trim($request->get('TreeSlug'));
            $GLOBALS["SL"]->treeRow->TreeCoreTable = $GLOBALS["SL"]->tblI[$request->get('TreeCoreTable')];
            $GLOBALS["SL"]->treeRow->TreeOpts = 1;
            $opts = [
                Globals::TREEOPT_SKINNY,
                Globals::TREEOPT_ADMIN,
                Globals::TREEOPT_HOMEPAGE,
                Globals::TREEOPT_NOEDITS,
                Globals::TREEOPT_REPORT,
                Globals::TREEOPT_VOLUNTEER,
                Globals::TREEOPT_CONTACT, 
                Globals::TREEOPT_PROFILE,
                Globals::TREEOPT_NOCACHE,
                Globals::TREEOPT_SEARCH,
                Globals::TREEOPT_SURVNAVBOT,
                Globals::TREEOPT_PARTNER, 
                Globals::TREEOPT_STAFF,
                Globals::TREEOPT_PUBLICID
                ];
            foreach ($opts as $o) {
                if ($GLOBALS["SL"]->REQ->has('opt' . $o) && intVal($GLOBALS["SL"]->REQ->get('opt' . $o)) == $o) {
                    $GLOBALS["SL"]->treeRow->TreeOpts *= $o;
                }
            }
            $GLOBALS["SL"]->treeRow->save();
            $this->storeProTips($request);
            return redirect('/dashboard/surv-' . $treeID . '/map?all=1&alt=1&refresh=1');
        }
        return view('vendor.survloop.admin.tree.settings', $this->v);
    }
    
    public function storeProTips(Request $request)
    {
        for ($i = 0; $i < 20; $i++) {
            if ($request->has('proTip' . $i) && trim($request->get('proTip' . $i)) != '') {
                $chk = SLDefinitions::where('DefDatabase', $this->dbID)
                    ->where('DefSet', 'Tree Settings')
                    ->where('DefSubset', 'LIKE', 'tree-' . $this->treeID . '-protip')
                    ->where('DefOrder', $i)
                    ->update([ 'DefDescription' => $request->get('proTip' . $i) ]);
                if (!$chk) {
                    $chk = new SLDefinitions;
                    $chk->DefDatabase    = $this->dbID;
                    $chk->DefSet         = 'Tree Settings';
                    $chk->DefSubset      = 'tree-' . $this->treeID . '-protip';
                    $chk->DefOrder       = $i;
                    $chk->DefDescription = $request->get('proTip' . $i);
                    $chk->save();
                }
                $chk = SLDefinitions::where('DefDatabase', $this->dbID)
                    ->where('DefSet', 'Tree Settings')
                    ->where('DefSubset', 'LIKE', 'tree-' . $this->treeID . '-protipimg')
                    ->where('DefOrder', $i)
                    ->update([ 'DefDescription' => $request->get('proTipImg' . $i) ]);
                if (!$chk) {
                    $chk = new SLDefinitions;
                    $chk->DefDatabase    = $this->dbID;
                    $chk->DefSet         = 'Tree Settings';
                    $chk->DefSubset      = 'tree-' . $this->treeID . '-protipimg';
                    $chk->DefOrder       = $i;
                    $chk->DefDescription = $request->get('proTipImg' . $i);
                    $chk->save();
                }
            } else { // empty tip row
                $chk = SLDefinitions::where('DefDatabase', $this->dbID)
                    ->where('DefSet', 'Tree Settings')
                    ->where('DefSubset', 'LIKE', 'tree-' . $this->treeID . '-protip%')
                    ->where('DefOrder', $i)
                    ->delete();
            }
        }
        return true;
    }
    
    public function blurbsList(Request $request)
    {
        $this->admControlInit($request, '/dashboard/pages/snippets');
        if ($request->has('sublurb')) {
            $blurb = $this->blurbNew($request);
            if ($blurb > 0) {
                return $this->redir('/dashboard/pages/snippets/' . $blurb);
            }
        }
        $this->v["blurbRows"] = SLDefinitions::where('DefSet', 'Blurbs')
            ->orderBy('DefSubset')
            ->get();
        return view('vendor.survloop.admin.tree.snippets', $this->v);
    }
    
    public function autoAddPages(Request $request, $addPageType = '')
    {
        if ($addPageType == 'contact') {
            $survInst = new SurvLoopInstaller;
            $survInst->installPageContact();
        }
        return $this->pagesList($request);
    }
    
    public function data(Request $request, $treeID = -3)
    {
        $this->initLoader();
        $this->loader->syncDataTrees($request, -3, $treeID);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/data');
        if ($request->has('dataStruct')) {
            if ($request->has('delSub') && intVal($request->input('delSub')) > 0) {
                $found = SLDataSubsets::find($request->input('delSub'));
                if ($found && isset($found->DataSubTree)) {
                    $found->delete();
                }
            } elseif ($request->has('newSub') && $request->has('newSubset')) {
                $splits = explode(':', $request->input('newSubset'));
                $newSubset = new SLDataSubsets;
                $newSubset->DataSubTree    = $GLOBALS["SL"]->treeID;
                $newSubset->DataSubTbl     = $splits[0];
                $newSubset->DataSubTblLnk  = $splits[1];
                $newSubset->DataSubSubTbl  = $splits[2];
                $newSubset->DataSubSubLnk  = $splits[3];
                $newSubset->DataSubAutoGen = $request->input('newSubAuto');
                $newSubset->save();
            } elseif ($request->has('delHelper')) {
                $found = SLDataHelpers::find($request->input('delHelper'));
                if ($found && isset($found->DataHelpTree)) {
                    $found->delete();
                }
            } elseif ($request->has('newHelper')) {
                $splits = explode(':', $request->input('newHelper'));
                $valFld = str_replace($splits[2].':', '', $request->input('newHelperValue'));
                if (isset($splits[2])) {
                    $newHelp = new SLDataHelpers;
                    $newHelp->DataHelpTree        = $GLOBALS["SL"]->treeID;
                    $newHelp->DataHelpParentTable = $splits[0];
                    $newHelp->DataHelpTable       = $splits[2];
                    $newHelp->DataHelpKeyField    = $splits[3];
                    $newHelp->DataHelpValueField  = $valFld;
                    $newHelp->save();
                }
            } elseif ($request->has('delLinkage')) {
                $found = SLDataLinks::where('DataLinkTree', $GLOBALS["SL"]->treeID)
                    ->where('DataLinkTable', $request->input('delLinkage'))
                    ->first();
                if ($found && isset($found->DataLinkTree)) {
                    $found->delete();
                    unset($GLOBALS["SL"]->dataLinksOn[$found->DataLinkTable]);
                }
            } elseif ($request->has('newLinkage')) {
                $newLink = new SLDataLinks;
                $newLink->DataLinkTree = $GLOBALS["SL"]->treeID;
                $newLink->DataLinkTable = $request->input('newLinkage');
                $newLink->save();
                $GLOBALS["SL"]->dataLinksOn[$request->input('newLinkage')] 
                    = $GLOBALS["SL"]->getLinkTblMap($request->input('newLinkage'));
            }
        }
        
        if (!$this->checkCache() || $request->has('dataStruct') || $request->has('refresh')) {
            $this->v["content"] = view('vendor.survloop.admin.tree.tree-data', $this->v)->render();
            $this->saveCache();
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    public function nodeEdit(Request $request, $treeID = -3, $nID = -3) 
    {
        $node = [];
        if ($nID > 0) {
            $this->loadDbFromNode($request, $nID);
        } elseif ($request->has('parent') && intVal($request->get('parent')) > 0) {
            $this->loadDbFromNode($request, $request->get('parent'));
        } elseif ($request->has('nodeParentID') && intVal($request->nodeParentID) > 0) {
            $this->loadDbFromNode($request, $request->nodeParentID);
        }
        $currPage = '/dashboard/surv-' . $treeID . '/map?all=1&alt=1';
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
            //$currPage = '/dashboard/page/' . $treeID . '?all=1&alt=1';
            $currPage = '/dashboard/pages';
        }
        $this->admControlInit($request, $currPage);
        $this->v["content"] = $this->v["treeClassAdmin"]->adminNodeEdit($nID, $request, $currPage);
        if (isset($this->v["treeClassAdmin"]->v["needsWsyiwyg"]) && $this->v["treeClassAdmin"]->v["needsWsyiwyg"]) {
            $this->v["needsWsyiwyg"] = true;
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    public function treeStats(Request $request, $treeID = -3) 
    {
        $this->initLoader();
        $this->loader->syncDataTrees($request, -3, $treeID);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/stats?all=1');
        if (!$this->checkCache()) {
            $this->v["printTree"] = $this->v["treeClassAdmin"]->adminPrintFullTreeStats($request);
            $this->v["content"] = view('vendor.survloop.admin.tree.treeStats', $this->v)->render();
            $this->saveCache();
        }
        return view('vendor.survloop.master', $this->v);
    }

    public function treeSessions(Request $request, $treeID = 1, $refresh = false) 
    {
        $this->initLoader();
        $this->loader->syncDataTrees($request, -3, $treeID);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/sessions');
        if (!$this->checkCache() || $refresh) {
            $this->loadCustLoop($request, $treeID);
            $this->custReport->loadTree($treeID, $request);
            $this->sysDef = new SystemDefinitions;
            $this->v["css"] = $this->sysDef->loadCss();
            
            // clear empties here
            $this->v["dayold"] = mktime(date("H"), date("i"), date("s"), date("m"), date("d")-3, date("Y"));
            eval("\$chk = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) . "::"
                . $this->custReport->treeSessionsWhereExtra()
                . "where('updated_at', '<', '" . date("Y-m-d H:i:s", $this->v["dayold"]) 
                . "')->get();");
            if ($chk->isNotEmpty()) {
                foreach ($chk as $row) {
                    if ($this->custReport->chkCoreRecEmpty($row->getKey(), $row)) {
                        $row->delete();
                        //eval("\$del = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) . "::find(" 
                        //    . $row->getKey() . ")->delete();");
                    }
                }
            }
            
            $analyze = new SessAnalysis($this->custReport->treeID);
            $this->v["nodeTots"] = $analyze->loadNodeTots($this->custReport);
            $this->v["nodeSort"] = $analyze->nodeSort;
            $this->v["coreTots"] = [];
            $this->v["allPublicCoreIDs"] = $this->custReport->getAllPublicCoreIDs();
            
            $this->v["last100ids"] = DB::table('SL_NodeSavesPage')
                ->join('SL_Sess', 'SL_NodeSavesPage.PageSaveSession', '=', 'SL_Sess.SessID')
                ->where('SL_Sess.SessTree', '=', $treeID)
                ->where('SL_Sess.SessCoreID', '>', 0)
                //->select('SL_NodeSaves.*', 'SL_Sess.SessCoreID')
                ->orderBy('SL_Sess.created_at', 'desc')
                ->select('SL_Sess.SessCoreID', 'SL_Sess.SessCurrNode', 'SL_Sess.created_at')
                ->distinct()
                ->get([ 'SL_Sess.SessCoreID' ]);
            
            $this->v["graph1data"] = [];
            $this->v["genTots"] = [
                "date" => [ 0, 0, 0, [] ], // incomplete time tot, complete time tot, start date, totals by date
                "cmpl" => [ 0, 0 ], // incomplete (I), complete (C)
                "mobl" => [ 0, 0, [ 0, 0 ], [ 0, 0 ] ] // desktop (D), mobile (M), [ DI, DC ], [ MI, MC ]
                ];
            $nodeTots = $lines = [];
            if ($this->v["last100ids"]->isNotEmpty()) {
                foreach ($this->v["last100ids"] as $i => $rec) {
                    $coreTots = $analyze->analyzeCoreSessions($rec->SessCoreID, $this->v["allPublicCoreIDs"]);
                    if ($coreTots["node"] > 0 && isset($this->v["nodeTots"][$coreTots["node"]])) {
                        $this->v["coreTots"][] = $coreTots;
                        $cmpl = (($coreTots["cmpl"]) ? 1 : 0);
                        $mobl = (($coreTots["mobl"]) ? 1 : 0);
                        $this->v["nodeTots"][$coreTots["node"]]["cmpl"][$cmpl]++;
                        $this->v["genTots"]["cmpl"][$cmpl]++;
                        $this->v["genTots"]["mobl"][$mobl]++;
                        $this->v["genTots"]["mobl"][(2+$mobl)][$cmpl]++;
                        $this->v["genTots"]["date"][2] = $coreTots["date"];
                        $this->v["genTots"]["date"][$cmpl] = $this->v["genTots"]["date"][$cmpl]+$coreTots["dur"];
                        $date = date("Y-m-d", $coreTots["date"]);
                        if (!isset($this->v["genTots"]["date"][3][$date])) {
                            $this->v["genTots"]["date"][3][$date] = 0;
                        }
                        $this->v["genTots"]["date"][3][$date]++;
                        $min = $coreTots["dur"]/60;
                        if ($min < 70) {
                            $this->v["graph1data"][] = [
                                (($coreTots["cmpl"]) ? 100 : $this->v["nodeTots"][$coreTots["node"]]["perc"]),
                                $min
                                ];
                        }
                    }
                }
            }
            $this->v["graph1print"] = view('vendor.survloop.reports.graph-scatter', [
                "currGraphID" => 'treeSessScat',
                "hgt"         => '400px',
                "dotColor"    => $this->v["css"]["color-main-on"],
                "brdColor"    => $this->v["css"]["color-main-grey"],
                "title"       => '<h3 class="mT0 mB10">Duration of Attempt by Percent Completion</h3>'
                    . '<div class="mTn10 mB10"><i>Based on the final page saved during incomplete submission attempts.'
                    . '</i></div>',
                "xAxes"       => '% Complete',
                "yAxes"       => 'Minutes',
                "data"        => $this->v["graph1data"],
                "css"         => $this->v["css"]
                ])->render();
            
            $this->v["graph2"] = [ "dat" => '', "lab" => '', "bg" => '', "brd" => '' ];
            $cnt = 0;
            $currTime = $this->v["genTots"]["date"][2];
            $currDate = date("Y-m-d", $currTime);
            while ($currDate != date("Y-m-d")) {
                $cma = (($cnt > 0) ? ", " : "");
                $this->v["graph2"]["dat"] .= $cma . ((isset($this->v["genTots"]["date"][3][$currDate])) 
                    ? $this->v["genTots"]["date"][3][$currDate] : 0);
                $this->v["graph2"]["lab"] .= $cma . "\"" . $currDate . "\"";
                $this->v["graph2"]["bg"]  .= $cma . "\"" . $this->v["css"]["color-main-on"]  . "\"";
                $this->v["graph2"]["brd"] .= $cma . "\"" . $this->v["css"]["color-main-grey"] . "\"";
                $cnt++;
                $currTime += (24*60*60);
                $currDate = date("Y-m-d", $currTime);
            }
            $this->v["graph2print"] = view('vendor.survloop.reports.graph-bar', [
                "currGraphID" => 'treeSessCalen',
                "hgt"         => '380px',
                "yAxes"       => '# of Submission Attempts (Active Sessions)',
                "title"       => '<h3 class="mT0 mB10">Number of Submission Attempts by Date</h3>',
                "graph"       => $this->v["graph2"],
                "css"         => $this->v["css"]
                ])->render();
            $this->v["content"] = view('vendor.survloop.admin.tree.tree-sessions-stats', $this->v)->render();
            $this->saveCache();
        }
        $this->v["needsCharts"] = true;
        return view('vendor.survloop.master', $this->v);
    }
    
    protected function treeSessGraphDaily(Request $request, $treeID = 1)
    {
        $this->treeSessions($request, $treeID, true);
        return $this->v["graph2print"];
    }
    
    protected function treeSessGraphDurations(Request $request, $treeID = 1)
    {
        $this->treeSessions($request, $treeID, true);
        return $this->v["graph1print"];
    }
    
    public function workflows(Request $request) 
    {
        $this->admControlInit($request, '/dashboard/db/workflows');
        return view('vendor.survloop.admin.tree.workflows', $this->v);
    }
    
    public function condAdd(Request $request) 
    {
        $this->admControlInit($request, '/dashboard/db/conds/add');
        if ($request->has('addNewCond')) {
            $GLOBALS["SL"]->saveEditCondition($request);
        }
        if (is_array($this->custReport)) {
            $this->loadCustLoop($request, $this->treeID);
        }
        $this->custReport->addCondEditorAjax();
        return view('vendor.survloop.admin.tree.cond-add-page', $this->v);
    }
    
    public function conditions(Request $request) 
    {
        $this->admControlInit($request, '/dashboard/db/conds');
        $this->v["filtOnly"] = (($request->has('only')) ? $request->get('only') : 'all');
        $condsRaw = $this->loadCondList();
        if ($request->has('totalConds') && intVal($request->totalConds) > 0) {
            if ($condsRaw && sizeof($condsRaw) > 0) {
                foreach ($condsRaw as $i => $cond) {
                    if ($request->has('CondDelete' . $i . '')) {
                        SLConditions::find($cond->CondID)
                            ->delete();
                        SLConditionsVals::where('CondValCondID', $cond->CondID)
                            ->delete();
                        SLConditionsArticles::where('ArticleCondID', $cond->CondID)
                            ->delete();
                    }
                }
            }
        }
        $this->v["condSplits"] = $this->loadCondList();
        $this->v["condIDs"] = '';
        if ($this->v["condSplits"] && sizeof($this->v["condSplits"]) > 0) {
            foreach ($this->v["condSplits"] as $i => $cond) {
                $this->v["condIDs"] .= ',' . $cond->CondID;
            }
            $this->v["condIDs"] = substr($this->v["condIDs"], 1);
        }
        $this->loadCondArticles();
        return view('vendor.survloop.admin.tree.conditions', $this->v);
    }
    
    public function condEdit(Request $request, $cid = -3) 
    {
        $this->admControlInit($request, '/dashboard/db/conds/edit/');
        $this->v["cond"] = SLConditions::find($cid);
        if ($this->v["cond"] && isset($this->v["cond"]->CondTag)) {
            if ($request->has('editCond') && intVal($request->get('editCond')) == 1) {
                $GLOBALS["SL"]->saveEditCondition($request);
                $this->v["cond"] = SLConditions::find($cid);
            }
            $this->v["cond"]->loadVals();
            $this->loadCondArticles($cid);
            $GLOBALS["SL"]->pageAJAX .= view('vendor.survloop.admin.db.inc-addCondition-ajax', [
                "newOnly"      => false,
                "cond"         => $this->v["cond"],
                "condArticles" => $this->v["condArticles"]
                ])->render();
            return view('vendor.survloop.admin.db.inc-condition-edit', $this->v);
        }
        return $this->redir('/dashboard/db/conds');
    }
    
    protected function getRawConds()
    {
        if ($this->v["filtOnly"] == 'public') {
            return DB::select("SELECT `CondID` FROM `SL_Conditions` WHERE `CondOpts`%2 = 0 ORDER BY `CondTag`");
        } elseif ($this->v["filtOnly"] == 'articles') {
            return DB::select("SELECT `CondID` FROM `SL_Conditions` WHERE `CondOpts`%3 = 0 ORDER BY `CondTag`");
        } else {
            return SLConditions::orderBy('CondTag', 'asc')->get();
        }
        //elseif ($this->v["filtOnly"] == 'public') $condsRaw = SLConditions::where('CondOpts', 2)->orderBy('CondTag', 'asc')->get();
        //elseif ($this->v["filtOnly"] == 'articles') $condsRaw = SLConditions::where('CondOpts', 3)->orderBy('CondTag', 'asc')->get();
    }
    
    public function loadCondList()
    {
        $condsRaw = [];
        $condsTmp = $this->getRawConds();
        if ($condsTmp->isNotEmpty()) {
            foreach ($condsTmp as $c) {
                $condsRaw[] = SLConditions::find($c->CondID);
            }
        }
        if ($condsRaw && sizeof($condsRaw) > 0) {
            foreach ($condsRaw as $i => $c) {
                $condsRaw[$i]->loadVals();
            }
        }
        return $condsRaw;
    }
    
    
    public function loadCondArticles($cid = -3)
    {
        $this->v["condArticles"] = $arts = [];
        if ($cid > 0) $arts = SLConditionsArticles::where('ArticleCondID', $cid)->get();
        else $arts = SLConditionsArticles::get();
        if ($arts && sizeof($arts) > 0) {
            foreach ($arts as $i => $art) {
                if (!isset($this->v["condArticles"][$art->ArticleCondID])) {
                    $this->v["condArticles"][$art->ArticleCondID] = [];
                }
                if (trim($art->ArticleURL) !== '') {
                    $this->v["condArticles"][$art->ArticleCondID][] = [
                        trim($art->ArticleTitle), 
                        trim($art->ArticleURL)
                    ];
                }
            }
        }
        return true;
    }
    
    public function xmlmap(Request $request, $treeID = -3)
    {
        $this->initLoader();
        $this->loader->syncDataTrees($request, -3, $treeID);
        //$this->switchTree($treeID, '/dashboard/tree/switch', $request);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/xmlmap');
        $xmlmap = new TreeSurvAPI;
        $xmlmap->loadTree($GLOBALS["SL"]->xmlTree["id"], $request);
        $this->v["adminPrintFullTree"] = $xmlmap->adminPrintFullTree($request);
        $GLOBALS["SL"]->pageAJAX .= '$(document).on("click", "#editXmlMap", function() {
            $(".editXml").css("display","inline"); });';
        return view('vendor.survloop.admin.tree.xmlmap', $this->v);
    }
    
    public function xmlNodeEdit(Request $request, $treeID = -3, $nID = -3)
    {
        $this->initLoader();
        $this->loader->syncDataTrees($request, -3, $treeID);
        //$this->switchTree($treeID, '/dashboard/tree/switch', $request);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/xmlmap');
        $xmlmap = new TreeSurvAPI;
        $xmlmap->loadTree($GLOBALS["SL"]->xmlTree["id"], $request, true);
        $this->v["content"] = $xmlmap->adminNodeEditXML($request, $nID);
        return view('vendor.survloop.master', $this->v);
    }
    
    protected function updateSysSet($set, $val)
    {
        if ($this->v["user"] && $this->v["user"]->hasRole('administrator')) {
            SLDefinitions::where('DefDatabase', '=', 1)
                ->where('DefSet', '=', 'System Settings')
                ->where('DefSubset', '=', $set)
                ->update(['DefDescription' => $val]);
        }
        return true;
    }
    
    public function freshDBstore(Request $request, $db)
    {
        $db->DbUser    = $this->v["uID"];
        $db->DbPrefix  = trim($request->DbPrefix) . '_';
        $db->DbName    = trim($request->DbName);
        $db->DbDesc    = trim($request->DbDesc);
        $db->DbMission = trim($request->DbMission);
        $db->save();
        $GLOBALS["SL"] = new Globals($request, $db->dbID, -3);
        return $db;
    }
    
    public function freshDB(Request $request)
    {
        $this->survLoopInit($request, '/fresh/database');
        $chk = SLUsersRoles::get();
        if ($chk->isEmpty()) {
            $this->v["user"]->assignRole('administrator');
            $this->logPageVisit('NEW SYSTEM ADMINISTRATOR!');
        }
        $this->v["isFresh"] = true;
        if ($request->has('freshSub') && intVal($request->freshSub) == 1
            && $this->v["user"] && $this->v["user"]->hasRole('administrator')) {
            // Initialize Database Record Settings
            $db = SLDatabases::find(1);
            if (!$db) {
                $db = new SLDatabases;
                $db->DbID = 1;
            }
            $db = $this->freshDBstore($request, $db);
            $this->logPageVisit('/fresh/database', $db->DbID . ';0');
            
            // Initialize system-wide settings
            $this->updateSysSet('cust-abbr', trim($request->DbPrefix));
            $this->updateSysSet('site-name', trim($request->DbName));
            $this->updateSysSet('meta-desc', trim($request->DbName));
            $this->updateSysSet('meta-title', trim($request->DbDesc . ' | ' . trim($request->DbName)));
            
            $this->genDbClasses($request->DbPrefix);
            
            if (!$this->chkHasTreeOne()) {
                return $this->redir('/fresh/survey', true);
            }
            return $this->redir('/dashboard/tree/new');
        }
        return view('vendor.survloop.admin.fresh-install-setup-db', $this->v);
    }

    public function newDB(Request $request)
    {
        $this->survLoopInit($request, '/dashboard/db/new');
        $this->v["isFresh"] = false;
        if ($request->has('freshSub') && intVal($request->freshSub) == 1
            && $this->v["user"] && $this->v["user"]->hasRole('administrator')) {
            // Initialize Database Record Settings
            $db = new SLDatabases;
            $db = $this->freshDBstore($request, $db);
            $this->logPageVisit('/fresh/database', $db->DbID.';0');
            $this->genDbClasses($request->DbPrefix);
            if (!$this->chkHasTreeOne()) {
                return $this->redir('/fresh/survey', true);
            }
            return $this->redir('/dashboard/tree/new');
        }
        return view('vendor.survloop.admin.fresh-install-setup-db', $this->v);
    }

    protected function genDbClasses($dbPrefix)
    {
        // Generate controller files for client customization
        if (!file_exists('../app/Http/Controllers/' . trim($dbPrefix))) {
            mkdir('../app/Http/Controllers/' . trim($dbPrefix));
        }
        $fileName = '../app/Http/Controllers/' . trim($dbPrefix) . '/' . trim($dbPrefix) . '.php';
        $file = "<"."?"."php\n" 
            . view('vendor.survloop.admin.fresh-install-class-core', [ "abbr" => trim($dbPrefix) ])->render();
        if (is_writable($fileName)) {
            file_put_contents($fileName, $file);
        }
        $file = "<"."?"."php\n" 
            . view('vendor.survloop.admin.fresh-install-class-report', [ "abbr" => trim($dbPrefix) ])->render();
        if (is_writable($fileName)) {
            file_put_contents(str_replace('.php', 'Report.php', $fileName), $file);
        }
        $file = "<"."?"."php\n" 
            . view('vendor.survloop.admin.fresh-install-class-admin', [ "abbr" => trim($dbPrefix) ])->render();
        if (is_writable($fileName)) {
            file_put_contents(str_replace('.php', 'Admin.php', $fileName), $file);
        }
        return true;
    }
    
    public function freshUXstore(Request $request, $tree, $currPage = '')
    {
        $tableName = trim($request->TreeTable);
        $coreTbl = SLTables::where('TblDatabase', $GLOBALS["SL"]->dbID)
            ->where('TblEng', $tableName)
            ->first();
        if (!$coreTbl) {
            $coreTbl = new SLTables;
            $coreTbl->TblDatabase = $GLOBALS["SL"]->dbID;
            $coreTbl->TblEng      = $tableName;
            $coreTbl->TblName     = $this->eng2data($tableName);
            $coreTbl->TblAbbr     = $this->eng2abbr($tableName);
            $coreTbl->TblDesc     = trim($request->TreeDesc);
            $coreTbl->save();
        }

        $userTbl = $GLOBALS["SL"]->loadUsrTblRow();
        if (!$userTbl) {
            $userTbl = new SLTables;
            $userTbl->TblDatabase = $GLOBALS["SL"]->dbID;
            $userTbl->TblEng      = 'Users';
            $userTbl->TblName     = 'users';
            $userTbl->TblAbbr     = '';
            $userTbl->TblDesc     = 'This represents the Laravel Users table, but will not '
                . 'actually be implemented by SurvLoop as part of the database installation.';
            $userTbl->save();
        }
        
        $tree->TreeName = trim($request->TreeName);
        $tree->TreeDesc = trim($request->TreeDesc);
        $tree->TreeSlug = $GLOBALS["SL"]->slugify($tree->TreeName);
        $tree->save();
        $tree = $this->initTree($tree, $coreTbl, $userTbl, 'Survey');
        $this->initTreeXML($tree, $coreTbl, 'Survey XML');
        
        $this->installNewCoreTable($coreTbl);
        
        $GLOBALS["SL"] = new Globals($request, $GLOBALS["SL"]->dbID, $tree->TreeID);
        return true;
    }
    
    public function freshUX(Request $request)
    {
        $this->survLoopInit($request, '/fresh/survey');
        $this->v["isFresh"] = true;
        if ($request->has('freshSub') && intVal($request->freshSub) == 1
            && $this->v["user"] && $this->v["user"]->hasRole('administrator')) {
            $tree = SLTree::find(1);
            if (!$tree) {
                $tree = new SLTree;
                $tree->TreeID = 1;
            }
            $tree = $this->freshUXstore($request, $tree, '/fresh/survey');
            return $this->redir('/dashboard/settings?refresh=1');
            //return $this->redir('/dashboard/surv-' . $tree->TreeID . '/map?all=1&alt=1');
        }
        return view('vendor.survloop.admin.fresh-install-setup-ux', $this->v);
    }
    
    public function newTree(Request $request)
    {
        $this->survLoopInit($request, '/dashboard/tree/new');
        $this->v["isFresh"] = false;
        if ($request->has('freshSub') && intVal($request->freshSub) == 1
            && $this->v["user"] && $this->v["user"]->hasRole('administrator')) {
            $tree = new SLTree;
            $tree->save();
            $tree = $this->freshUXstore($request, $tree, '/dashboard/tree/new');
            return $this->redir('/dashboard/surv-' . $tree->TreeID . '/map?all=1&alt=1');
        }
        return view('vendor.survloop.admin.fresh-install-setup-ux', $this->v);
    }
    
    protected function initTree($tree, $coreTbl, $userTbl, $type = 'Public')
    {
        $tree->TreeUser            = $this->v["uID"];
        $tree->TreeDatabase        = $GLOBALS["SL"]->dbID;
        $tree->TreeCoreTable       = $coreTbl->TblID;
        $tree->TreeType            = $type;
        $tree->save();
        
        $this->logPageVisit('/fresh/database', $GLOBALS["SL"]->dbID.';'.$tree->TreeID);
        
        $rootNode = new SLNode;
        $rootNode->NodeTree        = $tree->TreeID;
        $rootNode->NodeParentID    = -3;
        $rootNode->NodeType        = 'Branch Title';
        $rootNode->NodePromptText  = $tree->TreeName;
        $rootNode->save();
        
        $pageNode = new SLNode;
        $pageNode->NodeTree        = $tree->TreeID;
        $pageNode->NodeParentID    = $rootNode->NodeID;
        $pageNode->NodeType        = 'Page';
        $pageNode->NodePromptText  = 'Welcome To ' . $tree->TreeName;
        $pageNode->NodePromptNotes = 'welcome';
        $pageNode->save();
        
        $qNode = new SLNode;
        $qNode->NodeTree           = $tree->TreeID;
        $qNode->NodeParentID       = $pageNode->NodeID;
        $qNode->NodeType           = 'Text';
        $qNode->NodePromptText     = '<h2 class="slBlueDark">Welcome</h2>'
            . 'This is a sample question to a user. '
            . 'What will you ask them first?';
        $qNode->save();
        
        $tree->TreeRoot            = $rootNode->NodeID;
        $tree->TreeFirstPage       = $pageNode->NodeID;
        $tree->TreeLastPage        = $pageNode->NodeID;
        $tree->save();
        
        $GLOBALS["SL"]->initCoreTable($coreTbl, $userTbl);
        
        return $tree;
    }
    
    protected function initTreeXML($tree, $coreTbl, $type = 'Public XML')
    {
        $treeXML = new SLTree;
        $treeXML->TreeName         = $tree->TreeName;
        $treeXML->TreeSlug         = $tree->TreeSlug;
        $treeXML->TreeUser         = $this->v["uID"];
        $treeXML->TreeDatabase     = $GLOBALS["SL"]->dbID;
        $treeXML->TreeType         = 'Survey XML';
        $treeXML->TreeCoreTable    = $coreTbl->TblID;
        $treeXML->save();
        $rootNode = new SLNode;
        $rootNode->NodeTree        = $treeXML->TreeID;
        $rootNode->NodeParentID    = -3;
        $rootNode->NodeType        = 'XML';
        $rootNode->NodePromptText  = $coreTbl->TblName;
        $rootNode->NodePromptNotes = $coreTbl->TblID;
        $rootNode->save();
        return $treeXML;
    }
    
    protected function chkAllCoreTbls()
    {
        $chk = SLTree::where('TreeType', 'Survey')
            ->where('TreeCoreTable', '>', 0)
            ->get();
        $chk = DB::table('SL_Tree')
            ->join('SL_Tables', 'SL_Tables.TblID', '=', 'SL_Tree.TreeCoreTable')
            ->where('SL_Tree.TreeDatabase', $GLOBALS["SL"]->dbID)
            ->where('SL_Tree.TreeType', 'Survey')
            ->select('SL_Tables.*')
            ->get();
        $userTbl = $GLOBALS["SL"]->loadUsrTblRow();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $i => $tbl) {
                $GLOBALS["SL"]->initCoreTable($tbl, $userTbl);
            }
        }
        return true;
    }
    
    public function installNewCoreTable($tbl)
    {
        $tblQry = $GLOBALS["SL"]->mysqlTblCoreStart($tbl) . "  `" 
            . $tbl->TblAbbr . "UserID` bigint(20) unsigned NULL, \n  `"
            . $tbl->TblAbbr . "SubmissionProgress` int(11) NULL , \n  `" 
            . $tbl->TblAbbr . "VersionAB` varchar(255) NULL , \n  `" 
            . $tbl->TblAbbr . "UniqueStr` varchar(50) NULL , \n  `" 
            . $tbl->TblAbbr . "IPaddy` varchar(255) NULL , \n  `" 
            . $tbl->TblAbbr . "IsMobile` int(1) NULL , \n"
            . $GLOBALS["SL"]->mysqlTblCoreFinish($tbl);
        return DB::statement($tblQry);
    }
    
}
