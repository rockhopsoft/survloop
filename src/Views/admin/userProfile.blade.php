<!-- resources/views/vendor/survloop/admin/userProfile.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h1><i class="fa fa-users"></i> User Profile: {!! $userObj->printUsername(true, '/dashboard/volun/user/') !!}</h1>

<a @if ($isAdminList) href="/dashboard/volun/stars" @else href="/volunteer/stars" @endif 
    class="btn btn-sm btn-secondary mR10">Back to List</a>

<div class="nodeAnchor"><a name="recentEdits"></a></div>
<h2>All Department Edits</h2>
<table class="table table-striped" border=0 cellpadding=10 >
    <tr><th>Edit Details</th><th>Department Info</th><th>Internal Affairs</th><th>Civilian Oversight</th></tr>
    {!! $recentEdits !!}
</table>

<div class="adminFootBuff"></div>

@endsection