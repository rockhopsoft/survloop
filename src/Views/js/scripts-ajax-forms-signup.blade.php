/* generated from resources/views/vendor/survloop/js/scripts-ajax-forms-signup.blade.php */


function checkNodeFormSignup() {
@if (isset($GLOBALS["SL"]->sysOpts["user-email-optional"]) && $GLOBALS["SL"]->sysOpts["user-email-optional"] == 'On')
    var emailRequired = false;
@else var emailRequired = true; @endif
    hasAttemptedSubmit = true;
    totFormErrors = 0;
    formErrorsEng = "";
@if (isset($GLOBALS["SL"]->sysOpts["user-name-req"]) && intVal($GLOBALS["SL"]->sysOpts["user-name-req"]) == 1)
    if (document.getElementById('nameID').value.trim() == '') {
        setFormLabelRed('004');
        totFormErrors++;
    } else {
        setFormLabelBlack('004');
    }
@endif
    if (emailRequired && (!reqFormEmail('emailID') || document.getElementById('emailID').value.trim() == '')) {
        setFormLabelRed('001'); 
        totFormErrors++;
    } else if (reqFormEmail('emailID') && document.getElementById('emailID').value.trim() != '') {
        document.getElementById('emailWarning').style.display='none';
        $.ajax({
            url: "/chkEmail?"+$("#emailID").serialize(),
            type: 'GET',
            async: false,
            cache: false,
            timeout: 30000,
            error: function(){
                return true;
            },
            success: function(chkData){ 
                if (chkData == 'found') {
                    if (document.getElementById('emailBlockID')) {
                        document.getElementById('emailBlockID').value = 1;
                    }
                    setFormLabelRed('001'); 
                    totFormErrors++;
                    //document.getElementById('emailWarning').style.display='block';
                    $("#emailWarning").slideDown("fast");
                } else {
                    if (document.getElementById('emailBlockID')) {
                        document.getElementById('emailBlockID').value = 0;
                    }
                    setFormLabelBlack('001');
                }
            }
        });
    }
    if (document.getElementById('password') && document.getElementById('password_confirmation')) {
        var pass1 = document.getElementById('password').value;
        if (pass1 == '' || pass1.length < 8 || pass1 != document.getElementById('password_confirmation').value) {
            setFormLabelRed('002');
            setFormLabelRed('003');
            totFormErrors++;
        } else {
            setFormLabelBlack('002');
            setFormLabelBlack('003');
        }
    }
    if (totFormErrors > 0) {
        setFormErrs();
        return false;
    }
    if (!emailRequired && (!reqFormEmail('emailID') || document.getElementById('emailID').value.trim() == '')) {
        document.getElementById('emailID').value = 'no.email.'+document.getElementById('nameID').value+'@noemail.org';
    }
    clearFormErrs();
    if (document.getElementById('nameID').value.trim() == '') {
        document.getElementById('nameID').value=document.getElementById('emailID').value;
    }
    if (document.getElementById("loadAnimSignup") && document.getElementById("loadAnimClickedSignup")) {
        document.getElementById("loadAnimSignup").style.display="none";
        document.getElementById("loadAnimClickedSignup").style.display="block";
    }
    return true;
}
$(document).on("click", ".nFormSignupSubBtn", function() {
    pressedSubmit = true;
    return checkNodeFormSignup();
});




function checkNodeFormResetPass() {
    hasAttemptedSubmit = true;
    totFormErrors = 0;
    formErrorsEng = "";
    if (!reqFormEmail('emailID') || document.getElementById('emailID').value.trim() == '') {
        setFormLabelRed('001'); 
        totFormErrors++;
    }
    if (document.getElementById('password') && document.getElementById('password_confirmation')) {
        var pass1 = document.getElementById('password').value;
        if (pass1 == '' || pass1.length < 8 || pass1 != document.getElementById('password_confirmation').value) {
            setFormLabelRed('002');
            setFormLabelRed('003');
            totFormErrors++;
        } else {
            setFormLabelBlack('002');
            setFormLabelBlack('003');
        }
    }
    if (totFormErrors > 0) {
        setFormErrs();
        return false;
    }
    clearFormErrs();
    if (document.getElementById("loadAnimResetPass") && document.getElementById("loadAnimClickedResetPass")) {
        document.getElementById("loadAnimResetPass").style.display="none";
        document.getElementById("loadAnimClickedResetPass").style.display="block";
    }
    return true;
}
$(document).on("click", "#loadAnimBtnResetPass", function() {
    pressedSubmit = true;
    return checkNodeFormResetPass();
});




/*
function checkNodeFormPassResetEmail() {
    hasAttemptedSubmit = true;
    totFormErrors = 0;
    formErrorsEng = "";
    if ((!reqFormEmail('emailID') || document.getElementById('emailID').value.trim() == '')
     || !reqFormEmail('emailID')) {
        setFormLabelRed('001'); 
        totFormErrors++;
    }
    if (totFormErrors > 0) {
        setFormErrs();
        return false;
    }
    setFormLabelBlack('001');
    if (document.getElementById("loadAnimPassResetEmail") && document.getElementById("loadAnimClickedPassResetEmail")) {
        document.getElementById("loadAnimPassResetEmail").style.display="none";
        document.getElementById("loadAnimClickedPassResetEmail").style.display="block";
    }
    return true;
}
$(document).on("click", "#loadAnimBtnResetPassEmail", function() {
    pressedSubmit = true;
    return checkNodeFormPassResetEmail();
});
*/
