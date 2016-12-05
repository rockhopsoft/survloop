<?php
namespace SurvLoop\Controllers;

use Storage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\File\File;

use App\Models\User;
use App\Models\SLNodeResponses;

class SurvFormTree extends SurvLoopTree
{
    
    public $classExtension   = 'SurvFormTree';

    public $nodeTypes        = [ 
        'Radio', 'Checkbox', 'Drop Down', 'Text', 'Long Text', 'Text:Number', 'Email', 'Password', 
        'Date', 'Date Picker', 'Date Time', 'Time', 'Gender', 'Gender Not Sure', 'Feet Inches', 
        'U.S. States', 'Hidden Field', 'Spambot Honey Pot', 'Uploads', 'Other/Custom' 
    ];
    
    public $nodeSpecialTypes = [
        'Instructions', 'Page', 'Branch Title', 'Loop Root', 
        'Data Manip: New', 'Data Manip: Update', 'Data Manip: Wrap'
    ];
    
    protected $pageJSnode    = '';
    protected $pageJSextra   = '';
    protected $pageJSvalid   = '';
    protected $pageAJAX      = '';
    protected $pageHasUpload = array();
    protected $pageHasReqs   = '';
    protected $pageFldList   = array();
    
    public $uploadTypes      = array();
    protected $upLinks       = array();
    protected $uploads       = array();
    protected $upDeets       = array();
    
    
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
    
    protected function printSpecial($nID, $promptNotesSpecial = '', $currNodeSessData = '') { return ''; }
    
    protected function customNodePrintButton($nID = -3, $nodeRow = array()) { return ''; }
    
    protected $nextBtnOverride = '';
    
    protected function nodePrintButton($nID = -3, $tmpSubTier = array(), $promptNotesSpecial = '', $printBack = true)
    { 
        $ret = $this->customNodePrintButton($nID, $promptNotesSpecial);
        if ($ret != '') return $ret;
        
        // else print standard button variations
        $ret .= '<div class="fC p10"></div><div class="nodeSub">';
        if (isset($this->loopItemsCustBtn) && $this->loopItemsCustBtn != '')
        {
            $ret .= $this->loopItemsCustBtn;
        }
        else
        {
            $nextLabel = (($this->allNodes[$nID]->nodeType == 'Instructions' && sizeof($tmpSubTier[1]) == 0) 
                || ($this->nodePrintJumpTo($nID) > 0)) ? 'OK' : 'Next';
            if (trim($this->nextBtnOverride) != '') $nextLabel = $this->nextBtnOverride;
            if ($this->allNodes[$nID]->isStepLoop() 
                && sizeof($this->sessData->loopItemIDs[$GLOBALS["DB"]->closestLoop["loop"]]) 
                    != sizeof($this->sessData->loopItemIDsDone))
            {
                $ret .= '<a href="javascript:;" class="fR btn btn-lg btn-primary nFormNext" id="nFormNext" 
                    ><i class="fa fa-arrow-circle-o-right"></i> ' . $nextLabel . '</a>';
            }
            else $ret .= '<input type="submit" value="' . $nextLabel . '" class="fR btn btn-lg btn-primary nFormNext" id="nFormNext">';
        }
        if ($this->nodePrintJumpTo($nID) <= 0 && $printBack 
            && $GLOBALS["DB"]->treeRow->TreeFirstPage != $nID)
        {
            $ret .= '<input type="button" value="Back" class="fL btn btn-lg btn-default" id="nFormBack">';
        }
        $ret .= '<div class="clearfix p5"></div></div>';
        return $ret; 
    }
    
    protected function printNodePublicFormStart($nID)
    {
        return view( 'vendor.survloop.formtree-form-start', [
            "nID"             => $nID, 
            "pageHasUpload"   => $this->pageHasUpload, 
            "nodePrintJumpTo" => $this->nodePrintJumpTo($nID), 
            "zoomPref"        => ((isset($this->sessInfo->SessZoomPref)) ? intVal($this->sessInfo->SessZoomPref) : 0), 
        ])->render();
    }
    
    protected function printNodePublicFormEnd($nID, $promptNotesSpecial = '')
    {
        $loopRootJustLeft = -3;
        if (isset($this->sessInfo->SessLoopRootJustLeft) 
            && intVal($this->sessInfo->SessLoopRootJustLeft) > 0)
        {
            $loopRootJustLeft = $this->sessInfo->SessLoopRootJustLeft;
            $this->sessInfo->SessLoopRootJustLeft = -3;
            $this->sessInfo->save();
        }
        return view( 'vendor.survloop.formtree-form-end', [
            "nID"              => $nID, 
            "isLoopRoot"       => $this->allNodes[$nID]->isLoopRoot(), 
            "pageURL"          => $this->allNodes[$nID]->nodeRow->NodePromptNotes, 
            "pageHasUpload"    => $this->pageHasUpload, 
            "pageFldList"      => $this->pageFldList, 
            "pageJSextra"      => $this->pageJSextra, 
            "pageJSvalid"      => $this->pageJSvalid, 
            "pageAJAX"         => $this->pageAJAX, 
            "loopRootJustLeft" => $loopRootJustLeft, 
        ])->render();
    }
    
    private $pageNodes = array();
    private $pagePrevLiners = array();
    
    protected function getPrintableNodeList($nID = -3, $tmpSubTier = array())
    {
        if (sizeof($tmpSubTier) == 0) $tmpSubTier = $this->loadNodeSubTier($nID);
        $this->pageNodes[] = $this->allNodes[$nID];
        if (sizeof($tmpSubTier[1]) > 0)
        {
            foreach ($tmpSubTier[1] as $childNode)
            {     // recurse these bitches
                if (!$this->allNodes[$childNode[0]]->isPage())
                {
                    $this->getPrintableNodeList($childNode[0], $childNode);
                }
            }
        }
        return true;
    }
    
    protected function getPrintSpecs($nID = -3, $tmpSubTier = array())
    {
        $this->pageNodes = array();
        $this->getPrintableNodeList($nID, $tmpSubTier);
        $prevPrevLiner = false;
        if (sizeof($this->pageNodes) > 0) 
        {
            for ($i=0; $i<sizeof($this->pageNodes); $i++)
            {
                if ($this->pageNodes[$i]->isOnPrevLine())
                {
                    if ($i > 0 && !$this->pageNodes[($i-1)]->isOnPrevLine())
                    {     // then this is the first PrevLiner
                        $this->pagePrevLiners[] = array($this->pageNodes[($i-1)]->nodeID);
                        $prevPrevLiner = true;
                    }
                    $this->pagePrevLiners[(sizeof($this->pagePrevLiners)-1)][] = $this->pageNodes[$i]->nodeID;
                }
                else $prevPrevLiner = false;
            }
        }
    }
    
    protected function customLabels($nID = -3, $str = '') { return $str; }
    
    protected function cleanLabel($str = '')
    {
        $str = str_replace('<span class="slBlueDark"><b>You</b></span>', '<span class="slBlueDark"><b>you</b></span>', $str);
        $str = str_replace('<span class="slBlueDark"><b>you</b></span>&#39;s', '<span class="slBlueDark"><b>your</b></span>', $str);
        $str = str_replace('Was <span class="slBlueDark"><b>you</b></span>', 'Were <span class="slBlueDark"><b>you</b></span>', $str);
        $str = str_replace('was <span class="slBlueDark"><b>you</b></span>', 'were <span class="slBlueDark"><b>you</b></span>', $str);
        $str = str_replace('<span class="slBlueDark"><b>you</b></span>\'s', '<span class="slBlueDark"><b>your</b></span>', $str);
        $str = str_replace('<span class="slBlueDark"><b>you</b></span> was', '<span class="slBlueDark"><b>you</b></span> were', $str);
        $str = str_replace(', [LoopItemLabel]:', ':', str_replace(', <span class="slBlueDark"><b>[LoopItemLabel]</b></span>:', ':', $str));
        $str = str_replace(', <span class="slBlueDark"><b></b></span>:', ':', str_replace(', <span class="slBlueDark"><b>&nbsp;</b></span>:', ':', $str));
        $str = trim(str_replace(', :', ':', $str));
        return $str;
    }
    
    protected function makeLabelH1($str)
    {
        return str_replace('<span class="slBlueDark"><b>', '<h1>', 
            str_replace('</b></span>', '</h1>', $str));
    }
    
    protected function customNodePrintWrap($nID, $bladeRender = '')
    {
        return $this->printNodePublicFormStart($nID) . $bladeRender
            . $this->nodePrintButton($nID) . $this->printNodePublicFormEnd($nID) 
            . '<div class="fC p20"></div>';
    }

    
    //************  hidden field with nested nodes, flagged on/off when visible, 
    //    so i know which versions of duplicated questions to store during post processing  ************
    
