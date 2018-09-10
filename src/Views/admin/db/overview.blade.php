<!-- resources/views/vendor/survloop/admin/db/overview.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h1><span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>: Database Overview</h1>

<div class="pB10">
    @if (isset($GLOBALS['SL']->dbRow->DbMission) && trim($GLOBALS['SL']->dbRow->DbMission) != '')
        <b>Mission:</b> {!! $GLOBALS['SL']->dbRow->DbMission !!}<br />
    @endif
    <nobr><span class="fPerc133">{!! strip_tags($dbStats) !!}</span></nobr>
@if (!$isPrint)
    <a href="/dashboard/db?print=1" target="_blank" class="btn btn-sm btn-secondary mL10 mTn5"
        ><i class="fa fa-print"></i> Print This Overview</a>
    <a href="/dashboard/db/addTable" class="btn btn-sm btn-secondary mL10 mTn5"
        ><i class="fa fa-plus"></i> Add a New Table</a>
    <a href="/dashboard/db/sortTable" class="btn btn-sm btn-secondary mL10 mTn5"
        ><i class="fa fa-sort-amount-asc"></i> Re-Order Tables</a>
@endif
</div>

<div class="fC pT20 pL15">
    <i class="slGrey">Table English Name, Description, Data Type, Technical Name (Abbreviation), Notes</i>
</div>

@forelse ($groupTbls as $group => $tbls)
    <div class="card">
        <div class="card-header"><h3>{{ $group }}</h3></div>
        <div class="card-body">
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
