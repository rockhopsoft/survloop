<!-- resources/views/vendor/survloop/formtree-looproot-row.blade.php -->

@if ($node->isStepLoop())
    <a id="editLoopItem{{ $itemID }}" class="btn btn-secondary btn-xl w100 taL mB20 editLoopItem" href="javascript:;">
    @if (trim($ico) != '')
        <span class=" @if (strpos($ico, 'gryC') !== false) slBlueFaint @else slBlueLight @endif "
            >{!! $ico !!}</span>
    @endif
    {!! $itemLabel !!}</a>
@else 
    <div class="wrapLoopItem"><a name="item{{ $setIndex }}"></a>
        <div id="wrapItem{{ $itemID }}On" class="brdLgt round20 mB20 pL20 pR20">
            <div class="row">
                <div class="col-12 p20">
                    <div class="fL"><h3 class="m0">{!! $itemLabel !!}</h3></div>
                    @if ($canEdit)
                        <a href="javascript:;" id="editLoopItem{{ $itemID }}" 
                            class="editLoopItem btn btn-secondary mL10 mR10 fR"
                            ><i class="fa fa-pencil fa-flip-horizontal"></i> Edit</a>
                        <a href="javascript:;" id="delLoopItem{{ $itemID }}" 
                            class="delLoopItem nFormLnkDel nobld btn btn-secondary mL10 mR10 fR"
                            ><i class="fa fa-trash-o"></i> Delete</a>
                        <input type="checkbox" class="disNon" 
                            name="delItem[]" id="delItem{{ $itemID }}" value="{{ $itemID }}" >
                    @endif
                    <div class="fC"></div>
                </div>
            </div>
        </div>
        @if (!$node->isStepLoop())
            <div id="wrapItem{{ $itemID }}Off" class="wrapItemOff brdA round20 mB20">
                <i class="mR20 fL">Deleted: {{ $GLOBALS['SL']->closestLoop["obj"]->DataLoopSingular }} 
                    #{{ (1+$setIndex) }}: {!! $itemLabel !!}</i> 
                <a href="javascript:;" id="unDelLoopItem{{ $itemID }}" class="unDelLoopItem nFormLnkEdit mL20 fR"
                    ><i class="fa fa-undo"></i> Undo</a>
                <div class="fC"></div>
            </div>
        @endif
    </div>
@endif