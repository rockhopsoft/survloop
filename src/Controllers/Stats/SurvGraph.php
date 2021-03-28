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

    public $maxDataCnt = -1;
    public $maxDataInd = -1;
    public $minDataCnt = 1000000000;
    public $minDataInd = -1;

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
        if ($this->minDataInd < 0 && sizeof($this->dataLines) > 0) {
            foreach ($this->dataLines as $l => $line) {
                if ($this->maxDataCnt < sizeof($line->data)) {
                    $this->maxDataCnt = sizeof($line->data);
                    $this->maxDataInd = $l;
                }
                if ($this->minDataCnt > sizeof($line->data)) {
                    $this->minDataCnt = sizeof($line->data);
                    $this->minDataInd = $l;
                }
            }
        }
    }

    public function listXaxis()
    {
        $this->checkDataRange();
        if ($this->minDataInd >= 0 && isset($this->dataLines[$this->minDataInd])) {
            return $this->dataLines[$this->minDataInd]->printDataLabels();
        }
        return '';
    }

    public function getAxisMinX()
    {
        $this->checkDataRange();
        if (isset($this->dataLines[$this->minDataInd])) {
            return $this->dataLines[$this->minDataInd]->getDataLabelMin();
        }
        return '';
    }

    public function getAxisMaxX()
    {
        $this->checkDataRange();
        if (isset($this->dataLines[$this->minDataInd])) {
            return $this->dataLines[$this->minDataInd]->getDataLabelMax();
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