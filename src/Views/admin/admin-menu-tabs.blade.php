<ul class="nav nav-pills">
@foreach ($nA[4] as $k => $nB)
    @if (isset($nB[1]) && trim($nB[1]) != '')
        <li class="nav-item"><a 
        @if (strpos($nB[0], '?ajax=1') !== false || strpos($nB[0], '&ajax=1') !== false)
            href="javascript:;"
        @else
            href="{!! $nB[0] !!}" @if ($nB[3]%3 == 0) target="_blank" @endif 
        @endif class="nav-link 
            @if ($currNavPos[0] == $i 
                && $currNavPos[1] == $j 
                && $currNavPos[2] == $k) active
            @endif 
            @if ($GLOBALS['SL']->isAdmMenuHshoo($nB[0]))
                hshoo" id="admLnk{{ substr($nB[0], 1+strpos($nB[0], '#')) }}"
            @else " id="admLnkK{{ $k }}"
            @endif >{!! $nB[1] !!}
            <div></div>
        </a></li>
    @endif
@endforeach
</ul>