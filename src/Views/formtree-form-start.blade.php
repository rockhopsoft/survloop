<!-- resources/views/vendor/survloop/formtree-form-start.blade.php -->

<form id="postNodeForm" name="postNode" method="post" @if (sizeof($pageHasUpload) > 0) enctype="multipart/form-data" @endif >
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="ajax" value="1">
<input type="hidden" name="node" id="nodeID" value="{{ $nID }}">
<input type="hidden" name="loop" id="loopID" value="{{ $GLOBALS['DB']->closestLoop['loop'] }}">
<input type="hidden" name="loopItem" id="loopItemID" value="{{ $GLOBALS['DB']->closestLoop['itemID'] }}">
<input type="hidden" name="step" id="stepID" value="next">
<input type="hidden" name="alt" id="altID" value="-3">
<input type="hidden" name="jumpTo" id="jumpToID" value="{{ $nodePrintJumpTo }}">
<input type="hidden" name="afterJumpTo" id="afterJumpToID" value="-3">
<input type="hidden" name="zoomPref" id="zoomPrefID" value="{{ $zoomPref }}">
