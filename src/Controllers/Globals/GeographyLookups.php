<?php
/**
  * GeographyLookups has the data lookups required for geographical analysis,
  * including climate zones.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.2.5
  */
namespace SurvLoop\Controllers\Globals;

use App\Models\SLZips;
use App\Models\SLZipAshrae;
use App\Models\SLNodeResponses;

class GeographyLookups extends GeographyLists
{
    protected $zoneZips = [];

    public function getStateAbrr($state = '')
    {
        if ($state == 'Federal') return 'US';
        $this->loadStates();
        foreach ($this->stateList as $abbr => $name) {
            if (strtolower($name) == strtolower($state)) {
                return $abbr;
            }
        }
        if ($this->hasCanada) {
            foreach ($this->stateListCa as $abbr => $name) {
                if (strtolower($name) == strtolower($state)) {
                    return $abbr;
                }
            }
        }
        return '';
    }
    
    public function getState($abbr = '')
    {
        if ($abbr == '') {
            return '';
        }
        if ($abbr == 'US') {
            return 'Federal';
        }
        $this->loadStates();
        if (isset($this->stateList[$abbr])) {
            return $this->stateList[$abbr];
        }
        if (isset($this->stateListCa[$abbr])) {
            return $this->stateListCa[$abbr];
        }
        return '';
    }
    
    public function getStateByInd($ind = -1)
    {
        $cnt = 1;
        $this->loadStates();
        foreach ($this->stateList as $abbr => $name) {
            if ($ind == $cnt) {
                return $abbr;
            }
            $cnt++;
        }
        if ($this->hasCanada) {
            foreach ($this->stateListCa as $abbr => $name) {
                if ($ind == $cnt) {
                    return $abbr;
                }
                $cnt++;
            }
        }
        return '';
    }
    
    public function loadStateResponseVals()
    {
        $this->loadStates();
        $retArr = [];
        foreach ($this->stateList as $abbr => $name) {
            $retArr[] = $abbr;
        }
        if ($this->hasCanada) {
            foreach ($this->stateListCa as $abbr => $name) {
                $retArr[] = $abbr;
            }
        }
        return $retArr;
    }
    
    public function getCountryResponses($nID, $showKidsList = [])
    {
        $this->loadCountries();
        $ret = [];
        foreach ($this->countryList as $i => $name) {
            $res = new SLNodeResponses;
            $res->NodeResNode     = $nID;
            $res->NodeResOrd      = $i;
            $res->NodeResEng      = $name;
            $res->NodeResValue    = $name;
            $res->NodeResShowKids = ((in_array($name, $showKidsList)) ? 1 : 0);
            $ret[] = $res;
        }
        return $ret;
    }
    
    public function getZipRow($zip = '')
    {
        if (trim($zip) == '') {
            return null;
        }
        if (strlen($zip) > 7) {
            $zip = substr($zip, 0, 5);
        }
        return SLZips::where('ZipZip', $zip)
            ->first();
    }
    
    public function getZipProperty($zip = '', $fld = 'City')
    {
        $zipRow = $this->getZipRow($zip);
        if ($zipRow && isset($zipRow->{ 'Zip' . $fld })) {
            return $zipRow->{ 'Zip' . $fld };
        }
        return '';
    }
    
    public function getCityCounty($city = '', $state = '')
    {
        $chk = SLZips::where('ZipCity', $city)
            ->where('ZipState', $state)
            ->first();
        if ($chk && isset($chk->ZipCounty)) {
            return $chk->ZipCounty;
        }
        return '';
    }
    
    public function getAshrae($zipRow = null)
    {
        if (!$zipRow) {
            return '';
        }
        if (isset($zipRow->ZipCountry) 
            && $zipRow->ZipCountry == 'Canada') {
            return 'Canada';
        }
        if ((!isset($zipRow->ZipCountry) 
            || trim($zipRow->ZipCountry) == '') 
            && isset($zipRow->ZipState) 
            && !in_array($zipRow->ZipState, $this->getTerritoryAbbrs())) {
            $ashrae = SLZipAshrae::where('AshrState', $zipRow->ZipState)
                ->where('AshrCounty', $zipRow->ZipCounty)
                ->first();
            if ($ashrae && isset($ashrae->AshrZone)) {
                return $ashrae->AshrZone;
            }
        }
        return '';   
    }
    
    public function countryDrop($cntry = '')
    {
        $this->loadCountries();
        return view(
            'vendor.survloop.forms.inc-drop-opts-countries', 
            [
                "cntry"       => trim($cntry),
                "countryList" => $this->countryList
            ]
        )->render();
    }
    
    public function stateDrop($state = '', $all = false)
    {
        $this->loadStates();
        return view(
            'vendor.survloop.forms.inc-drop-opts-states', 
            [
                "state"       => trim($state),
                "stateList"   => $this->stateList,
                "stateListCa" => $this->stateListCa,
                "hasCanada"   => $this->hasCanada,
                "all"         => $all
            ]
        )->render();
    }
    
