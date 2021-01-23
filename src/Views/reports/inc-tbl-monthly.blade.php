<!-- resources/views/vendor/survloop/reports/inc-tbl-monthly.blade.php -->

<table class="table table-striped slSpreadTbl">
    <tr>
        <th><b>Month</b></th>
    @foreach ($cols as $c => $col)
        <th><b>
            {{ $col->name }} 
            @if (trim($col->unit) != '') ({{ $col->unit }}) @endif
        </b></th>
    @endforeach
    </tr>
    <tr>
        <th><b>Total</b></th>
    @foreach ($cols as $c => $col)
        <th id="colTot{{ str_replace('-', '', 
            $GLOBALS['SL']->slugify($col->name)) }}">
            <b>{{ number_format($col->sum) }}</b>
        </th>
    @endforeach
    </tr>
    @foreach ($monthlyData as $m => $month)
        <tr>
            <td style="padding-right: 30px;"><nobr>
            @if (isset($month->{ $monthFld }))
                {{ date("M", mktime(0, 0, 0, $month->{ $monthFld }, 1, 2000)) }} 
                @if (isset($years[$m]) && intVal($years[$m]) > 0)
                    '{{ $years[$m] }}
                @endif
            @endif
            </nobr></td>
        @for ($c = 0; $c < $colCnt; $c++)
            <td>
            @if (isset($month->{ $cols[$c]->fld }))
                {{ number_format($month->{ $cols[$c]->fld }) }}
            @endif
            </td>
        @endfor
        </tr>
    @endforeach
</table>
