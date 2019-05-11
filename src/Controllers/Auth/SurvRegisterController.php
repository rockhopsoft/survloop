<?php

namespace SurvLoop\Controllers\Auth;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use App\Http\Controllers\Auth\RegisterController;

use SurvLoop\Models\User;
use SurvLoop\Models\SLUsersRoles;
use SurvLoop\Models\SLUsersActivity;
use SurvLoop\Models\SLDefinitions;

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
        $log->UserActUser = $survUser->id;
        $adminRoleID = 15; // but let's double-check this system
        $admDef = SLDefinitions::where('DefDatabase', 1)
            ->where('DefSet', 'User Roles')
            ->where('DefSubset', 'administrator')
            ->first();
        if ($admDef && isset($admDef->DefID)) {
            $adminRoleID = $admDef->DefID;
        }
        $hasAdmins = SLUsersRoles::where('RoleUserRID', $adminRoleID) // role id of 'administrator'
            ->get();
        if ($hasAdmins->isEmpty()) {
            $survUser->assignRole('administrator');
            $log->UserActCurrPage = 'NEW SYSTEM ADMINISTRATOR!';
        } elseif ($request->has('newVolunteer') && intVal($request->newVolunteer) == 1) {
            $survUser->assignRole('volunteer');
            $log->UserActCurrPage = 'NEW VOLUNTEER!';
        }
        $log->save();
        $domainPath = '';
        $appUrl = SLDefinitions::where('DefDatabase', 1)
            ->where('DefSet', 'System Settings')
            ->where('DefSubset', 'app-url')
            ->first();
        if ($appUrl && isset($appUrl->DefDescription)) {
            $domainPath = $appUrl->DefDescription;
        }
        if ($request->has('previous') && trim($request->get('previous')) != '') {
            session()->put('redirLoginSurvey', time());
            session()->put('previousUrl', trim($request->get('previous')));
        }
        return redirect($domainPath . '/afterLogin');
    }
    
}
