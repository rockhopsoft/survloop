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
    public $colPrfx  = '';
    public $currRow  = 0;

    /**
     * Initialize this table's settings for a prefix on column labels,
     * and an arrary of row indexes which need bottom borders.
     *
     * @param  string $colPrfx
     * @param  array $lineRows
     * @return void
     */
    public function __construct($colPrfx = '', $lineRows = [])
    {
        $this->colPrfx  = $colPrfx;
        $this->lineRows = $lineRows;
    }

    /**
     * Add a new row to this table, and treat it as our 'current row'.
     * By default, new cells will be added to this row.
     *
     * @param  string $cls
     * @return SurvStatTblRow
     */
    public function startNewRow($cls = '')
    {
        $this->currRow = sizeof($this->rows);
        $this->rows[$this->currRow] = new SurvStatTblRow($this->currRow, $cls);
        return $this->currRow;
    }

    /**
     * Add a cell to this table for column or row headers, <th>.
     *
     * @param  string $label
     * @param  string $cls
     * @param  int $colspan
     * @param  string $unit
     * @return void
     */
    public function addHeaderCell($label = '', $cls = '', $colspan = 1, $unit = '', $rowspan = 1)
    {
        if (isset($this->rows[$this->currRow])) {
            $val = $this->colPrfx . $label;
            $this->rows[$this->currRow]->addRowHeadCell($val, $cls, $unit, $colspan, $rowspan);
        }
    }

    /**
     * Add a header cell spanning multiple columns in this table, <th colspan="2">.
     *
     * @param  string $label
     * @param  string $cls
     * @param  int $colspan
     * @param  string $unit
     * @return void
     */
    public function addHeaderCellSpan($label = '', $cls = '', $colspan = 2)
    {
        return $this->addHeaderCell($label, $cls, $colspan);
    }

    /**
     * Add a header cell spanning multiple columns in this table, <th colspan="2">.
     *
     * @param  string $label
     * @param  string $cls
     * @param  int $rowspan
     * @param  string $unit
     * @return void
     */
    public function addHeaderRowSpan($label = '', $cls = '', $rowspan = 2)
    {
        return $this->addHeaderCell($label, $cls, 1, '', $rowspan);
    }

    /**
     * Add a regular cell to this table's row, <td>.
     *
     * @param  string $val
     * @param  string $cls
     * @param  int $rowInd
     * @return void
     */
    public function addRowCell($val = null, $cls = '', $unit = '', $rowInd = -1)
    {
        if ($rowInd < 0) {
            $rowInd = $this->currRow;
        }
        if (isset($this->rows[$rowInd])) {
            $this->rows[$rowInd]->addRowCell($val, $cls, $unit);
        }
    }

    /**
     * Add a numeric value as a regular cell to this table's row.
     *
     * @param  string $val
     * @param  string $cls
     * @param  int $rowInd
     * @return void
     */
    public function addRowCellNumber($val = null, $cls = '', $rowInd = -1)
    {
        $cls .= ' taR ';
        $this->addRowCell($val, $cls, '(number)');
    }

    /**
     * Add a numeric value as a regular cell to this table's row.
     *
     * @param  string $val
     * @param  string $cls
     * @param  int $rowInd
     * @return void
     */
    public function addRowCellPerc($val = null, $cls = '', $rowInd = -1)
    {
        $cls .= ' taR ';
        $this->addRowCell($val, $cls, '%');
    }

    /**
     * Print this entire table for the web.
     *
     * @param  boolean $innerOnly
     * @return string
     */
    public function print($innerOnly = false)
    {
        return $this->printTblView($innerOnly);
    }

    /**
     * Calls the view which turns this table object into HTML.
     *
     * @param  boolean $innerOnly
     * @param  boolean $isExcel
     * @return string
     */
    public function printTblView($innerOnly = false, $isExcel = false)
    {
        return view(
            'vendor.survloop.reports.inc-report-surv-stats-tbl',
            [
                "rows"      => $this->rows,
                "lineRows"  => $this->lineRows,
                "innerOnly" => $innerOnly,
                "isExcel"   => $isExcel
            ]
        )->render();
    }

    /**
     * Export this entire table for spreadsheet software.
     * Pass in empty string to skip Excel download delivery.
     *
     * @param  string $filename
     * @return string
     */
    public function excel($filename = 'export.xls')
    {
        $innerTbl = $this->printTblView(true, true);
        if (trim($filename) == '') {
            return $innerTbl;
        }
        $GLOBALS["SL"]->exportExcel($innerTbl, $filename);
    }

    /**
     * Export this entire table as Comma Separated Values.
     *
     * @return string
     */
    public function csv($filename = 'export.csv', $delim = ',')
    {
        $ret = '';
        if (sizeof($this->rows) > 0) {
            foreach ($this->rows as $i => $row) {
                foreach ($row->cols as $j => $cell) {
                    if ($j > 0) {
                        $ret .= $delim;
                    }
                    if ($i == 0 || $j == 0) {
                        $ret .= '"' . $cell->toCsv() . '"';
                    } else {
                        $ret .= $cell->toCsv();
                    }
                }
                $ret .= "\n";
            }
        }
        $GLOBALS["SL"]->downloadAsCSV($ret, $filename);
    }

}

class SurvStatTblRow
{
    public $rowInd = -1;
    public $cls    = '';
    public $cols   = [];

