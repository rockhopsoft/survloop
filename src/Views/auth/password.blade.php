<!-- resources/views/vendor/survloop/auth/password.blade.php -->

@extends('vendor.survloop.master')

@section('content')
<center><div class="halfPageWidth">
<form method="POST" action="/password/email">
    {!! csrf_field() !!}
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
<div class="nFld"><input id="emailID" name="email" value="{{ old('email') }}" type="email" class="form-control"></div>
</div>

<div class="nodeGap"></div>

<center><input type="submit" class="nFormBtnSub" style="font-size: 150%; float: none;" value="Send Password Reset Link"></center>

<div class="nodeGap"></div>
</form>
</div></center>
@endsection
