<?php
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

use MatthiasMullie\Minify;

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

use SurvLoop\Controllers\CoreTree;
use SurvLoop\Controllers\SurvLoopNode;
use SurvLoop\Controllers\SurvLoopData;
use SurvLoop\Controllers\SurvLoopStat;
use SurvLoop\Controllers\SurvLoopTreeXML;
use SurvLoop\Controllers\SurvLoopSearch;
use SurvLoop\Controllers\CoreGlobals;

class SurvLoopTree extends CoreTree
{
    
    public $classExtension        = 'SurvLoopTree';
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
    
    protected $isReport           = false;
    protected $isBigSurvLoop      = ['', '', '']; // table name, and sort field, if this is tree one big loop
    
    public $xmlMapTree            = false;
    
    public $search                = false;
            public $checkedSearch         = false;
            public $searchTxt             = '';
            public $searchParse           = [];
            public $advSearchUrlSffx      = '';
            public $advSearchBarJS        = '';
            public $searchFilts           = [];
            public $searchOpts            = [];
            public $searchResults         = [];
    
    public $allPublicCoreIDs      = [];
    public $allPublicFiltIDs      = [];
    public $emojiTagUsrs          = [];
    
    
    // kidMaps[nodeID][kidNodeID][] = [ responseInd, responseValue ]
    public $kidMaps               = [];
    protected $newLoopItemID      = -3;
    
    protected function loadNode($nodeRow = [])
    {
        if ($nodeRow && isset($nodeRow->NodeID) && $nodeRow->NodeID > 0) {
            return new SurvLoopNode($nodeRow->NodeID, $nodeRow);
        }
        $newNode = new SurvLoopNode();
        $newNode->nodeRow->NodeTree = $this->treeID;
        return $newNode;
    }
    
    protected function loadLookups()
    {
        $this->debugOn = (!isset($_SERVER["REMOTE_ADDR"]) 
            || in_array($_SERVER["REMOTE_ADDR"], ['192.168.10.1', '173.79.192.119']));
        return true;
    }
    
    public function __construct(Request $request, $sessIn = -3, $dbID = -3, $treeID = -3, $skipSessLoad = false)
    {
        return $this->constructor($request, $sessIn, $dbID, $treeID, $skipSessLoad);
    }

    public function constructor(Request $request, $sessIn = -3, $dbID = -3, $treeID = -3, $skipSessLoad = false)
    {
        $this->dbID = (($dbID > 0) ? $dbID : ((isset($GLOBALS["SL"])) ? $GLOBALS["SL"]->dbID : 1));
        $this->treeID = (($treeID > 0) ? $treeID : ((isset($GLOBALS["SL"])) ? $GLOBALS["SL"]->treeID : 1));
        $this->search = new SurvLoopSearch;
        $this->survLoopInit($request);
        $this->coreIDoverride = -3;
        if ($sessIn > 0) $this->coreIDoverride = $sessIn;
        $this->REQ = $request;
        if (isset($GLOBALS["SL"]) && $GLOBALS["SL"]->REQ->has('step') && $GLOBALS["SL"]->REQ->has('tree') 
            && intVal($GLOBALS["SL"]->REQ->get('tree')) > 0) {
            $this->hasREQ = true;
            $this->REQstep = $GLOBALS["SL"]->REQ->get('step');
        }
        $this->loadLookups();
        $this->sessData = new SurvLoopData;
        return true;
    }
    
