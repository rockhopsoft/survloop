<?php
/**
  * SurvData is a critical class with loads all the details of a survey's core record
  * according to the database design, and it's settings for a given survey.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.0.18
  */
namespace SurvLoop\Controllers\Tree;

use DB;
use Auth;
use App\Models\SLFields;
use App\Models\SLNodeSaves;
use SurvLoop\Controllers\Tree\SurvDataTestsAB;
use SurvLoop\Controllers\Tree\SurvDataUtils;

class SurvData extends SurvDataUtils
{
    
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
    
    public function refreshDataSets($isBigSurvLoop = [])
    {
        $this->dataSets = $this->id2ind = $this->kidMap 
            = $this->helpInfo = $this->dataSetsSubbed = [];
        $this->loadData($this->coreTbl, $this->coreID);
// if (Auth::user() && Auth::user()->id) $this->loadData('users', Auth::user()->id);
// check for data needed for root data loop which isn't connected to the core record
        if (sizeof($isBigSurvLoop) > 0 && trim($isBigSurvLoop[0]) != '') {
            $model = trim($GLOBALS["SL"]->modelPath($isBigSurvLoop[0]));
            if ($model != '') {
                eval("\$rows = " . $model 
                    . "::orderBy('" . $isBigSurvLoop[1] . "', '" . $isBigSurvLoop[2] 
                    . "')->get();");
                if ($rows->isNotEmpty()) {
                    foreach ($rows as $row) {
                        $this->loadData($isBigSurvLoop[0], $row->getKey(), $row);
                    }
                }
            }
        }
        return true;
    }


