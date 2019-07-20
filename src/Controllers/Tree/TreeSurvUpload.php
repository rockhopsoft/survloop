<?php
/**
  * TreeSurvUpload is a mid-level class atop SurvLoop's branching tree, specifically for 
  * uploading functionality in surveys and pages.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
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
            $this->v["vidTypeID"] = $GLOBALS["SL"]->def->getID(
                $GLOBALS["SL"]->sysOpts["tree-" . $treeID . "-upload-types"], 'Video');
        }
        return $this->v["vidTypeID"];
    }
    
    protected function genRandStr($len)
    {
        return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 1) 
             . substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, ($len-1));
    }
    
    protected function checkRandStr($tbl, $fld, $str)
    {
        $modelObj = [];
        eval("\$modelObj = " . $GLOBALS["SL"]->modelPath($tbl) . "::where('" . $fld . "', '" . $str . "')->get();");
        return $modelObj->isEmpty();
    }
    
    protected function getRandStr($tbl, $fld, $len)
    {
        $str = $this->genRandStr($len);
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
        $fold = '../storage/app/up/evidence/' . str_replace('-', '/', substr($coreRow->created_at, 0, 10)) 
            . '/' . $coreRow->{ $GLOBALS["SL"]->tblAbbr[$coreTbl] . 'UniqueStr' } . '/';
        return $fold;
    }
    
    protected function getUploadFile($nID)
    {
        return $this->getRandStr('Uploads', 'UpStoredFile', 30);
    }
    
    protected function cleanUploadList()
    {
        $treeID = $this->getUpTree();
        $chk = SLUploads::where('UpTreeID', $treeID)
            ->where('UpCoreID', $this->coreID)
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $up) {
                if (trim($up->UpTitle) == '' && trim($up->UpEvidenceDesc) == '' && trim($up->UpUploadFile) == '' 
                    && trim($up->UpStoredFile) == '' && trim($up->UpVideoLink) == '') {
                    SLUploads::find($up->UpID)->delete();
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
                    $chk = SLUploads::where('UpTreeID', $treeID)
                        ->where('UpCoreID', $this->coreID)
                        ->where('UpNodeID', $nID)
                        ->where('UpLinkFldID', $fldID)
                        ->where('UpLinkRecID', $linkRecID)
                        ->orderBy('created_at', 'asc')
                        ->get();
                }
            } else {
                $chk = SLUploads::where('UpTreeID', $treeID)
                    ->where('UpCoreID', $this->coreID)
                    ->where('UpNodeID', $nID)
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
        } else {
            $chk = SLUploads::where('UpTreeID', $treeID)
                ->where('UpCoreID', $this->coreID)
                ->orderBy('created_at', 'asc')
                ->get();
        }
        if ($chk->isNotEmpty()) {
            foreach ($chk as $i => $up) {
                if ((isset($up->UpUploadFile) && trim($up->UpUploadFile) != '' && isset($up->UpStoredFile) 
                    && trim($up->UpStoredFile) != '') || (isset($up->UpVideoLink) && trim($up->UpVideoLink) != '')) {
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
        $upRow = SLUploads::where('UpTreeID', $treeID)
            ->where('UpCoreID', $this->coreID)
            ->where('UpStoredFile', $fileRoot)
            ->first();
        if ($upRow) {
            $deet = $this->loadUpDeets($upRow);
            if (sizeof($deet) > 0) {
                if ($deet["privacy"] == 'Block') { // != 'Public' && !$this->v["isAdmin"] && !$this->v["isOwner"]) {
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
            $h1 = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $header_modified);
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
            if (in_array(strtolower($ty->DefValue), ['video', 'videos'])) {
                $GLOBALS["SL"]->pageJAVA .= 'uploadTypeVid = ' . $j . ';' . "\n";
            }
        }
        $ret = view('vendor.survloop.forms.upload-tool', [
            "nID"            => $nID,
            "uploadTypes"    => $this->uploadTypes,
            "uploadWarn"     => $this->uploadWarning($nID),
            "isPublic"       => $this->isPublic(), 
            "getPrevUploads" => $this->getPrevUploads($nID, $nIDtxt, true)
        ])->render();
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
        if ($upRow && isset($upRow->UpPrivacy)) {
            return $upRow->UpPrivacy;
        }
        return 'Private';
    }
    
    protected function loadUpDeets($upRow = NULL, $i = 0)
    {
        $ret = [];
        $treeID = $this->getUpTree();
        $ret["ind"]      = $i;
        $ret["privacy"]  = $this->loadUpDeetPrivacy($upRow);
        $ret["ext"]      = $GLOBALS["SL"]->getFileExt($upRow->UpUploadFile);
        $ret["filename"] = $upRow->UpStoredFile . '.' . $ret["ext"];
        $ret["file"]     = $this->getUploadFolder() . $upRow->UpStoredFile 
            . '.' . $ret["ext"];
        $ret["filePub"]  = '/up/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/' . $this->coreID
            . '/' . $upRow->UpStoredFile . '.' . $ret["ext"];
        $ret["fileOrig"] = $upRow->UpUploadFile;
        $ret["fileLnk"]  = '<a href="' . $ret["filePub"] 
            . '" target="_blank">' . $upRow->UpUploadFile . '</a>';
        $ret["youtube"]  = '';
        $ret["vimeo"]    = '';
        $ret["imgWidth"] = $ret["imgHeight"] = 0;
        $ret["imgClass"] = 'w100';
        $vidTypeID = $this->getVidType($treeID);
        if ($GLOBALS["SL"]->REQ->has('step') && $GLOBALS["SL"]->REQ->step == 'uploadDel' 
            && $GLOBALS["SL"]->REQ->has('alt') && intVal($GLOBALS["SL"]->REQ->alt) == $upRow->UpID) {
            if (file_exists($ret["file"]) && trim($upRow->UpType) != $vidTypeID) {
                unlink($ret["file"]);
            }
            SLUploads::find($upRow->UpID)->delete();
        } else {
            if (intVal($upRow->UpType) == $vidTypeID) {
                if (stripos($upRow->UpVideoLink, 'youtube') !== false 
                    || stripos($upRow->UpVideoLink, 'youtu.be') !== false) {
                    $ret["youtube"] = $this->getYoutubeID($upRow->UpVideoLink);
                    $ret["fileLnk"] = '<a href="' . $upRow->UpVideoLink 
                        . '" target="_blank">youtube/' . $ret["youtube"] . '</a>';
                } elseif (stripos($upRow->UpVideoLink, 'vimeo.com') !== false) {
                    $ret["vimeo"] = $this->getVimeoID($upRow->UpVideoLink);
                    $ret["fileLnk"] = '<a href="' . $upRow->UpVideoLink 
                        . '" target="_blank">vimeo/' . $ret["vimeo"] . '</a>';
                }
            } elseif (isset($upRow->UpStoredFile) && trim($upRow->UpStoredFile) != '') {
                $ret["file"] = $GLOBALS["SL"]->searchDeeperDirs($ret["file"]);
                if (!file_exists($ret["file"])) {
                    $ret["fileLnk"] .= ' &nbsp;&nbsp;<span class="txtDanger">'
                        . '<i class="fa fa-exclamation-triangle"></i> <i>File Not Found</i></span>';
                } elseif (in_array(strtolower($ret["ext"]), ['png', 'gif', 'jpg', 'jpeg'])) {
                    list($ret["imgWidth"], $ret["imgHeight"]) 
                        = getimagesize($ret["file"]);
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
        return view('vendor.survloop.forms.upload-previous', [
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
        ])->render();
    }
    
    protected function getUploads($nID, $isAdmin = false, $isOwner = false)
    {
        $this->prepPrevUploads($nID);
        if (empty($this->uploads)) {
            return [];
        }
        $treeID = $this->getUpTree();
        $vidTypeID = $this->getVidType($treeID);
        $upTypes = [];
        if (isset($GLOBALS["SL"]->sysOpts["tree-" . $treeID . "-upload-types"])) {
            $upTypes = $GLOBALS["SL"]->sysOpts["tree-" . $treeID . "-upload-types"];
        }
        $this->v["uploadPrintMap"] = [ "img" => [], "vid" => [], "fil" => [] ];
        $ups = [];
        if (sizeof($this->uploads) > 0) {
            foreach ($this->uploads as $i => $upRow) {
                if (intVal($upRow->UpType) == $vidTypeID) {
                    $this->v["uploadPrintMap"]["vid"][] = sizeof($ups);
                } elseif (isset($upRow->UpUploadFile) && isset($upRow->UpStoredFile) 
                    && trim($upRow->UpUploadFile) != '' && trim($upRow->UpStoredFile) != '') {
                    if (in_array($this->upDeets[$i]["ext"], array("gif", "jpeg", "jpg", "png"))) {
                        $this->v["uploadPrintMap"]["img"][] = sizeof($ups);
                    } else {
                        $this->v["uploadPrintMap"]["fil"][] = sizeof($ups);
                    }
                }
                $ups[] = view('vendor.survloop.forms.uploads-print', [
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
                    "canShow"     => $this->canShowUpload($nID, $this->upDeets[$i], $isAdmin, $isOwner),
                    "v"           => $this->v
                ])->render();
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
        $this->v["uploadPrintMultiMap"] = [ "img" => [], "vid" => [], "fil" => [] ];
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
        return view('vendor.survloop.reports.inc-uploads', [
            "uploads" => $ups,
            "upMap"   => $this->v["uploadPrintMultiMap"]
        ])->render();
    }
    
    protected function postUploadTool($nID)
    {
        $ret = '';
        $this->loadPrevUploadDeets($nID);
        $treeID = $this->getUpTree();
        $vidTypeID = -1;
        if (isset($GLOBALS["SL"]->sysOpts["tree-" . $treeID . "-upload-types"])) {
            $vidTypeID = $GLOBALS["SL"]->def->getID($GLOBALS["SL"]->sysOpts["tree-" . $treeID 
                . "-upload-types"], 'Video');
        }
        if (sizeof($this->uploads) > 0) {
            foreach ($this->uploads as $i => $upRow) {
                if ($GLOBALS["SL"]->REQ->has('up' . $upRow->UpID . 'EditVisib') 
                    && intVal($GLOBALS["SL"]->REQ->input('up' . $upRow->UpID . 'EditVisib')) == 1) {
                    $upRow = SLUploads::find($upRow->UpID);
                    if ($upRow) {
                        $upRow->UpType    = $GLOBALS["SL"]->REQ->input('up'.$upRow->UpID.'EditType');
                        $upRow->UpPrivacy = $GLOBALS["SL"]->REQ->input('up'.$upRow->UpID.'EditPrivacy');
                        $upRow->UpTitle   = $GLOBALS["SL"]->REQ->input('up'.$upRow->UpID.'EditTitle');
                        //$upRow->UpDesc  = $GLOBALS["SL"]->REQ->input('up'.$upRow->UpID.'EditDesc');
                        $upRow->save();
                    }
                }
            }
        }
        if ($GLOBALS["SL"]->REQ->has('step') && $GLOBALS["SL"]->REQ->has('n' . $nID . 'fld')) {
            $upRow = new SLUploads;
            $upRow->UpTreeID    = $treeID;
            $upRow->UpCoreID    = $this->coreID;
            $upRow->UpNodeID    = $nID;
            $upRow->UpLinkFldID = $this->allNodes[$nID]->getTblFldID();
            $upRow->UpLinkRecID = -3;
            if ($upRow->UpLinkFldID > 0) {
                list($tbl, $fld) = $this->allNodes[$nID]->getTblFld();
                list($loopInd, $loopID) = $this->sessData->currSessDataPos($tbl);
                if ($loopID > 0) {
                    $upRow->UpLinkRecID = $loopID;
                }
            }
            $upRow->UpType    = $GLOBALS["SL"]->REQ->input('n' . $nID . 'fld');
            $upRow->UpPrivacy = $GLOBALS["SL"]->REQ->input('up' . $nID . 'Privacy');
            $upRow->UpTitle   = $GLOBALS["SL"]->REQ->input('up' . $nID . 'Title');
            //$upRow->UpDesc  = $GLOBALS["SL"]->REQ->input('up' . $nID . 'Desc');
            if ($GLOBALS["SL"]->REQ->has('up' . $nID . 'Vid') 
                && $GLOBALS["SL"]->REQ->input('n' . $nID . 'fld') == $vidTypeID) {
                $upRow->UpVideoLink     = $GLOBALS["SL"]->REQ->input('up' . $nID . 'Vid');
                $upRow->UpVideoDuration = $this->getYoutubeDuration($upRow->UpVideoLink);
            } elseif ($GLOBALS["SL"]->REQ->hasFile('up' . $nID . 'File')) { // file upload
                $upRow->UpUploadFile    = $GLOBALS["SL"]->REQ->file('up' . $nID . 'File')->getClientOriginalName();
                $extension = $GLOBALS["SL"]->REQ->file('up' . $nID . 'File')->getClientOriginalExtension();
                $mimetype  = $GLOBALS["SL"]->REQ->file('up' . $nID . 'File')->getMimeType();
                $size      = $GLOBALS["SL"]->REQ->file('up' . $nID . 'File')->getSize();
                if (in_array($extension, array("gif", "jpeg", "jpg", "png", "pdf")) 
                    && in_array($mimetype, array("image/gif", "image/jpeg", "image/jpg", "image/pjpeg", 
                        "image/x-png", "image/png", "application/pdf"))) {
                    if (!$GLOBALS["SL"]->REQ->file('up' . $nID . 'File')->isValid()) {
                        $ret .= '<div class="txtDanger">Upload Error.' 
                            . /* $_FILES["up" . $nID . "File"]["error"] . */ '</div>';
                    } else {
                        $upFold = $this->getUploadFolder();
                        $this->mkNewFolder($upFold);
                        $upRow->UpStoredFile = $this->getUploadFile($nID);
                        $filename = $upRow->UpStoredFile . '.' . $extension;
                        //if ($this->debugOn) { $ret .= "saving as filename: " . $upFold . $filename . "<br>"; }
                        if (file_exists($upFold . $filename)) {
                            Storage::delete($upFold . $filename);
                        }
                        $GLOBALS["SL"]->REQ->file('up' . $nID . 'File')->move($upFold, $filename);
                    }
                } else {
                    $ret .= '<div class="txtDanger">Invalid file. Please check the format and try again.</div>';
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