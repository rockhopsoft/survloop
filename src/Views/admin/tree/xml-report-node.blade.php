@if (intVal($tblID) <= 0)
    <{{ $tbl }}>
        {!! $kids !!}
    </{{ $tbl }}>
@else
    @if ($TblOpts%5 > 0)
        <{{ $tblAbbr }} @if (isset($rec) && $rec) id="{{ $rec->getKey() }}" @endif >
    @endif
    @forelse ($tblFlds as $i => $fld)
        @if ($recFlds[$fld->fld_id] !== false)
            <{{ $tblAbbr . $fld->fld_name }}>{!! $recFlds[$fld->fld_id] !!}</{{ $tblAbbr . $fld->fld_name }}>
        @endif
    @empty
    @endforelse
    
    {!! $kids !!}
    
    @if ($TblOpts%5 > 0)
        </{{ $tblAbbr }}>
    @endif
@endif    