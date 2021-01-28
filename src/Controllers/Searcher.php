<?php
/**
  * Searcher manages the primary needs of system searches, optionally autoloads client class extension.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers;

use DB;
use Auth;
use Session;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SLSearchRecDump;
use RockHopSoft\Survloop\Controllers\SurvCustLoop;

class Searcher extends SurvCustLoop
{
    public $search           = false;
    public $checkedSearch    = false;
    public $searchTxt        = '';
    public $searchParse      = [];
    public $advSearchUrlSffx = '';
    public $advSearchBarJS   = '';
    public $cacheName        = '';
    public $searchFilts      = [];
    public $searchOpts       = [];
    public $searchResults    = [];
    
    public $allPublicCoreIDs = [];
    public $allPublicFiltIDs = [];
    
    public $v                = []; // variables to pass to views
    
    public function __construct($treeID = 1)
    {
        $this->treeID = $treeID;
        $this->initExtra();
        $this->v["sort"] = [ 'created_at', 'desc' ];
        $this->v["sortLab"] = 'date';
        $this->v["flts"] = [];
    }
    
    public function initExtra()
    {
        return true;
    }
    
    public function getAllPublicCoreIDs($coreTbl = '')
    {
        if (trim($coreTbl) == '') {
            $coreTbl = $GLOBALS["SL"]->coreTbl;
        }
        $this->allPublicCoreIDs = [];
        eval("\$list = " . $GLOBALS["SL"]->modelPath($coreTbl) 
            . "::orderBy('created_at', 'desc')->get();");
        if ($list->isNotEmpty()) {
            foreach ($list as $l) {
                $this->allPublicCoreIDs[] = $l->getKey();
            }
        }
        return $this->allPublicCoreIDs;
    }
    
    public function addArchivedCoreIDs($coreTbl = '')
    {
        if (trim($coreTbl) == '') {
            $coreTbl = $GLOBALS["SL"]->coreTbl;
        }
        $arcs = $this->getArchivedCoreIDs($coreTbl);
        if (sizeof($arcs) > 0) {
            foreach ($arcs as $coreID) {
                if (!in_array($coreID, $this->allPublicFiltIDs)) {
                    $this->allPublicFiltIDs[] = $coreID;
                }
            }
        }
        return $arcs;
    }
    
    protected function getArchivedCoreIDs($coreTbl = '')
    {
        return [];
    }
    
    public function searchBar()
    {
        $this->survloopInit($request, '/search-bar/' . $this->treeID);
        return $this->printSearchBar();
    }
    
    public function printSearchBar($search = '', $treeID = 1, $pre = '', $post = '', $nID = -3, $ajax = 0)
    {
        if ($treeID <= 0 && $GLOBALS["SL"]->REQ->has('t')) {
            $treeID = intVal($GLOBALS["SL"]->REQ->get('t'));
        }
        $this->getSearchFilts();
        $GLOBALS["SL"]->pageAJAX .= '$("#searchAdvBtn' . $nID . 't' . $treeID 
            . '").click(function() { $("#searchAdv' . $nID . 't' . $treeID 
            . '").slideToggle("fast"); });';
        return view('vendor.survloop.elements.inc-search-bar', [
            "nID"      => $nID, 
            "treeID"   => $treeID, 
            "pre"      => $GLOBALS["SL"]->extractJava($pre),
            "post"     => $GLOBALS["SL"]->extractJava($post),
            "ajax"     => $ajax,
            "search"   => $this->searchTxt,
            "extra"    => $this->printSearchBarFilters($treeID, $nID),
            "advanced" => $this->printSearchBarAdvanced($treeID, $nID),
            "advUrl"   => $this->advSearchUrlSffx,
            "advBarJS" => $this->advSearchBarJS
        ])->render();
    }
    
    public function searchCacheName()
    {
        $this->cacheName = '/search?t=' . $this->treeID 
            . $this->searchFiltsURL() . $this->advSearchUrlSffx;
        return $this->cacheName;
    }
    
    public function prepSearchResults(Request $request)
    {
        $this->processSearchFilts();
        if (trim($this->searchTxt) == '') {
            if (sizeof($this->allPublicFiltIDs) > 0) {
                foreach ($this->allPublicFiltIDs as $id) {
                    $this->addSearchResult($id);
                }
            }
        } else {
            $chk = SLSearchRecDump::where('sch_rec_dmp_tree_id', $this->treeID)
                ->whereIn('sch_rec_dmp_rec_id', $this->allPublicFiltIDs)
                ->where('sch_rec_dmp_rec_dump', 'LIKE', '%' . $this->searchTxt . '%')
                ->where('sch_rec_dmp_perms', $GLOBALS["SL"]->getCacheSffxAdds())
                ->orderBy('sch_rec_dmp_rec_id', 'desc')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $rec) {
                    $this->addSearchResult($rec->sch_rec_dmp_rec_id);
                }
            }
        }
        return true;
    }
    
    public function searchResults(Request $request)
    {
        $ret = $this->searchResultsOverride($this->treeID);
        if (trim($ret) != '') {
            return $ret;
        }
        $this->prepSearchResults($request);
        if (sizeof($this->searchResults) > 0) {
            $printed = [];
            while (sizeof($printed) < sizeof($this->searchResults)) {
                $currMax = -1000000;
                foreach ($this->searchResults as $r) {
                    if ($currMax < $r[1] && !in_array($r[0], $printed)) {
                        $currMax = $r[1];
                    }
                }
                foreach ($this->searchResults as $r) {
                    if ($currMax == $r[1] && !in_array($r[0], $printed)) {
                        $printed[] = $r[0];
                        if (!isset($this->searchOpts["limit"]) 
                            || sizeof($printed) < $this->searchOpts["limit"]) {
                            $ret .= $r[2];
                        }
                    }
                }
            }
        } else {
            $ret .= $this->searchResultsNone($this->treeID);
        }
        return $ret;
    }
    
    protected function addSearchResult($recID = -3, $weight = 1, $preview = '')
    {
        if ($recID > 0) {
            if (sizeof($this->searchResults) > 0) {
                foreach ($this->searchResults as $i => $r) {
                    if ($r[0] == $recID) {
                        $this->searchResults[$i][1] += $weight;
                        return false;
                    }
                }
            }
            $this->searchResults[] = [$recID, $weight, $preview];
        }
        return true;
    }
    
    public function searchResultsOverride($treeID = 1)
    {
        return '';
    }
    
    public function searchResultsXtra($treeID = 1)
    {
        return true;
    }
    
    public function searchResultsNone($treeID = 1)
    {
        return '<h4>No records were found matching your search.</h4>';
    }
    
    public function searchResultsFeatured($treeID = 1)
    {
        return '';
    }
    
    public function printSearchBarFilters($treeID = 1, $nID = -3)
    {
        return '';
    }
    
    public function printSearchBarAdvanced($treeID = 1, $nID = -3)
    {
        return '';
    }
    
    public function getSearchFilts($treeID = 1)
    {
        if (!$this->checkedSearch) {
            $this->checkedSearch = true;
            $this->searchTxt = '';
            if ($GLOBALS["SL"]->REQ->has('s') 
                && trim($GLOBALS["SL"]->REQ->get('s')) != '') {
                $this->searchTxt = trim($GLOBALS["SL"]->REQ->get('s'));
            }
            $this->searchParse = $GLOBALS["SL"]->parseSearchWords($this->searchTxt);
            $this->searchFilts = $this->searchOpts = [];
            if ($GLOBALS["SL"]->REQ->has('d') 
                && trim($GLOBALS["SL"]->REQ->get('d')) != '') {
                $d = $GLOBALS["SL"]->REQ->get('d');
                $this->searchFilts["d"] = $GLOBALS["SL"]->mexplode(',', $d);
            }
            if ($GLOBALS["SL"]->REQ->has('f') 
                && trim($GLOBALS["SL"]->REQ->get('f')) != '') {
                $f = $GLOBALS["SL"]->REQ->get('f');
                $this->searchFilts["f"] = $GLOBALS["SL"]->mexplode('__', $f);
            }
            if ($GLOBALS["SL"]->REQ->has('u') 
                && intVal($GLOBALS["SL"]->REQ->get('u')) > 0) {
                $this->searchFilts["user"] = intVal($GLOBALS["SL"]->REQ->get('u'));
            } elseif ($GLOBALS["SL"]->REQ->has('mine') 
                && intVal($GLOBALS["SL"]->REQ->get('mine')) == 1) {
                $this->searchFilts["user"] = $this->v["uID"];
            }
            $GLOBALS["SL"]->loadStates();
            $this->getSearchFiltsGeography();
            $this->searchFilts["dataSet"] = $this->v["dataSet"] = '';
            if ($GLOBALS["SL"]->REQ->has('dataSet')) {
                $this->v["dataSet"] = trim($GLOBALS["SL"]->REQ->get('dataSet'));
                $this->searchFilts["dataSet"] = $this->v["dataSet"];
            }
            $this->searchOpts["limit"] = $GLOBALS["SL"]->getLimit();
            $this->getSearchBarAdvanced($treeID);
            $this->searchResultsXtra($treeID);
            $this->printSearchBarAdvanced($treeID);
        }
        return true;
    }
    
    protected function getSearchFiltsGeography()
    {
        $this->searchFilts["state"] 
            = $this->searchFilts["fltStateClim"] 
            = '';
        $this->searchFilts["states"] 
            = $this->searchFilts["fltStateClimTag"]
            = [];
        if ($GLOBALS["SL"]->REQ->has('state')) {
            $state = trim($GLOBALS["SL"]->REQ->get('state'));
            if ($state != '' 
                && (isset($GLOBALS["SL"]->states->stateList[$state])
                    || isset($GLOBALS["SL"]->states->stateListCa[$state]))) {
                $this->searchFilts["state"] = $state;
            }
        }
        if ($GLOBALS["SL"]->REQ->has('states')) {
            $this->getSearchFiltsStates($GLOBALS["SL"]->REQ->get('states'));
        }
        if ($GLOBALS["SL"]->REQ->has('fltStateClim')) {
            $stateClim = trim($GLOBALS["SL"]->REQ->get('fltStateClim'));
            if ($stateClim != '' 
                && (isset($GLOBALS["SL"]->states->stateList[$stateClim])
                    || isset($GLOBALS["SL"]->states->stateListCa[$stateClim])
                    || sizeof($GLOBALS["SL"]->states->getAshraeGroupZones($stateClim)) > 0)) {
                $this->searchFilts["fltStateClim"] = $stateClim;
            }
        }
        if ($GLOBALS["SL"]->REQ->has('fltStateClimTag')
            && trim($GLOBALS["SL"]->REQ->has('fltStateClimTag')) != ',') {
            $tags = $GLOBALS["SL"]->REQ->get('fltStateClimTag');
            $this->searchFilts["fltStateClimTag"] = $GLOBALS["SL"]->mexplode(',', $tags);
        } elseif ($GLOBALS["SL"]->REQ->has('fltStateClimNID')
            && intVal($GLOBALS["SL"]->REQ->get('fltStateClimNID')) > 0) {
            $nID = intVal($GLOBALS["SL"]->REQ->get('fltStateClimNID'));
            if ($GLOBALS["SL"]->REQ->has('n' . $nID . 'tagIDs')) {
                $tags = $GLOBALS["SL"]->REQ->get('n' . $nID . 'tagIDs');
                $this->searchFilts["fltStateClimTag"] = $GLOBALS["SL"]->mexplode(',', $tags);
            }
        }
//echo '<pre>'; print_r($this->searchFilts); print_r($GLOBALS["SL"]->REQ->all()); echo '</pre>'; exit;
        $this->v["state"]           = $this->searchFilts["state"];
        $this->v["states"]          = $this->searchFilts["states"];
        $this->v["fltStateClim"]    = $this->searchFilts["fltStateClim"];
        $this->v["fltStateClimTag"] = $this->searchFilts["fltStateClimTag"];
        return true;
    }
    
    public function getSearchFiltsStates($statesStr = '')
    {
        $states = $GLOBALS["SL"]->mexplode(',', $statesStr);
        if (is_array($states) && sizeof($states) > 0) {
            foreach ($states as $abbr) {
                if (isset($GLOBALS["SL"]->states->stateList[$abbr])
                    || isset($GLOBALS["SL"]->states->stateListCa[$abbr])) {
                    if (!in_array($abbr, $this->searchFilts["states"])) {
                        $this->searchFilts["states"][] = $abbr;
                    }
                }
            }
        }
        return true;
    }
    
    public function getSearchBarAdvanced($treeID = 1)
    {
        if ($GLOBALS["SL"]->REQ->has('sSort') 
            && trim($GLOBALS["SL"]->REQ->get('sSort')) != '') {
            $this->v["sortLab"] = trim($GLOBALS["SL"]->REQ->get('sSort'));
        }
        if ($GLOBALS["SL"]->REQ->has('sSortDir') 
            && trim($GLOBALS["SL"]->REQ->get('sSortDir')) != '') {
            $this->v["sortDir"] = trim($GLOBALS["SL"]->REQ->get('sSortDir'));
        }
        if ($GLOBALS["SL"]->REQ->has('sFilt') 
            && trim($GLOBALS["SL"]->REQ->get('sFilt')) != '') {
            $tmp = $GLOBALS["SL"]->mexplode('__', $GLOBALS["SL"]->REQ->get('sFilt'));
            if (sizeof($tmp) > 0) {
                foreach ($tmp as $tmpFilt) {
                    $filtParts = $GLOBALS["SL"]->mexplode('_', $tmpFilt);
                    if (sizeof($filtParts) == 2) {
                        $this->searchFilts[$filtParts[0]] 
                            = $GLOBALS["SL"]->mexplode(',', $filtParts[1]);
                    }
                }
            }
        }
//if ($GLOBALS["SL"]->REQ->has('showPreviews')) { echo '<pre>'; print_r($this->searchFilts); print_r($GLOBALS["SL"]->REQ->all()); echo '</pre>'; exit; }
        return '';
    }
    
    public function processSearchFilts()
    {
        //if (sizeof($this->allPublicFiltIDs) > 0) return true;
        $this->getAllPublicCoreIDs();
        $this->allPublicFiltIDs = $this->allPublicCoreIDs;
        if (sizeof($this->searchFilts) > 0) {
            if (isset($this->searchFilts["user"]) && Auth::user() 
                && $this->searchFilts["user"] == Auth::user()->id) {
                $this->addArchivedCoreIDs();
            }
            $coreAbbr = $GLOBALS["SL"]->coreTblAbbr();
            foreach ($this->searchFilts as $key => $val) {
                if ($key == 'user' && intVal($val) > 0) {
                    $coreIdFld = $GLOBALS["SL"]->coreTblIdFldOrPublicId(); /* test more */
                    $eval = "\$chk = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) 
                        . "::whereIn('" . $coreIdFld . "', \$this->allPublicFiltIDs)->where('"
                        . $GLOBALS["SL"]->getCoreTblUserFld() . "', " . $val . ")"
                        . "->select('" . $coreIdFld . "')->get();";
                    eval($eval);
                    $this->allPublicFiltIDs = [];
                    if ($chk->isNotEmpty()) {
                        foreach ($chk as $lnk) {
                            $this->allPublicFiltIDs[] = $lnk->getKey();
                        }
                    }
                } elseif ($key == 'f') {
                    if (sizeof($val) > 0) {
                        foreach ($val as $v) {
                            list($fldID, $value) = explode('|', $v);
                            $this->allPublicFiltIDs = $GLOBALS["SL"]->processFiltFld(
                                $fldID, 
                                $value, 
                                $this->allPublicFiltIDs
                            );
                        }
                    }
                } else {
                    $this->processSearchFilt($key, $val);
                }
            }
        }
        $this->processSearchAdvanced();
