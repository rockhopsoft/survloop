<?php
/**
  * TreeSurvAB is a mid-level class which handles the AB test variants of a CoreTree.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.1.2
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use RockHopSoft\Survloop\Controllers\Tree\TreeSurvLoad;

class TreeSurvAB extends TreeSurvLoad
{
    //public $treeVersion = ''; // 'v0.1'
    public $abTest = [];
    
    protected function addTestAB()
    {
        $this->abTest[] = 'A';
        return true;
    }
    
    
    
    
    
}