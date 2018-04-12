<?php
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Storage;
use Illuminate\Http\Request;

use App\Models\SLDatabases;
use App\Models\SLTables;
use App\Models\SLFields;
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
use App\Models\SLSess;
use App\Models\SLNodeSaves;
use App\Models\SLNodeSavesPage;

use SurvLoop\Controllers\SurvLoopTreeAdmin;
use SurvLoop\Controllers\SurvLoopTreeXML;
use SurvLoop\Controllers\SurvLoopInstaller;
use SurvLoop\Controllers\SurvLoopNode;

class AdminTreeController extends AdminController
{
    
    protected function initExtra(Request $request)
    {
    	if ($this->v["uID"] > 0) {
    		$this->v["allowEdits"] = $this->v["user"]->hasRole('administrator|databaser');
        }
        $this->v["adminOverOpts"] = ((session()->has('adminOverOpts')) 
            ? session()->get('adminOverOpts') : '');
        if (trim($this->v["currPage"][0]) == '') $this->v["currPage"][0] = '/dashboard/tree';
        
        $this->v["treeClassAdmin"] = new SurvLoopTreeAdmin($this->REQ);
        $this->v["treeClassAdmin"]->loadTree($GLOBALS["SL"]->treeID, $this->REQ);
        $this->initExtraCust();
        
        if (!session()->has('chkCoreTbls') || $GLOBALS["SL"]->REQ->has('refresh')) {
            $userTbl = SLTables::where('TblDatabase', $GLOBALS["SL"]->dbID)
                ->where('TblName', 'Users')
                ->first();
            $trees = SLTree::where('TreeDatabase', $GLOBALS["SL"]->dbID)
                ->where('TreeCoreTable', '>', 0)
                ->get();
            if ($trees && sizeof($trees) > 0) {
                foreach ($trees as $tree) {
                    $coreTbl = SLTables::find($tree->TreeCoreTable);
                    $this->initCoreTable($coreTbl, $userTbl);
                }
            }
            $this->allStdCondition('#IsAdmin', 'The user is currently logged in as an administrator.');
            $this->allStdCondition('#IsNotAdmin', 'The user is not currently logged in as an administrator.');
            $this->allStdCondition('#NodeDisabled', 'This node is not active (for the public).');
            $this->allStdCondition('#IsLoggedIn', 'Complainant is currently logged into the system.');
            $this->allStdCondition('#IsNotLoggedIn', 'Complainant is not currently logged into the system.');
            $this->allStdCondition('#EmailVerified', 'Current user\'s email address has been verified.');
            $trees = SLTree::where('TreeType', 'Page')->get();
            if ($trees && sizeof($trees) > 0) {
                foreach ($trees as $tree) $this->v["treeClassAdmin"]->updateTreeOpts($tree->TreeID);
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
        if (!$chk || sizeof($chk) == 0) {
            $newCond = new SLConditions;
            $newCond->CondDatabase = 0;
            $newCond->CondTag = $tag;
            $newCond->CondDesc = $desc;
            $newCond->CondOperator = 'CUSTOM';
            $newCond->save();
        }
        return true;
    }
    
    protected function initExtraCust() { return true; }
    
    protected function loadBelowAdmMenu()
    {
        return $this->loadTreesPagesBelowAdmMenu();
    }
    
    public function index(Request $request, $treeID = -3)
    {
        $this->syncDataTrees($request, -3, $treeID);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/map?all=1&alt=1');
        if (!$this->checkCache()) {
            $this->v["printTree"] = $this->v["treeClassAdmin"]->adminPrintFullTree($request);
            $this->v["ipLegal"] = view('vendor.survloop.dbdesign-legal', [
                "sysOpts" => $GLOBALS["SL"]->sysOpts
            ])->render();
            $this->v["content"] = view('vendor.survloop.admin.tree.tree', $this->v)->render();
            $this->saveCache();
        }
        $treeAbout = view('vendor.survloop.admin.tree.tree-about', [ "showAbout" => false ])->render();
        $this->v["content"] = $treeAbout . $this->v["content"];
        return view('vendor.survloop.master', $this->v);
    }
    
    public function adminPrintFullTreePublic(Request $request, $treeSlug = '')
    {
        $tree = SLTree::where('TreeSlug', $treeSlug)
            ->where('TreeType', 'Survey')
            ->get();
        if ($tree && sizeof($tree) > 0) {
            foreach ($tree as $t) {
                if ($t->TreeOpts%3 > 0) { // no admin trees made public [for now]
                    $this->treeID = $t->TreeID;
                    $this->dbID = $t->TreeDatabase;
                    $GLOBALS["SL"] = new DatabaseLookups($request, $this->dbID, $this->treeID, $this->treeID);
                }
            }
        }
        $this->survLoopInit($request, '/tree/' . $treeSlug);
        if (!$this->checkCache()) {
            $this->v["treeClassAdmin"]->loadTreeNodeStats();
            $GLOBALS["SL"]->x["hideDisabledNodes"] = true;
            $this->v["content"] = '<div class="w33">' 
                . view('vendor.survloop.logo-print', [ "sysOpts" => $GLOBALS["SL"]->sysOpts, "w100" => true ])->render()
                . '</div>' . view('vendor.survloop.print-tree-map-desc', [])->render() 
                . '<div class="p10"></div>' . $GLOBALS["SL"]->printTreeNodeStats(true, true, true) 
                . $this->v["treeClassAdmin"]->adminPrintFullTree($request, true)
                . view('vendor.survloop.dbdesign-legal', ["sysOpts" => $GLOBALS["SL"]->sysOpts ])->render();
            $this->saveCache();
        }
        $this->v["isPrint"] = true;
        return view('vendor.survloop.master', $this->v);
    }
    
    public function indexPage(Request $request, $treeID)
    {
        $tree = SLTree::find($treeID);
        if (!$tree || !isset($tree->TreeName)) return $this->redir('/dashboard/pages/list');
        $this->treeID = $treeID;
        $this->dbID = $tree->TreeDatabase;
        $GLOBALS["SL"] = new DatabaseLookups($request, $this->dbID, $this->treeID, $this->treeID);
        $this->admControlInit($request, '/dashboard/pages/list');
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
            if ($tree->TreeSlug == '') $tree->TreeSlug = $GLOBALS["SL"]->slugify($request->newTreeName);
            $tree->TreeOpts = 1;
            if ($request->has('pageAdmOnly') && intVal($request->pageAdmOnly) == 1) $tree->TreeOpts *= 3;
            if ($request->has('pageVolOnly') && intVal($request->pageVolOnly) == 1) $tree->TreeOpts *= 17;
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
        $this->v["autoTrees"] = [ ];
        if ($this->v["myTrees"] && sizeof($this->v["myTrees"]) > 0) {
            foreach ($this->v["myTrees"] as $tree) {
                //if ($tree->TreeOpts%19 == 0) $this->v["autoTrees"]["contact"] = true;
            }
        }
        $GLOBALS["SL"]->pageAJAX .= '$(document).on("click", "#newTree", function() { '
            . '$("#newTreeForm").slideToggle("fast"); });';
        return view('vendor.survloop.admin.tree.trees', $this->v);
    }
    
    public function pagesList(Request $request)
    {
        $this->admControlInit($request, '/dashboard/pages/list');
        if ($request->has('sub') && $request->has('newPageName')) {
            $tree = new SLTree;
            $tree->TreeDatabase = $GLOBALS["SL"]->dbID;
            $tree->TreeUser = $this->v["uID"];
            $tree->TreeType = 'Page';
            $tree->TreeName = trim($request->newPageName);
            $tree->TreeSlug = trim($request->newPageSlug);
            if ($tree->TreeSlug == '') $tree->TreeSlug = $GLOBALS["SL"]->slugify($request->newPageName);
            $tree->TreeOpts = 1;
            if ($request->has('pageAdmOnly') && intVal($request->pageAdmOnly) == 1) $tree->TreeOpts *= 3;
            if ($request->has('pageVolOnly') && intVal($request->pageVolOnly) == 1) $tree->TreeOpts *= 17;
            if ($request->has('pageIsReport') && intVal($request->pageIsReport) == 1) $tree->TreeOpts *= 13;
            $tree->save();
            if ($tree->TreeOpts%13 == 0 && $request->has('reportPageTree') && intVal($request->reportPageTree) > 0) {
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
            return $this->redir('/dashboard/page/' . $tree->TreeID . '?all=1&alt=1&refresh=1');
        }
        if ($request->has('subRedir') && $request->has('newRedirFrom')) {
            $tree = new SLTree;
            $tree->TreeDatabase = $GLOBALS["SL"]->dbID;
            $tree->TreeUser = $this->v["uID"];
            $tree->TreeType = 'Redirect';
            $tree->TreeDesc = trim($request->newRedirTo);
            $tree->TreeSlug = trim($request->newRedirFrom);
            $tree->TreeOpts = 1;
            if ($request->has('redirAdmOnly') && intVal($request->redirAdmOnly) == 1) $tree->TreeOpts *= 3;
            if ($request->has('redirVolOnly') && intVal($request->redirVolOnly) == 1) $tree->TreeOpts *= 17;
            $tree->save();
            return $this->redir('/dashboard/pages/list');
        }
        if ($request->has('redirEdit') && intVal($request->get('redirEdit')) > 0 
            && $request->has('redirTo') && $request->has('redirFrom')) {
            $tree = SLTree::find(intVal($request->get('redirEdit')));
            if ($tree && isset($tree->TreeID)) {
                $tree->TreeDesc = trim($request->redirTo);
                $tree->TreeSlug = trim($request->redirFrom);
                $tree->save();
            }
            return $this->redir('/dashboard/pages/list');
        }
        $this->v["myRdr"] = [ "home" => [], "volun" => [], "admin" => [] ];
        $GLOBALS["SL"]->x["pageUrls"] = $GLOBALS["SL"]->x["myRedirs"] = [];
        $this->v["myPages"] = SLTree::where('TreeDatabase', $GLOBALS["SL"]->dbID)
            ->where('TreeType', 'LIKE', 'Page')
            ->orderBy('TreeName', 'asc')
            ->get();
        if ($this->v["myPages"] && sizeof($this->v["myPages"]) > 0) {
            foreach ($this->v["myPages"] as $i => $tree) {
                if ($tree->TreeOpts%3 == 0 && $tree->TreeOpts%7 == 0) {
                    $GLOBALS["SL"]->x["pageUrls"][$tree->TreeID] = '/dashboard';
                } else {
                    $GLOBALS["SL"]->x["pageUrls"][$tree->TreeID] = '/' 
                        . (($tree->TreeOpts%3 == 0 || $tree->TreeOpts%17 == 0) ? 'dash/' : '')
                        . $tree->TreeSlug;
                }
            }
        }
        $chk = SLTree::where('TreeDatabase', $GLOBALS["SL"]->dbID)
            ->where('TreeType', 'LIKE', 'Redirect')
            ->get();
        if ($chk && sizeof($chk) > 0) {
            foreach ($chk as $i => $redir) {
                $redirUrl = '/' . (($redir->TreeOpts%3 == 0 || $redir->TreeOpts%17 == 0) ? 'dash/' : '') 
                    . $redir->TreeSlug;
                if (!isset($GLOBALS["SL"]->x["myRedirs"][$redir->TreeSlug])) {
                    $GLOBALS["SL"]->x["myRedirs"][$redir->TreeSlug] = '';
                }
                $GLOBALS["SL"]->x["myRedirs"][$redir->TreeSlug] .= '<br /><i class="mL5 mR5 slGreenDark">also redirects'
                    . ' from</i><a href="' . $redir->TreeDesc . '" target="_blank">' . $redir->TreeDesc . '</a>';
                if (!in_array($redirUrl, $GLOBALS["SL"]->x["pageUrls"])) {
                    $type = (($redir->TreeOpts%3 == 0) ? 'admin' : (($redir->TreeOpts%17 == 0) ? 'volun' : 'home'));
                    $this->v["myRdr"][$type][] = [ $redirUrl, $redir->TreeDesc, $redir->TreeID ];
                }
            }
        }
        $this->v["autopages"] = [ "contact" => false ];
        if ($this->v["myPages"] && sizeof($this->v["myPages"]) > 0) {
            foreach ($this->v["myPages"] as $page) {
                if ($page->TreeOpts%19 == 0) $this->v["autopages"]["contact"] = true;
            }
        }
        return view('vendor.survloop.admin.tree.pages', $this->v);
    }
    
    public function treeSettings(Request $request, $treeID = -3)
    {
        $this->syncDataTrees($request, -3, $treeID);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/map?all=1&alt=1');
        if ($request->has('sub') && $request->has('TreeName') && trim($request->get('TreeName')) != '') {
            $GLOBALS["SL"]->treeRow->TreeName      = trim($request->get('TreeName'));
            $GLOBALS["SL"]->treeRow->TreeSlug      = trim($request->get('TreeSlug'));
            $GLOBALS["SL"]->treeRow->TreeCoreTable = $GLOBALS["SL"]->tblI[$request->get('TreeCoreTable')];
            $GLOBALS["SL"]->treeRow->TreeOpts = 1;
            $opts = [3, 7, 11, 13, 17, 19, 23, 29, 31];
            foreach ($opts as $o) {
                if ($GLOBALS["SL"]->REQ->has('opt'.$o.'') && intVal($GLOBALS["SL"]->REQ->get('opt'.$o.'')) == $o) {
                    $GLOBALS["SL"]->treeRow->TreeOpts *= $o;
                }
            }
            $GLOBALS["SL"]->treeRow->save();
            return redirect('/dashboard/surv-' . $treeID . '/map?all=1&alt=1&refresh=1');
        }
        return view('vendor.survloop.admin.tree.settings', $this->v);
    }
    
    public function blurbsList(Request $request)
    {
        $this->admControlInit($request, '/dashboard/pages/snippets');
        if ($request->has('sublurb')) {
            $blurb = $this->blurbNew($request);
            if ($blurb > 0) return $this->redir('/dashboard/pages/snippets/' . $blurb);
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
        $this->syncDataTrees($request, -3, $treeID);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/data');
        if ($request->has('dataStruct')) {
            if ($request->has('delSub') && intVal($request->input('delSub')) > 0) {
                $found = SLDataSubsets::find($request->input('delSub'));
                if ($found && isset($found->DataSubTree)) $found->delete();
            } elseif ($request->has('newSub') && $request->has('newSubset')) {
                $splits = explode(':', $request->input('newSubset'));
                $newSubset = new SLDataSubsets;
                $newSubset->DataSubTree    = $this->treeID;
                $newSubset->DataSubTbl     = $splits[0];
                $newSubset->DataSubTblLnk  = $splits[1];
                $newSubset->DataSubSubTbl  = $splits[2];
                $newSubset->DataSubSubLnk  = $splits[3];
                $newSubset->DataSubAutoGen = $request->input('newSubAuto');
                $newSubset->save();
            } elseif ($request->has('delHelper')) {
                $found = SLDataHelpers::find($request->input('delHelper'));
                if ($found && isset($found->DataHelpTree)) $found->delete();
            } elseif ($request->has('newHelper')) {
                $splits = explode(':', $request->input('newHelper'));
                $valFld = str_replace($splits[2].':', '', $request->input('newHelperValue'));
                if (isset($splits[2])) {
                    $newHelp = new SLDataHelpers;
                    $newHelp->DataHelpTree        = $this->treeID;
                    $newHelp->DataHelpParentTable = $splits[0];
                    $newHelp->DataHelpTable       = $splits[2];
                    $newHelp->DataHelpKeyField    = $splits[3];
                    $newHelp->DataHelpValueField  = $valFld;
                    $newHelp->save();
                }
            } elseif ($request->has('delLinkage')) {
                $found = SLDataLinks::where('DataLinkTree', $this->treeID)
                    ->where('DataLinkTable', $request->input('delLinkage'))
                    ->first();
                if ($found && isset($found->DataLinkTree)) {
                    $found->delete();
                    unset($GLOBALS["SL"]->dataLinksOn[$found->DataLinkTable]);
                }
            } elseif ($request->has('newLinkage')) {
                $newLink = new SLDataLinks;
                $newLink->DataLinkTree = $this->treeID;
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
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/map?all=1&alt=1');
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') $this->v["currPage"][0] = '/dashboard/pages/list';
        $this->v["content"] = $this->v["treeClassAdmin"]->adminNodeEdit($nID, $request, $this->v["currPage"][0]);
        if (isset($this->v["treeClassAdmin"]->v["needsWsyiwyg"]) && $this->v["treeClassAdmin"]->v["needsWsyiwyg"]) {
            $this->v["needsWsyiwyg"] = true;
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    public function treeStats(Request $request, $treeID = -3) 
    {
        $this->syncDataTrees($request, -3, $treeID);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/stats?all=1');
        if (!$this->checkCache()) {
            $this->v["printTree"] = $this->v["treeClassAdmin"]->adminPrintFullTreeStats($request);
            $this->v["content"] = view('vendor.survloop.admin.tree.treeStats', $this->v)->render();
            $this->saveCache();
        }
        return view('vendor.survloop.master', $this->v);
    }

    public function treeSessions(Request $request, $treeID = -3) 
    {
        $this->syncDataTrees($request, -3, $treeID);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/sessions');
        if (!$this->checkCache()) {
            $this->CustReport->loadTree($this->treeID, $request);
            $this->v["css"] = $this->loadCss();
            
            // clear empties here
            $this->v["dayold"] = mktime(date("H"), date("i"), date("s"), date("m"), date("d")-2, date("Y"));
            eval("\$chk = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) . "::"
                . $this->CustReport->treeSessionsWhereExtra()
                . "where('updated_at', '<', '" . date("Y-m-d H:i:s", $this->v["dayold"]) 
                . "')->get();");
            if ($chk && sizeof($chk) > 0) {
                foreach ($chk as $row) {
                    if ($this->CustReport->chkCoreRecEmpty($row->getKey(), $row)) {
                        eval("\$del = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) . "::find(" 
                            . $row->getKey() . ")->delete();");
                    }
                }
            }
            
            $this->v["nodeTots"] = $this->v["nodeSort"] = $this->v["coreTots"] = [];
            $chk = SLNode::where('NodeTree', $treeID)
                ->whereIn('NodeType', [ 'Page', 'Loop Root' ])
                ->get();
            if ($chk && sizeof($chk) > 0) {
                foreach ($chk as $n) {
                    $tmp = new SurvLoopNode($n->NodeID, $n);
                    $tmp->fillNodeRow();
                    $this->v["nodeTots"][$n->NodeID] = [
                        "cmpl" => [ 0, 0 ],
                        "perc" => intVal($this->CustReport->rawOrderPercent($n->NodeID)),
                        "name" => ((isset($tmp->extraOpts["meta-title"]) && trim($tmp->extraOpts["meta-title"]) != '')
                            ? $tmp->extraOpts["meta-title"] : $n->NodePromptNotes)
                        ];
                    $this->v["nodeSort"][$this->v["nodeTots"][$n->NodeID]["perc"]] = $n->NodeID;
                }
            }
            ksort($this->v["nodeSort"], 1); // SORT_NUMERIC
            $this->v["allPublicCoreIDs"] = $this->CustReport->getAllPublicCoreIDs();
            
            $this->v["last100ids"] = DB::table('SL_NodeSavesPage')
                ->join('SL_Sess', 'SL_NodeSavesPage.PageSaveSession', '=', 'SL_Sess.SessID')
                ->where('SL_Sess.SessTree', '=', $treeID)
                ->where('SL_Sess.SessCoreID', '>', 0)
                //->select('SL_NodeSaves.*', 'SL_Sess.SessCoreID')
                ->orderBy('SL_Sess.created_at', 'desc')
                ->select('SL_Sess.SessCoreID', 'SL_Sess.SessCurrNode', 'SL_Sess.created_at')
                ->distinct()->get([ 'SL_Sess.SessCoreID' ]);
            
            $this->v["graph1data"] = [];
            $this->v["genTots"] = [
                "date" => [ 0, 0, 0, [] ], // incomplete time tot, complete time tot, start date, totals by date
                "cmpl" => [ 0, 0 ], // incomplete (I), complete (C)
                "mobl" => [ 0, 0, [ 0, 0 ], [ 0, 0 ] ] // desktop (D), mobile (M), [ DI, DC ], [ MI, MC ]
                ];
            $nodeTots = $lines = [];
            if (sizeof($this->v["last100ids"]) > 0) {
                foreach ($this->v["last100ids"] as $i => $rec) {
                    $coreTots = $this->analyzeCoreSessions($rec->SessCoreID);
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
                        if (!isset($this->v["genTots"]["date"][3][$date])) $this->v["genTots"]["date"][3][$date] = 0;
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
            $this->v["graph1print"] = view('vendor.survloop.graph-scatter', [
                "currGraphID" => 'treeSessScat',
                "hgt"         => '400px',
                "dotColor"    => $this->v["css"]["color-main-on"],
                "brdColor"    => $this->v["css"]["color-main-grey"],
                "title"       => '<h3 class="mT0 mB10">Duration of Attempt <i class="mL5 mR10">by</i> '
                    . 'Percent Completion</h3>'
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
            $this->v["graph2print"] = view('vendor.survloop.graph-bar', [
                "currGraphID" => 'treeSessCalen',
                "hgt"         => '380px',
                "yAxes"       => '# of Submission Attempts (Active Sessions)',
                "title"       => '<h3 class="mT0 mB10">'
                    . 'Number of Submission Attempts <i class="mL5 mR10">by</i> Date</h3>',
                "graph"       => $this->v["graph2"],
                "css"         => $this->v["css"]
                ])->render();

            $this->v["content"] = view('vendor.survloop.admin.tree.treeSessions', $this->v)->render();
            $this->saveCache();
        }
        $this->v["needsCharts"] = true;
        return view('vendor.survloop.master', $this->v);
    }
    
    protected function analyzeCoreSessions($coreID = -3)
    {
        if ($coreID <= 0) $coreID = $this->coreID;
        if (!is_dir('../storage/app/anlyz')) mkdir('../storage/app/anlyz');
        if (!is_dir('../storage/app/anlyz/t' . $this->CustReport->treeID)) {
            mkdir('../storage/app/anlyz/t' . $this->CustReport->treeID);
        }
        $coreTots = [ "core" => $coreID, "node" => -3, "date" => 0, "dur" => 0, "mobl" => false, "cmpl" => false, 
            "log" => [] ];
        eval("\$coreRec = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) . "::find(" . $coreID . ");");
        if (!$coreRec || !isset($coreRec->updated_at)) return $coreTots;
        $cacheFile = '../storage/app/anlyz/t' . $this->CustReport->treeID . '/c' . $coreID . '.php';
        if (!file_exists($cacheFile) || strtotime($coreRec->updated_at) > $this->v["dayold"]
            || $GLOBALS["SL"]->REQ->has('refresh')) {
            if (in_array($coreID, $this->v["allPublicCoreIDs"])) $coreTots["cmpl"] = true;
            $coreAbbr = $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl];
            if (isset($coreRec->{ $coreAbbr . 'SubmissionProgress' })) {
                $coreTots["node"] = $coreRec->{ $coreAbbr . 'SubmissionProgress' };
                $coreTots["date"] = strtotime($coreRec->created_at);
                if (isset($coreRec->{ $coreAbbr . 'IsMobile' }) && intVal($coreRec->{ $coreAbbr . 'IsMobile' }) == 1) {
                    $coreTots["mobl"] = true;
                }
            }
            $coreLog = '';
            $pages = DB::table('SL_NodeSavesPage')
                ->join('SL_Sess', 'SL_NodeSavesPage.PageSaveSession', '=', 'SL_Sess.SessID')
                ->where('SL_Sess.SessTree', '=', $this->CustReport->treeID)
                ->where('SL_Sess.SessCoreID', '=', $coreID)
                ->orderBy('SL_NodeSavesPage.created_at', 'asc')
                ->select('SL_NodeSavesPage.PageSaveNode', 'SL_NodeSavesPage.created_at')
                ->distinct()
                ->get([ 'SL_Sess.SessCoreID' ]);
            if ($pages && sizeof($pages) > 0) {
                $lastCreateDate = $durMinus = 0;
                foreach ($pages as $i => $p) {
                    $dur = strtotime($p->created_at)-$coreTots["date"];
                    if ($dur >= 0 && isset($this->v["nodeTots"][$p->PageSaveNode])) {
                        $coreLog .= ', [ ' . $dur . ', ' . $p->PageSaveNode . ' ]';
                        $coreTots["dur"] = $dur;
                        if ($lastCreateDate > 0) {
                            $lastGap = strtotime($p->created_at)-$lastCreateDate;
                            if ($lastGap > 3600) $durMinus += $lastGap;
                        } elseif ($dur > 3600) {
                            $durMinus += $dur;
                        }
                        $lastCreateDate = strtotime($p->created_at);
                    }
                }
                $coreTots["dur"] = $coreTots["dur"]-$durMinus;
                if ($coreTots["dur"] < 0) $coreTots["dur"] = 0;
                if (trim($coreLog) != '') $coreLog = substr($coreLog, 1);
            }
            $cacheCode = '$'.'coreTots = [ "core" => ' . $coreID . ', "node" => ' . $coreTots["node"] 
                . ', "date" => ' . ((trim($coreTots["date"]) != '') ? $coreTots["date"] : 0) 
                . ', "dur" => ' . ((trim($coreTots["dur"]) != '') ? $coreTots["dur"] : 0) 
                . ', "mobl" => ' . (($coreTots["mobl"]) ? 'true' : 'false') 
                . ', "cmpl" => ' . (($coreTots["cmpl"]) ? 'true' : 'false') 
                . ', "log" => [ ' . $coreLog . ' ] ];' . "\n";
            file_put_contents($cacheFile, $cacheCode);
        }
        eval(file_get_contents($cacheFile));
        return $coreTots;
    }

    public function workflows(Request $request) 
    {
        $this->admControlInit($request, '/dashboard/db/workflows');
        return view('vendor.survloop.admin.tree.workflows', $this->v);
    }
    
    public function conditions(Request $request) 
    {
        $this->admControlInit($request, '/dashboard/db/conds');
        
        if ($request->has('addNewCond')) $GLOBALS["SL"]->saveEditCondition($request);
        
        $this->v["filtOnly"] = 'all';
        if ($request->has('only')) $this->v["filtOnly"] = $request->get('only');
        $condsRaw = $this->loadCondList();
        if ($request->has('totalConds') && intVal($request->totalConds) > 0) {
            if ($condsRaw && sizeof($condsRaw) > 0) {
                foreach ($condsRaw as $i => $cond) {
                    if ($request->has('CondDelete'.$i.'')) {
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
        $this->CustReport->addCondEditorAjax();
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
        $condsRaw = array();
        $condsTmp = $this->getRawConds();
        if ($condsTmp && sizeof($condsTmp) > 0) {
            foreach ($condsTmp as $c) $condsRaw[] = SLConditions::find($c->CondID);
        }
        if ($condsRaw && sizeof($condsRaw) > 0) {
            foreach ($condsRaw as $i => $c) $condsRaw[$i]->loadVals();
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
        $this->syncDataTrees($request, -3, $treeID);
        //$this->switchTree($treeID, '/dashboard/tree/switch', $request);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/xmlmap');
        $xmlmap = new SurvLoopTreeXML;
        $xmlmap->loadTree($GLOBALS["SL"]->xmlTree["id"], $request);
        $this->v["adminPrintFullTree"] = $xmlmap->adminPrintFullTree($request);
        $GLOBALS["SL"]->pageAJAX .= '$(document).on("click", "#editXmlMap", function() {
            $(".editXml").css("display","inline"); });';
        return view('vendor.survloop.admin.tree.xmlmap', $this->v);
    }
    
    public function xmlNodeEdit(Request $request, $treeID = -3, $nID = -3)
    {
        $this->syncDataTrees($request, -3, $treeID);
        //$this->switchTree($treeID, '/dashboard/tree/switch', $request);
        $this->admControlInit($request, '/dashboard/surv-' . $treeID . '/xmlmap');
        $xmlmap = new SurvLoopTreeXML;
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
        $GLOBALS["SL"] = new DatabaseLookups($request, $db->dbID, -3);
        return $db;
    }
    
    public function freshDB(Request $request)
    {
        $this->survLoopInit($request, '/fresh/database');
        $chk = SLUsersRoles::get();
        if (!$chk || sizeof($chk) == 0) {
            $this->v["user"]->assignRole('administrator');
            $this->logPageVisit('NEW SYSTEM ADMINISTRATOR!');
        }
        $this->v["isFresh"] = true;
        if ($request->has('freshSub') && intVal($request->freshSub) == 1
            && $this->v["user"] && $this->v["user"]->hasRole('administrator')) {
            // Initialize Database Record Settings
            $db = SLDatabases::find(1);
            if (!$db || sizeof($db) == 0) {
                $db = new SLDatabases;
                $db->DbID = 1;
            }
            $db = $this->freshDBstore($request, $db);
            
            $this->logPageVisit('/fresh/database', $db->DbID.';0');
            
            // Initialize system-wide settings
            $this->updateSysSet('cust-abbr', trim($request->DbPrefix));
            $this->updateSysSet('site-name', trim($request->DbName));
            $this->updateSysSet('meta-desc', trim($request->DbName));
            $this->updateSysSet('meta-title', trim($request->DbDesc . ' | ' . trim($request->DbName)));
            
            $this->genDbClasses($request->DbPrefix);
            
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
        
        $file = "<"."?"."php\n" 
            . view('vendor.survloop.admin.fresh-install-class-core', [
                "abbr" => trim($dbPrefix)
            ])->render();
        if (is_writable($fileName)) file_put_contents($fileName, $file);
        
        $file = "<"."?"."php\n" 
            . view('vendor.survloop.admin.fresh-install-class-report', [
                "abbr" => trim($dbPrefix)
            ])->render();
        if (is_writable($fileName)) file_put_contents(str_replace('.php', 'Report.php', $fileName), $file);
        
        $file = "<"."?"."php\n" 
            . view('vendor.survloop.admin.fresh-install-class-admin', [
                "abbr" => trim($dbPrefix)
            ])->render();
        if (is_writable($fileName)) file_put_contents(str_replace('.php', 'Admin.php', $fileName), $file);
        return true;
    }
    
    public function freshUXstore(Request $request, $tree, $currPage = '')
    {
        $tableName = trim($request->TreeTable);
        $coreTbl = SLTables::where('TblDatabase', $GLOBALS["SL"]->dbID)
            ->where('TblEng', $tableName)
            ->first();
        if (!$coreTbl || sizeof($coreTbl) == 0) {
            $coreTbl = new SLTables;
            $coreTbl->TblDatabase = $GLOBALS["SL"]->dbID;
            $coreTbl->TblEng      = $tableName;
            $coreTbl->TblName     = $this->eng2data($tableName);
            $coreTbl->TblAbbr     = $this->eng2abbr($tableName);
            $coreTbl->TblDesc     = trim($request->TreeDesc);
            $coreTbl->save();
        }

        $userTbl = SLTables::where('TblDatabase', $GLOBALS["SL"]->dbID)
            ->where('TblEng', 'Users')
            ->first();
        if (!$userTbl || sizeof($userTbl) == 0) {
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
        
        $GLOBALS["SL"] = new DatabaseLookups($request, $GLOBALS["SL"]->dbID, $tree->TreeID);
        return true;
    }
    
    public function freshUX(Request $request)
    {
        $this->survLoopInit($request, '/fresh/experience');
        $this->v["isFresh"] = true;
        if ($request->has('freshSub') && intVal($request->freshSub) == 1
            && $this->v["user"] && $this->v["user"]->hasRole('administrator')) {
            $tree = SLTree::find(1);
            if (!$tree || sizeof($tree) == 0) {
                $tree = new SLTree;
                $tree->TreeID = 1;
            }
            $tree = $this->freshUXstore($request, $tree, '/fresh/experience');
            return $this->redir('/dashboard/surv-' . $tree->TreeID . '/map?all=1&alt=1');
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
        
        $this->initCoreTable($coreTbl, $userTbl);
        
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
    
    protected function initCoreTable($coreTbl, $userTbl = [])
    {
        if (!$coreTbl || sizeof($coreTbl) == 0) return false;
        $coreFlds = [ [ 
                "FldType" => 'INT', 
                "FldEng"  => 'User ID', 
                "FldName" => 'UserID', 
                "FldDesc" => 'Indicates the unique User ID number of the User '
                    . 'owning the data stored in this record for this Experience.' 
            ], [ 
                "FldType" => 'INT', 
                "FldEng"  => 'Experience Node Progress', 
                "FldName" => 'SubmissionProgress', 
                "FldDesc" => 'Indicates the unique Node ID number of the last '
                    . 'Experience Node loaded during this User\'s Experience.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'Tree Version Number', 
                "FldName" => 'TreeVersion', 
                "FldDesc" => 'Stores the current version number of this User Experience, important for tracking bugs.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'A/B Testing Version', 
                "FldName" => 'VersionAB', 
                "FldDesc" => 'Stores a complex string reflecting all A/B Testing '
                    . 'variations in effect at the time of this User\'s Experience of this Node.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'Unique String For Record', 
                "FldName" => 'UniqueStr', 
                "FldDesc" => 'This unique string is for cases when including the record ID number is not appropriate.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'IP Address', 
                "FldName" => 'IPaddy', 
                "FldDesc" => 'Encrypted IP address of the current user.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'Using Mobile Device', 
                "FldName" => 'IsMobile', 
                "FldDesc" => 'Indicates whether or not the current user is interacting via a mobile deviced.' 
            ]
        ];
        foreach ($coreFlds as $f) {
            $chk = SLFields::where('FldDatabase', $this->dbID)
                ->where('FldTable', $coreTbl->TblID)
                ->where('FldName', $f["FldName"])
                ->get();
            if (!$chk || sizeof($chk) == 0) {
                $fld = new SLFields;
                $fld->FldDatabase         = $this->dbID;
                $fld->FldTable            = $coreTbl->TblID;
                $fld->FldEng              = $f["FldEng"];
                $fld->FldName             = $f["FldName"];
                $fld->FldDesc             = $f["FldDesc"];
                $fld->FldSpecType         = 'Replica';
                $fld->FldType             = $f["FldType"];
                if ($f["FldType"] == 'INT') {
                    $fld->FldDataType     = 'Numeric';
                    $fld->FldCharSupport  = ',Numbers,';
                }
                if ($f["FldName"] == 'UserID') {
                    $fld->FldKeyType      = ',Foreign,';
                    $fld->FldForeignTable = $userTbl->TblID;
                }
                // Options: Auto-Managed By SurvLoop; Internal Use not in XML
                $fld->FldOpts             = 39;
                $fld->save();
            }
        }
        $this->installNewModel($coreTbl, true);
        return true;
    }
    
    protected function installNewModel($tbl, $forceFile = true)
    {
        if ($tbl && sizeof($tbl) > 0 && $tbl->TblName != 'Users') {
            $GLOBALS["SL"]->modelPath($tbl->TblName, $forceFile);
        }
        return true;
    }
    
    protected function installNewCoreTable($tbl)
    {
        $tblQry = $this->exportMysqlTblCoreStart($tbl) 
            . "  `" . $tbl->TblAbbr . "UserID` bigint(20) unsigned NULL, \n"
            . "  `" . $tbl->TblAbbr . "SubmissionProgress` int(11) NULL , \n"
            . "  `" . $tbl->TblAbbr . "VersionAB` varchar(255) NULL , \n"
            . "  `" . $tbl->TblAbbr . "UniqueStr` varchar(50) NULL , \n"
            . "  `" . $tbl->TblAbbr . "IPaddy` varchar(50) NULL , \n"
            . "  `" . $tbl->TblAbbr . "IsMobile` int(1) NULL , \n"
            . $this->exportMysqlTblCoreFinish($tbl);
        return DB::statement($tblQry);
    }
    
    
}
