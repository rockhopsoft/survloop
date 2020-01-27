<!-- resources/views/vender/survloop/admin/db/switch.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h2>Switch Current Database To Design</h2>
<hr>

@forelse ($myDbs as $db)
    <div class="p10 @if ($GLOBALS['SL']->dbID == $db->db_id) row2 @endif ">
        @if ($GLOBALS['SL']->dbID == $db->db_id)
            <a href="javascript:;" class="btn btn-lg btn-primary float-right" DISABLED 
                ><i class="fa fa-database mR5" aria-hidden="true"></i> Current Database Being Designed</a>
        @else
            <a href="/dashboard/db/switch/{{ $db->db_id }}" class="btn btn-lg btn-primary float-right"
                ><i class="fa fa-arrow-left mR5" aria-hidden="true"></i> Design This Database</a>
        @endif
        <h1 class="mT0 @if ($GLOBALS['SL']->dbID == $db->db_id) slBlueDark @endif " >{{ $db->db_name }}</h1>
        <div class="nPrompt">
            <p><b>{{ $db->db_desc }}</b> ({{ $db->db_tables }} Tables, {{ $db->db_fields }} Fields)</p>
            @if (trim($db->db_mission) != '') 
                <p><i class="mR10">Mission:</i> {{ $db->db_mission }}</p>
            @endif
        </div>
    </div>
    <hr>
@empty
    <i>Sorry, no databases found.</i> <a href="/fresh/database">Click here to create one</a>.
@endforelse

<div class="p10">
    <a href="/dashboard/db/new" class="btn btn-lg btn-primary float-right"
        ><i class="fa fa-star mR5" aria-hidden="true"></i> Create New Database</a>
</div>
<hr>

@endsection