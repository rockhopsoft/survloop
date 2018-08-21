<?php
namespace SurvLoop\Controllers;

use Storage;
use Illuminate\Http\Request;

use App\Models\SLDatabases;
use App\Models\SLTables;
use App\Models\SLFields;
use App\Models\SLTree;
use App\Models\SLDefinitions;
use App\Models\SLConditionsNodes;
use App\Models\SLZips;
use App\Models\SLTokens;

class CoreGlobalsImportExport extends CoreGlobalsTables
{
    private $exprtProg = [];
    
    public function loadDBFromCache(Request $request = NULL)
    {
        $cacheFile = '/cache/db-load-' . $this->dbID . '-' . $this->treeID . '.php';
        if ((!$request || !$request->has('refresh')) && file_exists($cacheFile)) {
            $content = Storage::get($cacheFile);
            eval($content);
        } else {
            $cache = '// Auto-generated loading cache from /SurvLoop/Controllers/CoreGlobals.php' . "\n\n";
            
            $cache .= '$'.'this->allDbs = [];' . "\n";
            $allDbs = SLDatabases::get();
            if ($allDbs->isNotEmpty()) {
                foreach ($allDbs as $db) {
                    $cache .= '$'.'this->allDbs[] = ['
                        . ' "id" => ' . $db->DbID . ', '
                        . ' "name" => "' . str_replace('"', '\\"', $db->DbName) . '", '
                        . ' "prfx" => "' . $db->DbPrefix . '" '
                        . '];' . "\n";
                }
            }
            
            $this->allTrees = [];
            $cache .= '$'.'this->allTrees = [];' . "\n";
            $allTrees = SLTree::where('TreeType', 'Survey')
                ->orderBy('TreeName', 'asc')
                ->get();
            if ($allTrees->isNotEmpty()) {
                foreach ($allTrees as $tree) {
                    if (!isset($this->allTrees[$tree->TreeDatabase])) {
                        $this->allTrees[$tree->TreeDatabase] = [];
                        $cache .= '$'.'this->allTrees[' . $tree->TreeDatabase . '] = [];' . "\n";
                    }
                    $cache .= '$'.'this->allTrees[' . $tree->TreeDatabase . '][] = ['
                        . ' "id" => ' . $tree->TreeID . ', '
                        . ' "name" => "' . str_replace('"', '\\"', $tree->TreeName) . '", '
                        . ' "slug" => "' . $tree->TreeSlug . '", '
                        . ' "opts" => ' . ((isset($tree->TreeOpts) && intVal($tree->TreeOpts) > 0) 
                            ? $tree->TreeOpts : 1) . ' ];' . "\n";
                }
            }
            
            if ($this->treeRow && isset($this->treeRow->TreeRoot)) {
                if ($this->treeRow->TreeRoot > 0) {
                    $chk = SLConditionsNodes::select('SL_ConditionsNodes.CondNodeID')
                        ->join('SL_Conditions', 'SL_Conditions.CondID', '=', 'SL_ConditionsNodes.CondNodeCondID')
                        ->where('SL_Conditions.CondTag', '#IsAdmin')
                        ->where('SL_ConditionsNodes.CondNodeNodeID', $this->treeRow->TreeRoot)
                        ->first();
                    if ($chk && isset($chk->CondNodeID)) {
                        if ($this->treeRow->TreeOpts%3 > 0) {
                            $this->treeRow->TreeOpts *= 3;
                            $this->treeRow->save();
                        }
                    }
                }
                if ($this->treeRow->TreeOpts%3 == 0) {
                    $cache .= '$'.'this->treeIsAdmin = true;' . "\n"
                        . '$'.'this->treeBaseSlug = "/dash/' . $this->treeRow->TreeSlug . '/";' . "\n";
                } else {
                    $cache .= '$'.'this->treeBaseSlug = "/u/' . $this->treeRow->TreeSlug . '/";' . "\n";
                }
            }

            //$sys = SLDefinitions::where('DefDatabase', $this->dbID)
            //    ->where('DefSet', 'System Settings')
            //    ->get(); 
            //if (!$sys || sizeof($sys) == 0) {
                $sys = SLDefinitions::where('DefDatabase', 1)
                    ->where('DefSet', 'System Settings')
                    ->get();
            //}
            if ($sys->isNotEmpty()) {
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
                        $cache .= '$'.'this->coreTbl = \'' . $coreTbl . '\';' . "\n";
                    }
                    $cache .= '$'.'this->tbls[] = ' . $tbl->TblID . ';' . "\n"
                        . '$'.'this->tblI[\'' . $tbl->TblName . '\'] = ' . intVal($tbl->TblID) . ';' . "\n"
                        . '$'.'this->tbl[' . $tbl->TblID . '] = \'' . $tbl->TblName . '\';' . "\n"
                        . '$'.'this->tblEng[' . $tbl->TblID . '] = \'' .str_replace("'", "\\'", $tbl->TblEng).'\';'."\n"
                        . '$'.'this->tblOpts[' . $tbl->TblID . '] = ' . intVal($tbl->TblOpts) . ';' . "\n"
                        . '$'.'this->tblAbbr[\'' . $tbl->TblName . '\'] = \'' . $tbl->TblAbbr . '\';' . "\n"
                        . '$'.'this->fldTypes[\'' . $tbl->TblName . '\'] = [];' . "\n"
                        . '$'.'this->fldTypes[\'' . $tbl->TblName . '\'][\'' . $tbl->TblAbbr . 'ID\'] = \'INT\';' . "\n"
                        . '$'.'this->tblModels[\'' . $tbl->TblName . '\'] = \'' 
                        . str_replace('_', '', $this->dbRow->DbPrefix . $tbl->TblName) . '\';' . "\n";
                    if ($this->treeRow && isset($this->treeRow->TreeCoreTable) 
                        && $tbl->TblID == $this->treeRow->TreeCoreTable) {
                    	$coreType = '$'.'this->fldTypes[\'' . $tbl->TblName . '\'][\'' . $tbl->TblAbbr;
                        $cache .= $coreType . 'UserID\'] = \'INT\';' . "\n"
                        	. $coreType . 'SubmissionProgress\'] = \'INT\';' . "\n"
                        	. $coreType . 'TreeVersion\'] = \'VARCHAR\';' . "\n"
                        	. $coreType . 'VersionAB\'] = \'VARCHAR\';' . "\n"
                        	. $coreType . 'UniqueStr\'] = \'VARCHAR\';' . "\n"
                        	. $coreType . 'IPaddy\'] = \'VARCHAR\';' . "\n"
                        	. $coreType . 'IsMobile\'] = \'INT\';' . "\n";
                    }
                    // temporarily loading for the sake of cache creation...
                    $this->tbl[$tbl->TblID] = $tbl->TblName;
                    $this->tblAbbr[$tbl->TblName] = $tbl->TblAbbr;
                }
                $cache .= '$'.'this->coreTbl = \'' . $coreTbl . '\';' . "\n";
                
                $fldNames = [];
                $flds = SLFields::select()
                    ->where('FldDatabase', $this->dbID)
                    ->where('FldTable', '>', 0)
                    ->orderBy('FldOrd', 'asc')
                    ->get();
                foreach ($flds as $fld) {
                    if (isset($this->tbl[$fld->FldTable])) {
                        $fldName = $this->tblAbbr[$this->tbl[$fld->FldTable]] . $fld->FldName;
                        $fldNames[] = $fldName;
                        $cache .= '$'.'this->fldTypes[\'' . $this->tbl[$fld->FldTable] . '\'][\'' . $fldName 
                            . '\'] = \'' . $fld->FldType . '\';' . "\n";
                        if (strtolower(substr($fldName, strlen($fldName)-5)) == 'other') {
                            $othFld = substr($fldName, 0, strlen($fldName)-5);
                            if (trim($othFld) != '' && in_array($othFld, $fldNames)) {
                                $cache .= '$'.'this->fldOthers[\'' . $fldName . '\'] = ' . $fld->FldID . ';' . "\n";
                            }
                        }
                    }
                }
                
                $cache .= $this->loadDataMapLinks($this->treeID);
                
                $cache .= '$'.'this->nodeCondInvert = [];' . "\n";
                $inv = SLConditionsNodes::where('CondNodeNodeID', '>', 0)
                    ->where('CondNodeLoopID', '<', 0)
                    ->get();
                if ($inv->isNotEmpty()) {
                    foreach ($inv as $invert) {
                        if (!isset($this->nodeCondInvert[$invert["CondNodeNodeID"]])) {
                            $cache .= '$'.'this->nodeCondInvert[' . $invert["CondNodeNodeID"] . '] = [];' . "\n";
                            $this->nodeCondInvert[$invert["CondNodeNodeID"]] = [];
                        }
                        if (!isset($this->nodeCondInvert[$invert["CondNodeNodeID"]][$invert["CondNodeCondID"]])) {
                            $cache .= '$'.'this->nodeCondInvert[' . $invert["CondNodeNodeID"] . '][' 
                                . $invert["CondNodeCondID"] . '] = true;' . "\n";
                            $this->nodeCondInvert[$invert["CondNodeNodeID"]][$invert["CondNodeCondID"]] = true;
                        }
                    }
                }
            } // end if (isset($this->dbRow->DbPrefix))

