<!-- resources/views/admin/treeStats.blade.php -->

<h1><i class="fa fa-snowflake-o"></i> Users Response Stats</nobr></h1>
<div class="container">
    <b>Down To The NODE.</b> This report provides a breakdown of response statistics for every freakin' <a href="https://en.wikipedia.org/wiki/Tree_%28data_structure%29" target="_blank">node</a> 
    on the tree. As users can generally go back and forth through, one percentage is provided for the final response submitted for a given complaint, 
    as well as totals of all attempts (page loads) by all complaints. This is clearly more useful for some nodes than others, 
    and some of the nodes' final submission stats will be very useful to sexily present with graphs on various public and staff reports.
    But this comprehensive overview might illuminate trends or bugs which might not otherwise be noticed.
</div>

<div class="p10">
@if ($isAll)
    <a class="btn btn-lg btn-default mR10" href="/admin/tree/stats"><i class="fa fa-expand fa-flip-horizontal"></i> Collapse All Nodes</a>
@else
    <a class="btn btn-lg btn-default mR10" href="/admin/tree/stats?all=1"><i class="fa fa-expand fa-flip-horizontal"></i> Expand All Nodes</a>
@endif
</div>

{!! $printTree !!}

<style> ul { margin: 0px 30px; padding: 0px; } </style>

<div class="adminFootBuff"></div>
