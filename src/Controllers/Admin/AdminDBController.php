<?php
/**
  * AdminDBController is the admin class responsible for the tools to edit Survloop's database design.
  * (Ideally, this will eventually be replaced by Survloop-generated surveys.)
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.1
  */
namespace RockHopSoft\Survloop\Controllers\Admin;

use DB;
use Auth;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Database\Migrations\Migration;

use App\Models\SLTree;
use App\Models\SLTables;
use App\Models\SLFields;
use App\Models\SLDefinitions;
use App\Models\SLBusRules;
use App\Models\SLLogActions;
use App\Models\SLDatabases;

use RockHopSoft\Survloop\Controllers\Tree\SurvData;
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use RockHopSoft\Survloop\Controllers\Admin\AdminController;

class AdminDBController extends AdminController
{
    /******************************************************
    *** Initializing Foundation for this Admin Area
    ******************************************************/
    
    protected $dbRow      = [];
    protected $dbTitle    = ''; 
    protected $dbSubTitle = '';
    
    protected $dbPrivs    = [];
    
    protected function initExtra(Request $request)
    {
        $this->v["DbID"] = $this->dbID = $GLOBALS["SL"]->dbID;
        $this->dbTitle = '<h1 class="disIn red">' 
            . $GLOBALS["SL"]->dbRow->db_name . '&nbsp;</h1>';
        $this->dbSubTitle = '<span class="red">' 
            . $GLOBALS["SL"]->dbRow->db_desc . '</span>';
        $this->v["dbAllowEdits"] = ($this->v["user"] 
            && $this->v["user"]->hasRole('administrator|databaser'));
        $this->v["mission"] = view(
            'vendor.survloop.elements.inc-mission-statement', 
            [ "DbMission" => $GLOBALS["SL"]->dbRow->db_mission ]
        );
        if (trim($this->v["currPage"][0]) == '') {
            $this->v["currPage"][0] = '/dashboard/db';
        }
        $this->v["help"] = '<span class="fPerc80 slGrey">?</span>&nbsp;&nbsp;&nbsp;';
        $this->loadLookups();
        set_time_limit(180);
        return true;
    }
    
    protected function loadBelowAdmMenu()
    {
        return $this->loadTreesPagesBelowAdmMenu();
    }
    
    protected function loadLookups()
    {
        $runChecks = false;
        if (!session()->has('dbDesignChecks')) {
            session()->put('dbDesignChecks', 0);
        } else {
            session()->put('dbDesignChecks', (1+session()->get('dbDesignChecks')));
        }
        session()->save();
        // moderating cleanup to periodic page loads
        if (session()->get('dbDesignChecks')%10 == 0) {
            $runChecks = true;
        }
        
        $this->v["FldDataTypes"] = [];
        $this->v["FldDataTypes"]['VARCHAR'] = [
            'Text/String (255 characters max)', 
            'Text'
        ];
        $this->v["FldDataTypes"]['TEXT'] = [
            'Long Text/String',
            'Text-Long'
        ];
        $this->v["FldDataTypes"]['INT'] = [
            'Integer',
            'Number'
        ];
        $this->v["FldDataTypes"]['DOUBLE'] = [
            'Decimal/Large Number',
            'Number-Decimals'
        ];
        $this->v["FldDataTypes"]['DATE'] = [
            'Date',
            'Date'
        ];
        $this->v["FldDataTypes"]['DATETIME'] = [
            'Date and Time',
            'Date&Time'
        ];
        
        $tbls = SLTables::select('tbl_id', 'tbl_name', 'tbl_eng', 'tbl_abbr', 'tbl_opts')
            ->where('tbl_database', $this->dbID)
            ->orderBy('tbl_ord', 'asc')
            ->get();
        if ($tbls->isNotEmpty()) {
            foreach ($tbls as $tbl) {
                if ($runChecks) {
                    if ($tbl->tbl_opts%3 == 0) {
                        $tbl->tbl_opts = $tbl->tbl_opts/3;
                    }
                    $keyFlds = SLFields::where('fld_table', $tbl->tbl_id)
                        ->where('fld_key_type', 'LIKE', '%Primary%')
                        ->first();
                    if ($keyFlds) {
                        $tbl->tbl_opts *= 3;
                    }
                    $tbl->save();
                }
            }
        }
        
        $this->v["dbBusRulesFld"] = [];
        $busRules = SLBusRules::select('rule_id', 'rule_statement', 'rule_fields')
            ->where('rule_database', $this->dbID)
            ->get();
        if ($busRules->isNotEmpty()) {
            foreach ($busRules as $rule) {
                $fldList = $GLOBALS["SL"]->mexplode(',', $rule->rule_fields);
                if (sizeof($fldList) > 0) {
                    foreach ($fldList as $fldID) {
                        $this->v["dbBusRulesFld"][intVal($fldID)] = [
                            $rule->rule_id, 
                            $rule->rule_statement
                        ];
                    }
                }
            }
        }
        if ($runChecks) {
            $this->refreshTableStats();
        }
        $this->v["dbStats"] = $this->printDbStats();
        return true;
    }
    
