<?php
/**
  * Geographs is a class focused on providing geographically-based data interactions
  * from dropdown lists of U.S. and Canadian states, to embedding maps.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers\Globals;

use App\Models\SLZips;
use App\Models\SLZipAshrae;
use App\Models\SLAddyGeo;
use App\Models\SLNodeResponses;

class Geographs
{
    public $stateList   = [];
    public $stateListCa = [];
    public $countryList = [];
    public $hasCanada   = false;
    
    public function __construct($hasCanada = false)
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
            foreach ($this->stateListCa as $abbr => $name) {
                if (strtolower($name) == strtolower($state)) return $abbr;
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
    
    public function getStateByInd($ind = -1)
    {
        $cnt = 1;
        $this->loadStates();
        foreach ($this->stateList as $abbr => $name) {
            if ($ind == $cnt) return $abbr;
            $cnt++;
        }
        if ($this->hasCanada) {
            foreach ($this->stateListCa as $abbr => $name) {
                if ($ind == $cnt) return $abbr;
                $cnt++;
            }
        }
        return '';
    }
    
    public function loadStateResponseVals()
    {
        $this->loadStates();
        $retArr = [];
        foreach ($this->stateList as $abbr => $name) $retArr[] = $abbr;
        if ($this->hasCanada) {
            foreach ($this->stateListCa as $abbr => $name) $retArr[] = $abbr;
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
        if (trim($zip) == '') return null;
        if (strlen($zip) > 7) $zip = substr($zip, 0, 5);
        return SLZips::where('ZipZip', $zip)
            ->first();
    }
    
    public function getZipProperty($zip = '', $fld = 'City')
    {
        $zipRow = $this->getZipRow($zip);
        if ($zipRow && isset($zipRow->{ 'Zip' . $fld })) return $zipRow->{ 'Zip' . $fld };
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
        return view('vendor.survloop.forms.inc-drop-opts-states', [
            "state"       => trim($state),
            "stateList"   => $this->stateList,
            "stateListCa" => $this->stateListCa,
            "hasCanada"   => $this->hasCanada,
            "all"         => $all
            ])->render();
    }
    
    public function stateResponses($all = false)
    {
        $this->loadStates();
        $responses = [];
        $cnt = 0;
        foreach ($this->stateList as $abbr => $name) {
            $responses[$cnt] = new SLNodeResponses;
            $responses[$cnt]->NodeResValue = $abbr;
            $responses[$cnt]->NodeResEng = $name . ' (' . $abbr . ')';
            $cnt++;
        }
        if ($this->hasCanada) {
            foreach ($this->stateListCa as $abbr => $name) {
                $responses[$cnt] = new SLNodeResponses;
                $responses[$cnt]->NodeResValue = $abbr;
                $responses[$cnt]->NodeResEng = $name . ' (' . $abbr . ')';
                $cnt++;
            }
        }
        return $responses;
    }
    
    public function climateZoneDrop($fltClimate = '')
    {
        return view('vendor.survloop.forms.inc-drop-opts-ashrae', [
            "fltClimate" => $fltClimate,
            "hasCanada"  => $this->hasCanada
            ])->render();
    }
    
    public function countryDrop($cntry = '')
    {
        $this->loadCountries();
        return view('vendor.survloop.forms.inc-drop-opts-countries', [
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
    
    
    public function getLatLng($addy = '')
    {
        if (trim($addy) == '') return [0, 0];
        $chk = SLAddyGeo::where('AdyGeoAddress', $addy)
            ->first();
        if (!$chk || !isset($chk->AdyGeoLat) || !isset($chk->AdyGeoLong) || $GLOBALS["SL"]->REQ->has('refresh')) {
            if (isset($GLOBALS["SL"]->sysOpts["google-cod-key"]) && !$GLOBALS["SL"]->isHomestead()) {
                $jsonFile = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($addy) . '&key=' 
                    . $GLOBALS["SL"]->sysOpts["google-cod-key"]; // '&sensor=true'
                $json = json_decode(file_get_contents($jsonFile),TRUE);
                if (sizeof($json) > 0 && isset($json["results"]) && sizeof($json["results"]) > 0 
                    && isset($json["results"][0]["geometry"]) && isset($json["results"][0]["geometry"]["location"])) {
                    $chk = new SLAddyGeo;
                    $chk->AdyGeoAddress = $addy;
                    $chk->AdyGeoLat     = $json["results"][0]["geometry"]["location"]["lat"];
                    $chk->AdyGeoLong    = $json["results"][0]["geometry"]["location"]["lng"];
                    $chk->save();
                }
            }
        }
        if ($chk && isset($chk->AdyGeoLat) && isset($chk->AdyGeoLong)) {
            return [$chk->AdyGeoLat, $chk->AdyGeoLong];
        }
        return [0, 0];
    }
    
    public function embedMapSimpAddy($nID = 0, $addy = '', $label = '', $height = 450, $maptype = 'satellite')
    {
        if ($GLOBALS["SL"]->isHomestead()) {
            return '(Map)';
        }
        list($lat, $lng) = $this->getLatLng($addy);
        return view('vendor.survloop.reports.embed-google-map-simple', [
            "nID"     => $nID,
            "addy"    => $addy,
            "lat"     => $lat,
            "lng"     => $lng,
            "height"  => $height,
            "maptype" => $maptype
            ])->render();
    }
    
    protected $mapMarkTyp = [];
    protected $mapMarkers = [];
    protected $kmlMarkTyp = '';
    protected $kmlMarkers = '';
    
    protected $custCenter = false;
    protected $mapCenter  = [ 38.5, -97, 4 ]; // initial loading (lat, lng, zoom)
    
    protected $kmlPath    = '../storage/app/gen-kml';
    
    public function embedMap($nID = 0, $filename = '', $docName = '', $docDesc = '')
    {
        if ($GLOBALS["SL"]->isHomestead()) {
            return '(Map)';
        }
        if (sizeof($this->mapMarkers) > 0) {
            $this->kmlMarkersFull($filename);
            $descAjax = false;
            if (sizeof($this->mapMarkers) > 0) {
                foreach ($this->mapMarkers as $i => $mark) {
                    if (!$descAjax && trim($mark[5]) != '') $descAjax = true;
                }
            }
            $GLOBALS["SL"]->pageJAVA .= view('vendor.survloop.reports.embed-google-map-js', [
                "nID"       => $nID,
                "filename"  => $filename,
                "descAjax"  => $descAjax,
                "mapCenter" => $this->mapCenter
                ])->render();
            return view('vendor.survloop.reports.embed-google-map', [
                "nID"       => $nID,
                "docDesc"   => $docDesc
                ])->render();
        }
        return '';
    }
    
    public function addMarkerType($markerName = '', $markerImg = '')
    {
        if (trim($markerName) != '') $this->mapMarkTyp[$markerName] = $markerImg;
        return true;
    }
    
    public function addMapMarker($lat, $lng, $marker = '', $title = '', $desc = '', $ajaxLoad = '')
    {
        $this->mapMarkers[] = [$lat, $lng, $marker, $title, $desc, $ajaxLoad];
        return true;
    }
    
    protected function kmlMarkerStyles()
    {
        $this->kmlMarkTyp = '';
        if (sizeof($this->mapMarkTyp) > 0) {
            foreach ($this->mapMarkTyp as $type => $img) {
                $this->kmlMarkTyp .= '<Style id="' . $type . '"><IconStyle><Icon><href>' . $img 
                    . '</href></Icon></IconStyle></Style>' . "\n\t";
            }
        }
        return $this->kmlMarkTyp;
    }
    
    protected function kmlMapMarkers()
    {
        $this->kmlMarkers = '';
        if (sizeof($this->mapMarkers) > 0) {
            foreach ($this->mapMarkers as $i => $mark) {
                if ($mark[0] != 0 && $mark[1] != 0) {
                    $this->kmlMarkers .= "\t" . '<Placemark>
                        <name>' . $GLOBALS["SL"]->makeXMLSafe($mark[3]) . '</name>
                        <description>' . ((trim($mark[5]) != '') ? $mark[5] : '<![CDATA[' . $mark[4] . ']]>')
                        . '</description>' . (($mark[2] != '') ? '<styleUrl>#' . $mark[2] . '</styleUrl>'."\n" : '') . '
                        <Point><coordinates>' . $mark[1] . ',' . $mark[0] . ',0</coordinates></Point>
                    </Placemark>' . "\n";
                }
            }
        }
        return $this->kmlMarkers;
    }
    
    public function kmlMarkersFull($filename, $docName = '', $docDesc = '')
    {
        if (!is_dir($this->kmlPath)) {
            mkdir($this->kmlPath);
        }
        $fullpath = $this->kmlPath . '/' . $filename . '.kml';
        if (!file_exists($fullpath) || $GLOBALS["SL"]->REQ->has('refresh')) {
            $finalKML = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' . "\n"
                . '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n\t"
              //. '<refreshMode>onChange</refreshMode>' . "\n\t"
                . '<Document>' . "\n\t" . '<name>' . $GLOBALS["SL"]->makeXMLSafe($docName) . '</name>' . "\n\t"
                . '<description>' . $GLOBALS["SL"]->makeXMLSafe($docDesc) . '</description>' . "\n\t"
                . $this->kmlMarkerStyles() . "\n\t" . $this->kmlMapMarkers() . '</Document>' . "\n" . '</kml>';
//echo 'kmlMarkersFull(' . $filename . '<br /><pre>' . $finalKML . '</pre>'; exit;
            if (file_exists($fullpath)) {
                unlink($fullpath); 
            }
            file_put_contents($fullpath, $finalKML);
            $this->kml2kmz($filename);
        }
        return true;
    }
    
    public function kmlDrawCircle($centerLat, $centerLng, $radius, $dotCnt = 36)
    {
        $retKML = '';
        $latScale = 0.8-(0.25*(($centerLat-37)/(62-37)));
        for ($deg=0; $deg<360; $deg+=(360/$dotCnt)) {
            $lng = ($radius*cos(deg2rad($deg)))+$centerLng;
            $lat = ($latScale*$radius*sin(deg2rad($deg)))+$centerLat;
            $retKML .= "\t\t\t\t\t" . $lng . ',' . $lat . ',0' . "\n";
        }
        return $retKML;
    }
    
    public function kml2kmz($filename = '')
    {
        $filename = $this->kmlPath . '/' . $filename . '.kml';
        if (!file_exists($filename)) {
            return false;
        }
        $zipname = str_replace('.kml', '.kmz', $filename);
        if (file_exists($zipname)) {
            unlink($zipname);
        }
        
        /*
        $zip = new ZipArchive();
        $zip->open($zipname, ZIPARCHIVE::CREATE);
        $zip->addFile($filename);
        $zip->close();
        */
        return true;
    }
    
    
}
