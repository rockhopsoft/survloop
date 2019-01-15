<?php
/**
  * SurvLoop is a core class for routing system access, particularly for loading a
  * client installation's customized extension of TreeSurvForm instead of the default.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use Auth;
use Illuminate\Http\Request;
use SurvLoop\Controllers\PageLoadUtils;

class SurvCustLoop extends PageLoadUtils
{
    // This is where the client installation's extension of TreeSurvForm is loaded
    public $custLoop       = null;
    
    protected function isAdmin()
    {
        return (Auth::user() && Auth::user()->hasRole('administrator'));
    }
    
    public function loadLoop(Request $request, $skipSessLoad = false)
    {
        $this->loadAbbr();
        $class = "SurvLoop\\Controllers\\TreeSurvForm";
        if ($this->custAbbr != 'SurvLoop') {
            $custClass = $this->custAbbr . "\\Controllers\\" . $this->custAbbr . "";
            if (class_exists($custClass)) {
                $class = $custClass;
            }
        }
        eval("\$this->custLoop = new " . $class . "(\$request, -3, " . $this->dbID . ", " . $this->treeID . ", " 
            . (($skipSessLoad) ? "true" : "false") . ");");
        return true;
    }
}