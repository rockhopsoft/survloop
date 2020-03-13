<?php
/**
  * TreeSurvForm is the main class for SurvLoop's branching tree, capable of generating complex forms.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace SurvLoop\Controllers\Tree;

use SurvLoop\Controllers\Tree\TreeSurvDataPrint;

class TreeSurvForm extends TreeSurvDataPrint
{
    protected function customNodePrintWrap($nID, $bladeRender = '')
    {
        return $this->printNodePublicFormStart($nID) . $bladeRender 
            . $this->nodePrintButton($nID) . $this->printNodePublicFormEnd($nID)
            . '<div class="fC p20"></div>';
    }
    
    protected function customNodePrint($nID = -3, $tmpSubTier = [], $nIDtxt = '', $nSffx = '', $currVisib = 1)
    {
        return '';
    }
    
    protected function closePrintNodePublic($nID, $nIDtxt, $curr)
    {
        return true;
    }
    
    protected function customStartPrintNodePublic($nID = -3, $tmpSubTier = [], $currVisib = -1)
    {
        return false;
    }
    
    protected function printNodePublic($nID = -3, $tmpSubTier = [], $currVisib = -1)
    {
        $this->customStartPrintNodePublic($nID, $tmpSubTier, $currVisib);
        if (!isset($this->allNodes[$nID]) || !$this->checkNodeConditions($nID)) {
            return '';
        }
        if ($this->allNodes[$nID]->nodeType == 'Send Email') {
            return $this->postNodeSendEmail($nID);
        }
        
        if (empty($tmpSubTier)) {
            $tmpSubTier = $this->loadNodeSubTier($nID);
        }
        if (!isset($this->v["hasFixedHeader"])) {
            $this->v["hasFixedHeader"] = false;
        }
        $ret = '';
        
        // copy node object; load field info and current session data
        $this->allNodes[$nID]->fillNodeRow($nID);
        $curr = $this->allNodes[$nID];
        list($tbl, $fld) = $curr->getTblFld();
        $this->v["fldForeignTbl"] = $GLOBALS["SL"]->fldForeignKeyTbl($tbl, $fld);
        if (($curr->isPage() || $curr->isInstruct()) 
            && isset($GLOBALS["SL"]->closestLoop["obj"]->data_loop_table)) {
            $tbl = $GLOBALS["SL"]->closestLoop["obj"]->data_loop_table;
        }
        if ($tbl == '' && $this->hasCycleAncestorActive($nID)) {
            $tbl = $GLOBALS["SL"]->currCyc["cyc"][0];
        }
        if ($tbl == '' && $this->hasSpreadsheetParentActive($nID)) {
            $tbl = $GLOBALS["SL"]->currCyc["tbl"][0];
        }

        if ($curr->isPage()) {
            $extraOpts = $this->swapIDsSEO($curr->extraOpts);
            $GLOBALS['SL']->setSEO(
                $this->swapLabels($nID . '', $extraOpts["meta-title"]), 
                $this->swapLabels($nID . '', $extraOpts["meta-desc"]), 
                $this->swapLabels($nID . '', $extraOpts["meta-keywords"]), 
                $this->swapLabels($nID . '', $extraOpts["meta-img"])
            );
        }
        
        // if ($currVisib == 1 && $curr->nodeType == 'Data Manip: New') $this->runDataManip($nID);
        if ($curr->isDataManip()) {
            $this->loadManipBranch($nID, ($currVisib == 1));
        }
        $hasParManip = $this->hasParentDataManip($nID);
        
        $nSffx = $GLOBALS["SL"]->getCycSffx();
        $nIDtxt = trim($nID . $nSffx);

        if ($curr->isPage() || $curr->isLoopRoot()) {

            $ret = $this->customNodePrint($nID, $tmpSubTier, $nIDtxt, $nSffx);
            if (!is_array($ret) && trim($ret) != '') {
                return $ret;
            }
            $this->checkLoopRootInput($nID);
            // print the button, and form initialization 
            // which only happens once per page make sure 
            // these are reset, in case of redirect
            $this->pageJSvalid = $this->pageHasReqs = '';
            $this->pageHasUpload = $this->pageFldList = $this->hideKidNodes = [];
            $this->runPageExtra($nID);
            $this->runPageLoad($nID);
            if ($GLOBALS["SL"]->treeRow->tree_type != 'Page') {
                $ret .= '<div id="pageTopGapID" class="pageTopGap"></div>';
            }
            if ($curr->isLoopRoot()) {
                if (isset($curr->nodeRow->node_prompt_text) 
                    && trim($curr->nodeRow->node_prompt_text) != '') {
                    $ret .= '<div id="loopRootPromptText' . $nID 
                        . '" class="nPrompt loopRootPromptText">' 
                        . stripslashes($curr->nodeRow->node_prompt_text) . '</div>';
                }
                $ret .= $this->printSetLoopNav($nID, $curr->defaultVal);
            } else { // isPage()
                if (sizeof($tmpSubTier[1]) > 0) { // recurse deez!..
                    foreach ($tmpSubTier[1] as $cNode) { 
                        if (!$this->allNodes[$cNode[0]]->isPage()) {
                            $ret .= $this->printNodePublic($cNode[0], $cNode, $currVisib);
                        }
                    } 
                }
            }
            if (intVal($curr->nodeRow->node_char_limit) > 0) {
                $GLOBALS["SL"]->pageJAVA .= 'setTimeout("focusNodeID(' 
                    . $curr->nodeRow->node_char_limit . ').focus()", 100);' . "\n";
            } elseif (trim($this->page1stVisib) != '' 
                && intVal($curr->nodeRow->node_char_limit) == 0) {
                $GLOBALS["SL"]->pageJAVA .= 'setTimeout("'
                    . 'if (document.getElementById(\'' . $this->page1stVisib 
                    . '\')) document.getElementById(\'' . $this->page1stVisib 
                    . '\').focus()", 100);' . "\n";
            }
            $gap = '';
            if ($GLOBALS["SL"]->treeRow->tree_type != 'Page') {
                $gap = '<div class="pageBotGap"></div>';
            }
            return $this->printNodePublicFormStart($nID) . $ret 
                . '<div id="pageBtns"><div id="formErrorMsg"></div>' 
                . $this->nodePrintButton($nID, $tmpSubTier, '' /* $promptNotesSpecial */) 
                . '</div>' . $this->printNodePublicFormEnd($nID, '' /* $promptNotesSpecial */)
                . $gap;

        } // else not Page or Loop Root
        
        $itemInd = $itemID = -3;
        if ($this->hasActiveParentCyc($nID, $tbl)) {
            list($itemInd, $itemID) = $this->chkParentCycInds($nID, $tbl);
        } else { // default logic, not LoopCycle, not SpreadTable 
            list($itemInd, $itemID) = $this->sessData->currSessDataPos($tbl, $hasParManip);
            if ($itemInd < 0 
                && isset($GLOBALS["SL"]->closestLoop["loop"]) 
                && trim($GLOBALS["SL"]->closestLoop["loop"]) != '' 
                && $tbl == $this->sessData->isCheckboxHelperTable($tbl)) {
                // In this context, relevant item index is item's 
                // index with the loop, not table's whole data set
                $itemInd = $this->sessData->getLoopIndFromID(
                    $GLOBALS["SL"]->closestLoop["loop"], 
                    $itemID
                );
            }
        }
        $currNodeSessData = $this->sessData
            ->currSessData($nID, $tbl, $fld, 'get', '', $hasParManip, $itemInd, $itemID);
        //if ($itemID <= 0) $currNodeSessData = ''; // override false profit ;-P
        if ($currNodeSessData == '' && trim($curr->nodeRow->node_default) != '') {
            $currNodeSessData = $curr->nodeRow->node_default;
        }
        
        // check for extra custom PHP code stored with the node; check for standardized techniques
        $nodeOverrides = $this->printNodeSessDataOverride(
            $nID, 
            $tmpSubTier, 
            $nIDtxt, 
            $currNodeSessData
        );
        if (is_array($nodeOverrides) && sizeof($nodeOverrides) > 1) {
            $currNodeSessData = $nodeOverrides;
        } elseif (is_array($nodeOverrides) 
            && sizeof($nodeOverrides) == 1 
            && isset($nodeOverrides[0])) {
            $currNodeSessData = $nodeOverrides[0];
        }
        if ($GLOBALS["SL"]->REQ->has('nv' . $nIDtxt) 
            && trim($GLOBALS["SL"]->REQ->get('nv' . $nIDtxt)) != '') {
            $currNodeSessData = $GLOBALS["SL"]->REQ->get('nv' . $nIDtxt);
        }
        $this->v["currNodeSessData"] = $currNodeSessData; // should migrate in this direction
        
        if (!isset($this->v["javaNodes"])) {
            $this->v["javaNodes"] = '';
        }
        $this->v["javaNodes"] .= 'nodeParents[' . $nID . '] = ' . $curr->parentID . ';' . "\n";
        if ($nSffx != '') {
            $this->v["javaNodes"] .= 'nodeSffxs[nodeSffxs.length] = "' . $nSffx . '";' . "\n";
        }
        $condKids = $showMoreNodes = [];
        if (sizeof($tmpSubTier[1]) > 0) {
            if ($curr->nodeType == 'Countries') {
                $nxtNode = $this->nextNode($nID);
                if ($nxtNode > 0 && isset($this->allNodes[$nxtNode])) {
                    if ($this->allNodes[$nxtNode]->nodeType == 'U.S. States') {
                        $curr->hasShowKids = true;
                        $GLOBALS["SL"]->loadStates();
                        $curr->responses = $GLOBALS["SL"]->states
                            ->getCountryResponses($nID, ['United States']);
                    }
                }
            }
            if ($curr->hasShowKids && sizeof($curr->responses) > 0) {
                // displaying children on page is conditional
                foreach ($curr->responses as $j => $res) {
                    if (intVal($res->node_res_show_kids) > 0) {
                        if (!isset($condKids[$res->node_res_show_kids])) {
                            $condKids[$res->node_res_show_kids] = [];
                        }
                        $condKids[$res->node_res_show_kids][] = $res->node_res_value;
                    }
                }
                if (sizeof($condKids) > 0) {
                    foreach ($condKids as $condNode => $condVals) {
                        $condHide = true;
                        foreach ($condVals as $cVal) {
                            if ($this->isCurrDataSelected(
                                $currNodeSessData, 
                                $cVal, 
                                $curr)) {
                                $condHide = false;
                            }
                        }
                        if ($condHide) {
                            $this->hideKidNodes[] = $condNode;
                        }
                    }
                }
                $this->v["javaNodes"] .= 'conditionNodes[' . $nID . '] = true;' . "\n";
                $typeList = [
                    'Page Block', 'Data Manip: New', 'Data Manip: Update', 
                    'Data Manip: Wrap', 'Instructions', 'Instructions Raw', 
                    'Layout Row', 'Gallery Slider'
                ];
                $childList = [];
                foreach ($tmpSubTier[1] as $childNode) {
                    $childList[] = $childNode[0];
                    if (isset($this->kidMaps[$nID]) 
                        && sizeof($this->kidMaps[$nID]) > 0) {
                        foreach ($this->kidMaps[$nID] as $nKid => $ress) {
                            if ($nKid == $childNode[0] 
                                && sizeof($childNode[1]) > 0
                                && in_array($this->allNodes[$nKid]->nodeType, $typeList)) {
                                foreach ($childNode[1] as $grandNode) {
                                    if (!isset($showMoreNodes[$childNode[0]])) {
                                        $showMoreNodes[$childNode[0]] = []; 
                                    }
                                    $showMoreNodes[$childNode[0]][] = $grandNode[0];
                                    if ($this->allNodes[$nKid]->nodeType == 'Layout Row' 
                                        && sizeof($grandNode[1]) > 0) {
                                        foreach ($grandNode[1] as $gGrandNode) {
                                            if ($this->allNodes[$gGrandNode[0]]->nodeType 
                                                == 'Layout Column' 
                                                && sizeof($gGrandNode[1]) > 0) {
                                                $showMoreNodes[$childNode[0]][] = $gGrandNode[0];
                                                foreach ($gGrandNode[1] as $ggGrandNode) {
                                                    $showMoreNodes[$childNode[0]][] = $ggGrandNode[0];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $this->v["javaNodes"] .= 'nodeKidList[' . $nID . '] = ['
                    . implode(', ', $childList).'];' . "\n";
            }
        }
        if (sizeof($curr->responses) == 3 && $curr->responses[1]->node_res_value == '...') {
            $start = intVal($curr->responses[0]->node_res_value);
            $finish = intVal($curr->responses[2]->node_res_value);
            $curr->responses = [];
            if ($start < $finish) {
                for ($i=$start; $i<=$finish; $i++) {
                    $curr->addTmpResponse($i);
                }
            } else {
                for ($i=$start; $i>=$finish; $i--) {
                    $curr->addTmpResponse($i);
                }
            }
            $curr->chkFldOther();
        }
        
        if ($currVisib < 0) {
            $currVisib = ((in_array($nID, $this->hideKidNodes)) ? 0 : 1);
        } elseif ($currVisib == 1 && in_array($nID, $this->hideKidNodes)) {
            $currVisib = 0;
        }
        $visibilityField = '<input type="hidden" name="n' 
            . $nIDtxt . 'Visible" id="n' . $nIDtxt 
            . 'VisibleID" value="' . $currVisib . '">';
        if ($curr->nodeType == 'Layout Column') {
            $visibilityField = '';
        }
        if ($this->page1stVisib == '' && $currVisib == 1) {
            if (in_array($curr->nodeType, 
                ['Radio', 'Checkbox', 'Gender', 'Gender Not Sure'])) {
                $this->page1stVisib = 'n' . $nID . 'fld0';
            } elseif (in_array($curr->nodeType, ['Date', 'Date Time'])) {
                $this->page1stVisib = 'n' . $nID . 'fldMonthID';
            } elseif (in_array($curr->nodeType, ['Time'])) {
                $this->page1stVisib = 'n' . $nID . 'fldHrID';
            } elseif (in_array($curr->nodeType, ['Feet Inches'])) {
                $this->page1stVisib = 'n' . $nID . 'fldFeetID';
            } elseif (in_array($curr->nodeType, ['Drop Down', 'Text', 
                'Long Text', 'Text:Number', 'Slider', 'Email', 
                'Password', 'U.S. States', 'Countries'])) {
                $this->page1stVisib = 'n' . $nID . 'FldID';
            }
        }
        
        if ($curr->isRequired()) {
            $this->pageHasReqs++;
        }
        
        $ret = $this->customNodePrint($nID, $tmpSubTier, $nIDtxt, $nSffx, $currVisib);
        if ($curr->nodeType == 'Data Print Row' 
            && is_array($ret) 
            && sizeof($ret) > 0) {
            return $ret; 
        } elseif (!is_array($ret) && $ret != '') {
            return $visibilityField . $this->wrapNodePrint($ret, $nID);
        }
        $ret = $visibilityField . $this->nodeSessDump($nIDtxt, $nID);
        // else print standard node output...
        
        $xtraClass = ' slTab slNodeChange';
        if ($curr->nodeType != 'Long Text') {
            $xtraClass .= ' ntrStp';
        }
        if (isset($this->v["graphFilters"]) 
            && intVal($this->v["graphFilters"]) > 0 
            && trim($curr->dataStore) != '') {
            $typeList = [ 'Drop Down', 'U.S. States', 'Countries' ];
            $xtraClass .= ' graphUp' 
                . ((in_array($curr->nodeType, $typeList)) ? 'Drp' : '');
            $GLOBALS["SL"]->pageAJAX .= 'addGraphFld("' . $nIDtxt . '", ' 
                . $GLOBALS["SL"]->getFldIDFromFullWritName($curr->dataStore) . ', ' 
                . $this->v["graphFilters"] . ');' . "\n";
        }
        
        $mobileCheckbox = ($curr->nodeRow->node_opts%2 > 0);
        //if (in_array($curr->nodeType, ['Radio', 'Checkbox']) 
        //    && sizeof($curr->responses) > 0 
        //    && $mobileCheckbox) {
            $GLOBALS["SL"]->pageJAVA .= 'addIsMobile(' . $nID . ', true); ';
        //} else {
        //    $GLOBALS["SL"]->pageJAVA .= 'addIsMobile(' . $nID . ', false); ';
        //}
        
        // check for extra custom HTML/JS/CSS code stored with the node; check for standardized techniques
        $nodePromptAfter = '';
        $onKeyUp = '';
        if (trim($curr->nodeRow->node_prompt_after) != '' && !$curr->isWidget()) {
            if (stripos($curr->nodeRow->node_prompt_after, '/'.'* formAJAX *'.'/') !== false) {
                $GLOBALS["SL"]->pageAJAX .= $curr->nodeRow->node_prompt_after;
            } else {
                if (!$curr->isPage()) {
                    $pos = strpos($curr->nodeRow->node_prompt_after, 'function fldOnKeyUp[[nID]](');
                    if ($pos !== false) {
                        $onKeyUp .= ' fldOnKeyUp' . $nIDtxt . '(); ';
                    }
                    $nodePromptAfter = $this->swapLabels(
                        $nIDtxt, 
                        $curr->nodeRow->node_prompt_after, 
                        $itemID, 
                        $itemInd
                    );
                    $nodePromptAfter = $GLOBALS["SL"]->extractJava($nodePromptAfter, $nID);
                }
            }
        }
        $charLimit = '';
        /* if (intVal($curr->nodeRow->node_char_limit) > 0 && $curr->nodeRow->node_opts%31 > 0 
            && $curr->nodeType != 'Uploads') {
            $onKeyUp .= ' charLimit(\'' . $nIDtxt . '\', ' . $curr->nodeRow->node_char_limit . '); ';
            $charLimit = "\n" . '<div id="charLimit' . $nID . 'Msg" class="txtDanger f12 opac33"></div>';
            $GLOBALS["SL"]->pageJAVA .= 'setTimeout("charLimit(\'' . $nIDtxt . '\', ' 
                . $curr->nodeRow->node_char_limit . ')", 50);' . "\n";
        } */
        if ($curr->nodeRow->node_opts%31 == 0 || $curr->nodeRow->node_opts%47 == 0) {
            if (intVal($curr->nodeRow->node_char_limit) == 0) {
                $curr->nodeRow->node_char_limit = 10000000000;
            }
            $wrdCntKey = 'wordCountKeyUp(\'' . $nIDtxt . '\', ' 
                . (($curr->nodeRow->node_opts%47 == 0) ? $curr->nodeRow->node_char_limit : 0) 
                . ', ' . intVal($curr->nodeRow->node_char_limit) . ')';
            $onKeyUp .= ' ' . $wrdCntKey . '; ';
            $GLOBALS["SL"]->pageJAVA .= 'setTimeout("' . $wrdCntKey . '", 50);' . "\n";
        }
        if (isset($curr->extraOpts["minVal"]) && $curr->extraOpts["minVal"] !== false) {
            $onKeyUp .= ' checkMin(\'' . $nIDtxt . '\', ' . $curr->extraOpts["minVal"] . '); ';
        }
        if (isset($curr->extraOpts["maxVal"]) && $curr->extraOpts["maxVal"] !== false) {
            $onKeyUp .= ' checkMax(\'' . $nIDtxt . '\', ' . $curr->extraOpts["maxVal"] . '); ';
        }
        //if ($curr->nodeType == 'Long Text') $onKeyUp .= ' flexAreaAdjust(this); ';
        if (trim($onKeyUp) != '') {
            $onKeyUp = ' onKeyUp="' . $onKeyUp . '" ';
        }
        
        // check notes settings for any standardized techniques
        $promptNotesSpecial = '';
        if ($this->isPromptNotesSpecial($curr->nodeRow->node_prompt_notes)) {
            $promptNotesSpecial = $curr->nodeRow->node_prompt_notes;
            $curr->nodeRow->node_prompt_notes = '';
        }
        
        // write basic node field labeling
        $nodePromptText  = stripslashes($this->swapLabels(
            $nIDtxt, 
            $curr->nodeRow->node_prompt_text, 
            $itemID, 
            $itemInd
        ));
        if ($curr->isRequired() && $curr->nodeType != 'Hidden Field') {
            $nodePromptText = $this->addPromptTextRequired($curr, $nodePromptText, $nIDtxt);
        }

        $nodePromptNotes = $curr->nodeRow->node_prompt_notes;
        $nodePromptNotes = $this->swapLabels($nIDtxt, $nodePromptNotes, $itemID, $itemInd);
        $nodePromptNotes = stripslashes($nodePromptNotes);
        if (trim($nodePromptNotes) != '' && !$curr->isLoopRoot()) {
            if ($curr->nodeRow->node_opts%83 == 0) {
                $nodePromptText = '<a id="hidivBtnnLabel' . $nIDtxt . 'notes" '
                    . 'class="hidivBtn crsrPntr float-right">'
                    . '<i class="fa fa-info-circle" aria-hidden="true"></i></a>' . $nodePromptText;
            }
            $nodePromptText .= '<div id="hidivnLabel' . $nIDtxt . 'notes" class="subNote'
                . (($curr->nodeRow->node_opts%83 == 0) ? ' disNon' : '') . '">' 
                . $nodePromptNotes . '</div>' . "\n";
        }
        if (strpos($nodePromptText, 'fixedHeader') !== false) {
            $this->v["hasFixedHeader"] = true;
        }
        
        $nodePromptText  = $GLOBALS["SL"]->extractJava($nodePromptText, $nID);
        $nodePromptNotes = $GLOBALS["SL"]->extractJava($nodePromptNotes, $nID);
        
        $nodePrompt = '';
        if (strpos($curr->nodeRow->node_prompt_text, '[[PreviewPrivate]]') !== false 
            || strpos($curr->nodeRow->node_prompt_text, '[[PreviewPublic]]') !== false) {
            $nodePrompt = $nodePromptText;
        } elseif (trim($nodePromptText) != '' 
            && !$curr->isDataPrint() 
            && !$this->hasSpreadsheetParent($nID)) {
            if ($curr->isInstructAny()) {
                $nodePrompt = '<div id="nLabel' . $nIDtxt . '" class="nPrompt">' 
                    . $nodePromptText . '</div>' . "\n";
            } else {
                $w100 = '';
                if (!in_array($curr->nodeType, ['Radio', 'Checkbox']) 
                    || $curr->nodeRow->node_opts%83 == 0) {
                    $w100 = ' class="w100"';
                }
                $nodePrompt = "\n" . '<div id="nLabel' . $nIDtxt . '" class="nPrompt">'
                    . '<label for="n' . $nIDtxt . 'FldID"' . $w100 . ' >'
                    . $nodePromptText . '</label></div>' . "\n";
            }
        }
        
        if ($curr->isDataPrint()) {
            
            return $this->nodePrintData(
                $nID, 
                $nIDtxt, 
                $curr, 
                $tbl, 
                $fld, 
                $currVisib, 
                $tmpSubTier, 
                $nodePromptText,
                $currNodeSessData, 
                $itemID, 
                $itemInd
            );
            
        } else { // not data printing...
        
            if ($curr->nodeType == 'Layout Column') {

                $cols = $curr->nodeRow->node_char_limit;
                $ret .= '<div id="col' . $nIDtxt . '" class="col-lg-' . $cols . '">';

            } else {

                if ($this->hasParentType($nID, 'Gallery Slider') 
                    && isset($curr->colors["blockImg"]) 
                    && trim($curr->colors["blockImg"]) != '') {
                    $GLOBALS["SL"]->addPreloadImg($curr->colors["blockImg"]);
                }
                $isParallax = (isset($curr->colors["blockImgFix"]) 
                    && trim($curr->colors["blockImgFix"]) == 'P');
                $dis = '';
                if ($this->hasParentType($nID, 'Gallery Slider')) {
                    if ($curr->nodeRow->node_parent_order == 0) {
                        $dis = ' disBlo';
                    } else {
                        $dis = ' disNon';
                    }
                }
                $ret .= view(
                    'vendor.survloop.css.inc-block', [
                        "nIDtxt" => $nIDtxt,
                        "node"   => $curr
                    ]
                )->render();
                $ret .= '<div id="blockWrap' . $nIDtxt . '" class="w100' . $dis;
                if ($isParallax) {
                    $imgSrc = '';
                    if (sizeof($curr->colors) > 0) {
                        if (isset($curr->colors["blockImg"]) 
                            && trim($curr->colors["blockImg"]) != '') {
                            $GLOBALS["SL"]->pageAJAX .= "$('#blockWrap" 
                                . $nIDtxt . "').parallax({imageSrc: '" 
                                . $curr->colors["blockImg"] . "'}); ";
                            $ret .= ' parallax-window';
                            //$ret .= ' parallax-window" data-parallax="scroll" data-image-src="' 
                            //    . $curr->colors["blockImg"];
                        }
                    }
                }
                $ret .= '">';

            }

            if ($GLOBALS["SL"]->treeRow->tree_type == 'Page' 
                && $curr->parentID == $GLOBALS['SL']->treeRow->tree_root) {
                if ($curr->isPageBlockSkinny()) { // wrap page block
                    $ret .= '<center><div class="treeWrapForm" id="treeWrap' 
                        . $nIDtxt . '">'; //  class="container"
                } else{
                    if ($this->hasFrameLoad() 
                        || ($GLOBALS["SL"]->treeRow->tree_opts%3 == 0 
                        || $GLOBALS["SL"]->treeRow->tree_opts%17 == 0 
                        || $GLOBALS["SL"]->treeRow->tree_opts%41 == 0 
                        || $GLOBALS["SL"]->treeRow->tree_opts%43 == 0)) {
                        $ret .= '<div class="w100 pL15 pR15" id="treeWrap' . $nIDtxt . '">';
                    } else {
                        $ret .= '<div class="container" id="treeWrap' . $nIDtxt . '">';
                    }
                }
            }
            
            if (!$this->hasSpreadsheetParent($nID)) {
                $ret .= '<div class="fC"></div><div class="nodeAnchor"><a id="n' 
                    . $nIDtxt . '" name="n' . $nIDtxt . '"></a></div>';
            }
            
            // write the start of the main node wrapper
            if (!in_array($curr->nodeType, ['Layout Row', 'Layout Column'])) {
                $ret .= '<div id="node' . $nIDtxt . '" class="nodeWrap' 
                    . (($curr->isGraph()) ? ' nGraph' : '')
                    . (($curr->nodeRow->node_opts%89 == 0) ? ' slCard' : '');
                if ($GLOBALS["SL"]->treeRow->tree_type == 'Page') {
                    $ret .= (($curr->isInstructAny()) ? ' w100' : '') 
                        . (($curr->isPage()) ? ' h100' : '');
                }
                if ($currVisib != 1 
                    && (trim($GLOBALS["SL"]->currCyc["res"][1]) == '' 
                        || substr($GLOBALS["SL"]->currCyc["res"][1], 0, 3) != 'res')) {
                    $ret .= ' disNon';
                }
                $ret .= '">' . "\n";
            }
            
            if ($curr->nodeRow->node_opts%37 == 0) {
                $ret .= '<div class="jumbotron">';
            }
            
            if ($this->shouldPrintHalfGap($curr)) {
                $ret .= '<div class="nodeHalfGap"></div>';
            }
            
            if ($curr->isLayout() || $curr->isBranch()) {
                
            } elseif ($curr->isLoopCycle()) {
                
                list($tbl, $fld, $newVal) = $this->nodeBranchInfo($nID);
                $loop = str_replace('LoopItems::', '', $curr->nodeRow->node_response_set);
                $loopCycle = $this->sessData->getLoopRows($loop);
                if (sizeof($tmpSubTier[1]) > 0 && sizeof($loopCycle) > 0) {
                    $GLOBALS["SL"]->currCyc["cyc"][0] = $GLOBALS["SL"]->getLoopTable($loop);
                    foreach ($loopCycle as $i => $loopItem) {
                        $GLOBALS["SL"]->currCyc["cyc"][1] = 'cyc' . $i;
                        $GLOBALS["SL"]->currCyc["cyc"][2] = $loopItem->getKey();
                        $this->sessData->startTmpDataBranch($tbl, $loopItem->getKey());
                        $GLOBALS["SL"]->fakeSessLoopCycle($loop, $loopItem->getKey());
                        foreach ($tmpSubTier[1] as $childNode) {
                            if (!$this->allNodes[$childNode[0]]->isPage()) {
                                $ret .= $this->printNodePublic(
                                    $childNode[0], 
                                    $childNode, 
                                    $currVisib
                                );
                            }
                        }
                        $GLOBALS["SL"]->removeFakeSessLoopCycle($loop, $loopItem->getKey());
                        $this->sessData->endTmpDataBranch($tbl);
                        $GLOBALS["SL"]->currCyc["cyc"][1] = '';
                        $GLOBALS["SL"]->currCyc["cyc"][2] = -3;
                    }
                    $GLOBALS["SL"]->currCyc["cyc"][0] = '';
                }
                
            } elseif ($curr->isSpreadTbl()) {
                
                if (sizeof($tmpSubTier[1]) > 0) {
                    $this->tableDat = $this->loadTableDat($curr, $currNodeSessData, $tmpSubTier);
                    $GLOBALS["SL"]->currCyc["tbl"][0] = $this->tableDat["tbl"];
                    for ($i = 0; $i < $this->tableDat["maxRow"]; $i++) {
                        if ($i < sizeof($this->tableDat["rows"])) {
                            if (trim($this->tableDat["rowCol"]) != '') {
                                if ($this->tableDat["rows"][$i]["id"] <= 0 
                                    && trim($this->tableDat["rowCol"]) != '') {
                                    $recObj = $this->sessData->checkNewDataRecord(
                                        $this->tableDat["tbl"], 
                                        $this->tableDat["rowCol"], 
                                        $this->tableDat["rows"][$i]["leftVal"]
                                    );
                                    if ($recObj) {
                                        $this->tableDat["rows"][$i]["id"] = $recObj->getKey();
                                    }
                                }
                            }
                            $GLOBALS["SL"]->currCyc["tbl"][1] = 'tbl' . $i;
                            $GLOBALS["SL"]->currCyc["tbl"][2] = $this->tableDat["rows"][$i]["id"];
                            $this->sessData->startTmpDataBranch(
                                $this->tableDat["tbl"], 
                                $this->tableDat["rows"][$i]["id"], 
                                false
                            );
                            foreach ($tmpSubTier[1] as $k => $kidNode) {
                                $printFld = $this->printNodePublic($kidNode[0], $kidNode, 1);
                                $printFld = str_replace('nFld', '', 
                                    str_replace('nFld mT0', '', $printFld));
                                $this->tableDat["rows"][$i]["cols"][] = $printFld;
                            }
                            $this->sessData->endTmpDataBranch($this->tableDat["tbl"]);
                            $GLOBALS["SL"]->currCyc["tbl"][1] = '';
                            $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
                        }
                    }
                    $GLOBALS["SL"]->currCyc["tbl"][1] = 'tbl?';
                    $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
                    $java = '';
                    foreach ($tmpSubTier[1] as $k => $kidNode) {
                        $kid = $this->printNodePublic($kidNode[0], $kidNode, 1);
                        $this->tableDat["blnk"][$k] = str_replace('nFld', '', 
                            str_replace('nFld mT0', '', $kid));
                        $java .= (($k > 0) ? ', ' : '') . $kidNode[0];
                    }
                    $this->v["javaNodes"] .= 'nodeTblList[' . $nID 
                        . '] = new Array(' . $java . '); ';
                    if ($this->tableDat["req"][0]) {
                        $this->pageJSvalid .= "var cols = new Array(";
                        foreach ($tmpSubTier[1] as $k => $kidNode) {
                            $this->pageJSvalid .= (($k > 0) ? ", " : "") 
                                . " new Array(" . $kidNode[0] . ", " 
                                . (($this->tableDat["req"][2][$k]) ? 'true' : 'false') . ") ";
                        }
                        $this->pageJSvalid .= ");\n" . "addReqNodeTbl(" . $nID . ", '" . $nIDtxt 
                            . "', 'reqFormFldTbl', " . $this->tableDat["maxRow"] . ", cols, " 
                            . (($this->tableDat["req"][1]) ? 'true' : 'false') . ");\n";
                    }
                    $ret .= view(
                        'vendor.survloop.forms.formtree-table', 
                        [
                            "nID"             => $nID,
                            "nIDtxt"          => $nIDtxt,
                            "node"            => $curr,
                            "nodePromptText"  => $nodePromptText,
                            "tableDat"        => $this->tableDat
                        ]
                    )->render();
                    $GLOBALS["SL"]->currCyc["tbl"] = [ '', '', -3 ];
                    $this->tableDat = [];
                }
                
            } elseif ($curr->isLoopSort()) {
                
                $loop = str_replace('LoopItems::', '', $curr->nodeRow->node_response_set);
                $loopCycle = $this->sessData->getLoopRows($loop);
                if (sizeof($loopCycle) > 0) {
                    $GLOBALS["SL"]->pageAJAX .= '$("#sortable").sortable({ 
                        axis: "y", update: function (event, ui) {
                        var url = "/sortLoop/?n=' . $nID . '&"+$(this).sortable("serialize")+"";
                        document.getElementById("hidFrameID").src=url;
                    } }); $("#sortable").disableSelection();';
                    $ret .= '<div class="nFld">' . $this->sortableStart($nID) 
                        . '<ul id="sortableN' . $nID . '" class="slSortable">' . "\n";
                    foreach ($loopCycle as $i => $loopItem) {
                        $ret .= '<li id="item-' . $loopItem->getKey() 
                            . '" class="sortOff" onMouseOver="this.className=\'sortOn\';" '
                            . 'onMouseOut="this.className=\'sortOff\';">'
                            . '<span><i class="fa fa-sort slBlueDark"></i></span> ' 
                            . $this->getLoopItemLabel($loop, $loopItem, $i) . '</li>' . "\n";
                    }
                    $ret .= '</ul>' . $this->sortableEnd($nID) . '</div>' . "\n";
                }
                
            } elseif ($curr->isDataManip()) {
                
                $ret .= '<input type="hidden" name="dataManip' . $nIDtxt . '" value="1">';
                if ($currVisib == 1) { // run a thing on page load
                    if ($curr->nodeType == 'Data Manip: Close Sess') {
                        $this->deactivateSess($curr->nodeRow->node_response_set);
                    }
                }
                
            } elseif ($curr->nodeType == 'Back Next Buttons') {
                
                $ret .= view('vendor.survloop.forms.inc-extra-back-next-buttons')->render();
                
            } elseif (in_array($curr->nodeType, ['Search', 'Search Results', 
                'Search Featured', 'Record Full', 'Record Full Public', 'Record Previews', 
                'Incomplete Sess Check', 'Member Profile Basics', 'Plot Graph', 'Line Graph', 
                'Bar Graph', 'Pie Chart', 'Map', 'MFA Dialogue', 'Widget Custom'])) {
                
                $ret .= $this->printWidget($nID, $nIDtxt, $curr);
                
            } elseif (!$curr->isPage()) { // otherwise, the main Node printer...
                
                $this->customResponses($nID, $curr);
                // Start normal data field checks
                $dateStr = $timeStr = '';
                if ($fld != '' 
                    && isset($GLOBALS["SL"]->tblAbbr[$tbl]) 
                    && $fld != ($GLOBALS["SL"]->tblAbbr[$tbl] . 'ID')
                    && !is_array($currNodeSessData) 
                    && trim($currNodeSessData) != '' 
                    && isset($GLOBALS["SL"]->fldTypes[$tbl][$fld])) {
                    // convert current session data for dates and times
                    if ($GLOBALS["SL"]->fldTypes[$tbl][$fld] == 'DATETIME') {
                        list($dateStr, $timeStr) = explode(' ', $currNodeSessData);
                        $dateStr = $this->cleanDateVal($dateStr);
                        if (trim($dateStr) != '') {
                            $dateStr = date("m/d/Y", strtotime($dateStr));
                        }
                    } elseif ($GLOBALS["SL"]->fldTypes[$tbl][$fld] == 'DATE') {
                        $dateStr = $this->cleanDateVal($currNodeSessData);
                        if (trim($dateStr) != '') {
                            $dateStr = date("m/d/Y", strtotime($dateStr));
                        }
                    }
                    if ($dateStr == '12/31/1969') {
                        $dateStr = '';
                    }
                } // end normal data field checks
                
                // check if this field's label and field is to be printed on the same line
                $isOneLiner = $isOneLinerFld = '';
                if (in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
                    if ($curr->isOneLiner()) {
                        $isOneLiner = ' disIn mR20';
                    }
                    if ($curr->isOneLiner() || $curr->isOneLineResponses()) {
                        $isOneLinerFld = ' disIn mR20';
                    }
                } elseif ($curr->isOneLiner()) {
                    $isOneLiner = $isOneLinerFld = ' col-6';
                }
                if (trim($isOneLiner) != '') {
                    $nodePrompt = str_replace(
                        'class="nPrompt"', 
                        'class="nPrompt' . $isOneLiner . '"', 
                        $nodePrompt
                    );
                }
                
                $chk = ['Radio', 'Checkbox', 'Instructions', 'Other/Custom'];
                if (!in_array($curr->nodeType, $chk)) {
                    $this->pageFldList[] = 'n' . $nID . 'FldID';
                }
                
                // start Q&A on same row
                if ($curr->isOneLiner() && !in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
                    $ret .= '<div class="row"> <!-- start one-liner -->';
                }

                // print out each of the various field types
                if ($curr->nodeType == 'Hidden Field') {
                    
                    $ret .= $nodePrompt . '<input type="hidden" name="n' . $nIDtxt 
                        . 'fld" id="n' . $nIDtxt . 'FldID" value="' . $currNodeSessData 
                        . '" class="' . $xtraClass . '">' . "\n"; 
                        
                } elseif ($curr->nodeType == 'Big Button') {
                    
                    $currNodeSessData = '';
                    $btn = '<div class="nFld"><a id="nBtn' . $nIDtxt . '" class="crsrPntr '
                        . (($curr->nodeRow->node_response_set == 'Text') ? '' : 'btn btn-lg btn-' 
                            . (($curr->nodeRow->node_response_set == 'Default') 
                                ? 'secondary' : 'primary') . ' nFldBtn')
                        . (($curr->nodeRow->node_opts%43 == 0) ? '' : ' nFormNext') . '" ' 
                        . ((trim($curr->nodeRow->node_data_store) != '') 
                            ? 'onClick="' . $curr->nodeRow->node_data_store . '"' : '') . ' >' 
                        . $curr->nodeRow->node_default . '</a></div>';
                    $lastDivPos = strrpos($nodePrompt, "</div>\n            </label></div>");
                    if (strpos($nodePrompt, 'jumbotron') > 0 && $lastDivPos > 0) {
                        $ret .= substr($nodePrompt, 0, $lastDivPos) . '<center>' . $btn . '</center>' 
                            . substr($nodePrompt, $lastDivPos) 
                            . '<input type="hidden" name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt 
                            . 'FldID" value="' . $currNodeSessData . '" class="' . $xtraClass 
                            . '" data-nid="' . $nID . '">' . "\n"; 
                    } else {
                        $ret .= $nodePrompt . '<input type="hidden" name="n' . $nIDtxt 
                            . 'fld" id="n' . $nIDtxt . 'FldID" value="' . $currNodeSessData 
                            . '" class="' . $xtraClass . '" data-nid="' . $nID . '">' . $btn . "\n"; 
                    }
                    if ($curr->nodeRow->node_opts%43 == 0) {
                        $this->allNodes[$nID]->hasShowKids = $curr->hasShowKids = true;
                        $childList = [];
                        if (sizeof($tmpSubTier[1]) > 0) {
                            foreach ($tmpSubTier[1] as $childNode) {
                                $childList[] = $childNode[0];
                                $this->hideKidNodes[] = $childNode[0];
                            }
                        }
                        $this->v["javaNodes"] .= 'nodeKidList[' . $nID . '] = [' 
                            . implode(', ', $childList) . ']; ';
                        $GLOBALS["SL"]->pageAJAX .= view(
                            'vendor.survloop.forms.formtree-button-show-kids-ajax', 
                            [
                                "nIDtxt" => $nIDtxt,
                                "nSffx"  => $nSffx
                            ]
                        )->render();
                    }
                    
                } elseif ($curr->nodeType == 'User Sign Up') {
                    
                    $ret .= $GLOBALS["SL"]->spinner() . '<script type="text/javascript">'
                        . 'setTimeout("window.location=\'/register?nd=' . $nID . '\'", 100); </script><style>'
                        . ' #pageBtns, #navDesktop, #navMobile, #sessMgmtWrap { display: none; } </style>';
                    
                } elseif (in_array($curr->nodeType, [ 'Text', 'Email', 'Spambot Honey Pot' ])) {
                    
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '">'
                        . '<input class="form-control form-control-lg' . $xtraClass . '" type="' 
                        . (($curr->nodeType == 'Email') ? 'email' : 'text') . '" name="n' . $nIDtxt 
                        . 'fld" id="n' . $nIDtxt . 'FldID" value="' . $currNodeSessData . '" ' 
                        . $onKeyUp . ' data-nid="' . $nID . '" ' . $GLOBALS["SL"]->tabInd() . '></div>'
                        . $charLimit . "\n" . $this->printWordCntStuff($nIDtxt, $curr->nodeRow);
                    if ($curr->isRequired()) {
                        if ($curr->nodeType == 'Email') {
                            $this->pageJSvalid .= "addReqNode('" . $nIDtxt . "', 'reqFormFldEmail');\n";
                        } else {
                            $this->pageJSvalid .= "addReqNode('" . $nIDtxt . "', 'reqFormFld');\n";
                        }
                    }
                    if ($curr->nodeType == 'Spambot Honey Pot') {
                        $GLOBALS["SL"]->pageJAVA .= 'document.getElementById("node"+"' . $nID 
                            . '").style.display="none"; ';
                    }
                    if (trim($curr->nodeRow->node_text_suggest) != '') {
                        $GLOBALS["SL"]->pageAJAX .= '$( "#n' . $nIDtxt . 'FldID" ).autocomplete({ source: [';
                        foreach ($GLOBALS["SL"]->def->getSet($curr->nodeRow->node_text_suggest) as $i => $def) {
                            $GLOBALS["SL"]->pageAJAX .= (($i > 0) ? ',' : '') . ' ' . json_encode($def->def_value);
                        }
                        $GLOBALS["SL"]->pageAJAX .= ' ] });' . "\n";
                    }
                    
                } elseif ($curr->nodeType == 'Long Text') {
                    
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '">'
                        . '<textarea class="form-control form-control-lg flexarea' . $xtraClass 
                        . '" name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt . 'FldID" ' . $onKeyUp 
                        . ' data-nid="' . $nID . '" ' . $GLOBALS["SL"]->tabInd() . '>' 
                        . $currNodeSessData . '</textarea></div>' . $charLimit . "\n" 
                        . $this->printWordCntStuff($nIDtxt, $curr->nodeRow);
                    //$GLOBALS["SL"]->pageJAVA .= "\n" . 'setTimeout("flexAreaAdjust(document.getElementById(\'n' 
                    //    . $nIDtxt . 'FldID\'))", 50);';
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "addReqNode('" . $nIDtxt 
                            . "', 'reqFormFld');\n";
                    }
                    
                } elseif (in_array($curr->nodeType, [ 'Text:Number', 'Slider' ])) {
                    
                    $attrMin = $attrMax = '';
                    if (isset($curr->extraOpts["minVal"]) && $curr->extraOpts["minVal"] !== false) {
                        if (isset($curr->nodeRow->node_default) 
                            && $curr->nodeRow->node_default < $curr->extraOpts["minVal"]) {
                            $attrMin = 'min="' . $curr->nodeRow->node_default . '" ';
                        } else {
                            $attrMin = 'min="' . $curr->extraOpts["minVal"] . '" ';
                        }
                    }
                    if (isset($curr->extraOpts["maxVal"]) && $curr->extraOpts["maxVal"] !== false) {
                        if (isset($curr->nodeRow->node_default) 
                            && $curr->nodeRow->node_default > $curr->extraOpts["maxVal"]) {
                            $attrMax = 'max="' . $curr->nodeRow->node_default . '" ';
                        } else {
                            $attrMax = 'max="' . $curr->extraOpts["maxVal"] . '" ';
                        }
                    }
                    $attrIncr = 'step="any" ';
                    if (isset($curr->extraOpts["incr"]) && $curr->extraOpts["incr"] > 0) {
                        $attrIncr = 'step="' . $curr->extraOpts["incr"] . '" ';
                    }
                    $ret .= $nodePrompt;
                    if ($curr->chkCurrOpt('MONTHCALC')) {
                        $presel = $this->monthlyCalcPreselections($nID, $nIDtxt);
                        $ret .= $this->printMonthlyCalculator($nIDtxt, $presel);
                    }
                    $ret .= '<div class="nFld' . $isOneLinerFld . '">';
                    if (!$this->hasSpreadsheetParent($nID)) {
                        $ret .= '<div class="row"><div class="col-sm-3 col-4">';
                    }
                    $unitCls = 'w100 ';
                    if (isset($curr->extraOpts["unit"]) 
                        && trim($curr->extraOpts["unit"]) != '') {
                        $unitCls = 'unitFld ';
                    }
                    $ret .= '<nobr><input type="number" data-nid="' . $nID 
                        . '" class="form-control form-control-lg ' 
                        . (($curr->nodeType == 'Slider') ? 'slidePercFld ' : $unitCls)
                        . $xtraClass . '" name="n' . $nIDtxt . 'fld" id="n' 
                        . $nIDtxt . 'FldID" value="' . $currNodeSessData . '" '
                        . $onKeyUp . ' ' . $attrIncr . $attrMin . $attrMax
                        . $GLOBALS["SL"]->tabInd() . '> ';
                    if (isset($curr->extraOpts["unit"]) 
                        && trim($curr->extraOpts["unit"]) != '') {
                        if ($curr->nodeType == 'Text:Number' 
                            && !$this->hasSpreadsheetParent($nID)) {
                            $ret .= '</nobr></div><div class="col-sm-9 col-4 pT10"><nobr>';
                        }
                        $ret .= $curr->extraOpts["unit"];
                    }
                    $ret .= '</nobr>';
                    if (!$this->hasSpreadsheetParent($nID)) {
                        $ret .= '</div></div>';
                    }
                    if ($curr->nodeType == 'Slider') {
                        $ret .= '<div class="col-10 slideCol"><div id="n' . $nIDtxt . 'slider" '
                            . 'class="ui-slider ui-slider-horizontal slSlider"></div></div>';
                    }
                    $ret .= '</div>' . "\n";
                    if ($curr->nodeType == 'Slider') {
                        $GLOBALS["SL"]->pageAJAX .= '$("#n' . $nIDtxt . 'slider").slider({ '
                            . ((isset($curr->extraOpts["incr"]) 
                                && intVal($curr->extraOpts["incr"]) > 0) 
                                ? 'step: ' . $curr->extraOpts["incr"] . ', ' : '') 
                            . 'change: function( event, ui ) {
                            var newVal = $("#n' . $nIDtxt . 'slider").slider("value");
                            document.getElementById("n' . $nIDtxt . 'FldID").value=newVal;
                        } });
                        $(document).on("keyup", "#n' . $nIDtxt . 'FldID", function() { $("#n' 
                            . $nIDtxt . 'slider").slider("value", document.getElementById("n' 
                            . $nIDtxt . 'FldID").value); }); 
                        setTimeout(function() { $("#n' . $nIDtxt 
                            . 'slider").slider("value", document.getElementById("n' 
                            . $nIDtxt . 'FldID").value); }, 5); ';
                    }
                    if ($curr->isRequired()) {
                        if ((!isset($curr->extraOpts["minVal"]) 
                                || $curr->extraOpts["minVal"] === false)
                            && (!isset($curr->extraOpts["maxVal"]) 
                                || $curr->extraOpts["maxVal"] === false)) {
                            $this->pageJSvalid .= "addReqNode('" 
                                . $nIDtxt . "', 'reqFormFld');\n";
                        } else {
                            if (isset($curr->extraOpts["minVal"]) 
                                && $curr->extraOpts["minVal"] !== false) {
                                $this->pageJSvalid .= "addReqNodeRadio('" . $nIDtxt 
                                    . "', 'reqFormFldGreater', " 
                                    . $curr->extraOpts["minVal"] . ");\n";
                            }
                            if (isset($curr->extraOpts["maxVal"]) 
                                && $curr->extraOpts["maxVal"] !== false) {
                                $this->pageJSvalid .= "addReqNodeRadio('" . $nIDtxt 
                                    . "', 'reqFormFldLesser', " 
                                    . $curr->extraOpts["maxVal"] . ");\n";
                            }
                        }
                    }
                    
                } elseif ($curr->nodeType == 'Password') {
                    
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld 
                        . '"><input type="password" name="n' . $nIDtxt . 'fld" id="n' 
                        . $nIDtxt . 'FldID" value="" ' . $onKeyUp . ' autocomplete="off" '
                        . 'class="form-control form-control-lg' . $xtraClass . '" data-nid="' 
                        . $nID . '"' . $GLOBALS["SL"]->tabInd() . '></div>' . $charLimit . "\n"; 
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "addReqNode('" . $nIDtxt . "', 'reqFormFld');\n";
                    }
                    
                } elseif (in_array($curr->nodeType, ['Drop Down', 'U.S. States', 'Countries'])) {
                    
                    $curr = $this->checkResponses($curr, $this->v["fldForeignTbl"]);
                    if (sizeof($curr->responses) > 0 
                        || in_array($curr->nodeType, ['U.S. States', 'Countries'])) {
                        $ret .= $nodePrompt . "\n" . '<div class="nFld' . $isOneLinerFld 
                            . '"><select name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt . 'FldID" data-nid="' 
                            . $nID . '" class="form-control form-control-lg'
                            . (($isOneLinerFld != '') ? ' w33' : '') . $xtraClass . '" onChange="' 
                            . (($curr->isDropdownTagger()) ? ' selectTag(\'' . $nIDtxt 
                                . '\', this.value); this.value=\'\';' : '') . '" autocomplete="off" ' 
                            . $GLOBALS["SL"]->tabInd() . '>'
                            . '<option class="slGrey" value=""' . ((trim($currNodeSessData) == '' 
                                || $curr->isDropdownTagger()) ? ' SELECTED' : '') . ' >'
                            . ((isset($curr->nodeRow->node_text_suggest) 
                                && trim($curr->nodeRow->node_text_suggest) != '')
                                ? $curr->nodeRow->node_text_suggest : 'select...') . '</option>' . "\n";
                        if ($curr->nodeType == 'U.S. States' && !$curr->isDropdownTagger()) {
                            $GLOBALS["SL"]->loadStates();
                            $ret .= $GLOBALS["SL"]->states->stateDrop($currNodeSessData);
                        } else {
                            foreach ($curr->responses as $j => $res) {
                                $val = (($curr->nodeType == 'U.S. States') ? (1+$j) : $res->node_res_value);
                                $select = $this->isCurrDataSelected(
                                    $currNodeSessData, 
                                    $res->node_res_value, 
                                    $curr
                                );
                                $ret .= '<option value="' . $val . '" ' 
                                    . (($select && !$curr->isDropdownTagger()) ? 'SELECTED' : '') . ' >'
                                    . $res->node_res_eng . '</option>' . "\n";
                                if ($curr->isDropdownTagger()) {
                                    $GLOBALS["SL"]->pageJAVA .= "\n" . 'addTagOpt(\'' . $nIDtxt . '\', ' 
                                        . json_encode($val) . ', ' . json_encode($res->node_res_eng) . ', ' 
                                        . (($select) ? 1 : 0) . ');';
                                }
                            }
                        }
                        if ($curr->isRequired()) {
                            $this->pageJSvalid .= "addReqNode('" . $nIDtxt . "', 'reqFormFld');\n";
                        }
                        $ret .= '</select></div>' . "\n"; 
                        if ($curr->isDropdownTagger()) {
                            $ret .= '<input type="hidden" name="n' . $nIDtxt . 'tagIDs" id="n' . $nIDtxt 
                                . 'tagIDsID" value="," class="' . $xtraClass . '" data-nid="' . $nID 
                                . '"><div id="n' . $nIDtxt . 'tags" class="slTagList"></div>';
                            $GLOBALS["SL"]->pageJAVA .= "\n" . 'setTimeout("updateTagList(\'' 
                                . $nIDtxt . '\')", 50); ';
                        }
                    }
                    
                } elseif (in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
                    
                    $curr = $this->checkResponses($curr, $this->v["fldForeignTbl"]);
                    if ($curr->nodeType == 'Radio') {
                        $ret .= '<input type="hidden" name="n' . $nIDtxt . 'radioCurr" id="n' 
                            . $nIDtxt . 'radioCurrID" value="';
                        if (!is_array($currNodeSessData)) {
                            $ret .= $currNodeSessData;
                        } elseif (sizeof($currNodeSessData) > 0) {
                            foreach ($currNodeSessData as $d) {
                                if (strpos($d, trim($GLOBALS["SL"]->currCyc["cyc"][1])) === 0) {
                                    $ret .= $d;
                                }
                            }
                        }
                        $ret .= '">';
                        $GLOBALS["SL"]->pageJAVA .= "\n" . 'addRadioNode(' . $nID . ');';
                    }
                    if (sizeof($curr->responses) > 0) {
                        $ret .= (($curr->isOneLiner()) ? '<div class="pB20">' : '') 
                            . str_replace('<label for="n' . $nIDtxt . 'FldID">', '', 
                                str_replace('<label for="n' . $nIDtxt . 'FldID" >', '', 
                                str_replace('</label>', '', $nodePrompt)))
                            . '<div class="nFld';
                        if ($this->hasSpreadsheetParent($nID)) {
                            $ret .= '">' . "\n";
                        } elseif ($mobileCheckbox) {
                            $ret .= '" style="margin-top: 12px;">' . "\n";
                        } else {
                            $ret .= $isOneLiner . ' pB0 mBn5">' . "\n";
                        }
                        $respKids = ' data-nid="' . $nID . '" class="nCbox' . $nID . ' ' . $xtraClass 
                            . (($curr->hasShowKids) ? ' n' . $nIDtxt . 'fldCls' : '') . '"'; 
                        
                            // onClick="return check' . $nID . 'Kids();"
                        $onClickXtra = '';
                        $GLOBALS["SL"]->pageJAVA .= "\n" . 'addResTot("' . $nID . '", ' 
                            . sizeof($curr->responses) . ');';
                        if ($curr->nodeRow->node_opts%79 == 0) {
                            $onClickXtra = 'chkRadioHide(\'' . $nIDtxt . '\'); ';
                            $GLOBALS["SL"]->pageJAVA .= "\n" . 'setTimeout("' . $onClickXtra . '", 100);';
                        }
                        if ($curr->nodeRow->node_opts%61 == 0) {
                            $ret .= '<div class="row">';
                            $mobileCheckbox = true;
                        }
                        foreach ($curr->responses as $j => $res) {
                            $val = $res->node_res_value;
                            $otherFld = [ '', '', '', '' ];
                            if (in_array($j, $curr->fldHasOther)) {
                                $otherFld[0] = $fld . '_other';
                                $fldVals = [ $fld => $val ];
                                $s = sizeof($this->sessData->dataBranches);
                                if ($s > 0 
                                    && intVal($this->sessData->dataBranches[$s-1]["itemID"]) > 0) {
                                    $tbl2 = $this->sessData->dataBranches[$s-1]["branch"];
                                    $branchLnkFld = $GLOBALS["SL"]
                                        ->getForeignLnkNameFldName($tbl, $tbl2);
                                    if ($branchLnkFld != '') {
                                        $fldVals[$branchLnkFld] = $this->sessData
                                            ->dataBranches[$s-1]["itemID"];
                                    }
                                }
                                $subRowIDs = $this->sessData
                                    ->getRowIDsByFldVal($tbl, $fldVals);
                                $branchRowID = ((sizeof($subRowIDs) > 0) 
                                    ? $subRowIDs[0] : -3);
                                if ($branchRowID > 0) {
                                    $GLOBALS["SL"]->currCyc["res"] = [
                                        $tbl, 
                                        'res' . $j, 
                                        $val
                                    ];
                                    $this->sessData->startTmpDataBranch($tbl, $branchRowID);
                                    $otherFld[1] = $this->sessData->currSessData(
                                        $nID, 
                                        $tbl, 
                                        $otherFld[0], 
                                        'get', 
                                        '', 
                                        $hasParManip
                                    );
                                    $this->sessData->endTmpDataBranch($tbl);
                                    $GLOBALS["SL"]->currCyc["res"] = [
                                        '', 
                                        '', 
                                        -3 
                                    ];                                    
                                } else {
                                    $otherFld[1] = '';
                                }
                                $otherFld[2] = '<input type="text" name="n' . $nID . 'fldOther' . $j 
                                    . '" id="n' . $nID . 'fldOtherID' . $j . '" value="' . $otherFld[1] 
                                    . '" class="form-control ntrStp slTab otherFld slNodeKeyUpOther mL10"' 
                                    . $GLOBALS["SL"]->tabInd() . '>';
                            }
                            
                            if ($curr->nodeType == 'Checkbox' && $curr->indexMutEx($j)) {
                                $GLOBALS["SL"]->pageJAVA .= "\n" . 'addMutEx(' . $nID . ', ' . $j . ');';
                            }
                            $this->pageFldList[] = 'n' . $nIDtxt . 'fld' . $j;
                            $resNameCheck = '';
                            $boxChecked = $this->isCurrDataSelected(
                                $currNodeSessData, 
                                $res->node_res_value, 
                                $curr
                            );
                            if ($curr->nodeType == 'Radio') {
                                $resNameCheck = 'name="n' . $nIDtxt . 'fld" ' 
                                    . (($boxChecked) ? 'CHECKED' : '');
                                if (sizeof($curr->fldHasOther) > 0 && $otherFld[1] == '') {
                                    $otherFld[3] = ' document.getElementById(\'n' . $nID 
                                        . 'fldOtherID' . $j . '\').value=\'\'; ';
                                }
                            } else {
                                $resNameCheck = 'name="n' . $nIDtxt . 'fld[]" ' 
                                    . (($boxChecked) ? 'CHECKED' : '');
                            }
                            
                            if ($curr->nodeRow->node_opts%61 == 0) {
                                $ret .= '<div class="col-' 
                                    . $GLOBALS["SL"]->getColsWidth(sizeof($curr->responses)) . '">';
                            }
                            $onClickFull = trim($otherFld[3] . $onClickXtra);
                            if ($onClickFull != '') {
                                $onClickFull = ' onClick="' . $onClickFull . '" ';
                            }
                            if ($mobileCheckbox) {
                                $ret .= '<label for="n' . $nIDtxt . 'fld' . $j . '" id="n' . $nIDtxt 
                                    . 'fld' . $j . 'lab" class="finger' . (($boxChecked) ? 'Act' : '') 
                                    . '"><div class="disIn mR5"><input id="n' . $nIDtxt . 'fld' . $j 
                                    . '" value="' . $res->node_res_value . '" type="' 
                                    . strtolower($curr->nodeType) . '" ' . $resNameCheck . $respKids 
                                    . ' autocomplete="off" ' . $onClickFull . ' class="slNodeChange" ' 
                                    . $GLOBALS["SL"]->tabInd() . '></div> ' 
                                    . $res->node_res_eng . ' ' . $otherFld[2] . '</label>' . "\n";
                            } else {
                                $ret .= '<div class="' . $isOneLinerFld . '">' 
                                    . ((strlen($res) < 40) ? '<nobr>' : '') 
                                    . '<label for="n' . $nIDtxt . 'fld' . $j 
                                    . '" class="mR10"><div class="disIn mR5"><input id="n' . $nIDtxt 
                                    . 'fld' . $j . '" value="' . $res->node_res_value . '" type="' 
                                    . strtolower($curr->nodeType) . '" ' . $resNameCheck . $respKids 
                                    . ' autocomplete="off"' . $onClickFull . ' class="slNodeChange"' 
                                    . $GLOBALS["SL"]->tabInd() . '></div> ' 
                                    . $res->node_res_eng . ' ' . $otherFld[2] . '</label>' 
                                    . ((strlen($res) < 40) ? '</nobr>' : '') . '</div>' . "\n";
                            }
                            if ($curr->nodeRow->node_opts%61 == 0) {
                                $ret .= '</div> <!-- end col -->' . "\n";
                            }
                            // Check for Layout Sub-Response between each Checkbox Response
                            if ($curr->nodeType == 'Checkbox' && sizeof($tmpSubTier[1]) > 0) {
                                $ret .= $this->printCheckSubRes(
                                    $nID, 
                                    $nSffx, 
                                    $nIDtxt, 
                                    $j, 
                                    $res, 
                                    $boxChecked,
                                    $tbl, 
                                    $fld,
                                    $tmpSubTier
                                );
                            }
                            /* if ($curr->nodeType == 'Checkbox' && in_array($j, $curr->fldHasOther)) {
                                $GLOBALS["SL"]->pageAJAX .= '$(document).on("keyup", "#n' . $nIDtxt . 'fldOtherID' . $j 
                                    . '", function() { if (document.getElementById("n' . $nIDtxt . 'fldOtherID' . $j 
                                    . '").value.trim() != "") { formKeyUpOther(\'' . $nID . '\', ' . $j . '); } });';
                                    // chkSubRes' . $nIDtxt . 'j' . $j . '();
                            } */
                        }
                        if ($curr->isRequired()) {
                            $this->pageJSvalid .= "addReqNodeRadio('" . $nIDtxt 
                                . "', 'reqFormFldRadio', " . sizeof($curr->responses) . ");\n";
                        }
                        
                        if ($curr->nodeRow->node_opts%61 == 0) {
                            $ret .= '</div> <!-- end row -->';
                        }
                        if ($curr->nodeRow->node_opts%79 == 0) {
                            $ret .= '<div id="radioUnHide' . $nIDtxt . '" class="disNon">'
                                . '<a onClick="radioUnHide(\'' . $nIDtxt . '\');" '
                                . 'class="btn btn-secondary btn-sm opac66" href="javascript:;" '
                                . '>Show All Options Again</a></div>';
                        }
                        
                        $ret .= '</div>' . (($curr->isOneLiner()) ? '</div>' : '') . "\n"; 
                    }
                    
                } elseif ($curr->nodeType == 'Date') {
                    
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '">' 
                        . $this->formDate($nID, $nIDtxt, $dateStr, $xtraClass) . '</div>' . "\n";
                    if ($this->nodeHasDateRestriction($curr->nodeRow)) { // then enforce time validation
                        if ($curr->isRequired()) {
                            $this->pageJSvalid .= "addReqNodeDateLimit('" . $nIDtxt 
                                . "', 'reqFormFldDate" . (($curr->isRequired()) ? "And" : "") 
                                . "Limit', " . $curr->nodeRow->node_char_limit . ", '" 
                                . date("Y-m-d") . "', 1);\n";
                            
                        }
                    } elseif ($curr->isRequired()) {
                        $this->pageJSvalid .= "addReqNode('" . $nIDtxt . "', 'reqFormFldDate');\n";
                    }
                    
                } elseif ($curr->nodeType == 'Date Picker') {
                    
                    $GLOBALS["SL"]->pageAJAX .= '$( "#n' . $nIDtxt 
                        . 'FldID" ).datepicker({ maxDate: "+0d" });';
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld 
                        . '"><input name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt 
                        . 'FldID" value="' . $dateStr . '" autocomplete="off" ' . $onKeyUp 
                        . ' type="text" class="dateFld form-control form-control-lg' 
                        . $xtraClass . '" data-nid="' . $nID . '"' 
                        . $GLOBALS["SL"]->tabInd() . '></div>' . "\n";
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "addReqNode('" . $nIDtxt . "', 'reqFormFld');\n";
                    }
                    
                } elseif ($curr->nodeType == 'Time') {
                    
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldHrID'; 
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldMinID';
                    $ret .= str_replace(
                            '<label for="n' . $nIDtxt . 'FldID">', 
                            '<label for="n' . $nIDtxt . 'fldHrID"><label for="n' 
                                . $nIDtxt . 'fldMinID"><label for="n' 
                                . $nIDtxt . 'fldPMID">', 
                            str_replace(
                                '</label>', 
                                '</label></label></label>', 
                                $nodePrompt
                            )
                        ) . '<div class="nFld' . $isOneLinerFld . '">' 
                        . $this->formTime($nIDtxt, $timeStr, $xtraClass) 
                        . '</div>' . "\n";
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "addReqNode('" . $nIDtxt 
                            . "', 'reqFormFld');\n";
                    }
                    
                } elseif ($curr->nodeType == 'Date Time') {
                    
                    $GLOBALS["SL"]->pageAJAX .= '$( "#n' . $nIDtxt 
                        . 'FldID" ).datepicker({ maxDate: "+0d" });';
                    $nodePrompt = str_replace(
                        '</label>', 
                        '</label></label></label></label>', 
                        $nodePrompt
                    );
                    $newLabels = '<label for="n' . $nIDtxt 
                        . 'FldID"><label for="n' . $nIDtxt 
                        . 'fldHrID"><label for="n' . $nIDtxt 
                        . 'fldMinID"><label for="n' . $nIDtxt . 'fldPMID">';
                    $nodePrompt = str_replace(
                        '<label for="n' . $nIDtxt . 'FldID">', 
                        $newLabels, 
                        $nodePrompt
                    );
                    $ret .= view(
                        'vendor.survloop.forms.formtree-datetime', 
                        [
                            "nID"            => $nIDtxt,
                            "dateStr"        => $dateStr,
                            "onKeyUp"        => $onKeyUp,
                            "isOneLinerFld"  => $isOneLinerFld,
                            "xtraClass"      => $xtraClass,
                            "inputMobileCls" => $this->inputMobileCls($nID),
                            "formTime"       => $this->formTime($nID, $timeStr),
                            "nodePrompt"     => $nodePrompt
                        ]
                    )->render();
                    $this->pageFldList[] = 'n' . $nIDtxt . 'FldID'; 
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldHrID'; 
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldMinID';
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "addReqNode('" . $nIDtxt 
                            . "', 'reqFormFld');\n";
                    }
                    
                } elseif ($curr->nodeType == 'Feet Inches') {
                    
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldFeetID'; 
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldInchID';
                    $feet = ($currNodeSessData > 0) ? floor($currNodeSessData/12) : 0; 
                    $inch = ($currNodeSessData > 0) ? intVal($currNodeSessData)%12 : 0;
                    $nodePrompt = str_replace(
                        '</label>', 
                        '</label></label>', 
                        $nodePrompt
                    );
                    $nodePrompt = str_replace(
                        '<label for="n' . $nIDtxt . 'FldID">', 
                        '<label for="n' . $nIDtxt . 'fldFeetID"><label for="n' 
                            . $nIDtxt . 'fldInchID">', 
                        $nodePrompt
                    );
                    $ret .= view(
                        'vendor.survloop.forms.formtree-feetinch', 
                        [
                            "nIDtxt"           => $nIDtxt,
                            "feet"             => $feet,
                            "inch"             => $inch,
                            "isOneLinerFld"    => $isOneLinerFld,
                            "xtraClass"        => $xtraClass,
                            "currNodeSessData" => $currNodeSessData,
                            "inputMobileCls"   => $this->inputMobileCls($nID),
                            "nodePrompt"       => $nodePrompt
                        ]
                    )->render();
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "addReqNode('" . $nIDtxt 
                            . "', 'reqFormFeetInches');\n";
                    }
                    
                } elseif (in_array($curr->nodeType, ['Gender', 'Gender Not Sure'])) {
                    
                    $fldOth = $fld . '_other';
                    $currSessDataOther = $this->sessData->currSessData($nID, $tbl, $fldOth);
                    for ($j = 0 ; $j < sizeof($curr->responses); $j++) {
                        $this->pageFldList[] = 'n' . $nIDtxt . 'fld' . $j;
                    }
                    $ret .= view(
                        'vendor.survloop.forms.formtree-gender', 
                        [
                            "nID"               => $nIDtxt,
                            "nodeRow"           => $curr->nodeRow,
                            "nodePromptText"    => $nodePromptText,
                            "nodePromptNotes"   => $nodePromptNotes,
                            "isOneLinerFld"     => $isOneLinerFld,
                            "xtraClass"         => $xtraClass,
                            "responses"         => $curr->responses,
                            "currNodeSessData"  => $currNodeSessData,
                            "currSessDataOther" => $currSessDataOther
                        ]
                    )->render();
                    $gendAllow = [ 'Female', 'Male', 'Other', 'Not sure' ];
                    $genderSuggest = '';
                    foreach ($GLOBALS["SL"]->def->getOtherGenders() as $gen) {
                        if (!in_array($gen, $gendAllow)) {
                            $genderSuggest .= ', "' . $gen . '"';
                        }
                    }
                    $GLOBALS["SL"]->pageAJAX .= '$( "#n' . $nIDtxt 
                        . 'fldOtherID2" ).autocomplete({ source: [' 
                        . substr($genderSuggest, 1) . '] });' . "\n";
                    $this->v["javaNodes"] .= 'nodeResTot[' . $nID . '] = ' 
                        . sizeof($curr->responses) . '; ';
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "addReqNode('" 
                            . $nIDtxt . "', 'reqFormGender');\n";
                    }
                    
                } elseif ($curr->nodeType == 'Uploads') {
                    
                    $this->pageHasUpload[] = $nID;
                    $ret .= $nodePrompt . '<div class="nFld">' 
                        . $this->uploadTool($nID, $nIDtxt) . '</div>';
                    
                } else { // instruction only
                    
                    $ret .= "\n" . $nodePrompt . "\n";
                    
                } // end node input field types
                
                // copy input to extra div?
                if (in_array($curr->nodeType, ['Text', 'Text:Number']) 
                    && $curr->nodeRow->node_opts%41 == 0) { 
                    $GLOBALS["SL"]->pageJAVA .= "\n" . 'setTimeout("copyNodeResponse(\'n' 
                        . $nIDtxt . 'FldID\', \'nodeEcho' . $nIDtxt . '\')", 50);';
                    $GLOBALS["SL"]->pageAJAX .= '$("#n' . $nIDtxt . 'FldID").keyup('
                        . 'function() { copyNodeResponse(\'n' . $nIDtxt 
                        . 'FldID\', \'nodeEcho' . $nIDtxt . '\'); });' . "\n";
                }

                if ($curr->hasShowKids 
                    && isset($this->kidMaps[$nID]) 
                    && sizeof($this->kidMaps[$nID]) > 0) {
                    if (!isset($this->v["nodeKidFunks"])) {
                        $this->v["nodeKidFunks"] = '';
                    }
                    $this->v["nodeKidFunks"] .= 'checkNodeKids' . $nIDtxt . '(); ';
                    $GLOBALS["SL"]->pageAJAX .= 'function checkNodeKids' 
                        . $nIDtxt . '() { var showKids = false; ';
                    foreach ($this->kidMaps[$nID] as $nKid => $ress) {
                        $nKidTxt = trim($nKid . $nSffx);
                        if ($ress && sizeof($ress) > 0) {
                            $if = $this->getJsShowKidsIf($ress, $nIDtxt, $curr->nodeType);
                            $grankids = [];
                            if (isset($showMoreNodes[$nKid]) 
                                && sizeof($showMoreNodes[$nKid]) > 0) {
                                $grankids = $showMoreNodes[$nKid];
                            }
                            $GLOBALS["SL"]->pageAJAX .= view(
                                'vendor.survloop.forms.formtree-show-kids-ajax', 
                                [
                                    "nID"        => $nID,
                                    "nIDtxt"        => $nIDtxt,
                                    "nSffx"         => $nSffx,
                                    "curr"          => $curr,
                                    "nKid"          => $nKid,
                                    "ress"          => $ress,
                                    "grankids" => $grankids,
                                    "if"            => $if
                                ]
                            )->render();
                        }
                    }
                    $GLOBALS["SL"]->pageAJAX .= 'if (showKids) { $("#node' . $nIDtxt 
                        . 'kids").slideDown("50"); } else { $("#node' . $nIDtxt 
                        . 'kids").slideUp("50"); } ';
                    if (in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
                        $GLOBALS["SL"]->pageAJAX .= '} $(".n' . $nIDtxt 
                            . 'fldCls").click(function(){ checkAllNodeKids(); }); ';
                    } else {
                        $GLOBALS["SL"]->pageAJAX .= '} $(document).on("change", "#n' 
                            . $nIDtxt . 'FldID", function(){ checkAllNodeKids(); }); ';
                    }
                }
                
                // end Q&A on same row
                if ($curr->isOneLiner() 
                    && !in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
                    $ret .= '</div> <!-- end one-liner -->';
                }
                
            } // end main Node printer
            
            if (trim($promptNotesSpecial) != '') {
                $ret .= $this->printSpecial($nID, $promptNotesSpecial, $currNodeSessData);
            }
            
            $ret .= $nodePromptAfter;
            if ($this->shouldPrintHalfGap($curr)) {
                $ret .= '<div class="nodeHalfGap"></div>';
            }
            
            $retKids = '';
            if (sizeof($tmpSubTier[1]) > 0 && !$curr->isLoopCycle() 
                && !$curr->isSpreadTbl()) {
                if ($curr->nodeType == 'Big Button' 
                    && $curr->nodeRow->node_opts%43 == 0) {
                    $currVisib = 0;
                }
                $retKids .= '<div id="node' . $nIDtxt . 'kids" class="'
                    . (($currVisib == 0) ? 'disNon' 
                        : (($curr->nodeType == 'Layout Row') ? 'disFlx row' : 'disBlo'))
                    . (($curr->nodeType == 'Gallery Slider') ? ' h50 ovrNo' : '') . '">';
                if ($curr->isGraph()) {
                    $this->v["graphFilters"] = $nID;
                }
                foreach ($tmpSubTier[1] as $childNode) { // recurse deez!..
                    if (!$this->allNodes[$childNode[0]]->isPage() 
                        && $this->allNodes[$childNode[0]]->nodeType != 'Layout Sub-Response') {
                        $kid = $this->printNodePublic($childNode[0], $childNode, $currVisib);
                        if (!is_array($kid)) {
                            $retKids .= $kid;
                        } else {
                            $retKids .= implode(' ', $kid);
                        }
                    }
                }
                if ($curr->isGraph()) $this->v["graphFilters"] = false;
                $retKids .= '</div> <!-- end #node' . $nIDtxt . 'kids -->';
            }
            $ret .= $retKids;
            
            if ($curr->nodeType == 'Gallery Slider' && sizeof($tmpSubTier[1]) > 0) {
                $GLOBALS["SL"]->pageJAVA .= 'initGalSlider("' . $nIDtxt . '", "';
                $ret .= '<div id="sliNavDiv' . $nIDtxt . '" class="sliNavDiv">'
                    . '<a href="javascript:;" class="sliLft" id="sliLft' . $nIDtxt 
                    . '"><div id="sliLftHvr' . $nIDtxt
                    . '"></div><i class="fa fa-chevron-left" aria-hidden="true"></i></a>'
                    . '<a href="javascript:;" class="sliRgt" id="sliRgt' . $nIDtxt 
                    . '"><div id="sliRgtHvr' . $nIDtxt
                    . '"></div><i class="fa fa-chevron-right" aria-hidden="true"></i></a><div class="pT5">';
                foreach ($tmpSubTier[1] as $j => $kid) {
                    $ret .= '<a href="javascript:;" class="sliNav' 
                        . (($j == 0) ? 'Act' : '') . '" id="sliNav' . $nIDtxt
                        . 'dot' . $j . '"><i class="fa fa-dot-circle-o" aria-hidden="true"></i></a>';
                    $GLOBALS["SL"]->pageJAVA .= (($j > 0) ? ',' : '') . $kid[0];
                }
                $ret .= '</div></div>';
                $GLOBALS["SL"]->pageJAVA .= '", \'{ }\'); ' . "\n";
            }
            
            if ($curr->nodeRow->node_opts%37 == 0) {
                $ret .= '</div> <!-- end jumbotron -->' . "\n";
            }
            if (!in_array($curr->nodeType, ['Layout Row', 'Layout Column'])) {
                $ret .= "\n" . '</div> <!-- end #node' . $nIDtxt . ' -->' . "\n";
            }
            if ($GLOBALS["SL"]->treeRow->tree_type == 'Page' 
                && $curr->parentID == $GLOBALS['SL']->treeRow->tree_root) {
                $ret .= '</div>' . (($curr->isPageBlockSkinny()) 
                    ? '</center>' : '') . "\n";
            }
            $ret .= '</div>'; // for blockWrap or Layout Column
            
        } // end of non-Hero Image node
        
        if ($curr->isDataManip()) {
            $this->closeManipBranch($nID);
        }
        $this->closePrintNodePublic($nID, $nIDtxt, $curr);
        
        return $this->wrapNodePrint($ret, $nID);
    }

    protected function printCheckSubRes($nID, $nSffx, $nIDtxt, $j, $res, $boxChecked, $tbl, $fld, $tmpSubTier)
    {
        $ret = '';
        foreach ($tmpSubTier[1] as $childNode) {
            if ($this->allNodes[$childNode[0]]->nodeType == 'Layout Sub-Response' 
                && sizeof($childNode[1]) > 0) {
                $ret .= '<div id="n' . $nIDtxt . 'fld' . $j . 'sub" class="subRes '
                    . (($boxChecked) ? 'disBlo' : 'disNon') . '" >';
                $GLOBALS["SL"]->currCyc["res"][0] = $tbl;
                $GLOBALS["SL"]->currCyc["res"][1] = 'res' . $j;
                $GLOBALS["SL"]->currCyc["res"][2] = $res->node_res_value;
                $fldAssign = [ $fld => $res->node_res_value ];
                $subRowIDs = $this->sessData->getRowIDsByFldVal($tbl, $fldAssign);
                $branchRowID = ((sizeof($subRowIDs) > 0) ? $subRowIDs[0] : -3);
                if ($branchRowID > 0) {
                    $this->sessData->startTmpDataBranch($tbl, $branchRowID);
                }
                $grankids = '';
                foreach ($childNode[1] as $k => $granNode) {
                    $grankids .= (($k > 0) ? ', ' : '') . $granNode[0];
                    $ret .= $this->printNodePublic($granNode[0], $granNode, $boxChecked);
                }
                if ($branchRowID > 0) {
                    $this->sessData->endTmpDataBranch($tbl);
                }
                $GLOBALS["SL"]->currCyc["res"] = [ '', '', -3 ];
                $ret .= '</div>';
                $GLOBALS["SL"]->pageAJAX .= view(
                    'vendor.survloop.forms.formtree-sub-response-ajax', 
                    [
                        "nID"      => $nID,
                        "nSffx"    => $nSffx,
                        "nIDtxt"   => $nIDtxt,
                        "j"        => $j,
                        "grankids" => $grankids
                    ]
                )->render();
            }
        }
        return $ret;
    }

    
} // end of TreeSurvForm class
