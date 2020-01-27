<!-- resources/views/vendor/survloop/admin/db/field-matrix.blade.php -->

<div class="slCard nodeWrap" style="max-width: 800px;">
    <div class="row">
        <div class="col-md-8">
            <h2><span class="slBlueDark"><i class="fa fa-database"></i> {{ $GLOBALS['SL']->dbRow->db_name }}</span></h2>
            @if ($isAlt) Field Matrix (in English) @else Field Matrix (in Geek) @endif <br />
            {!! strip_tags($dbStats) !!}
        </div>
        <div class="col-md-4">
            <a href="/dashboard/db/field-matrix?{{ $urlParam }}print=1" 
                class="btn btn-secondary btn-block mB10" target="_blank"
                ><i class="fa fa-print"></i> Print Matrix</a>
            <a href="/dashboard/db/field-matrix?{{ $urlParam }}excel=1" 
                class="btn btn-secondary btn-block mB10"
                ><i class="fa fa-file-excel-o"></i> Matrix to Excel</a>
        </div>
    </div>
</div>

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
        <td class="p5"><nobr> 
        @if (isset($row[$r])) {!! $row[$r] !!} @else &nbsp; @endif
        </nobr></td>
    @endforeach
    </tr>
@endfor
</table>
<style> body { overflow-x: visible; } </style>