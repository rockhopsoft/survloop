<!-- Stored in resources/views/survloop/forms/formtree-datetime.blade.php -->
{!! $nodePrompt !!}
<div class="nFld{{ $isOneLinerFld }}">
    <input type="text" name="n{{ $nID }}fld" id="n{{ $nID }}FldID" value="{{ $dateStr }}" {{ $onKeyUp }} 
        class="dateFld form-control form-control-lg disIn mR20 @if (isset($xtraClass)) {{ $xtraClass }} @endif " 
        data-nid="{{ $nID }}" {!! $GLOBALS["SL"]->tabInd() !!}>
    at <div class="disIn mL20">{!! $formTime !!}</div>
</div>