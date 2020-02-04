<?php
/**
  * TreeSurvUpload is a mid-level class atop SurvLoop's branching tree, specifically for 
  * uploading functionality in surveys and pages.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.0.18
  */
namespace SurvLoop\Controllers\Tree;

use Auth;
use Storage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\File\File;
use App\Models\User;
use App\Models\SLUploads;
use App\Models\SLNode;
use App\Models\SLNodeResponses;
use SurvLoop\Controllers\Tree\TreeNodeSurv;
use SurvLoop\Controllers\Tree\TreeSurv;

class TreeSurvUpload extends TreeSurv
{
    public $uploadTypes      = [];
    protected $uploads       = [];
    protected $upDeets       = [];
    protected $upTree        = -3;
    
    protected function getVidType($treeID)
    {
        $this->v["vidTypeID"] = -1;
        if (isset($GLOBALS["SL"]->sysOpts["tree-" . $treeID . "-upload-types"])) {
            $defSet = $GLOBALS["SL"]->sysOpts["tree-" . $treeID . "-upload-types"];
            $this->v["vidTypeID"] = $GLOBALS["SL"]->def->getID($defSet, 'Video');
        }
        return $this->v["vidTypeID"];
    }
    
    protected function genRandStr($len)
    {
        $part1 = str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
        $part1 = substr($part1, 0, 1);
        $part2 = str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
        $part2 = substr($part2, 0, ($len-1));
        return $part1 . $part2;
    }
    
    protected function checkRandStr($tbl, $fld, $str)
    {
        $model = trim($GLOBALS["SL"]->modelPath($tbl));
        if ($model == '') {
            return null;
        }
        $modelObj = [];
//echo 'checkRandStr(' . $tbl . ', ' . $fld . ', ' . $str . ' - ' . $model . '<br />'; exit;
        eval("\$modelObj = " . $model 
            . "::where('" . $fld . "', '" . $str . "')->get();");
        return $modelObj->isEmpty();
    }
    
    protected function getRandStr($tbl, $fld, $len)
    {
        $str = $this->genRandStr($len);
        $model = trim($GLOBALS["SL"]->modelPath($tbl));
        if ($model == '') {
            return $str;
        }
        while (!$this->checkRandStr($tbl, $fld, $str)) {
            $str = $this->genRandStr($len);
        }
        return $str;
    }
    
    protected function getUpTree()
    {
        if ($this->upTree > 0) {
            return $this->upTree;
        }
        return $GLOBALS["SL"]->chkReportCoreTree();
    }
    
    
    //////////////////////////////////////////////////////////////////////
    //  START FILE UPLOADING FUNCTIONS
    //////////////////////////////////////////////////////////////////////
    
    protected function loadUploadTypes()
    {
        if ($this->uploadTypes && $this->uploadTypes->isNotEmpty()) {
            return $this->uploadTypes;
        }
        //$treeID = $this->getUpTree();
        $treeID = $this->treeID;
        $upType = "tree-" . $treeID . "-upload-types";
        if (isset($GLOBALS["SL"]->sysOpts[$upType])) {
            $this->uploadTypes = $GLOBALS["SL"]->def->getSet($GLOBALS["SL"]->sysOpts[$upType]);
        }
        if (!$this->uploadTypes || $this->uploadTypes->isEmpty()) {
            $this->uploadTypes = $GLOBALS["SL"]->def->getSet('Upload Types');
        }
        return $this->uploadTypes;
    }
    
    protected function checkBaseFolders()
    {
        $this->checkFolder('../storage/app/up/avatar');
        $this->checkFolder('../storage/app/up/evidence/' . date("Y/m/d"));
        return true;
    }
    
