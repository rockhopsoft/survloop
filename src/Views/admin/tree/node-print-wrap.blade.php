<!-- resources/views/vendor/survloop/admin/tree/node-print-wrap.blade.php -->
<div class="container">
<form name="mainPageForm" action="?all=1&alt=1&refresh=1&manip=1" method="post">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" id="moveNodeID" name="moveNode" value="-3">
<input type="hidden" id="moveToParentID" name="moveToParent" value="-3">
<input type="hidden" id="moveToOrderID" name="moveToOrder" value="-3">
{!! $adminBasicPrint !!}
</form>
</div>