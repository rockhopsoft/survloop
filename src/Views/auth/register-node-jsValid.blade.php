/* resources/views/auth/register-node-jsValid.blade.php */

@if (isset($GLOBALS["SL"]->sysOpts["user-name-ask"]) && $GLOBALS["SL"]->sysOpts["user-name-ask"] == 'On'
    && $GLOBALS["SL"]->sysOpts["user-name-optional"] == 'Off')
    if (document.getElementById('nameID').value.trim() == '') {
        setFormLabelRed('004');
        totFormErrors++;
    } else {
        setFormLabelBlack('004');
    }
@endif

@if (!isset($GLOBALS["SL"]->sysOpts["user-email-optional"]) || $GLOBALS["SL"]->sysOpts["user-email-optional"] == 'Off')
    if (!reqFormEmail('emailID') || document.getElementById('emailID').value.trim() == '') {
        setFormLabelRed('001'); 
        totFormErrors++;
    } else {
@else
    if (reqFormEmail('emailID') && document.getElementById('emailID').value.trim() != '') {
@endif
@if ($coreID > 0)
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
            if (chkData == 'found')
            {
                document.getElementById('emailBlockID').value = 1;
                setFormLabelRed('001'); 
                totFormErrors++;
                //document.getElementById('emailWarning').style.display='block';
                $("#emailWarning").slideDown("fast");
            }
            else
            {
                document.getElementById('emailBlockID').value = 0;
                setFormLabelBlack('001');
            }
        }
    });
@endif
}
if (document.getElementById('password') && document.getElementById('password_confirmation'))
{
    if (document.getElementById('password').value.trim() == '' 
        || document.getElementById('password').value.trim().length < 6
        || document.getElementById('password').value != document.getElementById('password_confirmation').value)
    {
        setFormLabelRed('002');
        setFormLabelRed('003');
        totFormErrors++;
    }
    else
    {
        setFormLabelBlack('002');
        setFormLabelBlack('003');
    }
}

@if (isset($GLOBALS["SL"]->sysOpts["user-email-optional"]) && $GLOBALS["SL"]->sysOpts["user-email-optional"] == 'On')
    if (totFormErrors == 0 && (!reqFormEmail('emailID') || document.getElementById('emailID').value.trim() == '')) {
        @if ($coreID > 0)
            document.getElementById('emailID').value = 'no.{{ $coreID }}.email@noemail.org';
        @else
            document.getElementById('emailID').value = 'no.email.'+document.getElementById('nameID').value+'@noemail.org';
        @endif
    }
@endif