    public function loadData($tbl, $rowID, $recObj = NULL)
    {
        if (isset($this->dataSetsSubbed[$tbl . $rowID])
            && $this->dataSetsSubbed[$tbl . $rowID]) {
            return false; // no double-loading is worth it
        }
        //$GLOBALS["SL"]->microLog('loadData( start ' . $tbl . ', ' . $rowID);
        $GLOBALS["SL"]->modelPath($tbl);
        $subObj = [];
        if (trim($tbl) != '' && $rowID > 0) {
            if (!$recObj) {
                $recObj = $this->dataFind($tbl, $rowID);
            }
            if ($tbl == $this->coreTbl && $rowID == $this->coreID && !$recObj) {
                $this->newDataRecord($tbl, '', -3, true, $this->coreID);
            }
            if ($recObj) {
                // Adding record to main set of all records
                $setInd = $this->initDataSet($tbl);
                $this->dataSets[$tbl][$setInd] = $recObj;
                $this->id2ind[$tbl][$recObj->getKey()] = $setInd;
                
                // Recurse through this parent's families...
                if (isset($GLOBALS["SL"]->dataSubsets) 
                    && sizeof($GLOBALS["SL"]->dataSubsets) > 0) {
                    foreach ($GLOBALS["SL"]->dataSubsets as $subset) {
                        if ($subset->data_sub_tbl == $tbl) {
                            $subObjs = [];
                            if (trim($subset->data_sub_tbl_lnk) != '' 
                                && intVal($recObj->{ $subset->data_sub_tbl_lnk }) > 0) {
                                $subObjs = $this->dataFind(
                                    $subset->data_sub_sub_tbl, 
                                    $recObj->{ $subset->data_sub_tbl_lnk }
                                );
                                if ($subObjs) {
                                    $subObjs = [ $subObjs ];
                                }
                            } elseif (trim($subset->data_sub_sub_lnk) != '') {
                                $subObjs = $this->dataWhere(
                                    $subset->data_sub_sub_tbl, 
                                    $subset->data_sub_sub_lnk, 
                                    $rowID
                                );
                            }
                            if (empty($subObjs) && $subset->data_sub_auto_gen == 1) {
                                $subObjs = [
                                    $this->newDataRecordInner($subset->data_sub_sub_tbl)
                                ];
                                if (trim($subset->data_sub_tbl_lnk) != '') {
                                    $recObj->update([
                                        $subset->data_sub_tbl_lnk => $subObjs[0]->getKey()
                                    ]);
                                    $recObj->save();
                                } elseif (trim($subset->data_sub_sub_lnk) != '') {
                                    $subObjs[0]->data_sub_sub_lnk = $rowID;
                                    $subObjs[0]->save();
                                }
                            }
                            $this->processSubObjs(
                                $tbl, 
                                $rowID, 
                                $setInd, 
                                $subset->data_sub_sub_tbl, 
                                $subObjs
                            );
                        }
                    }
                }
                
                // checking loops...
                if ($tbl == $this->coreTbl
                    && isset($GLOBALS["SL"]->dataLoops) 
                    && sizeof($GLOBALS["SL"]->dataLoops) > 0) {
                    foreach ($GLOBALS["SL"]->dataLoops as $loopName => $loop) {
                        if (isset($loop->data_loop_table) 
                            && isset($GLOBALS["SL"]->tblI[$tbl])
                            && isset($GLOBALS["SL"]->tblI[$loop->data_loop_table])) {
                            $keyField = $GLOBALS["SL"]->getForeignLnk(
                                $GLOBALS["SL"]->tblI[$loop->data_loop_table], 
                                $GLOBALS["SL"]->tblI[$tbl]
                            );
                            if (trim($keyField) != '') {
                                $loopTbl = $loop->data_loop_table;
                                $keyField = $GLOBALS["SL"]->tblAbbr[$loopTbl] . $keyField;
                                $subObjs = $this->dataWhere($loopTbl, $keyField, $rowID);
                                $this->processSubObjs($tbl, $rowID, $setInd, $loopTbl, $subObjs);
                            }
                        }
                    }
                }
                
                // checking helpers...
                if (isset($GLOBALS["SL"]->dataHelpers) 
                    && sizeof($GLOBALS["SL"]->dataHelpers) > 0) {
                    foreach ($GLOBALS["SL"]->dataHelpers as $helper) {
                        if ($helper->data_help_parent_table == $tbl) {
                            $hlpTbl = $helper->data_help_table;
                            $hlpFld = $helper->data_help_key_field;
                            $subObjs = $this->dataWhere($hlpTbl, $hlpFld, $rowID);
                            $this->processSubObjs($tbl, $rowID, $setInd, $hlpTbl, $subObjs);
                        }
                    }
                }
                
                // checking linkages...
                if (isset($GLOBALS["SL"]->dataLinksOn) 
                    && sizeof($GLOBALS["SL"]->dataLinksOn) > 0) {
                    foreach ($GLOBALS["SL"]->dataLinksOn as $linkage) {
                        if ($tbl == $linkage[4]) {
                            $linkage = [
                                $linkage[4], 
                                $linkage[3], 
                                $linkage[2], 
                                $linkage[1], 
                                $linkage[0]
                            ];
                        }
                        if ($tbl == $linkage[0]) {
                            $lnkObjs = $this->dataWhere($linkage[2], $linkage[1], $rowID);
                            if ($lnkObjs && sizeof($lnkObjs) > 0) {
                                $this->processSubObjs(
                                    $tbl, 
                                    $rowID, 
                                    $setInd, 
                                    $linkage[2], 
                                    $lnkObjs
                                );
                                foreach ($lnkObjs as $lnkObj) {
                                    $findObj = $this->dataFind(
                                        $linkage[4], 
                                        $lnkObj->{ $linkage[3] }
                                    );
                                    if ($findObj) {
                                        $subObjs = array($findObj);
                                        $this->processSubObjs(
                                            $tbl, 
                                            $rowID, 
                                            $setInd, 
                                            $linkage[4], 
                                            $subObjs
                                        );
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
        //$GLOBALS["SL"]->microLog('loadData( end ' . $tbl . ', ' . $rowID);
        $this->dataSetsSubbed[$tbl . $rowID] = true;
        return true;
    }
    
    protected function getRecordLinks($tbl = '', $extraOutFld = '', $extraOutVal = -3, $skipIncoming = true)
    {
        $linkages = [
            "outgoing" => [],
            "incoming" => []
        ];
        if (trim($extraOutFld) != '') {
            $linkages["outgoing"][] = [$extraOutFld, $extraOutVal];
        }
        if (trim($tbl) == '' || !isset($GLOBALS["SL"]->tblI[$tbl])) {
            return $linkages;
        }
        // Outgoing Keys
        $flds = SLFields::select('fld_name', 'fld_foreign_table')
            ->where('fld_table', $GLOBALS["SL"]->tblI[$tbl])
            ->where('fld_foreign_table', '>', 0)
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fldKey) {
                $foreignTbl = $GLOBALS["SL"]->tbl[$fldKey->fld_foreign_table];
                if ($fldKey->fld_foreign_table == $GLOBALS["SL"]->treeRow->tree_core_table) {
                    $linkages["outgoing"][] = [
                        $GLOBALS["SL"]->tblAbbr[$tbl] . $fldKey->fld_name, 
                        $this->coreID
                    ];
                } else { // not the special Core case, so find an ancestor
                    list($loopInd, $loopID) = $this->currSessDataPos($foreignTbl);
                    if ($loopID > 0) {
                        $newLink = [
                            $GLOBALS["SL"]->tblAbbr[$tbl] . $fldKey->fld_name, 
                            $loopID
                        ];
                        if (!in_array($newLink, $linkages["outgoing"])) {
                            $linkages["outgoing"][] = $newLink;
                        }
                    }
                }
            }
        }
        
        // Incoming Keys
        if (!$skipIncoming) {
            $flds = SLFields::select('fld_name', 'fld_table')
                ->where('fld_foreign_table', $GLOBALS["SL"]->tblI[$tbl])
                ->where('fld_foreign_table', '>', 0)
                ->where('fld_table', '>', 0)
                ->get();
            if ($flds->isNotEmpty()) {
                foreach ($flds as $fldKey) {
                    if (isset($GLOBALS["SL"]->tbl[$fldKey->fld_table])) {
                        $foreignTbl = $GLOBALS["SL"]->tbl[$fldKey->fld_table];
                        $foreignFldName = $GLOBALS["SL"]->tblAbbr[$foreignTbl] . $fldKey->fld_name;
                        if ($fldKey->fld_table == $GLOBALS["SL"]->treeRow->tree_core_table) {
                            $linkages["incoming"][] = [
                                $foreignTbl, 
                                $foreignFldName, 
                                $this->coreID
                            ];
                        } else { // not the special Core case, so find an ancestor
                            list($loopInd, $loopID) = $this->currSessDataPos($foreignTbl);
                            if ($loopID > 0) {
                                $newLink = [
                                    $foreignTbl, 
                                    $foreignFldName, 
                                    $loopID
                                ];
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
        $model = trim($GLOBALS["SL"]->modelPath($tbl));
        if ($model == '') {
            return false;
        }
        $eval = "\$recObj = " . $model . "::" . $eval . "first();";
        eval($eval);
        return $recObj;
    }
    
    public function newDataRecordInner($tbl = '', $linkages = [], $recID = -3, $skipLinks = [])
    {
        if (trim($tbl) == '') {
            return [];
        }
        $model = trim($GLOBALS["SL"]->modelPath($tbl));
        if ($model == '') {
            return [];
        }
        eval("\$recObj = new " . $model . ";");
        if ($recID > 0) {
            $recObj->{ $GLOBALS["SL"]->tblAbbr[$tbl] . 'id' } = $recID;
        }
        if (isset($linkages["outgoing"]) && sizeof($linkages["outgoing"]) > 0) {
            foreach ($linkages["outgoing"] as $i => $link) {
                if (!in_array($link[0], $skipLinks)) {
                    $recObj->{ $link[0] } = $link[1];
                }
            }
        }
        $recObj->save();
        $setInd = $this->initDataSet($tbl);
        $this->dataSets[$tbl][$setInd] = $recObj;
        $this->id2ind[$tbl][$recObj->getKey()] = $setInd;
        if (isset($linkages["incoming"]) && sizeof($linkages["incoming"]) > 0) {
            foreach ($linkages["incoming"] as $link) {
                if (!in_array($link[0], $skipLinks)) {
                    $incomingInd = $this->getRowInd($link[0], intVal($link[2]));
                    if ($incomingInd >= 0) {
                        $this->dataSets[$link[0]][$incomingInd]->update([
                            $link[1] => $recObj->getKey()
                        ]);
                    }
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
                    $recInd = $this->getRowInd(
                        $tbl, 
                        intVal($this->dataSets[$link[0]][$incomingInd]->{ $link[1] })
                    );
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
        $model = trim($GLOBALS["SL"]->modelPath($tbl));
        if ($model == '') {
            return 1;
        }
        eval("\$recObj = " . $model . "::select('" 
            . $GLOBALS["SL"]->tblAbbr[$tbl] . "id')->orderBy('" 
            . $GLOBALS["SL"]->tblAbbr[$tbl] . "id', 'desc')->first();");
        if ($recObj && isset($recObj->{ $GLOBALS["SL"]->tblAbbr[$tbl] . 'id' })) {
            return (1+$recObj->{ $GLOBALS["SL"]->tblAbbr[$tbl] . 'id' });
        }
        return 1;
    }
    
    public function deleteDataRecord($tbl = '', $fld = '', $newVal = -3)
    {
        $linkages = $this->getRecordLinks($tbl, $fld, $newVal);
        if (sizeof($linkages["incoming"]) == 0) {
            $delObj = $this->findRecLinkOutgoing($tbl, $linkages);
            if ($delObj) {
                $delObj->delete();
            }
        } else {
            foreach ($linkages["incoming"] as $link) {
                $incomingInd = $this->getRowInd($link[0], intVal($link[2]));
                if (isset($this->dataSets[$link[0]][$incomingInd]->{ $link[1] })) {
                    $recInd = $this->getRowInd(
                        $tbl, 
                        intVal($this->dataSets[$link[0]][$incomingInd]->{ $link[1] })
                    );
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
        if ($tbl == '' || $id <= 0) {
            return false;
        }
        $recInd = $this->getRowInd($tbl, $id);
        if ($recInd >= 0) {
            $this->dataSets[$tbl][$recInd]->delete();
        }
        if ($refresh) {
            $this->refreshDataSets();
        }
        return true;
    }
    
    public function addRemoveSubsets($tbl, $newTot = -3)
    {
        if (trim($tbl) == '' || $newTot < 0) {
            return false;
        }
        $currTot = ((isset($this->dataSets[$tbl])) ? sizeof($this->dataSets[$tbl]) : 0);
        if ($newTot > $currTot) {
            for ($i = $currTot; $i < $newTot; $i++) {
                $this->newDataRecord($tbl, '', -3, true);
            }
        } elseif ($newTot < $currTot) {
            for ($i = $newTot; $i < $currTot; $i++) {
                $this->dataSets[$tbl][$i]->delete();
            }
            $this->refreshDataSets();
        }
        return true;
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
    
    public function getLoopDoneItems($loopName, $fld = '')
    {
        $tbl = $GLOBALS["SL"]->dataLoops[$loopName]->data_loop_table;
        if (trim($fld) == '') {
            $doneFld = $GLOBALS["SL"]->dataLoops[$loopName]->data_loop_done_fld;
            list($tbl, $fld) = $GLOBALS["SL"]->splitTblFld($doneFld);
        }
        $this->loopItemIDsDone = $saves = [];
        $saves = DB::table('sl_node_saves')
            ->join('sl_sess', 'sl_node_saves.node_save_session', '=', 'sl_sess.sess_id')
            ->where('sl_sess.sess_tree', '=', $GLOBALS["SL"]->treeID)
            ->where('sl_sess.sess_core_id', '=', $this->coreID)
            ->where('sl_node_saves.node_save_tbl_fld', 'LIKE', $tbl . ':' . $fld)
            ->get();
        if ($saves->isNotEmpty()) {
            foreach ($saves as $save) {
                if (in_array($save->node_save_loop_item_id, $this->loopItemIDs[$loopName]) 
                    && !in_array($save->node_save_loop_item_id, $this->loopItemIDsDone)) {
                    $this->loopItemIDsDone[] = $save->node_save_loop_item_id;
                }
            }
        }
        $this->getLoopDoneNextItemID($loopName);
        return $this->loopItemIDsDone;
    }
    
    public function getLoopDoneNextItemID($loopName)
    {
        $this->loopItemsNextID = -3;
        if (sizeof($this->loopItemIDs[$loopName]) > 0) {
            foreach ($this->loopItemIDs[$loopName] as $id) {
                if ($this->loopItemsNextID <= 0 && !in_array($id, $this->loopItemIDsDone)) {
                    $this->loopItemsNextID = $id;
                }
            }
        }
        return true;
    }
    
    public function createNewDataLoopItem($nID = -3, $skipLinks = [])
    {
        if (intVal($GLOBALS["SL"]->closestLoop["obj"]->data_loop_auto_gen) == 1) {
            $loopName = $GLOBALS["SL"]->closestLoop["obj"]->data_loop_plural;
            $loopTbl  = $GLOBALS["SL"]->closestLoop["obj"]->data_loop_table;
            // auto-generate new record in the standard way
            $newFld = $newVal = '';
            if (isset($GLOBALS["SL"]->closestLoop["obj"]->data_loop_tree)) {
                $GLOBALS["SL"]->closestLoop["obj"]->loadLoopConds();
            }
            if (sizeof($GLOBALS["SL"]->closestLoop["obj"]->conds) > 0) {
                if ($GLOBALS["SL"]->closestLoop["obj"]->conds 
                    && sizeof($GLOBALS["SL"]->closestLoop["obj"]->conds) > 0) {
                    foreach ($GLOBALS["SL"]->closestLoop["obj"]->conds as $i => $cond) {
                        $fld = $GLOBALS["SL"]->getFullFldNameFromID($cond->cond_field, false);
                        $loopTbl = $GLOBALS["SL"]->closestLoop["obj"]->data_loop_table;
                        if (trim($newFld) == '' 
                            && trim($fld) != '' 
                            && $cond->cond_operator == '{' 
                            && sizeof($cond->condVals) == 1 
                            && $GLOBALS["SL"]->tbl[$cond->cond_table] == $loopTbl) {
                            $newFld = $fld;
                            $newVal = $cond->condVals[0];
                        }
                    }
                }
            }
            $linkages = $this->getRecordLinks($loopTbl, $newFld, $newVal);
            $recObj = $this->newDataRecordInner($loopTbl, $linkages, -3, $skipLinks);
            if ($recObj) {
                $this->loopItemIDs[$loopName][] = $recObj->getKey();
                //$this->refreshDataSets();
                $GLOBALS["SL"]->closestLoop["itemID"] = $recObj->getKey();
                $GLOBALS["SL"]->sessLoops[0]->update([
                    'sess_loop_item_id' => $recObj->getKey()
                ]);
                $logDesc = 'AddingItem #' . $recObj->getKey();
                $loop = $GLOBALS["SL"]->closestLoop["loop"];
                $this->logDataSave($nID, $loopTbl, $recObj->getKey(), $logDesc, $loop);
                return $recObj->getKey();
            }
        }
        return -3;
    }
    
    public function startTmpDataBranch($tbl, $itemID = -3, $findItemID = true)
    {
        $foundBranch = false;
        for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
            if ($this->dataBranches[$i]["branch"] == $tbl) {
                $foundBranch = true;
                if (intVal($this->dataBranches[$i]["itemID"]) <= 0 
                    && intVal($itemID) > 0) {
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
            return [ -3, -3 ];
        }
        if ($tbl == $this->coreTbl) {
            return [0, $this->coreID];
        }
        $itemID = $itemInd = -3;
        $tblNew = $this->isCheckboxHelperTable($tbl);
        for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
            if (intVal($itemID) <= 0 && isset($this->dataBranches[$i])) {
                $brch = $this->dataBranches[$i];
                list($itemInd, $itemID) = $this->currSessDataPosBranch($tblNew, $brch);
                if (intVal($itemID) > 0) {
                    return [ $itemInd, $itemID ];
                }
            }
        }
        for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
            if (intVal($itemID) <= 0 && isset($this->dataBranches[$i])) {
                $brch = $this->dataBranches[$i];
                list($itemInd, $itemID) = $this->currSessDataPosBranch($tbl, $brch);
            }
        }
        if (intVal($itemID) <= 0 
            && !$hasParManip 
            && trim($GLOBALS["SL"]->currCyc["res"][1]) == '') {
            $itemID = $this->sessChildIDFromParent($tbl);
            if ($itemID > 0) {
                $itemInd = $this->getRowInd($tbl, $itemID);
                for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
                    if ($this->dataBranches[$i]["branch"] == $tbl 
                        && $this->dataBranches[$i]["loop"] == '') {
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
         // } elseif (isset($this->dataSets[$tbl]) && isset($this->dataSets[$tbl][0])) {
         //     $itemID = $this->dataSets[$tbl][0]->getKey(); 
            }
        }
        $itemInd = $this->getRowInd($tbl, $itemID);
        return [ $itemInd, $itemID ];
    }
    
    public function currSessDataPosBranchOnly($tbl)
    {
        $itemID = $itemInd = 0;
        $tbl = $this->isCheckboxHelperTable($tbl);
        for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
            if ($itemID <= 0) {
                $brch = $this->dataBranches[$i];
                list($itemInd, $itemID) = $this->currSessDataPosBranch($tbl, $brch);
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
        if (in_array($nID, $this->checkboxNodes) 
            && $GLOBALS["SL"]->isFldCheckboxHelper($fld)) {
            $tblFld = $tbl . '-' . $fld;
            $this->helpInfo[$tblFld] = $this->getCheckboxHelperInfo($tbl, $fld);
            if ($this->helpInfo[$tblFld]["link"] 
                && isset($this->helpInfo[$tblFld]["link"]->data_help_value_field)) {
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
        } elseif ($action == 'update' 
            && $fld != ($GLOBALS["SL"]->tblAbbr[$tbl] . 'id')) {
            $this->logDataSave($nID, $tbl, $itemID, $fld, $newVal);
            if (trim($newVal) == '' 
                && in_array($GLOBALS["SL"]->fldTypes[$tbl][$fld], ['INT', 'DOUBLE'])) {
                $newVal = null;
            } elseif ($GLOBALS["SL"]->fldTypes[$tbl][$fld] == 'INT') {
                $newVal = intVal($newVal);
            } elseif ($GLOBALS["SL"]->fldTypes[$tbl][$fld] == 'DOUBLE') {
                $newVal = floatval($newVal);
            } else {
                $newVal = strip_tags($newVal);
            }
            if (isset($this->dataSets[$tbl]) 
                && isset($this->dataSets[$tbl][$itemInd])) {
                if ($fld != $GLOBALS["SL"]->tblAbbr[$tbl] . 'id') {
                    $this->dataSets[$tbl][$itemInd]->{ $fld } = $newVal;
                    $this->dataSets[$tbl][$itemInd]->save();
                }
            } else {
                $GLOBALS["errors"] .= 'Couldn\'t find dataSets[' 
                    . $tbl . '][' . $itemInd . '] for ' . $fld . '<br />';
            }
        }
        return $newVal;
    }
    
    public function currSessDataCheckbox($nID, $tbl, $fld = '', $action = 'get', $newVals = [], $curr = [], 
        $itemInd = -3, $itemID = -3)
    {
        $tblFld = $tbl . '-' . $fld;
        $this->helpInfo[$tblFld] = $this->getCheckboxHelperInfo($tbl, $fld);
        if (!$this->helpInfo[$tblFld]["link"] 
            || !isset($this->helpInfo[$tblFld]["link"]->data_help_value_field)) {
            $v = ';' . implode(';;', $newVals) . ';';
            return $this->currSessData($nID, $tbl, $fld, $action, $v, false, $itemInd, $itemID);
        }
        if ($action == 'get') {
            if (sizeof($this->helpInfo[$tblFld]["pastVals"]) > 0) {
                return ';' . implode(';;', $this->helpInfo[$tblFld]["pastVals"]) . ';';
            }
            return '';
        } elseif ($action == 'update') {
            $parentID = $this->helpInfo[$tblFld]["parentID"];
            $this->logDataSave($nID, $tbl, $parentID, $fld, $newVals);
            // check for newly submitted responses...
            if (is_array($newVals) && sizeof($newVals) > 0) {
                foreach ($newVals as $i => $val) {
                    if (!in_array($val, $this->helpInfo[$tblFld]["pastVals"]) 
                        && isset($this->helpInfo[$tblFld]["link"]->data_help_table)) {
                        if ($this->helpInfo[$tblFld]["parentID"] <= 0 
                            && $this->helpInfo[$tblFld]["link"]->data_help_parent_table == 'users') {
                            $this->helpInfo[$tblFld]["parentID"] = ((Auth::user() && Auth::user()->id)
                                ? Auth::user()->id : -3);
                        }
                        if ($val && trim($val) != '') {
                            $model = trim($GLOBALS["SL"]->modelPath(
                                $this->helpInfo[$tblFld]["link"]->data_help_table
                            ));
                            if ($model != '') {
                                eval("\$newObj = new " . $model . ";");
                                $newObj->save();
                                $newObj->update([
                                    $this->helpInfo[$tblFld]["link"]->data_help_key_field 
                                        => $this->helpInfo[$tblFld]["parentID"],
                                    $this->helpInfo[$tblFld]["link"]->data_help_value_field 
                                        => strip_tags($val)
                                ]);
                                $setInd = $this->initDataSet($tbl);
                                $this->dataSets[$tbl][$setInd] = $newObj;
                                $this->id2ind[$tbl][$newObj->getKey()] = $setInd;
                            }
                        }
                    }
                }
            }
            if (isset($curr->responses) && sizeof($curr->responses) > 0) {
                foreach ($curr->responses as $j => $res) {
                    if ((!is_array($newVals) || !in_array($res->node_res_value, $newVals)) 
                        && isset($this->helpInfo[$tblFld]["pastValToID"][$res->node_res_value])) {
                        $helpTbl = $this->helpInfo[$tblFld]["link"]->data_help_table;
                        $helpVal = $this->helpInfo[$tblFld]["pastValToID"][$res->node_res_value];
                        $this->deleteDataItem($nID, $helpTbl, $helpVal);
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
            if (isset($GLOBALS["SL"]->dataHelpers) 
                && sizeof($GLOBALS["SL"]->dataHelpers) > 0) {
                foreach ($GLOBALS["SL"]->dataHelpers as $helper) {
                    if ($helper->data_help_table == $tbl && $helper->data_help_value_field == $fld) {
                        $this->helpInfo[$tblFld]["link"] = $helper;
                        //BranchOnly
                        list($parentInd, $this->helpInfo[$tblFld]["parentID"]) 
                            = $this->currSessDataPos($helper->data_help_parent_table);
                        $this->helpInfo[$tblFld]["pastObjs"] = $this->dataWhere(
                            $helper->data_help_table, 
                            $helper->data_help_key_field, 
                            $this->helpInfo[$tblFld]["parentID"]
                        );
                        if ($this->helpInfo[$tblFld]["pastObjs"] 
                            && sizeof($this->helpInfo[$tblFld]["pastObjs"]) > 0) {
                            foreach ($this->helpInfo[$tblFld]["pastObjs"] as $obj) {
                                $val = $obj->{ $helper->data_help_value_field };
                                $this->helpInfo[$tblFld]["pastVals"][] = $val;
                                $this->helpInfo[$tblFld]["pastValToID"][$val] = $obj->getKey();
                            }
                        }
                    }
                }
            }
        //}
        return $this->helpInfo[$tblFld];
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
        $qryWheres = "where('node_save_session', \$sessID)->where('node_save_node', ".$nID.")->";
        if (trim($tbl) != '' && trim($fld) != '') {
            $qryWheres .= "where('node_save_tbl_fld', '" . $tbl . ":" . $fld 
                . ((trim($set) != '') ? "[" . $set . "]" : "") . "')->";
        }
        if (isset($GLOBALS["SL"]->closestLoop["itemID"]) 
            && intVal($GLOBALS["SL"]->closestLoop["itemID"]) > 0) {
            $qryWheres .= "where('node_save_loop_item_id', " . $GLOBALS["SL"]->closestLoop["itemID"] . ")->";
        }
        eval("\$nodeSave = App\\Models\\SLNodeSaves::" . $qryWheres 
            . "orderBy('created_at', 'desc')->first();"); 
        if ($nodeSave && isset($nodeSave->node_save_new_val)) {
            return $nodeSave->node_save_new_val;
        }
        return '';
    }
    
    public function parseCondition($cond = [], $recObj = [], $nID = -3)
    {
        $passed = true;
        if ($cond && isset($cond->cond_database) && $cond->cond_operator != 'CUSTOM') {
            $cond->loadVals();
            $loopName = '';
            if (intVal($cond->cond_loop) > 0 
                && isset($GLOBALS["SL"]->dataLoopNames[$cond->cond_loop])) {
                $loopName = $GLOBALS["SL"]->dataLoopNames[$cond->cond_loop];
            }
            if (intVal($cond->cond_table) <= 0 
                && trim($loopName) != '' 
                && isset($GLOBALS["SL"]->dataLoops[$loopName])) {
                $tblName = $GLOBALS["SL"]->dataLoops[$loopName]->data_loop_table;
            } else {
                $tblName = $GLOBALS["SL"]->tbl[$cond->cond_table];
            }
//if ($tbl != $setTbl) list($setTbl, $setSet, $loopItemID) = $this->getDataSetTblTranslate($set, $tbl, $loopItemID);
            if ($cond->cond_operator == 'EXISTS=') {
                if (!isset($this->dataSets[$tblName]) || (intVal($cond->cond_loop) > 0 
                    && !isset($this->loopItemIDs[$loopName]))) {
                    if (intVal($cond->cond_oper_deet) == 0) {
                        $passed = true;
                    } else {
                        $passed = false;
                    }
                } else {
                    $existCnt = sizeof($this->dataSets[$tblName]);
                    if (intVal($cond->cond_loop) > 0) {
                        $existCnt = sizeof($this->loopItemIDs[$loopName]);
                    }
                    $passed = ($existCnt == intVal($cond->cond_oper_deet));
                }
            } elseif ($cond->cond_operator == 'EXISTS>') {
                if (!isset($this->dataSets[$tblName]) 
                    || (intVal($cond->cond_loop) > 0 
                        && !isset($this->loopItemIDs[$loopName]))) {
                    $passed = false;
                } else {
                    $existCnt = sizeof($this->dataSets[$tblName]);
                    if (intVal($cond->cond_loop) > 0) {
                        $existCnt = sizeof($this->loopItemIDs[$loopName]);
                    }
                    if (intVal($cond->cond_oper_deet) == 0) {
                        $passed = ($existCnt > 0);
                    } elseif ($cond->cond_oper_deet > 0) {
                        $passed = ($existCnt > intVal($cond->cond_oper_deet));
                    } elseif ($cond->cond_oper_deet < 0) {
                        $passed = ($existCnt < ((-1)*intVal($cond->cond_oper_deet)));
                    }
                }
            } elseif (intVal($cond->cond_field) > 0) {
                $fldName = $GLOBALS["SL"]->getFullFldNameFromID($cond->cond_field, false);
                if ($cond->cond_operator == '{{') { // find any match in any row for this table
                    $passed = false;
                    if (isset($this->dataSets[$tblName]) 
                        && sizeof($this->dataSets[$tblName]) > 0) {
                        foreach ($this->dataSets[$tblName] as $ind => $row) {
                            if (isset($row->{ $fldName }) 
                                && trim($row->{ $fldName }) != '' 
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
                        if ($cond->cond_operator == '{') {
                            $passed = (in_array($currSessData, $cond->condVals));
                        } elseif ($cond->cond_operator == '}') {
                            $passed = (!in_array($currSessData, $cond->condVals));
                        }
                    } else {
                        if ($cond->cond_operator == '{') {
                            $passed = false;
                        } elseif ($cond->cond_operator == '}') {
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
            if (isset($GLOBALS["SL"]->dataHelpers) 
                && sizeof($GLOBALS["SL"]->dataHelpers) > 0) {
                foreach ($GLOBALS["SL"]->dataHelpers as $helper) {
                    if ($helper->data_help_table == $helperTbl 
                        && trim($helper->data_help_value_field) != '') {
                        $tbl = $helper->data_help_parent_table;
                    }
                }
            }
        }
        return $tbl;
    }
    
    public function updateZipInfo($zipIn = '', $tbl = '', $fldState = '', $fldCounty = '', $fldAshrae = '', $fldCountry = '', $setInd = 0)
    {
        if (trim($zipIn) == '' || trim($tbl) == '') {
            return false;
        }
        $GLOBALS["SL"]->loadStates();
        $zipRow = $GLOBALS["SL"]->states->getZipRow($zipIn);
        if ($zipRow && isset($zipRow->zip_zip) && isset($this->dataSets[$tbl])) {
            if (trim($fldState) != '' && isset($zipRow->zip_state)) {
                $this->dataSets[$tbl][$setInd]->update([ $fldState  => $zipRow->zip_state  ]);
            }
            if (trim($fldCounty) != '' && isset($zipRow->zip_county)) {
                $this->dataSets[$tbl][$setInd]->update([ $fldCounty => $zipRow->zip_county ]);
            }
            if (trim($fldCountry) != '' && isset($zipRow->zip_country)) {
                $this->dataSets[$tbl][$setInd]->update([ $fldCountry => $zipRow->zip_country ]);
            }
            if (trim($fldAshrae) != '') {
                $ashrae = $GLOBALS["SL"]->states->getAshrae($zipRow);
                $this->dataSets[$tbl][$setInd]->update([ $fldAshrae => $ashrae ]);
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
                $url .= (($i > 1) ? '-' : '') . $this->dataBranches[$i]["branch"] 
                    . '-' . $this->dataBranches[$i]["itemID"];
            }
        }
        return $url;
    }
    
    public function loadDataBranchFromUrl($url)
    {
        $badBranch = false;
        $branches = ((trim($url) != '' && strpos($url, '-') !== false) 
            ? explode('-', $url) : []);
        if (sizeof($branches) > 0) {
            for ($i = 0; $i < sizeof($branches); $i+=2) {
                if (!$badBranch) {
                    $chk = $this->getRowById($branches[$i], $branches[$i+1]);
                    if ($chk && isset($chk->created_at)) {
                        $this->dataBranches[] = [
                            "branch" => $branches[$i],
                            "loop"   => '',
                            "itemID" => $branches[$i+1]
                        ];
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
                        $abbr = $GLOBALS["SL"]->tblAbbr[$tblTo];
                        $this->dataSets[$tblTo][$ind]->{ $abbr . $fld } = $rowFrom->{ $fld };
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
        $params = $abField = '';
        if (isset($this->dataSets[$this->coreTbl]) && sizeof($this->dataSets[$this->coreTbl]) > 0) {
            $abField = $GLOBALS["SL"]->tblAbbr[$this->coreTbl] . 'version_ab';
            if (isset($this->dataSets[$this->coreTbl][0]->{ $abField })) {
                $params = $this->dataSets[$this->coreTbl][0]->{ $abField };
            }
        }
        $this->testsAB->addParamsAB($params);
        if ($GLOBALS["SL"]->REQ->has('ab') && trim($GLOBALS["SL"]->REQ->get('ab') != '')) {
            $this->testsAB->addParamsAB(trim($GLOBALS["SL"]->REQ->get('ab')));
        }
        if ($abField != '') {
            $this->dataSets[$this->coreTbl][0]->update([ $abField => $this->testsAB->printParams() ]);
        }
        return true;
    }
    
}
