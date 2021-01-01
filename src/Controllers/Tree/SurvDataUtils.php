<?php
/**
  * SurvDataUtils holds the core variables and functional infrastructure
  * for managing the data, as organized by the database design.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.6
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use App\Models\SLNodeSaves;

class SurvDataUtils
{
    protected $coreID       = -3;
    protected $coreTbl      = '';
    protected $privacy      = 'public';
    protected $id2ind       = [];
    protected $loaded       = false;
    
    // These are collections of all this session's records for each table
    public $dataSets        = [];
    public $dataSetsSubbed  = [];
    
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

    
    public function setCoreID($coreTbl, $coreID = -3)
    {
        $this->coreTbl = $coreTbl;
        $this->coreID  = $coreID;
        return true;
    }
    
    public function getCoreID()
    {
        return $this->coreID;
    }
    
    public function getDataCoreID()
    {
        if (isset($this->dataSets[$this->coreTbl])) {
            $abbr = $GLOBALS["SL"]->tblAbbr[$this->coreTbl];
            $core = $this->dataSets[$this->coreTbl];
            if (sizeof($core) > 0 && isset($core[0]->{ $abbr . 'id' })) {
                return $core[0]->{ $abbr . 'id' };
            }
        }
        return -3;
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
    
    public function dataFind($tbl, $rowID)
    {
        if ($rowID <= 0) {
            return [];
        }
        $model = trim($GLOBALS["SL"]->modelPath($tbl));
        if ($model == '') {
            return [];
        }
        eval("\$recObj = " . $model . "::find(" . $rowID . ");");
        return $recObj;
    }
    
    public function dataWhere($tbl, $where, $whereVal, $operator = "=", $getFirst = "get")
    {
        $model = trim($GLOBALS["SL"]->modelPath($tbl));
        if ($model == '') {
            return null;
        }
        eval("\$recObj = " . $model . "::where('" . $where 
                . "', '" . $operator . "', '" . $whereVal . "')"
            . "->orderBy('" . $GLOBALS["SL"]->tblAbbr[$tbl] . "id', 'asc')"
            . "->" . $getFirst . "();");
        return $recObj;
    }
    
    public function deleteDataItem($nID, $tbl = '', $itemID = -3)
    {
        $itemInd = $this->getRowInd($tbl, $itemID);
        if ($itemID <= 0 || $itemInd < 0) {
            return false;
        }
        $model = trim($GLOBALS["SL"]->modelPath($tbl));
        if ($model == '') {
            return false;
        }
        eval($model . "::find(" . $itemID . ")->delete();");
        unset($this->dataSets[$tbl][$itemInd]);
        unset($this->id2ind[$tbl][$itemID]);
        return true;
    }
    
    public function deleteEntireCore()
    {
        if (sizeof($this->dataSets) > 0) {
            foreach ($this->dataSets as $tbl => $rows) {
                $model = trim($GLOBALS["SL"]->modelPath($tbl));
                if ($model != '' && sizeof($rows) > 0) {
                    foreach ($rows as $row) {
                        eval($GLOBALS["SL"]->modelPath($tbl) . "::find(" 
                            . intVal($row->getKey()) . ")->delete();");
                    }
                }
            }
            $this->refreshDataSets();
        }
        return true;
    }
    
    public function dataHas($tbl, $rowID = -3)
    {
        if ($rowID <= 0) {
            return (isset($this->dataSets[$tbl]) && sizeof($this->dataSets[$tbl]) > 0);
        }
        $rowInd = $this->getRowInd($tbl, $rowID);
        if ($rowInd >= 0 
            && isset($this->dataSets[$tbl]) 
            && $this->dataSets[$tbl][$rowInd]->getKey() == $rowID) {
            return true;
        }
        return false;
    }
    
    public function getRowInd($tbl, $rowID)
    {
        if ($rowID > 0 
            && isset($this->id2ind[$tbl]) 
            && isset($this->id2ind[$tbl][$rowID])) {
            if (intVal($this->id2ind[$tbl][$rowID]) >= 0) {
                return $this->id2ind[$tbl][$rowID];
            }
        }
        // else double-check
        if ($rowID > 0 
            && isset($this->dataSets[$tbl]) 
            && sizeof($this->dataSets[$tbl]) > 0) {
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
        if (sizeof($fldVals) > 0 
            && isset($this->dataSets[$tbl]) 
            && sizeof($this->dataSets[$tbl]) > 0) {
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
    
    public function dataFieldExists($tbl, $fld, $ind = 0)
    {
        return (isset($this->dataSets[$tbl]) 
            && isset($this->dataSets[$tbl][$ind])
            && isset($this->dataSets[$tbl][$ind]->{ $fld }));
    }
    
    public function dataFieldIsInt($tbl, $fld, $int = 1, $ind = 0)
    {
        if ($this->dataFieldExists($tbl, $fld, $ind)) {
            return ($int == intVal($this->dataSets[$tbl][$ind]->{ $fld }));
        }
        return false;
    }
    
    public function dataFieldIsY($tbl, $fld, $val = 'Y', $ind = 0)
    {
        if ($this->dataFieldExists($tbl, $fld, $ind)) {
            return (strtoupper($val) 
                == strtoupper(trim($this->dataSets[$tbl][$ind]->{ $fld })));
        }
        return false;
    }
    
    public function getLoopRows($loopName)
    {
        $rows = [];
        if (isset($this->loopItemIDs[$loopName]) 
            && sizeof($this->loopItemIDs[$loopName]) > 0) {
            foreach ($this->loopItemIDs[$loopName] as $itemID) {
                $loopTbl = $GLOBALS["SL"]->dataLoops[$loopName]->data_loop_table;
                $rows[] = $this->getRowById($loopTbl, $itemID);
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
        if (isset($this->loopItemIDs[$loopName]) 
            && sizeof($this->loopItemIDs[$loopName]) > 0) {
            foreach ($this->loopItemIDs[$loopName] as $ind => $id) {
                if ($id == $itemID) {
                    return $ind;
                }
            }
        }
        return -1;
    }
    
    public function addToMap($tbl1, $tbl1ID, $tbl1Ind, $tbl2, $tbl2ID, $tbl2Ind = -3, $lnkTbl = '')
    {
        if ($tbl1Ind < 0) {
            $tbl1Ind = $this->getRowInd($tbl1, $tbl1ID);
        }
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
                    "id1"  => $tbl1ID,
                    "id2"  => $tbl2ID,
                    "ind1" => $tbl1Ind,
                    "ind2" => $tbl2Ind
                ];
            }
        }
        return false;
    }
    
    public function removeFromMap($tbl1, $tbl1ID)
    {
        if ($tbl1ID > 0 
            && trim($tbl1) != ''
            && isset($this->kidMap[$tbl1])
            && sizeof($this->kidMap[$tbl1]) > 0) {
            foreach ($this->kidMap[$tbl1] as $tbl2 => $map) {
                if (sizeof($map) > 0) {
                    $delInds = [];
                    foreach ($map as $i => $linkage) {
                        if ($linkage["id1"] == $tbl1ID) {
                            $delInds[] = $i;
                        }
                    }
                    for ($i = (sizeof($delInds)-1); $i >= 0; $i--) {
                        unset($delInds[$i]);
                    }
                }
            }
        }
        return false;
    }
    
    public function getChild($tbl1, $tbl1ID, $tbl2, $type = 'id')
    {
        if (trim($tbl1) != '' && trim($tbl2) != '' && $tbl1ID >= 0) {
            if (isset($this->kidMap[$tbl1]) 
                && isset($this->kidMap[$tbl1][$tbl2]) 
                && sizeof($this->kidMap[$tbl1][$tbl2]) > 0) {
                foreach ($this->kidMap[$tbl1][$tbl2] as $map) {
                    if ($tbl1ID == $map[$type . "1"]) {
                        return $map[$type . "2"];
                    }
                }
            }
            if (isset($this->kidMap[$tbl2]) 
                && isset($this->kidMap[$tbl2][$tbl1]) 
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
            if (isset($this->kidMap[$tbl1]) 
                && isset($this->kidMap[$tbl1][$tbl2]) 
                && sizeof($this->kidMap[$tbl1][$tbl2]) > 0) {
                foreach ($this->kidMap[$tbl1][$tbl2] as $map) {
                    if ($tbl1ID == $map["id1"]) {
                        $retArr[] = $this->getRowById($tbl2, $map["id2"]);
                    }
                }
            } elseif (isset($this->kidMap[$tbl2]) 
                && isset($this->kidMap[$tbl2][$tbl1]) 
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
        if ($bInd >= 0 
            && trim($this->dataBranches[$bInd]["branch"]) != '' 
            && isset($this->dataSets[$tbl2]) 
            && sizeof($this->dataSets[$tbl2]) > 0) {
            $branch = $this->dataBranches[$bInd];
            $tbl2fld = $GLOBALS["SL"]->getFornNameFldName($tbl2, $branch["branch"]);
            if (trim($tbl2fld) != '') {
                foreach ($this->dataSets[$tbl2] as $i => $row) {
                    if (isset($row->{ $tbl2fld }) && $row->{ $tbl2fld } == $branch["itemID"]) {
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
                $tbl2ID = $this->getChild(
                    $this->dataBranches[$i]["branch"], 
                    $this->dataBranches[$i]["itemID"], 
                    $tbl2
                );
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
    
    public function logDataSave($nID = -3, $tbl = '', $itemID = -3, $fld = '', $newVal = '')
    {
        $nodeSave = new SLNodeSaves;
        $nodeSave->node_save_session    = 0;
        $sessKey = 'sessID' . $GLOBALS["SL"]->sessTree;
        if (session()->has($sessKey) && intVal(session()->get($sessKey)) > 0) {
            $nodeSave->node_save_session = session()->get($sessKey);
        }
        $nodeSave->node_save_node         = $nID;
        $nodeSave->node_save_tbl_fld      = $tbl . ':' . $fld;
        $nodeSave->node_save_loop_item_id = $itemID;
        if (!is_array($newVal)) {
            $nodeSave->node_save_new_val = $newVal;
        } else {
            ob_start();
            print_r($newVal);
            $nodeSave->node_save_new_val = ob_get_contents();
            ob_end_clean();
        }
        $nodeSave->save();
        return true;
    }
    
    protected function loadSessionDataLog($nID = -3, $tbl = '', $fld = '', $set = '')
    {
        $sessID = 0;
        $sessKey = 'sessID' . $GLOBALS["SL"]->sessTree;
        if (session()->has($sessKey) && intVal(session()->get($sessKey)) > 0) {
            $sessID = session()->get($sessKey);
        }
        $qryWheres = "where('node_save_session', \$sessID)->"
            . "where('node_save_node', " . $nID . ")->";
        if (trim($tbl) != '' && trim($fld) != '') {
            $qryWheres .= "where('node_save_tbl_fld', '" . $tbl . ":" . $fld 
                . ((trim($set) != '') ? "[" . $set . "]" : "") . "')->";
        }
        if (isset($GLOBALS["SL"]->closestLoop["itemID"]) 
            && intVal($GLOBALS["SL"]->closestLoop["itemID"]) > 0) {
            $qryWheres .= "where('node_save_loop_item_id', " 
                . $GLOBALS["SL"]->closestLoop["itemID"] . ")->";
        }
        eval("\$nodeSave = App\\Models\\SLNodeSaves::" . $qryWheres 
            . "orderBy('created_at', 'desc')->first();"); 
        if ($nodeSave && isset($nodeSave->node_save_new_val)) {
            return $nodeSave->node_save_new_val;
        }
        return '';
    }

    public function updateZipInfo($zipIn = '', $tbl = '', $fldState = '', $fldCounty = '', $fldAshrae = '', $fldCountry = '', $setInd = 0)
    {
        if (trim($zipIn) == '' || trim($tbl) == '') {
            return false;
        }
        $GLOBALS["SL"]->loadStates();
        $zipRow = $GLOBALS["SL"]->states->getZipRow($zipIn);
        if ($zipRow && isset($zipRow->zip_zip) 
            && isset($this->dataSets[$tbl])) {
            if (trim($fldState) != '' && isset($zipRow->zip_state)) {
                $this->dataSets[$tbl][$setInd]->update([ 
                    $fldState  => $zipRow->zip_state  
                ]);
            }
            if (trim($fldCounty) != '' && isset($zipRow->zip_county)) {
                $this->dataSets[$tbl][$setInd]->update([ 
                    $fldCounty => $zipRow->zip_county 
                ]);
            }
            if (trim($fldCountry) != '' && isset($zipRow->zip_country)) {
                $this->dataSets[$tbl][$setInd]->update([
                    $fldCountry => $zipRow->zip_country
                ]);
            }
            if (trim($fldAshrae) != '') {
                $ashrae = $GLOBALS["SL"]->states->getAshrae($zipRow);
                $this->dataSets[$tbl][$setInd]->update([
                    $fldAshrae => $ashrae
                ]);
            }
            return true;
        }
        return false;
    }


}