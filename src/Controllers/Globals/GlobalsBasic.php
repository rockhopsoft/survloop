<?php
/**
  * GlobalsBasic is a mid-level class to declare the most basic
  * functions which are specifc to SurvLoop (compared to the
  * more generalized functions in GlobalsStatic.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.2.5
  */
namespace SurvLoop\Controllers\Globals;

use DB;
use App\Models\SLFields;
use App\Models\SLTables;
use SurvLoop\Controllers\Globals\GlobalsVars;

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
        if (!isset($this->treeRow->TreeOpts)) {
            return false;
        }
        return $this->chkTreeOpt($this->treeRow->TreeOpts, $type);
    }
    
    public function hasTreeOverride()
    {
        return ($this->treeOverride > 0);
    }
    
    public function dbFullSpecs()
    {
        return ($this->dbRow->DbOpts%3 > 0);
    }

    
    public function isCoreTbl($tblID)
    {
        if (!isset($this->treeRow->TreeCoreTable)) {
            return false;
        }
        return ($tblID == $this->treeRow->TreeCoreTable);
    }
    
    public function coreTblAbbr()
    {
        return ((isset($this->tblAbbr[$this->coreTbl])) 
            ? $this->tblAbbr[$this->coreTbl] : '');
    }
    
    public function coreTblIdFld()
    {
        return $this->coreTblAbbr() . 'ID';
    }
    
    public function getCoreTblUserFld()
    {
        if ((!isset($this->coreTblUserFld) 
            || trim($this->coreTblUserFld) == '') 
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
                    $this->coreTblUserFld 
                        = $this->tblAbbr[$this->coreTbl] 
                        . $keyFld->FldName;
                }
            }
        }
        return $this->coreTblUserFld;
    }
    
    public function getCoreEmailFld()
    {
        if (isset($this->tblI[$this->coreTbl])) {
            $chk = SLFields::where('FldDatabase', $this->dbID)
                ->where('FldTable', $this->tblI[$this->coreTbl])
                ->where('FldName', 'Email')
                ->orderBy('FldOrd', 'asc')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $i => $fld) {
                    return $this->tblAbbr[$this->coreTbl] 
                        . $fld->FldName;
                }
            }
        }
        return '';
    }
    
    public function addFldRowExtends($flds, $tblExtend)
    {
        $flds[] = $this->getFldRowExtendID($tblExtend);
        $exts = SLFields::where('FldTable', $tblExtend)
            ->where('FldDatabase', $this->dbID)
            ->orderBy('FldOrd', 'asc')
            ->get();
        if ($exts->isNotEmpty()) {
            foreach ($exts as $ext) {
                $ext->FldName = $this->tblAbbr[$this->tbl[$tblExtend]] 
                    . $ext->FldName;
                $flds[] = $ext;
            }
        }
        return $flds;
    }
    
    public function getFldRowExtendID($tblExtend)
    {
        $fldRow = new SLFields;
        $fldRow->FldTable = $tblExtend;
        if (isset($this->tbl[$tblExtend])) {
            $t = $this->tbl[$tblExtend];
            if (isset($this->tblAbbr[$t])) {
                $fldRow->FldName = $this->tblAbbr[$t] . 'ID';
                $fldRow->FldEng = $this->tbl[$tblExtend] . ' ID';
                $fldRow->FldDesc = 'Unique ID number of the '
                    . 'record from the other table being extended.';
                $fldRow->FldType = 'INT';
                $fldRow->FldKeyType = ',Foreign,';
                $fldRow->FldForeignTable = $tblExtend;
                $fldRow->FldForeignMin = '0';
                $fldRow->FldForeignMax = 'N';
                $fldRow->FldForeign2Min = '1';
                $fldRow->FldForeign2Max = '1';
            }
        }
        return $fldRow;
    }
    
    public function getTableFields($tbl = [])
    {
        $flds = [];
        if (isset($tbl->TblID) && intVal($tbl->TblID) > 0) {
            $flds = SLFields::where('FldTable', $tbl->TblID)
                ->orderBy('FldOrd', 'asc')
                ->orderBy('FldEng', 'asc')
                ->get();
            if (isset($tbl->TblExtend) 
                && intVal($tbl->TblExtend) > 0) {
                $flds = $this->addFldRowExtends(
                    $flds, 
                    $tbl->TblExtend
                );
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
            $tblRow = SLTables::where('TblName', $tbl)
                ->first();
            if ($tblRow) {
                $chk = SLFields::where('FldTable', $tblRow->TblID)
                    ->orderBy('FldOrd', 'asc')
                    ->get();
                if ($chk->isNotEmpty()) {
                    foreach ($chk as $fldRow) {
                        $f = $tblRow->TblAbbr . $fldRow->FldName;
                        $flds[$f] = $fldRow->FldType;
                    }
                }
            }
        }
        return $flds;
    }
    
    public function fldForeignKeyTbl($tbl, $fld)
    {
        if (trim($tbl) == '' || trim($fld) == '' 
            || !isset($this->tblI[$tbl])) {
            return '';
        }
        $abbr = substr($fld, strlen($this->tblAbbr[$tbl]));
        $fld = SLFields::select('FldForeignTable')
            ->where('FldTable', $this->tblI[$tbl])
            ->where('FldName', $abbr)
            ->where('FldForeignTable', '>', 0)
            ->first();
        if ($fld 
            && isset($this->tbl[$fld->FldForeignTable])) {
            return $this->tbl[$fld->FldForeignTable];
        }
        return '';
    }
    
    public function getForeignLnk($tbl1, $tbl2 = -3)
    {
        if ($tbl2 <= 0) {
            $tbl2 = $this->treeRow->TreeCoreTable;
        }
        if (!isset($this->x["foreignLookup"])) {
            $this->x["foreignLookup"] = [];
        }
        $t = $tbl1 . '-' . $tbl2;
        if (!isset($this->x["foreignLookup"][$t])) { 
            $this->x["foreignLookup"][$t] = '';
            $fld = SLFields::select('FldName')
                ->where('FldTable', $tbl1)
                ->where('FldForeignTable', $tbl2)
                ->first();
            if ($fld && isset($fld->FldName)) {
                $this->x["foreignLookup"][$t] = trim($fld->FldName);
            }
        }
        return $this->x["foreignLookup"][$t];
    }
    
    public function getForeignLnkName($tbl1, $tbl2 = '')
    {
        if (trim($tbl1) == '' || trim($tbl2) == '' 
            || !isset($this->tblI[$tbl1]) 
            || !isset($this->tblI[$tbl2])) {
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
    
    public function getForeignLnkNameFldName($tbl1, $tbl2 = '')
    {
        if (trim($tbl1) == '' || trim($tbl2) == '' 
            || !isset($this->tblI[$tbl1]) 
            || !isset($this->tblI[$tbl2])) {
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
                $this->dbRow->DbPrefix . $this->tbl[$foreignKey], 
                $this->tblAbbr[$GLOBALS['SL']->tbl[$foreignKey]] . "ID"
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
        return SLTables::where('TblDatabase', $this->dbID)
            ->where('TblType', 'Linking')
            ->orderBy('TblName', 'asc')
            ->get();
    }

    
    public function getFullFldNameFromID($fldID, $full = true)
    {
        $fld = DB::table('SL_Fields')
            ->join('SL_Tables', 'SL_Fields.FldTable', 
                '=', 'SL_Tables.TblID')
            ->where('SL_Fields.FldID', $fldID)
            ->select('SL_Tables.TblName', 'SL_Tables.TblAbbr', 
                'SL_Fields.FldName')
            ->first();
        if ($fld && isset($fld->TblAbbr)) {
            return (($full) ? $fld->TblName . ':' : '') 
                . $fld->TblAbbr . $fld->FldName;
        }
        return '';
    }
    
    public function getFldIDFromFullName($fldName)
    {
        $flds = DB::table('SL_Fields')
            ->join('SL_Tables', 'SL_Fields.FldTable', 
                '=', 'SL_Tables.TblID')
            ->select('SL_Tables.TblName', 'SL_Tables.TblAbbr', 
                'SL_Fields.FldName', 'SL_Fields.FldID')
            ->get();
        if ($flds->isNotEmpty()) {
            foreach ($flds as $f) { // $f->TblName . ':' . 
                $testName = $f->TblAbbr . $f->FldName;
                if ($fldName == $testName) {
                    return $f->FldID;
                }
            }
        }
        return -3;
    }
    
    public function getFldRowFromFullName($tbl, $fld)
    {
        if (!isset($this->tblI[$tbl]) 
            || !isset($this->tblAbbr[$tbl])) {
            return [];
        }
        $fldName = substr($fld, strlen($this->tblAbbr[$tbl]));
        return SLFields::where('FldTable', $this->tblI[$tbl])
            ->where('FldName', $fldName)
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
        if ($fldRow && isset($fldRow->FldValues)) {
            $yns = ['Y;N', 'N;Y', 'Y;N;?', '0;1', '1;0'];
            if (strpos($fldRow->FldValues, 'Def::') !== false) {
                $ret = str_replace('Def::', '', $fldRow->FldValues);
            } elseif (in_array($fldRow->FldValues, $yns)) {
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
        if ($fldRow && isset($fldRow->FldEng)) {
            return $fldRow->FldEng;
        }
        return '';
    }
    
    public function fld2SchemaType($fld)
    {
        if (strpos($fld->FldValues, 'Def::') !== false) {
            return 'xs:string';
        }
        switch (strtoupper(trim($fld->FldType))) {
            case 'INT':      return 'xs:integer'; break;
            case 'DOUBLE':   return 'xs:double'; break;
            case 'DATE':     return 'xs:date'; break;
            case 'DATETIME': return 'xs:dateTime'; break;
        } // case 'VARCHAR': case 'TEXT':
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
            $chk = SLFields::where('FldTable', '=', $this->tblI[$tbl])
                ->where('FldSpecType', '=', 'Unique')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $i => $fld) {
                    $ret[] = $this->tblAbbr[$tbl] . $fld->FldName;
                }
            }
            */
        }
        return $ret;
    }
    
    public function splitTblFld($tblFld)
    {
        $tbl = $fld = '';
        if (trim($tblFld) != '' 
            && strpos($tblFld, ':') !== false) {
            list($tbl, $fld) = explode(':', $tblFld);
        }
        return [$tbl, $fld];
    }
    
    public function getTblFldID($tblFld)
    {
        list($tbl, $fld) = $this->splitTblFld($tblFld);
        if (trim($tbl) != '' && trim($fld) != '' 
            && isset($this->tblI[$tbl])) {
            $fldName = substr($fld, strlen($this->tblAbbr[$tbl]));
            $fldRow = SLFields::select('FldID')
                ->where('FldTable', $this->tblI[$tbl])
                ->where('FldName', $fldName)
                ->first();
            if ($fldRow && isset($fldRow->FldID)) {
                return $fldRow->FldID;
            }
        }
        return -3;
    }
    
    public function getTblFldRow($tblFld = '', $tbl = '', $fld = '')
    {
        if ($tbl == '' || $fld == '') {
            list($tbl, $fld) = $this->splitTblFld($tblFld);
        }
        if (trim($tbl) != '' && trim($fld) != '' 
            && isset($this->tblI[$tbl])) {
            $fldName = substr($fld, strlen($this->tblAbbr[$tbl]));
            $fldRow = SLFields::where('FldTable', $this->tblI[$tbl])
                ->where('FldName', $fldName)
                ->first();
            return $fldRow;
        }
        return null;
    }
    
    public function isHomestead()
    {
        return (strpos($this->sysOpts["app-url"], '.test') !== false 
            || strpos($this->sysOpts["app-url"], '.app') !== false
            || strpos($this->sysOpts["app-url"], '.dev') !== false
            || strpos($this->sysOpts["app-url"], '.local') !== false
            || strpos($this->sysOpts["app-url"], 'localhost') !== false
            || strpos($this->sysOpts["app-url"], 'homestead') !== false);
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
        return SLTables::where('TblDatabase', $this->dbID)
            ->where('TblEng', 'Users')
            ->first();
    }
    
    public function chkTableExists($coreTbl, $userTbl = null)
    {
        $tbl = $this->dbRow->DbPrefix . $coreTbl->TblName;
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
        $this->x["pageSlugSffx"] = '/read-' . $cid;
        if ($this->pageView != '') {
            $this->x["pageSlugSffx"] .= '/' . $this->pageView;
        }
        return $this->x["pageSlugSffx"];
    }





}