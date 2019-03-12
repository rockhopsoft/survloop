<!-- resources/views/vendor/survloop/reports/inc-tree-node-type-stats.blade.php -->
<div class="row mT10 mB10">
    <div class="col-3">
        <h3 class="mT0">{{ number_format($qTypeStats["nodes"]["tot"]) }} Nodes Total</h3>
        <div class="pL5">
        <span @if ($qTypeStats["nodes"]["loopNodes"] == 0) class="slGrey" @endif >
            {{ number_format($qTypeStats["nodes"]["loopNodes"]) }} Looped Nodes</span><br />
        <span @if ($qTypeStats["nodes"]["loops"] == 0) class="slGrey" @endif >
            {{ number_format($qTypeStats["nodes"]["loops"]) }} Loops Total</span>
        </div>
    </div>
    <div class="col-3">
        <b class="fPerc125">Total Survey Questions</b><br />
        <span @if ($qTypeStats["choic"]["all"] == 0) class="slGrey" @endif >
            <nobr>{{ number_format($qTypeStats["choic"]["all"]) }} Multiple Choice</span>
        @if (intVal($qTypeStats["choic"]["req"]) > 0)
            <span class="red fPerc80 mL5">*{{ number_format($qTypeStats["choic"]["req"]) }}</span>
        @endif </nobr><br />
        <span @if ($qTypeStats["quali"]["all"] == 0) class="slGrey" @endif >
            <nobr>{{ number_format($qTypeStats["quali"]["all"]) }} Open-Ended</span>
        @if (intVal($qTypeStats["quali"]["req"]) > 0) 
            <span class="red fPerc80 mL5">*{{ number_format($qTypeStats["quali"]["req"]) }}</span>
        @endif </nobr><br />
        <span @if ($qTypeStats["quant"]["all"] == 0) class="slGrey" @endif >
            <nobr>{{ number_format($qTypeStats["quant"]["all"]) }} Numeric</span>
        @if (intVal($qTypeStats["quant"]["req"]) > 0)
            <span class="red fPerc80 mL5">*{{ number_format($qTypeStats["quant"]["req"]) }}</span>
        @endif </nobr>
    </div>
    <div class="col-3">
        <b class="fPerc125">Unique <nobr>Data Fields</nobr></b><br />
        <span @if ($dataTypeStats["choic"]["all"] == 0) class="slGrey" @endif >
            <nobr>{{ number_format($dataTypeStats["choic"]["all"]) }} Multiple Choice</span>
        @if (intVal($dataTypeStats["choic"]["req"]) > 0)
            <span class="red fPerc80 mL5">*{{ number_format($dataTypeStats["choic"]["req"]) }}</span>
        @endif </nobr><br />
        <span @if ($dataTypeStats["quali"]["all"] == 0) class="slGrey" @endif >
            <nobr>{{ number_format($dataTypeStats["quali"]["all"]) }} Open-Ended</span>
        @if (intVal($dataTypeStats["quali"]["req"]) > 0) 
            <span class="red fPerc80 mL5">*{{ number_format($dataTypeStats["quali"]["req"]) }}</span>
        @endif </nobr><br />
        <span @if ($dataTypeStats["quant"]["all"] == 0) class="slGrey" @endif >
            <nobr>{{ number_format($dataTypeStats["quant"]["all"]) }} Numeric</span>
        @if (intVal($dataTypeStats["quant"]["req"]) > 0)
            <span class="red fPerc80 mL5">*{{ number_format($dataTypeStats["quant"]["req"]) }}</span>
        @endif </nobr>
    </div>
    <div class="col-3 taR">
    
@if (!$isPrint)
    @if ($isAlt)
        <div class="m5"><a class="btn btn-info" 
            @if ($isAll) href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/map?all=1" 
            @else href="/dashboard/tree/map" @endif
            ><i class="fa fa-align-left"></i> Hide Details</a></div>
    @else
        <div class="m5"><a class="btn btn-info" 
            @if ($isAll) href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/map?all=1&alt=1" 
            @else href="/dashboard/tree/map?alt=1" @endif
            ><i class="fa fa-align-left"></i> Show Details</a></div>
    @endif
    @if ($isAll)
        <div class="m5"><a class="btn btn-info" 
            @if ($isAlt) href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/map?alt=1" 
            @else href="/dashboard/tree/map" @endif
            ><i class="fa fa-expand fa-flip-horizontal"></i> Collapse Tree</a></div>
    @else
        <div class="m5"><a class="btn btn-info" 
            @if ($isAlt) href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/map?all=1&alt=1" 
            @else href="/dashboard/tree/map?all=1" @endif
            ><i class="fa fa-expand fa-flip-horizontal"></i> Expand Tree</a></div>
    @endif
@endif
    </div>
</div>