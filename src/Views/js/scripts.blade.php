/* generated from resources/views/vendor/survloop/js/scripts.blade.php */

function debugTxt(txt) {
    if (document.getElementById("absDebug")) {
        document.getElementById("absDebug").innerHTML = txt+"<br />"+document.getElementById("absDebug").innerHTML;
    }
    return true;
}

var appUrl = "{{ $GLOBALS['SL']->sysOpts['app-url'] }}";
var appUrlParams = new Array();
var appUrlParamKeys = new Array('frame', 'widget', 'ajax', 'print', 'tree', 'tip', 'sub', 't2', 'resend', 'cid', 'core', 'new', 'start', 'redir', 'sView');
var defMetaImg = "{{ ((isset($GLOBALS['SL']->sysOpts['meta-img'])) 
    ? $GLOBALS['SL']->sysOpts['meta-img'] : '') }}";

var loggedIn = false;
var loggedInAdmin = false;
var loggedInStaff = false;
var loggedInPartner = false;
var loggedInVolun = false;
var pageDynaLoaded = false;
var pageFullLoaded = {};
var pageFadeInDelay = 300;
var pageFadeInSpeed = 1000;

var iOS = !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform);
var fixedHeaderOffset = 0;
var stickyFooterExtra = 0;

function addRefreshParam() {
    var val = findGetParam("refresh");
    if (!val || val.trim() == "") return "";
    return "&refresh="+val.trim();
}
function listUrlParams() {
    var params = "";
    for (var i = 0; i < appUrlParams.length; i++) {
        params += "&"+appUrlParams[i][0]+"="+appUrlParams[i][1];
    }
    return params;
}
function listFullUrlParams() {
    var params = listUrlParams();
    if (params.length > 0) {
        return "?"+params.substing(1);
    }
    return params;
}

function findGetParam(paramName) {
    var result = null;
    tmp = [];
    var items = location.search.substr(1).split("&");
    for (var index = 0; index < items.length; index++) {
        tmp = items[index].split("=");
        if (tmp[0] === paramName) result = decodeURIComponent(tmp[1]);
    }
    return result;
}
function addGetParam(paramName) {
    var val = findGetParam(paramName);
    if (!val || val.trim() == "") return false;
    appUrlParams[appUrlParams.length] = new Array(paramName, val);
    return true;
}
function getGetParam(paramName) {
    var result = null;
    for (var ind = 0; ind < appUrlParams.length; ind++) {
        if (appUrlParams[ind][0] == paramName) result = appUrlParams[ind][1];
    }
    return result;
}
function loadBasicUrlParams() {
    appUrlParams = new Array();
    for (var i = 0; i < appUrlParamKeys.length; i++) {
        addGetParam(appUrlParamKeys[i]);
    }
    return true;
}
setTimeout(function() { loadBasicUrlParams(); }, 10);

// Example:
// const flds = [ 'fldElementID' ];
// const params = convertArrToParams(flds);
function convertArrToParams(flds) {
    var params = "";
    flds.forEach((element) => {
        if (document.getElementById(element) && document.getElementById(element).value) {
            var val = encodeURI(document.getElementById(element).value.trim());
            if (val !== "") {
                var key = element;
                if (key.substring(key.length-2) == "ID") {
                    key = key.substring(0, key.length-2);
                }
                params += "&"+key+"="+val;
            }
        }
    })
    return params;
}
function convertCheckboxArrToParams(flds) {
    var params = "";
    flds.forEach((element) => {
        var cnt = 0;
        var cntFld = element+"CntID";
        if (document.getElementById(cntFld) && document.getElementById(cntFld).value) {
            cnt = parseInt(document.getElementById(cntFld).value);
        }
        if (cnt > 0) {
            var chkVals = "";
            for (var i=0; i < (1+cnt); i++) {
                var chkFld = element+""+cnt+"";
                if (document.getElementById(chkFld) && document.getElementById(chkFld).checked && document.getElementById(chkFld).value) {
                    var val = encodeURI(document.getElementById(chkFld).value.trim());
                    if (val !== "") {
                        chkVals += ","+val;
                    }
                }
            }
            if (chkVals !== "") {
                params += "&"+element+"="+chkVals.substring(1);
            }
        }
    });
    return params;
}

var treeList = new Array();
@forelse ($GLOBALS["SL"]->getTreeList() as $i => $t)
    treeList[{{ $i }}] = [ {{ $t[0] }}, "{{ str_replace('"', '\\"', $t[1]) }}", "{{ $t[2] }}", "{{ $t[3] }}" ];
@empty
@endforelse
function getTreeCoreTbl(treeID) {
    if (treeID > 0) {
        for (var i = 0; i < treeList.length; i++) {
            if (treeList[i][0] == treeID) return treeList[i][3];
        }
    }
    return "";
}

