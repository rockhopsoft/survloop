<!-- resources/views/vendor/survloop/admin/db/fieldSort.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<div class="container">
<div class="slCard nodeWrap">
<h1><span class="slBlueDark"><i class="fa fa-database"></i> {{ $tblName }}:</span> Sorting Table Fields</h1>

<a href="/dashboard/db/all" class="btn btn-secondary mR10">All Database Details</a>
<a href="/dashboard/db/table/{{ $tblName }}" class="btn btn-secondary mR10">View Table</a>

<div class="mT20">{!! $sortable !!}</div>

</div></div>

@endsection