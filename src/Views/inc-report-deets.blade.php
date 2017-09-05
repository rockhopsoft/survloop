<!-- resources/views/vendor/survloop/inc-report-deets.blade.php -->
<div class="reportBlock">
    @if (isset($blockName) && trim($blockName) != '')
        <div class="reportSectHead">{!! $blockName !!}</div>
    @endif
    @if (isset($deets) && sizeof($deets) > 0)
        @foreach ($deets as $i => $deet) 
            <div class="row @if ($i%2 == 0) row2 @endif " >
            @if (!isset($deet[1])) <div class="col-md-12">{!! $deet[0] !!}</div>
            @else
                <div class="col-md-6"><span>{!! $deet[0] !!}</span></div>
                <div class="col-md-6">{!! $deet[1] !!}</div>
            @endif
            </div>
        @endforeach
    @endif
</div>