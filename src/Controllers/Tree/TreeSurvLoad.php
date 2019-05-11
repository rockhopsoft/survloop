<?php
/**
  * TreeSurvLoad is a mid-level class using a standard branching tree, 
  * mostly for SurvLoop's surveys and pages.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers\Tree;

use DB;
use Auth;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use SurvLoop\Models\User;
use SurvLoop\Models\SLDatabases;
use SurvLoop\Models\SLDefinitions;
use SurvLoop\Models\SLTree;
use SurvLoop\Models\SLNode;
use SurvLoop\Models\SLNodeSaves;
use SurvLoop\Models\SLNodeSavesPage;
use SurvLoop\Models\SLNodeResponses;
use SurvLoop\Models\SLFields;
use SurvLoop\Models\SLSess;
use SurvLoop\Models\SLSessLoops;
use SurvLoop\Models\SLSessEmojis;
use SurvLoop\Models\SLSearchRecDump;
use SurvLoop\Models\SLContact;
use SurvLoop\Models\SLUsersActivity;
use SurvLoop\Controllers\Tree\TreeNodeSurv;
use SurvLoop\Controllers\Tree\SurvData;
use SurvLoop\Controllers\Globals\Globals;
use SurvLoop\Controllers\Tree\TreeSurvConds;

class TreeSurvLoad extends TreeSurvConds
{
    protected $pageJSvalid        = '';
    protected $REQstep            = '';
    protected $hasREQ             = false;
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
    
    protected function loadNode($nodeRow = NULL)
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

    public function constructor(Request $request = null, $sessIn = -3, $dbID = -3, $treeID = -3, $skipSessLoad = false)
    {
        $this->dbID = (($dbID > 0) ? $dbID : ((isset($GLOBALS["SL"])) ? $GLOBALS["SL"]->dbID : 1));
        $this->treeID = (($treeID > 0) ? $treeID : ((isset($GLOBALS["SL"])) ? $GLOBALS["SL"]->treeID : 1));
        $this->survLoopInit($request);
        $this->coreIDoverride = -3;
        if ($sessIn > 0) {
            $this->coreIDoverride = $sessIn;
        }
        if (isset($GLOBALS["SL"]) && $GLOBALS["SL"]->REQ->has('step') && $GLOBALS["SL"]->REQ->has('tree') 
            && intVal($GLOBALS["SL"]->REQ->get('tree')) > 0) {
            $this->hasREQ = true;
            $this->REQstep = $GLOBALS["SL"]->REQ->get('step');
        }
        $this->loadLookups();
        $this->isPage = (isset($GLOBALS["SL"]->treeRow->TreeType) && $GLOBALS["SL"]->treeRow->TreeType == 'Page');
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
                    if (in_array($row->NodeType, ['Page', 'Loop Root'])) {
                        $this->pageCnt++;
                    }
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
                            . 'new SurvLoop\\Controllers\\Tree\\TreeNodeSurv(' 
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
    
    public function hasParentPage($nID)
    {
        if (isset($this->allNodes[$nID]) && isset($this->allNodes[$nID]->parentID) 
            && intVal($this->allNodes[$nID]->parentID) > 0) {
            if (isset($this->allNodes[$this->allNodes[$nID]->parentID]) 
                && $this->allNodes[$this->allNodes[$nID]->parentID]->isPage()) {
                return true;
            } else {
                return $this->hasParentPage($this->allNodes[$nID]->parentID);
            }
        }
        return false;
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
        if (trim($coreTbl) == '') {
            $coreTbl = $GLOBALS["SL"]->coreTbl;
        }
        if ($coreID <= 0) {
            $coreID = $this->coreID; 
        }
        $this->loadSessInfo($coreTbl);
        $this->loadSessionData($coreTbl, $coreID);
        $this->loadSessionDataSaves();
        $this->runLoopConditions();
        return true;
    }
    
    protected function loadExtra()
    {
        return true;
    }
    
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
            if (intVal($nIDtmp) > 0) {
                $parents[] = $nIDtmp;
            }
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
            if (!$skipPublic) {
                $this->chkPublicCoreID($coreTbl, $coreID);
            } else {
                $this->coreID = $this->corePublicID = $coreID;
            }
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
        for ($s = 0; $s < sizeof($this->majorSections); $s++) {
            $this->sessMinorsTouched[$s] = [];
        }
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
    
    protected function postNodePublicCustom($nID = -3, $nIDtxt = '', $tmpSubTier = [])
    {
        return false;
    }
    
    protected function postNodePublic($nID = -3, $tmpSubTier = [])
    {
        return true;
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
            return view('vendor.survloop.elements.inc-var-dump', $this->v)->render();
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
            return view('vendor.survloop.elements.inc-var-dump-node', [
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
    
    public function __toString()
    {
        if (sizeof($this->allNodes) > 0) {
            $print = [];
            foreach ($this->allNodes as $n) {
                $print[$n->nodeID] = $n->listCore();
            }
            echo '<pre>';
            print_r($print);
            echo '</pre>';
        }
        return true;
    }
    
}