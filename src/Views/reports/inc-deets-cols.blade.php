<!-- resources/views/vendor/survloop/reports/inc-deets-cols.blade.php -->
<div class="w100" @if (isset($nID)) id="repNode{{ $nID }}" @endif >
@if (isset($blockName) && trim($blockName) != '')
    <h3 class="mT0 mB10 slBlueDark">{!! $blockName !!}</h3>
@endif
@if (isset($deetCols) && sizeof($deetCols) > 0)
    @if (in_array($GLOBALS["SL"]->pageView, ['pdf', 'full-pdf'])) <table border=0 class="w100"><tr>
    @else <div class="row"> @endif
    @foreach ($deetCols as $i => $deets) 
        @if (in_array($GLOBALS["SL"]->pageView, ['pdf', 'full-pdf']))
            <td class="vaT pL15 pR15 pB15 @if (sizeof($deetCols) == 2) w50 @elseif (sizeof($deetCols) == 3) w33 
                @elseif (sizeof($deetCols) == 4) w25 @elseif (sizeof($deetCols) == 5) w20 @endif ">
        @else <div class="col-md-{{ $GLOBALS['SL']->getColsWidth(sizeof($deetCols)) }}"> @endif
            <table class="repDeetsBlock">
            @foreach ($deets as $j => $deet)
                @if (isset($deet[0]) && trim($deet[0]) != '')
                   <?php if ($j == 0) { $cnt = $i%2; } else { $cnt++; } ?>
                   {!! view('vendor.survloop.reports.inc-deets-row', [ "i" => $cnt, "deet" => $deet ])->render() !!}
                @endif
            @endforeach
            </table>
        @if (in_array($GLOBALS["SL"]->pageView, ['pdf', 'full-pdf'])) </td> @else </div> @endif
    @endforeach
    @if (in_array($GLOBALS["SL"]->pageView, ['pdf', 'full-pdf'])) </tr></table>
    @else </div> @endif
@endif
</div>