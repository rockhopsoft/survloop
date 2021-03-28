<?php
/**
  * SurvGraphDataLine holds the basic data to be printed into graphs.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.3.2
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

class SurvGraphDataLine
{
    public $title   = '';
    public $color   = '';
    public $unit    = '';
    public $axisY   = null;
    public $data    = [];
    public $dataX   = [];
    public $dataLab = [];

    public function __construct($title = '', $color = '', $data = [], $dataLab = [], $dataX = [])
    {
        $this->title   = $title;
        $this->color   = $color;
        $this->data    = $data;
        $this->dataX   = $dataX;
        $this->dataLab = $dataLab;
        if (strpos($title, '(%)') !== false) {
            $this->unit = '%';
        }
    }

    public function addDataPoint($val = null, $lab = '')
    {
        $this->data[]    = $val;
        $this->dataLab[] = $lab;
    }

    public function addDataPointXY($x = null, $y = null, $lab = '')
    {
        $this->data[]    = $y;
        $this->dataX[]   = $x;
        $this->dataLab[] = $lab;
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

    public function isUnitPercent($unit = '')
    {
        if ($unit == '') {
            $unit = $this->unit;
        }
        return $this->axisY->isUnitPercent($unit);
    }

    public function printData()
    {
        if ($this->isUnitPercent() && sizeof($this->data) > 0) {
            $ret = '';
            foreach ($this->data as $dat) {
                $ret .= ', ' . (100*$dat);
            }
            return substr($ret, 2);
        }
        return implode(', ', $this->data);
    }

    public function printDataXY()
    {
        $ret = '';
        if (sizeof($this->data) > 0) {
            foreach ($this->data as $d => $dat) {
                if ($this->isUnitPercent() && sizeof($this->data) > 0) {
                    $dat = (100*$dat);
                }
                $ret .= ', { x: ' . $this->dataX[$d] . ', y: ' . $dat . ' }';
            }
            $ret = substr($ret, 2);
        }
        return $ret;
    }

    public function printDataLabels()
    {
        return "'" . implode("', '", $this->dataLab) . "'";
    }

    public function getDataLabelMin()
    {
        if (sizeof($this->dataLab)) {
            return $this->dataLab[0];
        }
        return '';
    }

    public function getDataLabelMax()
    {
        if (sizeof($this->dataLab)) {
            return $this->dataLab[(sizeof($this->dataLab)-1)];
        }
        return '';
    }

    public function printDataLabelsJava()
    {
        $ret = '';
        if (sizeof($this->dataLab)) {
            foreach ($this->dataLab as $i => $lab) {
                $ret .= (($i > 0) ? ', ' : '') . json_encode($lab);
            }
        }
        return $ret;
    }
}
