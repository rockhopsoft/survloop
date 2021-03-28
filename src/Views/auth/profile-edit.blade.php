<!-- resources/views/vendor/survloop/auth/profile-edit.blade.php -->

<div class="nodeAnchor"><a id="profileSecurity" name="profileSecurity"></a></div>

<h4>Manage Security</h4>

    <p>
        To change your password, please
        <a href="/logout">logout</a> then use the
        <a href="/forgot-password">reset password</a>
        tool from the login page.
    </p>

@if ((isset(Auth::user()->two_factor_secret)
        && trim(Auth::user()->two_factor_secret) != '')
    || session('status') == 'two-factor-authentication-enabled')
    <div class="alert alert-success fade in alert-dismissible show"
        style="padding: 10px 15px;">
        <h4 class="m0"><i class="fa fa-lock mR5" aria-hidden="true"></i>
        Two-Factor Authentication (2FA) <nobr>is enabled.</nobr></h4>
    </div>
@else
    <div class="alert alert-danger fade in alert-dismissible show"
        style="padding: 10px 15px;">
        <h4 class="m0"><i class="fa fa-unlock-alt mR5" aria-hidden="true"></i>
        Two-Factor Authentication (2FA) <nobr>is not enabled.</nobr></h4>
    </div>
    <p>
        Two-factor authentication is also known as
        two-step verification or multi-factor authentication (MFA).
        It is widely used to add a layer of security when using
        online accounts like this website.
    </p>
    <form id="enable2faForm" method="post"
        action="{{ url('user/two-factor-authentication') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <a id="btnEnable2fa" class="btn btn-primary btn-lg"
        href="javascript:;"><i class="fa fa-lock mR5" aria-hidden="true"></i>
        Enable Two-Factor Authentication</a>
    </form>
@endif

@if (((isset(Auth::user()->two_factor_secret)
            && trim(Auth::user()->two_factor_secret) != '')
        || session('status') == 'two-factor-authentication-enabled')
    && $profileUser->id == Auth::user()->id)
    <b>2FA Instructions:</b>
    <p>
        Scan the QR Code below with your phone
        using an authenticator app like the
        <a href="https://duo.com/product/multi-factor-authentication-mfa/duo-mobile-app"
            target="_blank">Duo Mobile (Android & iOS)</a>
        or
        <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2"
            target="_blank">Google Authenticator (Android)</a>
        (<a href="https://apps.apple.com/us/app/google-authenticator/id388497605"
            target="_blank">iOS</a>).
        And you must save your 2FA recovery codes in a
        secure location in case you lose your phone.
    </p>
    <p class="slRedDark">
        <i class="fa fa-exclamation-triangle mR5" aria-hidden="true"></i>
        <b>Before logging out...</b><br />
        If you do not enable an authenticator app,
        and do not save your recovery codes,
        then you will be locked out of your account.
    </p>

    <div class="row mT30 mB30">
        <div class="col-6">
            <div class="pB15"><b>QR Code:</b></div>
            {!! Auth::user()->twoFactorQrCodeSvg() !!}
        </div>
        <div class="col-6">
            <div class="pB15"><b>Recovery Codes:</b></div>
            <p>
            @foreach (json_decode(decrypt(Auth::user()->two_factor_recovery_codes, true)) as $code)
                {{ trim($code) }}<br />
            @endforeach
            </p>
        </div>
    </div>


    <form id="disable2faForm" method="post"
        action="{{ url('user/two-factor-authentication') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    @method('DELETE')
    <a id="btnDisable2fa" class="btn btn-danger btn-sm mT30"
        href="javascript:;"><i class="fa fa-unlock-alt mR5" aria-hidden="true"></i>
        Disable Two-Factor Authentication</a>
    </form>
@endif






<p><br /><br /></p>
<p><hr></p>
<div class="nodeAnchor"><a id="profileEdits" name="profileEdits"></a></div>
<p><br /></p>


<h4>Manage Profile</h4>

