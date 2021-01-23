/* generated from resources/views/vendor/survloop/js/scripts-ajax.blade.php */

$(document).ready(function(){
    
{!! view('vendor.survloop.js.scripts-ajax-forms')->render() !!}
    
{!! view('vendor.survloop.js.scripts-ajax-forms-signup')->render() !!}
    
{!! view('vendor.survloop.js.scripts-ajax-search')->render() !!}

{!! view('vendor.survloop.js.scripts-ajax-scrolling')->render() !!}

{!! view('vendor.survloop.js.scripts-ajax-size-match')->render() !!}

{!! view('vendor.survloop.js.scripts-ajax-colors')->render() !!}

{!! view('vendor.survloop.js.scripts-ajax-images')->render() !!}

    // Primary hook for little scripts which need to be run every second or two
    function loopCheckPageTweaks(timeout) {
        chkAutoLoadDashResults();
        chkSearchWidth();
        chkMatchCols();
        chkSpecialNodes();
        chkAnyAnimRevealResults();

        timeout=Math.round(1.1*timeout);
        setTimeout(function() { loopCheckPageTweaks(timeout); }, timeout);
        return true;
    }
    setTimeout(function() { loopCheckPageTweaks(1000); }, 100);

    function isAdmDash() {
        return ($( "#mainBody" ).hasClass( "mainBodyDash" ));
    }

    var winResizeHold = false;
    function slOnResize() {
        if (!winResizeHold) {
            winResizeHold = true;
            var win = $(this); //this = window
            sliLoadHgts();
            chkLogoResize();
            setTimeout(function() { winResizeHold = false; }, 250);
        }
    }
    $(window).on('resize', function(){ slOnResize(); });
    
    function popDialog(title, desc) {
        if (document.getElementById("dialogPop")) {
            document.getElementById("dialogPop").title=title;
            document.getElementById("dialogPop").innerHTML=desc;
            $( "#dialogPop" ).dialog();
            if (document.getElementById( "nondialog" )) {
                $( "#nondialog" ).addClass( "opac20" );
            }
        }
        return true;
    }
    $(document).on("click", ".popDialog", function() {
		var title = $(this).attr("data-dia-title");
		var desc = $(this).attr("data-dia-desc");
        popDialog(title, desc);
    });
    function unPopDialog() { $( "#nondialog" ).removeClass( "opac20" ); return true; }
    $(document).on("click", ".unPopDialog", function() { unPopDialog(); });
    
    function chkFormSess() {
        if (document.getElementById("csrfTok") && !document.getElementById("csrfTokSkip")) {
            if (document.getElementById("isLoginID") || document.getElementById("isSignupID")) {
                setTimeout("window.location='/login'", 10);
                return true;
            }
            document.getElementById('dialogCloseID').style.display='none';
            var src = "/time-out";
            if (document.getElementById("postNodeForm") && document.getElementById("stepID") && document.getElementById("treeID")) {
                src += "?form="+document.getElementById("treeID").value;
            }
            $("#dialogBody").load(src);
            $("#nondialog").fadeOut(300);
            setTimeout(function() { $("#dialog").fadeIn(300); }, 301);
        }
        return true;
    }
    setTimeout(function() { chkFormSess(); }, (45*60000));
    
    function chkNav2(currScroll) {
        if (document.getElementById("mainNav2")) {
            var pos = currNav2Pos[0];
            if (window.innerWidth < 992) {
                if (window.innerWidth > 768) {
                    if (currNav2Pos.length > 1) {
                        pos = currNav2Pos[1];
                    }
                } else if (window.innerWidth > 480) {
                    if (currNav2Pos.length > 2) {
                        pos = currNav2Pos[2];
                    } else if (currNav2Pos.length > 1) {
                        pos = currNav2Pos[1];
                    }
                }
            }
            if (currScroll > pos) {
                document.getElementById("mainNav2").style.display="block";
            } else {
                document.getElementById("mainNav2").style.display="none";
            }
        }

    }

    $(document).scroll(function() {
        var currScroll = getCurrScroll();
        chkHshooScroll(currScroll);
        chkNav2(currScroll);
    });
    
	function hideMajNav(majInd) {
        if (document.getElementById("minorNav"+majInd+"") && document.getElementById("majSect"+majInd+"Vert2")) {
            document.getElementById("minorNav"+majInd+"").style.display='none';
            document.getElementById("majSect"+majInd+"Vert2").style.display='none';
            //$("#majSect"+majInd+"Vert2").slideUp(50);
            //$("#minorNav"+majInd+"").slideUp(50);
        }
        return true;
	}
	
	$(document).on("click", ".navDeskMaj", function() {
		var majInd = parseInt($(this).attr("id").replace("maj", ""));
		if ($(this).attr("data-jumpnode") && document.getElementById("stepID") && document.getElementById("jumpToID")) {
            document.getElementById("jumpToID").value = $(this).attr("data-jumpnode");
            if (document.getElementById("dataLoopRootID")) document.getElementById("stepID").value="exitLoopJump";
            return runFormSub();
		}
		for (var i = 0; i < treeMajorSects.length; i++) {
		    if (i != majInd) hideMajNav(i);
		}
		if (document.getElementById("minorNav"+majInd+"") && document.getElementById("majSect"+majInd+"Vert2")) {
		    if (document.getElementById("minorNav"+majInd+"").style.display == 'block') {
		        hideMajNav(majInd);
            } else {
                document.getElementById("minorNav"+majInd+"").style.display='block';
                document.getElementById("majSect"+majInd+"Vert2").style.display='block';
                //setTimeout(function() {
                //    $("#minorNav"+majInd+"").slideDown("fast");
                //    $("#majSect"+majInd+"Vert2").slideDown("fast");
                //}, 50);
            }
        }
        $(this).blur();
	    return false;
	});

	$(document).on("click", "#navMobToggle", function() {
	    if (document.getElementById("navMobBurger1").style.display!="none") {
            document.getElementById("navMobBurger1").style.display="none";
            document.getElementById("navMobBurger2").style.display="inline";
            $("#navMobFull").slideDown("fast");
        } else {
            document.getElementById("navMobBurger1").style.display="inline";
            document.getElementById("navMobBurger2").style.display="none";
            $("#navMobFull").slideUp("fast");
        }
	});
	
	$(document).on("click", ".nodeShowCond", function() {
        var nID = $(this).attr("id").replace("showCond", "");
        if (document.getElementById("condDeets"+nID+"")) {
            if (document.getElementById("condDeets"+nID+"").style.display=="inline") {
                document.getElementById("condDeets"+nID+"").style.display="none";
            } else {
                document.getElementById("condDeets"+nID+"").style.display="inline";
            }
        }
        return true;
    });
    
    $(".slSortable").sortable({
        axis: "y",
        update: function (event, ui) {
            var submitURL = $(this).attr("data-url");
            document.getElementById("hidFrameID").src=submitURL+"&"+$(this).sortable("serialize");
        }
    });
    $(".slSortable").disableSelection();


    function iframeChkHeight(iframeID) {
        var iframeObj = document.getElementById(iframeID);
        if (iframeObj) {
            iframeObj.height = "";
            iframeObj.height = iframeObj.contentWindow.document.body.scrollHeight + "px";
        }
    }
    function iframeLoaded(iframeID) {
        setTimeout(function() { iframeChkHeight(iframeID); }, 1000);
        setTimeout(function() { iframeChkHeight(iframeID); }, 3000);
        setTimeout(function() { iframeChkHeight(iframeID); }, 6000);
        setTimeout(function() { iframeChkHeight(iframeID); }, 10000);
    }
    
    $(document).on("click", ".dialogOpen", function() {
	    if (document.getElementById("dialogBody") && document.getElementById("dialogTitle")) {
            document.getElementById("dialogBody").innerHTML='<center>'+getSpinner()+'</center>';
            var src = $(this).attr("href");
            var title = $(this).attr("title");
            document.getElementById("dialogTitle").innerHTML=title;
            $("#dialogBody").load(src);
            $("#nondialog").fadeOut(300);
            setTimeout(function() { $("#dialog").fadeIn(300); }, 301);
        }
		return false;
	});
	$(document).on("click", ".dialogClose", function() {
		$("#dialog").fadeOut(300);
		setTimeout(function() { $("#nondialog").fadeIn(300); }, 301);
	});
	
	if (document.getElementById('tblSelect')) {
        $("#tblSelect").load("/dashboard/db/ajax/tblFldSelT/"+encodeURIComponent(document.getElementById("RuleTablesID").value)+"");
        $("#fldSelect").load("/dashboard/db/ajax/tblFldSelF/"+encodeURIComponent(document.getElementById("RuleFieldsID").value)+"");
    }
    
    function toggleHidiv(fldGrp) {
        if (document.getElementById("hidiv"+fldGrp+"")) {
            if (document.getElementById("hidiv"+fldGrp+"").style.display!="block" || document.getElementById("hidiv"+fldGrp+"").style.display=="none") {
                $("#hidiv"+fldGrp+"").slideDown("fast");
                setTimeout(function() { document.getElementById("hidiv"+fldGrp+"").style.display="block"; }, 351);
                if (document.getElementById("hidivBtnArr"+fldGrp+"")) {
                    document.getElementById("hidivBtnArr"+fldGrp+"").className=document.getElementById("hidivBtnArr"+fldGrp+"").className.replace("-down", "-up");
                }
                if (document.getElementById("hidivBtnAcc"+fldGrp+"")) {
                    document.getElementById("hidivBtnAcc"+fldGrp+"").className=document.getElementById("hidivBtnAcc"+fldGrp+"").className.replace("-down", "-up");
                }
            } else {
                $("#hidiv"+fldGrp+"").slideUp("fast");
                setTimeout(function() { document.getElementById("hidiv"+fldGrp+"").style.display="none"; }, 351);
                if (document.getElementById("hidivBtnArr"+fldGrp+"")) {
                    document.getElementById("hidivBtnArr"+fldGrp+"").className=document.getElementById("hidivBtnArr"+fldGrp+"").className.replace("-up", "-down");
                }
                if (document.getElementById("hidivBtnAcc"+fldGrp+"")) {
                    document.getElementById("hidivBtnAcc"+fldGrp+"").className=document.getElementById("hidivBtnAcc"+fldGrp+"").className.replace("-up", "-down");
                }
            }
        }
        return true;
    }
	$(document).on("click", ".hidivCrt", function() {
        var fldGrp = $(this).attr("id").replace("hidivBtn", "");
        if (document.getElementById("hidiv"+fldGrp+"") && document.getElementById("hidivCrt"+fldGrp+"")) {
            if (document.getElementById("hidiv"+fldGrp+"").style.display!="block") {
                $("#hidivCrt"+fldGrp+"").attr('class', 'fa fa-chevron-up');
            } else {
                $("#hidivCrt"+fldGrp+"").attr('class', 'fa fa-chevron-down');
            }
        }
        return true;
	});
	$(document).on("click", ".hidivBtn", function() {
        var fldGrp = $(this).attr("id").replace("hidivBtn", "");
        toggleHidiv(fldGrp);
	});
    $(document).on("click", ".hidivBtnSelf", function() {
        var fldGrp = $(this).attr("id").replace("hidivBtn", "");
        $(this).slideUp("fast");
        setTimeout(function() { toggleHidiv(fldGrp); }, 350);
    });
    $(document).on("click", ".hidSelf", function() {
        $(this).slideUp("fast");
    });


    $(document).on("click", ".loadAnimBtn", function() {
        var animID = $(this).attr("data-anim-id");
        if (document.getElementById("loadAnim"+animID+"") && document.getElementById("loadAnimClicked"+animID+"")) {
            document.getElementById("loadAnim"+animID+"").style.display="none";
            document.getElementById("loadAnimClicked"+animID+"").style.display="block";
        }
    });

    
    function visibShowNode(nID) {
        setNodeVisib(""+nID+"", "", true);
        $("#node"+nID+"").slideDown("fast");
    }
    function visibHideNode(nID) {
        setNodeVisib(""+nID+"", "", false);
        $("#node"+nID+"").slideUp("fast");
    }

    function toggleHidnode(nID) {
        if (document.getElementById("node"+nID+"")) {
            if (document.getElementById("node"+nID+"").style.display!="block") {
                visibShowNode(nID);
            } else {
                visibHideNode(nID);
            }
        }
        return true;
    }
	$(document).on("click", ".hidnodeBtn", function() {
        var nID = $(this).attr("id").replace("hidnodeBtn", "");
        toggleHidnode(nID);
	});
	$(document).on("click", ".hidnodeBtnSelf", function() {
        var nID = $(this).attr("id").replace("hidnodeBtn", "");
        toggleHidnode(nID);
        $(this).slideUp("fast");
	});
	
	$(document).on("click", ".hidTogAll", function() {
	    if ($(this).attr("data-list") && $(this).attr("data-list").trim() != '') {
            var list = $(this).attr("data-list").split(",");
            if (list.length > 0 && document.getElementById("hidiv"+list[0]+"")) {
                var nowShow = false;
                if (document.getElementById("hidiv"+list[0]+"").style.display!="block") nowShow = true;
                for (var j = 0; j < list.length; j++) {
                    if (document.getElementById("hidiv"+list[j]+"")) {
                        if (nowShow) $("#hidiv"+list[j]+"").slideDown("fast");
                        else $("#hidiv"+list[j]+"").slideUp("fast");
                    }
                    if (document.getElementById("hidivCrt"+list[j]+"")) {
                        if (nowShow) $("#hidivCrt"+list[j]+"").attr('class', 'fa fa-chevron-up');
                        else $("#hidivCrt"+list[j]+"").attr('class', 'fa fa-chevron-down');
                    }
                }
            }
        }
	});
	
	$(document).on("click", ".ajx", function() {
        var ajxUrl = $(this).attr("data-ajx");
        var dest = $(this).attr("data-dst");
        if (document.getElementById(dest)) $("#"+dest+"").load(ajxUrl);
	});
    
    $(document).on("click", "#userMenuBtn", function() {
        toggleNav();
    });

    function chkMenuProgLoad() {
        if (headProgBarPerc <= 0 || !document.getElementById("myNavBar") || document.getElementById("myNavBar").innerHTML.indexOf("userMenuProg") >= 0) {
            return false;
        }
        document.getElementById("myNavBar").innerHTML += "<canvas id=\"userMenuProg\" class=\"float-right\" width=\"240\" height=\"240\" style=\"width: 120px; height: 120px;\"></canvas>";
        $('#userMenuProg').radialIndicator({
            barColor: '{!! $css["color-main-on"] !!}',
            barWidth: 10,
            initValue: headProgBarPerc,
            roundCorner : true,
            percentage: true
        });
        var radialObj = $('#userMenuProg').data('radialIndicator');
        radialObj.animate(60);
    }
    setTimeout(function() { chkMenuProgLoad(); }, 1000);

    function chkMenuLoad(last) {
	    if ((!document.getElementById('loginLnk') && !document.getElementById('userMenu')) && document.getElementById('headClear')) {
            var redirUrl = "/js-load-menu";
            if (currTreeType == 'Survey' && currPage[1].trim() != '') {
                redirUrl += "?currPage="+encodeURIComponent(currPage[1])+'&nd='+currTreeNode+'';
            }
	        $("#headClear").load(redirUrl);
	    }
	}
    setTimeout(function() { chkMenuLoad(); }, 500);
    setTimeout(function() { chkMenuLoad(true); }, 2000);
	
    $(document).on("click", ".adminAboutTog", function() {
        if (document.getElementById('adminAbout')) {
            $("#adminAbout").slideToggle('slow');
        }
	});
	
	function pullNewGraph(nID) {
	    for (var i=0; i < graphFlds.length; i++) {
	        if (graphFlds[i][0] == nID) {
	            var p = graphFlds[i][2];
	            for (var g=0; g < graphs.length; g++) {
	                if (graphs[g][0] == p) {
                        var graphUrl = graphs[g][2];
                        var fVars = "";
                        for (var j=0; j < graphFlds.length; j++) {
                            if (graphFlds[j][2] == graphFlds[i][2]) {
                                fVars += "__"+graphFlds[j][3]+"|";
                                if (document.getElementById("n"+graphFlds[j][1]+"FldID")) {
                                    fVars += document.getElementById("n"+graphFlds[j][1]+"FldID").value;
                                } else {
                                    fVars += $(".nCbox"+nID+":checked").map(function() { return this.value; }).get();
                                }
                            }
                        }
                        if (fVars.length > 0) graphUrl += "?f="+fVars.substring(2);
                        $("#n"+p+"ajaxLoad").load(graphUrl);
                        //document.getElementById('node287').innerHTML+="<br />"+graphUrl;
                    }
                }
	        }
	    }
	    return true;
    }
    $(document).on("change", ".graphUpDrp", function() {
        pullNewGraph($(this).attr("data-nid"));
        return true;
    });
    $(document).on("click", ".graphUp", function() { 
        pullNewGraph($(this).attr("data-nid"));
        return true;
    });
    
    $(document).on("click", ".admSrchFldFocus", function() {
        if (document.getElementById("admSrchFld")) {
            $("#admSrchFld").focus();
        }
    });

    function chkFixedHeader(newW, divID) {
        if (document.getElementById(divID)) {
            document.getElementById(divID).style.width = newW;
            document.getElementById(divID).style.minWidth = newW;
            document.getElementById(divID).style.maxWidth = newW;
        }
        return true;
    }
    function chkFixedHeaders(chkAgain) {
        var newW = Math.round($("#main").outerWidth());
        newW = ''+newW+'px';
        chkFixedHeader(newW, 'mainNav');
        chkFixedHeader(newW, 'fixedHeader2');
        if (isAdmDash()) {
            newW -= 60;
        }
        chkFixedHeader(newW, 'fixedHeader');
        if (chkAgain) {
            setTimeout(function() { chkFixedHeaders(true); }, 5000);
        }
        return true;
    }
    setTimeout(function() { chkFixedHeaders(true); }, 10);
    
    function logAdmMenuToggle(opening) {
        var cycle = admMenuCollapses%3;
        if (document.getElementById("admBgAjax"+cycle+"")) {
            var src = "/ajax/adm-menu-toggle";
            if (opening) src += "?status=open";
            $("#admBgAjax"+cycle+"").load(src);
        }
        admMenuCollapses++;
    }

    function openAdmMenu() {
        if (document.getElementById("leftSide")) {
            document.getElementById("leftSide").className="leftSide";
        }
        if (document.getElementById("admMenuClpsArr")) {
            document.getElementById("admMenuClpsArr").className="fa fa-arrow-left";
        }
        chkFixedHeaders(false);
        logAdmMenuToggle(true);
        return true;
    }
    function closeAdmMenu() {
        if (document.getElementById("leftSide")) {
            document.getElementById("leftSide").className="leftSideCollapse";
        }
        if (document.getElementById("admMenuClpsArr")) {
            document.getElementById("admMenuClpsArr").className="fa fa-arrow-right";
        }
        chkFixedHeaders(false);
        logAdmMenuToggle(false);
        return true;
    }
    $(document).on("click", "#admMenuClpsBtn", function() {
        if (document.getElementById("leftSide")) {
            if (document.getElementById("leftSide").className.localeCompare('leftSideCollapse') == 0) {
                return openAdmMenu();
            } else {
                return closeAdmMenu();
            }
        }
        return false;
    });
    setTimeout(function() { 
        var mobile = false;
        if (window.innerWidth < 768) mobile = true;
        if (openAdmMenuOnLoad && !mobile) openAdmMenu();
        else closeAdmMenu();
    }, 10);
    $(window).resize(function() {
        setTimeout(function() { chkFixedHeaders(false); }, 5);
    });
    $(document).on("click", ".admMenuTier1Lnk", function() {
        openAdmMenu();
    });
    $(document).on("click", ".admMenuTier2Lnk", function() {
        openAdmMenu();
    });
    
    $(document).on("click", ".clickBox", function() {
        if ($(this).attr("data-url")) window.location=$(this).attr("data-url");
        return true;
	});
    $(document).on({
        mouseenter: function () {
            $(this).css("background-color", "{!! $css['color-main-faint'] !!}");
        },
        mouseleave: function () {
            $(this).css("background-color", "{!! $css['color-main-bg'] !!}");
        }
    }, ".clickBox");

    @if (isset($GLOBALS['SL']->sysOpts['sys-cust-ajax']))
        {!! $GLOBALS['SL']->sysOpts['sys-cust-ajax'] !!}
    @endif
});