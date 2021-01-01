<!-- resources/views/vendor/survloop/admin/contact-tabs.blade.php -->
<li class="nav-item">
    <a href="?tab=unread" class="nav-link @if ($filtStatus == 'unread') active @endif "
        >Unread 
    @if ($recTots['Unread'] > 0)
        <span class="badge mL5">{{ number_format($recTots['Unread']) }}</span>
    @endif
    </a>
</li>
<li class="nav-item">
    <a href="?tab=all" class="nav-link @if ($filtStatus == 'all') active @endif "
        >All 
    @if (($recTots['Unread']+$recTots['Read']) > 0)
        <span class="badge mL5">{{ number_format($recTots['Unread']+$recTots['Read']) }}</span>
    @endif
    </a>
</li>
<li class="nav-item">
    <a href="?tab=archive" class="nav-link @if ($filtStatus == 'archive') active @endif "
        >Archive
    @if (($recTots['Archive']) > 0) 
        <span class="badge mL5">{{ number_format($recTots['Archive']) }}</span>
    @endif
    </a>
</li>
<li class="nav-item">
    <a href="?tab=trash" class="nav-link @if ($filtStatus == 'trash') active @endif "
        >Trash 
    @if ($recTots['Trash'] > 0) 
        <span class="badge mL5">{{ number_format($recTots['Trash']) }}</span> 
    @endif
    </a>
</li>