            eval($cache);
            
            $cache2 = '';
            $extends = SLTables::where('TblDatabase', $this->dbID)
                ->where('TblExtend', '>', 0)
                ->select('TblID', 'TblAbbr', 'TblExtend')
                ->get();
            if ($extends->isNotEmpty()) {
                foreach ($extends as $tbl) {
                    if (isset($this->tbl[$tbl->TblID]) && isset($this->fldTypes[$this->tbl[$tbl->TblExtend]])
                        && is_array($this->fldTypes[$this->tbl[$tbl->TblExtend]])
                        && sizeof($this->fldTypes[$this->tbl[$tbl->TblExtend]]) > 0) {
                        $cache2 .= '$'.'this->fldTypes[\'' . $this->tbl[$tbl->TblID] . '\'][\'' 
                            . $tbl->TblAbbr . $this->tblAbbr[$this->tbl[$tbl->TblExtend]] . 'ID\'] = \'INT\';' . "\n";
                        foreach ($this->fldTypes[$this->tbl[$tbl->TblExtend]] as $fldName => $fldType) {
                            $fldName2 = $this->tblAbbr[$this->tbl[$tbl->TblID]] . $fldName;
                            $cache2 .= '$'.'this->fldTypes[\'' . $this->tbl[$tbl->TblID] . '\'][\'' 
                                . $fldName2 . '\'] = \'' . $fldType . '\';' . "\n";
                            $fldNames[] = $fldName2;
                        }
                    }
                }
            }
            
