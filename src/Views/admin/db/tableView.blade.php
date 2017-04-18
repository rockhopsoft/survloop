<!-- resources/views/vendor/survloop/admin/db/tableView.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h3><i class="fa fa-database"></i> Table: {{ $tbl->TblEng }}</h3>
<h4 class="pull-right">{{ $tbl->TblName }} ({{ $tbl->TblAbbr }}) {{ $tbl->TblType }}</h4>
<div class="fC"></div>

@if (!$isPrint)
    <a href="/admin/db?print=1" target="_blank" class="btn btn-xs btn-default mR10"
        ><i class="fa fa-print"></i> Print This Overview</a>
    @if ($dbAllowEdits)
        <a href="/dashboard/db/table/{{ $tblName }}/edit" class="btn btn-xs btn-default mR10"
            ><nobr><i class="fa fa-pencil"></i> Edit Table Properties</nobr></a>
        <a href="/dashboard/db/field/{{ $tbl->TblAbbr }}" class="btn btn-xs btn-default mR10"
            ><i class="fa fa-plus"></i> Add a New Field</a>
        <a href="/dashboard/db/table/{{ $tblName }}/sort" class="btn btn-xs btn-default"
            ><i class="fa fa-sort-amount-asc"></i> Re-Order Fields</a>
    @endif
@endif

<div class="clearfix p5"></div>

<div class="pB10">{!! $tbl->TblDesc !!}</div>
@if (trim($tbl->TblNotes) != '')
    <div class="pB10"><i>Notes: {!! $tbl->TblNotes !!}</i></div>
@endif
@if ($rules && sizeof($rules) > 0)
    @foreach ($rules as $rule)
        <div class="pB10">
            <a href="/dashboard/db/bus-rules/edit/{{ $rule->RuleID }}" target="_blank"><i class="fa fa-university"></i> 
                <i>{!! $rule->RuleStatement !!}</i></a>
        </div>
    @endforeach
@endif
@if (trim($foreignsFlds) != '')
    <div class="pB10">
        <i class="fa fa-link"></i> {{ sizeof($flds) }} 
        Tables with Foreign Keys Incoming: {!! $foreignsFlds !!}
    </div>
@endif

{!! $basicTblFlds !!}

<div class="adminFootBuff"></div>

@endsection
