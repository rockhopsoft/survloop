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

use RockHopSoft\Survloop\Controllers\Stats\SurvStatsPercs;

class SurvStatsChart extends SurvStatsPercs
{

    public function loadAvgsTbl($lnk = '', $only = [], $lineRows = [])
    {
        if ($lnk == '') {
            $lnk = $this->baseUrl;
        }
        $this->tblOut = new SurvStatsTbl('', $lineRows, '');
        $this->tblOut->startNewRow('brdBotGrey');
        $this->tblOut->addHeaderCell('Averages');
        $this->tblOut->addHeaderCellSpan('', 'brdRgtBlue2');
        foreach ($this->filts as $fLet => $filt) {
            if (sizeof($only) == 0 || in_array($fLet, $only)) {
                $this->tblOut->addHeaderCellSpan($filt["lab"], 'brdLftGrey');
            }
        }
        $this->tblOut->startNewRow('brdBotBlue2');
        $this->tblOut->addRowCell('Total Record Count', 'slGrey');
        $this->tblOut->addRowCell(null);
        $this->tblOut->addRowCellNumber($this->getDatCntForDatLet('1', 'a'), 'slGrey brdRgtBlue2');
        foreach ($this->filts as $fLet => $filt) {
            if (sizeof($only) == 0 || in_array($fLet, $only)) {
                $maxCnt = 0;
                foreach ($this->datMap as $dLet => $dat) {
                    //$cnt = $this->getDatCntForDatLet($fLet . '1', $dLet);
                    $cnt = $this->getDatCnt($fLet . '1', 'a');
                    if ($maxCnt < $cnt) {
                        $maxCnt = $cnt;
                    }
                }
//echo '<pre>'; print_r($this->dat['1']['dat'][$fLet]['ids']); echo '</pre>'; exit;
                //$cnt = sizeof($this->dat[$fLet . '1']['dat'][$fLet]['ids']);
                //$cnt = $this->getDatCnt($fLet . '1', 'a');
                $this->tblOut->addRowCell(null, 'brdLftGrey');
                $this->tblOut->addRowCellNumber($maxCnt, 'slGrey');
            }
        }
        foreach ($this->datMap as $dLet => $dat) {
            $this->tblOut->startNewRow();
            $this->tblOut->addRowCell($dat["lab"]);
            $this->tblOut->addRowCellNumber($this->getDatLetAvg($dLet));
            $this->tblOut->addRowCellNumber($this->getDatCntForDatLet('1', $dLet), 'slGrey brdRgtBlue2');
            foreach ($this->filts as $fLet => $filt) {
                if (sizeof($only) == 0 || in_array($fLet, $only)) {
                    $cnt = $this->getDatCntForDatLet($fLet . '1', $dLet);
                    $avg = $this->getDatLetAvg($dLet, $fLet . '1');
                    $this->tblOut->addRowCellNumber($avg, 'brdLftGrey');
                    $this->tblOut->addRowCellNumber($cnt, 'slGrey');
                }
            }
        }
//echo '<pre>'; print_r($this->tblOut); echo '</pre>'; exit;
        return $this->tblOut;
    }

    public function printLoadAvgsTbl($lnk = '', $only = [], $lineRows = [])
    {
        $this->loadAvgsTbl($lnk, $only, $lineRows);
        return $this->tblOut->print();
    }


    public function printTblFltRowsBlksCalc($fltCol, $fltRow, $fltBlk, $datAbbr, $typ = 'sum', $lnk = '', $showTotsRow = true)
    {
        $blkLet = $this->fAbr($fltBlk);
        $ret = '';
        if ($this->tblHasCols($blkLet)) {
            foreach ($this->filts[$blkLet]["val"] as $i => $blkVal) {
                if (!$this->isCurrHid($blkLet, $blkVal)) {
                    $this->addCurrFilt($fltBlk, $blkVal);
                    $this->opts["datLabPrfx"] = $this->fValLab($fltBlk, $blkVal) . ' ';
                    $this->tblOut = [];
                    $this->loadTblFltRowsCalc($fltCol, $fltRow, $datAbbr, $typ, '', $lnk, $showTotsRow);
                    $ret .= $this->tblSpacerRowHtml($fltCol)
                        //. (($headRows) ? $this->tblHeaderRowPercs($fltCol) : '')
                        . $this->tblOut->print(true); // $this->printTblFltRowsCalc($fltCol, $fltRow, $datAbbr, $typ);
                    $this->opts["datLabPrfx"] = '';
                }
            }
        }
        $this->delRecFilt($fltBlk);
        return $ret;
    }



