/* generated from resources/views/vendor/survloop/styles-css-2.blade.php */

html, body {
    font-size: 16px;
}
body, p, .slTxt {
    color: {!! $css["color-main-text"] !!};
}
.note-editable p {
    color: #000;
}

#absDebug {
    position: absolute;
    top: 70px;
    left: 5px;
    width: 200px;
    font-size: 10pt;
    position: fixed;
}
#hidivBtnDbgPop, #hidivBtnDbgPop2 {
	color: {!! $css["color-main-faint"] !!};
}
#hidivBtnDbgPop {
    margin-right: 20px;
}
#hidivDbgPop, #hidivDbgPop2 {
    border: 1px {!! $css["color-main-on"] !!} solid;
    padding: 20px;
    margin: 0px 0px 10px 0px;
    -moz-border-radius: 20px; border-radius: 20px;
    width: 100%;
    display: none;
    text-align: left;
}
.hidivBtnDbgW {
    position: absolute;
    right: -20px;
    top: 20px;
    text-align: right;
}
.hidivBtnDbgN, .hidivBtnDbgW .hidivBtnDbgN, 
a.hidivBtnDbgN:link, a.hidivBtnDbgN:visited, a.hidivBtnDbgN:active, a.hidivBtnDbgN:hover {
	color: {!! $css["color-main-faint"] !!};
    margin-right: 20px;
    opacity:0.5; filter:alpha(opacity=50);
}
.hidivDbgN, a.hidivDbgN:link, a.hidivDbgN:active, a.hidivDbgN:visited, a.hidivDbgN:hover {
    position: absolute;
    right: 2px;
    top: 40px;
    display: none;
    border: 1px {!! $css["color-main-grey"] !!} dashed;
    background: {!! $css["color-main-faint"] !!};
    opacity:0.95; filter:alpha(opacity=95);
    -moz-border-radius: 20px; border-radius: 20px;
    text-align: left;
    padding: 5px 10px;
}

#mainNav {
	background: {!! $css["color-nav-bg"] !!};
    /* border-bottom: 1px {!! $css["color-line-hr"] !!} solid; */
}
#mainNav, #mainNav .col-4, #mainNav .col-8, .navbar, #myNavBar, #myNavBar .navbar {
    height: 56px;
	min-height: 56px;
	max-height: 56px;
	padding-top: 1px;
	overflow: hidden;
	color: {!! $css["color-nav-text"] !!};
}
.navbar, #myNavBar, #myNavBar .navbar {
    text-align: right;
}
#headClear {
	background: {!! $css["color-nav-bg"] !!};
	margin-left: -1px;
}
#navBurger, a#navBurger:link, a#navBurger:active, a#navBurger:visited, a#navBurger:hover,
#navBurgerClose, a#navBurgerClose:link, a#navBurgerClose:active, a#navBurgerClose:visited, a#navBurgerClose:hover {
    width: 55px;
    height: 39px;
    -moz-border-radius: 4px; border-radius: 4px;
    font-size: 24px;
	padding: 3px;
    margin: 6px 15px 0 0;
    text-align: center;
	color: {!! $css["color-nav-text"] !!};
	border: 1px {!! $css["color-main-grey"] !!} solid;
}
#navBurger .fa.fa-bars, a#navBurger:link .fa.fa-bars, a#navBurger:active .fa.fa-bars, a#navBurger:visited .fa.fa-bars, a#navBurger:hover .fa.fa-bars {
    margin-top: 3px;
    display: block;
}
#navBurgerClose .fa.fa-times, a#navBurgerClose:link .fa.fa-times, a#navBurgerClose:active .fa.fa-times, a#navBurgerClose:visited .fa.fa-times, a#navBurgerClose:hover .fa.fa-times {
    margin-top: 2px;
    display: block;
}
a#navBurger:hover, a#navBurgerClose:hover {
    text-decoration: none;
}
/*
@-moz-document url-prefix() {
    #navBurger, a#navBurger:link, a#navBurger:active, a#navBurger:visited, a#navBurger:hover {
        padding-top: 3px;
    }
    #navBurgerClose, a#navBurgerClose:link, a#navBurgerClose:active, a#navBurgerClose:visited, a#navBurgerClose:hover {
        padding-top: 2px;
    }
}
*/

#mySidenav {
    height: 100%;
    width: 0;
    position: fixed;
    z-index: 1;
    top: 0;
    right: 0;
    border-left: 0px none;
    overflow-x: hidden;
    transition: 0.5s;
	color: {!! $css["color-nav-text"] !!};
    background: {!! $css["color-main-faint"] !!};
    border-left: 0px none;
    box-shadow: none;
}
#mySidenav a {
    padding: 10px 20px;
    text-decoration: none;
    font-size: 18px;
    color: {!! $css["color-main-link"] !!};
    display: block;
    transition: 0.3s
}
#mySidenav a:hover {
	color: {!! $css["color-nav-text"] !!};
	background: {!! $css["color-nav-bg"] !!};
}
#main {
    transition: margin-right .5s;
    width: 100%;
    height: 100%;
}
@media screen and (max-height: 450px) {
    #mySidenav a {font-size: 18px;}
} 

a.slNavLnk, a.slNavLnk:link, a.slNavLnk:active, a.slNavLnk:visited, a.slNavLnk:hover, 
.slNavRight a, .slNavRight a.slNavLnk:link, .slNavRight a.slNavLnk:active, .slNavRight a.slNavLnk:visited, .slNavRight a.slNavLnk:hover {
    display: block;
    padding: 15px 15px;
    margin-right: 10px;
	color: {!! $css["color-nav-text"] !!};
}

.headGap {
    display: block;
    width: 100%;
    height: 56px;
	margin-bottom: 0px;
}
.headGap img {
    height: 56px;
    width: 1px;
}
#headBar {
    width: 100%;
    display: none;
	background: {!! $css["color-main-faint"] !!};
}
#progWrap {
    display: block;
	background: {!! $css["color-main-faint"] !!};
}
#progWrap .progress {
    height: 6px;
    -moz-border-radius: 0px; border-radius: 0px;
}
#slNavMain {
    margin-top: -4px;
}
#slNavMain .card-body, #slNavMain div .card .card-body {
    padding: 5px 0px 0px 0px;
}
#slNavMain .card-body, #slNavMain div .card .card-body .list-group {
    margin: 0px;
}
.list-group-item.completed, .list-group-item.completed:hover, .list-group-item.completed:focus {
    z-index: 2;
    color: {!! $css["color-main-text"] !!};
    background-color: {!! $css["color-main-faint"] !!};
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
}
#dialog .card .card-body {
    text-align: left;
}
#nondialog {
    display: block;
    width: 100%;
    height: 100%;
}
#menuColpsWrap {
    margin: 15px -15px 0px 0px;
}
a#menuColpsBtn:link, a#menuColpsBtn:visited, a#menuColpsBtn:active, a#menuColpsBtn:hover,
a#menuUnColpsBtn:link, a#menuUnColpsBtn:visited, a#menuUnColpsBtn:active, a#menuUnColpsBtn:hover {
    display: block;
    width: 100%;
    padding: 10px 15px;
    color: {!! $css["color-main-link"] !!};
}
a#menuUnColpsBtn:link, a#menuUnColpsBtn:visited, a#menuUnColpsBtn:active, a#menuUnColpsBtn:hover {
    display: none;
}

#leftSide {
    height: 100%;
    vertical-align: top;
    color: {!! $css["color-main-faint"] !!};
    background: {!! $css["color-main-grey"] !!};
}
#leftSideWdth {
    width: 200px;
}
#leftSideWrap {
    position: fixed;
    width: 200px;
	z-index: 0;
}
#leftAdmMenu {
    display: block;
    width: 100%;
}

#mainBody {
    padding: 15px 0px;
    vertical-align: top;
    background: {!! $css["color-main-bg"] !!};
	z-index: 100;
}
#mainBody.mainBodyDash {
    background: {!! $css["color-main-faint"] !!};
    padding: 0px;
}
body.bodyDash {
    background: {!! $css["color-main-grey"] !!};
}

#dashSearchFrmWrap {
    position: relative;
    width: 100%;
    padding: 8px 15px 7px 15px;
}
#admSrchFld {
    background: none; 
    background-color: none;
    z-index: 1;
}
#admSrchFld, #admSrchFld a:link, #admSrchFld a:visited, #admSrchFld a:active, #admSrchFld a:hover {
    color: {!! $css["color-main-bg"] !!};
}
#admSrchFld::placeholder, #admSrchFld:-ms-input-placeholder, #admSrchFld::-ms-input-placeholder {
    color: {!! $css["color-main-bg"] !!};
}
#dashSearchBtnID {
    position: absolute;
    z-index: 99;
    top: 10px;
    right: 24px;
}
#dashSearchBtnID a:link, #dashSearchBtnID a:active, #dashSearchBtnID a:visited, #dashSearchBtnID a:hover {
    color: {!! $css["color-main-bg"] !!};
    font-size: 14px;
}
#dashSearchBtnID a:hover {
    font-size: 16px;
}

#footerLinks {
    display: block;
    max-width: 730px;
    margin: 40px 15px 20px 15px;
}
#footerLinks .footerSocial a img, 
a.socialIco:link, a.socialIco:visited, a.socialIco:active, a.socialIco:hover, 
a.socialIco:link img, a.socialIco:visited img, a.socialIco:active img, a.socialIco:hover img {
    height: 40px;
    margin: 5px;
	-moz-border-radius: 5px; border-radius: 5px;
}
#footerLinks p, #footerLinks div div p, #footerLinks p h3, #footerLinks div div p h3, #footerLinks div div h3 {
    margin: 0px;
}

a.socialTwit:link, a.socialTwit:visited, a.socialTwit:active, a.socialTwit:hover,
a.socialFace:link, a.socialFace:visited, a.socialFace:active, a.socialFace:hover {
	position: relative;
    width: 61px;
    height: 20px;
    font-size: 8pt;
	-moz-border-radius: 3px; border-radius: 3px;
    color: #FFF;
    background: #1c95e0;
}
a.socialTwit:hover {
    background: #0c7abf;
}
a.socialFace:link, a.socialFace:visited, a.socialFace:active, a.socialFace:hover {
    background: #4266b2;
}
a.socialFace:hover {
    background: #365799;
}
a.socialTwit:link i, a.socialTwit:visited i, a.socialTwit:active i, a.socialTwit:hover i,
a.socialFace:link i, a.socialFace:visited i, a.socialFace:active i, a.socialFace:hover i {
	position: absolute;
	top: 3px;
	left: 8px;
    font-size: 11pt;
}
a.socialFace:link i, a.socialFace:visited i, a.socialFace:active i, a.socialFace:hover i {
	left: 7px;
}
a.socialTwit:link span, a.socialTwit:visited span, a.socialTwit:active span, a.socialTwit:hover span,
a.socialFace:link span, a.socialFace:visited span, a.socialFace:active span, a.socialFace:hover span {
	position: absolute;
	font-weight: 700;
	top: 3px;
	left: 26px;
}
a.socialTwit:link img, a.socialTwit:visited img, a.socialTwit:active img, a.socialTwit:hover img,
a.socialFace:link img, a.socialFace:visited img, a.socialFace:active img, a.socialFace:hover img,
a.socialTwit:link div, a.socialTwit:visited div, a.socialTwit:active div, a.socialTwit:hover div,
a.socialFace:link div, a.socialFace:visited div, a.socialFace:active div, a.socialFace:hover div {
    width: 61px;
    height: 20px;
    opacity:0.0; filter:alpha(opacity=0);
}

