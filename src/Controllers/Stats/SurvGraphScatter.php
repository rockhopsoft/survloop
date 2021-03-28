<?php
/**
  * SurvGraphScatter holds the basic data to be printed into a standalone scatter plot.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.3.2
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

use RockHopSoft\Survloop\Controllers\SystemDefinitions;
use RockHopSoft\Survloop\Controllers\Stats\SurvGraphAxis;
use RockHopSoft\Survloop\Controllers\Stats\SurvGraphDataLine;
use RockHopSoft\Survloop\Controllers\Stats\SurvGraph;

class SurvGraphScatter extends SurvGraph
{

    public function addDataLineVal($x = null, $y = null, $lab = '')
    {
        $this->dataLines[$this->currLine]->addDataPointXY($x, $y, $lab);
    }

    public function addLinearRegressionLine()
    {
        if ($this->currLine >= 0) {
            $x = $this->dataLines[$this->currLine]->dataX;
            $y = $this->dataLines[$this->currLine]->data;
            $trend = $GLOBALS["SL"]->linearRegression($x, $y);

            $this->linRegLin = new SurvGraphDataLine('Linear Regression Best Fit Line');
            $this->linRegLin->color = '#AAAAAA';
            $this->linRegLin->axisY = $this->dataLines[$this->currLine]->axisY;
            $this->linRegLin->unit  = $this->dataLines[$this->currLine]->unit;
            foreach ($this->dataLines[$this->currLine]->dataX as $i => $x) {
                $number = ($trend["slope"]*$x)+$trend["intercept"];
                $number = (($number <= 0) ? 0 : $number);
                $this->linRegLin->addDataPointXY($x, $number);
            }
        }

    }

    public function print($rand = null, $printTitle = true)
    {
        $this->title = str_replace(' , ', ', ', $this->title);
        $this->loadAxesY();
        $this->loadColors();
        $this->setPrintHeight();
        $this->addLinearRegressionLine();
        return view(
            'vendor.survloop.reports.graph-data-scatter',
            [
                "rand"       => $this->chkRand($rand),
                "title"      => $this->title,
                "axisX"      => $this->axisX,
                "axisY"      => $this->axisY,
                "axisY2"     => $this->axisY2,
                "colorTxt"   => $this->colorTxt,
                "height"     => $this->height,
                "dataLines"  => $this->dataLines,
                "linRegLin"  => $this->linRegLin,
                "labelsX"    => $this->listXaxis(),
                "printTitle" => $printTitle
            ]
        )->render();
    }


}


