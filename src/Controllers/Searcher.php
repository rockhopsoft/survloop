<?php
/**
  * Searcher manages the primary needs of system searches, optionally extended by a 
  * client-custom class.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Session;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SLSearchRecDump;
use SurvLoop\Controllers\SurvCustLoop;

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
    
    public function __construct()
    {
        $this->initExtra();
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
        eval("\$list = " . $GLOBALS["SL"]->modelPath($coreTbl) . "::orderBy('created_at', 'desc')->get();");
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
        $this->survLoopInit($request, '/search-bar/' . $this->treeID);
        return $this->printSearchBar();
    }
    
    public function printSearchBar($search = '', $treeID = 1, $pre = '', $post = '', $nID = -3, $ajax = 0)
    {
        if ($treeID <= 0 && $GLOBALS["SL"]->REQ->has('t')) {
            $treeID = intVal($GLOBALS["SL"]->REQ->get('t'));
        }
        $this->getSearchFilts();
        $GLOBALS["SL"]->pageAJAX .= '$("#searchAdvBtn' . $nID . 't' . $treeID . '").click(function() {
            $("#searchAdv' . $nID . 't' . $treeID . '").slideToggle("fast");
        });';
        return view('vendor.survloop.inc-search-bar', [
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
        $this->cacheName = '/search?t=' . $this->treeID . $this->searchFiltsURL() 
            . '&s=' . $this->searchTxt . $this->advSearchUrlSffx;
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
            $chk = SLSearchRecDump::where('SchRecDmpTreeID', $this->treeID)
                ->whereIn('SchRecDmpRecID', $this->allPublicFiltIDs)
                ->where('SchRecDmpRecDump', 'LIKE', '%' . $this->searchTxt . '%')
                ->orderBy('SchRecDmpRecID', 'desc')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $rec) {
                    $this->addSearchResult($rec->SchRecDmpRecID);
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
                        if (!isset($this->searchOpts["limit"]) || sizeof($printed) < $this->searchOpts["limit"]) {
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
            if ($GLOBALS["SL"]->REQ->has('s') && trim($GLOBALS["SL"]->REQ->get('s')) != '') {
                $this->searchTxt = trim($GLOBALS["SL"]->REQ->get('s'));
            }
            $this->searchParse = $GLOBALS["SL"]->parseSearchWords($this->searchTxt);
            $this->searchFilts = $this->searchOpts = [];
            if ($GLOBALS["SL"]->REQ->has('d') && trim($GLOBALS["SL"]->REQ->get('d')) != '') {
                $this->searchFilts["d"] = $GLOBALS["SL"]->mexplode(',', $GLOBALS["SL"]->REQ->get('d'));
            }
            if ($GLOBALS["SL"]->REQ->has('f') && trim($GLOBALS["SL"]->REQ->get('f')) != '') {
                $this->searchFilts["f"] = $GLOBALS["SL"]->mexplode('__', $GLOBALS["SL"]->REQ->get('f'));
            }
            if ($GLOBALS["SL"]->REQ->has('u') && intVal($GLOBALS["SL"]->REQ->get('u')) > 0) {
                $this->searchFilts["user"] = intVal($GLOBALS["SL"]->REQ->get('u'));
            } elseif ($GLOBALS["SL"]->REQ->has('mine') && intVal($GLOBALS["SL"]->REQ->get('mine')) == 1) {
                $this->searchFilts["user"] = $this->v["uID"];
            }
            if ($GLOBALS["SL"]->REQ->has('limit') && trim($GLOBALS["SL"]->REQ->get('limit')) != '') {
                $this->searchOpts["limit"] = intVal($GLOBALS["SL"]->REQ->get('limit'));
            }
            $GLOBALS["SL"]->loadStates();
            if ($GLOBALS["SL"]->REQ->has('state') && trim($GLOBALS["SL"]->REQ->get('state')) != '') {
                if (!isset($GLOBALS["SL"]->states->stateList[trim($GLOBALS["SL"]->REQ->get('state'))])) {
                    $this->searchOpts["state"] = trim($GLOBALS["SL"]->REQ->get('state'));
                }
            }
            $this->searchResultsXtra($treeID);
            $this->printSearchBarAdvanced($treeID);
        }
        return true;
    }
    
    public function processSearchFilts()
    {
        //if (sizeof($this->allPublicFiltIDs) > 0) return true;
        $this->getAllPublicCoreIDs();
        $this->allPublicFiltIDs = $this->allPublicCoreIDs;
        if (sizeof($this->searchFilts) > 0) {
            if (isset($this->searchFilts["user"]) && Auth::user() && $this->searchFilts["user"] == Auth::user()->id) {
                $this->addArchivedCoreIDs();
            }
            $coreAbbr = $GLOBALS["SL"]->coreTblAbbr();
            foreach ($this->searchFilts as $key => $val) {
                if ($key == 'user' && intVal($val) > 0) {
                    eval("\$chk = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) . "::whereIn('" . $coreAbbr 
                        . (($GLOBALS["SL"]->tblHasPublicID($GLOBALS["SL"]->coreTbl)) ? "Public" : "") 
                        . "ID', \$this->allPublicFiltIDs)->where('" . $GLOBALS["SL"]->getCoreTblUserFld() . "', "
                        . $val . ")->select('" . $coreAbbr . "ID')->get();");
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
                            $this->allPublicFiltIDs = $GLOBALS["SL"]->processFiltFld($fldID, $value, 
                                $this->allPublicFiltIDs);
                        }
                    }
                } else {
                    $this->processSearchFilt($key, $val);
                }
            }
        }
        $this->processSearchAdvanced();
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
     
    public function searchFiltsURL()
    {
        $ret = '';
        if (sizeof($this->searchFilts) > 0) {
            foreach ($this->searchFilts as $key => $val) {
                $paramVal = $val;
                if (is_array($paramVal) && sizeof($paramVal)) {
                  $paramVal = '';
                  foreach ($val as $i => $p) {
                      $paramVal .= (($i > 0) ? ',' : '') . urlencode($p);
                  }
                }
                $ret .= '&' . $key . '=' . $paramVal;
            }
        }
        if (sizeof($this->searchOpts) > 0) {
            foreach ($this->searchOpts as $key => $val) {
                $ret .= '&' . $key . '=' . $val;
            }
        }
        return $ret . $this->searchFiltsURLXtra();
    }
    
    public function searchFiltsURLXtra()
    {
        return '';
    }
    
}