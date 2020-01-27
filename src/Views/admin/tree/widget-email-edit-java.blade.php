/* resources/views/vendor/survloop/admin/tree/widget-email-edit-java.blade.php */
var emailListIDs = new Array();
@forelse ($emailList as $i => $email)
    emailListIDs[emailListIDs.length] = {{ $email->email_id }};
@empty
@endforelse
function changeWidgetEmailDef(newID) {
    for (var i=0; i<emailListIDs.length; i++) {
        if (emailListIDs[i] != newID && document.getElementById('previewEmail'+emailListIDs[i]+'')) {
            document.getElementById('previewEmail'+emailListIDs[i]+'').style.display='none';
        }
    }
    if (document.getElementById('previewEmail'+newID+'')) {
        document.getElementById('previewEmail'+newID+'').style.display='block';
    }
    if (document.getElementById('previewEmailDump1')) {
        if (newID == -69) document.getElementById('previewEmailDump1').style.display='block';
        else document.getElementById('previewEmailDump1').style.display='none';
    }
    return true;
}