<?php
namespace SurvLoop\Controllers;

use Cache;
use Auth;
use Illuminate\Http\Request;

use SurvLoop\Models\SLTables;
use SurvLoop\Models\SLFields;
use SurvLoop\Models\SLDefinitions;
use SurvLoop\Models\SLBusRules;
use SurvLoop\Models\SLLogActions;

use SurvLoop\Controllers\SurvLoopData;
use SurvLoop\Controllers\DatabaseLookups;
use SurvLoop\Controllers\AdminController;

class AdminDBController extends AdminController
{
	/******************************************************
	*** Initializing Foundation for this Admin Area
	******************************************************/
	
	protected $dbID 			= 1;
	protected $dbRow 			= array();
	protected $dbTitle 			= ''; 
	protected $dbSubTitle 		= '';
	
	protected $dbPrivs 			= array();
	
	protected function initExtra(Request $request)
	{
		$this->v["DbID"] = $this->dbID = $GLOBALS["DB"]->dbID;
		$this->dbTitle = '<span class="f40 red">' . $GLOBALS["DB"]->dbRow->DbName . '&nbsp;</span>';
		$this->dbSubTitle = '<span class="f14 red">' . $GLOBALS["DB"]->dbRow->DbDesc . '</span>';
		$this->v["dbAllowEdits"] = $this->v["user"]->hasRole('administrator|databaser');
		$this->v["mission"] = view( 'vendor.survloop.inc-mission-statement', array("DbMission" => $GLOBALS["DB"]->dbRow->DbMission));
		if (trim($this->v["currPage"]) == '') $this->v["currPage"] = '/dashboard/db';
		$this->v["help"] = '<span class="f10 gryA">?</span>&nbsp;&nbsp;&nbsp;';
		$this->loadLookups();
		return true;
	}
	
	protected function cacheFlush()
	{
		Cache::flush();
		return true;
	}
	
	protected function loadLookups()
	{
		$runChecks = false;
		if (!session()->has('dbDesignChecks')) session()->put('dbDesignChecks', 0);
		else session()->put('dbDesignChecks', (1+session()->get('dbDesignChecks')));
		if (session()->get('dbDesignChecks')%10 == 0) $runChecks = true;  // moderating cleanup to periodic page loads
		
		$this->v["FldDataTypes"] = array();
		$this->v["FldDataTypes"]['VARCHAR'] 	= array('Text/String (255 characters max)', 'Text');
		$this->v["FldDataTypes"]['TEXT'] 		= array('Long Text/String', 				'Text-Long');
		$this->v["FldDataTypes"]['INT'] 		= array('Integer', 							'Number');
		$this->v["FldDataTypes"]['DOUBLE'] 		= array('Decimal/Large Number', 			'Number-Decimals');
		$this->v["FldDataTypes"]['DATE'] 		= array('Date', 							'Date');
		$this->v["FldDataTypes"]['DATETIME'] 	= array('Date and Time', 					'Date&Time');
		
		$tbls = SLTables::select('TblID', 'TblName', 'TblEng', 'TblAbbr', 'TblOpts')
			->where('TblDatabase', $this->dbID)
			->orderBy('TblOrd', 'asc')
			->get();
		if ($tbls && sizeof($tbls) > 0)
		{
			foreach ($tbls as $tbl)
			{
				if ($runChecks)
				{
					if ($tbl->TblOpts%3 == 0) $tbl->TblOpts = $tbl->TblOpts/3;
					$keyFlds = SLFields::where('FldTable', $tbl->TblID)
						->where('FldKeyType', 'LIKE', '%Primary%')
						->first();
					if ($keyFlds && sizeof($keyFlds) > 0) $tbl->TblOpts *= 3;
					$tbl->save();
				}
			}
		}
		
		$this->v["dbBusRulesFld"] = array();
		$busRules = SLBusRules::select('RuleID', 'RuleStatement', 'RuleFields')
			->where('RuleDatabase', $this->dbID)
			->get();
		if ($busRules && sizeof($busRules) > 0)
		{
			foreach ($busRules as $rule)
			{
				$fldList = $this->mexplode(',', $rule->RuleFields);
				if (sizeof($fldList) > 0)
				{
					foreach ($fldList as $fldID)
					{
						$this->v["dbBusRulesFld"][intVal($fldID)] = array($rule->ruleID, $rule->RuleStatement);
					}
				}
			}
		}
		if ($runChecks) $this->refreshTableStats();
		$this->v["dbStats"] = $this->printDbStats();
		return true;
	}
	
	protected function refreshTableStats()
	{
		$tblForeigns = array();
		if (sizeof($GLOBALS["DB"]->tbls) > 0)
		{
			foreach ($GLOBALS["DB"]->tbls as $tblID)
			{
				$tblForeigns[$tblID] = array(0, 0, 0);
			}
		}
		$flds = SLFields::select('FldTable', 'FldForeignTable')
			->where('FldTable', '>', 0)
			->where('FldDatabase', $this->dbID)
			->get();
		if ($flds && sizeof($flds) > 0)
		{
			foreach ($flds as $fld)
			{
				if (isset($tblForeigns[$fld->FldTable]))
				{
					$tblForeigns[$fld->FldTable][0]++;
					if ($fld->FldForeignTable > 0 
						&& isset($tblForeigns[$fld->FldForeignTable]))
					{
						$tblForeigns[$fld->FldTable][1]++;
						$tblForeigns[$fld->FldForeignTable][2]++;
					}
				}
			}
		}
		foreach ($tblForeigns as $tblID => $tblTots)
		{
			SLTables::find($tblID)->update([ 'TblNumFields' => $tblTots[0], 'TblNumForeignKeys' => $tblTots[1], 'TblNumForeignIn' => $tblTots[2] ]);
		}
		$tbls = SLTables::select('TblID')->where('TblDatabase', $this->dbID)->get();
		$flds = SLFields::select('FldID')->where('FldDatabase', $this->dbID)->get();
		$GLOBALS["DB"]->dbRow->update([ 'DbTables' => sizeof($tbls), 'DbFields' => sizeof($flds) ]);
		return true;
	}
	
	protected function loadDefOpts()
	{
		$this->v["dbDefOpts"] = array();
		$defs = SLDefinitions::where('DefSet', 'Value Ranges')->where('DefDatabase', $this->dbID)->get();
		if ($defs && sizeof($defs) > 0)
		{
			foreach ($defs as $def)
			{
				if (!isset($this->v["dbDefOpts"][$def->DefSubset])) $this->v["dbDefOpts"][$def->DefSubset] = array('');
				$this->v["dbDefOpts"][$def->DefSubset][0] .= ';'.$def->DefValue;
				$this->v["dbDefOpts"][$def->DefSubset][] = $def->DefValue;
			}
			foreach ($this->v["dbDefOpts"] as $subset => $vals)
			{
				$this->v["dbDefOpts"][$subset][0] = substr($this->v["dbDefOpts"][$subset][0], 1);
			}
		}
		return true;
	}
	
	protected function getDefOpts($item = '', $link = 0)
	{
		if (sizeof($this->v["dbDefOpts"]) == 0) $this->loadDefOpts();
		return $this->v["dbDefOpts"][$item][0];
	}
	
	function logActions($actions = array())
	{
		$log = new SLLogActions;
		$log->LogDatabase = $this->dbID;
		$log->LogUser = Auth::user()->id;
		$log->save();
		$log->update($actions);
		return true;
	}
	

	
	
	/******************************************************
	*** Main Pages Called by Routes
	******************************************************/
	
	protected function isPrintView()
	{
		return (($this->REQ->has('print')) ? 'vendor.survloop.admin.db.dbprint' : 'vendor.survloop.admin.admin');
	}
	
	public function index(Request $request)
	{
		$this->admControlInit($request);
		return $this->printOverview();
	}
	
	public function printOverview()
	{
		$this->loadTblGroups();
		return view( 'vendor.survloop.admin.db.overview', $this->v );
	}
	
	public function full(Request $request)
	{
		$cache = '/dashboard/db/all';
		$this->v["onlyKeys"] = $request->has('onlyKeys');
		if ($this->v["onlyKeys"]) $cache .= '.onlyKeys';
		$this->admControlInit($request);
		if (!$this->checkCache($cache)) {
			$this->loadTblGroups();
			$this->loadTblForeigns();
			$this->loadTblRules();
			$GLOBALS["DB"]->loadFldAbout();
			$this->v["basicTblFlds"] = $this->v["basicTblDescs"] = array();
			if (sizeof($this->v["groupTbls"]) > 0)
			{
				foreach ($this->v["groupTbls"] as $group => $tbls)
				{
					foreach ($tbls as $tbl)
					{
						$this->v["basicTblFlds"][$tbl->TblID] = $this->printBasicTblFlds($tbl->TblID, (($this->v["isExcel"]) ? -1 : 2));
						$this->v["basicTblDescs"][$tbl->TblID] = $this->printBasicTblDesc($tbl, 
							((isset($this->v["tblForeigns"][$tbl->TblID])) ? $this->v["tblForeigns"][$tbl->TblID] : ''));
					}
				}
			}
			$this->v["innerTable"] = view( 'vendor.survloop.admin.db.full-innerTable', $this->v );
			// this shouldn't be needed, why is it happening?..
			//$this->v["innerTable"] = str_replace('&lt;', '<', str_replace('&gt;', '>', 
			//	str_replace('"&quot;', '"', str_replace('&quot;"', '"', $this->v["innerTable"]))));
			if ($this->v["isExcel"])
			{
				$this->exportExcelOldSchool('<tr><td colspan=5 ><b>Complete Database Table Field Listings</b></td></tr>'
										. $this->v["innerTable"], 'FullTableListings'.date("ymd").'.xls');
				exit;
			}
			$this->v["genericFlds"] = array();
			if (!$this->v["isPrint"] && !$this->v["isExcel"])
			{ 			// though shouldn't be here if is Excel
				$genericFlds = SLFields::where('FldSpecType', 'Generic')->where('FldDatabase', $this->dbID)->get();
				if ($genericFlds && sizeof($genericFlds) > 0)
				{
					foreach ($genericFlds as $cnt => $fld)
					{
						$this->v["genericFlds"][] = $this->printBasicTblFldRow($fld, -3, 2);
					}
				}
			}
			$this->v["content"] = view( 'vendor.survloop.admin.db.full', $this->v )->render();
			$this->saveCache();
		}
		return view( 'vendor.survloop.admin.admin', $this->v );
	}
	