    public function printTblPercHasDat($fltCol, $datTypes = [], $lineRows = [], $lnk = '')
    {
        $this->loadTblPercHasDat($fltCol, $datTypes, $lineRows, $lnk);
        return $this->tblOut->print();
    }

    public function printInnerTblPercHasDat($fltCol, $datTypes = [], $lineRows = [], $lnk = '')
    {
        $this->loadTblPercHasDat($fltCol, $datTypes, $lineRows, $lnk);
        return $this->tblOut->print(true);
    }

    protected function loadTblPercHasDat($fltCol, $datTypes = [], $lineRows = [], $lnk = '')
    {
        $filtStr = $this->getCurrFiltOr1();
        $colLet = $this->fAbr($fltCol);
        $totCnt = $this->getFiltTot($fltCol);
        $this->hasCols = $this->tblHasCols($colLet);
        $this->tblOut = new SurvStatsTbl('', $lineRows);
//if ($fltCol == 'status') { echo 'loadTblPercHasDat(' . $fltCol . ', filtStr: ' . $filtStr . ', colLet: ' . $colLet . ', totCnt: ' . $totCnt . '<br /><pre>'; print_r($this->datMap); echo '</pre>'; exit; }
        $this->addTblPercHasHeaderRow($colLet, 'brdBotGrey', $lnk);
        $this->addTblPercHasRecCntRow($colLet, $filtStr, 'brdBotBlue2');
        if (sizeof($datTypes) > 0) {
            foreach ($datTypes as $datAbbr) {
                $dLet = $this->dAbr($datAbbr);
                if (isset($this->datMap[$dLet])
                    && trim($this->datMap[$dLet]["lab"]) != '') {
                    $this->tblOut->startNewRow();
                    $label = $this->opts["datLabPrfx"] . $this->datMap[$dLet]["lab"];
                    $this->tblOut->addHeaderCell($label);
                    $this->addTblPercHasColData($dLet, $filtStr, '', 'brdRgtBlue2');
                    if ($this->hasCols) {
                        foreach ($this->filts[$colLet]["val"] as $colVal) {
                            if (!$this->isCurrHid($colLet, $colVal)) {
                                $colFilt = $colLet . $colVal;
                                $colFiltStr = $this->applyCurrFilt($colFilt);
                                $this->addTblPercHasColData($dLet, $colFiltStr, 'brdLftGrey');
                                $this->delCurrFilt($colFilt);
                            }
                        }
                    }
                }
            }
        }
        return $this->tblOut;
    }

    protected function addTblPercHasHeaderRow($colLet, $cls = '', $lnk = '')
    {
        $this->tblOut->startNewRow($cls);
        $this->tblOut->addRowCell();
        $cellCls = '';
        if ($this->hasCols) {
            $cellCls .= ' brdRgtBlue2';
        }
        $this->tblOut->addHeaderCellSpan('Frequency', $cellCls);
        if ($this->hasCols) {
            foreach ($this->filts[$colLet]["val"] as $v => $val) {
                if (!$this->isCurrHid($colLet, $val)) {
                    $label = $this->getFltHeaderLabel($colLet, $v, $val, $lnk);
                    $this->tblOut->addHeaderCellSpan($label, 'brdLftGrey');
                }
            }
        }
    }

    protected function addTblPercHasColData($dLet, $filtStr = '1', $cls1 = '', $cls2 = '')
    {
        if (trim($filtStr) == '') {
            $filtStr = '1';
        }
//echo 'addTblPercHasColData(' . $dLet . ', ' . $filtStr . ''; exit;
        $val = null;
        $totCnt = $this->dat[$filtStr]["cnt"];
//echo 'addTblPercHasColData(' . $dLet . ', ' . $filtStr . ' - cnt: ' . $cnt . ', totCnt: ' . $totCnt . '<br /><pre>'; print_r($this->dat[$filtStr]); echo '</pre>'; exit;
        if ($totCnt > 0 && isset($this->dat[$filtStr]["dat"][$dLet]["sum"])) {
            $cnt = $this->dat[$filtStr]["dat"][$dLet]["sum"];
            $val = $this->dat[$filtStr]["dat"][$dLet]["sum"]/$totCnt;
        }
        $this->tblOut->addRowCellPerc($val, $cls1);
        $this->tblOut->addRowCellNumber($cnt, 'slGrey ' . $cls2);
    }

    protected function addTblPercHasRowTotalEmpties($cnt, $cls1 = '', $cls2 = '')
    {
        $this->tblOut->addRowCell(0, $cls1);
        $this->tblOut->addRowCellNumber($cnt, 'slGrey ' . $cls2);
    }


