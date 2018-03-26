/* resources/views/vendor/survloop/formtree-form-js.blade.php */
function checkFullPage() {
    if (!document.getElementById('hidFrameID')) {
@if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
    @if ($GLOBALS['SL']->treeIsAdmin)
        window.location = "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}";
    @else
        window.location = "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/{{ $GLOBALS['SL']->treeRow->TreeSlug }}";
    @endif
@else
    @if ($GLOBALS['SL']->treeIsAdmin)
        window.location = "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $pageURL }}";
    @else
        window.location = "{{ $GLOBALS['SL']->sysOpts['app-url'] }}/u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $pageURL }}";
    @endif
@endif
    }
    return true;
}
setTimeout("checkFullPage()", 10);
@forelse ($pageFldList as $fld)
addFld("{{ $fld }}");
@empty
@endforelse
function checkNodeForm() {
    if (document.getElementById("stepID") && document.getElementById("stepID").value == "back") return true;
    hasAttemptedSubmit = true;
    totFormErrors = 0; formErrorsEng = ""; firstNodeError = 0;
    {!! $pageJSvalid !!}
    if (totFormErrors > 0) {
        setFormErrs();
        return false;
    }
    clearFormErrs();
    return true; 
}

setTimeout("hasAttemptedSubmit = false", 10);

@if ($hasFixedHeader)
    var mainFixed = function(){
        if (document.getElementById('fixedHeader')) {
            var fixer = $('#fixedHeader');
            var scrollMin = 40;
            if ($(window).width() <= 480) scrollMin = 30;
            if ($(this).scrollTop() >= scrollMin) fixer.addClass('fixed');
            $(document).scroll(function(){
                if ($(this).scrollTop() >= scrollMin) fixer.addClass('fixed');
                else fixer.removeClass('fixed');
            });
        }
    }
    $(document).ready(mainFixed);
@endif

setTimeout("lastSlTabIndex = {{ $GLOBALS['SL']->currTabInd }}", 1000);