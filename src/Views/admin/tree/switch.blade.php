<!-- Stored in resources/views/vender/survloop/admin/tree/switch.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

<h1>Switch Current User Experience To Design</h1>
<hr>

@forelse ($myTrees as $tree)
	<div class="row">
		<div class="col-md-3 pB10">
			@if ($GLOBALS["DB"]->treeID == $tree->TreeID)
				<a href="javascript:void(0)" class="btn btn-lg btn-primary" DISABLED 
					><i class="fa fa-star mR10" aria-hidden="true"></i> Current User Experience</a>
			@else
				<a href="/dashboard/db/switch/{{ $tree->TreeID }}" class="btn btn-lg btn-default"
					><i class="fa fa-arrow-right mR10" aria-hidden="true"></i> Design This User Experience</a>
			@endif
		</div>
		<div class="col-md-9">
			<h2 class="mT0 @if ($GLOBALS['DB']->treeID == $tree->TreeID) slBlueDark @endif " >{{ $tree->TreeName }}</h2>
			<p><b>{{ $tree->TreeDesc }}</b></p>
			<p>{{ $myTreeNodes[$tree->TreeID] }} Nodes</p>
		</div>
	</div>
	<hr>
@empty
	<i>Sorry, no experiences found.</i> <a href="/fresh/database">Click here to create one</a>.
@endforelse

<div class="row">
	<div class="col-md-3 pB10">
		<a href="/dashboard/tree/new/" class="btn btn-lg btn-default"
			>Create New User Experience</a>
	</div>
	<div class="col-md-9"></div>
</div>
<hr>

@endsection