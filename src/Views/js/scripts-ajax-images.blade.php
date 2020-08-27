/* generated from resources/views/vendor/survloop/js/scripts-ajax-images.blade.php */

function chkLogoResize() {
<?php /*    
@if (isset($GLOBALS['SL']->sysOpts['logo-img-sm']) 
&& $GLOBALS['SL']->sysOpts['logo-img-sm'] != $GLOBALS['SL']->sysOpts['logo-img-lrg'])
    if (!document.getElementById('slLogoImg')) return false;
    if (window.innerWidth <= 480) {
        document.getElementById('slLogoImg').src='{{ $GLOBALS['SL']->sysOpts['logo-img-sm'] }}';
    } else if (window.innerWidth <= 768) {
        document.getElementById('slLogoImg').src='{{ $GLOBALS['SL']->sysOpts['logo-img-md'] }}';
    } else {
        document.getElementById('slLogoImg').src='{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}';
    }
@endif
*/ ?>
}
setTimeout(function() { chkLogoResize(); }, 1);

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
    var url = "/ajax/img-sel?nIDtxt="+nIDtxt+"&presel="+encodeURIComponent(presel);
    console.log(url);
    $("#dialogBody").load(url);
    setTimeout(function() { $("#dialog").fadeIn(300); }, 301);
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
    var nIDtxt = "pageImg";
    if (document.getElementById("imgNode"+imgID+"ID") && document.getElementById("imgNode"+imgID+"ID").value.trim() != '') nIDtxt = document.getElementById("imgNode"+imgID+"ID").value;
console.log("getImgNode("+imgID+" - "+nIDtxt);
    return nIDtxt;
}
function imgChoose(imgID) {
    var nIDtxt = getImgNode(imgID);
    $("#nondialog").fadeIn(300);
    setTimeout(function() { $("#dialog").fadeOut(300); }, 301);
    var url = "";
    if (document.getElementById("imgUrl"+imgID+"ID")) url = document.getElementById("imgUrl"+imgID+"ID").value;
console.log("imgChoosen "+imgID+" - n"+nIDtxt+"FldID ?");
    if (document.getElementById("n"+nIDtxt+"FldID")) {
        document.getElementById("n"+nIDtxt+"FldID").value=url;

console.log("imgChoose("+imgID+" - n"+nIDtxt+"FldID = "+document.getElementById("n"+nIDtxt+"FldID").value);
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
    for (var i = 0; i < slideGals.length; i++) {
        sliHgt(slideGals[i][0], -1);
    }
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