@if ((!isset($isPrint) || !$isPrint) && sizeof($tierNode[1]) > 0)
    <div class="fR disBlo w1 mBn5 zind0"><nobr>
        <a href="#n{{ $nID }}" id="adminNode{{ $nID }}Expand" class="adminNodeExpand noUnd"
            ><i class="fa fa-expand fa-flip-horizontal"></i></a> 
        @if (isset($node->hasShowKids) && $node->hasShowKids && !$isAlt)
            <i class="fa fa-code-fork fa-flip-vertical fPerc66 dbColor" 
                title="Children displayed only with certain responses"></i>
        @endif
    </nobr></div>
@endif