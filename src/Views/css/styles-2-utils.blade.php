/* generated from resources/views/vendor/survloop/css/styles-2-utils.blade.php */
<?php /* some chunks of this one definitely needs to be migrated away from, and deleted */ ?>

.ww { 
  word-wrap: break-word; 
}

.dont-break-out {
  /* These are technically the same, but use both */
  overflow-wrap: break-word;
  word-wrap: break-word;

  -ms-word-break: break-all;
  /* This is the dangerous one in WebKit, as it breaks things wherever */
  word-break: break-all;
  /* Instead use this non-standard one: */
  word-break: break-word;

  /* Adds a hyphen where the word breaks, if supported (No Blink) */
  -ms-hyphens: auto;
  -moz-hyphens: auto;
  -webkit-hyphens: auto;
  hyphens: auto;
}

.hidden {
	position:absolute;
	left:-10000px;
	top:auto;
	width:1px;
	height:1px;
	overflow:hidden;
}

@foreach ([20, 22, 24, 26, 28, 30, 32, 36, 48, 60] as $size)
.f{{ $size }}, table tr td.f{{ $size }}, i.f{{ $size }}, a.f{{ $size }}:link, a.f{{ $size }}:active, a.f{{ $size }}:visited, a.f{{ $size }}:hover, input.f{{ $size }} , select.f{{ $size }} , textarea.f{{ $size }} { font-size: {{ $size }}pt; }
@endforeach

@foreach ([40, 50, 66, 80, 125, 133, 200, 300, 400, 500] as $perc)
.fPerc{{ $perc }}, table tr td.fPerc{{ $perc }}, i.fPerc{{ $perc }}, 
a.fPerc{{ $perc }}:link, a.fPerc{{ $perc }}:active, 
a.fPerc{{ $perc }}:visited, a.fPerc{{ $perc }}:hover, 
input.fPerc{{ $perc }}, select.fPerc{{ $perc }}, textarea.fPerc{{ $perc }} {
    font-size: {{ $perc }}%;
}
@endforeach

@foreach ([10, 13, 16, 18, 20, 24, 30, 38] as $height)
.lH{{ $height }}, table tr td.lH{{ $height }}, 
a.lH{{ $height }}:link, a.lH{{ $height }}:active, 
a.lH{{ $height }}:visited, a.lH{{ $height }}:hover { 
    line-height: {{ $height }}px; 
}
@endforeach

.lS0, table tr td.lS0, a.lS0:link, a.lS0:active, 
a.lS0:visited, a.lS0:hover { 
    letter-spacing: 0em; 
}
.lS1, table tr td.lS1, a.lS1:link, a.lS1:active, 
a.lS1:visited, a.lS1:hover {
    letter-spacing: -0.01em; 
}
.lS2, table tr td.lS2, a.lS2:link, a.lS2:active, 
a.lS2:visited, a.lS2:hover {
    letter-spacing: -0.02em; 
}
.lS3, table tr td.lS3, a.lS3:link, a.lS3:active, 
a.lS3:visited, a.lS3:hover {
    letter-spacing: -0.03em; 
}
.lS4, table tr td.lS4, a.lS4:link, a.lS4:active, 
a.lS4:visited, a.lS4:hover {
    letter-spacing: -0.04em; 
}
.lS6, table tr td.lS6, a.lS6:link, a.lS6:active, 
a.lS6:visited, a.lS6:hover {
    letter-spacing: -0.06em; 
}
.lSp2, table tr td.lSp2, a.lSp2:link, a.lSp2:active, 
a.lSp2:visited, a.lSp2:hover { 
    letter-spacing: 0.02em; 
}
.lSp4, table tr td.lSp4, a.lSp4:link, a.lSp4:active, 
a.lSp4:visited, a.lSp4:hover { 
    letter-spacing: 0.04em; 
}

.under, table tr td.under, a.under:link, 
a.under:active, a.under:visited, a.under:hover { 
    text-decoration: underline; 
}
.noUnd, a.noUnd:link, a.noUnd:active, 
a.noUnd:visited, a.noUnd:hover { 
    text-decoration: none; 
}
a.overUnd:link, a.overUnd:active, 
a.overUnd:visited, a.overUnd:hover { 
    text-decoration: none; 
}
a.overUnd:hover {
    text-decoration: underline; 
}

.fixDiv {
    position: fixed; 
}
.relDiv, .relDivMini, table tr td .relDiv {
    position: relative;
    vertical-align: top;
    text-align: left;
}
.absDiv, table tr td .absDiv {
    position: absolute;
    vertical-align: top;
    text-align: left; 
}
.relDivMini {
    height: 1px; 
    width: 1px;
}

.hidSelf {
    cursor: pointer;
}

.fL {
    float: left;
}
.fR {
    float: right; 
}
.fC {
    clear: both; 
}

.h100, table.h100, table tr td.h100 { 
    height: 100%; 
}
.h50, table.h50, table tr td.h50 { 
    height: 50%; 
}
.wAuto, h1.wAuto, h2.wAuto, h3.wAuto, h4.wAuto, h5.wAuto,
a.wAuto:link, a.wAuto:visited, a.wAuto:active, a.wAuto:hover {
    width: auto;
}
@foreach ([100, 95, 90, 85, 80, 75, 66, 60, 50, 48, 45, 
    40, 35, 33, 31, 30, 25, 23, 20, 15, 10, 5, 1] as $width)
