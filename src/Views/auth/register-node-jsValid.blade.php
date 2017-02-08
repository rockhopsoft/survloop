/* resources/views/auth/register-node-jsValid.blade.php */

if (!reqFormEmail('emailID') || document.getElementById('emailID').value.trim() == '') 
{
    setFormLabelRed('001'); 
    totFormErrors++;
}
else
{
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
}
if (document.getElementById('password') && document.getElementById('password_confirmation'))
{
    if (document.getElementById('password').value.trim() == '' || document.getElementById('password').value.trim().length < 6
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