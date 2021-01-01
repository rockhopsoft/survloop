<!-- resources/views/vendor/survloop/admin/admin-menu.blade.php -->
<div id="admMenu">
@forelse ($adminNav as $i => $nav)
    @if (isset($nav[0]))
        @if (!isset($nav[4]) || sizeof($nav[4]) <= 1)
            <div class="admMenuTier1" id="admMenu1Tier{{ $i }}">
                <a href="{!! $nav[0] !!}" id="hidivBtnAdminNav{{$i}}" class="
                @if (strpos($nav[0], 'admMenuClpsBtn') === false) admMenuTier1Lnk @endif
                @if ($currNavPos[0] == $i) active @endif
                @if ($GLOBALS['SL']->isAdmMenuHshoo($nav[0])) hshoo @endif
                primeNav" @if ($nav[3]%3 == 0) target="_blank" @endif 
                @if ($GLOBALS['SL']->isAdmMenuHshoo($nav[0]))
                    id="admLnk{{ substr($nav[0], 1+strpos($nav[0], '#')) }}"
                @endif > 
                @if (isset($nav[2]) && trim($nav[2]) != '')
                    <div class="admMenuIco pull-left">{!! $nav[2] !!}</div>
                @endif
                <div class="admMenuLbl">{!! $nav[1] !!}</div></a>
            </div>
        @else
            <div class="admMenuTier1" id="admMenu1Tier{{ $i }}">
                <a href="{!! $nav[0] !!}" id="hidivBtnAdminNav{{$i}}" 
                    class="hidivBtn admMenuTier1Lnk primeNav 
                    @if ($currNavPos[0] == $i) active @endif "
                    @if (intVal($nav[3])%3 == 0) target="_blank" @endif >
                    @if (isset($nav[2]) && trim($nav[2]) != '')
                        <div class="admMenuIco pull-left">{!! $nav[2] !!}</div>
                    @endif
                    <div class="admMenuLbl">{!! $nav[1] !!}</div>
                </a>
            </div>
            <div id="hidivAdminNav{{$i}}" <?php /* id="subA{{ $i }}" */ ?>
                class="sublinks" style="display: @if ($currNavPos[0] == $i) block @else none @endif ;">
            @foreach ($nav[4] as $j => $nA)
                @if (isset($nA[0]) && isset($nA[1]))
                    <div class="admMenuTier2" id="admMenu2Tier{{ $i }}j{{ $j }}">
                        <a href="{!! $nA[0] !!}" 
                            @if (isset($nA[3]) && intVal($nA[3])%3 == 0) target="_blank" @endif 
                            id="admMenu2Link{{ $i }}j{{ $j }}" class="admMenuTier2Lnk
                            @if ($currNavPos[0] == $i && $currNavPos[1] == $j) tier2active @endif " > 
                            @if (isset($nA[2]) && trim($nA[2]) != '') 
                                <div class="admMenuIco">{!! $nA[2] !!}</div>
                            @endif
                            <div class="admMenuLbl">{!! $nA[1] !!}</div>
                        </a>
                    </div>
                @endif
            @endforeach
            </div>
        @endif
    @endif
@empty
    <a href="/admin" class="primeNav" >Dashboard</a>
@endforelse
</div>