    public function loadPageVariation(Request $request, $dbID = 1, $treeID = 1, $currPage = '/')
    {
        $GLOBALS["SL"] = new CoreGlobals($request, $dbID, $treeID, $treeID);
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
            $cache = '// Auto-generated loading cache from /SurvLoop/Controllers/SurvLoopTree.php' . "\n\n";
            $this->pageCnt = 0;
            $nodeIDs = [];
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
                        . 'new SurvLoop\\Controllers\\SurvLoopNode(' 
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
            $this->kidMaps = [];
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
    
    // returns 1 if nID is a conditional kid, and is true
    // returns 0 if nID is not a conditional kid
    // returns -1 if nID is a conditional kid, and is false
    protected function chkKidMapTrue($nID)
    {
        $found = false;
        if (sizeof($this->kidMaps) > 0) {
            foreach ($this->kidMaps as $parent => $kids) {
                if (sizeof($kids) > 0) {
                    foreach ($kids as $nKid => $ress) {
                        if ($nID == $nKid && sizeof($ress) > 0) {
                            $found = true;
                            foreach ($ress as $cnt => $res) {
                                if (isset($res[2]) && $res[2]) return 1;
                            }
                        }
                    }
                }
            }
        }
        return (($found) ? -1 : 0);
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
    
    public function hasParentPage($nID)
    {
        if (isset($this->allNodes[$nID]) && isset($this->allNodes[$nID]->parentID) 
            && intVal($this->allNodes[$nID]->parentID) > 0) {
            if (isset($this->allNodes[$this->allNodes[$nID]->parentID]) 
                && $this->allNodes[$this->allNodes[$nID]->parentID]->isPage()) return true;
            else return $this->hasParentPage($this->allNodes[$nID]->parentID);
        }
        return false;
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
//echo '<br /><br /><br />core: ' . $coreID . ', coreTbl: ' . $coreTbl . '<pre>'; print_r($this->sessData->dataSets); echo '</pre>';
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
    
    protected function printNodeSessDataOverride($nID = -3, $tmpSubTier = [])
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
            . $GLOBALS["SL"]->sysOpts["logo-url"] . $this->getProgBarJsFilename() . '"></script>' . "\n";
        $ret = '';
        $majTot = 0;
        foreach ($this->majorSections as $maj => $majSect) {
            if ($maj == $this->currMajorSection) {
                $GLOBALS['SL']->pageJAVA .= 'treeMajorSects[' . $maj . '][3]="active";' . "\n";
            } elseif (in_array($maj, $this->sessMajorsTouched)) {
                $GLOBALS['SL']->pageJAVA .= 'treeMajorSects[' . $maj . '][3]="completed";' . "\n";
            }
            if ($majSect[2] == 'disabled') {
                $GLOBALS['SL']->pageJAVA .= 'treeMajorSectsDisabled[0]=' . $maj . ';' . "\n";
            } else {
                $majTot++;
            }
            if (sizeof($this->minorSections[$maj]) > 0) {
                foreach ($this->minorSections[$maj] as $min => $minSect) {
                    if ($maj == $this->currMajorSection && $min == $this->currMinorSection) {
                        $GLOBALS['SL']->pageJAVA .= 'treeMinorSects[' . $maj . '][' . $min . '][3]="active";' . "\n";
                    } elseif (in_array($min, $this->sessMinorsTouched[$maj])) {
                        $GLOBALS['SL']->pageJAVA .= 'treeMinorSects[' . $maj . '][' . $min . '][3]="completed";' ."\n";
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
            $ret .= view('vendor.survloop.inc-progress-bar', [
                "hasNavBot"         => ($GLOBALS["SL"]->treeRow->TreeOpts%59 == 0),
                "hasNavTop"         => ($GLOBALS["SL"]->treeRow->TreeOpts%37 == 0),
                "allNodes"          => $this->allNodes, 
                "majorSections"     => $this->majorSections, 
                "minorSections"     => $this->minorSections, 
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
                if ($this->v["uID"] <= 0) $retTF = false;
            } elseif (trim($cond->CondTag) == '#IsNotLoggedIn') {
                if ($this->v["uID"] > 0) $retTF = false;
            } elseif (trim($cond->CondTag) == '#IsAdmin') {
                if ($this->v["uID"] <= 0 || !$this->v["user"]->hasRole('administrator|staff|databaser')) $retTF = false;
            } elseif (trim($cond->CondTag) == '#IsNotAdmin') {
                if ($this->v["uID"] > 0 && $this->v["user"]->hasRole('administrator|staff|databaser')) $retTF = false;
            } elseif (trim($cond->CondTag) == '#IsOwner') {
                if ($this->v["uID"] <= 0 || !$this->v["isOwner"]) $retTF = false;
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
                if ($GLOBALS["SL"]->x["dataPerms"] != 'public') $retTF = false;
            } elseif (trim($cond->CondTag) == '#IsDataPermPrivate') {
                if ($GLOBALS["SL"]->x["dataPerms"] != 'private') $retTF = false;
            } elseif (trim($cond->CondTag) == '#IsDataPermSensitive') {
                if ($GLOBALS["SL"]->x["dataPerms"] != 'sensitive') $retTF = false;
            } elseif (trim($cond->CondTag) == '#IsDataPermInternal') {
                if ($GLOBALS["SL"]->x["dataPerms"] != 'internal') $retTF = false;
            } elseif (trim($cond->CondTag) == '#HasTokenDialogue') {
                if (!$this->pageLoadHasToken()) $retTF = false;
            } elseif (trim($cond->CondTag) == '#EmailVerified') {
                if ($this->v["uID"] <= 0 || !$this->v["user"]->hasVerifiedEmail()) $retTF = false;
            } elseif (trim($cond->CondTag) == '#NextButton') {
                if (!isset($this->REQstep) || $this->REQstep != 'next') $retTF = false;
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
    
    protected function nodeIsWithinPage($nID)
    {
        $parent = $this->allNodes[$nID]->getParent();
        while ($this->hasNode($parent)) {
            if ($this->allNodes[$parent]->isPage()) return true;
            if ($this->allNodes[$parent]->isBranch() || $this->allNodes[$parent]->isLoopRoot()) {
                return false;
            }
            $parent = $this->allNodes[$parent]->getParent();
        }
        return false;
    }
    
    protected function isDisplayableNode($nID, $exception = '')
    {
        if (!$this->hasNode($nID) || !$this->checkNodeConditions($nID)) return false;
        if ($this->allNodes[$nID]->isDataManip() && !$this->nodeIsWithinPage($nID)) {
            $this->runDataManip($nID, true);
        }
        if (!$this->allNodes[$nID]->isPage() && !$this->allNodes[$nID]->isLoopRoot()) return false;
        if (!$this->checkParentBranchConditions($nID)) return false;
        return true;
    }
    
    protected function checkParentBranchConditions($nID)
    {
        $clear = true;
        $parentID = $this->allNodes[$nID]->getParent();
        while ($parentID > 0 && $clear) {
            if (!$this->checkNodeConditions($parentID)) $clear = false;
            $parentID = $this->allNodes[$parentID]->getParent();
        }
        return $clear;
    }
    
    protected function runCurrNode($nID)
    {
        //if ($nID == $GLOBALS["SL"]->treeRow->TreeRoot) $this->runDataManip($nID);
        return true;
    }
    
    protected function runDataManip($nID, $betweenPages = false)
    {
//echo '<br /><br /><br />runDataManip(' . $nID . ' - <br />';
        if ($this->allNodes[$nID]->isDataManip()) {
            list($tbl, $fld, $newVal) = $this->allNodes[$nID]->getManipUpdate();
//echo 'runDataManip(' . $nID . ', tbl: ' . $tbl . ', ' . $fld . ' = ' . $newVal . ' - <pre>'; print_r($this->sessData->dataBranches); echo '</pre><br />';
            if ($this->allNodes[$nID]->nodeType == 'Data Manip: New') {
                //$newObj = $this->checkNewDataRecord($tbl, $fld, $newVal);
                //if (!$newObj) {
                    $newObj = $this->sessData->newDataRecord($tbl, $fld, $newVal);
                    if ($newObj) {
//echo 'runDataManip(' . $nID . ', tbl: ' . $tbl . ', ' . $fld . ', ' . $newObj->getKey() . ', ' . $newVal . ' - ' . $this->sessData->printAllTableIdFlds('PSAreas', ['PsAreaType']) . '<br />';
                        $this->sessData->startTmpDataBranch($tbl, $newObj->getKey());
//echo 'runDataManip(<pre>'; print_r($this->sessData->dataBranches); echo '</pre><br />';
                        $this->sessData->currSessData($nID, $tbl, $fld, 'update', $newVal);
                        $manipUpdates = SLNode::where('NodeTree', $this->treeID)
                            ->where('NodeType', 'Data Manip: Update')
                            ->where('NodeParentID', $nID)
                            ->get();
                        if ($manipUpdates->isNotEmpty()) {
                            foreach ($manipUpdates as $nodeRow) {
                                $tmpNode = new SurvLoopNode($nodeRow->NodeID, $nodeRow);
                                list($tbl, $fld, $newVal) = $tmpNode->getManipUpdate();
//echo 'runDataManip-_-_(' . $nID . ', tbl: ' . $tbl . ', ' . $fld . ' = ' . $newVal . ' - ' . $this->sessData->printAllTableIdFlds('PSAreas', ['PsAreaType']) . '<br />';
                                $this->sessData->currSessData($nodeRow->NodeID, $tbl, $fld, 'update', $newVal);
                            }
                        }
                        if ($betweenPages) $this->sessData->endTmpDataBranch($tbl);
//echo 'runDataManip-B(' . $nID . ', tbl: ' . $tbl . ', ' . $newObj->getKey() . ' - ' . $this->sessData->printAllTableIdFlds('PSAreas', ['PsAreaType']) . '<br />';
                    }
                //}
                //$this->loadAllSessData();
            } elseif ($this->allNodes[$nID]->nodeType == 'Data Manip: Update') {
                $this->sessData->currSessData($nID, $tbl, $fld, 'update', $newVal);
            }
        }
//echo 'runDataManip-Z(' . $nID . ' - ' . $this->sessData->printAllTableIdFlds('PSAreas', ['PsAreaType']) . '<br />';
        return true;
    }
    
    protected function reverseDataManip($nID)
    {
//echo 'reverseDataManip(' . $nID . '<br />';
        if ($this->allNodes[$nID]->isDataManip()) {
            list($tbl, $fld, $newVal) = $this->allNodes[$nID]->getManipUpdate();
            if ($this->allNodes[$nID]->nodeType == 'Data Manip: New') {
                $this->sessData->deleteDataRecord($tbl, $fld, $newVal);
                //$this->loadAllSessData();
            }
        }
        return true;
    }
    
    protected function nodeBranchInfo($nID, $curr = NULL)
    {
        $tbl = $fld = $newVal = '';
        if (!$curr) $curr = $this->allNodes[$nID];
        if (in_array($curr->nodeType, ['Data Manip: New', 'Data Manip: Wrap'])) { // Data Manip: Update
            list($tbl, $fld, $newVal) = $curr->getManipUpdate();
            if ($curr->nodeType == 'Data Manip: Wrap') $tbl = $curr->dataBranch;
        }
        if ($curr->isLoopCycle()) {
            $loop = '';
            if (isset($curr->nodeRow->NodeResponseSet) 
                && strpos($curr->nodeRow->NodeResponseSet, 'LoopItems:') === 0) {
                $loop = trim(str_replace('LoopItems:', '', $curr->nodeRow->NodeResponseSet));
            }
            if ($loop != '' && isset($GLOBALS["SL"]->dataLoops[$loop]) 
                && isset($GLOBALS["SL"]->dataLoops[$loop]->DataLoopTable)) {
                $tbl = $GLOBALS["SL"]->dataLoops[$loop]->DataLoopTable;
            } elseif (isset($curr->dataBranch) && trim($curr->dataBranch) != '') {
                $tbl = $curr->dataBranch;
            }
        }
        return [$tbl, $fld, $newVal];
    }
    
    protected function loadManipBranch($nID, $force = false)
    {
        list($tbl, $fld, $newVal) = $this->nodeBranchInfo($nID);
//echo '<br /><br /><br />loadManipBranch(' . $nID . ', tbl: ' . $tbl . ', fld: ' . $fld . ', val: ' . $newVal . '<br />';
        if ($tbl != '') {
            $manipBranchRow = $this->sessData->checkNewDataRecord($tbl, $fld, $newVal, []);
            if (!$manipBranchRow && $force) {
                $manipBranchRow = $this->sessData->newDataRecord($tbl, $fld, $newVal);
            }
            if ($manipBranchRow) $this->sessData->startTmpDataBranch($tbl, $manipBranchRow->getKey());
        }
//echo 'dataBranches: '; print_r($this->sessData->dataBranches); echo '<br />';
        return true;
    }
    
    protected function closeManipBranch($nID)
    {
        list($tbl, $fld, $newVal) = $this->nodeBranchInfo($nID);
//echo '<br /><br /><br />closeManipBranch(' . $nID . ', tbl: ' . $tbl . ', fld: ' . $fld . ', val: ' . $newVal . '<br />';
        if ($tbl != '') $this->sessData->endTmpDataBranch($tbl);
        return true;
    }
    
    protected function hasParentDataManip($nID)
    {
        $found = false;
        while ($this->hasNode($nID) && !$found) {
            if ($this->allNodes[$nID]->isDataManip()) {
                list($tbl, $fld, $newVal) = $this->allNodes[$nID]->getManipUpdate();
                if ($this->allNodes[$nID]->nodeType == 'Data Manip: New' && $fld != '' && $newVal != '') {
                    $found = true;
                }
            }
            $nID = $this->allNodes[$nID]->getParent();
        }
        return $found;
    }
    
    protected function getNextNonBranch($nID, $direction = 'next')
    {
        if ($nID == $GLOBALS["SL"]->treeRow->TreeLastPage && $direction == 'next') return -37;
        if (!$this->hasNode($nID)) return -3;
        $nIDbranch = $this->checkBranchCondition($nID, $direction);
        if ($nID != $nIDbranch) $nID = $nIDbranch; 
        $this->loopCnt = 0;
        while (!$this->isDisplayableNode($nID) && $this->loopCnt < 1000) {
            if ($nID == $GLOBALS["SL"]->treeRow->TreeLastPage && $direction == 'next') return -37;
            $nIDbranch = $this->checkBranchCondition($nID, $direction);
            if ($nID != $nIDbranch) $nID = $nIDbranch; 
            elseif ($direction == 'next') $nID = $this->nextNode($nID);
            else $nID = $this->prevNode($nID);
            $this->loopCnt++;
        }
        if (trim($this->loadingError) != '') {
            $ret .= '<div class="p10"><i>loadNodeSubTier() - ' . $this->loadingError . '</i></div>';
        }
        return $nID;
    }
    
    protected function checkBranchCondition($nID, $direction = 'next')
    {
        if (isset($this->allNodes[$nID]) && $this->allNodes[$nID]->isBranch() && !$this->checkNodeConditions($nID)) {
            if ($direction == 'next') $nID = $this->nextNodeSibling($nID);
            else $nID = $this->prevNode($nID);
        }
        return $nID;
    }
    
    protected function newLoopItem($nID = -3)
    {
        if (intVal($this->newLoopItemID) <= 0) {
            $newID = $this->sessData->createNewDataLoopItem($nID);
            $this->afterCreateNewDataLoopItem($GLOBALS["SL"]->REQ->input('loop'), $newID); //$loop->DataLoopPlural
            if ($newID > 0) {
                $GLOBALS["SL"]->REQ->loopItem = $newID;
                $this->settingTheLoop(trim($GLOBALS["SL"]->REQ->input('loop')), intVal($GLOBALS["SL"]->REQ->loopItem));
            }
            $this->newLoopItemID = $nID;
        }
        return true;
    }
    
    protected function settingTheLoop($name, $itemID = -3, $rootJustLeft = -3)
    {
        if ($name == '') return false; 
        $found = false;
        if (sizeof($GLOBALS["SL"]->sessLoops) > 0) {
            foreach ($GLOBALS["SL"]->sessLoops as $loop) {
                if (!$found && $loop->SessLoopName == $name) {
                    $loop->SessLoopItemID = $itemID;
                    $loop->save();
                    $found = true;
                }
            }
        }
        if (!$found) {
            $newLoop = new SLSessLoops;
            $newLoop->SessLoopSessID = $this->sessID;
            $newLoop->SessLoopName   = $name;
            $newLoop->SessLoopItemID = $itemID;
            $newLoop->save();
        }
        $GLOBALS["SL"]->loadSessLoops($this->sessID);
        
        $this->sessInfo->SessLoopRootJustLeft = $rootJustLeft;
        $this->sessInfo->save();
        $this->runLoopConditions();
        return true;
    }
    
    protected function leavingTheLoop($name = '', $justClearID = false)
    {
        if (sizeof($GLOBALS["SL"]->sessLoops) > 0) {
            foreach ($GLOBALS["SL"]->sessLoops as $i => $loop) {
                if ($loop->SessLoopName == $name || $name == '') {
                    if ($justClearID) {
                        $loop->SessLoopItemID = -3;
                        $loop->save();
                    } else {
                        $GLOBALS["SL"]->sessLoops[$i]->delete();
                        $this->sessData->leaveCurrLoop();
                    }
                }
            }
        }
        $GLOBALS["SL"]->loadSessLoops($this->sessID);
        return true;
    }
    
    protected function afterCreateNewDataLoopItem($tbl, $itemID = -3) { return true; }
    
    protected function checkLoopsPostProcessing($newNode, $prevNode)
    {
//echo '<br /><br /><br />checkLoopsPostProcessing(' . $newNode . ', ' . $prevNode . ' -- curr: ' . $this->currNode() . '<br />';
        $currLoops = [];
        $backToRoot = false;
        if ($newNode <= 0) $newNode = $this->nextNode($prevNode);
        // First, are we leaving one of our current loops?..
//echo 'sessLoops: <pre>'; print_r($GLOBALS["SL"]->sessLoops); echo '</pre>';
        if (sizeof($GLOBALS["SL"]->sessLoops) > 0) {
            foreach ($GLOBALS["SL"]->sessLoops as $sessLoop) {
                if (isset($GLOBALS["SL"]->dataLoops[$sessLoop->SessLoopName])) {
//echo 'dataLoops[' . $sessLoop->SessLoopName . '<br />';
                    $currLoops[$sessLoop->SessLoopName] = $sessLoop->SessLoopItemID;
                    $loop = $GLOBALS["SL"]->dataLoops[$sessLoop->SessLoopName];
                    if ($this->allNodes[$prevNode]->checkBranch($this->allNodes[$loop->DataLoopRoot]->nodeTierPath)
                        && !$this->allNodes[$newNode]->checkBranch($this->allNodes[$loop->DataLoopRoot]->nodeTierPath)){
                        // Then we are now trying to leave this loop
                        if (in_array($this->REQstep, ['back', 'exitLoopBack'])) { 
                            // Then leaving the loop backwards, always allowed
                            $this->leavingTheLoop($loop->DataLoopPlural);
                        } elseif ($this->REQstep != 'save') { // Check for conditions before moving leaving forward
                            if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop()) {
                                if (sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) > 1) {
                                    $backToRoot = true;
                                }
                            } elseif (intVal($loop->DataLoopMaxLimit) == 0 
                                || sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) 
                                    < $loop->DataLoopMaxLimit) {
                                // Then sure, we can add another item to this loop, back at the root node
                                $backToRoot = true;
                            }
                            if ($backToRoot) {
                                $this->updateCurrNode($loop->DataLoopRoot);
                                $this->leavingTheLoop($loop->DataLoopPlural, true);
                            } else { // OK, let's allow the user to keep going outside the loop
                                $this->sessInfo->SessLoopRootJustLeft = $loop->DataLoopRoot;
                                $this->sessInfo->save();
                                $this->leavingTheLoop($loop->DataLoopPlural);
                            }
                        }
                    } elseif ($newNode == $loop->DataLoopRoot) {
                        $skipRoot = false;
                        if ($this->allNodes[$newNode]->isStepLoop()) {
                            if (sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) == 1 
                                || ($loop->DataLoopMinLimit == 1 && $loop->DataLoopMaxLimit == 1)) {
                                $skipRoot = true;
                            }
                        } elseif ($loop->DataLoopMinLimit > 0 
                            && sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) == 0) {
                            $skipRoot = true;
                        }
                        if ($skipRoot) {
                            $this->pushCurrNodeVisit($newNode);
                            if ($this->REQstep == 'back') {
                                $this->leavingTheLoop($loop->DataLoopPlural);
                                $prev = $this->getNextNonBranch($this->prevNode($loop->DataLoopRoot), 'prev');
                                $this->updateCurrNodeNB($prev, 'prev');
                            } elseif ($this->REQstep != 'save') {
                                $this->updateCurrNodeNB($this->nextNode($loop->DataLoopRoot));
                            }
                        }
                    }
                }
            }
        }
        
//echo 'backToRoot: ' . (($backToRoot) ? 'true' : 'false') . ', dataLoops: ' . sizeof($GLOBALS["SL"]->dataLoops) . '<br />currLoops: <pre>'; print_r($currLoops); echo '</pre>';
        // If we haven't already tried to leave our loop, nor returned back to its root node...
        if (!$backToRoot && sizeof($GLOBALS["SL"]->dataLoops) > 0) {
            foreach ($GLOBALS["SL"]->dataLoops as $loop) {
                if (!isset($currLoops[$loop->DataLoopPlural]) && isset($this->allNodes[$loop->DataLoopRoot])) {
                    // Then this is a new loop we weren't previously in
                    $path = $this->allNodes[$loop->DataLoopRoot]->nodeTierPath;
                    if (!$this->allNodes[$prevNode]->checkBranch($path)
                        && $this->allNodes[$newNode]->checkBranch($path)) {
//echo '!checkBranch(' . $prevNode . ', checkBranch(' . $newNode . '<br />';
                        // Then we have just entered this loop from outside
                        if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop() 
                            && (!isset($this->sessData->loopItemIDs[$loop->DataLoopPlural]) 
                                || empty($this->sessData->loopItemIDs[$loop->DataLoopPlural]))) {
//echo 'no loopItemIDs(' . $loop->DataLoopPlural . '<br />';
                            $this->leavingTheLoop($loop->DataLoopPlural);
                            if (isset($this->REQstep) && in_array($this->REQstep, ['back', 'exitLoopBack'])) {
                                $prevRoot = $this->getNextNonBranch($this->prevNode($loop->DataLoopRoot), 'prev');
                                $this->updateCurrNodeNB($prevRoot);
                            } elseif (!isset($this->REQstep) || $this->REQstep != 'save') {
                                $this->updateCurrNodeNB($this->nextNodeSibling($newNode));
                            }
                        } else { // This loop is active
//echo 'loop is active - ' . $loop->DataLoopPlural . ', root: ' . $loop->DataLoopRoot . ', is step? ' . (($this->allNodes[$loop->DataLoopRoot]->isStepLoop()) ? 'Y' : 'N') . ', min: ' . $loop->DataLoopMinLimit . ', max: ' . $loop->DataLoopMaxLimit . '<br />';
                            $skipRoot = false;
                            if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop()) {
                                if (sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) == 1 
                                    || ($loop->DataLoopMinLimit == 1 && $loop->DataLoopMaxLimit == 1)) {
                                    $skipRoot = true;
                                }
                            } elseif ($loop->DataLoopMinLimit > 0 
                                && sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) == 0) {
                                $skipRoot = true;
                            }
                            $this->settingTheLoop($loop->DataLoopPlural);
                            if ($newNode == $loop->DataLoopRoot) {
                                // Then we landed directly on the loop's root node from outside, 
                                // so we must be going forward not back
                                if ($skipRoot) {
                                    $this->pushCurrNodeVisit($newNode);
                                    $itemID = -3;
                                    if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop()) {
                                        $itemID = $this->sessData->loopItemIDs[$loop->DataLoopPlural][0];
                                    } elseif ($loop->DataLoopAutoGen == 1) {
                                        $itemID = $this->sessData->createNewDataLoopItem($loop->DataLoopRoot);
                                        $this->afterCreateNewDataLoopItem($loop->DataLoopPlural, $itemID);
                                    }
                                    $GLOBALS["SL"]->REQ->loop = $loop->DataLoopPlural;
                                    $GLOBALS["SL"]->REQ->loopItem = $itemID;
                                    $this->settingTheLoop($loop->DataLoopPlural, $itemID);
                                    $this->updateCurrNodeNB($this->nextNode($loop->DataLoopRoot));
                                    $GLOBALS["SL"]->loadSessLoops($this->sessID);
                                }
                            } else {
                                // Must have landed at the loop's end node from outside, so we going back not forward
                                if ($skipRoot) {
                                    $this->pushCurrNodeVisit($newNode);
                                    if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop()) {
                                        $this->settingTheLoop($loop->DataLoopPlural, 
                                            $this->sessData->loopItemIDs[$loop->DataLoopPlural][0]);
                                    }
                                } else {
                                    $this->updateCurrNode($loop->DataLoopRoot);
                                }
                            }
                        }
                    }
                }
            }
        }
        /*
        if ($this->currNode() != $newNode) {
            return $this->checkLoopsPostProcessing($this->currNode(), $newNode);
        }
        */
