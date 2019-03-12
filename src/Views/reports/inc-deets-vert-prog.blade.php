<!-- resources/views/vendor/survloop/reports/inc-deets-vert-prog.blade.php -->
<div class="w100" @if (isset($nID)) id="repNode{{ $nID }}" @endif >
    @if (isset($blockName) && trim($blockName) != '')
        <h3 class="mT0 mB10 slBlueDark">{!! $blockName !!}</h3>
    @endif
    <table border=0 class="w100 brdNo repDeetVert">
    @foreach ($deets as $j => $deet)
        <tr><td class="vaT taC">
            @if ($j == $last || isset($deet[1]) && intVal($deet[1]) > 0) <div class="vertPrgDone">
            @else <div class="vertPrgFutr"> @endif
            <img src="/survloop/uploads/spacer.gif" border=0 alt="" ></div>
        </td><td class="vaT">
            @if (trim($deet[0])) {{ $deet[0] }} @endif
        </td><td class="vaT taR fPerc80 slGrey">
            @if (trim($deet[1]) && intVal($deet[1]) > 0)
                <div class="mT3">{{ date(($GLOBALS["SL"]->x["pageView"] == 'public') 
                    ? 'F Y' : 'n/j/y', $deet[1]) }}</div>
            @endif
        </td></tr>
    @endforeach
    </table>
</div>