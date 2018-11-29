<!-- resources/views/vendor/survloop/admin/system-all-settings.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<div class="disNon"><iframe src="/dashboard/css-reload" ></iframe></div>

<form name="mainPageForm" action="/dashboard/settings" method="post">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="sub" value="1">

<div class="nodeAnchor"><a id="search" name="search"></a></div>

<div class="fR taR slGrey fPerc80"> {!! str_replace('Current IP Address: ', 'Server IP Address:<br />',
    file_get_contents('http://checkip.dyndns.com/')) !!}
</div>

<h1 class="slBlueDark"><i class="fa fa-cogs"></i> System Settings</h1>
<a href="#search" class="hshoo">Search Engine Optimization</a> - 
<a href="#general" class="hshoo">SurvLoop Configuration</a> - 
<a href="#survopts" class="hshoo">SurvLoop Settings</a> - 
<a href="#social" class="hshoo">Social Media</a> - 
<a href="#license" class="hshoo">Licenses</a> - 
<a href="#logos" class="hshoo">Logos & Fonts</a> - 
<a href="#color" class="hshoo">Colors</a> - 
<a href="#hardcode" class="hshoo">Hard Code HTML CSS JS</a> - 
<a href="#custom" class="hshoo">Custom Settings</a>
<div class="fC"></div>
<p>&nbsp;</p>

<h2 class="mB10">Search Engine Optimization</h2>
<div class="row">
    <div class="col-8">
        {!! view('vendor.survloop.admin.seo-meta-editor', [ "currMeta" => $currMeta ])->render() !!}
    </div><div class="col-4">
        <h3 class="slBlueDark" style="margin-top: -40px;">Social Sharing Preview</h3>
        {!! view('vendor.survloop.admin.seo-meta-editor-preview', [])->render() !!}
    </div>
</div>

<div class="p20"></div>
<div class="nodeAnchor"><a id="general" name="general"></a></div>
<hr>
<h2>General Settings</h2>
<h3 class="slBlueDark"><u>SurvLoop Configurations</u></h3>
<div class="row">
    <div class="col-md-6">
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'site-name', "val" => $settingsList["site-name"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'cust-abbr', "val" => $settingsList["cust-abbr"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'cust-package', "val" => $settingsList["cust-package"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'parent-company', "val" => $settingsList["parent-company"] ])->render() !!}
    </div><div class="col-md-6">
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'app-url', "val" => $settingsList["app-url"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'logo-url', "val" => $settingsList["logo-url"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'parent-website', "val" => $settingsList["parent-website"] ])->render() !!}
    </div>
</div>
<div class="nodeAnchor"><a id="survopts" name="survopts"></a></div>
<div class="p20"></div>
<h3 class="slBlueDark"><u>SurvLoop Settings</u></h3>
<div class="row">
    <div class="col-md-6">
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'has-volunteers', "val" => $settingsList["has-volunteers"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'has-partners', "val" => $settingsList["has-partners"] ])->render() !!}
    </div><div class="col-md-6">
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'has-canada', "val" => $settingsList["has-canada"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'users-create-db', "val" => $settingsList["users-create-db"] ])->render() !!}
    </div>
</div>
<div class="nodeAnchor"><a id="social" name="social"></a></div>
<div class="p20"></div>
<h3 class="slBlueDark"><u>Social Settings</u></h3>
<div class="row">
    <div class="col-md-6">
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'twitter', "val" => $settingsList["twitter"] ])->render() !!}
    </div>
    <div class="col-md-6">
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'google-analytic', "val" => $settingsList["google-analytic"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'google-map-key', "val" => $settingsList["google-map-key"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'google-map-key2', "val" => $settingsList["google-map-key2"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'google-cod-key', "val" => $settingsList["google-cod-key"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'google-cod-key2', "val" => $settingsList["google-cod-key2"] ])->render() !!}
    </div>
</div>
<div class="nodeAnchor"><a id="license" name="license"></a></div>
<div class="p20"></div>
<h3 class="slBlueDark"><u>License Settings</u></h3>
<div class="row">
    <div class="col-md-6">
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'app-license', "val" => $settingsList["app-license"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'app-license-url', "val" => $settingsList["app-license-url"] ])->render() !!}
    </div><div class="col-md-6">
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'app-license-img', "val" => $settingsList["app-license-img"] ])->render() !!}
    </div>
</div>

