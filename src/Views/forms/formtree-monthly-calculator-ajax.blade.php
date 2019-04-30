/* resources/views/survloop/forms/formtree-monthly-calculator-ajax.blade.php */

function update{{ $nIDtxt }}Monthly() {
    if (!document.getElementById("n{{ $nIDtxt }}FldID")) return false;
    var newTot = 0;
    for (var i = 1; i < 13; i++) {
        var fldName = "month{{ $nIDtxt }}ly"+i+"ID";
        if (document.getElementById(fldName)) newTot += (1*document.getElementById(fldName).value);
    }
    document.getElementById("n{{ $nIDtxt }}FldID").value = newTot;
    @if (isset($extraJS) && trim($extraJS) != '') {!! $extraJS !!} @endif
    return true;
}
$(document).on("click", "#monthlyCalcTot{{ $nIDtxt }}", function() { update{{ $nIDtxt }}Monthly(); });
