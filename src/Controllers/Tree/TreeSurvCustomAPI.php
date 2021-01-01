<?php
/**
  * TreeSurvCustomAPI extends a standard branching tree, for maps of more customized
  * API exports, instead of those fully built using Survloop functionality.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.17
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\SLNode;
use App\Models\SLFields;
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use RockHopSoft\Survloop\Controllers\Tree\TreeCoreSess;

class TreeSurvCustomAPI extends TreeSurvAPI
{

    public function printRecordCustomAPI($name = 'custom_api', $type = 'xml', $id = '')
    {
        $ret = '';
        $coreTbl = $GLOBALS["SL"]->coreTbl;
        if (isset($this->v[$name])
            && isset($this->v[$name]->apiTables)
            && sizeof($this->v[$name]->apiTables) > 0
            && isset($this->sessData)
            && isset($this->sessData->dataSets)
            && isset($this->sessData->dataSets[$coreTbl])
            && sizeof($this->sessData->dataSets[$coreTbl]) == 1) {
            if ($id == '') {
                $id = $this->sessData->dataSets[$coreTbl][0]->getKey();
            }
            $ret .= '<' . $this->v[$name]->coreSingle . ' id="' . $id . '">';
            foreach ($this->v[$name]->apiTables as $i => $apiTbl) {
            	$tbl = '';
            	if (trim($apiTbl->loopTbl) != ''
            		&& isset($this->sessData->dataSets[$apiTbl->loopTbl])
        			&& sizeof($this->sessData->dataSets[$apiTbl->loopTbl]) > 0) {
            		$tbl = trim($apiTbl->loopTbl);
            	} elseif (trim($apiTbl->table) != ''
                		&& isset($this->sessData->dataSets[$apiTbl->table])
            			&& sizeof($this->sessData->dataSets[$apiTbl->table]) > 0) {
            		$tbl = trim($apiTbl->table);
            	}
//echo '<pre>'; print_r($apiTbl); ; echo '</pre>';
                if ($i == 0) {
                	if ($tbl != '') {
                		foreach ($this->sessData->dataSets[$tbl] as $tblRec) {
                    		$ret .= $this->printRecValuesCustomAPI($i, $name, $type, $tbl, $tblRec);
                    	}
                    }
                } elseif ($tbl != '') {
                    $ret .= '<' . $apiTbl->table . '>';
//echo '<br />loopTbl ' . $apiTbl->table . ' <pre>'; print_r($this->sessData->dataSets[$tbl]); echo '</pre> ';
                	foreach ($this->sessData->dataSets[$tbl] as $l => $loopRec) {
                		if (!$apiTbl->inline) {
                    		$ret .= '<' . $apiTbl->singular . '>';
                    	}
                    	$ret .= $this->printRecValuesCustomAPI($i, $name, $type, $tbl, $loopRec);
                		if (!$apiTbl->inline) {
                    		$ret .= '</' . $apiTbl->singular . '>';
                    	}
                	}
                    $ret .= '</' . $apiTbl->table . '>';
                }

            }
            $ret .= '</' . $this->v[$name]->coreSingle . '>';
        }
//exit;
        return $ret;
    }

    public function printRecValuesCustomAPI($i, $name = 'custom_api', $type = 'xml', $tbl = '', $rec = null)
    {
    	$ret = '';
        if ($rec
        	&& isset($this->v[$name]->apiTables[$i]->apiFields)
            && sizeof($this->v[$name]->apiTables[$i]->apiFields) > 0) {
        	$id = $rec->getKey();
        	$this->sessData->startTmpDataBranch($tbl, $id);

            foreach ($this->v[$name]->apiTables[$i]->apiFields as $j => $apiFld) {
                $label = $this->v[$name]->apiTables[$i]->singular . '_' . $apiFld->label;
            	$val = $this->overrideRecordValueCustomAPI($name, $type, $tbl, $rec, $apiFld);
            	if ($val != '') {
                	$ret .= '<' . $label . '>' . $val . '</' . $label . '>';
            	} elseif ($apiFld->fld 
                    && isset($apiFld->fld->fld_table)
                    && intVal($apiFld->fld->fld_table) > 0
                    && isset($GLOBALS["SL"]->tbl[$apiFld->fld->fld_table])) {
                    $fldTbl = $GLOBALS["SL"]->tbl[$apiFld->fld->fld_table];
                    $fldTblAbbr = $GLOBALS["SL"]->tblAbbr[$fldTbl];
                    $fldName = $fldTblAbbr . $apiFld->fld->fld_name;
	                $val = $this->genXmlFormatVal($rec, $apiFld->fld, $fldTblAbbr);
//if ($apiFld->fld->fld_name == 'summary') { echo 'fld:table: ' . $apiFld->fld->fld_table . ', fldTbl: ' . $fldTbl . ', fldTblAbbr: ' . $fldTblAbbr . ', fldName: ' . $fldName . ', val: ' . $val . ', id: ' . $id . ',<pre>'; print_r($this->sessData->getChildRows($tbl, $id, $fldTbl)); print_r($rec); echo '</pre>'; }
	                if ($val !== false && trim($val) != '') {
                		$ret .= '<' . $label . '>' . $val . '</' . $label . '>';
	                } else {
	                    // this is only checking one depth down, so far:
	                    $vals = [];
	                    $kids = $this->sessData->getChildRows($tbl, $id, $fldTbl);
	                    if ($kids && sizeof($kids) > 0) {
	                    	foreach ($kids as $kid) {
	                    		$val = $this->genXmlFormatVal($kid, $apiFld->fld, $fldTblAbbr);
	                			if ($val !== false && trim($val) != '') {
	                    			$vals[] = $val;
	                    		}
	                    	}
	                    }
	                    if (sizeof($vals) > 0
	                		&& (sizeof($vals) > 1 || trim($vals[0]) != '')) {
	                    	$ret .= '<' . $label . '>' . implode(', ', $vals) . '</' . $label . '>';
	                    }
	                }
                } else {
                	$ret .= $this->noFieldCoreRecordCustomAPI($name, $type, $apiFld);
                }
            }
			$this->sessData->endTmpDataBranch($tbl);
        }
        return $ret;
    }

    public function noFieldCoreRecordCustomAPI($name = 'custom_api', $type = 'xml', $apiFld = null)
    {
    	$ret = '';
    	return $ret;
    }

    public function overrideRecordValueCustomAPI($name = 'custom_api', $type = 'xml', $tbl = '', $rec = null, $apiFld = null)
    {
    	return '';
    }

}



/**
  * TreeCustomAPI is a helper class for creating custom API exports.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.17
  */

