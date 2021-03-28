<?php
/**
  * SurvStatsFilts provides simpler foundations for SurvStats to collect data set calculations.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.24
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

use RockHopSoft\Survloop\Controllers\Stats\SurvStatsCore;

class SurvStatsFilts extends SurvStatsCore
{
    public $filts    = [];
    public $tagMap   = [];
    public $tagTot   = [];
    public $recFlts  = [];
    public $fltCurr  = [];
    public $hidCurr  = [];
    public $tblOut   = [];
    public $tblGraph = [];
    public $hasCols  = false;

    public function addFilt($abbr = '', $label = '', $values = [], $valLab = [])
    {
        $let = chr(97+(sizeof($this->filts))); // assign a, b, c,..
        $vals = $values;
        if (sizeof($values) > 0 && isset($values[0]->def_id)) {
            $vals = $valLab = [];
            foreach ($values as $def) {
                $vals[]   = $def->def_id;
                $valLab[] = $def->def_value;
            }
        }
        $this->filts[$let] = [
            "abr" => $abbr,
            "lab" => $label,
            "val" => $vals,
            "vlu" => $valLab
        ];
        return true;
    }

    public function addFiltArr($abbr = '', $label = '', $valuesLabels = [])
    {
        return $this->addFilt($abbr, $label, $valuesLabels[0], $valuesLabels[1]);
    }

    public function addFiltStates($abbr = '', $label = '')
    {
        $values = $valLab = [];
        $states = $GLOBALS["SL"]->getStates();
        foreach ($states as $abbr => $name) {
            $values[] = $abbr;
            $valLab[] = $name;
        }
        return $this->addFilt($abbr, $label, $values, $valLab);
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

    public function tblHasCols($fLet = '')
    {
        return ($fLet != ''
            && isset($this->filts[$fLet])
            && sizeof($this->filts[$fLet]["val"]) > 0);
    }

    protected function hasFltDatTyp($filtStr, $datLet, $typ)
    {
        return (isset($this->dat[$filtStr])
            && isset($this->dat[$filtStr]["dat"][$datLet])
            && isset($this->dat[$filtStr]["dat"][$datLet][$typ]));
    }

    protected function getFltDatTypVal($filtStr, $datLet, $typ)
    {
        $val = null;
        if ($this->hasFltDatTyp($filtStr, $datLet, $typ)) {
            $val = $this->dat[$filtStr]["dat"][$datLet][$typ];
        }
        return $val;
    }

    protected function getTwoFltDatTypVal($fLet, $fVal, $filtStr, $datLet, $typ)
    {
        $val = null;
        $tmpStr = $this->applyCurrFilt($fLet . $fVal . '-' . $filtStr);
        if ($this->hasFltDatTyp($tmpStr, $datLet, $typ)) {
            $val = $this->dat[$tmpStr]["dat"][$datLet][$typ];
        }
        return $val;
    }

    public function addTag($abbr = '', $label = '', $values = [], $valLab = [])
    {
        $let = chr(97+(sizeof($this->tagMap))); // assign a, b, c,..
        $vals = $values;
        if (sizeof($values) > 0 && isset($values[0]->def_id)) {
            $vals = $valLab = [];
            foreach ($values as $def) {
                $vals[]   = $def->def_id;
                $valLab[] = $def->def_value;
            }
        }
        $this->tagMap[$let] = [
            "abr" => $abbr,
            "lab" => $label,
            "val" => $vals,
            "vlu" => $valLab
        ];
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
                $this->raw[$datLet] = [
                    "raw" => [],
                    "flt" => [],
                    "ids" => [],
                    "tag" => [],
                    "row" => []
                ];
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
                if (!in_array($fullStr, $strs)) {
                    $strs[] = $fullStr;
                }
                if (!in_array($fLet . $fVal, $strs)) {
                    $strs[] = $fLet . $fVal;
                }
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
        return $this->getCurrFiltOr1();
    }

    public function delCurrFilt($abbr = '')
    {
        $this->delRecFilt($abbr);
        return $this->getCurrFiltOr1();
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
        return ($val != -3737
            && trim($let) != ''
            && isset($this->hidCurr[$let])
            && $this->hidCurr[$let] == $val);
    }

    public function addRecFilt($abbr = '', $value = -3737, $recID = -3, $replace = 1)
    {
        $recLet = '';
        if ($replace == 1) {
            $this->delRecFilt($abbr);
        }
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

    public function getCurrFiltOr1()
    {
        $ret = $this->getCurrFilt();
        if (trim($ret) != '') {
            return $ret;
        }
        return '1';
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

    public function getTblTypeName($typ = 'sum')
    {
        switch ($typ) {
            case 'avg': return 'Average';
                break;
            case 'min': return 'Minimum';
                break;
            case 'max': return 'Maximum';
                break;
        }
        return 'Total';
    }

    protected function stripPerc($str)
    {
        $prcPos = strpos($str, '%');
        if ($prcPos > 0) {
            return substr($str, 0, $prcPos);
        }
        return $str;
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