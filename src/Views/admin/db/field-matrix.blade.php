<!-- resources/views/vendor/survloop/admin/db/field-matrix.blade.php -->

<h1>
	<span class="slBlueDark"><i class="fa fa-database"></i> 
	{{ $GLOBALS["DB"]->dbRow->DbName }}</span>:
	@if ($isAlt) Field Matrix (in English) @else Field Matrix (in Geek) @endif
	<nobr><span class="f14">({!! strip_tags($dbStats) !!})</span></nobr>
</h1>

@if ($isAlt) <a href="/dashboard/db/field-matrix" class="btn btn-default mR10"><i class="fa fa-random"></i> Matrix in Geek</a>
@else <a href="/dashboard/db/field-matrix?alt=1" class="btn btn-default mR10"><i class="fa fa-random"></i> Matrix in Engligh</a>
@endif
<a href="/dashboard/db/field-matrix?{{ $urlParam }}print=1" target="_blank" class="btn btn-default mR10"><i class="fa fa-print"></i> Print Matrix</a>
<a href="/dashboard/db/field-matrix?{{ $urlParam }}excel=1" class="btn btn-default mR10"><i class="fa fa-file-excel-o"></i> Matrix to Excel</a>

<div class="clearfix p20"></div>

{!! $dbStats !!}
<i class="fa fa-link"></i> Foreign Keys<br />
<table class="table table-striped">
<tr>
@foreach ($matrix as $row)
	<th class="p5">{!! $row[0] !!}</th>
@endforeach 
</tr>
@for ($r=1; $r < $max; $r++)
	<tr>
	@foreach ($matrix as $row) 
		<td class="p5"><nobr> @if (isset($row[$r])) {!! $row[$r] !!} @else &nbsp; @endif </nobr></td>
	@endforeach
	</tr>
@endfor
</table>
