<!-- resources/views/vendor/survloop/profile.blade.php -->
<div class="row mT20 mB20">
@if (isset($GLOBALS['SL']->sysOpts['avatar-empty']))
    <div class="col-md-2 pT20">
        <a href="/profile/{{ urlencode($profileUser->name) }}"
            ><img id="profilePic" class="imgTmb" src="{{ $GLOBALS['SL']->sysOpts['avatar-empty'] }}" border=0 ></a>
    </div>
    <div class="col-md-6">
@else
    <div class="col-md-8">
@endif
        <a href="/profile/{{ urlencode($profileUser->name) }}"><h1 class="slBlueDark">{{ $profileUser->name }}</h1></a>
        Member since {{ date('F d, Y', strtotime($profileUser->created_at)) }}
    </div>
    <div class="col-md-4">
        @if ($canEdit)
            <table class="table mT20 mB0" >
            <tr><td>Email:</td><td>{{ $profileUser->email }}
            @if ($profileUser->hasVerifiedEmail())
                <nobr><span class="slGrey"><i class="fa fa-check-circle-o mL10" aria-hidden="true"></i> 
                    verified</span></nobr>
            @endif
            </td></tr>
            <tr><td>Roles:</td><td>{{ $profileUser->listRoles() }}</td></tr>
            </table>
            <a id="hidivBtnChgPass" class="hidivBtn mTn5 mL10" href="javascript:;">Change Password</a>
        @endif
    </div>
</div>
@if ($canEdit)
    @if (Session::has('success')) <div class="alert alert-success">{!! Session::get('success') !!}</div> @endif
    @if (Session::has('failure')) <div class="alert alert-danger">{!! Session::get('failure') !!}</div> @endif
    @if ($profileUser->id == $uID)
        <div id="hidivChgPass" class="disNon"><div class="jumbotron"><center>
            <div class="taL w66 ovrSho" style="min-width: 420px; height: 260px;">
                {!! view('vendor.survloop.auth.pass-change-form')->render() !!}
            </div>
        </center></div></div>
    @else
        
    @endif
@endif
<div class="p20"></div>
<style> #unfinishedList { display: block; } </style>