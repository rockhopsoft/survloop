<?php
/**
  * Globals is a core class for loading and accessing system information from anywhere.
  * This is very helpful for processes which may need to be used within Views, etc.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers\Globals;

use DB;
use Auth;
use Storage;
use Illuminate\Http\Request;
use App\Models\SLDatabases;
use App\Models\SLDefinitions;
use App\Models\SLFields;
use App\Models\SLTables;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLNodeResponses;
use App\Models\SLDataLoop;
use App\Models\SLDataSubsets;
use App\Models\SLDataHelpers;
use App\Models\SLDataLinks;
use App\Models\SLSess;
use App\Models\SLSessLoops;
use App\Models\SLSessSite;
use App\Models\SLSessPage;
use App\Models\SLEmails;
use App\Models\SLSearchRecDump;
use SurvLoop\Controllers\SurvLoopImages;
use SurvLoop\Controllers\Tree\TreeNodeSurv;
use SurvLoop\Controllers\Globals\Geographs;
use SurvLoop\Controllers\Globals\GlobalsImportExport;

class Globals extends GlobalsImportExport
{
    public $states    = null;
    public $imgs      = false;
    public $blurbs    = [];
    public $emaBlurbs = [];
    
    function __construct(Request $request = NULL, $dbID = 1, $treeID = 1, $treeOverride = -3)
    {
        $this->loadStatic($request);
        $this->loadGlobalTables($dbID, $treeID, $treeOverride);
        $this->loadDBFromCache($request);
        $this->loadTreeMojis();
        $this->loadDataMap($this->treeID);
        $this->chkReportFormTree();
        $this->loadCustomGlobals();
        $GLOBALS["errors"] = '';
        return true;
    }
    
    public function loadCustomGlobals()
    {
        $GLOBALS["CUST"] = null;
        if (isset($this->sysOpts["cust-abbr"]) && $this->sysOpts["cust-abbr"] != 'SurvLoop') {
            $custClass = $this->sysOpts["cust-abbr"] . "\\Controllers\\" . $this->sysOpts["cust-abbr"] . "Globals";
            if (class_exists($custClass)) {
                eval("\$GLOBALS['CUST'] = new " . $custClass . ";");
            }
        }
        return true;
    }
    
    public function urlRoot()
    {
        return str_replace('https://', '', str_replace('http://', '', $this->sysOpts["app-url"]));
    }
    
    public function isStepLoop($loop)
    {
        return (isset($this->dataLoops[$loop]) && intVal($this->dataLoops[$loop]->DataLoopIsStep) == 1);
    }
    
    public function setClosestLoop($loop = '', $itemID = -3, $obj = [])
    {
        $this->closestLoop = [ "loop" => $loop, "itemID" => $itemID, "obj" => $obj ];
        return true;
    }
    
    public function chkClosestLoop()
    {
        if ($this->sessLoops && isset($this->sessLoops[0])) {
            $loop = $this->sessLoops[0]->SessLoopName;
            if (isset($this->dataLoops[$loop])) {
                $this->setClosestLoop($loop, $this->sessLoops[0]->SessLoopItemID, $this->dataLoops[$loop]);
            }
        }
        return true;
    }
    
    public function loadSessLoops($sessID)
    {
        $this->sessLoops = SLSessLoops::where('SessLoopSessID', $sessID)
            ->orderBy('SessLoopID', 'desc')
            ->get();
        $this->setClosestLoop();
        $this->chkClosestLoop();
        return $this->sessLoops;
    }
    
    public function fakeSessLoopCycle($loop, $itemID)
    {
        /// add fake to [0] position, then reset closest
        $tmpLoops = $this->sessLoops;
        $this->sessLoops = [];
        $this->sessLoops[0] = new SLSessLoops;
        $this->sessLoops[0]->SessLoopName = $loop;
        $this->sessLoops[0]->SessLoopItemID = $itemID;
        if (sizeof($tmpLoops) > 0) {
            foreach ($tmpLoops as $l) {
                $this->sessLoops[] = $l;
            }
        }
        $this->setClosestLoop($loop, $itemID, $this->dataLoops[$loop]);
        return true;
    }
    
    public function removeFakeSessLoopCycle($loop, $itemID)
    {
        $tmpLoops = $this->sessLoops;
        $this->sessLoops = [];
        if (sizeof($tmpLoops) > 0) {
            foreach ($tmpLoops as $i => $l) {
                if ($l->SessLoopName != $loop || $l->SessLoopItemID != $itemID) {
                    $this->sessLoops[] = $l;
                }
            }
        }
        $this->chkClosestLoop();
        return true;
    }
    
    public function getSessLoopID($loopName)
    {
        if (sizeof($this->sessLoops) > 0) {
            foreach ($this->sessLoops as $loop) {
                if ($loop->SessLoopName == $loopName && intVal($loop->SessLoopItemID) > 0) {
                    return $loop->SessLoopItemID;
                }
            }
        }
        return -3;
    }
    
    public function getLoopName($loopID)
    {
        if (sizeof($this->dataLoops) > 0) {
            foreach ($this->dataLoops as $loop) {
                if ($loopID == $loop->DataLoopID) {
                    return $loop->DataLoopPlural;
                }
            }
        }
        return '';
    }
    
    public function getLoopSingular($loopName)
    {
        if (isset($this->dataLoops[$loopName])) {
            return $this->dataLoops[$loopName]->DataLoopSingular;
        }
        return '';
    }
    
    public function getLoopTable($loopName)
    {
        if (isset($this->dataLoops[$loopName])) {
            return $this->dataLoops[$loopName]->DataLoopTable;
        }
        return '';
    }
    
    public function loadLoopConds()
    {
        if (isset($this->dataLoops) && sizeof($this->dataLoops) > 0) {
            foreach ($this->dataLoops as $loopName => $loop) {
                if (isset($this->dataLoops[$loopName]->DataLoopTree)) {
                    $this->dataLoops[$loopName]->loadLoopConds();
                }
            }
        }
        return true;
    }
    
    protected function getDataSetRow($loopName)
    {
        if ($loopName == '' || !isset($this->dataLoops[$loopName])) {
            return [];
        }
        return $this->dataLoops[$loopName];
    }

    protected function loadBlurbNames()
    {
        if (empty($this->blurbs)) {
            $defs = SLDefinitions::select('DefSubset')
                ->where('DefDatabase', $this->dbID)
                ->where('DefSet', 'Blurbs')
                ->get();
            if ($defs->isNotEmpty()) {
                foreach ($defs as $def) {
                    $this->blurbs[] = $def->DefSubset;
                }
            }
        }
        return $this->blurbs;
    }
    
    public function swapBlurbs($str)
    {
        $this->loadBlurbNames();
        if (trim($str) != '' && sizeof($this->blurbs) > 0) {
            $changesMade = true;
            while ($changesMade) {
                $changesMade = false;
                foreach ($this->blurbs as $b) {
                    if (strpos($str, '{{' . $b . '}}') !== false) {
                        $blurb = $this->getBlurb($b);
                        $str = str_replace('{{' . $b . '}}', $blurb, $str);
                        $changesMade = true;
                    }
                    if (strpos($str, '{{' . str_replace('&', '&amp;', $b) . '}}') !== false) {
                        $blurb = $this->getBlurb($b);
                        $str = str_replace('{{' . str_replace('&', '&amp;', $b) . '}}', $blurb, $str);
                        $changesMade = true;
                    }
                }
            }
        }
        return $str;
    }
    
    public function getBlurbAndSwap($blurbName = '', $blurbID = -3)
    {
        return $this->swapBlurbs($this->getBlurb($blurbName, $blurbID));
    }
    
    public function getBlurb($blurbName = '', $blurbID = -3)
    {
        $def = [];
        if ($blurbID > 0) {
            $def = SLDefinitions::find($blurbID);
        } else {
            $def = SLDefinitions::where('DefSubset', $blurbName)
                ->where('DefDatabase', $this->dbID)
                ->where('DefSet', 'Blurbs')
                ->first();
        }
        if ($def && isset($def->DefDescription)) {
            return $def->DefDescription;
        }
        return '';
    }
    
    
    public function loadEmailDropOpts($presel = -3, $tree = -3)
    {
        if ($tree <= 0) {
            $tree = $this->treeID;
        }
        $ret = '';
        $emas = SLEmails::where('EmailTree', $tree)
            ->where('EmailType', 'NOT LIKE', 'Blurb')
            ->get();
        if ($emas->isNotEmpty()) {
            foreach ($emas as $e) {
                $ret .= '<option value="' . $e->EmailID . '" ' . (($e->EmailID == $presel) ? 'SELECTED' : '') . ' >' 
                    . $e->EmailName . '</option>';
            }
        }
        return $ret;
    }
    
    protected function loadEmailBlurbNames()
    {
        if (sizeof($this->emaBlurbs) == 0) {
            $emas = SLEmails::select('EmailName')
                //->where('EmailTree', $this->treeID)
                ->where('EmailType', 'Blurb')
                ->get();
            if ($emas->isNotEmpty()) {
                foreach ($emas as $e) {
                    $this->emaBlurbs[] = $e->EmailName;
                }
            }
        }
        return $this->emaBlurbs;
    }
    
    public function swapEmailBlurbs($str)
    {
        $this->loadEmailBlurbNames();
        if (trim($str) != '' && sizeof($this->emaBlurbs) > 0) {
            $changesMade = true;
            while ($changesMade) {
                $changesMade = false;
                foreach ($this->emaBlurbs as $b) {
                    if (strpos($str, '[{ ' . $b . ' }]') !== false) {
                        $blurb = $this->getEmailBlurb($b);
                        $str = str_replace('[{ ' . $b . ' }]', $blurb, $str);
                        $changesMade = true;
                    }
                    if (strpos($str, '[{ ' . str_replace('&', '&amp;', $b) . ' }]') !== false) {
                        $blurb = $this->getEmailBlurb($b);
                        $str = str_replace('[{ ' . str_replace('&', '&amp;', $b) . ' }]', $blurb, $str);
                        $changesMade = true;
                    }
                }
            }
        }
        return $str;
    }
    
    public function getEmailBlurb($blurbName)
    {
        $ema = SLEmails::where('EmailName', $blurbName)->first();
        if ($ema && isset($ema->EmailBody)) {
            return $ema->EmailBody;
        }
        return '';
    }
    
    public function getEmailSubj($emaID)
    {
        $ema = SLEmails::find($emaID);
        if ($ema && isset($ema->EmailSubject)) {
            return $ema->EmailSubject;
        }
        return '';
    }
    
    public function addToHeadCore($js)
    {
        if (!isset($this->sysOpts['header-code'])) {
            $this->sysOpts['header-code'] = '';
        }
        if (strpos($this->sysOpts['header-code'], $js) === false) {
            $this->sysOpts['header-code'] .= $js;
        }
        return true;
    }
    
    public function debugPrintExtraFilesCSS()
    {
        $ret = '';
        if (isset($this->sysOpts["css-extra-files"]) && trim($this->sysOpts["css-extra-files"]) != '') {
            $files = $this->mexplode(',', $this->sysOpts["css-extra-files"]);
            foreach ($files as $url) {
                $url = trim($url);
                if (strpos($url, '../vendor/') === 0) {
                    $url = $this->convertRel2AbsURL($url);
                }
                if (trim($url) != '') {
                    $ret .= '<script src="' . $url . '" type="text/javascript"></script>';
                }
            }
        }
        return $ret;
    }
    
    public function addTopNavItem($title, $url)
    {
        if (strpos($this->pageJAVA, 'addTopNavItem("' . $title . '"') === false) {
            $this->pageJAVA .= 'setTimeout(\'addTopNavItem("' . $title . '", "' . $url . '")\', 2000);';
        }
        return true;
    }
    
    public function loadImgs($nID = '', $dbID = 1)
    {
        if ($this->imgs === false) {
            $this->imgs = new SurvLoopImages($nID, $dbID);
        }
        return true;
    }
    
    public function getImgSelect($nID = '', $dbID = 1, $presel = '', $newUp = '') 
    {
        $this->loadImgs($nID, $dbID);
        return $this->imgs->getImgSelect($nID, $dbID, $presel, $newUp);
    }
    
    public function getImgDeet($imgID = -3, $nID = '', $dbID = 1) 
    {
        $this->loadImgs($nID, $dbID);
        return $this->imgs->getImgDeet($imgID);
    }
    
    public function saveImgDeet($imgID = -3, $nID = '', $dbID = 1) 
    {
        $this->loadImgs($nID, $dbID);
        return $this->imgs->saveImgDeet($imgID);
    }
    
    public function uploadImg($nID = '', $presel = '', $dbID = 1)
    {
        $this->loadImgs($nID, $dbID);
        return $this->imgs->uploadImg($nID, $presel);
    }
    
    public function addPreloadImg($src = '')
    {
        if (trim($src) == '') {
            return false;
        }
        if (!isset($this->x["preload-imgs"])) {
            $this->x["preload-imgs"] = [];
        }
        $this->x["preload-imgs"][] = $src;
        return true;
    }
    
    public function listPreloadImgs()
    {
        if (!isset($this->x["preload-imgs"])) {
            $this->x["preload-imgs"] = [];
        }
        return $this->x["preload-imgs"];
    }
    
    public function getCurrUrl()
    {
        return $_SERVER["REQUEST_URI"];
    }
    
    public function addAdmMenuHshoo($url = '')
    {
        if (trim($url) == '') {
            return false;
        }
        if (!isset($this->x["menu-hshoos"])) {
            $this->x["menu-hshoos"] = [];
        }
        $this->x["menu-hshoos"][] = $url;
        $this->addHshoo($url);
        return true;
    }
    
    public function addAdmMenuHshoos($urls = [])
    {
        if (sizeof($urls) > 0) {
            foreach ($urls as $i => $url) {
                $this->addAdmMenuHshoo($url);
            }
        }
        return true;
    }
    
    public function isAdmMenuHshoo($url = '')
    {
        return (isset($this->x["menu-hshoos"]) && in_array($url, $this->x["menu-hshoos"]));
    }
    
    public function addHshoo($url = '')
    {
        if (trim($url) == '') {
            return false;
        }
        if (!isset($this->x["hshoos"])) {
            $this->x["hshoos"] = [];
        }
        if (strpos($url, '#') > 0) {
            $url = substr($url, strpos($url, '#'));
        }
        if (!in_array($url, $this->x["hshoos"])) {
            $this->x["hshoos"][] = $url;
        }
        return true;
    }
    
    public function addHshoos($urls = [])
    {
        if (sizeof($urls) > 0) {
            foreach ($urls as $i => $url) {
                $this->addAdmMenuHshoo($url);
            }
        }
        return true;
    }
    
    public function isHshoo($url = '')
    {
        return (isset($this->x["hshoos"]) && in_array($url, $this->x["hshoos"]));
    }
    
    public function getHshooJs()
    {
        return view('vendor.survloop.js.inc-check-add-hshoo', [
            "hshoos" => ((isset($this->x["hshoos"])) ? $this->x["hshoos"] : [])
            ])->render();
    }
    
    public function getXtraJs()
    {
        return $this->getHshooJs();
    }
    
    public function addBodyParams($html)
    {
        if (!isset($this->x["bodyParams"])) {
            $this->x["bodyParams"] = '';
        }
        $this->x["bodyParams"] .= $html;
        return true;
    }
    
    public function getBodyParams()
    {
        $ret = '';
        if (isset($this->x["bodyParams"])) {
            $ret .= $this->x["bodyParams"];
        }
        if (isset($this->x["bodyOverflowX"]) && trim($this->x["bodyOverflowX"]) != '') {
            $ret .= ' style="overflow-x: ' . $this->x["bodyOverflowX"] . ';" ';
        }
        return $ret;
    }
    
    public function pageBodyOverflowX($value = 'visible')
    {
        if ($value == 'visible') {
            $this->x["bodyOverflowX"] = 'visible';
        } else {
            $this->x["bodyOverflowX"] = 'hidden';
        }
        return true;
    }
    
    public function getSrchUrl($override = '')
    {
        if ($override != '') {
            return $this->x["srchUrls"][$override];
        }
        if ($this->isAdmin) {
            return $this->x["srchUrls"]["administrator"];
        } elseif ($this->isVolun) {
            return $this->x["srchUrls"]["volunteer"];
        }
        return $this->x["srchUrls"]["public"];
    }
    
    public function getDumpSrchResultIDs($searches = [], $treeID = -3)
    {
        if ($treeID <= 0) {
            $treeID = $this->treeID;
        }
        if (!isset($this->x["srchResDump"])) {
            $this->x["srchResDump"] = [];
        }
        if (sizeof($searches) > 0) {
            foreach ($searches as $s) {
                $rows = SLSearchRecDump::where('SchRecDmpTreeID', $treeID)
                    ->where('SchRecDmpRecDump', 'LIKE', '%' . $s . '%')
                    ->select('SchRecDmpRecID')
                    ->orderBy('created_at', 'desc')
                    ->get();
                if ($rows->isNotEmpty()) {
                    foreach ($rows as $row) {
                        if (isset($row->SchRecDmpRecID) && !in_array($row->SchRecDmpRecID, $this->x["srchResIDs"])) {
                            $this->x["srchResDump"][] = $row->SchRecDmpRecID;
                        }
                    }
                }
            }
        }
        return true;
    }
    
    public function addSrchResults($set = '?', $rows = [], $idFld = '')
    {
        if (!isset($this->x["srchResIDs"])) {
            $this->x["srchResIDs"] = [];
        }
        if (!isset($this->x["srchRes"])) {
            $this->x["srchRes"] = [];
        }
        if (!isset($this->x["srchRes"][$set])) {
            $this->x["srchRes"][$set] = [];
        }
        if ($rows->isNotEmpty()) {
            foreach ($rows as $row) {
                if (isset($row->{ $idFld }) && !in_array($row->{ $idFld }, $this->x["srchResIDs"])) {
                    $this->x["srchResIDs"][] = $row->{ $idFld };
                    $this->x["srchRes"][$set][] = $row;
                }
            }
        }
        return true;
    }
    
    public function loadStates()
    {
        if (!$this->states) {
            $this->states = new Geographs(isset($this->sysOpts['has-canada'])
                && intVal($this->sysOpts['has-canada']) == 1);
        }
        return true;
    }
    
    public function getState($abbr = '')
    {
        $this->loadStates();
        return $this->states->getState($abbr);
    }

    public function getZipProperty($zip = '', $fld = 'City')
    {
        $this->loadStates();
        return $this->states->getZipProperty($zip, $fld);
    }

    public function getCityCounty($city = '', $state = '')
    {
        $this->loadStates();
        return $this->states->getCityCounty($city, $state);
    }

    public function embedMapSimpAddy($nID = 0, $addy = '', $label = '', $height = 450)
    {
        $this->loadStates();
        return $this->states->embedMapSimpAddy($nID, $addy, $label, $height);
    }
    
    public function embedMapSimpRowAddy($nID, $row, $abbr, $label = '', $height = 450)
    {
        $this->loadStates();
        return $this->states->embedMapSimpAddy($nID, $this->printRowAddyNoPO($row, $abbr), $label, $height);
    }
    
    public function printRowAddy($row, $abbr, $twoLines = false)
    {
        $ret = '';
        if ($row) {
            foreach (['Address', 'Address2', 'AddressCity', 'AddressState', 'AddressZip'] as $i => $fld) {
                if (isset($row->{ $abbr . $fld }) && trim($row->{ $abbr . $fld }) != '') {
                    $ret .= (($twoLines && $fld == 'AddressCity') ? '<br />' : '') . trim($row->{ $abbr . $fld }) 
                        . ((in_array($fld, ['Address', 'Address2', 'AddressCity'])) ? ', ' : ' ');
                }
            }
        }
        return $ret;
    }
    
    public function printRowAddyNoPO($row, $abbr)
    {
        if (isset($row->{ $abbr . 'Address' })) {
            $addy1 = str_replace(' ', '', str_replace('.', '', strtolower($row->{ $abbr . 'Address' })));
            if (strpos($addy1, 'pobox') !== false && isset($row->{ $abbr . 'AddressCity' }) 
                && isset($row->{ $abbr . 'AddressState' })) {
                return $row->{ $abbr . 'AddressCity' } . ', ' . $row->{ $abbr . 'AddressState' };
            }
        }
        return $this->printRowAddy($row, $abbr);
    }
    
    public function mapsURL($addy)
    {
        return 'https://www.google.com/maps/search/' . urlencode($addy) . '/';
    }
    
    public function rowAddyMapsURL($row, $abbr)
    {
        return $this->mapsURL($this->printRowAddy($row, $abbr));
    }
    
    public function setCurrPage($currPage = '')
    {
        $this->x["currPage"] = $currPage;
        return true;
    }
    
    public function setTreeSessID($sessID = 0, $treeID = 0)
    {
        session()->put('sessID' . $treeID, $sessID);
        return true;
    }
    
    public function createNewSess($treeID = 0)
    {
        $sess = new SLSess;
        $sess->SessUserID   = ((Auth::user() && isset(Auth::user()->id)) ? Auth::user()->id : 0);
        $sess->SessTree     = $treeID;
        $sess->SessIsMobile = $this->isMobile();
        $sess->SessBrowser  = $this->getCroppedBrowser();
        $sess->SessIP       = $this->hashIP();
        $sess->SessIsActive = 1;
        $sess->save();
        $this->setTreeSessID($sess->SessID, $treeID);
        return $sess;
    }
    
    public function getCroppedBrowser()
    {
        $browser = ((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '');
        if (strlen($browser) > 254) {
            $browser = substr($browser, 0, 254);
        }
        return $browser;
    }
    
    public function logSiteSessPage()
    {
        $sess = null;
        if (session()->has('sessID0') && intVal(session()->get('sessID0')) > 0) {
            $sess = SLSessSite::find(intVal(session()->get('sessID0')));
        } elseif (Auth::user() && isset(Auth::user()->id)) {
            $sess = SLSessSite::where('SiteSessUserID', Auth::user()->id)
                ->orderBy('created_at', 'desc')
                ->first();
        } else {
            $sess = SLSessSite::where('SiteSessIPaddy', $this->hashIP())
                ->orderBy('created_at', 'desc')
                ->first();
        }
        if (!$sess || !isset($sess->SiteSessID)) {
            $sess = new SLSessSite;
            $sess->SiteSessUserID   = ((Auth::user() && isset(Auth::user()->id)) ? Auth::user()->id : 0);
            $sess->SiteSessIsMobile = $this->isMobile();
            $sess->SiteSessBrowser  = $this->getCroppedBrowser();
            $sess->SiteSessIPaddy   = $this->hashIP();
            $sess->save();
            $this->setTreeSessID($sess->SiteSessID);
        }
        $page = new SLSessPage;
        $page->SessPageSessID = $sess->SiteSessID;
        $page->SessPageURL = ((isset($_SERVER["REQUEST_URI"])) ? $_SERVER["REQUEST_URI"] : '');
        $page->save();
        return true;
    }
    
}
