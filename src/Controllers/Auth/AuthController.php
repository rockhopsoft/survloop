<?php
namespace SurvLoop\Controllers\Auth;

use Auth;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\User;
use App\Models\SLNode;
use App\Models\SLTree;
use App\Models\SLUsersActivity;
use App\Models\SLDefinitions;
use SurvLoop\Controllers\SurvLoopController;
use SurvLoop\Controllers\SurvLoop;

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
    
    protected $dbID         = 1;
    protected $treeID       = 1;
    protected $currTree     = null;
    protected $currNode     = null;
    protected $surv         = null;
    protected $midSurvRedir = '';
    
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
    
    protected function chkAuthPageOpts(Request $request)
    {
        if ($request->has('nd') && intVal($request->get('nd')) > 0 && session()->has('midSurvSignupNode')
            && intVal(session()->get('midSurvSignupNode')) == intVal($request->get('nd'))) {
            $this->currNode = SLNode::find(intVal($request->get('nd')));
            if ($this->currNode && isset($this->currNode->NodeID) && session()->has('midSurvSignupTree')
                && intVal(session()->get('midSurvSignupTree')) == $this->currNode->NodeTree) {
                $this->treeID = $this->currNode->NodeTree;
                $this->currTree = SLTree::find($this->treeID);
                if ($this->currTree && isset($this->currTree->TreeDatabase)) {
                    $this->dbID = $this->currTree->TreeDatabase;
                }
            }
        }
        $this->surv = new SurvLoop;
        $this->surv->syncDataTrees($request, $this->dbID, $this->treeID);
        $this->surv->loadLoop($request);
        if ($request->has('nd') && intVal($request->get('nd')) > 0 && $request->session()->has('midSurvSignupNode')
            && intVal($request->session()->get('midSurvSignupNode')) == intVal($request->get('nd'))) {
            $this->currNode = SLNode::find(intVal($request->get('nd')));
            if ($this->currNode && isset($this->currNode->NodeID) && $request->session()->has('midSurvSignupTree')
                && intVal($request->session()->get('midSurvSignupTree')) == $this->currNode->NodeTree) {
                $this->surv->custLoop->loadTree($this->treeID, $request);
                $this->surv->custLoop->updateCurrNode($this->currNode->NodeID);
                $this->surv->custLoop->updateCurrNode($this->surv->custLoop->getNextNonBranch($this->currNode->NodeID));
                $nextNode = $this->surv->custLoop->allNodes[$this->surv->custLoop->currNode()];
                $nextNode->fillNodeRow();
                $this->midSurvRedir = '/u/' . $this->currTree->TreeSlug . '/' . $nextNode->nodeRow->NodePromptNotes;
                if (isset($this->currNode->NodePromptText) && trim($this->currNode->NodePromptText) != '') {
                    $GLOBALS["SL"]->sysOpts["signup-instruct"] = $this->currNode->NodePromptText;
                }
            }
        }
        //session()->get('midSurvSignupCore', $this->coreID);
        return true;
    }
    
    public function getRegister(Request $request)
    {
        $this->chkAuthPageOpts($request);
        $emailRequired = 'false';
        if (!isset($GLOBALS["SL"]->sysOpts["user-email-optional"]) 
            || $GLOBALS["SL"]->sysOpts["user-email-optional"] == 'Off') {
            $emailRequired = 'true';
        }
        return view('vendor.survloop.auth.register', [
            "request"       => $request,
            "sysOpts"       => $GLOBALS["SL"]->sysOpts,
            "midSurvRedir"  => $this->midSurvRedir,
            "emailRequired" => $emailRequired
            ]);
    }
    
    public function getLogin(Request $request)
    {
        $this->chkAuthPageOpts($request);
        if (Auth::user() && isset(Auth::user()->id)) {
            return redirect($this->redirectPath());
        }
        
        
        return view('vendor.survloop.auth.login', [
            "request"      => $request,
            "sysOpts"      => $GLOBALS["SL"]->sysOpts,
            "midSurvRedir" => $this->midSurvRedir,
            "errorMsg"     => ''
            ]);
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
            if ($request->has('previous')) {
                return redirect($request->get('previous'));
            }
            return redirect('/afterLogin');
        }
        return view('vendor.survloop.auth.login', [
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
        if ($appUrl && isset($appUrl->DefDescription)) {
            $this->domainPath = $appUrl->DefDescription;
        }
        return $this->domainPath;
    }
    
    public function printPassReset(Request $request)
    {
        return view('vendor.survloop.auth.passwords.email');
    }
    
}