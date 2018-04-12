<?php
namespace SurvLoop\Controllers;

use Cache;
use Illuminate\Http\Request;

use App\Models\SLNode;

class SurvLoopTreeXML extends CoreTree
{
    
    public $classExtension = 'SurvLoopTreeXML';
    
    protected function initExtra(Request $request)
    {
        if ((!$this->rootID || intVal($this->rootID) <= 0) && intVal($GLOBALS["SL"]->treeRow->TreeCoreTable) > 0) {
            $newRoot = new SLNode;
            $newRoot->NodeTree        = $this->treeID;
            $newRoot->NodePromptNotes = $GLOBALS["SL"]->treeRow->TreeCoreTable;
            $newRoot->NodePromptText  = $GLOBALS["SL"]->coreTbl;
            $newRoot->save();
        }
        $this->canEditTree = true;
        return true;
    }
    
    public function adminNodeEditXML(Request $request, $nodeIN) 
    {
        $this->initExtra($request);
        $node = array();
        if ($nodeIN > 0) {
            if (sizeof($this->allNodes) > 0 && isset($this->allNodes[$nodeIN])) {
                $node = $this->allNodes[$nodeIN];
            } else {
                $node = $this->loadNode(SLNode::find($nodeIN));
            }
            $node->fillNodeRow($nodeIN);
        }
        if ($nodeIN <= 0 || !$node || sizeof($node) == 0) {
            $node = $this->loadNode();
            $node->nodeRow->NodeParentID    = $GLOBALS["SL"]->REQ->nodeParentID;
            $node->nodeRow->NodeParentOrder = 0;
            $node->nodeRow->NodeOpts        = 1;
            $node->nodeRow->NodeType        = 'XML';
        }
        
        if ($GLOBALS["SL"]->REQ->has('sub')) {
            if ($GLOBALS["SL"]->REQ->has('deleteNode') && intVal($GLOBALS["SL"]->REQ->input('deleteNode')) == 1) {
                $this->treeAdminNodeDelete($node->nodeRow->NodeID);
            } else {
                if ($nodeIN <= 0) $node = $this->treeAdminNodeNew($node);
                if (intVal($node->nodeRow->NodeOpts) < 1) $node->nodeRow->NodeOpts = 1;
                if ($GLOBALS["SL"]->REQ->xmlNodeType == 'dataWrap') {
                    $node->nodeRow->NodePromptText  = trim($GLOBALS["SL"]->REQ->wrapPromptText);
                    $node->nodeRow->NodePromptNotes = 0;
                } else {
                    $opts = array(5, 7, 11);
                    foreach ($opts as $o) {
                        if ($GLOBALS["SL"]->REQ->has('opts'.$o.'') 
                            && intVal($GLOBALS["SL"]->REQ->input('opts'.$o.'')) == $o) {
                            if ($node->nodeRow->NodeOpts%$o > 0) {
                                $node->nodeRow->NodeOpts *= $o;
                            }
                        } elseif ($node->nodeRow->NodeOpts%$o == 0) {
                            $node->nodeRow->NodeOpts = $node->nodeRow->NodeOpts/$o;
                        }
                    }
                    $node->nodeRow->NodePromptText  = trim($GLOBALS["SL"]->REQ->input('nodePromptText'));
                    $node->nodeRow->NodePromptNotes = $GLOBALS["SL"]->tblI[$node->nodeRow->NodePromptText];
                }
                $node->nodeRow->save();
            }
            $redir = '/dashboard/surv-' . $GLOBALS["SL"]->treeID . '/xmlmap?all=1&refresh=1#n' . $node->nodeRow->NodeID;
            echo '<script type="text/javascript"> setTimeout("window.location=\'' . $redir . '\'", 5); </script>';
            exit;
        }
        $GLOBALS["SL"]->pageAJAX .= '$(".xmlDataChng").click(function(){ 
            if (document.getElementById("xmlNodeTypeTbl").checked) {
                $("#xmlDataTbl").slideDown("fast"); $("#xmlDataWrap").slideUp("fast"); 
            } else {
                $("#xmlDataTbl").slideUp("fast"); $("#xmlDataWrap").slideDown("fast"); 
            }
        });';
        return view('vendor.survloop.admin.tree.node-edit-xmlmap', [
            "canEditTree" => $this->canEditTree, 
            "treeID"      => $this->treeID, 
            "node"        => $node, 
            "REQ"         => $this->REQ
        ]);
    }
    
    protected function adminBasicPrintNode($tierNode = array(), $tierDepth = 0)
    {
        $tierDepth++;
        if (sizeof($tierNode) > 0 && $tierNode[0] > 0) {
            if ($this->hasNode($tierNode[0])) {
                $this->allNodes[$tierNode[0]]->fillNodeRow();
                $childrenPrints = '';
                if (sizeof($tierNode[1]) > 0) {
                    foreach ($tierNode[1] as $next) {
                        $childrenPrints .= $this->adminBasicPrintNode($next, $tierDepth);
                    }
                }
                return view('vendor.survloop.admin.tree.node-print-core', [
                    "canEditTree"    => $this->canEditTree, 
                    "REQ"            => $this->REQ, 
                    "rootID"         => $this->rootID, 
                    "nID"            => $tierNode[0], 
                    "node"           => $this->allNodes[$tierNode[0]], 
                    "tierNode"       => $tierNode, 
                    "tierDepth"      => $tierDepth, 
                    "childrenPrints" => $childrenPrints
                ])->render();
            }
        }
        return '';
    }
    
    public function adminPrintFullTree(Request $request)
    {
        $this->loadTree();
        $this->initExtra($request);
        $this->treeAdminNodeManip();
        $GLOBALS["SL"]->pageAJAX .= view('vendor.survloop.admin.tree.node-print-wrap-ajax', [
            "canEditTree"     => $this->canEditTree
        ])->render();
        return view('vendor.survloop.admin.tree.node-print-wrap', [
            "adminBasicPrint" => $this->adminBasicPrintNode($this->nodeTiers, -1), 
            "canEditTree"     => $this->canEditTree
        ])->render();
    }
    
    public function getNodeTblName($nID)
    {
        if (isset($this->allNodes[$nID]) && isset($this->allNodes[$nID]->nodeRow->NodePromptText)) {
            return trim($this->allNodes[$nID]->nodeRow->NodePromptText);
        }
        return '';
    }
    
    public function getNodeTblID($nID)
    {
        if (isset($this->allNodes[$nID]) && isset($this->allNodes[$nID]->nodeRow->NodePromptText)) {
            return intVal($this->allNodes[$nID]->nodeRow->NodePromptNotes);
        }
        return -3;
    }
    
    
    
    
}