    protected function customNodePrint($nID = -3, $tmpSubTier = array()) { return ''; }
    protected function printNodePublic($nID = -3, $tmpSubTier = array(), $currVisib = true)
    {
        if (!$this->checkNodeConditions($nID)) return '';
        if (sizeof($tmpSubTier) == 0) $tmpSubTier = $this->loadNodeSubTier($nID);
        
        // copy node object; load field info and current session data
        $this->allNodes[$nID]->fillNodeRow($nID);
        $curr = $this->allNodes[$nID];
        list($tbl, $fld) = $curr->getTblFld();
        $fldForeignTbl = $GLOBALS["DB"]->fldForeignKeyTbl($tbl, $fld);
        if (($curr->isPage() || $curr->isInstruct()) && isset($GLOBALS['DB']->closestLoop['obj']->DataLoopTable))
        {
            $tbl = $GLOBALS['DB']->closestLoop['obj']->DataLoopTable;
        }
        
        if (substr($curr->nodeType, 0, 11) == 'Data Manip:') $this->loadManipBranch($nID);
        $hasParentDataManip = $this->hasParentDataManip($nID);
        
        list($itemInd, $itemID) = $this->sessData->currSessDataPos($tbl, $hasParentDataManip);
        if (trim($GLOBALS['DB']->closestLoop['loop']) != '' && $tbl == $this->sessData->isCheckboxHelperTable($tbl))
        {    // In this context the relevant item index is the item's index with the loop, not the table's whole data set...
            $itemInd = $this->sessData->getLoopIndFromID($GLOBALS['DB']->closestLoop['loop'], $itemID);
        }
        $currNodeSessData = $this->sessData->currSessData($nID, $tbl, $fld, 'get', '', $hasParentDataManip);
        if ($itemID <= 0) $currNodeSessData = ''; // override false profit ;-P
        
        // print the button, and form initialization which only happens once per page
        if ($curr->isPage() || $curr->isLoopRoot())
        {    // make sure these are reset, in case of redirect
            $this->pageJSnode = $this->pageJSextra = $this->pageJSvalid = $this->pageHasReqs = '';
            $this->pageHasUpload = $this->pageFldList = array();
        }
        
        // check for extra custom PHP code stored with the node; check for standardized techniques
        $nodeOverrides = $this->printNodeSessDataOverride($nID, $tmpSubTier, $currNodeSessData);
        if (sizeof($nodeOverrides) > 0 && isset($nodeOverrides[0])) $currNodeSessData = $nodeOverrides[0];
        //if ($this->debugOn) { echo '<br />printNodePublic(' . $nID . '), tbl: ' . $tbl . ', fld: '. $fld . ', set: ' . $set . ', itemID: ' . $itemID . ', itemInd: ' . $itemInd . ', currNodeSessData: ' . $currNodeSessData . '<br />'; }
        
        $showKidsResponded = true;
        if (sizeof($tmpSubTier[1]) > 0)
        {
            if ($curr->hasShowKids && sizeof($curr->responses) > 0)
            { // then displaying children on page is conditional
                $showKidsResponded = false;
                if ($currNodeSessData != '')
                {
                    foreach ($curr->responses as $res)
                    {
                        if (intVal($res->NodeResShowKids) == 1 && ($res->NodeResValue == $currNodeSessData 
                            || strpos($currNodeSessData, ';'.$res->NodeResValue.';') !== false))
                        {
                            $showKidsResponded = true;
                        }
                    }
                }
                $this->pageAJAX .= 'conditionNodes['.$nID.'] = true;' . "\n";
            }
            $childList = array();
            foreach ($tmpSubTier[1] as $childNode) $childList[] = $childNode[0];
            $this->pageAJAX .= 'nodeKidList['.$nID.'] = ['.implode(', ', $childList).'];' . "\n";
        }
        else $this->pageAJAX .= 'nodeKidList['.$nID.'] = new Array();' . "\n";

        $visibilityField = '<input type="hidden" name="n'.$nID.'Visible" id="n'.$nID.'VisibleID" value="' . (($currVisib) ? 1 : 0) . '">';
        if (!$showKidsResponded) $currVisib = false;
        
        $ret = $this->customNodePrint($nID, $tmpSubTier);
        if ($ret != '') return $visibilityField . $ret;
        $ret .= $visibilityField;
        // else print standard node output...
        
        // check for extra custom HTML/JS/CSS code stored with the node; check for standardized techniques
        if ($curr->isRequired()) $this->pageHasReqs++;
        $onKeyUp = ' checkNodeUp(); ';
        if (trim($curr->nodeRow->NodePromptAfter) != '')
        {
            if (stripos($curr->nodeRow->NodePromptAfter, '/'.'* formAJAX *'.'/') !== false) $this->pageAJAX .= $curr->nodeRow->NodePromptAfter;
            else
            {
                $this->pageJSnode .= str_replace('[[nID]]', $nID, $curr->nodeRow->NodePromptAfter);
                if (strpos($curr->nodeRow->NodePromptAfter, 'function reqFormNode[[nID]](') !== false)
                {
                    $this->pageJSvalid .= "if (document.getElementById('n".$nID."VisibleID').value == 1) reqFormNode".$nID."();\n";
                }
                if (strpos($curr->nodeRow->NodePromptAfter, 'function fldOnKeyUp[[nID]](') !== false) $onKeyUp .= ' fldOnKeyUp'.$nID.'(); ';
            }
        }
        $charLimit = '';
        if (intVal($curr->nodeRow->NodeCharLimit) > 0 && $curr->nodeType != 'Uploads')
        {
            $onKeyUp .= ' charLimit('.$nID.', '.$curr->nodeRow->NodeCharLimit.'); ';
            $charLimit = "\n".'<div id="charLimit'.$nID.'Msg" class="slRedDark f12 opac33"></div>';
            $this->pageJSextra .= 'setTimeout("charLimit('.$nID.', '.$curr->nodeRow->NodeCharLimit.')", 5);' . "\n";
        }
        if (trim($onKeyUp) != '') $onKeyUp = ' onKeyUp="'.$onKeyUp.'" ';
        
        // check notes settings for any standardized techniques
        $prevLineThis = -1; $isFinalOfPrevLine = true;
        $promptNotesSpecial = '';
        if ($this->isPromptNotesSpecial($curr->nodeRow->NodePromptNotes))
        {
            $promptNotesSpecial = $curr->nodeRow->NodePromptNotes;
            $curr->nodeRow->NodePromptNotes = '';
        }
        
        // write basic node field labeling
        $curr->nodeRow->NodePromptText  = $this->customLabels($nID, $curr->nodeRow->NodePromptText);
        $curr->nodeRow->NodePromptNotes = $this->customLabels($nID, $curr->nodeRow->NodePromptNotes);
        if ($itemID > 0 && $itemInd >= 0 && strpos($curr->nodeRow->NodePromptText, '[LoopItemLabel]') !== false || strpos($curr->nodeRow->NodePromptNotes, '[LoopItemLabel]') !== false)
        {
            $loopItemLabel = $this->getLoopItemLabel($GLOBALS["DB"]->closestLoop["loop"], $this->sessData->getRowById($GLOBALS["DB"]->closestLoop["obj"]->DataLoopTable, $itemID), $itemInd);
            $curr->nodeRow->NodePromptText  = str_replace('[LoopItemLabel]', '<span class="slBlueDark"><b>'.$loopItemLabel.'</b></span>', $curr->nodeRow->NodePromptText);
            $curr->nodeRow->NodePromptNotes = str_replace('[LoopItemLabel]', '<span class="slBlueDark"><b>'.$loopItemLabel.'</b></span>', $curr->nodeRow->NodePromptNotes);
        }
        if ($itemID > 0 && $itemInd >= 0 && strpos($curr->nodeRow->NodePromptText, '[LoopItemCnt]') !== false || strpos($curr->nodeRow->NodePromptNotes, '[LoopItemCnt]') !== false)
        {
            $curr->nodeRow->NodePromptText  = str_replace('[LoopItemCnt]', '<span class="slBlueDark"><b>'.(1+$itemInd).'</b></span>', $curr->nodeRow->NodePromptText);
            $curr->nodeRow->NodePromptNotes = str_replace('[LoopItemCnt]', '<span class="slBlueDark"><b>'.(1+$itemInd).'</b></span>', $curr->nodeRow->NodePromptNotes);
        }
        $curr->nodeRow->NodePromptText  = $this->cleanLabel($curr->nodeRow->NodePromptText);
        $curr->nodeRow->NodePromptNotes = $this->cleanLabel($curr->nodeRow->NodePromptNotes);
        $nodePrompt = "\n".'<div id="nLabel'.$nID.'" class="nPrompt"><label for="n'.$nID.'FldID">
            ' . $curr->nodeRow->NodePromptText 
            . (($curr->isRequired()) ? '<small class="red pL10 mTn10">*required</small>' : '') . '
            </label></div>';
        
        if (trim($curr->nodeRow->NodePromptNotes) != '' && !$curr->isLoopRoot())
        {
            $nodePrompt .= '<div class="nodeSidenote" id="nLabel'.$nID.'notes">' . $curr->nodeRow->NodePromptNotes . '</div>' . "\n";
        }
    
        // check for if we're at the root of a Loop Root, we've got special handling
        if ($curr->isPage())
        {
            $ret .= '<div class="fC"></div><div id="node'.$nID.'" class="nodeWrap">';
        }
        elseif ($curr->isLoopRoot())
        {
            $ret .= '<div class="fC"></div><div id="node'.$nID.'" class="nodeWrap">
                ' . $nodePrompt . $this->printSetLoopNav($nID, $curr->dataBranch);
        }
        elseif ($curr->isDataManip())
        {
            $ret .= '<div class="fC"></div><div id="node'.$nID.'" class="nodeWrap">
            <input type="hidden" name="dataManip' . $nID . '" value="1">';
        }
        else  // otherwise, the main Node printer...
        {
            
            // Start normal data field checks
            $dateStr = $timeStr = '';
            if ($fld != '' && $fld != ($GLOBALS["DB"]->tblAbbr[$tbl].'ID') 
                && trim($currNodeSessData) != '' 
                && isset($GLOBALS["DB"]->fldTypes[$tbl][$fld]))
            {
                // convert current session data for dates and times
                if ($GLOBALS["DB"]->fldTypes[$tbl][$fld] == 'DATETIME')
                {
                    list($dateStr, $timeStr) = explode(' ', $currNodeSessData);
                    $dateStr = $this->cleanDateVal($dateStr);
                    if (trim($dateStr) != '') $dateStr = date("m/d/Y", strtotime($dateStr));
                }
                elseif ($GLOBALS["DB"]->fldTypes[$tbl][$fld] == 'DATE')
                {
                    $dateStr = $this->cleanDateVal($currNodeSessData);
                    if (trim($dateStr) != '') $dateStr = date("m/d/Y", strtotime($dateStr));
                }
                if ($dateStr == '12/31/1969') $dateStr = '';
                
            } // end normal data field checks
            
            // check if this field's label and field is to be printed on the same line
            $isOneLiner = $isOneLinerFld = '';
            if ($curr->isOneLiner()) $isOneLiner = ' disIn mR20';
            elseif ($curr->isOneLiner() || $curr->isOneLineResponses()) $isOneLinerFld = ' disIn mR20';
            if (trim($isOneLiner) != '') $nodePrompt = str_replace('class="nPrompt"', 'class="nPrompt'.$isOneLiner.'"', $nodePrompt);
            
            // check if this field is among others to be printed on the same line; if so how many per line?..
            if (sizeof($this->pagePrevLiners) > 0)
            {
                foreach ($this->pagePrevLiners as $lines)
                {
                    if (in_array($nID, $lines))
                    {
                        $prevLineThis = sizeof($lines);
                        if ($lines[(sizeof($lines)-1)] == $nID) $isFinalOfPrevLine = true;
                        else $isFinalOfPrevLine = false;
                    }
                }
            }
            // write the start of the main node wrapper
            if ($prevLineThis < 2) $ret .= '<div class="fC"></div><div id="node'.$nID.'" class="nodeWrap">' . "\n";
            else
            {
                $width = (($prevLineThis == 2) ? 'fL w48 mRp1' : (($prevLineThis == 3) ? 'fL w31 mRp1' : (($prevLineThis == 4) ? 'fL w23 mRp1' : '')));
                if (stripos($curr->nodeRow->NodePromptText, 'city') !== false) $width = 'fL w48 mRp2';
                elseif (stripos($curr->nodeRow->NodePromptText, 'state') !== false) $width = 'fL w23 mRp1';
                elseif (stripos($curr->nodeRow->NodePromptText, 'zip') !== false) $width = 'fR w23';
                $ret .= '<div class="' . $width . '" ' . (($curr->nodeType == 'Time') ? 'style="min-width: 200px;"' : '') 
                    . ' ><div id="node'.$nID.'" class="nodeWrap">' . "\n";
            }
            
            if (!in_array($curr->nodeType, array('Radio', 'Checkbox', 'Instructions', 'Other/Custom')))
            {
                $this->pageFldList[] = 'n'.$nID.'FldID';
            }
            
            // print out each of the various field types
            if ($curr->nodeType == 'Hidden Field')
            {
                $ret .= $nodePrompt . '<input type="hidden" name="n'.$nID.'fld" id="n'.$nID.'FldID" value="' . $currNodeSessData . '">' . "\n"; 
            }
            elseif (in_array($curr->nodeType, array('Text', 'Email', 'Text:Number', 'Spambot Honey Pot')))
            {
                $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '"><input class="form-control" type="' 
                    . (($curr->nodeType == 'Email') ? 'email' : (($curr->nodeType == 'Text:Number') ? 'number' : 'text')) 
                    . '" name="n'.$nID.'fld" id="n'.$nID.'FldID" value="' . $currNodeSessData . '" '.$onKeyUp.' ></div>' . $charLimit . "\n"; 
                if ($curr->isRequired()) $this->pageJSvalid .= "if (document.getElementById('n".$nID."VisibleID').value == 1) "
                             . (($curr->nodeType == 'Email') ? "reqFormFldEmail(" . $nID . ");\n" : "reqFormFld(" . $nID . ");\n");
                if ($curr->nodeType == 'Spambot Honey Pot') $this->pageJSextra .= "\n".'nFldHP(' . $nID . ');';
                if (trim($curr->nodeRow->NodeTextSuggest) != '')
                {
                    $this->pageAJAX .= '$( "#n'.$nID.'FldID" ).autocomplete({ source: [';
                    foreach ($GLOBALS["DB"]->getDefSet($curr->nodeRow->NodeTextSuggest) as $i => $def)
                    {
                        $this->pageAJAX .= (($i > 0) ? ',' : '') . ' "' . $def->DefValue . '"';
                    }
                    $this->pageAJAX .= ' ] });' . "\n";
                }
            }
            elseif ($curr->nodeType == 'Long Text')
            {
                $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '"><textarea class="form-control" name="n'.$nID.'fld" id="n'.$nID.'FldID" '
                        . $onKeyUp . ' >' . $currNodeSessData . '</textarea></div>' . $charLimit . "\n"; 
                if ($curr->isRequired()) $this->pageJSvalid .= "if (document.getElementById('n".$nID."VisibleID').value == 1) reqFormFld(" . $nID . ");\n";
            }
            elseif ($curr->nodeType == 'Password')
            {
                $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '"><input type="password" name="n'.$nID.'fld" id="n'.$nID.'FldID" value="" '
                        . $onKeyUp . ' autocomplete="off" class="form-control" ></div>' . $charLimit . "\n"; 
                if ($curr->isRequired()) $this->pageJSvalid .= "if (document.getElementById('n".$nID."VisibleID').value == 1) reqFormFld(" . $nID . ");\n";
            }
            elseif ($curr->nodeType == 'Drop Down' || $curr->nodeType == 'U.S. States')
            {
                $curr = $this->checkResponses($curr, $fldForeignTbl);
                if (sizeof($curr->responses) > 0 || $curr->nodeType == 'U.S. States')
                {
                    $ret .= $nodePrompt . "\n".'<div class="nFld' . $isOneLinerFld . '">
                        <select name="n'.$nID.'fld" id="n'.$nID.'FldID" class="form-control' . (($isOneLinerFld != '') ? ' w33' : '') . '" >
                        <option value="" ' . ((trim($currNodeSessData) == '') ? 'SELECTED' : '') . ' ></option>' . "\n"; 
                    if ($curr->hasShowKids) $this->pageAJAX .= '$("#n'.$nID.'FldID").click(function(){ var foundKidResponse = false;' . "\n";
                    if ($curr->nodeType == 'U.S. States') {
                        $ret .= $GLOBALS["DB"]->states->stateDrop($currNodeSessData);
                    }
                    else { 
                        foreach ($curr->responses as $j => $res) {
                            $ret .= '<option value="' . $res->NodeResValue . '" ' . (($currNodeSessData == $res->NodeResValue) ? 'SELECTED' : '') . ' >' . $res->NodeResEng . '</option>' . "\n"; 
                            if ($curr->hasShowKids && intVal($res->NodeResShowKids) == 1) $this->pageAJAX .= 'if (document.getElementById("n'.$nID.'fld'.$j.'").value == "' . $res->NodeResValue . '") foundKidResponse = true;' . "\n";
                        }
                    }
                    if ($curr->hasShowKids) $this->pageAJAX .= "\n".'if (foundKidResponse) { $("#node'.$nID.'kids").slideDown("50"); kidsVisible('.$nID.', true, true); } else { $("#node'.$nID.'kids").slideUp("50"); kidsVisible('.$nID.', false, true); }' . "\n".' }); ' . "\n";
                    if ($curr->isRequired()) $this->pageJSvalid .= "if (document.getElementById('n".$nID."VisibleID').value == 1) reqFormFld(" . $nID . ");\n";
                    $ret .= '</select></div>' . "\n"; 
                }
            }
            elseif (in_array($curr->nodeType, array('Radio', 'Checkbox')))
            {
                $curr = $this->checkResponses($curr, $fldForeignTbl);
                if (sizeof($curr->responses) > 0)
                {
                    $ret .= (($curr->isOneLiner()) ? '<div class="pB20">' : '') 
                        . str_replace('<label for="n'.$nID.'FldID">', '', str_replace('</label>', '', $nodePrompt)) 
                        . '<div class="nFld' . $isOneLiner . '">' . "\n";
                    $respKids = (($curr->hasShowKids) ? ' class="n'.$nID.'fldCls" ' : ''); // onClick="return check'.$nID.'Kids();"
                    if ($curr->hasShowKids) $this->pageAJAX .= '$(".n'.$nID.'fldCls").click(function(){ var foundKidResponse = false;' . "\n";
                    foreach ($curr->responses as $j => $res)
                    {
                        $this->pageFldList[] = 'n'.$nID.'fld'.$j;
                        $ret .= '<div class="' . $isOneLinerFld . '">
                            ' . ((strlen($res) < 40) ? '<nobr>' : '') . '
                            <label for="n'.$nID.'fld'.$j.'" class="mR10">
                                <div class="disIn mR5"><input id="n'.$nID.'fld'.$j.'" value="' . $res->NodeResValue . '" type="' . strtolower($curr->nodeType) . '" ';
                                if ($curr->nodeType == 'Radio') $ret .= 'name="n'.$nID.'fld" ' . (($currNodeSessData == $res->NodeResValue) ? 'CHECKED' : '');
                                else $ret .= 'name="n'.$nID.'fld[]" ' . ((strpos(';'.$currNodeSessData.';', ';'.$res->NodeResValue.';') !== false) ? 'CHECKED' : '');
                                $ret .= $respKids . ' autocomplete="off" onClick="checkNodeUp();" ></div> ' . $res->NodeResEng . '
                            </label>
                            ' . ((strlen($res) < 40) ? '</nobr>' : '') . '
                        </div>' . "\n";
                        if ($curr->hasShowKids && intVal($res->NodeResShowKids) == 1)
                        {
                            $this->pageAJAX .= 'if (document.getElementById("n'.$nID.'fld'.$j.'").checked) foundKidResponse = true;' . "\n";
                        }
                    }
                    if ($curr->hasShowKids)
                    {
                        $this->pageAJAX .= "\n".'if (foundKidResponse) { $("#node'.$nID.'kids").slideDown("50"); kidsVisible('.$nID.', true, true); } ' . "\n"
                        .'else { $("#node'.$nID.'kids").slideUp("50"); kidsVisible('.$nID.', false, true); }' . "\n".' }); ' . "\n";
                    }
                    if ($curr->isRequired())
                    {
                        $this->pageJSvalid .= "if (document.getElementById('n".$nID."VisibleID').value == 1) reqFormFldRadio(" . $nID . ", " . sizeof($curr->responses) . ");\n";
                    }
                    $ret .= '</div>' . (($curr->isOneLiner()) ? '</div>' : '')."\n"; 
                }
            }
            elseif ($curr->nodeType == 'Date')
            {
                $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '">' . $this->formDate($nID, $dateStr) . '</div>' . "\n";
                if ($curr->isRequired()) $this->pageJSvalid .= "if (document.getElementById('n".$nID."VisibleID').value == 1) reqFormFldDate(" . $nID . ");\n";
            }
            elseif ($curr->nodeType == 'Date Picker')
            {
                $this->pageAJAX .= '$( "#n'.$nID.'FldID" ).datepicker({ maxDate: "+0d" });' . "\n";
                $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '"><input name="n'.$nID.'fld" id="n'.$nID.'FldID" value="' . $dateStr . '" ' 
                        . $onKeyUp . ' type="text" class="dateFld form-control" ></div>' . "\n";
                if ($curr->isRequired()) $this->pageJSvalid .= "if (document.getElementById('n".$nID."VisibleID').value == 1) reqFormFld(" . $nID . ");\n";
            }
            elseif ($curr->nodeType == 'Time')
            {
                $this->pageFldList[] = 'n'.$nID.'fldHrID'; 
                $this->pageFldList[] = 'n'.$nID.'fldMinID';
                $ret .= str_replace('<label for="n'.$nID.'FldID">', '<label for="n'.$nID.'fldHrID"><label for="n'.$nID.'fldMinID"><label for="n'.$nID.'fldPMID">', 
                            str_replace('</label>', '</label></label></label>', $nodePrompt)) 
                        . '<div class="nFld' . $isOneLinerFld . '">' . $this->formTime($nID, $timeStr) . '</div>' . "\n";
                if ($curr->isRequired()) $this->pageJSvalid .= "if (document.getElementById('n".$nID."VisibleID').value == 1) reqFormFld(" . $nID . ");\n";
            }
            elseif ($curr->nodeType == 'Date Time')
            {
                $this->pageFldList[] = 'n'.$nID.'FldID'; 
                $this->pageFldList[] = 'n'.$nID.'fldHrID'; 
                $this->pageFldList[] = 'n'.$nID.'fldMinID';
                $ret .= str_replace('<label for="n'.$nID.'FldID">', '<label for="n'.$nID.'FldID"><label for="n'.$nID.'fldHrID"><label for="n'.$nID.'fldMinID"><label for="n'.$nID.'fldPMID">', 
                            str_replace('</label>', '</label></label></label></label>', $nodePrompt)) ."\n". '<div class="nFld' . $isOneLinerFld . '">
                <script type="text/javascript"> $(function() { $( "#n'.$nID.'FldID" ).datepicker({ maxDate: "+0d" }); }); </script>
                <div class="nFld' . $isOneLinerFld . ' ">
                    <input type="text" name="n'.$nID.'fld" id="n'.$nID.'FldID" value="' . $dateStr . '" ' . $onKeyUp . ' class="dateFld form-control disIn mR20" >
                    <div class="nPrompt disIn">at</div>
                    <div class="disIn mL20">' . $this->formTime($nID, $timeStr) . '</div>
                </div>' . "\n";
                if ($curr->isRequired()) $this->pageJSvalid .= "if (document.getElementById('n".$nID."VisibleID').value == 1) reqFormFld(" . $nID . ");\n";
            }
            elseif ($curr->nodeType == 'Feet Inches')
            {
                $this->pageFldList[] = 'n'.$nID.'fldFeetID'; 
                $this->pageFldList[] = 'n'.$nID.'fldInchID';
                $feet = floor($currNodeSessData/12); $inch = intVal($currNodeSessData)%12;
                $ret .= str_replace('<label for="n'.$nID.'FldID">', '<label for="n'.$nID.'fldFeetID"><label for="n'.$nID.'fldInchID">', str_replace('</label>', '</label></label>', $nodePrompt)) 
                . '<div class="nFld' . $isOneLinerFld . ' f20">
                <input type="hidden" name="n'.$nID.'fld" id="n'.$nID.'FldID" value="' . $currNodeSessData . '">
                <nobr><select name="n'.$nID.'fldFeet" id="n'.$nID.'fldFeetID" class="tinyDrop form-control disIn" onChange="return formChangeFeetInches(' . $nID . ');" >' . "\n";
                for ($i=0; $i<8; $i++) { $ret .= '<option value="'.$i.'" ' . (($feet == $i) ? 'SELECTED' : '') . ' >' . $i . '</option>'; }
                $ret .= "\n".'</select> feet,</nobr> 
                <nobr><select name="n'.$nID.'fldInch" id="n'.$nID.'fldInchID" class="tinyDrop form-control disIn mL20" onChange="return formChangeFeetInches(' . $nID . ');" ><option value=""></option>' . "\n";
                for ($i=0; $i<13; $i++) { $ret .= '<option value="'.$i.'" ' . (($inch == $i) ? 'SELECTED' : '') . ' >' . $i . '</option>'; }
                $ret .= "\n".'</select> inches</nobr></div>' . "\n"; 
                if ($curr->isRequired()) $this->pageJSvalid .= "if (document.getElementById('n".$nID."VisibleID').value == 1) formRequireFeetInches(" . $nID . ");\n";
            }
            elseif (in_array($curr->nodeType, ['Gender', 'Gender Not Sure']))
            {
                $currSessDataOther = $this->sessData->currSessData($nID, $tbl, $fld.'Other');
                $ret .= str_replace('<label for="n'.$nID.'FldID">', '', str_replace('</label>', '', $nodePrompt)) 
                    . '<div class="nFld' . $isOneLiner . '">' . "\n";
                $coreResponses = [ ["F", "Female"], ["M", "Male"], ["O", "Other:"] ];
                if ($curr->nodeType == 'Gender Not Sure') $coreResponses[] = ["?", "Not Sure"];
                foreach ($coreResponses as $j => $res)
                {
                    $this->pageFldList[] = 'n'.$nID.'fld'.$j;
                    $ret .= '<div class="' . $isOneLinerFld . '"><nobr>
                        <label for="n'.$nID.'fld'.$j.'" class="mR10">
                            <div class="disIn mR5"><input name="n'.$nID.'fld" id="n'.$nID.'fld'.$j.'" type="radio" autocomplete="off" 
                            onClick="checkNodeUp();' 
                            . (($res[0] != 'O') ? ' document.getElementById(\'n'.$nID.'fldOtherID\').value=\'\';' : '') 
                            . '" value="' . $res[0] . '" ' . (($currNodeSessData == $res[0]) ? 'CHECKED' : '') . '>
                            </div> ' . $res[1] . '
                            ' . (($res[0] == 'O') ? '<input type="text" class="form-control disIn" style="width: 160px;" 
                                onKeyUp="if (this.value.trim() != \'\') document.getElementById(\'n'.$nID.'fld'.$j.'\').checked=true;"
                                name="n'.$nID.'fldOther" id="n'.$nID.'fldOtherID" value="' . $currSessDataOther . '">' : '') . '
                        </label>
                    </nobr></div>' . "\n";
                }
                $genderSuggest = '';
                foreach ($GLOBALS["DB"]->getDefSet('Gender Identity') as $i => $gen) {
                    if (!in_array($gen->DefValue, ['Female', 'Male', 'Other', 'Not sure'])) {
                        $genderSuggest .= ', "' . $gen->DefValue . '"';
                    }
                }
                $this->pageAJAX .= '$( "#n'.$nID.'fldOtherID" ).autocomplete({ source: [' . substr($genderSuggest, 1) . '] });' . "\n";
                if ($curr->isRequired()) $this->pageJSvalid .= "if (document.getElementById('n".$nID."VisibleID').value == 1) formRequireGender(" . $nID . ");\n";
            }
            elseif ($curr->nodeType == 'Uploads')
            {
                $this->pageHasUpload[] = $nID;
                $ret .= $nodePrompt . $this->uploadTool($nID);
            }
            else // instruction only
            {
                $ret .= "\n" . str_replace('class="nPrompt"', 'class="nPromptInstr"', $nodePrompt) . "\n";
            }
            
        } // end default Node printer
        
        if (trim($promptNotesSpecial) != '')
        {
            $ret .= $this->printSpecial($nID, $promptNotesSpecial, $currNodeSessData);
        }
        
        $retKids = '';
        if (sizeof($tmpSubTier[1]) > 0 && !$curr->isLoopRoot())
        {
            foreach ($tmpSubTier[1] as $childNode) // recurse deez!..
            {
                if (!$this->allNodes[$childNode[0]]->isPage())
                {
                    $retKids .= $this->printNodePublic($childNode[0], $childNode, $currVisib);
                }
            } 
        }
        if (trim($retKids) != '' && $curr->hasShowKids) // then displaying children on page is conditional
        {
            $ret .= "\n".'<div id="node'.$nID.'kids" class="dis' . (($showKidsResponded) ? 'Blo' : 'Non') . ' nKids">' . $retKids . '</div>' . "\n";
        }
        else $ret .= $retKids;
        
        if ($prevLineThis >= 2) $ret .= '</div>';
        $ret .= "\n".'</div> <!-- end #node'.$nID.' -->' . "\n";
        
        if (substr($curr->nodeType, 0, 11) == 'Data Manip:') $this->closeManipBranch($nID);
        
        if ($curr->isPage() || $curr->isLoopRoot()) // then wrap completed page in form
        {
            $ret = $this->printNodePublicFormStart($nID) . $ret . '
            <div id="pageBtns" class="w100 pT10">
                <div id="formErrorMsg" class="w100 taR slRedDark" ></div>
                ' . $this->nodePrintButton($nID, $tmpSubTier, $promptNotesSpecial) . '
            </div>
            ' . $this->printNodePublicFormEnd($nID, $promptNotesSpecial)
            . $this->pageJSnode; // extra JS/HTML/CSS tagged on the end of specific nodes
        }
        
        if ($isFinalOfPrevLine && !$curr->isLoopRoot()) $ret .= '<div class="nodeGap"></div>';
        
        return $ret;
    }
    
