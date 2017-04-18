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
            <a href="/dashboard/tree/map/node/-37/?parent={{ $node->nodeRow->NodeParentID }}&ordBefore={{ $nID }}"
                ><i class="fa fa-plus-square-o"></i> Add Sibling Node</a>
        </div>
        @if ($node->nodeRow->NodeParentOrder == 0)
            <div class="nodeMover disNon pT5 pB5 
                @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
                <span class="slBlueDark f14"><i class="fa fa-chevron-right"></i></span> 
                <a href="javascript:void(0)" class="adminNodeMoveTo" 
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
        @if (trim($node->nodeRow->NodeDataBranch || $node->nodeRow->NodeParentID <= 0) != '') basicTierData @endif
        ">
        @if (!$REQ->has('opts') || strpos($REQ->opts, 'noData') === false)
            @if (trim($node->nodeRow->NodeDataBranch) != '' || $node->nodeRow->NodeParentID <= 0)
                <div class="pull-right pT5 pL5 pR10 slGreenDark">
                    @if ($node->nodeRow->NodeParentID <= 0)
                        <b>{{ $GLOBALS['SL']->coreTbl }}</b>
                    @elseif ($node->isLoopRoot() && isset($GLOBALS['SL']->dataLoops[$node->nodeRow->NodeDataBranch])) 
                        {{ $GLOBALS['SL']->dataLoops[$node->nodeRow->NodeDataBranch]->DataLoopTable }}
                    @else
                        {{ $node->nodeRow->NodeDataBranch }}
                    @endif
                    <i class="fa fa-database fPerc80"></i>
                </div>
            @endif
            @if (trim($node->nodeRow->NodeDataStore) != '' && strpos($node->nodeRow->NodeDataStore, ':') !== false)
                <div class="pull-right taR pT5 slGreenDark opac50 f10 lH16">
                    <i>{!! str_replace(':', '</i><br />', $node->nodeRow->NodeDataStore) !!}
                </div>
            @endif
        @endif
        
        @if (!$REQ->has('opts') || strpos($REQ->opts, 'noNodeID') === false)
            <a href="/dashboard/tree/map/node/{{ $nID }}" class="btn btn-xs btn-default circleBtn1">#{{ $nID }}</a> 
        @endif
        
        @if ($node->isBranch())
            <span class="slBlueDark f26 opac80" title="Branch Title"><i class="fa fa-share-alt"></i>
        @elseif ($node->nodeRow->NodeType == 'Spambot Honey Pot')
            <span class="gryA f22 opac50" title="Only Visible to Robots"><i class="fa fa-bug fa-rotate-90"></i>
        @elseif ($node->isLoopRoot())
            <span class="slBlueDark f30" title="Start of a New Page, Root of a Data Loop"><i class="fa fa-refresh"></i>
        @elseif ($node->isLoopCycle())
            <span class="slBlueDark f20" title="Data Loop within a Page"><i class="fa fa-refresh"></i>
        @elseif ($node->isLoopSort())
            <span class="slBlueDark f20" title="Sort Data Loop Items"><i class="fa fa-sort"></i>
        @elseif ($node->isDataManip())
            <span class="slGreenDark @if ($node->parentID > 0) f16 @else f26 @endif " 
                title="Data Manipulation"><i class="fa fa-database slGreenDark"></i>
        @elseif ($node->isPage())
            <span class="slBlueDark f30" title="Start of a New Page"><i class="fa fa-file-text-o"></i>
        @endif
        </span> 
        
        @if ($node->isLayout())
        
            <span class="slGrey f20" title="Start of a Layout Row of Columns"><i class="fa fa-columns"></i></span>
            <span class="slGrey mL10">
                {{ $node->nodeRow->NodeType }}
                @if ($node->nodeRow->NodeType == 'Layout Column')
                    <i>{{ $node->nodeRow->NodeCharLimit }}/12 Wide</i>
                @endif
            </span>
            {!! $conditionList !!}
            {!! $nodeBtns !!}
            
        @else
        
            @if ($GLOBALS['SL']->treeRow->TreeType == 'Page' && ($node->isInstruct() || $node->isInstructRaw()))
                
                <span class="slGrey mL10">
                @if ($node->isInstruct()) Content Chunk, WYSIWYG @else Content Chunk, Hard-Coded @endif
                </span>
                {!! $conditionList !!}
                {!! $nodeBtns !!}
                <div class="nPrompt brdEdash round10 p5 mT5">{!! $node->nodeRow->NodePromptText !!}</div>
                {!! $node->nodeRow->NodePromptAfter !!}
            
            @else 
            
                @if ($node->isBranch())
                
                    <span class="slBlueDark mL5 opac80 f26"><b>{{ $nodePromptText }}</b></span>
                    <span class="slGrey mL10">Branch</span>
                    
                @elseif ($node->isPage())
                
                    <a target="_blank" class="f20 mL5"
                        @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
                            @if ($GLOBALS['SL']->treeIsAdmin)
                                @if ($GLOBALS['SL']->treeRow->TreeOpts%7 == 0)
                                    href="{{ $GLOBALS['SL']->sysOpts["app-url"] }}/dashboard">/dashboard</a>
                                @else
                                    href="{{ $GLOBALS['SL']->sysOpts["app-url"] }}/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}" 
                                    >/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}</a>
                                @endif
                            @else
                                href="{{ $GLOBALS['SL']->sysOpts["app-url"] }}/{{ $GLOBALS['SL']->treeRow->TreeSlug }}"
                                >/{{ $GLOBALS['SL']->treeRow->TreeSlug }}
                            @endif
                        @else
                            @if ($GLOBALS['SL']->treeIsAdmin)
                                href="/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $node->nodeRow->NodePromptNotes }}" 
                                >/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $node->nodeRow->NodePromptNotes }}</a>
                            @else
                                href="/u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $node->nodeRow->NodePromptNotes }}" 
                                >/u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $node->nodeRow->NodePromptNotes }}
                            @endif
                        @endif
                    </a>
                    @if ($node->nodeRow->NodeOpts%29 == 0)
                        <span class="red mL10"><i class="fa fa-sign-out" aria-hidden="true"></i> Exit Page</span>
                    @else 
                        <span class="slGrey mL10">Page</span>
                    @endif
                    
                @elseif ($node->isLoopRoot())
                
                    <span class="slBlueDark mL5 f30">{{ $node->nodeRow->NodeDataBranch }}</span> 
                    <a target="_blank" class="f20 mL10"
                        @if ($GLOBALS['SL']->treeIsAdmin)
                            href="/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $node->nodeRow->NodePromptNotes }}" 
                            >/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $node->nodeRow->NodePromptNotes }}</a>
                        @else
                            href="/u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $node->nodeRow->NodePromptNotes }}" 
                            >/u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $node->nodeRow->NodePromptNotes }}</a>
                        @endif
                    <span class="f18 mL10">{{ $nodePromptText }}</span>
                    <span class="slGrey mL10">SurvLoop Pages</span>
                    
                @elseif ($node->isLoopCycle())
                
                    <span class="slBlueDark mL5 f20">Repeat Child Nodes For Each 
                        {{ $GLOBALS['SL']->getLoopSingular(str_replace('LoopItems::', '', $node->nodeRow->NodeResponseSet)) }}
                    </span>
                    <div class="f18 mL10">{{ $nodePromptText }}</div>
                    <span class="slGrey mL10">SurvLoop Questions</span>
                    
                @elseif ($node->isLoopSort())
                    
                    <span class="slBlueDark mL5 f20">Sort Loop Items:  
                        {{ str_replace('LoopItems::', '', $node->nodeRow->NodeResponseSet) }}
                    </span>
                    <span class="slGrey mL10">Sort Tool</span>
                    
                @elseif ($node->isDataManip())
                
                    <span class="slGreenDark mL5 @if ($node->parentID > 0) f16 @else f26 @endif ">
                        {{ $node->nodeRow->NodeDataBranch }}
                        @if ($node->nodeRow->NodeType == 'Data Manip: New') 
                            New Record 
                        @elseif ($node->nodeRow->NodeType == 'Data Manip: Update') 
                            Update Record 
                        @else 
                            Table Wrap 
                        @endif
                    </span>
                    
                @elseif ($node->nodeRow->NodeType == 'Spambot Honey Pot')
                
                    <span class="f16 mL5 gryA">{{ $nodePromptText }}</span>
                    <span class="gryA mL10">Spambot Honey Pot (Only Visible to Robots)</span>
                
                @else
                
                    @if (trim($nodePromptText) != '')
                        <span class="f16 mL5 
                            @if ($node->isDataManip()) slGreenDark ital @endif
                            @if ($node->isLoopRoot()) slBlueDark @endif
                            ">{{ $nodePromptText }}</span> 
                    @endif
                    @if ($node->nodeRow->NodeType == 'Big Button') 
                        <span class="f16 slBlueDark mL5">[ {{ $node->nodeRow->NodeDefault }} ]</span>
                    @endif
                    <span class="slGrey mL10">{{ $node->nodeRow->NodeType }}</span>
                    
                    @if ($node->isRequired()) <span class="slRedDark" title="required">*</span> @endif
                    
                    @if ($isAlt)
                        
                        @if (sizeof($node->responses) > 0)
                            <div class="f16 gry6">
                            @foreach ($node->responses as $j => $res)
                                <nobr><i class="fa fa-circle-o gryA mL20" style="font-size: 10pt;" aria-hidden="true"></i>
                                @if ($node->indexShowsKid($j))
                                    <i class="fa fa-code-fork fa-flip-vertical" 
                                        title="Children displayed if selected"></i> 
                                @endif
                                {{ strip_tags($res->NodeResEng) }}</nobr>
                            @endforeach
                            </div>
                        @endif
                        <div class="slGrey f12">
                            @if ($node->isOneLiner()) <span class="mL10">(on one line)</span> @endif
                            @if ($node->isOneLineResponses()) <span class="mL10">(responses on one line)</span> @endif
                            @if (trim($node->nodeRow->NodePromptNotes) != '') 
                                <div>{{ strip_tags($node->nodeRow->NodePromptNotes) }}</div>
                            @endif
                            @if (trim($node->nodeRow->NodeInternalNotes) != '') 
                                <div><i>{{ $node->nodeRow->NodeInternalNotes }}</i></div>
                            @endif
                        </div>
                        
                    @endif
                @endif
            
                @if ($node->isDataManip())
                    <span class="slGreenDark mL10">{!! substr($node->printManipUpdate(), 2) !!}</span>
                @endif
                {!! $conditionList !!}
                {!! $nodeBtns !!}
            
            @endif
            
        @endif
        
        @if (!$isPrint)
            <div id="addChild{{ $nID }}" class="disNon 
                @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} @else basicTier9 @endif "
                ><span class="slBlueDark f22"><i class="fa fa-chevron-right"></i></span> 
                <a href="/dashboard/tree/map/node/-37/?parent={{ $nID }}&start=1"
                    ><i class="fa fa-plus-square-o"></i> Add Child Node</a>
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
                    <a href="/dashboard/tree/map/node/-37/?parent={{ $nID }}&end=1"
                        ><i class="fa fa-plus-square-o"></i> Add Child Node</a>
                </div>
            @endif
        @elseif (!$isPrint)
            <div class="nodeMover disNon pT5 pB5 
                @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} @else basicTier9 @endif ">
                <span class="slBlueDark f14"><i class="fa fa-chevron-right"></i></span> 
                <a href="javascript:void(0)" class="adminNodeMoveTo" id="moveTo{{ $nID }}ord0"
                    ><i class="fa fa-bullseye"></i> Move Node Here</a>
            </div>
            <div class="pT5"></div>
        @endif
        
    </div> <!-- end nPrintWrap{{ $nID }} -->
    
    @if ($node->nodeRow->NodeParentID > 0 && !$isPrint) 
        <div id="addSib{{ $nID }}B" class="disNon pT10 pB10 
            @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
            <span class="slBlueDark f22"><i class="fa fa-chevron-right"></i></span> 
            <a href="/dashboard/tree/map/node/-37/?parent={{ $node->nodeRow->NodeParentID }}&ordAfter={{ $nID }}"
                ><i class="fa fa-plus-square-o"></i> Add Sibling Node</a>
        </div>
        <div class="nodeMover disNon pT5 pB5 @if ($tierDepth < 10) basicTier{{ $tierDepth }} @else basicTier9 @endif ">
            <span class="slBlueDark f14"><i class="fa fa-chevron-right"></i></span> 
            <a id="moveTo{{ $node->nodeRow->NodeParentID }}ord{{ (1+$node->nodeRow->NodeParentOrder) }}"
                href="javascript:void(0)" class="adminNodeMoveTo"><i class="fa fa-bullseye"></i> Move Node Here</a>
        </div>
    @endif
        
    @if ($node->nodeRow->NodeType == 'Layout Column')
        </div>
    @endif

@endif