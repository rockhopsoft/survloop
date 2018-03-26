<!-- resources/views/vendor/survloop/admin/db/diagrams.blade.php -->

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>: Table Diagrams 
    <nobr><span class="f14">({!! strip_tags($dbStats) !!})</span></nobr>
</h1>

<a href="#tblDiagSimple" class="btn btn-default mR10"
    ><i class="fa fa-angle-double-down"></i> Simplistic Auto-Diagram</a>
<a href="#tblMatrix" class="btn btn-default mR10"
    ><i class="fa fa-angle-double-down"></i> Table Relationship Matrix</a>

@forelse ($diags as $cnt => $dia)
    <div class="container">
        <a name="dia{{ $dia->DefID }}"></a>
        <br /><br /><hr><b>{{ $dia->DefSubset }}</b>
        <a href="/images/diagrams/{{ $GLOBALS['SL']->dbID }}-{{ $dia->DefID }}.png" target="_blank"
        ><img src="/images/diagrams/{{ $GLOBALS['SL']->dbID }}-{{ $dia->DefID }}.png" border=0 width=80% ></a>
    </div>
@empty
@endforelse


<a name="tblDiagSimple"></a>
<div class="mT20"><iframe width=100% height=1000 frameborder=0 
    @if ($GLOBALS["SL"]->REQ->has('refresh')) src="/dashboard/db/network-map?iframe=1&refresh=1"
    @else src="/dashboard/db/network-map?iframe=1" @endif ></iframe></div>

<a name="tblMatrix"></a><br /><br /><br /><hr>
<h2>Table Relationship Matrix</h2>

{!! $printMatrix !!}

<div class="adminFootBuff"></div>

<style>
table.keyMatrix tr th { font-size: 10pt; padding: 3px; }
table.keyMatrix tr td { text-align: center; }
</style>