    public function printTblFltBlksPercHasDat($fltCol, $fltBlk, $datTypes = [], $lnk = '')
    {
        $this->loadTblFltBlksPercHasDat($fltCol, $fltBlk, $datTypes, $lnk);
        return $this->tblOut->print();
    }

    public function loadTblFltBlksPercHasDat($fltCol, $fltBlk, $datTypes = [], $lnk = '')
    {
        $colLet = $this->fAbr($fltCol);
        $blkLet = $this->fAbr($fltBlk);
        $this->hasCols = $this->tblHasCols($colLet);
        $this->tblOut = new SurvStatsTbl;
        $this->addPercsTblFltHeaderRow($colLet, 'brdBotBlue2', $lnk);
        if (sizeof($datTypes) > 0
            && isset($this->filts[$colLet])
            && $this->tblHasCols($blkLet)) {
            foreach ($this->filts[$blkLet]["val"] as $b => $blkVal) {
                if (!$this->isCurrHid($blkLet, $blkVal)) {
                    $blkStr = $this->applyCurrFilt($blkLet . $blkVal);
                    $blkLabel = $this->fValLab($fltBlk, $blkVal);
                    $blkTot = $this->getFiltValTot($fltBlk, $blkVal);
                    $newBlock = true;
                    foreach ($datTypes as $d => $datAbbr) {
                        $dLet = $this->dAbr($datAbbr);
                        if (isset($this->datMap[$dLet])
                            && trim($this->datMap[$dLet]["lab"]) != '') {
                            $rowCls = '';
                            if ($newBlock) {
                                $rowCls = 'brdTopGrey';
                                $newBlock = false;
                            }
                            $this->tblOut->startNewRow($rowCls);
                            $label = $this->opts["datLabPrfx"] . $blkLabel
                                . ' ' . $this->datMap[$dLet]["lab"];
                            $this->tblOut->addHeaderCell($label);
                            $val = null;
                            $cnt = 0;
                            if ($blkTot > 0
                                && isset($this->dat[$blkStr]["dat"][$dLet]["sum"])) {
                                $cnt = $this->dat[$blkStr]["dat"][$dLet]["sum"];
                                $val = $cnt/$blkTot;
                            }
                            $this->tblOut->addRowCellPerc($val);
                            $this->tblOut->addRowCellNumber($cnt, 'slGrey brdRgtBlue2');
                            if ($this->tblHasCols($colLet)) {
                                foreach ($this->filts[$colLet]["val"] as $colVal) {
                                    if (!$this->isCurrHid($colLet, $colVal)) {
                                        $filt = $blkStr . '-' . $colLet . $colVal;
                                        $this->addTblPercHasColData($dLet, $filt, 'brdLftGrey');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->tblOut;
    }

    public function tblTotalsRowPercs($fltAbbr = '')
    {
        return $this->tblTotalsRow($fltAbbr, true);
    }

    public function tblTotalsRow($fltAbbr = '', $percs = false)
    {
        $fLet = $this->fAbr($fltAbbr);
        $this->hasCols = $this->tblHasCols($fLet);
        $ret = '<tr class="brdTopGrey"><th class="slGrey">Total Record Count</th>';
        if ($percs) {
            $ret .= '<td> </td>'; // 100%
        }
        $ret .= '<td class="slGrey ' . (($this->hasCols) ? ' brdRgtBlue2' : '')
            . '">' . $this->getDatCnt($this->applyCurrFilt('1')) . '</td>';
        if ($this->hasCols) {
            foreach ($this->filts[$fLet]["val"] as $i => $val) {
                if ($percs) {
                    $ret .= '<td class="brdLftGrey"> </td>'; // 100%
                }
                $ret .= '<td class="slGrey">'
                    . $this->getDatCnt($this->applyCurrFilt($fLet . $val))
                    . '</th>';
            }
        }
        return $ret . '</tr>';
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
            if (isset($this->tagTot[$datLet]['1'])
                && isset($this->tagTot[$datLet]['1'][$typ]["raw"])) {
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
                    if (isset($this->tagTot[$datLet][$tStr])
                        && isset($this->tagTot[$datLet][$tStr][$typ]["raw"])) {
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
        if ($datLet != ''
            && isset($this->datMap[$datLet])
            && sizeof($this->datMap[$datLet]["row"]) > 0) {
            $ret .= '<tr><th>&nbsp;</th><th class="brdRgtBlue2">'
                . $this->datMap[$datLet]["lab"];
            if (trim($this->datMap[$datLet]["unt"]) != '') {
                $ret .= ' <span class="slGrey fPerc80">'
                    . $this->datMap[$datLet]["unt"] . '</span>';
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
