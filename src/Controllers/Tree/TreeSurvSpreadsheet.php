<?php
/**
  * TreeSurvSpreadsheet is a mid-level class which provides management
  * of in-line spreadsheets.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.13
  */
namespace SurvLoop\Controllers\Tree;

use SurvLoop\Controllers\Tree\TreeSurvFormCheckboxes;

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
                    if (trim($this->tableDat["rowCol"]) != '') {
                        if ($this->tableDat["rows"][$i]["id"] <= 0 
                            && trim($this->tableDat["rowCol"]) != '') {
                            $recObj = $this->sessData->checkNewDataRecord(
                                $this->tableDat["tbl"], 
                                $this->tableDat["rowCol"], 
                                $this->tableDat["rows"][$i]["leftVal"]
                            );
                            if ($recObj) {
                                $this->tableDat["rows"][$i]["id"] 
                                    = $recObj->getKey();
                            }
                        }
                    }
                    $GLOBALS["SL"]->currCyc["tbl"][1] = 'tbl' . $i;
                    $GLOBALS["SL"]->currCyc["tbl"][2] 
                        = $this->tableDat["rows"][$i]["id"];
                    $this->sessData->startTmpDataBranch(
                        $this->tableDat["tbl"], 
                        $this->tableDat["rows"][$i]["id"], 
                        false
                    );
                    foreach ($curr->tmpSubTier[1] as $k => $kidNode) {
                        $printFld = $this->printNodePublic(
                            $kidNode[0], 
                            $kidNode, 
                            1
                        );
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
                    . $curr->nID . ", '" . $curr->nIDtxt 
                    . "', 'reqFormFldTbl', " . $this->tableDat["maxRow"] 
                    . ", cols, " 
                    . (($this->tableDat["req"][1]) ? 'true' : 'false') 
                    . ");\n";
            }
            $ret .= view(
                'vendor.survloop.forms.formtree-table', 
                [
                    "nID"             => $curr->nID,
                    "nIDtxt"          => $curr->nIDtxt,
                    "node"            => $curr,
                    "nodePromptText"  => $curr->nodePromptText,
                    "tableDat"        => $this->tableDat
                ]
            )->render();
            $GLOBALS["SL"]->currCyc["tbl"] = [ '', '', -3 ];
            $this->tableDat = [];
        }
        return $ret;
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
                    if (isset($this->tableDat["rows"][$i]) && intVal($this->tableDat["rows"][$i]["id"]) > 0) {
                        $this->sessData->endTmpDataBranch($this->tableDat["tbl"]);
                    }
                    $GLOBALS["SL"]->currCyc["tbl"][1] = '';
                    $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
                }
            }
            $GLOBALS["SL"]->currCyc["tbl"] = ['', '', -3];
            $this->tableDat = [];
        }
        return $ret;
    }



}