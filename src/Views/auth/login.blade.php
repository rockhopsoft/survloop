<!-- resources/views/auth/login.blade.php -->

<?php // sorry, not sure how this should be done instead
$surv = new SurvLoop\Controllers\SurvLoop;
$surv->loadLoop(new Illuminate\Http\Request);
$v = $surv->custLoop->v;
?>

@extends('vendor.survloop.master')

@section('content')

<div class="p20"></div>

@if (isset($errorMsg))
    <div class="alert alert-danger" role="alert">{!! $errorMsg !!}</div>
@endif

<center><div class="halfPageWidth">
<form method="POST" action="/login">
{!! csrf_field() !!}
<br />
<div class="row">
    <div class="col-md-6 pB10">
        <h1 class="mT0">Login</h1>
    </div>
    <div class="col-md-6 taR pT5">
        <a href="/register" class="btn btn-default">Sign Up</a>
    </div>
</div>
@if (!isset($GLOBALS['SL']->sysOpts["login-instruct"]) 
    || trim($GLOBALS['SL']->sysOpts["login-instruct"]) != '')
    <div class="nPrompt mB20">{!! $GLOBALS['SL']->sysOpts["login-instruct"] !!}</div>
@endif

<div class="nodeWrap">
    <div class="nPrompt"><label for="emailID">Email:</label></div>
    <div class="nFld">
        <input id="emailID" name="email" value="{{ old('email') }}" type="email" class="form-control fingerTxt">
    </div>
</div>

<div class="nodeGap"></div>

<div class="nodeWrap">
    <div class="nPrompt"><label for="password">Password:</label></div>
    <div class="nFld">
        <input id="password" name="password" value="" type="password" class="form-control fingerTxt">
    </div>
</div>

<div class="row">
    <div class="col-md-6 taL">
        <div class="nFldRadio pT10"><label for="rememberID">
            <input type="checkbox" name="remember" id="rememberID"> Remember Me
        </label></div>
    </div>
    <div class="col-md-6 taR">
        <a href="/password/email">Forgot Password?</a>
    </div>
</div>

<center><input type="submit" class="btn btn-lg btn-primary f32" value="Login"></center>

<div class="nodeGap"></div>
</form>
</div></center>

@endsection