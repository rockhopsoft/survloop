<?php // sorry, not sure how this should be done instead
$surv = new RockHopSoft\Survloop\Controllers\Survloop;
$surv->loadLoop(new Illuminate\Http\Request);
$v = $surv->custLoop->v;
?>@extends('vendor.survloop.master')

@section('content')
<!-- resources/views/vendor/survloop/auth/passwords/reset.blade.php -->
<form class="form-horizontal" role="form" method="POST" 
    action="{{ url('/password/reset') }}">
{{ csrf_field() }}
<input type="hidden" name="token" value="{{ $token }}">

<div class="w100 row2" 
    style="padding: 30px 0px 60px 0px;"><center>
    <div id="treeWrap" class="treeWrapForm">

<div class="p20"></div>

<div class="loginTitles">
    <a href="/login" class="btn btn-secondary pull-right">Login</a>
    <h2 class="mT0">Reset Password</h2>
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
    <div class="nPrompt">
        <label for="emailID">Email:</label>
    </div>
    <div class="nFld">
        <input id="emailID" name="email" type="email" 
            value="{{ $email or old('email') }}" 
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
<div class="nFld">
    <input id="password" name="password" type="password" 
        class="form-control form-control-lg" required >
</div>
</div>
@if ($errors->has('password'))
    <span class="form-text">
        <strong>{{ $errors->first('password') }}</strong>
    </span>
@endif

<div class="nodeGap"></div>

<div class="nodeWrap">
    <div class="nPrompt">
        <label for="password-confirm">Confirm Password:</label>
    </div>
    <div class="nFld">
        <input id="password-confirm" name="password_confirmation" 
            type="password" class="form-control form-control-lg">
    </div>
</div>
@if ($errors->has('password_confirmation'))
    <span class="form-text">
        <strong>{{ $errors->first('password_confirmation') }}</strong>
    </span>
@endif

<center><input type="submit" value="Reset Password"
    class="btn btn-lg btn-primary mT20" ></center>

<div class="nodeGap"></div>

</div></center></div>

</form>
@endsection
