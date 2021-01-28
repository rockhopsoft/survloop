<?php
/**
  * AdminTreeController is the admin class responsible for the tools to edit Survloop's tree designs.
  * (Ideally, this will eventually be replaced by Survloop-generated surveys.)
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.1
  */
namespace RockHopSoft\Survloop\Controllers\Admin;

use DB;
use Illuminate\Http\Request;
use App\Models\SLDatabases;
use App\Models\SLTables;
use App\Models\SLDefinitions;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLDataSubsets;
use App\Models\SLDataHelpers;
use App\Models\SLDataLinks;
use App\Models\SLConditions;
use App\Models\SLConditionsVals;
use App\Models\SLConditionsArticles;
use App\Models\SLUsersRoles;
use App\Models\SLNodeSaves;
use App\Models\SLNodeSavesPage;
use RockHopSoft\Survloop\Controllers\SurvloopInstaller;
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvAPI;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvAdmin;
use RockHopSoft\Survloop\Controllers\Admin\AdminTreeStats;

class AdminTreeController extends AdminTreeStats
{
    
    protected function initExtra(Request $request)
    {
    	if ($this->v["uID"] > 0) {
    		$this->v["allowEdits"] = $this->v["user"]->hasRole('administrator|databaser');
        }
        $this->v["adminOverOpts"] = ((session()->has('adminOverOpts')) 
            ? session()->get('adminOverOpts') : '');
        if (trim($this->v["currPage"][0]) == '') {
            $this->v["currPage"][0] = '/dashboard/tree';
        }
        
        if (!isset($this->v["treeAdmin"])) {
            $this->v["treeAdmin"] = new TreeSurvAdmin($request);
        }
        $this->v["treeAdmin"]->loadTree($GLOBALS["SL"]->treeID, $request);
        $this->initExtraCust();
        $this->chkCoreTbls();
        if (!isset($GLOBALS["SL"]->treeRow->tree_root) 
            || $GLOBALS["SL"]->treeRow->tree_root <= 0) {
            $this->createRootNode($GLOBALS["SL"]->treeRow);
        }
        set_time_limit(180);
        return true;
    }
    
    protected function createRootNode($treeRow)
    {
        if (!isset($treeRow->tree_id)) {
            return -3;
        }
        $chk = SLNode::where('node_tree', $treeRow->tree_id)
            ->where('node_parent_id', -3)
            ->first();
        if ($chk && isset($chk->node_id)) {
            return -3;
        }
        $newRoot = new SLNode;
        $newRoot->node_tree = $treeRow->tree_id;
        $newRoot->node_parent_id = -3;
        $coreTbl = '';
        if (intVal($treeRow->tree_core_table) > 0 
            && isset($GLOBALS["SL"]->tbl[$treeRow->tree_core_table])) {
            $coreTbl = $GLOBALS["SL"]->tbl[$treeRow->tree_core_table];
        }
        if ($treeRow->tree_type == 'Page') {
            $newRoot->node_type = 'Page';
            $newRoot->node_data_branch = $coreTbl;
            if (!isset($treeRow->tree_slug) 
                || trim($treeRow->tree_slug) == '') {
                $treeRow->tree_slug = $GLOBALS["SL"]->slugify($treeRow->tree_name);
                $treeRow->save();
            }
            $newRoot->node_prompt_notes = $treeRow->tree_slug;
            $newRoot->node_prompt_after = $treeRow->tree_name . '::M::::M::::M::';
            $newRoot->node_char_limit = -1;
        } else {
            $newRoot->node_type = 'Data Manip: New';
            $newRoot->node_data_branch = $coreTbl;
        }
        $newRoot->save();
        $treeRow->tree_root = $newRoot->node_id;
        if ($treeRow->tree_type == 'Page') {
            $treeRow->tree_first_page = $treeRow->tree_last_page = $newRoot->node_id;
        }
        $treeRow->save();
        if ($treeRow->tree_type == 'Page') {
            $firstReal = new SLNode;
            $firstReal->node_tree        = $treeRow->tree_id;
            $firstReal->node_parent_id   = $newRoot->node_id;
            $firstReal->node_type        = 'Instructions';
            $firstReal->node_prompt_text = '<h2>' . $treeRow->tree_name . '</h2>' 
                . "\n" . '<p>Edit this node to start filling out this page!</p>';
            $firstReal->save();
        }
        return $newRoot;
    }
    
