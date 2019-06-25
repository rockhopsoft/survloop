<!-- resources/views/vendor/survloop/profile.blade.php -->
<div class="row mT20 mB20">
@if (isset($GLOBALS['SL']->sysOpts['avatar-empty']))
    <div class="col-md-2 col-sm-3 pT20">
        <a href="/profile/{{ urlencode($profileUser->name) }}"
            ><img id="profilePic" class="tmbRound" src="{{ $GLOBALS['SL']->sysOpts['avatar-empty'] }}" border=0
                alt="Avatar or Profile Picture for {{ $profileUser->name }}"></a>
    </div>
    <div class="col-md-6 col-sm-9">
@else
    <div class="col-md-8 col-sm-12">
@endif

        <div class="slCard h100">
        <a href="/profile/{{ urlencode($profileUser->name) }}"
            ><h2 class="slBlueDark">{{ $profileUser->name }}</h2></a>
        Member since {{ date('F d, Y', strtotime($profileUser->created_at)) }}
        @if ($canEdit)
            <br /><a id="hidivBtnEditProfile" class="hidivBtn" href="javascript:;"
                ><i class="fa fa-pencil" aria-hidden="true"></i> Edit User Info</a>
        @endif
        @if ($canEdit)
            <div id="hidivEditProfile" class="nodeWrap disNon">
                <hr>
                <form name="mainPageForm" action="/profile/{{ urlencode($profileUser->name) }}?edit=sub" method="post">
                <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="uID" value="{{ $profileUser->id }}">
                <div class="row mT20">
                    <div class="col-md-6">
                        <div class="nPrompt"><label for="nameID">Username:</label></div>
                        <div class="nFld">
                            <input type="text" name="name" id="nameID" value="{{ $profileUser->name }}"
                                class="form-control">
                        </div>
                        <div class="nodeHalfGap mT20"></div>
                        <div class="nPrompt"><label for="emailID">Email:</label></div>
                        <div class="nFld mB10">
                            <input type="email" name="email" id="emailID" value="{{ $profileUser->email }}"
                                class="form-control">
                        </div>
                    </div><div class="col-md-2">
                    </div><div class="col-md-4">
                    @if ($GLOBALS["SL"]->isAdmin)
                        <div class="nPrompt">Roles:</div>
                        <div class="nFldRadio">
                        @foreach ($profileUser->roles as $i => $role)
                            <input type="checkbox" name="roles[]" id="role{{ $i }}" value="{{ $role->DefID }}" 
                                @if ($profileUser->hasRole($role->DefSubset)) CHECKED @endif autocomplete="off" >
                            <label for="role{{ $i }}">{{ $role->DefValue }}</label><br />
                        @endforeach
                        </div>
                    @endif
                    </div>
                </div>
                <div class="nodeHalfGap"></div>
                <center><input type="submit" class="nFormBtnSub btn btn-primary btn-lg" value="Save Changes"></center>
                <div class="nodeHalfGap"></div>
                </form>
            </div>
        @endif
        </div>
    </div>
    <div class="col-md-4 col-sm-12">
        <div class="slCard h100">
            @if ($canEdit)
                <div class="row mB10">
                    <div class="col-lg-3 col-md-12 col-sm-3">Email:</div>
                    <div class="col-lg-9 col-md-12 col-sm-9">
                        {!! str_replace('@', '<div class="disIn mLn5"> </div>@', $profileUser->email) !!}
                    @if ($profileUser->hasVerifiedEmail())
                        <nobr><span class="slGrey">
                            <i class="fa fa-check-circle-o mL10" aria-hidden="true"></i> verified</span></nobr>
                    @endif
                    </div>
                </div>
            @endif
            @if (trim($profileUser->listRoles()) != '')
                <div class="row mB10">
                    <div class="col-lg-3 col-md-12 col-sm-3">Roles:</div>
                    <div class="col-lg-9 col-md-12 col-sm-9">{{ $profileUser->listRoles() }}</div>
                </div>
            @endif
            @if (isset($uID) && $profileUser->id == $uID) <a href="/logout" class="pull-right">Logout</a> @endif
            @if ($canEdit)
                <a id="hidivBtnChgPass" class="hidivBtn" href="javascript:;">Change Password</a>
                <div id="hidivChgPass" class="disNon mT0">
                    <br />Please <a href="/logout">logout</a> then use the <a href="/password/reset">reset password</a>
                    tool from the login page.
                    <?php /*
                    @if (Session::has('success'))
                        <div class="alert alert-success">{!! Session::get('success') !!}</div>
                    @endif
                    @if (Session::has('failure'))
                        <div class="alert alert-danger">{!!  Session::get('failure') !!}</div>
                    @endif
                    <hr>{!! view('vendor.survloop.auth.pass-change-form')->render() !!}
                    */ ?>
                </div>
            @endif
        </div>
    </div>
</div>
<div class="p10"></div>

<style> #unfinishedList { display: block; } </style>