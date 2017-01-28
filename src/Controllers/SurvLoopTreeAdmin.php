<?php
namespace SurvLoop\Controllers;

use Cache;
use Illuminate\Http\Request;

use App\Models\SLDefinitions;
use App\Models\SLNode;
use App\Models\SLNodeSaves;
use App\Models\SLNodeResponses;
use App\Models\SLDataLoop;
use App\Models\SLConditionsNodes;

use SurvLoop\Controllers\SurvFormTree;

class SurvLoopTreeAdmin extends SurvFormTree
{
    
    public $classExtension = 'SurvLoopTreeAdmin';
    
    protected $canEditTree = false;
    
    protected function initExtra(Request $request)
    {
        foreach ($this->allNodes as $nID => $nodeObj) {
            $this->allNodes[$nID]->fillNodeRow();
        }
        $this->canEditTree = $this->v["user"]->hasRole('administrator|brancher');
        return true;
    }
    
    public function adminNodeEdit($nodeIN, Request $request, $currPage = '') 
    {
        $this->survLoopInit($request, $currPage);
        $resLimit = 20;
        $node = array();
        if ($nodeIN > 0) {
            if (sizeof($this->allNodes) > 0 && isset($this->allNodes[$nodeIN])) {
                $node = $this->allNodes[$nodeIN];
            } else {
                $node = $this->loadNode(SLNode::find($nodeIN));
            }
            $node->fillNodeRow($nodeIN);
        }
        if ($nodeIN <= 0 || !$node || sizeof($node) == 0) {
            $node = $this->loadNode();
            $node->nodeRow->NodeParentID    = $this->REQ->nodeParentID;
            $node->nodeRow->NodeParentOrder = 0;
            $node->nodeRow->NodeOpts        = 1;
            $node->nodeRow->NodeType        = 'Text';
        }
        
        if ($this->REQ->has('sub') && $this->canEditTree) {
            if ($this->REQ->has('deleteNode') && intVal($this->REQ->input('deleteNode')) == 1) {
                $this->treeAdminNodeDelete($node->nodeRow->NodeID);
            } else {
                if ($nodeIN <= 0) $node = $this->treeAdminNodeNew($node);
                
                if (intVal($node->nodeRow->NodeOpts) <= 1) $node->nodeRow->NodeOpts = 1;
                
                $node->nodeRow->NodePromptText      = trim($this->REQ->input('nodePromptText'));
                $node->nodeRow->NodePromptNotes     = trim($this->REQ->input('nodePromptNotes'));
                $node->nodeRow->NodePromptAfter     = trim($this->REQ->input('nodePromptAfter'));
                $node->nodeRow->NodeInternalNotes   = trim($this->REQ->input('nodeInternalNotes'));
                $node->nodeRow->NodeCharLimit       = intVal($this->REQ->input('nodeCharLimit'));
                $node->nodeRow->NodeTextSuggest     = trim($this->REQ->input('nodeTextSuggest'));
                $node->nodeRow->NodeDataBranch      = trim($this->REQ->input('nodeDataBranch'));
                $node->nodeRow->NodeDataStore       = trim($this->REQ->input('nodeDataStore'));
                if ($this->REQ->nodeType == 'branch') {
                    $node->nodeRow->NodeType = 'Branch Title';
                    $node->nodeRow->NodePromptText  = trim($this->REQ->input('branchTitle'));
                } elseif ($this->REQ->nodeType == 'instruct') {
                    $node->nodeRow->NodeType        = 'Instructions';
                    $node->nodeRow->NodePromptText  = trim($this->REQ->input('nodeInstruct'));
                } elseif ($this->REQ->nodeType == 'page') {
                    $node->nodeRow->NodeType        = 'Page';
                    $node->nodeRow->NodePromptNotes = trim($this->REQ->input('nodeSlug'));
                } elseif ($this->REQ->nodeType == 'loop') {
                    $node->nodeRow->NodeType        = 'Loop Root';
                    $node->nodeRow->NodePromptText  = trim($this->REQ->input('nodeLoopInstruct'));
                    $node->nodeRow->NodePromptNotes = trim($this->REQ->input('loopSlug'));
                    $node->nodeRow->NodeDataBranch  = $loop = trim($this->REQ->input('nodeDataLoop'));
                    if (!isset($GLOBALS["DB"]->dataLoops[$loop])) {
                        $GLOBALS["DB"]->dataLoops[$loop] = new SLDataLoop;
                        $GLOBALS["DB"]->dataLoops[$loop]->DataLoopTree = $this->treeID;
                        $GLOBALS["DB"]->dataLoops[$loop]->DataLoopRoot = $node->nodeRow->NodeID;
                    }
                    $GLOBALS["DB"]->dataLoops[$loop]->DataLoopIsStep = 0;
                    $GLOBALS["DB"]->dataLoops[$loop]->DataLoopAutoGen = 1;
                    $GLOBALS["DB"]->dataLoops[$loop]->DataLoopDoneFld = '';
                    if ($this->REQ->has('stepLoop') && intVal($this->REQ->input('stepLoop')) == 1) {
                        $GLOBALS["DB"]->dataLoops[$loop]->DataLoopIsStep = 1;
                        $GLOBALS["DB"]->dataLoops[$loop]->DataLoopAutoGen = 0;
                        if ($this->REQ->has('stepLoopDoneField') 
                            && trim($this->REQ->input('stepLoopDoneField')) != '') {
                            $GLOBALS["DB"]->dataLoops[$loop]->DataLoopDoneFld 
                                = trim($this->REQ->input('stepLoopDoneField'));
                        }
                    } elseif (!$this->REQ->has('stdLoopAuto') || intVal($this->REQ->input('stdLoopAuto')) == 0) {
                        $GLOBALS["DB"]->dataLoops[$loop]->DataLoopAutoGen = 0;
                    }
                    $GLOBALS["DB"]->dataLoops[$loop]->save();
                } elseif ($this->REQ->nodeType == 'data') {
                    $node->nodeRow->NodeType             = 'Data Manip: ' . $this->REQ->input('dataManipType');
                    $node->nodeRow->NodeDataStore         = trim($this->REQ->input('manipMoreStore'));
                    $node->nodeRow->NodeDefault         = trim($this->REQ->input('manipMoreVal'));
                    $node->nodeRow->NodeResponseSet     = trim($this->REQ->input('manipMoreSet'));
                    for ($i=0; $i < $resLimit; $i++) {
                        if (trim($this->REQ->input('manipMore' . $i . 'Store')) != '') {
                            if (!isset($node->dataManips[$i])) {
                                $node->dataManips[$i] = new SLNode;
                                $node->dataManips[$i]->NodeTree        = $this->treeID;
                                $node->dataManips[$i]->NodeType        = 'Data Manip: Update';
                                $node->dataManips[$i]->NodeParentID    = $node->nodeID;
                                $node->dataManips[$i]->NodeParentOrder = $i;
                            }
                            $node->dataManips[$i]->NodeDataStore = trim($this->REQ->input('manipMore' . $i . 'Store'));
                            $node->dataManips[$i]->NodeDefault = trim($this->REQ->input('manipMore' . $i . 'Val'));
                            $node->dataManips[$i]->NodeResponseSet = trim($this->REQ->input('manipMore' . $i . 'Set'));
                            $node->dataManips[$i]->save();
                        } else {
                            if (isset($node->dataManips[$i])) $node->dataManips[$i]->delete();
                        }
                    }
                } else { // other normal response node
                    $node->nodeRow->NodeType = trim($this->REQ->input('nodeTypeQ'));
                    $opts = [5, 11, 13, 17, 23];
                    foreach ($opts as $o) {
                        if ($this->REQ->has('opts'.$o.'') && intVal($this->REQ->input('opts'.$o.'')) == $o) {
                            if ($node->nodeRow->NodeOpts%$o > 0) $node->nodeRow->NodeOpts *= $o;
                        } elseif ($node->nodeRow->NodeOpts%$o == 0) {
                            $node->nodeRow->NodeOpts = $node->nodeRow->NodeOpts/$o;
                        }
                    }
                    
                    $newResponses = array();
                    if (trim($this->REQ->input('responseLoopItems')) != '') {
                        $node->nodeRow->NodeResponseSet = 'LoopItems::'.$this->REQ->input('responseLoopItems');
                    } elseif (trim($this->REQ->input('responseDefinition')) != '') {
                        $node->nodeRow->NodeResponseSet = 'Definition::'.$this->REQ->input('responseDefinition');
                        $defs = SLDefinitions::where('DefSet', 'Value Ranges')
                            ->where('DefSubset', $this->REQ->input('responseDefinition'))
                            ->orderBy('DefOrder', 'asc')
                            ->get();
                        if ($defs && sizeof($defs) > 0) {
                            foreach ($defs as $i => $def) {
                                $newResponses[] = [
                                    "eng"     => $def->DefValue,
                                    "value" => $def->DefID, 
                                    "kids"     => (($this->REQ->has('response'.$i.'ShowKids')) 
                                        ? intVal($this->REQ->input('response'.$i.'ShowKids')) : 0)
                                ];
                            }
                        }
                    } else {
                        if (strpos($node->nodeRow->NodeResponseSet, 'Definition::') !== false) {
                            $node->nodeRow->NodeResponseSet = '';
                        }
                        for ($i=0; $i < 20; $i++) {
                            if ($this->REQ->has('response'.$i.'') && trim($this->REQ->input('response'.$i.'')) != '') {
                                $newResponses[] = [
                                    "eng"     => trim($this->REQ->input('response'.$i.'')),
                                    "value" => ((trim($this->REQ->input('response'.$i.'Val')) != '') 
                                        ? trim($this->REQ->input('response'.$i.'Val')) 
                                        : trim($this->REQ->input('response'.$i.''))), 
                                    "kids"     => (($this->REQ->has('response'.$i.'ShowKids')) 
                                        ? intVal($this->REQ->input('response'.$i.'ShowKids')) : 0)
                                ];
                            }
                        }
                    }
                    $node->nodeRow->save();
                    $node->nodeID = $node->nodeRow->NodeID;
                    $this->saveNewResponses($node, $newResponses, $resLimit);
                }
                $node->nodeRow->save();
                
                if ($this->REQ->has('condIDs') && sizeof($this->REQ->condIDs) > 0) {
                    foreach ($this->REQ->condIDs as $condID) {
                        if ($this->REQ->has('delCond'.$condID.'') && $this->REQ->get('delCond'.$condID.'') == 'Y') {
                            SLConditionsNodes::where('CondNodeCondID', $condID)
                                ->where('CondNodeNodeID', $node->nodeID)
                                ->delete();
                        }
                    }
                }
                if (($this->REQ->has('oldConds') && intVal($this->REQ->oldConds) > 0) 
                    || ($this->REQ->has('condHash') && trim($this->REQ->condHash) != '')) {
                    $newCond = $GLOBALS["DB"]->saveEditCondition($this->REQ);
                    $newLink = new SLConditionsNodes;
                    $newLink->CondNodeCondID = $newCond->CondID;
                    $newLink->CondNodeNodeID = $node->nodeID;
                    $newLink->save();
                }
                
                $this->updateLoopRoots();
                $this->updateBranchUrls();
            }
            $treeCaches = [
                '.dashboard.tree.map',         '.dashboard.tree.map.all',         '.dashboard.tree.map.alt', 
                '.dashboard.tree.stats',     '.dashboard.tree.stats.all',     '.dashboard.tree.stats.alt', 
                '.dashboard.tree'
            ];
            foreach ($treeCaches as $cache) Cache::forget($cache);
            echo '<script type="text/javascript"> setTimeout("window.location=\'/dashboard/tree/map?all=1&refresh=1#n'.$node->nodeRow->NodeID.'\'", 5); </script>';
            exit;
        }
        
        $defs = SLDefinitions::where('DefSet', 'Value Ranges')
            ->select('DefSubset')
            ->distinct()
            ->orderBy('DefSubset', 'asc')
            ->get();
        $currDefinition = $currLoopItems = '';
        if (isset($node->nodeRow->NodeResponseSet) 
            && strpos($node->nodeRow->NodeResponseSet, 'Definition::') !== false) {
            $currDefinition = str_replace('Definition::', '', $node->nodeRow->NodeResponseSet);
        } elseif (isset($node->nodeRow->NodeResponseSet) 
            && strpos($node->nodeRow->NodeResponseSet, 'LoopItems::') !== false) {
            $currLoopItems = str_replace('LoopItems::', '', $node->nodeRow->NodeResponseSet);
        }
        $branch = ((isset($node->nodeRow->NodeDataBranch)) ? $node->nodeRow->NodeDataBranch : '');
        return view('vendor.survloop.admin.tree.node-edit', [
            "canEditTree"    => $this->canEditTree, 
            "treeID"         => $this->treeID, 
            "node"           => $node, 
            "currDefinition" => $currDefinition, 
            "currLoopItems"  => $currLoopItems, 
            "nodeTypes"      => $this->nodeTypes, 
            "REQ"            => $this->REQ, 
            "resLimit"       => $resLimit, 
            "defs"           => $defs,
            "dataBranchDrop" => $GLOBALS["DB"]->tablesDropdown($branch, 
                'select database table to create deeper or more explicit data linkages')
        ]);
        
    }
    
    
    public function saveNewResponses($node, $newResponses, $resLimit = 20)
    {
        for ($i=0; $i < $resLimit; $i++) {
            if (isset($newResponses[$i])) {
                if (!isset($node->responses[$i])) {
                    $node->responses[$i] = new SLNodeResponses;
                    $node->responses[$i]->NodeResNode = $node->nodeID;
                }
                $node->responses[$i]->NodeResOrd      = $i;
                $node->responses[$i]->NodeResEng      = $newResponses[$i]["eng"];
                $node->responses[$i]->NodeResValue    = $newResponses[$i]["value"];
                $node->responses[$i]->NodeResShowKids = $newResponses[$i]["kids"];
                $node->responses[$i]->save();
            } elseif (isset($node->responses[$i])) {
                $node->responses[$i]->delete();
            }
        }
        return true;
    }
    
