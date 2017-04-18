<!-- resources/views/vendor/survloop/formtree-form-end.blade.php -->

<script type="text/javascript">

function checkFullPage() {
    if (!document.getElementById('fullPageChk')) {
        @if ($GLOBALS['SL']->treeIsAdmin)
            window.location = '/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $pageURL }}';
        @else
            window.location = '/u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $pageURL }}';
        @endif
    }
    return true;
}
setTimeout("checkFullPage()", 10);

@forelse ($pageFldList as $fld)
    addFld("{{ $fld }}");
@empty
@endforelse

{!! $pageJSextra !!}

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
    firstNodeError = 0;
    return true; 
}

$(function() {
        
    function runFormSub() {
    @if ($hasRegisterNode) 
        document.postNode.submit();
    @else 
        blurAllFlds();
        var formData = new FormData(document.getElementById("postNodeForm"));
        document.getElementById("ajaxWrap").innerHTML='<div id="ajaxWrapLoad" class="container">{!! $spinner !!}</div>';
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
        var upID = getUpID( $(this).attr("id") );
        if (document.getElementById("up"+upID+"Info").style.display == 'none') {
            document.getElementById("up"+upID+"Info").style.display = 'block';
            document.getElementById("up"+upID+"InfoEdit").style.display = 'none';
        }
        else {
            document.getElementById("up"+upID+"Info").style.display = 'none';
            document.getElementById("up"+upID+"InfoEdit").style.display = 'block';
        }
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
    @if ($loopRootJustLeft > 0)
        document.getElementById("jumpToID").value="{{ $loopRootJustLeft }}";
    @endif
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
    
    {!! $pageAJAX !!}

});

setTimeout("hasAttemptedSubmit = false", 10);

@if ($hasFixedHeader)
    var mainFixed = function(){
        if (document.getElementById('fixedHeader')) {
            var fixer = $('#fixedHeader');
            var scrollMin = 40;
            if ($(window).width() <= 480) scrollMin = 30;
            if ($(this).scrollTop() >= scrollMin) fixer.addClass('fixed');
            $(document).scroll(function(){
                if ($(this).scrollTop() >= scrollMin) fixer.addClass('fixed');
                else fixer.removeClass('fixed');
            });
        }
    }
    $(document).ready(mainFixed);
@endif

</script>
</form>