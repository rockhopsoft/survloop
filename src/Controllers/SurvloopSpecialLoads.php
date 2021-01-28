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
use RockHopSoft\Survloop\Controllers\SurvCustLoop;

class SurvloopSpecialLoads extends SurvCustLoop
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
        $this->loadDomain();
        $this->checkHttpsDomain($request);
        if (Auth::user() && isset(Auth::user()->name)) {
            return $this->showProfile($request);
            //return redirect($this->domainPath 
            //   . '/profile/' . urlencode(Auth::user()->name));
        }
        return redirect($this->domainPath . '/');
    }
    
    public function holdSess(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->holdSess($request);
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
    
    public function afterLogin(Request $request)
    {
        $redir = '';
        if (session()->has('previousUrl') 
            && $this->urlNotResourceFile(session()->get('previousUrl'))) {
            $redir = trim(session()->get('previousUrl'));
            $this->afterLoginSurveyRedir($request, $redir);
        } elseif (session()->has('redir2') 
            && $this->urlNotResourceFile(session()->get('redir2'))) {
            $redir = trim(session()->get('redir2'));
        }
        if (session()->has('lastTree') 
            && intVal(session()->get('lastTree')) > 0 
            && session()->has('loginRedir') 
            && trim(session()->get('loginRedir')) != ''
            && session()->has('lastTreeTime') 
            && session()->has('loginRedirTime')) {
            if (session()->get('lastTreeTime') < session()->get('loginRedirTime')) {
                $redir = trim(session()->get('loginRedir'));
            } else {
                $this->afterLoginLastTree($request);
            }
        } elseif (session()->has('lastTree') 
            && intVal(session()->get('lastTree')) > 0) {
            $this->afterLoginLastTree($request);
        } elseif (session()->has('loginRedir') 
            && $this->urlNotResourceFile(session()->get('loginRedir'))) {
            $redir = trim(session()->get('loginRedir'));
        }
        $this->loadDomain();
        $nonRedirs = [
            '', 
            '/', 
            '/home', 
            '/login', 
            '/register', 
            '/logout'
        ];
        $redir = str_replace($this->domainPath, '', $redir);
        if (in_array($redir, $nonRedirs)) {
            if (Auth::user() 
                && Auth::user()->hasRole('administrator|staff|databaser|brancher|partner')) {
                $redir = '/dashboard';
            } else {
                $redir = '/my-profile';
            }
        }
        if ($redir != '') {
            $this->clearSessRedirs();
            return redirect($redir);
        }
        $this->loadLoop($request);
        return $this->custLoop->afterLogin($request);
    }
    
    // check if being redirected back to a survey session, which needs to be associated with new user ID
    protected function afterLoginSurveyRedir(Request $request, $redir)
    {
        if (strpos($redir, '/u/') == 0 
            && Auth::user() 
            && isset(Auth::user()->id) 
            && Auth::user()->id > 0) {
            $treeSlug = substr($redir, 3);
            $pos = strpos($treeSlug, '/');
            if ($pos > 0) {
                $treeSlug = substr($treeSlug, 0, $pos);
                $chk = SLTree::where('tree_type', 'Survey')
                    ->where('tree_slug', $treeSlug)
                    ->get();
                if ($chk->isNotEmpty()) {
                    foreach ($chk as $tree) {
                        if (session()->has('sessID' . $tree->tree_id) 
                            && session()->has('coreID' . $tree->tree_id)
                            && intVal(session()->get('sessID' . $tree->tree_id)) > 0) {
                            $this->afterLoginUpdateSess($request, $tree);
                        }
                    }
                }
            }
        }
        return true;
    }
    
    protected function afterLoginUpdateSess(Request $request, $tree)
    {
        $sess = SLSess::find(session()->get('sessID' . $tree->tree_id));
        if ($sess && isset($sess->sess_core_id) && intVal($sess->sess_core_id) > 0) {
            $sess->sess_user_id = Auth::user()->id;
            $sess->save();
            $this->syncDataTrees($request, $tree->tree_database, $tree->tree_id);
            eval($GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl) 
                . "::find(" . $sess->sess_core_id . ")->update([ '" 
                . $GLOBALS["SL"]->getCoreTblUserFld() 
                . "' => " . Auth::user()->id . " ]);");
        }
    }
    
    protected function afterLoginLastTree(Request $request)
    {
        if (session()->has('lastTree')) {
            $tree = SLTree::find(session()->get('lastTree'));
            if ($tree && isset($tree->tree_database)) {
                $this->syncDataTrees($request, $tree->tree_database, $tree->tree_id);
            }
        }
        if (session()->has('sessID') && session()->get('sessID') > 0) {
            
        }
        return true;
    }
    
    protected function clearSessRedirs()
    {
        session()->forget('previousUrl');
        session()->forget('redir2');
        session()->forget('lastTree');
        session()->forget('lastTreeTime');
        session()->forget('loginRedir');
        session()->forget('loginRedirTime');
        session()->save();
        return true;
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
            if (strpos($username, 'Session#') !== false) {
                $atPos = strpos(Auth::user()->email, '@');
                $username = substr(Auth::user()->email, 0, $atPos);
            }
        }
        $previousUrl = '?';
        if ($request->has('currPage')) {
            $previousUrl .= 'previousUrl=' 
                . urlencode(trim($request->get('currPage'))) . '&';
        }
        if ($request->has('nd')) {
            $previousUrl .= 'nd=' . urlencode(trim($request->get('nd'))) . '&';
        }
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
