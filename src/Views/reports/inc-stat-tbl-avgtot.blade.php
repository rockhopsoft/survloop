<!-- resources/views/vendor/survloop/reports/inc-stat-tbl-avgtot.blade.php -->
@forelse ($tblOut as $i => $row)
    <tr @if ($i == 0) class="brdTop" @endif >
        <th>{!! $row[0] !!}</th>
        <td class="brdRgt">
            @if ($row[1] != 0 && $row[1] != -3737)
                @if ($row[1] < 1 && $row[1] > -1) {{ $GLOBALS["SL"]->sigFigs($row[1], 3) }}
                @else {{ number_format(round($row[1])) }} 
                @endif
            @else <span class="slGrey">0</span> 
            @endif
        </td>
        @for ($j = 2; $j < sizeof($row); $j++) 
            <td>
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