{!! view('vendor.survloop.js.scripts-forms')->render() !!}

function slugOnBlur(obj, dest) {
    if (obj.value.trim() != '' && document.getElementById(dest) && document.getElementById(dest).value.trim() == '') {
        document.getElementById(dest).value = slugify(obj.value);
    }
    return true;
}
function slugify(string) {
  return string.toString().trim().toLowerCase().replace(/\s+/g, "-").replace(/[^\w\-]+/g, "").replace(/\-\-+/g, "-")
    .replace(/^-+/, "").replace(/-+$/, "");
}

var currTreeType = 'Page';
var currTreeNode = 0;
var currPage = new Array('', '');
function setCurrPage(title, url, node) {
    currPage[0] = title;
    currPage[1] = url;
    currTreeNode = node;
    return true;
}

function copyClip(divID) {
    if (document.getElementById(divID)) {
        document.getElementById(divID).select();
        document.execCommand("Copy");
    }
    return true;
}

var currNav2 = "";
var currNav2Pos = new Array( 250, 400, 550 );
function setCurrNav2Pos(lg, md, sm) {
    currNav2Pos = new Array(lg, md, sm);
}

var graphs = new Array();
function addGraph(nIDtxt, baseurl) {
    graphs[graphs.length] = [ txt2nID(nIDtxt), nIDtxt, baseurl ];
    return true;
}
var graphFlds = new Array();
function addGraphFld(nIDtxt, fld, parent) {
    graphFlds[graphFlds.length] = [ txt2nID(nIDtxt), nIDtxt, parent, fld, "" ];
    return true;
}

var dontHideSearch = false;
var dashResultCnt = 0;
var autoRunDashResults = false;

var resultAnimBases = new Array();
function addResultAnimBase(setBase) {
    if (!resultAnimBases.includes(setBase)) {
        resultAnimBases[resultAnimBases.length] = setBase;
    }
}
setTimeout("addResultAnimBase('dashResults')", 1);


function getSpinner() {
    return {!! json_encode($GLOBALS["SL"]->spinner()) !!};
}
function getSpinnerPadded() {
    return '<div class="w100 p30"><center>'+getSpinner()+'</center></div>';
}
function getSpinnerAjaxWrap() {
    return {!! json_encode('<div id="ajaxWrapLoad" class="container">') !!}+getSpinner()+{!! json_encode('</div>') !!};
}
function replaceAjaxWithSpinner() {
    if (document.getElementById("ajaxWrap")) document.getElementById("ajaxWrap").innerHTML=getSpinnerAjaxWrap();
    return true;
}

var slideGals = new Array(); // [3] current slide, [4] number of change requests
function initGalSlider(nIDtxt, kids, style) {
    var found = false;
    for (var i = 0; i < slideGals.length; i++) {
        if (slideGals[i][0] == nIDtxt) found = true;
    }
    if (!found) {
        slideGals[slideGals.length] = new Array(nIDtxt, kids.split(","), JSON.parse(style), 0, 0);
    }
    return true;
}

var anchorOffsetBonus = 0;
function getAnchorOffset() {
    if (anchorOffsetBonus == 0 && document.getElementById("fixedHeader")) anchorOffsetBonus = -80;
    return anchorOffsetBonus;
}
var hshoos = new Array();
var hshooCurr = 0;
function addHshoo(hash) {
    hshoos[hshoos.length] = new Array(hash, 0);
    return true;
}
function getHshooInd(currHash) {
    for (var i = 0; i < hshoos.length; i++) {
        if (hshoos[i][0] == currHash) return i;
    }
    return -1;
}
function hasHshoos() {
    return (hshoos.length > 0);
}
function chkHshooTopTabs() {
    if (hasHshoos() && document.getElementById('slTopTabsWrap') && document.getElementById('adminMenuTopTabs')) {
        document.getElementById('slTopTabsWrap').style.position='fixed';
        //document.getElementById('adminMenuTopTabs').style.marginBottom='70px';
    } else {
        setTimeout("chkHshooTopTabs()", 2000);
    }
    return true;
}
setTimeout("chkHshooTopTabs()", 200);

