/* resources/views/vendor/survloop/js/formtree.blade.php */
function checkFullPage() {
    if ((!document.getElementById('main') || !document.getElementById('sysJs')) && !document.getElementById('isPrint')) {
@if (isset($currPage) && isset($currPage[0]) && trim($currPage[0]) != '')
        window.location="{{ $currPage[0] }}";
@else
        window.location = "{{ $GLOBALS['SL']->getCurrTreeUrl() }}";
@endif
    }
    if (document.getElementById('maincontentWrap')) document.getElementById('maincontentWrap').style.display = 'block';
    return true;
}
setTimeout("checkFullPage()", 10);
setTimeout("checkFullPage()", 100);
setTimeout("checkFullPage()", 1000);
setTimeout("checkFullPage()", 5000);
setTimeout("checkFullPage()", 10000);

@if ($GLOBALS["SL"]->treeRow->tree_type == 'Survey' 
    || $GLOBALS["SL"]->treeRow->tree_opts%19 == 0 
    || $GLOBALS["SL"]->treeRow->tree_opts%53 == 0)

@forelse ($pageFldList as $fld)
addFld("{{ $fld }}");
@empty
@endforelse
{!! $pageJSvalid !!}
setTimeout("hasAttemptedSubmit = false", 10);
setTimeout("lastSlTabIndex = {{ $GLOBALS['SL']->currTabInd }}", 300);

@endif