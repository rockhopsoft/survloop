<!-- resources/views/vendor/survloop/admin/systemsettings.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<div class="disNon"><iframe src="/dashboard/css-reload" ></iframe></div>

<form name="admsettings" action="/dashboard/settings" method="post">
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="sub" value="1">

<h1>System Settings</h1>
@forelse ($GLOBALS["SL"]->sysOpts as $opt => $val)
    @if (isset($settingsList[$opt]))
        <div class="mB20"><label class="w100">
            <h4 class="fL">{!! str_replace('(', '</h4><span class="fR mT20 slGrey">(', 
                    str_replace(')', ')</span>', $settingsList[$opt])) !!}
            <div class="fC"></div>
            <textarea name="sys-{{ $opt }}" class="form-control w100" style="
            @if (strpos($settingsList[$opt], 'Header Code') !== false) height: 200px; 
            @else height: 45px; @endif font-family: Courier New; ">{!! $val !!}</textarea>
        </label></div>
    @endif
@empty
@endforelse

<div class="p20"></div>

<h1>System Styles</h1>
@forelse ($sysStyles as $opt)
    @if (isset($stylesList[$opt->DefSubset]))
        <div class="mB20"><label class="row w100">
            @if ($opt->DefSubset == 'font-main') 
                <h4>{!! $stylesList[$opt->DefSubset] !!}</h4>
                <input type="text" name="sty-{{ $opt->DefSubset }}" class="form-control"
                    value="{!! $opt->DefDescription !!}">
            @else 
                <div class="col-md-2" style="background: {!! $opt->DefDescription !!};">
                    <img src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/spacer.gif" 
                        border=0 height=60 width=1 >
                </div>
                <div class="col-md-10">
                <h4>{!! $stylesList[$opt->DefSubset] !!}</h4>
                <input type="text" name="sty-{{ $opt->DefSubset }}" class="form-control"
                    value="{!! $opt->DefDescription !!}">
                </div>
             @endif
        </label></div>
    @endif
@empty
@endforelse
<div class="mB20"><label class="w100">
    <h2>Open-Ended Custom CSS:</h2>
    <textarea name="sys-cust-css" class="form-control" 
        style="height: 400px; font-family: Courier New;">{!! $custCSS->DefDescription !!}</textarea>
</label></div>

<div class="p20"></div>

<input type="submit" class="btn btn-lg btn-primary p20 f24" value="Save All Settings Changes">

</form>
<div class="p20"></div><div class="p20"></div>

@endsection