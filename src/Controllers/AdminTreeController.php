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
use App\Models\SLNodeSavesPage;

use SurvLoop\Controllers\SurvLoopTreeAdmin;
use SurvLoop\Controllers\SurvLoopTreeXML;
use SurvLoop\Controllers\SurvLoopInstaller;

class AdminTreeController extends AdminController
{
    
    protected function initExtra(Request $request)
    {
    	if ($this->v["user"] && isset($this->v["user"]->id)) {
    		$this->v["allowEdits"] = $this->v["user"]->hasRole('administrator|brancher');
        }
        $this->v["adminOverOpts"] = ((session()->has('adminOverOpts')) 
            ? session()->get('adminOverOpts') : '');
        if (trim($this->v["currPage"]) == '') $this->v["currPage"] = '/dashboard/tree';
        
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
            $chk = SLConditions::where('CondDatabase', 0)
                ->where('CondTag', '#IsAdmin')
                ->get();
            if (!$chk || sizeof($chk) == 0) {
                $newCond = new SLConditions;
                $newCond->CondDatabase = 0;
                $newCond->CondTag = '#IsAdmin';
                $newCond->CondDesc = 'The user is currently logged in as an administrator.';
                $newCond->CondOperator = 'CUSTOM';
                $newCond->save();
                $newCond = new SLConditions;
                $newCond->CondDatabase = 0;
                $newCond->CondTag = '#IsNotAdmin';
                $newCond->CondDesc = 'The user is not currently logged in as an administrator.';
                $newCond->CondOperator = 'CUSTOM';
                $newCond->save();
            }
            $chk = SLConditions::where('CondDatabase', 0)
                ->where('CondTag', '#NodeDisabled')
                ->get();
            if (!$chk || sizeof($chk) == 0) {
                $newCond = new SLConditions;
                $newCond->CondDatabase = 0;
                $newCond->CondTag = '#NodeDisabled';
                $newCond->CondDesc = 'This node is not active (for the public).';
                $newCond->CondOperator = 'CUSTOM';
                $newCond->save();
            }
            $chk = SLConditions::where('CondDatabase', 0)
                ->where('CondTag', '#IsLoggedIn')
                ->get();
            if (!$chk || sizeof($chk) == 0) {
                $newCond = new SLConditions;
                $newCond->CondDatabase = 0;
                $newCond->CondTag = '#IsLoggedIn';
                $newCond->CondDesc = 'Complainant is currently logged into the system.';
                $newCond->CondOperator = 'CUSTOM';
                $newCond->save();
                $newCond = new SLConditions;
                $newCond->CondDatabase = 0;
                $newCond->CondTag = '#IsNotLoggedIn';
                $newCond->CondDesc = 'Complainant is not currently logged into the system.';
                $newCond->CondOperator = 'CUSTOM';
                $newCond->save();
            }
            $trees = SLTree::where('TreeType', 'Page')->get();
            if ($trees && sizeof($trees) > 0) {
                foreach ($trees as $tree) $this->v["treeClassAdmin"]->updateTreeOpts($tree->TreeID);
            }
            session()->put('chkCoreTbls', 1);
        }
        if (!isset($GLOBALS["SL"]->treeRow->TreeRoot) || $GLOBALS["SL"]->treeRow->TreeRoot <= 0) {
            $newRoot = new SLNode;
            $newRoot->NodeTree           = $this->treeID;
            $newRoot->NodeParentID       = -3;
            if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
                $newRoot->NodeType       = 'Page';
                $newRoot->NodeDataBranch = $GLOBALS["SL"]->coreTbl;
                if (isset($GLOBALS["SL"]->treeRow->TreeSlug) && trim($GLOBALS["SL"]->treeRow->TreeSlug) != '') {
                    $newRoot->NodePromptNotes = $GLOBALS["SL"]->treeRow->TreeSlug;
                } else {
                    $newRoot->NodePromptNotes = $this->slugify($GLOBALS["SL"]->treeRow->TreeName);
                }
                $newRoot->NodeCharLimit  = -1;
            } else {
                $newRoot->NodeType = 'Data Manip: New';
                $newRoot->NodeDataBranch = $GLOBALS["SL"]->coreTbl;
            }
            $newRoot->save();
            $GLOBALS["SL"]->treeRow->TreeRoot = $newRoot->NodeID;
            if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
                $GLOBALS["SL"]->treeRow->TreeFirstPage = $newRoot->NodeID;
                $GLOBALS["SL"]->treeRow->TreeLastPage = $newRoot->NodeID;
            }
            $GLOBALS["SL"]->treeRow->save();
            if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
                $firstReal = new SLNode;
                $firstReal->NodeTree       = $this->treeID;
                $firstReal->NodeParentID   = $newRoot->NodeID;
                $firstReal->NodeType       = 'Instructions';
                $firstReal->NodePromptText = '<h2>Welcome to ' . $GLOBALS["SL"]->treeRow->TreeName . '.</h2>' . "\n"
                    . '<p>Edit this node to fill in your page! This node could be your entire page, '
                    . 'or just one little component.</p>';
                $firstReal->save();
            }
        }
        set_time_limit(180);
        return true;
    }
    
    protected function initExtraCust() { return true; }
    
    protected function loadBelowAdmMenu()
    {
        return $this->loadTreesPagesBelowAdmMenu();
    }
    
    public function index(Request $request, $treeID = -3)
    {
        $this->treeID = $treeID;
        $this->admControlInit($request, '/dashboard/tree-' . $treeID . '/map?all=1');
        if (!$this->checkCache()) {
            $this->v["printTree"] = $this->v["treeClassAdmin"]->adminPrintFullTree($request);
            $this->v["IPlegal"] = view('vendor.survloop.dbdesign-legal', [
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
            ->where('TreeType', 'Primary Public')
            ->get();
        if ($tree && sizeof($tree) > 0) {
            foreach ($tree as $t) {
                if ($t->TreeOpts%3 > 0) { // no admin trees made public [for now]
                    $this->treeID = $t->TreeID;
                    $this->dbID = $t->TreeDatabase;
                    $isAdmin = (Auth::user() && Auth::user()->hasRole('administrator'));
                    $GLOBALS["SL"] = new DatabaseLookups($request, $isAdmin, $this->dbID, $this->treeID, $this->treeID);
                }
            }
        }
        $this->survLoopInit($request, '/tree/' . $treeSlug);
        if (!$this->checkCache()) {
            $this->v["IPlegal"] = view('vendor.survloop.dbdesign-legal', [
                "sysOpts" => $GLOBALS["SL"]->sysOpts
            ])->render();
            $this->v["content"] = '<div class="pL20"><h2>Core Specifications of ' 
                . $GLOBALS["SL"]->treeRow->TreeName . ' User Experience</h2>' 
                . $this->v["IPlegal"] . '</div><div class="pT20">' 
                . $this->v["treeClassAdmin"]->adminPrintFullTree($request, true) . '</div>';
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
        $GLOBALS["SL"] = new DatabaseLookups($request, $this->isUserAdmin(), $this->dbID, $this->treeID, $this->treeID);
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
    
    public function pagesList(Request $request)
    {
        $this->admControlInit($request, '/dashboard/pages/list');
        if ($request->has('sub') && $request->has('newPageName')) {
            $tree = new SLTree;
            $tree->TreeDatabase = $GLOBALS["SL"]->dbID;
            $tree->TreeUser = $this->v["user"]->id;
            $tree->TreeType = 'Page';
            $tree->TreeName = trim($request->newPageName);
            $tree->TreeSlug = trim($request->newPageSlug);
            if ($tree->TreeSlug == '') $tree->TreeSlug = $this->slugify($request->newPageName);
            $tree->TreeOpts = 1;
            if ($request->has('pageAdmOnly') && intVal($request->pageAdmOnly) == 1) {
                $tree->TreeOpts *= 3;
            }
            $tree->save();
            return $this->redir('/dashboard/page/' . $tree->TreeID . '?all=1&refresh=1');
        }
        if ($request->has('sublurb')) {
            $blurb = $this->blurbNew($request);
            if ($blurb > 0) return $this->redir('/dashboard/blurbs/' . $blurb);
        }
        $this->v["myPages"] = SLTree::where('TreeDatabase', $GLOBALS["SL"]->dbID)
            ->where('TreeType', 'LIKE', 'Page')
            ->orderBy('TreeName', 'asc')
            ->get();
        $this->v["blurbRows"] = SLDefinitions::where('DefSet', 'Blurbs')
            ->orderBy('DefSubset')
            ->get();
        $this->v["autopages"] = [ "contact" => false ];
        if ($this->v["myPages"] && sizeof($this->v["myPages"]) > 0) {
            foreach ($this->v["myPages"] as $page) {
                if ($page->TreeOpts%19 == 0) $this->v["autopages"]["contact"] = true;
            }
        }
        $GLOBALS["SL"]->pageAJAX .= '
            $(document).on("click", "#newPage", function() { $("#newPageForm").slideToggle("fast"); });
            $(document).on("click", "#newBlurb", function() { $("#newBlurbForm").slideToggle("fast"); });';
        return view('vendor.survloop.admin.tree.pages', $this->v);
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
        $this->treeID = $treeID;
        $this->admControlInit($request, '/dashboard/tree-' . $treeID . '/map?all=1');
        if ($request->has('dataStruct')) {
            if ($request->has('delSub')) {
                $found = SLDataSubsets::find($request->input('delSub'))->delete();
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
        $this->admControlInit($request, '/dashboard/tree-' . $treeID . '/map?all=1');
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') $this->v["currPage"] = '/dashboard/pages/list';
        $this->v["content"] = $this->v["treeClassAdmin"]->adminNodeEdit($nID, $request, $this->v["currPage"]);
        if (isset($this->v["treeClassAdmin"]->v["needsWsyiwyg"]) && $this->v["treeClassAdmin"]->v["needsWsyiwyg"]) {
            $this->v["needsWsyiwyg"] = true;
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    public function treeStats(Request $request, $treeID = -3) 
    {
        $this->treeID = $treeID;
        $this->admControlInit($request, '/dashboard/tree-' . $treeID . '/stats?all=1');
        if (!$this->checkCache()) {
            $this->v["printTree"] = $this->v["treeClassAdmin"]->adminPrintFullTreeStats($request);
            $this->v["content"] = view('vendor.survloop.admin.tree.treeStats', $this->v)->render();
            $this->saveCache();
        }
        return view('vendor.survloop.master', $this->v);
    }

    public function treeSessions(Request $request, $treeID = -3) 
    {
        $this->treeID = $treeID;
        $this->admControlInit($request, '/dashboard/tree-' . $treeID . '/sessions');
        if (!$this->checkCache()) {
            $this->CustReport->loadTree($this->treeID, $request);
            $this->v["last100ids"] = [];
            eval("\$chk = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) . "::"
                . $this->CustReport->treeSessionsWhereExtra()
                . "orderBy('created_at', 'desc')->limit(500)->get();");
            if ($chk && sizeof($chk) > 0) {
                $dayold = mktime(date("H"), date("i"), date("s"), date("m"), date("d")-1, date("Y"));
                foreach ($chk as $row) {
                    if (sizeof($this->v["last100ids"]) < 100) {
                        if ($this->CustReport->chkCoreRecEmpty($row->getKey(), $row)) {
                            if (strtotime($row->created_at) < $dayold) {
                                eval("\$del = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl)
                                    . "::find(" . $row->getKey() . ")->delete();");
                            }
                        } else {
                            $this->v["last100ids"][] = [
                                "id"   => $row->getKey(), 
                                "node" => $row->{ $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl] 
                                            . 'SubmissionProgress' },
                                "date" => strtotime($row->created_at)
                            ];
                        }
                    }
                }
            }
            //echo '<pre>'; print_r($this->v["last100ids"]); echo '</pre>';
            $nodeTots = $lines = [];
            if (sizeof($this->v["last100ids"]) > 0) {
                foreach ($this->v["last100ids"] as $rec) {
                    $perc = $this->CustReport->rawOrderPercent($rec["node"]);
                    //echo 'node: ' . $rec["node"] . ', perc: ' . $perc . '<br />';
                    if (!isset($nodeTots[$perc])) {
                        $chk = SLNode::find($rec["node"]);
                        if ($chk && isset($chk->NodePromptNotes)) {
                            $nodeTots[$perc] = [
                                "tot"  => 1,
                                "node" => $rec["node"],
                                "url"  => $chk->NodePromptNotes
                            ];
                        }
                    } else {
                        $nodeTots[$perc]["tot"]++;
                    }
                }
                ksort($nodeTots);
                //echo '<pre>'; print_r($nodeTots); echo '</pre>';
                $lines[0] = [
                    "label"    => 'Last Page Visited', 
                    "brdColor" => '#2b3493', 
                    "dotColor" => 'rgba(75,192,192,1)', 
                    "data"     => [], 
                ];
                if (sizeof($nodeTots) > 0) {
                    foreach ($nodeTots as $perc => $node) {
                        $lines[0]["data"][] = $node["tot"];
                    }
                }
            }
            $this->v["axisLabels"] = [];
            if (sizeof($nodeTots) > 0) {
                foreach ($nodeTots as $perc => $node) {
                    $this->v["axisLabels"][] = '/' . $node["url"] . ' ' . $node["node"];
                }
            }
            $this->v["dataLines"] = '';
            if (sizeof($lines) > 0) {
                foreach ($lines as $l) {
                    $this->v["dataLines"] .= view('vendor.survloop.graph-data-line', $l)->render();
                }
            }
            $this->v["printRawSessions"] = $this->v["nodeUrls"] = $rawSessions = [];
            if (sizeof($this->v["last100ids"]) > 0) {
                foreach ($this->v["last100ids"] as $rec) {
                    $rawSessions[$rec["id"]] = [ "date" => $rec["date"], "pages" => [] ];
                    $pages = SLNodeSavesPage::where('PageSaveSession', $rec["id"])
                        ->orderBy('created_at', 'asc')
                        ->get();
                    if ($pages && sizeof($pages) > 0) {
                        foreach ($pages as $i => $p) {
                            if (!isset($this->v["nodeUrls"][$p->PageSaveNode])) {
                                $chk = SLNode::find($p->PageSaveNode);
                                if ($chk && isset($chk->NodePromptNotes)) {
                                    $this->v["nodeUrls"][$p->PageSaveNode] = $chk->NodePromptNotes;
                                }
                            }
                            $time = 0;
                            if ($i > 0) {
                                $prevDate = $rawSessions[$rec["id"]]["pages"][$i-1]["date"];
                                $time = (strtotime($p->created_at)-strtotime($prevDate))/60;
                            }
                            $rawSessions[$rec["id"]]["pages"][] = [
                                "node" => $p->PageSaveNode, 
                                "date" => $p->created_at, 
                                "time" => $time
                            ];
                        }
                    }
                    $this->v["printRawSessions"][] = view('vendor.survloop.admin.tree.tree-sessions-one', [
                        "recID"    => $rec["id"],
                        "session"  => $rawSessions[$rec["id"]],
                        "nodeUrls" => $this->v["nodeUrls"]
                    ])->render();
                }
            }
            //echo '<pre>'; print_r($this->v["last100ids"]); echo '</pre>';
            $this->v["content"] = view('vendor.survloop.admin.tree.treeSessions', $this->v)->render();
            $this->saveCache();
        }
        return view('vendor.survloop.master', $this->v);
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
                    }
                    
                    $cond->CondOpts = 1;
                    
                    $urls = (($request->has('CondArticles'.$i.'')) ? trim($request->get('CondArticles'.$i.'')) : '');
                    $urls = str_replace(',', ' , ', str_replace('  ', ' ', str_replace('  ', ' ', $urls)));
                    $article = SLConditionsArticles::where('ArticleCondID', $cond->CondID)
                        ->first();
                    if (trim($urls) != '') {
                        $cond->CondOpts *= 3;
                        if (!$article || !isset($article->ArticleCondID)) {
                            $article = new SLConditionsArticles;
                            $article->ArticleCondID = $cond->CondID;
                        }
                        $article->ArticleURL = $urls;
                        $article->save();
                    } elseif ($article && isset($article->ArticleCondID)) {
                        $article->delete();
                    }
                    
                    $cond->CondTag = (($request->has('CondTag'.$i.'')) ? trim($request->get('CondTag'.$i.'')) : '');
                    if (substr($cond->CondTag, 0, 1) != '#') {
                        $cond->CondTag = '#' . $cond->CondTag;
                    }
                    $cond->CondDesc = (($request->has('CondDesc'.$i.'')) ? trim($request->get('CondDesc'.$i.'')) : '');
                    if ($request->has('CondPublicFilter'.$i.'') 
                        && intVal($request->get('CondPublicFilter'.$i.'')) == 1) {
                        $cond->CondOpts *= 2;
                    }
                    $cond->save();
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
        $this->addCondEditorAjax();
        return view('vendor.survloop.admin.tree.conditions', $this->v);
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
    
    
    public function loadCondArticles()
    {
        $this->v["condArticles"] = array();
        $arts = SLConditionsArticles::get();
        if ($arts && sizeof($arts) > 0) {
            foreach ($arts as $i => $art) {
                if (!isset($this->v["condArticles"][$art->ArticleCondID])) {
                    $this->v["condArticles"][$art->ArticleCondID] = array();
                }
                $this->v["condArticles"][$art->ArticleCondID] = array();
                if (trim($art->ArticleURL) !== '') {
                    if (strpos($art->ArticleURL, ',') === false) {
                        $this->v["condArticles"][$art->ArticleCondID][] = $art->ArticleURL;
                    }
                    else $this->v["condArticles"][$art->ArticleCondID] = explode(',', $art->ArticleURL);
                }
            }
        }
        return true;
    }
    
    
    
    
    public function xmlmap(Request $request, $treeID = -3)
    {
        $this->treeID = $treeID;
        $this->switchTree($treeID, '/dashboard/tree/switch', $request);
        $this->admControlInit($request, '/dashboard/tree-' . $treeID . '/map?all=1');
        $xmlmap = new SurvLoopTreeXML;
        $xmlmap->loadTree($GLOBALS["SL"]->xmlTree["id"], $request);
        $this->v["adminPrintFullTree"] = $xmlmap->adminPrintFullTree($request);
        $GLOBALS["SL"]->pageAJAX .= '$(document).on("click", "#editXmlMap", function() {
            $(".editXml").css("display","inline"); });';
        return view('vendor.survloop.admin.tree.xmlmap', $this->v);
    }
    
    public function xmlNodeEdit(Request $request, $treeID = -3, $nID = -3)
    {
        $this->treeID = $treeID;
        $this->switchTree($treeID, '/dashboard/tree/switch', $request);
        $this->admControlInit($request, '/dashboard/tree-' . $treeID . '/map?all=1');
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
        $db->DbUser    = $this->v["user"]->id;
        $db->DbPrefix  = trim($request->DbPrefix) . '_';
        $db->DbName    = trim($request->DbName);
        $db->DbDesc    = trim($request->DbDesc);
        $db->DbMission = trim($request->DbMission);
        $db->save();
        $GLOBALS["SL"] = new DatabaseLookups($request, $this->v["isAdmin"], $db->dbID, -3);
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
        $tree->TreeSlug = $this->slugify($tree->TreeName);
        $tree->save();
        $tree = $this->initTree($tree, $coreTbl, $userTbl, 'Primary Public');
        $this->initTreeXML($tree, $coreTbl, 'Primary Public XML');
        
        $this->installNewCoreTable($coreTbl);
        
        $GLOBALS["SL"] = new DatabaseLookups($request, $this->v["isAdmin"], $GLOBALS["SL"]->dbID, $tree->TreeID);
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
            return $this->redir('/dashboard/tree-' . $tree->TreeID . '/map?all=1');
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
            return $this->redir('/dashboard/tree-' . $tree->TreeID . '/map?all=1');
        }
        return view('vendor.survloop.admin.fresh-install-setup-ux', $this->v);
    }
    
    protected function initTree($tree, $coreTbl, $userTbl, $type = 'Public')
    {
        $tree->TreeUser            = $this->v["user"]->id;
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
        $treeXML->TreeUser         = $this->v["user"]->id;
        $treeXML->TreeDatabase     = $GLOBALS["SL"]->dbID;
        $treeXML->TreeType         = 'Primary Public XML';
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
