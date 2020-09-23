<?php
/**
  * Geographs is a class focused on providing geographically-based data interactions
  * from dropdown lists of U.S. and Canadian states, to embedding maps.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.7
  */
namespace RockHopSoft\Survloop\Controllers\Globals;

use App\Models\SLAddyGeo;

class Geographs extends GeographyLookups
{   
    protected $mapMarkTyp = [];
    protected $mapMarkers = [];
    protected $kmlMarkTyp = '';
    protected $kmlMarkers = '';
    
    protected $custCenter = false;
    protected $mapCenter  = [
        38.5, // lat
        -97,  // lng
        4     // zoom
    ];
    
    protected $kmlPath    = '../storage/app/gen-kml';
    
    public function getLatLng($addy = '')
    {
        if (trim($addy) == '') {
            return [ 0, 0 ];
        }
        $chk = SLAddyGeo::where('ady_geo_address', $addy)
            ->first();
        if (!$chk || !isset($chk->AdyGeoLat) || !isset($chk->AdyGeoLong)) {
            // || $GLOBALS["SL"]->REQ->has('refresh')
            if (isset($GLOBALS["SL"]->sysOpts["google-cod-key"]) 
                && !$GLOBALS["SL"]->isHomestead()) {
                $jsonFile = 'https://maps.googleapis.com/maps/api/geocode/json?address=' 
                    . urlencode($addy) . '&key=' . $GLOBALS["SL"]->sysOpts["google-cod-key"];
                    // '&sensor=true'
                $json = json_decode(file_get_contents($jsonFile),TRUE);
                if (sizeof($json) > 0 
                    && isset($json["results"]) 
                    && sizeof($json["results"]) > 0 
                    && isset($json["results"][0]["geometry"]) 
                    && isset($json["results"][0]["geometry"]["location"])) {
                    $chk = SLAddyGeo::where('ady_geo_address', $addy)
                        ->delete();
                    $chk = new SLAddyGeo;
                    $chk->ady_geo_address = $addy;
                    $chk->ady_geo_lat     = $json["results"][0]["geometry"]["location"]["lat"];
                    $chk->ady_geo_long    = $json["results"][0]["geometry"]["location"]["lng"];
                    $chk->save();
                }
            }
        }
        if ($chk && isset($chk->ady_geo_lat) && isset($chk->ady_geo_long)) {
            return [
                $chk->ady_geo_lat, 
                $chk->ady_geo_long 
            ];
        }
        return [ 0, 0 ];
    }
    
    public function embedMapSimpAddy($nID = 0, $addy = '', $label = '', $height = 450, $maptype = 'satellite')
    {
        if ($GLOBALS["SL"]->isHomestead()) {
            return '<div style="height: 420px; padding-top: 200px;">(Map)</div>';
        }
        list($lat, $lng) = $this->getLatLng($addy);
        return view(
            'vendor.survloop.reports.embed-google-map-simple', 
            [
                "nID"     => $nID,
                "addy"    => $addy,
                "lat"     => $lat,
                "lng"     => $lng,
                "height"  => $height,
                "maptype" => $maptype
            ]
        )->render();
    }
    
    public function embedMap($nID = 0, $filename = '', $docName = '', $docDesc = '')
    {
        if (sizeof($this->mapMarkers) > 0) {
            $this->kmlMarkersFull($filename);
            if ($GLOBALS["SL"]->isHomestead()) {
                return '<div style="height: 420px; padding-top: 200px;">(Map)</div>';
            }
            $descAjax = false;
            if (sizeof($this->mapMarkers) > 0) {
                foreach ($this->mapMarkers as $i => $mark) {
                    if (!$descAjax && trim($mark[5]) != '') {
                        $descAjax = true;
                    }
                }
            }
            $GLOBALS["SL"]->pageJAVA .= view(
                'vendor.survloop.reports.embed-google-map-js', 
                [
                    "nID"       => $nID,
                    "filename"  => $filename,
                    "descAjax"  => $descAjax,
                    "mapCenter" => $this->mapCenter
                ]
            )->render();
            return view(
                'vendor.survloop.reports.embed-google-map', 
                [
                    "nID"     => $nID,
                    "docDesc" => $docDesc
                ]
            )->render();
        }
        return '';
    }
    
    public function addMarkerType($markerName = '', $markerImg = '')
    {
        if (trim($markerName) != '') {
            $this->mapMarkTyp[$markerName] = $markerImg;
        }
        return true;
    }
    
    public function addMapMarker($lat, $lng, $marker = '', $title = '', $desc = '', $ajaxLoad = '')
    {
        $this->mapMarkers[] = [
            $lat, 
            $lng, 
            $marker, 
            $title, 
            $desc, 
            $ajaxLoad
        ];
        return true;
    }
    
    protected function kmlMarkerStyles()
    {
        $this->kmlMarkTyp = '';
        if (sizeof($this->mapMarkTyp) > 0) {
            foreach ($this->mapMarkTyp as $type => $img) {
                $this->kmlMarkTyp .= '<Style id="' . $type . '"><IconStyle><Icon><href>' 
                    . $img . '</href></Icon></IconStyle></Style>' . "\n\t";
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
                        <name>' . $GLOBALS["SL"]->makeXMLSafe($mark[3]) 
                        . '</name><description>' 
                        . ((trim($mark[5]) != '') ? $mark[5] : '<![CDATA[' . $mark[4] . ']]>')
                        . '</description>' 
                        . (($mark[2] != '') ? '<styleUrl>#' . $mark[2] . '</styleUrl>'."\n" : '') 
                        . '<Point><coordinates>' . $mark[1] . ',' . $mark[0] 
                        . ',0</coordinates></Point></Placemark>' . "\n";
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
                . '<Document>' . "\n\t" . '<name>' 
                . $GLOBALS["SL"]->makeXMLSafe($docName) . '</name>' . "\n\t"
                . '<description>' . $GLOBALS["SL"]->makeXMLSafe($docDesc) 
                . '</description>' . "\n\t"
                . $this->kmlMarkerStyles() . "\n\t" . $this->kmlMapMarkers() 
                . '</Document>' . "\n" . '</kml>';
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
