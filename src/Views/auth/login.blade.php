<?php // sorry, not sure how this should be done instead
$surv = new SurvLoop\Controllers\SurvLoop;
$surv->loadLoop(new Illuminate\Http\Request);
$v = $surv->custLoop->v;
?>@extends('vendor.survloop.master')

@section('content')
<!-- resources/views/auth/login.blade.php -->
<form method="POST" action="/login">
{!! csrf_field() !!}

<div class="w100"><center><div id="treeWrap" class="treeWrapForm">

<div class="p10"></div>

<div class="row loginTitles">
    <div class="col-md-6">
        <h1 class="mT0">Login</h1>
    </div>
    <div class="col-md-6 taR pT5">
        <a href="/register" class="btn btn-default">Sign Up</a>
    </div>
</div>

@if (!isset($GLOBALS['SL']->sysOpts["login-instruct"]) 
    || trim($GLOBALS['SL']->sysOpts["login-instruct"]) != '')
    <h4 class="mB20">{!! $GLOBALS['SL']->sysOpts["login-instruct"] !!}</h4>
@endif

@if (isset($errorMsg))
    <div class="alert alert-danger" role="alert">{!! $errorMsg !!}</div>
@endif
@if (isset($GLOBALS['SL']->REQ) && $GLOBALS['SL']->REQ->has('error'))
    <div class="alert alert-danger" role="alert">{!! $GLOBALS['SL']->REQ->get('error') !!}a sdfasdf</div>
@endif

<div class="nodeWrap form-group">
    <div class="nPrompt"><label for="emailID">Username or Email: <span class="red">*required</span></label></div>
    <div class="nFld mT0">
        <input id="emailID" name="email" value="{{ old('email') }}" type="text" class="form-control">
        @if ($errors->has('email'))
            <span class="help-block">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="nodeHalfGap"></div>

<div class="nodeWrap form-group mB20">                                                                                            
    <div class="nPrompt"><label for="password">Password: <span class="red">*required</span></label></div>
    <div class="nFld mT0">
        <input id="password" name="password" value="" type="password" class="form-control">
        @if ($errors->has('password'))
            <span class="help-block">
                <strong>{{ $errors->first('password') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="nodeHalfGap"></div>

<div class="nFldRadio fL"><label for="rememberID">
    <input type="checkbox" name="remember" id="rememberID"> Remember Me
</label></div>
<a href="/password/reset" class="fR">Forgot your username or password?</a>
<div class="fC"></div>

<center><input type="submit" class="btn btn-xl btn-primary mT20" value="Login"></center>

<div class="nodeHalfGap"></div>

</div></center></div>

</form>
@endsection