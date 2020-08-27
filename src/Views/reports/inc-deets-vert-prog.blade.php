<!-- resources/views/vendor/survloop/reports/inc-deets-vert-prog.blade.php -->
<div class="w100" @if (isset($nIDtxt)) id="repNode{{ $nIDtxt }}" @endif >
@if (isset($blockName) && trim($blockName) != '')
    <h4 class="mT0 mB10 slBlueDark">{!! $blockName !!}</h4>
@endif
@if (isset($blockDesc) && trim($blockDesc) != '')
    <div class="mB10">{!! $blockDesc !!}</div>
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
                        <?php /* <img src="/survloop/uploads/spacer.gif" border=0 alt="" > */ ?>
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
                    <nobr>{{ date($dateType, $deet[1]) }}</nobr>
                @else
                    <span class="slGrey">pending</span>
                @endif
            </td>
        </tr>
    @endforeach
    </table>
</div>