    protected function refreshTableStats()
    {
        $tblForeigns = [];
        if (sizeof($GLOBALS["SL"]->tbls) > 0) {
            foreach ($GLOBALS["SL"]->tbls as $tblID) {
                $tblForeigns[$tblID] = [0, 0, 0];
            }
        }
        $flds = SLFields::select('fld_table', 'fld_foreign_table')
            ->where('fld_table', '>', 0)
            ->where('fld_database', $this->dbID)
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                if (isset($tblForeigns[$fld->fld_table])) {
                    $tblForeigns[$fld->fld_table][0]++;
                    if ($fld->fld_foreign_table > 0 
                        && isset($tblForeigns[$fld->fld_foreign_table])) {
                        $tblForeigns[$fld->fld_table][1]++;
                        $tblForeigns[$fld->fld_foreign_table][2]++;
                    }
                }
            }
        }
        foreach ($tblForeigns as $tblID => $tblTots) {
            SLTables::find($tblID)->update([ 
                'tbl_num_fields'      => $tblTots[0], 
                'tbl_num_foreign_keys' => $tblTots[1], 
                'tbl_num_foreign_in'   => $tblTots[2] 
            ]);
        }
        $tbls = SLTables::select('tbl_id')
            ->where('tbl_database', $this->dbID)
            ->get();
        $flds = SLFields::select('fld_id')
            ->where('fld_database', $this->dbID)
            ->get();
        $GLOBALS["SL"]->dbRow->update([
            'db_tables' => $tbls->count(), 
            'db_fields' => $flds->count() 
        ]);
        return true;
    }
    
    protected function loadDefOpts()
    {
        $this->v["dbDefOpts"] = [];
        $defs = SLDefinitions::where('def_set', 'Value Ranges')
            ->where('def_database', $this->dbID)
            ->get();
        if ($defs->isNotEmpty()) {
            foreach ($defs as $def) {
                if (!isset($this->v["dbDefOpts"][$def->def_subset])) {
                    $this->v["dbDefOpts"][$def->def_subset] = [''];
                }
                $this->v["dbDefOpts"][$def->def_subset][0] .= ';'.$def->def_value;
                $this->v["dbDefOpts"][$def->def_subset][] = $def->def_value;
            }
            foreach ($this->v["dbDefOpts"] as $subset => $vals) {
                $this->v["dbDefOpts"][$subset][0] = substr($this->v["dbDefOpts"][$subset][0], 1);
            }
        }
        return true;
    }
    
    protected function getDefOpts($item = '', $link = 0)
    {
        if (!isset($this->v["dbDefOpts"]) || !is_array($this->v["dbDefOpts"])
            || sizeof($this->v["dbDefOpts"]) == 0) {
            $this->loadDefOpts();
        }
        if (isset($this->v["dbDefOpts"][$item]) && isset($this->v["dbDefOpts"][$item][0])) {
            return $this->v["dbDefOpts"][$item][0];
        }
        return '';
    }
    
    function logActions($actions = [])
    {
        $log = new SLLogActions;
        $log->log_database = $this->dbID;
        $log->log_user = Auth::user()->id;
        $log->save();
        $log->update($actions);
        return true;
    }
    

    
    
    /******************************************************
    *** Main Pages Called by Routes
    ******************************************************/
    
    public function index(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db', '', false);
        return $this->printOverview();
    }
    
    public function printOverview()
    {
        $this->loadTblGroups();
        return view('vendor.survloop.admin.db.overview', $this->v);
    }
    
    public function full(Request $request, $pubPrint = false)
    {
        $cacheB = '';
        $this->v["onlyKeys"] = $request->has('onlyKeys');
        if ($this->v["onlyKeys"]) {
            $cacheB = '.onlyKeys';
        }
        if ($pubPrint) {
            $this->v["isPrint"] = true;
        } else {
            $this->admControlInit($request, '/dashboard/db/all');
        }
        if (!$this->checkCache('/db/' . $GLOBALS["SL"]->dbRow->db_prefix . $cacheB)) {
            $this->loadTblGroups();
            $this->loadTblForeigns();
            $this->loadTblRules();
            $GLOBALS["SL"]->loadFldAbout();
            $this->v["basicTblFlds"] = $this->v["basicTblDescs"] = [];
            if (sizeof($this->v["groupTbls"]) > 0) {
                foreach ($this->v["groupTbls"] as $group => $tbls) {
                    foreach ($tbls as $tbl) {
                        $this->v["basicTblFlds"][$tbl->tbl_id] = $this->printBasicTblFlds(
                            $tbl->tbl_id, 
                            (($this->v["isExcel"]) ? -1 : 2)
                        );
                        $this->v["basicTblDescs"][$tbl->tbl_id] = $this->printBasicTblDesc(
                            $tbl, 
                            ((isset($this->v["tblForeigns"][$tbl->tbl_id])) 
                                ? $this->v["tblForeigns"][$tbl->tbl_id] : '')
                        );
                    }
                }
            }
            $this->v["content"] = view('vendor.survloop.admin.db.full-innerTable', $this->v);
        }
        if ($pubPrint) {
            return $this->v["content"];
        }
        if (!$this->checkCache('/dashboard/db/all' . $cacheB)) {
            // this shouldn't be needed, why is it happening?..
            //$this->v["innerTable"] = str_replace('&lt;', '<', str_replace('&gt;', '>', 
            //    str_replace('"&quot;', '"', str_replace('&quot;"', '"', $this->v["innerTable"]))));
            if ($this->v["isExcel"]) {
                $GLOBALS["SL"]->exportExcelOldSchool(
                    '<tr><td colspan=5 ><b>Complete Database Table Field Listings'
                        . '</b></td></tr>' . $this->v["innerTable"], 
                    'FullTableListings'.date("ymd").'.xls'
                );
                exit;
            }
            $this->v["genericFlds"] = [];
            if (!$this->v["isPrint"] && !$this->v["isExcel"]) { // though shouldn't be here if is Excel
                $genericFlds = SLFields::where('fld_spec_type', 'Generic')
                    ->where('fld_database', $this->dbID)
                    ->get();
                if ($genericFlds->isNotEmpty()) {
                    foreach ($genericFlds as $cnt => $fld) {
                        $this->v["genericFlds"][] = $this->printBasicTblFldRow($fld, -3, 2);
                    }
                }
            }
            $this->v["innerTable"] = $this->v["content"];
            $this->v["content"] = view('vendor.survloop.admin.db.full', $this->v)->render();
            $this->saveCache();
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    public function adminPrintFullDBPublic(Request $request, $dbPrefix = '')
    {
        $db = SLDatabases::where('db_prefix', str_replace('_', '', $dbPrefix) . '_')
            ->get();
        if ($db->isNotEmpty()) {
            foreach ($db as $d) {
                if ($d->db_opts%3 > 0) { // no admin databases made public [for now]
                    $this->dbID = $d->db_id;
                    $tree = SLTree::where('tree_database', $this->dbID)
                        ->orderBy('tree_id', 'desc')
                        ->get();
                    if ($tree->isNotEmpty()) {
                        foreach ($tree as $t) {
                            if ($t->tree_opts%3 > 0) { // no admin trees made public [for now]
                                $this->treeID = $t->tree_id;
                            }
                        }
                    }
                    $GLOBALS["SL"] = new Globals(
                        $request, 
                        $this->dbID, 
                        $this->treeID, 
                        $this->treeID
                    );
                }
            }
        }
        $this->survloopInit($request, '/db/' . str_replace('_', '', $dbPrefix));
        $this->v["content"] = view('vendor.survloop.elements.print-header-legal')->render()
            . '<div class="pL20"><h2>' . $GLOBALS["SL"]->dbRow->db_name 
            . ': Database Design Specs</h2></div><div class="p20">' 
            . $this->full($request, true) . '</div>';
        $this->v["isPrint"] = true;
        return view('vendor.survloop.master', $this->v);
    }
    
    public function viewTable(Request $request, $tblName)
    {
        $this->admControlInit($request, '/dashboard/db/all');
        return $this->printViewTable($tblName);
    }
    
    public function printViewTable($tblName)
    {
        $this->v["tblName"] = $tblName;
        $this->v["tbl"] = SLTables::where('tbl_name', $tblName)
            ->where('tbl_database', $this->dbID)
            ->first();
        if (trim($tblName) == '' || !$this->v["tbl"]) {
            return $this->index($GLOBALS["SL"]->REQ);
        }
        $tblID = $this->v["tbl"]->tbl_id;
        $this->v["rules"] = SLBusRules::where('rule_tables', 'LIKE', '%,' . $tblID . ',%')
            ->get();
        $this->v["flds"] = SLFields::where('fld_table', $tblID)
            ->where('fld_database', $this->dbID)
            ->orderBy('fld_ord', 'asc')
            ->get();
        if (isset($this->v["tbl"]->tbl_extend) 
            && intVal($this->v["tbl"]->tbl_extend) > 0) {
            $this->v["flds"] = $GLOBALS["SL"]->addFldRowExtends(
                $this->v["flds"], 
                $this->v["tbl"]->tbl_extend
            );
        }
        $this->v["foreignsFlds"] = '';
        $foreignsFlds = SLFields::where('fld_foreign_table', $tblID)
            ->where('fld_table', '>', 0)
            ->where('fld_database', $this->dbID)
            ->orderBy('fld_id', 'asc')
            ->get();
        if ($foreignsFlds->isNotEmpty()) {
            foreach ($foreignsFlds as $cnt => $foreign) {
                $this->v["foreignsFlds"] .= (($cnt > 0) ? ', ' : '') 
                    . $this->getTblName($foreign->fld_table);
            }
        }
        $this->v["basicTblFlds"] = $this->printBasicTblFlds($tblID, 1, $this->v["flds"]);
        return view('vendor.survloop.admin.db.tableView', $this->v);
    }
    
    public function printEditTable($tblName = '')
    {
        if (!$this->v["dbAllowEdits"]) {
            return $this->printOverview();
        }
        $this->v["tblName"] = $tblName;
        $this->v["tbl"] = new SLTables;
        if (trim($tblName) != '') {
            $this->v["tbl"] = SLTables::where('tbl_name', $tblName)
                ->where('tbl_database', $this->dbID)
                ->first();
        }
        if ($GLOBALS["SL"]->REQ->has('tblEditForm')) {
            if ($GLOBALS["SL"]->REQ->has('deleteTbl')) {
                SLFields::where('fld_table', $this->v["tbl"]->tbl_id)
                    ->delete();
                $this->v["tbl"]->delete();
                return $this->printOverview();
            }
            $logActions = [
                'log_action'   => 'Edit', 
                'log_table'    => $this->v["tbl"]->tbl_id, 
                'log_field'    => 0, 
                'log_old_name' => $this->v["tbl"]->tbl_name, 
                'log_new_name' => $GLOBALS["SL"]->REQ->TblName
            ];
            if (trim($tblName) == '') {
                $this->v["tbl"]->tbl_database = $this->dbID;
            }
            $this->v["tbl"]->tbl_name  = $GLOBALS["SL"]->REQ->TblName;
            $this->v["tbl"]->tbl_eng   = $GLOBALS["SL"]->REQ->TblEng;
            $this->v["tbl"]->tbl_abbr  = $GLOBALS["SL"]->REQ->TblAbbr;
            $this->v["tbl"]->tbl_desc  = $GLOBALS["SL"]->REQ->TblDesc;
            $this->v["tbl"]->tbl_notes = $GLOBALS["SL"]->REQ->TblNotes;
            $this->v["tbl"]->tbl_group = $GLOBALS["SL"]->REQ->TblGroup;
            $this->v["tbl"]->tbl_type  = $GLOBALS["SL"]->REQ->TblType;
            $this->v["tbl"]->save();
            if (trim($tblName) == '' || $GLOBALS["SL"]->REQ->has('forceCreate')) {
                $logActions["log_action"] = 'New';
                DB::statement("CREATE TABLE `" 
                    . $GLOBALS["SL"]->dbRow->db_prefix . $this->v["tbl"]->tbl_name . "` 
                    (`" . $this->v["tbl"]->tbl_abbr . "id` int(11) NOT NULL AUTO_INCREMENT,
                    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`" . $this->v["tbl"]->tbl_abbr . "id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
            }
            $this->logActions($logActions);
            $this->cacheFlush();
            return $this->printViewTable($this->v["tbl"]->tbl_name);
        }
        return view('vendor.survloop.admin.db.tableEdit', $this->v);
    }
    
    
    public function printEditField($tblAbbr = '', $fldName = '')
    {
        $this->v["fldName"] = $fldName;
        $this->v["tbl"] = SLTables::where('tbl_abbr', $tblAbbr)
            ->where('tbl_database', $this->dbID)
            ->first();
        if (!$this->v["dbAllowEdits"] || !isset($this->v["tbl"]->tbl_id)) {
            return $this->printOverview();
        }
        $fld = new SLFields;
        if (trim($fldName) != '') {
            $fld = SLFields::where('fld_name', $fldName)
                ->where('fld_table', $this->v["tbl"]->tbl_id)
                ->where('fld_database', $this->dbID)
                ->first();
        } else {
            $fld->fld_database = $this->dbID;
            $fld->fld_table    = $this->v["tbl"]->tbl_id;
        }
        
        // Check invalid starting points
        if (intVal($fld->fld_opts) == 0) {
            $fld->fld_opts = 1;
        }
        if (intVal($fld->fld_compare_same) == 0) {
            $fld->fld_compare_same = 1;
        }
        if (intVal($fld->fld_compare_other) == 0) {
            $fld->fld_compare_other = 1;
        }
        if (intVal($fld->fld_compare_value) == 0) {
            $fld->fld_compare_value = 1;
        }
        if (intVal($fld->fld_operate_same) == 0) {
            $fld->fld_operate_same = 1;
        }
        if (intVal($fld->fld_operate_other) == 0) {
            $fld->fld_operate_other = 1;
        }
        if (intVal($fld->fld_operate_value) == 0) {
            $fld->fld_operate_value = 1;
        }
        
        if ($GLOBALS["SL"]->REQ->has('FldName')) {
            $this->cacheFlush();
            $logActions = [
                'log_action'   => 'Edit', 
                'log_table'    => $this->v["tbl"]->tbl_id, 
                'log_field'    => $fld->fld_id, 
                'log_old_name' => $fld->fld_name, 
                'log_new_name' => $GLOBALS["SL"]->REQ->FldName
            ];
            if ($GLOBALS["SL"]->REQ->has('delete')) {
                $logActions["log_action"] = 'Delete';
                $fld->delete();
            } else { // not deleting...
                if (trim($fldName) == '') {
                    $logActions["log_action"] = 'New';
                    $ordChk = SLFields::where('fld_database', $this->dbID)
                        ->where('fld_table', $this->v["tbl"]->tbl_id)
                        ->orderBy('fld_ord', 'desc')
                        ->first();
                    if ($ordChk) {
                        $fld->fld_ord = 1+$ordChk->fld_ord;
                    }
                }

                

                $fld->fld_eng           = $GLOBALS["SL"]->REQ->FldEng;
                $fld->fld_name          = $GLOBALS["SL"]->REQ->FldName;
                $fld->fld_desc          = $GLOBALS["SL"]->REQ->FldDesc;
                $fld->fld_notes         = $GLOBALS["SL"]->REQ->FldNotes;
                $fld->fld_type          = $GLOBALS["SL"]->REQ->FldType;
                $fld->fld_key_type      = ',';
                $fld->fld_foreign_table = intVal($GLOBALS["SL"]->REQ->FldForeignTable);
                $fld->fld_foreign_min   = $GLOBALS["SL"]->REQ->FldForeignMin;
                $fld->fld_foreign_max   = $GLOBALS["SL"]->REQ->FldForeignMax;
                $fld->fld_foreign2_min  = $GLOBALS["SL"]->REQ->FldForeign2Min;
                $fld->fld_foreign2_max  = $GLOBALS["SL"]->REQ->FldForeign2Max;
                $fld->fld_is_index      = $GLOBALS["SL"]->REQ->FldIsIndex;
                $fld->fld_values        = $GLOBALS["SL"]->REQ->FldValues;
                $fld->fld_default       = $GLOBALS["SL"]->REQ->FldDefault;
                $fld->fld_spec_type     = $GLOBALS["SL"]->REQ->FldSpecType;
                $fld->fld_spec_source   = intVal($GLOBALS["SL"]->REQ->FldSpecSource);
                $fld->fld_null_support  = $GLOBALS["SL"]->REQ->FldNullSupport;
                $fld->fld_opts          = 1;
                if ($GLOBALS["SL"]->dbFullSpecs()) {
                    $fld->fld_alias             = $GLOBALS["SL"]->REQ->FldAlias;
                    $fld->fld_data_type         = $GLOBALS["SL"]->REQ->FldDataType;
                    $fld->fld_data_length       = intVal($GLOBALS["SL"]->REQ->FldDataLength);
                    $fld->fld_data_decimals     = intVal($GLOBALS["SL"]->REQ->FldDataDecimals);
                    $fld->fld_input_mask        = $GLOBALS["SL"]->REQ->FldInputMask;
                    $fld->fld_display_format    = $GLOBALS["SL"]->REQ->FldDisplayFormat;
                    $fld->fld_key_struct        = $GLOBALS["SL"]->REQ->FldKeyStruct;
                    $fld->fld_edit_rule         = $GLOBALS["SL"]->REQ->FldEditRule;
                    $fld->fld_unique            = intVal($GLOBALS["SL"]->REQ->FldUnique);
                    $fld->fld_null_support      = intVal($GLOBALS["SL"]->REQ->FldNullSupport);
                    $fld->fld_values_entered_by = $GLOBALS["SL"]->REQ->FldValuesEnteredBy;
                    $fld->fld_required          = intVal($GLOBALS["SL"]->REQ->FldRequired);
                    $fld->fld_compare_same      = $fld->fld_compare_other 
                        = $fld->fld_compare_value = 1;
                    $fld->fld_operate_same      = $fld->fld_operate_other 
                        = $fld->fld_operate_value = 1;
                    $fld->fld_char_support      = ',';
                    if (is_array($GLOBALS["SL"]->REQ->FldCharSupport) 
                        && sizeof($GLOBALS["SL"]->REQ->FldCharSupport) > 0) {
                        foreach ($GLOBALS["SL"]->REQ->FldCharSupport  as $val) {
                            $fld->fld_char_support .= $val.',';
                        }
                    }
                    foreach (['fld_compare_same', 'fld_compare_other', 'fld_compare_value', 
                        'fld_operate_same', 'fld_operate_other', 'fld_operate_value'] as $co) {
                        if ($GLOBALS["SL"]->REQ->has($co) && is_array($GLOBALS["SL"]->REQ->input($co)) 
                            && sizeof($GLOBALS["SL"]->REQ->input($co)) > 0) {
                            if (in_array(3, $GLOBALS["SL"]->REQ->input($co))) {
                                $fld->{$co} = 6;
                            } else {
                                foreach ($GLOBALS["SL"]->REQ->input($co) as $val) {
                                    $fld->{$co} *= $val;
                                }
                                foreach (array(5, 7, 11, 13, 17, 19) as $cod) {
                                    if ($fld->{$co}%$cod == 0) {
                                        $fld->{$co} *= $fld->{$co}/$cod;
                                    }
                                }
                            }
                        }
                    }
                }
                if ($GLOBALS["SL"]->REQ->has('FldValuesDefX') 
                    && trim($GLOBALS["SL"]->REQ->FldValuesDefX) == 'X') {
                    $fld->fld_opts *= 5;
                }
                
                if ($GLOBALS["SL"]->REQ->has('FldKeyType') 
                    && is_array($GLOBALS["SL"]->REQ->FldKeyType) 
                    && sizeof($GLOBALS["SL"]->REQ->FldKeyType) > 0) {
                    foreach ($GLOBALS["SL"]->REQ->FldKeyType as $val) {
                        $fld->fld_key_type .= $val.',';
                    }
                }
                if ($GLOBALS["SL"]->REQ->FldSpecType == 'Generic' 
                        || ($GLOBALS["SL"]->REQ->has('saveGeneric') 
                    && $GLOBALS["SL"]->REQ->saveGeneric == 1)) {
                    $fld->fld_table = 0;
                }
                if ($GLOBALS["SL"]->REQ->has('FldValuesDef') 
                    && trim($GLOBALS["SL"]->REQ->FldValuesDef) != '') {
                    $fld->fld_values = 'Def::'.$GLOBALS["SL"]->REQ->FldValuesDef;
                }
                
                $fld->save();
                
                if ($GLOBALS["SL"]->REQ->has('pushGeneric') 
                    && intVal($GLOBALS["SL"]->REQ->pushGeneric) == 1) {
                    $replicaFlds = SLFields::where('FldSpecSource', $fld->fld_id)
                        ->where('fld_database', $this->dbID)
                        ->get();
                    if ($replicaFlds->isNotEmpty()) {
                        foreach ($replicaFlds as $replica) {
                            $genericCopy = $fld->replicate()->save();
                            $genericCopy->fld_spec_type   = 'Replica';
                            $genericCopy->fld_spec_source = $fld->fld_id;
                            $genericCopy->fld_table       = $replica->fld_table;
                            $genericCopy->fld_ord         = $replica->fld_ord;
                            $genericCopy->save();
                            $replica->delete();
                        }
                    }
                }
            }
            
            $this->logActions($logActions);
            $this->refreshTableStats();
            if ($fld->fld_table > 0) {
                return $this->redir('/dashboard/db/table/'.$this->v["tbl"]->tbl_name);
            } else {
                return $this->printViewTable('Generic');
            }
        }
        
        $this->v["fld"] = $fld;
        $this->v["fullFldSpecs"] = $this->fullFldSpecs($this->v["fld"], $this->v["dbAllowEdits"]);
        return view('vendor.survloop.admin.db.fieldEdit', $this->v);
    }
    
    protected function fullFldSpecs($fld = [], $edit = false)
    {
        $this->loadDefSets();
        $this->loadDefOpts();
        $this->loadGenerics();
        $this->v["fld"] = $fld;
        $this->v["edit"] = $edit;
        $this->v["chkDis"] = (($edit) ? '' : ' disabled ');
        $this->v["fldSfx"] = (intVal($this->v["fld"]->fld_id) > 0) ? $this->v["fld"]->fld_id : 'New';
        $this->v["forKeyChange"] = '';
        $this->v["FldSpecSourceJSlnk"] = '?tbl=' . $GLOBALS["SL"]->REQ->tbl . '&' 
            . (($GLOBALS["SL"]->REQ->has('edit')) ? 'edit='.$GLOBALS["SL"]->REQ->edit : 'add=1');
        $this->v["defSet"] = '';
        if (strpos($fld->fld_values, 'Def::') !== false 
            || strpos($fld->fld_values, 'DefX::') !== false) {
            $this->v["defSet"] = trim(str_replace('Def::', '', 
                str_replace('DefX::', '', $fld->fld_values)
            ));
        }
        $GLOBALS["SL"]->loadFldAbout();
        return view('vendor.survloop.admin.db.fieldSpecifications', $this->v);
    }
    
    public function businessRules(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/bus-rules');
        if ($GLOBALS["SL"]->REQ->has('delRule') 
            && $GLOBALS["SL"]->REQ->delRule > 0 
            && $this->v["dbAllowEdits"]) {
            $delRule = SLBusRules::find($GLOBALS["SL"]->REQ->delRule);
            if ($delRule->isNotEmpty()) {
                $delRule->delete();
            }
        }
        $this->v["rules"] = SLBusRules::where('rule_database', $this->dbID)
            ->orderBy('rule_tables', 'asc')
            ->orderBy('rule_fields', 'asc')
            ->get();
        $this->v["ruleTbls"] = [];
        if ($this->v["rules"]->isNotEmpty()) {
            foreach ($this->v["rules"] as $rule) {
                $this->v["ruleTbls"][] = $this->tblListID2Link($rule->RuleTables);
            }
        }
        return view('vendor.survloop.admin.db.rules', $this->v);
    }

    public function ajaxTblSelector($rT = '')
    {
        $this->v["addT"] = 0;
        $this->v["tblDrop"] = $this->getTblDropOpts();
        if (trim($rT) == '') {
            $rT = ',';
        }
        if ($GLOBALS["SL"]->REQ->has('addT') 
            && trim($GLOBALS["SL"]->REQ->addT) != '' 
            && strpos($rT, ',' . $GLOBALS["SL"]->REQ->addT . ',') === false) {
            $rT .= $GLOBALS["SL"]->REQ->addT . ',';
        }
        if ($GLOBALS["SL"]->REQ->has('delT') && trim($GLOBALS["SL"]->REQ->delT) != '') {
            $rT = str_replace(','.$GLOBALS["SL"]->REQ->delT . ',', ',', $rT);
        }
        $this->v["rT"] = trim($rT);
        $this->v["tblList"] = $GLOBALS["SL"]->mexplode(',', $this->v["rT"]);
        if (sizeof($this->v["tblList"]) > 0) {
            foreach ($this->v["tblList"] as $i => $tbl) {
                $this->v["tblList"][$i] = array(intVal(trim($tbl)));
                $this->v["tblList"][$i][1] = $this->getTblName($tbl, 1, '', ' target="_blank"');
            }
        }
        return view('vendor.survloop.admin.db.ajaxTblFldSelectorT', $this->v);
    }

    public function ajaxFldSelector($rF = '')
    {
        $this->v["addT"] = 0;
        if ($GLOBALS["SL"]->REQ->has('addT')) {
            $this->v["addT"] = intVal($GLOBALS["SL"]->REQ->addT);
        }
        $this->v["tblDrop"] = $this->getTblDropOpts($this->v["addT"], '(select table first)');
        $this->v["fldDrop"] = $this->getFldDropOpts($this->v["addT"]);
        if ($GLOBALS["SL"]->REQ->has('addF') && trim($GLOBALS["SL"]->REQ->addF) != '' 
            && strpos($rF, ','.$GLOBALS["SL"]->REQ->addF.',') === false) {
            $rF .= $GLOBALS["SL"]->REQ->addF.',';
        }
        if ($GLOBALS["SL"]->REQ->has('delF') && trim($GLOBALS["SL"]->REQ->delF) != '') {
            $rF = str_replace(','.$GLOBALS["SL"]->REQ->delF.',', ',', $rF);
        }
        $this->v["rF"] = trim($rF);
        $this->v["fldList"] = $this->getFldArr($this->v["rF"]);
        foreach ($this->v["fldList"] as $i => $fld) {
            $lnk = $this->getTblName($fld->fld_table, 1, '', ' target="_blank"')
                . ':&nbsp;' . $fld->fld_name;
            $this->v["fldList"][$i] = [ $fld->fld_id, $lnk ];
        }
        return view('vendor.survloop.admin.db.ajaxTblFldSelectorF', $this->v);
    }
    
    
    public function definitions(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/definitions');
        $this->v["defSets"] = [];
        $defs = SLDefinitions::where('def_set', 'Value Ranges')
            ->where('def_database', $this->dbID)
            ->orderBy('def_subset', 'asc')
            ->orderBy('def_order', 'asc')
            ->orderBy('def_value', 'asc')
            ->get();
        if ($defs->isNotEmpty()) {
            foreach ($defs as $cnt => $def) {
                if (!isset($this->v["defSets"][$def->def_subset])) {
                    $this->v["defSets"][$def->def_subset] = [];
                }
                $this->v["defSets"][$def->def_subset][] = $def;
            }
        }
        return view('vendor.survloop.admin.db.definitions', $this->v);
    }
    
    public function defAdd(Request $request, $set = '')
    {
        $this->admControlInit($request, '/dashboard/db/definitions');
        $setName = '';
        if (trim($set) != '' && trim($set) != '_') {
            $setName = urldecode($set);
        }
        return $this->printDefEdit(-3, $setName);
    }
    
    public function defEdit(Request $request, $defID)
    {
        $this->admControlInit($request, '/dashboard/db/definitions');
        return $this->printDefEdit($defID);
    }
    
    public function printDefEdit($defID = -3, $subset = '')
    {
        if (!$this->v["dbAllowEdits"]) {
            return $this->printOverview();
        }
        $this->v["defID"]  = $defID;
        $this->v["subset"] = $subset;
        $this->v["def"]    = new SLDefinitions;
        if ($defID > 0) {
            $this->v["def"] = SLDefinitions::where('def_id', $defID)
                ->where('def_database', $this->dbID)
                ->first();
        } else {
            $this->v["def"]->def_subset = $subset;
            $this->v["def"]->def_database = $this->dbID;
            $this->v["def"]->def_order = 0;
        }
        
        if ($GLOBALS["SL"]->REQ->has('defEditForm')) {
            $aname = str_replace(' ', '', $subset);
            $this->cacheFlush();
            if ($GLOBALS["SL"]->REQ->has('deleteDef')) {
                $this->v["def"]->delete();
                return $this->redir('/dashboard/db/definitions#' . $aname);
            }
            if (trim($subset) != '' && $defID <= 0) {
                $setVals = SLDefinitions::where('def_subset', $subset)
                    ->where('def_set', 'Value Ranges')
                    ->where('def_database', $this->dbID)
                    ->get();
                $this->v["def"]->def_order = $setVals->count();
            }
            $this->v["def"]->def_set = 'Value Ranges';
            $this->v["def"]->def_subset = $GLOBALS["SL"]->REQ->defSubset;
            if ($GLOBALS["SL"]->REQ->defSubset == '_' 
                && $GLOBALS["SL"]->REQ->has('newSubset')) {
                $this->v["def"]->def_subset = $GLOBALS["SL"]->REQ->newSubset;
            }
            $this->v["def"]->def_value = $GLOBALS["SL"]->REQ->defValue;
            $this->v["def"]->def_description = $GLOBALS["SL"]->REQ->defDescription;
            $this->v["def"]->save();
            return $this->redir('/dashboard/db/definitions#' . $aname);
        }
        
        $this->v["subList"] = SLDefinitions::select('def_subset')
            ->distinct()
            ->where('def_set', 'Value Ranges')
            ->where('def_database', $this->dbID)
            ->orderBy('def_subset')
            ->get();
        return view('vendor.survloop.admin.db.defEdit', $this->v);
    }
    
    public function ruleAdd(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/bus-rules');
        return $this->printRuleEdit(-3);
    }
    
    public function ruleEdit(Request $request, $ruleID)
    {
        $this->admControlInit($request, '/dashboard/db/bus-rules');
        return $this->printRuleEdit($ruleID);
    }
    
    public function printRuleEdit($ruleID = -3)
    {
        $this->v["ruleID"] = $ruleID;
        $this->v["rule"] = new SLBusRules;
        if ($ruleID > 0) {
            $this->v["rule"] = SLBusRules::where('rule_id', $ruleID)
                ->where('rule_database', $this->dbID)
                ->first();
        } else {
            $this->v["rule"]->rule_database = $this->dbID;
            $this->v["rule"]->rule_tables = $this->v["rule"]->rule_fields = ',';
        }
        $primeFlds = array('rule_test_on', 'rule_phys', 'rule_logic', 'rule_rel'); 
        
        if ($GLOBALS["SL"]->REQ->has('ruleEditForm') && $this->v["dbAllowEdits"]) {
            $this->cacheFlush();
            $this->v["rule"]->rule_statement = $GLOBALS["SL"]->REQ->rule_statement;
            $this->v["rule"]->rule_constraint = $GLOBALS["SL"]->REQ->rule_constraint;
            $this->v["rule"]->rule_action = $GLOBALS["SL"]->REQ->rule_action;
            $this->v["rule"]->rule_tables = $GLOBALS["SL"]->REQ->rule_tables;
            $this->v["rule"]->rule_fields = $GLOBALS["SL"]->REQ->rule_fields;
            $this->v["rule"]->rule_test_on = $this->v["rule"]->rule_phys 
                = $this->v["rule"]->rule_logic = $this->v["rule"]->rule_rel = 1;
            foreach ($primeFlds as $fld) {
                if ($GLOBALS["SL"]->REQ->has($fld) 
                    && is_array($GLOBALS["SL"]->REQ->input($fld)) 
                    && sizeof($GLOBALS["SL"]->REQ->input($fld)) > 0) {
                    foreach ($GLOBALS["SL"]->REQ->input($fld) as $prime) {
                        eval("\$this->v['rule']->" . $fld . " *= \$prime;");
                    }
                }
            }
            if ($GLOBALS["SL"]->REQ->has('RuleType23') 
                && intVal($GLOBALS["SL"]->REQ->RuleType23 > 0)) {
                $this->v["rule"]->rule_type *= intVal($GLOBALS["SL"]->REQ->RuleType23);
            }
            if ($GLOBALS["SL"]->REQ->has('RuleType57') 
                && intVal($GLOBALS["SL"]->REQ->RuleType57 > 0)) {
                $this->v["rule"]->rule_type *= intVal($GLOBALS["SL"]->REQ->RuleType57);
            }
            $this->v["rule"]->save();
        }
        
        $this->v["tblTxt"] = ((isset($this->v["rule"])) 
            ? $this->tblListID2Link($this->v["rule"]->rule_tables) : '');
        $this->v["fldTxt"] = ((isset($this->v["rule"])) 
            ? $this->fldListID2Link($this->v["rule"]->rule_fields) : '');
        $this->v["saveBtn"] = '';
        if ($this->v["dbAllowEdits"]) {
            if ($this->v["ruleID"] <= 0) {
                $this->v["saveBtn"] = '<input type="submit" value="Add New Rule" '
                    . 'class="btn btn-lg btn-primary" >';
            } else {
                $this->v["saveBtn"] = '<input type="submit" value="Save Rule Changes" '
                    . 'class="btn btn-primary" >';
            }
        }
        $GLOBALS["SL"]->loadFldAbout();
        return view('vendor.survloop.admin.db.ruleSpecifications', $this->v);
    }
    
    
    
    public function defSort(Request $request, $subset = '')
    {
        $this->admControlInit($request, '/dashboard/db/definitions');
        if (!$this->v["dbAllowEdits"]) return $this->printOverview();
        $this->v["subset"] = urldecode($subset);
        if ($GLOBALS["SL"]->REQ->has('saveOrder')) {
            $this->cacheFlush();
            if ($GLOBALS["SL"]->REQ->has('item') 
                && is_array($GLOBALS["SL"]->REQ->input('item')) 
                && sizeof($GLOBALS["SL"]->REQ->input('item')) > 0) {
                foreach ($GLOBALS["SL"]->REQ->input('item') as $i => $value) {
                    $def = SLDefinitions::find($value);
                    $def->def_order = $i;
                    $def->save();
                }
            }
            exit;
        }
        
        $sortTitle = '<a href="/dashboard/db/definitions/sort/' . $subset 
            . '" style="font-size: 26px;"><b>' . $this->v["subset"] . '</b></a>';
        $submitURL = '/dashboard/db/definitions/sort/' . $subset . '?saveOrder=1';
        $defs = SLDefinitions::where('def_subset', $this->v["subset"])
            ->where('def_set', 'Value Ranges')
            ->where('def_database', $this->dbID)
            ->orderBy('def_order')
            ->get();
        $sorts = [];
        if ($defs->isNotEmpty()) {
            foreach ($defs as $def) {
                $sorts[] = array($def->def_id, $def->def_value);
            }
        }
        $this->v["sortable"] = view(
            'vendor.survloop.elements.inc-sortable', 
            [
                'submitURL' => $submitURL,
                'sortID'    => 'definitions',
                'sortTitle' => $sortTitle,
                'sorts'     => $sorts
            ]
        );
        return view('vendor.survloop.admin.db.defSort', $this->v);
    }
    
    public function tblSort(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/all', '', false);
        if (!$this->v["dbAllowEdits"]) {
            return $this->printOverview();
        }
        
        if ($GLOBALS["SL"]->REQ->has('saveOrder')) {
            $this->cacheFlush();
            if ($GLOBALS["SL"]->REQ->has('item') 
                && is_array($GLOBALS["SL"]->REQ->input('item')) 
                && sizeof($GLOBALS["SL"]->REQ->input('item')) > 0) {
                foreach ($GLOBALS["SL"]->REQ->input('item') as $i => $value) {
                    $tbl = SLTables::find($value);
                    $tbl->tbl_ord = $i;
                    $tbl->save();
                }
            }
            exit;
        }
        
        $sortTitle = '<a href="/dashboard/db/table/sort" style="font-size: 26px;"><b>All Tables</b></a>
            <div class="f12 slBlueDark">
                Table Name <span class="fPerc80">Type</span>
                <div class="disIn slGrey" style="margin-left: 50px;"><i>Table Group</i></div>
            </div>';
        $submitURL = '/dashboard/db/sortTable?saveOrder=1';
        $tbls = SLTables::select('tbl_id', 'tbl_eng', 'tbl_type', 'tbl_group')
            ->where('tbl_database', $this->dbID)
            ->orderBy('tbl_ord')
            ->get();
        $sorts = [];
        if ($tbls->isNotEmpty()) {
            foreach ($tbls as $tbl) {
                $sorts[] = [
                    $tbl->tbl_id, 
                    $tbl->tbl_eng . ' <span style="font-size: 10px;">' . $tbl->tbl_type
                        . '</span><div class="fR"><i><span class="slGrey" style="font-size: 12px;">' 
                        . $tbl->tbl_group . '</span></i></div><div class="fC"></div>'
                ];
            }
        }
        $this->v["sortable"] = view(
            'vendor.survloop.elements.inc-sortable', 
            [
                'sortID'    => 'tables',
                'sortTitle' => $sortTitle, 
                'submitURL' => $submitURL, 
                'sorts'     => $sorts
            ]
        );
        return view('vendor.survloop.admin.db.tableSort', $this->v);
    }
    
    public function fldSort(Request $request, $tblName = '')
    {
        $this->admControlInit($request, '/dashboard/db/all');
        if (!$this->v["dbAllowEdits"] || trim($tblName) == '') {
            return $this->printOverview();
        }
        $this->v["tblName"] = $tblName;
        $this->v["tbl"] = SLTables::where('tbl_name', $tblName)
            ->where('tbl_database', $this->dbID)
            ->first();
        if ($GLOBALS["SL"]->REQ->has('saveOrder')) {
            $this->cacheFlush();
            if ($GLOBALS["SL"]->REQ->has('item') && is_array($GLOBALS["SL"]->REQ->input('item')) 
                && sizeof($GLOBALS["SL"]->REQ->input('item')) > 0) {
                foreach ($GLOBALS["SL"]->REQ->input('item') as $i => $value) {
                    $fld = SLFields::find($value);
                    $fld->fld_ord = $i;
                    $fld->save();
                }
            }
            exit;
        }
        $sortTitle = '<a href="/dashboard/db/table/sort" style="font-size: 26px;"><b>' 
            . $this->v["tbl"]->tbl_name . '&nbsp;&nbsp;&nbsp;(' . $this->v["tbl"]->tbl_abbr . ')</b></a>';
        $submitURL = '/dashboard/db/table/'.$tblName.'/sort?saveOrder=1';
        $flds = SLFields::select('fld_id', 'fld_eng', 'fld_name', 'fld_type', 'fld_foreign_table')
            ->where('fld_table', $this->v["tbl"]->tbl_id)
            ->where('fld_database', $this->dbID)
            ->orderBy('fld_ord')
            ->orderBy('fld_eng', 'asc')
            ->get();
        $sorts = [];
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                $sorts[] = [
                    $fld->fld_id, 
                    $fld->fld_eng . ' <span style="font-size: 10px;">' 
                        . ((intVal($fld->fld_foreign_table) > 0) ? '<i class="fa fa-link"></i>' : '') . '</span>'
                        . '<div class="fR"><i><span class="slGrey" style="font-size: 12px;">'
                        . '<span style="font-size: 8px;">('.$fld->fld_type.')</span> '
                        . $fld->fld_name . '</span></i></div><div class="fC"></div>'
                ];
            }
        }
        $this->v["sortable"] = view(
            'vendor.survloop.elements.inc-sortable', 
            [
                'sortID'    => 'fields',
                'sortTitle' => $sortTitle, 
                'submitURL' => $submitURL, 
                'sorts'     => $sorts
            ]
        );
        return view('vendor.survloop.admin.db.fieldSort', $this->v);
    }
    
    public function fieldDescs(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/fieldDescs');
        if (!$this->v["dbAllowEdits"]) {
            return $this->printOverview();
        }
        $this->v["view"] = (($request->has('view')) ? $request->get('view') : '');
        $this->v["tblID"] = (($request->has('table')) 
            ? intVal($request->get('table')) : $GLOBALS["SL"]->tbls[0]);
        $this->v["tblNext"] = 0;
        foreach ($GLOBALS["SL"]->tbls as $tblID) {
            if ($tblID == $this->v["tblID"]) {
                $this->v["tblNext"] = -1;
            } elseif ($this->v["tblNext"] == -1) {
                $this->v["tblNext"] = $tblID;
            }
        }
        if ($this->v["tblNext"] <= 0) {
            $this->v["tblNext"] = $GLOBALS["SL"]->tbls[0];
        }
        if ($request->has('save')) {
            $this->cacheFlush();
            if ($request->has('changedFlds') 
                && trim($request->changedFlds) != '' 
                && $request->changedFlds != ',') {
                $flds = $GLOBALS["SL"]->mexplode(',', $request->changedFlds);
                if (sizeof($flds) > 0) {
                    foreach ($flds as $f) {
                        if (intVal($f) > 0) {
                            $fld = SLFields::find(intVal($f));
                            $fld->fld_eng = trim($request->{ 'FldEng' . $f });
                            $fld->fld_desc = trim($request->{ 'FldDesc' . $f }); 
                            $fld->fld_notes = trim($request->{ 'FldNotes' . $f }); 
                            $fld->save();
                        }
                    }
                }
            }
            if ($request->has('changedFldsGen') 
                && trim($request->changedFldsGen) != '' 
                && trim($request->changedFldsGen) != ',') {
                $flds = $GLOBALS["SL"]->mexplode(',', $request->changedFldsGen);
                if (sizeof($flds) > 0) {
                    foreach ($flds as $f) {
                        if (intVal($f) > 0) {
                            SLFields::where($f)
                                ->orWhere(function ($query) { $query
                                    ->where('fld_spec_type', 'Replica')
                                    ->where('fld_spec_source', $f);
                                })
                                ->update([ 
                                    'fld_eng'   => $request->input('FldEng' . $f . ''), 
                                    'fld_desc'  => $request->input('FldDesc' . $f . ''), 
                                    'fld_notes' => $request->input('FldNotes' . $f . '') 
                                ]);
                        }
                    }
                }
            }
        }
        $this->loadDefOpts();
        $this->v["fldTots"] = [ [0, 0], [0, 0], [0, 0] ]; // unique, replica, generic
        $flds = SLFields::select('fld_desc', 'fld_spec_type')
            ->where('fld_spec_type', 'NOT LIKE', 'Generic')
            ->where('fld_database', $this->dbID)
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                $fldType = (($fld->fld_spec_type == 'Generic') 
                    ? 2 : (($fld->fld_spec_type == 'Replica') 
                        ? 1 : (($fld->fld_spec_type == 'Unique') 
                            ? 0 : 0)));
                $this->v["fldTots"][$fldType][1]++;
                if (trim($fld->fld_desc) != '') {
                    $this->v["fldTots"][$fldType][0]++;
                }
            }
        }
        $this->v["viewParam"] = (($this->v["view"] == 'generics') 
            ? '&view=generics' : (($this->v["view"] == 'replicas') 
                ? '&view=replicas' : (($this->v["view"] == 'uniques') 
                    ? '&view=uniques' : '')));
        $this->v["fldLabel"] = (($this->v["view"] == 'generics') 
            ? 'Generics' : (($this->v["view"] == 'replicas') 
                ? 'Replicas' : (($this->v["view"] == 'uniques') 
                    ? 'Unique' : 'Fields')));
        $fldSpecType = ['NOT LIKE', 'Generic'];
        if ($this->v["view"] == 'generics') {
            $fldSpecType = ['LIKE', 'Generic'];
        } elseif ($this->v["view"] == 'replicas') {
            $fldSpecType = ['LIKE', 'Replica'];
        } elseif ($this->v["view"] == 'uniques') {
            $fldSpecType = ['LIKE', 'Unique'];
        }
        $this->v["fldTot"] = SLFields::select('fld_id')
            ->where('fld_database', $this->dbID)
            ->where('fld_spec_type', $fldSpecType[0], $fldSpecType[1])
            ->get();
        $this->v["tblFldVals"] = $this->v["tblFldQuestion"] = [];
        $this->v["tblFldLists"] = SLFields::where('fld_database', $this->dbID)
            ->where('fld_spec_type', $fldSpecType[0], $fldSpecType[1])
            ->where('fld_table', $this->v["tblID"])
            ->orderBy('fld_ord', 'asc')
            ->orderBy('fld_eng', 'asc')
            ->get();
        if ($this->v["fldTot"]->isNotEmpty() && $this->v["tblFldLists"]->isNotEmpty()) {
            foreach ($this->v["tblFldLists"] as $fld) {
                $this->v["tblFldVals"][$fld->fld_id] = str_replace(';', ' ; ', $fld->fld_values);
                if (strpos($fld->fld_values, 'Def::') !== false 
                    || strpos($fld->fld_values, 'DefX::') !== false) {
                    $this->v["tblFldVals"][$fld->fld_id] = str_replace(';', ' ; ', 
                        $this->getDefOpts(str_replace('Def::', '', 
                            str_replace('DefX::', '', $fld->fld_values)))); 
                }
                if (isset($this->v["dbBusRulesFld"][$fld->fld_id])) {
                    $this->v["tblFldVals"][$fld->fld_id] .= ' <a href="busrules.php?rule=' 
                        . base64_encode($this->v["dbBusRulesFld"][$fld->fld_id][0]) 
                        . '" class="fPerc80" data-toggle="tooltip" data-placement="top"  title="' 
                        . str_replace('"', "'", $this->v["dbBusRulesFld"][$fld->fld_id][1]) 
                        . '"><i class="fa fa-university"></i></a>';
                }
                $this->v["tblFldQuestion"][$fld->fld_id] = strip_tags(
                    $GLOBALS["SL"]->getFldNodeQuestion(
                        $GLOBALS["SL"]->tbl[$this->v["tblID"]], 
                        $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->tbl[$this->v["tblID"]]] . $fld->fld_name,
                        0
                    )
                );
            }
        }
        return view('vendor.survloop.admin.db.fieldDescs', $this->v);
    }
    
    public function fieldDescsSave(Request $request)
    {

    }
    
    public function fieldXML(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/fieldXML');
        if (!$this->v["dbAllowEdits"]) {
            return $this->printOverview();
        }
        $this->v["tblsOrdered"] = SLTables::select('tbl_id')
            ->where('tbl_database', $this->dbID)
            ->orderBy('tbl_ord', 'asc')
            ->get();
        $this->v["tblFldLists"] = [];
        foreach ($GLOBALS["SL"]->tbls as $tblID) {
            $this->v["tblFldLists"][$tblID] = SLFields::where('fld_database', $this->dbID)
                ->where('fld_spec_type', 'NOT LIKE', 'Generic')
                ->where('fld_table', $tblID)
                ->orderBy('fld_ord', 'asc')
                ->orderBy('fld_eng', 'asc')
                ->get();
        }
        return view('vendor.survloop.admin.db.fieldxml', $this->v);
    }
    
    public function fieldXMLsave(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/fieldXML');
        if (!$this->v["dbAllowEdits"]) {
            return '';
        }
        if ($request->has('changedFld') 
            && $request->changedFld > 0 
            && $request->has('changedFldSetting')) {
            $fld = SLFields::where('fld_id', $request->changedFld)
                ->where('fld_database', $this->dbID)
                ->first();
            if ($fld) {
                if (!isset($fld->fld_opts) || intVal($fld->fld_opts) <= 0) {
                    $fld->fld_opts = 1;
                }
                $primes = [7, 11, 13];
                foreach ($primes as $p) {
                    if ($request->changedFldSetting == $p) {
                        if ($fld->fld_opts%$p > 0) {
                            $fld->fld_opts *= $p;
                        }
                    } elseif ($fld->fld_opts%$p == 0) {
                        $fld->fld_opts = $fld->fld_opts/$p;
                    }
                }
                $fld->save();
            }
        }
        return '';
    }
    
    public function diagrams(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/diagrams');
        if (!$this->checkCache()) {
            $this->v["printMatrix"] = '';
            $this->v["diags"] = SLDefinitions::where('def_set', 'Diagrams')
                ->where('def_database', $this->dbID)
                ->orderBy('def_order')
                ->get();
            $tblMatrix = [];
            if (sizeof($GLOBALS["SL"]->tbls) > 0) {
                foreach ($GLOBALS["SL"]->tbls as $tID) { 
                    $tblMatrix[$tID] = [];
                    foreach ($GLOBALS["SL"]->tbls as $tID2) {
                        $tblMatrix[$tID][$tID2] = [];
                    }
                }
                $flds = SLFields::select('fld_id', 
                    'fld_table', 'fld_foreign_table', 
                    'fld_foreign_min', 'fld_foreign_max', 
                    'fld_foreign2_min', 'fld_foreign2_max')
                    ->where('fld_table', '>', 0)
                    ->where('fld_foreign_table', '>', 0)
                    ->where('fld_database', $this->dbID)
                    ->get();
                if ($flds->isNotEmpty()) {
                    foreach ($flds as $fld) {
                        $tbl = $fld->fld_table;
                        $foreign = $fld->fld_foreign_table;
                        $dup = false;
                        if (isset($tblMatrix[$tbl])
                            && isset($tblMatrix[$tbl][$foreign])
                            && is_array($tblMatrix[$tbl][$foreign])
                            && sizeof($tblMatrix[$tbl][$foreign]) > 0) {
                            foreach ($tblMatrix[$tbl][$foreign] as $keys) {
                                if ($keys[0] == $fld->fld_foreign2_min 
                                    && $keys[1] == $fld->fld_foreign2_max) {
                                    $dup = true;
                                }
                            }
                        }
                        if (!$dup) {
                            $tblMatrix[$tbl][$foreign][] = [
                                $fld->fld_foreign2_min, 
                                $fld->fld_foreign2_max
                            ];
                        }
                        $dup = false;
                        if (isset($tblMatrix[$foreign])
                            && isset($tblMatrix[$foreign][$tbl])
                            && is_array($tblMatrix[$foreign][$tbl])
                            && sizeof($tblMatrix[$foreign][$tbl]) > 0) {
                            foreach ($tblMatrix[$foreign][$tbl] as $keys) {
                                if ($keys[0] == $fld->fld_foreign_min 
                                    && $keys[1] == $fld->fld_foreign_max) {
                                    $dup = true;
                                }
                            }
                        }
                        if (!$dup) {
                            $tblMatrix[$foreign][$tbl][] = [
                                $fld->fld_foreign_min, 
                                $fld->fld_foreign_max
                            ];
                        }
                    }
                    $this->v["printMatrix"] = '<table class="keyMatrix " border=1 '
                        . 'cellpadding=0 cellspacing=3 ><tr><td class="mid">&nbsp;</td>';
                    $cnt1 = $cnt2 = 1;
                    foreach ($GLOBALS["SL"]->tbls as $tID) {
                        $cnt2++;
                        $this->v["printMatrix"] .= '<th class="' . (($cnt2%2 == 0) ? 'cl2' : 'cl1') 
                            . '" >' . $GLOBALS["SL"]->tbl[$tID] . '</th>';
                    }
                    foreach ($GLOBALS["SL"]->tbls as $tID) {
                        $cnt1++; $cnt2 = 1;
                        $this->v["printMatrix"] .= '<tr ' . (($cnt1%2 == 0) ? 'class="row2"' : '') 
                            . ' ><th>' . $GLOBALS["SL"]->tbl[$tID] . '</th>';
                        foreach ($GLOBALS["SL"]->tbls as $tID2) { 
                            $cnt2++;
                            $this->v["printMatrix"] .= '<td class="' 
                                . (($tID == $tID2) ? 'bgPrimary ' : (($cnt2%2 == 0) ? 'cl2' : 'cl1'))
                                . '" data-toggle="tooltip" data-placement="top"  title="';
                            if (sizeof($tblMatrix[$tID][$tID2]) > 0) { 
                                $this->v["printMatrix"] .= $tblMatrix[$tID][$tID2][0][0] . ' to ' 
                                    . $tblMatrix[$tID][$tID2][0][1] . '</b> ' 
                                    . strip_tags($this->getTblName($tID2, 0) 
                                    . ' records can be related to a single ' 
                                    . $this->getTblName($tID, 0)) . ' record." >' 
                                    . $tblMatrix[$tID][$tID2][0][0] . ':' 
                                    . $tblMatrix[$tID][$tID2][0][1] . '</td>';
                            } else {
                                $this->v["printMatrix"] .= '<i>No direct relationship between ' 
                                    . strip_tags($this->getTblName($tID2, 0) . ' and ' 
                                    . $this->getTblName($tID, 0)) . '.</i>"></td>';
                            }
                        }
                        $this->v["printMatrix"] .= '</tr>';
                    }
                    $this->v["printMatrix"] .= '</table>';
                }
            }
            $this->v["content"] = view('vendor.survloop.admin.db.diagrams', $this->v)->render();
            $this->saveCache();
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    // http://www.html5canvastutorials.com/tutorials/
    public function networkMap(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/diagrams');
        if (!$this->checkCache('/dashboard/db/diagrams/network-map')) {
            $this->v["errors"] = '';
            $this->sysDef = new SystemDefinitions;
            $this->v["css"] = $this->sysDef->loadCss();
            $this->v["canvasDimensions"] = array(950, 950);
            $mainCircleCenter = [
                $this->v["canvasDimensions"][0]/2, 
                $this->v["canvasDimensions"][1]/2 
            ];
            $sizeMax = 0;
            $this->v["tables"] = $tableLookup = [];
            //$this->v["tables"][] = array('English', Size, Center-X, Center-Y);
            $tbls = SLTables::select('tbl_id', 'tbl_name', 'tbl_num_foreign_keys', 
                    'tbl_num_foreign_in', 'tbl_opts')
                ->where('tbl_database', $this->dbID)
                ->orderBy('tbl_ord', 'asc')
                ->get();
            if ($tbls->isNotEmpty()) {
                foreach ($tbls as $tbl) {
                    $tableLookup[$tbl->tbl_id] = sizeof($this->v["tables"]);
                    $this->v["tables"][] = [
                        $tbl->tbl_name, 
                        sqrt(sqrt($tbl->tbl_num_foreign_keys+$tbl->tbl_num_foreign_in)), 
                        0, 
                        0, 
                        (($GLOBALS["SL"]->isCoreTbl($tbl->tbl_id)) ? $this->v["css"]["color-success-on"] : '')
                    ];
                }
                foreach ($this->v["tables"] as $i => $tbl) {
                    if ($sizeMax < $this->v["tables"][$i][1]) {
                        $sizeMax = $this->v["tables"][$i][1];
                    }
                }
                foreach ($this->v["tables"] as $i => $tbl) {
                    if ($sizeMax <= 0) $sizeMax = 1;
                    $this->v["tables"][$i][1] = 43*($this->v["tables"][$i][1]/$sizeMax);
                    if ($this->v["tables"][$i][1] <= 10) {
                        $this->v["tables"][$i][1] = 10;
                    }
                    $rad = deg2rad(360*$i/sizeof($this->v["tables"]));
                    $dim = 0.42*$this->v["canvasDimensions"][1];
                    $this->v["tables"][$i][2] = round($mainCircleCenter[0]+(sin($rad)*$dim));
                    $this->v["tables"][$i][3] = round($mainCircleCenter[1]-(cos($rad)*$dim));
                }
            }
            $this->v["keyLines"] = [];
            $foreignFlds = SLFields::select('fld_table', 'fld_foreign_table')
                ->where('fld_foreign_table', '>', 0)
                ->where('fld_spec_type', 'NOT LIKE', 'Generic')
                ->where('fld_database', $this->dbID)
                ->get();
            if ($foreignFlds->isNotEmpty()) {
                foreach ($foreignFlds as $fld) {
                    if (!isset($tableLookup[$fld->fld_table])) {
                        $this->v["errors"] .= '<br />add line, missing FldTable tblLookup[' 
                            . $fld->fld_table . ']';
                    } elseif (!isset($tableLookup[$fld->fld_foreign_table])) {
                        $this->v["errors"] .= '<br />add line, missing FldForeignTable tblLookup[' 
                            . $fld->fld_foreign_table . ']';
                    } else {
                        $this->v["keyLines"][] = [
                            $tableLookup[$fld->fld_table], 
                            $tableLookup[$fld->fld_foreign_table]
                        ];
                    }
                }
            }
            $this->v["content"] = view('vendor.survloop.admin.db.network-map', $this->v)->render();
            $this->saveCache();
        }
        $this->v["hideWrap"] = true;
        $this->v["isPrint"] = true;
        $this->v["isFrame"] = true;
        return view('vendor.survloop.master', $this->v);
    }
    
    public function addTable(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/all');
        return $this->printEditTable('');
    }
    
    public function editTable(Request $request, $tblName)
    {
        $this->admControlInit($request, '/dashboard/db/all');
        if (trim($tblName) == '') {
            return $this->printOverview();
        }
        return $this->printEditTable($tblName);
    }
    
    public function addTableFld(Request $request, $tblAbbr)
    {
        $this->admControlInit($request, '/dashboard/db/all');
        if (trim($tblAbbr) == '') {
            return $this->printOverview();
        }
        return $this->printEditField($tblAbbr, '');
    }
    
    public function editField(Request $request, $tblAbbr, $fldName)
    {
        if (trim($fldName) == '') {
            return $this->addTableFld($request, $tblAbbr);
        }
        $this->admControlInit($request, '/dashboard/db/all');
        if (trim($tblAbbr) == '') {
            return $this->printOverview();
        }
        return $this->printEditField($tblAbbr, $fldName);
    }
    
    public function fieldAjax(Request $request, $fldID = -3)
    {
        if (intVal($fldID) <= 0) {
            exit;
        }
        $this->admControlInit($request);
        $fld = SLFields::find($fldID);
        return $this->fullFldSpecs($fld, false);
    }
    
    public function fieldMatrix(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/field-matrix');
        if (!$this->checkCache()) {
            $this->v["urlParam"] = (($this->v["isAlt"]) ? 'alt=1&' : '');
            $this->v["fieldMatrix"] = '...';
            $keySign = (($this->v["isExcel"]) ? ' *' : ' <i class="fa fa-link"></i>');
            $this->v["matrix"] = [];
            $this->v["max"] = 0;
            $tbls = $this->tblQryStd();
            if ($tbls->isNotEmpty()) {
                foreach ($tbls as $i => $tbl) {
                    $this->v["matrix"][] = array((($this->v["isAlt"]) 
                        ? $tbl->tbl_eng : $tbl->tbl_name));
                    $this->v["matrix"][$i][] = (($this->v["isAlt"]) 
                        ? 'Unique Primary ID' : $tbl->tbl_abbr . 'id');
                    $flds = SLFields::where('fld_table', $tbl->tbl_id)
                        ->where('fld_database', $this->dbID)
                        ->orderBy('fld_ord', 'asc')
                        ->orderBy('fld_eng', 'asc')
                        ->get();
                    if ($flds->isNotEmpty()) {
                        foreach ($flds as $fld) {
                            $lnk = (($fld->fld_foreign_table > 0) 
                                    ? $keySign . (($this->v["isExcel"]) ? '' 
                                    : '<span class="f8 tooltip" title="' 
                                        . $this->getForeignTip($fld) . '">')
                                . '(' . $fld->fld_foreign_min . ',' . $fld->fld_foreign_max . ')' 
                                . (($this->v["isExcel"]) ? '' : '</span>') : '');
                            $this->v["matrix"][$i][] = (($this->v["isAlt"]) 
                                ? $fld->fld_eng : $tbl->tbl_abbr . $fld->fld_name) . $lnk;
                            if ($this->v["max"] < sizeof($this->v["matrix"][$i])) {
                                $this->v["max"] = sizeof($this->v["matrix"][$i]);
                            }
                        }
                    }
                }
            }
            if ($this->v["isExcel"]) {
                $tblInner = '<tr><td colspan=4 >R2R Database Tables</td>
                    <td colspan=2 >* Foreign Keys</td></tr>';
                $tblInner .= '<tr>';
                foreach ($this->v["matrix"] as $row) {
                    $tblInner .= '<th>' . $row[0] . '</th>';
                }
                $tblInner .= '</tr>';
                for ($r=1; $r < $this->v["max"]; $r++) {
                    $tblInner .= '<tr>';
                    foreach ($this->v["matrix"] as $row) {
                        $tblInner .= '<td>' . ((isset($row[$r])) ? $row[$r] : '&nbsp;') . '</td>';
                    }
                    $tblInner .= '</tr>';
                }
                $filename = 'OPC-DB-Field_Matrix-' . (($this->v["isAlt"]) ? 'English' : 'Geek')
                    . '-' . date("ymd");
                $GLOBALS["SL"]->exportExcelOldSchool($tblInner, $filename.'.xls');
                exit;
            }
            $this->v["content"] = view('vendor.survloop.admin.db.field-matrix', $this->v)->render();
            $this->saveCache();
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    public function tblSelector(Request $request, $rT = '')
    {
        $this->admControlInit($request);
        return $this->ajaxTblSelector($rT);
    }
    
    public function fldSelector(Request $request, $rF = '')
    {
        $this->admControlInit($request);
        return $this->ajaxFldSelector($rF);
    }
    
    public function getSetFlds(Request $request, $rSet = '')
    {
        $this->admControlInit($request);
        return view(
            'vendor.survloop.admin.db.inc-getTblsFldsDropOpts', 
            [
                "setOptions" => $GLOBALS["SL"]->getAllSetTblFldDrops($rSet)
            ]
        );
    }
    
    public function getSetFldVals(Request $request, $fldID = '')
    {
        $this->admControlInit($request);
        $sessData = new SurvData;
        return view(
            'vendor.survloop.admin.db.inc-getTblsFldVals', 
            [ 
                "fldID"  => $fldID,
                "values" => $GLOBALS["SL"]->getFldResponsesByID($fldID)
            ]
        );
    }
    

    
    
    /******************************************************
    *** Helper and Lookup Functions
    ******************************************************/
    
    protected function loadTblGroups()
    {
        $this->v["groupTbls"] = [];
        $tbls = $this->tblQryStd();
        if ($tbls->isNotEmpty()) {
            foreach ($tbls as $tbl) {
                if (!isset($this->v["groupTbls"][$tbl->tbl_group])) {
                    $this->v["groupTbls"][$tbl->tbl_group] = [];
                }
                $this->v["groupTbls"][$tbl->tbl_group][] = $tbl;
            }
        }
        return true;
    }
    
    protected function loadTblForeigns()
    {
        $this->v["tblForeigns"] = [];
        $flds = SLFields::where('fld_foreign_table', '>', 0)
            ->where('fld_spec_type', 'NOT LIKE', 'Generic')
            ->where('fld_database', $this->dbID)
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                if (!isset($this->v["tblForeigns"][$fld->fld_foreign_table])) {
                    $this->v["tblForeigns"][$fld->fld_foreign_table] = '';
                }
                $this->v["tblForeigns"][$fld->fld_foreign_table] .= ', ' 
                    . $this->printForeignKey($fld, 2, 1);
            }
            foreach ($this->v["tblForeigns"] as $tID => $foreigns) {
                $this->v["tblForeigns"][$tID] = trim(substr($foreigns, 1));
            }
        }
        return true;
    }
    
    protected function loadTblRules()
    {
        $this->v["tblRules"] = [];
        $rules = SLBusRules::where('rule_database', $this->dbID)->get();
        if ($rules->isNotEmpty()) {
            foreach ($rules as $rule) {
                $tblList = $GLOBALS["SL"]->mexplode(',', $rule->RuleTables);
                if (sizeof($tblList) > 0) {
                    foreach ($tblList as $i => $tbl) {
                        $tbl = intVal($tbl);
                        if (!isset($this->v["tblRules"][$tbl])) {
                            $this->v["tblRules"][$tbl] = [];
                        }
                        $this->v["tblRules"][$tbl][] = $rule;
                    }
                }
            }
        }
        return true;
    }
    
    protected function loadDefSets()
    {
        $this->v["defDeets"] = [];
        $defs = SLDefinitions::where('def_set', 'Value Ranges')
            ->where('def_database', $this->dbID)
            ->orderBy('def_subset', 'asc')
            ->orderBy('def_order', 'asc')
            ->orderBy('def_value', 'asc')
            ->get();
        if ($defs->isNotEmpty()) {
            foreach ($defs as $def) {
                if (!isset($this->v["defDeets"][$def->def_subset])) {
                    $this->v["defDeets"][$def->def_subset] = [''];
                }
                $this->v["defDeets"][$def->def_subset][0] .= ';' . $def->def_value;
                $this->v["defDeets"][$def->def_subset][] = $def->def_value;
            }
        }
        $cnt = 0;
        $this->v["defDeetsJS"] = '';
        if (sizeof($this->v["defDeets"]) > 0) {
            foreach ($this->v["defDeets"] as $set => $vals) {
                $this->v["defDeetsJS"] .= 'definitions[' . $cnt 
                    . '] = new Array("' . htmlspecialchars($set) . '", "' 
                    . htmlspecialchars(substr($vals[0], 1)) . '");' . "\n";
                $cnt++;
            }
        }
        return true;
    }
    
    protected function getTblName($id = -3, $link = 1, $xtraTxt = '', $xtraLnk = '')
    {
        return view(
            'vendor.survloop.admin.db.inc-getTblName', 
            [
                "id" => $id, 
                "link" => $link, 
                "xtraTxt" => $xtraTxt, 
                "xtraLnk" => $xtraLnk 
            ]
        );
    }
    
    protected function getForeignTip($fld = [])
    {
        return 'Degree of Participation: ' . $fld->fld_foreign_min . ' Minimum and ' 
            . $fld->fld_foreign_max . ' Maximum number of ' 
            . $this->getTblName($fld->fld_table, 0) 
            . ' records which can be associated with a single record from ' 
            . $this->getTblName($fld->fld_foreign_table, 0);
    }

    protected function tblQryStd()
    {
        return SLTables::where('tbl_database', $this->dbID)
            ->orderBy('tbl_ord', 'asc')
            ->orderBy('tbl_num_foreign_keys', 'desc')
            ->get();
    }
    
    protected function getTblDropOpts($presel = -3, $blankDefTxt = '(select table)')
    {
        $this->v["presel"] = $presel;
        $this->v["blankDefTxt"] = $blankDefTxt;
        return view('vendor.survloop.admin.db.inc-getTblDropOpts', $this->v);
    }
    
    protected function getFldDropOpts($tbl = -3, $presel = -3, $blankDefTxt = '(select field)')
    {
        $ret = '<option value="-3" ' . (($presel == -3) ? 'SELECTED' : '') 
            . ' >' . $blankDefTxt . '</option>';
        if ($tbl > 0) {
            $flds = SLFields::select('fld_id', 'FldName')
                ->where('fld_table', $tbl)
                ->orderBy('fld_ord', 'asc')
                ->get();
            if ($flds->isNotEmpty()) {
                foreach ($flds as $fld) {
                    $ret .= '<option value="'.$fld->fld_id.'" ' 
                        . (($presel == $fld->fld_id) ? 'SELECTED' : '') 
                        . ' >'.$fld->fld_name.'</option>';
                }
            }
        }
        return $ret;
    }
    
    protected function loadGenerics()
    {
        $flds = SLFields::select('fld_id', 'fld_eng')
            ->where('fld_database', $this->dbID)
            ->where('fld_spec_type', 'Generic')
            ->orderBy('fld_ord', 'asc')
            ->orderBy('fld_eng', 'asc')
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                $this->v["dbFldGenerics"][] = [
                    $fld->fld_id,
                    $fld->fld_eng
                ];
            }
        }
        return true;
    }
    
    protected function getFldGenericOpts($presel = -3)
    {
        $this->v["presel"] = $presel;
        if (sizeof($this->v["dbFldGenerics"]) == 0) {
            $this->loadGenerics();
        }
        return view('vendor.survloop.admin.db.inc-getFldGenericOpts', $this->v);
    }
    
    protected function printDbStats()
    {
        return '<div class="pB10 pL20 mTn10">' . $GLOBALS["SL"]->dbRow->db_tables 
            . ' tables, ' . $GLOBALS["SL"]->dbRow->db_fields . ' fields</div>';
    }
    
    protected function printBasicTblDesc($tbl, $foreignKeyTbls = '')
    {
        if ($tbl) {
            $this->v["tbl"] = $tbl;
            $this->v["foreignKeyTbls"] = $foreignKeyTbls;
            return view('vendor.survloop.admin.db.inc-tblDesc', $this->v);
        }
        return '';
    }
    
    protected function printBasicTblFlds($tblID = -3, $tblLinks = 1, $flds = null) 
    {
        if (!$flds || $flds->isEmpty()) {
            $flds = SLFields::where('fld_table', $tblID)
                ->orderBy('fld_ord', 'asc')
                ->get();
        }
        $this->v["flds"] = $flds;
        $this->v["tblID"] = $tblID;
        $this->v["tblLinks"] = $tblLinks;
        $this->v["printTblFldRows"] = '';
        if ($flds->isNotEmpty()) {
            foreach ($flds as $i => $fld) {
                if (!$GLOBALS["SL"]->REQ->has('onlyKeys') 
                    || $fld->fld_foreign_table > 0) {
                    $this->v["printTblFldRows"] .= $this
                        ->printBasicTblFldRow($fld, $tblID, $tblLinks);
                }
            }
        }
        return view('vendor.survloop.admin.db.inc-basicTblFlds', $this->v);
    }
    
    protected function printBasicTblFldRow($fld = [], $tblID = -3, $tblLinks = 1)
    {
        $this->v["fld"] = $fld;
        $this->v["tblID"] = $tblID;
        $this->v["tblLinks"] = $tblLinks;
        $this->v["FldValues"] = $fld->fld_values;
        if (strpos($this->v["FldValues"], 'Def::') !== false 
            || strpos($this->v["FldValues"], 'DefX::') !== false) {
            $range = str_replace('Def::', '', str_replace('DefX::', '', 
                $this->v["FldValues"]));
            if (isset($this->v["dbDefOpts"][$range])) {
                $this->v["FldValues"] = str_replace(';', ' ; ', 
                    $this->v["dbDefOpts"][$range][0]);
            }
        } else {
            $this->v["FldValues"] = str_replace(';', ' ; ', $this->v["FldValues"]);
        }
        $this->v["fldForeignPrint"] = $this->printForeignKey($fld, $tblLinks);
        $this->v["fldGenerics"] = $this->printFldGenerics($fld, $tblLinks);
        return view('vendor.survloop.admin.db.inc-basicTblFldRow', $this->v);
    }
    
    protected function printForeignKey($fld = [], $tblLinks = 1, $whichway = 0)
    {
        if (intVal($fld->fld_foreign_table) > 0 
            && isset($GLOBALS["SL"]->tbl[$fld->fld_foreign_table])
            && isset($GLOBALS["SL"]->tbl[$fld->fld_table])) {
            if ($whichway == 0) {
                return '<a href="/dashboard/db/table/' 
                    . $GLOBALS["SL"]->tbl[$fld->fld_foreign_table] 
                    . '" data-toggle="tooltip" data-placement="top"'
                    . ' title="Degree of Participation: '
                    . $fld->fld_foreign2_min . ' to ' . $fld->fld_foreign2_max . ' ' 
                    . $this->getTblName($fld->fld_foreign_table, 0) 
                    . ' records can be related to a single ' 
                    . $this->getTblName($fld->fld_table, 0) . ' record. ' 
                    . $fld->fld_foreign_min . ' to ' . $fld->fld_foreign_max . ' ' 
                    . $this->getTblName($fld->fld_table, 0) 
                    . ' records can be related to a single ' 
                    . $this->getTblName($fld->fld_foreign_table, 0) . ' record." >'
                    . '<i class="fa fa-link"></i> ' 
                    . $GLOBALS["SL"]->tblEng[$fld->fld_foreign_table] 
                    . '<sup>(' . $fld->fld_foreign2_min . ',' . $fld->fld_foreign2_max 
                    . ')-(' . $fld->fld_foreign_min . ',' . $fld->fld_foreign_max 
                    . ')</sup></a>';
            } else {
                return '<a href="/dashboard/db/table/' 
                    . $GLOBALS["SL"]->tbl[$fld->fld_table] 
                    . '" data-toggle="tooltip" data-placement="top"'
                    . ' title="Degree of Participation: '
                    . $fld->fld_foreign_min . ' to ' . $fld->fld_foreign_max . ' ' 
                    . $this->getTblName($fld->fld_table, 0) 
                    . ' records can be related to a single ' 
                    . $this->getTblName($fld->fld_foreign_table, 0) . ' record. ' 
                    . $fld->fld_foreign2_min . ' to ' . $fld->fld_foreign2_max . ' ' 
                    . $this->getTblName($fld->fld_foreign_table, 0) 
                    . ' records can be related to a single ' 
                    . $this->getTblName($fld->fld_table, 0) . ' record." >'
                    . '<i class="fa fa-link"></i> ' 
                    . $GLOBALS["SL"]->tblEng[$fld->fld_table] 
                    . '<sup>(' . $fld->fld_foreign_min . ',' . $fld->fld_foreign_max 
                    . ')-(' . $fld->fld_foreign2_min . ',' . $fld->fld_foreign2_max 
                    . ')</sup></a>';
            }
        }
        return '';
    }
    
    protected function printFldGenerics($fld = [], $tblLinks = 1) 
    {
        $repList = '';
        if ($fld->fld_spec_type == 'Generic') {
            $repList = '<br />Replica Copies: ';
            $replicas = SLFields::select('fld_table')
                ->where('fld_spec_source', $fld->fld_id)
                ->where('fld_spec_type', 'Replica')
                ->get();
            if ($replicas->isNotEmpty()) {
                foreach ($replicas as $rep) {
                    $repList .= ', ' . $this->getTblName($rep->fld_table, $tblLinks);
                }
            }
        }
        return $repList;
    }
    
    protected function foreignLinkCnt($preSel = '1')
    {
        $ret = '<option value="N" ' . (($preSel == 'N') ? 'SELECTED' : '') 
            . ' >N    (unlimited)</option>';
        for ($i = 0; $i < 100; $i++) {
            $ret .= '<option value="'.$i.'" ' 
                . (($preSel == (''.$i.'')) ? 'SELECTED' : '')
                . ' >' . $i . '</option>';
        }
        return $ret;
        //view ( 'vendor.survloop.admin.db.inc-getLinkCnt', array("preSel" => $preSel) );
    }
    
    function getFldArr($RuleFields = '') 
    {
        return SLFields::select('fld_id', 'fld_table', 'fld_name')
            ->whereIn('fld_id', $GLOBALS["SL"]->mexplode(',', $RuleFields))
            ->orderBy('fld_table', 'asc')
            ->orderBy('fld_ord', 'asc')
            ->orderBy('fld_eng', 'asc')
            ->get();
    }
    
    protected function tblListID2Link($tblcommas = ',')
    {
        $tblList = '';
        if (trim($tblcommas) != '' && trim($tblcommas) != ',') {
            $tblArr = $GLOBALS["SL"]->mexplode(',', str_replace(',,', ',', $tblcommas));
            if (sizeof($tblArr) > 0) {
                foreach ($tblArr as $i => $tblID) {
                    if (intVal($tblID) > 0) {
                        $tblList .= (($i > 0) ? ', ' : '') . '<nobr>' 
                            . $this->getTblName(intVal($tblID), 1, '', ' target="_blank"') 
                            . '</nobr>';
                    }
                }
            }
        }
        return $tblList;
    }
    
    protected function fldListID2Link($fldcommas = ',') 
    {
        $fldTxt = '';
        $fldList = $this->getFldArr($fldcommas);
        if (sizeof($fldList) > 0) {
            foreach ($fldList as $i => $fld) {
                $fldTxt .= (($i > 0) ? ',&nbsp;&nbsp;&nbsp; ' : '') 
                    . $this->getTblName($fld->fld_table, 1, '', ' target="_blank"') 
                    . ':&nbsp;' 
                    . '<a href="fld.php?fldSpec=' . base64_encode($fld->fld_id) 
                    . '" target="_blank">' . $fld->fld_name . '</a>';
            }
        }
        return $fldTxt;
    }
    
    
}
