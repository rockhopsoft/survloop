@if (!$innerOnly)
    <table class="table table-striped w100">
@endif
@forelse ($rows as $i => $row)
    @if ($isExcel) <tr>
    @else
        <tr id="tblR{{ $i }}" class="
            @if ($row->cls != '') {{ $row->cls }} @endif
    	    @if (in_array($i, $lineRows)) brdBotGrey @endif ">
    @endif
    @forelse ($row->cols as $j => $cell)
    	@if ($isExcel)
        	{!! $cell->toExcel($i, $j) !!}
        @else
        	{!! $cell->toTable($i, $j) !!}
        @endif
    @empty
    @endforelse
    </tr>
@empty
@endforelse
@if (!$innerOnly)
    </table>
@endif
