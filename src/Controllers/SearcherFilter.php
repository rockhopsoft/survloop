<?php
/**
  * SearcherFilter manages a single filter's properties and custom functionality.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.18
  */
namespace RockHopSoft\Survloop\Controllers;

use DB;

class SearcherFilter
{
    public $abbr        = 'flt';
    public $title       = '';
    public $idType      = 'int';
    public $defSet      = '';
    public $options     = [];
    public $selected    = null;
    public $hideIfEmpty = false;

    public function __construct($abbr = 'flt', $title = '', $idType = 'int')
    {
        $this->abbr   = trim($abbr);
        $this->title  = trim($title);
        $this->idType = $idType;
        $this->initExtend();
    }

    protected function initExtend()
    {
        return true;
    }

    public function setHideIfEmpty()
    {
        $this->hideIfEmpty = true;
    }

    public function isEmpty()
    {
        if ($this->selected === null) {
            return true;
        }
        if ($this->idType == 'int') {
            return ($this->selected == 0);
        }
        return ($this->selected == '');
    }

    public function getValFromID($id = null)
    {
        if ($id !== null && sizeof($this->options) > 0) {
            foreach ($this->options as $chkOption) {
                if ($id == $chkOption->id) {
                    return trim($chkOption->label);
                }
            }
        }
        return '';
    }

    public function getValFromSelectedID()
    {
        return $this->getValFromID($this->selected);
    }

    public function loadOptsFromDefSet($defSet = '')
    {
        $this->options = [];
        $set = $GLOBALS["SL"]->def->getSet($defSet);
        if ($set && sizeof($set) > 0) {
            foreach ($set as $d) {
                $this->options[] = new SearcherFilterOption($d->def_id, $d->def_value);
            }
        }
        return $this->options;
    }

    public function loadOptsFromArr($array = [])
    {
        $this->options = [];
        if (sizeof($array) > 0) {
            foreach ($array as $id => $label) {
                $found = false;
                if (sizeof($this->options) > 0) {
                    foreach ($this->options as $chkOption) {
                        if ($id == $chkOption->id) {
                            $found = true;
                        }
                    }
                }
                if (!$found) {
                    $this->options[] = new SearcherFilterOption($id, $label);
                }
            }
        }
        return $this->options;
    }

    public function chkFormInput()
    {
        if ($this->idType == 'int') {
            $this->selected = 0;
        } else {
            $this->selected = '';
        }
        if ($GLOBALS["SL"]->REQ->has($this->abbr) 
            && trim($GLOBALS["SL"]->REQ->get($this->abbr)) != '') {
            if ($this->idType == 'int') {
                $this->selected = intVal($GLOBALS["SL"]->REQ->get($this->abbr));
            } else {
                $this->selected = trim($GLOBALS["SL"]->REQ->get($this->abbr));
            }
        }
        return $this->selected;
    }

    public function printDesc()
    {
        if ($this->idType == 'int') {
            if ($this->selected > 0) {
                return ', ' . strtolower($this->getValFromSelectedID());
            }
        } elseif ($this->selected != '') {
            return ', ' . strtolower($this->getValFromSelectedID());
        }
        return '';
    }

    public function printUrl()
    {
        if ($this->idType == 'int') {
            if ($this->selected > 0) {
                return '&' . $this->abbr . '=' . $this->selected;
            }
        } elseif ($this->selected != '') {
            return '&' . $this->abbr . '=' . $this->selected;
        }
        return '';
    }

    public function printEval()
    {
        return '';
    }

    public function printEvalBasic($tbl, $fldMatch, $fldID, $coreTblFldID)
    {
        if (!$this->isEmpty()) {
            $chk = DB::table($tbl)
                ->where($fldMatch, 'LIKE', $this->selected)
                ->select($fldID)
                ->get();
            $psids = $GLOBALS["SL"]->resultsToArrIds($chk, $fldID);
            return "->whereIn('" . $coreTblFldID . "', [" 
                . ((sizeof($psids) > 0) ? implode(', ', $psids) : 0)
                . "])";
        }
        return '';
    }

    public function printDropdownOpts()
    {
        $ret = '';
        if (sizeof($this->options) > 0) {
            foreach ($this->options as $opt) {
                $selected = '';
                if ($this->selected == $opt->id) {
                    $selected = ' SELECTED ';
                }
                $ret .= '<option value="' . $opt->id . '"' . $selected . '>'
                    . $opt->label . '</option>';
            }
        }
        return $ret;
    }

    public function printDropdown($class = '', $defID = '', $defLabel = '')
    {
        list($defID, $defLabel) = $this->getPrintDefaults($defID, $defLabel);
        $selected = '';
        if ($this->isEmpty()) {
            $selected = ' SELECTED ';
        }
        return '<select name="' . $this->abbr . '" '
            . 'id="' . $this->abbr . 'ID" autocomplete="off" '
            . 'class="form-control ntrStp slTab ' . $class . '" '
            . $GLOBALS["SL"]->tabInd() . ' ><option value="' . $defID 
            . '" ' . $selected . ' >' . $defLabel . '</option>'
            . $this->printDropdownOpts() . '</select>';
    }

    public function printDropdownColWrap($class = '', $cols = 4, $defID = '', $defLabel = '', $wrap1 = '', $wrap2 = '')
    {
        return '<div id="' . $this->abbr . 'Wrap" class="col-md-' . intVal($cols) . ' pB10"'
            . (($this->hideIfEmpty && $this->isEmpty()) ? ' style="display: none;" ' : '')
            . ' >' . $wrap1 . $this->printDropdown($class, $defID, $defLabel) . $wrap2
            . '</div>';
    }

    public function printDropdownColWraps($class = '', $wrap1 = '', $wrap2 = '')
    {
        return $this->printDropdownColWrap($class, 4, '', '', $wrap1, $wrap2);
    }

    public function getPrintDefaults($defID = '', $defLabel = '')
    {
        if ($defID == '' && $this->idType == 'int') {
            $defID = 0;
        }
        if ($defLabel == '') {
            $defLabel = 'All ' . $this->title;
        }
        return [ $defID, $defLabel ];
    }

}



class SearcherFilterOption
{
    public $id    = null;
    public $label = '';

    public function __construct($id = null, $label = '')
    {
        $this->id = $id;
        $this->label = $label;
    }
}