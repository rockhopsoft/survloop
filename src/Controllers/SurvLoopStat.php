<?php
namespace SurvLoop\Controllers;

class SurvLoopStat
{
    public $filts   = [];
    public $datMap  = [];
    public $dat     = [];
    public $tagMap  = [];
    public $tagTot  = [];
    public $raw     = [];
    
    public $recFlts = [];
    public $fltCurr = [];
    public $hidCurr = [];
    public $tblOut  = [];
    public $opts    = [ "scaler" => [ 1, '' ], "datLabPrfx" => '', "datLabOvr" => '' ];
    
    public function addFilt($abbr = '', $label = '', $values = [], $valLab = [])
    {
        $let = chr(97+(sizeof($this->filts))); // assign a, b, c,..
        $vals = $values;
        if (sizeof($values) > 0 && isset($values[0]->DefID)) {
            $vals = $valLab = [];
            foreach ($values as $def) {
                $vals[]   = $def->DefID;
                $valLab[] = $def->DefValue;
            }
        }
        $this->filts[$let] = [ "abr" => $abbr, "lab" => $label, "val" => $vals, "vlu" => $valLab ];
        return true;
    }
    
    public function fAbr($abbr = '')
    {
        if (sizeof($this->filts) > 0 && $abbr != '') {
            foreach ($this->filts as $let => $filt) {
                if ($abbr == $filt["abr"]) return $let;
            }
        }
        return '';
    }
    
    public function fValLab($abbr = '', $filtVal = -3737)
    {
        if (sizeof($this->filts) > 0 && $abbr != '') {
            foreach ($this->filts as $let => $filt) {
                if ($abbr == $filt["abr"] && sizeof($filt["val"]) > 0) {
                    foreach ($filt["val"] as $v => $val) {
                        if ($filtVal == $val) return $filt["vlu"][$v];
                    }
                }
            }
        }
        return '';
    }
    
    public function addTag($abbr = '', $label = '', $values = [], $valLab = [])
    {
        $let = chr(97+(sizeof($this->tagMap))); // assign a, b, c,..
        $vals = $values;
        if (sizeof($values) > 0 && isset($values[0]->DefID)) {
            $vals = $valLab = [];
            foreach ($values as $def) {
                $vals[]   = $def->DefID;
                $valLab[] = $def->DefValue;
            }
        }
        $this->tagMap[$let] = [ "abr" => $abbr, "lab" => $label, "val" => $vals, "vlu" => $valLab ];
//echo '<br /><br /><br />addTag(' . $abbr . ', ' . $label . ', '; print_r($vals); print_r($valLab); echo '<br />';
        return true;
    }
    
    public function tAbr($abbr = '')
    {
        if (sizeof($this->tagMap) > 0 && $abbr != '') {
            foreach ($this->tagMap as $let => $tag) {
                if ($abbr == $tag["abr"]) return $let;
            }
        }
        return '';
    }
    
    public function addDataType($abbr = '', $label = '', $unit = '', $rowLabels = [])
    {
        $let = chr(97+(sizeof($this->datMap)));
        $this->datMap[$let] = [ "abr" => $abbr, "lab" => $label, "unt" => $unit, "row" => $rowLabels ];
        return true;
    }
    
    public function addNewDataCalc($datAbbr, $datAbbr2, $oper = '*')
    {
        $datAbbr3 = $datAbbr . $oper . $datAbbr2;
        $datNewLab = $datNewUnt = '';
        $dLet1 = $this->dAbr($datAbbr);
        $dLet2 = $this->dAbr($datAbbr2);
        if (isset($this->datMap[$dLet1])) {
            $datNewLab .= $this->datMap[$dLet1]["lab"];
            $datNewUnt .= $this->datMap[$dLet1]["unt"];
        }
        $datNewLab .= ' ' . $oper . ' ';
        $datNewUnt .= $oper;
        if (isset($this->datMap[$dLet2])) {
            $datNewLab .= $this->datMap[$dLet2]["lab"];
            $datNewUnt .= $this->datMap[$dLet2]["unt"];
        }
        if (trim($this->opts["datLabOvr"]) != '') $datNewLab = $this->opts["datLabOvr"];
        $this->addDataType($datAbbr3, $datNewLab, $datNewUnt);
        $this->addNewMapRowsData($datAbbr3);
        $dLet3 = $this->dAbr($datAbbr3);
        foreach ($this->dat as $filtStr => $row) {
            foreach (['sum', 'avg'] as $typ) {
                if ($oper == '*') {
                    $this->dat[$filtStr]["dat"][$dLet3][$typ] = $row["dat"][$dLet1][$typ]*$row["dat"][$dLet2][$typ];
                } elseif ($oper == '+') {
                    $this->dat[$filtStr]["dat"][$dLet3][$typ] = $row["dat"][$dLet1][$typ]+$row["dat"][$dLet2][$typ];
                } elseif ($oper == '-') {
                    $this->dat[$filtStr]["dat"][$dLet3][$typ] = $row["dat"][$dLet1][$typ]-$row["dat"][$dLet2][$typ];
                } elseif ($oper == '/' && $row["dat"][$dLet2][$typ] > 0) {
                    $this->dat[$filtStr]["dat"][$dLet3][$typ] = $row["dat"][$dLet1][$typ]/$row["dat"][$dLet2][$typ];
                }
            }
        }
        return $datAbbr3;
    }
    
    public function dAbr($abbr = '')
    {
        if (sizeof($this->datMap) > 0 && $abbr != '') {
            foreach ($this->datMap as $let => $d) {
                if ($abbr == $d["abr"]) return $let;
            }
        }
        return '';
    }
    
    protected function addNewMapRowsData($datNewAbbr)
    {
        $datNewLet = $this->dAbr($datNewAbbr);
        foreach ($this->dat as $filtStr => $row) {
            $this->dat[$filtStr]["dat"][$datNewLet] = [ "sum" => 0, "avg" => 0, "ids" => [] ];
        }
        return true;
    }
    
    // raw data array, filters on each raw data point, data sum, data average, unique record count
    protected function loadMapRow()
    {
        $ret = [ "cnt" => 0, "rec" => [], "dat" => [] ];
        if (sizeof($this->datMap) > 0) {
            foreach ($this->datMap as $let => $d) {
                $ret["dat"][$let] = [ "sum" => 0, "avg" => 0, "ids" => [] ];
            }
        }
        return $ret;
    }
    
    protected function loadMapTagRow()
    {
        return [
            "sum" => [ "raw" => 0, "ids" => [], "row" => [] ],
            "avg" => [ "raw" => 0, "row" => [] ],
            "min" => [ "raw" => 0, "row" => [] ],
            "max" => [ "raw" => 0, "row" => [] ]
            ];
    }
    
    public function loadMap()
    {
        $this->dat = [ '1' => $this->loadMapRow() ];
        if (sizeof($this->filts) > 0) {
            foreach ($this->filts as $let => $filt) {
                if (sizeof($filt["val"]) > 0) {
                    foreach ($filt["val"] as $val) {
                        $this->dat[$let . $val] = $this->loadMapRow();
                        $this->loadMapInner($let . $val, $let);
                    }
                }
            }
        }
        ksort($this->dat);
        $this->raw = $this->tagTot = [];
        if (sizeof($this->datMap) > 0) {
            foreach ($this->datMap as $datLet => $d) { // parallel columns
                $this->raw[$datLet] = [ "raw" => [], "flt" => [], "ids" => [], "tag" => [], "row" => [] ];
                $this->tagTot[$datLet] = [ '1' => $this->loadMapTagRow() ];
                if (sizeof($this->tagMap) > 0) {
                    foreach ($this->tagMap as $tagLet => $t) {
                        if (isset($t["val"]) && sizeof($t["val"]) > 0) {
                            foreach ($t["val"] as $v => $tagVal) {
                                $this->tagTot[$datLet][$tagLet . $tagVal] = $this->loadMapTagRow();
                            }
                        }
                    }
                }
            }
        }
        $this->resetRecFilt();
        return true;
    }
    
