<?php
/**
  * SurvloopImages is a class which manages Survloop's uploaded images like a CMS.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.21
  */
namespace RockHopSoft\Survloop\Controllers;

use Auth;
use Storage;
use App\Models\SLImages;

class SurvloopImages
{
    private $nID  = '';
    private $dbID = 1;

    public $fold = '';
    public $imgs = [];

    function __construct($nID = '', $dbID = 1)
    {
        $this->setImgSet($nID, $dbID);
        $this->fold = '../storage/app/up/' . $GLOBALS["SL"]->getPckgProj();
        //$this->fold = '../vendor/'
        //    . $GLOBALS["SL"]->sysOpts["cust-package"] . '/src/Uploads';
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
        if ((trim($nID) != '' && $nID != $this->nID)
            || ($dbID != 1 && $dbID != $this->dbID)) {
            $this->setImgSet($nID, $dbID);
        }
        return true;
    }

    public function findImgs($onlyImgs = true)
    {
        $this->scanNewUploads();
        $this->imgs = [];
        $imgs = SLImages::where('img_database_id', 1)
            ->orderBy('created_at', 'desc')
            ->orderBy('img_file_loc', 'asc')
            ->get();
        if ($imgs->isNotEmpty()) {
            $imgExts = [ 'jpg', 'png', 'gif' ];
            foreach ($imgs as $i => $img) {
                if ((!$onlyImgs || in_array($img->img_type, $imgExts))
                    && $this->chkImgAllow($img)) {
                    $this->imgs[] = $img;
                }
            }
        }
        return true;
    }

