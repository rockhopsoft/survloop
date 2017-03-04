<!-- resources/views/vendor/survloop/admin/db/definitions.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

@if ($dbAllowEdits)
    <div class="fR">
        <a href="/dashboard/db/definitions/add" class="btn btn-default"><i class="fa fa-plus-circle"></i> Add a New Definition</a>
    </div>
@endif

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>: Definitions (Value Ranges)
</h1>

<div class="fC"></div>

<p>
    These are collections of value ranges for various database fields, 
    often used as the multi-choice options provided a user for certain questions.
</p>
<div class="row p5">
    <div class="col-md-3 p10"><i>Sets:</i></div>
    <div class="col-md-9 p10"><i>Definition ID#, Values / Options, Description / Notes:</i></div>
</div>


@foreach ($defSets as $subset => $setDefs)
    @if ($setDefs && sizeof($setDefs) > 0)
        <a name="{{ str_replace(' ', '', $subset) }}"></a>
        <div class="row mT20 mB20 row2">
            <div class="col-md-3 pL10" >
                <h1 class="mT0">{{ $subset }}</h1>
                @if ($dbAllowEdits)
                    <a href="/dashboard/db/definitions/add/{{ $subset }}" class="btn btn-default mR10"><i class="fa fa-plus-circle"></i> Add</a>
                    <a href="/dashboard/db/definitions/sort/{!! urlencode($subset) !!}" class="btn btn-default mR10" ><i class="fa fa-sort-amount-asc"></i>Sort</a>
                @endif
            </div>
            <div class="col-md-9" >
            @foreach ($setDefs as $cnt => $setDef)
                <div class="row @if ($cnt%2 > 0) row1 @endif " >
                <div class="col-md-1 p5" >
                @if ($dbAllowEdits)
                    <a href="/dashboard/db/definitions/edit/{{ $setDef->DefID }}" class="p5 btn btn-default">
                    <i class="fa fa-pencil fa-flip-horizontal"></i> {{ $setDef->DefID }}</a>
                @endif
                </div>
                <div class="col-md-8 p10">
                    <h3>{{ $setDef->DefValue }}</h3>
                    @if (trim($setDef->DefDescription) != '') <span class="gry9">{{ $setDef->DefDescription }}</span> @endif
                </div>
                </div>
            @endforeach
            </div>
        </div>
        <div class="clearfix p20" ></div>
    @endif
@endforeach

<style> h3 { margin: 0px; } </style>

<div class="adminFootBuff"></div>

@endsection
