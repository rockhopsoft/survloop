<?php
/**
  * GlobalsConvert is a bottom-level class for loading and accessing system information from anywhere.
  * This level contains the simplest conversion functions, etc.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.2.5
  */
namespace SurvLoop\Controllers\Globals;

class GlobalsConvert
{
    
    public function mexplode($delim, $str)
    {
        $ret = [];
        if (trim(str_replace($delim, '', $str)) != '') {
            if (strpos($str, $delim) === false) {
                $ret[] = $str;
            } else {
                if (substr($str, 0, 1) == $delim) {
                    $str = substr($str, 1);
                }
                if (substr($str, strlen($str)-1) == $delim) {
                    $str = substr($str, 0, strlen($str)-1);
                }
                $ret = explode($delim, $str);
            }
        }
        return $ret;
    }

    public function mexplodeSize($delim, $str)
    {
        return sizeof($this->mexplode($delim, $str));
    }
    
    public function wordLimitDotDotDot($str, $wordLimit = 50)
    {
        $strs = $this->mexplode(' ', $str);
        if (sizeof($strs) <= $wordLimit) {
            return $str;
        }
        $ret = '';
        for ($i=0; $i<$wordLimit; $i++) {
            $ret .= $strs[$i] . ' ';
        }
        return trim($ret) . '...';
    }
    
    public function sigFigs($value, $sigFigs = 2)
    {
        $exponent = floor(log10(abs($value))+1);
        if (pow(10, $exponent) == 0 || pow(10, $sigFigs) == 0) {
            return $value;
        }
        $significand = round(($value / pow(10, $exponent)) 
            * pow(10, $sigFigs)) / pow(10, $sigFigs);
        return $significand * pow(10, $exponent);
    }
    
    public function leadZero($num, $sigFigs = 2)
    {
        if ($sigFigs == 2) {
            return (($num < 10) ? '0' : '') . $num;
        }
        if ($sigFigs == 3) {
            return (($num < 10) ? '00' 
                : (($num < 100) ? '0' : '')) . $num;
        }
        return $num;
    }
    
    public function getFileExt($file)
    {
        $ext = '';
        if (trim($file) != '') {
            $tmpExt = $this->mexplode(".", $file);
            $ext = strtolower($tmpExt[(sizeof($tmpExt)-1)]);
        }
        return $ext;
    }
    
