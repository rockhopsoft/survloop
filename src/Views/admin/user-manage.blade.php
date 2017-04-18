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
<form name="manageUserRoles" action="/dashboard/users" method="post">
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<tr>
    <th>Name</th>
    <th class="taC">Volunteer</th>
    <th class="taC">Evaluators</th>
    <th class="taC">Brancher</th>
    <th class="taC">Databaser</th>
    <th class="taC">Admin</th>
    <th>Email</th>
</tr>

@foreach ($printVoluns as $userSet)
    @forelse ($userSet as $volun)
        <tr>
            <td><b>{!! $volun->printUsername(true, '/dashboard/users/') !!}</b></td>
            <td class="taC"><input type="checkbox" name="user{{ $volun->id }}[]" 
                value="volunteer" @if ($volun->hasRole('volunteer')) CHECKED @endif ></td>
            <td class="taC"><input type="checkbox" name="user{{ $volun->id }}[]" 
                value="staff" {{ $disableAdmin }} @if ($volun->hasRole('staff')) CHECKED @endif ></td>
            <td class="taC"><input type="checkbox" name="user{{ $volun->id }}[]" 
                value="brancher" {{ $disableAdmin }} @if ($volun->hasRole('brancher')) CHECKED @endif ></td>
            <td class="taC"><input type="checkbox" name="user{{ $volun->id }}[]" 
                value="databaser" {{ $disableAdmin }} @if ($volun->hasRole('databaser')) CHECKED @endif ></td>
            <td class="taC"><input type="checkbox" name="user{{ $volun->id }}[]" 
                value="administrator" {{ $disableAdmin }} @if ($volun->hasRole('administrator')) CHECKED @endif ></td>
            <td><a href="mailto:{{ $volun->email }}">{{ str_replace('@', ' @', $volun->email) }}</a></td>
        </tr>
    @empty
    @endforelse
@endforeach

</table><br />
<center><input type="submit" value=" Save All Changes " class="btn btn-lg btn-primary f30"></center>
</form>

<div class="adminFootBuff"></div>

@endsection