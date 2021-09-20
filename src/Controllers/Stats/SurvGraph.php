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
use RockHopSoft\Survloop\Controllers\Stats\SurvGraphDataLine;

class SurvGraph
{
    public $title     = '';
    public $height    = 550;
    public $dataLines = [];
    public $currLine  = -1;
    public $linRegLin = null;

    // Index of dataLines which define Y Axes
    public $axisX     = null;
    public $axisY     = null;
    public $axisY2    = null;
    public $axisListY = [];

    public $colorTxt   = '#111';
    public $colorLines = [];

    public $dataTbl    = null;

    public function __construct($title = '', $height = 0)
    {
        $this->initGraph($title, $height);
    }

    private function initGraph($title = '', $height = 0)
    {
        $this->title  = $title;
        if ($height > 0) {
            $this->height = $height;
        } else {
            $this->height = 550;
            if ($GLOBALS["SL"]->REQ->has('height')
                && intVal($GLOBALS["SL"]->REQ->get('height')) > 0) {
                $this->height = intVal($GLOBALS["SL"]->REQ->get('height'));
            }
        }
        $this->colorLines = [];
        $sysDef = new SystemDefinitions;
        $css = $sysDef->loadCss();
        for ($i = 1; $i <= 16; $i++) {
            $this->colorLines[] = $css["color-graph-" . $i];
        }
        $this->axisX  = new SurvGraphAxis('X');
        $this->axisY  = new SurvGraphAxis;
        $this->axisY2 = new SurvGraphAxis;
    }

    protected function setPrintHeight()
    {
        if ($GLOBALS["SL"]->REQ->has('frame')) {
            $this->height = 550;
        } elseif ($GLOBALS["SL"]->isMobile()) {
            $this->height = 350;
            if (sizeof($this->dataLines) > 3) {
                $this->height = 420;
            } elseif (sizeof($this->dataLines) > 7) {
                $this->height = 600;
            } elseif (sizeof($this->dataLines) > 12) {
                $this->height = 900;
            }
        }
    }

    protected function chkRand($rand = null)
    {
        if ($rand == null) {
            $rand = rand(100000, 1000000);
        }
        return $rand;
    }

    public function getDataTableDatColumnCnt()
    {
        return sizeof($this->dataLines);
    }

    public function loadColors()
    {
        if (sizeof($this->dataLines) > 0) {
            foreach ($this->dataLines as $l => $line) {
                if ($this->dataLines[$l]->color == '') {
                    $index = $l%(sizeof($this->colorLines));
                    $this->dataLines[$l]->color = $this->colorLines[$index];
                }
            }
        }
    }

    public function setUnit($unit = '')
    {
        if ($unit != '') {
            $this->unit = $unit;
        }
        $this->axisY = new SurvGraphAxis;
        $this->axisY->label = $this->unit;
        if ($this->unit == '%') {
            $this->axisY->label = 'Percent';
        }
    }

    public function addDataLine($title = '', $color = '', $labAxisY = '', $labAxisX = '')
    {
        $this->currLine++;
        $this->dataLines[] = new SurvGraphDataLine($title, $color);
        if ($labAxisY != '') {
            $this->dataLines[$this->currLine]->setUnit($labAxisY);
        }
        if ($labAxisX != '') {
            $this->axisX->label = $labAxisX;
        }
    }

    public function setDataLineUnit($unit = '')
    {
        $this->dataLines[$this->currLine]->unit = $unit;
    }

    public function setDataLineAxisMinY($min = 0)
    {
        $this->dataLines[$this->currLine]->axisY->min = $min;
    }

    public function checkDataRange()
    {

        if (sizeof($this->dataLines) > 0
            && sizeof($this->dataLines[0]->data) > 0) {
            $delInds = $this->findNullDataX();
            $totOrig = sizeof($this->dataLines[0]->data);
            if (sizeof($delInds) > 0
                && sizeof($delInds) != sizeof($this->dataLines[0]->data)) {
                $delUntilA = $delUntilB = -1;
                if ($delInds[0] == 0) {
                    $currInd = 0;
                    while (isset($delInds[$currInd]) && $currInd == $delInds[$currInd]) {
                        $currInd++;
                    }
                    $delUntilA = $currInd-1;
                }
                $i = 0;
                $lastInd = (sizeof($delInds)-1);
                while (($lastInd+$i) >= 0
                    && isset($delInds[($lastInd+$i)])
                    && $delInds[($lastInd+$i)] == (sizeof($this->dataLines[0]->data)-1+$i)) {
                    $i--;
                }
                if ($i < 0) {
                    $delUntilB = $totOrig+$i;
                }
                if ($delUntilA >= 0 || $delUntilB >= 0) {
                    $this->delDataRangeX($delUntilA, $delUntilB);
                }
//echo 'totOrig: ' . $totOrig . ', delUntilA: ' . $delUntilA . ', delUntilB: ' . $delUntilB . ', delInds: ' . print_r($delInds) . '<br />'; exit;
//echo 'totNew: ' . sizeof($this->dataLines[0]->data) . ', dataLines[0] <pre>'; print_r($this->dataLines[0]->data); echo '</pre>'; exit;
            }
        }
    }

