<!-- resources/views/vendor/survloop/reports/inc-tree-node-type-stats.blade.php -->
<div class="row">
    <div class="col-6 col-md-4 pB20">
        <b>Total Survey Questions</b><br />
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
    <div class="col-6 col-md-4 pB20">
        <b>Unique <nobr>Data Fields</nobr></b><br />
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
    <div class="col-6 col-md-4 pB20">
        <b>Survey Branching Tree</b><br />
        {{ number_format($qTypeStats["nodes"]["tot"]) }} Nodes Total<br />
        <span @if ($qTypeStats["nodes"]["loopNodes"] == 0) class="slGrey" @endif >
            {{ number_format($qTypeStats["nodes"]["loopNodes"]) }} Looped Nodes</span><br />
        <span @if ($qTypeStats["nodes"]["loops"] == 0) class="slGrey" @endif >
            {{ number_format($qTypeStats["nodes"]["loops"]) }} Loops Total</span>
    </div>
</div>