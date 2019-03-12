<!-- resources/views/vendor/survloop/admin/db/overview.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">

<div class="slCard nodeWrap">
    <div class="row">
        <div class="col-md-9">
            <h2><span class="slBlueDark"><i class="fa fa-database"></i> 
                {{ $GLOBALS['SL']->dbRow->DbName }}</span></h2>
            @if (isset($GLOBALS['SL']->dbRow->DbMission) && trim($GLOBALS['SL']->dbRow->DbMission) != '')
                <b>Mission:</b> {!! $GLOBALS['SL']->dbRow->DbMission !!}<br />
            @endif
            <div class="mT20"><h2>Database Overview of <nobr>{!! strip_tags($dbStats) !!}</nobr></h2></div>
        </div>
@if (!$isPrint)
        <div class="col-md-3">
            <a href="/dashboard/db?print=1" target="_blank" class="btn btn-secondary btn-block mB10 taL"
                ><i class="fa fa-print"></i> Print This Overview</a>
            <a href="/dashboard/db/addTable" class="btn btn-secondary btn-block mB10 taL"
                ><i class="fa fa-plus"></i> Add a New Table</a>
            <a href="/dashboard/db/sortTable" class="btn btn-secondary btn-block mB10 taL"
                ><i class="fa fa-sort-amount-asc"></i> Re-Order Tables</a>
        </div>
@endif
    </div>
    <i class="slGrey">Table Plain English Name, Description, Data Type, Database Name (Abbreviation), Notes</i>
</div>

@forelse ($groupTbls as $group => $tbls)
    <div class="slCard nodeWrap">
        <h3 class="mT0">{{ $group }}</h3><hr>
        @forelse ($tbls as $tbl)
            <div class="pB20">
                <div class="pB10">
                    <h3 class="disIn"><a href="/dashboard/db/table/{{ $tbl->TblName }}">{{ $tbl->TblEng }}</a></h3>
                    <span class="slGrey mL10">{{ $tbl->TblName }} ({{ $tbl->TblAbbr }}) {{ $tbl->TblType }}</span>
                </div>
                <p>{{ $tbl->TblDesc }}</p>
                @if (isset($tbl->TblNotes) && trim($tbl->TblNotes) != '')
                    <p><span class="slGrey">{{ $tbl->TblNotes }}</span></p>
                @endif
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
