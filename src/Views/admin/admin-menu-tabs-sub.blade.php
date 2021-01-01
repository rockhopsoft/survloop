@forelse ($adminNav as $i => $nav)
    @if (isset($nav[0]) 
        && isset($nav[4]) 
        && sizeof($nav[4]) > 0 
        && $currNavPos[0] == $i)
        @foreach ($nav[4] as $j => $nA)
            @if (sizeof($nA) > 4 
                && sizeof($nA[4]) > 0 
                && $currNavPos[0] == $i 
                && $currNavPos[1] == $j)
                @foreach ($nA[4] as $k => $nB)
                    @if (isset($nB[4]) 
                        && sizeof($nB[4]) > 0 
                        && $currNavPos[0] == $i 
                        && $currNavPos[1] == $j 
                        && $currNavPos[2] == $k)
                        <ul class="nav">
                            <li class="pL20">&nbsp;</li>
                        @foreach ($nB[4] as $l => $nC)
                            @if (isset($nC[1]) && trim($nC[1]) != '')
                                <li class="nav-item"><a href="{!! $nC[0] !!}" class="nav-link
                                @if ($GLOBALS['SL']->isAdmMenuHshoo($nC[0])) hshoo @endif 
                                @if ($currNavPos[0] == $i 
                                    && $currNavPos[1] == $j 
                                    && $currNavPos[2] == $k 
                                    && $currNavPos[3] == $l) active @endif
                                " @if ($nC[3]%3 == 0) target="_blank" @endif 
                                @if ($GLOBALS['SL']->isAdmMenuHshoo($nC[0])) 
                                    id="admLnk{{ substr($nC[0], 1+strpos($nC[0], '#')) }}" 
                                @endif >{!! $nC[1] !!}</a></li>
                            @endif
                        @endforeach
                        </ul>
                    @endif
                @endforeach
            @endif
        @endforeach
    @endif
@empty
@endforelse