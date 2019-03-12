function chkSubRes{{ $nIDtxt }}j{{ $j }}() {
    if (document.getElementById("n{{ $nIDtxt }}fld{{ $j }}").checked) {
        setSubResponses({{ $nID }}, "{{ $nSffx }}res{{ $j }}", true, new Array({{ $grankids }})); 
        $("#n{{ $nIDtxt }}fld{{ $j }}sub").slideDown("fast");
    } else {
        $("#n{{ $nIDtxt }}fld{{ $j }}sub").slideUp("fast");
        setTimeout( function() {
            setSubResponses({{ $nID }}, "{{ $nSffx }}res{{ $j }}", false, new Array({{ $grankids }}));
        }, 500);
    }
    return true;
}
$(document).on("click", "#n{{ $nIDtxt }}fld{{ $j }}", function() { chkSubRes{{ $nIDtxt }}j{{ $j }}(); });