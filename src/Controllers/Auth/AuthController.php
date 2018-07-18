<?php

namespace SurvLoop\Controllers\Auth;

use Auth;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

use SurvLoop\Controllers\CoreGlobals;
use SurvLoop\Controllers\SurvLoopController;

use App\Models\User;
use App\Models\SLUsersActivity;
use App\Models\SLDefinitions;

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
    
    protected $loginPath           = '/login';
    protected $redirectPath        = '/afterLogin';
    protected $redirectAfterLogout = '/login';
    
    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->loadDomain();
        $this->loginPath           = $this->domainPath . '/login';
        $this->redirectPath        = $this->domainPath . '/afterLogin';
        $this->redirectAfterLogout = $this->domainPath . '/login';
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
            'name' => 'required|max:50|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:8',
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
    
    public function postLogin(Request $request)
    {
        $pass = false;
        if (Auth::attempt(['name' => $request->email, 'password' => $request->password])) {
            $pass = true;
        } elseif (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $pass = true;
        }
        if ($pass) {
            $sl = new SurvLoopController;
            $uID = ((Auth::user() && isset(Auth::user()->id)) ? Auth::user()->id : 0);
            $sl->logAdd('session-stuff', 'User #' . $uID . ' Logged In');
            return redirect($this->redirectPath);
        }
        return view('auth.login', [
            "errorMsg" => 'That combination of password with that username or email did not work.' 
        ]);
        //return $this->getLogin($request);
        //return redirect($this->loginPath . '?error=1');
    }
    
    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        return $this->domainPath . '/afterLogin';
    }
    
    public function getLogout()
    {
        $sl = new SurvLoopController;
        $uID = ((Auth::user() && isset(Auth::user()->id)) ? Auth::user()->id : 0);
        $sl->logAdd('session-stuff', 'User #' . $uID . ' Logged Out');
        Auth::logout();
        session()->flush();
        return redirect($this->domainPath . '/');
    }
    
    protected function loadDomain()
    {
        $appUrl = SLDefinitions::select('DefDescription')
            ->where('DefDatabase', 1)
            ->where('DefSet', 'System Settings')
            ->where('DefSubset', 'app-url')
            ->first();
        if ($appUrl && isset($appUrl->DefDescription)) $this->domainPath = $appUrl->DefDescription;
        return $this->domainPath;
    }
    
    public function printPassReset(Request $request)
    {
        return view('vendor.survloop.auth.passwords.email', []);
    }

    
}