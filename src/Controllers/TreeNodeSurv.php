<?php
/**
  * TreeNodeSurv extends a standard branching tree's node for SurvLoop's needs.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use App\Models\SLNode;
use App\Models\SLNodeResponses;
use App\Models\SLConditions;
use App\Models\SLConditionsNodes;
use App\Models\SLFields;
use SurvLoop\Controllers\TreeNodeCore;

class TreeNodeSurv extends TreeNodeCore
{
    public $conds         = [];
    public $responses     = [];
    public $hasShowKids   = false;
    public $hasPageParent = false;
    public $fldHasOther   = [];
    
    public $dataManips    = [];
    public $colors        = [];
    public $extraOpts     = [];
    
    public $primeOpts     = [
        "Required"         => 5, 
        "OneLineResponses" => 17, 
        "OneLiner"         => 11, 
        "RequiredInLine"   => 13
    ];
    
    // Tree Nodes are assigned an optional property when ( SLNode->NodeOpts%OPT_PRIME == 0 )
    // Node Options
    public const OPT_DROPTAGGER = 53; // This node's dropdown stores like a checkbox, associating tags

    // Node Visual Layout Options
    public const OPT_SKINNY     = 67; // This node's contents are wrapped in the skinny page width 
    public const OPT_JUMBOTRON  = 37; // Wrap the contents of this node inside bootstrap's Jumbotron
    public const OPT_BLOCKBACKG = 71; // Node has content block background and color properties
    public const OPT_CARDWRAP   = 89; // Wrap the contents of this node inside a Card
    public const OPT_DEFERLOAD  = 97; // Defer loading the contents of this load until after the rest of the page
    
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
        eval("\$prime = self::OPT_" . $type . ";");
        return $prime;
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
        } elseif ($nID > 0) {
            $this->nodeRow = SLNode::find($nID)
                ->select('NodeID', 'NodeParentID', 'NodeParentOrder', 'NodeOpts', 'NodeType', 'NodeDataBranch', 
                    'NodeDataStore', 'NodeResponseSet', 'NodeDefault');
        } elseif ($this->nodeID > 0) {
            $this->nodeRow = SLNode::find($this->nodeID)
                ->select('NodeID', 'NodeParentID', 'NodeParentOrder', 'NodeOpts', 'NodeType', 'NodeDataBranch', 
                    'NodeDataStore', 'NodeResponseSet', 'NodeDefault');
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
        if ($this->nodeRow) {
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
        if ($chk->isNotEmpty()) {
            foreach ($chk as $c) {
                $cond = SLConditions::find($c->CondNodeCondID);
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
                $this->extraOpts["meta-title"] = $this->extraOpts["meta-desc"] = $this->extraOpts["meta-keywords"] 
                    = $this->extraOpts["meta-img"] = '';
                if (strpos($this->nodeRow->NodePromptAfter, '::M::') !== false) {
                    $meta = $GLOBALS["SL"]->mexplode('::M::', $this->nodeRow->NodePromptAfter);
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
                if ($this->isPage()) {
                    $this->extraOpts["page-url"] = '';
                    if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
                        if ($GLOBALS["SL"]->treeIsAdmin) {
                            if ($GLOBALS["SL"]->treeRow->TreeOpts%7 == 0) {
                                $this->extraOpts["page-url"] = '/dashboard';
                            } else {
                                $this->extraOpts["page-url"] = '/dash/' . $GLOBALS["SL"]->treeRow->TreeSlug;
                            }
                        } else {
                            $this->extraOpts["page-url"] = '/' . $GLOBALS["SL"]->treeRow->TreeSlug;
                        }
                    } else { // default survey mode
                        if ($GLOBALS["SL"]->treeIsAdmin) {
                            $this->extraOpts["page-url"] = '/dash/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/' 
                                . $this->nodeRow->NodePromptNotes;
                        } else {
                            $this->extraOpts["page-url"] = '/u/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/' 
                                . $this->nodeRow->NodePromptNotes;
                        }
                    }
                } else { // isLoopRoot
                    $this->extraOpts["page-url"] = '';
                    if ($GLOBALS["SL"]->treeIsAdmin) {
                        $this->extraOpts["page-url"] = '/dash/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/' 
                            . $this->nodeRow->NodePromptNotes;
                    } else {
                        $this->extraOpts["page-url"] = '/u/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/' 
                            . $this->nodeRow->NodePromptNotes;
                    }
                }
            }
            if (in_array($this->nodeRow->NodeType, ['Text:Number', 'Slider'])) { // load min and max values
                $this->extraOpts["minVal"] = $this->extraOpts["maxVal"] = $this->extraOpts["incr"] 
                    = $this->extraOpts["unit"] = false;
                $chk = SLNodeResponses::where('NodeResNode', $this->nodeID)
                    ->get();
                if ($chk->isNotEmpty()) {
                    foreach ($chk as $res) {
                        if (isset($res->NodeResOrd)) {
                            if (isset($res->NodeResValue)) {
                                switch (intVal($res->NodeResOrd)) {
                                    case -1: $this->extraOpts["minVal"] = floatVal($res->NodeResValue); break;
                                    case 1:  $this->extraOpts["maxVal"] = floatVal($res->NodeResValue); break;
                                    case 2:  $this->extraOpts["incr"]   = floatVal($res->NodeResValue); break;
                                }
                            }
                            if (isset($res->NodeResEng)) {
                                $this->extraOpts["unit"] = trim($res->NodeResEng);
                            }
                        }
                    }
                }
            } else { // default responses                      
                $this->responses = SLNodeResponses::where('NodeResNode', $this->nodeID)
                    ->orderBy('NodeResOrd', 'asc')
                    ->get();
                $this->chkFldOther();
            }
            $this->dataManips = SLNode::where('NodeParentID', $this->nodeID)
                ->where('NodeType', 'Data Manip: Update')
                ->orderBy('NodeParentOrder', 'asc')
                ->get();
        }
        if ($this->nodeType == 'Send Email') {
            $this->extraOpts["emailTo"] = $this->extraOpts["emailCC"] = $this->extraOpts["emailBCC"] = [];
            if (strpos($this->nodeRow->NodePromptNotes, '::CC::') !== false) {
                list($to, $ccs) = explode('::CC::', $this->nodeRow->NodePromptNotes);
                list($cc, $bcc) = explode('::BCC::', $ccs);
                $this->extraOpts["emailTo"]  = $GLOBALS["SL"]->mexplode(',', str_replace('::TO::', '', $to));
                $this->extraOpts["emailCC"]  = $GLOBALS["SL"]->mexplode(',', $cc);
                $this->extraOpts["emailBCC"] = $GLOBALS["SL"]->mexplode(',', $bcc);
            }
        } elseif (in_array($this->nodeType, ['Plot Graph', 'Line Graph'])) {
            if (strpos($this->nodeRow->NodePromptNotes, '::Ylab::') !== false) {
                list($this->extraOpts["y-axis"], $xtras) = explode('::Ylab::', 
                    str_replace('::Y::', '', $this->nodeRow->NodePromptNotes));
                list($this->extraOpts["y-axis-lab"], $xtras) = explode('::X::', $xtras);
                list($this->extraOpts["x-axis"], $xtras) = explode('::Xlab::', $xtras);
                list($this->extraOpts["x-axis-lab"], $this->extraOpts["conds"]) = explode('::Cnd::', $xtras);
                $this->extraOpts["data-conds"] = $GLOBALS["SL"]->mexplode('#', $this->extraOpts["conds"]);
            }
        } elseif (in_array($this->nodeType, ['Pie Chart'])) {
            
        } elseif (in_array($this->nodeType, ['Bar Graph'])) {
            if (strpos($this->nodeRow->NodePromptNotes, '::Ylab::') !== false) {
                list($this->extraOpts["y-axis"], $xtras) = explode('::Ylab::', 
                    str_replace('::Y::', '', $this->nodeRow->NodePromptNotes));
                list($this->extraOpts["y-axis-lab"], $xtras) = explode('::Lab1::', $xtras);
                list($this->extraOpts["lab1"], $xtras) = explode('::Lab2::', $xtras);
                list($this->extraOpts["lab2"], $xtras) = explode('::Clr1::', $xtras);
                list($this->extraOpts["clr1"], $xtras) = explode('::Clr2::', $xtras);
                list($this->extraOpts["clr2"], $xtras) = explode('::Opc1::', $xtras);
                list($this->extraOpts["opc1"], $xtras) = explode('::Opc2::', $xtras);
                list($this->extraOpts["opc2"], $xtras) = explode('::Hgt::', $xtras);
                list($this->extraOpts["hgt"], $this->extraOpts["conds"]) = explode('::Cnd::', $xtras);
                $this->extraOpts["data-conds"] = $GLOBALS["SL"]->mexplode('#', $this->extraOpts["conds"]);
                $this->extraOpts['hgt-sty'] = $this->extraOpts['hgt'];
                if (trim($this->extraOpts['hgt']) != '') {
                    if (strpos($this->extraOpts['hgt'], '%') === false) {
                        $this->extraOpts['hgt-sty'] .= $this->extraOpts['hgt-sty'] . 'px'; 
                    }
                } else {
                    $this->extraOpts['hgt-sty'] = '420px';
                }
            }
        } elseif (in_array($this->nodeType, ['Map'])) {
            
            
        } elseif ($this->isSpreadTbl()) {
            if (!isset($this->nodeRow->NodeCharLimit) || intVal($this->nodeRow->NodeCharLimit) == 0) {
                $this->nodeRow->NodeCharLimit = 20;
            }
        }
        $this->isPageBlock();
        return true;
    }
    
    public function chkFldOther()
    {
        $this->fldHasOther = [];
        if (sizeof($this->responses) > 0) {
            list($tbl, $fld) = $this->getTblFld();
            foreach ($this->responses as $j => $res) {
                if (intVal($res->NodeResShowKids) > 0) {
                    $this->hasShowKids = true;
                }
                if (isset($GLOBALS["SL"]->fldOthers[$fld . 'Other'])
                    && intVal($GLOBALS["SL"]->fldOthers[$fld . 'Other']) > 0) {
                    if (in_array(strtolower(strip_tags($res->NodeResValue)), ['other', 'other:'])
                        || in_array(strtolower(strip_tags($res->NodeResEng)), ['other', 'other:'])) {
                        $this->fldHasOther[] = $j;
                    }
                }
            }
        }
        return true;
    }
    
    public function valueShowsKid($responseVal = '')
    {
        if (sizeof($this->responses) > 0) {
            foreach ($this->responses as $res) {
                if ($res->NodeResValue == $responseVal) {
                    if (intVal($res->NodeResShowKids) > 0) return true;
                    return false;
                }
            }
        }
        return false;
    }
    
    public function indexShowsKid($ind = '')
    {
        return (sizeof($this->responses) > 0 && isset($this->responses[$ind]) 
            && intVal($this->responses[$ind]->NodeResShowKids) > 0);
    }
    
    public function indexShowsKidNode($ind = '')
    {
        if (empty($this->responses) || !isset($this->responses[$ind])
            || !isset($this->responses[$ind]->NodeResShowKids)) return -3;
        return intVal($this->responses[$ind]->NodeResShowKids);
    }
    
    public function indexMutEx($ind = '')
    {
        return (sizeof($this->responses) > 0 && isset($this->responses[$ind]) 
            && intVal($this->responses[$ind]->NodeResMutEx) == 1);
    }
    
    public function clearResponses()
    {
        $this->responses = [];
        return true;
    }
    
    public function addTmpResponse($val = '', $eng = '')
    {
        if (trim($eng) == '') $eng = $val;
        $newRes = new SLNodeResponses;
        $newRes->NodeResNode     = $this->nodeID;
        $newRes->NodeResEng      = $eng;
        $newRes->NodeResValue    = $val;
        $newRes->NodeResOrd      = sizeof($this->responses);
        $newRes->NodeResShowKids = 0;
        $this->responses[] = $newRes;
        return $newRes;
    }
    
    public function chkFill()
    {
        if ($this->nodeRow === null || !isset($this->nodeRow->NodeID)) {
            $this->fillNodeRow();
        }
        return true;
    }
    
    public function getTblFld()
    {
        $this->chkFill();
        return $GLOBALS["SL"]->splitTblFld($this->dataStore);
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
        if (sizeof($tblFld) > 1) return $tblFld[1];
        return '';
    }
    
    public function getTblFldLink($isPrint = true)
    {
        $fld = SLFields::find($this->getTblFldID());
        if ($fld && isset($fld->FldTable)) {
            return '<a target="_blank" href="' . (($isPrint) ? '/db/1' : '/dashboard/db/all') . '#' 
                . $GLOBALS['SL']->tblAbbr[$GLOBALS['SL']->tbl[$fld->FldTable]] . $fld->FldName 
                . '" class="slGreenDark">' . $this->getTblFldName() . '</a>';
        }
        return '';
    }
    
    public function hasDefSet()
    {
        if (isset($this->extraOpts["hasDefSet"])) return $this->extraOpts["hasDefSet"];
        $this->extraOpts["hasDefSet"] = (strpos($this->nodeRow->NodeResponseSet, 'Definition::') !== false);
        return $this->extraOpts["hasDefSet"];
    }
    
    public function parseResponseSet()
    {
        $set = [ "type" => '', "set" => '' ];
        if (trim($this->nodeRow->NodeResponseSet) != '' && strpos($this->nodeRow->NodeResponseSet, '::') !== false) {
            list($set["type"], $set["set"]) = explode('::', $this->nodeRow->NodeResponseSet);
        }
        return $set;
    }
    
    public function nodePreview()
    {
        return substr(strip_tags($this->nodeRow->NodePromptText), 0, 20);
    }
    
    public function tierPathStr($tierPathArr = [])
    {
        if (sizeof($tierPathArr) == 0) return implode('-', $this->nodeTierPath) . '-';
        return implode('-', $tierPathArr) . '-';
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
    
    public function isDataPrint()
    {
        return (in_array($this->nodeType, ['Data Print', 'Data Print Row', 'Data Print Block', 'Data Print Columns',
            'Print Vert Progress']));
    }
    
    public function isSpreadTbl()
    {
        return ($this->nodeType == 'Spreadsheet Table');
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
    
    public function isInstructAny()
    {
        return ($this->isInstruct() || $this->isInstructRaw());
    }
    
    public function isBigButt()
    {
        return ($this->nodeType == 'Big Button'); 
    }
    
    public function hasResponseOpts()
    {
        if ($this->nodeType == 'Spreadsheet Table') {
            return (isset($this->dataStore) && trim($this->dataStore) != '');
        }
        return in_array($this->nodeType, ['Radio', 'Checkbox', 'Drop Down', 'Other/Custom']);
    }
    
    public function isSpecial()
    {
        return ($this->isNonLoopSpecial() || $this->isLoopRoot() || $this->isLoopCycle());
    }
    
    public function isNonLoopSpecial()
    {
        return ($this->isInstruct() || $this->isInstructRaw() || $this->isPage()  || $this->isBranch() 
            || $this->isLoopSort() || $this->isDataManip() || $this->isWidget() || $this->isBigButt() 
            || $this->isLayout() || $this->isDataPrint() || in_array($this->nodeType, ['Send Email']));
    }
    
    public function isWidget()
    {
        return ($this->isGraph() || in_array($this->nodeType, ['Search', 'Search Results', 'Search Featured', 
            'Member Profile Basics', 'Record Full', 'Record Full Public', 'Record Previews', 'Incomplete Sess Check', 
            'Back Next Buttons', 'Widget Custom', 'Admin Form', 'MFA Dialogue']));
    }
    
    public function isGraph()
    {
        return (in_array($this->nodeType, ['Plot Graph', 'Line Graph', 'Bar Graph', 'Pie Chart', 'Map']));
    }
    
    public function isLayout()
    {
        return (in_array($this->nodeType, ['Page Block', 'Layout Row', 'Layout Column', 'Layout Sub-Response',
            'Gallery Slider']));
    }
    
    public function isPageBlock()
    {
        //if ($GLOBALS["SL"]->treeRow->TreeType == 'Page' && $this->parentID == $GLOBALS["SL"]->treeRow->TreeRoot) {
        if ($this->isLayout() || $this->isInstructAny()) {
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
        return (in_array($this->nodeType, ['Drop Down', 'U.S. States']) && $this->nodeOpts%53 == 0);
    }
    
    public function isHnyPot()
    {
        return ($this->nodeType == 'Spambot Honey Pot');
    }
    
    
    public function getManipUpdate()
    {
        if (!$this->isDataManip()) {
            return ['', '', ''];
        }
        $this->fillNodeRow();
        if (trim($this->dataBranch) != '') {
            $tbl = $this->dataBranch;
            $fld = str_replace($tbl.':', '', $this->dataStore);
        } else {
            list($tbl, $fld) = $GLOBALS["SL"]->splitTblFld($this->dataStore);
        }
        $newVal = (intVal($this->responseSet) > 0) ? intVal($this->responseSet) : trim($this->defaultVal);
        return [$tbl, $fld, $newVal];
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
    
    public function loadPageBlockColors()
    {
        if (isset($this->nodeRow->NodeDefault) && trim($this->nodeRow->NodeDefault) != '' && empty($this->colors)) {
            $colors = explode(';;', $this->nodeRow->NodeDefault);
            if (isset($colors[0])) {
                $this->colors["blockBG"] = $colors[0];
            }
            if (isset($colors[1])) {
                $this->colors["blockText"] = $colors[1];
            }
            if (isset($colors[2])) {
                $this->colors["blockLink"] = $colors[2];
            }
            if (isset($colors[3])) {
                $this->colors["blockImg"] = $colors[3];
            }
            if (isset($colors[4])) {
                $this->colors["blockImgType"] = $colors[4];
            }
            if (isset($colors[5])) {
                $this->colors["blockImgFix"] = $colors[5];
            }
            if (isset($colors[6])) {
                $this->colors["blockAlign"] = $colors[6];
            }
            if (isset($colors[7])) {
                $this->colors["blockHeight"] = $colors[7];
            }
        }
        return true;
    }
    
    public function getIcon()
    {
        if ($this->isBranch()) {
            return '<i class="fa fa-share-alt" title="Branch Title"></i>';
        } elseif ($this->isLoopRoot()) {
            return '<i class="fa fa-refresh" title="Start of a New Page, Root of a Data Loop"></i>';
        } elseif ($this->isLoopCycle()) {
            return '<i class="fa fa-refresh" title="Data Loop within a Page"></i>';
        } elseif ($this->isLoopSort()) {
            return '<i class="fa fa-sort" title="Sort Data Loop Items"></i>';
        } elseif ($this->isDataManip()) {
            return '<i class="fa fa-database" title="Data Manipulation"></i>';
        } elseif ($this->isPage()) {
            return '<i class="fa fa-file-text-o" title="Start of a New Page"></i>';
        } elseif ($this->isBigButt() || $this->nodeType == 'Back Next Buttons') {
            return '<i class="fa fa-hand-pointer-o fa-rotate-90" aria-hidden="true"></i>';
        } elseif ($this->nodeType == 'Spambot Honey Pot') {
            return '<i class="fa fa-bug fa-rotate-90" title="Only visible to robots"></i>';
        } elseif ($this->nodeType == 'Send Email') {
            return '<i class="fa fa-envelope-o" aria-hidden="true" title="Send an Email"></i>';
        } elseif ($this->nodeType == 'Checkbox') {
            return '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
        } elseif ($this->nodeType == 'Radio') {
            return '<i class="fa fa-dot-circle-o" aria-hidden="true"></i>';
        } elseif (in_array($this->nodeType, ['Email', 'Gender', 'Gender Not Sure', 'Long Text', 
            'Text', 'Text:Number'])) {
            return '<i class="fa fa-i-cursor" aria-hidden="true"></i>';
        } elseif (in_array($this->nodeType, ['U.S. States', 'Drop Down', 'Date', 'Feet Inches'])) {
            return '<i class="fa fa-caret-square-o-down" aria-hidden="true"></i>';
        } elseif ($this->isSpreadTbl()) {
            return '<i class="fa fa-table" aria-hidden="true"></i>';
        } elseif ($this->nodeType == 'Instructions') {
            return '<i class="fa fa-info-circle" aria-hidden="true"></i>';
        } elseif ($this->nodeType == 'Instructions Raw') {
            return '<i class="fa fa-code" aria-hidden="true"></i>';
        } elseif ($this->nodeType == 'Hidden Field') {
            return '<i class="fa fa-eye-slash opac50" aria-hidden="true"></i>';
        } elseif ($this->nodeType == 'Date Picker') {
            return '<i class="fa fa-calendar" aria-hidden="true"></i>';
        } elseif (in_array($this->nodeType, ['Time', 'Date Time'])) {
            return '<i class="fa fa-clock-o" aria-hidden="true"></i>';
        } elseif ($this->nodeType == 'Slider') {
            return '<i class="fa fa-sliders" aria-hidden="true"></i>';
        } elseif ($this->nodeType == 'User Sign Up') {
            return '<i class="fa fa-user-plus" aria-hidden="true"></i>';
        } elseif ($this->nodeType == 'Uploads') {
            return '<i class="fa fa-cloud-upload" aria-hidden="true"></i>';
        } elseif (in_array($this->nodeType, ['Gallery Slider'])) {
            return '<i class="fa fa-picture-o" aria-hidden="true"></i>';
        } elseif ($this->isPageBlock()) {
            return '<i class="fa fa-square-o" aria-hidden="true"></i>';
        } elseif ($this->isLayout() || $this->nodeType == 'Data Print Columns') {
            return '<i class="fa fa-columns"></i>';
        } elseif ($this->isWidget()) {
            if ($this->nodeType == 'Incomplete Sess Check') {
                return '<i class="fa fa-user-o" aria-hidden="true"></i>';
            } elseif ($this->nodeType == 'Member Profile') {
                return '<i class="fa fa-user-circle-o" aria-hidden="true"></i>';
            } elseif (in_array($this->nodeType, ['Search', 'Search Results', 'Search Featured'])) {
                return '<i class="fa fa-search" aria-hidden="true"></i>';
            } elseif (in_array($this->nodeType, ['Plot Graph', 'Line Graph'])) {
                return '<i class="fa fa-area-chart" aria-hidden="true"></i>';
            } elseif ($this->nodeType == 'Bar Graph') {
                return '<i class="fa fa-bar-chart" aria-hidden="true"></i>';
            } elseif ($this->nodeType == 'Pie Chart') {
                return '<i class="fa fa-pie-chart" aria-hidden="true"></i>';
            } elseif ($this->nodeType == 'Map') {
                return '<i class="fa fa-map-o" aria-hidden="true"></i>';
            } elseif ($this->nodeType == 'MFA Dialogue') {
                return '<i class="fa fa-lock" aria-hidden="true"></i>';
            } else {
                return '<i class="fa fa-magic" aria-hidden="true"></i>';
            }
        } elseif ($this->isDataPrint()) {
            return '<i class="fa fa-list-alt" aria-hidden="true"></i>';
        } else { // if ($this->nodeType == 'Other/Custom')
            return '<i class="fa fa-hand-spock-o" aria-hidden="true"></i>';
        }
    }
    
    public function isPrintBasicTine()
    {
        return ($this->isDataManip() || $this->isLoopCycle() || $this->isLayout() || $this->isBranch());
    }
    
}
