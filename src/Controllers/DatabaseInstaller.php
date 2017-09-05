<?php
namespace SurvLoop\Controllers;

use DB;
use Storage;
use Illuminate\Http\Request;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

use App\Models\SLTree;
use App\Models\SLTables;
use App\Models\SLFields;

use SurvLoop\Controllers\AdminDBController;

class DatabaseInstaller extends AdminDBController
{
    
    protected function tweakAdmMenu($currPage = '')
    {
        $this->v["dateStamp"] = date("Y_m_d_His");
        $this->v["zipFileMig"] = $this->v["exportDir"] . '/' . $this->v["dateStamp"] . '_LaravelMigrations.zip';
        $this->v["zipFileModel"] = $this->v["exportDir"] . '/' . $this->v["dateStamp"] . '_LaravelModels.zip';
        return true;
    }
    
    protected function exportMysqlTbl($tbl, $installHereNow = false)
    {
        if (!isset($this->v["export"])) $this->v["export"] = $this->v["indexesEnd"] = '';
        if (strtolower($tbl->TblEng) == 'Users') return "";
        $tblQuery = $this->exportMysqlTblCoreStart($tbl);
        $indexes = "";
        $flds = SLFields::where('FldTable', $tbl->TblID)
            ->where('FldDatabase', $this->dbID)
            ->orderBy('FldOrd', 'asc')
            ->orderBy('FldEng', 'asc')
            ->get();
        if ($flds && sizeof($flds)) {
            foreach ($flds as $fld) {
                $tblQuery .= "  `" . $tbl->TblAbbr . $fld->FldName . "` ";
                if ($fld->FldType == 'INT') {
                    if (intVal($fld->FldForeignTable) > 0 
                        && strtolower($GLOBALS["SL"]->tbl[$fld->FldForeignTable]) == 'users') {
                        $tblQuery .= "BIGINT(20) unsigned ";
                    } else {
                        $tblQuery .= "INT(" . (($fld->FldDataLength > 0) ? $fld->FldDataLength : 11) . ") ";
                    }
                } elseif ($fld->FldType == 'DOUBLE') {
                    $tblQuery .= "DOUBLE ";
                } elseif ($fld->FldType == 'VARCHAR') {
                    if ($fld->FldValues == 'Y;N' || $fld->FldValues == 'M;F') {
                        $tblQuery .= "VARCHAR(1) ";
                    } else {
                        $tblQuery .= "VARCHAR(" . (($fld->FldDataLength > 0) 
                            ? $fld->FldDataLength : 255) . ") ";
                    }
                } elseif ($fld->FldType == 'TEXT') {
                    $tblQuery .= "TEXT ";
                } elseif ($fld->FldType == 'DATE') {
                    $tblQuery .= "DATE ";
                } elseif ($fld->FldType == 'DATETIME') {
                    $tblQuery .= "DATETIME ";
                }
                if (($fld->FldNullSupport && intVal($fld->FldNullSupport) == 1)
                    || ($fld->FldDefault && trim($fld->FldDefault) == 'NULL')) {
                    $tblQuery .= "NULL ";
                }
                if ($fld->FldDefault && trim($fld->FldDefault) != '') {
                    if (in_array($fld->FldDefault, ['NULL', 'NOW()'])) $tblQuery .= "DEFAULT " . $fld->FldDefault . " ";
                    else $tblQuery .= "DEFAULT '" . $fld->FldDefault . "' ";
                }
                $tblQuery .= ", \n";
                if ($fld->FldIsIndex && intVal($fld->FldIsIndex) == 1) {
                    $indexes .= "  , KEY `" . $tbl->TblAbbr . $fld->FldName . "` "
                        . "(`" . $tbl->TblAbbr . $fld->FldName . "`) \n";
                }
                if (intVal($fld->FldForeignTable) > 0) {
                    list($forTbl, $forID) = $this->chkForeignKey($fld->FldForeignTable);
                    $this->v["indexesEnd"] .= "ALTER TABLE `" 
                        . $GLOBALS["SL"]->dbRow->DbPrefix . $tbl->TblName 
                        . "` ADD FOREIGN KEY (`" . $tbl->TblAbbr . $fld->FldName . "`) "
                        . "REFERENCES `" . $forTbl . "` (`" . $forID . "`); \n";
                }
            }
            $tblQuery .= "  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP , \n"
                . "  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP , \n"
                . "  PRIMARY KEY (`" . $tbl->TblAbbr . "ID`) \n " 
                . $indexes 
                . " ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; \n\n";
        }
        return $tblQuery;
    }
    