    private function findNullDataX()
    {
        $delInds = [];
        foreach ($this->dataLines[0]->data as $d => $dat) {
            $foundDat = false;
            foreach ($this->dataLines as $l => $line) {
                if (isset($line->data[$d]) && $line->data[$d] !== null) {
                    $foundDat = true;
                }
            }
            if (!$foundDat) {
                $delInds[] = $d;
            }
        }
        return $delInds;
    }

    private function delDataRangeX($delUntilA = -1, $delUntilB = -1)
    {
        foreach ($this->dataLines as $l => $line) {
            $data = $dataX = $dataLab = [];
            foreach ($line->data as $d => $dat) {
                if (($delUntilA == -1 || $d > $delUntilA)
                    && ($delUntilB == -1 || $d < $delUntilB)) {
                    $data[]    = $dat;
                    $dataLab[] = $this->dataLines[$l]->dataLab[$d];
                    if (isset($this->dataLines[$l]->dataX[$d])) {
                        $dataX[] = $this->dataLines[$l]->dataX[$d];
                    }
                }
            }
            $this->dataLines[$l]->data    = $data;
            $this->dataLines[$l]->dataX   = $dataX;
            $this->dataLines[$l]->dataLab = $dataLab;
        }
    }

    public function listXaxis()
    {
        if (sizeof($this->dataLines) > 0) {
            return $this->dataLines[0]->printDataLabels();
        }
        return '';
    }

    public function getAxisMinX()
    {
        if (sizeof($this->dataLines) > 0) {
            return $this->dataLines[0]->getDataLabelMin();
        }
        return '';
    }

    public function getAxisMaxX()
    {
        if (sizeof($this->dataLines) > 0) {
            return $this->dataLines[0]->getDataLabelMax();
        }
        return '';
    }

    protected function loadAxesY()
    {
        $mins1 = $mins2 = $maxs1 = $maxs2 = [];
        if (sizeof($this->dataLines) > 0) {
            foreach ($this->dataLines as $l => $line) {
                if (trim($line->unit) != '') {
                    $currMin = $line->axisY->min;
                    if ($line->axisY->min === null) {
                        $currMin = 'NULL';
                    }
                    if ($this->axisY->label == ''
                        || $this->axisY->label == $line->unit) {
                        $this->axisY->label = $line->unit;
                        if (!in_array($currMin, $mins1)) {
                            $mins1[] = $currMin;
                        }
                        if ($line->axisY->max !== null) {
                            $maxs1[] = $line->axisY->max;
                        }
                    } elseif ($this->axisY2->label == ''
                        || $this->axisY->label == $line->unit) {
                        $this->axisY2->label = $line->unit;
                        if (!in_array($currMin, $mins2)) {
                            $mins2[] = $currMin;
                        }
                        if ($line->axisY->max !== null) {
                            $maxs2[] = $line->axisY->max;
                        }
                    }
                    if ($line->unit != ''
                        && !in_array($line->unit, $this->axisListY)) {
                        $this->axisListY[] = $line->unit;
                    }
                }
            }
            $this->loadAxesMinsY($mins1, $mins2);
            $this->loadAxesMaxsY($maxs1, $maxs2);
        }
//echo 'loadAxesY <pre>'; print_r($mins1); echo '</pre><pre>'; print_r($this->axisY); echo '</pre><pre>'; print_r($mins2); echo '</pre><pre>'; print_r($this->axisY2); echo '</pre>'; dd($this->dataLines); exit;
    }

    private function loadAxesMinsY($mins1, $mins2)
    {
        if (sizeof($mins1) == 1) {
            $this->axisY->min = $mins1[0];
        } elseif (sizeof($mins1) > 1) {
            if (!in_array('NULL', $mins1)) {
                foreach ($mins1 as $min) {
                    if ($min != 'NULL') {
                        $this->axisY->min = $min;
                    }
                }
            }
        }
        if (sizeof($mins2) == 1) {
            $this->axisY2->min = $mins2[0];
        } elseif (sizeof($mins2) > 1) {
            if (!in_array('NULL', $mins2)) {
                foreach ($mins2 as $min) {
                    if ($min != 'NULL') {
                        $this->axisY2->min = $min;
                    }
                }
            }
        }
    }

    private function loadAxesMaxsY($maxs1, $maxs2)
    {
        if (sizeof($maxs1) > 0) {
            $this->axisY->max = $maxs1[0];
        }
        if (sizeof($maxs2) > 0) {
            $this->axisY2->max = $maxs2[0];
        }
    }

}