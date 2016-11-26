<?php
namespace SurvLoop\Controllers;

use SurvLoop\Models\SLFields;
use SurvLoop\Models\SLNodeSaves;

class SurvLoopData
{
	protected $coreID 		= -3;
	protected $privacy 		= 'public';
	protected $id2ind		= array();
	protected $loaded 		= false;
	
	// These are collections of all this session's records for each table
	public $dataSets 		= array();
	
	// Lookup arrays mapping this record to others by table an ID
	public $kidMap			= array();
	public $parentMap		= array();
	public $linkMap 		= array(); // obsolete? i think so
	
	// Tree node's current data structure nested position
	public $dataBranches 	= array();
	
	// Tree node's which capture multiple-response checkboxes
	public $checkboxNodes	= array();

	// These are the IDs the items within a table's dataSet which are in a loop collection
	public $loopItemIDs 	= array();
	
	public $loopTblID 		= -3;
	public $loopItemIDsDone = array();
	public $loopItemsNextID = -3;
	
	
	public function loadCore($coreID = -3, $dataBranches = array(), $checkboxNodes = array(), $privacy = 'public')
	{
		//echo 'loadCore(' . $coreID . ', <pre>'; print_r($dataBranches); echo '</pre>';
		$this->coreID 			= $coreID;
		$this->dataBranches 	= $dataBranches;
		$this->checkboxNodes 	= $checkboxNodes;
		$this->privacy 			= $privacy;
		$this->refreshDataSets();
		$this->loaded = true;
		return true;
	}
	
	public function refreshDataSets()
	{
		//echo 'refreshDataSets()<br />';
		$this->dataSets = $this->id2ind = $this->kidMap = $this->parentMap = array();
		$this->loadData($GLOBALS["DB"]->coreTbl, $this->coreID);
		return true;
	}
	
	protected function initDataSet($tbl)
	{
		$setInd = 0;
		if (!isset($this->dataSets[$tbl])) $this->dataSets[$tbl] = $this->id2ind[$tbl] = array();
		else $setInd = sizeof($this->dataSets[$tbl]);
		return $setInd;
	}
	                                                                                        
	public function loadData($tbl, $rowID, $recObj = array())
	{
		//echo 'loadData('.$tbl.', '.$rowID.')<br />' . "\n";
		$subObj = array();
		if (trim($tbl) != '' && $rowID > 0)
		{
			if (!$recObj || sizeof($recObj) == 0) $recObj = $this->dataFind($tbl, $rowID);
			//echo '<pre>'; print_r($recObj); echo '</pre>';
			if ($recObj && sizeof($recObj) > 0)
			{
				// Adding record to main set of all records
				$setInd = $this->initDataSet($tbl);
				$this->dataSets[$tbl][$setInd] = $recObj;
				$this->id2ind[$tbl][$recObj->getKey()] = $setInd;
				
				// Recurse through this parent's families...
				
				//echo 'checking 1-to-1 subsets..<br /> ' . "\n";
				if (isset($GLOBALS["DB"]->dataSubsets) && sizeof($GLOBALS["DB"]->dataSubsets) > 0)
				{
					foreach ($GLOBALS["DB"]->dataSubsets as $subset)
					{
						if ($subset->DataSubTbl == $tbl)
						{
							//echo 'subset match ' . $tbl . ' -> ' . $subset->subTbl . '<br /> ' . "\n";
							$subObjs = array();
							if (trim($subset->DataSubTblLnk) != '' && intVal($recObj->{ $subset->DataSubTblLnk }) > 0)
							{
								$subObjs = $this->dataFind($subset->DataSubSubTbl, $recObj->{ $subset->DataSubTblLnk });
								if ($subObjs && sizeof($subObjs) > 0) $subObjs = array($subObjs);
							}
							elseif (trim($subset->DataSubSubLnk) != '')
							{
								$subObjs = $this->dataWhere($subset->DataSubSubTbl, $subset->DataSubSubLnk, $rowID);
							}
							if (sizeof($subObjs) == 0 && $subset->DataSubAutoGen == 1)
							{
								//echo 'making a new subset, table: ' . $subset->subTbl . '<br /> ' . "\n";
								$subObjs = array($this->newDataRecordSimple($subset->DataSubSubTbl));
								if (trim($subset->DataSubTblLnk) != '') //  && intVal($recObj->{ $subset->tblLnk }) > 0
								{
									//echo 'making a new subset, tblLnk: ' . $subset->tblLnk . '<br /> ' . "\n";
									$recObj->update([ $subset->DataSubTblLnk => $subObjs[0]->getKey() ]);
									$recObj->save();
								}
								elseif (trim($subset->DataSubSubLnk) != '')
								{
									//echo 'making a new subset, subLnk: ' . $subset->subLnk . '<br /> ' . "\n";
									$subObjs[0]->update([ $subset->DataSubSubLnk => $rowID ]);
									$subObjs[0]->save();
								}
							}
							$this->processSubObjs($tbl, $rowID, $setInd, $subset->DataSubSubTbl, $subObjs);
						}
					}
				}
				
				//echo 'checking loops..<br /> ' . "\n";
				if ($tbl == $GLOBALS["DB"]->coreTbl
					&& isset($GLOBALS["DB"]->dataLoops) && sizeof($GLOBALS["DB"]->dataLoops) > 0)
				{
					foreach ($GLOBALS["DB"]->dataLoops as $loopName => $loop)
					{
						$keyField = $GLOBALS["DB"]->getForeignLnk($GLOBALS["DB"]->tblI[$loop->DataLoopTable], $tbl);
						if (trim($keyField) != '')
						{
							$subObjs = $this->dataWhere($loop->DataLoopTable, $GLOBALS["DB"]->tblAbbr[$loop->DataLoopTable].$keyField, $rowID);
							//echo 'loop match ' . $tbl . ': ' . $loop->DataLoopTable . ' - ' . $GLOBALS["DB"]->tblAbbr[$loop->DataLoopTable].$keyField . ' ?= ' . $rowID . '<pre>'; print_r($subObjs); echo '</pre>';
							$this->processSubObjs($tbl, $recObj->getKey(), $setInd, $loop->DataLoopTable, $subObjs);
						}
					}
				}
				
				//echo 'checking helpers..<br /> ' . "\n";
				if (isset($GLOBALS["DB"]->dataHelpers) && sizeof($GLOBALS["DB"]->dataHelpers) > 0)
				{
					foreach ($GLOBALS["DB"]->dataHelpers as $helper)
					{
						if ($helper->DataHelpParentTable == $tbl)
						{
							//echo 'helper match ' . $tbl . ': ' . $helper->DataHelpTable . ' <i>(' . $helper->DataHelpKeyField . ')</i><br /> ' . "\n";
							$subObjs = $this->dataWhere($helper->DataHelpTable, $helper->DataHelpKeyField, $rowID);
							$this->processSubObjs($tbl, $recObj->getKey(), $setInd, $helper->DataHelpTable, $subObjs);
						}
					}
				}
				
				//echo 'checking linkages..<br /> ' . "\n";
				if (isset($GLOBALS["DB"]->dataLinksOn) && sizeof($GLOBALS["DB"]->dataLinksOn) > 0)
				{
					foreach ($GLOBALS["DB"]->dataLinksOn as $linkage)
					{
						if ($tbl == $linkage[4])
						{
							$linkage = array($linkage[4], $linkage[3], $linkage[2], $linkage[1], $linkage[0]);
						}
						if ($tbl == $linkage[0])
						{
							//echo 'linkage match ' . $tbl . ': ' . $linkage[4] . '<br /> ';
							$lnkObjs = $this->dataWhere($linkage[2], $linkage[1], $rowID);
							if ($lnkObjs && sizeof($lnkObjs) > 0)
							{
								$this->processSubObjs($tbl, $recObj->getKey(), $setInd, $linkage[2], $lnkObjs);
								foreach ($lnkObjs as $lnkObj)
								{
									$findObj = $this->dataFind($linkage[4], $lnkObj->{ $linkage[3] });
									if ($findObj && sizeof($findObj) > 0)
									{
										$subObjs = array($findObj);
										//echo 'linkage2 match ' . $tbl . ': ' . $linkage[4] . '<br /> '; print_r($lnkObj); echo '<br />';
										$this->processSubObjs($tbl, $recObj->getKey(), $setInd, $linkage[4], $subObjs);
									}
									elseif (intVal($lnkObj->{ $linkage[3] }) > 0)
									{	// If this is a bad linkage, let's delete it
										$lnkObj->{ $linkage[3] } = NULL;
										$lnkObj->save();
									}
								}
							}
						}
					}
				}
				
			}
		}
		//echo '</div> done loadData()<br />';
		return true;
	}
	
	
	
