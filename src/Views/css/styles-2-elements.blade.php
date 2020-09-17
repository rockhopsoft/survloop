/* generated from resources/views/vendor/survloop/css/styles-2-elements.blade.php */

.slCard {
    width: 100%;
    padding: 20px;
    background: {!! $css["color-main-bg"] !!};
    box-shadow: 0px 2px 4px {!! $css["color-main-grey"] !!};
}
.slCard.nodeWrap {
    margin-bottom: 30px;
}

.prevImg {
    position: relative;
    width: 100%;
    //padding-top: 100%; /* 1:1 Aspect Ratio */
    padding-bottom: 52.35%; /* 1.91 Aspect Ratio? */
    //padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
    //padding-top: 75%; /* 4:3 Aspect Ratio */
    //padding-top: 66.66%; /* 3:2 Aspect Ratio */
    //padding-top: 62.5%; /* 8:5 Aspect Ratio */
    overflow: hidden;
    border-bottom: 1px solid {!! $css["color-main-on"] !!};
}
.prevImg.brd {
    border: 1px solid {!! $css["color-main-on"] !!};
}
.prevImg img {
    position: absolute;
    top: 0px;
    left: 0px;
    width: 100%;
}

.imgFileLibrary, a.imgFileLibrary:link, a.imgFileLibrary:visited, a.imgFileLibrary:active, a.imgFileLibrary:hover {
    font-size: 11px;
    line-height: 13px;
}

.imgPreload {
    opacity:0.01; filter:alpha(opacity=1);
    width: 100%;
    height: 1px;
    margin-bottom: -1px;
}
.imgPreload img {
    width: 1px;
    height: 1px;
    display: inline;
    border: 0px none;
}

.tmbRound { 
    width: 140px; 
    height: 140px; 
    -moz-border-radius: 70px; 
    border-radius: 70px; 
}
.bigTmbRound, .bigTmbRoundDiv {
    width: 125px;
    height: 125px; 
    -moz-border-radius: 62px; 
    border-radius: 62px; 
    overflow: hidden; 
}
.bigTmbRoundDiv img {
    width: 125px; 
    min-height: 125px; 
}

.hugTmbRound, .hugTmbRoundDiv { 
    width: 175px; 
    height: 175px; 
    -moz-border-radius: 87px; 
    border-radius: 87px; 
    overflow: hidden; 
}
.hugTmbRoundDiv img { 
    width: 175px; 
    min-height: 175px; 
}
.hugTmbRoundDiv { 
    border: 2px {!! $css["color-main-bg"] !!} solid; 
    box-shadow: 0px 0px 2px {!! $css["color-main-text"] !!}; 
}

.litRedDot, .litRedDottie {
    background: {!! $css["color-danger-on"] !!};
    width: 10px;
    height: 10px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    overflow: hidden;
}
.litRedDot .img, .litRedDottie .img {
    width: 10px;
    height: 10px;
    display: inline;
    border: 0px none;
}
.litRedDottie {
    background: none;
    border: 1px {!! $css["color-danger-on"] !!} solid;
}

.icoBig, i.icoBig, .icoBig i { 
    font-size: 54px; 
}
.icoHuge, i.icoHuge, .icoHuge i { 
    font-size: 82px; 
}
.icoMssv, i.icoMssv, .icoMssv i { 
    font-size: 120px; 
}

