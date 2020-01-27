<!-- resources/views/vendor/survloop/reports/inc-stat-tbl-percs.blade.php -->
@forelse ($tblOut as $i => $row)
    <tr @if ($i == 0) class="brdTop" @endif >
        <th>{!! $row[0] !!}</th>
        <td class="brdRgt">
            @if (trim(strip_tags($row[1])) != '0' && $row[1] != -3737 && trim($row[1]) != '')
                {!! $row[1] !!}
            @else <span class="slGrey">0</span>
            @endif
        </td>
        @for ($j = 2; $j < sizeof($row); $j++)
            <td>
                @if (trim(strip_tags($row[$j])) != '0' && $row[$j] != -3737 && trim($row[$j]) != '')
                    {!! $row[$j] !!}
                @else <span class="slGrey">0</span> 
                @endif 
            </td>
        @endfor
    </tr>
@empty @endforelse