	protected function getRecordLinks($tbl = '', $extraOutFld = '', $extraOutVal = -3, $skipIncoming = false)
	{
		$linkages = [
			"outgoing" => [],
			"incoming" => []
		];
		
		if (trim($extraOutFld) != '') $linkages["outgoing"][] = [$extraOutFld, $extraOutVal];
		
		// Outgoing Keys
		$flds = SLFields::select('FldName', 'FldForeignTable')
			->where('FldTable', $GLOBALS["DB"]->tblI[$tbl])
			->where('FldForeignTable', '>', 0)
			->get();
		if ($flds && sizeof($flds) > 0)
		{
			foreach ($flds as $fldKey)
			{
				$foreignTbl = $GLOBALS["DB"]->tbl[$fldKey->FldForeignTable];
				if ($fldKey->FldForeignTable == $GLOBALS["DB"]->treeRow->TreeCoreTable)
				{
					$linkages["outgoing"][] = [$GLOBALS["DB"]->tblAbbr[$tbl].$fldKey->FldName, $this->coreID];
				}
				else // not the special Core case, so find an ancestor
				{
					list($loopInd, $loopID) = $this->currSessDataPos($foreignTbl);
					//if ($tbl == 'Civilians') echo 'getRecordLinks(' . $tbl . ' ; foreignTbl: ' . $foreignTbl . ', loopInd: ' . $loopInd . ', loopID: ' . $loopID . '<br />';
					if ($loopID > 0)
					{
						$newLink = [$GLOBALS["DB"]->tblAbbr[$tbl].$fldKey->FldName, $loopID];
						if (!in_array($newLink, $linkages["outgoing"])) $linkages["outgoing"][] = $newLink;
					}
				}
			}
		}
		
		// Incoming Keys
		if (!$skipIncoming)
		{
			$flds = SLFields::select('FldName', 'FldTable')
				->where('FldForeignTable', $GLOBALS["DB"]->tblI[$tbl])
				->where('FldForeignTable', '>', 0)
				->where('FldTable', '>', 0)
				->get();
			if ($flds && sizeof($flds) > 0)
			{
				foreach ($flds as $fldKey)
				{
					$foreignTbl = $GLOBALS["DB"]->tbl[$fldKey->FldTable];
					if ($fldKey->FldTable == $GLOBALS["DB"]->treeRow->TreeCoreTable)
					{
						$linkages["incoming"][] = [$foreignTbl, $GLOBALS["DB"]->tblAbbr[$foreignTbl].$fldKey->FldName, $this->coreID];
					}
					else // not the special Core case, so find an ancestor
					{
						list($loopInd, $loopID) = $this->currSessDataPos($foreignTbl);
						if ($loopID > 0)
						{
							$newLink = [$foreignTbl, $GLOBALS["DB"]->tblAbbr[$foreignTbl].$fldKey->FldName, $loopID];
							if (!in_array($newLink, $linkages["incoming"])) $linkages["incoming"][] = $newLink;
						}
					}
				}
			}
		}
		return $linkages;
	}
	
	protected function findRecLinkOutgoing($tbl, $linkages)
	{
		$eval = "";
		foreach ($linkages["outgoing"] as $i => $link)
		{
			$eval .= "where('" . $link[0] . "', '" . $link[1] . "')->";
		}
		eval("\$recObj = " . $GLOBALS["DB"]->abbrModelDir()
			. $GLOBALS["DB"]->tblModels[$tbl] 
			. "::" . $eval . "first();");
		return $recObj;
	}
	
	public function newDataRecordSimple($tbl = '', $fld = '', $newVal = -3, $linkages = array(), $forceAdd = false)
	{
		//echo 'newDataRecordSimple(' . $tbl . ', ' . $fld . ', ' . $newVal . ')<br />';
		if (sizeof($linkages) == 0) $linkages = $this->getRecordLinks($tbl, $fld, $newVal, true);
		//echo '<pre>'; print_r($linkages); echo '</pre>';
		$recObj = $this->checkNewDataRecordSimple($tbl, $fld, $newVal, $linkages, $forceAdd);
		//echo 'newDataRecordSimple - recObj: <pre>'; print_r($recObj); echo '</pre>';
		if (!$recObj || sizeof($recObj) == 0 || $forceAdd)
		{
			eval("\$recObj = new " . $GLOBALS["DB"]->abbrModelDir()
				. $GLOBALS["DB"]->tblModels[$tbl] . ";");
			if (sizeof($linkages["outgoing"]) > 0)
			{
				foreach ($linkages["outgoing"] as $i => $link)
				{
					$recObj->{ $link[0] } = $link[1];
				}
			}
			$recObj->save();
			//echo 'newDataRecordSimple - new: ' . $recObj->getKey() . ')<br />';
			$setInd = $this->initDataSet($tbl);
			$this->dataSets[$tbl][$setInd] = $recObj;
			$this->id2ind[$tbl][$recObj->getKey()] = $setInd;
		}
		return $recObj;
	}
	
