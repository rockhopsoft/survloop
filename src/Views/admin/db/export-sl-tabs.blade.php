<!-- Stored in resources/views/vendor/survloop/db/export-sl-tabs.blade.php -->
<ul id="pageTabs" class="nav nav-tabs">
<li @if ($curr == 'laravel') class="active" @endif ><a href="/dashboard/sl/export/laravel">Export for Laravel</a></li>
<li @if ($curr == 'mysql') class="active" @endif ><a href="/dashboard/sl/export">MySQL</a></li>
<!--- <li @if ($curr == 'settings') class="active" @endif ><a href="/dashboard/sl/export/settings"
    >Package Options</a></li> --->
</ul>