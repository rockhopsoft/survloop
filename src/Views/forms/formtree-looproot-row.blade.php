<!-- resources/views/vendor/survloop/forms/formtree-looproot-row.blade.php -->
@if ($node->isStepLoop())
    <a id="editLoopItem{{ $itemID }}" href="javascript:;"
        data-loop-id="{{ $itemID }}" data-loop-ind="{{ $setIndex }}"
        class="btn btn-secondary btn-lg btn-block taL mB20 editLoopItem">
        <table border="0" class="w100" ><tr>
        @if (trim($ico) != '')
            <td class="vaT w5 pR10">
                <span class="editLoopIco 
                    @if (strpos($ico, 'gryC') !== false) slBlueFaint 
                    @else slBlueDark @endif ">
                    {!! $ico !!}</span>
            </td>
            <td class="vaT w95">
        @else
            <td class="vaT w100">
        @endif
                {!! $itemLabel !!}
            </td>
        </tr></table>
    </a>
@else 
    <div class="wrapLoopItem">
        <div class="nodeAnchor"><a name="item{{ $setIndex }}"></a></div>
        <div id="wrapItem{{ $itemID }}On" class="slCard nodeWrap">
            <h4 class="mT0">{!! $itemLabel !!}</h4>
        @if ($canEdit)
            <div class="mT5">
                <a href="javascript:;" id="editLoopItem{{ $itemID }}" 
                    data-loop-id="{{ $itemID }}" data-loop-ind="{{ $setIndex }}" 
                    class="editLoopItem btn btn-secondary loopItemBtn"
                    ><i class="fa fa-pencil fa-flip-horizontal"></i> Edit</a>
                <a href="javascript:;" id="delLoopItem{{ $itemID }}" 
                    class="delLoopItem nFormLnkDel nobld btn btn-secondary loopItemBtn"
                    data-item-id="{{ $itemID }}" data-item-label="{{ strip_tags($itemLabel) }}"
                    ><i class="fa fa-trash-o"></i> Delete</a>
            </div>
        @endif
        </div>
    </div>
@endif