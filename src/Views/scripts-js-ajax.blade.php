/* generated from resources/views/vendor/survloop/scripts-js-ajax.blade.php */
$(document).ready(function(){
        
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
        /*
        if (document.getElementById("csrfTok")) {
            var src = "/time-out";
            if (document.getElementById("postNodeForm") && document.getElementById("stepID") && document.getElementById("treeID")) {
                src += "?form="+document.getElementById("treeID").value;
            } else if (document.getElementById("isLoginID") || document.getElementById("isSignupID")) {
                src += "?login=1";
            }
            $("#dialogBody").load(src);
            $("#nondialog").fadeOut(300);
            $("#dialog").fadeIn(300);
        }
        */
        return true;
    }
    setTimeout(function() { chkFormSess(); }, (115*60000));
    
    function slideToHshooPos(hash) {
        if (document.getElementById(hash)) {
            if (anchorOffsetBonus == 0 && document.getElementById("fixedHeader")) anchorOffsetBonus = -80;
            var newTop = (1+anchorOffsetBonus+$("#"+hash+"").offset().top);
            $('html, body').animate({ scrollTop: newTop }, 800, 'swing', function(){ });
        }
        return true;
    }
    
    $("a.hsho").on('click', function(event) {
        var hash = $(this).attr("data-hash").trim();
        if (hash !== "") slideToHshooPos(hash);
        return false;
    });
    
    $("a.hshoo").on('click', function(event) {
        event.preventDefault();
        var hash = '';
        if ($(this).attr("id") && $(this).attr("id").substring(0, 6) == 'admLnk') {
            hash = '#'+$(this).attr("id").substring(6);
        } else if (this.hash !== "") {
            hash = this.hash;
        }
        var hashDivID = hash.replace('#', '');
        if (document.getElementById(hashDivID)) {
            slideToHshooPos(hash);
            hshooCurr = getHshooInd(hash);
            setTimeout(function() { updateHshooActive(hshooCurr); }, 900);
        }
        return false;
    });
    
    function chkHshoosPos() {
        for (var i = 0; i < hshoos.length; i++) {
            if (document.getElementById(hshoos[i][0].replace('#', ''))) {
                hshoos[i][1] = $(hshoos[i][0]).offset().top;
                var admLnk = "admLnk"+hshoos[i][0].replace('#', '');
                if (document.getElementById(admLnk)) {
                    if (document.getElementById(admLnk).className.indexOf("active") !== false) hshooCurr = i;
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
    
    function chkHshooScroll() {
        if (hshoos.length > 0) {
            hshooCurr = -1;
            var currScroll = Math.ceil($(document).scrollTop())-anchorOffsetBonus;
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
    setTimeout(function() { chkHshooScroll(); }, 50);
    $(document).scroll(function() { chkHshooScroll(); });
    
{!! view('vendor.survloop.scripts-js-ajax-forms')->render() !!}
    
	function hideMajNav(majInd) {
        if (document.getElementById("minorNav"+majInd+"") && document.getElementById("majSect"+majInd+"Vert2")) {
            $("#majSect"+majInd+"Vert2").slideUp(50);
            $("#minorNav"+majInd+"").slideUp(50);
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
                setTimeout(function() {
                    $("#minorNav"+majInd+"").slideDown("fast");
                    $("#majSect"+majInd+"Vert2").slideDown("fast");
                }, 50);
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
    
    $(".slSortable").sortable({
        axis: "y",
        update: function (event, ui) {
            var submitURL = $(this).attr("data-url");
            document.getElementById("hidFrameID").src=submitURL+"&"+$(this).sortable("serialize");
        }
    });
    $(".slSortable").disableSelection();
    
    $(document).on("click", ".dialogOpen", function() {
	    if (document.getElementById("dialogBody") && document.getElementById("dialogTitle")) {
            document.getElementById("dialogBody").innerHTML='<center>'+getSpinner()+'</center>';
            var src = $(this).attr("href");
            var title = $(this).attr("title");
            document.getElementById("dialogTitle").innerHTML=title;
            $("#dialogBody").load(src);
            $("#nondialog").fadeOut(300);
            $("#dialog").fadeIn(300);
        }
		return false;
	});
	$(document).on("click", ".dialogClose", function() {
		$("#dialog").fadeOut(300);
		$("#nondialog").fadeIn(300);
	});
	
	function showColorList(fldName) {
	    if (document.getElementById(""+fldName+"ID") && document.getElementById("colorPick"+fldName+"")) {
	        if (document.getElementById("colorPick"+fldName+"").innerHTML == "") {
                var src = "/ajax/color-pick?fldName="+fldName+"&preSel="+document.getElementById(""+fldName+"ID").value.replace("#", "")+"";
                $("#colorPick"+fldName+"").load(src);
            } else {
                $("#colorPick"+fldName+"").slideDown("fast");
            }
        }
        return true;
	}
    $(document).on("click", ".colorPickFld", function() {
        return showColorList($(this).attr("name"));
	});
    $(document).on("click", ".colorPickFldSwatch", function() {
        return showColorList($(this).attr("id").replace("ColorSwatch", ""));
	});
	function setColorFld(fldName, val) {
	    if (document.getElementById(""+fldName+"ID") && document.getElementById("colorPick"+fldName+"")) {
	        document.getElementById(""+fldName+"ID").value = val;
	        document.getElementById(""+fldName+"ColorSwatch").style.backgroundColor = val;
            $("#colorPick"+fldName+"").slideUp("fast");
        }
        return true;
	}
    $(document).on("click", ".colorPickRadio", function() {
        setColorFld($(this).attr("name").replace("Radio", ""), $(this).val());
		return true;
	});
    $(document).on("click", ".colorPickFldSwatchBtn", function() {
        var colorArr = $(this).attr("id").split("ColorSwatch");
        if (document.getElementById(""+colorArr[0]+"CustomID")) {
            return setColorFld(colorArr[0], "#"+colorArr[1]+"");
        }
		return true;
	});
	function setColorToCustom(fldName) {
        if (document.getElementById(""+fldName+"CustomID")) {
            return setColorFld(fldName, document.getElementById(""+fldName+"CustomID").value);
        }
        return true;
	}
    $(document).on("click", ".colorPickCustomBtn", function() {
        var fldName = $(this).attr("id").replace("SetCustomColor", "");
		return setColorToCustom(fldName);
	});
    $(document).on("keyup", ".colorPickCustomFld", function(e) {
        var fldName = $(this).attr("name").replace("Custom", "");
        if (document.getElementById(""+fldName+"CustomColor")) {
            document.getElementById(""+fldName+"CustomColor").style.backgroundColor = $(this).val();
        }
        if (e.keyCode == 13) {
            var fldName = $(this).attr("name").replace("Custom", "");
            setColorToCustom(fldName);
            e.preventDefault();
            return false;
        }
		return true;
	});
	
	if (document.getElementById('tblSelect')) {
        $("#tblSelect").load("/dashboard/db/ajax/tblFldSelT/"+encodeURIComponent(document.getElementById("RuleTablesID").value)+"");
        $("#fldSelect").load("/dashboard/db/ajax/tblFldSelF/"+encodeURIComponent(document.getElementById("RuleFieldsID").value)+"");
    }
    
    function toggleHidiv(fldGrp) {
        if (document.getElementById("hidiv"+fldGrp+"")) {
            if (document.getElementById("hidiv"+fldGrp+"").style.display!="block") {
                $("#hidiv"+fldGrp+"").slideDown("fast");
            } else {
                $("#hidiv"+fldGrp+"").slideUp("fast");
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
    
    function toggleHidnode(nID) {
        if (document.getElementById("node"+nID+"")) {
            if (document.getElementById("node"+nID+"").style.display!="block") {
                setNodeVisib(""+nID+"", "", true);
                $("#node"+nID+"").slideDown("fast");
            } else {
                setNodeVisib(""+nID+"", "", false);
                $("#node"+nID+"").slideUp("fast");
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
    
	function chkMenuLoad() {
	    if (!document.getElementById('loginLnk') && document.getElementById('headClear')) {
	        $("#headClear").load("/js-load-menu");
	    }
	}
    setTimeout(function() { chkMenuLoad(); }, 500);
    setTimeout(function() { chkMenuLoad(); }, 2000);
	
    $(document).on("click", ".adminAboutTog", function() {
        if (document.getElementById('adminAbout')) {
            $("#adminAbout").slideToggle('slow');
        }
	});
	
	function updateImgSelect(nIDtxt) {
        if (document.getElementById("n"+nIDtxt+"FldID") && document.getElementById("n"+nIDtxt+"SelImg")) {
            var imgSrc = "";
            if (document.getElementById("n"+nIDtxt+"FldID").value.trim() != "") {
                imgSrc = document.getElementById("n"+nIDtxt+"FldID").value.trim();
            }
            document.getElementById("n"+nIDtxt+"SelImg").src=imgSrc;
        }
        return true;
    }
	$(document).on("click", ".openImgUpdate", function() {
        updateImgSelect($(this).attr("id").replace("imgUpd", "").replace("n", "").replace("FldID", ""));
	});
    function defaultImgSelect(nIDtxt) {
        if (document.getElementById("n"+nIDtxt+"FldID")) {
            document.getElementById("n"+nIDtxt+"FldID").value = defMetaImg.replace(appUrl, "");
        }
        updateImgSelect(nIDtxt);
        return true;
    }
	$(document).on("click", ".openImgReset", function() {
	    var nIDtxt = $(this).attr("id").replace("imgReset", "");
        defaultImgSelect(nIDtxt);
        updateImgSelect(nIDtxt);
	});
    function openImgSelect(nIDtxt, title, presel) {
        if (document.getElementById("dialogTitle")) document.getElementById("dialogTitle").innerHTML = title;
        $("#nondialog").fadeOut(300);
        window.scrollTo(0, 0);
        $("#dialogBody").load("/ajax/img-sel?nIDtxt="+nIDtxt+"&presel="+encodeURIComponent(presel));
        $("#dialog").fadeIn(300);
        return true;
    }
	$(document).on("click", ".openImgSelect", function() {
	    var imgID = $(this).attr("id").replace("imgSelect", "");
	    var title = "";
	    if (document.getElementById("imgSelect"+imgID+"Title")) {
	        title = document.getElementById("imgSelect"+imgID+"Title").innerHTML.trim();
	    } else if ($(this).attr("data-title") && $(this).attr("data-title").trim() != '') {
	        title = $(this).attr("data-title").trim();
	    }
        openImgSelect(imgID, title, $(this).attr("data-presel"));
	});
    function openImgDetail(nIDtxt, imgID) {
        if (document.getElementById("imgDeetDiv"+nIDtxt+"")) {
            document.getElementById("imgDeetDiv"+nIDtxt+"").innerHTML = '<center>'+getSpinner()+'</center>';
            document.getElementById("hidivImgUp"+nIDtxt+"").style.display = "none";
            document.getElementById("hidivBtnImgUp"+nIDtxt+"").style.display = "block";
            $("#imgDeetDiv"+nIDtxt+"").load( "/ajax/img-deet?nIDtxt="+nIDtxt+"&imgID="+imgID+"" );
        }
        return true;
    }
	$(document).on("click", ".openImgDetail", function() {
	    var ids = $(this).attr("id").replace("selectImg", "").split("sel");
        openImgDetail(ids[0], ids[1]);
	});
    function getImgNode(imgID) {
	    var nIDtxt = "";
	    if (document.getElementById("imgNode"+imgID+"ID")) nIDtxt = document.getElementById("imgNode"+imgID+"ID").value;
	    return nIDtxt;
    }
    function imgChoose(imgID) {
        var nIDtxt = getImgNode(imgID);
        $("#nondialog").fadeIn(300);
        $("#dialog").fadeOut(300);
	    var url = "";
	    if (document.getElementById("imgUrl"+imgID+"ID")) url = document.getElementById("imgUrl"+imgID+"ID").value;
        if (document.getElementById("n"+nIDtxt+"FldID")) {
            document.getElementById("n"+nIDtxt+"FldID").value=url;
        }
        updateImgSelect(nIDtxt);
        return true;
    }
	$(document).on("click", ".imgChoose", function() {
	    imgChoose($(this).attr("id").replace("imgChoose", ""));
	});
    function imgSaveDeet(imgID) {
        var nIDtxt = getImgNode(imgID);
        if (document.getElementById("img"+imgID+"saveUpdate")) {
            document.getElementById("img"+imgID+"saveUpdate").innerHTML='<center>'+getSpinner()+'</center>';
            var formData = new FormData(document.getElementById("formSaveImg"+imgID+"ID"));
            $.ajax({
                url: "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/ajax/img-save",
                type: "POST", 
                data: formData, 
                contentType: false,
                processData: false,
                success: function(data) {
                    $("#img"+imgID+"saveUpdate").empty();
                    $("#img"+imgID+"saveUpdate").append(data);
                }, 
                error: function(xhr, status, error) {
                    $("#img"+imgID+"saveUpdate").append("<div>(error - "+xhr.responseText+")</div>");
                }
            });
        }
        return true;
    }
	$(document).on("click", ".imgSaveDeet", function() {
	    var imgID = $(this).attr("id").replace("imgSave", "");
	    imgSaveDeet(imgID);
	});
	$(document).on("keyup", ".imgSaveDeetFld", function(e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            if ($(this).attr("data-imgid")) imgSaveDeet($(this).attr("data-imgid"));
        }
    });
    function imgUpBtn(nIDtxt) {
        if (document.getElementById("img"+nIDtxt+"fileUpdate")) {
            document.getElementById("img"+nIDtxt+"fileUpdate").innerHTML='<center>'+getSpinner()+'</center>';
            var formData = new FormData(document.getElementById("formUpImg"+nIDtxt+"ID"));
            $.ajax({
                url: "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/ajax/img-up",
                type: "POST", 
                data: formData, 
                contentType: false,
                processData: false,
                success: function(data) {
                    $("#img"+nIDtxt+"fileUpdate").empty();
                    $("#img"+nIDtxt+"fileUpdate").append(data);
                }, 
                error: function(xhr, status, error) {
                    $("#img"+nIDtxt+"fileUpdate").append("<div>(error - "+xhr.responseText+")</div>");
                }
            });
        }
        return true;
    }
	$(document).on("click", ".imgUpBtn", function() {
	    imgUpBtn($(this).attr("id").replace("imgUp", ""));
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
    $(document).on("change", ".graphUpDrp", function() { pullNewGraph($(this).attr("data-nid")); return true; });
    $(document).on("click", ".graphUp", function() { pullNewGraph($(this).attr("data-nid")); return true; });
    
    function sliHgt(nIDtxt, next) {
        for (var i = 0; i < slideGals.length; i++) {
            if (next < 0) next = slideGals[i][3];
            if (slideGals[i][0] == nIDtxt && document.getElementById("blockWrap"+slideGals[i][1][next]+"")) {
                var newH = $("#blockWrap"+slideGals[i][1][next]+"").height();
                if (document.getElementById("node"+nIDtxt+"kids")) {
                    document.getElementById("node"+nIDtxt+"kids").style.height=newH+"px";
                }
                var btnHgt = 30+newH;
                var hvrHgt = newH;
                if (document.getElementById("sliLft"+nIDtxt+"")) {
                    document.getElementById("sliLft"+nIDtxt+"").style.height=btnHgt+"px";
                    document.getElementById("sliLft"+nIDtxt+"").style.marginTop="-"+newH+"px";
                    document.getElementById("sliLftHvr"+nIDtxt+"").style.height=newH+"px";
                }
                if (document.getElementById("sliRgt"+nIDtxt+"")) {
                    document.getElementById("sliRgt"+nIDtxt+"").style.height=btnHgt+"px";
                    document.getElementById("sliRgt"+nIDtxt+"").style.marginTop="-"+newH+"px";
                    document.getElementById("sliRgtHvr"+nIDtxt+"").style.height=newH+"px";
                }
            }
        }
        return true;
    }
    function sliChange(nIDtxt, next) {
        for (var i = 0; i < slideGals.length; i++) {
            if (slideGals[i][0] == nIDtxt) {
                if (document.getElementById("sliNav"+nIDtxt+"dot"+slideGals[i][3]+"") && document.getElementById("sliNav"+nIDtxt+"dot"+next+"")) {
                    $("#sliNav"+nIDtxt+"dot"+slideGals[i][3]+"").removeClass('sliNavAct');
                    $("#sliNav"+nIDtxt+"dot"+slideGals[i][3]+"").addClass('sliNav');
                    $("#sliNav"+nIDtxt+"dot"+next+"").removeClass('sliNav');
                    $("#sliNav"+nIDtxt+"dot"+next+"").addClass('sliNavAct');
                }
                for (var j = 0; j < slideGals[i][1].length; j++) {
                    var kidID = slideGals[i][1][j];
                    if (document.getElementById("blockWrap"+kidID+"")) {
                        if (j == next) {
                            $("#blockWrap"+kidID+"").delay(451).fadeIn(450);
                            setTimeout(function() { sliHgt(nIDtxt, j); }, 451);
                        } else {
                            $("#blockWrap"+kidID+"").fadeOut(450);
                        }
                    }
                }
                slideGals[i][3] = next;
            }
        }
        return true;
    }
    function sliLoadAuto(nIDtxt) {
        for (var i = 0; i < slideGals.length; i++) {
            if (slideGals[i][0] == nIDtxt) {
                if (slideGals[i][4] > 0) {
                    slideGals[i][4] = 0;
                } else {
                    sliNext(nIDtxt);
                }
                setTimeout(function() { sliLoadAuto(nIDtxt); }, 8000);
            }
        }
        return true;
    }
    function sliLoadHgts() {
        for (var i = 0; i < slideGals.length; i++) sliHgt(slideGals[i][0], -1);
        return true;
    }
    function sliLoad() {
        for (var i = 0; i < slideGals.length; i++) {
            var nIDtxt = slideGals[i][0];
            sliChange(nIDtxt, 0);
            sliHgt(nIDtxt, 0);
            setTimeout(function() { sliLoadAuto(nIDtxt); }, 8000);
        }
        sliLoadHgts();
        return true;
    }
    setTimeout(function() { sliLoad(); }, 1);
    function sliNext(nIDtxt) {
        for (var i = 0; i < slideGals.length; i++) {
            if (slideGals[i][0] == nIDtxt) {
                var next = slideGals[i][3]+1;
                if (next >= slideGals[i][1].length) next = 0;
                sliChange(nIDtxt, next);
            }
        }
        return true;
    }
    $(document).on("click", ".sliRgt", function() {
        var nIDtxt = $(this).attr("id").replace("sliRgt", "");
        for (var i = 0; i < slideGals.length; i++) {
            if (slideGals[i][0] == nIDtxt) slideGals[i][4]++;
        }
        sliNext(nIDtxt);
        return true;
    });
    $(document).on("click", ".sliLft", function() {
        var nIDtxt = $(this).attr("id").replace("sliLft", "");
        for (var i = 0; i < slideGals.length; i++) {
            if (slideGals[i][0] == nIDtxt) {
                var next = slideGals[i][3]-1;
                if (next < 0) next = slideGals[i][1].length-1;
                slideGals[i][4]++;
                sliChange(nIDtxt, next);
            }
        }
        return true;
    });
    $(document).on("click", ".sliNav", function() {
        var n = $(this).attr("id").replace("sliNav", "").split("dot");
        for (var i = 0; i < slideGals.length; i++) {
            if (slideGals[i][0] == n[0]) slideGals[i][4]++;
        }
        sliChange(n[0], n[1]);
        return true;
    });
    
@if (isset($GLOBALS['SL']->sysOpts['logo-img-sm']) 
    && $GLOBALS['SL']->sysOpts['logo-img-sm'] != $GLOBALS['SL']->sysOpts['logo-img-lrg'])
    function chkLogoResize() {
        if (!document.getElementById('slLogoImg')) return false;
        if (window.innerWidth <= 480) {
            document.getElementById('slLogoImg').src='{{ $GLOBALS['SL']->sysOpts['logo-img-sm'] }}';
        } else if (window.innerWidth <= 768) {
            document.getElementById('slLogoImg').src='{{ $GLOBALS['SL']->sysOpts['logo-img-md'] }}';
        } else {
            document.getElementById('slLogoImg').src='{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}';
        }
    }
    setTimeout(function() { chkLogoResize(); }, 1);
@endif

    var winResizeHold = false;
    $(window).on('resize', function(){
        if (!winResizeHold) {
            winResizeHold = true;
            var win = $(this); //this = window
            sliLoadHgts();
@if (isset($GLOBALS['SL']->sysOpts['logo-img-sm']) 
    && $GLOBALS['SL']->sysOpts['logo-img-sm'] != $GLOBALS['SL']->sysOpts['logo-img-lrg'])
            chkLogoResize();
@endif
            setTimeout(function() { winResizeHold = false; }, 250);
        }
    });
    
    $(document).on("click", ".admSrchFldFocus", function() {
        if (document.getElementById("admSrchFld")) {
            $("#admSrchFld").focus();
        }
    });
    
    function chkFixedHeader() {
        if (document.getElementById('fixedHeader')) {
            var newW = Math.round($("#mainBody").outerWidth())-4;
            if (document.getElementById('fixedHeadWidth')) {
                newW = Math.round($("#fixedHeadWidth").outerWidth())+30;
            }
            document.getElementById('fixedHeader').style.width = ''+newW+'px';
            setTimeout(function() { chkFixedHeader(); }, 1000);
        } else {
            setTimeout(function() { chkFixedHeader(); }, 5000);
        }
    }
    setTimeout(function() { chkFixedHeader(); }, 10);
    
    function openAdmMenu() {
        if (document.getElementById("leftAdmMenu") && document.getElementById("leftAdmMenu").style.display != 'block') {
            if (document.getElementById("menuUnColpsBtn")) $("#menuUnColpsBtn").slideUp("fast");
            if (document.getElementById("menuColpsBtn")) $("#menuColpsBtn").slideDown("fast");
            if (document.getElementById("leftSideWdth")) {
                $("#leftSideWdth").animate({
                    width: "200px"
                }, {
                    duration: 150,
                    specialEasing: {
                        width: 'swing'
                    }
                });
                setTimeout(function() { $("#leftAdmMenu").slideDown(150); }, 150);
            }
        }
    }
    function closeAdmMenu() {
        if (document.getElementById("leftAdmMenu") && document.getElementById("leftAdmMenu").style.display != 'none') {
            if (document.getElementById("menuColpsBtn")) $("#menuColpsBtn").slideUp("fast");
            if (document.getElementById("menuUnColpsBtn")) $("#menuUnColpsBtn").slideDown("fast");
               $("#leftAdmMenu").slideUp(150);
            if (document.getElementById("leftSideWdth")) {
                setTimeout(function() { 
                    if (document.getElementById("leftSideWrap")) {
                        $("#leftSideWrap").animate({
                            padding: "0px 6px"
                        }, {
                            duration: 150,
                            specialEasing: {
                                width: 'swing'
                            }
                        });
                    }
                    $("#leftSideWdth").animate({
                        width: "24px"
                    }, {
                        duration: 150,
                        specialEasing: {
                            width: 'swing'
                        }
                    });
                }, 150);
            }
        }
    }
    $(document).on("click", "#menuColpsBtn", function() { closeAdmMenu(); });
    $(document).on("click", "#menuUnColpsBtn", function() { openAdmMenu(); });
    $(window).resize(function() {
        if ($(window).width() <= 992) closeAdmMenu();
        else openAdmMenu();
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
	
});