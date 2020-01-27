<!-- resources/views/survloop/forms/formtree-feetinch.blade.php -->

{!! $nodePrompt !!}
<input name="n{{ $nIDtxt }}fld" id="n{{ $nIDtxt }}FldID" 
    type="hidden" value="{{ $currNodeSessData }}">
<div class="nFld{{ $isOneLinerFld }}"><nobr>
    <select name="n{{ $nIDtxt }}fldFeet" id="n{{ $nIDtxt }}fldFeetID" data-nid-txt="{{ $nIDtxt }}"
        class="tinyDrop form-control form-control-lg formChangeFeetInches
        @if (isset($xtraClass)) {{ $xtraClass }} @endif " 
        style="display: inline;" {!! $GLOBALS["SL"]->tabInd() !!} >
        @for ($i=0; $i<8; $i++)
            <option value="{{ $i }}" @if ($feet == $i) SELECTED @endif >{{ $i }}</option>
        @endfor
    </select> feet,
    </nobr><nobr>
    <select name="n{{ $nIDtxt }}fldInch" id="n{{ $nIDtxt }}fldInchID" data-nid-txt="{{ $nIDtxt }}"
        class="tinyDrop form-control form-control-lg formChangeFeetInches
        @if (isset($xtraClass)) {{ $xtraClass }} @endif formChangeFeetInches" 
        style="display: inline;" {!! $GLOBALS["SL"]->tabInd() !!} >
        <option value=""></option>
        @for ($i=0; $i<13; $i++)
            <option value="{{ $i }}" @if ($inch == $i) SELECTED @endif >{{ $i }}</option>
        @endfor
    </select> inches
</nobr></div>
