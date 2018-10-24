<!-- resources/views/vendor/survloop/profile.blade.php -->
<div class="row mT20 mB20">
@if (isset($GLOBALS['SL']->sysOpts['avatar-empty']))
    <div class="col-md-2 col-sm-3 pT20">
        <a href="/profile/{{ urlencode($profileUser->name) }}"
            ><img id="profilePic" class="imgTmb" src="{{ $GLOBALS['SL']->sysOpts['avatar-empty'] }}" border=0 ></a>
    </div>
    <div class="col-md-6 col-sm-9">
@else
    <div class="col-md-8 col-sm-12">
@endif
        <a href="/profile/{{ urlencode($profileUser->name) }}"><h1 class="slBlueDark">{{ $profileUser->name }}</h1></a>
        Member since {{ date('F d, Y', strtotime($profileUser->created_at)) }}
    </div>
    <div class="col-md-4 col-sm-12">
        @if ($canEdit)
            <div class="slCard mT10">
                <div class="row mB10">
                    <div class="col-lg-3 col-md-12 col-sm-3">Email:</div>
                    <div class="col-lg-9 col-md-12 col-sm-9">{{ $profileUser->email }}
                    @if ($profileUser->hasVerifiedEmail())
                        <nobr><span class="slGrey"><i class="fa fa-check-circle-o mL10" aria-hidden="true"></i> 
                            verified</span></nobr>
                    @endif
                    </div>
                </div>
                <div class="row mB10">
                    <div class="col-lg-3 col-md-12 col-sm-3">Roles:</div>
                    <div class="col-lg-9 col-md-12 col-sm-9">{{ $profileUser->listRoles() }}</div>
                </div>
                <a id="hidivBtnChgPass" class="hidivBtn" href="javascript:;">Change Password</a>
            </div>
        @endif
    </div>
</div>
@if ($canEdit)
    @if (Session::has('success')) <div class="alert alert-success">{!! Session::get('success') !!}</div> @endif
    @if (Session::has('failure')) <div class="alert alert-danger">{!!  Session::get('failure') !!}</div> @endif
    @if ($profileUser->id == $uID)
        <div id="hidivChgPass" class="disNon"><div class="jumbotron"><center>
            <div class="taL w66 ovrSho" style="min-width: 420px; height: 260px;">
                {!! view('vendor.survloop.auth.pass-change-form')->render() !!}
            </div>
        </center></div></div>
    @endif
@endif
<div class="p10"></div>
<style> #unfinishedList { display: block; } </style>