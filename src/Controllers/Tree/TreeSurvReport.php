<?php
/**
  * TreeSurvReport is a mid-level class with functions related to generating reports within a tree.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.19
  */
namespace SurvLoop\Controllers\Tree;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\SLSessEmojis;
use SurvLoop\Controllers\Tree\TreeSurvBasicNav;

class TreeSurvReport extends TreeSurvBasicNav
{
    public function printAdminReport($coreID)
    {
        $this->v["cID"] = $coreID;
        return $this->printFullReport('', true);
    }
    
    public function printFullReport($reportType = '', $isAdmin = false, $inForms = false)
    {
        return '';
    }
    
    public function printPreviewReportCustom($isAdmin = false)
    {
        return '';
    }
    
    public function printPreviewReport($isAdmin = false)
    {
        $ret = $this->printPreviewReportCustom($isAdmin);
        if (trim($ret) != '') {
            return $ret;
        }
        $fldNames = $found = [];
        if (sizeof($this->nodesRawOrder) > 0) {
            foreach ($this->nodesRawOrder as $i => $nID) {
                if (isset($this->allNodes[$nID]) 
                    && $this->allNodes[$nID]->isRequired()) {
                    $tblFld = $this->allNodes[$nID]->getTblFld();
                    $fldNames[] = [ $tblFld[0], $tblFld[1] ];
                }
            }
        }
        if (sizeof($fldNames) > 0) {
            foreach ($fldNames as $i => $fld) {
                if (isset($this->sessData->dataSets[$fld[0]]) 
                    && sizeof($this->sessData->dataSets[$fld[0]]) > 0
                    && isset($this->sessData->dataSets[$fld[0]][0]->{ $fld[1] }) 
                    && sizeof($found) < 6) {
                    $found[] = $fld[1];
                    $ret .= '<span class="mR20">' 
                        . $this->sessData->dataSets[$fld[0]][0]->{ $fld[1] } 
                        . '</span>';
                }
            }
        }
        return $ret;
    }
    
    public function ajaxEmojiTag(Request $request, $recID = -3, $defID = -3)
    {
        if ($recID <= 0) {
            return '';
        }
        $this->survLoopInit($request, '');
        if ($this->v["uID"] <= 0) {
            return '<h4><i>Please <a href="/login">Login</a></i></h4>';
        }
        $this->loadSessionData($GLOBALS["SL"]->coreTbl, $recID);
        $this->loadEmojiTags($defID);
        if (sizeof($GLOBALS["SL"]->treeSettings['emojis']) > 0 && $recID > 0) {
            foreach ($GLOBALS["SL"]->treeSettings['emojis'] as $i => $emo) {
                if ($emo["id"] == $defID) {
                    if (isset($this->emojiTagUsrs[$emo["id"]]) 
                        && in_array($this->v["uID"], $this->emojiTagUsrs[$emo["id"]])) {
                        SLSessEmojis::where('sess_emo_rec_id', $this->coreID)
                            ->where('sess_emo_def_id', $emo["id"])
                            ->where('sess_emo_tree_id', $this->treeID)
                            ->where('sess_emo_user_id', $this->v["uID"])
                            ->delete();
                        $this->emojiTagOff($emo["id"]);
                    } else {
                        $newTag = new SLSessEmojis;
                        $newTag->sess_emo_rec_id  = $this->coreID;
                        $newTag->sess_emo_def_id  = $emo["id"];
                        $newTag->sess_emo_tree_id = $this->treeID;
                        $newTag->sess_emo_user_id = $this->v["uID"];
                        $newTag->save();
                        $this->emojiTagOn($emo["id"]);
                    }
                }
            }
            $this->loadEmojiTags($defID);
        }
        $isActive = false;
        if (isset($GLOBALS["SL"]->treeSettings["emojis"])) {
            $emos = $GLOBALS["SL"]->treeSettings["emojis"];
            if (sizeof($emos) > 0) {
                foreach ($emos as $emo) {
                    if ($emo["id"] == $defID) {
                        if ($this->v["uID"] > 0 
                            && isset($this->emojiTagUsrs[$defID])
                            && in_array($this->v["uID"], $this->emojiTagUsrs[$defID])) {
                            $isActive = true;
                        }
                        $spot = 't' . $this->treeID . 'r' . $this->coreID;
                        $cnt = sizeof($this->emojiTagUsrs[$defID]);
                        return view(
                            'vendor.survloop.elements.inc-emoji-tag', 
                            [
                                "spot"     => $spot, 
                                "emo"      => $emo, 
                                "cnt"      => $cnt,
                                "isActive" => $isActive
                            ]
                        )->render();
                    }
                }
            }
        }
        return '';
    }
    
