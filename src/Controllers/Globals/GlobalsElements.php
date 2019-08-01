<?php
/**
  * GlobalsTables is a mid-level class for loading and accessing system information from anywhere.
  * This level contains access to the database design, its tables, and field details.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 2.4
  */
namespace SurvLoop\Controllers\Globals;

use SurvLoop\Controllers\Globals\GlobalsStatic;

class GlobalsElements extends GlobalsStatic
{
    public function printAccordian($title, $body = '', $open = false, $big = false, $type = '')
    {
      	return view('vendor.survloop.elements.inc-accordian', [
        		"accordID" => rand(100000, 1000000),
        		"title"    => $title,
        		"body"     => $body,
        		"big"      => $big,
        		"open"     => $open,
            "isCard"   => ($type == 'card'),
            "isText"   => ($type == 'text')
      	])->render();
    }

    public function printAccard($title, $body = '', $open = false)
    {
        return $this->printAccordian($title, $body, $open, true, 'card');
    }

    public function printAccordTxt($title, $body = '', $open = false)
    {
        return $this->printAccordian($title, $body, $open, false, 'text');
    }

    public function addSideNavItem($title = '', $url = '', $delay = 2000)
    {
        $this->pageJAVA .= ' setTimeout(\'addSideNavItem("' . $title . '", "' . $url
            . '")\', ' . $delay . '); ';
        return true;
    }

    public function printDatePicker($dateStr = '', $fldName = '')
    {
        if ($fldName == '') {
          $fldName = rand(10000000, 100000000);
        }
        $this->pageAJAX .= '$( "#' . $fldName . 'ID" ).datepicker({ maxDate: "+0d" });';
        return '<input type="text" name="' . $fldName . '" id="' . $fldName . 'ID" value="' 
            . (($dateStr != '') ? date("m/d/Y", strtotime($dateStr)) : '')
            . '" class="dateFld form-control" ' . $this->tabInd() . ' autocomplete="off" >' . "\n";
    }

}
