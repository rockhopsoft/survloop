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

use Auth;
use Storage;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use App\Models\User;
use App\Models\SLTree;
use App\Models\SLSess;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvInput;

class UserProfile extends TreeSurvInput
{
    public function afterLogin(Request $request)
    {
        $this->survloopInit($request, '');
        if ($this->v["user"] 
            && $this->v["user"]->hasRole('administrator|staff|databaser|brancher')) {
            //return redirect()->intended('dashboard');
            return $this->redir('/dashboard');
        } elseif ($this->v["user"] && $this->v["user"]->hasRole('volunteer|partner')) {
            $opt = ($this->v["user"]->hasRole('partner')) ? 41 : 17;
            $trees = SLTree::where('tree_database', 1)
                ->where('tree_opts', '>', 1)
                ->get();
            if ($trees->isNotEmpty()) {
                foreach ($trees as $tree) {
                    if ($tree->tree_opts%$opt == 0 && $tree->tree_opts%7 == 0) {
                        //return redirect()->intended('dash/' . $tree->tree_slug);
                        return $this->redir('/dash/' . $tree->tree_slug, true);
                    }
                }
            }
        } else {
            $hasCoreTbl = (isset($GLOBALS["SL"]->coreTbl) && $GLOBALS["SL"]->coreTblAbbr() != '');
            $sessTree = $GLOBALS["SL"]->sessTree;
            if (session()->has('sessTreeReg')) {
                $sessTree = session()->get('sessTreeReg');
            }
            $sessInfo = null;
            $coreAbbr = (($hasCoreTbl) ? $GLOBALS["SL"]->coreTblAbbr() : '');
            $minute = mktime(date("H"), date("i")-1, date("s"), date('n'), date('j'), date('Y'));
            if ($this->v["user"] 
                && isset($this->v["user"]->created_at) 
                && $minute < strtotime($this->v["user"]->created_at)) {
                // signed up in the past minute
                $firstUser = User::select('id')->get();
                if ($firstUser->isNotEmpty() && sizeof($firstUser) == 1) {
                    $this->v["user"]->assignRole('administrator');
                    $this->logAdd(
                        'session-stuff', 
                        'New System Administrator #' . $this->v["user"]->id . ' Registered'
                    );
                } elseif ($request->has('newVolunteer') 
                    && intVal($request->newVolunteer) == 1) {
                    $this->v["user"]->assignRole('volunteer');
                    $this->logAdd(
                        'session-stuff', 
                        'New Volunteer #' . $this->v["user"]->id . ' Registered'
                    );
                } else {
                    $this->logAdd(
                        'session-stuff', 
                        'New User #' . $this->v["user"]->id . ' Registered'
                    );
                }
                if (session()->has('coreID' . $sessTree) && $hasCoreTbl) {
                    eval("\$chkRec = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl)
                        . "::find(" . session()->get('coreID' . $sessTree) . ");");
                    if ($chkRec && isset($chkRec->{ $coreAbbr . 'ip_addy' })) {
                        $badUserField = (!isset($chkRec->{ $GLOBALS["SL"]->coreTblUserFld }) 
                            || intVal($chkRec->{ $GLOBALS["SL"]->coreTblUserFld }) <= 0);
                        if ($chkRec->{ $coreAbbr . 'ip_addy' } == $GLOBALS["SL"]->hashIP() && $badUserField) {
                            $chkRec->update([ $GLOBALS["SL"]->coreTblUserFld => $this->v["uID"] ]);
                            $log = 'Assigning ' . $GLOBALS["SL"]->coreTbl . '#' . $chkRec->getKey() 
                                . ' to U#' . $this->v["uID"] . ' <i>(afterLogin)</i>';
                            $this->logAdd('session-stuff', $log);
                        }
                    }
                }
                if (session()->has('sessID' . $sessTree)) {
                    $sessInfo = SLSess::find(session()->get('sessID' . $sessTree));
                    if ($sessInfo && isset($sessInfo->sess_tree)) {
                        if (!isset($sessInfo->sess_user_id) || intVal($sessInfo->sess_user_id) <= 0) {
                            $sessInfo->update([ 'sess_user_id' => $this->v["uID"] ]);
                            $log = 'Assigning Sess#' . $sessInfo->getKey() . ' to U#' 
                                . $this->v["uID"] . ' <i>(afterLogin)</i>';
                            $this->logAdd('session-stuff', $log);
                        }
                    }
                }
            }
            //$this->loadSessInfo($GLOBALS["SL"]->coreTbl);
            if (!session()->has('coreID' . $sessTree) || $this->coreID <= 0) {
                $this->coreID = $GLOBALS["SL"]->coreID = $this->findUserCoreID();
                if ($this->coreID > 0) {
                    session()->put('coreID' . $sessTree, $this->coreID);
                    session()->put('coreID' . $sessTree . 'old' . $this->coreID, time());
                    session()->save();
                    $log = 'Putting Cookie ' . $GLOBALS["SL"]->coreTbl 
                        . '#' . $this->coreID . ' for U#' 
                        . $this->v["uID"] . ' <i>(afterLogin)</i>';
                    $this->logAdd('session-stuff', $log);
                }
            }
            if ($sessInfo 
                && isset($sessInfo->sess_curr_node) 
                && intVal($sessInfo->sess_curr_node) > 0) {
                $this->loadTree();
                $nodeURL = $this->currNodeURL($this->sessInfo->sess_curr_node);
                if (trim($nodeURL) != '') {
                    return $this->redir($nodeURL, true);
                }
            }
            //return redirect()->intended('my-profile');
            return $this->redir('/my-profile', true);
        }
        //return redirect()->intended('home');
        return $this->redir('/', true);
    }

