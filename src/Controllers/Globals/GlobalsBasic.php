<?php
/**
  * GlobalsBasic is a mid-level class to declare the most basic
  * functions which are specifc to Survloop (compared to the
  * more generalized functions in GlobalsStatic.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */
namespace RockHopSoft\Survloop\Controllers\Globals;

use DB;
use Auth;
use App\Models\SLFields;
use App\Models\SLTables;
use App\Models\SLTree;
use App\Models\SLUserActivity;
use RockHopSoft\Survloop\Controllers\Globals\GlobalsVars;

class GlobalsBasic extends GlobalsVars
{
    public function getTreePrimeConst($type)
    {
        eval("\$prime = self::TREEOPT_" . $type . ";");
        return intVal($prime);
    }
    
    public function chkTreeOpt($treeOpts = 1, $type = '')
    {
        if ($type == '' || $treeOpts == 0) {
            return false;
        }
        $prime = $this->getTreePrimeConst($type);
        return (intVal($prime) != 0 && $treeOpts%$prime == 0);
    }

    public function chkCurrTreeOpt($type = '')
    {
        if (!isset($this->treeRow->tree_opts)) {
            return false;
        }
        return $this->chkTreeOpt($this->treeRow->tree_opts, $type);
    }
    
    public function hasTreeOverride()
    {
        return ($this->treeOverride > 0);
    }
    
    public function dbFullSpecs()
    {
        return ($this->dbRow->db_opts%3 > 0);
    }

    
    public function isCoreTbl($tblID)
    {
        if (!isset($this->treeRow->tree_core_table)) {
            return false;
        }
        return ($tblID == $this->treeRow->tree_core_table);
    }
    
    public function coreTblAbbr()
    {
        return ((isset($this->tblAbbr[$this->coreTbl])) 
            ? $this->tblAbbr[$this->coreTbl] : '');
    }
    
    public function coreTblIdFld()
    {
        return $this->coreTblAbbr() . 'id';
    }
    
    public function coreTblIdFldOrPublicId()
    {
        $ret = $this->coreTblAbbr();
        if ($this->tblHasPublicID()) {
            $ret .= "public_";
        }
        return $ret . "id";
    }
    
    public function swapIfPublicID($pubID = -3, $tbl = '')
    {
        if (trim($tbl) == '') {
            $tbl = $this->coreTbl;
        }
        if ($this->tblHasPublicID($tbl)) {
            return $this->chkInPublicID($pubID, $tbl);
        }
        return $pubID;
    }
    
    public function tblHasPublicID($tbl = '')
    {
        if (trim($tbl) == '') {
            $tbl = $this->coreTbl;
        }
        if (!isset($this->x["tblHasPublicID"])) {
            $this->x["tblHasPublicID"] = [];
        }
        if (isset($this->x["tblHasPublicID"][$tbl])) {
            return $this->x["tblHasPublicID"][$tbl];
        }
        $this->x["tblHasPublicID"][$tbl] = false;
        if ((isset($this->treeRow->tree_opts) 
                && $this->treeRow->tree_opts%Globals::TREEOPT_PUBLICID == 0) 
            || (isset($this->reportTree["opts"]) 
                && $this->reportTree["opts"]%Globals::TREEOPT_PUBLICID == 0)) {
            $this->x["tblHasPublicID"][$tbl] = true;
        } elseif (isset($this->tblI[$tbl])) {
            $chk = SLTree::where('tree_type', 'Survey')
                ->where('tree_core_table', $this->tblI[$tbl])
                ->orderBy('tree_id', 'asc')
                ->first();
            if ($chk
                && isset($chk->tree_opts)
                && $chk->tree_opts%Globals::TREEOPT_PUBLICID == 0) {
                $this->x["tblHasPublicID"][$tbl] = true;
            }
        }
        if (isset($this->x["tblHasPublicID"][$tbl])
            && $this->x["tblHasPublicID"][$tbl] 
            && isset($this->tblI[$tbl])) {
            $chk = SLFields::where('fld_table', $this->tblI[$tbl])
                ->where('fld_name', 'public_id')
                ->first();
            if (!$chk) {
                $this->x["tblHasPublicID"][$tbl] = false;
            }
        }
        return $this->x["tblHasPublicID"][$tbl];
    }
    
    public function getTblRecPublicID($rec, $tbl = '')
    {
        if (trim($tbl) == '') {
            $tbl = $this->coreTbl;
        }
        if ($rec) {
            if ($this->tblHasPublicID($tbl)) {
                if (isset($rec->{ $this->tblAbbr[$tbl] . 'public_id' })) {
                    return $rec->{ $this->tblAbbr[$tbl] . 'public_id' };
                }
            } else {
                if (isset($rec->{ $this->tblAbbr[$tbl] . 'id' })) {
                    return $rec->{ $this->tblAbbr[$tbl] . 'id' };
                }
            }
        }
        return 0;
    }
    
