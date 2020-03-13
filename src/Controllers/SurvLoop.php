<?php
/**
  * SurvLoop is a core class for routing system access, particularly for loading a
  * client installation's customized extension of TreeSurvForm instead of the default.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since  v0.0.1
  */
namespace SurvLoop\Controllers;

use Auth;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\File\File;
use App\Models\User;
use App\Models\SLSess;
use App\Models\SLTree;
use App\Models\SLNode;
use SurvLoop\Controllers\Tree\TreeNodeSurv;
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
        if ($request->has('step') && $request->has('tree') 
            && intVal($request->get('tree')) > 0) {
            $this->loadTreeByID($request, $request->tree);
        }
        $this->loadLoop($request);
        return $this->custLoop->index($request, $type, $val);
    }
    
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
    
    /**
     * Loading a url identifying a specific Page Node within a Survey Tree.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  string  $treeSlug
     * @param  string  $nodeSlug
     * @return string
     */
    public function loadNodeURL(Request $request, $treeSlug = '', $nodeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->loadNodeURL($request, $nodeSlug);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    /**
     * Loading an ajax-retrieved Node within a Tree's Page.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  int  $treeID
     * @param  int  $cid
     * @param  int  $nID
     * @param  string  $date
     * @param  int  $rand
     * @return string
     */
    public function deferNode(Request $request, $treeID = 1, $cid = 0, $nID = 0, $date = '', $rand = 0)
    {
        $file = '../storage/app/cache/html/' . $date . '-t' . $treeID
            . '-c' . $cid . '-n' . $nID . '-r' . $rand . '.html';
        if ($treeID > 0 && $nID > 0 && $this->loadTreeByID($request, $treeID)) {
            $node = SLNode::find($nID);
            if ($node && isset($node->node_opts) && intVal($node->node_opts) > 0) {
                if ($node->node_opts%TreeNodeSurv::OPT_NONODECACH > 0) {
                    if (file_exists($file)) {
                        return file_get_contents($file);
                    }
                } else { // No caching allow for this node
                    $this->loadLoop($request);
                    if ($cid > 0) {
                        $GLOBALS["SL"]->isOwner = $this->custLoop->isCoreOwner($cid);
                        $GLOBALS["SL"]->initPageReadSffx($cid);
                        $this->custLoop->loadSessionDataRawCid($cid);
                    }
                    return $this->custLoop->printTreeNodePublic($nID);
                }
            }
        }
        return '';
    }
    
    /**
     * Loading a url identifying a specific Page Tree.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  string  $pageSlug
     * @param  int  $cid
     * @param  string  $view
     * @param  boolean  $skipPublic
     * @return string
     */
    public function loadPageURL(Request $request, $pageSlug = '', $cid = 0, $view = '', $skipPublic = false)
    {
        $redir = $this->chkPageRedir($pageSlug);
        if ($redir != $pageSlug) {
            return redirect($redir, 301);
        }
        if ($this->loadTreeBySlug($request, $pageSlug, 'Page')) {
            if ($this->hasParamEdit($request) && $this->isUserAdmin()) {
                echo '<script type="text/javascript"> window.location="/dashboard/page/' 
                    . $this->treeID . '?all=1&alt=1&refresh=1"; </script>';
                exit;
            }
            $this->loadLoop($request);
            if (in_array($view, ['pdf', 'full-pdf'])) {
                $this->custLoop->v["isPrint"] = 1;
                $GLOBALS["SL"]->x["isPrintPDF"] = true;
            }
            $GLOBALS["SL"]->pageView = trim($view); // blank results in user default

            if ($cid <= 0 && $request->has('cid')) {
                $cid = intVal($request->get('cid'));
            }
            if ($cid > 0) {
                if (!$skipPublic) {
                    $GLOBALS["SL"]->coreID = $GLOBALS["SL"]->swapIfPublicID($cid);
                } else {
                    $GLOBALS["SL"]->coreID = intVal($cid);
                }
                $cid = $GLOBALS["SL"]->coreID;
                $GLOBALS["SL"]->isOwner = $this->custLoop->isCoreOwner($cid);
            }

            if ($this->topCheckCache($request, 'page')
                && $GLOBALS["SL"]->treeRow->tree_opts%Globals::TREEOPT_NOCACHE > 0) {
                return $this->addSessAdmCodeToPage($request, $this->pageContent);
            }
            if ($cid > 0) {
                $GLOBALS["SL"]->initPageReadSffx($cid);
                $this->custLoop->loadSessionDataRawCid($cid);
                if ($request->has('hideDisclaim') 
                    && intVal($request->hideDisclaim) == 1) {
                    $this->custLoop->hideDisclaim = true;
                }
            }
            if (in_array($view, ['xml', 'json'])) {
                $GLOBALS["SL"]->pageView = 'public';
                $this->custLoop->loadXmlMapTree($request);
                return $this->custLoop->getXmlID($request, $cid, $pageSlug);
            }
            $this->pageContent = $this->custLoop->index($request);
            if ($GLOBALS["SL"]->treeRow->tree_opts
                %Globals::TREEOPT_NOCACHE > 0) {
                $this->topSaveCache(
                    $GLOBALS["SL"]->treeRow->tree_id, 
                    strtolower($GLOBALS["SL"]->treeRow->tree_type)
                );
            }
            return $this->addSessAdmCodeToPage(
                $request, 
                $this->pageContent
            );
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    /**
     * Loading the site's home page by looking up the right Page Tree.
     *
     * @param  Illuminate\Http\Request  $request
     * @return string
     */
    public function loadPageHome(Request $request)
    {
        $this->syncDataTrees($request);
        $this->loadDomain();
        $this->checkHttpsDomain($request);
        $trees = SLTree::where('tree_type', 'Page')
            ->where('tree_opts', '>', (Globals::TREEOPT_HOMEPAGE-1))
            /* ->whereRaw("tree_opts%" . Globals::TREEOPT_HOMEPAGE . " = 0")
            ->whereRaw("tree_opts%" . Globals::TREEOPT_ADMIN . " > 0")
            ->whereRaw("tree_opts%" . Globals::TREEOPT_STAFF . " > 0")
            ->whereRaw("tree_opts%" . Globals::TREEOPT_PARTNER . " > 0")
            ->whereRaw("tree_opts%" . Globals::TREEOPT_VOLUNTEER . " > 0") */
            ->orderBy('tree_id', 'asc')
            ->get();
        if ($trees->isNotEmpty()) {
            foreach ($trees as $i => $tree) {
                if (isset($tree->tree_opts) 
                    && $tree->tree_opts%Globals::TREEOPT_HOMEPAGE == 0 
                    && $tree->tree_opts%Globals::TREEOPT_ADMIN     > 0 
                    && $tree->tree_opts%Globals::TREEOPT_STAFF     > 0
                    && $tree->tree_opts%Globals::TREEOPT_PARTNER   > 0 
                    && $tree->tree_opts%Globals::TREEOPT_VOLUNTEER > 0) {
                    $redir = $this->chkPageRedir($tree->tree_slug);
                    if ($redir != $tree->tree_slug) {
                        return redirect($redir);
                    }
                    if ($request->has('edit') && intVal($request->get('edit')) == 1 
                        && $this->isUserAdmin()) {
                        echo '<script type="text/javascript"> '
                            . 'window.location="/dashboard/page/' 
                            . $tree->tree_id . '?all=1&alt=1&refresh=1";'
                            . ' </script>';
                        exit;
                    }
                    $this->syncDataTrees(
                        $request, 
                        $tree->tree_database, 
                        $tree->tree_id
                    );
                    if ($this->topCheckCache($request, 'page')) {
                        return $this->addSessAdmCodeToPage(
                            $request, 
                            $this->pageContent
                        );
                    }
                    $this->loadLoop($request);
                    $this->pageContent = $this->custLoop->index($request);
                    if ($tree->tree_opts%Globals::TREEOPT_NOCACHE > 0) {
                        $this->topSaveCache($tree->tree_id, 'page');
                    }
                    return $this->addAdmCodeToPage(
                        $GLOBALS["SL"]->swapSessMsg($this->pageContent)
                    );
                }
            }
        }
        
        // else Home Page not found, so let's create one
        $installer = new SurvLoopInstaller;
        $installer->checkSysInit();
        return '<center><br /><br /><i>Reloading...</i><br /> '
            . '<iframe src="/css-reload" frameborder=0'
            . 'style="width: 60px; height: 60px; border: 0px none;"'
            . '></iframe></center><script type="text/javascript"> '
            . 'setTimeout("window.location=\'/\'", 2000); </script>';
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
            if (session()->get('lastTreeTime') 
                < session()->get('loginRedirTime')) {
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
        if (strpos($redir, '/u/') == 0 && Auth::user() 
            && isset(Auth::user()->id) && Auth::user()->id > 0) {
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
            $hasAjax = $request->has('ajax');
            return $this->custLoop->byID($request, $cid, $coreSlug, $hasAjax);
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
    
    public function getUploadFile(Request $request, $abbr, $file)
    {
        $filename = '../storage/app/up/' . $abbr . '/' . $file;
        $handler = new File($filename);
        // Get the last modified time for the file (Unix timestamp):
        $file_time = $handler->getMTime(); 
        $lifetime = (60*60*24*5); // five days in seconds
        $header_etag = md5($file_time . $filename);
        $header_last_modified = gmdate('r', $file_time);
        $headers = [
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            // override caching for sensitive:
            'Cache-Control'       => 'public, max-age="' . $lifetime . '"', 
            'Last-Modified'       => $header_last_modified,
            'Expires'             => gmdate('r', $file_time + $lifetime),
            'Pragma'              => 'public',
            'Etag'                => $header_etag
        ];
        
        // Is the resource cached?
        $h1 = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) 
            && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $header_last_modified);
        $h2 = (isset($_SERVER['HTTP_IF_NONE_MATCH']) 
            && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) 
                == $header_etag);
        if (($h1 || $h2) && !$request->has('refresh')) {
            return Response::make('', 304, $headers); 
        }
        // File (image) is cached by the browser, so we don't have to send it again
        
        $headers = array_merge($headers, [
            'Content-Type'   => $handler->getMimeType(),
            'Content-Length' => $handler->getSize()
        ]);
        return Response::make(
            file_get_contents($filename), 
            200, 
            $headers
        );
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
                "username"    => $username,
                "previousUrl" => $previousUrl
            ]
        );
    }
    
    public function timeOut(Request $request)
    {
        return view(
            'auth.dialog-check-form-sess',
            [ "req" => $request ]
        );
    }
    
    public function getJsonSurvStats(Request $request)
    {
        $this->syncDataTrees($request);
        $this->loadLoop($request);
        header('Content-Type: application/json');
        $stats = $GLOBALS["SL"]->getJsonSurvStats();
    	$stats["Survey1Complete"] = sizeof(
            $this->custLoop->getAllPublicCoreIDs()
        );
        echo json_encode($stats);
        exit;
    }
    
}