    public function findUserCoreID()
    {
        $this->coreIncompletes = [];
        if (isset($this->v["uID"]) 
            && $this->v["uID"] > 0 
            && isset($GLOBALS["SL"]->coreTbl) 
            && trim($GLOBALS["SL"]->coreTbl) != ''
            && trim($GLOBALS["SL"]->coreTblUserFld) != '') {
            $model = $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl);
            if (trim($model) != '') {
                eval("\$incompletes = " . $model . "::where('" 
                    . $GLOBALS["SL"]->coreTblUserFld . "', " . $this->v["uID"] 
                    . ")->orderBy('created_at', 'desc')->get();");
                if ($incompletes->isNotEmpty()) {
                    foreach ($incompletes as $i => $row) {
                        if ($this->recordIsIncomplete(
                            $GLOBALS["SL"]->coreTbl, 
                            $row->getKey(), 
                            $row)) {
                            $this->coreIncompletes[] = [
                                $row->getKey(), 
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
            if ($this->v["uID"] == $GLOBALS["SL"]->REQ->uID 
                || $this->v["user"]->hasRole('administrator')) {
                $user = User::find($GLOBALS["SL"]->REQ->uID);
                $user->name = $GLOBALS["SL"]->REQ->name;
                $user->email = $GLOBALS["SL"]->REQ->email;
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
    
    public function showProfileBasics()
    {
        if (isset($this->v["profileUser"]) 
            && isset($this->v["profileUser"]) 
            && isset($this->v["profileUser"]->id)) {
            $this->v["profileUser"]->loadRoles();
            $uID = $this->v["profileUser"]->id;
            $this->v["canEdit"] = false;
            if (isset($this->v["user"]) 
                && $this->v["user"] 
                && ($this->v["user"]->hasRole('administrator|staff')
                    || $this->v["user"]->id == $uID)) {
                $this->v["canEdit"] = true;
            }
            if (isset($this->v["uID"]) 
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
            }
            $this->v["userActivity"] = $this->v["userSess"] = '';
            if ($this->v["user"] 
                && $this->v["user"]->hasRole('administrator|staff')) {
                $this->v["userSess"] = $this->logPreviewUser('session-stuff', $uID);
                $this->v["userActivity"] = $this->activityPreviewUser($uID);
            }
            $this->v["picInstruct"] = $this->profilePhotoUploadInstruct();
            return view('vendor.survloop.auth.profile', $this->v)->render();
        }
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