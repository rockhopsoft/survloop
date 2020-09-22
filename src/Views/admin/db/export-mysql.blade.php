<!-- resources/views/vendor/survloop/admin/db/export-mysql.blade.php -->

<div class="container"><div class="slCard mB20">

<nobr><span class="float-right pT20">{!! strip_tags($dbStats) !!}</span></nobr>
<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->db_name }}</span>: MySQL Export 
    @if (isset($GLOBALS["SL"]->x["exportAsPackage"]) 
        && $GLOBALS["SL"]->x["exportAsPackage"])
        for Survloop Extension Package
    @endif
</h1>

@if (isset($GLOBALS["SL"]->x["exportAsPackage"]) && $GLOBALS["SL"]->x["exportAsPackage"])
    {!! view(
        'vendor.survloop.admin.db.export-sl-tabs', 
        [ "curr" => 'mysql' ]
    )->render() !!}
@else
    {!! view(
        'vendor.survloop.admin.db.export-tabs', 
        [ "curr" => 'mysql' ]
    )->render() !!} @endif
<div id="myTabContent" class="tab-content">
    <textarea id="mysqlDump" class="w100">{!! $export !!}</textarea>
</div>

</div></div>

<script type="text/javascript">
setTimeout("flexAreaAdjust(document.getElementById('mysqlDump'))", 100);
</script>