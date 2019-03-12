/* resources/views/auth/register-ajax-zxcvbn.blade.php */
$("#password").keyup(function() {
    if (document.getElementById('passStrng')) {
        var textValue = $(this).val();
        var result = zxcvbn(textValue);
        $("#passStrng").removeClass("slGreenDark").removeClass("slGreenDark").removeClass("warnOn").removeClass("red");
        if (result.score == 4) {
            $("#passStrng").html("very strong");
            $("#passStrng").addClass("slGreenDark");
        } else if (result.score == 3) {
            $("#passStrng").html("strong");
            $("#passStrng").addClass("slGreenDark");
        } else if (result.score == 2) {
            $("#passStrng").html("so-so");
            $("#passStrng").addClass("warnOn");
        } else if (result.score == 1) {
            $("#passStrng").html("weak");
            $("#passStrng").addClass("red");
        } else {
            $("#passStrng").html("very weak");
            $("#passStrng").addClass("red");
        }
    }
});