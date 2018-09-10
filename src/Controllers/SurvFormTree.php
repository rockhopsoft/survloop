<?php
namespace SurvLoop\Controllers;

use Auth;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\File\File;

use App\Models\User;
use App\Models\SLNode;
use App\Models\SLNodeResponses;
use App\Models\SLTree;
use App\Models\SLContact;
use App\Models\SLEmails;
use App\Models\SLTokens;
use App\Models\SLUsersRoles;

class SurvFormTree extends SurvUploadTree
{
    
    public $classExtension      = 'SurvFormTree';

    public $nodeTypes           = [ 
        'Radio', 'Checkbox', 'Drop Down', 'Text', 'Long Text', 'Text:Number', 'Slider', 'Email', 'Password', 
        'Date', 'Date Picker', 'Date Time', 'Time', 'Gender', 'Gender Not Sure', 'Feet Inches', 
        'U.S. States', 'Countries', 'Uploads', 'Spreadsheet Table', 'User Sign Up', 'Hidden Field', 
        'Spambot Honey Pot', 'Other/Custom' 
    ];
    
    public $nodeSpecialTypes    = [
        'Instructions', 'Instructions Raw', 'Page', 'Branch Title', 'Loop Root', 'Loop Cycle', 'Loop Sort', 
        'Data Manip: New', 'Data Manip: Update', 'Data Manip: Wrap', 'Data Manip: Close Sess', 'Big Button', 
        'Search', 'Search Results', 'Search Featured', 'Member Profile Basics', 'Send Email', 'Admin Form',
        'Record Full', 'Record Full Public', 'Record Previews', 'Incomplete Sess Check', 'Back Next Buttons', 
        'Data Print', 'Data Print Row', 'Data Print Block', 'Data Print Columns', 'Print Vert Progress', 
        'Plot Graph', 'Line Graph', 'Bar Graph', 'Pie Chart', 'Map', 'MFA Dialogue', 'Widget Custom', 
        'Page Block', 'Layout Row', 'Layout Column', 'Layout Sub-Response', 'Gallery Slider'
    ];
    
    protected $pageHasUpload    = [];
    protected $pageHasReqs      = '';
    protected $pageFldList      = [];
    protected $page1stVisib     = '';
    protected $hideKidNodes     = [];
    
    protected $nextBtnOverride  = '';
    protected $loopItemsCustBtn = '';
    
    protected $pageCoreRow      = [];
    protected $pageCoreFlds     = [];
    
    protected $tableDat         = [];
    
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
    
    protected function nodePrintButton($nID = -3, $tmpSubTier = [], $promptNotesSpecial = '', $printBack = true)
    { 
        $ret = $this->customNodePrintButton($nID, $promptNotesSpecial);
        if ($ret != '') return $ret;
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') return '';
        $btnSize = ((in_array($this->pageCnt, [1, 2])) ? 'btn-xl' : 'btn-lg');
        
        // else print standard button variations
        $ret .= '<div class="fC"></div><div id="nodeSubBtns" class="nodeSub">';
        if (isset($this->loopItemsCustBtn) && $this->loopItemsCustBtn != '') {
            $ret .= $this->loopItemsCustBtn;
        } elseif ($this->allNodes[$nID]->nodeType != 'Page' || $this->allNodes[$nID]->nodeOpts%29 > 0) {
            $nextLabel = 'Next';
            if ($this->nodePrintJumpTo($nID) > 0
                || ($this->allNodes[$nID]->nodeType == 'Instructions' && empty($tmpSubTier[1]))) {
                $nextLabel = 'OK';
            }
            if (trim($this->nextBtnOverride) != '') {
                $nextLabel = $this->nextBtnOverride;
            }
            $itemCnt = 0;
            if (isset($this->sessData->loopItemIDs[$GLOBALS["SL"]->closestLoop["loop"]])) {
                $itemCnt = sizeof($this->sessData->loopItemIDs[$GLOBALS["SL"]->closestLoop["loop"]]);
            }
            if ($this->allNodes[$nID]->isStepLoop() && $itemCnt != sizeof($this->sessData->loopItemIDsDone)) {
                $ret .= '<a href="javascript:;" class="fR btn btn-primary ' . $btnSize . ' nFormNext" id="nFormNextBtn" 
                    ><i class="fa fa-arrow-circle-o-right"></i> ' . $nextLabel . '</a>';
            } else {
                $ret .= '<a href="javascript:;" class="fR btn btn-primary ' . $btnSize . ' nFormNext" id="nFormNextBtn" 
                    >' . $nextLabel . '</a>';
                //$ret .= '<input type="button" value="' . $nextLabel 
                //    . '" class="fR btn btn-primary ' . $btnSize . ' nFormNext" id="nFormNextBtn">';
            }
        }
        if ($this->nodePrintJumpTo($nID) <= 0 && $printBack && $GLOBALS["SL"]->treeRow->TreeFirstPage != $nID
            && ($this->allNodes[$nID]->nodeType != 'Page' || $this->allNodes[$nID]->nodeOpts%29 > 0)) {
            $ret .= '<a href="javascript:;" class="fL btn btn-secondary ' . $btnSize . ' nFormBack" id="nFormBack" 
                >Back</a>';
            //$ret .= '<input type="button" value="Back" class="fL nFormBack btn btn-lg btn-secondary" id="nFormBack">';
        }
        $ret .= '<div class="clearfix p5"></div></div><div class="disNon"><input type="submit"></div>';
        return $ret; 
    }
    
