<?php
namespace SurvLoop\Controllers;

use App\Models\SLNode;
use App\Models\SLNodeResponses;
use App\Models\SLConditions;
use App\Models\SLConditionsNodes;

use SurvLoop\Controllers\CoreNode;

class SurvLoopNode extends CoreNode
{
    public $conds         = [];
    public $responses     = [];
    public $hasShowKids   = false;
    public $hasPageParent = false;
    
    public $dataManips    = [];
    public $colors        = [];
    public $extraOpts     = [];
    
    public $primeOpts     = [
        "Required"         => 5, 
        "OneLineResponses" => 17, 
        "OneLiner"         => 11, 
        "RequiredInLine"   => 13
    ];
    
    // maybe initialize this way to lighten the tree's load?...
    public function loadNodeCache($nID = -3, $nCache = [])
    {
        if (sizeof($nCache) > 0) {
            if (isset($nCache["pID"]))    $this->parentID    = $nCache["pID"];
            if (isset($nCache["pOrd"]))   $this->parentOrd   = $nCache["pOrd"];
            if (isset($nCache["opts"]))   $this->nodeOpts    = $nCache["opts"];
            if (isset($nCache["type"]))   $this->nodeType    = $nCache["type"];
            if (isset($nCache["branch"])) $this->dataBranch  = $nCache["branch"];
            if (isset($nCache["store"]))  $this->dataStore   = $nCache["store"];
            if (isset($nCache["set"]))    $this->responseSet = $nCache["set"];
            if (isset($nCache["def"]))    $this->defaultVal  = $nCache["def"];
        }
        return true;
    }
    
    public function loadNodeRow($nID = -3, $nRow = [])
    {
        $this->nodeRow = [];
        if (sizeof($nRow) > 0) {
            $this->nodeRow = $nRow;
        } elseif ($nID > 0) {
            $this->nodeRow = SLNode::find($nID)
                ->select('NodeID', 'NodeParentID', 'NodeParentOrder', 'NodeOpts', 
                    'NodeType', 'NodeDataBranch', 'NodeDataStore', 
                    'NodeResponseSet', 'NodeDefault');
        } elseif ($this->nodeID > 0) {
            $this->nodeRow = SLNode::find($this->nodeID)
                ->select('NodeID', 'NodeParentID', 'NodeParentOrder', 'NodeOpts', 
                    'NodeType', 'NodeDataBranch', 'NodeDataStore', 
                    'NodeResponseSet', 'NodeDefault');
        }
        $this->copyFromRow();
        if (!isset($this->nodeRow) || sizeof($this->nodeRow) == 0) {
            $this->nodeRow = new SLNode;
            return false;
        }
        //$this->fillNodeRow();
        return true;
    }
    
    protected function copyFromRow()
    {
        if (sizeof($this->nodeRow) > 0) {
            $this->parentID    = $this->nodeRow->NodeParentID;
            $this->parentOrd   = $this->nodeRow->NodeParentOrder;
            $this->nodeOpts    = $this->nodeRow->NodeOpts;
            $this->nodeType    = $this->nodeRow->NodeType;
            $this->dataBranch  = $this->nodeRow->NodeDataBranch;
            $this->dataStore   = $this->nodeRow->NodeDataStore;
            $this->responseSet = $this->nodeRow->NodeResponseSet;
            $this->defaultVal  = $this->nodeRow->NodeDefault;
        }
        return true;
    }
    
