/* generated from resources/views/vendor/survloop/css/styles-2-navbar.blade.php */

#mainNav {
	background: {!! $css["color-nav-bg"] !!};
    /* border-bottom: 1px {!! $css["color-line-hr"] !!} solid; */
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
	background: {!! $css["color-nav-bg"] !!};
	margin-left: -1px;
}
#navBurger, a#navBurger:link, a#navBurger:active, a#navBurger:visited, a#navBurger:hover,
#navBurgerClose, a#navBurgerClose:link, a#navBurgerClose:active, a#navBurgerClose:visited, a#navBurgerClose:hover {
    width: 55px;
    height: 39px;
    -moz-border-radius: 4px; border-radius: 4px;
    font-size: 24px;
	padding: 3px;
    margin: 6px 15px 0 0;
    text-align: center;
	color: {!! $css["color-nav-text"] !!};
	border: 1px {!! $css["color-main-grey"] !!} solid;
}
#navBurger .fa.fa-bars, a#navBurger:link .fa.fa-bars, a#navBurger:active .fa.fa-bars, a#navBurger:visited .fa.fa-bars, a#navBurger:hover .fa.fa-bars {
    margin-top: 3px;
    display: block;
}
#navBurgerClose .fa.fa-times, a#navBurgerClose:link .fa.fa-times, a#navBurgerClose:active .fa.fa-times, a#navBurgerClose:visited .fa.fa-times, a#navBurgerClose:hover .fa.fa-times {
    margin-top: 2px;
    display: block;
}
a#navBurger:hover, a#navBurgerClose:hover {
    text-decoration: none;
}
/*
@-moz-document url-prefix() {
    #navBurger, a#navBurger:link, a#navBurger:active, a#navBurger:visited, a#navBurger:hover {
        padding-top: 3px;
    }
    #navBurgerClose, a#navBurgerClose:link, a#navBurgerClose:active, a#navBurgerClose:visited, a#navBurgerClose:hover {
        padding-top: 2px;
    }
}
*/

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
    margin: 7px 0px 0px 16px;
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
    height: 130px;
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
