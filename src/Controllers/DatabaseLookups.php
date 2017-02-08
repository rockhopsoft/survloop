<?php
namespace SurvLoop\Controllers;

use DB;
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
use App\Models\SLSessLoops;
use App\Models\SLConditions;
use App\Models\SLConditionsVals;

use SurvLoop\Controllers\StatesUS;

class DatabaseLookups
{
    
    public $dbID           = 1;
    public $dbRow          = [];
    public $treeID         = 1;
    public $treeRow        = [];
    public $treeName       = '';
    public $coreTbl        = '';
    public $treeXmlID      = -3;
    
    public $sysOpts        = [];
    public $userRoles      = [];
    
    public $tblModels      = [];
    public $tbls           = [];
    public $tbl            = [];
    public $tblID          = [];
    public $tblAbbr        = [];
    public $tblOpts        = [];
    public $fldTypes       = [];
    public $defValues      = [];
    
    public $foreignKeysIn  = [];
    public $foreignKeysOut = [];
    
    public $dataLoops      = [];
    public $dataLoopNames  = [];
    public $dataSubsets    = [];
    public $dataHelpers    = [];
    public $dataLinksOn    = [];
    
    // User's position within potentially nested loops
    public $sessLoops      = [];
    public $closestLoop    = [];
    public $tblLoops       = [];
    
    public $states         = [];
    
    public $fldAbouts      = [];
    public $instructs      = [];
    public $debugOn        = true;
    
