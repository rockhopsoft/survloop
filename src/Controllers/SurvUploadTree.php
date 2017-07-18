<?php
namespace SurvLoop\Controllers;

use Auth;
use Storage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\File\File;

use App\Models\User;       
use App\Models\SLNodeResponses;

class SurvUploadTree extends SurvLoopTree
{
    public $uploadTypes      = array();
    protected $upLinks       = array();
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
        $upFolds = [
            '../storage/app/up', 
            '../storage/app/up/avatar', 
            '../storage/app/up/evidence', 
            '../storage/app/up/evidence/' . date("Y/m/d")
        ];
        foreach ($upFolds as $fold) $this->checkFolder($fold);
        return true;
    }
    
    protected function getUploadFolder($nID = -3)
    {
        $coreRow = $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0];
        $coreAbbr = $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl];
        $fold = '../storage/app/up/evidence/' . str_replace('-', '/', substr($coreRow->created_at, 0, 10)) 
            . '/' . $coreRow->{ $coreAbbr . 'UniqueStr' } . '/';
        return $fold;
    }
    
    protected function getUploadFile($nID)
    {
        return $this->getRandStr('Evidence', 'EvidStoredFile', 30);
    }
    
    protected function getUploadLinks($nID)
    {
        return array();
    }
    
    protected function getUploadSet($nID)
    {
        return '+';
    }
    
    protected function prevUploadList($nID)
    {
        return array();
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
        $handler = new File($up["file"]);
        $file_time = $handler->getMTime(); // Get the last modified time for the file (Unix timestamp)
        $lifetime = 86400; // One day in seconds
        $header_etag = md5($file_time . $up["file"]);
        $header_last_modified = gmdate('r', $file_time);
        $headers = array(
            'Content-Disposition' => 'inline; filename="' . $this->coreID . '-' . (1+$up["ind"]) 
                                        . '-' . $up["fileOrig"] . '"',
            'Last-Modified'       => $header_last_modified,
            'Cache-Control'       => 'must-revalidate',
            'Expires'             => gmdate('r', $file_time + $lifetime),
            'Pragma'              => 'public',
            'Etag'                => $header_etag
        );
        // Is the resource cached?
        $h1 = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) 
            && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $header_last_modified);
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
        return Response::make(file_get_contents($up["file"]), 200, $headers);
    }
    
    protected function uploadTool($nID)
    {
        $this->loadUploadTypes();
        $GLOBALS["SL"]->pageAJAX .= 'window.refreshUpload = function () { $("#uploadAjax").load("?ajax=1&upNode=' 
            . $nID . '"); }' . "\n";
        $this->pageJSvalid .= "if (document.getElementById('n" . $nID . "VisibleID').value == 1) reqUploadTitle(" 
            . $nID . ");\n";
        $GLOBALS["SL"]->pageJAVA .= "addResTot(" . $nID . ", 4);\n";
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
        $this->upLinks = $this->getUploadLinks($nID);
        $this->uploads = $this->prevUploadList($nID, $this->upLinks);
        $this->upDeets = array();
        if ($this->uploads && sizeof($this->uploads) > 0) {
            foreach ($this->uploads as $i => $upRow) {
                $this->upDeets[$i]["ind"]         = $i;
                $this->upDeets[$i]["privacy"]     = $upRow->privacy;
                $this->upDeets[$i]["ext"] = '';
                if (trim($upRow->upFile) != '') {
                    $tmpExt = explode(".", $upRow->upFile);
                    $this->upDeets[$i]["ext"] = $tmpExt[(sizeof($tmpExt)-1)];
                }
                $this->upDeets[$i]["filename"] = $upRow->storeFile . '.' . $this->upDeets[$i]["ext"];
                $this->upDeets[$i]["file"]     = $this->getUploadFolder($nID) . $upRow->storeFile 
                    . '.' . $this->upDeets[$i]["ext"];
                $this->upDeets[$i]["filePub"]  = '/up/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/' . $this->coreID 
                    . '/' . $upRow->storeFile . '.' . $this->upDeets[$i]["ext"];
                $this->upDeets[$i]["fileOrig"] = $upRow->upFile;
                $this->upDeets[$i]["fileLnk"]  = '<a href="' . $this->upDeets[$i]["filePub"] 
                    . '" target="_blank">' . $upRow->upFile . '</a>';
                $this->upDeets[$i]["youtube"]  = '';
                $this->upDeets[$i]["vimeo"]    = '';
                $vidTypeID = $GLOBALS["SL"]->getDefID($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID 
                    . "-upload-types"], 'Video');
                if ($GLOBALS["SL"]->REQ->has('step') && $GLOBALS["SL"]->REQ->step == 'uploadDel' 
                    && $GLOBALS["SL"]->REQ->has('alt') && intVal($GLOBALS["SL"]->REQ->alt) == $upRow->id) {
                    if (file_exists($this->upDeets[$i]["file"]) && trim($upRow->type) != $vidTypeID) {
                        unlink($this->upDeets[$i]["file"]);
                    }
                    $this->sessData->deleteDataItem($nID, 'Evidence', $upRow->id);
                } else {
                    if (trim($upRow->type) == $vidTypeID) {
                        if (stripos($upRow->video, 'youtube') !== false 
                            || stripos($upRow->video, 'youtu.be') !== false) {
                            $this->upDeets[$i]["youtube"] = $this->getYoutubeID($upRow->video);
                            $this->upDeets[$i]["fileLnk"] = '<a href="' . $upRow->video . '" target="_blank">youtube/' 
                                . $this->upDeets[$i]["youtube"] . '</a>';
                        } elseif (stripos($upRow->video, 'vimeo.com') !== false) {
                            $this->upDeets[$i]["vimeo"] = $this->getVimeoID($upRow->video);
                            $this->upDeets[$i]["fileLnk"] = '<a href="' . $upRow->video . '" target="_blank">vimeo/' 
                                . $this->upDeets[$i]["vimeo"] . '</a>';
                        }
                    } elseif (!file_exists($this->upDeets[$i]["file"]))  {
                        $this->upDeets[$i]["fileLnk"] .= ' &nbsp;&nbsp;<span class="slRedDark"
                            ><i class="fa fa-exclamation-triangle"></i> <i>File Not Found</i></span>';
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
        return view('vendor.survloop.upload-previous', [
            "nID"         => $nID,
            "REQ"         => $this->REQ,
            "height"      => 160,          
            "width"       => 330,
            "uploads"     => $this->uploads, 
            "upDeets"     => $this->upDeets, 
            "uploadTypes" => $this->uploadTypes, 
            "vidTypeID"   => $GLOBALS["SL"]->getDefID($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID 
                . "-upload-types"], 'Video'),
            "v"           => $this->v
        ])->render();
    }
    
    protected function getUploads($nID, $isAdmin = false, $isOwner = false)
    {
        $this->prepPrevUploads($nID);
        if (!$this->uploads || sizeof($this->uploads) == 0) return [];
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
    
    protected function getUploadsList($list = [], $isAdmin = false, $isOwner = false)
    {
        $uploads = [];
        if ($list && sizeof($list) == 0) return [];
        foreach ($list as $nID) {
            $tmp = $this->getUploads($nID, $isAdmin, $isOwner);
            if (sizeof($tmp) > 0) {
                foreach ($tmp as $up) $uploads[] = $up;
            }
        }
        return $uploads;
    }
    
    protected function postUploadTool($nID)
    {
        $ret = '';
        $this->loadPrevUploadDeets($nID);
        $vidTypeID = $GLOBALS["SL"]->getDefID($GLOBALS["SL"]->sysOpts["tree-" . $GLOBALS["SL"]->treeID 
            . "-upload-types"], 'Video');
        if (sizeof($this->uploads) > 0) {
            foreach ($this->uploads as $i => $upRow) {
                if ($GLOBALS["SL"]->REQ->has('up'.$upRow->id.'EditVisib') 
                    && intVal($GLOBALS["SL"]->REQ->input('up'.$upRow->id.'EditVisib')) == 1) {
                    $upArr = [ 
                        'id'      => $upRow->id, 
                        'title'   => $GLOBALS["SL"]->REQ->input('up'.$upRow->id.'EditTitle'), 
                        //'desc'    => $GLOBALS["SL"]->REQ->input('up'.$upRow->id.'EditDesc'), 
                        'type'    => $GLOBALS["SL"]->REQ->input('up'.$upRow->id.'EditType'), 
                        'privacy' => $GLOBALS["SL"]->REQ->input('up'.$upRow->id.'EditPrivacy')
                    ];
                    $ret .= ' ' . $this->updateUploadRecord($nID, $upArr);
                }
            }
        }
        if ($GLOBALS["SL"]->REQ->has('step') && $GLOBALS["SL"]->REQ->has('n' . $nID . 'fld')) {
            $upArr = [
                'type'      => $GLOBALS["SL"]->REQ->input('n' . $nID . 'fld'), 
                'title'     => $GLOBALS["SL"]->REQ->input('up' . $nID . 'Title'), 
                //'desc'      => $GLOBALS["SL"]->REQ->input('up' . $nID . 'Desc'), 
                'privacy'   => $GLOBALS["SL"]->REQ->input('up' . $nID . 'Privacy'), 
                'upFile'    => '', 
                'extension' => '', 
                'mimetype'  => '', 
                'size'      => 0, 
                'storeFile' => '', 
                'video'     => '', 
                'vidDur'    => -1
            ];
            if ($GLOBALS["SL"]->REQ->has('up' . $nID . 'Vid') && $GLOBALS["SL"]->REQ->input('n' . $nID . 'fld') == $vidTypeID) {
                $upArr["video"] = $GLOBALS["SL"]->REQ->input('up' . $nID . 'Vid');
                $upArr["vidDur"] = $this->getYoutubeDuration($upArr["video"]);
                $this->storeUploadRecord($nID, $upArr, $this->upLinks);
            } elseif ($GLOBALS["SL"]->REQ->hasFile('up' . $nID . 'File')) { // file upload
                $upArr["upFile"]    = $GLOBALS["SL"]->REQ->file('up' . $nID . 'File')->getClientOriginalName();
                $upArr["extension"] = $GLOBALS["SL"]->REQ->file('up' . $nID . 'File')->getClientOriginalExtension();
                $upArr["mimetype"]  = $GLOBALS["SL"]->REQ->file('up' . $nID . 'File')->getMimeType();
                $upArr["size"]      = $GLOBALS["SL"]->REQ->file('up' . $nID . 'File')->getSize();
                if (in_array($upArr["extension"], array("gif", "jpeg", "jpg", "png", "pdf")) 
                    && in_array($upArr["mimetype"], array("image/gif", "image/jpeg", "image/jpg", "image/pjpeg", 
                        "image/x-png", "image/png", "application/pdf"))) {
                    if (!$GLOBALS["SL"]->REQ->file('up' . $nID . 'File')->isValid()) {
                        $ret .= '<div class="slRedDark">Upload Error.' 
                            . /* $_FILES["up" . $nID . "File"]["error"] . */ '</div>';
                    } else {
                        $upFold = $this->getUploadFolder($nID);
                        $this->mkNewFolder($upFold);
                        $upArr["storeFile"] = $this->getUploadFile($nID);
                        $filename = $upArr["storeFile"] . '.' . $upArr["extension"];
                        if ($this->debugOn || true) { $ret .= "saving as filename: " . $upFold.$filename . "<br>"; }
                        if (file_exists($upFold.$filename)) Storage::delete($upFold.$filename);
                        $GLOBALS["SL"]->REQ->file('up' . $nID . 'File')->move($upFold, $filename);
                        $this->storeUploadRecord($nID, $upArr, $this->upLinks);
                    }
                } else {
                    $ret .= '<div class="slRedDark">Invalid file. Please check the format and try again.</div>';
                }
            }
        }
        return $ret;
    }
    
    // $upArr = array('type' => '', 'title' => '', 'desc' => '', 'privacy' => '', 'upFile' => '', 
    // 'storeFile' => '', 'video' => '', 'vidDur' => 0);
    protected function storeUploadRecord($nID, $upArr, $upLinks) { return true; }
    protected function updateUploadRecord($nID, $upArr) { return true; }
    
    protected function mkNewFolder($fold)
    {
        $this->checkBaseFolders();
        $this->checkFolder($fold);
        return true;
    }
    
    function checkFolder($fold)
    {
        if (!is_dir(storage_path($fold))) Storage::MakeDirectory(storage_path($fold));
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