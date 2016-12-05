<!-- resources/views/vendor/survloop/admin/db/tableSort.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS["DB"]->dbRow->DbName }}</span>:
    Sorting Database Tables
</h1>

<a href="/dashboard/db/all" class="btn btn-default mR10">All Database Details</a>

{!! $sortable !!}

<div class="adminFootBuff"></div>

@endsection
