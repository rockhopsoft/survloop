<?php
/**
  * TreeNodeSurvVars preps for TreeNodeSurv to extend a 
  * standard branching tree's node for Survloop's needs.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use RockHopSoft\Survloop\Controllers\Tree\TreeNodeCore;

class TreeNodeSurvVars extends TreeNodeCore
{
    public $conds            = [];
    public $responses        = [];
    public $hasShowKids      = false;
    public $hasPageParent    = false;
    public $fldHasOther      = [];
    public $condKids         = [];
    public $showMoreNodes    = [];
    
    public $dataManips       = [];
    public $colors           = [];
    public $extraOpts        = [];
    
    public $primeOpts        = [
        "Required"         => 5, 
        "OneLineResponses" => 17, 
        "OneLiner"         => 11, 
        "RequiredInLine"   => 13
    ];

    public $nID              = 0;
    public $nSffx            = '';
    public $nIDtxt           = '';
    public $currVisib        = 1;
    public $sessData         = null;
    public $tbl              = '';
    public $fld              = '';
    public $itemID           = -1;
    public $itemInd          = -1;
    public $nodePrompt       = '';
    public $nodePromptText   = '';
    public $nodePromptNotes  = '';
    public $nodePromptAfter  = '';
    public $onKeyUp          = '';
    public $onChange         = '';
    public $charLimit        = '';
    public $isOneLiner       = '';
    public $isOneLinerFld    = '';
    public $xtraClass        = '';
    public $hasParManip      = false;
    public $dateStr          = '00/00/0000';
    public $timeStr          = '00:00:00';
    public $dynaMonthFld     = '';

    
    // Tree Nodes are assigned an optional property when ( SLNode->node_opts%OPT_PRIME == 0 )
    // (Coding style originally adopted for native cross-language compatibility.
    // Yes, the plan is to swap strategies.)
   
    // Node Options
    public const OPT_DROPTAGGER = 53;  
    // This node's dropdown stores like a checkbox, associating tags

    // Node Visual Layout Options
    public const OPT_SKINNY     = 67;
    // This node's contents are wrapped in the skinny page width 

    public const OPT_JUMBOTRON  = 37;
    // Wrap the contents of this node inside bootstrap's Jumbotron

    public const OPT_BLOCKBACKG = 71;
    // Node has content block background and color properties

    public const OPT_CARDWRAP   = 89;
    // Wrap the contents of this node inside a Card

    public const OPT_DEFERLOAD  = 97;
    // Defer loading the contents of this load until after the rest of the page

    public const OPT_NONODECACH = 103;
    // The deferred loading of this node's contents should not pull from a cache
    
    
    // Node Form Field Layout Options
    public const OPT_CUSTOMLAY  = 2;   // Node uses some layout overrides instead of default
    public const OPT_REQUIRELIN = 13;  // "*Required" must appear on its own line
    public const OPT_RESPOCOLS  = 61;  // Node responses layed out in columns
    
    // Node Form Field Saving Options
    public const OPT_TBLSAVEROW = 73;  // Table leaves existing rows' records upon saving (don't delete empties)
    
    // Node Interaction Options
    public const OPT_WORDCOUNT  = 31;  // Open ended field should show a live word count
    public const OPT_WORDLIMIT  = 47;  // Force limit on word count
    public const OPT_ECHOSTROKE = 41;  // Echo response edits to specific div, every keystroke
    public const OPT_BTNTOGGLE  = 43;  // Toggle child nodes if node button is clicked
    public const OPT_HIDESELECT = 79;  // Hide unselected options after radio button selected
    public const OPT_REVEALINFO = 83;  // Reveal node sub-notes upon clicking a little info icon
    public const OPT_MONTHCALC  = 101; // Provides a calculator to total 12 months
    
    // Page Node Options
    public const OPT_EXITPAGE   = 29;  // Node is an Exit Page, without a next button 
    public const OPT_HIDEPROG   = 59;  // Hide progress bar on this page
    
    // For XML Tree Nodes
    public const OPT_XMLPARENTS = 5;   // Include members with parent, without table wrap
    public const OPT_XMLMIN     = 7;   // Min 1 Record
    public const OPT_XMLMAX     = 11;  // Max 1 Record
    
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
    
    public function clearResponses()
    {
        $this->responses = [];
        return true;
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
//echo 'isStepLoop() nID: ' . $this->nodeID . ', type: ' . $this->nodeType . ', branch: ' . $this->dataBranch . '<pre>'; print_r($GLOBALS["SL"]->dataLoops[$this->nodeRow->node_default]); echo '</pre>'; exit;
        if ($this->isLoopRoot()) {
            if ($GLOBALS["SL"]->isStepLoop($this->dataBranch)) {
                return true;
            } // hmmm..
            if (isset($this->nodeRow->node_default) 
                && $GLOBALS["SL"]->isStepLoop($this->nodeRow->node_default)) {
                return true;
            }
        }
        return false;
    }
    
    public function isDataManip()
    {
        return (substr($this->nodeType, 0, 10) == 'Data Manip');
    }
    
    public function isDataPrint()
    {
        $types = [
            'Data Print', 
            'Data Print Row', 
            'Data Print Block', 
            'Data Print Columns',
            'Print Vert Progress'
        ];
        return in_array($this->nodeType, $types);
    }
    
    public function isSpreadTbl()
    {
        return ($this->nodeType == 'Spreadsheet Table');
    }
    
    public function isDynaMonthTbl()
    {
        if ($this->isSpreadTbl() && strpos($this->responseSet, 'Months::') == 0) {
            if (trim($this->dynaMonthFld) == '') {
                $this->dynaMonthFld = str_replace('Months::', '', $this->responseSet);
            }
            return true;
        }
        return false;
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
        $types = [
            'Radio', 
            'Checkbox', 
            'Drop Down', 
            'Other/Custom'
        ];
        return in_array($this->nodeType, $types);
    }
    
    public function isSpecial()
    {
        return ($this->isNonLoopSpecial() || $this->isLoopRoot() || $this->isLoopCycle());
    }
    
    public function isNonLoopSpecial()
    {
        return ($this->isInstruct() 
            || $this->isInstructRaw() 
            || $this->isPage()  
            || $this->isBranch() 
            || $this->isLoopSort() 
            || $this->isDataManip() 
            || $this->isWidget() 
            || $this->isBigButt() 
            || $this->isLayout() 
            || $this->isDataPrint() 
            || in_array($this->nodeType, ['Send Email']));
    }
    
    public function isWidget()
    {
        $types = [
            'Search', 
            'Search Results', 
            'Search Featured', 
            'Member Profile Basics', 
            'Record Full', 
            'Record Full Public', 
            'Record Previews', 
            'Incomplete Sess Check', 
            'Back Next Buttons', 
            'Widget Custom', 
            'Admin Form', 
            'MFA Dialogue'
        ];
        return ($this->isGraph() || in_array($this->nodeType, $types));
    }
    
    public function isGraph()
    {
        $types = [
            'Plot Graph', 
            'Line Graph', 
            'Bar Graph', 
            'Pie Chart', 
            'Map'
        ];
        return in_array($this->nodeType, $types);
    }
    
    public function isLayout()
    {
        $types = [
            'Page Block', 
            'Layout Row', 
            'Layout Column', 
            'Layout Sub-Response',
            'Gallery Slider'
        ];
        return (in_array($this->nodeType, $types));
    }
    
    public function isPageBlock()
    {
        //if ($GLOBALS["SL"]->treeRow->tree_type == 'Page' && $this->parentID == $GLOBALS["SL"]->treeRow->tree_root) {
        if ($this->isLayout() || $this->isInstructAny()) {
            $this->loadPageBlockColors();
            return true;
        }
        return false;
    }
    
    public function isPageBlockSkinny()
    {
        return ($this->isPageBlock() && $this->nodeRow->node_opts%67 == 0);
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
        return (in_array($this->nodeType, ['Drop Down', 'U.S. States']) 
            && $this->nodeOpts%53 == 0);
    }
    
    public function isHnyPot()
    {
        return ($this->nodeType == 'Spambot Honey Pot');
    }
    
    public function isPrintBasicTine()
    {
        return ($this->isDataManip() 
            || $this->isLoopCycle() 
            || $this->isLayout() 
            || $this->isBranch());
    }
    
    public function getIcon()
    {
        if ($this->isBranch()) {
            return '<i class="fa fa-share-alt" title="Branch Title"></i>';
        } elseif ($this->isLoopRoot()) {
            return '<i class="fa fa-refresh" title="'
                . 'Start of a New Page, Root of a Data Loop"></i>';
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
            return '<i class="fa fa-check-square-o" aria-hidden="true" alt="Checkboxes"></i>';
        } elseif ($this->nodeType == 'Radio') {
            return '<i class="fa fa-dot-circle-o" aria-hidden="true" title="Radio Buttons"></i>';
        } elseif (in_array($this->nodeType, ['Email', 'Gender', 'Gender Not Sure', 'Long Text', 
            'Text', 'Text:Number'])) {
            return '<i class="fa fa-i-cursor" aria-hidden="true" title="Text Field"></i>';
        } elseif (in_array($this->nodeType, ['U.S. States', 'Drop Down', 'Date', 'Feet Inches'])) {
            return '<i class="fa fa-caret-square-o-down" aria-hidden="true" title="Drop Down"></i>';
        } elseif ($this->isSpreadTbl()) {
            return '<i class="fa fa-table" aria-hidden="true" title="Spreadsheet Table"></i>';
        } elseif ($this->nodeType == 'Instructions') {
            return '<i class="fa fa-info-circle" aria-hidden="true" title="Instructions"></i>';
        } elseif ($this->nodeType == 'Instructions Raw') {
            return '<i class="fa fa-code" aria-hidden="true" title="Instructions (HTML)"></i>';
        } elseif ($this->nodeType == 'Hidden Field') {
            return '<i class="fa fa-eye-slash opac50" aria-hidden="true" title="Hidden Field"></i>';
        } elseif ($this->nodeType == 'Date Picker') {
            return '<i class="fa fa-calendar" aria-hidden="true" title="Date Picker"></i>';
        } elseif (in_array($this->nodeType, ['Time', 'Date Time'])) {
            return '<i class="fa fa-clock-o" aria-hidden="true" title="Date and/or Time"></i>';
        } elseif ($this->nodeType == 'Slider') {
            return '<i class="fa fa-sliders" aria-hidden="true" title="Slider"></i>';
        } elseif ($this->nodeType == 'User Sign Up') {
            return '<i class="fa fa-user-plus" aria-hidden="true" title="User Sign Up"></i>';
        } elseif ($this->nodeType == 'Uploads') {
            return '<i class="fa fa-cloud-upload" aria-hidden="true" title="Uploads"></i>';
        } elseif (in_array($this->nodeType, ['Gallery Slider'])) {
            return '<i class="fa fa-picture-o" aria-hidden="true" title="Gallery Slider"></i>';
        } elseif ($this->isPageBlock()) {
            return '<i class="fa fa-square-o" aria-hidden="true" title="Page Block"></i>';
        } elseif ($this->isDataPrint()) {
            return '<i class="fa fa-list-alt" aria-hidden="true" title="Data Print"></i>';
        } elseif ($this->isLayout() || $this->nodeType == 'Data Print Columns') {
            return '<i class="fa fa-columns" title="Column"></i>';
        } elseif ($this->isWidget()) {
            if ($this->nodeType == 'Incomplete Sess Check') {
                return '<i class="fa fa-user-o" aria-hidden="true" title="Incomplete Session Check"></i>';
            } elseif ($this->nodeType == 'Member Profile') {
                return '<i class="fa fa-user-circle-o" aria-hidden="true" title="Member Profile"></i>';
            } elseif (in_array($this->nodeType, ['Search', 'Search Results', 'Search Featured'])) {
                return '<i class="fa fa-search" aria-hidden="true" title="Search"></i>';
            } elseif (in_array($this->nodeType, ['Plot Graph', 'Line Graph'])) {
                return '<i class="fa fa-area-chart" aria-hidden="true" title="Graph"></i>';
            } elseif ($this->nodeType == 'Bar Graph') {
                return '<i class="fa fa-bar-chart" aria-hidden="true" title="Bar Graph"></i>';
            } elseif ($this->nodeType == 'Pie Chart') {
                return '<i class="fa fa-pie-chart" aria-hidden="true" title="Pie Chart"></i>';
            } elseif ($this->nodeType == 'Map') {
                return '<i class="fa fa-map-o" aria-hidden="true" title="Map"></i>';
            } elseif ($this->nodeType == 'MFA Dialogue') {
                return '<i class="fa fa-lock" aria-hidden="true" title="MFA Dialogue"></i>';
            } else {
                return '<i class="fa fa-magic" aria-hidden="true" title="Widget"></i>';
            }
        } else { // if ($this->nodeType == 'Other/Custom')
            return '<i class="fa fa-magic" aria-hidden="true" title="Other/Custom"></i>';
        }
    }
    
    public function loadPageBlockColors()
    {
        if (isset($this->nodeRow->node_default) 
            && trim($this->nodeRow->node_default) != '' 
            && empty($this->colors)) {
            $colors = explode(';;', $this->nodeRow->node_default);
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


}