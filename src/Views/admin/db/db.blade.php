<!-- resources/views/vendor/survloop/admin/db/db.blade.php -->

@extends('vendor.survloop.master')

@section('content')

@if (isset($admPageTitle) && isset($admPageLinks) && trim($admPageTitle) != '' && trim($admPageLinks) != '')
	<div class="clearfix p20"></div>
	<div class="col-md-8">
	
		{!! $admPageTitle !!}
	
	</div>
	<div class="col-md-4 taR dbNav">
	
		{!! $admPageLinks !!}
		
	</div>
	<div class="clearfix p20"></div>
@endif
	
{!! $admContent !!}


<style>
#bigWrap {
	width: 92%;
	max-width: 1600px;
}
</style>

<script type="text/javascript">
$(function() {
	$(document).on("click", ".fldSpecBtn", function() {
		var FldID = $(this).attr("id").replace("fldSpecBtn", "");
		if (document.getElementById("fldSpec"+FldID+"").innerHTML == '') {
			$("#fldSpec"+FldID+"").load("/dashboard/db/field/ajax/"+FldID+"");
		}
		$("#fldSpec"+FldID+"").slideToggle("slow");
		return true;
	});
});
</script>


@endsection
