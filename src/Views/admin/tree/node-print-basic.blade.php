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
            @if (trim($node->nodeRow->NodeDataStore) != '' && strpos($node->nodeRow->NodeDataStore, ':') !== false
                && !$node->isDataManip())
                <div class="pull-right taR pT5 slGreenDark opac50 f10 lH16">
                    <i>{!! str_replace(':', '</i><br />', $node->nodeRow->NodeDataStore) !!}
                </div>
            @endif
        @endif
        
        <?php /* /dashboard/tree-{{ $node->nodeRow->NodeTree }}/map/node/{{ $nID }} */ ?>
        <a id="showBtns{{ $nID }}" href="javascript:;" class="btn btn-xs btn-primary circleBtn 
            @if ($node->isPage() || $node->isLoopRoot()) circleBtn0 @elseif ($node->isLayout()) circleBtn2
            @else circleBtn1 @endif " >
        @if ($node->isBranch())
            <i class="fa fa-share-alt" title="Branch Title"></i>
        @elseif ($node->nodeRow->NodeType == 'Spambot Honey Pot')
            <i class="fa fa-bug fa-rotate-90" title="Only visible to robots"></i>
        @elseif ($node->isLoopRoot())
            <i class="fa fa-refresh" title="Start of a New Page, Root of a Data Loop"></i>
        @elseif ($node->isLoopCycle())
            <i class="fa fa-refresh" title="Data Loop within a Page"></i>
        @elseif ($node->isLoopSort())
            <i class="fa fa-sort" title="Sort Data Loop Items"></i>
        @elseif ($node->isDataManip())
            <i class="fa fa-database" title="Data Manipulation"></i>
        @elseif ($node->isPage())
            <i class="fa fa-file-text-o" title="Start of a New Page"></i>
        @elseif ($node->isBigButt() || $node->nodeType == 'Back Next Buttons')
            <i class="fa fa-hand-pointer-o fa-rotate-90" aria-hidden="true"></i>
        @elseif ($node->nodeType == 'Send Email')
            <i class="fa fa-envelope-o" aria-hidden="true" title="Send an Email"></i></span>
        @elseif ($node->nodeType == 'Checkbox')
            <i class="fa fa-check-square-o" aria-hidden="true"></i>
        @elseif ($node->nodeType == 'Radio')
            <i class="fa fa-dot-circle-o" aria-hidden="true"></i>
        @elseif (in_array($node->nodeType, ['Email', 'Gender', 'Gender Not Sure', 'Long Text', 'Text', 'Text:Number']))
            <i class="fa fa-i-cursor" aria-hidden="true"></i>
        @elseif (in_array($node->nodeType, ['U.S. States', 'Dropdown', 'Date', 'Feet Inches']))
            <i class="fa fa-caret-square-o-down" aria-hidden="true"></i>
        @elseif ($node->nodeType == 'Instructions')
            <i class="fa fa-info-circle" aria-hidden="true"></i>
        @elseif ($node->nodeType == 'Instructions Raw')
            <i class="fa fa-code" aria-hidden="true"></i>
        @elseif ($node->nodeType == 'Hidden Field')
            <i class="fa fa-eye-slash opac50" aria-hidden="true"></i>
        @elseif ($node->nodeType == 'Date Picker')
            <i class="fa fa-calendar" aria-hidden="true"></i>
        @elseif (in_array($node->nodeType, ['Time', 'Date Time']))
            <i class="fa fa-clock-o" aria-hidden="true"></i>
        @elseif ($node->nodeType == 'User Sign Up')
            <i class="fa fa-user-plus" aria-hidden="true"></i>
        @elseif ($node->nodeType == 'Uploads')
            <i class="fa fa-cloud-upload" aria-hidden="true"></i>
        @elseif ($node->isLayout())
            <i class="fa fa-columns"></i>
        @elseif ($node->isWidget())
            @if ($node->nodeType == 'Incomplete Sess Check')
                <i class="fa fa-user-o" aria-hidden="true"></i>
            @elseif ($node->nodeType == 'Member Profile')
                <i class="fa fa-user-circle-o" aria-hidden="true"></i>
            @elseif (in_array($node->nodeType, ['Search', 'Search Results', 'Search Featured']))
                <i class="fa fa-search" aria-hidden="true"></i>
            @else
                <i class="fa fa-magic" aria-hidden="true"></i>
            @endif
        @else <?php /* if ($node->nodeType == 'Other/Custom') */ ?>
            <i class="fa fa-hand-spock-o" aria-hidden="true"></i>
        @endif
        </a>
        
        @if ($node->isLayout())
        
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
                <div class="nPrompt brdEdash round10 p5 mT5">{!! $instructPrint !!}</div>
            
            @else 
            
                @if ($node->isBranch())
                
                    <span class="slBlueDark mL5 f26"><b>{{ $nodePromptText }}</b></span>
                    <span class="slGrey mL10">Branch</span>
                    
                @elseif ($node->isPage())
                
                    <a target="_blank" class="f20 mL5"
                        @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
                            @if ($GLOBALS['SL']->treeIsAdmin)
                                @if ($GLOBALS['SL']->treeRow->TreeOpts%7 == 0)
                                    href="{{ $GLOBALS['SL']->sysOpts["app-url"] }}/dashboard">/dashboard</a>
                                @else
                                    href="{{ $GLOBALS['SL']->sysOpts["app-url"] 
                                        }}/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}?preview=1" 
                                            >/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}</a>
                                @endif
                            @else
                                href="{{ $GLOBALS['SL']->sysOpts["app-url"] }}/{{ 
                                    $GLOBALS['SL']->treeRow->TreeSlug }}?preview=1" >/{{ 
                                    $GLOBALS['SL']->treeRow->TreeSlug }}
                            @endif
                        @else
                            @if ($GLOBALS['SL']->treeIsAdmin)
                                href="/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $node->nodeRow->NodePromptNotes 
                                    }}?preview=1" >/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug 
                                        }}/{{ $node->nodeRow->NodePromptNotes }}</a>
                            @else
                                href="/u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/{{ $node->nodeRow->NodePromptNotes 
                                    }}?preview=1" >/u/{{ $GLOBALS['SL']->treeRow->TreeSlug 
                                        }}/{{ $node->nodeRow->NodePromptNotes }}
                            @endif
                        @endif
                    </a>
                    @if ($node->nodeRow->NodeOpts%29 == 0)
                        <span class="red mL10"><i class="fa fa-sign-out" aria-hidden="true"></i> Exit Page</span>
                    @else 
                        <span class="slGrey mL10">Page</span>
                    @endif
                    @if (trim($node->nodeRow->NodeTextSuggest) != '')
                        <span class="slGrey mL10">(Has Hero Image)</span>
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
                        {{ $GLOBALS['SL']->getLoopSingular(str_replace('LoopItems::', '', 
                            $node->nodeRow->NodeResponseSet)) }}
                    </span>
                    <div class="f18 mL10">{{ $nodePromptText }}</div>
                    <span class="slGrey mL10">SurvLoop Questions</span>
                    
                @elseif ($node->isLoopSort())
                    
                    <span class="slBlueDark mL5 f20">Sort Loop Items:  
                        {{ str_replace('LoopItems::', '', $node->nodeRow->NodeResponseSet) }}
                    </span>
                    <span class="slGrey mL10">Sort Tool</span>
                    
                @elseif ($node->isDataManip())
                
                    <span class="slGreenDark mL10 @if ($node->parentID > 0) f16 @else f26 @endif ">
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
                    
                @elseif ($node->nodeRow->NodeType == 'Spambot Honey Pot')
                
                    <span class="f16 mL5 gryA">{{ $nodePromptText }}</span>
                    <span class="gryA mL10">Spambot Honey Pot (Only Visible to Robots)</span>
                
                @elseif ($node->nodeType == 'Send Email')
                    
                    <span class="f16 mL5">{{ $GLOBALS["SL"]->getEmailSubj($node->nodeRow->NodeDefault) }}</span>
                    <span class="slGrey mL10">{{ $node->nodeRow->NodeType }}</span>
                
                @else
                
                    @if (trim($nodePromptText) != '')
                        <span class="f16 mL5 
                            @if ($node->isDataManip()) slGreenDark ital @endif
                            @if ($node->isLoopRoot()) slBlueDark @endif
                            ">{{ $nodePromptText }}</span> 
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
                        
                        @if (sizeof($node->responses) > 0)
                            <div class="f16 gry6">
                            @foreach ($node->responses as $j => $res)
                                <nobr><i class="fa fa-circle-o gryA mL20" style="font-size: 10pt;" 
                                    aria-hidden="true"></i>
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
                
                @if ($node->isPage() && trim($node->nodeRow->NodeTextSuggest) != '')
                    <div class="brdEdash round10 p5 mT5">
                    <img class="heroImg" src="{{ $node->nodeRow->NodeTextSuggest }}" border=0 ></div>
                @endif
            
            @endif
            
        @endif
        
        @if (!$isPrint)
            <div id="addChild{{ $nID }}" class="disNon 
                @if ((1+$tierDepth) < 10) basicTier{{ (1+$tierDepth) }} @else basicTier9 @endif "
                ><span class="slBlueDark f22"><i class="fa fa-chevron-right"></i></span> 
                <a href="/dashboard/tree-{{ $node->nodeRow->NodeTree }}/map/node/-37/?parent={{ $nID }}&start=1"
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
                    <a href="/dashboard/tree-{{ $node->nodeRow->NodeTree }}/map/node/-37/?parent={{ $nID }}&end=1"
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
            <a href="/dashboard/tree-{{ $node->nodeRow->NodeTree }}/map/node/-37/?parent={{ 
                $node->nodeRow->NodeParentID }}&ordAfter={{ $nID }}"
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