/* generated from resources/views/vendor/survloop/css/styles-2-navbar.blade.php */

#mainNav {
    position: fixed;
    z-index: 99;
    width: 100%;
    background: {!! $css["color-nav-bg"] !!};
    border-bottom: 1px {!! $css["color-main-grey"] !!} solid;
}
#mainNav, 
#mainNav .col-4, 
#mainNav .col-8, 
.navbar, 
#myNavBar, 
#myNavBar .navbar {
    height: 56px;
	min-height: 56px;
	max-height: 56px;
	padding-top: 1px;
	color: {!! $css["color-nav-text"] !!};
}
.navbar, #myNavBar, #myNavBar .navbar {
    text-align: right;
}

#mainNav2 {
    display: none;
    width: 100%;
    margin-top: -1px;
    background: {!! $css["color-nav-bg"] !!};
}
#headClear {
    clear: both;
	background: {!! $css["color-nav-bg"] !!};
	margin-left: -1px;
}

#topNavSearchBtn {
    display: block;
    margin-left: 30px;
}
#topNavSearch {
    display: none;
    position: relative;
    width: 100%;
    height: 1px;
    max-height: 1px;
    margin-bottom: -1px;
}
#topNavSearchAbs {
    position: absolute;
    height: 49px;
    width: 100%;
    left: 0px;
    top: -1px;
    z-index: 99;
    overflow-x: hidden;
}
#topNavSearchWrap {
    position: fixed;
    height: 49px;
    width: 100%;
    background: {!! $css["color-main-bg"] !!};
    border-bottom: 1px {!! $css["color-main-grey"] !!} solid;
}
#topNavSearchWrap .container-fluid {
    width: 100%;
}
#topNavSearchWrap .container-fluid .row .formIn {
    background: {!! $css["color-main-bg"] !!};
}
#sDataSetID::after, #topNavSearch div div #sDataSetID::after {
    margin-right: 15px;
}
#admSrchFld, #topNavSearch div div #admSrchFld, 
#sDataSetID, #topNavSearch div div #sDataSetID {
    color: {!! $css["color-main-text"] !!};
}
#admSrchFld, #topNavSearch div div #admSrchFld, 
#sDataSetID, #topNavSearch div div #sDataSetID,
#admSrchSubmitBtn, #topNavSearch div div #admSrchSubmitBtn {
    height: 48px;
    border-left: 0px none;
    border-right: 0px none;
    border-bottom: 0px none;
    -moz-border-radius: 0px; border-radius: 0px; 
}
#admSrchSubmitBtn, #topNavSearch div div #admSrchSubmitBtn {
    border: 1px {!! $css["color-main-on"] !!} solid;
}

.headGap {
    display: block;
    width: 100%;
    height: 56px;
	margin-bottom: 0px;
}
.headGap img {
    height: 56px;
    width: 1px;
}
#headBar {
    width: 100%;
    display: none;
	background: {!! $css["color-main-faint"] !!};
}

#mySidenav {
    height: 100%;
    width: 0;
    position: fixed;
    z-index: 99;
    top: 0;
    right: 0;
    border-left: 0px none;
    overflow-x: hidden;
    transition: 0.5s;
	color: {!! $css["color-nav-text"] !!};
    background: {!! $css["color-main-faint"] !!};
    border-left: 0px none;
    box-shadow: none;
}
#mySidenav a {
    padding: 10px 20px;
    text-decoration: none;
    font-size: 18px;
    color: {!! $css["color-main-link"] !!};
    display: block;
    transition: 0.3s
}
#mySidenav a:hover {
	color: {!! $css["color-nav-text"] !!};
	background: {!! $css["color-nav-bg"] !!};
}
@media screen and (max-height: 450px) {
    #mySidenav a {font-size: 18px;}
} 
#mySideUL {
    padding-top: 10px;
}

a.slNavLnk, a.slNavLnk:link, a.slNavLnk:active, 
a.slNavLnk:visited, a.slNavLnk:hover, 
.slNavRight a, 
.slNavRight a.slNavLnk:link, .slNavRight a.slNavLnk:active, 
.slNavRight a.slNavLnk:visited, .slNavRight a.slNavLnk:hover {
    display: block;
    padding: 15px 15px;
    margin-right: 10px;
	color: {!! $css["color-nav-text"] !!};
}

#slNavMain {
    margin-top: -4px;
}
#slNavMain .card-body, #slNavMain div .card .card-body {
    padding: 5px 0px 0px 0px;
}
#slNavMain .card-body, #slNavMain div .card .card-body .list-group {
    margin: 0px;
}
.list-group-item.completed, .list-group-item.completed:hover, 
.list-group-item.completed:focus {
    z-index: 2;
    color: {!! $css["color-main-text"] !!};
    background-color: {!! $css["color-main-faint"] !!};
}


#slLogoWrap {
    display: block;
}
#slLogo {
    display: block;
    margin: 7px 0px 0px 30px;
}
#slLogo.navbar-brand {
    color: {!! $css["color-nav-logo"] !!};
    margin: 0px 0px 0px 20px;
    font-size: 26pt;
}
#slLogoImg, #slLogoImgSm {
    display: inline;
    height: 40px;
    margin-top: 0px;
}
#slLogoImgSm {
    display: none;
}
#logoPrint #slLogoImg {
    height: 50px;
    margin-top: 10px;
}
#slLogo.w100 {
    width: 100%;
    margin-top: 10px;
}
#slLogoImg.w100 {
    height: auto;
    width: 100%;
}
.slPrint #slLogo, .slPrint #slLogo.w100, 
.slPrint #slLogoImg, .slPrint #slLogoImg.w100 {
    height: 100px;
    width: auto;
}

.navbar-brand, a.navbar-brand:link, a.navbar-brand:visited, a.navbar-brand:active, a.navbar-brand:hover {
	font-size: 32pt;
}
#logoTxt {
	padding-left: 10px;
	margin-top: -2px;
}
#headLogoLong img {
    height: 50px;
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

a.navbar-brand:link, a.navbar-brand:visited, 
a.navbar-brand:active, a.navbar-brand:hover {
	color: {!! $css["color-nav-text"] !!};
}

#userMenuBtnWrp {
    position: relative;
    padding: 0px 18px 0px 49px;
}
#userMenuArr, #userMenuBtnWrp #userMenuArr {
    position: absolute;
    top: 3px;
    right: 0px;
}
#userMenuBtnAvatar, #userMenuBtnWrp #userMenuBtnAvatar {
    position: absolute;
    top: -6px;
    left: 2px;
    border: 1px {!! $css["color-main-grey"] !!} solid;
    -moz-border-radius: 19px; border-radius: 19px;
    height: 36px;
    max-height: 36px;
    width: 36px;
    max-width: 36px;
    overflow: hidden;
}
#userMenuBtnAvatar img, 
#userMenuBtnWrp #userMenuBtnAvatar img {
    border: 0px none;
    width: 34px;
    min-width: 34px;
    max-width: 34px;
}
#userMenuBtnName {
    display: inline;
}
