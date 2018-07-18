<?php
namespace SurvLoop\Controllers;

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

use SurvLoop\Controllers\SurvLoopData;
use SurvLoop\Controllers\CoreGlobals;
use SurvLoop\Controllers\AdminController;

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
        $this->dbTitle = '<span class="f40 red">' . $GLOBALS["SL"]->dbRow->DbName . '&nbsp;</span>';
        $this->dbSubTitle = '<span class="f14 red">' . $GLOBALS["SL"]->dbRow->DbDesc . '</span>';
        $this->v["dbAllowEdits"] = ($this->v["user"] && $this->v["user"]->hasRole('administrator|databaser'));
        $this->v["mission"] = view('vendor.survloop.inc-mission-statement', 
            array("DbMission" => $GLOBALS["SL"]->dbRow->DbMission));
        if (trim($this->v["currPage"][0]) == '') $this->v["currPage"][0] = '/dashboard/db';
        $this->v["help"] = '<span class="f10 gryA">?</span>&nbsp;&nbsp;&nbsp;';
        $this->loadLookups();
        set_time_limit(180);
        return true;
    }
    
    protected function loadBelowAdmMenu()
    {
        return $this->loadTreesPagesBelowAdmMenu();
    }
    
    protected function cacheFlush()
    {
        Cache::flush();
        return true;
    }
    
    protected function loadLookups()
    {
        $runChecks = false;
        if (!session()->has('dbDesignChecks')) session()->put('dbDesignChecks', 0);
        else session()->put('dbDesignChecks', (1+session()->get('dbDesignChecks')));
        // moderating cleanup to periodic page loads
        if (session()->get('dbDesignChecks')%10 == 0) $runChecks = true;  
        
        $this->v["FldDataTypes"] = [];
        $this->v["FldDataTypes"]['VARCHAR']  = array('Text/String (255 characters max)', 'Text');
        $this->v["FldDataTypes"]['TEXT']     = array('Long Text/String',                 'Text-Long');
        $this->v["FldDataTypes"]['INT']      = array('Integer',                          'Number');
        $this->v["FldDataTypes"]['DOUBLE']   = array('Decimal/Large Number',             'Number-Decimals');
        $this->v["FldDataTypes"]['DATE']     = array('Date',                             'Date');
        $this->v["FldDataTypes"]['DATETIME'] = array('Date and Time',                    'Date&Time');
        
        $tbls = SLTables::select('TblID', 'TblName', 'TblEng', 'TblAbbr', 'TblOpts')
            ->where('TblDatabase', $this->dbID)
            ->orderBy('TblOrd', 'asc')
            ->get();
        if ($tbls->isNotEmpty()) {
            foreach ($tbls as $tbl) {
                if ($runChecks) {
                    if ($tbl->TblOpts%3 == 0) $tbl->TblOpts = $tbl->TblOpts/3;
                    $keyFlds = SLFields::where('FldTable', $tbl->TblID)
                        ->where('FldKeyType', 'LIKE', '%Primary%')
                        ->first();
                    if ($keyFlds) $tbl->TblOpts *= 3;
                    $tbl->save();
                }
            }
        }
        
        $this->v["dbBusRulesFld"] = [];
        $busRules = SLBusRules::select('RuleID', 'RuleStatement', 'RuleFields')
            ->where('RuleDatabase', $this->dbID)
            ->get();
        if ($busRules->isNotEmpty()) {
            foreach ($busRules as $rule) {
                $fldList = $GLOBALS["SL"]->mexplode(',', $rule->RuleFields);
                if (sizeof($fldList) > 0) {
                    foreach ($fldList as $fldID) {
                        $this->v["dbBusRulesFld"][intVal($fldID)] = array($rule->ruleID, $rule->RuleStatement);
                    }
                }
            }
        }
        if ($runChecks) $this->refreshTableStats();
        $this->v["dbStats"] = $this->printDbStats();
        return true;
    }
    
    protected function refreshTableStats()
    {
        $tblForeigns = [];
        if (sizeof($GLOBALS["SL"]->tbls) > 0) {
            foreach ($GLOBALS["SL"]->tbls as $tblID) {
                $tblForeigns[$tblID] = array(0, 0, 0);
            }
        }
        $flds = SLFields::select('FldTable', 'FldForeignTable')
            ->where('FldTable', '>', 0)
            ->where('FldDatabase', $this->dbID)
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                if (isset($tblForeigns[$fld->FldTable])) {
                    $tblForeigns[$fld->FldTable][0]++;
                    if ($fld->FldForeignTable > 0 && isset($tblForeigns[$fld->FldForeignTable])) {
                        $tblForeigns[$fld->FldTable][1]++;
                        $tblForeigns[$fld->FldForeignTable][2]++;
                    }
                }
            }
        }
        foreach ($tblForeigns as $tblID => $tblTots) {
            SLTables::find($tblID)->update([ 
                'TblNumFields'      => $tblTots[0], 
                'TblNumForeignKeys' => $tblTots[1], 
                'TblNumForeignIn'   => $tblTots[2] 
            ]);
        }
        $tbls = SLTables::select('TblID')
            ->where('TblDatabase', $this->dbID)
            ->get();
        $flds = SLFields::select('FldID')
            ->where('FldDatabase', $this->dbID)
            ->get();
        $GLOBALS["SL"]->dbRow->update([
            'DbTables' => $tbls->count(), 
            'DbFields' => $flds->count() 
        ]);
        return true;
    }
    
    protected function loadDefOpts()
    {
        $this->v["dbDefOpts"] = [];
        $defs = SLDefinitions::where('DefSet', 'Value Ranges')
            ->where('DefDatabase', $this->dbID)
            ->get();
        if ($defs->isNotEmpty()) {
            foreach ($defs as $def) {
                if (!isset($this->v["dbDefOpts"][$def->DefSubset])) {
                    $this->v["dbDefOpts"][$def->DefSubset] = array('');
                }
                $this->v["dbDefOpts"][$def->DefSubset][0] .= ';'.$def->DefValue;
                $this->v["dbDefOpts"][$def->DefSubset][] = $def->DefValue;
            }
            foreach ($this->v["dbDefOpts"] as $subset => $vals) {
                $this->v["dbDefOpts"][$subset][0] = substr($this->v["dbDefOpts"][$subset][0], 1);
            }
        }
        return true;
    }
    
    protected function getDefOpts($item = '', $link = 0)
    {
        if (empty($this->v["dbDefOpts"])) $this->loadDefOpts();
        if (isset($this->v["dbDefOpts"][$item]) && isset($this->v["dbDefOpts"][$item][0])) {
            return $this->v["dbDefOpts"][$item][0];
        }
        return '';
    }
    
    function logActions($actions = [])
    {
        $log = new SLLogActions;
        $log->LogDatabase = $this->dbID;
        $log->LogUser = Auth::user()->id;
        $log->save();
        $log->update($actions);
        return true;
    }
    

    
    
    /******************************************************
    *** Main Pages Called by Routes
    ******************************************************/
    
    public function index(Request $request)
    {
        $this->admControlInit($request);
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
        if ($this->v["onlyKeys"]) $cacheB = '.onlyKeys';
        if ($pubPrint) {
            $this->v["isPrint"] = true;
        } else {
            $this->admControlInit($request, '/dashboard/db/all');
        }
        if (!$this->checkCache('/db/' . $GLOBALS["SL"]->dbRow->DbPrefix . $cacheB)) {
            $this->loadTblGroups();
            $this->loadTblForeigns();
            $this->loadTblRules();
            $GLOBALS["SL"]->loadFldAbout();
            $this->v["basicTblFlds"] = $this->v["basicTblDescs"] = [];
            if (sizeof($this->v["groupTbls"]) > 0) {
                foreach ($this->v["groupTbls"] as $group => $tbls) {
                    foreach ($tbls as $tbl) {
                        $this->v["basicTblFlds"][$tbl->TblID] 
                            = $this->printBasicTblFlds($tbl->TblID, (($this->v["isExcel"]) ? -1 : 2));
                        $this->v["basicTblDescs"][$tbl->TblID] 
                            = $this->printBasicTblDesc($tbl, ((isset($this->v["tblForeigns"][$tbl->TblID])) 
                                ? $this->v["tblForeigns"][$tbl->TblID] : ''));
                    }
                }
            }
            $this->v["content"] = view('vendor.survloop.admin.db.full-innerTable', $this->v);
        }
        if ($pubPrint) return $this->v["content"];
        if (!$this->checkCache('/dashboard/db/all' . $cacheB)) {
            // this shouldn't be needed, why is it happening?..
            //$this->v["innerTable"] = str_replace('&lt;', '<', str_replace('&gt;', '>', 
            //    str_replace('"&quot;', '"', str_replace('&quot;"', '"', $this->v["innerTable"]))));
            if ($this->v["isExcel"]) {
                $GLOBALS["SL"]->exportExcelOldSchool('<tr><td colspan=5 ><b>Complete Database Table Field Listings'
                    . '</b></td></tr>' . $this->v["innerTable"], 'FullTableListings'.date("ymd").'.xls');
                exit;
            }
            $this->v["genericFlds"] = [];
            if (!$this->v["isPrint"] && !$this->v["isExcel"]) { // though shouldn't be here if is Excel
                $genericFlds = SLFields::where('FldSpecType', 'Generic')
                    ->where('FldDatabase', $this->dbID)
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
        $db = SLDatabases::where('DbPrefix', str_replace('_', '', $dbPrefix) . '_')
            ->get();
        if ($db->isNotEmpty()) {
            foreach ($db as $d) {
                if ($d->DbOpts%3 > 0) { // no admin databases made public [for now]
                    $this->dbID = $d->DbID;
                    $tree = SLTree::where('TreeDatabase', $this->dbID)
                        ->orderBy('TreeID', 'desc')
                        ->get();
                    if ($tree->isNotEmpty()) {
                        foreach ($tree as $t) {
                            if ($t->TreeOpts%3 > 0) { // no admin trees made public [for now]
                                $this->treeID = $t->TreeID;
                            }
                        }
                    }
                    $GLOBALS["SL"] = new CoreGlobals($request, $this->dbID, $this->treeID, $this->treeID);
                }
            }
        }
        $this->survLoopInit($request, '/db/' . str_replace('_', '', $dbPrefix));
        $this->v["content"] = view('vendor.survloop.print-header-legal', [])->render() . '<div class="pL20"><h2>' 
            . $GLOBALS["SL"]->dbRow->DbName . ': Database Design Specs</h2></div><div class="p20">' 
            . $this->full($request, true) . '</div>';
        //echo 'adminPrintFullDBPublic(' . $dbPrefix . '<pre>'; print_r($db); echo '</pre>'; exit;
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
        $this->v["tbl"] = SLTables::where('TblName', $tblName)
            ->where('TblDatabase', $this->dbID)
            ->first();
//echo '<pre>'; print_r($GLOBALS["SL"]->fldTypes[$tblName]); echo '</pre>';
        if (trim($tblName) == '' || !$this->v["tbl"]) {
            return $this->index($GLOBALS["SL"]->REQ);
        }
        $this->v["rules"] = SLBusRules::where('RuleTables', 'LIKE', '%,'.$this->v["tbl"]->TblID.',%')->get();
        $this->v["flds"] = SLFields::where('FldTable', $this->v["tbl"]->TblID)
            ->where('FldDatabase', $this->dbID)
            ->orderBy('FldOrd', 'asc')
            ->get();
        if (isset($this->v["tbl"]->TblExtend) && intVal($this->v["tbl"]->TblExtend) > 0) {
            $this->v["flds"] = $GLOBALS["SL"]->addFldRowExtends($this->v["flds"], $this->v["tbl"]->TblExtend);
        }
        $this->v["foreignsFlds"] = '';
        $foreignsFlds = SLFields::where('FldForeignTable', $this->v["tbl"]->TblID)
            ->where('FldTable', '>', 0)
            ->where('FldDatabase', $this->dbID)
            ->orderBy('FldID', 'asc')
            ->get();
        if ($foreignsFlds->isNotEmpty()) {
            foreach ($foreignsFlds as $cnt => $foreign) {
                $this->v["foreignsFlds"] .= (($cnt > 0) ? ', ' : '') . $this->getTblName($foreign->FldTable);
            }
        }
        $this->v["basicTblFlds"] = $this->printBasicTblFlds($this->v["tbl"]->TblID, 1, $this->v["flds"]);
        return view('vendor.survloop.admin.db.tableView', $this->v);
    }
    
    public function printEditTable($tblName = '')
    {
        if (!$this->v["dbAllowEdits"]) return $this->printOverview();
        $this->v["tblName"] = $tblName;
        $this->v["tbl"] = new SLTables;
        if (trim($tblName) != '') $this->v["tbl"] = SLTables::where('TblName', $tblName)
            ->where('TblDatabase', $this->dbID)
            ->first();
        
        if ($GLOBALS["SL"]->REQ->has('tblEditForm')) {
            if ($GLOBALS["SL"]->REQ->has('deleteTbl')) {
                SLFields::where('FldTable', $this->v["tbl"]->TblID)->delete();
                $this->v["tbl"]->delete();
                return $this->printOverview();
            }
            $logActions = [
                'LogAction'  => 'Edit', 
                'LogTable'   => $this->v["tbl"]->TblID, 
                'logField'   => 0, 
                'logOldName' => $this->v["tbl"]->TblName, 
                'logNewName' => $GLOBALS["SL"]->REQ->TblName
            ];
            if (trim($tblName) == '') {
                $this->v["tbl"]->TblDatabase = $this->dbID;
            }
            $this->v["tbl"]->TblName  = $GLOBALS["SL"]->REQ->TblName;
            $this->v["tbl"]->TblEng   = $GLOBALS["SL"]->REQ->TblEng;
            $this->v["tbl"]->TblAbbr  = $GLOBALS["SL"]->REQ->TblAbbr;
            $this->v["tbl"]->TblDesc  = $GLOBALS["SL"]->REQ->TblDesc;
            $this->v["tbl"]->TblNotes = $GLOBALS["SL"]->REQ->TblNotes;
            $this->v["tbl"]->TblGroup = $GLOBALS["SL"]->REQ->TblGroup;
            $this->v["tbl"]->TblType  = $GLOBALS["SL"]->REQ->TblType;
            $this->v["tbl"]->save();
            if (trim($tblName) == '' || $GLOBALS["SL"]->REQ->has('forceCreate')) {
                $logActions["LogAction"] = 'New';
                DB::statement("CREATE TABLE `" . $GLOBALS["SL"]->dbRow->DbPrefix . $this->v["tbl"]->TblName . "` (
                  `" . $this->v["tbl"]->TblAbbr . "ID` int(11) NOT NULL AUTO_INCREMENT,
                  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`" . $this->v["tbl"]->TblAbbr . "ID`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
            }
            $this->logActions($logActions);
            $this->cacheFlush();
            return $this->printViewTable($this->v["tbl"]->TblName);
        }
        
        return view('vendor.survloop.admin.db.tableEdit', $this->v);
    }
    
    
    public function printEditField($tblAbbr = '', $fldName = '')
    {
        $this->v["fldName"] = $fldName;
        $this->v["tbl"] = SLTables::where('TblAbbr', $tblAbbr)
            ->where('TblDatabase', $this->dbID)
            ->first();
        if (!$this->v["dbAllowEdits"] || !isset($this->v["tbl"]->TblID)) {
            return $this->printOverview();
        }
        $fld = new SLFields;
        if (trim($fldName) != '') {
            $fld = SLFields::where('FldName', $fldName)
                ->where('FldTable', $this->v["tbl"]->TblID)
                ->where('FldDatabase', $this->dbID)
                ->first();
        } else {
            $fld->FldDatabase = $this->dbID;
            $fld->FldTable    = $this->v["tbl"]->TblID;
        }
        
        // Check invalid starting points
        if (intVal($fld->FldOpts) == 0)         $fld->FldOpts = 1;
        if (intVal($fld->FldCompareSame) == 0)  $fld->FldCompareSame = 1;
        if (intVal($fld->FldCompareOther) == 0) $fld->FldCompareOther = 1;
        if (intVal($fld->FldCompareValue) == 0) $fld->FldCompareValue = 1;
        if (intVal($fld->FldOperateSame) == 0)  $fld->FldOperateSame = 1;
        if (intVal($fld->FldOperateOther) == 0) $fld->FldOperateOther = 1;
        if (intVal($fld->FldOperateValue) == 0) $fld->FldOperateValue = 1;
        
        if ($GLOBALS["SL"]->REQ->has('FldName')) {
            $this->cacheFlush();
            $logActions = [
                'logAction'  => 'Edit', 
                'LogTable'   => $this->v["tbl"]->TblID, 
                'logField'   => $fld->FldID, 
                'logOldName' => $fld->FldName, 
                'logNewName' => $GLOBALS["SL"]->REQ->FldName
            ];
            if ($GLOBALS["SL"]->REQ->has('delete')) {
                $logActions["LogAction"] = 'Delete';
                $fld->delete();
            } else { // not deleting...
                if (trim($fldName) == '') {
                    $logActions["LogAction"] = 'New';
                    $ordChk = SLFields::where('FldDatabase', $this->dbID)
                        ->where('FldTable', $this->v["tbl"]->TblID)
                        ->orderBy('FldOrd', 'desc')
                        ->first();
                    if ($ordChk) $fld->FldOrd = 1+$ordChk->FldOrd;
                }
                
                $fld->FldEng                 = $GLOBALS["SL"]->REQ->FldEng;
                $fld->FldName                = $GLOBALS["SL"]->REQ->FldName;
                $fld->FldDesc                = $GLOBALS["SL"]->REQ->FldDesc;
                $fld->FldNotes               = $GLOBALS["SL"]->REQ->FldNotes;
                $fld->FldType                = $GLOBALS["SL"]->REQ->FldType;
                $fld->FldKeyType             = ',';
                $fld->FldForeignTable        = intVal($GLOBALS["SL"]->REQ->FldForeignTable);
                $fld->FldForeignMin          = $GLOBALS["SL"]->REQ->FldForeignMin;
                $fld->FldForeignMax          = $GLOBALS["SL"]->REQ->FldForeignMax;
                $fld->FldForeign2Min         = $GLOBALS["SL"]->REQ->FldForeign2Min;
                $fld->FldForeign2Max         = $GLOBALS["SL"]->REQ->FldForeign2Max;
                $fld->FldIsIndex             = $GLOBALS["SL"]->REQ->FldIsIndex;
                $fld->FldValues              = $GLOBALS["SL"]->REQ->FldValues;
                $fld->FldDefault             = $GLOBALS["SL"]->REQ->FldDefault;
                $fld->FldSpecType            = $GLOBALS["SL"]->REQ->FldSpecType;
                $fld->FldSpecSource          = intVal($GLOBALS["SL"]->REQ->FldSpecSource);
                $fld->FldNullSupport         = $GLOBALS["SL"]->REQ->FldNullSupport;
                $fld->FldOpts                = 1;
                if ($GLOBALS["SL"]->dbFullSpecs()) {
                    $fld->FldAlias           = $GLOBALS["SL"]->REQ->FldAlias;
                    $fld->FldDataType        = $GLOBALS["SL"]->REQ->FldDataType;
                    $fld->FldDataLength      = intVal($GLOBALS["SL"]->REQ->FldDataLength);
                    $fld->FldDataDecimals    = intVal($GLOBALS["SL"]->REQ->FldDataDecimals);
                    $fld->FldInputMask       = $GLOBALS["SL"]->REQ->FldInputMask;
                    $fld->FldDisplayFormat   = $GLOBALS["SL"]->REQ->FldDisplayFormat;
                    $fld->FldKeyStruct       = $GLOBALS["SL"]->REQ->FldKeyStruct;
                    $fld->FldEditRule        = $GLOBALS["SL"]->REQ->FldEditRule;
                    $fld->FldUnique          = intVal($GLOBALS["SL"]->REQ->FldUnique);
                    $fld->FldNullSupport     = intVal($GLOBALS["SL"]->REQ->FldNullSupport);
                    $fld->FldValuesEnteredBy = $GLOBALS["SL"]->REQ->FldValuesEnteredBy;
                    $fld->FldRequired        = intVal($GLOBALS["SL"]->REQ->FldRequired);
                    $fld->FldCompareSame     = $fld->FldCompareOther = $fld->FldCompareValue = 1;
                    $fld->FldOperateSame     = $fld->FldOperateOther = $fld->FldOperateValue = 1;
                    $fld->FldCharSupport     = ',';
                    if (is_array($GLOBALS["SL"]->REQ->FldCharSupport) 
                        && sizeof($GLOBALS["SL"]->REQ->FldCharSupport) > 0) {
                        foreach ($GLOBALS["SL"]->REQ->FldCharSupport  as $val) {
                            $fld->FldCharSupport .= $val.',';
                        }
                    }
                    foreach (['FldCompareSame', 'FldCompareOther', 'FldCompareValue', 
                        'FldOperateSame', 'FldOperateOther', 'FldOperateValue'] as $co) {
                        if ($GLOBALS["SL"]->REQ->has($co) && is_array($GLOBALS["SL"]->REQ->input($co)) 
                            && sizeof($GLOBALS["SL"]->REQ->input($co)) > 0) {
                            if (in_array(3, $GLOBALS["SL"]->REQ->input($co))) $fld->{$co} = 6;
                            else {
                                foreach ($GLOBALS["SL"]->REQ->input($co) as $val) $fld->{$co} *= $val;
                                foreach (array(5, 7, 11, 13, 17, 19) as $cod) {
                                    if ($fld->{$co}%$cod == 0) $fld->{$co} *= $fld->{$co}/$cod;
                                }
                            }
                        }
                    }
                }
                if ($GLOBALS["SL"]->REQ->has('FldValuesDefX') && trim($GLOBALS["SL"]->REQ->FldValuesDefX) == 'X') {
                    $fld->FldOpts *= 5;
                }
                
                if ($GLOBALS["SL"]->REQ->has('FldKeyType') && is_array($GLOBALS["SL"]->REQ->FldKeyType) 
                    && sizeof($GLOBALS["SL"]->REQ->FldKeyType) > 0) {
                    foreach ($GLOBALS["SL"]->REQ->FldKeyType as $val) $fld->FldKeyType .= $val.',';
                }
                if ($GLOBALS["SL"]->REQ->FldSpecType == 'Generic' || ($GLOBALS["SL"]->REQ->has('saveGeneric') 
                    && $GLOBALS["SL"]->REQ->saveGeneric == 1)) {
                    $fld->FldTable = 0;
                }
                if ($GLOBALS["SL"]->REQ->has('FldValuesDef') && trim($GLOBALS["SL"]->REQ->FldValuesDef) != '') {
                    $fld->FldValues = 'Def::'.$GLOBALS["SL"]->REQ->FldValuesDef;
                }
                
                $fld->save();
                
                if ($GLOBALS["SL"]->REQ->has('pushGeneric') && intVal($GLOBALS["SL"]->REQ->pushGeneric) == 1) {
                    $replicaFlds = SLFields::where('FldSpecSource', $fld->FldID)
                        ->where('FldDatabase', $this->dbID)
                        ->get();
                    if ($replicaFlds->isNotEmpty()) {
                        foreach ($replicaFlds as $replica) {
                            $genericCopy = $fld->replicate()->save();
                            $genericCopy->FldSpecType   = 'Replica';
                            $genericCopy->FldSpecSource = $fld->FldID;
                            $genericCopy->FldTable      = $replica->FldTable;
                            $genericCopy->FldOrd        = $replica->FldOrd;
                            $genericCopy->save();
                            $replica->delete();
                        }
                    }
                }
            }
            
            $this->logActions($logActions);
            $this->refreshTableStats();
            if ($fld->FldTable > 0) return $this->redir('/dashboard/db/table/'.$this->v["tbl"]->TblName);
            else return $this->printViewTable('Generic');
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
        $this->v["fldSfx"] = (intVal($this->v["fld"]->FldID) > 0) ? $this->v["fld"]->FldID : 'New';
        $this->v["forKeyChange"] = '';
        $this->v["FldSpecSourceJSlnk"] = '?tbl=' . $GLOBALS["SL"]->REQ->tbl . '&' 
            . (($GLOBALS["SL"]->REQ->has('edit')) ? 'edit='.$GLOBALS["SL"]->REQ->edit : 'add=1');
        $this->v["defSet"] = '';
        if (strpos($fld->FldValues, 'Def::') !== false || strpos($fld->FldValues, 'DefX::') !== false) {
            $this->v["defSet"] = trim(str_replace('Def::', '', str_replace('DefX::', '', $fld->FldValues)));
        }
        $GLOBALS["SL"]->loadFldAbout();
        return view('vendor.survloop.admin.db.fieldSpecifications', $this->v);
    }
    
    public function businessRules(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/bus-rules');
        if ($GLOBALS["SL"]->REQ->has('delRule') && $GLOBALS["SL"]->REQ->delRule > 0 && $this->v["dbAllowEdits"]) {
            $delRule = SLBusRules::find($GLOBALS["SL"]->REQ->delRule);
            if ($delRule->isNotEmpty()) $delRule->delete();
        }
        $this->v["rules"] = SLBusRules::where('RuleDatabase', $this->dbID)
            ->orderBy('RuleTables', 'asc')
            ->orderBy('RuleFields', 'asc')
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
        if (trim($rT) == '') $rT = ',';
        if ($GLOBALS["SL"]->REQ->has('addT') && trim($GLOBALS["SL"]->REQ->addT) != '' 
            && strpos($rT, ','.$GLOBALS["SL"]->REQ->addT.',') === false) {
            $rT .= $GLOBALS["SL"]->REQ->addT.',';
        }
        if ($GLOBALS["SL"]->REQ->has('delT') && trim($GLOBALS["SL"]->REQ->delT) != '') {
            $rT = str_replace(','.$GLOBALS["SL"]->REQ->delT.',', ',', $rT);
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
        if ($GLOBALS["SL"]->REQ->has('addT')) $this->v["addT"] = intVal($GLOBALS["SL"]->REQ->addT);
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
            $this->v["fldList"][$i] = [
                $fld->FldID, 
                $this->getTblName($fld->FldTable, 1, '', ' target="_blank"') . ':&nbsp;' . $fld->FldName
            ];
        }
        return view('vendor.survloop.admin.db.ajaxTblFldSelectorF', $this->v);
    }
    
    
    public function definitions(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/definitions');
        $this->v["defSets"] = [];
        $defs = SLDefinitions::where('DefSet', 'Value Ranges')
            ->where('DefDatabase', $this->dbID)
            ->orderBy('DefSubset', 'asc')
            ->orderBy('DefOrder', 'asc')
            ->orderBy('DefValue', 'asc')
            ->get();
        if ($defs->isNotEmpty()) {
            foreach ($defs as $cnt => $def) {
                if (!isset($this->v["defSets"][$def->DefSubset])) $this->v["defSets"][$def->DefSubset] = [];
                $this->v["defSets"][$def->DefSubset][] = $def;
            }
        }
        return view('vendor.survloop.admin.db.definitions', $this->v);
    }
    
    public function defAdd(Request $request, $set = '')
    {
        $this->admControlInit($request, '/dashboard/db/definitions');
        return $this->printDefEdit(-3, ((trim($set) != '') ? urldecode($set) : ''));
    }
    
    public function defEdit(Request $request, $defID)
    {
        $this->admControlInit($request, '/dashboard/db/definitions');
        return $this->printDefEdit($defID);
    }
    
    public function printDefEdit($defID = -3, $subset = '')
    {
        if (!$this->v["dbAllowEdits"]) return $this->printOverview();
        $this->v["defID"]  = $defID;
        $this->v["subset"] = $subset;
        $this->v["def"]    = new SLDefinitions;
        if ($defID > 0) {
            $this->v["def"] = SLDefinitions::where('DefID', $defID)
                ->where('DefDatabase', $this->dbID)
                ->first();
        } else {
            $this->v["def"]->DefSubset     = $subset;
            $this->v["def"]->DefDatabase   = $this->dbID;
            $this->v["def"]->DefOrder      = 0;
        }
        
        if ($GLOBALS["SL"]->REQ->has('defEditForm')) {
            $aname = str_replace(' ', '', $subset);
            $this->cacheFlush();
            if ($GLOBALS["SL"]->REQ->has('deleteDef')) {
                $this->v["def"]->delete();
                return $this->redir('/dashboard/db/definitions#' . $aname);
            }
            if (trim($subset) != '' && $defID <= 0) {
                $setVals = SLDefinitions::where('DefSubset', $subset)
                    ->where('DefSet', 'Value Ranges')
                    ->where('DefDatabase', $this->dbID)
                    ->get();
                $this->v["def"]->DefOrder = $setVals->count();
            }
            $this->v["def"]->DefSet        = 'Value Ranges';
            $this->v["def"]->DefSubset     = $GLOBALS["SL"]->REQ->defSubset;
            if ($GLOBALS["SL"]->REQ->defSubset == '_' && $GLOBALS["SL"]->REQ->has('newSubset')) {
                $this->v["def"]->DefSubset = $GLOBALS["SL"]->REQ->newSubset;
            }
            $this->v["def"]->DefValue         = $GLOBALS["SL"]->REQ->defValue;
            $this->v["def"]->DefDescription = $GLOBALS["SL"]->REQ->defDescription;
            $this->v["def"]->save();
            return $this->redir('/dashboard/db/definitions#' . $aname);
        }
        
        $this->v["subList"] = SLDefinitions::select('DefSubset')
            ->distinct()
            ->where('DefSet', 'Value Ranges')
            ->where('DefDatabase', $this->dbID)
            ->orderBy('DefSubset')
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
            $this->v["rule"] = SLBusRules::where('RuleID', $ruleID)
                ->where('RuleDatabase', $this->dbID)
                ->first();
        } else {
            $this->v["rule"]->RuleDatabase = $this->dbID;
            $this->v["rule"]->RuleTables = $this->v["rule"]->RuleFields = ',';
        }
        $primeFlds = array('RuleTestOn', 'RulePhys', 'RuleLogic', 'RuleRel'); 
        
        if ($GLOBALS["SL"]->REQ->has('ruleEditForm') && $this->v["dbAllowEdits"]) {
            $this->cacheFlush();
            $this->v["rule"]->RuleStatement = $GLOBALS["SL"]->REQ->RuleStatement;
            $this->v["rule"]->RuleConstraint = $GLOBALS["SL"]->REQ->RuleConstraint;
            $this->v["rule"]->RuleAction = $GLOBALS["SL"]->REQ->RuleAction;
            $this->v["rule"]->RuleTables = $GLOBALS["SL"]->REQ->RuleTables;
            $this->v["rule"]->RuleFields = $GLOBALS["SL"]->REQ->RuleFields;
            $this->v["rule"]->RuleTestOn = $this->v["rule"]->RulePhys 
                = $this->v["rule"]->RuleLogic = $this->v["rule"]->RuleRel = 1;
            foreach ($primeFlds as $fld) {
                if ($GLOBALS["SL"]->REQ->has($fld) && is_array($GLOBALS["SL"]->REQ->input($fld)) 
                    && sizeof($GLOBALS["SL"]->REQ->input($fld)) > 0) {
                    foreach ($GLOBALS["SL"]->REQ->input($fld) as $prime) {
                        eval("\$this->v['rule']->".$fld." *= \$prime;");
                    }
                }
            }
            if ($GLOBALS["SL"]->REQ->has('RuleType23') && intVal($GLOBALS["SL"]->REQ->RuleType23 > 0)) {
                $this->v["rule"]->RuleType *= intVal($GLOBALS["SL"]->REQ->RuleType23);
            }
            if ($GLOBALS["SL"]->REQ->has('RuleType57') && intVal($GLOBALS["SL"]->REQ->RuleType57 > 0)) {
                $this->v["rule"]->RuleType *= intVal($GLOBALS["SL"]->REQ->RuleType57);
            }
            $this->v["rule"]->save();
        }
        
        $this->v["tblTxt"] = ((isset($this->v["rule"])) ? $this->tblListID2Link($this->v["rule"]->RuleTables) : '');
        $this->v["fldTxt"] = ((isset($this->v["rule"])) ? $this->fldListID2Link($this->v["rule"]->RuleFields) : '');
        $this->v["saveBtn"] = '';
        if ($this->v["dbAllowEdits"]) {
            if ($this->v["ruleID"] <= 0) {
                $this->v["saveBtn"] = '<input type="submit" value="Add New Rule" class="btn btn-lg btn-primary" >';
            }
            else $this->v["saveBtn"] = '<input type="submit" value="Save Rule Changes" class="btn btn-primary" >';
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
            if ($GLOBALS["SL"]->REQ->has('item') && is_array($GLOBALS["SL"]->REQ->input('item')) 
                && sizeof($GLOBALS["SL"]->REQ->input('item')) > 0) {
                foreach ($GLOBALS["SL"]->REQ->input('item') as $i => $value) {
                    $def = SLDefinitions::find($value);
                    $def->DefOrder = $i;
                    $def->save();
                }
            }
            exit;
        }
        
        $sortTitle = '<a href="/dashboard/db/definitions/sort/' . $subset . '" style="font-size: 26px;"><b>' 
            . $this->v["subset"] . '</b></a>';
        $submitURL = '/dashboard/db/definitions/sort/' . $subset . '?saveOrder=1';
        $defs = SLDefinitions::where('DefSubset', $this->v["subset"])
            ->where('DefSet', 'Value Ranges')
            ->where('DefDatabase', $this->dbID)
            ->orderBy('DefOrder')
            ->get();
        $sorts = [];
        if ($defs->isNotEmpty()) {
            foreach ($defs as $def) $sorts[] = array($def->DefID, $def->DefValue);
        }
        $this->v["needsJqUi"] = true;
        $this->v["sortable"] = view('vendor.survloop.inc-sortable', [
            'submitURL' => $submitURL,
            'sortID'    => 'definitions',
            'sortTitle' => $sortTitle,
            'sorts' => $sorts
            ]);
        return view('vendor.survloop.admin.db.defSort', $this->v);
    }
    
    public function tblSort(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/all');
        if (!$this->v["dbAllowEdits"]) return $this->printOverview();
        
        if ($GLOBALS["SL"]->REQ->has('saveOrder')) {
            $this->cacheFlush();
            if ($GLOBALS["SL"]->REQ->has('item') && is_array($GLOBALS["SL"]->REQ->input('item')) 
                && sizeof($GLOBALS["SL"]->REQ->input('item')) > 0) {
                foreach ($GLOBALS["SL"]->REQ->input('item') as $i => $value) {
                    $tbl = SLTables::find($value);
                    $tbl->TblOrd = $i;
                    $tbl->save();
                }
            }
            exit;
        }
        
        $sortTitle = '<a href="/dashboard/db/table/sort" style="font-size: 26px;"><b>All Tables</b></a>
            <div class="f12 slBlueDark">
                Table Name <span class="f10">Type</span>
                <div class="disIn slGrey" style="margin-left: 50px;"><i>Table Group</i></div>
            </div>';
        $submitURL = '/dashboard/db/sortTable?saveOrder=1';
        $tbls = SLTables::select('TblID', 'TblEng', 'TblType', 'TblGroup')
            ->where('TblDatabase', $this->dbID)
            ->orderBy('TblOrd')
            ->get();
        $sorts = [];
        if ($tbls->isNotEmpty()) {
            foreach ($tbls as $tbl) {
                $sorts[] = [
                    $tbl->TblID, 
                    $tbl->TblEng . ' <span style="font-size: 10px;">' . $tbl->TblType . '</span><div class="fR"><i>'
                        . '<span class="slGrey" style="font-size: 12px;">' . $tbl->TblGroup 
                        . '</span></i></div><div class="fC"></div>'
                ];
            }
        }
        $this->v["needsJqUi"] = true;
        $this->v["sortable"] = view('vendor.survloop.inc-sortable', [
            'sortID'    => 'tables',
            'sortTitle' => $sortTitle, 
            'submitURL' => $submitURL, 
            'sorts'     => $sorts
        ]);
        return view('vendor.survloop.admin.db.tableSort', $this->v);
    }
    
    public function fldSort(Request $request, $tblName = '')
    {
        $this->admControlInit($request, '/dashboard/db/all');
        if (!$this->v["dbAllowEdits"] || trim($tblName) == '') {
            return $this->printOverview();
        }
        $this->v["tblName"] = $tblName;
        $this->v["tbl"] = SLTables::where('TblName', $tblName)
            ->where('TblDatabase', $this->dbID)
            ->first();
        if ($GLOBALS["SL"]->REQ->has('saveOrder')) {
            $this->cacheFlush();
            if ($GLOBALS["SL"]->REQ->has('item') && is_array($GLOBALS["SL"]->REQ->input('item')) 
                && sizeof($GLOBALS["SL"]->REQ->input('item')) > 0) {
                foreach ($GLOBALS["SL"]->REQ->input('item') as $i => $value) {
                    $fld = SLFields::find($value);
                    $fld->FldOrd = $i;
                    $fld->save();
                }
            }
            exit;
        }
        $sortTitle = '<a href="/dashboard/db/table/sort" style="font-size: 26px;"><b>' 
            . $this->v["tbl"]->TblName . '&nbsp;&nbsp;&nbsp;(' . $this->v["tbl"]->TblAbbr . ')</b></a>';
        $submitURL = '/dashboard/db/table/'.$tblName.'/sort?saveOrder=1';
        $flds = SLFields::select('FldID', 'FldEng', 'FldName', 'FldType', 'FldForeignTable')
            ->where('FldTable', $this->v["tbl"]->TblID)
            ->where('FldDatabase', $this->dbID)
            ->orderBy('FldOrd')
            ->orderBy('FldEng', 'asc')
            ->get();
        $sorts = [];
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                $sorts[] = [
                    $fld->FldID, 
                    $fld->FldEng . ' <span style="font-size: 10px;">' 
                        . ((intVal($fld->FldForeignTable) > 0) ? '<i class="fa fa-link"></i>' : '') . '</span>'
                        . '<div class="fR"><i><span class="slGrey" style="font-size: 12px;">'
                        . '<span style="font-size: 8px;">('.$fld->FldType.')</span> '
                        . $fld->FldName . '</span></i></div><div class="fC"></div>'
                ];
            }
        }
        $this->v["needsJqUi"] = true;
        $this->v["sortable"] = view('vendor.survloop.inc-sortable', [
            'sortID'    => 'fields',
            'sortTitle' => $sortTitle, 
            'submitURL' => $submitURL, 
            'sorts'     => $sorts
        ]);
        return view('vendor.survloop.admin.db.fieldSort', $this->v);
    }
    
    public function fieldDescs(Request $request, $view = '')
    {
        $this->admControlInit($request, '/dashboard/db/fieldDescs');
        return $this->printFieldDescs($view, false);
    }
    
    public function fieldDescsAll(Request $request, $view = '')
    {
        $this->admControlInit($request, '/dashboard/db/fieldDescs/all');
        return $this->printFieldDescs($view, true);
    }
    
    public function printFieldDescs($view = '', $all = false)
    {
        if (!$this->v["dbAllowEdits"]) return $this->printOverview();
        $this->loadDefOpts();
        $this->v["FldDescsView"] = $view;
        $this->v["FldDescsViewAll"] = $all;
        $this->v["fldTots"] = [ [0, 0], [0, 0], [0, 0] ]; // unique, replica, generic
        $flds = SLFields::select('FldDesc', 'FldSpecType')
            ->where('FldSpecType', 'NOT LIKE', 'Generic')
            ->where('FldDatabase', $this->dbID)
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                $FldType = (($fld->FldSpecType == 'Generic') ? 2 : (($fld->FldSpecType == 'Replica') ? 1 
                        : (($fld->FldSpecType == 'Unique') ? 0 : 0)));
                $this->v["fldTots"][$FldType][1]++;
                if (trim($fld->FldDesc) != '') $this->v["fldTots"][$FldType][0]++;
            }
        }
        $this->v["baseURL"] = '/dashboard/db/fieldDescs' . (($view == 'generics') ? '/generics' 
                : (($view == 'replicas') ? '/replicas' : (($view == 'uniques') ? '/uniques' : '')));
        $this->v["fldLabel"] = (($view == 'generics') ? 'Generics' : (($view == 'replicas') ? 'Replicas' 
            : (($view == 'uniques') ? 'Unique' : '')));
        
        $FldSpecType = ['NOT LIKE', 'Generic'];
        if ($view == 'generics') $FldSpecType = ['LIKE', 'Generic'];
        elseif ($view == 'replicas') $FldSpecType = ['LIKE', 'Replica'];
        elseif ($view == 'uniques') $FldSpecType = ['LIKE', 'Unique'];
        $whereAll = ['FldDesc', 'LIKE'];
        if ($all) $whereAll = ['FldName', 'NOT LIKE'];
        $this->v["fldTot"] = SLFields::select('FldID')
            ->where('FldDatabase', $this->dbID)
            ->where('FldSpecType', $FldSpecType[0], $FldSpecType[1])
            ->where($whereAll[0], $whereAll[1], '')
            ->get();
        $this->v["tblFldLists"] = [];
        $this->v["tblFldVals"] = [];
        if ($this->v["fldTot"]->isNotEmpty()) {
            foreach ($GLOBALS["SL"]->tbls as $tblID) {
                $this->v["tblFldLists"][$tblID] = SLFields::where('FldDatabase', $this->dbID)
                    ->where('FldSpecType', $FldSpecType[0], $FldSpecType[1])
                    ->where($whereAll[0], $whereAll[1], '')
                    ->where('FldTable', $tblID)
                    ->orderBy('FldOrd', 'asc')
                    ->orderBy('FldEng', 'asc')
                    ->get();
                if ($this->v["tblFldLists"][$tblID]->isNotEmpty()) {
                    foreach ($this->v["tblFldLists"][$tblID] as $fld) {
                        $this->v["tblFldVals"][$fld->FldID] = str_replace(';', ' ; ', $fld->FldValues);
                        if (strpos($fld->FldValues, 'Def::') !== false 
                            || strpos($fld->FldValues, 'DefX::') !== false) {
                            $this->v["tblFldVals"][$fld->FldID] = str_replace(';', ' ; ', 
                                $this->getDefOpts(str_replace('Def::', '', 
                                    str_replace('DefX::', '', $fld->FldValues)))); 
                        }
                        if (isset($this->v["dbBusRulesFld"][$fld->FldID])) {
                            $this->v["tblFldVals"][$fld->FldID] .= ' <a href="busrules.php?rule=' 
                                . base64_encode($this->v["dbBusRulesFld"][$fld->FldID][0]) 
                                . '" class="f10" data-toggle="tooltip" data-placement="top"  title="' 
                                . str_replace('"', "'", $this->v["dbBusRulesFld"][$fld->FldID][1]) 
                                . '"><i class="fa fa-university"></i></a>';
                        }
                    }
                }
            }
        }
        return view('vendor.survloop.admin.db.fieldDescs', $this->v);
    }
    
    public function fieldXML(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/fieldXML');
        if (!$this->v["dbAllowEdits"]) return $this->printOverview();
        $this->v["tblsOrdered"] = SLTables::select('TblID')
            ->where('TblDatabase', $this->dbID)
            ->orderBy('TblOrd', 'asc')
            ->get();
        $this->v["tblFldLists"] = [];
        foreach ($GLOBALS["SL"]->tbls as $tblID) {
            $this->v["tblFldLists"][$tblID] = SLFields::where('FldDatabase', $this->dbID)
                ->where('FldSpecType', 'NOT LIKE', 'Generic')
                ->where('FldTable', $tblID)
                ->orderBy('FldOrd', 'asc')
                ->orderBy('FldEng', 'asc')
                ->get();
        }
        return view('vendor.survloop.admin.db.fieldxml', $this->v);
    }
    
    public function fieldXMLsave(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/fieldXML');
        if (!$this->v["dbAllowEdits"]) return '';
        if ($GLOBALS["SL"]->REQ->has('changedFld') && $GLOBALS["SL"]->REQ->changedFld > 0 && $GLOBALS["SL"]->REQ->has('changedFldSetting')) {
            $fld = SLFields::where('FldID', $GLOBALS["SL"]->REQ->changedFld)
                ->where('FldDatabase', $this->dbID)
                ->first();
            if ($fld) {
                if (!isset($fld->FldOpts) || intVal($fld->FldOpts) <= 0) $fld->FldOpts = 1;
                $primes = [7, 11, 13];
                foreach ($primes as $p) {
                    if ($GLOBALS["SL"]->REQ->changedFldSetting == $p) {
                        if ($fld->FldOpts%$p > 0) $fld->FldOpts *= $p;
                    }
                    elseif ($fld->FldOpts%$p == 0) $fld->FldOpts = $fld->FldOpts/$p;
                }
                $fld->save();
            }
        }
        return '';
    }
    
    function fieldDescsSave(Request $request) 
    {
        $this->admControlInit($request, '/dashboard/db/fieldDescs/all');
        if (!$this->v["dbAllowEdits"]) exit;
        $this->cacheFlush();
        if ($GLOBALS["SL"]->REQ->has('changedFLds') && $GLOBALS["SL"]->REQ->changedFLds != '' && $GLOBALS["SL"]->REQ->changedFLds != ',') {
            $flds = $GLOBALS["SL"]->mexplode(',', $GLOBALS["SL"]->REQ->changedFLds);
            if (sizeof($flds) > 0) {
                foreach ($flds as $f) {
                    if (intVal($f) > 0) {
                        SLFields::find($f)->update([ 
                            'FldDesc'  => $GLOBALS["SL"]->REQ->input('FldDesc' . $f . ''), 
                            'FldNotes' => $GLOBALS["SL"]->REQ->input('FldNotes' . $f . '') 
                        ]);
                    }
                }
            }
        }
        if ($GLOBALS["SL"]->REQ->has('changedFLdsGen') && trim($GLOBALS["SL"]->REQ->changedFLdsGen) != '' 
            && trim($GLOBALS["SL"]->REQ->changedFLdsGen) != ',') {
            $flds = $GLOBALS["SL"]->mexplode(',', $GLOBALS["SL"]->REQ->changedFLdsGen);
            if (sizeof($flds) > 0) {
                foreach ($flds as $f) {
                    if (intVal($f) > 0) {
                        SLFields::where($f)
                            ->orWhere(function ($query) { 
                                $query->where('FldSpecType', 'Replica')
                                ->where('FldSpecSource', $f);
                            })
                            ->update([ 
                                'FldDesc' => $GLOBALS["SL"]->REQ->input('FldDesc'.$f.''), 
                                'FldNotes' => $GLOBALS["SL"]->REQ->input('FldNotes'.$f.'') 
                            ]);
                    }
                }
            }
        }
        exit;
    }
    
    public function diagrams(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/diagrams');
        if (!$this->checkCache()) {
            $this->v["printMatrix"] = '';
            $this->v["diags"] = SLDefinitions::where('DefSet', 'Diagrams')
                ->where('DefDatabase', $this->dbID)
                ->orderBy('DefOrder')
                ->get();
            $tblMatrix = [];
            if (sizeof($GLOBALS["SL"]->tbls) > 0) {
                foreach ($GLOBALS["SL"]->tbls as $tID) { 
                    $tblMatrix[$tID] = [];
                    foreach ($GLOBALS["SL"]->tbls as $tID2) $tblMatrix[$tID][$tID2] = [];
                }
                $flds = SLFields::select('FldID', 
                    'FldTable', 'FldForeignTable', 
                    'FldForeignMin', 'FldForeignMax', 
                    'FldForeign2Min', 'FldForeign2Max')
                    ->where('FldTable', '>', 0)
                    ->where('FldForeignTable', '>', 0)
                    ->where('FldDatabase', $this->dbID)
                    ->get();
                if ($flds->isNotEmpty()) {
                    foreach ($flds as $fld) {
                        $dup = false;
                        if (isset($tblMatrix[$fld->FldTable])
                            && isset($tblMatrix[$fld->FldTable][$fld->FldForeignTable])
                            && is_array($tblMatrix[$fld->FldTable][$fld->FldForeignTable])
                            && sizeof($tblMatrix[$fld->FldTable][$fld->FldForeignTable]) > 0) {
                            foreach ($tblMatrix[$fld->FldTable][$fld->FldForeignTable] as $keys) {
                                if ($keys[0] == $fld->FldForeign2Min && $keys[1] == $fld->FldForeign2Max) {
                                    $dup = true;
                                }
                            }
                        }
                        if (!$dup) {
                            $tblMatrix[$fld->FldTable][$fld->FldForeignTable][] 
                                = [$fld->FldForeign2Min, $fld->FldForeign2Max];
                        }
                        $dup = false;
                        if (isset($tblMatrix[$fld->FldForeignTable])
                            && isset($tblMatrix[$fld->FldForeignTable][$fld->FldTable])
                            && is_array($tblMatrix[$fld->FldForeignTable][$fld->FldTable])
                            && sizeof($tblMatrix[$fld->FldForeignTable][$fld->FldTable]) > 0) {
                            foreach ($tblMatrix[$fld->FldForeignTable][$fld->FldTable] as $keys) {
                                if ($keys[0] == $fld->FldForeignMin && $keys[1] == $fld->FldForeignMax) {
                                    $dup = true;
                                }
                            }
                        }
                        if (!$dup) {
                            $tblMatrix[$fld->FldForeignTable][$fld->FldTable][] 
                                = [$fld->FldForeignMin, $fld->FldForeignMax];
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
                                . (($tID == $tID2) ? 'BGblueDark ' : (($cnt2%2 == 0) ? 'cl2' : 'cl1'))
                                . '" data-toggle="tooltip" data-placement="top"  title="';
                            if (sizeof($tblMatrix[$tID][$tID2]) > 0) { 
                                $this->v["printMatrix"] .= $tblMatrix[$tID][$tID2][0][0] . ' to ' 
                                    . $tblMatrix[$tID][$tID2][0][1] . '</b> ' . strip_tags($this->getTblName($tID2, 0) 
                                    . ' records can be related to a single ' . $this->getTblName($tID, 0)) 
                                    . ' record." >' . $tblMatrix[$tID][$tID2][0][0] . ':' 
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
            $this->v["css"] = $this->loadCss();
            $this->v["canvasDimensions"] = array(950, 950);
            $mainCircleCenter = [ $this->v["canvasDimensions"][0]/2, $this->v["canvasDimensions"][1]/2 ];
            $sizeMax = 0;
            $this->v["tables"] = $tableLookup = [];
            //$this->v["tables"][] = array('English', Size, Center-X, Center-Y);
            $tbls = SLTables::select('TblID', 'TblName', 'TblNumForeignKeys', 'TblNumForeignIn', 'TblOpts')
                ->where('TblDatabase', $this->dbID)
                ->orderBy('TblOrd', 'asc')
                ->get();
            if ($tbls->isNotEmpty()) {
                foreach ($tbls as $tbl) {
                    $tableLookup[$tbl->TblID] = $this->v["tables"]->count();
                    $this->v["tables"][] = [
                        $tbl->TblName, 
                        sqrt(sqrt($tbl->TblNumForeignKeys+$tbl->TblNumForeignIn)), 
                        0, 
                        0, 
                        (($GLOBALS["SL"]->isCoreTbl($tbl->TblID)) ? $this->v["css"]["color-success-on"] : '')
                    ];
                }
                foreach ($this->v["tables"] as $i => $tbl) {
                    if ($sizeMax < $this->v["tables"][$i][1]) $sizeMax = $this->v["tables"][$i][1];
                }
                foreach ($this->v["tables"] as $i => $tbl) {
                    if ($sizeMax <= 0) $sizeMax = 1;
                    $this->v["tables"][$i][1] = 43*($this->v["tables"][$i][1]/$sizeMax);
                    if ($this->v["tables"][$i][1] <= 10) $this->v["tables"][$i][1] = 10;
                    $rad = deg2rad(360*$i/sizeof($this->v["tables"]));
                    $dim = 0.42*$this->v["canvasDimensions"][1];
                    $this->v["tables"][$i][2] = round($mainCircleCenter[0]+(sin($rad)*$dim));
                    $this->v["tables"][$i][3] = round($mainCircleCenter[1]-(cos($rad)*$dim));
                }
            }
            $this->v["keyLines"] = [];
            $foreignFlds = SLFields::select('FldTable', 'FldForeignTable')
                ->where('FldForeignTable', '>', 0)
                ->where('FldSpecType', 'NOT LIKE', 'Generic')
                ->where('FldDatabase', $this->dbID)
                ->get();
            if ($foreignFlds->isNotEmpty()) {
                foreach ($foreignFlds as $fld) {
                    if (!isset($tableLookup[$fld->FldTable])) {
                        $this->v["errors"] .= '<br />add line, missing FldTable tblLookup[' . $fld->FldTable . ']';
                    } elseif (!isset($tableLookup[$fld->FldForeignTable])) {
                        $this->v["errors"] .= '<br />add line, missing FldForeignTable tblLookup[' . $fld->FldForeignTable . ']';
                    } else {
                        $this->v["keyLines"][] = [
                            $tableLookup[$fld->FldTable], 
                            $tableLookup[$fld->FldForeignTable]
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
        return view('vendor.survloop.admin.db.overview', $this->v);
    }
    
    public function editTable(Request $request, $tblName)
    {
        $this->admControlInit($request, '/dashboard/db/all');
        if (trim($tblName) == '') return $this->printOverview();
        return $this->printEditTable($tblName);
    }
    
    public function addTableFld(Request $request, $tblAbbr)
    {
        $this->admControlInit($request, '/dashboard/db/all');
        if (trim($tblAbbr) == '') return $this->printOverview();
        return $this->printEditField($tblAbbr, '');
    }
    
    public function editField(Request $request, $tblAbbr, $fldName)
    {
        if (trim($fldName) == '') return $this->addTableFld($request, $tblAbbr);
        $this->admControlInit($request, '/dashboard/db/all');
        if (trim($tblAbbr) == '') return $this->printOverview();
        return $this->printEditField($tblAbbr, $fldName);
    }
    
    public function fieldAjax(Request $request, $fldID = -3)
    {
        if (intVal($fldID) <= 0) exit;
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
                    $this->v["matrix"][] = array((($this->v["isAlt"]) ? $tbl->TblEng : $tbl->TblName));
                    $this->v["matrix"][$i][] = (($this->v["isAlt"]) ? 'Unique Primary ID' : $tbl->TblAbbr.'ID');
                    $flds = SLFields::where('FldTable', $tbl->TblID)
                        ->where('FldDatabase', $this->dbID)
                        ->orderBy('FldOrd', 'asc')
                        ->orderBy('FldEng', 'asc')
                        ->get();
                    if ($flds->isNotEmpty()) {
                        foreach ($flds as $fld) {
                            $lnk = (($fld->FldForeignTable > 0) ? $keySign . (($this->v["isExcel"]) ? '' 
                                    : '<span class="f8 tooltip" title="' . $this->getForeignTip($fld) . '">')
                                . '(' . $fld->FldForeignMin . ',' . $fld->FldForeignMax . ')' 
                                . (($this->v["isExcel"]) ? '' : '</span>') : '');
                            $this->v["matrix"][$i][] = (($this->v["isAlt"]) 
                                ? $fld->FldEng : $tbl->TblAbbr.$fld->FldName) . $lnk;
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
                $filename = 'OPC-DB-Field_Matrix-' 
                    . (($this->v["isAlt"]) ? 'English' : 'Geek')
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
        return view('vendor.survloop.admin.db.inc-getTblsFldsDropOpts', [
            "setOptions" => $GLOBALS["SL"]->getAllSetTblFldDrops($rSet)
        ]);
    }
    
    public function getSetFldVals(Request $request, $fldID = '')
    {
        $this->admControlInit($request);
        $sessData = new SurvLoopData;
        return view('vendor.survloop.admin.db.inc-getTblsFldVals', [ 
            "fldID"  => $fldID,
            "values" => $GLOBALS["SL"]->getFldResponsesByID($fldID)
        ]);
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
                if (!isset($this->v["groupTbls"][$tbl->TblGroup])) $this->v["groupTbls"][$tbl->TblGroup] = [];
                $this->v["groupTbls"][$tbl->TblGroup][] = $tbl;
            }
        }
        return true;
    }
    
    protected function loadTblForeigns()
    {
        $this->v["tblForeigns"] = [];
        $flds = SLFields::where('FldForeignTable', '>', 0)
            ->where('FldSpecType', 'NOT LIKE', 'Generic')
            ->where('FldDatabase', $this->dbID)
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                if (!isset($this->v["tblForeigns"][$fld->FldForeignTable])) {
                    $this->v["tblForeigns"][$fld->FldForeignTable] = '';
                }
                $this->v["tblForeigns"][$fld->FldForeignTable] .= ', ' . $this->printForeignKey($fld, 2, 1);
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
        $rules = SLBusRules::where('RuleDatabase', $this->dbID)->get();
        if ($rules->isNotEmpty()) {
            foreach ($rules as $rule) {
                $tblList = $GLOBALS["SL"]->mexplode(',', $rule->RuleTables);
                if (sizeof($tblList) > 0) {
                    foreach ($tblList as $i => $tbl) {
                        $tbl = intVal($tbl);
                        if (!isset($this->v["tblRules"][$tbl])) $this->v["tblRules"][$tbl] = [];
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
        $defs = SLDefinitions::where('DefSet', 'Value Ranges')
            ->where('DefDatabase', $this->dbID)
            ->orderBy('DefSubset', 'asc')
            ->orderBy('DefOrder', 'asc')
            ->orderBy('DefValue', 'asc')
            ->get();
        if ($defs->isNotEmpty()) {
            foreach ($defs as $def) {
                if (!isset($this->v["defDeets"][$def->DefSubset])) $this->v["defDeets"][$def->DefSubset] = [''];
                $this->v["defDeets"][$def->DefSubset][0] .= ';' . $def->DefValue;
                $this->v["defDeets"][$def->DefSubset][] = $def->DefValue;
            }
        }
        $cnt = 0;
        $this->v["defDeetsJS"] = '';
        if (sizeof($this->v["defDeets"]) > 0) {
            foreach ($this->v["defDeets"] as $set => $vals) {
                $this->v["defDeetsJS"] .= 'definitions['.$cnt.'] = new Array("' . htmlspecialchars($set) . '", "' 
                    . htmlspecialchars(substr($vals[0], 1)) . '");' . "\n";
                $cnt++;
            }
        }
        return true;
    }
    
    protected function getTblName($id = -3, $link = 1, $xtraTxt = '', $xtraLnk = '')
    {
        return view('vendor.survloop.admin.db.inc-getTblName', [
            "id" => $id, 
            "link" => $link, 
            "xtraTxt" => $xtraTxt, 
            "xtraLnk" => $xtraLnk 
        ]);
    }
    
    protected function getForeignTip($fld = [])
    {
        return 'Degree of Participation: ' . $fld->FldForeignMin . ' Minimum and ' 
            . $fld->FldForeignMax . ' Maximum number of ' 
            . $this->getTblName($fld->FldTable, 0) 
            . ' records which can be associated with a single record from ' 
            . $this->getTblName($fld->FldForeignTable, 0);
    }

    protected function tblQryStd()
    {
        return SLTables::where('TblDatabase', $this->dbID)
            ->orderBy('TblOrd', 'asc')
            ->orderBy('TblNumForeignKeys', 'desc')
            ->get();
    }
    
    protected function getTableFields($tbl = [])
    {
        $flds = [];
        if (isset($tbl->TblID) && intVal($tbl->TblID) > 0) {
            $flds = SLFields::where('FldTable', $tbl->TblID)
                ->orderBy('FldOrd', 'asc')
                ->orderBy('FldEng', 'asc')
                ->get();
            if (isset($tbl->TblExtend) && intVal($tbl->TblExtend) > 0) {
                $flds = $GLOBALS["SL"]->addFldRowExtends($flds, $tbl->TblExtend);
            }
        }
        return $flds;
    }
    
    protected function getTableSeedDump($tblClean = '', $eval = '')
    {
        $seedChk = [];
        if (trim($tblClean) != '' && file_exists('../app/Models/' . $tblClean . '.php')) {
            eval("\$seedChk = App\\Models\\" . $tblClean . "::" . $eval . "get();");
        }
        return $seedChk;
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
            $flds = SLFields::select('FldID', 'FldName')
                ->where('FldTable', $tbl)
                ->orderBy('FldOrd', 'asc')
                ->get();
            if ($flds->isNotEmpty()) {
                foreach ($flds as $fld) {
                    $ret .= '<option value="'.$fld->FldID.'" ' . (($presel == $fld->FldID) ? 'SELECTED' : '') 
                        . ' >'.$fld->FldName.'</option>';
                }
            }
        }
        return $ret;
    }
    
    protected function loadGenerics()
    {
        $flds = SLFields::select('FldID', 'FldEng')
            ->where('FldDatabase', $this->dbID)
            ->where('FldSpecType', 'Generic')
            ->orderBy('FldOrd', 'asc')
            ->orderBy('FldEng', 'asc')
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) $this->v["dbFldGenerics"][] = [$fld->FldID, $fld->FldEng];
        }
        return true;
    }
    
    protected function getFldGenericOpts($presel = -3)
    {
        $this->v["presel"] = $presel;
        if (sizeof($this->v["dbFldGenerics"]) == 0) $this->loadGenerics();
        return view('vendor.survloop.admin.db.inc-getFldGenericOpts', $this->v);
    }
    
    protected function printDbStats()
    {
        return '<div class="f16 pB10 pL20 mTn10">' . $GLOBALS["SL"]->dbRow->DbTables 
            . ' tables, ' . $GLOBALS["SL"]->dbRow->DbFields . ' fields</div>';
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
            $flds = SLFields::where('FldTable', $tblID)
                ->orderBy('FldOrd', 'asc')
                ->get();
        }
        $this->v["flds"] = $flds;
        $this->v["tblID"] = $tblID;
        $this->v["tblLinks"] = $tblLinks;
        $this->v["printTblFldRows"] = '';
        if ($flds->isNotEmpty()) {
            foreach ($flds as $i => $fld) {
                if (!$GLOBALS["SL"]->REQ->has('onlyKeys') || $fld->FldForeignTable > 0) {
                    $this->v["printTblFldRows"] .= $this->printBasicTblFldRow($fld, $tblID, $tblLinks);
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
        $this->v["FldValues"] = $fld->FldValues;
        if (strpos($this->v["FldValues"], 'Def::') !== false 
            || strpos($this->v["FldValues"], 'DefX::') !== false) {
            $range = str_replace('Def::', '', str_replace('DefX::', '', $this->v["FldValues"]));
            if (isset($this->v["dbDefOpts"][$range])) {
                $this->v["FldValues"] = str_replace(';', ' ; ', $this->v["dbDefOpts"][$range][0]);
            }
        }
        else $this->v["FldValues"] = str_replace(';', ' ; ', $this->v["FldValues"]);
        $this->v["fldForeignPrint"] = $this->printForeignKey($fld, $tblLinks);
        $this->v["fldGenerics"] = $this->printFldGenerics($fld, $tblLinks);
        return view('vendor.survloop.admin.db.inc-basicTblFldRow', $this->v);
    }
    
    protected function printForeignKey($fld = [], $tblLinks = 1, $whichway = 0)
    {
        if (intVal($fld->FldForeignTable) > 0 && isset($GLOBALS["SL"]->tbl[$fld->FldForeignTable])
            && isset($GLOBALS["SL"]->tbl[$fld->FldTable])) {
            if ($whichway == 0) {
                return '<a href="/dashboard/db/table/' . $GLOBALS["SL"]->tbl[$fld->FldForeignTable] 
                    . '" data-toggle="tooltip" data-placement="top" title="Degree of Participation: '
                    . $fld->FldForeign2Min . ' to ' . $fld->FldForeign2Max . ' ' 
                    . $this->getTblName($fld->FldForeignTable, 0) . ' records can be related to a single ' 
                    . $this->getTblName($fld->FldTable, 0) . ' record. ' 
                    . $fld->FldForeignMin . ' to ' . $fld->FldForeignMax . ' ' 
                    . $this->getTblName($fld->FldTable, 0) . ' records can be related to a single ' 
                    . $this->getTblName($fld->FldForeignTable, 0) . ' record." >'
                    . '<i class="fa fa-link"></i> ' . $GLOBALS["SL"]->tblEng[$fld->FldForeignTable] 
                    . '<sup>(' . $fld->FldForeign2Min . ',' . $fld->FldForeign2Max . ')-(' 
                    . $fld->FldForeignMin . ',' . $fld->FldForeignMax . ')</sup></a>';
            } else {
                return '<a href="/dashboard/db/table/' . $GLOBALS["SL"]->tbl[$fld->FldTable] 
                    . '" data-toggle="tooltip" data-placement="top" title="Degree of Participation: '
                    . $fld->FldForeignMin . ' to ' . $fld->FldForeignMax . ' ' 
                    . $this->getTblName($fld->FldTable, 0) . ' records can be related to a single ' 
                    . $this->getTblName($fld->FldForeignTable, 0) . ' record. ' 
                    . $fld->FldForeign2Min . ' to ' . $fld->FldForeign2Max . ' ' 
                    . $this->getTblName($fld->FldForeignTable, 0) . ' records can be related to a single ' 
                    . $this->getTblName($fld->FldTable, 0) . ' record." >'
                    . '<i class="fa fa-link"></i> ' . $GLOBALS["SL"]->tblEng[$fld->FldTable] 
                    . '<sup>(' . $fld->FldForeignMin . ',' . $fld->FldForeignMax . ')-(' 
                    . $fld->FldForeign2Min . ',' . $fld->FldForeign2Max . ')</sup></a>';
            }
        }
        return '';
    }
    
    protected function printFldGenerics($fld = [], $tblLinks = 1) 
    {
        $repList = '';
        if ($fld->FldSpecType == 'Generic') {
            $repList = '<br />Replica Copies: ';
            $replicas = SLFields::select('FldTable')
                ->where('FldSpecSource', $fld->FldID)
                ->where('FldSpecType', 'Replica')
                ->get();
            if ($replicas->isNotEmpty()) {
                foreach ($replicas as $rep) {
                    $repList .= ', ' . $this->getTblName($rep->FldTable, $tblLinks);
                }
            }
        }
        return $repList;
    }
    
    protected function foreignLinkCnt($preSel = '1')
    {
        $ret = '<option value="N" ' . (($preSel == 'N') ? 'SELECTED' : '') 
            . ' >N    (unlimited)</option>';
        for ($i=0; $i<100; $i++) {
            $ret .= '<option value="'.$i.'" ' 
                . (($preSel == (''.$i.'')) ? 'SELECTED' : '') 
                . ' >'.$i.'</option>';
        }
        return $ret;
        view ( 'vendor.survloop.admin.db.inc-getLinkCnt', array("preSel" => $preSel) );
    }
    
    function getFldArr($RuleFields = '') 
    {
        return SLFields::select('FldID', 'FldTable', 'FldName')
            ->whereIn('FldID', $GLOBALS["SL"]->mexplode(',', $RuleFields))
            ->orderBy('FldTable', 'asc')
            ->orderBy('FldOrd', 'asc')
            ->orderBy('FldEng', 'asc')
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
                    . $this->getTblName($fld->FldTable, 1, '', ' target="_blank"') . ':&nbsp;' 
                    . '<a href="fld.php?fldSpec=' . base64_encode($fld->FldID) 
                    . '" target="_blank">' . $fld->FldName . '</a>';
            }
        }
        return $fldTxt;
    }
    
    
}
