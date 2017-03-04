<?php
namespace SurvLoop\Controllers;

use App\Models\SLFields;
use App\Models\SLNodeSaves;

class SurvLoopData
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
    public $parentMap       = [];
    public $linkMap         = []; // obsolete? i think so
    
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
    
    
    public function loadCore($coreTbl, $coreID = -3, $dataBranches = [], $checkboxNodes = [], $isBigSurvLoop = [])
    {
        $this->setCoreID($coreTbl, $coreID);
        $this->dataBranches  = $dataBranches;
        $this->checkboxNodes = $checkboxNodes;
        $this->refreshDataSets($isBigSurvLoop);
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
        $this->dataSets = $this->id2ind = $this->kidMap = $this->parentMap = [];
        $this->loadData($this->coreTbl, $this->coreID);
        
        // check for data needed for root data loop which isn't connected to the core record
        if (sizeof($isBigSurvLoop) > 0 && trim($isBigSurvLoop[0]) != '') {
            eval("\$rows = " . $GLOBALS["SL"]->modelPath($isBigSurvLoop[0]) . "::orderBy('" . $isBigSurvLoop[1] 
                . "', '" . $isBigSurvLoop[2] . "')->get();");
            if ($rows && sizeof($rows) > 0) {
                foreach ($rows as $row) $this->loadData($isBigSurvLoop[0], $row->getKey(), $row);
            }
        }
        return true;
    }
    
    protected function initDataSet($tbl)
    {
        $setInd = 0;
        if (!isset($this->dataSets[$tbl])) {
            $this->dataSets[$tbl] = $this->id2ind[$tbl] = [];
        } else {
            $setInd = sizeof($this->dataSets[$tbl]);
        }
        return $setInd;
    }
    
                                                                                            
    public function loadData($tbl, $rowID, $recObj = [])
    {
        $GLOBALS["SL"]->modelPath($tbl);
        $subObj = [];
        if (trim($tbl) != '' && $rowID > 0) {
            if (!$recObj || sizeof($recObj) == 0) {
                $recObj = $this->dataFind($tbl, $rowID);
            }
            if ($tbl == $this->coreTbl && $rowID > 0 && $rowID == $this->coreID 
                && (!$recObj || sizeof($recObj) == 0)) {
                $this->newDataRecord($tbl, '', -3, true, $this->coreID);
            }
            if ($recObj && sizeof($recObj) > 0) {
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
                                if ($subObjs && sizeof($subObjs) > 0) $subObjs = array($subObjs);
                            } elseif (trim($subset->DataSubSubLnk) != '') {
                                $subObjs = $this->dataWhere($subset->DataSubSubTbl, $subset->DataSubSubLnk, $rowID);
                            }
                            if (sizeof($subObjs) == 0 && $subset->DataSubAutoGen == 1) {
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
                                    $GLOBALS["SL"]->tblAbbr[$loop->DataLoopTable].$keyField, $rowID);
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
                                    if ($findObj && sizeof($findObj) > 0) {
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
    
    
    
    protected function getRecordLinks($tbl = '', $extraOutFld = '', $extraOutVal = -3, $skipIncoming = false)
    {
        $linkages = [ "outgoing" => [], "incoming" => [] ];
        if (trim($extraOutFld) != '') $linkages["outgoing"][] = [$extraOutFld, $extraOutVal];
        // Outgoing Keys
        $flds = SLFields::select('FldName', 'FldForeignTable')
            ->where('FldTable', $GLOBALS["SL"]->tblI[$tbl])
            ->where('FldForeignTable', '>', 0)
            ->get();
        if ($flds && sizeof($flds) > 0) {
            foreach ($flds as $fldKey) {
                $foreignTbl = $GLOBALS["SL"]->tbl[$fldKey->FldForeignTable];
                if ($fldKey->FldForeignTable == $GLOBALS["SL"]->treeRow->TreeCoreTable) {
                    $linkages["outgoing"][] = [$GLOBALS["SL"]->tblAbbr[$tbl] . $fldKey->FldName, $this->coreID];
                } else { // not the special Core case, so find an ancestor
                    list($loopInd, $loopID) = $this->currSessDataPos($foreignTbl);
                    if ($loopID > 0) {
                        $newLink = [$GLOBALS["SL"]->tblAbbr[$tbl].$fldKey->FldName, $loopID];
                        if (!in_array($newLink, $linkages["outgoing"])) $linkages["outgoing"][] = $newLink;
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
            if ($flds && sizeof($flds) > 0) {
                foreach ($flds as $fldKey) {
                    $foreignTbl = $GLOBALS["SL"]->tbl[$fldKey->FldTable];
                    if ($fldKey->FldTable == $GLOBALS["SL"]->treeRow->TreeCoreTable) {
                        $linkages["incoming"][] = [$foreignTbl, $GLOBALS["SL"]->tblAbbr[$foreignTbl].$fldKey->FldName, $this->coreID];
                    } else { // not the special Core case, so find an ancestor
                        list($loopInd, $loopID) = $this->currSessDataPos($foreignTbl);
                        if ($loopID > 0) {
                            $newLink = [$foreignTbl, $GLOBALS["SL"]->tblAbbr[$foreignTbl].$fldKey->FldName, $loopID];
                            if (!in_array($newLink, $linkages["incoming"])) $linkages["incoming"][] = $newLink;
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
        eval("\$recObj = new " . $GLOBALS["SL"]->modelPath($tbl) . ";");
        if ($recID > 0) $recObj->{ $GLOBALS["SL"]->tblAbbr[$tbl] . 'ID' } = $recID;
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
            if (!$recObj || sizeof($recObj) == 0) {
                $recObj = $this->newDataRecordInner($tbl, $linkages, $recID);
                $this->refreshDataSets();
            }
        }
        return $recObj;
    }
    
    public function checkNewDataRecord($tbl = '', $fld = '', $newVal = -3, $linkages = [])
    {
        $recObj = [];
        if (sizeof($linkages) == 0) $linkages = $this->getRecordLinks($tbl, $fld, $newVal);
        if (sizeof($linkages["outgoing"]) > 0) {
            $recObj = $this->findRecLinkOutgoing($tbl, $linkages);
        }
        if ((!$recObj || sizeof($recObj) == 0) && sizeof($linkages["incoming"]) > 0) {
            foreach ($linkages["incoming"] as $link) {
                $incomingInd = $this->getRowInd($link[0], intVal($link[2]));
                if (isset($this->dataSets[$link[0]][$incomingInd]->{ $link[1] }) 
                    && intVal($this->dataSets[$link[0]][$incomingInd]->{ $link[1] }) > 0) {
                    $recInd = $this->getRowInd($tbl, intVal($this->dataSets[$link[0]][$incomingInd]->{ $link[1] }));
                    if ($recInd >= 0) $recObj = $this->dataSets[$tbl][$recInd];
                }
            }
        }
        return $recObj;
    }
    
    public function deleteDataRecord($tbl = '', $fld = '', $newVal = -3)
    {
        $linkages = $this->getRecordLinks($tbl, $fld, $newVal);
        if (sizeof($linkages["incoming"]) == 0) {
            $delObj = $this->findRecLinkOutgoing($tbl, $linkages);
            if ($delObj && sizeof($delObj) > 0) $delObj->delete();
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
    
    protected function dataFind($tbl, $rowID)
    {
        if ($rowID <= 0) return [];
        eval("\$recObj = " . $GLOBALS["SL"]->modelPath($tbl) . "::find(" . $rowID . ");");
        return $recObj;
    }
    
    protected function dataWhere($tbl, $where, $whereVal, $operator = "=", $getFirst = "get")
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
        $rowInd = $this->getRowInd($tbl, $rowID);
        if ($rowInd >= 0) return $this->dataSets[$tbl][$rowInd];
        return [];
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
    
    public function getLoopRowIDs()
    {
        if (isset($this->loopItemIDs[$loopName])) return $this->loopItemIDs[$loopName];
        return [];
    }
    
    public function getLoopIndFromID($loopName, $itemID)
    {
        if (isset($this->loopItemIDs[$loopName]) && sizeof($this->loopItemIDs[$loopName]) > 0) {
            foreach ($this->loopItemIDs[$loopName] as $ind => $id) {
                if ($id == $itemID) return $ind;
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
                if ($subObj && sizeof($subObj) > 0) {
                    if (!$this->dataHas($tbl2, $subObj->getKey())) {
                        $this->addToMap($tbl1, $tbl1ID, $tbl1Ind, $tbl2, $subObj->getKey());
                        $this->loadData($tbl2, $subObj->getKey(), $subObj);
                    }
                }
            }
        }
        return true;
    }
    
    protected function addToMap($tbl1, $tbl1ID, $tbl1Ind, $tbl2, $tbl2ID, $tbl2Ind = -3, $lnkTbl = '')
    {
        if (trim($tbl1) != '' && trim($tbl2) != '' && $tbl1ID > 0 && $tbl1Ind >= 0 && $tbl2ID > 0) {
            if (!isset($this->kidMap[$tbl1]))           $this->kidMap[$tbl1] = [];
            if (!isset($this->kidMap[$tbl1][$tbl2]))    $this->kidMap[$tbl1][$tbl2] = [ "id" => [], "ind" => [] ];
            if (!isset($this->parentMap[$tbl2]))        $this->parentMap[$tbl2] = [];
            if (!isset($this->parentMap[$tbl2][$tbl1])) $this->parentMap[$tbl2][$tbl1] = [ "id" => [], "ind" => [] ];
            
            if ($tbl2Ind < 0) { // !presuming it's about to be loaded
                $tbl2Ind = (isset($this->dataSets[$tbl2])) ? sizeof($this->dataSets[$tbl2]) : 0;
            }
            
            $this->kidMap[$tbl1][$tbl2]["id" ][$tbl1ID]     = $tbl2ID;
            $this->kidMap[$tbl1][$tbl2]["ind"][$tbl1Ind]    = $tbl2Ind;
            $this->parentMap[$tbl2][$tbl1]["id" ][$tbl2ID]  = $tbl1ID;
            $this->parentMap[$tbl2][$tbl1]["ind"][$tbl2Ind] = $tbl1Ind;
        }
        return false;
    }
    
    public function getChild($tbl1, $tbl1ID, $tbl2, $type = "id")
    {
        if (trim($tbl1) != '' && trim($tbl2) != '' && $tbl1ID >= 0 
            && isset($this->kidMap[$tbl1]) && isset($this->kidMap[$tbl1][$tbl2]) 
            && isset($this->kidMap[$tbl1][$tbl2][$type][$tbl1ID])) {
            return $this->kidMap[$tbl1][$tbl2][$type][$tbl1ID];
        }
        return -3;
    }
    
    public function getChildRow($tbl1, $tbl1ID, $tbl2)
    {
        $childID = $this->getChild($tbl1, $tbl1ID, $tbl2);
        if ($childID > 0) return $this->getRowById($tbl2, $childID);
        return [];
    }
    
    public function getChildRows($tbl1, $tbl1ID, $tbl2)
    {
        $retArr = [];
        if (trim($tbl1) != '' && trim($tbl2) != '' && $tbl1ID >= 0 
            && isset($this->kidMap[$tbl1]) && isset($this->kidMap[$tbl1][$tbl2]) 
            && isset($this->kidMap[$tbl1][$tbl2]["id"][$tbl1ID])
            && intVal($this->kidMap[$tbl1][$tbl2]["id"][$tbl1ID]) > 0) {
            $retArr[] = $this->getRowById($tbl2, $this->kidMap[$tbl1][$tbl2]["id"][$tbl1ID]);
        }
        return $retArr;
    }
    
    public function sessChildIDFromParent($tbl2)
    {
        for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
            if ($this->dataBranches[$i]["branch"] != $tbl2) {
                $tbl2ID = $this->getChild($this->dataBranches[$i]["branch"], $this->dataBranches[$i]["itemID"], $tbl2);
                if ($tbl2ID > 0) return $tbl2ID;
            }
        }
        return -3;
    }

    
    protected function getAllTableIDs($tbl)
    {
        $tmpIDs = [];
        if (isset($this->dataSets[$tbl]) && sizeof($this->dataSets[$tbl]) > 0) {
            foreach ($this->dataSets[$tbl] as $recObj) $tmpIDs[] = $recObj->getKey();
        }
        return $tmpIDs;
    }
    
    public function getLoopDoneItems($loopName, $fld = '')
    {
        $tbl = $GLOBALS["SL"]->dataLoops[$loopName]->DataLoopTable;
        if (trim($fld) == '') {
            list($tbl, $fld) = $GLOBALS["SL"]->splitTblFld($GLOBALS["SL"]->dataLoops[$loopName]->DataLoopDoneFld);
        }
        $this->loopItemIDsDone = $saves = [];
        $saves = SLNodeSaves::where('NodeSaveSession', $this->coreID)
            ->where('NodeSaveTblFld', 'LIKE', $tbl.':'.$fld)
            ->get();
        if ($saves && sizeof($saves) > 0) {
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
                        if (trim($newFld) == '' 
                            && trim($fld) != '' && $cond->CondOperator == '{' && sizeof($cond->condVals) == 1
                            && $GLOBALS["SL"]->tbl[$cond->CondTable] == $GLOBALS["SL"]->closestLoop["obj"]->DataLoopTable) {
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
    
    public function startTmpDataBranch($tbl, $itemID = -3)
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
            if (intVal($itemID) <= 0) $itemID = $this->sessChildIDFromParent($tbl);
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
                if ($tbl == $b["branch"]) $this->dataBranches[] = $b;
            }
        }
        return true;
    }

    public function currSessDataPos($tbl, $hasParentDataManip = false)
    {
        if (trim($tbl) == '') return [-3, -3];
        if ($tbl == $this->coreTbl) return [0, $this->coreID];
        $itemID = $itemInd = -3;
        $tblNew = $this->isCheckboxHelperTable($tbl);
        $tbl = $tblNew;
        for ($i=(sizeof($this->dataBranches)-1); $i>=0; $i--) {
            if (intVal($itemID) <= 0 && isset($this->dataBranches[$i])) {
                list($itemInd, $itemID) = $this->currSessDataPosBranch($tbl, $this->dataBranches[$i]);
            }
        }
        if (intVal($itemID) <= 0 && !$hasParentDataManip) {
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
        return [$itemInd, $itemID];
    }
    
    public function currSessDataPosBranch($tbl, $branch)
    {
        $itemID = 0;
        if ($tbl == $branch["branch"]) {
            if (trim($branch["loop"]) != '') {
                $itemID = $GLOBALS["SL"]->getSessLoopID($branch["loop"]);
            } elseif (intVal($branch["itemID"]) > 0) {
                $itemID = $branch["itemID"];
            }
            /*
            elseif (isset($this->dataSets[$tbl]) && isset($this->dataSets[$tbl][0])) 
            {
                $itemID = $this->dataSets[$tbl][0]->getKey();
            }
            */
        }
        /* this needs to happen elsewhere, in a more specific usage?
        elseif (trim($branch["branch"]) != '' && trim($branch["loop"]) == ''
            && isset($this->dataSets[$branch["branch"]]) && isset($this->dataSets[$branch["branch"]][0])) 
        {
            $itemID = $this->dataSets[$branch["branch"]][0]->getKey();
            if ($itemID > 0 && isset($this->id2ind[$branch["branch"]]) 
                && isset($this->id2ind[$branch["branch"]][$itemID])) {
                $itemInd = $this->id2ind[$branch["branch"]][$itemID];
            }
        }
        */
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
    public function currSessData($nID, $tbl, $fld = '', $action = 'get', $newVal = '', $hasParentDataManip = false, 
        $itemInd = -3, $itemID = -3)
    {
        if (trim($tbl) == '' || trim($fld) == '' || !$this->loaded) return '';
        if (in_array($nID, $this->checkboxNodes)) {
            $tblFld = $tbl . '-' . $fld;
            $this->helpInfo[$tblFld] = $this->getCheckboxHelperInfo($tbl, $fld);
            if ($this->helpInfo[$tblFld]["link"] && isset($this->helpInfo[$tblFld]["link"]->DataHelpValueField)) {
                return $this->currSessDataCheckbox($nID, $tbl, $fld);
            }
        }
        if ($itemInd < 0 || $itemID <= 0) list($itemInd, $itemID) = $this->currSessDataPos($tbl, $hasParentDataManip);
        if ($itemInd < 0 || $itemID <= 0) return '';
        if ($action == 'get') {
            if ($this->dataFieldExists($tbl, $itemInd, $fld)) {
                //if ($nID == 28) { echo '<br /><br /><br />nID: ' . $nID . ', tbl: ' . $tbl . ', fld: ' . $fld . ', newVal: ' . $newVal . ', type: ' . $GLOBALS["SL"]->fldTypes[$tbl][$fld] . ' - ' . $this->dataSets[$tbl][$itemInd]->{ $fld } . '<br />'; exit; }
                return $this->dataSets[$tbl][$itemInd]->{ $fld };
            }
        } elseif ($action == 'update' && $fld != ($GLOBALS["SL"]->tblAbbr[$tbl].'ID')) {
            $this->logDataSave($nID, $tbl, $itemID, $fld, $newVal);
            if ($GLOBALS["SL"]->fldTypes[$tbl][$fld] == 'INT') $newVal = intVal($newVal);
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
    
    public function currSessDataCheckbox($nID, $tbl, $fld = '', $action = 'get', $newVals = [])
    {
        $tblFld = $tbl . '-' . $fld;
        $this->helpInfo[$tblFld] = $this->getCheckboxHelperInfo($tbl, $fld);
        if (!$this->helpInfo[$tblFld]["link"] || !isset($this->helpInfo[$tblFld]["link"]->DataHelpValueField)) {
            return $this->currSessData($nID, $tbl, $fld, $action, ';' . implode(';;', $newVals) . ';');
        }
        if ($action == 'get') {
            return ((sizeof($this->helpInfo[$tblFld]["pastVals"]) > 0) ? ';'.implode(';;', $this->helpInfo[$tblFld]["pastVals"]).';' : '');
        } elseif ($action == 'update') {
            $this->logDataSave($nID, $tbl, $this->helpInfo[$tblFld]["parentID"], $fld, $newVals);
            // check for newly submitted responses...
            if (sizeof($newVals) > 0) {
                foreach ($newVals as $i => $val) {
                    if (!in_array($val, $this->helpInfo[$tblFld]["pastVals"]) 
                        && isset($this->helpInfo[$tblFld]["link"]->DataHelpTable)) {
                        eval("\$newObj = new " 
                            . $GLOBALS["SL"]->modelPath($this->helpInfo[$tblFld]["link"]->DataHelpTable) . ";");
                        $newObj->{ $this->helpInfo[$tblFld]["link"]->DataHelpKeyField } 
                            = $this->helpInfo[$tblFld]["parentID"];
                        $newObj->{ $this->helpInfo[$tblFld]["link"]->DataHelpValueField } = $val;
                        $newObj->save();
                    }
                }
            }
            // check for previously submitted responses are being deselected...
            if (sizeof($this->helpInfo[$tblFld]["pastVals"]) > 0) {
                foreach ($this->helpInfo[$tblFld]["pastVals"] as $i => $val) {
                    if (!in_array($val, $newVals)) {
                        $this->deleteDataItem($nID, $this->helpInfo[$tblFld]["link"]->DataHelpTable, 
                            $this->helpInfo[$tblFld]["pastValToID"][$val]);
                    }
                }
            }
        }
        return '';
    }
    
    public function getCheckboxHelperInfo($tbl, $fld)
    {
        $tblFld = $tbl . '-' . $fld;
        if (!isset($this->helpInfo[$tblFld])) {
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
        }
        return $this->helpInfo[$tblFld];
    }
    
    public function deleteDataItem($nID, $tbl = '', $itemID = -3)
    {
        $itemInd = $this->getRowInd($tbl, $itemID);
        if ($itemID <= 0 || $itemInd < 0) return false;
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
                        eval($GLOBALS["SL"]->modelPath($tbl) . "::find(" . $row->getKey() . ")->delete();");
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
        $nodeSave->NodeSaveSession    = $this->coreID;
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
        $qryWheres = "where('NodeSaveSession', \$this->coreID)->where('NodeSaveNode', ".$nID.")->";
        if (trim($tbl) != '' && trim($fld) != '') {
            $qryWheres .= "where('NodeSaveTblFld', '" . $tbl . ":" . $fld 
                . ((trim($set) != '') ? "[" . $set . "]" : "") . "')->";
        }
        if (isset($GLOBALS["SL"]->closestLoop["itemID"]) && intVal($GLOBALS["SL"]->closestLoop["itemID"]) > 0) {
            $qryWheres .= "where('NodeSaveLoopItemID', " . $GLOBALS["SL"]->closestLoop["itemID"] . ")->";
        }
        eval("\$nodeSave = App\\Models\\SLNodeSaves::" . $qryWheres . "orderBy('created_at', 'desc')->first();"); 
        if ($nodeSave && isset($nodeSave->NodeSaveNewVal)) return $nodeSave->NodeSaveNewVal;
        return '';
    }
    
    public function parseCondition($cond = [], $recObj = [], $nID = -3)
    {
        $passed = true;
        if ($cond && isset($cond->CondDatabase) && $cond->CondOperator != 'CUSTOM') {
            if ($cond->CondOperator == 'URL-PARAM') {
                if (trim($cond->CondOperDeet) == '' || !$GLOBALS["SL"]->REQ->has($cond->CondOperDeet) 
                    || trim($GLOBALS["SL"]->REQ->get($cond->CondOperDeet)) != trim($cond->condFldResponses["vals"][0][1])) {
                    $passed = false;
                }
            } else {
                $loopName = ((intVal($cond->CondLoop) > 0) ? $GLOBALS["SL"]->dataLoopNames[$cond->CondLoop] : '');
                if (intVal($cond->CondTable) <= 0 && trim($loopName) != '' && isset($GLOBALS["SL"]->dataLoops[$loopName])) {
                    $tblName = $GLOBALS["SL"]->dataLoops[$loopName]->DataLoopTable;
                } else {
                    $tblName = $GLOBALS["SL"]->tbl[$cond->CondTable];
                }
                //if ($tbl != $setTbl) list($setTbl, $setSet, $loopItemID) = $this->getDataSetTblTranslate($set, $tbl, $loopItemID);
                if ($cond->CondOperator == 'EXISTS=') {
                    if (!isset($this->dataSets[$tblName]) || (intVal($cond->CondLoop) > 0 
                        && !isset($this->loopItemIDs[$loopName]))) {
                        if (intVal($cond->CondOperDeet) == 0) {
                            $passed = true;
                        } else {
                            $passed = false;
                        }
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
                        if (intVal($cond->CondLoop) > 0) $existCnt = sizeof($this->loopItemIDs[$loopName]);
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
                    $currSessData = '';
                    if ($recObj && $recObj->getKey() > 0) {
                        $currSessData = $recObj->{ $fldName };
                    } else {
                        $currSessData = $this->currSessData($nID, $tblName, $fldName);
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
        // This is where all the condition-inversion is applied
        if ($nID > 0 && isset($GLOBALS["SL"]->nodeCondInvert[$nID]) 
            && isset($GLOBALS["SL"]->nodeCondInvert[$nID][$cond->CondID])) {
            $passed = !$passed;
        }
        return $passed;
    }
    
    
    
    public function isCheckboxHelperTable($helperTbl = '')
    {
        $tbl = $helperTbl;
        if (trim($helperTbl) != '') {
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
    
}
