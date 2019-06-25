/* generated from resources/views/vendor/survloop/css/styles-2-navbar.blade.php */

#mainNav {
    position: fixed;
    z-index: 99;
    width: 100%;
	background: {!! $css["color-nav-bg"] !!};
}
#mainNav, #mainNav .col-4, #mainNav .col-8, .navbar, #myNavBar, #myNavBar .navbar {
    height: 56px;
	min-height: 56px;
	max-height: 56px;
	padding-top: 1px;
	overflow: hidden;
	color: {!! $css["color-nav-text"] !!};
}
.navbar, #myNavBar, #myNavBar .navbar {
    text-align: right;
}
#headClear {
    clear: both;
	background: {!! $css["color-nav-bg"] !!};
	margin-left: -1px;
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
    z-index: 1;
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

a.slNavLnk, a.slNavLnk:link, a.slNavLnk:active, a.slNavLnk:visited, a.slNavLnk:hover, 
.slNavRight a, .slNavRight a.slNavLnk:link, .slNavRight a.slNavLnk:active, .slNavRight a.slNavLnk:visited, .slNavRight a.slNavLnk:hover {
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
.list-group-item.completed, .list-group-item.completed:hover, .list-group-item.completed:focus {
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
.slPrint #slLogo, .slPrint #slLogo.w100, .slPrint #slLogoImg, .slPrint #slLogoImg.w100 {
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

a.navbar-brand:link, a.navbar-brand:visited, a.navbar-brand:active, a.navbar-brand:hover {
	color: {!! $css["color-nav-text"] !!};
}
