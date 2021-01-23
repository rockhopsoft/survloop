<?php
/**
  * Survloop is a core class for routing system access, particularly for loading a
  * client installation's customized extension of TreeSurvForm instead of the default.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since  v0.0.1
  */
namespace RockHopSoft\Survloop\Controllers;

use Illuminate\Http\Request;
use App\Models\SLTree;
use App\Models\SLNode;
use RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv;
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use RockHopSoft\Survloop\Controllers\SurvloopInstaller;
use RockHopSoft\Survloop\Controllers\SurvloopSpecialLoads;

class Survloop extends SurvloopSpecialLoads
{
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function mainSub(Request $request, $type = '', $val = '')
    {
        if ($request->has('step') 
            && $request->has('tree') 
            && intVal($request->get('tree')) > 0) {
            $this->loadTreeByID($request, $request->tree);
        }
        $this->loadLoop($request);
        return $this->custLoop->index($request, $type, $val);
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
                        $this->custLoop->loadSessionData($cid);
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
            if ($this->hasParamEdit($request) && $this->isStaffOrAdmin()) {
                echo '<script type="text/javascript"> '
                    . 'window.location="/dashboard/page/' 
                    . $this->treeID . '?all=1&alt=1&refresh=1"; '
                    . '</script>';
                exit;
            }
            $this->loadLoop($request);
            $view = $this->chkPageView($view);
            $cid = $this->chkPageCID($request, $cid, $skipPublic);
            if ($cid > 0) {
                $GLOBALS["SL"]->isOwner = $this->custLoop->isCoreOwner($cid);
                if (in_array($view, ['pdf', 'full-pdf'])) {
                    return $this->custLoop->byID($request, $cid, ' - ' . $this->custLoop->getCoreID() . '', $request->has('ajax'));
                }
            }
            $this->custLoop->chkPageToken();
            $allowCache = $this->chkPageAllowCache($request);
            if ($this->topCheckCache($request, 'page') && $allowCache) {
                return $this->addSessAdmCodeToPage($request, $this->pageContent);
            }
            //$this->custLoop->loadSessionData($cid);
            $this->chkPageHideDisclaim($request, $cid);
            if (in_array($view, ['xml', 'json'])) {
                $GLOBALS["SL"]->pageView = 'public';
                $this->custLoop->loadXmlMapTree($request);
                return $this->custLoop->getXmlID($request, $cid, $pageSlug);
            }
            if ($cid > 0) {
                $this->loadPageCID($request, $GLOBALS["SL"]->treeRow, intVal($cid));
                $this->custLoop->loadSessionData($GLOBALS["SL"]->coreTbl, $cid);
            }
            $this->pageContent = $this->custLoop->index($request);
            if ($allowCache) {
                $treeID = $GLOBALS["SL"]->treeRow->tree_id;
                $treeType = strtolower($GLOBALS["SL"]->treeRow->tree_type);
                $this->topSaveCache($treeID, $treeType);
            }
            if ($request->has('ajax') && intVal($request->ajax) == 1) {
                return $this->pageContent;
            }
            return $this->addSessAdmCodeToPage($request, $this->pageContent);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }

    /**
     * Check the view for this page's load.
     *
     * @param  string  $view
     * @return string
     */
    private function chkPageView($view = '')
    {
        $this->custLoop->customPdfFilename();
        if (in_array($view, ['pdf', 'full-pdf'])) {
            $this->custLoop->v["isPrint"] = 1;
            $GLOBALS["SL"]->x["isPrintPDF"] = true;
            if ($view == 'full-pdf') {
                $GLOBALS["SL"]->x["fullAccess"] = true;
            }
        }
        $GLOBALS["SL"]->pageView = trim($view); // blank results in user default
        return $view;
    }
    
