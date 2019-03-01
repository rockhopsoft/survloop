<?php
/**
  * TreeSurvFormVarieties is a mid-level class with functions to print specific node types,
  * and swap out various language.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SLTree;
use SurvLoop\Controllers\UserProfile;

class TreeSurvFormVarieties extends UserProfile
{
    protected function customLabels($nIDtxt = '', $str = '')
    {
        return $str;
    }
    
    protected function swapIDs($nIDtxt = '', $str = '')
    {
        $str = str_replace('[[nID]]', $nIDtxt, $str);
        $str = str_replace('[[coreID]]', $this->coreID, str_replace('[[cID]]', $this->coreID, $str));
        $str = str_replace('[[corePubID]]', $this->getCurrPubID(), $str);
        $str = str_replace('[[DOMAIN]]', $GLOBALS["SL"]->sysOpts["app-url"], $str);
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
    
    protected function swapLabels($nIDtxt = '', $str = '', $itemID = -3, $itemInd = -3)
    {
        if (trim($str) == '') {
            return '';
        }
        $str = $this->sendEmailBlurbsCustom($str);
        $str = $this->customLabels($nIDtxt, $str);
        $str = $GLOBALS["SL"]->swapBlurbs($str);
        $str = $this->swapIDs($nIDtxt, $str);
        if (!isset($this->v["printFullTree"]) || !$this->v["printFullTree"]) {
            if ($itemID > 0 && $itemInd >= 0) {
                if (strpos($str, '[LoopItemLabel]') !== false && isset($GLOBALS["SL"]->closestLoop["loop"])) {
                    $label = $this->getLoopItemLabel($GLOBALS["SL"]->closestLoop["loop"], 
                        $this->sessData->getRowById($GLOBALS["SL"]->closestLoop["obj"]->DataLoopTable, $itemID), $itemInd);
                    $str = str_replace('[LoopItemLabel]', '<span class="slBlueDark">' . $label . '</span>', $str);
                }
                $cnt = 1+$itemInd;
                if (isset($GLOBALS["SL"]->closestLoop["loop"])) {
                    $rows = $this->sessData->getLoopRows($GLOBALS["SL"]->closestLoop["loop"]);
                    if ($rows && sizeof($rows) > 0) {
                        foreach ($rows as $j => $rec) {
                            if ($rec->getKey() == $itemID) {
                                $cnt = 1+$j;
                            }
                        }
                    }
                }
                $str = str_replace('[LoopItemCnt]', '<span class="slBlueDark">' . $cnt . '</span>', $str);
            }
            $labelPos = strpos($str, '[LoopItemLabel:');
            if (($itemID <= 0 || $itemInd < 0) && $labelPos !== false) {
                $strPre = substr($str, 0, $labelPos);
                $loopName = substr($str, $labelPos+15);
                $labelEndPos = strpos($loopName, ']');
                $strPost = substr($loopName, $labelEndPos+1);
                $loopName = substr($loopName, 0, $labelEndPos);
                $loopRows = $this->sessData->getLoopRows($loopName);
                if (sizeof($loopRows) == 1) {
                    $label = $this->getLoopItemLabel($loopName, $loopRows[0], $itemInd);
                    $str = $strPre . '<span class="slBlueDark">' . $label . '</span>' . $strPost;
                }
            }
        }
        $str = $this->customCleanLabel($str, $nIDtxt);
        return $this->cleanLabel($str);
    }
    
    protected function customCleanLabel($str = '', $nIDtxt = '')
    {
        return $str;
    }
    
    protected function cleanLabel($str = '')
    {
        $span = '<span class="slBlueDark">';
        $str = str_replace($span . 'You</span>', $span . 'you</span>', $str);
        $str = str_replace($span . 'you</span>&#39;s', $span . 'your</span>', $str);
        $str = str_replace('Was <span class="slBlueDark">you</span>', 'Were <span class="slBlueDark">you</span>', $str);
        $str = str_replace('was <span class="slBlueDark">you</span>', 'were <span class="slBlueDark">you</span>', $str);
        $str = str_replace($span . 'you</span>\'s', $span . 'your</span>', $str);
        $str = str_replace($span . 'you</span> was', $span . 'you</span> were', $str);
        $str = str_replace(', [LoopItemLabel]:', ':', str_replace(', <span class="slBlueDark">[LoopItemLabel]</span>:',
            ':', $str));
        $str = str_replace(', <span class="slBlueDark"></span>:', ':', 
            str_replace(', <span class="slBlueDark">&nbsp;</span>:', ':', $str));
        $str = trim(str_replace(', :', ':', $str));
        if (strpos(strip_tags($str), 'you') === 0) $str = str_replace($span . 'you', $span . 'You', $str);
        
        $str = str_replace('you&#39;s', 'your', str_replace('You&#39;s', 'Your', $str));
        $str = str_replace('Was you', 'Were you', str_replace('Was You', 'Were you', $str));
        $str = str_replace('was you', 'were you', str_replace('was You', 'were you', $str));
        $str = str_replace('you\'s', 'your', str_replace('You\'s', 'Your', $str));
        $str = str_replace('you was', 'you were', str_replace('You was', 'You were', $str));
        $str = str_replace(', [LoopItemLabel]:', ':', str_replace(', <span class="slBlueDark">[LoopItemLabel]:',
            ':', $str));
        $str = str_replace(', <span class="slBlueDark">:', ':', 
            str_replace(', <span class="slBlueDark">&nbsp;:', ':', $str));
        $str = trim(str_replace(', :', ':', $str));
        if (strpos(strip_tags($str), 'you') === 0) $str = str_replace('you', 'You', $str);
        return $str;
    }
    
    protected function printWidget($nID, $nIDtxt, $curr)
    {
        $ret = '';
        $blockWidget = false;
        if ($curr->nodeType == 'Incomplete Sess Check' && isset($this->v["profileUser"]) 
            && isset($this->v["profileUser"]->id)) {
            if (!isset($this->v["uID"]) || $this->v["uID"] != $this->v["profileUser"]->id) {
                $blockWidget = true;
            }
        }
        if ($blockWidget) {
            // don't show widget
        } elseif ($curr->nodeType == 'Member Profile Basics') {
            $ret .= $this->showProfileBasics();
        } elseif ($curr->nodeType == 'MFA Dialogue') {
            $ret .= ((isset($this->v["mfaMsg"])) ? $this->v["mfaMsg"] : '');
        } elseif (intVal($curr->nodeRow->NodeResponseSet) > 0) {
            $widgetTreeID = $curr->nodeRow->NodeResponseSet;
            $widgetLimit  = intVal($curr->nodeRow->NodeCharLimit);
            $this->initSearcher();
            if ($curr->nodeType == 'Search') {
                $ret .= $this->searcher->printSearchBar('', $widgetTreeID, trim($curr->nodeRow->NodePromptText), 
                    trim($curr->nodeRow->NodePromptAfter), $nID, 0);
            } else { // this widget loads via ajax
                $spinner = (($curr->nodeType != 'Incomplete Sess Check') ? $GLOBALS["SL"]->spinner() : '');
                $loadURL = '/records-full/' . $widgetTreeID;
                $search = (($GLOBALS["SL"]->REQ->has('s')) ? trim($GLOBALS["SL"]->REQ->get('s')) : '');
                if (isset($this->v["profileUser"]) && isset($this->v["profileUser"]->id)) {
                    $this->searcher->advSearchUrlSffx .= '&u=' . $this->v["profileUser"]->id;
                } elseif (isset($curr->nodeRow->NodeDataBranch) && trim($curr->nodeRow->NodeDataBranch) == 'users') {
                    $this->searcher->advSearchUrlSffx .= '&mine=1';
                }
                if (in_array($curr->nodeType, ['Record Full', 'Record Full Public'])) {
                    $cid = (($GLOBALS["SL"]->REQ->has('i')) ? intVal($GLOBALS["SL"]->REQ->get('i')) 
                        : (($this->treeID == $widgetTreeID && $this->coreID > 0) ? $this->coreID : -3));
                    //$loadURL .= '?i=' . $cid . (($search != '') ? '&s=' . $search : '');
                    $wTree = SLTree::find($widgetTreeID);
                    if ($cid > 0 && $wTree) {
                        $loadURL = '/' . $wTree->TreeSlug . '/read-' . $cid . '/full?ajax=1&wdg=1'
                            . (($curr->nodeType == 'Record Full Public') ? '&publicView=1' : '');
                        $spinner = '<br /><br /><center>' . $spinner . '</center><br />';
                    }
                } elseif ($curr->nodeType == 'Search Featured') {
                    $this->initSearcher();
                    $ret .= $this->searcher->searchResultsFeatured($search, $widgetTreeID);
                } elseif ($curr->nodeType == 'Search Results') {
                    $this->initSearcher();
                    $this->searcher->getSearchFilts();
                    $loadURL = '/search-results/' . $widgetTreeID . '?s=' . urlencode($this->searcher->searchTxt) 
                        . $this->searcher->searchFiltsURL() . $this->searcher->advSearchUrlSffx;
                    $curr->nodeRow->NodePromptText = $GLOBALS["SL"]->extractJava(str_replace('[[search]]', $search, 
                        $curr->nodeRow->NodePromptText), $nID);
                    $curr->nodeRow->NodePromptAfter = $GLOBALS["SL"]->extractJava(str_replace('[[search]]', $search, 
                        $curr->nodeRow->NodePromptAfter), $nID);
                } elseif ($curr->nodeType == 'Record Previews') {
                    $loadURL = '/record-prevs/' . $widgetTreeID . '?limit=' . $widgetLimit;
                } elseif ($curr->nodeType == 'Incomplete Sess Check') {
                    $loadURL = '/record-check/' . $widgetTreeID;
                } elseif ($curr->isGraph()) {
                    $GLOBALS["SL"]->x["needsCharts"] = true;
                    $loadURL = '/record-graph/' . str_replace(' ', '-', strtolower($curr->nodeType)) . '/' 
                        . $widgetTreeID . '/' . $curr->nodeID;
                    $GLOBALS["SL"]->pageAJAX .= 'addGraph("' . $nIDtxt . '", "' . $loadURL.'");'."\n";
                } elseif ($curr->nodeType == 'Widget Custom') {
                    $loadURL = '/widget-custom/' . $widgetTreeID . '/' . $nID . '?txt=' 
                        . str_replace($nID, '', $nIDtxt) . $this->sessData->getDataBranchUrl();
                    $loadURL .= $this->widgetCustomLoadUrl($nID, $nIDtxt, $curr);
                }
                $ret .= ((trim($curr->nodeRow->NodePromptText) != '') ? '<div>' 
                    . $GLOBALS["SL"]->extractJava($curr->nodeRow->NodePromptText, $nID) 
                    . '</div>' : '') . '<div id="n' . $nID . 'ajaxLoad" class="w100">' . $spinner . '</div>'
                    . ((trim($curr->nodeRow->NodePromptAfter) != '') ? '<div>' 
                    . $GLOBALS["SL"]->extractJava($curr->nodeRow->NodePromptAfter, $nID) . '</div>' : '');
                $GLOBALS["SL"]->pageAJAX .= '$("#n' . $nID . 'ajaxLoad").load("' . $loadURL . '");' . "\n";
            }
        }
        return $ret;
    }
    
    // customizable function for what URL is used to load the widget's div
    protected function widgetCustomLoadUrl($nID, $nIDtxt, $curr)
    {
        // if ($nID == ...
        return '';
    }
    
    // customizable function for what content is loaded in the widget's div 
    public function widgetCust(Request $request, $nID = -3)
    {
        $this->survLoopInit($request, '');
        $this->loadAllSessData();
        //$this->loadTree();
        $txt = (($request->has('txt')) ? trim($request->get('txt')) : '');
        $nIDtxt = $nID . $txt;
        $branches = (($request->has('branch')) ? trim($request->get('branch')) : '');
        $this->sessData->loadDataBranchFromUrl($branches);
        return $this->widgetCustomRun($nID, $nIDtxt);
    }
    
    // customizable function for what content is loaded in the widget's div 
    public function widgetCustomRun($nID = -3, $nIDtxt)
    {
        // if ($nID == ...
        return '';
    }
    
    protected function getNodeFormFldBasic($nID = -3, $curr = null)
    {
        if ($nID <= 0) {
            return null;
        }
        if (!$curr) {
            if (!isset($this->allNodes[$nID])) return null;
            $curr = $this->allNodes[$nID];
        }
        if ($curr->nodeType == 'Big Button') return null;
        $nIDtxt = $nID . $GLOBALS["SL"]->getCycSffx();
        $newVal = (($GLOBALS["SL"]->REQ->has('n' . $nIDtxt . 'fld')) 
            ? $GLOBALS["SL"]->REQ->input('n' . $nIDtxt . 'fld') : null);
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
        if (!$currNode || !isset($currNode->nodeRow->NodeOpts)) {
            return '';
        }
        $txt = '*required';
        if ($this->nodeHasDateRestriction($currNode->nodeRow)) {
            if ($currNode->nodeRow->NodeCharLimit < 0) $txt = '*past date required';
            elseif ($currNode->nodeRow->NodeCharLimit > 0) $txt = '*future date required';
        }
        if ($currNode->nodeRow->NodeOpts%13 == 0) {
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
            }
            else {
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
                    return substr($nodePromptText, 0, $swapPos) . ' <small id="req' . $nIDtxt . '" class="rqd">' 
                        . $txt . '</small>' . substr($nodePromptText, $swapPos);
                }
            }
            return $nodePromptText . ' <span id="req' . $nIDtxt . '" class="rqd">' . $txt . '</span>';
        }
        return '';
    }
    
    public function nodeHasDateRestriction($nodeRow)
    {
        return (in_array($nodeRow->NodeType, ['Date', 'Date Picker', 'Date Time']) 
            && $nodeRow->NodeOpts%31 > 0 // Character limit means word count, if enabled
            && $nodeRow->NodeCharLimit != 0);
    }
    
    public function inputMobileCls($nID)
    {
        return (isset($this->allNodes[$nID]) && $this->allNodes[$nID]->nodeRow->NodeOpts%2 > 0) ? ' fingerTxt' : '';
    }
    
    protected function isNodeJustH1($nodePrompt)
    {
        return (substr($nodePrompt, 0, 3) == '<h1' && substr($nodePrompt, strlen($nodePrompt)-5) == '</h1>');
    }
    
    protected function cleanDateVal($dateStr)
    {
        if ($dateStr == '0000-00-00' || $dateStr == '1970-01-01' || trim($dateStr) == '') {
            return '';
        }
        return $dateStr;
    }
    
    protected function printWordCntStuff($nIDtxt, $nodeRow)
    {
        $ret = '';
        if ($nodeRow->NodeOpts%31 == 0 || $nodeRow->NodeOpts%47 == 0) {
            $ret .= '<div class="fL pT5">'
                . (($nodeRow->NodeOpts%47 == 0) ? 'Word count limit: ' . intVal($nodeRow->NodeCharLimit) . '. ' : '')
                . (($nodeRow->NodeOpts%31 == 0) 
                    ? 'Current word count: <div id="wordCnt' . $nIDtxt . '" class="disIn"></div>.' : '')
            . '</div><div class="fC"></div>';
        }
        return $ret;
    }
    
    protected function formDate($nID, $dateStr = '00/00/0000', $xtraClass = '')
    {
        list($month, $day, $year) = ['', '', ''];
        if (trim($dateStr) != '') {
            list($month, $day, $year) = explode('/', $dateStr);
            if (intVal($month) == 0 || intVal($day) == 0 || intVal($year) == 0) {
                list($month, $day, $year) = ['', '', ''];
            }
        }
        return view('vendor.survloop.formtree-form-date', [
            "nID"            => $nID,
            "dateStr"        => $dateStr,
            "month"          => $month,
            "day"            => $day,
            "year"           => $year,
            "xtraClass"      => $xtraClass,
            "inputMobileCls" => $this->inputMobileCls($nID)
            ])->render();
    }
    
    protected function formTime($nID, $timeStr = '00:00:00', $xtraClass = '')
    {
        if (strlen($timeStr) == 19) {
            $timeStr = substr($timeStr, 11);
        }
        $timeArr = explode(':', $timeStr); 
        foreach ($timeArr as $i => $t) {
            $timeArr[$i] = intVal($timeArr[$i]);
        }
        if (!isset($timeArr[0])) {
            $timeArr[0] = 0;
            if (!isset($timeArr[1])) {
                $timeArr[1] = 0;
            }
        }
        $timeArr[3] = 'AM';
        if ($timeArr[0] > 11) {
            $timeArr[3] = 'PM'; 
            if ($timeArr[0] > 12) {
                $timeArr[0] = $timeArr[0]-12;
            }
        }
        if ($timeArr[0] == 0 && (!isset($timeArr[1]) || $timeArr[1] == 0)) {
            $timeArr[0] = -1; 
            $timeArr[1] = 0; 
        }
        return view('vendor.survloop.formtree-form-time', [
            "nID"            => $nID,
            "timeArr"        => $timeArr,
            "xtraClass"      => $xtraClass,
            "inputMobileCls" => $this->inputMobileCls($nID)
            ])->render();
    }
    
    protected function postFormTimeStr($nID)
    {
        $nIDtxt = $nID . $GLOBALS["SL"]->getCycSffx();
        if (!$GLOBALS["SL"]->REQ->has('n' . $nIDtxt . 'fldHr') 
            || trim($GLOBALS["SL"]->REQ->input('n' . $nIDtxt . 'fldHr')) == '-1') {
            return null;
        }
        $hr = intVal($GLOBALS["SL"]->REQ->input('n' . $nIDtxt . 'fldHr'));
        if ($hr == -1) {
            return null;
        }
        if ($GLOBALS["SL"]->REQ->input('n' . $nIDtxt . 'fldPM') == 'PM' && $hr < 12) {
            $hr += 12;
        }
        $min = intVal($GLOBALS["SL"]->REQ->input('n' . $nIDtxt . 'fldMin'));
        return ((intVal($hr) < 10) ? '0' : '') . $hr . ':' . ((intVal($min) < 10) ? '0' : '') . $min . ':00';
    }
    
    
    protected function valListArr($val)
    {
        return explode('-=-', str_replace(';', '', str_replace(';;', '-=-', $val)));
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
        if ($val == '?') {
            return 'Not sure';
        }
        return '';
    }
    
    protected function printValCustom($nID, $val)
    {
        return $val;
    }
    
    public function chkEmail()
    {
        $ret = '';
        if ($GLOBALS["SL"]->REQ->has('email') && trim($GLOBALS["SL"]->REQ->email) != '') {
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
    
}