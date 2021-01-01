<?php
/**
  * TreeSurvUpload is a mid-level class atop Survloop's branching tree, specifically for 
  * uploading functionality in surveys and pages.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use Auth;
use Storage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\File\File;
use Intervention\Image\ImageManagerStatic as Image;
use App\Models\User;
use App\Models\SLUploads;
use App\Models\SLNode;
use App\Models\SLNodeResponses;
use RockHopSoft\Survloop\Controllers\SurvloopPDF;
use RockHopSoft\Survloop\Controllers\DeliverImage;
use RockHopSoft\Survloop\Controllers\Tree\TreeNodeSurv;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurv;

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
        $part1 = str_shuffle("abcdefghijklmnopqrstuvwxyz"
            . "ABCDEFGHIJKLMNOPQRSTUVWXYZ");
        $part1 = substr($part1, 0, 1);
        $part2 = str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"
            . "ABCDEFGHIJKLMNOPQRSTUVWXYZ");
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
            $this->uploadTypes = $GLOBALS["SL"]->def->getSet(
                $GLOBALS["SL"]->sysOpts[$upType]
            );
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
            $chk = $this->getTreeUploadsRecs($treeID);
        }
        if ($chk->isNotEmpty()) {
            foreach ($chk as $i => $up) {
                $hasUpload = (isset($up->up_upload_file) && trim($up->up_upload_file) != '');
                $hasStored = (isset($up->up_stored_file) && trim($up->up_stored_file) != '');
                $hasVidLnk = (isset($up->up_video_link)  && trim($up->up_video_link)  != '');
                if (($hasUpload && $hasStored) || $hasVidLnk) {
                    $ret[] = $up;
                }
            }
        }
        return $ret;
    }
    
    private function getTreeUploadsRecs($treeID = 0)
    {
        if ($treeID <= 0) {
            $treeID = $this->getUpTree();
        }
        return SLUploads::where('up_tree_id', $treeID)
            ->where('up_core_id', $this->coreID)
            ->orderBy('created_at', 'asc')
            ->get();
    }
    
    protected function prevUploadsPDF()
    {
        $ret = [];
        if ($GLOBALS["SL"]->dataPerms == 'none') {
            return $ret;
        }
        $noPub = '';
        $this->loadPdfByID();
        $chk = $this->getTreeUploadsRecs();
        if ($chk->isNotEmpty()) {
            $cnt = 0;
            foreach ($chk as $i => $up) {
                if (isset($up->up_upload_file) && trim($up->up_upload_file) != ''
                    && isset($up->up_stored_file) && trim($up->up_stored_file) != '') {
                    if ($GLOBALS["SL"]->REQ->has('resize')) {
                        $this->checkImgResize($up);
                    }
                    $deet = $this->loadUpDeets($up);
                    if (sizeof($deet) > 0) {
                        $cnt++;
                        $title = $this->prevUploadImageTitlePDF($cnt, $up) . '<br />';
                        $public = [ 'pdf', 'public' ];
                        if ($deet["privacy"] == 'Block'
                            && (in_array($GLOBALS["SL"]->pageView, $public)
                            || in_array($GLOBALS["SL"]->dataPerms, $public)) ) {
                            $noPub .= $title;
                        } elseif (isset($deet["file"])) {
                            $ext = substr($up->up_upload_file, strlen($up->up_upload_file)-4);
                            if (strtolower($ext) == '.pdf') {
                                $compressed = str_replace('.pdf', '-.pdf', $deet["file"]);
                                if (!file_exists($compressed)
                                    || $GLOBALS["SL"]->REQ->has('refresh')) {
                                    $this->v["pdf-gen"]->simplifyPdf($deet["file"], $compressed);
                                }
                                $ret[] = $compressed;
                            } else { // create PDF for each image
                                $ret[] = $this->prevUploadImagePDF($deet, $title);
                            }
                        }
                    }
                }
            }
            if ($noPub != '') {
                $noPub = '<h4>Uploads Not Published</h4>' . $noPub;
                $noPubFile = '-attach-not-published.pdf';
                $noPubFile = str_replace('.pdf', $noPubFile, $this->v["pdf-file"]);
                $this->v["pdf-gen"]->genSimplePDF($noPub, $noPubFile);
                $ret[] = $noPubFile;
            }
        }
        $fileAttach = str_replace('.pdf', '-attach.pdf', $this->v["pdf-file"]);
        if (file_exists($fileAttach)) {
            unlink($fileAttach);
        }
        if (sizeof($ret) > 0) {
            $this->v["pdf-gen"]->mergePdfs($ret, $fileAttach);
        }
        return $ret;
    }
    
    protected function prevUploadImageTitlePDF($cnt, $up)
    {
        $title = '<h5>Upload #' . $cnt;
        if (isset($up->up_title) && trim($up->up_title) != '') {
            $title .= ': ' . $up->up_title;
        }
        return $title . '</h5>' . $up->up_upload_file;
    }
    
    protected function prevUploadImagePDF($deet, $title = '')
    {
        $imgPdfFile = str_replace('.jpg', '.pdf', $deet["file"]);
        if (isset($deet["file"])
            && file_exists($deet["file"])
            && (!file_exists($imgPdfFile) || $GLOBALS["SL"]->REQ->has('refresh'))) {
            list($width, $height, $type, $attr) = getimagesize($deet["file"]);
            $title = '<div class="page-break-avoid" style="width: 100%;">'
                . $title . '<img src="data:image/jpeg;base64, '
                . base64_encode(file_get_contents($deet["file"])) . '" style="'
                . (($width < ($height*0.7)) ? 'width: 67%;' : 'width: 100%;')
                . '" ></div>';
            $this->v["pdf-gen"]->genSimplePDF($title, $imgPdfFile);
        }
        return $imgPdfFile;
    }
    
    public function retrieveUploadFile($upID = '', $refresh = false)
    {
        $this->checkPageViewPerms();
        if (!$this->isPublic() 
            && !$this->isStaffOrAdmin() 
            && !$this->v["isOwner"]) {
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
                // != 'Public' && !$this->isStaffOrAdmin() && !$this->v["isOwner"]) {
                    return $this->retrieveUploadFail();
                }
                return $this->previewImg($deet, $refresh);
            }
        }
        return $this->retrieveUploadFail();
    }
    
    public function retrieveUploadFail()
    {
        return '';
    }
    
    public function previewImg($up, $refresh = false)
    {
        if (is_array($up) && sizeof($up) > 0 && isset($up["file"])) {
            $filename = $up["file"];
            if ($GLOBALS["SL"]->REQ->has('orig')) {
                $filename = $up["fileOG"];
            }
            if ($GLOBALS["SL"]->REQ->has('refresh')) {
                $refresh = true;
            }
            $lifetime = 0;
            if ($refresh) {
                $lifetime = 10;
            }
            $img = new DeliverImage($filename, 0, $refresh);
            return $img->delivery();
        }
        return '';
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
        $upFold = $this->getUploadFolder();
        $treeSlug = $GLOBALS["SL"]->treeRow->tree_slug;
        $ret["ind"]      = $i;
        $ret["privacy"]  = $this->loadUpDeetPrivacy($upRow);
        $ret["ext"]      = $GLOBALS["SL"]->getFileExt($upRow->up_upload_file);
        if ($ret["ext"] == 'pdf') {
            $ret["filename"] = $upRow->up_stored_file . '.pdf';
            $ret["fileOG"]
                = $ret["file"]
                = $upFold . $upRow->up_stored_file . '.pdf';
        } else {
            $ret["filename"] = $upRow->up_stored_file . '.jpg';
            $ret["fileOG"]   = $upFold . $upRow->up_stored_file . '-orig.' . $ret["ext"];
            $ret["file"]     = $upFold . $upRow->up_stored_file . '.jpg';
        }
        $ret["filePub"]  = '/up/' . $treeSlug . '/' . $this->coreID . '/' . $ret["filename"];
        $ret["fileFrsh"] = '/up-fresh-' . rand(100000, 1000000) . '/' . $treeSlug 
            . '/' . $this->coreID . '/' . $ret["filename"] . '?refresh=1';
        $ret["fileOrig"] = $upRow->up_upload_file;
        $ret["fileLnk"]  = '<a href="' . $ret["filePub"] . '" target="_blank">' 
            . $upRow->up_upload_file . '</a>';
        $ret["youtube"]  
            = $ret["vimeo"]
            = $ret["archiveVid"]
            = $ret["instagram"]
            = $ret["otherLnk"]
            = '';
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
            SLUploads::find($upRow->up_id)
                ->delete();
        } else {
            $imgTypes = ['png', 'gif', 'jpg', 'jpeg'];
            if (intVal($upRow->up_type) == $vidTypeID) {
                $this->loadUpDeetsVideo($ret, $upRow);
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
    
    protected function loadUpDeetsVideo(&$ret, $upRow)
    {
        if (stripos($upRow->up_video_link, 'youtube') !== false 
            || stripos($upRow->up_video_link, 'youtu.be') !== false) {
            $ret["youtube"] = $GLOBALS["SL"]->getYoutubeID($upRow->up_video_link);
            $ret["fileLnk"] = '<a href="' . $upRow->up_video_link 
                . '" target="_blank">youtube/' . $ret["youtube"] . '</a>';
        } elseif (stripos($upRow->up_video_link, 'vimeo.com') !== false) {
            $ret["vimeo"] = $GLOBALS["SL"]->getVimeoID($upRow->up_video_link);
            $ret["fileLnk"] = '<a href="' . $upRow->up_video_link 
                . '" target="_blank">vimeo/' . $ret["vimeo"] . '</a>';
            $ret["thmbUrl"] = '';
        } elseif (stripos($upRow->up_video_link, 'archive.org') !== false) {
            $ret["archiveVid"] = $GLOBALS["SL"]->getArchiveOrgVidID($upRow->up_video_link);
            $ret["fileLnk"] = '<a href="' . $upRow->up_video_link 
                . '" target="_blank">archive.org/details/' . $ret["archiveVid"] . '</a>';
            $ret["thmbUrl"] = '';
        } elseif (stripos($upRow->up_video_link, 'instagram.com') !== false) {
            $ret["instagram"] = $GLOBALS["SL"]->getInstagramID($upRow->up_video_link);
            $ret["fileLnk"] = '<a href="' . $upRow->up_video_link 
                . '" target="_blank">instagram/' . $ret["instagram"] . '</a>';
            $ret["thmbUrl"] = $ret["instagramShortLink"] = '';
            /*
            $jsonUrl = $upRow->up_video_link . '?__a=1';
            $json = json_decode(file_get_contents($jsonUrl), true);
            if (isset($json["graphql"])
                && isset($json["graphql"]["shortcode_media"])) {
                if (isset($json["graphql"]["shortcode_media"]["thumbnail_src"])) {
                    $ret["thmbUrl"] = $json["graphql"]["shortcode_media"]["thumbnail_src"];
                }
                if (isset($json["graphql"]["shortcode_media"]["display_url"])) {
                    $ret["instagramShortLink"] = $json["graphql"]["shortcode_media"]["display_url"];
                }
            }
            */
        } elseif (isset($upRow->up_video_link)) {
            $ret["otherLnk"] = trim($upRow->up_video_link);
        }
        return true;
    }
    
    protected function loadPrevUploadDeets($nID = -3)
    {
        $this->uploads = $this->prevUploadList($nID);
        $treeID = $this->getUpTree();
        $this->upDeets = [];
        if (sizeof($this->uploads) > 0) {
            foreach ($this->uploads as $i => $upRow) {
                if ($GLOBALS["SL"]->REQ->has('upRotate') 
                    && intVal($GLOBALS["SL"]->REQ->get('upRotate')) == $upRow->up_id
                    && $GLOBALS["SL"]->REQ->has('rots') 
                    && intVal($GLOBALS["SL"]->REQ->get('rots')) > 0) {

                    $upFold = $this->getUploadFolder();
                    $file = $upRow->up_stored_file . '.jpg';
                    Image::configure(array('driver' => 'imagick'));
                    $image = Image::make($upFold . $file);
                    $image->rotate(90*intVal($GLOBALS["SL"]->REQ->get('rots')));
                    //unlink($upFold . $file);
                    $image->save($upFold . $file, 85, 'jpg');
                }
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
                "edit"        => $edit,
                "v"           => $this->v
            ]
        )->render();
    }
    
    protected function getUploads($nID, $isAdmin = false, $isOwner = false, $style = '')
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
                $canShow = $this->canShowUpload($this->upDeets[$i], $isAdmin, $isOwner);
                $view = 'vendor.survloop.forms.uploads-print';
                if ($style == 'text') {
                    $view .= '-text';
                }
                $ups[] = view(
                    $view, 
                    [
                        "cnt"         => sizeof($ups),
                        "nID"         => $nID,
                        "REQ"         => $GLOBALS["SL"]->REQ,
                        "height"      => 400,
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
    
    protected function canShowUpload($upDeets, $isAdmin = false, $isOwner = false)
    {
        return ($isAdmin 
            || $isOwner 
            || $this->isStaffOrAdmin() 
            || $this->v["isOwner"]);
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
                $upRow->up_video_duration = $GLOBALS["SL"]
                    ->getYoutubeDuration($upRow->up_video_link);
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
                        $origFile = $upRow->up_stored_file . '-orig.' 
                            . strtolower($extension);
                        //if ($GLOBALS["SL"]->debugOn) { $ret .= "saving as filename: " . $upFold . $origFile . "<br>"; }
                        if (file_exists($upFold . $origFile)) {
                            Storage::delete($upFold . $origFile);
                        }
                        $GLOBALS["SL"]->REQ->file($file)->move($upFold, $origFile);
                        $filename = $upRow->up_stored_file;
                        if (strtolower($mimetype) == "application/pdf") {
                            $minFile = $upRow->up_stored_file . '.pdf';
                            $pdf = new SurvloopPDF($GLOBALS["SL"]->coreTbl);
                            $pdf->simplifyPdf($upFold . $origFile, $upFold . $minFile);
                        } else {
                            $this->upImgResize($upFold, $filename, $origFile);
                        }
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
    
    protected function checkImgResize($upRow)
    {
        $ret = '';
        if (isset($upRow->up_upload_file) && trim($upRow->up_upload_file) != '') {
            $extPos = strrpos($upRow->up_upload_file, '.');
            if ($extPos > 0) {
                $extension = strtolower(trim(substr($upRow->up_upload_file, $extPos)));
                if (in_array($extension, [".gif", ".jpeg", ".jpg", ".png"])) {
                    $upFold = $GLOBALS["SL"]->getCoreUpFold(
                        $upRow->up_tree_id, 
                        $upRow->up_core_id
                    );
                    if ($upFold != '' && trim($upRow->up_stored_file) != '') {
                        $resize = $GLOBALS["SL"]->REQ->has('refresh');
                        $filename = $upRow->up_stored_file;
                        $origFile = $upRow->up_stored_file . '-orig' . $extension;
                        if (!file_exists($upFold . $origFile)
                            && file_exists($upFold . $filename . $extension)) {
                            copy($upFold . $filename . $extension, $upFold . $origFile);
                            $resize = true;
                        }
                        if (file_exists($upFold . $origFile)
                            && !file_exists($upFold . $filename . '.jpg')) {
                            $resize = true;
                        }
                        if ($resize) {
                            $this->upImgResize($upFold, $filename, $origFile);
                            $ret .= '<br />' . $upFold . ', ' . $filename . ', ' . $origFile;
                        }
                    }
                }
            }
        }
        return $ret;
    }
    
    protected function upImgResize($upFold, $filename, $origFile)
    {
        Image::configure(array('driver' => 'imagick'));
        $filename .= '.jpg';
        $image = Image::make($upFold . $origFile);
        $isLarge = false;
        $width = $height = $max = 1400;
        if ($image->width() > $image->height()) {
            $isLarge = ($image->width() > $max);
            $height=null;
        } else {
            $isLarge = ($image->height() > $max);
            $width=null;
        }
        if ($isLarge) {
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        if (file_exists($upFold . $filename)) {
            unlink($upFold . $filename);
        }
        $image->save($upFold . $filename, 85, 'jpg');
        return true;
    }
    
    public function checkImgResizeAll()
    {
        $cnt = 0;
        ini_set('max_execution_time', 600);
        $upRows = SLUploads::where('up_tree_id', $GLOBALS["SL"]->treeID)
            ->whereNotNull('up_upload_file')
            ->where('up_upload_file', 'NOT LIKE', '')
            ->whereNotNull('up_stored_file')
            ->where('up_stored_file', 'NOT LIKE', '')
            ->get();
        echo '<h2>Checking ' . number_format($upRows->count()) . '</h2>';
        $ret = '';
        if ($upRows->isNotEmpty()) {
            foreach ($upRows as $upRow) {
                if ($cnt < 100) {
                    $curr = $this->checkImgResize($upRow);
                    if ($curr != '') {
                        $ret .= $curr;
                        $cnt++;
                    }
                }
            }
        }
        if ($GLOBALS["SL"]->REQ->has('redir')) {
            return $this->redir($GLOBALS["SL"]->REQ->get('redir'), true);
        }
        echo 'Done. Resized ' . number_format($cnt) . '<br /><br />' . $ret;
        exit;
    }
    
    protected function mkNewFolder($fold)
    {
        $this->checkBaseFolders();
        $this->checkFolder($fold);
        return true;
    }                        
    
}

?>