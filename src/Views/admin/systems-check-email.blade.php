<!-- resources/views/vendor/survloop/admin/systems-check-email.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h2><i class="fa fa-heartbeat"></i> Check - Email</h2>

<form name="mainPageForm" method="post" action="?testEmail=1&sendTest=1" >
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="text" name="emailTo" value="{{ $user->email }}">
<center><input type="submit" value="Save All Changes" class="btn btn-lg btn-primary" ></center>
<a href="?testEmail=1&sendTest=1" class="btn btn-primary">Send Test Email</a>
</form>


@endsection