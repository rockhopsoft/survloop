<!-- resources/views/vendor/survloop/reports/inc-deets-row.blade.php -->
@if (isset($deet) && sizeof($deet) > 0 && trim($deet[0]) != '')
    <tr @if (isset($deet[2])) id="dataPrnt{{ $deet[2] }}" @endif >
        <?php /* class=" @if (isset($i) && $i%2 == 0) row2 @endif " */ ?>
    @if (!isset($deet[1]))
        <td colspan=2 >{!! $deet[0] !!}</td>
    @else
        <td class="w50 datTdLab"><span>{!! $deet[0] !!}</span></td>
        <td class="w50 datTdDat">{!! $deet[1] !!}</td>
    @endif
    </tr>
@endif