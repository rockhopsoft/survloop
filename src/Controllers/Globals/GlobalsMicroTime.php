<?php
/**
  * GlobalsMicroTime is a standalone global class to log a page's load times.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */
namespace RockHopSoft\Survloop\Controllers\Globals;

class GlobalsMicroTime
{
    protected $start = 0;
    protected $log   = [];

    public function __construct()
    {
        $this->start = microtime(true);
    }

    public function microLog($label = 'Start Page Load')
    {
        $elapsed = microtime(true)-$this->start;
        $this->log[] = [ $label, $elapsed ];
    }

    public function printMicroLog()
    {
        return view(
            'vendor.survloop.elements.inc-var-dump-microtime', 
            [ "log" => $this->log ]
        )->render();
    }

}