    public function loadMapInner($filtIn = '', $lastLet = '')
    {
        $foundLet = 0;
        foreach ($this->filts as $let => $filt) {
            if ($let == $lastLet) $foundLet = 1;
            elseif ($foundLet == 1) {
                if (sizeof($filt["val"]) > 0) {
                    foreach ($filt["val"] as $val) {
                        $this->dat[$filtIn . '-' . $let . $val] = $this->loadMapRow();
                        $this->loadMapInner($filtIn . '-' . $let . $val, $let);
                    }
                }
            }
        }
        return true;
    }
    
    public function addRecDat($datAbbr = '', $datRaw = '', $recID = -3, $row = [], $tags = [])
    {
        $datLet = $this->dAbr($datAbbr);
        if ($datLet != '' && isset($this->raw[$datLet])) {
            $i = sizeof($this->raw[$datLet]["raw"]);
            $this->raw[$datLet]["raw"][$i] = $datRaw;
            $this->raw[$datLet]["ids"][$i] = $recID;
            $this->raw[$datLet]["row"][$i] = $row;
            $this->raw[$datLet]["flt"][$i] = [];
            if (sizeof($this->fltCurr) > 0) {
                foreach ($this->fltCurr as $currLet => $currVal) $this->raw[$datLet]["flt"][$i][$currLet] = $currVal;
            }
            $this->raw[$datLet]["tag"][$i] = [];
            if (sizeof($tags) > 0) {
                foreach ($tags as $tag) $this->raw[$datLet]["tag"][$i][] = [ $this->tAbr($tag[0]), $tag[1] ];
            }
        }
        return true;
    }
    
    public function loadAbbrVals($abbrVals = [])
    {
        $this->fltCurr = [];
        if (sizeof($abbrVals) > 0) {
            foreach ($abbrVals as $abbr => $val) {
                if (sizeof($this->filts) > 0) {
                    foreach ($this->filts as $let => $filt) {
                        if ($abbr == $filt["abr"]) $this->fltCurr[$let] = $val;
                    }
                }
            }
        }
        return $this->fltCurr;
    }
    
    public function addCurrFilt($abbr = '', $value = -3737)
    {
        if (sizeof($this->filts) > 0 && $value != -3737) {
            foreach ($this->filts as $let => $filt) {
                if ($abbr == $filt["abr"]) $this->fltCurr[$let] = $value;
            }
        }
        return true;
    }
    
    public function addCurrHide($abbr = '', $value = -3737)
    {
        if (sizeof($this->filts) > 0 && $value != -3737) {
            foreach ($this->filts as $let => $filt) {
                if ($abbr == $filt["abr"]) $this->hidCurr[$let] = $value;
            }
        }
        return '';
    }
    
    public function delCurrHide($abbr = '')
    {
        if (sizeof($this->filts) > 0) {
            foreach ($this->filts as $let => $filt) {
                if ($abbr == $filt["abr"]) unset($this->hidCurr[$let]);
            }
        }
        return '';
    }
    
    public function isCurrHid($let = '', $val = -3737)
    {
        return ($val != -3737 && trim($let) != '' && isset($this->hidCurr[$let]) && $this->hidCurr[$let] == $val);
    }
    
    public function addRecFilt($abbr = '', $value = -3737, $recID = -3, $replace = 1)
    {
        $recLet = '';
        if ($replace == 1) $this->delRecFilt($abbr);
        if (sizeof($this->filts) > 0 && $value != -3737) {
            $this->addCurrFilt($abbr, $value);
            foreach ($this->loadStrs($this->fltCurr) as $str) $this->addRecCnt($str, $recID);
        }
        return true;
    }
    
    public function addRecCnt($filtStr = '', $recID = -3)
    {
        if ($filtStr != '' && isset($this->dat[$filtStr])) {
            if ($recID <= 0) {
                $this->dat[$filtStr]["cnt"]++;
            } elseif (!(in_array($recID, $this->dat[$filtStr]["rec"]))) {
                $this->dat[$filtStr]["cnt"]++;
                $this->dat[$filtStr]["rec"][] = $recID;
            }
        }
        return true;
    }
    
    public function getCurrFilt()
    {
        $ret = '';
        if (sizeof($this->fltCurr) > 0) {
            foreach ($this->fltCurr as $let => $val) $ret .= (($let != 'a') ? '-' : '') . $let . $val;
        }
        return $ret;
    }
    
    public function addRecFilts($abbrVals = [], $replace = 0)
    {
        if (sizeof($abbrVals) > 0) {
            foreach ($abbrVals as $abbr => $val) $this->addRecFilt($abbr, $val, $replace);
        }
        return true;
    }
    
    public function delRecFilt($abbr = '')
    {
        if (sizeof($this->filts) > 0) {
            foreach ($this->filts as $let => $filt) {
                if ($abbr == $filt["abr"] && isset($this->fltCurr[$let])) unset($this->fltCurr[$let]);
            }
        }
        return true;
    }
    
    public function resetRecFilt()
    {
        $this->fltCurr = [];
        return true;
    }
    
    public function calcAddSum($filtStr, $datLet, $raw = 0, $id = -3)
    {
        $this->dat[$filtStr]["dat"][$datLet]["sum"] += $raw;
        $this->dat[$filtStr]["dat"][$datLet]["ids"][] = $id;
        return true;
    }
    
    public function calcAddTagSum($datLet, $tagLet, $tagVal, $raw = 0, $row = [], $id = -3)
    {
//echo 'calcAddTagSum( datLet: '; print_r($datLet); echo ', tagLet: '; print_r($tagLet); echo ', tagVal: '; print_r($tagVal); echo ', raw: '; print_r($raw); echo ', row: '; print_r($row); echo ' , id: '; print_r($id); echo '<br />';
        $tStr = $tagLet . $tagVal;
        if (isset($this->tagTot[$datLet]) && isset($this->tagTot[$datLet][$tStr])) {
            if ($id <= 0 || !in_array($id, $this->tagTot[$datLet][$tStr]["sum"]["ids"])) {
                $this->tagTot[$datLet][$tStr]["sum"]["raw"] += $raw;
                $this->tagTot[$datLet][$tStr]["sum"]["ids"][] = $id;
                if ($this->tagTot[$datLet][$tStr]["min"]["raw"] > $raw) {
                    $this->tagTot[$datLet][$tStr]["min"]["raw"] = $raw;
                }
                if ($this->tagTot[$datLet][$tStr]["max"]["raw"] < $raw) {
                    $this->tagTot[$datLet][$tStr]["max"]["raw"] = $raw;
                }
                if (sizeof($row) > 0) {
                    foreach ($row as $i => $r) {
                        if (!isset($this->tagTot[$datLet][$tStr]["sum"]["row"][$i])) {
                            $this->tagTot[$datLet][$tStr]["sum"]["row"][$i] = 0;
                        }
                        $this->tagTot[$datLet][$tStr]["sum"]["row"][$i] += $r;
                        if (!isset($this->tagTot[$datLet][$tStr]["min"]["row"][$i]) 
                            || $this->tagTot[$datLet][$tStr]["min"]["row"][$i] > $raw) {
                            $this->tagTot[$datLet][$tStr]["min"]["row"][$i] = $raw;
                        }
                        if (!isset($this->tagTot[$datLet][$tStr]["max"]["row"][$i])
                            || $this->tagTot[$datLet][$tStr]["max"]["row"][$i] < $raw) {
                            $this->tagTot[$datLet][$tStr]["max"]["row"][$i] = $raw;
                        }
                    }
                }
            }
        }
        return true;
    }
    
