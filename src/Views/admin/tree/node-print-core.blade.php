<!-- resources/views/vendor/survloop/admin/tree/node-print-core.blade.php -->

<div class="nodeAnchor"><a name="n{{ $nID }}"></a></div>

@if ($canEditTree && $node->nodeRow->node_parent_id > 0)
    <div id="addSib{{ $nID }}" class="disNon pT10 pB10 
        @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
        <span class="slBlueDark fPerc125"><i class="fa fa-chevron-right"></i></span> 
        <a href="/dashboard/surv-{{ $GLOBALS['SL']->treeID }}/xmlmap/node/-37/?parent={{ $node->nodeRow->node_parent_id }}&ordBefore={{ $nID }}"
            ><i class="fa fa-plus-square-o"></i> Add Sibling Node</a>
    </div>
    @if ($node->nodeRow->node_parent_order == 0)
        <div class="nodeMover disNon pT5 pB5 @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
            <span class="slBlueDark"><i class="fa fa-chevron-right"></i></span> 
            <a href="javascript:;" class="adminNodeMoveTo" 
                id="moveTo{{ $node->nodeRow->node_parent_id }}ord{{ $node->nodeRow->node_parent_order }}"
                ><i class="fa fa-bullseye"></i> Move Node Here</a>
        </div>
    @endif
@endif

<div id="nPrintWrap{{ $nID }}" 
    class=" @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
    
    <a id="showBtns{{ $nID }}" class="btn btn-primary circleBtn circleBtn0 editXml"
        href="javascript:;">#{{ $nID }}</a> 
    
    <span class="mR10">{{ strip_tags($node->nodeRow->node_prompt_text) }}</span> 
    
    <div class="editXml">
        <nobr>{!! view(
            'vendor.survloop.admin.tree.node-print-basic-btns', 
            [
                "nID"         => $nID,
                "node"        => $node,
                "tierNode"    => $tierNode, 
                "canEditTree" => $canEditTree
            ]
        )->render() !!}</nobr>
    </div>
    
    @if (!$REQ->has('print'))
        <div id="addChild{{ $nID }}" 
            class="disNon @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} @else basicTier9 @endif ">
            <span class="slBlueDark fPerc125"><i class="fa fa-chevron-right"></i></span> 
            <a href="/dashboard/surv-{{ $node->nodeRow->node_tree 
                }}/xmlmap/node/-37/?parent={{ $nID }}&start=1"
                ><i class="fa fa-plus-square-o"></i> Add Child Node</a>
        </div>
    @endif
    @if (sizeof($tierNode[1]) > 0)
        <div id="nodeKids{{ $nID }}" class=" @if (session()->get('adminOverOpts')%2 == 0 
            || $nID == $rootID) disBlo @else disNon @endif">
        
            {!! $childrenPrints !!}
            
        </div>
        <div id="addChild{{ $nID }}B" 
            class="disNon @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} @else basicTier9 @endif ">
            <span class="slBlueDark fPerc125"><i class="fa fa-chevron-right"></i></span> 
            <a href="/dashboard/surv-{{ $node->nodeRow->node_tree 
                }}/xmlmap/node/-37/?parent={{ $nID }}&end=1"
                ><i class="fa fa-plus-square-o"></i> Add Child Node</a>
        </div>
    @else
        <div class="nodeMover disNon pT5 pB5 
            @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} @else basicTier9 @endif ">
            <span class="slBlueDark"><i class="fa fa-chevron-right"></i></span> 
            <a href="javascript:;" class="adminNodeMoveTo" 
            id="moveTo{{ $nID }}ord0"><i class="fa fa-bullseye"></i> Move Node Here</a>
        </div>
        <div class="pT5"></div>
    @endif
    
</div> <!-- end nPrintWrap{{ $nID }} -->

@if ($node->nodeRow->node_parent_id > 0 && !$REQ->has('print')) 
    <div id="addSib{{ $nID }}B" class="disNon pT10 pB10 
        @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
        <span class="slBlueDark fPerc125"><i class="fa fa-chevron-right"></i></span> 
        <a href="/dashboard/surv-{{ $node->nodeRow->node_tree }}/xmlmap/node/-37/?parent={{ 
            $node->nodeRow->node_parent_id }}&ordAfter={{ $nID }}"
            ><i class="fa fa-plus-square-o"></i> Add Sibling Node</a>
    </div>
    <div class="nodeMover disNon pT5 pB5 
        @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
        <span class="slBlueDark"><i class="fa fa-chevron-right"></i></span> 
        <a id="moveTo{{ $node->nodeRow->node_parent_id 
            }}ord{{ (1+$node->nodeRow->node_parent_order) }}"
            class="adminNodeMoveTo" href="javascript:;"
            ><i class="fa fa-bullseye"></i> Move Node Here</a>
    </div>
@endif
