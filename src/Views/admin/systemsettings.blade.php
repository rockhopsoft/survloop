<!-- resources/views/vendor/survloop/admin/systemsettings.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

<form name="admsettings" action="/dashboard/settings" method="post">
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="sub" value="1">

<h1>System Settings</h1>
@forelse ($GLOBALS["SL"]->sysOpts as $opt => $val)
    @if (isset($settingsList[$opt]))
        <div class="mB20"><label class="w100">
            <h3>{!! str_replace('(', '<span class="gry9">(', str_replace(')', ')</span>', $settingsList[$opt])) !!}</h3>
            <textarea name="sys-{{ $opt }}" class="form-control" style="height: 45px;">{!! $val !!}</textarea>
        </label></div>
    @endif
@empty
@endforelse

<div class="p20"></div>

<h1>System Styles</h1>
@forelse ($sysStyles as $opt)
    @if (isset($stylesList[$opt->DefSubset]))
        <div class="mB20"><label class="w100">
            <h3>{!! $stylesList[$opt->DefSubset] !!}</h3>
            <input type="text" name="sty-{{ $opt->DefSubset }}" class="form-control" 
                value="{!! $opt->DefDescription !!}">
        </label></div>
    @endif
@empty
@endforelse

<div class="p20"></div>

<input type="submit" class="btn btn-lg btn-primary p20 f24" value="Save All Settings Changes">

</form>
<div class="p20"></div><div class="p20"></div>

@endsection