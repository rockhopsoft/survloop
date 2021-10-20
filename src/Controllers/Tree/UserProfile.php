<?php
/**
  * UserProfile is the mid-level class for user profile functions.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.1.2
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use DB;
use Auth;
use Storage;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use App\Models\User;
use App\Models\SLTree;
use App\Models\SLSess;
use RockHopSoft\Survloop\Controllers\Admin\SurvLogAnalysis;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvInput;

class UserProfile extends TreeSurvInput
{

    public function findUserCoreID()
    {
        $this->coreIncompletes = [];
        if (isset($this->v["uID"])
            && $this->v["uID"] > 0
            && isset($GLOBALS["SL"]->coreTbl)
            && trim($GLOBALS["SL"]->coreTbl) != ''
            && trim($GLOBALS["SL"]->coreTblUserFld) != '') {
            $model = $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl);
            $idFld = $GLOBALS["SL"]->tblAbbr[$GLOBALS["SL"]->coreTbl] . 'id';
            if (trim($model) != '') {
                $fullTbl = $GLOBALS["SL"]->dbRow->db_prefix . $GLOBALS["SL"]->coreTbl;
                $incompletes = DB::table($fullTbl)
                    ->where($GLOBALS["SL"]->coreTblUserFld, $this->v["uID"])
                    ->orderBy('created_at', 'desc')
                    ->get();
                //eval("\$incompletes = " . $model . "::where('"
                //    . $GLOBALS["SL"]->coreTblUserFld . "', " . $this->v["uID"]
                //    . ")->orderBy('created_at', 'desc')->get();");
                if ($incompletes->isNotEmpty()) {
                    foreach ($incompletes as $i => $row) {
                        if ($this->recordIsIncomplete(
                            $GLOBALS["SL"]->coreTbl,
                            $row->{ $idFld },
                            $row)) {
                            $this->coreIncompletes[] = [
                                $row->{ $idFld },
                                $row
                            ];
                        }
                    }
                    if (sizeof($this->coreIncompletes) > 0) {
                        return $this->coreIncompletes[0][0];
                    }
                }
            }
        }
        return -3;
    }

    /**
     * Update the user's profile.
     *
     * @param  Request  $request
     * @return Response
     */
    public function updateProfile()
    {
        if ($this->v["uID"] > 0) {
            // $GLOBALS["SL"]->user() returns an instance of the authenticated user...
            if ($this->v["uID"] == intVal($GLOBALS["SL"]->REQ->uID)
                || $this->v["user"]->hasRole('administrator|staff')) {
                $user = User::find(intVal($GLOBALS["SL"]->REQ->uID));
                $name = strip_tags($GLOBALS["SL"]->REQ->name);
                $chk = User::where('id', 'NOT LIKE', $this->v["uID"])
                    ->where('name', $name)
                    ->first();
                if (!$chk && !isset($chk->name)) {
                    $user->name = $name;
                }
                $email = strip_tags($GLOBALS["SL"]->REQ->email);
                $chk = User::where('id', 'NOT LIKE', $this->v["uID"])
                    ->where('email', $email)
                    ->first();
                if (!$chk && !isset($chk->email)) {
                    $user->email = $email;
                }
                $user->save();
                $user->loadRoles();
                if ($this->v["user"]->hasRole('administrator')) {
                    if ($GLOBALS["SL"]->REQ->has('roles')
                        && is_array($GLOBALS["SL"]->REQ->roles)
                        && sizeof($GLOBALS["SL"]->REQ->roles) > 0) {
                        foreach ($user->roles as $i => $role) {
                            if (in_array($role->def_id, $GLOBALS["SL"]->REQ->roles)) {
                                if (!$user->hasRole($role->def_subset)) {
                                    $user->assignRole($role->def_subset);
                                }
                            } elseif ($user->hasRole($role->def_subset)) {
                                $user->revokeRole($role->def_subset);
                            }
                        }
                    } else { // no roles selected, delete all that exist
                        foreach ($user->roles as $i => $role) {
                            if ($user->hasRole($role->def_subset)) {
                                $user->revokeRole($role->def_subset);
                            }
                        }
                    }
                }
                $this->redir('/profile/' . urlencode($user->name), true);
                return true;
            }
        }
        return false;
    }

    public function setCurrUserProfile($uname = '')
    {
        if (trim($uname) != '') {
            $this->v["profileUser"] = User::where('name', 'LIKE', urldecode($uname))
                ->first();
            if ($this->v["profileUser"] && isset($this->v["profileUser"]->id)) {
                return true;
            }
        } elseif (isset($this->v["uID"])
            && $this->v["uID"] > 0
            && isset($this->v["user"]->id)) {
            $this->v["profileUser"] = $this->v["user"];
            return true;
        }
        return false;
    }

    private function setCurrUserProfileCanEdit()
    {
        $this->v["canEdit"] = false;
        if (isset($this->v["user"])
            && $this->v["user"]
            && ($this->v["user"]->hasRole('administrator|staff')
                || $this->v["user"]->id == $this->v["profileUser"]->id)) {
            $this->v["canEdit"] = true;
        }
        return true;
    }

    protected function showProfileBasics($isEditPage = false)
    {
        if (isset($this->v["profileUser"])
            && isset($this->v["profileUser"])
            && isset($this->v["profileUser"]->id)) {
            $this->v["profileUser"]->loadRoles();
            $this->setCurrUserProfileCanEdit();
            $this->v["isEditPage"] = $isEditPage;
            return view('vendor.survloop.auth.profile', $this->v)->render();
        }
        return $this->redirUserNotFound();
    }

    public function editProfileBasics()
    {
        $ret = $this->showProfileBasics(true);
        if (isset($this->v["profileUser"])
            && isset($this->v["profileUser"])
            && isset($this->v["profileUser"]->id)
            && isset($this->v["uID"])
            && $this->v["uID"] > 0
            && $this->v["canEdit"]) {
            if ($GLOBALS["SL"]->REQ->has('edit')
                && $GLOBALS["SL"]->REQ->get('edit') == 'sub') {
                $this->updateProfile();
            } elseif (session()->has('success')) {
                $this->profileResetPass();
            }
            if ($GLOBALS["SL"]->REQ->has('upload')
                && $GLOBALS["SL"]->REQ->get('upload') == 'photo') {
                $this->profilePhotoUpload();
            }
            if ($GLOBALS["SL"]->REQ->has('delProfPic')
                && intVal($GLOBALS["SL"]->REQ->delProfPic) == 1) {
                $this->profilePhotoDelete();
            }
            $this->v["picInstruct"] = $this->profilePhotoUploadInstruct();
            $this->v["content"] = '<div id="ajaxWrap" class="w100"><center>'
                . '<div id="skinnySurv" class="treeWrapForm">' . $ret
                . view('vendor.survloop.auth.profile-edit', $this->v)
                    ->render()
                . '</div></center></div>';
            return $this->indexResponse();
        }
        return $this->redirUserNotFound();
    }

    public function printProfileStats()
    {
        if (isset($this->v["profileUser"])
            && isset($this->v["profileUser"])
            && isset($this->v["profileUser"]->id)
            && $this->isStaffOrAdmin()) {
            $uID = $this->v["profileUser"]->id;
            $this->v["logs"] = new SurvLogAnalysis;
            $this->v["allSessionLogs"]
                = $this->v["allSessionsGrouped"]
                = [];
            $this->v["userSess"]
                = $this->v["logs"]->logPreviewUser('session-stuff', $uID);
            $this->v["userActivity"]
                = $this->v["logs"]->activityPreviewUser($uID);
            $this->v["customStats"]
                = $this->printProfileStatsCustom();
            $this->v["content"] = '<div id="ajaxWrap" class="w100">'
                . '<div id="wideSurv" class="container-fluid">'
                . view('vendor.survloop.auth.profile-stats', $this->v)
                    ->render()
                . '</div></div>';
            return $this->indexResponse();
        }
        return $this->redirUserNotFound();
    }

    protected function printProfileStatsCustom()
    {
        return '';
    }

    protected function redirUserNotFound()
    {
        return '<br /><br /><br /><center><i>User not found.</i></center>'
            . '<script type="text/javascript"> '
            . 'setTimeout("window.location=\'/login\'", 1); '
            . '</script>';
    }

    protected function profilePhotoUploadInstruct()
    {
        return 'Please upload an appropriate photo of yourself.'
            . 'This will be <nobr>visible to the public.</nobr>';
    }

    protected function profilePhotoUpload()
    {
        $file = 'profilePhotoUp';
        if (!$GLOBALS["SL"]->REQ->hasFile($file)) {
            return '<div class="txtDanger">No File Found.</div>';
        }
        $ret = '';
        $fileOrig = $GLOBALS["SL"]->REQ->file($file)->getClientOriginalName();
        $extension = $GLOBALS["SL"]->REQ->file($file)->getClientOriginalExtension();
        $mimetype = $GLOBALS["SL"]->REQ->file($file)->getMimeType();
        $size = $GLOBALS["SL"]->REQ->file($file)->getSize();
        $exts = ["gif", "jpeg", "jpg", "png", "pdf"];
        $mimes = [
            "image/gif", "image/jpeg", "image/jpg",
            "image/pjpeg", "image/x-png", "image/png"
        ];
        if (in_array(strtolower($extension), $exts)
            && in_array(strtolower($mimetype), $mimes)) {
            if (!$GLOBALS["SL"]->REQ->file($file)->isValid()) {
                $ret .= '<div class="txtDanger">Upload Error.'
                    . /* $_FILES["up" . $nID . "File"]["error"] . */ '</div>';
            } else {
                $upFold = '../storage/app/up/avatar/';
                $filename = $this->v["profileUser"]->id . '.jpg';
                if (file_exists($upFold . $filename)) {
                    unlink($upFold . $filename);
                }
                $GLOBALS["SL"]->REQ->file($file)->move($upFold, $filename);
                Image::configure(array('driver' => 'imagick'));
                $image = Image::make($upFold . $filename);
                $width = $height = 500;
                $image->width() > $image->height() ? $width=null : $height=null;
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $image->crop(500, 500);
                $crop = str_replace('.jpg', '-.jpg', $filename);
                if (file_exists($upFold . $crop)) {
                    unlink($upFold . $crop);
                }
                $image->save($upFold . $crop, 60);
            }
        } else {
            $ret .= '<div class="txtDanger">Invalid file. '
                . 'Please check the format and try again.</div>';
        }
        return $ret;
    }

    private function profilePhotoDelete()
    {
        $upFold = '../storage/app/up/avatar/';
        $filename = $this->v["profileUser"]->id . '.jpg';
        if (file_exists($upFold . $filename)) {
            unlink($upFold . $filename);
        }
        $crop = str_replace('.jpg', '-.jpg', $filename);
        if (file_exists($upFold . $crop)) {
            unlink($upFold . $crop);
        }
        return true;
    }

    protected function profileResetPass()
    {
        if ($GLOBALS["SL"]->isHomestead()) {
            return false;
        }
        $emaSubject = 'Your ' . $GLOBALS["SL"]->sysOpts["site-name"]
            . ' password has been changed.';
        $emaContent = '<h3>Password updated</h3><p>Hi '
            . $this->v["user"]->name . ',</p><p>We\'ve changed your '
            . $GLOBALS["SL"]->sysOpts["site-name"]
            . ' password, as you asked. To view or change your '
            . 'account information, visit <a href="'
            . $GLOBALS["SL"]->sysOpts["app-url"]
            . '/my-profile" target="_blank">your profile</a>.</p>'
            . '<p>If you did not ask to change your password '
            . 'we are here to help secure your account, '
            . 'just contact us.</p><p>–Your friends at '
            . $GLOBALS["SL"]->sysOpts["site-name"] . '</p>';
        $emaTo = [
            [
                ((isset($this->v["user"])) ? $this->v["user"]->email : ''),
                ''
            ]
        ];
        $this->sendEmail($emaContent, $emaSubject, $emaTo);
        return true;
    }


}