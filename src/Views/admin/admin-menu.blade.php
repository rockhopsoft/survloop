<!-- Stored in resources/views/vendor/survloop/admin/admin-menu.blade.php -->

<!--- <a href="javascript:void(0)" class="list-group-item" data-toggle="collapse" data-target="#sm" data-parent="#menu">MESSAGES <span class="label label-info">5</span> <span class="glyphicon glyphicon-envelope pull-right"></span></a> --->

<div id="sidemenu">
    <div class="panel list-group">

    @forelse ($adminNav as $i => $nav)
        @if (isset($nav[0]))
            @if (!isset($nav[3]) || sizeof($nav[3]) <= 1)
                <a href="{!! $nav[0] !!}" class="list-group-item  
                @if ($currNavPos[0] == $i) active @endif
                primeNav" data-parent="#menu" @if ($nav[2]%3 == 0) target="_blank" @endif >{!! $nav[1] !!}</a>
            @else
                <a href="{!! $nav[0] !!}" data-parent="#menu" data-toggle="collapse" class="list-group-item 
                @if ($currNavPos[0] == $i) active @endif
                primeNav" data-target="#subA{{ $i }}" @if ($nav[2]%3 == 0) target="_blank" @endif >{!! $nav[1] !!}</a>
                <div id="subA{{ $i }}" class="sublinks 
                @if ($currNavPos[0] != $i) collapse @endif
                ">
                @foreach ($nav[3] as $j => $nA)
                
                    @if (!isset($nA[3]) || sizeof($nA[3]) == 0)
                        <a href="{!! $nA[0] !!}" data-parent="#menu" class="list-group-item 
                        @if ($currNavPos[0] == $i && $currNavPos[1] == $j) active @endif
                        " @if ($nav[2]%3 == 0) target="_blank" @endif > {!! $nA[1] !!}</a>
                    @else
                        <a href="{!! $nA[0] !!}" data-parent="#menu" data-toggle="collapse" class="list-group-item 
                        @if ($currNavPos[0] == $i && $currNavPos[1] == $j) active @endif
                        " data-target="#subB{{ $j }}" @if ($nav[2]%3 == 0) target="_blank" @endif >
                        <?php /* <i class="fa fa-chevron-right"></i> */ ?> {!! $nA[1] !!}</a>
                        <div id="subB{{ $j }}" class="sublinks 
                        <?php /* @if ($currNavPos[0] != $i || $currNavPos[1] != $j) collapse @endif */ ?>
                        ">
                        @foreach ($nA[3] as $k => $nB)
                
                
                            @if (!isset($nB[3]) || sizeof($nB[3]) == 0)
                                <a href="{!! $nB[0] !!}" class="list-group-item 
                                @if ($currNavPos[0] == $i && $currNavPos[1] == $j && $currNavPos[2] == $k) active @endif
                                " data-parent="#menu" @if ($nav[2]%3 == 0) target="_blank" @endif >
                                <i class="fa fa-angle-right mL10"></i> {!! $nB[1] !!}</a>
                            @else
                                <a href="{!! $nB[0] !!}" data-parent="#menu" data-toggle="collapse" class="list-group-item 
                                @if ($currNavPos[0] == $i && $currNavPos[1] == $j && $currNavPos[2] == $k) active @endif
                                " data-target="#subC{{ $k }}" @if ($nav[2]%3 == 0) target="_blank" @endif >
                                <i class="fa fa-angle-right mL10"></i> {!! $nB[1] !!}</a>
                                <div id="subC{{ $k }}" class="sublinks 
                                <?php /* @if ($currNavPos[0] != $i || $currNavPos[1] != $j || $currNavPos[2] != $k) collapse @endif */ ?>
                                ">
                                @foreach ($nB[3] as $l => $nC)
                                    <a class="list-group-item small 
                                    @if ($currNavPos[0] == $i && $currNavPos[1] == $j && $currNavPos[2] == $k && $currNavPos[3] == $l) active @endif
                                    " href="{!! $nC[0] !!}" @if ($nav[2]%3 == 0) target="_blank" @endif >
                                    <i class="fa fa-caret-right mL20"></i> {!! $nC[1] !!}</a>
                                @endforeach
                                </div>
                            @endif
                            
                        
                        @endforeach
                        </div>
                    @endif
                
                
                @endforeach
                </div>
            @endif
        @endif
    @empty
        <a href="/admin/" class="list-group-item primeNav" data-parent="#menu">Dashboard</a>
    @endforelse
    
    </div>
    
</div>