#slLogoWrap {
    display: block;
}
#slLogo {
    display: block;
    margin: 7px 0px 0px 16px;
}
#slLogoImg, #slLogoImgSm {
    display: inline;
    height: 40px;
    margin-top: 0px;
}
#slLogoImgSm {
    display: none;
}
#logoPrint #slLogoImg {
    height: 50px;
    margin-top: 10px;
}
#slLogo.w100 {
    width: 100%;
    margin-top: 10px;
}
#slLogoImg.w100 {
    height: auto;
    width: 100%;
}
.slPrint #slLogo, .slPrint #slLogo.w100, .slPrint #slLogoImg, .slPrint #slLogoImg.w100 {
    height: 130px;
    width: auto;
}


.navbar-brand, a.navbar-brand:link, a.navbar-brand:visited, a.navbar-brand:active, a.navbar-brand:hover {
	font-size: 32pt;
}
#logoTxt {
	padding-left: 10px;
	margin-top: -2px;
}
#headLogoLong img {
    height: 50px;
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


.pageTopGap {
	width: 100%;
	padding-top: 20px;
	clear: both;
}
.pageBotGap {
	width: 100%;
	padding-top: 30px;
	clear: both;
}
.nodeGap {
	width: 100%;
	padding: 20px;
	clear: both;
}
.nodeHalfGap {
	width: 100%;
	height: 20px;
	clear: both;
}
.nodeAnchor {
    width: 1px;
    height: 1px;
    margin-bottom: -1px;
    position: relative;
}
.nodeAnchor a {
    position: absolute;
    top: -50px;
}
.nodeWrap, .nodeWrapError {
	background: none;
	display: block;
	padding: 0px;
	-moz-border-radius: 8px; border-radius: 8px;
	overflow: visible;
	box-shadow: 0px 0px 0px none;
}
.nodeWrapError {
	padding: 10px 5px 10px 5px;
	border: 3px {!! $css["color-danger-on"] !!} solid;
	box-shadow: 0px 0px 20px {!! $css["color-main-grey"] !!};
}
.nodeWrap.nGraph {
    padding: 10px;
    border: 2px {!! $css["color-main-faint"] !!} dashed;
}
.nPrompt, .nFld, .nFldFing {
	display: block;
	font-size: 16px;
	color: {!! $css["color-main-text"] !!};
}
.nPrompt h1, .nPrompt h2, .nPrompt h3, .nFld h1, .nFld h2, .nFld h3, .nFld h4, .nFld h5, .nFld h6 {
    padding: 0px;
    margin: 0px;
}
.nPrompt h1.slBlueDark, .nPrompt h2.slBlueDark, .nPrompt h3.slBlueDark {
	color: {!! $css["color-main-on"] !!};
}
.nPrompt p, .nPrompt ul {
	font-size: 16px;
    margin-top: 15px;
}
.nPrompt.col-6 {
    padding-top: 15px;
}
.nFld {
    margin-top: 20px;
}
/* .nFld, .nFld input, .nFld select, .nFld textarea {
	font-size: 16px;
} */
.subRes {
    padding-left: 20px;
    margin-bottom: 20px;
}
.subRes .nodeWrap {
    margin-top: -15px;
}
.subRes .nFld, .subRes .nodeWrap .nFld {
    margin-top: 5px;
    margin-bottom: 10px;
}

.slideCol {
    padding: 19px 0px 0px 25px;
}
.slidePercFld {
    display: inline;
    width: 55px; 
    padding: 6px 6px;
    text-align: right;
}
.unitFld {
    display: inline;
    width: 100px; 
    padding: 6px 6px;
}

/*
.nPrompt ul, .nPrompt ol {
    padding: 0px 0px 0px 20px;
    margin: 20px 0px 0px 20px;
}  */
ul li, ol li, .nPrompt ul li, .nPrompt ol li {
    margin: 0px 0px 10px 0px;
}

.nPromptHeader {
	color: {!! $css["color-main-on"] !!};
}
label, .nPrompt label {
    margin: 0px;
}
.nodeWrap .jumbotron, .nPrompt .jumbotron {
    padding: 30px 40px 30px 40px;
}
.nPrompt .jumbotron p, .nPrompt .jumbotron h1, .nPrompt .jumbotron h2, .nPrompt .jumbotron h3 {
    padding: 0px 0px 20px 0px;
}
.nFld input, .nFld select, .nFld textarea, .nFld label, .nFld .radio label, .nFld .checkbox label {
	color: {!! $css["color-form-text"] !!};
}
.nFld input, .nFld select, .nFld textarea {
	color: {!! $css["color-form-text"] !!};
	background: {!! $css["color-field-bg"] !!};
	border: 1px {!! $css["color-main-on"] !!} solid;
}

.nFld input.dateFld {
	width: 140px;
}
.nFld select.form-control-lg {
    padding: .2rem 0.7rem;
}
.nFld select.timeDrop, .nFld select.form-control.timeDrop {
	width: 95px;
}
.nFld select.tinyDrop, .nFld select.form-control.tinyDrop {
	width: 60px;
}
.timeWrap input {
	width: 60px;
}

.flexarea, textarea.flexarea, textarea.form-control.flexarea {
    min-height: 60px;
    overflow-y: auto;
    word-wrap:break-word;
}

.rqd, h1.rqd, h2.rqd, h3.rqd, label .rqd {
    color: {!! $css["color-danger-on"] !!}; 
    font-weight: 100; 
}
.rqd, h1 .rqd, h2 .rqd, h3 .rqd, h4 .rqd {
    font-size: 16px;
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
    border-bottom: 1px solid {!! $css["color-main-off"] !!};
}
.prevImg.brd {
    border: 1px solid {!! $css["color-main-off"] !!};
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

.ui-widget-header {
    border: 1px solid {!! $css["color-main-grey"] !!};
    background: {!! $css["color-main-text"] !!};
    color: {!! $css["color-main-bg"] !!};
}
.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {
    border: 1px solid {!! $css["color-main-grey"] !!};
    background: {!! $css["color-main-bg"] !!};
    color: {!! $css["color-main-on"] !!};
}
.ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active {
    border: 1px solid {!! $css["color-main-bg"] !!};
    background: {!! $css["color-main-on"] !!};
    color: {!! $css["color-main-bg"] !!};
}
.ui-state-highlight, .ui-widget-content .ui-state-highlight, .ui-widget-header .ui-state-highlight {
    border: 1px solid {!! $css["color-main-text"] !!};
    background: {!! $css["color-main-faint"] !!};
    color: {!! $css["color-main-text"] !!};
}
.ui-datepicker-prev.ui-corner-all.ui-state-hover.ui-datepicker-prev-hover {
    border: 1px solid {!! $css["color-main-text"] !!};
    background: {!! $css["color-main-faint"] !!};
    color: {!! $css["color-main-text"] !!};
}
/* .ui-datepicker-prev.ui-corner-all.ui-state-hover.ui-datepicker-prev-hover .ui-icon.ui-icon-circle-triangle-w {
    background: {!! $css["color-main-on"] !!};
} */


input.nFormBtnSub, input.nFormBtnBack {
    font-size: 26pt;
}

input.otherFld, input.form-control.otherFld, label input.otherFld, label input.form-control.otherFld {
    width: 400px;
}
.form-control.pT0, .form-control.form-control-lg.pT0 {
    padding-top: 0px;
}
.nodeSub, .nodeWrap.w100 label, .nodeWrap.w100 .nPrompt, .nodeWrap.w100 .nPrompt label {
    width: 100%;
}

label.finger, label.fingerAct {
    cursor: pointer;
    width: 100%;
    border: 1px {!! $css["color-main-faint"] !!} solid;
    -moz-border-radius: 20px; border-radius: 20px;
    padding: 10px 20px;
    margin: 3px 0px 3px 0px;
    background: {!! $css["color-main-bg"] !!};
}
.finger:hover, input.finger:hover+label {
    border: 1px {!! $css["color-main-off"] !!} solid;
    background: {!! $css["color-main-faint"] !!};
}
label.fingerAct, label.fingerAct:active, input.fingerAct:active+label, 
label.fingerAct:hover, input.fingerAct:hover+label, label.finger:active, input.finger:active+label {
    border: 1px {!! $css["color-main-off"] !!} solid;
    background: {!! $css["color-main-faint"] !!};
    color: {!! $css["color-main-on"] !!};
}
label.finger input {
    margin-top: 0px;
    margin-bottom: 0px;
}
label.finger i.float-right, label.fingerAct i.float-right {
    margin-top: 3px;
}

input.fingerTxt, input.form-control.fingerTxt, .nFld input.form-control.fingerTxt, 
textarea.fingerTxt, textarea.form-control.fingerTxt, .nFld textarea.form-control.fingerTxt {
    cursor: pointer;
    width: 100%;
    border: 1px {!! $css["color-main-off"] !!} solid;
    -moz-border-radius: 5px; border-radius: 5px;
}
select.fingerTxt, select.form-control.fingerTxt, .nFld select.form-control.fingerTxt {
    border: 1px {!! $css["color-main-off"] !!} solid;
    -moz-border-radius: 5px; border-radius: 5px;
}

.slTagList {
    margin-top: 5px;
    line-height: 43px;
}
.slTagList a.btn i {
    padding-left: 10px;
    margin-left: 10px;
    border-left: 1px {!! $css["color-main-faint"] !!} solid;
}


#pageBtns {
    margin-top: 15px;
    width: 100%;
}
#formErrorMsg {
    display: none;
    width: 100%;
    text-align: center;
    background: {!! $css["color-danger-on"] !!};
    padding: 10px 20px;
    margin: -30px 0px 20px 0px;
	-moz-border-radius: 5px; border-radius: 5px;
}
#formErrorMsg, #formErrorMsg h1, #formErrorMsg h2, #formErrorMsg h3 {
    color: {!! $css["color-main-bg"] !!};
}
#formErrorMsg h1, #formErrorMsg h2, #formErrorMsg h3 {
    margin: 0px;
    padding: 0px;
}

a.nFldBtn, a.nFldBtn:link, a.nFldBtn:active, a.nFldBtn:visited, a.nFldBtn:hover,
.nPrompt a.nFldBtn, .nPrompt a.nFldBtn:link, .nPrompt a.nFldBtn:active, .nPrompt a.nFldBtn:visited, .nPrompt a.nFldBtn:hover {
    width: 100%;
    font-size: 125%;
    white-space: normal;
}

.nFormNext, a.nFldBtn, a.nFldBtn:link, a.nFldBtn:active, a.nFldBtn:visited, a.nFldBtn:hover, {
    white-space: normal;
}
.btn.btn-xl, .btn.btn-lg, .btn.btn-md {
    white-space: normal;
}
.btn.btn-ico, a.btn.btn-ico:link, a.btn.btn-ico:visited, a.btn.btn-ico:active, a.btn.btn-ico:hover {
    font-size: 20px;
    padding: 1px 9px;
}

.subNote, .nPrompt .subNote {
    margin-top: 10px;
}
.subNote, .nPrompt .subNote, .nPrompt .subNote p, .nWrap .nPrompt .subNote p, .finger .subNote, .fingerAct .subNote {
	font-size: 14px;
}
.finger .subNote, .fingerAct .subNote {
    margin-top: 0px;
    padding-left: 20px;
}
label.finger .subNote, .nFld label.finger .subNote, label.fingerAct .subNote, .nFld label.fingerAct .subNote {
	font-size: 14px;
	color: {!! $css["color-main-text"] !!};
}


.slSlider {
	width: 100%; 
	position: relative; 
}
.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {
    border: 1px solid {!! $css["color-main-faint"] !!};
    background: {!! $css["color-main-on"] !!};
    color: {!! $css["color-main-faint"] !!};
}
.ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active {
    border: 1px solid {!! $css["color-main-on"] !!};
    background: {!! $css["color-main-faint"] !!};
    color: {!! $css["color-main-on"] !!};
}

