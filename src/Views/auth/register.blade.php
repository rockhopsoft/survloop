@extends('vendor.survloop.master')
@section('content')
<div id="ajaxWrap">
<!-- resources/views/vendor/survloop/auth/register.blade.php -->

<form name="mainPageForm" method="POST" action="/register">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" id="isSignupID" name="isSignup" value="1">
<input type="hidden" name="previous" 
    @if (isset($midSurvRedir) && trim($midSurvRedir) != '') 
        value="{{ $midSurvRedir }}"
    @elseif (isset($GLOBALS["SL"]->REQ) && $GLOBALS["SL"]->REQ->has('redir')) 
        value="{{ $GLOBALS["SL"]->REQ->get('redir') }}"
    @elseif (isset($GLOBALS["SL"]->REQ) && $GLOBALS["SL"]->REQ->has('previous')) 
        value="{{ $GLOBALS["SL"]->REQ->get('previous') }}"
    @else 
        value="{{ URL::previous() }}"
    @endif >

<div class="w100 row2" style="padding: 30px 0px 60px 0px;"><center>
<div id="treeWrap" class="treeWrapForm">
    <div class="slCard">

@if (!isset($GLOBALS["SL"]->sysOpts["signup-instruct"])
    || trim($GLOBALS["SL"]->sysOpts["signup-instruct"]) 
        != '<h2 class="mT5 mB0">Create Admin Account</h2>')
    <a href="/login{{ ((isset($GLOBALS["SL"]->REQ) && $GLOBALS["SL"]->REQ->has('nd')) 
        ? '?nd=' . $GLOBALS["SL"]->REQ->get('nd') : '') 
        }}" class="btn btn-secondary pull-right mL20" 
        id="registerLoginLnk">Login</a>
@endif
<div class="nodeAnchor"><a id="n004" name="n004"></a></div>
<div class="nPrompt">
    <h2 class="mT0 mB20">Sign Up</h2>
    @if (isset($GLOBALS["SL"]->sysOpts["midsurv-instruct"]) 
        && trim($GLOBALS["SL"]->sysOpts["midsurv-instruct"]) != '')
        {!! $GLOBALS["SL"]->sysOpts["midsurv-instruct"] !!}
    @elseif (isset($GLOBALS["SL"]->sysOpts["signup-instruct"]) 
        && trim($GLOBALS["SL"]->sysOpts["signup-instruct"]) != '')
        {!! $GLOBALS["SL"]->sysOpts["signup-instruct"] !!}
    @endif
</div>

@if (isset($errorMsg) && trim($errorMsg) != '') 
    <div class="alert alert-danger mT15" role="alert">{!! $errorMsg !!}</div>
@endif
@if (isset($GLOBALS["SL"]->x["registerNotes"]))
    <p>{!! $GLOBALS["SL"]->x["registerNotes"] !!}</p>
@endif

@if (isset($GLOBALS["SL"]->sysOpts["has-usernames"])
    && intVal($GLOBALS["SL"]->sysOpts["has-usernames"]) == 1)
    <div id="node004" class="nodeWrap{{ 
        ((isset($errors) && $errors->has('name')) ? 'Error' : '') }}">
        <div class="nodeHalfGap"></div>
        <div id="nLabel004" class="nPrompt"><label for="nameID">
            Username
            @if (isset($GLOBALS["SL"]->sysOpts["user-name-req"]) 
                && intVal($GLOBALS["SL"]->sysOpts["user-name-req"]) == 1)
                <span class="red">*required</span>
            @endif
        </label></div>
        <div class="nFld">
            <input id="nameID" name="name" value="{{ old('name') }}" 
                type="text" class="form-control">
            @if (isset($errors) && $errors->has('name'))
                <div class="alert alert-danger mT15" role="alert">
                    {{ $errors->first('name') }}
                </div>
            @endif
        </div>
        <div class="nodeHalfGap"></div>
    </div>
@else
    <input type="hidden" id="nameID" name="name" value="" DISABLED >
@endif

<div class="nodeAnchor"><a id="n001" name="n001"></a></div>
<div id="node001" class="nodeWrap{{ 
    ((isset($errors) && $errors->has('email')) ? 'Error' : '') }}">
    <div class="nodeHalfGap"></div>
    <div id="nLabel001" class="nPrompt"><label for="emailID">
        Email
        @if (!isset($GLOBALS["SL"]->sysOpts["user-email-optional"]) 
            || $GLOBALS["SL"]->sysOpts["user-email-optional"] == 'Off')
            <span class="red">*required</span>
        @endif
    </label></div>
    <div class="nFld">
        <input id="emailID" name="email" value="{{ old('email') }}" 
            type="email" class="form-control">
        
        @if (isset($errors) && $errors->has('email'))
            <div class="alert alert-danger mT15" role="alert">
                {{ $errors->first('email') }}
            </div>
        @endif
        @if (isset($GLOBALS["SL"]->sysOpts["user-email-optional"]) && $GLOBALS["SL"]->sysOpts["user-email-optional"] == 'On')
            * Currently, you will only be able reset 
            a lost password with an email address.
        @endif
        <div id="emailWarning" style="display: none;"></div>
    </div>
    <div class="nodeHalfGap"></div>
