/* generated from resources/views/vendor/survloop/css/styles-2-responsive.blade.php */

@media screen and (max-width: 1200px) {
    
}
@media screen and (max-width: 992px) {

/*    
	#navDesktop {
        display: none;
    }
	#navMobile {
        display: block;
    }
*/
	
    a#menuColpsBtn:link, a#menuColpsBtn:visited, 
    a#menuColpsBtn:active, a#menuColpsBtn:hover {
        display: none;
    }
    a#menuUnColpsBtn:link, a#menuUnColpsBtn:visited, 
    a#menuUnColpsBtn:active, a#menuUnColpsBtn:hover {
	    display: block;
	}
    #leftSideWdth {
        width: 24px;
    }
    #leftSideWrap {
        width: 24px;
    }
    #mainBody {
        padding: 0px;
    }
    @media screen and (max-height: 650px) {
        #leftSideWrap { position: static; }
    }
    
}
@media screen and (max-width: 768px) {

    @if (isset($GLOBALS['SL']->sysOpts['logo-img-sm']) 
        && trim($GLOBALS['SL']->sysOpts['logo-img-sm']) != ''
        && trim($GLOBALS['SL']->sysOpts['logo-img-sm']) 
            != trim($GLOBALS['SL']->sysOpts['logo-img-lrg']))
        #slLogoImgSm { display: inline; }
        #slLogoImg { display: none; }
    @endif
    #userMenuBtnName {
        display: none;
    }
    a.slNavLnk, a.slNavLnk:link, a.slNavLnk:active, 
    a.slNavLnk:visited, a.slNavLnk:hover, 
    .slNavRight a, 
    .slNavRight a.slNavLnk:link, .slNavRight a.slNavLnk:active, 
    .slNavRight a.slNavLnk:visited, .slNavRight a.slNavLnk:hover {
        padding: 15px 5px;
    }
    #userMenuBtnAvatar, #userMenuBtnWrp #userMenuBtnAvatar {
        left: 10px;
    }

    #admSrchFld, #topNavSearch div div #admSrchFld, 
    #sDataSetID, #topNavSearch div div #sDataSetID,
    #admSrchFld.form-control-lg, #topNavSearch div div #admSrchFld.form-control-lg, 
    #sDataSetID.form-control-lg, #topNavSearch div div #sDataSetID.form-control-lg {
        font-size: 1rem;
    }

	input.nFormBtnSub, input.nFormBtnBack {
        font-size: 20pt;
    }
    #slLogo {
        margin: 7px 0px 0px 12px;
    }
	#logoTxt {
        padding-left: 0px;
        margin-top: -2px;
        margin-left: -5px;
    }
    a.slNavLnk, a.slNavLnk:link, a.slNavLnk:active, 
    a.slNavLnk:visited, a.slNavLnk:hover, 
    .slNavRight a, 
    .slNavRight a.slNavLnk:link, .slNavRight a.slNavLnk:active, 
    .slNavRight a.slNavLnk:visited, .slNavRight a.slNavLnk:hover {
        margin-right: 7px;
    }
	#formErrorMsg h1, #formErrorMsg h2, #formErrorMsg h3 {
        font-size: 18pt;
    }
	.nodeWrap .jumbotron, .nPrompt .jumbotron {
        padding: 30px 20px 30px 20px;
    }
    input.otherGender {
        width: 240px;
    }
    table.slSpreadTbl tr td.sprdFld input.form-control-lg,
    table.slSpreadTbl tr td.sprdFld select.form-control-lg {
        padding: 5px;
    }
    table.slSpreadTbl tr td.sprdRowLab, table.slSpreadTbl tr th.sprdRowLab, 
        table.slSpreadTbl tr th, .nFld table.slSpreadTbl tr th { 
        font-size: 14px; 
    }
    input.otherFld, input.form-control.otherFld, 
    label input.otherFld, label input.form-control.otherFld {
        width: 270px;
    }
    .glossaryList .col-10 { padding-top: 0px; margin-top: -5px; }

}
@media screen and (max-width: 480px) {
    
	#logoTxt {
	    font-size: 28pt; 
	    padding-left: 0px;
	    margin-top: -9px 0px -9px -5px;
	}

    a.slNavLnk, a.slNavLnk:link, a.slNavLnk:active, 
    a.slNavLnk:visited, a.slNavLnk:hover, 
    .slNavRight a, 
    .slNavRight a.slNavLnk:link, .slNavRight a.slNavLnk:active, 
    .slNavRight a.slNavLnk:visited, .slNavRight a.slNavLnk:hover {
        padding: 15px 5px 15px 5px;
        margin-right: 5px;
    }
    
    table.slAdmTable tr td, table.slAdmTable tr th, 
    table.slAdmTable tr td a:link, table.slAdmTable tr td a:active, 
    table.slAdmTable tr td a:visited, table.slAdmTable tr td a:hover,
    table.slAdmTable tr th a:link, table.slAdmTable tr th a:active, 
    table.slAdmTable tr th a:visited, table.slAdmTable tr th a:hover {
        font-size: 10pt;
    }
    table.slAdmTable tr td.fPerc133, 
    table.slAdmTable tr th.fPerc133, 
    table.slAdmTable tr td a.fPerc133:link, 
    table.slAdmTable tr td a.fPerc133:active, 
    table.slAdmTable tr td a.fPerc133:visited, 
    table.slAdmTable tr td a.fPerc133:hover, 
    table.slAdmTable tr th a.fPerc133:link, 
    table.slAdmTable tr th a.fPerc133:active, 
    table.slAdmTable tr th a.fPerc133:visited, 
    table.slAdmTable tr th a.fPerc133:hover {
        font-size: 13pt;
    }
    table.slAdmTable tr td.fPerc66, 
    table.slAdmTable tr th.fPerc66, 
    table.slAdmTable tr td a.fPerc66:link, 
    table.slAdmTable tr td a.fPerc66:active, 
    table.slAdmTable tr td a.fPerc66:visited, 
    table.slAdmTable tr td a.fPerc66:hover, 
    table.slAdmTable tr th a.fPerc66:link, 
    table.slAdmTable tr th a.fPerc66:active, 
    table.slAdmTable tr th a.fPerc66:visited, 
    table.slAdmTable tr th a.fPerc66:hover {
        font-size: 7pt;
    }

	.nodeSub .btn-lg { 
        font-size: 18pt; 
    }
	
	.fixed, #fixedHeader.fixed {
        padding-top: 10px; top: 30px; 
    }
	.jumbotron {
        padding: 20px; 
    }
	
	.unitFld {
        width: 70px; 
    }
    input.otherFld, input.form-control.otherFld, 
    label input.otherFld, label input.form-control.otherFld {
        width: 100%;
    }
    
    .nFld textarea.form-control-lg { 
        font-size: 1rem; 
    }
    
    table.slSpreadTbl tr td.sprdFld input.form-control-lg, 
    table.slSpreadTbl tr td.sprdFld select.form-control-lg {
        padding: 5px; 
    }
    table.slSpreadTbl tr td.sprdRowLab, 
    table.slSpreadTbl tr th.sprdRowLab, 
    table.slSpreadTbl tr th, 
    .nFld table.slSpreadTbl tr th {
        padding: 12px 6px 0px 6px;
    }
	
}
