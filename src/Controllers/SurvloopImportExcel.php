<?php
/**
  * SurvloopImportExcel is a class which aid imports from Excel.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.18
  */
namespace RockHopSoft\Survloop\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
//use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use App\Models\SLFields;
use App\Models\SLTables;

class SurvloopImportExcel
{
    protected $folder     = 'api/excel/';
    protected $file       = 'tmp.xls';

    public $arr           = null;
    public $tblEng        = 'Excel Import';
    public $tblName       = 'excel_import_';
    public $tblAbbr       = 'ei_';
    public $tblNew        = true;
    public $tblObj        = true;
    public $dataFlds      = [];
    public $dataRowsAdded = 0;

    private $rowData      = [];
    private $row2heads    = [];

    public function __construct($tblEng = '', $tblName = '', $tblAbbr = '')
    {
        if (trim($tblEng) != '') {
            $this->tblEng = $tblEng;
        } else {
            $this->tblEng .= ' ' . date("Ymd");
        }
        if (trim($tblName) != '') {
            $this->tblName = $tblName;
        } else {
            $this->tblName = $GLOBALS["SL"]->slugify($this->tblEng, '_');
        }
        if (trim($tblAbbr) != '') {
            $this->tblAbbr = $tblAbbr;
        } else {
            $this->tblAbbr = '';
            $words = $GLOBALS["SL"]->mexplode('_', $this->tblName);
            $chars = 3;
            if (sizeof($words) > 2) {
                $chars = 2;
            }
            foreach ($words as $word) {
                $this->tblAbbr .= substr($word, 0, $chars) . '_';
            }
        }
    }

    public function upload($fldName = '')
    {
        if (!$GLOBALS["SL"]->REQ->hasFile('importExcel')) {
            return 'File was not successfully uploaded.';
        }
        $ret = '';
        $upFold = '../storage/app/' . $this->folder;
        $ext = $GLOBALS["SL"]->REQ->file($fldName)
            ->getClientOriginalExtension();
        $this->file = 'import-excel-' . date('ymj') 
            . '-' . rand(10000000, 100000000) . '.' . $ext;
        if (file_exists($upFold . $this->file)) {
            unlink($upFold . $this->file);
        }
        $GLOBALS["SL"]->REQ->file($fldName)
            ->move($upFold, $this->file);
        return $upFold . $this->file;
    }

    public function uploadToArray($fldName = '')
    {
        $filePath = $this->upload($fldName);
        $this->arr = (new CollectionImportExcel)->toArray($filePath);
        return $this->arr;
    }

    public function loadFile($file = '')
    {
        $this->file = trim($file);
        $filePath = '../storage/app/' . $this->folder . $this->file;
        if ($this->file != '' && file_exists($filePath)) {
            $this->arr = (new CollectionImportExcel)->toArray($filePath);
        }
        return $this->arr;
    }

    public function loadFldNames(Request $request)
    {
        if ($request->has('import') 
            && trim($request->get('import')) == 'fldNames'
            && $request->has('file')
            && trim($request->get('file')) != ''
            && $request->has('tblEng')
            && trim($request->get('tblEng')) != '') {
            $this->loadFile($request->get('file'));
            if (sizeof($this->arr) > 0
                && sizeof($this->arr[0]) > 0
                && sizeof($this->arr[0][0]) > 0) {
                $this->loadFldNamesMakeTbl($request);
                $this->dataRowsAdded = 0;
                $this->dataFlds 
                    = $this->row2heads 
                    = [];
                foreach ($this->arr[0][0] as $col => $colHeader) {
                    if (trim($colHeader) != ''
                        && $request->has('fldImport' . $col)
                        && intVal($request->get('fldImport' . $col)) == 1) {
                        $this->dataFlds[] = $this->loadFldNamesMakeFld($request, $col);
                    } else {
                        $this->dataFlds[] = null;
                    }
                }
                $GLOBALS["SL"]->createTableIfNotExists($this->tblObj);
            }
            echo '<script type="text/javascript"> '
                . 'setTimeout("window.location=\'?import=dataRows&file=' 
                . trim($request->get('file')) . '&tblID=' 
                . $this->tblObj->tbl_id . '\'", 1000); </script>';
            exit;
        }
        return false;
    }

