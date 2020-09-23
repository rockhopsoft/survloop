<?php
/**
  * SurvDataTestsAB is a small helper class to track the current user-survey-session's list
  * of relevant AB Tests, and their active/inactive aka true/false aka pass/fail status.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.1.3
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

class SurvDataTestsAB
{
    public $tests = [];
    
    public function addParamsAB($params = '')
    {
        $abs = $GLOBALS["SL"]->mexplode('.', $params);
        if (sizeof($abs) > 0) {
            foreach ($abs as $i => $ab) {
                foreach (['a', 'b'] as $let) {
                    if (strpos($ab, $let) > 0) {
                        $abNum = intVal(str_replace($let, '', $ab));
                        $this->addAB($abNum, ($let == 'a'));
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
    
    public function checkCond($conditionID = 0)
    {
        if (intVal($conditionID) > 0 && sizeof($this->tests) > 0) {
            foreach ($this->tests as $test) {
                if ($conditionID == $test->id && $test->isActive) {
                    return true;
                }
            }
        }
        return false;
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