<!-- resources/views/vendor/survloop/reports/inc-stat-tag-avgtot.blade.php -->
@forelse ($tblOut as $i => $row)
    <tr @if ($i == 0) class="brdTop" @endif >
        <th>{!! $row[0] !!}</th>
        @for ($j = 1; $j < sizeof($row); $j++) 
            <td @if ($j == 1) class="brdRgt" @elseif ($j == (sizeof($row)-1)) class="brdLft slGrey" @endif > 
                @if ($row[$j] != 0 && $row[$j] != -3737)
                    @if ($row[$j] < 1 && $row[$j] > -1) {{ $GLOBALS["SL"]->sigFigs($row[$j], 3) }}
                    @else {{ number_format(round($row[$j])) }} 
                    @endif
                @else <span class="slGrey">0</span> 
                @endif 
                </td>
        @endfor
    </tr>
@empty @endforelse