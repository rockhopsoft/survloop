/* generated from resources/views/vendor/survloop/js/scripts.blade.php */

function debugTxt(txt) {
    if (document.getElementById("absDebug")) {
        document.getElementById("absDebug").innerHTML = txt+"<br />"+document.getElementById("absDebug").innerHTML;
    }
    return true;
}

var appUrl = "{{ $GLOBALS['SL']->sysOpts['app-url'] }}";
var defMetaImg = "{{ ((isset($GLOBALS['SL']->sysOpts['meta-img'])) ? $GLOBALS['SL']->sysOpts['meta-img'] : '') }}";

var iOS = !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform);

function findGetParam(paramName) {
    var result = null,
    tmp = [];
    var items = location.search.substr(1).split("&");
    for (var index = 0; index < items.length; index++) {
        tmp = items[index].split("=");
        if (tmp[0] === paramName) result = decodeURIComponent(tmp[1]);
    }
    return result;
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

var currPage = new Array();
function setCurrPage(title, url) {
    currPage[0] = title;
    currPage[1] = url;
    return true;
}

function copyClip(divID) {
    if (document.getElementById(divID)) {
        document.getElementById(divID).select();
        document.execCommand("Copy");
    }
    return true;
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

function ajaxSearchExpandResults() {
	if (document.getElementById("ajaxSearchResults").className=="ajaxSearch") document.getElementById("ajaxSearchResults").className="ajaxSearchExpand";
	else document.getElementById("ajaxSearchResults").className="ajaxSearch";
	return true;
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
    window.location.replace(sURL);
    return false;
}

function getSpinner() {
    return {!! json_encode($GLOBALS["SL"]->spinner()) !!};
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
        document.getElementById('adminMenuTopTabs').style.marginBottom='70px';
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

function openNav() {
    document.getElementById("mySidenav").style.borderLeft = "1px {!! $css["color-main-on"] !!} solid";
    document.getElementById("mySidenav").style.boxShadow = "0px 0px 40px {!! $css["color-main-faint"] !!}";
    document.getElementById("mySidenav").style.width = "300px";
    document.getElementById("main").style.marginRight = "300px";
    document.getElementById("navBurger").style.display = "none";
    document.getElementById("navBurgerClose").style.display = "block";
}
function closeNav() {
    document.getElementById("mySidenav").style.borderLeft = "0px none";
    document.getElementById("mySidenav").style.boxShadow = "none";
    document.getElementById("mySidenav").style.width = "0";
    document.getElementById("main").style.marginRight = "0";
    document.getElementById("navBurger").style.display = "block";
    document.getElementById("navBurgerClose").style.display = "none";
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
            document.getElementById("ajaxWrapLoad").innerHTML += '<center><h3 class="slBlueDark pL15 pR15">'+getNextProTipText()+'</h3></center>';
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

function addTopCust(navCode) {
    if (document.getElementById("myNavBar")) {
        if (document.getElementById("myNavBar").innerHTML.indexOf(navCode) < 0) {
            document.getElementById("myNavBar").innerHTML += navCode;
        }
    }
    return true;
}
function addTopCustRight(navCode) {
    if (document.getElementById("myNavBar")) {
        if (document.getElementById("myNavBar").innerHTML.indexOf(navCode) < 0) {
            var loginPos = document.getElementById("myNavBar").innerHTML.indexOf('fa fa-times');
            if (loginPos > 0) {
                document.getElementById("myNavBar").innerHTML = document.getElementById("myNavBar").innerHTML.substring(0,loginPos+40)+navCode+document.getElementById("myNavBar").innerHTML.substring(loginPos+40);
            } else {
                document.getElementById("myNavBar").innerHTML += navCode;
            }
        }
    }
    return true;
}
function addTopNavItem(navTxt, navLink) {
    if (document.getElementById("myNavBar")) {
        if (navTxt == 'pencil') navTxt = "<i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i>";
        var newLink = "<a class=\"float-right slNavLnk\" href=\""+navLink+"\">"+navTxt+"</a>";
        if (document.getElementById("myNavBar").innerHTML.indexOf(newLink) < 0) {
            if (navTxt == 'pencil') document.getElementById("myNavBar").innerHTML = newLink+document.getElementById("myNavBar").innerHTML;
            else document.getElementById("myNavBar").innerHTML += newLink;
        }
    }
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
function printHeadBar(percIn) {
    if (percIn > 0) {
        progressPerc = percIn;
        if (document.getElementById("progWrap")) document.getElementById("progWrap").innerHTML = getProgBar();
    }
    return true;
}
function getProgBar() {
    return "<div class=\"progress progress-striped progress-bar-animated\"><div class=\"progress-bar bg-striped\" role=\"progressbar\" aria-valuenow=\""+progressPerc+"\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width:"+progressPerc+"%\"><span class=\"sr-only\">"+progressPerc+"% Complete</span></div></div>";
}

@if (isset($treeJs)) {!! $treeJs !!} @endif

@if (isset($jsXtra)) {!! $jsXtra !!} @endif
