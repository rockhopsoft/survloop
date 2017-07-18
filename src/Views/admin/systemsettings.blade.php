<!-- resources/views/vendor/survloop/admin/systemsettings.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<div class="disNon"><iframe src="/dashboard/css-reload" ></iframe></div>

<form name="admsettings" action="/dashboard/settings" method="post">
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="sub" value="1">

<div class="row">
    <div class="col-md-5">

        <h1>System Settings</h1>
        @forelse ($GLOBALS["SL"]->sysOpts as $opt => $val)
            @if (isset($settingsList[$opt]))
                <div class="mB20 mT10 w100"><label class="w100">
                    <h4 class="fL m0">{!! $settingsList[$opt][0] !!}</h4>
                    @if (trim($settingsList[$opt][1]) != '')
                        <div class="fR pT5 slGrey">{!! $settingsList[$opt][1] !!}</div>
                    @endif
                    <div class="fC"></div>
                    <textarea name="sys-{{ $opt }}" class="form-control w100" style="
                    @if (strpos($settingsList[$opt][0], 'Header Code') !== false) height: 200px; 
                    @else height: 75px; @endif font-family: Courier New; ">{!! $val !!}</textarea>
                </label></div>
            @endif
        @empty
        @endforelse
        
        <br /><br />
        
        <h2>Custom Settings</h2>
        @if (isset($rawSettings) && sizeof($rawSettings) > 0)
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
            @endforeach
        @endif
        
    </div>
    <div class="col-md-1">
    </div>
    <div class="col-md-6">

        <h1>System Styles</h1>
        @forelse ($sysStyles as $opt)
            @if (isset($stylesList[$opt->DefSubset]))
                @if ($opt->DefSubset == 'font-main') 
                    <div class="mB20 mT10 w100"><label class="w100">
                        <h4 class="fL m0">{!! $stylesList[$opt->DefSubset][0] !!}</h4>
                        @if (trim($stylesList[$opt->DefSubset][1]) != '')
                            <div class="fR pT5 slGrey">{!! $stylesList[$opt->DefSubset][1] !!}</div>
                        @endif
                        <input type="text" name="sty-{{ $opt->DefSubset }}" class="form-control"
                            value="{!! $opt->DefDescription !!}">
                    </label></div>
                @else
                    <div class="mB20 mT10 w100"><label class="w100">
                        <h4 class="fL m0">{!! $stylesList[$opt->DefSubset][0] !!}</h4>
                        @if (trim($stylesList[$opt->DefSubset][1]) != '')
                            <div class="fR pT5 slGrey slShadeLight">{!! $stylesList[$opt->DefSubset][1] !!}</div>
                        @endif
                        </label>
                        {!! view('vendor.survloop.inc-color-picker', [
                            'fldName' => 'sty-' . $opt->DefSubset,
                            'preSel'  => strtoupper($opt->DefDescription)
                        ])->render() !!}
                    </div>
                @endif
            @endif
        @empty
        @endforelse
        <div class="mB20"><label class="w100">
            <h2>Open-Ended Custom CSS:</h2>
            <textarea name="sys-cust-css" class="form-control" 
                style="height: 400px; font-family: Courier New;">{!! $custCSS->DefDescription !!}</textarea>
        </label></div>
        <div class="mB20"><label class="w100">
            <h4>Open-Ended Custom CSS for Emails:</h4>
            <textarea name="sys-cust-css-email" class="form-control" 
                style="height: 200px; font-family: Courier New;">{!! $custCSSemail->DefDescription !!}</textarea>
        </label></div>
    </div>
</div>

<div class="p20"></div>

<input type="submit" class="btn btn-lg btn-primary p20 f24" value="Save All Settings Changes">

</form>
<div class="p20"></div><div class="p20"></div>

@endsection