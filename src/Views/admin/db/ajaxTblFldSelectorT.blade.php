<!-- resources/views/vendor/survloop/admin/db/ajaxTblFldSelectorT.blade.php -->

<input type="hidden" name="RuleTables" id="RuleTablesID" value="{{ urldecode($rT) }}">
@forelse ($tblList as $i => $tbl)
	@if ($i > 0)
		,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
	@endif
	<nobr>{!! $tbl[1] !!}
	<a href="javascript:void(0)" onClick="delTbl({{ $tbl[0] }});" 
		class="red f12 mL5"><span class="glyphicon glyphicon-remove"></span></a></nobr>
@empty
@endforelse
<br />
<span class="f10">Add Table:</span> <select name="addT" id="addTID">{!! $tblDrop !!}</select>
<a href="javascript:void(0)" id="addTbtn"><i class="fa fa-plus-circle"></i></a>
<script type="text/javascript"> 
$(document).ready(function(){
	$("#addTbtn").click(function(){
		$("#tblSelect").load("/dashboard/db/ajax/tblFldSelT/{{ $rT }}?addT="+document.getElementById("addTID").value+"");
	});
});
function delTbl(delT) {
	$("#tblSelect").load("/dashboard/db/ajax/tblFldSelT/{{ $rT }}?delT="+delT+"");
}
</script>
