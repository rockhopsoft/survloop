<?php
/**
  * SurvStatTbl builds on SurvStats data set calculations to present the raw data in tables.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

class SurvStatsTbl
{
    public $rows     = [];
    public $lineRows = [];
    public $lineCols = [];
    public $colPrfx  = '';
    public $currRow  = 0;
    
    function __construct($colPrfx = 'Average ', $lineRows = [], $lineCols = [])
    {
        $this->colPrfx  = $colPrfx;
        $this->lineRows = $lineRows;
        $this->lineCols = $lineCols;
        $this->rows = [
            [
                new SurvStatTh(), 
                new SurvStatTh($colPrfx, 0)
            ]
        ];
    }
    
    public function addHeaderCol($label = '', $cnt = -3)
    {
        $this->rows[0][1]->cnt += $cnt;
        $this->rows[0][] = new SurvStatTh($this->colPrfx . $label, $cnt);
        return true;
    }
    
    public function addRowStart($label = '', $cnt = -3)
    {
        $this->currRow = sizeof($this->rows);
        $this->rows[$this->currRow][] = new SurvStatTh($label, $cnt);
        return $this->currRow;
    }
    
    public function addRowCell($val = null, $cnt = -3)
    {
        $this->rows[$this->currRow][] = new SurvStatTd($val, $cnt);
        return true;
    }
}

class SurvStatTh
{
    public $lab = '';
    public $cnt = 0;
    
    function __construct($lab = '', $cnt = -3)
    {
        $this->lab = $lab;
        $this->cnt = $cnt;
    }
        
    public function __toString()
    {
        $ret = '';
        if ($this->cnt >= 0) {
            $ret = '<sub class="slGrey">' . $this->cnt . '</sub>';
        }
        return $this->lab . $ret;
    }
        
    public function toExcel($j = 0, $brdRgt = false)
    {
        $ret = '<th' . (($j == 0) ? ' align=left ' : '') . ' >' 
            . (($this->lab) ? $this->lab : '') . '</th>';
        if ($j > 0 && $this->cnt !== null && $this->cnt >= 0) {
            $ret .= '<th><sub>' . intVal($this->cnt) . '</sub></th>';
            //$ret .= '<th style="color: #777;' 
            //    . (($brdRgt) ? ' border-right: 1px #777 solid;' : '')
            //    . '">(count)</th>';
        }
        return $ret;
    }
}

class SurvStatTd
{
    public $val  = null;
    public $cnt  = 0;
    public $unit = '';
    
    function __construct($val = null, $cnt = -3, $unit = '')
    {
        $this->val  = $val;
        $this->cnt  = $cnt;
        $this->unit = $unit;
    }
    
    public function __toString()
    {
        $cnt = '';
        if ($this->cnt >= 0) {
            $cnt = '<sub class="slGrey">' . $this->cnt . '</sub>';
        }
        if ($this->val === null) {
            return '<span class="slGrey">0</span>';
        }
        return $GLOBALS["SL"]->sigFigs($this->val, 3) 
            . (($this->unit != '') ? $this->unit : '') . $cnt;
    }
        
    public function toExcel($j = 0, $brdRgt = false)
    {
        if ($this->val === null) {
            return '<td style="color: #777;"></td>'
                . '<td style="color: #777;">' . $this->cnt . '</td>';
        }
        $brd = $cnt = '';
        if ($brdRgt) {
            $brd = ' border-right: 1px #777 solid;';
        }
        $ret = '<td style="' . $brd . '">';
        if ($this->cnt !== null && $this->cnt >= 0) {
            $cnt = '<td style="color: #777;' . $brd . '">'
                . '<sub>' . number_format($this->cnt) . '</sub></td>';
            $ret = '<td>';
        }
        return $ret . $GLOBALS["SL"]->sigFigs($this->val, 3)
            . (($this->unit != '') ? $this->unit : '') . '</td>' . $cnt;
    }
}
