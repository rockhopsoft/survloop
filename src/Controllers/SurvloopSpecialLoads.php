<?php
/**
  * SurvloopSpecialLoads handles the system
  * routes for less generalized needs.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since  v0.2.27
  */
namespace RockHopSoft\Survloop\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\SLSess;
use App\Models\SLTree;
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use RockHopSoft\Survloop\Controllers\DeliverImage;
use RockHopSoft\Survloop\Controllers\PageLoadUtils;

class SurvloopSpecialLoads extends PageLoadUtils
{

    /**
     * Process a confirmation token emailed to a user, and clicked.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  string  $token
     * @param  string  $tokenB
     * @return string
     */
    public function processEmailConfirmToken(Request $request, $token = '', $tokenB = '')
    {
        $this->loadLoop($request);
        return $this->custLoop->processEmailConfirmToken($request, $token, $tokenB);
    }

    public function testRun(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->testRun($request);
    }

    public function ajaxChecks(Request $request, $type = '')
    {
        $this->loadLoop($request);
        $GLOBALS["SL"]->v["cacheKey"] = $this->topGenCacheKey('ajax');
        return $this->custLoop->ajaxChecks($request, $type);
    }

    public function sortLoop(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->sortLoop($request);
    }

    public function showProfile(Request $request, $uname = '')
    {
        $redir = $this->chkLoginRedir($request);
        if ($redir != '') {
            return redirect($redir);
        }
        $trees = SLTree::where('tree_type', 'Page')
            //->whereRaw("tree_opts%" . Globals::TREEOPT_PROFILE . " = 0")
            ->orderBy('tree_id', 'asc')
            ->get();
        if ($trees->isNotEmpty()) {
            foreach ($trees as $tree) {
                if (isset($tree->tree_opts)
                    && $tree->tree_opts%Globals::TREEOPT_PROFILE == 0) {
                    $this->syncDataTrees(
                        $request,
                        $tree->tree_database,
                        $tree->tree_id
                    );
                    $this->loadLoop($request);
                    $this->custLoop->setCurrUserProfile($uname);
                    return $this->custLoop->index($request);
                }
            }
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }

    public function showMyProfile(Request $request)
    {
        $redir = $this->chkLoginRedir($request);
        if ($redir != '') {
            return redirect($redir);
        }
        $this->loadDomain();
        $this->checkHttpsDomain($request);
        if (Auth::user() && isset(Auth::user()->name)) {
            return $this->showProfile($request);
            //return redirect($this->domainPath
            //   . '/profile/' . urlencode(Auth::user()->name));
        }
        return redirect($this->domainPath . '/');
    }

    public function editProfile(Request $request, $uname = '')
    {
        $this->syncDataTrees($request, 1, 1);
        $this->loadLoop($request);
        $this->custLoop->setCurrUserProfile($uname);
        return $this->custLoop->editProfileBasics($request);
    }

    public function profileStats(Request $request, $uname = '')
    {
        $this->syncDataTrees($request, 1, 1);
        $this->loadLoop($request);
        $this->custLoop->setCurrUserProfile($uname);
        return $this->custLoop->printProfileStats($request);
    }

    public function restartSess(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->restartSess($request);
    }

    public function sessDump(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->sessDump();
    }

    public function switchSess(Request $request, $treeID, $cid)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request);
        return $this->custLoop->switchSess($request, $cid);
    }

    public function delSess(Request $request, $treeID, $cid)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request);
        return $this->custLoop->delSess($request, $cid);
    }

    public function cpySess(Request $request, $treeID, $cid)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request);
        return $this->custLoop->cpySess($request, $cid);
    }

    public function retrieveUpload(Request $request, $treeSlug = '', $cid = -3, $upID = '', $refresh = false)
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->retrieveUpload($request, $cid, $upID, $refresh);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }

    public function retrieveUploadFresh(Request $request, $rand = '', $treeSlug = '', $cid = -3, $upID = '')
    {
        return $this->retrieveUpload($request, $treeSlug, $cid, $upID, true);
    }

    public function getUploadFile(Request $request, $abbr, $file)
    {
        $filename = '../storage/app/up/' . $abbr . '/' . $file;
        $img = new DeliverImage($filename, 0, $request->has('refresh'));
        return $img->delivery();
    }

    public function checkImgResizeAll(Request $request, $treeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->checkImgResizeAll();
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }

    public function chkEmail(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->chkEmail($request);
    }


    public function freshUser(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->freshUser($request);
    }

    public function freshDB(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->freshDB($request);
    }

    // Survloop Widgets

    public function ajaxMultiRecordCheck(Request $request, $treeID = 1)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->multiRecordCheck(true);
    }

    public function ajaxRecordFulls(Request $request, $treeID = 1)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->printReports($request);
    }

    public function ajaxRecordPreviews(Request $request, $treeID = 1)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->printReports($request, false);
    }

    public function ajaxEmojiTag(Request $request, $treeID = 1, $recID = -3, $defID = -3)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->ajaxEmojiTag($request, $recID, $defID);
    }

    public function ajaxGraph(Request $request, $gType = '', $treeID = 1, $nID = -3)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->ajaxGraph($request, $gType, $nID);
    }

    public function searchPrep(Request $request, $treeID = 1)
    {
        $this->loadLoop($request, true);
        $this->custLoop->searchPrep($request, $treeID);
        return true;
    }

    public function searchBar(Request $request, $treeID = 1)
    {
        $this->loadTreeByID($request, $treeID);
        $this->searchPrep($request, $treeID);
        return $this->custLoop->searcher->searchBar();
    }

    public function searchResults(Request $request, $treeID = 1, $ajax = 0)
    {
        $this->loadTreeByID($request, $treeID, true);
        $this->searchPrep($request, $treeID);
        $this->custLoop->searcher->searchCacheName();
    //$this->currLoop->survloopInit($request, $this->currLoop->searchCacheName());
    // [ check for cache ]
        $this->custLoop->searcher->prepSearchResults($request);
        if (sizeof($this->custLoop->searcher->searchResults) > 0) {
            foreach ($this->custLoop->searcher->searchResults as $i => $rec) {
                if (trim($rec[2]) == '') {
                    $this->custLoop->sessData->loadCore(
                        $GLOBALS["SL"]->coreTbl,
                        $rec[0]
                    );
                    $this->custLoop->searcher->searchResults[$i][2] = '<div class="reportPreview">'
                        . $this->custLoop->printPreviewReport() . '</div>';
                    if (isset($this->custLoop->sessData->dataSets[$GLOBALS["SL"]->coreTbl])) {
                        $setRecs = $this->custLoop->sessData->dataSets[$GLOBALS["SL"]->coreTbl];
                        if (sizeof($setRecs) > 0 && isset($setRecs[0]->created_at)) {
                            $this->custLoop->searcher->searchResults[$i][1]
                                += strtotime($setRecs[0]->created_at)/1000000000000;
                        }
                    }
                }
            }
        }
        return $this->custLoop->searcher->searchResults($request, $ajax);
    }

    public function searchResultsAjax(Request $request, $treeID = 1)
    {
        return $this->searchResults($request, $treeID, 1);
    }

    public function widgetCust(Request $request, $treeID = 1, $nID = -3)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->widgetCust($request, $nID);
    }

    public function getSetFlds(Request $request, $treeID = 1, $rSet = '')
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->getSetFlds($request, $rSet);
    }

    public function jsLoadMenu(Request $request)
    {
        $username = '';
        if (Auth::user() && isset(Auth::user()->id)) {
            $username = Auth::user()->name;
            if (strpos($username, 'Session#') !== false
                || strpos($username, 'User ') !== false) {
                $atPos = strpos(Auth::user()->email, '@');
                $username = substr(Auth::user()->email, 0, $atPos);
            }
            if (strlen($username) > 25) {
                $username = substr($username, 0, 25) . '...';
            }
        }
        $previousUrl = '';
        if ($request->has('nd') && trim($request->get('nd')) != '') {
            $previousUrl .= '&nd=' . urlencode(trim($request->get('nd')));
        }
        if ($request->has('currPage') && trim($request->get('currPage')) != '') {
            $previousUrl .= '&previousUrl=' . urlencode(trim($request->get('currPage')));
        }
        if ($previousUrl != '') {
            $previousUrl = '?' . substr($previousUrl, 1);
        }




        $this->loadCustomGlobals();
        // inject more from $GLOBALS["CUST"] ...





        return view(
            'vendor.survloop.js.inc-load-menu',
            [
                "username"       => $username,
                "previousUrl"    => $previousUrl,
                "userLoadTweaks" => $this->jsLoadMenuTweaks()
            ]
        );
    }

    private function jsLoadMenuTweaks()
    {
        $userLoadTweaks = null;
        $this->loadAbbr();
        if ($this->custAbbr != 'Survloop') {
            $file = '../vendor/' . $this->custPckg
                . '/src/Controllers/' . $this->custAbbr . 'UserLoad.php';
            $class = $this->custVend . "\\" . $this->custAbbr
                . "\\Controllers\\" . $this->custAbbr . "UserLoad";
            if (file_exists($file) && class_exists($class)) {
                eval("\$userLoadTweaks = new " . $class . ";");
            }
        }
        return $userLoadTweaks;
    }

    public function timeOut(Request $request)
    {
        return view(
            'auth.dialog-check-form-sess',
            [ "req" => $request ]
        );
    }

    public function spinnerUrl(Request $request)
    {
        $this->syncDataTrees($request, 1, 1);
        return $GLOBALS["SL"]->spinner();
    }

    public function getJsonSurvStats(Request $request)
    {
        $this->syncDataTrees($request);
        $this->loadLoop($request);
        header('Content-Type: application/json');
        $pkg = '';
        if ($request->has('pkg')) {
            $pkg = trim($request->get('pkg'));
        }
        $stats = $GLOBALS["SL"]->getJsonSurvStats($pkg);
        $stats["survey1_complete"] = sizeof(
            $this->custLoop->getAllPublicCoreIDs()
        );
        echo json_encode($stats);
        exit;
    }

}
