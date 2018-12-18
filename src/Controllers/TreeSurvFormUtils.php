<?php
/**
  * TreeSurvFormUtils is a mid-level class using a standard branching tree, which provides
  * lots of smaller functions used by the form generation processes (in TreeSurvForm).
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use Storage;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLNodeResponses;
use App\Models\SLContact;
use App\Models\SLEmails;
use App\Models\SLTokens;
use App\Models\SLUsersRoles;
use SurvLoop\Controllers\TreeSurvInput;

class TreeSurvFormUtils extends TreeSurvInput
{
    protected function getNodeCurrSessData($nID)
    {
        $this->allNodes[$nID]->fillNodeRow();
        list($tbl, $fld) = $this->allNodes[$nID]->getTblFld();
        return $this->sessData->currSessData($nID, $tbl, $fld);
    }
    
    protected function isPromptNotesSpecial($nodePromptNotes = '')
    {
        return (substr($nodePromptNotes, 0, 1) == '[' 
            && substr($nodePromptNotes, strlen($nodePromptNotes)-1) == ']');
    }
    
    protected function printSpecial($nID, $promptNotesSpecial = '', $currNodeSessData = '')
    {
        return '';
    }
    
    protected function customNodePrintButton($nID = -3, $nodeRow = [])
    {
        return '';
    }
    
    protected function customResponses($nID, $curr) { return $curr; }
    
    protected function skipFormForPreview($nID)
    {
        return ($GLOBALS["SL"]->REQ->has('isPreview') && $GLOBALS["SL"]->REQ->has('ajax'));
    }
    
    protected function loadAncestXtnd($nID)
    {
        if (isset($this->v["ancestors"]) && is_array($this->v["ancestors"]) && sizeof($this->v["ancestors"]) > 0) {
            for ($i = (sizeof($this->v["ancestors"])-1); $i >= 0; $i--) {
                $parent = $this->v["ancestors"][$i];
                if (isset($this->allNodes[$parent]) && $this->allNodes[$parent]->isDataManip()) {
                    $this->loadManipBranch($parent);
                }
            }
        }
        return true;
    }
    
    protected function customLabels($nIDtxt = '', $str = '') { return $str; }
    
    protected function swapIDs($nIDtxt = '', $str = '')
    {
        $str = str_replace('[[nID]]', $nIDtxt, $str);
        $str = str_replace('[[coreID]]', $this->coreID, str_replace('[[cID]]', $this->coreID, $str));
        $str = str_replace('[[DOMAIN]]', $GLOBALS["SL"]->sysOpts["app-url"], $str);
        return $str;
    }
    
    protected function swapIDsSEO($extraOpts = [])
    {
        if (sizeof($extraOpts) > 0) {
            foreach ($extraOpts as $key => $val) $extraOpts[$key] = $this->swapIDs('', $val);
        }
        return $this->swapIDsSEOCustom($extraOpts);
    }
    
    protected function swapIDsSEOCustom($extraOpts = [])
    {
        return $extraOpts;
    }
    
    protected function swapLabels($nIDtxt = '', $str = '', $itemID = -3, $itemInd = -3)
    {
        if (trim($str) == '') return '';
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
                            if ($rec->getKey() == $itemID) $cnt = 1+$j;
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
    
    protected function customCleanLabel($str = '', $nIDtxt = '') { return $str; }
    
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
            if ($curr->nodeType == 'Search') {
                $this->initSearcher();
                $ret .= $this->searcher->printSearchBar('', $widgetTreeID, trim($curr->nodeRow->NodePromptText), 
                    trim($curr->nodeRow->NodePromptAfter), $nID, 0);
            } else { // this widget loads via ajax
                $spinner = (($curr->nodeType != 'Incomplete Sess Check') 
                    ? ((isset($GLOBALS["SL"]->sysOpts["spinner-code"])) 
                        ? $GLOBALS["SL"]->sysOpts["spinner-code"] : '') : '');
                $loadURL = '/records-full/' . $widgetTreeID;
                $search = (($GLOBALS["SL"]->REQ->has('s')) ? trim($GLOBALS["SL"]->REQ->get('s')) : '');
                if (isset($this->v["profileUser"]) && isset($this->v["profileUser"]->id)) {
                    $this->advSearchUrlSffx .= '&u=' . $this->v["profileUser"]->id;
                } elseif (isset($curr->nodeRow->NodeDataBranch) && trim($curr->nodeRow->NodeDataBranch) == 'users') {
                    $this->advSearchUrlSffx .= '&mine=1';
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
                    $curr->nodeRow->NodePromptText = $this->extractJava(str_replace('[[search]]', $search, 
                        $curr->nodeRow->NodePromptText), $nID);
                    $curr->nodeRow->NodePromptAfter = $this->extractJava(str_replace('[[search]]', $search, 
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
                    . $this->extractJava($curr->nodeRow->NodePromptText, $nID) 
                    . '</div>' : '') . '<div id="n' . $nID . 'ajaxLoad" class="w100">' . $spinner . '</div>'
                    . ((trim($curr->nodeRow->NodePromptAfter) != '') ? '<div>' 
                    . $this->extractJava($curr->nodeRow->NodePromptAfter, $nID) . '</div>' : '');
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
    
    protected function shouldPrintHalfGap($curr)
    {
        return (($GLOBALS["SL"]->treeRow->TreeType != 'Page' || $GLOBALS["SL"]->treeRow->TreeOpts%19 == 0 
            || $GLOBALS["SL"]->treeRow->TreeOpts%53 == 0)
            && !$curr->isPage() && !$curr->isLoopRoot() && !$curr->isLoopCycle() && !$curr->isDataManip()
            && !$curr->isLayout() && trim($GLOBALS["SL"]->currCyc["res"][1]) == '' 
            && !$this->hasSpreadsheetParent($curr->nodeID));
    }
    
    protected function isCurrDataSelected($currNodeSessData, $value, $node)
    {
        $selected = false;
        $resValCyc = $value . trim($GLOBALS["SL"]->currCyc["cyc"][1]);
        $resValCyc2 = trim($GLOBALS["SL"]->currCyc["cyc"][1]) . $value;
        if (is_array($currNodeSessData)) {
            $selected = (in_array($value, $currNodeSessData) || in_array($resValCyc, $currNodeSessData) 
                || in_array($resValCyc2, $currNodeSessData));
        } else {
            if ($node->nodeType == 'Checkbox' || $node->isDropdownTagger()) {
                $selected = (strpos(';' . $currNodeSessData . ';', ';' . $value . ';') !== false 
                    || strpos(';' . $currNodeSessData . ';', ';' . $resValCyc . ';') !== false
                    || strpos(';' . $currNodeSessData . ';', ';' . $resValCyc2 . ';') !== false);
            } else {
                $selected = ($currNodeSessData == trim($value) || $currNodeSessData == trim($resValCyc) 
                    || $currNodeSessData == trim($resValCyc2));
            }
        }
        return $selected;
    }
    
    protected function getNodeFormFldBasic($nID = -3, $curr = null)
    {
        if ($nID <= 0) return null;
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
    
    public function addPromptTextRequired($currNode = NULL, $nodePromptText = '')
    {
        if (!$currNode || !isset($currNode->nodeRow->NodeOpts)) return '';
        $txt = '*required';
        if ($this->nodeHasDateRestriction($currNode->nodeRow)) {
            if ($currNode->nodeRow->NodeCharLimit < 0) $txt = '*past date required';
            elseif ($currNode->nodeRow->NodeCharLimit > 0) $txt = '*future date required';
        }
        if ($currNode->nodeRow->NodeOpts%13 == 0) {
            return $nodePromptText . '<p class="rqd">' . $txt . '</p>';
        } else {
            $swapPos = -1;
            $lastP = strrpos($nodePromptText, '</p>');
            $lastDiv = strrpos($nodePromptText, '</div>');
            if ($lastP > 0)       $swapPos = $lastP;
            elseif ($lastDiv > 0) $swapPos = $lastDiv;
            if ($swapPos > 0) {
                return substr($nodePromptText, 0, $swapPos) . ' <span class="rqd">' . $txt . '</span>' 
                    . substr($nodePromptText, $swapPos);
            }
            else {
                $lastH3 = strrpos($nodePromptText, '</h3>');
                $lastH2 = strrpos($nodePromptText, '</h2>');
                $lastH1 = strrpos($nodePromptText, '</h1>');
                if ($lastH3 > 0)  $swapPos = $lastH3;
                elseif ($lastH2 > 0)  $swapPos = $lastH2;
                elseif ($lastH1 > 0)  $swapPos = $lastH1;
                if ($swapPos > 0) {
                    return substr($nodePromptText, 0, $swapPos) 
                        . ' <small class="rqd">' . $txt . '</small>' 
                        . substr($nodePromptText, $swapPos);
                }
            }
            return $nodePromptText . ' <span class="rqd">' . $txt . '</span>';
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
    
    protected function checkResponses($curr, $fldForeignTbl)
    {
        if (isset($curr->responseSet) && strpos($curr->responseSet, 'LoopItems::') !== false) {
            $loop = str_replace('LoopItems::', '', $curr->responseSet);
            $currLoopItems = $this->sessData->getLoopRows($loop);
            if (sizeof($currLoopItems) > 0) {
                foreach ($currLoopItems as $i => $row) {
                    $curr->responses[$i] = new SLNodeResponses;
                    $curr->responses[$i]->NodeResValue = $row->getKey();
                    $curr->responses[$i]->NodeResEng = $this->getLoopItemLabel($loop, $row, $i);
                }
            }
        } elseif (isset($curr->responseSet) && strpos($curr->responseSet, 'Table::') !== false) {
            $tbl = str_replace('Table::', '', $curr->responseSet);
            if (isset($this->sessData->dataSets[$tbl]) && sizeof($this->sessData->dataSets[$tbl]) > 0) {
                foreach ($this->sessData->dataSets[$tbl] as $i => $row) {
                    $recName = $this->getTableRecLabel($tbl, $row, $i);
                    if (trim($recName) != '') {
                        $curr->responses[$i] = new SLNodeResponses;
                        $curr->responses[$i]->NodeResValue = $row->getKey();
                        $curr->responses[$i]->NodeResEng = $recName;
                    }
                }
            }
        } elseif (isset($curr->responseSet) && $curr->responseSet == 'Definition::--STATES--') {
            $GLOBALS["SL"]->loadStates();
            $curr->responses = $GLOBALS["SL"]->states->stateResponses();
        } elseif (empty($curr->responses) && trim($fldForeignTbl) != '' 
            && isset($this->sessData->dataSets[$fldForeignTbl]) 
            && sizeof($this->sessData->dataSets[$fldForeignTbl]) > 0) {
            foreach ($this->sessData->dataSets[$fldForeignTbl] as $i => $row) {
                $loop = ((isset($GLOBALS["SL"]->tblLoops[$fldForeignTbl])) 
                    ? $GLOBALS["SL"]->tblLoops[$fldForeignTbl] : $fldForeignTbl);
                // what about tables with multiple loops??
                $curr->responses[$i] = new SLNodeResponses;
                $curr->responses[$i]->NodeResValue = $row->getKey();
                $curr->responses[$i]->NodeResEng = $this->getLoopItemLabel($loop, $row, $i);
            }
        }
        return $curr;
    }
    
    protected function getTableRecLabel($tbl, $rec = [], $ind = -3)
    {
        $name = $this->getTableRecLabelCustom($tbl, $rec, $ind);
        if (trim($name) != '') return $name;
        if (file_exists(base_path('resources/views/vendor/' . strtolower($GLOBALS["SL"]->sysOpts["cust-abbr"]) 
            . '/nodes/tbl-rec-label-' . strtolower($tbl) . '.blade.php'))) {
            $name = trim(view('vendor.' . strtolower($GLOBALS["SL"]->sysOpts["cust-abbr"]) . '.nodes.tbl-rec-label-' 
                . strtolower($tbl), [ "rec" => $rec ])->render());
        } else {
            $name = $GLOBALS["SL"]->tblEng[$GLOBALS["SL"]->tblI[$tbl]] . (($ind >= 0) ? ' #' . (1+$ind) : '');
        }
        return $name;
    }
    
    protected function getTableRecLabelCustom($tbl, $rec = [], $ind = -3)
    {
        return '';
    }
    
    protected function getLoopItemLabel($loop, $itemRow = [], $itemInd = -3)
    {
        $name = $this->getLoopItemLabelCustom($loop, $itemRow, $itemInd);
        if (trim($name) != '') return $name;
        if (isset($GLOBALS["SL"]->dataLoops[$loop]) && $itemInd >= 0) {
            return $GLOBALS["SL"]->dataLoops[$loop]->DataLoopSingular . ' #' . (1+$itemInd);
        }
        return '';
    }
    
    protected function getLoopItemLabelCustom($loop, $itemRow = [], $itemInd = -3)
    {
        return '';
    }
    
    protected function getLoopItemCntLabelCustom($loop, $itemInd = -3)
    {
        return -3;
    }
    
    protected function printSetLoopNav($nID, $loopName)
    {
        $this->settingTheLoop($loopName);
        if ($this->allNodes[$nID]->isStepLoop()) {
            $this->sessData->getLoopDoneItems($loopName);
            if ($this->sessData->loopItemsNextID > 0) {
                $this->loopItemsCustBtn = '<a href="javascript:;" class="fR btn btn-lg btn-primary" '
                    . 'id="nFormNextStepItem"><i class="fa fa-arrow-circle-o-right"></i> Next ' 
                    . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular . ' Details</a>';
                $GLOBALS["SL"]->pageJAVA .= 'loopItemsNextID = ' . $this->sessData->loopItemsNextID . '; ';
            }
        }
        
        $labelFirstLet = substr(strtolower($GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular), 0, 1);
        $limitTxt = '';
        if ($GLOBALS["SL"]->closestLoop["obj"]->DataLoopMaxLimit > 0 
            && isset($this->sessData->loopItemIDs[$loopName])
            && sizeof($this->sessData->loopItemIDs[$loopName]) 
                > $GLOBALS["SL"]->closestLoop["obj"]->DataLoopWarnLimit) {
            $limitTxt .= '<div class="gry6 pT20 fPerc133">Limit of ' 
                . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopMaxLimit . ' '
                . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopPlural . '</div>';
        }
        $ret = '<div class="nPrompt"><input type="hidden" id="isLoopNav" name="loopNavRoot" value="'
            . intVal($GLOBALS['SL']->closestLoop['obj']->DataLoopRoot) . '">' 
            . (($this->allNodes[$nID]->isStepLoop()) ? '<div id="isStepLoop"></div>' : '');
        if (!$this->allNodes[$nID]->isStepLoop() && empty($this->sessData->loopItemIDs[$loopName])) {
            $ret .= '<h3 class="slGrey"><i>No ' . strtolower($GLOBALS["SL"]->closestLoop["obj"]->DataLoopPlural) 
                . ' added yet.</i></h3>' . "\n";
        } else {
            $ret .= '<div class="p10"></div>';
        }
        if (sizeof($this->sessData->loopItemIDs[$loopName]) > 0) {
            if (!$this->allNodes[$nID]->isStepLoop() && sizeof($this->sessData->loopItemIDs[$loopName]) > 10) {
                $ret .= '<div class="mTn10 mB20">' . $this->printSetLoopNavAddBtn($nID, $loopName, $labelFirstLet) 
                    . '</div>';
            }
            foreach ($this->sessData->loopItemIDs[$loopName] as $setIndex => $loopItem) {
                $tbl = $GLOBALS["SL"]->dataLoops[$loopName]->DataLoopTable;
                $ret .= $this->printSetLoopNavRow($nID, $this->sessData->getRowById($tbl, $loopItem), $setIndex);
            }
        }
        if (!$this->allNodes[$nID]->isStepLoop()) {
            $ret .= $this->printSetLoopNavAddBtn($nID, $loopName, $labelFirstLet)
                . $limitTxt . '<div class="p20"></div>' . "\n";
            $GLOBALS["SL"]->pageJAVA .= 'currItemCnt = ' . sizeof($this->sessData->loopItemIDs[$loopName]) . '; '
                . 'maxItemCnt = ' . $GLOBALS['SL']->closestLoop["obj"]->DataLoopMaxLimit . '; ';
        }
        /* if (!$this->allNodes[$nID]->isStepLoop()) {
            $this->nextBtnOverride = 'Done Adding ' . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopPlural;
        } elseif (sizeof($this->sessData->loopItemIDs[$loopName]) == sizeof($this->sessData->loopItemIDsDone)) {
            $this->nextBtnOverride = 'Done With ' . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopPlural;
        } */
        $ret .= '</div>';
        return $ret;
    }
    
    protected function printSetLoopNavAddBtn($nID, $loopName, $labelFirstLet)
    {
        return '<button type="button" id="nFormAdd" class="btn btn-lg btn-secondary mT20 w100 fPerc133 '
            . (($GLOBALS["SL"]->closestLoop["obj"]->DataLoopMaxLimit == 0 || 
                sizeof($this->sessData->loopItemIDs[$loopName]) < $GLOBALS["SL"]->closestLoop["obj"]->DataLoopMaxLimit) 
                ? 'disBlo' : 'disNon')
            . '"><i class="fa fa-plus-circle"></i> Add ' . ((empty($this->sessData->loopItemIDs[$loopName])) 
                ? 'a'.((in_array($labelFirstLet, array('a', 'e', 'i', 'o', 'u'))) ? 'n' : '') : 'another') . ' ' 
            . strtolower($GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular) . '</button>' ;
    }
    
    protected function printSetLoopNavRowCustom($nID, $loopItem, $setIndex)
    {
        return '';
    }
    
    protected function printSetLoopNavRow($nID, $loopItem, $setIndex)
    {
        $ret = $this->printSetLoopNavRowCustom($nID, $loopItem, $setIndex);
        if ($ret != '') return $ret;
        $canEdit = true;
        $itemLabel = $this->getLoopItemLabel($GLOBALS["SL"]->closestLoop["loop"], $loopItem, $setIndex);
        if (strtolower(strip_tags($itemLabel)) == 'you') {
            //$itemLabel = 'You (' . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular . ' #' . (1+$setIndex) . ')';
            $canEdit = false;
        } /* elseif ($itemLabel != $GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular . ' #' . (1+$setIndex)) {
            $itemLabel = $itemLabel . ' (' . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular 
                . ' #' . (1+$setIndex) . ')';
        } */
        $ico = '';
        if ($this->allNodes[$nID]->isStepLoop()) {
            if ($this->sessData->loopItemsNextID > 0 && $this->sessData->loopItemsNextID == $loopItem->getKey()) {
                $ico = '<i class="fa fa-arrow-circle-o-right"></i>';
            } elseif (in_array($loopItem->getKey(), $this->sessData->loopItemIDsDone)) {
                $ico = '<i class="fa fa-check"></i>';
            } else {
                $ico = '<i class="fa fa-check gryA opac10"></i>';
            }
        }
        return view('vendor.survloop.formtree-looproot-row', [
            "nID"            => $nID,
            "setIndex"       => $setIndex,
            "itemID"         => $loopItem->getKey(),
            "itemLabel"      => $itemLabel,
            "canEdit"        => $canEdit,
            "ico"            => $ico, 
            "node"           => $this->allNodes[$nID]
        ])->render();
    }
    
    protected function isNodeJustH1($nodePrompt)
    {
        return (substr($nodePrompt, 0, 3) == '<h1' && substr($nodePrompt, strlen($nodePrompt)-5) == '</h1>');
    }
    
    protected function cleanDateVal($dateStr)
    {
        if ($dateStr == '0000-00-00' || $dateStr == '1970-01-01' || trim($dateStr) == '') return '';
        return $dateStr;
    }
    
    protected function printWordCntStuff($nIDtxt, $nodeRow)
    {
        $ret = '';
        if ($nodeRow->NodeOpts%31 == 0 || $nodeRow->NodeOpts%47 == 0) {
            $ret .= '<div class="fL slGrey f12 pT5">'
                . (($nodeRow->NodeOpts%47 == 0) 
                    ? 'Word count limit: ' . intVal($nodeRow->NodeCharLimit) . '. ' : '')
                . (($nodeRow->NodeOpts%31 == 0) 
                    ? 'Current word count: <div id="wordCnt' . $nIDtxt . '" class="disIn"></div>.' : '')
            . '</div><div class="fC"></div>';
        }
        return $ret;
    }
    
    protected function formDate($nID, $dateStr = '00/00/0000', $xtraClass = '')
    {
        list($month, $day, $year) = array('', '', '');
        if (trim($dateStr) != '') {
            list($month, $day, $year) = explode('/', $dateStr);
            if (intVal($month) == 0 || intVal($day) == 0 || intVal($year) == 0) {
                list($month, $day, $year) = array('', '', '');
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
        if (strlen($timeStr) == 19) $timeStr = substr($timeStr, 11);
        $timeArr = explode(':', $timeStr); 
        foreach ($timeArr as $i => $t) $timeArr[$i] = intVal($timeArr[$i]);
        if (!isset($timeArr[0])) $timeArr[0] = 0; if (!isset($timeArr[1])) $timeArr[1] = 0;
        $timeArr[3] = 'AM';
        if ($timeArr[0] > 11) {
            $timeArr[3] = 'PM'; 
            if ($timeArr[0] > 12) $timeArr[0] = $timeArr[0]-12;
        }
        if ($timeArr[0] == 0 && $timeArr[1] == 0) {
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
        if ($hr == -1) return null;
        if ($GLOBALS["SL"]->REQ->input('n' . $nIDtxt . 'fldPM') == 'PM' && $hr < 12) $hr += 12;
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
        if ($val == 'Y') return 'Yes';
        if ($val == 'N') return 'No';
        if ($val == '?') return 'Not sure';
    }
    
    protected function printMF($val)
    {
        if ($val == 'M') return 'Male';
        if ($val == 'F') return 'Female';
        if ($val == '?') return 'Not sure';
    }
    
    protected function printValCustom($nID, $val) { return $val; }
    
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
                if ($i < $wordLimit) $ret .= ' ' . $w;
            }
        }
        return trim($ret);
    }
    
    public function sortableStart($nID)
    {
        return '';
    }
    
    public function sortableEnd($nID)
    {
        return '';
    }
    
    public function getSetFlds(Request $request, $rSet = '')
    {
        $this->survLoopInit($request);
        if (trim($rSet) == '') $rSet = $GLOBALS["SL"]->coreTbl;
        $preSel = (($request->has('fld')) ? trim($request->get('fld')) : '');
        return $GLOBALS["SL"]->getAllSetTblFldDrops($rSet, $preSel);
    }
    
    public function loadTableDat($curr, $currNodeSessData = [], $tmpSubTier = [])
    {
        $this->tableDat = [
            "tbl"    => '', 
            "defSet" => '', 
            "loop"   => '', 
            "rowCol" => $curr->getTblFldName(), 
            "rows"   => [], 
            "cols"   => [], 
            "blnk"   => [],
            "maxRow" => 10, 
            "req"    => [ $curr->isRequired(), false, [] ]
            ];
        if (isset($curr->nodeRow->NodeDataBranch) && trim($curr->nodeRow->NodeDataBranch) != '') {
            $this->tableDat["tbl"] = $curr->nodeRow->NodeDataBranch;
        }
        $rowSet = $curr->parseResponseSet();
        if ($rowSet["type"] == 'Definition') { // lookup id based on rowCol and currNodeSessData
            $this->tableDat["defSet"] = $rowSet["set"];
            $defs = $GLOBALS["SL"]->def->getSet($rowSet["set"]);
            if (sizeof($defs) > 0) {
                foreach ($defs as $i => $def) {
                    $this->tableDat["rows"][] = $this->addTableDatRow(-3, $def->DefValue, $def->DefID);
                }
            }
        } elseif ($rowSet["type"] == 'LoopItems') {
            $this->tableDat["loop"] = $rowSet["set"];
            $loopCycle = $this->sessData->getLoopRows($rowSet["set"]);
            if (sizeof($loopCycle) > 0) {
                $this->tableDat["tbl"] = $GLOBALS["SL"]->getLoopTable($rowSet["set"]);
                foreach ($loopCycle as $i => $loopItem) {
                    $label = $this->getLoopItemLabel($rowSet["set"], $loopItem, $i);
                    $this->tableDat["rows"][] = $this->addTableDatRow($loopItem->getKey(), $label);
                }
            }
        } elseif ($rowSet["type"] == 'Table') {
            $this->tableDat["tbl"] = $rowSet["set"];
            if (isset($this->dataSets[$this->tableDat["tbl"]]) && sizeof($this->dataSets[$this->tableDat["tbl"]]) > 0) {
                foreach ($this->dataSets[$this->tableDat["tbl"]] as $i => $tblItem) {
                    $label = $this->getTableRecLabel($rowSet["set"], $tblItem, $i);
                    $this->tableDat["rows"][] = $this->addTableDatRow($tblItem->getKey(), $label);
                }
            }
        } else { // no set, type is to just let the user add rows of the table
            $rowIDs = $this->sessData->getBranchChildRows($this->tableDat["tbl"], true);
            if (sizeof($rowIDs) > 0) {
                foreach ($rowIDs as $rowID) $this->tableDat["rows"][] = $this->addTableDatRow($rowID);
            }
        }
        if (sizeof($this->tableDat["rows"]) > 0) {
            foreach ($this->tableDat["rows"] as $i => $row) {
                if ($row["leftTxt"] == strtolower($row["leftTxt"])) {
                    $this->tableDat["rows"][$i]["leftTxt"] = ucwords($row["leftTxt"]);
                }
            }
        }
        $this->tableDat["maxRow"] = sizeof($this->tableDat["rows"]);
        if (isset($curr->nodeRow->NodeCharLimit) && intVal($curr->nodeRow->NodeCharLimit) > 0) {
            $this->tableDat["maxRow"] = $curr->nodeRow->NodeCharLimit;
        }
        if (sizeof($tmpSubTier) > 0) {
            foreach ($tmpSubTier[1] as $k => $kidNode) {
                $this->tableDat["cols"][]   = $this->allNodes[$kidNode[0]];
                $this->tableDat["req"][2][] = $this->allNodes[$kidNode[0]]->isRequired();
                if ($this->allNodes[$kidNode[0]]->isRequired()) $this->tableDat["req"][1] = true;
            }
        }
        return $this->tableDat;
    }
    
    public function addTableDatRow($id = -3, $leftTxt = '', $leftVal = '', $cols = [])
    {
        return [
            "id"      => $id,      // unique row ID
            "leftTxt" => $leftTxt, // displayed in the left column of this row
            "leftVal" => $leftVal, // in addition to unique row ID
            "cols"    => $cols     // filled with nested field printings
            ];
    }
    
    protected function hasParentType($nID = -3, $type = '', $types = [])
    {
        if (isset($this->allNodes[$nID]) && $this->allNodes[$nID]->parentID > 0
            && isset($this->allNodes[$this->allNodes[$nID]->parentID])) {
            $p = $this->allNodes[$this->allNodes[$nID]->parentID];
            return (isset($p->nodeType) && (($type != '' && $p->nodeType == $type) 
                || (sizeof($types) > 0 && in_array($p->nodeType, $types))));
        }
        return false;
    }
    
    protected function hasCycleAncestor($nID = -3)
    {
        if (isset($this->allNodes[$nID]) && $this->allNodes[$nID]->parentID > 0) {
            if (isset($this->allNodes[$this->allNodes[$nID]->parentID])
                && $this->allNodes[$this->allNodes[$nID]->parentID]->isLoopCycle()) {
                return true;
            }
            return $this->hasCycleAncestor($this->allNodes[$nID]->parentID);
        }
        return false;
    }
    
    protected function hasCycleAncestorActive($nID = -3)
    {
        return ($this->hasCycleAncestor($nID) && trim($GLOBALS["SL"]->currCyc["cyc"][1]) != '');
    }
    
    protected function hasSpreadsheetParent($nID = -3)
    {
        if ($this->allNodes[$nID]->parentID > 0) {
            if (isset($this->allNodes[$this->allNodes[$nID]->parentID]) 
                && $this->allNodes[$this->allNodes[$nID]->parentID]->isSpreadTbl()) {
                return true;
            }
        }
        return false;
    }
    
    protected function hasSpreadsheetParentActive($nID = -3)
    {
        return ($this->hasSpreadsheetParent($nID) && trim($GLOBALS["SL"]->currCyc["tbl"][1]) != '');
    }
    
    protected function hasActiveParentCyc($nID = -3, $tbl = '')
    {
        return (($this->hasCycleAncestorActive($nID) && $tbl == $GLOBALS["SL"]->currCyc["cyc"][0])
            || ($this->hasSpreadsheetParentActive($nID) && $tbl == $GLOBALS["SL"]->currCyc["tbl"][0]));
    }
    
    protected function chkParentCycInds($nID = -3, $tbl = '')
    {
        $itemInd = $itemID = -3;
        if ($this->hasCycleAncestorActive($nID) && $tbl == $GLOBALS["SL"]->currCyc["cyc"][0]) {
            if (intVal($GLOBALS["SL"]->currCyc["cyc"][2]) > 0) {
                $itemID = $GLOBALS["SL"]->currCyc["cyc"][2];
                $itemInd = $this->sessData->getRowInd($tbl, $itemID);
            }
        } elseif ($this->hasSpreadsheetParentActive($nID) && $tbl == $GLOBALS["SL"]->currCyc["tbl"][0]) {
            if (intVal($GLOBALS["SL"]->currCyc["tbl"][2]) > 0) {
                $itemID = $GLOBALS["SL"]->currCyc["tbl"][2];
                $itemInd = $this->sessData->getRowInd($tbl, $itemID);
            }
        }
        return [ $itemInd, $itemID ];
    }
    
    public function loadTreeNodeStats()
    {
        $GLOBALS["SL"]->resetTreeNodeStats();
        $GLOBALS["SL"]->x["qTypeStats"]["nodes"]["tot"] = sizeof($this->allNodes);
        if (sizeof($this->allNodes) > 0) {
            $loops = [];
            foreach ($this->allNodes as $nID => $node) {
                $GLOBALS["SL"]->logTreeNodeStat($node);
                if (isset($node->nodeType) 
                    && in_array($node->nodeType, ['Loop Root', 'Loop Cycle', 'Spreadsheet Table'])) {
                    $loops[] = $node->nodeID;
                }
            }
            if (sizeof($loops) > 0) {
                $GLOBALS["SL"]->x["qTypeStats"]["nodes"]["loops"] = sizeof($loops);
                foreach ($loops as $nID) {
                    $this->loadTreeNodeStatsRecursLoop($this->loadNodeSubTier($nID));
                }
            }
        }
        return true;
    }
    
    public function loadTreeNodeStatsRecursLoop($tmpSubTier = [])
    {
        if (sizeof($tmpSubTier) > 1 && sizeof($tmpSubTier[1]) > 0) {
            foreach ($tmpSubTier[1] as $childNode) {
                if (isset($this->allNodes[$childNode[0]]) && isset($this->allNodes[$childNode[0]]->nodeType)) {
                    $curr = $this->allNodes[$childNode[0]];
                    if (in_array($curr->nodeType, $this->nodeTypes) && !in_array($curr->nodeType, ['Spreadsheet Table', 
                        'User Sign Up', 'Hidden Field', 'Spambot Honey Pot'])) {
                        $GLOBALS["SL"]->x["qTypeStats"]["nodes"]["loopNodes"]++;
                    }
                }
                $this->loadTreeNodeStatsRecursLoop($childNode);
            }
        }
        return true;
    }
        
}