    public function chkInPublicID($pubID = -3, $tbl = '')
    {
        if (intVal($pubID) <= 0) {
            return $pubID;
        }
        if (trim($tbl) == '') {
            $tbl = $this->coreTbl;
        }
        if (!$this->tblHasPublicID($tbl)) {
            return $pubID;
        }
        $pubIdFld = $this->tblAbbr[$tbl] . 'public_id';
        $eval = "\$idChk = " . $this->modelPath($tbl) . "::where('" 
            . $pubIdFld . "', '" . intVal($pubID) . "')->first();";
        eval($eval);
        if ($idChk) {
            return $idChk->getKey();
        }
        return $pubID;
    }
    
    public function genNewCorePubID($tbl = '')
    {
        if (trim($tbl) == '') {
            $tbl = $this->coreTbl;
        }
        if (isset($this->tblAbbr[$tbl])) {
            $pubIdFld = $this->tblAbbr[$tbl] . 'public_id';
            eval("\$idChk = " . $this->modelPath($tbl) 
                . "::orderBy('" . $pubIdFld . "', 'desc')->first();");
            if (!$idChk || !isset($idChk->{ $pubIdFld }) 
                || intVal($idChk->{ $pubIdFld }) <= 0) {
                return 1;
            }
            return (1+intVal($idChk->{ $pubIdFld }));
        }
        return 1;
    }
    
    public function getDbName($dbID = -3)
    {
        if ($dbID <= 0 || sizeof($this->allDbs) == 0) {
            return '';
        }
        foreach ($this->allDbs as $db) {
            if ($db["id"] == $dbID) {
                return $db["name"];
            }
        }
        return '';
    }
    
    public function getCoreTblUserFld()
    {
        if ((!isset($this->coreTblUserFld) || trim($this->coreTblUserFld) == '') 
            && isset($this->tblI[$this->coreTbl])) {
            $coreTblID = $this->tblI[$this->coreTbl];
            $userTbl = SLTables::where('tbl_database', $this->dbID)
                ->whereIn('tbl_name', ['users', 'Users'])
                ->first();
            if ($userTbl && isset($userTbl->tbl_id)) {
                $keyFld = SLFields::where('fld_table', $coreTblID)
                    ->where('fld_foreign_table', $userTbl->tbl_id)
                    ->first();
                if ($keyFld && isset($keyFld->fld_name)) {
                    $this->coreTblUserFld = $this->tblAbbr[$this->coreTbl] 
                        . $keyFld->fld_name;
                }
            }
        }
        return $this->coreTblUserFld;
    }
    
