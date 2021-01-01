<!-- resources/views/vendor/survloop/reports/inc-deets-cols.blade.php -->
<div class="w100" @if (isset($nIDtxt)) id="repNode{{ $nIDtxt }}" @endif >
@if (isset($blockName) && trim($blockName) != '')
    <h4 class="mT0 mB10 slBlueDark">{!! $blockName !!}</h4>
@endif
@if (isset($blockDesc) && trim($blockDesc) != '')
    <div class="mB10">{!! $blockDesc !!}</div>
@endif
@if (isset($deetCols) && sizeof($deetCols) > 0)
    <div class="row repDeetsCols">
    @foreach ($deetCols as $i => $deets) 
        <div class="col-md-{{ $GLOBALS['SL']->getColsWidth(sizeof($deetCols)) }}">
            <table class="repDeetsBlock">
            @foreach ($deets as $j => $deet)
                @if (isset($deet[0]) && trim($deet[0]) != '')
                    <?php if ($j == 0) { $cnt = $i%2; } else { $cnt++; } ?>
                    {!! view(
                        'vendor.survloop.reports.inc-deets-row', 
                        [ 
                            "i"    => $cnt, 
                            "deet" => $deet 
                        ]
                    )->render() !!}
                @endif
            @endforeach
            </table>
        </div>
    @endforeach
    </div>
@endif
</div>