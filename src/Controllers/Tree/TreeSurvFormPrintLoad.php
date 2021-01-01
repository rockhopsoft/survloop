<?php
/**
  * TreeSurvFormPrintLoad is a mid-level class of the branching tree, which provides
  * functions to prep for the main functions that generate output.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.13
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use RockHopSoft\Survloop\Controllers\Tree\TreeSurvFormWidgets;

class TreeSurvFormPrintLoad extends TreeSurvFormWidgets
{
    protected function printNodePublicInit(&$curr)
    {
        $this->v["fldForeignTbl"] = $GLOBALS["SL"]->fldForeignKeyTbl(
            $curr->tbl, 
            $curr->fld
        );
        if (($curr->isPage() || $curr->isInstruct()) 
            && isset($GLOBALS["SL"]->closestLoop["obj"]->data_loop_table)) {
            $curr->tbl = $GLOBALS["SL"]->closestLoop["obj"]->data_loop_table;
        }
        if ($curr->tbl == '' && $this->hasCycleAncestorActive($curr->nID)) {
            $curr->tbl = $GLOBALS["SL"]->currCyc["cyc"][0];
        }
        if ($curr->tbl == '' && $this->hasSpreadsheetParentActive($curr->nID)) {
            $curr->tbl = $GLOBALS["SL"]->currCyc["tbl"][0];
        }
        
        // if ($curr->currVisib == 1 && $curr->nodeType == 'Data Manip: New') $this->runDataManip($nID);
        if ($curr->isDataManip()) {
            $this->loadManipBranch($curr->nID, ($curr->currVisib == 1));
        }
        $curr->hasParManip = $this->hasParentDataManip($curr->nID);

        if (!isset($this->v["hasFixedHeader"])) {
            $this->v["hasFixedHeader"] = false;
        }
        return true;
    }

    protected function printNodePublicCurrData(&$curr)
    {
        $this->printNodePublicCurrDataGetItem($curr);
        $curr->sessData = $this->sessData->currSessData($curr, 'get', '');
        //if ($itemID <= 0) $curr->sessData = ''; // override false profit ;-P
        if ($curr->sessData == '' && trim($curr->nodeRow->node_default) != '') {
            $curr->sessData = $curr->nodeRow->node_default;
        }

        // check for extra custom PHP code stored with the node; check for standardized techniques
        $nodeOverrides = $this->printNodeSessDataOverride($curr);
        if (is_array($nodeOverrides) && sizeof($nodeOverrides) > 1) {
            $curr->sessData = $nodeOverrides;
        } elseif (is_array($nodeOverrides) 
            && sizeof($nodeOverrides) == 1 
            && isset($nodeOverrides[0])) {
            $curr->sessData = $nodeOverrides[0];
        }
        if ($GLOBALS["SL"]->REQ->has('nv' . $curr->nIDtxt) 
            && trim($GLOBALS["SL"]->REQ->get('nv' . $curr->nIDtxt)) != '') {
            $curr->sessData = $GLOBALS["SL"]->REQ->get('nv' . $curr->nIDtxt);
        }

        // should migrate in this direction
        $this->v["currNodeSessData"] = $curr->sessData; 
        return true;
    }

    protected function printNodePublicCurrDataGetItem(&$curr)
    {
        $curr->itemInd = $curr->itemID = -3;
        if ($this->hasActiveParentCyc($curr->nID, $curr->tbl)) {
            list($curr->itemInd, $curr->itemID) = $this->chkParentCycInds(
                $curr->nID, 
                $curr->tbl
            );
        } else { // default logic, not LoopCycle, not SpreadTable 
            list($curr->itemInd, $curr->itemID) = $this->sessData->currSessDataPos(
                $curr->tbl, 
                $curr->hasParManip,
                $curr->nID
            );
            if ($curr->itemInd < 0 
                && isset($GLOBALS["SL"]->closestLoop["loop"]) 
                && trim($GLOBALS["SL"]->closestLoop["loop"]) != '' 
                && $curr->tbl == $this->sessData->isCheckboxHelperTable($curr->tbl)) {
                // In this context, relevant item index is item's 
                // index with the loop, not table's whole data set
                $curr->itemInd = $this->sessData->getLoopIndFromID(
                    $GLOBALS["SL"]->closestLoop["loop"], 
                    $curr->itemID
                );
            }
        }
        if ($curr->itemInd < 0 && sizeof($this->sessData->dataBranches) > 0) {
            $ind = sizeof($this->sessData->dataBranches)-1;
            if (isset($this->sessData->dataBranches[$ind]["branch"])
                && trim($this->sessData->dataBranches[$ind]["branch"]) != ''
                && isset($this->sessData->dataBranches[$ind]["itemID"])
                && intVal($this->sessData->dataBranches[$ind]["itemID"]) > 0) {
                $tbl = $this->sessData->dataBranches[$ind]["branch"];
                $curr->itemID = intVal($this->sessData->dataBranches[$ind]["itemID"]);
                $curr->itemInd = $this->sessData->getRowInd($tbl, $curr->itemID);
            }
        }
        return true;
    }

    protected function printNodeCondKids(&$curr)
    {
        if (!isset($this->v["javaNodes"])) {
            $this->v["javaNodes"] = '';
        }
        $this->v["javaNodes"] .= 'nodeParents[' . $curr->nID . '] = ' 
            . $curr->parentID . ';' . "\n";
        if ($curr->nSffx != '') {
            $this->v["javaNodes"] .= 'nodeSffxs[nodeSffxs.length] = "' 
                . $curr->nSffx . '";' . "\n";
        }
        $curr->condKids = $curr->showMoreNodes = [];
        if (sizeof($curr->tmpSubTier[1]) > 0) {
            if ($curr->nodeType == 'Countries') {
                $this->printNodePublicLoadCondKidsCountry($curr);
            }

            if ($curr->hasShowKids && sizeof($curr->responses) > 0) {

                // displaying children on page is conditional
                foreach ($curr->responses as $j => $res) {
                    if (intVal($res->node_res_show_kids) > 0) {
                        if (!isset($curr->condKids[$res->node_res_show_kids])) {
                            $curr->condKids[$res->node_res_show_kids] = [];
                        }
                        $curr->condKids[$res->node_res_show_kids][] = $res->node_res_value;
                    }
                }
                if (sizeof($curr->condKids) > 0) {
                    foreach ($curr->condKids as $condNode => $condVals) {
                        $condHide = true;
                        foreach ($condVals as $cVal) {
                            if ($this->isCurrDataSelected($curr, $cVal)) {
                                $condHide = false;
                            }
                        }
                        if ($condHide) {
                            $this->hideKidNodes[] = $condNode;
                        }
                    }
                }
                $this->v["javaNodes"] .= 'conditionNodes[' 
                    . $curr->nID . '] = true;' . "\n";
                $childList = $this->printNodePublicLoadAllCondKids($curr);
                $this->v["javaNodes"] .= 'nodeKidList[' . $curr->nID . '] = ['
                    . implode(', ', $childList).'];' . "\n";
            }
        }
        if ($curr->currVisib < 0) {
            $curr->currVisib = 1;
            if (in_array($curr->nID, $this->hideKidNodes)) {
                $curr->currVisib = 0;
            }
        } elseif ($curr->currVisib == 1 
            && in_array($curr->nID, $this->hideKidNodes)) {
            $curr->currVisib = 0;
        }
        if ($curr->isRequired()) {
            $this->pageHasReqs++;
        }
        return true;
    }

    protected function printNodePublicLoadCondKidsCountry(&$curr)
    {
        $nxtNode = $this->nextNode($curr->nID);
        if ($nxtNode > 0 && isset($this->allNodes[$nxtNode])) {
            if ($this->allNodes[$nxtNode]->nodeType == 'U.S. States') {
                $curr->hasShowKids = true;
                $GLOBALS["SL"]->loadStates();
                $curr->responses = $GLOBALS["SL"]->states->getCountryResponses(
                    $curr->nID, 
                    [ 'United States' ]
                );
            }
        }
        return true;
    }

    protected function printNodePublicLoadAllCondKids(&$curr)
    {
        $types = [
            'Page Block', 'Data Manip: New', 'Data Manip: Update', 
            'Data Manip: Wrap', 'Instructions', 'Instructions Raw', 
            'Layout Row', 'Gallery Slider'
        ];
        $childList = [];
        foreach ($curr->tmpSubTier[1] as $child) {
            $childList[] = $child[0];
            if (isset($this->kidMaps[$curr->nID]) 
                && sizeof($this->kidMaps[$curr->nID]) > 0) {
                foreach ($this->kidMaps[$curr->nID] as $nKid => $ress) {
                    $this->printNodeCondKid($curr, $nKid, $child, $ress, $types);
                }
            }
        }
        return $childList;
    }

    protected function printNodeCondKid(&$curr, $nKid, $child, $ress, $types)
    {
        if ($nKid == $child[0] 
            && sizeof($child[1]) > 0
            && in_array($this->allNodes[$nKid]->nodeType, $types)) {
            foreach ($child[1] as $grand) {
                if (!isset($showMoreNodes[$child[0]])) {
                    $curr->showMoreNodes[$child[0]] = []; 
                }
                $curr->showMoreNodes[$child[0]][] = $grand[0];
                $this->printNodeCondGrandKids($nKid, $child, $grand);
            }
        }
        return true;
    }

    protected function printNodeCondGrandKids($nKid, $child, $grand)
    {
        if ($this->allNodes[$nKid]->nodeType == 'Layout Row' 
            && sizeof($grand[1]) > 0) {
            foreach ($grand[1] as $greatGrand) {
                if ($this->allNodes[$greatGrand[0]]->nodeType == 'Layout Column' 
                    && sizeof($greatGrand[1]) > 0) {
                    $curr->showMoreNodes[$child[0]][] = $greatGrand[0];
                    foreach ($greatGrand[1] as $greatGreatGrand) {
                        $curr->showMoreNodes[$child[0]][] = $greatGreatGrand[0];
                    }
                }
            }
        }
        return true;
    }

    protected function printNodePublicVisibility($curr)
    {
        $ret = '';
        if (!$GLOBALS["SL"]->isPdfView()) {
            $ret .= '<input type="hidden" name="n' 
                . $curr->nIDtxt . 'Visible" id="n' . $curr->nIDtxt 
                . 'VisibleID" value="' . $curr->currVisib . '">';
        }
        if ($curr->nodeType == 'Layout Column') {
            $ret = '';
        }
        if ($this->page1stVisib == '' && $curr->currVisib == 1) {
            $checkboxes = [
                'Radio', 'Checkbox', 'Gender', 'Gender Not Sure'
            ];
            $texts = [
                'Drop Down', 'Text', 'Long Text', 'Text:Number', 'Slider', 
                'Email', 'Password', 'U.S. States', 'Countries'
            ];
            if (in_array($curr->nodeType, $checkboxes)) {
                $this->page1stVisib = 'n' . $curr->nID . 'fld0';
            } elseif (in_array($curr->nodeType, ['Date', 'Date Time'])) {
                $this->page1stVisib = 'n' . $curr->nID . 'fldMonthID';
            } elseif (in_array($curr->nodeType, ['Time'])) {
                $this->page1stVisib = 'n' . $curr->nID . 'fldHrID';
            } elseif (in_array($curr->nodeType, ['Feet Inches'])) {
                $this->page1stVisib = 'n' . $curr->nID . 'fldFeetID';
            } elseif (in_array($curr->nodeType, $texts)) {
                $this->page1stVisib = 'n' . $curr->nID . 'FldID';
            }
        }
        return $ret;
    }

    protected function printNodePublicAddOns(&$curr)
    {
        $curr->nodePromptAfter = $curr->onKeyUp = $curr->charLimit = '';
        //if (in_array($curr->nodeType, ['Radio', 'Checkbox']) 
        //    && sizeof($curr->responses) > 0 
            $GLOBALS["SL"]->pageJAVA .= 'addIsMobile(' . $curr->nID . ', true); ';
        //} else {
        //    $GLOBALS["SL"]->pageJAVA .= 'addIsMobile(' . $curr->nID . ', false); ';
        //}
        $this->printNodePublicAddClass($curr);
        $this->printNodePublicAddWidget($curr);
        $this->printNodePublicAddKeyUp($curr);
        return true;
    }

    protected function printNodePublicAddClass(&$curr)
    {
        $curr->xtraClass = ' slTab slNodeChange';
        if ($curr->nodeType != 'Long Text') {
            $curr->xtraClass .= ' ntrStp';
        }
        if (isset($this->v["graphFilters"]) 
            && intVal($this->v["graphFilters"]) > 0 
            && trim($curr->dataStore) != '') {
            $typeList = [ 'Drop Down', 'U.S. States', 'Countries' ];
            $drp = ((in_array($curr->nodeType, $typeList)) ? 'Drp' : '');
            $curr->xtraClass .= ' graphUp' . $drp;
            $GLOBALS["SL"]->pageAJAX .= 'addGraphFld("' . $curr->nIDtxt . '", ' 
                . $GLOBALS["SL"]->getFldIDFromFullWritName($curr->dataStore) . ', ' 
                . $this->v["graphFilters"] . ');' . "\n";
        }
        return true;
    }

    // check for extra custom HTML/JS/CSS code stored with the node; 
    // check for standardized techniques
    protected function printNodePublicAddWidget(&$curr)
    {
        if (trim($curr->nodeRow->node_prompt_after) != '' 
            && !$curr->isWidget()) {
            $after = $curr->nodeRow->node_prompt_after;
            $ajax = '/'.'* formAJAX *'.'/';
            if (stripos($after, $ajax) !== false) {
                $GLOBALS["SL"]->pageAJAX .= $after;
            } else {
                if (!$curr->isPage()) {
                    $pos = strpos(
                        $after, 
                        'function fldOnKeyUp[[nID]]('
                    );
                    if ($pos !== false) {
                        $curr->onKeyUp .= ' fldOnKeyUp' 
                            . $curr->nIDtxt . '(); ';
                    }
                    $curr->nodePromptAfter = $this->swapLabels($curr, $after);
                    $curr->nodePromptAfter = $GLOBALS["SL"]->extractJava(
                        $curr->nodePromptAfter, 
                        $curr->nID
                    );
                }
            }
        }
        return true;
    }
    
    protected function printNodePublicAddKeyUp(&$curr)
    {
        $this->printNodePublicAddCharCnt($curr);
        $this->printNodePublicAddMinMax($curr);
        //if ($curr->nodeType == 'Long Text') $curr->onKeyUp .= ' flexAreaAdjust(this); ';
        if (trim($curr->onKeyUp) != '') {
            $curr->onKeyUp = ' onKeyUp="' . $curr->onKeyUp . '" ';
        }
        return true;
    }
    
    protected function printNodePublicAddCharCnt(&$curr)
    {
        /* if (intVal($curr->nodeRow->node_char_limit) > 0 && $curr->nodeRow->node_opts%31 > 0 
            && $curr->nodeType != 'Uploads') {
            $curr->onKeyUp .= ' charLimit(\'' . $curr->nIDtxt . '\', ' . $curr->nodeRow->node_char_limit . '); ';
            $curr->charLimit = "\n" . '<div id="charLimit' . $curr->nID . 'Msg" class="txtDanger f12 opac33"></div>';
            $GLOBALS["SL"]->pageJAVA .= 'setTimeout("charLimit(\'' 
                . $curr->nIDtxt . '\', ' . $curr->nodeRow->node_char_limit 
                . ')", 50);' . "\n";
        } */
        if ($curr->nodeRow->node_opts%31 == 0 
            || $curr->nodeRow->node_opts%47 == 0) {
            if (intVal($curr->nodeRow->node_char_limit) == 0) {
                $curr->nodeRow->node_char_limit = 10000000000;
            }
            $max = 0;
            if ($curr->nodeRow->node_opts%47 == 0) {
                $max = $curr->nodeRow->node_char_limit;
            }
            $wrdCntKey = 'wordCountKeyUp(\'' . $curr->nIDtxt . '\', ' . $max
                . ', ' . intVal($curr->nodeRow->node_char_limit) . ')';
            $curr->onKeyUp .= ' ' . $wrdCntKey . '; ';
            $GLOBALS["SL"]->pageJAVA .= 'setTimeout("' 
                . $wrdCntKey . '", 50);' . "\n";
        }
        return true;
    }
    
    protected function printNodePublicAddMinMax(&$curr)
    {
        if (isset($curr->extraOpts["minVal"]) 
            && $curr->extraOpts["minVal"] !== false) {
            $curr->onKeyUp .= ' checkMin(\'' . $curr->nIDtxt . '\', ' 
                . $curr->extraOpts["minVal"] . '); ';
        }
        if (isset($curr->extraOpts["maxVal"]) 
            && $curr->extraOpts["maxVal"] !== false) {
            $curr->onKeyUp .= ' checkMax(\'' . $curr->nIDtxt . '\', ' 
                . $curr->extraOpts["maxVal"] . '); ';
        }
        return true;
    }

    protected function printNodePublicPrompts(&$curr)
    {
        // check notes settings for any standardized techniques
        $promptNotesSpecial = '';
        if ($this->isPromptNotesSpecial($curr->nodeRow->node_prompt_notes)) {
            $promptNotesSpecial = $curr->nodeRow->node_prompt_notes;
            $curr->nodeRow->node_prompt_notes = '';
        }
        
        // write basic node field labeling
        $tmp = $curr->nodeRow->node_prompt_text;
        $tmp = $this->swapLabels($curr, $tmp);
        $tmp  = stripslashes($tmp);
        if ($curr->isRequired() && $curr->nodeType != 'Hidden Field') {
            $tmp = $this->addPromptTextRequired($curr, $tmp, $curr->nIDtxt);
        }
        $curr->nodePromptText = $tmp;

        $tmp = $curr->nodeRow->node_prompt_notes;
        $curr->nodePromptNotes = $this->swapLabels($curr, $tmp);
        $curr->nodePromptNotes = stripslashes($curr->nodePromptNotes);
        if (trim($curr->nodePromptNotes) != '' && !$curr->isLoopRoot()) {
            if ($curr->nodeRow->node_opts%83 == 0) {
                $curr->nodePromptText = '<a id="hidivBtnnLabel' . $curr->nIDtxt 
                    . 'notes" class="hidivBtn crsrPntr float-right">'
                    . '<i class="fa fa-info-circle" aria-hidden="true"></i></a>' 
                    . $curr->nodePromptText;
            }
            $curr->nodePromptText .= '<div id="hidivnLabel' 
                . $curr->nIDtxt . 'notes" class="subNote'
                . (($curr->nodeRow->node_opts%83 == 0) ? ' disNon' : '') 
                . '">' . $curr->nodePromptNotes . '</div>' . "\n";
        }
        if (strpos($curr->nodePromptText, 'fixedHeader') !== false) {
            $this->v["hasFixedHeader"] = true;
        }
        
        $curr->nodePromptText  = $GLOBALS["SL"]->extractJava(
            $curr->nodePromptText, 
            $curr->nID
        );
        $curr->nodePromptNotes = $GLOBALS["SL"]->extractJava(
            $curr->nodePromptNotes, 
            $curr->nID
        );
        
        $curr->nodePrompt = '';
        if (strpos($curr->nodeRow->node_prompt_text, '[[PreviewPrivate]]') !== false 
            || strpos($curr->nodeRow->node_prompt_text, '[[PreviewPublic]]') !== false) {
            $curr->nodePrompt = $curr->nodePromptText;
        } elseif (trim($curr->nodePromptText) != '' 
            && !$curr->isDataPrint() 
            && !$this->hasSpreadsheetParent($curr->nID)) {
            if ($curr->isInstructAny()) {
                $curr->nodePrompt = '<div id="nLabel' . $curr->nIDtxt 
                    . '" class="nPrompt">' . $curr->nodePromptText . '</div>' . "\n";
            } else {
                $w100 = '';
                if (!in_array($curr->nodeType, ['Radio', 'Checkbox']) 
                    || $curr->nodeRow->node_opts%83 == 0) {
                    $w100 = ' class="w100"';
                }
                $curr->nodePrompt = "\n" . '<div id="nLabel' . $curr->nIDtxt 
                    . '" class="nPrompt"><label for="n' . $curr->nIDtxt . 'FldID"' 
                    . $w100 . ' >' . $curr->nodePromptText . '</label></div>' . "\n";
            }
        }
        return $promptNotesSpecial;
    }

    protected function nodePrintWrapStart(&$curr)
    {
        $ret = '';
        if ($curr->nodeType == 'Layout Column') {
            $ret .= '<div id="col' . $curr->nIDtxt . '" class="col-lg-' 
                . $curr->nodeRow->node_char_limit . '">';
        } else {
            $ret .= $this->nodePrintBlockStart($curr);
        }

        if (!$this->hasSpreadsheetParent($curr->nID)
            && !$GLOBALS["SL"]->isPdfView()) {
            $ret .= '<div class="fC"></div><div class="nodeAnchor"><a id="n' 
                . $curr->nIDtxt . '" name="n' . $curr->nIDtxt . '"></a></div>';
        }

        $ret .= $this->nodePrintWrapTreeStart($curr)
            . $this->nodePrintWrapNodeStart($curr);

        if ($curr->nodeRow->node_opts%37 == 0) {
            $ret .= '<div class="jumbotron">';
        }
        if ($this->shouldPrintHalfGap($curr)) {
            $ret .= '<div class="nodeHalfGap"></div>';
        }
        return $ret;
    }

    protected function nodePrintWrapEnd(&$curr)
    {
        $ret = '';
        if ($curr->nodeType == 'Gallery Slider' 
            && sizeof($curr->tmpSubTier[1]) > 0) {
            $GLOBALS["SL"]->pageJAVA .= 'initGalSlider("' . $curr->nIDtxt . '", "';
            foreach ($curr->tmpSubTier[1] as $j => $kid) {
                $GLOBALS["SL"]->pageJAVA .= (($j > 0) ? ',' : '') . $kid[0];
            }
            $GLOBALS["SL"]->pageJAVA .= '", \'{ }\'); ' . "\n";
            $ret .= view(
                'vendor.survloop.forms.formtree-gallery-end', 
                [ "curr" => $curr ]
            )->render();
        }
        
        if ($curr->nodeRow->node_opts%37 == 0) {
            $ret .= '</div> <!-- end jumbotron -->' . "\n";
        }
        if (!in_array($curr->nodeType, ['Layout Row', 'Layout Column'])) {
            $ret .= "\n" . '</div> <!-- end #node' . $curr->nIDtxt . ' -->' . "\n";
        }
        if ($GLOBALS["SL"]->treeRow->tree_type == 'Page' 
            && $curr->parentID == $GLOBALS['SL']->treeRow->tree_root) {
            $ret .= '</div>' . (($curr->isPageBlockSkinny()) ? '</center>' : '');
        }
        return $ret . '</div>'; // for blockWrap or Layout Column
    }

    protected function nodePrintWrapTreeStart($curr)
    {
        $ret = '';
        if ($GLOBALS["SL"]->treeRow->tree_type == 'Page' 
            && $curr->parentID == $GLOBALS['SL']->treeRow->tree_root) {
            if ($curr->isPageBlockSkinny()) { // wrap page block
                $ret .= '<center><div class="treeWrapForm" id="treeWrap' 
                    . $curr->nIDtxt . '">'; //  class="container"
            } else{
                if ($this->hasFrameLoad() 
                    || ($GLOBALS["SL"]->treeRow->tree_opts%3 == 0 
                    || $GLOBALS["SL"]->treeRow->tree_opts%17 == 0 
                    || $GLOBALS["SL"]->treeRow->tree_opts%41 == 0 
                    || $GLOBALS["SL"]->treeRow->tree_opts%43 == 0)) {
                    $ret .= '<div class="w100 pL15 pR15" id="treeWrap' . $curr->nIDtxt . '">';
                } else {
                    $ret .= '<div class="container" id="treeWrap' . $curr->nIDtxt . '">';
                }
            }
        }
        return $ret;
    }

    protected function nodePrintWrapNodeStart($curr)
    {
        $ret = '';
        // write the start of the main node wrapper
        if (!in_array($curr->nodeType, ['Layout Row', 'Layout Column'])) {
            $ret .= '<div id="node' . $curr->nIDtxt . '" class="nodeWrap' 
                . (($curr->isGraph()) ? ' nGraph' : '')
                . (($curr->nodeRow->node_opts%89 == 0) ? ' slCard' : '');
            if ($GLOBALS["SL"]->treeRow->tree_type == 'Page') {
                $ret .= (($curr->isInstructAny()) ? ' w100' : '') 
                    . (($curr->isPage()) ? ' h100' : '');
            }
            if ($curr->currVisib != 1 
                && (trim($GLOBALS["SL"]->currCyc["res"][1]) == '' 
                    || substr($GLOBALS["SL"]->currCyc["res"][1], 0, 3) != 'res')) {
                $ret .= ' disNon';
            }
            $ret .= '">' . "\n";
        }
        return $ret;
    }

    protected function nodePrintBlockStart(&$curr)
    {
        $ret = '';
        if ($this->hasParentType($curr->nID, 'Gallery Slider') 
            && isset($curr->colors["blockImg"]) 
            && trim($curr->colors["blockImg"]) != '') {
            $GLOBALS["SL"]->addPreloadImg($curr->colors["blockImg"]);
        }
        $dis = '';
        if ($this->hasParentType($curr->nID, 'Gallery Slider')) {
            if ($curr->nodeRow->node_parent_order == 0) {
                $dis = ' disBlo';
            } else {
                $dis = ' disNon';
            }
        }
        $ret .= view(
            'vendor.survloop.css.inc-block', 
            [ "curr"   => $curr ]
        )->render();
        $isParallax = (isset($curr->colors["blockImgFix"]) 
            && trim($curr->colors["blockImgFix"]) == 'P');
        $ret .= '<div id="blockWrap' . $curr->nIDtxt 
            . '" class="w100 page-break-avoid' . $dis;
        if ($isParallax) {
            $imgSrc = '';
            if (sizeof($curr->colors) > 0) {
                if (isset($curr->colors["blockImg"]) 
                    && trim($curr->colors["blockImg"]) != '') {
                    $GLOBALS["SL"]->pageAJAX .= "$('#blockWrap" 
                        . $curr->nIDtxt . "').parallax({imageSrc: '" 
                        . $curr->colors["blockImg"] . "'}); ";
                    $ret .= ' parallax-window';
                    //$ret .= ' parallax-window" data-parallax="scroll" ' 
                    //    . 'data-image-src="' . $curr->colors["blockImg"];
                }
            }
        }
        return $ret . '">';
    }

    protected function nodePrintLoadDateTime(&$curr)
    {
        $curr->dateStr = $curr->timeStr = '';
        if ($curr->fld != '' 
            && isset($GLOBALS["SL"]->tblAbbr[$curr->tbl]) 
            && $curr->fld != ($GLOBALS["SL"]->tblAbbr[$curr->tbl] . 'id')
            && !is_array($curr->sessData) 
            && trim($curr->sessData) != '' 
            && isset($GLOBALS["SL"]->fldTypes[$curr->tbl][$curr->fld])) {
            // convert current session data for dates and times
            if ($GLOBALS["SL"]->fldTypes[$curr->tbl][$curr->fld] == 'DATETIME') {
                list($curr->dateStr, $curr->timeStr) = explode(' ', $curr->sessData);
                $curr->dateStr = $this->cleanDateVal($curr->dateStr);
                if (trim($curr->dateStr) != '') {
                    $curr->dateStr = date("m/d/Y", strtotime($curr->dateStr));
                }
            } elseif ($GLOBALS["SL"]->fldTypes[$curr->tbl][$curr->fld] == 'DATE') {
                $curr->dateStr = $this->cleanDateVal($curr->sessData);
                if (trim($curr->dateStr) != '') {
                    $curr->dateStr = date("m/d/Y", strtotime($curr->dateStr));
                }
            }
            if ($curr->dateStr == '12/31/1969') {
                $curr->dateStr = '';
            }
        } // end normal data field checks
        return true;
    }

    protected function nodePrintOneLiner($curr)
    {
        $ret = '';
        // check if this field's label and field is to be printed on the same line
        $curr->isOneLiner = $curr->isOneLinerFld = '';
        if (in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
            if ($curr->isOneLiner()) {
                $curr->isOneLiner = ' disIn mR20';
            }
            if ($curr->isOneLiner() || $curr->isOneLineResponses()) {
                $curr->isOneLinerFld = ' disIn mR20';
            }
        } elseif ($curr->isOneLiner()) {
            $curr->isOneLiner = $curr->isOneLinerFld = ' col-6';
        }
        if (trim($curr->isOneLiner) != '') {
            $curr->nodePrompt = str_replace(
                'class="nPrompt"', 
                'class="nPrompt' . $curr->isOneLiner . '"', 
                $curr->nodePrompt
            );
        }
        // start Q&A on same row
        if ($curr->isOneLiner() 
            && !in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
            $ret .= '<div class="row"> <!-- start one-liner -->';
        }
        return $ret;
    }




}