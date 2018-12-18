<?php
/**
  * TreeNodeCore - The core class for a single node in a SurvLoop tree.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

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
    
    // Tree Nodes are assigned an optional property when ( SLNode->NodeOpts%OPT_PRIME == 0 )
    // Node Options
    public const OPT_DROPTAGGER = 53; // This node's dropdown stores like a checkbox, associating tags

    // Node Visual Layout Options
    public const OPT_SKINNY     = 67; // This node's contents are wrapped in the skinny page width 
    public const OPT_JUMBOTRON  = 37; // Wrap the contents of this node inside bootstrap's Jumbotron
    public const OPT_BLOCKBACKG = 71; // Node has content block background and color properties
    
    // Node Form Field Layout Options
    public const OPT_CUSTOMLAY  = 2;  // Node uses some layout overrides instead of default
    public const OPT_REQUIRELIN = 13; // "*Required" must appear on its own line
    public const OPT_RESPOCOLS  = 61; // Node responses layed out in columns
    
    // Node Form Field Saving Options
    public const OPT_TBLSAVEROW = 73; // Table leaves existing rows' records upon saving (don't delete empties)
    
    // Node Interaction Options
    public const OPT_WORDCOUNT  = 31; // Open ended field should show a live word count
    public const OPT_WORDLIMIT  = 47; // Force limit on word count
    public const OPT_ECHOSTROKE = 41; // Echo response edits to specific div, every keystroke
    public const OPT_BTNTOGGLE  = 43; // Toggle child nodes if node button is clicked
    public const OPT_HIDESELECT = 79; // Hide unselected options after radio button selected
    public const OPT_REVEALINFO = 83; // Reveal node sub-notes upon clicking a little info icon
    
    // Page Node Options
    public const OPT_EXITPAGE   = 29; // Node is an Exit Page, without a next button 
    public const OPT_HIDEPROG   = 59; // Hide progress bar on this page
    
    // For XML Tree Nodes
    public const OPT_XMLPARENTS = 5;  // Include members with parent, without table wrap
    public const OPT_XMLMIN     = 7;  // Min 1 Record
    public const OPT_XMLMAX     = 11; // Max 1 Record
    
    public function getPrimeConst($type)
    {
        eval("return self::OPT_" . $type . ";");
    }
    
    public function chkOpt($nodeOpts = 1, $type = '')
    {
        if ($type == '' || $nodeOpts == 0) {
            return false;
        }
        $prime = $this->getPrimeConst($type);
        return (intVal($prime) != 0 && $nodeOpts%$prime == 0);
    }
    
    public function chkCurrOpt($type = '')
    {
        if (!isset($this->nodeOpts) || intVal($this->nodeOpts) == 0) {
            return false;
        }
        return $this->chkOpt($this->nodeOpts, $type);
    }
    
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
            if (isset($nCache["pID"]))  $this->parentID  = $nCache["pID"];
            if (isset($nCache["pOrd"])) $this->parentOrd = $nCache["pOrd"];
            if (isset($nCache["opts"])) $this->nodeOpts  = $nCache["opts"];
            if (isset($nCache["type"])) $this->nodeType  = $nCache["type"];
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
                ->select('NodeID', 'NodeParentID', 'NodeParentOrder', 'NodeOpts', 'NodeType');
        } elseif ($this->nodeID > 0) {
            $this->nodeRow = SLNode::find($this->nodeID)
                ->select('NodeID', 'NodeParentID', 'NodeParentOrder', 'NodeOpts', 'NodeType');
        }
        if (!$this->nodeRow) {
            $this->nodeRow = new SLNode;
        }
        $this->parentID  = $this->nodeRow->NodeParentID;
        $this->parentOrd = $this->nodeRow->NodeParentOrder;
        $this->nodeOpts  = $this->nodeRow->NodeOpts;
        $this->nodeType  = $this->nodeRow->NodeType;
        if (!isset($this->nodeRow)) {
            $this->nodeRow = new SLNode;
            return false;
        }
        //$this->fillNodeRow();
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
            $this->initiateNodeRow();
            $this->nodeRowFilled = true;
        }
        return true;
    }
    
    public function initiateNodeRow() { return true; }
    
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
        if (sizeof($tierPath) == 0) return implode('-', $this->nodeTierPath).'-';
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
