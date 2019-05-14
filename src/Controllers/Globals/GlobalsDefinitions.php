<?php
/**
  * GlobalsDefinitions is a side-class which loads and gets installation definition sets.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers\Globals;

use App\Models\SLDefinitions;

class GlobalsDefinitions
{   
	private $dbID = 1;
	
    function __construct($dbID = 1)
    {
        $this->dbID = $dbID;
    }
    
    public function loadDefs($subset)
    {
        if (!isset($this->defValues[$subset])) {
            $this->defValues[$subset] = SLDefinitions::where('DefDatabase', $this->dbID)
                ->where('DefSubset', $subset)
                ->where('DefSet', 'Value Ranges')
                ->orderBy('DefOrder', 'asc')
                ->select('DefID', 'DefValue')
                ->get();
        }
        return true;
    }
    
    public function getID($subset = '', $value = '')
    {
        $this->loadDefs($subset);
        if ($this->defValues[$subset]->isNotEmpty()) {
            foreach ($this->defValues[$subset] as $def) {
                if ($def->DefValue == $value) {
                    return $def->DefID;
                }
            }
        }
        return -3;
    }
    
    public function getValById($id = -3)
    {
        if ($id <= 0) {
            return '';
        }
        $def = SLDefinitions::find($id);
        if ($def && isset($def->DefValue)) {
            return trim($def->DefValue);
        }
        return '';
    }
    
    public function getVal($subset = '', $id = '')
    {
        if ($subset == 'Yes/No') {
            if (in_array($id, ['Y', '1'])) {
                return 'Yes';
            }
            if (in_array($id, ['N', '0'])) {
                return 'No';
            }
            if ($id == '?') {
                return 'Not sure';
            }
            return '';
        }
        $this->loadDefs($subset);
        if ($this->defValues[$subset]->isNotEmpty()) {
            foreach ($this->defValues[$subset] as $def) {
                if ($def->DefID == $id) {
                    return $def->DefValue;
                }
            }
        }
        return '';
    }
    
    public function getSet($subset = '')
    {
        $this->loadDefs($subset);
        return $this->defValues[$subset];
    }
    
    public function getSetDrop($subset = '', $presel = -3, $skip = [])
    {
        $ret = '';
        $this->loadDefs($subset);
        if ($this->defValues[$subset]->isNotEmpty()) {
            foreach ($this->defValues[$subset] as $i => $val) {
                if (sizeof($skip) == 0 || !in_array($val->DefID, $skip)) {
                    $ret .= '<option value="' . $val->DefID . '" ' . (($presel == $val->DefID) ? 'SELECTED ' : '')
                        . '>' . $val->DefValue . '</option>';
                }
            }
        }
        return $ret;
    }
    
    public function getDesc($subset = '', $val = '')
    {
        $chk = SLDefinitions::where('DefDatabase', $this->dbID)
            ->where('DefSet', 'Value Ranges')
            ->where('DefSubset', $subset)
            ->where('DefValue', $val)
            ->first();
        if ($chk && isset($chk->DefDescription)) {
            return $chk->DefDescription;
        }
        return '';
    }
}