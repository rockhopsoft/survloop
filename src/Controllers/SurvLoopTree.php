<?php
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
use App\Models\SLFields;
use App\Models\SLSess;
use App\Models\SLSessLoops;

use SurvLoop\Controllers\CoreTree;
use SurvLoop\Controllers\SurvLoopData;
use SurvLoop\Controllers\SurvLoopTreeXML;
use SurvLoop\Controllers\DatabaseLookups;

class SurvLoopTree extends CoreTree
{
    
    public $classExtension        = 'SurvLoopTree';
    public $treeVersion           = 'v0.1';
    public $abTest                = 'A';
    
    public $currMajorSection      = 0;
    public $majorSections         = [];
    public $currMinorSection      = 0;
    public $minorSections         = [];
    
    protected $sessDataChangeLog  = [];
    protected $sessNodesDone      = [];
    protected $sessMajorsTouched  = [];
    protected $sessMinorsTouched  = [];
    public $navBottom             = '';
    
    protected $REQstep            = '';
    protected $hasREQ             = false;
    
    public $nodeTreeProgressBar   = '';
    protected $checkboxNodes      = [];
    
    protected $loopCnt            = 0;
    protected $loadingError       = '';
    protected $urlSlug            = '';
    
    protected $isReport           = false;
    protected $isBigSurvLoop      = ['', '', '']; // table name, and sort field, if this is tree one big loop
    
