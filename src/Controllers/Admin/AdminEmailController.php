<?php
/**
  * AdminEmailController contains the emailing functions for users who are logged in.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since  v0.2.5
  */
namespace SurvLoop\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\SLEmails;
use App\Models\SLEmailed;
use App\Models\SLContact;
use App\Models\User;
use SurvLoop\Controllers\Admin\AdminCoreController;

class AdminEmailController extends AdminCoreController
{
    
    public function userEmailing(Request $request)
    {
        $this->admControlInit($request, '/dashboard/users/emailing');
        $this->loadPrintUsers();
        return view('vendor.survloop.admin.user-emailing', $this->v);
    }
    
    protected function loadEmails()
    {
        $this->v["emailList"] = SLEmails::orderBy('EmailName', 'asc')
            ->orderBy('EmailType', 'asc')
            ->get();
        return true;
    }
    
    public function processEmail($emailID)
    {
        $email = [
            "rec"     => false,
            "subject" => '',
            "body"    => ''
        ];
        if ($emailID > 0) {
            if (sizeof($this->v["emailList"]) > 0) {
                foreach ($this->v["emailList"] as $e) {
                    if ($e->EmailID == $emailID) {
                        $email["rec"] = $e;
                    }
                }
                if ($email["rec"] !== false && isset($email["rec"]->EmailBody) 
                    && trim($email["rec"]->EmailBody) != '') {
                    $email["body"] 
                        = $GLOBALS["SL"]->swapEmailBlurbs($email["rec"]->EmailBody);
                    $email["subject"] 
                        = $GLOBALS["SL"]->swapEmailBlurbs($email["rec"]->EmailSubject);
                    if (isset($this->custReport->isLoaded)) {
                        $email["body"] = $this->custReport
                            ->sendEmailBlurbsCustom($email["body"]);
                        $email["subject"] = $this->custReport
                            ->sendEmailBlurbsCustom($email["subject"]);
                    }
                }
            }
        }
        return $email;
    }
    
