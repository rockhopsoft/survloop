@if (isset($tbl) && $tbl)
    @if ($isExcel)
        <tr><td><b>{{ $tbl->TblName }}</b></td><td colspan=4 >{{ $tbl->TblEng }} - {{ $tbl->TblDesc }}</td></tr>
        <tr><td>{{ $tbl->TblAbbr }}) {{ $tbl->TblType }}</td><td colspan=4 ><i>Notes: {{ $tbl->TblNotes }}</i></td></tr>
        <tr><td># of Fields: {{ $tbl->TblNumFields }}</td><td>Foreign Keys Outgoing: {{ $tbl->TblNumForeignKeys }}</td>
        <td colspan=3 >{{ $tbl->TblNumForeignIn }} 
        @if ($tbl->TblNumForeignIn != 1) Tables @else Table @endif
         with Foreign 
        @if ($tbl->TblNumForeignIn != 1) Keys @else Key @endif
        Incoming:</i> {{ $foreignKeyTbls }}</td></tr>
    @else
        <h2>{{ $tbl->TblName }}&nbsp;&nbsp;&nbsp;
        ({{ $tbl->TblAbbr }})&nbsp;&nbsp;&nbsp;<span class="gry6"><i>{{ $tbl->TblType }}</i></h2>
        <h4 class="m0">{{ $tbl->TblDesc }}</h4>
        @if (trim($tbl->TblNotes) != '')
            <div class="gry6 f12"><i>Notes: {{ $tbl->TblNotes }}</i></div>
        @endif
    @endif
@endif