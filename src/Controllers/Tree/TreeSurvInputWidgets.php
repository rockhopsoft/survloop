<?php
/**
  * TreeSurvInputWidgets is a mid-level class using a standard branching tree, mostly for 
  * processing the input Survloop's surveys and pages.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.13
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use App\Models\User;
use App\Models\SLNode;
use App\Models\SLEmails;
use Illuminate\Http\Request;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvUpload;

class TreeSurvInputWidgets extends TreeSurvUpload
{
    public function sortLoop(Request $request)
    {
        $ret = date("Y-m-d H:i:s");
        $this->survloopInit($request, '');
        $this->loadTree();
        if ($request->has('n') && intVal($request->n) > 0) {
            $nID = intVal($request->n);
            if (isset($this->allNodes[$nID]) && $this->allNodes[$nID]->isLoopSort()) {
                $this->allNodes[$nID]->fillNodeRow();
                $loop = $this->allNodes[$nID]->nodeRow->node_response_set;
                $loop = str_replace('LoopItems::', '', $loop);
                $loopTbl = $GLOBALS["SL"]->dataLoops[$loop]->data_loop_table;
                $sortFld = $this->allNodes[$nID]->nodeRow->node_data_store;
                $sortFld = str_replace($loopTbl . ':', '', $sortFld);
                $loopModel = $GLOBALS["SL"]->modelPath($loopTbl);
                foreach ($GLOBALS["SL"]->REQ->input('item') as $i => $value) {
                    eval("\$recObj = " . $loopModel . "::find(" . $value . ");");
                    $recObj->{ $sortFld } = $i;
                    $recObj->save();
                }
            }
            $ret .= ' ?-)';
        }
        return $ret;
    }
    
    protected function getUserEmailList($userList = [])
    {
        $emaToList = [];
        if (sizeof($userList) > 0) {
            foreach ($userList as $emaTo) {
                $emaUsr = User::where('id', $emaTo)
                    ->where('email', 'NOT LIKE', 'anonymous.%@anonymous.org')
                    ->first();
                if (intVal($emaTo) == -69) { // Current user of the form
                    if (isset($this->v["uID"])) {
                        $emaUsr = User::where('id', $this->v["uID"])
                            ->where('email', 'NOT LIKE', 'anonymous.%@anonymous.org')
                            ->first();
                    }
                    if (!$emaUsr || !isset($emaUsr->email)) {
                        $dataStores = SLNode::where('node_tree', $this->treeID)
                            ->where('node_data_store', 'NOT LIKE', '')
                            //->where('node_data_store', 'IS NOT', NULL)
                            ->where('node_type', 'LIKE', 'Email')
                            ->select('node_data_store')
                            ->get();
                        if ($dataStores->isNotEmpty()) {
                            foreach ($dataStores as $ds) {
                                if (strpos($ds->node_data_store, ':') !== false) {
                                    list($tbl, $fld) = explode(':', $ds->node_data_store);
                                    if (isset($this->sessData->dataSets[$tbl]) 
                                        && isset($this->sessData->dataSets[$tbl][0]->{ $fld })
                                        && trim($this->sessData->dataSets[$tbl][0]->{ $fld }) != '') {
                                        $emaToList[] = [
                                            $this->sessData->dataSets[$tbl][0]->{ $fld }, 
                                            ''
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
                if (intVal($emaTo) == -68) {
                    
                }
                if ($emaUsr && isset($emaUsr->email)) {
                    $emaToList[] = [ $emaUsr->email, $emaUsr->name ];
                }
            }
        }
        return $emaToList;
    }
    
    protected function postEmailFrom()
    {
        return [];
    }
    
    protected function postDumpFormEmailSubject()
    {
        return $GLOBALS["SL"]->sysOpts["site-name"] . ': ' . $GLOBALS["SL"]->treeRow->tree_name;
    }
    
    protected function postNodeLoadEmail($nID)
    {
        $this->v["emaTo"] = $this->getUserEmailList($this->allNodes[$nID]->extraOpts["emailTo"]);
        $this->v["emaCC"] = $this->getUserEmailList($this->allNodes[$nID]->extraOpts["emailCC"]);
        $this->v["emaBCC"] = $this->getUserEmailList($this->allNodes[$nID]->extraOpts["emailBCC"]);
        $this->v["toList"] = '';
        if (sizeof($this->v["emaTo"]) > 0) {
            foreach ($this->v["emaTo"] as $i => $e) {
                $this->v["toList"] .= (($i > 0) ? ' ; ' : '') . $e[0];
            }
        }
        if (sizeof($this->v["emaCC"]) > 0) {
            foreach ($this->v["emaCC"] as $i => $e) {
                $this->v["toList"] .= (($i > 0) ? ' ; ' : '') . $e[0];
            }
        }
        if (sizeof($this->v["emaBCC"]) > 0) {
            foreach ($this->v["emaBCC"] as $i => $e) {
                $this->v["toList"] .= (($i > 0) ? ' ; ' : '') . $e[0];
            }
        }
        return true;
    }
    
    protected function postNodeSendEmail($nID)
    {
        if (sizeof($this->allNodes[$nID]->extraOpts["emailTo"]) > 0) {
            $default = intVal($this->allNodes[$nID]->nodeRow->node_default);
            if ($default > 0 || $default == -69) {
                $this->postNodeLoadEmail($nID);
                if (sizeof($this->v["emaTo"]) > 0) {
                    $currEmail = [];
                    $emaSubject = $this->postDumpFormEmailSubject();
                    $emaContent = '';
                    if ($default > 0) {
                        $currEmail = SLEmails::find($default);
                        if ($currEmail && isset($currEmail->email_subject)) {
                            $emaSubject = $currEmail->email_subject;
                            $emaContent = $this->sendEmailBlurbs($currEmail->email_body);
                        }
                    } elseif ($default == -69) { // dump all form fields
                        $flds = $GLOBALS["SL"]->REQ->all();
                        if ($flds && sizeof($flds) > 0) {
                            foreach ($flds as $key => $val) {
                                if (is_array($val)) {
                                    $val = implode(', ', $val);
                                }
                                $paramKeys = [
                                    '_token', 'ajax', 'tree', 'treeSlug', 'node', 
                                    'nodeSlug', 'loop', 'loopItem', 'step', 
                                    'alt', 'jumpTo', 'afterJumpTo', 'zoomPref'
                                ];
                                if (!in_array($key, $paramKeys)
                                    && strpos($key, 'Visible') === false 
                                    && trim($val) != '') {
                                    $fldNID = intVal(str_replace('n', '', 
                                        str_replace('fld', '', $key)));
                                    $line = '';
                                    if (isset($this->allNodes[$fldNID])) {
                                        $fldNode = $this->allNodes[$fldNID];
                                        if (isset($fldNode->nodeRow->node_prompt_text)) {
                                            $promptText = trim($fldNode->nodeRow->node_prompt_text);
                                            if ($promptText != '') {
                                                $line .= '<b>' . strip_tags($promptText) 
                                                    . '</b><br />';
                                            }
                                        }
                                    }
                                    $line .= $val . '<br /><br />';
                                    if (strpos($emaContent, $line) === false) {
                                        $emaContent .= $line;
                                    }
                                }
                            }
                        }
                    }
                    if ($emaContent != '') {
                        $emaContent = $this->emailRecordSwap($emaContent);
                        $emaSubject = $this->emailRecordSwap($emaSubject);
                        $this->sendEmail(
                            $emaContent, 
                            $emaSubject, 
                            $this->v["emaTo"], 
                            $this->v["emaCC"], 
                            $this->v["emaBCC"],
                            $this->postEmailFrom()
                        );
                        $emaID = ((isset($currEmail->email_id)) 
                            ? $currEmail->email_id : -3);
                        $this->logEmailSent(
                            $emaContent, 
                            $emaSubject, 
                            $this->v["toList"], 
                            $emaID, 
                            $this->treeID, 
                            $this->coreID, 
                            $this->v["uID"]
                        );
                    }
                }
            }
        }
        return '';
    }
    
    public function emailRecordSwap($emaTxt)
    {
        return $this->sendEmailBlurbs($emaTxt);
    }
    
    public function sendEmailBlurbs($emailBody)
    {
        if (!isset($this->v["emailList"])) {
            $this->v["emailList"] = SLEmails::orderBy('email_name', 'asc')
                ->orderBy('email_type', 'asc')
                ->get();
        }
        if (trim($emailBody) != '' && sizeof($this->v["emailList"]) > 0) {
            foreach ($this->v["emailList"] as $i => $e) {
                $emailTag = '[{ ' . $e->email_name . ' }]';
                if (strpos($emailBody, $emailTag) !== false) {
                    $emailBody = str_replace(
                        $emailTag, 
                        $this->sendEmailBlurbs($e->email_body), 
                        $emailBody
                    );
                }
            }
        }
        $dynamos = [
            '[{ Core ID }]', 
            '[{ Login URL }]', 
            '[{ User Email }]', 
            '[{ Email Confirmation URL }]'
        ];
        foreach ($dynamos as $dy) {
            if (strpos($emailBody, $dy) !== false) {
                $swap = $dy;
                $dyCore = str_replace('[{ ', '', str_replace(' }]', '', $dy));
                switch ($dy) {
                    case '[{ Core ID }]':
                        $swap = 0;
                        if (isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl]) 
                            && sizeof($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl]) > 0) {
                            $swap = $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->getKey();
                        }
                        break;
                    case '[{ Login URL }]':
                        $swap = $GLOBALS["SL"]->swapURLwrap($GLOBALS["SL"]->sysOpts["app-url"] . '/login');
                        break;
                    case '[{ User Email }]': 
                        $swap = ((isset($this->v["user"]) && isset($this->v["user"]->email)) 
                            ? $this->v["user"]->email : '');
                        break;
                    case '[{ Email Confirmation URL }]': 
                        $swap = $GLOBALS["SL"]->swapURLwrap($GLOBALS["SL"]->sysOpts["app-url"] 
                            . '/email-confirm/' . $this->createToken('Confirm Email') 
                            . '/' . md5($this->v["user"]->email));
                        break;
                }
                $emailBody = str_replace($dy, $swap, $emailBody);
            }
        }
        return $this->sendEmailBlurbsCustom($emailBody);
    }
    
    public function processEmailConfirmToken(Request $request, $token = '', $tokenB = '')
    {
        $tokRow = SLTokens::where('tok_tok_token', $token)
            ->where('updated_at', '>', $this->tokenExpireDate('Confirm Email'))
            ->first();
        if ($tokRow 
            && isset($tokRow->tok_user_id) 
            && intVal($tokRow->tok_user_id) > 0 
            && trim($tokenB) != '') {
            $usr = User::find($tokRow->tok_user_id);
            if ($usr 
                && isset($usr->email) 
                && trim($usr->email) != '' 
                && md5($usr->email) == $tokenB) {
                $chk = SLUsersRoles::where('role_user_uid', $tokRow->tok_user_id)
                    ->where('role_user_rid', -37)
                    ->first();
                if (!$chk || !isset($chk->role_user_rid)) {
                    $chk = new SLUsersRoles;
                    $chk->role_user_uid = $tokRow->tok_user_id;
                    $chk->role_user_rid = -37;
                    $chk->save();
                }
            }
        }
        $this->setNotif('Thank you for confirming your email address!', 'success');
        return $this->redir('/my-profile');
    }
    
    public function sendEmailBlurbsCustom($emailBody)
    {
        return $emailBody;
    }
    
    protected function manualLogContact($nID, $emaContent, $emaSubject, $email = '', $type = '')
    {
        $log = new SLContact;
        $log->cont_flag    = 'Unread';
        $log->cont_type    = $type;
        $log->cont_email   = $email;
        $log->cont_subject = $emaSubject;
        $log->cont_body    = $emaContent;
        $log->save();
        return true;
    }
    
    protected function processContactForm($nID = -3, $tmpSubTier = [])
    {
        $this->pageCoreFlds = [
            'cont_type', 
            'cont_email', 
            'cont_subject', 
            'cont_body' 
        ];
        $ret = $this->processPageForm($nID, $tmpSubTier, 'SLContact', 'cont_body');
        $this->pageCoreRow->update([ 'cont_flag' => 'Unread' ]);
        $rootNode = SLNode::find($GLOBALS["SL"]->treeRow->tree_root);
        if ($rootNode && isset($rootNode->node_default)) {
            $emails = $GLOBALS["SL"]->mexplode(';', $rootNode->node_default);
            if (sizeof($emails) > 0) {
                $emaToArr = [];
                foreach ($emails as $e) {
                    $emaToArr[] = [ $e, '' ];
                }
                $emaSubj = strip_tags($this->pageCoreRow->cont_subject);
                if (strlen($emaSubj) > 30) {
                    $emaSubj = trim(substr($emaSubj, 0, 30)) . '...'; 
                }
                $emaSubj = $GLOBALS["SL"]->sysOpts["site-name"] . ' Contact: ' . $emaTitle;
                $emaContent = view(
                    'vendor.survloop.admin.contact-row', 
                    [
                        "contact"  => $this->pageCoreRow,
                        "forEmail" => true
                    ]
                )->render();
                $this->sendEmail($emaContent, $emaSubj, $emaToArr);
            }
        }
        $this->setNotif('Thank you for contacting us!', 'success');
        return $ret;
    }
    
    public function authMinimalInit(Request $request, $currPage = '')
    {
        return true;
    }


}