</div>

<div class="nodeAnchor"><a id="n002" name="n002"></a></div>
<div id="node002" class="nodeWrap">
    <div class="nodeHalfGap"></div>
    <div id="node002" class="nodeWrap{{ 
        ((isset($errors) && $errors->has('password')) ? 'Error' : '') }}">
        <div id="nLabel002" class="nPrompt">
            <label for="password">
                Password 
                <span class="red">*required, 8 character minimum</span>
            </label>
        </div>
        <div class="relDiv w100"><div id="passStrng" class="red"></div></div>
        <div class="nFld">
            <input id="password" name="password" value="" 
                type="password" class="form-control">
            @if (isset($errors) && $errors->has('password'))
                <div class="alert alert-danger mT15" role="alert">
                    {{ $errors->first('password') }}
                </div>
            @endif
        </div>
    </div>
    <div class="nodeHalfGap"></div>
</div>

<div class="nodeAnchor"><a id="n003" name="n003"></a></div>
<div id="node003" class="nodeWrap">
    <div class="nodeHalfGap"></div>
    <div id="node003" class="nodeWrap">
        <div id="nLabel003" class="nPrompt">
            <label for="password-confirm">
                Confirm Password 
                <span class="red">*required</span>
            </label>
        </div>
        <div class="nFld">
            <input id="password_confirmation" name="password_confirmation" 
                type="password" class="form-control" value="">
        </div>
    </div>
    <div class="nodeHalfGap"></div>
</div>

@if (isset($GLOBALS["SL"]) 
    && $GLOBALS["SL"]->sysHas('volunteers') 
    && (!isset($midSurvRedir) || trim($midSurvRedir) == ''))
    <label><input type="checkbox" name="newVolunteer" 
        id="newVolunteerID" value="1"
        @if (isset($GLOBALS["SL"]->REQ) && $GLOBALS["SL"]->REQ->has('volunteer')) CHECKED @endif
        > Volunteer</label>
@endif

@if (!isset($midSurvBack) || trim($midSurvBack) == '')
    <center>
    <div id="loadAnimSignup" class="disBlo">
        <input id="loadAnimBtnSignup" type="submit" 
            class="nFormSignupSubBtn btn btn-lg btn-primary"
            value="Sign Up">
    </div>
    <div id="loadAnimClickedSignup" class="disNon">
        <button class="btn btn-lg btn-primary" type="button" disabled >
            <table border=0 cellpadding=0 cellspacing=0 ><tr>
                <td><span class="spinner-border spinner-border-sm" 
                    role="status" aria-hidden="true"></span></td>
                <td class="pL5"><div class="disIn pT5">Loading...</div></td>
            </tr></table>
        </button>
    </div>
    </center>
@else
    <div id="pageBtns">
        <div id="formErrorMsg"></div>
        <div id="nodeSubBtns" class="nodeSub">
            <input type="submit" value="Sign Up" 
                class="nFormSignupSubBtn fR btn btn-primary btn-lg">
            <a href="{{ $midSurvBack }}" id="nFormBack"
                class="fL btn btn-secondary btn-lg">Back</a>
            <div class="fC p5"></div>
        </div>
    </div>
@endif

    </div>
</div></center></div>

@if (isset($formFooter)) {!! $formFooter !!} @endif

</form>


<style> #main, body { background: {{ $css["color-main-faint"] }}; } </style>

<script type="text/javascript" src="/survloop/zxcvbn.js"></script>
<script type="text/javascript"> $(document).ready(function(){

    setTimeout(function() {
        if (document.getElementById('nondialog')) {
            document.getElementById('nondialog').className = "row2"; 
        }
    }, 10);
@if (isset($GLOBALS["SL"]) && $GLOBALS["SL"]->sysHas('volunteers'))
    setTimeout(function() {
        if (findGetParam('volunteer')) {
            document.getElementById('newVolunteerID').checked=true;
        }
    }, 50);
@endif
    {!! view('vendor.survloop.auth.register-ajax-zxcvbn', [])->render() !!}

    setTimeout(function() { window.location='/register'; }, (45*60000));

}); </script>

</div>
@endsection