<?php
/**
  * SurvGraphDataTypes holds a collection of SurvGraphDataType objects
  * used to build a graph with multiple data lines.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.3.2
  */
namespace RockHopSoft\Survloop\Controllers\Stats;

use RockHopSoft\Survloop\Controllers\Stats\SurvGraphDataType;

class SurvGraphDataTypes
{
    public $typeGroups = [];
    public $g          = -1;
    public $skips      = [];

    public function addNewGroup($title = '')
    {
        $this->typeGroups[] = new SurvGraphDataTypeGroups($title);
        $this->g++;
    }

    public function addType($slug = '', $title = '', $unit = '', $fields = [])
    {
        $this->typeGroups[$this->g]->types[] = new SurvGraphDataType($slug, $title, $unit, $fields);
    }

    public function setAxisMaxY($axisMaxY = '100')
    {
        $last = sizeof($this->typeGroups[$this->g]->types)-1;
        $this->typeGroups[$this->g]->types[$last]->axisY->max = $axisMaxY;
    }

    public function setAxisMinY($axisMinY = '0')
    {
        $last = sizeof($this->typeGroups[$this->g]->types)-1;
        $this->typeGroups[$this->g]->types[$last]->axisY->min = $axisMinY;
    }

    public function clearTypeAxisMinY($axisMinY = '')
    {
        $last = sizeof($this->typeGroups[$this->g]->types)-1;
        $this->typeGroups[$this->g]->types[$last]->axisMinY = $axisMinY;
    }

    public function getDataTypeObj($dataSlug)
    {
        if (sizeof($this->typeGroups) > 0) {
            foreach ($this->typeGroups as $group) {
                if (sizeof($group->types) > 0) {
                    foreach ($group->types as $type) {
                        if ($type->slug == $dataSlug) {
                            return $type;
                        }
                    }
                }
            }
        }
        return null;
    }

    public function getDataPointTitle($dataSlug)
    {
        $type = $this->getDataTypeObj($dataSlug);
        if ($type && isset($type->title)) {
            return $type->title;
        }
        return '';
    }

    public function getDataPointUnit($dataSlug)
    {
        $type = $this->getDataTypeObj($dataSlug);
        if ($type && isset($type->unit)) {
            return $type->unit;
        }
        return '';
    }

    public function getDataPointFields($dataSlug)
    {
        $type = $this->getDataTypeObj($dataSlug);
        if ($type && isset($type->fields)) {
            return $type->fields;
        }
        return [];
    }

    public function replaceInAllFieldNames($replace, $with)
    {
        if (sizeof($this->typeGroups) > 0) {
            foreach ($this->typeGroups as $g => $group) {
                if (sizeof($group->types) > 0) {
                    foreach ($group->types as $t => $type) {
                        $this->typeGroups[$g]->types[$t]->replaceInFieldNames($replace, $with);
                    }
                }
            }
        }
    }

    public function replaceInAllLabels($replace, $with)
    {
        if (sizeof($this->typeGroups) > 0) {
            foreach ($this->typeGroups as $g => $group) {
                if (sizeof($group->types) > 0) {
                    foreach ($group->types as $t => $type) {
                        $this->typeGroups[$g]->types[$t]->replaceInAllLabels($replace, $with);
                    }
                }
            }
        }
    }

    public function skipDataType($type)
    {
        if (!in_array($type, $this->skips)) {
            $this->skips[] = $type;
        }
    }

    public function printDropOpts($presel = '')
    {
        return view(
            'vendor.survloop.reports.graph-data-type-dropdown-opts',
            [
                "typeGroups" => $this->typeGroups,
                "skips"      => $this->skips,
                "presel"     => $presel
            ]
        )->render();
    }

}


class SurvGraphDataTypeGroups
{
    public $title = '';
    public $types = [];

    public function __construct($title = '')
    {
        $this->title = $title;
    }

}
