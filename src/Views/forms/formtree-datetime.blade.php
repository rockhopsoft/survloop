<!-- resources/views/survloop/forms/formtree-datetime.blade.php -->
{!! $curr->nodePrompt !!}
<div class="nFld {{ $curr->isOneLinerFld }}">
    <input name="n{{ $curr->nID }}fld" id="n{{ $curr->nID }}FldID" 
        type="text" value="{{ $curr->dateStr }}" {{ $curr->onKeyUp }} 
        class="dateFld form-control form-control-lg 
        @if (isset($curr->xtraClass)) {{ $curr->xtraClass }} @endif fL"
        data-nid="{{ $curr->nID }}" {!! $GLOBALS["SL"]->tabInd() !!} >
    <div class="fL pT15 pL30 pR30">at</div>
    <div class="fL">{!! $formTime !!}</div>
    <div class="fC"></div>
</div>