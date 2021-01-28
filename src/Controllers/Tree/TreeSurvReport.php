<?php
/**
  * TreeSurvReport is a mid-level class with functions related to generating reports within a tree.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.19
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\SLSessEmojis;
use App\Models\SLTree;
use RockHopSoft\Survloop\Controllers\SurvloopPDF;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvBasicNav;

class TreeSurvReport extends TreeSurvBasicNav
{
    public function printPreviewReportCustom($isAdmin = false, $view = '')
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
    
    public function byID(Request $request, $coreID, $coreSlug = '', $skipWrap = false, $skipPublic = false)
    {
        ini_set('max_execution_time', 90);
        $this->survloopInit($request, '/report/' . $coreID);
        if (!$skipPublic) {
            $coreID = $GLOBALS["SL"]->chkInPublicID($coreID);
        }
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $coreID);
        $this->checkPageViewPerms();
        if ($request->has('hideDisclaim') && intVal($request->hideDisclaim) == 1) {
            $this->hideDisclaim = true;
        }
        if ($GLOBALS["SL"]->isPdfView()) {
            if ($this->chkCachePdfByID()) {
                return $this->getCachePdfByID();
            } elseif ($request->has('gen-pdf')) {
                return $this->genPdfByID();
            }
        }
        if ($GLOBALS["SL"]->isPdfView()
            && !$GLOBALS["SL"]->REQ->has('pdf-attached')) {
            if (sizeof($this->prevUploadsPDF()) > 0) {
                return '<script type="text/javascript"> setTimeout("'
                . 'window.location=\'?pdf=1&refresh=1&pdf-attached=1' 
                . '\'", 10); </script>';
            }
        }

        $this->v["isPublicRead"] = true;
        $this->v["content"] = $this->index($request);
        if ($GLOBALS["SL"]->isPdfView()) {
            return $this->prepPdfByID();
        }
        if ($skipWrap) {
            return $this->v["content"];
        }
        $this->v["footOver"] = $this->printNodePageFoot();
        return $GLOBALS["SL"]->swapSessMsg(
            view('vendor.survloop.master', $this->v)->render()
        );
    }
    
    public function chkCachePdfByID($skipPDfViewCheck = false)
    {
        $isPdfView = $skipPDfViewCheck;
        if (!$isPdfView) {
            $isPdfView = $GLOBALS["SL"]->isPdfView();
        }
        $this->loadPdfByID();
        return ($isPdfView
            && file_exists($this->v["pdf-file"]) 
            && !$GLOBALS["SL"]->REQ->has('refresh'));
    }
    
    protected function loadPdfByID()
    {
        $this->v["pdf-gen"] = new SurvloopPDF($GLOBALS["SL"]->coreTbl);
        $this->v["pdf-file"] = $GLOBALS["SL"]->coreTbl . '-' 
            . $this->corePublicID . '-' . $GLOBALS["SL"]->pageView 
            . '-' . $GLOBALS["SL"]->dataPerms . '.pdf';
        $this->v["pdf-file"] = $this->v["pdf-gen"]->getPdfFile($this->v["pdf-file"]);
        $fileDeliver = $this->loadPdfFilename();
        if (trim($fileDeliver) == '') {
            $fileDeliver = $this->v["pdf-file"];
        }
        $this->v["pdf-gen"]->setOutput($this->v["pdf-file"], $fileDeliver);
        return $this->v["pdf-file"];
    }
    
    protected function loadPdfFilename()
    {
        return '';
    }
    
    // Override PDF filename to be used for delivery to user.
    public function customPdfFilename()
    {
        $GLOBALS["SL"]->x["pdfFilename"] = '';
    }
    
    public function getCachePdfByID()
    {
        return $this->v["pdf-gen"]->pdfResponse($this->v["pdf-file"]);
    }
    
    public function prepPdfByID()
    {
        if (!isset($this->v["content"]) || trim($this->v["content"]) == '') {
            $this->v["isPublicRead"] = true;
            $this->v["content"] = $this->index($GLOBALS["SL"]->REQ);
        }
        return $this->v["pdf-gen"]->storeHtml(
            view('vendor.survloop.master', $this->v)->render(), 
            $this->v["pdf-file"]
        );
    }
    
    public function genPdfByID()
    {
        $fileHtml = str_replace('.pdf', '.html', $this->v["pdf-file"]);
        if (!file_exists($fileHtml)) {
            return $this->prepPdfByID();
        }
        $this->v["pdf-gen"]->genCorePdf($this->v["pdf-file"]);
        return $this->v["pdf-gen"]->pdfResponse($this->v["pdf-file"]);
    }
    
    public function ajaxEmojiTag(Request $request, $recID = -3, $defID = -3)
    {
        if ($recID <= 0) {
            return '';
        }
        $this->survloopInit($request, '');
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
            $admPower = ($this->v["user"] && $this->isStaffOrAdmin());
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
        return view(
            'vendor.survloop.reports.inc-glossary', 
            [
                "glossaryList" => $this->v["glossaryList"]
            ]
        )->render();
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
            $GLOBALS["SL"]->sysOpts['meta-title'] 
                = $this->swapSeo($GLOBALS["SL"]->sysOpts['meta-title']);
            $GLOBALS["SL"]->sysOpts['meta-desc'] 
                = $this->swapSeo($GLOBALS["SL"]->sysOpts['meta-desc']);
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
    
    public function printReportDeetsBlock($deets, $blockName = '', $curr = null, $blockDesc = '')
    {
        $deets = $this->chkDeets($deets);
        return view(
            'vendor.survloop.reports.inc-deets', 
            [
                "nID"       => $curr->nID,
                "nIDtxt"    => $curr->nIDtxt,
                "deets"     => $deets,
                "blockName" => $blockName,
                "blockDesc" => $blockDesc
            ]
        )->render();
    }
    
    public function printReportDeetsBlockCols($deets, $blockName = '', $cols = 2, $curr = null, $blockDesc = '')
    {
        $deets = $this->chkDeets($deets);
        $deetCols = $deetsTots = $deetsTotCols = [];
        $colChars = (($cols == 2) ? 37 : (($cols == 3) ? 24 : 16));
        $colChars = round(2.4*$colChars);
        if (sizeof($deets) > 0) {
            foreach ($deets as $i => $deet) {
                // Get longest character count between question and answer
                $size = 0;
                if (sizeof($deet) > 1) {
                    $size = strlen($deet[0]);
                    if ($size < strlen($deet[1])) {
                        $size = strlen($deet[1]);
                    }
                }
                // Estimate line count from character count
                $size = ($size/$colChars);
                if ($i == 0) {
                    $deetsTots[$i] = $size;
                } else {
                    $deetsTots[$i] = $deetsTots[$i-1]+$size;
                }
            }
            // Initiale output array, and tracking helper
            $totSize = $deetsTots[sizeof($deetsTots)-1];
            for ($c = 0; $c < $cols; $c++) {
                $deetCols[$c] = [];
                $deetsTotCols[$c] = [
                    "cutoff" => ((1.3*($c/$cols))*$totSize),
                    "ind"    => -3
                ];
            }
            $c = 0;
            $deetsTotCols[$c][1] = 0;
            foreach ($deets as $i => $deet) {
                // Check if we should switch columns
                $chk = 1+$c;
                if ($chk < $cols 
                    && $deetsTotCols[$chk]["ind"] < 0 
                    && $deetsTotCols[$chk]["cutoff"] < $deetsTots[$i]
                    && sizeof($deetCols[$c]) > 0) {
                    $deetsTotCols[$chk]["ind"] = $i;
                    $c++;
                }
                // Add deet to column
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
                "blockDesc" => $blockDesc,
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
                "last"      => $last,
                "dateType"  => (($GLOBALS["SL"]->pageView == 'public') ? 'F Y' : 'n/j/y')
            ]
        )->render();
    }
    
    public function printReports(Request $request, $full = true)
    {
        $this->survloopInit($request, '/reports-full/' . $this->treeID);
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
            $ret .= $this->printReportsDefault($full);
        }
        if ($ret == '') {
            $ret = '<p><i class="slGrey">None found.</i></p>';
        }
        return $ret;
    }
    
    public function printReportsDefault($full = true)
    {
        $ret = '';
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
        return $ret;
    }
    
    public function printReportsRecord($coreID = -3, $full = true)
    {
        if (!$this->isPublished($GLOBALS["SL"]->coreTbl, $coreID) 
            && !$this->isCoreOwner($coreID) 
            && (!$this->v["user"] || !$this->isStaffOrAdmin())) {
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
            && (!$this->v["user"] || !$this->isStaffOrAdmin())) {
            return $this->unpublishedMessage($GLOBALS["SL"]->coreTbl);
        }
        if ($full) {
            return '<div class="reportWrap">' 
                . $this->byID($GLOBALS["SL"]->REQ, $coreID, '', true) 
                . '</div>';
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
        return '<!-- <div class="well well-lg">#' . $this->corePublicID 
            . ' is no longer published.</div> -->';
    }
    
    protected function xmlAllAccess()
    {
        return true; 
    }
    
    public function xmlAll(Request $request)
    {
        $xtras = '';
        if ($request->has('state') && trim($request->get('state')) != '') {
            $xtras .= '&state=' . trim($request->get('state'));
        }
        $page 
            = $pageBasic
            = '/api/all/' . $GLOBALS["SL"]->treeRow->tree_slug . '/xml';
        $this->survloopInit($request, $page);
        if (!$this->xmlAllAccess()) {
            return 'Sorry, access not permitted.';
        }
        $limit  = $GLOBALS["SL"]->getLimit(200);
        $start = $GLOBALS["SL"]->getStart();
        $page .= '?limit=' . $limit . '&start=' . $start . $xtras;
        $content = $GLOBALS["SL"]->chkCache($page, 'api', $GLOBALS["SL"]->treeID);
        // $GLOBALS["SL"]->hasCache($page, 'api', $GLOBALS["SL"]->treeID)
        if ($content != '' && !$request->has('refresh')) {
            return Response::make($content, '200')
                ->header('Content-Type', 'text/xml');
        }
        $this->v["apiLoadLinks"] = 'Current: ' 
            . $GLOBALS['SL']->sysOpts["app-url"] . $page;

        $this->loadXmlMapTree($request);

        if (sizeof($GLOBALS["SL"]->dataLoops) == 0
            && sizeof($GLOBALS["SL"]->dataSubsets) == 0
            && sizeof($GLOBALS["SL"]->dataHelpers) == 0) {
            $tblI = $GLOBALS["SL"]->tblI[$GLOBALS["SL"]->coreTbl];
            $treeChk = SLTree::where('tree_type', 'Survey')
                ->where('tree_database', $GLOBALS["SL"]->dbID)
                ->where('tree_core_table', $tblI)
                ->orderBy('tree_id', 'asc')
                ->first();
            if ($treeChk && isset($treeChk->tree_id)) {
                $GLOBALS["SL"]->loadDataMap($treeChk->tree_id);
            }
        }

        $this->v["nestedNodes"] = $this->v["nextPage"] = '';
        $coreTbl = $GLOBALS["SL"]->xmlTree["coreTbl"];
        $allIDs = $this->getAllPublicCoreIDs($coreTbl);
        $this->v["tot"] = sizeof($allIDs);
        if (sizeof($allIDs) > 0) {
            foreach ($allIDs as $i => $coreID) {
                if ($i >= $start && $i < ($start+$limit)) {
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
            if ($limit < sizeof($allIDs)) {
                $this->v["nextPage"] = $GLOBALS["SL"]->sysOpts["app-url"] 
                    . $pageBasic . '?limit=' . $limit . '&start=' 
                    . ($start+$limit) . $xtras;
                $this->v["apiLoadLinks"] .= "\n" . 'Next: ' . $this->v["nextPage"];
            }
        }
        $view = view('vendor.survloop.admin.tree.xml-report', $this->v)->render();
        $GLOBALS["SL"]->putCache($page, $view, 'api', $GLOBALS["SL"]->treeID);
        return Response::make($view, '200')
            ->header('Content-Type', 'text/xml');
    }
    
    public function xmlByID(Request $request, $coreID, $coreSlug = '')
    {
        $page = '/' . $GLOBALS["SL"]->treeRow->tree_slug 
            . '/readi-' . $coreID . '/xml';
        $this->survloopInit($request, $page);
        $GLOBALS["SL"]->pageView = 'public';
        if (!$this->xmlAccess()) {
            return 'Sorry, access not permitted.';
        }
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
        $page = '/' . $GLOBALS["SL"]->treeRow->tree_slug 
            . '/readi-' . $coreID . '/full-xml';
        $this->survloopInit($request, $page);
        $GLOBALS["SL"]->pageView = 'full-xml';
        if (!$this->xmlAccess()) {
            return 'Sorry, access not permitted.';
        }
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
    
    public function getXmlID(Request $request, $coreID, $coreSlug = '')
    {
//echo 'getXmlID( view: ' . $GLOBALS["SL"]->pageView . ', perms: ' . $GLOBALS["SL"]->dataPerms;
        //$this->maxUserView();
//echo 'getXmlID( view: ' . $GLOBALS["SL"]->pageView . ', perms: ' . $GLOBALS["SL"]->dataPerms; exit;
        $this->xmlMapTree->v["view"] = $GLOBALS["SL"]->pageView;
        if (isset($GLOBALS["SL"]->x["fullAccess"]) 
            && $GLOBALS["SL"]->x["fullAccess"] 
            && !in_array($GLOBALS["SL"]->pageView, ['full', 'full-xml'])) {
            $this->v["content"] = $this->errorDeniedFullXml();
            return view('vendor.survloop.master', $this->v);
        }
        return $this->genXmlReport($request);
    }
    
    public function getXmlExample(Request $request)
    {
        $page = '/' . $GLOBALS["SL"]->treeRow->tree_slug . '-xml-example';
        $this->survloopInit($request, $page);
        $coreID = $this->getXmlExampleID();
        $optTree = $optXmlTree = "tree-" . $GLOBALS["SL"]->treeID . "-example";
        if ($coreID <= 0 && isset($GLOBALS["SL"]->sysOpts[$optTree])) {
            $coreID = intVal($GLOBALS["SL"]->sysOpts[$optTree]);
        }
        if ($coreID <= 0 
            && $optXmlTree != $optTree
            && isset($GLOBALS["SL"]->xmlTree["id"])) {
            $optXmlTree = "tree-" . $GLOBALS["SL"]->xmlTree["id"] . "-example";
            if (isset($GLOBALS["SL"]->sysOpts[$optXmlTree])) {
                $coreID = intVal($GLOBALS["SL"]->sysOpts[$optXmlTree]);
            }
        }
        if ($coreID <= 0) {
            $optXmlTree = "tree-" . $GLOBALS["SL"]->getXmlSurveyID() . "-example";
            if (isset($GLOBALS["SL"]->sysOpts[$optXmlTree])) {
                $coreID = intVal($GLOBALS["SL"]->sysOpts[$optXmlTree]);
            }
        }
        if (isset($GLOBALS["SL"]->xmlTree["coreTbl"])) {
            $model = $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->xmlTree["coreTbl"]);
            eval("\$chk = " . $model . "::find(" . $coreID . ");");
            if ($chk) {
                return $this->xmlByID($request, $coreID);
            }
        }
        if (isset($GLOBALS["SL"]->coreTbl)) {
            $model = $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl);
            eval("\$chk = " . $model . "::find(" . $coreID . ");");
            if ($chk) {
                return $this->xmlByID($request, $coreID);
            }
        }
        return $this->redir('/xml-schema');
    }
    
    /**
     * Specify the example record for individual XML exports.
     *
     * @return int
     */
    protected function getXmlExampleID()
    {
        return -3;
    }
    
    protected function reloadStats($coreIDs = [])
    {
        return true;
    }
    
    public function retrieveUpload(Request $request, $cid = -3, $upID = '', $refresh = false)
    {
        if ($cid <= 0) {
            return '';
        }
        $this->survloopInit($request, '');
        $GLOBALS["SL"]->pageView = 'full';
        $this->loadAllSessData($GLOBALS["SL"]->coreTbl, $cid);
        return $this->retrieveUploadFile($upID, $refresh);
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
                || intVal($GLOBALS["SL"]->REQ->get('ajax')) == 0)
            && !$GLOBALS["SL"]->isPdfView());
    }
    
    protected function hasFrameLoad()
    {
        return ($GLOBALS["SL"]->REQ->has('frame') 
            && intVal($GLOBALS["SL"]->REQ->get('frame')) == 1);
    }



    
    public function ajaxGraph(Request $request, $gType = '', $nID = -3)
    {
        $this->survloopInit($request, '');
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
                            //    . "::orderBy('" . $isBigSurvloop[1] 
                            //    . "', '" . $isBigSurvloop[2] . "')->get();");
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
