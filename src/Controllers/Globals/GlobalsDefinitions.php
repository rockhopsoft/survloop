<?php
/**
  * GlobalsDefinitions is a side-class which loads and gets installation definition sets.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Globals;

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
            $this->defValues[$subset] = SLDefinitions::where('def_database', $this->dbID)
                ->where('def_subset', $subset)
                ->where('def_set', 'Value Ranges')
                ->orderBy('def_order', 'asc')
                ->select('def_id', 'def_value')
                ->get();
        }
        return true;
    }
    
    public function getID($subset = '', $value = '')
    {
        $this->loadDefs($subset);
        if ($this->defValues[$subset]->isNotEmpty()) {
            foreach ($this->defValues[$subset] as $def) {
                if ($def->def_value == $value) {
                    return $def->def_id;
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
        if ($def && isset($def->def_value)) {
            return trim($def->def_value);
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
                if ($def->def_id == $id) {
                    return $def->def_value;
                }
            }
        }
        return '';
    }
    
    public function getSet($subset = '', $fullRecs = false)
    {
        if ($fullRecs) {
            return SLDefinitions::where('def_database', $this->dbID)
                ->where('def_subset', $subset)
                ->where('def_set', 'Value Ranges')
                ->orderBy('def_order', 'asc')
                ->get();
        }
        $this->loadDefs($subset);
        return $this->defValues[$subset];
    }
    
    public function getSetDrop($subset = '', $presel = -3, $skip = [])
    {
        $ret = '';
        $this->loadDefs($subset);
        if ($this->defValues[$subset]->isNotEmpty()) {
            foreach ($this->defValues[$subset] as $i => $val) {
                if (sizeof($skip) == 0 || !in_array($val->def_id, $skip)) {
                    $ret .= '<option value="' . $val->def_id . '" ' 
                        . (($presel == $val->def_id) ? 'SELECTED ' : '')
                        . '>' . $val->def_value . '</option>';
                }
            }
        }
        return $ret;
    }
    
    public function getDesc($subset = '', $val = '')
    {
        $chk = SLDefinitions::where('def_database', $this->dbID)
            ->where('def_set', 'Value Ranges')
            ->where('def_subset', $subset)
            ->where('def_value', $val)
            ->first();
        if ($chk && isset($chk->def_description)) {
            return $chk->def_description;
        }
        return '';
    }

    public function getOtherGenders()
    {
        $ret = [];
        $set = $this->getSet('Gender Identity');
        if (sizeof($set) > 0) {
            foreach ($set as $i => $gen) {
                $ret[] = $gen->def_value;
            }
        } else {
            $ret = [
                'Transgender',
                'Female to male transgender',
                'Male to female transgender',
                'Genderqueer/Androgynous',
                'Cross-dresser',
                'Transsexual',
                'Intersex'
            ];
        }
        return $ret;
    }

}