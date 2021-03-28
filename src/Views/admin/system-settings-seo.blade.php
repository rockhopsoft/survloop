<!-- resources/views/vendor/survloop/admin/system-settings-seo.blade.php -->
@extends('vendor.survloop.admin.system-settings-ajax-form')
@section('formContent')

<input type="hidden" id="optListID" name="optList" 
    value="twitter,matomo-analytic-url,matomo-analytic-site-id,google-analytic,google-map-key,google-map-key2,google-cod-key,google-cod-key2">
<input type="hidden" id="styListID" name="styList" value="">

<h3 class="mB15 slBlueDark">Search Engine Optimization</h3>

<div class="row mB30">
    <div class="col-md-7">
        {!! view(
            'vendor.survloop.admin.seo-meta-editor', 
            [ "currMeta" => $currMeta ]
        )->render() !!}
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-4">
        <div class="pB10">Social Sharing Preview</div>
        {!! view('vendor.survloop.admin.seo-meta-editor-preview')->render() !!}
    </div>
</div>

<p><br /></p>

<h3 class="mT30 mB15 slBlueDark">Social Settings</h3>
<table class="table table-striped">
@foreach (['twitter', 'matomo-analytic-url', 'matomo-analytic-site-id',
		'google-analytic', 'google-map-key', 'google-map-key2', 
    	'google-cod-key', 'google-cod-key2'] as $opt)
    {!! view(
        'vendor.survloop.admin.system-one-setting', 
        [
            "opt" => $opt,
            "val" => $sysDef->v["settingsList"][$opt]
        ]
    )->render() !!}
@endforeach
</table>

@endsection