<div class="nodeWrap">
    <form name="mainPageForm" method="post" action="/user/{{
        urlencode($profileUser->name) }}/manage?edit=sub#profileBasics">
    <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="uID"
        value="{{ $profileUser->id }}">

    <div class="row">
    @if (Auth::user()->hasRole('administrator'))
        <div class="col-8">
    @else
        <div class="col-12">
    @endif
            <div class="nPrompt">
                <label for="nameID">Username:</label>
            </div>
            <div class="nFld">
                <input name="name" id="nameID" class="form-control"
                    type="text" value="{{ $profileUser->name }}">
            </div>
            <div class="nodeHalfGap"></div>
            <div class="nPrompt">
                <label for="emailID">Email:</label>
            </div>
            <div class="nFld">
                <input name="email" id="emailID"
                    value="{{ $profileUser->email }}"
                    type="email" class="form-control">
            </div>
            <div class="nodeHalfGap"></div>
            <div id="profileEditInfoExtra"></div>
    @if (Auth::user()->hasRole('administrator'))
        </div>
        <div class="col-4">
            <div class="nPrompt">Roles:</div>
            <div class="nFldRadio">
            @foreach ($profileUser->roles as $i => $role)
                <input name="roles[]" id="role{{ $i }}"
                    type="checkbox" value="{{ $role->def_id }}"
                    @if ($profileUser->hasRole($role->def_subset))
                        CHECKED
                    @endif
                    autocomplete="off" >
                <label for="role{{ $i }}">
                    {{ $role->def_value }}
                </label><br />
            @endforeach
            </div>
            <div class="nodeHalfGap"></div>
    @endif
        </div>
    </div>

    <input type="submit" value="Save Changes" class="btn btn-primary">
    <div class="nodeHalfGap"></div>

    </form>
</div>

<div class="nodeWrap">
    <hr>
    <form name="mainPageForm" method="post"
        enctype="multipart/form-data" action="/user/{{
            urlencode($profileUser->name)
        }}?upload=photo#profileBasics">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="uID"
        value="{{ $profileUser->id }}">

    <div class="nPrompt">
        <label for="profilePhotoUpID">
            <b>New Profile Photo</b>
        </label>
        <div>{!! $picInstruct !!}</div>
    </div>
    <div class="nFld">
        <input type="file" name="profilePhotoUp" id="profilePhotoUpID"
            class="form-control" style="border: 1px #CCC solid;"
            {!! $GLOBALS["SL"]->tabInd() !!} >
    </div>
    <div class="nodeHalfGap"></div>
    <a id="hidivBtnDeletePic" href="javascript:;"
        class="hidivBtn btn btn-danger pull-right"
        >Delete Existing Picture</a>
    <input type="submit" value="Upload Profile Photo"
        class="btn btn-primary">
    <div class="nodeHalfGap"></div>
    </form>
</div>

<div class="nodeWrap">
    <div id="hidivDeletePic" class="disNon pT15">
        <p>
            Are you sure you want to delete this profile picture?
            <nobr>This cannot be undone.</nobr>
        </p>
        <div class="row">
            <div class="col-6">
                <a id="cancelDeletePic" class="btn btn-secondary"
                    href="javascript:;">No, Don't Delete</a>
            </div>
            <div class="col-6">
                <a class="hidivBtn btn btn-danger mR10"
                    href="/user/{{ urlencode($profileUser->name)
                    }}?delProfPic=1">Yes, Delete</a>
            </div>
        </div>
    </div>
</div>


<p><br /></p>
<p><br /></p>
<p><br /></p>
<p><br /></p>

<style>
#skinnySurv, #skinnySurv.treeWrapForm { padding: 30px; }
</style>

<script type="text/javascript"> $(document).ready(function(){

$(document).on("click", "#cancelDeletePic", function() {
    $("#hidivDeletePic").slideUp(300);
});
$(document).on("click", "#btnEnable2fa", function() {
    if (document.getElementById("enable2faForm")) {
        document.getElementById("enable2faForm").submit();
    }
});
$(document).on("click", "#btnDisable2fa", function() {
    if (document.getElementById("disable2faForm")) {
        document.getElementById("disable2faForm").submit();
    }
});

}); </script>