            $this->getCoreTblUserFld();
            $cache2 .= '$'.'this->coreTblUserFld = \'' . $this->coreTblUserFld . '\';' . "\n";
            if ($this->treeRow && isset($this->treeRow->TreeType) && $this->treeRow->TreeType == 'Survey') {
                $xmlTree = SLTree::where('TreeSlug', $this->treeRow->TreeSlug)
                    ->where('TreeDatabase', $this->treeRow->TreeDatabase)
                    ->where('TreeType', 'Survey XML')
                    ->orderBy('TreeID', 'asc')
                    ->first();
                if ($xmlTree && isset($xmlTree->TreeID)) {
                    if (!isset($xmlTree->TreeRoot) || intVal($xmlTree->TreeRoot) <= 0) {
                        if (intVal($xmlTree->TreeCoreTable) > 0) {
                            $xmlRootNode = new SLNode;
                            $xmlRootNode->NodeTree        = $xmlTree->TreeID;
                            $xmlRootNode->NodeParentID    = -3;
                            $xmlRootNode->NodeType        = 'XML';
                            $xmlRootNode->NodePromptText  = $this->tbl[$xmlTree->TreeCoreTable];
                            $xmlRootNode->NodePromptNotes = $xmlTree->TreeCoreTable;
                            $xmlRootNode->save();
                            $xmlTree->TreeRoot = $xmlRootNode->NodeID;
                            $xmlTree->save();
                        }
                    }
                    $cache2 .= '$'.'this->xmlTree = [ '
                        . '"id" => '        . $xmlTree->TreeID . ', '
                        . '"root" => '      . ((intVal($xmlTree->TreeRoot) > 0) ? $xmlTree->TreeRoot : 0) . ', '
                        . '"coreTblID" => ' . ((intVal($xmlTree->TreeCoreTable) > 0) 
                            ? $xmlTree->TreeCoreTable : 0) . ', '
                        . '"coreTbl" => "'  . ((isset($this->tbl[$xmlTree->TreeCoreTable])) 
                            ? $this->tbl[$xmlTree->TreeCoreTable] : '') . '", '
                        . '"opts" => ' . ((isset($xmlTree->TreeOpts) && intVal($xmlTree->TreeOpts) > 0) 
                            ? $xmlTree->TreeOpts : 0)
                    . ' ];' . "\n";
                }
                $reportTree = $this->chkReportTree();
                if ($reportTree) {
                    $cache2 .= '$'.'this->reportTree = [ '
                        . '"id" => '    . $reportTree->TreeID . ', '
                        . '"root" => '  . $reportTree->TreeRoot . ', '
                        . '"slug" => "' . $reportTree->TreeSlug . '", '
                        . '"opts" => '  . $reportTree->TreeOpts . ''
                    . ' ];' . "\n";
                }
            } elseif ($this->treeRow->TreeOpts%13 == 0) {
                $reportTree = SLTree::where('TreeType', 'Survey')
                    ->where('TreeDatabase', $this->dbID)
                    ->where('TreeCoreTable', $this->treeRow->TreeCoreTable)
                    ->get();
                if ($reportTree->isNotEmpty()) {
                    foreach ($reportTree as $t) {
                        if ($t->TreeOpts%13 > 0) {
                            $foundRepTree = true;
                            $cache2 .= '$'.'this->reportTree = [ '
                                . '"id" => '   . $t->TreeID . ', '
                                . '"root" => ' . $t->TreeRoot . ', '
                                . '"slug" => "' . $t->TreeSlug . '", '
                                . '"opts" => ' . $t->TreeOpts . ''
                            . ' ];' . "\n";
                        }
                    }
                }
            }
            