    protected function adminBasicPrintNode($tierNode = array(), $tierDepth = 0)
    {
        $tierDepth++;
        if (sizeof($tierNode) > 0 && $tierNode[0] > 0) {
            if ($this->hasNode($tierNode[0])) {
                $this->allNodes[$tierNode[0]]->fillNodeRow();
                $tmp = $this->allNodes[$tierNode[0]]->nodeRow->NodePromptText;
                $styPos = strpos($tmp, '<style>');
                if ($styPos !== false) {
                    $styPosEnd = strpos($tmp, '</style>', $styPos);
                    $tmp = substr($tmp, 0, $styPos) . substr($tmp, 8+$styPosEnd);
                }
                $this->allNodes[$tierNode[0]]->nodeRow->NodePromptText = strip_tags($tmp);
                $childrenPrints = '';
                if (sizeof($tierNode[1]) > 0) { 
                    foreach ($tierNode[1] as $next) {
                        $childrenPrints .= $this->adminBasicPrintNode($next, $tierDepth);
                    }
                }
                $conditionList = (sizeof($this->allNodes[$tierNode[0]]->conds) == 0) ? ''
                    : '<div class="f12 slBlueDark">Condition' 
                        . ((sizeof($this->allNodes[$tierNode[0]]->conds) > 1) ? 's' : '') . ' - '
                        . view('vendor.survloop.admin.tree.node-list-conditions', [
                            "conds" => $this->allNodes[$tierNode[0]]->conds
                        ])->render() . '</div>';
                if (intVal($tierNode[0]) > 0 && isset($this->allNodes[$tierNode[0]])) {
                    return view('vendor.survloop.admin.tree.node-print-basic', [
                        "canEditTree"    => $this->canEditTree, 
                        "REQ"            => $this->REQ, 
                        "nID"            => $tierNode[0], 
                        "node"           => $this->allNodes[$tierNode[0]], 
                        "tierNode"       => $tierNode, 
                        "tierDepth"      => $tierDepth, 
                        "childrenPrints" => $childrenPrints,
                        "conditionList"  => $conditionList
                    ])->render();
                }
            }
        }
        return '';
    }
    
