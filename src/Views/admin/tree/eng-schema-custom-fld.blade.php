
<div class="row">
	<div class="col-8">
		<h4>{!! $labelEng !!}</h4>
		<p>
			{{ $tblPrefix . $label }}<br />
			{{ ucfirst(str_replace('xs:', '', $elemType)) }}
		</p>
		<p>{!! $desc !!}</p>
	</div>
	<div class="col-4">
	@if (sizeof($enums) > 0)
		Possible Values:
		<ul>
		@foreach ($enums as $enum) 
			<li>{{ $enum }}</li>
		@endforeach
		</ul>
	@endif
	</div>
</div>
