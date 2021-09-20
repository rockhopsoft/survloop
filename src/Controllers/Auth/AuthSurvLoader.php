<?php
/**
  * AuthSurvLoader provides some shared functions for customized
  * loads of Laravel-controlled authorization behavior.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since 0.3.2
  */
namespace RockHopSoft\Survloop\Controllers\Auth;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\SLSess;
use App\Models\SLNode;
use App\Models\SLTree;
use App\Models\SLDefinitions;
use RockHopSoft\Survloop\Controllers\SurvloopController;
use RockHopSoft\Survloop\Controllers\Survloop;
use RockHopSoft\Survloop\Controllers\SystemDefinitions;
use RockHopSoft\Survloop\Controllers\Globals\Globals;

class AuthSurvLoader extends Controller
{
    protected $request = null;

    protected $domainPath          = '';
    protected $loginPath           = '/login';
    protected $redirectPath        = '/dashboard'; // '/after-login';
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
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->chkAuthPageOpts();
        $this->domainPath          = $GLOBALS["SL"]->sysOpts["app-url"];
        $this->loginPath           = $this->domainPath . '/login';
        $this->redirectPath        = $this->domainPath . '/dashboard'; // '/after-login';
        $this->redirectAfterLogout = $this->domainPath . '/login';
        $this->middleware('guest', ['except' => 'getLogout']);

        // SurvloopControllerUtils.php does not set this IF loaded with a
        // frame parameter, but that is not allowed for authentication pages:
        header('X-Frame-Options: SAMEORIGIN');
    }

    /**
     * Load core system information needed to customize user authentication.
     *
     * @return void
     */
    protected function chkAuthPageOpts()
    {
        if (session()->has('lastTree')
            && intVal(session()->get('lastTree')) > 0) {
            $this->chkAuthLastTree();
        }
        if (!isset($GLOBALS["SL"]->sysOpts["footer-master"])
            || !isset($this->surv->custLoop->v["css"])) {
            $this->surv = new Survloop;
            $this->surv->syncDataTrees($this->request, $this->dbID, $this->treeID);
            $this->surv->loadLoop($this->request);
            $this->surv->custLoop->v["sysDefs"] = new SystemDefinitions;
            $this->surv->custLoop->v["css"]
                = $this->surv->custLoop->v["sysDefs"]->loadCss();

            $this->surv->custLoop->v["midSurvRedir"] = $this->midSurvRedir;
            $this->surv->custLoop->v["midSurvBack"]  = $this->midSurvBack;
            $this->surv->custLoop->v["formFooter"]   = $this->formFooter;
            $this->surv->custLoop->v["errorMsg"]     = '';
            //$sl = new SurvloopController;
            //$sl->initCustViews($this->request);
        }
        return true;
    }

    /**
     * Check for the last survey tree this user accessed [before logging in].
     *
     * @return void
     */
    private function chkAuthLastTree()
    {
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
                $this->loadNodeLoginPass();
            }
        }
    }

    /**
     * Load the current survey tree node, which this user was on [before logging in].
     *
     * @return void
     */
    private function loadNodeLoginPass()
    {
        if ($this->currNode
            && isset($this->currNode->node_id)
            && $this->request->has('nd')
            && intVal($this->request->get('nd')) > 0) {
            $nIn = intVal($this->request->get('nd'));
            $nID = $this->currNode->node_id;
            $this->surv = new Survloop;
            $this->surv->syncDataTrees($this->request, $this->dbID, $this->treeID);
            $this->surv->loadLoop($this->request);
            $this->surv->custLoop->loadTree($this->treeID, $this->request);
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
            //if ($this->request->has('nd') && intVal($this->request->get('nd')) > 0) {
                if ($this->surv->custLoop->allNodes[$nIn]
                    && $this->surv->custLoop->allNodes[$nIn]->nodeType
                        == 'User Sign Up') {
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
                $GLOBALS["SL"]->sysOpts["midsurv-instruct"]
                    = $node2->nodeRow->node_prompt_text;
            }
            $this->formFooter = '<center><div class="treeWrapForm">'
                . $this->surv->custLoop->printCurrRecMgmt()
                . '</div></center>';
        }
    }


}