	public function newDataRecord($tbl = '', $fld = '', $newVal = -3, $forceAdd = false)
	{
		//echo 'newDataRecord(' . $tbl . ', ' . $fld . ', ' . $newVal . ')<br />';
		$linkages = $this->getRecordLinks($tbl, $fld, $newVal);
		//echo '<pre>'; print_r($linkages); echo '</pre>';
		$recObj = $this->checkNewDataRecord($tbl, $fld, $newVal, $linkages);
		//if ($tbl == 'Civilians') echo '<pre>'; print_r($recObj); echo '</pre>';
		if (!$recObj || sizeof($recObj) == 0 || $forceAdd)
		{
			//if ($tbl == 'Civilians') echo 'newDataRecord(' . $tbl . ', ' . $fld . ', ' . $newVal . ') not found OR ' . (($forceAdd) ? '<i>forced</i>' : '<i>not forced</i>') . '<br />';
			if (sizeof($linkages["incoming"]) == 0)
			{
				$recObj = $this->newDataRecordSimple($tbl, $fld, $newVal, $linkages, $forceAdd);
			}
			else
			{
				foreach ($linkages["incoming"] as $link)
				{
					$recObj = $this->newDataRecordSimple($tbl, $fld, $newVal, $linkages, $forceAdd);
					$incomingInd = $this->getRowInd($link[0], intVal($link[2]));
					//echo 'checkNewDataRecord incoming didn\'t find parent index: ' . $incomingInd . '<br />';
					if ($incomingInd >= 0)
					{
						$this->dataSets[$link[0]][$incomingInd]->{ $link[1] } = $recObj->getKey();
						$this->dataSets[$link[0]][$incomingInd]->save();
					}
				}
			}
			$this->refreshDataSets();
		}
		return $recObj;
	}
	
	public function checkNewDataRecordSimple($tbl = '', $fld = '', $newVal = -3, $linkages = array(), $forceAdd = false)
	{
		//echo 'checkNewDataRecordSimple(' . $tbl . ', ' . $fld . ', ' . $newVal . ')<br />';
		if (sizeof($linkages) == 0) $linkages = $this->getRecordLinks($tbl, $fld, $newVal, true);
		//echo '<pre>'; print_r($linkages); echo '</pre>';
		if (sizeof($linkages["outgoing"]) > 0)
		{
			$recObj = $this->findRecLinkOutgoing($tbl, $linkages);
			if ($recObj && sizeof($recObj) > 0) return $recObj;
		}
		return [];
	}
	
	public function checkNewDataRecord($tbl = '', $fld = '', $newVal = -3, $linkages = array(), $forceAdd = false)
	{
		$recObj = [];
		if (sizeof($linkages) == 0) $linkages = $this->getRecordLinks($tbl, $fld, $newVal);
		//echo 'checkNewDataRecord(' . $tbl . ', ' . $fld . ', ' . $newVal . ')<pre>'; print_r($linkages); echo '</pre>';
		if (sizeof($linkages["incoming"]) > 0)
		{
			foreach ($linkages["incoming"] as $link)
			{
				$incomingInd = $this->getRowInd($link[0], intVal($link[2]));
				//if ($incomingInd >= 0) { echo 'found incomingInd: ' . $incomingInd . ', looking for field ' . $link[1] . '<pre>'; if (isset($this->dataSets[$link[0]])) { print_r($this->dataSets[$link[0]][$incomingInd]); } echo '</pre>'; }
				if (isset($this->dataSets[$link[0]][$incomingInd]->{ $link[1] }) && intVal($this->dataSets[$link[0]][$incomingInd]->{ $link[1] }) > 0)
				{
					//echo 'checkNewDataRecord(' . $tbl . ', ' . $fld . ', ' . $newVal . ') incoming found parent index: ' . $incomingInd . '<br />';
					$recInd = $this->getRowInd($tbl, intVal($this->dataSets[$link[0]][$incomingInd]->{ $link[1] }));
					if ($recInd >= 0) $recObj = $this->dataSets[$tbl][$recInd];
				}
				//else echo 'checkNewDataRecord(' . $tbl . ', ' . $fld . ', ' . $newVal . ') ' . $link[0] . ', ' . $link[2] . ' incoming NOT found parent index: ' . $incomingInd . '<br />';
			}
		}
		if (!$recObj || sizeof($recObj) == 0)
		{
			$recObj = $this->checkNewDataRecordSimple($tbl, $fld, $newVal, $linkages, $forceAdd);
		}
		return $recObj;
	}
	
	public function deleteDataRecord($tbl = '', $fld = '', $newVal = -3)
	{
		//echo 'deleteDataRecord(' . $tbl . ', ' . $fld . ', ' . $newVal . ')<br />';
		$linkages = $this->getRecordLinks($tbl, $fld, $newVal);
		if (sizeof($linkages["incoming"]) == 0)
		{
			$delObj = $this->findRecLinkOutgoing($tbl, $linkages);
			if ($delObj && sizeof($delObj) > 0) $delObj->delete();
		}
		else
		{
			foreach ($linkages["incoming"] as $link)
			{
				$incomingInd = $this->getRowInd($link[0], intVal($link[2]));
				if (isset($this->dataSets[$link[0]][$incomingInd]->{ $link[1] }))
				{
					$recInd = $this->getRowInd($tbl, intVal($this->dataSets[$link[0]][$incomingInd]->{ $link[1] }));
					if ($recInd >= 0)
					{
						$this->dataSets[$tbl][$recInd]->delete();
						$this->dataSets[$link[0]][$incomingInd]->{ $link[1] } = NULL;
						$this->dataSets[$link[0]][$incomingInd]->save();
					}
				}
			}
		}
		$this->refreshDataSets();
		return true;
	}
	
