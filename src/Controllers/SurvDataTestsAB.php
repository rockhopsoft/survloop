<?php
/**
  * SurvDataTestsAB is a small helper class to track the current user-survey-session's list
  * of relevant AB Tests, and their active/inactive aka true/false aka pass/fail status.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

class SurvDataTestsAB
{
    public $tests = [];
    
    public function addParamsAB($params = '')
    {
        if ($params == '' && $GLOBALS["SL"]->REQ->has('ab') && trim($GLOBALS["SL"]->REQ->get('ab') != '')) {
            $params = trim($GLOBALS["SL"]->REQ->get('ab'));
        }
        $abs = $GLOBALS["SL"]->mexplode('.', $params);
        if (sizeof($abs) > 0) {
            foreach ($abs as $i => $ab) {
                foreach (['a', 'b'] as $let) {
                    if (strpos($ab, $let) > 0) {
                        $this->addAB(intVal(str_replace($let, '', $ab)), ($let == 'a'));
                    }
                }
            }
        }
        return true;
    }
    
    public function addAB($conditionID = 0, $isActive = true)
    {
        $found = false;
        if (sizeof($this->tests) > 0) {
            foreach ($this->tests as $i => $test) {
                if ($test->id == $conditionID) {
                    $this->tests[$i]->isActive = $isActive;
                    $found = true;
                }
            }
        }
        if (!$found) {
            $this->tests[] = new SurvDataAB($conditionID, $isActive);
        }
        return true;
    }
    
    public function printParams()
    {
        $ret = '';
        if (sizeof($this->tests) > 0) {
            foreach ($this->tests as $i => $test) {
                $ret .= (($i > 0) ? '.' : '') . $test->id . (($test->isActive) ? 'a' : 'b');
            }
        }
        return $ret;
    }
    
    public function __toString()
    {
        return $this->printParams();
    }
}

class SurvDataAB
{
    public $id       = 0;
    public $isActive = true;
    
    public function __construct($conditionID = 0, $isActive = true)
    {
        $this->id       = $conditionID;
        $this->isActive = $isActive;
        return true;
    }
}