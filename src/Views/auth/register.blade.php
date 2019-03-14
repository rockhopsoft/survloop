@extends('vendor.survloop.master')
@section('content')
<!-- resources/views/vendor/survloop/auth/register.blade.php -->

<form name="mainPageForm" method="POST" action="{{ url('/register') }}">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" id="isSignupID" name="isSignup" value="1">
<input type="hidden" name="previous" 
    @if (isset($midSurvRedir) && trim($midSurvRedir) != '') value="{{ $midSurvRedir }}"
    @elseif ($request->has('redir')) value="{{ $request->get('redir') }}"
    @elseif ($request->has('previous')) value="{{ $request->get('previous') }}"
    @else value="{{ URL::previous() }}"
    @endif >

<div class="w100"><center><div id="treeWrap" class="treeWrapForm">

<div class="p20"></div>

@if (!isset($sysOpts["signup-instruct"])
    || trim($sysOpts["signup-instruct"]) != '<h2 class="mT5 mB0">Create Admin Account</h2>')
    <a href="/login{{ (($request->has('nd')) ? '?nd=' . $request->get('nd') : '') 
        }}" class="btn btn-secondary pull-right mL20">Login</a>
@endif
<div class="nodeAnchor"><a id="n004" name="n004"></a></div>
<div class="nPrompt">
    <h1 class="mT0 mB20">Sign Up</h1>
    @if (isset($sysOpts["midsurv-instruct"]) && trim($sysOpts["midsurv-instruct"]) != '')
        {!! $sysOpts["midsurv-instruct"] !!}
    @elseif (isset($sysOpts["signup-instruct"]) && trim($sysOpts["signup-instruct"]) != '')
        {!! $sysOpts["signup-instruct"] !!}
    @endif
</div>

@if (isset($errorMsg)) <div class="alert alert-danger" role="alert">{!! $errorMsg !!}</div> @endif

<div id="node004" class="nodeWrap{{ ((isset($errors) && $errors->has('name')) ? 'Error' : '') }}">
    <div class="nodeHalfGap"></div>
    <div id="nLabel004" class="nPrompt"><label for="nameID">
        Username: 
        @if (isset($sysOpts["user-name-req"]) && intVal($sysOpts["user-name-req"]) == 1)
            <span class="red">*required</span>
        @endif
    </label></div>
    <div class="nFld" style="margin-top: 20px;">
        <input id="nameID" name="name" value="{{ old('name') }}" type="text" class="form-control">
        @if (isset($errors) && $errors->has('name'))
            <span class="form-text"><strong>{{ $errors->first('name') }}</strong></span>
        @endif
    </div>
    <div class="nodeHalfGap"></div>
</div>

<div class="nodeAnchor"><a id="n001" name="n001"></a></div>
<div id="node001" class="nodeWrap{{ ((isset($errors) && $errors->has('email')) ? 'Error' : '') }}">
    <div class="nodeHalfGap"></div>
    <div id="nLabel001" class="nPrompt"><label for="emailID">
        Email:
        @if (!isset($sysOpts["user-email-optional"]) || $sysOpts["user-email-optional"] == 'Off')
            <span class="red">*required</span>
        @endif
    </label></div>
    <div class="nFld" style="margin-top: 20px;">
        <input id="emailID" name="email" value="{{ old('email') }}" type="email" class="form-control">
        
        @if (isset($errors) && $errors->has('email'))
            <span class="form-text"><strong>{{ $errors->first('email') }}</strong></span>
        @endif
        @if (isset($sysOpts["user-email-optional"]) && $sysOpts["user-email-optional"] == 'On')
            * Currently, you will only be able reset a lost password with an email address.
        @endif
        <div id="emailWarning" style="display: none;"></div>
    </div>
    <div class="nodeHalfGap"></div>
</div>

<div class="nodeAnchor"><a id="n002" name="n002"></a></div>
<div id="node002" class="nodeWrap">
    <div class="nodeHalfGap"></div>
    <div id="node002" class="nodeWrap{{ ((isset($errors) && $errors->has('password')) ? 'Error' : '') }}">
        <div id="nLabel002" class="nPrompt"><label for="password">
            Password: <span class="red">*required, 8 character minimum</span>
        </label></div>
        <div class="relDiv w100"><div id="passStrng" class="red"></div></div>
        <div class="nFld" style="margin-top: 20px;">
            <input id="password" name="password" value="" type="password" class="form-control">
            @if (isset($errors) && $errors->has('password'))
                <span class="form-text"><strong>{{ $errors->first('password') }}</strong></span>
            @endif
        </div>
    </div>
    <div class="nodeHalfGap"></div>
</div>

<div class="nodeAnchor"><a id="n003" name="n003"></a></div>
<div id="node003" class="nodeWrap">
    <div class="nodeHalfGap"></div>
    <div id="node003" class="nodeWrap">
        <div id="nLabel003" class="nPrompt"><label for="password-confirm">
            Confirm Password: <span class="red">*required</span>
        </label></div>
        <div class="nFld" style="margin-top: 20px;">
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control"
                 value="">
        </div>
    </div>
    <div class="nodeHalfGap"></div>
</div>

@if ($GLOBALS["SL"]->sysHas('volunteers') && (!isset($midSurvRedir) || trim($midSurvRedir) == ''))
    <label><input type="checkbox" name="newVolunteer" id="newVolunteerID" value="1"
        @if ($request->has('volunteer')) CHECKED @endif > Volunteer</label>
@endif

@if (!isset($midSurvBack) || trim($midSurvBack) == '')
    <center><input type="submit" class="nFormSignupSubBtn btn btn-lg btn-xl btn-primary" value="Sign Up"></center>
@else
    <div id="pageBtns">
        <div id="formErrorMsg"></div>
        <div id="nodeSubBtns" class="nodeSub">
            <input type="submit" class="nFormSignupSubBtn fR btn btn-primary btn-lg" value="Sign Up">
            <a href="{{ $midSurvBack }}" class="fL btn btn-secondary btn-lg" id="nFormBack">Back</a>
            <div class="fC p5"></div>
        </div>
    </div>
    <div class="pageBotGap"></div>
@endif

</div></center></div>

@if (isset($formFooter)) {!! $formFooter !!} @endif

</form>

<script type="text/javascript" src="/survloop/zxcvbn.js"></script>
<script type="text/javascript">
@if ($GLOBALS["SL"]->sysHas('volunteers'))
setTimeout(function() { if (findGetParam('volunteer')) document.getElementById('newVolunteerID').checked=true; }, 50);
@endif
$(document).ready(function(){
    {!! view('vendor.survloop.auth.register-ajax-zxcvbn', [])->render() !!}
});</script>

@endsection
