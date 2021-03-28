<?php
$surv = new RockHopSoft\Survloop\Controllers\Survloop;
$surv->loadLoop(new Illuminate\Http\Request);
$v = $surv->custLoop->v;
$sysDefs = new RockHopSoft\Survloop\Controllers\SystemDefinitions;
$css = $sysDefs->loadCss();
?>@extends('vendor.survloop.master')
@section('content')
<div id="ajaxWrap">
<!-- resources/views/vendor/survloop/auth/password-confirm.blade.php -->

<form name="mainPageForm" method="POST" action="{{ url('user/confirm-password') }}">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" id="isLoginID" name="isLogin" value="1">
<input type="hidden" name="previous" 
    @if (isset($midSurvRedir) && trim($midSurvRedir) != '') 
        value="{{ $midSurvRedir }}"
    @elseif ($GLOBALS['SL']->REQ->has('redir')) 
        value="{{ $GLOBALS['SL']->REQ->get('redir') }}"
    @elseif ($GLOBALS['SL']->REQ->has('previous')) 
        value="{{ $GLOBALS['SL']->REQ->get('previous') }}"
    @else value="{{ URL::previous() }}"
    @endif >

<div class="w100 row2" style="padding: 30px 0px 60px 0px;"><center>
    <div id="treeWrap" class="treeWrapForm">
        <div class="slCard">

            <div class="nodeAnchor"><a id="n004" name="n004"></a></div>
            <div class="nPrompt">
                <h1 class="mT0">Confirm Password</h1>
            </div>

            @if (isset($errorMsg) && trim($errorMsg) != '')
                <div class="alert alert-danger" role="alert">{!! 
                    $errorMsg 
                !!}</div>
            @endif
            @if (isset($GLOBALS['SL']->REQ) 
                && $GLOBALS['SL']->REQ->has('error') 
                && trim($GLOBALS['SL']->REQ->get('error')) != '')
                <div class="alert alert-danger" role="alert">{!! 
                    $GLOBALS['SL']->REQ->get('error') 
                !!}</div>
            @endif

            <div id="node003" class="nodeWrap">
                <div class="nodeHalfGap"></div>
                <div id="nLabel003" class="nPrompt">
                    <label for="password">
                        Password 
                        <span class="red">*required</span>
                    </label>
                </div>
                <div class="nFld">
                    <input id="password" name="password" value="" 
                        type="password" class="form-control">
                    @if ($errors->has('password'))
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first('password') }}
                        </div>
                    @endif
                </div>
                <div class="nodeHalfGap"></div>
            </div>

            <div id="pageBtns">
                <div id="formErrorMsg"></div>
                <div id="nodeSubBtns" class="nodeSub">
                    <input type="submit" value="Confirm" 
                        class="fR btn btn-primary btn-lg" 
                        id="passwordConfirmBtn">
                </div>
            </div>
            <div class="pageBotGap"></div>

        </div>
    </div></center>
</div>

@if (isset($formFooter)) {!! $formFooter !!} @endif

</form>

<style>
#main, body { background: {{ $css["color-main-faint"] }}; }
</style>

</div>
@endsection