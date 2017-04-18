/* generated from resources/views/vendor/survloop/styles-css.blade.php */

body, p, div, table tr td, table tr th, input, textarea, select {
    font-family: {!! $css["font-main"] !!};
    font-style: normal;
    font-weight: 200;
}
b, h1, h2, h3, h4, h5, h6 {
    font-family: {!! $css["font-main"] !!};
    font-weight: 200;
}

body {
	margin: 0px; padding: 0px;
    background: {!! $css["color-main-bg"] !!};
}

body, p {
    color: {!! $css["color-main-text"] !!};
}
body, p, div, input, select, textarea, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
    line-height: 1.42857143;
}

.note-editor.note-frame .note-editing-area .note-editable, 
#summernote .note-editor.note-frame .note-editing-area .note-editable {
    background: {!! $css["color-main-bg"] !!};
    color: {!! $css["color-main-text"] !!};
}

.navbar {
    height: 56px;
	min-height: 56px;
	max-height: 56px;
	padding-top: 2px;
	overflow: hidden;
	color: {!! $css["color-main-off"] !!};
	background: {!! $css["color-nav-bg"] !!};
    border-bottom: 1px {!! $css["color-line-hr"] !!} solid;
}
#navBurger, a#navBurger:link, a#navBurger:active, a#navBurger:visited, a#navBurger:hover {
    font-size: 14pt;
    padding: 5px 10px;
    margin: 7px 0px 0px 10px;
    border: 1px {!! $css["color-main-on"] !!} solid;
    -moz-border-radius: 6px; border-radius: 6px;
}
a.slNavLnk, a.slNavLnk:link, a.slNavLnk:active, a.slNavLnk:visited, a.slNavLnk:hover, 
.slNavRight a, .slNavRight a.slNavLnk:link, .slNavRight a.slNavLnk:active, .slNavRight a.slNavLnk:visited, .slNavRight a.slNavLnk:hover {
    display: block;
    padding: 15px 15px;
    margin-right: 10px;
}

#headGap {
    display: block;
    width: 100%;
    height: 56px;
	margin-bottom: 0px;
	background: {!! $css["color-nav-bg"] !!};
}
#headGap img {
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
	background: {!! $css["color-main-faint"] !!};
}
#slNavMain .panel-body, #slNavMain div .panel .panel-body {
    padding: 5px 0px 0px 0px;
}
#slNavMain .panel-body, #slNavMain div .panel .panel-body .list-group {
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
    padding: 20px;
    text-align: center;
}
#dialogTitle {
    float: left;
    font-size: 22pt;
}
#dialog .panel .panel-heading .dialogClose {
    float: right;
}
#dialog .panel .panel-body {
    text-align: left;
}
#nondialog {
    display: block;
    width: 100%;
}

#footerLinks {
    width: 100%;
    display: block;
}

#logoLrg {
    display: block;
    margin: 1px 0px 0px 0px;
}
#logoMed {
    display: none;
    margin: 3px 0px 0px 0px;
}
#logoMed img, #logoLrg img {
    height: 46px;
}
#logoSm {
    display: none;
    margin: 1px 0px 0px 0px;
}
#logoSm img {
    height: 30px;
}
.navbar-brand, a.navbar-brand:link, a.navbar-brand:visited, a.navbar-brand:active, a.navbar-brand:hover {
	font-size: 32pt;
}
#logoTxt {
	padding-left: 10px;
	margin-top: -2px;
}

