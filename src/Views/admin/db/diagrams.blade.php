<!-- resources/views/vendor/survloop/admin/db/diagrams.blade.php -->

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->db_name }}</span>: Table Diagrams 
    <nobr>({!! strip_tags($dbStats) !!})</nobr>
</h1>

<a href="#tblDiagSimple" class="btn btn-secondary mR10"
    ><i class="fa fa-angle-double-down"></i> Simplistic Auto-Diagram</a>
<a href="#tblMatrix" class="btn btn-secondary mR10"
    ><i class="fa fa-angle-double-down"></i> Table Relationship Matrix</a>

@forelse ($diags as $cnt => $dia)
    <div class="container">
        <div class="nodeAnchor"><a name="dia{{ $dia->def_id }}"></a></div>
        <br /><br /><hr><b>{{ $dia->def_subset }}</b>
        <a href="/images/diagrams/{{ $GLOBALS['SL']->dbID 
            }}-{{ $dia->def_id }}.png" target="_blank"
            ><img src="/images/diagrams/{{ $GLOBALS['SL']->dbID 
            }}-{{ $dia->def_id }}.png" border=0 width=80% 
            alt="Diagram #{{ $dia->def_id }}" ></a>
    </div>
@empty
@endforelse


<div class="nodeAnchor"><a name="tblDiagSimple"></a></div>
<div class="mT20"><iframe width=100% height=1000 frameborder=0 
    @if ($GLOBALS["SL"]->REQ->has('refresh')) 
        src="/dashboard/db/network-map?iframe=1&refresh=1"
    @else src="/dashboard/db/network-map?iframe=1" 
    @endif ></iframe></div>

<div class="nodeAnchor"><a name="tblMatrix"></a></div><br /><br /><br /><hr>
<h2>Table Relationship Matrix</h2>

{!! $printMatrix !!}

<div class="adminFootBuff"></div>

<style>
table.keyMatrix tr th { font-size: 10pt; padding: 3px; }
table.keyMatrix tr td { text-align: center; }
</style>