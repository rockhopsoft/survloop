<!-- resources/views/vendor/survloop/admin/admin-menu.blade.php -->
<div id="admMenu">
@forelse ($adminNav as $i => $nav)
    @if (isset($nav[0]))
        @if (!isset($nav[4]) || sizeof($nav[4]) <= 1)
            <div class="admMenuTier1"><a href="{!! $nav[0] !!}" class=" 
            @if ($currNavPos[0] == $i) active @endif @if ($GLOBALS['SL']->isAdmMenuHshoo($nav[0])) hshoo @endif
            primeNav" @if ($nav[3]%3 == 0) target="_blank" @endif 
            @if ($GLOBALS['SL']->isAdmMenuHshoo($nav[0])) id="admLnk{{ substr($nav[0], 1+strpos($nav[0], '#')) }}"
            @endif > 
            @if (isset($nav[2]) && trim($nav[2]) != '') <div class="admMenuIco pull-left">{!! $nav[2] !!}</div> @endif
            {!! $nav[1] !!}</a></div>
        @else
            <div class="admMenuTier1"><a href="{!! $nav[0] !!}" data-toggle="collapse" class="
            @if ($currNavPos[0] == $i) active @endif @if ($GLOBALS['SL']->isAdmMenuHshoo($nav[0])) hshoo @endif
            primeNav" data-target="#subA{{ $i }}" @if ($nav[3]%3 == 0) target="_blank" @endif 
            @if ($GLOBALS['SL']->isAdmMenuHshoo($nav[0])) id="admLnk{{ substr($nav[0], 1+strpos($nav[0], '#')) }}"
            @endif > 
            @if (isset($nav[2]) && trim($nav[2]) != '') <div class="admMenuIco pull-left">{!! $nav[2] !!}</div> @endif
            {!! $nav[1] !!}</a>
            <div id="subA{{ $i }}" class="sublinks @if ($currNavPos[0] != $i) collapse @endif ">
            @foreach ($nav[4] as $j => $nA)
                <div class="admMenuTier2"><a href="{!! $nA[0] !!}" @if ($nA[3]%3 == 0) target="_blank" @endif class="
                @if ($currNavPos[0] == $i && $currNavPos[1] == $j) active @endif
                @if ($GLOBALS['SL']->isAdmMenuHshoo($nA[0])) hshoo" id="admLnk{{ substr($nA[0], 1+strpos($nA[0], '#'))}}
                @else " @endif > 
                @if (isset($nA[2]) && trim($nA[2]) != '') <div class="admMenuIco">{!! $nA[2] !!}</div> @endif
                {!! $nA[1] !!}</a></div>
            @endforeach
            </div>
            </div>
        @endif
    @endif
@empty
    <a href="/admin" class="primeNav" >Dashboard</a>
@endforelse
</div>
