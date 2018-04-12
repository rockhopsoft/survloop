<!-- resources/views/vendor/survloop/admin/db/export-laravel.blade.php -->

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->DbName }}</span>: Export for Laravel 
    <nobr><span class="f14">({!! strip_tags($dbStats) !!})</span></nobr>
</h1>

<a href="{{ $zipFileMig }}" target="_blank" class="btn btn-sm btn-default mR10" disabled >Download Migrations.zip</a>
<a href="{{ $zipFileModel }}" target="_blank" class="btn btn-sm btn-default mR10" disabled >Download Models.zip</a>
<a href="?refreshVendor=1" class="btn btn-sm btn-default mR10" >Push Models to Vendor Folder</a>
<a href="?refresh=1" class="btn btn-sm btn-default mR10" >Force Refresh</a>

<div class="p5"></div>
<p>Preview Laravel Migration, Seeder, and Model files:</p>
<div class="row">
    <div class="col-md-4">
        <textarea class="f12" style="width: 100%; height: 400px;">
        {!! $dumpOut["Migrations"] !!}
        </textarea>
    </div>
    <div class="col-md-4">
        <textarea class="f12" style="width: 100%; height: 400px;">
        {!! $dumpOut["Seeders"] !!}
        </textarea>
    </div>
    <div class="col-md-4">
        <textarea class="f12" style="width: 100%; height: 400px;">
        {!! $dumpOut["Models"] !!}
        </textarea>
    </div>
</div>

<h1>Instructions To Install Your Database Design</h1>
<h3>(and this entire system on another server)</h3>

<h2>Step 1: Install Laravel & SurvLoop</h2>
<p><a href="https://survloop.org/how-to-install-survloop" target="_blank">How To Install SurvLoop</a></p>

<h2>Step 2: Include Your Exports In The Database Folder</h2>
<p>...</p>