    public function loadStrs($flts)
    {
        $fullStr = '';
        $strs = ['1'];
        if (sizeof($flts) > 0) {
            foreach ($flts as $fLet => $fVal) {
                $currStr = (($fLet != 'a') ? '-' : '') . $fLet . $fVal;
                $fullStr .= $currStr;
                if (!in_array($fullStr, $strs)) $strs[] = $fullStr;
                if (!in_array($fLet . $fVal, $strs)) $strs[] = $fLet . $fVal;
                $xtras = [];
                for ($k = 1; $k < sizeof($strs); $k++) {
                    if ($strs[$k] != $fLet . $fVal && strpos($strs[$k], $currStr) === false) {
                        $tmpStr = $strs[$k] . $currStr;
                        if (!in_array($tmpStr, $strs)) $xtras[] = $tmpStr;
                    }
                }
                if (sizeof($xtras) > 0) {
                    foreach ($xtras as $tmpStr) $strs[] = $tmpStr;
                }
            }
        }
        return $strs;
    }
    
    public function calcTagAvg($datLet = '', $tStr = '')
    {
        if (sizeof($this->tagTot[$datLet][$tStr]["sum"]["ids"]) > 0) {
            $this->tagTot[$datLet][$tStr]["avg"]["raw"] 
                = $this->tagTot[$datLet][$tStr]["sum"]["raw"]/sizeof($this->tagTot[$datLet][$tStr]["sum"]["ids"]);
            if (sizeof($this->tagTot[$datLet][$tStr]["sum"]["row"]) > 0) {
                foreach ($this->tagTot[$datLet][$tStr]["sum"]["row"] as $i => $r) {
                    $this->tagTot[$datLet][$tStr]["avg"]["row"][$i] 
                        = $r/sizeof($this->tagTot[$datLet][$tStr]["sum"]["ids"]);
                }
            }
        }
        return true;
    }
    
    public function calcAllAvgs()
    {
        if (sizeof($this->raw) > 0) {
            foreach ($this->raw as $datLet => $d) {
                if (sizeof($d["raw"]) > 0) {
                    foreach ($d["raw"] as $i => $raw) {
                        $strs = $this->loadStrs($d["flt"][$i]);
                        foreach ($strs as $str) {
                            if ($str != '' && isset($this->dat[$str])) {
                                $this->calcAddSum($str, $datLet, $raw, $d["ids"][$i]);
                            }
                        }
                        $this->calcAddTagSum($datLet, '1', '', $raw, $d["row"][$i], $d["ids"][$i]);
                        if (sizeof($d["tag"][$i]) > 0) {
                            foreach ($d["tag"][$i] as $tag) {
                                $this->calcAddTagSum($datLet, $tag[0], $tag[1], $raw, $d["row"][$i], $d["ids"][$i]);
                            }
                        }
                    }
                }
                foreach ($this->dat as $filtStr => $dat) {
                    if (sizeof($this->dat[$filtStr]["dat"][$datLet]["ids"]) > 0) {
                        $this->dat[$filtStr]["dat"][$datLet]["avg"] 
                            = $this->dat[$filtStr]["dat"][$datLet]["sum"
                                ]/sizeof($this->dat[$filtStr]["dat"][$datLet]["ids"]);
                    }
                }
                $this->calcTagAvg($datLet, '1');
                if (sizeof($this->tagMap) > 0) {
                    foreach ($this->tagMap as $tagLet => $t) {
                        if (isset($t["val"]) && sizeof($t["val"]) > 0) {
                            foreach ($t["val"] as $v => $tagVal) $this->calcTagAvg($datLet, $tagLet . $tagVal);
                        }
                    }
                }   
            }
        }
//echo '<br /><br /><br /><hr>'; foreach ($this->dat as $filtStr => $dat) echo 'dat: ' . $filtStr . '<br />'; echo '<br /><hr><br />';
        return true;
    }
    
    public function tblInitSimpStatRow($typ = 'sum')
    {
        $row = [ 'Total', 0 ];
        switch ($typ) {
            case 'avg': $row[0] = 'Average'; break;
            case 'min': $row[0] = 'Minimum'; break;
            case 'max': $row[0] = 'Maximum'; break;
        }
        return $row;
    }
    
