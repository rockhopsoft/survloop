<?php
/**
  * UserProfile is the mid-level class for user profile functions.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers\Tree;

use Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SLTree;
use App\Models\SLSess;
use SurvLoop\Controllers\Tree\TreeSurvInput;

class UserProfile extends TreeSurvInput
{
    public function afterLogin(Request $request)
    {
        $this->survLoopInit($request, '');
        if ($this->v["user"] && $this->v["user"]->hasRole('administrator|staff|databaser|brancher')) {
            //return redirect()->intended('dashboard');
            return $this->redir('/dashboard');
        } elseif ($this->v["user"] && $this->v["user"]->hasRole('volunteer|partner')) {
            $opt = ($this->v["user"]->hasRole('partner')) ? 41 : 17;
            $trees = SLTree::where('TreeDatabase', 1)
                ->where('TreeOpts', '>', 1)
                ->get();
            if ($trees->isNotEmpty()) {
                foreach ($trees as $tree) {
                    if ($tree->TreeOpts%$opt == 0 && $tree->TreeOpts%7 == 0) {
                        //return redirect()->intended('dash/' . $tree->TreeSlug);
                        return $this->redir('/dash/' . $tree->TreeSlug, true);
                    }
                }
            }
        } else {
            $hasCoreTbl = (isset($GLOBALS["SL"]->coreTbl) && $GLOBALS["SL"]->coreTblAbbr() != '');
            $sessTree = ((session()->has('sessTreeReg')) ? session()->get('sessTreeReg') : $GLOBALS["SL"]->sessTree);
            $sessInfo = null;
            $coreAbbr = (($hasCoreTbl) ? $GLOBALS["SL"]->coreTblAbbr() : '');
            $minute = mktime(date("H"), date("i")-1, date("s"), date('n'), date('j'), date('Y'));
            if ($minute < strtotime($this->v["user"]->created_at)) { // signed up in the past minute
                $firstUser = User::select('id')->get();
                if ($firstUser->isNotEmpty() && sizeof($firstUser) == 1) {
                    $this->v["user"]->assignRole('administrator');
                    $this->logAdd('session-stuff', 'New System Administrator #' . $this->v["user"]->id . ' Registered');
                } elseif ($request->has('newVolunteer') && intVal($request->newVolunteer) == 1) {
                    $this->v["user"]->assignRole('volunteer');
                    $this->logAdd('session-stuff', 'New Volunteer #' . $this->v["user"]->id . ' Registered');
                } else {
                    $this->logAdd('session-stuff', 'New User #' . $this->v["user"]->id . ' Registered');
                }
                if (session()->has('coreID' . $sessTree) && $hasCoreTbl) {
                    eval("\$chkRec = " . $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl)
                        . "::find(" . session()->get('coreID' . $sessTree) . ");");
                    if ($chkRec && isset($chkRec->{ $coreAbbr . 'IPaddy' })) {
                        if ($chkRec->{ $coreAbbr . 'IPaddy' } == $GLOBALS["SL"]->hashIP() 
                            && (!isset($chkRec->{ $GLOBALS["SL"]->coreTblUserFld }) 
                                || intVal($chkRec->{ $GLOBALS["SL"]->coreTblUserFld }) <= 0)) {
                            $chkRec->update([ $GLOBALS["SL"]->coreTblUserFld => $this->v["uID"] ]);
                            $this->logAdd('session-stuff', 'Assigning ' . $GLOBALS["SL"]->coreTbl . '#'
                                . $chkRec->getKey() . ' to U#' . $this->v["uID"] . ' <i>(afterLogin)</i>');
                        }
                    }
                }
                if (session()->has('sessID' . $sessTree)) {
                    $sessInfo = SLSess::find(session()->get('sessID' . $sessTree));
                    if ($sessInfo && isset($sessInfo->SessTree)) {
                        if (!isset($sessInfo->SessUserID) || intVal($sessInfo->SessUserID) <= 0) {
                            $sessInfo->update([ 'SessUserID' => $this->v["uID"] ]);
                            $this->logAdd('session-stuff', 'Assigning Sess#' . $sessInfo->getKey() . ' to U#' 
                                . $this->v["uID"] . ' <i>(afterLogin)</i>');
                        }
                    }
                }
            }
            //$this->loadSessInfo($GLOBALS["SL"]->coreTbl);
            if (!session()->has('coreID' . $sessTree) || $this->coreID <= 0) {
                $this->coreID = $this->findUserCoreID();
                if ($this->coreID > 0) {
                    session()->put('coreID' . $sessTree, $this->coreID);
                    $this->logAdd('session-stuff', 'Putting Cookie ' . $GLOBALS["SL"]->coreTbl . '#'
                        . $this->coreID . ' for U#' . $this->v["uID"] . ' <i>(afterLogin)</i>');
                }
            }
            if ($sessInfo && isset($sessInfo->SessCurrNode) && intVal($sessInfo->SessCurrNode) > 0) {
                $this->loadTree();
                $nodeURL = $this->currNodeURL($this->sessInfo->SessCurrNode);
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
        if ($this->v["uID"] > 0 && isset($GLOBALS["SL"]->coreTbl) && trim($GLOBALS["SL"]->coreTbl) != '') {
            $model = $GLOBALS["SL"]->modelPath($GLOBALS["SL"]->coreTbl);
            if (trim($model) != '') {
                eval("\$incompletes = " . $model . "::where('" . $GLOBALS["SL"]->coreTblUserFld . "', " 
                    . $this->v["uID"] . ")->orderBy('created_at', 'desc')->get();");
                if ($incompletes->isNotEmpty()) {
                    foreach ($incompletes as $i => $row) {
                        if ($this->recordIsIncomplete($GLOBALS["SL"]->coreTbl, $row->getKey(), $row)) {
                            $this->coreIncompletes[] = [$row->getKey(), $row];
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
            if ($this->v["uID"] == $GLOBALS["SL"]->REQ->uID || $this->v["user"]->hasRole('administrator')) {
                $user = User::find($GLOBALS["SL"]->REQ->uID);
                $user->name = $GLOBALS["SL"]->REQ->name;
                $user->email = $GLOBALS["SL"]->REQ->email;
                $user->save();
                $user->loadRoles();
                if ($this->v["user"]->hasRole('administrator')) {
                    if ($GLOBALS["SL"]->REQ->has('roles') && is_array($GLOBALS["SL"]->REQ->roles)
                        && sizeof($GLOBALS["SL"]->REQ->roles) > 0) {
                        foreach ($user->roles as $i => $role) {
                            if (in_array($role->DefID, $GLOBALS["SL"]->REQ->roles)) {
                                if (!$user->hasRole($role->DefSubset)) {
                                    $user->assignRole($role->DefSubset);
                                }
                            } elseif ($user->hasRole($role->DefSubset)) {
                                $user->revokeRole($role->DefSubset);
                            }
                        }
                    } else { // no roles selected, delete all that exist
                        foreach ($user->roles as $i => $role) {
                            if ($user->hasRole($role->DefSubset)) {
                                $user->revokeRole($role->DefSubset);
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
            $this->v["profileUser"] = User::where('name', 'LIKE', urldecode($uname))->first();
            if ($this->v["profileUser"] && isset($this->v["profileUser"]->id)) {
                return true;
            }
        } elseif ($this->v["uID"] > 0 && isset($this->v["user"]->id)) {
            $this->v["profileUser"] = $this->v["user"];
            return true;
        }
        return false;
    }
    
    public function showProfileBasics()
    {
        if (isset($this->v["profileUser"]) && isset($this->v["profileUser"]) && isset($this->v["profileUser"]->id)) {
            $this->v["profileUser"]->loadRoles();
            $this->v["canEdit"] = ($this->v["user"] 
                && ($this->v["user"]->hasRole('administrator') || $this->v["user"]->id == $this->v["profileUser"]->id));
            if ($this->v["uID"] > 0) {
                if ($GLOBALS["SL"]->REQ->has('edit') && $GLOBALS["SL"]->REQ->get('edit') == 'sub') {
                    $this->updateProfile();
                } elseif (session()->has('success') && !$GLOBALS["SL"]->isHomestead()) {
                    $emaSubject = 'Your ' . $GLOBALS["SL"]->sysOpts["site-name"] . ' password has been changed.';
                    $emaContent = '<h3>Password updated</h3><p>Hi ' . $this->v["user"]->name 
                        . ',</p><p>We\'ve changed your ' . $GLOBALS["SL"]->sysOpts["site-name"] 
                        . ' password, as you asked. To view or change your account information, visit <a href="' 
                        . $GLOBALS["SL"]->sysOpts["app-url"] . '/my-profile" target="_blank">your profile</a>.</p>'
                        . '<p>If you did not ask to change your password we are here to help secure your account, '
                        . 'just contact us.</p><p>–Your friends at ' . $GLOBALS["SL"]->sysOpts["site-name"] . '</p>';
                    $this->sendEmail($emaContent, $emaSubject, [[$this->v["user"]->email, '']]);
                }
            }
            return view('vendor.survloop.auth.profile', $this->v)->render();
        }
        return '<br /><br /><br /><center><i>User not found.</i></center>'
            . '<script type="text/javascript"> setTimeout("window.location=\''
            . ((Auth::user() && isset(Auth::user()->name)) ? '/my-profile' : '/login') . '\'", 10000); </script>';
    }
    
}