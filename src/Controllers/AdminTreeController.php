<?php
namespace SurvLoop\Controllers;

use DB;
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

use SurvLoop\Controllers\SurvLoopTreeAdmin;
use SurvLoop\Controllers\SurvLoopTreeXML;

class AdminTreeController extends AdminController
{
    
    protected function initExtra(Request $request)
    {
        $this->v["allowEdits"] = $this->v["user"]->hasRole('administrator|brancher');
        $this->v["adminOverOpts"] = (($this->REQ->session()->has('adminOverOpts')) 
            ? $this->REQ->session()->get('adminOverOpts') : '');
        if (trim($this->v["currPage"]) == '') $this->v["currPage"] = '/dashboard/tree';
        
        $this->v["treeClassAdmin"] = new SurvLoopTreeAdmin($this->REQ);
        $this->v["treeClassAdmin"]->loadTree($GLOBALS["DB"]->treeID, $this->REQ);
        $this->initExtraCust();
        return true;
    }
    
    protected function initExtraCust() { return true; }
    
    public function index(Request $request)
    {
        $this->admControlInit($request, '/dashboard/tree/map?all=1');
        if (!$this->checkCache()) {
            $this->v["printTree"] = $this->v["treeClassAdmin"]->adminPrintFullTree($request);
            $this->v["content"] = view('vendor.survloop.admin.tree.tree', $this->v)->render();
            $this->saveCache();
        }
        $treeAbout = view('vendor.survloop.admin.tree.tree-about', [ "showAbout" => false ])->render();
        $this->v["content"] = $treeAbout . $this->v["content"];
        return view('vendor.survloop.admin.admin', $this->v);
    }
    
    
    public function data(Request $request)
    {
        $this->admControlInit($request, '/dashboard/tree/data');
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
                $newHelp = new SLDataHelpers;
                $newHelp->DataHelpTree        = $this->treeID;
                $newHelp->DataHelpParentTable = $splits[0];
                $newHelp->DataHelpTable       = $splits[2];
                $newHelp->DataHelpKeyField    = $splits[3];
                $newHelp->DataHelpValueField  = $valFld;
                $newHelp->save();
            } elseif ($request->has('delLinkage')) {
                $found = SLDataLinks::where('DataLinkTree', $this->treeID)
                    ->where('DataLinkTable', $request->input('delLinkage'))
                    ->first();
                if ($found && isset($found->DataLinkTree)) {
                    $found->delete();
                    unset($GLOBALS["DB"]->dataLinksOn[$found->DataLinkTable]);
                }
            } elseif ($request->has('newLinkage')) {
                $newLink = new SLDataLinks;
                $newLink->DataLinkTree = $this->treeID;
                $newLink->DataLinkTable = $request->input('newLinkage');
                $newLink->save();
                $GLOBALS["DB"]->dataLinksOn[$request->input('newLinkage')] 
                    = $GLOBALS["DB"]->getLinkTblMap($request->input('newLinkage'));
            }
        }
        
        if (!$this->checkCache() || $request->has('dataStruct')) {
            if (sizeof($GLOBALS["DB"]->dataLoops) > 0) {
                foreach ($GLOBALS["DB"]->dataLoops as $l => $loop) {
                    if (isset($GLOBALS["DB"]->dataLoops[$l]->DataLoopTree)) {
                        $GLOBALS["DB"]->dataLoops[$l]->loadConds();
                    }
                }
            }
            //echo '<pre>'; print_r($GLOBALS["DB"]->dataLoops); echo '</pre>';
            $this->v["content"] = view('vendor.survloop.admin.tree.tree-data', $this->v)->render();
            $this->saveCache();
        }
        return view('vendor.survloop.admin.admin', $this->v);
    }
    
    public function nodeEdit(Request $request, $nID) 
    {
        $this->admControlInit($request, '/dashboard/tree/map?all=1');
        $this->v["content"] = $this->v["treeClassAdmin"]->adminNodeEdit($nID, $request, '/dashboard/tree/map?all=1');
        return view('vendor.survloop.admin.admin', $this->v);
    }
    
    public function treeStats(Request $request) 
    {
        $this->admControlInit($request, '/dashboard/tree/stats?all=1');
        if (!$this->checkCache()) {
            $this->v["printTree"] = $this->v["treeClassAdmin"]->adminPrintFullTreeStats($request);
            $this->v["content"] = view('vendor.survloop.admin.tree.treeStats', $this->v)->render();
            $this->saveCache();
        }
        return view('vendor.survloop.admin.admin', $this->v);
    }

    public function treeSessions(Request $request) 
    {
        $this->admControlInit($request, '/dashboard/tree');
        if (!$this->checkCache()) {
            $this->v["content"] = view('vendor.survloop.admin.tree.treeSessions', $this->v)->render();
            $this->saveCache();
        }
        return view('vendor.survloop.admin.admin', $this->v);
    }

    public function workflows(Request $request) 
    {
        $this->admControlInit($request, '/dashboard/tree/workflows');
        return view('vendor.survloop.admin.tree.workflows', $this->v);
    }
    
    public function conditions(Request $request) 
    {
        $this->admControlInit($request, '/dashboard/tree/conds');
        
        if ($request->has('addNewCond')) $GLOBALS["DB"]->saveEditCondition($request);
        
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
    
    
    
    
    public function xmlmap(Request $request)
    {
        $this->admControlInit($request, '/dashboard/tree/xmlmap');
        $xmlmap = new SurvLoopTreeXML;
        $xmlmap->loadTree($GLOBALS["DB"]->getTreeXML(), $request);
        $this->v["adminPrintFullTree"] = $xmlmap->adminPrintFullTree($request);
        return view('vendor.survloop.admin.tree.xmlmap', $this->v);
    }
    
    public function xmlNodeEdit(Request $request, $nID = -3)
    {
        $this->admControlInit($request, '/dashboard/tree/xmlmap');
        $xmlmap = new SurvLoopTreeXML;
        $xmlmap->loadTree($GLOBALS["DB"]->getTreeXML(), $request, true);
        $this->v["content"] = $xmlmap->adminNodeEditXML($request, $nID);
        return view('vendor.survloop.admin.admin', $this->v);
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
        $GLOBALS["DB"] = new DatabaseLookups($db->dbID);
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
                $db->DbID     = 1;
            }
            $db = $this->freshDBstore($request, $db);
            
            $this->logPageVisit('/fresh/database', $db->DbID.';0');
            
            // Initialize system-wide settings
            $this->updateSysSet('cust-abbr', trim($request->DbPrefix));
            $this->updateSysSet('site-name', trim($request->DbName));
            $this->updateSysSet('meta-desc', trim($request->DbName));
            $this->updateSysSet('meta-title', trim($request->DbName) 
                . ' - ' . trim($request->DbDesc));
            
            $this->genDbClasses($request->DbPrefix);
            
            return redirect('/dashboard/tree/new');
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
            
            return redirect('/dashboard/tree/new');
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
        file_put_contents($fileName, $file);
        
        $file = "<"."?"."php\n" 
            . view('vendor.survloop.admin.fresh-install-class-report', [
                "abbr" => trim($dbPrefix)
            ])->render();
        file_put_contents(str_replace('.php', 'Report.php', $fileName), $file);
        
        $file = "<"."?"."php\n" 
            . view('vendor.survloop.admin.fresh-install-class-admin', [
                "abbr" => trim($dbPrefix)
            ])->render();
        file_put_contents(str_replace('.php', 'Admin.php', $fileName), $file);
        return true;
    }
    
    public function freshUXstore(Request $request, $tree, $currPage = '')
    {
        $tableName = trim($request->TreeTable);
        $coreTbl = SLTables::where('TblDatabase', $GLOBALS["DB"]->dbID)
            ->where('TblEng', $tableName)
            ->first();
        if (!$coreTbl || sizeof($coreTbl) == 0) {
            $coreTbl = new SLTables;
            $coreTbl->TblDatabase = $GLOBALS["DB"]->dbID;
            $coreTbl->TblEng      = $tableName;
            $coreTbl->TblName     = $this->eng2data($tableName);
            $coreTbl->TblAbbr     = $this->eng2abbr($tableName);
            $coreTbl->TblDesc     = trim($request->TreeDesc);
            $coreTbl->save();
        }

        $userTbl = SLTables::where('TblDatabase', $GLOBALS["DB"]->dbID)
            ->where('TblEng', 'Users')
            ->first();
        if (!$userTbl || sizeof($userTbl) == 0) {
            $userTbl = new SLTables;
            $userTbl->TblDatabase = $GLOBALS["DB"]->dbID;
            $userTbl->TblEng      = 'Users';
            $userTbl->TblName     = 'users';
            $userTbl->TblAbbr     = '';
            $userTbl->TblDesc     = 'This represents the Laravel Users table, but will not '
                . 'actually be implemented by SurvLoop as part of the database installation.';
            $userTbl->save();
        }
        
        $tree->TreeName         = trim($request->TreeName);
        $tree->TreeDesc         = trim($request->TreeDesc);
        $tree = $this->initTree($tree, $coreTbl, $userTbl, 'Primary Public');
        
        $treeXML = SLTree::find(2);
        if (!$treeXML || sizeof($treeXML) == 0) {
            $treeXML = new SLTree;
            $treeXML->TreeID = 2;
        }
        $treeXML->TreeName             = trim($request->TreeName);
        $this->initTreeXML($treeXML, $coreTbl, 'Primary Public XML');
        
        $this->installNewTable($coreTbl);
        
        $GLOBALS["DB"] = new DatabaseLookups($GLOBALS["DB"]->dbID, $tree->TreeID);
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
            return redirect('/dashboard/tree/map?all=1');
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
            return redirect('/dashboard/tree/map?all=1');
        }
        return view('vendor.survloop.admin.fresh-install-setup-ux', $this->v);
    }
    
    protected function initTree($tree, $coreTbl, $userTbl, $type = 'Public')
    {
        $tree->TreeUser            = $this->v["user"]->id;
        $tree->TreeDatabase        = $GLOBALS["DB"]->dbID;
        $tree->TreeCoreTable       = $coreTbl->TblID;
        $tree->TreeType            = $type;
        $tree->save();
        
        $this->logPageVisit('/fresh/database', $GLOBALS["DB"]->dbID.';'.$tree->TreeID);
        
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
    
    protected function initTreeXML($treeXML, $coreTbl, $type = 'Public XML')
    {
        $treeXML->TreeUser         = $this->v["user"]->id;
        $treeXML->TreeDatabase     = $GLOBALS["DB"]->dbID;
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
    
    protected function initCoreTable($coreTbl, $userTbl)
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
                "FldEng"  => 'A/B Testing Version', 
                "FldName" => 'VersionAB', 
                "FldDesc" => 'Stores a complex string reflecting all A/B Testing '
                    . 'variations in effect at the time of this User\'s Experience of this Node.' 
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
        $this->installNewModel($coreTbl);
        return true;
    }
    
    protected function installNewModel($tbl)
    {
        if ($tbl && sizeof($tbl) > 0 && $tbl->TblName != 'Users') {
            $tblClean = str_replace('_', '', $GLOBALS["DB"]->dbRow->DbPrefix . $tbl->TblName);
            $fields = "";
            if ($GLOBALS["DB"]->isCoreTbl($tbl->TblID)) {
                $fields = "'" . $tbl->TblAbbr . "SubmissionProgress', '" . $tbl->TblAbbr . "VersionAB'";
                
            }
            $model = view('vendor.survloop.admin.db.export-laravel-gen-model' , [
                "tblClean"  => $tblClean,
                "tblName"   => $GLOBALS["DB"]->dbRow->DbPrefix . $tbl->TblName,
                "tbl"       => $tbl,
                "modelFile" => $fields
            ]);
            $this->chkModelsFolder();
            $modelFilename = '../app/Models/' . $GLOBALS["DB"]->sysOpts["cust-abbr"] . '/' . $tblClean . '.php';
            file_put_contents($modelFilename, $model);
        }
        return true;
    }
    
    protected function installNewTable($tbl)
    {
        $tblQry = $this->exportMysqlTblCoreStart($tbl) 
            . "  `" . $tbl->TblAbbr . "UserID` bigint(20) unsigned NULL, \n"
            . "  `" . $tbl->TblAbbr . "SubmissionProgress` int(11) NULL , \n"
            . "  `" . $tbl->TblAbbr . "ABtests` varchar(255) NULL , \n"
            . "  `" . $tbl->TblAbbr . "UniqueStr` varchar(50) NULL , \n"
            . $this->exportMysqlTblCoreFinish($tbl);
        return DB::statement($tblQry);
    }
    
    
}
