@forelse ($adminNav as $i => $nav)
    @if (isset($nav[0]) && isset($nav[4]) 
        && sizeof($nav[4]) > 0 && $currNavPos[0] == $i)
        @foreach ($nav[4] as $j => $nA)
            @if (isset($nA[4]) && sizeof($nA[4]) > 0 
                && $currNavPos[0] == $i && $currNavPos[1] == $j)
                <ul class="nav nav-pills">
                    @foreach ($nA[4] as $k => $nB)
                        @if (isset($nB[1]) && trim($nB[1]) != '')
                            <li class="nav-item"><a href="{!! $nB[0] !!}" 
                                @if ($nB[3]%3 == 0) target="_blank" @endif 
                                class="nav-link 
                                @if ($currNavPos[0] == $i 
                                    && $currNavPos[1] == $j 
                                    && $currNavPos[2] == $k) active @endif 
                                @if ($GLOBALS['SL']->isAdmMenuHshoo($nB[0]))
                                    hshoo" id="admLnk{{ substr($nB[0], 1+strpos($nB[0], '#')) }}"
                                @else " @endif >{!! $nB[1] !!}
                                <div></div>
                            </a></li>
                        @endif
                    @endforeach
                </ul>
            @endif
        @endforeach
    @endif
@empty
@endforelse