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
use App\Models\SLConditionsNodes;
use App\Models\SLEmails;

use SurvLoop\Controllers\StatesUS;

class DatabaseLookups
{
    public $isAdmin        = false;
    public $dbID           = 1;
    public $dbRow          = [];
    public $treeID         = 1;
    public $treeRow        = [];
    public $treeName       = '';
    public $treeIsAdmin    = false;
    public $xmlTree        = [];
    public $coreTbl        = '';
    public $coreTblUserFld = '';
    public $treeXmlID      = -3;
    public $treeOverride   = -3;
    
    public $REQ            = [];
    public $sysOpts        = [];
    public $userRoles      = [];
    public $pageSCRIPTS = '';
    public $pageJAVA      = '';
    public $pageAJAX       = '';
    
    public $tblModels      = [];
    public $tbls           = [];
    public $tbl            = [];
    public $tblID          = [];
    public $tblAbbr        = [];
    public $tblOpts        = [];
    public $fldTypes       = [];
    public $fldOthers      = [];
    public $defValues      = [];
    
    public $foreignKeysIn  = [];
    public $foreignKeysOut = [];
    
    public $dataLoops      = [];
    public $dataLoopNames  = [];
    public $dataSubsets    = [];
    public $dataHelpers    = [];
    public $dataLinksOn    = [];
    
    // User's position within potentially nested loops
    public $sessTree       = 'Page';
    public $sessLoops      = [];
    public $closestLoop    = [];
    public $tblLoops       = [];
    public $nodeCondInvert = [];
    
    public $states         = [];
    
    public $fldAbouts      = [];
    public $blurbs         = [];
    public $emaBlurbs      = [];
    public $debugOn        = true;
    
    public $sysTree        = [ "forms" => [ "pub" => [], "adm" => [] ], "pages" => [ "pub" => [], "adm" => [] ] ];
    public $treeSettings   = [];
    
    function __construct(Request $request = NULL, $isAdmin = false, $dbID = 1, $treeID = 1, $treeOverride = -3)
    {
        //echo '<br /><br /><br />__construct, ' . $dbID . ', ' . $treeID . ', ' . $treeOverride . '<br />';
        $this->isAdmin = $isAdmin;
        $this->REQ = $request;
        if ($treeOverride > 0) $this->treeOverride = $treeOverride;
        if ($treeOverride > 0 || $this->treeOverride <= 0) {
            $this->dbID = $dbID;
        }
        $this->dbRow = SLDatabases::find($this->dbID);
        if (($treeOverride > 0 || $this->treeOverride <= 0) && $dbID == 1 
            && (!isset($this->dbRow) || sizeof($this->dbRow) == 0)) {
        	$this->dbID = 3;
        	$this->dbRow = SLDatabases::find($this->dbID);
        }
        if ($treeOverride > 0 || $this->treeOverride <= 0) {
            $this->treeID  = $treeID;
        }
        $this->treeRow = SLTree::where('TreeID', $this->treeID)
            ->where('TreeDatabase', $this->dbID)
            ->first();
        if (($treeOverride > 0 || $this->treeOverride <= 0) && (!$this->treeRow || !isset($this->treeRow->TreeID))) {
            $this->treeRow = SLTree::where('TreeDatabase', $this->dbID)
                ->where('TreeType', 'Primary Public')
                ->orderBy('TreeID', 'asc')
                ->first();
            if (isset($this->treeRow->TreeID)) $this->treeID = $this->treeRow->TreeID;
        }
        $this->treeIsAdmin = false;
        if (isset($this->treeRow->TreeOpts) && $this->treeRow->TreeOpts > 1 && $this->treeRow->TreeOpts%3 == 0) {
            $this->treeIsAdmin = true;
        }
        if (isset($this->dbRow->DbName) && isset($this->treeRow->TreeName)) {
            $this->treeName = str_replace($this->dbRow->DbName, str_replace('_', '', $this->dbRow->DbPrefix), 
                $this->treeRow->TreeName);
            if ($this->treeRow->TreeType != 'Page') $this->sessTree = $this->treeRow->TreeID;
        }

        $this->sysOpts = ["cust-abbr" => 'SurvLoop'];
        
        $this->loadDBFromCache($request);
        
        $this->loadTreeMojis();
        
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
    
    public function urlRoot()
    {
        return str_replace('https://', '', str_replace('http://', '', $this->sysOpts["app-url"]));
    }
    
    public function hasTreeOverride()
    {
        return ($this->treeOverride > 0);
    }
    
    public function loadDBFromCache(Request $request = NULL)
    {
        $cacheFile = '/cache/db-load-' . $this->dbID . '-' . $this->treeID . '.php';
        if ((!$request || !$request->has('refresh')) && file_exists($cacheFile)) {
            $content = Storage::get($cacheFile);
            eval($content);
        } else {
            $cache = '// Auto-generated loading cache from /SurvLoop/Controllers/DatabaseLookups.php' . "\n\n";
            
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
                    $cache .= '$'.'this->treeIsAdmin = true;' . "\n";
                }
            }

            $sys = []; /* SLDefinitions::where('DefDatabase', $this->dbID)
                ->where('DefSet', 'System Settings')
                ->get(); */
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
                        $cache .= '$'.'this->coreTbl = \'' . $coreTbl . '\';' . "\n";
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
                    if ($tbl->TblID == $this->treeRow->TreeCoreTable) {
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
                        $othFld = '';
                        if (strtolower(substr($fldName, strlen($fldName)-5)) == 'other') {
                            $othFld = substr($fldName, 0, strlen($fldName)-5);
                        }
                        if (trim($othFld) != '' && in_array($othFld, $fldNames)) {
                            $cache .= '$'.'this->fldOthers[\'' . $fldName . '\'] = ' . $fld->FldID . ';' . "\n";
                        }
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
                        $cache .= '$'.'this->dataLinksOn[' . $tbl . '] = [ \'' . $map[0] . '\', \'' . $map[1] 
                            . '\', \'' . $map[2] . '\', \'' . $map[3] . '\', \'' . $map[4] . '\' ];' . "\n";
                    }
                }
                
