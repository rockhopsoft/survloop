<?php
/**
  * SurvloopUserLoad is a class which inserts behavior within
  * Survloop's loading of a user's top-right corner of the UX.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since  v0.2.27
  */
namespace RockHopSoft\Survloop\Controllers;

class SurvloopUserLoad
{
    public $tweaks = [];

    public function addNavMenuTweak($posA, $posB, $posC, $link = '')
    {
        $this->tweaks[] = new SurvloopUserLoadTweak($posA, $posB, $posC, $link);
    }

}

class SurvloopUserLoadTweak
{
    public $posA = 1;
    public $posB = 1;
    public $posC = 1;
    public $link = '';

    public function __construct($posA, $posB, $posC, $link = '')
    {
        $this->posA = $posA;
        $this->posB = $posB;
        $this->posC = $posC;
        $this->link = $link;
    }

}