.w{{ $width }}, table.w{{ $width }}, table tr td.w{{ $width }}, 
input.w{{ $width }}, select.w{{ $width }}, textarea.w{{ $width }} { 
    width: {{ $width }}%; 
}
@endforeach

.zind0 { 
    z-index: 0; 
}
.zind100 { 
    z-index: 100; 
}

.vaT, table tr td.vaT { 
    vertical-align: top; 
}
.vaM, table tr td.vaM {
    vertical-align: middle; 
}
.vaB, table tr td.vaB { 
    vertical-align: bottom; 
}
.taL, table tr td.taL { 
    text-align: left; 
}
.taC, table tr td.taC { 
    text-align: center; 
}
.taR, table tr td.taR { 
    text-align: right; 
}
.justMe, table tr td.justMe { 
    text-align: justify; 
    text-align-last: justify; 
    text-justify: inter-word; 
    width: 100%; 
}
.unJust, table tr td.unJust { 
    text-align: left; 
}
a.undL, a.undL:visited, a.undL:active, a.undL:hover { 
    text-decoration: underline; 
}

a.noPoint, a.noPoint:link, a.noPoint:visited, 
a.noPoint:active, a.noPoint:hover,
a.btn.noPoint, a.btn.noPoint:link, a.btn.noPoint:visited, 
a.btn.noPoint:active, a.btn.noPoint:hover {
    cursor: default;
}
.crsrPntr { 
    cursor: pointer; 
}
select.form-control { 
    cursor: pointer; 
}

/* Can Be Read Like... margin Left 5, margin Bottom negative 5 */
h1.m0, h2.m0, h3.m0 { 
    margin: 0px; 
}

@foreach ([0, 3, 5, 10, 15, 20, 25, 30, 50] as $px)
    .m{{ $px }}, p.m{{ $px }}, 
    h1.m{{ $px }}, h2.m{{ $px }}, h3.m{{ $px }} {
        margin: {{ $px }}px; 
    }
    .mL{{ $px }}, p.mL{{ $px }}, 
    h1.mL{{ $px }}, h2.mL{{ $px }}, h3.mL{{ $px }} {
        margin-left: {{ $px }}px;
    }
    .mR{{ $px }}, p.mR{{ $px }}, 
    h1.mR{{ $px }}, h2.mR{{ $px }}, h3.mR{{ $px }} {
        margin-right: {{ $px }}px;
    }
    .mT{{ $px }}, p.mT{{ $px }}, 
    h1.mT{{ $px }}, h2.mT{{ $px }}, h3.mT{{ $px }} { 
        margin-top: {{ $px }}px; 
    } 
    .mB{{ $px }}, p.mB{{ $px }}, 
    h1.mB{{ $px }}, h2.mB{{ $px }}, h3.mB{{ $px }} { 
        margin-bottom: {{ $px }}px; 
    }
    @if ($px > 0)
    .mLn{{ $px }}, p.mLn{{ $px }}, 
    h1.mLn{{ $px }}, h2.mLn{{ $px }}, h3.mLn{{ $px }} { 
        margin-left: -{{ $px }}px; 
    } 
    .mRn{{ $px }}, p.mRn{{ $px }}, 
    h1.mRn{{ $px }}, h2.mRn{{ $px }}, h3.mRn{{ $px }} { 
        margin-right: -{{ $px }}px; 
    } 
    .mTn{{ $px }}, p.mTn{{ $px }}, 
    h1.mTn{{ $px }}, h2.mTn{{ $px }}, h3.mTn{{ $px }} { 
        margin-top: -{{ $px }}px; 
    } 
    .mBn{{ $px }}, p.mBn{{ $px }}, 
    h1.mBn{{ $px }}, h2.mBn{{ $px }}, h3.mBn{{ $px }} { 
        margin-bottom: -{{ $px }}px; 
    }
    @endif
    .p{{ $px }}, table tr td.p{{ $px }} { 
        padding: {{ $px }}px; 
    } 
    .pL{{ $px }}, table tr td.pL{{ $px }} { 
        padding-left: {{ $px }}px; 
    } 
    .pR{{ $px }}, table tr td.pR{{ $px }} { 
        padding-right: {{ $px }}px; 
    } 
    .pT{{ $px }}, table tr td.pT{{ $px }} { 
        padding-top: {{ $px }}px; 
    } 
    .pB{{ $px }}, table tr td.pB{{ $px }} { 
        padding-bottom: {{ $px }}px; 
    }
@endforeach

.mRp1 { 
    margin-right: 1%; 
}
.mRp2 { 
    margin-right: 2%; 
}

@foreach ([0, 5, 10, 15, 20, 30] as $px)
    .round{{ $px }} {
        -moz-border-radius: {{ $px }}px;
        border-radius: {{ $px }}px; 
    }
@endforeach

.opac1 {
    opacity:0.01; 
    filter:alpha(opacity=1); 
}
@foreach ([10, 20, 25, 33, 50, 66, 75, 80, 85, 90, 95, 99] as $prc)
    .opac{{ $prc }} {
        opacity:0.{{ $prc }};
        filter:alpha(opacity={{ $prc }}); 
    }
@endforeach
.opac100 { 
    opacity:1.00; 
    filter:alpha(opacity=100); 
}

.wrdBrkAll, a.wrdBrkAll:link, a.wrdBrkAll:visited, 
a.wrdBrkAll:active, a.wrdBrkAll:hover { 
    word-break: break-all; 
}

.monospacer, textarea.monospacer {
    font-family: Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;
}

