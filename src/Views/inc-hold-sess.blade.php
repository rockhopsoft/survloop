<!-- Stored in resources/views/survloop/inc-hold-sess.blade.php -->

<script type="text/javascript">
	holdSess = 1;
	$(function() {
		// handling of back button for ajax calls
		$(window).on("popstate", function(e) {
			if (e.originalEvent.state !== null) {
				//document.getElementById("ajaxWrap").innerHTML=\'<div id="ajaxWrapLoad" class="container f48"><i class="fa fa-spinner fa-pulse"></i></div>\';
				//$("#ajaxWrap").load( location.href+"?ajax=1" );
				window.location=location.href;
			}
		});
	});
</script>
