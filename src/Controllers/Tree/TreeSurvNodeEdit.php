<?php
/**
  * TreeSurvNodeEdit is a mid-level class extending Survloop's core tree class
  * with the functions needed to save node edits using the original interface.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.1.12
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
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvForm;

class TreeSurvNodeEdit extends TreeSurvForm
{   
    public function adminNodeEdit($nodeIN, Request $request, $currPage = '') 
    {
        $this->survloopInit($request, $currPage);
        $resLimit = 60;
        $node = $parent = NULL;
        if ($nodeIN > 0) {
            $node = $this->loadNode(SLNode::find($nodeIN));
            $node->fillNodeRow($nodeIN);
        }
        if ($nodeIN <= 0 || !$node) {
            $node = $this->loadNode();
            $node->nodeRow->node_tree = $GLOBALS["SL"]->treeID;
            $node->nodeRow->node_parent_id = -3;
            if ($GLOBALS["SL"]->REQ->has('parent')) {
                $node->nodeRow->node_parent_id = $GLOBALS["SL"]->REQ->parent;
            } elseif ($GLOBALS["SL"]->REQ->has('nodeParentID')) {
                $node->nodeRow->node_parent_id = $GLOBALS["SL"]->REQ->nodeParentID;
            }
            $node->parentID = $node->nodeRow->node_parent_id;
            $node->nodeRow->node_parent_order = 0;
            $node->nodeRow->node_opts = 1;
            if ($GLOBALS["SL"]->treeRow->tree_type == 'Page') {
                $node->nodeRow->node_type = 'Instructions';
            } else {
                $node->nodeRow->node_type = 'Text';
            }
            if ($node->parentID > 0) {
                $parent = [];
                if ($node->parentID > 0) {
                    $parent = SLNode::find($node->parentID);
                }
                if ($parent && isset($parent->node_type)) {
                    $types = ['Data Print Block', 'Data Print Columns'];
                    if (in_array($parent->node_type, $types)) {
                        $node->nodeRow->node_type = 'Data Print Row';
                    } elseif ($parent->node_type == 'Loop Cycle') {
                        $grandParent = [];
                        if ($parent->node_parent_id > 0) {
                            $grandParent = SLNode::find($parent->node_parent_id);
                        }
                        if ($grandParent 
                            && isset($grandParent->node_type) 
                            && in_array($grandParent->node_type, $types)) {
                            $node->nodeRow->node_type = 'Data Print Row';
                        }
                    }
                }
            }
            $node->nodeType = $node->nodeRow->node_type;
        }
        if (!isset($node->nodeRow->node_opts) 
            || intVal($node->nodeRow->node_opts) <= 0) {
            $node->nodeRow->node_opts = 1;
            $node->nodeRow->save();
        }
        //$node = $parent = [];
        if ($node->parentID > 0) {
            $parent = SLNode::find($node->parentID);
        } elseif ($GLOBALS["SL"]->REQ->has('parent')) {
            $parent = SLNode::find($GLOBALS["SL"]->REQ->parent);
        }
        if ($GLOBALS["SL"]->REQ->has('sub') && $this->canEditTree) {
            $redirOver = $this->adminNodeEditSave($node, $nodeIN, $resLimit, $request);
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
            $redir = '/dashboard/' 
                . (($GLOBALS["SL"]->treeRow->tree_type == 'Page') ? 'page/' : 'surv-')
                . $this->treeID . '/map?all=1&alt=1&refresh=1#n' . $node->nodeRow->node_id;
            if ($redirOver != '') {
                $redir = $redirOver . '?redir64=' . base64_encode($redir);
            }
            return $this->redir($redir, true);
        }
        
        $nodeTypeSel = '';
        if ($GLOBALS["SL"]->treeRow->tree_type == 'Page' 
            && $node->nodeRow->node_id == $GLOBALS["SL"]->treeRow->tree_root) {
            $nodeTypeSel = ' DISABLED ';
        }
        $emailList = SLEmails::where('email_type', 'NOT LIKE', 'Blurb')
            ->orderBy('email_name', 'asc')
        	->orderBy('email_type', 'asc')
        	->get();
        $emailUsers = [
            "admin" => [], 
            "volun" => [], 
            "users" => [] 
        ];
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
        $GLOBALS["SL"]->pageJAVA .= view(
            'vendor.survloop.admin.tree.widget-email-edit-java', 
            [ "emailList"  => $emailList ]
        );
        $widgetEmail = view(
            'vendor.survloop.admin.tree.widget-email-edit', 
            [
                "node"       => $node, 
                "emailList"  => $emailList,
                "emailUsers" => $emailUsers
            ]
        )->render();
        $treeList = SLTree::where('tree_type', 'Survey')
            ->where('tree_database', $this->dbID)
            ->select('tree_id', 'tree_name')
            ->orderBy('tree_name', 'asc')
            ->get();
        $parentID = ((isset($node->nodeRow->node_id)) ? $node->nodeRow->node_id : 0);
        $childNodes = SLNode::where('node_parent_id', $parentID)
            ->orderBy('node_parent_order', 'asc')
            ->get();
        
        $currMeta = [
            "title" => '', 
            "slug"  => '', 
            "desc"  => '', 
            "wrds"  => '', 
            "img"   => '', 
            "base"  => ''
        ];
        if (isset($node->extraOpts["meta-title"]) 
            && trim($node->extraOpts["meta-title"]) != '') {
            $currMeta["title"] = $node->extraOpts["meta-title"];
        } else {
            $currMeta["title"] = $GLOBALS['SL']->treeRow->tree_name;
        }
        if ($GLOBALS['SL']->treeRow->tree_type == 'Page' 
            && isset($GLOBALS['SL']->treeRow->tree_slug)) {
            $currMeta["slug"] = $GLOBALS['SL']->treeRow->tree_slug;
        } elseif (isset($node->nodeRow->node_prompt_notes)) {
            $currMeta["slug"] = $node->nodeRow->node_prompt_notes;
        }
        if (isset($node->extraOpts["meta-desc"]) 
            && trim($node->extraOpts["meta-desc"]) != '') {
            $currMeta["desc"] = $node->extraOpts["meta-desc"];
        } else {
            $currMeta["desc"] = $GLOBALS['SL']->sysOpts['meta-desc'];
        }
        if (isset($node->extraOpts["meta-keywords"]) 
            && trim($node->extraOpts["meta-keywords"]) != '') {
            $currMeta["wrds"] = $node->extraOpts["meta-keywords"];
        } else {
            $currMeta["wrds"] = $GLOBALS['SL']->sysOpts['meta-keywords'];
        }
        if (isset($node->extraOpts["meta-img"]) 
            && trim($node->extraOpts["meta-img"]) != '') {
            $path = ((substr($node->extraOpts["meta-img"], 0, 1) == '/') 
                ? $GLOBALS['SL']->sysOpts['app-url'] : '');
            $currMeta["img"] = $path . $node->extraOpts["meta-img"];
        } else {
            $currMeta["img"] = $GLOBALS['SL']->sysOpts['meta-img'];
        }
        $currMeta["base"] = $GLOBALS['SL']->treeBaseUrl(true, true);
        
        $this->addCondEditorAjax();
        if ($node->isInstruct()) {
            $this->v["needsWsyiwyg"] = true;
        }
        $GLOBALS["SL"]->pageJAVA .= view(
            'vendor.survloop.admin.tree.node-edit-java', 
            [
                "node"     => $node, 
                "resLimit" => $resLimit
            ]
        )->render();
        $GLOBALS["SL"]->pageAJAX .= view(
            'vendor.survloop.admin.tree.node-edit-ajax', 
            [ "node" => $node ]
        )->render();
        if ($node->isInstruct()) {
            $GLOBALS["SL"]->pageAJAX .= ' $("#nodeInstructID")'
                . '.summernote({ height: 350 });';
        }
        $dataBranchDrop = ((isset($node->nodeRow->node_data_branch)) 
            ? $node->nodeRow->node_data_branch : '');
        $dataBranchDrop = $GLOBALS["SL"]->tablesDropdown(
            $dataBranchDrop,
            'select database table to create deeper or more explicit data linkages'
        );
        $loopDrops = $this->printLoopsDropdowns(
            $node->nodeRow->node_response_set, 
            'responseList'
        );
        return view(
            'vendor.survloop.admin.tree.node-edit', 
            [
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
                "dataBranchDrop" => $dataBranchDrop,
                "loopDrops"      => $loopDrops
            ]
        );
    }
    
    public function printLoopsDropdowns($preSel = '', $fld = 'loopList', $manualOpt = true)
    {
        $currDefinition = $currLoopItems = $currTblRecs = $currTblAll = $currMonthFld = '';
        $currTblAllCond = 0;
        if (isset($preSel)) {
            if (strpos($preSel, 'Definition::') !== false) {
                $currDefinition = str_replace('Definition::', '', $preSel);
            } elseif (strpos($preSel, 'LoopItems::') !== false) {
                $currLoopItems = str_replace('LoopItems::', '', $preSel);
            } elseif (strpos($preSel, 'Table::') !== false) {
                $currTblRecs = str_replace('Table::', '', $preSel);
            } elseif (strpos($preSel, 'TableAll::') !== false) {
                $explode = str_replace('TableAll::', '', $preSel);
                list($currTblAll, $currTblAllCond) = $GLOBALS["SL"]
                    ->mexplode('::', $explode);
                $currTblAllCond = intVal($currTblAllCond);
            } elseif (strpos($preSel, 'Months::') !== false) {
                $currMonthFld = intVal(str_replace('Months::', '', $preSel));
            }
        }
        $monthNodeOpts = $this->getTreeNodeDropdownAll($currMonthFld);
        return view(
            'vendor.survloop.admin.tree.node-edit-loop-list', 
            [
                "fld"            => $fld,
                "manualOpt"      => $manualOpt,
                "defs"           => $GLOBALS["SL"]->allDefSets(),
                "currDefinition" => $currDefinition, 
                "currLoopItems"  => $currLoopItems, 
                "currTblRecs"    => $currTblRecs,
                "currTblAll"     => $currTblAll,
                "currTblAllCond" => $currTblAllCond,
                "currMonthFld"   => $currMonthFld,
                "monthNodeOpts"  => $monthNodeOpts
            ]
        )->render();
    }
    
    public function postLoopsDropdowns($fld = 'loopList')
    {
        $ret = '';
        if ($GLOBALS["SL"]->REQ->has($fld . 'Type')) {
            if (trim($GLOBALS["SL"]->REQ->input($fld . 'Type')) == 'auto-def') {
                if (trim($GLOBALS["SL"]->REQ->input($fld . 'Definition')) != '') {
                    $ret = 'Definition::' . $GLOBALS["SL"]->REQ->input($fld . 'Definition');
                }
            } elseif (trim($GLOBALS["SL"]->REQ->input($fld . 'Type')) == 'auto-loop') {
                if (trim($GLOBALS["SL"]->REQ->input($fld . 'LoopItems')) != '') {
                    $ret = 'LoopItems::' . $GLOBALS["SL"]->REQ->input($fld . 'LoopItems');
                }
            } elseif (trim($GLOBALS["SL"]->REQ->input($fld . 'Type')) == 'auto-tbl') {
                if (trim($GLOBALS["SL"]->REQ->input($fld . 'Tables')) != '') {
                    $ret = 'Table::' . $GLOBALS["SL"]->REQ->input($fld . 'Tables');
                }
            } elseif (trim($GLOBALS["SL"]->REQ->input($fld . 'Type')) == 'auto-tbl-all') {
                if (trim($GLOBALS["SL"]->REQ->input($fld . 'Tables')) != '' 
                    && $GLOBALS["SL"]->isAdmin) {
                    $ret = 'TableAll::' . $GLOBALS["SL"]->REQ->input($fld . 'Tables') . '::' 
                        . intVal($GLOBALS["SL"]->REQ->input($fld . 'TableCond'));
                }
            } elseif (trim($GLOBALS["SL"]->REQ->input($fld . 'Type')) == 'auto-months'
                && $GLOBALS["SL"]->REQ->has($fld . 'MonthFld')
                && trim($GLOBALS["SL"]->REQ->input($fld . 'MonthFld')) != '') {
                $ret = 'Months::' . intVal($GLOBALS["SL"]->REQ->input($fld . 'MonthFld'));
            }
        }
        return $ret;
    }

    protected function adminNodeEditSave(&$node, $nodeIN, $resLimit, Request $request, $currPage = '') 
    {
        $redirOver = '';
        if ($GLOBALS["SL"]->REQ->has('deleteNode') 
            && intVal($GLOBALS["SL"]->REQ->get('deleteNode')) == 1) {
            $this->treeAdminNodeDelete($node->nodeRow->node_id);
        } else {
            if ($nodeIN <= 0) {
                $node = $this->treeAdminNodeNew($node);
            }
            if (!$node->nodeRow || !isset($node->nodeRow->node_opts)) {
                $node->fillNodeRow();
            }
            
            if (intVal($node->nodeRow->node_opts) <= 1) {
                $node->nodeRow->node_opts = 1;
            }
            if ($GLOBALS["SL"]->REQ->changeResponseMobile == 'desktop') {
                if ($node->nodeRow->node_opts%2 > 0) {
                    $node->nodeRow->node_opts *= 2;
                }
            } elseif ($node->nodeRow->node_opts%2 == 0) {
                $node->nodeRow->node_opts = $node->nodeRow->node_opts/2;
            }
            $opts = [
                5, 11, 13, 17, 23, 29, 31, 37, 41, 43, 47, 53, 
                59, 61, 67, 71, 73, 79, 83, 89, 97, 101, 103
            ];
            $optsDesktop = [ 11, 17 ];
            foreach ($opts as $o) {
                if ($GLOBALS["SL"]->REQ->has('opts'.$o.'') 
                    && intVal($GLOBALS["SL"]->REQ->get('opts'.$o.'')) == $o
                    && (!in_array($o, $optsDesktop) || $node->nodeRow->node_opts%2 == 0)) {
                    if ($node->nodeRow->node_opts%$o > 0) {
                        $node->nodeRow->node_opts *= $o;
                    }
                } elseif ($node->nodeRow->node_opts%$o == 0) {
                    $node->nodeRow->node_opts = $node->nodeRow->node_opts/$o;
                }
            }
            $isPageRoot = ($GLOBALS["SL"]->treeRow->tree_type == 'Page' 
                && $node->nodeRow->node_parent_id <= 0);
            $node->nodeRow->node_prompt_text    = trim($GLOBALS["SL"]->REQ->nodePromptText);
            $node->nodeRow->node_prompt_notes   = trim($GLOBALS["SL"]->REQ->nodePromptNotes);
            $node->nodeRow->node_prompt_after   = trim($GLOBALS["SL"]->REQ->nodePromptAfter);
            $node->nodeRow->node_internal_notes = trim($GLOBALS["SL"]->REQ->nodeInternalNotes);
            $node->nodeRow->node_default        = trim($GLOBALS["SL"]->REQ->nodeDefault);
            $node->nodeRow->node_text_suggest   = trim($GLOBALS["SL"]->REQ->nodeTextSuggest);
            $node->nodeRow->node_data_branch    = trim($GLOBALS["SL"]->REQ->nodeDataBranch);
            $node->nodeRow->node_data_store     = trim($GLOBALS["SL"]->REQ->nodeDataStore);
            $node->nodeRow->node_char_limit     = intVal($GLOBALS["SL"]->REQ->nodeCharLimit);
            if (in_array($GLOBALS["SL"]->REQ->nodeType, ['page', 'loop']) || $isPageRoot) {
                $node->nodeRow->node_prompt_notes = trim($GLOBALS["SL"]->REQ->nodeSlug);
                $metaDesc = trim($GLOBALS["SL"]->REQ->get('pageDesc'));
                if ($metaDesc == $GLOBALS["SL"]->sysOpts["meta-desc"]) {
                    $metaDesc = '';
                }
                $metaWords = trim($GLOBALS["SL"]->REQ->get('pageKey'));
                if ($metaWords == $GLOBALS["SL"]->sysOpts["meta-keywords"]) {
                    $metaWords = '';
                }
                $metaImg = trim($GLOBALS["SL"]->REQ->get('pageImg'));
                if (strpos($metaImg, $GLOBALS['SL']->sysOpts['app-url']) === 0) {
                    $metaImg = str_replace($GLOBALS['SL']->sysOpts['app-url'], '', $metaImg);
                }
                if ($metaImg == $GLOBALS["SL"]->sysOpts["meta-img"]) {
                    $metaImg = '';
                }
                $node->nodeRow->node_prompt_after = trim($GLOBALS["SL"]->REQ->get('pageTitle')) 
                    . '::M::' . $metaDesc . '::M::' . $metaWords . '::M::' . $metaImg;
            }
            if ($GLOBALS["SL"]->REQ->nodeType == 'page' || $isPageRoot) {
                $node->nodeRow->node_type = 'Page';
                $node->nodeRow->node_char_limit = intVal($GLOBALS["SL"]->REQ->pageFocusField);
                if ($isPageRoot) {
                    $treeOpts = $GLOBALS["SL"]->treeRow->tree_opts;
                    if ($GLOBALS["SL"]->REQ->has('reportPageTree')
                        && intVal($GLOBALS["SL"]->REQ->reportPageTree) > 0) {
                        $node->nodeRow->node_response_set = $GLOBALS["SL"]->REQ->reportPageTree;
                        $node->nodeRow->save();
                        if ($GLOBALS["SL"]->REQ->has('reportPage')
                            && intVal($GLOBALS["SL"]->REQ->reportPage) == 13) {
                            if ($treeOpts%13 > 0) {
                                $treeOpts *= 13;
                            }
                        } else {
                            if ($treeOpts%13 == 0) {
                                $treeOpts = $treeOpts/13;
                            }
                        }
                        if ($GLOBALS["SL"]->REQ->has('searchPage') 
                            && intVal($GLOBALS["SL"]->REQ->searchPage) == 31) {
                            if ($treeOpts%31 > 0) {
                                $treeOpts *= 31;
                            }
                        } else {
                            if ($treeOpts%31 == 0) {
                                $treeOpts = $treeOpts/31;
                            }
                        }
                    } else {
                        if ($node->nodeRow->node_opts <= 0) {
                            $node->nodeRow->node_opts = 1;
                        }
                        $node->nodeRow->node_response_set = null;
                        $node->nodeRow->save();
                    }
                    if ($GLOBALS["SL"]->REQ->has('pageBg') 
                        && intVal($GLOBALS["SL"]->REQ->pageBg) == 67) {
                        if ($treeOpts%67 > 0) {
                            $treeOpts *= 67;
                        }
                    } else {
                        if ($treeOpts%67 == 0) {
                            $treeOpts = $treeOpts/67;
                        }
                    }
                    if ($GLOBALS["SL"]->REQ->has('pageFadeIn') 
                        && intVal($GLOBALS["SL"]->REQ->pageFadeIn) == 71) {
                        if ($treeOpts%71 > 0) {
                            $treeOpts *= 71;
                        }
                    } else {
                        if ($treeOpts%71 == 0) {
                            $treeOpts = $treeOpts/71;
                        }
                    }
                    if ($GLOBALS["SL"]->REQ->has('noCache') 
                        && intVal($GLOBALS["SL"]->REQ->noCache) == 29) {
                        if ($treeOpts%29 > 0) {
                            $treeOpts *= 29;
                        }
                    } else {
                        if ($treeOpts%29 == 0) {
                            $treeOpts = $treeOpts/29;
                        }
                    }
                    if ($GLOBALS["SL"]->treeRow->tree_opts != $treeOpts) {
                        $GLOBALS["SL"]->treeRow->tree_opts = $treeOpts;
                        $GLOBALS["SL"]->treeRow->save();
                    }
                }
            } elseif ($GLOBALS["SL"]->REQ->nodeType == 'branch') {
                $node->nodeRow->node_type = 'Branch Title';
                $node->nodeRow->node_prompt_text  = trim($GLOBALS["SL"]->REQ->branchTitle);
            } elseif (in_array($GLOBALS["SL"]->REQ->nodeType, ['instruct', 'instructRaw'])) {
                $node->nodeRow->node_type = 'Instructions';
                if ($GLOBALS["SL"]->REQ->nodeType == 'instructRaw') {
                    $node->nodeRow->node_type .= ' Raw';
                }
                $node->nodeRow->node_prompt_text = trim($GLOBALS["SL"]->REQ->nodeInstruct);
                $node->nodeRow->node_prompt_after = trim($GLOBALS["SL"]->REQ->instrPromptAfter);
                if ($GLOBALS["SL"]->REQ->has('opts37') 
                    && intVal($GLOBALS["SL"]->REQ->get('opts37')) == 37) {
                    if ($node->nodeRow->node_opts%37 > 0) {
                        $node->nodeRow->node_opts *= 37;
                    }
                } elseif ($node->nodeRow->node_opts%37 == 0) {
                    $node->nodeRow->node_opts = $node->nodeRow->node_opts/37;
                }
            } elseif ($GLOBALS["SL"]->REQ->nodeType == 'dataPrint') {
                $node->nodeRow->node_type         = trim($GLOBALS["SL"]->REQ->nodeTypeD);
                $node->nodeRow->node_data_store   = trim($GLOBALS["SL"]->REQ->nodeDataPull);
                $node->nodeRow->node_prompt_text  = trim($GLOBALS["SL"]->REQ->nodeDataBlcTitle);
                $node->nodeRow->node_default      = trim($GLOBALS["SL"]->REQ->nodeDataHideIf);
            } elseif ($GLOBALS["SL"]->REQ->nodeType == 'heroImg') {
                $node->nodeRow->node_type         = 'Hero Image';
                $node->nodeRow->node_text_suggest = trim($GLOBALS["SL"]->REQ->pageHeroImg);
                $node->nodeRow->node_prompt_after = trim($GLOBALS["SL"]->REQ->pageHeroImgTxt);
                $node->nodeRow->node_default      = trim($GLOBALS["SL"]->REQ->pageHeroImgBtn);
                $node->nodeRow->node_response_set = trim($GLOBALS["SL"]->REQ->pageHeroImgUrl);
            } elseif ($GLOBALS["SL"]->REQ->nodeType == 'loop') {
                $node->nodeRow->node_type         = 'Loop Root';
                $node->nodeRow->node_prompt_text  = trim($GLOBALS["SL"]->REQ->nodeLoopInstruct);
                $node->nodeRow->node_default  = $loop 
                    = trim($GLOBALS["SL"]->REQ->get('nodeDataLoop'));
                if (!isset($GLOBALS["SL"]->dataLoops[$loop])) {
                    $GLOBALS["SL"]->dataLoops[$loop] = new SLDataLoop;
                    $GLOBALS["SL"]->dataLoops[$loop]->data_loop_tree = $this->treeID;
                    $GLOBALS["SL"]->dataLoops[$loop]->data_loop_root = $node->nodeRow->node_id;
                } else {
                    $loopTbl = trim($GLOBALS["SL"]->dataLoops[$loop]->data_loop_table);
                    if ($loopTbl != '' && isset($GLOBALS["SL"]->tblAbbr[$loopTbl])) {
                        $node->nodeRow->node_data_store = $loopTbl . ':' 
                            . $GLOBALS["SL"]->tblAbbr[$loopTbl] . 'id';
                    }
                }
                $GLOBALS["SL"]->dataLoops[$loop]->data_loop_is_step  = 0;
                $GLOBALS["SL"]->dataLoops[$loop]->data_loop_auto_gen = 1;
                $GLOBALS["SL"]->dataLoops[$loop]->data_loop_done_fld = '';
                if ($GLOBALS["SL"]->REQ->has('stepLoop') 
                    && intVal($GLOBALS["SL"]->REQ->stepLoop) == 1) {
                    $GLOBALS["SL"]->dataLoops[$loop]->data_loop_is_step = 1;
                    $GLOBALS["SL"]->dataLoops[$loop]->data_loop_auto_gen = 0;
                    if ($GLOBALS["SL"]->REQ->has('stepLoopDoneField') 
                        && trim($GLOBALS["SL"]->REQ->stepLoopDoneField) != '') {
                        $GLOBALS["SL"]->dataLoops[$loop]->data_loop_done_fld 
                            = trim($GLOBALS["SL"]->REQ->stepLoopDoneField);
                    }
                } elseif (!$GLOBALS["SL"]->REQ->has('stdLoopAuto') 
                    || intVal($GLOBALS["SL"]->REQ->stdLoopAuto) == 0) {
                    $GLOBALS["SL"]->dataLoops[$loop]->data_loop_auto_gen = 0;
                }
                $GLOBALS["SL"]->dataLoops[$loop]->save();
            } elseif ($GLOBALS["SL"]->REQ->nodeType == 'cycle') {
                $loop = trim($GLOBALS["SL"]->REQ->nodeDataCycle);
                $node->nodeRow->node_type = 'Loop Cycle';
                $node->nodeRow->node_response_set = 'LoopItems::' . $loop;
                if (trim($node->nodeRow->node_data_branch) == '' && $loop != '') {
                    $node->nodeRow->node_data_branch = $GLOBALS["SL"]->getLoopTable($loop);
                }
            } elseif ($GLOBALS["SL"]->REQ->nodeType == 'sort') {
                $node->nodeRow->node_type = 'Loop Sort';
                $node->nodeRow->node_response_set = 'LoopItems::'
                    . trim($GLOBALS["SL"]->REQ->nodeDataSort);
                $node->nodeRow->node_data_store = trim($GLOBALS["SL"]->REQ->DataStoreSort);
            } elseif ($GLOBALS["SL"]->REQ->nodeType == 'data') {
                $node->nodeRow->node_type = 'Data Manip: ' . $GLOBALS["SL"]->REQ->dataManipType;
                if ($GLOBALS["SL"]->REQ->get('dataManipType') == 'Close Sess') {
                    $node->nodeRow->node_response_set = $GLOBALS["SL"]->REQ->dataManipCloseSessTree;
                } else {
                    $node->nodeRow->node_data_store = trim($GLOBALS["SL"]->REQ->manipMoreStore);
                    $node->nodeRow->node_default = trim($GLOBALS["SL"]->REQ->manipMoreVal);
                    $node->nodeRow->node_response_set = trim($GLOBALS["SL"]->REQ->manipMoreSet);
                    for ($i=0; $i < $resLimit; $i++) {
                        if (trim($GLOBALS["SL"]->REQ->get('manipMore' . $i . 'Store')) != '') {
                            if (!isset($node->dataManips[$i])) {
                                $node->dataManips[$i] = new SLNode;
                                $node->dataManips[$i]->node_tree = $this->treeID;
                                $node->dataManips[$i]->node_type = 'Data Manip: Update';
                                $node->dataManips[$i]->node_parent_id = $node->nodeID;
                                $node->dataManips[$i]->node_parent_order = $i;
                            }
                            $node->dataManips[$i]->node_data_store 
                                = trim($GLOBALS["SL"]->REQ->get('manipMore' . $i . 'Store'));
                            $node->dataManips[$i]->node_default 
                                = trim($GLOBALS["SL"]->REQ->get('manipMore' . $i . 'Val'));
                            $node->dataManips[$i]->node_response_set 
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
                $node->nodeRow->node_type = $GLOBALS["SL"]->REQ->nodeSurvWidgetType;
                if ($GLOBALS["SL"]->REQ->nodeType == 'sendEmail') {
                    $node->nodeRow->node_type = 'Send Email';
                }
                $node->nodeRow->node_response_set = $GLOBALS["SL"]->REQ->nodeSurvWidgetTree;
                $node->nodeRow->node_char_limit = intVal($GLOBALS["SL"]->REQ->nodeSurvWidgetLimit);
                $node->nodeRow->node_prompt_text = $GLOBALS["SL"]->REQ->nodeSurvWidgetPre;
                $node->nodeRow->node_prompt_after = $GLOBALS["SL"]->REQ->nodeSurvWidgetPost;
                if ($node->nodeRow->node_type == 'Send Email') {
                    $notes = '::TO::';
                    if ($GLOBALS["SL"]->REQ->has('widgetEmailTo')) {
                        $notes .= implode(',', $GLOBALS["SL"]->REQ->widgetEmailTo);
                    }
                    $notes .= '::CC::';
                    if ($GLOBALS["SL"]->REQ->has('widgetEmailCC')) {
                        $notes .= implode(',', $GLOBALS["SL"]->REQ->widgetEmailCC);
                    }
                    $notes .= '::BCC::';
                    if ($GLOBALS["SL"]->REQ->has('widgetEmailBCC')) {
                        $notes .= implode(',', $GLOBALS["SL"]->REQ->widgetEmailBCC);
                    }
                    $node->nodeRow->node_prompt_notes = $notes;
                    $node->nodeRow->node_default = -3;
                    if ($GLOBALS["SL"]->REQ->has('widgetEmaDef')) {
                        $node->nodeRow->node_default = $GLOBALS["SL"]->REQ->widgetEmaDef;
                    }
                } elseif (in_array($node->nodeRow->node_type, ['Plot Graph', 'Line Graph'])) {
                    $notes = '::Y::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgGrphY')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgGrphY;
                    }
                    $notes .= '::Ylab::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgGrphYlab')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgGrphYlab;
                    }
                    $notes .= '::X::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgGrphX')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgGrphX;
                    }
                    $notes .= '::Xlab::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgGrphXlab')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgGrphXlab;
                    }
                    $notes .= '::Cnd::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgConds')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgConds;
                    }
                    $node->nodeRow->node_prompt_notes = $notes;
                } elseif (in_array($node->nodeRow->node_type, ['Bar Graph'])) {
                    $notes = '::Y::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgBarY')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgBarY;
                    }
                    $notes .= '::Ylab::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgBarYlab')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgBarYlab;
                    }
                    $notes .= '::Lab1::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgBarL1')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgBarL1;
                    }
                    $notes .= '::Lab2::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgBarL2')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgBarL2;
                    }
                    $notes .= '::Clr1::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgBarC1')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgBarC1;
                    }
                    $notes .= '::Clr2::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgBarC2')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgBarC2;
                    }
                    $notes .= '::Opc1::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgBarO1')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgBarO1;
                    }
                    $notes .= '::Opc2::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgBarO2')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgBarO2;
                    }
                    $notes .= '::Hgt::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgHgt')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgHgt;
                    }
                    $notes .= '::Cnd::';
                    if ($GLOBALS["SL"]->REQ->has('nodeWidgConds')) {
                        $notes .= $GLOBALS["SL"]->REQ->nodeWidgConds;
                    }
                    $node->nodeRow->node_prompt_notes = $notes;
                } elseif (in_array($node->nodeRow->node_type, ['Pie Chart'])) {
                    $node->nodeRow->node_prompt_notes = '';
                    
                } elseif (in_array($node->nodeRow->node_type, ['Map'])) {
                    $node->nodeRow->node_prompt_notes = '';
                    
                }
            } elseif ($GLOBALS["SL"]->REQ->nodeType == 'layout') {
                $node->nodeRow->node_type = $GLOBALS["SL"]->REQ->nodeLayoutType;
                if ($node->nodeRow->node_type == 'Layout Row') {
                    $node->nodeRow->node_char_limit = intVal($GLOBALS["SL"]->REQ->nodeLayoutLimitRow);
                } elseif ($node->nodeRow->node_type == 'Layout Column') {
                    $node->nodeRow->node_char_limit = intVal($GLOBALS["SL"]->REQ->nodeLayoutLimitCol);
                } elseif ($node->nodeRow->node_type == 'Layout Table') {
                    
                }
            } elseif ($GLOBALS["SL"]->REQ->nodeType == 'bigButt') {
                $node->nodeRow->node_type        = 'Big Button';
                $node->nodeRow->node_response_set = trim($GLOBALS["SL"]->REQ->get('bigBtnStyle'));
                $node->nodeRow->node_default     = trim($GLOBALS["SL"]->REQ->get('bigBtnText'));
                $node->nodeRow->node_data_store   = trim($GLOBALS["SL"]->REQ->get('bigBtnJS'));
            } else { // other normal response node
                $node->nodeRow->node_type = trim($GLOBALS["SL"]->REQ->get('nodeTypeQ'));
                if (in_array($node->nodeRow->node_type, ['Drop Down', 'U.S. States'])) {
                    $node->nodeRow->node_text_suggest = trim($GLOBALS["SL"]->REQ->dropDownSuggest);
                } elseif ($node->nodeRow->node_type == 'Spreadsheet Table') {
                    $node->nodeRow->node_data_store = trim($GLOBALS["SL"]->REQ->get('nodeDataStoreSprd'));
                    $node->nodeRow->node_char_limit = intVal($GLOBALS["SL"]->REQ->get('spreadTblMaxRows'));
                    $node->nodeRow->node_response_set = '';
                    if ($GLOBALS["SL"]->REQ->has('spreadTblLoop')
                        || trim($GLOBALS["SL"]->REQ->spreadTblLoop) != '') {
                        $node->nodeRow->node_response_set = 'LoopItems::' 
                            . $GLOBALS["SL"]->REQ->spreadTblLoop;
                    }
                }
                $newResponses = [];
                if (!$GLOBALS["SL"]->REQ->has('spreadTblLoop') 
                    || trim($GLOBALS["SL"]->REQ->spreadTblLoop) == '') {
                    $node->nodeRow->node_response_set = $this->postLoopsDropdowns('responseList');
                    if ($node->nodeRow->node_response_set == '') {
                        for ($i=0; $i < 20; $i++) {
                            if ($GLOBALS["SL"]->REQ->has('response' . $i . '') 
                                && trim($GLOBALS["SL"]->REQ->get('response' . $i . '')) != '') {
                                $val = trim($GLOBALS["SL"]->REQ->get('response' . $i . ''));
                                if (trim($GLOBALS["SL"]->REQ->get('response' . $i . 'Val')) != '') {
                                    $val = trim($GLOBALS["SL"]->REQ->get('response' . $i . 'Val'));
                                }
                                $kids = 0;
                                if ($GLOBALS["SL"]->REQ->has('response' . $i . 'ShowKids')
                                    && $GLOBALS["SL"]->REQ->has('kidForkSel' . $i . '')) {
                                    $kids = intVal($GLOBALS["SL"]->REQ->get('kidForkSel' . $i . ''));
                                }
                                $mutEx = 0;
                                if ($GLOBALS["SL"]->REQ->has('response' . $i . 'MutEx')) {
                                    $mutEx = intVal($GLOBALS["SL"]->REQ->get('response' . $i . 'MutEx'));
                                }
                                $newResponses[] = [
                                    "eng"   => trim($GLOBALS["SL"]->REQ->get('response' . $i . '')),
                                    "value" => $val, 
                                    "kids"  => $kids,
                                    "mutEx" => $mutEx
                                ];
                            }
                        }
                    } elseif (strpos($node->nodeRow->node_response_set, 'Definition::') !== false) {
                        $defSet = str_replace('Definition::', '', $node->nodeRow->node_response_set);
                        $defs = SLDefinitions::where('def_set', 'Value Ranges')
                            ->where('def_subset', $defSet)
                            ->orderBy('def_order', 'asc')
                            ->get();
                        if ($defs->isNotEmpty()) {
                            foreach ($defs as $i => $def) {
                                $kids = 0;
                                if ($GLOBALS["SL"]->REQ->has('response' . $i . 'ShowKids')
                                    && $GLOBALS["SL"]->REQ->has('kidForkSel' . $i . '')) {
                                    $kids = intVal($GLOBALS["SL"]->REQ->get('kidForkSel' . $i . ''));
                                }
                                $mutEx = 0;
                                if ($GLOBALS["SL"]->REQ->has('response' . $i . 'MutEx')) {
                                    $mutEx = intVal($GLOBALS["SL"]->REQ->get('response' . $i . 'MutEx'));
                                }
                                $newResponses[] = [
                                    "eng"   => $def->def_value,
                                    "value" => $def->def_id, 
                                    "kids"  => $kids,
                                    "mutEx" => $mutEx
                                ];
                            }
                        }
                    }
                }
                if ($GLOBALS["SL"]->REQ->has('responseListType') 
                    && trim($GLOBALS["SL"]->REQ->responseListType) == 'auto-months'
                    && $GLOBALS["SL"]->REQ->has('responseListMonthFld') 
                    && trim($GLOBALS["SL"]->REQ->responseListMonthFld) != '') {
                    $node->nodeRow->node_response_set = 'Months::' 
                        . trim($GLOBALS["SL"]->REQ->responseListMonthFld);
                }
                if (in_array($GLOBALS["SL"]->REQ->nodeTypeQ, ['Date', 'Date Picker', 'Date Time'])) {
                    $node->nodeRow->node_char_limit = intVal($GLOBALS["SL"]->REQ->get('dateOptRestrict'));
                }
                $node->nodeRow->save();
                $node->nodeID = $node->nodeRow->node_id;
                $this->saveNewResponses($node, $newResponses, $resLimit);
                if (in_array($node->nodeRow->node_type, ['Text:Number', 'Slider'])) {
                    $this->saveSettingInResponse('numOptMin', $node, -1, 'minVal');
                    $this->saveSettingInResponse('numOptMax', $node, 1, 'maxVal');
                    $this->saveSettingInResponse('numIncr', $node, 2, 'incr', 'numUnit');
                }
            }
            if (in_array($GLOBALS["SL"]->REQ->nodeType, ['instruct', 'instructRaw', 'layout'])) {
                if ($node->nodeRow->node_opts%71 == 0) {
                    $node->nodeRow->node_default = $GLOBALS["SL"]->REQ->get('blockBG') . ';;' 
                        . $GLOBALS["SL"]->REQ->get('blockText') . ';;' 
                        . $GLOBALS["SL"]->REQ->get('blockLink') . ';;' 
                        . $GLOBALS["SL"]->REQ->get('blockImg') . ';;' 
                        . $GLOBALS["SL"]->REQ->get('blockImgType') . ';;' 
                        . $GLOBALS["SL"]->REQ->get('blockImgFix') . ';;'
                        . $GLOBALS["SL"]->REQ->get('blockAlign') . ';;'
                        . $GLOBALS["SL"]->REQ->get('blockHeight');
                } else {
                    $node->nodeRow->node_default = '';
                }
            }
            if ($node->nodeRow->node_opts <= 0) {
                $node->nodeRow->node_opts = 1;
            }
            $node->nodeRow->node_tree = $this->treeID;
            $node->nodeRow->save();
            
            if ($node->nodeRow->node_parent_id <= 0) {
                if (isset($node->nodeRow->node_data_branch) 
                    && isset($GLOBALS["SL"]->tblI[$node->nodeRow->node_data_branch])
                    && (!isset($GLOBALS["SL"]->treeRow->coreTbl) 
                        || intVal($GLOBALS["SL"]->treeRow->coreTbl) <= 0)) {
                    $newCore = $GLOBALS["SL"]->tblI[$node->nodeRow->node_data_branch];
                    if ($GLOBALS["SL"]->treeRow->tree_core_table != $newCore) {
                        $GLOBALS["SL"]->treeRow->tree_core_table = $newCore;
                        $GLOBALS["SL"]->treeRow->save();
                        $redirOver = '/dashboard/db/export/laravel/table-model/' 
                            . $node->nodeRow->node_data_branch;
                    }
                }
                if ($GLOBALS["SL"]->treeRow->tree_type == 'Page' 
                    && $node->nodeRow->node_type == 'Page') {
                    $treeOpts = [
                        [ 'homepage',  7  ],
                        [ 'adminPage', 3  ],
                        [ 'volunPage', 17 ],
                        [ 'partnPage', 41 ], 
                        [ 'staffPage', 43 ]
                    ];
                    foreach ($treeOpts as $o) {
                        if ($GLOBALS["SL"]->REQ->has($o[0]) 
                            && intVal($GLOBALS["SL"]->REQ->get($o[0])) == $o[1]) {
                            if ($GLOBALS["SL"]->treeRow->tree_opts%$o[1] > 0) {
                                $GLOBALS["SL"]->treeRow->tree_opts *= $o[1];
                            }
                        } elseif ($GLOBALS["SL"]->treeRow->tree_opts%$o[1] == 0) {
                            $GLOBALS["SL"]->treeRow->tree_opts = $GLOBALS["SL"]
                                ->treeRow->tree_opts/$o[1];
                        }
                    }
                    $GLOBALS["SL"]->treeRow->tree_name = trim($GLOBALS["SL"]->REQ->get('pageTitle'));
                    $GLOBALS["SL"]->treeRow->tree_slug = trim($GLOBALS["SL"]->REQ->nodeSlug);
                    $GLOBALS["SL"]->treeRow->tree_first_page = $node->nodeRow->node_id;
                    $GLOBALS["SL"]->treeRow->tree_last_page  = $node->nodeRow->node_id;
                    $GLOBALS["SL"]->treeRow->save();
                }
            }
            if ($GLOBALS["SL"]->REQ->has('condIDs') 
                && is_array($GLOBALS["SL"]->REQ->condIDs) 
                && sizeof($GLOBALS["SL"]->REQ->condIDs) > 0) {
                foreach ($GLOBALS["SL"]->REQ->condIDs as $condID) {
                    if ($GLOBALS["SL"]->REQ->has('delCond' . $condID . '') 
                        && $GLOBALS["SL"]->REQ->get('delCond' . $condID . '') == 'Y') {
                        SLConditionsNodes::where('cond_node_cond_id', $condID)
                            ->where('cond_node_node_id', $node->nodeID)
                            ->delete();
                    }
                }
            }

            $hasOldConds = ($GLOBALS["SL"]->REQ->has('oldConds') 
                && intVal($GLOBALS["SL"]->REQ->oldConds) > 0);
            $hasCondHash = ($GLOBALS["SL"]->REQ->has('condHash') 
                && trim($GLOBALS["SL"]->REQ->condHash) != ''
                && trim($GLOBALS["SL"]->REQ->condHash) != '#');
            if ($hasOldConds || $hasCondHash) {
                $newCond = $GLOBALS["SL"]->saveEditCondition($GLOBALS["SL"]->REQ);
                $newLink = new SLConditionsNodes;
                $newLink->cond_node_cond_id = $newCond->cond_id;
                $newLink->cond_node_node_id = $node->nodeID;
                if ($GLOBALS["SL"]->REQ->has('oldCondInverse') 
                    && intVal($GLOBALS["SL"]->REQ->oldCondInverse) == 1) {
                    $newLink->cond_node_loop_id = -1;
                }
                $newLink->save();
            }
            $this->saveTestAB($request, $node->nodeID);
            
            if ($node->nodeRow->node_type == 'Layout Row' && $nodeIN <= 0) { // new row, so create default columns
                if ($node->nodeRow->node_char_limit > 0 && $GLOBALS["SL"]->treeID > 0) {
                    $colW = $GLOBALS["SL"]->getColsWidth($node->nodeRow->node_char_limit);
                    $evenTot = $node->nodeRow->node_char_limit*$colW;
                    for ($c = 0; $c < $node->nodeRow->node_char_limit; $c++) {
                        $colNode = $this->loadNode();
                        $colNode->nodeRow->node_tree         = $GLOBALS["SL"]->treeID;
                        $colNode->nodeRow->node_parent_id    = $node->nodeRow->node_id;
                        $colNode->nodeRow->node_parent_order = $c;
                        $colNode->nodeRow->node_opts         = 1;
                        $colNode->nodeRow->node_type         = 'Layout Column';
                        $colNode->nodeRow->node_char_limit   = $colW;
                        if ($c == 0 && $evenTot < 12) {
                            $colNode->nodeRow->node_char_limit += (12-$evenTot);
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
        return $redirOver;
    }
    
    public function saveNewResponses($node, $newResponses, $resLimit = 60)
    {
        for ($i = 0; $i < $resLimit; $i++) {
            if (isset($newResponses[$i])) {
                if (!isset($node->responses[$i])) {
                    $node->responses[$i] = new SLNodeResponses;
                    $node->responses[$i]->node_res_node = $node->nodeID;
                }
                $node->responses[$i]->node_res_ord       = $i;
                $node->responses[$i]->node_res_eng       = $newResponses[$i]["eng"];
                $node->responses[$i]->node_res_value     = $newResponses[$i]["value"];
                $node->responses[$i]->node_res_show_kids = $newResponses[$i]["kids"];
                $node->responses[$i]->node_res_mut_ex    = $newResponses[$i]["mutEx"];
                $node->responses[$i]->save();
            } elseif (isset($node->responses[$i])) {
                $node->responses[$i]->delete();
            }
        }
        return true;
    }
    
    public function saveSettingInResponse($fldName, $node, $settingInd = 0, $optName = '', $engFldName = '')
    {
        if ($optName == '') {
            $optName = $fldName;
        }
        $hasFld = ($GLOBALS["SL"]->REQ->has($fldName) && trim($GLOBALS["SL"]->REQ->get($fldName)) != '');
        $hasEng = ($GLOBALS["SL"]->REQ->has($engFldName) && trim($GLOBALS["SL"]->REQ->get($engFldName)) != '');
        if ($hasFld || $hasEng) {
            $extraOpt = SLNodeResponses::where('node_res_node', $node->nodeID)
                ->where('node_res_ord', $settingInd)
                ->first();
            if (!$extraOpt || !isset($extraOpt->node_res_node)) {
                $extraOpt = new SLNodeResponses;
                $extraOpt->node_res_node = $node->nodeID;
                $extraOpt->node_res_ord  = $settingInd;
            }
            if ($GLOBALS["SL"]->REQ->has($fldName) && trim($GLOBALS["SL"]->REQ->get($fldName)) != '') {
                $extraOpt->node_res_value = $GLOBALS["SL"]->REQ->get($fldName);
            }
            if (trim($engFldName) != '' 
                && $GLOBALS["SL"]->REQ->has($engFldName) 
                && trim($GLOBALS["SL"]->REQ->get($engFldName)) != '') {
                $extraOpt->node_res_eng = $GLOBALS["SL"]->REQ->get($engFldName);
            }
            $extraOpt->save();
        } elseif (isset($node->extraOpts[$optName]) && $node->extraOpts[$optName] !== false) {
            SLNodeResponses::where('node_res_node', $node->nodeID)
                ->where('node_res_ord', $settingInd)
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
                    SLConditionsNodes::where('cond_node_cond_id', $ab[0])
                        ->where('cond_node_node_id', $nodeID)
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
                $newCond->cond_node_cond_id = $condID;
                $newCond->cond_node_node_id = $nodeID;
                if ($which == 'B') {
                    $newCond->cond_node_loop_id = -1;
                }
                $newCond->save();
            }
        }
    }
    
    protected function addTestAB(Request $request)
    {
        if ($request->has('addTestABdesc') && trim($request->get('addTestABdesc')) != '') {
            $cond = new SLConditions;
            $cond->cond_database = $this->dbID;
            $cond->cond_operator = 'AB TEST';
            $cond->cond_tag      = '%AB Tree' . $this->treeID;
            $cond->cond_desc     = trim($request->get('addTestABdesc'));
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
        if ($treeRow->tree_type == 'Page') {
            /* // auto-setting 
            $skipConds = [];
            $testCond = SLConditions::where('cond_tag', '#TestLink')
                ->where('cond_database', $treeRow->tree_database)
                ->first();
            if (!$testCond || !isset($testCond->cond_id)) {
                $testCond = SLConditions::where('cond_tag', '#TestLink')
                    ->first();
            }
            if ($testCond && isset($testCond->cond_id)) $skipConds[] = $testCond->cond_id;
            $chk = DB::select( DB::raw( "SELECT c.`cond_node_cond_id`, n.`node_id` FROM `SL_ConditionsNodes` c 
                LEFT OUTER JOIN `SL_Node` n ON c.`cond_node_node_id` LIKE n.`node_id` 
                WHERE n.`node_tree` LIKE '" . $treeID . "'" . ((sizeof($skipConds) > 0) 
                    ? " AND c.`cond_node_cond_id` NOT IN ('" . implode("', '", $skipConds) . "')" : "") ) );
            if ($chk && sizeof($chk)) {
                if ($treeRow->tree_opts%29 > 0) $treeRow->tree_opts *= 29;
            } else {
                if ($treeRow->tree_opts%29 == 0) $treeRow->tree_opts = $treeRow->tree_opts/29;
            }
            $treeRow->save();
            */
        }
        return true;
    }
    
}
