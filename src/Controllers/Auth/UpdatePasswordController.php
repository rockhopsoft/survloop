<?php
/**
  * UpdatePasswordController customizes the Laravel default.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.9
  */
namespace RockHopSoft\Survloop\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UpdatePasswordController extends Controller
{
    /*
     * Ensure the user is signed in to access this page
     */
    public function __construct() {
        $this->middleware('auth');
    }
    /**
     * Show the form to change the user password.
     */
    public function index(){
        return view('user.change-password');
    }

    /**
     * Update the password for the user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function runUpdate(Request $request)
    {
        $user = User::find(Auth::id());
        $this->validate($request, [
            'old' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);
        if (Auth::attempt([ 'name' => $user->name, 'password' => $request->old ])) {
            $user->fill([ 'password' => bcrypt($request->password) ])->save();
            $request->session()->flash('success', 'Your password has been changed.');
            $request->session()->save();
            return redirect('/my-profile');
        }
        $request->session()->flash('failure', 'Your password has not been changed.');
        $request->session()->save();
        return redirect('/my-profile');
    }
    
}