.slSortable { 
	list-style-type: none; 
	margin: 0; padding: 0; 
	text-align: left; 
	width: 100%; 
	cursor: move; 
}
.slSortable li, .slSortable li.sortOn, .slSortable li.sortOff { 
	border-top: 1px {!! $css["color-main-faint"] !!} solid; 
	border-bottom: 1px {!! $css["color-main-off"] !!} solid; 
	background: {!! $css["color-main-faint"] !!}; 
	color: {!! $css["color-main-on"] !!}; 
	font-size: 20pt; 
	padding: 12px;
	margin: 1px 0px;
	width: 100%; 
	text-align: left; 
}
.slSortable li.sortOn { 
	background: {!! $css["color-main-faint"] !!}; 
}
.slSortable li i, .slSortable li.sortOn i, .slSortable li.sortOff i { 
    margin: 0px 20px;
}
@media screen and (max-width: 768px) {
    .slSortable li, .slSortable li.sortOn, .slSortable li.sortOff {
        font-size: 14pt;
        padding: 10px 5px;
    }
    .slSortable li i, .slSortable li.sortOn i, .slSortable li.sortOff i { 
        margin: 0px 10px;
    }
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


a.facebookShareBig:link, a.facebookShareBig:visited, a.facebookShareBig:active, a.facebookShareBig:hover {
	font-size: 26pt;
	font-weight: 500;
	padding: 10px;
	margin: 10px;
	background: {!! $css["color-main-on"] !!};
	color: {!! $css["color-main-bg"] !!};
	border: 0px none;
	box-shadow: 1px 1px 6px {!! $css["color-main-off"] !!};
	-moz-border-radius: 5px; border-radius: 5px;
	cursor: pointer;
}
a.facebookShareBig:link, a.facebookShareBig:visited, a.facebookShareBig:active, a.facebookShareBig:hover {
	float: none;
	display: block;
	padding: 15px 30px;
	margin-top: 40px;
	text-decoration: none;
}


.wrapLoopItem {
}
.wrapItemOff {
	display: none;
	background: {!! $css["color-main-faint"] !!};
	color: {!! $css["color-main-grey"] !!};
	padding: 15px;
	-moz-border-radius: 5px; border-radius: 5px;
}
.loopItemBtn {
    width: 90px;
    margin: 5px 10px 0px 0px;
}

#reportTakeActions {
	margin: -20px 0px 15px 0px;
	border: 1px {!! $css["color-main-off"] !!} solid;
	background: {!! $css["color-main-faint"] !!};
	padding: 25px 15px 5px 15px;
	-moz-border-radius: 20px; border-radius: 20px;
}
.slCard {
    width: 100%;
    padding: 20px;
    -moz-border-radius: 6px; border-radius: 6px;
    background: {!! $css["color-main-bg"] !!};
    box-shadow: 0px 1px 4px {!! $css["color-main-grey"] !!};
}
table.repDeetsBlock, .slReport table.repDeetsBlock, table.repDeetsBlock tbody, .slReport table.repDeetsBlock tbody {
	width: 100%;
}
.slCard.nodeWrap {
    margin-bottom: 30px;
}
table.repDeetsBlock tr td, table.repDeetsBlock tr th {
    word-wrap: break-word;
    line-break: loose;
    padding: 10px 15px;
    vertical-align: top;
    width: 50%;
}
table.repDeetsBlock tr td span, table.repDeetsBlock tr td div span, 
.slReport table.repDeetsBlock tr td span, .slReport table.repDeetsBlock tr td div span, 
.slReport table.repDeetsBlock tr td span a.hidivBtn:link, .slReport table.repDeetsBlock tr td span a.hidivBtn:visited, 
.slReport table.repDeetsBlock tr td span a.hidivBtn:active, .slReport table.repDeetsBlock tr td span a.hidivBtn:hover {
	color: {!! $css["color-main-grey"] !!};
}

#gMap {
    height: 100%;
    min-height: 600px;
}

.dontBreakOut, table.repDeetsBlock tr td, table.repDeetsBlock tr th {
    overflow-wrap: break-word;
    word-wrap: break-word;
    /*
    -ms-word-break: break-all;
    word-break: break-all;
    word-break: break-word;
    -ms-hyphens: auto;
    -moz-hyphens: auto;
    -webkit-hyphens: auto;
    hyphens: auto;
    */
}

table.repDeetVert {
    width: 100%;
}
table.repDeetVert, table.repDeetVert tr td, table.repDeetVert tr th {
    border: 0px;
    padding: 3px 0px;
}
.vertPrgDone, table.repDeetVert tr td .vertPrgDone,
.vertPrgFutr, table.repDeetVert tr td .vertPrgFutr {
    margin: 6px 5px 9px 1px;
	background: {!! $css["color-main-on"] !!};
}
.vertPrgCurr, table.repDeetVert tr td .vertPrgCurr {
    margin: 5px 4px 8px 0px;
	border: 3px {!! $css["color-main-on"] !!} solid;
}
.vertPrgFutr, table.repDeetVert tr td .vertPrgFutr {
    opacity:0.20; filter:alpha(opacity=20);
}
.vertPrgDone, table.repDeetVert tr td .vertPrgDone,
.vertPrgDone img, table.repDeetVert tr td .vertPrgDone img,
.vertPrgFutr, table.repDeetVert tr td .vertPrgFutr,
.vertPrgFutr img, table.repDeetVert tr td .vertPrgFutr img {
    width: 8px;
    height: 8px;
	-moz-border-radius: 4px; border-radius: 4px;
}
.vertPrgCurr, table.repDeetVert tr td .vertPrgCurr, 
.vertPrgCurr img, table.repDeetVert tr td .vertPrgCurr img {
    width: 10px;
    height: 10px;
	-moz-border-radius: 5px; border-radius: 5px;
}

.reportPreview {
    margin: 10px 0px;
    padding: 10px 0px;
    border-bottom: 1px {!! $css["color-main-faint"] !!} solid;
}

.slWebReport p {
    font-size: 125%;
    margin: 30px 0px;
}



.search-bar {
    width: 100%;
    position: relative;
}
.search-bar input {
    width: 100%
}
.search-bar .search-btn-wrap {
    position: absolute;
    top: 0px;
    right: 0px;
    height: 54px;
    width: 52px;
    overflow: hidden;
}
.search-bar .search-btn-wrap a .fa-search {
    font-size: 15pt;
    margin: 5px 5px;
}
.btn.btn-info.searchBarBtn {
    height: 46px;
    width: 54px;
    margin-left: -2px;
    margin-right: -2px;
}

.btn.btn-success, a.btn.btn-success:link, a.btn.btn-success:visited, a.btn.btn-success:active, a.btn.btn-success:hover,
.btn.btn-warning, a.btn.btn-warning:link, a.btn.btn-warning:visited, a.btn.btn-warning:active, a.btn.btn-warning:hover {
	color: {!! $css["color-main-bg"] !!};
}



.basicTier0, .basicTier1, .basicTier2, .basicTier3, .basicTier4, 
.basicTier5, .basicTier6, .basicTier7, .basicTier8, .basicTier9 {
    min-width: 80px;
    max-width: 1130px;
    padding: 10px 5px 10px 5px;
    margin: 0px 5px 0px 5px;
	-moz-border-radius: 20px; border-radius: 20px;
	border: 1px {!! $css["color-main-faint"] !!} solid;
	border-top: 1px {!! $css["color-main-faint"] !!} solid;
}
.basicTier0 {
    margin: 0px;
}
.basicTier1, .basicTier2, .basicTier3 { border-left: 3px {!! $css["color-main-off"] !!} dotted; }
.basicTier4, .basicTier5 { border-left: 2px {!! $css["color-main-off"] !!} dotted; }
.basicTier6, .basicTier7 { border-left: 2px {!! $css["color-main-faint"] !!} dotted; }
.basicTier8, .basicTier9 { border-left: 1px {!! $css["color-main-faint"] !!} dotted; }
.basicTier0, .basicTier0.basicTierBranch {
	border: 4px {!! $css["color-success-on"] !!} dotted;
}
.basicTierBranch, .basicTier1.basicTierBranch, .basicTier2.basicTierBranch, .basicTier3.basicTierBranch, .basicTier4.basicTierBranch, .basicTier5.basicTierBranch, .basicTier6.basicTierBranch, .basicTier7.basicTierBranch, .basicTier8.basicTierBranch {
	border: 1px {!! $css["color-main-grey"] !!} dashed;
}
.basicTierPage, .basicTier1.basicTierPage, .basicTier2.basicTierPage, .basicTier3.basicTierPage, .basicTier4.basicTierPage, .basicTier5.basicTierPage, .basicTier6.basicTierPage, .basicTier7.basicTierPage, .basicTier8.basicTierPage,
.basicTierLoop, .basicTier1.basicTierLoop, .basicTier2.basicTierLoop, .basicTier3.basicTierLoop, .basicTier4.basicTierLoop, .basicTier5.basicTierLoop, .basicTier6.basicTierLoop, .basicTier7.basicTierLoop, .basicTier8.basicTierLoop {
	border: 4px {!! $css["color-info-on"] !!} double;
	background: {!! $css["color-main-faintr"] !!};
	margin: 15px 5px 15px 5px;
    padding: 10px;
}
.basicTierLoop, .basicTier0.basicTierBranch, .basicTier0.basicTierBranch.basicTierData {
	border: 4px {!! $css["color-main-off"] !!} dotted;
}
.basicTierData, .basicTier1.basicTierData, .basicTier2.basicTierData, .basicTier3.basicTierData, .basicTier4.basicTierData, .basicTier5.basicTierData, .basicTier6.basicTierData, .basicTier7.basicTierData, .basicTier8.basicTierData {
	border-right: 2px {!! $css["color-success-on"] !!} dotted;
}
.basicTierLoop.basicTierData, .basicTier1.basicTierLoop.basicTierData, .basicTier2.basicTierLoop.basicTierData, .basicTier3.basicTierLoop.basicTierData, .basicTier4.basicTierLoop.basicTierData, .basicTier5.basicTierLoop.basicTierData {
	border-right: 2px {!! $css["color-success-on"] !!} dotted;
}
.basicTier1.slCard, .basicTier2.slCard, .basicTier3.slCard, .basicTier4.slCard, .basicTier5.slCard, .basicTier6.slCard, .basicTier7.slCard, .basicTier8.slCard {
    border: 0px none;
    padding: 10px 5px;
    margin-right: 5px;
    margin-bottom: 10px;
    -moz-border-radius: 6px; border-radius: 6px;
}
.basicTierDisabled {
    opacity:0.25; filter:alpha(opacity=25);
}
.dbColor {
    color: {!! $css["color-success-on"] !!};
}

.circleBtn, a.circleBtn:link, a.circleBtn:active, a.circleBtn:visited, a.circleBtn:hover,
.circleBtn0, a.circleBtn0:link, a.circleBtn0:active, a.circleBtn0:visited, a.circleBtn0:hover,
.circleBtn1, a.circleBtn1:link, a.circleBtn1:active, a.circleBtn1:visited, a.circleBtn1:hover,
.circleBtn2, a.circleBtn2:link, a.circleBtn2:active, a.circleBtn2:visited, a.circleBtn2:hover,
.circleBtn3, a.circleBtn3:link, a.circleBtn3:active, a.circleBtn3:visited, a.circleBtn3:hover {
    display: block;
	text-align: center;
	width: 20px;
	height: 20px;
	-moz-border-radius: 10px; border-radius: 10px;
	padding: 4px 1px 0 1px;
	font-size: 8px;
	letter-spacing: -0.03em;
	color: {!! $css["color-main-bg"] !!};
	background: {!! $css["color-main-on"] !!};
    opacity:0.25; filter:alpha(opacity=25);
}
a.circleBtn:hover, a.circleBtn0:hover, a.circleBtn1:hover, a.circleBtn2:hover, a.circleBtn3:hover {
	background: {!! $css["color-main-off"] !!};
    opacity:1.00; filter:alpha(opacity=100);
}
.circleBtn0, a.circleBtn0:link, a.circleBtn0:active, a.circleBtn0:visited, a.circleBtn0:hover {
	background: {!! $css["color-info-on"] !!};
}
a.circleBtn0:hover {
	background: {!! $css["color-info-off"] !!};
}
.circleBtn3, a.circleBtn3:link, a.circleBtn3:active, a.circleBtn3:visited, a.circleBtn3:hover {
	background: {!! $css["color-success-on"] !!};
}
a.circleBtn3:hover {
	background: {!! $css["color-success-off"] !!};
}

