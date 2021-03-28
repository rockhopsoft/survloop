<?php
/**
  * SurvStatsPercs builds on SurvStatsCalcs to handle data tables
  * which display percentages
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.3.1
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

use RockHopSoft\Survloop\Controllers\Stats\SurvStatsCalcs;

class SurvStatsPercs extends SurvStatsCalcs
{
    /**
     * Print a data table of percentages of records matching two filters.
     *
     * @param  string $fltRow
     * @param  string $fltCol
     * @param  string $lnk
     * @return string
     */
    public function printTblMultiPercHas($fltRow, $fltCol = '', $lnk = '', $filtStr = '1')
    {
    	$this->loadTblMultiPercHas($fltRow, $fltCol, $lnk);
    	return $this->tblOut->print();
    }

    /**
     * Print a data table of percentages of records matching two filters.
     *
     * @param  string $fltRow
     * @param  string $fltCol
     * @param  string $lnk
     * @return string
     */
    public function printInnerTblMultiPercHas($fltRow, $fltCol = '', $lnk = '', $filtStr = '1')
    {
    	$this->loadTblMultiPercHas($fltRow, $fltCol, $lnk);
    	return $this->tblOut->print(true);
    }

    /**
     * Print a data table of percentages of records matching two filters.
     *
     * @param  string $fltRow
     * @param  string $fltCol
     * @param  string $lnk
     * @return string
     */
    public function loadTblMultiPercHas($fltRow, $fltCol = '', $lnk = '', $filtStr = '1')
    {
        $rowLet = $this->fAbr($fltRow);
        $colLet = $this->fAbr($fltCol);
        $this->hasCols = $this->tblHasCols($colLet);
        $totCnt = $this->getFiltTot($fltCol);
        if (trim($fltCol) == '') {
            $totCnt = $this->getFiltTot($fltRow);
        }

        $this->tblOut = new SurvStatsTbl;
        $this->addTblPercHasHeaderRow($colLet, 'brdBotGrey', $lnk);
        $filtStr = $this->applyCurrFilt($this->getCurrFilt() . '-' . $filtStr);
//echo 'loadTblMultiPercHas(fltRow: ' . $fltRow . ', fltCol: ' . $fltCol . ', filtStr: ' . $filtStr . '<br />'; exit;
        $this->addTblPercHasRecCntRow($colLet, $filtStr, 'brdBotBlue2');
        if ($this->tblHasCols($rowLet)) {
            foreach ($this->filts[$rowLet]["val"] as $i => $rowVal) {
                if (!$this->isCurrHid($rowLet, $rowVal)
                    && isset($this->filts[$rowLet]["vlu"][$i])
                    && trim($this->filts[$rowLet]["vlu"][$i]) != '') {
                	$this->loadRowTblMultiPercHas($i, $rowLet, $rowVal, $colLet, $totCnt);
                }
            }
        }
        return $this->tblOut;
    }

    protected function addTblPercHasRecCntRow($colLet, $filtStr = '1', $cls = '')
    {
        $this->tblOut->startNewRow($cls);
        $this->tblOut->addRowCell('Total Record Count', $cls);
        $this->tblOut->addRowCell();
        $cellCls = 'slGrey';
        if ($this->hasCols) {
            $cellCls .= ' brdRgtBlue2';
        }
        $cnt = $this->dat[$filtStr]["cnt"]; // $this->getDatCntForDatLet($filtStr, $colLet);
        $this->tblOut->addRowCellNumber($cnt, $cellCls);
        if ($this->hasCols) {
            foreach ($this->filts[$colLet]["val"] as $v => $colVal) {
            	$cellStr = $this->applyCurrFilt($colLet . $colVal . '-' . $filtStr);
                $cnt = $this->dat[$cellStr]["cnt"];
                //$cnt = $this->getDatCntForDatLet($cellStr, 'a');
                $this->tblOut->addRowCell(null, 'brdLftGrey');
                $this->tblOut->addRowCellNumber($cnt, 'slGrey');
            }
        }
    }

    /**
     * Print a data table of percentages of records matching two filters.
     *
     * @param  string $fltRow
     * @param  string $fltCol
     * @return string
     */
    protected function loadRowTblMultiPercHas($i, $rowLet, $rowVal, $colLet, $totCnt)
    {
    	$this->tblOut->startNewRow();
        $label = $this->opts["datLabPrfx"] . $this->filts[$rowLet]["vlu"][$i];
        $this->tblOut->addHeaderCell($label);
        $rowStr = $this->applyCurrFilt($rowLet . $rowVal);
        if ($totCnt > 0
        	&& isset($this->dat[$rowStr])
            && isset($this->dat[$rowStr]["cnt"])) {
        	$this->tblOut->addRowCellPerc($this->dat[$rowStr]["cnt"]/$totCnt);
        	$cellCls = 'slGrey';
        	if ($this->tblHasCols($colLet)) {
        		$cellCls .= ' brdRgtBlue2';
        	}
        	$this->tblOut->addRowCellNumber($this->dat[$rowStr]["cnt"], $cellCls);
        }
        if ($this->tblHasCols($colLet)) {
            foreach ($this->filts[$colLet]["val"] as $colVal) {
                if (!$this->isCurrHid($colLet, $colVal)) {
                    $cnt = 0;
                    $val = null;
                    $colStr = $this->applyCurrFilt($colLet . $colVal);
                    $cellStr = $this->applyCurrFilt($colLet . $colVal . '-' . $rowStr);
                    if (isset($this->dat[$colStr])
                        && isset($this->dat[$cellStr])
                        && isset($this->dat[$colStr]["cnt"])
                        && intVal($this->dat[$colStr]["cnt"]) > 0) {
                    	$cnt = $this->dat[$cellStr]["cnt"];
                        $val = $cnt/$this->dat[$colStr]["cnt"];
                    }
                	$this->tblOut->addRowCellPerc($val, 'brdLftGrey');
                	$this->tblOut->addRowCellNumber($cnt, 'slGrey');
                }
            }
        }
    }

    /**
     * Print a data table of percentages for multiple data types.
     *
     * @param  string $fltCol
     * @param  string $fltRow
     * @param  string $datAbbr
     * @param  string $label
     * @return string
     */
    public function loadTblFltDatColPerc($fltCol, $fltRow, $datAbbr, $label = '', $lnk = '')
    {
        $colLet = $this->fAbr($fltCol);
        $rowLet = $this->fAbr($fltRow);
        $datLet = $this->dAbr($datAbbr);
        $this->chkDataTblSffx($datLet);
        $this->hasCols = $this->tblHasCols($colLet);

        $this->tblOut = new SurvStatsTbl;
        $this->addTblFltHeaderRow($colLet, 'brdBotBlue2', '', $lnk);
        if ($this->tblHasCols($rowLet)) {
            foreach ($this->filts[$rowLet]["val"] as $i => $rowVal) {
                if (!$this->isCurrHid($rowLet, $rowVal)
                	&& isset($this->filts[$rowLet]["vlu"][$i])
                    && trim($this->filts[$rowLet]["vlu"][$i]) != '') {
            		$this->addRowTblFltDatColPerc($i, $rowLet, $rowVal, $colLet, $datLet, $label);
                }
            }
        }
        return $this->tblOut;
    }


    /**
     * Print a data table of percentages for multiple data types.
     *
     * @param  string $fltCol
     * @param  string $fltRow
     * @param  string $datAbbr
     * @param  string $label
     * @return string
     */
    public function printTblFltDatColPerc($fltCol, $fltRow, $datAbbr, $label = '', $lnk = '')
    {
        $this->loadTblFltDatColPerc($fltCol, $fltRow, $datAbbr, $label, $lnk);
        return $this->tblOut->print();
	}

    /**
     * Print a data table of percentages for multiple data types without main table tags.
     *
     * @param  string $fltCol
     * @param  string $fltRow
     * @param  string $datAbbr
     * @param  string $label
     * @return string
     */
    public function printInnerTblFltDatColPerc($fltCol, $fltRow, $datAbbr, $label = '', $lnk = '')
    {
        $this->loadTblFltDatColPerc($fltCol, $fltRow, $datAbbr, $label, $lnk);
        return $this->tblOut->print(true);
	}

    /**
     * Print a data table of percentages for multiple data types without main table tags.
     *
     * @param  int $i
     * @param  string $rowLet
     * @param  string $rowVal
     * @param  string $colLet
     * @param  string $datLet
     * @param  string $labelIn
     * @return string
     */
    protected function addRowTblFltDatColPerc($i, $rowLet, $rowVal, $colLet, $datLet, $labelIn = '')
    {
        $this->tblOut->startNewRow();
    	$label = 'Percent of ' . $this->opts["datSfx"] . ' ' . $this->filts[$rowLet]["vlu"][$i];
        if (trim($labelIn) != '') {
            $label = $labelIn . ' ' . $this->filts[$rowLet]["vlu"][$i];
        }
        $label = $this->opts["datLabPrfx"] . $label;
        $this->tblOut->addHeaderCell($label);
        $rowStr = $this->applyCurrFilt($rowLet . $rowVal);
        $filtStr = $this->applyCurrFilt('1');
        $val = null;
        if (isset($this->dat[$rowStr])
            && isset($this->dat[$rowStr]["dat"][$datLet])
            && isset($this->dat[$filtStr]["dat"][$datLet]["sum"])
            && $this->dat[$filtStr]["dat"][$datLet]["sum"] > 0) {
            $val = $this->dat[$rowStr]["dat"][$datLet]["sum"]
                /$this->dat[$filtStr]["dat"][$datLet]["sum"];
        }
        $this->tblOut->addRowCellPerc($val, 'brdRgtBlue2');
        if ($this->tblHasCols($colLet)) {
            foreach ($this->filts[$colLet]["val"] as $colVal) {
                if (!$this->isCurrHid($colLet, $colVal)) {
                    $val = null;
                    $colStr = $colLet . $colVal;
                    if (isset($this->dat[$colStr])
                    	&& isset($this->dat[$colStr]["cnt"])) {
                        $cellStr = $this->applyCurrFilt($colStr . '-' . $rowStr);
                        if (isset($this->dat[$cellStr])
                            && isset($this->dat[$cellStr]["dat"][$datLet])
                            && isset($this->dat[$cellStr]["dat"][$datLet]["sum"])
                            && isset($this->dat[$colStr])
                            && isset($this->dat[$colStr]["dat"][$datLet])
                            && $this->dat[$colStr]["dat"][$datLet]["sum"] > 0) {
                            $val = $this->dat[$cellStr]["dat"][$datLet]["sum"]
                                /$this->dat[$colStr]["dat"][$datLet]["sum"];
                        }
                    }
        			$this->tblOut->addRowCellPerc($val);
                }
            }
        }
    }

    /**
     * Print a data table breaking down the percentages
     * of the row filter by the column filter.
     *
     * @param  int $i
     * @param  string $fltCol
     * @param  string $fltRow
     * @param  string $datAbbr
     * @param  string $label
     * @param  string $lnk
     * @return string
     */
    public function printTblFltDatRowPerc($fltCol, $fltRow, $datAbbr, $label = '', $lnk = '')
    {
    	$this->loadTblFltDatRowPerc($fltCol, $fltRow, $datAbbr, $label, $lnk);
    	return $this->tblOut->print();
    }

    /**
     * Print a data table breaking down the percentages
     * of the row filter by the column filter.
     *
     * @param  int $i
     * @param  string $fltCol
     * @param  string $fltRow
     * @param  string $datAbbr
     * @param  string $label
     * @param  string $lnk
     * @return string
     */
    public function printInnerTblFltDatRowPerc($fltCol, $fltRow, $datAbbr, $label = '', $lnk = '')
    {
    	$this->loadTblFltDatRowPerc($fltCol, $fltRow, $datAbbr, $label, $lnk);
    	return $this->tblOut->print(true);
    }

    /**
     * Print a data table breaking down the percentages
     * of the row filter by the column filter.
     *
     * @param  int $i
     * @param  string $fltCol
     * @param  string $fltRow
     * @param  string $datAbbr
     * @param  string $label
     * @return string
     */
    public function loadTblFltDatRowPerc($fltCol, $fltRow, $datAbbr, $label = '', $lnk = '')
    {
        $colLet = $this->fAbr($fltCol);
        $rowLet = $this->fAbr($fltRow);
        $datLet = $this->dAbr($datAbbr);
        $this->chkDataTblSffx($datLet);
        $this->hasCols = $this->tblHasCols($colLet);
        $this->tblOut = new SurvStatsTbl;
        $this->addTblFltHeaderRow($colLet, 'brdBotBlue2', '', $lnk);
        if ($this->tblHasCols($rowLet)) {
            foreach ($this->filts[$rowLet]["val"] as $i => $rowVal) {
                if (!$this->isCurrHid($rowLet, $rowVal)
                    && isset($this->filts[$rowLet]["vlu"][$i])
                    && trim($this->filts[$rowLet]["vlu"][$i]) != '') {
                    $this->tblOut->startNewRow();
                	$label = $this->opts["datLabPrfx"] . 'Percent of '
                		. $this->opts["datSfx"] . ' ' . $this->filts[$rowLet]["vlu"][$i];
                    if (trim($label) != '') {
                        $label .= ' ' . $this->filts[$rowLet]["vlu"][$i];
                    }
                    $this->tblOut->addHeaderCell($label);
                    $this->tblOut->addRowCellPerc(1, 'brdRgtBlue2');
                    $rowStr = $this->applyCurrFilt($rowLet . $rowVal);
                    if ($this->tblHasCols($colLet)) {
                        foreach ($this->filts[$colLet]["val"] as $colVal) {
                            if (!$this->isCurrHid($colLet, $colVal)) {
                                $val = null;
                                $cellStr = $this->applyCurrFilt($colLet . $colVal . '-' . $rowStr);
                                if (isset($this->dat[$cellStr])
                                    && isset($this->dat[$cellStr]["dat"][$datLet])
                                    && isset($this->dat[$cellStr]["dat"][$datLet]["sum"])
                                    && isset($this->dat[$rowStr])
                                    && isset($this->dat[$rowStr]["dat"][$datLet])
                                    && $this->dat[$rowStr]["dat"][$datLet]["sum"] > 0) {
                                    $val = $this->dat[$cellStr]["dat"][$datLet]["sum"]
                                        /$this->dat[$rowStr]["dat"][$datLet]["sum"];
                                }
                    			$this->tblOut->addRowCellPerc($val);
                            }
                        }
                    }
                }
            }
        }
        return $this->tblOut;
    }


}