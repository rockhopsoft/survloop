<?php
/**
  * SurvRegisterController customizes new user registration.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers\Auth;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use App\Http\Controllers\Auth\RegisterController;

use App\Models\User;
use App\Models\SLUsersRoles;
use App\Models\SLUsersActivity;
use App\Models\SLDefinitions;

class SurvRegisterController extends RegisterController
{
    
    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        $survUser = User::find($user->id);
        $log = new SLUsersActivity;
        $log->user_act_user = $survUser->id;
        $adminRoleID = 15; // but let's double-check this system
        $admDef = SLDefinitions::where('def_database', 1)
            ->where('def_set', 'User Roles')
            ->where('def_subset', 'administrator')
            ->first();
        if ($admDef && isset($admDef->def_id)) {
            $adminRoleID = $admDef->def_id;
        }
        $hasAdmins = SLUsersRoles::where('role_user_rid', $adminRoleID) 
            ->get(); // role id of 'administrator'
        if ($hasAdmins->isEmpty()) {
            $survUser->assignRole('administrator');
            $log->user_act_curr_page = 'NEW SYSTEM ADMINISTRATOR!';
        } elseif ($request->has('newVolunteer') 
            && intVal($request->newVolunteer) == 1) {
            $survUser->assignRole('volunteer');
            $log->user_act_curr_page = 'NEW VOLUNTEER!';
        }
        $log->save();
        $domainPath = '';
        $appUrl = SLDefinitions::where('def_database', 1)
            ->where('def_set', 'System Settings')
            ->where('def_subset', 'app-url')
            ->first();
        if ($appUrl && isset($appUrl->def_description)) {
            $domainPath = $appUrl->def_description;
        }
        if ($request->has('previous') && trim($request->get('previous')) != '') {
            session()->put('redirLoginSurvey', time());
            session()->put('previousUrl', trim($request->get('previous')));
            session()->save();
        }
        return redirect($domainPath . '/afterLogin');
    }
    
}
