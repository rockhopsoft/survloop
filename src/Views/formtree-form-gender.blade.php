<!-- Stored in resources/views/survloop/formtree-form-gender.blade.php -->

<div id="nLabel42" class="nPrompt">{!! $nodeRow->NodePromptText !!}</div>
@if ($nodeRow->NodeOpts%2 > 0) <div class="nFldFing mT20"> @else <div class="nFld{{ $isOneLiner }}"> @endif
@foreach ($coreResponses as $j => $res)
    <label for="n{{ $nID }}fld{{ $j }}" id="n{{ $nID }}fld{{ $j }}lab"
    @if ($nodeRow->NodeOpts%2 > 0)
        class=" @if ($currNodeSessData == $res[0]) fingerAct @else finger @endif "
            @if ($res[0] == 'O') style="padding-top: 1px; padding-bottom: 1px;" @endif >
            <nobr><div class="disIn mR5">
                <input id="n{{ $nID }}fld{{ $j }}" name="n{{ $nID }}fld" value="{{ $res[0] }}" type="radio" 
                    autocomplete="off" onClick="formClickGender({{ $nID }}); checkNodeUp({{ $nID }}, {{ $j }}, 1);"
    @else
        class="mR10"><nobr><div class="disIn mR5">
            <input name="n{{ $nID }}fld" id="n{{ $nID }}fld{{ $j }}" value="{{ $res[0] }}" autocomplete="off" 
                type="radio" onClick="formClickGender({{ $nID }}); checkNodeUp({{ $nID }}, {{ $j }}, 0); 
                @if ($res[0] != 'O') document.getElementById('n{{ $nID }}fldOtherID').value=''; @endif "
    @endif
    @if ($currNodeSessData == $res[0]) CHECKED @endif ></div> {{ $res[1] }}</nobr>
    @if ($res[0] == 'O')
        <input type="text" name="n{{ $nID }}fldOther" id="n{{ $nID }}fldOtherID" value="{{ $currSessDataOther }}" 
            class="form-control disIn otherGender" onKeyUp="formKeyUpOther({{ $nID }}, {{ $j }});">
    @endif
    </label>
@endforeach
</div>
