<?php
/**
  * TreeNodeCore - The core class for a single node in a Survloop tree.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use DB;
use App\Models\SLNode;

class TreeNodeCore
{
    public $nodeID        = 1;
    public $parentID      = 1;
    public $parentOrd     = 0;
    
    public $nodeOpts      = 1;
    public $nodeType      = '';
    public $dataBranch    = '';
    public $dataStore     = '';
    public $responseSet   = '';
    public $defaultVal    = '';
    
    public $nodeRow       = NULL;
    public $nodeRowFilled = false;
    public $nodeTierPath  = [];
    
    function __construct($nID = -3, $nRow = [], $nCache = [])
    {
        $this->nodeID = $nID;
        if (sizeof($nCache) > 0) {
            return $this->loadNodeCache($nID, $nCache);
        }
        $this->loadNodeRow($nID, $nRow);
        return true;
    }
    
    public function loadNodeCache($nID = -3, $nCache = [])
    {
        if (sizeof($nCache) > 0) {
            if (isset($nCache["pID"])) {
                $this->parentID  = $nCache["pID"];
            }
            if (isset($nCache["pOrd"])) {
                $this->parentOrd = $nCache["pOrd"];
            }
            if (isset($nCache["opts"])) {
                $this->nodeOpts  = $nCache["opts"];
            }
            if (isset($nCache["type"])) {
                $this->nodeType  = $nCache["type"];
            }
        }
        return true;
    }
    
    public function loadNodeRow($nID = -3, $nRow = NULL)
    {
        $this->nodeRow = null;
        if ($nRow) {
            $this->nodeRow = $nRow;
        } elseif ($nID > 0) {
            $this->nodeRow = SLNode::find($nID)
                ->select('node_id', 'node_parent_id', 'node_parent_order', 'node_opts', 'node_type');
        } elseif ($this->nodeID > 0) {
            $this->nodeRow = SLNode::find($this->nodeID)
                ->select('node_id', 'node_parent_id', 'node_parent_order', 'node_opts', 'node_type');
        }
        if (!$this->nodeRow) {
            $this->nodeRow = new SLNode;
            return false;
        }
        $this->chkNodeInvalidFields();
        $this->parentID  = $this->nodeRow->node_parent_id;
        $this->parentOrd = $this->nodeRow->node_parent_order;
        $this->nodeOpts  = $this->nodeRow->node_opts;
        $this->nodeType  = $this->nodeRow->node_type;
        //$this->fillNodeRow();
        return true;
    }
    
    public function chkNodeInvalidFields()
    {
        if ($this->nodeRow && isset($this->nodeRow->node_id)) {
            if (!isset($this->nodeRow->node_parent_order) 
                || intVal($this->nodeRow->node_parent_order) < 0) {
                $this->nodeRow->node_parent_order = 0;
                $this->nodeRow->save();
            }
            if (!isset($this->nodeRow->node_opts) 
                || intVal($this->nodeRow->node_opts) <= 0) {
                $this->nodeRow->node_opts = 1;
                $this->nodeRow->save();
            }
        }
        return true;
    }
    
    public function listCore()
    {
        return [
            "parentID"  => $this->parentID, 
            "parentOrd" => $this->parentOrd, 
            "nodeOpts"  => $this->nodeOpts, 
            "nodeType"  => $this->nodeType 
        ];
    }
    
    public function fillNodeRow($nID = -3, $nRow = NULL)
    {
        if ($nID <= 0 && $this->nodeID > 0) {
            $nID = $this->nodeID;
        }
        if (!$this->nodeRowFilled) {
            if ($nRow) {
                $this->nodeRow = $nRow;
            } else {
                $this->nodeRow = SLNode::find($nID);
            }
            $this->chkNodeInvalidFields();
            $this->initiateNodeRow();
            $this->nodeRowFilled = true;
        }
        return true;
    }
    
    public function initiateNodeRow()
    {
        return true;
    }
    
    /**
     * Returns the Node ID, in the simplest case. 
     * @return array Results
     */
    public function nodePreview()
    {
        return number_format($this->nodeID);
    }
    
    public function tierPathStr($tierPath = [])
    {
        if (sizeof($tierPath) == 0) {
            return implode('-', $this->nodeTierPath).'-';
        }
        return implode('-', $tierPath).'-';
    }
    
    public function checkBranch($tierPath = [])
    {
        $tierPathStr = $this->tierPathStr($tierPath);
        if ($tierPathStr != '') {
            return (strpos($this->tierPathStr($this->nodeTierPath), $tierPathStr) === 0);
        }
        return 0;
    }
    
    public function getParent()
    {
        return intVal($this->parentID);
    }
    
}