.halfPageWidth {
	width: 50%;
	min-width: 300px;
	text-align: left;
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
	padding-top: 10px;
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
    top: -40px;
}
.nodeWrap, .nodeWrapError {
	background: {!! $css["color-main-bg"] !!};
	display: block;
	padding: 0px;
	-moz-border-radius: 8px; border-radius: 8px;
}
.nodeWrapError {
	padding: 10px 5px 10px 5px;
	border: 3px {!! $css["color-danger-on"] !!} solid;
}
.nPrompt, .nFld, .nFldFing {
	display: block;
	font-size: 18px;
	color: {!! $css["color-main-text"] !!};
}
.nPrompt h1, .nPrompt h2, .nPrompt h3, .nFld h1, .nFld h2, .nFld h3, .nFld h4, .nFld h5, .nFld h6, 
.nPrompt p, .nPrompt div {
    padding: 0px;
    margin: 0px;
}
.nPrompt h1.slBlueDark, .nPrompt h2.slBlueDark, .nPrompt h3.slBlueDark {
	color: {!! $css["color-main-on"] !!};
}
.nPrompt p, .nPrompt ul {
	font-size: 18px;
    margin-top: 15px;
}
.nFld {
    margin-top: 20px;
}
.nFld, .nFld input, .nFld select, .nFld textarea {
	font-size: 16px;
}
.nPrompt ul, .nPrompt ol {
    padding: 0px 0px 0px 20px;
    margin: 20px 0px 0px 20px;
}
.nPrompt ul li, .nPrompt ol li {
    padding: 0px 0px 0px 0px;
    margin: 10px 0px 0px 0px;
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
.nFld select.timeDrop, .nFld select.form-control.timeDrop {
	width: 95px;
}
.nFld select.tinyDrop, .nFld select.form-control.tinyDrop {
	width: 60px;
}
.timeWrap input {
	width: 60px;
}

.ui-widget-header {
    border: 1px solid {!! $css["color-main-grey"] !!};
    background: {!! $css["color-main-text"] !!};
    color: {!! $css["color-main-bg"] !!};
}
.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {
    border: 1px solid {!! $css["color-main-grey"] !!};
    background: {!! $css["color-main-text"] !!};
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

input.nFormBtnSub, input.nFormBtnBack {
    font-size: 26pt;
}

input.otherFld, input.form-control.otherFld, label input.otherFld, label input.form-control.otherFld {
    width: 400px;
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
    margin-top: 40px;
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
.btn.btn-lg, .btn.btn-md {
    white-space: normal;
}


#sortable { 
	list-style-type: none; 
	margin: 0; padding: 0; 
	text-align: left; 
	width: 100%; 
	cursor: move; 
}
#sortable li, #sortable li.sortOn, #sortable li.sortOff { 
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
#sortable li.sortOn { 
	background: {!! $css["color-main-faint"] !!}; 
}
#sortable li i, #sortable li.sortOn i, #sortable li.sortOff i { 
    margin: 0px 20px;
}
@media screen and (max-width: 768px) {
    #sortable li, #sortable li.sortOn, #sortable li.sortOff {
        font-size: 14pt;
        padding: 10px 5px;
    }
    #sortable li i, #sortable li.sortOn i, #sortable li.sortOff i { 
        margin: 0px 10px;
    }
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
	background: {!! $css["color-danger-off"] !!};
	color: {!! $css["color-main-grey"] !!};
	padding: 15px;
	-moz-border-radius: 5px; border-radius: 5px;
}



.uploadWrap {
	padding: 10px 20px;
	border: 1px {!! $css["color-main-off"] !!} dotted;
	-moz-border-radius: 20px; border-radius: 20px;
}
.uploadWrap .uploadTypes {
	width: 100%;
}
.uploadWrap hr {
    border: 0px;
    border-top: 1px {!! $css["color-line-hr"] !!} dotted;
}
.uploadedWrap {
	border: 2px {!! $css["color-main-off"] !!} solid;
    -moz-border-radius: 20px; border-radius: 20px;
    margin-bottom: 10px;
}

