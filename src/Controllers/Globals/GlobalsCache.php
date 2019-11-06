<?php
/**
  * GlobalsCache is a mid-level class for optimizing content, 
  * mostly HTML, JS, and CSS.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.2.5
  */
namespace SurvLoop\Controllers\Globals;

use Storage;
use MatthiasMullie\Minify;
use App\Models\SLCaches;
use App\Models\SLTree;

class GlobalsCache extends GlobalsBasic
{
    public function getCache($key = '', $type = '', $treeID = 0, $coreID = 0)
    {
        $type = $this->chkCacheType($type);
        $chk = null;
        if ($treeID > 0) {
            if ($coreID > 0) {
                $chk = SLCaches::where('CachType', $type)
                    ->where('CachTreeID', $treeID)
                    ->where('CachRecID', $coreID)
                    ->where('CachKey', $key)
                    ->first();
            } else {
                $chk = SLCaches::where('CachType', $type)
                    ->where('CachTreeID', $treeID)
                    ->where('CachKey', $key)
                    ->first();
            }
        } else {
            $chk = SLCaches::where('CachType', $type)
                ->where('CachKey', $key)
                ->first();
        }
        return $chk;
    }

    public function hasCache($key = '', $type = '', $treeID = 0, $coreID = 0)
    {
        $type = $this->chkCacheType($type);
        $chk = $this->getCache($key, $type, $treeID, $coreID);
        return ($chk && isset($chk->CachID));
    }

    public function chkCacheType($type = '')
    {
        if ($type == '' && isset($this->treeRow) 
            && isset($this->treeRow->TreeType)) {
            $type = strtolower($this->treeRow->TreeType);
        }
        return $type;
    }

    public function chkCache($key = '', $type = '', $treeID = 0, $coreID = 0)
    {
        $type = $this->chkCacheType($type);
        $chk = $this->getCache($key, $type, $treeID, $coreID);
        if ($chk && isset($chk->CachValue) && trim($chk->CachValue) != '') {
            $file = $this->cachePath . '/html/' . $chk->CachValue;
            if (Storage::exists($file)) {
                return trim(Storage::get($file));
            }
        }
        return '';
    }

    public function chkCachePage($key = '', $treeID = 0, $coreID = 0)
    {
        return $this->chkCache($key, 'page', $treeID, $coreID);
    }

    public function forgetCache($key = '', $type = '', $treeID = 0, $coreID = 0)
    {
        $type = $this->chkCacheType($type);
        $cache = $this->getCache($key, $type, $treeID, $coreID);
        return $this->deleteCacheFile($cache);
    }

