<!-- resources/views/vendor/survloop/admin/systemsettings.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<div class="disNon"><iframe src="/dashboard/css-reload" ></iframe></div>

<form name="mainPageForm" action="/dashboard/settings" method="post">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="sub" value="1">

<div class="row">
    <div class="col-md-5">

        <h1>System Settings</h1>
        @forelse ($settingsList as $opt => $val)
            <div class="mB20 mT10 w100"><label class="w100">
                <h4 class="fL m0">{!! $val[0] !!}</h4>
                @if (trim($val[1]) != '')
                    <div class="fR pT5 slGrey">eg. "{!! $val[1] !!}"</div>
                @endif
                <div class="fC"></div>
                <textarea name="sys-{{ $opt }}" class="form-control w100" autocomplete="off" style="
                @if (strpos($val[0], 'Header Code') !== false) height: 200px; 
                @else height: 75px; @endif font-family: Courier New; "
                >@if (isset($GLOBALS["SL"]->sysOpts[$opt])){!! $GLOBALS["SL"]->sysOpts[$opt] !!}@endif</textarea>
            </label></div>
        @empty
        @endforelse
        
        @if (isset($rawSettings) && sizeof($rawSettings) > 0)
            <br /><br /><h2>Custom Settings</h2>
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
        @forelse ($stylesList as $opt => $val)
            @if ($opt == 'font-main') 
                <div class="mB20 mT10 w100"><label class="w100">
                    <h4 class="fL m0">{!! $val[1] !!}</h4>
                    @if (trim($val[0]) != '') <div class="fR pT5 slGrey">eg. "{!! $val[0] !!}"</div> @endif
                    <textarea name="sty-{{ $opt }}" class="form-control" style="height: 40px;"
                        >@if (isset($sysStyles[$opt])){!! $sysStyles[$opt] !!}@endif</textarea>
                </label></div>
            @else
                <div class="mB20 mT10 w100">
                    <label class="w100"><h4 class="fL m0">{!! $val[1] !!}</h4>
                    @if (trim($val[0]) != '')
                        <div class="fR pT5 slGrey slShdLgt"><span style="color: {!! $val[0] !!};">
                        eg. "{!! $val[0] !!}"</span></div>
                    @endif
                    </label>
                    {!! view('vendor.survloop.inc-color-picker', [
                        'fldName' => 'sty-' . $opt,
                        'preSel'  => strtoupper($sysStyles[$opt])
                    ])->render() !!}
                </div>
            @endif
        @empty
        @endforelse
        <div class="mB20"><label class="w100">
            <h2>Open-Ended Custom CSS:</h2>
            <textarea name="sys-cust-css" class="form-control" autocomplete="off"
                style="height: 400px; font-family: Courier New;">{!! $custCSS->DefDescription !!}</textarea>
        </label></div>
        <div class="mB20"><label class="w100">
            <h4>Open-Ended Custom CSS for Emails:</h4>
            <textarea name="sys-cust-css-email" class="form-control" autocomplete="off" 
                style="height: 200px; font-family: Courier New;">{!! $custCSSemail->DefDescription !!}</textarea>
        </label></div>
    </div>
</div>

<div class="p20"></div>

<input type="submit" class="btn btn-lg btn-primary p20 f24" value="Save All Settings Changes">

<div class="p20"></div>

<h3>Previews</h3>
<div class="row row2">
    <div class="col-md-6">
        <center><h4>Spinner Animation:</h4><div class="p5"></div>
        {!! $GLOBALS["SL"]->sysOpts["spinner-code"] !!}</center>
    </div>
    <div class="col-md-6">
        
    </div>
</div>

</form>
<div class="p20"></div><div class="p20"></div>

@endsection