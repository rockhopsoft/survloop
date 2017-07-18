<!-- Stored in resources/views/vender/survloop/admin/tree/switch.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h2>Switch Current User Experience To Design</h2>
<h4>Experiences for Database: {{ $GLOBALS['SL']->dbRow->DbName }}</h4>
<hr>

@forelse ($myTrees as $tree)
    <div class="p10 @if ($GLOBALS['SL']->treeID == $tree->TreeID) row2 @endif ">
        @if ($GLOBALS['SL']->treeID == $tree->TreeID)
            <a href="javascript:void(0)" class="btn btn-lg btn-primary pull-right" DISABLED 
                ><i class="fa fa-snowflake-o mR5" aria-hidden="true"></i> Current User Experience</a>
        @else
            <a href="/dashboard/tree/switch/{{ $tree->TreeID }}" class="btn btn-lg btn-primary pull-right"
                ><i class="fa fa-arrow-left mR5" aria-hidden="true"></i> Design This User Experience</a>
        @endif
        <h2 class="mT0 @if ($GLOBALS['SL']->treeID == $tree->TreeID) slBlueDark @endif ">{{ $tree->TreeName }}</h2>
        @if (isset($tree->TreeDesc)) <p><b>{{ $tree->TreeDesc }}</b></p> @endif
        <a id="treeEdit{{ $tree->TreeID }}btn" class="treeEditbtn" href="javascript:;"
            ><i class="fa fa-pencil fa-flip-horizontal mR5" aria-hidden="true"></i></a>
        {{ $myTreeNodes[$tree->TreeID] }} Nodes
        @if ($tree->TreeType == 'Primary Public' && $GLOBALS['SL']->treeID == $tree->TreeID) 
            - <a href="/dashboard/tree-{{ $tree->TreeID }}/xmlmap">XML Map for data sharing</a>
        @endif
        <br />Tree URL: 
        @if ($GLOBALS['SL']->treeIsAdmin)
            /dash/{{ $tree->TreeSlug }}/node-slug
        @else
            /u/{{ $tree->TreeSlug }}/node-slug
        @endif
        <br />
        <div id="treeEdit{{ $tree->TreeID }}" class="disNon">
            <form name="treeForm{{ $tree->TreeID }}" method="post" action="/dashboard/tree/switch">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="treeID" value="{{ $tree->TreeID }}">
            
            
            </form>
        </div>
    </div>
    <hr>
@empty
    <i>Sorry, no experiences found.</i> <a href="/fresh/database">Click here to create one</a>.
@endforelse

<div class="p10">
    <a href="/dashboard/tree/new/" class="btn btn-lg btn-primary pull-right"
        ><i class="fa fa-star mR5" aria-hidden="true"></i> Create New User Experience</a>
</div>
<hr>

@endsection