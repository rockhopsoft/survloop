<!-- resources/views/survloop/forms/formtree-date-picker.blade.php -->

{!! $curr->nodePrompt !!}
<div class="nFld {!! $curr->isOneLinerFld !!}">
    <input name="n{{ $curr->nIDtxt }}fld" id="n{{ $curr->nIDtxt }}FldID" 
        data-nid="{{ $curr->nID }}" value="{{ $curr->dateStr }}" 
        {!! $curr->onKeyUp !!} type="text" autocomplete="off" 
        class="dateFld form-control form-control-lg {!! $curr->xtraClass !!}"
        {!! $GLOBALS["SL"]->tabInd() !!} >
</div>
