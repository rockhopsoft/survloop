<!-- resources/views/vendor/survloop/admin/systems-check-cache.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<div class="container">
<div class="slCard nodeWrap">
<h2><i class="fa fa-heartbeat"></i> Check Email</h2>

<div class="p20"><br /></div>

<form name="mainPageForm" method="post" action="?testCache=1&sendTest=1" >
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="text" name="cacheVal" value="" autocomplete="off">
<input type="submit" value="Store Test Cache" class="btn btn-primary">
</form>

<div class="p20"><br /></div>

<h4>Test Cache:</h4>
@if (isset($testCache)) <h4>{!! $testCache !!}</h4> @endif

</div></div>

@endsection