table.slSpreadTbl tr td.sprdFld {
    margin: 0px;
    padding: 0px;
}
table.slSpreadTbl tr th {
    padding: 0px 12px 6px 12px;
    vertical-align: bottom;
    border-top: 0px none;
    border-bottom: 1px {!! $css["color-main-off"] !!} solid;
}
table.slSpreadTbl tr td.sprdRowLab {
    padding: 12px 0px 0px 12px;
    color: {!! $css["color-main-off"] !!};
}
table.slSpreadTbl tr td.sprdRowLab, table.slSpreadTbl tr th.sprdRowLab {
    border-right: 1px {!! $css["color-main-off"] !!} solid;
}
table.slSpreadTbl tr td.sprdRowLab, table.slSpreadTbl tr th.sprdRowLab, table.slSpreadTbl tr th {
    font-size: 16px;
}
table.slSpreadTbl tr.rw2 td {
    background: {!! $css["color-main-faint"] !!};
}
table.slSpreadTbl tr th.cl1, table.slSpreadTbl tr td.sprdFld.cl1 {
    border-left: 1px {!! $css["color-main-text"] !!} solid;
}
table.slSpreadTbl tr th.cl2, table.slSpreadTbl tr td.sprdFld.cl2 {
    border-left: 1px {!! $css["color-main-faint"] !!} solid;
}
table.slSpreadTbl tr td.sprdFld input, table.slSpreadTbl tr td.sprdFld select {
    margin: 0px;
    background: none;
    border: 1px {!! $css["color-main-faint"] !!} solid;
    -moz-border-radius: 0px; border-radius: 0px;
}
table.slSpreadTbl tr td.sprdFld .nodeWrap div label {
    background: none;
    border: 0px none;
}
table.slSpreadTbl tr th, table.slSpreadTbl tr td {
    border-bottom: 1px {!! $css["color-main-grey"] !!} solid;
}
table.slSpreadTbl tr.sprdRowErr th, table.slSpreadTbl tr.sprdRowErr td {
    border-bottom: 1px {!! $css["color-danger-on"] !!} solid;
}
table.slSpreadTbl tr.sprdRowErr td.sprdRowLab {
    color: {!! $css["color-danger-on"] !!};
}

table.listTable {
	width: 100%;
}
table.listTable tr th {
	font-weight: bold;
}
table.listTable tr td, table.listTable tr th {
	padding: 10px 0px;
}

.clickBox, tr.clickBox, table tr.clickBox {
    cursor: pointer;
	background: {!! $css["color-main-bg"] !!};
}


table.detailList {
	width: 100%;
}
table.detailList tr td, table.detailList tr th {
	text-align: left;
	vertical-align: top;
	font-size: 10pt;
}
table.detailList tr th {
	font-size: 14pt;
	font-weight: bold;
}
table.detailList tr td {
	padding-bottom: 40px;
	border-top: 1px {!! $css["color-main-off"] !!} solid;
}

#treeWrap, .treeWrapForm {
    display: block;
	position: relative;
    text-align: left;
    margin: 0px;
}
.treeWrapForm {
    max-width: 730px;
    padding-right: 15px;
    padding-left: 15px;
}
#fixedHeader {
	position: relative;
	margin: 0px;
	width: 100%;
	min-width: 440px;
	background: none;
}
#fixedHeader h1, #fixedHeader h2 {
	margin: 0px;
}
.fixed, #fixedHeader.fixed {
	position: fixed;
	z-index: 99;
	background: {!! $css["color-main-bg"] !!};
	margin: 0px -15px 0px -15px;
	padding: 15px;
	top: 47px;
	border-bottom: 1px {!! $css["color-main-faint"] !!} solid;
}



/* Bootstrap modifications... */
.jumbotron {
	background: {!! $css["color-main-faint"] !!};
}

label { font-weight: normal; }


.stepNum {
	display: block;
	font-size: 12pt;                                      
	padding-top: 2px;
	margin-bottom: 5px;
	height: 30px;
	width: 30px;
	-moz-border-radius: 15px; border-radius: 15px;
}
.stepNum i {
    margin-top: 4px;
}
.navVertLine, .navVertLine2 {
    width: 1px;
    height: 15px;
    margin: 10px 0px 7px 0px;
    border-left: 1px {!! $css["color-main-grey"] !!} solid;
}
.navVertLine2 {
    height: 30px;
}
.stepNum, .navDeskMaj .stepNum, .navDeskMin .stepNum {
	color: {!! $css["color-main-grey"] !!};
	background: {!! $css["color-main-bg"] !!};
	border: 1px {!! $css["color-main-grey"] !!} solid;
}
.navDeskMaj.active .stepNum, .navDeskMin.active .stepNum,
.navDeskMaj.completed .stepNum, .navDeskMin.completed .stepNum {
	color: {!! $css["color-main-link"] !!};
	background: {!! $css["color-main-faint"] !!};
	border: 1px {!! $css["color-main-link"] !!} solid;
}
.navDeskMaj.active .navVertLine, .navDeskMaj.completed .navVertLine, 
.navDeskMaj.active .navVertLine2, .navDeskMaj.completed .navVertLine2
.navDeskMin.active .navVertLine, .navDeskMin.completed .navVertLine, 
.navDeskMin.active .navVertLine2, .navDeskMin.completed .navVertLine2 {
	border-left: 1px {!! $css["color-main-grey"] !!} solid;
}
.navDeskMaj.active .navVertLine, .navDeskMaj.active .navVertLine2,
.navDeskMaj.completed .navVertLine, .navDeskMaj.completed .navVertLine2 {
    border-left: 1px {!! $css["color-main-link"] !!} solid;
}
a.navDeskMaj:link, a.navDeskMaj:visited, a.navDeskMaj:active, a.navDeskMaj:hover,
a.navDeskMin:link, a.navDeskMin:visited, a.navDeskMin:active, a.navDeskMin:hover {
    cursor: not-allowed;
    color: {!! $css["color-main-grey"] !!};
}
a.navDeskMaj.active:link, a.navDeskMaj.active:visited, a.navDeskMaj.active:active, a.navDeskMaj.active:hover,
a.navDeskMaj.completed:link, a.navDeskMaj.completed:visited, a.navDeskMaj.completed:active, a.navDeskMaj.completed:hover,
a.navDeskMin.active:link, a.navDeskMin.active:visited, a.navDeskMin.active:active, a.navDeskMin.active:hover,
a.navDeskMin.completed:link, a.navDeskMin.completed:visited, a.navDeskMin.completed:active, a.navDeskMin.completed:hover {
    cursor: pointer;
    color: {!! $css["color-main-link"] !!};
}
.minorNavWrap {
    display: none;
    border-top: 1px {!! $css["color-main-off"] !!} solid;
    padding: 20px 0px;
    margin-top: -8px;
    -moz-border-radius: 20px; border-radius: 20px;
}

.card {
    background-color: {!! $css["color-main-faintr"] !!};
    background: {!! $css["color-main-faintr"] !!};
}
.card > .card-header {
    background-image: none;
    background-color: {!! $css["color-info-on"] !!};
}
.card > .card-header, .card > .card-header h1, .card > .card-header h2, .card > .card-header h3, 
.card > .card-header h4, .card > .card-header h5, .card > .card-header h6 {
    color: {!! $css["color-main-bg"] !!};
}

/* Pagination styling */ 
.pagination { display: inline-block; padding-right: 0; margin: 10px 0; border-radius: 4px; } 
.pagination>li { display: inline } 
.pagination>li>a, .pagination>li>span { position: relative; float: left; padding: 6px 12px; line-height: 1.428571429; text-decoration: none; color: #333333; background-color: {!! $css["color-main-bg"] !!}; border: 1px solid {!! $css["color-main-faint"] !!}; } 
.pagination>li:first-child>a, .pagination>li:first-child>span { margin-right: 0; border-bottom-right-radius: 4px; border-top-right-radius: 4px } 
.pagination>li:last-child>a, .pagination>li:last-child>span { border-bottom-left-radius: 4px; border-top-left-radius: 4px } 
.pagination>li>a:hover, .pagination>li>span:hover, .pagination>li>a:focus, .pagination>li>span:focus { color: {!! $css["color-main-bg"] !!}; background-color: {!! $css["color-main-on"] !!}; border-color: {!! $css["color-main-off"] !!} } 
.pagination>.active>a, .pagination>.active>span, .pagination>.active>a:hover, .pagination>.active>span:hover, .pagination>.active>a:focus, .pagination>.active>span:focus { z-index: 2; color: {!! $css["color-main-bg"] !!}; background-color: {!! $css["color-main-off"] !!}; border-color: {!! $css["color-main-on"] !!}; cursor: default } 
.pagination>.disabled>span, .pagination>.disabled>span:hover, .pagination>.disabled>span:focus, .pagination>.disabled>a, .pagination>.disabled>a:hover, .pagination>.disabled>a:focus { color: #777; background-color: {!! $css["color-main-bg"] !!}; border-color: {!! $css["color-main-faint"] !!}; cursor: not-allowed } 
.pagination-lg>li>a, .pagination-lg>li>span { padding: 10px 16px; font-size: 18px } 
.pagination-lg>li:first-child>a, .pagination-lg>li:first-child>span { border-bottom-right-radius: 6px; border-top-right-radius: 6px; } 
.pagination-lg>li:last-child>a, .pagination-lg>li:last-child>span { border-bottom-left-radius: 6px; border-top-left-radius: 6px; } 
.pagination-sm>li>a, .pagination-sm>li>span { padding: 5px 10px; font-size: 12px } 
.pagination-sm>li:first-child>a, .pagination-sm>li:first-child>span { border-bottom-right-radius: 3px; border-top-rightt-radius: 3px; } 
.pagination-sm>li:last-child>a, .pagination-sm>li:last-child>span { border-bottom-left-radius: 3px; border-top-left-radius: 3px; }

.btn-group-xl > .btn, .btn-xl {
    padding: 15px 20px;
    font-size: 30px;
    line-height: 1.3333333;
    border-radius: 6px;
}

#adminMenu {
    margin-top: 10px;
}
.admMenu a:link, .admMenu a:visited, .admMenu a:active, .admMenu a:link {
    display: block;
}
.admMenu .admMenuTier1 a:link, .admMenu .admMenuTier1 a:visited, .admMenu .admMenuTier1 a:active, .admMenu .admMenuTier1 a:hover {
    color: {!! $css["color-main-faint"] !!};
    background: {!! $css["color-main-grey"] !!};
    padding: 6px 5px 6px 9px;
    font-size: 120%;
}
.admMenu a.active:link, .admMenu a.active:visited, .admMenu a.active:active, .admMenu a.active:hover,
.admMenu div a.active:link, .admMenu div a.active:visited, .admMenu div a.active:active, .admMenu div a.active:hover {
    color: {!! $css["color-main-faint"] !!};
    background: {!! $css["color-main-link"] !!};
}
.admMenuIco, .admMenu .admMenuIco, .admMenu div a .admMenuIco, .admMenu div a:link .admMenuIco, .admMenu div a:visited .admMenuIco, .admMenu div a:active .admMenuIco, .admMenu div a:hover .admMenuIco {
    display: inline;
}
.admMenuIco.pull-left, .admMenu .admMenuIco.pull-left, .admMenu div a .admMenuIco.pull-left, 
.admMenu div a:link .admMenuIco.pull-left, .admMenu div a:visited .admMenuIco.pull-left, .admMenu div a:active .admMenuIco.pull-left, .admMenu div a:hover .admMenuIco.pull-left {
    display: block;
    width: 30px;
    text-align: center;
}
.admMenu .admMenuTier2 a:link, .admMenu .admMenuTier2 a:visited, .admMenu .admMenuTier2 a:active, .admMenu .admMenuTier2 a:hover {
    padding: 10px 5px 10px 23px;
    font-size: 100%;
}
.admMenu .admMenuTier2 a.active:link, .admMenu .admMenuTier2 a.active:visited, .admMenu .admMenuTier2 a.active:active, .admMenu .admMenuTier2 a.active:hover {
    color: {!! $css["color-main-link"] !!};
    background: {!! $css["color-main-faint"] !!};
}

#slTopTabsWrap {
    position: static;
    z-index: 100;
}
.slTopTabs {
    width: 100%;
    padding-top: 7px;
    margin-bottom: 25px;
    background: {!! $css["color-main-grey"] !!};
}
.slTopTabs ul.nav.nav-tabs {
    width: 100%;
    border: 0px none;
    border-bottom: 1px {!! $css["color-main-faint"] !!} solid;
}
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link:link,
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link:visited,
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link:active,
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link:hover {
    color: {!! $css["color-main-faint"] !!};
    background: {!! $css["color-main-off"] !!};
    border: 0px none;
    border-bottom: 1px {!! $css["color-main-faint"] !!} solid;
    margin-right: 7px;
}
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link.active:link,
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link.active:visited,
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link.active:active,
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link.active:hover {
    color: {!! $css["color-main-link"] !!};
    background: {!! $css["color-main-faint"] !!};
    border: 0px none;
}
.slTopTabsSub {
    margin-top: -20px;
}
.slTopTabsSub ul.nav {
    
}

