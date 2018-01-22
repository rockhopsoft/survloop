<!-- resources/views/vendor/survloop/admin/db/full.blade.php -->

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>:
    Full Database Design 
    <nobr><span class="f14">({!! strip_tags($dbStats) !!})</span></nobr>
</h1>

@if (!$isPrint)
    <a href="/dashboard/db/all?print=1" target="_blank" class="btn btn-md btn-default mR10"
        ><i class="fa fa-print"></i> Print This Overview</a>
    
    <a class="btn btn-md btn-default mR10" 
        @if ($onlyKeys)
            @if ($isAll) href="/dashboard/db/all?all=1" @else href="/dashboard/db/all" @endif
            >Show More Than Just Foreign Keys
        @else
            @if ($isAll) href="/dashboard/db/all?all=1&onlyKeys=1" @else href="/dashboard/db/all?onlyKeys=1" @endif
            >Show Only Foreign Keys
        @endif
    </a>
    
    <a class="btn btn-md btn-default mR10" 
        @if ($isAll)
            @if ($onlyKeys) href="/dashboard/db/all?onlyKeys=1" @else href="/dashboard/db/all" @endif
            >Hide
        @else
            @if ($onlyKeys) href="/dashboard/db/all?all=1&onlyKeys=1" @else href="/dashboard/db/all?all=1" @endif
            >Show
        @endif
     All Full Field Specs</a>
    <div class="pT10">
        <a href="/dashboard/db/addTable" class="btn btn-xs btn-default mR10"><i class="fa fa-plus"></i> Add a New Table</a>
        <a href="/dashboard/db/bus-rules" target="_blank" class="btn btn-xs btn-default mR10">Business Rules</a>
        <a href="/dashboard/db/diagrams" target="_blank" class="btn btn-xs btn-default mR10">Tables Diagrams</a>
        <a href="/dashboard/db/field-matrix" target="_blank" class="btn btn-xs btn-default mR10">Field Matrix</a>
        <a href="/dashboard/db/export" target="_blank" class="btn btn-xs btn-default mR10">Export / Install</a>
        <a href="/dashboard/db/sortTable" class="btn btn-xs btn-default mR10">Re-Order Tables</a>
        <a href="/dashboard/db/fieldDescs" class="btn btn-xs btn-default mR10">Field Descriptions</a>
        <a href="/dashboard/db/fieldXML" class="btn btn-xs btn-default mR10">Field XML Settings</a>
    </div>
    
@else

    All specifications for database designs and user experience (form tree map) are made available
    by <a href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" target="_blanK" 
        >{{ $GLOBALS['SL']->sysOpts['site-name'] }}</a> under the
    <a href="{{ $GLOBALS['SL']->sysOpts['app-license-url'] }}" target="_blank" 
    >{{ $GLOBALS['SL']->sysOpts['app-license'] }}</a>.
    
@endif

<div class="clearfix p5"></div>

<div class="fC"></div>

    {!! $innerTable !!}
    


@if (!$isPrint)

    <br /><br /><a href="javascript:;" onClick="document.getElementById('genericFlds').style.display='block'; this.style.display='none';" 
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

