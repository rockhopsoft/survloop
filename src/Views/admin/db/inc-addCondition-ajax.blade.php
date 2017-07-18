/* resources/views/vendor/survloop/admin/db/inc-addCondition-ajax.blade.php */
$("#oldCondsID").change(function() {
    if (document.getElementById("oldCondsID").value >= 0) $("#createNewCond").slideUp("fast");
    else $("#createNewCond").slideDown("fast");
});
$("#setSelectID").change(function() {
//alert("/dashboard/db/ajax/getSetFlds/"+encodeURIComponent(document.getElementById("setSelectID").value)+"");
    if (document.getElementById("setSelectID").value == "url-parameters") {
        if (document.getElementById("urlParams")) document.getElementById("urlParams").style.display = "block";
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


$("#setFldID").change(function() {
    if (document.getElementById("setFldID").value == '') {
        document.getElementById("valSelect").innerHTML = '';
        document.getElementById("nameIt").style.display = 'none';
    }
    else if (document.getElementById("setFldID").value == 'EXISTS' || document.getElementById("setFldID").value == 'EXISTS>1') {
        document.getElementById("nameIt").style.display = 'block';
    }
    else {    
        //alert("/dashboard/db/ajax/getSetFldVals/"+encodeURIComponent(document.getElementById("setFldID").value)+"");
        $("#valSelect").load("/dashboard/db/ajax/getSetFldVals/"+encodeURIComponent(document.getElementById("setFldID").value)+"");
        document.getElementById("nameIt").style.display = 'block';
    }
});

var openResponses = 0;
$("#addValResponse").click(function() { 
    openResponses++;
    $("#valsOpenDiv"+openResponses+"").slideDown('fast');
    if (openResponses == 9) $("#addValResponse").slideUp('fast');
});