    public function manageEmails(Request $request)
    {
        $this->admControlInit($request, '/dashboard/emails');
        $this->loadEmails();
        $this->v["cssColors"] = $GLOBALS["SL"]->getCssColorsEmail();
        $GLOBALS["SL"]->pageAJAX .= '$(document).on("click", "a.emailLnk", function() {
            $("#emailBody"+$(this).attr("id").replace("showEmail", "")).slideToggle("fast"); });
        $(document).on("click", "#showAll", function() { $(".emailBody").slideToggle("fast"); }); ';
        return view('vendor.survloop.admin.email-manage', $this->v);
    }
    
    public function manageEmailsForm(Request $request, $emailID = -3)
    {
        $this->admControlInit($request, '/dashboard/emails');
        $this->v["currEmailID"] = $emailID;
        $this->v["currEmail"] = new SLEmails;
        if ($emailID > 0) {
            $this->v["currEmail"] = SLEmails::find($emailID);
        }
        $this->v["needsWsyiwyg"] = true;
        $GLOBALS["SL"]->pageAJAX .= ' $("#emailBodyID").summernote({ height: 500 }); ';
        return view('vendor.survloop.admin.email-form', $this->v);
    }
    
    public function manageEmailsPost(Request $request, $emailID)
    {
        if ($request->has('emailType')) {
            $currEmail = new SLEmails;
            if ($request->emailID > 0 && $request->emailID == $emailID) {
                $currEmail = SLEmails::find($request->emailID);
            }
            $currEmail->EmailType    = $request->emailType;
            $currEmail->EmailName    = $request->emailName;
            $currEmail->EmailSubject = $request->emailSubject;
            $currEmail->EmailBody    = $request->emailBody;
            $currEmail->EmailOpts    = 1;
            $currEmail->save();
        }
        return $this->redir('/dashboard/emails');
    }
    
    public function manageContact(Request $request)
    {
        $this->admControlInit($request, '/dashboard/contact');
        $status = [''];
        $this->v["recs"] = [];
        $this->getRecFiltTots('SLContact', 'ContFlag', ['Unread', 'Read', 'Trash'], 'ContID');
        $this->v["filtStatus"] = 'unread';
        if ($request->has('tab')) {
            $this->v["filtStatus"] = trim($request->get('tab'));
        }
        if (in_array($this->v["filtStatus"], ['', 'unread'])) {
            $this->v["recs"] = SLContact::where('ContFlag', 'Unread')
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($this->v["filtStatus"] == 'all') {
            $this->v["recs"] = SLContact::whereIn('ContFlag', ['Read', 'Unread'])
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($this->v["filtStatus"] == 'trash') {
            $this->v["recs"] = SLContact::where('ContFlag', 'Trash')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        $this->v["currPage"][1] = 'Contact Form Messages';
        $GLOBALS["SL"]->pageAJAX .= '$(".changeContStatus").change(function(){
            var cID = $(this).attr( "name" ).replace( "ContFlag", "" );
            var postUrl = "/ajadm/contact?' 
                . ((isset($filtStatus)) ? 'tab=' . $filtStatus . '&' : '') 
                . 'cid="+cID+"&status="+$(this).val();
            $( "#wrapItem"+cID+"" ).load( postUrl );
        });';
        return view('vendor.survloop.admin.contact', $this->v)->render();
    }
    
    public function ajaxSendEmail(Request $request)
    {
        $emaID = (($request->has('e') && intVal($request->get('e')) > 0) 
            ? intVal($request->get('e')) : 0);
        $treeID = (($request->has('t') && intVal($request->get('t')) > 0) 
            ? intVal($request->get('t')) : 1);
        $coreID = (($request->has('c') && intVal($request->get('c')) > 0) 
            ? intVal($request->get('c')) : 0);
        $this->custReport->loadTree($treeID);
        $emaToArr = [];
        $emaToUsrID = 0;
        $ret = $emaTo = $emaSubj = $emaCont = '';
        $currEmail = SLEmails::find($emaID);
        if ($currEmail && isset($currEmail->EmailSubject)) {
            if ($coreID > 0) {
                $this->custReport->loadSessionData($GLOBALS["SL"]->coreTbl, $coreID);
                $emaFld = $GLOBALS["SL"]->getCoreEmailFld();
                if (isset($this->custReport->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->{ $emaFld })) {
                    $emaTo = $this->custReport->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->{ $emaFld };
                    $emaToArr[] = [$emaTo, ''];
                }
            }
            if ($request->has('o') && trim($request->get('o')) != '') {
                $emaToArr = [];
                $overrideEmail = $GLOBALS["SL"]->mexplode(';', $request->get('o'));
                if (sizeof($overrideEmail) > 0) {
                    $emaTo = $overrideEmail[0];
                    foreach ($overrideEmail as $ovr) {
                        $emaToArr[] = [trim($ovr), ''];
                    }
                }
            }
            if (sizeof($emaToArr) > 0) {
                foreach ($emaToArr as $j => $e) {
                    $emaToName = '';
                    $chkEma = User::where('email', $e[0])
                        ->first();
                    if (trim($e[0]) != '' && $chkEma && isset($chkEma->name)) {
                        $emaToName = $chkEma->name;
                    }
                    $emaToArr[$j][1] = $emaToName;
                }
            }
            $emaSubj = $this->custReport->emailRecordSwap($currEmail->EmailSubject);
            $emaCont = $this->custReport->emailRecordSwap($currEmail->EmailBody);
            $sffx = 'e' . $emaID . 't' . $treeID . 'c' . $coreID;
            $ret .= '<a id="hidivBtnMsgDeet' . $sffx 
                . '" class="hidivBtn" href="javascript:;">Message sent to '
                . '<i class="slBlueDark">' . $emaTo . ' (' . $emaToName . ')</i>: ' . $emaSubj 
                . '"</a><div id="hidivMsgDeet' . $sffx . '" class="disNon container"><h2>' 
                . $emaSubj . '</h2><p>' . $emaCont . '</p><hr><hr></div>';
            $replyTo = [
                'info@' . $GLOBALS['SL']->getParentDomain(),
                $GLOBALS["SL"]->sysOpts["site-name"]
            ];
            if ($request->has('r') && trim($request->get('r')) != '') {
                $replyTo[0] = trim($request->get('r'));
            }
            if ($request->has('rn') && trim($request->get('rn')) != '') {
                $replyTo[1] = trim($request->get('rn'));
            }
            if (!$GLOBALS["SL"]->isHomestead()) {
                $this->custReport->sendEmail($emaCont, $emaSubj, $emaToArr, [], [], $replyTo);
            }
            $emaToUsr = User::where('email', $emaTo)->first();
            if ($emaToUsr && isset($emaToUsr->id)) {
                $emaToUsrID = $emaToUsr->id;
            }
            $this->custReport->logEmailSent($emaCont, $emaSubj, $emaTo, $emaID, $treeID, $coreID, $emaToUsrID);
        } else {
            $ret .= '<i class="red">Email template not found.</i>';
        }
        if ($request->has('l') && trim($request->get('l')) != '') {
            //$ret .= $GLOBALS["SL"]->opnAjax() . '$("#' . trim($request->get('l')) . '").fadeOut(100);' 
            //    . $GLOBALS["SL"]->clsAjax();
        }
        return $ret;
    }
    
    public function ajaxContactTabs(Request $request)
    {
        $this->getRecFiltTots('SLContact', 'ContFlag', ['Unread', 'Read', 'Trash'], 'ContID');
        return view('vendor.survloop.admin.contact-tabs', [
            "filtStatus" => (($request->has('tab')) ? $request->get('tab') : 'unread'),
            "recTots"    => $this->v["recTots"]
        ])->render();
    }
    
    public function ajaxContact(Request $request)
    {
        $cID = (($request->has('cid')) ? $request->get('cid') : -3);
        $cRow = (($cID > 0) ? SLContact::find($cID) : []);
        $newStatus = (($request->has('status')) ? $request->get('status') : '');
        if ($cID > 0 && isset($cRow->ContID) && $newStatus != '') {
            $cRow->ContFlag = $newStatus;
            $cRow->save();
        }
        if ($cID > 0 && isset($cRow->ContID)) {
            $currTab = (($request->has('tab')) ? trim($request->get('tab')) : 'unread');
            $newRow = (($currTab == 'unread' && $newStatus != 'Unread')
                || ($currTab == 'all' && $newStatus == 'Trash')) 
                || ($currTab == 'trash' && $newStatus != 'Trash')
                ? '<div class="col-12"><i>Message moved.</i></div>' 
                : view('vendor.survloop.admin.contact-row', [ "contact" => $cRow ])->render();
            return $newRow . '<script type="text/javascript"> $(document).ready(function(){
                setTimeout( function() {
                    var tabLnk = "/ajadm/contact-tabs?tab=' . $currTab . '";
                    $( "#pageTabs" ).load( tabLnk );
                    $( "#contactPush" ).load( "/ajadm/contact-push" );
                }, 100);
            }); </script>';
        }
    }
    
    public function sendEmailPage(Request $request)
    {
        $this->admControlInit($request, '/dashboard/send-email');
        if ($request->has('send') && intVal($request->get('send')) == 1
            && $request->has('emaTo') && trim($request->emaTo) != '') {
            $emaTo = $emaCC = $emaBCC = [];
            foreach (['To', 'CC', 'BCC'] as $type) {
                if ($request->has('ema' . $type) 
                    && trim($request->get('ema' . $type)) != '') {
                    eval("\$ema" . $type . "[] = ['" 
                        . trim($request->get('ema' . $type)) . "', ''];");
                }
            }
            $this->loadCustLoop($request);
            $this->custReport->sendEmail(
                (($request->has('emaBody')) ? trim($request->emaBody) : ''), 
                (($request->has('emaSubject')) ? trim($request->emaSubject) : ''), 
                $emaTo, 
                $emaCC, 
                $emaBCC, 
                $emaCC[0]
            );
            if ($request->has('redir') && trim($request->get('redir')) != '') {
                return $this->redir($request->get('redir'), true);
            }
        }
        $this->loadEmails();
        $this->v["userList"] = User::where('name', 'NOT LIKE', 'Session#%')
            ->where('email', 'NOT LIKE', 'anonymous.%@anonymous.org')
            ->select('email', 'name')
            ->orderBy('name', 'asc')
            ->get();
        $this->v["emaID"] = (($request->has('emaTemplate')) 
            ? $request->get('emaTemplate') : -3);
        $this->v["email"] = $this->processEmail($this->v["emaID"]);
        $this->v["needsWsyiwyg"] = true;
        $GLOBALS["SL"]->pageAJAX .= ' $("#emaBodyID").summernote({ height: 350 }); ';
        return view('vendor.survloop.admin.send-email', $this->v);
    }
    
    public function printSentEmails(Request $request)
    {
        $this->admControlInit($request, '/dashboard/sent-emails');
        $this->v["emailed"] = SLEmailed::whereNotNull('EmailedTo')
            ->where('EmailedTo', 'NOT LIKE', '')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('vendor.survloop.admin.sent-emails', $this->v);
    }

}