    /**
     * Check page load's current allowance for caching.
     *
     * @param  Illuminate\Http\Request  $request
     * @return boolean
     */
    private function chkPageAllowCache(Request $request)
    {
        $allowCache = true;
        $hasToken = (isset($this->custLoop->v["tokenIn"]) 
            && trim($this->custLoop->v["tokenIn"]) != '');
        if ($GLOBALS["SL"]->treeRow->tree_opts%Globals::TREEOPT_NOCACHE == 0
            || $hasToken
            || $request->has('refresh')) {
            $allowCache = false;
        }
        return $allowCache;
    }
    
    /**
     * Check page's settings for the need to hide disclaimers.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  int  $cid
     * @return boolean
     */
    private function chkPageHideDisclaim(Request $request, $cid)
    {
        if ($cid > 0) {
            $GLOBALS["SL"]->initPageReadSffx($cid);
            if ($request->has('hideDisclaim') && intVal($request->hideDisclaim) == 1) {
                $this->custLoop->hideDisclaim = true;
            }
        }
        return true;
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
        $GLOBALS["SL"]->x["fullAccess"] = true;
        return $this->byID($request, $treeSlug, $cid, $coreSlug = '');
    }
    
    public function pdfByID(Request $request, $treeSlug, $cid)
    {
        $GLOBALS["SL"]->x["isPrintPDF"] = true;
        return $this->byID($request, $treeSlug, $cid);
    }
    
    public function fullPdfByID(Request $request, $treeSlug, $cid)
    {
        $GLOBALS["SL"]->x["fullAccess"] = true;
        return $this->pdfByID($request, $treeSlug, $cid);
    }
    
    public function tokenByID(Request $request, $pageSlug, $cid, $token)
    {
        return $this->loadPageURL($request, $pageSlug, $cid, 'token-' . trim($token));
        //return $this->byID($request, $treeSlug, $cid);
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
                    if ($request->has('edit') 
                        && intVal($request->get('edit')) == 1 
                        && $this->isStaffOrAdmin()) {
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
        $installer = new SurvloopInstaller;
        $installer->checkSysInit();
        return '<center><br /><br /><i>Reloading...</i><br /> '
            . '<iframe src="/css-reload" frameborder=0'
            . 'style="width: 60px; height: 60px; border: 0px none;"'
            . '></iframe></center><script type="text/javascript"> '
            . 'setTimeout("window.location=\'/\'", 2000); </script>';
    }
    
    public function xmlAll(Request $request, $treeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug, 'Survey XML')) {
            $this->loadLoop($request);
            return $this->custLoop->xmlAll($request);
        }
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->xmlAll($request);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function xmlByID(Request $request, $treeSlug, $cid)
    {
        if ($this->loadTreeBySlug($request, $treeSlug, 'Survey XML')) {
            $this->loadLoop($request);
            $cid = $GLOBALS["SL"]->chkInPublicID($cid);
            return $this->custLoop->xmlByID($request, $cid);
        }
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            $cid = $GLOBALS["SL"]->chkInPublicID($cid);
            return $this->custLoop->xmlByID($request, $cid);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function fullXmlByID(Request $request, $treeSlug, $cid)
    {
        $GLOBALS["SL"]->x["fullAccess"] = true;
        return $this->xmlByID($request, $treeSlug, $cid);
    }
    
    public function xmlFullByID(Request $request, $treeSlug, $cid)
    {
        if ($this->loadTreeBySlug($request, $treeSlug, 'Survey XML')) {
            $this->loadLoop($request);
            $cid = $GLOBALS["SL"]->chkInPublicID($cid);
            return $this->custLoop->xmlFullByID($request, $cid);
        }
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            $cid = $GLOBALS["SL"]->chkInPublicID($cid);
            return $this->custLoop->xmlFullByID($request, $cid);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function getXmlExample(Request $request, $treeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug, 'Survey XML')) {
            $this->loadLoop($request);
            return $this->custLoop->getXmlExample($request);
        }
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->getXmlExample($request);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function genXmlSchema(Request $request, $treeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug, 'Survey XML')) {
            $this->loadLoop($request);
            return $this->custLoop->genXmlSchema($request);
        }
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->genXmlSchema($request);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
}