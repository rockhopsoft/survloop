<?php
namespace SurvLoop\Controllers;

use Auth;
use Storage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\File\File;

use App\Models\User;
use App\Models\SLUploads;
use App\Models\SLNodeResponses;

class SurvUploadTree extends SurvLoopTree
{
    public $uploadTypes      = array();
    protected $uploads       = array();
    protected $upDeets       = array();
    
    protected function genRandStr($len)
    {
        return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 1) 
             . substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, ($len-1));
    }
    
    protected function checkRandStr($tbl, $fld, $str)
    {
        $modelObj = array();
        eval("\$modelObj = " . $GLOBALS["SL"]->modelPath($tbl) . "::where('" . $fld . "', '" . $str . "')->get();");
        return (!$modelObj || sizeof($modelObj) <= 0);
    }
    
    protected function getRandStr($tbl, $fld, $len)
    {
        $str = $this->genRandStr($len);
        while (!$this->checkRandStr($tbl, $fld, $str)) $str = $this->genRandStr($len);
        return $str;
    }
    
    
    //////////////////////////////////////////////////////////////////////
    //  START FILE UPLOADING FUNCTIONS
    //////////////////////////////////////////////////////////////////////
    
    protected function loadUploadTypes()
    {
        if (sizeof($this->uploadTypes) > 0) return $this->uploadTypes;
        $upType = "tree-" . $GLOBALS["SL"]->treeID . "-upload-types";
        if (isset($GLOBALS["SL"]->sysOpts[$upType])) {
            $this->uploadTypes = $GLOBALS["SL"]->getDefSet($GLOBALS["SL"]->sysOpts[$upType]);
        }
        if (sizeof($this->uploadTypes) == 0) {
            $this->uploadTypes = $GLOBALS["SL"]->getDefSet('Upload Types');
        }
        return $this->uploadTypes;
    }
    
    protected function checkBaseFolders()
    {
        $this->checkFolder('../storage/app/up/avatar');
        $this->checkFolder('../storage/app/up/evidence/' . date("Y/m/d"));
        return true;
    }
    
    protected function getUploadFolder($nID = -3, $coreRow = [], $coreTbl = '')
    {
        if ($coreTbl == '') $coreTbl = $GLOBALS["SL"]->coreTbl;
        if (sizeof($coreRow) == 0) $coreRow = $this->sessData->dataSets[$coreTbl][0];
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
        $chk = SLUploads::where('UpTreeID', $GLOBALS["SL"]->treeID)
            ->where('UpCoreID', $this->coreID)
            ->get();
        if ($chk && sizeof($chk) > 0) {
            foreach ($chk as $up) {
                if (trim($up->UpTitle) == '' && trim($up->UpEvidenceDesc) == '' && trim($up->UpUploadFile) == '' 
                    && trim($up->UpStoredFile) == '' && trim($up->UpVideoLink) == '') {
                    SLUploads::find($up->UpID)->delete();
                }
            }
        }
        return true;
    }
    
    protected function prevUploadList($nID = -3)
    {
        $this->cleanUploadList();
        $ret = $chk = [];
        if ($nID > 0 && isset($this->allNodes[$nID])) {
            $fldID = $this->allNodes[$nID]->getTblFldID();
            if ($fldID > 0) {
                list($tbl, $fld) = $this->allNodes[$nID]->getTblFld();
                list($linkRecInd, $linkRecID) = $this->sessData->currSessDataPos($tbl);
                if ($linkRecID > 0) {
                    $chk = SLUploads::where('UpTreeID', $GLOBALS["SL"]->treeID)
                        ->where('UpCoreID', $this->coreID)
                        ->where('UpNodeID', $nID)
                        ->where('UpLinkFldID', $fldID)
                        ->where('UpLinkRecID', $linkRecID)
                        ->orderBy('created_at', 'asc')
                        ->get();
                }
            } else {
                $chk = SLUploads::where('UpTreeID', $GLOBALS["SL"]->treeID)
                    ->where('UpCoreID', $this->coreID)
                    ->where('UpNodeID', $nID)
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
        } else {
            $chk = SLUploads::where('UpTreeID', $GLOBALS["SL"]->treeID)
                ->where('UpCoreID', $this->coreID)
                ->orderBy('created_at', 'asc')
                ->get();
        }
        if ($chk && sizeof($chk) > 0) {
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
        if (!$this->isPublic() && !$this->isAdminUser() && !$this->isCoreOwner()) {
            return $this->retrieveUploadFail();
        }
        $upRequest = array();
        $this->loadPrevUploadDeets();
        if ($this->upDeets && sizeof($this->upDeets) > 0) {
            foreach ($this->upDeets as $i => $up) {
                if ($up["filename"] == $upID) {
                    if ($up["privacy"] != 'Public' && !$this->isAdminUser() && !$this->isCoreOwner()) {
                        return $this->retrieveUploadFail();
                    }
                    return $this->previewImg($up);
                }
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
        return response()->file($up["file"]);
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
    
    protected function uploadTool($nID)
    {
        $this->loadUploadTypes();
        $GLOBALS["SL"]->pageAJAX .= 'window.refreshUpload = function () { $("#uploadAjax").load("?ajax=1&upNode=' 
            . $nID . '"); }' . "\n";
        $this->pageJSvalid .= "if (document.getElementById('n" . $nID . "VisibleID') && document.getElementById('n" 
            . $nID . "VisibleID').value == 1) reqUploadTitle(" . $nID . ");\n";
        $GLOBALS["SL"]->pageJAVA .= "addResTot(" . $nID . ", 4);\n";
        foreach ($this->uploadTypes as $j => $ty) {
            if (in_array(strtolower($ty->DefValue), ['video', 'videos'])) {
                $GLOBALS["SL"]->pageJAVA .= 'uploadTypeVid = ' . $j . ';' . "\n";
            }
        }
        $ret = ((!$GLOBALS["SL"]->REQ->has('ajax')) ? '<div id="uploadAjax">' : '') 
            . view('vendor.survloop.upload-tool', [
                "nID"            => $nID,
                "uploadTypes"    => $this->uploadTypes,
                "uploadWarn"     => $this->uploadWarning($nID),
                "isPublic"       => $this->isPublic(), 
                "getPrevUploads" => $this->getPrevUploads($nID, true)
            ])->render() 
            . ((!$GLOBALS["SL"]->REQ->has('ajax')) ? '</div>' : '');
        return $ret;
    }
    
    protected function uploadWarning($nID)
    {
        return '';
    }
    
    protected function loadPrevUploadDeets($nID = -3)
    {
        $this->uploads = $this->prevUploadList($nID);
        $this->upDeets = [];
        if ($this->uploads && sizeof($this->uploads) > 0) {
            foreach ($this->uploads as $i => $upRow) {
                $this->upDeets[$i]["ind"]      = $i;
                $this->upDeets[$i]["privacy"]  = $upRow->UpPrivacy;
                $this->upDeets[$i]["ext"]      = $GLOBALS["SL"]->getFileExt($upRow->UpUploadFile);
                $this->upDeets[$i]["filename"] = $upRow->UpStoredFile . '.' . $this->upDeets[$i]["ext"];
                $this->upDeets[$i]["file"]     = $this->getUploadFolder($nID) . $upRow->UpStoredFile 
                    . '.' . $this->upDeets[$i]["ext"];
                $this->upDeets[$i]["filePub"]  = '/up/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/' . $this->coreID 
                    . '/' . $upRow->UpStoredFile . '.' . $this->upDeets[$i]["ext"];
                $this->upDeets[$i]["fileOrig"] = $upRow->UpUploadFile;
                $this->upDeets[$i]["fileLnk"]  = '<a href="' . $this->upDeets[$i]["filePub"] 
                    . '" target="_blank">' . $upRow->UpUploadFile . '</a>';
                $this->upDeets[$i]["youtube"]  = '';
                $this->upDeets[$i]["vimeo"]    = '';
                $this->upDeets[$i]["imgWidth"] = $this->upDeets[$i]["imgHeight"] = 0;
                $this->upDeets[$i]["imgClass"] = 'w100';
                $vidTypeID = -1;
                if (isset($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID . "-upload-types"])) {
                    $vidTypeID = $GLOBALS["SL"]->getDefID($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID 
                        . "-upload-types"], 'Video');
                }
                if ($GLOBALS["SL"]->REQ->has('step') && $GLOBALS["SL"]->REQ->step == 'uploadDel' 
                    && $GLOBALS["SL"]->REQ->has('alt') && intVal($GLOBALS["SL"]->REQ->alt) == $upRow->UpID) {
                    if (file_exists($this->upDeets[$i]["file"]) && trim($upRow->type) != $vidTypeID) {
                        unlink($this->upDeets[$i]["file"]);
                    }
                    SLUploads::find($upRow->UpID)->delete();
                } else {
                    if (trim($upRow->type) == $vidTypeID) {
                        if (stripos($upRow->UpVideoLink, 'youtube') !== false 
                            || stripos($upRow->UpVideoLink, 'youtu.be') !== false) {
                            $this->upDeets[$i]["youtube"] = $this->getYoutubeID($upRow->UpVideoLink);
                            $this->upDeets[$i]["fileLnk"] = '<a href="' . $upRow->UpVideoLink 
                                . '" target="_blank">youtube/' . $this->upDeets[$i]["youtube"] . '</a>';
                        } elseif (stripos($upRow->UpVideoLink, 'vimeo.com') !== false) {
                            $this->upDeets[$i]["vimeo"] = $this->getVimeoID($upRow->UpVideoLink);
                            $this->upDeets[$i]["fileLnk"] = '<a href="' . $upRow->UpVideoLink 
                                . '" target="_blank">vimeo/' . $this->upDeets[$i]["vimeo"] . '</a>';
                        }
                    } elseif (isset($upRow->UpStoredFile) && trim($upRow->UpStoredFile) != '') {
                        $this->upDeets[$i]["file"] = $GLOBALS["SL"]->searchDeeperDirs($this->upDeets[$i]["file"]);
                        if (!file_exists($this->upDeets[$i]["file"])) {
                            $this->upDeets[$i]["fileLnk"] .= ' &nbsp;&nbsp;<span class="slRedDark">'
                                . '<i class="fa fa-exclamation-triangle"></i> <i>File Not Found</i></span>';
                        } elseif (in_array(strtolower($this->upDeets[$i]["ext"]), ['png', 'gif', 'jpg', 'jpeg'])) {
                            list($this->upDeets[$i]["imgWidth"], $this->upDeets[$i]["imgHeight"]) 
                                = getimagesize($this->upDeets[$i]["file"]);
                            if ($this->upDeets[$i]["imgWidth"] > $this->upDeets[$i]["imgHeight"]) {
                                $this->upDeets[$i]["imgClass"] = 'h100';
                            }
                        }
                    }
                }
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
    
    protected function getPrevUploads($nID, $edit = false)
    {
        $this->prepPrevUploads($nID);
        $vidTypeID = -1;
        if (isset($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID . "-upload-types"])) {
            $vidTypeID = $GLOBALS["SL"]->getDefID($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID 
                . "-upload-types"], 'Video');
        }
        return view('vendor.survloop.upload-previous', [
            "nID"         => $nID,
            "REQ"         => $this->REQ,
            "height"      => 160,          
            "width"       => 330,
            "uploads"     => $this->uploads, 
            "upDeets"     => $this->upDeets, 
            "uploadTypes" => $this->uploadTypes, 
            "vidTypeID"   => $vidTypeID,
            "v"           => $this->v
        ])->render();
    }
    
    protected function getUploads($nID, $isAdmin = false, $isOwner = false)
    {
        $this->prepPrevUploads($nID);
        if (!$this->uploads || sizeof($this->uploads) == 0) return [];
        $vidTypeID = -1;
        if (isset($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID . "-upload-types"])) {
            $vidTypeID = $GLOBALS["SL"]->getDefID($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID 
                . "-upload-types"], 'Video');
        }
        $ups = [];
        if (isset($this->uploads) && sizeof($this->uploads) > 0) {
            foreach ($this->uploads as $i => $upRow) {
                $ups[] = view('vendor.survloop.uploads-print', [
                    "nID"         => $nID,
                    "REQ"         => $this->REQ,
                    "height"      => 160,
                    "width"       => 330,
                    "upRow"       => $upRow, 
                    "upDeets"     => $this->upDeets[$i], 
                    "uploadTypes" => $this->uploadTypes, 
                    "vidTypeID"   => $GLOBALS["SL"]->getDefID($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID 
                        . "-upload-types"], 'Video'),
                    "v"           => $this->v,
                    "isAdmin"     => $isAdmin,
                    "isOwner"     => $isOwner
                ])->render();
            }
        }
        return $ups;
    }
    
    protected function getUploadsMultNodes($nIDs, $isAdmin = false, $isOwner = false)
    {
        $ups = [];
        if (sizeof($nIDs) > 0) {
            foreach ($nIDs as $nID) {
                $tmpUps = $this->getUploads($nID, $isAdmin, $isOwner);
                if (sizeof($tmpUps) > 0) {
                    foreach ($tmpUps as $up) {
                        if (!in_array($up, $ups)) $ups[] = $up;
                    }
                }
            }
        }
        return $ups;
    }
    
    protected function postUploadTool($nID)
    {
        $ret = '';
        $this->loadPrevUploadDeets($nID);
        $vidTypeID = -1;
        if (isset($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID 
            . "-upload-types"])) {
            $vidTypeID = $GLOBALS["SL"]->getDefID($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID 
                . "-upload-types"], 'Video');
        }
        if (sizeof($this->uploads) > 0) {
            foreach ($this->uploads as $i => $upRow) {
                if ($GLOBALS["SL"]->REQ->has('up' . $upRow->UpID . 'EditVisib') 
                    && intVal($GLOBALS["SL"]->REQ->input('up' . $upRow->UpID . 'EditVisib')) == 1) {
                    $upRow = SLUploads::find($upRow->UpID);
                    if ($upRow && sizeof($upRow) > 0) {
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
            $upRow->UpTreeID    = $GLOBALS["SL"]->treeID;
            $upRow->UpCoreID    = $this->coreID;
            $upRow->UpNodeID    = $nID;
            $upRow->UpLinkFldID = $this->allNodes[$nID]->getTblFldID();
            $upRow->UpLinkRecID = -3;
            if ($upRow->UpLinkFldID > 0) {
                list($tbl, $fld) = $this->allNodes[$nID]->getTblFld();
                list($loopInd, $loopID) = $this->sessData->currSessDataPos($tbl);
                if ($loopID > 0) $upRow->UpLinkRecID = $loopID;
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
                        $ret .= '<div class="slRedDark">Upload Error.' 
                            . /* $_FILES["up" . $nID . "File"]["error"] . */ '</div>';
                    } else {
                        $upFold = $this->getUploadFolder($nID);
                        $this->mkNewFolder($upFold);
                        $upRow->UpStoredFile = $this->getUploadFile($nID);
                        $filename = $upRow->UpStoredFile . '.' . $extension;
                        //if ($this->debugOn) { $ret .= "saving as filename: " . $upFold . $filename . "<br>"; }
                        if (file_exists($upFold . $filename)) Storage::delete($upFold . $filename);
                        $GLOBALS["SL"]->REQ->file('up' . $nID . 'File')->move($upFold, $filename);
                    }
                } else {
                    $ret .= '<div class="slRedDark">Invalid file. Please check the format and try again.</div>';
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