<!-- resources/views/vendor/survloop/inc-report-deets.blade.php -->
<div class="reportBlock" @if (isset($nID)) id="repNode{{ $nID }}" @endif >
    @if (isset($blockName) && trim($blockName) != '')
        <div class="reportSectHead">{!! $blockName !!}</div>
    @endif
    @if (isset($deets) && sizeof($deets) > 0)
        @foreach ($deets as $i => $deet) 
            <div class="row @if ($i%2 == 0) row2 @endif " @if (isset($deet[2])) id="repNode{{ $deet[2] }}" @endif  >
            @if (!isset($deet[1])) <div class="col-md-12">{!! $deet[0] !!}</div>
            @elseif (strlen($deet[1]) > 35) 
                <div class="col-md-12"><span>{!! $deet[0] !!}:</span> {!! $deet[1] !!}</div>
            @else
                <div class="col-md-6"><span>{!! $deet[0] !!}</span></div>
                <div class="col-md-6">{!! $deet[1] !!}</div>
            @endif
            </div>
        @endforeach
    @endif
</div>