#reportTakeActions {
	margin: -20px 0px 15px 0px;
	border: 1px {!! $css["color-main-off"] !!} solid;
	background: {!! $css["color-main-faint"] !!};
	padding: 25px 15px 5px 15px;
	-moz-border-radius: 20px; border-radius: 20px;
}
.slReport {
    font-size: 14pt;
}
.reportSectHead, .reportSectHead2, 
.slReport .reportSectHead, .slReport .reportSectHead2 {
	clear: both;
	width: 100%;
	color: {!! $css["color-main-on"] !!};
	font-size: 18pt;
	padding: 10px 0px 10px 0px;
	border-top: 1px {!! $css["color-main-on"] !!} solid;
}
.reportSectHead2, .slReport .reportSectHead2 {
	font-size: 20pt;
	border-bottom: 1px {!! $css["color-main-on"] !!} solid;
}
.reportBlock, .slReport .reportBlock {
	display: block;
	width: 100%;
	margin-bottom: 40px;
}
.reportBlock div span, .reportBlock table tr td span, 
.slReport .reportBlock div span, .slReport .reportBlock table tr td span {
	color: {!! $css["color-main-grey"] !!};
}
.reportMiniBlockLabel, .slReport .reportMiniBlockLabel {
    display: block;
    width: 100%;
    color: {!! $css["color-main-on"] !!};
    font-size: 18pt;
    font-weight: bold;
}
.reportMiniBlockDeets, .slReport .reportMiniBlockDeets {
    display: block;
    width: 100%;
    font-size: 16pt;
    padding-bottom: 20px;
}

.reportPreview {
    margin: 10px 0px;
    padding: 10px 0px;
    border-bottom: 1px {!! $css["color-main-faint"] !!} solid;
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

.basicTier0, .basicTier1, .basicTier2, .basicTier3, .basicTier4, 
.basicTier5, .basicTier6, .basicTier7, .basicTier8, .basicTier9 {
    padding: 10px 5px 10px 5px;
    margin: 0px 5px 0px 5px;
	-moz-border-radius: 20px; border-radius: 20px;
	border: 1px {!! $css["color-main-faint"] !!} dotted;
}
.basicTier1, .basicTier2, .basicTier3 { border-left: 3px {!! $css["color-main-off"] !!} dotted; }
.basicTier4, .basicTier5 { border-left: 2px {!! $css["color-main-off"] !!} dotted; }
.basicTier6, .basicTier7 { border-left: 2px {!! $css["color-main-faint"] !!} dotted; }
.basicTier8, .basicTier9 { border-left: 1px {!! $css["color-main-faint"] !!} dotted; }
.basicTier0, .basicTier0.basicTierBranch {
	border: 1px {!! $css["color-success-on"] !!} dotted;
}
.basicTierBranch {
	border: 2px {!! $css["color-main-on"] !!} dotted;
}
.basicTier1.basicTierBranch {
	border: 2px {!! $css["color-main-on"] !!} dotted;
}
.basicTierLoop, .basicTier1.basicTierLoop, .basicTier2.basicTierLoop, .basicTier3.basicTierLoop, .basicTier4.basicTierLoop, .basicTier5.basicTierLoop {
	border: 4px {!! $css["color-main-off"] !!} double;
    padding: 15px;
}
.basicTierPage {
	border: 4px {!! $css["color-main-on"] !!} double;
	margin: 15px;
    padding: 15px;
}
.basicTierData, .basicTierBranch.basicTierData, .basicTierPage.basicTierData,
.basicTierLoop.basicTierData, .basicTier1.basicTierLoop.basicTierData, .basicTier2.basicTierLoop.basicTierData, .basicTier3.basicTierLoop.basicTierData, .basicTier4.basicTierLoop.basicTierData, .basicTier5.basicTierLoop.basicTierData {
	border-right: 3px {!! $css["color-success-on"] !!} dotted;
}
.dbColor {
    color: {!! $css["color-success-on"] !!};
}

.circleBtn1 {
	color: {!! $css["color-main-on"] !!};
	opacity:0.50; filter:alpha(opacity=50);
	text-align: center;
	width: 34px;
	height: 34px;
	-moz-border-radius: 17px; border-radius: 17px;
	padding: 7px 3px 0px 3px;
	margin: -8px 5px 0px 0px;
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

#treeWrap {
	position: relative;
	margin: 0px;
    text-align: left;
}
.treeWrapForm {
    max-width: 700px;
}
#fixedHeader {
	position: relative;
	margin: 0px;
	width: 100%;
	background: none;
}
#fixedHeader h1, #fixedHeader h2 {
	margin: 0px;
}
.fixed, #fixedHeader.fixed {
	position: fixed;
	z-index: 99;
	background: {!! $css["color-main-bg"] !!};
	box-shadow: 0px 0px 20px {!! $css["color-main-bg"] !!};
	padding-top: 10px;
	padding-bottom: 5px;
	top: 47px;
	border-bottom: 1px {!! $css["color-main-faint"] !!} solid;
}



