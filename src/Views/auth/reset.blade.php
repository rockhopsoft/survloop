<!-- resources/views/OPC/auth/reset.blade.php -->

<?php // sorry, not sure how this should be done instead
$surv = new SurvLoop\Controllers\SurvLoop;
$surv->loadLoop(new Illuminate\Http\Request);
$v = $surv->custLoop->v;
?>

@extends('vendor.survloop.master')

@section('content')
<center><div class="halfPageWidth">
<form method="POST" action="/password/reset">
{!! csrf_field() !!}
<input type="hidden" name="token" value="{{ $token }}">
<br />
<h1>Reset Password</h1>

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
        <input id="emailID" name="email" value="{{ old('email') }}" type="email" class="form-control fingerTxt">
    </div>
</div>

<div class="nodeGap"></div>

<div class="nodeWrap">
<div class="nPrompt"><label for="password">Password:</label></div>
<div class="nFld"><input id="password" name="password" type="password" class="form-control fingerTxt"></div>
</div>

<div class="nodeGap"></div>

<div class="nodeWrap">
    <div class="nPrompt"><label for="password_confirmation">Confirm Password:</label></div>
    <div class="nFld">
        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control fingerTxt">
    </div>
</div>

<center><input type="submit" class="nFormBtnSub" style="font-size: 150%; float: none;" value="Reset Password"></center>

<div class="nodeGap"></div>
</form>
</div></center>
@endsection