    public function emojiTagOn($defID = -3)
    {
        return true;
    }
    
    public function emojiTagOff($defID = -3)
    {
        return true;
    }
    
    protected function loadEmojiTags($defID = -3)
    {
        if ($this->coreID > 0 && isset($GLOBALS["SL"]->treeSettings["emojis"])) {
            $emos = $GLOBALS["SL"]->treeSettings["emojis"];
            if (sizeof($emos) > 0) {
                foreach ($emos as $emo) {
                    if ($defID <= 0 || $emo["id"] == $defID) {
                        $this->emojiTagUsrs[$emo["id"]] = [];
                        $chk = SLSessEmojis::where('sess_emo_rec_id', $this->coreID)
                            ->where('sess_emo_def_id', $emo["id"])
                            ->where('sess_emo_tree_id', $this->treeID)
                            ->get();
                        if ($chk->isNotEmpty()) {
                            foreach ($chk as $tag) {
                                $this->emojiTagUsrs[$emo["id"]][] = $tag->SessEmoUserID;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }
    
    protected function printEmojiTags()
    {
        $ret = '';
        $this->loadEmojiTags();
        if (isset($GLOBALS["SL"]->treeSettings['emojis']) 
            && sizeof($GLOBALS["SL"]->treeSettings['emojis']) > 0 
            && $this->coreID > 0) {
            $admPower = ($this->v["user"] && $this->v["user"]->hasRole('administrator|staff'));
            $spot = 't' . $this->treeID . 'r' . $this->coreID;
            foreach ($GLOBALS["SL"]->treeSettings['emojis'] as $emo) {
                if (!$emo["admin"] || $admPower) {
                    $GLOBALS["SL"]->pageAJAX .= '$(document).on("click", "#' . $spot . 'e' . $emo["id"] 
                        . '", function() { $("#' . $spot . 'e' . $emo["id"] . 'Tag").load("/ajax-emoji-tag/' 
                        . $this->treeID . '/' . $this->coreID . '/' . $emo["id"] . '"); });' . "\n";
                }
            }
            $ret .= view(
                'vendor.survloop.elements.inc-emoji-tags', 
                [
                    "spot"     => $spot, 
                    "emojis"   => $GLOBALS["SL"]->treeSettings["emojis"], 
                    "users"    => $this->emojiTagUsrs,
                    "uID"      => (($this->v["uID"] > 0) ? $this->v["uID"] : -3),
                    "admPower" => $admPower
                ]
            )->render();
        }
        return $ret;
    }
    
    protected function fillGlossary()
    {
        $this->v["glossaryList"] = [];
        return true;
    }
    
    protected function printGlossary()
    {
        if (!isset($this->v["glossaryList"]) || sizeof($this->v["glossaryList"]) == 0) {
            $this->fillGlossary();
        }
        if (sizeof($this->v["glossaryList"]) > 0) {
            $ret = '<h3 class="mT0 mB20 slBlueDark">Glossary of Terms</h3><div class="glossaryList">';
            foreach ($this->v["glossaryList"] as $i => $gloss) {
                $ret .= '<div class="row pT15 pB15"><div class="col-md-3">' . $gloss[0] . '</div>'
                    . '<div class="col-md-9">' . ((isset($gloss[1])) ? $gloss[1] : '') . '</div></div>';
            }
            return $ret . '</div>';
        }
        return '';
    }
    
    protected function swapSeo($str)
    {
        $str = str_replace('#1111', '#' . $this->corePublicID, $str);
        $str = str_replace('[cID]', $this->corePublicID, $str);
        $str = str_replace('[coreID]', $this->corePublicID, $str);
        return $str;
    }
    
    protected function runPageLoad($nID)
    {
        if (!$this->isPage && $GLOBALS["SL"]->treeRow->tree_opts%13 == 0) { // report
            $GLOBALS["SL"]->sysOpts['meta-title'] = $this->swapSeo($GLOBALS["SL"]->sysOpts['meta-title']);
            $GLOBALS["SL"]->sysOpts['meta-desc'] = $this->swapSeo($GLOBALS["SL"]->sysOpts['meta-desc']);
        }
        return true;
    }
    
    /**
     * Override the default behavior for wrapping a tree which has
     * been called through an ajax call.
     *
     * @return boolean
     */
    protected function runPageExtra($nID) 
    {
        return true; 
    }
    
    protected function printNodePageFoot()
    {
        return (isset($GLOBALS["SL"]->sysOpts["footer-master"]) 
            ? $GLOBALS["SL"]->sysOpts["footer-master"] : '');
    }
    
    public function printCurrRecMgmt()
    {
        $recDesc = '';
        if (isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl]) 
            && sizeof($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl]) > 0) {
            $rec = $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0];
            $recDesc = trim($this->getTableRecLabel($GLOBALS["SL"]->coreTbl, $rec));
        }
        $isUser = (isset($this->v["uID"]) && $this->v["uID"] > 0);
        $multiRecs = ((isset($this->v["multipleRecords"])) ? $this->v["multipleRecords"] : '');
        return view(
            'vendor.survloop.forms.foot-record-mgmt', 
            [
                "coreID"          => $this->coreID,
                "treeID"          => $this->treeID,
                "multipleRecords" => $multiRecs,
                "isUser"          => $isUser,
                "recDesc"         => $recDesc
            ]
        )->render();
    }
    
    public function chkDeets($deets)
    {
        $new = [];
        if (sizeof($deets) > 0) {
            foreach ($deets as $i => $deet) {
                if (isset($deet[0]) && trim($deet[0]) != '') {
                    $new[] = $deet;
                }
            }
        }
        return $new;
    }
    
    public function printReportDeetsBlock($deets, $blockName = '', $curr = null)
    {
        $deets = $this->chkDeets($deets);
        return view(
            'vendor.survloop.reports.inc-deets', 
            [
                "nID"       => $curr->nID,
                "nIDtxt"    => $curr->nIDtxt,
                "deets"     => $deets,
                "blockName" => $blockName
            ]
        )->render();
    }
    
    public function printReportDeetsBlockCols($deets, $blockName = '', $cols = 2, $curr = null)
    {
        $deets = $this->chkDeets($deets);
        $deetCols = $deetsTots = $deetsTotCols = [];
        $colChars = (($cols == 2) ? 37 : (($cols == 3) ? 24 : 16));
        if (sizeof($deets) > 0) {
            foreach ($deets as $i => $deet) {
                $size = 0; //strlen($deet[0]);
                if (sizeof($deet) > 1 && isset($deet[1]) && $size < strlen($deet[1])) {
                    $size = strlen($deet[1]);
                }
                if ($size > $colChars) {
                    $size -= $colChars;
                } else {
                    $size = 0;
                }
                $size = ($size/$colChars)+2;
                if ($i == 0) {
                    $deetsTots[$i] = $size;
                } else {
                    $deetsTots[$i] = $deetsTots[$i-1]+$size;
                }
            }
            for ($c = 0; $c < $cols; $c++) {
                $deetCols[$c] = [];
                $deetsTotCols[$c] = [
                    (($c/$cols)*$deetsTots[sizeof($deetsTots)-1]), 
                    -3
                ];
            }
            $c = $deetsTotCols[0][1] = 0;
            foreach ($deets as $i => $deet) {
                $chk = 1+$c;
                if ($chk < $cols 
                    && $deetsTotCols[$chk][1] < 0 
                    && $deetsTotCols[$chk][0] < $deetsTots[$i]
                    && sizeof($deetCols[$c]) > 0) {
                    $deetsTotCols[$chk][1] = $i;
                    $c++;
                }
                $deetCols[$c][] = $deet;
            }
        }
        return view(
            'vendor.survloop.reports.inc-deets-cols', 
            [
                "nID"       => $curr->nID,
                "nIDtxt"    => $curr->nIDtxt,
                "deetCols"  => $deetCols,
                "blockName" => $blockName,
                "colWidth"  => $GLOBALS["SL"]->getColsWidth($cols)
            ]
        )->render();
    }
    
    public function printReportDeetsVertProg($deets, $blockName = '', $curr = null)
    {
        $last = 0;
        if (sizeof($deets) > 0) {
            foreach ($deets as $i => $deet) {
                if (isset($deet[1]) && intVal($deet[1]) > 0) {
                    $last = $i;
                }
            }
        }
        return view(
            'vendor.survloop.reports.inc-deets-vert-prog', 
            [
                "nID"       => $curr->nID,
                "nIDtxt"    => $curr->nIDtxt,
                "deets"     => $deets,
                "blockName" => $blockName,
                "last"      => $last
            ]
        )->render();
    }
    
    public function byID(Request $request, $coreID, $coreSlug = '', $skipWrap = false, $skipPublic = false)
    {
        $this->survLoopInit($request, '/report/' . $coreID);
        if (!$skipPublic) {
            $coreID = $GLOBALS["SL"]->chkInPublicID($coreID);
        }
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        if ($request->has('hideDisclaim') 
            && intVal($request->hideDisclaim) == 1) {
            $this->hideDisclaim = true;
        }
        $this->v["isPublicRead"] = true;
        $this->v["content"] = $this->printFullReport();
        if ($skipWrap) {
            return $this->v["content"];
        }
        $this->v["footOver"] = $this->printNodePageFoot();
        return $GLOBALS["SL"]->swapSessMsg(
            view('vendor.survloop.master', $this->v)->render()
        );
    }
    
    public function printReports(Request $request, $full = true)
    {
        $this->survLoopInit($request, '/reports-full/' . $this->treeID);
        $this->loadTree();
        $ret = '';
        if ($request->has('i') && intVal($request->get('i')) > 0) {
            $ret .= $this->printReportsRecordPublic($request->get('i'), $full);
        } elseif ($request->has('ids') && trim($request->get('ids')) != '') {
            foreach ($GLOBALS["SL"]->mexplode(',', $request->get('ids')) as $id) {
                $ret .= $this->printReportsRecordPublic($id, $full);
            }
        } elseif ($request->has('rawids') && trim($request->get('rawids')) != '') {
            foreach ($GLOBALS["SL"]->mexplode(',', $request->get('rawids')) as $id) {
                $ret .= $this->printReportsRecord($id, $full);
            }
        } else {
            $this->getAllPublicCoreIDs();
            $this->initSearcher();
            $this->searcher->getSearchFilts();
            $this->searcher->processSearchFilts();
            if (sizeof($this->searcher->allPublicFiltIDs) > 0) {
                foreach ($this->searcher->allPublicFiltIDs as $i => $coreID) {
                    if (!isset($this->searchOpts["limit"]) 
                        || intVal($this->searchOpts["limit"]) == 0
                        || $i < $this->searchOpts["limit"]) {
                        if ($GLOBALS["SL"]->tblHasPublicID($GLOBALS["SL"]->coreTbl)) {
                            $ret .= $this->printReportsRecordPublic($coreID, $full);
                        } else {
                            $ret .= $this->printReportsRecord($coreID, $full);
                        }
                    }
                }
            }
        }
        if ($ret == '') {
            $ret = '<p><i class="slGrey">None found.</i></p>';
        }
        return $ret;
    }
    
    public function printReportsRecord($coreID = -3, $full = true)
    {
        if (!$this->isPublished($GLOBALS["SL"]->coreTbl, $coreID) 
            && !$this->isCoreOwner($coreID) 
            && (!$this->v["user"] || !$this->v["user"]->hasRole('administrator|staff'))) {
            return $this->unpublishedMessage($GLOBALS["SL"]->coreTbl);
        }
        if ($full) {
            return '<div class="reportWrap">' 
                . $this->byID($GLOBALS["SL"]->REQ, $coreID, '', true, true) . '</div>';
        }
        return $this->printReportsPrev($coreID);
    }
    
    public function printReportsRecordPublic($coreID = -3, $full = true)
    {
        if (!$this->isPublishedPublic($GLOBALS["SL"]->coreTbl, $coreID) 
            && !$this->isCoreOwnerPublic($coreID) 
            && (!$this->v["user"] || !$this->v["user"]->hasRole('administrator|staff'))) {
            return $this->unpublishedMessage($GLOBALS["SL"]->coreTbl);
        }
        if ($full) {
            return '<div class="reportWrap">' 
                . $this->byID($GLOBALS["SL"]->REQ, $coreID, '', true) . '</div>';
        }
        return $this->printReportsPrev($coreID);
    }
    
    public function printReportsPrev($coreID = -3)
    {
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        return '<div id="reportPreview' . $coreID . '" class="reportPreview">' 
            . $this->printPreviewReport() . '</div>';
    }
    
    public function unpublishedMessage($coreTbl = '')
    {
        if ($this->corePublicID <= 0) {
            return '<!-- -->';
        }
        return '<div class="well well-lg">#' . $this->corePublicID 
            . ' is no longer published.</div>';
    }
    
    public function xmlAllAccess()
    {
        return true; 
    }
    
    public function xmlAll(Request $request)
    {
        $page = '/' . $GLOBALS["SL"]->treeRow->tree_slug . '-xml-all';
        $this->survLoopInit($request, $page);
        if (!$this->xmlAllAccess()) {
            return 'Sorry, access not permitted.';
        }
        $limit = $GLOBALS["SL"]->getLimit();
        $this->loadXmlMapTree($request);
        $this->v["nestedNodes"] = '';
        $coreTbl = $GLOBALS["SL"]->xmlTree["coreTbl"];
        $allIDs = $this->getAllPublicCoreIDs($coreTbl);
        if (sizeof($allIDs) > 0) {
            foreach ($allIDs as $i => $coreID) {
                if ($i < $limit) {
                    $this->loadAllSessData($coreTbl, $coreID);
                    if (isset($this->sessData->dataSets[$coreTbl]) 
                        && sizeof($this->sessData->dataSets[$coreTbl]) > 0) {
                        $this->v["nestedNodes"] .= $this->genXmlReportNode(
                            $this->xmlMapTree->rootID, 
                            $this->xmlMapTree->nodeTiers, 
                            $this->sessData->dataSets[$coreTbl][0]
                        );
                    }
                }
            }
        }
        $view = view('vendor.survloop.admin.tree.xml-report', $this->v)->render();
        return Response::make($view, '200')->header('Content-Type', 'text/xml');
    }
    
    public function xmlByID(Request $request, $coreID, $coreSlug = '')
    {
        $page = '/' . $GLOBALS["SL"]->treeRow->tree_slug . '/read-' . $coreID . '/xml';
        $this->survLoopInit($request, $page);
        $GLOBALS["SL"]->pageView = 'public';
        $coreID = $GLOBALS["SL"]->chkInPublicID($coreID);
        $this->loadXmlMapTree($request);
        if ($GLOBALS["SL"]->xmlTree["coreTbl"] == $GLOBALS["SL"]->coreTbl) {
            $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        } else { // XML core table is different from main tree
            $this->loadSessInfo($GLOBALS["SL"]->xmlTree["coreTbl"]);
            $this->loadAllSessData($GLOBALS["SL"]->xmlTree["coreTbl"], $coreID);
        }
        $this->checkPageViewPerms();
        if ($GLOBALS["SL"]->dataPerms == 'none') {
            return '';
        }
        return $this->getXmlID($request, $coreID, $coreSlug);
    }
    
    public function xmlFullByID(Request $request, $coreID, $coreSlug = '')
    {
        $page = '/' . $GLOBALS["SL"]->treeRow->tree_slug . '/read-' . $coreID . '/full-xml';
        $this->survLoopInit($request, $page);
        $coreID = $GLOBALS["SL"]->chkInPublicID($coreID); 
        $this->loadXmlMapTree($request);
        $GLOBALS["SL"]->pageView = 'full';
        if ($GLOBALS["SL"]->xmlTree["coreTbl"] == $GLOBALS["SL"]->coreTbl) {
            $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        } else { // XML core table is different from main tree
            $this->loadSessInfo($GLOBALS["SL"]->xmlTree["coreTbl"]);
            $this->loadAllSessData($GLOBALS["SL"]->xmlTree["coreTbl"], $coreID);
        }
        $this->checkPageViewPerms();
        if ($GLOBALS["SL"]->dataPerms == 'none') {
            return '';
        }
        return $this->getXmlID($request, $coreID, $coreSlug);
    }
    
    public function getXmlID(Request $request, $coreID, $coreSlug = '')
    {
        $this->maxUserView();
        $this->xmlMapTree->v["view"] = $GLOBALS["SL"]->pageView;
        if (isset($GLOBALS["fullAccess"]) 
            && $GLOBALS["fullAccess"] 
            && !in_array($GLOBALS["SL"]->pageView, ['full', 'full-xml'])) {
            $this->v["content"] = $this->errorDeniedFullXml();
            return view('vendor.survloop.master', $this->v);
        }
        return $this->genXmlReport($request);
    }
    
    public function getXmlExample(Request $request)
    {
        $page = '/' . $GLOBALS["SL"]->treeRow->tree_slug . '-xml-example';
        $this->survLoopInit($request, $page);
        $coreID = 1;
        $optXmlTree = "tree-" . $GLOBALS["SL"]->xmlTree["id"] . "-example";
        $optTree = "tree-" . $GLOBALS["SL"]->treeID . "-example";
        if (isset($GLOBALS["SL"]->sysOpts[$optTree])) {
            $coreID = intVal($GLOBALS["SL"]->sysOpts[$optTree]);
        }
        if ($coreID <= 0 && isset($GLOBALS["SL"]->sysOpts[$optXmlTree])) {
            $coreID = intVal($GLOBALS["SL"]->sysOpts[$optXmlTree]);
        }
        $model = $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->xmlTree["coreTbl"]);
        eval("\$chk = " . $model . "::find(" . $coreID . ");");
        if ($chk) {
            return $this->xmlByID($request, $coreID);
        }
        return $this->redir('/xml-schema');
    }
    
    protected function reloadStats($coreIDs = [])
    {
        return true;
    }
    
    public function retrieveUpload(Request $request, $cid = -3, $upID = '')
    {
        if ($cid <= 0) {
            return '';
        }
        $this->survLoopInit($request, '');
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $cid);
        $GLOBALS["SL"]->pageView = 'full'; // changed from 'sensitive';
        return $this->retrieveUploadFile($upID);
    }
    
