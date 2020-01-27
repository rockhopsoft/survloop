<?php
/**
  * GlobalsTables is a mid-level class for loading and accessing system information from anywhere.
  * This level contains access to the database design, its tables, and field details.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.2.5
  */
namespace SurvLoop\Controllers\Globals;

class GlobalsElements extends GlobalsCache
{

    public function printAccordian($title, $body = '', $open = false, $big = false, $type = '')
    {
      	return view(
            'vendor.survloop.elements.inc-accordian', 
            [
        		"accordID" => rand(100000, 1000000),
        		"title"    => $title,
        		"body"     => $body,
        		"big"      => $big,
        		"open"     => $open,
                "isCard"   => ($type == 'card'),
                "isText"   => ($type == 'text')
          	]
        )->render();
    }

    public function printAccard($title, $body = '', $open = false)
    {
        return $this->printAccordian($title, $body, $open, true, 'card');
    }

    public function printAccordTxt($title, $body = '', $open = false)
    {
        return $this->printAccordian($title, $body, $open, false, 'text');
    }

    public function addSideNavItem($title = '', $url = '', $delay = 2000)
    {
        $this->pageJAVA .= ' setTimeout(\'addSideNavItem("' 
            . $title . '", "' . $url . '")\', ' . $delay . '); ';
        return true;
    }

    public function printDatePicker($dateStr = '', $fldName = '')
    {
        if ($fldName == '') {
            $fldName = rand(10000000, 100000000);
        }
        $this->pageAJAX .= '$( "#' . $fldName 
            . 'ID" ).datepicker({ maxDate: "+0d" });';
        return '<input type="text" name="' . $fldName . '" id="' 
            . $fldName . 'ID" value="' . (($dateStr != '') 
                ? date("m/d/Y", strtotime($dateStr)) : '')
            . '" class="dateFld form-control" ' . $this->tabInd() 
            . ' autocomplete="off" >' . "\n";
    }
    
    public function getTwitShareLnk($url = '', $title = '', $hashtags = '')
    {
        return 'http://twitter.com/share?url=' . urlencode($url) 
            . ((trim($title) != '') ? '&text=' . urlencode($title) : '')
            . ((trim($hashtags) != '') ? '&hashtags=' . urlencode($hashtags) : '');
    }
    
    public function twitShareBtn($url = '', $title = '', $hashtags = '', $class = '', $btnText = '')
    {
        return view(
            'vendor.survloop.elements.inc-social-simple-tweet', 
            [
                "link"     => $url,
                "title"    => $title,
                "hashtags" => $hashtags,
                "class"    => $class,
                "btnText"  => $btnText
            ]
        )->render();
    }
    
    public function getFacebookShareLnk($url = '', $title = '')
    {
        return 'https://www.facebook.com/sharer/sharer.php?u=' 
            . urlencode($url);
    }
    
    public function faceShareBtn($url = '', $title = '', $class = '', $btnText = '')
    {
        return view(
            'vendor.survloop.elements.inc-social-simple-facebook', 
            [
                "link"    => $url,
                "title"   => $title,
                "class"   => $class,
                "btnText" => $btnText
            ]
        )->render();
    }
    
    public function getLinkedinShareLnk($url = '', $title = '')
    {
        return 'https://www.linkedin.com/shareArticle?mini=true&url=' 
            . urlencode($url) . '&title=' . urlencode($title);
    }
    
    public function linkedinShareBtn($url = '', $title = '', $class = '', $btnText = '')
    {
        return view(
            'vendor.survloop.elements.inc-social-simple-linkedin', 
            [
                "link"    => $url,
                "title"   => $title,
                "class"   => $class,
                "btnText" => $btnText
            ]
        )->render();
    }
    
    public function getYoutubeID($url)
    {
        $ret = '';
        $pos = strpos($url, 'v=');
        if ($pos > 0) {
            $ret = substr($url, (2+$pos));
            $pos = strpos($ret, '&');
            if ($pos > 0) {
                $ret = substr($ret, 0, $pos);
            }
        }
        return $ret;
    }
    