                $cache .= '$'.'this->nodeCondInvert = [];' . "\n";
                $inv = SLConditionsNodes::where('CondNodeNodeID', '>', 0)
                    ->where('CondNodeLoopID', '<', 0)
                    ->get();
                if ($inv && sizeof($inv) > 0) {
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
            
            $this->getCoreTblUserFld();
            $cache2 = '$'.'this->coreTblUserFld = \'' . $this->coreTblUserFld . '\';' . "\n";
            if ($this->treeRow->TreeType == 'Primary Public') {
                $xmlTree = SLTree::where('TreeSlug', $this->treeRow->TreeSlug)
                    ->where('TreeDatabase', $this->treeRow->TreeDatabase)
                    ->where('TreeType', 'Primary Public XML')
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
                        . '"root" => '      . $xmlTree->TreeRoot . ', '
                        . '"coreTblID" => ' . $xmlTree->TreeCoreTable . ', '
                        . '"coreTbl" => "'  . $this->tbl[$xmlTree->TreeCoreTable] . '", '
                        . '"opts" => '      . $xmlTree->TreeOpts
                    . ' ];' . "\n";
                }
            }
            eval($cache2);
            
            if (file_exists($cacheFile)) Storage::delete($cacheFile);
            Storage::put($cacheFile, $cache . $cache2);
        }
        return true;
    }
    
    public function modelPath($tbl = '', $forceFile = false)
    {
        if ($tbl == 'users') return "App\\Models\\User";
        if (isset($this->tblModels[$tbl])) {
            $path = "App\\Models\\" . $this->tblModels[$tbl];
            $this->chkTblModel($tbl, $path, $forceFile);
            return $path;
        }
        return '';
    }
    
    public function chkTblModel($tbl, $path, $forceFile = false)
    {
        if (in_array(strtolower(trim($tbl)), ['', 'uers'])) return false;
        $modelFilename = str_replace('App\\Models\\', '../app/Models/', $path) . '.php';
        if ($this->isAdmin && (!file_exists($modelFilename) || $forceFile)) { // copied from DatabaseInstaller...
            $modelFile = '';
            $tbl = SLTables::where('TblDatabase', $this->dbID)
                ->where('TblName', $tbl)
                ->first();
            $flds = SLFields::where('FldDatabase', $this->dbID)
                ->where('FldTable', $tbl->TblID)
                ->orderBy('FldOrd', 'asc')
                ->orderBy('FldEng', 'asc')
                ->get();
            if ($flds && sizeof($flds) > 0) {
                foreach ($flds as $fld) {
                    $modelFile .= "\n\t\t'" . $tbl->TblAbbr . $fld->FldName . "', ";
                }
            }                                      
            $tblName = $this->dbRow->DbPrefix . $tbl->TblName;
            $fullFileOut = view('vendor.survloop.admin.db.export-laravel-gen-model' , [
                "modelFile" => $modelFile, 
                "tbl"       => $tbl,
                "tblName"   => $tblName,
                "tblClean"  => str_replace('_', '', $tblName)
            ]);
            if (is_writable($modelFilename)) {
                if (file_exists($modelFilename)) unlink($modelFilename);
                file_put_contents($modelFilename, $fullFileOut);
            }
        }
        return true;
    }
    
    public function isCoreTbl($tblID)
    {
        if (!isset($this->treeRow->TreeCoreTable)) return false;
        return ($tblID == $this->treeRow->TreeCoreTable);
    }
    
    public function getCoreTblUserFld()
    {
        if ((!isset($this->coreTblUserFld) || trim($this->coreTblUserFld) == '') 
        	&& isset($this->tblI[$this->coreTbl])) {
            $coreTblID = $this->tblI[$this->coreTbl];
            $userTbl = SLTables::where('TblDatabase', $this->dbID)
                ->whereIn('TblName', ['users', 'Users'])
                ->first();
            if ($userTbl && isset($userTbl->TblID)) {
                $keyFld = SLFields::where('FldTable', $coreTblID)
                    ->where('FldForeignTable', $userTbl->TblID)
                    ->first();
                if ($keyFld && isset($keyFld->FldName)) {
                    $this->coreTblUserFld = $this->tblAbbr[$this->coreTbl] . $keyFld->FldName;
                }
            }
        }
        return $this->coreTblUserFld;
    }
    
    public function dbFullSpecs()
    {
        return ($this->dbRow->DbOpts%3 > 0);
    }
    
    
    public function isStepLoop($loop)
    {
        return (isset($this->dataLoops[$loop]) && intVal($this->dataLoops[$loop]->DataLoopIsStep) == 1);
    }
    
    public function setClosestLoop($loop = '', $itemID = -3, $obj = [])
    {
        $this->closestLoop = [ "loop" => $loop, "itemID" => $itemID, "obj" => $obj ];
        return true;
    }
    
    public function chkClosestLoop()
    {
        if ($this->sessLoops && isset($this->sessLoops[0])) {
            $loop = $this->sessLoops[0]->SessLoopName;
            if (isset($this->dataLoops[$loop])) {
                $this->setClosestLoop($loop, $this->sessLoops[0]->SessLoopItemID, $this->dataLoops[$loop]);
            }
        }
        return true;
    }
    
    public function loadSessLoops($sessID)
    {
        $this->sessLoops = SLSessLoops::where('SessLoopSessID', $sessID)
            ->orderBy('SessLoopID', 'desc')
            ->get();
        $this->setClosestLoop();
        $this->chkClosestLoop();
        return $this->sessLoops;
    }
    
    public function fakeSessLoopCycle($loop, $itemID)
    {
        /// add fake to [0] position, then reset closest
        $tmpLoops = $this->sessLoops;
        $this->sessLoops[0] = new SLSessLoops;
        $this->sessLoops[0]->SessLoopName = $loop;
        $this->sessLoops[0]->SessLoopItemID = $itemID;
        if (sizeof($tmpLoops) > 0) {
            foreach ($tmpLoops as $l) $this->sessLoops[] = $l;
        }
        $this->setClosestLoop($loop, $itemID, $this->dataLoops[$loop]);
        return true;
    }
    
    public function removeFakeSessLoopCycle($loop, $itemID)
    {
        $tmpLoops = $this->sessLoops;
        if (sizeof($tmpLoops) > 0) {
            foreach ($tmpLoops as $i => $l) {
                if ($l->SessLoopName != $loop || $l->SessLoopItemID != $itemID) {
                    $this->sessLoops[] = $l;
                }
            }
        }
        $this->chkClosestLoop();
        return true;
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
    
    public function getLoopSingular($loopName)
    {
        if (isset($this->dataLoops[$loopName])) {
            return $this->dataLoops[$loopName]->DataLoopSingular;
        }
        return '';
    }
    
    public function getLoopTable($loopName)
    {
        if (isset($this->dataLoops[$loopName])) {
            return $this->dataLoops[$loopName]->DataLoopTable;
        }
        return '';
    }
    
    public function loadLoopConds()
    {
        if (isset($this->dataLoops) && sizeof($this->dataLoops) > 0) {
            foreach ($this->dataLoops as $loopName => $loop) {
                if (isset($this->dataLoops[$loopName]->DataLoopTree)) {
                    $this->dataLoops[$loopName]->loadLoopConds();
                }
            }
        }
        return true;
    }
    
    
    public function fldForeignKeyTbl($tbl, $fld)
    {
        if (trim($tbl) == '' || trim($fld) == '' || !isset($this->tblI[$tbl])) return '';
        $fld = SLFields::select('FldForeignTable')
            ->where('FldTable', $this->tblI[$tbl])
            ->whereIn('FldName', [$fld, str_replace($this->tblAbbr[$tbl], '', $fld)])
            ->where('FldForeignTable', '>', 0)
            ->first();
        if ($fld && sizeof($fld) > 0) return $this->tbl[$fld->FldForeignTable];
        return '';
    }
    
    public function getForeignLnkName($tbl1, $tbl2 = '')
    {
        if (trim($tbl1) == '' || trim($tbl2) == '' || !isset($this->tblI[$tbl1]) || !isset($this->tblI[$tbl2])) {
            return '';
        }
        return $this->getForeignLnk($this->tblI[$tbl1], $this->tblI[$tbl2]);
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
            ->where('FldTable',            '>', 0)
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
                    if (isset($this->tbl[$fld->FldTable]) && isset($this->tbl[$fld->FldForeignTable])) {
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
        }
        return $retVal;
    }
    
    
    // returns array(Table 1, Foreign Key 1, Linking Table, Foreign Key 2, Table 2)
    public function getLinkTblMap($linkTbl = -3)
    {
        if ($linkTbl <= 0) return [];
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
        return [];
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
        if ($loopName == '' || !isset($this->dataLoops[$loopName])) return [];
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
        return [];
    }
    
    
    
    
    public function tablesDropdown($preSel = '', $instruct = '', $prefix = '', $disableBlank = false)
    {
        $loopTbl = '';
        if (trim($preSel) != '' && isset($this->dataLoops[$preSel])) {
            $loopTbl = $this->dataLoops[$preSel]->DataLoopTable;
        }
        $retVal = '<option value="" ' . (($preSel == "") ? 'SELECTED' : '') 
            . (($disableBlank) ? ' DISABLED ' : '') . ' >' . $instruct . '</option>' . "\n";
        foreach ($this->tblAbbr as $tblName => $tblAbbr) {
            $retVal .= '<option value="' . $tblName.'" ' 
                . (($preSel == $tblName || $loopTbl == $tblName) ? 'SELECTED' : '') 
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
            WHERE f.`FldTable` > '0' [[EXTRA]] AND f.`FldDatabase` LIKE '" . $this->dbID . "' 
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
        $tblID = -3;
        if (strpos($tblIn, 'loop-') !== false && sizeof($this->dataLoops) > 0) {
            $loopID = intVal(str_replace('loop-', '', $tblIn));
            foreach ($this->dataLoops as $loopName => $loopRow) {
                if ($loopRow->id == $loopID && isset($this->tblI[$loopRow->DataLoopTable])) {
                    $tblID = $this->tblI[$loopRow->DataLoopTable];
                }
            }
        } elseif (isset($this->tblI[$tblIn])) {
            $tblID = $this->tblI[$tblIn];
        }
        $tbls = $this->getSubsetTables($tblID, $tbls);
        return $tbls;
    }
    
    public function getSubsetTables($tbl1 = -3, $tbls = [])
    {
        if ($tbl1 > 0 && !in_array($tbl1, $tbls))
        {
            $tbls[] = $tbl1;
            if (isset($this->dataSubsets) && sizeof($this->dataSubsets) > 0) {
                foreach ($this->dataSubsets as $subset) {
                    if ($tbl1 == $this->tblI[$subset->DataSubTbl]) {
                        $tbls = $this->getSubsetTables($this->tblI[$subset->DataSubSubTbl], $tbls);
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
    
    public function fieldsTblsDropdown($tbls = [], $preSel = '', $prfx = '')
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
        $url = urlClean($url);
        if (strpos($url, '/') !== false) $url = substr($url, 0, strpos($url, '/'));
        return $url;
    }
    
    function urlClean($url)
    {
        $url = str_replace('http://', '', str_replace('https://', '', 
            str_replace('http://www.', '', str_replace('https://www.', '', $url))));
        $pos = strrpos($url, '/');
        if ($pos !== false && $pos == strlen($url)-1) $url = substr($url, 0, $pos);
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
        if (intVal($fldID) <= 0) return array( "prompt" => '', "vals" => [] );
        return $this->getFldResponses($this->getFullFldNameFromID($fldID));
    }
    
    
    public function getFldResponses($fldName)
    {
        $ret = array( "prompt" => '', "vals" => [] );
        $tmpVals = array( [], [] );
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
        return SLConditions::whereIn('CondDatabase', [0, $this->dbID])
            ->orderBy('CondTag')
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
            SLConditionsVals::where('CondValCondID', $cond->CondID)->delete();
        }
        else $cond->CondDatabase = $this->dbID;
        $cond->CondTag      = (($request->has('condHash')) ? $request->condHash : '#');
        if (substr($cond->CondTag, 0, 1) != '#') {
            $cond->CondTag  = '#' . $cond->CondTag;
        }
        $cond->CondDesc     = (($request->has('condDesc')) ? $request->condDesc : '');
        $cond->CondOpts     = 1;
        $cond->CondOperator = 'CUSTOM';
        $cond->CondOperDeet = 0;
        $cond->CondField = $cond->CondTable = $cond->CondLoop = 0;
        if ($request->has('setSelect')) {
            $tmp = trim($request->setSelect);
            if ($tmp == 'url-parameters') {
                $cond->CondOperator = 'URL-PARAM';
            } elseif (strpos($tmp, 'loop-') !== false) {
                $cond->CondLoop = intVal(str_replace('loop-', '', $tmp));
            } else {
                $cond->CondTable = intVal($this->tblI[$tmp]);
            }
        }
        if ($cond->CondOperator == 'URL-PARAM') {
            $cond->CondOperDeet = $request->paramName;
        } elseif ($request->has('setFld')) {
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
        if ($cond->CondOperator == 'URL-PARAM') {
            $tmpVal = new SLConditionsVals;
            $tmpVal->CondValCondID = $cond->CondID;
            $tmpVal->CondValValue  = $request->paramVal;
            $tmpVal->save();
        } elseif ($request->has('vals') && sizeof($request->vals) > 0) {
            foreach ($request->vals as $val) {
                $tmpVal = new SLConditionsVals;
                $tmpVal->CondValCondID = $cond->CondID;
                $tmpVal->CondValValue  = $val;
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
    
    
    protected function loadBlurbNames()
    {
        if (sizeof($this->blurbs) == 0) {
            $defs = SLDefinitions::select('DefSubset')
                ->where('DefDatabase', $this->dbID)
                ->where('DefSet', 'Blurbs')
                ->get();
            if ($defs && sizeof($defs) > 0) {
                foreach ($defs as $def) $this->blurbs[] = $def->DefSubset;
            }
        }
        return $this->blurbs;
    }
    
    public function swapBlurbs($str)
    {
        $this->loadBlurbNames();
        if (trim($str) != '' && $this->blurbs && sizeof($this->blurbs) > 0) {
            $changesMade = true;
            while ($changesMade) {
                $changesMade = false;
                foreach ($this->blurbs as $b) {
                    if (strpos($str, '{{' . $b . '}}') !== false) {
                        $blurb = $this->getBlurb($b);
                        $str = str_replace('{{' . $b . '}}', $blurb, $str);
                        $changesMade = true;
                    }
                    if (strpos($str, '{{' . str_replace('&', '&amp;', $b) . '}}') !== false) {
                        $blurb = $this->getBlurb($b);
                        $str = str_replace('{{' . str_replace('&', '&amp;', $b) . '}}', $blurb, $str);
                        $changesMade = true;
                    }
                }
            }
        }
        return $str;
    }
    
    public function getBlurbAndSwap($blurbName = '', $blurbID = -3)
    {
        return $this->swapBlurbs($this->getBlurb($blurbName, $blurbID));
    }
    
    public function getBlurb($blurbName = '', $blurbID = -3)
    {
        $def = [];
        if ($blurbID > 0) $def = SLDefinitions::find($blurbID);
        else {
            $def = SLDefinitions::where('DefSubset', $blurbName)
                ->where('DefDatabase', $this->dbID)
                ->where('DefSet', 'Blurbs')
                ->first();
        }
        if ($def && isset($def->DefDescription)) return $def->DefDescription;
        return '';
    }
    
    
    protected function loadEmailBlurbNames()
    {
        if (sizeof($this->emaBlurbs) == 0) {
            $emas = SLEmails::select('EmailName')
                ->where('EmailTree', $this->treeID)
                ->where('EmailType', 'Blurb')
                ->get();
            if ($emas && sizeof($emas) > 0) {
                foreach ($emas as $e) $this->emaBlurbs[] = $e->EmailName;
            }
        }
        return $this->emaBlurbs;
    }
    
    public function swapEmailBlurbs($str)
    {
        $this->loadEmailBlurbNames();
        if (trim($str) != '' && $this->emaBlurbs && sizeof($this->emaBlurbs) > 0) {
            $changesMade = true;
            while ($changesMade) {
                $changesMade = false;
                foreach ($this->emaBlurbs as $b) {
                    if (strpos($str, '[{ ' . $b . ' }]') !== false) {
                        $blurb = $this->getEmailBlurb($b);
                        $str = str_replace('[{ ' . $b . ' }]', $blurb, $str);
                        $changesMade = true;
                    }
                    if (strpos($str, '[{ ' . str_replace('&', '&amp;', $b) . ' }]') !== false) {
                        $blurb = $this->getEmailBlurb($b);
                        $str = str_replace('[{ ' . str_replace('&', '&amp;', $b) . ' }]', $blurb, $str);
                        $changesMade = true;
                    }
                }
            }
        }
        return $str;
    }
    
    public function getEmailBlurb($blurbName)
    {
        $ema = SLEmails::where('EmailName', $blurbName)->first();
        if ($ema && isset($ema->EmailBody)) return $ema->EmailBody;
        return '';
    }
    
    
    public function addToHeadCore($js)
    {
        if (!isset($this->sysOpts['header-code'])) $this->sysOpts['header-code'] = '';
        $this->sysOpts['header-code'] .= $js;
        return true;
    }
    
    public function loadSysTrees($type = 'forms')
    {
        if (!isset($this->sysTree[$type]) || !isset($this->sysTree[$type]["pub"]) 
            || sizeof($this->sysTree[$type]["pub"]) == 0) {
            $treeType = 'Primary Public';
            if ($type == 'pages') $treeType = 'Page';
            $trees = SLTree::where('TreeType', $treeType)
                ->orderBy('TreeName', 'asc')
                ->select('TreeID', 'TreeName', 'TreeOpts')
                ->get();
            if ($trees && sizeof($trees) > 0) {
                foreach ($trees as $i => $tree) {
                    $pubType = (($tree->TreeOpts%3 == 0) ? 'adm' : 'pub');
                    $this->sysTree[$type][$pubType][] = [$tree->TreeID, $tree->TreeName];
                }
            }
        }
        return true;
    }
    
    public function sysTreesDrop($preSel = -3, $type = 'forms', $pubPri = 'pub')
    {
        $this->loadSysTrees($type);
        $ret = '';
        if (in_array($pubPri, ['pub', 'all']) && sizeof($this->sysTree[$type]['pub']) > 0) {
            foreach ($this->sysTree[$type]['pub'] as $tree) {
                $ret .= '<option value="' . $tree[0] . '" ' . (($preSel == $tree[0]) ? 'SELECTED ' : '') . '>' 
                    . $tree[1] . (($type == 'page') ? ' (Page)' : '') . '</option>';
            }
        }
        if (in_array($pubPri, ['adm', 'all']) && sizeof($this->sysTree[$type]['adm']) > 0) {
            foreach ($this->sysTree[$type]['adm'] as $tree) {
                $ret .= '<option value="' . $tree[0] . '" ' . (($preSel == $tree[0]) ? 'SELECTED ' : '') . '>' 
                    . $tree[1] . ' (' . (($type == 'page') ? 'Page, ' : '') . 'Admin)</option>';
            }
        }
        return $ret;
    }
    
    public function loadTreeMojis()
    {
        if (sizeof($this->treeSettings) == 0) {
            $chk = SLDefinitions::where('DefDatabase', $this->dbID)
                ->where('DefSet', 'Tree Settings')
                ->where('DefSubset', 'LIKE', 'tree-' . $this->treeID . '-%')
                ->orderBy('DefOrder', 'asc')
                ->get();
            if ($chk && sizeof($chk) > 0) {
                foreach ($chk as $set) {
                    $setting = str_replace('tree-' . $this->treeID . '-', '', $set->DefSubset);
                    if (!isset($this->treeSettings[$setting])) $this->treeSettings[$setting] = [];
                    if ($setting == 'emojis') {
                        $names = explode(';', $set->DefValue);
                        $this->treeSettings[$setting][] = [
                            "id"     => $set->DefID,
                            "admin"  => ($set->DefIsActive%7 == 0),
                            "verb"   => $names[0],
                            "plural" => $names[1], 
                            "html"   => $set->DefDescription
                        ];
                    } else {
                        $this->treeSettings[$setting][] = $set->DefDescription;
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
                if ($emo["id"] == $defID) return $emo["verb"];
            }
        }
        return '';
    }
    
    public function getSysStyles()
    {
        return SLDefinitions::where('DefDatabase', 1)
            ->where('DefSet', 'Style Settings')
            ->orderBy('DefOrder')
            ->get();
    }
    
    
    
    
    
    public function mexplode($delim, $str)
    {
        $ret = [];
        if (trim(str_replace($delim, '', $str)) != '') {
            if (strpos($str, $delim) === false) {
                $ret[] = $str;
            } else {
                if (substr($str, 0, 1) == $delim) $str = substr($str, 1);
                if (substr($str, strlen($str)-1) == $delim) $str = substr($str, 0, strlen($str)-1);
                $ret = explode($delim, $str);
            }
        }
        return $ret;
    }
    
    
    public function swapURLwrap($url, $printHttp = true)
    {
        $urlPrint = str_replace('mailto:', '', $url);
        if (!$printHttp) $urlPrint = str_replace('http://', '', str_replace('https://', '', $urlPrint));
        return '<a href="' . $url . '" target="_blank">' . $urlPrint . '</a>'; 
    }
    
    public function sortArrByKey($arr, $key, $ord = 'asc')
    {
        if (sizeof($arr) < 2) return $arr;
        $arrCopy = $arrOrig = $arr;
        $arr = [];
        for ($i = 0; $i < sizeof($arrOrig); $i++) {
            if (sizeof($arrCopy) == 1) {
                $arr[] = $arrCopy[0];
            } else {
                $nextInd = -1;
                for ($j = 0; $j < sizeof($arrCopy); $j++) {
                    if ($nextInd < 0) {
                        $nextInd = $j;
                    } elseif ($ord == 'asc') {
                        if ($arrCopy[$j][$key] < $arrCopy[$nextInd][$key]) $nextInd = $j;
                    } else {
                        if ($arrCopy[$j][$key] > $arrCopy[$nextInd][$key]) $nextInd = $j;
                    }
                }
                $arr[] = $arrCopy[$nextInd];
                array_splice($arrCopy, $nextInd, 1);
            }
        }
        return $arr;
    }
    
    public function mapsURL($addy)
    {
        return 'https://www.google.com/maps/search/' . urlencode($addy) . '/';
    }
    
}