//echo '<br /><br /><br />end checkLoopsPostProcessing(' . $newNode . ', ' . $prevNode . ' -- now curr: ' . $this->currNode() . '<br />loopItemIDs: <pre>'; print_r($this->sessData->loopItemIDs); echo '</pre>';
        return true;
    }
    
    public function pushCurrNodeURL($nID = -3)
    {
        if ($GLOBALS['SL']->treeRow->TreeType == 'Page') return true;
        if (intVal($nID) > 0 && isset($this->allNodes[$nID])) {
            $this->allNodes[$nID]->fillNodeRow();
            if (isset($this->allNodes[$nID]->nodeRow->NodePromptNotes) 
                && trim($this->allNodes[$nID]->nodeRow->NodePromptNotes) != '') {
                $this->pushCurrNodeVisit($nID);
                if ($this->hasREQ && ($GLOBALS["SL"]->REQ->has('ajax') || $GLOBALS["SL"]->REQ->has('frame'))) {
                    $title = $this->allNodes[$nID]->nodeRow->NodePromptText;
                    if (strpos($title, '</h1>') > 0) $title = substr($title, 0, strpos($title, '</h1>'));
                    elseif (strpos($title, '</h2>') > 0) $title = substr($title, 0, strpos($title, '</h2>'));
                    elseif (strpos($title, '</h3>') > 0) $title = substr($title, 0, strpos($title, '</h3>'));
                    $title = str_replace('"', '\\"', str_replace('(s)', '', strip_tags($title)));
                    $title = trim(preg_replace('/\s\s+/', ' ', $title));
                    $title = str_replace("\n", " ", $title);
                    if (trim($title) == '') $title = trim($GLOBALS['SL']->treeRow->TreeName);
                    if (strlen($title) > 40) $title = substr($title, 0, 40) . '...';
                    $this->v["currPage"]    = [];
                    $this->v["currPage"][1] = ((trim($title) != '') ? $title . ' - ' : '') 
                        . $GLOBALS["SL"]->sysOpts["site-name"];
                    $this->v["currPage"][0] = '/' . (($GLOBALS["SL"]->treeIsAdmin) ? 'dash' : 'u') . '/' 
                        . $GLOBALS["SL"]->treeRow->TreeSlug . '/' . $this->allNodes[$nID]->nodeRow->NodePromptNotes;
                    $GLOBALS["SL"]->pageAJAX .= 'history.pushState( {}, "' . $this->v["currPage"][1] . '", '
                        . '"' . $this->v["currPage"][0] . '");' . "\n" 
                        . 'document.title="' . $this->v["currPage"][1] . '";' . "\n";
                }
            }
        }
        return true;
    }
    
    public function pushCurrNodeVisit($nID)
    {
        if (isset($this->sessInfo->SessID) && $nID > 0 && !$GLOBALS["SL"]->REQ->has('preview')) {
            $pagsSave = new SLNodeSavesPage;
            $pagsSave->PageSaveSession = $this->sessInfo->SessID;
            $pagsSave->PageSaveNode    = $nID;
            $pagsSave->save();
        }
        return true;
    }
    
    public function setNodeURL($slug = '')
    {
        $this->urlSlug = $slug;
        return true;
    }
    
    public function currNodeURL($nID = -3)
    {
        $curr = $this->currNode();
        if ($nID > 0) $curr = $nID;
        if (!isset($this->allNodes[$curr])) return '';
        $this->allNodes[$curr]->fillNodeRow();
        if (isset($this->allNodes[$curr]) && $this->allNodes[$curr]->isPage()
            && trim($this->allNodes[$curr]->nodeRow->NodePromptNotes) != '') {
            return '/' . (($GLOBALS["SL"]->treeIsAdmin) ? 'dash' : 'u') . '/' 
                . $GLOBALS["SL"]->treeRow->TreeSlug . '/' . $this->allNodes[$curr]->nodeRow->NodePromptNotes;
        }
        return '';
    }
    
    public function pullNewNodeURL()
    {
        if (trim($this->urlSlug) != '') {
            $loadNode = SLNode::where('NodeTree', $this->treeID)
                ->where('NodePromptNotes', $this->urlSlug)
                ->where(function ($query) {
                    return $query->where('NodeType', 'Page')
                        ->orWhere('NodeType', 'Loop Root');
                })
                ->first();
            if ($loadNode && isset($loadNode->NodeID)) {
                if (!$GLOBALS["SL"]->REQ->has('preview') && !$GLOBALS["SL"]->REQ->has('popStateUrl')) {
                    $loadNodeChk = DB::table('SL_NodeSavesPage')
                        ->join('SL_Sess', 'SL_NodeSavesPage.PageSaveSession', '=', 'SL_Sess.SessID')
                        ->where('SL_Sess.SessTree', '=', $this->treeID)
                        ->where('SL_Sess.SessCoreID', '=', $this->coreID)
                        ->where('SL_NodeSavesPage.PageSaveNode', $loadNode->NodeID)
                        ->get();
                    if ($loadNodeChk->isEmpty()) return false;
                }
                // perhaps upgrade to check for loop item id first?
                //$this->leavingTheLoop();
                $prevNode = $this->currNode();
                $this->updateCurrNode($loadNode->NodeID);
                if (sizeof($GLOBALS["SL"]->dataLoops) > 0 && sizeof($GLOBALS["SL"]->sessLoops) > 0) {
                    foreach ($GLOBALS["SL"]->sessLoops as $sessLoop) {
                        foreach ($GLOBALS["SL"]->dataLoops as $loop) {
                            if ($sessLoop->SessLoopName == $loop->DataLoopPlural) {
                                $path = $this->allNodes[$loop->DataLoopRoot]->nodeTierPath;
                                if ($this->allNodes[$prevNode]->checkBranch($path)
                                    && !$this->allNodes[$this->currNode()]->checkBranch($path)) {
                                    $this->leavingTheLoop($loop->DataLoopPlural);
                                }
                            }
                        }
                    }
                }
                if ($loadNode->NodeType == 'Loop Root') {
                    $this->checkLoopsPostProcessing($loadNode->NodeID, $prevNode);
                }
            }
        }
        return true;
    }
    
    
    
    /******************************************************************************************************
    
    REPORT OUTPUT
    
    ******************************************************************************************************/
    
    public function chkDeets($deets)
    {
        $new = [];
        if (sizeof($deets) > 0) {
            foreach ($deets as $i => $deet) {
                if (isset($deet[0]) && trim($deet[0]) != '') $new[] = $deet;
            }
        }
        return $new;
    }
    
    public function printReportDeetsBlock($deets, $blockName = '', $nID = -3)
    {
        $deets = $this->chkDeets($deets);
        return view('vendor.survloop.inc-report-deets', [
            "nID"       => $nID,
            "deets"     => $deets,
            "blockName" => $blockName
            ])->render();
    }
    
    public function printReportDeetsBlockCols($deets, $blockName = '', $cols = 2, $nID = -3)
    {
        $deets = $this->chkDeets($deets);
        $deetCols = $deetsTots = $deetsTotCols = [];
        $colChars = (($cols == 2) ? 37 : (($cols == 3) ? 24 : 16));
        if (sizeof($deets) > 0) {
            foreach ($deets as $i => $deet) {
                $size = 0; //strlen($deet[0]);
                if (sizeof($deet) > 1 && isset($deet[1]) && $size < strlen($deet[1])) $size = strlen($deet[1]);
                if ($size > $colChars) $size -= $colChars;
                else $size = 0;
                $size = ($size/$colChars)+2;
                if ($i == 0) $deetsTots[$i] = $size;
                else $deetsTots[$i] = $deetsTots[$i-1]+$size;
            }
            for ($c = 0; $c < $cols; $c++) {
                $deetCols[$c] = [];
                $deetsTotCols[$c] = [ (($c/$cols)*$deetsTots[sizeof($deetsTots)-1]), -3 ];
            }
            $c = $deetsTotCols[0][1] = 0;
            foreach ($deets as $i => $deet) {
                $chk = 1+$c;
                if ($chk < $cols && $deetsTotCols[$chk][1] < 0 && $deetsTotCols[$chk][0] < $deetsTots[$i]
                    && sizeof($deetCols[$c]) > 0) {
                    $deetsTotCols[$chk][1] = $i;
                    $c++;
                }
                $deetCols[$c][] = $deet;
            }
        }
        return view('vendor.survloop.inc-report-deets-cols', [
            "nID"       => $nID,
            "deetCols"  => $deetCols,
            "blockName" => $blockName,
            "colWidth"  => $GLOBALS["SL"]->getColsWidth($cols)
            ])->render();
    }
    
    public function printReportDeetsVertProg($deets, $blockName = '', $nID = -3)
    {
        $last = 0;
        if (sizeof($deets) > 0) {
            foreach ($deets as $i => $deet) {
                if (isset($deet[1]) && intVal($deet[1]) > 0) $last = $i;
            }
        }
        return view('vendor.survloop.inc-report-deets-vert-prog', [
            "nID"       => $nID,
            "deets"     => $deets,
            "blockName" => $blockName,
            "last"      => $last
            ])->render();
    }
    
    
    
    
    /******************************************************************************************************
    
    XML OUTPUT
    
    ******************************************************************************************************/
    
    protected function maxUserView()
    {
        return true;
    }
    
    private function loadXmlMapTree(Request $request)
    {
        $this->survLoopInit($request);
        if (isset($GLOBALS["SL"]->xmlTree["id"]) && empty($this->xmlMapTree)) {
            $this->xmlMapTree = new SurvLoopTreeXML;
            $this->xmlMapTree->loadTree($GLOBALS["SL"]->xmlTree["id"], $request, true);
        }
        return true;
    }
        
    private function getXmlTmpV($nID, $tblID = -3)
    {
        $v = [];
        if ($tblID > 0) $v["tbl"] = $GLOBALS["SL"]->tbl[$tblID];
        else $v["tbl"] = $this->xmlMapTree->getNodeTblName($nID);
        $v["tblID"]    = ((isset($GLOBALS["SL"]->tblI[$v["tbl"]])) ? $GLOBALS["SL"]->tblI[$v["tbl"]] : 0);
        $v["tblAbbr"]  = ((isset($GLOBALS["SL"]->tblAbbr[$v["tbl"]])) ? $GLOBALS["SL"]->tblAbbr[$v["tbl"]] : '');
        $v["TblOpts"]  = 1;
        if ($nID > 0 && isset($this->xmlMapTree->allNodes[$nID])) {
            $v["TblOpts"] = $this->xmlMapTree->allNodes[$nID]->nodeOpts;
        }
        $v["tblFlds"] = SLFields::select()
            ->where('FldDatabase', $this->dbID)
            ->where('FldTable', '=', $v["tblID"])
            ->orderBy('FldOrd', 'asc')
            ->orderBy('FldEng', 'asc')
            ->get();
        $v["tblFldEnum"] = $v["tblFldDefs"] = [];
        if ($v["tblFlds"]->isNotEmpty()) {
            foreach ($v["tblFlds"] as $i => $fld) {
                $v["tblFldDefs"][$fld->FldID] = [];
                if (strpos($fld->FldValues, 'Def::') !== false) {
                    $set = $GLOBALS["SL"]->def->getSet(str_replace('Def::', '', $fld->FldValues));
                    if (sizeof($set) > 0) {
                        foreach ($set as $def) $v["tblFldDefs"][$fld->FldID][] = $def->DefValue;
                    }
                } elseif (trim($fld->FldValues) != '' && strpos($fld->FldValues, ';') !== false) {
                    $v["tblFldDefs"][$fld->FldID] = explode(';', $fld->FldValues);
                }
                $v["tblFldEnum"][$fld->FldID] = (sizeof($v["tblFldDefs"][$fld->FldID]) > 0);
            }
        }
        $v["tblHelp"] = $v["tblHelpFld"] = [];
        if ($v["tblID"] > 0 && sizeof($GLOBALS["SL"]->dataHelpers) > 0) {
            foreach ($GLOBALS["SL"]->dataHelpers as $helper) {
                if ($v["tbl"] == $helper->DataHelpParentTable && $helper->DataHelpValueField
                    && !in_array($GLOBALS["SL"]->tblI[$helper->DataHelpTable], $v["tblHelp"])) {
                    $v["tblHelp"][] = $GLOBALS["SL"]->tblI[$helper->DataHelpTable];
                    $v["tblHelpFld"][$GLOBALS["SL"]->tblI[$helper->DataHelpTable]] 
                        = SLFields::where('FldTable', $GLOBALS["SL"]->tblI[$helper->DataHelpTable])
                            ->where('FldName', substr($helper->DataHelpValueField, 
                                strlen($GLOBALS["SL"]->tblAbbr[$helper->DataHelpTable])))
                            ->first();
                }
            }
        }
        return $v;
    }
    
    public function genXmlSchema(Request $request)
    {
        $this->loadXmlMapTree($request);
        if (!isset($GLOBALS["SL"]->xmlTree["coreTbl"])) return $this->redir('/');
        $this->v["nestedNodes"] = $this->genXmlSchemaNode($this->xmlMapTree->rootID, $this->xmlMapTree->nodeTiers);
        $view = view('vendor.survloop.admin.tree.xml-schema', $this->v)->render();
        return Response::make($view, '200')->header('Content-Type', 'text/xml');
    }
    
    public function genXmlSchemaNode($nID, $nodeTiers, $overV = [])
    {
        $v = [];
        if (sizeof($overV) > 0) $v = $overV;
        else $v = $this->getXmlTmpV($nID);
        $v["kids"] = '';
        if ($v["tblHelp"] && sizeof($v["tblHelp"]) > 0) {
            foreach ($v["tblHelp"] as $help) {
                $nextV = $this->getXmlTmpV(-3, $help);
                if (isset($v["tblHelpFld"][$help]->FldName)) {
                    $v["kids"] .= '<xs:element name="' . $nextV["tbl"] . '" minOccurs="0">
                        <xs:complexType mixed="true"><xs:sequence>
                            <xs:element name="' . $v["tblHelpFld"][$help]->FldName 
                            . '" minOccurs="0" maxOccurs="unbounded" />
                        </xs:sequence></xs:complexType>
                    </xs:element>' . "\n";
                }
            }
        }
        for ($i = 0; $i < sizeof($nodeTiers[1]); $i++) {
            $v["kids"] .= $this->genXmlSchemaNode($nodeTiers[1][$i][0], $nodeTiers[1][$i]);
        }
        return view('vendor.survloop.admin.tree.xml-schema-node', $v )->render();
    }
    
    public function genXmlReport(Request $request)
    {
        if (!isset($GLOBALS["SL"]->xmlTree["coreTbl"])) return $this->redir('/xml-schema');
        if (!isset($this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]]) 
            || empty($this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]])) {
            return $this->redir('/xml-schema');
        }
        $this->v["nestedNodes"] = $this->genXmlReportNode($this->xmlMapTree->rootID, $this->xmlMapTree->nodeTiers, 
            $this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]][0]);
        if (trim($this->v["nestedNodes"]) == '') return $this->redir('/xml-schema');
        $view = view('vendor.survloop.admin.tree.xml-report', $this->v)->render();
        return Response::make($view, '200')->header('Content-Type', 'text/xml');
    }
    
    public function genXmlReportNode($nID, $nodeTiers, $rec, $overV = [])
    {
        $v = [];
        if (sizeof($overV) > 0) $v = $overV;
        else $v = $this->getXmlTmpV($nID);
        $v["rec"]     = $rec;
        $v["recFlds"] = [];
        if (sizeof($v["tblFlds"]) > 0) {
            foreach ($v["tblFlds"] as $i => $fld) {
                //if (!$this->checkValEmpty($fld->FldType, $rec->{ $v["tblAbbr"] . $fld->FldName })) {
                    $v["recFlds"][$fld->FldID] = $this->genXmlFormatVal($rec, $fld, $v["tblAbbr"]);
                //}
            }
        }
        $v["kids"] = '';
        if (is_array($v["tblHelp"]) && sizeof($v["tblHelp"]) > 0) {
            foreach ($v["tblHelp"] as $help) {
                $nextV = $this->getXmlTmpV(-3, $help);
                $kidRows = $this->sessData->getChildRows($v["tbl"], $rec->getKey(), $nextV["tbl"]);
                if ($kidRows && sizeof($kidRows) > 0) {
                    if (intVal($nextV["tblID"]) > 0 && $nextV["TblOpts"]%5 > 0) {
                        $v["kids"] .= '<' . $nextV["tbl"] . '>' . "\n";
                    }
                    foreach ($kidRows as $j => $kid) {
                        if (isset($v["tblHelpFld"][$help]->FldName)) {
                            //if (!$this->checkValEmpty($kid, 
                            //    $rec->{ $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->tbl[$help]] 
                            //        . $v["tblHelpFld"][$help] })) {
                                $v["kids"] .= '<' . $v["tblHelpFld"][$help]->FldName . '>' 
                                    . $this->genXmlFormatVal($kid, $v["tblHelpFld"][$help], 
                                        $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->tbl[$help]])
                                . '</' . $v["tblHelpFld"][$help]->FldName . '>' . "\n";
                            //}
                        }
                    }
                    if (intVal($nextV["tblID"]) > 0 && $nextV["TblOpts"]%5 > 0) {
                        $v["kids"] .= '</' . $nextV["tbl"] . '>' . "\n";
                    }
                }
            }
        }
        for ($i = 0; $i < sizeof($nodeTiers[1]); $i++) {
            $tbl2 = $this->xmlMapTree->getNodeTblName($nodeTiers[1][$i][0]);
            $kidRows = $this->sessData->getChildRows($v["tbl"], $rec->getKey(), $tbl2);
            if ($kidRows && sizeof($kidRows) > 0) {
                $nextV = $this->getXmlTmpV($nodeTiers[1][$i][0]);
                if (intVal($nextV["tblID"]) > 0 && $nextV["TblOpts"]%5 > 0) {
                    $v["kids"] .= '<' . $nextV["tbl"] . '>' . "\n";
                }
                foreach ($kidRows as $j => $kid) {
                    $v["kids"] .= $this->genXmlReportNode($nodeTiers[1][$i][0], $nodeTiers[1][$i], $kid);
                }
                if (intVal($nextV["tblID"]) > 0 && $nextV["TblOpts"]%5 > 0) {
                    $v["kids"] .= '</' . $nextV["tbl"] . '>' . "\n";
                }
            }
        }
        return view('vendor.survloop.admin.tree.xml-report-node', $v )->render();
    }
    
    // FldOpts %1 XML Public Data; %7 XML Private Data; %11 XML Sensitive Data; %13 XML Internal Use Data
    public function checkViewDataPerms($fld)
    {
        if ($fld && isset($fld->FldOpts) && intVal($fld->FldOpts) > 0) {
            if ($fld->FldOpts%7 > 0 && $fld->FldOpts%11 > 0 && $fld->FldOpts%13 > 0) return true;
            if (in_array($GLOBALS["SL"]->x["pageView"], ['full', 'full-pdf', 'full-xml'])) return true;
            if ($fld->FldOpts%13 == 0 || $fld->FldOpts%11 == 0) return false;
            return true;
        }
        return false;
    }
    
    public function checkFldDataPerms($fld)
    {
        if ($fld && isset($fld->FldOpts) && intVal($fld->FldOpts) > 0) {
            if ($fld->FldOpts%7 > 0 && $fld->FldOpts%11 > 0 && $fld->FldOpts%13 > 0) return true;
            if ($GLOBALS["SL"]->x["dataPerms"] == 'internal') return true;
            elseif ($fld->FldOpts%13 == 0) return false;
            if ($fld->FldOpts%11 == 0) return ($GLOBALS["SL"]->x["dataPerms"] == 'sensitive');
            if ($fld->FldOpts%7 == 0) return in_array($GLOBALS["SL"]->x["dataPerms"], ['private', 'sensitive']);
        }
        return false;
    }
    
    public function genXmlFormatVal($rec, $fld, $abbr)
    {
        $val = false;
        if ($this->checkFldDataPerms($fld) && $this->checkViewDataPerms($fld)
            && isset($rec->{ $abbr . $fld->FldName })) {
            $val = $rec->{ $abbr . $fld->FldName };
            if (strpos($fld->FldValues, 'Def::') !== false) {
                if (intVal($val) > 0) {
                    $val = $GLOBALS["SL"]->def->getVal(str_replace('Def::', '', $fld->FldValues), $val);
                } else {
                    $val = false;
                }
            } else { // not pulling values from a definition set
                if (in_array($fld->FldType, array('INT', 'DOUBLE'))) {
                    if (intVal($val) == 0) $val = false;
                } elseif (in_array($fld->FldType, array('VARCHAR', 'TEXT'))) {
                    if (trim($val) == '') {
                        $val = false;
                    } else {
                        if ($val != htmlspecialchars($val, ENT_XML1, 'UTF-8')) {
                            $val = '<![CDATA[' . $val . ']]>'; // !in_array($val, array('Y', 'N', '?'))
                        }
                    }
                } elseif ($fld->FldType == 'DATETIME') {
                    if ($val == '0000-00-00 00:00:00' || $val == '1970-01-01 00:00:00') return '';
                    $val = str_replace(' ', 'T', $val);
                } elseif ($fld->FldType == 'DATE') {
                    if ($val == '0000-00-00' || $val == '1970-01-01') return '';
                }
            }
        }
        return $val;
    }
    
    public function checkValEmpty($fldType, $val)
    {
        $val = trim($val);
        if ($fldType == 'DATE' && ($val == '' || $val == '0000-00-00' || $val == '1970-01-01')) {
            return true;
        } elseif ($fldType == 'DATETIME' 
            && ($val == '' || $val == '0000-00-00 00:00:00' || $val == '1970-01-01 00:00:00')) {
            return true;
        }
        return false;
    }
    
    
    
    
    public function runAjaxChecks(Request $request, $over = '') {
        if ($request->has('email') && $request->has('password')) {
            print_r($request);
            $chk = User::where('email', $request->email)
                ->get();
            if ($chk->isNotEmpty()) echo 'found';
            echo 'not found';
            exit;
        }
    }
    
    protected function isStepUpload()
    {
        return (in_array($this->REQstep, ['upload', 'uploadDel', 'uploadSave']));
    }
    
    public function loadNodeURL(Request $request, $nodeSlug = '')
    {
        if (trim($nodeSlug) != '') $this->setNodeURL($nodeSlug);
        return $this->index($request);
    }
    
    public function testRun(Request $request)
    {
        return $this->index($request, 'testRun');
    }
    
    public function ajaxChecks(Request $request, $type = '')
    {
        $this->survLoopInit($request, '/ajax/' . $type);
        $ret = $this->ajaxChecksCustom($request, $type);
        if (trim($ret) != '') return $ret;
        $ret = $this->ajaxChecksSL($request, $type);
        if (trim($ret) != '') return $ret;
        return $this->index($request, 'ajaxChecks');
    }
    
    public function ajaxChecksSL(Request $request, $type = '')
    {
        $this->survLoopInit($request, '/ajadm/' . $type);
        $nID = (($request->has('nID')) ? trim($request->get('nID')) : '');
        if ($type == 'color-pick') {
            $fldName = (($request->has('fldName')) ? trim($request->get('fldName')) : '');
            $preSel = (($request->has('preSel')) ? '#' . trim($request->get('preSel')) : '');
            if (trim($fldName) != '') {
                $sysColors = [];
                $sysStyles = SLDefinitions::where('DefDatabase', 1)
                    ->where('DefSet', 'Style Settings')
                    ->orderBy('DefOrder')
                    ->get();
                $isCustom = true;
                if ($sysStyles->isNotEmpty()) {
                    foreach ($sysStyles as $i => $sty) {
                        if (strpos($sty->DefSubset, 'color-') !== false 
                            && !in_array($sty->DefDescription, $sysColors)) {
                            $sysColors[] = $sty->DefDescription;
                            if ($sty->DefDescription == $preSel) $isCustom = false;
                        }
                    }
                }
                return view('vendor.survloop.inc-color-picker-ajax', [
                    "sysColors" => $sysColors,
                    "fldName"   => $fldName,
                    "preSel"    => $preSel,
                    "isCustom"  => $isCustom
                ]);
            }
        } elseif (substr($type, 0, 4) == 'img-') {
            $imgID = (($request->has('imgID')) ? trim($request->get('imgID')) : '');
            $presel = (($request->has('presel')) ? trim($request->get('presel')) : '');
            if ($type == 'img-sel') {
                $newUp = (($request->has('newUp')) ? trim($request->get('newUp')) : '');
                return $GLOBALS["SL"]->getImgSelect($nID, $GLOBALS["SL"]->dbID, $presel, $newUp);
            } elseif ($type == 'img-deet') {
                return $GLOBALS["SL"]->getImgDeet($imgID, $nID);
            } elseif ($type == 'img-save') {
                return $GLOBALS["SL"]->saveImgDeet($imgID, $nID);
            } elseif ($type == 'img-up') {
                return $GLOBALS["SL"]->uploadImg($nID, $presel);
            }
        }
        return '';
    }
    
    public function ajaxChecksCustom(Request $request, $type = '')
    {
        return '';
    }
    
    public function byID(Request $request, $coreID, $coreSlug = '', $skipWrap = false, $skipPublic = false)
    {
        $this->survLoopInit($request, '/report/' . $coreID);
        if (!$skipPublic) $coreID = $GLOBALS["SL"]->chkInPublicID($coreID);
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        if ($request->has('hideDisclaim') && intVal($request->hideDisclaim) == 1) $this->hideDisclaim = true;
        $this->v["isPublicRead"] = true;
        $this->v["content"] = $this->printFullReport();
        if ($skipWrap) return $this->v["content"];
        $this->v["footOver"] = $this->printNodePageFoot();
        return $GLOBALS["SL"]->swapSessMsg(view('vendor.survloop.master', $this->v)->render());
    }
    
    public function getAllPublicCoreIDs($coreTbl = '')
    {
        if (trim($coreTbl) == '') $coreTbl = $GLOBALS["SL"]->coreTbl;
        $this->allPublicCoreIDs = [];
        eval("\$list = " . $GLOBALS["SL"]->modelPath($coreTbl) . "::orderBy('created_at', 'desc')->get();");
        if ($list->isNotEmpty()) {
            foreach ($list as $l) $this->allPublicCoreIDs[] = $l->getKey();
        }
        return $this->allPublicCoreIDs;
    }
    
    public function printReports(Request $request, $full = true)
    {
        $this->survLoopInit($request, '/reports-full/' . $this->treeID);
        $this->loadTree();
        $ret = '';
        if ($request->has('i') && intVal($request->get('i')) > 0) {
            $ret .= $this->printReportsRecordPublic($request->get('i'), $full);
        } elseif ($request->has('ids') && trim($request->get('ids')) != '') {
            foreach ($GLOBALS["SL"]->mexplode(',', $request->get('ids')) as $id) {
                $ret .= $this->printReportsRecordPublic($id, $full);
            }
        } elseif ($request->has('rawids') && trim($request->get('rawids')) != '') {
            foreach ($GLOBALS["SL"]->mexplode(',', $request->get('rawids')) as $id) {
                $ret .= $this->printReportsRecord($id, $full);
            }
        } else {
            $this->getAllPublicCoreIDs();
            $this->getSearchFilts();
            $this->processSearchFilts();
            if (sizeof($this->allPublicFiltIDs) > 0) {
                foreach ($this->allPublicFiltIDs as $i => $coreID) {
                    if (!isset($this->searchOpts["limit"]) || $i < $this->searchOpts["limit"]) {
                        $ret .= $this->printReportsRecordPublic($coreID, $full);
                    }
                }
            }
        }
        return $ret;
    }
    
    public function printReportsRecord($coreID = -3, $full = true)
    {
        if (!$this->isPublished($GLOBALS["SL"]->coreTbl, $coreID) && !$this->isCoreOwner($coreID)
            && (!$this->v["user"] || !$this->v["user"]->hasRole('administrator|staff'))) {
            return $this->unpublishedMessage($GLOBALS["SL"]->coreTbl);
        }
        if ($full) {
            return '<div class="reportWrap">' . $this->byID($GLOBALS["SL"]->REQ, $coreID, '', true, true) . '</div>';
        }
        return $this->printReportsPrev($coreID);
    }
    
    public function printReportsRecordPublic($coreID = -3, $full = true)
    {
        if (!$this->isPublishedPublic($GLOBALS["SL"]->coreTbl, $coreID) && !$this->isCoreOwnerPublic($coreID)
            && (!$this->v["user"] || !$this->v["user"]->hasRole('administrator|staff'))) {
            return $this->unpublishedMessage($GLOBALS["SL"]->coreTbl);
        }
        if ($full) {
            return '<div class="reportWrap">' . $this->byID($GLOBALS["SL"]->REQ, $coreID, '', true) . '</div>';
        }
        return $this->printReportsPrev($coreID);
    }
    
    public function printReportsPrev($coreID = -3)
    {
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        return '<div id="reportPreview' . $coreID . '" class="reportPreview">' . $this->printPreviewReport() . '</div>';
    }
    
    public function unpublishedMessage($coreTbl = '')
    {
        return '<div class="well well-lg">#' . $this->corePublicID . ' is no longer published.</div>';
    }
    
    public function xmlAllAccess() { return true; }
    
    public function xmlAll(Request $request)
    {
        $this->survLoopInit($request, '/' . $GLOBALS["SL"]->treeRow->TreeSlug . '-xml-all');
        if (!$this->xmlAllAccess()) return 'Sorry, access not permitted.';
        $this->loadXmlMapTree($request);
        $this->v["nestedNodes"] = '';
        $this->getAllPublicCoreIDs($GLOBALS["SL"]->xmlTree["coreTbl"]);
        if (sizeof($this->allPublicCoreIDs) > 0) {
            foreach ($this->allPublicCoreIDs as $coreID) {
                $this->loadAllSessData($GLOBALS["SL"]->xmlTree["coreTbl"], $coreID);
                if (isset($this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]]) 
                    && sizeof($this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]]) > 0) {
                    $this->v["nestedNodes"] .= $this->genXmlReportNode($this->xmlMapTree->rootID, 
                        $this->xmlMapTree->nodeTiers, $this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]][0]);
                }
            }
        }
        $view = view('vendor.survloop.admin.tree.xml-report', $this->v)->render();
        return Response::make($view, '200')->header('Content-Type', 'text/xml');
    }
    
    public function xmlByID(Request $request, $coreID, $coreSlug = '')
    {
        $this->survLoopInit($request, '/' . $GLOBALS["SL"]->treeRow->TreeSlug . '-report-xml/' . $coreID);
        $coreID = $GLOBALS["SL"]->chkInPublicID($coreID);
        $this->loadXmlMapTree($request);
        if ($GLOBALS["SL"]->xmlTree["coreTbl"] == $GLOBALS["SL"]->coreTbl) {
            $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
//echo 'version A<br />';
//echo '<pre>'; print_r($GLOBALS["SL"]->treeRow); echo '</pre>';
        } else { // XML core table is different from main tree
            $this->loadSessInfo($GLOBALS["SL"]->xmlTree["coreTbl"]);
            $this->loadAllSessData($GLOBALS["SL"]->xmlTree["coreTbl"], $coreID);
//echo 'version B<br />';
        }
//echo '<pre>'; print_r($this->sessData->dataSets); echo '</pre>';
        $this->maxUserView();
        $this->xmlMapTree->v["view"] = $GLOBALS["SL"]->x["pageView"];
        if (isset($GLOBALS["fullAccess"]) && $GLOBALS["fullAccess"] && $GLOBALS["SL"]->x["pageView"] != 'full') {
            $this->v["content"] = $this->errorDeniedFullXml();
            return view('vendor.survloop.master', $this->v);
        }
        return $this->genXmlReport($request);
    }
    
    public function getXmlExample(Request $request)
    {
        $this->survLoopInit($request, '/' . $GLOBALS["SL"]->treeRow->TreeSlug . '-xml-example');
        $coreID = 1;
        if (isset($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->xmlTree["id"] . "-example"])) {
            $coreID = intVal($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->xmlTree["id"] . "-example"]);
        } elseif (isset($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID . "-example"])) {
            $coreID = intVal($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID . "-example"]);
        }
        eval("\$chk = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->xmlTree["coreTbl"]) . "::find(" . $coreID . ");");
        if ($chk) {
            return $this->xmlByID($request, $coreID);
        }
        return $this->redir('/xml-schema');
    }
    
    protected function genRecDump($coreID)
    {
        $this->loadXmlMapTree($GLOBALS["SL"]->REQ);
        if ($GLOBALS["SL"]->xmlTree["coreTbl"] == $GLOBALS["SL"]->coreTbl) {
            $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        } else { // XML core table is different from main tree
            $this->loadSessInfo($GLOBALS["SL"]->xmlTree["coreTbl"]);
            $this->loadAllSessData($GLOBALS["SL"]->xmlTree["coreTbl"], $coreID);
        }
        if (!isset($this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]])) return false;
        $dump = $this->genRecDumpNode($this->xmlMapTree->rootID, $this->xmlMapTree->nodeTiers, 
            $this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]][0]);
        $dump .= $this->genRecDumpXtra();
        //echo 'dump: <textarea style="width: 100%; height: 300px;">' . $dump . '</textarea><br />'; exit;
        $dumpRec = new SLSearchRecDump;
        $dumpRec->SchRecDmpTreeID  = $this->treeID;
        $dumpRec->SchRecDmpRecID   = $coreID;
        $dumpRec->SchRecDmpRecDump = utf8_encode($GLOBALS["SL"]->stdizeChars(str_replace('  ', ' ', trim($dump))));
        $dumpRec->save();
        return true;
    }
    
    public function genRecDumpNode($nID, $nodeTiers, $rec, $overV = [])
    {
        $ret = '';
        $v = $this->getXmlTmpV($nID);
        if (sizeof($v["tblFlds"]) > 0) {
            foreach ($v["tblFlds"] as $i => $fld) {
                $ret .= ' ' . $this->genXmlFormatVal($rec, $fld, $v["tblAbbr"]);
            }
        }
        if (sizeof($v["tblHelp"]) > 0) {
            foreach ($v["tblHelp"] as $help) {
                $nextV = $this->getXmlTmpV(-3, $help);
                $kidRows = $this->sessData->getChildRows($v["tbl"], $rec->getKey(), $nextV["tbl"]);
                if (sizeof($kidRows) > 0) {
                    foreach ($kidRows as $j => $kid) {
                        $ret .= ' ' . $this->genXmlFormatVal($kid, $v["tblHelpFld"][$help], 
                            $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->tbl[$help]]);
                    }
                }
            }
        }
        for ($i = 0; $i < sizeof($nodeTiers[1]); $i++) {
            $tbl2 = $this->xmlMapTree->getNodeTblName($nodeTiers[1][$i][0]);
            $kidRows = $this->sessData->getChildRows($v["tbl"], $rec->getKey(), $tbl2);
            if (sizeof($kidRows) > 0) {
                $nextV = $this->getXmlTmpV($nodeTiers[1][$i][0]);
                foreach ($kidRows as $j => $kid) {
                    $ret .= ' ' . $this->genRecDumpNode($nodeTiers[1][$i][0], $nodeTiers[1][$i], $kid);
                }
            }
        }
        return $ret;
    }
    
    protected function genRecDumpXtra()
    {
        return '';
    }
    
    protected function reloadStats($coreIDs = [])
    {
        return true;
    }
    
    public function retrieveUpload(Request $request, $cid = -3, $upID = '')
    {
        if ($cid <= 0) return '';
        $this->survLoopInit($request, '');
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $cid);
        return $this->retrieveUploadFile($upID);
    }
    
    public function searchBar()
    {
        $this->survLoopInit($request, '/search-bar/' . $this->treeID);
        return $this->printSearchBar();
    }
    
    public function printSearchBar($search = '', $treeID = -3, $pre = '', $post = '', $nID = -3, $ajax = 0)
    {
        if ($treeID <= 0 && $GLOBALS["SL"]->REQ->has('t')) $treeID = intVal($GLOBALS["SL"]->REQ->get('t'));
        $this->getSearchFilts();
        $GLOBALS["SL"]->pageAJAX .= '$("#searchAdvBtn' . $nID . 't' . $treeID . '").click(function() {
            $("#searchAdv' . $nID . 't' . $treeID . '").slideToggle("fast");
        });';
        return view('vendor.survloop.inc-search-bar', [
            "nID"      => $nID, 
            "treeID"   => $treeID, 
            "pre"      => $this->extractJava($pre),
            "post"     => $this->extractJava($post),
            "ajax"     => $ajax,
            "search"   => $this->searchTxt,
            "extra"    => $this->printSearchBarFilters($treeID, $nID),
            "advanced" => $this->printSearchBarAdvanced($treeID, $nID),
            "advUrl"   => $this->advSearchUrlSffx,
            "advBarJS" => $this->advSearchBarJS
        ])->render();
    }
    
    public function chkRecsPub(Request $request, $treeID = -3)
    {
        if ($treeID <= 0) $treeID = $this->treeID;
        if (!session()->has('chkRecsPub') || $request->has('refresh')) {
            $dumped = [];
            if ($request->has('refresh')) {
                $chk = SLSearchRecDump::where('SchRecDmpTreeID', $treeID)->delete();
            } else {
                $chk = SLSearchRecDump::where('SchRecDmpTreeID', $treeID)
                    ->select('SchRecDmpRecID')
                    ->get();
                if ($chk->isNotEmpty()) {
                    foreach ($chk as $rec) $dumped[] = $rec->SchRecDmpRecID;
                }
            }
            if (sizeof($this->allPublicCoreIDs) > 0) {
                foreach ($this->allPublicCoreIDs as $coreID) {
                    if (!in_array($coreID, $dumped)) $this->genRecDump($coreID);
                }
            }
            $this->reloadStats($this->allPublicCoreIDs);
            session()->put('chkRecsPub', 1);
            return true;
        }
        return false;
    }
    
    public function searchResults(Request $request)
    {
        $this->loadTree();
        $this->getAllPublicCoreIDs();
//echo 'A allPublicCoreIDs:<pre>'; print_r($this->allPublicCoreIDs); echo '</pre>';
        $this->chkRecsPub($request);
        $this->getSearchFilts();
        $cacheName = '/search?t=' . $this->treeID . $this->searchFiltsURL() 
            . '&s=' . $this->searchTxt . $this->advSearchUrlSffx;
        $this->survLoopInit($request, $cacheName);
        
        // [ check for cache ]
        
        $ret = $this->searchResultsOverride($this->treeID);
        if (trim($ret) != '') return $ret;
        $this->processSearchFilts();
        if (trim($this->searchTxt) == '') {
            if (sizeof($this->allPublicFiltIDs) > 0) {
                foreach ($this->allPublicFiltIDs as $id) $this->addSearchResult($id);
            }
        } else {
            $chk = SLSearchRecDump::where('SchRecDmpTreeID', $this->treeID)
                ->whereIn('SchRecDmpRecID', $this->allPublicFiltIDs)
                ->where('SchRecDmpRecDump', 'LIKE', '%' . $this->searchTxt . '%')
                ->orderBy('SchRecDmpRecID', 'desc')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $rec) $this->addSearchResult($rec->SchRecDmpRecID);
            }
        }
        if (sizeof($this->searchResults) > 0) {
            $printed = [];
            while (sizeof($printed) < sizeof($this->searchResults)) {
                $currMax = -1000000;
                foreach ($this->searchResults as $r) {
                    if ($currMax < $r[1] && !in_array($r[0], $printed)) $currMax = $r[1];
                }
                foreach ($this->searchResults as $r) {
                    if ($currMax == $r[1] && !in_array($r[0], $printed)) {
                        $printed[] = $r[0];
                        if (!isset($this->searchOpts["limit"]) || sizeof($printed) < $this->searchOpts["limit"]) {
                            $ret .= $r[2];
                        }
                    }
                }
            }
        } else {
            $ret .= $this->searchResultsNone($this->treeID);
        }
        return $ret;
    }
    
    protected function addSearchResult($recID = -3, $weight = 1, $preview = '')
    {
        if ($recID > 0) {
            if (sizeof($this->searchResults) > 0) {
                foreach ($this->searchResults as $i => $r) {
                    if ($r[0] == $recID) {
                        $this->searchResults[$i][1] += $weight;
                        return false;
                    }
                }
            }
            if (trim($preview) == '') {
                $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $recID);
//echo 'loadedSessData(' . $recID . '<pre>'; print_r($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]); echo '</pre>';
                $preview = '<div class="reportPreview">' . $this->printPreviewReport() . '</div>';
                if (isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl])
                    && sizeof($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl]) > 0
                    && isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->created_at)) {
                    $dateWeight = strtotime($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->created_at);
                    $weight += $dateWeight/1000000000000;
                }
            }
            $this->searchResults[] = [$recID, $weight, $preview];
        }
        return true;
    }
    
    public function searchResultsOverride($treeID = 1)
    {
        return '';
    }
    
    public function searchResultsXtra($treeID = -3)
    {
        return true;
    }
    
    public function searchResultsNone($treeID = 1)
    {
        return '<div class="jumbotron"><h4><i>No records were found matching your search.</i></h4></div>';
    }
    
    public function searchResultsFeatured($treeID = 1)
    {
        return '';
    }
    
    public function printSearchBarFilters($treeID = -3, $nID = -3)
    {
        return '';
    }
    
    public function printSearchBarAdvanced($treeID = -3, $nID = -3)
    {
        return '';
    }
    
    protected function getSearchFilts()
    {
        if (!$this->checkedSearch) {
            $this->checkedSearch = true;
            $this->searchTxt = '';
            if ($GLOBALS["SL"]->REQ->has('s') && trim($GLOBALS["SL"]->REQ->get('s')) != '') {
                $this->searchTxt = trim($GLOBALS["SL"]->REQ->get('s'));
            }
            $this->searchParse = $GLOBALS["SL"]->parseSearchWords($this->searchTxt);
            $this->searchFilts = $this->searchOpts = [];
            if ($GLOBALS["SL"]->REQ->has('d') && trim($GLOBALS["SL"]->REQ->get('d')) != '') {
                $this->searchFilts["d"] = $GLOBALS["SL"]->mexplode(',', $GLOBALS["SL"]->REQ->get('d'));
            }
            if ($GLOBALS["SL"]->REQ->has('f') && trim($GLOBALS["SL"]->REQ->get('f')) != '') {
                $this->searchFilts["f"] = $GLOBALS["SL"]->mexplode('__', $GLOBALS["SL"]->REQ->get('f'));
            }
            if ($GLOBALS["SL"]->REQ->has('u') && intVal($GLOBALS["SL"]->REQ->get('u')) > 0) {
                $this->searchFilts["user"] = intVal($GLOBALS["SL"]->REQ->get('u'));
            } elseif ($GLOBALS["SL"]->REQ->has('mine') && intVal($GLOBALS["SL"]->REQ->get('mine')) == 1) {
                $this->searchFilts["user"] = $this->v["uID"];
            }
            if ($GLOBALS["SL"]->REQ->has('limit') && trim($GLOBALS["SL"]->REQ->get('limit')) != '') {
                $this->searchOpts["limit"] = intVal($GLOBALS["SL"]->REQ->get('limit'));
            }
            $GLOBALS["SL"]->loadStates();
            if ($GLOBALS["SL"]->REQ->has('state') && trim($GLOBALS["SL"]->REQ->get('state')) != '') {
                if (!isset($GLOBALS["SL"]->states->stateList[trim($GLOBALS["SL"]->REQ->get('state'))])) {
                    $this->searchOpts["state"] = trim($GLOBALS["SL"]->REQ->get('state'));
                }
            }
            $this->searchResultsXtra($this->treeID);
            $this->printSearchBarAdvanced($this->treeID);
        }
        return true;
    }
    
    protected function processSearchFilts()
    {
        //if (sizeof($this->allPublicFiltIDs) > 0) return true;
        $this->getAllPublicCoreIDs();
        $this->allPublicFiltIDs = $this->allPublicCoreIDs;
//echo 'allPublicCoreIDs:<pre>'; print_r($this->allPublicCoreIDs); echo '</pre>';
//echo 'processSearchFilts() ' . $GLOBALS["SL"]->getCoreTblUserFld() . ' <pre>'; print_r($this->searchFilts); echo '</pre>';
        if (sizeof($this->searchFilts) > 0) {
            $coreAbbr = $GLOBALS["SL"]->coreTblAbbr();
            foreach ($this->searchFilts as $key => $val) {
                if ($key == 'user' && intVal($val) > 0) {
                    eval("\$chk = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) . "::whereIn('" . $coreAbbr 
                        . (($GLOBALS["SL"]->tblHasPublicID($GLOBALS["SL"]->coreTbl)) ? "Public" : "") 
                        . "ID', \$this->allPublicFiltIDs)->where('" . $GLOBALS["SL"]->getCoreTblUserFld() . "', "
                        . $val . ")->select('" . $coreAbbr . "ID')->get();");
                    $this->allPublicFiltIDs = [];
                    if ($chk->isNotEmpty()) {
                        foreach ($chk as $lnk) $this->allPublicFiltIDs[] = $lnk->getKey();
                    }
                } elseif ($key == 'f') {
                    if (sizeof($val) > 0) {
                        foreach ($val as $v) {
                            list($fldID, $value) = explode('|', $v);
                            $this->allPublicFiltIDs = $GLOBALS["SL"]->processFiltFld($fldID, $value, 
                                $this->allPublicFiltIDs);
                        }
                    }
                } else {
                    $this->processSearchFilt($key, $val);
                }
            }
        }
        $this->processSearchAdvanced();
        return true;
    }
    
    protected function processSearchFilt($key, $val)
    {
        return true;
    }
    
    protected function processSearchAdvanced()
    {
        return true;
    }
     
    protected function searchFiltsURL()
    {
        $ret = '';
        if (sizeof($this->searchFilts) > 0) {
            foreach ($this->searchFilts as $key => $val) {
                $paramVal = $val;
                if (is_array($paramVal) && sizeof($paramVal)) {
                  $paramVal = '';
                  foreach ($val as $i => $p) {
                      $paramVal .= (($i > 0) ? ',' : '') . urlencode($p);
                  }
                }
                $ret .= '&' . $key . '=' . $paramVal;
            }
        }
        if (sizeof($this->searchOpts) > 0) {
            foreach ($this->searchOpts as $key => $val) $ret .= '&' . $key . '=' . $val;
        }
        return $ret . $this->searchFiltsURLXtra();
    }
    
    protected function searchFiltsURLXtra()
    {
        return '';
    }
    
    public function ajaxGraph(Request $request, $gType = '', $nID = -3)
    {
        $this->survLoopInit($request, '');
        $this->v["currNode"] = new SurvLoopNode;
        $this->v["currNode"]->fillNodeRow($nID);
        $this->v["currGraphID"] = 'nGraph' . $nID;
        if ($this->v["currNode"] && isset($this->v["currNode"]->nodeRow->NodeID) && trim($gType) != '') {
            $this->getAllPublicCoreIDs();
            $this->getSearchFilts();
            $this->processSearchFilts();
            $this->v["graphDataPts"] = $this->v["graphMath"] = $rows = $rowsFilt = [];
            if (sizeof($this->allPublicFiltIDs) > 0) {
                if (isset($this->v["currNode"]->extraOpts["y-axis"]) 
                    && intVal($this->v["currNode"]->extraOpts["y-axis"]) > 0) {
                    $fldRec = SLFields::find($this->v["currNode"]->extraOpts["y-axis"]);
                    $lab1Rec = SLFields::find($this->v["currNode"]->extraOpts["lab1"]);
                    $lab2Rec = SLFields::find($this->v["currNode"]->extraOpts["lab2"]);
                    if ($fldRec && isset($fldRec->FldTable)) {
                        $tbl = $GLOBALS["SL"]->tbl[$fldRec->FldTable];
                        $tblAbbr = $GLOBALS["SL"]->tblAbbr[$tbl];
                        $fldName = $tblAbbr . $fldRec->FldName;
                        $lab1Fld = (($lab1Rec && isset($lab1Rec->FldName)) ? $tblAbbr . $lab1Rec->FldName : '');
                        $lab2Fld = (($lab2Rec && isset($lab2Rec->FldName)) ? $tblAbbr . $lab2Rec->FldName : '');
                        if ($tbl == $GLOBALS["SL"]->coreTbl) {
                            eval("\$rows = " . $GLOBALS["SL"]->modelPath($tbl) . "::select('" . $tblAbbr . "ID', '" 
                                . $fldName . "'" . ((trim($lab1Fld) != '') ? ", '" . $lab1Fld . "'" : "") 
                                . ((trim($lab2Fld) != '') ? ", '" . $lab2Fld . "'" : "") . ")->where('" . $fldName 
                                . "', 'NOT LIKE', '')->where('" . $fldName . "', 'NOT LIKE', 0)->whereIn('" . $tblAbbr 
                                . "ID', \$this->allPublicFiltIDs)->orderBy('" . $fldName . "', 'asc')->get();");
                        } else {
                            //eval("\$rows = " . $GLOBALS["SL"]->modelPath($tbl) . "::orderBy('" . $isBigSurvLoop[1] 
                            //    . "', '" . $isBigSurvLoop[2] . "')->get();");
                        }
                        if ($rows->isNotEmpty()) {
                            if (isset($this->v["currNode"]->extraOpts["conds"]) 
                                && strpos('#', $this->v["currNode"]->extraOpts["conds"]) !== false) {
                                $this->loadCustReport($request);
                                foreach ($rows as $i => $row) {
                                    $this->CustReport->loadAllSessData($GLOBALS["SL"]->coreTbl, $row->getKey());
                                    if ($this->CustReport->chkConds($this->v["currNode"]->extraOpts["conds"])) {
                                        $rowsFilt[] = $row;
                                    }
                                }
                            } else {
                                $rowsFilt = $rows;
                            }
                        }
                        if (sizeof($rowsFilt) > 0) {
                            if ($this->v["currNode"]->nodeType == 'Bar Graph') {
                                $this->v["graphMath"]["absMin"] = $rows[0]->{ $fldName };
                                $this->v["graphMath"]["absMax"] = $rows[sizeof($rows)-1]->{ $fldName };
                                $this->v["graphMath"]["absRange"] 
                                    = $this->v["graphMath"]["absMax"]-$this->v["graphMath"]["absMin"];
                                foreach ($rows as $i => $row) {
                                    $lab = '';
                                    if (trim($lab1Fld) != '' && isset($row->{ $lab1Fld })) { 
                                       $lab .= (($lab1Rec->FldType == 'DOUBLE') 
                                           ? $GLOBALS["SL"]->sigFigs($row->{ $lab1Fld }) : $row->{ $lab1Fld }) . ' ';
                                       if (trim($lab2Fld) != '' && isset($row->{ $lab2Fld })) { 
                                           $lab .= (($lab2Rec->FldType == 'DOUBLE') 
                                               ? $GLOBALS["SL"]->sigFigs($row->{ $lab2Fld }) : $row->{ $lab2Fld }) .' ';
                                       }
                                    }
                                    $perc = ((1+$i)/sizeof($rows));
                                    $this->v["graphDataPts"][] = [
                                        "id"  => $row->getKey(),
                                        "val" => (($fldRec->FldType == 'DOUBLE') 
                                            ? $GLOBALS["SL"]->sigFigs($row->{ $fldName }, 4) : $row->{ $fldName }), 
                                        "lab" => trim($lab),
                                        "dsc" => '',
                                        "bg"  => $GLOBALS["SL"]->printColorFade( $perc, 
                                            $this->v["currNode"]->extraOpts["clr1"], 
                                            $this->v["currNode"]->extraOpts["clr2"], 
                                            $this->v["currNode"]->extraOpts["opc1"], 
                                            $this->v["currNode"]->extraOpts["opc2"] ), 
                                        "brd" => $GLOBALS["SL"]->printColorFade( $perc, 
                                            $this->v["currNode"]->extraOpts["clr1"], 
                                            $this->v["currNode"]->extraOpts["clr2"] )
                                        ];
                                }
                            }
                        }
                    }
                }
                $this->v["graph"] = [ "dat" => '', "lab" => '', "bg" => '', "brd" => '' ];
                if (sizeof($this->v["graphDataPts"]) > 0) {
                    foreach ($this->v["graphDataPts"] as $cnt => $dat) {
                        $cma = (($cnt > 0) ? ", " : "");
                        $this->v["graph"]["dat"] .= $cma . $dat["val"];
                        $this->v["graph"]["lab"] .= $cma . "\"" . $dat["lab"] . "\"";
                        $this->v["graph"]["bg"]  .= $cma . "\"" . $dat["bg"]  . "\"";
                        $this->v["graph"]["brd"] .= $cma . "\"" . $dat["brd"] . "\"";
                    }
                }
                return view('vendor.survloop.graph-bar', $this->v);
            }
        }
        $this->v["graphFail"] = true;
        return view('vendor.survloop.graph-bar', $this->v);
    }
    
    public function printAdminReport($coreID)
    {
        $this->v["cID"] = $coreID;
        return $this->printFullReport('', true);
    }
    
    public function printFullReport($reportType = '', $isAdmin = false, $inForms = false)
    {
        return '';
    }
    
    public function printPreviewReportCustom($isAdmin = false) { return ''; }
    
    public function printPreviewReport($isAdmin = false)
    {
        $ret = $this->printPreviewReportCustom($isAdmin);
        if (trim($ret) != '') return $ret;
        $fldNames = $found = [];
        if (sizeof($this->nodesRawOrder) > 0) {
            foreach ($this->nodesRawOrder as $i => $nID) {
                if (isset($this->allNodes[$nID]) && $this->allNodes[$nID]->isRequired()) {
                    $tblFld = $this->allNodes[$nID]->getTblFld();
                    $fldNames[] = [ $tblFld[0], $tblFld[1] ];
                }
            }
        }
        if (sizeof($fldNames) > 0) {
            foreach ($fldNames as $i => $fld) {
                if (isset($this->sessData->dataSets[$fld[0]]) && sizeof($this->sessData->dataSets[$fld[0]]) > 0
                    && isset($this->sessData->dataSets[$fld[0]][0]->{ $fld[1] }) && sizeof($found) < 6) {
                    $found[] = $fld[1];
                    $ret .= '<span class="mR20">' . $this->sessData->dataSets[$fld[0]][0]->{ $fld[1] } . '</span>';
                }
            }
        }
        return $ret;
    }
    
    public function previewReportPubPri($inForms = false)
    {
        $previewPublic = '<div id="prevRprtPub' . $this->currNode() . '" class="w100"></div>';
        $previewPrivate = str_replace('prevRprtPub', 'prevRprtPrv', $previewPublic);
        if (isset($GLOBALS["SL"]->reportTree["slug"])) {
            $GLOBALS["SL"]->pageAJAX .= 'function chkReportPreviews() {
                var src = "/' . $GLOBALS["SL"]->reportTree["slug"] . '/u-' . $this->currNode() 
                    . '/?ajax=1&hideDisclaim=1";
                if (document.getElementById("prevRprtPub' . $this->currNode() . '")) {
                    $("#prevRprtPub' . $this->currNode() . '").load(src);
                }
                if (document.getElementById("prevRprtPrv' . $this->currNode() . '")) {
                    $("#prevRprtPrv' . $this->currNode() . '").load(src+"&prv=1");
                }
            } setTimeout(function() { chkReportPreviews(); }, 500);';
        } else {
            eval("\$treeClassReport = new " . $this->loadLoopReportClass() . "(\$this->REQ, \$this->coreID);");
            $treeClassReport->loadSessionData($GLOBALS["SL"]->coreTbl, $this->coreID);
            $treeClassReport->hideDisclaim = true;
            $previewPublic = $treeClassReport->printFullReport('Public');
            $previewPrivate = $treeClassReport->printFullReport('Investigate');
        }
        return [$previewPublic, $previewPrivate];
    }
    
    public function wordLimitDotDotDot($str, $wordLimit = 50)
    {
        $strs = $GLOBALS["SL"]->mexplode(' ', $str);
        if (sizeof($strs) <= $wordLimit) return $str;
        $ret = '';
        for ($i=0; $i<$wordLimit; $i++) $ret .= $strs[$i] . ' ';
        return $ret . '...';
    }
    
    public function ajaxEmojiTag(Request $request, $recID = -3, $defID = -3)
    {
        if ($recID <= 0) return '';
        $this->survLoopInit($request, '');
        if ($this->v["uID"] <= 0) return '<h4><i>Please <a href="/login">Login</a></i></h4>';
        $this->loadSessionData($GLOBALS["SL"]->coreTbl, $recID);
        $this->loadEmojiTags($defID);
        if (sizeof($GLOBALS["SL"]->treeSettings['emojis']) > 0 && $recID > 0) {
            foreach ($GLOBALS["SL"]->treeSettings['emojis'] as $i => $emo) {
                if ($emo["id"] == $defID) {
                    if (isset($this->emojiTagUsrs[$emo["id"]]) 
                        && in_array($this->v["uID"], $this->emojiTagUsrs[$emo["id"]])) {
                        SLSessEmojis::where('SessEmoRecID', $this->coreID)
                            ->where('SessEmoDefID', $emo["id"])
                            ->where('SessEmoTreeID', $this->treeID)
                            ->where('SessEmoUserID', $this->v["uID"])
                            ->delete();
                        $this->emojiTagOff($emo["id"]);
                    } else {
                        $newTag = new SLSessEmojis;
                        $newTag->SessEmoRecID  = $this->coreID;
                        $newTag->SessEmoDefID  = $emo["id"];
                        $newTag->SessEmoTreeID = $this->treeID;
                        $newTag->SessEmoUserID = $this->v["uID"];
                        $newTag->save();
                        $this->emojiTagOn($emo["id"]);
                    }
                }
            }
            $this->loadEmojiTags($defID);
        }
        $isActive = false;
        if (isset($GLOBALS["SL"]->treeSettings['emojis']) && sizeof($GLOBALS["SL"]->treeSettings["emojis"]) > 0) {
            foreach ($GLOBALS["SL"]->treeSettings["emojis"] as $emo) {
                if ($emo["id"] == $defID) {
                    if ($this->v["uID"] > 0 && isset($this->emojiTagUsrs[$defID])
                        && in_array($this->v["uID"], $this->emojiTagUsrs[$defID])) $isActive = true;
                    return view('vendor.survloop.inc-emoji-tag', [
                        "spot"     => 't' . $this->treeID . 'r' . $this->coreID, 
                        "emo"      => $emo, 
                        "cnt"      => sizeof($this->emojiTagUsrs[$defID]),
                        "isActive" => $isActive
                    ])->render();
                }
            }
        }
        return '';
    }
    
    public function emojiTagOn($defID = -3)
    {
        return true;
    }
    
    public function emojiTagOff($defID = -3)
    {
        return true;
    }
    
    protected function loadEmojiTags($defID = -3)
    {
        if (isset($GLOBALS["SL"]->treeSettings['emojis']) 
            && sizeof($GLOBALS["SL"]->treeSettings['emojis']) > 0 && $this->coreID > 0) {
            foreach ($GLOBALS["SL"]->treeSettings['emojis'] as $emo) {
                if ($defID <= 0 || $emo["id"] == $defID) {
                    $this->emojiTagUsrs[$emo["id"]] = [];
                    $chk = SLSessEmojis::where('SessEmoRecID', $this->coreID)
                        ->where('SessEmoDefID', $emo["id"])
                        ->where('SessEmoTreeID', $this->treeID)
                        ->get();
                    if ($chk->isNotEmpty()) {
                        foreach ($chk as $tag) $this->emojiTagUsrs[$emo["id"]][] = $tag->SessEmoUserID;
                    }
                }
            }
        }
        return true;
    }
    
    protected function printEmojiTags()
    {
        $ret = '';
        $this->loadEmojiTags();
        if (isset($GLOBALS["SL"]->treeSettings['emojis']) && sizeof($GLOBALS["SL"]->treeSettings['emojis']) > 0 
            && $this->coreID > 0) {
            $admPower = ($this->v["user"] && $this->v["user"]->hasRole('administrator|staff'));
            $spot = 't' . $this->treeID . 'r' . $this->coreID;
            foreach ($GLOBALS["SL"]->treeSettings['emojis'] as $emo) {
                if (!$emo["admin"] || $admPower) {
                    $GLOBALS["SL"]->pageAJAX .= '$(document).on("click", "#' . $spot . 'e' . $emo["id"] 
                        . '", function() { $("#' . $spot . 'e' . $emo["id"] . 'Tag").load("/ajax-emoji-tag/' 
                        . $this->treeID . '/' . $this->coreID . '/' . $emo["id"] . '/"); });' . "\n";
                }
            }
            $ret .= view('vendor.survloop.inc-emoji-tags', [
                "spot"     => $spot, 
                "emojis"   => $GLOBALS["SL"]->treeSettings["emojis"], 
                "users"    => $this->emojiTagUsrs,
                "uID"      => (($this->v["uID"] > 0) ? $this->v["uID"] : -3),
                "admPower" => $admPower
            ])->render();
        }
        return $ret;
    }
    
    protected function fillGlossary()
    {
        $this->v["glossaryList"] = [];
        return true;
    }
    
    protected function printGlossary()
    {
        if (sizeof($this->v["glossaryList"]) > 0) {
            $ret = '<h3 class="mT0 mB20 slBlueDark">Glossary of Terms</h3><div class="glossaryList">';
            foreach ($this->v["glossaryList"] as $i => $gloss) {
                $ret .= '<div class="row' . (($i%2 == 0) ? ' row2' : '') . '"><div class="col-2 pT15 pB15">' 
                    . $gloss[0] . '</div><div class="col-10 pT15 pB15">' . ((isset($gloss[1])) ? $gloss[1] : '') 
                    . '</div></div>';
            }
            return $ret . '</div>';
        }
        return '';
    }
    
    protected function swapSeo($str)
    {
        return str_replace('[coreID]', $this->corePublicID, str_replace('[cID]', $this->corePublicID, 
            str_replace('#1111', '#' . $this->corePublicID, $str)));
    }
    
    protected function runPageLoad($nID)
    {
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page' && $GLOBALS["SL"]->treeRow->TreeOpts%13 == 0) { // report
            $GLOBALS["SL"]->sysOpts['meta-title'] = $this->swapSeo($GLOBALS["SL"]->sysOpts['meta-title']);
            $GLOBALS["SL"]->sysOpts['meta-desc'] = $this->swapSeo($GLOBALS["SL"]->sysOpts['meta-desc']);
        }
        return true;
    }
    
    protected function runPageExtra($nID) { return true; }
    
    protected function printNodePageFoot()
    {
        return (isset($GLOBALS["SL"]->sysOpts["footer-master"]) ? $GLOBALS["SL"]->sysOpts["footer-master"] : '');
    }
    
    protected function printCurrRecMgmt()
    {
        $recDesc = '';
        if (isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl]) 
            && sizeof($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl]) > 0) {
            $recDesc = trim($this->getTableRecLabel($GLOBALS["SL"]->coreTbl, 
                $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]));
        }
        return view('vendor.survloop.formfoot-record-mgmt', [
            "coreID"          => $this->coreID,
            "treeID"          => $this->treeID,
            "multipleRecords" => $this->v["multipleRecords"],
            "isUser"          => ($this->v["uID"] > 0),
            "recDesc"         => $recDesc
            ])->render();
    }
    
    
    // This function is the primary front-facing controller for the user experience
    public function index(Request $request, $type = '', $val = '')
    {
        $this->survLoopInit($request, '');
        $chk = $this->checkSystemInit();
        if (trim($chk) != '') return $chk;
        // Basic System Is Setup, Check for User Intercept From Index
        if ($GLOBALS["SL"]->treeIsAdmin) {
            if ($this->v["uID"] <= 0 || ($GLOBALS["SL"]->treeRow->TreeOpts%3 == 0 
                    && !$this->v["user"]->hasRole('administrator')) 
                || ($GLOBALS["SL"]->treeRow->TreeOpts%17 == 0 
                    && !$this->v["user"]->hasRole('administrator|staff|databaser|volunteer'))
                || ($GLOBALS["SL"]->treeRow->TreeOpts%41 == 0 
                    && !$this->v["user"]->hasRole('administrator|staff|databaser|partner'))
                || ($GLOBALS["SL"]->treeRow->TreeOpts%43 == 0 
                    && !$this->v["user"]->hasRole('administrator|staff|databaser'))) {
                return $this->redir('/login');
            }
        }
        $this->checkPageViewPerms();
        if ($GLOBALS["SL"]->treeRow->TreeOpts%13 == 0) $this->fillGlossary(); // is report
        //if ($this->v["uID"] > 0) $this->loadAllSessData();
        if ($type == 'ajaxChecks') {
            $this->runAjaxChecks($request);
            exit;
        }
        $this->v["content"] = $this->printTreePublic()
            . ((isset($GLOBALS["SL"]->x["pageView"]) && trim($GLOBALS["SL"]->x["pageView"]) != '') 
                ? '<!-- pv.' . $GLOBALS["SL"]->x["pageView"] . ' dp.' . $GLOBALS["SL"]->x["dataPerms"] . ' -->' : '');
        if ($this->v["currPage"][0] != '/') {
            $log = new SLUsersActivity;
            $log->UserActUser = $this->v["uID"];
            $log->UserActCurrPage = $this->v["currPage"][0] . '.pv:' . $GLOBALS["SL"]->x["pageView"] . '.dp:' 
                . $GLOBALS["SL"]->x["dataPerms"];
            $log->save();
        }
        if ($request->has('ajax') && $request->ajax == 1) { // tree form ajax submission
            echo $this->v["content"];
            exit;
        }
        // Otherwise, Proceed Running Various Index Functions
        $this->v["currInReport"] = $this->currInReport();
        if ($type == 'testRun') return $this->redir('/');
        
        if ($GLOBALS["SL"]->treeRow->TreeOpts%31 == 0) { // search results page
            if ($GLOBALS["SL"]->REQ->has('s') && trim($GLOBALS["SL"]->REQ->get('s')) != '') {
                if ($GLOBALS["SL"]->treeRow->TreeOpts%3 == 0 || $GLOBALS["SL"]->treeRow->TreeOpts%17 == 0 
                    || $GLOBALS["SL"]->treeRow->TreeOpts%41 == 0 || $GLOBALS["SL"]->treeRow->TreeOpts%43 == 0) {
                    $GLOBALS["SL"]->pageJAVA .= 'setTimeout(\'if (document.getElementById("admSrchFld")) '
                        . 'document.getElementById("admSrchFld").value=' 
                        . json_encode(trim($GLOBALS["SL"]->REQ->get('s'))) . '\', 10); ';
                } // else check for the main public search field? 
            }
        }
        
        if ($GLOBALS["SL"]->treeIsAdmin) return $GLOBALS["SL"]->swapSessMsg($this->v["content"]);
        else return $GLOBALS["SL"]->swapSessMsg(view('vendor.survloop.master', $this->v)->render());
    }
    
    protected function hasAncestPrintBlock($nID)
    {
        $found = false;
        if (isset($this->allNodes[$nID]) && isset($this->allNodes[$nID]->parentID)) {
            $parent = $this->allNodes[$nID]->parentID;
            while ($parent > 0 && isset($this->allNodes[$parent]) && isset($this->allNodes[$parent]->parentID)) {
                if ($this->allNodes[$parent]->isDataPrint()) $found = true;
                $parent = $this->allNodes[$parent]->parentID;
            }
        }
        return $found;
    }
    
    /******************************************************************************************************
    
    MAIN PUBLIC OUTPUT WHERE EVERYTHING HAPPENS: print public version of currNode
    
    ******************************************************************************************************/
    
    protected function hasAjaxWrapPrinting()
    {
        return (!$this->hasREQ && (!$GLOBALS["SL"]->REQ->has('ajax') || intVal($GLOBALS["SL"]->REQ->get('ajax')) == 0));
    }
    
    protected function hasFrameLoad()
    {
        return ($GLOBALS["SL"]->REQ->has('frame') && intVal($GLOBALS["SL"]->REQ->get('frame')) == 1);
    }
    
    public function printTreePublic()
    {
        $ret = '';
        $this->loadTree();
        
        if ($this->hasAjaxWrapPrinting()) $ret .= '<div id="ajaxWrap">';
        $ret .= '<a name="maincontent" id="maincontent"></a>' . "\n";
        if ($GLOBALS["SL"]->treeRow->TreeType != 'Page') {
            $ret .= '<div id="maincontentWrap" style="display: none;">' . "\n";
        }
        if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('node') && $GLOBALS["SL"]->REQ->input('node') > 0) {
            $this->updateCurrNode($GLOBALS["SL"]->REQ->input('node'));
        }
        $lastNode = $this->currNode();
        if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('superHardJump')) {
            $this->updateCurrNode(intVal($GLOBALS["SL"]->REQ->superHardJump));
        }
        
        if ($this->currNode() < 0 || !isset($this->allNodes[$this->currNode()])) {
            $this->updateCurrNode($GLOBALS["SL"]->treeRow->TreeRoot);
            //return '<h1>Sorry, Page Not Found.</h1>';
        }
        
        // double-check we haven't landed on a mid-page node
        if (isset($this->allNodes[$this->currNode()]) && !$this->allNodes[$this->currNode()]->isPage() 
            && !$this->allNodes[$this->currNode()]->isLoopRoot()) {
            $this->updateCurrNode($this->allNodes[$this->currNode()]->getParent());
        }
        
        $this->loadAncestry($this->currNode());
        
        if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('step')) {
            // Process form POST for all nodes, then store the data updates...
            if ($this->REQstep == 'autoSave' 
                && (!$GLOBALS["SL"]->REQ->has('chgCnt') || intVal($GLOBALS["SL"]->REQ->get('chgCnt')) <= 0)) {
                return 'No changes found to auto-save. <3';
            }
            $logTitle = 'PAGE SAVE' . (($this->REQstep == 'autoSave') ? ' AUTO' : '');
            $this->sessData->logDataSave($GLOBALS["SL"]->REQ->node, $logTitle, -3, '', '');
            $ret .= $this->postNodePublic($GLOBALS["SL"]->REQ->node);
            if ($this->REQstep == 'autoSave') return 'Saved!-)';
            $this->loadAllSessData();
//$this->tmpDebug('umm3');
            if ($GLOBALS["SL"]->treeRow->TreeType != 'Page') {
                if ($this->REQstep == 'save') {
                    if ($GLOBALS["SL"]->REQ->has('popStateUrl') && trim($GLOBALS["SL"]->REQ->popStateUrl) != '') {
                        $this->setNodeURL(str_replace($GLOBALS["SL"]->treeBaseSlug, '', 
                            $GLOBALS["SL"]->REQ->popStateUrl));
                        $this->pullNewNodeURL();
                    } else {
                        $redir1 = '';
                        if ($GLOBALS["SL"]->REQ->has('jumpTo') && trim($GLOBALS["SL"]->REQ->get('jumpTo')) != '') {
                            $redir1 = trim($GLOBALS["SL"]->REQ->get('jumpTo'));
                        }
                        if ($GLOBALS["SL"]->REQ->has('afterJumpTo') 
                            && trim($GLOBALS["SL"]->REQ->get('afterJumpTo')) != '') {
                            session()->put('redir2', trim($GLOBALS["SL"]->REQ->get('afterJumpTo')));
                        }
                        if ($redir1 != '') return $this->redir($redir1, true);
                    }
                } else {
                    $this->updateCurrNode($GLOBALS["SL"]->REQ->node);
                    $lastNode = $GLOBALS["SL"]->REQ->node;
                    // Now figure what comes next. 
                    if (!$this->isStepUpload()) { // if uploading, then don't change nodes yet
                        $jumpID = $this->jumpToNode($this->currNode());
                        if (in_array($this->REQstep, ['exitLoop', 'exitLoopBack', 'exitLoopJump']) 
                            && trim($GLOBALS["SL"]->REQ->input('loop')) != '') {
                            $this->sessData->logDataSave($this->currNode(), 
                                $GLOBALS["SL"]->closestLoop["obj"]->DataLoopTable, 
                                $GLOBALS["SL"]->REQ->input('loopItem'), $this->REQstep, $GLOBALS["SL"]->REQ->input('loop'));
                            $this->leavingTheLoop($GLOBALS["SL"]->REQ->input('loop'));
                            if ($this->REQstep == 'exitLoop') {
                                $this->updateCurrNodeNB($this->nextNodeSibling($this->currNode()));
                            } elseif ($this->REQstep == 'exitLoopBack') {
                                $prev = $this->getNextNonBranch($this->prevNode($this->currNode()), 'prev');
                                $this->updateCurrNodeNB($prev, 'prev');
                            } else {
                                $this->updateCurrNode($jumpID); // exit through jump
                            }
                        } elseif ($jumpID > 0) {
                            $this->updateCurrNode($jumpID);
                        } else { // no jumps, let's do the old back and forth...
                            if ($this->REQstep == 'back') {
                                $prev = $this->getNextNonBranch($this->prevNode($this->currNode()), 'prev');
                                $this->updateCurrNodeNB($prev, 'prev');
                            } else {
                                $this->updateCurrNodeNB($this->nextNode($this->currNode(), $this->currNodeSubTier));
                            }
                        }
                    }
                } // end REQstep == 'save' check
            }
        } elseif (trim($this->urlSlug) != '') {
            $this->pullNewNodeURL();
            if ($this->currNode() == $GLOBALS["SL"]->treeRow->TreeFirstPage 
                && $GLOBALS["SL"]->REQ->has('start') && intVal($GLOBALS["SL"]->REQ->get('start')) == 1) {
                $this->runDataManip($GLOBALS["SL"]->treeRow->TreeRoot);
            }
        }
        
        if (!$this->isStepUpload()) {
            $this->updateCurrNodeNB($this->currNode());
            if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('step')) {
                $this->loadAllSessData();
                $this->checkLoopsPostProcessing($this->currNode(), $lastNode);
            } else {
                if (!$this->checkNodeConditions($this->currNode())) {
                    $this->updateCurrNode($this->nextNode($this->currNode(), $this->currNodeSubTier));
                }
                $this->updateCurrNodeNB($this->currNode());
            }
            //$this->loadAllSessData();
        }
        /* if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('step')) {
            $newNodeURL = $this->currNodeURL();
            if ($newNodeURL != '') {
                echo '<script type="text/javascript"> window.location="' . $newNodeURL . '"; </script>';
                exit;
            }
        } */
        
