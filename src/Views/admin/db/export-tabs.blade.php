<!-- resources/views/vendor/survloop/db/export-tabs.blade.php -->
<ul id="pageTabs" class="nav nav-tabs">
<li class="nav-item"><a href="/dashboard/db/export" 
    class="nav-link @if ($curr == 'mysql') active @endif "
    >MySQL</a></li>
<li class="nav-item"><a href="/dashboard/db/export/laravel" 
    class="nav-link @if ($curr == 'laravel') active @endif "
    >Export for Laravel</a></li>
<li class="nav-item"><a href="/dashboard/db/install" 
    class="nav-link @if ($curr == 'install') active @endif "
    >Auto-Install Here</a></li>
</ul>