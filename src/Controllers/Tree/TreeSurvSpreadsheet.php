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
    /**
     * Prints spreadsheet table as node within branching survey.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @return string
     */
    protected function nodePrintSpreadsheet(&$curr)
    {
        $ret = '';
        if (sizeof($curr->tmpSubTier[1]) > 0) {
            $GLOBALS["SL"]->currCyc["tbl"] = [ '', '', -3 ];
            $this->tableDat = $this->loadTableDat(
                $curr,
                $curr->sessData,
                $curr->tmpSubTier
            );
            $GLOBALS["SL"]->x["rowLabelMore"] = [];
            $GLOBALS["SL"]->currCyc["tbl"][0] = $this->tableDat["tbl"];
            for ($i = 0; $i < $this->tableDat["maxRow"]; $i++) {
                if ($i < sizeof($this->tableDat["rows"])) {
                    $this->nodePrintSpreadsheetRow($curr, $i);
                } else {
                    $GLOBALS["SL"]->x["rowLabelMore"][$i]
                        = $this->nodeSprdTblBlnkExtras($curr, $i);
                }
            }
            $GLOBALS["SL"]->currCyc["tbl"][1] = 'tbl?';
            $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
            $this->v["skipCurrNodeSessData"] = true;
            foreach ($curr->tmpSubTier[1] as $k => $kidNode) {
                $kid = $this->printNodePublic($kidNode[0], $kidNode, 0);
                $kid = str_replace('nFld mT0', '', $kid);
                $kid = str_replace('nFld', '', $kid);
                $this->tableDat["blnk"][$k] = $kid;
            }
            $this->v["skipCurrNodeSessData"] = false;
            $this->nodePrintSpreadsheetLoadJs($curr);
            $GLOBALS["SL"]->currCyc["tbl"] = [ '', '', -3 ];
            //$GLOBALS["SL"]->currCyc["tbl"][1] = 'tbl' . $curr->nIDtxt;
            $prompt = $this->swapLabels($curr, $curr->nodeRow->node_prompt_text);
            $ret .= view(
                'vendor.survloop.forms.formtree-table',
                [
                    "nID"            => $curr->nID,
                    "nIDtxt"         => $curr->nIDtxt,
                    "node"           => $curr,
                    "nodePromptText" => $prompt,
                    "hasCol1"        => $this->nodePrintSpreadsheetHasCol1($curr),
                    "tableDat"       => $this->tableDat
                ]
            )->render();
            $GLOBALS["SL"]->currCyc["tbl"] = [ '', '', -3 ];
            $this->tableDat = [];
        }
        return $ret;
    }

    /**
     * Extras needed for printing blank spreadsheet rows.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @param   int   $i
     * @return string
     */
    protected function nodeSprdTblBlnkExtras($curr, $i)
    {
        return '';
    }

    /**
     * Prints javascript needed for spreadsheet
     * table printing within branching survey.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @return void
     */
    protected function nodePrintSpreadsheetLoadJs($curr)
    {
        $java = '';
        foreach ($curr->tmpSubTier[1] as $k => $kidNode) {
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
        $GLOBALS["SL"]->pageAJAX .= view(
            'vendor.survloop.forms.formtree-table-ajax',
            [
                "nIDtxt"   => $curr->nIDtxt,
                "node"     => $curr,
                "tableDat" => $this->tableDat
            ]
        )->render();
    }

    /**
     * Determines whether or not this spreadsheet table has a first column.
     *
     * @return boolean
     */
    protected function nodePrintSpreadsheetHasCol1($curr)
    {
        $rowSet = $curr->parseResponseSet();
        if ($rowSet["type"] == 'LoopItems') {
            return true;
        }
        $hasCol1 = false;
        if (sizeof($this->tableDat["rows"]) > 0) {
            foreach ($this->tableDat["rows"] as $row) {
                if (isset($row["leftTxt"])
                    && trim(strip_tags($row["leftTxt"])) != '') {
                    $hasCol1 = true;
                }
            }
        }
        return $hasCol1;
    }

    /**
     * Prints single row from spreadsheet table within branching survey.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @param   int   $i
     * @return void
     */
    protected function nodePrintSpreadsheetRow(&$curr, $i)
    {
        $this->nodePrintSpreadsheetRowCol($curr, $i);
        $tbl = $this->tableDat["tbl"];
        $currRecID = $this->tableDat["rows"][$i]["id"];
        $GLOBALS["SL"]->currCyc["tbl"][1] = 'tbl' . $i;
        $GLOBALS["SL"]->currCyc["tbl"][2] = $currRecID;
        $this->sessData->startTmpDataBranch($tbl, $currRecID, false);
        foreach ($curr->tmpSubTier[1] as $k => $kidNode) {
            $printFld = $this->printNodePublic($kidNode[0], $kidNode, 1);
            $printFld = str_replace('nFld mT0', '', $printFld);
            $printFld = str_replace('nFld', '', $printFld);
            $this->tableDat["rows"][$i]["cols"][] = $printFld;
            $this->tableDat["rows"][$i]["data"][] = $this->v["currNodeSessData"];
        }
        $this->sessData->endTmpDataBranch($tbl);
        $GLOBALS["SL"]->currCyc["tbl"][1] = '';
        $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
    }

    /**
     * Initialize single row for printing a RowCol-style spreadsheet table.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @param   int   $i
     * @return void
     */
    protected function nodePrintSpreadsheetRowCol(&$curr, $i)
    {
        if (trim($this->tableDat["rowCol"]) != ''
            && trim($this->tableDat["rowCol"]) != ''
            && (!isset($this->tableDat["rows"][$i])
                || $this->tableDat["rows"][$i]["id"] <= 0)) {
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

    /**
     * Initialize spreadsheet's tracking table
     * with most basic structure and a few values.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @return void
     */
    private function initTableDat($curr)
    {
        $req = [ $curr->isRequired(), false, [] ];
        $this->tableDat = [
            "tbl"       => '',
            "defSet"    => '',
            "loop"      => '',
            "month"     => '',
            "myear"     => '',
            "loopLbl"   => '',
            "rows"      => [],
            "cols"      => [],
            "data"      => [],
            "blnk"      => [],
            "fldVals"   => [],
            "maxRow"    => 20,
            "currRowID" => -3,
            "hasRow"    => false,
            "rowCol"    => $curr->getTblFldName(),
            "req"       => $req
        ];
        if (isset($curr->nodeRow->node_data_branch)
            && trim($curr->nodeRow->node_data_branch) != '') {
            $this->tableDat["tbl"] = $curr->nodeRow->node_data_branch;
        }
    }

    /**
     * Load data table which tracks the needs of the current spreadsheet.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @param   array   $currNodeSessData
     * @param   array   $tmpSubTier
     * @return array
     */
    public function loadTableDat($curr, $currNodeSessData = [], $tmpSubTier = [])
    {
        $this->initTableDat($curr);
        $rowSet = $curr->parseResponseSet();
        if ($rowSet["type"] == 'Definition') {
            // lookup id based on rowCol and currNodeSessData
            $this->loadTableDatDefs($rowSet);
        } elseif ($rowSet["type"] == 'LoopItems') {
            $this->loadTableDatLoop($curr->nID, $rowSet);
        } elseif ($rowSet["type"] == 'Table') {
            $this->loadTableDatTable($rowSet);
        } elseif ($rowSet["type"] == 'Months' && $curr->isDynaMonthTbl()) {
            $this->loadTableDatMonths($curr, $rowSet);
        } else { // no set, type is to just let the user add rows of the table
            $this->loadTableDatUserAdds();
        }
        $this->loadTableDatTots($curr);
        if (sizeof($tmpSubTier) > 0) {
            foreach ($tmpSubTier[1] as $k => $kidNode) {
                $this->loadTableDatKid($kidNode[0]);
            }
        }
        return $this->tableDat;
    }

    /**
     * Load data table with definition rows.
     *
     * @param   array   $rowSet
     * @return void
     */
    private function loadTableDatDefs($rowSet)
    {
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
    }

    /**
     * Load data table with rows for loop items.
     *
     * @param   int   $nID
     * @param   array   $rowSet
     * @return void
     */
    private function loadTableDatLoop($nID, $rowSet)
    {
        $idFld = '';
        $this->tableDat["loop"] = $loop = $rowSet["set"];
        if (isset($GLOBALS["SL"]->dataLoops[$loop])) {
            $loopRow = $GLOBALS["SL"]->dataLoops[$loop];
            $this->tableDat["loopLbl"] = $loopRow->data_loop_singular;
            if (isset($loopRow->data_loop_table)
                && isset($GLOBALS["SL"]->tblAbbr[$loopRow->data_loop_table])) {
                $idFld = $GLOBALS["SL"]->tblAbbr[$loopRow->data_loop_table] . 'id';
            }
        }
        $loopCycle = $this->sessData->getLoopRows($loop);
        if (sizeof($loopCycle) > 0 && $idFld != '') {
            $this->tableDat["tbl"] = $GLOBALS["SL"]->getLoopTable($loop);
            foreach ($loopCycle as $i => $loopItem) {
                if (isset($loopItem->{ $idFld })
                    && intVal($loopItem->{ $idFld }) > 0) {
                    $id = intVal($loopItem->{ $idFld });
                    $label = $this->getLoopItemLabelCol1($nID, $loop, $loopItem, $i)
                        . $this->getLoopItemLabel($loop, $loopItem, $i);
                    $this->tableDat["rows"][] = $this->addTableDatRow($id, $label);
                }
            }
        }
    }

    /**
     * Load data table with rows for regular table records.
     *
     * @param   array   $rowSet
     * @return void
     */
    private function loadTableDatTable($rowSet)
    {
        $this->tableDat["tbl"] = $tbl = $rowSet["set"];
        if (isset($this->sessData->dataSets[$tbl])
            && sizeof($this->sessData->dataSets[$tbl]) > 0) {
            foreach ($this->sessData->dataSets[$tbl] as $i => $tblItem) {
                $label = $this->getTableRecLabel($rowSet["set"], $tblItem, $i);
                $this->tableDat["rows"][] = $this->addTableDatRow(
                    $tblItem->getKey(),
                    $label
                );
            }
        }
    }

    /**
     * Load data table with rows for each month.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @param   array   $rowSet
     * @return void
     */
    private function loadTableDatMonths($curr, $rowSet)
    {
        $this->tableDat["month"] = $curr->dynaMonthFld;
        $this->tableDat["myear"] = $curr->dynaYearFld;
        $tbl = $this->tableDat["tbl"];
        $monthTblAbbr = $GLOBALS["SL"]->tblAbbr[$tbl];
        if (isset($this->sessData->dataSets[$tbl])
            && sizeof($this->sessData->dataSets[$tbl]) > 0) {
            for ($m = 1; $m <= 12; $m++) {
                $label = date("M", mktime(0, 0, 0, $m, 1, 2000));
                foreach ($this->sessData->dataSets[$tbl] as $i => $tblItem) {
                    $fld = $monthTblAbbr . 'month';
                    if (isset($tblItem->{ $fld })
                        && $m == intVal($tblItem->{ $fld })) {
                        $this->tableDat["rows"][] = $this->addTableDatRow(
                            $tblItem->getKey(),
                            $label,
                            intVal($tblItem->{ $fld })
                        );
                    }
                }
            }
        }
    }

    /**
     * Load data table with rows that the user can add.
     *
     * @return void
     */
    private function loadTableDatUserAdds()
    {
        $tbl = $this->tableDat["tbl"];
        $rowIDs = $this->sessData->getBranchChildRows($tbl, true);
        if (sizeof($rowIDs) > 0) {
            foreach ($rowIDs as $rowID) {
                $this->tableDat["rows"][] = $this->addTableDatRow($rowID);
            }
        }
    }

    /**
     * Analyze the current data table rows for later reference.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @return void
     */
    private function loadTableDatTots($curr)
    {
        if (sizeof($this->tableDat["rows"]) > 0) {
            foreach ($this->tableDat["rows"] as $i => $row) {
                if ($row["leftTxt"] == strtolower($row["leftTxt"])) {
                    $this->tableDat["rows"][$i]["leftTxt"]
                        = ucwords($row["leftTxt"]);
                }
            }
        }
        $this->tableDat["maxRow"] = sizeof($this->tableDat["rows"]);
        if (isset($curr->nodeRow->node_char_limit)
            && intVal($curr->nodeRow->node_char_limit) > 0) {
            $this->tableDat["maxRow"] = intVal($curr->nodeRow->node_char_limit);
        }
    }

    /**
     * Load the table column into the table tracking structure.
     *
     * @param   int   $kidID
     * @return void
     */
    private function loadTableDatKid($kidID)
    {
        $this->allNodes[$kidID]->chkFill();
        $kidNode = $this->allNodes[$kidID];
        $label = $kidNode->nodeRow->node_prompt_text;
        $kidNode->nodePromptText = $this->customLabels($kidNode, $label);
        //$kidNode->nodePromptText = $this->customLabels($curr, $label);
        $this->tableDat["cols"][]   = $kidNode;
        $this->tableDat["req"][2][] = $kidNode->isRequired();
        if ($kidNode->isRequired()) {
            $this->tableDat["req"][1] = true;
        }
    }

    /**
     * Add table data row to the current spreadsheet tracker.
     *
     * @param   int   $id
     * @param   string   $leftTxt
     * @param   string   $leftVal
     * @param   array   $cols
     * @return array
     */
    public function addTableDatRow($id = -3, $leftTxt = '', $leftVal = '', $cols = [])
    {
        return [
            "id"      => $id,      // unique row ID
            "leftTxt" => $leftTxt, // displayed in the left column of this row
            "leftVal" => $leftVal, // in addition to unique row ID
            "cols"    => $cols     // filled with nested field printings
        ];
    }

    /**
     * Process form submission for the current spreadsheet table.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @return array
     */
    protected function postNodeSpreadTbl($curr)
    {
        $ret = '';
        if (sizeof($curr->tmpSubTier[1]) > 0) {
            $this->tableDat = $this->loadTableDat($curr, [], $curr->tmpSubTier);
            $GLOBALS["SL"]->currCyc["tbl"][0] = $this->tableDat["tbl"];
            for ($i = 0; $i < $this->tableDat["maxRow"]; $i++) {
                $this->postNodeSpreadTblHasRow($curr, $i);
                $this->postNodeSpreadTblGetID($curr, $i);
                if (trim($this->tableDat["month"]) != '') {
                    $ret .= $this->postNodeSpreadTblMonths($curr, $i);
                } else {
                    $ret .= $this->postNodeSpreadTblNotMonths($curr, $i);
                }
            }
            $GLOBALS["SL"]->currCyc["tbl"] = ['', '', -3];
            $this->tableDat = [];
        }
        return $ret;
    }

    /**
     * Process the posting of a basic spreadsheet table.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @param   int   $i
     * @return string
     */
    protected function postNodeSpreadTblNotMonths($curr, $i)
    {
        $ret = '';
        if (trim($this->tableDat["rowCol"]) != '') {
            if (isset($this->tableDat["rows"][$i])
                && isset($this->tableDat["rows"][$i]["leftVal"])
                && trim($this->tableDat["rows"][$i]["leftVal"]) != '') {
                $ret .= $this->postNodeSpreadTblRowCol($curr, $i);
            }
        } else { // user adds rows as they go
            $ret .= $this->postNodeSpreadTblNotRowCol($curr, $i);
        }

        if ($this->tableDat["hasRow"]) {
            $GLOBALS["SL"]->currCyc["tbl"][1] = 'tbl' . $i;
            $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
            if (isset($this->tableDat["rows"][$i])
                && intVal($this->tableDat["rows"][$i]["id"]) > 0) {
                $GLOBALS["SL"]->currCyc["tbl"][2]
                    = $this->tableDat["rows"][$i]["id"];
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
                if ($this->REQstep == 'autoSave') {
                    echo '<script type="text/javascript"> '
                        . 'setTimeout("setFldVal(\'n' . $curr->nIDtxt . 'tbl' . $i
                        . 'fldRowID\', ' . intVal($this->tableDat["rows"][$i]["id"])
                        . ')", 1); </script>';
                }
                $this->sessData->endTmpDataBranch($this->tableDat["tbl"]);
            }
            $GLOBALS["SL"]->currCyc["tbl"][1] = '';
            $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
        }
    }

    /**
     * Process the posting of a basic spreadsheet table.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @param   int   $i
     * @return string
     */
    protected function postNodeSpreadTblRowCol($curr, $i)
    {
        $ret = '';
        $tbl = $this->tableDat["tbl"];
        $col = $this->tableDat["rowCol"];
        $lftVal = $this->tableDat["rows"][$i]["leftVal"];
        $recObj = $this->sessData->getRowById($tbl, $this->tableDat["currRowID"]);
        if ($this->tableDat["hasRow"]) {
            if (!$recObj || $recObj === null) {
                $recObj = $this->sessData->checkNewDataRecord($tbl, $col, $lftVal);
            }
            if (!$recObj || $recObj === null) {
                $recObj = $this->sessData->newDataRecord($tbl, $col, $lftVal, true);
            }
        } else { // does not have this row
            if ($recObj && $curr->nodeRow->node_opts%73 > 0) {
                $this->sessData->deleteDataRecordByID($tbl, $recObj->getKey());
            }
        }
        if ($recObj) {
            $this->tableDat["rows"][$i]["id"] = $recObj->getKey();
        } else {
            $this->tableDat["rows"][$i]["id"] = -3;
        }
        return $ret;
    }

    /**
     * Process the posting of a basic spreadsheet table.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @param   int   $i
     * @return string
     */
    protected function postNodeSpreadTblNotRowCol($curr, $i)
    {
        $ret = '';
        $tbl = $this->tableDat["tbl"];
        $keyFld = $GLOBALS["SL"]->tblAbbr[$tbl] . 'id';
        if ($this->tableDat["hasRow"]) {
            $this->tableDat["rows"][$i]["id"] = -3;
            $recObj = $this->sessData->getRowById($tbl, $this->tableDat["currRowID"]);
            if (!$recObj || !isset($recObj->{ $keyFld })) {
                $fldVals = $this->tableDat["fldVals"];
                $matches = $this->sessData->getRowIDsByFldVal($tbl, $fldVals, true);
                if (empty($matches)) {
                    $recObj = $this->sessData->simpleNewDataRecord($tbl);
                    if (trim($this->tableDat["loop"]) != '') {
                        $loop = $this->tableDat["loop"];
                        $loopLnks = $GLOBALS["SL"]->getLoopCondLinks($loop);
                        if (sizeof($loopLnks) > 0) {
                            foreach ($loopLnks as $lnk) {
                                $recObj->{ $lnk[0] } = $lnk[1];
                                $recObj->save();
                            }
                        }
                    }
                }
            }
            if ($recObj) {
                $this->tableDat["rows"][$i]["id"] = $recObj->{ $keyFld };
            }
        } elseif (isset($this->tableDat["rows"][$i])
            && $this->tableDat["rows"][$i]["id"] > 0) {
            $currRecID = $this->tableDat["rows"][$i]["id"];
            $this->sessData->deleteDataRecordByID($tbl, $currRecID);
        }
        return $ret;
    }

    /**
     * Process the posting of a monthly spreadsheet table.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @param   int   $i
     * @return string
     */
    protected function postNodeSpreadTblMonths($curr, $i)
    {
        $tbl = $this->tableDat["tbl"];
        $monthTblAbbr = $GLOBALS["SL"]->tblAbbr[$tbl];
        if ($this->tableDat["currRowID"] > 0
            && sizeof($this->tableDat["fldVals"]) > 0
            && isset($this->sessData->dataSets[$tbl])
            && sizeof($this->sessData->dataSets[$tbl]) > 0) {
            foreach ($this->sessData->dataSets[$tbl] as $m => $month) {
                if ($month->getKey()
                    && intVal($month->getKey()) == $this->tableDat["currRowID"]) {
                    foreach ($this->tableDat["fldVals"] as $fld => $val) {
                        if (trim($val) == '') {
                            $val = null;
                        }
                        $this->sessData->dataSets[$tbl][$m]->{ $fld } = $val;
                    }
                    $this->sessData->dataSets[$tbl][$m]->save();
                }
            }
        }
        return '';
    }

    /**
     * Determine the unique ID# of the record in this
     * spreadsheet row, if it had previously been created.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @param   int   $i
     * @return int
     */
    protected function postNodeSpreadTblGetID($curr, $i)
    {
        $this->tableDat["currRowID"] = -3;
        $idFld = 'n' . $curr->nIDtxt . 'tbl' . $i . 'fldRow';
        if ($GLOBALS["SL"]->REQ->has($idFld)) {
            $this->tableDat["currRowID"]
                = intVal($GLOBALS["SL"]->REQ->get($idFld));
        }
        return $this->tableDat["currRowID"];
    }

    /**
     * Determine what values are in all of the
     * columns of the current spreadsheet table.
     *
     * @param   RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv   $curr
     * @param   int   $i
     * @return void
     */
    protected function postNodeSpreadTblHasRow($curr, $i)
    {
        $this->tableDat["hasRow"]  = false;
        $this->tableDat["fldVals"] = [];
        foreach ($curr->tmpSubTier[1] as $k => $kidNode) {
            list($kidTbl, $kidFld) = $this->allNodes[$kidNode[0]]->getTblFld();
            $this->tableDat["fldVals"][$kidFld] = '';
            $tmpFldName = 'n' . $kidNode[0] . $curr->nSffx . 'tbl' . $i . 'fld';
            if ($GLOBALS["SL"]->REQ->has($tmpFldName)) {
                if (is_array($GLOBALS["SL"]->REQ->get($tmpFldName))) {
                    if (sizeof($GLOBALS["SL"]->REQ->get($tmpFldName)) > 0) {
                        $this->tableDat["hasRow"] = true;
                    }
                } else {
                    $val = trim($GLOBALS["SL"]->REQ->get($tmpFldName));
                    if ($val != '') {
                        $this->tableDat["hasRow"] = true;
                    }
                    if ($kidTbl == $this->tableDat["tbl"]) {
                        $this->tableDat["fldVals"][$kidFld] = $val;
                    }
                }
            }
        }
    }

}