    public function loadDataRows(Request $request)
    {
        if ($request->has('import') 
            && trim($request->get('import')) == 'dataRows'
            && $request->has('file')
            && trim($request->get('file')) != ''
            && $request->has('tblID')
            && intVal($request->get('tblID')) > 0) {
            $this->tblObj = SLTables::find(intVal($request->get('tblID')));
            if ($this->tblObj && isset($this->tblObj->tbl_id)) {
                $this->tblEng  = $this->tblObj->tbl_eng;
                $this->tblName = $this->tblObj->tbl_name;
                $this->tblAbbr = $this->tblObj->tbl_abbr;
                $this->dataFlds 
                    = $this->row2heads 
                    = [];
                $this->loadFile($request->get('file'));
                foreach ($this->arr[0][0] as $col => $colHeader) {
                    if (trim($colHeader) != '') {
                        $this->dataFlds[] = SLFields::where('fld_table', $this->tblObj->tbl_id)
                            ->where('fld_eng', 'LIKE', $colHeader)
                            ->first();
                    } else {
                        $this->dataFlds[] = null;
                    }
                }
                if (sizeof($this->arr) > 0
                    && sizeof($this->arr[0]) > 0
                    && sizeof($this->arr[0][0]) > 0) {
                    $startRow = 1;
                    if (sizeof($this->row2heads) > (sizeof($this->arr[0][0])/3)) {
                        $startRow = 2;
                    }
                    for ($row = $startRow; $row < sizeof($this->arr[0]); $row++) {
                        $this->loadFldNamesSaveRow($row);
                    }
                }
            }
        }
        return $this->dataRowsAdded;
    }

    private function loadFldNamesMakeTbl(Request $request)
    {
        $this->tblObj= new SLTables;
        $this->tblObj->tbl_name = trim($request->get('tblName'));
        $chk = SLTables::where('tbl_name', $this->tblObj->tbl_name)
            ->where('tbl_database', $GLOBALS["SL"]->dbID)
            ->first();
        if ($chk && isset($chk->tbl_id)) {
            $this->tblObj = $chk;
            return $this->tblObj;
        }
        $ord = 0;
        $chk = SLTables::where('tbl_database', $GLOBALS["SL"]->dbID)
            ->select('tbl_ord')
            ->orderBy('tbl_ord', 'desc')
            ->first();
        if ($chk && isset($chk->tbl_ord)) {
            $ord = 1+intVal($chk->tbl_ord);
        }
        $this->tblObj->tbl_abbr     = trim($request->get('tblAbbr'));
        $this->tblObj->tbl_eng      = trim($request->get('tblEng'));
        $this->tblObj->tbl_database = $GLOBALS["SL"]->dbID;
        $this->tblObj->tbl_ord      = $ord;
        $this->tblObj->tbl_type     = 'Data';
        $this->tblObj->tbl_group    = 'Excel Import';
        $this->tblObj->tbl_extend   = 0;
        $this->tblObj->tbl_opts     = 1;
        $this->tblObj->save();
        return $this->tblObj;
    }

