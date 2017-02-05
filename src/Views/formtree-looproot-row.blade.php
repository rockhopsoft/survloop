<!-- resources/views/vendor/survloop/formtree-looproot-row.blade.php -->

<div class="wrapLoopItem"><a name="item{{ $setIndex }}"></a>
    <div id="wrapItem{{ $itemID }}On">
        <div class="row brd round20 mB20">
            @if ($ico != '')
                <div class="col-md-1 p20">
                    <a href="javascript:;" id="arrowLoopItem{{ $itemID }}" class="editLoopItem slBlueLight f24"
                        >{!! $ico !!}</a>
                </div>
            @endif
            @if ($node->isStepLoop())
                <div class=" @if ($ico != '') col-md-9 @else col-md-10 @endif p20">
                    @if ($itemLabel == $GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular . ' #' . (1+$setIndex))
                        <h2 class="m0">{!! $itemLabel !!}</h2>
                    @else
                        <h2 class="m0">{{ $GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular }} #{{ (1+$setIndex) }}: 
                            {!! $itemLabel !!}</h2>
                    @endif
                </div>
                <div class="col-md-2 p20 taC">
                    @if (strtolower($itemLabel) != 'you')
                        <a href="javascript:;" id="editLoopItem{{ $itemID }}" class="editLoopItem btn btn-default"
                            ><i class="fa fa-pencil fa-flip-horizontal"></i> Edit</a>
                    @endif
                </div>
            @else
                <div class=" @if ($ico != '') col-md-7 @else col-md-8 @endif p20">
                    @if ($itemLabel == $GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular . ' #' . (1+$setIndex))
                        <h2 class="m0">{!! $itemLabel !!}</h2>
                    @else
                        <h2 class="m0">{{ $GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular }} #{{ (1+$setIndex) }}: 
                            {!! $itemLabel !!}</h2>
                    @endif
                </div>
                <div class="col-md-2 p20 taC">
                    @if (strtolower($itemLabel) != 'you')
                        <a href="javascript:;" id="delLoopItem{{ $itemID }}" 
                            class="delLoopItem nFormLnkDel nobld btn btn-default"
                            ><i class="fa fa-times"></i> Delete</a>
                        <input type="checkbox" class="disNon" 
                            name="delItem[]" id="delItem{{ $itemID }}" value="{{ $itemID }}" >
                    @endif
                </div>
                <div class="col-md-2 p20 taC">
                    @if (strtolower($itemLabel) != 'you')
                        <a href="javascript:;" id="editLoopItem{{ $itemID }}" class="editLoopItem btn btn-default"
                            ><i class="fa fa-pencil fa-flip-horizontal"></i> Edit</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
    @if (!$node->isStepLoop())
        <div id="wrapItem{{ $itemID }}Off" class="wrapItemOff">
            <i class="mR20">Deleted: {{ $GLOBALS["DB"]->closestLoop["obj"]->DataLoopSingular }} #{{ (1+$setIndex) }}: 
                {!! $itemLabel !!}</i> 
            <a href="javascript:;" id="unDelLoopItem{{ $itemID }}" class="unDelLoopItem nFormLnkEdit f14 nobld mL20"
                ><i class="fa fa-undo"></i> Undo</a>
        </div>
    @endif
</div>