    public function adminPrintFullTree(Request $request)
    {
        $this->loadTree();
        $this->initExtra($request);
        $this->checkTreeSessOpts();
        $this->treeAdminNodeManip();
        if ($this->REQ->has('dataStruct')) {
            
        }
        return view('vendor.survloop.admin.tree.node-print-wrap', [
            "adminBasicPrint"     => $this->adminBasicPrintNode($this->nodeTiers, -1), 
            "canEditTree"         => $this->canEditTree
        ])->render();
    }
    
    
    
    protected function adminResponseNodeStatsTxt($res, $nodeFinalCnt, $nodeAttemptsCnt, $nodeFinalVals, $nodeAttempts, $nodeSess)
    {
        $stats = '<span class="gry6">';
        if (isset($nodeFinalVals[$res])) {
            $stats .= '<b>' . number_format(100*$nodeFinalVals[$res]/$nodeFinalCnt, 1)
                . '%</b> <span class="f12">of ' . sizeof($nodeSess) . ' Final Submissions</span>; ';
        } else {
            $stats .= '<b>0%</b> <span class="f12">of ' . sizeof($nodeSess) . ' Final Submissions</span>; ';
        }
        if (isset($nodeAttempts[$res])) {
            $stats .= number_format(100*$nodeAttempts[$res]/$nodeAttemptsCnt, 1) 
                . '% <span class="f12">of All ' . $nodeAttemptsCnt . ' Attempts</span>; ';
        } else {
            $stats .= '0% <span class="f12">of All Attempts</span>; ';
        }
        return $stats . '</span>';
    }
    