/* Bootstrap modifications... */
.jumbotron {
	background: {!! $css["color-main-faint"] !!};
}

label { font-weight: normal; }

.nav-pills.nav-wizard > li.completed .nav-arrow {
  border-color: transparent transparent transparent {!! $css["color-main-faint"] !!};
}
.nav-pills.nav-wizard > li.completed .nav-wedge {
  border-color: {!! $css["color-main-faint"] !!} {!! $css["color-main-faint"] !!} {!! $css["color-main-faint"] !!} transparent;
}
.nav-pills.nav-wizard > li.completed a {
  background-color: {!! $css["color-main-faint"] !!};
  font-weight: bold;
}
.nav-pills.nav-wizard > li.completed a:hover {
  color: {!! $css["color-main-text"] !!};
}
.nav-pills.nav-wizard > li.active a {
  font-weight: bold;
}

.stepNum, .nav-pills.nav-wizard > li .stepNum {
	display: inline;
	font-size: 10pt;
	line-height: 8px;
	padding: 0px 5px;
	margin-right: 3px;
	-moz-border-radius: 8px; border-radius: 8px;
	border: 1px {!! $css["color-main-on"] !!} solid;
}
.nav-pills.nav-wizard > li.active .stepNum {
	border: 1px {!! $css["color-main-bg"] !!} solid;
}
.stepNum2, .nav-pills.nav-wizard > li .stepNum2, 
.stepNum2active, .nav-pills.nav-wizard > li .stepNum2active, 
.stepNum2complete, .nav-pills.nav-wizard > li .stepNum2complete {
	font-size: 14pt;
	text-align: center;
	-moz-border-radius: 8px; border-radius: 8px;
	width: 30px;
	margin-top: 7px;
	padding-left: 1px;
	color: {!! $css["color-main-on"] !!};
	background: {!! $css["color-main-faint"] !!};
	border: 1px {!! $css["color-main-on"] !!} solid;
	cursor: pointer;
}
.stepNum2active, .nav-pills.nav-wizard > li .stepNum2active { 
	color: {!! $css["color-main-bg"] !!};
	background: {!! $css["color-main-on"] !!};
}
.stepNum2complete, .nav-pills.nav-wizard > li .stepNum2complete {
	color: {!! $css["color-main-on"] !!};
	background: {!! $css["color-main-faint"] !!};
}