a.navMobOff:link, a.navMobOff:active, a.navMobOff:visited, a.navMobOff:hover,
a.navMobActive:link, a.navMobActive:active, a.navMobActive:visited, a.navMobActive:hover,
a.navMobDone:link, a.navMobDone:active, a.navMobDone:visited, a.navMobDone:hover {
	display: block;
	font-size: 12pt;
	line-height: 10px;
	padding: 5px 5px 5px 10px;
	margin-right: 3px;
	margin-bottom: 3px;
	-moz-border-radius: 20px; border-radius: 20px;
	border: 1px {!! $css["color-main-on"] !!} solid;
}
a.navMobOff:link, a.navMobOff:active, a.navMobOff:visited, a.navMobOff:hover {
	color: {!! $css["color-main-off"] !!};
	border: 1px {!! $css["color-main-faint"] !!} solid;
	pointer-events: none;
	text-decoration: none;
}
a.navMobActive:link, a.navMobActive:active, a.navMobActive:visited, a.navMobActive:hover {
	color: {!! $css["color-main-bg"] !!};
	background: {!! $css["color-main-on"] !!};
}
#navMobTogInr { padding: 5px 0px 5px 5px; }
#navMobBurger1, #navMobBurger2 { margin-right: 5px; }
#navMobPercWrap { margin: 0px -10px -5px -10px; }
#navMobPercProg {
    height: 4px;
    -moz-border-radius: 4px; border-radius: 4px;
    background: {!! $css["color-main-off"] !!};
}

#passStrng {
    position: absolute;
    right: 10px;
    top: 27px;
    font-size: 80%;
    font-style: italic;
}


table.slAdmTable tr td, table.slAdmTable tr th, 
table.slAdmTable tr td a:link, table.slAdmTable tr td a:active, table.slAdmTable tr td a:visited, table.slAdmTable tr td a:hover, 
table.slAdmTable tr th a:link, table.slAdmTable tr th a:active, table.slAdmTable tr th a:visited, table.slAdmTable tr th a:hover {
    font-size: 14pt;
    text-decoration: none;
}
table.slAdmTable tr td a:hover, table.slAdmTable tr th a:hover {
    text-decoration: underline;
}
table.slAdmTable tr td.fPerc133, table.slAdmTable tr th.fPerc133, 
table.slAdmTable tr td a.fPerc133:link, table.slAdmTable tr td a.fPerc133:active, table.slAdmTable tr td a.fPerc133:visited, table.slAdmTable tr td a.fPerc133:hover, 
table.slAdmTable tr th a.fPerc133:link, table.slAdmTable tr th a.fPerc133:active, table.slAdmTable tr th a.fPerc133:visited, table.slAdmTable tr th a.fPerc133:hover {
    font-size: 18pt;
}
table.slAdmTable tr td.fPerc66, table.slAdmTable tr th.fPerc66, 
table.slAdmTable tr td a.fPerc66:link, table.slAdmTable tr td a.fPerc66:active, table.slAdmTable tr td a.fPerc66:visited, table.slAdmTable tr td a.fPerc66:hover, 
table.slAdmTable tr th a.fPerc66:link, table.slAdmTable tr th a.fPerc66:active, table.slAdmTable tr th a.fPerc66:visited, table.slAdmTable tr th a.fPerc66:hover {
    font-size: 10pt;
}


.adminFootBuff {
	clear: both;
	width: 100%;
	height: 80px;
}
#hidivBtnAdmFoot, #hidivAdmFoot {
    color: {!! $css["color-main-grey"] !!};
}
#hidivBtnAdmFoot {
	padding: 20px 0px 20px 0px;
	margin-bottom: 10px;
}
#hidivAdmFoot {
	padding: 20px 0px 40px 0px;
}

#navDesktop {
	display: block;
	margin-top: 20px;
}
#navMobile {
	display: none;
	margin-bottom: 10px;
}

.uploadedWrap {
    margin-top: 40px;
}


.imgTmb {
    width: 150px;
    height: 150px;
}

.responsive-video, .nPrompt .responsive-video {
    width: 100%;
    position: relative;
    padding-bottom: 56.25%;
    padding-top: 60px; overflow: hidden;
}
.responsive-video iframe, .nPrompt .responsive-video iframe,
.responsive-video object, .nPrompt .responsive-video object,
.responsive-video embed, .nPrompt .responsive-video embed {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}


.embedMapA, .embedMapDescA { width: 100%; height: 420px; }
.embedMapDescA { overflow-y: scroll; overflow-x: hidden; }
@media screen and (max-width: 992px) {
    .embedMapA { margin-bottom: 20px; }
    .embedMapA, .embedMapDescA { height: 340px; }
}
@media screen and (max-width: 768px) {
    .embedMapA, .embedMapDescA { height: 260px; }
}


.sliGalSlide {
    width: 100%;
    
}
.sliNavDiv {
    width: 100%;
    height: 30px;
    text-align: center;
}
a.sliNav, .sliNavDiv a.sliNav, .sliNavDiv a.sliNav:link, .sliNavDiv a.sliNav:active, .sliNavDiv a.sliNav:visited, .sliNavDiv a.sliNav:hover,
a.sliNavAct, .sliNavDiv a.sliNavAct, .sliNavDiv a.sliNavAct:link, .sliNavDiv a.sliNavAct:active, .sliNavDiv a.sliNavAct:visited, .sliNavDiv a.sliNavAct:hover {
    color: {!! $css["color-main-on"] !!};
    font-size: 12pt;
    margin: 5px;
}
a.sliNav, .sliNavDiv a.sliNav, .sliNavDiv a.sliNav:link, .sliNavDiv a.sliNav:active, .sliNavDiv a.sliNav:visited, .sliNavDiv a.sliNav:hover {
    opacity:0.33; filter:alpha(opacity=33);
}
.sliNavDiv a.sliLft:link, .sliNavDiv a.sliLft:active, .sliNavDiv a.sliLft:visited, .sliNavDiv a.sliLft:hover, 
.sliNavDiv a.sliRgt:link, .sliNavDiv a.sliRgt:active, .sliNavDiv a.sliRgt:visited, .sliNavDiv a.sliRgt:hover {
    width: 20%;
    height: 30px;
    padding: 0px;
    margin: 0px;
    font-size: 16pt;
    text-shadow: -1px 1px 1px {!! $css["color-main-faint"] !!};
    float: left;
    text-align: left;
}
.sliNavDiv a.sliRgt:link, .sliNavDiv a.sliRgt:active, .sliNavDiv a.sliRgt:visited, .sliNavDiv a.sliRgt:hover {
    float: right;
    text-align: right;
}
.sliNavDiv a.sliLft i, .sliNavDiv a.sliLft:link i, .sliNavDiv a.sliLft:active i, .sliNavDiv a.sliLft:visited i, .sliNavDiv a.sliLft:hover i, 
.sliNavDiv a.sliRgt i, .sliNavDiv a.sliRgt:link i, .sliNavDiv a.sliRgt:active i, .sliNavDiv a.sliRgt:visited i, .sliNavDiv a.sliRgt:hover i {
    margin: 10px;
}
.sliNavDiv a.sliLft div, .sliNavDiv a.sliLft:link div, .sliNavDiv a.sliLft:active div, .sliNavDiv a.sliLft:visited div, .sliNavDiv a.sliLft:hover div, 
.sliNavDiv a.sliRgt div, .sliNavDiv a.sliRgt:link div, .sliNavDiv a.sliRgt:active div, .sliNavDiv a.sliRgt:visited div, .sliNavDiv a.sliRgt:hover div {
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

.glossaryList { margin: 0px 15px; }

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


@media screen and (max-width: 1200px) {
    
}
@media screen and (max-width: 992px) {
    
	#navDesktop { display: none; }
	#navMobile { display: block; }
	
    a#menuColpsBtn:link, a#menuColpsBtn:visited, a#menuColpsBtn:active, a#menuColpsBtn:hover {
        display: none;
    }
    a#menuUnColpsBtn:link, a#menuUnColpsBtn:visited, a#menuUnColpsBtn:active, a#menuUnColpsBtn:hover {
	    display: block;
	}
    #leftSideWdth { width: 24px; }
    #leftSideWrap { width: 24px; padding: 0px 6px; }
    #leftAdmMenu { display: none; }
    #mainBody { padding: 0px; }
    @media screen and (max-height: 650px) {
        #leftSideWrap { position: static; }
    }
    
}
@media screen and (max-width: 768px) {
	
	input.nFormBtnSub, input.nFormBtnBack { font-size: 20pt; }
	#logoTxt { padding-left: 0px; margin-top: -2px; margin-left: -5px; }
	#formErrorMsg h1, #formErrorMsg h2, #formErrorMsg h3 { font-size: 18pt; }
	.nodeWrap .jumbotron, .nPrompt .jumbotron { padding: 30px 20px 30px 20px; }
    input.otherGender { width: 240px; }
    table.slSpreadTbl tr td.sprdFld input.form-control-lg, table.slSpreadTbl tr td.sprdFld select.form-control-lg {
        padding: 5px;
    }
    table.slSpreadTbl tr td.sprdRowLab, table.slSpreadTbl tr th.sprdRowLab, 
        table.slSpreadTbl tr th, .nFld table.slSpreadTbl tr th { 
        font-size: 14px; 
    }
    input.otherFld, input.form-control.otherFld, label input.otherFld, label input.form-control.otherFld {
        width: 270px;
    }
    .glossaryList .col-10 { padding-top: 0px; margin-top: -5px; }

}
@media screen and (max-width: 480px) {
    
    @if (isset($GLOBALS['SL']->sysOpts['logo-img-sm']) && trim($GLOBALS['SL']->sysOpts['logo-img-sm']) != ''
        && $GLOBALS['SL']->sysOpts['logo-img-sm'] != $GLOBALS['SL']->sysOpts['logo-img-lrg'])
        #slLogoImgSm { display: inline; }
        #slLogoImg { display: none; }
    @endif
	#logoTxt {
	    font-size: 28pt; 
	    padding-left: 0px;
	    margin-top: -9px 0px -9px -5px;
	}

    a.slNavLnk, a.slNavLnk:link, a.slNavLnk:active, a.slNavLnk:visited, a.slNavLnk:hover, 
    .slNavRight a, .slNavRight a.slNavLnk:link, .slNavRight a.slNavLnk:active, .slNavRight a.slNavLnk:visited, .slNavRight a.slNavLnk:hover {
        padding: 15px 5px 15px 5px;
        margin-right: 5px;
    }
    
    table.slAdmTable tr td, table.slAdmTable tr th, 
    table.slAdmTable tr td a:link, table.slAdmTable tr td a:active, table.slAdmTable tr td a:visited, table.slAdmTable tr td a:hover, 
    table.slAdmTable tr th a:link, table.slAdmTable tr th a:active, table.slAdmTable tr th a:visited, table.slAdmTable tr th a:hover {
        font-size: 10pt;
    }
    table.slAdmTable tr td.fPerc133, table.slAdmTable tr th.fPerc133, 
    table.slAdmTable tr td a.fPerc133:link, table.slAdmTable tr td a.fPerc133:active, table.slAdmTable tr td a.fPerc133:visited, table.slAdmTable tr td a.fPerc133:hover, 
    table.slAdmTable tr th a.fPerc133:link, table.slAdmTable tr th a.fPerc133:active, table.slAdmTable tr th a.fPerc133:visited, table.slAdmTable tr th a.fPerc133:hover {
        font-size: 13pt;
    }
    table.slAdmTable tr td.fPerc66, table.slAdmTable tr th.fPerc66, 
    table.slAdmTable tr td a.fPerc66:link, table.slAdmTable tr td a.fPerc66:active, table.slAdmTable tr td a.fPerc66:visited, table.slAdmTable tr td a.fPerc66:hover, 
    table.slAdmTable tr th a.fPerc66:link, table.slAdmTable tr th a.fPerc66:active, table.slAdmTable tr th a.fPerc66:visited, table.slAdmTable tr th a.fPerc66:hover {
        font-size: 7pt;
    }

	.nodeSub .btn-lg { font-size: 18pt; }
	
	.fixed, #fixedHeader.fixed { padding-top: 10px; top: 30px; }
	.jumbotron { padding: 20px; }
	
	.unitFld { width: 70px; }
    input.otherFld, input.form-control.otherFld, label input.otherFld, label input.form-control.otherFld {
        width: 100%;
    }
    
    .nFld textarea.form-control-lg { font-size: 1rem; }
    
    table.slSpreadTbl tr td.sprdFld input.form-control-lg, table.slSpreadTbl tr td.sprdFld select.form-control-lg { padding: 5px; }
    table.slSpreadTbl tr td.sprdRowLab, table.slSpreadTbl tr th.sprdRowLab, 
        table.slSpreadTbl tr th, .nFld table.slSpreadTbl tr th {
        padding: 12px 6px 0px 6px;
    }
	
}



