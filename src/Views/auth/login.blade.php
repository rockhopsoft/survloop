<!-- resources/views/auth/login.blade.php -->

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
		<span class="gry9">Here you can finish, review, or update your complaint. Volunteers also login here.</span>
	</div>
	<div class="col-md-6 taR pT5">
		<a href="/register" class="btn btn-default">Sign Up To<br />Volunteer</a>
	</div>
</div>

<div class="nodeWrap">
<div class="nPrompt"><label for="emailID">Email:</label></div>
<div class="nFld"><input id="emailID" name="email" value="{{ old('email') }}" type="email" class="form-control"></div>
</div>

<div class="nodeGap"></div>

<div class="nodeWrap">
<div class="nPrompt"><label for="password">Password:</label></div>
<div class="nFld"><input id="password" name="password" value="" type="password" class="form-control"></div>
</div>

<div class="row">
	<div class="col-md-6 taL">
		<div class="nFldRadio pT5"><label for="rememberID">
			<input type="checkbox" name="remember" id="rememberID">
			Remember Me
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