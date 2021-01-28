<?php
/**
  * TreeSurvAPI extends a standard branching tree, for maps of API exports.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\SLNode;
use App\Models\SLFields;
use App\Models\SLLogActions;
use App\Models\SLSearchRecDump;
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use RockHopSoft\Survloop\Controllers\Tree\TreeCoreSess;

class TreeSurvAPI extends TreeCoreSess
{
    protected $canEditTree = false;
    
    protected function initExtra(Request $request)
    {
        if (!isset($this->v["uID"])) {
            $this->loadUserVars();
        }
        foreach ($this->allNodes as $nID => $nodeObj) {
            $this->allNodes[$nID]->fillNodeRow();
            if ($this->allNodes[$nID]->nodeRow->node_parent_id == -3
                && (!$this->rootID || intVal($this->rootID) <= 0)) {
                $this->rootID = $this->allNodes[$nID]->nodeRow->node_parent_id;
            }
        }
        $this->checkTreeRoot();
        $this->canEditTree = ($this->v["uID"] > 0 && $this->v["user"]->hasRole('administrator|databaser'));
        return true;
    }
    
    public function checkTreeRoot()
    {
        if ((!$this->rootID || intVal($this->rootID) <= 0) 
            && isset($GLOBALS["SL"]->treeRow->tree_core_table)
            && intVal($GLOBALS["SL"]->treeRow->tree_core_table) > 0) {
            $chk = SLNode::where('node_tree', $this->treeID)
                ->where('node_parent_id', -3)
                ->first();
            if (!$chk || !isset($chk->node_id)) {
                $newRoot = new SLNode;
                $newRoot->node_tree         = $this->treeID;
                $newRoot->node_parent_id    = -3;
                $newRoot->node_type         = 'Page';
                $newRoot->node_prompt_notes = $GLOBALS["SL"]->treeRow->tree_core_table;
                $newRoot->node_prompt_text  = $GLOBALS["SL"]->coreTbl;
                $newRoot->save();
            }
        }
        return true;
    }
    
    public function adminNodeEditXML(Request $request, $nodeIN) 
    {
        $this->initExtra($request);
        $node = NULL;
        if ($nodeIN > 0) {
            if (sizeof($this->allNodes) > 0 && isset($this->allNodes[$nodeIN])) {
                $node = $this->allNodes[$nodeIN];
            } else {
                $node = $this->loadNode(SLNode::find($nodeIN));
            }
            $node->fillNodeRow($nodeIN);
        }
        if ($nodeIN <= 0 || !$node) {
            $node = $this->loadNode();
            $node->nodeRow->node_parent_id    = $GLOBALS["SL"]->REQ->nodeParentID;
            $node->nodeRow->node_parent_order = 0;
            $node->nodeRow->node_opts         = 1;
            $node->nodeRow->node_type         = 'XML';
        }
        
        if ($GLOBALS["SL"]->REQ->has('sub')) {
            if ($GLOBALS["SL"]->REQ->has('deleteNode') 
                && intVal($GLOBALS["SL"]->REQ->input('deleteNode')) == 1) {
                $this->treeAdminNodeDelete($node->nodeRow->node_id);
            } else {
                if ($nodeIN <= 0) {
                    $node = $this->treeAdminNodeNew($node);
                }
                if (intVal($node->nodeRow->node_opts) < 1) {
                    $node->nodeRow->node_opts = 1;
                }
                if ($GLOBALS["SL"]->REQ->xmlNodeType == 'dataWrap') {
                    $node->nodeRow->node_prompt_text  = trim($GLOBALS["SL"]->REQ->wrapPromptText);
                    $node->nodeRow->node_prompt_notes = 0;
                } else {
                    $opts = [ 5, 7, 11 ];
                    foreach ($opts as $o) {
                        if ($GLOBALS["SL"]->REQ->has('opts'.$o.'') 
                            && intVal($GLOBALS["SL"]->REQ->input('opts'.$o.'')) == $o) {
                            if ($node->nodeRow->node_opts%$o > 0) {
                                $node->nodeRow->node_opts *= $o;
                            }
                        } elseif ($node->nodeRow->node_opts%$o == 0) {
                            $node->nodeRow->node_opts = $node->nodeRow->node_opts/$o;
                        }
                    }
                    $tmp = $GLOBALS["SL"]->REQ->input('nodePromptText');
                    $node->nodeRow->node_prompt_text  = stripslashes(trim($tmp));
                    $tmp = $GLOBALS["SL"]->tblI[$node->nodeRow->node_prompt_text];
                    $node->nodeRow->node_prompt_notes = stripslashes($tmp);
                }
                $node->nodeRow->save();
            }
            $redir = '/dashboard/surv-' . $GLOBALS["SL"]->treeID 
                . '/xmlmap?all=1&refresh=1#n' . $node->nodeRow->node_id;
            echo '<script type="text/javascript"> setTimeout("window.location=\'' 
                . $redir . '\'", 5); </script>';
            exit;
        }
        $GLOBALS["SL"]->pageAJAX .= '$(".xmlDataChng").click(function(){ 
            if (document.getElementById("xmlNodeTypeTbl").checked) {
                $("#xmlDataTbl").slideDown("fast"); $("#xmlDataWrap").slideUp("fast"); 
            } else {
                $("#xmlDataTbl").slideUp("fast"); $("#xmlDataWrap").slideDown("fast"); 
            }
        });';
        return view(
            'vendor.survloop.admin.tree.node-edit-xmlmap', 
            [
                "canEditTree" => $this->canEditTree, 
                "treeID"      => $this->treeID, 
                "node"        => $node, 
                "REQ"         => $GLOBALS["SL"]->REQ
            ]
        );
    }
    
    protected function adminBasicPrintNode($tierNode = [], $tierDepth = 0)
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
                return view(
                    'vendor.survloop.admin.tree.node-print-core', 
                    [
                        "canEditTree"    => $this->canEditTree, 
                        "REQ"            => $GLOBALS["SL"]->REQ, 
                        "rootID"         => $this->rootID, 
                        "nID"            => $tierNode[0], 
                        "node"           => $this->allNodes[$tierNode[0]], 
                        "tierNode"       => $tierNode, 
                        "tierDepth"      => $tierDepth, 
                        "childrenPrints" => $childrenPrints
                    ]
                )->render();
            }
        }
        return '';
    }
    
    public function adminPrintFullTree(Request $request)
    {
        $this->loadTree();
        $this->initExtra($request);
        $this->treeAdminNodeManip();
        $GLOBALS["SL"]->pageAJAX .= view(
            'vendor.survloop.admin.tree.node-print-wrap-ajax', 
            [ "canEditTree" => $this->canEditTree ]
        )->render();
        return view(
            'vendor.survloop.admin.tree.node-print-wrap', 
            [
                "adminBasicPrint" => $this->adminBasicPrintNode($this->nodeTiers, -1), 
                "canEditTree"     => $this->canEditTree
            ]
        )->render();
    }
    
    public function getNodeTblName($nID)
    {
        if (isset($this->allNodes[$nID]) 
            && isset($this->allNodes[$nID]->nodeRow->node_prompt_text)) {
            return trim($this->allNodes[$nID]->nodeRow->node_prompt_text);
        }
        return '';
    }
    
    public function getNodeTblID($nID)
    {
        if (isset($this->allNodes[$nID]) 
            && isset($this->allNodes[$nID]->nodeRow->node_prompt_text)) {
            return intVal($this->allNodes[$nID]->nodeRow->node_prompt_notes);
        }
        return -3;
    }
    
    protected function maxUserView()
    {
        return true;
    }
    
    public function loadXmlMapTree(Request $request, $forceReload = false)
    {
        $this->survloopInit($request);
        if (empty($this->xmlMapTree) || $forceReload) {
            if (isset($GLOBALS["SL"]->xmlTree["id"])) {
                $this->xmlMapTree = new TreeSurvAPI;
                $this->xmlMapTree->loadTree($GLOBALS["SL"]->xmlTree["id"], $request, true);
            } elseif ($GLOBALS["SL"]->treeRow->tree_type == 'Survey XML') {
                $this->xmlMapTree = new TreeSurvAPI;
                $this->xmlMapTree->loadTree($GLOBALS["SL"]->treeID, $request, true);
                $this->copyXmlTreeCore();
            }
        }
        return true;
    }
    
    private function copyXmlTreeCore()
    {
        $t = $GLOBALS["SL"]->treeRow;
        $GLOBALS["SL"]->xmlTree["id"] = $GLOBALS["SL"]->treeID;
        $GLOBALS["SL"]->xmlTree["root"] = $t->tree_root;
        $GLOBALS["SL"]->xmlTree["coreTblID"] = $t->tree_core_table;
        $GLOBALS["SL"]->xmlTree["coreTbl"] = $GLOBALS["SL"]->tbl[$t->tree_core_table];
        $GLOBALS["SL"]->xmlTree["slug"] = $t->tree_slug;
        $GLOBALS["SL"]->xmlTree["opts"] = intVal($t->tree_opts);
        return true;
    }
    
    protected function getXmlTmpV($nID, $tblID = -3)
    {
        $v = [];
        $v["tblID"] = 0;
        $v["tblAbbr"] = '';
        if ($tblID > 0) {
            $v["tbl"] = $GLOBALS["SL"]->tbl[$tblID];
            $v["tblID"] = $tblID;
//echo 'getXmlTmpV(' . $nID . ', ' . $tblID . '  --- A --- ' . $v["tbl"] . '<br />'; exit;
        } else {
            $v["tbl"] = $this->xmlMapTree->getNodeTblName($nID);
            if (isset($GLOBALS["SL"]->tblI[$v["tbl"]])) {
                $v["tblID"] = $GLOBALS["SL"]->tblI[$v["tbl"]];
            }
//echo 'getXmlTmpV(' . $nID . ', ' . $v["tblID"] . '  --- B --- ' . $v["tbl"] . '<br />'; exit;
        }
        if (isset($GLOBALS["SL"]->tblAbbr[$v["tbl"]])) {
            $v["tblAbbr"] = $GLOBALS["SL"]->tblAbbr[$v["tbl"]];
        }
        $v["tblAbbrTrim"] = $v["tblAbbr"];
        if (substr($v["tblAbbr"], strlen($v["tblAbbr"])-1) == '_') {
            $v["tblAbbrTrim"] = substr($v["tblAbbr"], 0, strlen($v["tblAbbr"])-1);
        }
//echo 'abbr: ' . $v["tblAbbr"] . ' ---' . substr($v["tblAbbr"], strlen($v["tblAbbr"])-1) . '--- ' . $v["tblAbbrTrim"] . '<br />'; exit;
        $v["tblOpts"] = 1;
        if ($nID > 0 && isset($this->xmlMapTree->allNodes[$nID])) {
            $v["tblOpts"] = $this->xmlMapTree->allNodes[$nID]->nodeOpts;
        }
        $v["tblFlds"] = SLFields::select()
            ->where('fld_database', $this->dbID)
            ->where('fld_table', '=', $v["tblID"])
            ->orderBy('fld_ord', 'asc')
            ->orderBy('fld_eng', 'asc')
            ->get();
        $v["tblFldEnum"] = $v["tblFldDefs"] = [];
        if ($v["tblFlds"]->isNotEmpty()) {
            foreach ($v["tblFlds"] as $i => $fld) {
                $v["tblFldDefs"][$fld->fld_id] = [];
                if (strpos($fld->fld_values, 'Def::') !== false) {
                    $set = str_replace('Def::', '', $fld->fld_values);
                    $set = $GLOBALS["SL"]->def->getSet($set);
                    if (sizeof($set) > 0) {
                        foreach ($set as $def) {
                            $v["tblFldDefs"][$fld->fld_id][] = $def->def_value;
                        }
                    }
                } elseif (trim($fld->fld_values) != '' 
                    && strpos($fld->fld_values, ';') !== false) {
                    $v["tblFldDefs"][$fld->fld_id] = explode(';', $fld->fld_values);
                }
                $v["tblFldEnum"][$fld->fld_id] = (sizeof($v["tblFldDefs"][$fld->fld_id]) > 0);
            }
        }
        $v["tblHelp"] = $v["tblHelpFld"] = [];
        if ($v["tblID"] > 0 && sizeof($GLOBALS["SL"]->dataHelpers) > 0) {
            foreach ($GLOBALS["SL"]->dataHelpers as $helper) {
                $hlpTbl = $GLOBALS["SL"]->tblI[$helper->data_help_table];
                if ($v["tbl"] == $helper->data_help_parent_table 
                    && $helper->DataHelpValueField
                    && !in_array($hlpTbl, $v["tblHelp"])) {
                    $abbrLen = strlen($GLOBALS["SL"]->tblAbbr[$helper->data_help_table]);
                    $hlpFld = substr($helper->data_help_value_field, $abbrLen);
                    $v["tblHelp"][] = $hlpTbl;
                    $v["tblHelpFld"][$hlpTbl] = SLFields::where('fld_table', $hlpTbl)
                        ->where('fld_name', $hlpFld)
                        ->first();
                }
            }
        }
        return $v;
    }

    protected function xmlAccess()
    {
        return true;
    }
    
    public function genXmlSchema(Request $request)
    {
        $this->loadXmlMapTree($request);
        if (!isset($GLOBALS["SL"]->xmlTree["coreTbl"])) {
            return $this->redir('/');
        }
        if (!$this->xmlAccess()) {
            return 'Sorry, access not permitted.';
        }
        $this->v["nestedNodes"] = $this->genXmlSchemaNode(
            $this->xmlMapTree->rootID, 
            $this->xmlMapTree->nodeTiers
        );
        $view = view('vendor.survloop.admin.tree.xml-schema', $this->v)
            ->render();
        return Response::make($view, '200')
            ->header('Content-Type', 'text/xml');
    }
    
    public function genXmlSchemaNode($nID, $nodeTiers, $overV = [])
    {
        $v = [];
        if (sizeof($overV) > 0) {
            $v = $overV;
        } else {
            $v = $this->getXmlTmpV($nID);
        }
        $v["kids"] = '';
        if ($v["tblHelp"] && sizeof($v["tblHelp"]) > 0) {
            foreach ($v["tblHelp"] as $help) {
                $nextV = $this->getXmlTmpV(-3, $help);
                if (isset($v["tblHelpFld"][$help]->fld_name)) {
                    $v["kids"] .= '<xs:element name="' 
                        . $nextV["tbl"] . '">
                        <xs:complexType mixed="true"><xs:sequence>
                            <xs:element name="' 
                            . $v["tblHelpFld"][$help]->fld_name . '" />
                        </xs:sequence></xs:complexType>
                    </xs:element>' . "\n";
                }
            }
        }
        for ($i = 0; $i < sizeof($nodeTiers[1]); $i++) {
            $v["kids"] .= $this->genXmlSchemaNode(
                $nodeTiers[1][$i][0], 
                $nodeTiers[1][$i]
            );
        }
        return view(
            'vendor.survloop.admin.tree.xml-schema-node', 
            $v
        )->render();
    }
    
    public function genXmlReport(Request $request)
    {
        if (!isset($GLOBALS["SL"]->xmlTree["coreTbl"])) {
            return $this->redir('/xml-schema');
        }
        if (!isset($this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]]) 
            || empty($this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]])) {
            return $this->redir('/xml-schema');
        }
        $this->v["nestedNodes"] = $this->genXmlReportNode(
            $this->xmlMapTree->rootID, 
            $this->xmlMapTree->nodeTiers, 
            $this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]][0]
        );
        if (trim($this->v["nestedNodes"]) == '') {
            return $this->redir('/xml-schema');
        }
        $view = view('vendor.survloop.admin.tree.xml-report', $this->v)->render();
        return Response::make($view, '200')->header('Content-Type', 'text/xml');
    }
    
    public function genXmlReportNode($nID, $nodeTiers, $rec, $overV = [])
    {
        $v = [];
        if (sizeof($overV) > 0) {
            $v = $overV;
        } else {
            $v = $this->getXmlTmpV($nID);
        }
        $v["tot"]     = 1;
        $v["rec"]     = $rec;
        $v["recFlds"] = [];
        if (sizeof($v["tblFlds"]) > 0) {
            foreach ($v["tblFlds"] as $i => $fld) {
                //if (!$this->checkValEmpty($fld->fld_type, 
                //    $rec->{ $v["tblAbbr"] . $fld->fld_name })) {
                $v["recFlds"][$fld->fld_id] = $this->genXmlFormatVal(
                    $rec, 
                    $fld, 
                    $v["tblAbbr"]
                );
                //}
            }
        }
        $v["kids"] = '';
        if (is_array($v["tblHelp"]) && sizeof($v["tblHelp"]) > 0) {
            foreach ($v["tblHelp"] as $help) {
                $this->genXmlReportNodeAddKidTbls($v, $help);
            }
        }
        for ($i = 0; $i < sizeof($nodeTiers[1]); $i++) {
            $tbl2 = $this->xmlMapTree->getNodeTblName($nodeTiers[1][$i][0]);
            $kidRows = $this->sessData->getChildRows(
                $v["tbl"], 
                $rec->getKey(), 
                $tbl2
            );
            if ($kidRows && sizeof($kidRows) > 0) {
                $nextV = $this->getXmlTmpV($nodeTiers[1][$i][0]);
                if (intVal($nextV["tblID"]) > 0 && $nextV["tblOpts"]%5 > 0) {
                    $v["kids"] .= '<' . $nextV["tbl"] . '>' . "\n";
                }
                foreach ($kidRows as $j => $kid) {
                    $v["kids"] .= $this->genXmlReportNode(
                        $nodeTiers[1][$i][0], 
                        $nodeTiers[1][$i], 
                        $kid
                    );
                }
                if (intVal($nextV["tblID"]) > 0 && $nextV["tblOpts"]%5 > 0) {
                    $v["kids"] .= '</' . $nextV["tbl"] . '>' . "\n";
                }
            }
        }
        return view('vendor.survloop.admin.tree.xml-report-node', $v)->render();
    }
    
    protected function genXmlReportNodeAddKidTbls(&$v, $help)
    {
        $nextV = $this->getXmlTmpV(-3, $help);
        $kidRows = $this->sessData->getChildRows(
            $v["tbl"], 
            $rec->getKey(), 
            $nextV["tbl"]
        );
        if ($kidRows && sizeof($kidRows) > 0) {
            if (intVal($nextV["tblID"]) > 0 
                && $nextV["tblOpts"]%5 > 0) {
                $v["kids"] .= '<' . $nextV["tbl"] . '>' . "\n";
            }
            foreach ($kidRows as $j => $kid) {
                if (isset($v["tblHelpFld"][$help]->fld_name)) {
                    //if (!$this->checkValEmpty($kid, 
                    //    $rec->{ $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->tbl[$help]] 
                    //        . $v["tblHelpFld"][$help] })) {
                    $val = $this->genXmlFormatVal(
                        $kid, 
                        $v["tblHelpFld"][$help], 
                        $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->tbl[$help]]
                    );
                    $v["kids"] .= '<' . $v["tblHelpFld"][$help]->fld_name 
                        . '>' . $val . '</' 
                        . $v["tblHelpFld"][$help]->fld_name . '>' . "\n";
                    //}
                }
            }
            if (intVal($nextV["tblID"]) > 0 && $nextV["tblOpts"]%5 > 0) {
                $v["kids"] .= '</' . $nextV["tbl"] . '>' . "\n";
            }
        }
        return true;
    }
    
    // FldOpts %1 XML Public Data; %7 XML Private Data; %11 XML Sensitive Data; %13 XML Internal Use Data
    public function checkViewDataPerms($fld)
    {
        if ($fld 
            && isset($fld->fld_opts) 
            && intVal($fld->fld_opts) > 0) {
            if ($fld->fld_opts%7 > 0 
                && $fld->fld_opts%11 > 0 
                && $fld->fld_opts%13 > 0) {
                return true;
            }
            if (in_array($GLOBALS["SL"]->pageView, ['full', 'full-pdf', 'full-xml'])) {
                return true;
            }
            if ($fld->fld_opts%7 == 0
                || $fld->fld_opts%11 == 0
                || $fld->fld_opts%13 == 0) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    public function checkFldDataPerms($fld)
    {
        if ($fld && isset($fld->fld_opts) && intVal($fld->fld_opts) > 0) {
            if ($fld->fld_opts%7 > 0 
                && $fld->fld_opts%11 > 0 
                && $fld->fld_opts%13 > 0) {
                return true;
            }
            if ($GLOBALS["SL"]->dataPerms == 'internal') {
                return true;
            } elseif ($fld->fld_opts%13 == 0) {
                return false;
            }
            if ($fld->fld_opts%11 == 0) {
                return ($GLOBALS["SL"]->dataPerms == 'sensitive');
            }
            if ($fld->fld_opts%7 == 0) {
                return in_array(
                    $GLOBALS["SL"]->dataPerms, 
                    [ 'private', 'sensitive' ]
                );
            }
        }
        return false;
    }
    
    protected function genXmlFormatValCustomPerms($rec, $fld, $abbr)
    {
        return false;
    }
    
    public function genXmlFormatVal($rec, $fld, $abbr)
    {
        $val = false;
//if ($fld->fld_name == 'summary') { echo 'genXmlFormatVal(abbr: ' . $abbr . ',<br />fld: ' . $fld->fld_name . ' ' . $fld->fld_opts . ', dataPerms: ' . $GLOBALS["SL"]->dataPerms . ', pageView: ' . $GLOBALS["SL"]->pageView . ' <br />genXmlFormatValCustomPerms: ' . (($this->genXmlFormatValCustomPerms($rec, $fld, $abbr)) ? 't' : 'f') . ' — checkFldDataPerms: ' . (($this->checkFldDataPerms($fld)) ? 't' : 'f') . ' — checkViewDataPerms: ' . (($this->checkViewDataPerms($fld)) ? 't' : 'f') . ' — rec:<pre>'; print_r($rec); echo '</pre>'; exit; }
//if ($apiFld->fld->fld_name == 'summary') { echo 'fld:table: ' . $apiFld->fld->fld_table . ', fldTbl: ' . $fldTbl . ', fldTblAbbr: ' . $fldTblAbbr . ', fldName: ' . $fldName . ', val: ' . $val . ', id: ' . $id . ',<pre>'; print_r($this->sessData->getChildRows($tbl, $id, $fldTbl)); print_r($rec); echo '</pre>'; }
        if (isset($rec->{ $abbr . $fld->fld_name })
            && ($this->genXmlFormatValCustomPerms($rec, $fld, $abbr)
                || ($this->checkFldDataPerms($fld) 
                    && $this->checkViewDataPerms($fld)))) {
            $val = $rec->{ $abbr . $fld->fld_name };
            if (strpos($fld->fld_values, 'Def::') !== false) {
                if (intVal($val) > 0) {
                    $val = $GLOBALS["SL"]->def->getVal(
                        str_replace('Def::', '', $fld->fld_values), 
                        $val
                    );
                } else {
                    $val = false;
                }
            } else { // not pulling values from a definition set
                if (in_array($fld->fld_type, array('INT', 'DOUBLE'))) {
                    if (intVal($val) == 0) {
                        $val = false;
                    }
                } elseif (in_array($fld->fld_type, array('VARCHAR', 'TEXT'))) {
                    if (trim($val) == '') {
                        $val = false;
                    }
                } elseif ($fld->fld_type == 'DATETIME') {
                    if ($val == '0000-00-00 00:00:00' 
                        || $val == '1970-01-01 00:00:00') {
                        return '';
                    }
                    $val = str_replace(' ', 'T', $val);
                } elseif ($fld->fld_type == 'DATE') {
                    if ($val == '0000-00-00' || $val == '1970-01-01') {
                        return '';
                    }
                }
            }
            if ($val != htmlspecialchars($val, ENT_XML1, 'UTF-8')
                || strpos($val, '<') !== false
                || strpos($val, '>') !== false) {
                $val = '<![CDATA[' . $val . ']]>'; // !in_array($val, array('Y', 'N', '?'))
            }
        }
        return $val;
    }
    
    public function checkValEmpty($fldType, $val)
    {
        $val = trim($val);
        if ($fldType == 'DATE' 
            && ($val == '' 
                || $val == '0000-00-00' 
                || $val == '1970-01-01')) {
            return true;
        } elseif ($fldType == 'DATETIME' 
            && ($val == '' 
                || $val == '0000-00-00 00:00:00' 
                || $val == '1970-01-01 00:00:00')) {
            return true;
        }
        return false;
    }
    
    public function chkRecsPub(Request $request, $treeID = 1)
    {
        if ($treeID <= 0) {
            $treeID = $this->treeID;
        }
        if (!session()->has('chkRecsPub') || $request->has('refresh')) {
            /*
            $dumped = [];
            if ($request->has('refresh')) {
                $chk = SLSearchRecDump::where('sch_rec_dmp_tree_id', $treeID)
                    ->delete();
                unset($chk);
            } else {
                $chk = SLSearchRecDump::where('sch_rec_dmp_tree_id', $treeID)
                    ->select('sch_rec_dmp_rec_id')
                    ->get();
                if ($chk->isNotEmpty()) {
                    foreach ($chk as $rec) {
                        $dumped[] = $rec->sch_rec_dmp_rec_id;
                    }
                }
                unset($chk);
            }
            $this->initSearcher();
            if (sizeof($this->searcher->allPublicCoreIDs) > 0) {
                foreach ($this->searcher->allPublicCoreIDs as $coreID) {
                    if (!in_array($coreID, $dumped)) {
                        //$this->genRecDump($coreID, false);
                    }
                }
            }
            $this->reloadStats($this->searcher->allPublicCoreIDs);
            */
            session()->put('chkRecsPub', 1);
            session()->save();
            return true;
        }
        return false;
    }
    
    public function genRecDump($coreID, $loadTree = true)
    {
        $coreTbl = $GLOBALS["SL"]->coreTbl;
        if ($loadTree) {
            $this->loadXmlMapTree($GLOBALS["SL"]->REQ, true);
        }
        if ($GLOBALS["SL"]->xmlTree["coreTbl"] == $GLOBALS["SL"]->coreTbl 
            && $GLOBALS["SL"]->treeRow->tree_opts%Globals::TREEOPT_SEARCH > 0) {
            $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        } else { // XML core table is different from main tree
            $this->loadSessInfo($GLOBALS["SL"]->xmlTree["coreTbl"]);
            $this->loadAllSessData($GLOBALS["SL"]->xmlTree["coreTbl"], $coreID);
            $coreTbl = $GLOBALS["SL"]->xmlTree["coreTbl"];
        }
        $treeID = $GLOBALS["SL"]->chkReportCoreTree($coreTbl);
        if (!isset($this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]])) {
            return false;
        }
        $dump = $this->genRecDumpNode(
                $this->xmlMapTree->rootID, 
                $this->xmlMapTree->nodeTiers, 
                $this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]][0]
            ) . $this->genRecDumpXtra();
        $dump = str_replace('  ', ' ', trim($dump));
        $dump = utf8_encode(htmlentities($GLOBALS["SL"]->stdizeChars($dump)));

        $dumpRec = new SLSearchRecDump;
        $dumpRec->sch_rec_dmp_tree_id  = $treeID;
        $dumpRec->sch_rec_dmp_rec_id   = $this->sessData->getDataCoreID();
        $dumpRec->sch_rec_dmp_perms    = $GLOBALS["SL"]->getCacheSffxAdds();
        $dumpRec->sch_rec_dmp_rec_dump = $dump;
        $chk = SLSearchRecDump::where('sch_rec_dmp_tree_id', '=', $treeID)
            ->where('sch_rec_dmp_rec_id', '=', $dumpRec->sch_rec_dmp_rec_id)
            ->where('sch_rec_dmp_perms', 'LIKE', $dumpRec->sch_rec_dmp_perms)
            ->where('sch_rec_dmp_rec_dump', 'LIKE', $dump)
            ->get();
        if ($chk->isEmpty()) {
            try {
                $dumpRec->save();
            } catch (Exception $e) {
                $log = new SLLogActions;
                $log->log_database = $this->dbID;
                $log->log_user     = $this->v["uID"];
                $log->log_action   = 'Search Rec Dump';
                $log->log_old_name = 'Tree ' . $treeID;
                $log->log_new_name = 'Rec ' . $dumpRec->sch_rec_dmp_rec_id;
                $log->save();
            }
        }
        return $dumpRec;
    }
    
    public function genRecDumpNode($nID, $nodeTiers, $rec, $overV = [])
    {
        $ret = '';
        $v = $this->getXmlTmpV($nID);
        if (sizeof($v["tblFlds"]) > 0) {
            foreach ($v["tblFlds"] as $i => $fld) {
                $ret .= ' ' . $this->genXmlFormatVal($rec, $fld, $v["tblAbbr"]);
            }
        }
        if (sizeof($v["tblHelp"]) > 0) {
            foreach ($v["tblHelp"] as $help) {
                $nextV = $this->getXmlTmpV(-3, $help);
                $kidRows = $this->sessData->getChildRows($v["tbl"], $rec->getKey(), $nextV["tbl"]);
                if (sizeof($kidRows) > 0) {
                    foreach ($kidRows as $j => $kid) {
                        $abbr = $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->tbl[$help]];
                        $ret .= ' ' . $this->genXmlFormatVal(
                                $kid, 
                                $v["tblHelpFld"][$help], 
                                $abbr
                            );
                    }
                }
            }
        }
//echo 'genRecDumpNode(<pre>'; print_r($nodeTiers[1]); echo '</pre>'; exit;
        for ($i = 0; $i < sizeof($nodeTiers[1]); $i++) {
            $tbl2 = $this->xmlMapTree->getNodeTblName($nodeTiers[1][$i][0]);
            $kidRows = $this->sessData->getChildRows($v["tbl"], $rec->getKey(), $tbl2);
            if (sizeof($kidRows) > 0) {
                $nextV = $this->getXmlTmpV($nodeTiers[1][$i][0]);
                foreach ($kidRows as $j => $kid) {
                    $ret .= ' ' . $this->genRecDumpNode(
                            $nodeTiers[1][$i][0], 
                            $nodeTiers[1][$i], 
                            $kid
                        );
                }
            }
        }
        return $ret;
    }

    protected function genRecDumpXtra()
    {
        return '';
    }

}
