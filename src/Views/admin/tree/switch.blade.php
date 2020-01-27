<!-- resources/views/vender/survloop/admin/tree/switch.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h2>Switch Current User Experience To Design</h2>
<h4>Experiences for Database: {{ $GLOBALS['SL']->dbRow->db_name }}</h4>
<hr>

@forelse ($myTrees as $tree)
    <div class="p10 @if ($GLOBALS['SL']->treeID == $tree->tree_id) row2 @endif ">
        @if ($GLOBALS['SL']->treeID == $tree->tree_id)
            <a href="javascript:;" class="btn btn-lg btn-primary float-right" DISABLED 
                ><i class="fa fa-snowflake-o mR5" aria-hidden="true"></i> Current User Experience</a>
        @else
            <a href="/dashboard/tree/switch/{{ $tree->tree_id }}" class="btn btn-lg btn-primary float-right"
                ><i class="fa fa-arrow-left mR5" aria-hidden="true"></i> Design This User Experience</a>
        @endif
        <h2 class="mT0 @if ($GLOBALS['SL']->treeID == $tree->tree_id) slBlueDark @endif ">
            {{ $tree->tree_name }}
        </h2>
        @if (isset($tree->tree_desc)) <p><b>{{ $tree->tree_desc }}</b></p> @endif
        <a id="treeEdit{{ $tree->tree_id }}btn" class="treeEditbtn" href="javascript:;"
            ><i class="fa fa-pencil fa-flip-horizontal mR5" aria-hidden="true"></i></a>
        {{ $myTreeNodes[$tree->tree_id] }} Nodes
        @if ($tree->tree_type == 'Survey' && $GLOBALS['SL']->treeID == $tree->tree_id) 
            - <a href="/dashboard/surv-{{ $tree->tree_id }}/xmlmap">XML Map for data sharing</a>
        @endif
        <br />Tree URL: 
        @if ($GLOBALS['SL']->treeIsAdmin) /dash/{{ $tree->tree_slug }}/node-slug
        @else /u/{{ $tree->tree_slug }}/node-slug
        @endif
        <br />
        <div id="treeEdit{{ $tree->tree_id }}" class="disNon">
            <form name="treeForm{{ $tree->tree_id }}" method="post" action="/dashboard/tree/switch">
            <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="treeID" value="{{ $tree->tree_id }}">
            
            
            </form>
        </div>
    </div>
    <hr>
@empty
    <i>Sorry, no experiences found.</i> <a href="/fresh/database">Click here to create one</a>.
@endforelse

<div class="p10">
    <a href="/dashboard/tree/new" class="btn btn-lg btn-primary float-right"
        ><i class="fa fa-star mR5" aria-hidden="true"></i> Create New User Experience</a>
</div>
<hr>

@endsection