    protected function adminResponseNodeStats($tierNode = array(), $tierDepth = 0)
    {
        $retVal = '';
        $tierDepth++;
        if (sizeof($tierNode) > 0 && $tierNode[0] > 0) {
            $nID = $tierNode[0];
            if ($this->hasNode($nID)) {
                $nodeFinalCnt = $nodeAttemptsCnt = 0;
                $nodeFinalVals = $nodeAttempts = $nodeSess = array();
                $nodeSaves = SLNodeSaves::where('NodeSaveNode', $nID)
                    ->orderBy('created_at', 'desc')
                    ->get();
                if (sizeof($nodeSaves) > 0) {
                    foreach ($nodeSaves as $save) {
                        if (strlen($save->NodeSaveNewVal) > 100) {
                            $save->NodeSaveNewVal = substr($save->NodeSaveNewVal, 0, 100) . '...';
                        }
                        $responses = array($save->NodeSaveNewVal);
                        if ($this->allNodes[$nID]->nodeType == 'Checkbox') {
                            $responses = explode(';;', $save->NodeSaveNewVal);
                        }
                        if (!isset($nodeSess[$save->NodeSaveSession])) {
                            $nodeSess[$save->NodeSaveSession] = 1;
                            foreach ($responses as $j => $res) {
                                if (!isset($nodeFinalVals[strtolower($res)])) {
                                    $nodeFinalVals[strtolower($res)] = 0;
                                }
                                $nodeFinalVals[strtolower($res)]++;
                                $nodeFinalCnt++;
                            }
                        } else {
                            $nodeSess[$save->NodeSaveSession]++;
                        }
                        foreach ($responses as $j => $res) {
                            if (!isset($nodeAttempts[strtolower($res)])) {
                                $nodeAttempts[strtolower($res)] = 0;
                            }
                            $nodeAttempts[strtolower($res)]++;
                            $nodeAttemptsCnt++;
                        }
                    }
                }
                if ($this->allNodes[$nID]->nodeType == 'U.S. States') {
                    foreach ($GLOBALS["DB"]->states->stateList as $abbr => $name) {
                        $ind = sizeof($this->allNodes[$nID]->responses);
                        $this->allNodes[$nID]->responses[$ind] = new SLNodeResponses;
                        $this->allNodes[$nID]->responses[$ind]->NodeResValue = $abbr;
                        $this->allNodes[$nID]->responses[$ind]->NodeResEng = $name;
                        $this->allNodes[$nID]->responses[$ind]->NodeResShowKids = 0;
                    }
                }
                
                $nodeAJAX = '';
                $retVal .= '<a name="n'.$nID.'"></a><div class="basicTier' 
                    . (($tierDepth < 10) ? $tierDepth : 9) . '">';
                if (!$this->allNodes[$nID]->isPage()) {
                    $retVal .= '<span class="slBlueDark f22" title="(on parent\'s page)">'
                        . '<i class="fa fa-angle-double-up"></i></span> ';
                }
                if ($this->allNodes[$nID]->isLoopRoot()) {
                    $retVal .= '<span class="f22 slBlueDark"><i class="fa fa-refresh"></i> </span>';
                }
                if ($this->allNodes[$nID]->isDataManip()) {
                    $retVal .= '<span class="f22 slBlueDark"><i class="fa fa-database"></i> </span>';
                }
                $retVal .= '<span class="slBlueDark f22"><span class="f16">#' . $nID
                    . '</span><i class="fa fa-chevron-right"></i></span> ';
                if ($this->allNodes[$nID]->isBranch())
                {
                    $retVal .= '<span class="f20"><span class="gry9"><i class="fa fa-share-alt"></i> <i>' 
                    . (($nID == $this->rootID) ? 'Tree Root Node' : 'Section Branch') . ':</i></span> <b>' 
                    . $this->allNodes[$nID]->nodeRow->NodePromptText . '</b></span>'
                    . '<div class="pT5"><a href="#n'.$nID.'" id="adminNode'.$nID.'Expand" class="adminNodeExpand '
                    . 'slBlueLight noUnd"><i class="fa fa-expand fa-flip-horizontal"></i></a></div>';
                }
                else
                { // non-branch nodes
                    $retVal .= '<span class="' . ((strlen($this->allNodes[$nID]->nodeRow->NodePromptText) > 100) 
                        ? 'f16' : 'f22') . '">' . strip_tags(str_replace('</div>', ' ', str_replace('</h1>', ' ', 
                        str_replace('</h2>', ' ', $this->allNodes[$nID]->nodeRow->NodePromptText)))) 
                        . (($this->allNodes[$nID]->isRequired()) ? ' <span class="slRedDark">*</span> ' : '') 
                        . '</span><div class="pT5">'
                        . ((sizeof($tierNode[1]) > 0) ? '<a href="#n'.$nID.'" id="adminNode' . $nID . 'Expand" '
                            . 'class="slBlueLight noUnd"><i class="fa fa-expand fa-flip-horizontal"'
                            . '></i></a>&nbsp;&nbsp;&nbsp;' : '');
                    if (sizeof($this->allNodes[$nID]->nodeResponses) > 0) {
                        $retVal .= '<ul>';
                        foreach ($this->allNodes[$nID]->nodeResponseVals as $j => $res) {
                            $stats = $this->adminResponseNodeStatsTxt(strtolower($res), $nodeFinalCnt, 
                                $nodeAttemptsCnt, $nodeFinalVals, $nodeAttempts, $nodeSess);
                            if ($this->allNodes[$nID]->nodeResponses[0] != '[U.S.States]' 
                                || isset($nodeFinalVals[strtolower($res)]) || isset($nodeAttempts[strtolower($res)])) {
                                if (sizeof($this->allNodes[$nID]->nodeResponsesShowKids) > 0 
                                    && in_array($j, $this->allNodes[$nID]->nodeResponsesShowKids)) {
                                    $retVal .= '<li class="mT5 mB20"><span class="f18"><b>' . strip_tags($res)
                                        . '</b></span> <i class="fa fa-code-fork fa-flip-vertical" title="Children '
                                        . 'displayed if selected"></i><br />' . $stats . '</li>';
                                } else {
                                    $retVal .= '<li class="mT5 mB20"><span class="f18"><b>' 
                                        . strip_tags($res) . '</b></span><br />' . $stats . '</li>';
                                }
                            }
                        }
                        $retVal .= '</ul>';
                    } elseif (sizeof($nodeFinalVals) > 0) {
                        // if ($this->allNodes[$nID]->nodeType == '')
                        $retVal .= '<div><ul>';
                        arsort($nodeFinalVals);
                        $j=0;
                        foreach ($nodeFinalVals as $res => $cnt) {
                            $retVal .= '<li class="mT5 mB20"><span class="f18"><b>' . ((trim($res) != '') ? strip_tags($res) : '<span class="gry9"><i>(empty)</i></span>') . '</b></span><br />' 
                                . $this->adminResponseNodeStatsTxt(strtolower($res), $nodeFinalCnt, $nodeAttemptsCnt, $nodeFinalVals, $nodeAttempts, $nodeSess) . '</li>';
                            if ($j == 9) {
                                $retVal .= '<li><a name="n'.$nID.'more"></a><a href="#n'.$nID.'more" id="show'.$nID.'Response'.$j.'Stats">show more</a></li></ul></div>
                                <div id="more'.$nID.'Response'.$j.'Stats" class="disNon"><ul>';
                                $nodeAJAX .= '$("#show'.$nID.'Response'.$j.'Stats").click(function(){ $("#more'.$nID.'Response'.$j.'Stats").slideToggle("fast"); }); ' . "\n";
                            }
                            $j++;
                        }
                        $retVal .= '</ul></div>';
                    }
                    $retVal .= '</div>';
                }
                if (sizeof($tierNode[1]) > 0) { 
                    $retVal .= '<div id="nodeKids' . $nID . '" class="dis' 
                        . ((session()->get('adminOverOpts')%2 == 0 || $nID == $this->rootID) ? 'Blo' : 'Non') . '">';
                    foreach ($tierNode[1] as $next) $retVal .= $this->adminResponseNodeStats($next, $tierDepth);
                    $retVal .= '</div>';
                }
                $retVal .= '</div><script type="text/javascript"> $(document).ready(function(){ 
                $("#adminNode'.$nID.'Expand").click(function(){ $("#nodeKids' . $nID . '").slideToggle("fast"); }); 
                ' . $nodeAJAX . '
                }); </script>';
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
        if (!session()->has('adminOverOpts')) session()->put('adminOverOpts', 2);
        if ($this->REQ->has('all')) {
            if (session()->get('adminOverOpts')%2 > 0) {
                session()->put('adminOverOpts', (2*session()->get('adminOverOpts')));
            }
        } elseif (session()->get('adminOverOpts')%2 == 0) {
            session()->put('adminOverOpts', (session()->get('adminOverOpts')/2));
        }
        return true;
    }
    
    
    
    
    protected function adminBasicDropdownNode($tierNode = array(), $tierDepth = 0, $preSel = -3)
    {
        $retVal = '';
        $tierDepth++;
        if (sizeof($tierNode) > 0 && $tierNode[0] > 0) { 
            $nID = $tierNode[0]; 
            if ($this->hasNode($nID)) {
                $indent = ''; for ($i=0; $i<$tierDepth; $i++) $indent .= ' - ';
                $nodeName = $this->allNodes[$nID]->nodeRow->NodePromptText;
                $retVal .= '<option value="'.$nID.'" ' . ((intVal($preSel) == $nID) ? 'SELECTED' : '') . ' >' 
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
