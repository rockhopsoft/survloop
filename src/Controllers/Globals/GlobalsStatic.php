<?php
/**
  * GlobalsStatic is the mid-level core class for loading and accessing system information from anywhere.
  * This level contains mostly standalone functions which are not SurvLoop-specific.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.0.18
  */
namespace SurvLoop\Controllers\Globals;

use Auth;
use Illuminate\Http\Request;

use SurvLoop\Controllers\Globals\GlobalsConvert;

class GlobalsStatic extends GlobalsConvert
{
    public $uID = -3;
    public $REQ = [];
    
    function __construct(Request $request = NULL)
    {
        $this->loadStatic($request);
    }
    
    public function loadStatic(Request $request = NULL)
    {
        $this->uID = ((Auth::user()) ? Auth::user()->id : -3);
        $this->REQ = $request;
        return true;
    }
    
    public function splitNumDash($str, $delim = '-')
    {
        $str = trim($str);
        $pos = strpos($str, $delim);
        if ($pos !== false) {
            return [
                intVal(substr($str, 0, $pos)),
                intVal(substr($str, (1+$pos)))
            ];
        }
        if ($str != '') {
            return [ 0, intVal($str) ];
        }
        return [ 0, 0 ];
    }
    
    public function swapURLwrap($url, $printHttp = true)
    {
        $urlPrint = str_replace('mailto:', '', $url);
        if (!$printHttp) {
            $urlPrint = $this->printURLdomain($urlPrint);
        }
        return '<a href="' . $url . '" target="_blank" class="dont-break-out">'
            . $urlPrint . '</a>'; 
    }
    
    public function printURLdomain($url)
    {
        if (trim($url) != '') {
            $url = str_replace('http://', '', str_replace('https://', '', 
                str_replace('http://www.', '', str_replace('https://www.', '', $url))));
            if (substr($url, strlen($url)-1) == '/') {
                $url = substr($url, 0, strlen($url)-1);
            }
        }
        return $url;
    }
    
    public function searchDeeperDirs($file)
    {
        $newFile = $file;
        $limit = 0;
        while (!file_exists($newFile) && $limit < 9) {
            $newFile = '../' . $newFile;
            $limit++;
        }
        if (file_exists($newFile)) {
            return $newFile;
        }
        return $file;
    }
    