    public function chkImgAllow($img = [])
    {
        $allow = false;
        if ($img && isset($img->img_id)) {
            if (!isset($img->img_user_id)
                || intVal($img->img_user_id) <= 0) {
                if ($img->img_database_id == 1) {
                    $allow = true;
                }
                // might need more checks here, but for now, allow
                $allow = true;
            } else { // UserID > 0
                $user = Auth::user();
                if ($user) {
                    if ($img->img_user_id == $user->id) {
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
        if (session()->has('scanNewUploads')
            || $GLOBALS["SL"]->REQ->has('refresh')) {
            $fileMap = $GLOBALS["SL"]->mapDirFilesSlim($this->fold);
            $this->scanNewUpInner($fileMap, $this->fold);
            $this->chkAllAnalized();
            session()->put('scanNewUploads', 1);
            session()->save();
        }
        return true;
    }

    private function scanNewUpInner($fileMap = [], $folder = '', $depth = 0)
    {
        if (sizeof($fileMap) > 0) {
            foreach ($fileMap as $i => $file) {
                if (is_array($file)) {
                    $this->scanNewUpInner($fileMap[$i], $folder, (1+$depth));
                } else {
                    $img = SLImages::where('img_file_loc', $file)
                        ->first();
                    if (!$img || !isset($img->img_file_loc)) {
                        $fileName = '/' . $GLOBALS["SL"]->getPckgProj()
                            . '/uploads/' . $file;
                        $img = new SLImages;
                        $img->img_database_id   = 1;
                        $img->img_user_id       = 0;
                        $img->img_file_orig     = '';
                        $img->img_file_loc      = $file;
                        $img->img_full_filename = $fileName;
                        $img->save();
                    }
                }
            }
        }
        return true;
    }

    public function analizeImg($img = [])
    {
        if (!isset($img->img_full_filename)
            || trim($img->img_full_filename) == '') {
            $img->img_full_filename = '/' . $GLOBALS["SL"]->getPckgProj()
                . '/uploads/' . $img->img_file_loc;
        }
        //$file = $this->fold . '/' . $img->img_file_loc;
        $pos = strrpos($img->img_file_loc, '.');
        $img->img_type = substr($img->img_file_loc, ($pos+1));
        $img->img_type = strtolower($img->img_type);
        if ($img->img_type == 'jpeg') {
            $img->img_type = 'jpg';
        }
        $img->img_file_size = filesize($img->img_full_filename);
        if (in_array($img->img_type, ['jpg', 'png', 'gif'])) {
            $size = getimagesize($img->img_full_filename);
            $img->img_width  = $size[0];
            $img->img_height = $size[1];
        }
        $img->save();
        return true;
    }

    public function chkAllAnalized()
    {
        $imgs = SLImages::where('ImgDatabaseID', $GLOBALS["SL"]->dbID)
            ->get();
        if ($imgs->isNotEmpty()) {
            foreach ($imgs as $i => $img) {
                if (!isset($img->img_type)
                    || trim($img->img_type) == '') {
                    // update to newest added field
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
        return view(
            'vendor.survloop.forms.inc-image-selecter',
            [
                "nID"    => $this->nID,
                "imgs"   => $this->imgs,
                "presel" => $presel,
                "newUp"  => $newUp
            ]
        )->render();
    }

    public function getImgDeet($imgID = -3, $nID = '', $dbID = 1)
    {
        $this->chkImgSet($nID, $dbID);
        $img = SLImages::where('img_id', $imgID)
            ->where('img_database_id', $this->dbID)
            ->first();
        $urlPrint = $img->img_full_filename;
        if (strpos($img->img_full_filename, '/') === 0) {
            $urlPrint = $GLOBALS["SL"]->sysOpts["app-url"] . $urlPrint;
        }
        $cleanOrig = trim($img->img_file_orig);
        if ($cleanOrig != '' && strrpos($cleanOrig, '/') !== false) {
            $cleanOrig = substr($cleanOrig, strrpos($cleanOrig, '/')+1);
        }
        $cleanCurr = $img->img_file_loc;
        if (strrpos($cleanCurr, '/') !== false) {
            $cleanCurr = substr($cleanCurr, strrpos($cleanCurr, '/')+1);
        }
        return view(
            'vendor.survloop.forms.inc-image-deet',
            [
                "nID"       => $this->nID,
                "img"       => $img,
                "urlPrint"  => $urlPrint,
                "cleanOrig" => $cleanOrig,
                "cleanCurr" => $cleanCurr
            ]
        )->render();
    }

    public function saveImgDeet($imgID = -3, $nID = '', $dbID = 1)
    {
        $this->chkImgSet($nID, $dbID);
        $img = SLImages::where('img_id', $imgID)
            ->where('img_database_id', $this->dbID)
            ->first();
        $img->img_title
            = $img->img_credit
            = $img->img_credit_url
            = '';
        $fld = 'img' . $imgID . 'Name';
        if ($GLOBALS["SL"]->REQ->has($fld)) {
            $img->img_title = trim($GLOBALS["SL"]->REQ->get($fld));
        }
        $fld = 'img' . $imgID . 'Credit';
        if ($GLOBALS["SL"]->REQ->has($fld)) {
            $img->img_credit = trim($GLOBALS["SL"]->REQ->get());
        }
        $fld = 'img' . $imgID . 'CreditUrl';
        if ($GLOBALS["SL"]->REQ->has($fld)) {
            $img->img_credit_url = trim($GLOBALS["SL"]->REQ->get($fld));
        }
        $img->save();
        return '<i class="fa fa-check" aria-hidden="true"></i> Saved';
    }

    public function uploadImgDir($nID = '', $path = '', $file = '', $title = '')
    {
        return $this->uploadImg($nID, '', 1, $path, $file, $title);
    }

    public function uploadImg($nID = '', $presel = '', $dbID = 1, $path = '', $file = '', $title = '')
    {
        $exts  = $this->allowedExts();
        $mimes = $this->allowedMimes();
        $this->chkImgSet($nID, $dbID);
        if ($GLOBALS["SL"]->REQ->hasFile('imgFile' . $nID . '')) {
            $fileObj = $GLOBALS["SL"]->REQ->file('imgFile' . $nID . '');
            $extension = $fileObj->getClientOriginalExtension();
            $origName = $fileObj->getClientOriginalName();
            $origName = str_replace(' ', '_', $origName);
            $storeName = $origName;
            if ($file != '') {
                $storeName = $file . '.' . $extension;
            }
            $img = $this->createImgRec($nID, $storeName, $dbID);
            $img->img_file_orig = $origName;
            $img->img_user_id   = 0;
            if (Auth::user()) {
                $img->img_user_id = Auth::user()->id;
            }
            if ($title != '') {
                $img->img_title = $title;
            }
            $mimetype = $fileObj->getMimeType();
            $size = $fileObj->getSize();
            if (in_array($extension, $exts) && in_array($mimetype, $mimes)) {
                if (!$fileObj->isValid()) {
                    return '<div class="txtDanger p10">Upload Error.'
                        . /* $_FILES["up" . $nID . "File"]["error"]
                        . */ '</div>';
                } elseif ($size > 4000000 || $size === false) {
                    return '<div class="txtDanger p10">File size too large. '
                        . 'Please compress to less than 4MB.</div>';
                } else {
                    //$upFold = '../vendor/' . $GLOBALS["SL"]->sysOpts["cust-package"]
                    //    . '/src/Uploads/';
//if ($GLOBALS["SL"]->debugOn) { $ret .= "saving as filename: " . $upFold . $filename . "<br>"; }
                    //if (file_exists($upFold . $img->img_file_loc)) {
                    //    Storage::delete($upFold . $img->img_file_loc);
                    //}
                    //$fileObj->move($upFold, $img->img_file_loc);
                    $fold = '../storage/app/up/' . $GLOBALS["SL"]->getPckgProj();
                    if ($path != '') {
                        $fold = $path;
                    }
                    $fullFile = $fold . '/' . $img->img_file_loc;
                    if (file_exists($fullFile)) {
                        unlink($fullFile);
                        //Storage::delete($fold . '/' . $img->img_file_loc);
                    }
                    $fileObj->move($fold, $img->img_file_loc);
                    //copy(
                    //    $upFold . $img->img_file_loc,
                    //    $fold . $img->img_file_loc
                    //);
                    $img->img_full_filename = $fullFile;
//echo 'fileObj->move(' . $fold . '<br />' . $img->img_file_loc . '<br />' . $img->img_full_filename . '<br /><br />';
                }
                $img->save();
            } else {
                return '<div class="txtDanger p10">Invalid file. '
                    . 'Please check the format and try again.</div>';
            }
            $this->analizeImg($img);
            if (isset($img->img_id) && intVal($img->img_id) > 0) {
                $GLOBALS["SL"]->x["lastUpImgID"] = $img->img_id;
                return view(
                    'vendor.survloop.forms.inc-image-uploaded',
                    [
                        "nID"    => $this->nID,
                        "presel" => $presel,
                        "imgID"  => $img->img_id
                    ]
                )->render();
            }
        }
        return '<i class="fa fa-times" aria-hidden="true"></i> '
            . 'Something went wrong with your upload.';
    }

    private function createImgRec($nID = '', $storeName = '', $dbID = 1)
    {
        $img = SLImages::where('img_node_id', $nID)
            ->where('img_database_id', $dbID)
            ->where('img_file_loc', $storeName)
            ->first();
        if (!$img || !isset($img->img_database_id)) {
            $img = new SLImages;
            $img->img_database_id = $dbID;
            $img->img_node_id     = $nID;
            $img->img_file_loc    = $storeName;
            //$img->save();
        }
        return $img;
    }

    public function allowedExts()
    {
        return [
            "gif",
            "jpeg",
            "jpg",
            "png",
            "pdf",
            "ods" // OpenOffice document
        ];
    }

    public function allowedMimes()
    {
        return [
            "image/gif",
            "image/jpeg",
            "image/jpg",
            "image/pjpeg",
            "image/x-png",
            "image/png",
            "application/pdf",
            "application/vnd.oasis.opendocument.spreadsheet"
        ];
    }

}