<?php
namespace SurvLoop\Controllers;

use Symfony\Component\HttpFoundation\File\File;

/* Largely Utilities */
// just wanted this utility global to easily call from anywhere including views
class SurvLoopStatic
{
    
    public function mexplode($delim, $str)
    {
        $ret = [];
        if (trim(str_replace($delim, '', $str)) != '') {
            if (strpos($str, $delim) === false) {
                $ret[] = $str;
            } else {
                if (substr($str, 0, 1) == $delim) $str = substr($str, 1);
                if (substr($str, strlen($str)-1) == $delim) $str = substr($str, 0, strlen($str)-1);
                $ret = explode($delim, $str);
            }
        }
        return $ret;
    }
    
    public function swapURLwrap($url, $printHttp = true)
    {
        $urlPrint = str_replace('mailto:', '', $url);
        if (!$printHttp) $urlPrint = str_replace('http://', '', str_replace('https://', '', $urlPrint));
        return '<a href="' . $url . '" target="_blank">' . $urlPrint . '</a>'; 
    }
    
    public function sortArrByKey($arr, $key, $ord = 'asc')
    {
        if (sizeof($arr) < 2) return $arr;
        $arrCopy = $arrOrig = $arr;
        $arr = [];
        for ($i = 0; $i < sizeof($arrOrig); $i++) {
            if (sizeof($arrCopy) == 1) {
                $arr[] = $arrCopy[0];
            } else {
                $nextInd = -1;
                for ($j = 0; $j < sizeof($arrCopy); $j++) {
                    if ($nextInd < 0) {
                        $nextInd = $j;
                    } elseif ($ord == 'asc') {
                        if ($arrCopy[$j][$key] < $arrCopy[$nextInd][$key]) $nextInd = $j;
                    } else {
                        if ($arrCopy[$j][$key] > $arrCopy[$nextInd][$key]) $nextInd = $j;
                    }
                }
                $arr[] = $arrCopy[$nextInd];
                array_splice($arrCopy, $nextInd, 1);
            }
        }
        return $arr;
    }
    
    public function mapsURL($addy)
    {
        return 'https://www.google.com/maps/search/' . urlencode($addy) . '/';
    }
    
    public function getYoutubeID($url)
    {
        $ret = '';
        $pos = strpos($url, 'v=');
        if ($pos > 0) {
            $ret = substr($url, (2+$pos));
            $pos = strpos($ret, '&');
            if ($pos > 0) $ret = substr($ret, 0, $pos);
        }
        return $ret;
    }
    
    public function getFileExt($file)
    {
        $ext = '';
        if (trim($file) != '') {
            $tmpExt = explode(".", $file);
            $ext = strtolower($tmpExt[(sizeof($tmpExt)-1)]);
        }
        return $ext;
    }
    
    public function searchDeeperDirs($file)
    {
        $newFile = $file;
        $limit = 0;
        while (!file_exists($newFile) && $limit < 9) {
            $newFile = '../' . $newFile;
            $limit++;
        }
        if (file_exists($newFile)) return $newFile;
        return $file;
    }
    
    public function convertRel2AbsURL($url)
    {
        $u = str_replace('../vendor/', '', trim($url));
        $dashPos = strpos($u, '/');
        if ($dashPos > 0) {
            $u = substr($u, $dashPos+1);
            $dashPos = strpos($u, '/');
            if ($dashPos > 0) {
                $abbr = substr($u, 0, $dashPos);
                $u = substr($u, $dashPos+1);
                $dashPos = strpos($u, 'src/Public/');
                if ($dashPos === 0) {
                    $u = str_replace('src/Public/', '', $u);
                    return $this->sysOpts['app-url'] . '/' . $abbr . '/' . $u;
                }
            }
        }
        return '';
    }
    
