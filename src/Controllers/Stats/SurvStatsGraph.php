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
    public function piePercHas($fltCol, $fltRow, $tot = 'filt')
    {
        if (sizeof($this->tblOut) == 0) {
            $retTable = $this->tblPercHas($fltCol, $fltRow, $tot);
        }
        $colLet = $this->fAbr($fltCol);
        $rowLet = $this->fAbr($fltRow);
        $ret = '<div class="row">';
        foreach ($this->filts[$colLet]["val"] as $i => $colVal) {
            $ret .= '<div class="col-6 pB20"><h3 class="m0">' . $this->filts[$colLet]["vlu"][$i] 
                . '</h3>' . $this->piePercHasCore($colLet, $rowLet, $i, $tot) . '</div>';
            if ($i > 0 && $i%2 == 1) {
                $ret .= '</div><div class="row">';
            }
        }
        return $ret . '</div>';
    }
    
    public function piePercHasCore($colLet, $rowLet, $i, $tot = 'filt')
    {
        $data = [];
        if ($rowLet != '' && isset($this->filts[$rowLet]) && sizeof($this->filts[$rowLet]["val"]) > 0) {
            foreach ($this->filts[$rowLet]["val"] as $j => $rowVal) {
                if (!$this->isCurrHid($rowLet, $rowVal) 
                    && isset($this->tblOut[$j][(2+$i)]) 
                    && isset($this->tblOut[$j][0])) {
                    $count = $this->getSub($this->tblOut[$j][(2+$i)]);
                    if (intVal($count) > 0) {
                        $color = $GLOBALS["SL"]->printColorFadeHex(
                            ($j*0.1), 
                            $GLOBALS["SL"]->getCssColor('color-main-on'), 
                            $GLOBALS["SL"]->getCssColor('color-main-bg')
                        );
                        $data[] = [
                            $count,
                            $this->tblOut[$j][0],
                            "'" . $color . "'"
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
                if (isset($this->dat[$colLet . $val]) 
                    && isset($this->dat[$colLet . $val]["cnt"])
                    && intVal($this->dat[$colLet . $val]["cnt"]) > 0) {
                    if (!isset($colors[$i])) {
                        $colors[$i] = $GLOBALS["SL"]->printColorFadeHex(
                            ($i*$fade), 
                            $GLOBALS["SL"]->getCssColor('color-main-on'), 
                            $GLOBALS["SL"]->getCssColor('color-main-bg')
                        );
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
        $GLOBALS["SL"]->x["needsPlots"] = true;
        
        $dLet = $this->dAbr($datAbbr);
        $data = [];
        return view(
            'vendor.survloop.reports.graph-box-whisker', 
            [ "data" => $data, "hgt" => $hgt ]
        )->render();
    }
    
    public function pieView($data, $hgt = null)
    {
        $GLOBALS["SL"]->x["needsCharts"] = true;
        return view(
            'vendor.survloop.reports.graph-pie', 
            [ "pieData" => $data, "hgt" => $hgt ]
        )->render();
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
                    $ret .= '<div class="p20"></div><h2 class="slBlueDark">' . $header . $blkLabel 
                        . '</h2>' . $this->pieTblPercHas($fltCol, $fltRow, $tot);
                }
            }
        }
        $this->delRecFilt($fltBlk);
        return $ret;
    }
    
    
}