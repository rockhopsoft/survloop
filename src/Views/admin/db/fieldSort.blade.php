<!-- resources/views/vendor/survloop/admin/db/fieldSort.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h1><i class="fa fa-database"></i> Sorting Table Fields</h1>

<a href="/dashboard/db/all" class="btn btn-default mR10">All Database Details</a>
<a href="/dashboard/db/table/{{ $tblName }}" class="btn btn-default mR10">View Table</a>

{!! $sortable !!}

<div class="adminFootBuff"></div>

@endsection
