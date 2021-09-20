<?php
/**
  * SurvStatsCalcs builds on SurvStats to add
  * more complicated data set calculations.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.3.1
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

use RockHopSoft\Survloop\Controllers\Stats\SurvStatsTableUtils;

class SurvStatsCalcs extends SurvStatsTableUtils
{

    /**
     * Load a big table to print with dataset calcualtions.
     *
     * @param  string $fltCol
     * @param  string $fltRow
     * @param  string $datAbbr
     * @param  string $typ
     * @param  string $datLabOvr
     * @param  string $lnk
     * @return RockHopSoft\Survloop\Controllers\Stats\SurvStatsTbl
     */
    public function loadTblFltRowsCalc($fltCol, $fltRow, $datAbbr, $typ = 'sum', $datLabOvr = '', $lnk = '', $showTotsRow = true)
    {
        $filtStr = $this->getCurrFiltOr1();
        $colLet = $this->fAbr($fltCol);
        $rowLet = $this->fAbr($fltRow);
        $datLet = $this->dAbr($datAbbr);
        $totCnt = $this->getFiltTot($fltCol);
        if (trim($datLabOvr) != '') {
            $this->opts["datLabOvr"] = $datLabOvr;
        }
        $this->chkDataTblSffx($datLet);
        $this->hasCols = $this->tblHasCols($colLet);

        $this->tblOut = new SurvStatsTbl;
        $this->addTblFltHeaderRow($colLet, 'brdBotGrey', $typ, $lnk, 2);
		$this->addTblFltCalcRecCntRow($typ, $colLet, $filtStr, 'brdBotBlue2');
        if ($showTotsRow) {
        	$this->addTblFltCalcTotalsRow($typ, $colLet, $datLet, 'brdBotGrey', $filtStr);
        }
        if ($this->tblHasCols($rowLet)) {
            foreach ($this->filts[$rowLet]["val"] as $i => $rowVal) {
            	$rowStr = $this->applyCurrFilt($rowLet . $rowVal . '-' . $filtStr);
            	$this->addRowTblFltRowsCalc($i, $rowLet, $rowVal, $colLet, $datLet, $typ, $rowStr);
            }
        }
        if (trim($datLabOvr) != '') {
            $this->opts["datLabOvr"] = '';
        }
        return $this->tblOut;
    }

    /**
     * Check for a standard suffix for labels in this table, and load into table options.
     *
     * @param  string $datLet
     * @return string
     */
    protected function chkDataTblSffx($datLet)
    {
        $this->opts["datSfx"] = '';
        if (isset($this->datMap[$datLet])) {
        	$this->opts["datSfx"] = ' ' . $this->datMap[$datLet]["lab"];
        }
        return $this->opts["datSfx"];
    }

    /**
     * Compile data filter label within a data table.
     *
     * @param  string $typ
     * @return string
     */
    protected function getTblFltCalcLabel($typ)
    {
        $label = $this->getTblTypeName($typ);
        if (trim($this->opts["datLabOvr"]) != '') {
            $label = $this->opts["datLabOvr"];
        } elseif (isset($this->opts["datSfx"])) {
            $label .= $this->opts["datSfx"];
        }
        return $label;
    }

    /**
     * Add a row with the totals for all row breakdowns.
     *
     * @param  string $typ
     * @param  string $colLet
     * @param  string $datLet
     * @param  string $cls
     * @return void
     */
    protected function addTblFltCalcTotalsRow($typ, $colLet, $datLet, $cls = '', $filtStr = '1')
    {
        $this->tblOut->startNewRow($cls);
        $label = $this->opts["datLabPrfx"] . $this->getTblFltCalcLabel($typ);
        $this->tblOut->addHeaderCell($label);
        $val = null;
        $rowStr = $this->applyCurrFilt('1');
        if ($this->hasFltDatTyp($rowStr, $datLet, $typ)) {
        	$val = $this->dat[$rowStr]["dat"][$datLet][$typ];
        }
        $this->tblOut->addRowCellNumber($val);
        $cnt = sizeof($this->dat[$filtStr]["dat"][$datLet]["ids"]);
        $this->tblOut->addRowCellNumber($cnt, 'slGrey brdRgtBlue2');
        if ($this->hasCols) {
            foreach ($this->filts[$colLet]["val"] as $colVal) {
                if (!$this->isCurrHid($colLet, $colVal)) {
                    $colStr = $this->applyCurrFilt($colLet . $colVal);
                    $val = $this->getFltDatTypVal($colStr, $datLet, $typ);
        			$this->tblOut->addRowCellNumber($val, 'brdLftGrey');
			        $cnt = sizeof($this->dat[$colStr]["dat"][$datLet]["ids"]);
			        $this->tblOut->addRowCellNumber($cnt, 'slGrey');
                }
            }
        }
    }

    /**
     * Load a big table to print with dataset calcualtions.
     *
     * @param  int $i
     * @param  string $rowLet
     * @param  string $rowVal
     * @param  string $colLet
     * @param  string $typ
     * @return void
     */
    protected function addRowTblFltRowsCalc($i, $rowLet, $rowVal, $colLet, $datLet, $typ = 'sum', $filtStr = '1')
    {
        if (!$this->isCurrHid($rowLet, $rowVal)
            && isset($this->filts[$rowLet]["vlu"][$i])
            && trim($this->filts[$rowLet]["vlu"][$i]) != '') {
	        $this->tblOut->startNewRow();
			$label = $this->getTblFltCalcLabel($typ);
	        $label = $this->opts["datLabPrfx"] . $label . ' ' . $this->filts[$rowLet]["vlu"][$i];
	        $this->tblOut->addHeaderCell($label);
	        $rowStr = $this->applyCurrFilt($rowLet . $rowVal);
	    	$val = 0;
	        if ($this->hasFltDatTyp($rowStr, $datLet, $typ)) {
	        	$val = $this->dat[$rowStr]["dat"][$datLet][$typ];
	        }
	        $this->tblOut->addRowCellNumber($val);
	        $cnt = sizeof($this->dat[$rowStr]["dat"][$datLet]["ids"]);
	        $this->tblOut->addRowCellNumber($cnt, 'slGrey brdRgtBlue2');
	        if ($this->tblHasCols($colLet)) {
	            foreach ($this->filts[$colLet]["val"] as $colVal) {
	                if (!$this->isCurrHid($colLet, $colVal)) {
	                	$cellStr = $this->applyCurrFilt($colLet . $colVal . '-' . $filtStr);
	                	$cnt = $this->getDatCntForDatLet($cellStr, 'a');
	                    $val = $this->getTwoFltDatTypVal($colLet, $colVal, $rowStr, $datLet, $typ);
	        			$this->tblOut->addRowCellNumber($val, 'brdLftGrey');
	        			$this->tblOut->addRowCellNumber($cnt, 'slGrey');
	                }
	            }
	        }
	    }
    }

    /**
     * Add a header row to a calculating data table.
     *
     * @param  string $typ
     * @param  string $colLet
     * @param  string $filtStr
     * @param  string $cls
     * @return void
     */
    protected function addTblFltCalcRecCntRow($typ, $colLet, $filtStr = '1', $cls = '')
    {
        $this->tblOut->startNewRow($cls);
        $this->tblOut->addRowCell('Total Record Count', 'slGrey');
        $this->tblOut->addRowCell();
        $cellCls = '';
        if ($this->tblHasCols($colLet)) {
            $cellCls .= 'brdRgtBlue2';
        }
        $cnt = $GLOBALS["SL"]->cntArrayUnique($this->dat[$filtStr]["dat"]["a"]["ids"]);
        $this->tblOut->addRowCellNumber($cnt, $cellCls . ' slGrey');
        if ($this->tblHasCols($colLet)) {
            foreach ($this->filts[$colLet]["val"] as $v => $colVal) {
                if (!$this->isCurrHid($colLet, $colVal)) {
                	$cellStr = $this->applyCurrFilt($colLet . $colVal . '-' . $filtStr);
	                $cnt = $this->getDatCntForDatLet($cellStr, 'a');
        			$this->tblOut->addRowCell(null, 'brdLftGrey');
	                $this->tblOut->addRowCellNumber($cnt, 'slGrey');
                }
            }
        }
    }

    /**
     * Add a row with the totals for all column breakdowns.
     *
     * @param  string $typ
     * @param  string $fltAbbr
     * @param  string $datLet
     * @param  string $cls
     * @return string
     */
    public function printInnerTblCalcRecCntRow($fltAbbr, $typ = 'sum', $filtStr = '1', $cls = '')
    {
        $colLet = $this->fAbr($fltAbbr);
        $this->hasCols = $this->tblHasCols($colLet);
        $this->tblOut = new SurvStatsTbl;
        $this->addTblFltCalcRecCntRow($typ, $colLet, $filtStr, $cls);
        return $this->tblOut->print(true);
    }

    /**
     * Print table with dataset calcualtions applied to filters.
     *
     * @param  string $fltCol
     * @param  string $fltRow
     * @param  string $datAbbr
     * @param  string $typ
     * @param  string $datLabOvr
     * @param  string $lnk
     * @return string
     */
    public function printTblFltRowsCalc($fltCol, $fltRow, $datAbbr, $typ = 'sum', $datLabOvr = '', $lnk = '', $showTotsRow = true)
    {
        $this->loadTblFltRowsCalc($fltCol, $fltRow, $datAbbr, $typ, $datLabOvr, $lnk, $showTotsRow);
        return $this->tblOut->print();
    }

    /**
     * Print inside of table table with dataset calcualtions applied to filters.
     * No <table> tags.
     *
     * @param  string $fltCol
     * @param  string $fltRow
     * @param  string $datAbbr
     * @param  string $typ
     * @param  string $datLabOvr
     * @param  string $lnk
     * @return string
     */
    public function printInnerTblFltRowsCalc($fltCol, $fltRow, $datAbbr, $typ = 'sum', $datLabOvr = '', $lnk = '', $showTotsRow = true)
    {
        $this->loadTblFltRowsCalc($fltCol, $fltRow, $datAbbr, $typ, $datLabOvr, $lnk, $showTotsRow);
        return $this->tblOut->print(true);
    }

    /**
     * Print table with dataset calcualtions, applied to filters,
     * with an adhoc calculation on top.
     *
     * @param  string $fltCol
     * @param  string $fltRow
     * @param  string $datAbbr
     * @param  string $datAbbr2
     * @param  string $datLabOvr
     * @param  string $lnk
     * @return string
     */
    public function printInnerTblFltRowsCalcDiv($fltCol, $fltRow, $datAbbr, $datAbbr2, $datLabOvr = '', $lnk = '')
    {
        $this->opts["datLabOvr"] = $datLabOvr;
        $datAbbr3 = $this->addNewDataCalc($datAbbr, $datAbbr2, '/');
        $ret = $this->printInnerTblFltRowsCalc($fltCol, $fltRow, $datAbbr3, 'sum', $datLabOvr, $lnk);
        $this->opts["datLabOvr"] = '';
        return $ret;
    }

    /**
     * Print table with dataset calcualtions, applied to filters,
     * with an adhoc calculation on top.
     *
     * @param  string $fltCol
     * @param  string $fltRow
     * @param  string $datAbbr
     * @param  string $datAbbr2
     * @param  string $datLabOvr
     * @param  string $lnk
     * @return string
     */
    public function printTblFltRowsCalcDiv($fltCol, $fltRow, $datAbbr, $datAbbr2, $datLabOvr = '', $lnk = '')
    {
        $this->opts["datLabOvr"] = $datLabOvr;
        $datAbbr3 = $this->addNewDataCalc($datAbbr, $datAbbr2, '/');
        $ret = $this->printTblFltRowsCalc($fltCol, $fltRow, $datAbbr3, 'sum', $datLabOvr, $lnk);
        $this->opts["datLabOvr"] = '';
        return $ret;
    }