	public function viewTable(Request $request, $tblName)
	{
		$this->admControlInit($request, '/dashboard/db/all');
		return $this->printViewTable($tblName);
	}
	
	public function printViewTable($tblName)
	{
		$this->v["tblName"] = $tblName;
		$this->v["tbl"] = SLTables::where('TblName', $tblName)->where('TblDatabase', $this->dbID)->first();
		//echo 'tbl: ' . $tblName . ', <pre>'; print_r($this->v["tbl"]); echo '</pre>';
		if (trim($tblName) == '' || !$this->v["tbl"] || sizeof($this->v["tbl"]) == 0) return $this->index($this->REQ);
		$this->v["rules"] = SLBusRules::where('RuleTables', 'LIKE', '%,'.$this->v["tbl"]->TblID.',%')->get();
		$this->v["flds"] = SLFields::where('FldTable', $this->v["tbl"]->TblID)->where('FldTable', '>', 0)->where('FldDatabase', $this->dbID)->orderBy('FldOrd', 'asc')->get();
		$this->v["foreignsFlds"] = '';
		$foreignsFlds = SLFields::where('FldForeignTable', $this->v["tbl"]->TblID)->where('FldTable', '>', 0)->where('FldDatabase', $this->dbID)->orderBy('FldID', 'asc')->get();
		if (sizeof($foreignsFlds) > 0)
		{
			foreach ($foreignsFlds as $cnt => $foreign)
			{
				$this->v["foreignsFlds"] .= (($cnt > 0) ? ', ' : '') . $this->getTblName($foreign->FldTable);
			}
		}
		$this->v["basicTblFlds"] = $this->printBasicTblFlds($this->v["tbl"]->TblID, 1, $this->v["flds"]);
		return view( 'vendor.survloop.admin.db.tableView', $this->v );
	}
	
	public function printEditTable($tblName = '')
	{
		if (!$this->v["dbAllowEdits"]) return $this->printOverview();
		$this->v["tblName"] = $tblName;
		$this->v["tbl"] = new SLTables;
		if (trim($tblName) != '') $this->v["tbl"] = SLTables::where('TblName', $tblName)->where('TblDatabase', $this->dbID)->first();
		
		if ($this->REQ->has('tblEditForm'))
		{
			if ($this->REQ->has('deleteTbl'))
			{
				SLFields::where('FldTable', $this->v["tbl"]->TblID)->delete();
				$this->v["tbl"]->delete();
				return $this->printOverview();
			}
			$logActions = array('LogAction' => 'Edit', 'LogTable' => $this->v["tbl"]->TblID, 'logField' => 0, 'logOldName' => $this->v["tbl"]->TblName, 'logNewName' => $this->REQ->TblName);
			if (trim($tblName) == '')
			{
				$logActions["LogAction"] = 'New';
				$this->v["tbl"]->TblDatabase = $this->dbID;
			}
			$this->v["tbl"]->TblName 	= $this->REQ->TblName;
			$this->v["tbl"]->TblEng 	= $this->REQ->TblEng;
			$this->v["tbl"]->TblAbbr 	= $this->REQ->TblAbbr;
			$this->v["tbl"]->TblDesc 	= $this->REQ->TblDesc;
			$this->v["tbl"]->TblNotes 	= $this->REQ->TblNotes;
			$this->v["tbl"]->TblGroup 	= $this->REQ->TblGroup;
			$this->v["tbl"]->TblType 	= $this->REQ->TblType;
			$this->v["tbl"]->save();
			$this->logActions($logActions);
			$this->cacheFlush();
			return $this->printViewTable($this->v["tbl"]->TblName);
		}
		
		return view( 'vendor.survloop.admin.db.tableEdit', $this->v );
	}
	
	
	public function printEditField($tblAbbr = '', $fldName = '')
	{
		$this->v["fldName"] = $fldName;
		$this->v["tbl"] = SLTables::where('TblAbbr', $tblAbbr)
			->where('TblDatabase', $this->dbID)
			->first();
		if (!$this->v["dbAllowEdits"] || !isset($this->v["tbl"]->TblID))
		{
			return $this->printOverview();
		}
		$fld = new SLFields;
		if (trim($fldName) != '')
		{
			$fld = SLFields::where('FldName', $fldName)
				->where('FldTable', $this->v["tbl"]->TblID)
				->where('FldDatabase', $this->dbID)
				->first();
		}
		else
		{
			$fld->FldDatabase 	= $this->dbID;
			$fld->FldTable 		= $this->v["tbl"]->TblID;
		}
		
		// Check invalid starting points
		if (intVal($fld->FldOpts) == 0) 			$fld->FldOpts = 1;
		if (intVal($fld->FldCompareSame) == 0) 		$fld->FldCompareSame = 1;
		if (intVal($fld->FldCompareOther) == 0) 	$fld->FldCompareOther = 1;
		if (intVal($fld->FldCompareValue) == 0) 	$fld->FldCompareValue = 1;
		if (intVal($fld->FldOperateSame) == 0) 		$fld->FldOperateSame = 1;
		if (intVal($fld->FldOperateOther) == 0) 	$fld->FldOperateOther = 1;
		if (intVal($fld->FldOperateValue) == 0) 	$fld->FldOperateValue = 1;
		
		if ($this->REQ->has('fldEditForm'))
		{
			//echo '<br /><br /><br />addTableFld<br />';
			$this->cacheFlush();
			$logActions = [
				'logAction' 	=> 'Edit', 
				'LogTable' 		=> $this->v["tbl"]->TblID, 
				'logField' 		=> $fld->FldID, 
				'logOldName' 	=> $fld->FldName, 
				'logNewName' 	=> $this->REQ->FldName
			];
			if ($this->REQ->has('delete'))
			{
				$logActions["LogAction"] = 'Delete';
				$fld->delete();
			}
			else 
			{ 	// not deleting...
				if (trim($fldName) == '')
				{
					$logActions["LogAction"] = 'New';
					$ordChk = SLFields::where('FldDatabase', $this->dbID)
						->where('FldTable', $this->v["tbl"]->TblID)
						->orderBy('FldOrd', 'desc')
						->first();
					if ($ordChk && sizeof($ordChk) > 0)
					{
						$fld->FldOrd 		= 1+$ordChk->FldOrd;
					}
				}
				
				$fld->FldEng 				= $this->REQ->FldEng;
				$fld->FldName 				= $this->REQ->FldName;
				$fld->FldDesc 				= $this->REQ->FldDesc;
				$fld->FldNotes 				= $this->REQ->FldNotes;
				$fld->FldType 				= $this->REQ->FldType;
				$fld->FldKeyType 			= ',';
				$fld->FldForeignTable 		= $this->REQ->FldForeignTable;
				$fld->FldForeignMin			= $this->REQ->FldForeignMin;
				$fld->FldForeignMax 		= $this->REQ->FldForeignMax;
				$fld->FldForeign2Min 		= $this->REQ->FldForeign2Min;
				$fld->FldForeign2Max 		= $this->REQ->FldForeign2Max;
				$fld->FldIsIndex 			= $this->REQ->FldIsIndex;
				$fld->FldValues 			= $this->REQ->FldValues;
				$fld->FldDefault 			= $this->REQ->FldDefault;
				$fld->FldSpecType 			= $this->REQ->FldSpecType;
				$fld->FldSpecSource 		= $this->REQ->FldSpecSource;
				$fld->FldNullSupport 		= $this->REQ->FldNullSupport;
				$fld->FldOpts 				= 1;
				if ($GLOBALS["DB"]->dbFullSpecs())
				{
					$fld->FldAlias 			= $this->REQ->FldAlias;
					$fld->FldDataType 		= $this->REQ->FldDataType;
					$fld->FldDataLength 	= $this->REQ->FldDataLength;
					$fld->FldDataDecimals 	= $this->REQ->FldDataDecimals;
					$fld->FldInputMask 		= $this->REQ->FldInputMask;
					$fld->FldDisplayFormat 	= $this->REQ->FldDisplayFormat;
					$fld->FldKeyStruct 		= $this->REQ->FldKeyStruct;
					$fld->FldEditRule 		= $this->REQ->FldEditRule;
					$fld->FldUnique 		= $this->REQ->FldUnique;
					$fld->FldNullSupport 	= $this->REQ->FldNullSupport;
					$fld->FldValuesEnteredBy = $this->REQ->FldValuesEnteredBy;
					$fld->FldRequired 		= $this->REQ->FldRequired;
					$fld->FldCompareSame 	= $fld->FldCompareOther = $fld->FldCompareValue = 1;
					$fld->FldOperateSame 	= $fld->FldOperateOther = $fld->FldOperateValue = 1;
					$fld->FldCharSupport 	= ',';
					if (sizeof($this->REQ->FldCharSupport) > 0)
					{
						foreach ($this->REQ->FldCharSupport  as $val) $fld->FldCharSupport .= $val.',';
					}
					foreach (['FldCompareSame', 'FldCompareOther', 'FldCompareValue', 'FldOperateSame', 'FldOperateOther', 'FldOperateValue'] as $co)
					{
						if ($this->REQ->has($co) && sizeof($this->REQ->input($co)) > 0)
						{
							if (in_array(3, $this->REQ->input($co))) $fld->{$co} = 6;
							else
							{
								foreach ($this->REQ->input($co) as $val) $fld->{$co} *= $val;
								foreach (array(5, 7, 11, 13, 17, 19) as $cod)
								{
									if ($fld->{$co}%$cod == 0) $fld->{$co} *= $fld->{$co}/$cod;
								}
							}
						}
					}
				}
				if ($this->REQ->has('FldValuesDefX') && trim($this->REQ->FldValuesDefX) == 'X') $fld->FldOpts *= 5;
				
				if ($this->REQ->has('FldKeyType') && sizeof($this->REQ->FldKeyType) > 0)
				{
					print_r($this->REQ->FldKeyType);
					foreach ($this->REQ->FldKeyType as $val) $fld->FldKeyType .= $val.',';
				}
				if ($this->REQ->FldSpecType == 'Generic' || ($this->REQ->has('saveGeneric') && $this->REQ->saveGeneric == 1)) $fld->FldTable = 0;
				if ($this->REQ->has('FldValuesDef') && trim($this->REQ->FldValuesDef) != '') $fld->FldValues = 'Def::'.$this->REQ->FldValuesDef;
				
				$fld->save();
				//echo '<br /><br /><br />addTableFld: ' . $fld->id . '<br />';
				
				if ($this->REQ->has('pushGeneric') && intVal($this->REQ->pushGeneric) == 1) {
					$replicaFlds = SLFields::where('FldSpecSource', $fld->FldID)->where('FldDatabase', $this->dbID)->get();
					if ($replicaFlds && sizeof($replicaFlds) > 0) {
						foreach ($replicaFlds as $replica) {
							$genericCopy = $fld->replicate()->save();
							$genericCopy->FldSpecType 		= 'Replica';
							$genericCopy->FldSpecSource 	= $fld->FldID;
							$genericCopy->FldTable 			= $replica->FldTable;
							$genericCopy->FldOrd 			= $replica->FldOrd;
							$genericCopy->save();
							$replica->delete();
						}
					}
				}
			}
			
			$this->logActions($logActions);
			$this->refreshTableStats();
			if ($fld->FldTable > 0) return redirect('/dashboard/db/table/'.$this->v["tbl"]->TblName);
			else return $this->printViewTable('Generic');
		}
		
		$this->v["fld"] = $fld;
		$this->v["fullFldSpecs"] = $this->fullFldSpecs($this->v["fld"], $this->v["dbAllowEdits"]);
		return view( 'vendor.survloop.admin.db.fieldEdit', $this->v );
	}
	
