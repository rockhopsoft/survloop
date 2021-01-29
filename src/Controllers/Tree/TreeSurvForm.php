<?php
/**
  * TreeSurvForm is the main class for Survloop's branching tree, capable of generating complex forms.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use RockHopSoft\Survloop\Controllers\Tree\TreeSurvSpreadsheet;

class TreeSurvForm extends TreeSurvSpreadsheet
{
    protected function customNodePrintWrap($nID, $bladeRender = '')
    {
        return $this->printNodePublicFormStart($nID) . $bladeRender 
            . $this->nodePrintButton($nID) . $this->printNodePublicFormEnd($nID)
            . '<div class="fC p20"></div>';
    }
    
    protected function customNodePrint(&$curr = null)
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

        // copy node object; load field info and current session data
        $this->allNodes[$nID]->fillNodeRow($nID);
        $this->allNodes[$nID]->getTblFld();
        $curr = $this->allNodes[$nID];
        // Copy commonly needed variables into current Node object
        $curr->nID        = $nID;
        $curr->nSffx      = $nSffx = $GLOBALS["SL"]->getCycSffx();
        $curr->nIDtxt     = $nIDtxt = trim($nID . $nSffx);
        $curr->tmpSubTier = $tmpSubTier;
        $curr->currVisib  = $currVisib;
        $this->printNodePublicInit($curr);
        if ($curr->isPage() || $curr->isLoopRoot()) {
            return $this->printNodePublicPageOrLoop($curr);
        }
        return $this->printNodePublicDefault($curr);
    }
    
    protected function printNodePublicDefault(&$curr)
    {
        $this->printNodePublicCurrData($curr);
        $this->printNodeCondKids($curr);
        $curr->printNodePublicResponses();
        $visibilityField = $this->printNodePublicVisibility($curr);
        $ret = $this->customNodePrint($curr);
        if ($curr->nodeType == 'Data Print Row' 
            && is_array($ret) 
            && sizeof($ret) > 0) {
            return $ret; 
        } elseif (!is_array($ret) && $ret != '') {
            return $visibilityField . $this->wrapNodePrint($ret, $curr->nID);
        }
        // else print standard node output...
        $ret = $visibilityField . $this->nodeSessDump($curr->nIDtxt, $curr->nID);
        $this->printNodePublicAddOns($curr);
        $promptNotesSpecial = $this->printNodePublicPrompts($curr);
        if ($curr->isDataPrint()) {
            return $this->nodePrintData($curr);
        } // else not data printing...
        $ret .= $this->nodePrintWrapStart($curr)
            . $this->printNodePublicInner($curr, $promptNotesSpecial)
            . $this->printNodePublicKids($curr)
            . $this->nodePrintWrapEnd($curr);
        if ($curr->isDataManip()) {
            $this->closeManipBranch($curr->nID);
        }
        $this->closePrintNodePublic($curr->nID, $curr->nIDtxt, $curr);
        return $this->wrapNodePrint($ret, $curr->nID);
    }
    
    protected function printNodePublicPageOrLoop(&$curr)
    {
        if ($curr->isPage()) {
            $extraOpts = $this->swapIDsSEO($curr->extraOpts);
            $GLOBALS['SL']->setSEO(
                $this->swapLabels($curr, $extraOpts["meta-title"]), 
                $this->swapLabels($curr, $extraOpts["meta-desc"]), 
                $this->swapLabels($curr, $extraOpts["meta-keywords"]), 
                $this->swapLabels($curr, $extraOpts["meta-img"])
            );
        }

        $ret = $this->customNodePrint($curr);
        if (!is_array($ret) && trim($ret) != '') {
            return $ret;
        }
        $this->checkLoopRootInput($curr->nID);
        // print the button, and form initialization 
        // which only happens once per page make sure 
        // these are reset, in case of redirect
        $this->pageJSvalid = $this->pageHasReqs = '';
        $this->pageHasUpload = $this->pageFldList = $this->hideKidNodes = [];
        $this->runPageExtra($curr->nID);
        $this->runPageLoad($curr->nID);
        if ($GLOBALS["SL"]->treeRow->tree_type != 'Page') {
            $ret .= '<div id="pageTopGapID" class="pageTopGap"></div>';
        }
        if ($curr->isLoopRoot()) {
            $desc = '';
            if (isset($curr->nodeRow->node_prompt_text) 
                && trim($curr->nodeRow->node_prompt_text) != '') {
                $desc = '<div id="loopRootPromptText' . $curr->nID 
                    . '" class="nPrompt loopRootPromptText">' 
                    . stripslashes($curr->nodeRow->node_prompt_text) 
                    . '</div>';
            }
            $ret .= $this->printSetLoopNav($curr->nID, $curr->defaultVal, $desc);
        } else { // isPage()
            if (sizeof($curr->tmpSubTier[1]) > 0) { // recurse deez!..
                foreach ($curr->tmpSubTier[1] as $cNode) { 
                    if (!$this->allNodes[$cNode[0]]->isPage()) {
                        $ret .= $this->printNodePublic(
                            $cNode[0], 
                            $cNode, 
                            $curr->currVisib
                        );
                    }
                } 
            }
        }
        $GLOBALS["SL"]->pageJAVA .= view(
            'vendor.survloop.forms.formtree-page-focus-java', 
            [
                "charLimit"    => $curr->nodeRow->node_char_limit,
                "page1stVisib" => $this->page1stVisib
            ]
        )->render();
        $gap = '';
        if ($GLOBALS["SL"]->treeRow->tree_type != 'Page') {
            $gap = '<div class="pageBotGap"></div>';
        }
        $id = '';
        if ($GLOBALS["SL"]->REQ->has('ajax')) {
            $id = 'n' . $curr->nID;
        }
        $btns = '';
        if (!$GLOBALS["SL"]->isPdfView()) {
            $btns = '<div id="pageBtns' . $id . '">'
                . '<div id="formErrorMsg' . $id . '"></div>' 
                . $this->nodePrintButton($curr->nID, $curr->tmpSubTier, '')
                . '</div>';
        }
        return $this->printNodePublicFormStart($curr->nID) . $ret . $btns 
            . $this->printNodePublicFormEnd($curr->nID, '') . $gap;
    }

    protected function printNodePublicInner(&$curr, $promptNotesSpecial)
    {
        $ret = '';
        $widgets = [
            'Search', 'Search Results', 'Search Featured', 
            'Record Full', 'Record Full Public', 'Record Previews', 
            'Incomplete Sess Check', 'Member Profile Basics', 
            'Plot Graph', 'Line Graph', 'Bar Graph', 'Pie Chart', 
            'Map', 'MFA Dialogue', 'Widget Custom'
        ];
        if ($curr->isLayout() || $curr->isBranch()) {
            // skip
        } elseif ($curr->isLoopCycle()) {
            $ret .= $this->nodePrintLoopCycle($curr);
        } elseif ($curr->isSpreadTbl()) {
            $ret .= $this->nodePrintSpreadsheet($curr);
        } elseif ($curr->isLoopSort()) {
            $ret .= $this->nodePrintLoopSort($curr);
        } elseif ($curr->isDataManip()) {
            $ret .= $this->nodePrintDataManip($curr);
        } elseif ($curr->nodeType == 'Back Next Buttons') {
            $ret .= view(
                'vendor.survloop.forms.inc-extra-back-next-buttons'
            )->render();
        } elseif (in_array($curr->nodeType, $widgets)) {
            $ret .= $this->nodePrintWidget($curr);
        } else { // otherwise, the main Node printer...
            $ret .= $this->printNodePublicElements($curr)
                . $this->printNodePublicAfterAddOns($curr);
            $this->printNodePublicAjaxKids($curr);
        } // end main Node printer
        if (trim($promptNotesSpecial) != '') {
            $ret .= $this->printSpecial($curr, $promptNotesSpecial);
        }
        $ret .= $curr->nodePromptAfter;
        if ($this->shouldPrintHalfGap($curr)) {
            $ret .= '<div class="nodeHalfGap"></div>';
        }
        return $ret;
    }

    protected function printNodePublicKids(&$curr)
    {
        $ret = '';
        if (sizeof($curr->tmpSubTier[1]) > 0 
            && !$curr->isLoopCycle() 
            && !$curr->isSpreadTbl()) {
            if ($curr->nodeType == 'Big Button' 
                && $curr->nodeRow->node_opts%43 == 0) {
                $curr->currVisib = 0;
            }
            $class = $this->printNodePublicKidsClass($curr);
            $ret .= '<div id="node' . $curr->nIDtxt . 'kids" class="' . $class . '">';
            if ($curr->isGraph()) {
                $this->v["graphFilters"] = $curr->nID;
            }
            foreach ($curr->tmpSubTier[1] as $child) { // recurse deez!..
                if (!$this->allNodes[$child[0]]->isPage() 
                    && $this->allNodes[$child[0]]->nodeType != 'Layout Sub-Response') {
                    $kid = $this->printNodePublic($child[0], $child, $curr->currVisib);
                    if (!is_array($kid)) {
                        $ret .= $kid;
                    } else {
                        $ret .= implode(' ', $kid);
                    }
                }
            }
            if ($curr->isGraph()) {
                $this->v["graphFilters"] = false;
            }
            $ret .= '</div> <!-- end #node' . $curr->nIDtxt . 'kids -->';
        }
        return $ret;
    }

    protected function printNodePublicKidsClass($curr)
    {
        $class = '';
        if ($curr->currVisib == 0) {
            $class .= 'disNon';
        } else {
            if ($curr->nodeType == 'Layout Row') {
                $class .= 'disFlx row';
            } else {
                $class .= 'disBlo';
            }
            if ($curr->nodeType == 'Gallery Slider') {
                $class .= ' h50 ovrNo';
            }
        }
        return $class;
    }
    
    protected function printNodePublicElements(&$curr)
    {
        $chk = ['Radio', 'Checkbox', 'Instructions', 'Other/Custom'];
        if (!in_array($curr->nodeType, $chk)) {
            $this->pageFldList[] = 'n' . $curr->nID . 'FldID';
        }
        $this->checkResponses($curr, $this->v["fldForeignTbl"]);
        $this->customResponses($curr);
        $this->nodePrintLoadDateTime($curr);
        $ret = $this->nodePrintOneLiner($curr);
        switch ($curr->nodeType) {
            case 'Hidden Field':
                $ret .= $this->nodePrintHiddenField($curr); 
                break;
            case 'Big Button':
                $ret .= $this->nodePrintBigButton($curr);
                break;
            case 'User Sign Up':
                $ret .= $this->nodePrintSignUp($curr);
                break;
            case 'Text':
            case 'Email':
            case 'Spambot Honey Pot':
                $ret .= $this->nodePrintTextField($curr);
                break;
            case 'Long Text':
                $ret .= $this->nodePrintTextareaField($curr);
                break;
            case 'Text:Number':
            case 'Slider':
                $ret .= $this->nodePrintNumberField($curr);
                break;
            case 'Password':
                $ret .= $this->nodePrintPasswordField($curr);
                break;
            case 'Drop Down':
            case 'U.S. States':
            case 'Countries':
                $ret .= $this->nodePrintDropdown($curr);
                break;
            case 'Radio':
            case 'Checkbox':
                $ret .= $this->printNodePublicCheckboxes($curr);
                break;
            case 'Date':
                $ret .= $this->nodePrintDate($curr);
                break;
            case 'Date Picker':
                $ret .= $this->nodePrintDatePicker($curr);
                break;
            case 'Time':
                $ret .= $this->nodePrintTime($curr);
                break;
            case 'Date Time':
                $ret .= $this->nodePrintDateTime($curr);
                break;
            case 'Feet Inches':
                $ret .= $this->nodePrintFeetInch($curr);
                break;
            case 'Gender':
            case 'Gender Not Sure':
                $ret .= $this->nodePrintGender($curr);
                break;
            case 'Uploads':
                $ret .= $this->nodePrintUploads($curr);
                break;
            default: // instruction only
                $ret .= "\n" . $curr->nodePrompt . "\n";
                break;
        }
        return $ret;
    }
    
    protected function printNodePublicAjaxKids(&$curr)
    {
        if ($curr->hasShowKids 
            && isset($this->kidMaps[$curr->nID]) 
            && sizeof($this->kidMaps[$curr->nID]) > 0) {
            if (!isset($this->v["nodeKidFunks"])) {
                $this->v["nodeKidFunks"] = '';
            }
            $this->v["nodeKidFunks"] .= 'checkNodeKids' . $curr->nIDtxt . '(); ';
            $GLOBALS["SL"]->pageAJAX .= 'function checkNodeKids' 
                . $curr->nIDtxt . '() { var showKids = false; ';
            foreach ($this->kidMaps[$curr->nID] as $nKid => $ress) {
                $nKidTxt = trim($nKid . $curr->nSffx);
                if ($ress && sizeof($ress) > 0) {
                    $if = $this->getJsShowKidsIf($ress, $curr->nIDtxt, $curr->nodeType);
                    $grankids = [];
                    if (isset($showMoreNodes[$nKid]) 
                        && sizeof($showMoreNodes[$nKid]) > 0) {
                        $grankids = $showMoreNodes[$nKid];
                    }
                    $GLOBALS["SL"]->pageAJAX .= view(
                        'vendor.survloop.forms.formtree-show-kids-ajax', 
                        [
                            "curr"     => $curr,
                            "nKid"     => $nKid,
                            "ress"     => $ress,
                            "grankids" => $grankids,
                            "if"       => $if
                        ]
                    )->render();
                }
            }
            $GLOBALS["SL"]->pageAJAX .= view(
                'vendor.survloop.forms.formtree-show-kids-close-ajax', 
                [ "curr" => $curr ]
            )->render();
        }
        return true;
    }
    
    protected function printNodePublicAfterAddOns(&$curr)
    {
        $ret = '';
        // end Q&A on same row
        if ($curr->isOneLiner() 
            && !in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
            $ret .= '</div> <!-- end one-liner -->';
        }
        // copy input to extra div?
        if (in_array($curr->nodeType, ['Text', 'Text:Number']) 
            && $curr->nodeRow->node_opts%41 == 0) { 
            $GLOBALS["SL"]->pageJAVA .= ' setTimeout("copyNodeResponse(\'n' 
                . $curr->nIDtxt . 'FldID\', \'nodeEcho' 
                . $curr->nIDtxt . '\')", 50); ';
            $GLOBALS["SL"]->pageAJAX .= ' $("#n' . $curr->nIDtxt 
                . 'FldID").keyup(function() { copyNodeResponse(\'n' 
                . $curr->nIDtxt . 'FldID\', \'nodeEcho' 
                . $curr->nIDtxt . '\'); }); ' . "\n";
        }
        return $ret;
    }

    
} // end of TreeSurvForm class