            $this->x["srchUrls"] = [ 'public' => '', 'administrator' => '', 'volunteer' => '', 'partner' => '', 
                'staff' => '' ];
            if ($this->treeRow && isset($this->treeRow->TreeDatabase)) {
                $searchTrees = SLTree::where('TreeDatabase', $this->treeRow->TreeDatabase)
                    ->where('TreeType', 'Page')
                    ->where('TreeOpts', '>', 1)
                    ->orderBy('TreeID', 'desc')
                    ->get();
                if ($searchTrees->isNotEmpty()) {
                    foreach ($searchTrees as $tree) {
                        if ($tree->TreeOpts%31 == 0) {
                            if ($tree->TreeOpts%3 == 0) $this->x["srchUrls"]["administrator"] = '/dash/' . $tree->TreeSlug;
                            elseif ($tree->TreeOpts%17 == 0) $this->x["srchUrls"]["volunteer"] = '/dash/' . $tree->TreeSlug;
                            elseif ($tree->TreeOpts%41 == 0) $this->x["srchUrls"]["partner"] = '/dash/' . $tree->TreeSlug;
                            elseif ($tree->TreeOpts%43 == 0) $this->x["srchUrls"]["staff"] = '/dash/' . $tree->TreeSlug;
                            else $this->x["srchUrls"]["public"] = '/' . $tree->TreeSlug;
                        }
                    }
                }
            }
            $cache2 .= '$'.'this->x["srchUrls"] = [ '
                . '"public"        => \'' . $this->x["srchUrls"]["public"]        . '\', '
                . '"administrator" => \'' . $this->x["srchUrls"]["administrator"] . '\', '
                . '"staff"         => \'' . $this->x["srchUrls"]["staff"]         . '\', '
                . '"partner"       => \'' . $this->x["srchUrls"]["partner"]       . '\', '
                . '"volunteer"     => \'' . $this->x["srchUrls"]["volunteer"]     . '\''
            . ' ];' . "\n";
            
            eval($cache2);
            
