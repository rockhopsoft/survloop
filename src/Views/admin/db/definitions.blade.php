<!-- resources/views/vendor/survloop/admin/db/definitions.blade.php -->
@extends('vendor.survloop.master')
@section('content')

@if ($dbAllowEdits)
    <a href="/dashboard/db/definitions/add" class="btn btn-secondary float-right"
        ><i class="fa fa-plus-circle"></i> Add a New Definition</a>
@endif

<h2><span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>: Definition Sets (Value Ranges)</h2>
<h5>These are collections of value ranges for various database fields, 
    often used as the multi-choice options provided a user for certain questions.</h5>
    
<div class="slGrey">
    <i>Values / Options, Description / Notes:</i><i class="float-right">Definition ID#</i>
</div>

@foreach ($defSets as $subset => $setDefs)
    @if (sizeof($setDefs))
        <a name="{{ str_replace(' ', '', $subset) }}"></a>
        <div class="row mT10" >
            <div class="col-9">
                <h3 class="m0 slBlueDark">{{ $subset }}</h3>
            </div>
            <div class="col-3 taR" >
            @if ($dbAllowEdits)
                <a href="/dashboard/db/definitions/add/{{ $subset }}" class="btn btn-secondary m5"
                    ><i class="fa fa-plus-circle"></i> Add</a>
                <a href="/dashboard/db/definitions/sort/{!! urlencode($subset) !!}" 
                    class="btn btn-secondary m5" ><i class="fa fa-sort-amount-asc"></i>Sort</a>
            @endif
            </div>
        </div>
        @foreach ($setDefs as $cnt => $setDef)
            <div class="pL15 pR15"><div class="row @if ($cnt%2 == 0) row2 @endif " >
                <div class="col-1">
                @if ($dbAllowEdits)
                    <p class="mT5 mB5"><a href="/dashboard/db/definitions/edit/{{ $setDef->DefID }}"
                        ><nobr><i class="fa fa-pencil fa-flip-horizontal"></i> {{ $setDef->DefID }}</nobr></a></p>
                @endif
                </div>
                <div class="col-11">
                    <p class="mT5 mB5">{{ $setDef->DefValue }}</p>
                    @if (trim($setDef->DefDescription) != '')
                        <p class="mT0 slGrey">{{ $setDef->DefDescription }}</p>
                    @endif
                </div>
            </div></div>
        @endforeach
        <div class="clearfix p10 mB20" ></div>
    @endif
@endforeach

<div class="adminFootBuff"></div>

@endsection