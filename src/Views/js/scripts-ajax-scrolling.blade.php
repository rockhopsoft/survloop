/* generated from resources/views/vendor/survloop/js/scripts-ajax-srolling.blade.php */

function slideToHshooPos(hash) {
    if (document.getElementById(hash)) {
        var newTop = (1+getAnchorOffset()+$("#"+hash+"").offset().top);
        $('html, body').animate({ scrollTop: newTop }, 800, 'swing', function(){ });
    }
    return true;
}

$(".hsho").on('click', function(event) {
    var hash = $(this).attr("data-hash").trim();
    if (hash !== "") slideToHshooPos(hash);
    return false;
});

$(".hshoo").on('click', function(event) {
    event.preventDefault();
    var hash = '';
    if ($(this).attr("id") && $(this).attr("id").substring(0, 6) == 'admLnk') {
        hash = '#'+$(this).attr("id").substring(6);
    } else if (this.hash !== "") {
        hash = this.hash;
    }
    var hashDivID = hash.replace('#', '');
    if (document.getElementById(hashDivID)) {
        setTimeout(function() { slideToHshooPos(hashDivID); }, 100);
        hshooCurr = getHshooInd(hash);
        setTimeout(function() { updateHshooActive(hshooCurr); }, 1000);
    }
    return false;
});

function chkHshoosPos() {
    for (var i = 0; i < hshoos.length; i++) {
        var divID = hshoos[i][0].replace('#', '');
        if (divID != "" && document.getElementById(divID)) {
            hshoos[i][1] = $(hshoos[i][0]).offset().top;
            var admLnk = "admLnk"+divID+"";
            if (document.getElementById(admLnk)) {
                if (document.getElementById(admLnk).className.indexOf("active") !== false) {
                    hshooCurr = i;
                }
            }
        }
    }
    var absMin = -1000000000;
    var newArr = new Array();
    for (var i = 0; i < hshoos.length; i++) {
        var min = new Array(0, 1000000000);
        for (var j = 0; j < hshoos.length; j++) {
            if (absMin < hshoos[j][1] && hshoos[j][1] < min[1]) min = new Array(j, hshoos[j][1]);
        }
        if (min[1] > -1000000000) {
            absMin = min[1];
            newArr[newArr.length] = new Array(hshoos[min[0]][0], hshoos[min[0]][1]);
        }
    }
    hshoos = newArr;
    setTimeout(function() { chkHshoosPos(); }, 5000);
    return true;
}

function updateHshooActive(hshooCurr) {
    for (var i = 0; i < hshoos.length; i++) {
        var admLnk = "admLnk"+hshoos[i][0].replace('#', '');
        if (document.getElementById(admLnk)) {
            if (i == hshooCurr) $("#"+admLnk+"").addClass('active');
            else $("#"+admLnk+"").removeClass('active');
        }
    }
    return true;
}

function getCurrScroll() {
    return Math.ceil($(document).scrollTop())-getAnchorOffset();
}

function chkHshooScroll(currScroll) {
    if (hshoos.length > 0) {
        hshooCurr = -1;
        var compareScroll = currScroll+2;
        for (var i = 0; i < hshoos.length; i++) {
            if (hshoos[i][1] <= compareScroll) hshooCurr = i;
        }
        if (hshooCurr < 0) hshooCurr = 0;
        updateHshooActive(hshooCurr);
    }
    return true;
}
setTimeout(function() { chkHshoosPos(); }, 10);
setTimeout(function() { chkHshooScroll(getCurrScroll()); }, 50);