    protected function postNodePublic($nID = -3, $tmpSubTier = array())
    {
        if (!$this->checkNodeConditions($nID)) return '';
        $ret = '';
        if (sizeof($tmpSubTier) == 0)
        {
            $tmpSubTier = $this->loadNodeSubTier($nID);
            // then we're at the page's root, so let's check this once
            if ($this->REQ->has('delItem') && sizeof($this->REQ->input('delItem')) > 0)
            {
                foreach ($this->REQ->input('delItem') as $delID)
                {
                    $loopTable = $GLOBALS["DB"]->closestLoop["obj"]->DataLoopTable;
                    $this->sessData->deleteDataItem($this->REQ->node, $loopTable, $delID);
                }
            }
        }
        $this->allNodes[$nID]->fillNodeRow($nID);
        $curr = $this->allNodes[$nID];
        
        if (substr($curr->nodeType, 0, 11) == 'Data Manip:') $this->loadManipBranch($nID);
        $hasParentDataManip = $this->hasParentDataManip($nID);
        
        if (!$this->postNodePublicCustom($nID, $tmpSubTier))
        {     // then run standard post
            if ($this->REQ->has('loop'))
            {
                $this->settingTheLoop(trim($this->REQ->input('loop')), intVal($this->REQ->loopItem));
            }
            if ($curr->nodeType == 'Uploads')
            {
                $ret .= $this->postUploadTool($nID);
            }
            elseif ($curr->isDataManip())
            {
                if ($this->REQ->has('dataManip'.$nID.'') && intVal($this->REQ->input('dataManip'.$nID.'')) == 1)
                {
                    if ($this->REQ->has('n'.$nID.'Visible') && intVal($this->REQ->input('n'.$nID.'Visible')) == 1)
                    {
                        $this->runDataManip($nID);
                    }
                    else $this->reverseDataManip($nID);
                }
            }
            elseif (strpos($curr->dataStore, ':') !== false)
            {
                list($tbl, $fld) = $curr->getTblFld();
                if ($this->REQ->has('loopItem') && intVal($this->REQ->loopItem) == -37)
                {    // signal from previous form to start a new row in the current set
                    $newID = $this->sessData->createNewDataLoopItem($nID);
                    if ($newID > 0)
                    {
                        $this->REQ->loopItem = $newID;
                        $this->settingTheLoop(trim($this->REQ->input('loop')), intVal($this->REQ->loopItem));
                    }
                    //$this->updateCurrNode($this->nextNode($this->currNode()));
                }
                elseif (!$curr->isInstruct() && $tbl != '' && $fld != '')
                {
                    $newVal = (($this->REQ->has('n'.$nID.'fld')) ? $this->REQ->input('n'.$nID.'fld') : '');
                    if ($curr->nodeType == 'Checkbox')
                    {
                        $this->sessData->currSessDataCheckbox($nID, $tbl, $fld, 'update', 
                            (($this->REQ->has('n'.$nID.'fld')) ? $this->REQ->input('n'.$nID.'fld') : []));
                    }
                    else
                    {
                        if (in_array($curr->nodeType, array('Date', 'Date Picker')))
                        {
                            $newVal = date("Y-m-d", strtotime($newVal));
                        }
                        elseif ($curr->nodeType == 'Date Time')
                        {
                            $newVal = date("Y-m-d", strtotime($newVal)).' '.$this->postFormTimeStr($nID);
                        }
                        elseif ($curr->nodeType == 'Password')
                        {
                            $newVal = md5($newVal);
                        }
                        $this->sessData->currSessData($nID, $tbl, $fld, 'update', $newVal, $hasParentDataManip);
                        if (in_array($curr->nodeType, ['Gender', 'Gender Not Sure']))
                        {
                            $this->sessData->currSessData($nID, $tbl, $fld.'Other', 'update', 
                                (($this->REQ->has('n'.$nID.'fldOther')) 
                                    ? $this->REQ->input('n'.$nID.'fldOther') 
                                    : ''), $hasParentDataManip);
                        }
                    }
                }
            }
        }
        if (sizeof($tmpSubTier[1]) > 0)
        {
            foreach ($tmpSubTier[1] as $childNode)
            {
                if (!$this->allNodes[$childNode[0]]->isPage())
                {
                    $this->postNodePublic($childNode[0], $childNode);
                }
            }
        }
        
        if (substr($curr->nodeType, 0, 11) == 'Data Manip:') $this->closeManipBranch($nID);
        
        return $ret;
    }
    
