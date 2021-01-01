<?php // sorry, not sure how this should be done instead
$surv = new RockHopSoft\Survloop\Controllers\Survloop;
$surv->loadLoop(new Illuminate\Http\Request);
$v = $surv->custLoop->v;
$sysDefs = new RockHopSoft\Survloop\Controllers\SystemDefinitions;
$css = $sysDefs->loadCss();
?>@extends('vendor.survloop.master')
@section('content')
<div id="ajaxWrap">
<!-- resources/views/vendor/survloop/auth/passwords/reset.blade.php -->

<form class="form-horizontal" role="form" method="POST" 
    action="{{ url('/password/reset') }}">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="token" value="{{ $token }}">

<div class="w100 row2" style="padding: 30px 0px 60px 0px;"><center>
<div id="treeWrap" class="treeWrapForm">
    <div class="slCard">

        <a class="btn btn-secondary pull-right mL20" 
            href="/login" id="registerLoginLnk">Login</a>

        <div class="nPrompt">
            <h2 class="mT0">Reset Password</h2>
            <p>
                Please create a new password for your account.
            </p>
        </div>

    @if (session('status'))
        <div class="alert alert-success mT20" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
        <div class="alert alert-danger mT20" role="alert">
            {{ $error }}
        </div>
        @endforeach
    @endif

    <div class="nodeAnchor"><a id="n001" name="n001"></a></div>
    <div id="node001" class="nodeWrap">
        <div class="nodeHalfGap"></div>
        <div id="nLabel001" class="nPrompt">
            <label for="emailID">
                Email
                <span class="red">*required</span>
            </label>
        </div>
        <div class="nFld">
            <input id="emailID" name="email" type="text" 
                value="{{ old('email') }}" 
                class="form-control" autofocus >
        </div>
        <div class="nodeGap"></div>
    </div>

    <div class="nodeAnchor"><a id="n002" name="n002"></a></div>
    <div id="node002" class="nodeWrap">
        <div class="nodeHalfGap"></div>
        <div id="node002" class="nPrompt">
            <label for="password">
                Password
                <span class="red">*required</span>
            </label>
        </div>
        <div class="nFld">
            <input id="password" name="password" 
                type="password" class="form-control">
        </div>
        <div class="nodeGap"></div>
    </div>

    <div class="nodeAnchor"><a id="n003" name="n003"></a></div>
    <div id="node003" class="nodeWrap">
        <div class="nodeGap"></div>
        <div id="node003" class="nPrompt">
            <label for="password-confirm">
                Confirm Password
                <span class="red">*required</span>
            </label>
        </div>
        <div class="nFld">
            <input id="password_confirmation" name="password_confirmation" 
                type="password" class="form-control">
        </div>
        <div class="nodeGap"></div>
    </div>

    <div id="formErrorMsg"></div>

    <center>
    <div id="loadAnimResetPass" class="disBlo">
        <input id="loadAnimBtnResetPass" type="submit" 
            class="btn btn-lg btn-primary"
            value="Reset Password">
    </div>
    <div id="loadAnimClickedResetPass" class="disNon">
        <button class="btn btn-lg btn-primary" type="button" disabled >
            <table border=0 cellpadding=0 cellspacing=0 ><tr>
                <td><span class="spinner-border spinner-border-sm" 
                    role="status" aria-hidden="true"></span></td>
                <td class="pL5"><div class="disIn pT5">Loading...</div></td>
            </tr></table>
        </button>
    </div>
    </center>

    </div>
</div></center></div>

</form>

<style> #main, body { background: {{ $css["color-main-faint"] }}; } </style>

</div>
@endsection