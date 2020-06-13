<?php namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\Notifiable;
use App\Models\SLDefinitions;
use App\Models\SLUsersRoles;
use SurvLoop\Controllers\DatabaseLookups;
use SurvLoop\Controllers\Notifications\MailResetPasswordNotification;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, Notifiable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
    
    /**
    * Send the password reset notification.
    *
    * @param  string  $token
    * @return void
    */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new MailResetPasswordNotification($token));
    }
    
    
    public function urlSlug($baseurl = '/user/')
    {
        return $baseurl . urlencode($this->name);
    }
    
    
    public function printUsername($link = true, $baseurl = '/user/')
    {
        if ($link) {
            return '<a href="' . $this->urlSlug($baseurl) . '">'
                . $this->name . '</a>';
        }
        return $this->name;
    }
    
    public function printCasualUsername($link = true, $baseurl = '/user/', $preFix = '')
    {
        $uName = $this->name;
        if (strpos($uName, ' ') !== false) {
            $uName = substr($uName, 0, strpos($uName, ' '));
        }
        if ($link) {
            return '<a href="' . $this->urlSlug($baseurl) . '">'
                . $preFix . $uName . '</a>';
        }
        return $uName;
    }
    
    
    public function profileImgSrc()
    {
        $file = '../storage/app/up/avatar/' . $this->id . '.jpg';
        if (file_exists($file)) {
            return '/img/user/' . urlencode($this->name) . '.jpg';
        }
        if (isset($GLOBALS["SL"]->sysOpts["has-avatars"])
            && trim($GLOBALS["SL"]->sysOpts["has-avatars"]) != '') {
            return trim($GLOBALS["SL"]->sysOpts["has-avatars"]);
        }
        return '';
    }
    
    
    public function profileImg()
    {
        return '<a href="/user/' . urlencode($this->name) 
            . '"><img class="tmbRound profilePic" src="'
            . $this->profileImgSrc() . '" border=0 alt="Profile Picture for '
            . $this->name . '"></a>';
    }
    
    
    
    /**
     * The information of all possible SurvLoop Roles.
     *
     * @var array
     */
    public $roles = [];
    
    /**
     * The names of SurvLoop Roles held by this user.
     *
     * @var array
     */
    protected $SLRoles = [];
    
    public function loadRoles()
    {
        if (empty($this->roles)) {
            $this->roles = SLDefinitions::select('def_id', 'def_subset', 'def_value')
                ->where('def_database', 1)
                ->where('def_set', 'User Roles')
                ->orderBy('def_order')
                ->get();
            $chk = DB::table('sl_users_roles')
                ->join('sl_definitions', 'sl_users_roles.role_user_rid', '=', 'sl_definitions.def_id')
                ->where('sl_users_roles.role_user_uid', $this->id)
                ->where('sl_definitions.def_set', 'User Roles')
                ->select('sl_definitions.def_subset')
                ->get();
            if ($chk->isNotEmpty()) {
                foreach ($chk as $role) {
                    $this->SLRoles[] = $role->def_subset;
                }
            } else {
                $this->SLRoles[] = 'NO-ROLES';
            }
        }
        return true;
    }
    
    public function hasRole($role)
    {
        $this->loadRoles();
        if (strpos($role, '|') === false) {
            return in_array($role, $this->SLRoles);
        }
        $ret = false;
        $roles = explode('|', $role);
        foreach ($roles as $r) {
            if (in_array($r, $this->SLRoles)) {
                $ret = true;
            }
        }
        return $ret;
    }
    
    public function assignRole($role)
    {
        $this->loadRoles();
        $roleDef = SLDefinitions::select('def_id')
            ->where('def_database', 1)
            ->where('def_set', 'User Roles')
            ->where('def_subset', $role)
            ->orderBy('def_order')
            ->first();
        $chk = SLUsersRoles::select('role_user_id')
            ->where('role_user_rid', '=', $roleDef->def_id)
            ->where('role_user_uid', '=', $this->id)
            ->get();
        if ($chk->isEmpty()) {
            $newRole = new SLUsersRoles;
            $newRole->role_user_rid = $roleDef->def_id;
            $newRole->role_user_uid = $this->id;
            $newRole->save();
            $this->SLRoles[] = $role;
        }
        return true;
    }
    
    public function revokeRole($role)
    {
        $this->loadRoles();
        $roleDef = SLDefinitions::select('def_id')
            ->where('def_database', 1)
            ->where('def_set', 'User Roles')
            ->where('def_subset', $role)
            ->orderBy('def_order')
            ->first();
        $chk = SLUsersRoles::where('role_user_rid', '=', $roleDef->def_id)
            ->where('role_user_uid', '=', $this->id)
            ->delete();
        if (sizeof($this->SLRoles) > 0) {
            $roles = $this->SLRoles;
            $this->SLRoles = [];
            foreach ($roles as $r) {
                if ($r != $role) {
                    $this->SLRoles[] = $r;
                }
            }
        }
        return true;
    }
    
    public function highestPermission()
    {
        $this->loadRoles();
        foreach ($this->roles as $role) {
            if ($this->hasRole($role->def_subset)) {
                return $role->def_subset;
            }
        }
        return '';
    }
    
    public function listRoles()
    {
        $this->loadRoles();
        $retVal = '';
        foreach ($this->roles as $role) { 
            if ($this->hasRole($role->def_subset)) {
                $retVal .= ', ' . ucfirst($role->def_subset);
            }
        }
        if ($retVal != '') {
            $retVal = substr($retVal, 2);
        }
        return $retVal;
    }
    
    public function hasVerifiedEmail()
    {
        $chk = SLUsersRoles::select('role_user_id')
            ->where('role_user_rid', '=', -37)
            ->where('role_user_uid', '=', $this->id)
            ->first();
        return ($chk && isset($chk->role_user_id));
    }
    
}