.panel-info > .panel-heading {
    color: {!! $css["color-main-bg"] !!};
    background-image: none;
    background-color: {!! $css["color-info-on"] !!};
}
.panel-info > .panel-heading h1, .panel-info > .panel-heading h2, .panel-info > .panel-heading h3, 
.panel-info > .panel-heading h4, .panel-info > .panel-heading h5, .panel-info > .panel-heading h6 {
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
.pagination-lg>li:first-child>a, .pagination-lg>li:first-child>span { border-bottom-right-radius: 6px; border-top-right-radius: 6px } 
.pagination-lg>li:last-child>a, .pagination-lg>li:last-child>span { border-bottom-left-radius: 6px; border-top-left-radius: 6px } 
.pagination-sm>li>a, .pagination-sm>li>span { padding: 5px 10px; font-size: 12px } 
.pagination-sm>li:first-child>a, .pagination-sm>li:first-child>span { border-bottom-right-radius: 3px; border-top-rightt-radius: 3px } 
.pagination-sm>li:last-child>a, .pagination-sm>li:last-child>span { border-bottom-left-radius: 3px; border-top-left-radius: 3px }

.btn-group-xl > .btn, .btn-xl {
    padding: 15px 20px;
    font-size: 30px;
    line-height: 1.3333333;
    border-radius: 6px;
}


#adminMenu .list-group.panel > .list-group-item {
  border-bottom-right-radius: 4px;
  border-bottom-left-radius: 4px
}
#adminMenu .list-group.panel > .list-group-item .list-group-submenu {
  margin-left: 20px;
}
#adminMenu .list-group.panel > .list-group-item.primeNav {
	font-weight: normal;
	font-size: 18pt;
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
#admFootLegal {
	font-size: 11pt;
	padding: 20px 0px 10px 0px;
}

#navDesktop {
	display: block;
	margin-top: 20px;
}
#navMobile {
	display: none;
	margin-bottom: 10px;
}



