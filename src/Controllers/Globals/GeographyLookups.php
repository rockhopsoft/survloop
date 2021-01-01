<?php
/**
  * GeographyLookups has the data lookups required for geographical analysis,
  * including climate zones.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */
namespace RockHopSoft\Survloop\Controllers\Globals;

use App\Models\SLZips;
use App\Models\SLZipAshrae;
use App\Models\SLNodeResponses;

class GeographyLookups extends GeographyLists
{
    protected $zoneZips = [];

    public function getAbrr($abbr = '')
    {
        if (trim($abbr) == '') {
            return '';
        }
        $ret = trim($this->getState($abbr));
        if ($ret != '') {
            return $ret;
        }
        $ret = trim($this->getAshraeZoneLabel($abbr));
        if ($ret != '') {
            return 'Climate Zone ' . $abbr;
        }
        return '';
    }

    public function getStateAbrr($state = '')
    {
        if ($state == 'Federal') {
            return 'US';
        }
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
        $abbr = strtoupper($abbr);
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
    
    public function getStateSlug($abbr = '')
    {
        return $GLOBALS["SL"]->slugify($this->getState($abbr));
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

    public function getStateFlagImg($abbr = '')
    {
        if ($abbr == 'US') {
            return '/survloop-libraries/state-flags/united_states.jpg';
        }
        if ($abbr == 'DC') {
            return '/survloop-libraries/state-flags/usa_district_of_columbia.jpg';
        }
        $file = '';
        $this->loadStates();
        $state = $this->getState($abbr);
        if (isset($this->stateList[$abbr])) {
            $file = '/survloop-libraries/state-flags/usa_' 
                . str_replace(' ', '_', strtolower($state)) . '.jpg';
        } else {
            if (isset($this->stateListCa[$abbr])) {
                $file = '/survloop-libraries/state-flags/canada_' 
                    . str_replace(' ', '_', strtolower($state)) . '.jpg';
            }
        }
        return $file;
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
            $res->node_res_node      = $nID;
            $res->node_res_ord       = $i;
            $res->node_res_eng       = $name;
            $res->node_res_value     = $name;
            $res->node_res_show_kids = ((in_array($name, $showKidsList)) ? 1 : 0);
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
        return SLZips::where('zip_zip', $zip)
            ->first();
    }
    
    public function getZipProperty($zip = '', $fld = 'city')
    {
        $zipRow = $this->getZipRow($zip);
        if ($zipRow && isset($zipRow->{ 'zip_' . $fld })) {
            return $zipRow->{ 'zip_' . $fld };
        }
        return '';
    }
    
    public function getCityCounty($city = '', $state = '')
    {
        $chk = SLZips::where('zip_city', $city)
            ->where('zip_state', $state)
            ->first();
        if ($chk && isset($chk->zip_county)) {
            return $chk->zip_county;
        }
        return '';
    }
    
    public function getAshrae($zipRow = null)
    {
        if (!$zipRow) {
            return '';
        }
        if (isset($zipRow->zip_country) 
            && $zipRow->zip_country == 'Canada') {
            return 'Canada';
        }
        if ((!isset($zipRow->zip_country) || trim($zipRow->zip_country) == '') 
            && isset($zipRow->zip_state) 
            && !in_array($zipRow->zip_state, $this->getTerritoryAbbrs())) {
            $ashrae = SLZipAshrae::where('ashr_state', $zipRow->zip_state)
                ->where('ashr_county', $zipRow->zip_county)
                ->first();
            if ($ashrae && isset($ashrae->ashr_zone)) {
                return $ashrae->ashr_zone;
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
            $responses[$cnt]->node_res_value = $abbr;
            $responses[$cnt]->node_res_eng = $name . ' (' . $abbr . ')';
            $cnt++;
        }
        if ($this->hasCanada) {
            foreach ($this->stateListCa as $abbr => $name) {
                $responses[$cnt] = new SLNodeResponses;
                $responses[$cnt]->node_res_value = $abbr;
                $responses[$cnt]->node_res_eng = $name . ' (' . $abbr . ')';
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
            [ "fltClimate" => $fltClimate ]
        )->render();
    }
    
    public function stateClimateDrop($fltStateClim = '', $all = false)
    {
        return $this->climateGroupDrop($fltStateClim) . '<option disabled ></option>'
            . $this->stateDrop($fltStateClim, $all);
    }
    
    public function stateClimateTagsSelect($fltStateClimTag = [], $nID = 1, $classExtra = '')
    {
        return view(
            'vendor.survloop.forms.formtree-climate-tagger', 
            [
                "print"           => 'select',
                "nID"             => $nID,
                "fltStateClimTag" => $fltStateClimTag,
                "stateList"       => $this->stateList,
                "stateListCa"     => $this->stateListCa,
                "hasCanada"       => $this->hasCanada,
                "classExtra"      => $classExtra
            ]
        )->render();
    }
    
    public function stateClimateTagsList($fltStateClimTag = [], $nID = 1)
    {
        return view(
            'vendor.survloop.forms.formtree-climate-tagger', 
            [
                "print"           => 'tag',
                "nID"             => $nID,
                "fltStateClimTag" => $fltStateClimTag
            ]
        )->render();
    }
    
    public function stateClimateTagsJS($fltStateClimTag = [], $nID = 1, $classExtra = '')
    {
        return view(
            'vendor.survloop.forms.formtree-climate-tagger', 
            [
                "print"           => 'js',
                "nID"             => $nID,
                "fltStateClimTag" => $fltStateClimTag,
                "stateList"       => $this->stateList,
                "stateListCa"     => $this->stateListCa,
                "hasCanada"       => $this->hasCanada,
                "classExtra"      => $classExtra
            ]
        )->render();
    }
    
    public function stateClimateTagsInRow($fltStateClimTag = [], $nID = 1, $classExtra = '')
    {
        return '<div class="row"><div class="col-md-4 pB10">'
            . $this->stateClimateTagsSelect($fltStateClimTag, $nID, $classExtra)
            . '</div><div class="col-md-8 pT0 pB10">'
            . $this->stateClimateTagsList($fltStateClimTag, $nID)
            . '</div></div>'
            . $this->stateClimateTagsJS($fltStateClimTag, $nID, $classExtra);
    }

    public function getTagsStates($fltStateClimTag = [])
    {
        $states = $zones = [];
        if (sizeof($fltStateClimTag) > 0) {
            foreach ($fltStateClimTag as $tag) {
                if ($this->getState($tag) != '') {
                    $states[] = $tag;
                }
            }
        }
        if (sizeof($fltStateClimTag) > 0) {
            foreach ($fltStateClimTag as $tag) {
                if ($this->getAshraeZoneLabel($tag) != '') {
                    $zones[] = $tag;
                }
            }
        }
        return [ $states, $zones ];
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
            $zips = DB::table('sl_zips')
                ->join('sl_zip_ashrae', 'sl_zips.zip_county', 
                    'LIKE', 'sl_zip_ashrae.ashr_county')
                ->whereIn('sl_zip_ashrae.ashr_zone', 
                    $this->getAshraeGroupZones($zoneGroup))
                ->select('sl_zips.zip_zip')
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