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
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\File\File;
use App\Models\User;
use App\Models\SLSess;
use App\Models\SLTree;
use SurvLoop\Controllers\Globals\Globals;
use SurvLoop\Controllers\SurvLoopInstaller;
use SurvLoop\Controllers\SurvCustLoop;

class SurvLoop extends SurvCustLoop
{
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function mainSub(Request $request, $type = '', $val = '')
    {
        if ($request->has('step') && $request->has('tree') && intVal($request->get('tree')) > 0) {
            $this->loadTreeByID($request, $request->tree);
        }
        $this->loadLoop($request);
        return $this->custLoop->index($request, $type, $val);
    }
    
    public function processEmailConfirmToken(Request $request, $token = '', $tokenB = '')
    { 
        $this->loadLoop($request);
        return $this->custLoop->processEmailConfirmToken($request, $token, $tokenB);
    }
    
    public function loadNodeURL(Request $request, $treeSlug = '', $nodeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->loadNodeURL($request, $nodeSlug);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function deferNode(Request $request, $treeID = 1, $nodeID = -3)
    {
        $file = '../storage/app/cache/dynascript/t' . $treeID . 'n' . $nodeID . '.html';
        if ($treeID > 0 && $nodeID > 0 && $this->loadTreeByID($request, $treeID) && file_exists($file)) {
            return file_get_contents($file);
        }
        return '';
    }
    
    public function loadPageURL(Request $request, $pageSlug = '', $cid = -3, $view = '', $skipPublic = false)
    {
        $redir = $this->chkPageRedir($pageSlug);
        if ($redir != $pageSlug) {
            redirect($redir);
        }
        if ($this->loadTreeBySlug($request, $pageSlug, 'Page')) {
            if ($request->has('edit') && intVal($request->get('edit')) == 1 && $this->isUserAdmin()) {
                echo '<script type="text/javascript"> window.location="/dashboard/page/' 
                    . $this->treeID . '?all=1&alt=1&refresh=1"; </script>';
                exit;
            }
            $this->loadLoop($request);
            if ($view == 'pdf') {
                $this->custLoop->v["isPrint"] = 1;
                $GLOBALS["SL"]->x["isPrintPDF"] = true;
            }
            if ($cid > 0) {
                $this->custLoop->loadSessionData($GLOBALS["SL"]->coreTbl, $cid, $skipPublic);
                if ($request->has('hideDisclaim') && intVal($request->hideDisclaim) == 1) {
                    $this->custLoop->hideDisclaim = true;
                }
                $GLOBALS["SL"]->x["pageSlugSffx"] = '/read-' . $cid;
                $GLOBALS["SL"]->x["pageView"] = trim($view); // blank results in user default
                if ($GLOBALS["SL"]->x["pageView"] != '') {
                    $GLOBALS["SL"]->x["pageSlugSffx"] .= '/' . $GLOBALS["SL"]->x["pageView"];
                }
            }
            if (in_array($view, ['xml', 'json'])) {
                $GLOBALS["SL"]->x["pageView"] = 'public';
                $this->custLoop->loadXmlMapTree($request);
                return $this->custLoop->getXmlID($request, $cid, $pageSlug);
            }
            $this->pageContent = $this->custLoop->index($request);
            if ($GLOBALS["SL"]->treeRow->TreeOpts%Globals::TREEOPT_NOCACHE > 0 && $cid <= 0) {
                $this->topSaveCache();
            }
            return $this->addAdmCodeToPage($GLOBALS["SL"]->swapSessMsg($this->pageContent));
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function loadPageHome(Request $request)
    {
        if ($this->topCheckCache($request) && (!$request->has('edit') || intVal($request->get('edit')) != 1 
            || !$this->isUserAdmin())) {
            return $this->addAdmCodeToPage($GLOBALS["SL"]->swapSessMsg($this->pageContent));
        }
        $this->loadDomain();
        $this->checkHttpsDomain($request);
        $tree = SLTree::where('TreeType', 'Page')
            ->whereRaw("TreeOpts%" . Globals::TREEOPT_HOMEPAGE . " = 0")
            ->whereRaw("TreeOpts%" . Globals::TREEOPT_ADMIN . " > 0")
            ->whereRaw("TreeOpts%" . Globals::TREEOPT_STAFF . " > 0")
            ->whereRaw("TreeOpts%" . Globals::TREEOPT_PARTNER . " > 0")
            ->whereRaw("TreeOpts%" . Globals::TREEOPT_VOLUNTEER . " > 0")
            ->orderBy('TreeID', 'asc')
            ->first();
        if ($tree && isset($tree->TreeID)) {
            $redir = $this->chkPageRedir($tree->TreeSlug);
            if ($redir != $tree->TreeSlug) return redirect($redir);
            if ($request->has('edit') && intVal($request->get('edit')) == 1 && $this->isUserAdmin()) {
                echo '<script type="text/javascript"> window.location="/dashboard/page/' 
                    . $tree->TreeID . '?all=1&alt=1&refresh=1"; </script>';
                exit;
            }
            $this->syncDataTrees($request, $tree->TreeDatabase, $tree->TreeID);
            $this->loadLoop($request);
            $this->pageContent = $this->custLoop->index($request);
            if ($tree->TreeOpts%Globals::TREEOPT_NOCACHE > 0) {
                $this->topSaveCache();
            }
            return $this->addAdmCodeToPage($GLOBALS["SL"]->swapSessMsg($this->pageContent));
        }
        
        // else Home Page not found, so let's create one
        $this->syncDataTrees($request);
        $installer = new SurvLoopInstaller;
        $installer->checkSysInit();
        return '<center><br /><br /><i>Reloading...</i><br /> <iframe src="/css-reload" frameborder=0
            style="width: 60px; height: 60px; border: 0px none;"></iframe></center>
            <script type="text/javascript"> setTimeout("window.location=\'/\'", 2000); </script>';
    }
    
    public function testRun(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->testRun($request);
    }
    
    public function ajaxChecks(Request $request, $type = '')
    {
        $this->loadLoop($request);
        return $this->custLoop->ajaxChecks($request, $type);
    }
    
    public function sortLoop(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->sortLoop($request);
    }
    
    public function showProfile(Request $request, $uname = '')
    {
        $tree = SLTree::where('TreeType', 'Page')
            ->whereRaw("TreeOpts%" . Globals::TREEOPT_PROFILE . " = 0")
            ->orderBy('TreeID', 'asc')
            ->first();
        if ($tree && isset($tree->TreeID)) {
            $this->syncDataTrees($request, $tree->TreeDatabase, $tree->TreeID);
            $this->loadLoop($request);
            $this->custLoop->setCurrUserProfile($uname);
            return $this->custLoop->index($request);
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
            //return redirect($this->domainPath . '/profile/' . urlencode(Auth::user()->name));
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
        if (session()->has('previousUrl') && $this->urlNotCssNorJs(session()->get('previousUrl'))) {
            $redir = trim(session()->get('previousUrl'));
            // check if being redirected back to a survey session, which needs to be associated with new user ID
            if (strpos($redir, '/u/') == 0 && Auth::user() && isset(Auth::user()->id) && Auth::user()->id > 0) {
                $treeSlug = substr($redir, 3);
                $pos = strpos($treeSlug, '/');
                if ($pos > 0) {
                    $treeSlug = substr($treeSlug, 0, $pos);
                    $chk = SLTree::where('TreeType', 'Survey')
                        ->where('TreeSlug', $treeSlug)
                        ->get();
                    if ($chk->isNotEmpty()) {
                        foreach ($chk as $tree) {
                            if (session()->has('sessID' . $tree->TreeID) && session()->has('coreID' . $tree->TreeID)
                                && intVal(session()->get('sessID' . $tree->TreeID)) > 0) {
                                SLSess::find(session()->get('sessID' . $tree->TreeID))
                                    ->update([ 'SessUserID' => Auth::user()->id ]);
                            }
                        }
                    }
                }
            }
        } elseif (session()->has('redir2') && $this->urlNotCssNorJs(session()->get('redir2'))) {
            $redir = trim(session()->get('redir2'));
        }
        if (session()->has('lastTree') && intVal(session()->get('lastTree')) > 0 
            && session()->has('loginRedir') && trim(session()->get('loginRedir')) != ''
            && session()->has('lastTreeTime') && session()->has('loginRedirTime')) {
            if (session()->get('lastTreeTime') < session()->get('loginRedirTime')) {
                $redir = trim(session()->get('loginRedir'));
            } else {
                $this->afterLoginLastTree($request);
            }
        } elseif (session()->has('lastTree') && intVal(session()->get('lastTree')) > 0) {
            $this->afterLoginLastTree($request);
        } elseif (session()->has('loginRedir') && $this->urlNotCssNorJs(session()->get('loginRedir'))) {
            $redir = trim(session()->get('loginRedir'));
        }
        $this->loadDomain();
        if (in_array($redir, ['/', '/home', $this->domainPath, $this->domainPath . '/'])) {
            $redir = '/my-profile';
        }
        if ($redir != '') {
            $this->clearSessRedirs();
            return redirect($redir);
        }
        $this->loadLoop($request);
        return $this->custLoop->afterLogin($request);
    }
    
    protected function afterLoginLastTree(Request $request)
    {
        if (session()->has('lastTree')) {
            $tree = SLTree::find(session()->get('lastTree'));
            if ($tree && isset($tree->TreeDatabase)) {
                $this->syncDataTrees($request, $tree->TreeDatabase, $tree->TreeID);
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
        return true;
    }
    
    public function retrieveUpload(Request $request, $treeSlug = '', $cid = -3, $upID = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->retrieveUpload($request, $cid, $upID);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function byID(Request $request, $treeSlug, $cid, $coreSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->byID($request, $cid, $coreSlug, $request->has('ajax'));
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function fullByID(Request $request, $treeSlug, $cid, $coreSlug = '')
    {
        $GLOBALS["fullAccess"] = true;
        return $this->byID($request, $treeSlug, $cid, $coreSlug = '');
    }
    
    public function pdfByID(Request $request, $treeSlug, $cid)
    {
        $GLOBALS["SL"]->x["isPrintPDF"] = true;
        return $this->byID($request, $treeSlug, $cid);
    }
    
    public function fullPdfByID(Request $request, $treeSlug, $cid)
    {
        $GLOBALS["fullAccess"] = true;
        return $this->pdfByID($request, $treeSlug, $cid);
    }
    
    public function fullXmlByID(Request $request, $treeSlug, $cid)
    {
        $GLOBALS["fullAccess"] = true;
        return $this->xmlByID($request, $treeSlug, $cid);
    }
    
    public function tokenByID(Request $request, $pageSlug, $cid, $token)
    {
        return $this->loadPageURL($request, $pageSlug, $cid, 'token-' . trim($token));
        //return $this->byID($request, $treeSlug, $cid);
    }
    
    public function xmlAll(Request $request, $treeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->xmlAll($request);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function xmlByID(Request $request, $treeSlug, $cid)
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->xmlByID($request, $cid);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function getXmlExample(Request $request, $treeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->getXmlExample($request);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function genXmlSchema(Request $request, $treeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->genXmlSchema($request);
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
    
    // SurvLoop Widgets
    
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
        $this->custLoop->loadTree($treeID);
        $this->custLoop->initSearcher();
        $this->custLoop->searcher->getSearchFilts();
        $this->custLoop->searcher->getAllPublicCoreIDs();
        $this->custLoop->chkRecsPub($request);
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
            //$this->currLoop->survLoopInit($request, $this->currLoop->searchCacheName());
            // [ check for cache ]
        $this->custLoop->searcher->prepSearchResults($request);
        if (sizeof($this->custLoop->searcher->searchResults) > 0) {
            foreach ($this->custLoop->searcher->searchResults as $i => $rec) {
                if (trim($rec[2]) == '') {
                    $this->custLoop->sessData->loadCore($GLOBALS["SL"]->coreTbl, $rec[0]);
                    $this->custLoop->searcher->searchResults[$i][2] 
                        = '<div class="reportPreview">' . $this->custLoop->printPreviewReport() . '</div>';
                    if (isset($this->custLoop->sessData->dataSets[$GLOBALS["SL"]->coreTbl])
                        && sizeof($this->custLoop->sessData->dataSets[$GLOBALS["SL"]->coreTbl]) > 0
                        && isset($this->custLoop->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->created_at)) {
                        $this->custLoop->searcher->searchResults[$i][1] 
                            += strtotime($this->custLoop->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->created_at)
                                /1000000000000;
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
    
    public function getUploadFile(Request $request, $abbr, $file)
    {
        $filename = '../storage/app/up/' . $abbr . '/' . $file;
        $handler = new File($filename);
        $file_time = $handler->getMTime(); // Get the last modified time for the file (Unix timestamp)
        $lifetime = (60*60*24*5); // five days in seconds
        $header_etag = md5($file_time . $filename);
        $header_last_modified = gmdate('r', $file_time);
        $headers = [
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control'       => 'public, max-age="' . $lifetime . '"', // override caching for sensitive
            'Last-Modified'       => $header_last_modified,
            'Expires'             => gmdate('r', $file_time + $lifetime),
            'Pragma'              => 'public',
            'Etag'                => $header_etag
        ];
        
        // Is the resource cached?
        $h1 = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) 
            && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $header_last_modified);
        $h2 = (isset($_SERVER['HTTP_IF_NONE_MATCH']) 
            && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $header_etag);
        if (($h1 || $h2) && !$request->has('refresh')) {
            return Response::make('', 304, $headers); 
        }
        // File (image) is cached by the browser, so we don't have to send it again
        
        $headers = array_merge($headers, [
            'Content-Type'   => $handler->getMimeType(),
            'Content-Length' => $handler->getSize()
        ]);
        return Response::make(file_get_contents($filename), 200, $headers);
    }
    
    public function jsLoadMenu(Request $request)
    {
        $username = '';
        if (Auth::user() && isset(Auth::user()->id)) {
            $username = Auth::user()->name;
            if (strpos($username, 'Session#') !== false) {
                $username = substr(Auth::user()->email, 0, strpos(Auth::user()->email, '@'));
            }
        }
        return view('vendor.survloop.js.inc-load-menu', [ "username" => $username ]);
    }
    
    public function timeOut(Request $request)
    {
        return view('auth.dialog-check-form-sess', [ "req" => $request ]);
    }
    
    public function getJsonSurvStats(Request $request)
    {
        $this->syncDataTrees($request);
        $this->loadLoop($request);
        header('Content-Type: application/json');
        $stats = $GLOBALS["SL"]->getJsonSurvStats();
    	$stats["Survey1Complete"] = sizeof($this->custLoop->getAllPublicCoreIDs());
        echo json_encode($stats);
        exit;
    }
    
}