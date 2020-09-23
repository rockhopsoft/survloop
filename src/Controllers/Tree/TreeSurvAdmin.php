<?php
/**
  * TreeSurvAdmin is a higher-level class extending Survloop's core tree class
  * with tools to edit the tree itself.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

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
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvNodeEdit;

class TreeSurvAdmin extends TreeSurvNodeEdit
{
    protected function adminBasicPrintNode($tierNode = [], $tierDepth = 0)
    {
        $ret = '';
        $tierDepth++;
        if (!isset($this->v["pageCnt"])) {
            $this->v["pageCnt"] = 0;
        }
        if (sizeof($tierNode) > 0 && $tierNode[0] > 0) {
            if (isset($this->allNodes[$tierNode[0]]) 
                && $this->hasNode($tierNode[0])) {
                $this->allNodes[$tierNode[0]]->fillNodeRow();
                if ($this->allNodes[$tierNode[0]]->isPage()) {
                    $this->v["pageCnt"]++;
                }
                $nodePromptText = '';
                if (isset($this->allNodes[$tierNode[0]]->nodeRow->node_prompt_text)) {
                    $nodePromptText = $this->allNodes[$tierNode[0]]->nodeRow->node_prompt_text;
                    $nodePromptText = stripslashes($nodePromptText);
                }
                $styPos = strpos($nodePromptText, '<style>');
                if ($styPos !== false) {
                    $styPosEnd = strpos($nodePromptText, '</style>', $styPos);
                    $nodePromptText = substr($nodePromptText, 0, $styPos) 
                        . substr($nodePromptText, 8+$styPosEnd);
                }
                $nodePromptText = strip_tags($nodePromptText);
                $childrenPrints = '';
                if (sizeof($tierNode[1]) > 0) { 
                    foreach ($tierNode[1] as $next) {
                        $childrenPrints .= $this->adminBasicPrintNode(
                            $next, 
                            $tierDepth
                        );
                    }
                }
                $dataManips = $this->allNodes[$tierNode[0]]->printManipUpdate();
                if (trim($dataManips) != '') {
                    $dataManips = '<span class="fPerc80 mL5">' . $dataManips . '</span>';
                }
                $condList = view(
                    'vendor.survloop.admin.tree.node-list-conditions', 
                    [
                        "conds"     => $this->allNodes[$tierNode[0]]->conds,
                        "nID"       => $tierNode[0],
                        "hideDeets" => true
                    ]
                )->render();
                if (sizeof($this->allNodes[$tierNode[0]]->conds) > 0) {
                    $condList = '<span class="slGreenDark opac50 mL10">'
                            . '<i class="fa fa-filter" aria-hidden="true"></i>' 
                            . $condList . '</span>';
                }
                if (!isset($GLOBALS["SL"]->x["hideDisabledNodes"]) 
                    || !$GLOBALS["SL"]->x["hideDisabledNodes"]
                    || strpos($condList, '#NodeDisabled') === false) {
                    $ret .= $this->adminBasicPrintNodeInner(
                        $tierNode, 
                        $tierDepth, 
                        $nodePromptText,
                        $childrenPrints,
                        $dataManips,
                        $condList
                    );
                }
            }
        }
        return $ret;
    }

    protected function adminBasicPrintNodeInner($tierNode, $tierDepth, $nodePromptText, $childrenPrints, $dataManips, $condList)
    {
        $nodeBtns = view(
            'vendor.survloop.admin.tree.node-print-basic-btns', 
            [
                "nID"         => $tierNode[0], 
                "node"        => $this->allNodes[$tierNode[0]], 
                "tierNode"    => $tierNode, 
                "canEditTree" => $this->canEditTree, 
                "isPrint"     => $this->v["isPrint"],
                "isAlt"       => $this->v["isAlt"]
            ]
        )->render();
        $nodeBtnExpand = view(
            'vendor.survloop.admin.tree.node-print-basic-btn-expand', 
            [
                "nID"      => $tierNode[0], 
                "node"     => $this->allNodes[$tierNode[0]], 
                "tierNode" => $tierNode, 
                "isPrint"  => $this->v["isPrint"],
                "isAlt"    => $this->v["isAlt"]
            ]
        )->render();
        $instructPrint = '';
        if ($this->allNodes[$tierNode[0]]->isInstruct() 
            || $this->allNodes[$tierNode[0]]->isInstructRaw()) {
            $instructPrint = $this->printNodePublic($tierNode[0]);
        }
        $instructPrint = str_replace('<div class="nodeHalfGap"></div>', '', $instructPrint);
        $instructPrint = str_replace('nPrint' . $tierNode[0], '', $instructPrint);
        $instructPrint = str_replace('node' . $tierNode[0], '', $instructPrint);
        $instructPrint = $GLOBALS["SL"]->extractStyle($instructPrint, -3, true);
        $instructPrint = $GLOBALS["SL"]->extractJava($instructPrint, -3, true);
        if (intVal($tierNode[0]) > 0 && isset($this->allNodes[$tierNode[0]])) {
            return view(
                'vendor.survloop.admin.tree.node-print-basic', 
                [
                    "rootID"         => $this->rootID, 
                    "nID"            => $tierNode[0], 
                    "node"           => $this->allNodes[$tierNode[0]], 
                    "nodePromptText" => $nodePromptText,
                    "tierNode"       => $tierNode, 
                    "tierDepth"      => $tierDepth, 
                    "childrenPrints" => $childrenPrints,
                    "dataManips"     => $dataManips,
                    "conditionList"  => $condList,
                    "nodeBtns"       => $nodeBtns,
                    "nodeBtnExpand"  => $nodeBtnExpand,
                    "REQ"            => $GLOBALS["SL"]->REQ,
                    "canEditTree"    => $this->canEditTree, 
                    "isPrint"        => $this->v["isPrint"],
                    "isAll"          => $this->v["isAll"],
                    "isAlt"          => $this->v["isAlt"],
                    "pageCnt"        => $this->v["pageCnt"], 
                    "instructPrint"  => $instructPrint
                ]
            )->render();
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
        $retAjax = view(
            'vendor.survloop.admin.tree.node-print-wrap-ajax', 
            [ "canEditTree" => $this->canEditTree ]
        )->render();
        $ret = view(
            'vendor.survloop.admin.tree.node-print-wrap', [
                "adminBasicPrint" => $this->adminBasicPrintNode($this->nodeTiers, -1), 
                "canEditTree"     => $this->canEditTree,
                "isPrint"         => $this->v["isPrint"]
            ]
        )->render();
        $ret = $ret . '<script type="text/javascript"> $(document).ready(function(){ '
            . $retAjax . ' }); </script>';
        $GLOBALS['SL']->pageJAVA = $pageJava;
        return $ret;
    }
    
    
    
    protected function adminResponseNodeStatsTxt($res, $fnlCnt, $atmptCnt, $fnlVals, $nAtmpts, $nodeSess)
    {
        $totCnt = ((is_array($nodeSess)) ? sizeof($nodeSess) : 0);
        $stats = [
            '0% of ' . $totCnt . ' final submissions',
            '0% of all attempts'
        ];
        if (isset($fnlVals[strtolower($res)])) {
            $stats[0] = '<b>' . round(100*$fnlVals[strtolower($res)]/$fnlCnt) 
                . '%</b> of ' . $totCnt . ' final submissions';
        }
        if (isset($nAtmpts[strtolower($res)])) {
            $stats[1] = round(100*$nAtmpts[strtolower($res)]/$atmptCnt) 
                . '% of all ' . $atmptCnt . ' attempts';
        }
        return $stats;
    }
    
    protected function adminResponseNodeStats($tierNode = [], $tierDepth = 0)
    {
        $retVal = '';
        $tierDepth++;
        if (sizeof($tierNode) > 0 && $tierNode[0] > 0) {
            $nID = $tierNode[0];
            if ($this->hasNode($nID) 
                && (sizeof($tierNode[1]) > 0) 
                    || (!$this->allNodes[$nID]->isDataManip() 
                        && !$this->allNodes[$nID]->isInstructAny())) {
                $fnlCnt = $atmptCnt = 0;
                $fnlVals = $nAtmpts = $nodeSess = [];
                $nodeSaves = SLNodeSaves::where('node_save_node', $nID)
                    ->orderBy('created_at', 'desc')
                    ->get();
                if ($nodeSaves->isNotEmpty()) {
                    foreach ($nodeSaves as $save) {
                        if (strlen($save->node_save_new_val) > 100) {
                            $save->node_save_new_val = substr($save->node_save_new_val, 0, 100);
                            $save->node_save_new_val = trim($save->node_save_new_val) . '...';
                        }
                        $responses = [];
                        $str2arr = $GLOBALS["SL"]->str2arr($save->node_save_new_val);
                        if (sizeof($str2arr) > 0 && $str2arr[0] != 'EMPTY ARRAY') {
                            $responses = $str2arr;
                        } elseif (!is_array($save->node_save_new_val)) {
                            if ($this->allNodes[$nID]->nodeType == 'Checkbox' 
                                && strpos($save->node_save_new_val, ';;') !== false) {
                                $responses = explode(';;', $save->node_save_new_val);
                            } else {
                                $responses[] = $save->node_save_new_val;
                            }
                        }
                        if (!isset($nodeSess[$save->node_save_session])) {
                            $nodeSess[$save->node_save_session] = 1;
                            foreach ($responses as $j => $res) {
                                if (!isset($fnlVals[strtolower($res)])) {
                                    $fnlVals[strtolower($res)] = 0;
                                }
                                $fnlVals[strtolower($res)]++;
                                $fnlCnt++;
                            }
                        } else {
                            $nodeSess[$save->node_save_session]++;
                        }
                        foreach ($responses as $j => $res) {
                            if (!isset($nAtmpts[strtolower($res)])) {
                                $nAtmpts[strtolower($res)] = 0;
                            }
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
                        $this->allNodes[$nID]->responses[$ind]->node_res_value = $abbr;
                        $this->allNodes[$nID]->responses[$ind]->node_res_eng = $name;
                        $this->allNodes[$nID]->responses[$ind]->node_res_show_kids = 0;
                        $this->allNodes[$nID]->responses[$ind]->node_res_mut_ex = 0;
                    }
                }
                
                $nodeAJAX = '';
                $retVal .= '<div class="nodeAnchor"><a id="n' . $nID 
                    . '" name="n' . $nID . '"></a></div><div class="basicTier' 
                    . (($tierDepth < 10) ? $tierDepth : 9) . '"><div>';
                
                if (!$this->allNodes[$nID]->isSpecial()) {
                    $retVal .= '<span class="slBlueDark mR5">#' . $nID . '</span> ';
                    if ($this->allNodes[$nID]->isBranch()) {
                        $retVal .= '<h3 class="disIn slGrey"><i class="fa fa-share-alt"></i> ' 
                            . (($nID == $this->rootID) ? 'Tree Root Node' : 'Section Branch') . ': ' 
                            . $this->allNodes[$nID]->nodeRow->node_prompt_text 
                            . '</h3><div class="pT5"><a href="#n' . $nID . '" id="adminNode' . $nID 
                            . 'Expand" class="adminNodeExpand slBlueDark noUnd">'
                            . '<i class="fa fa-expand fa-flip-horizontal"></i></a></div>';
                    } else { // non-branch nodes
                        list($tbl, $fld) = $this->allNodes[$nID]->getTblFld();
                        $questionText = $this->allNodes[$nID]->nodeRow->node_prompt_text;
                        $questionText = trim(strip_tags($questionText));
                        if ($questionText == '') {
                            if ($this->allNodes[$nID]->nodeType == 'Checkbox' 
                                && isset($this->allNodes[$nID]->responses) 
                                && sizeof($this->allNodes[$nID]->responses) == 1
                                && isset($this->allNodes[$nID]->responses[0]->node_res_eng)) {
                                $questionText = $this->allNodes[$nID]->responses[0]->node_res_eng;
                            } else {
                                $questionText = $GLOBALS["SL"]->getFldTitle($tbl, $fld);
                            }
                        }
                        $req = (($this->allNodes[$nID]->isRequired()) 
                            ? ' <span class="txtDanger">*</span> ' : '');
                        $retVal .= '<h3 class="disIn">' . $questionText . $req . '</h3><div class="pT5">';
                        if (sizeof($tierNode[1]) > 0) {
                            $retVal .= '<a href="#n' . $nID . '" id="adminNode' . $nID . 'Expand" '
                                . 'class="slBlueDark noUnd"><i class="fa fa-expand fa-flip-horizontal"'
                                . '></i></a>&nbsp;&nbsp;&nbsp;';
                        }
                        if (isset($this->allNodes[$nID]->responses) 
                            && sizeof($this->allNodes[$nID]->responses) > 0) {
                            foreach ($this->allNodes[$nID]->responses as $j => $res) {
                                $val = strtolower($res->node_res_value);
                                $stats = $this->adminResponseNodeStatsTxt(
                                    $val, 
                                    $fnlCnt, 
                                    $atmptCnt, 
                                    $fnlVals, 
                                    $nAtmpts, 
                                    $nodeSess
                                );
                                if ($this->allNodes[$nID]->responses[0] != '[U.S.States]' 
                                    || isset($fnlVals[strtolower($res->node_res_value)]) 
                                    || isset($nAtmpts[strtolower($res->node_res_value)])) {
                                    $retVal .= '<div class="row p15 m0' . (($j%2 == 0) ? ' row2' : '') 
                                        . '"><div class="col-6">' 
                                        . $GLOBALS["SL"]->printResponse($tbl, $fld, $res->node_res_value);
                                    if (isset($res->node_res_show_kids) && $res->node_res_show_kids > 0) {
                                        $retVal .= '<i class="fa fa-code-fork fa-flip-vertical mL5" '
                                            . 'title="Children displayed if selected"></i>';
                                    }
                                    $retVal .= '</div><div class="col-3 slBlueDark">' . $stats[0] . '</div>'
                                        . '<div class="col-3 slGrey">' . $stats[1] . '</div></div>';
                                }
                            }
                        } elseif (sizeof($fnlVals) > 0) {
                            arsort($fnlVals);
                            $j=0;
                            foreach ($fnlVals as $res => $cnt) {
                                $stats = $this->adminResponseNodeStatsTxt(
                                    strtolower($res), 
                                    $fnlCnt, 
                                    $atmptCnt, 
                                    $fnlVals, 
                                    $nAtmpts, 
                                    $nodeSess
                                );
                                $retVal .= '<div class="row p15 m0' . (($j%2 == 0) ? ' row2' : '') 
                                    . '"><div class="col-6 fPerc133">' . ((trim($res) != '') 
                                        ? $GLOBALS["SL"]->printResponse($tbl, $fld, $res)
                                        : '<span class="slGrey"><i>(empty)</i></span>')
                                    . '</div><div class="col-3 slBlueDark">' . $stats[0] 
                                    . '</div><div class="col-3 slGrey">' . $stats[1] . '</div></div>';
                                if ($j == 9) {
                                    $retVal .= '<div class="nodeAnchor"><a name="n' . $nID 
                                        . 'more"></a></div><a href="#n' . $nID 
                                        . 'more" id="show' . $nID . 'Response' . $j 
                                        . 'Stats">show more</a></div><div id="more' . $nID 
                                        . 'Response' . $j . 'Stats" class="disNon">';
                                    $nodeAJAX .= '$("#show' . $nID . 'Response' . $j 
                                        . 'Stats").click(function(){ $("#more' . $nID . 'Response' 
                                        . $j . 'Stats").slideToggle("fast"); }); ' . "\n";
                                }
                                $j++;
                            }
                        }
                        $retVal .= '</div>';
                    }
                }
                if (sizeof($tierNode[1]) > 0) { 
                    $dis = 'Non';
                    if (session()->get('adminOverOpts')%2 == 0 || $nID == $this->rootID) {
                        $dis = 'Blo';
                    }
                    $retVal .= '<div id="nodeKids' . $nID . '" class="dis' . $dis . '">';
                    foreach ($tierNode[1] as $next) {
                        $retVal .= $this->adminResponseNodeStats($next, $tierDepth);
                    }
                    $retVal .= '</div>';
                }
                $retVal .= '</div></div>';
                $GLOBALS["SL"]->pageAJAX .= '$("#adminNode' . $nID . 'Expand").click(function(){ '
                    . '$("#nodeKids' . $nID . '").slideToggle("fast"); }); ' . $nodeAJAX;
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
        session()->save();
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
                $nodeName = $this->allNodes[$nID]->nodeRow->node_prompt_text;
                if (strlen($nodeName) > 70) {
                    $nodeName = trim(substr($nodeName, 0, 70)) . '...';
                }
                $retVal .= '<option value="' . $nID . '" ' 
                    . ((intVal($preSel) == $nID) ? 'SELECTED' : '') 
                    . ' >' . $indent . $nodeName . '</option>';
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
        return '<select name="nodeID" style="width: 100%;">'
            . '<option value="-3" ' . ((intVal($preSel) <= 0) ? 'SELECTED' : '') 
            . ' >select tree node</option>
            ' . $this->adminBasicDropdownNode($this->nodeTiers, -1, $preSel) . '
            </select>';
    }
    
    protected function updateTreeEnds()
    {
        $GLOBALS["SL"]->treeRow->tree_first_page 
            = $GLOBALS["SL"]->treeRow->tree_last_page = 0;
        foreach ($this->nodesRawOrder as $nID) {
            if (isset($this->allNodes[$nID]) 
                && ($this->allNodes[$nID]->isPage() 
                    || $this->allNodes[$nID]->isLoopRoot())) {
                if ($GLOBALS["SL"]->treeRow->tree_first_page <= 0) {
                    $GLOBALS["SL"]->treeRow->tree_first_page = $nID;
                }
                $GLOBALS["SL"]->treeRow->tree_last_page = $nID;
            }
        }
        $GLOBALS["SL"]->treeRow->save();
        return true;
    }
    
    protected function updateLoopRoots()
    {
        $nodes = SLNode::where('node_tree', $this->treeID)
            ->where('node_type', 'Loop Root')
            ->select('node_id', 'node_default')->get();
        foreach ($nodes as $row) {
            SLDataLoop::where('data_loop_tree', $this->treeID)
                ->where('data_loop_plural', $row->node_default)
                ->update([ 'data_loop_root' => $row->node_id ]);
        }
        return true;
    }
    
    protected function updateBranchUrls()
    {
        $branches = SLNode::where('node_tree', $this->treeID)
            ->where('node_type', 'Branch Title')
            ->get();
        foreach ($branches as $branch) {
            $nextNode = $this->getNextNonBranch($branch->node_id);
            if ($nextNode > 0) {
                $page = SLNode::find($nextNode);
                if ($page && isset($page->node_prompt_notes)) {
                    $branch->node_prompt_notes = $page->node_prompt_notes;
                    $branch->save();
                }
            }
        }
        return true;
    }
    
}
