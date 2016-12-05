<!-- resources/views/vendor/survloop/admin/db/export-laravel.blade.php -->

<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS["DB"]->dbRow->DbName }}</span>:
    Export for Laravel 
    <nobr><span class="f14">({!! strip_tags($dbStats) !!})</span></nobr>
</h1>

<a href="/dashboard/db/export" class="btn btn-default mR10">MySQL Export</a>
<a href="/dashboard/db/install" class="btn btn-default mR10">Auto-Install Database Design</a>

<a href="{{ $zipFileMig }}" target="_blank" class="btn btn-lg btn-primary mR10" disabled >Download All: LaravelMigrations.zip</a> 
<a href="{{ $zipFileModel }}" target="_blank" class="btn btn-lg btn-primary mR10" disabled >Download All: LaravelModels.zip</a>

<div class="p5"></div>
Preview Laravel Migration, Seeder, and Model files. <a href="?refresh=1"><i>Force Refresh</i></a>
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

<h1>Instructions To Install Your Database Design<br />
(and this entire system on another server)</h1>

<h2>Step 1: Install Laravel 5.1</h2>
Maybe choose a distinct name instead of "MyUniqueSurvLoop"
<pre><code>
$ composer global require "laravel/installer=~1.1"
$ composer create-project laravel/laravel MyUniqueSurvLoop "5.1.*"
$ cd MyUniqueSurvLoop
$ php artisan app:name SurvLoop
</code></pre>
The internet knows far more than I...
<ul>
    <li><a href="https://laravel.com/docs/5.1/" target="_blank">https://laravel.com/docs/5.1/</a></li>
    <li><a href="https://laravel.com/docs/5.1/homestead" target="_blank">https://laravel.com/docs/5.1/homestead</a></li>
    <li><a href="https://laracasts.com/series/laravel-5-from-scratch" target="_blank">https://laracasts.com/series/laravel-5-from-scratch</a></li>
</ul>

<h2>Step 2: Manually Install Dependent Packages</h2>
(Sorry this isn't a proper package yet)
<ol>
    <li><a href="https://github.com/kodeine/laravel-acl/" target="_blank">https://github.com/kodeine/laravel-acl/</a></li>
    <?php /* <li><a href="https://github.com/cmgmyr/laravel-messenger" target="_blank">https://github.com/cmgmyr/laravel-messenger</a></li> */ ?>
</ol>


