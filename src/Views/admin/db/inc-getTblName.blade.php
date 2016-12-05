@if (isset($id) && $id > 0 && isset($GLOBALS["DB"]->tbl[$id]))
    @if (!isset($link) || $link == 1)
        <a href="/dashboard/db/table/{!! $GLOBALS['DB']->tbl[$id] !!}"
        @if (isset($xtraLnk)) {!! $xtraLnk !!} @endif
        > {!! $GLOBALS["DB"]->tblEng[$id] !!}
        @if (isset($xtraTxt)) {!! $xtraTxt !!} @endif
        </a>
    @elseif ($link <= 0)
        {!! $GLOBALS["DB"]->tblEng[$id] !!}
        @if (isset($xtraTxt)) {!! $xtraTxt !!} @endif
    @else
        <a href="#tbl{{ $id }}" 
        @if (isset($xtraLnk)) {!! $xtraLnk !!} @endif
        >{!! $GLOBALS["DB"]->tblEng[$id] !!}
        @if (isset($xtraTxt)) {!! $xtraTxt !!} @endif
        </a>
    @endif
@endif