    public function stateResponses($all = false)
    {
        $this->loadStates();
        $responses = [];
        $cnt = 0;
        foreach ($this->stateList as $abbr => $name) {
            $responses[$cnt] = new SLNodeResponses;
            $responses[$cnt]->NodeResValue = $abbr;
            $responses[$cnt]->NodeResEng = $name 
                . ' (' . $abbr . ')';
            $cnt++;
        }
        if ($this->hasCanada) {
            foreach ($this->stateListCa as $abbr => $name) {
                $responses[$cnt] = new SLNodeResponses;
                $responses[$cnt]->NodeResValue = $abbr;
                $responses[$cnt]->NodeResEng = $name 
                    . ' (' . $abbr . ')';
                $cnt++;
            }
        }
        return $responses;
    }
    
    public function climateZoneDrop($fltClimate = '')
    {
        return view(
            'vendor.survloop.forms.inc-drop-opts-ashrae', 
            [
                "fltClimate" => $fltClimate,
                "hasCanada"  => $this->hasCanada
            ]
        )->render();
    }
    
    public function climateGroupDrop($fltClimate = '')
    {
        return view(
            'vendor.survloop.forms.inc-drop-opts-ashrae-groups', 
            [
                "fltClimate" => $fltClimate
            ]
        )->render();
    }
    
    public function stateClimateDrop($state = '', $all = false)
    {
        return $this->climateGroupDrop($state) 
            . '<option disabled ></option>'
            . $this->stateDrop($state, $all);
    }
    
    public function getStateWhereIn($fltState = '')
    {
        $ret = [];
        if (trim($fltState) != '') {
            $this->loadStates();
            if ($fltState == 'US') {
                foreach ($this->stateList as $abbr => $name) {
                    $ret[] = $abbr;
                }
            } elseif ($fltState == 'Canada') {
                foreach ($this->stateListCa as $abbr => $name) {
                    $ret[] = $abbr;
                }
            } else {
                $ret[] = $fltState;
            }
        }
        return $ret;
    }

    public function printAllAbbrs($delim = '", "', $ends = '"')
    {
        $ret = '';
        if (sizeof($this->stateList) > 0) {
            foreach ($this->stateList as $abbr => $state) {
                $ret .= $delim . $abbr;
            }
        }
        if (sizeof($this->stateListCa) > 0) {
            foreach ($this->stateListCa as $abbr => $state) {
                $ret .= $delim . $abbr;
            }
        }
        if (trim($ret) != '') {
            $ret = $ends . substr($ret, strlen($delim)) . $ends;
        }
        return $ret;
    }
    
    public function getAshraeZoneLabel($ashraeZone = '')
    {
        switch ($ashraeZone) {
            case '1A':
            case '2A':
            case '2B':
                return 'Hot-Humid';
            case '3A':
            case '3B':
            case '3C':
            case '4A':
            case '4B':
            case '4C':
                return 'Mixed-Humid';
            case '5A':
            case '5B':
            case '6A':
            case '6B':
                return 'Cold';
            case '7A':
            case '7B':
            case 'Ca':
            case 'Canada':
                return 'Very Cold';
            case '8A':
            case '8B':
                return 'Subarctic';
        }
        return '';
    }
    
    public function isAshraeZoneGroup($zoneGroup = '')
    {
        $zones = [
            'Hot-Humid', 
            'Mixed-Humid', 
            'Cold', 
            'Very Cold', 
            'Subarctic'
        ];
        return (in_array($zoneGroup, $zones));
    }
    
    public function getAshraeGroupZones($zoneGroup = '')
    {
        switch ($zoneGroup) {
            case 'Hot-Humid':
                return ['1A', '2A', '2B'];
            case 'Mixed-Humid':
                return ['3A', '3B', '3C', '4A', '4B', '4C'];
            case 'Cold':
                return ['5A', '5B', '6A', '6B'];
            case 'Very Cold':
                return ['7A', '7B', 'Ca', 'Canada'];
            case 'Subarctic':
                return ['8A', '8A'];
        }
        return [];
    }

    public function getZoneOrState($str = '')
    {
        if ($this->isAshraeZoneGroup($str)) {
            return $str;
        }
        $name = $this->getAshraeZoneLabel($str);
        if (trim($name) != '') {
            return $name;
        }
        $name = $this->getState($str);
        if (trim($name) != '') {
            return $name;
        }
        return $str;
    }
    
    public function getAshraeGroupZips($zoneGroup = '')
    {
        if (trim($zoneGroup) == '') {
            return [];
        }
        if (!isset($this->zoneZips[$zoneGroup])) {
            $this->zoneZips[$zoneGroup] = [];
            $zips = DB::table('SL_Zips')
                ->join('SL_ZipAshrae', 'SL_Zips.ZipCounty', 
                    'LIKE', 'SL_ZipAshrae.AshrCounty')
                ->whereIn('SL_ZipAshrae.AshrZone', 
                    $this->getAshraeGroupZones($zoneGroup))
                ->select('SL_Zips.ZipZip')
                ->get();
            if ($zips->isNotEmpty()) {
                foreach ($zips as $zip) {
                    if (!in_array($zip, $zoneZips[$zoneGroup])) {
                        $this->zoneZips[$zoneGroup][] = $zip;
                    }
                }
            }
        }
        return $this->zoneZips[$zoneGroup];
    }

}