    public function copyDirFiles($from, $to, $recurse = true)
    {
        $ret = '';
        $dir = opendir($from);
        if (!file_exists($to) || !is_dir($to)) @mkdir($to);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($from . '/' . $file)) {
                    if ($recurse) copyDirFiles($from . '/' . $file, $to . '/' . $file);
                } else {
                    if (copy($from . '/' . $file, $to . '/' . $file)) {
                        $ret .= 'copied ' . $from . '/' . $file . ' to ' . $to . '/' . $file . '<br />' . "\n";
                    } else {
                        $ret .= 'didn\'t copy ' . $from . '/' . $file . ' to ' . $to . '/' . $file . '<br />' . "\n";
                    }
                }
            }
        }
        closedir($dir);
        return $ret;
    }
    
    public function findDirFile($folder, $file)
    {
        return $this->findDirFileInner($folder, $file);
    }
    
    public function findDirFileInner($folder, $file, $subFold = [])
    {
        if (!file_exists($folder) || !is_dir($folder)) return [];
        $dir = opendir($folder);
        while (false !== ($f = readdir($dir))) {
            if ($f != '.' && $f != '..') {
                if (is_dir($folder . '/' . $f)) {
                    $tmp = $subFold;
                    $tmp[] = $f;
                    $tmp = $this->findDirFileInner($folder . '/' . $f, $file, $tmp);
                    if (sizeof($tmp) > 0) return $tmp;
                } else {
                    if ($f == $file) return $subFold;
                }
            }
        }
        closedir($dir);
        return [];
    }
    
    public function mapDirFiles($folder, $recurse = true)
    {
        $ret = [];
        $dir = opendir($folder);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($folder . '/' . $file)) {
                    if ($recurse) $ret[] = $this->mapDirFiles($folder . '/' . $file, true);
                } else {
                    $ret[] = $folder . '/' . $file;
                }
            }
        }
        closedir($dir);
        return $ret;
    }
    
    public function mapDirSlimmer($map, $folder)
    {
        if ($map && sizeof($map) > 0 && trim($folder) != '') {
            foreach ($map as $i => $file) {
                if (is_array($file)) $map[$i] = $this->mapDirSlimmer($map[$i], $folder);
                else $map[$i] = str_replace($folder . '/', '', $map[$i]);
            }
        }
        return $map;
    }
    public function mapDirFilesSlim($folder, $recurse = true)
    {
        return $this->mapDirSlimmer($this->mapDirFiles($folder, $recurse), $folder);
    }
    
    public function sigFigs($value, $sigFigs = 2)
    {
        $exponent = floor(log10(abs($value))+1);
        if (pow(10, $exponent) == 0 || pow(10, $sigFigs) == 0) return $value;
        $significand = round(($value / pow(10, $exponent)) * pow(10, $sigFigs)) / pow(10, $sigFigs);
        return $significand * pow(10, $exponent);
    }
    
    public function colorHex2Rgba($hex = '#000000', $a = 1)
    {
        $hex = str_replace("#", "", $hex);
        $rgba = [ "r" => 0, "g" => 0, "b" => 0, "a" => $a ];
        if(strlen($hex) == 3) {
            $rgba["r"] = hexdec(substr($hex,0,1).substr($hex,0,1));
            $rgba["g"] = hexdec(substr($hex,1,1).substr($hex,1,1));
            $rgba["b"] = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $rgba["r"] = hexdec(substr($hex,0,2));
            $rgba["g"] = hexdec(substr($hex,2,2));
            $rgba["b"] = hexdec(substr($hex,4,2));
        }
        return $rgba;
    }
    
    public function colorRgba2Hex($rgba = [])
    {
        return '#' . dechex($rgba["r"]) . dechex($rgba["g"]) . dechex($rgba["b"]);
    }

    public function printRgba($rgba = [])
    {
        if (!isset($rgba["r"])) return '';
        if (!isset($rgba["a"]) || $rgba["a"] == 1) {
            return 'rgb(' . $rgba["r"] . ', ' . $rgba["g"] . ', ' . $rgba["b"] . ')';
        }
        return 'rgba(' . $rgba["r"] . ', ' . $rgba["g"] . ', ' . $rgba["b"] . ', ' . number_format($rgba["a"], 2) . ')';
    }
    
    public function colorFade($perc = 0, $hex1 = '#ffffff', $hex2 = '#000000', $a1 = 1, $a2 = 1)
    {
        $c1 = $this->colorHex2Rgba($hex1, $a1);
        $c2 = $this->colorHex2Rgba($hex2, $a2);
        //echo 'colorFade( perc: ' . $perc . ', c1: '; print_r($c1); echo ', c2: '; print_r($c2); echo '<br />';
        if ($perc == 1)     return $c2;
        elseif ($perc == 0) return $c1;
        $cNew = [
            "r" => (($c1["r"] == $c2["r"]) ? $c1["r"] : intVal(($c1["r"]+(($c2["r"]-$c1["r"])*$perc)))),
            "g" => (($c1["g"] == $c2["g"]) ? $c1["g"] : intVal(($c1["g"]+(($c2["g"]-$c1["g"])*$perc)))),
            "b" => (($c1["b"] == $c2["b"]) ? $c1["b"] : intVal(($c1["b"]+(($c2["b"]-$c1["b"])*$perc)))),
            "a" => (($c1["a"] == $c2["a"]) ? $c1["a"] : number_format(($c1["a"]+(($c2["a"]-$c1["a"])*$perc)), 2))
            ];
        //echo 'cNew:<pre>'; print_r($cNew); echo '</pre>';
        return $cNew;
    }
    
    public function printColorFade($perc = 0, $hex1 = '#ffffff', $hex2 = '#000000', $a1 = 1, $a2 = 1)
    {
        return $this->printRgba($this->colorFade($perc, $hex1, $hex2, $a1, $a2));
    }
    
    public function printHex2Rgba($hex = '#000000', $a = 1)
    {
        return $this->printRgba($this->colorHex2Rgba($hex, $a));
    }
    
    public function urlPreview($url)
    {
        $url = urlClean($url);
        if (strpos($url, '/') !== false) $url = substr($url, 0, strpos($url, '/'));
        return $url;
    }
    
    public function urlClean($url)
    {
        $url = str_replace('http://', '', str_replace('https://', '', 
            str_replace('http://www.', '', str_replace('https://www.', '', $url))));
        $pos = strrpos($url, '/');
        if ($pos !== false && $pos == strlen($url)-1) $url = substr($url, 0, $pos);
        return $url;
    }
    
    public function stdizeChars($txt)
    {
        return str_replace('“', '"', str_replace('”', '"', str_replace("’", "'", $txt)));
    }

    public function humanFilesize($bytes, $decimals = 2) 
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }
    
    // takes in and returns rows of [ Record ID, Ranked Value, Rank Order, Percentile ]
    public function calcPercentiles($arr = [])
    {
        $bak = $arr;
        $sorted = [];
        for ($i=0; $i<sizeof($bak); $i++) {
            $max = [ 0, -100000000 ];
            for ($j=0; $j<sizeof($bak); $j++) {
                if (isset($arr[$j]) && $max[1] < $arr[$j][1]) $max = [ $j, $arr[$j][1] ];
            }
            $sorted[] = [ $arr[$max[0]][0], $arr[$max[0]][1], sizeof($sorted), -1 ];
            unset($arr[$max[0]]);
        }
        for ($i=0; $i<sizeof($sorted); $i++) {
            $sorted[$i][3] = (100*(sizeof($sorted)-$sorted[$i][2])/sizeof($sorted));
        }
        return $sorted;
    }
    
    public function num2Month3($num = 0)
    {
        switch (intVal($num)) {
            case 1:  return 'Jan'; break;
            case 2:  return 'Feb'; break;
            case 3:  return 'Mar'; break;
            case 4:  return 'Apr'; break;
            case 5:  return 'May'; break;
            case 6:  return 'Jun'; break;
            case 7:  return 'Jul'; break;
            case 8:  return 'Aug'; break;
            case 9:  return 'Sep'; break;
            case 10: return 'Oct'; break;
            case 11: return 'Nov'; break;
            case 12: return 'Dec'; break;
        }
        return '';
    }
    
    public function str2arr($str)
    {
        $arr = [];
        if (!is_array($str) && strpos($str, "rray\n") === 1) {
            if (strpos($str, '=>') !== false) {
                $split = explode('=>', str_replace("\n", "", str_replace("\n)\n", "", $str)));
                for ($i = 1; $i < sizeof($split); $i++) {
                    $val = trim(str_replace('[' . $i . ']', '', $split[$i]));
                    $arr[] = $val;
                }
            } else {
                $arr[] = 'EMPTY ARRAY';
            }
        }
        return $arr;
    }
    
    public function plainLineBreaks($str)
    {
        return str_replace("\n", "<br />", $str);
    }
    
    public function sec2minSec($sec)
    {
        $s = ($sec%60);
        $min = floor($sec/60);
        $m = ($min%60);
        $h = floor($min/60);
        return (($h > 0) ? $h . ':' : '') . (($h > 0 && $m < 10) ? '0' : '') . $m . ':' . (($s < 10) ? '0' : '') . $s;
    }
    
    public function slugify($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }
    
    function exportExcelOldSchool($innerTable, $inFilename = "export.xls")
    {
        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=' .$inFilename );
        echo "<table border=1>";
        echo $innerTable;
        echo "</table>";
        exit;
        return true;
    }
    
    
    
    public function parseSearchWords($search = '')
    {
        $search = trim($search);
        $ret = [$search];
        if (substr($search, 0, 1) == '"' && substr($search, 0, 1) == '"') {
            $ret = [substr($search, 1, strlen($search)-2)];
        } else {
            $quote1 = strpos($search, '"');
            while ($quote1 > 0) {
                $quote2 = strpos($search, '"', $quote1+1);
                if ($quote2 > 0) {
                    $quote = substr($search, $quote1, ($quote2-$quote1+1));
                    $search = str_replace($quote, '', $search);
                    $quote1 = strpos($search, '"');
                    $ret[] = str_replace('"', '', $quote);
                } else { // single instance of a double-quote :(
                    $search = str_replace('"', '', $search);
                }
            }
            $search = trim($search);
            if ($search != '') {
                $wordSplit = $GLOBALS["SL"]->mexplode(' ', str_replace('  ', ' ', $search));
                foreach ($wordSplit as $word) {
                    if (!in_array($word, $ret)) $ret[] = $word;
                }
            }
        }
        return $ret;
    }
    
}