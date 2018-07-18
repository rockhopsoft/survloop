<!-- resources/views/vendor/survloop/admin/systems-check-email.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h2><i class="fa fa-heartbeat"></i> Check Email</h2>

<div class="p20"><br /></div>

<form name="mainPageForm" method="post" action="?testEmail=1&sendTest=1" >
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="text" name="emailTo" value="{{ $user->email }}">
<input type="submit" value="Send Test Email" class="btn btn-primary">
</form>

<div class="p20"><br /></div>

@if (isset($testResults)) {!! $testResults !!} @endif

<div class="p20"><br /></div>

@endsection