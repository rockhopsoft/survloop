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
    width: 100%;
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
.slBlueLight, a.slBlueLight:link, a.slBlueLight:visited, a.slBlueLight:active, a.slBlueLight:hover {
	color: {!! $cssColors["color-main-off"] !!};
}
h1 { font-size: 200%; }
h2 { font-size: 175%; }
h3 { font-size: 150%; }
h4 { font-size: 125%; }
h5 { font-size: 110%; }
.fPerc133 { font-size: 133%; }

.contentBox {
    padding: 20px;
    color: {!! $cssColors['color-main-text'] !!};
}
#logoImg {
    max-width: 300px;
    margin-bottom: 10px;
}

.btn.btn-primary, .btn.btn-primary:link, .btn.btn-primary:visited, .btn.btn-primary:active, .btn.btn-primary:hover,
.btn.btn-primary.btn-lg, .btn.btn-primary.btn-lg:link, .btn.btn-primary.btn-lg:visited, .btn.btn-primary.btn-lg:active, .btn.btn-primary.btn-lg:hover,
.btn.btn-primary.btn-xl, .btn.btn-primary.btn-xl:link, .btn.btn-primary.btn-xl:visited, .btn.btn-primary.btn-xl:active, .btn.btn-primary.btn-xl:hover {
    background-color: {!! $cssColors["color-main-on"] !!};
    color: {!! $cssColors["color-main-bg"] !!};
    -moz-border-radius: 10px; border-radius: 10px;
    border: 0px none;
    text-decoration: none;
    padding: 6px 12px;
    font-size: 14px;
}
.btn.btn-primary.btn-lg, .btn.btn-primary.btn-lg:link, .btn.btn-primary.btn-lg:visited, .btn.btn-primary.btn-lg:active, .btn.btn-primary.btn-lg:hover {
    padding: 10px 16px;
    font-size: 18px;
}
.btn.btn-primary.btn-xl, .btn.btn-primary.btn-xl:link, .btn.btn-primary.btn-xl:visited, .btn.btn-primary.btn-xl:active, .btn.btn-primary.btn-xl:hover {
    padding: 15px 20px;
    font-size: 30px;
}
{!! $cssColors['css-dump'] !!}
</style>
</head><body>
<!-- <center><img id="logoImg" src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
    }}{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}" border=0 ></center> -->
<div class="contentBox">{!! $emaContent !!}</div>
</body></html>