class TreeCustomAPI
{
    public $apiName      = '';
    public $apiDesc      = '';
    public $corePlural   = '';
    public $coreSingle   = '';
    public $apiSchema 	 = '';
    public $apiNamespace = '';
    public $apiTables    = [];
    public $apiTblLookup = [];

    public function __construct($name = '', $plural = '', $singular = '', $desc = '')
    {
        $this->apiName    = $name;
        $this->apiDesc    = $desc;
        $this->corePlural = $plural;
        $this->coreSingle = $singular;
        $GLOBALS["SL"]->x["tmpApiCnt"] = 0;
    }

    public function setSchema($apiSchema, $apiNamespace = '')
    {
    	$this->apiSchema    = trim($apiSchema);
    	$this->apiNamespace = trim($apiNamespace);
    	if ($this->apiNamespace == '') {
    		$this->apiNamespace = $GLOBALS["SL"]->sysOpts["app-url"];
    	}
    	return true;
    }

    public function addTable($table = '', $singular = '', $loopTbl = '', $inline = false)
    {
        $this->apiTblLookup[$table] = sizeof($this->apiTables);
        $this->apiTables[] = new TreeCustomTableAPI($table, $singular, $loopTbl, $inline);
        return true;
    }

    public function addFldID($table = '', $fldID = 0)
    {
        $this->apiTables[$this->apiTblLookup[$table]]->addNodeFld($fldID);
        return true;
    }

    public function addFld($table = '', $label = '', $labelEng = '', $desc = '', $enums = [], $fldID = 0)
    {
        $ind = $this->apiTblLookup[$table];
        $this->apiTables[$ind]->addNode($label, $labelEng, $desc, $fldID, $enums);
        return true;
    }

    public function fldNameStrReplace($before = '', $after = '')
    {
        if (sizeof($this->apiTables) > 0) {
            foreach ($this->apiTables as $i => $apiTbl) {
                if (isset($this->apiTables[$i]->apiFields)
                    && sizeof($this->apiTables[$i]->apiFields) > 0) {
                    foreach ($this->apiTables[$i]->apiFields as $j => $apiFld) {
                        $this->apiTables[$i]->apiFields[$j]->nameStrReplace($before, $after);
                    }
                }
            }
        }
        return true;
    }

