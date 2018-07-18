<!-- Stored in resources/views/vendor/survloop/db/export-tabs.blade.php -->
<ul id="pageTabs" class="nav nav-tabs">
<li @if ($curr == 'mysql') class="active" @endif ><a href="/dashboard/db/export">MySQL</a></li>
<li @if ($curr == 'laravel') class="active" @endif ><a href="/dashboard/db/export/laravel">Export for Laravel</a></li>
<li @if ($curr == 'install') class="active" @endif ><a href="/dashboard/db/install">Auto-Install Here</a></li>
</ul>