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
use App\Models\SLSess;
use App\Models\SLNode;
use App\Models\SLTree;
use App\Models\SLUsersActivity;
use App\Models\SLDefinitions;
use SurvLoop\Controllers\Globals;
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
    protected $formFooter   = '';
    protected $midSurvRedir = '';
    protected $midSurvBack  = '';
    
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
    
    protected function chkGlobal(Request $request)
    {
        if (!isset($GLOBALS["SL"])) {
            $GLOBALS["SL"] = new Globals($request, $this->dbID, $this->treeID, $this->treeID);
        }
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
            "midSurvBack"   => $this->midSurvBack,
            "formFooter"    => $this->formFooter,
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
            "midSurvBack"  => $this->midSurvBack,
            "formFooter"   => $this->formFooter,
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
    
    protected function chkAuthPageOpts(Request $request)
    {
        if (session()->has('lastTree') && intVal(session()->get('lastTree')) > 0) {
            $this->treeID = intVal(session()->get('lastTree'));
            $this->currTree = SLTree::find($this->treeID);
            if ($this->currTree && isset($this->currTree->TreeDatabase)) {
                $this->dbID = $this->currTree->TreeDatabase;
            }
            if (session()->has('sessID' . $this->treeID) && intVal(session()->get('sessID' . $this->treeID)) > 0
                && session()->has('coreID' . $this->treeID) && intVal(session()->get('coreID' . $this->treeID)) > 0) {
                $sess = SLSess::where('SessID', intVal(session()->get('sessID' . $this->treeID)))
                    ->where('SessCoreID', intVal(session()->get('coreID' . $this->treeID)))
                    ->where('SessTree', $this->treeID)
                    ->first();
                if ($sess && isset($sess->SessCurrNode) && intVal($sess->SessCurrNode) > 0) {
                    $this->currNode = SLNode::find($sess->SessCurrNode);
                    $this->loadNodeLoginPass($request);
                }
            }
        }
        $this->chkGlobal($request);
        return true;
    }
    
    protected function loadNodeLoginPass(Request $request)
    {
        if ($this->currNode && isset($this->currNode->NodeID)) {
            $nID = $this->currNode->NodeID;
            $this->surv = new SurvLoop;
            $this->surv->syncDataTrees($request, $this->dbID, $this->treeID);
            $this->surv->loadLoop($request);
            $this->surv->custLoop->loadTree($this->treeID, $request);
            $this->surv->custLoop->updateCurrNode($nID);
            $this->surv->custLoop->updateCurrNode($this->surv->custLoop->getNextNonBranch($nID));
            $node2 = $this->surv->custLoop->allNodes[$this->surv->custLoop->currNode()];
            $node2->fillNodeRow();
            $this->midSurvRedir = '/u/' . $this->currTree->TreeSlug . '/' . $node2->nodeRow->NodePromptNotes;
            
            $backPageNode = $this->surv->custLoop->getPrevOfType($nID);
            if ($backPageNode > 0) {
                $node2 = $this->surv->custLoop->allNodes[$backPageNode];
                $node2->fillNodeRow();
                $this->midSurvBack = '/u/' . $this->currTree->TreeSlug . '/' . $node2->nodeRow->NodePromptNotes;
            }
            
            $node2 = null; // reset in search of custom mid-survey language
            if ($request->has('nd') && intVal($request->get('nd')) > 0) {
                $nIn = intVal($request->get('nd'));
                if ($this->surv->custLoop->allNodes[$nIn] 
                    && $this->surv->custLoop->allNodes[$nIn]->nodeType == 'User Sign Up') {
                    $node2 = $this->surv->custLoop->allNodes[$nIn];
                    $node2->fillNodeRow();
                }
            } else {
                $node2 = $this->surv->custLoop->allNodes[$nID];
                while ($node2 && $node2->nodeType != 'User Sign Up') {
                    $nID2 = $this->surv->custLoop->nextNode($nID);
                    $node2 = null;
                    if (isset($this->surv->custLoop->allNodes[$nID2]) 
                        && $this->surv->custLoop->allNodes[$nID2]->nodeType != 'Page') {
                        $node2 = $this->surv->custLoop->allNodes[$nID2];
                    }
                }
                if ($node2 && $node2->nodeType == 'User Sign Up') {
                    $node2->fillNodeRow();
                }
            }
            if ($node2 && isset($node2->nodeRow->NodePromptText) && trim($node2->nodeRow->NodePromptText) != '') {
                $GLOBALS["SL"]->sysOpts["midsurv-instruct"] = $node2->nodeRow->NodePromptText;
            }
            $this->formFooter = '<center><div class="treeWrapForm">' . $this->surv->custLoop->printCurrRecMgmt()
                . '</div></center>';
        }
        return true;
    }
    
}