<!-- resources/views/auth/register-node.blade.php -->

<input type="hidden" name="emailBlock" id="emailBlockID" value="1">
<input type="hidden" name="sessTree" id="sessTreeID" value="{{ $GLOBALS['SL']->sessTree }}">
@if (!isset($GLOBALS["SL"]->sysOpts["user-name-ask"]) || $GLOBALS["SL"]->sysOpts["user-name-ask"] == 'Off')
    <input type="hidden" name="name" id="nameID" value="Session#{{ $coreID }}" >
@endif

@if ($anonyLogin)
    <script type="text/javascript">
    function anonymousLogin() {
        document.getElementById('emailID').value='anonymous.{{ $coreID }}@anonymous.org';
        document.getElementById('password').value='{{ $anonyPass }}';
        document.getElementById('password_confirmation').value='{{ $anonyPass }}';
        document.postNode.submit();
        return true;
    }
    setTimeout("anonymousLogin()", 500);
    </script>
    <h2><i class="slGrey">Creating a temporary, anonymous account...</i></h2>
    <!-- hiding form from anonymous users or those with unresolved charges -->
    <div class="disNon">
@endif

@if (isset($GLOBALS["SL"]->sysOpts["user-name-ask"]) && $GLOBALS["SL"]->sysOpts["user-name-ask"] == 'On')
    <div id="node004" class="nodeWrap">
        <div id="nLabel004" class="nPrompt"><label for="nameID">
            Username
            @if ($GLOBALS["SL"]->sysOpts["user-name-optional"] == 'Off')
                <span class="red">*required</span>
            @endif
        </label></div>
        <div class="nFld">
            <input id="nameID" name="name" value="{{ old('name') }}" type="text" class="form-control">
        </div>
    </div>
    <div class="nodeGap"></div>
@endif

<div id="node001" class="nodeWrap">
    <div id="nLabel001" class="nPrompt">
        <label for="emailID">
            Email
            @if (!isset($GLOBALS["SL"]->sysOpts["user-email-optional"]) 
                || $GLOBALS["SL"]->sysOpts["user-email-optional"] == 'Off')
                <span class="red">*required</span>
            @endif
        </label>
    </div>
    <div class="nFld">
        <input id="emailID" name="email" value="{{ old('email') }}" type="email" class="form-control">
    </div>
    @if (isset($GLOBALS["SL"]->sysOpts["user-email-optional"]) 
        && $GLOBALS["SL"]->sysOpts["user-email-optional"] == 'On')
        * Currently, you will only be able reset a lost password with an email address.
    @endif
    
    <div id="emailWarning" class="disNon">
        <div class="alert alert-danger mT20 w100" role="alert">
            <div class="row">
                <div class="col-8">
                    <h5>The email has already been taken. Please login or create a new account.</h5>
                </div>
                <div class="col-4 taC">
                    <a href="/login" class="btn btn-lg btn-primary mT20">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="nodeGap"></div>

<div id="node002" class="nodeWrap">
    <div id="nLabel002" class="nPrompt"><label for="password">
        Password <span class="red">*required, 8 character minimum</span>
    </label></div>
    <div class="nFld">
        <div class="relDiv w100"><span id="passStrng" class="mL20 red"></span></div>
        <input id="password" name="password" type="password" class="form-control">
    </div>
</div>
<div class="nodeGap"></div>
<div id="node003" class="nodeWrap">
    <div id="nLabel003" class="nPrompt"><label for="password_confirmation">
        Confirm Password <span class="red">*required</span>
    </label></div>
    <div class="nFld">
        <input id="password_confirmation" name="password_confirmation" type="password" 
            class="form-control">
    </div>
</div>

@if ($anonyLogin)
    </div> <!-- end div hiding form from anonymous users or those with unresolved charges -->
@endif

<script type="text/javascript" src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/zxcvbn.js">
</script>
<script type="text/javascript"> $(document).ready(function(){
{!! view('vendor.survloop.auth.register-ajax-zxcvbn', [])->render() !!}
}); </script>