.scoreLabel {
	font-size: 20pt;
	color: {!! $css["color-main-on"] !!};
}




.hidden {
	position:absolute;
	left:-10000px;
	top:auto;
	width:1px;
	height:1px;
	overflow:hidden;
}

.prevBox {
    width: 100%;
    height: 200px;
	overflow:auto;
	padding: 0px;
}



.f8, table tr td.f8, i.f8, a.f8:link, a.f8:active, a.f8:visited, a.f8:hover, input.f8 , select.f8 , textarea.f8 { font-size: 8pt; }
.f9, table tr td.f9, i.f9, a.f9:link, a.f9:active, a.f9:visited, a.f9:hover, input.f9 , select.f9 , textarea.f9 { font-size: 9pt; }
.f10, table tr td.f10, i.f10, a.f10:link, a.f10:active, a.f10:visited, a.f10:hover, input.f10 , select.f10 , textarea.f10 { font-size: 10pt; }
.f11, table tr td.f11, i.f11, a.f11:link, a.f11:active, a.f11:visited, a.f11:hover, input.f11 , select.f11 , textarea.f11 { font-size: 11pt; }
.f12, table tr td.f12, i.f12, a.f12:link, a.f12:active, a.f12:visited, a.f12:hover, input.f12 , select.f12 , textarea.f12 { font-size: 12pt; }
.f13, table tr td.f13, i.f13, a.f13:link, a.f13:active, a.f13:visited, a.f13:hover, input.f13 , select.f13 , textarea.f13 { font-size: 13pt; }
.f14, table tr td.f14, i.f14, a.f14:link, a.f14:active, a.f14:visited, a.f14:hover, input.f14 , select.f14 , textarea.f14 { font-size: 14pt; }
.f16, table tr td.f16, i.f16, a.f16:link, a.f16:active, a.f16:visited, a.f16:hover, input.f16 , select.f16 , textarea.f16 { font-size: 16pt; }
.f18, table tr td.f18, i.f18, a.f18:link, a.f18:active, a.f18:visited, a.f18:hover, input.f18 , select.f18 , textarea.f18 { font-size: 18pt; }
.f20, table tr td.f20, i.f20, a.f20:link, a.f20:active, a.f20:visited, a.f20:hover, input.f20 , select.f20 , textarea.f20 { font-size: 20pt; }
.f22, table tr td.f22, i.f22, a.f22:link, a.f22:active, a.f22:visited, a.f22:hover, input.f22 , select.f22 , textarea.f22 { font-size: 22pt; }
.f24, table tr td.f24, i.f24, a.f24:link, a.f24:active, a.f24:visited, a.f24:hover, input.f24 , select.f24 , textarea.f24 { font-size: 24pt; }
.f26, table tr td.f26, i.f26, a.f26:link, a.f26:active, a.f26:visited, a.f26:hover, input.f26 , select.f26 , textarea.f26 { font-size: 26pt; }
.f28, table tr td.f28, i.f28, a.f28:link, a.f28:active, a.f28:visited, a.f28:hover, input.f28 , select.f28 , textarea.f28 { font-size: 28pt; }
.f30, table tr td.f30, i.f30, a.f30:link, a.f30:active, a.f30:visited, a.f30:hover, input.f30 , select.f30 , textarea.f30 { font-size: 30pt; }
.f32, table tr td.f32, i.f32, a.f32:link, a.f32:active, a.f32:visited, a.f32:hover, input.f32 , select.f32 , textarea.f32 { font-size: 32pt; }
.f36, table tr td.f36, i.f36, a.f36:link, a.f36:active, a.f36:visited, a.f36:hover, input.f36 , select.f36 , textarea.f36 { font-size: 36pt; }
.f48, table tr td.f48, i.f48, a.f48:link, a.f48:active, a.f48:visited, a.f48:hover, input.f48 , select.f48 , textarea.f48 { font-size: 48pt; }
.f60, table tr td.f60, i.f60, a.f60:link, a.f60:active, a.f60:visited, a.f60:hover, input.f60 , select.f60 , textarea.f60 { font-size: 60pt; }

.fPerc40 { font-size: 40%; }
.fPerc66 { font-size: 66%; }
.fPerc80 { font-size: 80%; }
.fPerc125 { font-size: 125%; }
.fPerc133 { font-size: 133%; }
.fPerc200 { font-size: 200%; }
.fPerc300 { font-size: 300%; }

.lH10, table tr td.lH10, a.lH10:link, a.lH10:active, a.lH10:visited, a.lH10:hover { line-height: 10px; }
.lH13, table tr td.lH13, a.lH13:link, a.lH13:active, a.lH13:visited, a.lH13:hover { line-height: 13px; }
.lH16, table tr td.lH16, a.lH16:link, a.lH16:active, a.lH16:visited, a.lH16:hover { line-height: 16px; }
.lH18, table tr td.lH18, a.lH18:link, a.lH18:active, a.lH18:visited, a.lH18:hover { line-height: 18px; }
.lH20, table tr td.lH20, a.lH20:link, a.lH20:active, a.lH20:visited, a.lH20:hover { line-height: 20px; }
.lH24, table tr td.lH24, a.lH24:link, a.lH24:active, a.lH24:visited, a.lH24:hover { line-height: 24px; }
.lH30, table tr td.lH30, a.lH30:link, a.lH30:active, a.lH30:visited, a.lH30:hover { line-height: 30px; }
.lH38, table tr td.lH38, a.lH38:link, a.lH38:active, a.lH38:visited, a.lH38:hover { line-height: 38px; }

.lS0, table tr td.lS0, a.lS0:link, a.lS0:active, a.lS0:visited, a.lS0:hover { letter-spacing: 0em; }
.lS1, table tr td.lS1, a.lS1:link, a.lS1:active, a.lS1:visited, a.lS1:hover { letter-spacing: -0.01em; }
.lS2, table tr td.lS2, a.lS2:link, a.lS2:active, a.lS2:visited, a.lS2:hover { letter-spacing: -0.02em; }
.lS3, table tr td.lS3, a.lS3:link, a.lS3:active, a.lS3:visited, a.lS3:hover { letter-spacing: -0.03em; }
.lS4, table tr td.lS4, a.lS4:link, a.lS4:active, a.lS4:visited, a.lS4:hover { letter-spacing: -0.04em; }
.lS6, table tr td.lS6, a.lS6:link, a.lS6:active, a.lS6:visited, a.lS6:hover { letter-spacing: -0.06em; }
.lSp2, table tr td.lSp2, a.lSp2:link, a.lSp2:active, a.lSp2:visited, a.lSp2:hover { letter-spacing: 0.02em; }
.lSp4, table tr td.lSp4, a.lSp4:link, a.lSp4:active, a.lSp4:visited, a.lSp4:hover { letter-spacing: 0.04em; }

.under, table tr td.under, a.under:link, a.under:active, a.under:visited, a.under:hover { text-decoration: underline; }
.noUnd, a.noUnd:link, a.noUnd:active, a.noUnd:visited, a.noUnd:hover { text-decoration: none; }
a.overUnd:link, a.overUnd:active, a.overUnd:visited, a.overUnd:hover { text-decoration: none; }
a.overUnd:hover { text-decoration: underline; }

.fixDiv { position: fixed; }
.relDiv, .relDivMini, table tr td .relDiv { position: relative; vertical-align: top; text-align: left; }
.absDiv, table tr td .absDiv { position: absolute; vertical-align: top; text-align: left; }
.relDivMini { height: 1px; width: 1px; }

.fL { float: left; }
.fR { float: right; }
.fC { clear: both; }

.ww { word-wrap: break-word; }

