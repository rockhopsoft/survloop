<!-- resources/views/vendor/survloop/auth/profile-stats.blade.php -->

<div class="pT30 pB30 w100">
    <a href="?refresh=1" class="pull-right btn btn-secondary btn-sm"
        ><i class="fa fa-refresh" aria-hidden="true"></i> Refresh</a>
    <h2>User Statistics:
        <a href="/user/{{ urlencode($profileUser->name) }}"
            >{{ $profileUser->name }}</a>
    </h2>
</div>

@if ($customStats != '')
    <div class="mT30 w100">
        {!! $customStats !!}
    </div>
@endif

@if ($userSess != '')
    <div class="mT30 w100">
        {!! $GLOBALS["SL"]->printAccordian('Session Logs', $userSess) !!}
    </div>
@endif

@if ($userActivity != '')
    <div class="mT30 w100">
        {!! $GLOBALS["SL"]->printAccordian('Activity Logs', $userActivity) !!}
    </div>
@endif