    public $xmlMapTree            = false;
    
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
            || in_array($_SERVER["REMOTE_ADDR"], ['192.168.10.1']));
        return true;
    }
    
    public function __construct(Request $request, $sessIn = -3, $dbID = -3, $treeID = -3)
    {
        $this->dbID = (($dbID > 0) ? $dbID : $GLOBALS["SL"]->dbID);
        $this->treeID = (($treeID > 0) ? $treeID : $GLOBALS["SL"]->treeID);
        $this->survLoopInit($request);
        $this->coreIDoverride = -3;
        if ($sessIn > 0) $this->coreIDoverride = $sessIn;
        $this->REQ = $request;
        if ($GLOBALS["SL"]->REQ->has('node') && intVal($GLOBALS["SL"]->REQ->node) > 0) {
            $this->hasREQ = true;
            $this->REQstep = $GLOBALS["SL"]->REQ->get('step');
        }
        $this->loadLookups();
        $this->sessData = new SurvLoopData;
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
            $nodes = SLNode::where('NodeTree', $this->treeID)
                ->select('NodeID', 'NodeParentID', 'NodeParentOrder', 'NodeType', 'NodeOpts', 
                    'NodeDataBranch', 'NodeDataStore', 'NodeResponseSet', 'NodeDefault')
                ->get();
            foreach ($nodes as $row) {
                if ($row->NodeParentID <= 0) {
                    $rootID = $row->NodeID;
                    $cache .= '$'.'this->rootID = ' . $row->NodeID . ';' . "\n";
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
                if ($row->NodeType == 'Checkbox') {
                    $cache .= '$'.'this->checkboxNodes[] = ' . $row->NodeID . ';' . "\n";
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
                            . '"def" => "'    . $row->NodeDefault             . '"'
                        . ']);' . "\n";
                    eval($cacheNode);
                    $cache .= $cacheNode;
                }
            }
            $cache .= '$'.'this->treeSize = sizeof($'.'this->allNodes);' . "\n";
            
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
    
    public function loadTree($treeIn = -3, Request $req = NULL, $loadFull = false)
    {
        if ($treeIn > 0) {
            $this->treeID = $treeIn;
        } elseif ($this->treeID <= 0) {
            if (intVal($GLOBALS["SL"]->treeID) > 0) {
                $this->treeID = $GLOBALS["SL"]->treeID;
            } else {
                $this->tree = SLTree::orderBy('TreeID', 'asc')
                    ->first();
                $this->treeID = $this->tree->TreeID;
            }
        }
        $this->loadTreeFromCache();
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl);
        return true;
    }
    
    protected function loadAllSessData($coreTbl = '', $coreID = -3)
    {
        if (trim($coreTbl) == '') $coreTbl = $GLOBALS["SL"]->coreTbl;
        $this->loadSessInfo($coreTbl);
        $this->loadSessionData($coreTbl, $coreID);
        $this->loadExtra();
        $this->chkCoreTblFlds();
        $this->loadSessionDataSaves();
        $this->runLoopConditions();
        return true;
    }
    
    protected function loadExtra() { }
    
    protected function chkCoreTblFlds() 
    {
        if ($GLOBALS["SL"]->treeRow->TreeType != 'Primary Public XML') {
            if (isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl]) 
                && isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0])) {
                $coreAbbr = $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl];
                if ((!isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->{ $coreAbbr . 'UserID' }) 
                    || intVal($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->{ $coreAbbr . 'UserID' }) <= 0)
                    && $this->v["user"] && isset($this->v["user"]->id)) {
                    $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->update([
                        $coreAbbr . 'UserID' => $this->v["user"]->id
                    ]);
                }
                if (!isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->{ $coreAbbr . 'UniqueStr' }) 
                    || trim($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->{ $coreAbbr . 'UniqueStr' }) == '') {
                    $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->update([
                        $coreAbbr . 'UniqueStr' => $this->getRandStr($GLOBALS["SL"]->coreTbl, $coreAbbr . 'UniqueStr', 20)
                    ]);
                }
                if ((!isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->{ $coreAbbr . 'IPaddy' }) 
                    || trim($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->{ $coreAbbr . 'IPaddy' }) == '') 
                    && isset($_SERVER["REMOTE_ADDR"])) {
                    $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->update([
                        $coreAbbr . 'IPaddy' => bcrypt($_SERVER["REMOTE_ADDR"])
                    ]);
                }
                if (!isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->{ $coreAbbr . 'IsMobile' }) 
                    || trim($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->{ $coreAbbr . 'IsMobile' }) == '') {
                    $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->update([
                        $coreAbbr . 'IsMobile' => (($this->isMobile()) ? 1: 0)
                    ]);
                }
            }
        }
        return true;
    }
    
    public function currInComplaint()
    {
        if (trim($GLOBALS["SL"]->coreTbl) != '' && isset($GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl])) {
            $isLastPage = ($GLOBALS["SL"]->treeRow->TreeLastPage
                == $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl] . 'SubmissionProgress');
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
        $this->sessData->dataBranches = [];
        $this->sessData->dataBranches[] = [
            "branch" => $GLOBALS["SL"]->coreTbl, 
            "loop"   => '', 
            "itemID" => $this->coreID 
        ];
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
                                } else {
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
        if ($this->debugOn && true) {
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
            $this->v["currNodeDataBranch"] = $this->currNodeDataBranch;
            $this->v["REQ"]                = $this->REQ;
            return view('vendor.survloop.inc-var-dump', $this->v)->render();
        }
        return '';
    }
    
    /******************************************************************************************************
    
    Next are functions related to data management, storing and retrieving responses from the tree's forms. 
    
    ******************************************************************************************************/
    
    protected function loadSessionData($coreTbl, $coreID = -3)
    {
        if ($coreID > 0) $this->coreID = $coreID;
        $this->sessData->loadCore($coreTbl, $this->coreID, $this->currNodeDataBranch, $this->checkboxNodes, 
            $this->isBigSurvLoop);
        return true;
    }
    
    
    
    protected function loadSessionDataSaves()
    {
        $this->sessMajorsTouched = $this->sessMinorsTouched = [];
        for ($s=0; $s<sizeof($this->majorSections); $s++) $this->sessMinorsTouched[$s] = [];
        $nodeSave = SLNodeSaves::where('NodeSaveSession', $this->coreID)
            ->distinct()
            ->select('NodeSaveNode')
            ->get();
        if (sizeof($nodeSave) > 0) {
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
            if (is_array($GLOBALS["SL"]->REQ->input($fldName)) && sizeof($GLOBALS["SL"]->REQ->input($fldName)) > 0) {
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
    
    protected function customNodePrint($nID = -3, $tmpSubTier = [])
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
    
    protected function loadProgBar()
    {
        $rawPerc = $this->rawOrderPercent($this->currNode());
        if (intVal($rawPerc) < 5) $rawPerc = 5;
        $this->currMajorSection = $this->getCurrMajorSection($this->currNode());
        if (!isset($this->minorSections[$this->currMajorSection]) 
            || sizeof($this->minorSections[$this->currMajorSection]) == 0) {
            $this->currMinorSection = 0;
        } else {
            $this->currMinorSection = $this->getCurrMinorSection($this->currNode(), $this->currMajorSection);
        }
        $this->loadProgBarTweak();
        if (sizeof($this->majorSections) > 0) {
            foreach ($this->majorSections as $maj => $majSect) {
                if (sizeof($this->minorSections[$maj]) > 0) {
                    foreach ($this->minorSections[$maj] as $min => $minSect) {
                        if (isset($minSect[0])) $this->allNodes[$minSect[0]]->fillNodeRow();
                    }
                }
            }
        }
        if (isset($this->majorSections[$this->currMajorSection][1]) > 0) {
            $this->pageAJAX .= '$(".snLabel").click(function() { '
                . '$("html, body").animate({ scrollTop: 0 }, "fast"); });' . "\n";
            return view('vendor.survloop.inc-progress-bar', [
                "allNodes"          => $this->allNodes, 
                "majorSections"     => $this->majorSections, 
                "minorSections"     => $this->minorSections, 
                "sessMajorsTouched" => $this->sessMajorsTouched, 
                "sessMinorsTouched" => $this->sessMinorsTouched, 
                "currMajorSection"  => $this->currMajorSection, 
                "currMinorSection"  => $this->currMinorSection, 
                "rawPerc"           => $rawPerc
            ])->render();
        }
        return false;
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
        $this->allNodes[$nID]->fillNodeRow();
        return $this->parseConditions($this->allNodes[$nID]->conds, [], $nID);
    }
    
    protected function checkNodeConditionsCustom($nID, $condition = '')
    {
        return true;
    }
    
    // Setting the second parameter to false alternatively returns an array of individual conditions
    public function parseConditions($conds = [], $recObj = [], $nID = -3)
    {
        $retTF = true;
        if (sizeof($conds) > 0) {
            foreach ($conds as $i => $cond) {
                if ($retTF) {
                    if ($cond && isset($cond->CondDatabase) && $cond->CondOperator == 'CUSTOM') {
                        if (trim($cond->CondTag) == '#NodeDisabled') {
                            $retTF = false;
                        } elseif (trim($cond->CondTag) == '#NotLoggedIn') {
                            if ($this->v["user"] && isset($this->v["user"]->id) && intVal($this->v["user"]->id) > 0) {
                                $retTF = false;
                            }
                        } elseif (trim($cond->CondTag) == '#LoggedIn') {
                            if (!$this->v["user"] || !isset($this->v["user"]->id) 
                                || intVal($this->v["user"]->id) <= 0) {
                                $retTF = false;
                            }
                        } elseif (trim($cond->CondTag) == '#IsAdmin') {
                            if (!$this->v["user"] || !isset($this->v["user"]->id) || intVal($this->v["user"]->id) <= 0 
                                || !$this->v["user"]->hasRole('administrator|staff|databaser|brancher')) {
                                $retTF = false;
                            }
                        } elseif (trim($cond->CondTag) == '#IsNotAdmin') {
                            if ($this->v["user"] && isset($this->v["user"]->id) && intVal($this->v["user"]->id) > 0 
                                && $this->v["user"]->hasRole('administrator|staff|databaser|brancher')) {
                                $retTF = false;
                            }
                        } elseif (!$this->checkNodeConditionsCustom($nID, trim($cond->CondTag))) {
                            $retTF = false;
                        }
                    } elseif (!$this->sessData->parseCondition($cond, $recObj, $nID)) {
                        $retTF = false; 
                    }
                }
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
                        if (sizeof($recObj) > 0 && $this->parseConditions($loop->conds, $recObj)) {
                            $this->sessData->loopItemIDs[$loop->DataLoopPlural][] = $recObj->getKey();
                            if (trim($loop->DataLoopSortFld) != '') {
                                $sortable[''.$recObj->getKey().''] = $recObj->{ $loop->DataLoopSortFld };
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
        if ($this->allNodes[$nID]->isDataManip() && !$this->nodeIsWithinPage($nID)) $this->runDataManip($nID);
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
    
    protected function runDataManip($nID)
    {
        if ($this->allNodes[$nID]->isDataManip()) {
            list($tbl, $fld, $newVal) = $this->allNodes[$nID]->getManipUpdate();
            if ($this->allNodes[$nID]->nodeType == 'Data Manip: New') {
                $newObj = $this->sessData->newDataRecord($tbl, $fld, $newVal);
                $manipUpdates = SLNode::where('NodeTree', $this->treeID)
                    ->where('NodeType', 'Data Manip: Update')
                    ->where('NodeParentID', $nID)
                    ->get();
                if (sizeof($manipUpdates) > 0) {
                    foreach ($manipUpdates as $nodeRow) {
                        $tmpNode = new SurvLoopNode($nodeRow->NodeID, $nodeRow);
                        list($tbl, $fld, $newVal) = $tmpNode->getManipUpdate();
                        $this->sessData->currSessData($nodeRow->NodeID, $tbl, $fld, 'update', $newVal);
                    }
                }
                $this->sessData->startTmpDataBranch($tbl, $newObj->getKey());
                //$this->loadAllSessData($GLOBALS["SL"]->coreTbl);
            } elseif ($this->allNodes[$nID]->nodeType == 'Data Manip: Update') {
                $this->sessData->currSessData($nID, $tbl, $fld, 'update', $newVal);
            }
        }
        return true;
    }
    
    protected function reverseDataManip($nID)
    {
        if ($this->allNodes[$nID]->isDataManip()) {
            list($tbl, $fld, $newVal) = $this->allNodes[$nID]->getManipUpdate();
            if ($this->allNodes[$nID]->nodeType == 'Data Manip: New') {
                $this->sessData->deleteDataRecord($tbl, $fld, $newVal);
                $this->loadAllSessData($GLOBALS["SL"]->coreTbl);
            }
        }
        return true;
    }
    
    protected function nodeBranchInfo($nID)
    {
        $tbl = $fld = $newVal = '';
        if (in_array($this->allNodes[$nID]->nodeType, ['Data Manip: New', 'Data Manip: Wrap'])) { // Data Manip: Update
            list($tbl, $fld, $newVal) = $this->allNodes[$nID]->getManipUpdate();
            if ($this->allNodes[$nID]->nodeType == 'Data Manip: Wrap') {
                $tbl = $this->allNodes[$nID]->dataBranch;
            }
        }
        if ($this->allNodes[$nID]->isLoopCycle() 
            && isset($GLOBALS["SL"]->dataLoops[$this->allNodes[$nID]->dataBranch])) {
            $tbl = $GLOBALS["SL"]->dataLoops[$this->allNodes[$nID]->dataBranch]->DataLoopTable;
        }
        return [$tbl, $fld, $newVal];
    }
    
    protected function loadManipBranch($nID, $force = false)
    {
        list($tbl, $fld, $newVal) = $this->nodeBranchInfo($nID);
        if ($tbl != '') {
            $manipBranchRow = $this->sessData->checkNewDataRecord($tbl, $fld, $newVal, []);
            if ((!$manipBranchRow || sizeof($manipBranchRow) == 0) && $force) {
                $manipBranchRow = $this->sessData->newDataRecord($tbl, $fld, $newVal);
            }
            if ($manipBranchRow && sizeof($manipBranchRow) > 0) {
                $this->sessData->startTmpDataBranch($tbl, $manipBranchRow->getKey());
            }
        }
        return true;
    }
    
    protected function closeManipBranch($nID)
    {
        list($tbl, $fld, $newVal) = $this->nodeBranchInfo($nID);
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
    
    protected function hasCycleAncestor($nID = -3)
    {
        if ($this->allNodes[$nID]->parentID > 0) {
            if ($this->allNodes[$this->allNodes[$nID]->parentID]->isLoopCycle()) {
                return true;
            }
            return $this->hasCycleAncestor($this->allNodes[$nID]->parentID);
        }
        return false;
    }
    
    protected function newLoopItem($nID = -3)
    {
        if (intVal($this->newLoopItem) <= 0) {
            $newID = $this->sessData->createNewDataLoopItem($nID);
            if ($newID > 0) {
                $GLOBALS["SL"]->REQ->loopItem = $newID;
                $this->settingTheLoop(trim($GLOBALS["SL"]->REQ->input('loop')), intVal($GLOBALS["SL"]->REQ->loopItem));
            }
            $this->newLoopItem = $nID;
        }
        return true;
    }
    
    protected function settingTheLoop($name, $itemID = -3, $rootJustLeft = -3)
    {
        if ($name == '') return false; 
        $found = false;
        if ($GLOBALS["SL"]->sessLoops && sizeof($GLOBALS["SL"]->sessLoops) > 0) {
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
        if ($GLOBALS["SL"]->sessLoops && sizeof($GLOBALS["SL"]->sessLoops) > 0) {
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
    
    protected function checkLoopsPostProcessing($newNode, $prevNode)
    {
        $currLoops = [];
        $backToRoot = false;
        // First, are we leaving one of our current loops?..
        if ($GLOBALS["SL"]->sessLoops && sizeof($GLOBALS["SL"]->sessLoops) > 0) {
            foreach ($GLOBALS["SL"]->sessLoops as $sessLoop) {
                if (isset($GLOBALS["SL"]->dataLoops[$sessLoop->SessLoopName])) {
                    $currLoops[$sessLoop->SessLoopName] = $sessLoop->SessLoopItemID;
                    $loop = $GLOBALS["SL"]->dataLoops[$sessLoop->SessLoopName];
                    if ($this->allNodes[$prevNode]->checkBranch($this->allNodes[$loop->DataLoopRoot]->nodeTierPath)
                        && !$this->allNodes[$newNode]->checkBranch($this->allNodes[$loop->DataLoopRoot]->nodeTierPath)) {
                        // Then we are now trying to leave this loop
                        if (in_array($this->REQstep, ['back', 'exitLoopBack'])) { // Then leaving the loop backwards, always allowed
                            $this->leavingTheLoop($loop->DataLoopPlural);
                        } else { // Check for conditions before moving leaving forward
                            if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop()) {
                                if (sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) > 1) {
                                    $backToRoot = true;
                                }
                            } elseif (intVal($loop->DataLoopMaxLimit) == 0 
                                || sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) < $loop->DataLoopMaxLimit) {
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
                            } else {
                                $this->updateCurrNodeNB($this->nextNode($loop->DataLoopRoot));
                            }
                        }
                    }
                }
            }
        }
        
        // If we haven't already tried to leave our loop, nor returned back to its root node...
        if (!$backToRoot && $GLOBALS["SL"]->dataLoops && sizeof($GLOBALS["SL"]->dataLoops) > 0) {
            foreach ($GLOBALS["SL"]->dataLoops as $loop) {
                if (!isset($currLoops[$loop->DataLoopPlural])) {
                    // Then this is a new loop we weren't previously in
                    $path = $this->allNodes[$loop->DataLoopRoot]->nodeTierPath;
                    if (!$this->allNodes[$prevNode]->checkBranch($path)
                        && $this->allNodes[$newNode]->checkBranch($path)) {
                        // Then we have just entered this loop from outside
                        if ($this->allNodes[$loop->DataLoopRoot]->isStepLoop() 
                            && (!isset($this->sessData->loopItemIDs[$loop->DataLoopPlural]) 
                                || sizeof($this->sessData->loopItemIDs[$loop->DataLoopPlural]) == 0)) {
                            $this->leavingTheLoop($loop->DataLoopPlural);
                            $this->updateCurrNodeNB($this->nextNodeSibling($newNode));
                        } else { // This loop is active
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
        return true;
    }
    
    public function pushCurrNodeURL($nID = -3)
    {
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
                    if (strlen($title) > 40) $title = substr($title, 0, 40) . '...';
                    $this->pageAJAX .= 'history.pushState( {}, "' . $title . ' - Open Police Complaints", '
                        . '"/' . (($GLOBALS["SL"]->treeIsAdmin) ? 'dash' : 'u') . '/' 
                        . $GLOBALS["SL"]->treeRow->TreeSlug . '/'
                        . $this->allNodes[$nID]->nodeRow->NodePromptNotes . '");' . "\n";
                }
            }
        }
        return '';
    }
    
    public function pushCurrNodeVisit($nID)
    {
        if (intVal($this->coreID) > 0 && $nID > 0) {
            $pagsSave = new SLNodeSavesPage;
            $pagsSave->PageSaveSession = $this->coreID;
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
                $loadNodeChk = SLNodeSavesPage::where('PageSaveSession', $this->coreID)
                    ->where('PageSaveNode', $loadNode->NodeID)
                    ->get();
                if (!$loadNodeChk || sizeof($loadNodeChk) == 0) return false;
                // perhaps upgrade to check for loop item id first?
                //$this->leavingTheLoop();
                $prevNode = $this->currNode();
                $this->updateCurrNode($loadNode->NodeID);
                if ($GLOBALS["SL"]->dataLoops && sizeof($GLOBALS["SL"]->dataLoops) > 0
                    && $GLOBALS["SL"]->sessLoops && sizeof($GLOBALS["SL"]->sessLoops) > 0) {
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
    
    public function printReportDeetsBlock($deets, $blockName = '')
    {
        return view('vendor.survloop.inc-report-deets', [ "deets" => $deets, "blockName" => $blockName ])->render();
    }
    
    
    
    
    /******************************************************************************************************
    
    XML OUTPUT
    
    ******************************************************************************************************/
    
    private function loadXmlMapTree(Request $request)
    {
        $this->survLoopInit($request);
        if (isset($GLOBALS["SL"]->xmlTree["id"])
            && (!$this->xmlMapTree || !isset($this->xmlMapTree) || sizeof($this->xmlMapTree) == 0)) {
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
        $v["tblFldEnum"] = [];
        $v["tblFldDefs"] = [];
        if ($v["tblFlds"] && sizeof($v["tblFlds"]) > 0) {
            foreach ($v["tblFlds"] as $i => $fld) {
                $v["tblFldDefs"][$fld->FldID] = [];
                if (strpos($fld->FldValues, 'Def::') !== false) {
                    $set = $GLOBALS["SL"]->getDefSet(str_replace('Def::', '', $fld->FldValues));
                    if ($set && sizeof($set) > 0) {
                        foreach ($set as $def) $v["tblFldDefs"][$fld->FldID][] = $def->DefValue;
                    }
                } elseif (trim($fld->FldValues) != '' && strpos($fld->FldValues, ';') !== false) {
                    $v["tblFldDefs"][$fld->FldID] = explode(';', $fld->FldValues);
                }
                $v["tblFldEnum"][$fld->FldID] = (sizeof($v["tblFldDefs"][$fld->FldID]) > 0);
            }
        }
        $v["tblHelp"] = $v["tblHelpFld"] = [];
        if ($v["tblID"] > 0 && isset($GLOBALS["SL"]->dataHelpers) && sizeof($GLOBALS["SL"]->dataHelpers) > 0) {
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
                $v["kids"] .= '<xs:element name="' . $nextV["tbl"] . '" minOccurs="0">
                    <xs:complexType mixed="true"><xs:sequence>
                        <xs:element name="' . $v["tblHelpFld"][$help]->FldName 
                        . '" minOccurs="0" maxOccurs="unbounded" />
                    </xs:sequence></xs:complexType>
                </xs:element>' . "\n";
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
            || sizeof($this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]]) == 0) {
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
                $v["recFlds"][$fld->FldID] = $this->genXmlFormatVal($rec, $fld, $v["tblAbbr"]);
            }
        }
        $v["kids"] = '';
        if ($v["tblHelp"] && sizeof($v["tblHelp"]) > 0) {
            foreach ($v["tblHelp"] as $help) {
                $nextV = $this->getXmlTmpV(-3, $help);
                $kidRows = $this->sessData->getChildRows($v["tbl"], $rec->getKey(), $nextV["tbl"]);
                if ($kidRows && sizeof($kidRows) > 0) {
                    if (intVal($nextV["tblID"]) > 0 && $nextV["TblOpts"]%5 > 0) {
                        $v["kids"] .= '<' . $nextV["tbl"] . '>' . "\n";
                    }
                    foreach ($kidRows as $j => $kid) {
                        $v["kids"] .= '<' . $v["tblHelpFld"][$help]->FldName . '>' 
                            . $this->genXmlFormatVal($kid, $v["tblHelpFld"][$help], 
                                $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->tbl[$help]])
                        . '</' . $v["tblHelpFld"][$help]->FldName . '>' . "\n";
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
    
    public function genXmlFormatVal($rec, $fld, $abbr)
    {
        $val = false;
        if ($fld->FldOpts%13 > 0 && isset($rec->{ $abbr . $fld->FldName })) {
            $val = $rec->{ $abbr . $fld->FldName };
            if (strpos($fld->FldValues, 'Def::') !== false) {
                if (intVal($val) > 0) {
                    $val = $GLOBALS["SL"]->getDefValue(str_replace('Def::', '', $fld->FldValues), $val);
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
                    $val = str_replace(' ', 'T', $val);
                }
            }
        }
        return $val;
    }
    
    
    
    
    public function runAjaxChecks(Request $request) {
        if ($request->has('email') && $request->has('password')) {
            print_r($request);
            $chk = User::where('email', $request->email)->get();
            if ($chk && sizeof($chk) > 0) echo 'found';
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
    
    public function ajaxChecks(Request $request)
    {
        return $this->index($request, 'ajaxChecks');
    }
    
    public function byID(Request $request, $coreID, $ComSlug = '')
    {
        $this->survLoopInit($request, '/report/' . $coreID);
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        $this->v["hasFbWidget"] = true;
        $this->v["content"] = $this->printFullReport();
        return view('vendor.survloop.master', $this->v);
    }
    
    protected function getAllPublicCoreIDs($coreTbl)
    {
        $ret = [];
        eval("\$list = " . $GLOBALS["SL"]->modelPath($coreTbl) 
            . "::orderBy('created_at', 'desc')->get();");
        if ($list && sizeof($list) > 0) {
            foreach ($list as $l) $ret[] = $l->getKey();
        }
        return $ret;
    }
    
    public function xmlAll(Request $request)
    {
        $this->survLoopInit($request, '/' . $GLOBALS["SL"]->treeRow->TreeSlug . '-xml-all');
        $this->loadXmlMapTree($request);
        $this->v["nestedNodes"] = '';
        $coreIDs = $this->getAllPublicCoreIDs($GLOBALS["SL"]->xmlTree["coreTbl"]);
        if (sizeof($coreIDs) > 0) {
            foreach ($coreIDs as $coreID) {
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
    
    public function xmlByID(Request $request, $coreID, $ComSlug = '')
    {
        $this->survLoopInit($request, '/' . $GLOBALS["SL"]->treeRow->TreeSlug . '-report-xml/' . $coreID);
        $this->loadXmlMapTree($request);
        if ($GLOBALS["SL"]->xmlTree["coreTbl"] == $GLOBALS["SL"]->coreTbl) {
            $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        } else { // XML core table is different from main tree
            $this->loadSessInfo($GLOBALS["SL"]->xmlTree["coreTbl"]);
            $this->loadAllSessData($GLOBALS["SL"]->xmlTree["coreTbl"], $coreID);
        }
        return $this->genXmlReport($request);
    }
    
    public function getXmlExample(Request $request)
    {
        $this->survLoopInit($request, '/' . $GLOBALS["SL"]->treeRow->TreeSlug . '-xml-example');
        $coreID = 1;
        if (isset($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->xmlTree["id"] . "-example"])) {
            $coreID = intVal($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->xmlTree["id"] . "-example"]);
        }
        eval("\$chk = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->xmlTree["coreTbl"]) . "::find(" . $coreID . ");");
        if ($chk && sizeof($chk) > 0) {
            return $this->xmlByID($request, $coreID);
        }
        return $this->redir('/xml-schema');
    }
    
    public function retrieveUpload(Request $request, $cid = -3, $upID = '')
    {
        if ($cid <= 0) return '';
        $this->survLoopInit($request, '');
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $cid);
        return $this->retrieveUploadFile($upID);
    }
    
    public function printAdminReport($coreID)
    {
        $this->v["cID"] = $coreID;
        $this->v["hasFbWidget"] = true;
        return $this->printFullReport('', true);
    }
    
    public function printFullReport($reportType = '', $isAdmin = false)
    {
        return '';
    }
    
    public function previewReportPubPri()
    {
        eval("\$treeClassReport = new " . $this->loadLoopReportClass() . "(\$this->REQ, \$this->coreID);");
        $treeClassReport->loadSessionData($GLOBALS["SL"]->coreTbl, $this->coreID);
        $treeClassReport->hideDisclaim = true;
        $previewPublic = $treeClassReport->printFullReport('Public');
        $previewPrivate = $treeClassReport->printFullReport('Investigate');
        return [$previewPublic, $previewPrivate];
    }
    
    protected function printNodePageFoot()
    {
        return $GLOBALS["SL"]->sysOpts["footer-master"];
    }
    
    
    // This function is the primary front-facing controller for the user experience
    public function index(Request $request, $type = '', $val = '')
    {
        $this->survLoopInit($request, '');
        $chk = $this->checkSystemInit();
        if (trim($chk) != '') return $chk;
        
        // Basic System Is Setup, Check for User Intercept From Index
        if ($GLOBALS["SL"]->treeIsAdmin) {
            if (!isset($this->v["user"]->id) || !$this->v["user"]->hasRole('administrator|staff|databaser|brancher')) {
                return $this->redir('/login');
            }
        }
        
        // Otherwise, Proceed Running Various Index Functions
        if ($type == 'ajaxChecks') {
            $this->runAjaxChecks($request);
            exit;
        }
        
        $this->v["content"] = $this->printTreePublic();
        if ($request->has('ajax') && $request->ajax == 1) { // tree form ajax submission
            echo $this->v["content"];
            exit;
        }
        
        $this->v["currInComplaint"] = $this->currInComplaint();
        
        if ($type == 'testRun') return $this->redir('/');
        $this->v["footOver"] = '';
        
        if ($GLOBALS["SL"]->treeIsAdmin) return $this->v["content"];
        else return view('vendor.survloop.master', $this->v);
    }
    
    
    /******************************************************************************************************
    
    MAIN PUBLIC OUTPUT WHERE EVERYTHING HAPPENS: print public version of currNode
    
    ******************************************************************************************************/
    
    public function printTreePublic()
    {
        $ret = '';
        $this->loadTree();
        
        if (!$this->hasREQ || (!$GLOBALS["SL"]->REQ->has('ajax') && !$GLOBALS["SL"]->REQ->has('frame'))) {
            $ret .= '<div id="fullPageChk"></div>' . "\n" . '<div id="ajaxWrap">';
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
        if (!$this->allNodes[$this->currNode()]->isPage() && !$this->allNodes[$this->currNode()]->isLoopRoot()) {
            $this->updateCurrNode($this->allNodes[$this->currNode()]->getParent());
        }
        
        if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('step')) {
            // Process form POST for all nodes, then store the data updates...
            $this->sessData->logDataSave($GLOBALS["SL"]->REQ->node, 'PAGE SAVE', -3, '', '');
            $ret .= $this->postNodePublic($GLOBALS["SL"]->REQ->node);
            $this->loadAllSessData($GLOBALS["SL"]->coreTbl);
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
        } elseif (trim($this->urlSlug) != '') {
            $this->pullNewNodeURL();
        }
        
        if (!$this->isStepUpload()) {
            $this->updateCurrNodeNB($this->currNode());
            if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('step')) {
                $this->loadAllSessData($GLOBALS["SL"]->coreTbl);
                $this->checkLoopsPostProcessing($this->currNode(), $lastNode);
            } else {
                if (!$this->checkNodeConditions($this->currNode())) {
                    $this->updateCurrNode($this->nextNode($this->currNode(), $this->currNodeSubTier));
                }
                $this->updateCurrNodeNB($this->currNode());
            }
            //$this->loadAllSessData($GLOBALS["SL"]->coreTbl);
        }
        /* if ($this->hasREQ && $GLOBALS["SL"]->REQ->has('step')) {
            $newNodeURL = $this->currNodeURL();
            if ($newNodeURL != '') {
                echo '<script type="text/javascript"> window.location="' . $newNodeURL . '"; </script>';
                exit;
            }
        } */
        
        $this->multiRecordCheck();
        $this->getPrintSpecs($this->currNode(), $this->currNodeSubTier);
        $ret .= $this->pushCurrNodeURL($this->currNode())
            . '<div id="treeWrap">' . "\n" 
                . (($this->allNodes[$this->currNode()]->nodeOpts%29 > 0) // Check Is Not An Exit Page
                    ? $this->loadProgBar() : '') . "\n" 
                . '<a name="maincontent" id="maincontent"></a>' . "\n"
                . ((trim($GLOBALS["errors"]) != '') ? $GLOBALS["errors"] : '')
                . $this->printNodePublic($this->currNode(), $this->currNodeSubTier) . "\n"
                . ((isset($this->v["multipleRecords"])) ? $this->v["multipleRecords"] : '')
            . '</div> <!-- end treeWrap -->' 
            . (($this->debugOn) ? $this->sessDump($lastNode) : '')
            . "\n";
        
        if (!$GLOBALS["SL"]->treeIsAdmin) {
            $ret .= $this->printNodePageFoot();
        }
        if (!$this->hasREQ || (!$GLOBALS["SL"]->REQ->has('ajax') && !$GLOBALS["SL"]->REQ->has('frame'))) {
            $ret .= '</div>' . view('vendor.survloop.inc-hold-sess')->render();
        }
        return $ret;
    }
    
    protected function changeNodeID($nID, $newID)
    {
        
    }
    
}
