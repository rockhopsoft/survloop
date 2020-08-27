<!-- resources/views/vendor/survloop/admin/db/defSort.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h2>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->db_name }}</span>:
    Sorting Defintion Set
</h2>

<a href="/dashboard/db/all" class="btn btn-secondary mR10">All Database Details</a>
<a href="/dashboard/db/definitions" class="btn btn-secondary mR10">All Definitions</a>

{!! $sortable !!}

<div class="adminFootBuff"></div>

@endsection
