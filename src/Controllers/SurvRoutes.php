<?php
/**
  * SurvRoutes is a side-class to handle the smallest routing functions which largely just redirect.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.1.9
  */
namespace SurvLoop\Controllers;

use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller;
use SurvLoop\Controllers\Globals\GlobalsCache;

class SurvRoutes extends Controller
{
    public function testHome(Request $request)
    {
        return redirect('/?test=1');
    }
    
    public function getSysExpire($size = '.min')
    {
        if ($size == '') {
            return 1;
        }
        return (60*60*24);
    }
    
    public function getContentType($type = 'js')
    {
        if ($type == 'css') {
            return 'text/css';
        }
        return 'application/javascript';
    }
    
    public function loadSysFile($type = 'js', $which = 1, $size = '.min')
    {
        $expires = $this->getSysExpire($size);
        $metaType = $this->getContentType($type);
        $file = '../storage/app/sys/sys' . $which . $size . '.' . $type;
        $response = Response::make(file_get_contents($file));
        $response->header('Content-Type', $metaType);
        $response->header('Cache-Control', 'public, max-age="' . $expires . '"');
        $response->header('Expires', gmdate('r', time()+$expires));
        return $response;
    }
    
    public function getSysFile(Request $request, $which = 1, $type = 'js')
    {
        return $this->loadSysFile($type, $which, '');
    }
    
    public function getSysFileMin(Request $request, $which = 1, $type = 'js')
    {
        return $this->loadSysFile($type, $which);
    }
    
    public function getSysTreeJs(Request $request, $treeID = 1)
    {
        $expires = $this->getSysExpire(); // expires daily
        $file = '../storage/app/sys/tree-' . $treeID . '.js';
        $response = Response::make(file_get_contents($file));
        $response->header('Content-Type', 'application/javascript');
        $response->header('Cache-Control', 'public, max-age="' . $expires . '"');
        $response->header('Expires', gmdate('r', time()+$expires));
        return $response;
    }
    
    public function getDynaFile(Request $request, $file = '', $type = '')
    {
        $cache = new GlobalsCache;
        $filename = $file . '.' . $type;
        //$expires = $this->getSysExpire(''); // expires immediately
        $response = null;
        if (strpos($file, '-s') === false || (session()->has('slSessID') 
            && strpos($file, '-s' . session()->get('slSessID') . '-') !== false)) {
            $ret = '';
            if ($type == 'js') {
                $ret = $cache->getCachePageJs($filename);
            } elseif ($type == 'css') {
                $ret = $cache->getCachePageCss($filename);
            }
            if (trim($ret) != '') {
                $response = Response::make($ret);
            }
        }
        if ($response === null) {
            $response = Response::make('/* */');
        }
        $memeType = (($type == 'css') ? 'text/css' : 'application/javascript');
        $response->header('Content-Type', $memeType);
        //$response->header('Cache-Control', 'public, max-age="' . $expires . '"');
        //$response->header('Expires', gmdate('r', time()+$expires));
        return $response;
    }
    
    public function getKml(Request $request, $kmlfile = '')
    {
        $expires = $this->getSysExpire(); // expires daily
        if (file_exists('../storage/app/gen-kml/' . $kmlfile . '.kml')) {
            $file = '../storage/app/gen-kml/' . $kmlfile . '.kml';
            $response = Response::make(file_get_contents($file));
            $response->header('Content-Type', 'text/xml');
            $response->header('Cache-Control', 'public, max-age="' . $expires . '"');
            $response->header('Expires', gmdate('r', time()+$expires));
            return $response;
        }
        return '';
    }
    
    public function getLibFile($loc = '', $type = 'js')
    {
        $metaType = $this->getContentType($type);
        $file = '../vendor/' . $loc . '.' . $type;
        $response = Response::make(file_get_contents($file));
        $response->header('Content-Type', $metaType);
        return $response;
    }
    
    public function getJquery(Request $request)
    {
        return $this->getLibFile('components/jquery/jquery.min');
    }
    
    public function getJqueryUi(Request $request, $type = 'js')
    {
        $path = 'components/jqueryui/';
        if ($type == 'js') {
            return $this->getLibFile($path . 'jquery-ui.min', $type);
        }
        return $this->getLibFile($path . 'themes/base/jquery-ui.min', 'css');
    }
    
    public function catchJqueryUiMappingError(Request $request, $file = '')
    {
        return redirect('/survloop/jquery-ui-1.12.1/images/' . $file);
    }
    
    public function getBootstrap(Request $request, $type = 'js')
    {
        return $this->getLibFile('twbs/bootstrap/dist/' . $type . '/bootstrap.min', $type);
    }
    
    public function getFontAwesome(Request $request)
    {
        return $this->getLibFile('forkawesome/fork-awesome/css/fork-awesome.min', 'css');
    }
    
    public function getFont(Request $request, $file = '')
    {
        $filename = '../vendor/forkawesome/fork-awesome/fonts/' . $file;
        $response = Response::make(file_get_contents($filename));
        //$response->header('Content-Type', 'text/css');
        return $response;
    }
    
    public function getSummernoteJs(Request $request)
    {
        return $this->getLibFile('summernote/summernote/dist/summernote.min', 'js');
    }
    
    public function getSummernoteCss(Request $request)
    {
        return $this->getLibFile('summernote/summernote/dist/summernote', 'css');
    }
    
    public function getSummernoteEot(Request $request)
    {
        $response = Response::make(file_get_contents('../vendor/summernote/summernote/dist/font/summernote.eot'));
        $response->header('Content-Type', 'application/vnd.ms-fontobject');
        return $response;
    }
    
    public function getChartJs(Request $request)
    {
        return $this->getLibFile('nnnick/chartjs/dist/Chart.bundle.min', 'js');
    }
    
    public function getPlotlyJs(Request $request)
    {
        return $this->getLibFile('plotly/plotly.js/dist/plotly.min', 'js');
    }
    
}