    /**
     * Initialize this row's index in the array of rows,
     * and an class to be applied to the <tr> tag.
     *
     * @param  int $rowInd
     * @param  string $cls
     * @return void
     */
    function __construct($rowInd = -1, $cls = '')
    {
        $this->rowInd = $rowInd;
        $this->cls    = $cls;
    }

    /**
     * Add a header cell to this row.
     *
     * @param  string $label
     * @param  string $cls
     * @param  string $unit
     * @param  int $colspan
     * @return void
     */
    public function addRowHeadCell($label = null, $cls = '', $unit = '', $colspan = 1, $rowspan = 1)
    {
        $colInd = sizeof($this->cols);
        $this->cols[] = new SurvStatTh($this->rowInd, $colInd, $label, $cls, $unit, $colspan, $rowspan);
    }

    /**
     * Add a regular cell to this row.
     *
     * @param  string $val
     * @param  string $cls
     * @param  string $unit
     * @param  int $colspan
     * @return void
     */
    public function addRowCell($val = null, $cls = '', $unit = '', $colspan = 1)
    {
        $colInd = sizeof($this->cols);
        $this->cols[] = new SurvStatTd($this->rowInd, $colInd, $val, $cls, $unit, $colspan);
    }

}

class SurvStatTd
{
    public $val  = null;
    public $unit = '';
    public $cls  = '';
    public $span = 1;

    public $rowInd = -1;
    public $colInd = -1;

    /**
     * Initialize this cell's location in the table, and it's contents.
     *
     * @param  int $rowInd
     * @param  int $colInd
     * @param  string $val
     * @param  string $cls
     * @param  string $unit
     * @param  int $colspan
     * @return void
     */
    function __construct($rowInd, $colInd, $val = null, $cls = '', $unit = '', $colspan = 1, $rowspan = 1)
    {
        $this->rowInd  = $rowInd;
        $this->colInd  = $colInd;
        $this->val     = $val;
        $this->cls     = $cls;
        $this->unit    = $unit;
        $this->span    = $colspan;
        $this->spanRow = $rowspan;
    }

    /**
     * Print this cell in its simplest form.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->val === null) {
            return '<span class="slGrey">-</span>';
        }
        return $GLOBALS["SL"]->sigFigs($this->val, 3) . $this->printUnit();
    }

    /**
     * Print this cell as part of this table's printing.
     *
     * @return string
     */
    protected function formatValue()
    {
        if ($this->val === null) {
            return '';
        }
        if ($this->unit == '%') {
            return $GLOBALS["SL"]->sigFigs((100*$this->val), 3);
        } elseif ($this->unit == '(number)') {
            if ($this->val < 10) {
                return $GLOBALS["SL"]->sigFigs($this->val, 3);
            } else {
                return number_format($this->val);
            }
        }
        return str_replace('  ', ' ', $this->val);
    }

    /**
     * Print this cell's units.
     *
     * @return string
     */
    protected function printUnit()
    {
        if (!in_array($this->unit, ['', '(number)'])) {
            return $this->unit;
        }
        return '';
    }

    /**
     * Print the html for this cell's class.
     *
     * @return string
     */
    protected function printClass()
    {
        if ($this->cls != '') {
            return ' class="' . $this->cls . '" ';
        }
        return '';
    }

    /**
     * Print the html for this cell's column span.
     *
     * @return string
     */
    protected function printColSpan()
    {
        $ret = '';
        if ($this->span > 1) {
            $ret .= ' colspan="' . $this->span . '" ';
        }
        if ($this->spanRow > 1) {
            $ret .= ' rowspan="' . $this->spanRow . '" ';
        }
        return $ret;
    }

    /**
     * Print this cell as part of this CSV file's printing.
     *
     * @return string
     */
    public function toCsv()
    {
        return $this->formatValue();
    }

    /**
     * Print this cell as part of this table's printing.
     *
     * @return string
     */
    public function toTable($i = 0, $j = 0)
    {
        return $this->toTableHtml($i, $j);
    }

    /**
     * Generate the HTML to print this cell for the web.
     *
     * @return string
     */
    public function toTableHtml($i = 0, $j = 0, $type = 'td')
    {
        return '<' . $type . ' id="tblR' . $i . 'C' . $j . '" '
            . (($j == 0) ? ' align=left ' : '')
            . $this->printClass() . $this->printColSpan() . ' >'
            . $this->formatValue() . $this->printUnit() . '</' . $type . '>';
    }

    /**
     * Generate this cell's HTML for export to spreadsheet software.
     *
     * @return string
     */
    public function toExcel($i = 0, $j = 0)
    {
        if ($this->val === null) {
            return '<td' . $this->printColSpan() . '> </td>';
            //  style="color: #777;"
        }
        return '<td' . $this->convertClassToExcel()
            . $this->printColSpan() . '>' . $this->val
            . $this->printUnit() . '</td>';
    }

    /**
     * Convert this cell's styles for spreadsheet export.
     *
     * @return string
     */
    protected function convertClassToExcel()
    {
        if (strpos($this->cls, 'brdLftGrey') !== false) {
            // return ' style="border-left: 1px #777 solid;" ';
        }
        return '';
    }
}

class SurvStatTh extends SurvStatTd
{
    /**
     * Print this header cell as part of this table's printing.
     *
     * @return string
     */
    public function toTable($i = 0, $j = 0)
    {
        return $this->toTableHtml($i, $j, 'th');
    }
}