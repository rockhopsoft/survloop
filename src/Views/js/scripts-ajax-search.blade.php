/* generated from resources/views/vendor/survloop/js/scripts-ajax-search.blade.php */

function loadDashResults() {
    var resultsDivID = "dashResultsWrap";
    if (document.getElementById("sResultsDivID") && document.getElementById("sResultsDivID").value) {
        resultsDivID = document.getElementById("sResultsDivID").value.trim();
    }
    var hasResultsDiv = false;
    if (resultsDivID.length > 0 && document.getElementById(resultsDivID)) {
        hasResultsDiv = true;
    }
    var resultsUrl = "?ajax=1&dashResults=1";
    if (document.getElementById("sResultsUrlID") && document.getElementById("sResultsUrlID").value) {
        resultsUrl = document.getElementById("sResultsUrlID").value.trim();
    }
    if (!hasResultsDiv && resultsUrl == "?ajax=1&dashResults=1") {
        if (loggedInAdmin) resultsUrl = "/dash/search?run=1";
        else if (loggedInStaff) resultsUrl = "/dash/staff-search?run=1";
        else if (loggedInPartner) resultsUrl = "/dash/partner-search?run=1";
        else resultsUrl = "/search?run=1";
    }
    if (document.getElementById("admSrchFld") && document.getElementById("admSrchFld").value && document.getElementById("admSrchFld").value.trim() != "") {
        resultsUrl += "&s="+encodeURI(document.getElementById("admSrchFld").value.trim());
    }
    var paramFlds = [ 'sFiltID', 'sSortID', 'sSortDirID', 'sViewID' ];
    resultsUrl += convertArrToParams(paramFlds);
    paramFlds = [ 'sDataSet' ];
    resultsUrl += convertCheckboxArrToParams(paramFlds);
    if (hasResultsDiv) {
        console.log(resultsDivID+": "+resultsUrl);
        document.getElementById(resultsDivID).innerHTML=getSpinner();
        $("#"+resultsDivID).load(resultsUrl);
    } else {
        console.log("window.location = "+resultsUrl);
        window.location=resultsUrl;
    }
    return false;
}

function chkAutoLoadDashResults() {
    if (autoRunDashResults) {
        autoRunDashResults = false;
        console.log("autoRunDashResults");
        loadDashResults();
        return true;
    }
    return false;
}

function runSearch(nID, treeID) {
    var sURL = "/search?t="+treeID;
    @if (isset($jqueryXtraSrch)) {!! $jqueryXtraSrch !!} @endif
    if (document.getElementById("searchBar"+nID+"t"+treeID)) {
        sURL += "&s="+encodeURIComponent(document.getElementById("searchBar"+nID+"t"+treeID).value);
    }
    if (document.getElementById("advUrlID")) {
        sURL += document.getElementById("advUrlID").value;
    }

    if (multiSearchDataSetsChecked()) {
console.log("multiSearchDataSetsChecked: "+sURL);
        //window.location.replace(sURL);
    } else if (!didSearchDataSetChange() || !tryAjaxDashResults()) {
        //setTimeout(function() { loadDashResults(); }, 500);
console.log("NOT didSearchDataSetChange: "+sURL);
        //window.location.replace(sURL);
    }
    return false;
}

$(document).on("keyup", "#admSrchFld", function(e) {
    if (e.keyCode == 13) {
        e.preventDefault();
        setTimeout(function() { loadDashResults(); }, 500);
    }
});

function ajaxSearchExpandResults() {
    if (document.getElementById("ajaxSearchResults").className=="ajaxSearch") document.getElementById("ajaxSearchResults").className="ajaxSearchExpand";
    else document.getElementById("ajaxSearchResults").className="ajaxSearch";
    return true;
}

function chkSearchOnLoad() {
    if (document.getElementById("topNavSearch") && document.getElementById("admSrchFld") && document.getElementById("admSrchFld").value && document.getElementById("admSrchFld").value.trim() != '') {
        //document.getElementById("topNavSearchBtn").style.display = 'none';
        document.getElementById("topNavSearch").style.display = 'block';
    }
    return true;
}
setTimeout(function() { chkSearchOnLoad(); }, 10);
$(document).on("click", "#topNavSearchBtn", function() {
    $("#topNavSearchBtn").fadeOut(25);
    setTimeout(function() { $("#topNavSearch").fadeIn(25); }, 26);
    setTimeout(function() { $("#admSrchFld").focus(); }, 52);
    if (document.getElementById("userMenuBtnName")) {
        document.getElementById("userMenuBtnName").style.display="none";
    }
});
$(document).on("focusin", "#admSrchFld", function() {
    if (document.getElementById("topNavSearch")) {
        document.getElementById("topNavSearch").className="fL topNavSearchActive";
    }
});
$(document).on("focusout", "#admSrchFld", function() {
    setTimeout(function() { hideAdvSeach(); }, 100);
});
function hideAdvSeach() {
    if (!dontHideSearch) {
        if (document.getElementById("topNavSearch")) {
            document.getElementById("topNavSearch").className="fL topNavSearch";
        }
        setTimeout(function() { chkHideAdvSeach(); }, 20);
    }
    return true;
}
function chkHideAdvSeach() {
    if (!dontHideSearch && document.getElementById("hidivSearchOpts") && document.getElementById("topNavSearch") && document.getElementById("topNavSearch").className=="fL topNavSearch" && document.getElementById("hidivSearchOpts").style.display=="block") {
        $("#hidivSearchOpts").slideUp("fast");
        document.getElementById("hidivBtnArrSearchOpts").className="fa fa-caret-down";
    }
    return true;
}
function pauseHideAdvSearch() {
    $("#admSrchFld").focus();
    dontHideSearch = true;
    setTimeout(function() { dontHideSearch = false; }, 300);
}
$(document).on("focusin", "#hidivBtnSearchOpts", function() {
    pauseHideAdvSearch();
});
$(document).on("focusin", "#hidivSearchOpts", function() {
    pauseHideAdvSearch();
});
$(document).on("focusin", "#hidivSearchOpts a", function() {
    pauseHideAdvSearch();
});
$(document).on("click", ".srchBarParts", function() {
    pauseHideAdvSearch();
});
$(document).on("focusin", "input.srchBarParts", function() {
    pauseHideAdvSearch();
});
$(document).on("click", ".updateSearchFilts", function() {
    setTimeout(function() { loadDashResults(); }, 750);
});

