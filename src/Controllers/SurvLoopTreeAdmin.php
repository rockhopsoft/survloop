<?php
namespace SurvLoop\Controllers;

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
        $this->canEditTree = false;
    	if ($this->v["user"] && isset($this->v["user"]->id)) {
    		$this->canEditTree = $this->v["user"]->hasRole('administrator|brancher');
    	}
        return true;
    }
    
    public function adminNodeEdit($nodeIN, Request $request, $currPage = '') 
    {
        $this->survLoopInit($request, $currPage);
        $resLimit = 60;
        $node = [];
        if ($nodeIN > 0) {
            $node = $this->loadNode(SLNode::find($nodeIN));
            $node->fillNodeRow($nodeIN);
        }
        if ($nodeIN <= 0 || !$node || sizeof($node) == 0) {
            $node = $this->loadNode();
            $node->nodeRow->NodeTree        = $GLOBALS["SL"]->treeID;
            $node->nodeRow->NodeParentID    = $GLOBALS["SL"]->REQ->nodeParentID;
            $node->nodeRow->NodeParentOrder = 0;
            $node->nodeRow->NodeOpts        = 1;
            if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
                $node->nodeRow->NodeType    = 'Instructions';
            } else {
                $node->nodeRow->NodeType    = 'Text';
            }
            $node->nodeType = $node->nodeRow->NodeType;
        }
        if ($GLOBALS["SL"]->REQ->has('sub') && $this->canEditTree) {
            if ($GLOBALS["SL"]->REQ->has('deleteNode') && intVal($GLOBALS["SL"]->REQ->input('deleteNode')) == 1) {
                $this->treeAdminNodeDelete($node->nodeRow->NodeID);
            } else {
                if ($nodeIN <= 0) $node = $this->treeAdminNodeNew($node);
                
                if (intVal($node->nodeRow->NodeOpts) <= 1) $node->nodeRow->NodeOpts = 1;
                if ($GLOBALS["SL"]->REQ->changeResponseMobile == 'desktop') {
                    if ($node->nodeRow->NodeOpts%2 > 0) $node->nodeRow->NodeOpts *= 2;
                }
                elseif ($node->nodeRow->NodeOpts%2 == 0) $node->nodeRow->NodeOpts = $node->nodeRow->NodeOpts/2;
                $opts = [5, 11, 13, 17, 23, 29, 31, 37, 41, 43, 47, 53, 59, 61, 67, 71];
                $optsDesktop = [11, 17];
                foreach ($opts as $o) {
                    if ($GLOBALS["SL"]->REQ->has('opts'.$o.'') && intVal($GLOBALS["SL"]->REQ->input('opts'.$o.'')) == $o
                        && (!in_array($o, $optsDesktop) || $node->nodeRow->NodeOpts%2 == 0)) {
                        if ($node->nodeRow->NodeOpts%$o > 0) $node->nodeRow->NodeOpts *= $o;
                    } elseif ($node->nodeRow->NodeOpts%$o == 0) {
                        $node->nodeRow->NodeOpts = $node->nodeRow->NodeOpts/$o;
                    }
                }
                
                $node->nodeRow->NodePromptText      = trim($GLOBALS["SL"]->REQ->input('nodePromptText'));
                $node->nodeRow->NodePromptNotes     = trim($GLOBALS["SL"]->REQ->input('nodePromptNotes'));
                $node->nodeRow->NodePromptAfter     = trim($GLOBALS["SL"]->REQ->input('nodePromptAfter'));
                $node->nodeRow->NodeInternalNotes   = trim($GLOBALS["SL"]->REQ->input('nodeInternalNotes'));
                $node->nodeRow->NodeDefault         = trim($GLOBALS["SL"]->REQ->input('nodeDefault'));
                $node->nodeRow->NodeTextSuggest     = trim($GLOBALS["SL"]->REQ->input('nodeTextSuggest'));
                $node->nodeRow->NodeDataBranch      = trim($GLOBALS["SL"]->REQ->input('nodeDataBranch'));
                $node->nodeRow->NodeDataStore       = trim($GLOBALS["SL"]->REQ->input('nodeDataStore'));
                $node->nodeRow->NodeCharLimit       = intVal($GLOBALS["SL"]->REQ->input('nodeCharLimit'));
                if (($GLOBALS["SL"]->treeRow->TreeType == 'Page' && $node->nodeRow->NodeParentID <= 0)
                    || $GLOBALS["SL"]->REQ->nodeType == 'page') {
                    $node->nodeRow->NodeType        = 'Page';
                    $node->nodeRow->NodePromptNotes = trim($GLOBALS["SL"]->REQ->input('nodeSlug'));
                    $node->nodeRow->NodeCharLimit   = intVal($GLOBALS["SL"]->REQ->input('pageFocusField'));
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'branch') {
                    $node->nodeRow->NodeType = 'Branch Title';
                    $node->nodeRow->NodePromptText  = trim($GLOBALS["SL"]->REQ->input('branchTitle'));
                } elseif (in_array($GLOBALS["SL"]->REQ->nodeType, ['instruct', 'instructRaw'])) {
                    $node->nodeRow->NodeType        = 'Instructions' 
                        . (($GLOBALS["SL"]->REQ->nodeType == 'instructRaw') ? ' Raw' : '');
                    $node->nodeRow->NodePromptText  = trim($GLOBALS["SL"]->REQ->input('nodeInstruct'));
                    $node->nodeRow->NodePromptAfter = trim($GLOBALS["SL"]->REQ->input('instrPromptAfter'));
                    if ($GLOBALS["SL"]->REQ->has('opts37') && intVal($GLOBALS["SL"]->REQ->input('opts37')) == 37) {
                        if ($node->nodeRow->NodeOpts%37 > 0) $node->nodeRow->NodeOpts *= 37;
                    } elseif ($node->nodeRow->NodeOpts%37 == 0) {
                        $node->nodeRow->NodeOpts    = $node->nodeRow->NodeOpts/37;
                    }
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'heroImg') {
                    $node->nodeRow->NodeType        = 'Hero Image';
                    $node->nodeRow->NodeTextSuggest = trim($GLOBALS["SL"]->REQ->input('pageHeroImg'));
                    $node->nodeRow->NodePromptAfter = trim($GLOBALS["SL"]->REQ->input('pageHeroImgTxt'));
                    $node->nodeRow->NodeDefault     = trim($GLOBALS["SL"]->REQ->input('pageHeroImgBtn'));
                    $node->nodeRow->NodeResponseSet = trim($GLOBALS["SL"]->REQ->input('pageHeroImgUrl'));
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'loop') {
                    $node->nodeRow->NodeType        = 'Loop Root';
                    $node->nodeRow->NodePromptText  = trim($GLOBALS["SL"]->REQ->input('nodeLoopInstruct'));
                    $node->nodeRow->NodePromptNotes = trim($GLOBALS["SL"]->REQ->input('loopSlug'));
                    $node->nodeRow->NodeDataBranch  = $loop = trim($GLOBALS["SL"]->REQ->input('nodeDataLoop'));
                    if (!isset($GLOBALS["SL"]->dataLoops[$loop])) {
                        $GLOBALS["SL"]->dataLoops[$loop] = new SLDataLoop;
                        $GLOBALS["SL"]->dataLoops[$loop]->DataLoopTree = $this->treeID;
                        $GLOBALS["SL"]->dataLoops[$loop]->DataLoopRoot = $node->nodeRow->NodeID;
                    } elseif (trim($GLOBALS["SL"]->dataLoops[$loop]->DataLoopTable) != ''
                        && isset($GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->dataLoops[$loop]->DataLoopTable])) {
                        $node->nodeRow->NodeDataStore = $GLOBALS["SL"]->dataLoops[$loop]->DataLoopTable . ':' 
                            . $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->dataLoops[$loop]->DataLoopTable] . 'ID';
                    }
                    $GLOBALS["SL"]->dataLoops[$loop]->DataLoopIsStep = 0;
                    $GLOBALS["SL"]->dataLoops[$loop]->DataLoopAutoGen = 1;
                    $GLOBALS["SL"]->dataLoops[$loop]->DataLoopDoneFld = '';
                    if ($GLOBALS["SL"]->REQ->has('stepLoop') && intVal($GLOBALS["SL"]->REQ->input('stepLoop')) == 1) {
                        $GLOBALS["SL"]->dataLoops[$loop]->DataLoopIsStep = 1;
                        $GLOBALS["SL"]->dataLoops[$loop]->DataLoopAutoGen = 0;
                        if ($GLOBALS["SL"]->REQ->has('stepLoopDoneField') 
                            && trim($GLOBALS["SL"]->REQ->input('stepLoopDoneField')) != '') {
                            $GLOBALS["SL"]->dataLoops[$loop]->DataLoopDoneFld 
                                = trim($GLOBALS["SL"]->REQ->input('stepLoopDoneField'));
                        }
                    } elseif (!$GLOBALS["SL"]->REQ->has('stdLoopAuto') 
                        || intVal($GLOBALS["SL"]->REQ->input('stdLoopAuto')) == 0) {
                        $GLOBALS["SL"]->dataLoops[$loop]->DataLoopAutoGen = 0;
                    }
                    $GLOBALS["SL"]->dataLoops[$loop]->save();
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'cycle') {
                    $node->nodeRow->NodeType        = 'Loop Cycle';
                    $node->nodeRow->NodeResponseSet = 'LoopItems::' . trim($GLOBALS["SL"]->REQ->input('nodeDataCycle'));
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'sort') {
                    $node->nodeRow->NodeType        = 'Loop Sort';
                    $node->nodeRow->NodeResponseSet = 'LoopItems::' . trim($GLOBALS["SL"]->REQ->input('nodeDataSort'));
                    $node->nodeRow->NodeDataStore   = trim($GLOBALS["SL"]->REQ->input('DataStoreSort'));
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'data') {
                    $node->nodeRow->NodeType        = 'Data Manip: ' . $GLOBALS["SL"]->REQ->input('dataManipType');
                    if ($GLOBALS["SL"]->REQ->input('dataManipType') == 'Close Sess') {
                        $node->nodeRow->NodeResponseSet = $GLOBALS["SL"]->REQ->input('dataManipCloseSessTree');
                    } else {
                        $node->nodeRow->NodeDataStore   = trim($GLOBALS["SL"]->REQ->input('manipMoreStore'));
                        $node->nodeRow->NodeDefault     = trim($GLOBALS["SL"]->REQ->input('manipMoreVal'));
                        $node->nodeRow->NodeResponseSet = trim($GLOBALS["SL"]->REQ->input('manipMoreSet'));
                        for ($i=0; $i < $resLimit; $i++) {
                            if (trim($GLOBALS["SL"]->REQ->input('manipMore' . $i . 'Store')) != '') {
                                if (!isset($node->dataManips[$i])) {
                                    $node->dataManips[$i] = new SLNode;
                                    $node->dataManips[$i]->NodeTree        = $this->treeID;
                                    $node->dataManips[$i]->NodeType        = 'Data Manip: Update';
                                    $node->dataManips[$i]->NodeParentID    = $node->nodeID;
                                    $node->dataManips[$i]->NodeParentOrder = $i;
                                }
                                $node->dataManips[$i]->NodeDataStore 
                                    = trim($GLOBALS["SL"]->REQ->input('manipMore' . $i . 'Store'));
                                $node->dataManips[$i]->NodeDefault 
                                    = trim($GLOBALS["SL"]->REQ->input('manipMore' . $i . 'Val'));
                                $node->dataManips[$i]->NodeResponseSet 
                                    = trim($GLOBALS["SL"]->REQ->input('manipMore' . $i . 'Set'));
                                $node->dataManips[$i]->save();
                            } else {
                                if (isset($node->dataManips[$i])) $node->dataManips[$i]->delete();
                            }
                        }
                    }
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'survWidget') {
                    $node->nodeRow->NodeType        = $GLOBALS["SL"]->REQ->nodeSurvWidgetType;
                    $node->nodeRow->NodeResponseSet = $GLOBALS["SL"]->REQ->nodeSurvWidgetTree;
                    $node->nodeRow->NodeCharLimit   = intVal($GLOBALS["SL"]->REQ->nodeSurvWidgetLimit);
                    $node->nodeRow->NodePromptText  = $GLOBALS["SL"]->REQ->nodeSurvWidgetPre;
                    $node->nodeRow->NodePromptAfter = $GLOBALS["SL"]->REQ->nodeSurvWidgetPost;
                    if ($node->nodeRow->NodeType == 'Send Email') {
                        $node->nodeRow->NodePromptAfter = '::TO::' . (($GLOBALS["SL"]->REQ->has('widgetEmailTo')) 
                                ? implode(',', $GLOBALS["SL"]->REQ->widgetEmailTo) : '')
                            . '::CC::' . (($GLOBALS["SL"]->REQ->has('widgetEmailCC')) 
                                ? implode(',', $GLOBALS["SL"]->REQ->widgetEmailCC) : '')
                            . '::BCC::' . (($GLOBALS["SL"]->REQ->has('widgetEmailBCC')) 
                                ? implode(',', $GLOBALS["SL"]->REQ->widgetEmailBCC) : '');
                        //$node->nodeRow->NodeDefault = (($GLOBALS["SL"]->REQ->has('widgetEmaDef')) 
                        //    ? $GLOBALS["SL"]->REQ->widgetEmaDef : -3);
                    }
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'layout') {
                    $node->nodeRow->NodeType        = $GLOBALS["SL"]->REQ->nodeLayoutType;
                    if ($node->nodeRow->NodeType == 'Layout Row') {
                        $node->nodeRow->NodeCharLimit = intVal($GLOBALS["SL"]->REQ->nodeLayoutLimitRow);
                    } elseif ($node->nodeRow->NodeType == 'Layout Column') {
                        $node->nodeRow->NodeCharLimit = intVal($GLOBALS["SL"]->REQ->nodeLayoutLimitCol);
                    }
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'bigButt') {
                    $node->nodeRow->NodeType        = 'Big Button';
                    $node->nodeRow->NodeResponseSet = trim($GLOBALS["SL"]->REQ->input('bigBtnStyle'));
                    $node->nodeRow->NodeDefault     = trim($GLOBALS["SL"]->REQ->input('bigBtnText'));
                    $node->nodeRow->NodeDataStore   = trim($GLOBALS["SL"]->REQ->input('bigBtnJS'));
                } else { // other normal response node
                    $node->nodeRow->NodeType = trim($GLOBALS["SL"]->REQ->input('nodeTypeQ'));
                    if ($node->nodeRow->NodeType == 'Drop Down') {
                        $node->nodeRow->NodeTextSuggest = trim($GLOBALS["SL"]->REQ->dropDownSuggest);
                    }
                    $newResponses = array();
                    if (trim($GLOBALS["SL"]->REQ->input('responseListType')) == 'auto') {
                        if (trim($GLOBALS["SL"]->REQ->input('responseLoopItems')) != '') {
                            $node->nodeRow->NodeResponseSet 
                                = 'LoopItems::'.$GLOBALS["SL"]->REQ->input('responseLoopItems');
                        } elseif (trim($GLOBALS["SL"]->REQ->input('responseDefinition')) != '') {
                            $node->nodeRow->NodeResponseSet 
                                = 'Definition::'.$GLOBALS["SL"]->REQ->input('responseDefinition');
                            $defs = SLDefinitions::where('DefSet', 'Value Ranges')
                                ->where('DefSubset', $GLOBALS["SL"]->REQ->input('responseDefinition'))
                                ->orderBy('DefOrder', 'asc')
                                ->get();
                            if ($defs && sizeof($defs) > 0) {
                                foreach ($defs as $i => $def) {
                                    $newResponses[] = [
                                        "eng"   => $def->DefValue,
                                        "value" => $def->DefID, 
                                        "kids"  => (($GLOBALS["SL"]->REQ->has('response'.$i.'ShowKids')) 
                                            ? intVal($GLOBALS["SL"]->REQ->input('response'.$i.'ShowKids')) : 0),
                                        "mutEx" => (($GLOBALS["SL"]->REQ->has('response'.$i.'MutEx')) 
                                            ? intVal($GLOBALS["SL"]->REQ->input('response'.$i.'MutEx')) : 0)
                                    ];
                                }
                            }
                        }
                    } else {
                        $node->nodeRow->NodeResponseSet = '';
                        for ($i=0; $i < 20; $i++) {
                            if ($GLOBALS["SL"]->REQ->has('response'.$i.'') 
                                && trim($GLOBALS["SL"]->REQ->input('response'.$i.'')) != '') {
                                $newResponses[] = [
                                    "eng"   => trim($GLOBALS["SL"]->REQ->input('response'.$i.'')),
                                    "value" => ((trim($GLOBALS["SL"]->REQ->input('response'.$i.'Val')) != '') 
                                        ? trim($GLOBALS["SL"]->REQ->input('response'.$i.'Val')) 
                                        : trim($GLOBALS["SL"]->REQ->input('response'.$i.''))), 
                                    "kids"  => (($GLOBALS["SL"]->REQ->has('response'.$i.'ShowKids')) 
                                        ? intVal($GLOBALS["SL"]->REQ->input('response'.$i.'ShowKids')) : 0),
                                    "mutEx" => (($GLOBALS["SL"]->REQ->has('response'.$i.'MutEx')) 
                                        ? intVal($GLOBALS["SL"]->REQ->input('response'.$i.'MutEx')) : 0)
                                ];
                            }
                        }
                    }
                    if (in_array($GLOBALS["SL"]->REQ->nodeTypeQ, ['Date', 'Date Picker', 'Date Time'])) {
                        $node->nodeRow->NodeCharLimit = intVal($GLOBALS["SL"]->REQ->input('dateOptRestrict'));
                    }
                    $node->nodeRow->save();
                    $node->nodeID = $node->nodeRow->NodeID;
                    $this->saveNewResponses($node, $newResponses, $resLimit);
                }
                if (in_array($GLOBALS["SL"]->REQ->nodeType, ['instruct', 'instructRaw', 'layout'])
                    && $node->nodeRow->NodeOpts%71 == 0) {
                    $node->nodeRow->NodeDefault = $GLOBALS["SL"]->REQ->get('blockBG') . ';;' 
                        . $GLOBALS["SL"]->REQ->get('blockText') . ';;' . $GLOBALS["SL"]->REQ->get('blockLink');
                }
                $node->nodeRow->NodeTree = $this->treeID;
                $node->nodeRow->save();
                
                if ($node->nodeRow->NodeParentID <= 0) {
                    if (isset($node->nodeRow->NodeDataBranch) 
                    && isset($GLOBALS["SL"]->tblI[$node->nodeRow->NodeDataBranch])
                    && (!isset($GLOBALS["SL"]->treeRow->coreTbl) || intVal($GLOBALS["SL"]->treeRow->coreTbl) <= 0)) {
                        $GLOBALS["SL"]->treeRow->TreeCoreTable = $GLOBALS["SL"]->tblI[$node->nodeRow->NodeDataBranch];
                        $GLOBALS["SL"]->treeRow->save();
                    }
                    if ($GLOBALS["SL"]->treeRow->TreeType == 'Page' && $node->nodeRow->NodeType == 'Page') {
                        if ($GLOBALS["SL"]->REQ->has('homepage') 
                            && intVal($GLOBALS["SL"]->REQ->homepage) == 7) {
                            if ($GLOBALS["SL"]->treeRow->TreeOpts%7 > 0) $GLOBALS["SL"]->treeRow->TreeOpts *= 7;
                        } elseif ($GLOBALS["SL"]->treeRow->TreeOpts%7 == 0) {
                            $GLOBALS["SL"]->treeRow->TreeOpts = $GLOBALS["SL"]->treeRow->TreeOpts/7;
                        }
                        if ($GLOBALS["SL"]->REQ->has('adminPage') 
                            && intVal($GLOBALS["SL"]->REQ->adminPage) == 3) {
                            if ($GLOBALS["SL"]->treeRow->TreeOpts%3 > 0) $GLOBALS["SL"]->treeRow->TreeOpts *= 3;
                        } elseif ($GLOBALS["SL"]->treeRow->TreeOpts%3 == 0) {
                            $GLOBALS["SL"]->treeRow->TreeOpts = $GLOBALS["SL"]->treeRow->TreeOpts/3;
                        }
                        $GLOBALS["SL"]->treeRow->TreeName      = trim($GLOBALS["SL"]->REQ->input('pageTreeName'));
                        $GLOBALS["SL"]->treeRow->TreeSlug      = trim($GLOBALS["SL"]->REQ->nodeSlug);
                        $GLOBALS["SL"]->treeRow->TreeFirstPage = $node->nodeRow->NodeID;
                        $GLOBALS["SL"]->treeRow->TreeLastPage  = $node->nodeRow->NodeID;
                        $GLOBALS["SL"]->treeRow->save();
                    }
                }
                if ($GLOBALS["SL"]->REQ->has('condIDs') && sizeof($GLOBALS["SL"]->REQ->condIDs) > 0) {
                    foreach ($GLOBALS["SL"]->REQ->condIDs as $condID) {
                        if ($GLOBALS["SL"]->REQ->has('delCond'.$condID.'') 
                            && $GLOBALS["SL"]->REQ->get('delCond'.$condID.'') == 'Y') {
                            SLConditionsNodes::where('CondNodeCondID', $condID)
                                ->where('CondNodeNodeID', $node->nodeID)
                                ->delete();
                        }
                    }
                }
                if (($GLOBALS["SL"]->REQ->has('oldConds') && intVal($GLOBALS["SL"]->REQ->oldConds) > 0) 
                    || ($GLOBALS["SL"]->REQ->has('condHash') && trim($GLOBALS["SL"]->REQ->condHash) != '')) {
                    $newCond = $GLOBALS["SL"]->saveEditCondition($this->REQ);
                    $newLink = new SLConditionsNodes;
                    $newLink->CondNodeCondID = $newCond->CondID;
                    $newLink->CondNodeNodeID = $node->nodeID;
                    if ($GLOBALS["SL"]->REQ->has('oldCondInverse')
                        && intVal($GLOBALS["SL"]->REQ->oldCondInverse) == 1) {
                        $newLink->CondNodeLoopID = -1;
                    }
                    $newLink->save();
                }
                
                if ($node->nodeRow->NodeType == 'Layout Row' && $nodeIN <= 0) { // new row, so create default columns
                    if ($node->nodeRow->NodeCharLimit > 0) {
                        $colW = $this->getColsWidth($node->nodeRow->NodeCharLimit);
                        $evenTot = $node->nodeRow->NodeCharLimit*$colW;
                        for ($c=0; $c<$node->nodeRow->NodeCharLimit; $c++) {
                            $colNode = $this->loadNode();
                            $colNode->nodeRow->NodeTree        = $GLOBALS["SL"]->treeID;
                            $colNode->nodeRow->NodeParentID    = $node->nodeRow->NodeID;
                            $colNode->nodeRow->NodeParentOrder = $c;
                            $colNode->nodeRow->NodeOpts        = 1;
                            $colNode->nodeRow->NodeType        = 'Layout Column';
                            $colNode->nodeRow->NodeCharLimit   = $colW;
                            if ($c == 0 && $evenTot < 12) $colNode->nodeRow->NodeCharLimit += (12-$evenTot);
                            $colNode->nodeRow->save();
                        }
                    }
                }
                
                $this->updateTreeEnds();
                $this->updateLoopRoots();
                $this->updateBranchUrls();
                $this->updateTreeOpts();
            }
            $treeCaches = [
                '.dashboard.tree.map',
                '.dashboard.tree.map.all',
                '.dashboard.tree.map.alt', 
                '.dashboard.tree.stats',
                '.dashboard.tree.stats.all',
                '.dashboard.tree.stats.alt', 
                '.dashboard.tree'
            ];
            foreach ($treeCaches as $cache) Cache::forget($cache);
            $redir = '/dashboard/tree-' . $this->treeID . '/map?all=1&refresh=1#n' . $node->nodeRow->NodeID;
            if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
                $redir = '/dashboard/page/' . $GLOBALS["SL"]->treeID . '?all=1&refresh=1#n' . $node->nodeRow->NodeID;
            }
            return $this->redir($redir, true);
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
        $nodeTypeSel = (($GLOBALS["SL"]->treeRow->TreeType == 'Page' 
            && $node->nodeRow->NodeID == $GLOBALS["SL"]->treeRow->TreeRoot) ? ' DISABLED ' : '');
        $branch = ((isset($node->nodeRow->NodeDataBranch)) ? $node->nodeRow->NodeDataBranch : '');
        $emailList = SLEmails::where('EmailType', 'NOT LIKE', 'Blurb')
            ->orderBy('EmailName', 'asc')
        	->orderBy('EmailType', 'asc')
        	->get();
        $emailUsers = [ "admin" => [], "volun" => [], "users" => [] ];
        $chk = User::where('email', 'NOT LIKE', 'anonymous.%@anonymous.org')
            ->get();
        if ($chk && sizeof($chk) > 0) {
            foreach ($chk as $u) {
                if ($u->hasRole('administrator|staff|brancher|databaser')) {
                    $emailUsers["admin"][] = [$u->id, $u->email, $u->name];
                } elseif ($u->hasRole('volunteer')) {
                    $emailUsers["volun"][] = [$u->id, $u->email, $u->name];
                } else {
                    $emailUsers["users"][] = [$u->id, $u->email, $u->name];
                }
            }
        }
        $GLOBALS["SL"]->pageJAVA .= view('vendor.survloop.admin.tree.widget-email-edit-java', [
            "emailList"      => $emailList
            ]);
        $widgetEmail = view('vendor.survloop.admin.tree.widget-email-edit', [
            "node"           => $node, 
            "emailList"      => $emailList,
            "emailUsers"     => $emailUsers
            ])->render();
        $treeList = SLTree::where('TreeType', 'Primary Public')
            ->where('TreeDatabase', $this->dbID)
            ->select('TreeID', 'TreeName')
            ->orderBy('TreeName', 'asc')
            ->get();
        $this->addCondEditorAjax();
        if ($node->isInstruct()) $this->v["needsWsyiwyg"] = true;
        $GLOBALS["SL"]->pageJAVA .= view('vendor.survloop.admin.tree.node-edit-java', [
            "node"           => $node, 
            "resLimit"       => $resLimit
            ])->render();
        $GLOBALS["SL"]->pageAJAX .= view('vendor.survloop.admin.tree.node-edit-ajax', [])->render();
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
            "nodeTypeSel"    => $nodeTypeSel, 
            "widgetEmail"    => $widgetEmail,
            "emailList"      => $emailList,
            "treeList"       => $treeList,
            "dataBranchDrop" => $GLOBALS["SL"]->tablesDropdown($branch, 
                'select database table to create deeper or more explicit data linkages')
        ]);
    }
    
    
    public function saveNewResponses($node, $newResponses, $resLimit = 60)
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
                $node->responses[$i]->NodeResMutEx    = $newResponses[$i]["mutEx"];
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
                $nodePromptText = $this->allNodes[$tierNode[0]]->nodeRow->NodePromptText;
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
                $conditionList = (sizeof($this->allNodes[$tierNode[0]]->conds) == 0) ? ''
                    : '<span class="slBlueDark mL10">' 
                        . view('vendor.survloop.admin.tree.node-list-conditions', [
                            "conds"      => $this->allNodes[$tierNode[0]]->conds,
                            "nID"        => $tierNode[0],
                            "hideDeets"  => true
                        ])->render() . '</span>';
                $nodeBtns = view('vendor.survloop.admin.tree.node-print-basic-btns', [
                        "nID"            => $tierNode[0], 
                        "node"           => $this->allNodes[$tierNode[0]], 
                        "tierNode"       => $tierNode, 
                        "canEditTree"    => $this->canEditTree, 
                        "isPrint"        => $this->v["isPrint"],
                        "isAlt"          => $this->v["isAlt"]
                    ])->render();
                if (intVal($tierNode[0]) > 0 && isset($this->allNodes[$tierNode[0]])) {
                    return view('vendor.survloop.admin.tree.node-print-basic', [
                        "rootID"         => $this->rootID, 
                        "nID"            => $tierNode[0], 
                        "node"           => $this->allNodes[$tierNode[0]], 
                        "nodePromptText" => $nodePromptText,
                        "tierNode"       => $tierNode, 
                        "tierDepth"      => $tierDepth, 
                        "childrenPrints" => $childrenPrints,
                        "conditionList"  => $conditionList,
                        "nodeBtns"       => $nodeBtns,
                        "REQ"            => $this->REQ,
                        "canEditTree"    => $this->canEditTree, 
                        "isPrint"        => $this->v["isPrint"],
                        "isAll"          => $this->v["isAll"],
                        "isAlt"          => $this->v["isAlt"]
                    ])->render();
                }
            }
        }
        return '';
    }
    
    public function adminPrintFullTree(Request $request, $pubPrint = false)
    {
        if ($pubPrint) {
            $this->v["isPrint"] = $this->v["isAll"] = $this->v["isAlt"] = true;
        }
        $this->loadTree();
        $this->initExtra($request);
        if ($pubPrint) $this->v["isPrint"] = true;
        $this->checkTreeSessOpts();
        $this->treeAdminNodeManip();
        if ($GLOBALS["SL"]->REQ->has('dataStruct')) {
            
        }
        if ($pubPrint) {
            return $this->adminBasicPrintNode($this->nodeTiers, -1);
        }
        $GLOBALS["SL"]->pageAJAX .= view('vendor.survloop.admin.tree.node-print-wrap-ajax', [
            "canEditTree"     => $this->canEditTree
        ])->render();
        return view('vendor.survloop.admin.tree.node-print-wrap', [
            "adminBasicPrint"     => $this->adminBasicPrintNode($this->nodeTiers, -1), 
            "canEditTree"         => $this->canEditTree,
            "isPrint"             => $this->v["isPrint"]
        ])->render();
    }
    
    
    
    protected function adminResponseNodeStatsTxt($res, $nodeFinalCnt, $nodeAttemptsCnt, $nodeFinalVals, $nodeAttempts, 
        $nodeSess)
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
                $retVal .= '<a name="n' . $nID . '"></a><div class="basicTier' 
                    . (($tierDepth < 10) ? $tierDepth : 9) . '">';
                if (!$this->allNodes[$nID]->isSpecial() && $this->allNodes[$nID]->nodeType != 'Big Button') {
                    $retVal .= '<span class="slBlueDark f16 mL5">#' . $nID . ' ';
                    if ($this->allNodes[$nID]->isLoopRoot()) $retVal .= '<i class="fa fa-refresh mL5"></i> ';
                    if ($this->allNodes[$nID]->isDataManip()) $retVal .= '<i class="fa fa-database mL5"></i> ';
                    echo '</span> ';
                    if ($this->allNodes[$nID]->isBranch())
                    {
                        $retVal .= '<span class="f20"><span class="slGrey"><i class="fa fa-share-alt"></i> <i>' 
                        . (($nID == $this->rootID) ? 'Tree Root Node' : 'Section Branch') . ':</i></span> <b>' 
                        . $this->allNodes[$nID]->nodeRow->NodePromptText . '</b></span><div class="pT5"><a href="#n' 
                        . $nID . '" id="adminNode' . $nID . 'Expand" class="adminNodeExpand '
                        . 'slBlueLight noUnd f12"><i class="fa fa-expand fa-flip-horizontal"></i></a></div>';
                    }
                    else
                    { // non-branch nodes
                        $retVal .= '<span class="' . ((strlen($this->allNodes[$nID]->nodeRow->NodePromptText) > 100) 
                            ? 'f16' : 'f22') . '">' . strip_tags(str_replace('</div>', ' ', str_replace('</h1>', ' ', 
                            str_replace('</h2>', ' ', $this->allNodes[$nID]->nodeRow->NodePromptText)))) 
                            . (($this->allNodes[$nID]->isRequired()) ? ' <span class="slRedDark">*</span> ' : '') 
                            . '</span><div class="pT5">'
                            . ((sizeof($tierNode[1]) > 0) ? '<a href="#n' . $nID . '" id="adminNode' . $nID . 'Expand" '
                                . 'class="slBlueLight noUnd f12"><i class="fa fa-expand fa-flip-horizontal"'
                                . '></i></a>&nbsp;&nbsp;&nbsp;' : '');
                        if (isset($this->allNodes[$nID]->nodeResponses) 
                            && sizeof($this->allNodes[$nID]->nodeResponses) > 0) {
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
                                $retVal .= '<li class="mT5 mB20"><span class="f18"><b>' 
                                    . ((trim($res) != '') ? strip_tags($res) : '<span class="slGrey"><i>(empty)</i></span>') 
                                    . '</b></span><br />' . $this->adminResponseNodeStatsTxt(strtolower($res), 
                                    $nodeFinalCnt, $nodeAttemptsCnt, $nodeFinalVals, $nodeAttempts, $nodeSess) . '</li>';
                                if ($j == 9) {
                                    $retVal .= '<li><a name="n' . $nID . 'more"></a><a href="#n' . $nID . 'more" id="show' 
                                        . $nID . 'Response'.$j.'Stats">show more</a></li></ul></div>
                                        <div id="more' . $nID . 'Response'.$j.'Stats" class="disNon"><ul>';
                                    $nodeAJAX .= '$("#show' . $nID . 'Response'.$j.'Stats").click(function(){ $("#more' 
                                        . $nID . 'Response'.$j.'Stats").slideToggle("fast"); }); ' . "\n";
                                }
                                $j++;
                            }
                            $retVal .= '</ul></div>';
                        }
                        $retVal .= '</div>';
                    }
                }
                if (sizeof($tierNode[1]) > 0) { 
                    $retVal .= '<div id="nodeKids' . $nID . '" class="dis' 
                        . ((session()->get('adminOverOpts')%2 == 0 || $nID == $this->rootID) ? 'Blo' : 'Non') . '">';
                    foreach ($tierNode[1] as $next) $retVal .= $this->adminResponseNodeStats($next, $tierDepth);
                    $retVal .= '</div>';
                }
                $retVal .= '</div>';
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
        if (!session()->has('adminOverOpts')) session()->put('adminOverOpts', 2);
        if ($GLOBALS["SL"]->REQ->has('all')) {
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
    
    // If none of this tree's nodes have conditions, this tree's cache is the most basic
    public function updateTreeOpts($treeID = -3)
    {
        $treeRow = $GLOBALS["SL"]->treeRow;
        if ($treeID <= 0) $treeID = $GLOBALS["SL"]->treeID;
        else $treeRow = SLTree::find($treeID);
        if ($treeRow->TreeType == 'Page') {
            $skipConds = [];
            $testCond = SLConditions::where('CondTag', '#TestLink')
                ->where('CondDatabase', $treeRow->TreeDatabase)
                ->first();
            if (!$testCond || !isset($testCond->CondID)) $testCond = SLConditions::where('CondTag', '#TestLink')->first();
            if ($testCond && isset($testCond->CondID)) $skipConds[] = $testCond->CondID;
            $chk = DB::select( DB::raw( "SELECT c.`CondNodeCondID`, n.`NodeID` FROM `SL_ConditionsNodes` c 
                LEFT OUTER JOIN `SL_Node` n ON c.`CondNodeNodeID` LIKE n.`NodeID` 
                WHERE n.`NodeTree` LIKE '" . $treeID . "'" . ((sizeof($skipConds) > 0) 
                    ? " AND c.`CondNodeCondID` NOT IN ('" . implode("', '", $skipConds) . "')" : "") ) );
            if ($chk && sizeof($chk) > 0) {
                if ($treeRow->TreeOpts%29 > 0) $treeRow->TreeOpts *= 29;
            } else {
                if ($treeRow->TreeOpts%29 == 0) $treeRow->TreeOpts = $treeRow->TreeOpts/29;
            }
            $treeRow->save();
        }
        return true;
    }
    
}
