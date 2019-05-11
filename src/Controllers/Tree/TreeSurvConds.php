<?php
/**
  * TreeSurvConds is a mid-level class focused on checking node conditions.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers\Tree;

use Storage\App\Models\SLConditions;
use Storage\App\Models\SLConditionsArticles;
use SurvLoop\Controllers\Tree\TreeSurvAPI;

class TreeSurvConds extends TreeSurvAPI
{
    protected function checkNodeConditions($nID)
    {
        if (!isset($this->allNodes[$nID])) {
            return false;
        }
        $this->allNodes[$nID]->fillNodeRow();
        return $this->parseConditions($this->allNodes[$nID]->conds, [], $nID);
    }
    
    protected function checkNodeConditionsCustom($nID, $condition = '')
    {
        return -1;
    }
    
    // Setting the second parameter to false alternatively returns an array of individual conditions
    public function parseConditions($conds = [], $recObj = [], $nID = -3)
    {
        $retTF = true;
        if (sizeof($conds) > 0) {
            foreach ($conds as $i => $cond) {
                if ($retTF) {
                    if ($cond && isset($cond->CondDatabase) && $cond->CondOperator == 'CUSTOM') {
                        if (!$this->parseCondPreInstalled($cond)) {
                            $retTF = false;
                        }
                    } elseif ($cond->CondOperator == 'AB TEST') {
                        if (!$this->checkActiveTestAB($cond)) {
                            $retTF = false;
                        }
                    } elseif ($cond->CondOperator == 'URL-PARAM') {
                        if (trim($cond->CondOperDeet) == '') {
                            $retTF = false;
                        } elseif (!$GLOBALS["SL"]->REQ->has($cond->CondOperDeet) 
                            || trim($GLOBALS["SL"]->REQ->get($cond->CondOperDeet)) 
                                != trim($cond->condFldResponses["vals"][0][1])) {
                            $retTF = false;
                        }
                    } elseif ($cond->CondOperator == 'COMPLEX') {
                        $cond->loadVals();
                        if (isset($cond->condVals) && sizeof($cond->condVals) > 0) {
                            foreach ($cond->condVals as $i => $val) {
                                if ($val > 0) {
                                    $subCond = SLConditions::find($val);
                                    if ($subCond && isset($subCond->CondOperator)) {
                                        if (!$this->sessData->parseCondition($subCond, $recObj, $nID)) {
                                            $retTF = false;
                                        }
                                    }
                                } else { // opposite
                                    $subCond = SLConditions::find(-1*$val);
                                    if ($subCond && isset($subCond->CondOperator)) {
                                        if ($this->sessData->parseCondition($subCond, $recObj, $nID)) {
                                            $retTF = false;
                                        }
                                    }
                                }
                            }
                        }
                    } elseif (!$this->sessData->parseCondition($cond, $recObj, $nID)) {
                        $retTF = false; 
                    }
                    $custom = $this->checkNodeConditionsCustom($nID, trim($cond->CondTag));
                    if ($custom == 0) {
                        $retTF = false;
                    } elseif ($custom == 1) {
                        $retTF = true;
                    }
                    // This is where all the condition-inversion is applied
                    if ($nID > 0 && isset($GLOBALS["SL"]->nodeCondInvert[$nID]) 
                        && isset($GLOBALS["SL"]->nodeCondInvert[$nID][$cond->CondID])) {
                        $retTF = !$retTF;
                    }
                }
            }
        }
        return $retTF;
    }
    
    public function parseCondPreInstalled($cond = NULL)
    {
        $retTF = true;
        if ($cond && isset($cond->CondTag)) {
            if (trim($cond->CondTag) == '#NodeDisabled') {
                $retTF = false;
            } elseif (trim($cond->CondTag) == '#IsLoggedIn') {
                if ($this->v["uID"] <= 0) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsNotLoggedIn') {
                if ($this->v["uID"] > 0) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsAdmin') {
                if ($this->v["uID"] <= 0 || !$this->v["user"]->hasRole('administrator')) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsNotAdmin') {
                if ($this->v["uID"] > 0 && $this->v["user"]->hasRole('administrator')) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsStaff') {
                if ($this->v["uID"] <= 0 || !$this->v["user"]->hasRole('staff')) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsStaffOrAdmin') {
                if ($this->v["uID"] <= 0 || !$this->v["user"]->hasRole('administrator|staff')) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsPartner') {
                if ($this->v["uID"] <= 0 || !$this->v["user"]->hasRole('partner')) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsVolunteer') {
                if ($this->v["uID"] <= 0 || !$this->v["user"]->hasRole('volunteer')) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsBrancher') {
                if ($this->v["uID"] <= 0 || !$this->v["user"]->hasRole('databaser')) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsOwner') {
                if ($this->v["uID"] <= 0 || !$this->v["isOwner"]) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsProfileOwner') {
                if ($this->v["uID"] <= 0 || !isset($this->v["profileUser"]) || !$this->v["profileUser"]
                    || !isset($this->v["profileUser"]->id) || $this->v["uID"] != $this->v["profileUser"]->id) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsPrintable') {
                if (!$GLOBALS["SL"]->REQ->has('print') && (!isset($GLOBALS["SL"]->x["pageView"]) 
                    || !in_array($GLOBALS["SL"]->x["pageView"], ['pdf', 'full-pdf']))) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsPrintInFrame') {
                if (!$GLOBALS["SL"]->REQ->has('ajax') && !$GLOBALS["SL"]->REQ->has('frame') 
                    && !$GLOBALS["SL"]->REQ->has('wdg')) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#TestLink') {
                if (!$GLOBALS["SL"]->REQ->has('test') && intVal($GLOBALS["SL"]->REQ->get('test')) < 1) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsDataPermPublic') {
                if ($GLOBALS["SL"]->x["dataPerms"] != 'public') {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsDataPermPrivate') {
                if ($GLOBALS["SL"]->x["dataPerms"] != 'private') {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsDataPermSensitive') {
                if ($GLOBALS["SL"]->x["dataPerms"] != 'sensitive') {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#IsDataPermInternal') {
                if ($GLOBALS["SL"]->x["dataPerms"] != 'internal') {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#HasTokenDialogue') {
                if (!$this->pageLoadHasToken()) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#EmailVerified') {
                if ($this->v["uID"] <= 0 || !$this->v["user"]->hasVerifiedEmail()) {
                    $retTF = false;
                }
            } elseif (trim($cond->CondTag) == '#NextButton') {
                if (!isset($this->REQstep) || $this->REQstep != 'next') {
                    $retTF = false;
                }
            //} elseif (trim($cond->CondTag) == '#HasUploads') {
            }
        }
        return $retTF;
    }
    
    public function runLoopConditions()
    {
        $this->sessData->loopItemIDs = [];
        if (isset($GLOBALS["SL"]->dataLoops) && sizeof($GLOBALS["SL"]->dataLoops) > 0) {
            $GLOBALS["SL"]->loadLoopConds();
            foreach ($GLOBALS["SL"]->dataLoops as $loopName => $loop) {
                $this->sessData->loopItemIDs[$loop->DataLoopPlural] = $sortable = [];
                if (isset($this->sessData->dataSets[$loop->DataLoopTable]) 
                    && sizeof($this->sessData->dataSets[$loop->DataLoopTable]) > 0) {
                    foreach ($this->sessData->dataSets[$loop->DataLoopTable] as $recObj) {
                        if ($recObj && $this->parseConditions($loop->conds, $recObj)) {
                            $this->sessData->loopItemIDs[$loop->DataLoopPlural][] = $recObj->getKey();
                            if (trim($loop->DataLoopSortFld) != '') {
                                $sortable['' . $recObj->getKey() . ''] = $recObj->{ $loop->DataLoopSortFld };
                            }
                        }
                    }
                }
                if (trim($loop->DataLoopSortFld) != '' && sizeof($sortable) > 0) {
                    $this->sessData->loopItemIDs[$loop->DataLoopPlural] = [];
                    asort($sortable);
                    foreach ($sortable as $id => $ord) {
                        $this->sessData->loopItemIDs[$loop->DataLoopPlural][] = intVal($id);
                    }
                }
            }
        }
        return true;
    }
    
    // Setting the second parameter to false alternatively returns an array of individual conditions
    public function loadRelatedArticles()
    {
        $this->v["articles"] = $artCondIDs = [];
        $this->v["allUrls"] = [ "txt" => [], "vid" => [] ];
        $allArticles = SLConditionsArticles::get();
        if ($allArticles->isNotEmpty()) {
            foreach ($allArticles as $i => $a) {
                $artCondIDs[] = $a->ArticleCondID;
            }
            $allConds = SLConditions::whereIn('CondID', $artCondIDs)->get();
            if ($allConds->isNotEmpty()) {
                foreach ($allConds as $i => $c) {
                    if ($this->parseConditions([$c])) {
                        $artLnks = [];
                        foreach ($allArticles as $i => $a) {
                            if ($a->ArticleCondID == $c->CondID) {
                                $artLnks[] = [$a->ArticleTitle, $a->ArticleURL];
                                $set = ((strpos(strtolower($a->ArticleURL), 'youtube.com') !== false) ? 'vid' : 'txt');
                                $found = false;
                                if (sizeof($this->v["allUrls"][$set]) > 0) {
                                    foreach ($this->v["allUrls"][$set] as $url) {
                                        if ($url[1] == $a->ArticleURL) {
                                            $found = true;
                                        }
                                    }
                                }
                                if (!$found) {
                                    $this->v["allUrls"][$set][] = [$a->ArticleTitle, $a->ArticleURL];
                                }
                            }
                        }
                        $this->v["articles"][] = [$c, $artLnks];
                    }
                }
            }
            return true;
        }
        return false;
    }
    
    public function getPrevOfTypeWithConds($nID, $type = 'Page')
    {
        $nID = $this->getPrevOfType($nID, $type);
        while ($nID > 0) {
            if ($this->checkNodeConditions($nID)) {
                return $nID;
            }
            $nID = $this->getPrevOfType($nID, $type);
        }
        return $nID;
    }
    
    public function checkActiveTestAB($cond = NULL)
    {
        if (!$cond || !isset($cond->CondID) || !$this->sessData->testsAB->checkCond($cond->CondID)) {
            return false;
        }
        return true;
    }
    
}