	protected function dataFind($tbl, $rowID)
	{
		if ($rowID <= 0) return [];
		//echo "dataFind - recObj = " . $GLOBALS["DB"]->abbrModelDir() . Models\\" . $GLOBALS["DB"]->tblModels[$tbl] . "::find(" . $rowID . ")<br /> \n";
		eval("\$recObj = " . $GLOBALS["DB"]->abbrModelDir()
			. $GLOBALS["DB"]->tblModels[$tbl] . "::find(" . $rowID . ");");
		return $recObj;
	}
	
	protected function dataWhere($tbl, $where, $whereVal, $operator = "=", $getFirst = "get")
	{
		//echo "dataFind - recObj = " . $GLOBALS["DB"]->abbrModelDir() . $GLOBALS["DB"]->tblModels[$tbl] . "::where('" . $where . "', '" . $operator . "', '" . $whereVal . "')->first()<br /> \n";
		eval("\$recObj = " . $GLOBALS["DB"]->abbrModelDir()
			. $GLOBALS["DB"]->tblModels[$tbl] 
			. "::where('" . $where . "', '" . $operator . "', '" . $whereVal . "')"
			. "->orderBy('" . $GLOBALS["DB"]->tblAbbr[$tbl] . "ID', 'asc')"
			. "->" . $getFirst . "();");
		return $recObj;
	}
	
	public function dataHas($tbl, $rowID = -3)
	{
		if ($rowID <= 0) return (isset($this->dataSets[$tbl]) && sizeof($this->dataSets[$tbl]) > 0);
		//echo 'dataHas( tbl: ' . $tbl . ', recID: ' . $rowID . '<br />';
		$rowInd = $this->getRowInd($tbl, $rowID);
		if ($rowInd >= 0 && isset($this->dataSets[$tbl]) && $this->dataSets[$tbl][$rowInd]->getKey() == $rowID)
		{
			return true;
		}
		return false;
	}
	
	public function getRowInd($tbl, $rowID)
	{
		if ($rowID > 0 && isset($this->id2ind[$tbl]) && isset($this->id2ind[$tbl][$rowID]))
		{
			if (intVal($this->id2ind[$tbl][$rowID]) >= 0) return $this->id2ind[$tbl][$rowID];
		}
		// else double-check
		if ($rowID > 0 && isset($this->dataSets[$tbl]) && sizeof($this->dataSets[$tbl]) > 0)
		{
			foreach ($this->dataSets[$tbl] as $ind => $d)
			{
				if ($d->getKey() == $rowID)
				{
					$this->initDataSet($tbl);
					$this->id2ind[$tbl][$rowID] = $ind;
					return $ind;
				}
			}
		}
		return -3;
	}
	
	public function getRowById($tbl, $rowID)
	{
		$rowInd = $this->getRowInd($tbl, $rowID);
		if ($rowInd >= 0) return $this->dataSets[$tbl][$rowInd];
		return [];
	}
	
	public function dataFieldExists($tbl, $ind, $fld)
	{
		return (isset($this->dataSets[$tbl]) && isset($this->dataSets[$tbl][$ind])
			&& isset($this->dataSets[$tbl][$ind]->{ $fld }));
	}
	
	public function getLoopRows($loopName)
	{
		$rows = array();
		if (isset($this->loopItemIDs[$loopName]) && sizeof($this->loopItemIDs[$loopName]) > 0)
		{
			foreach ($this->loopItemIDs[$loopName] as $itemID)
			{
				$rows[] = $this->getRowById($GLOBALS["DB"]->dataLoops[$loopName]->DataLoopTable, $itemID);
			}
		}
		return $rows;
	}
	
	public function getLoopRowIDs()
	{
		if (isset($this->loopItemIDs[$loopName])) return $this->loopItemIDs[$loopName];
		return [];
	}
	
	public function getLoopIndFromID($loopName, $itemID)
	{
		if (isset($this->loopItemIDs[$loopName]) && sizeof($this->loopItemIDs[$loopName]) > 0)
		{
			foreach ($this->loopItemIDs[$loopName] as $ind => $id)
			{
				if ($id == $itemID) return $ind;
			}
		}
		return -1;
	}
	
	public function leaveCurrLoop()
	{
		$this->loopTblID = $this->loopItemsNextID = -3;
		$this->loopItemIDsDone = array();
		return true;
	}
	
	protected function processSubObjs($tbl1, $tbl1ID, $tbl1Ind, $tbl2, $subObjs)
	{
		//echo 'processSubObjs(' . $tbl1 . ' - ' . $tbl2 . '<pre>'; print_r($subObjs); echo '</pre>';
		if ($subObjs && sizeof($subObjs) > 0)
		{
			foreach ($subObjs as $subObj)
			{
				if ($subObj && sizeof($subObj) > 0)
				{
					if (!$this->dataHas($tbl2, $subObj->getKey()))
					{
						$this->addToMap($tbl1, $tbl1ID, $tbl1Ind, $tbl2, $subObj->getKey());
						$this->loadData($tbl2, $subObj->getKey(), $subObj);
					}
				}
			}
		}
		return true;
	}
	
	protected function addToMap($tbl1, $tbl1ID, $tbl1Ind, $tbl2, $tbl2ID, $tbl2Ind = -3, $lnkTbl = '')
	{
		//echo 'addToMap('.$tbl1.', '.$tbl1ID.', '.$tbl1Ind.', '.$tbl2.', '.$tbl2ID.', '.$tbl2Ind.', '.$lnkTbl.'<br />';
		if (trim($tbl1) != '' && trim($tbl2) != '' && $tbl1ID > 0 && $tbl1Ind >= 0 && $tbl2ID > 0)
		{
			if (!isset($this->kidMap[$tbl1])) 			$this->kidMap[$tbl1] = array();
			if (!isset($this->kidMap[$tbl1][$tbl2])) 	$this->kidMap[$tbl1][$tbl2] = [ "id" => [], "ind" => [] ];
			if (!isset($this->parentMap[$tbl2])) 		$this->parentMap[$tbl2] = array();
			if (!isset($this->parentMap[$tbl2][$tbl1])) $this->parentMap[$tbl2][$tbl1] = [ "id" => [], "ind" => [] ];
			
			if ($tbl2Ind < 0) { // !presuming it's about to be loaded
				$tbl2Ind = (isset($this->dataSets[$tbl2])) ? sizeof($this->dataSets[$tbl2]) : 0;
			}
			
			$this->kidMap[$tbl1][$tbl2]["id" ][$tbl1ID] 	= $tbl2ID;
			$this->kidMap[$tbl1][$tbl2]["ind"][$tbl1Ind] 	= $tbl2Ind;
			$this->parentMap[$tbl2][$tbl1]["id" ][$tbl2ID] 	= $tbl1ID;
			$this->parentMap[$tbl2][$tbl1]["ind"][$tbl2Ind] = $tbl1Ind;
		}
		return false;
	}
	
