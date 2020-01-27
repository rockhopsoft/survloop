<!-- resources/views/vendor/survloop/profile.blade.php -->
<div class="row mT20 mB20">
@if (isset($GLOBALS['SL']->sysOpts['avatar-empty']))
    <div class="col-3 pT20">
        <a href="/profile/{{ urlencode($profileUser->name) }}"
            ><img id="profilePic" class="tmbRound" 
                src="{{ $GLOBALS['SL']->sysOpts['avatar-empty'] }}" border=0
                alt="Avatar or Profile Picture for {{ $profileUser->name }}"></a>
    </div>
    <div class="col-9">
@else
    <div class="col-12">
@endif

        <h2 class="slBlueDark">{{ $profileUser->name }}'s Profile</h2>
        <p>
        @if ($canEdit)
            Email: {!! $profileUser->email !!}
            @if ($profileUser->hasVerifiedEmail())
                <nobr><span class="slGrey">
                    <i class="fa fa-check-circle-o mL10" aria-hidden="true"></i> verified</span></nobr>
            @endif <br />
        @endif

        Member since {{ date('F d, Y', strtotime($profileUser->created_at)) }}<br />

        @if (trim($profileUser->listRoles()) != '')
            Roles: {{ $profileUser->listRoles() }}<br />
        @endif
        </p>

        @if ($canEdit)
            <p><a id="hidivBtnEditProfile" class="hidivBtn" href="javascript:;"
                >Edit User Info</a></p>
        @endif
        @if ($canEdit)
            <div id="hidivEditProfile" class="disNon">
                <div class="nodeWrap">
                    <hr>
                    <form name="mainPageForm" action="/profile/{{ urlencode($profileUser->name) }}?edit=sub" method="post">
                    <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="uID" 
                        value="{{ $profileUser->id }}">
                    <div class="row mT20">
                        <div class="col-md-6">
                            <div class="nPrompt">
                                <label for="nameID">
                                    Username:
                                </label>
                            </div>
                            <div class="nFld">
                                <input name="name" id="nameID" 
                                    value="{{ $profileUser->name }}"
                                    type="text" class="form-control">
                            </div>
                            <div class="nodeHalfGap mT20"></div>
                            <div class="nPrompt">
                                <label for="emailID">
                                    Email:
                                </label>
                            </div>
                            <div class="nFld mB10">
                                <input name="email" id="emailID" 
                                    value="{{ $profileUser->email }}"
                                    type="email" class="form-control">
                            </div>
                        </div><div class="col-md-2">
                        </div><div class="col-md-4">
                        @if ($GLOBALS["SL"]->isAdmin)
                            <div class="nPrompt">Roles:</div>
                            <div class="nFldRadio">
                            @foreach ($profileUser->roles as $i => $role)
                                <input name="roles[]" id="role{{ $i }}" autocomplete="off" 
                                    type="checkbox" value="{{ $role->def_id }}" 
                                    @if ($profileUser->hasRole($role->def_subset)) CHECKED @endif >
                                <label for="role{{ $i }}">{{ $role->def_value }}</label><br />
                            @endforeach
                            </div>
                        @endif
                        </div>
                    </div>
                    <div class="nodeHalfGap"></div>
                    <center><input type="submit" value="Save Changes" 
                        class="nFormBtnSub btn btn-primary btn-lg"></center>
                    <div class="nodeHalfGap"></div>

                    <p>
                        To change your password, please <a href="/logout">logout</a> then use the 
                        <a href="/password/reset">reset password</a> tool from the login page.
                    </p>

                    <div class="nodeHalfGap"></div>
                    </form>
                </div>
            </div>
        @endif

    </div>
</div>

<style> #unfinishedList { display: block; } </style>