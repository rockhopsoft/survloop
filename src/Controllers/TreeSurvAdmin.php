<?php
/**
  * TreeSurvAdmin is a higher-level class extending SurvLoop's core tree class
  * with tools to edit the tree itself.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
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
use SurvLoop\Controllers\TreeSurvForm;

class TreeSurvAdmin extends TreeSurvForm
{
    protected $canEditTree = false;
    
    protected function initExtra(Request $request)
    {
        foreach ($this->allNodes as $nID => $nodeObj) {
            $this->allNodes[$nID]->fillNodeRow();
        }
        $this->canEditTree = false;
    	if ($this->v["uID"] > 0) $this->canEditTree = $this->v["user"]->hasRole('administrator|databaser');
        return true;
    }
    
    public function adminNodeEdit($nodeIN, Request $request, $currPage = '') 
    {
        $this->survLoopInit($request, $currPage);
        $resLimit = 60;
        $node = $parent = NULL;
        if ($nodeIN > 0) {
            $node = $this->loadNode(SLNode::find($nodeIN));
            $node->fillNodeRow($nodeIN);
        }
        if ($nodeIN <= 0 || !$node) {
            $node = $this->loadNode();
            $node->nodeRow->NodeTree        = $GLOBALS["SL"]->treeID;
            $node->nodeRow->NodeParentID    = (($GLOBALS["SL"]->REQ->has('parent')) ? $GLOBALS["SL"]->REQ->parent 
                : (($GLOBALS["SL"]->REQ->has('nodeParentID')) ? $GLOBALS["SL"]->REQ->nodeParentID : -3));
            $node->parentID = $node->nodeRow->NodeParentID;
            $node->nodeRow->NodeParentOrder = 0;
            $node->nodeRow->NodeOpts        = 1;
            if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
                $node->nodeRow->NodeType    = 'Instructions';
            } else {
                $node->nodeRow->NodeType    = 'Text';
            }
            if ($node->parentID > 0) {
                $parent = (($node->parentID > 0) ? SLNode::find($node->parentID) : []);
                if ($parent && isset($parent->NodeType)) {
                    if (in_array($parent->NodeType, ['Data Print Block', 'Data Print Columns'])) {
                        $node->nodeRow->NodeType = 'Data Print Row';
                    } elseif ($parent->NodeType == 'Loop Cycle') {
                        $grandParent = (($parent->NodeParentID > 0) ? SLNode::find($parent->NodeParentID) : []);
                        if ($grandParent && isset($grandParent->NodeType) 
                            && in_array($grandParent->NodeType, ['Data Print Block', 'Data Print Columns'])) {
                            $node->nodeRow->NodeType = 'Data Print Row';
                        }
                    }
                }
            }
            $node->nodeType = $node->nodeRow->NodeType;
        }
        $parent = (($node->parentID > 0) ? SLNode::find($node->parentID) 
            : (($GLOBALS["SL"]->REQ->has('parent')) ? SLNode::find($GLOBALS["SL"]->REQ->parent) : []));
        //echo '<pre>'; print_r($node->dataManips); echo '</pre>';
        if ($GLOBALS["SL"]->REQ->has('sub') && $this->canEditTree) {
            $redirOver = '';
            if ($GLOBALS["SL"]->REQ->has('deleteNode') && intVal($GLOBALS["SL"]->REQ->get('deleteNode')) == 1) {
                $this->treeAdminNodeDelete($node->nodeRow->NodeID);
            } else {
                if ($nodeIN <= 0) {
                    $node = $this->treeAdminNodeNew($node);
                }
                if (!$node->nodeRow || !isset($node->nodeRow->NodeOpts)) {
                    $node->fillNodeRow();
                }
                
                if (intVal($node->nodeRow->NodeOpts) <= 1) {
                    $node->nodeRow->NodeOpts = 1;
                }
                if ($GLOBALS["SL"]->REQ->changeResponseMobile == 'desktop') {
                    if ($node->nodeRow->NodeOpts%2 > 0) {
                        $node->nodeRow->NodeOpts *= 2;
                    }
                } elseif ($node->nodeRow->NodeOpts%2 == 0) {
                    $node->nodeRow->NodeOpts = $node->nodeRow->NodeOpts/2;
                }
                $opts = [5, 11, 13, 17, 23, 29, 31, 37, 41, 43, 47, 53, 59, 61, 67, 71, 73, 79, 83, 89, 97];
                $optsDesktop = [11, 17];
                foreach ($opts as $o) {
                    if ($GLOBALS["SL"]->REQ->has('opts'.$o.'') && intVal($GLOBALS["SL"]->REQ->get('opts'.$o.'')) == $o
                        && (!in_array($o, $optsDesktop) || $node->nodeRow->NodeOpts%2 == 0)) {
                        if ($node->nodeRow->NodeOpts%$o > 0) $node->nodeRow->NodeOpts *= $o;
                    } elseif ($node->nodeRow->NodeOpts%$o == 0) {
                        $node->nodeRow->NodeOpts = $node->nodeRow->NodeOpts/$o;
                    }
                }
                $isPageRoot = ($GLOBALS["SL"]->treeRow->TreeType == 'Page' && $node->nodeRow->NodeParentID <= 0);
                $node->nodeRow->NodePromptText      = trim($GLOBALS["SL"]->REQ->get('nodePromptText'));
                $node->nodeRow->NodePromptNotes     = trim($GLOBALS["SL"]->REQ->get('nodePromptNotes'));
                $node->nodeRow->NodePromptAfter     = trim($GLOBALS["SL"]->REQ->get('nodePromptAfter'));
                $node->nodeRow->NodeInternalNotes   = trim($GLOBALS["SL"]->REQ->get('nodeInternalNotes'));
                $node->nodeRow->NodeDefault         = trim($GLOBALS["SL"]->REQ->get('nodeDefault'));
                $node->nodeRow->NodeTextSuggest     = trim($GLOBALS["SL"]->REQ->get('nodeTextSuggest'));
                $node->nodeRow->NodeDataBranch      = trim($GLOBALS["SL"]->REQ->get('nodeDataBranch'));
                $node->nodeRow->NodeDataStore       = trim($GLOBALS["SL"]->REQ->get('nodeDataStore'));
                $node->nodeRow->NodeCharLimit       = intVal($GLOBALS["SL"]->REQ->get('nodeCharLimit'));
                if (in_array($GLOBALS["SL"]->REQ->nodeType, ['page', 'loop']) || $isPageRoot) {
                    $node->nodeRow->NodePromptNotes = trim($GLOBALS["SL"]->REQ->get('nodeSlug'));
                    $metaDesc = trim($GLOBALS["SL"]->REQ->get('pageDesc'));
                    if ($metaDesc == $GLOBALS["SL"]->sysOpts["meta-desc"]) $metaDesc = '';
                    $metaWords = trim($GLOBALS["SL"]->REQ->get('pageKey'));
                    if ($metaWords == $GLOBALS["SL"]->sysOpts["meta-keywords"]) $metaWords = '';
                    $metaImg = trim($GLOBALS["SL"]->REQ->get('pageImg'));
                    if (strpos($metaImg, $GLOBALS['SL']->sysOpts['app-url']) === 0) {
                        $metaImg = str_replace($GLOBALS['SL']->sysOpts['app-url'], '', $metaImg);
                    }
                    if ($metaImg == $GLOBALS["SL"]->sysOpts["meta-img"]) {
                        $metaImg = '';
                    }
                    $node->nodeRow->NodePromptAfter  = trim($GLOBALS["SL"]->REQ->get('pageTitle')) . '::M::' 
                        . $metaDesc . '::M::' . $metaWords . '::M::' . $metaImg;
                }
                if ($GLOBALS["SL"]->REQ->nodeType == 'page' || $isPageRoot) {
                    $node->nodeRow->NodeType        = 'Page';
                    $node->nodeRow->NodeCharLimit   = intVal($GLOBALS["SL"]->REQ->get('pageFocusField'));
                    if ($isPageRoot) {
                        if ($GLOBALS["SL"]->REQ->has('reportPage') && intVal($GLOBALS["SL"]->REQ->reportPage) == 13) {
                            if ($GLOBALS["SL"]->treeRow->TreeOpts%13 > 0) {
                                $GLOBALS["SL"]->treeRow->TreeOpts *= 13;
                                $GLOBALS["SL"]->treeRow->save();
                            }
                            if ($GLOBALS["SL"]->REQ->has('reportPageTree') 
                                && intVal($GLOBALS["SL"]->REQ->reportPageTree) > 0) {
                                $node->nodeRow->NodeResponseSet = $GLOBALS["SL"]->REQ->reportPageTree;
                            } else {
                                $node->nodeRow->NodeResponseSet = null;
                            }
                        } else {
                            if ($GLOBALS["SL"]->treeRow->TreeOpts%13 == 0) {
                                $GLOBALS["SL"]->treeRow->TreeOpts = $GLOBALS["SL"]->treeRow->TreeOpts/13;
                                $GLOBALS["SL"]->treeRow->save();
                            }
                            $node->nodeRow->NodeResponseSet = null;
                        }
                    }
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'branch') {
                    $node->nodeRow->NodeType = 'Branch Title';
                    $node->nodeRow->NodePromptText  = trim($GLOBALS["SL"]->REQ->get('branchTitle'));
                } elseif (in_array($GLOBALS["SL"]->REQ->nodeType, ['instruct', 'instructRaw'])) {
                    $node->nodeRow->NodeType        = 'Instructions' 
                        . (($GLOBALS["SL"]->REQ->nodeType == 'instructRaw') ? ' Raw' : '');
                    $node->nodeRow->NodePromptText  = trim($GLOBALS["SL"]->REQ->get('nodeInstruct'));
                    $node->nodeRow->NodePromptAfter = trim($GLOBALS["SL"]->REQ->get('instrPromptAfter'));
                    if ($GLOBALS["SL"]->REQ->has('opts37') && intVal($GLOBALS["SL"]->REQ->get('opts37')) == 37) {
                        if ($node->nodeRow->NodeOpts%37 > 0) {
                            $node->nodeRow->NodeOpts *= 37;
                        }
                    } elseif ($node->nodeRow->NodeOpts%37 == 0) {
                        $node->nodeRow->NodeOpts    = $node->nodeRow->NodeOpts/37;
                    }
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'dataPrint') {
                    $node->nodeRow->NodeType        = trim($GLOBALS["SL"]->REQ->get('nodeTypeD'));
                    $node->nodeRow->NodeDataStore   = trim($GLOBALS["SL"]->REQ->get('nodeDataPull'));
                    $node->nodeRow->NodePromptText  = trim($GLOBALS["SL"]->REQ->get('nodeDataBlcTitle'));
                    $node->nodeRow->NodeDefault     = trim($GLOBALS["SL"]->REQ->get('nodeDataHideIf'));
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'heroImg') {
                    $node->nodeRow->NodeType        = 'Hero Image';
                    $node->nodeRow->NodeTextSuggest = trim($GLOBALS["SL"]->REQ->get('pageHeroImg'));
                    $node->nodeRow->NodePromptAfter = trim($GLOBALS["SL"]->REQ->get('pageHeroImgTxt'));
                    $node->nodeRow->NodeDefault     = trim($GLOBALS["SL"]->REQ->get('pageHeroImgBtn'));
                    $node->nodeRow->NodeResponseSet = trim($GLOBALS["SL"]->REQ->get('pageHeroImgUrl'));
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'loop') {
                    $node->nodeRow->NodeType        = 'Loop Root';
                    $node->nodeRow->NodePromptText  = trim($GLOBALS["SL"]->REQ->get('nodeLoopInstruct'));
                    $node->nodeRow->NodeDataBranch  = $loop = trim($GLOBALS["SL"]->REQ->get('nodeDataLoop'));
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
                    if ($GLOBALS["SL"]->REQ->has('stepLoop') && intVal($GLOBALS["SL"]->REQ->get('stepLoop')) == 1) {
                        $GLOBALS["SL"]->dataLoops[$loop]->DataLoopIsStep = 1;
                        $GLOBALS["SL"]->dataLoops[$loop]->DataLoopAutoGen = 0;
                        if ($GLOBALS["SL"]->REQ->has('stepLoopDoneField') 
                            && trim($GLOBALS["SL"]->REQ->get('stepLoopDoneField')) != '') {
                            $GLOBALS["SL"]->dataLoops[$loop]->DataLoopDoneFld 
                                = trim($GLOBALS["SL"]->REQ->get('stepLoopDoneField'));
                        }
                    } elseif (!$GLOBALS["SL"]->REQ->has('stdLoopAuto') 
                        || intVal($GLOBALS["SL"]->REQ->get('stdLoopAuto')) == 0) {
                        $GLOBALS["SL"]->dataLoops[$loop]->DataLoopAutoGen = 0;
                    }
                    $GLOBALS["SL"]->dataLoops[$loop]->save();
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'cycle') {
                    $loop = trim($GLOBALS["SL"]->REQ->get('nodeDataCycle'));
                    $node->nodeRow->NodeType        = 'Loop Cycle';
                    $node->nodeRow->NodeResponseSet = 'LoopItems::' . $loop;
                    if (trim($node->nodeRow->NodeDataBranch) == '' && $loop != '') {
                        $node->nodeRow->NodeDataBranch = $GLOBALS["SL"]->getLoopTable($loop);
                    }
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'sort') {
                    $node->nodeRow->NodeType        = 'Loop Sort';
                    $node->nodeRow->NodeResponseSet = 'LoopItems::' . trim($GLOBALS["SL"]->REQ->get('nodeDataSort'));
                    $node->nodeRow->NodeDataStore   = trim($GLOBALS["SL"]->REQ->get('DataStoreSort'));
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'data') {
                    $node->nodeRow->NodeType        = 'Data Manip: ' . $GLOBALS["SL"]->REQ->get('dataManipType');
                    if ($GLOBALS["SL"]->REQ->get('dataManipType') == 'Close Sess') {
                        $node->nodeRow->NodeResponseSet = $GLOBALS["SL"]->REQ->get('dataManipCloseSessTree');
                    } else {
                        $node->nodeRow->NodeDataStore   = trim($GLOBALS["SL"]->REQ->get('manipMoreStore'));
                        $node->nodeRow->NodeDefault     = trim($GLOBALS["SL"]->REQ->get('manipMoreVal'));
                        $node->nodeRow->NodeResponseSet = trim($GLOBALS["SL"]->REQ->get('manipMoreSet'));
                        for ($i=0; $i < $resLimit; $i++) {
                            if (trim($GLOBALS["SL"]->REQ->get('manipMore' . $i . 'Store')) != '') {
                                if (!isset($node->dataManips[$i])) {
                                    $node->dataManips[$i] = new SLNode;
                                    $node->dataManips[$i]->NodeTree        = $this->treeID;
                                    $node->dataManips[$i]->NodeType        = 'Data Manip: Update';
                                    $node->dataManips[$i]->NodeParentID    = $node->nodeID;
                                    $node->dataManips[$i]->NodeParentOrder = $i;
                                }
                                $node->dataManips[$i]->NodeDataStore 
                                    = trim($GLOBALS["SL"]->REQ->get('manipMore' . $i . 'Store'));
                                $node->dataManips[$i]->NodeDefault 
                                    = trim($GLOBALS["SL"]->REQ->get('manipMore' . $i . 'Val'));
                                $node->dataManips[$i]->NodeResponseSet 
                                    = trim($GLOBALS["SL"]->REQ->get('manipMore' . $i . 'Set'));
                                $node->dataManips[$i]->save();
                            } else {
                                if (isset($node->dataManips[$i])) {
                                    $node->dataManips[$i]->delete();
                                }
                            }
                        }
                    }
                } elseif (in_array($GLOBALS["SL"]->REQ->nodeType, ['survWidget', 'sendEmail'])) {
                    $node->nodeRow->NodeType        = (($GLOBALS["SL"]->REQ->nodeType == 'sendEmail') ? 'Send Email'
                        : $GLOBALS["SL"]->REQ->nodeSurvWidgetType);
                    $node->nodeRow->NodeResponseSet = $GLOBALS["SL"]->REQ->nodeSurvWidgetTree;
                    $node->nodeRow->NodeCharLimit   = intVal($GLOBALS["SL"]->REQ->nodeSurvWidgetLimit);
                    $node->nodeRow->NodePromptText  = $GLOBALS["SL"]->REQ->nodeSurvWidgetPre;
                    $node->nodeRow->NodePromptAfter = $GLOBALS["SL"]->REQ->nodeSurvWidgetPost;
                    if ($node->nodeRow->NodeType == 'Send Email') {
                        $node->nodeRow->NodePromptNotes = '::TO::' . (($GLOBALS["SL"]->REQ->has('widgetEmailTo')) 
                                ? implode(',', $GLOBALS["SL"]->REQ->widgetEmailTo) : '')
                            . '::CC::' . (($GLOBALS["SL"]->REQ->has('widgetEmailCC')) 
                                ? implode(',', $GLOBALS["SL"]->REQ->widgetEmailCC) : '')
                            . '::BCC::' . (($GLOBALS["SL"]->REQ->has('widgetEmailBCC')) 
                                ? implode(',', $GLOBALS["SL"]->REQ->widgetEmailBCC) : '');
                        $node->nodeRow->NodeDefault = (($GLOBALS["SL"]->REQ->has('widgetEmaDef')) 
                            ? $GLOBALS["SL"]->REQ->widgetEmaDef : -3);
                    } elseif (in_array($node->nodeRow->NodeType, ['Plot Graph', 'Line Graph'])) {
                        $node->nodeRow->NodePromptNotes = '::Y::' . (($GLOBALS["SL"]->REQ->has('nodeWidgGrphY')) 
                            ? $GLOBALS["SL"]->REQ->nodeWidgGrphY : '') . '::Ylab::' 
                            . (($GLOBALS["SL"]->REQ->has('nodeWidgGrphYlab')) 
                            ? $GLOBALS["SL"]->REQ->nodeWidgGrphYlab : '') . '::X::' 
                            . (($GLOBALS["SL"]->REQ->has('nodeWidgGrphX')) ? $GLOBALS["SL"]->REQ->nodeWidgGrphX : '') 
                            . '::Xlab::' . (($GLOBALS["SL"]->REQ->has('nodeWidgGrphXlab')) 
                            ? $GLOBALS["SL"]->REQ->nodeWidgGrphXlab : '') 
                            . '::Cnd::' . (($GLOBALS["SL"]->REQ->has('nodeWidgConds')) 
                            ? $GLOBALS["SL"]->REQ->nodeWidgConds : '');
                    } elseif (in_array($node->nodeRow->NodeType, ['Bar Graph'])) {
                        $node->nodeRow->NodePromptNotes = '::Y::' . (($GLOBALS["SL"]->REQ->has('nodeWidgBarY')) 
                            ? $GLOBALS["SL"]->REQ->nodeWidgBarY : '') . '::Ylab::' 
                            . (($GLOBALS["SL"]->REQ->has('nodeWidgBarYlab')) 
                            ? $GLOBALS["SL"]->REQ->nodeWidgBarYlab : '') . '::Lab1::' 
                            . (($GLOBALS["SL"]->REQ->has('nodeWidgBarL1')) ? $GLOBALS["SL"]->REQ->nodeWidgBarL1 : '')
                            . '::Lab2::' . (($GLOBALS["SL"]->REQ->has('nodeWidgBarL2')) 
                            ? $GLOBALS["SL"]->REQ->nodeWidgBarL2 : '')
                            . '::Clr1::' . (($GLOBALS["SL"]->REQ->has('nodeWidgBarC1')) 
                            ? $GLOBALS["SL"]->REQ->nodeWidgBarC1 : '')
                            . '::Clr2::' . (($GLOBALS["SL"]->REQ->has('nodeWidgBarC2')) 
                            ? $GLOBALS["SL"]->REQ->nodeWidgBarC2 : '')
                            . '::Opc1::' . (($GLOBALS["SL"]->REQ->has('nodeWidgBarO1')) 
                            ? $GLOBALS["SL"]->REQ->nodeWidgBarO1 : '')
                            . '::Opc2::' . (($GLOBALS["SL"]->REQ->has('nodeWidgBarO2')) 
                            ? $GLOBALS["SL"]->REQ->nodeWidgBarO2 : '')
                            . '::Hgt::' . (($GLOBALS["SL"]->REQ->has('nodeWidgHgt')) 
                            ? $GLOBALS["SL"]->REQ->nodeWidgHgt : '')
                            . '::Cnd::' . (($GLOBALS["SL"]->REQ->has('nodeWidgConds')) 
                            ? $GLOBALS["SL"]->REQ->nodeWidgConds : '');
                    } elseif (in_array($node->nodeRow->NodeType, ['Pie Chart'])) {
                        $node->nodeRow->NodePromptNotes = '';
                        
                    } elseif (in_array($node->nodeRow->NodeType, ['Map'])) {
                        $node->nodeRow->NodePromptNotes = '';
                        
                    }
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'layout') {
                    $node->nodeRow->NodeType          = $GLOBALS["SL"]->REQ->nodeLayoutType;
                    if ($node->nodeRow->NodeType == 'Layout Row') {
                        $node->nodeRow->NodeCharLimit = intVal($GLOBALS["SL"]->REQ->nodeLayoutLimitRow);
                    } elseif ($node->nodeRow->NodeType == 'Layout Column') {
                        $node->nodeRow->NodeCharLimit = intVal($GLOBALS["SL"]->REQ->nodeLayoutLimitCol);
                    } elseif ($node->nodeRow->NodeType == 'Layout Table') {
                        
                    }
                } elseif ($GLOBALS["SL"]->REQ->nodeType == 'bigButt') {
                    $node->nodeRow->NodeType        = 'Big Button';
                    $node->nodeRow->NodeResponseSet = trim($GLOBALS["SL"]->REQ->get('bigBtnStyle'));
                    $node->nodeRow->NodeDefault     = trim($GLOBALS["SL"]->REQ->get('bigBtnText'));
                    $node->nodeRow->NodeDataStore   = trim($GLOBALS["SL"]->REQ->get('bigBtnJS'));
                } else { // other normal response node
                    $node->nodeRow->NodeType = trim($GLOBALS["SL"]->REQ->get('nodeTypeQ'));
                    if (in_array($node->nodeRow->NodeType, ['Drop Down', 'U.S. States'])) {
                        $node->nodeRow->NodeTextSuggest = trim($GLOBALS["SL"]->REQ->dropDownSuggest);
                    } elseif ($node->nodeRow->NodeType == 'Spreadsheet Table') {
                        $node->nodeRow->NodeDataStore = trim($GLOBALS["SL"]->REQ->get('nodeDataStoreSprd'));
                        $node->nodeRow->NodeCharLimit = intVal($GLOBALS["SL"]->REQ->get('spreadTblMaxRows'));
                        $node->nodeRow->NodeResponseSet = ((!$GLOBALS["SL"]->REQ->has('spreadTblLoop')
                            && trim($GLOBALS["SL"]->REQ->spreadTblLoop) == '') ? ''
                            : 'LoopItems::' . $GLOBALS["SL"]->REQ->spreadTblLoop);
                    }
                    $newResponses = [];
                    if (!$GLOBALS["SL"]->REQ->has('spreadTblLoop') || trim($GLOBALS["SL"]->REQ->spreadTblLoop) == '') {
                        $node->nodeRow->NodeResponseSet = $GLOBALS["SL"]->postLoopsDropdowns('responseList');
                        if ($node->nodeRow->NodeResponseSet == '') {
                            for ($i=0; $i < 20; $i++) {
                                if ($GLOBALS["SL"]->REQ->has('response' . $i . '') 
                                    && trim($GLOBALS["SL"]->REQ->get('response' . $i . '')) != '') {
                                    $newResponses[] = [
                                        "eng"   => trim($GLOBALS["SL"]->REQ->get('response' . $i . '')),
                                        "value" => ((trim($GLOBALS["SL"]->REQ->get('response' . $i . 'Val')) != '') 
                                            ? trim($GLOBALS["SL"]->REQ->get('response' . $i . 'Val')) 
                                            : trim($GLOBALS["SL"]->REQ->get('response' . $i . ''))), 
                                        "kids"  => (($GLOBALS["SL"]->REQ->has('response' . $i . 'ShowKids')
                                            && $GLOBALS["SL"]->REQ->has('kidForkSel' . $i . '')) 
                                            ? intVal($GLOBALS["SL"]->REQ->get('kidForkSel' . $i . '')) : 0),
                                        "mutEx" => (($GLOBALS["SL"]->REQ->has('response' . $i . 'MutEx')) 
                                            ? intVal($GLOBALS["SL"]->REQ->get('response' . $i . 'MutEx')) : 0)
                                    ];
                                }
                            }
                        } elseif (strpos($node->nodeRow->NodeResponseSet, 'Definition::') !== false) {
                            $defs = SLDefinitions::where('DefSet', 'Value Ranges')
                                ->where('DefSubset', str_replace('Definition::', '', $node->nodeRow->NodeResponseSet))
                                ->orderBy('DefOrder', 'asc')
                                ->get();
                            if ($defs->isNotEmpty()) {
                                foreach ($defs as $i => $def) {
                                    $newResponses[] = [
                                        "eng"   => $def->DefValue,
                                        "value" => $def->DefID, 
                                        "kids"  => (($GLOBALS["SL"]->REQ->has('response' . $i . 'ShowKids')
                                            && $GLOBALS["SL"]->REQ->has('kidForkSel' . $i . '')) 
                                            ? intVal($GLOBALS["SL"]->REQ->get('kidForkSel' . $i . '')) : 0),
                                        "mutEx" => (($GLOBALS["SL"]->REQ->has('response' . $i . 'MutEx')) 
                                            ? intVal($GLOBALS["SL"]->REQ->get('response' . $i . 'MutEx')) : 0)
                                    ];
                                }
                            }
                        }
                    }
                    if (in_array($GLOBALS["SL"]->REQ->nodeTypeQ, ['Date', 'Date Picker', 'Date Time'])) {
                        $node->nodeRow->NodeCharLimit = intVal($GLOBALS["SL"]->REQ->get('dateOptRestrict'));
                    }
                    $node->nodeRow->save();
                    $node->nodeID = $node->nodeRow->NodeID;
                    $this->saveNewResponses($node, $newResponses, $resLimit);
                    if (in_array($node->nodeRow->NodeType, ['Text:Number', 'Slider'])) {
                        $this->saveSettingInResponse('numOptMin', $node, -1, 'minVal');
                        $this->saveSettingInResponse('numOptMax', $node, 1, 'maxVal');
                        $this->saveSettingInResponse('numIncr', $node, 2, 'incr', 'numUnit');
                    }
                }
                if (in_array($GLOBALS["SL"]->REQ->nodeType, ['instruct', 'instructRaw', 'layout'])) {
                    if ($node->nodeRow->NodeOpts%71 == 0) {
                        $node->nodeRow->NodeDefault = $GLOBALS["SL"]->REQ->get('blockBG') . ';;' 
                            . $GLOBALS["SL"]->REQ->get('blockText') . ';;' 
                            . $GLOBALS["SL"]->REQ->get('blockLink') . ';;' 
                            . $GLOBALS["SL"]->REQ->get('blockImg') . ';;' 
                            . $GLOBALS["SL"]->REQ->get('blockImgType') . ';;' 
                            . $GLOBALS["SL"]->REQ->get('blockImgFix') . ';;'
                            . $GLOBALS["SL"]->REQ->get('blockAlign') . ';;'
                            . $GLOBALS["SL"]->REQ->get('blockHeight');
                    } else {
                        $node->nodeRow->NodeDefault = '';
                    }
                }
                $node->nodeRow->NodeTree = $this->treeID;
                $node->nodeRow->save();
                
                if ($node->nodeRow->NodeParentID <= 0) {
                    if (isset($node->nodeRow->NodeDataBranch) 
                        && isset($GLOBALS["SL"]->tblI[$node->nodeRow->NodeDataBranch])
                        && (!isset($GLOBALS["SL"]->treeRow->coreTbl) || intVal($GLOBALS["SL"]->treeRow->coreTbl) <= 0)){
                        $newCore = $GLOBALS["SL"]->tblI[$node->nodeRow->NodeDataBranch];
                        if ($GLOBALS["SL"]->treeRow->TreeCoreTable != $newCore) {
                            $GLOBALS["SL"]->treeRow->TreeCoreTable = $newCore;
                            $GLOBALS["SL"]->treeRow->save();
                            $redirOver = '/dashboard/db/export/laravel/table-model/' . $node->nodeRow->NodeDataBranch;
                        }
                    }
                    if ($GLOBALS["SL"]->treeRow->TreeType == 'Page' && $node->nodeRow->NodeType == 'Page') {
                        $treeOpts = [ ['homepage', 7], ['adminPage', 3], ['volunPage', 17], ['partnPage', 41], 
                            ['staffPage', 43] ];
                        foreach ($treeOpts as $o) {
                            if ($GLOBALS["SL"]->REQ->has($o[0]) && intVal($GLOBALS["SL"]->REQ->get($o[0])) == $o[1]) {
                                if ($GLOBALS["SL"]->treeRow->TreeOpts%$o[1] > 0) {
                                    $GLOBALS["SL"]->treeRow->TreeOpts *= $o[1];
                                }
                            } elseif ($GLOBALS["SL"]->treeRow->TreeOpts%$o[1] == 0) {
                                $GLOBALS["SL"]->treeRow->TreeOpts = $GLOBALS["SL"]->treeRow->TreeOpts/$o[1];
                            }
                        }
                        $GLOBALS["SL"]->treeRow->TreeName      = trim($GLOBALS["SL"]->REQ->get('pageTitle'));
                        $GLOBALS["SL"]->treeRow->TreeSlug      = trim($GLOBALS["SL"]->REQ->nodeSlug);
                        $GLOBALS["SL"]->treeRow->TreeFirstPage = $node->nodeRow->NodeID;
                        $GLOBALS["SL"]->treeRow->TreeLastPage  = $node->nodeRow->NodeID;
                        $GLOBALS["SL"]->treeRow->save();
                    }
                }
                if ($GLOBALS["SL"]->REQ->has('condIDs') && is_array($GLOBALS["SL"]->REQ->condIDs) 
                    && sizeof($GLOBALS["SL"]->REQ->condIDs) > 0) {
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
                    || ($GLOBALS["SL"]->REQ->has('condHash') && trim($GLOBALS["SL"]->REQ->condHash) != ''
                        && trim($GLOBALS["SL"]->REQ->condHash) != '#')) {
                    $newCond = $GLOBALS["SL"]->saveEditCondition($GLOBALS["SL"]->REQ);
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
                        $colW = $GLOBALS["SL"]->getColsWidth($node->nodeRow->NodeCharLimit);
                        $evenTot = $node->nodeRow->NodeCharLimit*$colW;
                        for ($c=0; $c<$node->nodeRow->NodeCharLimit; $c++) {
                            $colNode = $this->loadNode();
                            $colNode->nodeRow->NodeTree        = $GLOBALS["SL"]->treeID;
                            $colNode->nodeRow->NodeParentID    = $node->nodeRow->NodeID;
                            $colNode->nodeRow->NodeParentOrder = $c;
                            $colNode->nodeRow->NodeOpts        = 1;
                            $colNode->nodeRow->NodeType        = 'Layout Column';
                            $colNode->nodeRow->NodeCharLimit   = $colW;
                            if ($c == 0 && $evenTot < 12) {
                                $colNode->nodeRow->NodeCharLimit += (12-$evenTot);
                            }
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
            foreach ($treeCaches as $cache) {
                Cache::forget($cache);
            }
            $redir = '/dashboard/' . (($GLOBALS["SL"]->treeRow->TreeType == 'Page') ? 'page/' : 'surv-')
                . $this->treeID . '/map?all=1&alt=1&refresh=1#n' . $node->nodeRow->NodeID;
            if ($redirOver != '') {
                $redir = $redirOver . '?redir64=' . base64_encode($redir);
            }
            return $this->redir($redir, true);
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
        if ($chk->isNotEmpty()) {
            foreach ($chk as $u) {
                if ($u->hasRole('administrator|staff|databaser')) {
                    $emailUsers["admin"][] = [$u->id, $u->email, $u->name];
                } elseif ($u->hasRole('volunteer')) {
                    $emailUsers["volun"][] = [$u->id, $u->email, $u->name];
                } else {
                    $emailUsers["users"][] = [$u->id, $u->email, $u->name];
                }
            }
        }
        $GLOBALS["SL"]->pageJAVA .= view('vendor.survloop.admin.tree.widget-email-edit-java', [
            "emailList"  => $emailList
            ]);
        $widgetEmail = view('vendor.survloop.admin.tree.widget-email-edit', [
            "node"       => $node, 
            "emailList"  => $emailList,
            "emailUsers" => $emailUsers
            ])->render();
        $treeList = SLTree::where('TreeType', 'Survey')
            ->where('TreeDatabase', $this->dbID)
            ->select('TreeID', 'TreeName')
            ->orderBy('TreeName', 'asc')
            ->get();
        $childNodes = SLNode::where('NodeParentID', ((isset($node->nodeRow->NodeID)) ? $node->nodeRow->NodeID : 0))
            ->orderBy('NodeParentOrder', 'asc')
            ->get();
        
        $currMeta = [ "title" => '', "slug" => '', "desc" => '', "wrds" => '', "img" => '', "base" => '' ];
        if (isset($node->extraOpts["meta-title"]) && trim($node->extraOpts["meta-title"]) != '') {
            $currMeta["title"] = $node->extraOpts["meta-title"];
        } else {
            $currMeta["title"] = $GLOBALS['SL']->treeRow->TreeName;
        }
        if ($GLOBALS['SL']->treeRow->TreeType == 'Page' && isset($GLOBALS['SL']->treeRow->TreeSlug)) {
            $currMeta["slug"] = $GLOBALS['SL']->treeRow->TreeSlug;
        } elseif (isset($node->nodeRow->NodePromptNotes)) {
            $currMeta["slug"] = $node->nodeRow->NodePromptNotes;
        }
        if (isset($node->extraOpts["meta-desc"]) && trim($node->extraOpts["meta-desc"]) != '') {
            $currMeta["desc"] = $node->extraOpts["meta-desc"];
        } else {
            $currMeta["desc"] = $GLOBALS['SL']->sysOpts['meta-desc'];
        }
        if (isset($node->extraOpts["meta-keywords"]) && trim($node->extraOpts["meta-keywords"]) != '') {
            $currMeta["wrds"] = $node->extraOpts["meta-keywords"];
        } else {
            $currMeta["wrds"] = $GLOBALS['SL']->sysOpts['meta-keywords'];
        }
        if (isset($node->extraOpts["meta-img"]) && trim($node->extraOpts["meta-img"]) != '') {
            $currMeta["img"] = ((substr($node->extraOpts["meta-img"], 0, 1) == '/') 
                ? $GLOBALS['SL']->sysOpts['app-url'] : '') . $node->extraOpts["meta-img"];
        } else {
            $currMeta["img"] = $GLOBALS['SL']->sysOpts['meta-img'];
        }
        $currMeta["base"] = $GLOBALS['SL']->treeBaseUrl(true, true);
        
        $this->addCondEditorAjax();
        if ($node->isInstruct()) {
            $this->v["needsWsyiwyg"] = true;
        }
        $GLOBALS["SL"]->pageJAVA .= view('vendor.survloop.admin.tree.node-edit-java', [
            "node"           => $node, 
            "resLimit"       => $resLimit
            ])->render();
        $GLOBALS["SL"]->pageAJAX .= view('vendor.survloop.admin.tree.node-edit-ajax', [
            "node"           => $node
            ])->render();
        if ($node->isInstruct()) {
            $GLOBALS["SL"]->pageAJAX .= ' $("#nodeInstructID").summernote({ height: 350 });';
        }
        return view('vendor.survloop.admin.tree.node-edit', [
            "canEditTree"    => $this->canEditTree, 
            "treeID"         => $this->treeID, 
            "node"           => $node, 
            "parentNode"     => $parent,
            "nodeTypes"      => $this->nodeTypes, 
            "REQ"            => $GLOBALS["SL"]->REQ, 
            "resLimit"       => $resLimit, 
            "defs"           => $GLOBALS["SL"]->allDefSets(),
            "nodeTypeSel"    => $nodeTypeSel, 
            "widgetEmail"    => $widgetEmail,
            "emailList"      => $emailList,
            "treeList"       => $treeList,
            "childNodes"     => $childNodes,
            "currMeta"       => $currMeta,
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
    
    public function saveSettingInResponse($fldName, $node, $settingInd = 0, $optName = '', $engFldName = '')
    {
        if ($optName == '') $optName = $fldName;
        if (($GLOBALS["SL"]->REQ->has($fldName) && trim($GLOBALS["SL"]->REQ->get($fldName)) != '')
            || ($GLOBALS["SL"]->REQ->has($engFldName) && trim($GLOBALS["SL"]->REQ->get($engFldName)) != '')){
            $extraOpt = SLNodeResponses::where('NodeResNode', $node->nodeID)
                ->where('NodeResOrd', $settingInd)
                ->first();
            if (!$extraOpt || !isset($extraOpt->NodeResNode)) {
                $extraOpt = new SLNodeResponses;
                $extraOpt->NodeResNode = $node->nodeID;
                $extraOpt->NodeResOrd  = $settingInd;
            }
            if ($GLOBALS["SL"]->REQ->has($fldName) && trim($GLOBALS["SL"]->REQ->get($fldName)) != '') {
                $extraOpt->NodeResValue = $GLOBALS["SL"]->REQ->get($fldName);
            }
            if (trim($engFldName) != '' && $GLOBALS["SL"]->REQ->has($engFldName) 
                && trim($GLOBALS["SL"]->REQ->get($engFldName)) != '') {
                $extraOpt->NodeResEng = $GLOBALS["SL"]->REQ->get($engFldName);
            }
            $extraOpt->save();
        } elseif (isset($node->extraOpts[$optName]) && $node->extraOpts[$optName] !== false) {
            SLNodeResponses::where('NodeResNode', $node->nodeID)
                ->where('NodeResOrd', $settingInd)
                ->delete();
        }
        return true;
    }
    
    
    protected function adminBasicPrintNode($tierNode = [], $tierDepth = 0)
    {
        $tierDepth++;
        if (!isset($this->v["pageCnt"])) $this->v["pageCnt"] = 0;
        if (sizeof($tierNode) > 0 && $tierNode[0] > 0) {
            if ($this->hasNode($tierNode[0])) {
                $this->allNodes[$tierNode[0]]->fillNodeRow();
                if ($this->allNodes[$tierNode[0]]->isPage()) $this->v["pageCnt"]++;
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
        if ($pubPrint) $this->v["isPrint"] = true;
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
                                if (!isset($fnlVals[strtolower($res)])) $fnlVals[strtolower($res)] = 0;
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
                        . 'slBlueLight noUnd"><i class="fa fa-expand fa-flip-horizontal"></i></a></div>';
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
                            ? ' <span class="slRedDark">*</span> ' : '') . '</h3><div class="pT5">';
                        if (sizeof($tierNode[1]) > 0) {
                            $retVal .= '<a href="#n' . $nID . '" id="adminNode' . $nID . 'Expand" '
                                . 'class="slBlueLight noUnd"><i class="fa fa-expand fa-flip-horizontal"'
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
                    foreach ($tierNode[1] as $next) $retVal .= $this->adminResponseNodeStats($next, $tierDepth);
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
    
    
    
    
    protected function adminBasicDropdownNode($tierNode = [], $tierDepth = 0, $preSel = -3)
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
            /* // auto-setting 
            $skipConds = [];
            $testCond = SLConditions::where('CondTag', '#TestLink')
                ->where('CondDatabase', $treeRow->TreeDatabase)
                ->first();
            if (!$testCond || !isset($testCond->CondID)) {
                $testCond = SLConditions::where('CondTag', '#TestLink')
                    ->first();
            }
            if ($testCond && isset($testCond->CondID)) $skipConds[] = $testCond->CondID;
            $chk = DB::select( DB::raw( "SELECT c.`CondNodeCondID`, n.`NodeID` FROM `SL_ConditionsNodes` c 
                LEFT OUTER JOIN `SL_Node` n ON c.`CondNodeNodeID` LIKE n.`NodeID` 
                WHERE n.`NodeTree` LIKE '" . $treeID . "'" . ((sizeof($skipConds) > 0) 
                    ? " AND c.`CondNodeCondID` NOT IN ('" . implode("', '", $skipConds) . "')" : "") ) );
            if ($chk && sizeof($chk)) {
                if ($treeRow->TreeOpts%29 > 0) $treeRow->TreeOpts *= 29;
            } else {
                if ($treeRow->TreeOpts%29 == 0) $treeRow->TreeOpts = $treeRow->TreeOpts/29;
            }
            $treeRow->save();
            */
        }
        return true;
    }
    
    
}
