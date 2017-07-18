<!-- Stored in resources/views/vendor/survloop/admin/contact-tabs.blade.php -->
<li @if ($filtStatus == 'unread') class="active" @endif ><a href="?tab=unread">Unread 
    @if ($recTots['Unread'] > 0) <span class="badge mL5">{{ $recTots['Unread'] }}</span> @endif
    </a></li>
<li @if ($filtStatus == 'all') class="active" @endif ><a href="?tab=all">All 
    @if (($recTots['Unread']+$recTots['Read']) > 0) 
        <span class="badge mL5">{{ ($recTots['Unread']+$recTots['Read']) }}</span> @endif
    </a></li>
<li @if ($filtStatus == 'trash') class="active" @endif ><a href="?tab=trash">Trash 
    @if ($recTots['Trash'] > 0) <span class="badge mL5">{{ $recTots['Trash'] }}</span> @endif
    </a></li>