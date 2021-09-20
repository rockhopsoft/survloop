<!-- resources/views/vendor/survloop/auth/profile.blade.php -->

<?php /* <pre>{!! print_r($GLOBALS['SL']->sysOpts) !!}</pre> */ ?>

<div class="nodeAnchor"><a id="profileBasics" name="profileBasics"></a></div>

<div class="row mT20 mB20">
@if (isset($GLOBALS['SL']->sysOpts['has-avatars']) && isset($profileUser->id))
    <div class="col-lg-3">
        {!! $profileUser->profileImg() !!}
    </div>
    @if (!$isEditPage)
        <div class="col-lg-5">
    @else
        <div class="col-lg-8">
    @endif
@else
    @if (!$isEditPage)
        <div class="col-lg-8">
    @else
        <div class="col-lg-12">
    @endif
@endif
        <a href="/user/{{ urlencode($profileUser->name) }}" class="slBlueDark urlWrap"
            ><h3 class="slBlueDark">{{ $profileUser->name }}</h3></a>
        <p>
    @if ($canEdit)
        <nobr>{!! $profileUser->email !!}
        @if ($profileUser->hasVerifiedEmail())
            <span class="slGrey">
            <i class="fa fa-check-circle-o mL5" aria-hidden="true"></i>
            verified</span>
        @endif
        </nobr><br />
        @if (isset($profileUser->two_factor_secret)
                && trim($profileUser->two_factor_secret) != '')
            <i class="fa fa-lock mR3" aria-hidden="true"></i> 2FA Enabled<br />
        @endif
    @endif
        Member since {{ date('F d, Y', strtotime($profileUser->created_at)) }}<br />
        @if (trim($profileUser->listRoles()) != '')
            {{ $profileUser->listRoles() }}<br />
        @endif
        </p>

    </div>
@if (!$isEditPage && Auth::user())
    <div class="col-lg-4">
        <a class="btn btn-secondary btn-sm btn-block taL mB5"
            @if ($profileUser->id == Auth::user()->id)
                href="/my-profile/manage"
            @else
                href="/user/{{ urlencode($profileUser->name) }}/manage"
            @endif
            ><i class="fa fa-pencil mR3" aria-hidden="true"></i> Edit Settings</a>
    @if (Auth::user()->hasRole('administrator|staff') && !$isEditPage)
        <a href="/user/{{ urlencode($profileUser->name) }}/stats"
            class="btn btn-secondary btn-sm btn-block taL mB5"
            ><nobr><i class="fa fa-line-chart mR3" aria-hidden="true"></i>
            User Statistics</nobr></a>
        <a href="/dashboard/users"
            class="btn btn-secondary btn-sm btn-block taL mB5"
            ><nobr><i class="fa fa-long-arrow-left mR3" aria-hidden="true"></i>
            List of All Users</nobr></a>
    @endif
    </div>
@endif
</div>

<style>
#unfinishedList { display: block; }
</style>

<?php /*
@if (Auth::user() && Auth::user()->hasRole('administrator|staff'))
<script type="text/javascript"> $(document).ready(function(){
setTimeout(function() {
    $("#leftAdmMenu").load("/ajadm/load-dash-layout");
}, 1);
</script>
@endif
*/ ?>
