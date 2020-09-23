<?php
/**
  * TreeSurvSpreadsheet is a mid-level class which provides management
  * of in-line spreadsheets.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.13
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use RockHopSoft\Survloop\Controllers\Tree\TreeSurvFormCheckboxes;

class TreeSurvSpreadsheet extends TreeSurvFormCheckboxes
{
    protected function nodePrintSpreadsheet(&$curr)
    {
        $ret = '';
        if (sizeof($curr->tmpSubTier[1]) > 0) {
            $this->tableDat = $this->loadTableDat(
                $curr, 
                $curr->sessData, 
                $curr->tmpSubTier
            );
            $GLOBALS["SL"]->currCyc["tbl"][0] = $this->tableDat["tbl"];
            for ($i = 0; $i < $this->tableDat["maxRow"]; $i++) {
                if ($i < sizeof($this->tableDat["rows"])) {
                    $this->nodePrintSpreadsheetRow($curr, $i);
                }
            }
            $GLOBALS["SL"]->currCyc["tbl"][1] = 'tbl' . $curr->nIDtxt;
            $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
            $java = '';
            foreach ($curr->tmpSubTier[1] as $k => $kidNode) {
                $kid = $this->printNodePublic($kidNode[0], $kidNode, 1);
                $this->tableDat["blnk"][$k] = str_replace('nFld', '', 
                    str_replace('nFld mT0', '', $kid));
                $java .= (($k > 0) ? ', ' : '') . $kidNode[0];
            }
            $this->v["javaNodes"] .= 'nodeTblList[' . $curr->nID 
                . '] = new Array(' . $java . '); ';
            if ($this->tableDat["req"][0]) {
                $this->pageJSvalid .= "var cols = new Array(";
                foreach ($curr->tmpSubTier[1] as $k => $kidNode) {
                    $this->pageJSvalid .= (($k > 0) ? ", " : "") 
                        . " new Array(" . $kidNode[0] . ", " 
                        . (($this->tableDat["req"][2][$k]) ? 'true' : 'false') 
                        . ") ";
                }
                $this->pageJSvalid .= ");\n" . "addReqNodeTbl(" 
                    . $curr->nID . ", '" . $curr->nIDtxt . "', 'reqFormFldTbl', " 
                    . $this->tableDat["maxRow"] . ", cols, " 
                    . (($this->tableDat["req"][1]) ? 'true' : 'false') . ");\n";
            }
            $prompt = $this->swapLabels($curr, $curr->nodeRow->node_prompt_text);

            $GLOBALS["SL"]->pageAJAX .= view(
                'vendor.survloop.forms.formtree-table-ajax', 
                [
                    "nIDtxt"          => $curr->nIDtxt,
                    "node"            => $curr,
                    "tableDat"        => $this->tableDat
                ]
            )->render();
            $ret .= view(
                'vendor.survloop.forms.formtree-table', 
                [
                    "nID"             => $curr->nID,
                    "nIDtxt"          => $curr->nIDtxt,
                    "node"            => $curr,
                    "nodePromptText"  => $prompt,
                    "tableDat"        => $this->tableDat
                ]
            )->render();
            $GLOBALS["SL"]->currCyc["tbl"] = [ '', '', -3 ];
            $this->tableDat = [];
        }
        return $ret;
    }

    protected function nodePrintSpreadsheetRow(&$curr, $i)
    {
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
        foreach ($curr->tmpSubTier[1] as $k => $kidNode) {
            $printFld = $this->printNodePublic($kidNode[0], $kidNode, 1);
            $printFld = str_replace('nFld', '', str_replace('nFld mT0', '', $printFld));
            $this->tableDat["rows"][$i]["cols"][] = $printFld;
            $this->tableDat["rows"][$i]["data"][] = $this->v["currNodeSessData"];
        }
        $this->sessData->endTmpDataBranch($this->tableDat["tbl"]);
        $GLOBALS["SL"]->currCyc["tbl"][1] = '';
        $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
        return true;
    }
    
    public function loadTableDat($curr, $currNodeSessData = [], $tmpSubTier = [])
    {
        $req = [
            $curr->isRequired(), 
            false, 
            [] 
        ];
        $this->tableDat = [
            "tbl"    => '', 
            "defSet" => '', 
            "loop"   => '', 
            "month"  => '', 
            "rowCol" => $curr->getTblFldName(), 
            "rows"   => [], 
            "cols"   => [], 
            "data"   => [], 
            "blnk"   => [],
            "maxRow" => 10, 
            "req"    => $req
        ];
        if (isset($curr->nodeRow->node_data_branch) 
            && trim($curr->nodeRow->node_data_branch) != '') {
            $this->tableDat["tbl"] = $curr->nodeRow->node_data_branch;
        }
        $rowSet = $curr->parseResponseSet();
        if ($rowSet["type"] == 'Definition') { // lookup id based on rowCol and currNodeSessData
            $this->tableDat["defSet"] = $rowSet["set"];
            $defs = $GLOBALS["SL"]->def->getSet($rowSet["set"]);
            if (sizeof($defs) > 0) {
                foreach ($defs as $i => $def) {
                    $this->tableDat["rows"][] = $this->addTableDatRow(
                        -3, 
                        $def->def_value, 
                        $def->def_id
                    );
                }
            }
        } elseif ($rowSet["type"] == 'LoopItems') {
            $this->tableDat["loop"] = $rowSet["set"];
            $loopCycle = $this->sessData->getLoopRows($rowSet["set"]);
            if (sizeof($loopCycle) > 0) {
                $this->tableDat["tbl"] = $GLOBALS["SL"]->getLoopTable($rowSet["set"]);
                foreach ($loopCycle as $i => $loopItem) {
                    $label = $this->getLoopItemLabel($rowSet["set"], $loopItem, $i);
                    $this->tableDat["rows"][] = $this->addTableDatRow(
                        $loopItem->getKey(), 
                        $label
                    );
                }
            }
        } elseif ($rowSet["type"] == 'Table') {
            $this->tableDat["tbl"] = $rowSet["set"];
            if (isset($this->sessData->dataSets[$this->tableDat["tbl"]]) 
                && sizeof($this->sessData->dataSets[$this->tableDat["tbl"]]) > 0) {
                foreach ($this->sessData->dataSets[$this->tableDat["tbl"]] as $i => $tblItem) {
                    $label = $this->getTableRecLabel($rowSet["set"], $tblItem, $i);
                    $this->tableDat["rows"][] = $this->addTableDatRow(
                        $tblItem->getKey(), 
                        $label
                    );
                }
            }
        } elseif ($rowSet["type"] == 'Months' && $curr->isDynaMonthTbl()) {
            $this->tableDat["month"] = $curr->dynaMonthFld;
            $monthTblAbbr = $GLOBALS["SL"]->tblAbbr[$this->tableDat["tbl"]];
            if (isset($this->sessData->dataSets[$this->tableDat["tbl"]]) 
                && sizeof($this->sessData->dataSets[$this->tableDat["tbl"]]) > 0) {
                for ($m = 1; $m <= 12; $m++) {
                    $label = date("M", mktime(0, 0, 0, $m, 1, 2000));
                    foreach ($this->sessData->dataSets[$this->tableDat["tbl"]] as $i => $tblItem) {
                        if (isset($tblItem->{ $monthTblAbbr . 'month' })
                            && $m == intVal($tblItem->{ $monthTblAbbr . 'month' })) {
                            $this->tableDat["rows"][] = $this->addTableDatRow(
                                $tblItem->getKey(), 
                                $label,
                                intVal($tblItem->{ $monthTblAbbr . 'month' })
                            );
                        }
                    }
                }
            }
        } else { // no set, type is to just let the user add rows of the table
            $rowIDs = $this->sessData->getBranchChildRows($this->tableDat["tbl"], true);
            if (sizeof($rowIDs) > 0) {
                foreach ($rowIDs as $rowID) {
                    $this->tableDat["rows"][] = $this->addTableDatRow($rowID);
                }
            }
        }
        if (sizeof($this->tableDat["rows"]) > 0) {
            foreach ($this->tableDat["rows"] as $i => $row) {
                if ($row["leftTxt"] == strtolower($row["leftTxt"])) {
                    $this->tableDat["rows"][$i]["leftTxt"] = ucwords($row["leftTxt"]);
                }
            }
        }
        $this->tableDat["maxRow"] = sizeof($this->tableDat["rows"]);
        if (isset($curr->nodeRow->node_char_limit) 
            && intVal($curr->nodeRow->node_char_limit) > 0) {
            $this->tableDat["maxRow"] = $curr->nodeRow->node_char_limit;
        }
        if (sizeof($tmpSubTier) > 0) {
            foreach ($tmpSubTier[1] as $k => $kidNode) {
                $this->allNodes[$kidNode[0]]->chkFill();
                $label = $this->allNodes[$kidNode[0]]->nodeRow->node_prompt_text;
                $this->allNodes[$kidNode[0]]->nodePromptText = $this->customLabels($curr, $label);
                $this->tableDat["cols"][]   = $this->allNodes[$kidNode[0]];
                $this->tableDat["req"][2][] = $this->allNodes[$kidNode[0]]->isRequired();
                if ($this->allNodes[$kidNode[0]]->isRequired()) {
                    $this->tableDat["req"][1] = true;
                }
            }
        }
        return $this->tableDat;
    }
    
    public function addTableDatRow($id = -3, $leftTxt = '', $leftVal = '', $cols = [])
    {
        return [
            "id"      => $id,      // unique row ID
            "leftTxt" => $leftTxt, // displayed in the left column of this row
            "leftVal" => $leftVal, // in addition to unique row ID
            "cols"    => $cols     // filled with nested field printings
        ];
    }

    protected function postNodePublicSpreadTbl($curr)
    {
        $ret = '';
        if (sizeof($curr->tmpSubTier[1]) > 0) {
            $this->tableDat = $this->loadTableDat($curr, [], $curr->tmpSubTier);
            $GLOBALS["SL"]->currCyc["tbl"][0] = $this->tableDat["tbl"];
            for ($i = 0; $i < $this->tableDat["maxRow"]; $i++) {
                $hasRow = false;
                $fldVals = [];
                foreach ($curr->tmpSubTier[1] as $k => $kidNode) {
                    list($kidTbl, $kidFld) = $this->allNodes[$kidNode[0]]->getTblFld();
                    $fldVals[$kidFld] = '';
                    $tmpFldName = 'n' . $kidNode[0] . $curr->nSffx . 'tbl' . $i . 'fld';
                    if ($GLOBALS["SL"]->REQ->has($tmpFldName)) {
                        if (is_array($GLOBALS["SL"]->REQ->get($tmpFldName))) {
                            if (sizeof($GLOBALS["SL"]->REQ->get($tmpFldName)) > 0) {
                                $hasRow = true;
                            }
                        } else {
                            if (trim($GLOBALS["SL"]->REQ->get($tmpFldName)) != '') {
                                $hasRow = true;
                            }
                            if ($kidTbl == $this->tableDat["tbl"]) {
                                $fldVals[$kidFld] = trim($GLOBALS["SL"]->REQ->get($tmpFldName));
                            }
                        }
                    }
                }
                if (trim($this->tableDat["month"]) != '') {
                    $ret .= $this->postNodePublicSpreadTblMonths($curr, $i, $fldVals);
                } else {
                    if (trim($this->tableDat["rowCol"]) != '') {
                        if (isset($this->tableDat["rows"][$i]) 
                            && isset($this->tableDat["rows"][$i]["leftVal"]) 
                            && trim($this->tableDat["rows"][$i]["leftVal"]) != '') {
                            $recObj = $this->sessData->checkNewDataRecord(
                                $this->tableDat["tbl"], 
                                $this->tableDat["rowCol"], 
                                $this->tableDat["rows"][$i]["leftVal"]
                            );
                            if ($hasRow) {
                                if (!$recObj) {
                                    $recObj = $this->sessData->newDataRecord(
                                        $this->tableDat["tbl"], 
                                        $this->tableDat["rowCol"], 
                                        $this->tableDat["rows"][$i]["leftVal"], 
                                        true
                                    );
                                }
                            } else { // does not have this row
                                if ($recObj && $curr->nodeRow->node_opts%73 > 0) {
                                    $this->sessData->deleteDataRecordByID(
                                        $this->tableDat["tbl"], 
                                        $recObj->getKey()
                                    );
                                }
                            }
                            if ($recObj) {
                                $this->tableDat["rows"][$i]["id"] = $recObj->getKey();
                            } else {
                                $this->tableDat["rows"][$i]["id"] = -3;
                            }
                        }
                    } else { // user adds rows as they go
                        if ($hasRow) {
                            $matches = $this->sessData->getRowIDsByFldVal(
                                $this->tableDat["tbl"], 
                                $fldVals, 
                                true
                            );
                            if (empty($matches)) {
                                $recObj = $this->sessData
                                    ->simpleNewDataRecord($this->tableDat["tbl"]);
                                if (trim($this->tableDat["loop"]) != '') {
                                    $loopLnks = $GLOBALS["SL"]
                                        ->getLoopConditionLinks($this->tableDat["loop"]);
                                    if (sizeof($loopLnks) > 0) {
                                        foreach ($loopLnks as $lnk) {
                                            $recObj->{ $lnk[0] } = $lnk[1];
                                            $recObj->save();
                                        }
                                    }
                                }
                                if ($recObj) {
                                    $this->tableDat["rows"][$i]["id"] = $recObj->getKey();
                                } else {
                                    $this->tableDat["rows"][$i]["id"] = -3;
                                }
                            } else {
                                $this->tableDat["rows"][$i]["id"] = -3;
                            }
                        } elseif (isset($this->tableDat["rows"][$i])) {
                            $this->sessData->deleteDataRecordByID(
                                $this->tableDat["tbl"], 
                                $this->tableDat["rows"][$i]["id"]
                            );
                        }
                    }
                    if ($hasRow) {
                        $GLOBALS["SL"]->currCyc["tbl"][1] = 'tbl' . $i;
                        $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
                        if (isset($this->tableDat["rows"][$i]) 
                            && intVal($this->tableDat["rows"][$i]["id"]) > 0) {
                            $GLOBALS["SL"]->currCyc["tbl"][2] = $this->tableDat["rows"][$i]["id"];
                            $this->sessData->startTmpDataBranch(
                                $this->tableDat["tbl"], 
                                $this->tableDat["rows"][$i]["id"], 
                                false
                            );
                        }
                        foreach ($curr->tmpSubTier[1] as $k => $kidNode) {
                            $ret .= $this->postNodePublic(
                                $kidNode[0], 
                                $kidNode, 
                                $curr->currVisib
                            );
                        }
                        if (isset($this->tableDat["rows"][$i]) 
                            && intVal($this->tableDat["rows"][$i]["id"]) > 0) {
                            $this->sessData->endTmpDataBranch($this->tableDat["tbl"]);
                        }
                        $GLOBALS["SL"]->currCyc["tbl"][1] = '';
                        $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
                    }
                }
            }
            $GLOBALS["SL"]->currCyc["tbl"] = ['', '', -3];
            $this->tableDat = [];
        }
        return $ret;
    }

    protected function postNodePublicSpreadTblMonths($curr, $i, $fldVals)
    {
        $idRec = 0;
        $idFld = 'n' . $curr->nIDtxt . 'tbl' . $i . 'fldRow';
        if ($GLOBALS["SL"]->REQ->has($idFld)) {
            $idRec = intVal($GLOBALS["SL"]->REQ->get($idFld));
        }
//echo '<br />postNodePublicSpreadTblMonths(' . $i . ', ' . $idFld . ' = ' . $idRec . ' - '; print_r($fldVals); echo '<br />';
        $tbl = $this->tableDat["tbl"];
        $monthTblAbbr = $GLOBALS["SL"]->tblAbbr[$tbl];
        if ($idRec > 0
            && sizeof($fldVals) > 0
            && isset($this->sessData->dataSets[$tbl])
            && sizeof($this->sessData->dataSets[$tbl]) > 0) {
            foreach ($this->sessData->dataSets[$tbl] as $m => $month) {
                if ($month->getKey() && intVal($month->getKey()) == $idRec) {
//echo 'postNodePublicSpreadTblMonths(' . $i . ', ' . $month->getKey() . ' ? = ' . $idRec . ' - '; print_r($fldVals); echo '<br />';
                    foreach ($fldVals as $fld => $val) {
                        if (trim($val) == '') {
                            $val = null;
                        }
                        $this->sessData->dataSets[$tbl][$m]->{ $fld } = $val;
//echo 'postNodePublicSpreadTblMonths(' . $i . ', ' . $idFld . ' = ' . $idRec . ' — ' . $fld . ' = ' . $val . '<br />';
                    }
                    $this->sessData->dataSets[$tbl][$m]->save();
//echo '<br />';
                }
            }
        }
        return '';
    }

}

