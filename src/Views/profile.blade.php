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
            <table class="table mT20" >
            <tr><td>Email:</td><td>{{ $profileUser->email }}
                @if ($profileUser->hasVerifiedEmail())
                    <nobr><span class="slGrey"><i class="fa fa-check-circle-o mL10" aria-hidden="true"></i> 
                        verified</span></nobr>
                @endif
            </td></tr>
            <tr><td>Roles:</td><td>{{ $profileUser->listRoles() }}</td></tr>
            </table>
        @endif
    </div>
</div>
<style> #unfinishedList { display: block; } </style>