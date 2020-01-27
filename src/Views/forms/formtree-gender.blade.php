<!-- resources/views/survloop/forms/formtree-gender.blade.php -->

<div id="nLabel42" class="nPrompt">{!! $nodePromptText !!}</div>
@if ($nodeRow->node_opts%2 > 0) <div class="nFldFing mT20"> 
@else <div class="nFld{{ $isOneLiner }}"> 
@endif
@foreach ($responses as $j => $res)
    <label for="n{{ $nID }}fld{{ $j }}" id="n{{ $nID }}fld{{ $j }}lab" 
        data-nid="{{ $nID }}" class="
        @if (isset($xtraClass)) {{ $xtraClass }} @endif 
    @if ($nodeRow->node_opts%2 > 0)
        @if ($currNodeSessData == $res->node_res_value) fingerAct 
        @else finger 
        @endif "
        @if ($res->node_res_value == 'O')
            style="padding-top: 1px; padding-bottom: 1px;"
        @endif >
    @else
        mR10">
    @endif
        <nobr><div class="disIn mR5">
        <input name="n{{ $nID }}fld" id="n{{ $nID }}fld{{ $j }}" 
            value="{{ $res->node_res_value }}" autocomplete="off" 
            type="radio" class="slNodeClkGender" data-nid="{{ $nID }}" 
        @if ($res->node_res_value != 'O')
            onClick="document.getElementById('n{{ $nID }}fldOtherID2').value='';"
        @endif
        @if ($currNodeSessData == $res->node_res_value) CHECKED @endif >
        </div> {{ $res->node_res_eng }}</nobr>
    @if ($res->node_res_value == 'O')
        <input type="text" name="n{{ $nID }}fldOther{{ $j }}" 
            id="n{{ $nID }}fldOtherID{{ $j }}" value="{{ $currSessDataOther }}" 
            data-nid="{{ $nID }}" data-j="{{ $j }}" class="form-control disIn 
            ntrStp slTab otherFld slNodeKeyUpOther ui-autocomplete-input" 
            {!! $GLOBALS['SL']->tabInd() !!} >
    @endif
    </label>
@endforeach
</div>
