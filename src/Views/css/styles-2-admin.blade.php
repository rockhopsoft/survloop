/* generated from resources/views/vendor/survloop/css/styles-2-admin.blade.php */

.admMenu a:link, .admMenu a:visited, .admMenu a:active, .admMenu a:link {
    display: block;
}
.admMenu .admMenuTier1 a:link, .admMenu .admMenuTier1 a:visited, .admMenu .admMenuTier1 a:active, .admMenu .admMenuTier1 a:hover {
    color: {!! $css["color-main-faint"] !!};
    background: {!! $css["color-main-text"] !!};
    padding: 16px 5px 17px 9px;
    font-weight: bold;
    min-height: 56px;
}
.admMenu a.active:link, .admMenu a.active:visited, .admMenu a.active:active, .admMenu a.active:hover,
.admMenu div a.active:link, .admMenu div a.active:visited, .admMenu div a.active:active, .admMenu div a.active:hover {
    color: {!! $css["color-main-faint"] !!};
    background: {!! $css["color-main-link"] !!};
    padding: 16px 5px 17px 9px;
    font-weight: bold;
}
.admMenuIco, .admMenu .admMenuIco, .admMenu div a .admMenuIco, .admMenu div a:link .admMenuIco, .admMenu div a:visited .admMenuIco, .admMenu div a:active .admMenuIco, .admMenu div a:hover .admMenuIco {
    display: inline;
}
.admMenuIco.pull-left, .admMenu .admMenuIco.pull-left, .admMenu div a .admMenuIco.pull-left, 
.admMenu div a:link .admMenuIco.pull-left, .admMenu div a:visited .admMenuIco.pull-left, .admMenu div a:active .admMenuIco.pull-left, .admMenu div a:hover .admMenuIco.pull-left {
    display: block;
    width: 40px;
    margin: -3px 0px 0px 12px;
    font-size: 120%;
}
.admMenuTier2 {
    height: 42px;
    max-height: 42px;
}
.admMenuTier2 a:link, .admMenuTier2 a:visited, .admMenuTier2 a:active, .admMenuTier2 a:hover,
.admMenu .admMenuTier2 a:link, .admMenu .admMenuTier2 a:visited, .admMenu .admMenuTier2 a:active, .admMenu .admMenuTier2 a:hover {
    padding: 10px 5px 10px 62px;
    font-weight: normal;
}
a.tier2active:link, a.tier2active:visited, a.tier2active:active, a.tier2active:hover,
.admMenuTier2 a.tier2active:link, .admMenuTier2 a.tier2active:visited, .admMenuTier2 a.tier2active:active, .admMenuTier2 a.tier2active:hover {
    color: {!! $css["color-main-link"] !!};
    background: {!! $css["color-main-faint"] !!};
    height: 33px;
    max-height: 33px;
}

#slTopTabsWrap {
    position: static;
    z-index: 100;
}
.slTopTabs {
    width: 100%;
    padding-top: 7px;
    margin-bottom: 25px;
    background: {!! $css["color-main-text"] !!};
}
.slTopTabs ul.nav.nav-tabs {
    width: 100%;
    border: 0px none;
    border-bottom: 1px {!! $css["color-main-faint"] !!} solid;
}
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link:link,
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link:visited,
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link:active,
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link:hover {
    color: {!! $css["color-main-faint"] !!};
    background: {!! $css["color-main-on"] !!};
    border: 0px none;
    border-bottom: 1px {!! $css["color-main-faint"] !!} solid;
    margin-right: 7px;
}
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link.active:link,
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link.active:visited,
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link.active:active,
.slTopTabs ul.nav.nav-tabs li.nav-item a.nav-link.active:hover {
    color: {!! $css["color-main-link"] !!};
    background: {!! $css["color-main-faint"] !!};
    border: 0px none;
}
.slTopTabsSub {
    margin-top: -20px;
}
.slTopTabsSub ul.nav {
    
}

.adminFootBuff {
	clear: both;
	width: 100%;
	height: 80px;
}
#hidivBtnAdmFoot, #hidivAdmFoot {
    color: {!! $css["color-main-grey"] !!};
}
#hidivBtnAdmFoot {
	padding: 20px 0px 20px 0px;
	margin-bottom: 10px;
}
#hidivAdmFoot {
	padding: 20px 0px 40px 0px;
}

#menuColpsWrap {
    margin: 15px -15px 0px 0px;
}
.leftSide, .leftSideCollapse {
    width: 240px;
    height: 100%;
    vertical-align: top;
    overflow-x: visible;
    overflow-y: hidden;
    color: {!! $css["color-main-faint"] !!};
    background: {!! $css["color-main-text"] !!};
}
#leftSideWdth, .leftSide #leftSideWdth {
    width: 240px;
}
#leftSideWrap, .leftSide #leftSideWrap {
    position: fixed;
    width: 240px;
	z-index: 0;
}
#leftAdmMenu {
    display: block;
    width: 100%;
}
.leftSideCollapse, .leftSideCollapse #leftSideWdth, .leftSideCollapse #leftSideWrap {
    width: 60px;
    overflow-x: hidden;
}
.admMenuLbl {
    display: inline;
}
.leftSideCollapse #leftSideWrap #leftAdmMenu .admMenu #admMenu .admMenuTier1 .admMenuLbl {
    display: none;
}

#mainBody {
    padding: 15px 0px;
    vertical-align: top;
    background: {!! $css["color-main-bg"] !!};
	z-index: 100;
}
#mainBody.mainBodyDash {
    background: {!! $css["color-main-faint"] !!};
    padding: 0px;
}
body.bodyDash {
    background: {!! $css["color-main-text"] !!};
}

#dashSearchFrmWrap {
    position: relative;
    width: 100%;
    padding: 8px 15px 7px 15px;
}
#topNavSearch {
    

}
#admSrchFld {
    background: none; 
    background-color: none;
    z-index: 1;
}
#admSrchFld, #admSrchFld a:link, #admSrchFld a:visited, #admSrchFld a:active, #admSrchFld a:hover {
    color: {!! $css["color-main-bg"] !!};
}
#admSrchFld::placeholder, #admSrchFld:-ms-input-placeholder, #admSrchFld::-ms-input-placeholder {
    color: {!! $css["color-main-bg"] !!};
}

#dashSearchBtnID {
    position: absolute;
    z-index: 99;
    top: 10px;
    right: 24px;
}
#dashSearchBtnID a:link, #dashSearchBtnID a:active, #dashSearchBtnID a:visited, #dashSearchBtnID a:hover {
    color: {!! $css["color-main-bg"] !!};
    font-size: 14px;
}
#dashSearchBtnID a:hover {
    font-size: 16px;
}