    protected function currNodeFormAction()
    {
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
            $ret = '/' . $GLOBALS["SL"]->treeRow->TreeSlug;
            if ($GLOBALS["SL"]->treeIsAdmin) $ret = '/dash' . $ret;
            if (isset($GLOBALS["SL"]->x["pageSlugSffx"])) $ret .= $GLOBALS["SL"]->x["pageSlugSffx"];
            return $ret;
        }
        if ($GLOBALS["SL"]->treeIsAdmin) return '/dash-sub';
        return '/sub';
    }
    
    protected function printNodePublicFormStart($nID)
    {
        $ret = '';
        $loopRootJustLeft = -3;
        if (isset($this->sessInfo->SessLoopRootJustLeft) && intVal($this->sessInfo->SessLoopRootJustLeft) > 0) {
            $loopRootJustLeft = $this->sessInfo->SessLoopRootJustLeft;
            $this->sessInfo->SessLoopRootJustLeft = -3;
            $this->sessInfo->save();
        }
        /* if (isset($this->v["hasRegisterNode"]) && $this->v["hasRegisterNode"]) {
            session()->put('sessTreeReg', $this->treeID);
            session()->put('sessTreeRegTime', mktime());
        } */
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') $ret .= '<div id="isPage"></div>';
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Survey' || $GLOBALS["SL"]->treeRow->TreeOpts%19 == 0 
            || $GLOBALS["SL"]->treeRow->TreeOpts%53 == 0) {
            $ret .= view('vendor.survloop.formtree-form-start', [
                "nID"              => $nID, 
                "nSlug"            => $this->allNodes[$nID]->nodeRow->NodePromptNotes, 
                "currPage"         => $this->v["currPage"],
                "action"           => $this->currNodeFormAction(), 
                "pageHasUpload"    => $this->pageHasUpload, 
                "nodePrintJumpTo"  => $this->nodePrintJumpTo($nID), 
                "loopRootJustLeft" => $loopRootJustLeft, 
                "zoomPref"         => ((isset($this->sessInfo->SessZoomPref)) 
                    ? intVal($this->sessInfo->SessZoomPref) : 0)
            ])->render();
        }
        return $ret;
    }
    
    protected function printNodePublicFormEnd($nID, $promptNotesSpecial = '')
    {
        $GLOBALS["SL"]->pageJAVA .= view('vendor.survloop.formtree-form-js', [
            "currPage"         => $this->v["currPage"],
            "pageURL"          => $this->allNodes[$nID]->nodeRow->NodePromptNotes, 
            "pageFldList"      => $this->pageFldList, 
            "pageJSvalid"      => $this->pageJSvalid,
            "hasFixedHeader"   => $this->v["hasFixedHeader"]
            ])->render();
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Survey' || $GLOBALS["SL"]->treeRow->TreeOpts%19 == 0 
            || $GLOBALS["SL"]->treeRow->TreeOpts%53 == 0) {
            return '</form>';
        }
    }
    
    /* seems unused lately
    private $pagePrevLiners = [];
    private $pageNodes = [];
    
    protected function getPrintableNodeList($nID = -3, $tmpSubTier = [])
    {
        if (!isset($this->allNodes[$nID])) return false;
        if (empty($tmpSubTier)) $tmpSubTier = $this->loadNodeSubTier($nID);
        $this->pageNodes[] = $this->allNodes[$nID];
        if (sizeof($tmpSubTier[1]) > 0) {
            foreach ($tmpSubTier[1] as $childNode) { // recurse these bitches
                if (!$this->allNodes[$childNode[0]]->isPage()) {
                    $this->getPrintableNodeList($childNode[0], $childNode);
                }
            }
        }
        return true;
    }
    
    protected function getPrintSpecs($nID = -3, $tmpSubTier = [])
    {
        $this->pageNodes = [];
        $this->getPrintableNodeList($nID, $tmpSubTier);
        return true;
    }
    */
    
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
                    $str = str_replace('[LoopItemLabel]', '<span class="slBlueDark"><b>' . $label . '</b></span>', $str);
                }
                $str = str_replace('[LoopItemCnt]', '<span class="slBlueDark"><b>' . (1+$itemInd) . '</b></span>', $str);
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
                    $str = $strPre . '<span class="slBlueDark"><b>' . $label . '</b></span>' . $strPost;
                }
            }
        }
        $str = $this->customCleanLabel($str, $nIDtxt);
        return $this->cleanLabel($str);
    }
    
    protected function customCleanLabel($str = '', $nIDtxt = '') { return $str; }
    
    protected function cleanLabel($str = '')
    {
        $span = '<span class="slBlueDark"><b>';
        $str = str_replace($span . 'You</b></span>', $span . 'you</b></span>', $str);
        $str = str_replace($span . 'you</b></span>&#39;s', $span . 'your</b></span>', $str);
        $str = str_replace('Was <span class="slBlueDark"><b>you</b></span>', 
            'Were <span class="slBlueDark"><b>you</b></span>', $str);
        $str = str_replace('was <span class="slBlueDark"><b>you</b></span>', 
            'were <span class="slBlueDark"><b>you</b></span>', $str);
        $str = str_replace($span . 'you</b></span>\'s', $span . 'your</b></span>', $str);
        $str = str_replace($span . 'you</b></span> was', $span . 'you</b></span> were', $str);
        $str = str_replace(', [LoopItemLabel]:', ':', 
            str_replace(', <span class="slBlueDark"><b>[LoopItemLabel]</b></span>:', ':', $str));
        $str = str_replace(', <span class="slBlueDark"><b></b></span>:', ':', 
            str_replace(', <span class="slBlueDark"><b>&nbsp;</b></span>:', ':', $str));
        $str = trim(str_replace(', :', ':', $str));
        if (strpos(strip_tags($str), 'you') === 0) {
            $str = str_replace($span . 'you', $span . 'You', $str);
        }
        return $str;
    }
    
    protected function customNodePrintWrap($nID, $bladeRender = '')
    {
        return $this->printNodePublicFormStart($nID) . $bladeRender
            . $this->nodePrintButton($nID) . $this->printNodePublicFormEnd($nID) 
            . '<div class="fC p20"></div>';
    }

    
    //************  hidden field with nested nodes, flagged on/off when visible, 
    //    so i know which versions of duplicated questions to store during post processing  ************
    
    protected function customNodePrint($nID = -3, $tmpSubTier = [], $nIDtxt = '', $nSffx = '', $currVisib = 1)
    {
        return '';
    }
    
    protected function printNodePublic($nID = -3, $tmpSubTier = [], $currVisib = -1)
    {
        if (!isset($this->allNodes[$nID]) || $this->allNodes[$nID]->nodeType == 'Send Email') return '';
        if (!$this->checkNodeConditions($nID)) return '';
        if (empty($tmpSubTier)) $tmpSubTier = $this->loadNodeSubTier($nID);
        if (!isset($this->v["hasFixedHeader"])) $this->v["hasFixedHeader"] = false;
        $ret = '';
        
        // copy node object; load field info and current session data
        $this->allNodes[$nID]->fillNodeRow($nID);
        $curr = $this->allNodes[$nID];
        list($tbl, $fld) = $curr->getTblFld();
        $fldForeignTbl = $GLOBALS["SL"]->fldForeignKeyTbl($tbl, $fld);
        if (($curr->isPage() || $curr->isInstruct()) && isset($GLOBALS["SL"]->closestLoop["obj"]->DataLoopTable)) {
            $tbl = $GLOBALS["SL"]->closestLoop["obj"]->DataLoopTable;
        }
        if ($tbl == '' && $this->hasCycleAncestorActive($nID)) {
            $tbl = $GLOBALS["SL"]->currCyc["cyc"][0];
        }
        if ($tbl == '' && $this->hasSpreadsheetParentActive($nID)) {
            $tbl = $GLOBALS["SL"]->currCyc["tbl"][0];
        }

        if ($curr->isPage()) {
            $extraOpts = $this->swapIDsSEO($curr->extraOpts);
            $GLOBALS['SL']->setSEO($extraOpts["meta-title"], $extraOpts["meta-desc"], $extraOpts["meta-keywords"], 
                $extraOpts["meta-img"]);
        }
        
        // if ($currVisib == 1 && $curr->nodeType == 'Data Manip: New') $this->runDataManip($nID);
        if ($curr->isDataManip()) $this->loadManipBranch($nID, ($currVisib == 1));
        $hasParManip = $this->hasParentDataManip($nID);
        
        if ($curr->isPage() || $curr->isLoopRoot()) {
            // print the button, and form initialization which only happens once per page
            // make sure these are reset, in case of redirect
            $this->pageJSvalid = $this->pageHasReqs = '';
            $this->pageHasUpload = $this->pageFldList = $this->hideKidNodes = [];
            $this->runPageExtra($nID);
            $this->runPageLoad($nID);
            if ($GLOBALS["SL"]->treeRow->TreeType != 'Page') $ret .= '<div id="pageTopGapID" class="pageTopGap"></div>';
            if ($curr->isLoopRoot()) {
                $ret .= $curr->nodeRow->NodePromptText . $this->printSetLoopNav($nID, $curr->dataBranch);
            } else { // isPage()
                if (sizeof($tmpSubTier[1]) > 0) {
                    foreach ($tmpSubTier[1] as $childNode) { // recurse deez!..
                        if (!$this->allNodes[$childNode[0]]->isPage()) {
                            $ret .= $this->printNodePublic($childNode[0], $childNode, $currVisib);
                        }
                    } 
                }
            }
            if (intVal($curr->nodeRow->NodeCharLimit) > 0) {
                $GLOBALS["SL"]->pageJAVA .= 'setTimeout("focusNodeID(' . $curr->nodeRow->NodeCharLimit 
                    . ').focus()", 100);' . "\n";
            } elseif (trim($this->page1stVisib) != '' && intVal($curr->nodeRow->NodeCharLimit) == 0) {
                $GLOBALS["SL"]->pageJAVA .= 'setTimeout("if (document.getElementById(\'' . $this->page1stVisib 
                    . '\')) document.getElementById(\'' . $this->page1stVisib . '\').focus()", 100);' . "\n";
            }
            return $this->printNodePublicFormStart($nID) . $ret . '<div id="pageBtns"><div id="formErrorMsg"></div>' 
                . $this->nodePrintButton($nID, $tmpSubTier, '' /* $promptNotesSpecial */) . '</div>' 
                . $this->printNodePublicFormEnd($nID, '' /* $promptNotesSpecial */)
                . (($GLOBALS["SL"]->treeRow->TreeType != 'Page') ? '<div class="pageBotGap"></div>' : '');
        } // else not Page or Loop Root
        
        $itemInd = $itemID = -3;
        if ($this->hasActiveParentCyc($nID, $tbl)) {
            list($itemInd, $itemID) = $this->chkParentCycInds($nID, $tbl);
        } else { // default logic, not LoopCycle, not SpreadTable 
            list($itemInd, $itemID) = $this->sessData->currSessDataPos($tbl, $hasParManip);
            if ($itemInd < 0 && isset($GLOBALS["SL"]->closestLoop["loop"]) 
                && trim($GLOBALS["SL"]->closestLoop["loop"]) != '' 
                && $tbl == $this->sessData->isCheckboxHelperTable($tbl)) {
                // In this context, relevant item index is item's index with the loop, not table's whole data set
                $itemInd = $this->sessData->getLoopIndFromID($GLOBALS["SL"]->closestLoop["loop"], $itemID);
            }
        }
        $currNodeSessData = $this->sessData->currSessData($nID, $tbl, $fld, 'get', '', $hasParManip, $itemInd, $itemID);
//if ($nID == 115) { echo '<br /><br /><br />nID: ' . $nID . ', currNodeSessData: ' . $currNodeSessData . ', tbl: ' . $tbl . ', fld: ' . $fld . ', itemInd: ' . $itemInd . ', itemID: ' . $itemID . '<br />'; }
        //if ($itemID <= 0) $currNodeSessData = ''; // override false profit ;-P
        if ($currNodeSessData == '' && trim($curr->nodeRow->NodeDefault) != '') {
            $currNodeSessData = $curr->nodeRow->NodeDefault;
        }
        
        // check for extra custom PHP code stored with the node; check for standardized techniques
        $nodeOverrides = $this->printNodeSessDataOverride($nID, $tmpSubTier, $currNodeSessData);
        if (is_array($nodeOverrides) && sizeof($nodeOverrides) > 1) $currNodeSessData = $nodeOverrides;
        elseif (is_array($nodeOverrides) && sizeof($nodeOverrides) == 1 && isset($nodeOverrides[0])) {
            $currNodeSessData = $nodeOverrides[0];
        }
        
        $nSffx = $GLOBALS["SL"]->getCycSffx();
        $GLOBALS["SL"]->pageJAVA .= 'nodeList[nodeList.length] = ' . $nID . '; '
            . 'nodeParents[' . $nID . '] = ' . $curr->parentID . ';' . "\n"
            . (($nSffx != '') ? 'nodeSffxs[nodeSffxs.length] = "' . $nSffx . '";' . "\n" : '');
        $nIDtxt = trim($nID . $nSffx);
        $condKids = $showMoreNodes = [];
        if (sizeof($tmpSubTier[1]) > 0) {
            if ($curr->nodeType == 'Countries') {
                $nxtNode = $this->nextNode($nID);
                if ($nxtNode > 0 && isset($this->allNodes[$nxtNode])) {
                    if ($this->allNodes[$nxtNode]->nodeType == 'U.S. States') {
                        $curr->hasShowKids = true;
                        $GLOBALS["SL"]->loadStates();
                        $curr->responses = $GLOBALS["SL"]->states->getCountryResponses($nID, ['United States']);
                    }
                }
            }
            if ($curr->hasShowKids && sizeof($curr->responses) > 0) { // displaying children on page is conditional
                foreach ($curr->responses as $j => $res) {
                    if (intVal($res->NodeResShowKids) > 0) {
                        if (!isset($condKids[$res->NodeResShowKids])) $condKids[$res->NodeResShowKids] = [];
                        $condKids[$res->NodeResShowKids][] = $res->NodeResValue;
                    }
                }
                if (sizeof($condKids) > 0) {
                    foreach ($condKids as $condNode => $condVals) {
                        $condHide = true;
                        foreach ($condVals as $cVal) {
                            if ($this->isCurrDataSelected($currNodeSessData, $cVal, $curr->nodeType)) {
                                $condHide = false;
                            }
                        }
                        if ($condHide) $this->hideKidNodes[] = $condNode;
                    }
                }
                $GLOBALS["SL"]->pageJAVA .= 'conditionNodes[' . $nID . '] = true;' . "\n";
                $childList = [];
                foreach ($tmpSubTier[1] as $childNode) {
                    $childList[] = $childNode[0];
                    if (isset($this->kidMaps[$nID]) && sizeof($this->kidMaps[$nID]) > 0) {
                        foreach ($this->kidMaps[$nID] as $nKid => $ress) {
                            if ($nKid == $childNode[0] && sizeof($childNode[1]) > 0
                                && in_array($this->allNodes[$nKid]->nodeType, ['Page Block', 'Data Manip: New', 
                                    'Data Manip: Update', 'Data Manip: Wrap', 'Instructions', 'Instructions Raw', 
                                    'Layout Row', 'Gallery Slider'])) {
                                foreach ($childNode[1] as $grandNode) {
                                    if (!isset($showMoreNodes[$childNode[0]])) $showMoreNodes[$childNode[0]] = []; 
                                    $showMoreNodes[$childNode[0]][] = $grandNode[0];
                                    if ($this->allNodes[$nKid]->nodeType == 'Layout Row' && sizeof($grandNode[1]) > 0) {
                                        foreach ($grandNode[1] as $gGrandNode) {
                                            if ($this->allNodes[$gGrandNode[0]]->nodeType == 'Layout Column' 
                                                && sizeof($gGrandNode[1]) > 0) {
                                                $showMoreNodes[$childNode[0]][] = $gGrandNode[0];
                                                foreach ($gGrandNode[1] as $ggGrandNode) {
                                                    $showMoreNodes[$childNode[0]][] = $ggGrandNode[0];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $GLOBALS["SL"]->pageJAVA .= 'nodeKidList[' . $nID . '] = ['.implode(', ', $childList).'];' . "\n";
            }
        }
        if (sizeof($curr->responses) == 3 && $curr->responses[1]->NodeResValue == '...') {
            $start = intVal($curr->responses[0]->NodeResValue);
            $finish = intVal($curr->responses[2]->NodeResValue);
            $curr->responses = [];
            if ($start < $finish) {
                for ($i=$start; $i<=$finish; $i++) $curr->addTmpResponse($i);
            } else {
                for ($i=$start; $i>=$finish; $i--) $curr->addTmpResponse($i);
            }
            $curr->chkFldOther();
        }
        
        if ($currVisib < 0) $currVisib = ((in_array($nID, $this->hideKidNodes)) ? 0 : 1);
        elseif ($currVisib == 1 && in_array($nID, $this->hideKidNodes)) $currVisib = 0;
        $visibilityField = '<input type="hidden" name="n' . $nIDtxt . 'Visible" id="n' . $nIDtxt 
            . 'VisibleID" value="' . $currVisib . '">';
        if ($curr->nodeType == 'Layout Column') $visibilityField = '';
        if ($this->page1stVisib == '' && $currVisib == 1) {
            if (in_array($curr->nodeType, ['Radio', 'Checkbox', 'Gender', 'Gender Not Sure'])) {
                $this->page1stVisib = 'n' . $nID . 'fld0';
            } elseif (in_array($curr->nodeType, ['Date', 'Date Time'])) {
                $this->page1stVisib = 'n' . $nID . 'fldMonthID';
            } elseif (in_array($curr->nodeType, ['Time'])) {
                $this->page1stVisib = 'n' . $nID . 'fldHrID';
            } elseif (in_array($curr->nodeType, ['Feet Inches'])) {
                $this->page1stVisib = 'n' . $nID . 'fldFeetID';
            } elseif (in_array($curr->nodeType, ['Drop Down', 'Text', 'Long Text', 'Text:Number', 'Slider',
                'Email', 'Password', 'U.S. States', 'Countries'])) {
                $this->page1stVisib = 'n' . $nID . 'FldID';
            }
        }
        
        if ($curr->isRequired()) $this->pageHasReqs++;
        
        $ret = $this->customNodePrint($nID, $tmpSubTier, $nIDtxt, $nSffx, $currVisib);
        if ($curr->nodeType == 'Data Print Row' && is_array($ret) && sizeof($ret) > 0) return $ret; 
        if (!is_array($ret) && $ret != '') return $visibilityField . $ret;
        $ret = $visibilityField . $this->nodeSessDump($nIDtxt, $nID);
        // else print standard node output...
        
        $xtraClass = ' slTab';
        if ($curr->nodeType != 'Long Text') $xtraClass .= ' ntrStp';
        if (isset($this->v["graphFilters"]) && intVal($this->v["graphFilters"]) > 0 && trim($curr->dataStore) != '') {
            $xtraClass .= ' graphUp' . ((in_array($curr->nodeType, ['Drop Down', 'U.S. States', 'Countries'])) 
                ? 'Drp' : '');
            $GLOBALS["SL"]->pageAJAX .= 'addGraphFld("' . $nIDtxt . '", ' 
                . $GLOBALS["SL"]->getFldIDFromFullWritName($curr->dataStore) . ', ' 
                . $this->v["graphFilters"] . ');' . "\n";
        }
        
        // check for extra custom HTML/JS/CSS code stored with the node; check for standardized techniques
        $nodePromptAfter = '';
        $onKeyUp = ' checkNodeUp(\'' . $nIDtxt . '\', -1, 0); ';
        if (trim($curr->nodeRow->NodePromptAfter) != '' && !$curr->isWidget()) {
            if (stripos($curr->nodeRow->NodePromptAfter, '/'.'* formAJAX *'.'/') !== false) {
                $GLOBALS["SL"]->pageAJAX .= $curr->nodeRow->NodePromptAfter;
            } else {
                if (!$curr->isPage()) {
                    if (strpos($curr->nodeRow->NodePromptAfter, 'function reqFormNode[[nID]](') !== false) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID') && document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) reqFormNode" . $nID . "();\n";
                    }
                    if (strpos($curr->nodeRow->NodePromptAfter, 'function fldOnKeyUp[[nID]](') !== false) {
                        $onKeyUp .= ' fldOnKeyUp' . $nIDtxt . '(); ';
                    }
                    $nodePromptAfter = $this->swapLabels($nIDtxt, $curr->nodeRow->NodePromptAfter, $itemID, $itemInd);
                    $nodePromptAfter = $this->extractJava($nodePromptAfter, $nID);
                }
            }
        }
        $charLimit = '';
        /* if (intVal($curr->nodeRow->NodeCharLimit) > 0 && $curr->nodeRow->NodeOpts%31 > 0 
            && $curr->nodeType != 'Uploads') {
            $onKeyUp .= ' charLimit(\'' . $nIDtxt . '\', ' . $curr->nodeRow->NodeCharLimit . '); ';
            $charLimit = "\n" . '<div id="charLimit' . $nID . 'Msg" class="slRedDark f12 opac33"></div>';
            $GLOBALS["SL"]->pageJAVA .= 'setTimeout("charLimit(\'' . $nIDtxt . '\', ' 
                . $curr->nodeRow->NodeCharLimit . ')", 50);' . "\n";
        } */
        if ($curr->nodeRow->NodeOpts%31 == 0 || $curr->nodeRow->NodeOpts%47 == 0) {
            if (intVal($curr->nodeRow->NodeCharLimit) == 0) $curr->nodeRow->NodeCharLimit = 10000000000;
            $onKeyUp .= ' wordCountKeyUp(\'' . $nIDtxt . '\', ' . intVal($curr->nodeRow->NodeCharLimit) . '); ';
            $GLOBALS["SL"]->pageJAVA .= 'setTimeout("wordCountKeyUp(\'' . $nIDtxt . '\', ' 
                . intVal($curr->nodeRow->NodeCharLimit) . ')", 50);' . "\n";
        }
        if (isset($curr->extraOpts["minVal"]) && $curr->extraOpts["minVal"] !== false) {
            $onKeyUp .= ' checkMin(\'' . $nIDtxt . '\', ' . $curr->extraOpts["minVal"] . '); ';
        }
        if (isset($curr->extraOpts["maxVal"]) && $curr->extraOpts["maxVal"] !== false) {
            $onKeyUp .= ' checkMax(\'' . $nIDtxt . '\', ' . $curr->extraOpts["maxVal"] . '); ';
        }
        if ($curr->nodeType == 'Long Text') $onKeyUp .= ' flexAreaAdjust(this); ';
        if (trim($onKeyUp) != '') $onKeyUp = ' onKeyUp="' . $onKeyUp . '" ';
        
        // check notes settings for any standardized techniques
        $promptNotesSpecial = '';
        if ($this->isPromptNotesSpecial($curr->nodeRow->NodePromptNotes)) {
            $promptNotesSpecial = $curr->nodeRow->NodePromptNotes;
            $curr->nodeRow->NodePromptNotes = '';
        }
        
        // write basic node field labeling
        $nodePromptText  = $this->swapLabels($nIDtxt, $curr->nodeRow->NodePromptText, $itemID, $itemInd);
        if ($curr->isRequired() && $curr->nodeType != 'Hidden Field') {
            $nodePromptText = $this->addPromptTextRequired($curr, $nodePromptText);
        }
        $nodePromptNotes = $this->swapLabels($nIDtxt, $curr->nodeRow->NodePromptNotes, $itemID, $itemInd);
        if (trim($nodePromptNotes) != '' && !$curr->isLoopRoot()) {
            if ($curr->nodeRow->NodeOpts%83 == 0) {
                $nodePromptText = '<a id="hidivBtnnLabel' . $nIDtxt . 'notes" class="hidivBtn crsrPntr float-right">'
                    . '<i class="fa fa-info-circle" aria-hidden="true"></i></a>' . $nodePromptText;
            }
            $nodePromptText .= '<div id="hidivnLabel' . $nIDtxt . 'notes" class="subNote'
                . (($curr->nodeRow->NodeOpts%83 == 0) ? ' disNon' : '') . '">' 
                . $nodePromptNotes . '</div>' . "\n";
        }
        if (strpos($nodePromptText, 'fixedHeader') !== false) $this->v["hasFixedHeader"] = true;
        
        $nodePromptText  = $this->extractJava($nodePromptText, $nID);
        $nodePromptNotes = $this->extractJava($nodePromptNotes, $nID);
        
        $nodePrompt = '';
        if (strpos($curr->nodeRow->NodePromptText, '[[PreviewPrivate]]') !== false 
            || strpos($curr->nodeRow->NodePromptText, '[[PreviewPublic]]') !== false) {
            $nodePrompt = $nodePromptText;
        } elseif (trim($nodePromptText) != '' && !$curr->isDataPrint() && !$this->hasSpreadsheetParent($nID)) {
            if ($curr->isInstructAny()) {
                $nodePrompt = '<div id="nLabel' . $nIDtxt . '" class="nPrompt">' . $nodePromptText . '</div>' . "\n";
            } else {
                $nodePrompt = "\n" . '<div id="nLabel' . $nIDtxt . '" class="nPrompt">'
                    . '<label for="n' . $nIDtxt . 'FldID"' . ((!in_array($curr->nodeType, ['Radio', 'Checkbox']) || $curr->nodeRow->NodeOpts%83 == 0) 
                        ? ' class="w100"' : '') . ' >' . $nodePromptText . '</label></div>' . "\n";
            }
        }
        
        if ($curr->isDataPrint()) {
            
            if (in_array($curr->nodeType, ['Data Print', 'Data Print Row'])) {
                
                if (!$this->checkFldDataPerms($curr->getFldRow()) || !$this->checkViewDataPerms($curr->getFldRow())) {
                    if ($curr->nodeType == 'Data Print Row') return [];
                } else {
                    if (isset($GLOBALS["SL"]->formTree->TreeID) && in_array($nID, $this->checkboxNodes)) {
                        $currNodeSessData = $this->valListArr($this->sessData->currSessDataCheckbox($nID, $tbl, $fld));
                        if ($this->hasCycleAncestorActive($nID)) {
                            $cycInd = intVal(str_replace('cyc', '', $GLOBALS["SL"]->currCyc["cyc"][1]));
                            if (isset($currNodeSessData[$cycInd])) $currNodeSessData = $currNodeSessData[$cycInd];
                        }
                    }
                    $fldRow = $GLOBALS["SL"]->getFldRowFromFullName($tbl, $fld);
                    $printRow = true;
                    if ((!is_array($currNodeSessData) && (!$currNodeSessData || trim($currNodeSessData) == ''))
                        || (is_array($currNodeSessData) && sizeof($currNodeSessData) == 0)) {
                        $printRow = false;
                    } elseif (isset($curr->nodeRow->NodeDefault) && trim($curr->nodeRow->NodeDefault) != '' 
                        && ((!is_array($currNodeSessData) && trim($currNodeSessData) != '' 
                        && trim($currNodeSessData) == trim($curr->nodeRow->NodeDefault)) 
                        || (is_array($currNodeSessData) 
                            && trim($currNodeSessData[0]) == trim($curr->nodeRow->NodeDefault)))) {
                        $printRow = false;
                    }
                    if ($printRow) {
                        $deetLabel = (($fldRow && isset($fldRow->FldEng)) ? $fldRow->FldEng : '');
                        $deetLabel = $this->swapLabels($nIDtxt, $deetLabel, $itemID, $itemInd);
                        
                        $deetVal = $GLOBALS["SL"]->printResponse($tbl, $fld, $currNodeSessData, $fldRow);
                        $deetVal = $this->printValCustom($nID, $deetVal);
                        if (isset($GLOBALS["SL"]->formTree->TreeID)) {
                            $lab = $GLOBALS["SL"]->getFldNodeQuestion($tbl, $fld, $GLOBALS["SL"]->formTree->TreeID);
                            if (trim($lab) != '') {
                                $lab = $this->swapLabels($nIDtxt, $lab, $itemID, $itemInd);
                                if (strip_tags($deetLabel) != strip_tags($lab)) {
                                    $deetLabel = '<a id="hidivBtn' . $nIDtxt .'" class="hidivBtn slGrey" ' 
                                        . 'href="javascript:;">' . $deetLabel . '</a><div id="hidiv' . $nIDtxt 
                                        . '" class="disNon">"' . $lab . '"</div>';
                                }
                            }
                        }
                        if ($curr->nodeType == 'Data Print') {
                            $ret .= '<div id="nLabel' . $nIDtxt . '" class="nPrompt"><span class="slGrey mR10">' 
                                . $deetLabel . '</span>' . $deetVal . '</div>';
                        } elseif ($curr->nodeType == 'Data Print Row') {
                            return [$deetLabel, $deetVal, $nID];
                        }
                    } elseif ($curr->nodeType == 'Data Print Row') {
                        return [];
                    }
                }
                
            } elseif (in_array($curr->nodeType, ['Data Print Block', 'Data Print Columns'])) {
                
                $deets = [];
                if (sizeof($tmpSubTier[1]) > 0) {
                    foreach ($tmpSubTier[1] as $childNode) {
                        $kidID = $childNode[0];
                        if ($this->allNodes[$kidID]->nodeType == 'Data Print Row') {
                            $newDeet = $this->printNodePublic($kidID, $childNode, $currVisib);
                            if (is_array($newDeet) && sizeof($newDeet) > 0 && trim($newDeet[0]) != '' 
                                && trim($newDeet[1]) != '') {
                                $deets[] = $newDeet;
                            }
                        } elseif ($this->allNodes[$kidID]->isDataManip()) {
                            $this->loadManipBranch($kidID, ($currVisib == 1));
                            foreach ($childNode[1] as $granNode) {
                                if ($this->allNodes[$granNode[0]]->nodeType == 'Data Print Row') {
                                    $newDeet = $this->printNodePublic($granNode[0], $granNode, $currVisib);
//if ($nID == 193) { echo $kidID . ' -- ' . $granNode[0] . ' ; ' . $GLOBALS["SL"]->currCyc["cyc"][2] . ' [ ' . $newDeet[0] . ' , ' . $newDeet[1] . ' , ' . $newDeet[2] . ' ]<br />'; }
                                    if ($newDeet && is_array($newDeet) && sizeof($newDeet) > 0) $deets[] = $newDeet;
                                }
                            }
                            $this->closeManipBranch($kidID);
                        } elseif ($this->allNodes[$kidID]->isLoopCycle()) {
                            list($tbl, $fld, $newVal) = $this->nodeBranchInfo($nID, $this->allNodes[$kidID]);
                            $loop = str_replace('LoopItems::', '', $this->allNodes[$kidID]->responseSet);
                            $loopCycle = $this->sessData->getLoopRows($loop);
                            // if this is a simple loop of a just a table's rows
//if ($nID == 193) { echo '<br /><br /><br />loop: ' . $loop . ', ' . $this->allNodes[$kidID]->dataBranch . '<br />'; }
                            if (trim($loop) == '' && isset($this->allNodes[$kidID]->dataBranch)
                                && trim($this->allNodes[$kidID]->dataBranch) != '') {
                                $s = sizeof($this->sessData->dataBranches);
                                if ($s > 0 && isset($this->sessData->dataBranches[($s-1)]["itemID"]) 
                                    && intVal($this->sessData->dataBranches[($s-1)]["itemID"]) > 0) {
                                    $loopCycle = $this->sessData->getChildRows(
                                        $this->sessData->dataBranches[($s-1)]["branch"], 
                                        $this->sessData->dataBranches[($s-1)]["itemID"], 
                                        $this->allNodes[$kidID]->dataBranch);
//if ($nID == 193) { echo '<br /><br /><br />tblLoopCycle: <pre>'; print_r($loopCycle); echo '</pre>'; }
                                }
                            }
//if ($nID == 193) { echo '<br /><br /><br />isLoopCycle(' . $kidID . ', tbl: ' . $tbl . ', fld: ' . $fld . ', newVal: ' . $newVal . ', loop: ' . $loop . '<br />loopCycle: <pre>'; print_r($loopCycle); echo '</pre>'; }
                            if (sizeof($childNode[1]) > 0 && $loopCycle && sizeof($loopCycle) > 0) {
                                $GLOBALS["SL"]->currCyc["cyc"][0] = $tbl;
                                if ($loop != '') $GLOBALS["SL"]->currCyc["cyc"][0] =$GLOBALS["SL"]->getLoopTable($loop);
                                foreach ($loopCycle as $i => $loopItem) {
                                    $GLOBALS["SL"]->currCyc["cyc"][1] = 'cyc' . $i;
                                    $GLOBALS["SL"]->currCyc["cyc"][2] = $loopItem->getKey();
//if ($nID == 193) { echo 'starting loop branch: ' . $tbl . ', ' . $loopItem->getKey() . '<br />'; }
                                    $this->sessData->startTmpDataBranch($tbl, $loopItem->getKey());
                                    $label = (1+$i) . ')';
                                    if ($loop != '') {
                                        $GLOBALS["SL"]->fakeSessLoopCycle($loop, $loopItem->getKey());
                                        $label = $this->getLoopItemLabel($loop, $loopItem, $i);
                                    }
//if ($nID == 193) { echo 'dataBranches: <pre>'; print_r($this->sessData->dataBranches); echo '</pre>'; }
                                    foreach ($childNode[1] as $granNode) {
                                        if ($this->allNodes[$granNode[0]]->nodeType == 'Data Print Row') {
                                            $newDeet = $this->printNodePublic($granNode[0], $granNode, $currVisib);
//if ($nID == 193) { echo $kidID . ' -- ' . $granNode[0] . ' ; ' . $GLOBALS["SL"]->currCyc["cyc"][2] . ' [ ' . $newDeet[0] . ' , ' . $newDeet[1] . ' , ' . $newDeet[2] . ' ]<br />'; }
                                            if (isset($newDeet[0]) && trim($newDeet[0]) != '' && trim($newDeet[1]) != '') {
                                                $newDeet[0] = $label . ' ' . $newDeet[0];
                                                $deets[] = $newDeet;
                                            }
                                        }
                                    }
                                    if ($loop != '') {
                                        $GLOBALS["SL"]->removeFakeSessLoopCycle($loop, $loopItem->getKey());
                                    }
                                    $this->sessData->endTmpDataBranch($tbl);
                                    $GLOBALS["SL"]->currCyc["cyc"][1] = '';
                                    $GLOBALS["SL"]->currCyc["cyc"][2] = -3;
                                }
                                $GLOBALS["SL"]->currCyc["cyc"][0] = '';
                            }
                            
                        }
                    }
                }
                /* if ($this->allNodes[$curr->parentID]->isLoopCycle()) {
                    $label = $this->getLoopItemLabel($GLOBALS["SL"]->closestLoop["loop"], 
                        $this->sessData->getRowById($GLOBALS["SL"]->closestLoop["obj"]->DataLoopTable, $itemID), 
                        $itemInd);
                    $nodePromptText = $label . ' ' . $nodePromptText;
                } */
                if (sizeof($deets) > 0) {
                    if ($curr->nodeType == 'Data Print Block') {
                        $ret .= $this->printReportDeetsBlock($deets, strip_tags($nodePromptText), $nID);
                    } else {
                        $colCnt = 2;
                        $ret .= $this->printReportDeetsBlockCols($deets, strip_tags($nodePromptText), $colCnt, $nID);
                    }
                }
                
            } elseif (in_array($curr->nodeType, ['Print Vert Progress'])) {
                
                $deets = [];
                foreach ($tmpSubTier[1] as $childNode) {
                    if ($this->allNodes[$childNode[0]]->nodeType == 'Data Print Row') {
                        list($tbl, $fld) = $this->allNodes[$childNode[0]]->getTblFld();
                        list($itemInd, $itemID) = $this->sessData->currSessDataPos($tbl);
                        if ($itemID > 0 && isset($this->sessData->dataSets[$tbl]) 
                            && isset($this->sessData->dataSets[$tbl][$itemInd]) > 0) {
                            $dateTime = 0;
                            if (isset($this->sessData->dataSets[$tbl][$itemInd]->{ $fld })
                                && trim($this->sessData->dataSets[$tbl][$itemInd]->{ $fld }) != '') {
                                $dateTime = strtotime($this->sessData->dataSets[$tbl][$itemInd]->{ $fld });
                            }
                            if (!isset($this->allNodes[$childNode[0]]->nodeRow->NodeDefault)
                                || trim($this->allNodes[$childNode[0]]->nodeRow->NodeDefault) != trim($dateTime)) {
                                $fldRow = $GLOBALS["SL"]->getTblFldRow('', $tbl, $fld);
                                if ($fldRow && isset($fldRow->FldEng)) {
                                    $deets[] = [ $fldRow->FldEng, $dateTime, $childNode[0] ];
                                }
                            }
                        }
                    }
                }
                $ret .= $this->printReportDeetsVertProg($deets, strip_tags($nodePromptText), $nID);
                
            }
            
        } else { // not data printing...
        
            if ($curr->nodeType == 'Layout Column') {
                $ret .= '<div class="col-md-' . $curr->nodeRow->NodeCharLimit . '">';
            } else {
                if ($this->hasParentType($nID, 'Gallery Slider') && isset($curr->colors["blockImg"]) 
                    && trim($curr->colors["blockImg"]) != '') {
                    $GLOBALS["SL"]->addPreloadImg($curr->colors["blockImg"]);
                }
                $ret .= view('vendor.survloop.inc-block-css', [ "nIDtxt" => $nIDtxt, "node" => $curr ])->render()
                    . '<div id="blockWrap' . $nIDtxt . '" class="w100'
                    . ((isset($curr->colors["blockImgFix"]) && trim($curr->colors["blockImgFix"]) == 'P')
                        ? ' parallax' : '')
                    . (($this->hasParentType($nID, 'Gallery Slider')) 
                        ? (($curr->nodeRow->NodeParentOrder == 0) ? ' disBlo' : ' disNon') : '') . '">';
            }
            if ($GLOBALS["SL"]->treeRow->TreeType == 'Page' && $curr->parentID == $GLOBALS['SL']->treeRow->TreeRoot) {
                if ($curr->isPageBlockSkinny()) { // wrap page block
                    $ret .= '<center><div class="treeWrapForm" id="treeWrap' . $nIDtxt . '">'; //  class="container"
                } else{
                    if ($this->hasFrameLoad() || ($GLOBALS["SL"]->treeRow->TreeOpts%3 == 0 
                        || $GLOBALS["SL"]->treeRow->TreeOpts%17 == 0 || $GLOBALS["SL"]->treeRow->TreeOpts%41 == 0 
                        || $GLOBALS["SL"]->treeRow->TreeOpts%43 == 0)) {
                        $ret .= '<div class="w100 pL15 pR15" id="treeWrap' . $nIDtxt . '">';
                    } else {
                        $ret .= '<div class="container" id="treeWrap' . $nIDtxt . '">';
                    }
                }
            }
            
            if (!$this->hasSpreadsheetParent($nID)) {
                $ret .= '<div class="fC"></div><div class="nodeAnchor"><a id="n' . $nIDtxt . '" name="n' . $nIDtxt 
                    . '"></a></div>';
            }
            
            // write the start of the main node wrapper
            if (!in_array($curr->nodeType, ['Layout Row', 'Layout Column'])) {
                $ret .= '<div id="node' . $nIDtxt . '" class="nodeWrap' . (($curr->isGraph()) ? ' nGraph' : '')
                    . (($curr->nodeRow->NodeOpts%89 == 0) ? ' slCard' : '');
                if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
                    $ret .= (($curr->isInstructAny()) ? ' w100' : '') . (($curr->isPage()) ? ' h100' : '');
                }
                if ($currVisib != 1 && (trim($GLOBALS["SL"]->currCyc["res"][1]) == '' 
                    || substr($GLOBALS["SL"]->currCyc["res"][1], 0, 3) != 'res')) {
                    $ret .= ' disNon';
                }
                $ret .= '">' . "\n";
            }
            
            if ($curr->nodeRow->NodeOpts%37 == 0) $ret .= '<div class="jumbotron">';
            
            if ($this->shouldPrintHalfGap($curr)) $ret .= '<div class="nodeHalfGap"></div>';
            
            if ($curr->isLayout() || $curr->isBranch()) {
            } elseif ($curr->isLoopCycle()) {
                
                list($tbl, $fld, $newVal) = $this->nodeBranchInfo($nID);
                $loop = str_replace('LoopItems::', '', $curr->nodeRow->NodeResponseSet);
                $loopCycle = $this->sessData->getLoopRows($loop);
                if (sizeof($tmpSubTier[1]) > 0 && sizeof($loopCycle) > 0) {
                    $GLOBALS["SL"]->currCyc["cyc"][0] = $GLOBALS["SL"]->getLoopTable($loop);
                    foreach ($loopCycle as $i => $loopItem) {
                        $GLOBALS["SL"]->currCyc["cyc"][1] = 'cyc' . $i;
                        $GLOBALS["SL"]->currCyc["cyc"][2] = $loopItem->getKey();
                        $this->sessData->startTmpDataBranch($tbl, $loopItem->getKey());
                        $GLOBALS["SL"]->fakeSessLoopCycle($loop, $loopItem->getKey());
                        foreach ($tmpSubTier[1] as $childNode) {
                            if (!$this->allNodes[$childNode[0]]->isPage()) {
                                $ret .= $this->printNodePublic($childNode[0], $childNode, $currVisib);
                            }
                        }
                        $GLOBALS["SL"]->removeFakeSessLoopCycle($loop, $loopItem->getKey());
                        $this->sessData->endTmpDataBranch($tbl);
                        $GLOBALS["SL"]->currCyc["cyc"][1] = '';
                        $GLOBALS["SL"]->currCyc["cyc"][2] = -3;
                    }
                    $GLOBALS["SL"]->currCyc["cyc"][0] = '';
                }
                
            } elseif ($curr->isSpreadTbl()) {
                
//if ($nID == 1423) { echo '<br /><br /><br />isSpreadTbl: <pre>'; print_r($tmpSubTier[1]); echo '</pre><br />'; }
                if (sizeof($tmpSubTier[1]) > 0) {
                    $this->tableDat = $this->loadTableDat($curr, $currNodeSessData, $tmpSubTier);
//if ($nID == 1423) { echo '<br /><br /><pre>'; print_r($this->tableDat); echo '</pre>'; }
                    $GLOBALS["SL"]->currCyc["tbl"][0] = $this->tableDat["tbl"];
                    for ($i = 0; $i < $this->tableDat["maxRow"]; $i++) {
                        if ($i < sizeof($this->tableDat["rows"])) {
                            if (trim($this->tableDat["rowCol"]) != '') {
                                if ($this->tableDat["rows"][$i]["id"] <= 0 && trim($this->tableDat["rowCol"]) != '') {
                                    $recObj = $this->sessData->checkNewDataRecord($this->tableDat["tbl"], 
                                        $this->tableDat["rowCol"], $this->tableDat["rows"][$i]["leftVal"]);
                                    if ($recObj) $this->tableDat["rows"][$i]["id"] = $recObj->getKey();
                                }
                            }
//if ($nID == 1423) { echo 'rowID: ' . $this->tableDat["rows"][$i]["id"] . ', table: ' . $this->tableDat["tbl"] . ', ' . $this->tableDat["rowCol"] . ', ' .$this->tableDat["rows"][$i]["leftVal"] . ' - '; print_r($this->sessData->dataBranches); echo '<br />'; }
                            $GLOBALS["SL"]->currCyc["tbl"][1] = 'tbl' . $i;
                            $GLOBALS["SL"]->currCyc["tbl"][2] = $this->tableDat["rows"][$i]["id"];
                            $this->sessData->startTmpDataBranch($this->tableDat["tbl"], 
                                $this->tableDat["rows"][$i]["id"], false);
                            foreach ($tmpSubTier[1] as $k => $kidNode) {
                                $printFld = str_replace('nFld', '', str_replace('nFld mT0', '', 
                                     $this->printNodePublic($kidNode[0], $kidNode, 1)));
                                $this->tableDat["rows"][$i]["cols"][] = $printFld;
                            }
                            $this->sessData->endTmpDataBranch($this->tableDat["tbl"]);
                            $GLOBALS["SL"]->currCyc["tbl"][1] = '';
                            $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
                        }
                    }
                    $GLOBALS["SL"]->currCyc["tbl"][1] = 'tbl?';
                    $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
                    $java = '';
                    foreach ($tmpSubTier[1] as $k => $kidNode) {
                        $this->tableDat["blnk"][$k] = str_replace('nFld', '', str_replace('nFld mT0', '', 
                            $this->printNodePublic($kidNode[0], $kidNode, 1)));
                        $java .= (($k > 0) ? ', ' : '') . $kidNode[0];
                    }
                    $GLOBALS["SL"]->pageJAVA .= 'nodeTblList[' . $nID . '] = new Array(' . $java . '); ';
                    if ($this->tableDat["req"][0]) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID') && document.getElementById('n" . $nIDtxt . "VisibleID').value == 1) {\n"
                            . "var cols = new Array(";
                        foreach ($tmpSubTier[1] as $k => $kidNode) {
                            $this->pageJSvalid .= (($k > 0) ? ", " : "") . " new Array(" . $kidNode[0] . ", " 
                                . (($this->tableDat["req"][2][$k]) ? 'true' : 'false') . ") ";
                        }
                        $this->pageJSvalid .= ");\n reqFormFldTbl(" . $nID . ", '" . $nIDtxt . "', " 
                            . $this->tableDat["maxRow"] . ", cols, " . (($this->tableDat["req"][1]) ? 'true' : 'false')
                            . "); }\n";
                    }
//if ($nID == 1423) { echo '<pre>'; print_r($this->tableDat); echo '</pre>'; }
                    $ret .= view('vendor.survloop.formtree-form-table', [
                        "nID"             => $nID,
                        "nIDtxt"          => $nIDtxt,
                        "node"            => $curr,
                        "nodePromptText"  => $nodePromptText,
                        "tableDat"        => $this->tableDat
                        ])->render();
                    $GLOBALS["SL"]->currCyc["tbl"] = ['', '', -3];
                    $this->tableDat = [];
                }
                
            } elseif ($curr->isLoopSort()) {
                
                $loop = str_replace('LoopItems::', '', $curr->nodeRow->NodeResponseSet);
                $loopCycle = $this->sessData->getLoopRows($loop);
                if (sizeof($loopCycle) > 0) {
                    $this->v["needsJqUi"] = true;
                    $GLOBALS["SL"]->pageAJAX .= '$("#sortable").sortable({ 
                        axis: "y", update: function (event, ui) {
                        var url = "/sortLoop/?n=' . $nID . '&"+$(this).sortable("serialize")+"";
                        document.getElementById("hidFrameID").src=url;
                    } }); $("#sortable").disableSelection();';
                    $ret .= '<div class="nFld">' . $this->sortableStart($nID) . '<ul id="sortableN' . $nID 
                        . '" class="slSortable">' . "\n";
                    foreach ($loopCycle as $i => $loopItem) {
                        $ret .= '<li id="item-' . $loopItem->getKey() . '" class="sortOff" onMouseOver="'
                            . 'this.className=\'sortOn\';" onMouseOut="this.className=\'sortOff\';">'
                            . '<span><i class="fa fa-sort slBlueLight"></i></span> ' 
                            . $this->getLoopItemLabel($loop, $loopItem, $i) . '</li>' . "\n";
                    }
                    $ret .= '</ul>' . $this->sortableEnd($nID) . '</div>' . "\n";
                }
                
            } elseif ($curr->isDataManip()) {
                
                $ret .= '<input type="hidden" name="dataManip' . $nIDtxt . '" value="1">';
                if ($currVisib == 1) { // run a thing on page load
                    if ($curr->nodeType == 'Data Manip: Close Sess') {
                        $this->deactivateSess($curr->nodeRow->NodeResponseSet);
                    }
                }
                
            } elseif ($curr->nodeType == 'Back Next Buttons') {
                
                $ret .= view('vendor.survloop.inc-extra-back-next-buttons', [])->render();
                
            } elseif (in_array($curr->nodeType, ['Search', 'Search Results', 'Search Featured',
                'Record Full', 'Record Full Public', 'Record Previews', 'Incomplete Sess Check', 'Member Profile Basics',
                'Plot Graph', 'Line Graph', 'Bar Graph', 'Pie Chart', 'Map', 'MFA Dialogue', 'Widget Custom'])) {
                
                $ret .= $this->printWidget($nID, $nIDtxt, $curr);
                
            } elseif (!$curr->isPage()) { // otherwise, the main Node printer...
                
                $curr = $this->customResponses($nID, $curr);
//if ($nID == 57) { echo '<br /><br /><br />after customResponses <pre>'; print_r($curr->fldHasOther); echo '</pre>'; }
                // Start normal data field checks
                $dateStr = $timeStr = '';
                if ($fld != '' && isset($GLOBALS["SL"]->tblAbbr[$tbl]) 
                    && $fld != ($GLOBALS["SL"]->tblAbbr[$tbl] . 'ID') && trim($currNodeSessData) != '' 
                    && isset($GLOBALS["SL"]->fldTypes[$tbl][$fld])) {
                    // convert current session data for dates and times
                    if ($GLOBALS["SL"]->fldTypes[$tbl][$fld] == 'DATETIME') {
                        list($dateStr, $timeStr) = explode(' ', $currNodeSessData);
                        $dateStr = $this->cleanDateVal($dateStr);
                        if (trim($dateStr) != '') $dateStr = date("m/d/Y", strtotime($dateStr));
                    } elseif ($GLOBALS["SL"]->fldTypes[$tbl][$fld] == 'DATE') {
                        $dateStr = $this->cleanDateVal($currNodeSessData);
                        if (trim($dateStr) != '') $dateStr = date("m/d/Y", strtotime($dateStr));
                    }
                    if ($dateStr == '12/31/1969') $dateStr = '';
                } // end normal data field checks
                
                $mobileCheckbox = ($curr->nodeRow->NodeOpts%2 > 0);
                
                // check if this field's label and field is to be printed on the same line
                $isOneLiner = $isOneLinerFld = '';
                if (in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
                    if ($curr->isOneLiner()) $isOneLiner = ' disIn mR20';
                    if ($curr->isOneLiner() || $curr->isOneLineResponses()) $isOneLinerFld = ' disIn mR20';
                } elseif ($curr->isOneLiner()) {
                    $isOneLiner = $isOneLinerFld = ' col-md-6';
                }
                if (trim($isOneLiner) != '') {
                    $nodePrompt = str_replace('class="nPrompt"', 'class="nPrompt' . $isOneLiner . '"', $nodePrompt);
                }
                
                if (!in_array($curr->nodeType, ['Radio', 'Checkbox', 'Instructions', 'Other/Custom'])) {
                    $this->pageFldList[] = 'n' . $nID . 'FldID';
                }
                
                // start Q&A on same row
                if ($curr->isOneLiner() && !in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
                    $ret .= '<div class="row"> <!-- start one-liner -->';
                }

                // print out each of the various field types
                if ($curr->nodeType == 'Hidden Field') {
                    
                    $ret .= $nodePrompt . '<input type="hidden" name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt 
                        . 'FldID" value="' . $currNodeSessData . '" class="' . $xtraClass . '">' . "\n"; 
                        
                } elseif ($curr->nodeType == 'Big Button') {
                    
                    $currNodeSessData = '';
                    $btn = '<div class="nFld"><a id="nBtn' . $nIDtxt . '" class="crsrPntr '
                        . (($curr->nodeRow->NodeResponseSet == 'Text') ? '' : 'btn btn-lg btn-' 
                            . (($curr->nodeRow->NodeResponseSet == 'Default') ? 'default' : 'primary') . ' nFldBtn')
                        . (($curr->nodeRow->NodeOpts%43 == 0) ? '' : ' nFormNext') . '" ' 
                        . ((trim($curr->nodeRow->NodeDataStore) != '') 
                            ? 'onClick="' . $curr->nodeRow->NodeDataStore . '"' : '') . ' >' 
                        . $curr->nodeRow->NodeDefault . '</a></div>';
                    $lastDivPos = strrpos($nodePrompt, "</div>\n            </label></div>");
                    if (strpos($nodePrompt, 'jumbotron') > 0 && $lastDivPos > 0) {
                        $ret .= substr($nodePrompt, 0, $lastDivPos) 
                            . '<center>' . $btn . '</center>' 
                            . substr($nodePrompt, $lastDivPos) 
                            . '<input type="hidden" name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt 
                            . 'FldID" value="' . $currNodeSessData . '" class="' . $xtraClass . '" data-nid="' 
                            . $nID . '">' . "\n"; 
                    } else {
                        $ret .= $nodePrompt . '<input type="hidden" name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt 
                            . 'FldID" value="' . $currNodeSessData . '" class="' . $xtraClass . '" data-nid="' 
                            . $nID . '">' . $btn . "\n"; 
                    }
                    if ($curr->nodeRow->NodeOpts%43 == 0) {
                        $this->allNodes[$nID]->hasShowKids = $curr->hasShowKids = true;
                        $childList = [];
                        if (sizeof($tmpSubTier[1]) > 0) {
                            foreach ($tmpSubTier[1] as $childNode) {
                                $childList[] = $childNode[0];
                                $this->hideKidNodes[] = $childNode[0];
                            }
                        }
                        $GLOBALS["SL"]->pageJAVA .= 'nodeKidList[' . $nID . '] = [' . implode(', ', $childList) . ']; ';
                        $GLOBALS["SL"]->pageAJAX .= '$(document).on("click", "#nBtn' . $nIDtxt . '", function() { '
                            . 'if (document.getElementById("node' . $nIDtxt . 'kids")) { '
                                . 'if (!document.getElementById("node' . $nIDtxt . 'kids").style.display '
                                    . '|| document.getElementById("node' . $nIDtxt . 'kids").style.display=="") { '
                                    . 'document.getElementById("node' . $nIDtxt . 'kids").style.display="none"; } '
                                . 'if (document.getElementById("node' . $nIDtxt . 'kids").style.display=="none") { '
                                    . 'kidsVisible("' . $nIDtxt . '", "' . $nSffx . '", true); '
                                    . 'kidsDisplaySkip("' . $nIDtxt . '", "' . $nSffx . '", true); '
                                    . '$("#node' . $nIDtxt . 'kids").slideDown("50"); '
                                . '} else { '
                                    . '$("#node' . $nIDtxt . 'kids").slideUp("50"); '
                                    . 'kidsVisible("' . $nIDtxt . '", "' . $nSffx . '", false); '
                                    . 'setTimeout(function() { kidsDisplaySkip("' . $nIDtxt . '", "' . $nSffx 
                                        . '", false); }, 100); '
                                . '} '
                            . '} });' . "\n";
                    }
                    
                } elseif ($curr->nodeType == 'User Sign Up') {
                    
                    $this->v["hasRegisterNode"] = true;
                    $this->pageJSvalid .= view('vendor.survloop.auth.register-node-jsValid', [
                        "coreID"         => $this->coreID
                    ])->render();
                    $ret .= view('vendor.survloop.auth.register-node', [
                        "coreID"         => $this->coreID, 
                        "anonyLogin"     => $this->isAnonyLogin(), 
                        "anonyPass"      => uniqid(),
                        "currAdmPage"    => '', 
                        "user"           => Auth::user(),
                        "inputMobileCls" => $this->inputMobileCls($nID)
                    ])->render();
                    
                } elseif (in_array($curr->nodeType, [ 'Text', 'Email', 'Spambot Honey Pot' ])) {
                    
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '"><input class="form-control form-control-lg' 
                        . $xtraClass . '" type="' . (($curr->nodeType == 'Email') ? 'email' : 'text')
                        . '" name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt . 'FldID" value="' . $currNodeSessData 
                        . '" ' . $onKeyUp . ' data-nid="' . $nID . '" ' . $GLOBALS["SL"]->tabInd() . '></div>' 
                        . $charLimit . "\n" . $this->printWordCntStuff($nIDtxt, $curr->nodeRow); 
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID') && document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) " . (($curr->nodeType == 'Email') 
                                ? "reqFormFldEmail('" . $nIDtxt . "');\n" : "reqFormFld('" . $nIDtxt . "');\n");
                    }
                    if ($curr->nodeType == 'Spambot Honey Pot') {
                        $ret .= '<script type="text/javascript"> document.getElementById("node"+"' . $nID 
                            . '").style.display="none"; </script>';
                    }
                    if (trim($curr->nodeRow->NodeTextSuggest) != '') {
                        $this->v["needsJqUi"] = true;
                        $GLOBALS["SL"]->pageAJAX .= '$( "#n' . $nIDtxt . 'FldID" ).autocomplete({ source: [';
                        foreach ($GLOBALS["SL"]->def->getSet($curr->nodeRow->NodeTextSuggest) as $i => $def) {
                            $GLOBALS["SL"]->pageAJAX .= (($i > 0) ? ',' : '') . ' ' 
                                . json_encode($def->DefValue);
                        }
                        $GLOBALS["SL"]->pageAJAX .= ' ] });' . "\n";
                    }
                    
                } elseif ($curr->nodeType == 'Long Text') {
                    
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '">
                        <textarea class="form-control form-control-lg flexarea' . $xtraClass . '" name="n' . $nIDtxt 
                        . 'fld" id="n' . $nIDtxt . 'FldID" ' . $onKeyUp . ' data-nid="' . $nID . '" ' 
                        . $GLOBALS["SL"]->tabInd() . '>' . $currNodeSessData . '</textarea></div>' . $charLimit . "\n"
                        . $this->printWordCntStuff($nIDtxt, $curr->nodeRow);
                    $GLOBALS["SL"]->pageJAVA .= "\n" . 'setTimeout("flexAreaAdjust(document.getElementById(\'n' 
                        . $nIDtxt . 'FldID\'))", 50);';
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID') && document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) reqFormFld('" . $nIDtxt . "');\n";
                    }
                    
                } elseif (in_array($curr->nodeType, [ 'Text:Number', 'Slider' ])) {
                    
                    $attrMin = $attrMax = '';
                    if (isset($curr->extraOpts["minVal"]) && $curr->extraOpts["minVal"] !== false) {
                        if (isset($curr->nodeRow->NodeDefault) 
                            && $curr->nodeRow->NodeDefault < $curr->extraOpts["minVal"]) {
                            $attrMin = 'min="' . $curr->nodeRow->NodeDefault . '" ';
                        } else {
                            $attrMin = 'min="' . $curr->extraOpts["minVal"] . '" ';
                        }
                    }
                    if (isset($curr->extraOpts["maxVal"]) && $curr->extraOpts["maxVal"] !== false) {
                        if (isset($curr->nodeRow->NodeDefault) 
                            && $curr->nodeRow->NodeDefault > $curr->extraOpts["maxVal"]) {
                            $attrMax = 'max="' . $curr->nodeRow->NodeDefault . '" ';
                        } else {
                            $attrMax = 'max="' . $curr->extraOpts["maxVal"] . '" ';
                        }
                    }
                    $attrIncr = 'step="any" ';
                    if (isset($curr->extraOpts["incr"]) && $curr->extraOpts["incr"] > 0) {
                        $attrIncr = 'step="' . $curr->extraOpts["incr"] . '" ';
                    }
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '">';
                    if (!$this->hasSpreadsheetParent($nID)) $ret .= '<div class="row"><div class="col-md-3">';
                    $ret .= '<nobr><input type="number" data-nid="' . $nID . '" class="form-control form-control-lg ' 
                        . (($curr->nodeType == 'Slider') ? 'slidePercFld ' : ((isset($curr->extraOpts["unit"]) 
                            && trim($curr->extraOpts["unit"]) != '') ? 'unitFld ' : '')) . $xtraClass 
                        . '" name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt . 'FldID" value="' . $currNodeSessData 
                        . '" ' . $onKeyUp . ' ' . $attrIncr . $attrMin . $attrMax . $GLOBALS["SL"]->tabInd() . '> ';
                    if (isset($curr->extraOpts["unit"]) && trim($curr->extraOpts["unit"]) != '') {
                        if ($curr->nodeType == 'Text:Number' && !$this->hasSpreadsheetParent($nID)) {
                            $ret .= '</nobr></div><div class="col-md-3 pT10"><nobr>';
                        }
                        $ret .= $curr->extraOpts["unit"] . '&nbsp;&nbsp;';
                    }
                    $ret .= '</nobr>';
                    if (!$this->hasSpreadsheetParent($nID)) $ret .= '</div></div>';
                    if ($curr->nodeType == 'Slider') {
                        $ret .= '<div class="col-md-10 slideCol"><div id="n' . $nIDtxt 
                            . 'slider" class="ui-slider ui-slider-horizontal slSlider"></div></div>';
                    }
                    $ret .= '</div>' . "\n";
                    if ($curr->nodeType == 'Slider') {
                        $GLOBALS["SL"]->pageAJAX .= '$("#n' . $nIDtxt . 'slider").slider({ '
                            . ((isset($curr->extraOpts["incr"]) && intVal($curr->extraOpts["incr"]) > 0) 
                                ? 'step: ' . $curr->extraOpts["incr"] . ', ' : '') 
                            . 'change: function( event, ui ) {
                            var newVal = $("#n' . $nIDtxt . 'slider").slider("value");
                            document.getElementById("n' . $nIDtxt . 'FldID").value=newVal;
                        } });
                        $(document).on("keyup", "#n' . $nIDtxt . 'FldID", function() { $("#n' . $nIDtxt 
                            . 'slider").slider("value", document.getElementById("n' . $nIDtxt . 'FldID").value); }); 
                        setTimeout(function() { $("#n' . $nIDtxt . 'slider").slider("value", document.getElementById("n' 
                            . $nIDtxt . 'FldID").value); }, 5); ';
                    }
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID') && document.getElementById('n" . $nIDtxt . "VisibleID').value == 1) { ";
                        if ((!isset($curr->extraOpts["minVal"]) || $curr->extraOpts["minVal"] === false)
                            && (!isset($curr->extraOpts["maxVal"]) || $curr->extraOpts["maxVal"] === false)) {
                            $this->pageJSvalid .= "reqFormFld('" . $nIDtxt . "');";
                        } else {
                            if (isset($curr->extraOpts["minVal"]) && $curr->extraOpts["minVal"] !== false) {
                                $this->pageJSvalid .= "reqFormFldGreater('" . $nIDtxt . "', " 
                                    . $curr->extraOpts["minVal"] . ");";
                            }
                            if (isset($curr->extraOpts["maxVal"]) && $curr->extraOpts["maxVal"] !== false) {
                                $this->pageJSvalid .= "reqFormFldLesser('" . $nIDtxt . "', " 
                                    . $curr->extraOpts["maxVal"] . ");";
                            }
                        }
                        $this->pageJSvalid .= " }\n";
                    }
                    
                } elseif ($curr->nodeType == 'Password') {
                    
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld 
                        . '"><input type="password" name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt 
                        . 'FldID" value="" ' . $onKeyUp . ' autocomplete="off" class="form-control form-control-lg' 
                        . $xtraClass . '" data-nid="' . $nID . '"' . $GLOBALS["SL"]->tabInd() . '></div>' 
                        . $charLimit . "\n"; 
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID') && document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) reqFormFld('" . $nIDtxt . "');\n";
                    }
                    
                } elseif (in_array($curr->nodeType, ['Drop Down', 'U.S. States', 'Countries'])) {
                    
                    $curr = $this->checkResponses($curr, $fldForeignTbl);
                    if (sizeof($curr->responses) > 0 || in_array($curr->nodeType, ['U.S. States', 'Countries'])) {
                        $ret .= $nodePrompt . "\n" . '<div class="nFld' . $isOneLinerFld . '"><select name="n' 
                            . $nIDtxt . 'fld" id="n' . $nIDtxt . 'FldID" data-nid="' . $nID 
                            . '" class="form-control form-control-lg' . (($isOneLinerFld != '') ? ' w33' : '') 
                            . $xtraClass . '" onChange="checkNodeUp(\'' . $nIDtxt . '\', -1, 0);' 
                            . (($curr->isDropdownTagger()) ? ' selectTag(' . $nID 
                                . ', this.value); this.value=\'\';' : '') . '" autocomplete="off" ' 
                            . $GLOBALS["SL"]->tabInd() . '>'
                            . '<option class="slGrey" value=""' . ((trim($currNodeSessData) == '' 
                                || $curr->isDropdownTagger()) ? ' SELECTED' : '') . ' >'
                            . ((isset($curr->nodeRow->NodeTextSuggest)) ? $curr->nodeRow->NodeTextSuggest : '') 
                            . '</option>' . "\n";
                        if ($curr->nodeType == 'U.S. States') {
                            $GLOBALS["SL"]->loadStates();
                            $ret .= $GLOBALS["SL"]->states->stateDrop($currNodeSessData);
                        } else {
                            foreach ($curr->responses as $j => $res) {
                                $select = $this->isCurrDataSelected($currNodeSessData, $res->NodeResValue, 
                                    $curr->nodeType);
                                $ret .= '<option value="' . $res->NodeResValue . '" ' 
                                    . (($select && !$curr->isDropdownTagger()) ? 'SELECTED' : '') 
                                    . ' >' . $res->NodeResEng . '</option>' . "\n";
                                if ($curr->isDropdownTagger()) {
                                    $GLOBALS["SL"]->pageJAVA .= "\n" . 'addTagOpt(' . $nID . ', ' 
                                        . $res->NodeResValue . ', ' . json_encode($res->NodeResEng) . ', ' 
                                        . (($select) ? 1 : 0) . ');';
                                }
                            }
                        }
                        if ($curr->isRequired()) {
                            $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                                . "VisibleID') && document.getElementById('n" . $nIDtxt 
                                . "VisibleID').value == 1) reqFormFld('" . $nIDtxt . "');\n";
                        }
                        $ret .= '</select></div>' . "\n"; 
                        if ($curr->isDropdownTagger()) {
                            $ret .= '<input type="hidden" name="n' . $nID . 'tagIDs" id="n' . $nID 
                                . 'tagIDsID" value="," class="' . $xtraClass . '" data-nid="' . $nID 
                                . '"><div id="n' . $nID . 'tags" class="slTagList"></div>';
                            $GLOBALS["SL"]->pageJAVA .= "\n" . 'setTimeout("updateTagList(' . $nID . ')", 50);';
                        }
                    }
                    
                } elseif (in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
                    
                    $curr = $this->checkResponses($curr, $fldForeignTbl);
//if ($nID == 585) { echo '<br /><br /><br />ummm? ' . $nID . ', cyc: ' . trim($GLOBALS["SL"]->currCyc["cyc"][1]) . ', curr: '; print_r($currNodeSessData); echo '<br />'; }
//if ($nID == 585) { echo '<pre>'; print_r($nodePrompt); print_r($curr->responses); echo '</pre>'; }
                    if ($curr->nodeType == 'Radio') {
                        $ret .= '<input type="hidden" name="n' . $nIDtxt . 'radioCurr" id="n' . $nIDtxt 
                            . 'radioCurrID" value="';
                        if (!is_array($currNodeSessData)) {
                            $ret .= $currNodeSessData;
                        } elseif (sizeof($currNodeSessData) > 0) {
                            foreach ($currNodeSessData as $d) {
                                if (strpos($d, trim($GLOBALS["SL"]->currCyc["cyc"][1])) === 0) $ret .= $d;
                            }
                        }
                        $ret .= '">';
                        $GLOBALS["SL"]->pageJAVA .= "\n" . 'addRadioNode(' . $nID . ');';
                    }
                    if (sizeof($curr->responses) > 0) {
                        $ret .= (($curr->isOneLiner()) ? '<div class="pB20">' : '') 
                            . str_replace('<label for="n' . $nIDtxt . 'FldID">', '', 
                                str_replace('</label>', '', $nodePrompt))
                            . '<div class="nFld';
                        if ($this->hasSpreadsheetParent($nID)) $ret .= '">' . "\n";
                        elseif ($mobileCheckbox) $ret .= '" style="margin-top: 20px;">' . "\n";
                        else $ret .= $isOneLiner . ' pB0 mBn5">' . "\n";
                        $respKids = ' data-nid="' . $nID . '" class="nCbox' . $nID . ' ' . $xtraClass 
                            . (($curr->hasShowKids) ? ' n' . $nIDtxt . 'fldCls' : '') . '"'; 
                        
                            // onClick="return check' . $nID . 'Kids();"
                        $onClickXtra = '';
                        $GLOBALS["SL"]->pageJAVA .= "\n" . 'addResTot("' . $nID . '", ' 
                            . sizeof($curr->responses) . ');';
                        if ($curr->nodeRow->NodeOpts%79 == 0) {
                            $onClickXtra = 'chkRadioHide(\'' . $nIDtxt . '\'); ';
                            $GLOBALS["SL"]->pageJAVA .= "\n" . 'setTimeout("' . $onClickXtra . '", 100);';
                        }
                        if ($curr->nodeRow->NodeOpts%61 == 0) {
                            $ret .= '<div class="row">';
                            $mobileCheckbox = true;
                        }
//if ($nID == 585) { echo '<pre>'; print_r($curr->fldHasOther); echo '</pre>'; }
                        foreach ($curr->responses as $j => $res) {
                            $otherFld = ['', '', '', ''];
                            if (in_array($j, $curr->fldHasOther)) {
                                $otherFld[0] = $fld . 'Other';
                                $fldVals = [ $fld => $res->NodeResValue ];
                                $s = sizeof($this->sessData->dataBranches);
                                if ($s > 0 && intVal($this->sessData->dataBranches[$s-1]["itemID"]) > 0) {
                                    $tbl2 = $this->sessData->dataBranches[$s-1]["branch"];
                                    $branchLnkFld = $GLOBALS["SL"]->getForeignLnkNameFldName($tbl, $tbl2);
                                    if ($branchLnkFld != '') {
                                        $fldVals[$branchLnkFld] = $this->sessData->dataBranches[$s-1]["itemID"];
                                    }
                                }
                                $subRowIDs = $this->sessData->getRowIDsByFldVal($tbl, $fldVals);
                                $branchRowID = ((sizeof($subRowIDs) > 0) ? $subRowIDs[0] : -3);
//if ($nID == 585) { echo 'branchRowID: ' . $branchRowID . '<br />'; }
                                if ($branchRowID > 0) {
                                    $GLOBALS["SL"]->currCyc["res"] = [$tbl, 'res' . $j, $res->NodeResValue];
                                    $this->sessData->startTmpDataBranch($tbl, $branchRowID);
                                    $otherFld[1] = $this->sessData->currSessData($nID, $tbl, $otherFld[0], 'get', '', 
                                        $hasParManip);
                                    $this->sessData->endTmpDataBranch($tbl);
                                    $GLOBALS["SL"]->currCyc["res"] = ['', '', -3];                                    
                                } else {
                                    $otherFld[1] = '';
                                }
                                $otherFld[2] = '<input type="text" name="n' . $nID . 'fldOther' . $j . '" id="n' 
                                    . $nID . 'fldOtherID' . $j . '" value="' . $otherFld[1] 
                                    . '" class="form-control disIn ntrStp slTab otherFld mL10"' 
                                    . $GLOBALS["SL"]->tabInd() . '>';
                            }
//if ($nID == 57) { echo 'otherFld: <pre>'; print_r($otherFld); echo '</pre>'; }
                            
                            if ($curr->nodeType == 'Checkbox' && $curr->indexMutEx($j)) {
                                $GLOBALS["SL"]->pageJAVA .= "\n" . 'addMutEx(' . $nID . ', ' . $j . ');';
                            }
                            $this->pageFldList[] = 'n' . $nIDtxt . 'fld' . $j;
                            $resNameCheck = '';
                            $boxChecked = $this->isCurrDataSelected($currNodeSessData, $res->NodeResValue, 
                                $curr->nodeType);
                            if ($curr->nodeType == 'Radio') {
                                $resNameCheck = 'name="n' . $nIDtxt . 'fld" ' . (($boxChecked) ? 'CHECKED' : '');
                                if (sizeof($curr->fldHasOther) > 0 && $otherFld[1] == '') {
                                    $otherFld[3] = ' document.getElementById(\'n' . $nID . 'fldOtherID' . $j 
                                        . '\').value=\'\'; ';
                                }
                            } else {
                                $resNameCheck = 'name="n' . $nIDtxt . 'fld[]" ' 
                                    . (($boxChecked) ? 'CHECKED' : '');
                            }
                            
                            if ($curr->nodeRow->NodeOpts%61 == 0) {
                                $ret .= '<div class="col-md-' . $GLOBALS["SL"]->getColsWidth(sizeof($curr->responses)) 
                                    . '">';
                            }
                            if ($mobileCheckbox) {
                                $ret .= '<label for="n' . $nIDtxt . 'fld' . $j . '" id="n' . $nIDtxt . 'fld' . $j 
                                    . 'lab" class="finger' . (($boxChecked) ? 'Act' : '') 
                                    . '"><div class="disIn mR5"><input id="n' . $nIDtxt . 'fld' . $j . '" value="' 
                                    . $res->NodeResValue . '" type="' . strtolower($curr->nodeType) . '" ' 
                                    . $resNameCheck . $respKids . ' autocomplete="off" onClick="checkNodeUp(\'' 
                                    . $nIDtxt . '\', ' . $j . ', 1);' . $otherFld[3] . $onClickXtra 
                                    . '" ' . $GLOBALS["SL"]->tabInd() . '></div> ' . $res->NodeResEng . ' ' 
                                    . $otherFld[2] . '</label>' . "\n";
                            } else {
                                $ret .= '<div class="' . $isOneLinerFld . '">' . ((strlen($res) < 40) ? '<nobr>' : '') 
                                    . '<label for="n' . $nIDtxt . 'fld' . $j 
                                    . '" class="mR10"><div class="disIn mR5"><input id="n' . $nIDtxt . 'fld' . $j 
                                    . '" value="' . $res->NodeResValue . '" type="' . strtolower($curr->nodeType) . '" '
                                    . $resNameCheck . $respKids . ' autocomplete="off" onClick="checkNodeUp(\'' 
                                    . $nIDtxt . '\', ' . $j . ', 0);' . $otherFld[3] . $onClickXtra . '"' 
                                    . $GLOBALS["SL"]->tabInd() . '></div> ' . $res->NodeResEng . ' ' . $otherFld[2] 
                                    . '</label>' . ((strlen($res) < 40) ? '</nobr>' : '') . '</div>' . "\n";
                            }
                            if ($curr->nodeRow->NodeOpts%61 == 0) {
                                $ret .= '</div> <!-- end col -->' . "\n";
                            }
                            // Check for Layout Sub-Response between each Checkbox Response
                            if ($curr->nodeType == 'Checkbox' && sizeof($tmpSubTier[1]) > 0) {
                                foreach ($tmpSubTier[1] as $childNode) {
                                    if ($this->allNodes[$childNode[0]]->nodeType == 'Layout Sub-Response' 
                                        && sizeof($childNode[1]) > 0) {
                                        $ret .= '<div id="n' . $nIDtxt . 'fld' . $j . 'sub" class="subRes '
                                            . (($boxChecked) ? 'disBlo' : 'disNon') . '" >';
                                        $GLOBALS["SL"]->currCyc["res"][0] = $tbl;
                                        $GLOBALS["SL"]->currCyc["res"][1] = 'res' . $j;
                                        $GLOBALS["SL"]->currCyc["res"][2] = $res->NodeResValue;
                                        $subRowIDs = $this->sessData->getRowIDsByFldVal($tbl, 
                                            [ $fld => $res->NodeResValue ]);
                                        $branchRowID = ((sizeof($subRowIDs) > 0) ? $subRowIDs[0] : -3);
                                        if ($branchRowID > 0) {
                                            $this->sessData->startTmpDataBranch($tbl, $branchRowID);
                                        }
                                        $grankids = '';
                                        foreach ($childNode[1] as $k => $granNode) {
                                            $grankids .= (($k > 0) ? ', ' : '') . $granNode[0];
                                            $ret .= $this->printNodePublic($granNode[0], $granNode, $boxChecked);
                                        }
                                        if ($branchRowID > 0) $this->sessData->endTmpDataBranch($tbl);
                                        $GLOBALS["SL"]->currCyc["res"] = ['', '', -3];
                                        $ret .= '</div>';
                                        $GLOBALS["SL"]->pageAJAX .= view(
                                            'vendor.survloop.formtree-form-sub-response-ajax', [
                                            "nID"      => $nID,
                                            "nSffx"    => $nSffx,
                                            "nIDtxt"   => $nIDtxt,
                                            "j"        => $j,
                                            "grankids" => $grankids
                                            ])->render();
                                    }
                                }
                            }
                            if ($curr->nodeType == 'Checkbox' && in_array($j, $curr->fldHasOther)) {
                                $GLOBALS["SL"]->pageAJAX .= '$(document).on("keyup", "#n' . $nIDtxt . 'fldOtherID' . $j 
                                    . '", function() { if (document.getElementById("n' . $nIDtxt . 'fldOtherID' . $j 
                                    . '").value.trim() != "") { formKeyUpOther(\'' . $nID . '\', ' . $j . '); } });';
                                    // chkSubRes' . $nIDtxt . 'j' . $j . '();
                            }
                        }
                        if ($curr->isRequired()) {
                            $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                                . "VisibleID') && document.getElementById('n" . $nIDtxt 
                                . "VisibleID').value == 1) reqFormFldRadio('" . $nIDtxt . "', " 
                                . sizeof($curr->responses) . ");\n";
                        }
                        
                        if ($curr->nodeRow->NodeOpts%61 == 0) {
                            $ret .= '</div> <!-- end row -->';
                        }
                        if ($curr->nodeRow->NodeOpts%79 == 0) {
                            $ret .= '<div id="radioUnHide' . $nIDtxt . '" class="disNon"><a onClick="radioUnHide(\'' 
                                . $nIDtxt . '\');" class="btn btn-secondary btn-sm opac66" href="javascript:;" '
                                . '>Show All Options Again</a></div>';
                        }
                        
                        $ret .= '</div>' . (($curr->isOneLiner()) ? '</div>' : '') . "\n"; 
                    }
                    
                } elseif ($curr->nodeType == 'Date') {
                    
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '">' 
                        . $this->formDate($nIDtxt, $dateStr, $xtraClass) . '</div>' . "\n";
                    if ($this->nodeHasDateRestriction($curr->nodeRow)) { // then enforce time validation
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt
                            . "VisibleID') && document.getElementById('n" . $nIDtxt
                            . "VisibleID').value == 1) reqFormFldDate" 
                            . (($curr->isRequired()) ? "And" : "") . "Limit('" . $nIDtxt . "', " 
                            . $curr->nodeRow->NodeCharLimit . ", '" . date("Y-m-d") . "', 1);\n";
                    } elseif ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID') && document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) reqFormFldDate('" . $nIDtxt . "');\n";
                    }
                    
                } elseif ($curr->nodeType == 'Date Picker') {
                    
                    $this->v["needsJqUi"] = true;
                    $GLOBALS["SL"]->pageAJAX .= '$( "#n' . $nIDtxt . 'FldID" ).datepicker({ maxDate: "+0d" });';
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '"><input name="n' . $nIDtxt 
                        . 'fld" id="n' . $nIDtxt . 'FldID" value="' . $dateStr . '" autocomplete="off" ' 
                        . $onKeyUp . ' type="text" class="dateFld form-control form-control-lg' . $xtraClass 
                        . '" data-nid="' . $nID . '"' . $GLOBALS["SL"]->tabInd() . '></div>' . "\n";
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID') && document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) reqFormFld('" . $nIDtxt . "');\n";
                    }
                    
                } elseif ($curr->nodeType == 'Time') {
                    
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldHrID'; 
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldMinID';
                    $ret .= str_replace('<label for="n' . $nIDtxt . 'FldID">', '<label for="n' . $nIDtxt 
                        . 'fldHrID"><label for="n' . $nIDtxt . 'fldMinID"><label for="n' . $nIDtxt 
                        . 'fldPMID">', str_replace('</label>', '</label></label></label>', $nodePrompt)) 
                        . '<div class="nFld' . $isOneLinerFld . '">' 
                        . $this->formTime($nIDtxt, $timeStr, $xtraClass) . '</div>' . "\n";
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID') && document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) reqFormFld('" . $nIDtxt . "');\n";
                    }
                    
                } elseif ($curr->nodeType == 'Date Time') {
                    
                    $GLOBALS["SL"]->pageAJAX .= '$( "#n' . $nID . 'FldID" ).datepicker({ maxDate: "+0d" });';
                    $ret .= view('vendor.survloop.formtree-form-datetime', [
                        "nID"            => $nIDtxt,
                        "dateStr"        => $dateStr,
                        "onKeyUp"        => $onKeyUp,
                        "isOneLinerFld"  => $isOneLinerFld,
                        "xtraClass"      => $xtraClass,
                        "inputMobileCls" => $this->inputMobileCls($nID),
                        "formTime"       => $this->formTime($nID, $timeStr),
                        "nodePrompt"     => str_replace('<label for="n' . $nIDtxt . 'FldID">', 
                            '<label for="n' . $nIDtxt . 'FldID"><label for="n' . $nIDtxt 
                            . 'fldHrID"><label for="n' . $nIDtxt . 'fldMinID"><label for="n' . $nIDtxt 
                            . 'fldPMID">', str_replace('</label>', '</label></label></label></label>', 
                                $nodePrompt))
                        ])->render();
                    $this->pageFldList[] = 'n' . $nIDtxt . 'FldID'; 
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldHrID'; 
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldMinID';
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt
                            . "VisibleID') && document.getElementById('n" . $nIDtxt
                            . "VisibleID').value == 1) reqFormFld('" . $nIDtxt . "');\n";
                    }
                    
                } elseif ($curr->nodeType == 'Feet Inches') {
                    
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldFeetID'; 
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldInchID';
                    $feet = ($currNodeSessData > 0) ? floor($currNodeSessData/12) : 0; 
                    $inch = ($currNodeSessData > 0) ? intVal($currNodeSessData)%12 : 0;
                    $ret .= view('vendor.survloop.formtree-form-feetinch', [
                        "nID"              => $nIDtxt,
                        "feet"             => $feet,
                        "inch"             => $inch,
                        "isOneLinerFld"    => $isOneLinerFld,
                        "xtraClass"        => $xtraClass,
                        "currNodeSessData" => $currNodeSessData,
                        "inputMobileCls"   => $this->inputMobileCls($nID),
                        "nodePrompt"       => str_replace('<label for="n' . $nIDtxt . 'FldID">', 
                            '<label for="n' . $nIDtxt . 'fldFeetID"><label for="n' . $nIDtxt . 'fldInchID">', 
                            str_replace('</label>', '</label></label>', $nodePrompt))
                        ])->render();
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID') && document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) formRequireFeetInches('" . $nIDtxt . "');\n";
                    }
                    
                } elseif (in_array($curr->nodeType, ['Gender', 'Gender Not Sure'])) {
                    
                    $currSessDataOther = $this->sessData->currSessData($nID, $tbl, $fld . 'Other');
                    $coreResponses = [ ["F", "Female"], ["M", "Male"], ["O", "Other: "] ];
                    if ($curr->nodeType == 'Gender Not Sure') $coreResponses[] = ["?", "Not Sure"];
                    foreach ($coreResponses as $j => $res) $this->pageFldList[] = 'n' . $nIDtxt . 'fld' . $j;
                    $ret .= view('vendor.survloop.formtree-form-gender', [
                        "nID"               => $nIDtxt,
                        "nodeRow"           => $curr->nodeRow,
                        "nodePromptText"    => $nodePromptText,
                        "nodePromptNotes"   => $nodePromptNotes,
                        "isOneLinerFld"     => $isOneLinerFld,
                        "xtraClass"         => $xtraClass,
                        "coreResponses"     => $coreResponses,
                        "currNodeSessData"  => $currNodeSessData,
                        "currSessDataOther" => $currSessDataOther
                        ])->render();
                    $genderSuggest = '';
                    foreach ($GLOBALS["SL"]->def->getSet('Gender Identity') as $i => $gen) {
                        if (!in_array($gen->DefValue, ['Female', 'Male', 'Other', 'Not sure'])) {
                            $genderSuggest .= ', "' . $gen->DefValue . '"';
                        }
                    }
                    $this->v["needsJqUi"] = true;
                    $GLOBALS["SL"]->pageAJAX .= '$( "#n' . $nIDtxt . 'fldOtherID' . $j 
                        . '" ).autocomplete({ source: [' . substr($genderSuggest, 1) . '] });' . "\n";
                    $GLOBALS["SL"]->pageJAVA .= 'nodeResTot[' . $nID . '] = ' . sizeof($coreResponses) . '; ';
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID') && document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) formRequireGender(" . $nIDtxt . ");\n";
                    }
                    
                } elseif ($curr->nodeType == 'Uploads') {
                    
                    $this->pageHasUpload[] = $nID;
                    $ret .= $nodePrompt . '<div class="nFld">' . $this->uploadTool($nID) . '</div>';
                    
                } else { // instruction only
                    
                    $ret .= "\n" . $nodePrompt . "\n";
                    
                } // end node input field types
                
                // copy input to extra div?
                if (in_array($curr->nodeType, ['Text', 'Text:Number']) && $curr->nodeRow->NodeOpts%41 == 0) { 
                    $GLOBALS["SL"]->pageJAVA .= "\n" . 'setTimeout("copyNodeResponse(\'n' . $nIDtxt 
                        . 'FldID\', \'nodeEcho' . $nIDtxt . '\')", 50);';
                    $GLOBALS["SL"]->pageAJAX .= '$("#n' . $nIDtxt . 'FldID").keyup(function() { copyNodeResponse(\'n' 
                        . $nIDtxt . 'FldID\', \'nodeEcho' . $nIDtxt . '\'); });' . "\n";
                }