    protected function errorDeniedFullPdf()
    {
        $url = $GLOBALS["SL"]->treeRow->tree_slug . '/read-' . $this->coreID;
        return view(
            'vendor.survloop.reports.inc-error-denied-full-pdf', 
            [ "url" => $url ]
        );
    }
    
    protected function errorDeniedFullXml()
    {
        return $this->errorDeniedFullPdf();
    }
    
    protected function hasAjaxWrapPrinting()
    {
        return (!$this->hasREQ 
            && (!$GLOBALS["SL"]->REQ->has('ajax') 
                || intVal($GLOBALS["SL"]->REQ->get('ajax')) == 0));
    }
    
    protected function hasFrameLoad()
    {
        return ($GLOBALS["SL"]->REQ->has('frame') 
            && intVal($GLOBALS["SL"]->REQ->get('frame')) == 1);
    }



    
    public function ajaxGraph(Request $request, $gType = '', $nID = -3)
    {
        $this->survLoopInit($request, '');
        $this->v["currNode"] = new TreeNodeSurv;
        $this->v["currNode"]->fillNodeRow($nID);
        $this->v["currGraphID"] = 'nGraph' . $nID;
        if ($this->v["currNode"] 
            && trim($gType) != ''
            && isset($this->v["currNode"]->nodeRow->node_id)) {
            $this->getAllPublicCoreIDs();
            $this->searcher->getSearchFilts();
            $this->searcher->processSearchFilts();
            $this->v["graphDataPts"] = $this->v["graphMath"] = $rows = $rowsFilt = [];
            if (sizeof($this->searcher->allPublicFiltIDs) > 0) {
                if (isset($this->v["currNode"]->extraOpts["y-axis"]) 
                    && intVal($this->v["currNode"]->extraOpts["y-axis"]) > 0) {
                    $fldRec = SLFields::find($this->v["currNode"]->extraOpts["y-axis"]);
                    $lab1Rec = SLFields::find($this->v["currNode"]->extraOpts["lab1"]);
                    $lab2Rec = SLFields::find($this->v["currNode"]->extraOpts["lab2"]);
                    if ($fldRec && isset($fldRec->fld_table)) {
                        $tbl = $GLOBALS["SL"]->tbl[$fldRec->fld_table];
                        $tblAbbr = $GLOBALS["SL"]->tblAbbr[$tbl];
                        $fldName = $tblAbbr . $fldRec->fld_name;
                        $lab1Fld = (($lab1Rec && isset($lab1Rec->fld_name)) 
                            ? $tblAbbr . $lab1Rec->fld_name : '');
                        $lab2Fld = (($lab2Rec && isset($lab2Rec->fld_name)) 
                            ? $tblAbbr . $lab2Rec->fld_name : '');
                        if ($tbl == $GLOBALS["SL"]->coreTbl) {
                            eval("\$rows = " . $GLOBALS["SL"]->modelPath($tbl) 
                                . "::select('" . $tblAbbr . "ID', '" . $fldName . "'" 
                                . ((trim($lab1Fld) != '') ? ", '" . $lab1Fld . "'" : "") 
                                . ((trim($lab2Fld) != '') ? ", '" . $lab2Fld . "'" : "")
                                . ")->where('" . $fldName . "', 'NOT LIKE', '')->where('"
                                . $fldName . "', 'NOT LIKE', 0)->whereIn('" . $tblAbbr 
                                . "id', \$this->searcher->allPublicFiltIDs)->orderBy('" 
                                . $fldName . "', 'asc')->get();");
                        } else {
                            //eval("\$rows = " . $GLOBALS["SL"]->modelPath($tbl) 
                            //    . "::orderBy('" . $isBigSurvLoop[1] 
                            //    . "', '" . $isBigSurvLoop[2] . "')->get();");
                        }
                        if ($rows->isNotEmpty()) {
                            if (isset($this->v["currNode"]->extraOpts["conds"]) 
                                && strpos('#', $this->v["currNode"]->extraOpts["conds"]) !== false) {
                                $this->loadCustLoop($request);
                                foreach ($rows as $i => $row) {
                                    $this->custReport->loadAllSessData(
                                        $GLOBALS["SL"]->coreTbl, 
                                        $row->getKey()
                                    );
                                    if ($this->custReport->chkConds(
                                        $this->v["currNode"]->extraOpts["conds"])) {
                                        $rowsFilt[] = $row;
                                    }
                                }
                            } else {
                                $rowsFilt = $rows;
                            }
                        }
                        if (sizeof($rowsFilt) > 0) {
                            if ($this->v["currNode"]->nodeType == 'Bar Graph') {
                                $this->v["graphMath"]["absMin"] = $rows[0]->{ $fldName };
                                $this->v["graphMath"]["absMax"] = $rows[sizeof($rows)-1]->{ $fldName };
                                $this->v["graphMath"]["absRange"] = $this->v["graphMath"]["absMax"]
                                        -$this->v["graphMath"]["absMin"];
                                foreach ($rows as $i => $row) {
                                    $lab = '';
                                    if (trim($lab1Fld) != '' 
                                        && isset($row->{ $lab1Fld })) { 
                                        $lab .= (($lab1Rec->fld_type == 'DOUBLE') 
                                            ? $GLOBALS["SL"]->sigFigs($row->{ $lab1Fld }) 
                                            : $row->{ $lab1Fld }) . ' ';
                                        if (trim($lab2Fld) != '' 
                                            && isset($row->{ $lab2Fld })) { 
                                            $lab .= (($lab2Rec->fld_type == 'DOUBLE') 
                                               ? $GLOBALS["SL"]->sigFigs($row->{ $lab2Fld }) 
                                               : $row->{ $lab2Fld }) .' ';
                                        }
                                    }
                                    $perc = ((1+$i)/sizeof($rows));
                                    $this->v["graphDataPts"][] = [
                                        "id"  => $row->getKey(),
                                        "val" => (($fldRec->fld_type == 'DOUBLE') 
                                            ? $GLOBALS["SL"]->sigFigs($row->{ $fldName }, 4) 
                                            : $row->{ $fldName }), 
                                        "lab" => trim($lab),
                                        "dsc" => '',
                                        "bg"  => $GLOBALS["SL"]->printColorFade( $perc, 
                                            $this->v["currNode"]->extraOpts["clr1"], 
                                            $this->v["currNode"]->extraOpts["clr2"], 
                                            $this->v["currNode"]->extraOpts["opc1"], 
                                            $this->v["currNode"]->extraOpts["opc2"] ), 
                                        "brd" => $GLOBALS["SL"]->printColorFade( $perc, 
                                            $this->v["currNode"]->extraOpts["clr1"], 
                                            $this->v["currNode"]->extraOpts["clr2"] )
                                    ];
                                }
                            }
                        }
                    }
                }
                $this->v["graph"] = [
                    "dat" => '', 
                    "lab" => '', 
                    "bg"  => '', 
                    "brd" => '' 
                ];
                if (sizeof($this->v["graphDataPts"]) > 0) {
                    foreach ($this->v["graphDataPts"] as $cnt => $dat) {
                        $cma = (($cnt > 0) ? ", " : "");
                        $this->v["graph"]["dat"] .= $cma . $dat["val"];
                        $this->v["graph"]["lab"] .= $cma . "\"" . $dat["lab"] . "\"";
                        $this->v["graph"]["bg"]  .= $cma . "\"" . $dat["bg"]  . "\"";
                        $this->v["graph"]["brd"] .= $cma . "\"" . $dat["brd"] . "\"";
                    }
                }
                return view('vendor.survloop.reports.graph-bar', $this->v);
            }
        }
        $this->v["graphFail"] = true;
        return view('vendor.survloop.reports.graph-bar', $this->v);
    }

    
}
