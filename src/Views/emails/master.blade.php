<!-- resources/views/emails/master.blade.php -->
<html><head>
<style>
body, p, div, table tr td, table tr th, input, textarea, select {
    font-family: {!! $cssColors["font-main"] !!};
    font-style: normal;
    font-weight: 200;
}
b, h1, h2, h3, h4, h5, h6 {
    font-family: {!! $cssColors["font-main"] !!};
    font-weight: 400;
}
body {
	margin: 0px;
	padding: 0px;
    background: {!! $cssColors["color-main-bg"] !!};
}
body, p {
    color: {!! $cssColors["color-main-text"] !!};
}
body, p, div, input, select, textarea, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
    line-height: 1.42857143;
}
a:link, a:visited, a:active, a:hover {
    color: {!! $cssColors["color-main-link"] !!};
}
.red, a.red:link, a.red:visited, a.red:active, a.red:hover,
.slRedDark, a.slRedDark:link, a.slRedDark:visited, a.slRedDark:active, a.slRedDark:hover {
    color: {!! $cssColors["color-danger-on"] !!};
}
.slBlueDark, a.slBlueDark:link, a.slBlueDark:visited, a.slBlueDark:active, a.slBlueDark:hover {
	color: {!! $cssColors["color-main-on"] !!};
}
h1 { font-size: 200%; }
h2 { font-size: 175%; }
h3 { font-size: 150%; }
h4 { font-size: 125%; }
h5 { font-size: 110%; }
.fPerc133 { font-size: 133%; }

.borderBox {
    margin: 10px; 
    -moz-border-radius: 10px; border-radius: 10px;
    border: 2px {!! $cssColors['color-main-bg'] !!} solid;
}
#logoImg {
    margin: 10px;
    height: 60px;
    max-width: 600px;
}
{!! $cssColors['css-dump'] !!}
</style>
</head><body>
<div class="borderBox">
    <center><img id="logoImg" src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}" border=0 ></center>
    <div class="borderBox" style="padding: 20px; color: {!! $cssColors['color-main-text'] !!};">
        {!! $emaContent !!}
    </div>
</div>

</body></html>