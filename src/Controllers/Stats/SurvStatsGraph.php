<?php
/**
  * SurvStatsGraph builds on SurvStats and SurvStatsChart to present the visually present data.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

use RockHopSoft\Survloop\Controllers\Stats\SurvStatsChart;

class SurvStatsGraph extends SurvStatsChart
{

    /**
     * Prints the main view for a pie chart, given an object with its data.
     *
     * @param  RockHopSoft\Survloop\Controllers\SurvStatsPieData $data
     * @param  string $hgt
     * @return boolean
     */
    public function pieView($pieData, $hgt = null)
    {
        $GLOBALS["SL"]->x["needsCharts"] = true;
        return view(
            'vendor.survloop.reports.graph-pie',
            [
                "data"    => $pieData,
                "hgt"     => $hgt,
                "graphID" => 'rand' . rand(0, 100000)
            ]
        )->render();
    }

    public function piePercCntCore($fltCol, $fade = 0.1, $colors = [], $hgt = null)
    {
        $colLet = $this->fAbr($fltCol);
        $pieData = [];
        if ($this->tblHasCols($colLet)) {
            foreach ($this->filts[$colLet]["val"] as $i => $val) {
                if (isset($this->dat[$colLet . $val])
                    && isset($this->dat[$colLet . $val]["cnt"])
                    && intVal($this->dat[$colLet . $val]["cnt"]) > 0) {
                    if (!isset($colors[$i])) {
                        $colors[$i] = $GLOBALS["SL"]->colorFadeHex(($i*$fade), $this->clrFade1, $this->clrFade2);
                    }
                    $value = $this->dat[$colLet . $val]["cnt"];
                    $label = $this->filts[$colLet]["vlu"][$i];
                    $pieData[] = new SurvStatsPieData($value, $label, $colors[$i]);
                }
            }
        }
        return $this->pieView($pieData, $hgt);
    }

    public function pieMultiPercHas($fltRow, $fltCol = '', $tot = 'filt', $hgt = null)
    {
        /* if (sizeof($this->tblOut) == 0) {
            $retTable = $this->printInnerTblMultiPercHas($fltRow, $fltCol, $tot);
        } */
        $rowLet = $this->fAbr($fltRow);
        $colLet = $this->fAbr($fltCol);
        $ret = '';
        if ($this->tblHasCols($colLet)) {
            $ret .= '<div class="row">';
            foreach ($this->filts[$colLet]["val"] as $i => $colVal) {
                $ret .= '<div class="col-6 pB30"><h3 class="m0">'
                    . $this->filts[$colLet]["vlu"][$i] . '</h3>'
                    . $this->pieMultiPercHasCore($rowLet, $colLet, $i, $tot, $hgt) . '</div>';
                if ($i > 0 && $i%2 == 1) {
                    $ret .= '</div><div class="row">';
                }
            }
            $ret .= '</div>';
        } else {
            $ret .= $this->pieMultiPercHasCore($rowLet, '', -1, $tot, $hgt);
        }
        return $ret;
    }

    public function pieMultiPercHasCore($rowLet, $colLet = '', $i = 0, $tot = 'filt', $hgt = null)
    {
        $pieData = [];
        $datTblCol = $this->getFiltIndRecCntCol($i, true);
        if ($this->tblHasCols($rowLet)) {
            $inc = 0.1;
            if (sizeof($this->filts[$rowLet]["val"]) == 2) {
                $inc = 0.75;
            } elseif (sizeof($this->filts[$rowLet]["val"]) < 4) {
                $inc = 0.25;
            } elseif (sizeof($this->filts[$rowLet]["val"]) < 6) {
                $inc = 0.16;
            }
            foreach ($this->filts[$rowLet]["val"] as $j => $rowVal) {
                $datTblRow = $j+2;
//echo 'pieMultiPercHasCore(datTblRow: ' . $datTblRow . ', datTblCol: ' . $datTblCol . ')<br />';
                if (!$this->isCurrHid($rowLet, $rowVal)
                    && isset($this->tblOut->rows[$datTblRow]->cols[0])
                    && isset($this->tblOut->rows[$datTblRow]->cols[$datTblCol])) {
                    $count = $this->tblOut->rows[$datTblRow]->cols[$datTblCol]->val;
                    if (intVal($count) > 0) {
                        $label = $this->tblOut->rows[$datTblRow]->cols[0]->val;
                        $color = $GLOBALS["SL"]->colorFadeHex(($j*$inc), $this->clrFade1, $this->clrFade2);
                        $pieData[] = new SurvStatsPieData($count, $label, $color);
                    }
                }
            }
        }
//echo '<h2>piePercHasCore(rowLet: ' . $rowLet . ', colLet: ' . $colLet . '</h2><pre>'; print_r($pieData); echo '</pre>'; // <pre>'; print_r($this->tblOut); echo '</pre>
        return $this->pieView($pieData, $hgt);
    }

    public function pieTblMutliPercHasHgt($fltRow, $fltCol = '', $hgt = null)
    {
        return $this->pieTblMutliPercHas($fltRow, $fltCol, 'filt', '1', $hgt);
    }

    public function pieTblMutliPercHas($fltRow, $fltCol = '', $tot = 'filt', $filtStr = '1', $hgt = null)
    {
        $table = $this->printTblMultiPercHas($fltRow, $fltCol, '', $filtStr);
        $pie = $this->pieMultiPercHas($fltRow, $fltCol, $tot, $hgt);
        if ($fltCol != '') {
            return $pie . '<div class="p5"> </div>' . $table;
        }
        return '<div class="row"><div class="col-lg-6 pB30">' . $table
            . '</div><div class="col-lg-6 pB30">' . $pie . '</div></div>';
    }

    public function pieTblBlksMultiPercHas($fltCol, $fltRow, $fltBlk, $header = '', $tot = 'filt', $hgt = null)
    {
        $blkLet = $this->fAbr($fltBlk);
        $ret = '';
        if ($this->tblHasCols($blkLet)) {
            foreach ($this->filts[$blkLet]["val"] as $i => $blkVal) {
                if (!$this->isCurrHid($blkLet, $blkVal)) {
                    $this->addCurrFilt($fltBlk, $blkVal);
                    $blkLabel = $this->fValLab($fltBlk, $blkVal);
                    $ret .= '<div class="p20"></div><h2 class="slBlueDark">'
                        . $header . $blkLabel . '</h2>'
                        . $this->pieTblMutliPercHas($fltRow, $fltCol, $tot, $hgt);
                }
            }
        }
        $this->delRecFilt($fltBlk);
        return $ret;
    }

    public function boxWhisk($datAbbr, $fltCol, $hgt = '100%')
    {
        $GLOBALS["SL"]->x["needsPlots"] = true;

        $dLet = $this->dAbr($datAbbr);
        $data = [];
        return view(
            'vendor.survloop.reports.graph-box-whisker',
            [
                "data" => $data,
                "hgt" => $hgt
            ]
        )->render();
    }

}

class SurvStatsPieData
{
    public $value = null;
    public $label = null;
    public $color = null;

    public function __construct($value, $label, $color)
    {
        $this->value = $value;
        $this->label = $label;
        $this->color = $color;
    }
}