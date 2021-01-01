<?php
/**
  * TreeSurvFormCheckboxes is a mid-level class which provides management of
  * checkboxes, radio buttons, etc.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.13
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use RockHopSoft\Survloop\Controllers\Tree\TreeSurvFormPrintLoad;

class TreeSurvFormCheckboxes extends TreeSurvFormPrintLoad
{
    protected function printNodePublicCheckboxes(&$curr)
    {
        $ret = '';
        if ($curr->nodeType == 'Radio') {
            $ret .= '<input type="hidden" name="n' . $curr->nIDtxt 
                . 'radioCurr" id="n' . $curr->nIDtxt . 'radioCurrID" value="';
            if (!is_array($curr->sessData)) {
                $ret .= $curr->sessData;
            } elseif (sizeof($curr->sessData) > 0) {
                $cyc = trim($GLOBALS["SL"]->currCyc["cyc"][1]);
                foreach ($curr->sessData as $d) {
                    if (strpos($d, $cyc) === 0) {
                        $ret .= $d;
                    }
                }
            }
            $ret .= '">';
            $GLOBALS["SL"]->pageJAVA .= "\n" . 'addRadioNode(' . $curr->nID . ');';
        }
        if (sizeof($curr->responses) > 0) {
            $mobileChk = ($curr->nodeRow->node_opts%2 > 0);
            $ret .= (($curr->isOneLiner()) ? '<div class="pB20">' : '') 
                . str_replace('<label for="n' . $curr->nIDtxt . 'FldID">', '', 
                    str_replace('<label for="n' . $curr->nIDtxt . 'FldID" >', '', 
                    str_replace('</label>', '', $curr->nodePrompt)))
                . '<div class="nFld';
            if ($this->hasSpreadsheetParent($curr->nID)) {
                $ret .= '">' . "\n";
            } elseif ($mobileChk) {
                $ret .= '" style="margin-top: 12px;">' . "\n";
            } else {
                $ret .= $curr->isOneLiner . ' pB0 mBn5">' . "\n";
            }
            
                // onClick="return check' . $nID . 'Kids();"
            $curr->onChange = '';
            $GLOBALS["SL"]->pageJAVA .= "\n" . 'addResTot("' 
                . $curr->nID . '", ' . sizeof($curr->responses) . ');'; /// txt?
            if ($curr->nodeRow->node_opts%79 == 0) {
                $curr->onChange = 'chkRadioHide(\'' . $curr->nIDtxt . '\'); ';
                $GLOBALS["SL"]->pageJAVA .= "\n" . 'setTimeout("' 
                    . $curr->onChange . '", 100);';
            }
            if ($curr->nodeRow->node_opts%61 == 0) {
                $ret .= '<div class="row">';
                $mobileChk = true;
            }
            foreach ($curr->responses as $j => $res) {
                $ret .= $this->printNodeCheckboxResponse($curr, $j, $res, $mobileChk);
            }
            $ret .= $this->printNodeCheckboxesEnd($curr);
        }
        return $ret;
    }

    protected function printNodeCheckboxesEnd(&$curr)
    {
        $ret = '';
        if ($curr->isRequired()) {
            $this->pageJSvalid .= " addReqNodeRadio('" . $curr->nIDtxt 
                . "', 'reqFormFldRadio', " . sizeof($curr->responses) . "); ";
        }
        if ($curr->nodeRow->node_opts%61 == 0) {
            $ret .= '</div> <!-- end row -->';
        }
        if ($curr->nodeRow->node_opts%79 == 0) {
            $ret .= view(
                'vendor.survloop.forms.formtree-checkbox-unhide', 
                [ "curr" => $curr ]
            )->render();
        }
        $ret .= '</div>' . (($curr->isOneLiner()) ? '</div>' : '') . "\n"; 
        return $ret;
    }

    protected function printNodeCheckboxResponse($curr, $j, $res, $mobileChk)
    {
        $ret = '';
        $val = $res->node_res_value;
        if ($curr->hasShowKids) {
            $curr->xtraClass .= ' n' . $curr->nIDtxt . 'fldCls';
        }
        $respKids = ' data-nid="' . $curr->nID . '" class="nCbox' 
            . $curr->nID . ' ' . $curr->xtraClass . '"'; 
        $otherFld = $this->printNodeCheckboxOther($curr, $j, $res, $val);
        
        if ($curr->nodeType == 'Checkbox' && $curr->indexMutEx($j)) {
            $GLOBALS["SL"]->pageJAVA .= ' addMutEx("' . $curr->nIDtxt . '", ' . $j . '); ';
        }
        $this->pageFldList[] = 'n' . $curr->nIDtxt . 'fld' . $j;
        $resNameCheck = '';
        $boxChecked = $this->isCurrDataSelected($curr, $res->node_res_value);
    /// Somewhere 'round here
        if ($curr->nodeType == 'Radio') {
            $resNameCheck = 'name="n' . $curr->nIDtxt . 'fld" ' 
                . (($boxChecked) ? 'CHECKED' : '');
            if (sizeof($curr->fldHasOther) > 0 && $otherFld[1] == '') {
                $otherFld[3] = ' if (document.getElementById(\'n' 
                    . $curr->nIDtxt . 'fldOtherID' . $j . '\')) { document.getElementById(\'n' 
                    . $curr->nIDtxt . 'fldOtherID' . $j . '\').value=\'\'; } ';
            }
        } else {
            $resNameCheck = 'name="n' . $curr->nIDtxt . 'fld[]" ' 
                . (($boxChecked) ? 'CHECKED' : '');
        }
        
        if ($curr->nodeRow->node_opts%61 == 0) {
            $cols = $GLOBALS["SL"]->getColsWidth(sizeof($curr->responses));
            $ret .= '<div class="col-' . $cols . '">';
        }

        $onClickFull = trim($otherFld[3] . $curr->onChange);
        if ($onClickFull != '') {
            $onClickFull = ' onClick="' . $onClickFull . '" ';
        }
        $ret .= view(
            'vendor.survloop.forms.formtree-checkbox', 
            [
                "curr"           => $curr,
                "j"              => $j,
                "res"            => $res,
                "mobileCheckbox" => $mobileChk,
                "resNameCheck"   => $resNameCheck,
                "onClickFull"    => $onClickFull,
                "respKids"       => $respKids,
                "otherFld"       => $otherFld,
                "boxChecked"     => $boxChecked
            ]
        )->render();

        if ($curr->nodeRow->node_opts%61 == 0) {
            $ret .= '</div> <!-- end col -->' . "\n";
        }
        // Check for Layout Sub-Response between each Checkbox Response
        if ($curr->nodeType == 'Checkbox' 
            && sizeof($curr->tmpSubTier[1]) > 0) {
            $ret .= $this->printCheckSubRes($curr, $j, $res, $boxChecked);
        }
//if ($curr->nID == 3174) { echo '<pre>'; print_r($curr); echo '</pre>'; exit; }
        if (in_array($curr->nodeType, ['Checkbox', 'Radio'])
            && in_array($j, $curr->fldHasOther)) {
            $fldOth = 'n' . $curr->nIDtxt . 'fldOtherID' . $j;
            $GLOBALS["SL"]->pageAJAX .= '$(document).on("keyup", "#n' 
                . $curr->nIDtxt . 'fldOtherID' . $j . '", function() { '
                . 'if (document.getElementById("' . $fldOth . '") '
                . '&& document.getElementById("' . $fldOth . '").value.trim() '
                . '!= "") { document.getElementById("n' . $curr->nIDtxt . 'fld' . $j 
                . '").checked=true; checkFingerClass("' . $curr->nIDtxt . '"); } });';
            /*
            $GLOBALS["SL"]->pageAJAX .= '$(document).on("keyup", "#n' 
                . $curr->nIDtxt . 'fldOtherID' . $j . '", function() { formKeyUpOther(\'' 
                . $curr->nIDtxt . '\', ' . $j . '); });';
                // chkSubRes' . $nIDtxt . 'j' . $j . '();
            */
        }
        return $ret;
    }

    protected function printNodeCheckboxOther($curr, $j, $res, $val)
    {
        $otherFld = [ '', '', '', '' ];
        if (in_array($j, $curr->fldHasOther)) {
            $otherFld[0] = $curr->fld . '_other';
            $fldVals = [ $curr->fld => $val ];
            $s = sizeof($this->sessData->dataBranches);
            if ($s > 0) {
                $branch = $this->sessData->dataBranches[$s-1];
                if (intVal($branch["itemID"]) > 0) {
                    $tbl2 = $branch["branch"];
                    $branchLnkFld = $GLOBALS["SL"]->getFornNameFldName($curr->tbl, $tbl2);
                    if ($branchLnkFld != '') {
                        $fldVals[$branchLnkFld] = $branch["itemID"];
                    }
                }
            }
            $subRowIDs = $this->sessData->getRowIDsByFldVal($curr->tbl, $fldVals);
            $branchRowID = ((sizeof($subRowIDs) > 0) ? $subRowIDs[0] : -3);
            if ($branchRowID > 0) {
                $cyc = 'res' . $j;
                $GLOBALS["SL"]->currCyc["res"] = [ $curr->tbl, $cyc, $val ];
                $this->sessData->startTmpDataBranch($curr->tbl, $branchRowID);
                $othNode = new TreeNodeSurv($curr->nID);
                $othNode->nID = $curr->nID;
                $othNode->fillNodeRow();
                $othNode->tbl = $curr->tbl;
                $othNode->fld = $otherFld[0];
                $othNode->hasParManip = $curr->hasParManip;
                $otherFld[1] = $this->sessData->currSessData($othNode);
                $this->sessData->endTmpDataBranch($curr->tbl);
                $GLOBALS["SL"]->currCyc["res"] = [ '', '', -3 ];
            } else {
                $otherFld[1] = '';
            }
            $otherFld[2] = view(
                'vendor.survloop.forms.formtree-checkbox-other', 
                [
                    "curr" => $curr,
                    "j"    => $j,
                    "val"  => $otherFld[1]
                ]
            )->render();
        }
        return $otherFld;
    }

    protected function printCheckSubRes($curr, $j, $res, $boxChecked)
    {
        $ret = '';
        foreach ($curr->tmpSubTier[1] as $childNode) {
            if ($this->allNodes[$childNode[0]]->nodeType == 'Layout Sub-Response' 
                && sizeof($childNode[1]) > 0) {
                $ret .= '<div id="n' . $curr->nIDtxt 
                    . 'fld' . $j . 'sub" class="subRes '
                    . (($boxChecked) ? 'disBlo' : 'disNon') . '" >';
                $GLOBALS["SL"]->currCyc["res"][0] = $curr->tbl;
                $GLOBALS["SL"]->currCyc["res"][1] = 'res' . $j;
                $GLOBALS["SL"]->currCyc["res"][2] = $res->node_res_value;
                $fldAssign = [ $curr->fld => $res->node_res_value ];
                $subRowIDs = $this->sessData->getRowIDsByFldVal($curr->tbl, $fldAssign);
                $branchRowID = ((sizeof($subRowIDs) > 0) ? $subRowIDs[0] : -3);
                if ($branchRowID > 0) {
                    $this->sessData->startTmpDataBranch($curr->tbl, $branchRowID);
                }
                $grankids = '';
                foreach ($childNode[1] as $k => $granNode) {
                    $grankids .= (($k > 0) ? ', ' : '') . $granNode[0];
                    $ret .= $this->printNodePublic($granNode[0], $granNode, $boxChecked);
                }
                if ($branchRowID > 0) {
                    $this->sessData->endTmpDataBranch($curr->tbl);
                }
                $GLOBALS["SL"]->currCyc["res"] = [ '', '', -3 ];
                $ret .= '</div>';
                $GLOBALS["SL"]->pageAJAX .= view(
                    'vendor.survloop.forms.formtree-sub-response-ajax', 
                    [
                        "nID"      => $curr->nID,
                        "nSffx"    => $curr->nSffx,
                        "nIDtxt"   => $curr->nIDtxt,
                        "j"        => $j,
                        "grankids" => $grankids
                    ]
                )->render();
            }
        }
        return $ret;
    }

    protected function nodePrintGender($curr)
    {
        $ret = '';
        $fldOth = $curr->fld . '_other';
        $sessDataOther = $this->sessData->currSessDataTblFld(
            $curr->nID, 
            $curr->tbl, 
            $fldOth
        );
        for ($j = 0 ; $j < sizeof($curr->responses); $j++) {
            $this->pageFldList[] = 'n' . $curr->nIDtxt . 'fld' . $j;
        }
        $ret .= view(
            'vendor.survloop.forms.formtree-gender', 
            [
                "curr"          => $curr,
                "sessDataOther" => $sessDataOther
            ]
        )->render();
        $gendAllow = [ 'Female', 'Male', 'Other', 'Not sure' ];
        $genderSuggest = '';
        foreach ($GLOBALS["SL"]->def->getOtherGenders() as $gen) {
            if (!in_array($gen, $gendAllow)) {
                $genderSuggest .= ', "' . $gen . '"';
            }
        }
        $GLOBALS["SL"]->pageAJAX .= '$( "#n' . $curr->nIDtxt . 'fldOtherID2" )'
            . '.autocomplete({ source: [' . substr($genderSuggest, 1) . '] });' . "\n";
        $this->v["javaNodes"] .= 'nodeResTot["' . $curr->nID . '"] = ' 
            . sizeof($curr->responses) . '; ';
        if ($curr->isRequired()) {
            $this->pageJSvalid .= "addReqNode('" . $curr->nIDtxt . "', 'reqFormGender');\n";
        }
        return $ret;
    }


}