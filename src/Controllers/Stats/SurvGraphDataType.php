<?php
/**
  * SurvGraphDataType holds the basic info about one type of
  * data to be used within some graphing report.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.3.2
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

class SurvGraphDataType
{
    public $slug     = '';
    public $title    = '';
    public $unit     = '';
    public $fields   = [];

    public $axisY     = null;

    public function __construct($slug = '', $title = '', $unit = '', $fields = [])
    {
        $this->slug   = $slug;
        $this->title  = $title;
        if ($unit != '') {
            $this->unit = $unit;
        } elseif (strpos($title, '(%)') !== false) {
            $this->unit = 'Percent';
        }
        $this->axisY = new SurvGraphAxis;
        $this->addFields($fields);
    }

    public function addField($fieldname = '')
    {
        if (trim($fieldname) != '' && !in_array($fieldname, $this->fields)) {
            $this->fields[] = $fieldname;
        }
    }

    public function addFields($fieldnames = [])
    {
        if (sizeof($fieldnames) > 0) {
            foreach ($fieldnames as $fld) {
                $this->addField($fld);
            }
        }
    }

    public function replaceInFieldNames($replace, $with)
    {
        if (sizeof($this->fields) > 0) {
            foreach ($this->fields as $i => $fld) {
                $this->fields[$i] = str_replace($replace, $with, $fld);
            }
        }
    }

    public function replaceInAllLabels($replace, $with)
    {
        $this->title = str_replace($replace, $with, $this->title);
    }


}