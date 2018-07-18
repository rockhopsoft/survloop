<?php
namespace SurvLoop\Controllers;

use App\Models\SLZips;
use App\Models\SLZipAshrae;
use App\Models\SLNodeResponses;

class Geographs
{
    public $stateList   = [];
    public $stateListCa = [];
    public $countryList = [];
    public $hasCanada   = false;
    
    function __construct($hasCanada = false)
    {
        $this->hasCanada = $hasCanada;
        return true;
    }
    
    public function getStateAbrr($state = '')
    {
        if ($state == 'Federal') return 'US';
        $this->loadStates();
        foreach ($this->stateList as $abbr => $name) {
            if (strtolower($name) == strtolower($state)) return $abbr;
        }
        if ($this->hasCanada) {
            foreach ($this->stateListCa as $name) {
                if (strtolower($name) == strtolower($state)) return $name;
            }
        }
        return '';
    }
    
    public function getState($abbr = '')
    {
        if ($abbr == '') return '';
        if ($abbr == 'US') return 'Federal';
        $this->loadStates();
        if (isset($this->stateList[$abbr])) return $this->stateList[$abbr];
        if (isset($this->stateListCa[$abbr])) return $this->stateListCa[$abbr];
        return '';
    }
    
    public function loadStateResponseVals()
    {
        $this->loadStates();
        $retArr = [];
        foreach ($this->stateList as $abbr => $name) $retArr[] = $abbr;
        if ($this->hasCanada) {
            foreach ($this->stateListCa as $name) $retArr[] = $name;
        }
        return $retArr;
    }
    
    public function getCountryResponses($nID, $showKidsList = []) {
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
        if (trim($zip) == '') return null;
        if (strlen($zip) > 7) $zip = substr($zip, 0, 5);
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
        if ($chk && isset($chk->ZipCounty)) return $chk->ZipCounty;
        return '';
    }
    
    public function getAshrae($zipRow = null)
    {
        if (!$zipRow) return '';
        if (isset($zipRow->ZipCountry) && $zipRow->ZipCountry == 'Canada') return 'Canada';
        if ((!isset($zipRow->ZipCountry) || trim($zipRow->ZipCountry) == '') && isset($zipRow->ZipState) 
            && !in_array($zipRow->ZipState, ['PR', 'VI', 'AE', 'MH', 'MP', 'FM', 'PW', 'GU', 'AS', 'AP', 'AA'])) {
            $ashrae = SLZipAshrae::where('AshrState', $zipRow->ZipState)
                ->where('AshrCounty', $zipRow->ZipCounty)
                ->first();
            if ($ashrae && isset($ashrae->AshrZone)) return $ashrae->AshrZone;
        }
        return '';   
    }
    
    public function stateDrop($state = '', $all = false)
    {
        $this->loadStates();
        return view('vendor.survloop.inc-drop-opts-states', [
            "state"       => trim($state),
            "stateList"   => $this->stateList,
            "stateListCa" => $this->stateListCa,
            "hasCanada"   => $this->hasCanada,
            "all"         => $all
            ])->render();
    }
    
    public function climateZoneDrop($fltClimate = '')
    {
        return view('vendor.survloop.inc-drop-opts-ashrae', [
            "fltClimate" => $fltClimate,
            "hasCanada"  => $this->hasCanada
            ])->render();
    }
    
    public function countryDrop($cntry = '')
    {
        $this->loadCountries();
        return view('vendor.survloop.inc-drop-opts-countries', [
            "cntry"       => trim($cntry),
            "countryList" => $this->countryList
            ])->render();
    }
    
    public function getStateWhereIn($fltState = '')
    {
        $ret = [];
        if (trim($fltState) != '') {
            $this->loadStates();
            if ($fltState == 'US') {
                foreach ($this->stateList as $abbr => $name) $ret[] = $abbr;
            } elseif ($fltState == 'Canada') {
                foreach ($this->stateListCa as $abbr => $name) $ret[] = $abbr;
            } else {
                $ret[] = $fltState;
            }
        }
        return $ret;
    }
    
