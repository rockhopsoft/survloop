<!-- resources/views/vendor/survloop/admin/user-manage.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h1><i class="fa fa-users"></i> Manage User Privileges</h1>

<div class="well">
    <b>Adding A User:</b> Use the public volunteer sign up form 
    (<a href="/register" target="_blank">/register</a>, while logged out, easiest in a separate browser) 
    to first create the new user. Then reload this page and change their privileges here as needed.
</div>

<table class="table table-striped">
<form name="mainPageForm" action="/dashboard/users" method="post">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">

<?php $cnt = 0; ?>
@foreach ($printVoluns as $userSet)
    @forelse ($userSet as $volun)
        @if ($cnt%15 == 0)
            <tr>
                <th>Name</th>
                <th>Email <span class="slGrey mL10">
                    [<i class="fa fa-check-circle-o" aria-hidden="true"></i> verified]</span></th>
                <th class="taC">Volunteer</th>
                <th class="taC">Partners</th>
                <th class="taC">Staff</th>
                <th class="taC">Databaser</th>
                <th class="taC">Admin</th>
            </tr>
        @endif
        <?php $cnt++; ?>
        <tr>
            <td><b>{!! $volun->printUsername(true, '/dashboard/users/') !!}</b></td>
            <td><a href="mailto:{{ $volun->email }}">{{ str_replace('@', ' @', $volun->email) }}</a>
                @if ($volun->hasVerifiedEmail())
                    <span class="slGrey"><i class="fa fa-check-circle-o mL10" aria-hidden="true"></i></span>
                @endif
            </td>
            <td class="taC"><input type="checkbox" name="user{{ $volun->id }}[]" 
                value="volunteer" @if ($volun->hasRole('volunteer')) CHECKED @endif ></td>
            <td class="taC"><input type="checkbox" name="user{{ $volun->id }}[]" 
                value="partner" {{ $disableAdmin }} @if ($volun->hasRole('partner')) CHECKED @endif ></td>
            <td class="taC"><input type="checkbox" name="user{{ $volun->id }}[]" 
                value="staff" {{ $disableAdmin }} @if ($volun->hasRole('staff')) CHECKED @endif ></td>
            <td class="taC"><input type="checkbox" name="user{{ $volun->id }}[]" 
                value="databaser" {{ $disableAdmin }} @if ($volun->hasRole('databaser')) CHECKED @endif ></td>
            <td class="taC"><input type="checkbox" name="user{{ $volun->id }}[]" 
                value="administrator" {{ $disableAdmin }} @if ($volun->hasRole('administrator')) CHECKED @endif ></td>
        </tr>
    @empty
    @endforelse
@endforeach

</table><br />
<center><input type="submit" value=" Save All Changes " class="btn btn-lg btn-primary f30"></center>
</form>

<div class="adminFootBuff"></div>

@endsection