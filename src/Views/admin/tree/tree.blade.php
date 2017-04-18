<!-- resources/views/vendor/survloop/admin/tree/tree.blade.php -->

@if ($isPrint) 
    <style>
    .basicTier0, .basicTier1, .basicTier2, .basicTier3, .basicTier4, 
    .basicTier5, .basicTier6, .basicTier7, .basicTier8, .basicTier9 {
        padding: 3px 3px 8px 10px;
        margin: 8px 3px 0px 3px;
    }
    </style>
@endif
<h1 class="slBlueDark">
    @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
        <nobr>@if (!$isPrint) <i class="fa fa-newspaper-o"></i> @endif
        @if ($GLOBALS['SL']->treeIsAdmin) Admin @endif Page:</nobr>
    @else
        <nobr>@if (!$isPrint) <i class="fa fa-snowflake-o"></i> @endif
        @if ($GLOBALS['SL']->treeIsAdmin) Admin @else User @endif Experience:</nobr>
    @endif
    {{ $GLOBALS['SL']->treeName }}
</h1>
@if ($isPrint) 
    <h2><nobr>Core Specifications of {{ $GLOBALS['SL']->treeRow->TreeName }} User Experience</nobr></h2> 
    {{ $IPlegal }}
@endif

@if (!$isPrint)
    <div class="mB20">
        @if ($isAll)
            <a class="btn btn-primary pull-right mL10" 
                @if ($isAlt) href="/dashboard/tree/map?alt=1" @else href="/dashboard/tree/map" @endif
                ><i class="fa fa-expand fa-flip-horizontal"></i> Collapse Tree</a>
        @else
            <a class="btn btn-primary pull-right mL10" 
                @if ($isAlt) href="/dashboard/tree/map?all=1&alt=1" @else href="/dashboard/tree/map?all=1" @endif
                ><i class="fa fa-expand fa-flip-horizontal"></i> Expand Tree</a>
        @endif
        @if ($isAlt)
            <a class="btn btn-default pull-right mL10" 
                @if ($isAll) href="/dashboard/tree/map?all=1" @else href="/dashboard/tree/map" @endif
                ><i class="fa fa-align-left"></i> Hide Details</a>
        @else
            <a class="btn btn-default pull-right mL10" 
                @if ($isAll) href="/dashboard/tree/map?all=1&alt=1" @else href="/dashboard/tree/map?alt=1" @endif
                ><i class="fa fa-align-left"></i> Show Details</a>
        @endif
        <a class="btn btn-default pull-right mL10" id="adminAboutTog" href="/dashboard/tree/xmlmap"
            >Data XML Map</a>
        <a class="btn btn-default pull-right mL10" id="adminAboutTog" href="/dashboard/tree/data"
            >Tree Data Structures</a>
        <span class="slGrey">
            A user experience is created as a tree filled with branching nodes. Click an ID# to edit any node. 
            Click <i class="fa fa-expand fa-flip-horizontal"></i> to show or hide all the node's children.
            Click <i class="fa fa fa-dot-circle-o"></i> to add new nodes, or to move a node. 
            <a id="adminAboutTog" href="javascript:void(0)">Read more about these branching trees.</a>
        </span>
    </div>
@endif

@if ($GLOBALS['SL']->treeIsAdmin)
    <a href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/dashboard/start/{{ $GLOBALS['SL']->treeRow->TreeSlug }}" 
        class="f20">{{ $GLOBALS['SL']->sysOpts['app-url'] }}/dashboard/start/{{ 
        $GLOBALS['SL']->treeRow->TreeSlug }}</a>
@else                                                              
    <a href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/start/{{ $GLOBALS['SL']->treeRow->TreeSlug }}"
        class="f20">{{ $GLOBALS['SL']->sysOpts['app-url'] }}/start/{{ $GLOBALS['SL']->treeRow->TreeSlug }}
@endif

{!! $printTree !!}

@if (!isset($GLOBALS['SL']->treeRow->TreeRoot) || intVal($GLOBALS['SL']->treeRow->TreeRoot) <= 0)
    <a href="?node=-37" class="btn btn-lg btn-primary f22"><i class="fa fa-plus-square-o"></i> Create Root Node</a>
@endif

<div class="adminFootBuff"></div>

<style> ul { margin: 0px 30px; padding: 0px; } </style>