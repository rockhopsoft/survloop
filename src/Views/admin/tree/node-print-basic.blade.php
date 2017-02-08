<!-- resources/views/vendor/survloop/admin/tree/node-print-basic.blade.php -->

@if (isset($node->nodeRow->NodeType) && (!$REQ->has('opts') || strpos($REQ->opts, 'noData') === false 
    || (!$node->isDataManip() && $node->nodeRow->NodeType != 'Spambot Honey Pot')))

    <div class="relDiv"><a name="n{{ $nID }}" class="absDiv" style="top: -60px;"></a></div>
    
    @if ($canEditTree && $node->nodeRow->NodeParentID > 0 && !$REQ->has('print'))
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
                        <b>{{ $GLOBALS['DB']->coreTbl }}</b>
                    @elseif ($node->isLoopRoot() && isset($GLOBALS['DB']->dataLoops[$node->nodeRow->NodeDataBranch])) 
                        {{ $GLOBALS['DB']->dataLoops[$node->nodeRow->NodeDataBranch]->DataLoopTable }}
                    @else
                        {{ $node->nodeRow->NodeDataBranch }}
                    @endif
                    <i class="fa fa-database fPerc80"></i>
                </div>
            @endif
            @if (trim($node->nodeRow->NodeDataStore) != '' && strpos($node->nodeRow->NodeDataStore, ':') !== false)
                <div class="pull-right taR pT5 slGreenDark opac33 f10 lH10">
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
            <span class="gryA f22 opac50" title="Only visible to robots"><i class="fa fa-bug fa-rotate-90"></i>
        @elseif ($node->isLoopRoot())
            <span class="slBlueDark f30" title="Start of a New Page, Root of a Data Loop"><i class="fa fa-refresh"></i>
        @elseif ($node->isDataManip())
            <span class="slBlueDark f30" title="Data Manipulation"><i class="fa fa-database slGreenDark f22"></i>
        @elseif ($node->isPage()) 
            <span class="slBlueDark f30" title="Start of a New Page"><i class="fa fa-file-text-o"></i>
        @endif
        </span> 
        
        @if ($node->isBranch())
        
            <span class="f26 slBlueDark opac80"><b>{{ $node->nodeRow->NodePromptText }}</b></span>
            
        @elseif ($node->isPage())
        
            <a @if (!$REQ->has('print')) 
                href="{{ $GLOBALS['DB']->treeRow->TreeRootURL }}/u/{{ $node->nodeRow->NodePromptNotes }}" 
                target="_blank" @endif class="f20">
                /{{ $node->nodeRow->NodePromptNotes }}
            </a>
            
        @elseif ($node->isLoopRoot())
        
            <span class="slBlueDark f30">{{ $node->nodeRow->NodeDataBranch }}</span> 
            <a @if (!$REQ->has('print')) 
                href="{{ $GLOBALS['DB']->treeRow->TreeRootURL }}/u/{{ $node->nodeRow->NodePromptNotes }}" 
                target="_blank" @endif class="f20">
                /{{ $node->nodeRow->NodePromptNotes }}</a>
            <div class="f18">{{ $node->nodeRow->NodePromptText }}</div>
            
        @elseif ($node->isDataManip())
        
            <span class="slGreenDark">
            <span class="f18">
                {{ $node->nodeRow->NodeDataBranch }}
                @if ($node->nodeRow->NodeType == 'Data Manip: New') 
                    New Record 
                @elseif ($node->nodeRow->NodeType == 'Data Manip: Update') 
                    Update Record 
                @else 
                    Table Wrap 
                @endif
            </span>
            {{ $node->printManipUpdate() }}
            </span>
            
        @else
        
            <span class="f16 mR10 
            @if ($node->nodeRow->NodeType == 'Spambot Honey Pot') gryA @endif
            @if ($node->isDataManip()) slGreenDark ital @endif
            @if ($node->isLoopRoot()) slBlueDark opac50 @endif
            ">{{ $node->nodeRow->NodePromptText }}</span> 
            @if ($node->isRequired()) <span class="slRedDark" title="required">*</span> @endif
            
            @if ($REQ->has('alt'))
                
                <div class="row">
                    <div class="col-md-6 gry9">
                        @if (trim($node->nodeRow->NodePromptNotes) != '') 
                            <i>{{ strip_tags($node->nodeRow->NodePromptNotes) }}</i><br />
                        @endif
                        <div class=" @if ($node->isLoopRoot()) f18 bld slBlueDark @else f14 @endif ">
                            @if ($node->isLoopRoot())
                                @if (sizeof($GLOBALS['DB']->dataLoops[$node->nodeRow->NodeDataBranch]->conds) > 0) 
                                    {!! view('vendor.survloop.admin.tree.node-list-conditions', [
                                        "node" => $GLOBALS['DB']->dataLoops[$node->nodeRow->NodeDataBranch]
                                    ])->render() !!}
                                @endif
                                <span class="f14 gry6"><i class="fa fa-refresh"></i></span>
                            @endif
                            @if ($node->isRequired()) <span class="slRedDark">*required</span> @endif
                        </div>
                        @if (!$REQ->has('alt'))
                            <span class="f12">
                            @if ($node->isOneLiner()) <span class="gry9">(on one line)</span> @endif
                            @if ($node->isOneLineResponses()) <span class="gry9">(responses on one line)</span> @endif
                            </span>
                        @endif
                        </i>
                        @if (!$REQ->has('alt'))
                            @if (trim($node->nodeRow->NodeInternalNotes) != '') 
                                <div class="gry9 f10 p5 pL20"><i>{{ $node->nodeRow->NodeInternalNotes }}</i></div>
                            @endif
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if (sizeof($node->responses) > 0)
                            <ul>
                            @foreach ($node->responses as $j => $res)
                                @if ($node->indexShowsKid($j))
                                    <li class="mT5 mB5"><b>{{ strip_tags($res->NodeResEng) }} 
                                    <i class="fa fa-code-fork fa-flip-vertical" 
                                        title="Children displayed if selected"></i></b></li>
                                @else
                                    <li class="mT5 mB5">{{ strip_tags($res->NodeResEng) }}</li>
                                @endif
                            @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                
            @endif
        @endif
        
        <div class="fC">
            @if (!$REQ->has('print')) 
                @if (sizeof($tierNode[1]) > 0)
                    <a href="#n{{ $nID }}" id="adminNode{{ $nID }}Expand" class="slBlueLight noUnd"
                        ><i class="fa fa-expand fa-flip-horizontal"></i></a> 
                @endif
                @if ($canEditTree)
                    <a href="javascript:void(0)" id="showAdds{{ $nID }}" 
                        class="adminNodeShowAdds slBlueLight mL5"><i class="fa fa-plus-square-o"></i></a>
                    <a href="javascript:void(0)" id="showMove{{ $nID }}" 
                        class="adminNodeShowMove slBlueLight mL5 f12"><i class="fa fa-arrows-alt"></i></a>
                @endif
            @endif
            @if ($node->hasShowKids && !$REQ->has('alt'))
                <i class="fa fa-code-fork fa-flip-vertical mL5 mT5 f22 blk" 
                    title="Children displayed only with certain responses"></i>
            @endif
            <span class="gry9 mL10">
                @if ($node->isPage() && $node->nodeRow->NodeOpts%29 == 0)
                    <span class="red"><i class="fa fa-sign-out" aria-hidden="true"></i> Exit Page</span>
                @elseif ($node->isBranch() && $nID == $GLOBALS['DB']->treeRow->TreeRoot) Tree's Root Node
                @elseif ($node->isBranch()) Branch
                @elseif (!$node->isDataManip()) {{ $node->nodeRow->NodeType }}
                @endif
            </span>
            {!! $conditionList !!}
        </div>
        @if (!$REQ->has('print'))
            <div id="addChild{{ $nID }}" class="disNon 
                @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} @else basicTier9 @endif "
                ><span class="slBlueDark f22"><i class="fa fa-chevron-right"></i></span> 
                <a href="/dashboard/tree/map/node/-37/?parent={{ $nID }}&start=1"
                    ><i class="fa fa-plus-square-o"></i> Add Child Node</a>
            </div>
        @endif
        @if (sizeof($tierNode[1]) > 0)
            <div id="nodeKids{{ $nID }}" class=" 
                @if (session()->get('adminOverOpts')%2 == 0 || $nID == $GLOBALS['DB']->treeRow->TreeRoot) disBlo 
                @else disNon 
                @endif">
            
                {!! $childrenPrints !!}
                
            </div>
            @if (!$REQ->has('print'))
                <div id="addChild{{ $nID }}B" class="disNon 
                    @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} @else basicTier9 @endif ">
                    <span class="slBlueDark f22"><i class="fa fa-chevron-right"></i></span> 
                    <a href="/dashboard/tree/map/node/-37/?parent={{ $nID }}&end=1"
                        ><i class="fa fa-plus-square-o"></i> Add Child Node</a>
                </div>
            @endif
        @elseif (!$REQ->has('print'))
            <div class="nodeMover disNon pT5 pB5 
                @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} @else basicTier9 @endif ">
                <span class="slBlueDark f14"><i class="fa fa-chevron-right"></i></span> 
                <a href="javascript:void(0)" class="adminNodeMoveTo" id="moveTo{{ $nID }}ord0"
                    ><i class="fa fa-bullseye"></i> Move Node Here</a>
            </div>
            <div class="pT5"></div>
        @endif
        
    </div> <!-- end nPrintWrap{{ $nID }} -->
    
    @if ($node->nodeRow->NodeParentID > 0 && !$REQ->has('print')) 
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

@endif