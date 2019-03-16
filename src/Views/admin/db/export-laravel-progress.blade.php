<!-- resources/views/vendor/survloop/admin/db/export-laravel-progress.blade.php -->
<nobr><span class="float-right pT20">{!! strip_tags($dbStats) !!}</span></nobr>
<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>: Export for Laravel 
</h1>
<br /><br />
<center>
{!! $GLOBALS["SL"]->spinner() !!}
<h3>{!! str_replace('?refresh=2&tbl=', '', $nextUrl) !!}</h3>
</center>
<script type="text/javascript">
setTimeout("window.location='{!! $nextUrl !!}'", 3000);
</script>