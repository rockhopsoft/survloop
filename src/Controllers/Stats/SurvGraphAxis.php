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

class SurvGraphAxis
{
    public $axis  = 'Y';
    public $label = '';
    public $scale = 'linear';
    public $min   = null;
    public $max   = null;

    public function __construct($axis = 'Y')
    {
        $this->axis = $axis;
    }

    public function isUnitMatch($unitIn = '')
    {
        if ($this->label == $unitIn) {
            return true;
        }
        if ($this->isUnitPercent($this->label)
            && $this->isUnitPercent($unitIn)) {
            return true;
        }
    }

    public function isUnitPercent($unit = '')
    {
        if ($unit == '' ) {
            $unit = $this->label;
        }
        return in_array(strtolower($unit), ['percent', '%', '% increase']);
    }

}