/* 'area',
    'farm',
    'sqft',
    'lgtfx',
    'Square Feet per Fixture' */


    public function printInnerTblAvgTot($fltAbbr, $datAbbr, $datLabel = '', $cols = 'filt')
    {
    	$this->loadTblAvgTot($fltAbbr, $datAbbr, $datLabel, $cols);
        return $this->tblOut->print(true);
    }

    public function loadTblAvgTot($fltAbbr, $datAbbr, $datScale = 1, $datLabel = '', $cols = 'filt')
    {
        $fLet = $this->fAbr($fltAbbr);
        $dLet = $this->dAbr($datAbbr);
        $this->tblOut = new SurvStatsTbl;
        $this->addTblSimpStatRow($fLet, $dLet, 'avg');
        $this->addTblSimpStatRow($fLet, $dLet);
        $this->tblApplyScale();
        return $this->tblOut;
    }

    public function printInnerTblAvgTotScale($fltAbbr, $datAbbr, $datScale = 1, $datLabel = '', $cols = 'filt')
    {
    	$this->loadTblAvgTotScale($fltAbbr, $datAbbr, $datScale, $datLabel, $cols);
        return $this->tblOut->print(true);
    }

    public function loadTblAvgTotScale($fltAbbr, $datAbbr, $datScale = 1, $datLabel = '', $cols = 'filt')
    {
        $this->opts["scaler"][0] = $datScale;
        $this->opts["scaler"][1] = $datLabel;
        $this->loadTblAvgTot($fltAbbr, $datAbbr, $cols);
        $this->opts["scaler"][0] = 1;
        $this->opts["scaler"][1] = '';
        return $this->tblOut;
    }

    /**
     * Print a data table
     *
     * @param  string $fltCol
     * @param  string $fltRow
     * @param  string $datAbbr
     * @param  string $ratioVal
     * @param  string $label
     * @param  string $lnk
     * @param  boolean $topTop
     * @return string
     */
    public function printTblFltDatRatio2Col($fltCol, $fltRow, $datAbbr, $ratioVal, $label = '', $lnk = '', $totTop = true)
    {
        $this->loadTblFltDatRatio2Col($fltCol, $fltRow, $datAbbr, $ratioVal, $label, $lnk, $totTop);
        return $this->tblOut->print();
	}

    /**
     * Print a data table
     *
     * @param  string $fltCol
     * @param  string $fltRow
     * @param  string $datAbbr
     * @param  string $ratioVal
     * @param  string $label
     * @param  string $lnk
     * @param  boolean $topTop
     * @return string
     */
    public function printInnerTblFltDatRatio2Col($fltCol, $fltRow, $datAbbr, $ratioVal, $label = '', $lnk = '', $totTop = true)
    {
    	$this->loadTblFltDatRatio2Col($fltCol, $fltRow, $datAbbr, $ratioVal, $label, $lnk, $totTop);
        return $this->tblOut->print(true);
    }

    public function loadTblFltDatRatio2Col($fltCol, $fltRow, $datAbbr, $ratioVal, $label = '', $lnk = '', $totTop = true)
    {
        $colLet = $this->fAbr($fltCol);
        $rowLet = $this->fAbr($fltRow);
        $datLet = $this->dAbr($datAbbr);
        $ratioStr = $this->applyCurrFilt($colLet . $ratioVal);
        $this->chkDataTblSffx($datLet);
        $this->hasCols = $this->tblHasCols($colLet);

        $this->tblOut = new SurvStatsTbl;
        $this->addTblFltHeaderRow($colLet, 'brdBotBlue2', '', $lnk);
        if ($totTop) {
        	$this->tblFltDatRatio2ColTotTop($colLet, $datLet, $ratioStr, $label);
        }
        if ($this->tblHasCols($rowLet)) {
            foreach ($this->filts[$rowLet]["val"] as $i => $rowVal) {
                if (isset($this->filts[$rowLet]["vlu"][$i])
                	&& trim($this->filts[$rowLet]["vlu"][$i]) != '') {
                	$this->addRowTblFltDatRatio2Col($i, $colLet, $rowLet, $rowVal, $datLet, $ratioStr);
                }
            }
        }
        return $this->tblOut;
    }

    protected function tblFltDatRatio2ColTotTop($colLet, $datLet, $ratioStr, $labelIn = '')
    {
        $this->tblOut->startNewRow();
    	$label = 'Ratio' . $this->opts["datSfx"];
        if (trim($labelIn) != '') {
            $label = $labelIn;
        }
        $label = $this->opts["datLabPrfx"] . $label;
        $this->tblOut->addHeaderCell($label);
        $tot = 0;
        $cells = [];
        if ($this->hasCols) {
            foreach ($this->filts[$colLet]["val"] as $colVal) {
                if (!$this->isCurrHid($colLet, $colVal)) {
                	$val = null;
                    $colStr = $this->applyCurrFilt($colLet . $colVal);
                    if (isset($this->dat[$colStr])
                        && isset($this->dat[$colStr]["dat"][$datLet])
                        && isset($this->dat[$colStr]["dat"][$datLet]["sum"])
                        && isset($this->dat[$ratioStr])
                        && isset($this->dat[$ratioStr]["dat"][$datLet])
                        && $this->dat[$ratioStr]["dat"][$datLet]["sum"] > 0) {
                        $val = $this->dat[$colStr]["dat"][$datLet]["sum"]
                            /$this->dat[$ratioStr]["dat"][$datLet]["sum"];
                        $tot += $val;
                    }
                    $cells[] = $val;
                }
            }
        }
        $this->tblOut->addRowCellPerc($tot, 'brdRgtBlue2');
        $this->addPercRowCells($cells);
    }

    protected function addRowTblFltDatRatio2Col($i, $colLet, $rowLet, $rowVal, $datLet, $ratioStr, $labelIn = '')
    {
        $this->tblOut->startNewRow();
    	$label = 'Ratio' . $this->opts["datSfx"] . ' ' . $this->filts[$rowLet]["vlu"][$i];
        if (trim($labelIn) != '') {
            $label = $labelIn . ' ' . $this->filts[$rowLet]["vlu"][$i];
        }
        $label = $this->opts["datLabPrfx"] . $label;
        $this->tblOut->addHeaderCell($label);
        $tot = 0;
        $cells = [];
        $rowStr = $this->applyCurrFilt($rowLet . $rowVal);
        if ($this->tblHasCols($colLet)) {
            foreach ($this->filts[$colLet]["val"] as $colVal) {
                if (!$this->isCurrHid($colLet, $colVal)) {
                    $val = null;
                    $colStr = $rowStr . '-' . $ratioStr;
                    $cellStr = $this->applyCurrFilt($colLet . $colVal . '-' . $rowStr);
                    if (isset($this->dat[$cellStr])
                        && isset($this->dat[$cellStr]["dat"][$datLet])
                        && isset($this->dat[$cellStr]["dat"][$datLet]["sum"])
                        && isset($this->dat[$colStr])
                        && isset($this->dat[$colStr]["dat"][$datLet])
                        && $this->dat[$colStr]["dat"][$datLet]["sum"] > 0) {
                        $val = $this->dat[$cellStr]["dat"][$datLet]["sum"]
                            /$this->dat[$colStr]["dat"][$datLet]["sum"];
                        $tot += $val;
                    }
                    $cells[] = $val;
                }
            }
        }
        $this->tblOut->addRowCellPerc($tot, 'brdRgtBlue2');
        $this->addPercRowCells($cells);
    }

}