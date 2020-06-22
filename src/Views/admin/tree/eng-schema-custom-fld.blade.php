
<div class="disBlo">
	<h4>{!! $labelEng !!}</h4>
	<p>{!! $desc !!}</p>
@if ($GLOBALS["SL"]->REQ->has('print'))
	<p>
		<span class="slGrey">Label:</span> {{ $tblPrefix . $label }}
	</p><p>
		<span class="slGrey">Type:</span> 
		{{ ucfirst(str_replace('xs:', '', $elemType)) }}
	</p>
@else
	<div class="clearfix pB15">
		<div class="pull-left slGrey schema-fld-label">Label:</div>
		<div class="pull-left">{{ $tblPrefix . $label }}</div>
	</div>
	<div class="clearfix pB15">
		<div class="pull-left slGrey schema-fld-label">Type:</div> 
		<div class="pull-left">{{ ucfirst(str_replace('xs:', '', $elemType)) }}</div>
	</div>
@endif
@if (sizeof($enums) > 0)
	<div class="slGrey pB15">Response Options:</div>
	<ul>
	@foreach ($enums as $enum) 
		<li>{{ $enum }}</li>
	@endforeach
	</ul>
@endif
</div>
@if ($GLOBALS["SL"]->REQ->has('print'))
	<p><br /></p>
@endif
