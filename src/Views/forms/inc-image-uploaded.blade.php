<!-- resources/views/vendor/survloop/forms/inc-image-uploaded.blade.php -->
<i class="fa fa-check" aria-hidden="true"></i> Uploaded...
<script type="text/javascript"> $(document).ready(function(){
    setTimeout( function() {
        document.getElementById("dialogBody").innerHTML=getSpinnerAjaxWrap();
        $("#dialogBody").load("/ajax/img-sel?nID={{ $nID }}&presel={{ 
            urlencode($presel) }}&newUp={{ urlencode($imgID) 
            }}");
    }, 500);
}); </script>