    function loadStates()
    {
        if (empty($this->stateList)) {
            $this->stateList = [
                'AL' => "Alabama", 
                'AK' => "Alaska", 
                'AZ' => "Arizona", 
                'AR' => "Arkansas", 
                'CA' => "California", 
                'CO' => "Colorado", 
                'CT' => "Connecticut", 
                'DE' => "Delaware", 
                'DC' => "District Of Columbia", 
                'FL' => "Florida", 
                'GA' => "Georgia", 
                'HI' => "Hawaii", 
                'ID' => "Idaho", 
                'IL' => "Illinois", 
                'IN' => "Indiana", 
                'IA' => "Iowa", 
                'KS' => "Kansas", 
                'KY' => "Kentucky", 
                'LA' => "Louisiana", 
                'ME' => "Maine", 
                'MD' => "Maryland", 
                'MA' => "Massachusetts", 
                'MI' => "Michigan", 
                'MN' => "Minnesota", 
                'MS' => "Mississippi", 
                'MO' => "Missouri", 
                'MT' => "Montana", 
                'NE' => "Nebraska", 
                'NV' => "Nevada", 
                'NH' => "New Hampshire", 
                'NJ' => "New Jersey", 
                'NM' => "New Mexico", 
                'NY' => "New York", 
                'NC' => "North Carolina", 
                'ND' => "North Dakota", 
                'OH' => "Ohio", 
                'OK' => "Oklahoma", 
                'OR' => "Oregon", 
                'PA' => "Pennsylvania", 
                'RI' => "Rhode Island", 
                'SC' => "South Carolina", 
                'SD' => "South Dakota", 
                'TN' => "Tennessee", 
                'TX' => "Texas", 
                'UT' => "Utah", 
                'VT' => "Vermont", 
                'VA' => "Virginia", 
                'WA' => "Washington", 
                'WV' => "West Virginia", 
                'WI' => "Wisconsin", 
                'WY' => "Wyoming"
            ];
            if ($this->hasCanada) $this->loadCanadaStates();
        }
        return true;
    }
    
    function loadCanadaStates()
    {
        $this->stateListCa = [
            'AB' => "Alberta",
            'BC' => "British Columbia",
            'MB' => "Manitoba",
            'NB' => "New Brunswick",
            'NL' => "Newfoundland and Labrador",
            'NS' => "Nova Scotia",
            'NT' => "Northwest Territories",
            'NU' => "Nunavut",
            'ON' => "Ontario",
            'PE' => "Prince Edward Island",
            'QC' => "Quebec",
            'SK' => "Saskatchewan",
            'YT' => "Yoken"
            ];
        return true;
    }
    
