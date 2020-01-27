@if (isset($tbl) && $tbl)
    @if ($isExcel)
        <tr>
            <td><b>{{ $tbl->tbl_name }}</b></td>
            <td colspan=4 >{{ $tbl->tbl_eng }} - {{ $tbl->tbl_desc }}</td>
        </tr>
        <tr>
            <td>{{ $tbl->tbl_abbr }}) {{ $tbl->tbl_type }}</td>
            <td colspan=4 ><i>Notes: {{ $tbl->tbl_notes }}</i></td>
        </tr>
        <tr>
            <td># of Fields: {{ $tbl->tbl_num_fields }}</td>
            <td>Foreign Keys Outgoing: {{ $tbl->tbl_num_foreign_keys }}</td>
            <td colspan=3 >
                {{ $tbl->tbl_num_foreign_in }} 
                @if ($tbl->tbl_num_foreign_in != 1) Tables @else Table @endif
                with Foreign 
                @if ($tbl->tbl_num_foreign_in != 1) Keys @else Key @endif
                Incoming:</i> {{ $foreignKeyTbls }}
            </td>
        </tr>
    @else
        <h2>{{ $tbl->tbl_name }}&nbsp;&nbsp;&nbsp;
        ({{ $tbl->tbl_abbr }})&nbsp;&nbsp;&nbsp;<span class="slGrey"><i>{{ $tbl->tbl_type }}</i></h2>
        <h4 class="m0">{{ $tbl->tbl_desc }}</h4>
        @if (trim($tbl->tbl_notes) != '')
            <div class="slGrey f12"><i>Notes: {{ $tbl->tbl_notes }}</i></div>
        @endif
    @endif
@endif