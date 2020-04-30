<!-- resources/views/survloop/forms/formtree-feetinch.blade.php -->

{!! $curr->nodePrompt !!}
<input name="n{{ $curr->nIDtxt }}fld" id="n{{ $curr->nIDtxt }}FldID" 
    type="hidden" value="{{ $curr->sessData }}">
<div class="nFld{{ $curr->isOneLinerFld }}"><nobr>
    <select name="n{{ $curr->nIDtxt }}fldFeet" id="n{{ $curr->nIDtxt }}fldFeetID" 
        data-nid-txt="{{ $curr->nIDtxt }}"
        class="tinyDrop form-control form-control-lg formChangeFeetInches
        @if (isset($curr->xtraClass)) {{ $curr->xtraClass }} @endif " 
        style="display: inline;" {!! $GLOBALS["SL"]->tabInd() !!} >
        @for ($i=0; $i<8; $i++)
            <option value="{{ $i }}" @if ($feet == $i) SELECTED @endif
                >{{ $i }}</option>
        @endfor
    </select> feet,
    </nobr><nobr>
    <select name="n{{ $curr->nIDtxt }}fldInch" id="n{{ $curr->nIDtxt }}fldInchID" 
        data-nid-txt="{{ $curr->nIDtxt }}"
        class="tinyDrop form-control form-control-lg formChangeFeetInches
        @if (isset($curr->xtraClass)) {{ $curr->xtraClass }} @endif 
        formChangeFeetInches" style="display: inline;"
        {!! $GLOBALS["SL"]->tabInd() !!} >
        <option value=""></option>
        @for ($i=0; $i<13; $i++)
            <option value="{{ $i }}" @if ($inch == $i) SELECTED @endif 
                >{{ $i }}</option>
        @endfor
    </select> inches
</nobr></div>
