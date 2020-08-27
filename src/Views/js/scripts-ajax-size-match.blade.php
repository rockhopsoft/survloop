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
    return true;
}

function chkMatchColWidths(timeout) {
    for (var i = 0; i < 20; i++) {
        if (document.getElementById("fixHead"+i+"") && document.getElementById("fixHeadFixed"+i+"")) {
            var width = $("#fixHead"+i+"").width()-matchingColWidthPadding;
            document.getElementById("fixHeadFixed"+i+"").style.width = width+"px";
        }
    }
    return true;
}