<html><head><style>{!! $cssColors['css-dump'] !!}</style>
</head><body><!-- resources/views/emails/password.blade.php --><div class="contentBox"><p>
    We heard that you lost your 
    {{ $GLOBALS["SL"]->sysOpts["site-name"] }} password. 
    Sorry about that!
</p>
<p>
    But don't worry. Please click this 
    link to reset your password:<br />
    <a href="{{ url('password/reset/' . $token) }}" target="_blank"
        >{{ url('password/reset/' . $token) }}</a>
</p>
<p>
    If you didn't mean to reset your password, please 
    ignore this email; your password will not change.
</p>
<p>
    Thanks,
</p>
<p>
    Your friends at {{ $GLOBALS["SL"]->sysOpts["site-name"] }}
</p></div></body></html>