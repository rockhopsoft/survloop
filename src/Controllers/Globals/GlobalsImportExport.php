<?php
/**
  * GlobalsImportExport is a mid-level class for loading and accessing system information from anywhere.
  * This level contains processes which are uses during certain import and exports.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Globals;

use DB;
use Cache;
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

class GlobalsImportExport extends GlobalsTables
{
    private $exprtProg = [];
    
    public function loadDBFromCache(Request $request = NULL)
    {
        $cacheFile = '/cache/php/db-load-' 
            . $this->dbID . '-' . $this->treeID . '.php';
        if ((!$request || !$request->has('refresh')) && file_exists($cacheFile)) {
            $content = Storage::get($cacheFile);
            eval($content);
        } else {
            $cache = '// Auto-generated loading cache from '
                . '/Survloop/Controllers/Globals.php' . "\n\n";
            
            $cache .= '$'.'this->allDbs = [];' . "\n";
            $allDbs = SLDatabases::get();
            if ($allDbs->isNotEmpty()) {
                foreach ($allDbs as $db) {
                    $cache .= '$'.'this->allDbs[] = ['
                        . ' "id" => ' . $db->db_id . ', '
                        . ' "name" => "' . str_replace('"', '\\"', $db->db_name) . '", '
                        . ' "prfx" => "' . $db->db_prefix . '" '
                    . '];' . "\n";
                }
            }

            $cache .= '$'.'this->allCoreTbls = [];' . "\n";
            $cache .= '$'.'this->pubCoreTbls = [];' . "\n";
            $coresDone = [];
            $allCoreTbls = DB::table('sl_tree')
                ->join('sl_tables', 'sl_tree.tree_core_table', '=', 'sl_tables.tbl_id')
                ->where('sl_tree.tree_database', $this->dbID)
                ->where('sl_tables.tbl_name', 'NOT LIKE', 'Visitors')
                ->select('sl_tree.tree_slug', 'sl_tree.tree_opts', 
                    'sl_tables.tbl_id', 'sl_tables.tbl_eng')
                ->orderBy('sl_tree.tree_id', 'asc')
                ->get();
            if ($allCoreTbls->isNotEmpty()) {
                foreach ($allCoreTbls as $tbl) {
                    if (!in_array($tbl->tbl_id, $coresDone)) {
                        $coresDone[] = $tbl->tbl_id;
                        $cache .= '$'.'this->allCoreTbls[] = ['
                            . ' "id" => ' . $tbl->tbl_id . ', '
                            . ' "name" => "' . $tbl->tbl_eng . '", '
                            . ' "slug" => "' . $tbl->tree_slug . '" '
                        . '];' . "\n";
                        if ($tbl->tree_opts%Globals::TREEOPT_ADMIN > 0
                            && $tbl->tree_opts%Globals::TREEOPT_STAFF > 0
                            && $tbl->tree_opts%Globals::TREEOPT_PARTNER > 0) {
                            $cache .= '$'.'this->pubCoreTbls[] = ['
                                . ' "id" => ' . $tbl->tbl_id . ', '
                                . ' "name" => "' . $tbl->tbl_eng . '", '
                                . ' "slug" => "' . $tbl->tree_slug . '" '
                            . '];' . "\n";
                        }
                    }
                }
            }
            
            $this->allTrees = [];
            $cache .= '$'.'this->allTrees = [];' . "\n";
            $allTrees = SLTree::where('tree_type', 'Survey')
                ->orderBy('tree_name', 'asc')
                ->get();
            if ($allTrees->isNotEmpty()) {
                foreach ($allTrees as $tree) {
                    if (!isset($this->allTrees[$tree->tree_database])) {
                        $this->allTrees[$tree->tree_database] = [];
                        $cache .= '$'.'this->allTrees[' . $tree->tree_database . '] = [];' . "\n";
                    }
                    $cache .= '$'.'this->allTrees[' . $tree->tree_database . '][] = ['
                        . ' "id" => ' . $tree->tree_id . ', '
                        . ' "name" => "' . str_replace('"', '\\"', $tree->tree_name) . '", '
                        . ' "slug" => "' . $tree->tree_slug . '", '
                        . ' "opts" => ' . ((isset($tree->tree_opts) && intVal($tree->tree_opts) > 0) 
                            ? $tree->tree_opts : 1) 
                    . ' ];' . "\n";
                }
            }
            
            if ($this->treeRow && isset($this->treeRow->tree_root)) {
                if ($this->treeRow->tree_root > 0) {
                    $chk = SLConditionsNodes::select('sl_conditions_nodes.cond_node_id')
                        ->join('sl_conditions', 'sl_conditions.cond_id', 
                            '=', 'sl_conditions_nodes.cond_node_cond_id')
                        ->where('sl_conditions.cond_tag', '#IsAdmin')
                        ->where('sl_conditions_nodes.cond_node_node_id', $this->treeRow->tree_root)
                        ->first();
                    if ($chk && isset($chk->cond_node_id)) {
                        if ($this->treeRow->tree_opts%Globals::TREEOPT_ADMIN > 0) {
                            $this->treeRow->tree_opts *= 3;
                            $this->treeRow->save();
                        }
                    }
                }
                if ($this->treeRow->tree_opts%Globals::TREEOPT_ADMIN == 0
                    || $this->treeRow->tree_opts%Globals::TREEOPT_STAFF == 0
                    || $this->treeRow->tree_opts%Globals::TREEOPT_PARTNER == 0
                    || $this->treeRow->tree_opts%Globals::TREEOPT_VOLUNTEER == 0) {
                    $cache .= '$'.'this->treeIsAdmin = true;' . "\n"
                        . '$'.'this->treeBaseSlug = "/dash/' 
                        . $this->treeRow->tree_slug . '/";' . "\n";
                } else {
                    $cache .= '$'.'this->treeBaseSlug = "/u/' 
                        . $this->treeRow->tree_slug . '/";' . "\n";
                }
            }

            //$sys = SLDefinitions::where('def_database', $this->dbID)
            //    ->where('def_set', 'System Settings')
            //    ->get(); 
            //if (!$sys || sizeof($sys) == 0) {
                $sys = SLDefinitions::where('def_database', 1)
                    ->where('def_set', 'System Settings')
                    ->get();
            //}
            if ($sys->isNotEmpty()) {
                foreach ($sys as $s) {
                    $cache .= '$'.'this->sysOpts[\'' . $s->def_subset . '\'] = \''
                        . str_replace("'", "\\'", trim($s->def_description)) . '\';' . "\n";
                }
            }
            if (isset($this->dbRow->db_prefix)) {
                $coreTbl = '';
                // Establishing database table-field lookup arrays
                $tbls = SLTables::where('tbl_database', $this->dbID)
                    ->orderBy('tbl_ord', 'asc')
                    ->get();
                foreach ($tbls as $tbl) {
                    if (isset($this->treeRow->tree_core_table) 
                        && $tbl->tbl_id == $this->treeRow->tree_core_table) {
                        $coreTbl = $tbl->tbl_name;
                        $cache .= '$'.'this->coreTbl = \'' . $coreTbl . '\';' . "\n";
                    }
                    $cache .= '$'.'this->tbls[] = ' . $tbl->tbl_id . ';' . "\n"
                        . '$'.'this->tblI[\'' . $tbl->tbl_name 
                            . '\'] = ' . intVal($tbl->tbl_id) . ';' . "\n"
                        . '$'.'this->tbl[' . $tbl->tbl_id 
                            . '] = \'' . $tbl->tbl_name . '\';' . "\n"
                        . '$'.'this->tblEng[' . $tbl->tbl_id 
                            . '] = \'' . str_replace("'", "\\'", $tbl->tbl_eng).'\';'."\n"
                        . '$'.'this->tblOpts[' . $tbl->tbl_id 
                            . '] = ' . intVal($tbl->tbl_opts) . ';' . "\n"
                        . '$'.'this->tblAbbr[\'' . $tbl->tbl_name 
                            . '\'] = \'' . $tbl->tbl_abbr . '\';' . "\n"
                        . '$'.'this->fldTypes[\'' . $tbl->tbl_name . '\'] = [];' . "\n"
                        . '$'.'this->fldTypes[\'' . $tbl->tbl_name 
                            . '\'][\'' . $tbl->tbl_abbr . 'id\'] = \'INT\';' . "\n"
                        . '$'.'this->tblModels[\'' . $tbl->tbl_name . '\'] = \'' 
                            . $this->strFullTblModel($tbl->tbl_name) . '\';' . "\n";
                    if ($this->treeRow 
                        && isset($this->treeRow->tree_core_table) 
                        && $tbl->tbl_id == $this->treeRow->tree_core_table) {
                    	$coreType = '$'.'this->fldTypes[\'' . $tbl->tbl_name 
                            . '\'][\'' . $tbl->tbl_abbr;
                        $cache .= $coreType . 'user_id\'] = \'INT\';' . "\n"
                        	. $coreType . 'submission_progress\'] = \'INT\';' . "\n"
                        	. $coreType . 'tree_version\'] = \'VARCHAR\';' . "\n"
                        	. $coreType . 'version_ab\'] = \'VARCHAR\';' . "\n"
                        	. $coreType . 'unique_str\'] = \'VARCHAR\';' . "\n"
                        	. $coreType . 'ip_addy\'] = \'VARCHAR\';' . "\n"
                        	. $coreType . 'is_mobile\'] = \'INT\';' . "\n";
                    }
                    // temporarily loading for the sake of cache creation...
                    $this->tbl[$tbl->tbl_id] = $tbl->tbl_name;
                    $this->tblAbbr[$tbl->tbl_name] = $tbl->tbl_abbr;
                }
                $cache .= '$'.'this->coreTbl = \'' . $coreTbl . '\';' . "\n";
                
                $fldNames = [];
                $flds = SLFields::select()
                    ->where('fld_database', $this->dbID)
                    ->where('fld_table', '>', 0)
                    ->orderBy('fld_ord', 'asc')
                    ->get();
                foreach ($flds as $fld) {
                    if (isset($this->tbl[$fld->fld_table])) {
                        $fldName = $this->tblAbbr[$this->tbl[$fld->fld_table]] 
                            . $fld->fld_name;
                        $fldNames[] = $fldName;
                        $cache .= '$'.'this->fldTypes[\'' . $this->tbl[$fld->fld_table] 
                            . '\'][\'' . $fldName . '\'] = \'' . $fld->fld_type 
                            . '\';' . "\n";
                        if (strtolower(substr($fldName, strlen($fldName)-6)) == '_other') {
                            $othFld = substr($fldName, 0, strlen($fldName)-6);
                            if (trim($othFld) != '' && in_array($othFld, $fldNames)) {
                                $cache .= '$'.'this->fldOthers[\'' . $fldName . '\'] = '
                                    . $fld->fld_id . ';' . "\n";
                            }
                        }
                    }
                }
                
                $cache .= $this->loadDataMapLinks($this->treeID);
                
                $cache .= '$'.'this->nodeCondInvert = [];' . "\n";
                $inv = SLConditionsNodes::where('cond_node_node_id', '>', 0)
                    ->where('cond_node_loop_id', '<', 0)
                    ->get();
                if ($inv->isNotEmpty()) {
                    foreach ($inv as $invert) {
                        $invNode = $invert["cond_node_node_id"];
                        $invCond = $invert["cond_node_cond_id"];
                        if (!isset($this->nodeCondInvert[$invNode])) {
                            $cache .= '$'.'this->nodeCondInvert[' . $invNode . '] = [];' . "\n";
                            $this->nodeCondInvert[$invNode] = [];
                        }
                        if (!isset($this->nodeCondInvert[$invNode][$invCond])) {
                            $cache .= '$'.'this->nodeCondInvert[' . $invNode 
                                . '][' . $invCond . '] = true;' . "\n";
                            $this->nodeCondInvert[$invNode][$invCond] = true;
                        }
                    }
                }
            } // end if (isset($this->dbRow->db_prefix))

            eval($cache);
            
            $cache2 = '';
            $extends = SLTables::where('tbl_database', $this->dbID)
                ->where('tbl_extend', '>', 0)
                ->select('tbl_id', 'tbl_abbr', 'tbl_extend')
                ->get();
            if ($extends->isNotEmpty()) {
                foreach ($extends as $tbl) {
                    if (isset($this->tbl[$tbl->tbl_id]) 
                        && isset($this->fldTypes[$this->tbl[$tbl->tbl_extend]])
                        && is_array($this->fldTypes[$this->tbl[$tbl->tbl_extend]])
                        && sizeof($this->fldTypes[$this->tbl[$tbl->tbl_extend]]) > 0) {
                        $cache2 .= '$'.'this->fldTypes[\'' . $this->tbl[$tbl->tbl_id] . '\'][\'' 
                            . $tbl->tbl_abbr . $this->tblAbbr[$this->tbl[$tbl->tbl_extend]] 
                            . 'ID\'] = \'INT\';' . "\n";
                        foreach ($this->fldTypes[$this->tbl[$tbl->tbl_extend]] as $fldName => $fldType) {
                            $fldName2 = $this->tblAbbr[$this->tbl[$tbl->tbl_id]] . $fldName;
                            $cache2 .= '$'.'this->fldTypes[\'' . $this->tbl[$tbl->tbl_id] 
                                . '\'][\'' . $fldName2 . '\'] = \'' . $fldType . '\';' . "\n";
                            $fldNames[] = $fldName2;
                        }
                    }
                }
            }
            
            $this->getCoreTblUserFld();
            $cache2 .= '$'.'this->coreTblUserFld = \'' . $this->coreTblUserFld . '\';' . "\n";
            $xmlTreeSlug = '';
            if ($this->treeRow && isset($this->treeRow->tree_type)) {
                if ($this->treeRow->tree_type == 'Survey') {
                    $xmlTreeSlug = $this->treeRow->tree_slug;
                } elseif ($this->treeRow->tree_type == 'Page' 
                    && $this->treeRow->tree_opts%Globals::TREEOPT_SEARCH == 0
                    && $this->treeRow->tree_core_table > 0) {
                    $chk = SLTree::where('tree_type', 'Survey')
                        ->where('tree_core_table', $this->treeRow->tree_core_table)
                        ->orderBy('tree_id', 'asc')
                        ->first();
                    if ($chk && isset($chk->tree_slug)) {
                        $xmlTreeSlug = trim($chk->tree_slug);
                    }
                }
            }
            if ($xmlTreeSlug != '') {
                $xmlTree = SLTree::where('tree_slug', $xmlTreeSlug)
                    ->where('tree_database', $this->treeRow->tree_database)
                    ->where('tree_type', 'Survey XML')
                    ->orderBy('tree_id', 'asc')
                    ->first();
                if ($xmlTree && isset($xmlTree->tree_id)) {
                    if (!isset($xmlTree->tree_root) 
                        || intVal($xmlTree->tree_root) <= 0) {
                        if (intVal($xmlTree->tree_core_table) > 0) {
                            $xmlRootNode = new SLNode;
                            $xmlRootNode->node_tree         = $xmlTree->tree_id;
                            $xmlRootNode->node_parent_id    = -3;
                            $xmlRootNode->node_type         = 'XML';
                            $xmlRootNode->node_prompt_text  
                                = $this->tbl[$xmlTree->tree_core_table];
                            $xmlRootNode->node_prompt_notes = $xmlTree->tree_core_table;
                            $xmlRootNode->save();
                            $xmlTree->tree_root = $xmlRootNode->node_id;
                            $xmlTree->save();
                        }
                    }
                    $cache2 .= '$'.'this->xmlTree = [ '
                        . '"id" => ' . $xmlTree->tree_id . ', '
                        . '"root" => ' . ((intVal($xmlTree->tree_root) > 0) 
                            ? $xmlTree->tree_root : 0) . ', '
                        . '"coreTblID" => ' . ((intVal($xmlTree->tree_core_table) > 0) 
                            ? $xmlTree->tree_core_table : 0) . ', '
                        . '"coreTbl" => "' . ((isset($this->tbl[$xmlTree->tree_core_table])) 
                            ? $this->tbl[$xmlTree->tree_core_table] : '') . '", '
                        . '"slug" => "' . ((isset($xmlTree->tree_slug)) 
                            ? $xmlTree->tree_slug : '') . '", '
                        . '"opts" => ' . ((isset($xmlTree->tree_opts) 
                            && intVal($xmlTree->tree_opts) > 0) ? $xmlTree->tree_opts : 0)
                    . ' ];' . "\n";
                }
                $reportTree = $this->chkReportTree();
                if ($reportTree) {
                    $cache2 .= '$'.'this->reportTree = [ '
                        . '"id" => '    . $reportTree->tree_id . ', '
                        . '"root" => '  . $reportTree->tree_root . ', '
                        . '"slug" => "' . $reportTree->tree_slug . '", '
                        . '"opts" => '  . $reportTree->tree_opts . ''
                    . ' ];' . "\n";
                }
            } elseif ($this->treeRow && isset($this->treeRow->tree_opts) 
                && $this->treeRow->tree_opts%Globals::TREEOPT_REPORT == 0) {
                $reportTree = SLTree::where('tree_type', 'Survey')
                    ->where('tree_database', $this->dbID)
                    ->where('tree_core_table', $this->treeRow->tree_core_table)
                    ->get();
                if ($reportTree->isNotEmpty()) {
                    foreach ($reportTree as $t) {
                        if ($t->tree_opts%13 > 0) {
                            $foundRepTree = true;
                            $cache2 .= '$'.'this->reportTree = [ '
                                . '"id" => '    . $t->tree_id . ', '
                                . '"root" => '  . $t->tree_root . ', '
                                . '"slug" => "' . $t->tree_slug . '", '
                                . '"opts" => '  . $t->tree_opts . ''
                                . ' ];' . "\n";
                        }
                    }
                }
            }
            $this->loadTestsAB();
            if (sizeof($this->condABs) > 0) {
                $cache2 .= '$'.'this->condABs = [];' . "\n";
                foreach ($this->condABs as $i => $ab) {
                    $cache2 .= '$'.'this->condABs[] = [ ' 
                        . $ab[0] . ', "' . $ab[1] . '" ];' . "\n";
                }
            }
            
            $this->x["srchUrls"] = [
                'public'        => '',
                'administrator' => '',
                'volunteer'     => '',
                'partner'       => '', 
                'staff'         => ''
            ];
            if ($this->treeRow && isset($this->treeRow->tree_database)) {
                $searchTrees = SLTree::where('tree_database', $this->treeRow->tree_database)
                    ->where('tree_type', 'Page')
                    //->whereRaw("tree_opts%" . Globals::TREEOPT_SEARCH . " = 0")
                    ->orderBy('tree_id', 'asc')
                    ->get();
                if ($searchTrees->isNotEmpty()) {
                    foreach ($searchTrees as $tree) {
                        if ($tree->tree_opts%Globals::TREEOPT_SEARCH == 0) {
                            if ($tree->tree_opts%Globals::TREEOPT_ADMIN == 0) {
                                $this->x["srchUrls"]["administrator"] = '/dash/' . $tree->tree_slug;
                            } elseif ($tree->tree_opts%Globals::TREEOPT_STAFF == 0) {
                                $this->x["srchUrls"]["staff"] = '/dash/' . $tree->tree_slug;
                            } elseif ($tree->tree_opts%Globals::TREEOPT_PARTNER == 0) {
                                $this->x["srchUrls"]["partner"] = '/dash/' . $tree->tree_slug;
                            } elseif ($tree->tree_opts%Globals::TREEOPT_VOLUNTEER == 0) {
                                $this->x["srchUrls"]["volunteer"] = '/dash/' . $tree->tree_slug;
                            } else {
                                $this->x["srchUrls"]["public"] = '/' . $tree->tree_slug;
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

    public function getSearchCoreTbls()
    {
        $tbls = [];
        if (isset($this->x["searchCoreTbls"])
            && sizeof($this->x["searchCoreTbls"]) > 0) {
            $tbls = $this->x["searchCoreTbls"];
        } elseif (sizeof($this->pubCoreTbls) > 0) {
            $tbls = $this->pubCoreTbls;
        }
        return $tbls;
    }
    
    public function getGenericRows($tbl, $fld = '', $val = '', $oper = '', $ordFld = '')
    {
        eval("\$rows = " . $this->modelPath($tbl) . "::" 
            . ((trim($fld) != '') ? "where('" . $fld . "'" . ((trim($oper) != '') 
                ? ", '" . $oper . "'" : "") . ", '" . $val . "')->" : "")
            . ((in_array($oper, ['<>', 'NOT LIKE']) && strtolower($val) != 'null') 
                ? "orWhere('" . $fld . "', NULL)->" : "")
            . ((trim($ordFld) != '') ? "orderBy('" . $ordFld . "', 'asc')->" : "") 
            . ((isset($this->exprtProg["tok"]) && $this->exprtProg["tok"] 
                && intVal($this->exprtProg["tok"]->tok_core_id) > 0) 
                ? "offset(" . $this->exprtProg["tok"]->tok_core_id . ")->" : "")
            . "limit(50000)->get();");
        return $rows;
    }
    
    public function genCsv($tbl, $fld = '', $val = '', $oper = '', $ordFld = '', $filebase = '')
    {
        $flds = $this->getTblFldTypes($tbl);
        if (trim($filebase) == '') {
            $filebase = $tbl . ((trim($val) != '') ? '-' . $val 
                . ((trim($oper) != '') ? '-' . $oper : '') : '');
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
                if ($this->exprtProg["fileCnt"] == 1) {
                    $this->exprtProg["tok"]->tok_core_id = 0;
                }
            } elseif (isset($this->exprtProg["tok"]) && $this->exprtProg["tok"] 
                && intVal($this->exprtProg["tok"]->tok_tree_id) > 0) {
                $this->exprtProg["fileCnt"] = $this->exprtProg["tok"]->tok_tree_id;
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
                            $rowCsv .= ',' . ((isset($row->{ $fld })) 
                                ? str_replace(',', '!;!', $row->{ $fld }) : '');
                        }
                        if (trim($rowCsv) != '') {
                            $rowCsv = substr($rowCsv, 1) . "\n";
                        }
                        $this->exprtProg["fileContent"] .= $rowCsv;
                        $this->exprtProg["tok"]->tok_core_id++;
                        if (strlen($this->exprtProg["fileContent"]) > 9000000) {
                            $this->genCsvStore();
                            echo '<html><body><br /><br /><center>' 
                                . $this->spinner() . '<br /></center>' . "\n"
                                . '<script type="text/javascript"> '
                                . 'setTimeout("window.location=\'' 
                                . $this->getCurrUrlBase() . '?export=' 
                                . $this->exprtProg["fileCnt"] . '&off=' 
                                . $this->exprtProg["tok"]->tok_core_id 
                                . '\'", 1000); </script></body></html>';
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
            session()->save();
            echo '<html><body><br /><br /><center>' . $this->spinner() . '<br /></center>'
                . "\n" . '<script type="text/javascript"> setTimeout("window.location=\'' 
                . $this->getCurrUrlBase() . '\'", 1000); </script></body></html>';
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
        $filename = str_replace(
            '.csv',
            '-' . $this->leadZero($this->exprtProg["fileCnt"]) . '.csv', 
            $this->exprtProg["fileName"]
        );
        if (file_exists($filename)) {
            unlink($filename);
        }
        file_put_contents($filename, $this->exprtProg["fileContent"]);
        $this->exprtProg["fileCnt"]++;
        $this->exprtProg["tok"]->tok_tree_id = $this->exprtProg["fileCnt"];
        $this->exprtProg["tok"]->save();
        $this->exprtProg["fileContent"] = '';
        return true;
    }
    
    public function getExportProgress($filebase, $expImp = 'Export')
    {
        $twoDaysAgo = date("Y-m-d H:i:s", mktime(0, 0, 0, date("n"), date("j")-2, date("Y")));
        $this->exprtProg["tok"] = SLTokens::where('tok_type', $expImp . ' Progress')
            ->where('tok_tok_token', $filebase)
            ->where('tok_user_id', $this->uID)
            ->where('created_at', '>', $twoDaysAgo)
            ->first();
        if (!$this->exprtProg["tok"]) {
            $this->exprtProg["tok"] = new SLTokens;
            $this->exprtProg["tok"]->tok_type     = $expImp . ' Progress';
            $this->exprtProg["tok"]->tok_tok_token = $filebase;
            $this->exprtProg["tok"]->tok_user_id   = $this->uID;
            $this->exprtProg["tok"]->tok_core_id   = 0;
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
    	if (!file_exists($filebase) 
            && file_exists(str_replace('.csv', '-01.csv', $filebase))) {
    		$filebase = str_replace('.csv', '-01.csv', $filebase);
    	}
        $flds = $this->getTblFldTypes($tbl);
        $this->getExportProgress($filebase, 'Import');
        $this->exprtProg["tok"]->tok_tree_id = 1;
        $tokSffx = '-' . $this->leadZero($this->exprtProg["tok"]->tok_tree_id) . '.csv';
    	$this->exprtProg["fileName"] = str_replace('-01.csv', $tokSffx, $filebase);
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
				$this->exprtProg["tok"]->tok_tree_id++;
				$this->exprtProg["tok"]->save();
                $tokSffx = '-' . $this->leadZero($this->exprtProg["tok"]->tok_tree_id) . '.csv';
				$this->exprtProg["fileName"] = str_replace('-01.csv', $tokSffx, $filebase);
            }
        }
        return true;
    }
    
    public function importZipsUS()
    {
        $chk = SLZips::where('zip_country', 'IS', NULL)
        	->orWhere('zip_country', 'LIKE', '')
            ->first();
        if (!$chk) {
        	$this->importCsv(
                'Zips', 
                'https://survloop.org/survlooporg/expansion-pack/Zips-US-01.csv'
            );
        }
        return true;
    }
    
    // sourced from https://fusiontables.google.com/DataSource?docid=1H_cl-oyeG4FDwqJUTeI_aGKmmkJdPDzRNccp96M
    // and https://www.aggdata.com/free/canada-postal-codes
    public function importZipsCanada()
    {
        $sql = "";
        $chk = SLZips::where('zip_country', 'Canada')
            ->first();
        if (!$chk) {
        	$this->importCsv(
                'Zips', 
                'https://survloop.org/survlooporg/expansion-pack/Zips-Canada-01.csv'
            );
        }
        return true;
    }
    
    
    public function mysqlTblCoreStart($tbl)
    {
        return "CREATE TABLE IF NOT EXISTS `" 
            . (($tbl->tbl_database == 3) ? 'sl_' : $this->dbRow->db_prefix) 
            . $tbl->tbl_name . "` ( `" . $tbl->tbl_abbr
            . "id` int(11) NOT NULL AUTO_INCREMENT, \n";
    }
    
    public function mysqlTblCoreFinish($tbl)
    {
        return "  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP , \n"
            . "  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP "
                . "ON UPDATE CURRENT_TIMESTAMP , \n"
            . "  PRIMARY KEY (`" . $tbl->tbl_abbr . "id`) )"
            . "  ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    }
    
    public function exportMysqlTbl($tbl, $installHereNow = false)
    {
        if (!isset($this->x["indexesEnd"])) {
            $this->x["indexesEnd"] = '';
        }
        if (strtolower($tbl->tbl_eng) == 'users') {
            return "";
        }
        
        $tblQuery = $this->mysqlTblCoreStart($tbl);
        $indexes = "";
        $flds = SLFields::where('fld_table', $tbl->tbl_id)
            ->orderBy('fld_ord', 'asc')
            ->orderBy('fld_eng', 'asc')
            ->get();
        if (isset($tbl->tbl_extend) && intVal($tbl->tbl_extend) > 0) {
            $flds = $this->addFldRowExtends($flds, $tbl->tbl_extend);
        }
        if ($flds->isNotEmpty()) {
            foreach ($flds as $fld) {
                $tblQuery .= "  `" . $tbl->tbl_abbr . $fld->fld_name . "` ";
                if ($fld->fld_type == 'INT') {
                    if (intVal($fld->fld_foreign_table) > 0 
                        && isset($this->tbl[$fld->fld_foreign_table])
                        && strtolower($this->tbl[$fld->fld_foreign_table]) == 'users') {
                        $tblQuery .= "BIGINT(20) unsigned ";
                    } else {
                        $tblQuery .= "INT(" . (($fld->fld_data_length > 0) 
                            ? $fld->fld_data_length : 11) . ") ";
                    }
                } elseif ($fld->fld_type == 'DOUBLE') {
                    $tblQuery .= "DOUBLE ";
                } elseif ($fld->fld_type == 'VARCHAR') {
                    if ($fld->fld_values == 'Y;N' || $fld->fld_values == 'M;F') {
                        $tblQuery .= "VARCHAR(1) ";
                    } else {
                        $tblQuery .= "VARCHAR(" . (($fld->fld_data_length > 0) 
                            ? $fld->fld_data_length : 255) . ") ";
                    }
                } elseif ($fld->fld_type == 'TEXT') {
                    $tblQuery .= "TEXT ";
                } elseif ($fld->fld_type == 'DATE') {
                    $tblQuery .= "DATE ";
                } elseif ($fld->fld_type == 'DATETIME') {
                    $tblQuery .= "DATETIME ";
                }
                if (($fld->fld_null_support && intVal($fld->fld_null_support) == 1)
                    || ($fld->fld_default && trim($fld->fld_default) == 'NULL')) {
                    $tblQuery .= "NULL ";
                }
                if ($fld->fld_default && trim($fld->fld_default) != '') {
                    if (in_array($fld->fld_default, ['NULL', 'NOW()'])) {
                        $tblQuery .= "DEFAULT " . $fld->fld_default . " ";
                    } else {
                        $tblQuery .= "DEFAULT '" . $fld->fld_default . "' ";
                    }
                }
                $tblQuery .= ", \n";
                if ($fld->fld_is_index && intVal($fld->fld_is_index) == 1) {
                    $indexes .= "  , KEY `" . $tbl->tbl_abbr . $fld->fld_name . "` "
                        . "(`" . $tbl->tbl_abbr . $fld->fld_name . "`) \n";
                }
                if (intVal($fld->fld_foreign_table) > 0) {
                    list($forTbl, $forID) = $this->chkForeignKey($fld->fld_foreign_table);
                    $this->x["indexesEnd"] .= "ALTER TABLE `" 
                        . $this->dbRow->db_prefix . $tbl->tbl_name 
                        . "` ADD FOREIGN KEY (`" . $tbl->tbl_abbr . $fld->fld_name . "`) "
                        . "REFERENCES `" . $forTbl . "` (`" . $forID . "`); \n";
                }
            }
            $tblQuery .= $this->mysqlTblCoreFinish($tbl);
        }
        return $tblQuery;
    }
    
    public function tblQrySlExports()
    {
        $exportTbls = [
            'bus_rules', 'conditions', 'conditions_articles', 'conditions_nodes', 
            'conditions_vals', 'databases', 'data_helpers', 'data_links', 
            'data_loop', 'data_subsets', 'definitions','emails', 'fields', 
            'images', 'node', 'node_responses', 'tables', 'tree'
        ];
        return SLTables::where('tbl_database', 3)
            ->whereIn('tbl_name', $exportTbls)
            ->orderBy('tbl_ord', 'asc')
            ->get();
    }
    
    public function loadSlParents($dbIN = -3)
    {
        $dbID = intVal($this->dbID);
        if ($dbIN > 0) {
            $dbID = intVal($dbIN);
        }
        $dbIDs = [ $dbID ];
        if ($dbID == 3) {
            // if getting Survloop database, include the universal non-DbID
            $dbIDs[] = 0;
        }
        $this->x["slTrees"] = $this->x["slNodes"] = $this->x["slConds"] = [];
        $chk = SLTree::whereIn('tree_database', $dbIDs)
            ->select('tree_id')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $tree) {
                $this->x["slTrees"][] = $tree->tree_id;
            }
        }
        $chk = SLNode::whereIn('node_tree', $this->x["slTrees"])
            ->select('node_id')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $node) {
                $this->x["slNodes"][] = $node->node_id;
            }
        }
        $chk = SLConditions::whereIn('cond_database', $dbIDs)
            ->select('cond_id')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $cond) {
                $this->x["slConds"][] = $cond->cond_id;
            }
        }
        return true;
    }
    
    public function chkTableSeedCnt($tblClean = '', $eval = '')
    {
        $seedCnt = 0;
        if (trim($tblClean) != '' 
            && file_exists('../app/Models/' . $tblClean . '.php')) {
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
        if (trim($tblClean) != '' 
            && file_exists('../app/Models/' . $tblClean . '.php')) {
            eval("\$seedChk = App\\Models\\" . $tblClean . "::" 
                . $eval . "orderBy('created_at', 'asc')->get();");
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
    
    public function loadSlSeedEval($tbl = [], $dbIn = -3)
    {
        $dbID = $this->dbID;
        if ($dbIn > 0) {
            $dbID = $dbIn;
        }
        $dbIDs = "[" . $dbID . (($dbID == 3) ? ", 0" : "") . "]";
        if (!isset($this->x["slTrees"])) {
            $this->loadSlParents($dbID);
        }
        $eval = "";
        if (isset($tbl->tbl_name)) {
            $dbStandards = [
                'bus_rules', 'conditions', 'definitions', 'fields', 'tables', 'tree'
            ];
            $treeChildren = [
                'node', 'data_helpers', 'data_links', 'data_loop', 'data_subsets', 'emails'
            ];
            $condChildren = [
                'conditions_articles', 'conditions_nodes', 'conditions_vals'
            ];
            if ($tbl->tbl_name == 'databases') {
                $eval = "whereIn('db_id', " . $dbIDs . ")->";
            } elseif ($tbl->tbl_name == 'images') {
                $eval = "whereIn('" . $tbl->tbl_abbr . "database_id', " . $dbIDs . ")->";
            } elseif (in_array($tbl->tbl_name, $dbStandards)) {
                $eval = "whereIn('" . $tbl->tbl_abbr . "database', " . $dbIDs . ")->";
                if ($tbl->tbl_name == 'definitions') {
                    $eval .= "whereNotIn('def_subset', [
                        'facebook-app-id', 'google-analytic', 
                        'google-cod-key', 'google-cod-key2', 
                        'google-map-key', 'google-map-key2', 
                        'google-maps-key', 'google-maps-key2', 
                        'matomo-analytic-url', 'matomo-analytic-site-id'
                    ])->";
                }
            } elseif (in_array($tbl->tbl_name, $treeChildren)) {
                $eval = "whereIn('" . $tbl->tbl_abbr . "tree', [" 
                    . implode(", ", $this->x["slTrees"]) . "])->";
            } elseif (in_array($tbl->tbl_name, $condChildren)) {
                $eval = "whereIn('" . $tbl->tbl_abbr . "cond_id', [" 
                    . implode(", ", $this->x["slConds"]) ."])->";
            } elseif ($tbl->tbl_name == 'NodeResponses') {
                $eval = "whereIn('" . $tbl->tbl_abbr . "node', [" 
                    . implode(", ", $this->x["slNodes"]) . "])->";
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
                $this->x["tblName"] = 'sl_' . $tbl->tbl_name;
                $this->x["tblClean"] = str_replace('_', '', $this->x["tblName"]);
                $this->x["export"] .= "\nDROP TABLE IF EXISTS `" 
                    . $this->x["tblName"] . "`;\n" . $this->exportMysqlTbl($tbl);
                $flds = $this->getTableFields($tbl);
                if ($flds->isNotEmpty()) {
                    $seedChk = $this->getTableSeedDump(
                        $this->x["tblClean"], 
                        $this->loadSlSeedEval($tbl)
                    );
                    if ($seedChk->isNotEmpty()) {
                        $this->x["tblInsertStart"] = "\nINSERT INTO `" 
                            . $this->x["tblName"] . "` (`" . $tbl->tbl_abbr . "id`";
                        foreach ($flds as $i => $fld) {
                            $this->x["tblInsertStart"] .= ", `" 
                                . $tbl->tbl_abbr . $fld->fld_name . "`";
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
                                if (isset($seed->{ $tbl->tbl_abbr . $fld->fld_name })) {
                                    $this->x["export"] .= ", '" . str_replace("'", "\'", 
                                        $seed->{ $tbl->tbl_abbr . $fld->fld_name }) . "'";
                                } elseif ($fld->fld_null_support 
                                    && intVal($fld->fld_null_support) == 1) {
                                    $this->x["export"] .= ", NULL";
                                } else {
                                    $this->x["export"] .= ", ''";
                                }
                            }
                            $this->x["export"] .= ", '" . $seed->created_at 
                                . "', '" . $seed->updated_at . "')";
                        }
                        $this->x["export"] .= "; \n";
                    }
                }
            }
        }
        while (strpos($this->x["export"], ") VALUES \n,\n") !== false) {
            $this->x["export"] = str_replace(
                ") VALUES \n,\n", 
                ") VALUES \n", 
                $this->x["export"]
            );
        }
        //$this->tmpDbSwitchBack();
        return true;
    }
    
    public function createTableIfNotExists($coreTbl, $userTbl = null)
    {
        $this->modelPath($coreTbl->tbl_name, true);
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
        $dbID = $this->dbID;
        if ($pkg == 'rockhopsoft/survloop') {
            $dbID = 3;
        }
    	$types = $this->loadTreeNodeStatTypes();
    	$stats = [
            "date"     => date("Y-m-d"),
            "icon_url" => $this->sysOpts["app-url"] . $this->sysOpts["shortcut-icon"]
        ];
    	$survs = $pages = [];
    	$stats["db_tables"] = SLTables::where('tbl_database', $dbID)
    	   ->count();
    	$stats["db_fields"] = SLFields::where('fld_database', $dbID)
    	   ->where('fld_table', '>', 0)
    	   ->count();
    	$stats["db_links"] = SLFields::where('fld_database', $dbID)
    	   ->where('fld_foreign_table', '>', 0)
    	   ->where('fld_table', '>', 0)
    	   ->count();
    	$chk = SLTree::where('tree_type', 'Survey')
    		->where('tree_database', $dbID)
    		->select('tree_id')
    		->get();
    	if ($chk->isNotEmpty()) {
    		foreach ($chk as $t) {
    		    $survs[] = $t->tree_id;
    		}
    	}
    	$stats["surveys"] = sizeof($survs);
    	$stats["survey_nodes"] = SLNode::whereIn('node_tree', $survs)
            ->count();
    	$stats["survey_nodes_mult"] = SLNode::whereIn('node_tree', $survs)
            ->whereIn('node_type', $types["choic"])
            ->count();
    	$stats["survey_nodes_open"] = SLNode::whereIn('node_tree', $survs)
            ->whereIn('node_type', $types["quali"])
            ->count();
    	$stats["survey_nodes_numb"] = SLNode::whereIn('node_tree', $survs)
            ->whereIn('node_type', $types["quant"])
            ->count();
    	$chk = SLTree::where('tree_type', 'Page')
    		->where('tree_database', $dbID)
    		->select('tree_id')
    		->get();
    	if ($chk->isNotEmpty()) {
    		foreach ($chk as $t) {
    		    $pages[] = $t->tree_id;
    		}
    	}
    	$stats["pages"]             = sizeof($pages);
    	$stats["page_nodes"]        = SLNode::whereIn('node_tree', $pages)->count();
    	$stats["lines_controllers"] = $this->getPackageLineCount('Controllers', $pkg);
    	$stats["lines_views"]       = $this->getPackageLineCount('Views', $pkg);
    	$stats["bytes_controllers"] = $this->getPackageByteCount('Controllers', $pkg);
    	$stats["bytes_database"]    = $this->getPackageByteCount('Database', $pkg);
    	$stats["bytes_uploads"]     = $this->getPackageByteCount('Uploads', $pkg);
    	$stats["bytes_views"]       = $this->getPackageByteCount('Views', $pkg);
    	$stats["users"] = User::select('id')->count();
    	return $stats;
    }

    public function isPrintView()
    {
        return ($this->isPdfView()
            || ($this->REQ->has('print') && intVal($this->REQ->get('print')) > 0));
    }

    public function isPdfView()
    {
        return ((isset($this->x["isPrintPDF"]) && $this->x["isPrintPDF"])
            || (in_array($this->pageView, ['pdf', 'full-pdf']))
            || ($this->REQ->has('pdf') && intVal($this->REQ->get('pdf')) > 0)
            || ($this->REQ->has('gen-pdf') && intVal($this->REQ->get('gen-pdf')) > 0));
    }

    public function getLimit($limit = 100)
    {
        if ($this->REQ->has('limit')) { 
            $getLimit = intVal($this->REQ->get('limit'));
            if ($getLimit > 0) {
                $limit = $getLimit;
            }
        }
        return $limit;
    }

    public function getStart($start = 0)
    {
        if ($this->REQ->has('start')) { 
            $start = intVal($this->REQ->get('start'));
        }
        return $start;
    }

    public function getSysCustCSS()
    {
        $custCSS = SLDefinitions::where('def_database', $this->dbID)
            ->where('def_set', 'Style CSS')
            ->where('def_subset', 'main')
            ->first();
        if ($custCSS && isset($custCSS->def_description)) {
            return trim($custCSS->def_description);
        }
        return '';
    }
    
}