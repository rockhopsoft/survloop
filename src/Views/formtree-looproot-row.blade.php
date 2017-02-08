<!-- resources/views/vendor/survloop/formtree-looproot-row.blade.php -->

<div class="wrapLoopItem"><a name="item{{ $setIndex }}"></a>
    <div id="wrapItem{{ $itemID }}On" class="brdLgt round20 mB20 pL20 pR20">
        <div class="row">
            @if ($ico != '')
                <div class="col-md-1 pT15 pL20">
                    <a href="javascript:;" id="arrowLoopItem{{ $itemID }}" class="editLoopItem f24
                        @if (strpos($ico, 'gryC') !== false) gryC @else slBlueLight @endif ">{!! $ico !!}</a>
                </div>
            @endif
            @if ($node->isStepLoop())
                <div class="fL p20"><h2 class="m0">
                @if (strtolower(strip_tags($itemLabel)) == 'you')
                    {{ $GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular }} #{{ (1+$setIndex) }}: You</h2></div>
                @else
                    @if ($itemLabel == $GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular . ' #' . (1+$setIndex))
                        {!! $itemLabel !!}
                    @else
                        {{ $GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular }} #{{ (1+$setIndex) }}:
                        {!! $itemLabel !!}
                    @endif
                    </h2></div>
                    <a href="javascript:;" id="editLoopItem{{ $itemID }}" 
                        class="editLoopItem btn btn-default m10 mT20 fR"
                        ><i class="fa fa-pencil fa-flip-horizontal"></i> Edit</a>
                @endif
            @else
                <div class="fL p20"><h2 class="m0">
                @if (strtolower(strip_tags($itemLabel)) == 'you')
                    {{ $GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular }} #{{ (1+$setIndex) }}: You</h2></div>
                @else
                    @if ($itemLabel == $GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular . ' #' . (1+$setIndex))
                        {!! $itemLabel !!}
                    @else
                        {{ $GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular }} #{{ (1+$setIndex) }}: 
                        {!! $itemLabel !!}</h2>
                    @endif
                    </h2></div>
                    @if (strtolower(strip_tags($itemLabel)) != 'you')
                        <a href="javascript:;" id="editLoopItem{{ $itemID }}" 
                            class="editLoopItem btn btn-default m10 mT20 fR"
                            ><i class="fa fa-pencil fa-flip-horizontal"></i> Edit</a>
                        <a href="javascript:;" id="delLoopItem{{ $itemID }}" 
                            class="delLoopItem nFormLnkDel nobld btn btn-default m10 mT20 fR"
                            ><i class="fa fa-times"></i> Delete</a>
                        <input type="checkbox" class="disNon" 
                            name="delItem[]" id="delItem{{ $itemID }}" value="{{ $itemID }}" >
                    @endif
                @endif
                <div class="fC"></div>
            @endif
        </div>
    </div>
    @if (!$node->isStepLoop())
        <div id="wrapItem{{ $itemID }}Off" class="wrapItemOff brdA round20 mB20">
            <i class="mR20">Deleted: {{ $GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular }} #{{ (1+$setIndex) }}: 
                {!! $itemLabel !!}</i> 
            <a href="javascript:;" id="unDelLoopItem{{ $itemID }}" class="unDelLoopItem nFormLnkEdit f14 nobld mL20"
                ><i class="fa fa-undo"></i> Undo</a>
        </div>
    @endif
</div>
