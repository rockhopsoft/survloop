<!-- resources/views/vendor/survloop/admin/systems-check.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">
<div class="slCard nodeWrap">
    <div class="pull-right btn btn-secondary btn-sm m5"><a href="?testEmail=1">Test Email</a></div>
    <div class="pull-right btn btn-secondary btn-sm m5"><a href="?testCache=1">Test Cache</a></div>
    <h2><i class="fa fa-heartbeat"></i> Check</h2>
    <p>If these pages are not throwing error messages, or redirecting home, then that is a good thing...</p>
</div>

<div class="row mB20">
    @forelse ($sysChks as $i => $chk)
        @if ($i > 0 && $i%3 == 0) </div><div class="row"> @endif
        <div class="col-md-4"><div class="slCard nodeWrap">
            <a href="{{ $chk[1] }}" target="_blank"><h4>{{ $chk[0] }}</h4></a>
            <div id="chk{{ $i }}ID"></div>
        </div></div>
    @empty
    @endforelse
</div>

@if (Auth::user() && Auth::user()->hasRole('administrator')) 
    <div class="slCard nodeWrap">{!! phpinfo() !!}</div>
@endif

<script type="text/javascript">
function loadChk(i, url) {
    document.getElementById('chk'+i+'ID').innerHTML='<iframe src="{{ $GLOBALS["SL"]->sysOpts["app-url"] }}'+url+'" border=0 width=100% height=300 ></iframe>';
    return true;
}
@forelse ($sysChks as $i => $chk)
    setTimeout("loadChk({{ $i }}, '{{ $chk[1] }}')", {{ (3000*$i) }});
@empty
@endforelse
</script>

@endsection