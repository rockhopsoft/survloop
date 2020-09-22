<?php
/**
  * SurvRegisterController customizes new user registration.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since 0.0
  */
namespace Survloop\Controllers\Auth;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;

use App\Models\User;
use App\Models\SLUsersRoles;
use App\Models\SLUsersActivity;
use App\Models\SLDefinitions;

//use App\Http\Controllers\Auth\RegisterController;
//class SurvRegisterController extends RegisterController
class SurvRegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
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
        return redirect($domainPath . '/afterLogin');
    }
    
}
