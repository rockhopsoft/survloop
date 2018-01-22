/* resources/views/vendor/survloop/formtree-form-ajax.blade.php */
function runFormSub() {
@if ($hasRegisterNode || $GLOBALS['SL']->treeRow->TreeType == 'Page') 
    document.postNode.submit();
@else 
    blurAllFlds();
    var formData = new FormData(document.getElementById("postNodeForm"));
    document.getElementById("ajaxWrap").innerHTML=getSpinnerAjaxWrap();
    window.scrollTo(0, 0);
    $.ajax({
    @if ($GLOBALS['SL']->treeIsAdmin)
        url: "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/dash/sub", 
    @else
        url: "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/sub", 
    @endif
        type: "POST", 
        data: formData, 
        contentType: false,
        processData: false,
        success: function(data) {
            $("#ajaxWrap").empty();
            $("#ajaxWrap").append(data);
        }, 
        error: function(xhr, status, error) {
            $("#ajaxWrap").append("<div>(error - "+xhr.responseText+")</div>");
        }
    });
    resetCheckForm();
@endif
    return false;
}

function getUpID(thisAttr) {
    return thisAttr.replace("delLoopItem", "").replace("confirmN", "").replace("confirmY", "").replace("editLoopItem", "");
}

@if (sizeof($pageHasUpload) > 0)
$("#nFormUpload").click(function() {
    if (checkNodeForm()) {
        document.getElementById("stepID").value="upload";
        return runFormSub();
    } else {
        return false;
    }
});
$(".nFormUploadSave").click(function() {
    document.getElementById("stepID").value="uploadSave";
    document.getElementById("altID").value=getUpID( $(this).attr("id") );
    return runFormSub();
});
@endif

@if (sizeof($pageHasUpload) > 0 || $isLoopRoot)
$(".editLoopItem").click(function() {
    var id = $(this).attr("id").replace("editLoopItem", "").replace("arrowLoopItem", "");
    document.getElementById("loopItemID").value=id;
    return runFormSub();
});
var limitTog = false;
function toggleLineEdit(upID) {
    if (!limitTog) {
        limitTog = true;
        setTimeout(function() { limitTog = false; }, 700);
        if (document.getElementById("up"+upID+"InfoEdit").style.display != 'block') {
            $("#up"+upID+"Info").slideUp("fast");
            setTimeout(function() { $("#up"+upID+"InfoEdit").slideDown("fast"); }, 301);
            document.getElementById("up"+upID+"EditVisibID").value="0";
        } else {
            $("#up"+upID+"InfoEdit").slideUp("fast");
            setTimeout(function() { $("#up"+upID+"Info").slideDown("fast"); }, 301);
            document.getElementById("up"+upID+"EditVisibID").value="1";
        }
    }
    return true;
}
$(document).on("click", ".nFormLnkEdit", function() {
    return toggleLineEdit(getUpID( $(this).attr("id") ));
});
$(document).on("click", ".nFormLnkDel", function() {
    var upID = getUpID( $(this).attr("id") );
    $("#editLoopItem"+upID+"block").slideUp("fast");
    $("#delLoopItem"+upID+"confirm").slideDown("fast");
    return true;
});
$(document).on("click", ".nFormLnkDelConfirmNo", function() {
    var upID = getUpID( $(this).attr("id") );
    $("#delLoopItem"+upID+"confirm").slideUp("fast");
    $("#editLoopItem"+upID+"block").slideDown("fast");
    return true;
});
$(document).on("click", ".nFormLnkDelConfirmYes", function() {
    document.getElementById("stepID").value="uploadDel";
    document.getElementById("altID").value=getUpID( $(this).attr("id") );
    return runFormSub();
});
@endif

@if ($isLoopRoot)

function exitLoop(whichWay) {
    if (checkNodeForm()) {
        document.getElementById("stepID").value="exitLoop"+whichWay;
        document.getElementById("jumpToID").value="{{ intVal($GLOBALS['SL']->closestLoop['obj']->DataLoopRoot) }}";
        runFormSub();
    }
    return false;
}
$(".nFormNext").click(function() { return exitLoop(""); });
$(".nFormBack").click(function() { return exitLoop("Back"); });

@else

$(".nFormNext").click(function() {
    if (checkNodeForm()) {
        document.getElementById("stepID").value="next";
        return runFormSub();                                                                     
    }
    return false;
});
$(".nFormBack").click(function() {
    document.getElementById("stepID").value="back";
@if ($loopRootJustLeft > 0) document.getElementById("jumpToID").value="{{ $loopRootJustLeft }}"; @endif
    return runFormSub();
});

@endif

$(document).on("click", "a.navJump", function() {
    document.getElementById("jumpToID").value = $(this).attr("id").replace("jump", "");
    @if (isset($GLOBALS['SL']->closestLoop["obj"]->DataLoopRoot) 
        && intVal($GLOBALS['SL']->closestLoop["obj"]->DataLoopRoot) > 0)
        document.getElementById("stepID").value="exitLoopJump";
    @endif
    return runFormSub();
});

window.onpopstate = function(event) {
    var newPage = document.location.href;
    newPage = newPage.replace("{{ $GLOBALS['SL']->sysOpts['app-url'] }}", "");
    if (document.getElementById("stepID")) document.getElementById("stepID").value = "save";
    if (document.getElementById("popStateUrlID")) document.getElementById("popStateUrlID").value = newPage;
    return runFormSub();
};

@if (isset($currPage) && isset($currPage[0]) && trim($currPage[0]) != '')
    setTimeout(function() { if (!document.getElementById('main')) { window.location='{{ $currPage[0] }}'; } }, 10);
@endif