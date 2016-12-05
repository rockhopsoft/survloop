<!-- resources/views/vendor/survloop/admin/db/export-mysql.blade.php -->

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS["DB"]->dbRow->DbName }}</span>:
    MySQL Export 
    <nobr><span class="f14">({!! strip_tags($dbStats) !!})</span></nobr>
</h1>

<a href="/dashboard/db/export/laravel" class="btn btn-default mR10">Export for Laravel</a>
<a href="/dashboard/db/install" class="btn btn-default mR10">Auto-Install Database Design</a>

<div class="clearfix p20"></div>

<textarea style="width: 100%; height: 5000px;">
{!! $export !!}
</textarea>