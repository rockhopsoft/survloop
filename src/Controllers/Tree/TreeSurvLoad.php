<?php
/**
  * TreeSurvLoad is a mid-level class using a standard branching tree, 
  * mostly for Survloop's surveys and pages.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use DB;
use Storage;
use Illuminate\Http\Request;
use App\Models\SLNode;
use App\Models\SLNodeResponses;
use RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv;
use RockHopSoft\Survloop\Controllers\Tree\SurvData;
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvConds;

class TreeSurvLoad extends TreeSurvConds
{
    protected $pageJSvalid   = '';
    protected $REQstep       = '';
    protected $hasREQ        = false;
    protected $checkboxNodes = [];
    protected $tagsNodes     = [];
    
    protected $pageCnt       = 0;
    protected $loopCnt       = 0;
    protected $loadingError  = '';
    protected $urlSlug       = '';
    
    protected $isPage        = false;
    protected $isReport      = false;

    // table name, and sort field, if this is tree one big loop
    protected $isBigSurvloop = ['', '', ''];
    
    public $xmlMapTree       = false;
    
    public $emojiTagUsrs     = [];
    
    // kidMaps[nodeID][kidNodeID][] = [ responseInd, responseValue ]
    public $kidMaps          = [];
    protected $newLoopItemID = -3;
    
    protected function loadNode($nodeRow = NULL)
    {
        if ($nodeRow && isset($nodeRow->node_id) && $nodeRow->node_id > 0) {
            return new TreeNodeSurv($nodeRow->node_id, $nodeRow);
        }
        $newNode = new TreeNodeSurv();
        $newNode->nodeRow->node_tree = $this->treeID;
        return $newNode;
    }
    
    protected function loadLookups()
    {
        $localIPs = [
            '172.19.0.1',
            '192.168.10.1', 
            '173.79.192.119'
        ];
        $GLOBALS["SL"]->debugOn = (!isset($_SERVER["REMOTE_ADDR"]) 
            || in_array($_SERVER["REMOTE_ADDR"], $localIPs));
        if ($GLOBALS["SL"]->REQ->has('noDebug')) {
            $GLOBALS["SL"]->debugOn = false;
        }
        return true;
    }
    
    public function __construct(Request $request = null, $sessIn = -3, $dbID = -3, $treeID = -3, $skipSessLoad = false, $slInit = true)
    {
        return $this->constructor($request, $sessIn, $dbID, $treeID, $skipSessLoad, $slInit);
    }

    public function constructor(Request $request = null, $sessIn = -3, $dbID = -3, $treeID = -3, $skipSessLoad = false, $slInit = true)
    {
        $this->dbID = $this->treeID = 1;
        if ($dbID > 0) {
            $this->dbID = $dbID;
        } elseif (isset($GLOBALS["SL"])) {
            $this->dbID = $GLOBALS["SL"]->dbID;
        }
        if ($treeID > 0) {
            $this->treeID = $treeID;
        } elseif (isset($GLOBALS["SL"])) {
            $this->treeID = $GLOBALS["SL"]->treeID;
        }
        if ($slInit) {
            $this->survloopInit($request);
            $this->coreIDoverride = -3;
            if ($sessIn > 0) {
                $this->coreIDoverride = $sessIn;
            }
            if (isset($GLOBALS["SL"]) 
                && $GLOBALS["SL"]->REQ->has('step') 
                && $GLOBALS["SL"]->REQ->has('tree') 
                && intVal($GLOBALS["SL"]->REQ->get('tree')) > 0) {
                $this->hasREQ = true;
                $this->REQstep = $GLOBALS["SL"]->REQ->get('step');
            }
            $this->loadLookups();
            $this->isPage = (isset($GLOBALS["SL"]->treeRow->tree_type) 
                && $GLOBALS["SL"]->treeRow->tree_type == 'Page');
            $this->sessData = new SurvData;
        }
        $this->constructorExtra();
        return true;
    }

    /**
     * Initializing extra things for special admin pages.
     *
     * @param  Illuminate\Http\Request  $request
     * @return boolean
     */
    protected function constructorExtra()
    {
        return true;
    }
    
    public function loadPageVariation(Request $request, $dbID = 1, $treeID = 1, $currPage = '/')
    {
        $GLOBALS["SL"] = new Globals($request, $dbID, $treeID, $treeID);
        $GLOBALS["SL"]->microLog();
        $this->constructor($request, -3, $dbID, $treeID);
        $this->survInitRun = false;
        $this->survloopInit($request, $currPage);
        return true;
    }
    
    public function loadTreeFromCache()
    {
        $cacheFile = '/cache/php/tree-load-' 
            . $this->treeID . '.php';
        if (!$GLOBALS["SL"]->REQ->has('refresh') && file_exists($cacheFile)) {
            $content = Storage::get($cacheFile);
            eval($content);
        } else {
            $cache = '// Auto-generated loading cache from '
                . '/Survloop/Controllers/TreeSurv.php' . "\n\n";
            $this->pageCnt = 0;
            $this->kidMaps = $nodeIDs = [];
            if (isset($GLOBALS["SL"]->treeRow->tree_opts)) {
                $nodes = SLNode::where('node_tree', $this->treeID)
                    ->select('node_id', 'node_parent_id', 'node_parent_order', 
                        'node_type', 'node_opts', 'node_data_branch', 
                        'node_data_store', 'node_default', 'node_response_set')
                    ->get();
                foreach ($nodes as $row) {
                    $nodeIDs[] = $row->node_id;
                    if ($row->node_parent_id <= 0) {
                        $rootID = $row->node_id;
                        $cache .= '$'.'this->rootID = ' . $row->node_id . ';' . "\n";
                    }
                    if (in_array($row->node_type, ['Page', 'Loop Root'])) {
                        $this->pageCnt++;
                    }
                    $cache .= $this->loadTreeFromCacheRow($row);
                }
                $responses = SLNodeResponses::whereIn('node_res_node', $nodeIDs)
                    ->where('node_res_show_kids', '>', 0)
                    ->get();
                if ($responses->isNotEmpty()) {
                    foreach ($responses as $j => $res) {
                        $cache .= $this->loadTreeFromCacheResponse($res);
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
            
            if (file_exists($cacheFile)) {
                Storage::delete($cacheFile);
            }
            Storage::put($cacheFile, $cache . $cache2 . $cache3);
        }
        return true;
    }
    
    protected function loadTreeFromCacheRow($row)
    {
        $cache = '';
        if ($GLOBALS["SL"]->treeRow->tree_opts%5 == 0 
            && $row->node_parent_id == $rootID 
            && $row->node_type == 'Loop Root' 
            && trim($row->node_default) != ''
            && isset($GLOBALS["SL"]->dataLoops[$row->node_default])) {
            $loop = $GLOBALS["SL"]->dataLoops[$row->node_default];
            if (isset($loop->data_loop_table)) {
                $tbl = $loop->data_loop_table;
                $cache .= '$'.'this->isBigSurvloop = [\'' . $tbl . '\', \'';
                if (trim($row->node_default) != '') {
                    $cache .= $row->node_default . '\', \'asc\'];' . "\n";
                } else {
                    $cache .= $GLOBALS["SL"]->tblAbbr[$tbl] . 'id\', \'desc\'];' . "\n";
                }
            }
        }
        if ($row->node_type == 'Checkbox' 
            || (in_array($row->node_type, ['Drop Down', 'U.S. States']) 
                && $row->node_opts%53 == 0)) {
            $cache .= '$'.'this->checkboxNodes[] = ' . $row->node_id . ';' . "\n";
        } elseif (in_array($row->node_type, ['Data Print', 'Data Print Row']) 
            && isset($row->node_data_store)
            && trim($row->node_data_store) != '') {
            list($tbl, $fld) = $GLOBALS["SL"]->splitTblFld($row->node_data_store);
            if ($GLOBALS["SL"]->origFldCheckbox($tbl, $fld) > 0) {
                $cache .= '$'.'this->checkboxNodes[] = ' . $row->node_id . ';' . "\n";
            }
        }
        $includeNode = true;
        if ($row->node_type == 'Data Manip: Update') {
            // add unless this node is data manip update which is under a new record manip
            $includeNode = (!isset($this->allNodes[$row->node_parent_id]) 
                || $this->allNodes[$row->node_parent_id]->nodeType != 'Data Manip: New');
        }
        if ($includeNode) {
            $cacheNode = '$'.'this->allNodes[' . $row->node_id . '] = '
                . 'new RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv(' 
                    . $row->node_id . ', [], ['
                    . '"pID" => '      . intVal($row->node_parent_id)        . ', '
                    . '"pOrd" => '     . intVal($row->node_parent_order)     . ', '
                    . '"opts" => '     . intVal($row->node_opts)             . ', '
                    . '"type" => "'    . addslashes($row->node_type)         . '", '
                    . '"branch" => "'  . addslashes($row->node_data_branch)  . '", '
                    . '"store" => "'   . addslashes($row->node_data_store)   . '", '
                    . '"set" => "'     . addslashes(stripslashes($row->node_response_set)) . '", '
                    . '"def" => "'     . addslashes(stripslashes($row->node_default))      . '"'
                . ']);' . "\n";
            eval($cacheNode);
            $cache .= $cacheNode;
        }
        return $cache;
    }
    
    protected function loadTreeFromCacheResponse($res)
    {
        $cache = '';
        if (!isset($this->kidMaps[$res->node_res_node])) {
            $this->kidMaps[$res->node_res_node] = [];
            $cache .= '$'.'this->kidMaps[' . $res->node_res_node 
                . '] = [];' . "\n";
        }
        $showKids = intVal($res->node_res_show_kids);
        if (!isset($this->kidMaps[$res->node_res_node][$showKids])) {
            $this->kidMaps[$res->node_res_node][$showKids] = [];
            $cache .= '$'.'this->kidMaps[' . $res->node_res_node . '][' 
                . $res->node_res_show_kids . '] = [];' . "\n";
        }
        $cache .= '$'.'this->kidMaps[' . $res->node_res_node . '][' 
            . $res->node_res_show_kids . '][] = [ ' . $res->node_res_ord 
            . ', "' . $res->node_res_value . '" ];' . "\n";
        return $cache;
    }
    
    public function hasParentPage($nID)
    {
        if (isset($this->allNodes[$nID]) 
            && isset($this->allNodes[$nID]->parentID) 
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
        $GLOBALS["SL"]->microLog('Start TreeSurvLoad loadTree(');
        $this->loadTreeStart($treeIn, $request);
        $GLOBALS["SL"]->microLog('loadTree( after loadTreeStart(');
        $this->loadTreeFromCache();
        $GLOBALS["SL"]->microLog('loadTree( after loadTreeFromCache(');
        $this->loadAllSessData();
        $GLOBALS["SL"]->microLog('loadTree( after loadAllSessData(');
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
        $GLOBALS["SL"]->microLog('Start loadAllSessData(');
        $this->loadSessionClear($coreTbl, $coreID);
        $GLOBALS["SL"]->microLog('loadAllSessData( after loadSessionClear(');
        $this->loadSessInfo($coreTbl);
        $GLOBALS["SL"]->microLog('loadAllSessData( after loadSessInfo(');
        $this->loadSessionData($coreTbl, $coreID);
        $GLOBALS["SL"]->microLog('loadAllSessData( after loadSessionData(');
        $this->loadSessionDataSaves();
        $GLOBALS["SL"]->microLog('loadAllSessData( after loadSessionDataSaves(');
        $this->runLoopConditions();
        $GLOBALS["SL"]->microLog('End loadAllSessData( after runLoopConditions(');
        $this->loadAllSessDataChecks();
        return true;
    }
    
    /**
     * Initializing extra things after loading a core record's data.
     *
     * @return boolean
     */
    protected function loadAllSessDataChecks()
    {
        return false;
    }

    /**
     * Run anything else extra needed to clear data in between sessions.
     *
     * @return boolean
     */
    protected function loadSessionClear($coreTbl = '', $coreID = -3)
    {
        return false;
    }
    
    /**
     * Load anything else needed after default loading of a Tree Session.
     *
     * @return boolean
     */
    protected function loadExtra()
    {
        return true;
    }
    
    public function currInReport()
    {
        $abbr = $GLOBALS["SL"]->coreTblAbbr();
        $coreTbl = trim($GLOBALS["SL"]->coreTbl);
        if ($coreTbl != '' && $abbr != '' && isset($this->sessInfo->sess_curr_node)) {
            $currNode = intVal($this->sessInfo->sess_curr_node);
            $lastPage = intVal($GLOBALS["SL"]->treeRow->tree_last_page);
            $isLastPage = ($lastPage == $abbr . 'submission_progress');
            if ($this->sessData->currSessDataTblFld($currNode, $coreTbl, $isLastPage)) {
                session()->forget('sessID' . $GLOBALS["SL"]->sessTree);
                session()->forget('coreID' . $GLOBALS["SL"]->sessTree);
                session()->save();
                return false;
            }
        }
        return true;
    }
    
    // returns array of DataBranch tables, [0] being the closest related to the current node's data
    protected function loadNodeDataBranch($nID = -3)
    {
        $this->sessData->setCoreID($GLOBALS["SL"]->coreTbl, $this->coreID);
        // not sure why this is needed

        $nIDtmp = $nID;
        $parents = [$nID];
        while ($this->hasNode($nIDtmp)) {
            $nIDtmp = $this->allNodes[$nIDtmp]->getParent();
            if (intVal($nIDtmp) > 0) {
                $parents[] = $nIDtmp;
            }
        }
        $this->sessData->dataBranches = [
            [
                "branch" => $GLOBALS["SL"]->coreTbl, 
                "loop"   => '', 
                "itemID" => $this->coreID 
            ]
        ];
        if (sizeof($parents) > 1) {
            for ($i = (sizeof($parents)-2); $i >= 0; $i--) {
                $p = $parents[$i];
                if ($this->allNodes[$p]->nodeType == 'Data Manip: New') {
                    $this->loadManipBranch($p, true);
                } elseif (trim($this->allNodes[$p]->dataBranch) != '') {
                    $nBranch = $this->allNodes[$p]->dataBranch;
                    $addBranch = $addLoop = '';
                    $itemID = -3;
                    if ($this->allNodes[$p]->isLoopRoot()) {
                        $addLoop = $GLOBALS["SL"]->getLoopNameByNodeID($p);
                        if (isset($GLOBALS["SL"]->dataLoops[$addLoop])) {
                            $addBranch = $GLOBALS["SL"]->dataLoops[$addLoop]->data_loop_table;
                            $itemID = $GLOBALS["SL"]->getSessLoopID($addLoop);
                        } else {
                            $addLoop = '';
                        }
                    } else {
                        $addBranch = $nBranch;
                        $isManip = $this->allNodes[$p]->isDataManip();
                        list($itemInd, $itemID) = $this->sessData->currSessDataPos($nBranch, $isManip);
                        if ($itemID <= 0) {
                            if (isset($GLOBALS["SL"]->tblAbbr[$nBranch])) {
                                $lastInd = sizeof($this->sessData->dataBranches)-1;
                                $parBranch = $this->sessData->dataBranches[$lastInd]["branch"];
                                $lnkFld = $GLOBALS["SL"]->getForeignLnk(
                                    $GLOBALS["SL"]->tblI[$parBranch],
                                    $GLOBALS["SL"]->tblI[$nBranch]
                                );
                                if ($lnkFld != '') {
                                    $lnkFld = $GLOBALS["SL"]->tblAbbr[$parBranch] . $lnkFld;
                                    $lastItemID = $this->sessData->dataBranches[$lastInd]["itemID"];
                                    $row = $this->sessData->getRowById($parBranch, $lastItemID);
                                    if ($row && isset($row->{ $lnkFld })) {
                                        $itemID = $row->{ $lnkFld };
                                    }
                                }
                                if ($lnkFld == '' || $itemID <= 0) {
                                    $lnkFld = $GLOBALS["SL"]->getForeignLnk(
                                        $GLOBALS["SL"]->tblI[$nBranch], 
                                        $GLOBALS["SL"]->tblI[$parBranch]
                                    );
                                    if ($lnkFld != '') {
                                        $lnkFld = $GLOBALS["SL"]->tblAbbr[$nBranch] . $lnkFld;
                                        $lastItemID = $this->sessData->dataBranches[$lastInd]["itemID"];
                                        $row = $this->sessData->getRowById($nBranch, $lastItemID);
                                        if ($row && isset($row->{ $lnkFld })) {
                                            $itemID = $row->{ $lnkFld };
                                        }
                                    }
                                }
                                if ($lnkFld == '' || $itemID <= 0) {
                                    $lnkFld = $GLOBALS["SL"]->getForeignLnk(
                                        $GLOBALS["SL"]->tblI[$nBranch], 
                                        $GLOBALS["SL"]->tblI[$parBranch]
                                    );
                                    if ($parBranch == 'users' 
                                        && $lnkFld == 'user_id' 
                                        && isset($this->sessData->dataSets[$nBranch]) 
                                        && sizeof($this->sessData->dataSets[$nBranch]) > 0) {
                                        $lnkFld = $GLOBALS["SL"]->tblAbbr[$nBranch] . $lnkFld;
                                        $branchID = $this->sessData->dataBranches[$lastInd]["itemID"];
                                        foreach ($this->sessData->dataSets[$nBranch] as $row) {
                                            if (isset($row->{ $lnkFld }) 
                                                && $row->{ $lnkFld } == $branchID) {
                                                $itemID = $row->getKey();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $this->sessData->startLoopDataBranch($addBranch, $itemID, $addLoop);
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
                $this->coreID 
                    = $GLOBALS["SL"]->coreID 
                    = $GLOBALS["SL"]->chkInPublicID($coreID, $coreTbl);
            } else {
                $this->coreID 
                    = $GLOBALS["SL"]->coreID 
                    = $this->corePublicID 
                    = $coreID;
            }
        }
        return $this->coreID;
    }
    
    protected function setPublicID($coreTbl = '')
    {
        if (trim($coreTbl) == '') {
            $coreTbl = $GLOBALS["SL"]->coreTbl;
        }
        if ($GLOBALS["SL"]->tblHasPublicID($coreTbl) 
            && isset($this->sessData->dataSets[$coreTbl])) {
            $fld = $GLOBALS["SL"]->tblAbbr[$coreTbl] . 'public_id';
            if (isset($this->sessData->dataSets[$coreTbl][0])
                && isset($this->sessData->dataSets[$coreTbl][0]->{ $fld })) {
                $this->corePublicID = $this->sessData->dataSets[$coreTbl][0]->{ $fld };
                return true;
            }
        }
        return false;
    }
    
    public function loadSessionData($coreTbl, $coreID = -3)
    {
        if ($coreID > 0) {
            $this->coreID 
                = $GLOBALS["SL"]->coreID 
                = $this->corePublicID 
                = $coreID;
        }
        $this->sessData->loadCore(
            $coreTbl, 
            $this->coreID, 
            $this->checkboxNodes, 
            $this->isBigSurvloop
        );
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
        $nodeSave = DB::table('sl_node_saves')
            ->join('sl_sess', 'sl_node_saves.node_save_session', '=', 'sl_sess.sess_id')
            ->where('sl_sess.sess_tree', '=', $this->treeID)
            ->where('sl_sess.sess_core_id', '=', $this->coreID)
            ->distinct()
            ->get([ 'sl_node_saves.node_save_node' ]);
        if ($nodeSave->isNotEmpty()) {
            foreach ($nodeSave as $save) {
                if (!$this->loadSessionDataSavesExceptions($save->node_save_node)) {
                    $majorSection = $this->getCurrMajorSection($save->node_save_node);
                    if (!in_array($majorSection, $this->sessMajorsTouched)) {
                        $this->sessMajorsTouched[] = $majorSection;
                    }
                    $minorSection = $this->getCurrMinorSection($save->node_save_node);
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
            if (is_array($GLOBALS["SL"]->REQ->input($fldName)) 
                && is_array($GLOBALS["SL"]->REQ->input($fldName))
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
    
    protected function printNodeSessDataOverride(&$curr)
    {
        return [];
    }
    
    protected function customNodePrint(&$curr = null)
    {
        return '';
    }
    
    protected function printNodePublic($nID = -3, $tmpSubTier = [])
    {
        return 'Node #' . $nID . '<br />';
    }
    
    protected function postNodePublicCustom(&$curr)
    {
        return false;
    }
    
    protected function postNodePublic($nID = -3, $tmpSubTier = [])
    {
        return true;
    }
    
    public function addCondEditorAjax()
    {
        $GLOBALS["SL"]->pageAJAX .= view('vendor.survloop.admin.db.inc-addCondition-ajax')
            ->render();
        return true;
    }
    
    public function treeSessionsWhereExtra() 
    {
        return "";
    }
    
    public function sessDump($lastNode = -3)
    {
        //return '<!-- ipip: ' . $_SERVER["REMOTE_ADDR"] . ' -->';
        if ($GLOBALS["SL"]->debugOn 
            && !$GLOBALS["SL"]->REQ->has('ajax')
            && !$GLOBALS["SL"]->isPdfView()) {
            $userName = '';
            if (isset($this->v["user"]) && $this->v["user"]) {
                $userName = $this->v["user"]->name;
            }
            ob_start();
            print_r($GLOBALS["SL"]->REQ->all());
            $this->v["requestDeets"] = ob_get_contents();
            ob_end_clean(); 
            ob_start();
            print_r(session()->all());
            $this->v["sessionDeets"] = ob_get_contents();
            ob_end_clean(); 
            $this->v["lastNode"]           = $lastNode;
            $this->v["currNode"]           = $this->currNode();
            $this->v["coreID"]             = $this->coreID;
            $this->v["sessInfo"]           = $this->sessInfo;
            $this->v["sessData"]           = $this->sessData;
            $this->v["dataSets"]           = $this->sessData->dataSets;
            $this->v["currNodeDataBranch"] = $this->sessData->dataBranches;
            return view('vendor.survloop.elements.inc-var-dump', $this->v)
                ->render();
        }
        return '';
    }
    
    public function nodeSessDump($nIDtxt = '', $nID = -3)
    {
        if ($GLOBALS["SL"]->debugOn 
            && !$GLOBALS["SL"]->REQ->has('ajax')
            && !$GLOBALS["SL"]->isPrintView()) {
            if ($nID > 0 
                && isset($this->allNodes[$nID]) 
                && isset($this->allNodes[$nID]->nodeType)
                && $this->allNodes[$nID]->nodeType == 'Layout Column') {
                return '';
            }
            return view(
                'vendor.survloop.elements.inc-var-dump-node', 
                [
                    "nID"          => $nID,
                    "nIDtxt"       => $nIDtxt,
                    "dataBranches" => $this->sessData->dataBranches
                ]
            )->render();
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