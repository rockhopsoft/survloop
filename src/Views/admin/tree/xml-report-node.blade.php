@if (intVal($tblID) <= 0)
    <{{ $tbl }}>
        {!! $kids !!}
    </{{ $tbl }}>
@else
    @if ($TblOpts%5 > 0)
        <{{ $tblAbbr }} @if (isset($rec) && sizeof($rec) > 0) id="{{ $rec->getKey() }}" @endif >
    @endif
    @forelse ($tblFlds as $i => $fld)
        @if ($recFlds[$fld->FldID] !== false)
            <{{ $tblAbbr . $fld->FldName }}>{!! $recFlds[$fld->FldID] !!}</{{ $tblAbbr . $fld->FldName }}>
        @endif
    @empty
    @endforelse
    
    {!! $kids !!}
    
    @if ($TblOpts%5 > 0)
        </{{ $tblAbbr }}>
    @endif
@endif    