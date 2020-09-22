<!-- resources/views/vendor/survloop/admin/db/export-laravel.blade.php -->

<div class="container"><div class="slCard mB20">

<nobr><span class="float-right pT20">{!! strip_tags($dbStats) !!}</span></nobr>
<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
        {{ $GLOBALS['SL']->dbRow->db_name }}</span>: 
    @if (isset($GLOBALS["SL"]->x["exportAsPackage"]) 
        && $GLOBALS["SL"]->x["exportAsPackage"])
        Export for Survloop Extension Package 
    @else Export for Laravel 
    @endif
</h1>

@if (isset($GLOBALS["SL"]->x["exportAsPackage"]) 
    && $GLOBALS["SL"]->x["exportAsPackage"])
	{!! view(
        'vendor.survloop.admin.db.export-sl-tabs', 
        [ "curr" => 'laravel' ]
    )->render() !!}
@else
    {!! view(
        'vendor.survloop.admin.db.export-tabs', 
        [ "curr" => 'laravel' ]
    )->render() !!} 
@endif
<div id="myTabContent" class="tab-content">
    <div class="pT10">
        <!--- <a href="{{ $zipFileMig }}" target="_blank" class="btn btn-sm btn-secondary mR10" disabled 
            >Download Migrations.zip</a>
        <a href="{{ $zipFileModel }}" target="_blank" class="btn btn-sm btn-secondary mR10" disabled 
            >Download Models.zip</a> --->
        <a href="?refreshVendor=1" 
            class="btn btn-sm btn-secondary mR10" 
            >Push Models to Vendor Folder</a>
        <a href="?refresh=1" 
            class="btn btn-sm btn-secondary mR10" 
            >Force Refresh</a>
    </div>
    <div class="p5"></div>
    <h3 class="mB0">Laravel Migration</h3>
    <textarea id="lMigr" class="form-control w100" 
        style="height: 250px;"></textarea>
    <h3 class="mB0">Laravel Seeder</h3>
    <textarea id="lSeed" class="form-control w100" 
        style="height: 250px;"></textarea>
    <h3 class="mB0">Model Files</h3>
    <textarea id="lModl" class="form-control w100" 
        style="height: 250px;"></textarea>
    <div class="p20"></div>
    <script type="text/javascript"> $(document).ready(function(){
        setTimeout(function() { $.get('/dashboard/db/export/dump?which=migrations&url={{ 
            $dumpOut["Migrations"] }}', function( urlContent ) {
                document.getElementById('lMigr').value=urlContent;
            }, 'html');
        }, 1000);
        setTimeout(function() { $.get('/dashboard/db/export/dump?which=models', function( urlContent ) {
                document.getElementById('lModl').value=urlContent;
            }, 'html');
        }, 4000);
        setTimeout(function() { $.get('/dashboard/db/export/dump?which=seeders&url={{ 
            $dumpOut["Seeders"] }}', function( urlContent ) {
                document.getElementById('lSeed').value=urlContent;
            }, 'html');
        }, 8000);
    }); </script>
    
    @if (isset($GLOBALS["SL"]->x["exportAsPackage"]) && $GLOBALS["SL"]->x["exportAsPackage"])
    	
	@else
		<h1>Instructions To Install Your Database Design</h1>
		<h3>(and this entire system on another server)</h3>
		
		<h2>Step 1: Install Laravel & Survloop</h2>
		<p><a href="https://survloop.org/how-to-install-survloop" target="_blank">How To Install Survloop</a></p>
		
		<h2>Step 2: Copy Your Migration and Seeder Files</h2>
		<p>Copy these two generated files into the Database folder in your destination Laravel installation.</p>
		<pre>$ php artisan migrate
$ php artisan db:seed --class=YourDatabaseSeeder</pre>
	@endif

</div>

</div></div>