/* resources/views/survloop/forms/formtree-page-focus-java.blade.php */

@if (intVal($charLimit) > 0)
    setTimeout("focusNodeID({{ $charLimit }}).focus()", 100);
@elseif (trim($page1stVisib) != '' && intVal($charLimit) == 0)
    function setPageFocus1stVisib() {
        if (document.getElementById('{{ $page1stVisib }}')) {
            document.getElementById('{{ $page1stVisib }}').focus();
        }
    }
    setTimeout("setPageFocus1stVisib()", 100);
@endif
