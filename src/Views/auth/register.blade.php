<?php // sorry, not sure how this should be done instead
$surv = new SurvLoop\Controllers\SurvLoop;
$surv->loadLoop(new Illuminate\Http\Request);
$v = $surv->custLoop->v;
?>@extends('vendor.survloop.master')

@section('content')
<!-- resources/views/vendor/survloop/auth/register.blade.php -->
<form name="mainPageForm" method="POST" action="{{ url('/register') }}" onSubmit="return checkNodeForm();">
<input type="hidden" id="isSignupID" name="isSignup" value="1">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">

<div class="w100"><center><div id="treeWrap" class="treeWrapForm">

<div class="p20"></div>

<div class="row loginTitles">
    <div class="col-md-6">
        @if (isset($GLOBALS['SL']->sysOpts["signup-instruct"]) 
            && trim($GLOBALS['SL']->sysOpts["signup-instruct"]) != '')
            {!! $GLOBALS['SL']->sysOpts["signup-instruct"] !!}
        @else
            <h1 class="mT0">Sign Up</h1>
        @endif
    </div>
    <div class="col-md-6 taR pT5">
        @if (!isset($GLOBALS['SL']->sysOpts["signup-instruct"]) 
            || trim($GLOBALS['SL']->sysOpts["signup-instruct"]) != '<h2 class="mT5 mB0">Create Admin Account</h2>')
            <a href="/login" class="btn btn-default">Login</a>
        @endif
    </div>
</div>

<div class="nodeAnchor"><a id="n004" name="n004"></a></div>
@if (isset($GLOBALS['SL']->sysOpts["login-instruct"]) && trim($GLOBALS['SL']->sysOpts["login-instruct"]) != '')
    <h4 class="mB20">{!! $GLOBALS['SL']->sysOpts["login-instruct"] !!}</h4>
@endif

@if (isset($errorMsg))
    <div class="alert alert-danger" role="alert">{!! $errorMsg !!}</div>
@endif

<div id="node004" class="nodeWrap{{ $errors->has('name') ? 'Error' : '' }}">
    <div id="nLabel004" class="nPrompt"><label for="nameID">
        Username: 
        @if (isset($GLOBALS["SL"]->sysOpts["user-name-optional"]) 
            && $GLOBALS["SL"]->sysOpts["user-name-optional"] == 'Off')
            <span class="red">*required</span>
        @endif
    </label></div>
    <div class="nFld mT0">
        <input id="nameID" name="name" value="{{ old('name') }}" type="text" class="form-control">
        @if ($errors->has('name'))
            <span class="help-block">
                <strong>{{ $errors->first('name') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="nodeAnchor"><a id="n001" name="n001"></a></div>
<div class="nodeHalfGap"></div>

<div id="node001" class="nodeWrap{{ $errors->has('email') ? 'Error' : '' }}">
    <div id="nLabel001" class="nPrompt"><label for="emailID">
        Email:
        @if (!isset($GLOBALS["SL"]->sysOpts["user-email-optional"]) 
            || $GLOBALS["SL"]->sysOpts["user-email-optional"] == 'Off')
            <span class="red">*required</span>
        @endif
    </label></div>
    <div class="nFld mT0">
        <input id="emailID" name="email" value="{{ old('email') }}" type="email" class="form-control">
        @if ($errors->has('email'))
            <span class="help-block">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
        @endif
        @if (isset($GLOBALS["SL"]->sysOpts["user-email-optional"]) 
            && $GLOBALS["SL"]->sysOpts["user-email-optional"] == 'On')
            * Currently, you will only be able reset a lost password with an email address.
        @endif
    </div>
</div>

<div class="nodeAnchor"><a id="n002" name="n002"></a></div>
<div class="nodeHalfGap"></div>

<div id="node002" class="nodeWrap{{ $errors->has('password') ? 'Error' : '' }}">
    <div id="nLabel002" class="nPrompt"><label for="password">
        Password: <span class="red">*required, 8 character minimum</span>
    </label></div>
    <div class="relDiv w100"><div id="passStrng" class="red"></div></div>
    <div class="nFld mT0">
        <input id="password" name="password" value="" type="password" class="form-control">
        @if ($errors->has('password'))
            <span class="help-block"><strong>{{ $errors->first('password') }}</strong></span>
        @endif
    </div>
</div>

<div class="nodeAnchor"><a id="n003" name="n003"></a></div>
<div class="nodeHalfGap"></div>

<div id="node003" class="nodeWrap">
    <div id="nLabel003" class="nPrompt"><label for="password-confirm">
        Confirm Password: <span class="red">*required</span>
    </label></div>
    <div class="nFld mT0">
        <input id="password_confirmation" name="password_confirmation" value="" type="password" 
            class="form-control">
    </div>
</div>

<div class="nodeHalfGap"></div>

<label><input type="checkbox" name="newVolunteer" value="1" > Volunteer</label>

<center><input type="submit" class="btn btn-xl btn-primary" value="Sign Up"></center>

<div class="nodeHalfGap"></div>

</div></center></div>

</form>

<script type="text/javascript" src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/zxcvbn.js">
</script>
<script type="text/javascript">
$(document).ready(function(){
{!! view('vendor.survloop.auth.register-ajax-zxcvbn', [])->render() !!}
});
function checkNodeForm() {
    hasAttemptedSubmit = true;
    totFormErrors=0; formErrorsEng = "";
    {!! view('vendor.survloop.auth.register-node-jsValid', [ "coreID" => -3 ])->render() !!}
    if (totFormErrors > 0) {
        setFormErrs();
        return false;
    }
    clearFormErrs();
    firstNodeError = 0;
    return true;
}
</script>

@endsection
