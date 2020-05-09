<?php
/**
  * GlobalsTables is a mid-level class for loading and accessing system information from anywhere.
  * This level contains access to the database design, its tables, and field details.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace SurvLoop\Controllers\Globals;

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
use SurvLoop\Controllers\Tree\TreeNodeSurv;

class GlobalsTables extends GlobalsElements
{
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
        if (($treeOverride > 0 || $this->treeOverride <= 0) 
            && (!$this->treeRow || !isset($this->treeRow->tree_id))) {
            $this->treeRow = SLTree::where('tree_database', $this->dbID)
                ->where('tree_type', 'Survey')
                ->orderBy('tree_id', 'asc')
                ->first();
            if (isset($this->treeRow->tree_id)) {
                $this->treeID = $this->treeRow->tree_id;
            }
        }
        $coreTbl = 0;
        if ($this->treeRow 
            && isset($this->treeRow->tree_core_table) 
            && intVal($this->treeRow->tree_core_table) > 0) {
            $coreTbl = intVal($this->treeRow->tree_core_table);
        }
        if ($dbID == -3 
            && isset($this->treeRow->tree_database) 
            && intVal($this->treeRow->tree_database) > 0) {
            $dbID = $this->treeRow->tree_database;
        }
        if ($treeOverride > 0 || $this->treeOverride <= 0) {
            $this->dbID = $dbID;
        }
        $this->def = new GlobalsDefinitions($this->dbID);
        $this->dbRow = SLDatabases::find($this->dbID);
        if (($treeOverride > 0 || $this->treeOverride <= 0) 
            && $dbID == 1 
            && !$this->dbRow) {
        	$this->dbID = 3;
        	$this->dbRow = SLDatabases::find($this->dbID);
        }
        if ($coreTbl > 0) {
            $this->initCoreTable(SLTables::find($coreTbl));
        }
        $this->treeIsAdmin = false;
        if (isset($this->treeRow->tree_opts) 
            && $this->treeRow->tree_opts > 1 
            && ($this->treeRow->tree_opts%3 == 0 
                || $this->treeRow->tree_opts%17 == 0 
                || $this->treeRow->tree_opts%41 == 0 
                || $this->treeRow->tree_opts%43 == 0)) {
            $this->treeIsAdmin = true;
        }
        if (isset($this->dbRow->db_name) 
            && isset($this->treeRow->tree_name)) {
            $this->treeName = str_replace($this->dbRow->db_name, 
                str_replace('_', '', $this->dbRow->db_prefix), 
                $this->treeRow->tree_name);
            if ($this->treeRow->tree_type == 'Page') {
                if ($coreTbl > 0) {
                    $this->sessTree = $this->chkReportCoreTreeID($coreTbl);
                }
            } else {
                $this->sessTree = $this->treeRow->tree_id;
            }
        }
        $this->sysOpts = [ "cust-abbr" => 'SurvLoop' ];
        return true;
    }
    
    public function installNewModel($tbl, $forceFile = true)
    {
        if ($tbl && isset($tbl->tbl_name) && $tbl->tbl_name != 'Users') {
            $this->modelPath($tbl->tbl_name, $forceFile);
        }
        return true;
    }
    
    public function strTblToModel($tbl = '')
    {
        $ret = '';
        $arr = $this->mexplode('_', $tbl);
        if (sizeof($arr) > 0) {
            foreach ($arr as $i => $word) {
                $ret .= strtoupper(substr($word, 0, 1)) . substr($word, 1);
            }
        }
        return $ret;
    }
    
    public function strFullTblModel($tbl = '')
    {
        return strtoupper(str_replace('_', '', $this->dbRow->db_prefix))
            . $this->strTblToModel($tbl);
    }
    
    public function modelPath($tbl = '', $forceFile = false)
    {
        if (strtolower($tbl) == 'users') {
            return "App\\Models\\User";
        }
        $model = '';
        if (isset($this->tblModels[$tbl])) {
            $model = trim($this->tblModels[$tbl]);
        }
        if ($model == '' && isset($this->tblModels[strtolower($tbl)])) {
            $model = trim($this->tblModels[strtolower($tbl)]);
        }
        if ($model != '') {
            $path = "App\\Models\\" . $model;
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
        if (in_array(strtolower(trim($tbl)), ['', 'uers'])) {
            return false;
        }
        $modelFilename = str_replace('App\\Models\\', '../app/Models/', $path)
            . '.php';
        if ($this->isAdmin 
            && (!file_exists($modelFilename) || $forceFile)) {
            // copied from AdminDatabaseInstall...
            $modelFile = '';
            $tbl = SLTables::where('tbl_database', $this->dbID)
                ->where('tbl_name', $tbl)
                ->first();
            $flds = SLFields::where('fld_database', $this->dbID)
                ->where('fld_table', $tbl->tbl_id)
                ->orderBy('fld_ord', 'asc')
                ->orderBy('fld_eng', 'asc')
                ->get();
            if ($flds->isNotEmpty()) {
                foreach ($flds as $fld) {
                    $modelFile .= "\n\t\t'" . $tbl->tbl_abbr 
                        . $fld->fld_name . "', ";
                }
            }
            $tblName = $this->dbRow->db_prefix . $tbl->tbl_name;
            $fullFileOut = view(
                'vendor.survloop.admin.db.export-laravel-gen-model' , 
                [
                    "modelFile" => $modelFile, 
                    "tbl"       => $tbl,
                    "tblName"   => $tblName,
                    "tblClean"  => str_replace('_', '', $tblName)
                ]
            );
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
            if ($this->treeRow->tree_opts%Globals::TREEOPT_SEARCH == 0
                || $this->treeRow->tree_opts%Globals::TREEOPT_REPORT == 0) {
                $this->currSearchTbls[] = $this->treeRow->tree_core_table;
            }
        }
        $this->dataLoops = [];
        $this->dataLoopNames = [];
        $dataLoops = SLDataLoop::where('data_loop_tree', $treeID)
            ->where('data_loop_root', '>', 0)
            ->orderBy('data_loop_table', 'asc')
            ->get();
        foreach ($dataLoops as $row) {
            $this->dataLoopNames[$row->data_loop_id] = $row->data_loop_plural;
            $this->dataLoops[$row->data_loop_plural] = $row;
            if (!isset($this->tblLoops[$row->data_loop_table])) {
                $this->tblLoops[$row->data_loop_table] = $row->data_loop_plural;
            }
            // what about tables with multiple loops??
        }
        $this->dataSubsets = SLDataSubsets::where('data_sub_tree', $treeID)
            ->orderBy('data_sub_tbl', 'asc')
            ->orderBy('data_sub_sub_tbl', 'asc')
            ->get();
        $this->dataHelpers = SLDataHelpers::where('data_help_tree', $treeID)
            ->orderBy('data_help_parent_table', 'asc')
            ->orderBy('data_help_table', 'asc')
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
        $linksChk = SLDataLinks::where('data_link_tree', $treeID)
            ->get();
        if ($linksChk->isNotEmpty()) {
            foreach ($linksChk as $link) {
                $linkMap = $this->getLinkTblMap($link->data_link_table);
                if ($linkMap && sizeof($linkMap) == 5) {
                    $this->dataLinksOn[$link->data_link_table] = $linkMap;
                }
            }
        }
        $cache .= '$'.'this->dataLinksOn = [];' . "\n";
        if (sizeof($this->dataLinksOn) > 0) {
            foreach ($this->dataLinksOn as $tbl => $map) {
                $cache .= '$'.'this->dataLinksOn[' . $tbl . '] = [ \'' 
                    . $map[0] . '\', \'' . $map[1] . '\', \'' . $map[2] 
                    . '\', \'' . $map[3] . '\', \'' . $map[4] . '\' ];' . "\n";
            }
        }
        return $cache . $this->loadProTips();
    }
    
    public function getCurrTreeUrl()
    {
        if ($this->treeRow->tree_type == 'Page') {
            if ($this->treeIsAdmin) {
                return $this->sysOpts["app-url"] 
                    . '/dash/' . $this->treeRow->tree_slug;
            } else {
                return $this->sysOpts["app-url"] 
                    . '/' . $this->treeRow->tree_slug;
            }
        } else {
            if ($this->treeIsAdmin) {
                return $this->sysOpts["app-url"] 
                    . '/dashboard/start/' . $this->treeRow->tree_slug;
            } else {
                return $this->sysOpts["app-url"] 
                    . '/start/' . $this->treeRow->tree_slug;
            }
        }
        return $this->sysOpts["app-url"];
    }
    
    public function chkReportCoreTreeID($coreTblID = 0)
    {
        if ($coreTblID > 0) {
            $coreTree = SLTree::where('tree_type', 'Survey')
                ->where('tree_database', $this->dbID)
                ->where('tree_core_table', $coreTblID)
                ->orderBy('tree_id', 'asc')
                ->first();
            if ($coreTree && isset($coreTree->tree_id)) {
                return $coreTree->tree_id;
            }
        }
        return NULL;
    }
    
    public function chkReportCoreTree($coreTbl = '')
    {
        if ($coreTbl == '') {
            $coreTbl = $this->coreTbl;
        }
        if (isset($this->tblI[$coreTbl])) {
            return $this->chkReportCoreTreeID(
                $this->tblI[$coreTbl]
            );
        }
        return NULL;
    }
    
    public function chkReportTree($coreTbl = '')
    {
        if ($coreTbl == '') {
            $coreTbl = $this->coreTbl;
        }
        if (!isset($this->tblI[$coreTbl])) {
            return NULL;
        }
        $reportTree = SLTree::where('tree_type', 'Page')
            ->where('tree_database', $this->dbID)
            ->where('tree_core_table', $this->tblI[$coreTbl])
            ->get();
        if ($reportTree->isNotEmpty()) {
            foreach ($reportTree as $t) {
                if ($t->tree_opts%13 == 0) {
                    return $t;
                }
            }
        }
        return NULL;
    }
    
    public function getReportTreeID()
    {
        if (isset($this->reportTree["id"]) 
            && intVal($this->reportTree["id"]) > 0) {
            return $this->reportTree["id"];
        }
        return 0;
    }
    
    public function chkReportFormTree()
    {
        if ($this->treeRow 
            && isset($this->treeRow->tree_type) 
            && $this->treeRow->tree_type == 'Page') {
            $nodeChk = SLNode::find($this->treeRow->tree_root);
            if ($nodeChk 
                && isset($nodeChk->node_response_set) 
                && intVal($nodeChk->node_response_set) > 0
                && intVal($nodeChk->node_response_set) != $this->treeID) {
                $this->loadDataMap(intVal($nodeChk->node_response_set));
            }
        }
        if (!isset($this->x["pageSlugSffx"])) {
            $this->x["pageSlugSffx"] = '';
        }
        return true;
    }
    
    public function getForeignOpts($preSel = '', $opts = 'Subset')
    {
        $ret = '<option value="" ' . (($preSel == '') ? 'SELECTED' : '') 
            . ' >parent - field - child</option><option value=""></option>' . "\n";
        $flds = SLFields::select('sl_fields.fld_table', 
                'sl_fields.fld_name', 'sl_fields.fld_foreign_table')
            ->join('sl_tables', 'sl_tables.tbl_id', '=', 'sl_fields.fld_foreign_table')
            ->where('sl_fields.fld_database', $this->dbID)
            ->where('sl_fields.fld_table', '>', 0)
            ->where('sl_fields.fld_foreign_table', '>', 0)
            ->orderBy('sl_tables.tbl_name', 'asc')
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                if (isset($this->tbl[$fld->fld_table]) 
                    && isset($this->tbl[$fld->fld_foreign_table])) {
                    $lnkMap = $this->tbl[$fld->fld_foreign_table] 
                        . '::' . $this->tbl[$fld->fld_table] . ':' 
                        . $this->tblAbbr[$this->tbl[$fld->fld_table]] . $fld->fld_name;
                    $ret .= '<option value="' . $lnkMap . '" ' 
                        . (($preSel == $lnkMap) ? 'SELECTED' : '') . ' >' 
                        . $this->tbl[$fld->fld_foreign_table] . ' &larr; ' 
                        . $this->tblAbbr[$this->tbl[$fld->fld_table]] . $fld->fld_name 
                        . ' &larr; ' . $this->tbl[$fld->fld_table] 
                        . '</option>' . "\n";
                } else {
                    $ret .= '<option value="">** Warning ** not found: ' 
                        . $fld->fld_table . ' * ' . $fld->fld_foreign_table 
                        . '</option>';
                }
            }
        }
        if ($opts == 'Subset') {
            $flds = SLFields::select('sl_fields.fld_table', 
                    'sl_fields.fld_name', 'sl_fields.fld_foreign_table')
                ->join('sl_tables', 'sl_tables.tbl_id', '=', 'sl_fields.fld_table')
                ->where('sl_fields.fld_database', $this->dbID)
                ->where('sl_fields.fld_table', '>', 0)
                ->where('sl_fields.fld_foreign_table', '>', 0)
                ->orderBy('sl_tables.tbl_name', 'asc')
                ->get();
            if ($flds->isNotEmpty()) {
                foreach ($flds as $fld) {
                    if (isset($this->tbl[$fld->fld_table]) 
                        && isset($this->tbl[$fld->fld_foreign_table])) {
                        $lnkMap = $this->tbl[$fld->fld_table] . ':' 
                            . $this->tblAbbr[$this->tbl[$fld->fld_table]] . $fld->fld_name
                            . ':' . $this->tbl[$fld->fld_foreign_table] . ':';
                        $ret .= '<option value="' . $lnkMap . '" ' 
                            . (($preSel == $lnkMap) ? 'SELECTED' : '') . ' >' 
                            . $this->tbl[$fld->fld_table] . ' &rarr; ' 
                            . $this->tblAbbr[$this->tbl[$fld->fld_table]] 
                            . $fld->fld_name . ' &rarr; ' 
                            . $this->tbl[$fld->fld_foreign_table] 
                            . '</option>' . "\n";
                    }
                }
            }
        }
        return $ret;
    }
    
    // returns array(Table 1, Foreign Key 1, 
    // Linking Table, Foreign Key 2, Table 2)
    public function getLinkTblMap($linkTbl = -3)
    {
        if ($linkTbl <= 0) {
            return [];
        }
        $foreigns = SLFields::select('fld_name', 'fld_foreign_table')
            ->where('fld_database', $this->dbID)
            ->where('fld_table', $linkTbl)
            ->where('fld_foreign_table', '>', 0)
            ->orderBy('fld_ord', 'asc')
            ->orderBy('fld_eng', 'asc')
            ->get();
        if ($foreigns->isNotEmpty() && $foreigns->count() == 2) {
            if (isset($foreigns[0]->fld_foreign_table) 
                && isset($this->tbl[$foreigns[0]->fld_foreign_table])
                && isset($foreigns[1]->fld_foreign_table) 
                && isset($this->tbl[$foreigns[1]->fld_foreign_table])
                && isset($this->tbl[$linkTbl])) {
                $t = $this->tbl[$linkTbl];
                if (isset($this->tblAbbr[$t]) ) {
                    return [
                        $this->tbl[$foreigns[0]->fld_foreign_table], 
                        $this->tblAbbr[$t] . $foreigns[0]->fld_name, 
                        $this->tbl[$linkTbl], 
                        $this->tblAbbr[$t] . $foreigns[1]->fld_name, 
                        $this->tbl[$foreigns[1]->fld_foreign_table] 
                    ];
                }
            }
        }
        return [];
    }
    
    
    
    
    public function tablesDropdown($preSel = '', $instruct = 'select table', $prefix = '', $disableBlank = false)
    {
        $loopTbl = '';
        if (trim($preSel) != '') {
            if (isset($this->dataLoops[$preSel])) {
                $loopTbl = $this->dataLoops[$preSel]->data_loop_table;
            } elseif (isset($this->tblI[$preSel])) {
                $loopTbl = $preSel;
            }
        }
        $ret = '<option value="" ' . (($preSel == "") ? 'SELECTED' : '') 
            . (($disableBlank) ? ' DISABLED ' : '') 
            . ' >' . $instruct . '</option>' . "\n";
        foreach ($this->tblAbbr as $tblName => $tblAbbr) {
            $selected = '';
            if ($preSel == $tblName 
                || $preSel == $this->tblI[$tblName] 
                || $loopTbl == $tblName) {
                $selected = 'SELECTED';
            }
            $ret .= '<option value="' . $tblName.'" ' . $selected . ' >'
                . $prefix . $tblName . '</option>' . "\n";
        }
        return $ret;
    }
    
    // if $keys is 0 don't include primary keys; if $keys is 1 show primary keys; if $keys is -1 show only foreign keys; 
    public function fieldsDropdown($preSel = '', $keys = 2)
    {
        $ret = '<option value="" ' . ((trim($preSel) == '') 
            ? 'SELECTED' : '') . ' ></option>' . "\n";
        if ($keys > 0) {
            foreach ($this->tblAbbr as $tblName => $tblAbbr) {
                $ret .= '<option value="' . $tblName.':'. $tblAbbr . 'id" ' 
                    . (($preSel == $tblName.':'. $tblAbbr . 'id') ? 'SELECTED' : '') 
                    . ' >' . $tblName.' : '. $tblAbbr . 'id (primary key)</option>' . "\n";
            }
        }
        $flds = null;
        $qman = "SELECT t.tbl_name, t.tbl_abbr, 
                f.fld_id, f.fld_name, f.fld_type, f.fld_foreign_table 
            FROM sl_fields f LEFT OUTER JOIN sl_tables t ON f.fld_table LIKE t.tbl_id
            WHERE f.fld_table > '0' AND t.tbl_name IS NOT NULL AND f.fld_database LIKE '" 
            . $this->dbID . "' [[EXTRA]] ORDER BY t.tbl_name, f.fld_name";
        if ($keys == -1) {
            $flds = DB::select( DB::raw( 
                str_replace("[[EXTRA]]", "AND f.fld_foreign_table` > '0'", $qman)
            ) );
        } else {
            $flds = DB::select( DB::raw( str_replace("[[EXTRA]]", "", $qman) ) );
        }
        if ($flds && sizeof($flds) > 0) {
        /* why doesn't this version work?..
        $eval = "\$flds = DB::table('sl_fields')
                ->leftJoin('sl_tables', function (\$join) {
                    \$join->on('sl_tables.tbl_id', '=', 'sl_fields.fld_table')
                        ->where('sl_tables.tbl_name', 'IS NOT', NULL);
                })
                ->where('sl_fields.fld_database', " . $this->dbID . ")
                ->where('sl_fields.fld_table', '>', 0)
                [[EXTRA]]
                ->orderBy('sl_tables.tbl_name', 'asc')
                ->orderBy('sl_fields.fld_name', 'asc')
                ->select('sl_tables.tbl_name', 'sl_tables.tbl_abbr', 
                    'sl_fields.fld_id', 'sl_fields.fld_name', 'sl_fields.fld_type', 
                    'sl_fields.fld_foreign_table', 'sl_fields.fld_table')
                ->get();";
        if ($keys == -1) {
            $eval = str_replace(
                "[[EXTRA]]", 
                "->where('sl_fields.fld_foreign_table', '>' 0)", 
                $eval
            );
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
        if (!isset($fld->fld_id)) {
            return '';
        }
        if (!isset($fld->tbl_name) && isset($fld->fld_table) 
            && isset($this->tbl[$fld->fld_table])) {
            $fld->tbl_name = $this->tbl[$fld->fld_table];
        }
        if (!isset($fld->tbl_abbr) && isset($fld->fld_table) 
            && isset($this->tbl[$fld->fld_table])) {
            $fld->tbl_abbr = $this->tblAbbr[$this->tbl[$fld->fld_table]];
        }
        if ($valID) {
            return '<option value="' . $fld->fld_id . '"' 
                . ((intVal($preSel) != 0 && intVal($preSel) == $fld->fld_id) 
                    ? ' SELECTED' : '') . ' >' . $prfx . $fld->tbl_name . ' : '
                . $fld->tbl_abbr . $fld->fld_name 
                . ' (' . (($fld->fld_foreign_table > 0) 
                    ? 'foreign key' : strtolower($fld->fld_type)) 
                . ')</option>' . "\n";
        } else {
            $fldStr = $fld->tbl_name . ':' . $fld->tbl_abbr . $fld->fld_name;
            return '<option value="' . $fldStr . '"' 
                . ((trim($preSel) == $fldStr) ? ' SELECTED' : '') 
                . ' >' . $prfx . str_replace(':', ' : ', $fldStr) . ' (' 
                . (($fld->fld_foreign_table > 0) 
                    ? 'foreign key' : strtolower($fld->fld_type)) 
                . ')</option>' . "\n";
        }
        return '';
    }
    
    public function allDefsDropdown($preSel = '')
    {
        $ret = '<option value="" ' 
            . (($preSel == "") ? 'SELECTED' : '') 
            . ' ></option>' . "\n";
        $defs = SLDefinitions::select('def_id', 'def_subset', 'def_value')
            ->where('def_set', 'Value Ranges')
            ->orderBy('def_subset', 'asc')
            ->orderBy('def_order', 'asc')
            ->get();
        if ($defs->isNotEmpty()) {
            foreach ($defs as $def) {
                $ret .= '<option value="' . $def->def_id.'" ' 
                    . (($preSel == $def->def_id) ? 'SELECTED' : '') . ' >'
                    . $def->def_subset . ': ' . $def->def_value
                    . '</option>' . "\n";
            }
        }
        return $ret;
    }
    
    public function allDefSets()
    {
        return SLDefinitions::where('def_set', 'Value Ranges')
            ->select('def_subset')
            ->distinct()
            ->orderBy('def_subset', 'asc')
            ->get();
    }
    
    public function printLoopsDropdowns($preSel = '', $fld = 'loopList', $manualOpt = true)
    {
        $currDefinition = $currLoopItems = $currTblRecs = $currTblAll = '';
        $currTblAllCond = 0;
        if (isset($preSel)) {
            if (strpos($preSel, 'Definition::') !== false) {
                $currDefinition = str_replace('Definition::', '', $preSel);
            } elseif (strpos($preSel, 'LoopItems::') !== false) {
                $currLoopItems = str_replace('LoopItems::', '', $preSel);
            } elseif (strpos($preSel, 'Table::') !== false) {
                $currTblRecs = str_replace('Table::', '', $preSel);
            } elseif (strpos($preSel, 'TableAll::') !== false) {
                $explode = str_replace('TableAll::', '', $preSel);
                list($currTblAll, $currTblAllCond) = $GLOBALS["SL"]
                    ->mexplode('::', $explode);
                $currTblAllCond = intVal($currTblAllCond);
            }
        }                   
        return view(
            'vendor.survloop.admin.tree.node-edit-loop-list', 
            [
                "fld"            => $fld,
                "manualOpt"      => $manualOpt,
                "defs"           => $this->allDefSets(),
                "currDefinition" => $currDefinition, 
                "currLoopItems"  => $currLoopItems, 
                "currTblRecs"    => $currTblRecs,
                "currTblAll"     => $currTblAll,
                "currTblAllCond" => $currTblAllCond
            ]
        )->render();
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
                    $ret = 'LoopItems::' . $this->REQ->input($fld . 'LoopItems');
                }
            } elseif (trim($this->REQ->input($fld . 'Type')) == 'auto-tbl') {
                if (trim($this->REQ->input($fld . 'Tables')) != '') {
                    $ret = 'Table::' . $this->REQ->input($fld . 'Tables');
                }
            } elseif (trim($this->REQ->input($fld . 'Type')) == 'auto-tbl-all') {
                if (trim($this->REQ->input($fld . 'Tables')) != '' && $this->isAdmin) {
                    $ret = 'TableAll::' . $this->REQ->input($fld . 'Tables') . '::' 
                        . intVal($this->REQ->input($fld . 'TableCond'));
                }
            }
        }
        return $ret;
    }
    
    public function getLoopConditionLinks($loop)
    {
        $ret = [];
        if (isset($this->dataLoops[$loop]) 
            && isset($this->dataLoops[$loop]->data_loop_id)) {
            $chk = SLConditions::select('sl_conditions.cond_id', 
                    'sl_conditions.cond_field', 'sl_conditions.cond_table')
                ->join('sl_conditions_nodes', 'sl_conditions_nodes.cond_node_cond_id', 
                    '=', 'sl_conditions.cond_id')
                ->where('sl_conditions_nodes.cond_node_loop_id', 
                    $this->dataLoops[$loop]->data_loop_id)
                ->where('sl_conditions.cond_operator', '{')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $cond) {
                    if (isset($cond->cond_field) 
                        && intVal($cond->cond_field) > 0) {
                        $vals = SLConditionsVals::where('cond_val_cond_id', $cond->cond_id)
                            ->get();
                        if ($vals->isNotEmpty() && $vals->count() == 1 
                            && isset($vals[0]->cond_val_value) 
                            && trim($vals[0]->cond_val_value) != '') {
                            $fld = SLFields::find($cond->cond_field);
                            if ($fld && isset($fld->fld_name) 
                                && isset($this->tbl[$cond->cond_table])) {
                                $tblAbbr = $this->tblAbbr[$this->tbl[$cond->cond_table]];
                                $ret[] = [
                                    $tblAbbr . $fld->fld_name,
                                    $vals[0]->cond_val_value
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
        if (strpos($tblIn, 'loop-') !== false 
            && sizeof($this->dataLoops) > 0) {
            $loopID = intVal(str_replace('loop-', '', $tblIn));
            foreach ($this->dataLoops as $loopName => $loopRow) {
                if ($loopRow->id == $loopID 
                    && isset($this->tblI[$loopRow->data_loop_table])) {
                    $tblID = $this->tblI[$loopRow->data_loop_table];
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
            if (isset($this->dataSubsets) 
                && sizeof($this->dataSubsets) > 0) {
                foreach ($this->dataSubsets as $subset) {
                    if ($tbl1 == $this->tblI[$subset->data_sub_tbl]) {
                        $tbl2 = $this->tblI[$subset->data_sub_sub_tbl];
                        $tbls = $this->getSubsetTables($tbl2, $tbls);
                    }
                }
            }
            if (isset($this->dataHelpers) 
                && sizeof($this->dataHelpers) > 0) {
                foreach ($this->dataHelpers as $helper) {
                    if ($tbl1 == $this->tblI[$helper->data_help_parent_table]) {
                        $tbl2 = $this->tblI[$helper->data_help_table];
                        $tbls = $this->getSubsetTables($tbl2, $tbls);
                    }
                }
            }
        }
        return $tbls;
    }
    
    public function isFldCheckboxHelper($fld = '')
    {
        if (isset($this->dataHelpers) 
            && sizeof($this->dataHelpers) > 0) {
            foreach ($this->dataHelpers as $helper) {
                if (isset($helper->data_help_value_field) 
                    && $helper->data_help_value_field == $fld) {
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
        $flds = DB::table('sl_fields')
            ->leftJoin('sl_tables', function ($join) {
                $join->on('sl_tables.tbl_id', '=', 'sl_fields.fld_table');
            })
            ->whereIn('sl_fields.fld_table', $tbls)
            ->orderBy('sl_tables.tbl_name', 'asc')
            ->orderBy('sl_fields.fld_name', 'asc')
            ->select('sl_tables.tbl_name', 'sl_tables.tbl_abbr', 
                'sl_fields.fld_id', 'sl_fields.fld_name', 'sl_fields.fld_type', 
                'sl_fields.fld_foreign_table', 'sl_fields.fld_table')
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                if ($prevTbl != $fld->fld_table) {
                    $ret .= '<option value=""></option>' . "\n";
                }
                $ret .= $this->fieldsDropdownOption($fld, $preSel, true, $prfx) . "\n";
                $prevTbl = $fld->fld_table;
            }
        }
        return $ret;
    }
    
    public function getAllSetTblFldDrops($tblIn = '', $preSel = '')
    {
        $allSetTbls = $this->getAllSetTables($tblIn);
        return $this->fieldsTblsDropdown($allSetTbls, $preSel, ' - ');
    }
    
    public function copyTblRecFromRow($tbl, $row)
    {
        if (!isset($this->tblAbbr[$tbl]) 
            || !$row 
            || !isset($row->{ $this->tblAbbr[$tbl] . 'id' })) {
            return '';
        }
        $abbr = $this->tblAbbr[$tbl];
        eval("\$cpyTo = " . $this->modelPath($tbl) 
            . "::find(" . $row->{ $abbr . 'id' } . ");");
        if (!$cpyTo || !isset($cpyTo->{ $abbr . 'id' })) {
            eval("\$cpyTo = new " . $this->modelPath($tbl) . ";");
            $cpyTo->{ $abbr . 'id' } = $row->{ $abbr . 'id' };
        }
        $flds = $this->getTblFlds($tbl);
        if ($flds->isNotEmpty()) {
            foreach ($flds as $i => $fld) {
                $cpyTo->{ $fld } = $row->{ $fld };
            }
            $chk = SLTree::where('tree_core_table', $this->tblI[$tbl])
                ->get();
            if ($chk->isNotEmpty()) {
                $cpyTo->{ $abbr . 'user_id' }             = $row->{ $abbr . 'user_id' };
                $cpyTo->{ $abbr . 'ip_addy' }             = $row->{ $abbr . 'ip_addy' };
                $cpyTo->{ $abbr . 'unique_str' }          = $row->{ $abbr . 'unique_str' };
                $cpyTo->{ $abbr . 'version_ab' }          = $row->{ $abbr . 'version_ab' };
                $cpyTo->{ $abbr . 'tree_version' }        = $row->{ $abbr . 'tree_version' };
                $cpyTo->{ $abbr . 'is_mobile' }           = $row->{ $abbr . 'is_mobile' };
                $cpyTo->{ $abbr . 'submission_progress' } = $row->{ $abbr . 'submission_progress' };
            }
            $cpyTo->updated_at = $row->updated_at;
            $cpyTo->created_at = $row->created_at;
        }
        $cpyTo->save();
        return ' , copying ' . $tbl . ' row #' . $row->{ $abbr . 'id' };
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
                if ($fld2 != 'id' && in_array($fld2, $flds1)) {
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
            if (!is_array($val) 
                && $val != '' 
                && substr($val, 0, 1) == ';' 
                && substr($val, strlen($val)-1) == ';') {
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
            if (strpos(strtolower($fld), 'gender') !== false 
                && strtoupper($val) == 'M') {
                $ret = 'Male';
            } elseif (strpos(strtolower($fld), 'gender') !== false 
                && strtoupper($val) == 'F') {
                $ret = 'Female';
            } elseif (trim($defSet) == '') {
                if ($val != '' 
                    && isset($this->fldTypes[$tbl]) 
                    && isset($this->fldTypes[$tbl][$fld])
                    && in_array($this->fldTypes[$tbl][$fld], ['INT', 'DOUBLE'])) {
                    if ($this->fldTypes[$tbl][$fld] == 'DOUBLE') {
                        $ret = $this->sigFigs($val, 3);
                    } else {
                        $yearChk = strtolower(substr($fld, strlen($fld)-4));
                        if ($yearChk == 'year' && strlen(trim('' . $val . '')) == 4) {
                            $ret = $val;
                        } else {
                            $ret = number_format(1*floatval($val));
                        }
                    }
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
        if ($fld 
            && isset($fld->fld_table) 
            && $fld->fld_table != $this->tblI[$this->coreTbl]) {
            $linkMap = $this->getLinkTblMap($fld->fld_table);
            
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
                $tbl = $this->tbl[$fld->fld_table];
                $keyMap = $this->getMapToCore($fldID, $fld);
                if (empty($keyMap)) { // then field in core record
                    $eval = "\$chk = " . $this->modelPath($tbl) . "::whereIn('" 
                        . $this->tblAbbr[$tbl] . "id', \$ids)->where('" 
                        .  $this->tblAbbr[$tbl] . $fld->fld_name . "', '" . $value 
                        . "')->select('" . $this->tblAbbr[$tbl] . "id')->get();";
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
        if (!isset($this->formTree->tree_id)) {
            return -3;
        }
        $chk = SLNode::where('node_data_store', $tbl . ':' . $fld)
            ->where('node_type', 'Checkbox')
            ->where('node_tree', $this->formTree->tree_id)
            ->first();
        if ($chk && isset($chk->node_id)) {
            return $chk->node_id;
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
            $chk = SLNode::where('node_data_store', $tbl . ':' . $fld)
                ->orderBy('node_tree', 'asc')
                ->orderBy('node_id', 'desc')
                ->get();
        } else {
            $chk = SLNode::where('node_tree', $tree)
                ->where('node_data_store', $tbl . ':' . $fld)
                ->orderBy('node_id', 'desc')
                ->get();
        }
        if ($chk && $chk->isNotEmpty()) {
            foreach ($chk as $node) {
                if (isset($node->node_prompt_text) 
                    && trim($node->node_prompt_text) != '') {
                    return $node->node_prompt_text;
                }
            }
        }
        return '';
    }
    
    
    public function getFldResponsesByID($fldID)
    {
        if (intVal($fldID) <= 0) {
            return [
                "prompt" => '', 
                "vals"   => []
            ];
        }
        return $this->getFldResponses($this->getFullFldNameFromID($fldID));
    }
    
    public function getFldResponses($fldName)
    {
        $ret = [
            "prompt" => '', 
            "vals"   => []
        ];
        $tmpVals = array( [], [] );
        $nodes = SLNode::where('node_data_store', $fldName)->get();
        if (trim($fldName) != '' && $nodes->isNotEmpty()) {
            foreach ($nodes as $n) {
                if (trim($ret["prompt"]) == '' && trim($n->node_prompt_text) != '') {
                    $ret["prompt"] = strip_tags($n->node_prompt_text);
                }
                $res = SLNodeResponses::where('node_res_node', $n->node_id)
                    ->orderBy('node_res_ord', 'asc')
                    ->get();
                if ($res->isNotEmpty()) {
                    foreach ($res as $r) {
                        if (!in_array($r->node_res_value, $tmpVals[0])) {
                            $tmpVals[0][] = $r->node_res_value;
                            $tmpVals[1][] = strip_tags($r->node_res_eng);
                        }
                    }
                }
            }
            if (sizeof($tmpVals[0]) > 0) {
                foreach ($tmpVals[0] as $i => $val) {
                    $ret["vals"][] = [
                        $val, 
                        $tmpVals[1][$i] 
                    ];
                }
            }
        }
        return $ret;
    }
    
    public function getCondLookup()
    {
        if (empty($this->condTags)) {
            $chk = SLConditions::whereIn('cond_database', [ 0, $this->dbID ])
                ->orderBy('cond_tag')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $i => $c) {
                    $this->condTags[$c->cond_id] = $c->cond_tag;
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
        return SLConditions::whereIn('cond_database', [0, $this->dbID])
            ->orderBy('cond_tag')
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
            SLConditionsVals::where('cond_val_cond_id', $cond->cond_id)->delete();
        } else {
            $cond->cond_database = $this->dbID;
        }
        if ($request->has('condHash') 
            && trim($request->condHash) != '' 
            && trim($request->condHash) != '#') {
            $cond->cond_tag = (($request->has('condHash')) ? $request->condHash : '#');
            if (substr($cond->cond_tag, 0, 1) != '#') {
                $cond->cond_tag  = '#' . $cond->cond_tag;
            }
            $cond->cond_desc     = (($request->has('condDesc')) ? $request->condDesc : '');
            $cond->cond_operator = 'CUSTOM';
            $cond->cond_oper_deet = 0;
            $cond->cond_field = $cond->cond_table = $cond->cond_loop = 0;
            $cond->cond_opts     = 1;
            
            if ($request->has('condType') && $request->condType == 'complex') {
                $cond->cond_operator = 'COMPLEX';
                $cond->save();
                if ($request->has('multConds') && is_array($request->multConds) 
                    && sizeof($request->multConds) > 0) {
                    foreach ($request->multConds as $val) {
                        $chk = SLConditionsVals::where('cond_val_cond_id', $cond->cond_id)
                            ->where('cond_val_value', $val)
                            ->get();
                        if ($chk->isEmpty()) {
                            $tmpVal = new SLConditionsVals;
                            $tmpVal->cond_val_cond_id = $cond->cond_id;
                            $tmpVal->cond_val_value   = $val;
                            if ($request->has('multCondsNot') 
                                && in_array($val, $request->multCondsNot)) {
                                $tmpVal->cond_val_value = (-1*$val);
                            }
                            $tmpVal->save();
                        }
                    }
                }
            } else {
                if ($request->has('setSelect')) {
                    $tmp = trim($request->setSelect);
                    if ($tmp == 'url-parameters') {
                        $cond->cond_operator = 'URL-PARAM';
                    } elseif (strpos($tmp, 'loop-') !== false) {
                        $cond->cond_loop = intVal(str_replace('loop-', '', $tmp));
                    } elseif (isset($this->tblI[$tmp])) {
                        $cond->cond_table = intVal($this->tblI[$tmp]);
                    }
                }
                if ($cond->cond_operator == 'URL-PARAM') {
                    $cond->cond_oper_deet = $request->paramName;
                } elseif ($request->has('setFld')) {
                    $tmp = trim($request->setFld);
                    if (substr($tmp, 0, 6) == 'EXISTS') {
                        $cond->cond_operator = 'EXISTS' . substr($tmp, 6, 1);
                        $cond->cond_oper_deet = intVal(substr($tmp, 7));
                    } else {
                        $cond->cond_field = intVal($request->setFld);
                        if ($request->has('equals')) {
                            if ($request->get('equals') == 'equals') {
                                $cond->cond_operator = '{';
                            } else {
                                $cond->cond_operator = '}';
                            }
                        }
                    }
                }
                $cond->save();
                if ($cond->cond_operator == 'URL-PARAM') {
                    $tmpVal = new SLConditionsVals;
                    $tmpVal->cond_val_cond_id = $cond->cond_id;
                    $tmpVal->cond_val_value   = $request->paramVal;
                    $tmpVal->save();
                } elseif ($request->has('vals') && is_array($request->vals) 
                    && sizeof($request->vals) > 0) {
                    foreach ($request->vals as $val) {
                        $tmpVal = new SLConditionsVals;
                        $tmpVal->cond_val_cond_id = $cond->cond_id;
                        $tmpVal->cond_val_value   = $val;
                        $tmpVal->save();
                    }
                }
            }
            
            if ($request->has('CondPublicFilter') 
                && intVal($request->get('CondPublicFilter')) == 1) {
                $cond->cond_opts *= 2;
            }
            $artsIn = [];
            for ($j=0; $j < 10; $j++) {
                if ($request->has('condArtUrl' . $j . '') 
                    && trim($request->get('condArtUrl' . $j . '')) != '') {
                    $artsIn[$j] = ['', trim($request->get('condArtUrl' . $j . ''))];
                    if ($request->has('condArtTitle' . $j . '') 
                        && trim($request->get('condArtTitle' . $j . '')) != ''){
                        $artsIn[$j][0] = trim($request->get('condArtTitle' . $j . ''));
                    }
                }
            }
            $articles = SLConditionsArticles::where('article_cond_id', $cond->cond_id)
                ->get();
            if (!$artsIn || sizeof($artsIn) == 0) {
                SLConditionsArticles::where('article_cond_id', $cond->cond_id)
                    ->delete();
            } else {
                $cond->cond_opts *= 3;
                foreach ($artsIn as $j => $a) {
                    $foundArt = false;
                    if ($articles->isNotEmpty()) {
                        foreach ($articles as $chk) {
                            if ($chk->article_url == $a[1]) {
                                if ($chk->article_title != $a[0]) {
                                    $chk->article_title = $a[0];
                                    $chk->save();
                                }
                                $foundArt = true;
                            }
                        }
                    }
                    if (!$foundArt) {
                        $newArt = new SLConditionsArticles;
                        $newArt->article_cond_id = $cond->cond_id;
                        $newArt->article_title   = $a[0];
                        $newArt->article_url     = $a[1];
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
        $chk = SLConditions::where('cond_operator', 'AB TEST')
            ->where('cond_tag', 'LIKE', '%AB Tree' . $this->treeID . '%')
            ->orderBy('cond_tag', 'asc')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $cond) {
                $this->condABs[] = [
                    $cond->cond_id, 
                    $cond->cond_desc
                ];
            }
        }
        return $this->condABs;
    }
    
    public function loadFldAbout($pref = 'fld_')
    {
        $chk = SLFields::where('fld_database', 3)
            ->select('fld_name', 'fld_notes')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $f) {
                if ($f->fld_notes && trim($f->fld_notes) != '') {
                    $this->fldAbouts[$pref . $f->fld_name] = $f->fld_notes;
                }
            }
        }
        return true;
    }
    
    public function loadSysTrees($type = 'forms')
    {
        if (!isset($this->sysTree[$type]) 
            || !isset($this->sysTree[$type]["pub"]) 
            || empty($this->sysTree[$type]["pub"])) {
            $treeType = (($type == 'pages') ? 'Page' : 'Survey');
            $trees = SLTree::where('tree_type', $treeType)
                ->orderBy('tree_name', 'asc')
                ->select('tree_id', 'tree_name', 'tree_opts')
                ->get();
            if ($trees->isNotEmpty()) {
                foreach ($trees as $i => $tree) {
                    $pubType = (($tree->tree_opts%3 == 0) ? 'adm' : 'pub');
                    $this->sysTree[$type][$pubType][] = [$tree->tree_id, $tree->tree_name];
                }
            }
        }
        return true;
    }
    
    public function sysTreesDrop($preSel = -3, $type = 'forms', $pubPri = 'pub')
    {
        $this->loadSysTrees($type);
        $ret = '';
        if (in_array($pubPri, ['pub', 'all']) 
            && sizeof($this->sysTree[$type]['pub']) > 0) {
            foreach ($this->sysTree[$type]['pub'] as $tree) {
                $ret .= '<option value="' . $tree[0] . '" ' 
                    . (($preSel == $tree[0]) ? 'SELECTED ' : '') . '>' 
                    . $tree[1] . (($type == 'page') ? ' (Page)' : '') . '</option>';
            }
        }
        if (in_array($pubPri, ['adm', 'all']) 
            && sizeof($this->sysTree[$type]['adm']) > 0) {
            foreach ($this->sysTree[$type]['adm'] as $tree) {
                $ret .= '<option value="' . $tree[0] . '" ' 
                    . (($preSel == $tree[0]) ? 'SELECTED ' : '') . '>' 
                    . $tree[1] . ' (' . (($type == 'page') ? 'Page, ' : '') 
                    . 'Admin)</option>';
            }
        }
        return $ret;
    }
    
    public function loadProTips()
    {
        $cache = '$'.'this->proTips = [];' . "\n" 
            . '$'.'this->proTipsImg = [];' . "\n";
        $chk = SLDefinitions::where('def_database', $this->dbID)
            ->where('def_set', 'Tree Settings')
            ->where('def_subset', 'LIKE', 'tree-' . $this->treeID . '-protip')
            ->orderBy('def_order', 'asc')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $set) {
                if (trim($set->def_description) != '') {
                    $cache .= '$'.'this->proTips[] = ' 
                        . json_encode($set->def_description) . ';' . "\n";
                }
            }
        }
        $chk = SLDefinitions::where('def_database', $this->dbID)
            ->where('def_set', 'Tree Settings')
            ->where('def_subset', 'LIKE', 'tree-' . $this->treeID . '-protipimg')
            ->orderBy('def_order', 'asc')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $set) {
                $cache .= '$'.'this->proTipsImg[] = ' 
                    . json_encode($set->def_description) . ';' ."\n";
            }
        }
        return $cache;
    }
    
    public function loadTreeMojis()
    {
        if (empty($this->treeSettings)) {
            $chk = SLDefinitions::where('def_database', $this->dbID)
                ->where('def_set', 'Tree Settings')
                ->where('def_subset', 'LIKE', 'tree-' . $this->treeID . '-%')
                ->orderBy('def_order', 'asc')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $set) {
                    $setting = str_replace(
                        'tree-' . $this->treeID . '-', 
                        '', 
                        $set->def_subset
                    );
                    if ($setting != 'protip') {
                        if (!isset($this->treeSettings[$setting])) {
                            $this->treeSettings[$setting] = [];
                        }
                        if ($setting == 'emojis') {
                            $names = explode(';', $set->def_value);
                            $this->treeSettings[$setting][] = [
                                "id"     => $set->def_id,
                                "admin"  => ($set->def_order%7 == 0),
                                "verb"   => $names[0],
                                "plural" => $names[1], 
                                "html"   => $set->def_description
                            ];
                        } else {
                            $this->treeSettings[$setting][] 
                                = $set->def_description;
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
        return SLDefinitions::where('def_database', 1)
            ->where('def_set', 'Style Settings')
            ->orderBy('def_order')
            ->get();
    }
    
    public function getCssColors()
    {
        $this->x["sysColor"] = [];
        $cssRaw = $this->getSysStyles();
        if ($cssRaw->isNotEmpty()) {
            foreach ($cssRaw as $c) {
                $this->x["sysColor"][$c->def_subset] = $c->def_description;
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
        $cssRaw = SLDefinitions::where('def_database', 1)
                ->where('def_set', 'Style CSS')
                ->where('def_subset', 'email')
                ->first();
        if ($cssRaw && isset($cssRaw->def_description) > 0) {
            $cssColors["css-dump"] = $cssRaw->def_description;
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
                        . ((session()->has('sessMsgType') 
                            && trim(session()->get('sessMsgType')) != '')
                            ? session()->get('sessMsgType') : 'alert-info')
                        . ' "><button type="button" class="close" '
                        . 'data-dismiss="alert">×</button>' 
                        . session()->get('sessMsg') . '</div>';
                }
            }
            session()->forget('sessMsg');
            session()->forget('sessMsgType');
            session()->save();
        }
        return $ret;
    }
    
    public function allTreeDropOpts($preSel = -3)
    {
        $ret = '<option value="-3" ' 
            . ((intVal($preSel) <= 0) ? 'SELECTED' : '') 
            . ' >select form tree</option>';
        if (sizeof($this->allTrees) > 0) {
            foreach ($this->allTrees as $dbID => $trees) {
                if (sizeof($trees) > 0) {
                    $ret .= '<option value="-3" DISABLED >' 
                        . $this->getDbName($dbID) . ' Database...</option>';
                    foreach ($trees as $i => $tree) {
                        $ret .= '<option ' 
                            . ((intVal($preSel) == $tree["id"]) ? 'SELECTED' : '') 
                            . ' value="' . $tree["id"] . '" > - ' . $tree["name"]
                            . (($tree["opts"]%3 == 0) ? ' (Admin)' : '') 
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
        $chk = SLTree::where('tree_type', 'Survey')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $i => $t) {
                $tblChk = SLTables::find($t->tree_core_table);
                $trees[] = [ 
                    $t->tree_id, 
                    $t->tree_name, 
                    $t->tree_slug, 
                    (($tblChk && isset($tblChk->tbl_name)) ? $tblChk->tbl_name : '')
                ];
            }
        }
        return $trees;
    }
    
    public function treeBaseUrl($incDomain = false, $hideHttp = false)
    {
        $url = (($incDomain) ? $this->sysOpts["app-url"] : '');
        if ($hideHttp) {
            $url = str_replace('http://', '', 
                str_replace('http://www.', '', 
                str_replace('https://', '', 
                str_replace('https://www.', '', $url))));
        }
        if ($this->treeRow->tree_type == 'Page') {
            if ($this->treeIsAdmin) {
                return $url . '/dash/';
            } else {
                return $url . '/';
            }
        } else {
            if (isset($this->treeRow->tree_slug)) {
                if ($this->treeIsAdmin) {
                    return $url . '/dash/' . $this->treeRow->tree_slug . '/';
                } else {
                    return $url . '/u/' . $this->treeRow->tree_slug . '/';
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
                if ($row && isset($row->node_id)) {
                    $node = new TreeNodeSurv();
                    $node->fillNodeRow($currNode, $row);
                    if (isset($node->nodeRow) 
                        && isset($node->nodeRow->node_id)) {
                        if (isset($node->extraOpts["meta-title"]) 
                            && trim($node->extraOpts["meta-title"]) != '') {
                            $this->x["nodeNames"][$currNode] = $node->extraOpts["meta-title"];
                        }
                        if (isset($node->node_prompt_notes)) {
                            $this->x["nodeNames"][$currNode] = $node->node_prompt_notes;
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
        return trim($this->currCyc["cyc"][1]) . trim($this->currCyc["res"][1]) 
            . trim($this->currCyc["tbl"][1]);
    }
    
    public function getPckgProj()
    {
        if (isset($this->sysOpts["cust-package"]) 
            && strpos($this->sysOpts["cust-package"], '/')) {
            $split = explode('/', $this->sysOpts["cust-package"]);
            return $split[1];
        }
        return '';
    }
    
    public function setSEO($metaTitle = '', $metaDesc = '', $metaKeywords = '', $metaImg = '')
    {
        if (trim($metaTitle) != '') {
            $GLOBALS['SL']->sysOpts['meta-title']    = $metaTitle;
        } else {
            $GLOBALS['SL']->sysOpts['meta-title']    = $GLOBALS['SL']->sysOpts['meta-title'];
        }
        if (trim($metaDesc) != '') {
            $GLOBALS['SL']->sysOpts['meta-desc']     = $metaDesc;
        }
        if (trim($metaKeywords) != '') {
            $GLOBALS['SL']->sysOpts['meta-keywords'] = $metaKeywords;
        }
        if (trim($metaImg) != '') {
            $GLOBALS['SL']->sysOpts['meta-img']      = $metaImg;
        }
        return true;
    }
    
    public function loadTreeNodeStatTypes()
    {
        $this->x["dataStatTypes"] = [
            "quali" => [
                'Text', 
                'Long Text', 
                'Email', 
                'Uploads' 
            ],
            "choic" => [
                'Radio', 
                'Checkbox', 
                'Drop Down', 
                'Gender', 
                'Gender Not Sure', 
                'U.S. States', 
                'Countries' 
            ],
            "quant" => [ 
                'Text:Number', 
                'Slider', 
                'Date', 
                'Date Picker', 
                'Date Time', 
                'Time', 
                'Feet Inches' 
            ]
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
            if (in_array($node->nodeType, 
                $this->x["dataStatTypes"]["quali"])) {
                $type = 'quali';
            } elseif (in_array($node->nodeType, 
                $this->x["dataStatTypes"]["choic"])) {
                $type = 'choic';
            } elseif (in_array($node->nodeType, 
                $this->x["dataStatTypes"]["quant"])) {
                $type = 'quant';
            }
            if ($type != '') {
                if (isset($node->dataStore) 
                    && !in_array($node->dataStore, $this->x["dataTypeStats"]["flds"])) {
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
            return view(
                'vendor.survloop.reports.inc-tree-node-type-stats' , 
                [
                    "isPrint"       => $isPrint,
                    "isAll"         => $isAll,
                    "isAlt"         => $isAlt,
                    "dataTypeStats" => $this->x["dataTypeStats"],
                    "qTypeStats"    => $this->x["qTypeStats"]
                ]
            )->render();
        }
        return '';
    }
    
    public function chkMissingReportFlds($treeID = -3)
    {
        $ret = '';
        if ($treeID <= 0) {
            $treeID = $this->treeID;
        }
        $flds1 = $flds2 = [];
        $tree1 = SLTree::find($treeID);
        if ($tree1 && isset($tree1->tree_type) 
            && $tree1->tree_type == 'Page' 
            && $tree1->tree_opts%13 == 0) { // is report
            $tree2 = SLTree::where('tree_type', 'Survey')
                ->where('tree_core_table', $tree1->tree_core_table)
                ->orderBy('tree_id', 'desc')
                ->first();
            if ($tree2 && isset($tree2->tree_id)) {
                $chk = SLNode::where('node_tree', $tree1->tree_id)
                    ->select('node_data_store')
                    ->get();
                if ($chk->isNotEmpty()) {
                    foreach ($chk as $i => $node) {
                        if (isset($node->node_data_store) 
                            && trim($node->node_data_store) != '' 
                            && !in_array($node->node_data_store, $flds1)) {
                            $flds1[] = $node->node_data_store;
                        }
                    }
                }
                $chk = SLNode::where('node_tree', $tree2->tree_id)
                    ->orderBy('node_data_store', 'asc')
                    ->select('node_data_store')
                    ->get();
                if ($chk->isNotEmpty()) {
                    foreach ($chk as $i => $node) {
                        if (isset($node->node_data_store) 
                            && trim($node->node_data_store) != '' 
                            && !in_array($node->node_data_store, $flds2)) {
                            $flds2[] = $node->node_data_store;
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
            $ret = '<div class="mT20"><b>Fields Missing From Primary Survey:</b>' 
                . substr($ret, 1) . '</div>';
        }
        return $ret;
    }
    
    public function initCoreTable($coreTbl, $userTbl = null)
    {
        //if ($this->dbID == 3 && $this->sysOpts["cust-abbr"] != 'SurvLoop') return false;
        if (!$coreTbl || !isset($coreTbl->tbl_id)) {
            return false;
        }
        if (!$userTbl) {
            $userTbl = $this->loadUsrTblRow();
        }
        if ($coreTbl->tbl_id == $userTbl->tbl_id) {
            return false;
        }
        $coreFlds = [
            [
                "FldType" => 'INT', 
                "FldEng"  => 'User ID', 
                "FldName" => 'user_id', 
                "FldDesc" => 'Indicates the unique User ID number of the User '
                    . 'owning the data stored in this record for this Experience.' 
            ], [ 
                "FldType" => 'INT', 
                "FldEng"  => 'Experience Node Progress', 
                "FldName" => 'submission_progress', 
                "FldDesc" => 'Indicates the unique Node ID number of the last '
                    . 'Experience Node loaded during this User\'s Experience.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'Tree Version Number', 
                "FldName" => 'tree_version', 
                "FldDesc" => 'Stores the current version number of this User Experience, important for tracking bugs.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'A/B Testing Version', 
                "FldName" => 'version_ab', 
                "FldDesc" => 'Stores a complex string reflecting all A/B Testing '
                    . 'variations in effect at the time of this User\'s Experience of this Node.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'Unique String For Record', 
                "FldName" => 'unique_str', 
                "FldDesc" => 'This unique string is for cases when including the record ID number is not appropriate.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'IP Address', 
                "FldName" => 'ip_addy', 
                "FldDesc" => 'Encrypted IP address of the current user.' 
            ], [ 
                "FldType" => 'VARCHAR', 
                "FldEng"  => 'Using Mobile Device', 
                "FldName" => 'is_mobile', 
                "FldDesc" => 'Indicates whether or not the current user is interacting via a mobile deviced.' 
            ]
        ];
        foreach ($coreFlds as $f) {
            $chk = SLFields::where('fld_database', $this->dbID)
                ->where('fld_table', $coreTbl->tbl_id)
                ->where('fld_name', $f["FldName"])
                ->get();
            if ($chk->isEmpty()) {
                $fld = new SLFields;
                $fld->fld_database  = $this->dbID;
                $fld->fld_table     = $coreTbl->tbl_id;
                $fld->fld_eng       = $f["FldEng"];
                $fld->fld_name      = $f["FldName"];
                $fld->fld_desc      = $f["FldDesc"];
                $fld->fld_spec_type = 'Replica';
                $fld->fld_type             = $f["FldType"];
                if ($f["FldType"] == 'INT') {
                    $fld->fld_data_type     = 'Numeric';
                    $fld->fld_char_support  = ',Numbers,';
                }
                if ($f["FldName"] == 'UserID') {
                    $fld->fld_key_type      = ',Foreign,';
                    $fld->fld_foreign_table = $userTbl->tbl_id;
                }
                // Options: Auto-Managed By SurvLoop; Internal Use not in XML
                $fld->fld_opts = 39;
                $fld->save();
                if ($this->chkTableExists($coreTbl, $userTbl)) {
                    $tblQry = "ALTER TABLE  `" . $this->dbRow->db_prefix . $coreTbl->tbl_name 
                        . "` ADD `" . $coreTbl->tbl_abbr . $f["FldName"] . "` ";
                    switch ($f["FldName"]) {
                        case 'user_id':             $tblQry .= "bigint(20) unsigned"; break;
                        case 'submission_progress': $tblQry .= "int(11)"; break;
                        case 'unique_str':          $tblQry .= "varchar(50)"; break;
                        case 'is_mobile':           $tblQry .= "int(1) NULL"; break;
                        case 'tree_version':
                        case 'version_ab':
                        case 'ip_addy':             $tblQry .= "varchar(255)"; break;
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