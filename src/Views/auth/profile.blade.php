<!-- resources/views/vendor/survloop/auth/profile.blade.php -->

<?php /* <pre>{!! print_r($GLOBALS['SL']->sysOpts) !!}</pre> */ ?>

<div class="nodeAnchor"><a id="profileBasics" name="profileBasics"></a></div>

<div class="row mT20 mB20">
@if (isset($GLOBALS['SL']->sysOpts['has-avatars']))
    @if (isset($profileUser->id))
        <div class="col-3">
            {!! $profileUser->profileImg() !!}
        </div>
    @endif
    <div class="col-9">
@else
    <div class="col-12">
@endif

        <h2 class="slBlueDark">{{ $profileUser->name }}</h2>
        <p>
        @if ($canEdit)
            {!! $profileUser->email !!}
            @if ($profileUser->hasVerifiedEmail())
                <nobr><span class="slGrey">
                <i class="fa fa-check-circle-o mL10" aria-hidden="true"></i> 
                verified</span></nobr>
            @endif <br />
        @endif
        Member since {{ date('F d, Y', strtotime($profileUser->created_at)) }}<br />
        @if (trim($profileUser->listRoles()) != '')
            {{ $profileUser->listRoles() }}<br />
        @endif
        @if ($canEdit)
            <a id="hidivBtnEditProfile" class="hidivBtn" 
                href="javascript:;">Edit User Info</a>, 
            <a id="hidivBtnEditProfilePic" class="hidivBtn" 
                href="javascript:;">Upload Profile Picture</a>
        @endif
        @if (Auth::user() && Auth::user()->hasRole('administrator|staff'))
            @if ($userActivity != '')
                <br /><a id="hidivBtnActLogs" class="hidivBtn" 
                    href="javascript:;">Activity Logs</a>
            @endif
            @if ($userSess != '')
                <br /><a id="hidivBtnSessLogs" class="hidivBtn" 
                    href="javascript:;">Session Logs</a>
            @endif
            <br /><a href="/dashboard/users">List of All Users</a>
        @endif
        </p>
        @if (Auth::user() && Auth::user()->hasRole('administrator|staff'))
            @if ($userActivity != '')
                <div id="hidivActLogs" class="disNon">
                    <hr>{!! $userActivity !!}
                </div>
            @endif
            @if ($userSess != '')
                <div id="hidivSessLogs" class="disNon">
                    <hr>{!! $userSess !!}
                </div>
            @endif
        @endif
        @if ($canEdit)
            <div id="hidivEditProfile" class="disNon">
                <div class="nodeWrap">
                    <hr>
                    <form name="mainPageForm" method="post" action="/user/{{ 
                        urlencode($profileUser->name) }}?edit=sub#profileBasics">
                    <input id="csrfTok" name="_token" 
                        type="hidden" value="{{ csrf_token() }}">
                    <input type="hidden" name="uID" 
                        value="{{ $profileUser->id }}">
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
                @if ($GLOBALS["SL"]->isAdmin)
                    <div class="nPrompt">Roles:</div>
                    <div class="nFldRadio">
                    @foreach ($profileUser->roles as $i => $role)
                        <input name="roles[]" id="role{{ $i }}" 
                            type="checkbox" value="{{ $role->def_id }}" 
                            @if ($profileUser->hasRole($role->def_subset)) CHECKED @endif autocomplete="off" >
                        <label for="role{{ $i }}">
                            {{ $role->def_value }}
                        </label><br />
                    @endforeach
                    </div>
                    <div class="nodeHalfGap"></div>
                @endif

                    <center><input type="submit" value="Save Changes" 
                        class="btn btn-primary"></center>
                    <div class="nodeHalfGap"></div>

                    <p>
                        To change your password, please 
                        <a href="/logout">logout</a> then use the 
                        <a href="/password/reset">reset password</a> 
                        tool from the login page.
                    </p>

                    <div class="nodeHalfGap"></div>
                    </form>
                </div>
            </div>

            <div id="hidivEditProfilePic" class="disNon">
                <div class="nodeWrap">
                    <hr>
                    <form name="mainPageForm" method="post" 
                        enctype="multipart/form-data" action="/user/{{ 
                            urlencode($profileUser->name) 
                        }}?upload=photo#profileBasics">
                    <input type="hidden" id="csrfTok" name="_token" 
                        value="{{ csrf_token() }}">
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
                    <input type="submit" value="Upload Profile Photo" 
                        class="btn btn-primary">
                    <div class="nodeHalfGap"></div>
                    </form>
                </div>
                <div class="nodeWrap">
                    <hr>
                    <a id="hidivBtnDeletePic" class="hidivBtn btn btn-danger"
                        href="javascript:;">Delete Profile Picture</a>
                    <div id="hidivDeletePic" class="disNon pT15">
                        <p>
                            Are you sure you want to delete this profile picture?
                            <nobr>This cannot be undone.</nobr>
                        </p>
                        <a class="hidivBtn btn btn-danger mR10"
                            href="/user/{{ urlencode($profileUser->name) 
                            }}?delProfPic=1">Yes, Delete</a>
                        <a id="cancelDeletePic" class="btn btn-secondary"
                            href="javascript:;">No, Don't Delete</a>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

<style> #unfinishedList { display: block; } </style>

<script type="text/javascript"> $(document).ready(function(){

$(document).on("click", "#cancelDeletePic", function() {
    $("#hidivDeletePic").slideUp(300);
});

}); </script>
