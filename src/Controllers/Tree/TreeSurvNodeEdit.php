<?php
/**
  * TreeSurvNodeEdit is a mid-level class extending SurvLoop's core tree class
  * with the functions needed to save node edits using the original interface.
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
use SurvLoop\Models\User;
use SurvLoop\Models\SLDefinitions;
use SurvLoop\Models\SLTree;
use SurvLoop\Models\SLNode;
use SurvLoop\Models\SLNodeSaves;
use SurvLoop\Models\SLNodeResponses;
use SurvLoop\Models\SLConditions;
use SurvLoop\Models\SLDataLoop;
use SurvLoop\Models\SLConditionsNodes;
use SurvLoop\Models\SLEmails;
use SurvLoop\Controllers\Tree\TreeSurvForm;

class TreeSurvNodeEdit extends TreeSurvForm
{   
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
                $opts = [5, 11, 13, 17, 23, 29, 31, 37, 41, 43, 47, 53, 59, 61, 67, 71, 73, 79, 83, 89, 97, 101];
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
                        if ($GLOBALS["SL"]->REQ->has('pageBg') && intVal($GLOBALS["SL"]->REQ->pageBg) == 67) {
                            if ($GLOBALS["SL"]->treeRow->TreeOpts%67 > 0) {
                                $GLOBALS["SL"]->treeRow->TreeOpts *= 67;
                                $GLOBALS["SL"]->treeRow->save();
                            }
                        } else {
                            if ($GLOBALS["SL"]->treeRow->TreeOpts%67 == 0) {
                                $GLOBALS["SL"]->treeRow->TreeOpts = $GLOBALS["SL"]->treeRow->TreeOpts/67;
                                $GLOBALS["SL"]->treeRow->save();
                            }
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
                        $treeOpts = [
                            ['homepage',  7],
                            ['adminPage', 3],
                            ['volunPage', 17],
                            ['partnPage', 41], 
                            ['staffPage', 43]
                        ];
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
                        if ($GLOBALS["SL"]->REQ->has('delCond' . $condID . '') 
                            && $GLOBALS["SL"]->REQ->get('delCond' . $condID . '') == 'Y') {
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
                $this->saveTestAB($request, $node->nodeID);
                
                if ($node->nodeRow->NodeType == 'Layout Row' && $nodeIN <= 0) { // new row, so create default columns
                    if ($node->nodeRow->NodeCharLimit > 0 && $GLOBALS["SL"]->treeID > 0) {
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
    
    protected function saveTestAB(Request $request, $nodeID)
    {
        if (sizeof($GLOBALS["SL"]->condABs) > 0) {
            foreach ($GLOBALS["SL"]->condABs as $i => $ab) {
                if ($GLOBALS["SL"]->REQ->has('delCond' . $ab[0] . '') 
                    && $GLOBALS["SL"]->REQ->get('delCond' . $ab[0] . '') == 'Y') {
                    SLConditionsNodes::where('CondNodeCondID', $ab[0])
                        ->where('CondNodeNodeID', $nodeID)
                        ->delete();
                }
            }
        }
        if ($request->has('addTestAB') && trim($request->get('addTestAB')) != '') {
            $condID = -3;
            $which = 'A';
            if (trim($request->get('addTestAB')) == 'NewAB') {
                $condID = $this->addTestAB($request);
                if ($request->has('addTestABwhich') && trim($request->get('addTestABwhich')) != '') {
                    $which = trim($request->get('addTestABwhich'));
                }
            } elseif (strpos($request->get('addTestAB'), '.') !== false) {
                list($condID, $which) = explode('.', trim($request->get('addTestAB')));
                $condID = intVal($condID);
            }
            if ($condID > 0 && $nodeID > 0) {
                $newCond = new SLConditionsNodes;
                $newCond->CondNodeCondID = $condID;
                $newCond->CondNodeNodeID = $nodeID;
                if ($which == 'B') {
                    $newCond->CondNodeLoopID = -1;
                }
                $newCond->save();
            }
        }
    }
    
    protected function addTestAB(Request $request)
    {
        if ($request->has('addTestABdesc') && trim($request->get('addTestABdesc')) != '') {
            $cond = new SLConditions;
            $cond->CondDatabase = $this->dbID;
            $cond->CondOperator = 'AB TEST';
            $cond->CondTag      = '%AB Tree' . $this->treeID;
            $cond->CondDesc     = trim($request->get('addTestABdesc'));
            $cond->save();
            return $cond->getKey();
        }
        return -3;
    }
    
    // If none of this tree's nodes have conditions, this tree's cache is the most basic
    public function updateTreeOpts($treeID = -3)
    {
        $treeRow = $GLOBALS["SL"]->treeRow;
        if ($treeID <= 0) {
            $treeID = $GLOBALS["SL"]->treeID;
        } else {
            $treeRow = SLTree::find($treeID);
        }
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
