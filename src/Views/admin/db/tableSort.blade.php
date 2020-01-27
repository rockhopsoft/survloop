<!-- resources/views/vendor/survloop/admin/db/tableSort.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<div class="container">
<div class="slCard nodeWrap">
<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->db_name }}</span>:
    Sorting Database Tables
</h1>

<a href="/dashboard/db/all" class="btn btn-secondary mR10">All Database Details</a>

{!! $sortable !!}

</div>
</div>

@endsection
