<!-- Stored in resources/views/vendor/survloop/admin/admin-menu.blade.php -->

<div id="admMenu">
@forelse ($adminNav as $i => $nav)
    @if (isset($nav[0]))
        @if (!isset($nav[4]) || sizeof($nav[4]) <= 1)
            <div class="admMenuTier1"><a href="{!! $nav[0] !!}" class=" 
            @if ($currNavPos[0] == $i) active @endif @if ($GLOBALS['SL']->isAdmMenuHshoo($nav[0])) hshoo @endif
            primeNav" data-parent="#menu" @if ($nav[3]%3 == 0) target="_blank" @endif 
            @if ($GLOBALS['SL']->isAdmMenuHshoo($nav[0])) id="admLnk{{ substr($nav[0], 1+strpos($nav[0], '#')) }}"
            @endif > 
            @if (isset($nav[2]) && trim($nav[2]) != '') <div class="admMenuIco">{!! $nav[2] !!}</div> @endif
            {!! $nav[1] !!}</a></div>
        @else
            <div class="admMenuTier1"><a href="{!! $nav[0] !!}" data-parent="#menu" data-toggle="collapse" class="
            @if ($currNavPos[0] == $i) active @endif @if ($GLOBALS['SL']->isAdmMenuHshoo($nav[0])) hshoo @endif
            primeNav" data-target="#subA{{ $i }}" @if ($nav[3]%3 == 0) target="_blank" @endif 
            @if ($GLOBALS['SL']->isAdmMenuHshoo($nav[0])) id="admLnk{{ substr($nav[0], 1+strpos($nav[0], '#')) }}"
            @endif > 
            @if (isset($nav[2]) && trim($nav[2]) != '') <div class="admMenuIco">{!! $nav[2] !!}</div> @endif
            {!! $nav[1] !!}</a></div>
            <div id="subA{{ $i }}" class="sublinks @if ($currNavPos[0] != $i) collapse @endif ">
            @foreach ($nav[4] as $j => $nA)
            
                @if (!isset($nA[4]) || sizeof($nA[4]) == 0)
                    <div class="admMenuTier2"><a href="{!! $nA[0] !!}" data-parent="#menu" class="
                    @if ($currNavPos[0] == $i && $currNavPos[1] == $j) active @endif
                    @if ($GLOBALS['SL']->isAdmMenuHshoo($nA[0])) hshoo @endif " 
                    @if ($nA[3]%3 == 0) target="_blank" @endif 
                    @if ($GLOBALS['SL']->isAdmMenuHshoo($nA[0])) id="admLnk{{ substr($nA[0], 1+strpos($nA[0], '#')) }}"
                    @endif > 
                    @if (isset($nA[2]) && trim($nA[2]) != '') <div class="admMenuIco">{!! $nA[2] !!}</div> @endif
                    {!! $nA[1] !!}</a></div>
                @else
                    <div class="admMenuTier2"><a href="{!! $nA[0] !!}" data-parent="#menu" data-toggle="collapse" 
                    class=" @if ($currNavPos[0] == $i && $currNavPos[1] == $j) active @endif
                    @if ($GLOBALS['SL']->isAdmMenuHshoo($nA[0])) hshoo @endif
                    " data-target="#subB{{ $j }}" @if ($nA[3]%3 == 0) target="_blank" @endif 
                    @if ($GLOBALS['SL']->isAdmMenuHshoo($nA[0])) id="admLnk{{ substr($nA[0], 1+strpos($nA[0], '#')) }}" 
                    @endif > 
                    @if (isset($nA[2]) && trim($nA[2]) != '') <div class="admMenuIco">{!! $nA[2] !!}</div> @endif
                    {!! $nA[1] !!}</a></div>
                    <div id="subB{{ $j }}" class="sublinks 
                    @if ($currNavPos[0] != $i || $currNavPos[1] != $j) collapse @endif ">
                    @foreach ($nA[4] as $k => $nB)
            
                        @if (!isset($nB[4]) || sizeof($nB[4]) == 0)
                            <div class="admMenuTier3"><a href="{!! $nB[0] !!}" class="
                            @if ($currNavPos[0] == $i && $currNavPos[1] == $j && $currNavPos[2] == $k) active @endif
                            @if ($GLOBALS['SL']->isAdmMenuHshoo($nB[0])) hshoo @endif " data-parent="#menu" 
                            @if ($nB[3]%3 == 0) target="_blank" @endif 
                            @if ($GLOBALS['SL']->isAdmMenuHshoo($nB[0])) 
                                id="admLnk{{ substr($nB[0], 1+strpos($nB[0], '#')) }}" 
                            @endif >{!! $nB[1] !!}</a></div>
                        @else
                            <div class="admMenuTier3"><a href="{!! $nB[0] !!}" data-parent="#menu" data-toggle="collapse" class=" 
                            @if ($currNavPos[0] == $i && $currNavPos[1] == $j && $currNavPos[2] == $k) active @endif
                            @if ($GLOBALS['SL']->isAdmMenuHshoo($nB[0])) hshoo @endif " data-target="#subC{{ $k }}" 
                            @if ($nB[3]%3 == 0) target="_blank" @endif 
                            @if ($GLOBALS['SL']->isAdmMenuHshoo($nB[0])) 
                                id="admLnk{{ substr($nB[0], 1+strpos($nB[0], '#')) }}" 
                            @endif >{!! $nB[1] !!}</a></div>
                            <div id="subC{{ $k }}" class="sublinks 
                            <?php /* @if ($currNavPos[0] != $i || $currNavPos[1] != $j || $currNavPos[2] != $k) collapse @endif */ ?>
                            ">
                            @foreach ($nB[4] as $l => $nC)
                                <div class="admMenuTier4"><a href="{!! $nC[0] !!}" class="small
                                @if ($GLOBALS['SL']->isAdmMenuHshoo($nC[0])) hshoo @endif 
                                @if ($currNavPos[0] == $i && $currNavPos[1] == $j && $currNavPos[2] == $k 
                                    && $currNavPos[3] == $l) active @endif
                                " @if ($nC[3]%3 == 0) target="_blank" @endif 
                                @if ($GLOBALS['SL']->isAdmMenuHshoo($nC[0])) 
                                    id="admLnk{{ substr($nC[0], 1+strpos($nC[0], '#')) }}" 
                                @endif >{!! $nC[1] !!}</a></div>
                            @endforeach
                            <div class="p5"></div>
                            </div>
                        @endif
                    
                    @endforeach
                    <div class="p5"></div>
                    </div>
                @endif
            
            @endforeach
            <div class="p5"></div>
            </div>
        @endif
    @endif
@empty
    <a href="/admin/" class="list-group-item primeNav" data-parent="#menu">Dashboard</a>
@endforelse
</div>
