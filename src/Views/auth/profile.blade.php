<!-- resources/views/vendor/survloop/profile.blade.php -->

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
        </p>
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
                            New Profile Photo (Visible To Public):
                        </label>
                    </div>
                    <div class="nFld">
                        <input type="file" name="profilePhotoUp" id="profilePhotoUpID" 
                            class="form-control" style="border: 1px #CCC solid;"
                            {!! $GLOBALS["SL"]->tabInd() !!} >
                    </div>
                    <div class="nodeHalfGap"></div>
                    <center><input type="submit" value="Upload Profile Photo" 
                        class="btn btn-primary"></center>
                    <div class="nodeHalfGap"></div>
                    </form>
                </div>
            </div>
        @endif

    </div>
</div>

<style> #unfinishedList { display: block; } </style>