<?php

namespace SurvLoop\Controllers\Auth;

use Auth;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;

// ??..
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

use SurvLoop\Controllers\DatabaseLookups;

use SurvLoop\Models\User;
use SurvLoop\Models\SLUsersActivity;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use ThrottlesLogins, DispatchesJobs, ValidatesRequests;
    
    protected $redirectPath 		= '/afterLogin';
    protected $loginPath 			= '/login';
    protected $redirectAfterLogout 	= '/login';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
        /* if ($validator->fails()) {
        	echo $validator->messages()->toJson(); exit;
        } */
        return $validator;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
    	$user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
        return $user;
    }
    
    public function postRegister(Request $request)
    {
        if (session()->has('sessID') && session()->get('sessID') > 0)
        {
        	
        }
		$hasUsers = User::select('id')->get();	
    	//echo 'AuthController:create volunteer <pre>'; print_r($data); echo '</pre><pre>'; print_r($user); echo '</pre>'; exit;
    	if ($request->has('newVolunteer') && intVal($request->newVolunteer) == 1)
    	{
			$log = new SLUsersActivity;
			$log->UserActUser = $user->id;
    		if (!$hasUsers || sizeof($hasUsers) == 0)
    		{
    			$user->assignRole('administrator');
    			$log->UserActCurrPage = 'NEW SYSTEM ADMINISTRATOR!';
    		}
    		else
    		{
    			$user->assignRole('volunteer');
    			$log->UserActCurrPage = 'NEW VOLUNTEER!';
    		}
			$log->save();
    	}
    	return redirect('/afterLogin');
    }
   
    
    /**
	 * Get the post register / login redirect path.
	 *
	 * @return string
	 */
	public function redirectPath()
	{
		/*
		// Logic that determines where to send the user
		$user = Auth::user();
		if ($user->hasRole('administrator|staff|databaser|brancher')) {
			return '/dashboard';
		} elseif ($user->hasRole('volunteer')) {
			return '/volunteer';
		}
		*/
		return '/afterLogin';
	}
	
}
