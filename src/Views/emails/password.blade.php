<!-- resources/views/emails/password.blade.php -->
<html><head>
<style>{!! $cssColors['css-dump'] !!}</style>
</head><body>
<div class="contentBox">
    <h4>Hello!</h4>
    <p>
        You are receiving this email because 
        we received a password reset request for your 
        {{ $GLOBALS["SL"]->sysOpts["site-name"] }} account.
    </p>
    <p>
        Click here to reset your password: 
        {{ url('password/reset/?token=' . $token) }}
    <?php /* {{ url('password/reset/' . $token) }} */ ?>
    </p>
    <p>
        If you did not request a password reset, 
        no further action is required.
    </p>
    <p>
        Regards,<br />
        {{ $GLOBALS["SL"]->sysOpts["site-name"] }}
    </p>
</div>
</body></html>