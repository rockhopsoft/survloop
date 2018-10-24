<?php // sorry, not sure how this should be done instead
$surv = new SurvLoop\Controllers\SurvLoop;
$surv->loadLoop(new Illuminate\Http\Request);
$v = $surv->custLoop->v;
?>@extends('vendor.survloop.master')

@section('content')
<!-- resources/views/vendor/survloop/auth/passwords/email.blade.php -->
<form name="mainPageForm" class="form-horizontal" role="form" method="POST" action="{{ url('/password/email') }}">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">

<div class="w100"><center><div id="treeWrap" class="treeWrapForm">

<div class="p20"></div>

<div class="row loginTitles">
    <div class="col-6">
        <h1 class="mT0">Reset Password</h1>
    </div>
    <div class="col-6 taR pT5">
        <a href="/login" class="btn btn-secondary">Login</a>
    </div>
</div>

<h4 class="">
You will be sent an email with a link to change your password. (Please check your spam folder if you don't see it.)
</h4>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="nodeGap"></div>

<div class="nodeWrap">
    <div class="nPrompt"><h3><label for="emailID">Email:</label></h3></div>
    <div class="nFld">
        <input id="emailID" name="email" value="{{ old('email') }}" type="email" class="form-control form-control-lg">
    </div>
</div>
@if ($errors->has('email'))
    <span class="form-text">
        <strong>{{ $errors->first('email') }}</strong>
    </span>
@endif

<div class="nodeGap"></div>

<center><button type="submit" class="btn btn-xl btn-primary mT20">
    Send Password Reset Link
</button></center>

</div></center></div>
</form>
@endsection
