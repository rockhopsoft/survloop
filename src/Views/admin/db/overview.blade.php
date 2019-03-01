<!-- resources/views/vendor/survloop/admin/db/overview.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<div class="container">
<div class="slCard nodeWrap">
<h2><span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>: Database Overview</h2>

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
<i class="slGrey">Table Plain English Name, Description, Data Type, Database Name (Abbreviation), Notes</i>
</div>

@forelse ($groupTbls as $group => $tbls)
    <div class="slCard nodeWrap">
        <h3 class="mT0">{{ $group }}</h3><hr>
        @forelse ($tbls as $tbl)
            <div class="pB20">
                <h4 class="mB0"><a href="/dashboard/db/table/{{ $tbl->TblName }}">{{ $tbl->TblEng }}</a></h4>
                <p>
                <span class="slGrey">{{ $tbl->TblName }} ({{ $tbl->TblAbbr }}) {{ $tbl->TblType }}</span><br />
                {{ $tbl->TblDesc }}
                @if (isset($tbl->TblNotes) && trim($tbl->TblNotes) != '')
                    <br /><span class="slGrey">{{ $tbl->TblNotes }}</span>
                @endif
                </p>
            </div>
        @empty
        No tables in group.
        @endforelse
    </div>
@empty
    No tables yet.
@endforelse

{!! view('vendor.survloop.admin.db.acknowledgments') !!}

</div>
@endsection
