<!-- resources/views/vendor/survloop/admin/db/tableView.blade.php -->

@extends('vendor.survloop.admin.admin')

@section('content')

<h1 class="fL"><i class="fa fa-database"></i> Table: {{ $tbl->TblEng }}</h1>
<div class="fR taR pT20 f20 gry6">
	<b>{{ $tbl->TblName }}</b> 
	({{ $tbl->TblAbbr }}) 
	<div class="gry6"><i>{{ $tbl->TblType }}</i></div>
</div>
<div class="fC"></div>


@if (!$isPrint)
	<a href="/admin/db?print=1" target="_blank" class="btn btn-xs btn-default mR10"
		><i class="fa fa-print"></i> Print This Overview</a>
	@if ($dbAllowEdits)
		<a href="/dashboard/db/table/{{ $tblName }}/edit" class="btn btn-xs btn-default mR10"
			><nobr><i class="fa fa-pencil"></i> Edit Table Properties</nobr></a>
		<a href="/dashboard/db/field/{{ $tbl->TblAbbr }}" class="btn btn-xs btn-default mR10"
			><i class="fa fa-plus"></i> Add a New Field</a>
		<a href="/dashboard/db/table/{{ $tblName }}/sort" class="btn btn-xs btn-default"
			><i class="fa fa-sort-amount-asc"></i> Re-Order Fields</a>
	@endif
@endif

<div class="clearfix p5"></div>

<div class="pB10 f14">{!! $tbl->TblDesc !!}</div>
@if (trim($tbl->TblNotes) != '')
	<div class="pB10 f14 gry6"><i>Notes: {!! $tbl->TblNotes !!}</i></div>
@endif
@if ($rules && sizeof($rules) > 0)
	@foreach ($rules as $rule)
		<div class="pB10">
			<a href="/dashboard/db/bus-rules/edit/{{ $rule->RuleID }}" 
				target="_blank" class="gry6 f12"><i class="fa fa-university"></i> 
				<i>{!! $rule->RuleStatement !!}</i></a>
		</div>
	@endforeach
@endif
@if (trim($foreignsFlds) != '')
	<div class="pB10">
		<i class="fa fa-link"></i> {{ sizeof($flds) }} 
		Tables with Foreign Keys Incoming: {!! $foreignsFlds !!}
	</div>
@endif

{!! $basicTblFlds !!}

<div class="adminFootBuff"></div>

@endsection
