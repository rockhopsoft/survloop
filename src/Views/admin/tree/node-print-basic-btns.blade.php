<!-- resources/views/vendor/survloop/admin/tree/node-print-basic-btns.blade.php -->

@if ((!isset($isPrint) || !$isPrint) && $canEditTree)
    <a href="/dashboard/tree-{{ $node->nodeRow->NodeTree }}/map/node/{{ $nID }}" class="mL10">
        <span id="nodeBtns{{ $nID }}edit" class="slGrey" ><span class="fPerc66">#{{ $nID }}</span>
        <i class="fa fa-pencil"></i></span></a>
    <div id="nodeBtns{{ $nID }}" class="disNon">
        <nobr><a href="/dashboard/tree-{{ $node->nodeRow->NodeTree }}/map/node/{{ $nID }}">
             Edit This Node</a></nobr>
        <nobr><a href="javascript:void(0)" id="showAdds{{ $nID }}" 
            class="adminNodeShowAdds mL10"><i class="fa fa-plus-square-o"></i> 
            Add Related Node</a></nobr>
        <nobr><a href="javascript:void(0)" id="showMove{{ $nID }}" 
            class="adminNodeShowMove mL10"><i class="fa fa-arrows-alt"></i> 
            Move This Node Anywhere</a></nobr>
    </div>
@endif

@if (!isset($isPrint) || !$isPrint) 
    @if (sizeof($tierNode[1]) > 0)
        <div class="disBlo mBn10">
            <a href="#n{{ $nID }}" id="adminNode{{ $nID }}Expand" class="adminNodeExpand noUnd"
                ><i class="fa fa-expand fa-flip-horizontal"></i></a> 
            @if (isset($node->hasShowKids) && $node->hasShowKids && !$isAlt)
                <i class="fa fa-code-fork fa-flip-vertical mL5 f16 blk" 
                    title="Children displayed only with certain responses"></i>
            @endif
        </div>
    @endif
@endif