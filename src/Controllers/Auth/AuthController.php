<?php
/**
  * AuthController customizes some of the standard Laravel behavior.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since 0.0
  */
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
use SurvLoop\Controllers\Globals\Globals;
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
        if (!isset($data['name']) || trim($data['name']) == '') {
            $data['name'] = $data['email'];
        }
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
        return view(
            'vendor.survloop.auth.register', 
            [
                "request"       => $request,
                "sysOpts"       => $GLOBALS["SL"]->sysOpts,
                "midSurvRedir"  => $this->midSurvRedir,
                "midSurvBack"   => $this->midSurvBack,
                "formFooter"    => $this->formFooter
            ]
        );
    }
    
    public function getLogin(Request $request)
    {
        $this->chkAuthPageOpts($request);
        if (Auth::user() && isset(Auth::user()->id)) {
            return redirect('/my-profile');
            //return redirect($this->redirectPath());
        }
        return view(
            'vendor.survloop.auth.login', 
            [
                "request"      => $request,
                "sysOpts"      => $GLOBALS["SL"]->sysOpts,
                "midSurvRedir" => $this->midSurvRedir,
                "midSurvBack"  => $this->midSurvBack,
                "formFooter"   => $this->formFooter,
                "errorMsg"     => ''
            ]
        );
    }
    
    public function postLogin(Request $request)
    {
        $pass = false;
        if (Auth::attempt([
            'name'     => $request->email, 
            'password' => $request->password
        ])) {
            $pass = true;
        } elseif (Auth::attempt([
            'email'    => $request->email, 
            'password' => $request->password
        ])) {
            $pass = true;
        }
        if ($pass) {
            $sl = new SurvLoopController;
            $uID = ((Auth::user() && isset(Auth::user()->id)) ? Auth::user()->id : 0);
            $sl->logAdd('session-stuff', 'User #' . $uID . ' Logged In');
            if ($request->has('previous') && trim($request->get('previous')) != '') {
                session()->put('redirLoginSurvey', time());
                session()->put('previousUrl', trim($request->get('previous')));
                session()->save();
            }
            return redirect('/afterLogin');
        }
        $this->chkAuthPageOpts($request);
        $err = 'That combination of password with that username or email did not work.';
        return view(
            'vendor.survloop.auth.login', 
            [
                "request"      => $request,
                "sysOpts"      => $GLOBALS["SL"]->sysOpts,
                "midSurvRedir" => $this->midSurvRedir,
                "midSurvBack"  => $this->midSurvBack,
                "formFooter"   => $this->formFooter,
                "errorMsg"     => $err 
            ]
        );
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
        if ($uID > 0) {
            SLSess::where('sess_user_id', $uID)
                ->update([ 'sess_is_active' => 0 ]);
        }
        $sl->logAdd('session-stuff', 'User #' . $uID . ' Logged Out');
        Auth::logout();
        session()->flush();
        session()->save();
        return redirect($this->domainPath . '/');
    }
    
    protected function loadDomain()
    {
        $appUrl = SLDefinitions::select('def_description')
            ->where('def_database', 1)
            ->where('def_set', 'System Settings')
            ->where('def_subset', 'app-url')
            ->first();
        if ($appUrl && isset($appUrl->def_description)) {
            $this->domainPath = $appUrl->def_description;
        }
        return $this->domainPath;
    }
    
    public function printPassReset(Request $request)
    {
        return view('vendor.survloop.auth.passwords.email');
    }
    
    public function printPassResetSent(Request $request)
    {
        return view('vendor.survloop.auth.passwords.email-sent');
    }
    
    protected function chkAuthPageOpts(Request $request)
    {
        if (session()->has('lastTree') 
            && intVal(session()->get('lastTree')) > 0) {
            $this->treeID = intVal(session()->get('lastTree'));
            $this->currTree = SLTree::find($this->treeID);
            if ($this->currTree && isset($this->currTree->tree_database)) {
                $this->dbID = $this->currTree->tree_database;
            }
            if (session()->has('sessID' . $this->treeID) 
                && intVal(session()->get('sessID' . $this->treeID)) > 0
                && session()->has('coreID' . $this->treeID) 
                && intVal(session()->get('coreID' . $this->treeID)) > 0) {
                $sID = intVal(session()->get('sessID' . $this->treeID));
                $tID = intVal(session()->get('coreID' . $this->treeID));
                $sess = SLSess::where('sess_id', $sID)
                    ->where('sess_core_id', $tID)
                    ->where('sess_tree', $this->treeID)
                    ->first();
                if ($sess 
                    && isset($sess->sess_curr_node) 
                    && intVal($sess->sess_curr_node) > 0) {
                    $this->currNode = SLNode::find($sess->sess_curr_node);
                    $this->loadNodeLoginPass($request);
                }
            }
        }
        $this->chkGlobal($request);
        if (!isset($GLOBALS["SL"]->sysOpts["footer-master"])) {
            $sl = new SurvLoopController;
            $sl->initCustViews($request);
        }
        return true;
    }
    
    protected function loadNodeLoginPass(Request $request)
    {
        if ($this->currNode && isset($this->currNode->node_id) 
            && $request->has('nd') && intVal($request->get('nd')) > 0) {
            $nID = $this->currNode->node_id;
            $this->surv = new SurvLoop;
            $this->surv->syncDataTrees($request, $this->dbID, $this->treeID);
            $this->surv->loadLoop($request);
            $this->surv->custLoop->loadTree($this->treeID, $request);
            $this->surv->custLoop->updateCurrNode($nID);
            $curr = $this->surv->custLoop->getNextNonBranch($nID);
            $this->surv->custLoop->updateCurrNode($curr);
            $curr = $this->surv->custLoop->currNode();
            $node2 = $this->surv->custLoop->allNodes[$curr];
            $node2->fillNodeRow();
            $this->midSurvRedir = '/u/' . $this->currTree->tree_slug 
                . '/' . $node2->nodeRow->node_prompt_notes;
            
            $backPageNode = $this->surv->custLoop->getPrevOfTypeWithConds($nID);
            if ($backPageNode > 0) {
                $node2 = $this->surv->custLoop->allNodes[$backPageNode];
                $node2->fillNodeRow();
                $this->midSurvBack = '/u/' . $this->currTree->tree_slug 
                    . '/' . $node2->nodeRow->node_prompt_notes;
            }
            
            $node2 = null; // reset in search of custom mid-survey language
            //if ($request->has('nd') && intVal($request->get('nd')) > 0) {
                $nIn = intVal($request->get('nd'));
                if ($this->surv->custLoop->allNodes[$nIn] 
                    && $this->surv->custLoop->allNodes[$nIn]->nodeType == 'User Sign Up') {
                    $node2 = $this->surv->custLoop->allNodes[$nIn];
                    $node2->fillNodeRow();
                }
            /* } else {
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
            } */
            if ($node2 
                && isset($node2->nodeRow->node_prompt_text) 
                && trim($node2->nodeRow->node_prompt_text) != '') {
                $GLOBALS["SL"]->sysOpts["midsurv-instruct"] = $node2->nodeRow->node_prompt_text;
            }
            $this->formFooter = '<center><div class="treeWrapForm">' 
                . $this->surv->custLoop->printCurrRecMgmt()
                . '</div></center>';
        }
        return true;
    }
    
}