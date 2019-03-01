<?php
/**
  * GlobalsTables is a mid-level class for loading and accessing system information from anywhere.
  * This level contains access to the database design, its tables, and field details.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use DB;
use Auth;
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
use App\Models\SLConditions;
use App\Models\SLConditionsVals;
use App\Models\SLConditionsArticles;
use SurvLoop\Controllers\GlobalsDefinitions;
use SurvLoop\Controllers\TreeNodeSurv;
use SurvLoop\Controllers\GlobalsStatic;

class GlobalsTables extends GlobalsStatic
{
    public $def            = null;
    public $isAdmin        = false;
    public $isVolun        = false;
    public $dbID           = 1;
    public $dbRow          = [];
    public $treeID         = 1;
    public $treeRow        = [];
    public $treeName       = '';
    public $treeBaseSlug   = '';
    public $treeIsAdmin    = false;
    public $xmlTree        = [];
    public $reportTree     = [];
    public $formTree       = [];
    
    public $coreTbl        = '';
    public $coreTblUserFld = '';
    public $treeXmlID      = -3;
    public $treeOverride   = -3;
    public $currProTip     = 0;
    
    public $tblModels      = [];
    public $tbls           = [];
    public $tbl            = [];
    public $tblID          = [];
    public $tblAbbr        = [];
    public $tblOpts        = [];
    public $fldTypes       = [];
    public $fldOthers      = [];
    public $defValues      = [];
    public $condTags       = [];
    public $condABs        = [];
    
    public $foreignKeysIn  = [];
    public $foreignKeysOut = [];
    public $fldAbouts      = [];
    
    public $dataLoops      = [];
    public $dataLoopNames  = [];
    public $dataSubsets    = [];
    public $dataHelpers    = [];
    public $dataLinksOn    = [];
    public $currCyc        = [
        "cyc" => ['', '', -3],
        "res" => ['', '', -3],
        "tbl" => ['', '', -3]
        ];
        
    // User's position within potentially nested loops
    public $sessTree       = 'Page';
    public $sessLoops      = [];
    public $closestLoop    = [];
    public $tblLoops       = [];
    public $nodeCondInvert = [];
    
    public $sysTree        = [
        "forms" => [
            "pub" => [],
            "adm" => []
            ],
        "pages" => [
            "pub" => [],
            "adm" => []
            ]
        ];
    public $treeSettings   = [];
    public $proTips        = [];
    public $allTrees       = [];
    
    // Trees (Surveys & Pages) are assigned an optional property when ( SLTree->TreeOpts%TREEOPT_PRIME == 0 )
    // Site Map Architecture and Permissions Flags
    public const TREEOPT_HOMEPAGE   = 7;  // Page Tree acts as home page for site area
    public const TREEOPT_SEARCH     = 31; // Tree acts as search results page for site area 
    public const TREEOPT_PROFILE    = 23; // This page acts as the default Member Profile for the system
    public const TREEOPT_SURVREPORT = 13; // This page is a report for the records of another Survey Tree
    
    // Site Map Architecture and Permissions Flags
    public const TREEOPT_ADMIN      = 3;  // Tree access limited to admin users
    public const TREEOPT_STAFF      = 43; // Tree access limited to staff users
    public const TREEOPT_PARTNER    = 41; // Tree access limited to partner users
    public const TREEOPT_VOLUNTEER  = 17; // Tree access limited to volunteer users
    
    // Tree Options
    public const TREEOPT_SKINNY     = 2;  // Tree's contents are wrapped in the skinny page width 
    
    // Survey Tree Options
    public const TREEOPT_NOEDITS    = 11; // Record edits not allowed after complete (except admins)
    public const TREEOPT_PUBLICID   = 47; // Survey uses a separate unique Public ID for completed records
    public const TREEOPT_SURVNAVBOT = 37; // A navigation menu is generated below each page of the survey
    public const TREEOPT_SURVNAVTOP = 59; // A navigation menu is generated atop each page of the survey
    public const TREEOPT_SURVNAVLIN = 61; // A thin progress bar is generated atop each page of the survey
    public const TREEOPT_ONEBIGLOOP = 5;  // Survey is one big loop through editable records
    
    // Page Tree Options
    public const TREEOPT_REPORT     = 13; // Page Tree is a Report for a survey, so they share data structures
    public const TREEOPT_NOCACHE    = 29; // Page Tree is currently too complicated to cache
    public const TREEOPT_PAGEFORM   = 53; // This page's enclosing form is submittable
    public const TREEOPT_CONTACT    = 19; // This page is a SurvLoop standard contact form 
    
    public function getTreePrimeConst($type)
    {
        eval("\$prime = self::TREEOPT_" . $type . ";");
        return intVal($prime);
    }
    
    public function chkTreeOpt($treeOpts = 1, $type = '')
    {
        if ($type == '' || $treeOpts == 0) {
            return false;
        }
        $prime = $this->getTreePrimeConst($type);
        return (intVal($prime) != 0 && $treeOpts%$prime == 0);
    }
    
    public function chkCurrTreeOpt($type = '')
    {
        if (!isset($this->treeRow->TreeOpts)) {
            return false;
        }
        return $this->chkTreeOpt($this->treeRow->TreeOpts, $type);
    }
    
    public function loadGlobalTables($dbID = 1, $treeID = 1, $treeOverride = -3)
    {
        $this->isAdmin = (Auth::user() && Auth::user()->hasRole('administrator'));
        $this->isVolun = (Auth::user() && Auth::user()->hasRole('volunteer'));
        if ($treeOverride > 0) {
            $this->treeOverride = $treeOverride;
        }
        if ($treeOverride > 0 || $this->treeOverride <= 0) {
            $this->treeID  = $treeID;
        }
        $this->treeRow = SLTree::find($this->treeID);
        if (($treeOverride > 0 || $this->treeOverride <= 0) && (!$this->treeRow || !isset($this->treeRow->TreeID))) {
            $this->treeRow = SLTree::where('TreeDatabase', $this->dbID)
                ->where('TreeType', 'Survey')
                ->orderBy('TreeID', 'asc')
                ->first();
            if (isset($this->treeRow->TreeID)) {
                $this->treeID = $this->treeRow->TreeID;
            }
        }

        if ($dbID == -3 && isset($this->treeRow->TreeDatabase) && intVal($this->treeRow->TreeDatabase) > 0) {
            $dbID = $this->treeRow->TreeDatabase;
        }
        if ($treeOverride > 0 || $this->treeOverride <= 0) {
            $this->dbID = $dbID;
        }
        $this->def = new GlobalsDefinitions($this->dbID);
        $this->dbRow = SLDatabases::find($this->dbID);
        if (($treeOverride > 0 || $this->treeOverride <= 0) && $dbID == 1 && !$this->dbRow) {
        	$this->dbID = 3;
        	$this->dbRow = SLDatabases::find($this->dbID);
        }
        if ($this->treeRow && isset($this->treeRow->TreeCoreTable) && intVal($this->treeRow->TreeCoreTable) > 0) {
            $this->initCoreTable(SLTables::find($this->treeRow->TreeCoreTable));
        }
        $this->treeIsAdmin = false;
        if (isset($this->treeRow->TreeOpts) && $this->treeRow->TreeOpts > 1 && ($this->treeRow->TreeOpts%3 == 0 
            || $this->treeRow->TreeOpts%17 == 0 || $this->treeRow->TreeOpts%41 == 0 
            || $this->treeRow->TreeOpts%43 == 0)) {
            $this->treeIsAdmin = true;
        }
        if (isset($this->dbRow->DbName) && isset($this->treeRow->TreeName)) {
            $this->treeName = str_replace($this->dbRow->DbName, str_replace('_', '', $this->dbRow->DbPrefix), 
                $this->treeRow->TreeName);
            if ($this->treeRow->TreeType != 'Page') {
                $this->sessTree = $this->treeRow->TreeID;
            }
        }
        $this->sysOpts = ["cust-abbr" => 'SurvLoop'];
        return true;
    }
    
    public function hasTreeOverride()
    {
        return ($this->treeOverride > 0);
    }
    
    public function dbFullSpecs()
    {
        return ($this->dbRow->DbOpts%3 > 0);
    }
    
    public function installNewModel($tbl, $forceFile = true)
    {
        if ($tbl && isset($tbl->TblName) && $tbl->TblName != 'Users') {
            $this->modelPath($tbl->TblName, $forceFile);
        }
        return true;
    }
    
    public function modelPath($tbl = '', $forceFile = false)
    {
        if (strtolower($tbl) == 'users') {
            return "App\\Models\\User";
        }
        if (isset($this->tblModels[$tbl])) {
            $path = "App\\Models\\" . $this->tblModels[$tbl];
            $this->chkTblModel($tbl, $path, $forceFile);
            return $path;
        }
        if (file_exists('../app/Models/SL' . $tbl . '.php')) {
            return "App\\Models\\SL" . $tbl;
        }
        return '';
    }
    
    public function modelPathTblID($tblID = 0, $forceFile = false)
    {
        return $this->modelPath($this->tbl[$tblID], $forceFile);
    }
    
    public function chkTblModel($tbl, $path, $forceFile = false)
    {
        if (in_array(strtolower(trim($tbl)), ['', 'uers'])) return false;
        $modelFilename = str_replace('App\\Models\\', '../app/Models/', $path) . '.php';
        if ($this->isAdmin && (!file_exists($modelFilename) || $forceFile)) { // copied from AdminDatabaseInstall...
            $modelFile = '';
            $tbl = SLTables::where('TblDatabase', $this->dbID)
                ->where('TblName', $tbl)
                ->first();
            $flds = SLFields::where('FldDatabase', $this->dbID)
                ->where('FldTable', $tbl->TblID)
                ->orderBy('FldOrd', 'asc')
                ->orderBy('FldEng', 'asc')
                ->get();
            if ($flds->isNotEmpty()) {
                foreach ($flds as $fld) {
                    $modelFile .= "\n\t\t'" . $tbl->TblAbbr . $fld->FldName . "', ";
                }
            }
            $tblName = $this->dbRow->DbPrefix . $tbl->TblName;
            $fullFileOut = view('vendor.survloop.admin.db.export-laravel-gen-model' , [
                "modelFile" => $modelFile, 
                "tbl"       => $tbl,
                "tblName"   => $tblName,
                "tblClean"  => str_replace('_', '', $tblName)
            ]);
            //if (is_writable($modelFilename)) {
                if (file_exists($modelFilename)) {
                    unlink($modelFilename);
                }
                file_put_contents($modelFilename, $fullFileOut);
            //}
        }
        return true;
    }
    
    public function loadDataMap($treeID = -3)
    {
        if ($treeID != $this->treeID) {
            $this->formTree = SLTree::find($treeID);
        }
        $this->dataLoops = [];
        $this->dataLoopNames = [];
        $dataLoops = SLDataLoop::where('DataLoopTree', $treeID)
            ->where('DataLoopRoot', '>', 0)
            ->orderBy('DataLoopTable', 'asc')
            ->get();
        foreach ($dataLoops as $row) {
            $this->dataLoopNames[$row->DataLoopID] = $row->DataLoopPlural;
            $this->dataLoops[$row->DataLoopPlural] = $row;
            if (!isset($this->tblLoops[$row->DataLoopTable])) {
                $this->tblLoops[$row->DataLoopTable] = $row->DataLoopPlural;
            }
            // what about tables with multiple loops??
        }
        $this->dataSubsets = SLDataSubsets::where('DataSubTree', $treeID)
            ->orderBy('DataSubTbl', 'asc')
            ->orderBy('DataSubSubTbl', 'asc')
            ->get();
        $this->dataHelpers = SLDataHelpers::where('DataHelpTree', $treeID)
            ->orderBy('DataHelpParentTable', 'asc')
            ->orderBy('DataHelpTable', 'asc')
            ->get();
        eval($this->loadDataMapLinks($treeID));
        return true;
    }
    
    public function loadDataMapLinks($treeID = -3)
    {
        if ($treeID <= 0) {
            $treeID = $this->treeID;
        }
        $cache = '';
        $this->dataLinksOn = [];
        $linksChk = SLDataLinks::where('DataLinkTree', $treeID)
            ->get();
        if ($linksChk->isNotEmpty()) {
            foreach ($linksChk as $link) {
                $linkMap = $this->getLinkTblMap($link->DataLinkTable);
                if ($linkMap && sizeof($linkMap) == 5) {
                    $this->dataLinksOn[$link->DataLinkTable] = $linkMap;
                }
            }
        }
        $cache .= '$'.'this->dataLinksOn = [];' . "\n";
        if (sizeof($this->dataLinksOn) > 0) {
            foreach ($this->dataLinksOn as $tbl => $map) {
                $cache .= '$'.'this->dataLinksOn[' . $tbl . '] = [ \'' . $map[0] . '\', \'' . $map[1] 
                    . '\', \'' . $map[2] . '\', \'' . $map[3] . '\', \'' . $map[4] . '\' ];' . "\n";
            }
        }
        return $cache . $this->loadProTips();
    }
    
    public function getCurrTreeUrl()
    {
        if ($this->treeRow->TreeType == 'Page') {
            if ($this->treeIsAdmin) {
                return $this->sysOpts["app-url"] . '/dash/' . $this->treeRow->TreeSlug;
            } else {
                return $this->sysOpts["app-url"] . '/' . $this->treeRow->TreeSlug;
            }
        } else {
            if ($this->treeIsAdmin) {
                return $this->sysOpts["app-url"] . '/dashboard/start/' . $this->treeRow->TreeSlug;
            } else {
                return $this->sysOpts["app-url"] . '/start/' . $this->treeRow->TreeSlug;
            }
        }
        return $this->sysOpts["app-url"];
    }
    
    public function chkReportTree($coreTbl = '')
    {
        if ($coreTbl == '') {
            $coreTbl = $this->coreTbl;
        }
        $reportTree = SLTree::where('TreeType', 'Page')
            ->where('TreeDatabase', $this->dbID)
            ->where('TreeCoreTable', $this->coreTbl)
            ->get();
        if ($reportTree->isNotEmpty()) {
            foreach ($reportTree as $t) {
                if ($t->TreeOpts%13 == 0) {
                    return $t;
                }
            }
        }
        return NULL;
    }
    
    public function getReportTreeID()
    {
        if (isset($this->reportTree["id"]) && intVal($this->reportTree["id"]) > 0) {
            return $this->reportTree["id"];
        }
        return 0;
    }
    
    public function chkReportFormTree()
    {
        if ($this->treeRow && isset($this->treeRow->TreeType) && $this->treeRow->TreeType == 'Page') {
            $nodeChk = SLNode::find($this->treeRow->TreeRoot);
            if ($nodeChk && isset($nodeChk->NodeResponseSet) && intVal($nodeChk->NodeResponseSet) > 0
                && intVal($nodeChk->NodeResponseSet) != $this->treeID) {
                $chk = SLTree::find(intVal($nodeChk->NodeResponseSet));
                $this->loadDataMap(intVal($nodeChk->NodeResponseSet));
            }
        }
        if (!isset($GLOBALS["SL"]->x["pageView"])) {
            $GLOBALS["SL"]->x["pageView"] = $GLOBALS["SL"]->x["pageSlugSffx"] = '';
        }
        return true;
    }
    
    public function getFldRowExtendID($tblExtend)
    {
        $fldRow = new SLFields;
        $fldRow->FldTable = $tblExtend;
        if (isset($this->tbl[$tblExtend]) && isset($this->tblAbbr[$this->tbl[$tblExtend]])) {
            $fldRow->FldName = $this->tblAbbr[$this->tbl[$tblExtend]] . 'ID';
            $fldRow->FldEng = $this->tbl[$tblExtend] . ' ID';
            $fldRow->FldDesc = 'Unique ID number of the record from the other table being extended.';
            $fldRow->FldType = 'INT';
            $fldRow->FldKeyType = ',Foreign,';
            $fldRow->FldForeignTable = $tblExtend;
            $fldRow->FldForeignMin = '0';
            $fldRow->FldForeignMax = 'N';
            $fldRow->FldForeign2Min = '1';
            $fldRow->FldForeign2Max = '1';
        }
        return $fldRow;
    }
    
    public function addFldRowExtends($flds, $tblExtend)
    {
        $flds[] = $this->getFldRowExtendID($tblExtend);
        $exts = SLFields::where('FldTable', $tblExtend)
            ->where('FldDatabase', $this->dbID)
            ->orderBy('FldOrd', 'asc')
            ->get();
        if ($exts->isNotEmpty()) {
            foreach ($exts as $ext) {
                $ext->FldName = $this->tblAbbr[$this->tbl[$tblExtend]] . $ext->FldName;
                $flds[] = $ext;
            }
        }
        return $flds;
    }
    
    public function isCoreTbl($tblID)
    {
        if (!isset($this->treeRow->TreeCoreTable)) {
            return false;
        }
        return ($tblID == $this->treeRow->TreeCoreTable);
    }
    
    public function coreTblAbbr()
    {
        return ((isset($this->tblAbbr[$this->coreTbl])) ? $this->tblAbbr[$this->coreTbl] : '');
    }
    
    public function coreTblIdFld()
    {
        return $this->coreTblAbbr() . 'ID';
    }
    
    public function getCoreTblUserFld()
    {
        if ((!isset($this->coreTblUserFld) || trim($this->coreTblUserFld) == '') 
        	&& isset($this->tblI[$this->coreTbl])) {
            $coreTblID = $this->tblI[$this->coreTbl];
            $userTbl = SLTables::where('TblDatabase', $this->dbID)
                ->whereIn('TblName', ['users', 'Users'])
                ->first();
            if ($userTbl && isset($userTbl->TblID)) {
                $keyFld = SLFields::where('FldTable', $coreTblID)
                    ->where('FldForeignTable', $userTbl->TblID)
                    ->first();
                if ($keyFld && isset($keyFld->FldName)) {
                    $this->coreTblUserFld = $this->tblAbbr[$this->coreTbl] . $keyFld->FldName;
                }
            }
        }
        return $this->coreTblUserFld;
    }
    
    public function getCoreEmailFld()
    {
        if (isset($this->tblI[$this->coreTbl])) {
            $chk = SLFields::where('FldDatabase', $this->dbID)
                ->where('FldTable', $this->tblI[$this->coreTbl])
                ->where('FldName', 'Email')
                ->orderBy('FldOrd', 'asc')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $i => $fld) {
                    return $this->tblAbbr[$this->coreTbl] . $fld->FldName;
                }
            }
        }
        return '';
    }
    
    public function getTableFields($tbl = [])
    {
        $flds = [];
        if (isset($tbl->TblID) && intVal($tbl->TblID) > 0) {
            $flds = SLFields::where('FldTable', $tbl->TblID)
                ->orderBy('FldOrd', 'asc')
                ->orderBy('FldEng', 'asc')
                ->get();
            if (isset($tbl->TblExtend) && intVal($tbl->TblExtend) > 0) {
                $flds = $this->addFldRowExtends($flds, $tbl->TblExtend);
            }
        }
        return $flds;
    }
    
    // not limited to loaded database
    public function getTblFldTypes($tbl)
    {
        $flds = [];
        if (isset($this->fldTypes[$tbl]) && sizeof($this->fldTypes[$tbl]) > 0) {
            $flds = $this->fldTypes[$tbl];
        } else {
            $tblRow = SLTables::where('TblName', $tbl)
                ->first();
            if ($tblRow) {
                $chk = SLFields::where('FldTable', $tblRow->TblID)
                    ->orderBy('FldOrd', 'asc')
                    ->get();
                if ($chk->isNotEmpty()) {
                    foreach ($chk as $fldRow) {
                        $flds[$tblRow->TblAbbr . $fldRow->FldName] = $fldRow->FldType;
                    }
                }
            }
        }
        return $flds;
    }
    
    public function fldForeignKeyTbl($tbl, $fld)
    {
        if (trim($tbl) == '' || trim($fld) == '' || !isset($this->tblI[$tbl])) {
            return '';
        }
        $fld = SLFields::select('FldForeignTable')
            ->where('FldTable', $this->tblI[$tbl])
            ->where('FldName', substr($fld, strlen($this->tblAbbr[$tbl])))
            ->where('FldForeignTable', '>', 0)
            ->first();
        if ($fld && isset($this->tbl[$fld->FldForeignTable])) {
            return $this->tbl[$fld->FldForeignTable];
        }
        return '';
    }
    
    public function getForeignLnk($tbl1, $tbl2 = -3)
    {
        if ($tbl2 <= 0) {
            $tbl2 = $this->treeRow->TreeCoreTable;
        }
        if (!isset($this->x["foreignLookup"])) {
            $this->x["foreignLookup"] = [];
        }
        if (!isset($this->x["foreignLookup"][$tbl1 . '-' . $tbl2])) { 
            $this->x["foreignLookup"][$tbl1 . '-' . $tbl2] = '';
            $fld = SLFields::select('FldName')
                ->where('FldTable', $tbl1)
                ->where('FldForeignTable', $tbl2)
                ->first();
            if ($fld && isset($fld->FldName)) {
                $this->x["foreignLookup"][$tbl1 . '-' . $tbl2] = trim($fld->FldName);
            }
        }
        return $this->x["foreignLookup"][$tbl1 . '-' . $tbl2];
    }
    
    public function getForeignLnkName($tbl1, $tbl2 = '')
    {
        if (trim($tbl1) == '' || trim($tbl2) == '' || !isset($this->tblI[$tbl1]) || !isset($this->tblI[$tbl2])) {
            return '';
        }
        return $this->getForeignLnk($this->tblI[$tbl1], $this->tblI[$tbl2]);
    }
    
    public function getForeignLnkFldName($tbl1, $tbl2 = -3)
    {
        $fldName = $this->getForeignLnk($tbl1, $tbl2);
        if ($fldName != '') {
            return $this->tblAbbr[$this->tbl[$tbl1]] . $fldName;
        }
        return '';
    }
    
    public function getForeignLnkNameFldName($tbl1, $tbl2 = '')
    {
        if (trim($tbl1) == '' || trim($tbl2) == '' || !isset($this->tblI[$tbl1]) || !isset($this->tblI[$tbl2])) {
            return '';
        }
        return $this->getForeignLnkFldName($this->tblI[$tbl1], $this->tblI[$tbl2]);
    }
    
    public function getForeignOpts($preSel = '', $opts = 'Subset')
    {
        $ret = '<option value="" ' . (($preSel == '') ? 'SELECTED' : '') . ' >parent - field - child</option>'
            . '<option value=""></option>' . "\n";
        $flds = SLFields::select('SL_Fields.FldTable', 'SL_Fields.FldName', 'SL_Fields.FldForeignTable')
            ->join('SL_Tables', 'SL_Tables.TblID', '=', 'SL_Fields.FldForeignTable')
            ->where('FldDatabase',         $this->dbID)
            ->where('FldTable',            '>', 0)
            ->where('FldForeignTable',     '>', 0)
            ->orderBy('SL_Tables.TblName', 'asc')
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                if (isset($this->tbl[$fld->FldTable]) && isset($this->tbl[$fld->FldForeignTable])) {
                    $lnkMap = $this->tbl[$fld->FldForeignTable] . '::' 
                        . $this->tbl[$fld->FldTable] . ':' 
                        . $this->tblAbbr[$this->tbl[$fld->FldTable]] . $fld->FldName;
                    $ret .= '<option value="' . $lnkMap . '" ' 
                        . (($preSel == $lnkMap) ? 'SELECTED' : '') . ' >' 
                        . $this->tbl[$fld->FldForeignTable] . ' &larr; ' 
                        . $this->tblAbbr[$this->tbl[$fld->FldTable]] . $fld->FldName 
                        . ' &larr; ' . $this->tbl[$fld->FldTable] . '
                        </option>' . "\n";
                } else {
                    $ret .= '<option value="">** Warning ** not found: ' 
                        . $fld->FldTable . ' * ' . $fld->FldForeignTable . '</option>';
                }
            }
        }
        if ($opts == 'Subset') {
            $flds = SLFields::select('SL_Fields.FldTable', 'SL_Fields.FldName', 'SL_Fields.FldForeignTable')
                ->join('SL_Tables', 'SL_Tables.TblID', '=', 'SL_Fields.FldTable')
                ->where('FldDatabase', $this->dbID)
                ->where('FldTable', '>', 0)
                ->where('FldForeignTable', '>', 0)
                ->orderBy('SL_Tables.TblName', 'asc')
                ->get();
            if ($flds->isNotEmpty()) {
                foreach ($flds as $fld) {
                    if (isset($this->tbl[$fld->FldTable]) && isset($this->tbl[$fld->FldForeignTable])) {
                        $lnkMap = $this->tbl[$fld->FldTable] . ':' . $this->tblAbbr[$this->tbl[$fld->FldTable]] 
                            . $fld->FldName . ':' . $this->tbl[$fld->FldForeignTable] . ':';
                        $ret .= '<option value="' . $lnkMap . '" ' . (($preSel == $lnkMap) ? 'SELECTED' : '') . ' >' 
                            . $this->tbl[$fld->FldTable] . ' &rarr; ' 
                            . $this->tblAbbr[$this->tbl[$fld->FldTable]] . $fld->FldName 
                            . ' &rarr; ' . $this->tbl[$fld->FldForeignTable] . '
                            </option>' . "\n";
                    }
                }
            }
        }
        return $ret;
    }
    
    protected function chkForeignKey($foreignKey)
    {
        if ($foreignKey && intVal($foreignKey) > 0 && isset($this->tbl[$foreignKey])) {
            if (strtolower($this->tbl[$foreignKey]) == 'users') {
                return ['users', 'id'];
            }
            return [
                $this->dbRow->DbPrefix . $this->tbl[$foreignKey], 
                $this->tblAbbr[$GLOBALS['SL']->tbl[$foreignKey]] . "ID"
            ];
        }
        return ['', ''];
    }
    
    // returns array(Table 1, Foreign Key 1, Linking Table, Foreign Key 2, Table 2)
    public function getLinkTblMap($linkTbl = -3)
    {
        if ($linkTbl <= 0) {
            return [];
        }
        $foreigns = SLFields::select('FldName', 'FldForeignTable')
            ->where('FldDatabase', $this->dbID)
            ->where('FldTable', $linkTbl)
            ->where('FldForeignTable', '>', 0)
            ->orderBy('FldOrd', 'asc')
            ->orderBy('FldEng', 'asc')
            ->get();
        if ($foreigns->isNotEmpty() && $foreigns->count() == 2) {
            if (isset($foreigns[0]->FldForeignTable) && isset($this->tbl[$foreigns[0]->FldForeignTable])
                && isset($foreigns[1]->FldForeignTable) && isset($this->tbl[$foreigns[1]->FldForeignTable])
                && isset($this->tbl[$linkTbl]) && isset($this->tblAbbr[$this->tbl[$linkTbl]]) ) {
                return [
                    $this->tbl[$foreigns[0]->FldForeignTable], 
                    $this->tblAbbr[$this->tbl[$linkTbl]] . $foreigns[0]->FldName, 
                    $this->tbl[$linkTbl], 
                    $this->tblAbbr[$this->tbl[$linkTbl]] . $foreigns[1]->FldName, 
                    $this->tbl[$foreigns[1]->FldForeignTable] 
                ];
            }
        }
        return [];
    }
    
    /* public function getLnkTbls($tbl1ID)
    {
        $this->getLinkTblMap($tbl1ID);
    } */
    
    public function getLinkingTables()
    {
        return SLTables::where('TblDatabase', $this->dbID)
            ->where('TblType', 'Linking')
            ->orderBy('TblName', 'asc')
            ->get();
    }
    
    
    
    
    public function tablesDropdown($preSel = '', $instruct = 'select table', $prefix = '', $disableBlank = false)
    {
        $loopTbl = '';
        if (trim($preSel) != '' && isset($this->dataLoops[$preSel])) {
            $loopTbl = $this->dataLoops[$preSel]->DataLoopTable;
        }
        $ret = '<option value="" ' . (($preSel == "") ? 'SELECTED' : '') 
            . (($disableBlank) ? ' DISABLED ' : '') . ' >' . $instruct . '</option>' . "\n";
        foreach ($this->tblAbbr as $tblName => $tblAbbr) {
            $ret .= '<option value="' . $tblName.'" ' 
                . (($preSel == $tblName || $preSel == $this->tblI[$tblName] || $loopTbl == $tblName) ? 'SELECTED' : '')
                . ' >' . $prefix . $tblName.'</option>' . "\n";
        }
        return $ret;
    }
    
    // if $keys is 0 don't include primary keys; if $keys is 1 show primary keys; if $keys is -1 show only foreign keys; 
    public function fieldsDropdown($preSel = '', $keys = 2)
    {
        $ret = '<option value="" ' . ((trim($preSel) == '') ? 'SELECTED' : '') . ' ></option>' . "\n";
        if ($keys > 0) {
            foreach ($this->tblAbbr as $tblName => $tblAbbr) {
                $ret .= '<option value="' . $tblName.':'. $tblAbbr . 'ID" ' 
                    . (($preSel == $tblName.':'. $tblAbbr . 'ID') ? 'SELECTED' : '') 
                    . ' >' . $tblName.' : '. $tblAbbr . 'ID (primary key)</option>' . "\n";
            }
        }
        $flds = null;
        $qman = "SELECT t.`TblName`, t.`TblAbbr`, f.`FldID`, f.`FldName`, f.`FldType`, f.`FldForeignTable` 
            FROM `SL_Fields` f LEFT OUTER JOIN `SL_Tables` t ON f.`FldTable` LIKE t.`TblID` 
            WHERE f.`FldTable` > '0' AND t.`TblName` IS NOT NULL AND f.`FldDatabase` LIKE '" . $this->dbID . "' 
            [[EXTRA]] ORDER BY t.`TblName`, f.`FldName`";
        if ($keys == -1) {
            $flds = DB::select( DB::raw( str_replace("[[EXTRA]]", "AND f.`FldForeignTable` > '0'", $qman) ) );
        } else {
            $flds = DB::select( DB::raw( str_replace("[[EXTRA]]", "", $qman) ) );
        }
        if ($flds && sizeof($flds) > 0) {
        /* why doesn't this version work?..
        $eval = "\$flds = DB::table('SL_Fields')
                ->leftJoin('SL_Tables', function (\$join) {
                    \$join->on('SL_Tables.TblID', '=', 'SL_Fields.FldTable')
                        ->where('SL_Tables.TblName', 'IS NOT', NULL);
                })
                ->where('SL_Fields.FldDatabase', " . $this->dbID . ")
                ->where('SL_Fields.FldTable', '>', 0)
                [[EXTRA]]
                ->orderBy('SL_Tables.TblName', 'asc')
                ->orderBy('SL_Fields.FldName', 'asc')
                ->select('SL_Tables.TblName', 'SL_Tables.TblAbbr', 'SL_Fields.FldID', 'SL_Fields.FldName', 
                    'SL_Fields.FldType', 'SL_Fields.FldForeignTable', 'SL_Fields.FldTable')
                ->get();";
        if ($keys == -1) {
            $eval = str_replace("[[EXTRA]]", "->where('SL_Fields.FldForeignTable', '>' 0)", $eval);
        } else {
            $eval = str_replace("[[EXTRA]]", "", $eval);
        }
        eval($eval);
        if ($flds->isNotEmpty()) {
        ... some day */
            foreach ($flds as $fld) {
                $ret .= $this->fieldsDropdownOption($fld, $preSel);
            }
        }
        return $ret;
    }
    
    public function fieldsDropdownOption($fld, $preSel = '', $valID = false, $prfx = '')
    {
        if (!isset($fld->FldID)) {
            return '';
        }
        if (!isset($fld->TblName) && isset($fld->FldTable) && isset($this->tbl[$fld->FldTable])) {
            $fld->TblName = $this->tbl[$fld->FldTable];
        }
        if (!isset($fld->TblAbbr) && isset($fld->FldTable) && isset($this->tbl[$fld->FldTable])) {
            $fld->TblAbbr = $this->tblAbbr[$this->tbl[$fld->FldTable]];
        }
        if ($valID) {
            return '<option value="' . $fld->FldID . '"' . ((intVal($preSel) != 0 && intVal($preSel) == $fld->FldID) 
                    ? ' SELECTED' : '') . ' >' . $prfx . $fld->TblName . ' : '. $fld->TblAbbr . $fld->FldName 
                . ' ('. (($fld->FldForeignTable > 0) ? 'foreign key' : strtolower($fld->FldType)) . ')</option>' . "\n";
        } else {
            $fldStr = $fld->TblName . ':' . $fld->TblAbbr . $fld->FldName;
            return '<option value="' . $fldStr . '"' . ((trim($preSel) == $fldStr) ? ' SELECTED' : '') . ' >' . $prfx 
                . str_replace(':', ' : ', $fldStr) . ' (' . (($fld->FldForeignTable > 0) ? 'foreign key' 
                    : strtolower($fld->FldType)) . ')</option>' . "\n";
        }
        return '';
    }
    
    public function allDefsDropdown($preSel = '')
    {
        $ret = '<option value="" ' . (($preSel == "") ? 'SELECTED' : '') . ' ></option>' . "\n";
        $defs = SLDefinitions::select('DefSubset', 'DefID', 'DefValue')
            ->where('DefSet', 'Value Ranges')
            ->orderBy('DefSubset', 'asc')
            ->orderBy('DefOrder', 'asc')
            ->get();
        if ($defs->isNotEmpty()) {
            foreach ($defs as $def) {
                $ret .= '<option value="' . $def->DefID.'" ' . (($preSel == $def->DefID) ? 'SELECTED' : '') . ' >' 
                    . $def->DefSubset . ': ' . $def->DefValue . '</option>' . "\n";
            }
        }
        return $ret;
    }
    
    public function allDefSets()
    {
        return SLDefinitions::where('DefSet', 'Value Ranges')
            ->select('DefSubset')
            ->distinct()
            ->orderBy('DefSubset', 'asc')
            ->get();
    }
    
    public function printLoopsDropdowns($preSel = '', $fld = 'loopList', $manualOpt = true)
    {
        $currDefinition = $currLoopItems = $currTblRecs = '';
        if (isset($preSel)) {
            if (strpos($preSel, 'Definition::') !== false) {
                $currDefinition = str_replace('Definition::', '', $preSel);
            } elseif (strpos($preSel, 'LoopItems::') !== false) {
                $currLoopItems = str_replace('LoopItems::', '', $preSel);
            } elseif (strpos($preSel, 'Table::') !== false) {
                $currTblRecs = str_replace('Table::', '', $preSel);
            }
        }                   
        return view('vendor.survloop.admin.tree.node-edit-loop-list', [
            "fld"            => $fld,
            "manualOpt"      => $manualOpt,
            "defs"           => $this->allDefSets(),
            "currDefinition" => $currDefinition, 
            "currLoopItems"  => $currLoopItems, 
            "currTblRecs"    => $currTblRecs
            ])->render();
    }
    
    public function postLoopsDropdowns($fld = 'loopList')
    {
        $ret = '';
        if ($this->REQ->has($fld . 'Type')) {
            if (trim($this->REQ->input($fld . 'Type')) == 'auto-def') {
                if (trim($this->REQ->input($fld . 'Definition')) != '') {
                    $ret = 'Definition::' . $this->REQ->input($fld . 'Definition');
                }
            } elseif (trim($this->REQ->input($fld . 'Type')) == 'auto-loop') {
                if (trim($this->REQ->input($fld . 'LoopItems')) != '') {
                    $ret = 'LoopItems::'.$this->REQ->input($fld . 'LoopItems');
                }
            } elseif (trim($this->REQ->input($fld . 'Type')) == 'auto-tbl') {
                if (trim($this->REQ->input($fld . 'Tables')) != '') {
                    $ret = 'Table::'.$this->REQ->input($fld . 'Tables');
                }
            }
        }
        return $ret;
    }
    
    public function getLoopConditionLinks($loop)
    {
        $ret = [];
        if (isset($this->dataLoops[$loop]) && isset($this->dataLoops[$loop]->DataLoopID)) {
            $chk = SLConditions::select('SL_Conditions.CondID', 'SL_Conditions.CondField', 'SL_Conditions.CondTable')
                ->join('SL_ConditionsNodes', 'SL_ConditionsNodes.CondNodeCondID', '=', 'SL_Conditions.CondID')
                ->where('SL_ConditionsNodes.CondNodeLoopID', $this->dataLoops[$loop]->DataLoopID)
                ->where('SL_Conditions.CondOperator', '{')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $cond) {
                    if (isset($cond->CondField) && intVal($cond->CondField) > 0) {
                        $vals = SLConditionsVals::where('CondValCondID', $cond->CondID)
                            ->get();
                        if ($vals->isNotEmpty() && $vals->count() == 1 && isset($vals[0]->CondValValue) 
                            && trim($vals[0]->CondValValue) != '') {
                            $fld = SLFields::find($cond->CondField);
                            if ($fld && isset($fld->FldName) && isset($this->tbl[$cond->CondTable])) {
                                $tblAbbr = $this->tblAbbr[$this->tbl[$cond->CondTable]];
                                $ret[] = [
                                    $tblAbbr . $fld->FldName,
                                    $vals[0]->CondValValue
                                    ];
                            }
                        }
                    }
                }
            }
        }
        return $ret;
    }
    
    public function getAllSetTables($tblIn = '')
    {
        $tbls = [];
        $tblID = -3;
        if (strpos($tblIn, 'loop-') !== false && sizeof($this->dataLoops) > 0) {
            $loopID = intVal(str_replace('loop-', '', $tblIn));
            foreach ($this->dataLoops as $loopName => $loopRow) {
                if ($loopRow->id == $loopID && isset($this->tblI[$loopRow->DataLoopTable])) {
                    $tblID = $this->tblI[$loopRow->DataLoopTable];
                }
            }
        } elseif (isset($this->tblI[$tblIn])) {
            $tblID = $this->tblI[$tblIn];
        } elseif (isset($this->tbl[$tblIn])) {
            $tblID = intVal($tblIn);
        }
        $tbls = $this->getSubsetTables($tblID, $tbls);
        return $tbls;
    }
    
    public function getSubsetTables($tbl1 = -3, $tbls = [])
    {
        if ($tbl1 > 0 && !in_array($tbl1, $tbls)) {
            $tbls[] = $tbl1;
            if (isset($this->dataSubsets) && sizeof($this->dataSubsets) > 0) {
                foreach ($this->dataSubsets as $subset) {
                    if ($tbl1 == $this->tblI[$subset->DataSubTbl]) {
                        $tbls = $this->getSubsetTables($this->tblI[$subset->DataSubSubTbl], $tbls);
                    }
                }
            }
            if (isset($this->dataHelpers) && sizeof($this->dataHelpers) > 0) {
                foreach ($this->dataHelpers as $helper) {
                    if ($tbl1 == $this->tblI[$helper->DataHelpParentTable]) {
                        $tbls = $this->getSubsetTables($this->tblI[$helper->DataHelpTable], $tbls);
                    }
                }
            }
        }
        return $tbls;
    }
    
    public function isFldCheckboxHelper($fld = '')
    {
        if (isset($this->dataHelpers) && sizeof($this->dataHelpers) > 0) {
            foreach ($this->dataHelpers as $helper) {
                if (isset($helper->DataHelpValueField) && $helper->DataHelpValueField == $fld) {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function fieldsTblsDropdown($tbls = [], $preSel = '', $prfx = '')
    {
        $ret = '';
        $prevTbl = -3;
        $flds = DB::table('SL_Fields')
            ->leftJoin('SL_Tables', function ($join) {
                $join->on('SL_Tables.TblID', '=', 'SL_Fields.FldTable');
            })
            ->whereIn('SL_Fields.FldTable', $tbls)
            ->orderBy('SL_Tables.TblName', 'asc')
            ->orderBy('SL_Fields.FldName', 'asc')
            ->select('SL_Tables.TblName', 'SL_Tables.TblAbbr', 'SL_Fields.FldID', 'SL_Fields.FldName', 
                'SL_Fields.FldType', 'SL_Fields.FldForeignTable', 'SL_Fields.FldTable')
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                if ($prevTbl != $fld->FldTable) {
                    $ret .= '<option value=""></option>' . "\n";
                }
                $ret .= $this->fieldsDropdownOption($fld, $preSel, true, $prfx) . "\n";
                $prevTbl = $fld->FldTable;
            }
        }
        return $ret;
    }
    
    public function getAllSetTblFldDrops($tblIn = '', $preSel = '')
    {
        return $this->fieldsTblsDropdown($this->getAllSetTables($tblIn), $preSel, ' - ');
    }
    
    public function getFullFldNameFromID($fldID, $full = true)
    {
        $fld = DB::table('SL_Fields')
            ->join('SL_Tables', 'SL_Fields.FldTable', '=', 'SL_Tables.TblID')
            ->where('SL_Fields.FldID', $fldID)
            ->select('SL_Tables.TblName', 'SL_Tables.TblAbbr', 'SL_Fields.FldName')
            ->first();
        if ($fld && isset($fld->TblAbbr)) {
            return (($full) ? $fld->TblName . ':' : '') . $fld->TblAbbr . $fld->FldName;
        }
        return '';
    }
    
    public function getFldIDFromFullName($fldName)
    {
        $flds = DB::table('SL_Fields')
            ->join('SL_Tables', 'SL_Fields.FldTable', '=', 'SL_Tables.TblID')
            ->select('SL_Tables.TblName', 'SL_Tables.TblAbbr', 'SL_Fields.FldName', 'SL_Fields.FldID')
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $f) {
                $testName = $f->TblAbbr . $f->FldName; // $f->TblName . ':' . 
                if ($fldName == $testName) {
                    return $f->FldID;
                }
            }
        }
        return -3;
    }
    
    public function getFldRowFromFullName($tbl, $fld)
    {
        if (!isset($this->tblI[$tbl]) || !isset($this->tblAbbr[$tbl])) {
            return [];
        }
        return SLFields::where('FldTable', $this->tblI[$tbl])
            ->where('FldName', substr($fld, strlen($this->tblAbbr[$tbl])))
            ->first();
    }
    
    public function getFldIDFromFullWritName($tblFld)
    {
        list($tbl, $fld) = explode(':', $tblFld);
        return $this->getFldRowFromFullName($tbl, $fld)->getKey();
    }
    
    public function getFldDefSet($tbl, $fld, $fldRow = NULL)
    {
        $ret = '';
        if (!$fldRow) {
            $fldRow = $this->getFldRowFromFullName($tbl, $fld);
        }
        if ($fldRow && isset($fldRow->FldValues)) {
            if (strpos($fldRow->FldValues, 'Def::') !== false) {
                $ret = str_replace('Def::', '', $fldRow->FldValues);
            } elseif (in_array($fldRow->FldValues, ['Y;N', 'N;Y', 'Y;N;?', '0;1', '1;0'])) {
                $ret = 'Yes/No';
            }
        }
        return $ret;
    }
    
    public function getFldTitle($tbl, $fld, $fldRow = NULL)
    {
        if (!$fldRow) {
            $fldRow = $this->getFldRowFromFullName($tbl, $fld);
        }
        if ($fldRow && isset($fldRow->FldEng)) {
            return $fldRow->FldEng;
        }
        return '';
    }
    
    public function fld2SchemaType($fld)
    {
        if (strpos($fld->FldValues, 'Def::') !== false) {
            return 'xs:string';
        }
        switch (strtoupper(trim($fld->FldType))) {
            case 'INT':      return 'xs:integer'; break;
            case 'DOUBLE':   return 'xs:double'; break;
            case 'DATE':     return 'xs:date'; break;
            case 'DATETIME': return 'xs:dateTime'; break;
        } // case 'VARCHAR': case 'TEXT':
        return 'xs:string';
    }
    
    public function getTblFlds($tbl)
    {
        $ret = [];
        if (isset($this->tblI[$tbl]) && isset($this->fldTypes[$tbl]) && is_array($this->fldTypes[$tbl]) 
            && sizeof($this->fldTypes[$tbl]) > 0) {
            foreach ($this->fldTypes[$tbl] as $fld => $type) {
                $ret[] = $fld;
            }
            /*
            $chk = SLFields::where('FldTable', '=', $this->tblI[$tbl])
                ->where('FldSpecType', '=', 'Unique')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $i => $fld) {
                    $ret[] = $this->tblAbbr[$tbl] . $fld->FldName;
                }
            }
            */
        }
        return $ret;
    }
    
    public function copyTblRecFromRow($tbl, $row)
    {
        if (!isset($this->tblAbbr[$tbl]) || !$row || !isset($row->{ $this->tblAbbr[$tbl] . 'ID' })) {
            return '';
        }
        $abbr = $this->tblAbbr[$tbl];
        eval("\$cpyTo = " . $this->modelPath($tbl) . "::find(" . $row->{ $abbr . 'ID' } . ");");
        if (!$cpyTo || !isset($cpyTo->{ $abbr . 'ID' })) {
            eval("\$cpyTo = new " . $this->modelPath($tbl) . ";");
            $cpyTo->{ $abbr . 'ID' } = $row->{ $abbr . 'ID' };
        }
        $flds = $this->getTblFlds($tbl);
        if ($flds->isNotEmpty()) {
            foreach ($flds as $i => $fld) $cpyTo->{ $fld } = $row->{ $fld };
            $chk = SLTree::where('TreeCoreTable', $this->tblI[$tbl])
                ->get();
            if ($chk->isNotEmpty()) {
                $cpyTo->{ $abbr . 'UserID' }             = $row->{ $abbr . 'UserID' };
                $cpyTo->{ $abbr . 'IPaddy' }             = $row->{ $abbr . 'IPaddy' };
                $cpyTo->{ $abbr . 'UniqueStr' }          = $row->{ $abbr . 'UniqueStr' };
                $cpyTo->{ $abbr . 'VersionAB' }          = $row->{ $abbr . 'VersionAB' };
                $cpyTo->{ $abbr . 'TreeVersion' }        = $row->{ $abbr . 'TreeVersion' };
                $cpyTo->{ $abbr . 'IsMobile' }           = $row->{ $abbr . 'IsMobile' };
                $cpyTo->{ $abbr . 'SubmissionProgress' } = $row->{ $abbr . 'SubmissionProgress' };
            }
            $cpyTo->updated_at = $row->updated_at;
            $cpyTo->created_at = $row->created_at;
        }
        $cpyTo->save();
        return ' , copying ' . $tbl . ' row #' . $row->{ $abbr . 'ID' };
    }
    
    public function getMatchingFlds($tbl1, $tbl2)
    {
        $ret = [];
        $flds1 = $this->getTblFlds($tbl1);
        if ($flds1->isNotEmpty()) {
            $prfxPos = strlen($this->tblAbbr[$tbl1]);
            foreach ($flds1 as $i => $fld1) {
                $flds1[$i] = substr($fld1, $prfxPos);
            }
        }
        $flds2 = $this->getTblFlds($tbl2);
        if ($flds2->isNotEmpty()) {
            $prfxPos = strlen($this->tblAbbr[$tbl2]);
            foreach ($flds2 as $i => $fld2) {
                $fld2 = substr($fld2, $prfxPos);
                if ($fld2 != 'ID' && in_array($fld2, $flds1)) {
                    $ret[] = $fld2;
                }
            }
        }
        return $ret;
    }           
    
    public function printResponse($tbl, $fld, $val, $fldRow = null)
    {
        $ret = '';
        if (!$fldRow) {
            $fldRow = $this->getFldRowFromFullName($tbl, $fld);
        }
        $defSet = $this->getFldDefSet($tbl, $fld, $fldRow);
        if ($defSet != '') {
            if (!is_array($val) && $val != '' && substr($val, 0, 1) == ';' && substr($val, strlen($val)-1) == ';') {
                $val = $this->mexplode(';;', substr($val, 1, strlen($val)-2));
            }
        }
        $str2arr = $this->str2arr($val);
        if (sizeof($str2arr) > 0 && $str2arr[0] != 'EMPTY ARRAY') {
            $val = $str2arr;
        }
        if (is_array($val)) {
            if (sizeof($val) > 0) {
                if (trim($defSet) != '') {
                    foreach ($val as $i => $v) {
                        $val[$i] = $this->def->getVal($defSet, $v);
                    }
                }
                $ret = implode(', ', $val);
                if (trim($ret) == ',') {
                    $ret = '';
                }
            }
        } else { // not array
            if (strpos(strtolower($fld), 'gender') !== false && strtoupper($val) == 'M') {
                $ret = 'Male';
            } elseif (strpos(strtolower($fld), 'gender') !== false && strtoupper($val) == 'F') {
                $ret = 'Female';
            } elseif (trim($defSet) == '') {
                if ($val != '' && isset($this->fldTypes[$tbl]) && isset($this->fldTypes[$tbl][$fld])
                    && in_array($this->fldTypes[$tbl][$fld], ['INT', 'DOUBLE'])) {
                    $ret = number_format(1*floatval($val));
                } else {
                    $ret = $val;
                }
            } elseif (trim($defSet) == 'Yes/No') {
                if (in_array(trim(strtoupper($val)), ['1', 'Y'])) {
                    $ret = 'Yes';
                } elseif (in_array(trim(strtoupper($val)), ['0', 'N'])) {
                    $ret = 'No';
                } elseif (trim($val) == '?') {
                    $ret = 'Not sure';
                }
            } elseif ($this->fldTypes[$tbl][$fld] == 'DATE') {
                $ret = date("n/j/y", strtotime($val));
            } else {
                $ret = $this->def->getVal($defSet, $val);
            }
        }
        return $ret;
    }
    
    public function getMapToCore($fldID = -3, $fld = NULL)
    {
        $ret = [];
        if (!$fld) {
            $fld = SLFields::find($fldID);
        }
        if ($fld && isset($fld->FldTable) && $fld->FldTable != $this->tblI[$this->coreTbl]) {
            $linkMap = $this->getLinkTblMap($fld->FldTable);
            
        }
        return $ret;
    }
    
    public function digMapToCore($tbl = -3)
    {
        $tbls = [];
        $kids = $this->getSubsetTables($tbl);
        if (sizeof($kids) > 0) {
            foreach ($kids as $i => $k) {
                
                //$tbls[] = 
                if (sizeof($tbls) > 0) {
                    $tbls[] = $tbl;
                }
            }
        }
        return $tbls;
    }
    
    public function processFiltFld($fldID, $value = '', $ids = [])
    {
        if (trim($value) != '' && sizeof($ids) > 0) {
            if (trim($value) != '') {
                $fld = SLFields::find($fldID);
                $tbl = $this->tbl[$fld->FldTable];
                $keyMap = $this->getMapToCore($fldID, $fld);
                if (empty($keyMap)) { // then field in core record
                    $eval = "\$chk = " . $this->modelPath($tbl) . "::whereIn('" . $this->tblAbbr[$tbl] 
                        . "ID', \$ids)->where('" .  $this->tblAbbr[$tbl] . $fld->FldName . "', '" . $value 
                        . "')->select('" . $this->tblAbbr[$tbl] . "ID')->get();";
                    eval($eval);
                    $ids = [];
                    if ($chk->isNotEmpty()) {
                        foreach ($chk as $lnk) {
                            $ids[] = $lnk->getKey();
                        }
                    }
                    
                }
                // filter out for field value
            }
        }
        return $ids;
    }
    
    public function origFldCheckbox($tbl, $fld)
    {
        if (!isset($this->formTree->TreeID)) {
            return -3;
        }
        $chk = SLNode::where('NodeDataStore', $tbl . ':' . $fld)
            ->where('NodeType', 'Checkbox')
            ->where('NodeTree', $this->formTree->TreeID)
            ->first();
        if ($chk && isset($chk->NodeID)) {
            return $chk->NodeID;
        }
        return -3;
    }
    
    public function getFldNodeQuestion($tbl, $fld, $tree = -3)
    {
        $chk = null;
        if ($tree < 0) {
            $tree = $this->treeID;
        }
        if ($tree == 0) {
            $chk = SLNode::where('NodeDataStore', $tbl . ':' . $fld)
                ->orderBy('NodeTree', 'asc')
                ->orderBy('NodeID', 'desc')
                ->get();
        } else {
            $chk = SLNode::where('NodeTree', $tree)
                ->where('NodeDataStore', $tbl . ':' . $fld)
                ->orderBy('NodeID', 'desc')
                ->get();
        }
        if ($chk && $chk->isNotEmpty()) {
            foreach ($chk as $node) {
                if (isset($node->NodePromptText) && trim($node->NodePromptText) != '') {
                    return $node->NodePromptText;
                }
            }
        }
        return '';
    }
    
    
    
    public function splitTblFld($tblFld)
    {
        $tbl = $fld = '';
        if (trim($tblFld) != '' && strpos($tblFld, ':') !== false) {
            list($tbl, $fld) = explode(':', $tblFld);
        }
        return [$tbl, $fld];
    }
    
    public function getTblFldID($tblFld)
    {
        list($tbl, $fld) = $this->splitTblFld($tblFld);
        if (trim($tbl) != '' && trim($fld) != '' && isset($this->tblI[$tbl])) {
            $fldRow = SLFields::select('FldID')
                ->where('FldTable', $this->tblI[$tbl])
                ->where('FldName', substr($fld, strlen($this->tblAbbr[$tbl])))
                ->first();
            if ($fldRow && isset($fldRow->FldID)) {
                return $fldRow->FldID;
            }
        }
        return -3;
    }
    
    public function getTblFldRow($tblFld = '', $tbl = '', $fld = '')
    {
        if ($tbl == '' || $fld == '') {
            list($tbl, $fld) = $this->splitTblFld($tblFld);
        }
        if (trim($tbl) != '' && trim($fld) != '' && isset($this->tblI[$tbl])) {
            $fldRow = SLFields::where('FldTable', $this->tblI[$tbl])
                ->where('FldName', substr($fld, strlen($this->tblAbbr[$tbl])))
                ->first();
            return $fldRow;
        }
        return null;
    }
    
    public function getFldResponsesByID($fldID)
    {
        if (intVal($fldID) <= 0) {
            return [ "prompt" => '', "vals" => [] ];
        }
        return $this->getFldResponses($this->getFullFldNameFromID($fldID));
    }
    
    public function getFldResponses($fldName)
    {
        $ret = array( "prompt" => '', "vals" => [] );
        $tmpVals = array( [], [] );
        $nodes = SLNode::where('NodeDataStore', $fldName)->get();
        if (trim($fldName) != '' && $nodes->isNotEmpty()) {
            foreach ($nodes as $n) {
                if (trim($ret["prompt"]) == '' && trim($n->NodePromptText) != '') {
                    $ret["prompt"] = strip_tags($n->NodePromptText);
                }
                $res = SLNodeResponses::where('NodeResNode', $n->NodeID)
                    ->orderBy('NodeResOrd', 'asc')
                    ->get();
                if ($res->isNotEmpty()) {
                    foreach ($res as $r) {
                        if (!in_array($r->NodeResValue, $tmpVals[0])) {
                            $tmpVals[0][] = $r->NodeResValue;
                            $tmpVals[1][] = strip_tags($r->NodeResEng);
                        }
                    }
                }
            }
            if (sizeof($tmpVals[0]) > 0) {
                foreach ($tmpVals[0] as $i => $val) {
                    $ret["vals"][] = [$val, $tmpVals[1][$i]];
                }
            }
        }
        return $ret;
    }
    
    public function getCondLookup()
    {
        if (empty($this->condTags)) {
            $chk = SLConditions::whereIn('CondDatabase', [0, $this->dbID])
                ->orderBy('CondTag')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $i => $c) {
                    $this->condTags[$c->CondID] = $c->CondTag;
                }
            }
        }
        return true;
    }
    
    public function getCondByID($id)
    {
        $this->getCondLookup();
        return ((isset($this->condTags[intVal($id)])) ? $this->condTags[intVal($id)] : '');
    }
    
    public function getCondList()
    {
        return SLConditions::whereIn('CondDatabase', [0, $this->dbID])
            ->orderBy('CondTag')
            ->get();
    }
    
    public function saveEditCondition(Request $request)
    {
        if ($request->has('oldConds') && intVal($request->oldConds) > 0) {
            return SLConditions::find(intVal($request->oldConds));
        }
        $cond = new SLConditions;
        if ($request->has('condID') && intVal($request->condID) > 0) {
            $cond = SLConditions::find(intVal($request->condID));
            SLConditionsVals::where('CondValCondID', $cond->CondID)->delete();
        } else {
            $cond->CondDatabase = $this->dbID;
        }
        if ($request->has('condHash') && trim($request->condHash) != '' && trim($request->condHash) != '#') {
            $cond->CondTag      = (($request->has('condHash')) ? $request->condHash : '#');
            if (substr($cond->CondTag, 0, 1) != '#') {
                $cond->CondTag  = '#' . $cond->CondTag;
            }
            $cond->CondDesc     = (($request->has('condDesc')) ? $request->condDesc : '');
            $cond->CondOperator = 'CUSTOM';
            $cond->CondOperDeet = 0;
            $cond->CondField = $cond->CondTable = $cond->CondLoop = 0;
            $cond->CondOpts     = 1;
            
            if ($request->has('condType') && $request->condType == 'complex') {
                $cond->CondOperator = 'COMPLEX';
                $cond->save();
                if ($request->has('multConds') && is_array($request->multConds) && sizeof($request->multConds) > 0) {
                    foreach ($request->multConds as $val) {
                        $chk = SLConditionsVals::where('CondValCondID', $cond->CondID)
                            ->where('CondValValue', $val)
                            ->get();
                        if ($chk->isEmpty()) {
                            $tmpVal = new SLConditionsVals;
                            $tmpVal->CondValCondID    = $cond->CondID;
                            $tmpVal->CondValValue     = $val;
                            if ($request->has('multCondsNot') && in_array($val, $request->multCondsNot)) {
                                $tmpVal->CondValValue = (-1*$val);
                            }
                            $tmpVal->save();
                        }
                    }
                }
            } else {
                if ($request->has('setSelect')) {
                    $tmp = trim($request->setSelect);
                    if ($tmp == 'url-parameters') {
                        $cond->CondOperator = 'URL-PARAM';
                    } elseif (strpos($tmp, 'loop-') !== false) {
                        $cond->CondLoop = intVal(str_replace('loop-', '', $tmp));
                    } elseif (isset($this->tblI[$tmp])) {
                        $cond->CondTable = intVal($this->tblI[$tmp]);
                    }
                }
                if ($cond->CondOperator == 'URL-PARAM') {
                    $cond->CondOperDeet = $request->paramName;
                } elseif ($request->has('setFld')) {
                    $tmp = trim($request->setFld);
                    if (substr($tmp, 0, 6) == 'EXISTS') {
                        $cond->CondOperator = 'EXISTS' . substr($tmp, 6, 1);
                        $cond->CondOperDeet = intVal(substr($tmp, 7));
                    } else {
                        $cond->CondField = intVal($request->setFld);
                        if ($request->has('equals')) {
                            if ($request->get('equals') == 'equals') {
                                $cond->CondOperator = '{';
                            } else {
                                $cond->CondOperator = '}';
                            }
                        }
                    }
                }
                $cond->save();
                if ($cond->CondOperator == 'URL-PARAM') {
                    $tmpVal = new SLConditionsVals;
                    $tmpVal->CondValCondID = $cond->CondID;
                    $tmpVal->CondValValue  = $request->paramVal;
                    $tmpVal->save();
                } elseif ($request->has('vals') && is_array($request->vals) && sizeof($request->vals) > 0) {
                    foreach ($request->vals as $val) {
                        $tmpVal = new SLConditionsVals;
                        $tmpVal->CondValCondID = $cond->CondID;
                        $tmpVal->CondValValue  = $val;
                        $tmpVal->save();
                    }
                }
            }
            
            if ($request->has('CondPublicFilter') && intVal($request->get('CondPublicFilter')) == 1) {
                $cond->CondOpts *= 2;
            }
            $artsIn = [];
            for ($j=0; $j < 10; $j++) {
                if ($request->has('condArtUrl' . $j . '') && trim($request->get('condArtUrl' . $j . '')) != '') {
                    $artsIn[$j] = ['', trim($request->get('condArtUrl' . $j . ''))];
                    if ($request->has('condArtTitle' . $j . '') && trim($request->get('condArtTitle' . $j . '')) != ''){
                        $artsIn[$j][0] = trim($request->get('condArtTitle' . $j . ''));
                    }
                }
            }
            $articles = SLConditionsArticles::where('ArticleCondID', $cond->CondID)
                ->get();
            if (!$artsIn || sizeof($artsIn) == 0) {
                SLConditionsArticles::where('ArticleCondID', $cond->CondID)
                    ->delete();
            } else {
                $cond->CondOpts *= 3;
                foreach ($artsIn as $j => $a) {
                    $foundArt = false;
                    if ($articles->isNotEmpty()) {
                        foreach ($articles as $chk) {
                            if ($chk->ArticleURL == $a[1]) {
                                if ($chk->ArticleTitle != $a[0]) {
                                    $chk->ArticleTitle = $a[0];
                                    $chk->save();
                                }
                                $foundArt = true;
                            }
                        }
                    }
                    if (!$foundArt) {
                        $newArt = new SLConditionsArticles;
                        $newArt->ArticleCondID = $cond->CondID;
                        $newArt->ArticleTitle = $a[0];
                        $newArt->ArticleURL = $a[1];
                        $newArt->save();
                    }
                }
            }
            $cond->save();
        }
        return $cond;
    }
    
    public function loadTestsAB()
    {
        $this->condABs = [];
        $chk = SLConditions::where('CondOperator', 'AB TEST')
            ->where('CondTag', 'LIKE', '%AB Tree' . $this->treeID . '%')
            ->orderBy('CondTag', 'asc')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $cond) {
                $this->condABs[] = [$cond->CondID, $cond->CondDesc];
            }
        }
        return $this->condABs;
    }
    
    public function loadFldAbout($pref = 'Fld')
    {
        $chk = SLFields::where('FldDatabase', 3)
            ->select('FldName', 'FldNotes')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $f) {
                if ($f->FldNotes && trim($f->FldNotes) != '') {
                    $this->fldAbouts[$pref . $f->FldName] = $f->FldNotes;
                }
            }
        }
        return true;
    }
    
    public function loadSysTrees($type = 'forms')
    {
        if (!isset($this->sysTree[$type]) || !isset($this->sysTree[$type]["pub"]) 
            || empty($this->sysTree[$type]["pub"])) {
            $treeType = (($type == 'pages') ? 'Page' : 'Survey');
            $trees = SLTree::where('TreeType', $treeType)
                ->orderBy('TreeName', 'asc')
                ->select('TreeID', 'TreeName', 'TreeOpts')
                ->get();
            if ($trees->isNotEmpty()) {
                foreach ($trees as $i => $tree) {
                    $pubType = (($tree->TreeOpts%3 == 0) ? 'adm' : 'pub');
                    $this->sysTree[$type][$pubType][] = [$tree->TreeID, $tree->TreeName];
                }
            }
        }
        return true;
    }
    
    public function sysTreesDrop($preSel = -3, $type = 'forms', $pubPri = 'pub')
    {
        $this->loadSysTrees($type);
        $ret = '';
        if (in_array($pubPri, ['pub', 'all']) && sizeof($this->sysTree[$type]['pub']) > 0) {
            foreach ($this->sysTree[$type]['pub'] as $tree) {
                $ret .= '<option value="' . $tree[0] . '" ' . (($preSel == $tree[0]) ? 'SELECTED ' : '') . '>' 
                    . $tree[1] . (($type == 'page') ? ' (Page)' : '') . '</option>';
            }
        }
        if (in_array($pubPri, ['adm', 'all']) && sizeof($this->sysTree[$type]['adm']) > 0) {
            foreach ($this->sysTree[$type]['adm'] as $tree) {
                $ret .= '<option value="' . $tree[0] . '" ' . (($preSel == $tree[0]) ? 'SELECTED ' : '') . '>' 
                    . $tree[1] . ' (' . (($type == 'page') ? 'Page, ' : '') . 'Admin)</option>';
            }
        }
        return $ret;
    }
    
    public function loadProTips()
    {
        $cache = '$'.'this->proTips = [];' . "\n";
        $chk = SLDefinitions::where('DefDatabase', $this->dbID)
            ->where('DefSet', 'Tree Settings')
            ->where('DefSubset', 'LIKE', 'tree-' . $this->treeID . '-protip')
            ->orderBy('DefOrder', 'asc')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $set) {
                if (trim($set->DefDescription) != '') {
                    $cache .= '$'.'this->proTips[] = \'' . str_replace("'", "&#39;", $set->DefDescription) . '\';' 
                        . "\n";
                }
            }
        }
        return $cache;
    }
    
    public function loadTreeMojis()
    {
        if (empty($this->treeSettings)) {
            $chk = SLDefinitions::where('DefDatabase', $this->dbID)
                ->where('DefSet', 'Tree Settings')
                ->where('DefSubset', 'LIKE', 'tree-' . $this->treeID . '-%')
                ->orderBy('DefOrder', 'asc')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $set) {
                    $setting = str_replace('tree-' . $this->treeID . '-', '', $set->DefSubset);
                    if ($setting != 'protip') {
                        if (!isset($this->treeSettings[$setting])) {
                            $this->treeSettings[$setting] = [];
                        }
                        if ($setting == 'emojis') {
                            $names = explode(';', $set->DefValue);
                            $this->treeSettings[$setting][] = [
                                "id"     => $set->DefID,
                                "admin"  => ($set->DefIsActive%7 == 0),
                                "verb"   => $names[0],
                                "plural" => $names[1], 
                                "html"   => $set->DefDescription
                            ];
                        } else {
                            $this->treeSettings[$setting][] = $set->DefDescription;
                        }
                    }
                }
            }
        }
        return $this->treeSettings;
    }
    
    public function getEmojiName($defID = -3)
    {
        if ($defID > 0 && sizeof($this->treeSettings["emojis"]) > 0) {
            foreach ($this->treeSettings["emojis"] as $emo) {
                if ($emo["id"] == $defID) {
                    return $emo["verb"];
                }
            }
        }
        return '';
    }
    
    public function getSysStyles()
    {
        return SLDefinitions::where('DefDatabase', 1)
            ->where('DefSet', 'Style Settings')
            ->orderBy('DefOrder')
            ->get();
    }
    
    public function getCssColors()
    {
        $this->x["sysColor"] = [];
        $cssRaw = $this->getSysStyles();
        if ($cssRaw->isNotEmpty()) {
            foreach ($cssRaw as $c) {
                $this->x["sysColor"][$c->DefSubset] = $c->DefDescription;
            }
        }
        return $this->x["sysColor"];
    }
    
    public function getCssColor($name = '')
    {
        if (!isset($this->x["sysColor"])) {
            $this->getCssColors();
        }
        if (isset($this->x["sysColor"][$name])) {
            return $this->x["sysColor"][$name];
        }
        return '';
    }
    
    public function getCssColorsEmail()
    {
        $cssColors = $this->getCssColors();
        $cssColors["css-dump"] = '';
        $cssRaw = SLDefinitions::where('DefDatabase', 1)
                ->where('DefSet', 'Style CSS')
                ->where('DefSubset', 'email')
                ->first();
        if ($cssRaw && isset($cssRaw->DefDescription) > 0) {
            $cssColors["css-dump"] = $cssRaw->DefDescription;
        }
        return $cssColors;
    }
    
    public function swapSessMsg($page = '')
    {
        return str_replace('<!-- SessMsg -->', $this->getSessMsg(), $page);
    }
    
    public function getSessMsg()
    {
        $ret = '';
        if (session()->has('sessMsg')) {
            if (trim(session()->get('sessMsg')) != '') {
                if (strpos(session()->get('sessMsg'), 'class="alert') !== false) {
                    $ret .= session()->get('sessMsg');
                } else {
                    $ret .= '<div class="alert alert-dismissible w100 mB10 '
                        . ((session()->has('sessMsgType') && trim(session()->get('sessMsgType')) != '')
                            ? session()->get('sessMsgType') : 'alert-info') . ' ">'
                        . '<button type="button" class="close" data-dismiss="alert">×</button>' 
                        . session()->get('sessMsg') . '</div>';
                }
            }
            session()->forget('sessMsg');
            session()->forget('sessMsgType');
        }
        return $ret;
    }
    
    public function swapIfPublicID($pubID = -3, $tbl = '')
    {
        if (trim($tbl) == '') {
            $tbl = $this->coreTbl;
        }
        if ($this->tblHasPublicID($tbl)) {
            return $this->chkInPublicID($pubID, $tbl);
        }
        return $pubID;
    }
    
    public function tblHasPublicID($tbl = '')
    {
        if (trim($tbl) == '') {
            $tbl = $this->coreTbl;
        }
        if (!isset($this->x["tblHasPublicID"])) {
            $this->x["tblHasPublicID"] = [];
        }
        if (isset($this->x["tblHasPublicID"][$tbl])) {
            return $this->x["tblHasPublicID"][$tbl];
        }
        $this->x["tblHasPublicID"][$tbl] = false;
        if (isset($this->treeRow->TreeOpts) && $this->treeRow->TreeOpts%47 == 0 
            || (isset($this->reportTree["opts"]) && $this->reportTree["opts"]%47 == 0)) {
            $this->x["tblHasPublicID"][$tbl] = true;
        }
        if ($this->x["tblHasPublicID"][$tbl] && isset($this->tblI[$tbl])) {
            $chk = SLFields::where('FldTable', $this->tblI[$tbl])
                ->where('FldName', 'PublicID')
                ->first();
            if (!$chk) {
                $this->x["tblHasPublicID"][$tbl] = false;
            }
        }
        return $this->x["tblHasPublicID"][$tbl];
    }
    
    public function chkInPublicID($pubID = -3, $tbl = '')
    {
        if (trim($tbl) == '') {
            $tbl = $this->coreTbl;
        }
        if (intVal($pubID) <= 0 || !$this->tblHasPublicID($tbl)) {
            return $pubID;
        }
        $pubIdFld = $this->tblAbbr[$tbl] . 'PublicID';
        eval("\$idChk = " . $this->modelPath($tbl) . "::where('" . $pubIdFld . "', '" . intVal($pubID) . "')->first();");
        if ($idChk) {
            return $idChk->getKey();
        }
        return $pubID;
    }
    
    public function genNewCorePubID($tbl = '')
    {
        if (trim($tbl) == '') {
            $tbl = $this->coreTbl;
        }
        if (isset($this->tblAbbr[$tbl])) {
            $pubIdFld = $this->tblAbbr[$tbl] . 'PublicID';
            eval("\$idChk = " . $this->modelPath($tbl) . "::orderBy('" . $pubIdFld . "', 'desc')->first();");
            if (!$idChk || !isset($idChk->{ $pubIdFld }) || intVal($idChk->{ $pubIdFld }) <= 0) {
                return 1;
            }
            return (1+intVal($idChk->{ $pubIdFld }));
        }
        return 1;
    }
    
    public function getDbName($dbID = -3)
    {
        if ($dbID <= 0 || sizeof($this->allDbs) == 0) {
            return '';
        }
        foreach ($this->allDbs as $db) {
            if ($db["id"] == $dbID) {
                return $db["name"];
            }
        }
        return '';
    }
    
    public function allTreeDropOpts($preSel = -3)
    {
        $ret = '<option value="-3" ' . ((intVal($preSel) <= 0) ? 'SELECTED' : '') . ' >select form tree</option>';
        if (sizeof($this->allTrees) > 0) {
            foreach ($this->allTrees as $dbID => $trees) {
                if (sizeof($trees) > 0) {
                    $ret .= '<option value="-3" DISABLED >' . $this->getDbName($dbID) . ' Database...</option>';
                    foreach ($trees as $i => $tree) {
                        $ret .= '<option ' . ((intVal($preSel) == $tree["id"]) ? 'SELECTED' : '') . ' value="' 
                            . $tree["id"] . '" > - ' . $tree["name"] . (($tree["opts"]%3 == 0) ? ' (Admin)' : '') 
                            . '</option>';
                    }
                }
            }
        }
        return $ret;
    }
    
    public function getTreeList()
    {
        $trees = [];
        $chk = SLTree::where('TreeType', 'Survey')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $i => $t) {
                $tblChk = SLTables::find($t->TreeCoreTable);
                $coreTbl = (($tblChk && isset($tblChk->TblName)) ? $tblChk->TblName : '');
                $trees[] = [ $t->TreeID, $t->TreeName, $t->TreeSlug, $coreTbl ];
            }
        }
        return $trees;
    }
    
    public function treeBaseUrl($incDomain = false, $hideHttp = false)
    {
        $url = (($incDomain) ? $this->sysOpts["app-url"] : '');
        if ($hideHttp) {
            $url = str_replace('http://', '', str_replace('http://www.', '', str_replace('https://', '', 
                str_replace('https://www.', '', $url))));
        }
        if ($this->treeRow->TreeType == 'Page') {
            if ($this->treeIsAdmin) {
                return $url . '/dash/';
            } else {
                return $url . '/';
            }
        } else {
            if (isset($this->treeRow->TreeSlug)) {
                if ($this->treeIsAdmin) {
                    return $url . '/dash/' . $this->treeRow->TreeSlug . '/';
                } else {
                    return $url . '/u/' . $this->treeRow->TreeSlug . '/';
                }
            }
        }
        return $url . '/';
    }
    
    public function getNodePageName($currNode = -3)
    {
        if (!isset($this->x["nodeNames"])) {
            $this->x["nodeNames"] = [];
        }
        if ($currNode > 0) {
            if (!isset($this->x["nodeNames"][$currNode])) {
                $this->x["nodeNames"][$currNode] = '';
                $row = SLNode::find($currNode);
                if ($row && isset($row->NodeID)) {
                    $node = new TreeNodeSurv();
                    $node->fillNodeRow($currNode, $row);
                    if (isset($node->nodeRow) && isset($node->nodeRow->NodeID)) {
                        if (isset($node->extraOpts["meta-title"]) && trim($node->extraOpts["meta-title"]) != '') {
                            $this->x["nodeNames"][$currNode] = $node->extraOpts["meta-title"];
                        }
                        if (isset($node->NodePromptNotes)) {
                            $this->x["nodeNames"][$currNode] = $node->NodePromptNotes;
                        }
                    }
                }
            }
            return $this->x["nodeNames"][$currNode];
        }
        return '';
    }
    
    public function getCycSffx()
    {
        return trim($this->currCyc["cyc"][1]) . trim($this->currCyc["res"][1]) . trim($this->currCyc["tbl"][1]);
    }
    
    public function getPckgProj()
    {
        if (isset($this->sysOpts["cust-package"]) && strpos($this->sysOpts["cust-package"], '/')) {
            $split = explode('/', $this->sysOpts["cust-package"]);
            return $split[1];
        }
        return '';
    }
    
    public function setSEO($metaTitle = '', $metaDesc = '', $metaKeywords = '', $metaImg = '')
    {
        if (trim($metaTitle) != '') {
            $GLOBALS['SL']->sysOpts['meta-title'] = $metaTitle . ' - ' . $GLOBALS['SL']->sysOpts['meta-title'];
        }
        if (trim($metaDesc) != '') {
            $GLOBALS['SL']->sysOpts['meta-desc'] = $metaDesc;
        }
        if (trim($metaKeywords) != '') {
            $GLOBALS['SL']->sysOpts['meta-keywords'] = $metaKeywords;
        }
        if (trim($metaImg) != '') {
            $GLOBALS['SL']->sysOpts['meta-img'] = $metaImg;
        }
        return true;
    }
    
    public function loadTreeNodeStatTypes()
    {
        $this->x["dataStatTypes"] = [
            "quali" => [ 'Text', 'Long Text', 'Email', 'Uploads' ],
            "choic" => [ 'Radio', 'Checkbox', 'Drop Down', 'Gender', 'Gender Not Sure', 'U.S. States', 'Countries' ],
            "quant" => [ 'Text:Number', 'Slider', 'Date', 'Date Picker', 'Date Time', 'Time', 'Feet Inches' ]
            ];
        return $this->x["dataStatTypes"];
    }
    
    public function resetTreeNodeStats()
    {
        $this->loadTreeNodeStatTypes();
        $this->x["dataTypeStats"] = [
            "quali" => [ "all" => 0, "req" => 0 ],
            "choic" => [ "all" => 0, "req" => 0 ],
            "quant" => [ "all" => 0, "req" => 0 ],
            "flds"  => [ ]
            ];
        $this->x["qTypeStats"] = [
            "quali" => [ "all" => 0, "req" => 0 ],
            "choic" => [ "all" => 0, "req" => 0 ],
            "quant" => [ "all" => 0, "req" => 0 ],
            "nodes" => [ "tot" => 0, "loops" => 0, "loopNodes" => 0 ]
            ];
        return true;
    }
    
    public function logTreeNodeStat($node = [])
    {
        if ($node && isset($node->nodeType)) {
            $type = '';
            if (in_array($node->nodeType, $this->x["dataStatTypes"]["quali"])) {
                $type = 'quali';
            } elseif (in_array($node->nodeType, $this->x["dataStatTypes"]["choic"])) {
                $type = 'choic';
            } elseif (in_array($node->nodeType, $this->x["dataStatTypes"]["quant"])) {
                $type = 'quant';
            }
            if ($type != '') {
                if (isset($node->dataStore) && !in_array($node->dataStore, $this->x["dataTypeStats"]["flds"])) {
                    $this->x["dataTypeStats"]["flds"][] = $node->dataStore;
                    $this->x["dataTypeStats"][$type]["all"]++;
                    if ($node->isRequired()) {
                        $this->x["dataTypeStats"][$type]["req"]++;
                    }
                }
                $this->x["qTypeStats"][$type]["all"]++;
                if ($node->isRequired()) {
                    $this->x["qTypeStats"][$type]["req"]++;
                }
            }
        }
        return true;
    }
    
    public function printTreeNodeStats($isPrint = false, $isAll = true, $isAlt = true)
    {
        if (isset($this->x["dataTypeStats"]) && isset($this->x["dataTypeStats"]["quali"])) {
            return view('vendor.survloop.inc-tree-node-type-stats' , [
                "isPrint"       => $isPrint,
                "isAll"         => $isAll,
                "isAlt"         => $isAlt,
                "dataTypeStats" => $this->x["dataTypeStats"],
                "qTypeStats"    => $this->x["qTypeStats"]
                ])->render();
        }
        return '';
    }
    
    public function chkMissingReportFlds($treeID = -3)
    {
        $ret = '';
        if ($treeID <= 0) $treeID = $this->treeID;
        $flds1 = $flds2 = [];
        $tree1 = SLTree::find($treeID);
        if ($tree1 && isset($tree1->TreeType) && $tree1->TreeType == 'Page' && $tree1->TreeOpts%13 == 0) { // is report
            $tree2 = SLTree::where('TreeType', 'Survey')
                ->where('TreeCoreTable', $tree1->TreeCoreTable)
                ->orderBy('TreeID', 'desc')
                ->first();
            if ($tree2 && isset($tree2->TreeID)) {
                $chk = SLNode::where('NodeTree', $tree1->TreeID)
                    ->select('NodeDataStore')
                    ->get();
                if ($chk->isNotEmpty()) {
                    foreach ($chk as $i => $node) {
                        if (isset($node->NodeDataStore) && trim($node->NodeDataStore) != '' 
                            && !in_array($node->NodeDataStore, $flds1)) {
                            $flds1[] = $node->NodeDataStore;
                        }
                    }
                }
                $chk = SLNode::where('NodeTree', $tree2->TreeID)
                    ->orderBy('NodeDataStore', 'asc')
                    ->select('NodeDataStore')
                    ->get();
                if ($chk->isNotEmpty()) {
                    foreach ($chk as $i => $node) {
                        if (isset($node->NodeDataStore) && trim($node->NodeDataStore) != '' 
                            && !in_array($node->NodeDataStore, $flds2)) {
                            $flds2[] = $node->NodeDataStore;
                        }
                    }
                }
            }
        }
        if (sizeof($flds2) > 0) {
            foreach ($flds2 as $fld2) {
                if (!in_array($fld2, $flds1)) {
                    $ret .= ', <span class="mL20">' . $fld2 . '</span>';
                }
            }
        }
        if (trim($ret) != '') {
            $ret = '<div class="mT20"><b>Fields Missing From Primary Survey:</b>' . substr($ret, 1) . '</div>';
        }
        return $ret;
    }
    
    public function isHomestead()
    {
        return (strpos($this->sysOpts["app-url"], 'homestead.test') !== false);
    }
    
    public function getParentDomain()
    {
        if (isset($this->sysOpts["parent-website"]) && trim($this->sysOpts["parent-website"]) != '') {
            return $this->printURLdomain($this->sysOpts["parent-website"]);
        }
        return '';
    }
    
    public function sysHas($type)
    {
        return (isset($this->sysOpts["has-" . $type]) && intVal($this->sysOpts["has-" . $type]) == 1);
    }
    
    public function loadUsrTblRow()
    {
        return SLTables::where('TblDatabase', $this->dbID)
            ->where('TblEng', 'Users')
            ->first();
    }
    
    public function chkTableExists($coreTbl, $userTbl = null)
    {
        $chk = DB::select( DB::raw("SHOW TABLES LIKE '" . $this->dbRow->DbPrefix . $coreTbl->TblName . "'") );
        if (!$chk || sizeof($chk) == 0) {
            return false;
        }
        return true;
    }
    
    public function initCoreTable($coreTbl, $userTbl = null)
    {
        //if ($this->dbID == 3 && $this->sysOpts["cust-abbr"] != 'SurvLoop') return false;
        if (!$coreTbl || !isset($coreTbl->TblID)) {
            return false;
        }
        if (!$userTbl) {
            $userTbl = $this->loadUsrTblRow();
        }
        if ($coreTbl->TblID == $userTbl->TblID) {
            return false;
        }
        $coreFlds = [ [
                "FldType" => 'INT', 
                "FldEng"  => 'User ID', 
                "FldName" => 'UserID', 
                "FldDesc" => 'Indicates the unique User ID number of the User '
                    . 'owning the data stored in this record for this Experience.' 
            ], [ 
                "FldType" => 'INT', 
                "FldEng"  => 'Experience Node Progress', 
                "FldName" => 'SubmissionProgress', 
                "FldDesc" => 'Indicates the unique Node ID number of the last '
                    . 'Experience Node loaded during this User\'s Experience.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'Tree Version Number', 
                "FldName" => 'TreeVersion', 
                "FldDesc" => 'Stores the current version number of this User Experience, important for tracking bugs.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'A/B Testing Version', 
                "FldName" => 'VersionAB', 
                "FldDesc" => 'Stores a complex string reflecting all A/B Testing '
                    . 'variations in effect at the time of this User\'s Experience of this Node.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'Unique String For Record', 
                "FldName" => 'UniqueStr', 
                "FldDesc" => 'This unique string is for cases when including the record ID number is not appropriate.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'IP Address', 
                "FldName" => 'IPaddy', 
                "FldDesc" => 'Encrypted IP address of the current user.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'Using Mobile Device', 
                "FldName" => 'IsMobile', 
                "FldDesc" => 'Indicates whether or not the current user is interacting via a mobile deviced.' 
        ] ];
        foreach ($coreFlds as $f) {
            $chk = SLFields::where('FldDatabase', $this->dbID)
                ->where('FldTable', $coreTbl->TblID)
                ->where('FldName', $f["FldName"])
                ->get();
            if ($chk->isEmpty()) {
                $fld = new SLFields;
                $fld->FldDatabase         = $this->dbID;
                $fld->FldTable            = $coreTbl->TblID;
                $fld->FldEng              = $f["FldEng"];
                $fld->FldName             = $f["FldName"];
                $fld->FldDesc             = $f["FldDesc"];
                $fld->FldSpecType         = 'Replica';
                $fld->FldType             = $f["FldType"];
                if ($f["FldType"] == 'INT') {
                    $fld->FldDataType     = 'Numeric';
                    $fld->FldCharSupport  = ',Numbers,';
                }
                if ($f["FldName"] == 'UserID') {
                    $fld->FldKeyType      = ',Foreign,';
                    $fld->FldForeignTable = $userTbl->TblID;
                }
                // Options: Auto-Managed By SurvLoop; Internal Use not in XML
                $fld->FldOpts             = 39;
                $fld->save();
                if ($this->chkTableExists($coreTbl, $userTbl)) {
                    $tblQry = "ALTER TABLE  `" . $this->dbRow->DbPrefix . $coreTbl->TblName . "` ADD `" 
                        . $coreTbl->TblAbbr . $f["FldName"] . "` ";
                    switch ($f["FldName"]) {
                        case 'UserID':             $tblQry .= "bigint(20) unsigned"; break;
                        case 'SubmissionProgress': $tblQry .= "int(11)"; break;
                        case 'UniqueStr':          $tblQry .= "varchar(50)"; break;
                        case 'IsMobile':           $tblQry .= "int(1) NULL"; break;
                        case 'TreeVersion':
                        case 'VersionAB':
                        case 'IPaddy':             $tblQry .= "varchar(255)"; break;
                    }
                    DB::statement($tblQry . " NULL;");
                }
            }
        }
        $this->createTableIfNotExists($coreTbl, $userTbl);
        $this->installNewModel($coreTbl, true);
        return true;
    }
    
}