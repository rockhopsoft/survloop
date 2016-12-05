<?php
namespace SurvLoop\Controllers;

class StatesUS
{
    public $stateList = array();
    
    function __construct()
    {
        $this->stateList = 
        [
            'AL'=>"Alabama", 'AK'=>"Alaska", 'AZ'=>"Arizona", 'AR'=>"Arkansas", 'CA'=>"California", 'CO'=>"Colorado", 
            'CT'=>"Connecticut", 'DE'=>"Delaware", 'DC'=>"District Of Columbia", 'FL'=>"Florida", 'GA'=>"Georgia", 'HI'=>"Hawaii", 
            'ID'=>"Idaho", 'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa", 'KS'=>"Kansas", 'KY'=>"Kentucky", 'LA'=>"Louisiana", 
            'ME'=>"Maine", 'MD'=>"Maryland", 'MA'=>"Massachusetts", 'MI'=>"Michigan", 'MN'=>"Minnesota", 'MS'=>"Mississippi", 
            'MO'=>"Missouri", 'MT'=>"Montana", 'NE'=>"Nebraska", 'NV'=>"Nevada", 'NH'=>"New Hampshire", 'NJ'=>"New Jersey", 'NM'=>"New Mexico", 
            'NY'=>"New York", 'NC'=>"North Carolina", 'ND'=>"North Dakota", 'OH'=>"Ohio", 'OK'=>"Oklahoma", 'OR'=>"Oregon", 'PA'=>"Pennsylvania", 
            'RI'=>"Rhode Island", 'SC'=>"South Carolina", 'SD'=>"South Dakota", 'TN'=>"Tennessee", 'TX'=>"Texas", 'UT'=>"Utah", 'VT'=>"Vermont", 
            'VA'=>"Virginia", 'WA'=>"Washington", 'WV'=>"West Virginia", 'WI'=>"Wisconsin", 'WY'=>"Wyoming"
        ];
    }
    
    public function stateDrop($state = '', $fed = false)
    {
        $retVal = '<option value="" ' . (($state == '') ? 'SELECTED' : '') . ' ></option>';
        if ($fed) $retVal .= '<option value="US" ' . (($state == 'US') ? 'SELECTED' : '') . ' >Federal</option>';
        foreach ($this->stateList as $abbr => $name)
        {
            $retVal .= '<option value="' . $abbr . '" ' . (($state == $abbr) ? 'SELECTED' : '') . ' >' . $abbr . '</option>';
        }
        return $retVal;
    }
    
    public function getStateAbrr($state = '')
    {
        if ($state == 'Federal') return 'US';
        foreach ($this->stateList as $abbr => $name)
        {
            if (strtolower($name) == strtolower($state)) return $abbr;
        }
        return '';
    }
    
    public function getState($abbr = '')
    {
        if ($abbr == 'US') return 'Federal';
        return $this->stateList[$abbr];
    }
    
    public function loadStateResponseVals()
    {
        $retArr = array();
        foreach ($this->stateList as $abbr => $name) $retArr[] = $abbr;
        return $retArr;
    }
    
}