    private function loadFldNamesMakeFld(Request $request, $col)
    {
        list($type, $dataType, $length) = $this->getColType($col);
        $fld = new SLFields;
        if ($request->has('fldName' . $col)) {
            $fld->fld_name = trim($request->get('fldName' . $col));
        }
        $chk = SLFields::where('fld_name', $fld->fld_name)
            ->where('fld_database', $GLOBALS["SL"]->dbID)
            ->where('fld_table', $this->tblObj->tbl_id)
            ->first();
        if ($chk && isset($chk->fld_id)) {
            return $fld;
        }
        if ($request->has('fldEng' . $col)) {
            $fld->fld_eng  = trim($request->get('fldEng' . $col));
        }
        $fld->fld_database      = $GLOBALS["SL"]->dbID;
        $fld->fld_table         = $this->tblObj->tbl_id;
        $fld->fld_ord           = $col;
        $fld->fld_type          = $type;
        $fld->fld_data_type     = $dataType;
        $fld->fld_data_length   = $length;
        $fld->fld_spec_type     = 'Unique';
        $fld->fld_foreign_table = -3;
        $fld->fld_unique        = 0;
        $fld->fld_null_support  = 1;
        $fld->fld_opts          = 1;
        $fld->save();
        return $fld;
    }

    public function getColType($col)
    {
        $colTypes = [ "empty" => 0 ];
        foreach ($GLOBALS["SL"]->getVarTypeList() as $type) {
            $colTypes[$type] = 0;
        }
        $typeRow1 = '';
        for ($row = 1; $row < sizeof($this->arr[0]); $row++) {
            if (isset($this->arr[0][$row])
                && sizeof($this->arr[0][$row]) > 0
                && isset($this->arr[0][$row][$col])
                && trim($this->arr[0][$row][$col]) != '') {
                $cell = trim($this->arr[0][$row][$col]);
                $type = $GLOBALS["SL"]->getVarType($cell);
                $colTypes[$type]++;
                if ($row == 1) {
                    $typeRow1 = $type;
                }
            } else {
                $colTypes["empty"]++;
            }
        }
        if ($colTypes["textLong"] > 0) {
            return [ 'TEXT', 'Alphanumeric', 0 ];
        }
        if ($colTypes["float"] > 0) {
            if ($colTypes["text"] == 0 && $colTypes["textLong"] == 0) {
                return [ 'DOUBLE', 'Numeric', 0 ];
            }
            if ((($colTypes["text"] == 1 && $colTypes["textLong"] == 0)
                    || ($colTypes["text"] == 0 && $colTypes["textLong"] == 1))
                && in_array($typeRow1, ['text', 'textLong'])) {
                $this->row2heads[] = $col;
                return [ 'DOUBLE', 'Numeric', 0 ];
            }
        }
        if ($colTypes["int"] > 0) {
            if ($colTypes["text"] == 0 && $colTypes["textLong"] == 0) {
                return [ 'INT', 'Numeric', 11 ];
            }
            if ((($colTypes["text"] == 1 && $colTypes["textLong"] == 0)
                    || ($colTypes["text"] == 0 && $colTypes["textLong"] == 1))
                && in_array($typeRow1, ['text', 'textLong'])) {
                $this->row2heads[] = $col;
                return [ 'INT', 'Numeric', 11 ];
            }
        }
        return [ 'VARCHAR', 'Alphanumeric', 255 ];
    }

    private function loadFldNamesSaveRow($row)
    {
        $colCnt = 0;
        eval("\$this->rowData[\$row] = new " 
            . $GLOBALS["SL"]->modelPath($this->tblObj->tbl_name) . ";");
        foreach ($this->arr[0][0] as $col => $colHeader) {
            $val = trim($this->arr[0][$row][$col]);
            if (trim($colHeader) != ''
                && $this->dataFlds[$col] !== null
                && $val != '') {
                $dataFldName = $this->tblAbbr . $this->dataFlds[$col]->fld_name;
                $this->rowData[$row]->{ $dataFldName } = $val;
                $colCnt++;
            }
        }
        if ($colCnt > 0) {
            $this->rowData[$row]->save();
            $this->dataRowsAdded++;
        }
        return true;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getPathFile()
    {
        return '../storage/app/' . $this->folder . $this->file;
    }

}

class CollectionImportExcel implements ToModel
{
    use Importable;

    public function model(array $row)
    {
    }
}