    public function copyDirFiles($from, $to, $recurse = true)
    {
        if (trim($from) == '' || trim($to) == '' || !file_exists($from)) {
            return '';
        }
        $ret = '';
        $dir = opendir($from);
        if (!file_exists($to) || !is_dir($to)) @mkdir($to);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($from . '/' . $file)) {
                    if ($recurse) {
                        copyDirFiles($from . '/' . $file, $to . '/' . $file);
                    }
                } else {
                    if (copy($from . '/' . $file, $to . '/' . $file)) {
                        $ret .= 'copied ' . $from . '/' . $file . ' to '
                            . $to . '/' . $file . '<br />' . "\n";
                    } else {
                        $ret .= 'didn\'t copy ' . $from . '/' . $file . ' to '
                            . $to . '/' . $file . '<br />' . "\n";
                    }
                }
            }
        }
        closedir($dir);
        return $ret;
    }
    
    public function getDirSize($dirPath = '', $type = '')
    {
        if (!file_exists($dirPath)) {
            return 0;
        }
        $size = 0;
        $dir = opendir($dirPath);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($dirPath . '/' . $file)) {
                    $size += $this->getDirSize($dirPath . '/' . $file, $type);
                } elseif (($type == '' || strpos($file, $type) > 0) && file_exists($dirPath . '/' . $file)) {
                    $size += filesize($dirPath . '/' . $file);
                }
            }
        }
        closedir($dir);
        return $size;
    }
    
    public function getDirLinesCount($dirPath = '', $type = '.php')
    {
        $lines = 0;
        $dir = opendir($dirPath);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($dirPath . '/' . $file)) {
                    $lines += $this->getDirLinesCount($dirPath . '/' . $file, $type);
                } elseif ($type == '' || strpos($file, $type) > 0) {
                    $cnt = $this->getFileLineCount($dirPath . '/' . $file);
                    $lines += $cnt;
                }
            }
        }
        closedir($dir);
        return $lines;
    }
    
    public function getFileLineCount($file = '')
    {
        $lines = 0;
        if (trim($file) != '' && file_exists($file)) {
            $handle = fopen($file, "r");
            while (!feof($handle)) {
                $line = fgets($handle);
                $lines++;
            }
            fclose($handle);
        }
        return $lines;
    }
    
    public function findDirFile($folder, $file)
    {
        return $this->findDirFileInner($folder, $file);
    }
    
    public function findDirFileInner($folder, $file, $subFold = [])
    {
        if (!file_exists($folder) || !is_dir($folder)) {
            return [];
        }
        $dir = opendir($folder);
        while (false !== ($f = readdir($dir))) {
            if ($f != '.' && $f != '..') {
                if (is_dir($folder . '/' . $f)) {
                    $tmp = $subFold;
                    $tmp[] = $f;
                    $tmp = $this->findDirFileInner($folder . '/' . $f, $file, $tmp);
                    if (sizeof($tmp) > 0) {
                        return $tmp;
                    }
                } else {
                    if ($f == $file) {
                        return $subFold;
                    }
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
                    if ($recurse) {
                        $ret[] = $this->mapDirFiles($folder . '/' . $file, true);
                    }
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
                if (is_array($file)) {
                    $map[$i] = $this->mapDirSlimmer($map[$i], $folder);
                } else {
                    $map[$i] = str_replace($folder . '/', '', $map[$i]);
                }
            }
        }
        return $map;
    }
    public function mapDirFilesSlim($folder, $recurse = true)
    {
        return $this->mapDirSlimmer($this->mapDirFiles($folder, $recurse), $folder);
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
    
    public function urlPreview($url)
    {
        $url = urlClean($url);
        if (strpos($url, '/') !== false) {
            $url = substr($url, 0, strpos($url, '/'));
        }
        return $url;
    }
    
    public function urlClean($url)
    {
        $url = str_replace('m.facebook.com/', 'facebook.com/', 
            str_replace('http://', '', str_replace('https://', '', 
            str_replace('http://www.', '', str_replace('https://www.', '', $url)))));
        $pos = strrpos($url, '/');
        if ($pos !== false && $pos == strlen($url)-1) {
            $url = substr($url, 0, $pos);
        }
        return $url;
    }
    
    public function urlCleanIfShort($url, $altLabel = 'Link', $max = 35)
    {
        $shrt = $this->urlClean($url);
        if (strlen($shrt) > $max) {
            return $altLabel;
        }
        return $shrt;
    }
    
    // takes in and returns rows of [ Record ID, Ranked Value, Rank Order, Percentile ]
    public function calcPercentiles($arr = [])
    {
        $bak = $arr;
        $sorted = [];
        for ($i=0; $i<sizeof($bak); $i++) {
            $max = [ 0, -100000000 ];
            for ($j=0; $j<sizeof($bak); $j++) {
                if (isset($arr[$j]) && $max[1] < $arr[$j][1]) {
                    $max = [ $j, $arr[$j][1] ];
                }
            }
            $sorted[] = [ $arr[$max[0]][0], $arr[$max[0]][1], sizeof($sorted), -1 ];
            unset($arr[$max[0]]);
        }
        for ($i=0; $i<sizeof($sorted); $i++) {
            $sorted[$i][3] = (100*(sizeof($sorted)-$sorted[$i][2])/sizeof($sorted));
        }
        return $sorted;
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
                $wordSplit = $this->mexplode(' ', str_replace('  ', ' ', $search));
                foreach ($wordSplit as $word) {
                    if (!in_array($word, $ret)) {
                        $ret[] = $word;
                    }
                }
            }
        }
        return $ret;
    }
    
    public function getCurrUrlBase()
    {
        $url = $_SERVER['REQUEST_URI'];
        if (strpos($url, '?') !== false) {
            return substr($url, 0, strpos($url, '?'));
        }
        return $url;
    }
    
    public function get_content($URL)
    {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $URL);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
    
    public function getIP()
    {
        $ip = $_SERVER["REMOTE_ADDR"];
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"]; // share internet
        } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; // pass from proxy
        }
        return $ip;
    }
    
    public function hashIP()
    {
        return hash('sha512', $this->getIP());
    }
    
    public function isMobile()
    {
        if (!isset($_SERVER["HTTP_USER_AGENT"])) {
            return false;
        }
    	return (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec'
			. '|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?'
			. '|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap'
			. '|windows (ce|phone)|xda|xiino/i', $_SERVER["HTTP_USER_AGENT"])
			|| preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av'
			. '|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb'
			. '|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw'
			. '|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8'
			. '|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit'
			. '|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)'
			. '|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji'
			. '|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga'
			. '|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)'
			. '|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf'
			. '|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil'
			. '|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380'
			. '|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc'
			. '|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01'
			. '|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)'
			. '|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61'
			. '|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', 
			substr($_SERVER["HTTP_USER_AGENT"],0,4)));
    }

    public function getPastDateTime($days = 3)
    {
        return mktime(0, 0, 0, date("n"), date("j")-$days, date("Y"));
    }

    public function pastDateTimeStr($days = 3)
    {
        return date("Y-m-d H:i:s", mktime(0, 0, 0, date("n"), date("j")-$days, date("Y")));
    }

    public function getPastDateArray($minAge = 0)
    {
        if ($minAge <= 0 || $minAge >= mktime(0, 0, 0, date("n"), date("j")+1, date("Y"))) {
            $minAge = $this->getPastDateTime();
        }
        $start = mktime(0, 0, 0, date("n"), date("j")+1, date("Y"));
        $dates = [];
        for ($i = $start; $i >= $minAge; $i -= (60*60*24)) {
            $dates[] = date("Ymd", $i);
        }
        return $dates;
    }


}