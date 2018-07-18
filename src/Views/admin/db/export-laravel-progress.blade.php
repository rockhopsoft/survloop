<!-- resources/views/vendor/survloop/admin/db/export-laravel-progress.blade.php -->
<nobr><span class="pull-right pT20">{!! strip_tags($dbStats) !!}</span></nobr>
<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> {{ $GLOBALS['SL']->dbRow->DbName 
    	}}</span>: Export for Laravel 
</h1>
<br /><br />
<center>{!! $GLOBALS["SL"]->sysOpts["spinner-code"] !!}</center>
<script type="text/javascript">
setTimeout("window.location='{{ ( (!$GLOBALS['SL']->REQ->has('refresh')) ? '?refresh=2' :
	( (intVal($GLOBALS['SL']->REQ->get('refresh')) > 2) ? 
		((isset($GLOBALS['SL']->x['exportAsPackage']) && $GLOBALS['SL']->x['exportAsPackage'])
			? '/dashboard/sl/export/laravel' : '/dashboard/db/export/laravel')
		: '?refresh=' . (1+intVal($GLOBALS['SL']->REQ->get('refresh'))) ) ) }}'", 3000);
</script>