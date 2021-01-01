<?php
/**
  * SurvCustLoop is a core class for routing system access, particularly for loading a
  * client installation's customized extension of TreeSurvForm instead of the default.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.19
  */
namespace RockHopSoft\Survloop\Controllers;

use Auth;
use Illuminate\Http\Request;
use RockHopSoft\Survloop\Controllers\PageLoadUtils;

class SurvCustLoop extends PageLoadUtils
{
    // This is where the client installation's extension of TreeSurvForm is loaded
    public $custLoop = null;
    
    protected function isAdmin()
    {
        return (Auth::user() && Auth::user()->hasRole('administrator'));
    }
    
    public function loadLoop(Request $request, $skipSessLoad = false)
    {
        $this->loadAbbr();
        $class = "RockHopSoft\\Survloop\\Controllers\\Tree\\TreeSurvForm";
        $custLoopFile = '../vendor/' . $this->custPckg 
            . '/src/Controllers/' . $this->custAbbr . '.php';
        if ($this->custAbbr != 'Survloop'
            && file_exists($custLoopFile)) {
            $custClass = $this->custVend . "\\" . $this->custAbbr
                . "\\Controllers\\" . $this->custAbbr . "";
            if (class_exists($custClass)) {
                $class = $custClass;
            }
        }
        eval("\$this->custLoop = new " . $class . "("
            . "\$request, "
            . "-3, "
            . $this->dbID . ", "
            . $this->treeID . ", "
            . (($skipSessLoad) ? "true" : "false") 
            . ");"
        );
        return true;
    }
}