	protected function fullFldSpecs($fld = array(), $edit = false)
	{
		$this->loadDefSets();
		$this->loadDefOpts();
		$this->loadGenerics();
		$this->v["fld"] = $fld;
		$this->v["edit"] = $edit;
		$this->v["chkDis"] = (($edit) ? '' : ' disabled ');
		$this->v["fldSfx"] = (intVal($this->v["fld"]->FldID) > 0) ? $this->v["fld"]->FldID : 'New';
		$this->v["forKeyChange"] = '';
		$this->v["FldSpecSourceJSlnk"] = '?tbl=' . $this->REQ->tbl . '&' . (($this->REQ->has('edit')) ? 'edit='.$this->REQ->edit : 'add=1');
		$this->v["defSet"] = ((strpos($fld->FldValues, 'Def::') !== false || strpos($fld->FldValues, 'DefX::') !== false) 
			? trim(str_replace('Def::', '', str_replace('DefX::', '', $fld->FldValues))) : '');
		$GLOBALS["DB"]->loadFldAbout();
		//echo '<pre>'; print_r($this->v["dbDefOpts"]); echo '</pre>';
		return view( 'vendor.survloop.admin.db.fieldSpecifications', $this->v );
	}
	
	public function businessRules(Request $request)
	{
		$this->admControlInit($request, '/dashboard/db/bus-rules');
		if ($this->REQ->has('delRule') && $this->REQ->delRule > 0 && $this->v["dbAllowEdits"])
		{
			$delRule = SLBusRules::find($this->REQ->delRule);
			if ($delRule && sizeof($delRule) > 0) $delRule->delete();
		}
		$this->v["rules"] = SLBusRules::where('RuleDatabase', $this->dbID)->orderBy('RuleTables', 'asc')->orderBy('RuleFields', 'asc')->get();
		$this->v["ruleTbls"] = array();
		if (sizeof($this->v["rules"]) > 0)
		{
			foreach ($this->v["rules"] as $rule)
			{
				$this->v["ruleTbls"][] = $this->tblListID2Link($rule->RuleTables);
			}
		}
		return view( 'vendor.survloop.admin.db.rules', $this->v );
	}

	public function ajaxTblSelector($rT = '')
	{
		$this->v["addT"] = 0;
		$this->v["tblDrop"] = $this->getTblDropOpts();
		if (trim($rT) == '') $rT = ',';
		if ($this->REQ->has('addT') && trim($this->REQ->addT) != '' && strpos($rT, ','.$this->REQ->addT.',') === false) $rT .= $this->REQ->addT.',';
		if ($this->REQ->has('delT') && trim($this->REQ->delT) != '') $rT = str_replace(','.$this->REQ->delT.',', ',', $rT);
		$this->v["rT"] = trim($rT);
		$this->v["tblList"] = $this->mexplode(',', $this->v["rT"]);
		if (sizeof($this->v["tblList"]) > 0)
		{
			foreach ($this->v["tblList"] as $i => $tbl)
			{
				$this->v["tblList"][$i] = array(intVal(trim($tbl)));
				$this->v["tblList"][$i][1] = $this->getTblName($tbl, 1, '', ' target="_blank"');
			}
		}
		return view( 'vendor.survloop.admin.db.ajaxTblFldSelectorT', $this->v );
	}

	public function ajaxFldSelector($rF = '')
	{
		$this->v["addT"] = 0;
		if ($this->REQ->has('addT')) $this->v["addT"] = intVal($this->REQ->addT);
		$this->v["tblDrop"] = $this->getTblDropOpts($this->v["addT"], '(select table first)');
		$this->v["fldDrop"] = $this->getFldDropOpts($this->v["addT"]);
		if ($this->REQ->has('addF') && trim($this->REQ->addF) != '' && strpos($rF, ','.$this->REQ->addF.',') === false) $rF .= $this->REQ->addF.',';
		if ($this->REQ->has('delF') && trim($this->REQ->delF) != '') $rF = str_replace(','.$this->REQ->delF.',', ',', $rF);
		$this->v["rF"] = trim($rF);
		$this->v["fldList"] = $this->getFldArr($this->v["rF"]);
		foreach ($this->v["fldList"] as $i => $fld)
		{
			$this->v["fldList"][$i] = array($fld->FldID, $this->getTblName($fld->FldTable, 1, '', ' target="_blank"').':&nbsp;' . $fld->FldName);
		}
		return view( 'vendor.survloop.admin.db.ajaxTblFldSelectorF', $this->v );
	}
	
	
	public function definitions(Request $request)
	{
		$this->admControlInit($request);
		$this->v["defSets"] = array();
		// how does groupBy('DefSubset')->  work?..
		$defs = SLDefinitions::where('DefSet', 'Value Ranges')->where('DefDatabase', $this->dbID)
								->orderBy('DefSubset', 'asc')->orderBy('DefOrder', 'asc')->orderBy('DefValue', 'asc')->get();
		//echo '<pre>'; print_r($defs); echo '</pre>';
		if ($defs && sizeof($defs) > 0)
		{
			foreach ($defs as $cnt => $def)
			{
				if (!isset($this->v["defSets"][$def->DefSubset])) $this->v["defSets"][$def->DefSubset] = array();
				$this->v["defSets"][$def->DefSubset][] = $def;
			}
		}
		return view( 'vendor.survloop.admin.db.definitions', $this->v );
	}
	
	public function defAdd(Request $request, $set = '')
	{
		$this->admControlInit($request, '/dashboard/db/definitions');
		return $this->printDefEdit(-3, ((trim($set) != '') ? urldecode($set) : ''));
	}
	
	public function defEdit(Request $request, $defID)
	{
		$this->admControlInit($request, '/dashboard/db/definitions');
		return $this->printDefEdit($defID);
	}
	
