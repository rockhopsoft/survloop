<!-- resources/views/vendor/survloop/forms/formtree-looproot-nav.blade.php -->

@if ($addingLoopItem > 0)
    {!! $GLOBALS["SL"]->spinner() !!}
@endif
<div id="loopNav{{ $nID }}" class="nPrompt"
    @if ($addingLoopItem > 0) style="display: none;" @endif >
    <input type="hidden" id="isLoopNav" name="loopNavRoot" 
        value="{{ intVal($loop->data_loop_root) }}">
    @if ($node->isStepLoop()) 
        <div id="isStepLoop"></div>
    @endif
    @if (!$node->isStepLoop() && $currLoopSize == 0)
        <div class="pT15">
            <h4><span class="slGrey">
                No {{ strtolower($loopName) }} added yet.
            </span></h4>
        </div>
    @else
        <div class="pT15"></div>
    @endif
    @if ($currLoopSize > 0)
        @if (!$node->isStepLoop() && $currLoopSize > 10)
            <div class="mTn15 mB20">{!! $addBtn !!}</div>
        @endif
        {!! $loopRows !!}
    @endif
    @if (!$node->isStepLoop())
        @if ($loopMaxLimit <= 0 || $currLoopSize < $loopMaxLimit)
            {!! $addBtn !!}
            @if ($loopMaxLimit > 0 
                && $currLoopSize > $loop->data_loop_warn_limit)
                <div class="slGrey pT20">
                    Limit of {{ $loopMaxLimit }} {{ $loopName }}
                </div>
            @endif
            <div class="p20"></div>
        @endif
    @endif
</div> <!-- loopNav{{ $nID }} -->
<div class="p20"></div>
