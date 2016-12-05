<!-- resources/views/vendor/survloop/formtree-form-end.blade.php -->

<script type="text/javascript">

function checkFullPage() {
    if (!document.getElementById('fullPageChk')) window.location = '/u/{{ $pageURL }}';
    return true;
}
setTimeout("checkFullPage()", 10);

@forelse ($pageFldList as $fld)
    addFld("{{ $fld }}");
@empty
@endforelse

{!! $pageJSextra !!}

var hasAttemptedSubmit = false;
function checkNodeForm() {
    if (document.getElementById("stepID").value == "back") return true;
    hasAttemptedSubmit = true;
    totFormErrors=0; formErrorsEng = "";
{!! $pageJSvalid !!}
    if (totFormErrors > 0) {
        setFormErrs();
        return false;
    }
    clearFormErrs();
    return true; 
}
function checkNodeUp() {
    if (hasAttemptedSubmit) checkNodeForm();
    return true;
}

$(function() {
        
    function runFormSub() {
        blurAllFlds();
        var formData = new FormData(document.getElementById("postNodeForm"));
        document.getElementById("ajaxWrap").innerHTML='<div id="ajaxWrapLoad" class="container f48"><i class="fa fa-spinner fa-pulse"></i></div>';
        window.scrollTo(0, 0);
        $.ajax({
            url: "/sub", 
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
        return false;
    }
    
@if (sizeof($pageHasUpload) > 0)
    $("#nFormUpload").click(function() {
        if (checkNodeForm()) {
            document.getElementById("stepID").value="upload";
            return runFormSub();
        }
        else return false;
    });
    function getUpID(thisAttr) {
        return thisAttr.replace("delLoopItem", "").replace("confirmN", "").replace("confirmY", "").replace("editLoopItem", "");
    }
    $(".nFormUploadSave").click(function() {
        document.getElementById("stepID").value="uploadSave";
        document.getElementById("altID").value=getUpID( $(this).attr("id") );
        return runFormSub();
    });
    $(document).on("click", ".nFormLnkEdit", function() {
        //alert('nFormLnkEdit clicked');
        var upID = getUpID( $(this).attr("id") );
        $("#up"+upID+"Info").slideToggle("fast");
        $("#up"+upID+"InfoEdit").slideToggle("fast");
        document.getElementById("up"+upID+"EditVisibID").value="1";
        return true;
    });
    $(document).on("click", ".nFormLnkDel", function() {
        $("#delLoopItem"+getUpID( $(this).attr("id") )+"confirm").slideDown("fast"); return true;
    });
    $(document).on("click", ".nFormLnkDelConfirmNo", function() {
        $("#delLoopItem"+getUpID( $(this).attr("id") )+"confirm").slideUp("fast"); return true;
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
            document.getElementById("jumpToID").value="{{ intVal($GLOBALS['DB']->closestLoop['obj']->DataLoopRoot) }}";
            runFormSub();
        }
        return false;
    }
    $(".nFormNext").click(function() { return exitLoop(""); });
    $("#nFormBack").click(function() { return exitLoop("Back"); });
    
@else

    $(".nFormNext").click(function() {
        if (checkNodeForm()) {
            document.getElementById("stepID").value="next";
            return runFormSub();
        }
        return false;
    });
    $("#nFormBack").click(function() {
        document.getElementById("stepID").value="back";
    @if ($loopRootJustLeft > 0)
        document.getElementById("jumpToID").value="{{ $loopRootJustLeft }}";
    @endif
        return runFormSub();
    });
    
@endif

    $(document).on("click", "a.navJump", function() {
        document.getElementById("jumpToID").value = $(this).attr("id").replace("jump", "");
        @if (isset($GLOBALS["DB"]->closestLoop["obj"]->DataLoopRoot) && intVal($GLOBALS["DB"]->closestLoop["obj"]->DataLoopRoot) > 0)
            document.getElementById("stepID").value="exitLoopJump";
        @endif
        return runFormSub();
    });
    
    {!! $pageAJAX !!}

});
</script>
</form>