    public function initiateNodeRow()
    {
        $this->copyFromRow();
        $this->conds = [];
        $chk = SLConditionsNodes::where('CondNodeNodeID', $this->nodeID)
            ->get();
        if ($chk && sizeof($chk) > 0) {
            foreach ($chk as $c) {
                $cond = SLConditions::find($c->CondNodeCondID);
                if ($cond && sizeof($cond) > 0) $this->conds[] = $cond;
            }
        }
        if ($this->conds && sizeof($this->conds) > 0) {
            foreach ($this->conds as $i => $c) $c->loadVals();
        }
        $this->hasShowKids = false;
        if (sizeof($this->nodeRow) > 0) {
            $this->responses = SLNodeResponses::where('NodeResNode', $this->nodeID)
                ->orderBy('NodeResOrd', 'asc')
                ->get();
            if (sizeof($this->responses) > 0) {
                foreach ($this->responses as $res) {
                    if (intVal($res->NodeResShowKids) == 1) {
                        $this->hasShowKids = true;
                    }
                }
            }
            $this->dataManips = SLNode::where('NodeParentID', $this->nodeID)
                ->where('NodeType', 'Data Manip: Update')
                ->orderBy('NodeParentOrder', 'asc')
                ->get();
        }
        if ($this->nodeType == 'Send Email') {
            $this->extraOpts["emailTo"] = $this->extraOpts["emailCC"] = $this->extraOpts["emailBCC"] = [];
            if (strpos($this->nodeRow->NodePromptAfter, '::CC::') !== false) {
                list($to, $ccs) = explode('::CC::', $this->nodeRow->NodePromptAfter);
                list($cc, $bcc) = explode('::BCC::', $ccs);
                $this->extraOpts["emailTo"]  = $GLOBALS["SL"]->mexplode(',', str_replace('::TO::', '', $to));
                $this->extraOpts["emailCC"]  = $GLOBALS["SL"]->mexplode(',', $cc);
                $this->extraOpts["emailBCC"] = $GLOBALS["SL"]->mexplode(',', $bcc);
            }
        }
        return true;
    }
    
    public function genTmpNodeRes($value)
    {
        $res = new SLNodeResponses;
        $res->NodeResNode     = $this->nodeID;
        $res->NodeResEng      = $value;
        $res->NodeResValue    = $value;
        $res->NodeResOrd      = sizeof($this->responses);
        $res->NodeResShowKids = 0;
        return $res;
    }
    
    public function valueShowsKid($responseVal = '')
    {
        if (sizeof($this->responses) > 0) {
            foreach ($this->responses as $res) {
                if ($res->NodeResValue == $responseVal) {
                    if (intVal($res->NodeResShowKids) == 1) return true;
                    return false;
                }
            }
        }
        return false;
    }
    
    public function indexShowsKid($ind = '')
    {
        return (sizeof($this->responses) > 0 && isset($this->responses[$ind]) 
            && intVal($this->responses[$ind]->NodeResShowKids) == 1);
    }
    
    public function indexMutEx($ind = '')
    {
        return (sizeof($this->responses) > 0 && isset($this->responses[$ind]) 
            && intVal($this->responses[$ind]->NodeResMutEx) == 1);
    }
    
    public function splitTblFld($tblFld)
    {
        $tbl = $fld = '';
        if (trim($tblFld) != '' && strpos($tblFld, ':') !== false) {
            list($tbl, $fld) = explode(':', $tblFld);
        }
        return array($tbl, $fld);
    }
    
    public function getTblFld()
    {
        if (sizeof($this->nodeRow) == 0 || !isset($this->dataStore)) $this->fillNodeRow();
        return $this->splitTblFld($this->dataStore);
    }
    
    public function nodePreview()
    {
        return substr(strip_tags($this->nodeRow->NodePromptText), 0, 20);
    }
    
    public function tierPathStr($tierPathArr = [])
    {
        if (sizeof($tierPathArr) == 0) return implode('-', $this->nodeTierPath).'-';
        return implode('-', $tierPathArr).'-';
    }
    
