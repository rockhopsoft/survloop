
$(document).on("click", "#admLnkK{{ $k }}", function(evt) {
    if (document.getElementById("mainContain")) {
        console.log("{!! $nB[0] !!}");
        if (evt.ctrlKey || evt.altKey || evt.metaKey) {
            window.open("{!! $GLOBALS["SL"]->stripUrlAjax($nB[0]) !!}", "_blank");
            return false;
        } else {
            document.getElementById("mainContain").innerHTML=getSpinnerPadded();
            $("#mainContain").load("{!! $nB[0] !!}");
    @for ($k2 = 0; $k2 < sizeof($nA[4]); $k2++)
        @if ($k2 == $k)
            document.getElementById("admLnkK{{ $k2 }}").className="nav-link active {{ $hshoo }}";
        @else
            document.getElementById("admLnkK{{ $k2 }}").className="nav-link {{ $hshoo }}";
        @endif
    @endfor
            {!! $GLOBALS["SL"]->pushBrowserStateUrlAjax($nB[0], $nB[1]) !!}
        }
    }
}); 

