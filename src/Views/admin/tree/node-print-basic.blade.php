<!-- resources/views/vendor/survloop/admin/tree/node-print-basic.blade.php -->

@if (isset($node->nodeRow->NodeType) && (!$REQ->has('opts') || strpos($REQ->opts, 'noData') === false 
    || (!$node->isDataManip() && $node->nodeRow->NodeType != 'Spambot Honey Pot')))

    @if ($node->nodeRow->NodeType == 'Layout Column')
        <div class="col-md-{{ $node->nodeRow->NodeCharLimit }}">
    @endif

    <div class="relDiv"><a name="n{{ $nID }}" class="absDiv" style="top: -60px;"></a></div>
    
    @if ($canEditTree && $node->nodeRow->NodeParentID > 0 && !$isPrint)
        <div id="addSib{{ $nID }}" class="disNon pT10 pB10 
            @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
            <span class="slBlueDark f22"><i class="fa fa-chevron-right"></i></span> 
            <a href="/dashboard/tree-{{ $node->nodeRow->NodeTree }}/map/node/-37/?parent={{ 
                $node->nodeRow->NodeParentID }}&ordBefore={{ $nID }}"
                class="btn btn-sm btn-warning mTn15"><i class="fa fa-plus-square-o"></i> Add Sibling Node</a>
        </div>
        @if ($node->nodeRow->NodeParentOrder == 0)
            <div class="nodeMover disNon pT5 pB5 
                @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
                <span class="slBlueDark f14"><i class="fa fa-chevron-right"></i></span> 
                <a href="javascript:;" class="btn btn-sm btn-warning mTn10 adminNodeMoveTo" 
                    id="moveTo{{ $node->nodeRow->NodeParentID }}ord{{ $node->nodeRow->NodeParentOrder }}"
                    ><i class="fa fa-bullseye"></i> Move Node Here</a>
            </div>
        @endif
    @endif
    
    <div id="nPrintWrap{{ $nID }}" class=" @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif
        @if ($node->isPage()) basicTierPage 
        @elseif ($node->isBranch()) basicTierBranch 
        @elseif ($node->isLoopRoot()) basicTierLoop 
        @endif
        @if ($node->isLoopRoot() || trim($node->nodeRow->NodeDataBranch) != '' 
            || $node->nodeRow->NodeParentID <= 0) != '') basicTierData @endif
        @if (isset($conditionList) && strpos($conditionList, '#NodeDisabled') !== false) basicTierDisabled @endif
        ">
        
        @if ($node->isDataManip() || $node->isLoopCycle() || $node->isLayout() || $node->isBranch()) 
            <div class="w100 opac50">
        @endif
        <?php /* /dashboard/tree-{{ $node->nodeRow->NodeTree }}/map/node/{{ $nID }} */ ?>
        {!! $nodeBtns !!}
        <table class="w100" cellpadding=0 cellspacing=0 border=0 ><tr><td class="vaT pT5 pR10">
            <a id="showBtns{{ $nID }}" href="javascript:;" class="btn circleBtn 
                @if ($node->isPage() || $node->isLoopRoot()) btn-info circleBtn0 
                    @elseif ($node->isDataManip() || $node->isLoopCycle()) circleBtn3 @else btn-primary 
                    @if ($node->isLayout()) circleBtn2 @else circleBtn1 @endif 
                    @endif " @if ($node->isBranch()) style="margin-top: -5px;" @endif >
                {!! $node->getIcon() !!}
            </a>
        </td><td class="w100 vaT pT5">
        
        @if (!$REQ->has('opts') || strpos($REQ->opts, 'noData') === false)
            @if (trim($node->nodeRow->NodeDataBranch) != '' || $node->nodeRow->NodeParentID <= 0)
                <div class="pull-right taR pT5 pL5 pR10 slGreenDark">
                    @if ($node->nodeRow->NodeParentID <= 0)
                        <b>{{ $GLOBALS['SL']->coreTbl }}</b>
                    @elseif ($node->isLoopRoot() && isset($GLOBALS['SL']->dataLoops[$node->nodeRow->NodeDataBranch])) 
                        {{ $GLOBALS['SL']->dataLoops[$node->nodeRow->NodeDataBranch]->DataLoopTable }}
                    @else
                        {{ $node->nodeRow->NodeDataBranch }}
                    @endif
                    <i class="fa fa-database fPerc80"></i>
                    @if (!$node->isDataManip() && trim($node->nodeRow->NodeDataStore) != '' 
                        && strpos($node->nodeRow->NodeDataStore, ':') !== false)
                        <br /><span class="opac50 f10 lH16"><!--- 
                        {!! str_replace(':', ' --->', $node->nodeRow->NodeDataStore) !!}</span>
                    @endif
                </div>
            @elseif (!$node->isDataManip() && trim($node->nodeRow->NodeDataStore) != '' 
                && strpos($node->nodeRow->NodeDataStore, ':') !== false)
                <div class="pull-right taR pT5 slGreenDark opac50 f10 lH16">
                    <i>{!! str_replace(':', '</i><br />', $node->nodeRow->NodeDataStore) !!}
                </div>
            @endif
        @endif
        
        @if ($node->isLayout())
        
            <span class="slGrey mL10">
                {{ $node->nodeRow->NodeType }}
                @if ($node->nodeRow->NodeType == 'Layout Column')
                    <i>{{ $node->nodeRow->NodeCharLimit }}/12 Wide</i>
                @endif
            </span>
            {!! $conditionList !!}
            
        @else
        
            @if ($node->isInstruct() || $node->isInstructRaw())
                
                <span class="slGrey mL10">
                @if ($node->isInstruct()) Content Chunk, WYSIWYG @else Content Chunk, Hard-Coded @endif
                </span>
                {!! $conditionList !!}
                <div class="nPrompt brdEdash round10 p5 mT5">{!! $instructPrint !!}</div>
            
            @else 
            
                @if ($node->isBranch())
                
                    <h3 class="m0 disIn slBlueDark">{{ $nodePromptText }}</h3>
                    <span class="slGrey mL10">Branch</span>
                    
                @elseif ($node->isPage())
                
                    <div class="disBlo w100">
                        <a target="_blank" class="mT0 infoOn" href="{{ 
                            $GLOBALS['SL']->sysOpts['app-url'] }}{{ $node->extraOpts['page-url'] }}"
                            ><h3 class="m0">@if (isset($node->extraOpts["meta-title"])) {!! $node->extraOpts["meta-title"] 
                            !!} @endif </h3> <i class="fa fa-external-link" aria-hidden="true"></i> {!! 
                            $node->extraOpts["page-url"] !!}</a>
                        @if ($node->nodeRow->NodeOpts%29 == 0)
                            <span class="red mL10"><i class="fa fa-sign-out" aria-hidden="true"></i> 
                                Exit Page #{{ $pageCnt }}</span>
                        @else 
                            <span class="infoOn mL10">Page #{{ $pageCnt }}</span>
                        @endif
                    
                @elseif ($node->isLoopRoot())
                
                    <div class="pull-left"><a target="_blank" class="disBlo mTn5 infoOn" href="{{ 
                        $GLOBALS['SL']->sysOpts['app-url'] }}{{ $node->extraOpts['page-url'] }}"
                        ><h3 class="m0">@if (isset($node->extraOpts["meta-title"])) {!! $node->extraOpts["meta-title"] 
                        !!} @endif </h3> <i class="fa fa-external-link" aria-hidden="true"></i> {!! 
                        $node->extraOpts["page-url"] !!}</a>
                    </div>
                    <div class="pull-left">
                        <span class="slGreenDark mL10"><i class="fa fa-refresh" 
                            title="Start of a New Page, Root of a Data Loop"></i> 
                            Repeat For Each In {{ $node->nodeRow->NodeDataBranch }}</span> 
                        <span class="slGrey mL10">Loop Multiple Pages</span>
                        {!! $conditionList !!}
                    </div>
                    @if (isset($nodePromptText) && trim($nodePromptText) != '')
                        <div class="fC f16">{{ $nodePromptText }}</div>
                    @endif
                    
                @elseif ($node->isLoopCycle())
                
                    <span class="slGreenDark f16">Repeat For Each 
                        {{ $GLOBALS['SL']->getLoopSingular(str_replace('LoopItems::', '', 
                            $node->nodeRow->NodeResponseSet)) }}
                    </span>
                    <span class="slGrey mL10">Loop Within One Page</span>
                    <div class="f18 mL10">{{ $nodePromptText }}</div>
                    
                @elseif ($node->isLoopSort())
                    
                    <span class="slBlueDark f20">Sort Loop Items:  
                        {{ str_replace('LoopItems::', '', $node->nodeRow->NodeResponseSet) }}
                    </span>
                    <span class="slGrey mL10">Sort Tool</span>
                    
                @elseif ($node->isDataManip())
                
                    <span class="slGreenDark @if ($node->parentID > 0) f16 @else f26 @endif ">
                        {{ $node->nodeRow->NodeDataBranch }}
                        @if ($node->nodeRow->NodeType == 'Data Manip: New') 
                            New Database Record 
                        @elseif ($node->nodeRow->NodeType == 'Data Manip: Update') 
                            Update Database Record 
                        @elseif ($node->nodeRow->NodeType == 'Data Manip: Wrap') 
                            Database Table Wrap 
                        @else 
                            Close User Session
                        @endif
                    </span>
                    <span class="slGreenDark mL10">{!! substr($node->printManipUpdate(), 2) !!}</span>
                    
                @elseif ($node->nodeRow->NodeType == 'Spambot Honey Pot')
                
                    <span class="f16 gryA">{{ $nodePromptText }}</span>
                    <span class="gryA mL10">Spambot Honey Pot (Only Visible to Robots)</span>
                
                @elseif ($node->nodeType == 'Send Email')
                    
                    <span class="f16">{{ $GLOBALS["SL"]->getEmailSubj($node->nodeRow->NodeDefault) }}</span>
                    <span class="slGrey mL10">{{ $node->nodeRow->NodeType }}</span>
                
                @else
                
                    @if (trim($nodePromptText) != '')
                        <span class="f16 @if ($node->isDataManip()) slGreenDark ital @endif
                            @if ($node->isLoopRoot()) slBlueDark @endif
                            ">{{ $nodePromptText }}</span> 
                    @elseif (in_array($node->nodeRow->NodeType, ['Checkbox', 'Radio']) && !$isAlt)
                        {!! view('vendor.survloop.admin.tree.node-print-basic-responses', [
                            "node" => $node ])->render() !!}
                    @endif
                    <span class="slGrey mL10">{{ $node->nodeRow->NodeType }}</span>
                    @if ($node->nodeRow->NodeType == 'Big Button') 
                        @if ($node->nodeRow->NodeResponseSet == 'HTML')
                            {!! $node->nodeRow->NodeDefault !!}
                        @else
                           <span class="f16 slBlueDark mL5">{!! $node->nodeRow->NodeDefault !!}</span>
                        @endif
                    @endif
                    
                    @if ($node->isRequired()) <span class="slRedDark" title="required">*</span> @endif
                    
                    @if ($isAlt)
                        
                        @if ($node->isOneLiner()) <span class="slGrey f12 mL10">(Q&A on one line)</span> @endif
                        @if ($node->isOneLineResponses()) 
                            <span class="slGrey f12 mL10">(responses on one line)</span>
                        @endif
                        @if (trim($node->nodeRow->NodePromptNotes) != '')
                            <div class="slGrey f12">{{ strip_tags($node->nodeRow->NodePromptNotes) }}</div>
                        @endif
                        @if (sizeof($node->responses) > 0)
                            {!! $conditionList !!}
                            {!! view('vendor.survloop.admin.tree.node-print-basic-responses', [
                                "node" => $node ])->render() !!}
                        @endif
                        @if (trim($node->nodeRow->NodeInternalNotes) != '')
                            <div class="slGrey f10"><i>{{ $node->nodeRow->NodeInternalNotes }}</i></div>
                        @endif
                        
                    @endif
                @endif
            
                @if (!$node->isLoopRoot())
                    @if (sizeof($node->responses) == 0) {!! $conditionList !!} @endif
                    @if ($node->isPage()) </div> @endif
                @endif
                
            @endif
            
        @endif
        
        </td></tr></table> <!-- end showBtns{{ $nID }} table -->
        {!! $nodeBtnExpand !!}
        @if ($node->isDataManip() || $node->isLoopCycle() || $node->isLayout() || $node->isBranch()) </div> @endif

        @if (!$isPrint)
            <div id="addChild{{ $nID }}" class="disNon 
                @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} @else basicTier9 @endif "
                ><span class="slBlueDark f22"><i class="fa fa-chevron-right"></i></span> 
                <a href="/dashboard/tree-{{ $node->nodeRow->NodeTree }}/map/node/-37/?parent={{ $nID }}&start=1"
                    class="btn btn-sm btn-warning mTn15"><i class="fa fa-plus-square-o"></i> Add Child Node</a>
            </div>
        @endif
        @if (sizeof($tierNode[1]) > 0)
            <div id="nodeKids{{ $nID }}" class=" 
                @if (session()->get('adminOverOpts')%2 == 0 || $nID == $rootID 
                    || $isPrint) disBlo @else disNon @endif">
            
                @if ($node->nodeRow->NodeType == 'Layout Row') <div class="row"> @endif
                {!! $childrenPrints !!}
                @if ($node->nodeRow->NodeType == 'Layout Row') </div> @endif
                
            </div>
            @if (!$isPrint)
                <div id="addChild{{ $nID }}B" class="disNon 
                    @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} @else basicTier9 @endif ">
                    <span class="slBlueDark f22"><i class="fa fa-chevron-right"></i></span> 
                    <a href="/dashboard/tree-{{ $node->nodeRow->NodeTree }}/map/node/-37/?parent={{ $nID }}&end=1"
                        class="btn btn-sm btn-warning mTn15"><i class="fa fa-plus-square-o"></i> Add Child Node</a>
                </div>
            @endif
        @elseif (!$isPrint)
            <div class="nodeMover disNon pT5 pB5 
                @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} @else basicTier9 @endif ">
                <span class="slBlueDark f14"><i class="fa fa-chevron-right"></i></span> 
                <a href="javascript:;" class="btn btn-sm btn-warning mTn10 adminNodeMoveTo" id="moveTo{{ $nID }}ord0"
                    ><i class="fa fa-bullseye"></i> Move Node Here</a>
            </div>
            <div class="pT5"></div>
        @endif
        
    </div> <!-- end nPrintWrap{{ $nID }} -->
    
    @if ($node->nodeRow->NodeParentID > 0 && !$isPrint) 
        <div id="addSib{{ $nID }}B" class="disNon pT10 pB10 
            @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
            <span class="slBlueDark f22"><i class="fa fa-chevron-right"></i></span> 
            <a href="/dashboard/tree-{{ $node->nodeRow->NodeTree }}/map/node/-37/?parent={{ 
                $node->nodeRow->NodeParentID }}&ordAfter={{ $nID }}"
                class="btn btn-sm btn-warning mTn15"><i class="fa fa-plus-square-o"></i> Add Sibling Node</a>
        </div>
        <div class="nodeMover disNon pT5 pB5 @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
            <span class="slBlueDark f14"><i class="fa fa-chevron-right"></i></span> 
            <a id="moveTo{{ $node->nodeRow->NodeParentID }}ord{{ (1+$node->nodeRow->NodeParentOrder) }}"
                href="javascript:;" class="btn btn-sm btn-warning mTn10 adminNodeMoveTo"
                ><i class="fa fa-bullseye"></i> Move Node Here</a>
        </div>
    @endif
        
    @if ($node->nodeRow->NodeType == 'Layout Column')
        </div>
    @endif

@endif