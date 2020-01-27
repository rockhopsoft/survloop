<!-- resources/views/vendor/survloop/admin/systemsettings.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<div class="disNon"><iframe src="/css-reload" ></iframe></div>

<form name="mainPageForm" action="/dashboard/settings" method="post">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="sub" value="1">

<div class="row">
    <div class="col-5">

        <h1>System Settings</h1>
        @forelse ($settingsList as $opt => $val)
            {!! view(
                'vendor.survloop.admin.system-one-setting', 
                [
                    "opt" => $opt, 
                    "val" => $val 
                ]
            )->render() !!}
        @empty
        @endforelse
        
        @if ($rawSettings->isNotEmpty())
            <br /><br /><h2>Custom Settings</h2>
            @foreach ($rawSettings as $i => $s)
                <h4 class="m0">{{ $s->setting }}</h4>
                <label class="mL20">
                    <input type="radio" name="setting{{ $i }}" value="Y" 
                        @if ($s->val == 'Y') CHECKED @endif > Yes
                </label>
                <label class="mL20">
                    <input type="radio" name="setting{{ $i }}" value="N" 
                        @if ($s->val == 'N') CHECKED @endif > No
                </label>
            @endforeach
        @endif
        
    </div>
    <div class="col-1">
    </div>
    <div class="col-6">

        <h1>System Styles</h1>
        @forelse ($stylesList as $opt => $val)
            {!! view(
                'vendor.survloop.admin.system-one-style', 
                [ 
                    "opt" => $opt, 
                    "val" => $val, 
                    "sysStyles" => $sysStyles
                ]
            )->render() !!}
        @empty
        @endforelse
        <div class="mB20"><label class="w100">
            <h2>Open-Ended Custom CSS:</h2>
            <textarea name="sys-cust-css" class="form-control" autocomplete="off"
                style="height: 400px; font-family: Courier New;"
                >{!! $custCSS->def_description !!}</textarea>
        </label></div>
        <div class="mB20"><label class="w100">
            <h4>Open-Ended Custom CSS for Emails:</h4>
            <textarea name="sys-cust-css-email" class="form-control" autocomplete="off" 
                style="height: 200px; font-family: Courier New;"
                >{!! $custCSSemail->def_description !!}</textarea>
        </label></div>
    </div>
</div>

<div class="p20"></div>

<input type="submit" class="btn btn-lg btn-primary" value="Save All Settings Changes">

<div class="p20"></div>

<h3>Previews</h3>
<div class="row row2">
    <div class="col-6">
        <center><h4>Spinner Animation:</h4><div class="p5"></div>
        {!! $GLOBALS["SL"]->spinner() !!}</center>
    </div>
    <div class="col-6">
        
    </div>
</div>

</form>
<div class="p20"></div><div class="p20"></div>

@endsection