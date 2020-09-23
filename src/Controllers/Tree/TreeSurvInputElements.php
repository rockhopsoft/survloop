<?php
/**
  * TreeSurvInputElements is a mid-level class using a standard branching tree, mostly for 
  * processing the input Survloop's surveys and pages.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use Illuminate\Http\Request;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvInputWidgets;

class TreeSurvInputElements extends TreeSurvInputWidgets
{
    public $nodeTypes = [
        'Radio', 'Checkbox', 'Drop Down', 'Text', 'Long Text', 'Text:Number', 
        'Slider', 'Email', 'Password', 'Date', 'Date Picker', 'Date Time', 'Time', 
        'Gender', 'Gender Not Sure', 'Feet Inches', 'U.S. States', 'Countries', 
        'Uploads', 'Spreadsheet Table', 'User Sign Up', 'Hidden Field', 
        'Spambot Honey Pot', 'Other/Custom' 
    ];
    
    public $nodeSpecialTypes = [
        'Instructions', 'Instructions Raw', 'Page', 'Branch Title', 'Loop Root', 
        'Loop Cycle', 'Loop Sort', 'Data Manip: New', 'Data Manip: Update', 
        'Data Manip: Wrap', 'Data Manip: Close Sess', 'Big Button', 'Search', 
        'Search Results', 'Search Featured', 'Member Profile Basics', 'Send Email', 
        'Admin Form', 'Record Full', 'Record Full Public', 'Record Previews', 
        'Incomplete Sess Check', 'Back Next Buttons', 'Data Print', 'Data Print Row', 
        'Data Print Block', 'Data Print Columns', 'Print Vert Progress', 'Plot Graph', 
        'Line Graph', 'Bar Graph', 'Pie Chart', 'Map', 'MFA Dialogue', 'Widget Custom', 
        'Page Block', 'Layout Row', 'Layout Column', 'Layout Sub-Response', 'Gallery Slider'
    ];
    
    protected $pageHasUpload    = [];
    protected $pageHasReqs      = '';
    protected $pageFldList      = [];
    protected $page1stVisib     = '';
    protected $hideKidNodes     = [];
    
    protected $nextBtnOverride  = '';
    protected $loopItemsCustBtn = '';
    
    protected $pageCoreRow      = [];
    protected $pageCoreFlds     = [];
    
    protected $tableDat         = [];
    
    protected function postNodeTweakNewVal($curr, $newVal)
    {
        if ($curr->nodeType == 'U.S. States' && $curr->isDropdownTagger()) {
            $GLOBALS["SL"]->loadStates();
            if (!is_array($newVal)) {
                $newVal = $GLOBALS["SL"]->states->getStateByInd($newVal);
            } elseif (sizeof($newVal) > 0) {
                foreach ($newVal as $i => $val) {
                    $newVal[$i] = $GLOBALS["SL"]->states->getStateByInd($val);
                }
            }
        }
        return $newVal;
    }
    
    protected function getRawFormDate($nIDtxt)
    {
        $nIDtxt = 'n' . $nIDtxt;
        if ($GLOBALS["SL"]->REQ->has($nIDtxt . 'fldMonth') 
            && trim($GLOBALS["SL"]->REQ->get($nIDtxt . 'fldMonth')) != ''
            && $GLOBALS["SL"]->REQ->has($nIDtxt . 'fldDay')
            && trim($GLOBALS["SL"]->REQ->get($nIDtxt . 'fldDay')) != ''
            && $GLOBALS["SL"]->REQ->has($nIDtxt . 'fldYear')
            && trim($GLOBALS["SL"]->REQ->get($nIDtxt . 'fldYear')) != ''
            && trim($GLOBALS["SL"]->REQ->get($nIDtxt . 'fldYear')) != '0000') {
            return trim($GLOBALS["SL"]->REQ->get($nIDtxt . 'fldYear'))
                . '-' . trim($GLOBALS["SL"]->REQ->get($nIDtxt . 'fldMonth'))
                . '-' . trim($GLOBALS["SL"]->REQ->get($nIDtxt . 'fldDay'));
        }
        return '';
    }
    
    protected function checkLoopRootInput($nID)
    {
        // then we're at the page's root, so let's check this once
        if ($GLOBALS["SL"]->REQ->has('delLoopItem')) {
            $delID = intVal($GLOBALS["SL"]->REQ->get('delLoopItem'));
            if ($delID > 0) {
                $loopTable = $GLOBALS["SL"]->closestLoop["obj"]->data_loop_table;
                $this->sessData->deleteDataItem($nID, $loopTable, $delID);
            }
        }
        return true;
    }
    
    protected function postNodePublicLoopSort($curr)
    {
        $list = '';
        $loop = str_replace('LoopItems::', '', $curr->nodeRow->node_response_set);
        $loopCycle = $this->sessData->getLoopRows($loop);
        if (sizeof($loopCycle) > 0) {
            foreach ($loopCycle as $i => $loopItem) {
                $list .= ',' . $loopItem->getKey();
            }
        }
        $logDesc = 'Sorting ' . $loop . ' Items';
        $this->sessData->logDataSave($curr->nID, $loop, -3, $logDesc, $list);
        $this->closePostNodePublic($curr);
        return '';
    }
    
    protected function postNodePublicLoopCycle($curr)
    {
        $ret = '';
        list($curr->tbl, $curr->fld, $newVal) = $this->nodeBranchInfo($curr->nID);
        $loop = str_replace('LoopItems::', '', $curr->nodeRow->node_response_set);
        $loopCycle = $this->sessData->getLoopRows($loop);
        if (sizeof($curr->tmpSubTier[1]) > 0 && sizeof($loopCycle) > 0) {
            $GLOBALS["SL"]->currCyc["cyc"][0] = $GLOBALS["SL"]->getLoopTable($loop);
            foreach ($loopCycle as $i => $loopItem) {
                $GLOBALS["SL"]->currCyc["cyc"][1] = 'cyc' . $i;
                $GLOBALS["SL"]->currCyc["cyc"][2] = $loopItem->getKey();
                $this->sessData->startTmpDataBranch($curr->tbl, $loopItem->getKey());
                $GLOBALS["SL"]->fakeSessLoopCycle($loop, $loopItem->getKey());
                foreach ($curr->tmpSubTier[1] as $child) {
                    if (!$this->allNodes[$child[0]]->isPage()) {
                        $ret .= $this->postNodePublic($child[0], $child);
                    }
                }
                $GLOBALS["SL"]->removeFakeSessLoopCycle($loop, $loopItem->getKey());
                $this->sessData->endTmpDataBranch($curr->tbl);
                $GLOBALS["SL"]->currCyc["cyc"][1] = '';
                $GLOBALS["SL"]->currCyc["cyc"][2] = -3;
            }
            $GLOBALS["SL"]->currCyc["cyc"][0] = '';
        }
        return $ret;
    }




}