.h100, table.h100, table tr td.h100 { height: 100%; }
.h50, table.h50, table tr td.h50 { height: 50%; }
.w100, table.w100, table tr td.w100, input.w100, select.w100, textarea.w100 { width: 100%; }
.w95, table.w95, table tr td.w95, input.w95, select.w95, textarea.w95 { width: 95%; }
.w90, table.w90, table tr td.w90, input.w90, select.w90, textarea.w90 { width: 90%; }
.w85, table.w85, table tr td.w85, input.w85, select.w85, textarea.w85 { width: 85%; }
.w80, table.w80, table tr td.w80, input.w80, select.w80, textarea.w80 { width: 80%; }
.w75, table.w75, table tr td.w75, input.w75, select.w75, textarea.w75 { width: 75%; }
.w66, table.w66, table tr td.w66, input.w66, select.w66, textarea.w66 { width: 66%; }
.w60, table.w60, table tr td.w60, input.w60, select.w60, textarea.w60 { width: 60%; }
.w50, table.w50, table tr td.w50, input.w50, select.w50, textarea.w50 { width: 50%; }
.w48, table.w48, table tr td.w48, input.w48, select.w48, textarea.w48 { width: 48%; }
.w45, table.w45, table tr td.w45, input.w45, select.w45, textarea.w45 { width: 45%; }
.w40, table.w40, table tr td.w40, input.w40, select.w40, textarea.w40 { width: 40%; }
.w35, table.w35, table tr td.w35, input.w35, select.w35, textarea.w35 { width: 35%; }
.w33, table.w33, table tr td.w33, input.w33, select.w33, textarea.w33 { width: 33%; }
.w31, table.w31, table tr td.w31, input.w31, select.w31, textarea.w31 { width: 31%; }
.w30, table.w30, table tr td.w30, input.w30, select.w30, textarea.w30 { width: 30%; }
.w25, table.w25, table tr td.w25, input.w25, select.w25, textarea.w25 { width: 25%; }
.w23, table.w23, table tr td.w23, input.w23, select.w23, textarea.w23 { width: 23%; }
.w20, table.w20, table tr td.w20, input.w20, select.w20, textarea.w20 { width: 20%; }
.w15, table.w15, table tr td.w15, input.w15, select.w15, textarea.w15 { width: 15%; }
.w10, table.w10, table tr td.w10, input.w10, select.w10, textarea.w10 { width: 10%; }
.w5, table.w5, table tr td.w5, input.w5, select.w5, textarea.w5 { width: 5%; }
.w1, table.w1, table tr td.w1, input.w1, select.w1, textarea.w1 { width: 1%; }
.wAuto, h1.wAuto, h2.wAuto, h3.wAuto, h4.wAuto, h5.wAuto,
a.wAuto:link, a.wAuto:visited, a.wAuto:active, a.wAuto:hover {
    width: auto;
}

.zind0 { z-index: 0; }
.zind100 { z-index: 100; }

.vaT, table tr td.vaT { vertical-align: top; }
.vaM, table tr td.vaM { vertical-align: middle; }
.vaB, table tr td.vaB { vertical-align: bottom; }
.taL, table tr td.taL { text-align: left; }
.taC, table tr td.taC { text-align: center; }
.taR, table tr td.taR { text-align: right; }
.justMe, table tr td.justMe { text-align: justify; text-align-last: justify; text-justify: inter-word; width: 100%; }
.unJust, table tr td.unJust { text-align: left; }
a.undL, a.undL:visited, a.undL:active, a.undL:hover { text-decoration: underline; }

a.noPoint, a.noPoint:link, a.noPoint:visited, a.noPoint:active, a.noPoint:hover,
a.btn.noPoint, a.btn.noPoint:link, a.btn.noPoint:visited, a.btn.noPoint:active, a.btn.noPoint:hover {
    cursor: default;
}
.crsrPntr { cursor: pointer; }
select.form-control { cursor: pointer; }

/* Can Be Read Like... margin Left 5, margin Bottom negative 5 */
h1.m0, h2.m0, h3.m0 { margin: 0px; }
.m0 { margin: 0px; } .mL0 { margin-left: 0px; } .mR0 { margin-right: 0px; } .mT0 { margin-top: 0px; } .mB0 { margin-bottom: 0px; }
.m3 { margin: 3px; } .mL3 { margin-left: 3px; } .mR3 { margin-right: 3px; } .mT3 { margin-top: 3px; } .mB3 { margin-bottom: 3px; }
.m5 { margin: 5px; } .mL5 { margin-left: 5px; } .mR5 { margin-right: 5px; } .mT5 { margin-top: 5px; } .mB5 { margin-bottom: 5px; }
.m10 { margin: 10px; } .mL10 { margin-left: 10px; } .mR10 { margin-right: 10px; } .mT10 { margin-top: 10px; } .mB10 { margin-bottom: 10px; }
.m15 { margin: 15px; } .mL15 { margin-left: 15px; } .mR10 { margin-right: 15px; } .mT10 { margin-top: 15px; } .mB10 { margin-bottom: 15px; }
.m20 { margin: 20px; } .mL20 { margin-left: 20px; } .mR20 { margin-right: 20px; } .mT20 { margin-top: 20px; } .mB20 { margin-bottom: 20px; }
.m40 { margin: 40px; } .mL40 { margin-left: 40px; } .mR40 { margin-right: 40px; } .mT40 { margin-top: 40px; } .mB40 { margin-bottom: 40px; }
.mLn3 { margin-left: -3px; } .mRn3 { margin-right: -3px; } .mTn3 { margin-top: -3px; } .mBn3 { margin-bottom: -3px; }
.mLn5 { margin-left: -5px; } .mRn5 { margin-right: -5px; } .mTn5 { margin-top: -5px; } .mBn5 { margin-bottom: -5px; }
.mLn10 { margin-left: -10px; } .mRn10 { margin-right: -10px; } .mTn10 { margin-top: -10px; } .mBn10 { margin-bottom: -10px; }
.mLn15 { margin-left: -15px; } .mRn15 { margin-right: -15px; } .mTn15 { margin-top: -15px; } .mBn15 { margin-bottom: -15px; }
.mLn20 { margin-left: -20px; } .mRn20 { margin-right: -20px; } .mTn20 { margin-top: -20px; } .mBn20 { margin-bottom: -20px; }
.mLn50 { margin-left: -50px; } .mRn50 { margin-right: -50px; } .mTn50 { margin-top: -50px; } .mBn50 { margin-bottom: -50px; }

.mRp1 { margin-right: 1%; }
.mRp2 { margin-right: 2%; }

.p5, table tr td.p5 { padding: 5px; } .pL5, table tr td.pL5 { padding-left: 5px; } .pR5, table tr td.pR5 { padding-right: 5px; } .pT5, table tr td.pT5 { padding-top: 5px; } .pB5, table tr td.pB5 { padding-bottom: 5px; } .p5, table tr td.p5 { padding: 5px; }
.p10, table tr td.p10 { padding: 10px; } .pL10, table tr td.pL10 { padding-left: 10px; } .pR10, table tr td.pR10 { padding-right: 10px; } .pT10, table tr td.pT10 { padding-top: 10px; } .pB10, table tr td.pB10 { padding-bottom: 10px; } .p10, table tr td.p10 { padding: 10px; }
.p15, table tr td.p15 { padding: 15px; } .pL15, table tr td.pL15 { padding-left: 15px; } .pR15, table tr td.pR15 { padding-right: 15px; } .pT15, table tr td.pT15 { padding-top: 15px; } .pB15, table tr td.pB15 { padding-bottom: 15px; } .p15, table tr td.p15 { padding: 15px; }
.p20, table tr td.p20 { padding: 20px; } .pL20, table tr td.pL20 { padding-left: 20px; } .pR20, table tr td.pR20 { padding-right: 20px; } .pT20, table tr td.pT20 { padding-top: 20px; } .pB20, table tr td.pB20 { padding-bottom: 20px; } .p20, table tr td.p20 { padding: 20px; }
.p40, table tr td.p40 { padding: 40px; } .pL40, table tr td.pL40 { padding-left: 40px; } .pR40, table tr td.pR40 { padding-right: 40px; } .pT40, table tr td.pT40 { padding-top: 40px; } .pB40, table tr td.pB40 { padding-bottom: 40px; } .p40, table tr td.p40 { padding: 40px; }

.round5 { -moz-border-radius: 5px; border-radius: 5px; }
.round10 { -moz-border-radius: 10px; border-radius: 10px; }
.round15 { -moz-border-radius: 15px; border-radius: 15px; }
.round20 { -moz-border-radius: 20px; border-radius: 20px; }
.round30 { -moz-border-radius: 30px; border-radius: 30px; }

.tmbRound { width: 140px; height: 140px; -moz-border-radius: 70px; border-radius: 70px; }
.bigTmbRound, .bigTmbRoundDiv { width: 125px; height: 125px; -moz-border-radius: 62px; border-radius: 62px; overflow: hidden; }
.bigTmbRoundDiv img { width: 125px; min-height: 125px; }
.hugTmbRound, .hugTmbRoundDiv { width: 175px; height: 175px; -moz-border-radius: 87px; border-radius: 87px; overflow: hidden; }
.hugTmbRoundDiv img { width: 175px; min-height: 175px; }
.hugTmbRoundDiv { border: 2px {!! $css["color-main-bg"] !!} solid; box-shadow: 0px 0px 2px {!! $css["color-main-text"] !!}; }

.opac1 { opacity:0.01; filter:alpha(opacity=1); }
.opac10 { opacity:0.10; filter:alpha(opacity=10); }
.opac20 { opacity:0.20; filter:alpha(opacity=20); }
.opac25 { opacity:0.25; filter:alpha(opacity=25); }
.opac33 { opacity:0.33; filter:alpha(opacity=33); }
.opac50 { opacity:0.50; filter:alpha(opacity=50); }
.opac66 { opacity:0.66; filter:alpha(opacity=66); }
.opac75 { opacity:0.75; filter:alpha(opacity=75); }
.opac80 { opacity:0.80; filter:alpha(opacity=80); }
.opac85 { opacity:0.85; filter:alpha(opacity=85); }
.opac90 { opacity:0.90; filter:alpha(opacity=90); }
.opac95 { opacity:0.95; filter:alpha(opacity=95); }
.opac99 { opacity:0.99; filter:alpha(opacity=99); }
.opac100 { opacity:1.00; filter:alpha(opacity=100); }

.wrdBrkAll, a.wrdBrkAll:link, a.wrdBrkAll:visited, a.wrdBrkAll:active, a.wrdBrkAll:hover { word-break: break-all; }

.monospacer, textarea.monospacer {
    font-family: Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;
}

.icoBig, i.icoBig, .icoBig i { font-size: 54px; }
.icoHuge, i.icoHuge, .icoHuge i { font-size: 82px; }
.icoMssv, i.icoMssv, .icoMssv i { font-size: 120px; }

a.label, a.label:link, a.label:visited, a.label:active, a.label:hover, 
a.label.label-primary:link, a.label.label-primary:active, a.label.label-primary:visited, a.label.label-primary:hover {
    color: {!! $css["color-main-bg"] !!};
}

.red, .redDrk, h1.red, h2.red, h3.red, label .red { color: {!! $css["color-danger-on"] !!}; }

