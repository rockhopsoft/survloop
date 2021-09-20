<?php
/**
  * AuthController customizes some of the standard Laravel behavior.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since 0.0
  */
namespace RockHopSoft\Survloop\Controllers\Auth;

use Auth;
use Validator;
use App\Models\User;
use App\Models\SLSess;
use App\Models\SLNode;
use App\Models\SLTree;
use App\Models\SLDefintions;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Bus\DispatchesJobs;
use RockHopSoft\Survloop\Controllers\Survloop;
use RockHopSoft\Survloop\Controllers\SurvloopController;
use RockHopSoft\Survloop\Controllers\Auth\AuthSurvLoader;
use RockHopSoft\Survloop\Controllers\Globals\Globals;

class AuthController extends AuthSurvLoader
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

    //use ThrottlesLogins, DispatchesJobs, ValidatesRequests;


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    /*
    protected function validator(array $data)
    {
        $nameOpts = 'max:50|unique:users';
        if ($GLOBALS["SL"]->sysOpts["has-usernames"] == 1
            && $GLOBALS["SL"]->sysOpts["user-name-req"] == 1) {
            $nameOpts = 'required|' . $nameOpts;
        }
        $validator = Validator::make($data, [
            'name' => $nameOpts,
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:8',
            ]);
        // if ($validator->fails()) {
        //    echo $validator->messages()->toJson(); exit;
        //}
        return $validator;
    }
    */

    /**
     * Overrides the default Fortify::registerView function.
     *
     * @return mixed
     */
    public function printRegisterView()
    {
        return view(
            'vendor.survloop.auth.register',
            $this->surv->custLoop->v
        );
    }

    /**
     * Overrides the default Fortify::loginView function.
     *
     * @return mixed
     */
    public function printLoginView()
    {
        $redir = $this->getLoginViewRedir();
        if (Auth::user() && isset(Auth::user()->id)) {
            //return redirect('/after-login'); // '/my-profile');
            return redirect($this->redirectPath());
        }
        $this->surv->custLoop->v["content"] = view(
            'vendor.survloop.auth.login',
            $this->surv->custLoop->v
        )->render();
        if ($GLOBALS["SL"]->REQ->has('ajax')) {
            return $this->surv->custLoop->v["content"];
        }
        $this->surv->custLoop->v["content"] = '<div id="ajaxWrap">'
            . $this->surv->custLoop->v["content"] . '</div>';
        header(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, post-check=0, pre-check=0'
        );
        return view(
            'vendor.survloop.master',
            $this->surv->custLoop->v
        );
    }

    /**
     * Checks for best redirect link to provide with login page.
     *
     * @return string
     */
    private function getLoginViewRedir()
    {
        $redir = '/my-profile';
        if (isset($midSurvRedir) && trim($midSurvRedir) != '') {
            $redir = trim($midSurvRedir);
        } elseif ($GLOBALS["SL"]->REQ->has('redir')
            && trim($GLOBALS["SL"]->REQ->get('redir')) != '') {
            $redir = trim($GLOBALS["SL"]->REQ->get('redir'));
        } elseif ($GLOBALS["SL"]->REQ->has('previous')
            && trim($GLOBALS["SL"]->REQ->get('previous')) != '') {
            $redir = trim($GLOBALS["SL"]->REQ->get('previous'));
        } elseif (session()->has('loginRedirLast')
            && trim(session()->get('loginRedirLast')) != '') {
            $redir = trim(session()->get('loginRedirLast'));
        }
        session()->put('loginRedirLast', $redir);
        $this->surv->custLoop->v["loginRedir"] = $redir;
        return $redir;
    }

    /**
     * Overrides the default Fortify::authenticateUsing function.
     *
     * @return App\Models\User
     */
    public function loginAuthUsing()
    {
        $user = null;
        if ($GLOBALS["SL"]->sysOpts["has-usernames"] == 1) {
            $user = User::where('email', $this->request->email)
                ->orWhere('name', $this->request->email)
                ->first();
        } else {
            $user = User::where('email', $this->request->email)
                ->first();
        }
        if ($user
            && Hash::check($this->request->password, $user->password)) {
            $logTxt = 'User #' . $user->id . ' Logged In';
            $this->surv->custLoop->logAdd('session-stuff', $logTxt);
            return $user;
        }
        return null;
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        return $this->domainPath . '/dashboard'; // '/after-login';
    }

    /**
     * Adds procedures to the logout process.
     *
     * @return mixed
     */
    public function getLogout()
    {
        $uID = 0;
        if (Auth::user() && isset(Auth::user()->id)) {
            $uID = Auth::user()->id;
        }
        if ($uID > 0) {
            SLSess::where('sess_user_id', $uID)
                ->update([ 'sess_is_active' => 0 ]);
        }
        $logTxt = 'User #' . $uID . ' Logged Out';
        $this->surv->custLoop->logAdd('session-stuff', $logTxt);
        Auth::logout();
        session()->flush();
        session()->save();
        return redirect($this->domainPath . '/');
    }

    /**
     * Overrides the default Fortify::requestPasswordResetLinkView function.
     *
     * @return mixed
     */
    public function printPasswordResetLinkView()
    {
        return view('vendor.survloop.auth.passwords.email', $this->surv->custLoop->v);
    }

    /**
     * Manually handled from routes-core.php, this prints the view
     * appearing after a link has been emailed.
     *
     * @return mixed
     */
    public function printPassResetSent()
    {
        return view('vendor.survloop.auth.passwords.email-sent', $this->surv->custLoop->v);
    }

    /**
     * Overrides the default Fortify::requestPasswordResetLinkView function.
     *
     * @return mixed
     */
    public function printPassReset()
    {
        return view('vendor.survloop.auth.passwords.reset', $this->surv->custLoop->v);
    }


}