<?php
/**
  * GlobalsTables is a mid-level class for loading and accessing system information from anywhere.
  * This level contains access to the database design, its tables, and field details.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */
namespace Survloop\Controllers\Globals;

class GlobalsElements extends GlobalsCache
{

    public function printAccordian($title, $body = '', $open = false, $big = false, $type = '', $ico = 'chevron')
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
                "isText"   => ($type == 'text'),
                "ico"      => $ico
          	]
        )->render();
    }

    public function printAccard($title, $body = '', $open = false)
    {
        return $this->printAccordian($title, $body, $open, true, 'card');
    }

    public function printAccordTxt($title, $body = '', $open = false, $ico = 'chevron')
    {
        return $this->printAccordian($title, $body, $open, false, 'text', $ico);
    }

    public function setAdmMenuOnLoad($open = 1) 
    {
        $this->x["admMenuOnLoad"] = $open;
        $this->pageJAVA .= ' openAdmMenuOnLoad = ' 
            . (($open == 1) ? 'true' : 'false') . '; ';
    }

    public function openAdmMenuOnLoad() 
    {
        if ((isset($this->x["needsCharts"]) && $this->x["needsCharts"])
            || (isset($this->x["needsPlots"]) && $this->x["needsPlots"])) {
            return true;
        }
        return (isset($this->x["admMenuOnLoad"])
            && intVal($this->x["admMenuOnLoad"]) == 1);
    }

    public function setFullPageLoaded($delay = 5, $treeID = 0) 
    {
        if ($treeID <= 0) {
            $treeID = $this->treeID;
        }
        $this->pageAJAX .= view(
            'vendor.survloop.js.inc-element-page-full-loaded', 
            [
                "treeID" => $treeID,
                "delay"  => $delay
            ]
        )->render();
    }

    public function setAutoRunSearch() 
    {
        $this->pageJAVA .= ' autoRunDashResults = true; ';
        return true;
    }

    public function setDashSearchDiv($divID) 
    {
        $this->pageJAVA .= ' setTimeout("'
            . 'document.getElementById(\'sResultsDivID\').value=\'' 
            . $divID . '\'", 1); ';
        return true;
    }

    public function setDashSearchUrl($url) 
    {
        $this->pageJAVA .= ' setTimeout("'
            . 'document.getElementById(\'sResultsUrlID\').value=\'' 
            . $url . '\'", 1); ';
        return true;
    }

    public function setTreePageFadeIn($delay = 250, $speed = 1000, $treeID = 0) 
    {
        if ($treeID <= 0) {
            $treeID = $this->treeID;
        }
        $this->pageAJAX .= view(
            'vendor.survloop.js.inc-element-tree-page-fade-in', 
            [
                "treeID" => $treeID,
                "delay"  => $delay,
                "speed"  => $speed
            ]
        )->render();
        return true;
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
        return view(
            'vendor.survloop.forms.formtree-datepicker', 
            [
                "fldName" => $fldName,
                "dateStr" => $dateStr,
                "tabInd"  => $this->tabInd()
            ]
        )->render();
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
        if (strpos(strtolower($url), 'https://youtu.be/') !== false) {
            return str_ireplace('https://youtu.be/', '', $url);
        }
        preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $url, $matches);
        return $matches[1];
    }

    public function getYoutubeDuration($vidURL)
    {
        if (stripos($vidURL, 'youtube') !== false) {
            
        }
        return -1;
    }

    public function getVimeoID($url)
    {
        if (preg_match('#(?:https?://)?(?:www.)?(?:player.)?vimeo.com/(?:[a-z]*/)*([0-9]{6,11})[?]?.*#', $url, $m)) {
            return $m[1];
        }
        return false;
        /*
        if (strpos(strtolower($vidURL), 'https://vimeo.com/') !== false) {
            return str_ireplace('https://vimeo.com/', '', $vidURL);
        }
        return '';
        */
    } 
    
    public function getArchiveOrgVidID($vidURL)
    {
        if (strpos(strtolower($vidURL), 'https://archive.org/') !== false) {
            $id = str_ireplace('https://archive.org/', '', $vidURL);
            if (strpos(strtolower($id), 'details/') == 0) {
                return substr($id, 8);
            }
        }
        return '';
    }
     
    public function getYouTubeThumb($id)
    {
        return 'http://i3.ytimg.com/vi/' . $id . '/0.jpg';
    }   
     
    public function getVimeoThumb($id)
    {
        $arr_vimeo = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$id.php"));
        return $arr_vimeo[0]['thumbnail_small']; // returns small thumbnail
        // return $arr_vimeo[0]['thumbnail_medium']; // returns medium thumbnail
        // return $arr_vimeo[0]['thumbnail_large']; // returns large thumbnail
    }
     
    public function getArchiveOrgVidThumb($id)
    {
        return 'https://archive.org/services/img/' . $id;
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
    
    public function getArchiveOrgVidUrl($id = '', $link = true, $class = '')
    {
        if (trim($id) == '') {
            return '';
        }
        $url = 'https://archive.org/details/' . $id;
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

    
    public function saveIconAnim()
    {
        return view(
            'vendor.survloop.elements.inc-save-ico-anim', 
            [ "rand" => rand(100000, 1000000) ]
        )->render();
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

    public function setPageNav2Scroll($lgTop = 350, $mdTop = 500, $smTop = 650)
    {
        $this->pageNav2Scroll = [ $lgTop, $mdTop, $smTop ];
        return true;
    }

    public function setPageNav2($navContent = '', $containerWrap = true)
    {
        $this->pageNav2 = trim($navContent);
        if ($this->pageNav2 != '') {
            if ($containerWrap) {
                $this->pageNav2 = '<div class="w100 container">' 
                    . $this->pageNav2 . '</div>';
            }
            $encode = json_encode($this->pageNav2);
            if (strpos($this->pageJAVA, $encode) === false
                && strpos($this->pageJAVA, 'currNav2') === false) {
                $this->pageJAVA .= ' currNav2 = ' . $encode . '; ';
                $this->pageJAVA .= ' setCurrNav2Pos(' . $this->pageNav2Scroll[0] 
                    . ', ' . $this->pageNav2Scroll[1] . ', ' 
                    . $this->pageNav2Scroll[2] . '); ';
            }
        }
        return true;
    }

    
    public function lastMonths12($rec = null, $monthFld = 'start_month')
    {
        $ret = [
            "has"        => false,
            "startYear"  => intVal(date("Y")),
            "startMonth" => intVal(date("n")),
            "endYear"    => (intVal(date("Y"))-1),
            "endMonth"   => (intVal(date("n"))+1)
        ];
        if ($rec !== null) {
            if (isset($rec->{ $monthFld }) 
                && intVal($rec->{ $monthFld }) > 0
                && isset($rec->created_at)) {
                $ret["has"] = true;
                $startMonth   = intVal($rec->{ $monthFld });
                $createdYear  = intVal(date("Y", strtotime($rec->created_at)));
                $createdMonth = intVal(date("n", strtotime($rec->created_at)));
                if ($createdMonth < $startMonth) {
                    $createdYear--;
                }
                $ret["startYear"]  = $createdYear;
                $ret["startMonth"] = $startMonth;
                $ret["endYear"]    = $createdYear-1;
                $ret["endMonth"]   = $startMonth+1;
                if ($ret["endMonth"] > 12) {
                    $ret["endMonth"] = 1;
                    $ret["endYear"]++;
                }
            }
        }
        return $ret;
    }

}
