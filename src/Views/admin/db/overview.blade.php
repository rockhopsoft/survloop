<!-- resources/views/vendor/survloop/admin/db/overview.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS["DB"]->dbRow->DbName }}</span>:
    Database Overview 
    <nobr><span class="f14">({!! strip_tags($dbStats) !!})</span></nobr>
</h1>

@if (!$isPrint)
    <a href="/dashboard/db?print=1" target="_blank" class="btn btn-xs btn-default mR10"><i class="fa fa-print"></i> Print This Overview</a>
    <a href="/dashboard/db/addTable" class="btn btn-xs btn-default mR10"><i class="fa fa-plus"></i> Add a New Table</a>
    <a href="/dashboard/db/sortTable" class="btn btn-xs btn-default mR10"><i class="fa fa-sort-amount-asc"></i> Re-Order Tables</a>
    <a href="/dashboard/db/fieldDescs" class="btn btn-xs btn-default mR10"><i class="fa fa-pencil"></i> Field Descriptions</a>
    <a href="/dashboard/db/fieldXML" class="btn btn-xs btn-default mR10"><i class="fa fa-pencil"></i> Field XML Settings</a>
@endif

<div class="pL10 pT10 fPerc125">
    <i>Mission:</i><br />
    {!! $GLOBALS["DB"]->dbRow->DbMission !!}
</div>

<div class="row pT10 pB10">
    <div class="col-md-4 pL20">
        <i>Table Name (Abbreviation), Data Type</i>
    </div>
    <div class="col-md-8">
        <i>Table Description, <span class="gry6">Notes</span></i>
    </div>
</div>

@forelse ($groupTbls as $group => $tbls)
    <div class="panel panel-info">
        <div class="panel-heading"><h3 class="panel-title">{{ $group }}</h3></div>
        <div class="panel-body">
            @forelse ($tbls as $tbl)
                <div class="row pB20 mB20 pT5">
                    <div class="col-md-4">
                        <a href="/dashboard/db/table/{{ $tbl->TblName }}" class="f20"><b>{{ $tbl->TblEng }}</b></a><br />
                        <span class="gry6">{{ $tbl->TblName }} ({{ $tbl->TblAbbr }})</span><br />
                        <span class="gry9"><i>{{ $tbl->TblType }}</i></span>
                    </div>
                    <div class="col-md-8 f16 gry6">
                        {{ $tbl->TblDesc }}
                        <span class="gry9 fPerc80"><i>{{ $tbl->TblNotes }}</i></span>
                    </div>
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