    public function getCoreEmailFld()
    {
        if (isset($this->tblI[$this->coreTbl])) {
            $chk = SLFields::where('fld_database', $this->dbID)
                ->where('fld_table', $this->tblI[$this->coreTbl])
                ->where('fld_name', 'Email')
                ->orderBy('fld_ord', 'asc')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $i => $fld) {
                    return $this->tblAbbr[$this->coreTbl] . $fld->fld_name;
                }
            }
        }
        return '';
    }
    
    public function addFldRowExtends($flds, $tblExtend)
    {
        $flds[] = $this->getFldRowExtendID($tblExtend);
        $exts = SLFields::where('fld_table', $tblExtend)
            ->where('fld_database', $this->dbID)
            ->orderBy('fld_ord', 'asc')
            ->get();
        if ($exts->isNotEmpty()) {
            foreach ($exts as $ext) {
                $ext->fld_name = $this->tblAbbr[$this->tbl[$tblExtend]] 
                    . $ext->fld_name;
                $flds[] = $ext;
            }
        }
        return $flds;
    }
    
    public function getFldRowExtendID($tblExtend)
    {
        $fldRow = new SLFields;
        $fldRow->fld_table = $tblExtend;
        if (isset($this->tbl[$tblExtend])) {
            $t = $this->tbl[$tblExtend];
            if (isset($this->tblAbbr[$t])) {
                $fldRow->fld_name = $this->tblAbbr[$t] . 'ID';
                $fldRow->fld_eng  = $this->tbl[$tblExtend] . ' ID';
                $fldRow->fld_desc = 'Unique ID number of the '
                    . 'record from the other table being extended.';
                $fldRow->fld_type = 'INT';
                $fldRow->fld_key_type = ',Foreign,';
                $fldRow->fld_foreign_table = $tblExtend;
                $fldRow->fld_foreign_min   = '0';
                $fldRow->fld_foreign_max   = 'N';
                $fldRow->fld_foreign2_min  = '1';
                $fldRow->fld_foreign2_max  = '1';
            }
        }
        return $fldRow;
    }
    
    public function getTableFields($tbl = [])
    {
        $flds = [];
        if (isset($tbl->tbl_id) && intVal($tbl->tbl_id) > 0) {
            $flds = SLFields::where('fld_table', $tbl->tbl_id)
                ->orderBy('fld_ord', 'asc')
                ->orderBy('fld_eng', 'asc')
                ->get();
            if (isset($tbl->tbl_extend) 
                && intVal($tbl->tbl_extend) > 0) {
                $flds = $this->addFldRowExtends($flds, $tbl->tbl_extend);
            }
        }
        return $flds;
    }
    
    // not limited to loaded database
    public function getTblFldTypes($tbl)
    {
        $flds = [];
        if (isset($this->fldTypes[$tbl]) 
            && sizeof($this->fldTypes[$tbl]) > 0) {
            $flds = $this->fldTypes[$tbl];
        } else {
            $tblRow = SLTables::where('tbl_name', $tbl)
                ->first();
            if ($tblRow) {
                $chk = SLFields::where('fld_table', $tblRow->tbl_id)
                    ->orderBy('fld_ord', 'asc')
                    ->get();
                if ($chk->isNotEmpty()) {
                    foreach ($chk as $fldRow) {
                        $f = $tblRow->tbl_abbr . $fldRow->fld_name;
                        $flds[$f] = $fldRow->fld_type;
                    }
                }
            }
        }
        return $flds;
    }
    
    public function fldForeignKeyTbl($tbl, $fld)
    {
        if (trim($tbl) == '' || trim($fld) == '' || !isset($this->tblI[$tbl])) {
            return '';
        }
        $abbr = substr($fld, strlen($this->tblAbbr[$tbl]));
        $fld = SLFields::select('fld_foreign_table')
            ->where('fld_table', $this->tblI[$tbl])
            ->where('fld_name', $abbr)
            ->where('fld_foreign_table', '>', 0)
            ->first();
        if ($fld && isset($this->tbl[$fld->fld_foreign_table])) {
            return $this->tbl[$fld->fld_foreign_table];
        }
        return '';
    }
    
    public function getForeignLnk($tbl1, $tbl2 = -3)
    {
        if ($tbl2 <= 0) {
            $tbl2 = $this->treeRow->tree_core_table;
        }
        if (!isset($this->x["foreignLookup"])) {
            $this->x["foreignLookup"] = [];
        }
        $t = $tbl1 . '-' . $tbl2;
        if (!isset($this->x["foreignLookup"][$t])) { 
            $this->x["foreignLookup"][$t] = '';
            $fld = SLFields::select('fld_name')
                ->where('fld_table', $tbl1)
                ->where('fld_foreign_table', $tbl2)
                ->first();
            if ($fld && isset($fld->fld_name)) {
                $this->x["foreignLookup"][$t] = trim($fld->fld_name);
            }
        }
        return $this->x["foreignLookup"][$t];
    }
    
    public function getForeignLnkName($tbl1, $tbl2 = '')
    {
        if (trim($tbl1) == '' || trim($tbl2) == '' 
            || !isset($this->tblI[$tbl1]) || !isset($this->tblI[$tbl2])) {
            return '';
        }
        return $this->getForeignLnk(
            $this->tblI[$tbl1], 
            $this->tblI[$tbl2]
        );
    }
    
    public function getForeignLnkFldName($tbl1, $tbl2 = -3)
    {
        $fldName = $this->getForeignLnk($tbl1, $tbl2);
        if ($fldName != '') {
            return $this->tblAbbr[$this->tbl[$tbl1]] . $fldName;
        }
        return '';
    }
    
    public function getFornNameFldName($tbl1, $tbl2 = '')
    {
        if (trim($tbl1) == '' || trim($tbl2) == '' 
            || !isset($this->tblI[$tbl1]) || !isset($this->tblI[$tbl2])) {
            return '';
        }
        return $this->getForeignLnkFldName(
            $this->tblI[$tbl1], 
            $this->tblI[$tbl2]
        );
    }
    
    protected function chkForeignKey($foreignKey)
    {
        if ($foreignKey && intVal($foreignKey) > 0 
            && isset($this->tbl[$foreignKey])) {
            if (strtolower($this->tbl[$foreignKey]) == 'users') {
                return ['users', 'id'];
            }
            return [
                $this->dbRow->db_prefix . $this->tbl[$foreignKey], 
                $this->tblAbbr[$GLOBALS['SL']->tbl[$foreignKey]] . "id"
            ];
        }
        return ['', ''];
    }
    
    /* public function getLnkTbls($tbl1ID)
    {
        $this->getLinkTblMap($tbl1ID);
    } */
    
    public function getLinkingTables()
    {
        return SLTables::where('tbl_database', $this->dbID)
            ->where('tbl_type', 'Linking')
            ->orderBy('tbl_name', 'asc')
            ->get();
    }

    
    public function getFullFldNameFromID($fldID, $full = true)
    {
        $fld = DB::table('sl_fields')
            ->join('sl_tables', 'sl_fields.fld_table', '=', 'sl_tables.tbl_id')
            ->where('sl_fields.fld_id', $fldID)
            ->select('sl_tables.tbl_name', 'sl_tables.tbl_abbr', 
                'sl_fields.fld_name')
            ->first();
        if ($fld 
            && isset($fld->tbl_abbr) 
            && isset($fld->tbl_abbr) 
            && isset($fld->tbl_name)) {
            return (($full) ? $fld->tbl_name . ':' : '') 
                . $fld->tbl_abbr . $fld->fld_name;
        }
        return '';
    }
    
    public function getFldIDFromFullName($fldName)
    {
        $flds = DB::table('sl_fields')
            ->join('sl_tables', 'sl_fields.fld_table', '=', 'sl_tables.tbl_id')
            ->select('sl_tables.tbl_name', 'sl_tables.tbl_abbr', 
                'sl_fields.fld_name', 'sl_fields.fld_id')
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $f) { // $f->tbl_name . ':' . 
                if ($f && isset($f->tbl_abbr) && isset($f->fld_name)) {
                    $testName = $f->tbl_abbr . $f->fld_name;
                    if ($fldName == $testName) {
                        return $f->fld_id;
                    }
                }
            }
        }
        return -3;
    }
    
    public function getFldRowFromFullName($tbl, $fld)
    {
        if (!isset($this->tblI[$tbl]) || !isset($this->tblAbbr[$tbl])) {
            return [];
        }
        $fldName = substr($fld, strlen($this->tblAbbr[$tbl]));
        return SLFields::where('fld_table', $this->tblI[$tbl])
            ->where('fld_name', $fldName)
            ->first();
    }
    
    public function getFldIDFromFullWritName($tblFld)
    {
        list($tbl, $fld) = explode(':', $tblFld);
        return $this->getFldRowFromFullName($tbl, $fld)->getKey();
    }
    
    public function getFldDefSet($tbl, $fld, $fldRow = NULL)
    {
        $ret = '';
        if (!$fldRow) {
            $fldRow = $this->getFldRowFromFullName($tbl, $fld);
        }
        if ($fldRow && isset($fldRow->fld_values)) {
            $yns = [ 'Y;N', 'N;Y', 'Y;N;?', '0;1', '1;0' ];
            if (strpos($fldRow->fld_values, 'Def::') !== false) {
                $ret = str_replace('Def::', '', $fldRow->fld_values);
            } elseif (in_array($fldRow->fld_values, $yns)) {
                $ret = 'Yes/No';
            }
        }
        return $ret;
    }
    
    public function getFldTitle($tbl, $fld, $fldRow = NULL)
    {
        if (!$fldRow) {
            $fldRow = $this->getFldRowFromFullName($tbl, $fld);
        }
        if ($fldRow && isset($fldRow->fld_eng)) {
            return $fldRow->fld_eng;
        }
        return '';
    }
    
    public function fld2SchemaType($fld = null)
    {
        if (isset($fld->fld_values) 
            && strpos($fld->fld_values, 'Def::') !== false) {
            return 'xs:string';
        }
        if (isset($fld->fld_type)) {
            switch (strtoupper(trim($fld->fld_type))) {
                case 'INT':      return 'xs:integer'; break;
                case 'DOUBLE':   return 'xs:double'; break;
                case 'DATE':     return 'xs:date'; break;
                case 'DATETIME': return 'xs:dateTime'; break;
            } // case 'VARCHAR': case 'TEXT':
        }
        return 'xs:string';
    }
    
    public function fld2SchemaEnumsType($enums)
    {
        if (sizeof($enums) > 0
            && $enums[0] == 0
            && $enums[1] == 1) {
            return 'xs:integer';
        }
        return 'xs:string';
    }

    public function getTblFlds($tbl)
    {
        $ret = [];
        if (isset($this->tblI[$tbl]) 
            && isset($this->fldTypes[$tbl]) 
            && is_array($this->fldTypes[$tbl]) 
            && sizeof($this->fldTypes[$tbl]) > 0) {
            foreach ($this->fldTypes[$tbl] as $fld => $type) {
                $ret[] = $fld;
            }
            /*
            $chk = SLFields::where('fld_table', '=', $this->tblI[$tbl])
                ->where('FldSpecType', '=', 'Unique')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $i => $fld) {
                    $ret[] = $this->tblAbbr[$tbl] . $fld->fld_name;
                }
            }
            */
        }
        return $ret;
    }
    
    public function splitTblFld($tblFld)
    {
        $tbl = $fld = '';
        if (trim($tblFld) != '' && strpos($tblFld, ':') !== false) {
            list($tbl, $fld) = explode(':', $tblFld);
        }
        return [$tbl, $fld];
    }
    
    public function getTblFldID($tblFld)
    {
        list($tbl, $fld) = $this->splitTblFld($tblFld);
        if (trim($tbl) != '' && trim($fld) != '' && isset($this->tblI[$tbl])) {
            $fldName = substr($fld, strlen($this->tblAbbr[$tbl]));
            $fldRow = SLFields::select('fld_id')
                ->where('fld_table', $this->tblI[$tbl])
                ->where('fld_name', $fldName)
                ->first();
            if ($fldRow && isset($fldRow->fld_id)) {
                return $fldRow->fld_id;
            }
        }
        return -3;
    }
    
    public function getTblFldRow($tblFld = '', $tbl = '', $fld = '')
    {
        if ($tbl == '' || $fld == '') {
            list($tbl, $fld) = $this->splitTblFld($tblFld);
        }
        if (trim($tbl) != '' && trim($fld) != '' && isset($this->tblI[$tbl])) {
            $fldName = substr($fld, strlen($this->tblAbbr[$tbl]));
            $fldRow = SLFields::where('fld_table', $this->tblI[$tbl])
                ->where('fld_name', $fldName)
                ->first();
            return $fldRow;
        }
        return null;
    }
    
    public function isHomestead()
    {
        return (strpos($this->sysOpts["app-url"], '.test')     !== false 
            ||  strpos($this->sysOpts["app-url"], '.app')      !== false
            ||  strpos($this->sysOpts["app-url"], '.dev')      !== false
            ||  strpos($this->sysOpts["app-url"], '.local')    !== false
            ||  strpos($this->sysOpts["app-url"], 'localhost') !== false
            ||  strpos($this->sysOpts["app-url"], 'homestead') !== false);
    }
    
    public function getParentDomain()
    {
        if (isset($this->sysOpts["parent-website"]) 
            && trim($this->sysOpts["parent-website"]) != '') {
            return $this->printURLdomain($this->sysOpts["parent-website"]);
        }
        return '';
    }
    
    public function sysHas($type)
    {
        return (isset($this->sysOpts["has-" . $type]) 
            && intVal($this->sysOpts["has-" . $type]) == 1);
    }
    
    public function loadUsrTblRow()
    {
        return SLTables::where('tbl_database', $this->dbID)
            ->where('tbl_eng', 'Users')
            ->first();
    }
    
    public function chkTableExists($coreTbl, $userTbl = null)
    {
        $tbl = $this->dbRow->db_prefix . $coreTbl->tbl_name;
        return \Schema::hasTable($tbl);
        //$chk = DB::select( DB::raw("SHOW TABLES LIKE '" 
        // . $tbl . "'") );
        //if (!$chk || sizeof($chk) == 0) {
        //    return false;
        //}
        //return true;
    }

    public function initPageReadSffx($cid = 0)
    {
        $this->x["pageSlugSffx"] = '/readi-' . $cid;
        if ($this->pageView != '') {
            $this->x["pageSlugSffx"] .= '/' . $this->pageView;
        }
        return $this->x["pageSlugSffx"];
    }

    public function logError($err, $page = '')
    {
        $log = new SLUserActivity;
        if (Auth::user() && Auth::user()->id) {
            $log->user_act_user = Auth::user()->id;
        }
        $log->user_act_curr_page = $page;
        $log->user_act_val = $err;
        $log->save();
        return true;
    }

    public function loadAppUrlParams()
    {
        if ($this->REQ->has('refresh') 
            && intVal($this->REQ->get('refresh')) > 0) {
            $this->pageJAVA .= ' appUrlParams[appUrlParams.length]'
                . ' = new Array("refresh", "' 
                . intVal($this->REQ->get('refresh')) . '"); ';
        }
        return true;
    }

}