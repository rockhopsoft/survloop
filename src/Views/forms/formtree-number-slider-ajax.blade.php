/* resources/views/survloop/forms/formtree-number-slider-ajax.blade.php */

$("#n{{ $nIDtxt }}slider").slider({ @if (isset($curr->extraOpts["incr"]) && intVal($curr->extraOpts["incr"]) > 0) step: {!! $curr->extraOpts["incr"] !!}, @endif
    change: function( event, ui ) {
        var newVal = $("#n{{ $nIDtxt }}slider").slider("value");
        document.getElementById("n{{ $nIDtxt }}FldID").value=newVal;
    }
});
$(document).on("keyup", "#n{{ $nIDtxt }}FldID", function() { 
    var newVal = document.getElementById("n{{ $nIDtxt }}FldID").value;
    $("#n{{ $nIDtxt }}slider").slider("value", newVal);
}); 
setTimeout(function() {
    var newVal = document.getElementById("n{{ $nIDtxt }}FldID").value;
    $("#n{{ $nIDtxt }}slider").slider("value", newVal);
}, 5); 
