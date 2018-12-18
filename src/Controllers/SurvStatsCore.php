<?php
/**
  * SurvStatsCore provides simpler foundations for SurvStats to collect data set calculations.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

class SurvStatsCore
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
    
    public $v       = [];
    public $isExcel = false;
    
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
                if ($abbr == $filt["abr"]) {
                    return $let;
                }
            }
        }
        return '';
    }
    
    public function fVals($abbr = '')
    {
        return $this->fLetVals($this->fAbr($abbr));
    }
    
    public function fLetVals($fLet = '')
    {
        if (isset($this->filts[$fLet])) {
            return $this->filts[$fLet]["val"];
        }
        return [];
    }
    
    public function fValLab($abbr = '', $filtVal = -3737)
    {
        if (sizeof($this->filts) > 0 && $abbr != '') {
            $fLet = $this->fAbr($abbr);
            if (sizeof($this->filts[$fLet]["val"]) > 0) {
                foreach ($this->filts[$fLet]["val"] as $v => $val) {
                    if ($filtVal == $val) {
                        return $this->filts[$fLet]["vlu"][$v];
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
        return true;
    }
    
    public function tAbr($abbr = '')
    {
        if (sizeof($this->tagMap) > 0 && $abbr != '') {
            foreach ($this->tagMap as $let => $tag) {
                if ($abbr == $tag["abr"]) {
                    return $let;
                }
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
                if ($abbr == $d["abr"]) {
                    return $let;
                }
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
            if ($let == $lastLet) {
                $foundLet = 1;
            } elseif ($foundLet == 1) {
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
    
    public function loadAbbrVals($abbrVals = [])
    {
        $this->fltCurr = [];
        if (sizeof($abbrVals) > 0) {
            foreach ($abbrVals as $abbr => $val) {
                if (sizeof($this->filts) > 0) {
                    foreach ($this->filts as $let => $filt) {
                        if ($abbr == $filt["abr"]) {
                            $this->fltCurr[$let] = $val;
                        }
                    }
                }
            }
        }
        return $this->fltCurr;
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
                        if (!in_array($tmpStr, $strs)) {
                            $xtras[] = $tmpStr;
                        }
                    }
                }
                if (sizeof($xtras) > 0) {
                    foreach ($xtras as $tmpStr) {
                        $strs[] = $tmpStr;
                    }
                }
            }
        }
        return $strs;
    }
    
    public function addCurrFilt($abbr = '', $value = -3737)
    {
        if (sizeof($this->filts) > 0 && $value != -3737) {
            foreach ($this->filts as $let => $filt) {
                if ($abbr == $filt["abr"]) {
                    $this->fltCurr[$let] = $value;
                }
            }
        }
        return true;
    }
    
    public function addCurrHide($abbr = '', $value = -3737)
    {
        if (sizeof($this->filts) > 0 && $value != -3737) {
            foreach ($this->filts as $let => $filt) {
                if ($abbr == $filt["abr"]) {
                    $this->hidCurr[$let] = $value;
                }
            }
        }
        return '';
    }
    
    public function delCurrHide($abbr = '')
    {
        if (sizeof($this->filts) > 0) {
            foreach ($this->filts as $let => $filt) {
                if ($abbr == $filt["abr"]) {
                    unset($this->hidCurr[$let]);
                }
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
            foreach ($this->loadStrs($this->fltCurr) as $str) {
                $this->addRecCnt($str, $recID);
            }
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
            foreach ($this->fltCurr as $let => $val) {
                $ret .= (($let != 'a') ? '-' : '') . $let . $val;
            }
        }
        return $ret;
    }
    
    public function addRecFilts($abbrVals = [], $replace = 0)
    {
        if (sizeof($abbrVals) > 0) {
            foreach ($abbrVals as $abbr => $val) {
                $this->addRecFilt($abbr, $val, $replace);
            }
        }
        return true;
    }
    
    public function delRecFilt($abbr = '')
    {
        if (sizeof($this->filts) > 0) {
            foreach ($this->filts as $let => $filt) {
                if ($abbr == $filt["abr"] && isset($this->fltCurr[$let])) {
                    unset($this->fltCurr[$let]);
                }
            }
        }
        return true;
    }
    
    public function resetRecFilt()
    {
        $this->fltCurr = [];
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
    
    protected function stripPerc($str)
    {
        $prcPos = strpos($str, '%');
        if ($prcPos > 0) {
            return substr($str, 0, $prcPos);
        }
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
        
    public function __toString()
    {
        ob_start();
        echo 'filts:<pre>';
        print_r($this->filts);
        echo '</pre>datMap:<pre>';
        print_r($this->datMap);
        echo '</pre>fltCurr:<pre>';
        print_r($this->fltCurr);
        echo '</pre>raw:<pre>';
        print_r($this->raw);
        echo '</pre>dat:<pre>';
        print_r($this->dat);
        echo '</pre>';
        $out1 = ob_get_contents();
        ob_end_clean();
        return $out1;
    }
    
}