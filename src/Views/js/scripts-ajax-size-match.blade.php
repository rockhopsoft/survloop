/* generated from resources/views/vendor/survloop/js/scripts-ajax-size-match.blade.php */

function chkMatchCols() {
    if (matchingColRunning) {
        if (window.innerWidth > 992) {
            for (var i = 0; i < matchingColHgtsLg.length; i++) {
                var tallest = 0;
                for (var j = 0; j < matchingColHgtsLg[i].length; j++) {
                    if (document.getElementById(matchingColHgtsLg[i][j]) && tallest < $('#'+matchingColHgtsLg[i][j]+'').height()) {
                        tallest = $('#'+matchingColHgtsLg[i][j]+'').height();
                    }
                }
                var newHgt = ''+Math.round(tallest+40)+'px';
                for (var j = 0; j < matchingColHgtsLg[i].length; j++) {
                    if (document.getElementById(matchingColHgtsLg[i][j])) {
                        document.getElementById(matchingColHgtsLg[i][j]).style.minHeight=newHgt;
                    }
                }
            }
        } else {
            for (var i = 0; i < matchingColHgtsLg.length; i++) {
                for (var j = 0; j < matchingColHgtsLg[i].length; j++) {
                    document.getElementById(matchingColHgtsLg[i][j]).style.minHeight='1px';
                }
            }
        }
    }
    chkMatchColWidths();
    chkStickyFooter();
    return true;
}

function chkMatchColWidths() {
    for (var i = 0; i < 20; i++) {
        if (document.getElementById("fixHead"+i+"") && document.getElementById("fixHeadFixed"+i+"")) {
            var width = $("#fixHead"+i+"").width()-matchingColWidthPadding;
            document.getElementById("fixHeadFixed"+i+"").style.width = width+"px";
        }
    }
    return true;
}

function chkStickyFooter() {
    if (document.getElementById("mainNav") && document.getElementById("footerLinks") && document.getElementById("ajaxWrap")) {
        var headHeight = $("#mainNav").height();
        var footHeight = $("#footerLinks").height();
        var bodyHeight = (window.innerHeight || document.documentElement.clientHeight);
        var newHeight = Math.round(bodyHeight-footHeight-headHeight-stickyFooterExtra);
        document.getElementById("ajaxWrap").style.minHeight=newHeight+"px";
        /*
        if ($("#ajaxWrap").height() <= (newHeight+5)) {
            document.body.style.overflowY = "hidden";
        } else {
            document.body.style.overflowY = "auto";
        }
        */
    }
}

function chkSearchWidth() {
    if (document.getElementById("topNavSearchAbs")) {
        var bodyWidth = (window.innerWidth || document.documentElement.clientWidth);
        var leftWidth = 0;
        if (document.getElementById("leftSide")) {
            leftWidth = $("#leftSide").width();
        }
        var newWidth = Math.round(bodyWidth-leftWidth);
        document.getElementById("topNavSearchAbs").style.width=newWidth+"px";
        document.getElementById("topNavSearchWrap").style.width=newWidth+"px";
        document.getElementById("topNavSearchContain").style.width=newWidth+"px";
    }
}


