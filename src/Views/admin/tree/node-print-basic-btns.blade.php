@if ((!isset($isPrint) || !$isPrint))
    @if ($canEditTree)
        <div class="disIn mL10">
            <a  @if ($GLOBALS['SL']->treeRow->TreeType == 'Primary Public XML')
                    href="/dashboard/tree-{{ $node->nodeRow->NodeTree }}/xmlmap/node/{{ $nID }}" 
                @else
                    href="/dashboard/tree-{{ $node->nodeRow->NodeTree }}/map/node/{{ $nID }}" 
                @endif >
                <span id="nodeBtns{{ $nID }}edit" class="slGrey" >
                <i class="fa fa-pencil" id="nodeBtnEdit{{ $nID }}" class="disIn"></i></span></a>
            <div id="nodeBtns{{ $nID }}" class="disNon">
                <nobr><a class="btn btn-xs btn-default"
                @if ($GLOBALS['SL']->treeRow->TreeType == 'Primary Public XML')
                    href="/dashboard/tree-{{ $node->nodeRow->NodeTree }}/xmlmap/node/{{ $nID }}" 
                @else
                    href="/dashboard/tree-{{ $node->nodeRow->NodeTree }}/map/node/{{ $nID }}" 
                @endif ><i class="fa fa-pencil"></i>
                     Edit This Node</a></nobr>
                <nobr><a href="javascript:;" id="showAdds{{ $nID }}" 
                    class="btn btn-xs btn-default adminNodeShowAdds mL10"><i class="fa fa-plus-square-o"></i> 
                    Add Related Node</a></nobr>
                <nobr><a href="javascript:;" id="showMove{{ $nID }}" 
                    class="btn btn-xs btn-default adminNodeShowMove mL10"><i class="fa fa-arrows-alt"></i> 
                    Move This Node Anywhere</a></nobr>
            </div>
        </div>
    @endif
@else
    <div class="mTn5"></div>
@endif