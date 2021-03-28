<?php
$surv = new RockHopSoft\Survloop\Controllers\Survloop;
$surv->loadLoop(new Illuminate\Http\Request);
$v = $surv->custLoop->v;
$sysDefs = new RockHopSoft\Survloop\Controllers\SystemDefinitions;
$css = $sysDefs->loadCss();
?>@extends('vendor.survloop.master')
@section('content')
<div id="ajaxWrap">
<!-- resources/views/vendor/survloop/auth/two-factor-challenge.blade.php -->

<form name="mainPageForm" method="POST" action="{{ url('/two-factor-challenge') }}">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" id="isLoginID" name="isLogin" value="1">
<input type="hidden" name="previous" 
    @if (isset($midSurvRedir) && trim($midSurvRedir) != '') 
        value="{{ $midSurvRedir }}"
    @elseif ($GLOBALS['SL']->REQ->has('redir')) 
        value="{{ $GLOBALS['SL']->REQ->get('redir') }}"
    @elseif ($GLOBALS['SL']->REQ->has('previous')) 
        value="{{ $GLOBALS['SL']->REQ->get('previous') }}"
    @else value="{{ URL::previous() }}"
    @endif >

<div class="w100 row2" style="padding: 30px 0px 60px 0px;"><center>
    <div id="treeWrap" class="treeWrapForm">
        <div class="slCard">

            <a href="/login{{ (($GLOBALS['SL']->REQ->has('nd')) 
                ? '?nd=' . $GLOBALS['SL']->REQ->get('nd') : '') 
                }}" class="btn btn-secondary pull-right mL20"
                >Login</a>
            <div class="nodeAnchor"><a id="n004" name="n004"></a></div>
            <div class="nPrompt">
                <h1 class="mT0">Two-Factor Authentication Challenge</h1>
            </div>

            <div id="authAppCodes" class="disBlo">
                <div id="node003" class="nodeWrap">
                    <div class="nodeHalfGap"></div>
                    <div id="nLabel003" class="nPrompt">
                        <label for="password">
                            Please enter the <b>temporary code from your 
                            phone's authentication app</b> to login.
                            <span class="red">*required</span>
                        </label>
                    </div>
                    <div class="nFld">
                        <input type="text" id="code" name="code" value="" 
                            class="form-control" autocomplete="off">
                    </div>
                    <div class="nodeHalfGap"></div>
                    <p><a id="showRecoveryCodes" href="javascript:;"
                        >Lost your phone, and need to use recovery codes?</a></p>
                </div>
            </div>

            <div id="recoveryCodes" class="disNon">
                <div id="node004" class="nodeWrap">
                    <div class="nodeHalfGap"></div>
                    <div id="nLabel003" class="nPrompt">
                        <label for="password">
                            Please enter <b>one of the authentication recovery codes</b>
                            you copied to a secure place.
                            <span class="red">*required</span>
                        </label>
                    </div>
                    <div class="nFld">
                        <input type="text" id="recovery_code" name="recovery_code" 
                            value="" class="form-control" autocomplete="off">
                    </div>
                    <div class="nodeHalfGap"></div>
                    <p><a id="hideRecoveryCodes" href="javascript:;"
                        >Have your phone, and need to use your app?</a></p>
                </div>
            </div>

            <div id="pageBtns">
                <div id="formErrorMsg"></div>
                <div id="nodeSubBtns" class="nodeSub">
                    <input type="submit" value="Enter Code" 
                        class="fR btn btn-primary btn-lg" 
                        id="twoFactorConfirmBtn">
                </div>
            </div>
            <div class="pageBotGap"></div>

        </div>
    </div></center>
</div>

@if (isset($formFooter)) {!! $formFooter !!} @endif

</form>

<style>
#main, body { background: {{ $css["color-main-faint"] }}; }
</style>
<script type="text/javascript"> $(document).ready(function(){

$(document).on("click", "#showRecoveryCodes", function() {
    $("#authAppCodes").fadeOut(300);
    setTimeout(function() { $("#recoveryCodes").fadeIn(300); }, 301);
});
$(document).on("click", "#hideRecoveryCodes", function() {
    $("#recoveryCodes").fadeOut(300);
    setTimeout(function() { $("#authAppCodes").fadeIn(300); }, 301);
});

}); </script>


</div>
@endsection