    protected function exportMysql()
    {
        $tbls = $this->tblQryStd();
        if ($tbls && sizeof($tbls) > 0) {
            foreach ($tbls as $i => $tbl) {
                $this->v["export"] .= $this->exportMysqlTbl($tbl);
            }
            $this->v["export"] .= $this->v["indexesEnd"]; 
        }
        return true;
    }
    
    public function export(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/all');
        if (!$this->checkCache('/dashboard/db/export')) {
            $this->exportMysql();
            $this->v["content"] = view('vendor.survloop.admin.db.export-mysql', $this->v)->render();
            $this->saveCache();
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    public function printExportLaravel(Request $request) 
    {
        ini_set('max_execution_time', 180);
        $this->admControlInit($request, '/dashboard/db/all');
        if (!$this->checkCache('/dashboard/db/export/laravel') && !$request->has('refresh')) {
            $this->chkModelsFolder();
            $this->v["fileListModel"] = [];
            $this->v["migrationFileUp"] = $this->v["migrationFileDown"] = '';
            $this->v["dumpOut"] = [
                "Models"     => '', 
                "Migrations" => '', 
                "Seeders"    => '', 
                "Zip Files"  => ''
            ];
            
            $modelPath = "App\\Models\\";
            /* if ($GLOBALS["SL"]->dbRow->dbName != 'SurvLoop') {
                $modelPath = "App\\Models\\" 
                    . $GLOBALS["SL"]->sysOpts["cust-abbr"] . "\\";
            } */
            
            $tbls = SLTables::where('TblDatabase', $this->dbID)
                ->where('TblName', 'NOT LIKE', 'Users')
                ->where('TblName', 'NOT LIKE', 'users')
                ->where('TblName', 'NOT LIKE', 'Zips')
                ->orderBy('TblOrd')
                ->get();
            if ($tbls && sizeof($tbls) > 0) {
                foreach ($tbls as $tbl) {
                    $indexes = "";
                    $this->v["tbl"] = $tbl;
                    $this->v["tblName"] = $GLOBALS["SL"]->dbRow->DbPrefix . $tbl->TblName;
                    $this->v["tblClean"] = str_replace('_', '', $this->v["tblName"]);
                    $this->v["migrationFileUp"] .= "\t"."Schema::create('" . $GLOBALS["SL"]->dbRow->DbPrefix 
                        . $tbl->TblName . "', function(Blueprint $"."table)\n\t\t{\n\t\t\t"
                        ."$"."table->increments('" . $tbl->TblAbbr . "ID');";
                    $this->v["modelFile"] = ''; // also happens in DatabaseLookups->chkTblModel($tbl)
                    $flds = SLFields::where('FldTable', $tbl->TblID)
                        ->where('FldDatabase', $this->dbID)
                        ->orderBy('FldOrd', 'asc')
                        ->orderBy('FldEng', 'asc')
                        ->get();
                    if ($flds && sizeof($flds) > 0) {
                        foreach ($flds as $fld) {
                            $fldName = trim($tbl->TblAbbr . $fld->FldName);
                            $this->v["modelFile"] .= "\n\t\t'" . $fldName . "', ";
                            $this->v["migrationFileUp"] .=  "\n\t\t\t"."$"."table->";
                            if (strpos($fld->FldValues, 'Def::') !== false) {
                                $this->v["migrationFileUp"] .=  "integer('" . $fldName . "')->unsigned()";
                            } elseif ($fld->FldType == 'INT') {
                                if ($fld->FldValues == '0;1') {
                                    $this->v["migrationFileUp"] .=  "boolean('" . $fldName . "')";
                                } else {
                                    $this->v["migrationFileUp"] .=  "integer('" . $fldName . "')";
                                }
                                if (intVal($fld->FldForeignTable) > 0 && intVal($fld->FldDefault) >= 0) {
                                    $this->v["migrationFileUp"] .=  "->unsigned()";
                                }
                            } elseif ($fld->FldType == 'DOUBLE') {
                                $this->v["migrationFileUp"] .=  "double('" . $fldName . "')";
                            } elseif ($fld->FldType == 'VARCHAR') {
                                if ($fld->FldDataLength == 1 || $fld->FldValues == 'Y;N' || $fld->FldValues == 'M;F' 
                                    || $fld->FldValues == 'Y;N;?' || $fld->FldValues == 'M;F;?') {
                                    $this->v["migrationFileUp"] .=  "char('" . $fldName . "', 1)";
                                } else {
                                    $this->v["migrationFileUp"] .=  "string('" . $fldName . "'" 
                                        . (($fld->FldDataLength > 0) ? ", ".$fld->FldDataLength : "") . ")";
                                }
                            } elseif ($fld->FldType == 'TEXT') {
                                $this->v["migrationFileUp"] .=  "longText('" . $fldName . "')";
                            } elseif ($fld->FldType == 'DATE') {
                                $this->v["migrationFileUp"] .=  "date('" . $fldName . "')";
                            } elseif ($fld->FldType == 'DATETIME') {
                                $this->v["migrationFileUp"] .=  "dateTime('" . $fldName . "')";
                            }
                            if (trim($fld->FldDefault) != '') {
                                $this->v["migrationFileUp"] .=  "->default(" 
                                    . (($fld->FldDefault == 'NULL') ? "NULL" : "'".$fld->FldDefault."'") . ")";
                            }
                            $this->v["migrationFileUp"] .=  "->nullable();";
                            if ($fld->FldIsIndex == 1) {
                                $this->v["migrationFileUp"] .= "\n\t\t"."$"."table->index('" . $fldName . "');";
                            }
                            if (intVal($fld->FldForeignTable) > 0) {
                                list($forTbl, $forID) = $this->chkForeignKey($fld->FldForeignTable);
                                $this->v["migrationFileUp"] .= "\n\t\t\t"
                                    . "$"."table->foreign('" . $fldName . "')"
                                    . "->references('" . $forID . "')->on('" . $forTbl . "');";
                            }
                        }
                    }
                    $this->v["migrationFileUp"] .= "\n\t\t\t"."$"."table->timestamps();"."\n\t\t"
                        ."});"."\n\t";
                    $this->v["migrationFileDown"] .= "\t"."Schema::drop('" 
                        . $GLOBALS["SL"]->dbRow->DbPrefix . $tbl->TblName . "');"."\n\t";
                    
                    $newModelFilename = '../app/Models/' . $GLOBALS["SL"]->sysOpts["cust-abbr"] 
                        . '/' . $this->v["tblClean"] . '.php';
                    $this->v["fileListModel"][] = $newModelFilename;
                    $fullFileOut = view('vendor.survloop.admin.db.export-laravel-gen-model' , $this->v);
                    $this->v["dumpOut"]["Models"] .= $fullFileOut;
                    if (file_exists($newModelFilename)) {
                        $oldFile = file_get_contents($newModelFilename);
                        $endStr = '// END SurvLoop auto-generated portion of Model';
                        $endPos = strpos($oldFile, $endStr);
                        if ($endPos > 0 && ($endPos+strLen($endStr)+2) >= strLen($oldFile)) {
                            $append = substr($oldFile, ($endPos+strLen($endStr)+2));
                            $fullFileOut .= "\n\n" . $append;
                        }
                    }
                    file_put_contents($newModelFilename, $fullFileOut);
                    try {
                        copy($newModelFilename, str_replace('/Models/' . $GLOBALS["SL"]->sysOpts["cust-abbr"] . '/', 
                            '/Models/', $newModelFilename));
                    } catch (Exception $e) {
                        //echo 'Caught exception: ',  $e->getMessage(), "\n";
                    }
                    
                    if (file_exists('../app/Models/' . $this->v["tblClean"] . '.php')) {
                        eval("\$seedChk = " . $modelPath . $this->v["tblClean"] . "::get();");
                        if ($seedChk && sizeof($seedChk) > 0) {
                            foreach ($seedChk as $seed) {
                                $fldData = "\n\t\t\t'" . $tbl->TblAbbr . "ID' => " . $seed->getKey();
                                if ($flds && sizeof($flds) > 0) {
                                    foreach ($flds as $i => $fld) {
                                        $fldName = trim($tbl->TblAbbr . $fld->FldName);
                                        if (isset($seed->{$fldName}) 
                                            && trim($seed->{$fldName}) != trim($fld->FldDefault)) {
                                            $fldData .= ",\n\t\t\t'" . $fldName . "' => '" 
                                                . str_replace("'", "\'", $seed->{$fldName}) . "'";
                                        }
                                    }
                                }
                                if (trim($fldData) != '') {
                                    $this->v["dumpOut"]["Seeders"] .= "\tDB::table('" 
                                        . $GLOBALS["SL"]->dbRow->DbPrefix . $tbl->TblName 
                                        . "')->insert([" . $fldData . "\n\t\t"."]);"."\n\t";
                                }
                            }
                        }
                    }
                }
            }
            
            
            $newMigFilename = '../database/migrations/'
                . $this->v["dateStamp"] . '_' . $GLOBALS["SL"]->dbRow->DbPrefix 
                . 'create_tables.php';
            $fullFileOut = view('vendor.survloop.admin.db.export-laravel-gen-migration' , $this->v);
            file_put_contents($newMigFilename, $fullFileOut);
            $this->v["dumpOut"]["Migrations"] .= $fullFileOut;

            $newSeedFilename = '../database/seeds/'
                . str_replace('_', '', $GLOBALS["SL"]->dbRow->DbPrefix) 
                . 'Seeder.php';
            $fullFileOut = view('vendor.survloop.admin.db.export-laravel-gen-seeder' , $this->v);
            file_put_contents($newSeedFilename, $fullFileOut);
            
            /* NOT MVP!
            $zip = new ZipArchive();
            if (file_exists($this->v["zipFileMig"])) unlink($this->v["zipFileMig"]);
            if ($zip->open($this->v["zipFileMig"], ZipArchive::CREATE)!==TRUE) { exit("cannot open ".$this->v["zipFileMig"]."\n"); }
            foreach ($this->v["fileListMig"] as $file) $zip->addFile($this->v["exportDir"]."/".$file, $file);
            foreach ($this->v["fileListModel"] as $file) $zip->addFile($this->v["exportDir"]."/".$file, $file);
            $zip->addFile($this->v["exportDir"]."/Model-Namespaces.php", "Model-Namespaces.php");
            $this->v["dumpOut"]["Zip Files"] .= "\n\n\n\n numfiles: " . $zip->numFiles . "\n status:" . $zip->status . "\n";
            $zip->close();
            //$filesystem = new Filesystem(new ZipArchiveAdapter(__DIR__.$this->v["exportDir"]."/SurvLoop2Laravel-Export-".date("Y-m-d").".zip"));
            */
            
            $this->v["content"] = view('vendor.survloop.admin.db.export-laravel', $this->v)->render();
            $this->saveCache();
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    public function autoInstallDatabase(Request $request) 
    {
        $this->admControlInit($request, '/dashboard/db/all');
        
        $this->v["oldTables"] = array();
        
        $this->v["DbPrefix"] = $GLOBALS["SL"]->dbRow->DbPrefix;
        $this->v["tbls"] = $this->tblQryStd();
        
        $this->v["log"] = '';
        if ($this->v["dbAllowEdits"] && $GLOBALS["SL"]->REQ->has('dbConfirm') && $GLOBALS["SL"]->REQ->input('dbConfirm') == 'install'
            && $GLOBALS["SL"]->REQ->has('createTable') && sizeof($GLOBALS["SL"]->REQ->input('createTable')) > 0) {
            $transferData = array();
            if ($GLOBALS["SL"]->REQ->has('copyData') && sizeof($GLOBALS["SL"]->REQ->input('copyData')) > 0) {
                foreach ($GLOBALS["SL"]->REQ->input('copyData') as $copyTbl) {
                    if (file_exists('../app/Models/' . $GLOBALS["SL"]->tblModels[$GLOBALS["SL"]->tbl[$copyTbl]])) {
                        eval("\$transferData[\$copyTbl] = " 
                            . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->tbl[$copyTbl])
                            . "::get();");
                        $this->v["log"] .= '<br />copying table data!.. ' . $GLOBALS["SL"]->tbl[$copyTbl];
                    }
                }
            }
        
            foreach ($GLOBALS["SL"]->REQ->input('createTable') as $createTbl) {
                $tbl = SLTables::find($createTbl);
                if (!in_array($GLOBALS["SL"]->tbl[$createTbl], ['users', 'Users'])) {
                    DB::statement('DROP TABLE IF EXISTS `' 
                        . $GLOBALS["SL"]->dbRow->DbPrefix . $GLOBALS["SL"]->tbl[$createTbl] . '`');
                    $createQry = $this->exportMysqlTbl($tbl, true);
                    echo $createQry . '<br />';
                    DB::statement($createQry);
                    $this->v["log"] .= '<br />creating table!.. ' . $GLOBALS["SL"]->tbl[$createTbl]; // $createQry;
                }
            }
            
            if ($GLOBALS["SL"]->REQ->has('copyData') && sizeof($GLOBALS["SL"]->REQ->input('copyData')) > 0) {
                foreach ($GLOBALS["SL"]->REQ->input('copyData') as $copyTbl) {
                    if (!in_array($GLOBALS["SL"]->tbl[$createTbl], ['users', 'Users'])) {
                        $this->v["log"] .= '<br />pasting table data!.. ' . $GLOBALS["SL"]->tbl[$copyTbl];
                        if (isset($transferData[$copyTbl]) && sizeof($transferData[$copyTbl]) > 0) {
                            $newFlds = array();
                            $flds = SLFields::where('FldTable', $copyTbl)
                                ->where('FldDatabase', $this->dbID)
                                ->get();
                            if ($flds && sizeof($flds) > 0) {
                                $tblAbbr = $GLOBALS["SL"]->tblAbbr[$GLOBALS['SL']->tbl[$copyTbl]];
                                foreach ($flds as $fld) $newFlds[] = $fld->FldName;
                                foreach ($transferData[$copyTbl] as $oldRec) {
                                    eval("\$newRec = new " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->tbl[$copyTbl]) . ";");
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
        return view('vendor.survloop.admin.db.install', $this->v);
    }
    
    
    protected function chkForeignKey($foreignKey)
    {
        if ($foreignKey && intVal($foreignKey) > 0 && isset($GLOBALS["SL"]->tbl[$foreignKey])) {
            if (strtolower($GLOBALS["SL"]->tbl[$foreignKey]) == 'users') {
                return ['users', 'id'];
            }
            return [
                $GLOBALS["SL"]->dbRow->DbPrefix . $GLOBALS["SL"]->tbl[$foreignKey], 
                $GLOBALS["SL"]->tblAbbr[$GLOBALS['SL']->tbl[$foreignKey]] . "ID"
            ];
        }
        return ['', ''];
    }
    
    // for emergency cases with questionable database access, this should only be temporarily included
    public function manualMySql(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/all');
        if ($this->v["user"]->id == 1) { // && in_array($_SERVER["REMOTE_ADDR"], ['192.168.10.1'])) {
    		$this->v["manualMySql"] = 'Connected successfully<br />';
            $db = mysqli_connect(env('DB_HOST', 'localhost'), env('DB_USERNAME', 'homestead'), 
                env('DB_PASSWORD', 'secret'), env('DB_DATABASE', 'homestead'));
            if ($db->connect_error) $this->v["manualMySql"] = "Connection failed: " . $db->connect_error . '<br />';
    		$this->v["lastSql"] = '';
    		$this->v["lastResults"] = [];
    		if ($request->has('mys') && trim($request->mys) != '') {
    		    $this->v["lastSql"] = trim($request->mys);
    		    $this->v["manualMySql"] .= '<b>Statements submitted...</b><br />';
    		    $statements = $GLOBALS["SL"]->mexplode(';', $request->mys);
    		    foreach ($statements as $sql) {
    		        $cnt = 0;
    		        if (trim($sql) != '') {
                        ob_start();
                        $res = mysqli_query($db, $sql);
                        $errorCatch = ob_get_contents();
                        ob_end_clean();
                        $this->v["lastResults"][$cnt][0] = $sql;
                        $this->v["lastResults"][$cnt][1] = $errorCatch;
                        if ($res && sizeof($res) > 0) {
                            ob_start();
                            print_r($res);
                            $this->v["lastResults"][$cnt][2] = ob_get_contents();
                            ob_end_clean();
                        }
                        $cnt++;
                    }
                }
    		}
    		mysqli_close($db);
    		return view('vendor.survloop.admin.db.manualMySql', $this->v);
    	}
        return $this->redir('/dashboard/db/export');
    }
    
    
    
}