    public function checkBranch($tierPathArr = [])
    {
        $tierPathStr = $this->tierPathStr($tierPathArr);
        if ($tierPathStr != '') {
            return (strpos($this->tierPathStr($this->nodeTierPath), $tierPathStr) === 0);
        }
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
    
    public function isLoopCycle()
    {
        return ($this->nodeType == 'Loop Cycle');
    }
    
    public function isLoopSort()
    {
        return ($this->nodeType == 'Loop Sort');
    }
    
    public function isStepLoop()
    {
        return ($this->isLoopRoot() && $GLOBALS["SL"]->isStepLoop($this->dataBranch));
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
    
    public function isInstructRaw()
    {
        return ($this->nodeType == 'Instructions Raw');
    }
    
    public function isBigButt()
    {
        return ($this->nodeType == 'Big Button'); 
    }
    
    public function isHeroImg()
    {
        return ($this->nodeType == 'Hero Image');
    }
    
    public function isSpecial()
    {
        return ($this->isInstruct() || $this->isInstructRaw() || $this->isPage()  || $this->isBranch() 
            || $this->isLoopRoot() || $this->isLoopCycle() || $this->isLoopSort() || $this->isDataManip()
            || $this->isWidget() || $this->isBigButt() || $this->isHeroImg());
    }
    
    public function isWidget()
    {
        return (in_array($this->nodeType, ['Search', 'Search Results', 'Search Featured', 'Member Profile Basics', 
            'Record Full', 'Record Previews', 'Incomplete Sess Check', 'Back Next Buttons', 'Send Email']));
    }
    
    public function isLayout()
    {
        return (in_array($this->nodeType, ['Page Block', 'Layout Row', 'Layout Column']));
    }
    
    public function canBePageBlock()
    {
        return ($this->isLayout() || $this->isInstruct() || $this->isInstructRaw());
    }
    
    public function isPageBlock()
    {
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page' && $this->parentID == $GLOBALS["SL"]->treeRow->TreeRoot) {
            $this->loadPageBlockColors();
            return true;
        }
        return false;
    }
    
    public function isPageBlockSkinny()
    {
        return ($this->isPageBlock() && $this->nodeRow->NodeOpts%67 == 0);
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
    
    public function isDropdownTagger()
    {
        return ($this->nodeType == 'Drop Down' && $this->nodeRow->NodeOpts%53 == 0);
    }
    
    
    public function getManipUpdate()
    {
        if (!$this->isDataManip()) return ['', '', ''];
        $this->fillNodeRow();
        if (trim($this->dataBranch) != '') {
            $tbl = $this->dataBranch;
            $fld = str_replace($tbl.':', '', $this->dataStore);
        } else {
            list($tbl, $fld) = $this->splitTblFld($this->dataStore);
        }
        $newVal = (intVal($this->responseSet) > 0) ? intVal($this->responseSet) : trim($this->defaultVal);
        return [$tbl, $fld, $newVal];
    }
    
    public function printManipUpdate()
    {
        if (!$this->isDataManip() || $this->nodeType == 'Data Manip: Wrap') return '';
        $manipUpdate = $this->getManipUpdate();
        if (trim($manipUpdate[0]) == '' || $manipUpdate[1] == '') return '';
        $ret = ' , ' . $manipUpdate[1] . ' = ';
        if (isset($this->responseSet) && intVal($this->responseSet) > 0) {
            $ret .= $GLOBALS["SL"]->getDefValById(intVal($this->responseSet));
        } else {
            $ret .= $manipUpdate[2];
        }
        if (sizeof($this->dataManips) > 0) {
            foreach ($this->dataManips as $manip) {
                $tmpNode = new SurvLoopNode($manip->nodeID, $manip);
                $ret .= $tmpNode->printManipUpdate();
            }
        }
        return $ret;
    }
    
    public function loadPageBlockColors()
    {
        if (isset($this->nodeRow->NodeDefault) && trim($this->nodeRow->NodeDefault) != '') {
            $colors = explode(';;', $this->nodeRow->NodeDefault);
            if (isset($colors[0])) $this->colors["blockBG"]   = $colors[0];
            if (isset($colors[1])) $this->colors["blockText"] = $colors[1];
            if (isset($colors[2])) $this->colors["blockLink"] = $colors[2];
        }
        return true;
    }
    
}
