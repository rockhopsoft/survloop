<?php
/**
  * TreeSurv is a mid-level class using a standard branching tree, mostly for 
  * SurvLoop's surveys and pages.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\User;
use App\Models\SLDatabases;
use App\Models\SLDefinitions;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLNodeSaves;
use App\Models\SLNodeSavesPage;
use App\Models\SLNodeResponses;
use App\Models\SLFields;
use App\Models\SLSess;
use App\Models\SLSessLoops;
use App\Models\SLSessEmojis;
use App\Models\SLSearchRecDump;
use App\Models\SLContact;
use App\Models\SLConditions;
use App\Models\SLConditionsArticles;
use App\Models\SLUsersActivity;
use SurvLoop\Controllers\TreeNodeSurv;
use SurvLoop\Controllers\SurvData;
use SurvLoop\Controllers\TreeSurvAPI;
use SurvLoop\Controllers\Searcher;
use SurvLoop\Controllers\Globals;
use SurvLoop\Controllers\TreeCore;

class TreeSurvLoad extends TreeCore
{
    public $treeVersion           = 'v0.1';
    public $abTest                = 'A';
    
    public $majorSections         = [];
    public $minorSections         = [];
    public $currMajorSection      = 0;
    public $currMinorSection      = 0;
    
    protected $pageJSvalid        = '';
    
    protected $sessDataChangeLog  = [];
    protected $sessNodesDone      = [];
    protected $sessMajorsTouched  = [];
    protected $sessMinorsTouched  = [];
    public $navBottom             = '';
    
    protected $REQstep            = '';
    protected $hasREQ             = false;
    
    public $nodeTreeProgressBar   = '';
    protected $checkboxNodes      = [];
    protected $tagsNodes          = [];
    
    protected $pageCnt            = 0;
    protected $loopCnt            = 0;
    protected $loadingError       = '';
    protected $urlSlug            = '';
    
    protected $isPage             = false;
    protected $isReport           = false;
    protected $isBigSurvLoop      = ['', '', '']; // table name, and sort field, if this is tree one big loop
    
    public $xmlMapTree            = false;
    
    public $emojiTagUsrs          = [];
    
    // kidMaps[nodeID][kidNodeID][] = [ responseInd, responseValue ]
    public $kidMaps               = [];
    protected $newLoopItemID      = -3;
    
    protected function loadNode($nodeRow = [])
    {
        if ($nodeRow && isset($nodeRow->NodeID) && $nodeRow->NodeID > 0) {
            return new TreeNodeSurv($nodeRow->NodeID, $nodeRow);
        }
        $newNode = new TreeNodeSurv();
        $newNode->nodeRow->NodeTree = $this->treeID;
        return $newNode;
    }
    
    protected function loadLookups()
    {
        $this->debugOn = (!isset($_SERVER["REMOTE_ADDR"]) 
            || in_array($_SERVER["REMOTE_ADDR"], ['192.168.10.1', '173.79.192.119']));
        return true;
    }
    
    public function __construct(Request $request = null, $sessIn = -3, $dbID = -3, $treeID = -3, $skipSessLoad = false)
    {
        return $this->constructor($request, $sessIn, $dbID, $treeID, $skipSessLoad);
    }

    public function constructor(Request $request, $sessIn = -3, $dbID = -3, $treeID = -3, $skipSessLoad = false)
    {
        $this->dbID = (($dbID > 0) ? $dbID : ((isset($GLOBALS["SL"])) ? $GLOBALS["SL"]->dbID : 1));
        $this->treeID = (($treeID > 0) ? $treeID : ((isset($GLOBALS["SL"])) ? $GLOBALS["SL"]->treeID : 1));
        $this->searcher = new Searcher;
        $this->survLoopInit($request);
        $this->coreIDoverride = -3;
        if ($sessIn > 0) $this->coreIDoverride = $sessIn;
        if (isset($GLOBALS["SL"]) && $GLOBALS["SL"]->REQ->has('step') && $GLOBALS["SL"]->REQ->has('tree') 
            && intVal($GLOBALS["SL"]->REQ->get('tree')) > 0) {
            $this->hasREQ = true;
            $this->REQstep = $GLOBALS["SL"]->REQ->get('step');
        }
        $this->loadLookups();
        $this->isPage = ($GLOBALS["SL"]->treeRow->TreeType == 'Page');
        $this->sessData = new SurvData;
        return true;
    }
    
    public function loadPageVariation(Request $request, $dbID = 1, $treeID = 1, $currPage = '/')
    {
        $GLOBALS["SL"] = new Globals($request, $dbID, $treeID, $treeID);
        $this->constructor($request, -3, $dbID, $treeID);
        $this->survInitRun = false;
        $this->survLoopInit($request, $currPage);
        return true;
    }
    
    public function loadTreeFromCache()
    {
        $cacheFile = '/cache/tree-load-' . $this->treeID . '.php';
        if (!$GLOBALS["SL"]->REQ->has('refresh') && file_exists($cacheFile)) {
            $content = Storage::get($cacheFile);
            eval($content);
        } else {
            $cache = '// Auto-generated loading cache from /SurvLoop/Controllers/TreeSurv.php' . "\n\n";
            $this->pageCnt = 0;
            $this->kidMaps = $nodeIDs = [];
            if (isset($GLOBALS["SL"]->treeRow->TreeOpts)) {
                $nodes = SLNode::where('NodeTree', $this->treeID)
                    ->select('NodeID', 'NodeParentID', 'NodeParentOrder', 'NodeType', 'NodeOpts', 
                        'NodeDataBranch', 'NodeDataStore', 'NodeResponseSet', 'NodeDefault')
                    ->get();
                foreach ($nodes as $row) {
                    $nodeIDs[] = $row->NodeID;
                    if ($row->NodeParentID <= 0) {
                        $rootID = $row->NodeID;
                        $cache .= '$'.'this->rootID = ' . $row->NodeID . ';' . "\n";
                    }
                    if (in_array($row->NodeType, ['Page', 'Loop Root'])) $this->pageCnt++;
                    if ($GLOBALS["SL"]->treeRow->TreeOpts%5 == 0 && $row->NodeParentID == $rootID 
                        && $row->NodeType == 'Loop Root' && trim($row->NodeDataBranch) != ''
                        && isset($GLOBALS["SL"]->dataLoops[$row->NodeDataBranch])
                        && isset($GLOBALS["SL"]->dataLoops[$row->NodeDataBranch]->DataLoopTable)) {
                        $tbl = $GLOBALS["SL"]->dataLoops[$row->NodeDataBranch]->DataLoopTable;
                        $cache .= '$'.'this->isBigSurvLoop = [\'' . $tbl . '\', \'';
                        if (trim($row->NodeDefault) != '') {
                            $cache .= $row->NodeDefault . '\', \'asc\'];' . "\n";
                        } else {
                            $cache .= $GLOBALS["SL"]->tblAbbr[$tbl] . 'ID\', \'desc\'];' . "\n";
                        }
                    }
                    if ($row->NodeType == 'Checkbox' 
                        || (in_array($row->NodeType, ['Drop Down', 'U.S. States']) && $row->NodeOpts%53 == 0)) {
                        $cache .= '$'.'this->checkboxNodes[] = ' . $row->NodeID . ';' . "\n";
                    } elseif (in_array($row->NodeType, ['Data Print', 'Data Print Row']) && isset($row->NodeDataStore)
                        && trim($row->NodeDataStore) != '') {
                        list($tbl, $fld) = $GLOBALS["SL"]->splitTblFld($row->NodeDataStore);
                        if ($GLOBALS["SL"]->origFldCheckbox($tbl, $fld) > 0) {
                            $cache .= '$'.'this->checkboxNodes[] = ' . $row->NodeID . ';' . "\n";
                        }
                    }
                    $includeNode = true;
                    if ($row->NodeType == 'Data Manip: Update') {
                        // add unless this node is data manip update which is under a new record manip
                        $includeNode = (!isset($this->allNodes[$row->NodeParentID]) 
                            || $this->allNodes[$row->NodeParentID]->nodeType != 'Data Manip: New');
                    }
                    if ($includeNode) {
                        $cacheNode = '$'.'this->allNodes[' . $row->NodeID . '] = '
                            . 'new SurvLoop\\Controllers\\TreeNodeSurv(' 
                            . $row->NodeID . ', [], ['
                                . '"pID" => '     . intVal($row->NodeParentID)    . ', '
                                . '"pOrd" => '    . intVal($row->NodeParentOrder) . ', '
                                . '"opts" => '    . intVal($row->NodeOpts)        . ', '
                                . '"type" => "'   . $row->NodeType                . '", '
                                . '"branch" => "' . $row->NodeDataBranch          . '", '
                                . '"store" => "'  . $row->NodeDataStore           . '", '
                                . '"set" => "'    . $row->NodeResponseSet         . '", '
                                . '"def" => "'    . str_replace('"', '\\"', $row->NodeDefault) . '"'
                            . ']);' . "\n";
                        eval($cacheNode);
                        $cache .= $cacheNode;
                    }
                }
                $responses = SLNodeResponses::whereIn('NodeResNode', $nodeIDs)
                    ->where('NodeResShowKids', '>', 0)
                    ->get();
                if ($responses->isNotEmpty()) {
                    foreach ($responses as $j => $res) {
                        if (!isset($this->kidMaps[$res->NodeResNode])) {
                            $this->kidMaps[$res->NodeResNode] = [];
                            $cache .= '$'.'this->kidMaps[' . $res->NodeResNode . '] = [];' . "\n";
                        }
                        if (!isset($this->kidMaps[$res->NodeResNode][intVal($res->NodeResShowKids)])) {
                            $this->kidMaps[$res->NodeResNode][intVal($res->NodeResShowKids)] = [];
                            $cache .= '$'.'this->kidMaps[' . $res->NodeResNode . '][' . $res->NodeResShowKids . '] = [];' 
                                . "\n";
                        }
                        $cache .= '$'.'this->kidMaps[' . $res->NodeResNode . '][' . $res->NodeResShowKids . '][] = [ ' 
                            . $res->NodeResOrd . ', "' . $res->NodeResValue . '" ];' . "\n";
                    }
                }
                $cache .= '$'.'this->treeSize = sizeof($'.'this->allNodes);' . "\n"
                    . '$'.'this->pageCnt = ' . $this->pageCnt . ';' . "\n";
            }
            $this->allNodes = [];
            eval($cache);
            $cache2 = $this->loadNodeTiersCache();
            eval($cache2);
            $cache3 = $this->loadNodePageTiersCache();
            eval($cache3);
            
            if (file_exists($cacheFile)) Storage::delete($cacheFile);
            Storage::put($cacheFile, $cache . $cache2 . $cache3);
        }
        return true;
    }
    
    public function loadNodePageTiersCache()
    {
        $cache = '';
        if ($this->rootID > 0 && sizeof($this->allNodes) > 0) {
            foreach ($this->allNodes as $nID => $node) {
                if ($this->hasParentPage($nID)) {
                    $cache .= '$'.'this->allNodes[' . $node->nodeID . ']->hasPageParent = true;' . "\n";
                }
            }
        }
        return $cache;
    }
    
    public function loadTree($treeIn = -3, Request $request = NULL, $loadFull = false)
    {
        $this->loadTreeStart($treeIn, $request);
        $this->loadTreeFromCache();
        $this->loadAllSessData();
        return true;
    }
    
    public function loadAllSessData($coreTbl = '', $coreID = -3)
    {
        if (trim($coreTbl) == '') $coreTbl = $GLOBALS["SL"]->coreTbl;
        if ($coreID <= 0) $coreID = $this->coreID; 
        $this->loadSessInfo($coreTbl);
        $this->loadSessionData($coreTbl, $coreID);
        $this->loadSessionDataSaves();
        $this->runLoopConditions();
        return true;
    }
    
    protected function loadExtra() { return true; }
    
    public function currInReport()
    {
        if (trim($GLOBALS["SL"]->coreTbl) != '' && $GLOBALS["SL"]->coreTblAbbr() != '') {
            $isLastPage = ($GLOBALS["SL"]->treeRow->TreeLastPage
                == $GLOBALS["SL"]->coreTblAbbr() . 'SubmissionProgress');
            if (isset($this->sessInfo->SessCurrNode) 
                && $this->sessData->currSessData($this->sessInfo->SessCurrNode, $GLOBALS["SL"]->coreTbl, $isLastPage)) {
                session()->forget('sessID' . $GLOBALS["SL"]->sessTree);
                session()->forget('coreID' . $GLOBALS["SL"]->sessTree);
                return false;
            }
        }
        return true;
    }
    
    // returns array of DataBranch tables, [0] being the closest related to the current node's data
    protected function loadNodeDataBranch($nID = -3)
    {
        $this->sessData->setCoreID($GLOBALS["SL"]->coreTbl, $this->coreID); // not sure why this is needed
        $nIDtmp = $nID;
        $parents = [$nID];
        while ($this->hasNode($nIDtmp)) {
            $nIDtmp = $this->allNodes[$nIDtmp]->getParent();
            if (intVal($nIDtmp) > 0) $parents[] = $nIDtmp;
        }
        $this->sessData->dataBranches = [ [
            "branch" => $GLOBALS["SL"]->coreTbl, 
            "loop"   => '', 
            "itemID" => $this->coreID 
        ] ];
        if (sizeof($parents) > 1) {
            for ($i = (sizeof($parents)-2); $i >= 0; $i--) {
                if ($this->allNodes[$parents[$i]]->nodeType == 'Data Manip: New') {
                    $this->loadManipBranch($parents[$i], true);
                } elseif (trim($this->allNodes[$parents[$i]]->dataBranch) != '') {
                    $nBranch = $this->allNodes[$parents[$i]]->dataBranch;
                    $addBranch = $addLoop = '';
                    $itemID = -3;
                    if ($this->allNodes[$parents[$i]]->isLoopRoot()) {
                        $addBranch = $GLOBALS["SL"]->dataLoops[$nBranch]->DataLoopTable;
                        $addLoop = $nBranch;
                        $itemID = $GLOBALS["SL"]->getSessLoopID($nBranch);
                    } else {
                        $addBranch = $nBranch;
                        list($itemInd, $itemID) = $this->sessData->currSessDataPos($nBranch, 
                            $this->allNodes[$parents[$i]]->isDataManip());
                        if ($itemID <= 0) {
                            if (isset($GLOBALS["SL"]->tblAbbr[$nBranch])) {
                                $lastInd = sizeof($this->sessData->dataBranches)-1;
                                $parBranch = $this->sessData->dataBranches[$lastInd]["branch"];
                                $lnkFld = $GLOBALS["SL"]->getForeignLnk($GLOBALS["SL"]->tblI[$parBranch],
                                    $GLOBALS["SL"]->tblI[$nBranch]);
                                if ($lnkFld != '') {
                                    $lnkFld = $GLOBALS["SL"]->tblAbbr[$parBranch] . $lnkFld;
                                    $row = $this->sessData->getRowById($parBranch, 
                                        $this->sessData->dataBranches[$lastInd]["itemID"]);
                                    if ($row && isset($row->{ $lnkFld })) {
                                        $itemID = $row->{ $lnkFld };
                                    }
                                }
                                if ($lnkFld == '' || $itemID <= 0) {
                                    $lnkFld = $GLOBALS["SL"]->getForeignLnk($GLOBALS["SL"]->tblI[$nBranch], 
                                        $GLOBALS["SL"]->tblI[$parBranch]);
                                    if ($lnkFld != '') {
                                        $lnkFld = $GLOBALS["SL"]->tblAbbr[$nBranch] . $lnkFld;
                                        $row = $this->sessData->getRowById($nBranch, 
                                            $this->sessData->dataBranches[$lastInd]["itemID"]);
                                        if ($row && isset($row->{ $lnkFld })) {
                                            $itemID = $row->{ $lnkFld };
                                        }
                                    }
                                }
                                if ($lnkFld == '' || $itemID <= 0) {
                                    $lnkFld = $GLOBALS["SL"]->getForeignLnk($GLOBALS["SL"]->tblI[$nBranch], 
                                        $GLOBALS["SL"]->tblI[$parBranch]);
                                    if ($parBranch == 'users' && $lnkFld == 'UserID' 
                                        && isset($this->sessData->dataSets[$nBranch]) 
                                        && sizeof($this->sessData->dataSets[$nBranch]) > 0) {
                                        $lnkFld = $GLOBALS["SL"]->tblAbbr[$nBranch] . $lnkFld;
                                        foreach ($this->sessData->dataSets[$nBranch] as $row) {
                                            if (isset($row->{ $lnkFld }) && $row->{ $lnkFld } 
                                                == $this->sessData->dataBranches[$lastInd]["itemID"] ) {
                                                $itemID = $row->getKey();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $foundBranch = false; // $this->sessData->chkDataBranch($addBranch, $dataBranch);
                    if (!$foundBranch) {
                        $this->sessData->dataBranches[] = [
                            "branch" => $addBranch,
                            "loop"   => $addLoop,
                            "itemID" => $itemID
                        ];
                    }
                }
            }
        }
        return $this->sessData->dataBranches;
    }
    
    /******************************************************************************************************
    
    Next are functions related to data management, storing and retrieving responses from the tree's forms. 
    
    ******************************************************************************************************/
    
    public function chkPublicCoreID($coreTbl, $coreID = -3)
    {
        if ($coreID > 0) {
            if ($GLOBALS["SL"]->tblHasPublicID($coreTbl)) {
                $this->corePublicID = $coreID;
                $this->coreID = $GLOBALS["SL"]->chkInPublicID($coreID, $coreTbl);
            } else {
                $this->coreID = $this->corePublicID = $coreID;
            }
        }
        return $this->coreID;
    }
    
    protected function setPublicID($coreTbl = '')
    {
        if (trim($coreTbl) == '') $coreTbl = $GLOBALS["SL"]->coreTbl;
        if ($GLOBALS["SL"]->tblHasPublicID($coreTbl) && isset($this->sessData->dataSets[$coreTbl])) {
            $fld = $GLOBALS["SL"]->tblAbbr[$coreTbl] . 'PublicID';
            if (isset($this->sessData->dataSets[$coreTbl][0])
                && isset($this->sessData->dataSets[$coreTbl][0]->{ $fld })) {
                $this->corePublicID = $this->sessData->dataSets[$coreTbl][0]->{ $fld };
                return true;
            }
        }
        return false;
    }
    
    public function loadSessionData($coreTbl, $coreID = -3, $skipPublic = false)
    {
        if ($coreID > 0) {
            if (!$skipPublic) $this->chkPublicCoreID($coreTbl, $coreID);
            else $this->coreID = $this->corePublicID = $coreID;
        }
        $this->sessData->loadCore($coreTbl, $this->coreID, $this->checkboxNodes, $this->isBigSurvLoop);
        $this->loadExtra();
        $this->setPublicID();
        $this->v["isOwner"] = $this->isCoreOwner($this->coreID);
        return true;
    }
    
    protected function loadSessionDataSaves()
    {
        $this->sessMajorsTouched = $this->sessMinorsTouched = [];
        for ($s = 0; $s < sizeof($this->majorSections); $s++) $this->sessMinorsTouched[$s] = [];
        $nodeSave = DB::table('SL_NodeSaves')
            ->join('SL_Sess', 'SL_NodeSaves.NodeSaveSession', '=', 'SL_Sess.SessID')
            ->where('SL_Sess.SessTree', '=', $this->treeID)
            ->where('SL_Sess.SessCoreID', '=', $this->coreID)
            ->distinct()
            ->get([ 'SL_NodeSaves.NodeSaveNode' ]);
        if ($nodeSave->isNotEmpty()) {
            foreach ($nodeSave as $save) {
                if (!$this->loadSessionDataSavesExceptions($save->NodeSaveNode)) {
                    $majorSection = $this->getCurrMajorSection($save->NodeSaveNode);
                    if (!in_array($majorSection, $this->sessMajorsTouched)) {
                        $this->sessMajorsTouched[] = $majorSection;
                    }
                    $minorSection = $this->getCurrMinorSection($save->NodeSaveNode);
                    if (isset($this->sessMinorsTouched[$majorSection]) 
                        && !in_array($minorSection, $this->sessMinorsTouched[$majorSection])) {
                        $this->sessMinorsTouched[$majorSection][] = $minorSection;
                    }
                }
            }
        }
        return true;
    }
    
    protected function loadSessionDataSavesExceptions($nID)
    {
        return false;
    }
    
    protected function isAnonyLogin()
    {
        return false;
    }
    
    protected function fldToLog($fldName)
    {
        if (trim($fldName) != '' && $GLOBALS["SL"]->REQ->has($fldName)) {
            if (is_array($GLOBALS["SL"]->REQ->input($fldName)) && is_array($GLOBALS["SL"]->REQ->input($fldName))
                && sizeof($GLOBALS["SL"]->REQ->input($fldName)) > 0) {
                return implode(';;', $GLOBALS["SL"]->REQ->input($fldName));
            } else {
                return $GLOBALS["SL"]->REQ->input($fldName);
            }
        }
        return '';
    }
    
    protected function checkData()
    {
        return true;
    }
    
    protected function printNodeSessDataOverride($nID = -3, $tmpSubTier = [], $nIDtxt = '', $currNodeSessionData = '')
    {
        return [];
    }
    
    protected function customNodePrint($nID = -3, $tmpSubTier = [], $nIDtxt = '', $nSffx = '', $currVisib = 1)
    {
        return '';
    }
    
    protected function printNodePublic($nID = -3, $tmpSubTier = [])
    {
        return 'Node #' . $nID . '<br />';
    }
    
    protected function postNodePublicCustom($nID = -3, $tmpSubTier = [])
    {
        return false;
    }
    
    protected function postNodePublic($nID = -3, $tmpSubTier = [])
    {
        return true;
    }
    
    protected function rawOrderPercentTweak($nID, $rawPerc, $found = -3)
    {
        return $rawPerc;
    }
    
    protected function loadProgBarTweak()
    {
        return true;
    }
    
    protected function tweakProgBarJS()
    {
        return '';
    }
    
    protected function loadProgBar()
    {
        $rawPerc = $this->rawOrderPercent($this->currNode());
        if (intVal($rawPerc) < 0) $rawPerc = 0;
        if (isset($this->allNodes[$this->currNode()]) && $this->allNodes[$this->currNode()]->nodeType == 'Page' 
            && $this->allNodes[$this->currNode()]->nodeOpts%29 == 0) {
            $rawPerc = 100;
        }
        $this->currMajorSection = $this->getCurrMajorSection($this->currNode());
        if (!isset($this->minorSections[$this->currMajorSection]) 
            || empty($this->minorSections[$this->currMajorSection])) {
            $this->currMinorSection = 0;
        } else {
            $this->currMinorSection = $this->getCurrMinorSection($this->currNode(), $this->currMajorSection);
        }
        $this->loadProgBarTweak();
        if (sizeof($this->majorSections) > 0) {
            foreach ($this->majorSections as $maj => $majSect) {
                if (sizeof($this->minorSections[$maj]) > 0) {
                    foreach ($this->minorSections[$maj] as $min => $minSect) {
                        if (isset($minSect[0]) && isset($this->allNodes[$minSect[0]])) {
                            $this->allNodes[$minSect[0]]->fillNodeRow();
                        }
                    }
                }
            }
        }
        $this->createProgBarJs();
        $GLOBALS["SL"]->pageSCRIPTS .= "\n" . '<script type="text/javascript" id="treeJS" src="' 
            . $GLOBALS["SL"]->sysOpts["app-url"] . $this->getProgBarJsFilename() . '"></script>' . "\n";
        $ret = '';
        $majTot = 0;
        foreach ($this->majorSections as $maj => $majSect) {
            if ($maj == $this->currMajorSection) {
                $GLOBALS['SL']->pageJAVA .= view('vendor.survloop.inc-progress-bar-js-tweak', [
                    "maj" => $maj, "status" => 'active' ])->render();
            } elseif (in_array($maj, $this->sessMajorsTouched)) {
                $GLOBALS['SL']->pageJAVA .= view('vendor.survloop.inc-progress-bar-js-tweak', [
                    "maj" => $maj, "status" => 'completed' ])->render();
            }
            if ($majSect[2] == 'disabled') {
                $GLOBALS['SL']->pageJAVA .= 'treeMajorSectsDisabled[0]=' . $maj . ';' . "\n";
            } else {
                $majTot++;
            }
            if (sizeof($this->minorSections[$maj]) > 0) {
                foreach ($this->minorSections[$maj] as $min => $minSect) {
                    if ($maj == $this->currMajorSection && $min == $this->currMinorSection) {
                        $GLOBALS['SL']->pageJAVA .= view('vendor.survloop.inc-progress-bar-js-tweak', [
                            "maj" => $maj, "min" => $min, "status" => 'active' ])->render();
                    } elseif (in_array($min, $this->sessMinorsTouched[$maj])) {
                        $GLOBALS['SL']->pageJAVA .= view('vendor.survloop.inc-progress-bar-js-tweak', [
                            "maj" => $maj, "min" => $min, "status" => 'completed' ])->render();
                    }
                }
            }
        }
        if ($GLOBALS["SL"]->treeRow->TreeOpts%61 == 0) { // survey progress line
            $GLOBALS['SL']->pageJAVA .= 'printHeadBar(' 
                . ((isset($this->allNodes[$this->currNode()]) && $this->allNodes[$this->currNode()]->nodeOpts%59 > 0) 
                    ? intVal($rawPerc) : -3) . ');' . "\n";
        }
        if (($GLOBALS["SL"]->treeRow->TreeOpts%37 == 0 || $GLOBALS["SL"]->treeRow->TreeOpts%59 == 0)
            && isset($this->majorSections[$this->currMajorSection][1]) > 0) {
            $GLOBALS["SL"]->pageAJAX .= '$(".snLabel").click(function() { '
                . '$("html, body").animate({ scrollTop: 0 }, "fast"); });' . "\n";
            $majorsOut = $minorsOut = [];
            foreach ($this->majorSections as $maj => $majSect) {
                if ($majSect[2] != 'disabled') {
                    $majorsOut[] = $this->majorSections[$maj];
                    $minorsOut[] = $this->minorSections[$maj];
                }
            }
            $ret .= view('vendor.survloop.inc-progress-bar', [
                "hasNavBot"         => ($GLOBALS["SL"]->treeRow->TreeOpts%59 == 0),
                "hasNavTop"         => ($GLOBALS["SL"]->treeRow->TreeOpts%37 == 0),
                "allNodes"          => $this->allNodes, 
                "majorSections"     => $majorsOut, 
                "minorSections"     => $minorsOut, 
                "sessMajorsTouched" => $this->sessMajorsTouched, 
                "sessMinorsTouched" => $this->sessMinorsTouched, 
                "currMajorSection"  => $this->currMajorSection, 
                "currMinorSection"  => $this->currMinorSection, 
                "majTot"            => $majTot,
                "rawPerc"           => $rawPerc
            ])->render();
            $GLOBALS['SL']->pageJAVA .= 'document.getElementById("progWrap").style.display = "none";' . "\n";
        }
        $GLOBALS['SL']->pageJAVA .= $this->tweakProgBarJS();
        return $ret;
        //return false;
    }
    
    protected function createProgBarJs()
    {
        $jsFileName = '../storage/app/sys' . $this->getProgBarJsFilename();
        if (!file_exists($jsFileName) || $GLOBALS["SL"]->REQ->has('refresh')) {
            if (file_exists($jsFileName)) unlink($jsFileName);
            $jsOut = view('vendor.survloop.inc-tree-javascript', [
                    "allNodes"          => $this->allNodes, 
                    "majorSections"     => $this->majorSections, 
                    "minorSections"     => $this->minorSections
                ])->render();
            file_put_contents($jsFileName, $jsOut);
        }
        return true;
    }
    
    protected function getProgBarJsFilename()
    {
        return '/tree-' . $this->treeID . '.js';
    }
    
    protected function getCurrMajorSection($nID = -3)
    {
        if ($nID <= 0) $nID = $this->currNode();
        $currSection = 0;
        if (sizeof($this->majorSections) > 0) {
            foreach ($this->majorSections as $s => $sect) {
                if ($sect[0] > 0 && isset($this->allNodes[$sect[0]]) && $this->hasNode($nID)) {
                    if ($this->allNodes[$nID]->checkBranch($this->allNodes[$sect[0]]->nodeTierPath)) {
                        $currSection = $s;
                    }
                }
            }
        }
        return $currSection;
    }
    
    protected function getCurrMinorSection($nID = -3, $majorSectInd = -3)
    {
        if ($nID <= 0) $nID = $this->currNode();
        if ($majorSectInd <= 0) $majorSectInd = $this->getCurrMajorSection($nID);
        $overrideSection = $this->overrideMinorSection($nID, $majorSectInd);
        if ($overrideSection >= 0) return $overrideSection;
        $currSection = 0;
        if (sizeof($this->minorSections) > 0 && sizeof($this->minorSections[$majorSectInd]) > 0) {
            foreach ($this->minorSections[$majorSectInd] as $s => $sect) {
                if ($sect[0] > 0 && isset($this->allNodes[$sect[0]]) && $this->hasNode($nID)) {
                    if ($this->allNodes[$nID]->checkBranch($this->allNodes[$sect[0]]->nodeTierPath)) {
                        $currSection = $s;
                    }
                }
            }
        }
        return $currSection;
    }
    
    protected function overrideMinorSection($nID = -3, $majorSectInd = -3)
    {
        return -1;
    }
    
    protected function getBranchName($branchID = -3)
    {
        if ($branchID > 0 && sizeof($this->branches) > 0) {
            foreach ($this->branches as $b) {
                if ($b["id"] == $branchID) return $b["name"];
            }
        }
        return "";
    }
    
    protected function checkNodeConditions($nID)
    {
        if (!isset($this->allNodes[$nID])) return false;
        $this->allNodes[$nID]->fillNodeRow();
        return $this->parseConditions($this->allNodes[$nID]->conds, [], $nID);
    }
    
    protected function checkNodeConditionsCustom($nID, $condition = '')
    {
        return -1;
    }
    
    // Setting the second parameter to false alternatively returns an array of individual conditions
    public function parseConditions($conds = [], $recObj = [], $nID = -3)
    {
        $retTF = true;
        if (sizeof($conds) > 0) {
            foreach ($conds as $i => $cond) {
                if ($retTF) {
                    if ($cond && isset($cond->CondDatabase) && $cond->CondOperator == 'CUSTOM') {
                        if (!$this->parseCondPreInstalled($cond)) $retTF = false;
                    } elseif ($cond->CondOperator == 'URL-PARAM') {
                        if (trim($cond->CondOperDeet) == '') $retTF = false;
                        elseif (!$GLOBALS["SL"]->REQ->has($cond->CondOperDeet) 
                            || trim($GLOBALS["SL"]->REQ->get($cond->CondOperDeet)) 
                                != trim($cond->condFldResponses["vals"][0][1])) {
                            $retTF = false;
                        }
                    } elseif ($cond->CondOperator == 'COMPLEX') {
                        $cond->loadVals();
                        if (isset($cond->condVals) && sizeof($cond->condVals) > 0) {
                            foreach ($cond->condVals as $i => $val) {
                                if ($val > 0) {
                                    $subCond = SLConditions::find($val);
                                    if ($subCond && isset($subCond->CondOperator)) {
                                        if (!$this->sessData->parseCondition($subCond, $recObj, $nID)) {
                                            $retTF = false;
                                        }
                                    }
                                } else { // opposite
                                    $subCond = SLConditions::find(-1*$val);
                                    if ($subCond && isset($subCond->CondOperator)) {
                                        if ($this->sessData->parseCondition($subCond, $recObj, $nID)) {
                                            $retTF = false;
                                        }
                                    }
                                }
                            }
                        }
                    } elseif (!$this->sessData->parseCondition($cond, $recObj, $nID)) {
                        $retTF = false; 
                    }
                    $custom = $this->checkNodeConditionsCustom($nID, trim($cond->CondTag));
                    if ($custom == 0) $retTF = false;
                    elseif ($custom == 1) $retTF = true;
                    // This is where all the condition-inversion is applied
                    if ($nID > 0 && isset($GLOBALS["SL"]->nodeCondInvert[$nID]) 
                        && isset($GLOBALS["SL"]->nodeCondInvert[$nID][$cond->CondID])) {
                        $retTF = !$retTF;
                    }
                }
            }
        }
        return $retTF;
    }
    
    public function parseCondPreInstalled($cond = NULL)
    {
        $retTF = true;
        if ($cond && isset($cond->CondTag)) {
            if (trim($cond->CondTag) == '#NodeDisabled') {
                $retTF = false;
            } elseif (trim($cond->CondTag) == '#IsLoggedIn') {
                if ($this->v["uID"] <= 0) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsNotLoggedIn') {
                if ($this->v["uID"] > 0) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsAdmin') {
                if ($this->v["uID"] <= 0 || !$this->v["user"]->hasRole('administrator|staff|databaser')) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsNotAdmin') {
                if ($this->v["uID"] > 0 && $this->v["user"]->hasRole('administrator|staff|databaser')) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsOwner') {
                if ($this->v["uID"] <= 0 || !$this->v["isOwner"]) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsProfileOwner') {
                if ($this->v["uID"] <= 0 || !isset($this->v["profileUser"]) || !$this->v["profileUser"]
                    || !isset($this->v["profileUser"]->id) || $this->v["uID"] != $this->v["profileUser"]->id) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsPrintable') {
                if (!$GLOBALS["SL"]->REQ->has('print') && (!isset($GLOBALS["SL"]->x["pageView"]) 
                    || !in_array($GLOBALS["SL"]->x["pageView"], ['pdf', 'full-pdf']))) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsPrintInFrame') {
                if (!$GLOBALS["SL"]->REQ->has('ajax') && !$GLOBALS["SL"]->REQ->has('frame') 
                    && !$GLOBALS["SL"]->REQ->has('wdg')) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#TestLink') {
                if (!$GLOBALS["SL"]->REQ->has('test') && intVal($GLOBALS["SL"]->REQ->get('test')) < 1) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsDataPermPublic') {
                if ($GLOBALS["SL"]->x["dataPerms"] != 'public') {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsDataPermPrivate') {
                if ($GLOBALS["SL"]->x["dataPerms"] != 'private') {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsDataPermSensitive') {
                if ($GLOBALS["SL"]->x["dataPerms"] != 'sensitive') {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsDataPermInternal') {
                if ($GLOBALS["SL"]->x["dataPerms"] != 'internal') {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#HasTokenDialogue') {
                if (!$this->pageLoadHasToken()) $retTF = false;
            } elseif (trim($cond->CondTag) == '#EmailVerified') {
                if ($this->v["uID"] <= 0 || !$this->v["user"]->hasVerifiedEmail()) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#NextButton') {
                if (!isset($this->REQstep) || $this->REQstep != 'next') {
                    $retTF = false;
                }
            //} elseif (trim($cond->CondTag) == '#HasUploads') {
            }
        }
        return $retTF;
    }
    
    public function runLoopConditions()
    {
        $this->sessData->loopItemIDs = [];
        if (isset($GLOBALS["SL"]->dataLoops) && sizeof($GLOBALS["SL"]->dataLoops) > 0) {
            $GLOBALS["SL"]->loadLoopConds();
            foreach ($GLOBALS["SL"]->dataLoops as $loopName => $loop) {
                $this->sessData->loopItemIDs[$loop->DataLoopPlural] = $sortable = [];
                if (isset($this->sessData->dataSets[$loop->DataLoopTable]) 
                    && sizeof($this->sessData->dataSets[$loop->DataLoopTable]) > 0) {
                    foreach ($this->sessData->dataSets[$loop->DataLoopTable] as $recObj) {
                        if ($recObj && $this->parseConditions($loop->conds, $recObj)) {
                            $this->sessData->loopItemIDs[$loop->DataLoopPlural][] = $recObj->getKey();
                            if (trim($loop->DataLoopSortFld) != '') {
                                $sortable['' . $recObj->getKey() . ''] = $recObj->{ $loop->DataLoopSortFld };
                            }
                        }
                    }
                }
                if (trim($loop->DataLoopSortFld) != '' && sizeof($sortable) > 0) {
                    $this->sessData->loopItemIDs[$loop->DataLoopPlural] = [];
                    asort($sortable);
                    foreach ($sortable as $id => $ord) {
                        $this->sessData->loopItemIDs[$loop->DataLoopPlural][] = intVal($id);
                    }
                }
            }
        }
        return true;
    }
    
    // Setting the second parameter to false alternatively returns an array of individual conditions
    public function loadRelatedArticles()
    {
        $this->v["articles"] = $artCondIDs = [];
        $this->v["allUrls"] = [ "txt" => [], "vid" => [] ];
        $allArticles = SLConditionsArticles::get();
        if ($allArticles->isNotEmpty()) {
            foreach ($allArticles as $i => $a) $artCondIDs[] = $a->ArticleCondID;
            $allConds = SLConditions::whereIn('CondID', $artCondIDs)->get();
            if ($allConds->isNotEmpty()) {
                foreach ($allConds as $i => $c) {
                    if ($this->parseConditions([$c])) {
                        $artLnks = [];
                        foreach ($allArticles as $i => $a) {
                            if ($a->ArticleCondID == $c->CondID) {
                                $artLnks[] = [$a->ArticleTitle, $a->ArticleURL];
                                $set = ((strpos(strtolower($a->ArticleURL), 'youtube.com') !== false) ? 'vid' : 'txt');
                                $found = false;
                                if (sizeof($this->v["allUrls"][$set]) > 0) {
                                    foreach ($this->v["allUrls"][$set] as $url) {
                                        if ($url[1] == $a->ArticleURL) $found = true;
                                    }
                                }
                                if (!$found) $this->v["allUrls"][$set][] = [$a->ArticleTitle, $a->ArticleURL];
                            }
                        }
                        $this->v["articles"][] = [$c, $artLnks];
                    }
                }
            }
            return true;
        }
        return false;
    }
    
    public function addCondEditorAjax()
    {
        $GLOBALS["SL"]->pageAJAX .= view('vendor.survloop.admin.db.inc-addCondition-ajax', [])->render();
        return true;
    }
    
    public function treeSessionsWhereExtra() 
    {
        return "";
    }
    
    
    
    
    
    
    public function printAllNodesCore()
    {
        if (sizeof($this->allNodes) > 0) {
            $print = [];
            foreach ($this->allNodes as $n) $print[$n->nodeID] = $n->listCore();
            echo '<pre>';
            print_r($print);
            echo '</pre>';
        }
        return true;
    }
    
    public function sessDump($lastNode = -3)
    {
        //return '<!-- ipip: ' . $_SERVER["REMOTE_ADDR"] . ' -->';
        if ($this->debugOn) { // && true
            $userName = (($this->v["user"]) ? $this->v["user"]->name : '');
            ob_start();
            print_r($GLOBALS["SL"]->REQ->all());
            $this->v["requestDeets"] = ob_get_contents();
            ob_end_clean(); 
            $this->v["lastNode"]           = $lastNode;
            $this->v["currNode"]           = $this->currNode();
            $this->v["coreID"]             = $this->coreID;
            $this->v["sessInfo"]           = $this->sessInfo;
            $this->v["sessData"]           = $this->sessData;
            $this->v["dataSets"]           = $this->sessData->dataSets;
            $this->v["currNodeDataBranch"] = $this->sessData->dataBranches;
            return view('vendor.survloop.inc-var-dump', $this->v)->render();
        }
        return '';
    }
    
    public function nodeSessDump($nIDtxt = '', $nID = -3)
    {
        if ($this->debugOn && true) {
            if ($nID > 0 && isset($this->allNodes[$nID]) && isset($this->allNodes[$nID]->nodeType)
                && $this->allNodes[$nID]->nodeType == 'Layout Column') {
                return '';
            }
            return view('vendor.survloop.inc-var-dump-node', [
                "nID"          => $nID,
                "nIDtxt"       => $nIDtxt,
                "dataBranches" => $this->sessData->dataBranches
                ])->render();
        }
        return '';
    }
    
    protected function tmpDebug($str = '')
    {
        return true;
    }
    
}