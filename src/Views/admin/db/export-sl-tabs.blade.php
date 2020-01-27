<!-- resources/views/vendor/survloop/db/export-sl-tabs.blade.php -->
<ul id="pageTabs" class="nav nav-tabs">
<li class="nav-item"><a href="/dashboard/sl/export/laravel" 
    class="nav-link @if ($curr == 'laravel') active @endif "
    >Export for Laravel</a></li>
<li class="nav-item"><a href="/dashboard/sl/export" 
    class="nav-link @if ($curr == 'mysql') active @endif "
    >MySQL</a></li>
<!--- <li class="nav-item"><a href="/dashboard/sl/export/settings" 
    class="nav-link @if ($curr == 'settings') active @endif ">Package Options</a></li> --->
</ul>