<?php
/**
  * SurvData is a critical class with loads all the details of a survey's core record
  * according to the database design, and it's settings for a given survey.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use DB;
use Auth;
use App\Models\SLFields;
use RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv;
use RockHopSoft\Survloop\Controllers\Tree\SurvDataConditions;

class SurvData extends SurvDataConditions
{
    
    public function loadCore($coreTbl, $coreID = -3, $checkboxNodes = [], $isBigSurvloop = [], $dataBranches = [])
    {
        $this->setCoreID($coreTbl, $coreID);
        if (sizeof($dataBranches) > 0) {
            $this->dataBranches = $dataBranches;
        }
        $this->checkboxNodes = $checkboxNodes;
        $this->refreshDataSets($isBigSurvloop);
        $this->loadSessTestsAB();
        $this->loaded = true;
        return true;
    }
    
    public function refreshDataSets($isBigSurvloop = [])
    {
        $this->dataSets = $this->id2ind = $this->kidMap 
            = $this->helpInfo = $this->dataSetsSubbed = [];
        $this->loadData($this->coreTbl, $this->coreID);
// if (Auth::user() && Auth::user()->id) $this->loadData('users', Auth::user()->id);
// check for data needed for root data loop which isn't connected to the core record
        if (sizeof($isBigSurvloop) > 0 && trim($isBigSurvloop[0]) != '') {
            $model = trim($GLOBALS["SL"]->modelPath($isBigSurvloop[0]));
            if ($model != '') {
                eval("\$rows = " . $model . "::orderBy('" 
                    . $isBigSurvloop[1] . "', '" 
                    . $isBigSurvloop[2] . "')->get();");
                if ($rows->isNotEmpty()) {
                    foreach ($rows as $row) {
                        $this->loadData($isBigSurvloop[0], $row->getKey(), $row);
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
                
//echo 'loadData( dataSubsets:<pre>'; print_r($GLOBALS["SL"]->dataSubsets); echo '</pre>dataLoops:<pre>'; print_r($GLOBALS["SL"]->dataLoops); echo '</pre>dataHelpers:<pre>'; print_r($GLOBALS["SL"]->dataHelpers); echo '</pre>dataLinksOn:<pre>'; print_r($GLOBALS["SL"]->dataLinksOn); echo '</pre><pre>'; print_r($this->dataSets); echo '</pre>'; exit;
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
            $foundLink = -1;
            foreach ($linkages["incoming"] as $l => $link) {
                if ($foundLink < 0 && $this->hasDataBranchTbl($link[0])) {
                    $foundLink = $l;
                }
            }
            if ($foundLink >= 0) {
                $link = $linkages["incoming"][$foundLink];
                $recObj = $this->checkNewDataRecGetLinkObj($tbl, $link);
            } else {
                foreach ($linkages["incoming"] as $link) {
                    if ($recObj === NULL) {
                        $recObj = $this->checkNewDataRecGetLinkObj($tbl, $link);
                    }
                }
            }
        }
        return $recObj;
    }
    
    private function checkNewDataRecGetLinkObj($tbl, $link)
    {
        $recObj = NULL;
        $incomingInd = $this->getRowInd($link[0], intVal($link[2]));
        if (isset($this->dataSets[$link[0]][$incomingInd]->{ $link[1] })) {
            $linkID = intVal($this->dataSets[$link[0]][$incomingInd]->{ $link[1] });
            if ($linkID > 0) {
                $recInd = $this->getRowInd($tbl, $linkID);
                if ($recInd >= 0) {
                    $recObj = $this->dataSets[$tbl][$recInd];
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
    
    public function processSubObjs($tbl1, $tbl1ID, $tbl1Ind, $tbl2, $subObjs)
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
                if ($this->loopItemsNextID <= 0 
                    && !in_array($id, $this->loopItemIDsDone)) {
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

    public function startLoopDataBranch($tbl, $itemID = -3, $loopName = '')
    {
        $foundBranch = false;
        for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
            if (!$foundBranch 
                && isset($this->dataBranches[$i]) 
                && $this->dataBranches[$i]["branch"] == $tbl) {
                if ($this->dataBranches[$i]["itemID"] <= 0 && $itemID > 0) {
                    $this->dataBranches[$i]["itemID"] = $itemID;
                    $this->dataBranches[$i]["loop"] = $loopName;
                }
                $foundBranch = true;
            }
        }
        if (!$foundBranch) {
            $this->dataBranches[] = [
                "branch" => $tbl,
                "loop"   => $loopName,
                "itemID" => $itemID
            ];
        }
        return true;
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
                if (intVal($itemID) <= 0) {
                    $itemID = $this->startTmpDataBranchChkLoop($tbl);
                }
            }
            $this->dataBranches[] = [
                "branch" => $tbl,
                "loop"   => '',
                "itemID" => $itemID
            ];
        }
        return true;
    }
    
    public function startTmpDataBranchChkLoop($tbl)
    {
        if ($GLOBALS["SL"]->REQ->has('loopItem')
            && intVal($GLOBALS["SL"]->REQ->has('loopItem')) > 0
            && isset($this->dataSets[$tbl])
            && sizeof($this->dataSets[$tbl]) > 0) {
            $found = false;
            $itemID = intVal($GLOBALS["SL"]->REQ->get('loopItem'));
            foreach ($this->dataSets[$tbl] as $item) {
                if ($item->getKey() == $itemID) {
                    $found = true;
                }
            }
            if ($found) {
                return $itemID;
            }
        }
        return -3;
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

    public function currSessDataPos($tbl, $hasParManip = false, $nID = 0)
    {
        if (trim($tbl) == '') {
            return [ -3, -3 ];
        }
        if ($tbl == $this->coreTbl) {
            return [0, $this->coreID];
        }
        $itemID = $itemInd = -3;
        for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
            if (intVal($itemID) <= 0 && isset($this->dataBranches[$i])) {
                $brch = $this->dataBranches[$i];
                list($itemInd, $itemID) = $this->currSessDataPosBranch($tbl, $brch);
            }
        }
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
            if (intVal($branch["itemID"]) > 0) {
                $itemID = $branch["itemID"];
            } elseif (trim($branch["loop"]) != '') {
                $itemID = $GLOBALS["SL"]->getSessLoopID($branch["loop"]);
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
    
    public function hasDataBranchTbl($tbl)
    {
        for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--) {
            if ($this->dataBranches[$i]["branch"] == $tbl) {
                return true;
            }
        }
        return false;
    }
     
    public function currSessDataTblFld($nID, $tbl, $fld = '', $action = 'get', $newVal = null) 
    //, $hasParManip = false, $itemInd = -3, $itemID = -3)
    {
        $curr = new TreeNodeSurv($nID);
        $curr->fillNodeRow();
        $curr->nID = $nID;
        $curr->tbl = $tbl;
        $curr->fld = $fld;
        return $this->currSessData($curr, $action, $newVal);
    }
    
    // Here we're trying to find the closest relative within 
    // current tree navigation to the table and field in question. 
    // One of the most complex and important processes in the system.
    public function currSessData(&$curr, $action = 'get', $newVal = null)
        // ($nID, $tbl, $fld = '', $action = 'get', $newVal = null, $hasParManip = false, 
        // $itemInd = -3, $itemID = -3)
    {
        if (!isset($curr->tbl) 
            || trim($curr->tbl) == '' 
            || !isset($curr->fld) 
            || trim($curr->fld) == '' 
            || !$this->loaded) {
            return '';
        }
        if (in_array($curr->nID, $this->checkboxNodes) 
            && $GLOBALS["SL"]->isFldCheckboxHelper($curr->fld)) {
            $tblFld = $curr->tbl . '-' . $curr->fld;
            $this->helpInfo[$tblFld] = $this->getCheckboxHelperInfo($curr->tbl, $curr->fld);
            if ($this->helpInfo[$tblFld]["link"] 
                && isset($this->helpInfo[$tblFld]["link"]->data_help_value_field)) {
                return $this->currSessDataCheckbox($curr);
            }
        }
        if ($curr->itemInd < 0 || $curr->itemID <= 0) {
            list($curr->itemInd, $curr->itemID) = $this->currSessDataPos(
                $curr->tbl, 
                $curr->hasParManip
            );
        }
        if ($curr->itemInd < 0 || $curr->itemID <= 0) {
            return '';
        }
        $page = 'Node Save currSessData(' . $curr->nIDtxt . ')';
        if ($action == 'get') {
            if ($this->dataFieldExists($curr->tbl, $curr->fld, $curr->itemInd)) {
//if ($curr->nID == 1628) { echo 'currSessData A - <pre>'; print_r($this->currSessDataFieldVal($curr)); echo '</pre>'; exit; }
                return $this->currSessDataFieldVal($curr);
            }
        } elseif ($action == 'update' 
            && $curr->fld != ($GLOBALS["SL"]->tblAbbr[$curr->tbl] . 'id')) {
            $this->logDataSave($curr->nID, $curr->tbl, $curr->itemID, $curr->fld, $newVal);
            $newVal = $this->currSessDataFormatNewVal($newVal, $curr->tbl, $curr->fld);
            if (isset($this->dataSets[$curr->tbl]) 
                && isset($this->dataSets[$curr->tbl][$curr->itemInd])) {
                if ($curr->fld != $GLOBALS["SL"]->tblAbbr[$curr->tbl] . 'id') {
                    try {
                        $this->dataSets[$curr->tbl][$curr->itemInd]->{ $curr->fld } = $newVal;
                        $this->dataSets[$curr->tbl][$curr->itemInd]->save();
                    }
                    catch (Exception $e) {
                        $err = 'Error 1! tbl: ' . $curr->tbl . ', ind: ' . $curr->itemInd 
                            . ', fld: ' . $curr->fld . ', val: ' . $newVal;
                        $GLOBALS["SL"]->logError($err, $page);
                    }
                }
            } else {
                $err = 'Error 2! tbl: ' . $curr->tbl . ', ind: ' . $curr->itemInd 
                    . ', fld: ' . $curr->fld . ', val: ' . $newVal;
                $GLOBALS["SL"]->logError($err, $page);
            }
        }
        return $newVal;
    }
    
    private function currSessDataFieldVal(&$curr)
    {
        $ret = $this->dataSets[$curr->tbl][$curr->itemInd]->{ $curr->fld };
        if (isset($GLOBALS["SL"]->fldTypes[$curr->tbl])
            && isset($GLOBALS["SL"]->fldTypes[$curr->tbl][$curr->fld])) {
            if ($GLOBALS["SL"]->fldTypes[$curr->tbl][$curr->fld] == 'DATETIME') {
                if (trim($ret) != '0000-00-00 00:00:00'
                    && trim($ret) != '1970-01-01 00:00:00') {
                    return $ret;
                }
                return '';
            } elseif ($GLOBALS["SL"]->fldTypes[$curr->tbl][$curr->fld] == 'DATE') {
                if (trim($ret) != '0000-00-00' && trim($ret) != '1970-01-01') {
                    return $ret;
                }
                return '';
            }
        }
        return $ret;
    }

    public function isCheckboxHelperTable($helperTbl = '')
    {
        $tbl = $helperTbl;
        if (trim($helperTbl) != '' 
            && trim($GLOBALS["SL"]->currCyc["res"][1]) == '') {
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
    
    public function currSessDataCheckbox(&$curr, $action = 'get', $newVals = [])
    {
        $tblFld = $curr->tbl . '-' . $curr->fld;
        $this->helpInfo[$tblFld] = $this->getCheckboxHelperInfo($curr->tbl, $curr->fld);
        if (!$this->helpInfo[$tblFld]["link"] 
            || !isset($this->helpInfo[$tblFld]["link"]->data_help_value_field)) {
            $v = ';' . implode(';;', $newVals) . ';';
            return $this->currSessData($curr, $action, $v);
        }
        if ($action == 'get') {
            if (sizeof($this->helpInfo[$tblFld]["pastVals"]) > 0) {
//if ($curr->nID == 2835) { echo '<pre>'; print_r($this->helpInfo[$tblFld]); print_r($curr); echo '</pre>'; exit; }
                return ';' . implode(';;', $this->helpInfo[$tblFld]["pastVals"]) . ';';
            }
            return '';
        } elseif ($action == 'update') {
            $this->currSessDataCheckboxUpdate($curr, $newVals);
        }
        return '';
    }
    
    public function currSessDataCheckboxUpdate(&$curr, $newVals = [])
    {
        $tblFld = $curr->tbl . '-' . $curr->fld;
        $parentID = $this->helpInfo[$tblFld]["parentID"];
        $this->logDataSave($curr->nID, $curr->tbl, $parentID, $curr->fld, $newVals);
        // check for newly submitted responses...
        if (is_array($newVals) && sizeof($newVals) > 0) {
            foreach ($newVals as $i => $val) {
                if (!in_array($val, $this->helpInfo[$tblFld]["pastVals"]) 
                    && isset($this->helpInfo[$tblFld]["link"]->data_help_table)) {
                    $this->currSessDataCheckboxAdd($curr, $val);
                }
            }
        }
        if (isset($curr->responses) && sizeof($curr->responses) > 0) {
            foreach ($curr->responses as $j => $res) {
                if ((!is_array($newVals) || !in_array($res->node_res_value, $newVals)) 
                    && isset($this->helpInfo[$tblFld]["pastValToID"][$res->node_res_value])) {
                    $helpTbl = $this->helpInfo[$tblFld]["link"]->data_help_table;
                    $helpVal = $this->helpInfo[$tblFld]["pastValToID"][$res->node_res_value];
                    $this->deleteDataItem($curr->nID, $helpTbl, $helpVal);
                }
            }
        }
        return true;
    }
    
    public function currSessDataCheckboxAdd($curr, $val = '')
    {
        $tblFld = $curr->tbl . '-' . $curr->fld;
        if ($this->helpInfo[$tblFld]["parentID"] <= 0 
            && $this->helpInfo[$tblFld]["link"]->data_help_parent_table == 'users') {
            $this->helpInfo[$tblFld]["parentID"] = -3;
            if (Auth::user() && Auth::user()->id) {
                $this->helpInfo[$tblFld]["parentID"] = Auth::user()->id;
            }
        }
        if ($val && trim($val) != '') {
            $model = $this->helpInfo[$tblFld]["link"]->data_help_table;
            $model = trim($GLOBALS["SL"]->modelPath($model));
            if ($model != '') {
                eval("\$newObj = new " . $model . ";");
                $newObj->save();
                $keyFld = $this->helpInfo[$tblFld]["link"]->data_help_key_field;
                $valFld = $this->helpInfo[$tblFld]["link"]->data_help_value_field;
                $newObj->update([
                    $keyFld => $this->helpInfo[$tblFld]["parentID"],
                    $valFld => strip_tags($val)
                ]);
                $setInd = $this->initDataSet($curr->tbl);
                $this->dataSets[$curr->tbl][$setInd] = $newObj;
                $this->id2ind[$curr->tbl][$newObj->getKey()] = $setInd;
            }
        }
        return true;
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
    
    // Here we're trying to find the closest relative within current tree navigation to the table and field in question. 
    public function currSessDataFormatNewVal($newVal, $tbl, $fld = '')
    {
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
        return $newVal;
    }
    
    
    public function getDataBranchUrl()
    {
        $url = '&branch=';
        if (sizeof($this->dataBranches) > 1) {
            for ($i = 1; $i < sizeof($this->dataBranches); $i++) {
                $url .= (($i > 1) ? '-' : '') 
                    . $this->dataBranches[$i]["branch"] . '-' 
                    . $this->dataBranches[$i]["itemID"];
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
    

}
