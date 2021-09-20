<!-- resources/views/auth/login.blade.php -->

<form name="mainPageForm" method="POST" action="/login">
<input type="hidden" id="isLoginID" name="isLogin" value="1">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="previous" value="{{ $loginRedir }}">

<div class="w100 row2" style="padding: 30px 0px 60px 0px;"><center>
    <div id="treeWrap" class="treeWrapForm">
        <div class="slCard">

            <a href="/register{{ (($GLOBALS['SL']->REQ->has('nd')) 
                ? '?nd=' . $GLOBALS['SL']->REQ->get('nd') : '') 
                }}" class="btn btn-secondary pull-right mL20"
                >Sign Up</a>
            <div class="nodeAnchor"><a id="n004" name="n004"></a></div>
            <div class="nPrompt">
                <h2 class="mT0 mB20">Login</h2>
                @if (isset($sysOpts["midsurv-instruct"]) 
                    && trim($sysOpts["midsurv-instruct"]) != '')
                    {!! $sysOpts["midsurv-instruct"] !!}
                @elseif (isset($sysOpts["login-instruct"]) 
                    && trim($sysOpts["login-instruct"]) != '')
                    {!! $sysOpts["login-instruct"] !!}
                @endif
            </div>

            @if (isset($errorMsg) && trim($errorMsg) != '')
                <div class="alert alert-danger mT15" role="alert">{!! 
                    $errorMsg 
                !!}</div>
            @endif
            @if (isset($GLOBALS['SL']->REQ) 
                && $GLOBALS['SL']->REQ->has('error') 
                && trim($GLOBALS['SL']->REQ->get('error')) != '')
                <div class="alert alert-danger mT15" role="alert">{!! 
                    $GLOBALS['SL']->REQ->get('error') 
                !!}</div>
            @endif

            <div id="node004" class="nodeWrap">
                <div class="nodeHalfGap"></div>
                <div id="nLabel004" class="nPrompt">
                    <label for="emailID">
                    @if (isset($GLOBALS["SL"]->sysOpts["has-usernames"])
                        && intVal($GLOBALS["SL"]->sysOpts["has-usernames"]) == 1)
                        Username or Email
                    @else
                        Email 
                    @endif
                        <span class="red">*required</span>
                    </label>
                </div>
                <div class="nFld">
                    <input id="emailID" name="email" type="text"
                        value="{{ old('email') }}" class="form-control">
                    @if ($errors->has('email'))
                        <div class="alert alert-danger mT15" role="alert">
                            {{ $errors->first('email') }}
                        </div>
                    @endif
                </div>
                <div class="nodeHalfGap"></div>
            </div>

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
                        <div class="alert alert-danger mT15" role="alert">
                            {{ $errors->first('password') }}
                        </div>
                    @endif
                </div>
                <div class="nodeHalfGap"></div>
            </div>

            <div class="nFldRadio fL">
                <label for="rememberID">
                    <input name="remember" id="rememberID" 
                        type="checkbox" > Remember Me
                </label>
            </div>
            <a href="/forgot-password" class="fR"
                >Forgot your username or password?</a>
            <div class="fC pB20"></div>

        @if (!isset($midSurvBack) || trim($midSurvBack) == '')
            <center>{!!
                $GLOBALS["SL"]->printLoadAnimBtn('Login', 'Login')
            !!}</center>
        @else
            <div id="pageBtns">
                <div id="formErrorMsg"></div>
                <div id="nodeSubBtns" class="nodeSub">
                    <input type="submit" value="Login" 
                        class="fR btn btn-primary btn-lg" 
                        id="loginSubmitBtn">
                    <a href="{{ $midSurvBack }}" id="nFormBack" 
                        class="fL btn btn-secondary btn-lg">Back</a>
                    <div class="fC p5"></div>
                </div>
            </div>
            <div class="pageBotGap"></div>
        @endif

        </div>
    </div></center>
</div>

@if (isset($formFooter)) {!! $formFooter !!} @endif

</form>

<style>
#main, body { background: {{ $css["color-main-faint"] }}; }
</style>
<script type="text/javascript"> $(document).ready(function(){

function loginFocus() {
    if (document.getElementById("emailID")) {
        document.getElementById("emailID").focus();
    }
}
setTimeout(function() { loginFocus(); }, 10);

function reloadLogin() {
    var redir = '/login?ajax=1';
    console.log(redir);
    $("#ajaxWrap").load(redir);

}
setTimeout(function() { reloadLogin(); }, (30*60000));

}); </script>

</div>