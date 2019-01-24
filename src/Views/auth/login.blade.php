@extends('vendor.survloop.master')
@section('content')
<!-- resources/views/auth/login.blade.php -->
<form name="mainPageForm" method="POST" action="/login">
<input type="hidden" id="isLoginID" name="isLogin" value="1">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="previous" 
    @if (isset($midSurvRedir) && trim($midSurvRedir) != '') value="{{ $midSurvRedir }}"
    @elseif ($GLOBALS['SL']->REQ->has('redir')) value="{{ $GLOBALS['SL']->REQ->get('redir') }}"
    @elseif ($GLOBALS['SL']->REQ->has('previous')) value="{{ $GLOBALS['SL']->REQ->get('previous') }}"
    @else value="{{ URL::previous() }}"
    @endif >

<div class="w100"><center><div id="treeWrap" class="treeWrapForm">

<div class="p20"></div>

<a href="/register{{ (($GLOBALS['SL']->REQ->has('nd')) ? '?nd=' . $GLOBALS['SL']->REQ->get('nd') : '') 
    }}" class="btn btn-secondary pull-right mL20">Sign Up</a>
<div class="nodeAnchor"><a id="n004" name="n004"></a></div>
<div class="nPrompt">
    <h1 class="mT0">Login</h1>
    @if (isset($sysOpts["midsurv-instruct"]) && trim($sysOpts["midsurv-instruct"]) != '')
        {!! $sysOpts["midsurv-instruct"] !!}
    @elseif (isset($sysOpts["login-instruct"]) && trim($sysOpts["login-instruct"]) != '')
        {!! $sysOpts["login-instruct"] !!}
    @endif
</div>

@if (isset($errorMsg) && trim($errorMsg) != '')
    <div class="alert alert-danger" role="alert">{!! $errorMsg !!}</div>
@endif
@if (isset($GLOBALS['SL']->REQ) && $GLOBALS['SL']->REQ->has('error') && trim($GLOBALS['SL']->REQ->get('error')) != '')
    <div class="alert alert-danger" role="alert">{!! $GLOBALS['SL']->REQ->get('error') !!}</div>
@endif

<div id="node004" class="nodeWrap">
    <div class="nodeHalfGap"></div>
    <div id="nLabel004" class="nPrompt"><label for="emailID">
        Username or Email: <span class="red">*required</span>
    </label></div>
    <div class="nFld" style="margin-top: 20px;">
        <input id="emailID" name="email" value="{{ old('email') }}" type="text" class="form-control">
        @if ($errors->has('email'))
            <span class="form-text"><strong>{{ $errors->first('email') }}</strong></span>
        @endif
    </div>
    <div class="nodeHalfGap"></div>
</div>


<div id="node003" class="nodeWrap">
    <div class="nodeHalfGap"></div>
    <div id="nLabel003" class="nPrompt"><label for="password">
        Password: <span class="red">*required</span>
    </label></div>
    <div class="nFld" style="margin-top: 20px;">
        <input id="password" name="password" value="" type="password" class="form-control">
        @if ($errors->has('password'))
            <span class="form-text"><strong>{{ $errors->first('password') }}</strong></span>
        @endif
    </div>
    <div class="nodeHalfGap"></div>
</div>

<div class="nFldRadio fL"><label for="rememberID">
    <input type="checkbox" name="remember" id="rememberID"> Remember Me
</label></div>
<a href="/password/reset" class="fR">Forgot your username or password?</a>
<div class="fC pB20"></div>

@if (!isset($midSurvBack) || trim($midSurvBack) == '')
    <center><input type="submit" class="btn btn-xl btn-primary" value="Login"></center>
@else
    <div id="pageBtns">
        <div id="formErrorMsg"></div>
        <div id="nodeSubBtns" class="nodeSub">
            <input type="submit" class="fR btn btn-primary btn-lg" value="Login">
            <a href="{{ $midSurvBack }}" class="fL btn btn-secondary btn-lg" id="nFormBack">Back</a>
            <div class="fC p5"></div>
        </div>
    </div>
    <div class="pageBotGap"></div>
@endif

</div></center></div>

@if (isset($formFooter)) {!! $formFooter !!} @endif

</form>
@endsection