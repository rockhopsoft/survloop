/* resources/views/vendor/survloop/admin/tree/node-edit-ajax.blade.php */

$("#specialFuncsBtn").click(function(){
    $("#specialFuncs").slideToggle("fast");
});
$("#extraSmallBtn").click(function() {
    $("#extraSmall").slideToggle("fast");
});
$("#extraHTMLbtn").click(function() {
    $("#extraHTML").slideToggle("fast");
});
$("#extraHTMLbtn2").click(function() {
    $("#extraHTML2").slideToggle("fast");
});
$("#internalNotesBtn").click(function() {
    $("#internalNotes").slideToggle("fast");
});
$("#stepLoopN").click(function() {
    $("#stdLoopOpts").slideDown("fast");
    $("#stepLoopOpts").slideUp("fast");
});
$("#stepLoopY").click(function() {
    $("#stdLoopOpts").slideUp("fast"); 
    $("#stepLoopOpts").slideDown("fast"); 
});

$(document).on("click", "a.condDelBtn", function() {
    var cond = $(this).attr("id").replace("cond", "").replace("delBtn", "");
    document.getElementById("cond"+cond+"wrap").style.background='#DDDDDD';
    document.getElementById("cond"+cond+"delBtn").style.display="none";
    document.getElementById("cond"+cond+"delWrap").style.display="block";
    document.getElementById("delCond"+cond+"ID").value="Y";
});
$(document).on("click", "a.condDelBtnUndo", function() {
    var cond = $(this).attr("id").replace("cond", "").replace("delUndo", "");
    document.getElementById("cond"+cond+"wrap").style.background='#FFFFFF';
    document.getElementById("cond"+cond+"delBtn").style.display="block";
    document.getElementById("cond"+cond+"delWrap").style.display="none";
    document.getElementById("delCond"+cond+"ID").value="N";
});

$(document).on("click", ".showKidBox", function() {
    var r = $(this).attr("id").replace("r", "").replace("showKID", "");
    if (document.getElementById("kidFork"+r+"")) {
        if (document.getElementById("r"+r+"showKID") && document.getElementById("r"+r+"showKID").checked) {
            document.getElementById("kidFork"+r+"").style.display="inline";
        } else {
            document.getElementById("kidFork"+r+"").style.display="none";
        }
    }
});

$(document).on("click", ".sprdTblType", function() {
    if (document.getElementById("spreadTblDefs")) {
        if (document.getElementById("spreadTblTypB").checked) {
            document.getElementById("spreadTblDefs").style.display="block";
            document.getElementById("spreadTblOpen").style.display="none";
            document.getElementById("resOpts").style.display="block";
        } else {
            document.getElementById("spreadTblDefs").style.display="none";
            document.getElementById("spreadTblOpen").style.display="block";
            document.getElementById("resOpts").style.display="none";
        }
    }
});
