<!-- resources/views/vendor/survloop/admin/db/tableSort.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<div class="container">
    <a href="/dashboard/db/all" 
        class="btn btn-secondary btn-sm pull-right"
        >All Database Details</a>

    <h3>
        <span class="slBlueDark"><i class="fa fa-database"></i> 
        {{ $GLOBALS['SL']->dbRow->db_name }}</span>:
        Sorting Database Tables
    </h3>
    <p>
        Drag database tables into the desired order.
    </p>

    {!! $sortable !!}

</div>

<div class="pB30"></div>

@endsection
