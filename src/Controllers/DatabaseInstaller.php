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
use App\Models\SLNode;
use App\Models\SLConditions;

use SurvLoop\Controllers\AdminDBController;

class DatabaseInstaller extends AdminDBController
{
    protected $dbMysql = false;
    
    protected function tweakAdmMenu($currPage = '')
    {
        $this->v["dateStmp"] = date("Y_m_d");
        $this->v["dateStamp"] = date("Y_m_d_His");
        $this->v["zipFileMig"] = $this->v["exportDir"] . '/' . $this->v["dateStmp"] . '_LaravelMigrations.zip';
        $this->v["zipFileModel"] = $this->v["exportDir"] . '/' . $this->v["dateStmp"] . '_LaravelModels.zip';
        return true;
    }
    
    protected function exportMysql()
    {
        if (!isset($this->v["export"])) $this->v["export"] = '';
        $tbls = $this->tblQryStd();
        if ($tbls->isNotEmpty()) {
            foreach ($tbls as $i => $tbl) {
                $this->v["export"] .= $GLOBALS["SL"]->exportMysqlTbl($tbl);
            }
            $this->v["export"] .= $GLOBALS["SL"]->x["indexesEnd"]; 
        }
        if (isset($GLOBALS["SL"]->x["exportAsPackage"]) && $GLOBALS["SL"]->x["exportAsPackage"]) {
            $GLOBALS["SL"]->exportMysqlSl();
        }
        return true;
    }
    
