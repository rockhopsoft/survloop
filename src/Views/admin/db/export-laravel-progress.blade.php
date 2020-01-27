<!-- resources/views/vendor/survloop/admin/db/export-laravel-progress.blade.php -->
<div class="container"><div class="slCard mB20">

<nobr><span class="float-right pT20">{!! strip_tags($dbStats) !!}</span></nobr>
<h1>
    <span class="slBlueDark"><i class="fa fa-database"></i> 
    {{ $GLOBALS['SL']->dbRow->db_name }}</span>: Export for Laravel 
</h1>
<br /><br />
<center>
{!! $GLOBALS["SL"]->spinner() !!}
</center>

@if ($GLOBALS["SL"]->REQ->has('tbls') && trim($GLOBALS["SL"]->REQ->get('tbls')) != '')
    <div class="row">
        <div class="col-md-5"> </div>
        <div class="col-md-7"><ul>
        @forelse ($GLOBALS["SL"]->mexplode(',', $GLOBALS["SL"]->REQ->get('tbls')) as $i => $done)
            <li>{{ $done }}</li>
        @empty
        @endforelse
        </ul></div>
    </div>
@endif

</div></div>

@if (isset($nextUrl) && !$GLOBALS["SL"]->REQ->has('done'))
    nextUrl: {{ $nextUrl }}
    <script type="text/javascript">
    setTimeout("window.location='{!! $nextUrl !!}'", 3000);
    </script>
@endif