    public function forgetAllItemCaches($treeID = 0, $coreID = 0)
    {
        $chk = SLCaches::where('CachTreeID', $treeID)
            ->where('CachRecID', $coreID)
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $cache) {
                $this->deleteCacheFile($cache);
            }
        }
        return true;
    }

    public function deleteCacheFile($cache)
    {
        if ($cache && isset($cache->CachID)) {
            $file = $this->cachePath . '/html/' . $cache->CachValue;
            Storage::delete($file);
            $cache->delete();
            return true;
        }
        return false;
    }

    public function putCache($key = '', $content = '', $type = '', $treeID = 0, $coreID = 0)
    {
        $file = date("Ymd") . '-t' . $treeID 
            . (($coreID > 0) ? '-c' . $coreID : '');
        $treeRow = false;
        if (isset($this->treeRow->TreeType)) {
            $treeRow = $this->treeRow;
        } elseif ($treeID > 0) {
            $treeRow = SLTree::find($treeID);
        }
        if (isset($treeRow->TreeType) && $treeRow->TreeType == 'Page' 
            && $treeRow->TreeOpts%Globals::TREEOPT_NOCACHE == 0) {
            $file .= '-s' . session()->get('slSessID');
        }
        $fileDeets = '-' . str_replace('.html', '', str_replace('?', '_', 
                str_replace('&', '_', str_replace('/', '_', $key))));
        if (strlen($fileDeets) > 60) {
            $fileDeets = substr($fileDeets, 0, 60);
        }
        $file .= $fileDeets . '-r' . rand(10000000, 100000000) . '.html';
        Storage::put($this->cachePath . '/html/' . $file, $content);

        $type = $this->chkCacheType($type);
        $this->forgetCache($key, $type, $treeID, $coreID);
        $cache = new SLCaches;
        $cache->CachType   = $type;
        $cache->CachTreeID = $treeID;
        $cache->CachRecID  = $coreID;
        $cache->CachKey    = $key;
        $cache->CachValue  = $file;
        $cache->save();
        return $cache->CachID;
    }
    
    public function opnAjax()
    {
        return '<script type="text/javascript"> '
            . '$(document).ready(function(){ ';
    }
    
    public function clsAjax()
    {
        return ' }); </script>';
    }
    
    public function spinner($center = true)
    {
        $ret = ((isset($this->sysOpts["spinner-code"])) 
            ? $this->sysOpts["spinner-code"] : '<b>...</b>');
        if ($center) {
            return '<div class="w100 pT20 pB20"><center>' 
                . $ret . '</center></div>';
        }
        return $ret;
    }
    
    public function pausePageScriptCollection()
    {
        $this->x["pageSCRIPTS"] = $this->pageSCRIPTS;
        $this->x["pageJAVA"]    = $this->pageJAVA;
        $this->x["pageAJAX"]    = $this->pageAJAX;
        $this->x["pageCSS"]     = $this->pageCSS;
        return true;
    }

    public function resumePageScriptCollection()
    {
        $this->pageSCRIPTS = $this->x["pageSCRIPTS"];
        $this->pageJAVA    = $this->x["pageJAVA"];
        $this->pageAJAX    = $this->x["pageAJAX"];
        $this->pageCSS     = $this->x["pageCSS"];
        unset($this->x["pageSCRIPTS"]);
        unset($this->x["pageJAVA"]);
        unset($this->x["pageAJAX"]);
        unset($this->x["pageCSS"]);
        return true;
    }
    
    public function extractJava($str = '', $nID = -3, $destroy = false)
    {
        if (trim($str) == '') {
            return '';
        }
        $allMeat = '';
        $str = str_replace('</ script>', '</script>', $str);
        $orig = $str;
        $tag1start = strpos($str, '<script');
        $cnt = 0;
        while ($tag1start !== false && $cnt < 20) {
            $cnt++;
            $tagMeat = '';
            $tag1end = strpos($str, '>', $tag1start);
            if ($tag1end !== false 
                && substr($str, $tag1start, 21) 
                    != '<script id="noExtract') {
                $tag2 = strpos($str, '</script>', $tag1end);
                if ($tag2 !== false) {
                    $tagMeat = substr(
                        $str, 
                        ($tag1end+1), 
                        ($tag2-$tag1end-1)
                    );
                    $str = substr($str, 0, $tag1start) 
                        . substr($str, ($tag2+9));
                }
            }
            $offset = $tag1end-strlen($tagMeat);
            if (0 < $tag1end && 0 < $offset 
                && $offset < strlen($str)) {
                $tag1start = strpos($str, '<script', $offset);
            } else {
                $tag1start = false;
            }
            $allMeat .= $this->wrapScriptMeat($tagMeat, $nID);
        }
        if (!$destroy) {
            $this->pageJAVA .= $allMeat;
        }
        return $str;
    }
    
    public function extractStyle($str = '', $nID = -3, $destroy = false)
    {
        if (trim($str) == '') {
            return '';
        }
        $allMeat = '';
        $str = str_replace('</ style>', '</style>', $str);
        $orig = $str;
        $tag1start = strpos($str, '<style');
        while ($tag1start !== false) {
            $tagMeat = '';
            $tag1end = strpos($str, '>', $tag1start);
            if ($tag1end !== false 
                && substr($str, $tag1start, 20) 
                    != '<style id="noExtract') {
                $tag2 = strpos($str, '</style>', $tag1end);
                if ($tag2 !== false) {
                    $tagMeat = substr(
                        $str, 
                        ($tag1end+1), 
                        ($tag2-$tag1end-1)
                    );
                    $str = substr($str, 0, $tag1start) 
                        . substr($str, ($tag2+8));
                }
            }
            if ($tag1end > 0) {
                $tag1start = strpos(
                    $str, 
                    '<style', 
                    $tag1end-strlen($tagMeat)
                );
            }
            $allMeat .= $this->wrapScriptMeat($tagMeat, $nID);
        }
        if (!$destroy) {
            $this->pageCSS .= $allMeat;
        }
        return $str;
    }
    
    public function wrapScriptMeat($tagMeat = '', $nID = 0)
    {
        if (trim($tagMeat) != '') {
            if ($nID <= 0) {
                return $tagMeat;
            }
            return ' /* start extract from node ' . $nID . ': */ ' 
                . $tagMeat 
                . '/* end extract from node ' . $nID . ': */ ';
        }
        return '';
    }
    
    public function pullPageJsCss($content = '', $coreID = 0)
    {
        if (isset($this->x["pageCacheLoaded"]) 
            && $this->x["pageCacheLoaded"]) {
            return $content;
        }
        $minPath = '../storage/app/' . $this->cachePath;
        $fileCss = date("Ymd") . '-t' . $this->treeID;
        if ($this->treeRow->TreeType == 'Page' 
            && $this->treeRow->TreeOpts%Globals::TREEOPT_NOCACHE == 0) {
            $fileCss .= '-s' . session()->get('slSessID');
        }
        $fileCss .= '-r' . rand(10000000, 100000000) . '.css';

        $content = $this->extractStyle($content, 0);
        if (trim($this->pageCSS) != '' && trim($this->pageCSS) != '/* */') {
            Storage::put($this->cachePath . '/css/' . $fileCss, $this->pageCSS);
            $fileMin = str_replace('.css', '-min.css', $fileCss);
            $minifier = new Minify\CSS;
            $minifier->add($minPath . '/css/' . $fileCss);
            $minifier->minify($minPath . '/css/' . $fileMin);
            Storage::delete($this->cachePath . '/css/' . $fileCss);
            $this->pageSCRIPTS .= '<link id="dynCss" rel="stylesheet" '
                . 'href="/sys/dyna/' . $fileMin . '">' . "\n";
        }
        
        $fileJs = str_replace('.css', '.js', $fileCss);
        $content = $this->extractJava($content, 0);
        $java = $this->pageJAVA . $this->getXtraJs();
        if (trim($this->pageAJAX) != '' && trim($this->pageAJAX) != '/* */') {
            $java .= ' $(document).ready(function(){ ' . $this->pageAJAX . ' }); ';
        }
        if (trim($java) != '' && trim($java) != '/* */') {
            Storage::put($this->cachePath . '/js/' . $fileJs, $java);
            $fileMin = str_replace('.js', '-min.js', $fileJs);
            $minifier = new Minify\JS;
            $minifier->add($minPath . '/js/' . $fileJs);
            $minifier->minify($minPath . '/js/' . $fileMin);
            Storage::delete($this->cachePath . '/js/' . $fileJs);
            $this->pageSCRIPTS .= "\n" . '<script id="dynJs" type="text/javascript" '
                . 'src="/sys/dyna/' . $fileMin . '"></script>' . "\n";
        }

        $this->pageCSS = $this->pageJAVA = $this->pageAJAX = '';
        return $content;
    }

    public function getCachePageJs($filename = '')
    {
        if (!Storage::has($this->cachePath . '/js/' . $filename)) {
            return '<!-- not found ' . $this->cachePath . '/js/' . $filename 
                . ' -->';
        }
        return trim(Storage::get($this->cachePath . '/js/' . $filename));
    }

    public function getCachePageCss($filename = '')
    {
        return trim(Storage::get($this->cachePath . '/css/' . $filename));
    }

    public function clearOldDynascript($minAge = 0)
    {
        $safeDates = $this->getPastDateArray($minAge);
        foreach (['html', 'css', 'js'] as $fold) {
            $folder = $this->cachePath . '/' . $fold;
            if (!is_dir('../storage/app/' . $folder)) {
                mkdir('../storage/app/' . $folder);
            }
            $cnt = 0;
            $files = $this->mapDirFilesSlim('../storage/app/' . $folder, false);
            if (sizeof($files) > 0) {
                foreach ($files as $i => $file) {
                    if ($cnt < 5000) {
                        $delete = true;
                        $filenameParts = $this->mexplode('-', $file);
                        if (isset($filenameParts[0]) 
                            && in_array($filenameParts[0], $safeDates)) {
                            $delete = false;
                        }
                        if ($delete) {
                            $cnt++;
                            Storage::delete($folder . '/' . $file);
                            //unlink($folder . '/' . $file);
                        }
                    }
                }
            }
        }
        $chk = SLCaches::where('created_at', '<', date('Y-m-d H:i:s', $this->getPastDateTime()))
            ->delete();
        return true;
    }

    public function getCacheSffxAdds()
    {
        $sffx = '';
        if ($this->isOwner) {
            $sffx .= '-owner';
        }
        if (isset($this->coreID) && intVal($this->coreID) > 0) {
            $sffx .= '-c_' . $this->coreID;
        }
        if (isset($this->pageView) && $this->pageView != '') {
            $sffx .= '-v_' . $this->pageView;
        }
        if (isset($this->dataPerms) && $this->dataPerms != '') {
            $sffx .= '-p_' . $this->dataPerms;
        }
        return $sffx;
    }
    
    public function deferStaticNodePrint($nID, $content = '', $coreID = 0, $js = '', $ajax = '', $css = '')
    {
        if (!isset($this->x["deferCnt"])) {
            $this->x["deferCnt"] = 0;
        }
        if ($coreID < 0) {
            $coreID = 0;
        }
        $this->x["deferCnt"]++;
        $rand = rand(100000000, 1000000000);
        $file = $this->cachePath . '/html/' . date("Ymd") 
            . '-t' . $this->treeID . '-c' . $coreID . '-n' . $nID 
            . '-r' . $rand . '.html';
        if (trim($js) != '' || trim($ajax) != '') {
            $content .= '<script type="text/javascript"> ' . $js . ' ';
            if (trim($ajax) != '') {
                $content .= '$(document).ready(function(){ ' . $ajax . ' }); ';
            }
            $content .= '</script>';
        }
        if (trim($css) != '') {
            $content .= '<style> ' . $css . ' </style>';
        }
        Storage::put($file, $content);
        $loadUrl = '/defer/' . $this->treeID . '/' . $coreID 
            . '/' . $nID . '/' . date("Ymd") . '/' . $rand;
        $params = $this->getAnyReqParams();
        if ($params != '') {
            $loadUrl .= '?' . substr($params, 1);
        }
        $this->pageAJAX .= 'setTimeout(function() { '
            . '$("#deferNode' . $nID . '").load("' . $loadUrl . '"); '
            . '}, ' . (500+(500*$this->x["deferCnt"])) . '); ';
        return '<div id="deferNode' . $nID . '" class="w100 ovrSho">'
            . '<center><div id="deferAnim' . $nID . '" class="p20 m20">'
            . $this->spinner() . '</div></center>'
            . '</div>';
    }

}