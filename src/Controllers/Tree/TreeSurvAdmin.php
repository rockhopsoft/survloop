<?php
/**
  * TreeSurvAdmin is a higher-level class extending SurvLoop's core tree class
  * with tools to edit the tree itself.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers\Tree;

use DB;
use Cache;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SLDefinitions;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLNodeSaves;
use App\Models\SLNodeResponses;
use App\Models\SLConditions;
use App\Models\SLDataLoop;
use App\Models\SLConditionsNodes;
use App\Models\SLEmails;
use SurvLoop\Controllers\Tree\TreeSurvNodeEdit;

class TreeSurvAdmin extends TreeSurvNodeEdit
{
    protected $canEditTree = false;
    
    protected function initExtra(Request $request)
    {
        foreach ($this->allNodes as $nID => $nodeObj) {
            $this->allNodes[$nID]->fillNodeRow();
        }
        $this->canEditTree = ($this->v["uID"] > 0 && $this->v["user"]->hasRole('administrator|databaser'));
        return true;
    }
    
    protected function adminBasicPrintNode($tierNode = [], $tierDepth = 0)
    {
        $tierDepth++;
        if (!isset($this->v["pageCnt"])) {
            $this->v["pageCnt"] = 0;
        }
        if (sizeof($tierNode) > 0 && $tierNode[0] > 0) {
            if (isset($this->allNodes[$tierNode[0]]) && $this->hasNode($tierNode[0])) {
                $this->allNodes[$tierNode[0]]->fillNodeRow();
                if ($this->allNodes[$tierNode[0]]->isPage()) {
                    $this->v["pageCnt"]++;
                }
                $nodePromptText = '';
                if (isset($this->allNodes[$tierNode[0]]->nodeRow->NodePromptText)) {
                    $nodePromptText = $this->allNodes[$tierNode[0]]->nodeRow->NodePromptText;
                }
                $styPos = strpos($nodePromptText, '<style>');
                if ($styPos !== false) {
                    $styPosEnd = strpos($nodePromptText, '</style>', $styPos);
                    $nodePromptText = substr($nodePromptText, 0, $styPos) . substr($nodePromptText, 8+$styPosEnd);
                }
                $nodePromptText = strip_tags($nodePromptText);
                $childrenPrints = '';
                if (sizeof($tierNode[1]) > 0) { 
                    foreach ($tierNode[1] as $next) {
                        $childrenPrints .= $this->adminBasicPrintNode($next, $tierDepth);
                    }
                }
                $dataManips = $this->allNodes[$tierNode[0]]->printManipUpdate();
                if (trim($dataManips) != '') {
                    $dataManips = '<span class="fPerc80 mL5">' . $dataManips . '</span>';
                }
                $conditionList = (sizeof($this->allNodes[$tierNode[0]]->conds) == 0) ? ''
                    : '<span class="slGreenDark opac50 mL10"><i class="fa fa-filter" aria-hidden="true"></i>' 
                        . view('vendor.survloop.admin.tree.node-list-conditions', [
                            "conds"      => $this->allNodes[$tierNode[0]]->conds,
                            "nID"        => $tierNode[0],
                            "hideDeets"  => true
                        ])->render() . '</span>';
                if (!isset($GLOBALS["SL"]->x["hideDisabledNodes"]) || !$GLOBALS["SL"]->x["hideDisabledNodes"]
                    || strpos($conditionList, '#NodeDisabled') === false) {
                    $nodeBtns = view('vendor.survloop.admin.tree.node-print-basic-btns', [
                            "nID"            => $tierNode[0], 
                            "node"           => $this->allNodes[$tierNode[0]], 
                            "tierNode"       => $tierNode, 
                            "canEditTree"    => $this->canEditTree, 
                            "isPrint"        => $this->v["isPrint"],
                            "isAlt"          => $this->v["isAlt"]
                        ])->render();
                    $nodeBtnExpand = view('vendor.survloop.admin.tree.node-print-basic-btn-expand', [
                            "nID"            => $tierNode[0], 
                            "node"           => $this->allNodes[$tierNode[0]], 
                            "tierNode"       => $tierNode, 
                            "isPrint"        => $this->v["isPrint"],
                            "isAlt"          => $this->v["isAlt"]
                        ])->render();
                    $instructPrint = str_replace('node' . $tierNode[0], 'nPrint' . $tierNode[0], 
                        str_replace('<div class="nodeHalfGap"></div>', '', 
                        (($this->allNodes[$tierNode[0]]->isInstruct() || $this->allNodes[$tierNode[0]]->isInstructRaw())
                            ? $this->printNodePublic($tierNode[0]) : '')));
                    $instructPrint = $GLOBALS["SL"]->extractJava($GLOBALS["SL"]->extractStyle($instructPrint, -3, true), -3, true);
                    if (intVal($tierNode[0]) > 0 && isset($this->allNodes[$tierNode[0]])) {
                        return view('vendor.survloop.admin.tree.node-print-basic', [
                            "rootID"         => $this->rootID, 
                            "nID"            => $tierNode[0], 
                            "node"           => $this->allNodes[$tierNode[0]], 
                            "nodePromptText" => $nodePromptText,
                            "tierNode"       => $tierNode, 
                            "tierDepth"      => $tierDepth, 
                            "childrenPrints" => $childrenPrints,
                            "dataManips"     => $dataManips,
                            "conditionList"  => $conditionList,
                            "nodeBtns"       => $nodeBtns,
                            "nodeBtnExpand"  => $nodeBtnExpand,
                            "REQ"            => $GLOBALS["SL"]->REQ,
                            "canEditTree"    => $this->canEditTree, 
                            "isPrint"        => $this->v["isPrint"],
                            "isAll"          => $this->v["isAll"],
                            "isAlt"          => $this->v["isAlt"],
                            "pageCnt"        => $this->v["pageCnt"], 
                            "instructPrint"  => $instructPrint
                        ])->render();
                    }
                }
            }
        }
        return '';
    }
    
    public function adminPrintFullTree(Request $request, $pubPrint = false)
    {
        $ret = '';
        $this->v["printFullTree"] = true;
        if ($pubPrint) {
            $this->v["isPrint"] = $this->v["isAll"] = $this->v["isAlt"] = true;
        }
        $this->loadTree();
        $this->initExtra($request);
        if ($pubPrint) {
            $this->v["isPrint"] = true;
        }
        $this->checkTreeSessOpts();
        $this->treeAdminNodeManip();
        $this->loadTreeNodeStats();
        if ($GLOBALS["SL"]->REQ->has('dataStruct')) {
            
        }
        $pageJava = $GLOBALS['SL']->pageJAVA;
        $GLOBALS['SL']->pageJAVA = '';
        if ($pubPrint) {
            $ret = $this->adminBasicPrintNode($this->nodeTiers, -1);
        }
        $ret = view('vendor.survloop.admin.tree.node-print-wrap', [
            "adminBasicPrint" => $this->adminBasicPrintNode($this->nodeTiers, -1), 
            "canEditTree"     => $this->canEditTree,
            "isPrint"         => $this->v["isPrint"]
            ])->render() . '<script type="text/javascript"> $(document).ready(function(){ ' 
            . view('vendor.survloop.admin.tree.node-print-wrap-ajax', [
                "canEditTree" => $this->canEditTree
            ])->render() . ' }); </script>';
        $GLOBALS['SL']->pageJAVA = $pageJava;
        return $ret;
    }
    
    
    
    protected function adminResponseNodeStatsTxt($res, $fnlCnt, $atmptCnt, $fnlVals, $nAtmpts, $nodeSess)
    {
        $stats = [
            '0% of ' . ((is_array($nodeSess)) ? sizeof($nodeSess) : 0) . ' final submissions',
            '0% of all attempts'
            ];
        if (isset($fnlVals[strtolower($res)])) {
            $stats[0] = '<b>' . round(100*$fnlVals[strtolower($res)]/$fnlCnt) . '%</b> of ' 
                . ((is_array($nodeSess)) ? sizeof($nodeSess) : 0) . ' final submissions';
        }
        if (isset($nAtmpts[strtolower($res)])) {
            $stats[1] = round(100*$nAtmpts[strtolower($res)]/$atmptCnt) . '% of all ' . $atmptCnt . ' attempts';
        }
        return $stats;
    }
    
    protected function adminResponseNodeStats($tierNode = [], $tierDepth = 0)
    {
        $retVal = '';
        $tierDepth++;
        if (sizeof($tierNode) > 0 && $tierNode[0] > 0) {
            $nID = $tierNode[0];
            if ($this->hasNode($nID) && (sizeof($tierNode[1]) > 0) 
                || (!$this->allNodes[$nID]->isDataManip() && !$this->allNodes[$nID]->isInstructAny())) {
                $fnlCnt = $atmptCnt = 0;
                $fnlVals = $nAtmpts = $nodeSess = [];
                $nodeSaves = SLNodeSaves::where('NodeSaveNode', $nID)
                    ->orderBy('created_at', 'desc')
                    ->get();
                if ($nodeSaves->isNotEmpty()) {
                    foreach ($nodeSaves as $save) {
                        if (strlen($save->NodeSaveNewVal) > 100) {
                            $save->NodeSaveNewVal = substr($save->NodeSaveNewVal, 0, 100) . '...';
                        }
                        $responses = [];
                        $str2arr = $GLOBALS["SL"]->str2arr($save->NodeSaveNewVal);
                        if (sizeof($str2arr) > 0 && $str2arr[0] != 'EMPTY ARRAY') {
                            $responses = $str2arr;
                        } elseif (!is_array($save->NodeSaveNewVal)) {
                            if ($this->allNodes[$nID]->nodeType == 'Checkbox' 
                                && strpos($save->NodeSaveNewVal, ';;') !== false) {
                                $responses = explode(';;', $save->NodeSaveNewVal);
                            } else {
                                $responses[] = $save->NodeSaveNewVal;
                            }
                        }
                        if (!isset($nodeSess[$save->NodeSaveSession])) {
                            $nodeSess[$save->NodeSaveSession] = 1;
                            foreach ($responses as $j => $res) {
                                if (!isset($fnlVals[strtolower($res)])) {
                                    $fnlVals[strtolower($res)] = 0;
                                }
                                $fnlVals[strtolower($res)]++;
                                $fnlCnt++;
                            }
                        } else {
                            $nodeSess[$save->NodeSaveSession]++;
                        }
                        foreach ($responses as $j => $res) {
                            if (!isset($nAtmpts[strtolower($res)])) $nAtmpts[strtolower($res)] = 0;
                            $nAtmpts[strtolower($res)]++;
                            $atmptCnt++;
                        }
                    }
                }
                if ($this->allNodes[$nID]->nodeType == 'U.S. States') {
                    $GLOBALS["SL"]->loadStates();
                    foreach ($GLOBALS["SL"]->states->stateList as $abbr => $name) {
                        $ind = sizeof($this->allNodes[$nID]->responses);
                        $this->allNodes[$nID]->responses[$ind] = new SLNodeResponses;
                        $this->allNodes[$nID]->responses[$ind]->NodeResValue = $abbr;
                        $this->allNodes[$nID]->responses[$ind]->NodeResEng = $name;
                        $this->allNodes[$nID]->responses[$ind]->NodeResShowKids = 0;
                        $this->allNodes[$nID]->responses[$ind]->NodeResMutEx = 0;
                    }
                }
                
                $nodeAJAX = '';
                $retVal .= '<div class="nodeAnchor"><a id="n' . $nID . '" name="n' . $nID 
                    . '"></a></div><div class="basicTier' . (($tierDepth < 10) ? $tierDepth : 9) . '"><div>';
                
                if (!$this->allNodes[$nID]->isSpecial()) {
                    $retVal .= '<span class="slBlueDark mR5">#' . $nID . '</span> ';
                    if ($this->allNodes[$nID]->isBranch()) {
                        $retVal .= '<h3 class="disIn slGrey"><i class="fa fa-share-alt"></i> ' 
                        . (($nID == $this->rootID) ? 'Tree Root Node' : 'Section Branch') . ': ' 
                        . $this->allNodes[$nID]->nodeRow->NodePromptText . '</h3><div class="pT5"><a href="#n' 
                        . $nID . '" id="adminNode' . $nID . 'Expand" class="adminNodeExpand '
                        . 'slBlueDark noUnd"><i class="fa fa-expand fa-flip-horizontal"></i></a></div>';
                    } else { // non-branch nodes
                        list($tbl, $fld) = $this->allNodes[$nID]->getTblFld();
                        $questionText = trim(strip_tags($this->allNodes[$nID]->nodeRow->NodePromptText));
                        if ($questionText == '') {
                            if ($this->allNodes[$nID]->nodeType == 'Checkbox' 
                                && isset($this->allNodes[$nID]->responses) 
                                && sizeof($this->allNodes[$nID]->responses) == 1
                                && isset($this->allNodes[$nID]->responses[0]->NodeResEng)) {
                                $questionText = $this->allNodes[$nID]->responses[0]->NodeResEng;
                            } else {
                                $questionText = $GLOBALS["SL"]->getFldTitle($tbl, $fld);
                            }
                        }
                        $retVal .= '<h3 class="disIn">' . $questionText . (($this->allNodes[$nID]->isRequired()) 
                            ? ' <span class="txtDanger">*</span> ' : '') . '</h3><div class="pT5">';
                        if (sizeof($tierNode[1]) > 0) {
                            $retVal .= '<a href="#n' . $nID . '" id="adminNode' . $nID . 'Expand" '
                                . 'class="slBlueDark noUnd"><i class="fa fa-expand fa-flip-horizontal"'
                                . '></i></a>&nbsp;&nbsp;&nbsp;';
                        }
                        if (isset($this->allNodes[$nID]->responses) 
                            && sizeof($this->allNodes[$nID]->responses) > 0) {
                            foreach ($this->allNodes[$nID]->responses as $j => $res) {
                                $stats = $this->adminResponseNodeStatsTxt(strtolower($res->NodeResValue), $fnlCnt, 
                                    $atmptCnt, $fnlVals, $nAtmpts, $nodeSess);
                                if ($this->allNodes[$nID]->responses[0] != '[U.S.States]' 
                                    || isset($fnlVals[strtolower($res->NodeResValue)]) 
                                    || isset($nAtmpts[strtolower($res->NodeResValue)])) {
                                    $retVal .= '<div class="row p15 m0' . (($j%2 == 0) ? ' row2' : '') 
                                        . '"><div class="col-6 fPerc133">' 
                                        . $GLOBALS["SL"]->printResponse($tbl, $fld, $res->NodeResValue);
                                    if (isset($res->NodeResShowKids) && $res->NodeResShowKids > 0) {
                                        $retVal .= '<i class="fa fa-code-fork fa-flip-vertical mL5" title="Children '
                                            . 'displayed if selected"></i>';
                                    }
                                    $retVal .= '</div><div class="col-3 slBlueDark">' . $stats[0] 
                                        . '</div><div class="col-3 slGrey">' . $stats[1] . '</div></div>';
                                }
                            }
                        } elseif (sizeof($fnlVals) > 0) {
                            arsort($fnlVals);
                            $j=0;
                            foreach ($fnlVals as $res => $cnt) {
                                $stats = $this->adminResponseNodeStatsTxt(strtolower($res), $fnlCnt, $atmptCnt, 
                                    $fnlVals, $nAtmpts, $nodeSess);
                                $retVal .= '<div class="row p15 m0' . (($j%2 == 0) ? ' row2' : '') 
                                    . '"><div class="col-6 fPerc133">' . ((trim($res) != '') 
                                        ? $GLOBALS["SL"]->printResponse($tbl, $fld, $res)
                                        : '<span class="slGrey"><i>(empty)</i></span>')
                                    . '</div><div class="col-3 slBlueDark">' . $stats[0] 
                                    . '</div><div class="col-3 slGrey">' . $stats[1] . '</div></div>';
                                if ($j == 9) {
                                    $retVal .= '<a name="n' . $nID . 'more"></a><a href="#n' . $nID . 'more" id="show' 
                                        . $nID . 'Response' . $j . 'Stats">show more</a></div><div id="more' . $nID 
                                        . 'Response' . $j . 'Stats" class="disNon">';
                                    $nodeAJAX .= '$("#show' . $nID . 'Response' . $j 
                                        . 'Stats").click(function(){ $("#more' . $nID . 'Response' . $j 
                                        . 'Stats").slideToggle("fast"); }); ' . "\n";
                                }
                                $j++;
                            }
                        }
                        $retVal .= '</div>';
                    }
                }
                if (sizeof($tierNode[1]) > 0) { 
                    $retVal .= '<div id="nodeKids' . $nID . '" class="dis' 
                        . ((session()->get('adminOverOpts')%2 == 0 || $nID == $this->rootID) ? 'Blo' : 'Non') . '">';
                    foreach ($tierNode[1] as $next) {
                        $retVal .= $this->adminResponseNodeStats($next, $tierDepth);
                    }
                    $retVal .= '</div>';
                }
                $retVal .= '</div></div>';
                $GLOBALS["SL"]->pageAJAX .= '$("#adminNode' . $nID . 'Expand").click(function(){ $("#nodeKids' . $nID 
                    . '").slideToggle("fast"); }); ' . $nodeAJAX;
            }
        }
        return $retVal;
    }
    
    public function adminPrintFullTreeStats(Request $request) 
    {
        $this->loadTree();
        $this->initExtra($request);
        $this->checkTreeSessOpts();
        return $this->adminResponseNodeStats($this->nodeTiers, -1);
    }
    
    
    
    public function checkTreeSessOpts()
    {
        if (!session()->has('adminOverOpts')) {
            session()->put('adminOverOpts', 2);
        }
        if ($GLOBALS["SL"]->REQ->has('all')) {
            if (session()->get('adminOverOpts')%2 > 0) {
                session()->put('adminOverOpts', (2*session()->get('adminOverOpts')));
            }
        } elseif (session()->get('adminOverOpts')%2 == 0) {
            session()->put('adminOverOpts', (session()->get('adminOverOpts')/2));
        }
        return true;
    }
    
    
    
    
    protected function adminBasicDropdownNode($tierNode = [], $tierDepth = 0, $preSel = -3)
    {
        $retVal = '';
        $tierDepth++;
        if (sizeof($tierNode) > 0 && $tierNode[0] > 0) { 
            $nID = $tierNode[0]; 
            if ($this->hasNode($nID)) {
                $indent = '';
                for ($i=0; $i<$tierDepth; $i++) {
                    $indent .= ' - ';
                }
                $nodeName = $this->allNodes[$nID]->nodeRow->NodePromptText;
                $retVal .= '<option value="' . $nID . '" ' . ((intVal($preSel) == $nID) ? 'SELECTED' : '') . ' >' 
                    . $indent . ((strlen($nodeName) > 70) ? substr($nodeName, 0, 70).'...' : $nodeName) . '</option>';
                if (sizeof($tierNode[1]) > 0) {
                    foreach ($tierNode[1] as $next) {
                        $retVal .= $this->adminBasicDropdownNode($next, $tierDepth, $preSel);
                    }
                }
            }
        }
        return $retVal;
    }
    
    protected function adminBasicDropdown($preSel = -3)
    {
        return '<select name="nodeID" style="width: 100%;">
            <option value="-3" ' . ((intVal($preSel) <= 0) ? 'SELECTED' : '') . ' >select tree node</option>
            ' . $this->adminBasicDropdownNode($this->nodeTiers, -1, $preSel) . '
            </select>';
    }
    
    protected function updateTreeEnds()
    {
        $GLOBALS["SL"]->treeRow->TreeFirstPage = $GLOBALS["SL"]->treeRow->TreeLastPage = -3;
        foreach ($this->nodesRawOrder as $nID) {
            if (isset($this->allNodes[$nID]) 
                && ($this->allNodes[$nID]->isPage() || $this->allNodes[$nID]->isLoopRoot())) {
                if ($GLOBALS["SL"]->treeRow->TreeFirstPage <= 0) $GLOBALS["SL"]->treeRow->TreeFirstPage = $nID;
                $GLOBALS["SL"]->treeRow->TreeLastPage = $nID;
            }
        }
        $GLOBALS["SL"]->treeRow->save();
        return true;
    }
    
    protected function updateLoopRoots()
    {
        $nodes = SLNode::where('NodeTree', $this->treeID)
            ->where('NodeType', 'Loop Root')
            ->select('NodeID', 'NodeDataBranch')->get();
        foreach ($nodes as $row) {
            SLDataLoop::where('DataLoopTree', $this->treeID)
                ->where('DataLoopPlural', $row->NodeDataBranch)
                ->update(['DataLoopRoot' => $row->NodeID]);
        }
        return true;
    }
    
    protected function updateBranchUrls()
    {
        $branches = SLNode::where('NodeTree', $this->treeID)
            ->where('NodeType', 'Branch Title')
            ->get();
        foreach ($branches as $branch) {
            $nextNode = $this->getNextNonBranch($branch->NodeID);
            if ($nextNode > 0) {
                $page = SLNode::find($nextNode);
                if ($page && isset($page->NodePromptNotes)) {
                    $branch->NodePromptNotes = $page->NodePromptNotes;
                    $branch->save();
                }
            }
        }
        return true;
    }
    
}
