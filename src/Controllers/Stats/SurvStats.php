<?php
/**
  * SurvStats is a standalone class used to make aggregate calculations on data sets.
  * 
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

use RockHopSoft\Survloop\Controllers\Stats\SurvStatsCache;

class SurvStats extends SurvStatsCache
{
    public function addRecDat($datAbbr = '', $datRaw = '', $recID = -3, $row = [], $tags = [])
    {
        return $this->addRecDatLet($this->dAbr($datAbbr), $datRaw, $recID, $row, $tags);
    }
    
    public function addRecDatLet($datLet = '', $datRaw = '', $recID = -3, $row = [], $tags = [])
    {
        if ($datLet != '' && isset($this->raw[$datLet])) {
            $i = sizeof($this->raw[$datLet]["raw"]);
            $this->raw[$datLet]["raw"][$i] = $datRaw;
            $this->raw[$datLet]["ids"][$i] = $recID;
            $this->raw[$datLet]["row"][$i] = $row;
            $this->raw[$datLet]["flt"][$i] = [];
            if (sizeof($this->fltCurr) > 0) {
                foreach ($this->fltCurr as $currLet => $currVal) {
                    $this->raw[$datLet]["flt"][$i][$currLet] = $currVal;
                }
            }
            $this->raw[$datLet]["tag"][$i] = [];
            if (sizeof($tags) > 0) {
                foreach ($tags as $tag) {
                    $this->raw[$datLet]["tag"][$i][] = [
                        $this->tAbr($tag[0]), 
                        $tag[1]
                    ];
                }
            }
        }
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
    
    public function calcTagAvg($datLet = '', $tStr = '')
    {
        if (isset($this->tagTot[$datLet][$tStr])) {
            $tag = $this->tagTot[$datLet][$tStr];
            if (sizeof($tag["sum"]["ids"]) > 0) {
                $tag["avg"]["raw"] = $tag["sum"]["raw"]/sizeof($tag["sum"]["ids"]);
                if (sizeof($tag["sum"]["row"]) > 0) {
                    foreach ($tag["sum"]["row"] as $i => $r) {
                        $tag["avg"]["row"][$i] = $r/sizeof($tag["sum"]["ids"]);
                    }
                }
            }
        }
        return true;
    }
    
    public function calcStats()
    {
        $this->calcAllAvgs();
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
                            = $this->dat[$filtStr]["dat"][$datLet]["sum"]
                                /sizeof($this->dat[$filtStr]["dat"][$datLet]["ids"]);
                    }
                }
                $this->calcTagAvg($datLet, '1');
                if (sizeof($this->tagMap) > 0) {
                    foreach ($this->tagMap as $tagLet => $t) {
                        if (isset($t["val"]) && sizeof($t["val"]) > 0) {
                            foreach ($t["val"] as $v => $tagVal) {
                                $this->calcTagAvg($datLet, $tagLet . $tagVal);
                            }
                        }
                    }
                }   
            }
        }
        return true;
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
        if (isset($this->opts["scaler"][0]) 
            && $this->opts["scaler"][0] > 0 
            && $this->opts["scaler"][0] != 1 
            && sizeof($this->tblOut) > 0) {
            foreach ($this->tblOut as $i => $row) {
                if (sizeof($row) > 0) {
                    foreach ($row as $j => $cell) {
                        if ($j > 0) {
                            $this->tblOut[$i][$j] = $this->opts["scaler"][0]*$cell;
                        }
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
                if (!in_array($currLet . $currVal, $curr)) {
                    $curr[] = $currLet . $currVal;
                }
            }
        }
        asort($curr);
        if (sizeof($curr) > 1) {
            if ($curr[0] == '1') {
                unset($curr[0]);
            } elseif ($curr[sizeof($curr)-1] == '1') {
                unset($curr[sizeof($curr)-1]);
            }
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
                if ($val == $fltVal 
                    && isset($this->dat[$filtStr]) 
                    && isset($this->dat[$filtStr]["cnt"])) {
                    return $this->dat[$filtStr]["cnt"];
                }
            }
        }
        return 0;
    }
    
    public function getDatCnt($fStr = '1', $datAbbr = '')
    {
        if (trim($datAbbr) != '') {
            return $this->getDatCntForDat($fStr, $datAbbr);
        }
        if (isset($this->dat[$fStr])) {
            return $this->dat[$fStr]["cnt"];
        }
        return -3;
    }
    
    public function getDatCntForDat($fStr = '1', $datAbbr = '')
    {
        return $this->getDatCntForDatLet($fStr, $this->dAbr($datAbbr));
    }
    
    public function getDatCntForDatLet($fStr = '1', $dLet = '')
    {
        if (isset($this->dat[$fStr]) && isset($this->dat[$fStr]["dat"][$dLet])) {
            return sizeof($this->dat[$fStr]["dat"][$dLet]["ids"]);
        }
        return -3;
    }
    
    public function getDatTot($datAbbr, $fStr = '1', $typ = 'sum')
    {
        return $this->getDatLetTot($this->dAbr($datAbbr), $fStr, $typ);
    }
    
    public function getDatLetTot($dLet, $fStr = '1', $typ = 'sum')
    {
        if ($dLet != '' 
            && isset($this->dat[$fStr]) 
            && isset($this->dat[$fStr]["dat"][$dLet]) 
            && isset($this->dat[$fStr]["dat"][$dLet][$typ])) {
            return $this->dat[$fStr]["dat"][$dLet][$typ];
        }
        return 0;
    }
    
    public function getDatCntFval($fltAbbr, $fltVal)
    {
        return $this->getDatCnt($this->fAbr($fltAbbr) . $fltVal);
    }
    
    public function getDatTotFval($datAbbr, $fltAbbr, $fltVal, $typ = 'sum')
    {
        return $this->getDatTot($datAbbr, $this->fAbr($fltAbbr) . $fltVal, $typ);
    }
    
    public function getDatAvg($datAbbr, $fStr = '1')
    {
        return $this->getDatTot($datAbbr, $fStr, 'avg');
    }
    
    public function getDatLetAvg($dLet, $fStr = '1')
    {
        return $this->getDatLetTot($dLet, $fStr, 'avg');
    }
    
    public function getDatLetMin($dLet, $fStr = '1')
    {
//echo 'getDatLetMin(' . $dLet . '<pre>'; print_r($this->tagTot[$dLet]); echo '</pre>'; exit;
        return $this->getDatLetTot($dLet, $fStr, 'min');
    }
    
    public function getDatLetMax($dLet, $fStr = '1')
    {
//echo '<pre>'; print_r($this->tagTot[$dLet]); echo '</pre>'; exit;
        return $this->getDatLetTot($dLet, $fStr, 'max');
    }



    
}