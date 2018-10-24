<!-- resources/views/vendor/survloop/admin/systems-check.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<div class="fL"><h2><i class="fa fa-heartbeat"></i> Check</h2></div>
<div class="fR pT20"><a href="?testEmail=1">Test Email</a></div>
<div class="fC"></div>

<h3 style="mT0">If these pages are not throwing error messages, or redirecting home, then that is a good thing...</h3>

<div class="row mB20">
    @forelse ($sysChks as $i => $chk)
        @if ($i > 0 && $i%4 == 0) </div><div class="row mB20"> @endif
        <div class="col-3">
            <a href="{{ $chk[1] }}" target="_blank"><h4>{{ $chk[0] }}</h4></a>
            <div id="chk{{ $i }}ID"></div>
            
        </div>
    @empty
    @endforelse
</div>

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