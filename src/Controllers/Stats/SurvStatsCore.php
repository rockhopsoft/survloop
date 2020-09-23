<?php
/**
  * SurvStatsCore provides simpler foundations for SurvStats to collect data set calculations, 
  * and SurvTrends for line graphs with fewer filtering needs.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

class SurvStatsCore
{
    // for variables to be passed to views
    public $v       = [];
    public $isExcel = false;
    
    public $datMap  = [];
    public $dat     = [];
    public $raw     = [];
    public $opts    = [
        "scaler"     => [ 1, '' ],
        "datLabPrfx" => '',
        "datLabOvr"  => ''
    ];
    
    public function addDataType($abbr = '', $label = '', $unit = '', $rowLabels = [])
    {
        $let = chr(97+(sizeof($this->datMap)));
        $this->datMap[$let] = [
            "abr" => $abbr,
            "lab" => $label,
            "unt" => $unit,
            "row" => $rowLabels
        ];
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
        if (trim($this->opts["datLabOvr"]) != '') {
            $datNewLab = $this->opts["datLabOvr"];
        }
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
            $this->dat[$filtStr]["dat"][$datNewLet] = [
                "sum" => 0,
                "avg" => 0,
                "ids" => []
            ];
        }
        return true;
    }
    
    // raw data array, filters on each raw data point, data sum, data average, unique record count
    protected function loadMapRow()
    {
        $ret = [
            "cnt" => 0,
            "rec" => [],
            "dat" => []
        ];
        if (sizeof($this->datMap) > 0) {
            foreach ($this->datMap as $let => $d) {
                $ret["dat"][$let] = [
                    "sum" => 0,
                    "avg" => 0,
                    "ids" => []
                ];
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
    
}