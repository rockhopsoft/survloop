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
    public $collections = [];
    public $c           = -1;
    public $skips       = [];

    public function addNewCollection($title = '')
    {
        $this->collections[] = new SurvGraphDataTypeCollections($title);
        $this->c++;
    }

    public function addNewGroup($title = '')
    {
        $this->collections[$this->c]->addNewGroup($title);
    }

    public function addType($slug = '', $title = '', $unit = '', $fields = [])
    {
        $this->collections[$this->c]->addType($slug, $title, $unit, $fields);
    }

    public function setAxisMaxY($axisMaxY = '100')
    {
        $this->collections[$this->c]->setAxisMaxY($axisMaxY);
    }

    public function setAxisMinY($axisMinY = '0')
    {
        $this->collections[$this->c]->setAxisMinY($axisMinY);
    }

    public function clearTypeAxisMinY($axisMinY = '')
    {
        $this->collections[$this->c]->clearTypeAxisMinY($axisMinY);
    }

    public function getDataTypeObj($dataSlug)
    {
        if (sizeof($this->collections) > 0) {
            foreach ($this->collections as $collection) {
                if (sizeof($collection->groups) > 0) {
                    foreach ($collection->groups as $group) {
                        if (sizeof($group->types) > 0) {
                            foreach ($group->types as $type) {
                                if ($type->slug == $dataSlug) {
                                    return $type;
                                }
                            }
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

    public function getDataPointCat($dataSlug)
    {
        if (sizeof($this->collections) > 0) {
            foreach ($this->collections as $c => $collection) {
                if (sizeof($collection->groups) > 0) {
                    foreach ($collection->groups as $g => $group) {
                        if (sizeof($group->types) > 0) {
                            foreach ($group->types as $type) {
                                if ($type->slug == $dataSlug) {
                                    return $c . 'g' . $g;
                                }
                            }
                        }
                    }
                }
            }
        }
        return 'g';
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
        if (sizeof($this->collections) > 0) {
            foreach ($this->collections as $c => $collection) {
                if (sizeof($collection->groups) > 0) {
                    foreach ($collection->groups as $g => $group) {
                        if (sizeof($group->types) > 0) {
                            foreach ($group->types as $t => $type) {
                                $this->collections[$c]->groups[$g]->types[$t]
                                    ->replaceInFieldNames($replace, $with);
                            }
                        }
                    }
                }
            }
        }
    }

    public function replaceInAllLabels($replace, $with)
    {
        if (sizeof($this->collections) > 0) {
            foreach ($this->collections as $c => $collection) {
                if (sizeof($collection->groups) > 0) {
                    foreach ($collection->groups as $g => $group) {
                        if (sizeof($group->types) > 0) {
                            foreach ($group->types as $t => $type) {
                                $this->collections[$c]->groups[$g]->types[$t]
                                    ->replaceInAllLabels($replace, $with);
                            }
                        }
                    }
                }
            }
        }
    }

    public function findSlugLocation($dataSlug)
    {
        $c = $g = 0;
        if (sizeof($this->collections) > 0) {
            foreach ($this->collections as $c => $collection) {
                if (sizeof($collection->groups) > 0) {
                    foreach ($collection->groups as $g => $group) {
                        if (sizeof($group->types) > 0) {
                            foreach ($group->types as $type) {
                                if ($type->slug == $dataSlug) {
                                    return [ $c, $g ];
                                }
                            }
                        }
                    }
                }
            }
        }
        return [ 0, 0 ];
    }


    public function skipDataType($type)
    {
        if (!in_array($type, $this->skips)) {
            $this->skips[] = $type;
        }
    }

    public function printDropCatOpts($presel = '')
    {
        if (sizeof($this->collections) > 0) {
            foreach ($this->collections as $c => $collection) {
                $this->collections[$c]->skips = $this->skips;
                foreach ($collection->groups as $g => $group) {
                    $this->collections[$c]->groups[$g]->skips = $this->skips;
                }
            }
        }
        return view(
            'vendor.survloop.reports.graph-data-cats-dropdown-opts',
            [
                "collections"  => $this->collections,
                "skips"        => $this->skips
            ]
        )->render();
    }

    public function addAllTagOptExtra($nID = '1', $reqDataSlugs = [])
    {
        $ret = '';
        if (sizeof($this->collections) > 0) {
            foreach ($this->collections as $c => $collection) {
                if (sizeof($collection->groups) > 0) {
                    foreach ($collection->groups as $g => $group) {
                        if (sizeof($group->types) > 0) {
                            foreach ($group->types as $t => $type) {
                                $ret .= 'addTagOptExtra("' . $nID . '", "'
                                    . $type->slug . '", ' . json_encode($type->title) . ', '
                                    . ((in_array($type->slug, $reqDataSlugs)) ? 1 : 0)
                                    . ', ""); ';
                            }
                        }
                    }
                }
            }
        }
        return $ret;
    }

}


class SurvGraphDataTypeCollections
{
    public $title  = '';
    public $groups = [];
    public $g      = -1;
    public $skips  = [];

    public function __construct($title = '')
    {
        $this->title = $title;
    }

    public function addNewGroup($title = '')
    {
        $this->groups[] = new SurvGraphDataTypeGroups($title);
        $this->g++;
    }

    public function addType($slug = '', $title = '', $unit = '', $fields = [])
    {
        $this->groups[$this->g]->types[] = new SurvGraphDataType($slug, $title, $unit, $fields);
    }

    public function setAxisMaxY($axisMaxY = '100')
    {
        $last = sizeof($this->groups[$this->g]->types)-1;
        $this->groups[$this->g]->types[$last]->axisY->max = $axisMaxY;
    }

    public function setAxisMinY($axisMinY = '0')
    {
        $last = sizeof($this->groups[$this->g]->types)-1;
        $this->groups[$this->g]->types[$last]->axisY->min = $axisMinY;
    }

    public function clearTypeAxisMinY($axisMinY = '')
    {
        $last = sizeof($this->groups[$this->g]->types)-1;
        $this->groups[$this->g]->types[$last]->axisMinY = $axisMinY;
    }

}


class SurvGraphDataTypeGroups
{
    public $title = '';
    public $types = [];
    public $skips = [];

    public function __construct($title = '')
    {
        $this->title = $title;
    }

    public function printDropOpts($presel = '')
    {
        return view(
            'vendor.survloop.reports.graph-data-type-dropdown-opts',
            [
                "types"  => $this->types,
                "skips"  => $this->skips,
                "presel" => $presel
            ]
        )->render();
    }

}