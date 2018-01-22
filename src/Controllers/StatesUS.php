<?php
namespace SurvLoop\Controllers;

use App\Models\SLZips;
use App\Models\SLZipAshrae;
use App\Models\SLNodeResponses;

class StatesUS
{
    public $stateList = array();
    
    public $countryList = array();
    
    function loadStates()
    {
        if (sizeof($this->stateList) == 0) {
            $this->stateList = [
                'AL'=>"Alabama", 
                'AK'=>"Alaska", 
                'AZ'=>"Arizona", 
                'AR'=>"Arkansas", 
                'CA'=>"California", 
                'CO'=>"Colorado", 
                'CT'=>"Connecticut", 
                'DE'=>"Delaware", 
                'DC'=>"District Of Columbia", 
                'FL'=>"Florida", 
                'GA'=>"Georgia", 
                'HI'=>"Hawaii", 
                'ID'=>"Idaho", 
                'IL'=>"Illinois", 
                'IN'=>"Indiana", 
                'IA'=>"Iowa", 
                'KS'=>"Kansas", 
                'KY'=>"Kentucky", 
                'LA'=>"Louisiana", 
                'ME'=>"Maine", 
                'MD'=>"Maryland", 
                'MA'=>"Massachusetts", 
                'MI'=>"Michigan", 
                'MN'=>"Minnesota", 
                'MS'=>"Mississippi", 
                'MO'=>"Missouri", 
                'MT'=>"Montana", 
                'NE'=>"Nebraska", 
                'NV'=>"Nevada", 
                'NH'=>"New Hampshire", 
                'NJ'=>"New Jersey", 
                'NM'=>"New Mexico", 
                'NY'=>"New York", 
                'NC'=>"North Carolina", 
                'ND'=>"North Dakota", 
                'OH'=>"Ohio", 
                'OK'=>"Oklahoma", 
                'OR'=>"Oregon", 
                'PA'=>"Pennsylvania", 
                'RI'=>"Rhode Island", 
                'SC'=>"South Carolina", 
                'SD'=>"South Dakota", 
                'TN'=>"Tennessee", 
                'TX'=>"Texas", 
                'UT'=>"Utah", 
                'VT'=>"Vermont", 
                'VA'=>"Virginia", 
                'WA'=>"Washington", 
                'WV'=>"West Virginia", 
                'WI'=>"Wisconsin", 
                'WY'=>"Wyoming"
            ];
        }
        return true;
    }
    
    public function stateDrop($state = '', $fed = false)
    {
        $this->loadStates();
        $retVal = ''; // '<option value="" ' . (($state == '') ? 'SELECTED' : '') . ' ></option>';
        if ($fed) $retVal .= '<option value="US" ' . (($state == 'US') ? 'SELECTED' : '') . ' >Federal</option>';
        foreach ($this->stateList as $abbr => $name) {
            $retVal .= '<option value="' . $abbr . '" ' . (($state == $abbr) ? 'SELECTED' : '') 
                . ' >' . $abbr . ' ' . $name . '</option>';
        }
        return $retVal;
    }
    
    public function getStateAbrr($state = '')
    {
        if ($state == 'Federal') return 'US';
        $this->loadStates();
        foreach ($this->stateList as $abbr => $name) {
            if (strtolower($name) == strtolower($state)) return $abbr;
        }
        return '';
    }
    
    public function getState($abbr = '')
    {
        if ($abbr == '') return '';
        if ($abbr == 'US') return 'Federal';
        $this->loadStates();
        return $this->stateList[$abbr];
    }
    
    public function loadStateResponseVals()
    {
        $this->loadStates();
        $retArr = array();
        foreach ($this->stateList as $abbr => $name) $retArr[] = $abbr;
        return $retArr;
    }
    
    
    public function loadCountries()
    {
        if (sizeof($this->countryList) == 0) {
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
    
    public function countryDrop($cntry = '')
    {
        $this->loadCountries();
        $retVal = '<option value="" ' . (($cntry == '') ? 'SELECTED' : '') . ' ></option>';
        foreach ($this->countryList as $name) {
            $retVal .= '<option value="' . $name . '" ' . (($cntry == $name) ? 'SELECTED' : '') 
                . ' >' . $name . '</option>';
        }
        return $retVal;
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
        if (trim($zip) == '') return [];
        if (strlen($zip) > 5) $zip = substr($zip, 0, 5);
        return SLZips::where('ZipZip', $zip)
            ->first();
    }
    
    public function getZipCity($zip = '')
    {
        $zipRow = $this->getZipRow($zip);
        if ($zipRow && isset($zipRow->ZipCity)) {
            return $zipRow->ZipCity;
        }
        return '';
    }
    
    public function getAshrae($zipRow = [])
    {
        if (isset($zipRow->ZipState) 
            && !in_array($zipRow->ZipState, ['PR', 'VI', 'AE', 'MH', 'MP', 'FM', 'PW', 'GU', 'AS', 'AP', 'AA'])) {
            $ashrae = SLZipAshrae::where('AshrState', $zipRow->ZipState)
                ->where('AshrCounty', $zipRow->ZipCounty)
                ->first();
            if ($ashrae && isset($ashrae->AshrZone)) return $ashrae->AshrZone;
        }
        return '';   
    }
    
}
