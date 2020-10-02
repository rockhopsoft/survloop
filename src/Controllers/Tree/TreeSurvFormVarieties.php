<?php
/**
  * TreeSurvFormVarieties is a mid-level class with functions to print specific node types,
  * and swap out various language.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.1.2
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SLTree;
use RockHopSoft\Survloop\Controllers\Tree\UserProfile;

class TreeSurvFormVarieties extends UserProfile
{
    protected function customLabels($curr, $str = '')
    {
        return $str;
    }
    
    protected function swapIDs($nIDtxt = '', $str = '')
    {
        $str = str_replace('[[nID]]', $nIDtxt, $str);
        $str = str_replace('[[cID]]', $this->coreID, $str);
        $str = str_replace('[[coreID]]', $this->coreID, $str);
        $str = str_replace('[[corePubID]]', $this->getCurrPubID(), $str);
        $str = str_replace('[[DOMAIN]]', $GLOBALS["SL"]->sysOpts["app-url"], $str);
        $tbl = $GLOBALS["SL"]->coreTbl;
        if (strpos($str, '[[coreUnqStr]]') !== false
            && isset($GLOBALS["SL"]->tblAbbr[$tbl])
            && isset($this->sessData->dataSets[$tbl])) {
            $unqStrFld = $GLOBALS["SL"]->tblAbbr[$tbl] . 'unique_str';
            if (isset($this->sessData->dataSets[$tbl][0]->{ $unqStrFld })) {
                $unqStr = $this->sessData->dataSets[$tbl][0]->{ $unqStrFld };
                $str = str_replace('[[coreUnqStr]]', $unqStr, $str);
            }
        }
        return $str;
    }
    
    protected function swapIDsSEO($extraOpts = [])
    {
        if (sizeof($extraOpts) > 0) {
            foreach ($extraOpts as $key => $val) {
                $extraOpts[$key] = $this->swapIDs('', $val);
            }
        }
        return $this->swapIDsSEOCustom($extraOpts);
    }
    
    protected function swapIDsSEOCustom($extraOpts = [])
    {
        return $extraOpts;
    }
    
    protected function autoLabelClass($nIDtxt = '')
    {
        return 'slBlueDark';
    }
    
    protected function swapLabels($curr, $str = '')
    {
        if (trim($str) == '') {
            return '';
        }
        if (strpos($str, '[LoopItemLabel]') !== false 
            && $curr->itemID <= 0 
            && $curr->itemInd < 0) {
            $this->printNodePublicCurrDataGetItem($curr);
        }

        $str = $this->sendEmailBlurbsCustom($str);
        $str = $this->customLabels($curr, $str);
        $str = $GLOBALS["SL"]->swapBlurbs($str);
        $str = $this->swapIDs($curr->nIDtxt, $str);
        if (!isset($this->v["printFullTree"]) || !$this->v["printFullTree"]) {
            if ($curr->itemID > 0 && $curr->itemInd >= 0) {
                if (strpos($str, '[LoopItemLabel]') !== false 
                    && isset($GLOBALS["SL"]->closestLoop["loop"])) {
                    $loop = $GLOBALS["SL"]->closestLoop["loop"];
                    $loopTbl = $GLOBALS["SL"]->closestLoop["obj"]->data_loop_table;
                    $itemRowID = $this->sessData->getRowById($loopTbl, $curr->itemID);
                    $label = $this->getLoopItemLabel($loop, $itemRowID, $curr->itemInd);
                    $labelSwap = '<span class="' . $this->autoLabelClass($curr->nIDtxt) 
                        . '">' . $label . '</span>';
                    $str = str_replace('[LoopItemLabel]', $labelSwap, $str);
                }
                $cnt = 1+$curr->itemInd;
                if (isset($GLOBALS["SL"]->closestLoop["loop"])) {
                    $rows = $this->sessData->getLoopRows($GLOBALS["SL"]->closestLoop["loop"]);
                    if ($rows && sizeof($rows) > 0) {
                        foreach ($rows as $j => $rec) {
                            if ($rec->getKey() == $curr->itemID) {
                                $cnt = 1+$j;
                            }
                        }
                    }
                }
                $labelSwap = '<span class="' . $this->autoLabelClass($curr->nIDtxt) 
                    . '">' . $cnt . '</span>';
                $str = str_replace('[LoopItemCnt]', $labelSwap, $str);
                $str = str_replace('[LoopItemID]', $curr->itemID, $str);
            }
            $labelPos = strpos($str, '[LoopItemLabel:');
            if (($curr->itemID <= 0 || $curr->itemInd < 0) && $labelPos !== false) {
                $strPre      = substr($str, 0, $labelPos);
                $loopName    = substr($str, $labelPos+15);
                $labelEndPos = strpos($loopName, ']');
                $strPost     = substr($loopName, $labelEndPos+1);
                $loopName    = substr($loopName, 0, $labelEndPos);
                $loopRows    = $this->sessData->getLoopRows($loopName);
                if (sizeof($loopRows) == 1) {
                    $label = $this->getLoopItemLabel($loopName, $loopRows[0], $curr->itemInd);
                    $str = $strPre . '<span class="' 
                        . $this->autoLabelClass($curr->nIDtxt) 
                        . '">' . $label . '</span>' . $strPost;
                }
            }
        }
        $str = $this->customCleanLabel($str, $curr->nIDtxt);
        return $this->cleanLabel($str);
    }
    
    protected function customCleanLabel($str = '', $nIDtxt = '')
    {
        return $str;
    }
    
    protected function cleanLabel($str = '')
    {
        $cls = $this->autoLabelClass();
        $span = '<span class="' . $cls . '">';
        $str = str_replace($span . 'You</span>', $span . 'you</span>', $str);
        $str = str_replace($span . 'you</span>&#39;s', $span . 'your</span>', $str);
        $str = str_replace(
            'Was <span class="' . $cls . '">you</span>', 
            'Were <span class="' . $cls . '">you</span>', 
            $str
        );
        $str = str_replace(
            'was <span class="' . $cls . '">you</span>', 
            'were <span class="' . $cls . '">you</span>', 
            $str
        );
        $str = str_replace($span . 'you</span>\'s', $span . 'your</span>', $str);
        $str = str_replace($span . 'you</span> was', $span . 'you</span> were', $str);
        $str = str_replace(', <span class="' . $cls . '">[LoopItemLabel]</span>:', ':', $str);
        $str = str_replace(', [LoopItemLabel]:', ':', $str);
        $str = str_replace(', <span class="' . $cls . '">&nbsp;</span>:', ':', $str);
        $str = str_replace(', <span class="' . $cls . '"></span>:', ':', $str);
        $str = trim(str_replace(', :', ':', $str));
        if (strpos(strip_tags($str), 'you') === 0) {
            $str = str_replace($span . 'you', $span . 'You', $str);
        }
        $str = str_replace('you&#39;s', 'your', str_replace('You&#39;s', 'Your', $str));
        $str = str_replace('Was you', 'Were you', str_replace('Was You', 'Were you', $str));
        $str = str_replace('was you', 'were you', str_replace('was You', 'were you', $str));
        $str = str_replace('you\'s', 'your', str_replace('You\'s', 'Your', $str));
        $str = str_replace('you was', 'you were', str_replace('You was', 'You were', $str));
        $str = str_replace(', <span class="' . $cls . '">[LoopItemLabel]:', ':', $str);
        $str = str_replace(', [LoopItemLabel]:', ':', $str);
        $str = str_replace(', <span class="' . $cls . '">&nbsp;:', ':', $str);
        $str = str_replace(', <span class="' . $cls . '">:', ':', $str);
        $str = trim(str_replace(', :', ':', $str));
        if (strpos(strip_tags($str), 'you') === 0) {
            $str = str_replace('you', 'You', $str);
        }
        return $str;
    }
    
    protected function wrapNodePrint($ret, $nID)
    {
        if ($this->allNodes[$nID]->chkCurrOpt('DEFERLOAD')) {
            return $GLOBALS["SL"]->deferStaticNodePrint($nID, $ret, $this->coreID);
        }
        return $ret;
    }
    
    protected function getNodeFormFldBasic($nID = -3, $curr = null)
    {
        if ($nID <= 0) {
            return null;
        }
        if (!$curr) {
            if (!isset($this->allNodes[$nID])) {
                return null;
            }
            $curr = $this->allNodes[$nID];
        }
        if ($curr->nodeType == 'Big Button') {
            return null;
        }
        $nIDtxt = $nID . $GLOBALS["SL"]->getCycSffx();
        $newVal = null;
        if ($GLOBALS["SL"]->REQ->has('n' . $nIDtxt . 'fld')) {
            $newVal = $GLOBALS["SL"]->REQ->input('n' . $nIDtxt . 'fld');
        }
        if ($curr->nodeType == 'Checkbox' || $curr->isDropdownTagger()) {
            $newVal = [];
            if ($GLOBALS["SL"]->REQ->has('n' . $nIDtxt . 'fld')) {
                $newVal = $GLOBALS["SL"]->REQ->get('n' . $nIDtxt . 'fld');
            }
            if ($GLOBALS["SL"]->REQ->has('n' . $nIDtxt . 'tagIDs')) { // isDropdownTagger()
                $newVal = $GLOBALS["SL"]->mexplode(',', $GLOBALS["SL"]->REQ->get('n' . $nIDtxt . 'tagIDs'));
            }
        } else {
            if ($curr->nodeType == 'Text:Number') {
                if (!$GLOBALS["SL"]->REQ->has('n' . $nIDtxt . 'fld')
                    || trim($GLOBALS["SL"]->REQ->get('n' . $nIDtxt . 'fld')) == '') {
                    $newVal = null;
                }
            } elseif (in_array($curr->nodeType, ['Date', 'Date Picker'])) {
                $newVal = date("Y-m-d", strtotime($newVal));
            } elseif ($curr->nodeType == 'Date Time') {
                $newVal = date("Y-m-d", strtotime($newVal)) . ' ' . $this->postFormTimeStr($nID);
            } elseif ($curr->nodeType == 'Password') {
                $newVal = md5($newVal);
            }
        }
        return $newVal;
    }
    
    public function addPromptTextRequired($currNode = NULL, $nodePromptText = '', $nIDtxt = 'noNID')
    {
        if (!$currNode || !isset($currNode->nodeRow->node_opts)) {
            return '';
        }
        $txt = '*required';
        /* This needs to be limited in the form, before any validation
        if ($this->nodeHasDateRestriction($currNode->nodeRow)) {
            if ($currNode->nodeRow->node_char_limit < 0) {
                $txt = '*past date required';
            } elseif ($currNode->nodeRow->node_char_limit > 0) {
                $txt = '*future date required';
            }
        }
        */
        $txt = '<nobr>' . $txt . '</nobr>';
        if ($currNode->nodeRow->node_opts%13 == 0) {
            return $nodePromptText . '<p id="req' . $nIDtxt . '" class="rqd">' . $txt . '</p>';
        } else {
            $swapPos = -1;
            $lastP = strrpos($nodePromptText, '</p>');
            $lastDiv = strrpos($nodePromptText, '</div>');
            if ($lastP > 0) {
                $swapPos = $lastP;
            } elseif ($lastDiv > 0) {
                $swapPos = $lastDiv;
            }
            if ($swapPos > 0) {
                return substr($nodePromptText, 0, $swapPos) . ' <span id="req' . $nIDtxt . '" class="rqd">' 
                    . $txt . '</span>' . substr($nodePromptText, $swapPos);
            } else {
                $lastH3 = strrpos($nodePromptText, '</h3>');
                $lastH2 = strrpos($nodePromptText, '</h2>');
                $lastH1 = strrpos($nodePromptText, '</h1>');
                if ($lastH3 > 0) {
                    $swapPos = $lastH3;
                } elseif ($lastH2 > 0) {
                    $swapPos = $lastH2;
                } elseif ($lastH1 > 0) {
                    $swapPos = $lastH1;
                }
                if ($swapPos > 0) {
                    return substr($nodePromptText, 0, $swapPos) . ' <small id="req' . $nIDtxt 
                        . '" class="rqd">' . $txt . '</small>' . substr($nodePromptText, $swapPos);
                }
            }
            return $nodePromptText . ' <span id="req' . $nIDtxt . '" class="rqd">' . $txt . '</span>';
        }
        return '';
    }
    
    public function nodeHasDateRestriction($nodeRow)
    {
        return (in_array($nodeRow->node_type, ['Date', 'Date Picker', 'Date Time']) 
            && $nodeRow->node_opts%31 > 0 
                // Character limit means word count, if enabled
            && $nodeRow->node_char_limit != 0);
    }
    
    public function inputMobileCls($nID)
    {
        if (isset($this->allNodes[$nID]) 
            && $this->allNodes[$nID]->nodeRow->node_opts%2 > 0) {
            return ' fingerTxt';
        }
        return '';
    }
    
    protected function isNodeJustH1($nodePrompt)
    {
        return (substr($nodePrompt, 0, 3) == '<h1' 
            && substr($nodePrompt, strlen($nodePrompt)-5) == '</h1>');
    }
    
    protected function cleanDateVal($dateStr)
    {
        if ($dateStr == '0000-00-00' 
            || $dateStr == '1970-01-01' 
            || trim($dateStr) == '') {
            return '';
        }
        return $dateStr;
    }
    
    protected function printWordCntStuff($nIDtxt, $nodeRow)
    {
        $ret = '';
        if ($nodeRow->node_opts%31 == 0 
            || $nodeRow->node_opts%47 == 0) {
            $ret .= '<div id="currWordCount" class="fL pT15">';
            if ($nodeRow->node_opts%47 == 0) {
                $ret .= 'Word count limit: ' 
                    . intVal($nodeRow->node_char_limit) . '. ';
            }
            if ($nodeRow->node_opts%31 == 0) {
                $ret .= 'Current word count: <div id="wordCnt' 
                    . $nIDtxt . '" class="disIn"></div>';
            }
            $ret .= '</div><div class="fC"></div>';
        }
        return $ret;
    }
    
    protected function formDate($curr)
    {
        list($month, $day, $year) = [ '', '', '' ];
        if (trim($curr->dateStr) != '') {
            $curr->dateTime = $GLOBALS["SL"]->dateToTime($curr->dateStr);
            $month = date("m", $curr->dateTime);
            $day   = date("d", $curr->dateTime);
            $year  = date("Y", $curr->dateTime);
        }
        $startYear = intVal(date("Y"));
        if ($curr->nodeRow->node_char_limit >= 0) {
            $startYear++;
        }
        return view(
            'vendor.survloop.forms.formtree-date', 
            [
                "nID"            => $curr->nID,
                "nIDtxt"         => $curr->nIDtxt,
                "dateStr"        => $curr->dateStr,
                "month"          => $month,
                "day"            => $day,
                "year"           => $year,
                "startYear"      => $startYear,
                "xtraClass"      => $curr->xtraClass,
                "inputMobileCls" => $this->inputMobileCls($curr->nID)
            ]
        )->render();
    }
    
    protected function valListArr($val)
    {
        $val = str_replace(';', '', str_replace(';;', '-=-', $val));
        return explode('-=-', $val);
    }
    
    protected function printValList($val)
    {
        return str_replace(';', '', str_replace(';;', ', ', $val));
    }
    
    protected function printYN($val)
    {
        if ($val == 'Y') {
            return 'Yes';
        }
        if ($val == 'N') {
            return 'No';
        }
        if ($val == '?') {
            return 'Not sure';
        }
        return '';
    }
    
    protected function printMF($val)
    {
        if ($val == 'M') {
            return 'Male';
        }
        if ($val == 'F') {
            return 'Female';
        }
        if ($val == 'O') {
            return 'Other ';
        }
        if ($val == '?') {
            return 'Not sure';
        }
        return '';
    }
    
    /**
     * Provides customization of values reported in detail blocks.
     *
     * @param  int $nID
     * @param  string $val
     * @param  App\Models\SLFields $fldRow
     * @return string
     */
    protected function printValCustom($nID, $val, $fldRow)
    {
        return $val;
    }
    
    public function chkEmail()
    {
        $ret = '';
        if ($GLOBALS["SL"]->REQ->has('email') 
            && trim($GLOBALS["SL"]->REQ->email) != '') {
            $chk = User::where('email', 'LIKE', $GLOBALS["SL"]->REQ->email)
                ->get();
            if ($chk->isNotEmpty()) {
                $ret .= 'found';
            }
        }
        return $ret;
    }
    
    public function limitWordCount($str, $wordLimit)
    {
        $ret = '';
        $words = $GLOBALS["SL"]->mexplode(' ', $str);
        if (sizeof($words) > 0) {
            foreach ($words as $i => $w) {
                if ($i < $wordLimit) {
                    $ret .= ' ' . $w;
                }
            }
        }
        return trim($ret);
    }
    
    public function monthlyCalcPreselections($nID, $nIDtxt = '')
    {
        return [ 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 ];
    }
    
    public function printMonthlyCalculator(
        $nIDtxt = '', 
        $presel = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], 
        $extraJS = '')
    {
        $GLOBALS["SL"]->pageAJAX .= view(
            'vendor.survloop.forms.formtree-monthly-calculator-ajax', 
            [
                "nIDtxt"  => $nIDtxt,
                "extraJS" => $extraJS
            ]
        )->render();
        return view(
            'vendor.survloop.forms.formtree-monthly-calculator', 
            [
                "nIDtxt" => $nIDtxt,
                "presel" => $presel
            ]
        )->render();
    }
    
}