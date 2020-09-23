<?php
/**
  * SurvloopChart builds on SurvStats data set calculations to present the raw data in tables.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

use RockHopSoft\Survloop\Controllers\Stats\SurvStats;

class SurvStatsChart extends SurvStats
{
    public function tblAvgTot($fltAbbr, $datAbbr, $cols = 'filt')
    {
        $this->tblOut = [];
        $fLet = $this->fAbr($fltAbbr);
        $dLet = $this->dAbr($datAbbr);
        $this->tblOut[] = $this->tblSimpStatRow($fLet, $dLet, 'avg');
        $this->tblOut[] = $this->tblSimpStatRow($fLet, $dLet);
        $this->tblApplyScale();
        return view(
            'vendor.survloop.reports.inc-stat-tbl-avgtot', 
            [ "tblOut" => $this->tblOut ]
        )->render();
    }
    
    public function tblFltRowsCalc($fltCol, $fltRow, $datAbbr, $typ = 'sum', $datLabOvr = '', $totTop = true)
    {
        $this->tblOut = [];
        if (trim($datLabOvr) != '') {
            $this->opts["datLabOvr"] = $datLabOvr;
        }
        $colLet = $this->fAbr($fltCol);
        $rowLet = $this->fAbr($fltRow);
        $datLet = $this->dAbr($datAbbr);
        $datSfx = ((isset($this->datMap[$datLet])) ? ' ' . $this->datMap[$datLet]["lab"] : '');
        $totCnt = $this->getFiltTot($fltCol);
        if ($totTop) {
            $row = $this->tblInitSimpStatRow($typ);
            if (trim($this->opts["datLabOvr"]) != '') {
                $row[0] = $this->opts["datLabOvr"];
            } else {
                $row[0] .= $datSfx;
            }
            $row[0] = $this->opts["datLabPrfx"] . $row[0];
            $colStr = $this->applyCurrFilt('1');
            if ($typ != "avg" 
                && isset($this->dat[$colStr]) 
                && isset($this->dat[$colStr]["dat"][$datLet]) 
                && isset($this->dat[$colStr]["dat"][$datLet][$typ])) {
                $row[1] = $this->dat[$colStr]["dat"][$datLet][$typ];
            }
            if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                foreach ($this->filts[$colLet]["val"] as $colVal) {
                    if (!$this->isCurrHid($colLet, $colVal)) {
                        $cell = -3737;
                        $colStr = $this->applyCurrFilt($colLet . $colVal);
                        if (isset($this->dat[$colStr]) 
                            && isset($this->dat[$colStr]["dat"][$datLet]) 
                            && isset($this->dat[$colStr]["dat"][$datLet][$typ])) {
                            $cell = $this->dat[$colStr]["dat"][$datLet][$typ];
                            if ($typ == "avg") {
                                $row[1] += $cell;
                            }
                        }
                        $row[] = $cell;
                    }
                }
            }
            if ($typ == "avg") {
                $this->dat["1"]["dat"][$datLet][$typ] = $row[1];
            }
            $this->tblOut[] = $row;
        }
        if ($rowLet != '' && isset($this->filts[$rowLet]) && sizeof($this->filts[$rowLet]["val"]) > 0) {
            foreach ($this->filts[$rowLet]["val"] as $i => $rowVal) {
                if (!$this->isCurrHid($rowLet, $rowVal) 
                    && isset($this->filts[$rowLet]["vlu"][$i]) 
                    && trim($this->filts[$rowLet]["vlu"][$i]) != '') {
                    $row = $this->tblInitSimpStatRow($typ);
                    if (trim($this->opts["datLabOvr"]) != '') {
                        $row[0] = $this->opts["datLabOvr"];
                    } else {
                        $row[0] .= $datSfx;
                    }
                    $row[0] = $this->opts["datLabPrfx"] . $row[0] . ' ' . $this->filts[$rowLet]["vlu"][$i];
                    $rowStr = $this->applyCurrFilt($rowLet . $rowVal);
                    if ($typ != "avg" 
                        && isset($this->dat[$rowStr]) 
                        && isset($this->dat[$rowStr]["dat"][$datLet]) 
                        && isset($this->dat[$rowStr]["dat"][$datLet][$typ])) {
                        $row[1] = $this->dat[$rowStr]["dat"][$datLet][$typ];
                    }
                    if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                        foreach ($this->filts[$colLet]["val"] as $colVal) {
                            if (!$this->isCurrHid($colLet, $colVal)) {
                                $cell = -3737;
                                $cellStr = $this->applyCurrFilt($colLet . $colVal . '-' . $rowStr);
                                if (isset($this->dat[$cellStr]) 
                                    && isset($this->dat[$cellStr]["dat"][$datLet]) 
                                    && isset($this->dat[$cellStr]["dat"][$datLet][$typ])) {
                                    $cell = $this->dat[$cellStr]["dat"][$datLet][$typ];
                                    if ($typ == "avg") {
                                        $row[1] += $cell;
                                    }
                                }
                                $row[] = $cell;
                            }
                        }
                    }
                    if ($typ == "avg") {
                        $this->dat[$rowStr]["dat"][$datLet][$typ] = $row[1];
                    }
                    $this->tblOut[] = $row;
                }
            }
        }
        $ret = view(
            'vendor.survloop.reports.inc-stat-tbl-avgtot', 
            [ "tblOut" => $this->tblOut ]
        )->render();
        if (trim($datLabOvr) != '') {
            $this->opts["datLabOvr"] = '';
        }
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
                    $row = [
                        'Percent of ' . $datSfx . ' ' . $this->filts[$rowLet]["vlu"][$i], 
                        0 
                    ];
                    if (trim($label) != '') {
                        $row[0] = $label . ' ' . $this->filts[$rowLet]["vlu"][$i];
                    }
                    $row[0] = $this->opts["datLabPrfx"] . $row[0];
                    $rowStr = $this->applyCurrFilt($rowLet . $rowVal);
                    $filtStr = $this->applyCurrFilt('1');
                    if (isset($this->dat[$rowStr]) 
                        && isset($this->dat[$rowStr]["dat"][$datLet])
                        && isset($this->dat[$filtStr]["dat"][$datLet]["sum"]) 
                        && $this->dat[$filtStr]["dat"][$datLet]["sum"] > 0) {
                        $row[1] = round(100*$this->dat[$rowStr]["dat"][$datLet]["sum"]
                            /$this->dat[$filtStr]["dat"][$datLet]["sum"]) . '%';
                    }
                    if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                        foreach ($this->filts[$colLet]["val"] as $colVal) {
                            if (!$this->isCurrHid($colLet, $colVal)) {
                                $cell = -3737;
                                $colStr = $colLet . $colVal;
                                if (isset($this->dat[$colStr]) && isset($this->dat[$colStr]["cnt"])) {
                                    $cellStr = $this->applyCurrFilt($colStr . '-' . $rowStr);
                                    if (isset($this->dat[$cellStr]) 
                                        && isset($this->dat[$cellStr]["dat"][$datLet]) 
                                        && isset($this->dat[$cellStr]["dat"][$datLet]["sum"]) 
                                        && isset($this->dat[$colStr]) 
                                        && isset($this->dat[$colStr]["dat"][$datLet]) 
                                        && $this->dat[$colStr]["dat"][$datLet]["sum"] > 0) {
                                        $cell = round(100*$this->dat[$cellStr]["dat"][$datLet]["sum"]
                                            /$this->dat[$colStr]["dat"][$datLet]["sum"]) . '%';
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
        return view(
            'vendor.survloop.reports.inc-stat-tbl-percs', 
            [ "tblOut" => $this->tblOut ]
        )->render();
    }
    
    public function tblFltDatRowPerc($fltCol, $fltRow, $datAbbr, $label = '')
    {
        $this->tblOut = [];
        $colLet = $this->fAbr($fltCol);
        $rowLet = $this->fAbr($fltRow);
        $datLet = $this->dAbr($datAbbr);
        $datSfx = ((isset($this->datMap[$datLet])) ? ' ' . $this->datMap[$datLet]["lab"] : '');
        if ($rowLet != '' 
            && isset($this->filts[$rowLet]) 
            && sizeof($this->filts[$rowLet]["val"]) > 0) {
            foreach ($this->filts[$rowLet]["val"] as $i => $rowVal) {
                if (!$this->isCurrHid($rowLet, $rowVal) 
                    && isset($this->filts[$rowLet]["vlu"][$i]) 
                    && trim($this->filts[$rowLet]["vlu"][$i]) != '') {
                    $row = [ 'Percent of ' . $datSfx . ' ' . $this->filts[$rowLet]["vlu"][$i], '100%' ];
                    if (trim($label) != '') {
                        $row[0] = $label . ' ' . $this->filts[$rowLet]["vlu"][$i];
                    }
                    $row[0] = $this->opts["datLabPrfx"] . $row[0];
                    $rowStr = $this->applyCurrFilt($rowLet . $rowVal);
                    if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                        foreach ($this->filts[$colLet]["val"] as $colVal) {
                            if (!$this->isCurrHid($colLet, $colVal)) {
                                $cell = -3737;
                                $cellStr = $this->applyCurrFilt($colLet . $colVal . '-' . $rowStr);
                                if (isset($this->dat[$cellStr]) 
                                    && isset($this->dat[$cellStr]["dat"][$datLet]) 
                                    && isset($this->dat[$cellStr]["dat"][$datLet]["sum"]) 
                                    && isset($this->dat[$rowStr]) 
                                    && isset($this->dat[$rowStr]["dat"][$datLet]) 
                                    && $this->dat[$rowStr]["dat"][$datLet]["sum"] > 0) {
                                    $cell = round(100*$this->dat[$cellStr]["dat"][$datLet]["sum"]
                                        /$this->dat[$rowStr]["dat"][$datLet]["sum"]) . '%';
                                }
                                $row[] = $cell;
                            }
                        }
                    }
                    $this->tblOut[] = $row;
                }
            }
        }
        return view(
            'vendor.survloop.reports.inc-stat-tbl-percs', 
            [ "tblOut" => $this->tblOut ]
        )->render();
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
            $row = [
                'Ratio' . $datSfx, 
                0 
            ];
            if (trim($label) != '') {
                $row[0] = $label;
            }
            $row[0] = $this->opts["datLabPrfx"] . $row[0];
            if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                foreach ($this->filts[$colLet]["val"] as $colVal) {
                    if (!$this->isCurrHid($colLet, $colVal)) {
                        $cell = -3737;
                        $colStr = $this->applyCurrFilt($colLet . $colVal);
                        if (isset($this->dat[$colStr]) 
                            && isset($this->dat[$colStr]["dat"][$datLet]) 
                            && isset($this->dat[$colStr]["dat"][$datLet]["sum"]) 
                            && isset($this->dat[$ratioStr]) 
                            && isset($this->dat[$ratioStr]["dat"][$datLet]) 
                            && $this->dat[$ratioStr]["dat"][$datLet]["sum"] > 0) {
                            $cell = round(100*$this->dat[$colStr]["dat"][$datLet]["sum"]
                                /$this->dat[$ratioStr]["dat"][$datLet]["sum"]);
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
                    $row = [
                        'Ratio' . $datSfx . ' ' . $this->filts[$rowLet]["vlu"][$i], 
                        0 
                    ];
                    if (trim($label) != '') {
                        $row[0] = $label . ' ' . $this->filts[$rowLet]["vlu"][$i];
                    }
                    $row[0] = $this->opts["datLabPrfx"] . $row[0];
                    $rowStr = $this->applyCurrFilt($rowLet . $rowVal);
                    if (isset($this->filts[$colLet]) && sizeof($this->filts[$colLet]["val"]) > 0) {
                        foreach ($this->filts[$colLet]["val"] as $colVal) {
                            if (!$this->isCurrHid($colLet, $colVal)) {
                                $cell = -3737;
                                $cellStr = $this->applyCurrFilt($colLet . $colVal . '-' . $rowStr);
                                $colStr = $rowStr . '-' . $ratioStr;
                                if (isset($this->dat[$cellStr]) 
                                    && isset($this->dat[$cellStr]["dat"][$datLet]) 
                                    && isset($this->dat[$cellStr]["dat"][$datLet]["sum"]) 
                                    && isset($this->dat[$colStr]) 
                                    && isset($this->dat[$colStr]["dat"][$datLet]) 
                                    && $this->dat[$colStr]["dat"][$datLet]["sum"] > 0) {
                                    $cell = round(100*$this->dat[$cellStr]["dat"][$datLet]["sum"]
                                        /$this->dat[$colStr]["dat"][$datLet]["sum"]);
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
        return view(
            'vendor.survloop.reports.inc-stat-tbl-percs', 
            [ "tblOut" => $this->tblOut ]
        )->render();
    }
    
    public function tblPercHas($fltCol, $fltRow, $tot = 'filt')
    {
        $this->tblOut = [];
        $colLet = $this->fAbr($fltCol);
        $rowLet = $this->fAbr($fltRow);
        $totCnt = $this->getFiltTot($fltCol);
        if ($rowLet != '' && isset($this->filts[$rowLet]) && sizeof($this->filts[$rowLet]["val"]) > 0) {
            foreach ($this->filts[$rowLet]["val"] as $i => $rowVal) {
                if (!$this->isCurrHid($rowLet, $rowVal) 
                    && isset($this->filts[$rowLet]["vlu"][$i]) 
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
                                if (isset($this->dat[$colStr]) 
                                    && isset($this->dat[$cellStr])
                                    && isset($this->dat[$colStr]["cnt"]) 
                                    && intVal($this->dat[$colStr]["cnt"]) > 0) {
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
        return view(
            'vendor.survloop.reports.inc-stat-tbl-percs', 
            [ "tblOut" => $this->tblOut ]
        )->render();
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
                    $ret .= $this->tblSpacerRow($fltCol) 
                        . (($headRows) ? $this->tblHeaderRow($fltCol) : '')
                        . $this->tblFltRowsCalc($fltCol, $fltRow, $datAbbr, $typ);
                    $this->opts["datLabPrfx"] = '';
                }
            }
        }
        $this->delRecFilt($fltBlk);
        return $ret;
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
                                if (isset($this->dat[$colStr]["dat"][$dLet]["sum"]) 
                                    && $this->dat[$colStr]["cnt"] > 0) {
                                    $cell = round(100*$this->dat[$colStr]["dat"][$dLet]["sum"]
                                        /$this->dat[$colStr]["cnt"]);
                                    if ($cell > 0) {
                                        $cell = $cell . '% <sub class="slGrey">' 
                                            . number_format($this->dat[$colStr]["dat"][$dLet]["sum"]) 
                                            . '</sub>';
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
        return view(
            'vendor.survloop.reports.inc-stat-tbl-percs', 
            [ "tblOut" => $this->tblOut ]
        )->render();
    }
    
    public function tblFltBlksPercHasDat($fltCol, $fltBlk, $datTypes = [])
    {
        $ret = '';
        $colLet = $this->fAbr($fltCol);
        $blkLet = $this->fAbr($fltBlk);
        if (sizeof($datTypes) > 0 
            && isset($this->filts[$colLet]) 
            && isset($this->filts[$blkLet])
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
                                        if (isset($this->dat[$str]["dat"][$dLet]["sum"]) 
                                            && $this->dat[$str]["cnt"] > 0) {
                                            $cell = round(100*$this->dat[$str]["dat"][$dLet]["sum"]
                                                /$this->dat[$str]["cnt"]);
                                            if ($cell > 0) {
                                                $cell = $cell . '% <sub class="slGrey">' 
                                                    . number_format($this->dat[$str]["dat"][$dLet]["sum"])
                                                    . '</sub>';
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
                $ret .= view(
                    'vendor.survloop.reports.inc-stat-tbl-percs', 
                    [ "tblOut" => $this->tblOut ]
                )->render();
            }
        }
        return $ret;
    }
    
    public function tblHeaderRow($fltAbbr, $lnk = '', $tots = true)
    {
        $ret = '<tr><th>&nbsp;</th><th class="brdRgt">Total';
        if ($tots) {
            $ret .= '<sub class="slGrey">' . $this->getDatCnt($this->applyCurrFilt('1')) . '</sub>';
        }
        $ret .= '</th>';
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
                if (trim($lnk) != '') {
                    $lab = '<a href="' . str_replace('[[val]]', $val, $lnk) . '" target="_blank">' . $lab . '</a>';
                }
                $ret .= '<th>' . $lab;
                if ($tots) {
                    $ret .= '<sub class="slGrey">' . $this->getDatCnt($this->applyCurrFilt($fLet . $val)) . '</sub>';
                }
                $ret .= '</th>';
            }
        }
        return $ret . '</tr>';
    }
    
    public function tblSpacerRow($fltAbbr)
    {
        $datCols = 2;
        $fLet = $this->fAbr($fltAbbr);
        if ($fLet != '' && isset($this->filts[$fLet])) {
            $datCols += sizeof($this->filts[$fLet]["val"]);
        }
        return '<tr><td colspan=' . $datCols . ' > </td></tr>';
    }
    
    public function tblTagRows($datAbbr, $tagAbbr = '1', $typ = 'avg')
    {
        $this->tblOut = [];
        $datLet = $this->dAbr($datAbbr);
        if ($tagAbbr == '1') {
            $row = [ 'All', 0, 0 ];
            for ($i = 0; $i < sizeof($this->datMap[$datLet]["row"]); $i++) {
                $row[] = 0;
            }
            if (isset($this->tagTot[$datLet]['1']) && isset($this->tagTot[$datLet]['1'][$typ]["raw"])) {
                $row[1] = $this->tagTot[$datLet]['1'][$typ]["raw"];
                if (sizeof($this->tagTot[$datLet]['1'][$typ]["row"]) > 0) {
                    foreach ($this->tagTot[$datLet]['1'][$typ]["row"] as $j => $r) {
                        $row[(2+$j)] = $r;
                    }
                }
                $row[sizeof($row)-1] = sizeof($this->tagTot[$datLet]['1']["sum"]["ids"]);
            }
            $this->tblOut[] = $row;
        } else {
            $tagLet = $this->tAbr($tagAbbr);
            if ($datLet != '' 
                && isset($this->datMap[$datLet]) 
                && isset($this->tagTot[$datLet]) 
                && $tagLet != '' 
                && isset($this->tagMap[$tagLet]) 
                && isset($this->tagMap[$tagLet]["val"]) 
                && sizeof($this->tagMap[$tagLet]["val"]) > 0) {
                foreach ($this->tagMap[$tagLet]["val"] as $v => $tagVal) {
                    $row = ['', 0, 0];
                    for ($i = 0; $i < sizeof($this->datMap[$datLet]["row"]); $i++) {
                        $row[] = 0;
                    }
                    $tStr = $tagLet . $tagVal;
                    $row[0] = $this->tagMap[$tagLet]["vlu"][$v];
                    if (isset($this->tagTot[$datLet][$tStr]) && isset($this->tagTot[$datLet][$tStr][$typ]["raw"])) {
                        $row[1] = $this->tagTot[$datLet][$tStr][$typ]["raw"];
                        if (sizeof($this->tagTot[$datLet][$tStr][$typ]["row"]) > 0) {
                            foreach ($this->tagTot[$datLet][$tStr][$typ]["row"] as $j => $r) {
                                $row[(2+$j)] = $r;
                            }
                        }
                        $row[sizeof($row)-1] = sizeof($this->tagTot[$datLet][$tStr]["sum"]["ids"]);
                    }
                    $this->tblOut[] = $row;
                }
            }
        }
        $ret = view(
            'vendor.survloop.reports.inc-stat-tag-avgtot', 
            [ "tblOut" => $this->tblOut ]
        )->render();
        return $ret;
    }
    
    public function tblTagHeaderRow($datAbbr)
    {
        $ret = '';
        $datLet = $this->dAbr($datAbbr);
        if ($datLet != '' && isset($this->datMap[$datLet]) && sizeof($this->datMap[$datLet]["row"]) > 0) {
            $ret .= '<tr><th>&nbsp;</th><th class="brdRgt">' . $this->datMap[$datLet]["lab"];
            if (trim($this->datMap[$datLet]["unt"]) != '') {
                $ret .= ' <span class="slGrey fPerc80">' . $this->datMap[$datLet]["unt"] . '</span>';
            }
            $ret .= '</th>';
            foreach ($this->datMap[$datLet]["row"] as $r) {
                if (is_array($r) && sizeof($r) == 2) {
                    $ret .= '<th>' . $r[0];
                    if (trim($r[1]) != '') {
                        $ret .= ' <span class="slGrey fPerc80">' . $r[1] . '</span>';
                    }
                    $ret .= '</th>';
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
        if ($datLet != '' && isset($this->datMap[$datLet])) {
            $datCols += sizeof($this->datMap[$datLet]["row"]);
        }
        return '<tr><td colspan=' . $datCols . ' > </td></tr>';
    }
    
}
