<!-- Stored in resources/views/survloop/formtree-form-feetinch.blade.php -->

{!! $nodePrompt !!}
<input type="hidden" name="n{{ $nID }}fld" id="n{{ $nID }}FldID" value="{{ $currNodeSessData }}">
<div class="nFld{{ $isOneLinerFld }}"><nobr>
    <select name="n{{ $nID }}fldFeet" id="n{{ $nID }}fldFeetID" 
        class="tinyDrop form-control{{ $inputMobileCls }} disIn" 
        onChange="return formChangeFeetInches('{{ $nID }}');" >
        @for ($i=0; $i<8; $i++)
            <option value="{{ $i }}" @if ($feet == $i) SELECTED @endif >{{ $i }}</option>
        @endfor
    </select> feet,
    </nobr><nobr>
    <select name="n{{ $nID }}fldInch" id="n{{ $nID }}fldInchID" 
        class="tinyDrop form-control{{ $inputMobileCls }} disIn" 
        onChange="return formChangeFeetInches('{{ $nID }}');" ><option value=""></option>
        @for ($i=0; $i<13; $i++)
            <option value="{{ $i }}" @if ($inch == $i) SELECTED @endif >{{ $i }}</option>
        @endfor
    </select> inches
</nobr></div>
