<!-- Stored in resources/views/vender/survloop/admin/tree/switch.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

<h1>Switch Current User Experience To Design</h1>
<hr>

@forelse ($myTrees as $tree)
    <div class="p10 @if ($GLOBALS['DB']->treeID == $tree->TreeID) row2 @endif ">
        <div class="row">
            <div class="col-md-8">
                <h2 class="mT0 @if ($GLOBALS['DB']->treeID == $tree->TreeID) slBlueDark @endif " 
                    >{{ $tree->TreeName }}</h2>
                <p><b>{{ $tree->TreeDesc }}</b></p>
                <p>{{ $myTreeNodes[$tree->TreeID] }} Nodes</p>
            </div>
            <div class="col-md-4 pT20">
                @if ($GLOBALS["DB"]->treeID == $tree->TreeID)
                    <a href="javascript:void(0)" class="btn btn-lg btn-primary w100" DISABLED 
                        ><i class="fa fa-snowflake-o mR5" aria-hidden="true"></i> Current User Experience</a>
                @else
                    <a href="/dashboard/db/switch/{{ $tree->TreeID }}" class="btn btn-lg btn-primary w100"
                        ><i class="fa fa-arrow-right mR5" aria-hidden="true"></i> Design This User Experience</a>
                @endif
            </div>
        </div>
    </div>
    <hr>
@empty
    <i>Sorry, no experiences found.</i> <a href="/fresh/database">Click here to create one</a>.
@endforelse

<div class="p10">
    <div class="row">
        <div class="col-md-12">
            <a href="/dashboard/tree/new/" class="btn btn-lg btn-primary w100"
                ><i class="fa fa-star mR5" aria-hidden="true"></i> Create New User Experience</a>
        </div>
    </div>
</div>
<hr>

@endsection