    public function loadCountries()
    {
        if (empty($this->countryList)) {
            $this->countryList = [
                'United States', 
                'Afghanistan', 
                'Albania', 
                'Algeria', 
                'Andorra', 
                'Angola', 
                'Antigua and Barbuda', 
                'Argentina', 
                'Armenia', 
                'Aruba', 
                'Australia', 
                'Austria', 
                'Azerbaijan', 
                'Bahamas, The', 
                'Bahrain', 
                'Bangladesh', 
                'Barbados', 
                'Belarus', 
                'Belgium', 
                'Belize', 
                'Benin', 
                'Bhutan', 
                'Bolivia', 
                'Bosnia and Herzegovina', 
                'Botswana', 
                'Brazil', 
                'Brunei', 
                'Bulgaria', 
                'Burkina Faso', 
                'Burma', 
                'Burundi', 
                'Cambodia', 
                'Cameroon', 
                'Canada', 
                'Cabo Verde', 
                'Central African Republic', 
                'Chad', 
                'Chile', 
                'China', 
                'Colombia', 
                'Comoros', 
                'Congo, Democratic Republic of the (formerly Zaire)', 
                'Congo, Republic of the', 
                'Costa Rica', 
                'Cote d\'Ivoire', 
                'Croatia', 
                'Cuba', 
                'Curacao', 
                'Cyprus', 
                'Czechia', 
                'Denmark', 
                'Djibouti', 
                'Dominica', 
                'Dominican Republic', 
                'East Timor (Timor-Leste)', 
                'Ecuador', 
                'Egypt', 
                'El Salvador', 
                'Equatorial Guinea', 
                'Eritrea', 
                'Estonia', 
                'Ethiopia', 
                'Fiji', 
                'Finland', 
                'France', 
                'Gabon', 
                'Gambia, The', 
                'Georgia', 
                'Germany', 
                'Ghana', 
                'Greece', 
                'Grenada', 
                'Guatemala', 
                'Guinea', 
                'Guinea-Bissau', 
                'Guyana', 
                'Haiti', 
                'Honduras', 
                'Hong Kong', 
                'Hungary', 
                'Iceland', 
                'India', 
                'Indonesia', 
                'Iran', 
                'Iraq', 
                'Ireland', 
                'Israel', 
                'Italy', 
                'Jamaica', 
                'Japan', 
                'Jordan', 
                'Kazakhstan', 
                'Kenya', 
                'Kiribati', 
                'Korea, North', 
                'Korea, South', 
                'Kosovo', 
                'Kuwait', 
                'Kyrgyzstan', 
                'Laos', 
                'Latvia', 
                'Lebanon', 
                'Lesotho', 
                'Liberia', 
                'Libya', 
                'Liechtenstein', 
                'Lithuania', 
                'Luxembourg', 
                'Macau', 
                'Macedonia', 
                'Madagascar', 
                'Malawi', 
                'Malaysia', 
                'Maldives', 
                'Mali', 
                'Malta', 
                'Marshall Islands', 
                'Mauritania', 
                'Mauritius', 
                'Mexico', 
                'Micronesia', 
                'Moldova', 
                'Monaco', 
                'Mongolia', 
                'Montenegro', 
                'Morocco', 
                'Mozambique', 
                'Namibia', 
                'Nauru', 
                'Nepal', 
                'Netherlands', 
                'New Zealand', 
                'Nicaragua', 
                'Niger', 
                'Nigeria', 
                'Norway', 
                'Oman', 
                'Pakistan', 
                'Palau', 
                'Palestinian Territories', 
                'Panama', 
                'Papua New Guinea', 
                'Paraguay', 
                'Peru', 
                'Philippines', 
                'Poland', 
                'Portugal', 
                'Qatar', 
                'Romania', 
                'Russia', 
                'Rwanda', 
                'Saint Kitts and Nevis', 
                'Saint Lucia', 
                'Saint Vincent and the Grenadines', 
                'Samoa', 
                'San Marino', 
                'Sao Tome and Principe', 
                'Saudi Arabia', 
                'Senegal', 
                'Serbia', 
                'Seychelles', 
                'Sierra Leone', 
                'Singapore', 
                'Sint Maarten', 
                'Slovakia', 
                'Slovenia', 
                'Solomon Islands', 
                'Somalia', 
                'South Africa', 
                'South Sudan', 
                'Spain', 
                'Sri Lanka', 
                'Sudan', 
                'Suriname', 
                'Swaziland', 
                'Sweden', 
                'Switzerland', 
                'Syria', 
                'Taiwan', 
                'Tajikistan', 
                'Tanzania', 
                'Thailand', 
                'Togo', 
                'Tonga', 
                'Trinidad and Tobago', 
                'Tunisia', 
                'Turkey', 
                'Turkmenistan', 
                'Tuvalu', 
                'Uganda', 
                'Ukraine', 
                'United Arab Emirates', 
                'United Kingdom', 
                'Uruguay', 
                'Uzbekistan', 
                'Vanuatu', 
                'Venezuela', 
                'Vietnam', 
                'Yemen', 
                'Zambia', 
                'Zimbabwe'
            ];
        }
        return true;
    }
    
    
    
}
