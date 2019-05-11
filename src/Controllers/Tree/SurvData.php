<?php
/**
  * SurvData is a critical class with loads all the details of a survey's core record
  * according to the database design, and it's settings for a given survey.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers\Tree;

use DB;
use Auth;
use Storage\App\Models\SLFields;
use Storage\App\Models\SLNodeSaves;
use SurvLoop\Controllers\Tree\SurvDataTestsAB;

class SurvData
{
    protected $coreID       = -3;
    protected $coreTbl      = '';
    protected $privacy      = 'public';
    protected $id2ind       = [];
    protected $loaded       = false;
    
    // These are collections of all this session's records for each table
    public $dataSets        = [];
    
    // Lookup arrays mapping this record to others by table an ID
    public $kidMap          = [];
    
    // Tree node's current data structure nested position
    public $dataBranches    = [];
    
    // Tree node's which capture multiple-response checkboxes
    public $checkboxNodes   = [];
    public $helpInfo        = [];

    // These are the IDs the items within a table's dataSet which are in a loop collection
    public $loopItemIDs     = [];
    
    public $loopTblID       = -3;
    public $loopItemIDsDone = [];
    public $loopItemsNextID = -3;
                
    // These are the currently active AB Tests for this user's session. Each record has
    //  [ {Condition 
    public $testsAB         = [];
    
    public function loadCore($coreTbl, $coreID = -3, $checkboxNodes = [], $isBigSurvLoop = [], $dataBranches = [])
    {
        $this->setCoreID($coreTbl, $coreID);
        if (sizeof($dataBranches) > 0) {
            $this->dataBranches = $dataBranches;
        }
        $this->checkboxNodes = $checkboxNodes;
        $this->refreshDataSets($isBigSurvLoop);
        $this->loadSessTestsAB();
        $this->loaded = true;
        return true;
    }
    
    public function setCoreID($coreTbl, $coreID = -3)
    {
        $this->coreTbl = $coreTbl;
        $this->coreID = $coreID;
        return true;
    }
    
    public function refreshDataSets($isBigSurvLoop = [])
    {
        $this->dataSets = $this->id2ind = $this->kidMap = $this->helpInfo = [];
        $this->loadData($this->coreTbl, $this->coreID);
        // if (Auth::user() && Auth::user()->id) $this->loadData('users', Auth::user()->id);
        // check for data needed for root data loop which isn't connected to the core record
        if (sizeof($isBigSurvLoop) > 0 && trim($isBigSurvLoop[0]) != '') {
            eval("\$rows = " . $GLOBALS["SL"]->modelPath($isBigSurvLoop[0]) . "::orderBy('" 
                . $isBigSurvLoop[1] . "', '" . $isBigSurvLoop[2] . "')->get();");
            if ($rows->isNotEmpty()) {
                foreach ($rows as $row) $this->loadData($isBigSurvLoop[0], $row->getKey(), $row);
            }
        }
        return true;
    }
    
    protected function initDataSet($tbl)
    {
        $setInd = 0;
        if (!isset($this->dataSets[$tbl])) $this->dataSets[$tbl] = $this->id2ind[$tbl] = [];
        else $setInd = sizeof($this->dataSets[$tbl]);
        return $setInd;
    }
    
                                                                                            
    public function loadData($tbl, $rowID, $recObj = NULL)
    {
        $GLOBALS["SL"]->modelPath($tbl);
        $subObj = [];
        if (trim($tbl) != '' && $rowID > 0) {
            if (!$recObj) $recObj = $this->dataFind($tbl, $rowID);
            if ($tbl == $this->coreTbl && $rowID > 0 && $rowID == $this->coreID && !$recObj) {
                $this->newDataRecord($tbl, '', -3, true, $this->coreID);
            }
            if ($recObj) {
                // Adding record to main set of all records
                $setInd = $this->initDataSet($tbl);
                $this->dataSets[$tbl][$setInd] = $recObj;
                $this->id2ind[$tbl][$recObj->getKey()] = $setInd;
                
                // Recurse through this parent's families...
                if (isset($GLOBALS["SL"]->dataSubsets) && sizeof($GLOBALS["SL"]->dataSubsets) > 0) {
                    foreach ($GLOBALS["SL"]->dataSubsets as $subset) {
                        if ($subset->DataSubTbl == $tbl) {
                            $subObjs = [];
                            if (trim($subset->DataSubTblLnk) != '' && intVal($recObj->{ $subset->DataSubTblLnk }) > 0) {
                                $subObjs = $this->dataFind($subset->DataSubSubTbl, $recObj->{ $subset->DataSubTblLnk });
                                if ($subObjs) $subObjs = [$subObjs];
                            } elseif (trim($subset->DataSubSubLnk) != '') {
                                $subObjs = $this->dataWhere($subset->DataSubSubTbl, $subset->DataSubSubLnk, $rowID);
                            }
                            if (empty($subObjs) && $subset->DataSubAutoGen == 1) {
                                $subObjs = [$this->newDataRecordInner($subset->DataSubSubTbl)];
                                if (trim($subset->DataSubTblLnk) != '') {
                                    $recObj->update([ $subset->DataSubTblLnk => $subObjs[0]->getKey() ]);
                                    $recObj->save();
                                } elseif (trim($subset->DataSubSubLnk) != '') {
                                    $subObjs[0]->update([ $subset->DataSubSubLnk => $rowID ]);
                                    $subObjs[0]->save();
                                }
                            }
                            $this->processSubObjs($tbl, $rowID, $setInd, $subset->DataSubSubTbl, $subObjs);
                        }
                    }
                }
                
                // checking loops...
                if ($tbl == $this->coreTbl
                    && isset($GLOBALS["SL"]->dataLoops) && sizeof($GLOBALS["SL"]->dataLoops) > 0) {
                    foreach ($GLOBALS["SL"]->dataLoops as $loopName => $loop) {
                        if (isset($loop->DataLoopTable)) {
                            $keyField = $GLOBALS["SL"]->getForeignLnk($GLOBALS["SL"]->tblI[$loop->DataLoopTable], 
                                $GLOBALS["SL"]->tblI[$tbl]);
                            if (trim($keyField) != '') {
                                $subObjs = $this->dataWhere($loop->DataLoopTable, 
                                    $GLOBALS["SL"]->tblAbbr[$loop->DataLoopTable] . $keyField, $rowID);
                                $this->processSubObjs($tbl, $recObj->getKey(), $setInd, $loop->DataLoopTable, $subObjs);
                            }
                        }
                    }
                }
                
                // checking helpers...
                if (isset($GLOBALS["SL"]->dataHelpers) && sizeof($GLOBALS["SL"]->dataHelpers) > 0) {
                    foreach ($GLOBALS["SL"]->dataHelpers as $helper) {
                        if ($helper->DataHelpParentTable == $tbl) {
                            $subObjs = $this->dataWhere($helper->DataHelpTable, $helper->DataHelpKeyField, $rowID);
                            $this->processSubObjs($tbl, $recObj->getKey(), $setInd, $helper->DataHelpTable, $subObjs);
                        }
                    }
                }
                
                // checking linkages...
                if (isset($GLOBALS["SL"]->dataLinksOn) && sizeof($GLOBALS["SL"]->dataLinksOn) > 0) {
                    foreach ($GLOBALS["SL"]->dataLinksOn as $linkage) {
                        if ($tbl == $linkage[4]) {
                            $linkage = array($linkage[4], $linkage[3], $linkage[2], $linkage[1], $linkage[0]);
                        }
                        if ($tbl == $linkage[0]) {
                            $lnkObjs = $this->dataWhere($linkage[2], $linkage[1], $rowID);
                            if ($lnkObjs && sizeof($lnkObjs) > 0) {
                                $this->processSubObjs($tbl, $recObj->getKey(), $setInd, $linkage[2], $lnkObjs);
                                foreach ($lnkObjs as $lnkObj) {
                                    $findObj = $this->dataFind($linkage[4], $lnkObj->{ $linkage[3] });
                                    if ($findObj) {
                                        $subObjs = array($findObj);
                                        $this->processSubObjs($tbl, $recObj->getKey(), $setInd, $linkage[4], $subObjs);
                                    } elseif (intVal($lnkObj->{ $linkage[3] }) > 0) {
                                        // If this is a bad linkage, let's delete it
                                        $lnkObj->{ $linkage[3] } = NULL;
                                        $lnkObj->save();
                                    }
                                }
                            }
                        }
                    }
                }
                
            }
        }
        return true;
    }
    
    protected function getRecordLinks($tbl = '', $extraOutFld = '', $extraOutVal = -3, $skipIncoming = true)
    {
        $linkages = [ "outgoing" => [], "incoming" => [] ];
        if (trim($extraOutFld) != '') {
            $linkages["outgoing"][] = [$extraOutFld, $extraOutVal];
        }
        if (trim($tbl) == '' || !isset($GLOBALS["SL"]->tblI[$tbl])) {
            return $linkages;
        }
        // Outgoing Keys
        $flds = SLFields::select('FldName', 'FldForeignTable')
            ->where('FldTable', $GLOBALS["SL"]->tblI[$tbl])
            ->where('FldForeignTable', '>', 0)
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fldKey) {
                $foreignTbl = $GLOBALS["SL"]->tbl[$fldKey->FldForeignTable];
                if ($fldKey->FldForeignTable == $GLOBALS["SL"]->treeRow->TreeCoreTable) {
                    $linkages["outgoing"][] = [$GLOBALS["SL"]->tblAbbr[$tbl] . $fldKey->FldName, $this->coreID];
                } else { // not the special Core case, so find an ancestor
                    list($loopInd, $loopID) = $this->currSessDataPos($foreignTbl);
                    if ($loopID > 0) {
                        $newLink = [$GLOBALS["SL"]->tblAbbr[$tbl] . $fldKey->FldName, $loopID];
                        if (!in_array($newLink, $linkages["outgoing"])) {
                            $linkages["outgoing"][] = $newLink;
                        }
                    }
                }
            }
        }
        
        // Incoming Keys
        if (!$skipIncoming) {
            $flds = SLFields::select('FldName', 'FldTable')
                ->where('FldForeignTable', $GLOBALS["SL"]->tblI[$tbl])
                ->where('FldForeignTable', '>', 0)
                ->where('FldTable', '>', 0)
                ->get();
            if ($flds->isNotEmpty()) {
                foreach ($flds as $fldKey) {
                    if (isset($GLOBALS["SL"]->tbl[$fldKey->FldTable])) {
                        $foreignTbl = $GLOBALS["SL"]->tbl[$fldKey->FldTable];
                        $foreignFldName = $GLOBALS["SL"]->tblAbbr[$foreignTbl] . $fldKey->FldName;
                        if ($fldKey->FldTable == $GLOBALS["SL"]->treeRow->TreeCoreTable) {
                            $linkages["incoming"][] = [$foreignTbl, $foreignFldName, $this->coreID];
                        } else { // not the special Core case, so find an ancestor
                            list($loopInd, $loopID) = $this->currSessDataPos($foreignTbl);
                            if ($loopID > 0) {
                                $newLink = [$foreignTbl, $foreignFldName, $loopID];
                                if (!in_array($newLink, $linkages["incoming"])) {
                                    $linkages["incoming"][] = $newLink;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $linkages;
    }
    
    protected function findRecLinkOutgoing($tbl, $linkages)
    {
        $eval = "";
        foreach ($linkages["outgoing"] as $i => $link) {
            $eval .= "where('" . $link[0] . "', '" . $link[1] . "')->";
        }
        $eval = "\$recObj = " . $GLOBALS["SL"]->modelPath($tbl) . "::" . $eval . "first();";
        eval($eval);
        return $recObj;
    }
    
    public function newDataRecordInner($tbl = '', $linkages = [], $recID = -3)
    {
        if (trim($tbl) == '') {
            return [];
        }
        eval("\$recObj = new " . $GLOBALS["SL"]->modelPath($tbl) . ";");
        if ($recID > 0) {
            $recObj->{ $GLOBALS["SL"]->tblAbbr[$tbl] . 'ID' } = $recID;
        }
        if (isset($linkages["outgoing"]) && sizeof($linkages["outgoing"]) > 0) {
            foreach ($linkages["outgoing"] as $i => $link) {
                $recObj->{ $link[0] } = $link[1];
            }
        }
        $recObj->save();
        $setInd = $this->initDataSet($tbl);
        $this->dataSets[$tbl][$setInd] = $recObj;
        $this->id2ind[$tbl][$recObj->getKey()] = $setInd;
        if (isset($linkages["incoming"]) && sizeof($linkages["incoming"]) > 0) {
            foreach ($linkages["incoming"] as $link) {
                $incomingInd = $this->getRowInd($link[0], intVal($link[2]));
                if ($incomingInd >= 0) {
                    $this->dataSets[$link[0]][$incomingInd]->{ $link[1] } = $recObj->getKey();
                    $this->dataSets[$link[0]][$incomingInd]->save();
                }
            }
        }
        return $recObj;
    }
    
    public function newDataRecord($tbl = '', $fld = '', $newVal = -3, $forceAdd = false, $recID = -3)
    {
        $linkages = $this->getRecordLinks($tbl, $fld, $newVal);
        if ($forceAdd) {
            $recObj = $this->newDataRecordInner($tbl, $linkages, $recID);
            $this->refreshDataSets();
        } else {
            $recObj = $this->checkNewDataRecord($tbl, $fld, $newVal, $linkages);
            if (!$recObj) {
                $recObj = $this->newDataRecordInner($tbl, $linkages, $recID);
                $this->refreshDataSets();
            }
        }
        return $recObj;
    }
    
    public function checkNewDataRecord($tbl = '', $fld = '', $newVal = -3, $linkages = [])
    {
        $recObj = NULL;
        if (sizeof($linkages) == 0) {
            $linkages = $this->getRecordLinks($tbl, $fld, $newVal, false);
        }
        if (sizeof($linkages["outgoing"]) > 0) {
            $recObj = $this->findRecLinkOutgoing($tbl, $linkages);
        }
        if (!$recObj && sizeof($linkages["incoming"]) > 0) {
            foreach ($linkages["incoming"] as $link) {
                $incomingInd = $this->getRowInd($link[0], intVal($link[2]));
                if (isset($this->dataSets[$link[0]][$incomingInd]->{ $link[1] }) 
                    && intVal($this->dataSets[$link[0]][$incomingInd]->{ $link[1] }) > 0) {
                    $recInd = $this->getRowInd($tbl, intVal($this->dataSets[$link[0]][$incomingInd]->{ $link[1] }));
                    if ($recInd >= 0) {
                        $recObj = $this->dataSets[$tbl][$recInd];
                    }
                }
            }
        }
        return $recObj;
    }
    
    public function simpleNewDataRecord($tbl = '')
    {
        return $this->newDataRecordInner($tbl, $this->getRecordLinks($tbl));
    }
    
    public function getNextRecID($tbl = '')
    {
        eval("\$recObj = " . $GLOBALS["SL"]->modelPath($tbl) . "::select('" . $GLOBALS["SL"]->tblAbbr[$tbl] 
            . "ID')->orderBy('" . $GLOBALS["SL"]->tblAbbr[$tbl] . "ID', 'desc')->first();");
        if ($recObj && isset($recObj->{ $GLOBALS["SL"]->tblAbbr[$tbl] . 'ID' })) {
            return (1+$recObj->{ $GLOBALS["SL"]->tblAbbr[$tbl] . 'ID' });
        }
        return 1;
    }
    
    public function deleteDataRecord($tbl = '', $fld = '', $newVal = -3)
    {
        $linkages = $this->getRecordLinks($tbl, $fld, $newVal);
        if (sizeof($linkages["incoming"]) == 0) {
            $delObj = $this->findRecLinkOutgoing($tbl, $linkages);
            if ($delObj) $delObj->delete();
        } else {
            foreach ($linkages["incoming"] as $link) {
                $incomingInd = $this->getRowInd($link[0], intVal($link[2]));
                if (isset($this->dataSets[$link[0]][$incomingInd]->{ $link[1] })) {
                    $recInd = $this->getRowInd($tbl, intVal($this->dataSets[$link[0]][$incomingInd]->{ $link[1] }));
                    if ($recInd >= 0) {
                        $this->dataSets[$tbl][$recInd]->delete();
                        $this->dataSets[$link[0]][$incomingInd]->{ $link[1] } = NULL;
                        $this->dataSets[$link[0]][$incomingInd]->save();
                    }
                }
            }
        }
        $this->refreshDataSets();
        return true;
    }
    
    public function deleteDataRecordByID($tbl = '', $id = -3, $refresh = true)
    {
        if ($tbl == '' || $id <= 0) return false;
        $recInd = $this->getRowInd($tbl, $id);
        if ($recInd >= 0) $this->dataSets[$tbl][$recInd]->delete();
        if ($refresh) $this->refreshDataSets();
        return true;
    }
    
    public function addRemoveSubsets($tbl, $newTot = -3)
    {
        if (trim($tbl) == '' || $newTot < 0) return false;
        $currTot = ((isset($this->dataSets[$tbl])) ? sizeof($this->dataSets[$tbl]) : 0);
        if ($newTot > $currTot) {
            for ($i = $currTot; $i < $newTot; $i++) $this->newDataRecord($tbl, '', -3, true);
        } elseif ($newTot < $currTot) {
            for ($i = $newTot; $i < $currTot; $i++) $this->dataSets[$tbl][$i]->delete();
            $this->refreshDataSets();
        }
        return true;
    }
    
    protected function dataFind($tbl, $rowID)
    {
        if ($rowID <= 0) return [];
        eval("\$recObj = " . $GLOBALS["SL"]->modelPath($tbl) . "::find(" . $rowID . ");");
        return $recObj;
    }
    
    public function dataWhere($tbl, $where, $whereVal, $operator = "=", $getFirst = "get")
    {
        eval("\$recObj = " . $GLOBALS["SL"]->modelPath($tbl)
            . "::where('" . $where . "', '" . $operator . "', '" . $whereVal . "')"
            . "->orderBy('" . $GLOBALS["SL"]->tblAbbr[$tbl] . "ID', 'asc')"
            . "->" . $getFirst . "();");
        return $recObj;
    }
    
    public function dataHas($tbl, $rowID = -3)
    {
        if ($rowID <= 0) return (isset($this->dataSets[$tbl]) && sizeof($this->dataSets[$tbl]) > 0);
        $rowInd = $this->getRowInd($tbl, $rowID);
        if ($rowInd >= 0 && isset($this->dataSets[$tbl]) && $this->dataSets[$tbl][$rowInd]->getKey() == $rowID) {
            return true;
        }
        return false;
    }
    
    public function getRowInd($tbl, $rowID)
    {
        if ($rowID > 0 && isset($this->id2ind[$tbl]) && isset($this->id2ind[$tbl][$rowID])) {
            if (intVal($this->id2ind[$tbl][$rowID]) >= 0) return $this->id2ind[$tbl][$rowID];
        }
        // else double-check
        if ($rowID > 0 && isset($this->dataSets[$tbl]) && sizeof($this->dataSets[$tbl]) > 0) {
            foreach ($this->dataSets[$tbl] as $ind => $d) {
                if ($d->getKey() == $rowID) {
                    $this->initDataSet($tbl);
                    $this->id2ind[$tbl][$rowID] = $ind;
                    return $ind;
                }
            }
        }
        return -3;
    }
    
    public function getRowById($tbl, $rowID)
    {
        if ($rowID <= 0) {
            return [];
        }
        $rowInd = $this->getRowInd($tbl, $rowID);
        if ($rowInd >= 0) {
            return $this->dataSets[$tbl][$rowInd];
        }
        return [];
    }
    
    public function getRowIDsByFldVal($tbl, $fldVals = [], $getRow = false)
    {
        $ret = [];
        if (sizeof($fldVals) > 0 && isset($this->dataSets[$tbl]) && sizeof($this->dataSets[$tbl]) > 0) {
            foreach ($this->dataSets[$tbl] as $ind => $d) {
                $found = true;
                foreach ($fldVals as $fld => $val) {
                    if (!isset($d->{ $fld }) || $d->{ $fld } != $val) {
                        $found = false;
                    }
                }
                if ($found) {
                    if ($getRow) {
                        $ret[] = $d;
                    } else {
                        $ret[] = $d->getKey();
                    }
                }
            }
        }
        return $ret;
    }
    
    public function dataFieldExists($tbl, $ind, $fld)
    {
        return (isset($this->dataSets[$tbl]) && isset($this->dataSets[$tbl][$ind])
            && isset($this->dataSets[$tbl][$ind]->{ $fld }));
    }
    
    public function getLoopRows($loopName)
    {
        $rows = [];
        if (isset($this->loopItemIDs[$loopName]) && sizeof($this->loopItemIDs[$loopName]) > 0) {
            foreach ($this->loopItemIDs[$loopName] as $itemID) {
                $rows[] = $this->getRowById($GLOBALS["SL"]->dataLoops[$loopName]->DataLoopTable, $itemID);
            }
        }
        return $rows;
    }
    
    public function getLoopRowIDs($loopName)
    {
        if (isset($this->loopItemIDs[$loopName])) {
            return $this->loopItemIDs[$loopName];
        }
        return [];
    }
    
    public function getLoopIndFromID($loopName, $itemID)
    {
        if (isset($this->loopItemIDs[$loopName]) && sizeof($this->loopItemIDs[$loopName]) > 0) {
            foreach ($this->loopItemIDs[$loopName] as $ind => $id) {
                if ($id == $itemID) {
                    return $ind;
                }
            }
        }
        return -1;
    }
    
    public function leaveCurrLoop()
    {
        $this->loopTblID = $this->loopItemsNextID = -3;
        $this->loopItemIDsDone = [];
        return true;
    }
    
    protected function processSubObjs($tbl1, $tbl1ID, $tbl1Ind, $tbl2, $subObjs)
    {
        if ($subObjs && sizeof($subObjs) > 0) {
            foreach ($subObjs as $subObj) {
                if ($subObj && !$this->dataHas($tbl2, $subObj->getKey())) {
                    $this->addToMap($tbl1, $tbl1ID, $tbl1Ind, $tbl2, $subObj->getKey());
                    $this->loadData($tbl2, $subObj->getKey(), $subObj);
                }
            }
        }
        return true;
    }
    
    public function addToMap($tbl1, $tbl1ID, $tbl1Ind, $tbl2, $tbl2ID, $tbl2Ind = -3, $lnkTbl = '')
    {
        if ($tbl1Ind < 0) $tbl1Ind = $this->getRowInd($tbl1, $tbl1ID);
        if (trim($tbl1) != '' && trim($tbl2) != '' && $tbl1ID > 0 && $tbl1Ind >= 0 && $tbl2ID > 0) {
            if (!isset($this->kidMap[$tbl1])) {
                $this->kidMap[$tbl1] = [];
            }
            if (!isset($this->kidMap[$tbl1][$tbl2])) {
                $this->kidMap[$tbl1][$tbl2] = [];
            }
            if ($tbl2Ind < 0) {
                $tbl2Ind = $this->getRowInd($tbl2, $tbl2ID);
                if ($tbl2Ind < 0) { // not presuming it's about to be loaded
                    $tbl2Ind = (isset($this->dataSets[$tbl2])) ? sizeof($this->dataSets[$tbl2]) : 0;
                }
            }
            if ($tbl1ID > 0 && $tbl2ID > 0) {
                $this->kidMap[$tbl1][$tbl2][] = [
                    "id1" => $tbl1ID,
                    "id2" => $tbl2ID,
                    "ind1" => $tbl1Ind,
                    "ind2" => $tbl2Ind
                    ];
            }
        }
        return false;
    }
    
    public function getChild($tbl1, $tbl1ID, $tbl2, $type = 'id')
    {
        if (trim($tbl1) != '' && trim($tbl2) != '' && $tbl1ID >= 0) {
            if (isset($this->kidMap[$tbl1]) && isset($this->kidMap[$tbl1][$tbl2]) 
                && sizeof($this->kidMap[$tbl1][$tbl2]) > 0) {
                foreach ($this->kidMap[$tbl1][$tbl2] as $map) {
                    if ($tbl1ID == $map[$type . "1"]) {
                        return $map[$type . "2"];
                    }
                }
            }
            if (isset($this->kidMap[$tbl2]) && isset($this->kidMap[$tbl2][$tbl1]) 
                && sizeof($this->kidMap[$tbl2][$tbl1]) > 0) {
                foreach ($this->kidMap[$tbl2][$tbl1] as $map) {
                    if ($tbl1ID == $map[$type . "2"]) {
                        return $map[$type . "1"];
                    }
                }
            }
        }
        return -3;
    }
    
    public function getChildRow($tbl1, $tbl1ID, $tbl2)
    {
        $childID = $this->getChild($tbl1, $tbl1ID, $tbl2);
        if ($childID > 0) {
            return $this->getRowById($tbl2, $childID);
        }
        return [];
    }
    
    public function getChildRows($tbl1, $tbl1ID, $tbl2)
    {
        $retArr = [];
        if (trim($tbl1) != '' && trim($tbl2) != '' && $tbl1ID >= 0) {
            if (isset($this->kidMap[$tbl1]) && isset($this->kidMap[$tbl1][$tbl2]) 
                && sizeof($this->kidMap[$tbl1][$tbl2]) > 0) {
                foreach ($this->kidMap[$tbl1][$tbl2] as $map) {
                    if ($tbl1ID == $map["id1"]) {
                        $retArr[] = $this->getRowById($tbl2, $map["id2"]);
                    }
                }
            } elseif (isset($this->kidMap[$tbl2]) && isset($this->kidMap[$tbl2][$tbl1]) 
                && sizeof($this->kidMap[$tbl2][$tbl1]) > 0) {
                foreach ($this->kidMap[$tbl2][$tbl1] as $map) {
                    if ($tbl1ID == $map["id2"]) {
                        $retArr[] = $this->getRowById($tbl1, $map["id1"]);
                    }
                }
            }
        }
        return $retArr;
    }
    
    public function getBranchChildRows($tbl2, $idOnly = false)
    {
        $ret = [];
        $bInd = sizeof($this->dataBranches)-1;
        if ($bInd >= 0 && trim($this->dataBranches[$bInd]["branch"]) != '' && isset($this->dataSets[$tbl2])
            && sizeof($this->dataSets[$tbl2]) > 0) {
            $tbl2fld = $GLOBALS["SL"]->getForeignLnkNameFldName($tbl2, $this->dataBranches[$bInd]["branch"]);
            if (trim($tbl2fld) != '') {
                foreach ($this->dataSets[$tbl2] as $i => $row) {
                    if (isset($row->{ $tbl2fld }) && $row->{ $tbl2fld } == $this->dataBranches[$bInd]["itemID"]) {
                        if ($idOnly) {
                            $ret[] = $row->getKey();
                        } else {
                            $ret[] = $row;
                        }
                    }
                }
            }
        }
        return $ret;
    }
    
    public function sessChildIDFromParent($tbl2)
    {
        for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
            if ($this->dataBranches[$i]["branch"] != $tbl2) {
                $tbl2ID = $this->getChild($this->dataBranches[$i]["branch"], $this->dataBranches[$i]["itemID"], $tbl2);
                if ($tbl2ID > 0) {
                    return $tbl2ID;
                }
            }
        }
        return -3;
    }
    
    public function sessChildRowFromParent($tbl2)
    {
        return $this->getRowById($tbl2, $this->sessChildIDFromParent($tbl2));
    }

    
    protected function getAllTableIDs($tbl)
    {
        $tmpIDs = [];
        if (isset($this->dataSets[$tbl]) && sizeof($this->dataSets[$tbl]) > 0) {
            foreach ($this->dataSets[$tbl] as $recObj) {
                $tmpIDs[] = $recObj->getKey();
            }
        }
        return $tmpIDs;
    }
    
    protected function getAllTableIdFlds($tbl, $flds = [])
    {
        $ret = [];
        if (isset($this->dataSets[$tbl]) && sizeof($this->dataSets[$tbl]) > 0) {
            foreach ($this->dataSets[$tbl] as $i => $recObj) {
                $ret[$i] = [ "id" => $recObj->getKey() ];
                if (sizeof($flds) > 0) {
                    foreach ($flds as $i => $fld) {
                        if (isset($recObj->{ $fld })) {
                            $ret[$i][$fld] = $recObj->{ $fld };
                        } else {
                            $ret[$i][$fld] = null;
                        }
                    }
                }
            }
        }
        return $ret;
    }
    
    // For debugging purposes
    public function printAllTableIdFlds($tbl, $flds = [])
    {
        $ret = '';
        $arr = $this->getAllTableIdFlds($tbl, $flds);
        if (sizeof($arr) > 0) {
            foreach ($arr as $i => $row) {
                $ret .= ' (( ';
                if (sizeof($row) > 0) {
                    foreach ($row as $fld => $val) {
                        $ret .= (($fld != 'id') ? ' , ' : '') . $fld . ' : ' . $val;
                    }
                }
                $ret .= ' )) ';
            }
        }
        return $ret;
    }
    
    public function getLoopDoneItems($loopName, $fld = '')
    {
        $tbl = $GLOBALS["SL"]->dataLoops[$loopName]->DataLoopTable;
        if (trim($fld) == '') {
            list($tbl, $fld) = $GLOBALS["SL"]->splitTblFld($GLOBALS["SL"]->dataLoops[$loopName]->DataLoopDoneFld);
        }
        $this->loopItemIDsDone = $saves = [];
        $saves = DB::table('SL_NodeSaves')
            ->join('SL_Sess', 'SL_NodeSaves.NodeSaveSession', '=', 'SL_Sess.SessID')
            ->where('SL_Sess.SessTree', '=', $GLOBALS["SL"]->treeID)
            ->where('SL_Sess.SessCoreID', '=', $this->coreID)
            ->where('SL_NodeSaves.NodeSaveTblFld', 'LIKE', $tbl . ':' . $fld)
            ->get();
        if ($saves->isNotEmpty()) {
            foreach ($saves as $save) {
                if (in_array($save->NodeSaveLoopItemID, $this->loopItemIDs[$loopName]) 
                    && !in_array($save->NodeSaveLoopItemID, $this->loopItemIDsDone)) {
                    $this->loopItemIDsDone[] = $save->NodeSaveLoopItemID;
                }
            }
        }
        $this->loopItemsNextID = -3;
        if (sizeof($this->loopItemIDs[$loopName]) > 0) {
            foreach ($this->loopItemIDs[$loopName] as $id) {
                if ($this->loopItemsNextID <= 0 && !in_array($id, $this->loopItemIDsDone)) {
                    $this->loopItemsNextID = $id;
                }
            }
        }
        return $this->loopItemIDsDone;
    }
    
    public function createNewDataLoopItem($nID = -3)
    {
        if (intVal($GLOBALS["SL"]->closestLoop["obj"]->DataLoopAutoGen) == 1) {
            // auto-generate new record in the standard way
            $newFld = $newVal = '';
            if (isset($GLOBALS["SL"]->closestLoop["obj"]->DataLoopTree)) {
                $GLOBALS["SL"]->closestLoop["obj"]->loadLoopConds();
            }
            if (sizeof($GLOBALS["SL"]->closestLoop["obj"]->conds) > 0) {
                if ($GLOBALS["SL"]->closestLoop["obj"]->conds 
                    && sizeof($GLOBALS["SL"]->closestLoop["obj"]->conds) > 0) {
                    foreach ($GLOBALS["SL"]->closestLoop["obj"]->conds as $i => $cond) {
                        $fld = $GLOBALS["SL"]->getFullFldNameFromID($cond->CondField, false);
                        if (trim($newFld) == '' && trim($fld) != '' && $cond->CondOperator == '{' 
                            && sizeof($cond->condVals) == 1 && $GLOBALS["SL"]->tbl[$cond->CondTable] 
                                == $GLOBALS["SL"]->closestLoop["obj"]->DataLoopTable) {
                            $newFld = $fld;
                            $newVal = $cond->condVals[0];
                        }
                    }
                }
            }
            $recObj = $this->newDataRecord($GLOBALS["SL"]->closestLoop["obj"]->DataLoopTable, $newFld, $newVal, true);
            $GLOBALS["SL"]->sessLoops[0]->SessLoopItemID = $GLOBALS["SL"]->closestLoop["itemID"] = $recObj->getKey();
            $GLOBALS["SL"]->sessLoops[0]->save();
            $this->logDataSave($nID, $GLOBALS["SL"]->closestLoop["obj"]->DataLoopTable, 
                $GLOBALS["SL"]->closestLoop["itemID"], 'AddingItem #' 
                . $GLOBALS["SL"]->closestLoop["itemID"], $GLOBALS["SL"]->closestLoop["loop"]);
            return $recObj->getKey();
        }
        return -3;
    }
    
    public function getLatestDataBranch()
    {
        if (sizeof($this->dataBranches) > 0) {
            return $this->dataBranches[sizeof($this->dataBranches)-1];
        }
        return [];
    }
    
    public function getLatestDataBranchID()
    {
        $branch = $this->getLatestDataBranch();
        if (sizeof($branch) > 0 && isset($branch["itemID"])) {
            return $branch["itemID"];
        }
        return -3;
    }
    
    public function getLatestDataBranchRow()
    {
        $branch = $this->getLatestDataBranch();
        if (sizeof($branch) > 0 && isset($branch["branch"]) && isset($branch["itemID"])) {
            return $this->getRowById($branch["branch"], $branch["itemID"]);
        }
        return null;
    }
    
    public function getDataBranchRow($tbl = '')
    {
        for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
            if ($this->dataBranches[$i]["branch"] == $tbl) {
                return $this->getRowById($tbl, $this->dataBranches[$i]["itemID"]);
            }
        }
        return null;
    }
    
    public function startTmpDataBranch($tbl, $itemID = -3, $findItemID = true)
    {
        $foundBranch = false;
        for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
            if ($this->dataBranches[$i]["branch"] == $tbl) {
                $foundBranch = true;
                if (intVal($this->dataBranches[$i]["itemID"]) <= 0 && intVal($itemID) > 0) {
                    $this->dataBranches[$i]["itemID"] = $itemID;
                }
            }
        }
        if (!$foundBranch) {
            if (intVal($itemID) <= 0 && $findItemID) {
                $itemID = $this->sessChildIDFromParent($tbl);
            }
            $this->dataBranches[] = [
                "branch" => $tbl,
                "loop"   => '',
                "itemID" => $itemID
                ];
        }
        return true;
    }

    public function endTmpDataBranch($tbl)
    {
        $oldTmp = $this->dataBranches;
        $this->dataBranches = [];
        if (sizeof($oldTmp) > 0) {
            foreach ($oldTmp as $b) {
                if ($tbl != $b["branch"]) {
                    $this->dataBranches[] = $b;
                }
            }
        }
        return true;
    }

    public function currSessDataPos($tbl, $hasParManip = false)
    {
        if (trim($tbl) == '') {
            return [-3, -3];
        }
        if ($tbl == $this->coreTbl) {
            return [0, $this->coreID];
        }
        $itemID = $itemInd = -3;
        $tblNew = $this->isCheckboxHelperTable($tbl);
        for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
            if (intVal($itemID) <= 0 && isset($this->dataBranches[$i])) {
                list($itemInd, $itemID) = $this->currSessDataPosBranch($tblNew, $this->dataBranches[$i]);
                if (intVal($itemID) > 0) {
                    return [$itemInd, $itemID];
                }
            }
        }
        for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
            if (intVal($itemID) <= 0 && isset($this->dataBranches[$i])) {
                list($itemInd, $itemID) = $this->currSessDataPosBranch($tbl, $this->dataBranches[$i]);
            }
        }
        if (intVal($itemID) <= 0 && !$hasParManip && trim($GLOBALS["SL"]->currCyc["res"][1]) == '') {
            $itemID = $this->sessChildIDFromParent($tbl);
            if ($itemID > 0) {
                $itemInd = $this->getRowInd($tbl, $itemID);
                for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
                    if ($this->dataBranches[$i]["branch"] == $tbl && $this->dataBranches[$i]["loop"] == '') {
                        $this->dataBranches[$i]["itemID"] = $itemID;
                    }
                }
            }
        }
        return [ $itemInd, $itemID ];
    }
    
    public function currSessDataPosBranch($tbl, $branch)
    {
        $itemID = 0;
        if ($tbl == $branch["branch"]) {
            if (trim($branch["loop"]) != '') {
                $itemID = $GLOBALS["SL"]->getSessLoopID($branch["loop"]);
            } elseif (intVal($branch["itemID"]) > 0) {
                $itemID = $branch["itemID"];
            } /* elseif (isset($this->dataSets[$tbl]) && isset($this->dataSets[$tbl][0])) {
                $itemID = $this->dataSets[$tbl][0]->getKey();
            } */
        }
        $itemInd = $this->getRowInd($tbl, $itemID);
        return [$itemInd, $itemID];
    }
    
    public function currSessDataPosBranchOnly($tbl)
    {
        $itemID = $itemInd = 0;
        $tbl = $this->isCheckboxHelperTable($tbl);
        for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
            if ($itemID <= 0) {
                list($itemInd, $itemID) = $this->currSessDataPosBranch($tbl, $this->dataBranches[$i]);
            }
        }
        return [$itemInd, $itemID];
    }
    
    // Here we're trying to find the closest relative within current tree navigation to the table and field in question. 
    public function currSessData($nID, $tbl, $fld = '', $action = 'get', $newVal = null, $hasParManip = false, 
        $itemInd = -3, $itemID = -3)
    {
        if (trim($tbl) == '' || trim($fld) == '' || !$this->loaded) {
            return '';
        }
        if (in_array($nID, $this->checkboxNodes) && $GLOBALS["SL"]->isFldCheckboxHelper($fld)) {
            $tblFld = $tbl . '-' . $fld;
            $this->helpInfo[$tblFld] = $this->getCheckboxHelperInfo($tbl, $fld);
            if ($this->helpInfo[$tblFld]["link"] && isset($this->helpInfo[$tblFld]["link"]->DataHelpValueField)) {
                return $this->currSessDataCheckbox($nID, $tbl, $fld);
            }
        }
        if ($itemInd < 0 || $itemID <= 0) {
            list($itemInd, $itemID) = $this->currSessDataPos($tbl, $hasParManip);
        }
        if ($itemInd < 0 || $itemID <= 0) {
            return '';
        }
        if ($action == 'get') {
            if ($this->dataFieldExists($tbl, $itemInd, $fld)) {
                return $this->dataSets[$tbl][$itemInd]->{ $fld };
            }
        } elseif ($action == 'update' && $fld != ($GLOBALS["SL"]->tblAbbr[$tbl] . 'ID')) {
            $this->logDataSave($nID, $tbl, $itemID, $fld, $newVal);
            if (trim($newVal) == '' && in_array($GLOBALS["SL"]->fldTypes[$tbl][$fld], ['INT', 'DOUBLE'])) {
                $newVal = null;
            } elseif ($GLOBALS["SL"]->fldTypes[$tbl][$fld] == 'INT') {
                $newVal = intVal($newVal);
            } elseif ($GLOBALS["SL"]->fldTypes[$tbl][$fld] == 'DOUBLE') {
                $newVal = floatval($newVal);
            } else {
                $newVal = strip_tags($newVal);
            }
            if (isset($this->dataSets[$tbl]) && isset($this->dataSets[$tbl][$itemInd])) {
                $this->dataSets[$tbl][$itemInd]->{ $fld } = $newVal;
                $this->dataSets[$tbl][$itemInd]->save();
                return $newVal;
            } else {
              //$GLOBALS["errors"] .= 'Couldn\'t find dataSets[' . $tbl . '][' . $itemInd . '] for ' . $fld . '<br />';
            }
        }
        return $newVal;
    }
    
    public function currSessDataCheckbox($nID, $tbl, $fld = '', $action = 'get', $newVals = [], $curr = [], 
        $itemInd = -3, $itemID = -3)
    {
        $tblFld = $tbl . '-' . $fld;
        $this->helpInfo[$tblFld] = $this->getCheckboxHelperInfo($tbl, $fld);
        if (!$this->helpInfo[$tblFld]["link"] || !isset($this->helpInfo[$tblFld]["link"]->DataHelpValueField)) {
            return $this->currSessData($nID, $tbl, $fld, $action, ';' . implode(';;', $newVals) . ';', false, 
                $itemInd, $itemID);
        }
        if ($action == 'get') {
            if (sizeof($this->helpInfo[$tblFld]["pastVals"]) > 0) {
                return ';' . implode(';;', $this->helpInfo[$tblFld]["pastVals"]) . ';';
            }
            return '';
        } elseif ($action == 'update') {
            $this->logDataSave($nID, $tbl, $this->helpInfo[$tblFld]["parentID"], $fld, $newVals);
            // check for newly submitted responses...
            if (is_array($newVals) && sizeof($newVals) > 0) {
                foreach ($newVals as $i => $val) {
                    if (!in_array($val, $this->helpInfo[$tblFld]["pastVals"]) 
                        && isset($this->helpInfo[$tblFld]["link"]->DataHelpTable)) {
                        if ($this->helpInfo[$tblFld]["parentID"] <= 0 
                            && $this->helpInfo[$tblFld]["link"]->DataHelpParentTable == 'users') {
                            $this->helpInfo[$tblFld]["parentID"] = ((Auth::user() && Auth::user()->id) 
                                ? Auth::user()->id : -3);
                        }
                        if ($val && trim($val) != '') {
                            eval("\$newObj = new " 
                                . $GLOBALS["SL"]->modelPath($this->helpInfo[$tblFld]["link"]->DataHelpTable) . ";");
                            $newObj->save();
                            $newObj->update([
                                $this->helpInfo[$tblFld]["link"]->DataHelpKeyField 
                                    => $this->helpInfo[$tblFld]["parentID"],
                                $this->helpInfo[$tblFld]["link"]->DataHelpValueField => strip_tags($val)
                                ]);
                            $setInd = $this->initDataSet($tbl);
                            $this->dataSets[$tbl][$setInd] = $newObj;
                            $this->id2ind[$tbl][$newObj->getKey()] = $setInd;
                        }
                    }
                }
            }
            if (isset($curr->responses) && sizeof($curr->responses) > 0) {
                foreach ($curr->responses as $j => $res) {
                    if ((!is_array($newVals) || !in_array($res->NodeResValue, $newVals)) 
                        && isset($this->helpInfo[$tblFld]["pastValToID"][$res->NodeResValue])) {
                        $this->deleteDataItem($nID, $this->helpInfo[$tblFld]["link"]->DataHelpTable, 
                            $this->helpInfo[$tblFld]["pastValToID"][$res->NodeResValue]);
                    }
                }
            }
        }
        return '';
    }
    
    public function getCheckboxHelperInfo($tbl, $fld)
    {
        $tblFld = $tbl . '-' . $fld;
        //if (!isset($this->helpInfo[$tblFld]) || $this->helpInfo[$tblFld]["parentID"] < 0) {
            $this->helpInfo[$tblFld] = [
                "link"        => [],
                "parentID"    => -3,
                "pastVals"    => [],
                "pastObjs"    => [],
                "pastValToID" => []
                ];
            if (isset($GLOBALS["SL"]->dataHelpers) && sizeof($GLOBALS["SL"]->dataHelpers) > 0) {
                foreach ($GLOBALS["SL"]->dataHelpers as $helper) {
                    if ($helper->DataHelpTable == $tbl && $helper->DataHelpValueField == $fld) {
                        $this->helpInfo[$tblFld]["link"] = $helper;
                        //BranchOnly
                        list($parentInd, $this->helpInfo[$tblFld]["parentID"]) 
                            = $this->currSessDataPos($helper->DataHelpParentTable);
                        $this->helpInfo[$tblFld]["pastObjs"] = $this->dataWhere($helper->DataHelpTable, 
                            $helper->DataHelpKeyField, $this->helpInfo[$tblFld]["parentID"]);
                        if ($this->helpInfo[$tblFld]["pastObjs"] && sizeof($this->helpInfo[$tblFld]["pastObjs"]) > 0) {
                            foreach ($this->helpInfo[$tblFld]["pastObjs"] as $obj) {
                                $this->helpInfo[$tblFld]["pastVals"][] = $obj->{ $helper->DataHelpValueField };
                                $this->helpInfo[$tblFld]["pastValToID"][$obj->{ $helper->DataHelpValueField }] 
                                    = $obj->getKey();
                            }
                        }
                    }
                }
            }
        //}
        return $this->helpInfo[$tblFld];
    }
    
    public function deleteDataItem($nID, $tbl = '', $itemID = -3)
    {
        $itemInd = $this->getRowInd($tbl, $itemID);
        if ($itemID <= 0 || $itemInd < 0) {
            return false;
        }
        eval($GLOBALS["SL"]->modelPath($tbl) . "::find(" . $itemID . ")->delete();");
        unset($this->dataSets[$tbl][$itemInd]);
        unset($this->id2ind[$tbl][$itemID]);
        return true;
    }
    
    public function deleteEntireCore()
    {
        if (sizeof($this->dataSets) > 0) {
            foreach ($this->dataSets as $tbl => $rows) {
                if (sizeof($rows) > 0) {
                    foreach ($rows as $row) {
                        eval($GLOBALS["SL"]->modelPath($tbl) . "::find(" . intVal($row->getKey()) . ")->delete();");
                    }
                }
            }
            $this->refreshDataSets();
        }
        return true;
    }
    
    public function logDataSave($nID = -3, $tbl = '', $itemID = -3, $fld = '', $newVal = '')
    {
        $nodeSave = new SLNodeSaves;
        $nodeSave->NodeSaveSession    = 0;
        if (session()->has('sessID' . $GLOBALS["SL"]->sessTree) 
            && intVal(session()->get('sessID' . $GLOBALS["SL"]->sessTree)) > 0) {
            $nodeSave->NodeSaveSession = session()->get('sessID' . $GLOBALS["SL"]->sessTree);
        }
        $nodeSave->NodeSaveNode       = $nID;
        $nodeSave->NodeSaveTblFld     = $tbl . ':' . $fld;
        $nodeSave->NodeSaveLoopItemID = $itemID;
        if (!is_array($newVal)) {
            $nodeSave->NodeSaveNewVal = $newVal;
        } else {
            ob_start();
            print_r($newVal);
            $nodeSave->NodeSaveNewVal = ob_get_contents();
            ob_end_clean();
        }
        $nodeSave->save();
        return true;
    }
    
    protected function loadSessionDataLog($nID = -3, $tbl = '', $fld = '', $set = '')
    {
        $sessID = 0;
        if (session()->has('sessID' . $GLOBALS["SL"]->sessTree) 
            && intVal(session()->get('sessID' . $GLOBALS["SL"]->sessTree)) > 0) {
            $sessID = session()->get('sessID' . $GLOBALS["SL"]->sessTree);
        }
        $qryWheres = "where('NodeSaveSession', \$sessID)->where('NodeSaveNode', ".$nID.")->";
        if (trim($tbl) != '' && trim($fld) != '') {
            $qryWheres .= "where('NodeSaveTblFld', '" . $tbl . ":" . $fld 
                . ((trim($set) != '') ? "[" . $set . "]" : "") . "')->";
        }
        if (isset($GLOBALS["SL"]->closestLoop["itemID"]) && intVal($GLOBALS["SL"]->closestLoop["itemID"]) > 0) {
            $qryWheres .= "where('NodeSaveLoopItemID', " . $GLOBALS["SL"]->closestLoop["itemID"] . ")->";
        }
        eval("\$nodeSave = Storage\\App\\Models\\SLNodeSaves::" . $qryWheres . "orderBy('created_at', 'desc')->first();"); 
        if ($nodeSave && isset($nodeSave->NodeSaveNewVal)) {
            return $nodeSave->NodeSaveNewVal;
        }
        return '';
    }
    
    public function parseCondition($cond = [], $recObj = [], $nID = -3)
    {
        $passed = true;
        if ($cond && isset($cond->CondDatabase) && $cond->CondOperator != 'CUSTOM') {
            $cond->loadVals();
            $loopName = ((intVal($cond->CondLoop) > 0 && isset($GLOBALS["SL"]->dataLoopNames[$cond->CondLoop])) 
                ? $GLOBALS["SL"]->dataLoopNames[$cond->CondLoop] : '');
            if (intVal($cond->CondTable) <= 0 && trim($loopName) != '' 
                && isset($GLOBALS["SL"]->dataLoops[$loopName])) {
                $tblName = $GLOBALS["SL"]->dataLoops[$loopName]->DataLoopTable;
            } else {
                $tblName = $GLOBALS["SL"]->tbl[$cond->CondTable];
            }
//if ($tbl != $setTbl) list($setTbl, $setSet, $loopItemID) = $this->getDataSetTblTranslate($set, $tbl, $loopItemID);
            if ($cond->CondOperator == 'EXISTS=') {
                if (!isset($this->dataSets[$tblName]) || (intVal($cond->CondLoop) > 0 
                    && !isset($this->loopItemIDs[$loopName]))) {
                    if (intVal($cond->CondOperDeet) == 0) $passed = true;
                    else $passed = false;
                } else {
                    $existCnt = sizeof($this->dataSets[$tblName]);
                    if (intVal($cond->CondLoop) > 0) $existCnt = sizeof($this->loopItemIDs[$loopName]);
                    $passed = ($existCnt == intVal($cond->CondOperDeet));
                }
            } elseif ($cond->CondOperator == 'EXISTS>') {
                if (!isset($this->dataSets[$tblName]) || (intVal($cond->CondLoop) > 0 
                    && !isset($this->loopItemIDs[$loopName]))) {
                    $passed = false;
                } else {
                    $existCnt = sizeof($this->dataSets[$tblName]);
                    if (intVal($cond->CondLoop) > 0) {
                        $existCnt = sizeof($this->loopItemIDs[$loopName]);
                    }
                    if (intVal($cond->CondOperDeet) == 0) {
                        $passed = ($existCnt > 0);
                    } elseif ($cond->CondOperDeet > 0) {
                        $passed = ($existCnt > intVal($cond->CondOperDeet));
                    } elseif ($cond->CondOperDeet < 0) {
                        $passed = ($existCnt < ((-1)*intVal($cond->CondOperDeet)));
                    }
                }
            } elseif (intVal($cond->CondField) > 0) {
                $fldName = $GLOBALS["SL"]->getFullFldNameFromID($cond->CondField, false);
                if ($cond->CondOperator == '{{') { // find any match in any row for this table
                    $passed = false;
                    if (isset($this->dataSets[$tblName]) && sizeof($this->dataSets[$tblName]) > 0) {
                        foreach ($this->dataSets[$tblName] as $ind => $row) {
                            if (isset($row->{ $fldName }) && trim($row->{ $fldName }) != '' 
                                && in_array($row->{ $fldName }, $cond->condVals)) {
                                $passed = true;
                            }
                        }
                    }
                } else {
                    $currSessData = '';
                    if ($recObj && $recObj->getKey() > 0) {
                        $currSessData = $recObj->{ $fldName };
                    } elseif ($nID > 0) {
                        $currSessData = $this->currSessData($nID, $tblName, $fldName);
                    } else { // not a node, but general filter of entire core record's data set
                        if (isset($this->dataSets[$tblName]) && sizeof($this->dataSets[$tblName]) > 0) {
                            foreach ($this->dataSets[$tblName] as $ind => $row) {
                                if (isset($row->{ $fldName }) && trim($row->{ $fldName }) != '') {
                                    $currSessData = $row->{ $fldName };
                                }
                            }
                        } else {
                            $passed = false;
                        }
                    }
                    if (trim($currSessData) != '') {
                        if ($cond->CondOperator == '{') {
                            $passed = (in_array($currSessData, $cond->condVals));
                        } elseif ($cond->CondOperator == '}') {
                            $passed = (!in_array($currSessData, $cond->condVals));
                        }
                    } else {
                        if ($cond->CondOperator == '{') {
                            $passed = false;
                        } elseif ($cond->CondOperator == '}') {
                            $passed = true;
                        }
                    }
                }
            }
        }
        return $passed;
    }
    
    public function isCheckboxHelperTable($helperTbl = '')
    {
        $tbl = $helperTbl;
        if (trim($helperTbl) != '' && trim($GLOBALS["SL"]->currCyc["res"][1]) == '') {
            if (isset($GLOBALS["SL"]->dataHelpers) && sizeof($GLOBALS["SL"]->dataHelpers) > 0) {
                foreach ($GLOBALS["SL"]->dataHelpers as $helper) {
                    if ($helper->DataHelpTable == $helperTbl && trim($helper->DataHelpValueField) != '') {
                        $tbl = $helper->DataHelpParentTable;
                    }
                }
            }
        }
        return $tbl;
    }
    
    public function updateZipInfo($zipIn = '', $tbl = '', $fldState = '', $fldCounty = '', $fldAshrae = '', 
        $fldCountry = '', $setInd = 0)
    {
        if (trim($zipIn) == '' || trim($tbl) == '') {
            return false;
        }
        $GLOBALS["SL"]->loadStates();
        $zipRow = $GLOBALS["SL"]->states->getZipRow($zipIn);
        if ($zipRow && isset($zipRow->ZipZip) && isset($this->dataSets[$tbl])) {
            if (trim($fldState) != '') {
                $this->dataSets[$tbl][$setInd]->update([ $fldState  => $zipRow->ZipState  ]);
            }
            if (trim($fldCounty) != '') {
                $this->dataSets[$tbl][$setInd]->update([ $fldCounty => $zipRow->ZipCounty ]);
            }
            if (trim($fldCountry) != '' && isset($zipRow->ZipCountry)) {
                $this->dataSets[$tbl][$setInd]->update([ $fldCountry => $zipRow->ZipCountry ]);
            }
            if (trim($fldAshrae) != '') {
                $this->dataSets[$tbl][$setInd]->update([ $fldAshrae => $GLOBALS["SL"]->states->getAshrae($zipRow) ]);
            }
            return true;
        }
        return false;
    }
    
    public function getDataBranchUrl()
    {
        $url = '&branch=';
        if (sizeof($this->dataBranches) > 1) {
            for ($i = 1; $i < sizeof($this->dataBranches); $i++) {
                $url .= (($i > 1) ? '-' : '') . $this->dataBranches[$i]["branch"] . '-' 
                    . $this->dataBranches[$i]["itemID"];
            }
        }
        return $url;
    }
    
    public function loadDataBranchFromUrl($url)
    {
        $badBranch = false;
        $branches = ((trim($url) != '' && strpos($url, '-') !== false) ? explode('-', $url) : []);
        if (sizeof($branches) > 0) {
            for ($i = 0; $i < sizeof($branches); $i+=2) {
                if (!$badBranch) {
                    $chk = $this->getRowById($branches[$i], $branches[$i+1]);
                    if ($chk && isset($chk->created_at)) {
                        $this->dataBranches[] = ["branch" => $branches[$i], "loop" => '', "itemID" => $branches[$i+1]];
                    } else {
                        // also check for loop first?
                        $badBranch = true;
                    }
                }
            }
        }
        return true;
    }
    
    
    public function createTblExtendFlds($tblFrom, $idFrom, $tblTo, $xtraFlds = [], $save = true)
    {
        $mdl = $GLOBALS["SL"]->modelPath($tblTo);
        if (trim($mdl) != '') {
            if (!isset($this->dataSets[$tblTo])) {
                $this->dataSets[$tblTo] = [];
            }
            $ind = sizeof($this->dataSets[$tblTo]);
            eval("\$this->dataSets[\$tblTo][\$ind] = new " . $mdl . ";");
            $rowFrom = $this->getRowById($tblFrom, $idFrom);
            $extendFlds = $GLOBALS["SL"]->getTblFlds($tblFrom);
            if (sizeof($extendFlds) > 0) {
                foreach ($extendFlds as $i => $fld) {
                    if (isset($rowFrom->{ $fld })) {
                        $this->dataSets[$tblTo][$ind]->{ $GLOBALS["SL"]->tblAbbr[$tblTo] . $fld } = $rowFrom->{ $fld };
                    }
                }
            }
            if (sizeof($xtraFlds) > 0) {
                foreach ($xtraFlds as $fld => $val) {
                    $this->dataSets[$tblTo][$ind]->{ $fld } = $val;
                }
            }
        }
        if ($save) {
            $this->dataSets[$tblTo][$ind]->save();
        }
        return $this->dataSets[$tblTo][$ind];
    }
    
    protected function loadSessTestsAB()
    {
        $this->testsAB = new SurvDataTestsAB;
        $params = '';
        if (isset($this->dataSets[$this->coreTbl]) && sizeof($this->dataSets[$this->coreTbl]) > 0) {
            $abField = $GLOBALS["SL"]->tblAbbr[$this->coreTbl] . 'VersionAB';
            if (isset($this->dataSets[$this->coreTbl][0]->{ $abField })) {
                $params = $this->dataSets[$this->coreTbl][0]->{ $abField };
            }
        }
        $this->testsAB->addParamsAB($params);
        if ($GLOBALS["SL"]->REQ->has('ab') && trim($GLOBALS["SL"]->REQ->get('ab') != '')) {
            $this->testsAB->addParamsAB(trim($GLOBALS["SL"]->REQ->get('ab')));
        }
        if (isset($this->dataSets[$this->coreTbl]) && sizeof($this->dataSets[$this->coreTbl]) > 0) {
            $this->dataSets[$this->coreTbl][0]->update([
                $GLOBALS["SL"]->tblAbbr[$this->coreTbl] . 'VersionAB' => $this->testsAB->printParams()
                ]);
        }
        return true;
    }
    
}

