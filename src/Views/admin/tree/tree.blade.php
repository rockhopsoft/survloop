<!-- resources/views/vendor/survloop/admin/tree/tree.blade.php -->
<div class="container"><div class="slCard nodeWrap">
<h2 class="mB5"><span class="slBlueDark">
    @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
        <nobr>@if (!$isPrint) <i class="fa fa-newspaper-o"></i> @endif {{ $GLOBALS['SL']->treeName }}:</span>
        @if ($GLOBALS['SL']->treeIsAdmin) Admin @endif Page</nobr>
    @else
        <nobr>@if (!$isPrint) <i class="fa fa-snowflake-o"></i> @endif {{ $GLOBALS['SL']->treeName }}:</span>
        Full @if ($GLOBALS['SL']->treeIsAdmin) Admin @endif Survey Map</nobr>
    @endif
</h2>

@if ($isPrint)
    {!! view('vendor.survloop.print-header-legal', [])->render() !!}
    <h2><nobr>Core Specifications of {{ $GLOBALS['SL']->treeRow->TreeName }} User Form Tree</nobr></h2> 
@endif

@if (!$isPrint)
    <div class="row">
        <div class="col-9 slGrey">
            A survey (or form) is created as a tree filled with branching nodes. 
            Click any node's button (with its ID#) to edit, add new nodes, or to move a node. 
            Click <i class="fa fa-expand fa-flip-horizontal"></i> to show or hide all the node's children.
            <a class="adminAboutTog" href="javascript:;">Read more about these branching trees.</a> 
            <span class="red mL20">*required</span>
        </div><div class="col-3 taR">
            <a href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/settings" class="btn btn-secondary"
                ><i class="fa fa-cogs" aria-hidden="true"></i> Survey Settings</a>
        </div>
    </div>
@endif
{!! $GLOBALS["SL"]->printTreeNodeStats($isPrint, $isAll, $isAlt) !!}
</div></div>

@if ($GLOBALS['SL']->treeIsAdmin)
    <a href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/dashboard/start/{{ $GLOBALS['SL']->treeRow->TreeSlug }}" 
        ><h3 class="mTn10 mB5">{!! $GLOBALS['SL']->swapURLwrap($GLOBALS['SL']->sysOpts['app-url'] . '/dashboard/start/' 
        . $GLOBALS['SL']->treeRow->TreeSlug, false) !!}</h3></a>
@else
    <a href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/start/{{ $GLOBALS['SL']->treeRow->TreeSlug }}"
        ><h3 class="mTn10 mB5">{!! $GLOBALS['SL']->swapURLwrap($GLOBALS['SL']->sysOpts['app-url'] . '/start/' 
        . $GLOBALS['SL']->treeRow->TreeSlug, false) !!}</h3></a>
@endif
{!! $printTree !!}

@if (!isset($GLOBALS['SL']->treeRow->TreeRoot) || intVal($GLOBALS['SL']->treeRow->TreeRoot) <= 0)
    <a href="?node=-37" class="btn btn-lg btn-primary f22"><i class="fa fa-plus-square-o"></i> Create Root Node</a>
@endif

<div class="adminFootBuff"></div>

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