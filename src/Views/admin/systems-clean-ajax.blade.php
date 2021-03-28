<!-- resources/views/vendor/survloop/admin/systems-clean-ajax.blade.php -->

@if (isset($currStep) && trim($currStep) != '')
	<h3 class="slBlueDark">... Cleaning Loop Step {{ $currStep }} ...</h3>
@endif
<script type="text/javascript"> $(document).ready(function(){
setTimeout(function() { 
    $("#cleaningDiv").load("/dashboard/systems-clean?run=clean");
}, 2000);
}); </script>
