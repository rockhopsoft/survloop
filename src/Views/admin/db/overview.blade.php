<!-- resources/views/vendor/survloop/admin/db/overview.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h2><span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>: Database Overview</h2>

<div class="pB10">
    <b>Mission:</b> {!! $GLOBALS['SL']->dbRow->DbMission !!} ({!! strip_tags($dbStats) !!})
</div>

@if (!$isPrint)
    <a href="/dashboard/db?print=1" target="_blank" class="btn btn-xs btn-default mR10"
        ><i class="fa fa-print"></i> Print This Overview</a>
    <a href="/dashboard/db/addTable" class="btn btn-xs btn-default mR10"
        ><i class="fa fa-plus"></i> Add a New Table</a>
    <a href="/dashboard/db/sortTable" class="btn btn-xs btn-default mR10"
        ><i class="fa fa-sort-amount-asc"></i> Re-Order Tables</a>
    <a href="/dashboard/db/fieldDescs" class="btn btn-xs btn-default mR10"
        ><i class="fa fa-pencil"></i> Field Descriptions</a>
    <a href="/dashboard/db/fieldXML" class="btn btn-xs btn-default mR10"
        ><i class="fa fa-pencil"></i> Field XML Settings</a>
    <a href="/dashboard/db/diagrams" target="_blank" class="btn btn-xs btn-default mR10">Tables Diagrams</a>
    <a href="/dashboard/db/field-matrix" target="_blank" class="btn btn-xs btn-default mR10">Field Matrix</a>
    <a href="/dashboard/db/switch" class="btn btn-xs btn-default mR10">Switch Database</a>
@endif

<div class="fC pT20 pL15">
    <i class="slGrey">Table English Name, Description, Data Type, Technical Name (Abbreviation), Notes</i>
</div>

@forelse ($groupTbls as $group => $tbls)
    <div class="panel panel-info">
        <div class="panel-heading"><h3 class="panel-title">{{ $group }}</h3></div>
        <div class="panel-body">
            @forelse ($tbls as $tbl)
                <div class="pB20">
                    <h3><a href="/dashboard/db/table/{{ $tbl->TblName }}">{{ $tbl->TblEng }}</a></h3>
                    <h4 class="disIn">{{ $tbl->TblDesc }}</h4>
                    <h5 class="disIn slGrey">{{ $tbl->TblType }}, {{ $tbl->TblName }} ({{ $tbl->TblAbbr }})
                    @if (isset($tbl->TblNotes) && trim($tbl->TblNotes) != '') , {{ $tbl->TblNotes }} @endif
                    </h5>
                </div>
            @empty
            No tables in group.
            @endforelse
        </div>
    </div>
@empty
    No tables yet.
@endforelse

{!! view('vendor.survloop.admin.db.acknowledgments') !!}

@endsection
