<!-- Stored in resources/views/vender/survloop/admin/db/switch.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

<h1>Switch Current Database To Design</h1>
<hr>

@forelse ($myDbs as $db)
    <div class="p10 @if ($GLOBALS['DB']->dbID == $db->DbID) row2 @endif ">
        <div class="row">
            <div class="col-md-9">
                <h1 class=" @if ($GLOBALS['DB']->dbID == $db->DbID) slBlueDark @endif " >{{ $db->DbName }}</h1>
                <div class="nPrompt">
                    <p><b>{{ $db->DbDesc }}</b></p>
                    @if (trim($db->DbMission) != '') 
                        <p><i class="mR10">Mission:</i><br />{{ $db->DbMission }}</p>
                    @endif
                    <p>{{ $db->DbTables }} Tables, {{ $db->DbFields }} Fields</p>
                </div>
            </div>
            <div class="col-md-3 pT20">
                @if ($GLOBALS["DB"]->dbID == $db->DbID)
                    <a href="javascript:void(0)" class="btn btn-lg btn-primary w100" DISABLED 
                        ><i class="fa fa-database mR5" aria-hidden="true"></i> Current Database<br />Being Designed</a>
                @else
                    <a href="/dashboard/db/switch/{{ $db->DbID }}" class="btn btn-lg btn-primary w100"
                        ><i class="fa fa-arrow-left mR5" aria-hidden="true"></i> Design This Database</a>
                @endif
            </div>
        </div>
    </div>
    <hr>
@empty
    <i>Sorry, no databases found.</i> <a href="/fresh/database">Click here to create one</a>.
@endforelse

<div class="p10">
    <div class="row">
        <div class="col-md-12">
            <a href="/dashboard/db/new/" class="btn btn-lg btn-primary w100"
                ><i class="fa fa-star mR5" aria-hidden="true"></i> Create New Database</a>
        </div>
    </div>
</div>
<hr>

@endsection