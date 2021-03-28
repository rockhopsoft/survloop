<?php
/**
  * SurvStatsTableUtils builds on SurvStats to provide
  * helper functions for classes trunk of extensions.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.3.1
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

use RockHopSoft\Survloop\Controllers\Stats\SurvStats;

class SurvStatsTableUtils extends SurvStats
{
    /**
     * Get appropriate label for the current header.
     *
     * @param  string $colLet
     * @param  int $v
     * @param  string $val
     * @param  string $lnk
     * @return string
     */
    protected function getFltHeaderLabel($colLet, $v, $val, $lnk)
    {
        $label = $val;
        if (isset($this->filts[$colLet]["vlu"][$v])
            && trim($this->filts[$colLet]["vlu"][$v]) != '') {
            $label = $this->filts[$colLet]["vlu"][$v];
        } else {
            $defVal = $GLOBALS["SL"]->def->getValById($val);
            if (trim($defVal) != '') {
                $label = $defVal;
            }
        }
        if (trim($lnk) != '') {
            $label = '<a href="' . str_replace('[[val]]', $val, $lnk)
                . '" target="_blank">' . $label . '</a>';
        }
        return $label;
    }

    /**
     * Print header row to a calculating data table.
     *
     * @param  string $typ
     * @param  string $colLet
     * @param  string $cls
     * @param  string $lnk
     * @return string
     */
    public function printInnerTblFltHeaderRow($typ, $fltAbbr, $cls = '', $lnk = '', $colspan = 1)
    {
        $colLet = $this->fAbr($fltAbbr);
        $this->hasCols = $this->tblHasCols($colLet);
        $this->tblOut = new SurvStatsTbl;
        $this->addTblFltHeaderRow($colLet, $cls, $typ, $lnk, $colspan);
        return $this->tblOut->print(true);
    }

    /**
     * Add a header row to a calculating data table.
     *
     * @param  string $typ
     * @param  string $colLet
     * @param  string $cls
     * @param  string $lnk
     * @return void
     */
    protected function addTblFltHeaderRow($colLet, $cls = '', $typ = 'sum', $lnk = '', $colspan = 1)
    {
        $this->tblOut->startNewRow($cls);
        $this->tblOut->addRowCell();
        $cellCls = '';
        if ($this->hasCols) {
            $cellCls .= ' brdRgtBlue2';
        }
        $label = $this->getTblFltCalcLabel($typ);
        $this->tblOut->addHeaderCell($label, $cellCls, $colspan);
        if ($this->hasCols) {
            foreach ($this->filts[$colLet]["val"] as $v => $val) {
                if (!$this->isCurrHid($colLet, $val)) {
                	$cellCls = '';
                	if ($colspan == 2) {
                		$cellCls .= ' brdLftGrey';
                	}
                	$label = $this->getFltHeaderLabel($colLet, $v, $val, $lnk);
                    $this->tblOut->addHeaderCell($label, $cellCls, $colspan);
                }
            }
        }
    }

    /**
     * Add a header row to a calculating data table.
     *
     * @param  string $typ
     * @param  string $colLet
     * @param  string $cls
     * @param  string $lnk
     * @return void
     */
    protected function addPercsTblFltHeaderRow($colLet, $cls = '', $lnk = '')
    {
    	return $this->addTblFltHeaderRow($colLet, $cls, 'sum', $lnk, 2);
    }

    /*
    public function tblHeaderRowPercs($fltAbbr = '', $lnk = '')
    {
        return $this->tblHeaderRow($fltAbbr, $lnk, true);
    }

    public function tblHeaderRow($fltAbbr = '', $lnk = '', $percs = false)
    {
        $fLet = $this->fAbr($fltAbbr);
        $this->hasCols = $this->tblHasCols($fLet);
        $ret = '<tr class="brdTopNon brdBotBlue2"><th>&nbsp;</th>';
        $ret .= '<th' . (($percs) ? ' colspan="2"' : '')
            . (($this->hasCols) ? ' class="brdRgtBlue2"' : '') . '>'
            . (($percs) ? 'Frequency' : 'Total') . '</th>';
        if ($this->hasCols) {
            foreach ($this->filts[$fLet]["val"] as $i => $val) {
                $lab = $val;
                if (isset($this->filts[$fLet]["vlu"][$i])
                    && trim($this->filts[$fLet]["vlu"][$i]) != '') {
                    $lab = $this->filts[$fLet]["vlu"][$i];
                } else {
                    $defVal = $GLOBALS["SL"]->def->getValById($val);
                    if (trim($defVal) != '') $lab = $defVal;
                }
                if (trim($lnk) != '') {
                    $lab = '<a href="' . str_replace('[[val]]', $val, $lnk)
                        . '" target="_blank">' . $lab . '</a>';
                }
                $ret .= '<th colspan="2" class="brdLftGrey">' . $lab . '</th>';
            }
        }
        $ret .= '</tr>';
        return $ret;
    }
    */

    /**
     * Add one percentage cell (to the current row) for each value passed in.
     *
     * @param  array $vals
     * @return void
     */
    protected function addPercRowCells($vals)
    {
        if (sizeof($vals) > 0) {
        	foreach ($vals as $val) {
            	$this->tblOut->addRowCellPerc($val);
            }
        }
    }

    /**
     * Add a spacer row into the current data table.
     *
     * @param  string $fltAbbr
     * @param  boolean $percs
     * @return integer
     */
    public function getColumnCnt($fltAbbr, $percs = false)
    {
        $datCols = 2;
        if ($percs) {
        	$datCols = 3;
        }
        $fLet = $this->fAbr($fltAbbr);
        if ($fLet != '' && isset($this->filts[$fLet])) {
            if ($percs) {
                $datCols += (2*sizeof($this->filts[$fLet]["val"]));
            } else {
                $datCols += sizeof($this->filts[$fLet]["val"]);
            }
        }
        return $datCols;
    }

    /**
     * Convert this list index into this data table
     * for the current filter's record count.
     *
     * @param  int $rawInd
     * @param  boolean $percs
     * @return integer
     */
    protected function getFiltIndRecCntCol($rawInd, $percs = false)
    {
        $colInd = 2+$rawInd;
        if ($percs) {
        	$colInd = 4+($rawInd*2);
        }
        return $colInd;
    }

    /**
     * Get the index of the column in this data table
     * for the current filter's record count.
     *
     * @param  string $colLet
     * @param  boolean $percs
     * @return integer
     */
    protected function getFiltRecCntCol($colLet, $percs = false)
    {
    	$colLetNum = ord(strtolower($colLet))-97;
    	return $this->getFiltIndRecCntCol($colLetNum, $percs);
    }

    /**
     * Get the index of the column in this percentages data table
     * for the current filter's record count.
     *
     * @param  string $colLet
     * @return integer
     */
    protected function getFiltRecCntPercsCol($colLet)
    {
    	return $this->getFiltRecCntCol($colLet, true);
    }

    /**
     * Add a spacer row into the current data table.
     *
     * @param  string $fltAbbr
     * @param  boolean $percs
     * @return string
     */
    public function addTblSpacerRow($fltAbbr, $percs = false)
    {
    	$datCols = $this->getColumnCnt($fltAbbr, $percs);
        $this->tblOut->startNewRow();
        $this->tblOut->addRowCell('<br />', '', '', $datCols);
    }

    /**
     * Add a spacer row into the current data table of percentages,
     * where most column spans are double.
     *
     * @param  string $fltAbbr
     * @return string
     */
    public function addTblPercSpacerRow($fltAbbr)
    {
        return $this->addTblSpacerRow($fltAbbr, true);
    }

    /**
     * Provide HTML for a spacer row to add manually curated data tables.
     *
     * @param  string $fltAbbr
     * @param  boolean $percs
     * @return string
     */
    public function tblSpacerRowHtml($fltAbbr, $percs = false)
    {
    	$datCols = $this->getColumnCnt($fltAbbr, $percs);
        return '<tr><td colspan="' . $datCols . '" > </td></tr>';
    }

    /**
     * Provide HTML for a spacer row to add manually curated data tables
     * of percentages, with double the number of columns.
     *
     * @param  string $fltAbbr
     * @return string
     */
    public function tblPercSpacerRowHtml($fltAbbr)
    {
        return $this->tblSpacerRowHtml($fltAbbr, true);
    }


    public function addTblSimpStatRow($colLet, $dLet, $typ = 'sum', $cls = '')
    {
        $this->tblOut->startNewRow($cls);
		$label = $this->getTblFltCalcLabel($typ);
        if (isset($this->opts["scaler"][1])
            && trim($this->opts["scaler"][1]) != '') {
            $label .= ' ' . $this->opts["scaler"][1];
        } elseif (isset($this->datMap[$dLet])) {
            $label .= ' ' . $this->datMap[$dLet]["lab"];
        }
        $label = $this->opts["datLabPrfx"] . $label;
        $this->tblOut->addHeaderCell($label);
        $val = null;
        $colStr = $this->applyCurrFilt("1");
        if (isset($this->dat[$colStr])
            && isset($this->dat[$colStr]["dat"][$dLet])) {
            $val = $this->dat[$colStr]["dat"][$dLet][$typ];
        }
        $this->tblOut->addRowCellNumber($val);
        $cellCls = 'slGrey';
        if ($this->hasCols) {
            $cellCls .= ' brdRgtBlue2';
        }
        $cnt = $this->getDatCnt($colStr, $dLet);
        $this->tblOut->addRowCellNumber($cnt, $cellCls);
        if ($this->tblHasCols($colLet)) {
            foreach ($this->filts[$colLet]["val"] as $colVal) {
                if (!$this->isCurrHid($colLet, $colVal)) {
                    $val = null;
                    $colStr = $this->applyCurrFilt($colLet . $colVal);
                    if (isset($this->dat[$colStr])
                        && isset($this->dat[$colStr]["dat"][$dLet])) {
                        $val = $this->dat[$colStr]["dat"][$dLet][$typ];
                    }
        			$this->tblOut->addRowCellNumber($val, 'brdLftGrey');
        			$cnt = $this->getDatCnt($colStr, $dLet);
        			$this->tblOut->addRowCellNumber($cnt, 'slGrey');

                }
            }
        }
        return $this->tblOut;
    }

    public function tblApplyScale($scaleRows = [])
    {
        if (isset($this->opts["scaler"][0])
            && $this->opts["scaler"][0] > 0
            && $this->opts["scaler"][0] != 1
            && sizeof($this->tblOut->rows) > 0) {
            foreach ($this->tblOut->rows as $i => $row) {
                if ((sizeof($scaleRows) == 0 || in_array($i, $scaleRows))
                	&& sizeof($row->cols) > 0) {
                    foreach ($row->cols as $j => $cell) {
                        if ($j > 0
                        	&& $cell->val !== null
                        	&& strpos($cell->cls, 'slGrey') === false) {
                            $this->tblOut->rows[$i]->cols[$j]->val
                            	= $this->opts["scaler"][0]*$cell->val;
                        }
                    }
                }
            }
        }
        return true;
    }


}