    protected function loadSlParents()
    {
        $this->v["slTrees"] = $this->v["slNodes"] = $this->v["slConds"] = [];
        $chk = SLTree::where('TreeDatabase', $this->dbID)
            ->select('TreeID')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $tree) $this->v["slTrees"][] = $tree->TreeID;
        }
        $chk = SLNode::whereIn('NodeTree', $this->v["slTrees"])
            ->select('NodeID')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $node) $this->v["slNodes"][] = $node->NodeID;
        }
        $chk = SLConditions::where('CondDatabase', $this->dbID)
            ->select('CondID')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $cond) $this->v["slConds"][] = $cond->CondID;
        }
        return true;
    }
    
    protected function loadSlSeedEval($tbl = [])
    {
        if (!isset($this->v["slTrees"])) $this->loadSlParents();
        $eval = "";
        if (isset($tbl->TblName)) {
            if ($tbl->TblName == 'Databases') {
                $eval = "where('DbID', " . $this->dbID . ")->";
            } elseif (in_array($tbl->TblName, ['Images'])) {
                $eval = "where('" . $tbl->TblAbbr . "DatabaseID', " . $this->dbID . ")->";
            } elseif (in_array($tbl->TblName, ['BusRules', 'Conditions', 'Definitions', 'Fields', 'Tables', 'Tree'])) {
                $eval = "where('" . $tbl->TblAbbr . "Database', " . $this->dbID . ")->";
            } elseif (in_array($tbl->TblName, ['Node', 'DataHelpers', 'DataLinks', 'DataLoop', 'DataSubsets', 
                'Emails'])) {
                $eval = "whereIn('" . $tbl->TblAbbr . "Tree', [" . implode(", ", $this->v["slTrees"]) . "])->";
            } elseif (in_array($tbl->TblName, ['NodeResponses'])) {
                $eval = "whereIn('" . $tbl->TblAbbr . "Node', [" . implode(", ", $this->v["slNodes"]) . "])->";
            } elseif (in_array($tbl->TblName, ['ConditionsArticles', 'ConditionsNodes', 'ConditionsVals'])) {
                $eval = "whereIn('" . $tbl->TblAbbr . "CondID', [" . implode(", ", $this->v["slConds"]) ."])->";
            }
        }
        return $eval;
    }
    
    public function printExportPackage(Request $request)
    {
        return $this->printExport($request, true);
    }
    
    public function printExport(Request $request, $asPackage = false)
    {
        $this->admControlInit($request, (($asPackage) ? '/dashboard/sl/export/laravel' : '/dashboard/db/export'));
        if ($asPackage) $GLOBALS["SL"]->x["exportAsPackage"] = true;
        if (!$this->checkCache((($asPackage) ? '/dashboard/sl/export' : '/dashboard/db/export'))) {
            $this->exportMysql();
            $this->v["content"] = view('vendor.survloop.admin.db.export-mysql', $this->v)->render();
            $this->saveCache();
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    public function printExportPackageLaravel(Request $request) 
    {
        return $this->printExportLaravel($request, true);
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
		$this->v["migrationFileUp"] = $this->v["migrationFileDown"] = $this->v["tblClean"] = '';
        if (!isset($this->v["modelFile"])) $this->v["modelFile"] = "";
        return true;
    }
    
    public function printExportLaravel(Request $request, $asPackage = false) 
    {
        ini_set('max_execution_time', 180);
        $this->admControlInit($request, (($asPackage) 
            ? '/dashboard/sl/export/laravel' : '/dashboard/db/export'));
        if ($asPackage) $GLOBALS["SL"]->x["exportAsPackage"] = true;
        if (!$this->checkCache(($asPackage) ? '/dashboard/sl/export/laravel' : '/dashboard/db/export/laravel')) {
            
        	$this->v["refresh"] = 1;
            if ($GLOBALS["SL"]->REQ->has('refresh')) $this->v["refresh"] = intVal($GLOBALS["SL"]->REQ->refresh);
			$newMigFilename = 'database/migrations/' . $this->v["dateStmp"] . '_000000_create_' 
				. strtolower($GLOBALS["SL"]->dbRow->DbName) . '_tables.php';
			$newSeedFilename = 'database/seeds/' . str_replace('_', '', $GLOBALS["SL"]->dbRow->DbPrefix) 
				. 'Seeder.php';
		    $migEnds = '';
            $tbls = $this->exportQryTbls();
            if ($this->v["refresh"] == 1) {
                
            	$this->prepLaravelExport();
				$this->chkModelsFolder();
				if ($tbls->isNotEmpty()) {
					foreach ($tbls as $tbl) {
						$indexes = "";
						$this->loadTbl($tbl);
						$this->v["migrationFileUp"] .= "\t"."Schema::create('" . $GLOBALS["SL"]->dbRow->DbPrefix 
							. $tbl->TblName . "', function(Blueprint $"."table)\n\t\t{\n\t\t\t"
							."$"."table->increments('" . $tbl->TblAbbr . "ID');";
						$this->v["modelFile"] = ''; // also happens in CoreGlobals->chkTblModel($tbl)
						$flds = $this->getTableFields($tbl);
						if ($flds->isNotEmpty()) {
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
								/* // This is throwing errors
								if (intVal($fld->FldForeignTable) > 0) {
									list($forTbl, $forID) = $GLOBALS["SL"]->chkForeignKey($fld->FldForeignTable);
                                    $migEnds .= "\t"."Schema::table('" . $GLOBALS["SL"]->dbRow->DbPrefix . $tbl->TblName 
                                        . "', function($"."table) { $"."table->foreign('" . $fldName 
                                        . "')->references('" . $forID . "')->on('" . $forTbl . "'); });\n";
								}
								*/
							}
						}
						$this->v["migrationFileUp"] .= "\n\t\t\t"."$"."table->timestamps();"."\n\t\t"."});"."\n\t";
						$this->v["migrationFileDown"] .= "\t"."Schema::drop('" . $GLOBALS["SL"]->dbRow->DbPrefix 
							. $tbl->TblName . "');"."\n\t";
						$this->saveModelFile();
					}
				}
				if (trim($migEnds) != '') $this->v["migrationFileUp"] .= $migEnds;
				Storage::put($newMigFilename, 
					view('vendor.survloop.admin.db.export-laravel-gen-migration', $this->v)->render());
				$this->v["content"] = view('vendor.survloop.admin.db.export-laravel-progress', $this->v)->render();
				return view('vendor.survloop.master', $this->v);
				
			} elseif ($this->v["refresh"] == 2) {
				
				Storage::put($newSeedFilename, view('vendor.survloop.admin.db.export-laravel-gen-seeder', [
					"wholeSeed" => false ])->render());
				if ($tbls->isNotEmpty()) {
					foreach ($tbls as $tbl) {
						$this->loadTbl($tbl);
						$runTable = true;
						if ($GLOBALS["SL"]->dbRow->DbPrefix == 'SL_') {
							if (in_array($tbl->TblName, ['Zips', 'ZipAshrae'])) {
								$runTable = $asPackage;
							} elseif (in_array($tbl->TblName, ['Sess', 'SessLoops', 'SessEmojis', 'LogActions', 
								'Emailed', 'Tokens', 'UsersActivity', 'UsersRoles', 'SearchRecDump', 'NodeSaves', 
								'NodeSavesPage', 'users', 'DesignTweaks'])) {
								$runTable = false;
							}
						} elseif ($asPackage) {
							$runTable = in_array($tbl->TblName, $this->CustReport->tblsInPackage());
						}
						if ($runTable) {
							Storage::append($newSeedFilename, $this->printSeedTbl($this->loadSlSeedEval($tbl)));
						}
					}
				}
				if ($asPackage && $GLOBALS["SL"]->dbRow->DbPrefix != 'SL_') {
					$tbls = $GLOBALS["SL"]->tblQrySlExports();
					if ($tbls->isNotEmpty()) {
						foreach ($tbls as $tbl) {
							$this->v["tbl"] = $tbl;
							$this->v["tblName"] = 'SL_' . $tbl->TblName;
							$this->v["tblClean"] = str_replace('_', '', $this->v["tblName"]);
							Storage::append($newSeedFilename, $this->printSeedTbl($this->loadSlSeedEval($tbl)));
						}
					}
				}
				Storage::append($newSeedFilename, ' } } ');
				$this->v["content"] = view('vendor.survloop.admin.db.export-laravel-progress', $this->v)->render();
				return view('vendor.survloop.master', $this->v);
				
			} elseif ($this->v["refresh"] > 2) {
				
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
					. "/SurvLoop2Laravel-Export-" . date("Y-m-d") . ".zip"));
				*/
				
                $this->v["dumpOut"]["Migrations"] = $newMigFilename;
                $this->v["dumpOut"]["Seeders"] = $newSeedFilename;
				$this->v["content"] = view('vendor.survloop.admin.db.export-laravel', $this->v)->render();
				$this->saveCache();
				
			}
        }
        if ($request->has('refreshVendor')) {
            $this->v["content"] = $GLOBALS["SL"]->copyDirFiles('../app/Models/' . $GLOBALS["SL"]->sysOpts["cust-abbr"],
                    '../vendor/' . $GLOBALS["SL"]->sysOpts["cust-package"] . '/src/Models')
                . $GLOBALS["SL"]->copyDirFiles('../storage/app/database/migrations',
                    '../vendor/' . $GLOBALS["SL"]->sysOpts["cust-package"] . '/src/Database') . $this->v["content"];
        }
        return view('vendor.survloop.master', $this->v);
    }
    
    protected function refreshTableModel(Request $request, $tbl = '')
    {
        $this->admControlInit($request, '/dashboard/db/export/laravel/table-model');
        $this->prepLaravelExport();
        $tbl = SLTables::where('TblName', $tbl)
            ->first();
        $this->loadTbl($tbl);
        $flds = $this->getTableFields($tbl);
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                $this->v["modelFile"] .= "\n\t\t'" . trim($tbl->TblAbbr . $fld->FldName) . "', ";
            }
        }
        $this->saveModelFile();
        return ':)' . (($request->has('redir64')) ? '<script type="text/javascript"> setTimeout("window.location=\'' 
            . base64_decode($request->get('redir64')) . '\'", 100); </script>' : '');
    }
    
    protected function saveModelFile()
    {
        $newModelFilename = '../app/Models/' . $GLOBALS["SL"]->sysOpts["cust-abbr"] . '/' 
            . $this->v["tblClean"] . '.php';
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
        return true;
    }
    
    protected function printSeedTbl($eval = '')
    {
    	$ret = '';
        $seedChk = $this->getTableSeedDump($this->v["tblClean"], $eval);
        if ($seedChk->isNotEmpty()) {
            foreach ($seedChk as $seed) {
                $fldData = "\n\t\t\t'" . $this->v["tbl"]->TblAbbr . "ID' => " . $seed->getKey();
                $flds = $this->getTableFields($this->v["tbl"]);
                if ($flds->isNotEmpty()) {
                    foreach ($flds as $i => $fld) {
                        $fldName = trim($this->v["tbl"]->TblAbbr . $fld->FldName);
                        if (isset($seed->{ $fldName }) && trim($seed->{ $fldName }) != trim($fld->FldDefault)) {
                            $fldData .= ",\n\t\t\t'" . $fldName . "' => '" 
                                . str_replace("'", "\'", $seed->{ $fldName }) . "'";
                        }
                    }
                }
                if (trim($fldData) != '') {
                    $ret .= "\tDB::table('" 
                        . (($this->v["tbl"]->TblDatabase == 3) ? 'SL_' : $GLOBALS["SL"]->dbRow->DbPrefix)
                        . $this->v["tbl"]->TblName . "')->insert([" . $fldData . "\n\t\t"."]);"."\n\t";
                }
            }
        }
        return $ret;
    }
    
    protected function exportQryTbls()
    {
        return SLTables::where('TblDatabase', $this->dbID)
            ->where('TblName', 'NOT LIKE', 'Users')
            ->where('TblName', 'NOT LIKE', 'users')
            ->orderBy('TblOrd')
            ->get();
    }
    
    protected function loadTbl($tbl = [])
    {
        $this->v["tbl"] = $tbl;
        $this->v["tblName"] = $GLOBALS["SL"]->dbRow->DbPrefix . $tbl->TblName;
        $this->v["tblClean"] = str_replace('_', '', $this->v["tblName"]);
        return true;
    }
    
    public function printExportLaravelProgress(Request $request) 
    {
        
        $this->v["content"] = view('vendor.survloop.admin.db.export-laravel-progress', $this->v)->render();
        return view('vendor.survloop.master', $this->v);
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
                        $newModelFilename = '../app/Models/' . $GLOBALS["SL"]->sysOpts["cust-abbr"] . '/' 
                            . $this->v["tblClean"] . '.php';
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
        
        $this->v["DbPrefix"] = $GLOBALS["SL"]->dbRow->DbPrefix;
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
                    if (file_exists('../app/Models/' . $GLOBALS["SL"]->tblModels[$GLOBALS["SL"]->tbl[$copyTbl]])) {
                        eval("\$transferData[\$copyTbl] = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->tbl[$copyTbl])
                            . "::get();");
                        $this->v["log"] .= '<br />copying table data!.. ' . $GLOBALS["SL"]->tbl[$copyTbl];
                    }
                }
            }
        
            foreach ($GLOBALS["SL"]->REQ->input('createTable') as $createTbl) {
                $tbl = SLTables::find($createTbl);
                if (!in_array(strtolower($GLOBALS["SL"]->tbl[$createTbl]), ['users'])) {
                    DB::statement('DROP TABLE IF EXISTS `' . $GLOBALS["SL"]->dbRow->DbPrefix 
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
                            $flds = SLFields::where('FldTable', $copyTbl)
                                ->where('FldDatabase', $this->dbID)
                                ->get();
                            if ($flds->isNotEmpty()) {
                                $tblAbbr = $GLOBALS["SL"]->tblAbbr[$GLOBALS['SL']->tbl[$copyTbl]];
                                foreach ($flds as $fld) $newFlds[] = $fld->FldName;
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
    
    // for emergency cases with questionable database access, this should only be temporarily included
    public function manualMySql(Request $request)
    {
        $this->admControlInit($request, '/dashboard/db/all');
        if ($this->v["uID"] == 1) { // && in_array($_SERVER["REMOTE_ADDR"], ['192.168.10.1'])) {
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
                        if ($res->isNotEmpty()) {
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
    
    protected function chkModelsFolder()
    {
        if (!file_exists('../app/Models')) mkdir('../app/Models');
        if (!file_exists('../app/Models/' . $GLOBALS["SL"]->sysOpts["cust-abbr"])) {
            mkdir('../app/Models/' . $GLOBALS["SL"]->sysOpts["cust-abbr"]);
        }
        return true;
    }
    
    
}
