<!-- resources/views/vendor/survloop/admin/db/tableView.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">

<div class="slCard nodeWrap">
<div class="row">
    <div class="col-7">
        <h3 class="disIn"><i class="fa fa-database"></i> Table: {{ $tbl->tbl_eng }}</h3>
        <span class="mL10 slGrey">{{ $tbl->tbl_name }} ({{ $tbl->tbl_abbr }}) {{ $tbl->tbl_type }}</span>
        <div class="pT20 pB20">{!! $tbl->tbl_desc !!}</div>
        @if (trim($tbl->tbl_notes) != '') 
            <div class="slGrey pB20"><i>Notes:</i> {!! $tbl->tbl_notes !!}</div>
        @endif
    @if ($rules->isNotEmpty())
        @foreach ($rules as $rule)
            <div class="pB10">
                <a href="/dashboard/db/bus-rules/edit/{{ $rule->rule_id }}" target="_blank"
                ><i class="fa fa-university"></i> <i>{!! $rule->rule_statement !!}</i></a>
            </div>
        @endforeach
    @endif
    </div>
    <div class="col-5">
    @if (!$isPrint)
        @if (!$dbAllowEdits)
            <a href="/admin/db?print=1" target="_blank" 
                class="btn btn-sm btn-secondary btn-block"
                ><i class="fa fa-print"></i> Print This Overview</a>
        @else
            <a href="/admin/db?print=1" target="_blank" 
                class="btn btn-secondary btn-block"
                ><i class="fa fa-print"></i> Print This Overview</a>
            <a href="/dashboard/db/table/{{ $tblName }}/edit" 
                class="btn btn-secondary btn-block"
                ><i class="fa fa-pencil"></i> Edit Table Properties</a>
            <a href="/dashboard/db/field/{{ $tbl->tbl_abbr }}" 
                class="btn btn-secondary btn-block"
                ><i class="fa fa-plus"></i> Add a New Field</a>
            <a href="/dashboard/db/table/{{ $tblName }}/sort" 
                class="btn btn-secondary btn-block"
                ><i class="fa fa-sort-amount-asc"></i> Re-Order Fields</a>
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

</div></div>

@endsection