    public function sortArrByKey($arr, $key, $ord = 'asc')
    {
        if (sizeof($arr) < 2) {
            return $arr;
        }
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
                        if ($arrCopy[$j][$key] < $arrCopy[$nextInd][$key]) {
                            $nextInd = $j;
                        }
                    } else {
                        if ($arrCopy[$j][$key] > $arrCopy[$nextInd][$key]) {
                            $nextInd = $j;
                        }
                    }
                }
                $arr[] = $arrCopy[$nextInd];
                array_splice($arrCopy, $nextInd, 1);
            }
        }
        return $arr;
    }
    
    public function stdizeChars($txt)
    {
        return str_replace('“', '"', str_replace('”', '"', 
            str_replace("’", "'", $txt)));
    }

    public function humanFilesize($bytes, $decimals = 2) 
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) 
            . @$sz[$factor];
    }
    
    // Prints inches in feet and inches
    public function printHeight($val)
    {
        if ($val <= 0) {
            return '';
        }
        return (floor($val/12)) . "' " . floor($val%12) . '"';
    }
    
    public function getColsWidth($sizeof)
    {
        $colW = 12;
        if ($sizeof == 2) {
            $colW = 6;
        } elseif ($sizeof == 3) {
            $colW = 4;
        } elseif ($sizeof == 4) {
            $colW = 3;
        } elseif (in_array($sizeof, [5, 6])) {
            $colW = 2;
        } elseif (in_array($sizeof, [7, 8, 9, 10, 11, 12])) {
            $colW = 1;
        }
        return $colW;
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
    
    public function dateToTime($dateStr = '')
    {
        list($month, $day, $year) = ['', '', ''];
        if (trim($dateStr) != '') {
            if (strpos($dateStr, '-') > 0) {
                return strtotime(substr($dateStr, 0, 10));
            } elseif (strpos($dateStr, '/') > 0) {
                list($month, $day, $year) = explode('/', $dateStr);
                if (intVal($month) > 0 && intVal($day) > 0 
                    && intVal($year) > 0) {
                    return strtotime($year . '-' . $month 
                        . '-' . $day . ' 00:00:00');
                }
            }
        }
        return 0;
    }
    
    public function printTimeZoneShift($timeStr = '', $hourShift = -5, $format = 'n/j g:ia')
    {
        $time = strtotime($timeStr);
        return $this->printTimeZoneShiftStamp($time, $hourShift, $format);
    }
    
    public function printTimeZoneShiftStamp($time = 0, $hourShift = -5, $format = 'n/j g:ia')
    {
        $newTime = mktime(date('H', $time)+$hourShift, date('i', $time), 
            date('s', $time), date('m', $time), 
            date('d', $time), date('Y', $time));
        return date($format, $newTime);
    }
    
    public function printTimeAgo($str)
    {
        $date = new \DateTime($str);
        $now = date ('Y-m-d H:i:s', time());
        $now = new \DateTime($now);
        if ($now >= $date) {
            $timeDifference = date_diff($date , $now);
            $tense = " ago";
        } else {
            $timeDifference = date_diff($now, $date);
            $tense = " until";
        }
        $period = [" second", " minute", " hour", " day", " month", " year" ];
        $periodValue= [
            $timeDifference->format('%s'), 
            $timeDifference->format('%i'), 
            $timeDifference->format('%h'), 
            $timeDifference->format('%d'), 
            $timeDifference->format('%m'), 
            $timeDifference->format('%y')
            ];
        for ($i = 0; $i < count($periodValue); $i++) {
            if ($periodValue[$i] != 1) {
                $period[$i] .= "s";
            }
            if ($periodValue[$i] > 0) {
                $interval = $periodValue[$i].$period[$i].$tense;
            }
        }
        if (isset($interval)) {
            return $interval;
        }
        return "0 seconds" . $tense;
    }
    
    public function str2arr($str)
    {
        $arr = [];
        if (!is_array($str) && strpos($str, "rray\n") === 1) {
            if (strpos($str, '=>') !== false) {
                $split = explode('=>', str_replace("\n", "", 
                    str_replace("\n)\n", "", $str)));
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
        return str_replace("\n", "<br />", str_replace("\t", "    ", $str));
    }
    
    public function sec2minSec($sec)
    {
        $s = ($sec%60);
        $min = floor($sec/60);
        $m = ($min%60);
        $h = floor($min/60);
        return (($h > 0) ? $h . ':' : '') 
            . (($h > 0 && $m < 10) ? '0' : '') 
            . $m . ':' . (($s < 10) ? '0' : '') . $s;
    }
    
    public function numSupscript($num)
    {
        $numStr = trim($num);
        $last = intVal(substr($numStr, strlen($numStr)-1));
        if (in_array($num, [11, 12, 13])) {
            return '<sup>th</sup>';
        } elseif ($last == 1) {
            return '<sup>st</sup>';
        } elseif ($last == 2) {
            return '<sup>nd</sup>';
        } elseif ($last == 3) {
            return '<sup>rd</sup>';
        }
        return '<sup>th</sup>';
    }
    
    public function calcGrade($num = 100)
    {
        if ($num >= 97) {
            return 'A+';
        }
        if ($num >= 93) {
            return 'A';
        }
        if ($num >= 90) {
            return 'A-';
        }
        if ($num >= 87) {
            return 'B+';
        }
        if ($num >= 83) {
            return 'B';
        }
        if ($num >= 80) {
            return 'B-';
        }
        if ($num >= 77) {
            return 'C+';
        }
        if ($num >= 73) {
            return 'C';
        }
        if ($num >= 70) {
            return 'C-';
        }
        if ($num >= 67) {
            return 'D+';
        }
        if ($num >= 63) {
            return 'D';
        }
        if ($num >= 60) {
            return 'D-';
        }
        return 'F';
    }
    
    public function calcGradeSmp($num = 100)
    {
        if ($num >= 90) {
            return 'A';
        }
        if ($num >= 80) {
            return 'B';
        }
        if ($num >= 70) {
            return 'C';
        }
        if ($num >= 60) {
            return 'D';
        }
        return 'F';
    }
    
    public function convertAllCallToUp1stChars($str)
    {
        if (strtoupper($str) == $str) {
            return $this->allCapsToUp1stChars($str);
        }
        return $str;
    }
    
    public function allCapsToUp1stChars($str)
    {
        if (strtoupper($str) == $str) {
            $strOut = '';
            $words = $this->mexplode(' ', $str);
            if (sizeof($words) > 0) {
                foreach ($words as $w) {
                    if (strlen($w) > 1) {
                        $strOut .= substr($w, 0, 1) 
                            . strtolower(substr($w, 1)) . ' ';
                    }
                }
            }
            return trim($strOut);
        }
        return $str;
    }

    public function arrAvg($array = [])
    {
        if (is_array($array) && count($array) > 0) {
            return array_sum($array)/count($array);
        }
        return 0;
    }

    public function commaListAvg($commas = ',,')
    {
        return $this->arrAvg($this->mexplode(',', $commas));
    }
    
    public function arrStandardDeviation($dat = [])
    {
        $avg = $diffs = 0;
        if (sizeof($dat) > 0) {
            $avg = array_sum($dat)/sizeof($dat);
            foreach ($dat as $value) {
                $diffs += ($value-$avg)*($value-$avg);
            }
            return abs(sqrt($diffs/sizeof($dat)));
        }
        return 0;
    }
    
    public function getArrPercentileStr($str, $val, $isGolf = false)
    {
        return $this->getArrPercentile(
            $this->mexplode(',', $str), 
            $val, 
            $isGolf
        );
    }
    
    public function getArrPercentile($arr, $val, $isGolf = false)
    {
        $ret = $pos = 0;
        $max = (($isGolf) ? 1000000000 : -1000000000);
        if (is_array($arr) && sizeof($arr) > 0) {
            foreach ($arr as $i => $v) {
                if (floatval($val) >= floatval($v) 
                    && $max != $v) {
                    $pos = $i;
                    $max = $v;
                }
            }
            if ($isGolf) {
                $ret = (1-($pos/sizeof($arr)))*100;
            } else { // higher value is better
                $ret = ($pos/sizeof($arr))*100;
            }
        }
        return $ret;
    }
    
    public function textSaferHtml($strIN)
    {
        return '<p>' . str_replace("\n", '</p><p>', 
            str_replace("\n\n", "\n", str_replace("\n\n", "\n", 
            strip_tags($strIN, '<b><i><u>')))) . '</p>';
    }
    
    public function makeXMLSafe($strIN)
    {
        //$strIN = htmlentities($strIN);
        $strIN = str_replace("�", "'", str_replace('�', '\'', $strIN));
        $strIN = str_replace("&#146;", "'", str_replace("&#145;", "'", $strIN));
        $strIN = str_replace("&#148;", "'", str_replace("&#147;", "'", $strIN));
        //$strIN = str_replace('&amp;', '&', str_replace('&amp;', '&', 
        //  str_replace('&amp;', '&', $strIN)));
        $strIN = str_replace("&#39;", "'", str_replace("&apos;", "'", $strIN));
        $strIN = str_replace('&quot;', '"', $strIN);
        $strIN = str_replace('&', '&amp;', $strIN);
        $strIN = str_replace("'", "&apos;", str_replace("\'", "&apos;", 
            str_replace("\\'", "&apos;", $strIN)));
        $strIN = str_replace('"', '&quot;', str_replace('\"', '&quot;', 
            str_replace('\\"', '&quot;', $strIN)));
        $strIN = str_replace('<', '&lt;', $strIN);
        $strIN = str_replace('>', '&gt;', $strIN);
        return htmlspecialchars(trim($strIN), ENT_XML1, 'UTF-8');
        //return trim($strIN);
    }


}
