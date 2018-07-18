<!-- resources/views/vendor/survloop/admin/user-emailing.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h1><i class="fa fa-users"></i> Email Users</h1>

<table class="table table-striped">

@foreach ($printVoluns as $i => $userSet)
    <tr><th>
        @if ($i == 0) Administrators
        @elseif ($i == 1) Databasers
        @elseif ($i == 2) Staff
        @elseif ($i == 3) Partners
        @elseif ($i == 4) Volunteers
        @endif
    </th><td>
    @forelse ($userSet as $usr)
        <a href="mailto:{{ $usr->email }}">{{ $usr->email }}</a>, 
    @empty
    @endforelse
    </td></tr>
@endforeach
</table>

<div class="adminFootBuff"></div>

@endsection