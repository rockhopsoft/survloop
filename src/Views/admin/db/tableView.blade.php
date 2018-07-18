<!-- resources/views/vendor/survloop/admin/db/tableView.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<div class="row">
    <div class="col-md-7">
        <h3 class="mB5"><i class="fa fa-database"></i> Table: {{ $tbl->TblEng }}</h3>
        <h4 class="mB10">{{ $tbl->TblName }} ({{ $tbl->TblAbbr }}) <span class="slGrey">{{ $tbl->TblType }}</span></h4>
        <div class="pB10">{!! $tbl->TblDesc !!}</div>
        @if (trim($tbl->TblNotes) != '') <div class="slGrey pB10"><i>Notes:</i> {!! $tbl->TblNotes !!}</div> @endif
    @if ($rules->isNotEmpty())
        @foreach ($rules as $rule)
            <div class="pB10"><a href="/dashboard/db/bus-rules/edit/{{ $rule->RuleID }}" target="_blank"
                ><i class="fa fa-university"></i> <i>{!! $rule->RuleStatement !!}</i></a></div>
        @endforeach
    @endif
    </div>
    <div class="col-md-5">
    @if (!$isPrint)
        @if (!$dbAllowEdits)
            <a href="/admin/db?print=1" target="_blank" class="btn btn-xs btn-default mR10"
                ><i class="fa fa-print"></i> Print This Overview</a>
        @else
            <div class="row mT20">
                <div class="col-md-6">
                    <a href="/admin/db?print=1" target="_blank" class="btn btn-default disBlo taL"
                        ><i class="fa fa-print"></i> Print This Overview</a>
                </div><div class="col-md-6">
                    <a href="/dashboard/db/table/{{ $tblName }}/edit" class="btn btn-default disBlo taL"
                        ><nobr><i class="fa fa-pencil"></i> Edit Table Properties</nobr></a>
                </div>
            </div>
            <div class="row mT10 mB20">
                <div class="col-md-6">
                    <a href="/dashboard/db/field/{{ $tbl->TblAbbr }}" class="btn btn-default disBlo taL"
                        ><i class="fa fa-plus"></i> Add a New Field</a>
                </div><div class="col-md-6">
                    <a href="/dashboard/db/table/{{ $tblName }}/sort" class="btn btn-default disBlo taL"
                        ><i class="fa fa-sort-amount-asc"></i> Re-Order Fields</a>
                </div>
            </div>
        @endif
    @endif
    </div>
</div>

@if (trim($foreignsFlds) != '')
    <div class="pB10 fPerc80">
        <i class="fa fa-link"></i> {{ sizeof($flds) }} Tables with Foreign Keys Incoming: 
        {!! $foreignsFlds !!}
    </div>
@endif

{!! $basicTblFlds !!}

<div class="adminFootBuff"></div>

@endsection
