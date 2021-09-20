<!-- resources/views/vendor/survloop/admin/systems-clean.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<div class="container">
    <div class="slCard nodeWrap">
    	<a href="?refresh=1" class="btn btn-secondary pull-right"
    		>Force Cleanup</a>
    	<h2>
    		<i class="fa fa-bath" aria-hidden="true"></i>
    		System Cleanup
    	</h2>
		<div id="cleaningDiv" class="pT30">
	        {!! view(
	            'vendor.survloop.admin.systems-clean-ajax',
	            [ "currStep" => $currStep ]
	        )->render() !!}
	    </div>
	    <div class="pB30 taC">
	    	{!! $GLOBALS["SL"]->spinner() !!}
	    </div>
    </div>
</div>

@endsection