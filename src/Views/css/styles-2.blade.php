/* generated from resources/views/vendor/survloop/css/styles-2.blade.php */

html, body {
    font-size: 16px;
}
body, p, .slTxt {
    color: {!! $css["color-main-text"] !!};
}
.note-editable p {
    color: #000;
}

#main {
    transition: margin-right .5s;
    width: 100%;
    height: 100%;
}

.halfPageWidth {
	width: 50%;
	min-width: 300px;
	text-align: left;
}

#ajaxWrap {
    display: block;
    width: 100%;
    min-height: 100%;
    overflow: visible;
}
#ajaxWrapLoad {
    display: block;
	width: 100%;
	text-align: center;
	padding: 80px 0px 80px 0px;
	color: {!! $css["color-main-on"] !!};
	font-size: 48pt;
}

#dialog {
    display: none;
    width: 100%;
    padding: 15px;
    text-align: center;
}
#dialogTitle {
    float: left;
    font-size: 22pt;
}
#dialog .card .card-header .dialogClose {
    float: right;
    display: block;
}
#dialog .card .card-body {
    text-align: left;
}
#nondialog {
    display: block;
    width: 100%;
}

#footerLinks {
    display: block;
    max-width: 730px;
}

{!! view(
    'vendor.survloop.css.styles-2-debug', 
    [ "css" => $css ]
)->render() !!}

{!! view(
    'vendor.survloop.css.styles-2-navbar', 
    [ "css" => $css ]
    )->render() !!}

{!! view(
    'vendor.survloop.css.styles-2-prog-bar', 
    [ "css" => $css ]
)->render() !!}

{!! view(
    'vendor.survloop.css.styles-2-social', 
    [ "css" => $css ]
)->render() !!}

{!! view(
    'vendor.survloop.css.styles-2-node-forms', 
    [ "css" => $css ]
)->render() !!}

{!! view(
    'vendor.survloop.css.styles-2-reports', 
    [ "css" => $css ]
)->render() !!}

{!! view(
    'vendor.survloop.css.styles-2-elements', 
    [ "css" => $css ]
)->render() !!}

{!! view(
    'vendor.survloop.css.styles-2-tree-print', 
    [ "css" => $css ]
)->render() !!}

{!! view(
    'vendor.survloop.css.styles-2-tables', 
    [ "css" => $css ]
)->render() !!}

{!! view(
    'vendor.survloop.css.styles-2-bootstrap', 
    [ "css" => $css ]
)->render() !!}

{!! view(
    'vendor.survloop.css.styles-2-admin', 
    [ "css" => $css ]
)->render() !!}

{!! view(
    'vendor.survloop.css.styles-2-utils', 
    [ "css" => $css ]
)->render() !!}

{!! view(
    'vendor.survloop.css.styles-2-other-overs', 
    [ "css" => $css ]
)->render() !!}

{!! view(
    'vendor.survloop.css.styles-2-colors', 
    [ "css" => $css ]
)->render() !!}

{!! view(
    'vendor.survloop.css.styles-2-responsive', 
    [ "css" => $css ]
)->render() !!}

{!! $css["raw"] !!}
