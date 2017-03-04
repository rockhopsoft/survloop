<!-- resources/views/auth/register-node.blade.php -->

<input type="hidden" name="emailBlock" id="emailBlockID" value="1">
<input type="hidden" name="name" id="nameID" value="Session#{{ $coreID }}" >

@if ($anonyLogin)

    <script type="text/javascript">
    function anonymousLogin() {
        document.getElementById('emailID').value='anonymous.{{ $coreID }}@openpolice.org';
        document.getElementById('password').value='{{ $anonyPass }}';
        document.getElementById('password_confirmation').value='{{ $anonyPass }}';
        document.postNode.submit();
        return true;
    }
    setTimeout("anonymousLogin()", 500);
    </script>
    <h2><i class="gry9">Creating a temporary, anonymous account...</i></h2>
    <!-- hiding form from anonymous users or those with unresolved charges -->
    <div class="disNon">
    
@endif

<div id="node001" class="nodeWrap">
    <div id="nLabel001" class="nPrompt">
        <label for="emailID">
            <h2 class="disIn">Email:</h2> <span class="red">*required</span>
        </label>
    </div>
    <div class="nFld">
        <input id="emailID" name="email" value="{{ old('email') }}" type="email" 
            class="form-control{{ $inputMobileCls }}">
    </div>
    
    <div id="emailWarning" class="disNon">
        <div class="alert alert-danger mT20 w100" role="alert">
            <div class="row">
                <div class="col-md-10">
                    <h3>An account was already found with this email address. 
                    Please login to review and/or update your earlier complaint, 
                    or to file a new complaint with this email address.</h3>
                </div>
                <div class="col-md-2 taC">
                    <a href="/login" class="btn btn-lg btn-primary f26 mT20">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="nodeGap"></div>

<div id="node002" class="nodeWrap">
<div id="nLabel002" class="nPrompt">
    <label for="password">
        <h2 class="disIn">Password:</h2> <span class="red">*required, 6 character minimum</span>
    </label>
</div>
<div class="nFld">
    <input id="password" name="password" type="password" class="form-control{{ $inputMobileCls }}">
</div>
</div>
<div class="nodeGap"></div>
<div id="node003" class="nodeWrap">
    <div id="nLabel003" class="nPrompt">
        <label for="password_confirmation">
            <h2 class="disIn">Confirm Password:</h2> <span class="red">*required</span>
        </label>
    </div>
    <div class="nFld">
        <input id="password_confirmation" name="password_confirmation" type="password" 
            class="form-control{{ $inputMobileCls }}">
    </div>
</div>

@if ($anonyLogin)
    </div> <!-- end div hiding form from anonymous users or those with unresolved charges -->
@endif