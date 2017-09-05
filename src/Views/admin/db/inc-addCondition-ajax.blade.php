/* resources/views/vendor/survloop/admin/db/inc-addCondition-ajax.blade.php */
$("#oldCondsID").change(function() {
    if (document.getElementById("oldCondsID").value >= 0) $("#createNewCond").slideUp("fast");
    else $("#createNewCond").slideDown("fast");
});
$("#condTypeID").change(function() {
    if (document.getElementById("condTypeID").value == 'simple') {
        $("#createNewCondComplex").slideUp("fast");
        $("#createNewCondSimple").slideDown("fast");
    } else {
        $("#createNewCondSimple").slideUp("fast");
        $("#createNewCondComplex").slideDown("fast");
    }
});
$("#setSelectID").change(function() {
    //alert("/dashboard/db/ajax/getSetFlds/"+encodeURIComponent(document.getElementById("setSelectID").value)+"");
    if (document.getElementById("setSelectID").value == "url-parameters") {
        if (document.getElementById("urlParams")) document.getElementById("urlParams").style.display = "block";
        if (document.getElementById("fldSelect")) document.getElementById("fldSelect").innerHTML = "";
        if (document.getElementById("valSelect")) document.getElementById("valSelect").innerHTML = "";
    } else {
        if (document.getElementById("urlParams")) document.getElementById("urlParams").style.display = "none";
        var fldVal = encodeURIComponent(document.getElementById("setSelectID").value);
        $("#fldSelect").load("/dashboard/db/ajax/getSetFlds/"+fldVal+"");
        document.getElementById("valSelect").innerHTML = "";
    }
    //document.getElementById("nameIt").style.display = "none";
});
$("#addCondLnk").click(function() { 
    $("#addCond").slideToggle("fast");
});
$(document).on("click", "#addNewCond", function() {
    $("#newCond").slideToggle("fast");
});
$(document).on("click", ".condDelBtn", function() {
    var i = $(this).attr("id").replace("condDelBtn", "");
    $("#condDel"+i+"").slideToggle("fast");
});
$(document).on("click", ".hasArticleBox", function() {
    var i = $(this).attr("id").replace("CondHasArticleID", "");
    if (document.getElementById('CondHasArticleID'+i+'').checked) $("#condArticle"+i+"").slideDown("fast");
    else $("#condArticle"+i+"").slideUp("fast");
    return true;
});
$(document).on("change", "#setFldID", function() {
    if (document.getElementById("setFldID").value == '') {
        document.getElementById("valSelect").innerHTML = '';
        document.getElementById("nameIt").style.display = 'none';
    } else {
        var fldVal = document.getElementById("setFldID").value;
        if (fldVal == 'EXISTS' || fldVal == 'EXISTS>1') {
            document.getElementById("nameIt").style.display = 'block';
        } else {    
            //alert("/dashboard/db/ajax/getSetFldVals/"+encodeURIComponent(fldVal)+"");
            $("#valSelect").load("/dashboard/db/ajax/getSetFldVals/"+encodeURIComponent(fldVal)+"");
            document.getElementById("nameIt").style.display = 'block';
        }
    }
});
var openResponses = 0;
$(document).on("click", "#addValResponse", function() {
    //alert(openResponses);
    $("#valsOpenDiv"+openResponses+"").slideDown('fast');
    if (openResponses == 9) $("#addValResponse").slideUp('fast');
    openResponses++;
});
$(document).on("click", ".multConds", function() {
    var condID = $(this).attr("id").replace("multConds", "");
    if (document.getElementById('multConds'+condID+'desc')) {
        if (document.getElementById('multConds'+condID+'') && document.getElementById('multConds'+condID+'').checked) {
            document.getElementById('multConds'+condID+'not').style.display='inline';
            $("#multConds"+condID+"desc").slideDown("fast");
        } else {
            document.getElementById('multConds'+condID+'not').style.display='none';
            $("#multConds"+condID+"desc").slideUp("fast");
        }
    }
});

$(document).on("keyup", "#condHashID", function() {
    if (document.getElementById("condHashID").value.indexOf("#", 1) > 0) {
        document.getElementById("condHashID").value = document.getElementById("condHashID").value.replace("#", "").replace("#", "").replace("#", "").replace("#", "");
        document.getElementById("condHashID").value = "#"+document.getElementById("condHashID").value;
    } else if (document.getElementById("condHashID").value.substring(0, 1) != "#") {
        document.getElementById("condHashID").value = "#"+document.getElementById("condHashID").value;
    }
});

function chkArts(condInd) {
    var maxInd = -1;
    for (var j=0; j < 10; j++) {
        if (document.getElementById('arti'+j+'')) {
            if ((document.getElementById('condArtTitle'+j+'ID') && document.getElementById('condArtTitle'+j+'ID').value.trim() != '')
                || (document.getElementById('condArtUrl'+j+'ID') && document.getElementById('condArtUrl'+j+'ID').value.trim() != '')) {
                maxInd = j;
            }
        }
        if (j <= (1+maxInd)) $("#arti"+j+"").slideDown("fast");
        else $("#arti"+j+"").slideUp("fast");
    }
    return true;
}
$(document).on("click", ".addArtBtn", function() {
    var condInd = $(this).attr("id").replace("addArt", "");
    chkArts(condInd);
    if (document.getElementById('condArticle'+condInd+'')) $("#condArticle"+condInd+"").slideDown("fast");
    return true;
});