.responsive-video, .nPrompt .responsive-video {
    width: 100%;
    position: relative;
    padding-bottom: 56.25%;
    padding-top: 60px; overflow: hidden;
}
iframe.responsive-video, .responsive-video iframe, 
.nPrompt .responsive-video iframe,
object.responsive-video, .responsive-video object, 
.nPrompt .responsive-video object,
embed.responsive-video, .responsive-video embed, 
.nPrompt .responsive-video embed {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.proTip {
    width: 100%;
    text-align: center;
}

.sliGalSlide {
    width: 100%;
    
}
.sliNavDiv {
    width: 100%;
    height: 30px;
    text-align: center;
}
a.sliNav, .sliNavDiv a.sliNav, 
.sliNavDiv a.sliNav:link, .sliNavDiv a.sliNav:active, 
.sliNavDiv a.sliNav:visited, .sliNavDiv a.sliNav:hover,
a.sliNavAct, .sliNavDiv a.sliNavAct, 
.sliNavDiv a.sliNavAct:link, .sliNavDiv a.sliNavAct:active, 
.sliNavDiv a.sliNavAct:visited, .sliNavDiv a.sliNavAct:hover {
    color: {!! $css["color-main-on"] !!};
    font-size: 12pt;
    margin: 5px;
}
a.sliNav, .sliNavDiv a.sliNav, 
.sliNavDiv a.sliNav:link, .sliNavDiv a.sliNav:active, 
.sliNavDiv a.sliNav:visited, .sliNavDiv a.sliNav:hover {
    opacity:0.33; filter:alpha(opacity=33);
}
.sliNavDiv a.sliLft:link, .sliNavDiv a.sliLft:active, 
.sliNavDiv a.sliLft:visited, .sliNavDiv a.sliLft:hover, 
.sliNavDiv a.sliRgt:link, .sliNavDiv a.sliRgt:active, 
.sliNavDiv a.sliRgt:visited, .sliNavDiv a.sliRgt:hover {
    width: 20%;
    height: 30px;
    padding: 0px;
    margin: 0px;
    font-size: 16pt;
    text-shadow: -1px 1px 1px {!! $css["color-main-faint"] !!};
    float: left;
    text-align: left;
}
.sliNavDiv a.sliRgt:link, .sliNavDiv a.sliRgt:active, 
.sliNavDiv a.sliRgt:visited, .sliNavDiv a.sliRgt:hover {
    float: right;
    text-align: right;
}
.sliNavDiv a.sliLft i, 
.sliNavDiv a.sliLft:link i, .sliNavDiv a.sliLft:active i, 
.sliNavDiv a.sliLft:visited i, .sliNavDiv a.sliLft:hover i, 
.sliNavDiv a.sliRgt i, 
.sliNavDiv a.sliRgt:link i, .sliNavDiv a.sliRgt:active i, 
.sliNavDiv a.sliRgt:visited i, .sliNavDiv a.sliRgt:hover i {
    margin: 10px;
}
.sliNavDiv a.sliLft div, 
.sliNavDiv a.sliLft:link div, .sliNavDiv a.sliLft:active div, 
.sliNavDiv a.sliLft:visited div, .sliNavDiv a.sliLft:hover div, 
.sliNavDiv a.sliRgt div, 
.sliNavDiv a.sliRgt:link div, .sliNavDiv a.sliRgt:active div, 
.sliNavDiv a.sliRgt:visited div, .sliNavDiv a.sliRgt:hover div {
    display: block;
    width: 100%;
    height: 1px;
    margin: 0px;
    background: {!! $css["color-main-bg"] !!};
    opacity:0.01; filter:alpha(opacity=1);
}
.sliNavDiv a.sliLft:hover div, .sliNavDiv a.sliRgt:hover div {
    opacity:0.33; filter:alpha(opacity=33);
    box-shadow: 0px 0px 40px {!! $css["color-main-bg"] !!};
}

.prevBox {
    width: 100%;
    height: 200px;
	overflow:auto;
	padding: 0px;
}

.clickBox, tr.clickBox, table tr.clickBox {
    cursor: pointer;
	background: {!! $css["color-main-bg"] !!};
}

#gMap {
    height: 100%;
    min-height: 600px;
}

.embedMapA, .embedMapDescA {
    width: 100%;
    height: 420px;
}
.embedMapDescA {
    overflow-y: scroll;
    overflow-x: hidden;
}
@media screen and (max-width: 992px) {
    .embedMapA {
        margin-bottom: 20px;
    }
    .embedMapA, .embedMapDescA {
        height: 340px;
    }
}
@media screen and (max-width: 768px) {
    .embedMapA, .embedMapDescA {
        height: 260px;
    }
}

.slAccord {
    min-height: 36px;
}
.slAccord div fL, .slAccord div fR {
    height: 36px;
    padding-top: 5px;
}
.slAccord div .fR {
    font-size: 16px;
}
.slAccord div .fR .fa-caret-down, .slAccord div .fR .fa-caret-up {
    color: {!! $css["color-main-text"] !!};
}
.slAccordBig {
    min-height: 56px;
}
.slAccordBig div fL, .slAccordBig div fR {
    height: 56px;
    padding-top: 15px;
}
.slAccordBig div .fR {
    font-size: 22px;
}
.slAccordTxt {
    min-height: 32px;
    margin-bottom: -20px;
}
.slAccordTxt div fL, .slAccordTxt div fR {
    height: 32px;
    padding-top: 10px;
}

a.accordHeadBtn:link, a.accordHeadBtn:visited, 
a.accordHeadBtn:active, a.accordHeadBtn:hover {
    background: {!! $css["color-main-bg"] !!};
}
a.accordHeadBtn:hover {
    background: {!! $css["color-main-faint"] !!};
}
.slAccord .accordHeadWrap, .slAccord div a div .accordHeadWrap,
.slAccordTxt .accordHeadWrap, .slAccordTxt div a div .accordHeadWrap {
    min-height: 20px;
}
.slAccordBig .accordHeadWrap, .slAccordBig div a div .accordHeadWrap {
    min-height: 35px;
}

@for ($i = 0; $i < 20; $i++) @if ($i > 0) , @endif #fixHead{{ $i }} @endfor {
    width: 100%;
}
@for ($i = 0; $i < 20; $i++) @if ($i > 0) , @endif #fixHeadFixed{{ $i }} @endfor {
    width: 100%;
}

