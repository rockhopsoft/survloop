<?php

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
        if ($request->has('newVolunteer') && intVal($request->newVolunteer) == 1) {
            $log = new SLUsersActivity;
            $log->UserActUser = $survUser->id;
            $adminRoleID = 15;
            $admDef = SLDefinitions::where('DefDatabase', 1)
                ->where('DefSet', 'User Roles')
                ->where('DefSubset', 'administrator')
                ->first();
            if ($admDef && isset($admDef->DefID)) $adminRoleID = $admDef->DefID;
            $hasAdmins = SLUsersRoles::where('RoleUserRID', $adminRoleID) // role id of 'administrator'
                ->get();
            if ($hasAdmins->isEmpty()) {
                $survUser->assignRole('administrator');
                $log->UserActCurrPage = 'NEW SYSTEM ADMINISTRATOR!';
            } else {
                $survUser->assignRole('volunteer');
                $log->UserActCurrPage = 'NEW VOLUNTEER!';
            }
            $log->save();
        }
        $domainPath = '';
        $appUrl = SLDefinitions::where('DefDatabase', 1)
            ->where('DefSet', 'System Settings')
            ->where('DefSubset', 'app-url')
            ->first();
        if ($appUrl && isset($appUrl->DefDescription)) $domainPath = $appUrl->DefDescription;
        return redirect($domainPath . '/afterLogin');
    }
    
}
