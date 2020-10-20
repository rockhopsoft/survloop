<?php
/**
  * AdminDatabaseInstall is the admin class responsible for building standard Survloop components.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Admin;

use DB;
use Auth;
use Storage;
use Illuminate\Http\Request;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use App\Models\SLTree;
use App\Models\SLTables;
use App\Models\SLFields;
use App\Models\SLNode;
use App\Models\SLConditions;
use RockHopSoft\Survloop\Controllers\SurvloopImportExcel;
use RockHopSoft\Survloop\Controllers\Admin\AdminDBController;

class AdminDatabaseInstall extends AdminDBController
{
    protected function tweakAdmMenu($currPage = '')
    {
        $this->v["dateStmp"] = date("Y_m_d");
        $this->v["dateStamp"] = date("Y_m_d_His");
        $this->v["zipFileMig"] = $this->v["exportDir"] . '/' 
            . $this->v["dateStmp"] . '_LaravelMigrations.zip';
        $this->v["zipFileModel"] = $this->v["exportDir"] . '/' 
            . $this->v["dateStmp"] . '_LaravelModels.zip';
        return true;
    }
    
    protected function exportMysql()
    {
        if (!isset($this->v["export"])) {
            $this->v["export"] = '';
        }
        $tbls = $this->tblQryStd();
        if ($tbls->isNotEmpty()) {
            foreach ($tbls as $i => $tbl) {
                $this->v["export"] .= $GLOBALS["SL"]->exportMysqlTbl($tbl);
            }
            $this->v["export"] .= $GLOBALS["SL"]->x["indexesEnd"]; 
        }
        if ($this->v["asPackage"]) {
            $GLOBALS["SL"]->exportMysqlSl();
            $this->v["export"] .= $GLOBALS["SL"]->x["export"];
            $GLOBALS["SL"]->x["export"] = '';
        }
        return true;
    }
    
    public function printExportPackage(Request $request)
    {
        return $this->printExport($request, true);
    }
    
    public function printExport(Request $request, $asPackage = false)
    {
        $asPack = $cacheName = '/dashboard/db/export';
        $this->admControlInit($request, $asPack);
        $this->v["asPackage"] = $GLOBALS["SL"]->x["exportAsPackage"] = $asPackage;
        if ($asPackage) {
            $asPack = '/dashboard/sl/export/laravel';
            $cacheName = '/dashboard/sl/export';
        }
        if (!$this->checkCache()) {
            $this->exportMysql();
            $this->v["content"] = view(
                'vendor.survloop.admin.db.export-mysql', 
                $this->v
            )->render();
            $this->saveCache();
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    public function printExportPackageLaravel(Request $request) 
    {
        $cacheName = '/dashboard/sl/export/laravel';
        $this->admControlInit($request, $cacheName);
        return $this->printExportLaravel($request, true);
    }

    public function printRawTable(Request $request)
    {
        $cacheName = '/dashboard/db/tbl-raw';
        $this->admControlInit($request, $cacheName);
        if (Auth::user()
            && Auth::user()->hasRole('administrator')
            && $request->has('tbl') 
            && trim($request->get('tbl')) != '') {
            $tbl = trim($request->get('tbl'));
            if (in_array(strtolower($tbl), ['user', 'users'])) {
                $tbl = '';
            }
            $this->v["tbl"] = SLTables::where('tbl_name', $tbl)
                ->first();
            if ($this->v["tbl"] && isset($this->v["tbl"]->tbl_name)) {
                $tbl = $this->v["tbl"]->tbl_name;
                $this->printRawTableSorts($request);
                $this->printRawTableQry($tbl);
                if ($this->v["rows"]->isNotEmpty()
                    && $this->v["flds"]->isNotEmpty()) {
                    $this->v["content"] = view(
                        'vendor.survloop.admin.db.raw-tbl', 
                        $this->v
                    )->render();
                    if ($request->has('ajax')) {
                        return $this->v["content"];
                    }
                    $GLOBALS["SL"]->pageAJAX = view(
                        'vendor.survloop.admin.db.raw-tbl-ajax', 
                        $this->v
                    )->render();
                    $GLOBALS["SL"]->setAdmMenuOnLoad(0);
                }
            }
        }
        return view('vendor.survloop.master', $this->v);
    }

    private function printRawTableSorts(Request $request)
    {
        $this->v["sortFld"] = 'id';
        $this->v["sortDir"] = 'desc';
        if ($request->has('sortFld')
            && trim($request->get('sortFld')) != '') {
            $this->v["sortFld"] = trim($request->get('sortFld'));
        }
        if ($request->has('sortDir')
            && trim($request->get('sortDir')) == 'asc') {
            $this->v["sortDir"] = 'asc';
        }
        return true;
    }

    private function printRawTableQry($tbl)
    {
        $sortFld = $this->v["tbl"]->tbl_abbr . $this->v["sortFld"];
        eval("\$this->v['rowTotCnt'] = " 
            . $GLOBALS["SL"]->modelPath($tbl) . "::select('" 
            . $this->v["tbl"]->tbl_abbr . "id')->count();");
        eval("\$this->v['rows'] = " . $GLOBALS["SL"]->modelPath($tbl)
            . "::orderBy('" . $sortFld . "', '" 
            . $this->v["sortDir"] . "')->limit(2000)->get();");
        $this->v["flds"] = SLFields::where('fld_database', $GLOBALS["SL"]->dbID)
            ->where('fld_table', $this->v["tbl"]->tbl_id)
            ->select('fld_name', 'fld_eng', 
                'fld_id', 'fld_ord', 'fld_type')
            ->orderBy('fld_ord', 'asc')
            ->get();
        return true;
    }
    
    public function printImport(Request $request)
    {
        $cacheName = '/dashboard/db/import';
        $this->admControlInit($request, $cacheName);
        $this->v["uploadImport"] = null;
        if ($request->has('import')) {
            if (trim($request->get('import')) == 'excel') {
                $this->uploadImportStep1($request);
            } else {
                if ($request->has('file') 
                    && trim($request->get('file')) != '') { 
                    $tblEng = '';
                    if ($request->has('tblEng')) {
                        $tblEng = trim($request->tblEng);
                    }
                    $this->v["uploadImport"] = new SurvloopImportExcel($tblEng);
                    if (trim($request->get('import')) == 'flds') {
                        $this->v["uploadImport"]->loadFile($request->get('file'));
                    } elseif (trim($request->get('import')) == 'fldNames') {
                        $this->v["uploadImport"]->loadFldNames($request);
                    } elseif (trim($request->get('import')) == 'dataRows') {
                        $this->v["uploadImport"]->loadDataRows($request);
                    }
                }
            }
        }
        $GLOBALS["SL"]->pageAJAX = view(
            'vendor.survloop.admin.db.import-ajax', 
            $this->v
        )->render();
        $this->v["content"] = view(
            'vendor.survloop.admin.db.import', 
            $this->v
        )->render();
        return view('vendor.survloop.master', $this->v);
    }
    
    private function uploadImportStep1(Request $request)
    {
        if ($GLOBALS["SL"]->REQ->hasFile('importExcel')) {
            $tblEng = '';
            if ($request->has('importTblName')) {
                $tblEng = trim($request->get('importTblName'));
            }
            $this->v["uploadImport"] = new SurvloopImportExcel($tblEng);
            $this->v["uploadImport"]->uploadToArray('importExcel');
            $redir = '?import=flds&file=' . $this->v["uploadImport"]->getFile()
                . '&tblEng=' . $request->get('importTblName');
            $this->redir($redir, true);
        }
        return true;
    }
    
    protected function prepLaravelExport()
    {
        $this->v["dumpOut"] = [
            "Models"     => '', 
            "Migrations" => '', 
            "Seeders"    => '',
            "Zip Files"  => ''
        ];
		$this->v["fileListModel"] = [];
		$this->v["migratFileUp"] 
            = $this->v["migratFileDown"] 
            = $this->v["tblClean"] 
            = '';
        if (!isset($this->v["modelFile"])) {
            $this->v["modelFile"] = "";
        }
        return true;
    }
    
    public function printExportLaravel(Request $request, $asPackage = false) 
    {
        ini_set('max_execution_time', 180);
        $this->v["asPackage"] = $asPackage;
        $GLOBALS["SL"]->x["exportAsPackage"] = $asPackage;
        $currPage = (($asPackage) ? '/dashboard/sl/export/laravel' 
            : '/dashboard/db/export');
        $this->admControlInit($request, $currPage);
        if (!$this->checkCache($currPage) || $request->has('generate')) {
            $this->printExportLaravelRefresh($request);
        }
        if ($request->has('refreshVendor')) {
            $abbr = strtolower($GLOBALS["SL"]->sysOpts["cust-abbr"]);
            $pakg = $GLOBALS["SL"]->sysOpts["cust-package"];
            $this->v["content"] = $GLOBALS["SL"]->copyDirFiles(
                '../app/Models/' . $abbr,
                '../vendor/' . $pakg . '/src/Models'
                ) . $GLOBALS["SL"]->copyDirFiles(
                    '../storage/app/database/migrations',
                    '../vendor/' . $pakg . '/src/Database'
                ) . $this->v["content"];
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    public function printExportLaravelRefresh(Request $request) 
    {
        $this->v["generate"] = 1;
        if ($request->has('generate')) {
            $this->v["generate"] = intVal($request->get('generate'));
        }
        $this->v["newMigFile"] = 'database/migrations/' 
            . $this->v["dateStmp"] . '_000000_create_' 
            . strtolower($GLOBALS["SL"]->dbRow->db_name) . '_tables.php';
        $this->v["seedFile"] = 'database/seeders/' 
            . $GLOBALS["SL"]->dbRow->db_name . 'Seeder.php';
        $this->v["tbls"] = $this->exportQryTbls();
        
        if ($this->v["generate"] == 1) {
            // Phase 1) Create migration files
            $this->runExportLaravCreateMigrate();
            
        } elseif ($this->v["generate"] == 2) {
            // Phase 2) Create seeder files for custom system's database
            $this->runExportLaravCreateSeeds($request);
            
        } elseif ($this->v["generate"] == 3) {
            // Phase 3) Create seeder files for custom system's Survloop configurations
            $this->runExportLaravCreateSeedsConfig($request);
            
        } elseif ($this->v["generate"] == 4) {
            // Phase 4) Merge all seeder files into one ready for package
            $this->runExportLaravMergeSeeds($request);
            
        } elseif ($this->v["generate"] > 4) {
            // Phase 5) Display resulting exports
            $this->runExportLaravDisplayExports();
        }

        $this->v["content"] = view(
            'vendor.survloop.admin.db.export-laravel-progress', 
            $this->v
        )->render();
        if ($this->v["generate"] > 4) {
            $this->saveCache();
        }
        return true;
    }

    protected function runExportLaravCreateMigrate()
    {
        $this->prepLaravelExport();
        $this->chkModelsFolder();
        if ($this->v["tbls"]->isNotEmpty()) {
            foreach ($this->v["tbls"] as $tbl) {
                $indexes = "";
                $this->loadTbl($tbl);
                $this->v["migratFileUp"] .= "\t" . "Schema::create('" 
                    . $GLOBALS["SL"]->dbRow->db_prefix . $tbl->tbl_name 
                    . "', function(Blueprint $"."table)\n\t\t{\n\t\t\t"
                    . "$"."table->increments('" . $tbl->tbl_abbr . "id');";
                $this->v["modelFile"] = ''; // also happens in Globals->chkTblModel($tbl)
                $flds = $GLOBALS["SL"]->getTableFields($tbl);
                if ($flds->isNotEmpty()) {
                    foreach ($flds as $fld) {
                        $fldName = trim($tbl->tbl_abbr . $fld->fld_name);
                        $this->v["modelFile"] .= "\n\t\t'" . $fldName . "', ";
                        $this->runExportLaravMigrateFld($fldName, $fld);
                    }
                }
                $this->v["migratFileUp"] .= "\n\t\t\t"
                    . "$"."table->timestamps();" . "\n\t\t" . "});" . "\n\t";
                $this->v["migratFileDown"] .= "\t" . "Schema::drop('"
                    . $GLOBALS["SL"]->dbRow->db_prefix . $tbl->tbl_name . "');"."\n\t";
                $this->saveModelFile();
            }
        }
        if (isset($this->v["migrateEnd"]) && trim($this->v["migrateEnd"]) != '') {
            $this->v["migratFileUp"] .= $this->v["migrateEnd"];
        }
        Storage::put(
            $this->v["newMigFile"], 
            view(
                'vendor.survloop.admin.db.export-laravel-gen-migration', 
                $this->v
            )->render()
        );
        $this->v["nextUrl"] = '?generate=2';
        if ($this->v["tbls"]->isNotEmpty() && isset($this->v["tbls"][0]->tbl_name)) {
            $this->v["nextUrl"] .= '&tbl=' . $this->v["tbls"][0]->tbl_name;
        }
        return true;
    }
    
    protected function runExportLaravMigrateFld($fldName = '', $fld = null)
    {
        $this->v["migratFileUp"] .=  "\n\t\t\t"."$"."table->";
        if (strpos($fld->fld_values, 'Def::') !== false) {
            $this->v["migratFileUp"] .=  "integer('" . $fldName . "')->unsigned()";
        } elseif ($fld->fld_type == 'INT') {
            if ($fld->fld_values == '0;1') {
                $this->v["migratFileUp"] .=  "boolean('" . $fldName . "')";
            } else {
                $this->v["migratFileUp"] .=  "integer('" . $fldName . "')";
            }
            if (intVal($fld->fld_foreign_table) > 0 && intVal($fld->fld_default) >= 0) {
                $this->v["migratFileUp"] .=  "->unsigned()";
            }
        } elseif ($fld->fld_type == 'DOUBLE') {
            $this->v["migratFileUp"] .=  "double('" . $fldName . "')";
        } elseif ($fld->fld_type == 'VARCHAR') {
            if ($fld->fld_data_length == 1 
                || $fld->fld_values == 'Y;N' || $fld->fld_values == 'M;F' 
                || $fld->fld_values == 'Y;N;?' || $fld->fld_values == 'M;F;?') {
                $this->v["migratFileUp"] .=  "char('" . $fldName . "', 1)";
            } else {
                $this->v["migratFileUp"] .=  "string('" . $fldName . "'" 
                    . (($fld->fld_data_length > 0) ? ", " . $fld->fld_data_length : "") 
                    . ")";
            }
        } elseif ($fld->fld_type == 'TEXT') {
            $this->v["migratFileUp"] .=  "longText('" . $fldName . "')";
        } elseif ($fld->fld_type == 'DATE') {
            $this->v["migratFileUp"] .=  "date('" . $fldName . "')";
        } elseif ($fld->fld_type == 'DATETIME') {
            $this->v["migratFileUp"] .=  "dateTime('" . $fldName . "')";
        }
        if (trim($fld->fld_default) != '') {
            $this->v["migratFileUp"] .=  "->default(";
            if ($fld->fld_default == 'NULL') {
                $this->v["migratFileUp"] .= "NULL";
            } else {
                if ($fld->fld_values == '0;1') {
                    if (intVal($fld->fld_default) == 1) {
                        $this->v["migratFileUp"] .= "1";
                    } else {
                        $this->v["migratFileUp"] .= "0";
                    }
                } else {
                    $this->v["migratFileUp"] .= "'" . $fld->fld_default . "'";
                }
            }
            $this->v["migratFileUp"] .= ")";
        }
        $this->v["migratFileUp"] .=  "->nullable();";
        if ($fld->fld_is_index == 1) {
            $this->v["migratFileUp"] .= "\n\t\t\t$"."table->index('" . $fldName . "');";
        }
        /* // This is throwing errors
        if (intVal($fld->fld_foreign_table) > 0) {
            list($forTbl, $forID) = $GLOBALS["SL"]->chkForeignKey($fld->fld_foreign_table);
            $this->v["migrateEnd"] .= "\t"."Schema::table('" 
                . $GLOBALS["SL"]->dbRow->db_prefix . $tbl->tbl_name 
                . "', function($"."table) { $"."table->foreign('" . $fldName 
                . "')->references('" . $forID . "')->on('" . $forTbl . "'); });\n";
        }
        */
        return true;
    }

    protected function runExportLaravCreateSeeds(Request $request)
    {
        $found = -1;
        $seedCnt = $finishedTable = 1;
        $done = (($request->has('tbls') && trim($request->get('tbls')) != '') 
            ? trim($request->get('tbls')) : ',');
        $page = (($request->has('page')) ? intVal($request->get('page')) : 0);
        $limit = 1000;
        if ($this->v["tbls"]->isNotEmpty()) {
            if (is_array($this->custReport)) {
                $this->loadCustLoop($GLOBALS["SL"]->REQ, $this->treeID);
            }
            foreach ($this->v["tbls"] as $i => $tbl) {
                $this->v["tblClean"] = $GLOBALS["SL"]->strFullTblModel($tbl->tbl_name);
                if (in_array($tbl->tbl_name, $this->custReport->tblsInPackage())  
                    && strpos($done, ',' . $tbl->tbl_name . ',') === false && $found < 0) {
                    $this->loadTbl($tbl);
                    $tblSffx = '-' . $tbl->tbl_name . '.php';
                    $this->v["seedFile"] = str_replace('.php', $tblSffx, $this->v["seedFile"]);
                    /*
                    if ($GLOBALS["SL"]->chkTableSeedLimits($tblClean)) {
                        $tblSeeds = '';
                        list($seedCnt, $seedChk) = $GLOBALS["SL"]->getTableSeedDumpLimit(
                            $tblClean, 
                            '', 
                            $limit, 
                            $page
                        );
                        if ($seedChk->isNotEmpty()) {
                            foreach ($seedChk as $seed) {
                                $tblSeeds .= $this->printSeedTblRow($seed);
                            }
                        }
                        $this->v["seedFile"] = str_replace(
                            '.php', 
                            '-' . $page . '.php', 
                            $this->v["seedFile"]
                        );
                        //Storage::delete('file.jpg');
                        Storage::put($this->v["seedFile"], $tblSeeds);
                        if ($seedChk->count() < $limit) {
                            $done .= $tbl->tbl_name . ',';
                        } else {
                            $page++;
                            $finishedTable = 0;
                        }
                    } else { // dump whole table at once
                        */
                        $tblSeeds = $this->printSeedTbl($GLOBALS["SL"]->loadSlSeedEval($tbl));
                        Storage::put($this->v["seedFile"], $tblSeeds);
                        $done .= $tbl->tbl_name . ',';
                    //}
                    $found = $i;
                }
            }
        }
        $this->v["nextUrl"] = '?generate=';
        if ($found >= 0) {
            $this->v["nextUrl"] .= '2&tbls=' . $done;
            if ($finishedTable == 0) {
                $this->v["nextUrl"] .= '&page=' . $page;
            }
        } else {
            if ($this->v["asPackage"]) {
                $this->v["nextUrl"] .= '3';
            } else { 
                $this->v["nextUrl"] .= '4';
            }
        }
        return true;
    }

    protected function runExportLaravCreateSeedsConfig(Request $request)
    {
        $found = -1;
        $seedCnt = $finishedTable = 1;
        $done = (($request->has('tbls') && trim($request->get('tbls')) != '') 
            ? trim($request->get('tbls')) : ',');
        $page = (($request->has('page')) ? intVal($request->get('page')) : 0);
        $limit = 1000;
        //if ($this->v["asPackage"]) {
            $prevDb = $GLOBALS["SL"]->dbID;
            $prevTree = $GLOBALS["SL"]->treeID;
            $this->initLoader();
            $this->loader->syncDataTrees($GLOBALS["SL"]->REQ, 3, 3);
            $this->v["tbls"] = $GLOBALS["SL"]->tblQrySlExports();
            if ($this->v["tbls"]->isNotEmpty()) {
                foreach ($this->v["tbls"] as $i => $tbl) {
                    $this->v["tblClean"] = 'SL' . $GLOBALS["SL"]->strTblToModel($tbl->tbl_name);
                    if ($found < 0 && strpos($done, ',' . $tbl->tbl_name . ',') === false) {
                        $this->loadTbl($tbl);
                        $tblSffx = '-' . $tbl->tbl_name . '.php';
                        $this->v["seedFile"] = str_replace('.php', $tblSffx, $this->v["seedFile"]);
                        $tblSeeds = $this->printSeedTbl($GLOBALS["SL"]->loadSlSeedEval($tbl, $prevDb));
                        Storage::put($this->v["seedFile"], $tblSeeds);
                        $done .= $tbl->tbl_name . ',';
                        $found = $i;
                    }
                }
            }
            $this->loader->syncDataTrees($GLOBALS["SL"]->REQ, $prevDb, $prevTree);
        //}
        $this->v["nextUrl"] = '?generate=';
        if ($found >= 0) {
            $this->v["nextUrl"] .= '3&tbls=' . $done;
            if ($finishedTable == 0) {
                $this->v["nextUrl"] .= '&page=' . $page;
            }
        } else {
            $this->v["nextUrl"] .= '4';
        }
        return true;
    }

    protected function runExportLaravMergeSeeds(Request $request)
    {
        Storage::put(
            $this->v["seedFile"], 
            view(
                'vendor.survloop.admin.db.export-laravel-gen-seeder', 
                [ "wholeSeed" => false ]
            )->render()
        );
        $tbls = $this->exportQryTbls();
        if (is_array($this->custReport)) {
            $this->loadCustLoop($request, $this->treeID);
        }
        foreach ($tbls as $i => $tbl) {
            if (in_array($tbl->tbl_name, $this->custReport->tblsInPackage())) {
                $tblSffx = '-' . $tbl->tbl_name . '.php';
                $tblFilename = str_replace('.php', $tblSffx, $this->v["seedFile"]);
                if (Storage::exists($tblFilename)) {
                    Storage::append($this->v["seedFile"], Storage::get($tblFilename));
                }
            }
        }
        $tbls = $GLOBALS["SL"]->tblQrySlExports();
        if ($tbls->isNotEmpty()) {
            foreach ($tbls as $i => $tbl) {
                $tblSffx = '-' . $tbl->tbl_name . '.php';
                $tblFilename = str_replace('.php', $tblSffx, $this->v["seedFile"]);
                if (Storage::exists($tblFilename)) {
                    Storage::append($this->v["seedFile"], Storage::get($tblFilename));
                }
            }
        }
        Storage::append($this->v["seedFile"], ' } }');
        $this->v["nextUrl"] = '?generate=5';
        return true;
    }

    protected function runExportLaravDisplayExports()
    {   
        /* NOT MVP!
        $zip = new ZipArchive();
        if (file_exists($this->v["zipFileMig"])) unlink($this->v["zipFileMig"]);
        if ($zip->open($this->v["zipFileMig"], ZipArchive::CREATE)!==TRUE) {
            exit("cannot open ".$this->v["zipFileMig"]."\n");
        }
        foreach ($this->v["fileListMig"] as $file) $zip->addFile($this->v["exportDir"]."/".$file, $file);
        foreach ($this->v["fileListModel"] as $file) $zip->addFile($this->v["exportDir"]."/".$file, $file);
        $zip->addFile($this->v["exportDir"]."/Model-Namespaces.php", "Model-Namespaces.php");
        $this->v["dumpOut"]["Zip Files"] .= "\n\n\n\n numfiles: " . $zip->numFiles 
            . "\n status:" . $zip->status . "\n";
        $zip->close();
        //$filesystem = new Filesystem(new ZipArchiveAdapter(__DIR__ . $this->v["exportDir"] 
            . "/Survloop2Laravel-Export-" . date("Y-m-d") . ".zip"));
        */
        
        $this->v["dumpOut"]["Migrations"] = $this->v["newMigFile"];
        $this->v["dumpOut"]["Seeders"] = $this->v["seedFile"];
        $this->v["nextUrl"] = '/dashboard/db/export/laravel?done=1';
        if ($this->v["asPackage"]) {
            $this->v["nextUrl"] = '/dashboard/sl/export/laravel?done=1';
        }
        return true;
    }
    
    protected function refreshTableModel(Request $request, $tbl = '')
    {
        $this->admControlInit($request, '/dashboard/db/export/laravel/table-model');
        $this->prepLaravelExport();
        $tbl = SLTables::where('tbl_name', $tbl)
            ->first();
        $this->loadTbl($tbl);
        $flds = $GLOBALS["SL"]->getTableFields($tbl);
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                $this->v["modelFile"] .= "\n\t\t'" 
                    . trim($tbl->tbl_abbr . $fld->fld_name) . "', ";
            }
        }
        $this->saveModelFile();
        return ':)' . (($request->has('redir64')) 
            ? '<script type="text/javascript"> setTimeout("window.location=\'' 
                . base64_decode($request->get('redir64')) . '\'", 100); </script>' 
            : '');
    }
    
    protected function saveModelFile()
    {
        $newModelFilename = '../app/Models/' . $this->v["tblClean"] . '.php';
        $this->v["fileListModel"][] = $newModelFilename;
        $fullFileOut = view(
            'vendor.survloop.admin.db.export-laravel-gen-model' , 
            $this->v
        );
        $this->v["dumpOut"]["Models"] .= $fullFileOut;
        if (file_exists($newModelFilename)) {
            $oldFile = file_get_contents($newModelFilename);
            $endStr = '// END Survloop auto-generated portion of Model';
            $endPos = strpos($oldFile, $endStr);
            if ($endPos > 0 && ($endPos+strLen($endStr)+2) >= strLen($oldFile)) {
                $append = substr($oldFile, ($endPos+strLen($endStr)+2));
                $fullFileOut .= "\n\n" . $append;
            }
        }
        file_put_contents($newModelFilename, $fullFileOut);
        $abbr = strtolower($GLOBALS["SL"]->sysOpts["cust-abbr"]);
        try {
            copy(
                $newModelFilename, 
                str_replace(
                    '/app/models/' . $abbr . '/',
                    '/app/Models/', $newModelFilename
                )
            );
        } catch (Exception $e) {
            //echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        return true;
    }
    
    protected function printSeedTbl($eval = '', $limit = 10000, $start = 0)
    {
    	$ret = '';
//if ($tbl->tbl_name == 'Definitions') { echo 'eval: ' . $eval . '<br />'; exit; }
//echo '<pre>'; print_r($eval); echo '</pre>';
        $seedChk = $GLOBALS["SL"]->getTableSeedDump(
            $this->v["tblClean"], 
            $eval, 
            $limit, 
            $start
        );
//echo '<pre>'; print_r($seedChk); echo '</pre>'; exit;
        if ($seedChk->isNotEmpty()) {
            foreach ($seedChk as $seed) {
                $ret .= $this->printSeedTblRow($seed);
            }
        }
        return $ret;
    }
    
    protected function printSeedTblRow($seed)
    {
        $ret = '';
        $fldData = "\n\t\t\t'" . $this->v["tbl"]->tbl_abbr . "id' => " . $seed->getKey();
        $flds = $GLOBALS["SL"]->getTableFields($this->v["tbl"]);
        if ($flds->isNotEmpty()) {
            foreach ($flds as $i => $fld) {
                $fldName = trim($this->v["tbl"]->tbl_abbr . $fld->fld_name);
                if (isset($seed->{ $fldName }) 
                    && trim($seed->{ $fldName }) != trim($fld->fld_default)) {
                    $fldData .= ",\n\t\t\t'" . $fldName . "' => '" 
                        . str_replace("'", "\'", $seed->{ $fldName }) . "'";
                }
            }
        }
        if (trim($fldData) != '') {
            $ret .= "\tDB::table('" 
                . (($this->v["tbl"]->tbl_database == 3) 
                    ? 'sl_' : $GLOBALS["SL"]->dbRow->db_prefix)
                . $this->v["tbl"]->tbl_name . "')->insert([" 
                . $fldData . "\n\t\t"."]);"."\n\t";
        }
        return $ret;
    }
    
    protected function exportQryTbls()
    {
        return SLTables::where('tbl_database', $this->dbID)
            ->where('tbl_name', 'NOT LIKE', 'Users')
            ->where('tbl_name', 'NOT LIKE', 'users')
            ->orderBy('tbl_ord')
            ->get();
    }
    
    protected function loadTbl($tbl = [])
    {
        $this->v["tbl"] = $tbl;
        $this->v["tblName"] = $GLOBALS["SL"]->dbRow->db_prefix . $tbl->tbl_name;
        $this->v["tblClean"] = $GLOBALS["SL"]->strFullTblModel($tbl->tbl_name);
        return true;
    }
    
    public function exportDump(Request $request)
    {
        $ret = '';
        $this->admControlInit($request, '/dashboard/db/export');
        if ($request->has('which')) {
            if (in_array($request->get('which'), ['migrations', 'seeders'])) {
                $ret .= Storage::get($request->get('url'));
            } elseif ($request->get('which') == 'models') {
                $tbls = $this->exportQryTbls();
                if ($tbls->isNotEmpty()) {
                    foreach ($tbls as $tbl) {
                        $this->loadTbl($tbl);
                        $newModelFilename = '../vendor/rockhopsoft/survloop-models/' 
                            . strtolower($GLOBALS["SL"]->sysOpts["cust-abbr"])
                            . '/' . $this->v["tblClean"] . '.php';
                        if (file_exists($newModelFilename)) {
                            $ret .= file_get_contents($newModelFilename) . "\n\n";
                        }
                    }
                }
            }
        }
        return $ret;
    }
    
    
    public function autoInstallDatabase(Request $request) 
    {
        $this->admControlInit($request, '/dashboard/db/install');
        
        $this->v["oldTables"] = [];
        
        $this->v["DbPrefix"] = $GLOBALS["SL"]->dbRow->db_prefix;
        $this->v["tbls"] = $this->tblQryStd();
        
        $this->v["log"] = '';
        /* 
        if ($this->v["dbAllowEdits"] && $GLOBALS["SL"]->REQ->has('dbConfirm') 
            && $GLOBALS["SL"]->REQ->input('dbConfirm') == 'install'
            && $GLOBALS["SL"]->REQ->has('createTable') && is_array($GLOBALS["SL"]->REQ->input('createTable'))
            && sizeof($GLOBALS["SL"]->REQ->input('createTable')) > 0) {
            $transferData = [];
            if ($GLOBALS["SL"]->REQ->has('copyData') && is_array($GLOBALS["SL"]->REQ->input('copyData')) 
                && sizeof($GLOBALS["SL"]->REQ->input('copyData')) > 0) {
                foreach ($GLOBALS["SL"]->REQ->input('copyData') as $copyTbl) {
                    if (file_exists('../vendor/rockhopsoft/survloop-models/' 
                        . strtolower($GLOBALS["SL"]->tblModels[$GLOBALS["SL"]->tbl[$copyTbl]]))) {
                        eval("\$transferData[\$copyTbl] = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->tbl[$copyTbl])
                            . "::get();");
                        $this->v["log"] .= '<br />copying table data!.. ' . $GLOBALS["SL"]->tbl[$copyTbl];
                    }
                }
            }
        
            foreach ($GLOBALS["SL"]->REQ->input('createTable') as $createTbl) {
                $tbl = SLTables::find($createTbl);
                if (!in_array(strtolower($GLOBALS["SL"]->tbl[$createTbl]), ['users'])) {
                    DB::statement('DROP TABLE IF EXISTS `' . $GLOBALS["SL"]->dbRow->db_prefix 
                        . $GLOBALS["SL"]->tbl[$createTbl] . '`');
                    $createQry = $GLOBALS["SL"]->exportMysqlTbl($tbl, true);
                    echo $createQry . '<br />';
                    DB::statement($createQry);
                    $this->v["log"] .= '<br />creating table!.. ' . $GLOBALS["SL"]->tbl[$createTbl]; // $createQry;
                }
            }
            
            if ($GLOBALS["SL"]->REQ->has('copyData') && is_array($GLOBALS["SL"]->REQ->input('copyData')) 
                && sizeof($GLOBALS["SL"]->REQ->input('copyData')) > 0) {
                foreach ($GLOBALS["SL"]->REQ->input('copyData') as $copyTbl) {
                    if (!in_array($GLOBALS["SL"]->tbl[$createTbl], ['users', 'Users'])) {
                        $this->v["log"] .= '<br />pasting table data!.. ' . $GLOBALS["SL"]->tbl[$copyTbl];
                        if (isset($transferData[$copyTbl]) && is_array($transferData[$copyTbl]) 
                            && sizeof($transferData[$copyTbl]) > 0) {
                            $newFlds = [];
                            $flds = SLFields::where('fld_table', $copyTbl)
                                ->where('fld_database', $this->dbID)
                                ->get();
                            if ($flds->isNotEmpty()) {
                                $tblAbbr = $GLOBALS["SL"]->tblAbbr[$GLOBALS['SL']->tbl[$copyTbl]];
                                foreach ($flds as $fld) $newFlds[] = $fld->fld_name;
                                foreach ($transferData[$copyTbl] as $oldRec) {
                                    eval("\$newRec = new " 
                                        . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->tbl[$copyTbl]) . ";");
                                    $newRec->{$tblAbbr.'ID'} = $oldRec->{$tblAbbr.'ID'};
                                    $newRec->created_at = $oldRec->created_at;
                                    $newRec->updated_at = $oldRec->updated_at;
                                    foreach ($newFlds as $fldName) {
                                        if (isset($oldRec->{$tblAbbr.$fldName})) {
                                            $newRec->{$tblAbbr.$fldName} = $oldRec->{$tblAbbr.$fldName};
                                        }
                                    }
                                    $newRec->save();
                                    $this->v["log"] .= '<br />transferring ' . $tblAbbr 
                                        . ' record: ' . $newRec->{$tblAbbr.'ID'};
                                }
                            }
                        }
                    }
                }
            }
        }
        */
        return view('vendor.survloop.admin.db.install', $this->v);
    }
    
    protected function chkModelsFolder()
    {
        if (!file_exists('../app/Models')) {
            mkdir('../app/Models');
        }
        return true;
    }
    
    
}
