<!-- resources/views/vendor/survloop/admin/db/diagrams.blade.php -->

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>:
    Table Diagrams 
    <nobr><span class="f14">({!! strip_tags($dbStats) !!})</span></nobr>
</h1>

<a href="#tblMatrix" class="btn btn-default mR10"><i class="fa fa-angle-double-down"></i> <b>Table Relationship Matrix</a>
<a href="#tblDiagSimple" class="btn btn-default mR10"><i class="fa fa-angle-double-down"></i> <b>Simplistic Auto-Diagram</a>

@forelse ($diags as $cnt => $dia)
    <div class="container">
        <a name="dia{{ $dia->DefID }}"></a>
        <br /><br /><hr><b>{{ $dia->DefSubset }}</b>
        <a href="/images/diagrams/{{ $GLOBALS['SL']->dbID }}-{{ $dia->DefID }}.png" target="_blank"
        ><img src="/images/diagrams/{{ $GLOBALS['SL']->dbID }}-{{ $dia->DefID }}.png" border=0 width=80% ></a>
    </div>
@empty
@endforelse

<a name="tblMatrix"></a><br /><br /><br /><hr>
<h2>Table Relationship Matrix</h2>

{!! $printMatrix !!}

<style>
table.keyMatrix { border-collapse: collapse; background: #FFF; }
table.keyMatrix tr th { font-size: 10pt; padding: 5px; }
table.keyMatrix tr td { text-align: center; }
table.keyMatrix tr.row2 { background: #EEE; }
table.keyMatrix tr td.col1, table.keyMatrix tr th.col1 { border: 1px #000 solid; border-right: 1px #999 solid; }
table.keyMatrix tr td.col2, table.keyMatrix tr th.col2 { border: 1px #000 solid; border-left: 1px #999 solid; }
table.keyMatrix td.mid { background: #AAA; color: #FFF; border: 1px #000 solid; }
</style>


<br /><br /><a name="tblDiagSimple"></a><br /><hr><br />
<iframe src="/dashboard/db/network-map" width=100% height=1000 frameborder=0 ></iframe>