    protected function checkResponses($curr, $fldForeignTbl)
    {
        if (isset($curr->responseSet) && strpos($curr->responseSet, 'LoopItems::') !== false)
        {
            $loop = str_replace('LoopItems::', '', $curr->responseSet);
            $currLoopItems = $this->sessData->getLoopRows($loop);
            if ($currLoopItems && sizeof($currLoopItems) > 0)
            {
                foreach ($currLoopItems as $i => $row)
                {
                    $curr->responses[$i] = new SLNodeResponses;
                    $curr->responses[$i]->NodeResValue = $row->getKey();
                    $curr->responses[$i]->NodeResEng = $this->getLoopItemLabel($loop, $row, $i);
                }
            }
        }
        elseif (sizeof($curr->responses) == 0 && trim($fldForeignTbl) != '' && isset($this->sessData->dataSets[$fldForeignTbl]) 
            && sizeof($this->sessData->dataSets[$fldForeignTbl]) > 0)
        {
            foreach ($this->sessData->dataSets[$fldForeignTbl] as $i => $row)
            {
                $loop = ((isset($GLOBALS["DB"]->tblLoops[$fldForeignTbl])) ? $GLOBALS["DB"]->tblLoops[$fldForeignTbl] : $fldForeignTbl);
                // what about tables with multiple loops??
                $curr->responses[$i] = new SLNodeResponses;
                $curr->responses[$i]->NodeResValue = $row->getKey();
                $curr->responses[$i]->NodeResEng = $this->getLoopItemLabel($loop, $row, $i);
            }
        }
        return $curr;
    }
    
