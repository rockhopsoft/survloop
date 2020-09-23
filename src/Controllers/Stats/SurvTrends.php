<?php
/**
  * SurvTrends is optimized for generating line graphs, often for trends.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.24
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

use RockHopSoft\Survloop\Controllers\SystemDefinitions;
use RockHopSoft\Survloop\Controllers\Stats\SurvStatsCore;

class SurvTrends extends SurvStatsCore
{
    private $nIDtxt       = '0';
    public $pastDays      = 60;
    public $axisLabels    = [];
    public $dataDays     = [];
    
    public $datFldDate    = '';
    public $datRawResults = null;
    
    public function __construct($nIDtxt = '0', $datFldDate = '', $pastDays = 60)
    {
        $this->nIDtxt     = $nIDtxt;
        $this->datFldDate = $datFldDate;
        $this->pastDays   = $pastDays;
    }
    
    public function addDataLineType($abbr = '', $label = '', $fld = '', $brdClr = '#2b3493', $dotClr = '#2b3493')
    {
        $this->addDataType($abbr, $label);
        $dLet = $this->dAbr($abbr);
        $this->datMap[$dLet]["rowFld"] = $fld;
        $this->datMap[$dLet]["brdClr"] = $brdClr;
        $this->datMap[$dLet]["dotClr"] = $dotClr;
        $this->dataDays[$dLet] = [];
        for ($cnt = $this->pastDays; $cnt >= 0; $cnt--) {
            $this->dataDays[$dLet][] = 0;
        }
        return true;
    }
    
    public function getPastStartDate()
    {
        return date("Y-m-d", mktime(0, 0, 0, date("n"), date("j")-$this->pastDays, date("Y")));
    }
    
    public function loadAxisPastDayLabels()
    {
        $this->axisLabels = [];
        for ($cnt = $this->pastDays; $cnt >= 0; $cnt--) {
            $this->axisLabels[] = date("n/j", mktime(0, 0, 0, date("n"), date("j")-$cnt, date("Y")));
        }
        return $this->axisLabels;
    }
    
    // Takes Eloquent database search results
    public function addRawDataResults($res = null)
    {
        $this->rawDataRes = $res;
        return true;
    }
    
    private function getDateIndex($date = '')
    {
        $time = strtotime($date);
        $time = mktime(0, 0, 0, date("n", $time), date("j", $time), date("Y", $time));
        $daysPast = (mktime(0, 0, 0, date("n"), date("j"), date("Y"))-$time)/(60*60*24);
        $ind = $this->pastDays-$daysPast;
        if ($ind >= 0 && $ind <= $this->pastDays) {
            return $ind;
        }
        return -1;
    }
    
    private function getRawResultDateIndex($row = null)
    {
        if ($row && trim($this->datFldDate) != '' && isset($row->{ $this->datFldDate })) {
            return $this->getDateIndex($row->{ $this->datFldDate });
        }
        return -1;
    }
    
    public function processRawDataResults($res = null)
    {
        if ($res !== null) {
            $this->addRawDataResults($res);
        }
        if (sizeof($this->datMap) > 0 && $this->rawDataRes && $this->rawDataRes->isNotEmpty()) {
            foreach ($this->rawDataRes as $statRec) {
                $dateIndex = $this->getRawResultDateIndex($statRec);
                if ($dateIndex >= 0) {
                    foreach ($this->datMap as $dLet => $datMap) {
                        if (isset($datMap["rowFld"]) 
                            && trim($datMap["rowFld"]) != ''
                            && isset($statRec->{ $datMap["rowFld"] }) 
                            && $statRec->{ $datMap["rowFld"] } !== null) {
                            $this->dataDays[$dLet][$dateIndex] = $statRec->{ $datMap["rowFld"] };
                        }
                    }
                }
            }
        }
        return true;
    }
    
    public function addDayTally($abbr, $date, $tally = 1)
    {
        $dLet = $this->dAbr($abbr);
        $ind = $this->getDateIndex($date);
        if (isset($this->dataDays[$dLet]) && isset($this->dataDays[$dLet][$ind])) {
            $this->dataDays[$dLet][$ind] += $tally;
        }
        return true;
    }
    
    public function printDailyGraph($height = 500)
    {
        $GLOBALS["SL"]->x["needsPlots"] = true;
        $this->loadAxisPastDayLabels();
        $sysDef = new SystemDefinitions;
        return view(
            'vendor.survloop.reports.graph-bar-grouped', 
            [
                "nIDtxt"     => $this->nIDtxt,
                "datMap"     => $this->datMap,
                "axisLabels" => $this->axisLabels,
                "dataDays"   => $this->dataDays,
                "height"     => $height,
                "css"        => $sysDef->loadCss()
            ]
        )->render();
    }
    
    public function printDailyGraphLines($height = 500)
    {
        $GLOBALS["SL"]->x["needsPlots"] = true;
        $this->loadAxisPastDayLabels();
echo 'printDailyGraphLines(<pre>'; print_r($this->datMap); print_r($this->dataDays); print_r($this->axisLabels); echo '</pre>'; exit;
        $sysDef = new SystemDefinitions;
        return view(
            'vendor.survloop.reports.graph-bar-grouped', 
            [
                "nIDtxt"     => $this->nIDtxt,
                "datMap"     => $this->datMap,
                "axisLabels" => $this->axisLabels,
                "dataDays"   => $this->dataDays,
                "height"     => $height,
                "css"        => $sysDef->loadCss()
            ]
        )->render();
    }
    
}