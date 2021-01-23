<?php
/**
  * TreeSurvProgBar is a mid-level class using to report a user's progress through a survey.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.1.2
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use RockHopSoft\Survloop\Controllers\Tree\TreeSurvLoad;

class TreeSurvProgBar extends TreeSurvLoad
{
    public $majorSections         = [];
    public $minorSections         = [];
    public $currMajorSection      = 0;
    public $currMinorSection      = 0;
    
    protected $sessDataChangeLog  = [];
    protected $sessNodesDone      = [];
    protected $sessMajorsTouched  = [];
    protected $sessMinorsTouched  = [];
    
    public $navBottom             = '';
    public $nodeTreeProgressBar   = '';
    
    protected function rawOrderPercentTweak($nID, $rawPerc, $found = -3)
    {
        return $rawPerc;
    }
    
    protected function loadProgBarTweak()
    {
        return true;
    }
    
    protected function tweakProgBarJS()
    {
        return '';
    }
    
    public function loadProgBar()
    {
        $rawPerc = $this->rawOrderPercent($this->currNode());
        if (intVal($rawPerc) < 0) {
            $rawPerc = 0;
        }
        if (isset($this->allNodes[$this->currNode()]) 
            && $this->allNodes[$this->currNode()]->nodeType == 'Page' 
            && $this->allNodes[$this->currNode()]->nodeOpts%29 == 0) {
            $rawPerc = 100;
        }
        $this->currMajorSection = $this->getCurrMajorSection($this->currNode());
        if (!isset($this->minorSections[$this->currMajorSection]) 
            || empty($this->minorSections[$this->currMajorSection])) {
            $this->currMinorSection = 0;
        } else {
            $this->currMinorSection = $this->getCurrMinorSection(
                $this->currNode(), 
                $this->currMajorSection
            );
        }
        $this->loadProgBarTweak();
        if (sizeof($this->majorSections) > 0) {
            foreach ($this->majorSections as $maj => $majSect) {
                if (isset($this->minorSections[$maj])
                    && sizeof($this->minorSections[$maj]) > 0) {
                    foreach ($this->minorSections[$maj] as $min => $minSect) {
                        if (isset($minSect[0]) 
                            && isset($this->allNodes[$minSect[0]])) {
                            $this->allNodes[$minSect[0]]->fillNodeRow();
                        }
                    }
                }
            }
        }
        $this->createProgBarJs();
        $ret = '';
        $majTot = 0;
        foreach ($this->majorSections as $maj => $majSect) {
            if ($maj == $this->currMajorSection) {
                $GLOBALS['SL']->pageJAVA .= view(
                    'vendor.survloop.forms.inc-progress-bar-js-tweak', 
                    [
                        "maj"    => $maj, 
                        "status" => 'active' 
                    ]
                )->render();
            } elseif (in_array($maj, $this->sessMajorsTouched)) {
                $GLOBALS['SL']->pageJAVA .= view(
                    'vendor.survloop.forms.inc-progress-bar-js-tweak', 
                    [
                        "maj"    => $maj, 
                        "status" => 'completed'
                    ]
                )->render();
            }
            if ($majSect[2] == 'disabled') {
                $GLOBALS['SL']->pageJAVA .= 'treeMajorSectsDisabled[0]=' . $maj . ';' . "\n";
            } else {
                $majTot++;
            }
            if (sizeof($this->minorSections[$maj]) > 0) {
                foreach ($this->minorSections[$maj] as $min => $minSect) {
                    if ($maj == $this->currMajorSection && $min == $this->currMinorSection) {
                        $GLOBALS['SL']->pageJAVA .= view(
                            'vendor.survloop.forms.inc-progress-bar-js-tweak', 
                            [
                                "maj" => $maj, 
                                "min" => $min, 
                                "status" => 'active' 
                            ]
                        )->render();
                    } elseif (in_array($min, $this->sessMinorsTouched[$maj])) {
                        $GLOBALS['SL']->pageJAVA .= view(
                            'vendor.survloop.forms.inc-progress-bar-js-tweak', 
                            [
                                "maj"    => $maj, 
                                "min"    => $min, 
                                "status" => 'completed' 
                            ]
                        )->render();
                    }
                }
            }
        }
        if ($GLOBALS["SL"]->treeRow->tree_opts%61 == 0) { // survey progress line
            $currPerc = -3;
            if (isset($this->allNodes[$this->currNode()]) 
                && $this->allNodes[$this->currNode()]->nodeOpts%59 > 0) {
                $currPerc = intVal($rawPerc);
            }
            $GLOBALS['SL']->pageJAVA .= 'printHeadBar(' . $currPerc . ');' . "\n";
        }
        if (($GLOBALS["SL"]->treeRow->tree_opts%37 == 0 
            || $GLOBALS["SL"]->treeRow->tree_opts%59 == 0)
            && isset($this->majorSections[$this->currMajorSection][1]) > 0) {
            $GLOBALS["SL"]->pageAJAX .= '$(".snLabel").click(function() { '
                . '$("html, body").animate({ scrollTop: 0 }, "fast"); });' . "\n";
            $majorsOut = $minorsOut = $majorsWithMinors = [];
            foreach ($this->majorSections as $maj => $majSect) {
                if ($majSect[2] != 'disabled') {
                    $majorsOut[] = $this->majorSections[$maj];
                    $minorsOut[] = $this->minorSections[$maj];
                    if (isset($this->minorSections[$maj])
                        && sizeof($this->minorSections[$maj]) > 0) {
                        $majorsWithMinors[] = $maj;
                    }
                }
            }
            $ret .= view(
                'vendor.survloop.forms.inc-progress-bar', 
                [
                    "hasNavBot"         => ($GLOBALS["SL"]->treeRow->tree_opts%59 == 0),
                    "hasNavTop"         => ($GLOBALS["SL"]->treeRow->tree_opts%37 == 0),
                    "allNodes"          => $this->allNodes, 
                    "majorSections"     => $majorsOut, 
                    "minorSections"     => $minorsOut, 
                    "sessMajorsTouched" => $this->sessMajorsTouched, 
                    "sessMinorsTouched" => $this->sessMinorsTouched, 
                    "currMajorSection"  => $this->currMajorSection, 
                    "currMinorSection"  => $this->currMinorSection, 
                    "majorsWithMinors"  => $majorsWithMinors,
                    "majTot"            => $majTot,
                    "rawPerc"           => $rawPerc
                ]
            )->render();
        }
        $GLOBALS['SL']->pageJAVA .= $this->tweakProgBarJS();
        return $ret;
        //return false;
    }
    
    public function createProgBarJs()
    {
        if (!is_dir('../storage/app/sys')) {
            mkdir('../storage/app/sys');
        }
        $jsFileName = '../storage/app/sys/tree-' . $this->treeID . '.js';
        if (!file_exists($jsFileName) || $GLOBALS["SL"]->REQ->has('refresh')) {
            if (file_exists($jsFileName)) {
                unlink($jsFileName);
            }
            $jsOut = view(
                'vendor.survloop.js.inc-tree', 
                [
                    "treeID"        => $this->treeID,
                    "allNodes"      => $this->allNodes, 
                    "majorSections" => $this->majorSections, 
                    "minorSections" => $this->minorSections
                ]
            )->render();
            file_put_contents($jsFileName, $jsOut);
        }
        return true;
    }
    
    protected function getCurrMajorSection($nID = -3)
    {
        if ($nID <= 0) {
            $nID = $this->currNode();
        }
        $currSection = 0;
        if (sizeof($this->majorSections) > 0) {
            foreach ($this->majorSections as $s => $sect) {
                if ($sect[0] > 0 && isset($this->allNodes[$sect[0]]) && $this->hasNode($nID)) {
                    if ($this->allNodes[$nID]->checkBranch($this->allNodes[$sect[0]]->nodeTierPath)) {
                        $currSection = $s;
                    }
                }
            }
        }
        return $currSection;
    }
    
    protected function getCurrMinorSection($nID = -3, $majorSectInd = -3)
    {
        if ($nID <= 0) {
            $nID = $this->currNode();
        }
        if ($majorSectInd <= 0) {
            $majorSectInd = $this->getCurrMajorSection($nID);
        }
        $overrideSection = $this->overrideMinorSection($nID, $majorSectInd);
        if ($overrideSection >= 0) {
            return $overrideSection;
        }
        $currSection = 0;
        if (sizeof($this->minorSections) > 0 && sizeof($this->minorSections[$majorSectInd]) > 0) {
            foreach ($this->minorSections[$majorSectInd] as $s => $sect) {
                if ($sect[0] > 0 && isset($this->allNodes[$sect[0]]) && $this->hasNode($nID)) {
                    if ($this->allNodes[$nID]->checkBranch($this->allNodes[$sect[0]]->nodeTierPath)) {
                        $currSection = $s;
                    }
                }
            }
        }
        return $currSection;
    }
    
    protected function overrideMinorSection($nID = -3, $majorSectInd = -3)
    {
        return -1;
    }
    
    protected function getBranchName($branchID = -3)
    {
        if ($branchID > 0 && sizeof($this->branches) > 0) {
            foreach ($this->branches as $b) {
                if ($b["id"] == $branchID) {
                    return $b["name"];
                }
            }
        }
        return "";
    }
    
}