    protected function allStdCondition($tag, $desc)
    {
        $chk = SLConditions::where('cond_database', 0)
            ->where('cond_tag', $tag)
            ->get();
        if ($chk->isEmpty()) {
            $newCond = new SLConditions;
            $newCond->cond_database = 0;
            $newCond->cond_tag = $tag;
            $newCond->cond_desc = $desc;
            $newCond->cond_operator = 'CUSTOM';
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
        $page = '/dashboard/surv-' . $treeID . '/map?all=1&alt=1';
        $this->admControlInit($request, $page);
        if (!$this->checkCache()) {
            $this->chkAllCoreTbls();
            $this->v["printTree"] = $this->v["treeAdmin"]->adminPrintFullTree($request);
            $this->v["ipLegal"] = view(
                'vendor.survloop.elements.dbdesign-legal', 
                [ "sysOpts" => $GLOBALS["SL"]->sysOpts ]
            )->render();
            $this->v["content"] = view(
                'vendor.survloop.admin.tree.tree', 
                $this->v
            )->render();
            $this->saveCache();
        }
        $treeAbout = view(
            'vendor.survloop.admin.tree.tree-about', 
            [ "showAbout" => false ]
        )->render();
        $this->v["content"] = $treeAbout . $this->v["content"];
        if ($request->has('refresh')) {
            $this->v["treeAdmin"]->createProgBarJs();
            $GLOBALS["SL"]->pageJAVA .= 'setTimeout("document.'
                . 'getElementById(\'hidFrameID\').src=\'/css-reload\'", 2000);';
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    public function adminPrintFullTreePublic(Request $request, $treeSlug = '')
    {
        $tree = SLTree::where('tree_slug', $treeSlug)
            ->where('tree_type', 'Survey')
            ->get();
        if ($tree->isNotEmpty()) {
            foreach ($tree as $t) {
                if ($t->tree_opts%Globals::TREEOPT_ADMIN > 0) { // no admin trees made public [for now]
                    $this->treeID = $t->tree_id;
                    $this->dbID = $t->tree_database;
                    $GLOBALS["SL"] = new Globals(
                        $request, 
                        $this->dbID, 
                        $this->treeID, 
                        $this->treeID
                    );
                }
            }
        }
        $this->survloopInit($request, '/tree/' . $treeSlug);
        if (!$this->checkCache()) {
            $this->adminPrintFullTreePublicGen($request);
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    protected function adminPrintFullTreePublicGen(Request $request)
    {
        $this->v["treeAdmin"]->loadTreeNodeStats();
        $GLOBALS["SL"]->x["hideDisabledNodes"] = true;
        $this->v["content"] = '<div class="container">';
        $blurbName = 'Tree Map Header: ' . $GLOBALS["SL"]->treeRow->tree_name;
        $custHeader = $GLOBALS["SL"]->getBlurb($blurbName);
        if (trim($custHeader) != '') {
            $readMore = '<div class="p15"><a href="javascript:;" '
                . 'id="hidivBtnReadMore" class="hidivBtn"'
                . '>About this map</a><div id="hidivReadMore" class="disNon">';
            if (strpos($custHeader, '[[TreeStats]]') !== false) {
                $custHeader = str_replace(
                    '[[TreeStats]]', 
                    $GLOBALS["SL"]->printTreeNodeStats(true, true, true), 
                    $custHeader
                );
                $this->v["content"] .= $custHeader . $readMore 
                    . view('vendor.survloop.elements.print-tree-map-desc')->render() 
                    . '</div></div>';
            } else {
                $this->v["content"] .= $custHeader . $readMore 
                    . view('vendor.survloop.elements.print-tree-map-desc')->render() 
                    . '</div></div><div class="p10"></div>' 
                    . $GLOBALS["SL"]->printTreeNodeStats(true, true, true);
            }
        } else {
            $this->v["content"] .= view('vendor.survloop.elements.logo-print', [
                    "sysOpts" => $GLOBALS["SL"]->sysOpts,
                    "w100" => true
                ])->render()
                . '<h2>' . $GLOBALS["SL"]->treeRow->tree_name . ': Specifications</h2>'
                . view('vendor.survloop.elements.print-tree-map-desc')->render()
                . '<div class="p10"></div>'
                . $GLOBALS["SL"]->printTreeNodeStats(true, true, true);
        }
        $legal = view(
            'vendor.survloop.elements.dbdesign-legal', 
            [ "sysOpts" => $GLOBALS["SL"]->sysOpts ]
        )->render();
        $this->v["content"] .= str_replace('Content Chunk, WYSIWYG', 'Content Chunk', 
                $this->v["treeAdmin"]->adminPrintFullTree($request, true))
            . '<div class="nodeAnchor"><a name="licenseInfo"></a></div>'
            . '<div class="mT20 mB20 p20">' . $legal . '</div></div>';
        $this->saveCache();
        return true;
    }
    
    public function indexPage(Request $request, $treeID)
    {
        $tree = SLTree::find($treeID);
        if (!$tree || !isset($tree->tree_name)) {
            return $this->redir('/dashboard/pages');
        }
        $this->treeID = $treeID;
        $this->dbID = $tree->tree_database;
        $GLOBALS["SL"] = new Globals($request, $this->dbID, $this->treeID, $this->treeID);
        $this->admControlInit($request, '/dashboard/pages');
        if (!$this->checkCache()) {
            $this->v["printTree"] = $this->v["treeAdmin"]->adminPrintFullTree($request);
            $this->v["content"] = view('vendor.survloop.admin.tree.page', $this->v)->render();
            $this->saveCache();
        }
        $treeAbout = view(
            'vendor.survloop.admin.tree.page-about', 
            [ "showAbout" => false ]
        )->render();
        $this->v["content"] = $treeAbout . $this->v["content"];
        return view('vendor.survloop.master', $this->v);
    }                                        
    
    public function treesList(Request $request)
    {
        $this->admControlInit($request, '/dashboard/surveys/list');
        if ($request->has('sub') && $request->has('newTreeName')) {
            $tree = new SLTree;
            $tree->tree_database = $GLOBALS["SL"]->dbID;
            $tree->tree_user = $this->v["uID"];
            $tree->tree_type = 'Survey';
            $tree->tree_name = trim($request->newTreeName);
            $tree->tree_slug = trim($request->newTreeSlug);
            if ($tree->tree_slug == '') {
                $tree->tree_slug = $GLOBALS["SL"]->slugify($request->newTreeName);
            }
            $tree->tree_opts = 1;
            if ($request->has('pageAdmOnly') && intVal($request->pageAdmOnly) == 1) {
                $tree->tree_opts *= 3;
            }
            if ($request->has('pageStfOnly') && intVal($request->pageStfOnly) == 1) {
                $tree->tree_opts *= Globals::TREEOPT_STAFF;
            }
            if ($request->has('pagePrtOnly') && intVal($request->pagePrtOnly) == 1) {
                $tree->tree_opts *= Globals::TREEOPT_PARTNER;
            }
            if ($request->has('pageVolOnly') && intVal($request->pageVolOnly) == 1) {
                $tree->tree_opts *= Globals::TREEOPT_VOLUNTEER;
            }
            $tree->save();
            $treeXML = new SLTree;
            $treeXML->tree_database = $tree->tree_database;
            $treeXML->tree_user = $tree->tree_user;
            $treeXML->tree_type = 'Survey XML';
            $treeXML->tree_name = $tree->tree_name;
            $treeXML->tree_slug = $tree->tree_slug;
            $treeXML->tree_opts = $tree->tree_opts;
            $treeXML->save();
            $redir = '/dashboard/surv-' . $tree->tree_id . '/map?all=1&alt=1&refresh=1';
            return $this->redir($redir);
        }
        $this->v["myTrees"] = SLTree::where('tree_database', $GLOBALS["SL"]->dbID)
            ->where('tree_type', 'LIKE', 'Survey')
            ->orderBy('tree_name', 'asc')
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
        $this->admControlInit($request, (($pageType == 'Page') 
            ? '/dashboard/pages' : (($pageType == 'Report') 
                ? '/dashboard/reports' : '/dashboard/redirects')));
        $this->startNewPage($request, $pageType);
        $this->v["myRdr"] = [
            "home"  => [], 
            "volun" => [], 
            "partn" => [], 
            "staff" => [], 
            "admin" => []
        ];
        $this->v["myPages"] = $GLOBALS["SL"]->x["pageUrls"] 
            = $GLOBALS["SL"]->x["myRedirs"] = [];
        if ($pageType == 'Redirect') {
            $this->pagesRedirSaves($request);
            $chk = SLTree::where('tree_database', $GLOBALS["SL"]->dbID)
                ->where('tree_type', 'LIKE', 'Redirect')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $i => $redir) {
                    $redirUrl = '/' . (($redir->tree_opts%Globals::TREEOPT_ADMIN == 0 
                        || $redir->tree_opts%Globals::TREEOPT_VOLUNTEER == 0 
                        || $redir->tree_opts%Globals::TREEOPT_PARTNER == 0 
                        || $redir->tree_opts%Globals::TREEOPT_STAFF == 0) ? 'dash/' : '') . $redir->tree_slug;
                    if (!isset($GLOBALS["SL"]->x["myRedirs"][$redir->tree_slug])) {
                        $GLOBALS["SL"]->x["myRedirs"][$redir->tree_slug] = '';
                    }
                    $GLOBALS["SL"]->x["myRedirs"][$redir->tree_slug] .= '<br />'
                        . '<i class="mL5 mR5 slGreenDark">also redirects from</i><a href="' 
                        . $redir->tree_desc . '" target="_blank">' . $redir->tree_desc . '</a>';
                    if (!in_array($redirUrl, $GLOBALS["SL"]->x["pageUrls"])) {
                        $type = (($redir->tree_opts%Globals::TREEOPT_ADMIN == 0) ? 'admin'
                            : (($redir->tree_opts%Globals::TREEOPT_VOLUNTEER == 0) ? 'volun'
                                : (($redir->tree_opts%Globals::TREEOPT_PARTNER == 0) ? 'partn'
                                    : (($redir->tree_opts%Globals::TREEOPT_STAFF == 0) ? 'staff'
                                        : 'home'))));
                        $this->v["myRdr"][$type][] = [
                            $redirUrl,
                            $redir->tree_desc,
                            $redir->tree_id
                        ];
                    }
                }
            }
        } else { // not Redirect
            $this->v["myPages"] = [];
            $chk = SLTree::where('tree_database', $GLOBALS["SL"]->dbID)
               // ->whereRaw('tree_opts%' . Globals::TREEOPT_REPORT . ' ' 
               //     . (($pageType == 'Report') ? '=' : '>') . ' 0')
                ->where('tree_type', 'LIKE', 'Page')
                ->orderBy('tree_name', 'asc')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $i => $tree) {
                    if (($pageType == 'Report' && $tree->tree_opts%Globals::TREEOPT_REPORT == 0)
                        || ($pageType != 'Report' && $tree->tree_opts%Globals::TREEOPT_REPORT > 0)) {
                        $this->v["myPages"][] = $tree;
                        if ($tree->tree_opts%Globals::TREEOPT_ADMIN == 0 
                            && $tree->tree_opts%Globals::TREEOPT_HOMEPAGE == 0) {
                            $GLOBALS["SL"]->x["pageUrls"][$tree->tree_id] = '/dashboard';
                        } else {
                            $GLOBALS["SL"]->x["pageUrls"][$tree->tree_id] = '/' 
                                . (($tree->tree_opts%Globals::TREEOPT_ADMIN == 0 
                                    || $tree->tree_opts%Globals::TREEOPT_VOLUNTEER == 0 
                                    || $tree->tree_opts%Globals::TREEOPT_PARTNER == 0 
                                    || $tree->tree_opts%Globals::TREEOPT_STAFF == 0) ? 'dash/' : '')
                                . $tree->tree_slug;
                        }
                    }
                }
            }
            $this->v["autopages"] = [ "contact" => false ];
            if (sizeof($this->v["myPages"]) > 0) {
                foreach ($this->v["myPages"] as $page) {
                    if ($page->tree_opts%Globals::TREEOPT_CONTACT == 0) {
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
            $tree->tree_database = $GLOBALS["SL"]->dbID;
            $tree->tree_user = $this->v["uID"];
            $tree->tree_type = 'Page';
            $tree->tree_name = trim($request->newPageName);
            $tree->tree_slug = trim($request->newPageSlug);
            if ($tree->tree_slug == '') {
                $tree->tree_slug = $GLOBALS["SL"]->slugify($request->newPageName);
            }
            $tree->tree_opts = 1;
            if ($request->has('pageAdmOnly') && intVal($request->pageAdmOnly) == 1) {
                $tree->tree_opts *= Globals::TREEOPT_ADMIN;
            }
            if ($request->has('pageStfOnly') && intVal($request->pageStfOnly) == 1) {
                $tree->tree_opts *= Globals::TREEOPT_STAFF;
            }
            if ($request->has('pagePrtOnly') && intVal($request->pagePrtOnly) == 1) {
                $tree->tree_opts *= Globals::TREEOPT_PARTNER;
            }
            if ($request->has('pageVolOnly') && intVal($request->pageVolOnly) == 1) {
                $tree->tree_opts *= Globals::TREEOPT_VOLUNTEER;
            }
            if ($request->has('pageIsReport') && intVal($request->pageIsReport) == 1) {
                $tree->tree_opts *= Globals::TREEOPT_REPORT;
            }
            if ($request->has('pageIsSearch') && intVal($request->pageIsSearch) == 1) {
                $tree->tree_opts *= Globals::TREEOPT_SEARCH;
            }
            $tree->save();
            if ($request->has('reportPageTree') && intVal($request->reportPageTree) > 0
                && ($tree->tree_opts%Globals::TREEOPT_REPORT == 0 
                    || $tree->tree_opts%Globals::TREEOPT_SEARCH == 0)) {
                $chkTree = SLTree::find($request->reportPageTree);
                if ($chkTree && isset($chkTree->tree_id)) {
                    $tree->update([ 'TreeCoreTable' => $chkTree->tree_core_table ]);
                    $newRoot = $this->createRootNode($tree);
                    if ($newRoot && isset($newRoot->node_id)) {
                        $newRoot->node_response_set = $request->reportPageTree;
                        $newRoot->save();
                    }
                }
            }
            $redir = '/dashboard/page/' . $tree->tree_id . '?all=1&alt=1&refresh=1';
            echo $this->redir($redir, true);
            exit;
        }
        return false;
    }
    
    protected function pagesRedirSaves(Request $request)
    {
        if ($request->has('subRedir') && $request->has('newRedirFrom')) {
            $tree = new SLTree;
            $tree->tree_database = $GLOBALS["SL"]->dbID;
            $tree->tree_user = $this->v["uID"];
            $tree->tree_type = 'Redirect';
            $tree->tree_desc = trim($request->newRedirTo);
            $tree->tree_slug = trim($request->newRedirFrom);
            $tree->tree_opts = 1;
            if ($request->has('redirAdmOnly') && intVal($request->redirAdmOnly) == 1) {
                $tree->tree_opts *= Globals::TREEOPT_ADMIN;
            }
            if ($request->has('redirStfOnly') && intVal($request->redirStfOnly) == 1) {
                $tree->tree_opts *= Globals::TREEOPT_STAFF;
            }
            if ($request->has('redirPrtOnly') && intVal($request->redirPrtOnly) == 1) {
                $tree->tree_opts *= Globals::TREEOPT_PARTNER;
            }
            if ($request->has('redirVolOnly') && intVal($request->redirVolOnly) == 1) {
                $tree->tree_opts *= Globals::TREEOPT_VOLUNTEER;
            }
            $tree->save();
            echo $this->redir('/dashboard/redirects', true);
            exit;
        }
        if ($request->has('redirEdit') && intVal($request->get('redirEdit')) > 0 
            && $request->has('redirTo') && $request->has('redirFrom')) {
            $tree = SLTree::find(intVal($request->get('redirEdit')));
            if ($tree && isset($tree->tree_id)) {
                $tree->tree_desc = trim($request->redirTo);
                $tree->tree_slug = trim($request->redirFrom);
                $tree->save();
            }
            echo $this->redir('/dashboard/redirects', true);
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
            $GLOBALS["SL"]->treeRow->tree_name       = trim($request->get('TreeName'));
            $GLOBALS["SL"]->treeRow->tree_slug       = trim($request->get('TreeSlug'));
            $GLOBALS["SL"]->treeRow->tree_core_table = $GLOBALS["SL"]->tblI[$request->get('TreeCoreTable')];
            $GLOBALS["SL"]->treeRow->tree_opts = 1;
            $opts = [
                Globals::TREEOPT_SKINNY,
                Globals::TREEOPT_ADMIN,
                Globals::TREEOPT_HOMEPAGE,
                Globals::TREEOPT_NOEDITS,
                Globals::TREEOPT_REPORT,
                Globals::TREEOPT_SEARCH,
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
                if ($GLOBALS["SL"]->REQ->has('opt' . $o) 
                    && intVal($GLOBALS["SL"]->REQ->get('opt' . $o)) == $o) {
                    $GLOBALS["SL"]->treeRow->tree_opts *= $o;
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
                $chk = SLDefinitions::where('def_database', $this->dbID)
                    ->where('def_set', 'Tree Settings')
                    ->where('def_subset', 'LIKE', 'tree-' . $this->treeID . '-protip')
                    ->where('def_order', $i)
                    ->update([ 'def_description' => $request->get('proTip' . $i) ]);
                if (!$chk) {
                    $chk = new SLDefinitions;
                    $chk->def_database    = $this->dbID;
                    $chk->def_set         = 'Tree Settings';
                    $chk->def_subset      = 'tree-' . $this->treeID . '-protip';
                    $chk->def_order       = $i;
                    $chk->def_description = $request->get('proTip' . $i);
                    $chk->save();
                }
                $chk = SLDefinitions::where('def_database', $this->dbID)
                    ->where('def_set', 'Tree Settings')
                    ->where('def_subset', 'LIKE', 'tree-' . $this->treeID . '-protipimg')
                    ->where('def_order', $i)
                    ->update([ 'def_description' => $request->get('proTipImg' . $i) ]);
                if (!$chk) {
                    $chk = new SLDefinitions;
                    $chk->def_database    = $this->dbID;
                    $chk->def_set         = 'Tree Settings';
                    $chk->def_subset      = 'tree-' . $this->treeID . '-protipimg';
                    $chk->def_order       = $i;
                    $chk->def_description = $request->get('proTipImg' . $i);
                    $chk->save();
                }
            } else { // empty tip row
                $chk = SLDefinitions::where('def_database', $this->dbID)
                    ->where('def_set', 'Tree Settings')
                    ->where('def_subset', 'LIKE', 'tree-' . $this->treeID . '-protip%')
                    ->where('def_order', $i)
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
        $this->v["blurbRows"] = SLDefinitions::where('def_set', 'Blurbs')
            ->orderBy('def_subset')
            ->get();
        return view('vendor.survloop.admin.tree.snippets', $this->v);
    }
    
    public function autoAddPages(Request $request, $addPageType = '')
    {
        if ($addPageType == 'contact') {
            $survInst = new SurvloopInstaller;
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
                if ($found && isset($found->data_sub_tree)) {
                    $found->delete();
                }
            } elseif ($request->has('newSub') && $request->has('newSubset')) {
                $splits = explode(':', $request->input('newSubset'));
                $newSubset = new SLDataSubsets;
                $newSubset->data_sub_tree     = $GLOBALS["SL"]->treeID;
                $newSubset->data_sub_tbl      = $splits[0];
                $newSubset->data_sub_tbl_lnk  = $splits[1];
                $newSubset->data_sub_sub_tbl  = $splits[2];
                $newSubset->data_sub_sub_lnk  = $splits[3];
                $newSubset->data_sub_auto_gen = $request->input('newSubAuto');
                $newSubset->save();
            } elseif ($request->has('delHelper')) {
                $found = SLDataHelpers::find($request->input('delHelper'));
                if ($found && isset($found->data_help_tree)) {
                    $found->delete();
                }
            } elseif ($request->has('newHelper')) {
                $splits = explode(':', $request->input('newHelper'));
                $valFld = str_replace($splits[2].':', '', $request->input('newHelperValue'));
                if (isset($splits[2])) {
                    $newHelp = new SLDataHelpers;
                    $newHelp->data_help_tree         = $GLOBALS["SL"]->treeID;
                    $newHelp->data_help_parent_table = $splits[0];
                    $newHelp->data_help_table        = $splits[2];
                    $newHelp->data_help_key_field    = $splits[3];
                    $newHelp->data_help_value_field  = $valFld;
                    $newHelp->save();
                }
            } elseif ($request->has('delLinkage')) {
                $found = SLDataLinks::where('data_link_tree', $GLOBALS["SL"]->treeID)
                    ->where('data_link_table', $request->input('delLinkage'))
                    ->first();
                if ($found && isset($found->data_link_tree)) {
                    $found->delete();
                    unset($GLOBALS["SL"]->dataLinksOn[$found->data_link_table]);
                }
            } elseif ($request->has('newLinkage')
                && intVal($request->input('newLinkage')) > 0) {
                $linkTbl = intVal($request->input('newLinkage'));
                $newLink = new SLDataLinks;
                $newLink->data_link_tree = intVal($GLOBALS["SL"]->treeID);
                $newLink->data_link_table = $linkTbl;
                $newLink->save();
                $GLOBALS["SL"]->dataLinksOn[$request->input('newLinkage')] 
                    = $GLOBALS["SL"]->getLinkTblMap($linkTbl);
            }
        }
        
        if (!$this->checkCache() || $request->has('dataStruct')
            || $request->has('refresh')) {
            $this->v["content"] = view(
                'vendor.survloop.admin.tree.tree-data', 
                $this->v
            )->render();
            $this->saveCache();
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    public function nodeEdit(Request $request, $treeID = -3, $nID = -3) 
    {
        $node = [];
        if ($nID > 0) {
            $this->loadDbFromNode($request, $nID);
        } elseif ($request->has('parent') 
            && intVal($request->get('parent')) > 0) {
            $this->loadDbFromNode($request, $request->get('parent'));
        } elseif ($request->has('nodeParentID') 
            && intVal($request->nodeParentID) > 0) {
            $this->loadDbFromNode($request, $request->nodeParentID);
        }
        if (!isset($GLOBALS["SL"])) {
            $GLOBALS["SL"] = new Globals(
                $request, 
                $this->dbID, 
                $this->treeID, 
                $this->treeID
            );
        }
        $currPage = '/dashboard/surv-' . $treeID . '/map?all=1&alt=1';
        if ($GLOBALS["SL"]->treeRow->tree_type == 'Page') {
            //$currPage = '/dashboard/page/' . $treeID . '?all=1&alt=1';
            $currPage = '/dashboard/pages';
        }
        $this->admControlInit($request, $currPage);
        $this->v["content"] = $this->v["treeAdmin"]->adminNodeEdit($nID, $request, $currPage);
        if (isset($this->v["treeAdmin"]->v["needsWsyiwyg"]) 
            && $this->v["treeAdmin"]->v["needsWsyiwyg"]) {
            $this->v["needsWsyiwyg"] = true;
        }
        return view('vendor.survloop.master', $this->v);
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
                        SLConditions::find($cond->cond_id)
                            ->delete();
                        SLConditionsVals::where('cond_val_cond_id', $cond->cond_id)
                            ->delete();
                        SLConditionsArticles::where('article_cond_id', $cond->cond_id)
                            ->delete();
                    }
                }
            }
        }
        $this->v["condSplits"] = $this->loadCondList();
        $this->v["condIDs"] = '';
        if ($this->v["condSplits"] && sizeof($this->v["condSplits"]) > 0) {
            foreach ($this->v["condSplits"] as $i => $cond) {
                $this->v["condIDs"] .= ',' . $cond->cond_id;
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
        if ($this->v["cond"] && isset($this->v["cond"]->cond_tag)) {
            if ($request->has('editCond') && intVal($request->get('editCond')) == 1) {
                $GLOBALS["SL"]->saveEditCondition($request);
                $this->v["cond"] = SLConditions::find($cid);
            }
            $this->v["cond"]->loadVals();
            $this->loadCondArticles($cid);
            $GLOBALS["SL"]->pageAJAX .= view(
                'vendor.survloop.admin.db.inc-addCondition-ajax', 
                [
                    "newOnly"      => false,
                    "cond"         => $this->v["cond"],
                    "condArticles" => $this->v["condArticles"]
                ]
            )->render();
            return view('vendor.survloop.admin.db.inc-condition-edit', $this->v);
        }
        return $this->redir('/dashboard/db/conds');
    }
    
    protected function getRawConds()
    {
        $qry = "SELECT `cond_id` FROM `sl_conditions` WHERE ";
        if ($this->v["filtOnly"] == 'public') {
            return DB::select($qry . "`cond_opts`%2 = 0 ORDER BY `cond_tag`");
        } elseif ($this->v["filtOnly"] == 'articles') {
            return DB::select($qry . "`cond_opts`%3 = 0 ORDER BY `cond_tag`");
        }
        return SLConditions::orderBy('cond_tag', 'asc')
            ->get();
    }
    
    public function loadCondList()
    {
        $condsRaw = [];
        $condsTmp = $this->getRawConds();
        if ($condsTmp && sizeof($condsTmp) > 0) {
            foreach ($condsTmp as $c) {
                $condsRaw[] = SLConditions::find($c->cond_id);
            }
        }
        if ($condsRaw && sizeof($condsRaw) > 0) {
            foreach ($condsRaw as $i => $c) {
                if ($condsRaw[$i] && isset($condsRaw[$i]->cond_id)) {
                    $condsRaw[$i]->loadVals();
                }
            }
        }
        return $condsRaw;
    }
    
    
    public function loadCondArticles($cid = -3)
    {
        $this->v["condArticles"] = $arts = [];
        if ($cid > 0) {
            $arts = SLConditionsArticles::where('article_cond_id', $cid)
                ->get();
        } else {
            $arts = SLConditionsArticles::get();
        }
        if ($arts && sizeof($arts) > 0) {
            foreach ($arts as $i => $art) {
                if (!isset($this->v["condArticles"][$art->article_cond_id])) {
                    $this->v["condArticles"][$art->article_cond_id] = [];
                }
                if (trim($art->article_url) !== '') {
                    $this->v["condArticles"][$art->article_cond_id][] = [
                        trim($art->article_title), 
                        trim($art->article_url)
                    ];
                }
            }
        }
        return true;
    }
    
    public function xmlmap(Request $request, $treeID = -3)
    {
        $this->initLoader();
        $origTree = $treeID;
        $xmlTree = SLTree::find($treeID);
        if ($xmlTree 
            && isset($xmlTree->tree_type) 
            && isset($xmlTree->tree_slug)
            && $xmlTree->tree_type == 'Survey XML') {
            $chk = SLTree::where('tree_type', 'Survey')
                ->where('tree_core_table', $xmlTree->tree_core_table)
                ->first();
            if ($chk && isset($chk->tree_id)) {
                $origTree = $chk->tree_id;
            }
        }
        $this->loader->syncDataTrees($request, -3, $origTree);
//echo 'xmlmap(' . $treeID . ', orig: ' . $origTree . '<pre>'; print_r($xmlTree); echo '</pre>'; exit;
        //$this->switchTree($origTree, '/dashboard/tree/switch', $request);
        if ($origTree != $treeID && $xmlTree && isset($xmlTree->tree_opts)) {
            $GLOBALS["SL"]->xmlTree["id"] = $xmlTree->tree_id;
            $GLOBALS["SL"]->xmlTree["coreTbl"] 
                = $GLOBALS["SL"]->xmlTree["slug"] 
                = '';
            $GLOBALS["SL"]->xmlTree["root"] 
                = $GLOBALS["SL"]->xmlTree["coreTblID"] 
                = $GLOBALS["SL"]->xmlTree["opts"] 
                = 0;
            if (intVal($xmlTree->tree_root) > 0) {
                $GLOBALS["SL"]->xmlTree["root"] = $xmlTree->tree_root;
            }
            if (intVal($xmlTree->tree_core_table) > 0) {
                $GLOBALS["SL"]->xmlTree["coreTblID"] = $xmlTree->tree_core_table;
                $GLOBALS["SL"]->xmlTree["coreTbl"] 
                    = $GLOBALS["SL"]->tbl[$xmlTree->tree_core_table];
            }
            if (trim($xmlTree->tree_slug) != '') {
                $GLOBALS["SL"]->xmlTree["slug"] = $xmlTree->tree_slug;
            }
            if (intVal($xmlTree->tree_opts) > 0) {
                $GLOBALS["SL"]->xmlTree["opts"] = intVal($xmlTree->tree_opts);
            }
        }
        if (isset($GLOBALS["SL"]->xmlTree["id"])) {
            $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/xmlmap');
            $GLOBALS["SL"]->x["isXmlMap"] = true;
            $xmlmap = new TreeSurvAPI;
            $xmlmap->loadTree($GLOBALS["SL"]->xmlTree["id"], $request);
            $this->v["adminPrintFullTree"] = $xmlmap->adminPrintFullTree($request);
            $GLOBALS["SL"]->pageAJAX .= '$(document).on("click", "#editXmlMap", '
                . 'function() { $(".editXml").css("display","inline"); });';
            return view('vendor.survloop.admin.tree.xmlmap', $this->v);
        }
        return '';
    }
    
    public function xmlNodeEdit(Request $request, $treeID = -3, $nID = -3)
    {
        $this->initLoader();
        $origTree = $treeID;
        $this->loader->syncDataTrees($request, -3, $treeID);
        //$this->switchTree($treeID, '/dashboard/tree/switch', $request);
        $this->admControlInit($request, '/dashboard/surv-' . $origTree . '/xmlmap');
        $xmlmap = new TreeSurvAPI;
        $xmlmap->loadTree($treeID, $request, true);
        $this->v["content"] = $xmlmap->adminNodeEditXML($request, $nID);
        return view('vendor.survloop.master', $this->v);
    }
    
    protected function updateSysSet($set, $val)
    {
        if ($this->v["user"] && $this->v["user"]->hasRole('administrator')) {
            SLDefinitions::where('def_database', '=', 1)
                ->where('def_set', '=', 'System Settings')
                ->where('def_subset', '=', $set)
                ->update([ 'def_description' => $val ]);
        }
        return true;
    }
    
    public function freshDBstore(Request $request, &$db)
    {
        $db->db_user    = $this->v["uID"];
        if ($request->has('DbPrefix')) {
            $db->db_prefix = trim($request->DbPrefix);
        }
        if ($request->has('DbName')) {
            $db->db_name = trim($request->DbName);
        }
        if ($request->has('DbDesc')) {
            $db->db_desc = trim($request->DbDesc);
        }
        if ($request->has('DbMission')) {
            $db->db_mission = trim($request->DbMission);
        }
        $db->save();
        $GLOBALS["SL"] = new Globals($request, $db->db_id, -3);
        return $db;
    }
    
    public function freshDB(Request $request)
    {
        $this->survloopInit($request, '/fresh/database', false);
        $chk = SLUsersRoles::get();
        if ($chk->isEmpty()) {
            $this->v["user"]->assignRole('administrator');
            $this->logPageVisit('NEW SYSTEM ADMINISTRATOR!');
        }
        $this->v["isFresh"] = true;
        if ($request->has('freshSub') 
            && intVal($request->freshSub) == 1
            && $this->v["user"] 
            && $this->v["user"]->hasRole('administrator')) {
            // Initialize Database Record Settings
            $db = SLDatabases::find(1);
            if (!$db) {
                $db = new SLDatabases;
                $db->db_id = 1;
            }
            $this->freshDBstore($request, $db);
            $this->logPageVisit('/fresh/database', $db->db_id . ';0');
            
            // Initialize system-wide settings
            $this->updateSysSet('cust-abbr', trim($request->DbPrefix));
            $this->updateSysSet('site-name', trim($request->DbName));
            $this->updateSysSet('meta-desc', trim($request->DbName));
            $title = trim($request->DbDesc . ' | ' . trim($request->DbName));
            $this->updateSysSet('meta-title', $title);
            
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
        $this->survloopInit($request, '/dashboard/db/new', false);
        $this->v["isFresh"] = false;
        if ($request->has('freshSub') 
            && intVal($request->freshSub) == 1
            && $this->v["user"] 
            && $this->v["user"]->hasRole('administrator')) {
            // Initialize Database Record Settings
            $db = new SLDatabases;
            $this->freshDBstore($request, $db);
            $this->logPageVisit('/fresh/database', $db->db_id . ';0');
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
        $fileName = '../app/Http/Controllers/' . trim($dbPrefix) 
            . '/' . trim($dbPrefix) . '.php';
        $file = "<"."?"."php\n" . view(
            'vendor.survloop.admin.fresh-install-class-core', 
            [ "abbr" => trim($dbPrefix) ]
        )->render();
        if (is_writable($fileName)) {
            file_put_contents($fileName, $file);
        }
        $file = "<"."?"."php\n" . view(
            'vendor.survloop.admin.fresh-install-class-report', 
            [ "abbr" => trim($dbPrefix) ]
        )->render();
        if (is_writable($fileName)) {
            file_put_contents(str_replace('.php', 'Report.php', $fileName), $file);
        }
        $file = "<"."?"."php\n" . view(
            'vendor.survloop.admin.fresh-install-class-admin', 
            [ "abbr" => trim($dbPrefix) ]
        )->render();
        if (is_writable($fileName)) {
            file_put_contents(str_replace('.php', 'Admin.php', $fileName), $file);
        }
        return true;
    }
    
/* needs re-testing */
    public function freshUXstore(Request $request, $tree, $currPage = '')
    {
        $tableName = trim($request->TreeTable);
        $coreTbl = SLTables::where('tbl_database', $GLOBALS["SL"]->dbID)
            ->where('tbl_eng', $tableName)
            ->first();
        if (!$coreTbl) {
            $coreTbl = new SLTables;
            $coreTbl->tbl_database = $GLOBALS["SL"]->dbID;
            $coreTbl->tbl_eng      = $tableName;
            $coreTbl->tbl_name     = $this->eng2data($tableName);
            $coreTbl->tbl_abbr     = $this->eng2abbr($tableName);
            $coreTbl->tbl_desc     = trim($request->TreeDesc);
            $coreTbl->save();
        }

        $userTbl = $GLOBALS["SL"]->loadUsrTblRow();
        if (!$userTbl) {
            $userTbl = new SLTables;
            $userTbl->tbl_database = $GLOBALS["SL"]->dbID;
            $userTbl->tbl_eng      = 'Users';
            $userTbl->tbl_name     = 'users';
            $userTbl->tbl_abbr     = '';
            $userTbl->tbl_group    = 'Internal';
            $userTbl->tbl_desc     = 'This represents the Laravel Users table, but will not '
                . 'actually be implemented by Survloop as part of the database installation.';
            $userTbl->save();
        }
        
        $tree->tree_name = trim($request->TreeName);
        $tree->tree_desc = trim($request->TreeDesc);
        $tree->tree_slug = $GLOBALS["SL"]->slugify($tableName);
        $tree->save();
        $tree = $this->initTree($tree, $coreTbl, $userTbl, 'Survey');
        $this->initTreeXML($tree, $coreTbl, 'Survey XML');
        
        $this->installNewCoreTable($coreTbl);
        
        $GLOBALS["SL"] = new Globals($request, $GLOBALS["SL"]->dbID, $coreTbl->tree_id);
        return true;
    }
    
    public function freshUX(Request $request)
    {
        $this->survloopInit($request, '/fresh/survey', false);
        $this->v["isFresh"] = true;
        if ($request->has('freshSub') 
            && intVal($request->freshSub) == 1
            && $this->v["user"] 
            && $this->v["user"]->hasRole('administrator')) {
            $tree = SLTree::find(1);
            if (!$tree) {
                $tree = new SLTree;
                $tree->tree_id = 1;
            }
            $tree = $this->freshUXstore($request, $tree, '/fresh/survey');
            return $this->redir('/dashboard/settings?refresh=1');
            //return $this->redir('/dashboard/surv-' . $tree->tree_id . '/map?all=1&alt=1');
        }
        return view('vendor.survloop.admin.fresh-install-setup-ux', $this->v);
    }
    
    public function newTree(Request $request)
    {
        $this->survloopInit($request, '/dashboard/tree/new', false);
        $this->v["isFresh"] = false;
        if ($request->has('freshSub') 
            && intVal($request->freshSub) == 1
            && $this->v["user"] 
            && $this->v["user"]->hasRole('administrator')) {
            $tree = new SLTree;
            $tree->save();
            $tree = $this->freshUXstore($request, $tree, '/dashboard/tree/new');
            return $this->redir('/dashboard/surv-' . $tree->tree_id . '/map?all=1&alt=1');
        }
        return view('vendor.survloop.admin.fresh-install-setup-ux', $this->v);
    }
    
    protected function initTree($tree, $coreTbl, $userTbl, $type = 'Public')
    {
        $tree->tree_user       = $this->v["uID"];
        $tree->tree_database   = $GLOBALS["SL"]->dbID;
        $tree->tree_core_table = $coreTbl->tbl_id;
        $tree->tree_type       = $type;
        $tree->save();
        
        $this->logPageVisit('/fresh/database', $GLOBALS["SL"]->dbID.';'.$tree->tree_id);
        
        $rootNode = new SLNode;
        $rootNode->node_tree        = $tree->tree_id;
        $rootNode->node_parent_id   = -3;
        $rootNode->node_type        = 'Data Manip: New';
        $rootNode->node_data_branch = $coreTbl->tbl_name;
        $rootNode->save();
        
        $branchNode = new SLNode;
        $branchNode->node_tree        = $tree->tree_id;
        $branchNode->node_parent_id   = $rootNode->node_id;
        $branchNode->node_type        = 'Branch Title';
        $branchNode->node_prompt_text = 'Section 1';
        $branchNode->save();
        
        $pageNode = new SLNode;
        $pageNode->node_tree         = $tree->tree_id;
        $pageNode->node_parent_id    = $branchNode->node_id;
        $pageNode->node_type         = 'Page';
        $pageNode->node_prompt_text  = 'Welcome To ' . $tree->tree_name;
        $pageNode->node_prompt_notes = 'welcome';
        $pageNode->save();
        
        $qNode = new SLNode;
        $qNode->node_tree        = $tree->tree_id;
        $qNode->node_parent_id   = $pageNode->node_id;
        $qNode->node_type        = 'Text';
        $qNode->node_prompt_text = '<h2 class="slBlueDark">Welcome</h2>'
            . 'This is a sample question to a user. '
            . 'What will you ask them first?';
        $qNode->save();
        
        $tree->tree_root       = $rootNode->node_id;
        $tree->tree_first_page = $pageNode->node_id;
        $tree->tree_last_page  = $pageNode->node_id;
        $tree->save();
        
        $GLOBALS["SL"]->initCoreTable($coreTbl, $userTbl);
        
        return $tree;
    }
    
    protected function initTreeXML($tree, $coreTbl, $type = 'Public XML')
    {
        $treeXML = new SLTree;
        $treeXML->tree_name       = $tree->tree_name;
        $treeXML->tree_slug       = $tree->tree_slug;
        $treeXML->tree_user       = $this->v["uID"];
        $treeXML->tree_database   = $GLOBALS["SL"]->dbID;
        $treeXML->tree_type       = 'Survey XML';
        $treeXML->tree_core_table = $coreTbl->tbl_id;
        $treeXML->save();

        $rootNode = new SLNode;
        $rootNode->node_tree         = $treeXML->tree_id;
        $rootNode->node_parent_id    = -3;
        $rootNode->node_type         = 'XML';
        $rootNode->node_prompt_text  = $coreTbl->tbl_name;
        $rootNode->node_prompt_notes = $coreTbl->tbl_id;
        $rootNode->save();
        return $treeXML;
    }
    
    protected function chkAllCoreTbls()
    {
        $chk = SLTree::where('tree_type', 'Survey')
            ->where('tree_core_table', '>', 0)
            ->get();
        $chk = DB::table('sl_tree')
            ->join('sl_tables', 'sl_tables.tbl_id', '=', 'sl_tree.tree_core_table')
            ->where('sl_tree.tree_database', $GLOBALS["SL"]->dbID)
            ->where('sl_tree.tree_type', 'Survey')
            ->select('sl_tables.*')
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
            . $tbl->tbl_abbr . "user_id` bigint(20) unsigned NULL, \n  `"
            . $tbl->tbl_abbr . "submission_progress` int(11) NULL , \n  `" 
            . $tbl->tbl_abbr . "version_ab` varchar(255) NULL , \n  `" 
            . $tbl->tbl_abbr . "unique_str` varchar(50) NULL , \n  `" 
            . $tbl->tbl_abbr . "ip_addy` varchar(255) NULL , \n  `" 
            . $tbl->tbl_abbr . "is_mobile` int(1) NULL , \n"
            . $GLOBALS["SL"]->mysqlTblCoreFinish($tbl);
        return DB::statement($tblQry);
    }
    
}
