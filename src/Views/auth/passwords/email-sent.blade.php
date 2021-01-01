<?php
$sysDefs = new RockHopSoft\Survloop\Controllers\SystemDefinitions;
$css = $sysDefs->loadCss();
?>@extends('vendor.survloop.master')
@section('content')
<div id="ajaxWrap">
<!-- resources/views/vendor/survloop/auth/passwords/email-sent.blade.php -->

<div class="w100 row2" style="padding: 30px 0px 60px 0px;"><center>
    <div id="treeWrap" class="treeWrapForm">
        <div class="slCard">

            <a href="/login" id="registerLoginLnk"
                class="btn btn-secondary pull-right mL20">Login</a>
            <div class="nPrompt">
                <h2 class="mT0">Reset Password</h2>
                <p>
                    <div class="alert alert-success mT30">
                        You have been sent an email with a link to change your password. 
                        (Please check your spam folder if you don't see it.)
                    </div>
                </p>
            </div>

        </div>
    </div>
</center></div>

<style> #main, body { background: {{ $css["color-main-faint"] }}; } </style>

</div>
@endsection