	public function printDefEdit($defID = -3, $subset = '')
	{
		if (!$this->v["dbAllowEdits"]) return $this->printOverview();
		$this->v["defID"] 	= $defID;
		$this->v["subset"] 	= $subset;
		$this->v["def"] 	= new SLDefinitions;
		if ($defID > 0) 
		{
			$this->v["def"] = SLDefinitions::where('DefID', $defID)
				->where('DefDatabase', $this->dbID)
				->first();
		}
		else
		{
			$this->v["def"]->DefSubset 		= $subset;
			$this->v["def"]->DefDatabase 	= $this->dbID;
			$this->v["def"]->DefOrder 		= 0;
		}
		
		if ($this->REQ->has('defEditForm'))
		{
			$this->cacheFlush();
			if ($this->REQ->has('deleteDef'))
			{
				$this->v["def"]->delete();
				return redirect('/dashboard/db/definitions');
			}
			if (trim($subset) != '' && $defID <= 0)
			{
				$setVals = SLDefinitions::where('DefSubset', $subset)
					->where('DefSet', 'Value Ranges')
					->where('DefDatabase', $this->dbID)
					->get();
				$this->v["def"]->DefOrder = sizeof($setVals);
			}
			$this->v["def"]->DefSet 		= 'Value Ranges';
			$this->v["def"]->DefSubset 		= $this->REQ->defSubset;
			if ($this->REQ->defSubset == '_' && $this->REQ->has('newSubset'))
			{
				$this->v["def"]->DefSubset = $this->REQ->newSubset;
			}
			$this->v["def"]->DefValue 		= $this->REQ->defValue;
			$this->v["def"]->DefDescription = $this->REQ->defDescription;
			$this->v["def"]->save();
			return redirect('/dashboard/db/definitions');
		}
		
		$this->v["subList"] = SLDefinitions::select('DefSubset')
			->distinct()
			->where('DefSet', 'Value Ranges')
			->where('DefDatabase', $this->dbID)
			->orderBy('DefSubset')
			->get();
		return view( 'vendor.survloop.admin.db.defEdit', $this->v );
	}
	
	public function ruleAdd(Request $request)
	{
		$this->admControlInit($request, '/dashboard/db/bus-rules');
		return $this->printRuleEdit(-3);
	}
	
	public function ruleEdit(Request $request, $ruleID)
	{
		$this->admControlInit($request, '/dashboard/db/bus-rules');
		return $this->printRuleEdit($ruleID);
	}
	
	public function printRuleEdit($ruleID = -3)
	{
		$this->v["ruleID"] = $ruleID;
		$this->v["rule"] = new SLBusRules;
		if ($ruleID > 0) $this->v["rule"] = SLBusRules::where('RuleID', $ruleID)
			->where('RuleDatabase', $this->dbID)
			->first();
		else
		{
			$this->v["rule"]->RuleDatabase = $this->dbID;
			$this->v["rule"]->RuleTables = $this->v["rule"]->RuleFields = ',';
		}
		$primeFlds = array('RuleTestOn', 'RulePhys', 'RuleLogic', 'RuleRel'); 
		
		
		if ($this->REQ->has('ruleEditForm') && $this->v["dbAllowEdits"])
		{
			$this->cacheFlush();
			//echo 'ruleID: ' . $ruleID . '<pre>'; print_r($this->v["rule"]); echo '</pre>';
			$this->v["rule"]->RuleStatement = $this->REQ->RuleStatement;
			$this->v["rule"]->RuleConstraint = $this->REQ->RuleConstraint;
			$this->v["rule"]->RuleAction = $this->REQ->RuleAction;
			$this->v["rule"]->RuleTables = $this->REQ->RuleTables;
			$this->v["rule"]->RuleFields = $this->REQ->RuleFields;
			$this->v["rule"]->RuleTestOn = $this->v["rule"]->RulePhys = $this->v["rule"]->RuleLogic = $this->v["rule"]->RuleRel = 1;
			foreach ($primeFlds as $fld)
			{
				if ($this->REQ->has($fld) && sizeof($this->REQ->input($fld)) > 0)
				{
					foreach ($this->REQ->input($fld) as $prime) eval("\$this->v['rule']->".$fld." *= \$prime;");
				}
			}
			if ($this->REQ->has('RuleType23') && intVal($this->REQ->RuleType23 > 0)) $this->v["rule"]->RuleType *= intVal($this->REQ->RuleType23);
			if ($this->REQ->has('RuleType57') && intVal($this->REQ->RuleType57 > 0)) $this->v["rule"]->RuleType *= intVal($this->REQ->RuleType57);
			$this->v["rule"]->save();
			//echo '<br /><br />saving: <pre>'; print_r($this->v["rule"]); echo '</pre>';
		}
		
		$this->v["tblTxt"] = ((isset($this->v["rule"])) ? $this->tblListID2Link($this->v["rule"]->RuleTables) : '');
		$this->v["fldTxt"] = ((isset($this->v["rule"])) ? $this->fldListID2Link($this->v["rule"]->RuleFields) : '');
		$this->v["saveBtn"] = '';
		if ($this->v["dbAllowEdits"])
		{
			if ($this->v["ruleID"] <= 0) $this->v["saveBtn"] = '<input type="submit" value="Add New Rule" class="btn btn-lg btn-primary" >';
			else $this->v["saveBtn"] = '<input type="submit" value="Save Rule Changes" class="btn btn-primary" >';
		}
		$GLOBALS["DB"]->loadFldAbout();
		return view( 'vendor.survloop.admin.db.ruleSpecifications', $this->v );
	}
	
	
	
	public function defSort(Request $request, $subset = '')
	{
		$this->admControlInit($request, '/dashboard/db/definitions');
		if (!$this->v["dbAllowEdits"]) return $this->printOverview();
		$this->v["subset"] = urldecode($subset);
		if ($this->REQ->has('saveOrder'))
		{
			$this->cacheFlush();
			if ($this->REQ->has('item') && sizeof($this->REQ->input('item')) > 0)
			{
				foreach ($this->REQ->input('item') as $i => $value)
				{
					$def = SLDefinitions::find($value);
					$def->DefOrder = $i;
					$def->save();
				}
			}
			exit;
		}
		
		$sortTitle = '<a href="/dashboard/db/definitions/sort/' . $subset . '" style="font-size: 26px;"><b>' . $this->v["subset"] . '</b></a>';
		$submitURL = '/dashboard/db/definitions/sort/' . $subset . '?saveOrder=1';
		$defs = SLDefinitions::where('DefSubset', $this->v["subset"])->where('DefSet', 'Value Ranges')->where('DefDatabase', $this->dbID)->orderBy('DefOrder')->get();
		$sorts = array();
		if ($defs && sizeof($defs) > 0)
		{
			foreach ($defs as $def) $sorts[] = array($def->DefID, $def->DefValue);
		}
		$this->v["sortable"] = view( 'vendor.survloop.inc-sortable', ['sortTitle' => $sortTitle, 'submitURL' => $submitURL, 'sorts' => $sorts] );
		return view( 'vendor.survloop.admin.db.defSort', $this->v );
	}
	
	public function tblSort(Request $request)
	{
		$this->admControlInit($request, '/dashboard/db/all');
		if (!$this->v["dbAllowEdits"]) return $this->printOverview();
		
		if ($this->REQ->has('saveOrder'))
		{
			$this->cacheFlush();
			if ($this->REQ->has('item') && sizeof($this->REQ->input('item')) > 0)
			{
				foreach ($this->REQ->input('item') as $i => $value)
				{
					$tbl = SLTables::find($value);
					$tbl->TblOrd = $i;
					$tbl->save();
				}
			}
			exit;
		}
		
		$sortTitle = '<a href="/dashboard/db/table/sort" style="font-size: 26px;"><b>All Tables</b></a>
			<div class="f12 slBlueDark">
				Table Name <span class="f10">Type</span>
				<div class="disIn gry9" style="margin-left: 50px;"><i>Table Group</i></div>
			</div>';
		$submitURL = '/dashboard/db/sortTable?saveOrder=1';
		$tbls = SLTables::select('TblID', 'TblEng', 'TblType', 'TblGroup')
			->where('TblDatabase', $this->dbID)
			->orderBy('TblOrd')
			->get();
		$sorts = array();
		if ($tbls && sizeof($tbls) > 0)
		{
			foreach ($tbls as $tbl)
			{
				$sorts[] = [
					$tbl->TblID, 
					$tbl->TblEng . ' <span style="font-size: 10px;">' 
					. $tbl->TblType . '</span><div class="fR"><i>'
					. '<span class="gry9" style="font-size: 12px;">'
					. $tbl->TblGroup . '</span></i></div><div class="fC"></div>'
				];
			}
		}
		$this->v["sortable"] = view( 'vendor.survloop.inc-sortable', [
			'sortTitle' => $sortTitle, 
			'submitURL' => $submitURL, 
			'sorts' => $sorts
		]);
		return view( 'vendor.survloop.admin.db.tableSort', $this->v );
	}
	
