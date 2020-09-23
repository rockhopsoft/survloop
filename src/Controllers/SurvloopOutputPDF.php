<?php
/**
  * SurvloopOutputPDF helps exports to PDF.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.27
  */
namespace RockHopSoft\Survloop\Controllers;

class SurvloopOutputPDF
{
    public $fileStore   = '';
    public $fileDeliver = '';

    public $dpi         = 100; 
    public $quality     = 'screen'; 
    // 'screen' is smallest, "ebook" is medium, "prepress" is HQ
    // "printer" might be worth trying

}
