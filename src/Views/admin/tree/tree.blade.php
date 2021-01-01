<!-- resources/views/vendor/survloop/admin/tree/tree.blade.php -->
<div class="container-fluid">
    
@if (!$isPrint) <div class="slCard nodeWrap"> @else <div> @endif
    <div class="row mB15">
        <div class="col-8">
            <h2 class="mB5"><span class="slBlueDark">
            @if ($GLOBALS['SL']->treeRow->tree_type == 'Page')
                <nobr>@if (!$isPrint) <i class="fa fa-newspaper-o"></i> @endif
                    {{ $GLOBALS['SL']->treeName }}</span></nobr>
            @else
                <nobr>@if (!$isPrint) <i class="fa fa-snowflake-o"></i> @endif 
                    {{ $GLOBALS['SL']->treeName }}</span>
                </nobr>
            @endif
            </h2>
        @if ($isPrint)
            {!! view('vendor.survloop.elements.print-header-legal', [])->render() !!}
            <h2><nobr>Core Specifications of {{ $GLOBALS['SL']->treeRow->tree_name }} 
                User Form Tree</nobr></h2> 
        @else
            <div class="slGrey">
                <b>Full @if ($GLOBALS['SL']->treeIsAdmin) Admin @endif 
                @if ($GLOBALS['SL']->treeRow->tree_type == 'Page') Page
                @else Survey
                @endif Map</b><br />
                A survey (or form) is created as a tree filled with branching nodes. 
                Click any node's button (with its ID#) to edit, add new nodes, 
                or to move a node. 
                Click <i class="fa fa-expand fa-flip-horizontal"></i> to 
                show or hide all the node's children.
                <a class="adminAboutTog" href="javascript:;"
                    >Read more about these branching trees.</a> 
                <span class="red mL20">*required</span>
            </div>
        @endif
        </div><div class="col-4">
    @if (!$isPrint)
        @if ($isAlt)
            <div class="m5 pull-right"><a class="btn btn-info" 
                @if ($isAll) href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/map?all=1" 
                @else href="/dashboard/tree/map" @endif
                ><i class="fa fa-align-left"></i> Hide Details</a></div>
        @else
            <div class="m5 pull-right"><a class="btn btn-info" 
                @if ($isAll) href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/map?all=1&alt=1" 
                @else href="/dashboard/tree/map?alt=1" @endif
                ><i class="fa fa-align-left"></i> Show Details</a></div>
        @endif
        @if ($isAll)
            <div class="m5 pull-right"><a class="btn btn-info" 
                @if ($isAlt) href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/map?alt=1" 
                @else href="/dashboard/tree/map" @endif
                ><i class="fa fa-expand fa-flip-horizontal"></i> Collapse Tree</a></div>
        @else
            <div class="m5 pull-right"><a class="btn btn-info" 
                @if ($isAlt) href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/map?all=1&alt=1" 
                @else href="/dashboard/tree/map?all=1" @endif
                ><i class="fa fa-expand fa-flip-horizontal"></i> Expand Tree</a></div>
        @endif
    @endif
        </div>
    </div>

{!! $GLOBALS["SL"]->printTreeNodeStats($isPrint, $isAll, $isAlt) !!}

@if ($GLOBALS['SL']->treeIsAdmin)
    <a href="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/dashboard/start/{{ $GLOBALS['SL']->treeRow->tree_slug }}" 
        ><h4 class="mTn10 mB5">{!! 
            $GLOBALS['SL']->swapURLwrap($GLOBALS['SL']->sysOpts['app-url'] 
                . '/dashboard/start/' . $GLOBALS['SL']->treeRow->tree_slug, false)
        !!}</h4></a>
@else
    <a href="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/start/{{ $GLOBALS['SL']->treeRow->tree_slug }}"
        ><h4 class="mTn10 mB5">{!! 
            $GLOBALS['SL']->swapURLwrap($GLOBALS['SL']->sysOpts['app-url'] 
                . '/start/' . $GLOBALS['SL']->treeRow->tree_slug, false) 
        !!}</h4></a>
@endif

<div class="mT15">{!! $printTree !!}</div>

@if (!isset($GLOBALS['SL']->treeRow->tree_root) 
    || intVal($GLOBALS['SL']->treeRow->tree_root) <= 0)
    <a href="?node=-37" class="btn btn-primary"
        ><i class="fa fa-plus-square-o"></i> Create Root Node</a>
@endif

</div>

<style>
ul { margin: 0px 30px; padding: 0px; } 
@if ($isPrint) 
    .basicTier0, .basicTier1, .basicTier2, .basicTier3, .basicTier4, 
    .basicTier5, .basicTier6, .basicTier7, .basicTier8, .basicTier9 {
        padding: 3px 3px 8px 10px;
        margin: 8px 3px 0px 3px;
    }
@else
@endif
</style>