@media screen and (max-width: 1200px) {
}
@media screen and (max-width: 992px) {
	#logoMed, #logoSm { display: none; }
	#logoLrg { display: block; }
	
	#navDesktop { display: none; }
	#navMobile { display: block; }
}
@media screen and (max-width: 768px) {
	
	#logoLrg, #logoSm { display: none; }
	#logoMed { display: block; }
	input.nFormBtnSub, input.nFormBtnBack { font-size: 20pt; }
	#logoTxt { padding-left: 0px; margin-top: -2px; margin-left: -5px; }
	#formErrorMsg h1, #formErrorMsg h2, #formErrorMsg h3 { font-size: 18pt; }
	.nodeWrap .jumbotron, .nPrompt .jumbotron { padding: 30px 20px 30px 20px; }
    input.otherGender { width: 240px; }
    
    /* .nPrompt h1 { font-size: 45px; }
    .nPrompt h2 { font-size: 34px; }
    .nPrompt h3 { font-size: 24px; }
    .nPrompt h4 { font-size: 20px; }
    .nPrompt h5 { font-size: 16px; }
    .nPrompt h6 { font-size: 14px; } */
    
}
@media screen and (max-width: 480px) {
	#logoLrg, #logoMed { display: none; }
	#logoSm { display: block; }
	#logoTxt {
	    font-size: 28pt; 
	    padding-left: 0px;
	    margin-top: -9px 0px -9px -5px;
	}
	
	#headGap, #headGap img { height: 38px; }
    .navbar { min-height: 32px; height: 38px; padding-top: 2px; }
    #navBurger, a#navBurger:link, a#navBurger:active, a#navBurger:visited, a#navBurger:hover {
        font-size: 12pt;
        padding: 2px 6px;
        margin: 1px 0px 0px 5px;
    }
    a.slNavLnk, a.slNavLnk:link, a.slNavLnk:active, a.slNavLnk:visited, a.slNavLnk:hover, 
    .slNavRight a, .slNavRight a.slNavLnk:link, .slNavRight a.slNavLnk:active, .slNavRight a.slNavLnk:visited, .slNavRight a.slNavLnk:hover {
        padding: 5px 5px;
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

.fPerc66 { font-size: 66%; }
.fPerc80 { font-size: 80%; }
.fPerc125 { font-size: 125%; }
.fPerc133 { font-size: 133%; }

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

.disIn { display: inline; }
.disNon { display: none; }
.disBlo { display: block; }
.ovrFlo { overflow: auto; }

.ww { word-wrap: break-word; }

.h100, table.h100, table tr td.h100 { height: 100%; }
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

/* Can Be Read Like... margin Left 5, margin Bottom negative 5 */
h1.m0, h2.m0, h3.m0 { margin: 0px; }
.m0 { margin: 0px; } .mL0 { margin-left: 0px; } .mR0 { margin-right: 0px; } .mT0 { margin-top: 0px; } .mB0 { margin-bottom: 0px; }
.m5 { margin: 5px; } .mL5 { margin-left: 5px; } .mR5 { margin-right: 5px; } .mT5 { margin-top: 5px; } .mB5 { margin-bottom: 5px; }
.m10 { margin: 10px; } .mL10 { margin-left: 10px; } .mR10 { margin-right: 10px; } .mT10 { margin-top: 10px; } .mB10 { margin-bottom: 10px; }
.m20 { margin: 20px; } .mL20 { margin-left: 20px; } .mR20 { margin-right: 20px; } .mT20 { margin-top: 20px; } .mB20 { margin-bottom: 20px; }
.m40 { margin: 40px; } .mL40 { margin-left: 40px; } .mR40 { margin-right: 40px; } .mT40 { margin-top: 40px; } .mB40 { margin-bottom: 40px; }
.mLn5 { margin-left: -5px; } .mRn5 { margin-right: -5px; } .mTn5 { margin-top: -5px; } .mBn5 { margin-bottom: -5px; }
.mLn10 { margin-left: -10px; } .mRn10 { margin-right: -10px; } .mTn10 { margin-top: -10px; } .mBn10 { margin-bottom: -10px; }
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

.opac1 { opacity:0.01; filter:alpha(opacity=1); }
.opac10 { opacity:0.10; filter:alpha(opacity=10); }
.opac20 { opacity:0.20; filter:alpha(opacity=20); }
.opac25 { opacity:0.25; filter:alpha(opacity=25); }
.opac33 { opacity:0.33; filter:alpha(opacity=33); }
.opac50 { opacity:0.50; filter:alpha(opacity=50); }
.opac75 { opacity:0.75; filter:alpha(opacity=75); }
.opac80 { opacity:0.80; filter:alpha(opacity=80); }
.opac90 { opacity:0.90; filter:alpha(opacity=90); }
.opac95 { opacity:0.95; filter:alpha(opacity=95); }
.opac99 { opacity:0.99; filter:alpha(opacity=99); }
.opac100 { opacity:1.00; filter:alpha(opacity=100); }

.monospacer, textarea.monospacer {
    font-family: Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;
}

.blk { color: {!! $css["color-main-text"] !!}; }
.wht, b.wht, a.wht:link, a.wht:active, a.wht:visited, a.wht:hover { color: {!! $css["color-main-bg"] !!}; }
.red, .redDrk, h1.red, h2.red, h3.red, label .red { color: {!! $css["color-danger-on"] !!}; font-weight: 100; }
.gry4 { color: #444; }
.gry6 { color: #666; }
.gry8 { color: #888; }
.gry9 { color: #999; }
.gryA { color: #AAA; }
.gryC { color: #CCC; }

.bld, a.bld:link, a.bld:active, a.bld:visited, a.bld:hover { font-weight: bold; }
.nobld { font-weight: normal; }
.ital { font-style: italic; }

.brd { border: 1px {!! $css["color-main-off"] !!} solid; }
.brdDrk { border: 1px {!! $css["color-main-on"] !!} solid; }
.brdLgt { border: 1px {!! $css["color-main-off"] !!} solid; }
.brdRed { border: 1px {!! $css["color-danger-on"] !!} solid; }
.brdBlk { border: 1px {!! $css["color-main-text"] !!} solid; }
.brdA { border: 1px #AAA solid; }
.brdC { border: 1px #CCC solid; }
.brdEdash { border: 1px #EEE dashed; }

.brdTop, table tr.brdTop td, table tr.brdTop th { border-top: 1px {!! $css["color-main-off"] !!} solid; }
.brdBot, table tr.brdBot td, table tr.brdBot th { border-bottom: 1px {!! $css["color-main-off"] !!} solid; }
.brdLft, table tr.brdLft td, table tr.brdLft th { border-left: 1px {!! $css["color-main-off"] !!} solid; }

.brdBotBlk, table tr.brdBotBlk td, table tr.brdBotBlk th { border-bottom: 1px {!! $css["color-main-text"] !!} solid; }
.brdBotBlk2, table tr.brdBotBlk2 td, table tr.brdBotBlk2 th { border-bottom: 2px {!! $css["color-main-text"] !!} solid; }
.brdBot9, table tr.brdBot9 td, table tr.brdBot9 th { border-bottom: 1px #999 solid; }

.brdBotBluL, table tr.brdBotBluL td, table tr.brdBotBluL th { border-bottom: 1px {!! $css["color-main-off"] !!} solid; }
.brdBotBluL3, table tr.brdBotBluL3 td, table tr.brdBotBluL3 th { border-bottom: 3px {!! $css["color-main-off"] !!} solid; }

.row1, table tr.row1 { background: {!! $css["color-main-bg"] !!}; }
.row2, table tr.row2 { background: {!! $css["color-main-faint"] !!}; }
.BGblueLight { background: {!! $css["color-main-off"] !!}; }
.BGblueDark { background: {!! $css["color-main-on"] !!}; }
.BGredDark { background: {!! $css["color-danger-on"] !!}; }
.bgWht { background: {!! $css["color-main-bg"] !!}; }

.bgGrn { background: #c3ffe1; }
.bgYel { background: #fffdc3; }
.bgRed { background: #ffd2c9; }
.bgNone, textarea.bgNone { background: none; }


.slBlueLight, a.slBlueLight:link, a.slBlueLight:visited, a.slBlueLight:active, a.slBlueLight:hover {
	color: {!! $css["color-main-off"] !!};
}
.slBlueDark, a.slBlueDark:link, a.slBlueDark:visited, a.slBlueDark:active, a.slBlueDark:hover {
	color: {!! $css["color-main-on"] !!};
}
.slBlueFaint, a.slBlueFaint:link, a.slBlueFaint:visited, a.slBlueFaint:active, a.slBlueFaint:hover {
	color: {!! $css["color-main-faint"] !!};
}

.slRedDark, a.slRedDark:link, a.slRedDark:visited, a.slRedDark:active, a.slRedDark:hover {
	color: {!! $css["color-danger-on"] !!};
}
.slRedLight, a.slRedLight:link, a.slRedLight:visited, a.slRedLight:active, a.slRedLight:hover {
	color: {!! $css["color-danger-off"] !!};
}

.slGreenDark, a.slGreenDark:link, a.slGreenDark:visited, a.slGreenDark:active, a.slGreenDark:hover {
	color: {!! $css["color-success-on"] !!};
}
.slGreenLight, a.slGreenLight:link, a.slGreenLight:visited, a.slGreenLight:active, a.slGreenLight:hover {
	color: {!! $css["color-success-off"] !!};
}

.slGrey {
    color: {!! $css["color-main-grey"] !!};
}
.infoOn {
    color: {!! $css["color-info-on"] !!};
}
.infoOff {
    color: {!! $css["color-info-off"] !!};
}
.warnOn {
    color: {!! $css["color-warn-on"] !!};
}
.warnOff {
    color: {!! $css["color-warn-off"] !!};
}

a.navbar-brand:link, a.navbar-brand:visited, a.navbar-brand:active, a.navbar-brand:hover {
	color: {!! $css["color-main-off"] !!};
}
a.navbar-brand:hover {
	color: {!! $css["color-main-bg"] !!};
}

.slShade, a.slShade:link, a.slShade:visited, a.slShade:active, a.slShade:hover {
	text-shadow: 0px 2px 2px {!! $css["color-main-faint"] !!};
}

.slFaintHover, a.slFaintHover:link, a.slFaintHover:visited, a.slFaintHover:active, a.slFaintHover:hover {
	color: {!! $css["color-main-faint"] !!};
}
a.slFaintHover:hover {
	color: {!! $css["color-main-off"] !!};
}


{!! $css["raw"] !!}
