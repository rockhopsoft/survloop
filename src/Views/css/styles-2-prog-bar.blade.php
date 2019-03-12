/* generated from resources/views/vendor/survloop/css/styles-2-prog-bar.blade.php */

#progWrap {
    display: block;
	background: {!! $css["color-main-faint"] !!};
}
#progWrap .progress {
    height: 6px;
    -moz-border-radius: 0px; border-radius: 0px;
}

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

#navDesktop {
	display: block;
	margin-top: 20px;
}
#navMobile {
	display: none;
	margin-bottom: 10px;
}