    public function getYoutubeUrl($id = '', $link = true, $class = '')
    {
        if (trim($id) == '') {
            return '';
        }
        $url = 'https://www.youtube.com/watch?v=' . $id;
        if (!$link) {
            return $url;
        }
        return '<a href="' . $url . '" target="_blank"' 
            . (($class != '') ? ' class="' . $class . '"' : '')
            . ' >' . $url . '</a>';
    }
    
    public function getVimeoUrl($id = '', $link = true, $class = '')
    {
        if (trim($id) == '') {
            return '';
        }
        $url = 'https://vimeo.com/' . $id;
        if (!$link) {
            return $url;
        }
        return '<a href="' . $url . '" target="_blank"' 
            . (($class != '') ? ' class="' . $class . '"' : '')
            . ' >' . $url . '</a>';
    }
    
    public function tabInd()
    {
        $this->currTabInd++;
        return ' tabindex="' . $this->currTabInd . '" '; 
    }
    
    public function replaceTabInd($str)
    {
        $pos = strpos($str, 'tabindex="');
        if ($pos === false) {
            return $str . $this->tabInd();
        }
        $posEnd = strpos($str, '"', (10+$pos));
        return substr($str, 0, $pos) . $this->tabInd() . substr($str, (1+$posEnd));
    }
    
    public function colorHex2Rgba($hex = '#000000', $a = 1)
    {
        $hex = str_replace("#", "", $hex);
        $rgba = [
            "r" => 0,
            "g" => 0,
            "b" => 0,
            "a" => $a
        ];
        if (strlen($hex) == 3) {
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
        return '#' . dechex($rgba["r"]) 
            . dechex($rgba["g"]) 
            . dechex($rgba["b"]);
    }

    public function printRgba($rgba = [])
    {
        if (!isset($rgba["r"])) {
            return '';
        }
        if (!isset($rgba["a"]) || $rgba["a"] == 1) {
            return 'rgb(' . $rgba["r"] . ', ' 
                . $rgba["g"] . ', ' . $rgba["b"] . ')';
        }
        return 'rgba(' . $rgba["r"] . ', ' 
            . $rgba["g"] . ', ' . $rgba["b"] . ', '
            . number_format($rgba["a"], 2) . ')';
    }
    
    public function colorFade($perc = 0, $hex1 = '#ffffff', $hex2 = '#000000', $a1 = 1, $a2 = 1)
    {
        $c1 = $this->colorHex2Rgba($hex1, $a1);
        $c2 = $this->colorHex2Rgba($hex2, $a2);
        if ($perc == 1) {
            return $c2;
        } elseif ($perc == 0) {
            return $c1;
        }
        $cNew = [
            "r" => (($c1["r"] == $c2["r"]) ? $c1["r"] 
                : intVal(($c1["r"]+(($c2["r"]-$c1["r"])*$perc)))),
            "g" => (($c1["g"] == $c2["g"]) ? $c1["g"] 
                : intVal(($c1["g"]+(($c2["g"]-$c1["g"])*$perc)))),
            "b" => (($c1["b"] == $c2["b"]) ? $c1["b"] 
                : intVal(($c1["b"]+(($c2["b"]-$c1["b"])*$perc)))),
            "a" => (($c1["a"] == $c2["a"]) ? $c1["a"] 
                : number_format(($c1["a"]+(($c2["a"]-$c1["a"])*$perc)), 2))
        ];
        return $cNew;
    }
    
    public function printColorFade($perc = 0, $hex1 = '#ffffff', $hex2 = '#000000', $a1 = 1, $a2 = 1)
    {
        return $this->printRgba(
            $this->colorFade($perc, $hex1, $hex2, $a1, $a2)
        );
    }
    
    public function printColorFadeHex($perc = 0, $hex1 = '#ffffff', $hex2 = '#000000', $a1 = 1, $a2 = 1)
    {
        return $this->colorRgba2Hex(
            $this->colorFade($perc, $hex1, $hex2, $a1, $a2)
        );
    }
    
    public function printHex2Rgba($hex = '#000000', $a = 1)
    {
        return $this->printRgba($this->colorHex2Rgba($hex, $a));
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

}
