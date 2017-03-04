<!-- resources/views/vendor/survloop/admin/db/fieldEdit.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

@if (isset($fldName) && trim($fldName) != '')
    <h1 class="fL"><i class="fa fa-database"></i> Field: {{ $tbl->TblAbbr }}{{ $fld->FldName }}</h1>
    <div class="fR taR pT20 f20 gry6">
        {{ $fld->FldEng }}<br />({{ $fld->FldType }})</i>
    </div>
    <div class="fC"></div>
@else
    <h1><i class="fa fa-database"></i> Add New Field</h1>
@endif
<a href="/dashboard/db/table/{{ $tbl->TblName }}" class="btn btn-default">Back To <i>{{ $tbl->TblName }}</i></a>
<div class="p10"></div>

<form name="fldEdit" method="post" autocomplete="off" 
@if (!isset($fldName) || trim($fldName) == '') action="/dashboard/db/field/{{ $tbl->TblAbbr }}"
@else action="/dashboard/db/field/{{ $tbl->TblAbbr }}/{{ $fld->FldName }}"
@endif
>
<input type="hidden" name="fldEditForm" value="YES">
<input type="hidden" name="_token" value="{{ csrf_token() }}">

{!! $fullFldSpecs !!}

@if ($fld->FldSpecType == 'Generic' && $fld->FldTable <= 0)
    <br /><br /><input type="checkbox" style="width: 40px;" id="pushGenericID" name="pushGeneric" value="1"> 
    <label for="pushGenericID"><span class="f16"><i class="fa fa-retweet"></i> <i>Push Generic Field Changes To All Replicas</i></span></label><br />
@endif
<br /><br />
<center>
<input type="submit" value=" @if (trim($fldName) == '') Add Field @else Save Changes @endif " class="btn btn-lg btn-primary f30">
<br /><br /><br /><br />
<input type="checkbox" style="width: 40px;" name="delete" id="deleteID" value="1"> 
<label for="deleteID">Delete Field</label>
<br /><br />
</form></center>

@if ($GLOBALS['SL']->dbFullSpecs())
    <div class="jumbotron">
        <p>Hover your mouse over setting names for a bit of an explaination. 
        These field specifications are almost completely copied from best practices in Michael J. Hernandez's
        <a href="http://www.amazon.com/gp/product/0321884493/" target="_blank">Database Design for Mere Mortals: 
        A Hands-On Guide to Relational Database Design (3rd Edition)</a>.</p>
        <p>
        Only a handful of these specifications are automatically built into the database exports, so far. 
        For now the rest must be enforced by your custom code.
        </p>
    </div>
@endif

@endsection
