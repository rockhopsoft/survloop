<!-- resources/views/vendor/survloop/reports/inc-deets-vert-prog.blade.php -->
<div class="w100" @if (isset($nIDtxt)) id="repNode{{ $nIDtxt }}" @endif >
@if (isset($blockName) && trim($blockName) != '')
    <h3 class="mT0 mB10 slBlueDark">{!! $blockName !!}</h3>
@endif
    <table class="repDeetsBlock repDeetVert">
    @foreach ($deets as $j => $deet)
        <?php $done = ($j == $last || isset($deet[1]) && intVal($deet[1]) > 0); ?>
        <tr>
            <td class="w50">
                <div class="relDiv">
                    <div class="absDiv">
                        @if ($j == $last || isset($deet[1]) && intVal($deet[1]) > 0)
                            <div class="vertPrgDone">
                        @else <div class="vertPrgFutr">
                        @endif
                        <img src="/survloop/uploads/spacer.gif" border=0 alt="" >
                        </div>
                    </div>
                    @if (trim($deet[0]) != '')
                        @if (!$done) <span class="slGrey">{{ $deet[0] }}</span>
                        @else {{ $deet[0] }}
                        @endif
                    @endif
                </div>
            </td>
            <td class="w50">
                @if (trim($deet[1]) && intVal($deet[1]) > 0)
                    <nobr>{{ date(($GLOBALS["SL"]->pageView == 'public') 
                        ? 'F Y' : 'n/j/y', $deet[1]) }}</nobr>
                @else
                    <span class="slGrey">pending</span>
                @endif
            </td>
        </tr>
    @endforeach
    </table>
</div>