<!-- resources/views/vendor/survloop/admin/system-settings-ajax-form.blade.php -->

<div class="container-fluid">
    <a href="?refresh=2" class="btn btn-secondary btn-sm pull-right"
        ><i class="fa fa-refresh mR3" aria-hidden="true"></i> Refresh All Caches</a>
    <h4 class="mT0"><nobr><i class="fa fa-cogs"></i> System Settings</nobr></h4>

	<form id="sysSetFormID" method="post" 
		action="/dashboard/settings/{{ $sysSet }}?ajax=1">
	<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
	<input type="hidden" name="ajax" value="1">
	<input type="hidden" name="sub" value="1">
	<input type="hidden" name="set" value="{{ $sysSet }}">

	@yield('formContent')

	<div class="w100 taC p30 mT30">
		<a id="sysSaveBtn" href="javascript:;" class="btn btn-primary btn-xl mB30"
			>Save All Changes</a>
	</div>
	<div id="settingsSaveResults" class="w100 taC"></div>
	</form>

</div>


<script type="text/javascript"> $(document).ready(function(){

function runSysSettingsSubAjax() {
    blurAllFlds();
    var formData = new FormData(document.getElementById("sysSetFormID"));
    document.getElementById("settingsSaveResults").innerHTML=getSpinner();
    $.ajax({
        url: "/dashboard/settings/{{ $sysSet }}?ajax=1&sub=1",
        type: "POST", 
        data: formData, 
        contentType: false,
        processData: false,
        success: function(data) {
            $("#settingsSaveResults").empty();
            $("#settingsSaveResults").append(data);
        }, 
        error: function(xhr, status, error) {
            $("#settingsSaveResults").append("<div>(error - "+xhr.responseText+")</div>");
        }
    });
    return false;
}

$(document).on("click", "#sysSaveBtn", function() {
	runSysSettingsSubAjax();
});

}); </script>


<div class="adminFootBuff"></div>