$(document).on("click", "#toggleSearchFilts", function() {
    if (document.getElementById("searchFilts")) {
        if (document.getElementById("searchFilts").style.display!="block") {
            currRightPane = 'filters';
        } else {
            currRightPane = 'preview';
        }
    }
    updateRightPane();
});

$(document).on("click", "#hidivBtnDashViewLrg", function() {
    if (sView != 'lrg') {
        document.getElementById("sViewID").value='lrg';
        setTimeout(function() { loadDashResults(); }, 500);
    }
});
$(document).on("click", "#hidivBtnDashViewList", function() {
    if (sView != 'list') {
        document.getElementById("sViewID").value='list';
        setTimeout(function() { loadDashResults(); }, 500);
    }
});

$(document).on("click", ".fltSortTypeBtn", function() {
    var srtType = $(this).attr("data-sort-type");
    if (document.getElementById("sSortID") && srtType) {
        document.getElementById("sSortID").value=srtType;
        var srtDir = $(this).attr("data-sort-dir");
        if (document.getElementById("sSortDirID") && srtDir) {
            document.getElementById("sSortDirID").value=srtDir;
        }
        setTimeout(function() { loadDashResults(); }, 500);
    }
});
$(document).on("click", ".fltSortDirBtn", function() {
    var srtDir = $(this).attr("data-sort-dir");
    if (document.getElementById("sSortDirID") && srtDir) {
        document.getElementById("sSortDirID").value=srtDir;
        setTimeout(function() { loadDashResults(); }, 500);
    }
});

$(document).on("click", ".searchBarBtn", function() {
    var nID = $(this).attr("id").replace("searchTxt", "").split("t");
    return runSearch(nID[0], nID[1]);
});
$(document).on("keyup", ".searchBar", function(e) {
    if (e.keyCode == 13) {
        e.preventDefault();
        var nID = $(this).attr("id").replace("searchBar", "").split("t");
        return runSearch(nID[0], nID[1]);
    }
});

function chkDashHeight() {
    dashHeight = document.body.clientHeight;
    var newHeight = dashHeight-130;
    if (document.getElementById("dashResultsWrap")) {
        document.getElementById("dashResultsWrap").style.height=''+newHeight+'px';
    }
    if (document.getElementById("reportAdmPreview")) {
        document.getElementById("reportAdmPreview").style.height=''+newHeight+'px';
    }
    if (document.getElementById("hidivDashTools")) {
        document.getElementById("hidivDashTools").style.height=''+newHeight+'px';
    }
    if (document.getElementById("reportAdmPreviewFull")) {
        newHeight += 60;
        document.getElementById("reportAdmPreviewFull").style.height=''+newHeight+'px';
    }
    setTimeout(function() { chkDashHeight(); }, 5000);
    return true;
}
setTimeout(function() { chkDashHeight(); }, 1);


function animRevealDashResultsClose(setBase) {
    if (document.getElementById(setBase+"Spin") && document.getElementById(setBase+"Spin").style.display.localeCompare("none") !== 0) {
        $("#"+setBase+"Spin").fadeOut(300);
    }
}
function animRevealDashResultRow(setBase, cnt, limit, delay) {
    var divID = setBase+"Anim"+cnt+"";
    if (document.getElementById(divID) && document.getElementById(divID).style.display.localeCompare("none") == 0) {
        $("#"+divID+"").fadeIn(300);
        cnt++;
        if (cnt < limit) {
            divID = setBase+"Anim"+cnt+"";
            delay *= 0.7;
            if (delay < 10) delay = 10;
            setTimeout(function() { animRevealDashResultRow(setBase, cnt, limit, delay); }, (1+delay));
        } else {
            animRevealDashResultsClose(setBase);
        }
    } else {
        animRevealDashResultsClose(setBase);
    }
}
function animRevealDashResults(setBase) {
    if (document.getElementById(setBase+"None")) {
        animRevealDashResultsClose(setBase);
        return false;
    }
    if (document.getElementById(setBase+"Anim0") && document.getElementById(setBase+"Anim0").style.display.localeCompare("none") == 0) {
        var limit = 1000;
        var delay = 300;
        if (document.getElementById(setBase+"Head") && document.getElementById(setBase+"Head").style.display.localeCompare("none") == 0) {
            $("#"+setBase+"Head").fadeIn(300);
        }
        setTimeout(function() { animRevealDashResultRow(setBase, 0, limit, delay); }, (1+delay));
        return true;
    }
    return false;
}
function chkAnimRevealDashResults(setBase) {
    if (setBase === undefined || !setBase || (!document.getElementById(setBase+"Anim0") && !document.getElementById(setBase+"None"))) {
        return false;
    }
    animRevealDashResults(setBase);
    setTimeout(function() { chkAnimRevealDashResults(setBase); }, 500);
    return true;
}
function chkEachAnimRevealResult(item, ind) {
    setTimeout(function() { chkAnimRevealDashResults(item); }, ((1+ind)*100));
}
function chkAnyAnimRevealResults() {
    resultAnimBases.forEach(chkEachAnimRevealResult);
}
