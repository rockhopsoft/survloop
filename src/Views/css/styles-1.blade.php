/* generated from resources/views/vendor/survloop/css/styles-1.blade.php */

html, body {
    height: 100%;
}
body {
    min-height: 100%;
    width: 100%;
    overflow-x: hidden;
}
body, p, div, table tr td, table tr th, input, textarea, select {
    font-family: {!! $css["font-main"] !!};
    font-style: normal;
    font-weight: 200;
}
b, h1, h2, h3, h4, h5, h6 {
    font-family: {!! $css["font-main"] !!};
    font-weight: 300;
}
b { font-weight: 400; }

body, .slBg {
	margin: 0px;
	padding: 0px;
    background: {!! $css["color-main-bg"] !!};
}

body, p, div, input, select, textarea, 
.h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
    line-height: 1.42857143;
}

img { 
    border: 0; 
}

a:link, a:visited, a:active, a:hover, 
.clrLnk, a.clrLnk:link, a.clrLnk:visited, 
a.clrLnk:active, a.clrLnk:hover {
    color: {!! $css["color-main-link"] !!};
}

.disIn {
    display: inline;
}
.disNon {
    display: none;
}
.disBlo { 
    display: block; 
}
.disFlx { 
    display: flex; 
}
.disRow { 
    display: table-row; 
}
.ovrNo  { 
    overflow: hidden; 
}
.ovrSho { 
    overflow: visible; 
}
.ovrFlo { 
    overflow: auto; 
}
.ovrFloY { 
    overflow-y: auto; 
    overflow-x: hidden; 
}