    public function returnSchema($type = 'xml', $filename = '', $url = '')
    {
        return view('vendor.survloop.admin.tree.' . $type . '-schema-custom', [
            "type"         => $type,
            "url"          => $url,
            "apiName"      => $this->apiName,
            "apiDesc"      => $this->apiDesc,
            "corePlural"   => $this->corePlural,
            "coreSingle"   => $this->coreSingle,
            "apiTables"    => $this->apiTables,
            "apiTblLookup" => $this->apiTblLookup,
            "apiSchema"    => $this->apiSchema,
    		"apiNamespace" => $this->apiNamespace
        ])->render();
    }
    
}


class TreeCustomTableAPI
{
    public $table     = '';
    public $singular  = '';
    public $loopTbl   = '';
    public $apiFields = [];
    public $inline 	  = false;

    public function __construct($table = '', $singular = '', $loopTbl = '', $inline = false)
    {
        $this->table    = $table;
        $this->singular = $singular;
        $this->loopTbl  = $loopTbl;
        $this->inline 	= $inline;
    }
    
    public function addNode($label = '', $labelEng = '', $desc = '', $fldID = 0, $enums = [])
    {
        $this->apiFields[] = new TreeCustomFieldAPI($label, $labelEng, $desc, $fldID, $enums);
        return true;
    }

    public function addNodeFld($fldID = 0)
    {
        $label = $labelEng = $desc = '';
        $enums = [];
        if ($fldID > 0) {
            $fld = SLFields::find($fldID);
            if ($fld && isset($fld->fld_name) && trim($fld->fld_name) != '') {
                $label    = $fld->fld_name;
                $labelEng = $fld->fld_eng;
                $desc     = $fld->fld_desc;
                $enums    = $this->addNodeFldEnums($fld);
            }
        }
        $this->addNode($label, $labelEng, $desc, $fldID, $enums);
        return true;
    }
    
    protected function addNodeFldEnums($fld)
    {
        $enums = [];
        if (isset($fld->fld_values) && trim($fld->fld_values) != '') {
            if (strpos($fld->fld_values, 'Def::') !== false) {
                $defSet = trim(str_replace('Def::', '', 
                    str_replace('DefX::', '', $fld->fld_values)
                ));
                $defs = $GLOBALS["SL"]->def->getSet($defSet);
                foreach ($defs as $def) {
                    if (isset($def->def_value) && trim($def->def_value) != '') {
                        $enums[] = $def->def_value;
                    }
                }
            } else {
                $enums = $GLOBALS["SL"]->mexplode(';', $fld->fld_values);
            }
        }
        return $enums;
    }
    
    public function printFlds($type = 'xml')
    {
        $ret = '';
        if (sizeof($this->apiFields) > 0) {
            foreach ($this->apiFields as $apiFld) {
                if ($type == 'eng') {
                    $ret .= '<div class="p20"></div>';
                }
                $ret .= $apiFld->printFld($type, $this->singular . '_');
            }
        }
        return $ret;
    }

}


class TreeCustomFieldAPI
{
    public $ind        = 0;
    public $parentInd  = 0;
    public $fldID      = 0;
    public $label      = '';
    public $labelEng   = '';
    public $desc       = '';
    public $enums      = [];
    public $fld        = null;
    public $elemType   = 'xs:string';

    public function __construct($label = '', $labelEng = '', $desc = '', $fldID = 0, $enums = [])
    {
        $this->ind = $GLOBALS["SL"]->x["tmpApiCnt"];
        $GLOBALS["SL"]->x["tmpApiCnt"]++;
        $this->fldID      = $fldID;
        $this->label      = $label;
        $this->labelEng   = $labelEng;
        $this->desc       = $desc;
        $this->enums      = $enums;
        if ($fldID > 0) {
            $this->fld        = SLFields::find($fldID);
            $this->elemType   = $GLOBALS["SL"]->fld2SchemaType($this->fld);
        }
    }

    public function nameStrReplace($before = '', $after = '')
    {
        $this->label = str_replace($before, $after, $this->label);
        return $this->label;
    }
    
    public function printFld($type = 'xml', $tblPrefix = '')
    {
        return view('vendor.survloop.admin.tree.' . $type . '-schema-custom-fld', [
            "type"       => $type,
            "tblPrefix"  => $tblPrefix,
            "fld"        => $this->fld,
            "label"      => $this->label,
            "labelEng"   => $this->labelEng,
            "desc"       => $this->desc,
            "enums"      => $this->enums,
            "elemType"   => $this->elemType
        ])->render();
    }
    

}
