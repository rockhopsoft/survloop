<!-- resources/views/vendor/survloop/admin/db/fieldEdit.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<div class="container">
<div class="slCard nodeWrap">
@if (isset($fldName) && trim($fldName) != '')
    <h1 class="fL"><i class="fa fa-database"></i>
    Field: {{ $tbl->tbl_abbr }}{{ $fld->fld_name }}</h1>
    <div class="fR taR fPerc125 slGrey">
        {{ $fld->fld_eng }}<br />({{ $fld->fld_type }})
    </div>
    <div class="fC"></div>
@else
    <h1><i class="fa fa-database"></i> Add New Field</h1>
@endif
<a href="/dashboard/db/table/{{ $tbl->tbl_name }}" class="btn btn-secondary">Back To <i>{{ $tbl->tbl_name }}</i></a>
</div>

<form name="fldEdit" method="post" autocomplete="off" 
@if (!isset($fldName) || trim($fldName) == '') 
    action="/dashboard/db/field/{{ $tbl->tbl_abbr }}"
@else action="/dashboard/db/field/{{ $tbl->tbl_abbr }}/{{ $fld->fld_name }}"
@endif
>
<input type="hidden" name="mainPageForm" value="YES">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">

{!! $fullFldSpecs !!}

<div class="slCard nodeWrap">
@if ($fld->fld_spec_type == 'Generic' && $fld->fld_table <= 0)
    <br /><br /><input type="checkbox" style="width: 40px;" 
        id="pushGenericID" name="pushGeneric" value="1"> 
    <label for="pushGenericID">
        <i class="fa fa-retweet"></i> 
        <i>Push Generic Field Changes To All Replicas</i>
    </label><br />
@endif
<br /><br />
<center>
<input type="submit" class="btn btn-lg btn-primary"
    value=" @if (trim($fldName) == '') Add Field @else Save Changes @endif ">
<br /><br /><br /><br />
<input type="checkbox" style="width: 40px;" name="delete" id="deleteID" value="1"> 
<label for="deleteID">Delete Field</label>
<br /><br />
</form></center>

</div>

@if ($GLOBALS['SL']->dbFullSpecs())
    <p>Hover your mouse over setting names for a bit of an explaination. 
    These field specifications are almost completely copied from best practices in Michael J. Hernandez's
    <a href="http://www.amazon.com/gp/product/0321884493/" target="_blank">Database Design for Mere Mortals: 
    A Hands-On Guide to Relational Database Design (3rd Edition)</a>.</p>
    <p>
    Only a handful of these specifications are automatically built into the database exports, so far. 
    For now the rest must be enforced by your custom code.
    </p>
@endif

</div>
@endsection
