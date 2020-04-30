/* resources/views/survloop/forms/formtree-button-show-kids-ajax.blade.php */

$(document).on("click", "#nBtn{{ $nIDtxt }}", function() {
    if (document.getElementById("node{{ $nIDtxt }}kids")) {
        if (!document.getElementById("node{{ $nIDtxt }}kids").style.display || document.getElementById("node{{ $nIDtxt }}kids").style.display=="") {
            document.getElementById("node{{ $nIDtxt }}kids").style.display="none";
        }
        if (document.getElementById("node{{ $nIDtxt }}kids").style.display=="none") {
            kidsVisible("{{ $nIDtxt }}", "{{ $nSffx }}", true);
            kidsDisplaySkip("{{ $nIDtxt }}", "{{ $nSffx }}", true);
            $("#node{{ $nIDtxt }}kids").slideDown("50");
        } else {
            $("#node{{ $nIDtxt }}kids").slideUp("50");
            kidsVisible("{{ $nIDtxt }}", "{{ $nSffx }}", false);
            setTimeout(function() {
                kidsDisplaySkip("{{ $nIDtxt }}", "{{ $nSffx }}", false); 
            }, 100);
        }
    }
});