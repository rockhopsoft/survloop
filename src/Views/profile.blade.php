<!-- resources/views/vendor/survloop/profile.blade.php -->

@extends('vendor.survloop.master')

@section('content')
<h2>{{ $profileUser->name }}: Profile</h2>
<div class="fL"><table border=0 cellpadding=5 cellspacing=0 >
<tr><td>Name:</td><td>{{ $profileUser->name }}</td></tr>
<tr><td>Email:</td><td>{{ $profileUser->email }}</td></tr>
<tr><td>Roles:</td><td>{{ $profileUser->listRoles() }}</td></tr>
<tr><td>Since:</td><td>{{ $profileUser->created_at }}</td></tr>
</table></div>
<div class="fC"></div>

<div class="p20"></div><div class="p20"></div>
@endsection