<?php
/**
  * SurvDataConditions is holds the functions for SurvData's 
  * analysis of conditional logic.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.32
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use RockHopSoft\Survloop\Controllers\Tree\SurvDataTestsAB;
use RockHopSoft\Survloop\Controllers\Tree\SurvDataCheckbox;

class SurvDataConditions extends SurvDataCheckbox
{
    public function parseCondition($cond = [], $recObj = [], $nID = -3)
    {
//if (isset($recObj->ps_area_type)) { echo '<pre>'; print_r($cond); print_r($recObj); echo '</pre>'; }
        $passed = true;
        if ($cond 
            && isset($cond->cond_database) 
            && $cond->cond_operator != 'CUSTOM') {
            $cond->loadVals();
            $loopName = '';
            if (intVal($cond->cond_loop) > 0 
                && isset($GLOBALS["SL"]->dataLoopNames[$cond->cond_loop])) {
                $loopName = $GLOBALS["SL"]->dataLoopNames[$cond->cond_loop];
            }
            if (intVal($cond->cond_table) <= 0 
                && trim($loopName) != '' 
                && isset($GLOBALS["SL"]->dataLoops[$loopName])) {
                $tblName = $GLOBALS["SL"]->dataLoops[$loopName]->data_loop_table;
            } else {
                $tblName = $GLOBALS["SL"]->tbl[$cond->cond_table];
            }
//if ($tbl != $setTbl) list($setTbl, $setSet, $loopItemID) = $this->getDataSetTblTranslate($set, $tbl, $loopItemID);
            if ($cond->cond_operator == 'EXISTS=') {
                if (!isset($this->dataSets[$tblName]) 
                    || (intVal($cond->cond_loop) > 0 
                        && !isset($this->loopItemIDs[$loopName]))) {
                    if (intVal($cond->cond_oper_deet) == 0) {
                        $passed = true;
                    } else {
                        $passed = false;
                    }
                } else {
                    $existCnt = sizeof($this->dataSets[$tblName]);
                    if (intVal($cond->cond_loop) > 0) {
                        $existCnt = sizeof($this->loopItemIDs[$loopName]);
                    }
                    $passed = ($existCnt == intVal($cond->cond_oper_deet));
                }
            } elseif ($cond->cond_operator == 'EXISTS>') {
                if (!isset($this->dataSets[$tblName]) 
                    || (intVal($cond->cond_loop) > 0 
                        && !isset($this->loopItemIDs[$loopName]))) {
                    $passed = false;
                } else {
                    $existCnt = sizeof($this->dataSets[$tblName]);
                    if (intVal($cond->cond_loop) > 0) {
                        $existCnt = sizeof($this->loopItemIDs[$loopName]);
                    }
                    if (intVal($cond->cond_oper_deet) == 0) {
                        $passed = ($existCnt > 0);
                    } elseif ($cond->cond_oper_deet > 0) {
                        $passed = ($existCnt > intVal($cond->cond_oper_deet));
                    } elseif ($cond->cond_oper_deet < 0) {
                        $passed = ($existCnt < ((-1)*intVal($cond->cond_oper_deet)));
                    }
                }
            } elseif (intVal($cond->cond_field) > 0) {
                $fldName = $GLOBALS["SL"]->getFullFldNameFromID($cond->cond_field, false);
                if ($cond->cond_operator == '{{') {
                    // find any match in any row for this table
                    $passed = false;
                    if (isset($this->dataSets[$tblName]) 
                        && sizeof($this->dataSets[$tblName]) > 0) {
                        foreach ($this->dataSets[$tblName] as $ind => $row) {
                            if (isset($row->{ $fldName }) 
                                && trim($row->{ $fldName }) != '' 
                                && in_array($row->{ $fldName }, $cond->condVals)) {
                                $passed = true;
                            }
                        }
                    }
                } else {
                    $currSessData = '';
                    if ($recObj && $recObj->getKey() > 0) {
                        $currSessData = $recObj->{ $fldName };
//if ($cond->cond_id == 107) { echo 'COND GETKEY set sessData : <pre>'; print_r($currSessData); echo '</pre>'; }
                    } elseif ($nID > 0) {
                        $currSessData = $this->currSessDataTblFld($nID, $tblName, $fldName);
//if ($cond->cond_id == 107) { echo 'COND NID set sessData : <pre>'; print_r($currSessData); echo '</pre>'; }
                    }
                    // else not a node, but general filter of entire core record's data set
                    if (trim($currSessData) == '') { 
                        if (isset($this->dataSets[$tblName]) 
                            && sizeof($this->dataSets[$tblName]) > 0) {
                            foreach ($this->dataSets[$tblName] as $ind => $row) {
                                if (isset($row->{ $fldName }) 
                                    && trim($row->{ $fldName }) != '') {
                                    $currSessData = $row->{ $fldName };
//if ($cond->cond_id == 107 && $recObj->getKey() == 200) { echo 'COND ROUND2 set sessData : <pre>'; print_r($currSessData); echo '</pre>'; }
                                }
                            }
                        } else {
                            $passed = false;
                        }
                    }
                    if (trim($currSessData) != '') {
                        if ($cond->cond_operator == '{') {
//if ($cond->cond_id == 107 && $recObj->getKey() == 200) { echo 'COND { sessData: <pre>'; print_r($currSessData); echo '</pre> condVals: '; print_r($cond->condVals); echo '<br />'; }
                            $passed = (in_array($currSessData, $cond->condVals));
                        } elseif ($cond->cond_operator == '}') {
                            $passed = (!in_array($currSessData, $cond->condVals));
                        }
                    } else {
                        if ($cond->cond_operator == '{') {
                            $passed = false;
                        } elseif ($cond->cond_operator == '}') {
                            $passed = true;
                        }
                    }
                }
            }
        }
        return $passed;
    }

    protected function loadSessTestsAB()
    {
        $this->testsAB = new SurvDataTestsAB;
        $params = $abField = '';
        if (isset($this->dataSets[$this->coreTbl]) 
            && sizeof($this->dataSets[$this->coreTbl]) > 0) {
            $abField = $GLOBALS["SL"]->tblAbbr[$this->coreTbl] . 'version_ab';
            if (isset($this->dataSets[$this->coreTbl][0]->{ $abField })) {
                $params = $this->dataSets[$this->coreTbl][0]->{ $abField };
            }
        }
        $this->testsAB->addParamsAB($params);
        if ($GLOBALS["SL"]->REQ->has('ab') 
            && trim($GLOBALS["SL"]->REQ->get('ab') != '')) {
            $this->testsAB->addParamsAB(trim($GLOBALS["SL"]->REQ->get('ab')));
        }
        if ($abField != '') {
            $this->dataSets[$this->coreTbl][0]->update([
                $abField => $this->testsAB->printParams()
            ]);
        }
        return true;
    }

}
