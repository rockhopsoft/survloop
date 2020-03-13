<!-- resources/views/survloop/forms/formtree-datetime.blade.php -->
{!! $nodePrompt !!}
<div class="nFld{{ $isOneLinerFld }}">
    <input type="text" name="n{{ $nID }}fld" id="n{{ $nID }}FldID" value="{{ $dateStr }}" {{ $onKeyUp }} 
        class="dateFld form-control form-control-lg 
        @if (isset($xtraClass)) {{ $xtraClass }} @endif fL"
        data-nid="{{ $nID }}" {!! $GLOBALS["SL"]->tabInd() !!} >
    <div class="fL pT15 pL30 pR30">at</div>
    <div class="fL">{!! $formTime !!}</div>
    <div class="fC"></div>
</div>