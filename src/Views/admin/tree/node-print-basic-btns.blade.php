@if ((!isset($isPrint) || !$isPrint))
    @if ($canEditTree)
        <div class="disIn mL10">
            <a  @if ($GLOBALS['SL']->treeRow->tree_type == 'Survey XML'
                    || (isset($GLOBALS["SL"]->x["isXmlMap"]) && $GLOBALS["SL"]->x["isXmlMap"]))
                    href="/dashboard/surv-{{ $node->nodeRow->node_tree }}/xmlmap/node/{{ $nID }}" 
                @else
                    href="/dashboard/surv-{{ $node->nodeRow->node_tree }}/map/node/{{ $nID }}" 
                @endif >
                <span id="nodeBtns{{ $nID }}edit" class="slGrey" >
                <i class="fa fa-pencil disIn" id="nodeBtnEdit{{ $nID }}"></i></span></a>
            <div id="nodeBtns{{ $nID }}" class="disNon">
                <nobr><a class="btn btn-sm btn-secondary"
                    @if ($GLOBALS['SL']->treeRow->tree_type == 'Survey XML')
                        href="/dashboard/surv-{{ $node->nodeRow->node_tree }}/xmlmap/node/{{ $nID }}" 
                    @else
                        href="/dashboard/surv-{{ $node->nodeRow->node_tree }}/map/node/{{ $nID }}" 
                    @endif
                    ><i class="fa fa-pencil"></i>Edit This Node</a></nobr>
                <nobr><a href="javascript:;" id="showAdds{{ $nID }}" 
                    class="btn btn-sm btn-secondary adminNodeShowAdds mL10"
                    ><i class="fa fa-plus-square-o"></i> Add Related Node</a></nobr>
                <nobr><a href="javascript:;" id="showMove{{ $nID }}" 
                    class="btn btn-sm btn-secondary adminNodeShowMove mL10"
                    ><i class="fa fa-arrows-alt"></i> Move This Node Anywhere</a></nobr>
            </div>
        </div>
    @endif
@else
    <div class="mTn5"></div>
@endif