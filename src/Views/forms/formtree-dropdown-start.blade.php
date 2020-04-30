<!-- resources/views/survloop/forms/formtree-dropdown-start.blade.php -->

{!! $curr->nodePrompt !!}
<div class="nFld{{ $curr->isOneLinerFld }}">
    <select name="n{{ $curr->nIDtxt }}fld" id="n{{ $curr->nIDtxt }}FldID" 
        data-nid="{{ $curr->nID }}" {!! $curr->onChange !!}  
        class="form-control form-control-lg {{ $curr->xtraClass }}" 
        {!! $GLOBALS["SL"]->tabInd() !!} autocomplete="off">
        <option class="slGrey" value=""
            @if (trim($curr->sessData) == '' || $curr->isDropdownTagger()) 
                SELECTED 
            @endif >
            @if (isset($curr->nodeRow->node_text_suggest) 
                && trim($curr->nodeRow->node_text_suggest) != '')
                {!! $curr->nodeRow->node_text_suggest !!}
            @else 
                select...
            @endif
        </option>