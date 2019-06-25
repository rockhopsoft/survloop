/* generated from resources/views/vendor/survloop/css/styles-2-node-forms.blade.php */

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
	height: 30px;
	clear: both;
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
    margin-top: 15px;
}
/* .nFld, .nFld input, .nFld select, .nFld textarea {
	font-size: 16px;
} */
.subRes {
    padding-left: 20px;
    margin: 10px 0px 20px 0px;
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
    width: 57px; 
    padding: 6px 6px;
    text-align: right;
}
.unitFld {
    display: inline;
    width: 120px; 
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
    border: 1px {!! $css["color-main-on"] !!} solid;
    background: {!! $css["color-main-faint"] !!};
}
label.fingerAct, label.fingerAct:active, input.fingerAct:active+label, 
label.fingerAct:hover, input.fingerAct:hover+label, label.finger:active, input.finger:active+label {
    border: 1px {!! $css["color-main-on"] !!} solid;
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
    border: 1px {!! $css["color-main-on"] !!} solid;
    -moz-border-radius: 5px; border-radius: 5px;
}
select.fingerTxt, select.form-control.fingerTxt, .nFld select.form-control.fingerTxt {
    border: 1px {!! $css["color-main-on"] !!} solid;
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
	border-bottom: 1px {!! $css["color-main-on"] !!} solid; 
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

.uploadedWrap {
    margin-top: 40px;
}
