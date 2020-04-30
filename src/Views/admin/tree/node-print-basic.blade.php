<!-- resources/views/vendor/survloop/admin/tree/node-print-basic.blade.php -->

@if (isset($node->nodeRow->node_type) && (!$REQ->has('opts') || strpos($REQ->opts, 'noData') === false 
    || (!$node->isDataManip() && $node->nodeRow->node_type != 'Spambot Honey Pot')))

    @if ($node->nodeRow->node_type == 'Layout Column')
        <div class="col-{{ $node->nodeRow->node_char_limit }}">
    @endif

    <div class="nodeAnchor"><a id="n{{ $nID }}" name="n{{ $nID }}"></a></div>
    
    @if ($canEditTree && $node->nodeRow->node_parent_id > 0 && !$isPrint)
        <div id="addSib{{ $nID }}" class="disNon pT10 pB10 
            @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
            <a href="/dashboard/surv-{{ $node->nodeRow->node_tree }}/map/node/-37/?parent={{ 
                $node->nodeRow->node_parent_id }}&ordBefore={{ $nID }}"
                class="btn btn-sm btn-warning opac50 w100 mTn15"
                ><i class="fa fa-plus-square-o"></i> Add Sibling Node</a>
        </div>
        @if ($node->nodeRow->node_parent_order == 0)
            <div class="nodeMover disNon pT5 pB5 
                @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
                <a href="javascript:;" class="btn btn-sm btn-warning opac50 w100 mTn10 adminNodeMoveTo" 
                    id="moveTo{{ $node->nodeRow->node_parent_id }}ord{{ $node->nodeRow->node_parent_order }}"
                    ><i class="fa fa-bullseye"></i> Move Node Here</a>
            </div>
        @endif
    @endif
    
    <div id="nPrintWrap{{ $nID }}" class=" @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif
        @if ($node->isPage()) basicTierPage 
        @elseif ($node->isLoopRoot()) basicTierLoop 
        @elseif ($node->nodeRow->node_opts%89 == 0) slCard
        @else 
            @if ($node->isBranch()) basicTierBranch @endif
            @if (trim($node->nodeRow->node_data_branch) != '' || $node->nodeRow->node_parent_id <= 0) basicTierData @endif
        @endif
        @if (isset($conditionList) && strpos($conditionList, '#NodeDisabled') !== false) basicTierDisabled @endif ">
        
        @if ($node->isPrintBasicTine()) <div class="w100 opac50"> @endif
        <?php /* /dashboard/surv-{{ $node->nodeRow->node_tree }}/map/node/{{ $nID }} */ ?>
        <table class="w100 mTn5 mB5" cellpadding=0 cellspacing=0 border=0 ><tr><td>
            <a id="showBtns{{ $nID }}" href="javascript:;" class="circleBtn 
                @if ($node->isPage() || $node->isLoopRoot()) circleBtn0 
                @elseif ($node->isLoopCycle() || $node->isDataManip()) circleBtn3 
                @elseif ($node->isPrintBasicTine()) circleBtn2 
                @else circleBtn1 @endif ">{{ $nID }}</a>
        </td><td class="w100">
            {!! $nodeBtns !!}
            <span class="slGrey mL5">{!! $node->getIcon() !!} 
            @if ($node->isInstruct()) Content Chunk, WYSIWYG
            @elseif ($node->isInstructRaw()) Content Chunk, Hard-Coded HTML
            @elseif ($node->nodeRow->node_type == 'Layout Column') 
                <i>{{ $node->nodeRow->node_char_limit }}/12 Wide</i>
            @elseif ($node->isHnyPot()) 
                <span class="slGrey">Spambot Honey Pot (Only Visible to Robots)</span>
            @elseif ($node->isPage())
                Page 
                @if ($GLOBALS["SL"]->treeRow->tree_type != 'Page')
                    #{{ $pageCnt }}
                    @if ($node->nodeRow->node_opts%29 == 0)
                        <span class="red mL10"><i class="fa fa-sign-out" aria-hidden="true"></i> Exit</span>
                    @endif
                @endif
            @elseif ($node->isLoopRoot())
                Loop Root <b class="slGreenDark mL10">
                Repeat Child Pages For Each In {{ $node->nodeRow->node_default }}</b>
                <a target="_blank" class="infoOn mL10" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}{{ 
                    $node->extraOpts['page-url'] }}"><i class="fa fa-external-link" aria-hidden="true"></i> {!! 
                    $node->extraOpts["page-url"] !!}</a>
            @elseif ($node->isLoopCycle())
                Loop <b class="slGreenDark">Repeat For Each {{ 
                $GLOBALS['SL']->getLoopSingular(str_replace('LoopItems::', '', 
                    $node->nodeRow->node_response_set)) 
                }}</b>
            @else {{ $node->nodeRow->node_type }} @endif
            @if ($node->isSpreadTbl() 
                && isset($node->nodeRow->node_response_set) 
                && trim($node->nodeRow->node_response_set) != '')
                <span class="slGreenDark mL10"><i class="fa fa-refresh"></i> Add & Edit 
                    {{ str_replace('LoopItems::', '', $node->nodeRow->node_response_set) }}</span>
            @endif
            @if ($node->isRequired()) <span class="txtDanger" title="required">*</span> @endif
            @if ($isAlt)
                @if ($node->isOneLiner()) <span class="mL10">(Q&A on one line)</span> @endif
                @if ($node->isOneLineResponses()) <span class="mL10">(responses on one line)</span> @endif
            @endif
            </span>
            @if ($node->isPage())
                <a target="_blank" class="mL10 infoOn" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}{{ 
                    $node->extraOpts['page-url'] }}?preview=1">
                    <i class="fa fa-external-link" aria-hidden="true"></i> {!! $node->extraOpts["page-url"] !!}</a>
            @elseif ($node->isBranch()) 
                <span class="slBlueDark mL10">{{ $nodePromptText }}</span>
            @elseif ($node->isDataManip())
                <span class="slGreenDark">
                {{ $node->nodeRow->node_data_branch }}
                @if ($node->nodeRow->node_type == 'Data Manip: New') Record 
                @elseif ($node->nodeRow->node_type == 'Data Manip: Update') Record 
                @elseif ($node->nodeRow->node_type == 'Data Manip: Wrap') Table 
                @else Close User Session @endif
                {!! $dataManips !!} </span>
            @endif
        @if (trim($conditionList) != '') {!! $conditionList !!} @endif
        @if (!$REQ->has('opts') || strpos($REQ->opts, 'noData') === false)
            <div class="float-right taR pL20 pR5 slGreenDark">
                @if (trim($node->nodeRow->node_data_branch) != '' 
                    || $node->nodeRow->node_parent_id <= 0)
                    @if ($node->nodeRow->node_parent_id <= 0)
                        <b>{{ $GLOBALS['SL']->coreTbl }}</b> 
                        <i class="fa fa-database fPerc80"></i><br />
                    @elseif ($node->isLoopRoot() 
                        && isset($GLOBALS['SL']->dataLoops[$node->nodeRow->node_default]))
                        {{ $GLOBALS['SL']->dataLoops[$node->nodeRow->node_default]->data_loop_table }}
                        <i class="fa fa-database fPerc80"></i><br />
                    @elseif (trim($node->nodeRow->node_data_branch) != '') 
                        {{ $node->nodeRow->node_data_branch }} 
                        <i class="fa fa-database fPerc80"></i><br />
                    @endif
                @endif
                @if (!$node->isDataManip() 
                    && trim($node->nodeRow->node_data_store) != '' 
                    && strpos($node->nodeRow->node_data_store, ':') !== false)
                    <span class="opac50"> {!! $node->getTblFldLink($isPrint) !!}</span>
                @endif
            </div>
        @endif
        </td></tr></table>
        
        @if ($node->isInstruct() || $node->isInstructRaw()) 
            @if (isset($nodePromptText) && trim($nodePromptText) != '')
                {!! strip_tags($nodePromptText) !!}
            @endif
            <?php /* <div class="nPrompt">{!! $instructPrint !!}</div> */ ?>
        @elseif ($node->isPage())
            <a target="_blank" class="mT0 mB10 infoOn" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}{{ 
                $node->extraOpts['page-url'] }}?preview=1"><h2 class="mT0 mB5"><i class="fa fa-file-text-o"></i>
                @if (isset($node->extraOpts["meta-title"])) {!! $node->extraOpts["meta-title"] !!} @endif </h2></a>
        @elseif ($node->isLoopRoot())
            <a target="_blank" class="mTn5 mB10 infoOn" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}{{ 
                $node->extraOpts['page-url'] }}"><h2 class="mT0 mB5"><i class="fa fa-file-text-o"></i>
                @if (isset($node->extraOpts["meta-title"])) {!! 
                    $node->extraOpts["meta-title"] !!} @endif </h2></a>
            @if (isset($nodePromptText) && trim($nodePromptText) != '')
                {{ strip_tags($nodePromptText) }}
            @endif
        @elseif ($node->isLoopCycle()) <span class="fPerc125">{{ $nodePromptText }}</span>
        @elseif ($node->isLoopSort())
            <span class="slBlueDark">Sort Loop Items:  
                {{ str_replace('LoopItems::', '', $node->nodeRow->node_response_set) }}</span>
        @elseif ($node->nodeRow->node_type == 'Spambot Honey Pot')
            <span class="slGrey">{{ $nodePromptText }}</span>
        @elseif ($node->nodeType == 'Send Email')
            <div>{{ $GLOBALS["SL"]->getEmailSubj($node->nodeRow->node_default) }}</div>
        @elseif (!$node->isLayout() && !$node->isBranch() && !$node->isDataManip())
            <div class="mL10">
            @if (trim($nodePromptText) != '')
                <span class=" @if ($node->isDataManip()) slGreenDark ital @endif
                    @if ($node->isLoopRoot()) slBlueDark @endif ">{{ $nodePromptText }}</span> 
            @elseif (in_array($node->nodeRow->node_type, ['Checkbox', 'Radio']) && !$isAlt)
                {!! view('vendor.survloop.admin.tree.node-print-basic-responses', [
                    "node" => $node ])->render() !!}
            @endif
            @if ($node->nodeRow->node_type == 'Big Button') 
                @if ($node->nodeRow->node_response_set == 'HTML') {!! $node->nodeRow->node_default !!}
                @else <span class="fPerc125 slBlueDark">{!! $node->nodeRow->node_default !!}</span> @endif
            @endif
            @if ($isAlt)
                @if (trim($node->nodeRow->node_prompt_notes) != '')
                    <div class="slGrey">{{ strip_tags($node->nodeRow->node_prompt_notes) }}</div>
                @endif
                @if (sizeof($node->responses) > 0)
                    {!! view(
                        'vendor.survloop.admin.tree.node-print-basic-responses', 
                        [ "node" => $node ]
                    )->render() !!}
                @endif
                @if (trim($node->nodeRow->node_internal_notes) != '')
                    <div class="slGrey"><i>{{ $node->nodeRow->node_internal_notes }}</i></div>
                @endif
            @endif
            </div>
        @endif
        
        @if ($node->isPrintBasicTine()) </div> @endif

        @if (!$isPrint)
            <div id="addChild{{ $nID }}" class="disNon 
                @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} 
                @else basicTier9 
                @endif ">
                <a href="/dashboard/surv-{{ $node->nodeRow->node_tree 
                    }}/map/node/-37/?parent={{ $nID }}&start=1"
                    class="btn btn-sm btn-warning mTn15"><i class="fa fa-plus-square-o"></i> Add Child Node</a>
            </div>
        @endif
        @if (sizeof($tierNode[1]) > 0)
            <div id="nodeKids{{ $nID }}" class=" 
                @if (session()->get('adminOverOpts')%2 == 0 || $nID == $rootID 
                    || $isPrint) disBlo @else disNon @endif">
                @if ($node->nodeRow->node_type == 'Layout Row') <div class="row"> @endif
                {!! $childrenPrints !!}
                @if ($node->nodeRow->node_type == 'Layout Row') </div> @endif
            </div>
            @if (!$isPrint)
                <div id="addChild{{ $nID }}B" class="disNon 
                    @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} @else basicTier9 @endif ">
                    <a href="/dashboard/surv-{{ $node->nodeRow->node_tree 
                        }}/map/node/-37/?parent={{ $nID }}&end=1"
                        class="btn btn-sm btn-warning opac50 w100 mTn15"
                        ><i class="fa fa-plus-square-o"></i> Add Child Node</a>
                </div>
            @endif
        @elseif (!$isPrint)
            <div class="nodeMover disNon pT5 pB5 
                @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} @else basicTier9 @endif ">
                <a href="javascript:;" class="btn btn-sm btn-warning opac50 w100 mTn10 adminNodeMoveTo" 
                    id="moveTo{{ $nID }}ord0" ><i class="fa fa-bullseye"></i> Move Node Here</a>
            </div>
            <div class="pT5"></div>
        @endif
        
    </div> <!-- end nPrintWrap{{ $nID }} -->
    
    @if ($node->nodeRow->node_parent_id > 0 && !$isPrint) 
        <div id="addSib{{ $nID }}B" class="disNon pT10 pB10 
            @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
            <a href="/dashboard/surv-{{ $node->nodeRow->node_tree }}/map/node/-37/?parent={{ 
                $node->nodeRow->node_parent_id }}&ordAfter={{ $nID }}"
                class="btn btn-sm btn-warning opac50 w100 mTn15"
                ><i class="fa fa-plus-square-o"></i> Add Sibling Node</a>
        </div>
        <div class="nodeMover disNon pT5 pB5 
            @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
            <a id="moveTo{{ $node->nodeRow->node_parent_id }}ord{{ (1+$node->nodeRow->node_parent_order) }}"
                href="javascript:;" class="btn btn-sm btn-warning opac50 w100 mTn10 adminNodeMoveTo"
                ><i class="fa fa-bullseye"></i> Move Node Here</a>
        </div>
    @endif
        
    @if ($node->nodeRow->node_type == 'Layout Column')
        </div>
    @endif

@endif