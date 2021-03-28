<!-- resources/views/vendor/survloop/admin/db/overview.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">

<div class="row">
    <div class="col-md-9">
        @if (isset($GLOBALS['SL']->dbRow->db_mission) 
            && trim($GLOBALS['SL']->dbRow->db_mission) != '')
            <h2 class="slBlueDark">
                <i class="fa fa-database mR3"></i> 
                {{ $GLOBALS['SL']->dbRow->db_name }}
            </h2>
            <b>Mission:</b> 
            {!! $GLOBALS['SL']->dbRow->db_mission !!}<br />
        @else
            <h3 class="slBlueDark">
                <i class="fa fa-database mR3"></i> 
                {{ $GLOBALS['SL']->dbRow->db_name }}
                Database Overview
            </h3>
        @endif
        <b><nobr>{!! strip_tags($dbStats) !!}</nobr></b>
    </div>
@if (!$isPrint)
    <div class="col-md-3">
        <a href="/dashboard/db?print=1" target="_blank" 
            class="btn btn-secondary btn-block mB10 taL"
            ><i class="fa fa-print"></i> Print This Overview</a>
        <a href="/dashboard/db/addTable" 
            class="btn btn-secondary btn-block mB10 taL"
            ><i class="fa fa-plus"></i> Add a New Table</a>
        <a href="/dashboard/db/sortTable" 
            class="btn btn-secondary btn-block mB10 taL"
            ><i class="fa fa-sort-amount-asc"></i> Re-Order Tables</a>
    </div>
@endif
</div>
<p class="slGrey">
    <i>Table Plain English Name, Database Name (Abbreviation), 
    Data Type, Description, [Notes]</i>
</p>

@forelse ($groupTbls as $group => $tbls)
    <p><br /></p>
    <div class="w100 brdBotFnt">
        <h3 class="slBlueDark">{{ $group }}</h3>
    </div>
    @forelse ($tbls as $tbl)
        <div class="brdBotFnt pT15 pB15">
            <a href="/dashboard/db/table/{{ $tbl->tbl_name }}"
                class="pull-right btn btn-secondary btn-sm mT5 mL10"
                >Field List</a>
        @if (Auth::user() && Auth::user()->hasRole('administrator'))
            <a href="/dashboard/db/tbl-raw?tbl={{ $tbl->tbl_name }}"
                class="pull-right btn btn-secondary btn-sm mT5 mL10"
                >Raw Data</a>
        @endif
            <h3 class="disIn">{{ $tbl->tbl_eng }}</h3>
            <div class="pB10 mL10 slGrey">
                {{ $tbl->tbl_name }} ({{ $tbl->tbl_abbr }}) {{ $tbl->tbl_type }}
            </div>
            <p>{{ $tbl->tbl_desc }}</p>
            @if (isset($tbl->tbl_notes) && trim($tbl->tbl_notes) != '')
                <p><span class="slGrey">{{ $tbl->tbl_notes }}</span></p>
            @endif
        </div>

    @empty
        No tables in group.
    @endforelse
@empty
    No tables yet.
@endforelse

{!! view('vendor.survloop.admin.db.acknowledgments') !!}

</div>
@endsection
