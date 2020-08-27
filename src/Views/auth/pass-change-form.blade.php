<!-- resources/views/vendor/survloop/auth/pass-change-form.blade.php -->
<form action="/change-my-password" 
    name="changePass" method="post">
<input type="hidden" id="csrfTok" 
    name="_token" value="{{ csrf_token() }}">

<div class="nodeWrap">
    <div class="nPrompt">
        <label for="old">
            Old Password
        </label>
    </div>
    <div class="nFld">
        <input id="old" type="password" name="old"
            class="form-control passChgSub">
        @if ($errors->has('old'))
            <span class="form-text"><strong>{{ 
                $errors->first('old') 
            }}</strong></span>
        @endif
    </div>
</div>

<div class="nodeHalfGap"></div>

<div class="nodeWrap">
    <div class="nPrompt">
        <label for="password">
            Password
        </label>
    </div>
    <div class="nFld">
        <span id="passStrng" class="mR20 red"></span>
        <input id="password" type="password" name="password"
            class="form-control passChgSub">
        @if ($errors->has('password'))
            <span class="form-text"><strong>{{ 
                $errors->first('password') 
            }}</strong></span>
        @endif
    </div>
</div>

<div class="nodeHalfGap"></div>

<div class="nodeWrap">
    <div class="nPrompt">
        <label for="password-confirm">
            Confirm Password
        </label>
    </div>
    <div class="nFld">
        <input id="password-confirm" type="password" 
            class="form-control passChgSub" 
            name="password_confirmation">
        @if ($errors->has('password_confirmation'))
            <span class="form-text"><strong>{{ 
                $errors->first('password_confirmation') 
            }}</strong></span>
        @endif
    </div>
</div>

<div class="nodeHalfGap"></div>

<div id="passChgErrs" class="red"></div>
<div class="nodeHalfGap"></div>
<center><a id="passChgSub" href="javascript:;"
    class="btn btn-primary btn-lg"
    >Change Password</a></center>
<div class="nodeHalfGap"></div>
</form>

<script async defer type="text/javascript" src="{{ 
    $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/zxcvbn.js">
</script>
<script async defer type="text/javascript"> $(document).ready(function(){
    {!! view('vendor.survloop.auth.register-ajax-zxcvbn', [])->render() !!}
    function subPassChg() {
        var errs = '';
        if (document.getElementById('old') && document.getElementById('password') && document.getElementById('password-confirm') && document.getElementById('passChgErrs')) {
            var pass0 = document.getElementById('old').value;
            var pass1 = document.getElementById('password').value;
            var pass2 = document.getElementById('password-confirm').value;
            if (pass0.trim() == '' || pass1.trim() == '' || pass2.trim() == '') {
                if (pass0.trim() == '') errs += 'Please provide old password.<br />';
                if (pass1.trim() == '') errs += 'Please provide new password.<br />';
                if (pass2.trim() == '') errs += 'Please confirm new password.<br />';
            } else if (pass0.length < 8 || pass1.length < 8) {
                if (pass0.length < 8) errs += 'Old password must be at least 8 characters.<br />';
                if (pass1.length < 8) errs += 'New password must be at least 8 characters.<br />';
            } else if (pass1 != pass2) {
                errs += 'New password and confirmation must match.<br />';
            }
            if (errs == '') {
                document.getElementById('passChgErrs').innerHTML='';
                document.changePass.submit();
            } else {
                document.getElementById('passChgErrs').innerHTML=errs;
                return false;
            }
        }
        return true;
    }
    $(document).on("click", "#passChgSub", function() { subPassChg(); });
	$(document).on("keyup", ".passChgSub", function(e) {
        if (e.keyCode == 13) {
            if (e.preventDefault) e.preventDefault(); 
            else e.returnValue = false; 
            subPassChg();
            return false; 
        }
    });
}); </script>