.blk, a.blk:link, a.blk:active, a.blk:visited, a.blk:hover { color: {!! $css["color-main-text"] !!}; }
.wht, b.wht, a.wht:link, a.wht:active, a.wht:visited, a.wht:hover { color: {!! $css["color-main-bg"] !!}; }
.fnt, b.fnt, a.fnt:link, a.fnt:active, a.fnt:visited, a.fnt:hover { color: {!! $css["color-main-faint"] !!}; }
.gry4 { color: #444; }
.gry6 { color: #666; }
.gry8 { color: #888; }
.gry9 { color: #999; }
.gryA { color: #AAA; }
.gryC { color: #CCC; }

.bld, a.bld:link, a.bld:active, a.bld:visited, a.bld:hover { font-weight: bold; }
.nobld { font-weight: normal; }
.ital { font-style: italic; }

.brdNo, table.brdNo, table.brdNo tr, table.brdNo tr td { border: 0px none; }
.brd       { border: 1px {!! $css["color-main-off"] !!} solid; }
.brdDsh    { border: 1px {!! $css["color-main-off"] !!} dashed; }
.brdDshGry { border: 1px {!! $css["color-main-grey"] !!} dashed; }
.brdDrk    { border: 1px {!! $css["color-main-on"] !!} solid; }
.brdLgt    { border: 1px {!! $css["color-main-off"] !!} solid; }
.brdGrey   { border: 1px {!! $css["color-main-grey"] !!} solid; }
.brdFnt    { border: 1px {!! $css["color-main-faint"] !!} solid; }
.brdRed    { border: 1px {!! $css["color-danger-on"] !!} solid; }
.brdRedLgt { border: 1px {!! $css["color-danger-off"] !!} solid; }
.brdBlk    { border: 1px {!! $css["color-main-text"] !!} solid; }
.brdA      { border: 1px #AAA solid; }
.brdC      { border: 1px #CCC solid; }
.brdEdash  { border: 1px #EEE dashed; }

.brdTop, table tr.brdTop td, table tr.brdTop th, table tr td.brdTop, table tr th.brdTop { border-top: 1px {!! $css["color-main-off"] !!} solid; }
.brdBot, table tr.brdBot td, table tr.brdBot th, table tr td.brdBot, table tr th.brdBot { border-bottom: 1px {!! $css["color-main-off"] !!} solid; }
.brdLft, table tr.brdLft td, table tr.brdLft th, table tr td.brdLft, table tr th.brdLft { border-left: 1px {!! $css["color-main-off"] !!} solid; }
.brdRgt, table tr.brdRgt td, table tr.brdRgt th, table tr td.brdRgt, table tr th.brdRgt { border-right: 1px {!! $css["color-main-off"] !!} solid; }

.brdTopNon, table tr.brdTopNon td, table tr.brdTopNon th { border-top: 0px none; }
.brdBotNon, table tr.brdBotNon td, table tr.brdBotNon th { border-bottom: 0px none; }

.brdBotBlk, table tr.brdBotBlk td, table tr.brdBotBlk th { border-bottom: 1px {!! $css["color-main-text"] !!} solid; }
.brdBotBlk2, table tr.brdBotBlk2 td, table tr.brdBotBlk2 th { border-bottom: 2px {!! $css["color-main-text"] !!} solid; }
.brdBotGrey, table tr.brdBotGrey td, table tr.brdBotGrey th { border-bottom: 1px {!! $css["color-main-grey"] !!} solid; }

.brdBotBluL, table tr.brdBotBluL td, table tr.brdBotBluL th { border-bottom: 1px {!! $css["color-main-off"] !!} solid; }
.brdBotBluL3, table tr.brdBotBluL3 td, table tr.brdBotBluL3 th { border-bottom: 3px {!! $css["color-main-off"] !!} solid; }

.brdRgtBluL, table tr.brdRgtBluL td, table tr.brdRgtBluL th { border-right: 1px {!! $css["color-main-off"] !!} solid; }
.brdRgtGrey, table tr.brdRgtGrey td, table tr.brdRgtGrey th { border-right: 1px {!! $css["color-main-grey"] !!} solid; }

.brdTopBluL, table tr.brdTopBluL td, table tr.brdTopBluL th { border-top: 1px {!! $css["color-main-off"] !!} solid; }
.brdTopGrey, table tr.brdTopGrey td, table tr.brdTopGrey th { border-top: 1px {!! $css["color-main-grey"] !!} solid; }
.brdTopFnt, table tr.brdTopFnt td, table tr.brdTopFnt th { border-top: 1px {!! $css["color-main-faint"] !!} solid; }

.row1, table tr.row1 { background: {!! $css["color-main-bg"] !!}; }
.row2, table tr.row2, .table-striped>tbody>tr:nth-of-type(odd) { background: {!! $css["color-main-faint"] !!}; }
.BGblueLight { background: {!! $css["color-main-off"] !!}; }
.BGblueDark { background: {!! $css["color-main-on"] !!}; }
.BGredDark { background: {!! $css["color-danger-on"] !!}; }
.bgFnt { background: {!! $css["color-main-faint"] !!}; }
.bgWht { background: {!! $css["color-main-bg"] !!}; }
.bgGry { background: {!! $css["color-main-grey"] !!}; }

.bgGrn { background: #c3ffe1; }
.bgYel { background: #fffdc3; }
.bgRed { background: #ffd2c9; }
.bgNone, textarea.bgNone, img.bgNone { background: none; }


.infoOn, a.infoOn:link, a.infoOn:visited, a.infoOn:active, a.infoOn:hover { color: {!! $css["color-info-on"] !!};  }
.infoOff, a.infoOn:link, a.infoOff:visited, a.infoOff:active, a.infoOff:hover { color: {!! $css["color-info-off"] !!}; }
.warnOn, a.warnOn:link, a.warnOn:visited, a.warnOn:active, a.warnOn:hover  { color: {!! $css["color-warn-on"] !!};  }
.warnOff, a.warnOff:link, a.warnOff:visited, a.warnOff:active, a.warnOff:hover { color: {!! $css["color-warn-off"] !!}; }

a.navbar-brand:link, a.navbar-brand:visited, a.navbar-brand:active, a.navbar-brand:hover {
	color: {!! $css["color-nav-text"] !!};
}

.slBlueLight, a.slBlueLight:link, a.slBlueLight:visited, a.slBlueLight:active, a.slBlueLight:hover,
a:link .slBlueLight, a:visited .slBlueLight, a:active .slBlueLight, a:hover .slBlueLight {
	color: {!! $css["color-main-off"] !!};
}
.slBlueDark, a.slBlueDark:link, a.slBlueDark:visited, a.slBlueDark:active, a.slBlueDark:hover,
a:link .slBlueDark, a:visited .slBlueDark, a:active .slBlueDark, a:hover .slBlueDark {
	color: {!! $css["color-main-on"] !!};
}
.slBlueFaint, a.slBlueFaint:link, a.slBlueFaint:visited, a.slBlueFaint:active, a.slBlueFaint:hover,
a:link .slBlueFaint, a:visited .slBlueFaint, a:active .slBlueFaint, a:hover .slBlueFaint {
	color: {!! $css["color-main-faint"] !!};
}

.slRedDark, a.slRedDark:link, a.slRedDark:visited, a.slRedDark:active, a.slRedDark:hover,
a:link .slRedDark, a:visited .slRedDark, a:active .slRedDark, a:hover .slRedDark {
	color: {!! $css["color-danger-on"] !!};
}
.slRedLight, a.slRedLight:link, a.slRedLight:visited, a.slRedLight:active, a.slRedLight:hover,
a:link .slRedLight, a:visited .slRedLight, a:active .slRedLight, a:hover .slRedLight {
	color: {!! $css["color-danger-off"] !!};
}

.slGreenDark, a.slGreenDark:link, a.slGreenDark:visited, a.slGreenDark:active, a.slGreenDark:hover,
a:link .slGreenDark, a:visited .slGreenDark, a:active .slGreenDark, a:hover .slGreenDark {
	color: {!! $css["color-success-on"] !!};
}
.slGreenLight, a.slGreenLight:link, a.slGreenLight:visited, a.slGreenLight:active, a.slGreenLight:hover,
a:link .slGreenLight, a:visited .slGreenLight, a:active .slGreenLight, a:hover .slGreenLight {
	color: {!! $css["color-success-off"] !!};
}

.slGrey, a.slGrey:link, a.slGrey:active, a.slGrey:visited, a.slGrey:hover, 
a:link .slGrey, a:active .slGrey, a:visited .slGrey, a:hover .slGrey {
    color: {!! $css["color-main-grey"] !!};
}

sub.slGrey { padding-left: 3px; }

.slShade, a.slShade:link, a.slShade:visited, a.slShade:active, a.slShade:hover,
a:link .slShade, a:visited .slShade, a:active .slShade, a:hover .slShade {
	text-shadow: -1px 1px 0px {!! $css["color-main-text"] !!};
}
.slShdLgt, a.slShdLgt:link, a.slShdLgt:visited, a.slShdLgt:active, a.slShdLgt:hover,
a:link .slShdLgt, a:visited .slShdLgt, a:active .slShdLgt, a:hover .slShdLgt {
	text-shadow: -1px 1px 0px {!! $css["color-main-off"] !!};
}
.slBoxShd, a.slBoxShd:link, a.slBoxShd:visited, a.slBoxShd:active, a.slBoxShd:hover,
a:link .slBoxShd, a:visited .slBoxShd, a:active .slBoxShd, a:hover .slBoxShd {
	box-shadow: 0px 0px 3px {!! $css["color-main-text"] !!};
}
.slBoxShdB, a.slBoxShdB:link, a.slBoxShdB:visited, a.slBoxShdB:active, a.slBoxShdB:hover,
a:link .slBoxShdB, a:visited .slBoxShdB, a:active .slBoxShdB, a:hover .slBoxShdB {
	box-shadow: 0px 2px 3px {!! $css["color-main-text"] !!};
}
.slBoxShd10, a.slBoxShd10:link, a.slBoxShd10:visited, a.slBoxShd10:active, a.slBoxShd10:hover,
a:link .slBoxShd10, a:visited .slBoxShd10, a:active .slBoxShd10, a:hover .slBoxShd10 {
	box-shadow: 0px 0px 20px {!! $css["color-main-text"] !!};
}
.slBoxShd20, a.slBoxShd20:link, a.slBoxShd20:visited, a.slBoxShd20:active, a.slBoxShd20:hover,
a:link .slBoxShd20, a:visited .slBoxShd20, a:active .slBoxShd20, a:hover .slBoxShd20 {
	box-shadow: 0px 0px 20px {!! $css["color-main-text"] !!};
}
.slBoxShdLgt, a.slBoxShdLgt:link, a.slBoxShdLgt:visited, a.slBoxShdLgt:active, a.slBoxShdLgt:hover,
a:link .slBoxShdLgt, a:visited .slBoxShdLgt, a:active .slBoxShdLgt, a:hover .slBoxShdLgt {
	box-shadow: 0px 0px 3px {!! $css["color-main-off"] !!};
}
.slBoxShdLgt5, a.slBoxShdLgt5:link, a.slBoxShdLgt5:visited, a.slBoxShdLgt5:active, a.slBoxShdLgt5:hover,
a:link .slBoxShdLgt5, a:visited .slBoxShdLgt5, a:active .slBoxShdLgt5, a:hover .slBoxShdLgt5 {
	box-shadow: 0px 0px 5px {!! $css["color-main-off"] !!};
}
.slBoxShdBg20, a.slBoxShdBg20:link, a.slBoxShdBg20:visited, a.slBoxShdBg20:active, a.slBoxShdBg20:hover,
a:link .slBoxShdBg20, a:visited .slBoxShdBg20, a:active .slBoxShdBg20, a:hover .slBoxShdBg20 {
	box-shadow: 0px 0px 20px {!! $css["color-main-bg"] !!};
}
.slBoxShdGryB, a.slBoxShdGryB:link, a.slBoxShdGryB:visited, a.slBoxShdGryB:active, a.slBoxShdGryB:hover,
a:link .slBoxShdGryB, a:visited .slBoxShdGryB, a:active .slBoxShdGryB, a:hover .slBoxShdGryB {
	box-shadow: 0px 2px 3px {!! $css["color-main-grey"] !!};
}

.slFaintHover, a.slFaintHover:link, a.slFaintHover:visited, a.slFaintHover:active, a.slFaintHover:hover,
a:link .slFaintHover, a:visited .slFaintHover, a:active .slFaintHover, a:hover .slFaintHover {
	color: {!! $css["color-main-faint"] !!};
}
a.slFaintHover:hover {
	color: {!! $css["color-main-off"] !!};
}
.row2 .slFaintHover, .row2 a.slFaintHover:link, .row2 a.slFaintHover:visited, .row2 a.slFaintHover:active, .row2 a.slFaintHover:hover,
.row2 a:link .slFaintHover, .row2 a:visited .slFaintHover, .row2 a:active .slFaintHover, .row2 a:hover .slFaintHover {
	color: {!! $css["color-main-bg"] !!};
}

.disIn { display: inline; }
.disNon { display: none; }
.disBlo { display: block; }
.disFlx { display: flex; }
.disRow { display: table-row; }
.ovrNo  { overflow: hidden; }
.ovrSho { overflow: visible; }
.ovrFlo { overflow: auto; }
.ovrFloY { overflow-y: auto; overflow-x: hidden; }

{!! $css["raw"] !!}