    protected function getUploadFolder($coreRow = NULL, $coreTbl = '')
    {
        if ($coreTbl == '') {
            $coreTbl = $GLOBALS["SL"]->coreTbl;
        }
        if (!$coreRow && isset($this->sessData->dataSets[$coreTbl])) {
            $coreRow = $this->sessData->dataSets[$coreTbl][0];
        }
        if (!isset($coreRow->created_at)) {
            return '';
        }
        $fold = '../storage/app/up/evidence/' 
            . str_replace('-', '/', substr($coreRow->created_at, 0, 10)) . '/' 
            . $coreRow->{ $GLOBALS["SL"]->tblAbbr[$coreTbl] . 'unique_str' } . '/';
        return $fold;
    }
    
    protected function getUploadFile($nID)
    {
        return $this->getRandStr('uploads', 'up_stored_file', 30);
    }
    
    protected function cleanUploadList()
    {
        $treeID = $this->getUpTree();
        $chk = SLUploads::where('up_tree_id', $treeID)
            ->where('up_core_id', $this->coreID)
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $up) {
                if (trim($up->up_title) == '' 
                    && trim($up->up_evidence_desc) == '' 
                    && trim($up->up_upload_file)   == '' 
                    && trim($up->up_stored_file)   == '' 
                    && trim($up->up_video_link)    == '') {
                    SLUploads::find($up->up_id)
                        ->delete();
                }
            }
        }
        return true;
    }
    
    protected function getUpNode($nID = -3)
    {
        if ($nID > 0) {
            if (isset($this->allNodes[$nID])) {
                return $this->allNodes[$nID];
            }
            if (!isset($this->v["upNodes"])) {
                $this->v["upNodes"] = [];
            }
            if (!isset($this->v["upNodes"][$nID])) {
                $this->v["upNodes"][$nID] = null;
                $nodeRow = SLNode::find($nID);
                if ($nodeRow) {
                    $this->v["upNodes"][$nID] = new TreeNodeSurv($nID, $nodeRow);
                }
            }
            return $this->v["upNodes"][$nID];
        }
        return NULL;
    }
    
    protected function prevUploadList($nID = -3)
    {
        $ret = $chk = [];
        $this->cleanUploadList();
        $treeID = $this->getUpTree();
        $node = $this->getUpNode($nID);
        if ($node) {
            $fldID = $node->getTblFldID();
            if ($fldID > 0) {
                list($tbl, $fld) = $node->getTblFld();
                list($linkRecInd, $linkRecID) = $this->sessData->currSessDataPos($tbl);
                if ($linkRecID > 0) {
                    $chk = SLUploads::where('up_tree_id', $treeID)
                        ->where('up_core_id', $this->coreID)
                        ->where('up_node_id', $nID)
                        ->where('up_link_fld_id', $fldID)
                        ->where('up_link_rec_id', $linkRecID)
                        ->orderBy('created_at', 'asc')
                        ->get();
                }
            } else {
                $chk = SLUploads::where('up_tree_id', $treeID)
                    ->where('up_core_id', $this->coreID)
                    ->where('up_node_id', $nID)
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
        } else {
            $chk = SLUploads::where('up_tree_id', $treeID)
                ->where('up_core_id', $this->coreID)
                ->orderBy('created_at', 'asc')
                ->get();
        }
        if ($chk->isNotEmpty()) {
            foreach ($chk as $i => $up) {
                $hasUpload = (isset($up->up_upload_file) && trim($up->up_upload_file) != '');
                $hasStored = (isset($up->up_stored_file) && trim($up->up_stored_file) != '');
                $hasVidLnk = (isset($up->up_video_link) && trim($up->up_video_link) != '');
                if (($hasUpload && $hasStored) || $hasVidLnk) {
                    $ret[] = $up;
                }
            }
        }
        return $ret;
    }
    
    public function retrieveUploadFile($upID = '')
    {
        $this->checkPageViewPerms();
        $this->tweakPageViewPerms();
        if (!$this->isPublic() && !$this->v["isAdmin"] && !$this->v["isOwner"]) {
            return $this->retrieveUploadFail();
        }
        $upRequest = [];
        $treeID = $this->getUpTree();
        $fileRoot = $upID;
        $fileExt = '';
        if (strpos($upID, '.') !== false) {
            list($fileRoot, $fileExt) = explode('.', $upID);
        }
        $fileExt = strtolower($fileExt);
        $upRow = SLUploads::where('up_tree_id', $treeID)
            ->where('up_core_id', $this->coreID)
            ->where('up_stored_file', $fileRoot)
            ->first();
        if ($upRow) {
            $deet = $this->loadUpDeets($upRow);
            if (sizeof($deet) > 0) {
                if ($deet["privacy"] == 'Block') {
                // != 'Public' && !$this->v["isAdmin"] && !$this->v["isOwner"]) {
                    return $this->retrieveUploadFail();
                }
                return $this->previewImg($deet);
            }
        }
        return $this->retrieveUploadFail();
    }
    
    public function retrieveUploadFail()
    {
        return '';
    }
    
    public function previewImg($up)
    {
        if (is_array($up) && sizeof($up) > 0 && isset($up["file"])) {
            return response()->file($up["file"]);
        }
        /*
        if ($up["ext"] == 'pdf') {
            
        } else {
            $handler = new File($up["file"]);
            $file_time = $handler->getMTime(); // Get the last modified time for the file (Unix timestamp)
            $lifetime = 86400; // One day in seconds
            $header_etag = md5($file_time . $up["file"]);
            $header_modified = gmdate('r', $file_time);
            $headers = array(
                'Content-Disposition' => 'inline; filename="' . $this->coreID . '-' . (1+$up["ind"]) 
                                            . '-' . $up["fileOrig"] . '"',
                'Last-Modified'       => $header_modified,
                'Cache-Control'       => 'must-revalidate',
                'Expires'             => gmdate('r', $file_time + $lifetime),
                'Pragma'              => 'public',
                'Etag'                => $header_etag
            );
            // Is the resource cached?
            $h1 = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) 
                && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $header_modified);
            $h2 = (isset($_SERVER['HTTP_IF_NONE_MATCH']) 
                && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $header_etag);
            if ($h1 || $h2) {
                return Response::make('', 304, $headers); 
            }
            // File (image) is cached by the browser, so we don't have to send it again
            
            $headers = array_merge($headers, [
                'Content-Type'   => $handler->getMimeType(),
                'Content-Length' => $handler->getSize()
            ]);
            $up["file"] = $GLOBALS["SL"]->searchDeeperDirs($up["file"]);
            return Response::make(file_get_contents($up["file"]), 200, $headers);
        }
        */
    }
    
    protected function uploadTool($nID, $nIDtxt)
    {
        $this->loadUploadTypes();
        $GLOBALS["SL"]->pageAJAX .= 'window.refreshUpload = function () { '
            . '$("#uploadAjax").load("?ajax=1&upNode=' . $nID . '"); }' . "\n";
        $GLOBALS["SL"]->pageJAVA .= "addResTot(" . $nID . ", 4);\n";
        foreach ($this->uploadTypes as $j => $ty) {
            if (in_array(strtolower($ty->def_value), ['video', 'videos'])) {
                $GLOBALS["SL"]->pageJAVA .= 'uploadTypeVid = ' . $j . ';' . "\n";
            }
        }
        $ret = view(
            'vendor.survloop.forms.upload-tool', 
            [
                "nID"            => $nID,
                "uploadTypes"    => $this->uploadTypes,
                "uploadWarn"     => $this->uploadWarning($nID),
                "isPublic"       => $this->isPublic(), 
                "getPrevUploads" => $this->getPrevUploads($nID, $nIDtxt, true)
            ]
        )->render();
        if (!$GLOBALS["SL"]->REQ->has('ajax')) {
            $ret = '<div id="uploadAjax">' . $ret . '</div>';
        }
        return $ret;
    }
    
    protected function uploadWarning($nID)
    {
        return '';
    }
    
    protected function loadUpDeetPrivacy($upRow = NULL)
    {
        if ($upRow && isset($upRow->up_privacy)) {
            return $upRow->up_privacy;
        }
        return 'Private';
    }
    
    protected function loadUpDeets($upRow = NULL, $i = 0)
    {
        $ret = [];
        $treeID = $this->getUpTree();
        $ret["ind"]      = $i;
        $ret["privacy"]  = $this->loadUpDeetPrivacy($upRow);
        $ret["ext"]      = $GLOBALS["SL"]->getFileExt($upRow->up_upload_file);
        $ret["filename"] = $upRow->up_stored_file . '.' . $ret["ext"];
        $ret["file"]     = $this->getUploadFolder() . $upRow->up_stored_file 
            . '.' . $ret["ext"];
        $ret["filePub"]  = '/up/' . $GLOBALS["SL"]->treeRow->tree_slug . '/'
            . $this->coreID . '/' . $upRow->up_stored_file . '.' . $ret["ext"];
        $ret["fileOrig"] = $upRow->up_upload_file;
        $ret["fileLnk"]  = '<a href="' . $ret["filePub"] . '" target="_blank">' 
            . $upRow->up_upload_file . '</a>';
        $ret["youtube"]  = '';
        $ret["vimeo"]    = '';
        $ret["imgWidth"] = $ret["imgHeight"] = 0;
        $ret["imgClass"] = 'w100';
        $vidTypeID = $this->getVidType($treeID);
        if ($GLOBALS["SL"]->REQ->has('step') 
            && $GLOBALS["SL"]->REQ->step == 'uploadDel' 
            && $GLOBALS["SL"]->REQ->has('alt') 
            && intVal($GLOBALS["SL"]->REQ->alt) == $upRow->up_id) {
            if (file_exists($ret["file"]) && trim($upRow->up_type) != $vidTypeID) {
                unlink($ret["file"]);
            }
            SLUploads::find($upRow->up_id)->delete();
        } else {
            $imgTypes = ['png', 'gif', 'jpg', 'jpeg'];
            if (intVal($upRow->up_type) == $vidTypeID) {
                if (stripos($upRow->up_video_link, 'youtube') !== false 
                    || stripos($upRow->up_video_link, 'youtu.be') !== false) {
                    $ret["youtube"] = $this->getYoutubeID($upRow->up_video_link);
                    $ret["fileLnk"] = '<a href="' . $upRow->up_video_link 
                        . '" target="_blank">youtube/' . $ret["youtube"] . '</a>';
                } elseif (stripos($upRow->up_video_link, 'vimeo.com') !== false) {
                    $ret["vimeo"] = $this->getVimeoID($upRow->up_video_link);
                    $ret["fileLnk"] = '<a href="' . $upRow->up_video_link 
                        . '" target="_blank">vimeo/' . $ret["vimeo"] . '</a>';
                }
            } elseif (isset($upRow->up_stored_file) && trim($upRow->up_stored_file) != '') {
                $ret["file"] = $GLOBALS["SL"]->searchDeeperDirs($ret["file"]);
                if (!file_exists($ret["file"])) {
                    $ret["fileLnk"] .= ' &nbsp;&nbsp;<span class="txtDanger">'
                        . '<i class="fa fa-exclamation-triangle"></i> '
                        . '<i>File Not Found</i></span>';
                } elseif (in_array(strtolower($ret["ext"]), $imgTypes)) {
                    list($ret["imgWidth"], $ret["imgHeight"]) = getimagesize($ret["file"]);
                    if ($ret["imgWidth"] > $ret["imgHeight"]) {
                        $ret["imgClass"] = 'h100';
                    }
                }
            }
        }
        return $ret;
    }
    
    protected function loadPrevUploadDeets($nID = -3)
    {
        $this->uploads = $this->prevUploadList($nID);
        $treeID = $this->getUpTree();
        $this->upDeets = [];
        if (sizeof($this->uploads) > 0) {
            foreach ($this->uploads as $i => $upRow) {
                $this->upDeets[$i] = $this->loadUpDeets($upRow, $i);
            }
        }
        return true;
    }
    
    protected function prepPrevUploads($nID)
    {
        $this->uploads = [];
        $this->loadUploadTypes();
        $this->loadPrevUploadDeets($nID);
        //? $upSet = $this->getUploadSet($nID);
        return true;
    }
    
    protected function getPrevUploads($nID, $nIDtxt, $edit = false)
    {
        $this->prepPrevUploads($nID);
        $treeID = $this->getUpTree();
        return view(
            'vendor.survloop.forms.upload-previous', 
            [
                "nID"         => $nID,
                "nIDtxt"      => $nIDtxt,
                "REQ"         => $GLOBALS["SL"]->REQ,
                "height"      => 160,          
                "width"       => 330,
                "uploads"     => $this->uploads, 
                "upDeets"     => $this->upDeets, 
                "uploadTypes" => $this->uploadTypes, 
                "vidTypeID"   => $this->getVidType($treeID),
                "v"           => $this->v
            ]
        )->render();
    }
    
    protected function getUploads($nID, $isAdmin = false, $isOwner = false)
    {
        $this->prepPrevUploads($nID);
        if (empty($this->uploads)) {
            return [];
        }
        $treeID = $this->getUpTree();
        $vidTypeID = $this->getVidType($treeID);
        $opt = "tree-" . $treeID . "-upload-types";
        $upTypes = [];
        if (isset($GLOBALS["SL"]->sysOpts[$opt])) {
            $upTypes = $GLOBALS["SL"]->sysOpts[$opt];
        }
        $this->v["uploadPrintMap"] = [
            "img" => [], 
            "vid" => [], 
            "fil" => [] 
        ];
        $ups = [];
        if (sizeof($this->uploads) > 0) {
            foreach ($this->uploads as $i => $upRow) {
                if (intVal($upRow->up_type) == $vidTypeID) {
                    $this->v["uploadPrintMap"]["vid"][] = sizeof($ups);
                } elseif (isset($upRow->up_upload_file) 
                    && isset($upRow->up_stored_file) 
                    && trim($upRow->up_upload_file) != '' 
                    && trim($upRow->up_stored_file) != '') {
                    $imgTypes = ['png', 'gif', 'jpg', 'jpeg'];
                    if (in_array($this->upDeets[$i]["ext"], $imgTypes)) {
                        $this->v["uploadPrintMap"]["img"][] = sizeof($ups);
                    } else {
                        $this->v["uploadPrintMap"]["fil"][] = sizeof($ups);
                    }
                }
                $canShow = $this->canShowUpload(
                    $nID, 
                    $this->upDeets[$i], 
                    $isAdmin, 
                    $isOwner
                );
                $ups[] = view(
                    'vendor.survloop.forms.uploads-print', 
                    [
                        "nID"         => $nID,
                        "REQ"         => $GLOBALS["SL"]->REQ,
                        "height"      => 180,
                        "width"       => 330,
                        "upRow"       => $upRow, 
                        "upDeets"     => $this->upDeets[$i], 
                        "uploadTypes" => $this->uploadTypes, 
                        "vidTypeID"   => $this->getVidType($treeID),
                        "isAdmin"     => $isAdmin,
                        "isOwner"     => $isOwner,
                        "canShow"     => $canShow,
                        "v"           => $this->v
                    ]
                )->render();
            }
        }
        return $ups;
    }
    
    protected function canShowUpload($nID, $upDeets, $isAdmin = false, $isOwner = false)
    {
        return ($isAdmin || $isOwner);
    }
    
    protected function getUploadsMultNodes($nIDs, $isAdmin = false, $isOwner = false)
    {
        $ups = [];
        $this->v["uploadPrintMultiMap"] = [
            "img" => [],
            "vid" => [],
            "fil" => []
        ];
        if (sizeof($nIDs) > 0) {
            foreach ($nIDs as $nID) {
                $tmpUps = $this->getUploads($nID, $isAdmin, $isOwner);
                if (sizeof($tmpUps) > 0) {
                    $vidTypeID = $this->getVidType($this->getUpTree());
                    foreach ($tmpUps as $i => $up) {
                        if (!in_array($up, $ups)) {
                            if (in_array($i, $this->v["uploadPrintMap"]["img"])) {
                                $this->v["uploadPrintMultiMap"]["img"][] = sizeof($ups);
                            } elseif (in_array($i, $this->v["uploadPrintMap"]["vid"])) {
                                $this->v["uploadPrintMultiMap"]["vid"][] = sizeof($ups);
                            } else {
                                $this->v["uploadPrintMultiMap"]["fil"][] = sizeof($ups);
                            }
                            $ups[] = $up;
                        }
                    }
                }
            }
        }
        return $ups;
    }
    
    protected function reportUploadsMultNodes($nIDs, $isAdmin = false, $isOwner = false)
    {
        $ups = $this->getUploadsMultNodes($nIDs, $isAdmin, $isOwner);
        return view(
            'vendor.survloop.reports.inc-uploads', 
            [
                "uploads" => $ups,
                "upMap"   => $this->v["uploadPrintMultiMap"]
            ]
        )->render();
    }
    
    protected function postUploadTool($nID)
    {
        $ret = '';
        $this->loadPrevUploadDeets($nID);
        $treeID = $this->getUpTree();
        $vidTypeID = -1;
        $opt = "tree-" . $treeID . "-upload-types";
        if (isset($GLOBALS["SL"]->sysOpts[$opt])) {
            $vidTypeID = $GLOBALS["SL"]->def->getID($GLOBALS["SL"]->sysOpts[$opt], 'Video');
        }
        if (sizeof($this->uploads) > 0) {
            foreach ($this->uploads as $i => $upRow) {
                $fldBase = 'up' . $upRow->up_id . 'Edit';
                if ($GLOBALS["SL"]->REQ->has($fldBase . 'Visib') 
                    && intVal($GLOBALS["SL"]->REQ->input($fldBase . 'Visib')) == 1) {
                    $upRow = SLUploads::find($upRow->up_id);
                    if ($upRow) {
                        if ($GLOBALS["SL"]->REQ->has($fldBase . 'Type')) {
                            $upRow->up_type = $GLOBALS["SL"]->REQ->input($fldBase . 'Type');
                        }
                        $upRow->up_privacy = $GLOBALS["SL"]->REQ->input($fldBase . 'Privacy');
                        $upRow->up_title = $GLOBALS["SL"]->REQ->input($fldBase . 'Title');
                        //$upRow->up_desc  = $GLOBALS["SL"]->REQ->input('up'.$upRow->up_id.'EditDesc');
                        $upRow->save();
                    }
                }
            }
        }
        if ($this->isStepUpload()) {
            $upRow = new SLUploads;
            $upRow->up_tree_id    = $treeID;
            $upRow->up_core_id    = $this->coreID;
            $upRow->up_node_id    = $nID;
            $upRow->up_link_fld_id = $this->allNodes[$nID]->getTblFldID();
            $upRow->up_link_rec_id = 0;
            if ($upRow->up_link_fld_id > 0) {
                list($tbl, $fld) = $this->allNodes[$nID]->getTblFld();
                list($loopInd, $loopID) = $this->sessData->currSessDataPos($tbl);
                if ($loopID > 0) {
                    $upRow->up_link_rec_id = $loopID;
                }
            } else {
                $upRow->up_link_fld_id = 0;
            }
            $upRow->up_type    = 0;
            if ($GLOBALS["SL"]->REQ->has('n' . $nID . 'fld')) {
                $upRow->up_type = $GLOBALS["SL"]->REQ->input('n' . $nID . 'fld');
            }
            $upRow->up_privacy = $GLOBALS["SL"]->REQ->input('up' . $nID . 'Privacy');
            $upRow->up_title   = $GLOBALS["SL"]->REQ->input('up' . $nID . 'Title');
            //$upRow->up_desc  = $GLOBALS["SL"]->REQ->input('up' . $nID . 'Desc');
            $file = 'up' . $nID . 'File';
            if ($GLOBALS["SL"]->REQ->has('up' . $nID . 'Vid') 
                && $GLOBALS["SL"]->REQ->has('n' . $nID . 'fld')
                && $GLOBALS["SL"]->REQ->input('n' . $nID . 'fld') == $vidTypeID) {
                $upRow->up_video_link = $GLOBALS["SL"]->REQ->input('up' . $nID . 'Vid');
                $upRow->up_video_duration = $this->getYoutubeDuration($upRow->up_video_link);
            } elseif ($GLOBALS["SL"]->REQ->hasFile($file)) { // file upload
                $upRow->up_upload_file = $GLOBALS["SL"]->REQ->file($file)->getClientOriginalName();
                $extension = $GLOBALS["SL"]->REQ->file($file)->getClientOriginalExtension();
                $mimetype = $GLOBALS["SL"]->REQ->file($file)->getMimeType();
                $size = $GLOBALS["SL"]->REQ->file($file)->getSize();
                $exts = ["gif", "jpeg", "jpg", "png", "pdf"];
                $mimes = [
                    "image/gif", "image/jpeg", "image/jpg", 
                    "image/pjpeg", "image/x-png", "image/png", "application/pdf"
                ];
                if (in_array(strtolower($extension), $exts) 
                    && in_array(strtolower($mimetype), $mimes)) {
                    if (!$GLOBALS["SL"]->REQ->file($file)->isValid()) {
                        $ret .= '<div class="txtDanger">Upload Error.' 
                            . /* $_FILES["up" . $nID . "File"]["error"] . */ '</div>';
                    } else {
                        $upFold = $this->getUploadFolder();
                        $this->mkNewFolder($upFold);
                        $upRow->up_stored_file = $this->getUploadFile($nID);
                        $filename = $upRow->up_stored_file . '.' . strtolower($extension);
                        //if ($GLOBALS["SL"]->debugOn) { $ret .= "saving as filename: " . $upFold . $filename . "<br>"; }
                        if (file_exists($upFold . $filename)) {
                            Storage::delete($upFold . $filename);
                        }
                        $GLOBALS["SL"]->REQ->file($file)->move($upFold, $filename);
                    }
                } else {
                    $ret .= '<div class="txtDanger">'
                        . 'Invalid file. Please check the format and try again.</div>';
                }
            }
            $upRow->save();
        }
        return $ret;
    }
    
    protected function mkNewFolder($fold)
    {
        $this->checkBaseFolders();
        $this->checkFolder($fold);
        return true;
    }

    protected function getYoutubeDuration($vidURL)
    {
        if (stripos($vidURL, 'youtube') !== false) {
            
        }
        return -1;
    }
    
    protected function getYoutubeID($vidURL)
    {
        if (strpos(strtolower($vidURL), 'https://youtu.be/') !== false) {
            return str_ireplace('https://youtu.be/', '', $vidURL);
        }
        preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $vidURL, $matches);
        return $matches[1];
    }
    
    protected function getVimeoID($vidURL)
    {
        if (strpos(strtolower($vidURL), 'https://vimeo.com/') !== false) {
            return str_ireplace('https://vimeo.com/', '', $vidURL);
        }
        return '';
    }                           
    
}

?>