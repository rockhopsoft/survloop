<?php
/**
  * TreeSurvAPI is extends a standard branching tree, for maps of API exports.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers\Tree;

use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\SLNode;
use App\Models\SLFields;
use App\Models\SLSearchRecDump;
use SurvLoop\Controllers\Globals\Globals;
use SurvLoop\Controllers\Tree\TreeCoreSess;

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
            if ($this->allNodes[$nID]->nodeRow->NodeParentID == -3
                && (!$this->rootID || intVal($this->rootID) <= 0)) {
                $this->rootID = $this->allNodes[$nID]->nodeRow->NodeParentID;
            }
        }
        $this->checkTreeRoot();
        $this->canEditTree = ($this->v["uID"] > 0 && $this->v["user"]->hasRole('administrator|databaser'));
        return true;
    }
    
    public function checkTreeRoot()
    {
        if ((!$this->rootID || intVal($this->rootID) <= 0) && intVal($GLOBALS["SL"]->treeRow->TreeCoreTable) > 0) {
            $chk = SLNode::where('NodeTree', $this->treeID)
                ->where('NodeParentID', -3)
                ->first();
            if (!$chk || !isset($chk->NodeID)) {
                $newRoot = new SLNode;
                $newRoot->NodeTree        = $this->treeID;
                $newRoot->NodeParentID    = -3;
                $newRoot->NodeType        = 'Page';
                $newRoot->NodePromptNotes = $GLOBALS["SL"]->treeRow->TreeCoreTable;
                $newRoot->NodePromptText  = $GLOBALS["SL"]->coreTbl;
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
            $node->nodeRow->NodeParentID    = $GLOBALS["SL"]->REQ->nodeParentID;
            $node->nodeRow->NodeParentOrder = 0;
            $node->nodeRow->NodeOpts        = 1;
            $node->nodeRow->NodeType        = 'XML';
        }
        
        if ($GLOBALS["SL"]->REQ->has('sub')) {
            if ($GLOBALS["SL"]->REQ->has('deleteNode') && intVal($GLOBALS["SL"]->REQ->input('deleteNode')) == 1) {
                $this->treeAdminNodeDelete($node->nodeRow->NodeID);
            } else {
                if ($nodeIN <= 0) {
                    $node = $this->treeAdminNodeNew($node);
                }
                if (intVal($node->nodeRow->NodeOpts) < 1) {
                    $node->nodeRow->NodeOpts = 1;
                }
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
            "REQ"         => $GLOBALS["SL"]->REQ
            ]);
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
                return view('vendor.survloop.admin.tree.node-print-core', [
                    "canEditTree"    => $this->canEditTree, 
                    "REQ"            => $GLOBALS["SL"]->REQ, 
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
    
    protected function maxUserView()
    {
        return true;
    }
    
    public function loadXmlMapTree(Request $request, $forceReload = false)
    {
        $this->survLoopInit($request);
        if (isset($GLOBALS["SL"]->xmlTree["id"]) && (empty($this->xmlMapTree) || $forceReload)) {
            $this->xmlMapTree = new TreeSurvAPI;
            $this->xmlMapTree->loadTree($GLOBALS["SL"]->xmlTree["id"], $request, true);
        }
        return true;
    }
        
    protected function getXmlTmpV($nID, $tblID = -3)
    {
        $v = [];
        if ($tblID > 0) {
            $v["tbl"] = $GLOBALS["SL"]->tbl[$tblID];
        } else {
            $v["tbl"] = $this->xmlMapTree->getNodeTblName($nID);
        }
        $v["tblID"]    = ((isset($GLOBALS["SL"]->tblI[$v["tbl"]])) ? $GLOBALS["SL"]->tblI[$v["tbl"]] : 0);
        $v["tblAbbr"]  = ((isset($GLOBALS["SL"]->tblAbbr[$v["tbl"]])) ? $GLOBALS["SL"]->tblAbbr[$v["tbl"]] : '');
        $v["TblOpts"]  = 1;
        if ($nID > 0 && isset($this->xmlMapTree->allNodes[$nID])) {
            $v["TblOpts"] = $this->xmlMapTree->allNodes[$nID]->nodeOpts;
        }
        $v["tblFlds"] = SLFields::select()
            ->where('FldDatabase', $this->dbID)
            ->where('FldTable', '=', $v["tblID"])
            ->orderBy('FldOrd', 'asc')
            ->orderBy('FldEng', 'asc')
            ->get();
        $v["tblFldEnum"] = $v["tblFldDefs"] = [];
        if ($v["tblFlds"]->isNotEmpty()) {
            foreach ($v["tblFlds"] as $i => $fld) {
                $v["tblFldDefs"][$fld->FldID] = [];
                if (strpos($fld->FldValues, 'Def::') !== false) {
                    $set = $GLOBALS["SL"]->def->getSet(str_replace('Def::', '', $fld->FldValues));
                    if (sizeof($set) > 0) {
                        foreach ($set as $def) {
                            $v["tblFldDefs"][$fld->FldID][] = $def->DefValue;
                        }
                    }
                } elseif (trim($fld->FldValues) != '' && strpos($fld->FldValues, ';') !== false) {
                    $v["tblFldDefs"][$fld->FldID] = explode(';', $fld->FldValues);
                }
                $v["tblFldEnum"][$fld->FldID] = (sizeof($v["tblFldDefs"][$fld->FldID]) > 0);
            }
        }
        $v["tblHelp"] = $v["tblHelpFld"] = [];
        if ($v["tblID"] > 0 && sizeof($GLOBALS["SL"]->dataHelpers) > 0) {
            foreach ($GLOBALS["SL"]->dataHelpers as $helper) {
                if ($v["tbl"] == $helper->DataHelpParentTable && $helper->DataHelpValueField
                    && !in_array($GLOBALS["SL"]->tblI[$helper->DataHelpTable], $v["tblHelp"])) {
                    $v["tblHelp"][] = $GLOBALS["SL"]->tblI[$helper->DataHelpTable];
                    $v["tblHelpFld"][$GLOBALS["SL"]->tblI[$helper->DataHelpTable]] 
                        = SLFields::where('FldTable', $GLOBALS["SL"]->tblI[$helper->DataHelpTable])
                            ->where('FldName', substr($helper->DataHelpValueField, 
                                strlen($GLOBALS["SL"]->tblAbbr[$helper->DataHelpTable])))
                            ->first();
                }
            }
        }
        return $v;
    }
    
    public function genXmlSchema(Request $request)
    {
        $this->loadXmlMapTree($request);
        if (!isset($GLOBALS["SL"]->xmlTree["coreTbl"])) {
            return $this->redir('/');
        }
        $this->v["nestedNodes"] = $this->genXmlSchemaNode($this->xmlMapTree->rootID, $this->xmlMapTree->nodeTiers);
        $view = view('vendor.survloop.admin.tree.xml-schema', $this->v)->render();
        return Response::make($view, '200')->header('Content-Type', 'text/xml');
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
                if (isset($v["tblHelpFld"][$help]->FldName)) {
                    $v["kids"] .= '<xs:element name="' . $nextV["tbl"] . '" minOccurs="0">
                        <xs:complexType mixed="true"><xs:sequence>
                            <xs:element name="' . $v["tblHelpFld"][$help]->FldName 
                            . '" minOccurs="0" maxOccurs="unbounded" />
                        </xs:sequence></xs:complexType>
                    </xs:element>' . "\n";
                }
            }
        }
        for ($i = 0; $i < sizeof($nodeTiers[1]); $i++) {
            $v["kids"] .= $this->genXmlSchemaNode($nodeTiers[1][$i][0], $nodeTiers[1][$i]);
        }
        return view('vendor.survloop.admin.tree.xml-schema-node', $v )->render();
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
        $this->v["nestedNodes"] = $this->genXmlReportNode($this->xmlMapTree->rootID, $this->xmlMapTree->nodeTiers, 
            $this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]][0]);
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
        $v["rec"]     = $rec;
        $v["recFlds"] = [];
        if (sizeof($v["tblFlds"]) > 0) {
            foreach ($v["tblFlds"] as $i => $fld) {
                //if (!$this->checkValEmpty($fld->FldType, $rec->{ $v["tblAbbr"] . $fld->FldName })) {
                    $v["recFlds"][$fld->FldID] = $this->genXmlFormatVal($rec, $fld, $v["tblAbbr"]);
                //}
            }
        }
        $v["kids"] = '';
        if (is_array($v["tblHelp"]) && sizeof($v["tblHelp"]) > 0) {
            foreach ($v["tblHelp"] as $help) {
                $nextV = $this->getXmlTmpV(-3, $help);
                $kidRows = $this->sessData->getChildRows($v["tbl"], $rec->getKey(), $nextV["tbl"]);
                if ($kidRows && sizeof($kidRows) > 0) {
                    if (intVal($nextV["tblID"]) > 0 && $nextV["TblOpts"]%5 > 0) {
                        $v["kids"] .= '<' . $nextV["tbl"] . '>' . "\n";
                    }
                    foreach ($kidRows as $j => $kid) {
                        if (isset($v["tblHelpFld"][$help]->FldName)) {
                            //if (!$this->checkValEmpty($kid, 
                            //    $rec->{ $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->tbl[$help]] 
                            //        . $v["tblHelpFld"][$help] })) {
                                $v["kids"] .= '<' . $v["tblHelpFld"][$help]->FldName . '>' 
                                    . $this->genXmlFormatVal($kid, $v["tblHelpFld"][$help], 
                                        $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->tbl[$help]])
                                . '</' . $v["tblHelpFld"][$help]->FldName . '>' . "\n";
                            //}
                        }
                    }
                    if (intVal($nextV["tblID"]) > 0 && $nextV["TblOpts"]%5 > 0) {
                        $v["kids"] .= '</' . $nextV["tbl"] . '>' . "\n";
                    }
                }
            }
        }
        for ($i = 0; $i < sizeof($nodeTiers[1]); $i++) {
            $tbl2 = $this->xmlMapTree->getNodeTblName($nodeTiers[1][$i][0]);
            $kidRows = $this->sessData->getChildRows($v["tbl"], $rec->getKey(), $tbl2);
            if ($kidRows && sizeof($kidRows) > 0) {
                $nextV = $this->getXmlTmpV($nodeTiers[1][$i][0]);
                if (intVal($nextV["tblID"]) > 0 && $nextV["TblOpts"]%5 > 0) {
                    $v["kids"] .= '<' . $nextV["tbl"] . '>' . "\n";
                }
                foreach ($kidRows as $j => $kid) {
                    $v["kids"] .= $this->genXmlReportNode($nodeTiers[1][$i][0], $nodeTiers[1][$i], $kid);
                }
                if (intVal($nextV["tblID"]) > 0 && $nextV["TblOpts"]%5 > 0) {
                    $v["kids"] .= '</' . $nextV["tbl"] . '>' . "\n";
                }
            }
        }
        return view('vendor.survloop.admin.tree.xml-report-node', $v )->render();
    }
    
    // FldOpts %1 XML Public Data; %7 XML Private Data; %11 XML Sensitive Data; %13 XML Internal Use Data
    public function checkViewDataPerms($fld)
    {
        if ($fld && isset($fld->FldOpts) && intVal($fld->FldOpts) > 0) {
            if ($fld->FldOpts%7 > 0 && $fld->FldOpts%11 > 0 && $fld->FldOpts%13 > 0) {
                return true;
            }
            if (in_array($GLOBALS["SL"]->pageView, ['full', 'full-pdf', 'full-xml'])) {
                return true;
            }
            if ($fld->FldOpts%13 == 0 || $fld->FldOpts%11 == 0) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    public function checkFldDataPerms($fld)
    {
        if ($fld && isset($fld->FldOpts) && intVal($fld->FldOpts) > 0) {
            if ($fld->FldOpts%7 > 0 && $fld->FldOpts%11 > 0 && $fld->FldOpts%13 > 0) {
                return true;
            }
            if ($GLOBALS["SL"]->dataPerms == 'internal') {
                return true;
            } elseif ($fld->FldOpts%13 == 0) {
                return false;
            }
            if ($fld->FldOpts%11 == 0) {
                return ($GLOBALS["SL"]->dataPerms == 'sensitive');
            }
            if ($fld->FldOpts%7 == 0) {
                return in_array($GLOBALS["SL"]->dataPerms, ['private', 'sensitive']);
            }
        }
        return false;
    }
    
    public function genXmlFormatVal($rec, $fld, $abbr)
    {
        $val = false;
        if ($this->checkFldDataPerms($fld) && $this->checkViewDataPerms($fld) 
            && isset($rec->{ $abbr . $fld->FldName })) {
            $val = $rec->{ $abbr . $fld->FldName };
            if (strpos($fld->FldValues, 'Def::') !== false) {
                if (intVal($val) > 0) {
                    $val = $GLOBALS["SL"]->def->getVal(str_replace('Def::', '', $fld->FldValues), $val);
                } else {
                    $val = false;
                }
            } else { // not pulling values from a definition set
                if (in_array($fld->FldType, array('INT', 'DOUBLE'))) {
                    if (intVal($val) == 0) {
                        $val = false;
                    }
                } elseif (in_array($fld->FldType, array('VARCHAR', 'TEXT'))) {
                    if (trim($val) == '') {
                        $val = false;
                    } else {
                        if ($val != htmlspecialchars($val, ENT_XML1, 'UTF-8')) {
                            $val = '<![CDATA[' . $val . ']]>'; // !in_array($val, array('Y', 'N', '?'))
                        }
                    }
                } elseif ($fld->FldType == 'DATETIME') {
                    if ($val == '0000-00-00 00:00:00' || $val == '1970-01-01 00:00:00') {
                        return '';
                    }
                    $val = str_replace(' ', 'T', $val);
                } elseif ($fld->FldType == 'DATE') {
                    if ($val == '0000-00-00' || $val == '1970-01-01') {
                        return '';
                    }
                }
            }
        }
        return $val;
    }
    
    public function checkValEmpty($fldType, $val)
    {
        $val = trim($val);
        if ($fldType == 'DATE' && ($val == '' || $val == '0000-00-00' || $val == '1970-01-01')) {
            return true;
        } elseif ($fldType == 'DATETIME' 
            && ($val == '' || $val == '0000-00-00 00:00:00' || $val == '1970-01-01 00:00:00')) {
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
            $dumped = [];
            if ($request->has('refresh')) {
                $chk = SLSearchRecDump::where('SchRecDmpTreeID', $treeID)
                    ->delete();
                unset($chk);
            } else {
                $chk = SLSearchRecDump::where('SchRecDmpTreeID', $treeID)
                    ->select('SchRecDmpRecID')
                    ->get();
                if ($chk->isNotEmpty()) {
                    foreach ($chk as $rec) {
                        $dumped[] = $rec->SchRecDmpRecID;
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
            session()->put('chkRecsPub', 1);
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
            && $GLOBALS["SL"]->treeRow->TreeOpts%Globals::TREEOPT_SEARCH > 0) {
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
        $dump = $this->genRecDumpNode($this->xmlMapTree->rootID, $this->xmlMapTree->nodeTiers, 
            $this->sessData->dataSets[$GLOBALS["SL"]->xmlTree["coreTbl"]][0]) . $this->genRecDumpXtra();
        $dumpRec = new SLSearchRecDump;
        $dumpRec->SchRecDmpTreeID  = $treeID;
        $dumpRec->SchRecDmpRecID   = $this->sessData->getDataCoreID();
        $dumpRec->SchRecDmpRecDump = htmlentities($GLOBALS["SL"]->stdizeChars(
            str_replace('  ', ' ', trim($dump)))); // utf8_encode
        $dumpRec->save();
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
                        $ret .= ' ' . $this->genXmlFormatVal($kid, $v["tblHelpFld"][$help], 
                            $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->tbl[$help]]);
                    }
                }
            }
        }
        for ($i = 0; $i < sizeof($nodeTiers[1]); $i++) {
            $tbl2 = $this->xmlMapTree->getNodeTblName($nodeTiers[1][$i][0]);
            $kidRows = $this->sessData->getChildRows($v["tbl"], $rec->getKey(), $tbl2);
            if (sizeof($kidRows) > 0) {
                $nextV = $this->getXmlTmpV($nodeTiers[1][$i][0]);
                foreach ($kidRows as $j => $kid) {
                    $ret .= ' ' . $this->genRecDumpNode($nodeTiers[1][$i][0], $nodeTiers[1][$i], $kid);
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
