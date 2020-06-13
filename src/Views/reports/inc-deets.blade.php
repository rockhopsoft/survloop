<!-- resources/views/vendor/survloop/reports/inc-deets.blade.php -->
@if (isset($blockName) && trim($blockName) != '')
    <h4 class="mT0 mB10 slBlueDark">{!! $blockName !!}</h4>
@endif
<table class="repDeetsBlock" @if (isset($nIDtxt)) id="repNode{{ $nIDtxt }}" @endif >
@if (isset($deets) && sizeof($deets) > 0)
    @foreach ($deets as $i => $deet) 
        {!! view(
            'vendor.survloop.reports.inc-deets-row', 
            [ 
                "i" => $i, 
                "deet" => $deet 
            ]
        )->render() !!}
    @endforeach
@endif
</table>