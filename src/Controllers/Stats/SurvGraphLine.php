<?php
/**
  * SurvGraphLine holds the basic data to be printed into a standalone line graph.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.3.2
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

use RockHopSoft\Survloop\Controllers\SystemDefinitions;
use RockHopSoft\Survloop\Controllers\Stats\SurvGraphAxis;
use RockHopSoft\Survloop\Controllers\Stats\SurvGraph;

class SurvGraphLine extends SurvGraph
{

    public function addDataLineVal($val = null, $lab = '')
    {
        $this->dataLines[$this->currLine]->addDataPoint($val, $lab);
    }

    public function setDataLineOpts($unit = '', $axis = null)
    {
        $this->dataLines[$this->currLine]->setUnit($unit);
        $this->dataLines[$this->currLine]->axisY->label = $axis->label;
        $this->dataLines[$this->currLine]->axisY->min   = $axis->min;
        $this->dataLines[$this->currLine]->axisY->max   = $axis->max;
    }

    public function print($rand = null, $printTitle = true)
    {
        $this->title = str_replace(' , ', ', ', $this->title);
        $this->loadAxesY();
        $this->loadColors();
        $this->setPrintHeight();
        return view(
            'vendor.survloop.reports.graph-data-line-standalone',
            [
                "rand"       => $this->chkRand($rand),
                "title"      => $this->title,
                "axisX"      => $this->axisX,
                "axisY"      => $this->axisY,
                "axisY2"     => $this->axisY2,
                "axisListY"  => $this->axisListY,
                "colorTxt"   => $this->colorTxt,
                "height"     => $this->height,
                "dataLines"  => $this->dataLines,
                "labelsX"    => $this->listXaxis(),
                "printTitle" => $printTitle
            ]
        )->render();
    }

    public function loadDataTable($reverse = false)
    {
        $this->dataTbl = new SurvStatsTbl;
        $this->dataTbl->startNewRow('brdBotBlue2');
        $this->dataTbl->addHeaderCell($this->axisX->label, 'brdRgtBlue2');
        foreach ($this->dataLines as $l => $line) {
            $this->dataTbl->addHeaderCell($line->title, 'brdLftGrey');
        }
        if ($reverse) {
            for ($i = 0; $i < sizeof($this->dataLines[0]->data); $i++) {
                $this->printDataTableRow($i);
            }
        } else {
            for ($i = (sizeof($this->dataLines[0]->data)-1); $i >= 0; $i--) {
                $this->printDataTableRow($i);
            }
        }
        return $this->dataTbl;
    }

    private function printDataTableRow($i)
    {
        $this->dataTbl->startNewRow();
        $this->dataTbl->addHeaderCell($this->dataLines[0]->dataLab[$i], 'brdRgtBlue2');
        foreach ($this->dataLines as $l => $line) {
            foreach ($line->dataLab as $k => $lab) {
                if ($this->dataLines[0]->dataLab[$i] == $lab) {
                    if ($GLOBALS["SL"]->REQ->has('csv')) {
                        $this->dataTbl->addRowCell($line->data[$k]);
                    } elseif ($this->dataLines[0]->isUnitPercent()) {
                        $this->dataTbl->addRowCellPerc($line->data[$k], 'brdLftGrey');
                    } else {
                        $this->dataTbl->addRowCellNumber($line->data[$k], 'brdLftGrey');
                    }
                }
            }
        }
    }

    public function printDataTable($reverse = false)
    {
        $this->loadDataTable($reverse);
        return $this->dataTbl->print();
    }

    public function csv($file = '', $reverse = false)
    {
        $this->loadDataTable($reverse);
        return $this->dataTbl->csv($file);
    }

    public function excel($file = '', $reverse = false)
    {
        $this->loadDataTable($reverse);
        return $this->dataTbl->excel($file);
    }

}


