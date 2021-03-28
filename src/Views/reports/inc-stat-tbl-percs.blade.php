<!-- resources/views/vendor/survloop/reports/inc-stat-tbl-percs.blade.php -->
@forelse ($tblOut as $i => $row)
    <tr>
        <th>{!! $row[0] !!}</th>
        <td>
            @if (trim(strip_tags($row[1])) != '0' 
                && $row[1] != -3737 && trim($row[1]) != '')
                {!! $row[1] !!}
            @else <span class="slGrey">0</span>
            @endif
        </td>
        <td @if (sizeof($row) > 3) class="brdRgtBlue2" @endif >
            @if (trim(strip_tags($row[2])) != '0' 
                && $row[2] != -3737 
                && trim($row[2]) != '')
                {!! $row[2] !!}
            @else <span class="slGrey">0</span>
            @endif
        </td>
        @for ($j = 3; $j < sizeof($row); $j++)
            <td @if ($j%2 != 0) class="brdLftGrey" @endif >
                @if (trim(strip_tags($row[$j])) != '0' 
                    && $row[$j] != -3737 
                    && trim($row[$j]) != '')
                    {!! $row[$j] !!}
                @else <span class="slGrey">0</span> 
                @endif 
            </td>
        @endfor
    </tr>
@empty 
@endforelse