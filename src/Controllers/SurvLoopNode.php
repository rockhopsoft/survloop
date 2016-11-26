<?php
namespace SurvLoop\Controllers;

use SurvLoop\Models\SLNode;
use SurvLoop\Models\SLNodeResponses;
use SurvLoop\Models\SLConditions;
use SurvLoop\Models\SLConditionsNodes;

use SurvLoop\Controllers\CoreNode;

class SurvLoopNode extends CoreNode
{
	public $conds 		= array();
	public $responses 	= array();
	public $hasShowKids = false;
	
	public $dataManips 	= array();
	
	public $primeOpts 	= [
		"Required" 			=> 5, 
		"OneLineResponses" 	=> 17, 
		"OneLiner" 			=> 11, 
		"OnPrevLine" 		=> 13
	];
	
	// maybe initialize this way to lighten the tree's load?...
	public function loadNodeCache($nID = -3, $nCache = array())
	{
		if (sizeof($nCache) > 0)
		{
			if (isset($nCache["pID"]))		$this->parentID 	= $nCache["pID"];
			if (isset($nCache["pOrd"]))		$this->parentOrd 	= $nCache["pOrd"];
			if (isset($nCache["opts"]))		$this->nodeOpts 	= $nCache["opts"];
			if (isset($nCache["type"]))		$this->nodeType 	= $nCache["type"];
			if (isset($nCache["branch"]))	$this->dataBranch 	= $nCache["branch"];
			if (isset($nCache["store"]))	$this->dataStore 	= $nCache["store"];
			if (isset($nCache["set"]))		$this->responseSet 	= $nCache["set"];
			if (isset($nCache["def"]))		$this->defaultVal 	= $nCache["def"];
		}
		return true;
	}
	
	public function loadNodeRow($nID = -3, $nRow = array())
	{
		$this->nodeRow = array();
		if (sizeof($nRow) > 0)
		{
			$this->nodeRow = $nRow;
		}
		elseif ($nID > 0)
		{
			$this->nodeRow = SLNode::find($nID)
				->select('NodeID', 'NodeParentID', 'NodeParentOrder', 'NodeOpts', 
					'NodeType', 'NodeDataBranch', 'NodeDataStore', 
					'NodeResponseSet', 'NodeDefault');
		}
		elseif ($this->nodeID > 0)
		{
			$this->nodeRow = SLNode::find($this->nodeID)
				->select('NodeID', 'NodeParentID', 'NodeParentOrder', 'NodeOpts', 
					'NodeType', 'NodeDataBranch', 'NodeDataStore', 
					'NodeResponseSet', 'NodeDefault');
		}
		if (sizeof($nRow) > 0)
		{
			$this->parentID 	= $this->nodeRow->NodeParentID;
			$this->parentOrd 	= $this->nodeRow->NodeParentOrder;
			$this->nodeOpts 	= $this->nodeRow->NodeOpts;
			$this->nodeType 	= $this->nodeRow->NodeType;
			$this->dataBranch 	= $this->nodeRow->NodeDataBranch;
			$this->dataStore 	= $this->nodeRow->NodeDataStore;
			$this->responseSet 	= $this->nodeRow->NodeResponseSet;
			$this->defaultVal 	= $this->nodeRow->NodeDefault;
		}
		if (!isset($this->nodeRow) || sizeof($this->nodeRow) == 0)
		{
			$this->nodeRow = new SLNode;
			return false;
		}
		//$this->fillNodeRow();
		return true;
	}
	
	public function initiateNodeRow()
	{
		$this->conds = array();
		$chk = SLConditionsNodes::where('CondNodeNodeID', $this->nodeID)
			->get();
		if ($chk && sizeof($chk) > 0)
		{
			foreach ($chk as $c)
			{
				$cond = SLConditions::find($c->CondNodeCondID);
				if ($cond && sizeof($cond) > 0) $this->conds[] = $cond;
			}
		}
		if ($this->conds && sizeof($this->conds) > 0)
		{
			foreach ($this->conds as $i => $c) $c->loadVals();
		}
		$this->hasShowKids = false;
		if (sizeof($this->nodeRow) > 0)
		{
			$this->responses = SLNodeResponses::where('NodeResNode', $this->nodeID)
				->orderBy('NodeResOrd', 'asc')
				->get();
			if (sizeof($this->responses) > 0)
			{
				foreach ($this->responses as $res)
				{
					if (intVal($res->NodeResShowKids) == 1)
					{
						$this->hasShowKids = true;
					}
				}
			}
			$this->dataManips = SLNode::where('NodeParentID', $this->nodeID)
				->where('NodeType', 'Data Manip: Update')
				->orderBy('NodeParentOrder', 'asc')
				->get();
		}
		//echo $this->nodeRow->responses . ' ... <pre>'; print_r($this->responses); echo '</pre>';
		return true;
	}
	
	public function valueShowsKid($responseVal = '')
	{
		if (sizeof($this->responses) > 0)
		{
			foreach ($this->responses as $res)
			{
				if ($res->NodeResValue == $responseVal)
				{
					if (intVal($res->NodeResShowKids) == 1) return true;
					return false;
				}
			}
		}
		return false;
	}
	
