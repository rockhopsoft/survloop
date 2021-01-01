<?php
// sorry, not sure how this should be done instead
$surv = new RockHopSoft\Survloop\Controllers\Survloop;
$surv->loadLoop(new Illuminate\Http\Request);
$v = $surv->custLoop->v;
$sysDefs = new RockHopSoft\Survloop\Controllers\SystemDefinitions;
$css = $sysDefs->loadCss();
?>@extends('vendor.survloop.master')
@section('content')
<div id="ajaxWrap">
<!-- resources/views/vendor/survloop/auth/passwords/email.blade.php -->
<form name="mainPageForm" class="form-horizontal" role="form" 
    method="POST" action="{{ url('/password/email') }}">
<input type="hidden" id="csrfTok" name="_token" 
    value="{{ csrf_token() }}">

<div class="w100 row2" style="padding: 30px 0px 60px 0px;"><center>
    <div id="treeWrap" class="treeWrapForm">
        <div class="slCard">

            <a href="/login" id="registerLoginLnk"
                class="btn btn-secondary pull-right mL20">Login</a>
            <div class="nPrompt">
                <h2 class="mT0">Reset Password</h2>
                <p>
                    You will be sent an email with a 
                    link to change your password. 
                    (Please check your spam folder if you don't see it.)
                </p>
            </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @elseif ($GLOBALS["SL"]->REQ->has('sent'))
            <div class="alert alert-success">
                Please check your email for a password reset link.
            </div>
        @endif
        @if ($errors->has('email'))
            <div class="alert alert-danger mT20" role="alert">
                {{ $errors->first('email') }}
            </div>
        @endif

            <div class="nodeAnchor"><a id="n001" name="n001"></a></div>
            <div id="node001" class="nodeWrap">
                <div class="nodeHalfGap"></div>
                <div id="nLabel001" class="nPrompt"><label for="emailID">
                    Email <span class="red">*required</span>
                </label></div>
                <div class="nFld">
                    <input id="emailID" name="email" type="text"
                        value="{{ old('email') }}" class="form-control">
                </div>
                <div class="nodeHalfGap"></div>
            </div>

            <center>{!!
                $GLOBALS["SL"]->printLoadAnimBtn('Send Password Reset Link', 'ResetPassEmail')
            !!}</center>

        </div>
    </div>
</center></div>
</form>

<style> #main, body { background: {{ $css["color-main-faint"] }}; } </style>

</div>
@endsection
