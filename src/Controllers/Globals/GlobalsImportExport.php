<?php
/**
  * GlobalsImportExport is a mid-level class for loading and accessing system information from anywhere.
  * This level contains processes which are uses during certain import and exports.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers\Globals;

use DB;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use MatthiasMullie\Minify;
use App\Models\SLDatabases;
use App\Models\SLTables;
use App\Models\SLFields;
use App\Models\SLTree;
use App\Models\SLDefinitions;
use App\Models\SLNode;
use App\Models\SLConditions;
use App\Models\SLConditionsNodes;
use App\Models\SLZips;
use App\Models\SLTokens;
use SurvLoop\Controllers\Globals\Globals;
use SurvLoop\Controllers\Globals\GlobalsTables;

class GlobalsImportExport extends GlobalsTables
{
    private $exprtProg = [];
    
    public function loadDBFromCache(Request $request = NULL)
    {
        $cacheFile = '/cache/db-load-' . $this->dbID . '-' . $this->treeID . '.php';
        if ((!$request || !$request->has('refresh')) && file_exists($cacheFile)) {
            $content = Storage::get($cacheFile);
            eval($content);
        } else {
            $cache = '// Auto-generated loading cache from /SurvLoop/Controllers/Globals.php' . "\n\n";
            
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

            $cache .= '$'.'this->allCoreTbls = [];' . "\n";
            $coresDone = [];
            $allCoreTbls = DB::table('SL_Tree')
                ->join('SL_Tables', 'SL_Tree.TreeCoreTable', '=', 'SL_Tables.TblID')
                ->where('SL_Tree.TreeDatabase', $this->dbID)
                ->where('SL_Tables.TblName', 'NOT LIKE', 'Visitors')
                ->select('SL_Tree.TreeSlug', 'SL_Tables.TblID', 'SL_Tables.TblEng')
                ->orderBy('SL_Tree.TreeID', 'asc')
                ->get();
            if ($allCoreTbls->isNotEmpty()) {
                foreach ($allCoreTbls as $tbl) {
                    if (!in_array($tbl->TblID, $coresDone)) {
                        $coresDone[] = $tbl->TblID;
                        $cache .= '$'.'this->allCoreTbls[] = ['
                            . ' "id" => ' . $tbl->TblID . ', '
                            . ' "name" => "' . $tbl->TblEng . '", '
                            . ' "slug" => "' . $tbl->TreeSlug . '" '
                        . '];' . "\n";
                    }
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
                            ? $tree->TreeOpts : 1) 
                    . ' ];' . "\n";
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
                        if ($this->treeRow->TreeOpts%Globals::TREEOPT_ADMIN > 0) {
                            $this->treeRow->TreeOpts *= 3;
                            $this->treeRow->save();
                        }
                    }
                }
                if ($this->treeRow->TreeOpts%Globals::TREEOPT_ADMIN == 0
                    || $this->treeRow->TreeOpts%Globals::TREEOPT_STAFF == 0
                    || $this->treeRow->TreeOpts%Globals::TREEOPT_PARTNER == 0
                    || $this->treeRow->TreeOpts%Globals::TREEOPT_VOLUNTEER == 0) {
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
            $xmlTreeSlug = '';
            if ($this->treeRow && isset($this->treeRow->TreeType)) {
                if ($this->treeRow->TreeType == 'Survey') {
                    $xmlTreeSlug = $this->treeRow->TreeSlug;
                } elseif ($this->treeRow->TreeType == 'Page' 
                    && $this->treeRow->TreeOpts%Globals::TREEOPT_SEARCH == 0
                    && $this->treeRow->TreeCoreTable > 0) {
                    $chk = SLTree::where('TreeType', 'Survey')
                        ->where('TreeCoreTable', $this->treeRow->TreeCoreTable)
                        ->orderBy('TreeID', 'asc')
                        ->first();
                    if ($chk && isset($chk->TreeSlug)) {
                        $xmlTreeSlug = trim($chk->TreeSlug);
                    }
                }
            }
            if ($xmlTreeSlug != '') {
                $xmlTree = SLTree::where('TreeSlug', $xmlTreeSlug)
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
            } elseif ($this->treeRow && isset($this->treeRow->TreeOpts) 
                && $this->treeRow->TreeOpts%Globals::TREEOPT_REPORT == 0) {
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
            $this->loadTestsAB();
            if (sizeof($this->condABs) > 0) {
                $cache2 .= '$'.'this->condABs = [];' . "\n";
                foreach ($this->condABs as $i => $ab) {
                    $cache2 .= '$'.'this->condABs[] = [ ' . $ab[0] . ', "' . $ab[1] . '" ];' . "\n";
                }
            }
            
            $this->x["srchUrls"] = [
                'public'        => '',
                'administrator' => '',
                'volunteer'     => '',
                'partner'       => '', 
                'staff'         => ''
            ];
            if ($this->treeRow && isset($this->treeRow->TreeDatabase)) {
                $searchTrees = SLTree::where('TreeDatabase', $this->treeRow->TreeDatabase)
                    ->where('TreeType', 'Page')
                    //->whereRaw("TreeOpts%" . Globals::TREEOPT_SEARCH . " = 0")
                    ->orderBy('TreeID', 'asc')
                    ->get();
                if ($searchTrees->isNotEmpty()) {
                    foreach ($searchTrees as $tree) {
                        if ($tree->TreeOpts%Globals::TREEOPT_SEARCH == 0) {
                            if ($tree->TreeOpts%Globals::TREEOPT_ADMIN == 0) {
                                $this->x["srchUrls"]["administrator"] = '/dash/' . $tree->TreeSlug;
                            } elseif ($tree->TreeOpts%Globals::TREEOPT_STAFF == 0) {
                                $this->x["srchUrls"]["staff"] = '/dash/' . $tree->TreeSlug;
                            } elseif ($tree->TreeOpts%Globals::TREEOPT_PARTNER == 0) {
                                $this->x["srchUrls"]["partner"] = '/dash/' . $tree->TreeSlug;
                            } elseif ($tree->TreeOpts%Globals::TREEOPT_VOLUNTEER == 0) {
                                $this->x["srchUrls"]["volunteer"] = '/dash/' . $tree->TreeSlug;
                            } else {
                                $this->x["srchUrls"]["public"] = '/' . $tree->TreeSlug;
                            }
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
            
            if (file_exists($cacheFile)) {
                Storage::delete($cacheFile);
            }
            Storage::put($cacheFile, $cache . $cache2);
        }
        return true;
    }
    
    public function getGenericRows($tbl, $fld = '', $val = '', $oper = '', $ordFld = '')
    {
        eval("\$rows = " . $this->modelPath($tbl) . "::" . ((trim($fld) != '') 
            ? "where('" . $fld . "'" . ((trim($oper) != '') ? ", '" . $oper . "'" : "") . ", '" . $val . "')->" : "")
            . ((in_array($oper, ['<>', 'NOT LIKE']) && strtolower($val) != 'null') 
                ? "orWhere('" . $fld . "', NULL)->" : "")
            . ((trim($ordFld) != '') ? "orderBy('" . $ordFld . "', 'asc')->" : "") 
            . ((isset($this->exprtProg["tok"]) && $this->exprtProg["tok"] 
                && intVal($this->exprtProg["tok"]->TokCoreID) > 0) 
                ? "offset(" . $this->exprtProg["tok"]->TokCoreID . ")->" : "")
            . "limit(50000)->get();");
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
            if (!file_exists($dir)) {
                mkdir($dir);
            }
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
                            echo '<html><body><br /><br /><center>' . $this->spinner() 
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
            echo '<html><body><br /><br /><center>' . $this->spinner() . '<br /></center>' . "\n"
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
            foreach ($flds as $fld => $type) {
                $ret .= ',' . $fld;
            }
            if (trim($ret) != '') {
                $ret = substr($ret, 1) . "\n";
            }
        }
        return $ret;
    }
    
    public function genCsvStore()
    {
        $filename = str_replace('.csv', '-' . $this->leadZero($this->exprtProg["fileCnt"]) . '.csv', 
            $this->exprtProg["fileName"]);
        if (file_exists($filename)) {
            unlink($filename);
        }
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
		    if (sizeof($content) == 0) {
		        $content = $this->mexplode("\n", file_get_contents($filename));
		    }
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
								foreach ($row as $j => $fld) {
								    $cols[$j] = $fld;
								}
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
    
    
    public function mysqlTblCoreStart($tbl)
    {
        return "CREATE TABLE IF NOT EXISTS `" . (($tbl->TblDatabase == 3) ? 'SL_' : $this->dbRow->DbPrefix) 
            . $tbl->TblName . "` ( `" . $tbl->TblAbbr . "ID` int(11) NOT NULL AUTO_INCREMENT, \n";
    }
    
    public function mysqlTblCoreFinish($tbl)
    {
        return "  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP , \n"
            . "  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP , \n"
            . "  PRIMARY KEY (`" . $tbl->TblAbbr . "ID`) )"
            . "  ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    }
    
    public function exportMysqlTbl($tbl, $installHereNow = false)
    {
        if (!isset($this->x["indexesEnd"])) {
            $this->x["indexesEnd"] = '';
        }
        if (strtolower($tbl->TblEng) == 'users') {
            return "";
        }
        
        $tblQuery = $this->mysqlTblCoreStart($tbl);
        $indexes = "";
        $flds = SLFields::where('FldTable', $tbl->TblID)
            ->orderBy('FldOrd', 'asc')
            ->orderBy('FldEng', 'asc')
            ->get();
        if (isset($tbl->TblExtend) && intVal($tbl->TblExtend) > 0) {
            $flds = $this->addFldRowExtends($flds, $tbl->TblExtend);
        }
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                $tblQuery .= "  `" . $tbl->TblAbbr . $fld->FldName . "` ";
                if ($fld->FldType == 'INT') {
                    if (intVal($fld->FldForeignTable) > 0 && isset($this->tbl[$fld->FldForeignTable])
                        && strtolower($this->tbl[$fld->FldForeignTable]) == 'users') {
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
                        $tblQuery .= "VARCHAR(" . (($fld->FldDataLength > 0) ? $fld->FldDataLength : 255) . ") ";
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
                    if (in_array($fld->FldDefault, ['NULL', 'NOW()'])) {
                        $tblQuery .= "DEFAULT " . $fld->FldDefault . " ";
                    } else {
                        $tblQuery .= "DEFAULT '" . $fld->FldDefault . "' ";
                    }
                }
                $tblQuery .= ", \n";
                if ($fld->FldIsIndex && intVal($fld->FldIsIndex) == 1) {
                    $indexes .= "  , KEY `" . $tbl->TblAbbr . $fld->FldName . "` "
                        . "(`" . $tbl->TblAbbr . $fld->FldName . "`) \n";
                }
                if (intVal($fld->FldForeignTable) > 0) {
                    list($forTbl, $forID) = $this->chkForeignKey($fld->FldForeignTable);
                    $this->x["indexesEnd"] .= "ALTER TABLE `" 
                        . $this->dbRow->DbPrefix . $tbl->TblName 
                        . "` ADD FOREIGN KEY (`" . $tbl->TblAbbr . $fld->FldName . "`) "
                        . "REFERENCES `" . $forTbl . "` (`" . $forID . "`); \n";
                }
            }
            $tblQuery .= $this->mysqlTblCoreFinish($tbl);
        }
        return $tblQuery;
    }
    
    public function tblQrySlExports()
    {
        return SLTables::where('TblDatabase', 3)
            ->whereIn('TblName', ['BusRules', 'Conditions', 'ConditionsArticles', 'ConditionsNodes', 'ConditionsVals', 
                'Databases', 'DataHelpers', 'DataLinks', 'DataLoop', 'DataSubsets', 'Definitions', 'Emails', 'Fields', 
                'Images', 'Node', 'NodeResponses', 'Tables', 'Tree'])
            ->orderBy('TblOrd', 'asc')
            ->get();
    }
    
    public function loadSlParents($dbIN = -3)
    {
        $dbID = $this->dbID;
        if ($dbIN > 0) {
            $dbID = $dbIN;
        }
        $this->x["slTrees"] = $this->x["slNodes"] = $this->x["slConds"] = [];
        $chk = SLTree::where('TreeDatabase', $dbID)
            ->select('TreeID')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $tree) {
                $this->x["slTrees"][] = $tree->TreeID;
            }
        }
        $chk = SLNode::whereIn('NodeTree', $this->x["slTrees"])
            ->select('NodeID')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $node) {
                $this->x["slNodes"][] = $node->NodeID;
            }
        }
        $chk = SLConditions::where('CondDatabase', $dbID)
            ->select('CondID')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $cond) {
                $this->x["slConds"][] = $cond->CondID;
            }
        }
        return true;
    }
    
    public function chkTableSeedCnt($tblClean = '', $eval = '')
    {
        $seedCnt = 0;
        if (trim($tblClean) != '' && file_exists('../app/Models/' . $tblClean . '.php')) {
            eval("\$seedCnt = App\\Models\\" . $tblClean . "::" . $eval . "count();");
        }
        return (($seedCnt && intVal($seedCnt) > 0) ? intVal($seedCnt) : 0);
    }
    
    public function chkTableSeedLimits($tblClean = '', $eval = '', $limit = 10000)
    {
        $seedCnt = $this->chkTableSeedCnt($tblClean, $eval);
        return ($limit < intVal($seedCnt));
    }
    
    public function getTableSeedDump($tblClean = '', $eval = '', $limit = 10000, $start = 0)
    {
        $seedChk = [];
        if (trim($tblClean) != '' && file_exists('../app/Models/' . $tblClean . '.php')) {
            eval("\$seedChk = App\\Models\\" . $tblClean . "::" . $eval . "orderBy('created_at', 'asc')->get();");
        }
        return $seedChk;
    }
    
    public function getTableSeedDumpLimit($tblClean = '', $eval = '', $limit = 10000, $start = 0)
    {
        return [
            $this->chkTableSeedCnt($tblClean, $eval),
            $this->getTableSeedDump($tblClean, $eval, $limit, $start)
        ];
    }
    
    public function loadSlSeedEval($tbl = [], $dbIN = -3)
    {
        $dbID = $this->dbID;
        if ($dbIN > 0) {
            $dbID = $dbIN;
        }
        if (!isset($this->x["slTrees"])) {
            $this->loadSlParents($dbID);
        }
        $eval = "";
        if (isset($tbl->TblName)) {
            if ($tbl->TblName == 'Databases') {
                $eval = "where('DbID', " . $dbID . ")->";
            } elseif (in_array($tbl->TblName, ['Images'])) {
                $eval = "where('" . $tbl->TblAbbr . "DatabaseID', " . $dbID . ")->";
            } elseif (in_array($tbl->TblName, ['BusRules', 'Conditions', 'Definitions', 'Fields', 'Tables', 'Tree'])) {
                $eval = "where('" . $tbl->TblAbbr . "Database', " . $dbID . ")->";
                if ($tbl->TblName == 'Definitions') {
                    $eval = "whereNotIn('DefSubset', ['google-analytic', 'google-cod-key', 'google-cod-key2', 'google-map-key', 'google-map-key2', 'google-maps-key', 'google-maps-key2'])->";
                }
            } elseif (in_array($tbl->TblName, ['Node', 'DataHelpers', 'DataLinks', 'DataLoop', 'DataSubsets', 
                'Emails'])) {
                $eval = "whereIn('" . $tbl->TblAbbr . "Tree', [" . implode(", ", $this->x["slTrees"]) . "])->";
            } elseif (in_array($tbl->TblName, ['NodeResponses'])) {
                $eval = "whereIn('" . $tbl->TblAbbr . "Node', [" . implode(", ", $this->x["slNodes"]) . "])->";
            } elseif (in_array($tbl->TblName, ['ConditionsArticles', 'ConditionsNodes', 'ConditionsVals'])) {
                $eval = "whereIn('" . $tbl->TblAbbr . "CondID', [" . implode(", ", $this->x["slConds"]) ."])->";
            }
        }
        return $eval;
    }
    
    public function exportMysqlSl()
    {
        $this->loadSlParents();
        //$this->tmpDbSwitch();
        if (!isset($this->x["export"])) {
            $this->x["export"] = '';
        }
        $tbls = $this->tblQrySlExports();
        if ($tbls->isNotEmpty()) {
            foreach ($tbls as $i => $tbl) {
                $this->x["tbl"] = $tbl;
                $this->x["tblName"] = 'SL_' . $tbl->TblName;
                $this->x["tblClean"] = str_replace('_', '', $this->x["tblName"]);
                $this->x["export"] .= "\nDROP TABLE IF EXISTS `" . $this->x["tblName"] . "`;\n" 
                    . $this->exportMysqlTbl($tbl);
                $flds = $this->getTableFields($tbl);
                if ($flds->isNotEmpty()) {
                    $seedChk = $this->getTableSeedDump($this->x["tblClean"], $this->loadSlSeedEval($tbl));
                    if ($seedChk->isNotEmpty()) {
                        $this->x["tblInsertStart"] = "\nINSERT INTO `" . $this->x["tblName"] . "` (`" 
                            . $tbl->TblAbbr . "ID`";
                        foreach ($flds as $i => $fld) {
                            $this->x["tblInsertStart"] .= ", `" . $tbl->TblAbbr . $fld->FldName . "`";
                        }
                        $this->x["tblInsertStart"] .= ", `created_at`, `updated_at`) VALUES \n";
                        $this->x["export"] .= $this->x["tblInsertStart"];
                        foreach ($seedChk as $ind => $seed) {
                            if ($ind%5000 == 0 && $ind > 0) {
                                $this->x["export"] .= ";\n" . $this->x["tblInsertStart"];
                            } elseif ($ind > 0) {
                                $this->x["export"] .= ",\n";
                            }
                            $this->x["export"] .= "(" . $seed->getKey();
                            foreach ($flds as $fld) {
                                if (isset($seed->{ $tbl->TblAbbr . $fld->FldName })) {
                                    $this->x["export"] .= ", '" . str_replace("'", "\'", 
                                        $seed->{ $tbl->TblAbbr . $fld->FldName }) . "'";
                                } elseif ($fld->FldNullSupport && intVal($fld->FldNullSupport) == 1) {
                                    $this->x["export"] .= ", NULL";
                                } else {
                                    $this->x["export"] .= ", ''";
                                }
                            }
                            $this->x["export"] .= ", '" . $seed->created_at . "', '" . $seed->updated_at . "')";
                        }
                        $this->x["export"] .= "; \n";
                    }
                }
            }
        }
        while (strpos($this->x["export"], ") VALUES \n,\n") !== false) {
            $this->x["export"] = str_replace(") VALUES \n,\n", ") VALUES \n", $this->x["export"]);
        }
        //$this->tmpDbSwitchBack();
        return true;
    }
    
    public function createTableIfNotExists($coreTbl, $userTbl = null)
    {
        $this->modelPath($coreTbl->TblName, true);
        if (!$this->chkTableExists($coreTbl, $userTbl)) {
            $tblQuery = $this->exportMysqlTbl($coreTbl, true);
            $chk = DB::select( DB::raw( $tblQuery ) );
            return false;
        }
        return true;
    }
    
    public function getPackageLineCount($dir = 'Controllers', $pkg = '', $type = '.php')
    {
        if ($pkg == '') {
            $pkg = $this->sysOpts["cust-package"];
        }
        return $this->getDirLinesCount('../vendor/' . $pkg . '/src/' . $dir, $type);
    }
    
    public function getPackageByteCount($dir = 'Controllers', $pkg = '', $type = '')
    {
        if ($pkg == '') {
            $pkg = $this->sysOpts["cust-package"];
        }
        return $this->getDirSize('../vendor/' . $pkg . '/src/' . $dir, $type);
    }
    
    public function getJsonSurvStats($pkg = '')
    {
    	$types = $this->loadTreeNodeStatTypes();
    	$stats = [ "Date" => date("Y-m-d"), "IconUrl" => $this->sysOpts["app-url"] . $this->sysOpts["shortcut-icon"] ];
    	$survs = $pages = [];
    	$stats["DbTables"] = SLTables::where('TblDatabase', $this->dbID)
    	   ->count();
    	$stats["DbFields"] = SLFields::where('FldDatabase', $this->dbID)
    	   ->where('FldTable', '>', 0)
    	   ->count();
    	$stats["DbLinks"] = SLFields::where('FldDatabase', $this->dbID)
    	   ->where('FldForeignTable', '>', 0)
    	   ->where('FldTable', '>', 0)
    	   ->count();
    	$chk = SLTree::where('TreeType', 'Survey')
    		->where('TreeDatabase', $this->dbID)
    		->select('TreeID')
    		->get();
    	if ($chk->isNotEmpty()) {
    		foreach ($chk as $t) {
    		    $survs[] = $t->TreeID;
    		}
    	}
    	$stats["Surveys"] = sizeof($survs);
    	$stats["SurveyNodes"] = SLNode::whereIn('NodeTree', $survs)->count();
    	$stats["SurveyNodesMult"] = SLNode::whereIn('NodeTree', $survs)->whereIn('NodeType', $types["choic"])->count();
    	$stats["SurveyNodesOpen"] = SLNode::whereIn('NodeTree', $survs)->whereIn('NodeType', $types["quali"])->count();
    	$stats["SurveyNodesNumb"] = SLNode::whereIn('NodeTree', $survs)->whereIn('NodeType', $types["quant"])->count();
    	$chk = SLTree::where('TreeType', 'Page')
    		->where('TreeDatabase', $this->dbID)
    		->select('TreeID')
    		->get();
    	if ($chk->isNotEmpty()) {
    		foreach ($chk as $t) {
    		    $pages[] = $t->TreeID;
    		}
    	}
    	$stats["Pages"] = sizeof($pages);
    	$stats["PageNodes"] = SLNode::whereIn('NodeTree', $pages)->count();
    	$stats["CodeLinesControllers"] = $this->getPackageLineCount('Controllers', $pkg);
    	$stats["CodeLinesViews"] = $this->getPackageLineCount('Views', $pkg);
    	$stats["BytesControllers"] = $this->getPackageByteCount('Controllers', $pkg);
    	$stats["BytesDatabase"] = $this->getPackageByteCount('Database', $pkg);
    	$stats["BytesUploads"] = $this->getPackageByteCount('Uploads', $pkg);
    	$stats["BytesViews"] = $this->getPackageByteCount('Views', $pkg);
    	$stats["Users"] = User::select('id')->count();
    	return $stats;
    }
    
    public function genPageDynamicJs($content = '')
    {
        $fileCss = '/cache/dynascript/' . date("Ymd") . '-t' . $this->treeID;
        if ($this->treeRow->TreeType != 'Page' || $this->treeRow->TreeOpts%Globals::TREEOPT_NOCACHE == 0) {
            $fileCss .= '-s' . session()->get('slSessID');
        }
        $fileCss .= '-r' . rand(10000000, 100000000) . '.css';
        $sffx = (($this->REQ->has('refresh')) ? '?refresh=' . rand(10000000, 100000000) : '');
        $content = $this->extractStyle($content, 0);
        if (trim($this->pageCSS) != '' && trim($this->pageCSS) != '/* */') {
            Storage::put($fileCss, $this->pageCSS);
            $fileMin = str_replace('.css', '-min.css', $fileCss);
            $minifier = new Minify\CSS('../storage/app' . $fileCss);
            $minifier->minify('../storage/app' . $fileMin);
            Storage::delete($fileCss);
            $this->pageSCRIPTS .= '<style id="dynCss"> ' . file_get_contents('../storage/app' . $fileMin) . ' </style>';
            //$this->pageSCRIPTS .= '<link id="dynCss" rel="stylesheet" href="' . $this->sysOpts["app-url"]
            //    . str_replace('/cache/dynascript/', '/dyna-', $fileMin) . $sffx . '">' . "\n";
        }
        
        $fileJs = str_replace('.css', '.js', $fileCss);
        $content = $this->extractJava($content, 0);
        $java = $this->pageJAVA . $GLOBALS["SL"]->getXtraJs();
        if (trim($this->pageAJAX) != '' && trim($this->pageAJAX) != '/* */') {
            $java .= ' $(document).ready(function(){ ' . $this->pageAJAX . ' }); ';
        }
        if (trim($java) != '' && trim($java) != '/* */') {
            Storage::put($fileJs, $java);
            $fileMin = str_replace('.js', '-min.js', $fileJs);
            $minifier = new Minify\JS('../storage/app' . $fileJs);
            if (file_exists($fileMin)) {
                Storage::delete($fileMin);
            }
            $minifier->minify('../storage/app' . $fileMin);
            Storage::delete($fileJs);
            $this->pageSCRIPTS .= "\n" . '<script id="dynJs" type="text/javascript" src="' 
                . $this->sysOpts["app-url"] . str_replace('/cache/dynascript/', '/dyna-', $fileMin) . $sffx 
                . '"></script>'; // defer
        }
        $this->pageCSS = $this->pageJAVA = $this->pageAJAX = '';
        return $content;
    }
    
    public function clearOldDynascript($minAge = 0)
    {
        if ($minAge <= 0 || $minAge >= mktime(0, 0, 0, date("n"), date("j")+1, date("Y"))) {
            $minAge = mktime(0, 0, 0, date("n"), date("j")-3, date("Y"));
        }
        $safeDates = [];
        for ($i = mktime(0, 0, 0, date("n"), date("j")+1, date("Y")); $i >= $minAge; $i -= (60*60*24)) {
            $safeDates[] = date("Ymd", $i);
        }
        $cnt = 0;
        $files = $this->mapDirFilesSlim('../storage/app/cache/dynascript', false);
        if (sizeof($files) > 0) {
            foreach ($files as $i => $file) {
                $cnt++;
                if ($cnt < 100) {
                    $delete = false;
                    if ($this->getFileExt($file) != 'html') {
                        $delete = true;
                        $filenameParts = $GLOBALS["SL"]->mexplode('-', $file);
                        if (isset($filenameParts[0]) && in_array($filenameParts[0], $safeDates)) {
                            $delete = false;
                        }
                    }
                    if ($delete) {
                        unlink('../storage/app/cache/dynascript/' . $file);
                    }
                }
            }
        }
        return true;
    }
    
    public function deferStaticNodePrint($nID, $content = '', $js = '', $ajax = '', $css = '')
    {
        if (!isset($this->x["deferCnt"])) {
            $this->x["deferCnt"] = 0;
        }
        $this->x["deferCnt"]++;
        $file = '/cache/dynascript/t' . $this->treeID . 'n' . $nID . '.html';
        if (trim($js) != '' || trim($ajax) != '') {
            $content .= '<script type="text/javascript"> ' . $js . ' ';
            if (trim($ajax) != '') {
                $content .= '$(document).ready(function(){ ' . $ajax . ' }); ';
            }
            $content .= '</script>';
        }
        if (trim($css) != '') {
            $content .= '<style> ' . $css . ' </style>';
        }
        Storage::put($file, $content);
        $this->pageAJAX .= 'setTimeout(function() { $("#deferNode' . $nID . '").load("/defer/' 
            . $this->treeID . '/' . $nID . '"); }, ' . (500+(500*$this->x["deferCnt"])) . '); ';
        return '<div id="deferNode' . $nID . '" class="w100 ovrSho"><center><div id="deferAnim' . $nID 
            . '" class="p20 m20">' . $this->spinner() . '</div></center></div>';
    }
    
    public function spinner($center = true)
    {
        $ret = ((isset($this->sysOpts["spinner-code"])) ? $this->sysOpts["spinner-code"] : '<b>...</b>');
        if ($center) {
            return '<div class="w100 pT20 pB20"><center>' . $ret . '</center></div>';
        }
        return $ret;
    }

    public function isPrintView()
    {
        return ((isset($this->x["isPrintPDF"]) && $this->x["isPrintPDF"])
            || (isset($this->x["pageView"]) && in_array($this->x["pageView"], ['pdf', 'full-pdf']))
            || ($this->REQ->has('print') && intVal($this->REQ->get('print')) > 0)
            || ($this->REQ->has('pdf') && intVal($this->REQ->get('pdf')) > 0));
    }
    
}