	public function fldSort(Request $request, $tblName = '') 
	{
		$this->admControlInit($request, '/dashboard/db/all');
		if (!$this->v["dbAllowEdits"] || trim($tblName) == '')
		{
			return $this->printOverview();
		}
		$this->v["tblName"] = $tblName;
		$tbl = SLTables::where('TblName', $tblName)
			->where('TblDatabase', $this->dbID)
			->first();
		
		if ($this->REQ->has('saveOrder'))
		{
			$this->cacheFlush();
			if ($this->REQ->has('item') 
				&& sizeof($this->REQ->input('item')) > 0)
			{
				foreach ($this->REQ->input('item') as $i => $value)
				{
					$fld = SLFields::find($value);
					$fld->FldOrd = $i;
					$fld->save();
				}
			}
			exit;
		}
		
		$sortTitle = '<a href="/dashboard/db/table/sort" style="font-size: 26px;"><b>' 
			. $tbl->TblName . '&nbsp;&nbsp;&nbsp;(' . $tbl->TblAbbr . ')</b></a>';
		$submitURL = '/dashboard/db/table/'.$tblName.'/sort?saveOrder=1';
		$flds = SLFields::select('FldID', 'FldEng', 'FldName', 'FldType', 'FldForeignTable')
			->where('FldTable', $tbl->TblID)
			->where('FldDatabase', $this->dbID)
			->orderBy('FldOrd')
			->orderBy('FldEng', 'asc')
			->get();
		$sorts = array();
		if ($flds && sizeof($flds) > 0)
		{
			foreach ($flds as $fld)
			{
				$sorts[] = [
					$fld->FldID, 
					$fld->FldEng . ' <span style="font-size: 10px;">' 
						. (($fld->FldForeignTable) ? '<i class="fa fa-link"></i>' : '') . '</span>'
						. '<div class="fR"><i><span class="gry9" style="font-size: 12px;">'
						. '<span style="font-size: 8px;">('.$fld->FldType.')</span> '
						. $fld->FldName . '</span></i></div><div class="fC"></div>'
				];
			}
		}
		$this->v["sortable"] = view( 'vendor.survloop.inc-sortable', [
			'sortTitle' => $sortTitle, 
			'submitURL' => $submitURL, 
			'sorts' => $sorts
		]);
		return view( 'vendor.survloop.admin.db.fieldSort', $this->v );
	}
	
	public function fieldDescs(Request $request, $view = '')
	{
		$this->admControlInit($request, '/dashboard/db/all');
		return $this->printFieldDescs($view, false);
	}
	
	public function fieldDescsAll(Request $request, $view = '')
	{
		$this->admControlInit($request, '/dashboard/db/all');
		return $this->printFieldDescs($view, true);
	}
	
	public function printFieldDescs($view = '', $all = false)
	{
		if (!$this->v["dbAllowEdits"]) return $this->printOverview();
		$this->loadDefOpts();
		$this->v["FldDescsView"] = $view;
		$this->v["FldDescsViewAll"] = $all;
		$this->v["fldTots"] = [ [0, 0], [0, 0], [0, 0] ]; // unique, replica, generic
		$flds = SLFields::select('FldDesc', 'FldSpecType')
			->where('FldSpecType', 'NOT LIKE', 'Generic')
			->where('FldDatabase', $this->dbID)
			->get();
		if ($flds && sizeof($flds) > 0)
		{
			foreach ($flds as $fld) 
			{
				$FldType = (($fld->FldSpecType == 'Generic') ? 2 
					: (($fld->FldSpecType == 'Replica') ? 1 
						: (($fld->FldSpecType == 'Unique') ? 0 : 0)));
				$this->v["fldTots"][$FldType][1]++;
				if (trim($fld->FldDesc) != '') $this->v["fldTots"][$FldType][0]++;
			}
		}
		$this->v["baseURL"] = '/dashboard/db/fieldDescs' 
			. (($view == 'generics') ? '/generics' 
				: (($view == 'replicas') ? '/replicas' 
					: (($view == 'uniques') ? '/uniques' : '')));
		$this->v["fldLabel"] = (($view == 'generics') ? 'Generics' 
			: (($view == 'replicas') ? 'Replicas' 
				: (($view == 'uniques') ? 'Unique' : '')));
		
		$FldSpecType = array('NOT LIKE', 'Generic');
		if ($view == 'generics') $FldSpecType = array('LIKE', 'Generic');
		elseif ($view == 'replicas') $FldSpecType = array('LIKE', 'Replica');
		elseif ($view == 'uniques') $FldSpecType = array('LIKE', 'Unique');
		$whereAll = array('FldDesc', 'LIKE');
		if ($all) $whereAll = array('FldName', 'NOT LIKE');
		$this->v["fldTot"] = SLFields::select('FldID')
			->where('FldDatabase', $this->dbID)
			->where('FldSpecType', $FldSpecType[0], $FldSpecType[1])
			->where($whereAll[0], $whereAll[1], '')
			->get();
		
		$this->v["tblFldLists"] = array();
		$this->v["tblFldVals"] = array();
		if ($this->v["fldTot"] && sizeof($this->v["fldTot"]) > 0)
		{
			foreach ($GLOBALS["DB"]->tbls as $tblID)
			{
				$this->v["tblFldLists"][$tblID] = SLFields::where('FldDatabase', $this->dbID)
					->where('FldSpecType', $FldSpecType[0], $FldSpecType[1])
					->where($whereAll[0], $whereAll[1], '')
					->where('FldTable', $tblID)
					->orderBy('FldOrd', 'asc')
					->orderBy('FldEng', 'asc')
					->get();
				if ($this->v["tblFldLists"][$tblID] 
					&& sizeof($this->v["tblFldLists"][$tblID]) > 0)
				{
					foreach ($this->v["tblFldLists"][$tblID] as $fld)
					{
						$this->v["tblFldVals"][$fld->FldID] = str_replace(';', ' ; ', $fld->FldValues);
						if (strpos($fld->FldValues, 'Def::') !== false 
							|| strpos($fld->FldValues, 'DefX::') !== false)
						{
							$this->v["tblFldVals"][$fld->FldID] = str_replace(';', ' ; ', 
								$this->getDefOpts(str_replace('Def::', '', 
									str_replace('DefX::', '', $fld->FldValues)))); 
						}
						if (isset($this->v["dbBusRulesFld"][$fld->FldID]))
						{
							$this->v["tblFldVals"][$fld->FldID] .= ' <a href="busrules.php?rule=' 
								. base64_encode($this->v["dbBusRulesFld"][$fld->FldID][0]) 
								. '" class="f10" data-toggle="tooltip" data-placement="top"  title="' 
								. str_replace('"', "'", $this->v["dbBusRulesFld"][$fld->FldID][1]) 
								. '"><i class="fa fa-university"></i></a>';
						}
					}
				}
			}
		}
		return view( 'vendor.survloop.admin.db.fieldDescs', $this->v );
	}
	
	public function fieldXML(Request $request)
	{
		$this->admControlInit($request, '/dashboard/db/all');
		if (!$this->v["dbAllowEdits"]) return $this->printOverview();
		$this->v["tblsOrdered"] = SLTables::select('TblID')
			->where('TblDatabase', $this->dbID)
			->orderBy('TblOrd', 'asc')
			->get();
		$this->v["tblFldLists"] = array();
		foreach ($GLOBALS["DB"]->tbls as $tblID)
		{
			$this->v["tblFldLists"][$tblID] = SLFields::where('FldDatabase', $this->dbID)
				->where('FldSpecType', 'NOT LIKE', 'Generic')
				->where('FldTable', $tblID)
				->orderBy('FldOrd', 'asc')
				->orderBy('FldEng', 'asc')
				->get();
		}
		//echo '<pre>'; print_r($this->v["tblFldLists"]); echo '</pre>';
		return view( 'vendor.survloop.admin.db.fieldxml', $this->v );
	}
	
	public function fieldXMLsave(Request $request)
	{
		$this->admControlInit($request, '/dashboard/db/fieldXML');
		if (!$this->v["dbAllowEdits"]) return '';
		if ($this->REQ->has('changedFld') && $this->REQ->changedFld > 0 
			&& $this->REQ->has('changedFldSetting'))
		{
			$fld = SLFields::where('FldID', $this->REQ->changedFld)
				->where('FldDatabase', $this->dbID)
				->first();
			if ($fld && sizeof($fld) > 0)
			{
				if (intVal($fld->FldOpts) <= 0) $fld->FldOpts = 1;
				$primes = [7, 11, 13];
				foreach ($primes as $p)
				{
					if ($this->REQ->changedFldSetting == $p)
					{
						if ($fld->FldOpts%$p > 0) $fld->FldOpts *= $p;
					}
					elseif ($fld->FldOpts%$p == 0) $fld->FldOpts = $fld->FldOpts/$p;
				}
				$fld->save();
			}
		}
		return '';
	}
	
	function fieldDescsSave(Request $request) 
	{
		$this->admControlInit($request, '/dashboard/db/all');
		if (!$this->v["dbAllowEdits"]) exit;
		$this->cacheFlush();
		if ($this->REQ->has('changedFLds') 
			&& $this->REQ->changedFLds != '' && $this->REQ->changedFLds != ',')
		{
			$flds = $this->mexplode(',', $this->REQ->changedFLds);
			if (sizeof($flds) > 0)
			{
				foreach ($flds as $f)
				{
					if (intVal($f) > 0)
					{
						SLFields::find($f)->update([ 
							'FldDesc' => $this->REQ->input('FldDesc'.$f.''), 
							'FldNotes' => $this->REQ->input('FldNotes'.$f.'') 
						]);
					}
				}
			}
		}
		if ($this->REQ->has('changedFLdsGen') 
			&& $this->REQ->changedFLdsGen != '' && $this->REQ->changedFLdsGen != ',')
		{
			$flds = $this->mexplode(',', $this->REQ->changedFLdsGen);
			if (sizeof($flds) > 0)
			{
				foreach ($flds as $f)
				{
					if (intVal($f) > 0)
					{
						SLFields::where($f)
							->orWhere(function ($query) { 
								$query->where('FldSpecType', 'Replica')
								->where('FldSpecSource', $f);
							})
							->update([ 
								'FldDesc' => $this->REQ->input('FldDesc'.$f.''), 
								'FldNotes' => $this->REQ->input('FldNotes'.$f.'') 
							]);
					}
				}
			}
		}
		exit;
	}
	