	public function getChild($tbl1, $tbl1ID, $tbl2, $type = "id")
	{
		if (trim($tbl1) != '' && trim($tbl2) != '' && $tbl1ID >= 0 
			&& isset($this->kidMap[$tbl1]) && isset($this->kidMap[$tbl1][$tbl2]) 
			&& isset($this->kidMap[$tbl1][$tbl2][$type][$tbl1ID]))
		{
			return $this->kidMap[$tbl1][$tbl2][$type][$tbl1ID];
		}
		return -3;
	}
	
	public function getChildRow($tbl1, $tbl1ID, $tbl2)
	{
		$childID = $this->getChild($tbl1, $tbl1ID, $tbl2);
		//echo 'getChildRow(' . $tbl1 . ', ' . $tbl1ID . ', ' . $tbl2 . ') = id: ' . $childID . '<br />';
		if ($childID > 0) return $this->getRowById($tbl2, $childID);
		return [];
	}
	
	public function getChildRows($tbl1, $tbl1ID, $tbl2)
	{
		$retArr = array();
		if (trim($tbl1) != '' && trim($tbl2) != '' && $tbl1ID >= 0 
			&& isset($this->kidMap[$tbl1]) && isset($this->kidMap[$tbl1][$tbl2]) 
			&& isset($this->kidMap[$tbl1][$tbl2]["id"][$tbl1ID])
			&& intVal($this->kidMap[$tbl1][$tbl2]["id"][$tbl1ID]) > 0)
		{
			$retArr[] = $this->getRowById($tbl2, $this->kidMap[$tbl1][$tbl2]["id"][$tbl1ID]);
		}
		return $retArr;
	}
	
