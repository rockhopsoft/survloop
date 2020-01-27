<!-- resources/views/vendor/survloop/admin/db/full.blade.php -->

<div class="container">

<div class="row">
    <div class="col-9">
        <h1><span class="slBlueDark"><i class="fa fa-database"></i> 
            {{ $GLOBALS['SL']->dbRow->db_name }}</span>: Full Database Design</h1>
        <nobr>{!! strip_tags($dbStats) !!}</nobr>
@if (!$isPrint)
        <a href="/dashboard/db/addTable" class="btn btn-sm btn-secondary mL10 mTn5"><i class="fa fa-plus"
            ></i> Add a New Table</a>
        <a href="/dashboard/db/bus-rules" target="_blank" 
            class="btn btn-sm btn-secondary mL10 mTn5">Business Rules</a>
        <a href="/dashboard/db/field-matrix" target="_blank" 
            class="btn btn-sm btn-secondary mL10 mTn5">Field Matrix</a>
@endif
    </div>
@if (!$isPrint)
    <div class="col-3 taR">
    <a href="/dashboard/db/all?print=1" target="_blank" class="btn btn-sm btn-info m5"
        ><i class="fa fa-print mR5"></i> Print This Overview</a>
    <a class="btn btn-sm btn-info m5" 
        @if ($onlyKeys)
            @if ($isAll) href="/dashboard/db/all?all=1" @else href="/dashboard/db/all" @endif
            ><i class="fa fa-link mR5"></i> Show More Than Just Foreign Keys
        @else
            @if ($isAll) href="/dashboard/db/all?all=1&onlyKeys=1" 
            @else href="/dashboard/db/all?onlyKeys=1" 
            @endif ><i class="fa fa-link mR5"></i> Show Only Foreign Keys
        @endif
    </a>
    <a class="btn btn-sm btn-info m5" 
        @if ($isAll)
            @if ($onlyKeys) href="/dashboard/db/all?onlyKeys=1" @else href="/dashboard/db/all" @endif
            ><i class="fa fa-compress mR5"></i> Hide
        @else
            @if ($onlyKeys) href="/dashboard/db/all?all=1&onlyKeys=1" @else href="/dashboard/db/all?all=1" @endif
            ><i class="fa fa-expand mR5"></i> Show
        @endif
        All Full Field Specs</a>
    
@else
    <div class="col-6 taL">

    All specifications for database designs and user experience (form tree map) are made available
    by <a href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" target="_blanK" 
        >{{ $GLOBALS['SL']->sysOpts['site-name'] }}</a> under the
    <a href="{{ $GLOBALS['SL']->sysOpts['app-license-url'] }}" target="_blank" 
    >{{ $GLOBALS['SL']->sysOpts['app-license'] }}</a>.
    
@endif
    </div>
</div>

<div class="clearfix p5"></div>
<div class="fC"></div>

    {!! $innerTable !!}
    
@if (!$isPrint)

    <br /><br /><a href="javascript:;" class="p20 disBlo" 
    onClick="document.getElementById('genericFlds').style.display='block'; this.style.display='none';" 
    style="border: 1px #0b0b85 solid; -moz-border-radius: 15px; border-radius: 15px;"><b>Show Generic Fields</b></a>
    <br /><br /><div id="genericFlds" class="disNon"><table border=0 cellpadding=5 cellspacing=0 >
    <tr><td colspan=7 class="p5 pL20 row2 slGrey"><i>Generic Fields...</i></td></tr>
    <tr>
        <td class="pR20"><i>Field Plain English Name</i></td>
        <td></td>
        <td class="pR20"><i>Field Database Name</i></td>
        <td class="pR20"><nobr><i>Data Type</i></nobr></td>
        <td class="pR20"><i>Values?</i></td>
        <td><i>Foreign Key?</i></td>
        <td><i>Tables w/ Replicas</i></td>
    </tr>
    @forelse ($genericFlds as $fld) {!! $fld !!} @empty @endforelse
    </table></div>
    
@endif

</div>
