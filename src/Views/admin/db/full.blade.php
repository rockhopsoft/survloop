<!-- resources/views/vendor/survloop/admin/db/full.blade.php -->

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS["DB"]->dbRow->DbName }}</span>:
    Full Database Design 
    <nobr><span class="f14">({!! strip_tags($dbStats) !!})</span></nobr>
</h1>

@if (!$isPrint)
    <a href="/dashboard/db/all?print=1" target="_blank" class="btn btn-xs btn-default mR10"><i class="fa fa-print"></i> Print This Overview</a>
    
    <a class="btn btn-xs btn-default mR10" 
        @if ($onlyKeys)
            @if ($isAll)
                href="/dashboard/db/all?all=1"
            @else
                href="/dashboard/db/all"
            @endif
            >Show More Than Just Foreign Keys
        @else
            @if ($isAll)
                href="/dashboard/db/all?all=1&onlyKeys=1"
            @else
                href="/dashboard/db/all?onlyKeys=1"
            @endif
            >Show Only Foreign Keys
        @endif
    </a>
    
    <a class="btn btn-xs btn-default" 
        @if ($isAll)
            @if ($onlyKeys)
                href="/dashboard/db/all?onlyKeys=1"
            @else
                href="/dashboard/db/all"
            @endif
            >Hide
        @else
            @if ($onlyKeys)
                href="/dashboard/db/all?all=1&onlyKeys=1"
            @else
                href="/dashboard/db/all?all=1"
            @endif
            >Show
        @endif
     All Full Field Specs</a>
@else
    All specifications for database designs and user experience (form tree map) are made available
    by <a href="{{ $GLOBALS['DB']->sysOpts['logo-url'] }}" target="_blanK" 
        >{{ $GLOBALS['DB']->sysOpts['site-name'] }}</a> under the
    <a href="{{ $GLOBALS['DB']->sysOpts['app-license-url'] }}" target="_blank" 
    >{{ $GLOBALS['DB']->sysOpts['app-license'] }}</a>.
@endif

<div class="clearfix p5"></div>

<div class="fC"></div>

    {!! $innerTable !!}
    


@if (!$isPrint)

    <br /><br /><a href="javascript:void(0)" onClick="document.getElementById('genericFlds').style.display='block'; this.style.display='none';" 
    class="f16 p20 disBlo" style="border: 1px #0b0b85 solid; -moz-border-radius: 15px; border-radius: 15px;"><b>Show Generic Fields</b></a>
    <br /><br /><div id="genericFlds" class="disNon"><table border=0 cellpadding=5 cellspacing=0 >
    <tr><td colspan=7 class="p5 pL20 row2 f18 gry6"><i>Generic Fields...</i></td></tr>
    <tr><td class="pR20"><i>Field English Name</i></td><td></td><td class="pR20"><i>Field Name</i></td><td class="pR20"><nobr><i>Data Type</i></nobr></td>
    <td class="pR20"><i>Values?</i></td><td><i>Foreign Key?</i></td><td><i>Tables w/ Replicas</i></td></tr>
    @forelse ($genericFlds as $fld)
        {!! $fld !!}
    @empty
    @endforelse
    </table></div>
    
@endif

