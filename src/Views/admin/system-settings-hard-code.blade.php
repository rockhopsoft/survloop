<!-- resources/views/vendor/survloop/admin/system-settings-hard-code.blade.php -->
@extends('vendor.survloop.admin.system-settings-ajax-form')
@section('formContent')

<input type="hidden" id="optListID" name="optList" 
	value="header-code,css-extra-files,sys-cust-js,sys-cust-ajax">
<input type="hidden" id="styListID" name="styList" value="custCSS,custCSSemail">

<label class="w100">
	<h3 class="mB15 slBlueDark">System-Wide Header HTML</h3>
	<div>
		<div class="pull-left">
			This is injected within the top of system pages:
		</div>
		<div class="pull-left pT5 pL30">
			<pre>&lt;head&gt; ...custom HTML... &lt;/head&gt;</pre>
		</div>
	</div>
	{!! view(
	    'vendor.survloop.admin.system-one-setting-textarea', 
	    [
	        "opt"    => 'header-code', 
	        "val"    => $sysDef->v["settingsList"]["header-code"],
	        "height" => '250'
	    ]
	)->render() !!}
</label>

<p><br /></p>

<label class="w100">
	<h3 class="mT30 mB15 slBlueDark">Extra CSS Files</h3>
	<p>
		List full filenames for extra CSS files, separated by commas.
		They will be minified and appended to /sys1.min.css.
	</p>
	{!! view(
	    'vendor.survloop.admin.system-one-setting-textarea', 
	    [
	        "opt"    => 'css-extra-files', 
	        "val"    => $sysDef->v["settingsList"]["css-extra-files"],
	        "height" => '250'
	    ]
	)->render() !!}
</label>

<p><br /></p>

<label class="w100">
	<h3 class="mT30 mB15 slBlueDark">System-Wide Custom CSS</h3>
	<p></p>
	<textarea name="sys-cust-css" class="form-control" autocomplete="off"
	    style="height: 250px; font-family: Courier New;"
	    >{!! $sysDef->v["custCSS"]->def_description !!}</textarea>
</label>

<p><br /></p>

<label class="w100">
	<h3 class="mT30 mB15 slBlueDark">Custom CSS for Emails</h3>
	<p>
		This will be appended to /sys2.min.css.
	</p>
    <textarea name="sys-cust-css-email" class="form-control" autocomplete="off" 
        style="height: 250px; font-family: Courier New;"
        >{!! $sysDef->v["custCSSemail"]->def_description !!}</textarea>
</label>

<p><br /></p>

<label class="w100">
	<h3 class="mT30 mB15 slBlueDark">System-Wide Custom JS</h3>
	<p>
		This will be appended to /sys2.min.js.
	</p>
	{!! view(
	    'vendor.survloop.admin.system-one-setting-textarea', 
	    [
	        "opt"    => 'sys-cust-js', 
	        "val"    => $sysDef->v["settingsList"]["sys-cust-js"],
	        "height" => '250'
	    ]
	)->render() !!}
</label>

<p><br /></p>

<label class="w100">
	<h3 class="mT30 mB15 slBlueDark">System-Wide Custom jQuery</h3>
	<p>
		This is injected within the system's main jQuery/AJAX enclosure
		within /sys2.min.js:
	</p>
	<pre>$(document).ready(function(){ ...custom jQuery... });</pre>
	{!! view(
	    'vendor.survloop.admin.system-one-setting-textarea', 
	    [
	        "opt"    => 'sys-cust-ajax', 
	        "val"    => $sysDef->v["settingsList"]["sys-cust-ajax"],
	        "height" => '250'
	    ]
	)->render() !!}
</label>



@endsection