    protected function getLoopItemLabel($loop, $itemRow = array(), $itemInd = -3)
    {
        $name = $this->getLoopItemLabelCustom($loop, $itemRow, $itemInd);
        if (trim($name) != '') return $name;
        return '';
    }
    
    protected function getLoopItemLabelCustom($loop, $itemRow = array(), $itemInd = -3) { return ''; }
    protected function getLoopItemCntLabelCustom($loop, $itemInd = -3) { return -3; }
    
    protected $loopItemsCustBtn = '';
    
    protected function printSetLoopNav($nID, $loopName)
    {
        $this->settingTheLoop($loopName);
        if ($this->allNodes[$nID]->isStepLoop())
        {
            $this->sessData->getLoopDoneItems($loopName);
            if ($this->sessData->loopItemsNextID > 0)
            {
                $this->loopItemsCustBtn = '<a href="javascript:;" class="fR btn btn-lg btn-primary" id="nFormNextStepItem"
                    ><i class="fa fa-arrow-circle-o-right"></i> Next ' . $GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular . ' Details</a>';
                $this->pageAJAX .= '$("#nFormNextStepItem").click(function() { document.getElementById("loopItemID").value="' 
                    . $this->sessData->loopItemsNextID . '"; document.getElementById("jumpToID").value="-3"; '
                    . 'document.getElementById("stepID").value="next"; return runFormSub(); });' . "\n";
            }
        }
        
        $labelFirstLet = substr(strtolower($GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular), 0, 1);
        $limitTxt = '';
        if ($GLOBALS["DB"]->closestLoop["obj"]->DataLoopMaxLimit > 0 
            && isset($this->sessData->loopItemIDs[$loopName])
            && sizeof($this->sessData->loopItemIDs[$loopName]) > $GLOBALS["DB"]->closestLoop["obj"]->DataLoopWarnLimit)
        {
            $limitTxt .= '<br /><i class="f16 gry9 mL20">( limit of ' . $GLOBALS["DB"]->closestLoop["obj"]->DataLoopMaxLimit . ' )</i>';
        }
        $ret = '<div class="jumbotron">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-10">
                    <div class="f26 mBn10 mTn10">';
                        if ($this->allNodes[$nID]->isStepLoop()) $ret .= $GLOBALS["DB"]->closestLoop["obj"]->DataLoopPlural . ' to add details for:' . "\n";
                        elseif (sizeof($this->sessData->loopItemIDs[$loopName]) == 0) $ret .= '<i>No ' . strtolower($GLOBALS["DB"]->closestLoop["obj"]->DataLoopPlural) . ' added yet.</i>' . "\n";
                        else $ret .= 'Current ' . strtolower($GLOBALS["DB"]->closestLoop["obj"]->DataLoopPlural) . ' added:' . "\n";
                    $ret .= '</div>' . "\n";
                    if (sizeof($this->sessData->loopItemIDs[$loopName]) > 0)
                    {
                        foreach ($this->sessData->loopItemIDs[$loopName] as $setIndex => $loopItem)
                        {
                            $ret .= $this->printSetLoopNavRow($nID, 
                                $this->sessData->getRowById($GLOBALS["DB"]->dataLoops[$loopName]->DataLoopTable, $loopItem), 
                                $setIndex
                            );
                        }
                    }
                    $this->pageAJAX .= '$(".editLoopItem").click(function() {
                        var id = $(this).attr("id").replace("editLoopItem", "").replace("arrowLoopItem", "");
                        document.getElementById("loopItemID").value=id;
                        return runFormSub();
                    });' . "\n";
                    if (!$this->allNodes[$nID]->isStepLoop())
                    {
                        $ret .= '<button type="button" id="nFormAdd" class="btn btn-lg btn-default mT20 mL20 '
                            . ((sizeof($this->sessData->loopItemIDs[$loopName]) < $GLOBALS["DB"]->closestLoop["obj"]->DataLoopMaxLimit) ? 'disBlo' : 'disNon')
                            . '"><i class="fa fa-plus-circle"></i> Add '
                            . ((sizeof($this->sessData->loopItemIDs[$loopName]) == 0) 
                                ? 'a'.((in_array($labelFirstLet, array('a', 'e', 'i', 'o', 'u'))) ? 'n' : '') : 'another') . ' ' 
                            . strtolower($GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular) . '</button>' 
                            . $limitTxt . "\n";
                        $this->pageAJAX .= 'var currItemCnt = ' . sizeof($this->sessData->loopItemIDs[$loopName]) . ';
                        var maxItemCnt = ' . $GLOBALS["DB"]->closestLoop["obj"]->DataLoopMaxLimit . ';
                        $("#nFormAdd").click(function() {
                            document.getElementById("loopItemID").value="-37";
                            return runFormSub();
                        });
                        $(".delLoopItem").click(function() {
                            var id = $(this).attr("id").replace("delLoopItem", "");
                            document.getElementById("delItem"+id+"").checked=true;
                            document.getElementById("wrapItem"+id+"On").style.display="none";
                            document.getElementById("wrapItem"+id+"Off").style.display="block";
                            updateCnt(-1);
                            return true;
                        });
                        $(".unDelLoopItem").click(function() {
                            var id = $(this).attr("id").replace("unDelLoopItem", "");
                            document.getElementById("delItem"+id+"").checked=false;
                            document.getElementById("wrapItem"+id+"On").style.display="block";
                            document.getElementById("wrapItem"+id+"Off").style.display="none";
                            updateCnt(1);
                            return true;
                        });
                        function updateCnt(addCnt) {
                            currItemCnt += addCnt;
                            if (currItemCnt < maxItemCnt) document.getElementById("nFormAdd").style.display="block";
                            else document.getElementById("nFormAdd").style.display="none";
                            return true;
                        }' . "\n";
                    }
                $ret .= '</div>
            </div>
        </div>';
        if (!$this->allNodes[$nID]->isStepLoop())
        {
            $this->nextBtnOverride = 'Done Adding ' . $GLOBALS["DB"]->closestLoop["obj"]->DataLoopPlural;
        }
        elseif (sizeof($this->sessData->loopItemIDs[$loopName]) == sizeof($this->sessData->loopItemIDsDone))
        {
            $this->nextBtnOverride = 'Done With ' . $GLOBALS["DB"]->closestLoop["obj"]->DataLoopPlural;
        }
        //else $ret .= '<div class="p10 fC"></div>' . "\n"; 
        return $ret;
    }
    
    protected function printSetLoopNavRowCustom($nID, $loopItem, $setIndex) { return ''; }
    protected function printSetLoopNavRow($nID, $loopItem, $setIndex)
    {
        $ret = $this->printSetLoopNavRowCustom($nID, $loopItem, $setIndex);
        if ($ret != '') return $ret;
        $itemLabel = $this->getLoopItemLabel($GLOBALS["DB"]->closestLoop["loop"], $loopItem, $setIndex);
        $editLnk = '<a href="javascript:;" id="editLoopItem' . $loopItem->getKey() . '" class="editLoopItem f14 mR20"><i class="fa fa-pencil fa-flip-horizontal"></i> Edit</a>';
        $ret = '<div class="wrapLoopItem"><a name="item'.$setIndex.'"></a>
        <div id="wrapItem' . $loopItem->getKey() . 'On" class="disIn">';
            if ($this->allNodes[$nID]->isStepLoop())
            {
                $ret .= '<a href="javascript:;" id="arrowLoopItem' . $loopItem->getKey() . '" class="editLoopItem slBlueLight f24 mR10';
                if ($this->sessData->loopItemsNextID > 0 && $this->sessData->loopItemsNextID == $loopItem->getKey()) $ret .= '"><i class="fa fa-arrow-circle-o-right"></i>';
                else $ret .= ((!in_array($loopItem->getKey(), $this->sessData->loopItemIDsDone)) ? ' opac10' : '') . '"><i class="fa fa-check"></i>';
                $ret .= '</a> <h2 class="disIn m0">' . $itemLabel . '</h2> ' . $editLnk;
            }
            else
            {
                $ret .= '<h2 class="disIn m0">' . $itemLabel . '</h2>
                <div>' . $editLnk . ' <a href="javascript:;" id="delLoopItem' . $loopItem->getKey() . '" class="delLoopItem slRedDark nFormLnkDel f14 nobld mL20"><i class="fa fa-times"></i> Delete</a></div>
                <input type="checkbox" name="delItem[]" id="delItem' . $loopItem->getKey() . '" value="' . $loopItem->getKey() . '" class="disNon">';
            }
        $ret .= '</div>' . "\n";
        if (!$this->allNodes[$nID]->isStepLoop())
        {
            $ret .= '<div id="wrapItem' . $loopItem->getKey() . 'Off" class="wrapItemOff">
                <i class="mR20">Deleted: ' . $itemLabel . '</i> 
                <a href="javascript:;" id="unDelLoopItem' . $loopItem->getKey() . '" class="unDelLoopItem nFormLnkEdit f14 nobld mL20"><i class="fa fa-undo"></i> Undo</a>
            </div>';
        }
        $ret .= '</div>' . "\n";
        return $ret;
    }
    
    
    protected function cleanDateVal($dateStr)
    {
        if ($dateStr == '0000-00-00' || $dateStr == '1970-01-01' || trim($dateStr) == '') return '';
        return $dateStr;
    }
    
    protected function formDate($nID, $dateStr = '00/00/0000')
    {
        list($month, $day, $year) = array('', '', '');
        if (trim($dateStr) != '')
        {
            list($month, $day, $year) = explode('/', $dateStr);
            if (intVal($month) == 0 || intVal($day) == 0 || intVal($year) == 0) list($month, $day, $year) = array('', '', '');
        }
        //if (intVal($month) == 0 || intVal($day) == 0 || intVal($year) == 0) list($month, $day, $year) = array('MM', 'DD', 'YYYY');
        $months = [ 1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
                    7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December' ];
        $ret = '<input type="hidden" name="n'.$nID.'fld" id="n'.$nID.'FldID" value="' . $dateStr . '" >
        <div class="timeWrap"><nobr>
        <select name="n'.$nID.'fldMonth" id="n'.$nID.'fldMonthID" onChange="dateChange(' . $nID . ');" 
            class="form-control disIn mR5" style="width: 150px;">
            <option value="00" ' . (($month == 'MM' || intVal($month) == 0) ? 'SELECTED' : '') . ' >month</option>';
            foreach ($months as $m => $mm)
            {
                $ret .= '<option value="' . (($m < 10) ? 0 : '') . $m . '" ' 
                    . (($m == $month) ? 'SELECTED' : '') . '>' . $mm . '</option>';
            }
        $ret .= '</select>
        <select name="n'.$nID.'fldDay" id="n'.$nID.'fldDayID" onChange="dateChange(' . $nID . ');" 
            class="form-control disIn mL10 mR5" style="width: 70px;">
            <option value="00" ' . (($day == 'DD' || intVal($day) == 0) ? 'SELECTED' : '') . ' >day</option>';
            for ($i = 1; $i < 32; $i++)
            {
                $ret .= '<option value="' . (($i < 10) ? 0 : '') . $i . '" ' 
                    . (($i == $day) ? 'SELECTED' : '') . '>' . $i . '</option>';
            }
        $ret .= '</select>,
        <select name="n'.$nID.'fldYear" id="n'.$nID.'fldYearID" onChange="dateChange(' . $nID . ');" 
            class="form-control disIn mL5" style="width: 90px;">
            <option value="0000" ' . (($year == 'YYYY' || intVal($year) == 0) ? 'SELECTED' : '') . ' >year</option>';
            for ($i = intVal(date("Y")); $i > (intVal(date("Y"))-80); $i--) 
            {
                $ret .= '<option value="' . (($i < 10) ? 0 : '') . $i . '" ' 
                    . (($i == $year) ? 'SELECTED' : '') . '>' . $i . '</option>';
            }
        $ret .= '</select>
        </nobr></div>';
        return $ret;
    }
    
    protected function formTime($nID, $timeStr = '00:00:00')
    {
        $timeArr = explode(':', $timeStr); 
        foreach ($timeArr as $i => $t) $timeArr[$i] = intVal($timeArr[$i]);
        if (!isset($timeArr[0])) $timeArr[0] = 0; if (!isset($timeArr[1])) $timeArr[1] = 0;
        $timeArr[3] = 'AM';
        if ($timeArr[0] > 11)
        {
            $timeArr[3] = 'PM'; 
            if ($timeArr[0] > 12) $timeArr[0] = $timeArr[0]-12;
        }
        if ($timeArr[0] == 0 && $timeArr[1] == 0)
        {
            $timeArr[0] = -1; 
            $timeArr[1] = -1; 
        }
        $ret = "\n".'<div class="timeWrap f20 disIn"><nobr><select name="n'.$nID.'fldHr" id="n'.$nID.'fldHrID" class="timeDrop form-control disIn">
            <option value="0" ' . (($timeArr[0] == -1) ? 'SELECTED' : '') . ' >hour</option>' . "\n";
        for ($i=0; $i<13; $i++)
        {
            $ret .= '<option value="' . $i . '" ' . (($i == $timeArr[0]) ? 'SELECTED' : '') 
                . ' >' . $i . '</option>';
        }
        $ret .= "\n".'</select> : <select name="n'.$nID.'fldMin" id="n'.$nID.'fldMinID" class="timeDrop form-control disIn">
            <option value="0" >min</option>' . "\n";
        for ($i=0; $i<60; $i+=5)
        {
            $ret .= '<option value="' . $i . '" ' 
                . (($i == $timeArr[1] || ($timeArr[1] == -1 && $i == 0)) ? 'SELECTED' : '') 
                . ' >' . (($i<10) ? '0'.$i : $i) . '</option>';
        }
        $ret .= "\n".'</select>
        <select name="n'.$nID.'fldPM" id="n'.$nID.'fldPMID" class="timeDrop form-control disIn">
            <option value="AM" ' . (($timeArr[3] == 'AM') ? 'SELECTED' : '') . ' >AM</option>
            <option value="PM" ' . (($timeArr[3] == 'PM') ? 'SELECTED' : '') . ' >PM</option>
        </select></nobr></div>' . "\n";
        return $ret;
    }
    
    protected function postFormTimeStr($nID)
    {
        $hr = intVal($this->REQ->input('n'.$nID.'fldHr'));
        if ($this->REQ->input('n'.$nID.'fldPM') == 'PM' && $hr < 12) $hr += 12;
        $min = intVal($this->REQ->input('n'.$nID.'fldMin'));
        return ((intVal($hr) < 10) ? '0' : '').$hr . ':' . ((intVal($min) < 10) ? '0' : '').$min . ':00';
    }
    

    
    protected function genRandStr($len)
    {
        return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 1) 
             . substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, ($len-1));
    }
    
    protected function checkRandStr($tbl, $fld, $str)
    {
        $modelObj = array();
        eval("\$modelObj = " . $GLOBALS["DB"]->modelPath($tbl) 
                . "::where('" . $fld . "', '" . $str . "')->get();");
        return (!$modelObj || sizeof($modelObj) <= 0);
    }
    
    protected function getRandStr($tbl, $fld, $len)
    {
        $str = $this->genRandStr($len);
        while (!$this->checkRandStr($tbl, $fld, $str)) $str = $this->genRandStr($len);
        return $str;
    }
    
    
    //////////////////////////////////////////////////////////////////////
    //  START FILE UPLOADING FUNCTIONS
    //////////////////////////////////////////////////////////////////////
    
    protected function loadUploadTypes()
    {
        if (sizeof($this->uploadTypes) > 0) return $this->uploadTypes;
        if (isset($GLOBALS["DB"]->sysOpts["upload-types"])) $this->uploadTypes = $GLOBALS["DB"]->getDefSet($GLOBALS["DB"]->sysOpts["upload-types"]);
        if (sizeof($this->uploadTypes) == 0) $this->uploadTypes = $GLOBALS["DB"]->getDefSet('Upload Types');
        return $this->uploadTypes;
    }
    
    protected function getUploadFolder($nID = -3) { return '/up/'; }
    protected function getUploadFile($nID) { return ''; }
    protected function getUploadLinks($nID) { return array(); }
    protected function getUploadSet($nID) { return '+'; }
    protected function prevUploadList($nID) { return array(); }
    
    public function retrieveUploadFile($upID = '')
    {
        if (!$this->isPublic() && !$this->isAdminUser() && !$this->isOwnerUser())
        {
            return $this->retrieveUploadFail();
        }
        $upRequest = array();
        $this->loadPrevUploadDeets();
        if ($this->upDeets && sizeof($this->upDeets) > 0)
        {
            foreach ($this->upDeets as $i => $up)
            {
                if ($up["filename"] == $upID)
                {
                    if ($up["privacy"] != 'Public' && !$this->isAdminUser() && !$this->isOwnerUser())
                    {
                        return $this->retrieveUploadFail();
                    }
                    return $this->previewImg($up);
                }
            }
        }
        return $this->retrieveUploadFail();
    }
    
    public function retrieveUploadFail()
    {
        return '';
    }
    
    public function previewImg($up)
    {
        $handler = new File($up["file"]);
        $file_time = $handler->getMTime(); // Get the last modified time for the file (Unix timestamp)
        $lifetime = 86400; // One day in seconds
        $header_etag = md5($file_time . $up["file"]);
        $header_last_modified = gmdate('r', $file_time);
        $headers = array(
            'Content-Disposition'     => 'inline; filename="' . $this->coreID . '-' . (1+$up["ind"]) . '-' . $up["fileOrig"] . '"',
            'Last-Modified'         => $header_last_modified,
            'Cache-Control'         => 'must-revalidate',
            'Expires'                 => gmdate('r', $file_time + $lifetime),
            'Pragma'                 => 'public',
            'Etag'                     => $header_etag
        );
        // Is the resource cached?
        $h1 = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $header_last_modified);
        $h2 = (isset($_SERVER['HTTP_IF_NONE_MATCH']) && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $header_etag);
        if ($h1 || $h2) return Response::make('', 304, $headers); 
        // File (image) is cached by the browser, so we don't have to send it again
        
        $headers = array_merge($headers, array(
            'Content-Type'             => $handler->getMimeType(),
            'Content-Length'         => $handler->getSize()
        ));
        return Response::make(file_get_contents($up["file"]), 200, $headers);
    }
    
    protected function uploadTool($nID)
    {
        $this->loadUploadTypes();
        $this->pageAJAX .= 'window.refreshUpload = function () { $("#uploadAjax").load("?ajax=1&upNode='.$nID.'"); }' . "\n";
        $this->pageJSvalid .= "if (document.getElementById('n".$nID."VisibleID').value == 1) reqUploadTitle(" . $nID . ");\n";
        $ret = ((!$this->REQ->has('ajax')) ? '<div id="uploadAjax">' : '') 
            . view( 'vendor.survloop.upload-tool', [
                "nID"                => $nID,
                "uploadTypes"        => $this->uploadTypes,
                "isPublic"            => $this->isPublic(), 
                "getPrevUploads"    => $this->getPrevUploads($nID, true)
            ])->render() 
            . ((!$this->REQ->has('ajax')) ? '</div>' : '');
        return $ret;
    }
    
    protected function loadPrevUploadDeets($nID = -3)
    {
        $this->upLinks = $this->getUploadLinks($nID);
        $this->uploads = $this->prevUploadList($nID, $this->upLinks);
        $this->upDeets = array();
        if ($this->uploads && sizeof($this->uploads) > 0)
        {
            foreach ($this->uploads as $i => $upRow)
            {
                $this->upDeets[$i]["ind"]         = $i;
                $this->upDeets[$i]["privacy"]     = $upRow->privacy;
                $this->upDeets[$i]["ext"] = '';
                if (trim($upRow->upFile) != '')
                {
                    $tmpExt = explode(".", $upRow->upFile);
                    $this->upDeets[$i]["ext"] = $tmpExt[(sizeof($tmpExt)-1)];
                }
                $this->upDeets[$i]["filename"]     = $upRow->storeFile . '.' . $this->upDeets[$i]["ext"];
                $this->upDeets[$i]["file"]         = $this->getUploadFolder($nID) . $upRow->storeFile . '.' . $this->upDeets[$i]["ext"];
                $this->upDeets[$i]["filePub"]     = '/up/' . $this->coreID . '/' . $upRow->storeFile . '.' . $this->upDeets[$i]["ext"];
                $this->upDeets[$i]["fileOrig"]     = $upRow->upFile;
                $this->upDeets[$i]["fileLnk"]     = '<a href="' . $this->upDeets[$i]["filePub"] . '" target="_blank">' . $upRow->upFile . '</a>';
                $this->upDeets[$i]["youtube"]     = '';
                $this->upDeets[$i]["vimeo"]     = '';
                if ($this->REQ->has('step') && $this->REQ->step == 'uploadDel' 
                    && $this->REQ->has('alt') && intVal($this->REQ->alt) == $upRow->id)
                {
                    if (file_exists($this->upDeets[$i]["file"]) && trim($upRow->type) 
                        != $GLOBALS["DB"]->getDefID($GLOBALS["DB"]->sysOpts["upload-types"], 'Video'))
                    {
                        unlink($this->upDeets[$i]["file"]);
                    }
                    $this->sessData->deleteDataItem($nID, 'Evidence', $upRow->id);
                }
                else
                {
                    if (trim($upRow->type) == $GLOBALS["DB"]->getDefID($GLOBALS["DB"]->sysOpts["upload-types"], 'Video'))
                    {
                        if (stripos($upRow->video, 'youtube') !== false || stripos($upRow->video, 'youtu.be') !== false)
                        {
                            $this->upDeets[$i]["fileLnk"] = '<a href="'.$upRow->video.'" target="_blank">' 
                                . str_replace('https://www.youtube', 'youtube', $upRow->video) . '</a>';
                            $this->upDeets[$i]["youtube"] = $this->getYoutubeID($upRow->video);
                        }
                    }
                    elseif (!file_exists($this->upDeets[$i]["file"])) 
                    {
                        $this->upDeets[$i]["fileLnk"] .= ' &nbsp;&nbsp;<span class="slRedDark"><i class="fa fa-exclamation-triangle"></i> <i>File Not Found</i></span>';
                    }
                }
            }
        }
        return true;
    }
    
    protected function getPrevUploads($nID, $edit = false)
    {
        $this->loadUploadTypes();
        $height = 150; $width = 330;
        $this->loadPrevUploadDeets($nID);
        $upSet = $this->getUploadSet($nID);
        return view( 'vendor.survloop.upload-previous', [
            "nID"                => $nID,
            "REQ"                => $this->REQ,
            "height"            => $height,          
            "width"                => $width,
            "uploads"            => $this->uploads, 
            "upDeets"            => $this->upDeets, 
            "uploadTypes"        => $this->uploadTypes, 
            "v"                    => $this->v
        ])->render();
    }
    
    protected function postUploadTool($nID)
    {
        $ret = '';
        $this->loadPrevUploadDeets($nID);
        if (sizeof($this->uploads) > 0)
        {
            foreach ($this->uploads as $i => $upRow)
            {
                if ($this->REQ->has('up'.$upRow->id.'EditVisib') 
                    && intVal($this->REQ->input('up'.$upRow->id.'EditVisib')) == 1)
                {
                    $upArr = [ 
                        'id'         => $upRow->id, 
                        'title'     => $this->REQ->input('up'.$upRow->id.'EditTitle'), 
                        'desc'         => $this->REQ->input('up'.$upRow->id.'EditDesc'), 
                        'type'         => $this->REQ->input('up'.$upRow->id.'EditType'), 
                        'privacy'     => $this->REQ->input('up'.$upRow->id.'EditPrivacy')
                    ];
                    $ret .= ' ' . $this->updateUploadRecord($nID, $upArr);
                }
            }
        }
        if ($this->REQ->has('step') && $this->REQ->has('up'.$nID.'Type'))
        {
            $upArr = [
                'type'             => $this->REQ->input('up'.$nID.'Type'), 
                'title'         => $this->REQ->input('up'.$nID.'Title'), 
                'desc'             => $this->REQ->input('up'.$nID.'Desc'), 
                'privacy'         => $this->REQ->input('up'.$nID.'Privacy'), 
                'upFile'         => '', 
                'extension'     => '', 
                'mimetype'         => '', 
                'size'             => 0, 
                'storeFile'     => '', 
                'video'         => '', 
                'vidDur'         => -1
            ];
            if ($this->REQ->has('up'.$nID.'Vid') && $this->REQ->input('up'.$nID.'Type') 
                == $GLOBALS["DB"]->getDefID($GLOBALS["DB"]->sysOpts["upload-types"], 'Video'))
            {
                $upArr["video"] = $this->REQ->input('up'.$nID.'Vid');
                $upArr["vidDur"] = $this->getYoutubeDuration($upArr["video"]);
                $this->storeUploadRecord($nID, $upArr, $this->upLinks);
            }
            elseif ($this->REQ->hasFile('up'.$nID.'File')) // file upload
            {
                $upArr["upFile"]     = $this->REQ->file('up'.$nID.'File')->getClientOriginalName();
                $upArr["extension"] = $this->REQ->file('up'.$nID.'File')->getClientOriginalExtension();
                $upArr["mimetype"]     = $this->REQ->file('up'.$nID.'File')->getMimeType();
                $upArr["size"]         = $this->REQ->file('up'.$nID.'File')->getSize();
                if (in_array($upArr["extension"], array("gif", "jpeg", "jpg", "png", "pdf")) 
                    && in_array($upArr["mimetype"], array("image/gif", "image/jpeg", "image/jpg", "image/pjpeg", "image/x-png", "image/png", "application/pdf")))
                {
                    if (!$this->REQ->file('up'.$nID.'File')->isValid())
                    {
                        $ret .= '<div class="slRedDark">Upload Error.' . /* $_FILES["up".$nID."File"]["error"] . */ '</div>';
                    }
                    else
                    {
                        $upFold = $this->getUploadFolder($nID);
                        $this->mkNewFolder($upFold);
                        $upArr["storeFile"] = $this->getUploadFile($nID);
                        $filename = $upArr["storeFile"] . '.' . $upArr["extension"];
                        if ($this->debugOn || true) { $ret .= "saving as filename: " . $upFold.$filename . "<br>"; }
                        if (file_exists($upFold.$filename)) Storage::delete($upFold.$filename);
                        $this->REQ->file('up'.$nID.'File')->move($upFold, $filename);
                        $this->storeUploadRecord($nID, $upArr, $this->upLinks);
                    }
                }
                else $ret .= '<div class="slRedDark">Invalid file. Please check the format and try again.</div>';
            }
        }
        return $ret;
    }
    
    // $upArr = array('type' => '', 'title' => '', 'desc' => '', 'privacy' => '', 'upFile' => '', 'storeFile' => '', 'video' => '', 'vidDur' => 0);
    protected function storeUploadRecord($nID, $upArr, $upLinks) { return true; }
    protected function updateUploadRecord($nID, $upArr) { return true; }
    
    protected function checkBaseFolders() { return true; }
    protected function mkNewFolder($fold)
    {
        $this->checkBaseFolders();
        $this->checkFolder($fold);
        return true;
    }
    
    function checkFolder($fold)
    {
        if (!is_dir(storage_path($fold))) Storage::MakeDirectory(storage_path($fold));
        return true;
    }

    protected function getYoutubeDuration($vidURL)
    {
        if (stripos($vidURL, 'youtube') !== false)
        {

        }
        return -1;
    }
    
    protected function getYoutubeID($vidURL)
    {
        if (strpos(strtolower($vidURL), 'https://youtu.be/') !== false)
        {
            return str_ireplace('https://youtu.be/', '', $vidURL);
        }
        preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $vidURL, $matches);
        return $matches[1];
    }
    
    protected function printValList($val)
    {
        return str_replace(';;', ', ', $val);
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
    
    protected function printHeight($val)
    {
        return (floor($val/12))."' ".floor($val%12).'"';
    }
    
} // end of SurvFormTree class
