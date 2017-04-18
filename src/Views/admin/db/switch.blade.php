<!-- Stored in resources/views/vender/survloop/admin/db/switch.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h2>Switch Current Database To Design</h2>
<hr>

@forelse ($myDbs as $db)
    <div class="p10 @if ($GLOBALS['SL']->dbID == $db->DbID) row2 @endif ">
        @if ($GLOBALS['SL']->dbID == $db->DbID)
            <a href="javascript:void(0)" class="btn btn-lg btn-primary pull-right" DISABLED 
                ><i class="fa fa-database mR5" aria-hidden="true"></i> Current Database Being Designed</a>
        @else
            <a href="/dashboard/db/switch/{{ $db->DbID }}" class="btn btn-lg btn-primary pull-right"
                ><i class="fa fa-arrow-left mR5" aria-hidden="true"></i> Design This Database</a>
        @endif
        <h1 class="mT0 @if ($GLOBALS['SL']->dbID == $db->DbID) slBlueDark @endif " >{{ $db->DbName }}</h1>
        <div class="nPrompt">
            <p><b>{{ $db->DbDesc }}</b> ({{ $db->DbTables }} Tables, {{ $db->DbFields }} Fields)</p>
            @if (trim($db->DbMission) != '') 
                <p><i class="mR10">Mission:</i> {{ $db->DbMission }}</p>
            @endif
        </div>
    </div>
    <hr>
@empty
    <i>Sorry, no databases found.</i> <a href="/fresh/database">Click here to create one</a>.
@endforelse

<div class="p10">
    <a href="/dashboard/db/new/" class="btn btn-lg btn-primary pull-right"
        ><i class="fa fa-star mR5" aria-hidden="true"></i> Create New Database</a>
</div>
<hr>

@endsection