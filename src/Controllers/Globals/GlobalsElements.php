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
	public function printAccordian($title, $body = '', $open = false, $big = false, $isCard = false)
	{
		return view('vendor.survloop.elements.inc-accordian', [
			"accordID" => rand(100000, 1000000),
			"title"    => $title,
			"body"     => $body,
			"big"      => $big,
			"open"     => $open,
			"isCard"   => $isCard
		])->render();
	}

	public function printAccard($title, $body = '', $open = false)
	{
		return $this->printAccordian($title, $body, $open, true, true);
	}
}
