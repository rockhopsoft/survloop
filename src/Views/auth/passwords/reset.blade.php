<?php // sorry, not sure how this should be done instead
$surv = new SurvLoop\Controllers\SurvLoop;
$surv->loadLoop(new Illuminate\Http\Request);
$v = $surv->custLoop->v;
?>@extends('vendor.survloop.master')

@section('content')
<!-- resources/views/OPC/auth/reset.blade.php -->
<form class="form-horizontal" role="form" method="POST" action="{{ url('/password/reset') }}">
{{ csrf_field() }}
<input type="hidden" name="token" value="{{ $token }}">

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

@if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif

@if (count($errors) > 0)
<ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
</ul>
@endif

<div class="nodeWrap">
    <div class="nPrompt"><label for="emailID">Email:</label></div>
    <div class="nFld">
        <input id="emailID" name="email" value="{{ $email or old('email') }}" type="email" 
            class="form-control form-control-lg" required autofocus >
    </div>
</div>
@if ($errors->has('email'))
    <span class="form-text">
        <strong>{{ $errors->first('email') }}</strong>
    </span>
@endif

<div class="nodeGap"></div>

<div class="nodeWrap">
<div class="nPrompt"><label for="password">Password:</label></div>
<div class="nFld"><input id="password" name="password" type="password" class="form-control form-control-lg" required ></div>
</div>
@if ($errors->has('password'))
    <span class="form-text">
        <strong>{{ $errors->first('password') }}</strong>
    </span>
@endif

<div class="nodeGap"></div>

<div class="nodeWrap">
    <div class="nPrompt"><label for="password-confirm">Confirm Password:</label></div>
    <div class="nFld">
        <input id="password-confirm" name="password_confirmation" type="password" class="form-control form-control-lg">
    </div>
</div>
@if ($errors->has('password_confirmation'))
    <span class="form-text">
        <strong>{{ $errors->first('password_confirmation') }}</strong>
    </span>
@endif

<center><input type="submit" class="btn btn-xl btn-primary mT20" value="Reset Password"></center>

<div class="nodeGap"></div>

</div></center></div>

</form>
@endsection