	public function diagrams(Request $request)
	{
		$this->admControlInit($request);
		if (!$this->checkCache())
		{
			$this->v["printMatrix"] = '';
			$this->v["diags"] = SLDefinitions::where('DefSet', 'Diagrams')
				->where('DefDatabase', $this->dbID)
				->orderBy('DefOrder')
				->get();
			$tblMatrix = array();
			
			if (sizeof($GLOBALS["DB"]->tbls) > 0)
			{
				foreach ($GLOBALS["DB"]->tbls as $tID)
				{ 
					$tblMatrix[$tID] = array();
					foreach ($GLOBALS["DB"]->tbls as $tID2)
					{
						$tblMatrix[$tID][$tID2] = array();
					}
				}
				$flds = SLFields::select('FldID', 
					'FldTable', 'FldForeignTable', 
					'FldForeignMin', 'FldForeignMax', 
					'FldForeign2Min', 'FldForeign2Max')
					->where('FldTable', '>', 0)
					->where('FldForeignTable', '>', 0)
					->where('FldDatabase', $this->dbID)
					->get();
				if ($flds && sizeof($flds) > 0)
				{
					foreach ($flds as $fld)
					{
						$dup = false;
						if (sizeof($tblMatrix[$fld->FldTable][$fld->FldForeignTable]) > 0)
						{
							foreach ($tblMatrix[$fld->FldTable][$fld->FldForeignTable] as $keys)
							{
								if ($keys[0] == $fld->FldForeign2Min 
									&& $keys[1] == $fld->FldForeign2Max)
								{
									$dup = true;
								}
							}
						}
						if (!$dup)
						{
							$tblMatrix[$fld->FldTable][$fld->FldForeignTable][] 
								= [$fld->FldForeign2Min, $fld->FldForeign2Max];
						}
						$dup = false;
						if (sizeof($tblMatrix[$fld->FldForeignTable][$fld->FldTable]) > 0)
						{
							foreach ($tblMatrix[$fld->FldForeignTable][$fld->FldTable] as $keys)
							{
								if ($keys[0] == $fld->FldForeignMin 
									&& $keys[1] == $fld->FldForeignMax)
								{
									$dup = true;
								}
							}
						}
						if (!$dup)
						{
							$tblMatrix[$fld->FldForeignTable][$fld->FldTable][] 
								= [$fld->FldForeignMin, $fld->FldForeignMax];
						}
					}
					$this->v["printMatrix"] = '<table class="keyMatrix " border=1 '
						. 'cellpadding=0 cellspacing=3 ><tr><td class="mid">&nbsp;</td>';
					$cnt1 = $cnt2 = 1;
					foreach ($GLOBALS["DB"]->tbls as $tID)
					{
						$cnt2++;
						$this->v["printMatrix"] .= '<th class="' 
							. (($cnt2%2 == 0) ? 'col2' : 'col1') 
							. '" >' . $GLOBALS["DB"]->tbl[$tID] . '</th>';
					}
					foreach ($GLOBALS["DB"]->tbls as $tID)
					{
						$cnt1++; $cnt2 = 1;
						$this->v["printMatrix"] .= '<tr ' 
							. (($cnt1%2 == 0) ? 'class="row2"' : '') 
							. ' ><th>' . $GLOBALS["DB"]->tbl[$tID] . '</th>';
						foreach ($GLOBALS["DB"]->tbls as $tID2)
						{ 
							$cnt2++;
							$this->v["printMatrix"] .= '<td class="' 
								. (($tID == $tID2) ? 'mid ' : (($cnt2%2 == 0) ? 'col2' : 'col1')) 
								. '" data-toggle="tooltip" data-placement="top"  title="';
							if (sizeof($tblMatrix[$tID][$tID2]) > 0)
							{ 
								$this->v["printMatrix"] .= $tblMatrix[$tID][$tID2][0][0] 
									. ' to ' . $tblMatrix[$tID][$tID2][0][1] . '</b> ' 
									. strip_tags($this->getTblName($tID2, 0) 
									. ' records can be related to a single ' 
									. $this->getTblName($tID, 0)) . ' record." >' 
									. $tblMatrix[$tID][$tID2][0][0] . ':' 
									. $tblMatrix[$tID][$tID2][0][1] . '</td>';
							}
							else
							{
								$this->v["printMatrix"] .= '<i>No direct relationship between ' 
									. strip_tags($this->getTblName($tID2, 0) . ' and ' 
									. $this->getTblName($tID, 0)) . '.</i>"></td>';
							}
						}
						$this->v["printMatrix"] .= '</tr>';
					}
					$this->v["printMatrix"] .= '</table>';
				}
			}
			$this->v["content"] = view( 'vendor.survloop.admin.db.diagrams', $this->v )->render();
			$this->saveCache();
		}
		return view( 'vendor.survloop.admin.admin', $this->v );
	}
	
	// http://www.html5canvastutorials.com/tutorials/
	public function networkMap(Request $request)
	{
		$this->admControlInit($request, '/dashboard/db/diagrams');
		if (!$this->checkCache('/dashboard/db/diagrams/network-map'))
		{
			$this->v["errors"] = '';
			$this->v["canvasDimensions"] = array(950, 900);
			$mainCircleCenter = array($this->v["canvasDimensions"][0]/2, $this->v["canvasDimensions"][1]/2);
			$sizeMax = 0;
			$this->v["tables"] = $tableLookup = array();
			//$this->v["tables"][] = array('English', Size, Center-X, Center-Y);
			$tbls = SLTables::select('TblID', 'TblName', 'TblNumForeignKeys', 'TblNumForeignIn', 'TblOpts')
				->where('TblDatabase', $this->dbID)
				->orderBy('TblOrd', 'asc')
				->get();
			if (sizeof($tbls) > 0)
			{
				foreach ($tbls as $tbl)
				{
					$tableLookup[$tbl->TblID] = sizeof($this->v["tables"]);
					$this->v["tables"][] = [
						$tbl->TblName, 
						sqrt(sqrt($tbl->TblNumForeignKeys+$tbl->TblNumForeignIn)), 
						0, 
						0, 
						(($GLOBALS["DB"]->isCoreTbl($tbl->TblID)) ? '#f6c82e' : '')
					];
				}
			}
			foreach ($this->v["tables"] as $i => $tbl)
			{
				if ($sizeMax < $this->v["tables"][$i][1]) $sizeMax = $this->v["tables"][$i][1];
			}
			foreach ($this->v["tables"] as $i => $tbl)
			{
				if ($sizeMax <= 0) $sizeMax = 1;
				$this->v["tables"][$i][1] = 43*($this->v["tables"][$i][1]/$sizeMax);
				if ($this->v["tables"][$i][1] <= 10) $this->v["tables"][$i][1] = 10;
				$this->v["tables"][$i][2] = round($mainCircleCenter[0]+(sin(deg2rad(360*$i/sizeof($this->v["tables"])))*(0.46*$this->v["canvasDimensions"][1])));
				$this->v["tables"][$i][3] = round($mainCircleCenter[1]-(cos(deg2rad(360*$i/sizeof($this->v["tables"])))*(0.46*$this->v["canvasDimensions"][1])));
			}
			$this->v["keyLines"] = array();
			$foreignFlds = SLFields::select('FldTable', 'FldForeignTable')
				->where('FldForeignTable', '>', 0)
				->where('FldSpecType', 'NOT LIKE', 'Generic')
				->where('FldDatabase', $this->dbID)
				->get();
			if ($foreignFlds && sizeof($foreignFlds) > 0)
			{
				foreach ($foreignFlds as $fld)
				{
					if (!isset($tableLookup[$fld->FldTable]))
					{
						$errors .= '<br />adding line, missing FldTable tableLookup['.$fld->FldTable.']';
					}
					elseif (!isset($tableLookup[$fld->FldForeignTable]))
					{
						$errors .= '<br />adding line, missing FldForeignTable tableLookup['.$fld->FldForeignTable.']';
					}
					else $this->v["keyLines"][] = [$tableLookup[$fld->FldTable], $tableLookup[$fld->FldForeignTable]];
				}
			}
			$this->v["content"] = view( 'vendor.survloop.admin.db.network-map', $this->v )->render();
			$this->saveCache();
		}
		$this->v["hideWrap"] = true;
		return view( 'vendor.survloop.master-print', $this->v );
	}
	
	public function addTable(Request $request)
	{
		$this->admControlInit($request, '/dashboard/db/all');
		return $this->printEditTable('');
		return view( 'vendor.survloop.admin.db.overview', $this->v );
	}
	
	public function editTable(Request $request, $tblName)
	{
		$this->admControlInit($request, '/dashboard/db/all');
		if (trim($tblName) == '') return $this->printOverview();
		return $this->printEditTable($tblName);
	}
	
	public function addTableFld(Request $request, $tblAbbr)
	{
		//echo '<br /><br /><br />addTableFld: ' . $tblAbbr . '<br />';
		//echo '<pre>'; print_r($request->all()); echo '</pre>';
		$this->admControlInit($request, '/dashboard/db/all');
		//if ($request->has('fldEditForm')) echo 'request has form<br />';
		//if ($this->REQ->has('fldEditForm')) echo 'request has form<br />';
		if (trim($tblAbbr) == '') return $this->printOverview();
		return $this->printEditField($tblAbbr, '');
	}
	
	public function editField(Request $request, $tblAbbr, $fldName)
	{
		if (trim($fldName) == '') return $this->addTableFld($request, $tblAbbr);
		$this->admControlInit($request, '/dashboard/db/all');
		if (trim($tblAbbr) == '') return $this->printOverview();
		return $this->printEditField($tblAbbr, $fldName);
	}
	
