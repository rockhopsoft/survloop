<?php
/**
  * SurvloopControllerUtils holds helper functions for the primary base class for Survloop,
  * housing logging functions.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.18
  */
namespace RockHopSoft\Survloop\Controllers\Admin;

use DB;
use App\Models\SLNodeSavesPage;
use App\Models\SLSess;
use App\Models\SLSessSite;
use App\Models\SLTree;
use App\Models\SLUsersActivity;

class SurvLogAnalysis
{
    public $logs   = [];
    public $groups = [];

    private function logLoad($log)
    {
        $file = '../storage/app/log/' . $log . '.html';
        if ($GLOBALS["SL"]->isStaffOrAdmin() && file_exists($file)) {
            return file_get_contents($file);
        }
        return '';
    }

    public function logPreview($log)
    {
        $ret = '';
        $all = $this->logLoad($log);
        if (trim($all) != '' && strpos($all, '</p>') !== false) {
            $logs = $GLOBALS["SL"]->mexplode('</p>', $all);
            for ($i = 0; ($i < 100 && $i < sizeof($logs)); $i++) {
                $ret .= $logs[$i] . '</p><div class="p10"></div>';
            }
        }
        return $ret;
    }

    public function logPreviewCore($log, $coreID = 0, $coreTbl = '')
    {
        if ($coreTbl == '') {
            $coreTbl = $GLOBALS["SL"]->coreTbl;
        }
        $match = $coreTbl . '#' . $coreID;
        $ret = '';
        $all = $this->logLoad($log);
        if (trim($all) != '' && strpos($all, '</p>') !== false) {
            $logs = $GLOBALS["SL"]->mexplode('</p>', $all);
            for ($i = 0; $i < sizeof($logs); $i++) {
                if (strpos($logs[$i], $match) !== false) {
                    $ret .= $logs[$i] . '</p><div class="p5"></div>';
                }
            }
        }
        return $ret;
    }

    public function logPreviewUser($log, $userID = 0)
    {
        $match = '<b>U#' . $userID . '</b>';
        $ret = '';
        $all = $this->logLoad($log);
        if (trim($all) != '' && strpos($all, '</p>') !== false) {
            $logs = $GLOBALS["SL"]->mexplode('</p>', $all);
            for ($i = 0; $i < sizeof($logs); $i++) {
                if (strpos($logs[$i], $match) !== false) {
                    $ret .= $logs[$i] . '</p><div class="p5"></div>';
                }
            }
        }
        return $ret;
    }

    public function activityPreviewUser($userID = 0)
    {
        $ret = '';
        $chk = SLUsersActivity::where('user_act_user', $userID)
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $log) {
                $time = strtotime($log->created_at);
                $ret .= '<p>' . $GLOBALS["SL"]->printTimeZoneShiftStamp($time)
                    . ' ' . $log->user_act_curr_page . '</p>';
                $this->logs[] = new ActivityLog($time, $log->user_act_curr_page);
            }
        }
        $this->activityPreviewLogsSession($userID);
        usort($this->logs, function (ActivityLog $a, ActivityLog $b) {
            return -($a->time <=> $b->time);
        });
        $logInds = [];
        for ($i = 0; $i < (sizeof($this->logs)-1); $i++) {
            $time = $this->logs[$i]->time;
            $this->logs[$i]->prevGap = $time-$this->logs[$i+1]->time;
            $logInds[] = $i;
            if ($this->logs[$i]->prevGap > (60*45)) { // longer than 45 min
                $timeStart = 10000000000;
                $timeEnd = 0;
                if (sizeof($logInds) > 0) {
                    foreach ($logInds as $ind) {
                        if ($timeStart > $this->logs[$ind]->time) {
                            $timeStart = $this->logs[$ind]->time;
                        }
                        if ($timeEnd < $this->logs[$ind]->time) {
                            $timeEnd = $this->logs[$ind]->time;
                        }
                    }
                }
                $this->groups[] = new ActivityLogGroup($timeStart, $timeEnd, $logInds);
                $logInds = [];
            }
        }
        return $ret;
    }

    private function activityPreviewLogsSession($userID = 0)
    {
        $chk = SLSessSite::where('site_sess_user_id', $userID)
            ->orderBy('created_at', 'desc')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $log) {
                $time = strtotime($log->created_at);
                $note = 'Site Session ' . $log->site_sess_browser;
                $this->logs[] = new ActivityLog($time, $note);
            }
        }
        $sessIDs = [];
        $chk = SLSess::where('sess_user_id', $userID)
            ->orderBy('created_at', 'desc')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $log) {
                $sessIDs[] = $log->sess_id;
                $time = strtotime($log->created_at);
                $note = 'Sess Tree ' . $log->sess_tree . ', Core ' . $log->sess_core_id;
                $this->logs[] = new ActivityLog($time, $note);
            }
        }
        $chk = DB::table('sl_node_saves_page')
            ->join('sl_node', 'sl_node_saves_page.page_save_node', '=', 'sl_node.node_id')
            ->join('sl_sess', 'sl_node_saves_page.page_save_session', '=', 'sl_sess.sess_id')
            ->whereIn('sl_node_saves_page.page_save_session', $sessIDs)
            ->orderBy('sl_node_saves_page.created_at', 'desc')
            ->select('sl_node.node_tree', 'sl_sess.sess_core_id',
                'sl_node_saves_page.created_at', 'sl_node_saves_page.page_save_node')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $log) {
                $time = strtotime($log->created_at);
                $note = 'Survey Tree ' . $log->node_tree . ', Core '
                    . $log->sess_core_id . ', Page Node ' . $log->page_save_node;
                $this->logs[] = new ActivityLog($time, $note);
            }
        }
        $chk = DB::table('sl_node_saves')
            ->join('sl_node', 'sl_node_saves.node_save_node', '=', 'sl_node.node_id')
            ->join('sl_sess', 'sl_node_saves.node_save_session', '=', 'sl_sess.sess_id')
            ->whereIn('sl_node_saves.node_save_session', $sessIDs)
            ->orderBy('sl_node_saves.created_at', 'desc')
            ->select('sl_node.node_tree', 'sl_sess.sess_core_id',
                'sl_node_saves.created_at', 'sl_node_saves.node_save_node')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $log) {
                $time = strtotime($log->created_at);
                $note = 'Survey Tree ' . $log->node_tree . ', Core '
                    . $log->sess_core_id . ', Node Save ' . $log->node_save_node;
                $this->logs[] = new ActivityLog($time, $note);
            }
        }
    }

}


class ActivityLog
{
    public $time    = 0;
    public $prevGap = 0;
    public $url     = '';

    public function __construct($time, $url)
    {
        $this->time = $time;
        $this->url  = $url;
    }
}

class ActivityLogGroup
{
    public $timeStart = 0;
    public $timeEnd   = 0;
    public $logInds   = [];

    public function __construct($timeStart, $timeEnd, $logInds)
    {
        $this->timeStart = $timeStart;
        $this->timeEnd   = $timeEnd;
        $this->logInds   = $logInds;
    }

    public function printDuration()
    {
        $diff = ($this->timeEnd-$this->timeStart/(60*60));
        return $GLOBALS["SL"]->sigFigs($diff) . ' hr';
    }
}