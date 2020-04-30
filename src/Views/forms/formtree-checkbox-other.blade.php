<!-- resources/views/survloop/forms/formtree-checkbox-other.blade.php -->
<input type="text" name="n{{ $curr->nIDtxt }}fldOther{{ $j }}" 
    id="n{{ $curr->nIDtxt }}fldOtherID{{ $j }}" 
    class="form-control ntrStp slTab otherFld slNodeKeyUpOther mL10"
    value="{!! $val !!}" {!! $GLOBALS["SL"]->tabInd() !!} >