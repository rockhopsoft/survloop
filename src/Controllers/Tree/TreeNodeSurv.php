<?php
/**
  * TreeNodeSurv extends a standard branching tree's node for Survloop's needs.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use App\Models\SLNode;
use App\Models\SLNodeResponses;
use App\Models\SLConditions;
use App\Models\SLConditionsNodes;
use App\Models\SLFields;
use RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurvVars;

class TreeNodeSurv extends TreeNodeSurvVars
{
    
    // maybe initialize this way to lighten the tree's load?...
    public function loadNodeCache($nID = -3, $nCache = [])
    {
        if (sizeof($nCache) > 0) {
            if (isset($nCache["pID"])) {
                $this->parentID = $nCache["pID"];
            }
            if (isset($nCache["pOrd"])) {
                $this->parentOrd = $nCache["pOrd"];
            }
            if (isset($nCache["opts"])) {
                $this->nodeOpts = $nCache["opts"];
            }
            if (isset($nCache["type"])) {
                $this->nodeType = $nCache["type"];
            }
            if (isset($nCache["branch"])) {
                $this->dataBranch = $nCache["branch"];
            }
            if (isset($nCache["store"])) {
                $this->dataStore = $nCache["store"];
            }
            if (isset($nCache["set"])) {
                $this->responseSet = $nCache["set"];
            }
            if (isset($nCache["def"])) {
                $this->defaultVal = $nCache["def"];
            }
        }
        return true;
    }
    
    public function loadNodeRow($nID = -3, $nRow = NULL)
    {
        $this->nodeRow = null;
        if ($nRow) {
            $this->nodeRow = $nRow;
        } else {
            $searchNode = $nID;
            if ($nID <= 0 && $this->nodeID > 0) {
                $searchNode = $this->nodeID;
            }
            if ($searchNode > 0) {
                $this->nodeRow = SLNode::find($searchNode)
                    ->select('node_id', 'node_parent_id', 'node_parent_order', 
                        'node_opts', 'node_type', 'node_data_branch', 
                        'node_data_store', 'node_response_set', 'node_default');
            }
        }
        $this->copyFromRow();
        if (!$this->nodeRow) {
            $this->nodeRow = new SLNode;
            return false;
        }
        //$this->fillNodeRow();
        return true;
    }
    
    protected function copyFromRow()
    {
        if ($this->nodeRow && isset($this->nodeRow->node_parent_id)) {
            $this->parentID    = $this->nodeRow->node_parent_id;
            $this->parentOrd   = $this->nodeRow->node_parent_order;
            $this->nodeOpts    = $this->nodeRow->node_opts;
            $this->nodeType    = $this->nodeRow->node_type;
            $this->dataBranch  = $this->nodeRow->node_data_branch;
            $this->dataStore   = $this->nodeRow->node_data_store;
            $this->responseSet = $this->nodeRow->node_response_set;
            $this->defaultVal  = $this->nodeRow->node_default;
        }
        return true;
    }
    
    public function initiateNodeRow()
    {
        $this->copyFromRow();
        $this->conds = [];
        $chk = SLConditionsNodes::where('cond_node_node_id', $this->nodeID)
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $c) {
                $cond = SLConditions::find($c->cond_node_cond_id);
                if ($cond) {
                    $this->conds[] = $cond;
                }
            }
        }
        if ($this->conds && sizeof($this->conds) > 0) {
            foreach ($this->conds as $i => $c) {
                $c->loadVals();
            }
        }
        $this->hasShowKids = false;
        if ($this->nodeRow) {
            if ($this->isPage() || $this->isLoopRoot()) {
                $this->initNodeRowExtraOptsPage();
            } elseif (in_array($this->nodeRow->node_type, ['Text:Number', 'Slider'])) {
                $this->initNodeRowExtraOptsNumber();
            } elseif (in_array($this->nodeRow->node_type, ['Gender', 'Gender Not Sure'])) {
                $this->loadGenderResponses();
                $this->chkFldOther();
            } else { // default responses
                $this->responses = SLNodeResponses::where('node_res_node', $this->nodeID)
                    ->orderBy('node_res_ord', 'asc')
                    ->get();
                $this->chkFldOther();
            }
            $this->dataManips = SLNode::where('node_parent_id', $this->nodeID)
                ->where('node_type', 'Data Manip: Update')
                ->orderBy('node_parent_order', 'asc')
                ->get();
        }
        if ($this->nodeType == 'Send Email') {
            $this->initNodeRowExtraOptsEmail();
        } elseif (in_array($this->nodeType, ['Plot Graph', 'Line Graph'])) {
            $this->initNodeRowExtraOptsGraph();
        } elseif (in_array($this->nodeType, ['Pie Chart'])) {
            
        } elseif (in_array($this->nodeType, ['Bar Graph'])) {
            $this->initNodeRowExtraOptsBarGraph();
        } elseif (in_array($this->nodeType, ['Map'])) {
            
        } elseif ($this->isSpreadTbl()) {
            if (!isset($this->nodeRow->node_char_limit) 
                || intVal($this->nodeRow->node_char_limit) == 0) {
                $this->nodeRow->node_char_limit = 20;
            }
        }
        $this->isPageBlock();
        return true;
    }
    
    public function initNodeRowExtraOptsPage()
    {
        $this->extraOpts["meta-title"] = $this->extraOpts["meta-desc"] 
            = $this->extraOpts["meta-keywords"] = $this->extraOpts["meta-img"] = '';
        if (strpos($this->nodeRow->node_prompt_after, '::M::') !== false) {
            $meta = $GLOBALS["SL"]->mexplode('::M::', $this->nodeRow->node_prompt_after);
            if (isset($meta[0])) {
                $this->extraOpts["meta-title"] = $meta[0];
                if (isset($meta[1])) {
                    $this->extraOpts["meta-desc"] = $meta[1];
                    if (isset($meta[2])) {
                        $this->extraOpts["meta-keywords"] = $meta[2];
                        if (isset($meta[3])) {
                            $this->extraOpts["meta-img"] = $meta[3];
                        }
                    }
                }
            }
        }
        $slug = $GLOBALS["SL"]->treeRow->tree_slug;
        $nodeSlug = '/' . $this->nodeRow->node_prompt_notes;
        $this->extraOpts["page-url"] = '';
        if ($this->isPage()) {
            if ($GLOBALS["SL"]->treeRow->tree_type == 'Page') {
                if ($GLOBALS["SL"]->treeIsAdmin) {
                    if ($GLOBALS["SL"]->treeRow->tree_opts%7 == 0) {
                        $this->extraOpts["page-url"] = '/dashboard';
                    } else {
                        $this->extraOpts["page-url"] = '/dash/' . $slug;
                    }
                } else {
                    $this->extraOpts["page-url"] = '/' . $slug;
                }
            } else { // default survey mode
                if ($GLOBALS["SL"]->treeIsAdmin) {
                    $this->extraOpts["page-url"] = '/dash/' . $slug . $nodeSlug;
                } else {
                    $this->extraOpts["page-url"] = '/u/' . $slug . $nodeSlug;
                }
            }
        } else { // isLoopRoot
            if ($GLOBALS["SL"]->treeIsAdmin) {
                $this->extraOpts["page-url"] = '/dash/' . $slug . $nodeSlug;
            } else {
                $this->extraOpts["page-url"] = '/u/' . $slug . $nodeSlug;
            }
        }
        return true;
    }

    protected function initNodeRowExtraOptsNumber()
    {
        // load min and max values
        $this->extraOpts["minVal"] 
            = $this->extraOpts["maxVal"] 
            = $this->extraOpts["incr"] 
            = $this->extraOpts["unit"] 
            = false;
        $chk = SLNodeResponses::where('node_res_node', $this->nodeID)
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $res) {
                if (isset($res->node_res_ord)) {
                    if (isset($res->node_res_value)) {
                        switch (intVal($res->node_res_ord)) {
                            case -1:
                                $this->extraOpts["minVal"] 
                                    = floatVal($res->node_res_value); 
                                break;
                            case 1:
                                $this->extraOpts["maxVal"] 
                                    = floatVal($res->node_res_value); 
                                break;
                            case 2:  
                                $this->extraOpts["incr"]   
                                    = floatVal($res->node_res_value); 
                                break;
                        }
                    }
                    if (isset($res->node_res_eng)) {
                        $this->extraOpts["unit"] = trim($res->node_res_eng);
                    }
                }
            }
        }
        return true;
    }

    protected function initNodeRowExtraOptsEmail()
    {
        $this->extraOpts["emailTo"] = $this->extraOpts["emailCC"] 
            = $this->extraOpts["emailBCC"] = [];
        if (strpos($this->nodeRow->node_prompt_notes, '::CC::') !== false) {
            list($to, $ccs) = explode('::CC::', $this->nodeRow->node_prompt_notes);
            list($cc, $bcc) = explode('::BCC::', $ccs);
            $to = str_replace('::TO::', '', $to);
            $this->extraOpts["emailTo"]  = $GLOBALS["SL"]->mexplode(',', $to);
            $this->extraOpts["emailCC"]  = $GLOBALS["SL"]->mexplode(',', $cc);
            $this->extraOpts["emailBCC"] = $GLOBALS["SL"]->mexplode(',', $bcc);
        }
        return true;
    }

    protected function initNodeRowExtraOptsGraph()
    {
        if (strpos($this->nodeRow->node_prompt_notes, '::Ylab::') !== false) {
            list($this->extraOpts["y-axis"], $xtras) = explode('::Ylab::', 
                str_replace('::Y::', '', $this->nodeRow->node_prompt_notes));
            list($this->extraOpts["y-axis-lab"], $xtras) = explode('::X::', $xtras);
            list($this->extraOpts["x-axis"], $xtras) = explode('::Xlab::', $xtras);
            list($this->extraOpts["x-axis-lab"], $this->extraOpts["conds"]) 
                = explode('::Cnd::', $xtras);
            $this->extraOpts["data-conds"] = $GLOBALS["SL"]
                ->mexplode('#', $this->extraOpts["conds"]);
        }
        return true;
    }

    protected function initNodeRowExtraOptsBarGraph()
    {
        if (strpos($this->nodeRow->node_prompt_notes, '::Ylab::') !== false) {
            list($this->extraOpts["y-axis"], $xtras) = explode('::Ylab::', 
                str_replace('::Y::', '', $this->nodeRow->node_prompt_notes));
            list($this->extraOpts["y-axis-lab"], $xtras) = explode('::Lab1::', $xtras);
            list($this->extraOpts["lab1"], $xtras) = explode('::Lab2::', $xtras);
            list($this->extraOpts["lab2"], $xtras) = explode('::Clr1::', $xtras);
            list($this->extraOpts["clr1"], $xtras) = explode('::Clr2::', $xtras);
            list($this->extraOpts["clr2"], $xtras) = explode('::Opc1::', $xtras);
            list($this->extraOpts["opc1"], $xtras) = explode('::Opc2::', $xtras);
            list($this->extraOpts["opc2"], $xtras) = explode('::Hgt::', $xtras);
            list($this->extraOpts["hgt"], $this->extraOpts["conds"]) 
                = explode('::Cnd::', $xtras);
            $this->extraOpts["data-conds"] = $GLOBALS["SL"]
                ->mexplode('#', $this->extraOpts["conds"]);
            $this->extraOpts['hgt-sty'] = $this->extraOpts['hgt'];
            if (trim($this->extraOpts['hgt']) != '') {
                if (strpos($this->extraOpts['hgt'], '%') === false) {
                    $this->extraOpts['hgt-sty'] .= $this->extraOpts['hgt-sty'] . 'px'; 
                }
            } else {
                $this->extraOpts['hgt-sty'] = '420px';
            }
        }
        return true;
    }
    
    public function loadGenderResponses()
    {
        $this->addTmpResponse("F", "Female");
        $this->addTmpResponse("M", "Male");
        $this->addTmpResponse("O", "Other:");
        if ($this->nodeType == 'Gender Not Sure') {
            $this->addTmpResponse("?", "Not Sure");
        }
        return true;
    }
    
    public function chkFldOther()
    {
        $this->fldHasOther = [];
        if (sizeof($this->responses) > 0) {
            list($tbl, $fld) = $this->getTblFld();
            foreach ($this->responses as $j => $res) {
                if (intVal($res->node_res_show_kids) > 0) {
                    $this->hasShowKids = true;
                }
                if (isset($GLOBALS["SL"]->fldOthers[$fld . '_other'])
                    && intVal($GLOBALS["SL"]->fldOthers[$fld . '_other']) > 0) {
                    if ($this->detectFldNameOther($res->node_res_value)
                        || $this->detectFldNameOther($res->node_res_eng)) {
                        $this->fldHasOther[] = $j;
                    }
                }
            }
        }
        return true;
    }
    
    public function detectFldNameOther($str)
    {
        return in_array(strtolower(trim(strip_tags($str))), ['other', 'other:']);
    }
    
    public function valueShowsKid($responseVal = '')
    {
        if (sizeof($this->responses) > 0) {
            foreach ($this->responses as $res) {
                if ($res->node_res_value == $responseVal) {
                    if (intVal($res->node_res_show_kids) > 0) {
                        return true;
                    }
                    return false;
                }
            }
        }
        return false;
    }
    
    public function indexShowsKid($ind = '')
    {
        return ($this->indexShowsKidNode($ind) > 0);
    }
    
    public function indexShowsKidNode($ind = '')
    {
        if (empty($this->responses) 
            || !isset($this->responses[$ind])
            || !isset($this->responses[$ind]->node_res_show_kids)) {
            return -3;
        }
        return intVal($this->responses[$ind]->node_res_show_kids);
    }
    
    public function indexMutEx($ind = '')
    {
        return (sizeof($this->responses) > 0 
            && isset($this->responses[$ind]) 
            && intVal($this->responses[$ind]->node_res_mut_ex) == 1);
    }
    
    public function addTmpResponse($val = '', $eng = '')
    {
        if (trim($eng) == '') {
            $eng = $val;
        }
        $newRes = new SLNodeResponses;
        $newRes->node_res_node      = $this->nodeID;
        $newRes->node_res_eng       = $eng;
        $newRes->node_res_value     = $val;
        $newRes->node_res_ord       = sizeof($this->responses);
        $newRes->node_res_show_kids = 0;
        $this->responses[] = $newRes;
        return $newRes;
    }
    
    public function addTmpResponses($responses = [])
    {
        if (sizeof($responses) > 0) {
            foreach ($responses as $res) {
                $this->addTmpResponse($res[0], $res[1]);
            }
        }
        return $this->responses;
    }

    public function printNodePublicResponses()
    {
        if (sizeof($this->responses) == 3 
            && $this->responses[1]->node_res_value == '...') {
            $start = intVal($this->responses[0]->node_res_value);
            $finish = intVal($this->responses[2]->node_res_value);
            $this->responses = [];
            if ($start < $finish) {
                for ($i=$start; $i<=$finish; $i++) {
                    $this->addTmpResponse($i);
                }
            } else {
                for ($i=$start; $i>=$finish; $i--) {
                    $this->addTmpResponse($i);
                }
            }
            $this->chkFldOther();
        }
        return true;
    }
    
    public function chkFill()
    {
        if ($this->nodeRow === null || !isset($this->nodeRow->node_id)) {
            $this->fillNodeRow();
        }
        return true;
    }
    
    public function getTblFld()
    {
        $this->chkFill();
        if ($this->tbl == '' || $this->fld) {
            list($this->tbl, $this->fld) = $GLOBALS["SL"]->splitTblFld($this->dataStore);
        }
        return [ $this->tbl, $this->fld ];
    }
    
    public function getTblFldID()
    {
        $this->chkFill();
        return $GLOBALS["SL"]->getTblFldID($this->dataStore);
    }
    
    public function getFldRow()
    {
        $this->chkFill();
        return $GLOBALS["SL"]->getTblFldRow($this->dataStore);
    }
    
    public function getTblFldName()
    {
        $this->chkFill();
        $tblFld = $GLOBALS["SL"]->splitTblFld($this->dataStore);
        if (sizeof($tblFld) > 1) {
            return $tblFld[1];
        }
        return '';
    }
    
    public function getTblFldLink($isPrint = true)
    {
        $fld = SLFields::find($this->getTblFldID());
        if ($fld && isset($fld->fld_table)) {
            return '<a target="_blank" href="' 
                . (($isPrint) ? '/db/1' : '/dashboard/db/all')
                . '?fldID=' . $fld->fld_id . '#' 
                . $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->fld_table]] 
                . $fld->fld_name . '" class="slGreenDark">'
                . $this->getTblFldName() . '</a>';
        }
        return '';
    }
    
    public function hasDefSet()
    {
        if (isset($this->extraOpts["hasDefSet"])) {
            return $this->extraOpts["hasDefSet"];
        }
        $pos = strpos($this->nodeRow->node_response_set, 'Definition::');
        $this->extraOpts["hasDefSet"] = ($pos !== false);
        return $this->extraOpts["hasDefSet"];
    }
    
    public function parseResponseSet()
    {
        $set = [ "type" => '', "set" => '' ];
        if (trim($this->nodeRow->node_response_set) != '' 
            && strpos($this->nodeRow->node_response_set, '::') !== false) {
            list($set["type"], $set["set"]) = explode('::', $this->nodeRow->node_response_set);
        }
        return $set;
    }
    
    public function nodePreview()
    {
        return substr(strip_tags($this->nodeRow->node_prompt_text), 0, 20);
    }
    
    public function tierPathStr($tierPathArr = [])
    {
        if (sizeof($tierPathArr) == 0) {
            return implode('-', $this->nodeTierPath) . '-';
        }
        return implode('-', $tierPathArr) . '-';
    }
    
    public function checkBranch($tierPathArr = [])
    {
        $tierPathStr = $this->tierPathStr($tierPathArr);
        if ($tierPathStr != '') {
            $pos = strpos($this->tierPathStr($this->nodeTierPath), $tierPathStr);
            return ($pos === 0);
        }
        return 0;
    }
    
    
    public function getManipUpdate()
    {
        if (!$this->isDataManip()) {
            return [ '', '', '' ];
        }
        $this->fillNodeRow();
        if (trim($this->dataBranch) != '') {
            $tbl = $this->dataBranch;
            $fld = str_replace($tbl . ':', '', $this->dataStore);
        } else {
            list($tbl, $fld) = $GLOBALS["SL"]->splitTblFld($this->dataStore);
        }
        $newVal = (intVal($this->responseSet) > 0) 
            ? intVal($this->responseSet) : trim($this->defaultVal);
        return [ $tbl, $fld, $newVal ];
    }
    
    public function printManipUpdate()
    {
        if (!$this->isDataManip()) {
            return '';
        }
        $manipUpdate = $this->getManipUpdate();
        if (trim($manipUpdate[0]) == '' || $manipUpdate[1] == '') {
            return '';
        }
        $ret = ' , ' . $manipUpdate[1] . ' = ';
        if (isset($this->responseSet) && intVal($this->responseSet) > 0) {
            $ret .= $GLOBALS["SL"]->def->getValById(intVal($this->responseSet));
        } else {
            $ret .= $manipUpdate[2];
        }
        if (sizeof($this->dataManips) > 0) {
            foreach ($this->dataManips as $manip) {
                $tmpNode = new TreeNodeSurv($manip->nodeID, $manip);
                $ret .= $tmpNode->printManipUpdate();
            }
        }
        return $ret;
    }
    
}