//if ($nID == 588) { echo '<pre>'; print_r($this->kidMaps[$nID]); echo '</pre>'; }
                if ($curr->hasShowKids && isset($this->kidMaps[$nID]) && sizeof($this->kidMaps[$nID]) > 0) {
                    if (!isset($this->v["nodeKidFunks"])) $this->v["nodeKidFunks"] = '';
                    $this->v["nodeKidFunks"] .= 'checkNodeKids' . $nIDtxt . '(); ';
                    $GLOBALS["SL"]->pageAJAX .= 'function checkNodeKids' . $nIDtxt . '() { var showKids = false; ';
                    foreach ($this->kidMaps[$nID] as $nKid => $ress) {
                        $nKidTxt = trim($nKid . $nSffx);
                        if ($ress && sizeof($ress) > 0) {
                            $GLOBALS["SL"]->pageAJAX .= ' if (';
                            foreach ($ress as $cnt => $res) {
                                if (in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
                                    $GLOBALS["SL"]->pageAJAX .= (($cnt > 0) ? ' || ' : '') 
                                        . '(document.getElementById("n' . $nIDtxt . 'fld' . $res[0] 
                                        . '") && document.getElementById("n' . $nIDtxt . 'fld' . $res[0] 
                                        . '").checked)';
                                } else {
                                    $GLOBALS["SL"]->pageAJAX .= (($cnt > 0) ? ' || ' : '')
                                        . '(document.getElementById("n' . $nIDtxt . 'FldID") && '
                                        . 'document.getElementById("n' . $nIDtxt . 'FldID").value == "' 
                                        . $res[1] . '")';
                                }
                            }
//if ($nID == 575) { echo '<br /><br /><pre>'; print_r($showMoreNodes[$nKid]); echo '</pre>'; }
                            $GLOBALS["SL"]->pageAJAX .= ') { showKids = true; ';
                            if (isset($showMoreNodes[$nKid]) && sizeof($showMoreNodes[$nKid]) > 0) {
                                foreach ($showMoreNodes[$nKid] as $grandNode) {
                                    $GLOBALS["SL"]->pageAJAX .= 'styBlock("node' . $grandNode . $nSffx 
                                        . '"); styBlock("node' . $grandNode . $nSffx 
                                        . 'kids"); setNodeVisib("' . $grandNode . '", "' . $nSffx 
                                        . '", true); ';
                                }
                            }
                            $GLOBALS["SL"]->pageAJAX .= 'styBlock("node' . $nKid . $nSffx 
                                . '"); styBlock("node' . $nKid . $nSffx 
                                . 'kids"); setNodeVisib("' . $nKid . '", "' . $nSffx 
                                . '", true); } else { setNodeVisib("' . $nKid . '", "' . $nSffx 
                                . '", false); $("#node' . $nKid . $nSffx . '").slideUp("50"); ';
                            if (isset($showMoreNodes[$nKid]) && sizeof($showMoreNodes[$nKid]) > 0) {
                                foreach ($showMoreNodes[$nKid] as $grandNode) {
                                    $GLOBALS["SL"]->pageAJAX .= '$("#node' . $grandNode . $nSffx
                                        . '").slideUp("50"); setNodeVisib("' . $grandNode . '", "' 
                                        . $nSffx . '", false); ';
                                }
                            }
                            $GLOBALS["SL"]->pageAJAX .= '} ';
                        }
                    }
                    $GLOBALS["SL"]->pageAJAX .= 'if (showKids) { $("#node' . $nIDtxt 
                        . 'kids").slideDown("50"); } else { $("#node' . $nIDtxt . 'kids").slideUp("50"); } ';
                    if (in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
                        $GLOBALS["SL"]->pageAJAX .= '} $(".n' . $nIDtxt 
                            . 'fldCls").click(function(){ checkAllNodeKids(); }); ';
                    } else {
                        $GLOBALS["SL"]->pageAJAX .= '} $(document).on("change", "#n' . $nIDtxt 
                            . 'FldID", function(){ checkAllNodeKids(); }); ';
                    }
                }
                
                // end Q&A on same row
                if ($curr->isOneLiner() && !in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
                    $ret .= '</div> <!-- end one-liner -->';
                }
                
            } // end main Node printer
            
            if (trim($promptNotesSpecial) != '') {
                $ret .= $this->printSpecial($nID, $promptNotesSpecial, $currNodeSessData);
            }
            
            $ret .= $nodePromptAfter;
            if ($this->shouldPrintHalfGap($curr)) $ret .= '<div class="nodeHalfGap"></div>';
            
            $retKids = '';
            if (sizeof($tmpSubTier[1]) > 0 && !$curr->isLoopCycle() && !$curr->isSpreadTbl()) {
                if ($curr->nodeType == 'Big Button' && $curr->nodeRow->NodeOpts%43 == 0) $currVisib = 0;
                $retKids .= '<div id="node' . $nID . 'kids" class="'
                    . (($currVisib == 0) ? 'disNon' : (($curr->nodeType == 'Layout Row') ? 'disFlx row' : 'disBlo'))
                    . (($curr->nodeType == 'Gallery Slider') ? ' h50 ovrNo' : '') . '">';
                if ($curr->isGraph()) $this->v["graphFilters"] = $nID;
                foreach ($tmpSubTier[1] as $childNode) { // recurse deez!..
                    if (!$this->allNodes[$childNode[0]]->isPage() 
                        && $this->allNodes[$childNode[0]]->nodeType != 'Layout Sub-Response') {
                        $kid = $this->printNodePublic($childNode[0], $childNode, $currVisib);
                        if (!is_array($kid)) $retKids .= $kid;
                        else $retKids .= implode(' ', $kid);
                    }
                }
                if ($curr->isGraph()) $this->v["graphFilters"] = false;
                $retKids .= '</div> <!-- end #node' . $nID . 'kids -->';
            }
            $ret .= $retKids;
            
            if ($curr->nodeType == 'Gallery Slider' && sizeof($tmpSubTier[1]) > 0) {
                $GLOBALS["SL"]->pageJAVA .= 'initGalSlider("' . $nIDtxt . '", "';
                $ret .= '<div id="sliNavDiv' . $nIDtxt . '" class="sliNavDiv">'
                    . '<a href="javascript:;" class="sliLft" id="sliLft' . $nIDtxt . '"><div id="sliLftHvr' . $nIDtxt
                    . '"></div><i class="fa fa-chevron-left" aria-hidden="true"></i></a>'
                    . '<a href="javascript:;" class="sliRgt" id="sliRgt' . $nIDtxt . '"><div id="sliRgtHvr' . $nIDtxt
                    . '"></div><i class="fa fa-chevron-right" aria-hidden="true"></i></a><div class="pT5">';
                foreach ($tmpSubTier[1] as $j => $kid) {
                    $ret .= '<a href="javascript:;" class="sliNav' . (($j == 0) ? 'Act' : '') . '" id="sliNav' . $nIDtxt
                        . 'dot' . $j . '"><i class="fa fa-dot-circle-o" aria-hidden="true"></i></a>';
                    $GLOBALS["SL"]->pageJAVA .= (($j > 0) ? ',' : '') . $kid[0];
                }
                $ret .= '</div></div>';
                $GLOBALS["SL"]->pageJAVA .= '", \'{ }\'); ' . "\n";
            }
            
            if ($curr->nodeRow->NodeOpts%37 == 0) $ret .= '</div> <!-- end jumbotron -->' . "\n";
            if (!in_array($curr->nodeType, ['Layout Row', 'Layout Column'])) {
                $ret .= "\n" . '</div> <!-- end #node' . $nIDtxt . ' -->' . "\n";
            }
            if ($GLOBALS["SL"]->treeRow->TreeType == 'Page' && $curr->parentID == $GLOBALS['SL']->treeRow->TreeRoot) {
                $ret .= '</div>' . (($curr->isPageBlockSkinny()) ? '</center>' : '') . "\n";
            }
            $ret .= '</div>'; // for blockWrap or Layout Column
            
        } // end of non-Hero Image node
        
        if ($curr->isDataManip()) $this->closeManipBranch($nID);
        $this->closePrintNodePublic($nID, $nIDtxt, $curr);
        
        return $ret;
    }
    
    protected function closePrintNodePublic($nID, $nIDtxt, $curr) { return true; }
    
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
                $ret .= $this->printSearchBar('', $widgetTreeID, trim($curr->nodeRow->NodePromptText), 
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
                        $loadURL = '/' . $wTree->TreeSlug . '-read/' . $cid . '/full?ajax=1&wdg=1'
                            . (($curr->nodeType == 'Record Full Public') ? '&publicView=1' : '');
                        $spinner = '<br /><br /><center>' . $spinner . '</center><br />';
                    }
                } elseif ($curr->nodeType == 'Search Featured') {
                    $ret .= $this->searchResultsFeatured($search, $widgetTreeID);
                } elseif ($curr->nodeType == 'Search Results') {
                    $this->getSearchFilts($GLOBALS["SL"]->REQ);
                    $loadURL = '/search-results/' . $widgetTreeID . '?s=' . urlencode($this->searchTxt) 
                        . $this->searchFiltsURL() . $this->advSearchUrlSffx;
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
    
    protected function isCurrDataSelected($currNodeSessData, $value, $nodeType = 'Text')
    {
        $selected = false;
        $resValCyc = $value . trim($GLOBALS["SL"]->currCyc["cyc"][1]);
        $resValCyc2 = trim($GLOBALS["SL"]->currCyc["cyc"][1]) . $value;
        if (is_array($currNodeSessData)) {
            $selected = (in_array($value, $currNodeSessData) || in_array($resValCyc, $currNodeSessData) 
                || in_array($resValCyc2, $currNodeSessData));
        } else {
            if ($nodeType == 'Checkbox') {
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
    
    protected function postNodePublic($nID = -3, $tmpSubTier = [])
    {
        $ret = '';
//if (in_array($nID, [15, 16])) { echo '<br /><br /><br />postNodePublic(nID: ' . $nID . ' init, PsArBldTypeOther: ' . ((isset($this->sessData->dataSets["PSAreasBlds"][0]->PsArBldTypeOther)) ? $this->sessData->dataSets["PSAreasBlds"][0]->PsArBldTypeOther : '') . '<div class="fPerc66 slGrey">'; print_r($this->sessData->dataBranches); echo '</div>'; }
//if (in_array($nID, [827, 787])) { echo '<br /><br /><br />post postNodePublic(' . $nID . ' A<br />'; }
        if (!$this->checkNodeConditions($nID)) return '';
        if (empty($tmpSubTier)) $tmpSubTier = $this->loadNodeSubTier($nID);
        $this->allNodes[$nID]->fillNodeRow($nID);
        $curr = $this->allNodes[$nID];
        $this->openPostNodePublic($nID, $tmpSubTier, $curr);
        if ($curr->isLayout()) {
            if (sizeof($tmpSubTier[1]) > 0) {
                foreach ($tmpSubTier[1] as $childNode) {
                    if (!$this->allNodes[$childNode[0]]->isPage()) {
                        $ret .= $this->postNodePublic($childNode[0], $childNode);
                    }
                }
            }
            $this->closePostNodePublic($nID, $tmpSubTier, $curr);
            return $ret;
        }
        $nSffx = $GLOBALS["SL"]->getCycSffx();
        if ($this->chkKidMapTrue($nID) == -1) {
            $this->closePostNodePublic($nID, $tmpSubTier, $curr);
            return '';
        }
        $currVisib = ($GLOBALS["SL"]->REQ->has('n' . $nID . 'Visible') 
            && intVal($GLOBALS["SL"]->REQ->input('n' . $nID . 'Visible')) == 1);
//if (in_array($nID, [827, 787])) { echo 'post postNodePublic(' . $nID . ', type: ' . $curr->nodeType . ', currVisib: ' . (($currVisib) ? 'true' : 'false') . '<br />'; }
        // Check for and process special page forms
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page' && $this->allNodes[$nID]->nodeType == 'Page') {
            if ($GLOBALS["SL"]->treeRow->TreeOpts%19 == 0) {
                $ret .= $this->processContactForm($nID, $tmpSubTier);
            }
        }
        if (isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl])) {
            $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->update(["updated_at" => date("Y-m-d H:i:s")]);
        }
        if ($curr->isLoopSort()) { // actual storage happens with with each change /loopSort/
            $list = '';
            $loop = str_replace('LoopItems::', '', $curr->nodeRow->NodeResponseSet);
            $loopCycle = $this->sessData->getLoopRows($loop);
            if (sizeof($loopCycle) > 0) {
                foreach ($loopCycle as $i => $loopItem) $list .= ','.$loopItem->getKey();
            }
            $this->sessData->logDataSave($nID, $loop, -3, 'Sorting ' . $loop . ' Items', $list);
            $this->closePostNodePublic($nID, $tmpSubTier, $curr);
            return '';
        }
        if ($this->allNodes[$nID]->isPage() || $this->allNodes[$nID]->isLoopRoot()) {
            // then we're at the page's root, so let's check this once
            if ($GLOBALS["SL"]->REQ->has('delItem') && sizeof($GLOBALS["SL"]->REQ->input('delItem')) > 0) {
                foreach ($GLOBALS["SL"]->REQ->input('delItem') as $delID) {
                    $loopTable = $GLOBALS["SL"]->closestLoop["obj"]->DataLoopTable;
                    $this->sessData->deleteDataItem($GLOBALS["SL"]->REQ->node, $loopTable, $delID);
                }
            }
        }
        
        if ($curr->isDataManip()) $this->loadManipBranch($nID, $currVisib);
        $hasParManip = $this->hasParentDataManip($nID);
        
        if ($curr->isLoopCycle()) {
            
            list($tbl, $fld, $newVal) = $this->nodeBranchInfo($nID);
            $loop = str_replace('LoopItems::', '', $curr->nodeRow->NodeResponseSet);
            $loopCycle = $this->sessData->getLoopRows($loop);
            if (sizeof($tmpSubTier[1]) > 0 && sizeof($loopCycle) > 0) {
                $GLOBALS["SL"]->currCyc["cyc"][0] = $GLOBALS["SL"]->getLoopTable($loop);
                foreach ($loopCycle as $i => $loopItem) {
                    $GLOBALS["SL"]->currCyc["cyc"][1] = 'cyc' . $i;
                    $GLOBALS["SL"]->currCyc["cyc"][2] = $loopItem->getKey();
                    $this->sessData->startTmpDataBranch($tbl, $loopItem->getKey());
                    $GLOBALS["SL"]->fakeSessLoopCycle($loop, $loopItem->getKey());
                    foreach ($tmpSubTier[1] as $childNode) {
                        if (!$this->allNodes[$childNode[0]]->isPage()) {
                            $ret .= $this->postNodePublic($childNode[0], $childNode);
                        }
                    }
                    $GLOBALS["SL"]->removeFakeSessLoopCycle($loop, $loopItem->getKey());
                    $this->sessData->endTmpDataBranch($tbl);
                    $GLOBALS["SL"]->currCyc["cyc"][1] = '';
                    $GLOBALS["SL"]->currCyc["cyc"][2] = -3;
                }
                $GLOBALS["SL"]->currCyc["cyc"][0] = '';
            }
            
        } elseif ($curr->isSpreadTbl()) {
            
//if ($nID == 1423) { echo '<br /><br /><br />post isSpreadTbl(' . $nID . '<br />'; }
            if (sizeof($tmpSubTier[1]) > 0) {
                $this->tableDat = $this->loadTableDat($curr, [], $tmpSubTier);
                $GLOBALS["SL"]->currCyc["tbl"][0] = $this->tableDat["tbl"];
                for ($i = 0; $i < $this->tableDat["maxRow"]; $i++) {
//if ($nID == 1423) { echo '<br />post isSpreadTbl-A(' . $nID . ', '; print_r($this->sessData->dataBranches); echo '<br />'; }
                    $hasRow = false;
                    $fldVals = [];
                    foreach ($tmpSubTier[1] as $k => $kidNode) {
                        list($kidTbl, $kidFld) = $this->allNodes[$kidNode[0]]->getTblFld();
                        $fldVals[$kidFld] = '';
                        $tmpFldName = 'n' . $kidNode[0] . $nSffx . 'tbl' . $i . 'fld';
//if ($nID == 1423) { echo '<br />post isSpreadTbl-RowChk(' . $nID . ', kidFld: ' . $kidFld . ', tmpFldName: ' . $tmpFldName . '<br />'; }
                        if ($GLOBALS["SL"]->REQ->has($tmpFldName)) {
                            if (is_array($GLOBALS["SL"]->REQ->get($tmpFldName))) {
                                if (sizeof($GLOBALS["SL"]->REQ->get($tmpFldName)) > 0) $hasRow = true;
                            } else {
                                if (trim($GLOBALS["SL"]->REQ->get($tmpFldName)) != '') $hasRow = true;
//if ($nID == 1423) { echo 'tmpFldName: '. $tmpFldName . ', kidTbl: ' . $kidTbl . ', kidFld: ' . $kidFld . '<br />'; }
                                if ($kidTbl == $this->tableDat["tbl"]) {
                                    $fldVals[$kidFld] = trim($GLOBALS["SL"]->REQ->get($tmpFldName));
                                }
                            }
                        }
                    }
//if ($nID == 1423) { echo '<br />post isSpreadTbl-RowChk(' . $nID . ', i: ' . $i . ', hasRow: ' . (($hasRow) ? 'true' : 'false') . '<br />'; }
                    if (trim($this->tableDat["rowCol"]) != '') {
                        if (isset($this->tableDat["rows"][$i]) && isset($this->tableDat["rows"][$i]["leftVal"]) 
                            && trim($this->tableDat["rows"][$i]["leftVal"]) != '') {
                            $recObj = $this->sessData->checkNewDataRecord($this->tableDat["tbl"], 
                                $this->tableDat["rowCol"], $this->tableDat["rows"][$i]["leftVal"]);
                            if ($hasRow) {
                                if (!$recObj) {
                                    $recObj = $this->sessData->newDataRecord($this->tableDat["tbl"], 
                                        $this->tableDat["rowCol"], $this->tableDat["rows"][$i]["leftVal"], true);
                                }
                            } else { // does not have this row
                                if ($recObj && $curr->nodeRow->NodeOpts%73 > 0) {
                                    $this->sessData->deleteDataRecordByID($this->tableDat["tbl"], $recObj->getKey());
                                }
                            }
                            if ($recObj) $this->tableDat["rows"][$i]["id"] = $recObj->getKey();
                            else $this->tableDat["rows"][$i]["id"] = -3;
                        }
                    } else { // user adds rows as they go
                        if ($hasRow) {
                            $matches = $this->sessData->getRowIDsByFldVal($this->tableDat["tbl"], $fldVals, true);
//if ($nID == 1423) { echo '<br /><br /><br />checking tbl ' . $this->tableDat["tbl"] . ', fldVals:<pre>'; print_r($fldVals); print_r($matches); echo '</pre>'; }
                            if (empty($matches)) {
                                $recObj = $this->sessData->simpleNewDataRecord($this->tableDat["tbl"]);
                                if (trim($this->tableDat["loop"]) != '') {
                                    $loopLnks = $GLOBALS["SL"]->getLoopConditionLinks($this->tableDat["loop"]);
                                    if (sizeof($loopLnks) > 0) {
                                        foreach ($loopLnks as $lnk) {
                                            $recObj->{ $lnk[0] } = $lnk[1];
                                            $recObj->save();
                                        }
                                    }
                                }
                                $this->tableDat["rows"][$i]["id"] = $recObj->getKey();
                            } else {
                                $this->tableDat["rows"][$i]["id"] = -3;
                            }
                        } elseif (isset($this->tableDat["rows"][$i])) {
                            $this->sessData->deleteDataRecordByID($this->tableDat["tbl"], 
                                $this->tableDat["rows"][$i]["id"]);
                        }
                        
                        
                    }
                    if ($hasRow) {
                        $GLOBALS["SL"]->currCyc["tbl"][1] = 'tbl' . $i;
                        $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
                        if (isset($this->tableDat["rows"][$i]) && intVal($this->tableDat["rows"][$i]["id"]) > 0) {
                            $GLOBALS["SL"]->currCyc["tbl"][2] = $this->tableDat["rows"][$i]["id"];
                            $this->sessData->startTmpDataBranch($this->tableDat["tbl"], 
                                $this->tableDat["rows"][$i]["id"], false);
                        }
//if ($nID == 1423) { echo 'currCyc:<pre>'; print_r($GLOBALS["SL"]->currCyc["tbl"]); echo '</pre>'; }
//if ($nID == 1423) { echo 'post isSpreadTbl-C(' . $nID . ',  row: ' . $i . ', '; print_r($this->sessData->dataBranches); echo '<br />'; }
                        foreach ($tmpSubTier[1] as $k => $kidNode) {
                            $ret .= $this->postNodePublic($kidNode[0], $kidNode, $currVisib);
                        }
                        if (isset($this->tableDat["rows"][$i]) && intVal($this->tableDat["rows"][$i]["id"]) > 0) {
                            $this->sessData->endTmpDataBranch($this->tableDat["tbl"]);
                        }
                        $GLOBALS["SL"]->currCyc["tbl"][1] = '';
                        $GLOBALS["SL"]->currCyc["tbl"][2] = -3;
                    }
                }
                $GLOBALS["SL"]->currCyc["tbl"] = ['', '', -3];
                $this->tableDat = [];
            }
            
        } elseif (!$curr->isDataPrint()) {
            
//if (in_array($nID, [827, 787])) { echo '<br /><br /><br />post !isDataPrint(' . $nID . ', currVisib: ' . (($currVisib) ? 'true' : 'false') . ', store in ' . $curr->dataStore . '<br />'; }
            if (!$this->postNodePublicCustom($nID, $tmpSubTier)) { // then run standard post
                if ($GLOBALS["SL"]->REQ->has('loop')) {
                    $this->settingTheLoop(trim($GLOBALS["SL"]->REQ->input('loop')), 
                        intVal($GLOBALS["SL"]->REQ->loopItem));
                }
                if ($curr->nodeType == 'Uploads') {
                    if ($this->REQstep != 'autoSave') {
                        $ret .= $this->postUploadTool($nID);
                    }
                } elseif ($this->allNodes[$nID]->nodeType == 'Send Email') {
                    $ret .= $this->postNodeSendEmail($nID);
                } elseif ($curr->isDataManip()) {
//if ($nID == 557) { echo '<br /><br />post isDataManip(' . $nID . ', currVisib: ' . (($currVisib) ? 'true' : 'false') . '<br />';
                    if ($GLOBALS["SL"]->REQ->has('dataManip' . $nID . '') 
                        && intVal($GLOBALS["SL"]->REQ->input('dataManip' . $nID . '')) == 1) {
                        if ($currVisib) $this->runDataManip($nID);
                        else $this->reverseDataManip($nID);
                    }
                } elseif (strpos($curr->dataStore, ':') !== false) {
                    list($tbl, $fld) = $curr->getTblFld();
                    list($itemInd, $itemID) = $this->sessData->currSessDataPos($tbl);
                    $fldForeignTbl = $GLOBALS["SL"]->fldForeignKeyTbl($tbl, $fld);
                    if ($GLOBALS["SL"]->REQ->has('loopItem') && intVal($GLOBALS["SL"]->REQ->loopItem) == -37) {
                        // signal from previous form to start a new row in the current set
                        $this->newLoopItem($nID);
                        //$this->updateCurrNode($this->nextNode($this->currNode()));
                    } elseif (!$curr->isInstruct() && $tbl != '' && $fld != '') {
//if (in_array($nID, [827, 787])) { echo '<br /><br /><br />post(' . $nID . ', cyc: ' . $GLOBALS["SL"]->currCyc["tbl"][1] . '<br />'; print_r($this->sessData->dataBranches); echo '<br />'; }
                        $newVal = $this->getNodeFormFldBasic($nID, $curr);
//if (in_array($nID, [827, 787])) { echo '<br /><br /><br />nodeType: ' . $curr->nodeType . ', tagger: ' . (($curr->isDropdownTagger()) ? 'true' : 'false') . ', tbl: ' . $tbl . ', fld: ' . $fld . ', itemInd: ' . $itemInd . ', itemID: ' . $itemID . ', <pre>'; print_r($newVal); echo '</pre>'; }
                        if ($curr->nodeType == 'Checkbox' || $curr->isDropdownTagger()) {
                            if (sizeof($curr->responses) == 1) { // && !$GLOBALS["SL"]->isFldCheckboxHelper($fld)
                                if (is_array($newVal) && sizeof($newVal) == 1) {
                                    $this->sessData->currSessData($nID, $tbl, $fld, 'update', $newVal[0], 
                                        $hasParManip, $itemInd, $itemID);
                                } else {
                                    $tmpVal = '';
                                    $fldRow = $GLOBALS["SL"]->getFldRowFromFullName($tbl, $fld);
                                    if (isset($fldRow->FldDefault) && trim($fldRow->FldDefault) != '') {
                                        $tmpVal = $fldRow->FldDefault;
                                    }
                                    $this->sessData->currSessData($nID, $tbl, $fld, 'update', $tmpVal, 
                                        $hasParManip, $itemInd, $itemID);
                                }
                            } else {
//if (in_array($nID, [827, 787])) { echo '<br />Umm? 1 <pre>'; print_r($newVal); echo '</pre>'; }
                                $curr = $this->checkResponses($curr, $fldForeignTbl);
                                $this->sessData->currSessDataCheckbox($nID, $tbl, $fld, 'update', $newVal, $curr, 
                                    $itemInd, $itemID);
                            }
                        } else {
//if (in_array($nID, [827, 787])) { echo '<br />Umm? 2 <pre>'; print_r($newVal); echo '</pre>'; }
                            $this->sessData->currSessData($nID, $tbl, $fld, 'update', $newVal, $hasParManip, 
                                $itemInd, $itemID);
                        }
                        // Check for Layout Sub-Response between each Checkbox Response
//if (in_array($nID, [386, 341, 226, 339])) { echo 'nID: ' . $nID . ', type: ' . $curr->nodeType . ', '; print_r($curr->fldHasOther); echo '<br />'; }
                        if ($curr->nodeType == 'Checkbox' && sizeof($tmpSubTier[1]) > 0 && sizeof($newVal) > 0) {
                            foreach ($newVal as $r => $val) {
                                foreach ($tmpSubTier[1] as $childNode) {
                                    if ($this->allNodes[$childNode[0]]->nodeType == 'Layout Sub-Response' 
                                        && sizeof($childNode[1]) > 0) {
                                        foreach ($curr->responses as $j => $res) {
                                            if ($res->NodeResValue == $val) {
                                                $subRowIDs = $this->sessData->getRowIDsByFldVal($tbl, [
                                                    $fld => $res->NodeResValue
                                                    ]);
                                                if (sizeof($subRowIDs) > 0) {
                                                    $GLOBALS["SL"]->currCyc["res"][0] = $tbl;
                                                    $GLOBALS["SL"]->currCyc["res"][1] = 'res' . $j;
                                                    $GLOBALS["SL"]->currCyc["res"][2] = $res->NodeResValue;
                                                    $this->sessData->startTmpDataBranch($tbl, $subRowIDs[0]);
                                                    foreach ($childNode[1] as $k => $granNode) {
                                                        $ret .= $this->postNodePublic($granNode[0], $granNode);
                                                    }
                                                    $this->sessData->endTmpDataBranch($tbl);
                                                    $GLOBALS["SL"]->currCyc["res"] = ['', '', -3];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if (in_array($curr->nodeType, ['Checkbox', 'Radio']) && $curr->hasShowKids 
                            && isset($this->kidMaps[$nID]) && sizeof($this->kidMaps[$nID]) > 0) {
                            foreach ($this->kidMaps[$nID] as $nKid => $ress) {
                                $found = false;
                                if (sizeof($ress) > 0) {
                                    foreach ($ress as $cnt => $res) {
                                        $this->kidMaps[$nID][$nKid][$cnt][2] = false;
                                        if ($curr->nodeType == 'Checkbox' || $curr->isDropdownTagger()) {
                                            if (in_array($res[1], $newVal)) $this->kidMaps[$nID][$nKid][$cnt][2] = true;
                                        } else {
                                            if ($res[1] == $newVal) $this->kidMaps[$nID][$nKid][$cnt][2] = true;
                                        }
                                    }
                                }
                            }
                        }
//if (in_array($nID, [386, 341, 226, 339])) { echo 'nID: ' . $nID . ', type: ' . $curr->nodeType . ', '; print_r($curr->fldHasOther); echo '<br />'; }
                        if (in_array($curr->nodeType, ['Checkbox', 'Radio', 'Gender', 'Gender Not Sure'])
                            && sizeof($curr->fldHasOther) > 0) {
//if (in_array($nID, [386, 341, 226, 339])) { echo '<pre>'; print_r($curr->responses); echo '</pre>'; }
                            foreach ($curr->responses as $j => $res) {
                                if (in_array($j, $curr->fldHasOther)) {
                                    $otherVal = (($GLOBALS["SL"]->REQ->has('n' . $nID . 'fldOther' . $j)) 
                                        ? $GLOBALS["SL"]->REQ->get('n' . $nID . 'fldOther' . $j) : '');
                                    $fldVals = [ $fld => $res->NodeResValue ];
                                    $s = sizeof($this->sessData->dataBranches);
                                    if ($s > 0 && intVal($this->sessData->dataBranches[$s-1]["itemID"]) > 0) {
                                        $tbl2 = $this->sessData->dataBranches[$s-1]["branch"];
                                        $branchLnkFld = $GLOBALS["SL"]->getForeignLnkNameFldName($tbl, $tbl2);
                                        if ($branchLnkFld != '') {
                                            $fldVals[$branchLnkFld] = $this->sessData->dataBranches[$s-1]["itemID"];
                                        }
                                    }
                                    $subRowIDs = $this->sessData->getRowIDsByFldVal($tbl, $fldVals);
                                    $branchRowID = ((sizeof($subRowIDs) > 0) ? $subRowIDs[0] : -3);
//if (in_array($nID, [386, 341, 226, 339])) { echo '<br /><br /><br />nID: ' . $nID . ', ' . $res->NodeResEng . ' , ' . $branchRowID . ' , tbl: ' . $tbl . ' , fld: ' . $fld . ' => ' . $res->NodeResValue . ' ; '; print_r($subRowIDs); echo '<br />'; }
                                    if ($branchRowID > 0) {
                                        $GLOBALS["SL"]->currCyc["res"] = [$tbl, 'res' . $j, $res->NodeResValue];
                                        $this->sessData->startTmpDataBranch($tbl, $branchRowID);
//if (in_array($nID, [386, 341, 226, 339])) { echo '<br /><br /><br />nID: ' . $nID . ', ' . $tbl . ' , ' . $fld . 'Other = ' . $otherVal . '<br />'; }
//if (in_array($nID, [386, 341, 226, 339])) { echo 'nID: ' . $nID . ', dataBranches: <pre>'; print_r($this->sessData->dataBranches); echo '</pre>'; }
                                        $this->sessData->currSessData($nID, $tbl, $fld . 'Other', 'update', $otherVal, 
                                            $hasParManip);
                                        $this->sessData->endTmpDataBranch($tbl);
                                        $GLOBALS["SL"]->currCyc["res"] = ['', '', -3];                                    
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (sizeof($tmpSubTier[1]) > 0) {
                foreach ($tmpSubTier[1] as $childNode) {
                    if (!$this->allNodes[$childNode[0]]->isPage() 
                        && $this->allNodes[$childNode[0]]->nodeType != 'Layout Sub-Response') {
                        $ret .= $this->postNodePublic($childNode[0], $childNode);
                    }
                }
            }
            
        }
        
        if ($curr->isDataManip()) $this->closeManipBranch($nID);
        
//$this->tmpDebug('umm.' . $nID . '.B');
//if ($nID == 334) { echo '<pre>'; print_r($GLOBALS["SL"]->REQ->all()); echo '</pre>'; exit; }
//echo 'nID: ' . $nID . ' fini, PsArBldTypeOther: ' . ((isset($this->sessData->dataSets["PSAreasBlds"][0]->PsArBldTypeOther)) ? $this->sessData->dataSets["PSAreasBlds"][0]->PsArBldTypeOther : '');
        $this->closePostNodePublic($nID, $tmpSubTier, $curr);
        return $ret;
    }
    
    protected function openPostNodePublic($nID = -3, $tmpSubTier = [], $curr = [])  { return true; }
    protected function closePostNodePublic($nID = -3, $tmpSubTier = [], $curr = []) { return true; }
    
    protected function customResponses($nID, $curr) { return $curr; }
    
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
//if (in_array($nID, [240])) { echo 'getNodeFormFldBasic A (' . $nID . ', nIDtxt: ' . $nIDtxt . '<pre>'; print_r($newVal); echo '</pre>'; }
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
//if (in_array($nID, [240])) { echo 'getNodeFormFldBasic B (' . $nID . ', nIDtxt: ' . $nIDtxt . '<pre>'; print_r($newVal); echo '</pre>'; }
        return $newVal;
    }
        
    public function sortLoop(Request $request)
    {
        $ret = date("Y-m-d H:i:s");
        $this->survLoopInit($request, '');
        $this->loadTree();
        if ($request->has('n') && intVal($request->n) > 0) {
            $nID = intVal($request->n);
            if (isset($this->allNodes[$nID]) && $this->allNodes[$nID]->isLoopSort()) {
                $this->allNodes[$nID]->fillNodeRow();
                $loop = str_replace('LoopItems::', '', $this->allNodes[$nID]->nodeRow->NodeResponseSet);
                $loopTbl = $GLOBALS["SL"]->dataLoops[$loop]->DataLoopTable;
                $sortFld = str_replace($loopTbl . ':', '', $this->allNodes[$nID]->nodeRow->NodeDataStore);
                foreach ($GLOBALS["SL"]->REQ->input('item') as $i => $value) {
                    eval("\$recObj = " . $GLOBALS["SL"]->modelPath($loopTbl) . "::find(" . $value . ");");
                    $recObj->{ $sortFld } = $i;
                    $recObj->save();
                }
            }
            $ret .= ' ?-)';
        }
        return $ret;
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
//if ($curr->nodeID == 585) { echo '<br /><br /><br />checkResponses(' . $tbl . ', ' . $curr->responseSet . ', ' . $fldForeignTbl . '<br /><pre>'; print_r($curr->responses); echo '</pre>'; }
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
        $ret = '<input type="hidden" id="isLoopNav" name="loopNavRoot" value="'
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
    
    
    protected function getUserEmailList($userList = [])
    {
        $emaToList = [];
        if (sizeof($userList) > 0) {
            foreach ($userList as $emaTo) {
                $emaUsr = User::where('id', $emaTo)
                    ->where('email', 'NOT LIKE', 'anonymous.%@anonymous.org')
                    ->first();
                if (intVal($emaTo) == -69) { // Current user of the form
                    if (isset($this->v["uID"])) {
                        $emaUsr = User::where('id', $this->v["uID"])
                            ->where('email', 'NOT LIKE', 'anonymous.%@anonymous.org')
                            ->first();
                    }
                    if (!$emaUsr || !isset($emaUsr->email)) {
                        $dataStores = SLNode::where('NodeTree', $this->treeID)
                            ->where('NodeDataStore', 'NOT LIKE', '')
                            //->where('NodeDataStore', 'IS NOT', NULL)
                            ->where('NodeType', 'LIKE', 'Email')
                            ->select('NodeDataStore')
                            ->get();
                        if ($dataStores->isNotEmpty()) {
                            foreach ($dataStores as $ds) {
                                if (strpos($ds->NodeDataStore, ':') !== false) {
                                    list($tbl, $fld) = explode(':', $ds->NodeDataStore);
                                    if (isset($this->sessData->dataSets[$tbl]) 
                                        && isset($this->sessData->dataSets[$tbl][0]->{ $fld })
                                        && trim($this->sessData->dataSets[$tbl][0]->{ $fld }) != '') {
                                        $emaToList[] = [$this->sessData->dataSets[$tbl][0]->{ $fld }, ''];
                                    }
                                }
                            }
                        }
                    }
                }
                if (intVal($emaTo) == -68) {
                    
                }
                if ($emaUsr && isset($emaUsr->email)) {
                    $emaToList[] = [$emaUsr->email, $emaUsr->name];
                }
            }
        }
        return $emaToList;
    }
    
    
    protected function postNodeSendEmail($nID)
    {
        if (sizeof($this->allNodes[$nID]->extraOpts["emailTo"]) > 0 
            && (intVal($this->allNodes[$nID]->nodeRow->NodeDefault) > 0 
                || intVal($this->allNodes[$nID]->nodeRow->NodeDefault) == -69)) {
            $emaTo = $this->getUserEmailList($this->allNodes[$nID]->extraOpts["emailTo"]);
            $emaCC = $this->getUserEmailList($this->allNodes[$nID]->extraOpts["emailCC"]);
            $emaBCC = $this->getUserEmailList($this->allNodes[$nID]->extraOpts["emailBCC"]);
            if (sizeof($emaTo) > 0) {
                $currEmail = [];
                $emaSubject = $GLOBALS["SL"]->sysOpts["site-name"] . ': ' . $GLOBALS["SL"]->treeRow->TreeName;
                $emaContent = '';
                if (intVal($this->allNodes[$nID]->nodeRow->NodeDefault) > 0) {
                    $currEmail = SLEmails::find($this->allNodes[$nID]->nodeRow->NodeDefault);
                    if ($currEmail && isset($currEmail->EmailSubject)) {
                        $emaSubject = $currEmail->EmailSubject;
                        $emaContent = $this->sendEmailBlurbs($currEmail->EmailBody);
                    }
                } elseif (intVal($this->allNodes[$nID]->nodeRow->NodeDefault) == -69) { // dump all form fields
                    $flds = $GLOBALS["SL"]->REQ->all();
                    if ($flds && sizeof($flds) > 0) {
                        foreach ($flds as $key => $val) {
                            if (!in_array($key, [ '_token', 'ajax', 'tree', 'treeSlug', 'node', 'nodeSlug', 
                                'loop', 'loopItem', 'step', 'alt', 'jumpTo', 'afterJumpTo', 'zoomPref' ])
                                && strpos($key, 'Visible') === false && trim($val) != '') {
                                $fldNID = intVal(str_replace('n', '', str_replace('fld', '', $key)));
                                if (isset($this->allNodes[$fldNID]->nodeRow->NodePromptText) 
                                    && trim($this->allNodes[$fldNID]->nodeRow->NodePromptText) != '') {
                                    $emaContent .= strip_tags($this->allNodes[$fldNID]->nodeRow->NodePromptText) 
                                        . ':<br />';
                                }
                                $emaContent .= $val . '<br /><br />';
                            }
                        }
                    }
                }
                if ($emaContent != '') {
                    $emaContent = $this->emailRecordSwap($emaContent);
                    $emaSubject = $this->emailRecordSwap($emaSubject);
                    if ($GLOBALS["SL"]->isHomestead()) {
                        echo '<div class="container"><h2>' . $emaSubject . '</h2>' . $emaContent . '<hr><hr></div>';
                    } else {
                        $this->sendEmail($emaContent, $emaSubject, $emaTo, $emaCC, $emaBCC);
                    }
                    $toList = '';
                    if (sizeof($emaTo) > 0) {
                        foreach ($emaTo as $i => $e) $toList .= (($i > 0) ? ' ; ' : '') . $e[0];
                    }
                    $emaID = ((isset($currEmail->EmailID)) ? $currEmail->EmailID : -3);
                    $this->logEmailSent($emaContent, $emaSubject, $toList, $emaID, $this->treeID, $this->coreID, 
                        $this->v["uID"]);
                }
            }
        }
        return '';
    }
    
    public function emailRecordSwap($emaTxt)
    {
        return $this->sendEmailBlurbs($emaTxt);
    }
    
    public function sendEmailBlurbs($emailBody)
    {
        if (!isset($this->v["emailList"])) {
            $this->v["emailList"] = SLEmails::orderBy('EmailName', 'asc')
                ->orderBy('EmailType', 'asc')
                ->get();
        }
        if (trim($emailBody) != '' && sizeof($this->v["emailList"]) > 0) {
            foreach ($this->v["emailList"] as $i => $e) {
                $emailTag = '[{ ' . $e->EmailName . ' }]';
                if (strpos($emailBody, $emailTag) !== false) {
                    $emailBody = str_replace($emailTag, $this->sendEmailBlurbs($e->EmailBody), $emailBody);
                }
            }
        }
        $dynamos = [
            '[{ Login URL }]', 
            '[{ User Email }]', 
            '[{ Email Confirmation URL }]'
            ];
        foreach ($dynamos as $dy) {
            if (strpos($emailBody, $dy) !== false) {
                $swap = $dy;
                $dyCore = str_replace('[{ ', '', str_replace(' }]', '', $dy));
                switch ($dy) {
                    case '[{ Login URL }]':
                        $swap = $GLOBALS["SL"]->swapURLwrap($GLOBALS["SL"]->sysOpts["app-url"] . '/login');
                        break;
                    case '[{ User Email }]': 
                        $swap = ((isset($this->v["user"]) && isset($this->v["user"]->email)) 
                            ? $this->v["user"]->email : '');
                        break;
                    case '[{ Email Confirmation URL }]': 
                        $swap = $GLOBALS["SL"]->swapURLwrap($GLOBALS["SL"]->sysOpts["app-url"] . '/email-confirm/' 
                            . $this->createToken('Confirm Email') . '/' . md5($this->v["user"]->email));
                        break;
                }
                $emailBody = str_replace($dy, $swap, $emailBody);
            }
        }
        return $this->sendEmailBlurbsCustom($emailBody);
    }
    
    public function processEmailConfirmToken(Request $request, $token = '', $tokenB = '')
    {
        $tokRow = SLTokens::where('TokTokToken', $token)
            ->where('updated_at', '>', $this->tokenExpireDate('Confirm Email'))
            ->first();
        if ($tokRow && isset($tokRow->TokUserID) && intVal($tokRow->TokUserID) > 0 && trim($tokenB) != '') {
            $usr = User::find($tokRow->TokUserID);
            if ($usr && isset($usr->email) && trim($usr->email) != '' && md5($usr->email) == $tokenB) {
                $chk = SLUsersRoles::where('RoleUserUID', $tokRow->TokUserID)
                    ->where('RoleUserRID', -37)
                    ->first();
                if (!$chk || !isset($chk->RoleUserRID)) {
                    $chk = new SLUsersRoles;
                    $chk->RoleUserUID = $tokRow->TokUserID;
                    $chk->RoleUserRID = -37;
                    $chk->save();
                }
            }
        }
        $this->setNotif('Thank you for confirming your email address!', 'success');
        return $this->redir('/my-profile');
    }
    
    public function sendEmailBlurbsCustom($emailBody)
    {
        return $emailBody;
    }
    
    protected function processContactForm($nID = -3, $tmpSubTier = [])
    {
        $this->pageCoreFlds = [ 'ContType', 'ContEmail', 'ContSubject', 'ContBody' ];
        $ret = $this->processPageForm($nID, $tmpSubTier, 'SLContact', 'ContBody');
        $this->pageCoreRow->update([ 'ContFlag' => 'Unread' ]);
        $rootNode = SLNode::find($GLOBALS["SL"]->treeRow->TreeRoot);
        if ($rootNode && isset($rootNode->NodeDefault)) {
            $emails = $GLOBALS["SL"]->mexplode(';', $rootNode->NodeDefault);
            if (sizeof($emails) > 0) {
                $emaToArr = [];
                foreach ($emails as $e) $emaToArr[] = [$e, ''];
                $emaSubj = strip_tags($this->pageCoreRow->ContSubject);
                if (strlen($emaSubj) > 30) $emaSubj = substr($emaSubj, 0, 30) . '...'; 
                $emaSubj = $GLOBALS["SL"]->sysOpts["site-name"] . ' Contact: ' . $emaTitle;
                $emaContent = view('vendor.survloop.admin.contact-row', [
                    "contact"  => $this->pageCoreRow,
                    "forEmail" => true
                ])->render();
                $this->sendEmail($emaContent, $emaSubj, $emaToArr);
//echo '<br /><br /><br />mailing to ' . $mainEmail . '?... ' . $emaTitle . '<br /><div class="brd">' . $emaContent . '</div><br />';
            }
        }
        $this->setNotif('Thank you for contacting us!', 'success');
//exit;
        return $ret;
    }
    
    protected function processPageForm($nID = -3, $tmpSubTier = [], $slTable = '', $dumpFld = '')
    {
        if (trim($slTable) == '') return false;
        eval("\$this->pageCoreRow = new App\\Models\\" . $slTable . ";");
        $extraData = $this->processPageFormInner($nID, $tmpSubTier);
        if (trim($extraData) != '' && trim($dumpFld) != '') {
            $this->pageCoreRow->{ $dumpFld } = $this->pageCoreRow->{ $dumpFld } . $extraData;
        }
        $this->pageCoreRow->save();
        return '';
    }
    
    protected function processPageFormInner($nID = -3, $tmpSubTier = [])
    {
        $extraData = '';
        $newVal = $this->getNodeFormFldBasic($nID);
        if (trim($newVal) != '') {
            $found = false;
            if (isset($this->allNodes[$nID]->dataStore) 
                && trim($this->allNodes[$nID]->dataStore) != '') {
                $storeFld = trim($this->allNodes[$nID]->dataStore);
                if (strpos($storeFld, ':') !== false) $storeFld = substr($storeFld, strpos($storeFld, ':')+1);
                if (sizeof($this->pageCoreFlds) > 0) {
                    foreach ($this->pageCoreFlds as $fld) {
                        if ($storeFld == $fld) {
                            $found = true;
                            $this->pageCoreRow->{ $fld } = $newVal;
                        }
                    }
                }
            }
            if (!$found) $extraData .= '<p>' . $newVal . '</p>';
        }
        if (sizeof($tmpSubTier[1]) > 0) {
            foreach ($tmpSubTier[1] as $childNode) {
                if (!$this->allNodes[$childNode[0]]->isPage()) {
                    $extraData .= $this->processPageFormInner($childNode[0], $childNode);
                }
            }
        }
        return $extraData;
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
    
} // end of SurvFormTree class
