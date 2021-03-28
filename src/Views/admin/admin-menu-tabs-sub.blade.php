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
