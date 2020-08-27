<!-- resources/views/survloop/forms/formtree-checkbox.blade.php -->

<div id="n{{ $curr->nIDtxt }}fld{{ $j }}labWrap" class="w100">
@if ($mobileCheckbox)
    <label for="n{{ $curr->nIDtxt }}fld{{ $j }}" 
        id="n{{ $curr->nIDtxt }}fld{{ $j }}lab" 
        class=" @if ($boxChecked) fingerAct @else finger @endif ">
        <div class="disIn mR5">
            <input id="n{{ $curr->nIDtxt }}fld{{ $j }}" 
                value="{!! $res->node_res_value !!}" 
                type="{{ strtolower($curr->nodeType) }}" 
                {!! $resNameCheck . $respKids . $onClickFull !!}
                class="slNodeChange" autocomplete="off"
                {!! $GLOBALS["SL"]->tabInd() !!} >
        </div>
        {!! $res->node_res_eng !!} 
        {!! $otherFld[2] !!}
    </label>
@else
    <div class="{{ $curr->isOneLinerFld }}"> 
        @if (strlen($res) < 40) <nobr> @endif
        <label for="n{{ $curr->nIDtxt }}fld{{ $j }}" class="mR10">
            <div class="disIn mR5">
                <input id="n{{ $curr->nIDtxt }}fld{{ $j }}" 
                    value="{!! $res->node_res_value !!}" 
                    type="{{ strtolower($curr->nodeType) }}" 
                    {!! $resNameCheck . $respKids. $onClickFull !!} 
                    class="slNodeChange" autocomplete="off"
                    {!! $GLOBALS["SL"]->tabInd() !!} >
            </div>
            {!! $res->node_res_eng !!}
            {!! $otherFld[2] !!}
        </label>
        @if (strlen($res) < 40) </nobr> @endif
    </div>
@endif
</div>