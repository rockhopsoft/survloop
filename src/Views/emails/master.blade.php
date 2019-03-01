<!-- resources/views/emails/master.blade.php -->
<html><head>
<style>{!! $cssColors['css-dump'] !!}</style>
</head><body>
<!-- <center><img id="logoImg" src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
    }}{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}" alt="Logo Image" border=0 ></center> -->
<div class="contentBox">{!! $emaContent !!}</div>
</body></html>