<!-- resources/views/vendor/survloop/auth/passwords/email-sent.blade.php -->

@extends('vendor.survloop.master')

@section('content')
<div class="w100 mB30 pB30"><center>
    <div id="treeWrap" class="treeWrapForm">
        <div class="p20"></div>
        <div class="loginTitles">
            <a class="btn btn-secondary pull-right" href="/login" 
                >Login</a>
            <h2 class="mT0">Reset Password</h2>
        </div>
        <div class="alert alert-success mT30">
            You have been sent an email with a link to change your password. 
            (Please check your spam folder if you don't see it.)
        </div>
    </div>
</center></div>
@endsection
