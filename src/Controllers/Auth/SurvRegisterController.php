<?php

namespace SurvLoop\Controllers\Auth;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Auth\Events\Registered;

use App\Models\User;
use App\Models\SLUsersRoles;
use App\Models\SLUsersActivity;

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
        $hasAdmins = SLUsersRoles::where('RoleUserRID', 15) // role id of 'administrator'
            ->get();
        if ($request->has('newVolunteer') && intVal($request->newVolunteer) == 1)
        {
            $log = new SLUsersActivity;
            $log->UserActUser = $survUser->id;
            if (!$hasAdmins || sizeof($hasAdmins) == 0)
            {
                $survUser->assignRole('administrator');
                $log->UserActCurrPage = 'NEW SYSTEM ADMINISTRATOR!';
            }
            else
            {
                $survUser->assignRole('volunteer');
                $log->UserActCurrPage = 'NEW VOLUNTEER!';
            }
            $log->save();
        }
        return redirect('/afterLogin');
    }
    
}
