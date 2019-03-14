/* generated from resources/views/vendor/survloop/css/styles-2-debug.blade.php */

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