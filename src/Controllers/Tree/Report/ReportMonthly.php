<?php
/**
  * ReportMonthly is a helper class prints month-based data for a report.
  * It is loaded with a set of all possible columns for data to include,
  * then columns are printed in sets of 3 or 4 per table.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.19
  */
namespace RockHopSoft\Survloop\Controllers\Tree\Report;

class ReportMonthly
{
    public $allCols    = [];
    public $prints     = [];
    public $title      = '';
    public $footer     = '';
    public $totCols    = 0;
    public $rowSize    = 0;

    public $monthFld   = '';
    public $startMonth = 1;
    public $months     = [];
    public $years      = [];

    public function __construct($months)
    {
        $this->months = $months;
    }

    public function addCol($fld = '', $name = '', $unit = '')
    {
        $this->allCols[] = new ReportMonthCol($fld, $name, $unit);
    }

    public function setTitle($title = '', $footer = '')
    {
        $this->title  = $title;
        $this->footer = $footer;
    }

    public function setMonthFld($monthFld = '')
    {
        $this->monthFld = $monthFld;
    }

    public function setStartMonth($startMonth = 1)
    {
        $this->startMonth = $startMonth;
    }

    public function printTables($wrapEachSetInCard = true)
    {
        if (!$this->months || sizeof($this->months) == 0) {
            return '<!-- no monthly data to print into tables -->';
        }
        $this->sortMonths($this->months);
        $this->checkColumns($this->months);
        $this->loadTablePrints($this->months);
        $ret = '';
        if (sizeof($this->prints) > 0) {
            foreach ($this->prints as $p => $printedTable) {
                if ($p > 0) {
                    $ret .= '<div class="p15"></div>';
                }
                if ($wrapEachSetInCard) {
                    $ret .= '<div class="slCard">';
                }
                if ($p == 0) {
                    $ret .= $this->title;
                }
                $ret .= $printedTable;
                if ($p == (sizeof($this->prints)-1)) {
                    $ret .= $this->footer;
                }
                if ($wrapEachSetInCard) {
                    $ret .= '</div>';
                }
            }
        }
        return $ret;
    }

    private function sortMonths()
    {
        if (!$this->months || sizeof($this->months) == 0) {
            return false;
        }
        $sorted = $this->years = [];
        for ($cnt = 0; $cnt < 12; $cnt++) {
            $m = $this->startMonth-$cnt;
            $year = intVal(date("y"));
            if (intVal(date("n")) < $this->startMonth) {
                $year--;
            }
            if ($m < 1) {
                $m += 12;
                $year--;
            }
            $this->years[] = $year;
            foreach ($this->months as $mon) {
                if (isset($mon->{ $this->monthFld })
                    && intVal($mon->{ $this->monthFld }) == $m) {
                    $sorted[] = $mon;
                }
            }
        }
        $this->months = $sorted;
        return true;
    }

    private function checkColumns($monthlyData = null)
    {
        $this->totCols = 0;
        foreach ($this->allCols as $d => $data) {
            foreach ($monthlyData as $mon) {
                if (isset($mon->{ $data->fld })
                    && intVal($mon->{ $data->fld }) > 0) {
                    $this->allCols[$d]->sum += intVal($mon->{ $data->fld });
                    $this->allCols[$d]->cnt++;
                }
            }
            if ($this->allCols[$d]->cnt > 0) {
                $this->totCols++;
            }
        }
        $this->rowSize = 4;
        if (in_array($this->totCols, [6, 9])) {
            $this->rowSize = 3;
        }
        return true;
    }

    private function loadTablePrints($monthlyData = null)
    {
        $this->prints = $monthlyTbl = [];
        $cnt = $curr = 0;
        while ($curr < sizeof($this->allCols) && $cnt < $this->rowSize) {
            if ($cnt%$this->rowSize == 0) {
                $monthlyTbl = new ReportMonthlyColumns($this->monthFld, $this->years);
            }
            if ($this->allCols[$curr]->cnt > 0) {
                $monthlyTbl->addMonthCol($this->allCols[$curr]);
                $cnt++;
                if ($cnt%$this->rowSize == 0) {
                    $this->prints[] = $monthlyTbl->printTbl($monthlyData);
                    $cnt = 0;
                }
            }
            $curr++;
        }
        if ($cnt%$this->rowSize != 0 && sizeof($monthlyTbl->cols) > 0) {
            $this->prints[] = $monthlyTbl->printTbl($monthlyData);
        }
        return true;
    }
}

class ReportMonthlyColumns
{
    public $cols     = [];
    public $colCnt   = 0;
    public $monthFld = '';
    public $years    = [];

    public function __construct($monthFld = '', $years = [])
    {
        $this->monthFld = $monthFld;
        $this->years    = $years;
    }

    public function addCol($fld = '', $name = '', $unit = '')
    {
        $this->cols[] = new ReportMonthCol($fld, $name, $unit);
        $this->colCnt++;
    }

    public function addMonthCol($monthCol)
    {
        $this->cols[] = $monthCol;
        $this->colCnt++;
    }

    public function printTbl($monthlyData = null)
    {
        if (!$monthlyData || sizeof($monthlyData) == 0) {
            return '<!-- no monthly data to print into tables -->';
        }
        return view(
            'vendor.survloop.reports.inc-tbl-monthly', 
            [
                "monthlyData" => $monthlyData,
                "cols"        => $this->cols,
                "colCnt"      => $this->colCnt,
                "monthFld"    => $this->monthFld,
                "years"       => $this->years
            ]
        )->render();
    }

}

class ReportMonthCol
{
    public $fld  = '';
    public $name = '';
    public $unit = '';
    public $cnt  = 0;
    public $sum  = 0;

    public function __construct($fld = '', $name = '', $unit = '')
    {
        $this->fld  = $fld;
        $this->name = $name;
        $this->unit = $unit;
    }

}
