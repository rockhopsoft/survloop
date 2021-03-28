<!-- resources/views/vendor/survloop/admin/db/raw-tbl.blade.php -->

@if (!$GLOBALS["SL"]->REQ->has('ajax'))
    <div class="slCard mB20">

        <h2 class="slBlueDark">
            <i class="fa fa-database mR3"></i> Raw Data Table
        </h2>
        <h4>
            {{ $tbl->tbl_eng }}
            ({{ number_format($rowTotCnt) }}
        @if ($rowTotCnt == $rows->count())
            rows)
        @else
            rows, showing first {{ number_format($rows->count()) }})
        @endif
        </h4>
        <div id="ajaxTbl" class="w100">
@endif

<table class="table table-striped w100" border=0 >
    <tr>
        <th id="colHead0 slGrey">Row</th>
        <th id="colHead1">
            <a href="javascript:;" class="columnSort"
                data-sort-fld="id"
                @if ($sortFld == 'id' && $sortDir == 'desc')
                    data-sort-dir="asc"
                @else
                    data-sort-dir="desc"
                @endif >
                Unique Record ID#
                @if ($sortFld == 'id')
                    @if ($sortDir == 'desc')
                        <i class="fa fa-caret-down" aria-hidden="true"></i>
                    @else
                        <i class="fa fa-caret-up" aria-hidden="true"></i>
                    @endif
                @endif
            </a>
        </th>
    @forelse ($flds as $col => $fld)
        <th id="colHead{{ (2+$col) }}">
            <a href="javascript:;" class="columnSort"
                data-sort-fld="{{ $fld->fld_name }}"
                @if ($sortFld == $fld->fld_name && $sortDir == 'desc')
                    data-sort-dir="asc"
                @else
                    data-sort-dir="desc"
                @endif >
                {{ $fld->fld_eng }}
                @if ($sortFld == $fld->fld_name)
                    @if ($sortDir == 'desc')
                        <i class="fa fa-caret-down" aria-hidden="true"></i>
                    @else
                        <i class="fa fa-caret-up" aria-hidden="true"></i>
                    @endif
                @endif
            </a>
        </th>
    @empty
    @endforelse
    </tr>
@forelse ($rows as $cnt => $row)
    <tr class=" @if ($cnt%2 == 0) row2 @endif ">
        <td class="slGrey">{{ number_format(1+$cnt) }}</td>
        <td>{{ $row->{ $tbl->tbl_abbr . 'id' } }}</td>
    @forelse ($flds as $col => $fld)
        <?php $fldName = $tbl->tbl_abbr . $fld->fld_name; ?>
        <td>
        @if (isset($row->{ $fldName })
            && trim($row->{ $fldName }) != '')
            @if ($fld->fld_type == 'INT'
                && !in_array($fld->fld_name, ['year', 'year_code']))
                {{ number_format($row->{ $fldName }) }}
            @else
                {{ $row->{ $fldName } }}
            @endif
        @else
            &nbsp;
        @endif
        </td>
    @empty
    @endforelse
    </tr>
@empty
@endforelse
</table>

@if (!$GLOBALS["SL"]->REQ->has('ajax'))
        </div>
    </div>

    <style>
    body { overflow-x: visible; }
    </style>
@endif
