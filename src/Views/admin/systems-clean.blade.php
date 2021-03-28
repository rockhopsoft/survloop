<!-- resources/views/vendor/survloop/admin/systems-clean.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<div class="container">
    <h2><i class="fa fa-heartbeat"></i> System Cleanup</h2>
    <div class="slCard nodeWrap taC">
		<div id="cleaningDiv" class="pT30">
	        {!! view(
	            'vendor.survloop.admin.systems-clean-ajax',
	            [ "currStep" => $currStep ]
	        )->render() !!}
	    </div>
	    <div class="pB30">
	    	{!! $GLOBALS["SL"]->spinner() !!}
	    </div>
    </div>
</div>

@endsection