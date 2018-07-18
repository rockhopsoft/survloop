<?php
namespace SurvLoop\Controllers;

use Auth;
use Storage;
use App\Models\SLImages;

class SurvLoopImages
{
    private $nID  = '';
    private $dbID = 1;
    
    public $fold = '';
    public $imgs = [];
    
    function __construct($nID = '', $dbID = 1)
    {
        $this->setImgSet($nID, $dbID);
        $this->fold = '../vendor/' . $GLOBALS["SL"]->sysOpts["cust-package"] . '/src/Uploads';
        return true;
    }
    
    public function setImgSet($nID = '', $dbID = 1)
    {
        $this->nID  = $nID;
        $this->dbID = $dbID;
        return true;
    }
    
    public function chkImgSet($nID = '', $dbID = 1)
    {
        if ((trim($nID) != '' && $nID != $this->nID) || ($dbID != 1 && $dbID != $this->dbID)) {
            $this->setImgSet($nID, $dbID);
        }
        return true;
    }
    
    public function findImgs($onlyImgs = true)
    {
        $this->scanNewUploads();
        $this->imgs = [];
        $imgs = SLImages::where('ImgDatabase', 1)
            ->orderBy('created_at', 'desc')
            ->orderBy('ImgFileLoc', 'asc')
            ->get();
        if ($imgs->isNotEmpty()) {
            foreach ($imgs as $i => $img) {
                if ((!$onlyImgs || in_array($img->ImgType, ['jpg', 'png', 'gif'])) && $this->chkImgAllow($img)) {
                    $this->imgs[] = $img;
                }
            }
        }
        return true;
    }
    
    public function chkImgAllow($img = [])
    {
        $allow = false;
        if ($img && isset($img->ImgID)) {
            if (!isset($img->ImgUserID) || intVal($img->ImgUserID) <= 0) {
                if ($img->ImgDatabase == 1) {
                    $allow = true;
                }
                // might need more checks here, but for now, allow
                $allow = true;
            } else { // UserID > 0
                $user = Auth::user();
                if ($user) {
                    if ($img->ImgUserID == $user->id) {
                        $allow = true;
                    }
                }
                // check image privacy settings (soming soon)
                $allow = true;
            }
        }
        return $allow;
    }
    
    public function scanNewUploads()
    {
        if (session()->has('scanNewUploads') || $GLOBALS["SL"]->REQ->has('refresh')) {
            $fileMap = $GLOBALS["SL"]->mapDirFilesSlim($this->fold);
            $this->scanNewUpInner($fileMap, $this->fold);
            $this->chkAllAnalized();
            session()->put('scanNewUploads', 1);
        }
        return true;
    }
    
    private function scanNewUpInner($fileMap = [], $folder = '', $depth = 0)
    {
        if (sizeof($fileMap) > 0) {
            foreach ($fileMap as $i => $file) {
                if (is_array($file)) $this->scanNewUpInner($fileMap[$i], $folder, (1+$depth));
                else {
                    $img = SLImages::where('ImgFileLoc', $file)
                        ->first();
                    if (!$img || !isset($img->ImgFileLoc)) {
                        $img = new SLImages;
                        $img->ImgDatabase     = 1;
                        $img->ImgUserID       = 0;
                        $img->ImgFileOrig     = '';
                        $img->ImgFileLoc      = $file;
                        $img->ImgFullFilename = '/' . $GLOBALS["SL"]->getPckgProj() . '/uploads/' . $file;
                        $img->save();
                    }
                }
            }
        }
        return true;
    }
    
    public function analizeImg($img = [])
    {
        if (!isset($img->ImgFullFilename) || trim($img->ImgFullFilename) == '') {
            $img->ImgFullFilename = '/' . $GLOBALS["SL"]->getPckgProj() . '/uploads/' . $img->ImgFileLoc;
        }
        $file = $this->fold . '/' . $img->ImgFileLoc;
        $img->ImgType = strtolower(substr($img->ImgFileLoc, strrpos($img->ImgFileLoc, '.')+1));
        switch($img->ImgType) {
            case 'jpeg'; $img->ImgType = 'jpg'; break;
        }
        $img->ImgFileSize = filesize($file);
        if (in_array($img->ImgType, ['jpg', 'png', 'gif'])) {
            $size = getimagesize($file);
            $img->ImgWidth  = $size[0];
            $img->ImgHeight = $size[1];
        }
        $img->save();
        return true;
    }
    
    public function chkAllAnalized()
    {
        $imgs = SLImages::where('ImgDatabase', $GLOBALS["SL"]->dbID)
            ->get();
        if ($imgs->isNotEmpty()) {
            foreach ($imgs as $i => $img) {
                if (!isset($img->ImgType) || trim($img->ImgType) == '') { // update to newest added field
                    $this->analizeImg($img);
                }
            }
        }
        return true;
    }
    
    public function getImgSelect($nID = '', $dbID = 1, $presel = '', $newUp = '')
    {
        $this->chkImgSet($nID, $dbID);
        $this->findImgs();
        return view('vendor.survloop.inc-image-selecter', [
            "nID"    => $this->nID,
            "imgs"   => $this->imgs,
            "presel" => $presel,
            "newUp"  => $newUp
        ])->render();
    }
    
