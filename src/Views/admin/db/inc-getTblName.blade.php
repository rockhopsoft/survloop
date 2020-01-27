@if (isset($id) && $id > 0 && isset($GLOBALS['SL']->tbl[$id]))
    @if (!isset($link) || $link == 1)
        <a href="/dashboard/db/table/{!! $GLOBALS['SL']->tbl[$id] !!}" 
            @if (isset($xtraLnk)) {!! $xtraLnk !!} @endif
            > {!! $GLOBALS['SL']->tblEng[$id] !!} 
            @if (isset($xtraTxt)) {!! $xtraTxt !!} @endif </a>
    @elseif ($link <= 0)
        {!! $GLOBALS['SL']->tblEng[$id] !!} @if (isset($xtraTxt)) {!! $xtraTxt !!} @endif
    @else
        <a href="#tbl{{ $id }}" @if (isset($xtraLnk)) {!! $xtraLnk !!} @endif
            >{!! $GLOBALS['SL']->tblEng[$id] !!} 
            @if (isset($xtraTxt)) {!! $xtraTxt !!} @endif </a>
    @endif
@endif