<div class="p20"></div>
<div class="p20"></div>
<hr>
<div class="nodeAnchor"><a id="logos" name="logos"></a></div>
<h2>Logos & Fonts</h2>
<h3 class="slBlueDark"><u>Logos</u></h3>
<div class="row">
    <div class="col-md-6">
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'logo-img-lrg', "val" => $settingsList["logo-img-lrg"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'logo-img-md', "val" => $settingsList["logo-img-md"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'logo-img-sm', "val" => $settingsList["logo-img-sm"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'show-logo-title', "val" => $settingsList["show-logo-title"] ])->render() !!}
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'shortcut-icon', "val" => $settingsList["shortcut-icon"] ])->render() !!}
    </div>
    <div class="col-md-6">
        <h3 class="slBlueDark"><u>Fonts</u></h3>
        {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
            "opt" => 'font-main', "val" => $stylesList["font-main"] ])->render() !!}
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'spinner-code', "val" => ((isset($settingsList["spinner-code"]))
            ? $settingsList["spinner-code"] : '') ])->render() !!}
    </div><div class="col-md-6">
        @if (isset($GLOBALS["SL"]->sysOpts["spinner-code"])) {!! $GLOBALS["SL"]->sysOpts["spinner-code"] !!} @endif
    </div>
</div>

<div class="p20"></div>
<div class="p20"></div>
<hr>
<div class="nodeAnchor"><a id="color" name="color"></a></div>
<div class="row">
    <div class="col-8">
    
        <div class="fR pT20 slGrey"><i>BG = Background</i></div>
        <h2><u>Colors</u></h2>
        <div class="row fC">
            <div class="col-md-6">
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-main-bg', "val" => $stylesList["color-main-bg"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-main-text', "val" => $stylesList["color-main-text"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-main-link', "val" => $stylesList["color-main-link"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-main-grey', "val" => $stylesList["color-main-grey"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-main-faint', "val" => $stylesList["color-main-faint"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-main-faintr', "val" => $stylesList["color-main-faintr"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-field-bg', "val" => $stylesList["color-field-bg"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-form-text', "val" => $stylesList["color-form-text"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-line-hr', "val" => $stylesList["color-line-hr"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-logo', "val" => $stylesList["color-logo"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-nav-bg', "val" => $stylesList["color-nav-bg"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-nav-text', "val" => $stylesList["color-nav-text"] ])->render() !!}
            </div>
            <div class="col-md-6">
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-main-on', "val" => $stylesList["color-main-on"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-main-off', "val" => $stylesList["color-main-off"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-info-on', "val" => $stylesList["color-info-on"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-info-off', "val" => $stylesList["color-info-off"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-success-on', "val" => $stylesList["color-success-on"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-success-off', "val" => $stylesList["color-success-off"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-danger-on', "val" => $stylesList["color-danger-on"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-danger-off', "val" => $stylesList["color-danger-off"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-warn-on', "val" => $stylesList["color-warn-on"] ])->render() !!}
                {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysStyles,
                    "opt" => 'color-warn-off', "val" => $stylesList["color-warn-off"] ])->render() !!}
            </div>
        </div>

    </div>
    <div class="col-4">
        <div id="previewColors"></div>
    </div>
</div>
    

<div class="nodeAnchor"><a id="hardcode" name="hardcode"></a></div>
<div class="p20"></div>
<div class="p20"></div>
<hr>
<h2>Hard Code HTML, CSS, JS</h2>
{!! view('vendor.survloop.admin.system-one-setting', [
    "opt" => 'header-code', "val" => $settingsList["header-code"] ])->render() !!}
{!! view('vendor.survloop.admin.system-one-setting', [
    "opt" => 'css-extra-files', "val" => $settingsList["css-extra-files"] ])->render() !!}
    
<div class="mB20"><label class="w100">
    <h4 class="m0">Open-Ended Custom CSS:</h4>
    <textarea name="sys-cust-css" class="form-control" autocomplete="off"
        style="height: 400px; font-family: Courier New;">{!! $custCSS->DefDescription !!}</textarea>
</label></div>
<div class="mB20"><label class="w100">
    <h4 class="m0">Custom CSS for Emails:</h4>
    <textarea name="sys-cust-css-email" class="form-control" autocomplete="off" 
        style="height: 200px; font-family: Courier New;">{!! $custCSSemail->DefDescription !!}</textarea>
</label></div>

@if (sizeof($rawSettings) > 0)
    <div class="nodeAnchor"><a id="custom" name="custom"></a></div>
    <div class="p20"></div>
    <div class="p20"></div>
    <hr>
    <h2>Custom Settings</h2>
    <div class="row">
        <div class="col-md-6">
            @foreach ($rawSettings as $i => $s)
                <div class="f22">{{ $s->setting }}</div>
                <label class="mL20">
                    <input type="radio" name="setting{{ $i }}" value="Y"
                        @if ($s->val == 'Y') CHECKED @endif
                        > Yes
                </label>
                <label class="mL20">
                    <input type="radio" name="setting{{ $i }}" value="N"
                        @if ($s->val == 'N') CHECKED @endif
                        > No
                </label>
                @if ($i == ceil(sizeof($rawSettings)/2))
                    </div><div class="col-md-6">
                @endif
            @endforeach
        </div>
    </div>
@endif

<div class="p20"></div><div class="p20"></div>

<input type="submit" class="btn btn-xl btn-primary w100" value="Save All Settings Changes">

</form>
<div class="p20"></div><div class="p20"></div>

@endsection