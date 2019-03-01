<!-- resources/views/vendor/survloop/formtree-form-start.blade.php -->
<form id="postNodeForm" name="postNode" method="post" action="{{ $action }}" target="_self" {!! $pageHasUpload !!} >
<input type="hidden" name="_token" id="csrfTok" value="{{ csrf_token() }}">
<input type="hidden" name="formLoaded" value="<?= time() ?>">
<input type="hidden" name="postAction" id="postActionID" value="{{ $action }}">
<input type="hidden" name="ajax" id="ajaxID" value="{{ $isAjax }}">
<input type="hidden" name="tree" id="treeID" value="{{ $GLOBALS['SL']->treeID }}">
<input type="hidden" name="node" id="nodeID" value="{{ $nID }}">
<input type="hidden" name="treeSlug" id="treeSlugID" value="{{ $GLOBALS['SL']->treeRow->TreeSlug }}">
<input type="hidden" name="nodeSlug" id="nodeSlugID" value="{{ $nSlug }}">
<input type="hidden" name="abTest" id="abTestID" value="{{ $abTest }}">
@if ($GLOBALS['SL']->treeRow->TreeType != 'Page')
<input type="hidden" name="loop" id="loopID" value="{{
    ((isset($GLOBALS['SL']->closestLoop['loop'])) ? $GLOBALS['SL']->closestLoop['loop'] : 0) }}">
<input type="hidden" name="loopItem" id="loopItemID" value="{{ 
    ((isset($GLOBALS['SL']->closestLoop['itemID'])) ? $GLOBALS['SL']->closestLoop['itemID'] : 0) }}">
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
@if ($GLOBALS["SL"]->REQ->has("noAutoSave"))
    <input type="hidden" name="noAutoSave" id="noAutoSaveID" value="1">
@endif
@endif
<input type="hidden" name="popStateUrl" id="popStateUrlID" value="">
<input type="hidden" name="zoomPref" id="zoomPrefID" value="{{ $zoomPref }}">
<input type="hidden" name="step" id="stepID" value="next">