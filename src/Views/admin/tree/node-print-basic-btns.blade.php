<!-- resources/views/vendor/survloop/admin/tree/node-print-basic-btns.blade.php -->

@if (!$isPrint && $canEditTree)
    <a href="javascript:void(0)" id="showBtns{{ $nID }}" class="adminNodeShowBtns disIn slBlueLight mL10"
        ><i class="fa fa-dot-circle-o" aria-hidden="true"></i></a>
    <div id="nodeBtns{{ $nID }}" class="disNon">
        <nobr><a href="/dashboard/tree/map/node/{{ $nID }}" class="slBlueLight mL10">
            <i class="fa fa-pencil"></i> Edit This Node</a></nobr>
        <nobr><a href="javascript:void(0)" id="showAdds{{ $nID }}" 
            class="adminNodeShowAdds slBlueLight mL10"><i class="fa fa-plus-square-o"></i> 
            Add Related Node</a></nobr>
        <nobr><a href="javascript:void(0)" id="showMove{{ $nID }}" 
            class="adminNodeShowMove slBlueLight mL10"><i class="fa fa-arrows-alt"></i> 
            Move This Node Anywhere</a></nobr>
    </div>
@endif

@if (!$isPrint) 
    @if (sizeof($tierNode[1]) > 0)
        <div class="disBlo mBn10">
            <a href="#n{{ $nID }}" id="adminNode{{ $nID }}Expand" class="adminNodeExpand slBlueLight noUnd"
                ><i class="fa fa-expand fa-flip-horizontal"></i></a> 
            @if ($node->hasShowKids && !$isAlt)
                <i class="fa fa-code-fork fa-flip-vertical mL5 f16 blk" 
                    title="Children displayed only with certain responses"></i>
            @endif
        </div>
    @endif
@endif