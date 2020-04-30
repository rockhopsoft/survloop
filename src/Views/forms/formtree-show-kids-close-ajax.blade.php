/* resources/views/survloop/forms/formtree-show-kids-close-ajax.blade.php */

    if (showKids) {
        $("#node{{ $curr->nIDtxt }}kids").slideDown("50");
    } else {
        $("#node{{ $curr->nIDtxt }}kids").slideUp("50");
    }
}
@if (in_array($curr->nodeType, ['Radio', 'Checkbox']))
    $(".n{{ $curr->nIDtxt }}fldCls").click(function(){
        checkAllNodeKids();
    });
@else
    $(document).on("change", "#n{{ $curr->nIDtxt }}FldID", function(){ 
        checkAllNodeKids();
    });
@endif
