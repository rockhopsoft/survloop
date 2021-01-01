<!-- resources/views/vendor/survloop/admin/tree/page.blade.php -->
@if ($isPrint) 
    <style>
    .basicTier0, .basicTier1, .basicTier2, .basicTier3, .basicTier4, 
    .basicTier5, .basicTier6, .basicTier7, .basicTier8, .basicTier9 {
        padding: 3px 3px 8px 10px;
        margin: 8px 3px 0px 3px;
    }
    </style>
@endif
<div class="container">
    <h1 class="slBlueDark">
        @if ($GLOBALS['SL']->treeRow->tree_type == 'Page')
            <nobr>@if (!$isPrint) <i class="fa fa-newspaper-o"></i> @endif
            @if ($GLOBALS['SL']->treeIsAdmin) Admin @endif Page:</nobr>
        @else
            <nobr>@if (!$isPrint) <i class="fa fa-snowflake-o"></i> @endif
            @if ($GLOBALS['SL']->treeIsAdmin) Admin @else User @endif Experience:</nobr>
        @endif
        {{ $GLOBALS['SL']->treeName }}
    </h1>
    
    <div class="mB20">
        @if ($isAll)
            <a class="btn btn-primary float-right mL10" 
                @if ($isAlt) href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/map?alt=1" 
                @else href="/dashboard/tree/map" @endif
                ><i class="fa fa-expand fa-flip-horizontal"></i> Collapse Tree</a>
        @else
            <a class="btn btn-primary float-right mL10" 
                @if ($isAlt) href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/map?all=1&alt=1" 
                @else href="/dashboard/tree/map?all=1" @endif
                ><i class="fa fa-expand fa-flip-horizontal"></i> Expand Tree</a>
        @endif
        @if ($isAlt)
            <a class="btn btn-secondary float-right mL10" 
                @if ($isAll) href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/map?all=1" 
                @else href="/dashboard/tree/map" @endif
                ><i class="fa fa-align-left"></i> Hide Details</a>
        @else
            <a class="btn btn-secondary float-right mL10" 
                @if ($isAll) href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/map?all=1&alt=1" 
                @else href="/dashboard/tree/map?alt=1" @endif
                ><i class="fa fa-align-left"></i> Show Details</a>
        @endif
        <a class="btn btn-secondary float-right mL10" href="/dashboard/pages"
            ><i class="fa fa-newspaper-o"></i> List of Pages</a>
        <span class="slGrey">
            A page is also created as a tree filled with branching nodes. 
            Click any node's button (with the icons) to edit, add new nodes, or to move a node. 
            Click <i class="fa fa-expand fa-flip-horizontal"></i> to show or hide all the node's children.
            <a class="adminAboutTog" href="javascript:;">Read more about pages.</a>
        </span>
    </div>
</div>

{!! $printTree !!}

@if (!isset($GLOBALS['SL']->treeRow->tree_root) || intVal($GLOBALS['SL']->treeRow->tree_root) <= 0)
    <a href="?node=-37" class="btn btn-primary"
        ><i class="fa fa-plus-square-o"></i> Create Root Node</a>
@endif

{!! $GLOBALS["SL"]->chkMissingReportFlds() !!}

<div class="adminFootBuff"></div>

<style> ul { margin: 0px 30px; padding: 0px; } </style>