<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'name' => ['string', 'max:255'], // 'required',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        // Generate unique unsername
        if (!isset($input['name']) || trim($input['name']) == '') {
            $input['name'] = '';
            while (trim($input['name']) == '') {
                $input['name'] = 'User ' . rand(10000, 1000000);
                $chk = User::where('name', 'LIKE', $input['name'])
                    ->first();
                if ($chk && isset($chk->name)) {
                    $input['name'] = '';
                }
            }
        }

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }

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
//echo 'registered( /after-login'; exit;
        return redirect($domainPath . '/dashboard');
    }
}
