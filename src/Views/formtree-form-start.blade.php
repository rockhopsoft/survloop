<!-- resources/views/vendor/survloop/formtree-form-start.blade.php -->
@if (isset($currPage) && isset($currPage[0]) && trim($currPage[0]) != '')
<script type="text/javascript"> 
function chkLostFrame() {
    if (!document.getElementById('main')) { window.location='{{ $currPage[0] }}'; }
    else if (document.getElementById('maincontentWrap')) {
        document.getElementById('maincontentWrap').style.display = 'block';
    }
}
setTimeout("chkLostFrame()", 0);
</script>
@endif
<form id="postNodeForm" name="postNode" method="post" action="{{ $action }}" target="_self"
    @if (sizeof($pageHasUpload) > 0) enctype="multipart/form-data" @endif >
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="formLoaded" value="<?= time() ?>">
<input type="hidden" id="postActionID" name="postAction" value="{{ $action }}">
<input type="hidden" name="ajax" id="ajaxID"
    @if ($GLOBALS['SL']->treeRow->TreeType == 'Page') value="0" @else value="1" @endif >
<input type="hidden" name="tree" id="treeID" value="{{ $GLOBALS['SL']->treeID }}">
<input type="hidden" name="node" id="nodeID" value="{{ $nID }}">
<input type="hidden" name="treeSlug" id="treeSlugID" value="{{ $GLOBALS['SL']->treeRow->TreeSlug }}">
<input type="hidden" name="nodeSlug" id="nodeSlugID" value="{{ $nSlug }}">
@if ($GLOBALS['SL']->treeRow->TreeType != 'Page')
    <input type="hidden" name="loop" id="loopID" value="{{ $GLOBALS['SL']->closestLoop['loop'] }}">
    <input type="hidden" name="loopItem" id="loopItemID" value="{{ $GLOBALS['SL']->closestLoop['itemID'] }}">
    <input type="hidden" name="alt" id="altID" value="-3">
    <input type="hidden" name="jumpTo" id="jumpToID" value="{{ $nodePrintJumpTo }}">
    <input type="hidden" name="afterJumpTo" id="afterJumpToID" value="-3">
    <input type="hidden" name="loopRootJustLeft" id="loopRootJustLeftID" value="{{ $loopRootJustLeft }}">
    <input type="hidden" name="chgCnt" id="chgCntID" value="0">
    @if (isset($GLOBALS['SL']->closestLoop["obj"]->DataLoopRoot) 
        && intVal($GLOBALS['SL']->closestLoop["obj"]->DataLoopRoot) > 0)
        <input type="hidden" name="dataLoopRoot" id="dataLoopRootID" value="{{
            intVal($GLOBALS['SL']->closestLoop['obj']->DataLoopRoot) }}">
    @endif
@endif
<input type="hidden" name="popStateUrl" id="popStateUrlID" value="">
<input type="hidden" name="zoomPref" id="zoomPrefID" value="{{ $zoomPref }}">
<input type="hidden" name="step" id="stepID" value="next">
@if ($GLOBALS['SL']->treeRow->TreeType == 'Page') <div id="isPage"></div> @endif