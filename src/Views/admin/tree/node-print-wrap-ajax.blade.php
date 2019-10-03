/* resources/views/vendor/survloop/admin/tree/node-print-wrap-ajax.blade.php */

$(document).on("click", ".adminNodeExpand", function() {
    var nID = $(this).attr("id").replace("adminNode", "").replace("Expand", "");
    $("#nodeKids"+nID+"").slideToggle("fast");
    window.location='#n'+nID+'';
    return true;
});
$(document).on("click", ".circleBtn", function() {
    var nID = $(this).attr("id").replace("showBtns", "");
    if (document.getElementById("showBtns"+nID+"") && document.getElementById("nodeBtns"+nID+"")) {
        if (document.getElementById("nodeBtns"+nID+"").style.display=='inline') {
            document.getElementById("nodeBtns"+nID+"").style.display='none';
            document.getElementById("nodeBtns"+nID+"edit").className='slGrey';
            document.getElementById("nodeBtnEdit"+nID+"").style.display='inline';
            
        } else {
            document.getElementById("nodeBtns"+nID+"").style.display='inline';
            document.getElementById("nodeBtns"+nID+"edit").className='slBlueDark';
            document.getElementById("nodeBtnEdit"+nID+"").style.display='none';
        }
    }
    return true;
});
$(document).on("click", ".adminNodeShowAdds", function() {
    var nID = $(this).attr("id").replace("showAdds", "");
    $("#nodeKids"+nID+"").slideDown("fast");
    $("#addChild"+nID+"").slideToggle("fast");
    if (document.getElementById("addSib"+nID+""))    $("#addSib"+nID+"").slideToggle("fast");
    if (document.getElementById("addSib"+nID+"B"))   $("#addSib"+nID+"B").slideToggle("fast");
    if (document.getElementById("addChild"+nID+"B")) $("#addChild"+nID+"B").slideToggle("fast");
    return true;
});
$(document).on("click", ".adminNodeShowMove", function() {
    var nID = $(this).attr("id").replace("showMove", "");
    document.getElementById("moveNodeID").value = nID;
    $(".nodeMover").slideToggle(0);
    document.getElementById("adminMenuExtra").style.position="fixed";
    document.getElementById("adminMenuExtra").innerHTML="<i>Moving Node #"+nID+"</i>";
    window.location="#n"+nID+"";
    return true;
});
$(document).on("click", ".adminNodeMoveTo", function() {
    @if (!$canEditTree) 
        alert("Sorry, you do not have permissions to actually edit the tree.");
    @else
        var nodeID = document.getElementById("moveNodeID").value;
        var loc = $(this).attr("id").replace("moveTo", "").split("ord");
        window.location="?all=1&alt=1&refresh=1&manip=1&moveNode="+nodeID+"&moveToParent="+loc[0]+"&moveToOrder="+loc[1]+"#n"+nodeID+""; 
    @endif
    return true;
});
