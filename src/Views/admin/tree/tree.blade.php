<!-- resources/views/vendor/survloop/admin/tree/tree.blade.php -->

@if ($isPrint) 
    <style>
    .basicTier0, .basicTier1, .basicTier2, .basicTier3, .basicTier4, 
    .basicTier5, .basicTier6, .basicTier7, .basicTier8, .basicTier9 {
        padding: 3px 3px 8px 10px;
        margin: 8px 3px 0px 3px;
    }
    </style>
@endif
<h1>
    <span class="slBlueDark">
    @if (!$isPrint) <i class="fa fa-snowflake-o"></i> @endif
    {{ $GLOBALS["DB"]->treeName }}</span>:
    <nobr>User Experience</nobr>
</h1>
@if ($isPrint) 
    <div class="fPerc66"><nobr>Core Specifications of Complainant's User Experience</nobr></div></h1> 
    <div id="admFootLegal" class="mTn10">
        <a @if (!isset($isPrint) || !$isPrint) href="//creativecommons.org/licenses/by-sa/3.0/" target="_blank" @endif 
            ><img src="/survloop/creative-commons-by-sa-88x31.png" border=0 align=left class="mT5 mR10" ></a>
        <i>All specifications for database designs and user experience (form tree map) are made available<br />
        by <a @if (!isset($isPrint) || !$isPrint) href="http://FlexYourRights.org" target="_blanK" @endif >Flex Your Rights</a> under the
        <a @if (!isset($isPrint) || !$isPrint) href="http://creativecommons.org/licenses/by-sa/3.0/" target="_blank" @endif >Creative Commons Attribution-ShareAlike License</a>, {{ date("Y") }}.</i>
    </div>
@else
    </h1>
@endif

@if (!$isPrint)

    <div class="mT5">
    @if ($isAll)
        <a class="btn btn-primary mR10" @if ($isAlt) href="/dashboard/tree/map?alt=1" @else href="/dashboard/tree/map" @endif
            ><i class="fa fa-expand fa-flip-horizontal"></i> Collapse Tree</a>
    @else
        <a class="btn btn-primary mR10" @if ($isAlt) href="/dashboard/tree/map?all=1&alt=1" @else href="/dashboard/tree/map?all=1" @endif
            ><i class="fa fa-expand fa-flip-horizontal"></i> Expand Tree</a>
    @endif
    
    @if ($isAlt)
        <a class="btn btn-default mR10" @if ($isAll) href="/dashboard/tree/map?all=1" @else href="/dashboard/tree/map" @endif
            ><i class="fa fa-align-left"></i> Hide Node Details</a>
    @else
        <a class="btn btn-default mR10" @if ($isAll) href="/dashboard/tree/map?all=1&alt=1" @else href="/dashboard/tree/map?alt=1" @endif
            ><i class="fa fa-align-left"></i> Show Node Details</a>
    @endif
    
    <a class="btn btn-default mR10" id="adminAboutTog" href="javascript:void(0)">About The Tree</a>
    </div>
    <div class="p5"></div>
    
    <div class="row mB20 gry9">
        <div class="col-md-1"><i>Actions:</i></div>
        <div class="col-md-3">Node ID# (Click to Edit)</div>
        <div class="col-md-3"><i class="fa fa-expand fa-flip-horizontal"></i> Expand/Compress Children</div>
        <div class="col-md-3"><i class="fa fa-plus-square-o"></i> Add Related Node</div>
        <div class="col-md-2"><i class="fa fa-arrows-alt"></i> Move Node</div>
    </div>

@endif

{!! $printTree !!}

@if (trim($printTree) == '')
    <div class="basicTier0">
    <span class="slBlueDark f22"><i class="fa fa-chevron-right"></i></span> <a href="?node=-37"><i class="fa fa-plus-square-o"></i> Create Root Node</a>
    </div>
@endif

<div class="adminFootBuff"></div>

<style> ul { margin: 0px 30px; padding: 0px; } </style>