	public function indexShowsKid($ind = '')
	{
		return (sizeof($this->responses) > 0 && isset($this->responses[$ind]) && intVal($this->responses[$ind]->NodeResShowKids) == 1);
	}
	
	public function splitTblFld($tblFld)
	{
		$tbl = $fld = '';
		if (trim($tblFld) != '' && strpos($tblFld, ':') !== false) list($tbl, $fld) = explode(':', $tblFld);
		return array($tbl, $fld);
	}
	
	public function getTblFld()
	{
		//echo $this->nodeID . ', <pre>'; print_r($this->nodeRow); echo '</pre>';
		if (sizeof($this->nodeRow) == 0 || !isset($this->dataStore)) $this->fillNodeRow();
		return $this->splitTblFld($this->dataStore);
	}
	
	public function nodePreview()
	{
		return substr(strip_tags($this->nodeRow->NodePromptText), 0, 20);
	}
	
	public function tierPathStr($tierPathArr = array())
	{
		//echo $this->nodeID . ', tierPathStr()<pre>'; print_r($this->nodeTierPath); echo '</pre>';
		if (sizeof($tierPathArr) == 0) return implode('-', $this->nodeTierPath).'-';
		return implode('-', $tierPathArr).'-';
	}
	
	public function checkBranch($tierPathArr = array())
	{
		$tierPathStr = $this->tierPathStr($tierPathArr);
		//if ($this->debugOn && $this->nodeID == $this->currNode()) { echo 'checkBranch? tierPathStr: ' .  $tierPathStr.', nodeTierPath: ' . $this->tierPathStr($this->nodeTierPath).'<br />'; }
		if ($tierPathStr != '') return (strpos($this->tierPathStr($this->nodeTierPath), $tierPathStr) === 0);
		return 0;
	}
	
	public function isBranch()
	{
		return ($this->nodeType == 'Branch Title');
	}
	
	public function isLoopRoot()
	{
		return ($this->nodeType == 'Loop Root');
	}
	
	public function isStepLoop()
	{
		return ($this->isLoopRoot() && $GLOBALS["DB"]->isStepLoop($this->dataBranch));
	}
	
	public function isDataManip()
	{
		return (substr($this->nodeType, 0, 10) == 'Data Manip');
	}
	
	public function isPage()
	{
		return ($this->nodeType == 'Page');
	}
	
	public function isInstruct()
	{
		return ($this->nodeType == 'Instructions');
	}
	
	public function isSpecial()
	{
		//echo '> isInstruct? ' . (($this->isInstruct()) ? 'T' : 'F') . '<br />isPage? ' . (($this->isPage()) ? 'T' : 'F') . '<br />isBranch? ' . (($this->isBranch()) ? 'T' : 'F') . '<br />isLoopRoot? ' . (($this->isLoopRoot()) ? 'T' : 'F') . '<br />isDataManip? ' . (($this->isDataManip()) ? 'T' : 'F') . '<br /> <';
		return ($this->isInstruct() || $this->isPage() 
			|| $this->isBranch() || $this->isLoopRoot() 
			|| $this->isDataManip());
	}
	
	public function isRequired()
	{
		return ($this->nodeOpts%$this->primeOpts["Required"] == 0);
	}
	
	public function isOneLiner()
	{
		return ($this->nodeOpts%$this->primeOpts["OneLiner"] == 0);
	}
	
	public function isOneLineResponses()
	{
		return ($this->nodeOpts%$this->primeOpts["OneLineResponses"] == 0);
	}
	
	public function isOnPrevLine()
	{
		return ($this->nodeOpts%$this->primeOpts["OnPrevLine"] == 0);
	}
	
	public function getManipUpdate()
	{
		if (!$this->isDataManip()) return ['', '', ''];
		$this->fillNodeRow();
		if (trim($this->dataBranch) != '')
		{
			$tbl = $this->dataBranch;
			$fld = str_replace($tbl.':', '', $this->dataStore);
		}
		else list($tbl, $fld) = $this->splitTblFld($this->dataStore);
		$newVal = (intVal($this->responseSet) > 0) 
			? intVal($this->responseSet)
			: trim($this->defaultVal);
		return [$tbl, $fld, $newVal];
	}
	
	public function printManipUpdate()
	{
		if (!$this->isDataManip() || $this->nodeType == 'Data Manip: Wrap') return '';
		$manipUpdate = $this->getManipUpdate();
		if (trim($manipUpdate[0]) == '' || $manipUpdate[1] == '') return '';
		$ret = '';
		$ret = ' , ' . $manipUpdate[1] . ' = ';
		if (isset($this->responseSet) && intVal($this->responseSet) > 0)
		{
			$ret .= $GLOBALS["DB"]->getDefValById(intVal($this->responseSet));
		}
		else
		{
			$ret .= $manipUpdate[2];
		}
		if (sizeof($this->dataManips) > 0)
		{
			foreach ($this->dataManips as $manip)
			{
				$tmpNode = new SurvLoopNode($manip->nodeID, $manip);
				$ret .= $tmpNode->printManipUpdate();
			}
		}
		return $ret;
	}
	
}

?>