    public function tblSimpStatRow($colLet, $dLet, $typ = 'sum')
    {
        $row = $this->tblInitSimpStatRow($typ);
        if (isset($this->opts["scaler"][1]) && trim($this->opts["scaler"][1]) != '') {
            $row[0] .= ' ' . $this->opts["scaler"][1];
        } elseif (isset($this->datMap[$dLet])) {
            $row[0] .= ' ' . $this->datMap[$dLet]["lab"];
        }
        $row[0] = $this->opts["datLabPrfx"] . $row[0];
        $colStr = $this->applyCurrFilt("1");
        if (isset($this->dat[$colStr]) && isset($this->dat[$colStr]["dat"][$dLet])) {
            $row[1] = $this->dat[$colStr]["dat"][$dLet][$typ];
        }
        if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
            foreach ($this->filts[$colLet]["val"] as $colVal) {
                if (!$this->isCurrHid($colLet, $colVal)) {
                    $cell = -3737;
                    $colStr = $this->applyCurrFilt($colLet . $colVal);
                    if (isset($this->dat[$colStr]) && isset($this->dat[$colStr]["dat"][$dLet])) {
                        $cell = $this->dat[$colStr]["dat"][$dLet][$typ];
                    }
                    $row[] = $cell;
                }
            }
        }
        return $row;
    }
    
    public function tblAvgTot($fltAbbr, $datAbbr, $cols = 'filt')
    {
        $this->tblOut = [];
        $fLet = $this->fAbr($fltAbbr);
        $dLet = $this->dAbr($datAbbr);
        $this->tblOut[] = $this->tblSimpStatRow($fLet, $dLet, 'avg');
        $this->tblOut[] = $this->tblSimpStatRow($fLet, $dLet);
        $this->tblApplyScale();
        return view('vendor.survloop.inc-stat-tbl-avgtot', [ "tblOut" => $this->tblOut ])->render();
    }
    
    public function tblFltRowsCalc($fltCol, $fltRow, $datAbbr, $typ = 'sum', $datLabOvr = '', $totTop = true)
    {
        $this->tblOut = [];
        if (trim($datLabOvr) != '') $this->opts["datLabOvr"] = $datLabOvr;
        $colLet = $this->fAbr($fltCol);
        $rowLet = $this->fAbr($fltRow);
        $datLet = $this->dAbr($datAbbr);
        $datSfx = ((isset($this->datMap[$datLet])) ? ' ' . $this->datMap[$datLet]["lab"] : '');
        $totCnt = $this->getFiltTot($fltCol);
        if ($totTop) {
            $row = $this->tblInitSimpStatRow($typ);
            if (trim($this->opts["datLabOvr"]) != '') $row[0] = $this->opts["datLabOvr"];
            else $row[0] .= $datSfx;
            $row[0] = $this->opts["datLabPrfx"] . $row[0];
            $colStr = $this->applyCurrFilt('1');
            if ($typ != "avg" && isset($this->dat[$colStr]) && isset($this->dat[$colStr]["dat"][$datLet]) 
                && isset($this->dat[$colStr]["dat"][$datLet][$typ])) {
                $row[1] = $this->dat[$colStr]["dat"][$datLet][$typ];
            }
            if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                foreach ($this->filts[$colLet]["val"] as $colVal) {
                    if (!$this->isCurrHid($colLet, $colVal)) {
                        $cell = -3737;
                        $colStr = $this->applyCurrFilt($colLet . $colVal);
                        if (isset($this->dat[$colStr]) && isset($this->dat[$colStr]["dat"][$datLet]) 
                            && isset($this->dat[$colStr]["dat"][$datLet][$typ])) {
                            $cell = $this->dat[$colStr]["dat"][$datLet][$typ];
                            if ($typ == "avg") $row[1] += $cell;
                        }
                        $row[] = $cell;
                    }
                }
            }
            if ($typ == "avg") $this->dat["1"]["dat"][$datLet][$typ] = $row[1];
            $this->tblOut[] = $row;
        }
        if ($rowLet != '' && isset($this->filts[$rowLet]) && sizeof($this->filts[$rowLet]["val"]) > 0) {
            foreach ($this->filts[$rowLet]["val"] as $i => $rowVal) {
                if (!$this->isCurrHid($rowLet, $rowVal) && isset($this->filts[$rowLet]["vlu"][$i]) 
                    && trim($this->filts[$rowLet]["vlu"][$i]) != '') {
                    $row = $this->tblInitSimpStatRow($typ);
                    if (trim($this->opts["datLabOvr"]) != '') $row[0] = $this->opts["datLabOvr"];
                    else $row[0] .= $datSfx;
                    $row[0] = $this->opts["datLabPrfx"] . $row[0] . ' ' . $this->filts[$rowLet]["vlu"][$i];
                    $rowStr = $this->applyCurrFilt($rowLet . $rowVal);
                    if ($typ != "avg" && isset($this->dat[$rowStr]) && isset($this->dat[$rowStr]["dat"][$datLet]) 
                        && isset($this->dat[$rowStr]["dat"][$datLet][$typ])) {
                        $row[1] = $this->dat[$rowStr]["dat"][$datLet][$typ];
                    }
                    if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                        foreach ($this->filts[$colLet]["val"] as $colVal) {
                            if (!$this->isCurrHid($colLet, $colVal)) {
                                $cell = -3737;
                                $cellStr = $this->applyCurrFilt($colLet . $colVal . '-' . $rowStr);
                                if (isset($this->dat[$cellStr]) && isset($this->dat[$cellStr]["dat"][$datLet]) 
                                    && isset($this->dat[$cellStr]["dat"][$datLet][$typ])) {
                                    $cell = $this->dat[$cellStr]["dat"][$datLet][$typ];
                                    if ($typ == "avg") $row[1] += $cell;
                                }
                                $row[] = $cell;
                            }
                        }
                    }
                    if ($typ == "avg") $this->dat[$rowStr]["dat"][$datLet][$typ] = $row[1];
                    $this->tblOut[] = $row;
                }
            }
        }
        $ret = view('vendor.survloop.inc-stat-tbl-avgtot', [ "tblOut" => $this->tblOut ])->render();
        if (trim($datLabOvr) != '') $this->opts["datLabOvr"] = '';
        return $ret;
    }
    
    public function tblFltRowsCalcDiv($fltCol, $fltRow, $datAbbr, $datAbbr2, $datLabOvr = '', $totTop = true)
    {
        $this->opts["datLabOvr"] = $datLabOvr;
        $datAbbr3 = $this->addNewDataCalc($datAbbr, $datAbbr2, '/');
        $ret = $this->tblFltRowsCalc($fltCol, $fltRow, $datAbbr3, 'sum', $datLabOvr, $totTop);
        $this->opts["datLabOvr"] = '';
        return $ret;
    }
    
    public function tblFltDatColPerc($fltCol, $fltRow, $datAbbr, $label = '')
    {
        $this->tblOut = [];
        $colLet = $this->fAbr($fltCol);
        $rowLet = $this->fAbr($fltRow);
        $datLet = $this->dAbr($datAbbr);
        $datSfx = ((isset($this->datMap[$datLet])) ? ' ' . $this->datMap[$datLet]["lab"] : '');
        if ($rowLet != '' && isset($this->filts[$rowLet]) && sizeof($this->filts[$rowLet]["val"]) > 0) {
            foreach ($this->filts[$rowLet]["val"] as $i => $rowVal) {
                if (!$this->isCurrHid($rowLet, $rowVal) && isset($this->filts[$rowLet]["vlu"][$i]) 
                    && trim($this->filts[$rowLet]["vlu"][$i]) != '') {
                    $row = [ 'Percent of ' . $datSfx . ' ' . $this->filts[$rowLet]["vlu"][$i], 0 ];
                    if (trim($label) != '') $row[0] = $label . ' ' . $this->filts[$rowLet]["vlu"][$i];
                    $row[0] = $this->opts["datLabPrfx"] . $row[0];
                    $rowStr = $this->applyCurrFilt($rowLet . $rowVal);
                    $filtStr = $this->applyCurrFilt('1');
                    if (isset($this->dat[$rowStr]) && isset($this->dat[$rowStr]["dat"][$datLet])
                        && isset($this->dat[$filtStr]["dat"][$datLet]["sum"]) 
                        && $this->dat[$filtStr]["dat"][$datLet]["sum"] > 0) {
                        $row[1] = round(100*$this->dat[$rowStr]["dat"][$datLet]["sum"
                            ]/$this->dat[$filtStr]["dat"][$datLet]["sum"]) . '%';
                    }
                    if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                        foreach ($this->filts[$colLet]["val"] as $colVal) {
                            if (!$this->isCurrHid($colLet, $colVal)) {
                                $cell = -3737;
                                $colStr = $colLet . $colVal;
                                if (isset($this->dat[$colStr]) && isset($this->dat[$colStr]["cnt"])) {
                                    $cellStr = $this->applyCurrFilt($colStr . '-' . $rowStr);
                                    if (isset($this->dat[$cellStr]) && isset($this->dat[$cellStr]["dat"][$datLet]) 
                                        && isset($this->dat[$cellStr]["dat"][$datLet]["sum"]) 
                                        && isset($this->dat[$colStr]) && isset($this->dat[$colStr]["dat"][$datLet]) 
                                        && $this->dat[$colStr]["dat"][$datLet]["sum"] > 0) {
                                        $cell = round(100*$this->dat[$cellStr]["dat"][$datLet]["sum"
                                            ]/$this->dat[$colStr]["dat"][$datLet]["sum"]) . '%';
                                    }
                                }
                                $row[] = $cell;
                            }
                        }
                    }
                    $this->tblOut[] = $row;
                }
            }
        }
        return view('vendor.survloop.inc-stat-tbl-percs', [ "tblOut" => $this->tblOut ])->render();
    }
    
    public function tblFltDatRowPerc($fltCol, $fltRow, $datAbbr, $label = '')
    {
        $this->tblOut = [];
        $colLet = $this->fAbr($fltCol);
        $rowLet = $this->fAbr($fltRow);
        $datLet = $this->dAbr($datAbbr);
        $datSfx = ((isset($this->datMap[$datLet])) ? ' ' . $this->datMap[$datLet]["lab"] : '');
        if ($rowLet != '' && isset($this->filts[$rowLet]) && sizeof($this->filts[$rowLet]["val"]) > 0) {
            foreach ($this->filts[$rowLet]["val"] as $i => $rowVal) {
                if (!$this->isCurrHid($rowLet, $rowVal) && isset($this->filts[$rowLet]["vlu"][$i]) 
                    && trim($this->filts[$rowLet]["vlu"][$i]) != '') {
                    $row = [ 'Percent of ' . $datSfx . ' ' . $this->filts[$rowLet]["vlu"][$i], '100%' ];
                    if (trim($label) != '') $row[0] = $label . ' ' . $this->filts[$rowLet]["vlu"][$i];
                    $row[0] = $this->opts["datLabPrfx"] . $row[0];
                    $rowStr = $this->applyCurrFilt($rowLet . $rowVal);
                    if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                        foreach ($this->filts[$colLet]["val"] as $colVal) {
                            if (!$this->isCurrHid($colLet, $colVal)) {
                                $cell = -3737;
                                $cellStr = $this->applyCurrFilt($colLet . $colVal . '-' . $rowStr);
                                if (isset($this->dat[$cellStr]) && isset($this->dat[$cellStr]["dat"][$datLet]) 
                                    && isset($this->dat[$cellStr]["dat"][$datLet]["sum"]) 
                                    && isset($this->dat[$rowStr]) && isset($this->dat[$rowStr]["dat"][$datLet]) 
                                    && $this->dat[$rowStr]["dat"][$datLet]["sum"] > 0) {
                                    $cell = round(100*$this->dat[$cellStr]["dat"][$datLet]["sum"
                                        ]/$this->dat[$rowStr]["dat"][$datLet]["sum"]) . '%';
                                }
                                $row[] = $cell;
                            }
                        }
                    }
                    $this->tblOut[] = $row;
                }
            }
        }
        return view('vendor.survloop.inc-stat-tbl-percs', [ "tblOut" => $this->tblOut ])->render();
    }
    
    public function tblFltDatRatio2Col($fltCol, $fltRow, $datAbbr, $ratioVal, $label = '', $totTop = true)
    {
        $this->tblOut = [];
        $colLet = $this->fAbr($fltCol);
        $ratioStr = $this->applyCurrFilt($colLet . $ratioVal);
        $rowLet = $this->fAbr($fltRow);
        $datLet = $this->dAbr($datAbbr);
        $datSfx = ((isset($this->datMap[$datLet])) ? ' ' . $this->datMap[$datLet]["lab"] : '');
        if ($totTop) {
            $row = [ 'Ratio' . $datSfx, 0 ];
            if (trim($label) != '') $row[0] = $label;
            $row[0] = $this->opts["datLabPrfx"] . $row[0];
            if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                foreach ($this->filts[$colLet]["val"] as $colVal) {
                    if (!$this->isCurrHid($colLet, $colVal)) {
                        $cell = -3737;
                        $colStr = $this->applyCurrFilt($colLet . $colVal);
                        if (isset($this->dat[$colStr]) && isset($this->dat[$colStr]["dat"][$datLet]) 
                            && isset($this->dat[$colStr]["dat"][$datLet]["sum"]) 
                            && isset($this->dat[$ratioStr]) && isset($this->dat[$ratioStr]["dat"][$datLet]) 
                            && $this->dat[$ratioStr]["dat"][$datLet]["sum"] > 0) {
                            $cell = round(100*$this->dat[$colStr]["dat"][$datLet]["sum"
                                ]/$this->dat[$ratioStr]["dat"][$datLet]["sum"]);
                            $row[1] += $cell;
                            $cell .= '%';
                        }
                        $row[] = $cell;
                    }
                }
            }
            $row[1] .= '%';
            $this->tblOut[] = $row;
        }
        if ($rowLet != '' && isset($this->filts[$rowLet]) && sizeof($this->filts[$rowLet]["val"]) > 0) {
            foreach ($this->filts[$rowLet]["val"] as $i => $rowVal) {
                if (isset($this->filts[$rowLet]["vlu"][$i]) && trim($this->filts[$rowLet]["vlu"][$i]) != '') {
                    $row = [ 'Ratio' . $datSfx . ' ' . $this->filts[$rowLet]["vlu"][$i], 0 ];
                    if (trim($label) != '') $row[0] = $label . ' ' . $this->filts[$rowLet]["vlu"][$i];
                    $row[0] = $this->opts["datLabPrfx"] . $row[0];
                    $rowStr = $this->applyCurrFilt($rowLet . $rowVal);
                    if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                        foreach ($this->filts[$colLet]["val"] as $colVal) {
                            if (!$this->isCurrHid($colLet, $colVal)) {
                                $cell = -3737;
                                $cellStr = $this->applyCurrFilt($colLet . $colVal . '-' . $rowStr);
                                if (isset($this->dat[$cellStr]) && isset($this->dat[$cellStr]["dat"][$datLet]) 
                                    && isset($this->dat[$cellStr]["dat"][$datLet]["sum"]) 
                                    && isset($this->dat[$colStr]) && isset($this->dat[$colStr]["dat"][$datLet]) 
                                    && $this->dat[$colStr]["dat"][$datLet]["sum"] > 0) {
                                    $cell = round(100*$this->dat[$cellStr]["dat"][$datLet]["sum"
                                        ]/$this->dat[$rowStr . '-' . $ratioStr]["dat"][$datLet]["sum"]);
                                    $row[1] += $cell;
                                    $cell .= '%';
                                }
                                $row[] = $cell;
                            }
                        }
                    }
                    $row[1] .= '%';
                    $this->tblOut[] = $row;
                }
            }
        }
        return view('vendor.survloop.inc-stat-tbl-percs', [ "tblOut" => $this->tblOut ])->render();
    }
    
    public function tblAvgTotScale($fltAbbr, $datAbbr, $datScale = 1, $datLabel = '', $cols = 'filt')
    {
        $this->opts["scaler"][0] = $datScale;
        $this->opts["scaler"][1] = $datLabel;
        $ret = $this->tblAvgTot($fltAbbr, $datAbbr, $cols);
        $this->opts["scaler"][0] = 1;
        $this->opts["scaler"][1] = '';
        return $ret;
    }
    
    public function tblApplyScale()
    {
        if (isset($this->opts["scaler"][0]) && $this->opts["scaler"][0] > 0 && $this->opts["scaler"][0] != 1 
            && sizeof($this->tblOut) > 0) {
            foreach ($this->tblOut as $i => $row) {
                if (sizeof($row) > 0) {
                    foreach ($row as $j => $cell) {
                        if ($j > 0) $this->tblOut[$i][$j] = $this->opts["scaler"][0]*$cell;
                    }
                }
            }
        }
        return true;
    }
    
    public function applyCurrFilt($filtStr = '')
    {
        $curr = $GLOBALS["SL"]->mexplode('-', $filtStr);
        if (sizeof($this->fltCurr) > 0) {
            foreach ($this->fltCurr as $currLet => $currVal) {
                if (!in_array($currLet . $currVal, $curr)) $curr[] = $currLet . $currVal;
            }
        }
        asort($curr);
        if (sizeof($curr) > 1) {
            if ($curr[0] == '1') unset($curr[0]);
            elseif ($curr[sizeof($curr)-1] == '1') unset($curr[sizeof($curr)-1]);
        }
        return implode('-', $curr);
    }
    
    public function getFiltTot($fltAbbr = '')
    {
        $tot = 0;
        $fLet = $this->fAbr($fltAbbr);
        if (isset($this->filts[$fLet]) && sizeof($this->filts[$fLet]["val"]) > 0) {
            foreach ($this->filts[$fLet]["val"] as $val) {
                $filtStr = $this->applyCurrFilt($fLet . $val);
                if (isset($this->dat[$filtStr]) && isset($this->dat[$filtStr]["cnt"])) {
                    $tot += $this->dat[$filtStr]["cnt"];
                }
            }
        }
        return $tot;
    }
    
    public function getFiltValTot($fltAbbr = '', $fltVal = -3737)
    {
        $fLet = $this->fAbr($fltAbbr);
        if (isset($this->filts[$fLet]) && sizeof($this->filts[$fLet]["val"]) > 0) {
            foreach ($this->filts[$fLet]["val"] as $v => $val) {
                $filtStr = $this->applyCurrFilt($fLet . $val);
                if ($val == $fltVal && isset($this->dat[$filtStr]) && isset($this->dat[$filtStr]["cnt"])) {
                    return $this->dat[$filtStr]["cnt"];
                }
            }
        }
        return 0;
    }
    
    public function tblPercHas($fltCol, $fltRow, $tot = 'filt')
    {
        $this->tblOut = [];
        $colLet = $this->fAbr($fltCol);
        $rowLet = $this->fAbr($fltRow);
        $totCnt = $this->getFiltTot($fltCol);
        if ($rowLet != '' && isset($this->filts[$rowLet]) && sizeof($this->filts[$rowLet]["val"]) > 0) {
            foreach ($this->filts[$rowLet]["val"] as $i => $rowVal) {
                if (!$this->isCurrHid($rowLet, $rowVal) && isset($this->filts[$rowLet]["vlu"][$i]) 
                    && trim($this->filts[$rowLet]["vlu"][$i]) != '') {
                    $row = [ $this->filts[$rowLet]["vlu"][$i], 0 ];
                    $row[0] = $this->opts["datLabPrfx"] . $row[0];
                    $rowStr = $this->applyCurrFilt($rowLet . $rowVal);
                    if (isset($this->dat[$rowStr]) && isset($this->dat[$rowStr]["cnt"]) && $totCnt > 0) {
                        $row[1] = round(100*$this->dat[$rowStr]["cnt"]/$totCnt);
                        if ($row[1] > 0) {
                            $row[1] = $row[1] . '% <sub class="slGrey">' 
                                . number_format($this->dat[$rowStr]["cnt"]) . '</sub>';
                        }
                    }
                    if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                        foreach ($this->filts[$colLet]["val"] as $colVal) {
                            if (!$this->isCurrHid($colLet, $colVal)) {
                                $cell = -3737;
                                $colStr = $this->applyCurrFilt($colLet . $colVal);
                                $cellStr = $this->applyCurrFilt($colLet . $colVal . '-' . $rowStr);
                                if (isset($this->dat[$colStr]) && isset($this->dat[$cellStr])
                                    && isset($this->dat[$colStr]["cnt"]) && intVal($this->dat[$colStr]["cnt"]) > 0) {
                                    $cell = round(100*$this->dat[$cellStr]["cnt"]/$this->dat[$colStr]["cnt"]);
                                    if ($cell > 0) {
                                        $cell = $cell . '% <sub class="slGrey">' 
                                            . number_format($this->dat[$cellStr]["cnt"]) . '</sub>';
                                    }
                                }
                                $row[] = $cell;
                            }
                        }
                    }
                    $this->tblOut[] = $row;
                }
            }
        }
        return view('vendor.survloop.inc-stat-tbl-percs', [ "tblOut" => $this->tblOut ])->render();
    }
    
    public function tblFltRowsBlksCalc($fltCol, $fltRow, $fltBlk, $datAbbr, $typ = 'sum', $headRows = true)
    {
        $blkLet = $this->fAbr($fltBlk);
        $ret = '';
        if (isset($this->filts[$blkLet]) && sizeof($this->filts[$blkLet]["val"]) > 0) {
            foreach ($this->filts[$blkLet]["val"] as $i => $blkVal) {
                if (!$this->isCurrHid($blkLet, $blkVal)) {
                    $this->addCurrFilt($fltBlk, $blkVal);
                    $this->opts["datLabPrfx"] = $this->fValLab($fltBlk, $blkVal) . ' ';
                    $ret .= $this->tblSpacerRow($fltCol) . (($headRows) ? $this->tblHeaderRow($fltCol) : '')
                        . $this->tblFltRowsCalc($fltCol, $fltRow, $datAbbr, $typ);
                    $this->opts["datLabPrfx"] = '';
                }
            }
        }
        $this->delRecFilt($fltBlk);
        return $ret;
    }
    
    public function piePercHas($fltCol, $fltRow, $tot = 'filt')
    {
        if (sizeof($this->tblOut) == 0) $retTable = $this->tblPercHas($fltCol, $fltRow, $tot);
        $colLet = $this->fAbr($fltCol);
        $rowLet = $this->fAbr($fltRow);
        $ret = '<div class="row">';
        foreach ($this->filts[$colLet]["val"] as $i => $colVal) {
            $ret .= '<div class="col-md-6 pB20"><h3 class="m0">' . $this->filts[$colLet]["vlu"][$i] . '</h3>' 
                . $this->piePercHasCore($colLet, $rowLet, $i, $tot) . '</div>'
                . (($i > 0 && $i%2 == 1) ? '</div><div class="row">' : '');
        }
        return $ret . '</div>';
    }
    
    public function piePercHasCore($colLet, $rowLet, $i, $tot = 'filt')
    {
        $data = [];
        if ($rowLet != '' && isset($this->filts[$rowLet]) && sizeof($this->filts[$rowLet]["val"]) > 0) {
            foreach ($this->filts[$rowLet]["val"] as $j => $rowVal) {
                if (!$this->isCurrHid($rowLet, $rowVal) && isset($this->tblOut[$j][(2+$i)]) 
                    && isset($this->tblOut[$j][0])) {
                    $count = $this->getSub($this->tblOut[$j][(2+$i)]);
                    if (intVal($count) > 0) {
                        $data[] = [
                            $count,
                            $this->tblOut[$j][0],
                            "'" . $GLOBALS["SL"]->printColorFadeHex(($j*0.1), 
                                $GLOBALS["SL"]->getCssColor('color-main-on'), 
                                $GLOBALS["SL"]->getCssColor('color-main-bg')) . "'"
                            ];
                    }
                }
            }
        }
        return $this->pieView($data);
    }
    
    public function piePercCntCore($fltCol, $fade = 0.1, $colors = [], $hgt = null)
    {
        $colLet = $this->fAbr($fltCol);
        $data = [];
        if ($colLet != '' && isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
            foreach ($this->filts[$colLet]["val"] as $i => $val) {
                if (isset($this->dat[$colLet . $val]) && isset($this->dat[$colLet . $val]["cnt"])
                    && intVal($this->dat[$colLet . $val]["cnt"]) > 0) {
                    if (!isset($colors[$i])) {
                        $colors[$i] = $GLOBALS["SL"]->printColorFadeHex(($i*$fade), 
                            $GLOBALS["SL"]->getCssColor('color-main-on'), 
                            $GLOBALS["SL"]->getCssColor('color-main-bg'));
                    }
                    $data[] = [
                        $this->dat[$colLet . $val]["cnt"],
                        $this->filts[$colLet]["vlu"][$i],
                        "'" . $colors[$i] . "'"
                        ];
                }
            }
        }
        return $this->pieView($data);
    }
    
    public function boxWhisk($datAbbr, $fltCol, $hgt = '100%')
    {
        $GLOBALS["SL"]->x["needsCharts"] = true;
        $GLOBALS["SL"]->x["needsViolinPlot"] = true;
        
        $dLet = $this->dAbr($datAbbr);
        $data = [];
        return view('vendor.survloop.graph-box-whisker', [ "data" => $data, "hgt" => $hgt ])->render();
    }
    
    public function pieView($data, $hgt = null)
    {
        $GLOBALS["SL"]->x["needsCharts"] = true;
        return view('vendor.survloop.graph-pie', [ "pieData" => $data, "hgt" => $hgt ])->render();
    }
    
    public function pieTblPercHas($fltCol, $fltRow, $tot = 'filt')
    {
        $table = $this->tblPercHas($fltCol, $fltRow, $tot);
        return $this->piePercHas($fltCol, $fltRow, $tot) 
            . '<div class="p5"> </div><table border=0 class="table table-striped w100">'
            . $this->tblHeaderRow($fltCol) . $table . '</table>';
    }
    
    public function printTblPercHas($fltCol, $fltRow, $tot = 'filt')
    {
        return '<table border=0 class="table table-striped w100">'
            . $this->tblHeaderRow($fltCol) . $this->tblPercHas($fltCol, $fltRow, $tot) . '</table>';
        
    }
    
    public function pieTblBlksPercHas($fltCol, $fltRow, $fltBlk, $header = '', $tot = 'filt')
    {
        $blkLet = $this->fAbr($fltBlk);
        $ret = '';
        if (isset($this->filts[$blkLet]) && sizeof($this->filts[$blkLet]["val"]) > 0) {
            foreach ($this->filts[$blkLet]["val"] as $i => $blkVal) {
                if (!$this->isCurrHid($blkLet, $blkVal)) {
                    $this->addCurrFilt($fltBlk, $blkVal);
                    $blkLabel = $this->fValLab($fltBlk, $blkVal);
                    $ret .= '<div class="p20"></div><h2 class="slBlueDark">' . $header . $blkLabel . '</h2>'
                        . $this->pieTblPercHas($fltCol, $fltRow, $tot);
                }
            }
        }
        $this->delRecFilt($fltBlk);
        return $ret;
    }
    
    public function getDatCnt($fStr = '1')
    {
        if (isset($this->dat[$fStr])) return $this->dat[$fStr]["cnt"];
        return 0;
    }
    
    public function getDatCntFval($fltAbbr, $fltVal)
    {
        return $this->getDatCnt($this->fAbr($fltAbbr) . $fltVal);
    }
    
    public function getDatTot($datAbbr, $fStr = '1', $typ = 'sum')
    {
        $dLet = $this->dAbr($datAbbr);
        if ($dLet != '' && isset($this->dat[$fStr]) && isset($this->dat[$fStr]["dat"][$dLet]) 
            && isset($this->dat[$fStr]["dat"][$dLet][$typ])) {
            return $this->dat[$fStr]["dat"][$dLet][$typ];
        }
        return 0;
    }
    
    public function getDatTotFval($datAbbr, $fltAbbr, $fltVal, $typ = 'sum')
    {
        return $this->getDatTot($datAbbr, $this->fAbr($fltAbbr) . $fltVal, $typ);
    }
    
    public function tblPercHasDat($fltCol, $datTypes = [])
    {
        $this->tblOut = [];
        $colLet = $this->fAbr($fltCol);
        $totCnt = $this->getFiltTot($fltCol);
        if (sizeof($datTypes) > 0) {
            foreach ($datTypes as $datAbbr) {
                $dLet = $this->dAbr($datAbbr);
                if (isset($this->datMap[$dLet]) && trim($this->datMap[$dLet]["lab"]) != '') {
                    $row = [ $this->datMap[$dLet]["lab"], 0 ];
                    $row[0] = $this->opts["datLabPrfx"] . $row[0];
                    $filtStr = $this->applyCurrFilt('1');
                    if (isset($this->dat[$filtStr]["dat"][$dLet]["sum"]) && $totCnt > 0) {
                        $row[1] = round(100*$this->dat[$filtStr]["dat"][$dLet]["sum"]/$totCnt);
                        if ($row[1] > 0) {
                            $row[1] = $row[1] . '% <sub class="slGrey">' 
                                . number_format($this->dat[$filtStr]["dat"][$dLet]["sum"]) . '</sub>';
                        }
                    }
                    if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                        foreach ($this->filts[$colLet]["val"] as $colVal) {
                            if (!$this->isCurrHid($colLet, $colVal)) {
                                $cell = -3737;
                                $colStr = $this->applyCurrFilt($colLet . $colVal);
                                if (isset($this->dat[$colStr]["dat"][$dLet]["sum"]) && $this->dat[$colStr]["cnt"] > 0) {
                                    $cell = round(100*$this->dat[$colStr]["dat"][$dLet]["sum"
                                        ]/$this->dat[$colStr]["cnt"]);
                                    if ($cell > 0) {
                                        $cell = $cell . '% <sub class="slGrey">' 
                                            . number_format($this->dat[$colStr]["dat"][$dLet]["sum"]) . '</sub>';
                                    }
                                }
                                $row[] = $cell;
                            }
                        }
                    }
                    $this->tblOut[] = $row;
                }
            }
        }
        return view('vendor.survloop.inc-stat-tbl-percs', [ "tblOut" => $this->tblOut ])->render();
    }
    
    public function tblFltBlksPercHasDat($fltCol, $fltBlk, $datTypes = [])
    {
        $ret = '';
        $colLet = $this->fAbr($fltCol);
        $blkLet = $this->fAbr($fltBlk);
        if (sizeof($datTypes) > 0 && isset($this->filts[$colLet]) && isset($this->filts[$blkLet])
            && sizeof($this->filts[$blkLet]["val"]) > 0) {
            foreach ($this->filts[$blkLet]["val"] as $b => $blkVal) {
                if (!$this->isCurrHid($blkLet, $blkVal)) {
                    if ($b > 0) $ret .= $this->tblSpacerRow($fltCol);
                    $blkStr = $this->applyCurrFilt($blkLet . $blkVal);
                    $blkLabel = $this->fValLab($fltBlk, $blkVal);
                    $blkTot = $this->getFiltValTot($fltBlk, $blkVal);
                    $this->tblOut = [];
                    foreach ($datTypes as $datAbbr) {
                        $dLet = $this->dAbr($datAbbr);
                        if (isset($this->datMap[$dLet]) && trim($this->datMap[$dLet]["lab"]) != '') {
                            $row = [ $blkLabel . ' ' . $this->datMap[$dLet]["lab"], 0 ];
                            $row[0] = $this->opts["datLabPrfx"] . $row[0];
                            if (isset($this->dat[$blkStr]["dat"][$dLet]["sum"]) && $blkTot > 0) {
                                $row[1] = round(100*$this->dat[$blkStr]["dat"][$dLet]["sum"]/$blkTot);
                                if ($row[1] > 0) {
                                    $row[1] = $row[1] . '% <sub class="slGrey">' 
                                        . number_format($this->dat[$blkStr]["dat"][$dLet]["sum"]) . '</sub>';
                                }
                            }
                            if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                                foreach ($this->filts[$colLet]["val"] as $colVal) {
                                    if (!$this->isCurrHid($colLet, $colVal)) {
                                        $cell = -3737;
                                        $str = $this->applyCurrFilt($blkStr . '-' . $colLet . $colVal);
                                        if (isset($this->dat[$str]["dat"][$dLet]["sum"]) && $this->dat[$str]["cnt"] > 0) {
                                            $cell = round(100*$this->dat[$str]["dat"][$dLet]["sum"]/$this->dat[$str]["cnt"]);
                                            if ($cell > 0) {
                                                $cell = $cell . '% <sub class="slGrey">' 
                                                    . number_format($this->dat[$str]["dat"][$dLet]["sum"]) . '</sub>';
                                            }
                                        }
                                        $row[] = $cell;
                                    }
                                }
                            }
                            $this->tblOut[] = $row;
                        }
                    }
                }
                $ret .= view('vendor.survloop.inc-stat-tbl-percs', [ "tblOut" => $this->tblOut ])->render();
            }
        }
        return $ret;
    }
    
    public function tblHeaderRow($fltAbbr)
    {
        $ret = '<tr><th>&nbsp;</th><th class="brdRgt">Total</th>';
        $fLet = $this->fAbr($fltAbbr);
        if ($fLet != '' && isset($this->filts[$fLet]) && sizeof($this->filts[$fLet]["val"]) > 0) {
            foreach ($this->filts[$fLet]["val"] as $i => $val) {
                $lab = $val;
                if (isset($this->filts[$fLet]["vlu"][$i]) && trim($this->filts[$fLet]["vlu"][$i]) != '') {
                    $lab = $this->filts[$fLet]["vlu"][$i];
                } else {
                    $defVal = $GLOBALS["SL"]->def->getValById($val);
                    if (trim($defVal) != '') $lab = $defVal;
                }
                $ret .= '<th>' . $lab . '</th>';
            }
        }
        return $ret . '</tr>';
    }
    
    public function tblSpacerRow($fltAbbr)
    {
        $datCols = 2;
        $fLet = $this->fAbr($fltAbbr);
        if ($fLet != '' && isset($this->filts[$fLet])) $datCols += sizeof($this->filts[$fLet]["val"]);
        return '<tr><td colspan=' . $datCols . ' > </td></tr>';
    }
    
    public function tblTagRows($datAbbr, $tagAbbr = '1', $typ = 'avg')
    {
        $this->tblOut = [];
        $datLet = $this->dAbr($datAbbr);
        if ($tagAbbr == '1') {
            $row = ['All', 0, 0];
            for ($i = 0; $i < sizeof($this->datMap[$datLet]["row"]); $i++) $row[] = 0;
            if (isset($this->tagTot[$datLet]['1']) && isset($this->tagTot[$datLet]['1'][$typ]["raw"])) {
                $row[1] = $this->tagTot[$datLet]['1'][$typ]["raw"];
                if (sizeof($this->tagTot[$datLet]['1'][$typ]["row"]) > 0) {
                    foreach ($this->tagTot[$datLet]['1'][$typ]["row"] as $j => $r) $row[(2+$j)] = $r;
                }
                $row[sizeof($row)-1] = sizeof($this->tagTot[$datLet]['1']["sum"]["ids"]);
            }
            $this->tblOut[] = $row;
        } else {
            $tagLet = $this->tAbr($tagAbbr);
            if ($datLet != '' && isset($this->datMap[$datLet]) && isset($this->tagTot[$datLet]) 
                && $tagLet != '' && isset($this->tagMap[$tagLet]) && isset($this->tagMap[$tagLet]["val"]) 
                && sizeof($this->tagMap[$tagLet]["val"]) > 0) {
                foreach ($this->tagMap[$tagLet]["val"] as $v => $tagVal) {
                    $row = ['', 0, 0];
                    for ($i = 0; $i < sizeof($this->datMap[$datLet]["row"]); $i++) $row[] = 0;
                    $tStr = $tagLet . $tagVal;
                    $row[0] = $this->tagMap[$tagLet]["vlu"][$v];
                    if (isset($this->tagTot[$datLet][$tStr]) && isset($this->tagTot[$datLet][$tStr][$typ]["raw"])) {
                        $row[1] = $this->tagTot[$datLet][$tStr][$typ]["raw"];
                        if (sizeof($this->tagTot[$datLet][$tStr][$typ]["row"]) > 0) {
                            foreach ($this->tagTot[$datLet][$tStr][$typ]["row"] as $j => $r) $row[(2+$j)] = $r;
                        }
                        $row[sizeof($row)-1] = sizeof($this->tagTot[$datLet][$tStr]["sum"]["ids"]);
                    }
                    $this->tblOut[] = $row;
                }
            }
        }
//echo '<pre>'; print_r($this->tagTot['a']); echo '</pre>';
        $ret = view('vendor.survloop.inc-stat-tag-avgtot', [ "tblOut" => $this->tblOut ])->render();
        return $ret;
    }
    
    public function tblTagHeaderRow($datAbbr)
    {
        $ret = '';
        $datLet = $this->dAbr($datAbbr);
        if ($datLet != '' && isset($this->datMap[$datLet]) && sizeof($this->datMap[$datLet]["row"]) > 0) {
            $ret .= '<tr><th>&nbsp;</th><th class="brdRgt">' . $this->datMap[$datLet]["lab"] 
                . ((trim($this->datMap[$datLet]["unt"]) != '') ? ' <span class="slGrey fPerc80">' 
                    . $this->datMap[$datLet]["unt"] . '</span>' : '') . '</th>';
            foreach ($this->datMap[$datLet]["row"] as $r) {
                if (is_array($r) && sizeof($r) == 2) {
                    $ret .= '<th>' . $r[0] . ((trim($r[1]) != '') ? ' <span class="slGrey fPerc80">' . $r[1] . '</span>'
                        : '') . '</th>';
                } else {
                    $ret .= '<th>' . $r . '</th>';
                }
            }
            $ret .= '<th class="brdLft slGrey">Count</th></tr>';
        }
        return $ret;
    }
    
    public function tblTagSpacerRow($datAbbr)
    {
        $datCols = 3;
        $datLet = $this->dAbr($datAbbr);
        if ($datLet != '' && isset($this->datMap[$datLet])) $datCols += sizeof($this->datMap[$datLet]["row"]);
        return '<tr><td colspan=' . $datCols . ' > </td></tr>';
    }
    
    public function calcStats()
    {
        $this->calcAllAvgs();
        return true;
    }
    
    protected function stripPerc($str)
    {
        $prcPos = strpos($str, '%');
        if ($prcPos > 0) return substr($str, 0, $prcPos);
        return $str;
    }
    
    protected function getSub($str)
    {
        $subPos = strpos($str, '<sub');
        if ($subPos > 0) {
            $subPosEnd = strpos($str, '>', $subPos);
            if ($subPosEnd > 0) {
                $subPosEnd2 = strpos($str, '<', $subPosEnd);
                if ($subPosEnd2 > 0) {
                    return intVal(substr($str, (1+$subPosEnd), ($subPosEnd2-$subPosEnd-1)));
                }
            }
        }
        return 0;
    }
        
    public function dumpStat()
    {
echo 'filts:<pre>'; print_r($this->filts); echo '</pre>datMap:<pre>'; print_r($this->datMap); echo '</pre>fltCurr:<pre>'; print_r($this->fltCurr); echo '</pre>raw:<pre>'; print_r($this->raw); echo '</pre>dat:<pre>'; print_r($this->dat); echo '</pre>';
    }
    
}