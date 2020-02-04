<?php
/**
  * TreeSurvConds is a mid-level class focused on checking node conditions.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.1.2
  */
namespace SurvLoop\Controllers\Tree;

use App\Models\SLConditions;
use App\Models\SLConditionsArticles;
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
                    if ($cond && isset($cond->cond_database) 
                        && $cond->cond_operator == 'CUSTOM') {
                        if (!$this->parseCondPreInstalled($cond)) {
                            $retTF = false;
                        }
                    } elseif ($cond->cond_operator == 'AB TEST') {
                        if (!$this->checkActiveTestAB($cond)) {
                            $retTF = false;
                        }
                    } elseif ($cond->cond_operator == 'URL-PARAM') {
                        if (trim($cond->cond_oper_deet) == '') {
                            $retTF = false;
                        } else {
                            $val = trim($cond->condFldResponses["vals"][0][1]);
                            if (!$GLOBALS["SL"]->REQ->has($cond->cond_oper_deet) 
                                || trim($GLOBALS["SL"]->REQ->get($cond->cond_oper_deet)) != $val) {
                                $retTF = false;
                            }
                        }
                    } elseif ($cond->cond_operator == 'COMPLEX') {
                        $cond->loadVals();
                        if (isset($cond->condVals) && sizeof($cond->condVals) > 0) {
                            foreach ($cond->condVals as $i => $val) {
                                if ($val > 0) {
                                    $subCond = SLConditions::find($val);
                                    if ($subCond && isset($subCond->cond_operator)) {
                                        if (!$this->sessData->parseCondition(
                                            $subCond, 
                                            $recObj, 
                                            $nID)) {
                                            $retTF = false;
                                        }
                                    }
                                } else { // opposite
                                    $subCond = SLConditions::find(-1*$val);
                                    if ($subCond && isset($subCond->cond_operator)) {
                                        if ($this->sessData->parseCondition(
                                            $subCond, 
                                            $recObj, 
                                            $nID)) {
                                            $retTF = false;
                                        }
                                    }
                                }
                            }
                        }
                    } elseif (!$this->sessData->parseCondition($cond, $recObj, $nID)) {
                        $retTF = false; 
                    }
                    $custom = $this->checkNodeConditionsCustom(
                        $nID, 
                        trim($cond->cond_tag)
                    );
                    if ($custom == 0) {
                        $retTF = false;
                    } elseif ($custom == 1) {
                        $retTF = true;
                    }
                    // This is where all the condition-inversion is applied
                    if ($nID > 0 
                        && isset($GLOBALS["SL"]->nodeCondInvert[$nID]) 
                        && isset($GLOBALS["SL"]->nodeCondInvert[$nID][$cond->cond_id])) {
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
        if ($cond && isset($cond->cond_tag)) {
            if (trim($cond->cond_tag) == '#NodeDisabled') {
                $retTF = false;
            } elseif (trim($cond->cond_tag) == '#IsLoggedIn') {
                if (!isset($this->v["uID"]) || $this->v["uID"] <= 0) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsNotLoggedIn') {
                if (isset($this->v["uID"]) && $this->v["uID"] > 0) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsAdmin') {
                if (!isset($this->v["uID"]) 
                    || $this->v["uID"] <= 0 
                    || !$this->v["user"]->hasRole('administrator')) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsNotAdmin') {
                if (isset($this->v["uID"]) 
                    && $this->v["uID"] > 0 
                    && $this->v["user"]->hasRole('administrator')) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsStaff') {
                if (!isset($this->v["uID"]) 
                    || $this->v["uID"] <= 0 
                    || !$this->v["user"]->hasRole('staff')) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsStaffOrAdmin') {
                if (!isset($this->v["uID"]) 
                    || $this->v["uID"] <= 0 
                    || !$this->v["user"]->hasRole('administrator|staff')) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsPartnerStaffOrAdmin') {
                if (!isset($this->v["uID"]) 
                    || $this->v["uID"] <= 0 
                    || !$this->v["user"]->hasRole('administrator|staff|partner')) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsPartnerStaffAdminOrOwner') {
                if (!isset($this->v["uID"]) || $this->v["uID"] <= 0) {
                    $retTF = false;
                } elseif (!$this->v["user"]->hasRole('administrator|staff|partner')
                    && !$this->v["isOwner"]) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsPartner') {
                if (!isset($this->v["uID"]) 
                    || $this->v["uID"] <= 0 
                    || !$this->v["user"]->hasRole('partner')) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsVolunteer') {
                if (!isset($this->v["uID"]) 
                    || $this->v["uID"] <= 0 
                    || !$this->v["user"]->hasRole('volunteer')) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsBrancher') {
                if (!isset($this->v["uID"]) 
                    || $this->v["uID"] <= 0 
                    || !$this->v["user"]->hasRole('databaser')) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsOwner') {
                if (!isset($this->v["uID"]) 
                    || $this->v["uID"] <= 0 
                    || !$this->v["isOwner"]) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsProfileOwner') {
                if (!isset($this->v["uID"]) 
                    || $this->v["uID"] <= 0 
                    || !isset($this->v["profileUser"]) 
                    || !isset($this->v["profileUser"]->id) 
                    || !$this->v["profileUser"]
                    || $this->v["uID"] != $this->v["profileUser"]->id) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsPrintable') {
                $types = ['pdf', 'full-pdf'];
                if (!$GLOBALS["SL"]->REQ->has('print') 
                    && (!isset($GLOBALS["SL"]->pageView) 
                        || !in_array($GLOBALS["SL"]->pageView, $types))) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsPrintInFrame') {
                if (!$GLOBALS["SL"]->REQ->has('ajax') 
                    && !$GLOBALS["SL"]->REQ->has('frame') 
                    && !$GLOBALS["SL"]->REQ->has('wdg')) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#TestLink') {
                if (!$GLOBALS["SL"]->REQ->has('test') 
                    && intVal($GLOBALS["SL"]->REQ->get('test')) < 1) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsDataPermPublic') {
                if ($GLOBALS["SL"]->dataPerms != 'public') {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsDataPermPrivate') {
                if ($GLOBALS["SL"]->dataPerms != 'private') {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsDataPermSensitive') {
                if ($GLOBALS["SL"]->dataPerms != 'sensitive') {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#IsDataPermInternal') {
                if ($GLOBALS["SL"]->dataPerms != 'internal') {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#HasTokenDialogue') {
                if (!$this->pageLoadHasToken()) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#EmailVerified') {
                if ($this->v["uID"] <= 0 || !$this->v["user"]->hasVerifiedEmail()) {
                    $retTF = false;
                }
            } elseif (trim($cond->cond_tag) == '#NextButton') {
                if (!isset($this->REQstep) || $this->REQstep != 'next') {
                    $retTF = false;
                }
            //} elseif (trim($cond->cond_tag) == '#HasUploads') {
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
                $this->sessData->loopItemIDs[$loop->data_loop_plural] = $sortable = [];
                if (isset($this->sessData->dataSets[$loop->data_loop_table]) 
                    && sizeof($this->sessData->dataSets[$loop->data_loop_table]) > 0) {
                    foreach ($this->sessData->dataSets[$loop->data_loop_table] as $recObj) {
                        if ($recObj && $this->parseConditions($loop->conds, $recObj)) {
                            $this->sessData->loopItemIDs[$loop->data_loop_plural][] 
                                = $recObj->getKey();
                            if (trim($loop->data_loop_sort_fld) != '') {
                                $sortable['' . $recObj->getKey() . ''] 
                                    = $recObj->{ $loop->data_loop_sort_fld };
                            }
                        }
                    }
                }
                if (trim($loop->data_loop_sort_fld) != '' && sizeof($sortable) > 0) {
                    $this->sessData->loopItemIDs[$loop->data_loop_plural] = [];
                    asort($sortable);
                    foreach ($sortable as $id => $ord) {
                        $this->sessData->loopItemIDs[$loop->data_loop_plural][] = intVal($id);
                    }
                }
            }
        }
        return true;
    }
    
    // Setting the second parameter to false alternatively returns an array of individual conditions
    public function loadRelatedArticles()
    {
        $this->v["articles"] = $artCondIds = [];
        $this->v["allUrls"] = [
            "txt" => [], 
            "vid" => [] 
        ];
        $allArticles = SLConditionsArticles::get();
        if ($allArticles->isNotEmpty()) {
            foreach ($allArticles as $i => $a) {
                $artCondIds[] = $a->article_cond_id;
            }
            $allConds = SLConditions::whereIn('cond_id', $artCondIds)
                ->get();
            if ($allConds->isNotEmpty()) {
                foreach ($allConds as $i => $c) {
                    if ($this->parseConditions([$c])) {
                        $artLnks = [];
                        foreach ($allArticles as $i => $a) {
                            if ($a->article_cond_id == $c->cond_id) {
                                $artLnks[] = [$a->article_title, $a->article_url];
                                $url = strtolower($a->article_url);
                                $set = ((strpos($url, 'youtube.com') !== false) ? 'vid' : 'txt');
                                $found = false;
                                if (sizeof($this->v["allUrls"][$set]) > 0) {
                                    foreach ($this->v["allUrls"][$set] as $url) {
                                        if ($url[1] == $a->article_url) {
                                            $found = true;
                                        }
                                    }
                                }
                                if (!$found) {
                                    $this->v["allUrls"][$set][] = [$a->article_title, $a->article_url];
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
        if (!$cond 
            || !isset($cond->cond_id) 
            || !$this->sessData->testsAB->checkCond($cond->cond_id)) {
            return false;
        }
        return true;
    }
    
}