function isInViewport(node) {
    var rect = node.getBoundingClientRect();
    return (
        (rect.height > 0 || rect.width > 0) && rect.bottom >= 0 && rect.right >= 0 &&
        rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.left <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

var matchingColRunning = false;
var matchingColHgtsLg = new Array();
function addMatchingColsLg(col1, col2) {
    if (document.getElementById(col1) && document.getElementById(col2)) {
        var found = false;
        for (var i = 0; i < matchingColHgtsLg.length; i++) {
            if ((matchingColHgtsLg[i][0] == col1 && matchingColHgtsLg[i][1] == col2) || (matchingColHgtsLg[i][1] == col1 && matchingColHgtsLg[i][0] == col2)) {
                found = true;
            }
        }
        if (!found) {
            matchingColHgtsLg[matchingColHgtsLg.length] = new Array(col1, col2);
            matchingColRunning = true;
        }
    }
    return true;
}

var matchingColWidthPadding = 0;


function setPopDiaTxt(title, desc) {
    return true;
}

function flexAreaAdjust(o) {
    o.style.height = "1px";
    var newH = o.scrollHeight+25;
    o.style.height = (newH)+"px";
}

var cntDownOver = false;
function updateCountdown(divID, cntFrom, inc) {
    if (document.getElementById(divID)) {
        var newCnt = cntFrom-inc;
        if (newCnt >= 0) {
            document.getElementById(divID).innerHTML=newCnt;
            setTimeout("updateCountdown('"+divID+"', "+newCnt+", "+inc+")", (inc*1000));
        }
        if (newCnt <= 0) cntDownOver = true;
    }
    return true;
}
function startCountdown(divID, cntFrom, inc) {
    cntDownOver = false;
    if (document.getElementById(divID)) {
        setTimeout("updateCountdown('"+divID+"', "+cntFrom+", "+inc+")", (inc*1000));
    }
    return true;
}

var colorWht = "{!! $css["color-main-bg"] !!};";
var colorBlk = "{!! $css["font-main"] !!}";
var colorGry = "{!! $css["color-main-grey"] !!}";
var colorFnt = "{!! $css["color-main-faint"] !!}";

function openNav() {
    document.getElementById("mySidenav").style.boxShadow = "0px 2px 4px {!! $css["color-main-grey"] !!}";
    document.getElementById("mySidenav").style.width = "300px";
    /* document.getElementById("main").style.marginRight = "300px"; */
    if (document.getElementById("userMenuBtn") && document.getElementById("userMenuArr")) {
        document.getElementById("userMenuArr").className = "fa fa-caret-up mL3";
    }
    return true;
}
function closeNav() {
    document.getElementById("mySidenav").style.boxShadow = "none";
    document.getElementById("mySidenav").style.width = "0";
    /* document.getElementById("main").style.marginRight = "0"; */
    if (document.getElementById("userMenuBtn") && document.getElementById("userMenuArr")) {
        document.getElementById("userMenuArr").className = "fa fa-caret-down mL3";
    }
    return true;
}
function toggleNav() {
    if (document.getElementById("mySidenav").style.width == "300px") return closeNav();
    return openNav();
}

var currTree = 0;
var progressPerc = 0;
var treeMajorSects = new Array();
var treeMinorSects = new Array();
var treeMajorSectsDisabled = new Array();
var treeProTips = new Array();
var treeProTipsImg = new Array();
var lastProTip = 0;

function unloadTree() {
    treeMajorSects = new Array();
    treeMinorSects = new Array();
    treeMajorSectsDisabled = new Array();
    treeProTips = new Array();
    treeProTipsImg = new Array();
    return true;
}

function getNextProTipText() {
    lastProTip++;
    if (treeProTips.length <= lastProTip) {
        lastProTip = 0;
    }
    return treeProTips[lastProTip].trim();
}
function getProTipImg() {
    return treeProTipsImg[lastProTip].trim();
}
function addProTipToAjax() {
    if (treeProTips.length > 0) {
        if (document.getElementById("ajaxWrapLoad")) {
            document.getElementById("ajaxWrapLoad").innerHTML += '<div class="proTip"><h4 class="slBlueDark">'+getNextProTipText()+'</h4></div>';
            var img = getProTipImg();
            if (img.length > 0) {
                document.getElementById("ajaxWrapLoad").innerHTML += '<center><img src="'+img+'" border=0 class="mT20" style="min-width: 200px;" ></center>';
            }
            logLastProTip();
        }
    }
    return true;
}
function logLastProTip() {
    if (document.getElementById('hidFrameID')) {
        document.getElementById('hidFrameID').src='/ajax/log-pro-tip?tree='+currTree+'&tip='+lastProTip+'';
    }
    return true;
}

function addImgPreload(src) {
    if (document.getElementById("imgPreloadID")) {
        document.getElementById("imgPreloadID").innerHTML += '<img src="'+src+'" alt="" border=0 >';
    }
    return true;
}


function addTopCust(navCode) {
    if (document.getElementById("myNavBar")) {
        if (document.getElementById("myNavBar").innerHTML.indexOf(navCode) < 0 && document.getElementById("myNavBar").innerHTML.indexOf(navCode.replace("fa-caret-down", "fa-caret-up")) < 0) {
            document.getElementById("myNavBar").innerHTML += navCode;
        }
    }
    return true;
}
function addTopCustRight(navCode) {
    if (document.getElementById("myNavBar")) {
        if (document.getElementById("myNavBar").innerHTML.indexOf(navCode) < 0) {
            document.getElementById("myNavBar").innerHTML = navCode+document.getElementById("myNavBar").innerHTML;
        }
    }
    return true;
}
function addTopNavItem(navTxt, navLink) {
    if (document.getElementById("myNavBar")) {
        var newLink = "<a class=\"fR slNavLnk\" href=\""+navLink+"\">"+navTxt+"</a>";
        if (document.getElementById("myNavBar").innerHTML.indexOf(newLink) < 0) {
            if (navTxt == 'pencil') document.getElementById("myNavBar").innerHTML = newLink+document.getElementById("myNavBar").innerHTML;
            else document.getElementById("myNavBar").innerHTML += newLink;
        }
    }
    return true;
}
var userAvatar = "{{ ((isset($GLOBALS['SL']->sysOpts['has-avatars'])) 
    ? trim($GLOBALS['SL']->sysOpts['has-avatars']) : '') }}";

