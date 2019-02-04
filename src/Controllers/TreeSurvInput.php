<?php
/**
  * TreeSurvInput is a mid-level class using a standard branching tree, mostly for 
  * processing the input SurvLoop's surveys and pages.
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
use App\Models\SLNode;
use App\Models\SLContact;
use App\Models\SLEmails;
use App\Models\SLTokens;
use App\Models\SLUsersRoles;
use SurvLoop\Controllers\TreeSurvUpload;

class TreeSurvInput extends TreeSurvUpload
{
    public $nodeTypes = [
        'Radio', 'Checkbox', 'Drop Down', 'Text', 'Long Text', 'Text:Number', 'Slider', 'Email', 'Password', 
        'Date', 'Date Picker', 'Date Time', 'Time', 'Gender', 'Gender Not Sure', 'Feet Inches', 
        'U.S. States', 'Countries', 'Uploads', 'Spreadsheet Table', 'User Sign Up', 'Hidden Field', 
        'Spambot Honey Pot', 'Other/Custom' 
    ];
    
    public $nodeSpecialTypes = [
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
                foreach ($loopCycle as $i => $loopItem) $list .= ',' . $loopItem->getKey();
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
            
            if (sizeof($tmpSubTier[1]) > 0) {
                $this->tableDat = $this->loadTableDat($curr, [], $tmpSubTier);
                $GLOBALS["SL"]->currCyc["tbl"][0] = $this->tableDat["tbl"];
//if ($nID == 589) { echo '<pre>'; print_r($this->tableDat); echo '</pre>'; exit; }
                for ($i = 0; $i < $this->tableDat["maxRow"]; $i++) {
                    $hasRow = false;
                    $fldVals = [];
                    foreach ($tmpSubTier[1] as $k => $kidNode) {
                        list($kidTbl, $kidFld) = $this->allNodes[$kidNode[0]]->getTblFld();
                        $fldVals[$kidFld] = '';
                        $tmpFldName = 'n' . $kidNode[0] . $nSffx . 'tbl' . $i . 'fld';
                        if ($GLOBALS["SL"]->REQ->has($tmpFldName)) {
                            if (is_array($GLOBALS["SL"]->REQ->get($tmpFldName))) {
                                if (sizeof($GLOBALS["SL"]->REQ->get($tmpFldName)) > 0) {
                                    $hasRow = true;
                                }
                            } else {
                                if (trim($GLOBALS["SL"]->REQ->get($tmpFldName)) != '') {
                                    $hasRow = true;
                                }
                                if ($kidTbl == $this->tableDat["tbl"]) {
                                    $fldVals[$kidFld] = trim($GLOBALS["SL"]->REQ->get($tmpFldName));
                                }
                            }
                        }
                    }
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
                            if ($recObj) {
                                $this->tableDat["rows"][$i]["id"] = $recObj->getKey();
                            } else {
                                $this->tableDat["rows"][$i]["id"] = -3;
                            }
                        }
                    } else { // user adds rows as they go
                        if ($hasRow) {
                            $matches = $this->sessData->getRowIDsByFldVal($this->tableDat["tbl"], $fldVals, true);
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
            
            if (!$this->postNodePublicCustom($nID, $tmpSubTier)) { // then run standard post
                if ($GLOBALS["SL"]->REQ->has('loop')) {
                    $this->settingTheLoop(trim($GLOBALS["SL"]->REQ->input('loop')), 
                        intVal($GLOBALS["SL"]->REQ->loopItem));
                }
                if ($curr->nodeType == 'Uploads') {
                    if ($this->REQstep != 'autoSave') $ret .= $this->postUploadTool($nID);
                } elseif ($curr->isDataManip()) {
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
                        $newVal = $this->getNodeFormFldBasic($nID, $curr);
                        if ($curr->nodeType == 'Checkbox' || $curr->isDropdownTagger()) {
                            $newVal = $this->postNodeTweakNewVal($curr, $newVal);
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
                                $curr = $this->checkResponses($curr, $fldForeignTbl);
                                $this->sessData->currSessDataCheckbox($nID, $tbl, $fld, 'update', $newVal, $curr, 
                                    $itemInd, $itemID);
                            }
                        } else {
                            $this->sessData->currSessData($nID, $tbl, $fld, 'update', $newVal, $hasParManip, 
                                $itemInd, $itemID);
                        }
                        // Check for Layout Sub-Response between each Checkbox Response
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
                        if (in_array($curr->nodeType, ['Checkbox', 'Radio', 'Gender', 'Gender Not Sure'])
                            && sizeof($curr->fldHasOther) > 0) {
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
                                    if ($branchRowID > 0) {
                                        $GLOBALS["SL"]->currCyc["res"] = [$tbl, 'res' . $j, $res->NodeResValue];
                                        $this->sessData->startTmpDataBranch($tbl, $branchRowID);
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
        
        $this->closePostNodePublic($nID, $tmpSubTier, $curr);
        return $ret;
    }
    
    protected function openPostNodePublic($nID = -3, $tmpSubTier = [], $curr = [])  { return true; }
    protected function closePostNodePublic($nID = -3, $tmpSubTier = [], $curr = []) { return true; }
    
    protected function postNodeTweakNewVal($curr, $newVal)
    {
        if ($curr->nodeType == 'U.S. States' && $curr->isDropdownTagger()) {
            $GLOBALS["SL"]->loadStates();
            if (!is_array($newVal)) {
                $newVal = $GLOBALS["SL"]->states->getStateByInd($newVal);
            } elseif (sizeof($newVal) > 0) {
                foreach ($newVal as $i => $val) $newVal[$i] = $GLOBALS["SL"]->states->getStateByInd($val);
            }
        }
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
    
    protected function postEmailFrom()
    {
        return [];
    }
    
    protected function postDumpFormEmailSubject()
    {
        return $GLOBALS["SL"]->sysOpts["site-name"] . ': ' . $GLOBALS["SL"]->treeRow->TreeName;
    }
    
    protected function postNodeLoadEmail($nID)
    {
        $this->v["emaTo"] = $this->getUserEmailList($this->allNodes[$nID]->extraOpts["emailTo"]);
        $this->v["emaCC"] = $this->getUserEmailList($this->allNodes[$nID]->extraOpts["emailCC"]);
        $this->v["emaBCC"] = $this->getUserEmailList($this->allNodes[$nID]->extraOpts["emailBCC"]);
        $this->v["toList"] = '';
        if (sizeof($this->v["emaTo"]) > 0) {
            foreach ($this->v["emaTo"] as $i => $e) {
                $this->v["toList"] .= (($i > 0) ? ' ; ' : '') . $e[0];
            }
        }
        if (sizeof($this->v["emaCC"]) > 0) {
            foreach ($this->v["emaCC"] as $i => $e) {
                $this->v["toList"] .= (($i > 0) ? ' ; ' : '') . $e[0];
            }
        }
        if (sizeof($this->v["emaBCC"]) > 0) {
            foreach ($this->v["emaBCC"] as $i => $e) {
                $this->v["toList"] .= (($i > 0) ? ' ; ' : '') . $e[0];
            }
        }
        return true;
    }
    
    protected function postNodeSendEmail($nID)
    {
        if (sizeof($this->allNodes[$nID]->extraOpts["emailTo"]) > 0 
            && (intVal($this->allNodes[$nID]->nodeRow->NodeDefault) > 0 
                || intVal($this->allNodes[$nID]->nodeRow->NodeDefault) == -69)) {
            $this->postNodeLoadEmail($nID);
            if (sizeof($this->v["emaTo"]) > 0) {
                $currEmail = [];
                $emaSubject = $this->postDumpFormEmailSubject();
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
                            if (is_array($val)) {
                                $val = implode(', ', $val);
                            }
                            if (!in_array($key, [ '_token', 'ajax', 'tree', 'treeSlug', 'node', 'nodeSlug', 
                                'loop', 'loopItem', 'step', 'alt', 'jumpTo', 'afterJumpTo', 'zoomPref' ])
                                && strpos($key, 'Visible') === false && trim($val) != '') {
                                $fldNID = intVal(str_replace('n', '', str_replace('fld', '', $key)));
                                $line = '';
                                if (isset($this->allNodes[$fldNID]->nodeRow->NodePromptText) 
                                    && trim($this->allNodes[$fldNID]->nodeRow->NodePromptText) != '') {
                                    $line .= '<b>' . strip_tags($this->allNodes[$fldNID]->nodeRow->NodePromptText) 
                                        . '</b><br />';
                                }
                                $line .= $val . '<br /><br />';
                                if (strpos($emaContent, $line) === false) {
                                    $emaContent .= $line;
                                }
                            }
                        }
                    }
                }
                if ($emaContent != '') {
                    $emaContent = $this->emailRecordSwap($emaContent);
                    $emaSubject = $this->emailRecordSwap($emaSubject);
                    $this->sendEmail($emaContent, $emaSubject, $this->v["emaTo"], $this->v["emaCC"], $this->v["emaBCC"],
                        $this->postEmailFrom());
                    $emaID = ((isset($currEmail->EmailID)) ? $currEmail->EmailID : -3);
                    $this->logEmailSent($emaContent, $emaSubject, $this->v["toList"], $emaID, $this->treeID, 
                        $this->coreID, $this->v["uID"]);
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
            '[{ Core ID }]', 
            '[{ Login URL }]', 
            '[{ User Email }]', 
            '[{ Email Confirmation URL }]'
            ];
        foreach ($dynamos as $dy) {
            if (strpos($emailBody, $dy) !== false) {
                $swap = $dy;
                $dyCore = str_replace('[{ ', '', str_replace(' }]', '', $dy));
                switch ($dy) {
                    case '[{ Core ID }]':
                        $swap = ((isset($this->sessData->dataSets["PowerScore"]) 
                            && isset($this->sessData->dataSets["PowerScore"][0]->PsID))
                            ? $this->sessData->dataSets["PowerScore"][0]->PsID : 0);
                        break;
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
    
    protected function manualLogContact($nID, $emaContent, $emaSubject, $email = '', $type = '')
    {
        $log = new SLContact;
        $log->ContFlag = 'Unread';
        $log->ContType = $type;
        $log->ContEmail = $email;
        $log->ContSubject = $emaSubject;
        $log->ContBody = $emaContent;
        $log->save();
        return true;
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
            }
        }
        $this->setNotif('Thank you for contacting us!', 'success');
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
        if ($newVal && !is_array($newVal) && trim($newVal) != '') {
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
    
}