//echo '<pre>'; print_r($this->allPublicFiltIDs); echo '</pre>';
//echo '<pre>'; print_r($this->allPublicCoreIDs); echo '</pre>'; exit;
        return true;
    }
    
    protected function processSearchFilt($key, $val)
    {
        return true;
    }
    
    protected function processSearchAdvanced()
    {
        return true;
    }
     
    public function searchFiltsURL($refresh = false)
    {
        if ($refresh) {
            $this->v["searchFiltsURL"] = '';
        }
        if (isset($this->v["searchFiltsURL"]) 
            && trim($this->v["searchFiltsURL"]) != '') {
            return $this->v["searchFiltsURL"];
        }
        $this->v["searchFiltsURL"] = '';
        if ($GLOBALS["SL"]->REQ->has('refresh')) {
            $this->v["searchFiltsURL"] .= '&refresh=' 
                . $GLOBALS["SL"]->REQ->refresh;
        }
        if (trim($this->searchTxt) != '') {
            $this->v["searchFiltsURL"] .= '&s=' . $this->searchTxt;
        }
        if (sizeof($this->searchFilts) > 0) {
            foreach ($this->searchFilts as $key => $val) {
                $paramVal = $val;
                if (is_array($val)) {
                    $paramVal = '';
                    if (sizeof($val) > 0) {
                        foreach ($val as $i => $p) {
                            $paramVal .= (($i > 0) ? ',' : '') . urlencode($p);
                        }
                    }
                }
                $this->v["searchFiltsURL"] .= '&' . $key . '=' . $paramVal;
            }
        }
//echo 'url: ' . $this->v["searchFiltsURL"] . '<pre>'; print_r($this->searchFilts); echo '</pre>';
        if (sizeof($this->searchOpts) > 0) {
            foreach ($this->searchOpts as $key => $val) {
                $this->v["searchFiltsURL"] .= '&' . $key . '=' . $val;
            }
        }
        $this->v["searchFiltsURL"] .= $this->searchFiltsURLXtra();
        return $this->v["searchFiltsURL"];
    }
    
    public function searchFiltsURLXtra()
    {
        $this->v["urlFlts"] = '';
        return '';
    }
    
}