function tweakNavLink(posA, posB, posC, link) {
    if (posA > 0 && posB == 0 && posC == 0) {
        if (document.getElementById("hidivBtnAdminNav"+posA+"")) {
            document.getElementById("hidivBtnAdminNav"+posA+"").href=link;
        }
    } else if (posA > 0 && posB > 0 && posC == 0) {
        if (document.getElementById("admMenu2Link"+posA+"j"+posB+"")) {
            document.getElementById("admMenu2Link"+posA+"j"+posB+"").href=link;
        }
    }
    return true;
}        

function addTopUserBurger(username) {
    if (!document.getElementById("myNavBar") || document.getElementById("myNavBar").innerHTML.indexOf("userMenuBtn") >= 0) {
        return false;
    }
    var navCode = "<a id=\"userMenuBtn\" class=\"float-right slNavLnk\" href=\"javascript:;\"><div id=\"userMenuBtnWrp\">";
    if (userAvatar.trim() != "") {
        navCode += "<div id=\"userMenuBtnAvatar\"><img src=\"/img/user/"+username+".jpg\" border=0 ></div>";
    }
    navCode += "<div id=\"userMenuBtnName\">"+username+"</div> <i id=\"userMenuArr\" class=\"fa fa-caret-down\" aria-hidden=\"true\"></i></div></a>";
    addTopCust(navCode);
    return true;
}
function addSideNavItem(navTxt, navLink) {
    if (document.getElementById("mySideUL")) {
        var newLink = "<li class=\"nav-item\"><a href=\""+navLink+"\">"+navTxt+"</a></li>";
        if (document.getElementById("mySideUL").innerHTML.indexOf(newLink) < 0) {
            document.getElementById("mySideUL").innerHTML = newLink+document.getElementById("mySideUL").innerHTML;
        }
    }
    return true;
}
var headProgBarPerc = 0;
function printHeadBar(percIn) {
    headProgBarPerc = percIn;
}

function getStateList() {
    return new Array({!! $GLOBALS["SL"]->states->printAllAbbrs() !!});
}

var openAdmMenuOnLoad = true;
var admMenuCollapses = 0;

var sView = 'list';
var resultLoaded = 0;
var dashHeight = 0;
var currRightPane = 'preview';
function updateRightPane() {
    if (document.getElementById("searchFilts")) {
        if (currRightPane == 'filters') {
            document.getElementById("searchFilts").style.display="block";
        } else {
            document.getElementById("searchFilts").style.display="none";
        }
    }
    if (document.getElementById("toggleSearchFiltsArr")) {
        if (currRightPane == 'filters') {
            document.getElementById("toggleSearchFiltsArr").className="fa fa-caret-up";
        } else {
            document.getElementById("toggleSearchFiltsArr").className="fa fa-caret-down";
        }
    }
    if (document.getElementById("reportAdmPreview") && document.getElementById("hidivDashTools")) {
        if (currRightPane == 'preview') {
            document.getElementById("reportAdmPreview").style.display="block";
            document.getElementById("hidivDashTools").style.display="none";
        } else {
            document.getElementById("reportAdmPreview").style.display="none";
            document.getElementById("hidivDashTools").style.display="block";
        }
    }
    return true;
}


var specialNodes = new Array();
function addSpecialNodeNYC(cityNID, stateNID, nycNID) {
    specialNodes[specialNodes.length] = new Array('nyc', cityNID, stateNID, nycNID);
    return true;
}


@if (isset($treeJs)) {!! $treeJs !!} @endif

@if (isset($jsXtra)) {!! $jsXtra !!} @endif

@if (isset($GLOBALS['SL']->sysOpts['sys-cust-js']))
    {!! $GLOBALS['SL']->sysOpts['sys-cust-js'] !!}
@endif
