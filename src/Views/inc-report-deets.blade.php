<!-- resources/views/vendor/survloop/inc-report-deets.blade.php -->
<div class="reportBlock">
    @if (isset($blockName) && trim($blockName) != '')
        <div class="reportSectHead">{!! $blockName !!}</div>
    @endif
    @if (isset($deets) && sizeof($deets) > 0)
        <table class="table">
        @foreach ($deets as $i => $deet) 
            @if (!isset($deet[1])) <tr><td colspan=2 >{!! $deet[0] !!}</td></tr>
            @else <tr><td><span>{!! $deet[0] !!}</span></td><td>{!! $deet[1] !!}</td></tr> @endif
        @endforeach
        </table>
    @endif
</div>