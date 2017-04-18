<!-- resources/views/auth/register-node.blade.php -->

<input type="hidden" name="emailBlock" id="emailBlockID" value="1">
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
        <div id="nLabel004" class="nPrompt">
            <label for="nameID">
                Username
                @if ($GLOBALS["SL"]->sysOpts["user-name-optional"] == 'Off')
                    <span class="red">*required</span>
                @endif
            </label>
        </div>
        <div class="nFld mT0">
            <input id="nameID" name="name" value="{{ old('name') }}" type="text" class="form-control input-lg">
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
    <div class="nFld mT0">
        <input id="emailID" name="email" value="{{ old('email') }}" type="email" class="form-control input-lg">
    </div>
    
    <div id="emailWarning" class="disNon">
        <div class="alert alert-danger mT20 w100" role="alert">
            <div class="row">
                <div class="col-md-8">
                    <h5>An account was already found with this email address. 
                    Please login to review and/or update your earlier complaint, 
                    or to file a new complaint with this email address.</h5>
                </div>
                <div class="col-md-4 taC">
                    <a href="/login" class="btn btn-lg btn-primary mT20">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="nodeGap"></div>

<div id="node002" class="nodeWrap">
<div id="nLabel002" class="nPrompt">
    <label for="password">
        Password <span class="red">*required, 6 character minimum</span>
    </label>
</div>
<div class="nFld">
    <input id="password" name="password" type="password" class="form-control input-lg">
</div>
</div>
<div class="nodeGap"></div>
<div id="node003" class="nodeWrap">
    <div id="nLabel003" class="nPrompt">
        <label for="password_confirmation">
            Confirm Password <span class="red">*required</span>
        </label>
    </div>
    <div class="nFld mT0">
        <input id="password_confirmation" name="password_confirmation" type="password" 
            class="form-control input-lg">
    </div>
</div>

@if ($anonyLogin)
    </div> <!-- end div hiding form from anonymous users or those with unresolved charges -->
@endif