            if (file_exists($cacheFile)) Storage::delete($cacheFile);
            Storage::put($cacheFile, $cache . $cache2);
        }
        return true;
    }
    
    public function getGenericRows($tbl, $fld = '', $val = '', $oper = '', $ordFld = '')
    {
        $eval = "\$rows = " . $this->modelPath($tbl) . "::" . ((trim($fld) != '') 
            ? "where('" . $fld . "'" . ((trim($oper) != '') ? ", '" . $oper . "'" : "") . ", '" . $val . "')->" : "")
            . ((in_array($oper, ['<>', 'NOT LIKE']) && strtolower($val) != 'null') 
                ? "orWhere('" . $fld . "', NULL)->" : "")
            . ((trim($ordFld) != '') ? "orderBy('" . $ordFld . "', 'asc')->" : "") 
            . ((isset($this->exprtProg["tok"]) && $this->exprtProg["tok"] 
                && intVal($this->exprtProg["tok"]->TokCoreID) > 0) 
                ? "offset(" . $this->exprtProg["tok"]->TokCoreID . ")->" : "")
            . "limit(50000)->get();";
        eval($eval);
        return $rows;
    }
    
    public function genCsv($tbl, $fld = '', $val = '', $oper = '', $ordFld = '', $filebase = '')
    {
        $flds = $this->getTblFldTypes($tbl);
        if (trim($filebase) == '') {
            $filebase = $tbl . ((trim($val) != '') ? '-' . $val . ((trim($oper) != '') ? '-' . $oper : '') : '');
        }
        $this->getExportProgress($filebase);
        if (sizeof($flds) > 0) {
            $dir = '../storage/app/database/csv';
            if (!file_exists($dir)) mkdir($dir);
            $this->exprtProg["fileName"] = $dir . '/' . $filebase . '.csv';
            $this->exprtProg["fileCnt"] = 1;
            if ($this->REQ->has('export')) {
                $this->exprtProg["fileCnt"] = $this->REQ->get('export');
                if ($this->exprtProg["fileCnt"] == 1) $this->exprtProg["tok"]->TokCoreID = 0;
            } elseif (isset($this->exprtProg["tok"]) && $this->exprtProg["tok"] 
                && intVal($this->exprtProg["tok"]->TokTreeID) > 0) {
                $this->exprtProg["fileCnt"] = $this->exprtProg["tok"]->TokTreeID;
            }
            $hasMore = $fail = 1;
            $this->exprtProg["fileContent"] = $this->genCsvHeader($tbl, $flds);
            while ($hasMore > 0 && $fail < 100) {
                $rows = $this->getGenericRows($tbl, $fld, $val, $oper, $ordFld);
                if ($rows->isNotEmpty()) {
                    $hasMore = $rows->count();
                    foreach ($rows as $i => $row) {
                        $rowCsv = '';
                        foreach ($flds as $fld => $type) {
                            $rowCsv .= ',' . ((isset($row->{ $fld })) ? str_replace(',', '!;!', $row->{ $fld }) : '');
                        }
                        if (trim($rowCsv) != '') $rowCsv = substr($rowCsv, 1) . "\n";
                        $this->exprtProg["fileContent"] .= $rowCsv;
                        $this->exprtProg["tok"]->TokCoreID++;
                        if (strlen($this->exprtProg["fileContent"]) > 9000000) {
                            $this->genCsvStore();
                            echo '<html><body><br /><br /><center>' . $this->sysOpts["spinner-code"] 
                                . '<br /></center>' . "\n"
                                . '<script type="text/javascript"> setTimeout("window.location=\'' 
                                . $this->getCurrUrlBase() . '?export=' . $this->exprtProg["fileCnt"] . '&off=' 
                                . $this->exprtProg["tok"]->TokCoreID . '\'", 1000); </script></body></html>';
                            exit;
                        }
                    }
                    $fail++;
                } else {
                    $hasMore = 0;
                }
            }
            $this->genCsvStore();
            session()->put('sessMsg', '<h2 class="slBlueDark">Export Complete!</h2>');
            echo '<html><body><br /><br /><center>' . $this->sysOpts["spinner-code"] . '<br /></center>' . "\n"
                . '<script type="text/javascript"> setTimeout("window.location=\'' . $this->getCurrUrlBase() 
                . '\'", 1000); </script></body></html>';
            exit;
        }
        return true;
    }
    
    public function genCsvHeader($tbl, $flds)
    {
        $ret = '';
        if (isset($flds) && sizeof($flds) > 0) {
            foreach ($flds as $fld => $type) $ret .= ',' . $fld;
            if (trim($ret) != '') $ret = substr($ret, 1) . "\n";
        }
        return $ret;
    }
    
    public function genCsvStore()
    {
        $filename = str_replace('.csv', '-' . $this->leadZero($this->exprtProg["fileCnt"]) . '.csv', 
            $this->exprtProg["fileName"]);
        if (file_exists($filename)) unlink($filename);
        file_put_contents($filename, $this->exprtProg["fileContent"]);
        $this->exprtProg["fileCnt"]++;
        $this->exprtProg["tok"]->TokTreeID = $this->exprtProg["fileCnt"];
        $this->exprtProg["tok"]->save();
        $this->exprtProg["fileContent"] = '';
        return true;
    }
    
    public function getExportProgress($filebase, $expImp = 'Export')
    {
        $this->exprtProg["tok"] = SLTokens::where('TokType', $expImp . ' Progress')
            ->where('TokTokToken', $filebase)
            ->where('TokUserID', $this->uID)
            ->where('created_at', '>', date("Y-m-d H:i:s", mktime(0,0,0,date("n"),date("j")-2,date("Y"))))
            ->first();
        if (!$this->exprtProg["tok"]) {
            $this->exprtProg["tok"] = new SLTokens;
            $this->exprtProg["tok"]->TokType     = $expImp . ' Progress';
            $this->exprtProg["tok"]->TokTokToken = $filebase;
            $this->exprtProg["tok"]->TokUserID   = $this->uID;
            $this->exprtProg["tok"]->TokCoreID   = 0;
        }
        return true;
    }
    
    public function openFileLines($filename = '')
    {
		$content = [];
		if (file_exists($filename)) {
		    $content = $this->mexplode("\n", $this->get_content($filename));
		    if (sizeof($content) == 0) $content = $this->mexplode("\n", file_get_contents($filename));
		}
    	return $content;
    }
    
    public function importCsv($tbl, $filebase = '')
    {
    	if (!file_exists($filebase) && file_exists(str_replace('.csv', '-01.csv', $filebase))) {
    		$filebase = str_replace('.csv', '-01.csv', $filebase);
    	}
        $flds = $this->getTblFldTypes($tbl);
        $this->getExportProgress($filebase, 'Import');
        $this->exprtProg["tok"]->TokTreeID = 1;
    	$this->exprtProg["fileName"] = str_replace('-01.csv', '-' 
    		. $this->leadZero($this->exprtProg["tok"]->TokTreeID) . '.csv', $filebase);
        if (sizeof($flds) > 0) {
			$lines = $this->openFileLines($this->exprtProg["fileName"]);
        	while (sizeof($lines) > 0) {
				if (sizeof($lines) > 0) {
					$cols = [];
					foreach ($lines as $i => $l) {
						$row = $this->mexplode(',', $l);
						if (sizeof($row) > 0) {
							if ($i == 0) {
								foreach ($row as $j => $fld) $cols[$j] = $fld;
							} else {
								eval("\$rec = new " . $this->modelPath($tbl) . ";");
								foreach ($row as $j => $val) {
									$rec->{ $cols[$j] } = str_replace('!;!', ',', $val);
								}
								$rec->save();
							}
						}
					}
				}
				$this->exprtProg["tok"]->TokTreeID++;
				$this->exprtProg["tok"]->save();
				$this->exprtProg["fileName"] = str_replace('-01.csv', '-' 
					. $this->leadZero($this->exprtProg["tok"]->TokTreeID) . '.csv', $filebase);
            }
        }
        return true;
    }
    
    public function importZipsUS()
    {
        $chk = SLZips::where('ZipCountry', 'IS', NULL)
        	->orWhere('ZipCountry', 'LIKE', '')
            ->first();
        if (!$chk) {
        	$this->importCsv('Zips', 'https://survloop.org/survlooporg/expansion-pack/Zips-US-01.csv');
        }
        return true;
    }
    
    // sourced from https://fusiontables.google.com/DataSource?docid=1H_cl-oyeG4FDwqJUTeI_aGKmmkJdPDzRNccp96M
    // and https://www.aggdata.com/free/canada-postal-codes
    public function importZipsCanada()
    {
        $sql = "";
        $chk = SLZips::where('ZipCountry', 'Canada')
            ->first();
        if (!$chk) {
        	$this->importCsv('Zips', 'https://survloop.org/survlooporg/expansion-pack/Zips-Canada-01.csv');
        }
        return true;
    }
    
    public function printRowAddy($row, $abbr, $twoRows = false)
    {
        $ret = '';
        if ($row) {
            foreach (['Address', 'Address2', 'AddressCity', 'AddressState', 'AddressZip'] as $i => $fld) {
                if (isset($row->{ $abbr . $fld }) && trim($row->{ $abbr . $fld }) != '') {
                    $ret .= (($twoRows && $fld == 'AddressCity') ? '<br />' : '')
                        . trim($row->{ $abbr . $fld }) . (($fld == 'AddressCity') ? ', ' : ' ');
                }
            }
        }
        return $ret;
    }
    
}

?>