    public function getImgDeet($imgID = -3, $nID = '', $dbID = 1)
    {
        $this->chkImgSet($nID, $dbID);
        $img = SLImages::where('ImgID', $imgID)
            ->where('ImgDatabase', $this->dbID)
            ->first();
        $urlPrint = $img->ImgFullFilename;
        if (strpos($img->ImgFullFilename, '/') === 0) $urlPrint = $GLOBALS["SL"]->sysOpts["app-url"] . $urlPrint;
        $cleanOrig = trim($img->ImgFileOrig);
        if ($cleanOrig != '' && strrpos($cleanOrig, '/') !== false) {
            $cleanOrig = substr($cleanOrig, strrpos($cleanOrig, '/')+1);
        }
        $cleanCurr = $img->ImgFileLoc;
        if (strrpos($cleanCurr, '/') !== false) $cleanCurr = substr($cleanCurr, strrpos($cleanCurr, '/')+1);
        return view('vendor.survloop.inc-image-deet', [
            "nID"       => $this->nID,
            "img"       => $img,
            "urlPrint"  => $urlPrint,
            "cleanOrig" => $cleanOrig,
            "cleanCurr" => $cleanCurr
        ])->render();
    }
    
    public function saveImgDeet($imgID = -3, $nID = '', $dbID = 1)
    {
        $this->chkImgSet($nID, $dbID);
        $img = SLImages::where('ImgID', $imgID)
            ->where('ImgDatabase', $this->dbID)
            ->first();
        $img->ImgTitle = (($GLOBALS["SL"]->REQ->has('img' . $imgID . 'Name')) 
            ? trim($GLOBALS["SL"]->REQ->get('img' . $imgID . 'Name')) : '');
        $img->ImgCredit = (($GLOBALS["SL"]->REQ->has('img' . $imgID . 'Credit')) 
            ? trim($GLOBALS["SL"]->REQ->get('img' . $imgID . 'Credit')) : '');
        $img->ImgCreditUrl = (($GLOBALS["SL"]->REQ->has('img' . $imgID . 'CreditUrl')) 
            ? trim($GLOBALS["SL"]->REQ->get('img' . $imgID . 'CreditUrl')) : '');
        $img->save();
        return '<i class="fa fa-check" aria-hidden="true"></i> Saved';
    }
    
    public function uploadImg($nID = '', $presel = '', $dbID = 1)
    {
        $this->chkImgSet($nID, $dbID);
        $img = new SLImages;
        if ($GLOBALS["SL"]->REQ->hasFile('imgFile' . $nID . '')) { // file upload
            $img->ImgDatabase = $this->dbID;
            $img->ImgNodeID   = $nID;
            $img->ImgUserID   = ((Auth::user()) ? Auth::user()->id : 0);
            $img->ImgFileLoc  = $GLOBALS["SL"]->REQ->file('imgFile' . $nID . '')->getClientOriginalName();
            $img->ImgFileLoc  = str_replace(' ', '_', $img->ImgFileLoc);
            $extension = $GLOBALS["SL"]->REQ->file('imgFile' . $nID . '')->getClientOriginalExtension();
            $mimetype  = $GLOBALS["SL"]->REQ->file('imgFile' . $nID . '')->getMimeType();
            $size      = $GLOBALS["SL"]->REQ->file('imgFile' . $nID . '')->getSize();
            if (in_array($extension, ["gif", "jpeg", "jpg", "png"]) && in_array($mimetype, ["image/gif", "image/jpeg", 
                    "image/jpg", "image/pjpeg", "image/x-png", "image/png"])) {
                if (!$GLOBALS["SL"]->REQ->file('imgFile' . $nID . '')->isValid()) {
                    return '<div class="slRedDark p10">Upload Error.' 
                        . /* $_FILES["up" . $nID . "File"]["error"] . */ '</div>';
                } elseif ($size > 4000000) {
                    return '<div class="slRedDark p10">File size too large. Please compress to less than 4MB.</div>';
                } else {
                    $upFold = '../vendor/' . $GLOBALS["SL"]->sysOpts["cust-package"] . '/src/Uploads/';
                    //if ($this->debugOn) { $ret .= "saving as filename: " . $upFold . $filename . "<br>"; }
                    //if (file_exists($upFold . $img->ImgFileLoc)) Storage::delete($upFold . $img->ImgFileLoc);
                    if (!file_exists($upFold . $img->ImgFileLoc)) {
                        $GLOBALS["SL"]->REQ->file('imgFile' . $nID . '')->move($upFold, $img->ImgFileLoc);
                        $storageFold = '../storage/app/up/' . $GLOBALS["SL"]->getPckgProj() . '/';
                        copy($upFold . $img->ImgFileLoc, $storageFold . $img->ImgFileLoc);
                    }
                }
            } else {
                return '<div class="slRedDark p10">Invalid file. Please check the format and try again.</div>';
            }
            $img->save();
            $this->analizeImg($img);
        }
        if (isset($img->ImgID) && intVal($img->ImgID) > 0) {
            return view('vendor.survloop.inc-image-uploaded', [
                "nID"    => $this->nID,
                "presel" => $presel,
                "imgID"  => $img->ImgID
            ])->render();
        }
        return '<i class="fa fa-times" aria-hidden="true"></i> Something went wrong with your upload.';
    }
    
    
}