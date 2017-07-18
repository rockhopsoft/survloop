<!-- resources/views/vendor/survloop/formtree-form-start.blade.php -->

<form id="postNodeForm" name="postNode" method="post" 
    @if (sizeof($pageHasUpload) > 0) enctype="multipart/form-data" @endif 
    @if ($hasRegisterNode) action="/register" target="_parent"
    @elseif ($GLOBALS['SL']->treeRow->TreeType == 'Page')
        @if ($GLOBALS['SL']->treeIsAdmin)
            action="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}"
        @else
            action="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/{{ $GLOBALS['SL']->treeRow->TreeSlug }}"
        @endif
    @endif >
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="ajax" @if ($GLOBALS['SL']->treeRow->TreeType == 'Page') value="0" @else value="1" @endif >
<input type="hidden" name="tree" id="treeID" value="{{ $GLOBALS['SL']->treeID }}">
<input type="hidden" name="treeSlug" id="treeSlugID" value="{{ $GLOBALS['SL']->treeRow->TreeSlug }}">
@if ($GLOBALS['SL']->treeRow->TreeType != 'Page')
    <input type="hidden" name="node" id="nodeID" value="{{ $nID }}">
    <input type="hidden" name="nodeSlug" id="nodeSlugID" value="{{ $nSlug }}">
    <input type="hidden" name="loop" id="loopID" value="{{ $GLOBALS['SL']->closestLoop['loop'] }}">
    <input type="hidden" name="loopItem" id="loopItemID" value="{{ $GLOBALS['SL']->closestLoop['itemID'] }}">
    <input type="hidden" name="alt" id="altID" value="-3">
    <input type="hidden" name="jumpTo" id="jumpToID" value="{{ $nodePrintJumpTo }}">
    <input type="hidden" name="afterJumpTo" id="afterJumpToID" value="-3">
@endif
<input type="hidden" name="step" id="stepID" value="next">
<input type="hidden" name="zoomPref" id="zoomPrefID" value="{{ $zoomPref }}">