    function __construct($dbID = 1, $treeID = 1, Request $request = NULL)
    {
        $this->dbID     = $dbID;
        $this->dbRow     = SLDatabases::find($this->dbID);
        $this->treeID     = $treeID;
        $this->treeRow     = SLTree::where('TreeID', $this->treeID)
            ->where('TreeDatabase', $this->dbID)
            ->first();
        if (!$this->treeRow || !isset($this->treeRow->TreeID)) {
            $this->treeRow = SLTree::where('TreeDatabase', $this->dbID)
                ->where('TreeType', 'Primary Public')
                ->first();
            if (isset($this->treeRow->TreeID)) {
                $this->treeID = $this->treeRow->TreeID;
            }
        }
        if (isset($this->dbRow->DbName) && isset($this->treeRow->TreeName)) {
            $this->treeName    = str_replace($this->dbRow->DbName, 
                str_replace('_', '', $this->dbRow->DbPrefix), 
                    $this->treeRow->TreeName);
        }

        $this->sysOpts = array( "cust-abbr" => 'survloop' );
        
        $this->loadDBFromCache($request);
        
        $this->dataLoops = [];
        $this->dataLoopNames = [];
        $dataLoops = SLDataLoop::where('DataLoopTree', $this->treeID)
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
        $this->dataSubsets = SLDataSubsets::where('DataSubTree', $this->treeID)
            ->orderBy('DataSubTbl', 'asc')
            ->orderBy('DataSubSubTbl', 'asc')
            ->get();
        $this->dataHelpers = SLDataHelpers::where('DataHelpTree', $this->treeID)
            ->orderBy('DataHelpParentTable', 'asc')
            ->orderBy('DataHelpTable', 'asc')
            ->get();
        
        $this->states = new StatesUS;
        
        $GLOBALS["errors"] = '';
        
        return true;
    }
    
    
    public function loadDBFromCache(Request $request = NULL)
    {
        $cacheFile = '/cache/db-load-' . $this->dbID . '.php';
        if ((!$request || !$request->has('refresh')) && file_exists($cacheFile)) {
            $content = Storage::get($cacheFile);
            eval($content);
        } else {
            $cache = '// Auto-generated loading cache from /SurvLoop/Controllers/DatabaseLookups.php' . "\n\n";
            $sys = SLDefinitions::where('DefDatabase', $this->dbID)
                ->where('DefSet', 'System Settings')
                ->get();
            if (!$sys || sizeof($sys) == 0) {
                $sys = SLDefinitions::where('DefDatabase', 1)
                    ->where('DefSet', 'System Settings')
                    ->get();
            }
            if ($sys && sizeof($sys) > 0) {
                foreach ($sys as $s) {
                    $cache .= '$'.'this->sysOpts[\'' . $s->DefSubset . '\'] = \''
                        . str_replace("'", "\\'", trim($s->DefDescription)) . '\';' . "\n";
                }
            }
            if (isset($this->dbRow->DbPrefix)) {
                $coreTbl = '';
                // Establishing database table-field lookup arrays
                $tbls = SLTables::where('TblDatabase', $this->dbID)
                    ->orderBy('TblOrd', 'asc')
                    ->get();
                foreach ($tbls as $tbl) {
                    if (isset($this->treeRow->TreeCoreTable) && $tbl->TblID == $this->treeRow->TreeCoreTable) {
                        $coreTbl = $tbl->TblName;
                    }
                    $cache .= '$'.'this->tbls[] = ' . $tbl->TblID . ';' . "\n"
                        . '$'.'this->tblI[\'' . $tbl->TblName . '\'] = ' . intVal($tbl->TblID) . ';' . "\n"
                        . '$'.'this->tbl[' . $tbl->TblID . '] = \'' . $tbl->TblName . '\';' . "\n"
                        . '$'.'this->tblEng[' . $tbl->TblID . '] = \'' . str_replace("'", "\\'", $tbl->TblEng) . '\';' . "\n"
                        . '$'.'this->tblOpts[' . $tbl->TblID . '] = ' . intVal($tbl->TblOpts) . ';' . "\n"
                        . '$'.'this->tblAbbr[\'' . $tbl->TblName . '\'] = \'' . $tbl->TblAbbr . '\';' . "\n"
                        . '$'.'this->fldTypes[\'' . $tbl->TblName . '\'] = [];' . "\n"
                        . '$'.'this->fldTypes[\'' . $tbl->TblName . '\'][\'' . $tbl->TblAbbr . 'ID\'] = \'INT\';' . "\n"
                        . '$'.'this->tblModels[\'' . $tbl->TblName . '\'] = \'' 
                        . str_replace('_', '', $this->dbRow->DbPrefix . $tbl->TblName) . '\';' . "\n";
                    
                    // temporarily loading for the sake of cache creation...
                    $this->tbl[$tbl->TblID] = $tbl->TblName;
                    $this->tblAbbr[$tbl->TblName] = $tbl->TblAbbr;
                }
                $cache .= '$'.'this->coreTbl = \'' . $coreTbl . '\';' . "\n";
                
                $flds = SLFields::select()
                    ->where('FldDatabase', $this->dbID)
                    ->where('FldTable', '>', 0)
                    ->get();
                foreach ($flds as $fld) {
                    if (isset($this->tbl[$fld->FldTable])) {
                        $cache .= '$'.'this->fldTypes[\'' . $this->tbl[$fld->FldTable] . '\'][\''
                            . $this->tblAbbr[$this->tbl[$fld->FldTable]] . $fld->FldName
                            . '\'] = \'' . $fld->FldType . '\';' . "\n";
                    }
                }
                
                $this->dataLinksOn = [];
                $linksChk = SLDataLinks::where('DataLinkTree', $this->treeID)
                    ->get();
                if ($linksChk && sizeof($linksChk) > 0) {
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
                        $cache .= '$'.'this->dataLinksOn[' . $tbl . '] = [ '
                            . '\'' . $map[0] . '\', \'' . $map[1] . '\', '
                            . '\'' . $map[2] . '\', \'' . $map[3] . '\', '
                            . '\'' . $map[4] . '\' ];' . "\n";
                    }
                }
            } // end if (isset($this->dbRow->DbPrefix))

            eval($cache);
            if (file_exists($cacheFile)) Storage::delete($cacheFile);
            Storage::put($cacheFile, $cache);
        }
        return true;
    }
    
    
    
    public function modelPath($tbl = '')
    {
        return "App\\Models\\" . $this->tblModels[$tbl];
    }
    
    public function isCoreTbl($tblID)
    {
        if (!isset($this->treeRow->TreeCoreTable)) return false;
        return ($tblID == $this->treeRow->TreeCoreTable);
    }
    
    public function dbFullSpecs()
    {
        return ($this->dbRow->DbOpts%3 > 0);
    }
    
    
    public function isStepLoop($loop)
    {
        return (isset($this->dataLoops[$loop]) && intVal($this->dataLoops[$loop]->DataLoopIsStep) == 1);
    }
    
    public function loadSessLoops($sessID)
    {
        $this->sessLoops = SLSessLoops::where('SessLoopSessID', $sessID)
            ->orderBy('SessLoopID', 'desc')
            ->get();
        $this->closestLoop = [ "loop" => '', "itemID" => -3, "obj" => [] ];
        if ($this->sessLoops && isset($this->sessLoops[0])) {
            $this->closestLoop = [
                "loop"   => $this->sessLoops[0]->SessLoopName,         
                "itemID" => $this->sessLoops[0]->SessLoopItemID,
                "obj"    => $this->dataLoops[$this->sessLoops[0]->SessLoopName]
            ];
        }
        return $this->sessLoops;
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
                if ($loopID == $loop->DataLoopID) return $loop->DataLoopPlural;
            }
        }
        return '';
    }
    
    
    public function fldForeignKeyTbl($tbl, $fld)
    {
        if (trim($tbl) == '' || trim($fld) == '') return '';
        $fld = SLFields::select('FldForeignTable')
            ->where('FldTable', $this->tblI[$tbl])
            ->whereIn('FldName', [$fld, str_replace($this->tblAbbr[$tbl], '', $fld)])
            ->where('FldForeignTable', '>', 0)
            ->first();
        if ($fld && sizeof($fld) > 0) return $this->tbl[$fld->FldForeignTable];
        return '';
    }
    
    public function getForeignLnk($tbl1, $tbl2 = -3)
    {
        if ($tbl2 <= 0) $tbl2 = $this->treeRow->TreeCoreTable;
        $fld = SLFields::select('FldName')
            ->where('FldTable', $tbl1)
            ->where('FldForeignTable', $tbl2)
            ->first();
        if ($fld && isset($fld->FldName)) return trim($fld->FldName);
        return '';
    }
    
    public function getForeignOpts($preSel = '', $opts = 'Subset')
    {
        $retVal = '<option value="" ' . (($preSel == '') ? 'SELECTED' : '') . ' >parent - field - child</option>
        <option value=""></option>' . "\n";
        $flds = SLFields::select('SL_Fields.FldTable', 'SL_Fields.FldName', 'SL_Fields.FldForeignTable')
            ->join('SL_Tables', 'SL_Tables.TblID', '=', 'SL_Fields.FldForeignTable')
            ->where('FldDatabase',         $this->dbID)
            ->where('FldTable',         '>', 0)
            ->where('FldForeignTable',     '>', 0)
            ->orderBy('SL_Tables.TblName', 'asc')
            ->get();
        if ($flds && sizeof($flds) > 0) {
            foreach ($flds as $fld) {
                if (isset($this->tbl[$fld->FldTable]) && isset($this->tbl[$fld->FldForeignTable])) {
                    $lnkMap = $this->tbl[$fld->FldForeignTable] . '::' 
                        . $this->tbl[$fld->FldTable] . ':' 
                        . $this->tblAbbr[$this->tbl[$fld->FldTable]] . $fld->FldName;
                    $retVal .= '<option value="' . $lnkMap . '" ' 
                        . (($preSel == $lnkMap) ? 'SELECTED' : '') . ' >' 
                        . $this->tbl[$fld->FldForeignTable] . ' &larr; ' 
                        . $this->tblAbbr[$this->tbl[$fld->FldTable]] . $fld->FldName 
                        . ' &larr; ' . $this->tbl[$fld->FldTable] . '
                        </option>' . "\n";
                } else {
                    $retVal .= '<option value="">** Warning ** not found: ' 
                        . $fld->FldTable . ' * ' . $fld->FldForeignTable . '</option>';
                }
            }
        }
        if ($opts == 'Subset')
        {
            $flds = SLFields::select('SL_Fields.FldTable', 'SL_Fields.FldName', 'SL_Fields.FldForeignTable')
                ->join('SL_Tables', 'SL_Tables.TblID', '=', 'SL_Fields.FldTable')
                ->where('FldDatabase',         $this->dbID)
                ->where('FldTable',         '>', 0)
                ->where('FldForeignTable',     '>', 0)
                ->orderBy('SL_Tables.TblName', 'asc')
                ->get();
            if ($flds && sizeof($flds) > 0) {
                foreach ($flds as $fld) {
                    $lnkMap = $this->tbl[$fld->FldTable] . ':' . $this->tblAbbr[$this->tbl[$fld->FldTable]] . $fld->FldName 
                        . ':' . $this->tbl[$fld->FldForeignTable] . ':';
                    $retVal .= '<option value="' . $lnkMap . '" ' . (($preSel == $lnkMap) ? 'SELECTED' : '') . ' >' 
                        . $this->tbl[$fld->FldTable] . ' &rarr; ' 
                        . $this->tblAbbr[$this->tbl[$fld->FldTable]] . $fld->FldName 
                        . ' &rarr; ' . $this->tbl[$fld->FldForeignTable] . '
                        </option>' . "\n";
                }
            }
        }
        return $retVal;
    }
    
    
    // returns array(Table 1, Foreign Key 1, Linking Table, Foreign Key 2, Table 2)
    public function getLinkTblMap($linkTbl = -3)
    {
        if ($linkTbl <= 0) return array();
        $foreigns = SLFields::select('FldName', 'FldForeignTable')
            ->where('FldDatabase', $this->dbID)
            ->where('FldTable', $linkTbl)
            ->where('FldForeignTable', '>', 0)
            ->orderBy('FldOrd', 'asc')
            ->orderBy('FldEng', 'asc')
            ->get();
        if ($foreigns && sizeof($foreigns) == 2) {
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
        return array();
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
    
    
    
    protected function getDataSetRow($loopName)
    {
        if ($loopName == '' || !isset($this->dataLoops[$loopName])) return array();
        return $this->dataLoops[$loopName];
    }

    public function loadDefinitions($subset)
    {
        if (!isset($this->defValues[$subset])) {
            $this->defValues[$subset] = SLDefinitions::where('DefSubset', $subset)
                ->where('DefSet', 'Value Ranges')
                ->orderBy('DefOrder', 'asc')
                ->select('DefID', 'DefValue')
                ->get();
        }
        return true;
    }
    
    public function getDefID($subset = '', $value = '')
    {
        $this->loadDefinitions($subset);
        if (sizeof($this->defValues[$subset]) > 0) {
            foreach ($this->defValues[$subset] as $def) {
                if ($def->DefValue == $value) return $def->DefID;
            }
        }
        return -3;
    }
    
    public function getDefValById($id = -3)
    {
        if ($id <= 0) return '';
        $def = SLDefinitions::find($id);
        if ($def && isset($def->DefValue)) return trim($def->DefValue);
        return '';
    }
    
    public function getDefValue($subset = '', $id = '')
    {
        $this->loadDefinitions($subset);
        if (sizeof($this->defValues[$subset]) > 0) {
            foreach ($this->defValues[$subset] as $def) {
                if ($def->DefID == $id) return $def->DefValue;
            }
        }
        return '';
    }
    
    public function getDefSet($subset = '')
    {
        $this->loadDefinitions($subset);
        if (sizeof($this->defValues[$subset]) > 0) {
            return $this->defValues[$subset];
        }
        return array();
    }
    
    
    
    
    public function tablesDropdown($preSel = '', $instruct = '', $prefix = '', $disableBlank = false)
    {
        $retVal = '<option value="" ' . (($preSel == "") ? 'SELECTED' : '') 
            . (($disableBlank) ? ' DISABLED ' : '') . ' >' . $instruct . '</option>' . "\n";
        foreach ($this->tblAbbr as $tblName => $tblAbbr) {
            $retVal .= '<option value="' . $tblName.'" ' . (($preSel == $tblName) ? 'SELECTED' : '') 
                . ' >' . $prefix . $tblName.'</option>' . "\n";
        }
        return $retVal;
    }
    
    // if $keys is 0 don't include primary keys; if $keys is 1 show primary keys; if $keys is -1 show only foreign keys; 
    public function fieldsDropdown($preSel = '', $keys = 2)
    {
        $retVal = '<option value="" ' 
            . ((trim($preSel) == '') ? 'SELECTED' : '') . ' ></option>' . "\n";
        if ($keys > 0) {
            foreach ($this->tblAbbr as $tblName => $tblAbbr) {
                $retVal .= '<option value="' . $tblName.':'. $tblAbbr . 'ID" ' 
                    . (($preSel == $tblName.':'. $tblAbbr . 'ID') ? 'SELECTED' : '') 
                    . ' >' . $tblName.' : '. $tblAbbr . 'ID (primary key)</option>' . "\n";
            }
        }
        $flds = [];
        $qman = "SELECT t.`TblName`, t.`TblAbbr`, f.`FldName`, f.`FldType`, f.`FldForeignTable` 
            FROM `SL_Fields` f 
            LEFT OUTER JOIN `SL_Tables` t ON f.`FldTable` LIKE t.`TblID` 
            WHERE f.`FldTable` > '0' [[EXTRA]] 
            ORDER BY t.`TblName`, f.`FldName`";
        if ($keys == -1) $flds = DB::select( DB::raw( str_replace("[[EXTRA]]", "AND f.`FldForeignTable` > '0'", $qman) ) );
        else $flds = DB::select( DB::raw( str_replace("[[EXTRA]]", "", $qman) ) );
        if ($flds && sizeof($flds) > 0) {
            foreach ($flds as $fld) $retVal .= $this->fieldsDropdownOption($fld, $preSel);
        }
        return $retVal;
    }
    
    public function fieldsDropdownOption($fld, $preSel = '', $valID = false, $prfx = '')
    {
        return  '<option value="' . (($valID) ? $fld->FldID 
                : $fld->TblName . ':' . $fld->TblAbbr . $fld->FldName) . '" ' 
            . (($preSel == $fld->TblName.':'. $fld->TblAbbr . $fld->FldName) ? 'SELECTED' : '') 
            . ' >' . $prfx . $fld->TblName.' : '. $fld->TblAbbr . $fld->FldName 
            . ' ('. (($fld->FldForeignTable > 0) ? 'foreign key' 
                : strtolower($fld->FldType)) . ')</option>' . "\n";
    }
    
    public function allDefsDropdown($preSel = '')
    {
        $retVal = '<option value="" ' 
            . (($preSel == "") ? 'SELECTED' : '') . ' ></option>' . "\n";
        $defs = SLDefinitions::select('DefSubset', 'DefID', 'DefValue')
            ->where('DefSet', 'Value Ranges')
            ->orderBy('DefSubset', 'asc')
            ->orderBy('DefOrder', 'asc')
            ->get();
        if ($defs && sizeof($defs) > 0) {
            foreach ($defs as $def) {
                $retVal .= '<option value="' . $def->DefID.'" ' 
                    . (($preSel == $def->DefID) ? 'SELECTED' : '') . ' >' 
                    . $def->DefSubset . ': ' . $def->DefValue . '</option>' . "\n";
            }
        }
        return $retVal;
    }
    
    
    
    public function getAllSetTables($tblIn = '')
    {
        $tbls = [];
        $tblID = intVal($tblIn);
        if (strpos($tblIn, 'loop-') !== false && sizeof($this->dataLoops) > 0) {
            $loopID = intVal(str_replace('loop-', '', $tblIn));
            foreach ($this->dataLoops as $loopName => $loopRow) {
                if ($loopRow->id == $loopID) {
                    $tblID = $this->tblI[$loopRow->DataLoopTable];
                }
            }
        }
        else $tblID = $this->tblI[$tblIn];
        $tbls = $this->getSubsetTables($tblID, $tbls);
        return $tbls;
    }
    
    public function getSubsetTables($tbl1 = -3, $tbls = array())
    {
        if ($tbl1 > 0 && !in_array($tbl1, $tbls))
        {
            $tbls[] = $tbl1;
            if (isset($this->dataSubsets) && sizeof($this->dataSubsets) > 0) {
                foreach ($this->dataSubsets as $subset) {
                    if ($tbl1 == $this->tblI[$subset->tbl]) {
                        $tbls = $this->getSubsetTables($this->tblI[$subset->subTbl], $tbls);
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
    
    public function fieldsTblsDropdown($tbls = array(), $preSel = '', $prfx = '')
    {
        $retVal = '';
        $prevTbl = -3;
        $flds = DB::select( DB::raw( "SELECT t.`TblName`, t.`TblAbbr`, 
                f.`FldID`, f.`FldName`, f.`FldType`, f.`FldForeignTable`, f.`FldTable` 
            FROM `SL_Fields` f 
            LEFT OUTER JOIN `SL_Tables` t ON f.`FldTable` LIKE t.`TblID` 
            WHERE f.`FldTable` IN ('" . implode("', '", $tbls) . "')  
            ORDER BY t.`TblName`, f.`FldName`" ) );
        if ($flds && sizeof($flds) > 0) {
            foreach ($flds as $fld) {
                if ($prevTbl != $fld->FldTable) $retVal .= '<option value=""></option>' . "\n";
                $retVal .= $this->fieldsDropdownOption($fld, $preSel, true, $prfx) . "\n";
                $prevTbl = $fld->FldTable;
            }
        }
        return $retVal;
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
        if ($fld && sizeof($fld) > 0) {
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
        if ($flds && sizeof($flds) > 0) {
            foreach ($flds as $f) {
                $testName = $f->TblAbbr . $f->FldName; // $f->TblName . ':' . 
                //echo 'FldName: ' . $fldName . ' ?== ' . $testName . '<br />';
                if ($fldName == $testName) return $f->FldID;
            }
        }
        return -3;
    }
    
    
    
    // just wanted this utility global to easily call from anywhere including views
    function urlPreview($url)
    {
        $url = str_replace('http://', '', str_replace('https://', '', 
            str_replace('http://www.', '', str_replace('https://www.', '', $url))));
        if (strpos($url, '/') !== false) $url = substr($url, 0, strpos($url, '/'));
        return $url;
    }

    public function splitTblFld($tblFld)
    {
        $tbl = $fld = '';
        if (trim($tblFld) != '' && strpos($tblFld, ':') !== false) {
            list($tbl, $fld) = explode(':', $tblFld);
        }
        return array($tbl, $fld);
    }

    
    
    
    
    public function getFldResponsesByID($fldID)
    {
        if (intVal($fldID) <= 0) return array( "prompt" => '', "vals" => array() );
        return $this->getFldResponses($this->getFullFldNameFromID($fldID));
    }
    
    
    public function getFldResponses($fldName)
    {
        $ret = array( "prompt" => '', "vals" => array() );
        $tmpVals = array( array(), array() );
        $nodes = SLNode::where('NodeDataStore', $fldName)->get();
        if (trim($fldName) != '' && $nodes && sizeof($nodes) > 0) {
            foreach ($nodes as $n) {
                if (trim($ret["prompt"]) == '' && trim($n->NodePromptText) != '') {
                    $ret["prompt"] = strip_tags($n->NodePromptText);
                }
                $res = SLNodeResponses::where('NodeResNode', $n->NodeID)
                    ->orderBy('NodeResOrd', 'asc')
                    ->get();
                if ($res && sizeof($res) > 0) {
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
                    $ret["vals"][] = array($val, $tmpVals[1][$i]);
                }
            }
        }
        return $ret;
    }
    
    public function getCondList()
    {
        return SLConditions::orderBy('CondTag')->get();
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
        }
        else $cond->CondDatabase = $this->dbID;
        $cond->CondTag = (($request->has('condHash')) ? $request->condHash : '#');
        if (substr($cond->CondTag, 0, 1) != '#') $cond->CondTag = '#' . $cond->CondTag;
        $cond->CondDesc = (($request->has('condDesc')) ? $request->condDesc : '');
        $cond->CondOpts = 1;
        $cond->CondOperator = 'CUSTOM';
        $cond->CondOperDeet = 0;
        $cond->CondField = $cond->CondTable = $cond->CondLoop = 0;
        if ($request->has('setSelect')) {
            $tmp = trim($request->setSelect);
            if (strpos($tmp, 'loop-') !== false) {
                $cond->CondLoop = intVal(str_replace('loop-', '', $tmp));
            } else {
                $cond->CondTable = intVal($this->tblI[$tmp]);
            }
        }
        if ($request->has('setFld')) {
            $tmp = trim($request->setFld);
            if (substr($tmp, 0, 6) == 'EXISTS') {
                $cond->CondOperator = 'EXISTS' . substr($tmp, 6, 1);
                $cond->CondOperDeet = intVal(substr($tmp, 7));
            } else {
                $cond->CondField = intVal($request->setFld);
                if ($request->has('equals')) {
                    if ($request->equals == 'equals') $cond->CondOperator = '{';
                    else $cond->CondOperator = '}';
                }
            }
        }
        $cond->save();
        if ($request->has('vals') && sizeof($request->vals) > 0) {
            foreach ($request->vals as $val) {
                $tmpVal = new SLConditionsVals;
                $tmpVal->CondValCondID     = $cond->CondID;
                $tmpVal->CondValValue     = $val;
                $tmpVal->save();
            }
        }
        return $cond;
    }
    
    public function fld2SchemaType($fld)
    {
        if (strpos($fld->FldValues, 'Def::') !== false) return 'xs:string';
        switch (strtoupper(trim($fld->FldType))) {
            case 'INT':            return 'xs:integer'; break;
            case 'DOUBLE':        return 'xs:double'; break;
            case 'DATE':        return 'xs:date'; break;
            case 'DATETIME':    return 'xs:dateTime'; break;
            case 'VARCHAR':
            case 'TEXT':
            default:             return 'xs:string';
        }
        return 'xs:string';
    }
    
    public function loadFldAbout($pref = 'Fld')
    {
        $chk = SLFields::where('FldDatabase', 3)
            ->select('FldName', 'FldNotes')
            ->get();
        if ($chk && sizeof($chk) > 0) {
            foreach ($chk as $f) {
                if ($f->FldNotes && trim($f->FldNotes) != '') {
                    $this->fldAbouts[$pref . $f->FldName] = $f->FldNotes;
                }
            }
        }
        return true;
    }
    
    
    public function getTreeXML()
    {
        if ($this->treeXmlID <= 0) {
            $chk = SLTree::where('TreeDatabase', $this->treeRow->TreeDatabase)
                ->where('TreeType', $this->treeRow->TreeType . ' XML')
                ->first();
            if ($chk && sizeof($chk) > 0) $this->treeXmlID = $chk->TreeID;
        }
        return $this->treeXmlID;
    }
    
    
    public function getInstruct($spot = '')
    {
        if (trim($spot) == '') return '';
        if (isset($this->instructs[$spot]) && isset($this->instructs[$spot]->DefDescription)) {
            return $this->instructs[$spot]->DefDescription;
        }
        $this->instructs[$spot] = SLDefinitions::where('DefSubset', $spot)
            ->where('DefSet', 'Instruction')
            ->where('DefIsActive', '1')
            ->first();
        if (isset($this->instructs[$spot]->DefDescription)) {
            return $this->instructs[$spot]->DefDescription;
        }
        return '';
    }
    
}