	public function sessChildIDFromParent($tbl2)
	{
		for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--)
		{
			if ($this->dataBranches[$i]["branch"] != $tbl2)
			{
				$tbl2ID = $this->getChild($this->dataBranches[$i]["branch"], $this->dataBranches[$i]["itemID"], $tbl2);
				//echo 'sessChildIDFromParent(' . $tbl2 . ' VS ' . $this->dataBranches[$i]["branch"] . ', ' . $this->dataBranches[$i]["itemID"] . ' ... ' . $tbl2ID . '<br />';
				if ($tbl2ID > 0) return $tbl2ID;
			}
		}
		return -3;
	}

	
	protected function getAllTableIDs($tbl)
	{
		$tmpIDs = array();
		if (isset($this->dataSets[$tbl]) && sizeof($this->dataSets[$tbl]) > 0)
		{
			foreach ($this->dataSets[$tbl] as $recObj) $tmpIDs[] = $recObj->getKey();
		}
		return $tmpIDs;
	}
	
	public function getLoopDoneItems($loopName, $fld = '')
	{
		$tbl = $GLOBALS["DB"]->dataLoops[$loopName]->DataLoopTable;
		if (trim($fld) == '')
		{
			list($tbl, $fld) = $GLOBALS["DB"]->splitTblFld($GLOBALS["DB"]->dataLoops[$loopName]->DataLoopDoneFld);
		}
		$this->loopItemIDsDone = $saves = array();
		$saves = SLNodeSaves::where('NodeSaveSession', $this->coreID)
			->where('NodeSaveTblFld', 'LIKE', $tbl.':'.$fld)
			->get();
		if ($saves && sizeof($saves) > 0)
		{
			foreach ($saves as $save)
			{
				if (in_array($save->NodeSaveLoopItemID, $this->loopItemIDs[$loopName]) 
					&& !in_array($save->NodeSaveLoopItemID, $this->loopItemIDsDone))
				{
					$this->loopItemIDsDone[] = $save->NodeSaveLoopItemID;
				}
			}
		}
		$this->loopItemsNextID = -3;
		if (sizeof($this->loopItemIDs[$loopName]) > 0)
		{
			foreach ($this->loopItemIDs[$loopName] as $id)
			{
				if ($this->loopItemsNextID <= 0 && !in_array($id, $this->loopItemIDsDone))
				{
					$this->loopItemsNextID = $id;
				}
			}
		}
		//echo 'getLoopDoneItems(), loopName: ' . $loopName . ', loopItemIDs, Done: <pre>'; print_r($this->loopItemIDs[$loopName]); print_r($this->loopItemIDsDone); echo '</pre> (fld: ' . $fld. ')<br />';
		return $this->loopItemIDsDone;
	}
	
	public function createNewDataLoopItem($nID = -3)
	{
		//echo 'createNew: ' . $GLOBALS["DB"]->sessLoops[0]->SessLoopName . ' = ' . $GLOBALS["DB"]->sessLoops[0]->SessLoopItemID . '<pre>'; print_r($GLOBALS["DB"]->sessLoops); echo '</pre>';
		if (intVal($GLOBALS["DB"]->closestLoop["obj"]->DataLoopAutoGen) == 1)
		{	// auto-generate new record in the standard way
			$newFld = $newVal = '';
			$GLOBALS["DB"]->closestLoop["obj"]->loadConds();
			if (sizeof($GLOBALS["DB"]->closestLoop["obj"]->conds) > 0)
			{
				if ($GLOBALS["DB"]->closestLoop["obj"]->conds && sizeof($GLOBALS["DB"]->closestLoop["obj"]->conds) > 0)
				{
					foreach ($GLOBALS["DB"]->closestLoop["obj"]->conds as $i => $cond)
					{
						$fld = $GLOBALS["DB"]->getFullFldNameFromID($cond->CondField, false);
						if (trim($newFld) == '' && $GLOBALS["DB"]->tbl[$cond->CondTable] == $GLOBALS["DB"]->closestLoop["obj"]->DataLoopTable 
							&& trim($fld) != '' && $cond->CondOperator == '{' && sizeof($cond->condVals) == 1)
						{
							$newFld = $fld;
							$newVal = $cond->condVals[0];
						}
					}
				}
			}
			$recObj = $this->newDataRecord($GLOBALS["DB"]->closestLoop["obj"]->DataLoopTable, $newFld, $newVal, true);
			$GLOBALS["DB"]->sessLoops[0]->SessLoopItemID = $GLOBALS["DB"]->closestLoop["itemID"] = $recObj->getKey();
			$GLOBALS["DB"]->sessLoops[0]->save();
			//echo 'createNew: ' . $GLOBALS["DB"]->sessLoops[0]->SessLoopName . ' = ' . $GLOBALS["DB"]->sessLoops[0]->SessLoopItemID . ', new rec ID: ' . $recObj->getKey() . '<pre>'; print_r($recObj); echo '</pre>';
			$this->logDataSave($nID, $GLOBALS["DB"]->closestLoop["obj"]->DataLoopTable, 'SetNavLoop', 
				'AddingItem #' . $GLOBALS["DB"]->closestLoop["itemID"], $GLOBALS["DB"]->closestLoop["loop"]);
			return $recObj->getKey();
		}
		return -3;
	}
	
	public function startTmpDataBranch($tbl, $itemID = -3)
	{
		//echo 'startTmpDataBranch(tbl: ' . $tbl . ', itemID: ' . $itemID . '<br />';
		$foundBranch = false;
		for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--)
		{
			if ($this->dataBranches[$i]["branch"] == $tbl)
			{
				$foundBranch = true;
				if (intVal($this->dataBranches[$i]["itemID"]) <= 0 && intVal($itemID) > 0)
				{
					$this->dataBranches[$i]["itemID"] = $itemID;
				}
			}
		}
		if (!$foundBranch)
		{
			if (intVal($itemID) <= 0) $itemID = $this->sessChildIDFromParent($tbl);
			$this->dataBranches[] = [
				"branch" 	=> $tbl,
				"loop" 		=> '',
				"itemID"	=> $itemID
			];
		}
		//echo 'startTmpDataBranch(tbl: ' . $tbl . ', itemID: ' . $itemID . '<div class="pL20 f8">'; print_r($this->dataBranches); echo '</div>';
		return true;
	}

	public function endTmpDataBranch($tbl)
	{
		//echo 'endTmpDataBranch(tbl: ' . $tbl . '<br />';
		for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--)
		{
			if ($tbl == $this->dataBranches[$i]["branch"])
			{
				unset($this->dataBranches[$i]);
			}
		}
		return true;
	}

	public function currSessDataPos($tbl, $hasParentDataManip = false)
	{
		if (trim($tbl) == '') return [-3, -3];
		if ($tbl == $GLOBALS["DB"]->coreTbl) return [0, $this->coreID];
		$itemID = $itemInd = -3;
		//if ($tbl == 'Allegations') { echo ' - currSessDataPos.1A)(tbl: ' . $tbl . ', <br />'; }
		$tblNew = $this->isCheckboxHelperTable($tbl);
		//if ($tblNew != $tbl) echo ' - FOUND isCheckboxHelperTable! tbl: ' . $tbl . ' = > tblNew: ' . $tblNew . '<br />';
		$tbl = $tblNew;
		//if ($tbl == 'Allegations') { echo ' - currSessDataPos.1B)(tbl: ' . $tbl . ', <div class="pL20 f8">'; print_r($this->dataBranches); echo '</div>'; }
		for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--)
		{
			//if ($tbl == 'Allegations') { echo ' - currSessDataPos.Z(tbl: ' . $tbl . ', branch: ' .  $this->dataBranches[$i]["branch"] . ' --- ( itemID: ' . $itemID . ' )<br />'; }
			if (intVal($itemID) <= 0)
			{
				list($itemInd, $itemID) = $this->currSessDataPosBranch($tbl, $this->dataBranches[$i]);
			}
		}
		if (intVal($itemID) <= 0 && !$hasParentDataManip)
		{
			//if ($tbl == 'Allegations') { echo ' - currSessDataPos.2)(tbl: ' . $tbl . ', --- FOUND itemID: ' . $itemID . '<br />'; }
			$itemID = $this->sessChildIDFromParent($tbl);
			if ($itemID > 0)
			{
				$itemInd = $this->getRowInd($tbl, $itemID);
				for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--)
				{
					if ($this->dataBranches[$i]["branch"] == $tbl && $this->dataBranches[$i]["loop"] == '')
					{
						$this->dataBranches[$i]["itemID"] = $itemID;
					}
				}
			}
		}
		//if ($tbl == 'Allegations') { echo ' - currSessDataPos.3)(tbl: ' . $tbl . ', --- FOUND PART 2!! [' . $itemInd . '] = > itemID: ' . $itemID . '<br />'; }
		return [$itemInd, $itemID];
	}
	
	public function currSessDataPosBranch($tbl, $branch)
	{
		$itemID = 0;
		//if ($tbl == 'Allegations' || $branch["branch"] == 'Allegations') { echo 'currSessDataPosBranch(' . $tbl . ', branch: ' . $branch["branch"] . ', sizeof(dataSets[' . $tbl . ' = ' . ((isset($this->dataSets[$tbl])) ? sizeof($this->dataSets[$tbl]) : 0) . '<br />'; }
		if ($tbl == $branch["branch"])
		{
			if (trim($branch["loop"]) != '')
			{
				$itemID = $GLOBALS["DB"]->getSessLoopID($branch["loop"]);
			}
			elseif (intVal($branch["itemID"]) > 0)
			{
				$itemID = $branch["itemID"];
			}
			/*
			elseif (isset($this->dataSets[$tbl]) && isset($this->dataSets[$tbl][0])) 
			{
				$itemID = $this->dataSets[$tbl][0]->getKey();
			}
			*/
		}
		/* this needs to happen elsewhere, in a more specific usage?
		elseif (trim($branch["branch"]) != '' && trim($branch["loop"]) == ''
			&& isset($this->dataSets[$branch["branch"]]) && isset($this->dataSets[$branch["branch"]][0])) 
		{
			$itemID = $this->dataSets[$branch["branch"]][0]->getKey();
			if ($itemID > 0 && isset($this->id2ind[$branch["branch"]]) && isset($this->id2ind[$branch["branch"]][$itemID])) $itemInd = $this->id2ind[$branch["branch"]][$itemID];
			echo 'currSessDataPosBranch(' . $tbl . ', ' . $branch["branch"] . ', ' . $branch["loop"] . ', um? ' . $tbl . '[' . $itemInd . '] = ' . $itemID . ', <br />';
		}
		*/
		$itemInd = $this->getRowInd($tbl, $itemID);
		//if ($tbl == 'Allegations') { echo 'um? ' . $tbl . ' [' . $itemInd . '] = ' . $itemID . ', '; print_r($branch); echo '<br />'; }
		return [$itemInd, $itemID];
	}
	
	public function currSessDataPosBranchOnly($tbl)
	{
		$itemID = $itemInd = 0;
		$tbl = $this->isCheckboxHelperTable($tbl);
		for ($i = (sizeof($this->dataBranches)-1); $i >= 0; $i--)
		{
			if ($itemID <= 0)
			{
				list($itemInd, $itemID) = $this->currSessDataPosBranch($tbl, $this->dataBranches[$i]);
			}
		}
		return [$itemInd, $itemID];
	}
	
	// Here we're trying to find the closest relative within current tree navigation to the table and field in question. 
	public function currSessData($nID, $tbl, $fld = '', $action = 'get', $newVal = '', $hasParentDataManip = false)
	{
		if (trim($tbl) == '' || trim($fld) == '' || !$this->loaded) return '';
		if (in_array($nID, $this->checkboxNodes))
		{
			return $this->currSessDataCheckbox($nID, $tbl, $fld);
		}
		list($itemInd, $itemID) = $this->currSessDataPos($tbl, $hasParentDataManip);
		//if (in_array($nID, [430])) { echo 'currSessData(' . $nID . ', '.$tbl.', '.$fld.', '.$action.', '.$newVal.', itemInd: '.$itemInd.', itemID: '.$itemID.', <div class="pL20">dataBranches: '; print_r($this->dataBranches); echo '</div>'; }
		if ($itemInd < 0 || $itemID <= 0) return '';
		if ($action == 'get')
		{
			if ($this->dataFieldExists($tbl, $itemInd, $fld))
			{
				//if (in_array($nID, [430])) { echo ' - getData, '.$tbl.', '.$fld.', itemInd: ' . $itemInd . ' = <b>' . $this->dataSets[$tbl][$itemInd]->{ $fld } . '</b><br />'; }
				return $this->dataSets[$tbl][$itemInd]->{ $fld };
			}
		}
		elseif ($action == 'update' && $fld != ($GLOBALS["DB"]->tblAbbr[$tbl].'ID'))
		{
			$this->logDataSave($nID, $tbl, $itemID, $fld, $newVal);
			//if (in_array($nID, [430])) { echo 'currSessData(' . $nID . ', '.$tbl.', '.$fld.', '.$action.', '.$newVal.', itemInd: '.$itemInd.', itemID: '.$itemID.', dataBranches:<pre>'; print_r($this->dataBranches); echo '</pre><br />'; }
			if (isset($this->dataSets[$tbl]) && isset($this->dataSets[$tbl][$itemInd]))
			{
				$this->dataSets[$tbl][$itemInd]->{ $fld } = $newVal;
				$this->dataSets[$tbl][$itemInd]->save();
				//if (in_array($nID, [430])) { echo 'nID: ' . $nID . ', new dataSets[' . $tbl . '][' . $itemInd . ']->{ ' . $fld . ' } (' . $this->dataSets[$tbl][$itemInd]->getKey() . ') = ' . $this->dataSets[$tbl][$itemInd]->{ $fld } . '<br />'; }
				return $newVal;
			}
			else
			{
				//$GLOBALS["errors"] .= 'Couldn\'t find dataSets[' . $tbl . '][' . $itemInd . '] for ' . $fld . '<br />';
				//echo '<pre>'; print_r($this->dataSets); echo '</pre>';
			}
		}
		return $newVal;
	}
	
	public function currSessDataCheckbox($nID, $tbl, $fld = '', $action = 'get', $newVals = array())
	{
		$helpInfo = $this->getCheckboxHelperInfo($tbl, $fld);
		//echo 'currSessDataCheckbox() nID: ' . $nID . ', tbl: '.$tbl.', fld: '.$fld.', action: ' . $action . ', pastVals: '; print_r($helpInfo["pastVals"]); 
		//echo ', newVals: '; print_r($newVals); echo ', parentID: ' . $helpInfo["parentID"] . '<br />';
		if ($action == 'get')
		{
			return ((sizeof($helpInfo["pastVals"]) > 0) ? ';'.implode(';;', $helpInfo["pastVals"]).';' : '');
		}
		elseif ($action == 'update')
		{
			$this->logDataSave($nID, $tbl, $helpInfo["parentID"], $fld, $newVals);
			// check for newly submitted responses...
			if (sizeof($newVals) > 0)
			{
				foreach ($newVals as $i => $val)
				{
					if (!in_array($val, $helpInfo["pastVals"]))
					{
						eval("\$newObj = new " . $GLOBALS["DB"]->abbrModelDir()
							. $GLOBALS["DB"]->tblModels[$helpInfo["link"]->DataHelpTable].";");
						$newObj->{ $helpInfo["link"]->DataHelpKeyField } 	= $helpInfo["parentID"];
						$newObj->{ $helpInfo["link"]->DataHelpValueField } 	= $val;
						$newObj->save();
						//echo 'adding checkbox val:<pre>'; print_r($newObj); echo '</pre>';
					}
				}
			}
			// check for previously submitted responses are being deselected...
			if (sizeof($helpInfo["pastVals"]) > 0)
			{
				foreach ($helpInfo["pastVals"] as $i => $val)
				{
					if (!in_array($val, $newVals))
					{
						//echo 'deleting checkbox val: ' . $helpInfo["pastValToID"][$val] . '<br />';
						$this->deleteDataItem($nID, $helpInfo["link"]->DataHelpTable, $helpInfo["pastValToID"][$val]);
					}
				}
			}
		}
		return '';
	}
	
	public function getCheckboxHelperInfo($tbl, $fld) {
		$helpInfo = [ "link" => [], "parentID" => -3, "pastVals" => [], "pastObjs" => [], "pastValToID" => [] ];
		if (isset($GLOBALS["DB"]->dataHelpers) && sizeof($GLOBALS["DB"]->dataHelpers) > 0)
		{
			foreach ($GLOBALS["DB"]->dataHelpers as $helper)
			{
				if ($helper->DataHelpTable == $tbl && $helper->DataHelpValueField == $fld)
				{
					$helpInfo["link"] = $helper;
					list($parentInd, $helpInfo["parentID"]) = $this->currSessDataPos($helper->DataHelpParentTable); //BranchOnly
					$helpInfo["pastObjs"] = $this->dataWhere($helper->DataHelpTable, $helper->DataHelpKeyField, $helpInfo["parentID"]);
					if ($helpInfo["pastObjs"] && sizeof($helpInfo["pastObjs"]) > 0)
					{
						foreach ($helpInfo["pastObjs"] as $obj)
						{
							$helpInfo["pastVals"][] = $obj->{ $helper->DataHelpValueField };
							$helpInfo["pastValToID"][$obj->{ $helper->DataHelpValueField }] = $obj->getKey();
						}
					}
				}
			}
		}
		return $helpInfo;
	}
	
	public function deleteDataItem($nID, $tbl = '', $itemID = -3)
	{
		$itemInd = $this->getRowInd($tbl, $itemID);
		if ($itemID <= 0 || $itemInd < 0) return false;
		eval("" . $GLOBALS["DB"]->abbrModelDir() . $GLOBALS["DB"]->tblModels[$tbl] 
			. "::find(" . $itemID . ")->delete();");
		unset($this->dataSets[$tbl][$itemInd]);
		unset($this->id2ind[$tbl][$itemID]);
		return true;
	}
	
	
	public function logDataSave($nID = -3, $tbl = '', $itemID = -3, $fld = '', $newVal = '')
	{
		$nodeSave = new SLNodeSaves;
		$nodeSave->NodeSaveSession 		= $this->coreID;
		$nodeSave->NodeSaveNode 		= $nID;
		$nodeSave->NodeSaveTblFld 		= $tbl . ':' . $fld;
		$nodeSave->NodeSaveLoopItemID 	= $itemID;
		if (!is_array($newVal))
		{
			$nodeSave->NodeSaveNewVal = $newVal;
		}
		else
		{
			ob_start();
			print_r($newVal);
			$nodeSave->NodeSaveNewVal = ob_get_contents();
			ob_end_clean();
		}
		$nodeSave->save();
		return true;
	}
	
	protected function loadSessionDataLog($nID = -3, $tbl = '', $fld = '', $set = '')
	{
		$qryWheres = "where('NodeSaveSession', \$this->coreID)->where('NodeSaveNode', ".$nID.")->";
		if (trim($tbl) != '' && trim($fld) != '')
		{
			$qryWheres .= "where('NodeSaveTblFld', '" . $tbl . ":" . $fld . ((trim($set) != '') ? "[" . $set . "]" : "") . "')->";
		}
		if (isset($GLOBALS["DB"]->closestLoop["itemID"]) && intVal($GLOBALS["DB"]->closestLoop["itemID"]) > 0)
		{
			$qryWheres .= "where('NodeSaveLoopItemID', " . $GLOBALS["DB"]->closestLoop["itemID"] . ")->";
		}
		eval("\$nodeSave = SurvLoop\\Models\\SLNodeSaves::" . $qryWheres . "orderBy('created_at', 'desc')->first();"); // select('NodeSaveNewVal')->
		if ($nodeSave && isset($nodeSave->NodeSaveNewVal)) return $nodeSave->NodeSaveNewVal;
		return '';
	}
	
	public function parseCondition($cond = array(), $recObj = array(), $nID = -3)
	{
		//if ($cond->CondTag == '#HasEvent') { echo 'parseCondition(<pre>'; print_r($cond); echo '</pre>, recObj: <pre>'; print_r($recObj); echo '</pre>'; }
		$passed = true;
		if ($cond && isset($cond->CondDatabase) && $cond->CondOperator != 'CUSTOM')
		{
			$tblName = $GLOBALS["DB"]->tbl[$cond->CondTable];
			$loopName = ((intVal($cond->CondLoop) > 0) ? $GLOBALS["DB"]->dataLoopNames[$cond->CondLoop] : '');
			//if ($tbl != $setTbl) list($setTbl, $setSet, $loopItemID) = $this->getDataSetTblTranslate($set, $tbl, $loopItemID);
			if ($cond->CondOperator == 'EXISTS>')
			{
				//if ($cond->CondTag == '#HasEvent') { echo '&nbsp;&nbsp;Conds {EXISTS... CondTable: ' . $cond->CondTable . ', tblName: ' . $tblName . ', loopName: '.$loopName.'<pre>'; print_r($this->dataSets[$tblName]); echo '</pre>'; }
				if (!isset($this->dataSets[$tblName]) || (intVal($cond->CondLoop) > 0 && !isset($this->loopItemIDs[$loopName])))
				{
					$passed = false;
				}
				else
				{
					$existCnt = (intVal($cond->CondLoop) > 0) ? sizeof($this->loopItemIDs[$loopName]) : sizeof($this->dataSets[$tblName]);
					if (intVal($cond->CondOperDeet) == 0) 	$passed = ($existCnt > 0);
					elseif ($cond->CondOperDeet > 0) 		$passed = ($existCnt > intVal($cond->CondOperDeet));
					elseif ($cond->CondOperDeet < 0) 		$passed = ($existCnt < ((-1)*intVal($cond->CondOperDeet)));
				}
			}
			elseif (intVal($cond->CondField) > 0)
			{
				$fldName = $GLOBALS["DB"]->getFullFldNameFromID($cond->CondField, false);
				//if ($cond->CondTag == '#HasEvent') { echo 'cond->CondField: ' . $cond->CondField . ' ; FldName: ' . $fldName . ', recObj: ' . $recObj->getKey() . '<pre>'; print_r($recObj); echo '</pre>'; }
				$currSessData = '';
				if ($recObj && $recObj->getKey() > 0) $currSessData = $recObj->{ $fldName };
				else $currSessData = $this->currSessData($nID, $tblName, $fldName);
				//if ($cond->CondTag == '#HasEvent') { echo $cond->CondTag . '&nbsp;&nbsp;currSessData:'.$currSessData.', cond->valueType: ' . $cond->valueType. ', cond->condVals: '; print_r($cond->condVals); echo ' ---- ' . ((in_array($currSessData, $cond->condVals)) ? 'true' : 'false') . '<br />'; }
				if (trim($currSessData) != '')
				{
					if ($cond->CondOperator == '{') 	$passed = (in_array($currSessData, $cond->condVals)); // ($chk && sizeof($chk) > 0);   // show node if these values
					elseif ($cond->CondOperator == '}') $passed = (!in_array($currSessData, $cond->condVals)); // (!$chk || sizeof($chk) == 0); // skip node if these values
				}
				else
				{
					if ($cond->CondOperator == '{') 	$passed = false;
					elseif ($cond->CondOperator == '}') $passed = true;
				}
			}
		}
		//if ($cond->CondTag == '#HasEvent') { echo 'passed? ' . (($passed) ? 'true' : 'false') . '<br />'; exit; }
		return $passed;
	}
	
	
	
	public function isCheckboxHelperTable($helperTbl = '')
	{
		$tbl = $helperTbl;
		if (trim($helperTbl) != '')
		{
			if (isset($GLOBALS["DB"]->dataHelpers) && sizeof($GLOBALS["DB"]->dataHelpers) > 0)
			{
				foreach ($GLOBALS["DB"]->dataHelpers as $helper)
				{
					if ($helper->DataHelpTable == $helperTbl && trim($helper->DataHelpValueField) != '') 
					{
						$tbl = $helper->DataHelpParentTable;
					}
				}
			}
		}
		return $tbl;
	}
	
}

?>