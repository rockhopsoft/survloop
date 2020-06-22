<!-- resources/views/survloop/forms/formtree-gender.blade.php -->

<div id="nLabel42" class="nPrompt">{!! $curr->nodePromptText !!}</div>
@if ($curr->nodeRow->node_opts%2 > 0) <div class="nFldFing mT20"> 
@else <div class="nFld{{ $curr->isOneLiner }}"> 
@endif
@foreach ($curr->responses as $j => $res)
    <label for="n{{ $curr->nIDtxt }}fld{{ $j }}" 
        id="n{{ $curr->nIDtxt }}fld{{ $j }}lab" 
        data-nid="{{ $curr->nIDtxt }}" class="
        @if (isset($curr->xtraClass)) {{ $curr->xtraClass }} @endif 
    @if ($curr->nodeRow->node_opts%2 > 0)
        @if ($curr->sessData == $res->node_res_value) fingerAct 
        @else finger 
        @endif "
        @if ($res->node_res_value == 'O')
            style="padding-top: 1px; padding-bottom: 1px;"
        @endif >
    @else
        mR10">
    @endif
        <nobr><div class="disIn mR5">
        <input name="n{{ $curr->nIDtxt }}fld" 
            id="n{{ $curr->nIDtxt }}fld{{ $j }}" 
            value="{{ $res->node_res_value }}" autocomplete="off" 
            type="radio" class="slNodeClkGender" data-nid="{{ $curr->nIDtxt }}" 
        @if ($res->node_res_value != 'O')
            onClick="if (document.getElementById('n{{ 
                $curr->nIDtxt }}fldOtherID2')) { document.getElementById('n{{ 
                $curr->nIDtxt }}fldOtherID2').value=''; }"
        @endif
        @if ($curr->sessData == $res->node_res_value) CHECKED @endif >
        </div> {{ $res->node_res_eng }}</nobr>
    @if ($res->node_res_value == 'O')
        <input type="text" name="n{{ $curr->nIDtxt }}fldOther{{ $j }}" 
            id="n{{ $curr->nIDtxt }}fldOtherID{{ $j }}" value="{{ $sessDataOther }}" 
            data-nid="{{ $curr->nIDtxt }}" data-j="{{ $j }}" class="form-control disIn 
            ntrStp slTab otherFld slNodeKeyUpOther ui-autocomplete-input" 
            {!! $GLOBALS['SL']->tabInd() !!} >
    @endif
    </label>
@endforeach
</div>