	public function fieldAjax(Request $request, $fldID = -3)
	{
		if (intVal($fldID) <= 0) exit;
		$this->admControlInit($request);
		$fld = SLFields::find($fldID);
		return $this->fullFldSpecs($fld, false);
	}
	
	public function fieldMatrix(Request $request)
	{
		$this->admControlInit($request, '/dashboard/db/field-matrix');
		if (!$this->checkCache())
		{
			$this->v["urlParam"] = (($this->v["isAlt"]) ? 'alt=1&' : '');
			$this->v["fieldMatrix"] = '...';
			
			$keySign = (($this->v["isExcel"]) ? ' *' : ' <i class="fa fa-link"></i>');
			$this->v["matrix"] = array();
			$this->v["max"] = 0;
			$tbls = $this->tblQryStd();
			if ($tbls && sizeof($tbls) > 0)
			{
				foreach ($tbls as $i => $tbl)
				{
					$this->v["matrix"][] = array((($this->v["isAlt"]) ? $tbl->TblEng : $tbl->TblName));
					$this->v["matrix"][$i][] = (($this->v["isAlt"]) ? 'Unique Primary ID' : $tbl->TblAbbr.'ID');
					$flds = SLFields::where('FldTable', $tbl->TblID)
						->where('FldDatabase', $this->dbID)
						->orderBy('FldOrd', 'asc')
						->orderBy('FldEng', 'asc')
						->get();
					if ($flds && sizeof($flds) > 0)
					{
						foreach ($flds as $fld)
						{
							$lnk = (($fld->FldForeignTable > 0) ? $keySign
								. (($this->v["isExcel"]) ? '' 
									: '<span class="f8 tooltip" title="' . $this->getForeignTip($fld) . '">')
								. '(' . $fld->FldForeignMin . ',' . $fld->FldForeignMax . ')' 
								. (($this->v["isExcel"]) ? '' : '</span>') : '');
							$this->v["matrix"][$i][] = (($this->v["isAlt"]) 
								? $fld->FldEng : $tbl->TblAbbr.$fld->FldName) . $lnk;
							if ($this->v["max"] < sizeof($this->v["matrix"][$i]))
							{
								$this->v["max"] = sizeof($this->v["matrix"][$i]);
							}
						}
					}
				}
			}
			if ($this->v["isExcel"])
			{
				$tblInner = '<tr><td colspan=4 >R2R Database Tables</td>
					<td colspan=2 >* Foreign Keys</td></tr>';
				$tblInner .= '<tr>';
				foreach ($this->v["matrix"] as $row)
				{
					$tblInner .= '<th>' . $row[0] . '</th>';
				}
				$tblInner .= '</tr>';
				for ($r=1; $r < $this->v["max"]; $r++)
				{
					$tblInner .= '<tr>';
					foreach ($this->v["matrix"] as $row)
					{
						$tblInner .= '<td>' . ((isset($row[$r])) ? $row[$r] : '&nbsp;') . '</td>';
					}
					$tblInner .= '</tr>';
				}
				$filename = 'OPC-DB-Field_Matrix-' 
					. (($this->v["isAlt"]) ? 'English' : 'Geek')
					. '-' . date("ymd");
				$this->exportExcelOldSchool($tblInner, $filename.'.xls');
				exit;
			}
			
			$this->v["content"] = view( 'vendor.survloop.admin.db.field-matrix', $this->v )->render();
			$this->saveCache();
		}
		return view( 'vendor.survloop.admin.admin', $this->v );
	}
	
	public function tblSelector(Request $request, $rT = '')
	{
		$this->admControlInit($request);
		return $this->ajaxTblSelector($rT);
	}
	
	public function fldSelector(Request $request, $rF = '')
	{
		$this->admControlInit($request);
		return $this->ajaxFldSelector($rF);
	}
	
	public function getSetFlds(Request $request, $rSet = '')
	{
		$this->loadLookups();
		return view( 'vendor.survloop.admin.db.inc-getTblsFldsDropOpts', [ "rSet" => $rSet ] );
	}
	
	public function getSetFldVals(Request $request, $fldID = '')
	{
		$this->loadLookups();
		$sessData = new SurvLoopData;
		return view( 'vendor.survloop.admin.db.inc-getTblsFldVals', [ 
			"FldID" 	=> $fldID,
			"values" 	=> $GLOBALS["DB"]->getFldResponsesByID($fldID)
		]);
	}
	

	
	
	/******************************************************
	*** Helper and Lookup Functions
	******************************************************/
	
	protected function loadTblGroups()
	{
		$this->v["groupTbls"] = array();
		$tbls = $this->tblQryStd();
		if ($tbls && sizeof($tbls) > 0)
		{
			foreach ($tbls as $tbl)
			{
				if (!isset($this->v["groupTbls"][$tbl->TblGroup]))
				{
					$this->v["groupTbls"][$tbl->TblGroup] = array();
				}
				$this->v["groupTbls"][$tbl->TblGroup][] = $tbl;
			}
		}
		return true;
	}
	
	protected function loadTblForeigns()
	{
		$this->v["tblForeigns"] = array();
		$flds = SLFields::where('FldForeignTable', '>', 0)
			->where('FldSpecType', 'NOT LIKE', 'Generic')
			->where('FldDatabase', $this->dbID)
			->get();
		//echo '<pre>'; print_r($flds); echo '</pre>';
		if ($flds && sizeof($flds) > 0)
		{
			foreach ($flds as $fld)
			{
				if (!isset($this->v["tblForeigns"][$fld->FldForeignTable]))
				{
					$this->v["tblForeigns"][$fld->FldForeignTable] = '';
				}
				$this->v["tblForeigns"][$fld->FldForeignTable] .= ', ' 
					. $this->printForeignKey($fld, 2, 1);
			}
			foreach ($this->v["tblForeigns"] as $tID => $foreigns)
			{
				$this->v["tblForeigns"][$tID] = trim(substr($foreigns, 1));
			}
		}
		return true;
	}
	
	protected function loadTblRules()
	{
		$this->v["tblRules"] = array();
		$rules = SLBusRules::where('RuleDatabase', $this->dbID)->get();
		if ($rules && sizeof($rules) > 0)
		{
			foreach ($rules as $rule)
			{
				$tblList = $this->mexplode(',', $rule->RuleTables);
				if (sizeof($tblList) > 0)
				{
					foreach ($tblList as $i => $tbl)
					{
						$tbl = intVal($tbl);
						if (!isset($this->v["tblRules"][$tbl]))
						{
							$this->v["tblRules"][$tbl] = [];
						}
						$this->v["tblRules"][$tbl][] = $rule;
					}
				}
			}
		}
		return true;
	}
	
	protected function loadDefSets()
	{
		$this->v["defDeets"] = array();
		$defs = SLDefinitions::where('DefSet', 'Value Ranges')
			->where('DefDatabase', $this->dbID)
			->orderBy('DefSubset', 'asc')
			->orderBy('DefOrder', 'asc')
			->orderBy('DefValue', 'asc')
			->get();
		if ($defs && sizeof($defs) > 0)
		{
			foreach ($defs as $def)
			{
				if (!isset($this->v["defDeets"][$def->DefSubset]))
				{
					$this->v["defDeets"][$def->DefSubset] = [''];
				}
				$this->v["defDeets"][$def->DefSubset][0] .= ';' . $def->DefValue;
				$this->v["defDeets"][$def->DefSubset][] = $def->DefValue;
			}
		}
		$cnt = 0;
		$this->v["defDeetsJS"] = '';
		if (sizeof($this->v["defDeets"]) > 0)
		{
			foreach ($this->v["defDeets"] as $set => $vals)
			{
				$this->v["defDeetsJS"] .= 'definitions['.$cnt.'] = new Array("' 
					. htmlspecialchars($set) . '", "' 
					. htmlspecialchars(substr($vals[0], 1)) . '");' . "\n";
				$cnt++;
			}
		}
		return true;
	}
	
	protected function getTblName($id = -3, $link = 1, $xtraTxt = '', $xtraLnk = '')
	{
		return view( 'vendor.survloop.admin.db.inc-getTblName', [
			"id" => $id, 
			"link" => $link, 
			"xtraTxt" => $xtraTxt, 
			"xtraLnk" => $xtraLnk 
		]);
	}
	
	protected function getForeignTip($fld = array())
	{
		return 'Degree of Participation: ' . $fld->FldForeignMin . ' Minimum and ' 
			. $fld->FldForeignMax . ' Maximum number of ' 
			. $this->getTblName($fld->FldTable, 0) 
			. ' records which can be associated with a single record from ' 
			. $this->getTblName($fld->FldForeignTable, 0);
	}

	protected function tblQryStd()
	{
		return SLTables::where('TblDatabase', $this->dbID)
			->orderBy('TblOrd', 'asc')
			->orderBy('TblNumForeignKeys', 'desc')
			->get();
	}
	
	protected function getTblDropOpts($presel = -3, $blankDefTxt = '(select table)')
	{
		$this->v["presel"] = $presel;
		$this->v["blankDefTxt"] = $blankDefTxt;
		return view( 'vendor.survloop.admin.db.inc-getTblDropOpts', $this->v );
	}
	
