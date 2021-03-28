<!-- resources/views/vendor/survloop/admin/system-settings-colors.blade.php -->
@extends('vendor.survloop.admin.system-settings-ajax-form')
@section('formContent')

<input type="hidden" id="optListID" name="optList" 
    value="logo-img-lrg,logo-img-md,logo-img-sm,show-logo-title,shortcut-icon,spinner-code">
<input type="hidden" id="styListID" name="styList" 
    value="color-main-bg,color-main-text,color-main-link,color-main-grey,color-main-faint,color-main-faintr,color-field-bg,color-form-text,color-line-hr,color-nav-bg,color-nav-text,color-main-on,color-info-on,color-success-on,color-danger-on,color-warn-on,font-main">

<h3 class="mB15 slBlueDark">Colors</h3>
<table class="table table-striped">
@foreach ([
		'color-main-bg', 'color-main-text', 'color-main-link', 
		'color-main-grey', 'color-main-faint', 'color-main-faintr', 
		'color-field-bg', 'color-form-text', 'color-line-hr', 
		'color-nav-bg', 'color-nav-text', 'color-main-on', 
		'color-info-on', 'color-success-on', 
        'color-danger-on', 'color-warn-on'
    ] as $opt)
    @if (isset($sysDef->v["stylesList"][$opt]))
        {!! view(
            'vendor.survloop.admin.system-one-style', 
            [ 
                "opt"       => $opt, 
                "val"       => $sysDef->v["stylesList"][$opt],
                "sysStyles" => $sysDef->v["sysStyles"]
            ]
        )->render() !!}
    @endif
@endforeach
</table>

<div id="previewColors"></div>

<p><br /></p>

<h3 class="mT30 mB15 slBlueDark">Logos & Fonts</h3>
<table class="table table-striped">
    {!! view(
        'vendor.survloop.admin.system-one-style', 
        [
            "opt" => 'font-main', 
            "val" => $sysDef->v["stylesList"]["font-main"],
            "sysStyles" => $sysDef->v["sysStyles"]
        ]
    )->render() !!}
@foreach ([
		'logo-img-lrg', 'logo-img-md', 'logo-img-sm', 
		'show-logo-title', 'shortcut-icon', 'has-avatars', 'spinner-code'
	] as $opt)
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