//echo '<pre>'; print_r($this->sessData->id2ind['PSAreas']); echo '</pre>';

        if (!$GLOBALS["SL"]->REQ->has('popStateUrl') || trim($GLOBALS["SL"]->REQ->popStateUrl) == '') {
            $this->pushCurrNodeURL($this->currNode());
        }
        $this->multiRecordCheck();
        //$this->getPrintSpecs($this->currNode(), $this->currNodeSubTier);
        
        $this->loadAncestry($this->currNode());
        
        $this->v["nodeKidFunks"] = '';
        $goSkinny = (!$this->hasFrameLoad() && $GLOBALS["SL"]->treeRow->TreeType != 'Page' 
            && $GLOBALS["SL"]->treeRow->TreeOpts%23 > 0);
        if ($goSkinny) $ret .= '<center><div id="skinnySurv" class="treeWrapForm">';
        $ret .= ((trim($GLOBALS["errors"]) != '') ? $GLOBALS["errors"] : '') . $this->nodeSessDump('pageStart')
            . $this->printNodePublic($this->currNode(), $this->currNodeSubTier) . "\n"
            . $this->loadProgBar() . "\n"
                // (($this->allNodes[$this->currNode()]->nodeOpts%29 > 0) ? $this->loadProgBar() : '') // not exit page?
            . $this->printCurrRecMgmt() . $this->sessDump($lastNode) . "\n";
        if ($goSkinny) $ret .= '</div></center>';
        if (isset($GLOBALS["SL"]->treeSettings["footer"]) && isset($GLOBALS["SL"]->treeSettings["footer"][0]) 
            && trim($GLOBALS["SL"]->treeSettings["footer"][0]) != '') {
            $this->v["footOver"] = $GLOBALS["SL"]->treeSettings["footer"][0];
        }
        if (trim($this->v["nodeKidFunks"]) != '') {
            $GLOBALS["SL"]->pageAJAX .= 'function checkAllNodeKids() { ' . $this->v["nodeKidFunks"] 
                /* . ' if (nodeList && nodeList.length > 0) { for (var i=0; i < nodeList.length; i++) { '
                . 'chkNodeParentVisib(nodeList[i]); } } ' */
                . ' setTimeout(function() { checkAllNodeKids(); }, 3000); }' // re-check every 3 seconds
                . ' setTimeout(function() { checkAllNodeKids(); }, 1);' . "\n";
        }
        if ($GLOBALS["SL"]->treeRow->TreeType != 'Page') {
            $ret .= '</div> <!-- end maincontentWrap -->';
        }
        if ($this->hasAjaxWrapPrinting()) {
            $ret .= '</div>';
        } else {
            // replace page-based JS stuff
            $tmpFile = '../storage/app/dynamicJava-' . rand(100000000,1000000000) . '.js';
            $java = 'if (document.getElementById("dynamicJS")) document.getElementById("dynamicJS").remove();
                if (document.getElementById("treeJS")) document.getElementById("treeJS").remove();'
                . ((isset($GLOBALS["SL"]->pageJAVA) && trim($GLOBALS["SL"]->pageJAVA) != '') 
                ? $GLOBALS["SL"]->pageJAVA : '')
                . ((isset($GLOBALS["SL"]->pageAJAX) && trim($GLOBALS["SL"]->pageAJAX) != '') 
                ? ' $(document).ready(function(){ ' . $GLOBALS["SL"]->pageAJAX . ' }); ' : '');
            file_put_contents($tmpFile, $java);
            $minifier = new Minify\JS($tmpFile);
            unlink($tmpFile);
            $ret .= $GLOBALS["SL"]->pageSCRIPTS . '<script type="text/javascript">' 
                . $minifier->minify() . '</script>';
        }
        return $ret;
    }
    
    protected function changeNodeID($nID, $newID)
    {
        
    }
    
    protected function tmpDebug($str = '')
    {
        return true;
    }
    
    protected function errorDeniedFullPdf()
    {
        return '<br /><br /><center><h3>You are trying to access the complete details of a record which '
            . 'requires you to <a href="/login">login</a> as the owner, or an otherwise authorized user. '
            . '<br /><br />The public version of this complaint can be found here:<br />'
            . '<a href="/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/read-' . $this->coreID . '">' 
            . $GLOBALS["SL"]->sysOpts["app-url"] . '/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/read-' . $this->coreID 
            . '</a></h3></center>';
    }
    
    protected function errorDeniedFullXml()
    {
        return $this->errorDeniedFullPdf();
    }
    
    
}