	protected function getFldDropOpts($tbl = -3, $presel = -3, $blankDefTxt = '(select field)')
	{
		$ret = '<option value="-3" ' . (($presel == -3) ? 'SELECTED' : '') 
			. ' >' . $blankDefTxt . '</option>';
		if ($tbl > 0)
		{
			$flds = SLFields::select('FldID', 'FldName')
				->where('FldTable', $tbl)
				->orderBy('FldOrd', 'asc')
				->get();
			if ($flds && sizeof($flds) > 0)
			{
				foreach ($flds as $fld)
				{
					$ret .= '<option value="'.$fld->FldID.'" ' 
						. (($presel == $fld->FldID) ? 'SELECTED' : '') 
						. ' >'.$fld->FldName.'</option>';
				}
			}
		}
		return $ret;
	}
	
	protected function loadGenerics()
	{
		$flds = SLFields::select('FldID', 'FldEng')
			->where('FldDatabase', $this->dbID)
			->where('FldSpecType', 'Generic')
			->orderBy('FldOrd', 'asc')
			->orderBy('FldEng', 'asc')
			->get();
		if ($flds && sizeof($flds) > 0)
		{
			foreach ($flds as $fld)
			{
				$this->v["dbFldGenerics"][] = array($fld->FldID, $fld->FldEng);
			}
		}
		return true;
	}
	
	protected function getFldGenericOpts($presel = -3)
	{
		$this->v["presel"] = $presel;
		if (sizeof($this->v["dbFldGenerics"]) == 0) $this->loadGenerics();
		return view( 'vendor.survloop.admin.db.inc-getFldGenericOpts', $this->v );
	}
	
	protected function printDbStats()
	{
		return '<div class="f16 pB10 pL20 mTn10">' . $GLOBALS["DB"]->dbRow->DbTables 
			. ' tables, ' . $GLOBALS["DB"]->dbRow->DbFields . ' fields</div>';
	}
	
	protected function printBasicTblDesc($tbl, $foreignKeyTbls = '')
	{
		if ($tbl && sizeof($tbl) > 0)
		{
			$this->v["tbl"] = $tbl;
			$this->v["foreignKeyTbls"] = $foreignKeyTbls;
			return view( 'vendor.survloop.admin.db.inc-tblDesc', $this->v );
		}
		return '';
	}
	
	protected function printBasicTblFlds($tblID = -3, $tblLinks = 1, $flds = array()) 
	{
		if (sizeof($flds) == 0) $flds = SLFields::where('FldTable', $tblID)
			->orderBy('FldOrd', 'asc')
			->get();
		$this->v["flds"] = $flds;
		$this->v["tblID"] = $tblID;
		$this->v["tblLinks"] = $tblLinks;
		$this->v["printTblFldRows"] = '';
		if (isset($flds) && sizeof($flds) > 0)
		{
			foreach ($flds as $i => $fld)
			{
				if (!$this->REQ->has('onlyKeys') || $fld->FldForeignTable > 0)
				{
					$this->v["printTblFldRows"] .= $this->printBasicTblFldRow($fld, $tblID, $tblLinks);
				}
			}
		}
		return view( 'vendor.survloop.admin.db.inc-basicTblFlds', $this->v );
	}
	
	protected function printBasicTblFldRow($fld = array(), $tblID = -3, $tblLinks = 1)
	{
		$this->v["fld"] = $fld;
		$this->v["tblID"] = $tblID;
		$this->v["tblLinks"] = $tblLinks;
		$this->v["FldValues"] = $fld->FldValues;
		if (strpos($this->v["FldValues"], 'Def::') !== false 
			|| strpos($this->v["FldValues"], 'DefX::') !== false)
		{
			$range = str_replace('Def::', '', str_replace('DefX::', '', $this->v["FldValues"]));
			if (isset($this->v["dbDefOpts"][$range]))
			{
				$this->v["FldValues"] = str_replace(';', ' ; ', $this->v["dbDefOpts"][$range][0]);
			}
		}
		else $this->v["FldValues"] = str_replace(';', ' ; ', $this->v["FldValues"]);
		$this->v["fldForeignPrint"] = $this->printForeignKey($fld, $tblLinks);
		$this->v["fldGenerics"] = $this->printFldGenerics($fld, $tblLinks);
		//echo 'tblID: ' . $tblID . '<pre>'; print_r($fld); echo '</pre>';
		return view( 'vendor.survloop.admin.db.inc-basicTblFldRow', $this->v );
	}
	
	protected function printForeignKey($fld = array(), $tblLinks = 1, $whichway = 0)
	{
		if (intVal($fld->FldForeignTable) > 0 && isset($GLOBALS["DB"]->tbl[$fld->FldForeignTable])) 
		{
			if ($whichway == 0)
			{
				return '<a href="/dashboard/db/table/' . $GLOBALS["DB"]->tbl[$fld->FldForeignTable] 
					. '" data-toggle="tooltip" data-placement="top" title="Degree of Participation: '
					. $fld->FldForeign2Min . ' to ' . $fld->FldForeign2Max . ' ' 
					. $this->getTblName($fld->FldForeignTable, 0) . ' records can be related to a single ' 
					. $this->getTblName($fld->FldTable, 0) . ' record. ' 
					. $fld->FldForeignMin . ' to ' . $fld->FldForeignMax . ' ' 
					. $this->getTblName($fld->FldTable, 0) . ' records can be related to a single ' 
					. $this->getTblName($fld->FldForeignTable, 0) . ' record." >'
					. '<i class="fa fa-link"></i> ' . $GLOBALS["DB"]->tblEng[$fld->FldForeignTable] 
					. ' <span class="f8">(' 
					. $fld->FldForeign2Min . ',' . $fld->FldForeign2Max . ')-(' 
					. $fld->FldForeignMin . ',' . $fld->FldForeignMax . ')</span></a>';
			}
			else 
			{
				return '<a href="/dashboard/db/table/' . $GLOBALS["DB"]->tbl[$fld->FldTable] 
					. '" data-toggle="tooltip" data-placement="top" title="Degree of Participation: '
					. $fld->FldForeignMin . ' to ' . $fld->FldForeignMax . ' ' 
					. $this->getTblName($fld->FldTable, 0) . ' records can be related to a single ' 
					. $this->getTblName($fld->FldForeignTable, 0) . ' record. ' 
					. $fld->FldForeign2Min . ' to ' . $fld->FldForeign2Max . ' ' 
					. $this->getTblName($fld->FldForeignTable, 0) . ' records can be related to a single ' 
					. $this->getTblName($fld->FldTable, 0) . ' record." >'
					. '<i class="fa fa-link"></i> ' . $GLOBALS["DB"]->tblEng[$fld->FldTable] 
					. ' <span class="f8">(' 
					. $fld->FldForeignMin . ',' . $fld->FldForeignMax . ')-(' 
					. $fld->FldForeign2Min . ',' . $fld->FldForeign2Max . ')</span></a>';
			}
		}
		return '';
	}
	
	protected function printFldGenerics($fld = array(), $tblLinks = 1) 
	{
		$repList = '';
		if ($fld->FldSpecType == 'Generic')
		{
			$repList = '<br />Replica Copies: ';
			$replicas = SLFields::select('FldTable')
				->where('FldSpecSource', $fld->FldID)
				->where('FldSpecType', 'Replica')
				->get();
			if ($replicas && sizeof($replicas) > 0)
			{
				foreach ($replicas as $rep)
				{
					$repList .= ', ' . $this->getTblName($rep->FldTable, $tblLinks);
				}
			}
		}
		return $repList;
	}
	
	protected function foreignLinkCnt($preSel = '1')
	{
		$ret = '<option value="N" ' . (($preSel == 'N') ? 'SELECTED' : '') 
			. ' >N    (unlimited)</option>';
		for ($i=0; $i<100; $i++)
		{
			$ret .= '<option value="'.$i.'" ' 
				. (($preSel == (''.$i.'')) ? 'SELECTED' : '') 
				. ' >'.$i.'</option>';
		}
		return $ret;
		view ( 'vendor.survloop.admin.db.inc-getLinkCnt', array("preSel" => $preSel) );
	}
	
	function getFldArr($RuleFields = '') 
	{
		return SLFields::select('FldID', 'FldTable', 'FldName')
			->whereIn('FldID', $this->mexplode(',', $RuleFields))
			->orderBy('FldTable', 'asc')
			->orderBy('FldOrd', 'asc')
			->orderBy('FldEng', 'asc')
			->get();
	}
	
	protected function tblListID2Link($tblcommas = ',')
	{
		$tblList = '';
		if (trim($tblcommas) != '' && trim($tblcommas) != ',') 
		{
			$tblArr = $this->mexplode(',', str_replace(',,', ',', $tblcommas));
			if (sizeof($tblArr) > 0) 
			{
				foreach ($tblArr as $i => $tblID) 
				{
					if (intVal($tblID) > 0)
					{
						$tblList .= (($i > 0) ? ', ' : '') . '<nobr>' 
							. $this->getTblName(intVal($tblID), 1, '', ' target="_blank"') 
							. '</nobr>';
					}
				}
			}
		}
		return $tblList;
	}
	
	protected function fldListID2Link($fldcommas = ',') 
	{
		$fldTxt = '';
		$fldList = $this->getFldArr($fldcommas);
		if (sizeof($fldList) > 0) 
		{
			foreach ($fldList as $i => $fld) 
			{
				$fldTxt .= (($i > 0) ? ',&nbsp;&nbsp;&nbsp; ' : '') 
					. $this->getTblName($fld->FldTable, 1, '', ' target="_blank"') . ':&nbsp;' 
					. '<a href="fld.php?fldSpec=' . base64_encode($fld->FldID) 
					. '" target="_blank">' . $fld->FldName . '</a>';
			}
		}
		return $fldTxt;
	}
	
	
}

?>