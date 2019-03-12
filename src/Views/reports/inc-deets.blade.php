<!-- resources/views/vendor/survloop/reports/inc-deets.blade.php -->
@if (isset($blockName) && trim($blockName) != '')
    <h3 class="mT0 mB10 slBlueDark">{!! $blockName !!}</h3>
@endif
<table class="repDeetsBlock" @if (isset($nID)) id="repNode{{ $nID }}" @endif >
@if (isset($deets) && sizeof($deets) > 0)
    @foreach ($deets as $i => $deet) 
        {!! view('vendor.survloop.reports.inc-deets-row', [ "i" => $i, "deet" => $deet ])->render() !!}
    @endforeach
@endif
</table>