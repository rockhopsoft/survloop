<?php
/**
  * TreeSurvFormElements is a mid-level class in the branching tree, which provides
  * less complicated elements used during output generation.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.13
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use RockHopSoft\Survloop\Controllers\Tree\TreeSurvFormUtils;

class TreeSurvFormElements extends TreeSurvFormUtils
{
    protected function nodePrintDataManip($curr)
    {
        $ret = '<input type="hidden" name="dataManip' 
            . $curr->nIDtxt . '" value="1">';
        if ($curr->currVisib == 1) { // run a thing on page load
            if ($curr->nodeType == 'Data Manip: Close Sess') {
                $this->deactivateSess($curr->nodeRow->node_response_set);
            }
        }
        return $ret;
    }

    protected function nodePrintHiddenField($curr)
    {
        return $curr->nodePrompt . '<input type="hidden" name="n' 
            . $curr->nIDtxt . 'fld" id="n' . $curr->nIDtxt 
            . 'FldID" value="' . $curr->sessData 
            . '" class="' . $curr->xtraClass . '">' . "\n"; 
    }

    protected function nodePrintTextField($curr)
    {
        $ret = $curr->nodePrompt . '<div class="nFld' . $curr->isOneLinerFld . '">'
            . '<input class="form-control form-control-lg' . $curr->xtraClass 
            . '" type="' . (($curr->nodeType == 'Email') ? 'email' : 'text') 
            . '" name="n' . $curr->nIDtxt . 'fld" id="n' . $curr->nIDtxt 
            . 'FldID" value="' . $curr->sessData . '" ' . $curr->onKeyUp 
            . ' data-nid="' . $curr->nID . '" ' . $GLOBALS["SL"]->tabInd() 
            . '></div>' . $curr->charLimit . "\n" 
            . $this->printWordCntStuff($curr->nIDtxt, $curr->nodeRow);
        if ($curr->isRequired()) {
            if ($curr->nodeType == 'Email') {
                $this->pageJSvalid .= "addReqNode('" 
                    . $curr->nIDtxt . "', 'reqFormFldEmail');\n";
            } else {
                $this->pageJSvalid .= "addReqNode('" 
                    . $curr->nIDtxt . "', 'reqFormFld');\n";
            }
        }
        if ($curr->nodeType == 'Spambot Honey Pot') {
            $GLOBALS["SL"]->pageJAVA .= 'document.getElementById("node"+"' 
                . $curr->nID . '").style.display="none"; ';
        }
        if (trim($curr->nodeRow->node_text_suggest) != '') {
            $GLOBALS["SL"]->pageAJAX .= '$( "#n' . $curr->nIDtxt 
                . 'FldID" ).autocomplete({ source: [';
            $defSet = $GLOBALS["SL"]->def->getSet($curr->nodeRow->node_text_suggest);
            foreach ($defSet as $i => $def) {
                $GLOBALS["SL"]->pageAJAX .= (($i > 0) ? ',' : '') 
                    . ' ' . json_encode($def->def_value);
            }
            $GLOBALS["SL"]->pageAJAX .= ' ] });' . "\n";
        }
        return $ret;
    }

    protected function nodePrintTextareaField($curr)
    {
        $ret = $curr->nodePrompt . '<div class="nFld' . $curr->isOneLinerFld
            . '"><textarea class="form-control form-control-lg flexarea' 
            . $curr->xtraClass . '" name="n' . $curr->nIDtxt . 'fld" id="n' 
            . $curr->nIDtxt . 'FldID" ' . $curr->onKeyUp . ' data-nid="' 
            . $curr->nID . '" ' . $GLOBALS["SL"]->tabInd() . '>' 
            . $curr->sessData . '</textarea></div>' . $curr->charLimit . "\n" 
            . $this->printWordCntStuff($curr->nIDtxt, $curr->nodeRow);
        //$GLOBALS["SL"]->pageJAVA .= "\n" . 'setTimeout("flexAreaAdjust(document.getElementById(\'n' 
        //    . $curr->nIDtxt . 'FldID\'))", 50);';
        if ($curr->isRequired()) {
            $this->pageJSvalid .= "addReqNode('" . $curr->nIDtxt 
                . "', 'reqFormFld');\n";
        }
        return $ret;
    }

    protected function nodePrintNumberField($curr)
    {
        $ret = $curr->nodePrompt;
        if ($curr->chkCurrOpt('MONTHCALC')) {
            $presel = $this->monthlyCalcPreselections($curr->nID, $curr->nIDtxt);
            $ret .= $this->printMonthlyCalculator($curr->nIDtxt, $presel);
        }
        $ret .= '<div class="nFld' . $curr->isOneLinerFld . '">';
        if (!$this->hasSpreadsheetParent($curr->nID)) {
            $ret .= '<div class="row"><div class="col-6">';
        }
        $unitCls = 'w100 ';
        //if (isset($curr->extraOpts["unit"]) 
        //    && trim($curr->extraOpts["unit"]) != '') {
        //    $unitCls = 'unitFld ';
        //}
        $ret .= '<nobr><input type="number" data-nid="' . $curr->nID 
            . '" class="form-control form-control-lg ' 
            . (($curr->nodeType == 'Slider') ? 'slidePercFld ' : $unitCls)
            . $curr->xtraClass . '" name="n' . $curr->nIDtxt . 'fld" id="n' 
            . $curr->nIDtxt . 'FldID" value="' . $curr->sessData . '" '
            . $curr->onKeyUp . ' ' . $this->nodePrintNumberFieldMinMax($curr)
            . $GLOBALS["SL"]->tabInd() . '> ';
        if (isset($curr->extraOpts["unit"]) 
            && trim($curr->extraOpts["unit"]) != '') {
            if ($curr->nodeType == 'Text:Number' 
                && !$this->hasSpreadsheetParent($curr->nID)) {
                $ret .= '</nobr></div><div class="col-6 pT10"><nobr>';
            }
            $ret .= $this->nodePrintNumberFldUnitSwap($curr);
        }
        $ret .= '</nobr>';
        if (!$this->hasSpreadsheetParent($curr->nID)) {
            $ret .= '</div></div>';
        }
        $ret .= $this->nodePrintNumberSlider($curr) . '</div>' . "\n";
        $this->nodePrintNumberFieldReqs($curr);
        return $ret;
    }

    protected function nodePrintNumberFldUnitSwap($curr)
    {
        if (isset($curr->extraOpts["unit"])) {
            return trim($curr->extraOpts["unit"]);
        }
        return '';
    }

    protected function nodePrintNumberFieldMinMax($curr)
    {
        $attrMin = $attrMax = '';
        if (isset($curr->extraOpts["minVal"]) 
            && $curr->extraOpts["minVal"] !== false) {
            if (isset($curr->nodeRow->node_default) 
                && $curr->nodeRow->node_default < $curr->extraOpts["minVal"]) {
                $attrMin = 'min="' . $curr->nodeRow->node_default . '" ';
            } else {
                $attrMin = 'min="' . $curr->extraOpts["minVal"] . '" ';
            }
        }
        if (isset($curr->extraOpts["maxVal"]) 
            && $curr->extraOpts["maxVal"] !== false) {
            if (isset($curr->nodeRow->node_default) 
                && $curr->nodeRow->node_default > $curr->extraOpts["maxVal"]) {
                $attrMax = 'max="' . $curr->nodeRow->node_default . '" ';
            } else {
                $attrMax = 'max="' . $curr->extraOpts["maxVal"] . '" ';
            }
        }
        $attrIncr = 'step="any" ';
        if (isset($curr->extraOpts["incr"]) 
            && $curr->extraOpts["incr"] > 0) {
            $attrIncr = 'step="' . $curr->extraOpts["incr"] . '" ';
        }
        return $attrMin . $attrMax . $attrIncr;
    }

    protected function nodePrintNumberSlider($curr)
    {
        $ret = '';
        if ($curr->nodeType == 'Slider') {
            $ret .= '<div class="col-10 slideCol"><div id="n' . $curr->nIDtxt 
                . 'slider" class="ui-slider ui-slider-horizontal slSlider">'
                . '</div></div>';
            $GLOBALS["SL"]->pageAJAX .= view(
                'vendor.survloop.forms.formtree-number-slider-ajax', 
                [
                    "nIDtxt" => $curr->nIDtxt,
                    "curr"   => $curr
                ]
            )->render();
        }
        return $ret;
    }

    protected function nodePrintNumberFieldReqs($curr)
    {
        if ($curr->isRequired()) {
            if ((!isset($curr->extraOpts["minVal"]) 
                    || $curr->extraOpts["minVal"] === false)
                && (!isset($curr->extraOpts["maxVal"]) 
                    || $curr->extraOpts["maxVal"] === false)) {
                $this->pageJSvalid .= "addReqNode('" 
                    . $curr->nIDtxt . "', 'reqFormFld');\n";
            } else {
                if (isset($curr->extraOpts["minVal"]) 
                    && $curr->extraOpts["minVal"] !== false) {
                    $this->pageJSvalid .= "addReqNodeRadio('" 
                        . $curr->nIDtxt . "', 'reqFormFldGreater', " 
                        . $curr->extraOpts["minVal"] . ");\n";
                }
                if (isset($curr->extraOpts["maxVal"]) 
                    && $curr->extraOpts["maxVal"] !== false) {
                    $this->pageJSvalid .= "addReqNodeRadio('" 
                        . $curr->nIDtxt . "', 'reqFormFldLesser', " 
                        . $curr->extraOpts["maxVal"] . ");\n";
                }
            }
        }
        return true;
    }

    protected function nodePrintPasswordField($curr)
    {
        $ret = $curr->nodePrompt . '<div class="nFld' . $curr->isOneLinerFld 
            . '"><input type="password" name="n' . $curr->nIDtxt . 'fld" id="n' 
            . $curr->nIDtxt . 'FldID" value="" ' . $curr->onKeyUp 
            . ' autocomplete="off" class="form-control form-control-lg' 
            . $curr->xtraClass . '" data-nid="' . $curr->nID . '"' 
            . $GLOBALS["SL"]->tabInd() . '></div>' . $curr->charLimit . "\n"; 
        if ($curr->isRequired()) {
            $this->pageJSvalid .= "addReqNode('" . $curr->nIDtxt 
                . "', 'reqFormFld');\n";
        }
        return $ret;
    }

    protected function nodePrintDropdown($curr)
    {
        $ret = '';
        $this->checkResponses($curr, $this->v["fldForeignTbl"]);
        if (sizeof($curr->responses) > 0 
            || in_array($curr->nodeType, ['U.S. States', 'Countries'])) {
            if ($curr->isOneLinerFld != '') {
                $curr->xtraClass .= ' w33';
            }
            if ($curr->isDropdownTagger()) {
                $curr->onChange = ' onChange="selectTag(\'' . $curr->nIDtxt 
                    . '\', this.value); this.value=\'\';" ';
            }
            $ret .= view(
                'vendor.survloop.forms.formtree-dropdown-start', 
                [ "curr" => $curr ]
            )->render();
            if ($curr->nodeType == 'U.S. States' 
                && !$curr->isDropdownTagger()) {
                $GLOBALS["SL"]->loadStates();
                $ret .= $GLOBALS["SL"]->states->stateDrop($curr->sessData);
            } else {
                $ret .= $this->nodePrintDropdownOpts($curr);
            }
            if ($curr->isRequired()) {
                $this->pageJSvalid .= "addReqNode('" . $curr->nIDtxt 
                    . "', 'reqFormFld');\n";
            }
            $ret .= '</select></div>' . "\n" 
                . $this->nodePrintDropdownTagger($curr); 
        }
        return $ret;
    }

    protected function nodePrintDropdownOpts($curr)
    {
        $ret = '';
        foreach ($curr->responses as $j => $res) {
            $val = (($curr->nodeType == 'U.S. States') 
                ? (1+$j) : $res->node_res_value);
            $select = $this->isCurrDataSelected($curr, $res->node_res_value);
            $ret .= '<option value="' . $val . '" ' 
                . (($select && !$curr->isDropdownTagger()) 
                    ? 'SELECTED' : '')
                . ' >' . $res->node_res_eng . '</option>' . "\n";
            if ($curr->isDropdownTagger()) {
                $GLOBALS["SL"]->pageJAVA .= "\n" 
                    . 'addTagOpt(\'' . $curr->nIDtxt . '\', ' 
                    . json_encode($val) . ', ' 
                    . json_encode($res->node_res_eng) . ', ' 
                    . (($select) ? 1 : 0) . ');';
            }
        }
        return $ret;
    }

    protected function nodePrintDropdownTagger($curr)
    {
        $ret = '';
        if ($curr->isDropdownTagger()) {
            $ret .= view(
                'vendor.survloop.forms.formtree-dropdown-tagger', 
                [ "curr" => $curr ]
            )->render();
            $GLOBALS["SL"]->pageJAVA .= ' setTimeout("updateTagList(\''
                . $curr->nIDtxt . '\')", 50); ';
        }
        return $ret;
    }

    protected function nodePrintDate($curr)
    {
        if ($this->nodeHasDateRestriction($curr->nodeRow)) {
            if ($curr->isRequired()) {
                $this->pageJSvalid .= "addReqNodeDateLimit('" . $curr->nIDtxt 
                    . "', 'reqFormFldDate" . (($curr->isRequired()) ? "And" : "") 
                    . "Limit', " . $curr->nodeRow->node_char_limit . ", '" 
                    . date("Y-m-d") . "', 1);\n";
                
            }
        } elseif ($curr->isRequired()) {
            $this->pageJSvalid .= "addReqNode('" . $curr->nIDtxt 
                . "', 'reqFormFldDate');\n";
        }
        return $curr->nodePrompt . '<div class="nFld' . $curr->isOneLinerFld 
            . '">' . $this->formDate($curr) . '</div>' . "\n";
    }

    protected function nodePrintDatePicker($curr)
    {
        $GLOBALS["SL"]->pageAJAX .= '$( "#n' . $curr->nIDtxt 
            . 'FldID" ).datepicker({ maxDate: "+0d" });';
        if ($curr->isRequired()) {
            $this->pageJSvalid .= "addReqNode('" . $curr->nIDtxt 
                . "', 'reqFormFld');\n";
        }
        return view(
            'vendor.survloop.forms.formtree-date-picker', 
            [ "curr" => $curr ]
        )->render();
    }

    protected function nodePrintTime($curr)
    {
        $this->pageFldList[] = 'n' . $curr->nIDtxt . 'fldHrID'; 
        $this->pageFldList[] = 'n' . $curr->nIDtxt . 'fldMinID';
        $l = '</label>';
        $prompt = str_replace($l, $l . $l . $l, $curr->nodePrompt);
        $prompt = str_replace(
            '<label for="n' . $curr->nIDtxt . 'FldID">', 
            '<label for="n' . $curr->nIDtxt . 'fldHrID"><label for="n' 
                . $curr->nIDtxt . 'fldMinID"><label for="n' 
                . $curr->nIDtxt . 'fldPMID">', 
            $prompt
        );
        if ($curr->isRequired()) {
            $this->pageJSvalid .= "addReqNode('" . $nIDtxt 
                . "', 'reqFormFld');\n";
        }
        return $prompt . '<div class="nFld' . $curr->isOneLinerFld 
            . '">' . $this->formTime($curr) . '</div>' . "\n";
    }

    protected function nodePrintDateTime($curr)
    {
        $GLOBALS["SL"]->pageAJAX .= '$( "#n' . $curr->nIDtxt 
            . 'FldID" ).datepicker({ maxDate: "+0d" });';
        $curr->nodePrompt = str_replace(
            '</label>', 
            '</label></label></label></label>', 
            $curr->nodePrompt
        );
        $newLabels = '<label for="n' . $curr->nIDtxt 
            . 'FldID"><label for="n' . $curr->nIDtxt 
            . 'fldHrID"><label for="n' . $curr->nIDtxt 
            . 'fldMinID"><label for="n' . $curr->nIDtxt . 'fldPMID">';
        $curr->nodePrompt = str_replace(
            '<label for="n' . $curr->nIDtxt . 'FldID">', 
            $newLabels, 
            $curr->nodePrompt
        );
        $this->pageFldList[] = 'n' . $curr->nIDtxt . 'FldID'; 
        $this->pageFldList[] = 'n' . $curr->nIDtxt . 'fldHrID'; 
        $this->pageFldList[] = 'n' . $curr->nIDtxt . 'fldMinID';
        if ($curr->isRequired()) {
            $this->pageJSvalid .= "addReqNode('" . $curr->nIDtxt 
                . "', 'reqFormFld');\n";
        }
        return view(
            'vendor.survloop.forms.formtree-datetime', 
            [
                "curr"           => $curr,
                "inputMobileCls" => $this->inputMobileCls($curr->nID),
                "formTime"       => $this->formTime($curr)
            ]
        )->render();
    }

    protected function formTime($curr)
    {
        if (strlen($curr->timeStr) == 19) {
            $curr->timeStr = substr($curr->timeStr, 11);
        }
        $timeArr = explode(':', $curr->timeStr); 
        foreach ($timeArr as $i => $t) {
            $timeArr[$i] = intVal($timeArr[$i]);
        }
        if (!isset($timeArr[0])) {
            $timeArr[0] = 0;
            if (!isset($timeArr[1])) {
                $timeArr[1] = 0;
            }
        }
        $timeArr[3] = 'AM';
        if ($timeArr[0] > 11) {
            $timeArr[3] = 'PM'; 
            if ($timeArr[0] > 12) {
                $timeArr[0] = $timeArr[0]-12;
            }
        }
        if ($timeArr[0] == 0 
            && (!isset($timeArr[1]) || $timeArr[1] == 0)) {
            $timeArr[0] = -1; 
            $timeArr[1] = 0; 
        }
        return view(
            'vendor.survloop.forms.formtree-time', 
            [
                "nID"            => $curr->nID,
                "timeArr"        => $timeArr,
                "xtraClass"      => $curr->xtraClass,
                "inputMobileCls" => $this->inputMobileCls($curr->nID)
            ]
        )->render();
    }
    
    protected function postFormTimeStr($nID)
    {
        $nIDtxt = $nID . $GLOBALS["SL"]->getCycSffx();
        if (!$GLOBALS["SL"]->REQ->has('n' . $nIDtxt . 'fldHr') 
            || trim($GLOBALS["SL"]->REQ->input('n' . $nIDtxt . 'fldHr')) == '-1') {
            return null;
        }
        $hr = intVal($GLOBALS["SL"]->REQ->input('n' . $nIDtxt . 'fldHr'));
        if ($hr == -1) {
            return null;
        }
        if ($GLOBALS["SL"]->REQ->input('n' . $nIDtxt . 'fldPM') == 'PM' && $hr < 12) {
            $hr += 12;
        }
        $min = intVal($GLOBALS["SL"]->REQ->input('n' . $nIDtxt . 'fldMin'));
        return ((intVal($hr) < 10) ? '0' : '') . $hr . ':' 
            . ((intVal($min) < 10) ? '0' : '') . $min . ':00';
    }

    protected function nodePrintFeetInch($curr)
    {
        $this->pageFldList[] = 'n' . $curr->nIDtxt . 'fldFeetID'; 
        $this->pageFldList[] = 'n' . $curr->nIDtxt . 'fldInchID';
        $feet = ($curr->sessData > 0) ? floor($curr->sessData/12) : 0; 
        $inch = ($curr->sessData > 0) ? intVal($curr->sessData)%12 : 0;
        $curr->nodePrompt = str_replace(
            '</label>', 
            '</label></label>', 
            $curr->nodePrompt
        );
        $curr->nodePrompt = str_replace(
            '<label for="n' . $curr->nIDtxt . 'FldID">', 
            '<label for="n' . $curr->nIDtxt . 'fldFeetID"><label for="n' 
                . $curr->nIDtxt . 'fldInchID">', 
            $curr->nodePrompt
        );
        if ($curr->isRequired()) {
            $this->pageJSvalid .= "addReqNode('" . $curr->nIDtxt 
                . "', 'reqFormFeetInches');\n";
        }
        return view(
            'vendor.survloop.forms.formtree-feetinch', 
            [
                "curr"           => $curr,
                "feet"           => $feet,
                "inch"           => $inch,
                "inputMobileCls" => $this->inputMobileCls($curr->nID)
            ]
        )->render();
    }

    protected function nodePrintBigButton($curr)
    {
        $ret = $onClick = $curr->sessData = '';
        if (trim($curr->nodeRow->node_data_store) != '') {
            $onClick = 'onClick="' . $curr->nodeRow->node_data_store . '"';
        }
        $btn = '<div class="nFld"><a id="nBtn' . $curr->nIDtxt 
            . '" ' . $onClick . ' class="crsrPntr ' 
            . $this->nodePrintBigButtonType($curr) . '" >' 
            . $curr->nodeRow->node_default . '</a></div>';
        $lastDivPos = strrpos($curr->nodePrompt, "</div>\n\t\t</label></div>");
        if (strpos($curr->nodePrompt, 'jumbotron') > 0 && $lastDivPos > 0) {
            $ret .= substr($curr->nodePrompt, 0, $lastDivPos) . '<center>' 
                . $btn . '</center>' . substr($curr->nodePrompt, $lastDivPos) 
                . '<input type="hidden" name="n' . $curr->nIDtxt . 'fld" id="n' 
                . $curr->nIDtxt . 'FldID" value="' . $curr->sessData . '" class="' 
                . $curr->xtraClass . '" data-nid="' . $curr->nID . '">' . "\n"; 
        } else {
            $ret .= $curr->nodePrompt . '<input type="hidden" name="n' 
                . $curr->nIDtxt . 'fld" id="n' . $curr->nIDtxt . 'FldID" value="' 
                . $curr->sessData . '" class="' . $curr->xtraClass . '" data-nid="' 
                . $curr->nID . '">' . $btn . "\n"; 
        }
        if ($curr->nodeRow->node_opts%43 == 0) {
            $this->allNodes[$curr->nID]->hasShowKids = $curr->hasShowKids = true;
            $childList = [];
            if (sizeof($curr->tmpSubTier[1]) > 0) {
                foreach ($curr->tmpSubTier[1] as $childNode) {
                    $childList[] = $childNode[0];
                    $this->hideKidNodes[] = $childNode[0];
                }
            }
            $this->v["javaNodes"] .= 'nodeKidList[' . $curr->nID 
                . '] = [' . implode(', ', $childList) . ']; ';
            $GLOBALS["SL"]->pageAJAX .= view(
                'vendor.survloop.forms.formtree-button-show-kids-ajax', 
                [
                    "nIDtxt" => $curr->nIDtxt,
                    "nSffx"  => $curr->nSffx
                ]
            )->render();
        }
        return $ret;
    }

    protected function nodePrintBigButtonType($curr)
    {
        $btnType = '';
        if ($curr->nodeRow->node_response_set != 'Text') {
            $btnType = 'primary';
            if ($curr->nodeRow->node_response_set == 'Default') {
                $btnType = 'secondary';
            }
            $btnType = 'btn btn-lg btn-' . $btnType . ' nFldBtn';
        }
        if ($curr->nodeRow->node_opts%43 >= 0) {
            $btnType .= ' nFormNext';
        }
        return $btnType;
    }


}