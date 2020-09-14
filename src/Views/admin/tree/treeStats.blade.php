<!-- resources/views/admin/treeStats.blade.php -->

<h2>
    <i class="fa fa-snowflake-o"></i> Users Response Stats: 
    <nobr>{{ $GLOBALS['SL']->treeName }}</nobr>
</h2>
<div class="container mB20">
@if ($isAll)
    <a class="float-right btn btn-secondary mR10" 
        href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/stats"
        ><i class="fa fa-expand fa-flip-horizontal"></i> Collapse All Nodes</a>
@else
    <a class="float-right btn btn-secondary mR10" 
        href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/stats?all=1"
        ><i class="fa fa-expand fa-flip-horizontal"></i> Expand All Nodes</a>
@endif
    <a class="float-right btn btn-secondary mR10" 
        href="/dashboard/debug-node-saves?tree={{ $GLOBALS['SL']->treeID }}"
        ><i class="fa fa-bug"></i> Record Response History</a>

    <b>Down To The NODE.</b> <span class="slGrey">
    This report provides a breakdown of response statistics for every freakin' 
    <a href="https://en.wikipedia.org/wiki/Tree_%28data_structure%29" target="_blank">node</a> 
    on the tree. As users can generally go back and forth through, one percentage is provided 
    for the final response submitted for a given complaint, as well as totals of all attempts 
    (page loads) by all complaints. This is clearly more useful for some nodes than others, and 
    some of the nodes' final submission stats will be very useful to present with graphs on 
    various public and staff reports. But this comprehensive overview might illuminate 
    trends or bugs which might not otherwise be noticed.
    </span>
</div>

{!! $printTree !!}

<style> ul { margin: 0px 30px; padding: 0px; } </style>

<div class="adminFootBuff"></div>
