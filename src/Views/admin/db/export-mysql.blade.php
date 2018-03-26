<!-- resources/views/vendor/survloop/admin/db/export-mysql.blade.php -->

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>: MySQL Export 
    <nobr><span class="f14">({!! strip_tags($dbStats) !!})</span></nobr>
</h1>

<div class="clearfix p20"></div>

<textarea id="mysqlDump" style="width: 100%; height: 5000px;">
{!! $export !!}
</textarea>
<script type="text/javascript">
setTimeout("flexAreaAdjust(document.getElementById('mysqlDump'))", 100);
</script>