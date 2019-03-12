<!-- Stored in resources/views/survloop/forms/formtree-gender.blade.php -->

<div id="nLabel42" class="nPrompt">{!! $nodePromptText !!}</div>
@if ($nodeRow->NodeOpts%2 > 0) <div class="nFldFing mT20"> @else <div class="nFld{{ $isOneLiner }}"> @endif
@foreach ($coreResponses as $j => $res)
    <label for="n{{ $nID }}fld{{ $j }}" id="n{{ $nID }}fld{{ $j }}lab" data-nid="{{ $nID }}" class="
    @if (isset($xtraClass)) {{ $xtraClass }} @endif 
    @if ($nodeRow->NodeOpts%2 > 0)
        @if ($currNodeSessData == $res[0]) fingerAct @else finger @endif "
            @if ($res[0] == 'O') style="padding-top: 1px; padding-bottom: 1px;" @endif >
            <nobr><div class="disIn mR5">
                <input id="n{{ $nID }}fld{{ $j }}" name="n{{ $nID }}fld" value="{{ $res[0] }}" type="radio" 
                    autocomplete="off" onClick="formClickGender({{ $nID }});"
    @else
        mR10"><nobr><div class="disIn mR5">
            <input name="n{{ $nID }}fld" id="n{{ $nID }}fld{{ $j }}" value="{{ $res[0] }}" autocomplete="off" 
                type="radio" onClick="formClickGender({{ $nID }}); 
                @if ($res[0] != 'O') document.getElementById('n{{ $nID }}fldOtherID').value=''; @endif "
    @endif
    @if ($currNodeSessData == $res[0]) CHECKED @endif ></div> {{ $res[1] }}</nobr>
    @if ($res[0] == 'O')
        <input type="text" name="n{{ $nID }}fldOther{{ $j }}" id="n{{ $nID }}fldOtherID{{ $j }}" value="{{ 
            $currSessDataOther }}" class="form-control form-control-lg disIn ntrStp slTab otherFld" onKeyUp="formKeyUpOther('{{ 
            $nID }}', {{ $j }});" {!! $GLOBALS['SL']->tabInd() !!}>
    @endif
    </label>
@endforeach
</div>
