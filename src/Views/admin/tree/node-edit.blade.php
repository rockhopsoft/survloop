<!-- resources/views/vendor/survloop/admin/tree/node-edit.blade.php -->

<div class="p10 fC"></div>

@if ($canEditTree)
    <form name="nodeEditor" method="post" 
        @if (isset($node->nodeRow) && isset($node->nodeRow->NodeID))
            action="/dashboard/tree-{{ $treeID }}/map/node/{{ $node->nodeRow->NodeID }}"
        @else
            action="/dashboard/tree-{{ $treeID }}/map/node/-3"
        @endif
        >
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="sub" value="1">
    <input type="hidden" name="treeID" value="{{ $treeID }}">
    <input type="hidden" name="nodeParentID" 
        @if ($GLOBALS['SL']->REQ->has('parent') && intVal($GLOBALS['SL']->REQ->input('parent')) > 0) 
            value="{{ $GLOBALS['SL']->REQ->input('parent') }}"
        @else 
            value="{{ $node->parentID }}"
        @endif
        >
    <input type="hidden" name="childPlace" 
        @if ($GLOBALS['SL']->REQ->has('start') && intVal($GLOBALS['SL']->REQ->input('start')) > 0) 
            value="start"
        @else 
            @if ($GLOBALS['SL']->REQ->has('end') && intVal($GLOBALS['SL']->REQ->input('end')) > 0) value="end" @else value="" @endif
        @endif
        >
    <input type="hidden" name="orderBefore" 
        @if ($GLOBALS['SL']->REQ->has('ordBefore') && intVal($GLOBALS['SL']->REQ->ordBefore) > 0) 
            value="{{ $GLOBALS['SL']->REQ->ordBefore }}"
        @else 
            value="-3"
        @endif
        >
    <input type="hidden" name="orderAfter" 
        @if ($GLOBALS['SL']->REQ->has('ordAfter') && intVal($GLOBALS['SL']->REQ->ordAfter) > 0) 
            value="{{ $GLOBALS['SL']->REQ->ordAfter }}"
        @else 
            value="-3"
        @endif
        >
@endif

<a class="pull-right"
    @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
        href="/dashboard/page/{{ $treeID }}?all=1&refresh=1#n{{ $node->nodeRow->NodeID }}" 
    @elseif (isset($node->nodeRow) && isset($node->nodeRow->NodeID))
        href="/dashboard/tree-{{ $treeID }}/map?all=1&refresh=1#n{{ $node->nodeRow->NodeID }}" 
    @else
        href="/dashboard/tree-{{ $treeID }}/map?all=1&refresh=1" 
    @endif >Back to Form-Tree Map</a>
@if (isset($node->nodeRow->NodeID) && $node->nodeRow->NodeID > 0) 
    <h1 class="disIn slBlueDark"><i class="fa fa-cube mR5" aria-hidden="true"></i> 
        Editing Node #{{ $node->nodeRow->NodeID }}</h1>
@else 
    <h1 class="disIn slBlueDark"><i class="fa fa-cube mR5" aria-hidden="true"></i> Adding Node</h1>
@endif

<div class="row">
    <div class="col-md-8">
    
        <div id="hasInstruct" class="mTn20 
            @if ($node->isInstruct() || $node->isInstructRaw()) disBlo @else disNon @endif ">
            <div class="nFld w100">
                @if ($node->isInstruct()) 
                    <textarea name="nodeInstruct" id="nodeInstructID" class="form-control w100" autocomplete="off"
                        style="height: 450px; font-family: Courier New;">@if (isset($node->nodeRow->NodePromptText)){!! 
                            $node->nodeRow->NodePromptText !!}@endif</textarea>
                    <script>tinymce.init({ selector:'#nodeInstructID' });</script>
                    <?php /* <style> #tinymce {
                        @if (isset($node->colors['blockAlign']) && $node->colors['blockAlign'] == 'center') text-align: center; 
                        @elseif (isset($node->colors['blockAlign']) && $node->colors['blockAlign'] == 'right') text-align: right; 
                        @endif
                    } </style> */ ?>
                    <?php /* <div name="nodeInstruct" id="nodeInstructID" class="brdDashGrey nPrompt 
                        @if (isset($node->colors['blockAlign']) && $node->colors['blockAlign'] == 'center') taC 
                        @elseif (isset($node->colors['blockAlign']) && $node->colors['blockAlign'] == 'right') taR 
                        @endif " autocomplete="off"
                        style="height: 450px; width: 100%; overflow: auto;" 
                        >@if (isset($node->nodeRow->NodePromptText)){!! 
                            $node->nodeRow->NodePromptText !!}@endif</div>
                    <style> .ql-editor, #nodeInstructID .ql-editor {
                        @if (isset($node->colors['blockAlign']) && $node->colors['blockAlign'] == 'center') text-align: center; 
                        @elseif (isset($node->colors['blockAlign']) && $node->colors['blockAlign'] == 'right') text-align: right; 
                        @endif
                    } </style> */ ?>
                            
                    <?php /* <trix-editor input="nodeInstructID" class="nPrompt 
                        @if (isset($node->colors['blockAlign']) && $node->colors['blockAlign'] == 'center') taC 
                        @elseif (isset($node->colors['blockAlign']) && $node->colors['blockAlign'] == 'right') taR 
                        @endif " ></trix-editor> */ ?>
                @else
                    <textarea name="nodeInstruct" id="nodeInstructID" class="form-control w100" autocomplete="off"
                        style="height: 250px; font-family: Courier New;">@if (isset($node->nodeRow->NodePromptText)){!! 
                            $node->nodeRow->NodePromptText !!}@endif</textarea>
                @endif
            </div>
            <label class="w100 pT10 pB10">
                <a id="extraHTMLbtn2" href="javascript:void(0)" class="f12 fL">+ HTML/JS/CSS Extras</a> 
                <div id="extraHTML2" class="w100 fC @if (isset($node->nodeRow->NodePromptAfter) 
                    && trim($node->nodeRow->NodePromptAfter) != '') disBlo @else disNon @endif ">
                    <div class="nFld mT0"><textarea name="instrPromptAfter" class="form-control" 
                        style="width: 100%; height: 100px;" autocomplete="off"
                        >@if (isset($node->nodeRow->NodePromptAfter)
                            ){!! $node->nodeRow->NodePromptAfter !!}@endif</textarea></div>
                    <span class="slGrey f12">"[[nID]]" will be replaced with node ID</span>
                </div>
            </label>
        </div>
        
        @if ($node->isPageBlock() || ($GLOBALS['SL']->treeRow->TreeType == 'Page' && $GLOBALS['SL']->REQ->has('parent') 
            && $GLOBALS['SL']->REQ->get('parent') == $GLOBALS['SL']->treeRow->TreeRoot))
            <div id="isPageBlock" class=" @if ($node->canBePageBlock()) disBlo @else disNon @endif ">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h4 class="m0">Page Block Options</h4>
                        </div>
                    </div>
                    <div class="panel-body" style="padding: 0px;">
                        <div id="pageBlock" class="slBg slTxt p20">
                            <div class="row">
                                <div class="col-md-4">
                                    <select name="opts67" id="opts67ID" class="form-control input-lg mB20" autocomplete="off">
                                        <option value="1" @if ($node->nodeRow->NodeOpts%67 > 0) SELECTED @endif >Full Content Width</option>
                                        <option value="67" @if ($node->nodeRow->NodeOpts%67 == 0) SELECTED @endif >Skinny Content Width</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select name="blockHeight" id="blockHeightID" class="form-control input-lg mB20" 
                                        autocomplete="off">
                                        <option value="auto" @if (!isset($node->colors["blockHeight"]) 
                                            || $node->colors["blockHeight"] == 'auto') SELECTED @endif 
                                            >Auto Height, Default Padding</option>
                                        <option value="h100" @if (isset($node->colors["blockHeight"]) 
                                            && $node->colors["blockHeight"] == 'h100') SELECTED @endif 
                                            >Full Screen Height</option>
                                        <option value="h75" @if (isset($node->colors["blockHeight"]) 
                                            && $node->colors["blockHeight"] == 'h75') SELECTED @endif 
                                            >75% Screen Height</option>
                                        <option value="h66" @if (isset($node->colors["blockHeight"]) 
                                            && $node->colors["blockHeight"] == 'h66') SELECTED @endif 
                                            >66% Screen Height</option>
                                        <option value="h50" @if (isset($node->colors["blockHeight"]) 
                                            && $node->colors["blockHeight"] == 'h50') SELECTED @endif 
                                            >50% Screen Height</option>
                                        <option value="h33" @if (isset($node->colors["blockHeight"]) 
                                            && $node->colors["blockHeight"] == 'h33') SELECTED @endif 
                                            >33% Screen Height</option>
                                        <option value="h25" @if (isset($node->colors["blockHeight"]) 
                                            && $node->colors["blockHeight"] == 'h25') SELECTED @endif 
                                            >25% Screen Height</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select name="blockAlign" id="blockAlignID" class="form-control input-lg mB20" 
                                        autocomplete="off">
                                        <option value="left" @if (!isset($node->colors["blockAlign"]) 
                                            || $node->colors["blockAlign"] == 'left') SELECTED @endif 
                                            >Align Left</option>
                                        <option value="center" @if (isset($node->colors["blockAlign"]) 
                                            && $node->colors["blockAlign"] == 'center') SELECTED @endif 
                                            >Align Center</option>
                                        <option value="right" @if (isset($node->colors["blockAlign"]) 
                                            && $node->colors["blockAlign"] == 'right') SELECTED @endif 
                                            >Align Right</option>
                                    </select>
                                </div>
                            </div>
                            <label class="mT10 mB10"><input type="checkbox" name="opts71" id="opts71ID" value="71" 
                                autocomplete="off" onClick="return checkPageBlock();" 
                                @if ($node->nodeRow->NodeOpts%71 == 0) CHECKED @endif
                                > <span class="fPerc133">Background</span></label>
                            <div id="pageBlockOpts" class="pT5 
                                @if ($node->nodeRow->NodeOpts%71 == 0) disBlo @else disNon @endif ">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label for="blockBGID"><h4 class="m0">Background Color</h4></label>
                                        {!! view('vendor.survloop.inc-color-picker', [
                                            'fldName' => 'blockBG',
                                            'preSel'  => ((isset($node->colors["blockBG"])) 
                                                ? $node->colors["blockBG"] : '#000')
                                        ])->render() !!}
                                        <label for="blockTextID"><h4 class="m0">Text Color</h4></label>
                                        {!! view('vendor.survloop.inc-color-picker', [
                                            'fldName' => 'blockText',
                                            'preSel'  => ((isset($node->colors["blockText"])) 
                                                ? $node->colors["blockText"] : '#DDD')
                                        ])->render() !!}
                                        <label for="blockLinkID"><h4 id="blockLinkh4" class="m0 slTxt"
                                            >Link Color</h4></label>
                                        {!! view('vendor.survloop.inc-color-picker', [
                                            'fldName' => 'blockLink',
                                            'preSel'  => ((isset($node->colors["blockLink"])) 
                                                ? $node->colors["blockLink"] : '#FFF')
                                        ])->render() !!}
                                    </div>
                                    <div class="col-md-1"></div>
                                    <div class="col-md-6">
                                        <label for="blockImgID"><h4 class="m0">Background Image</h4></label>
                                        <input type="text" class="form-control input-lg w100 mB20 mR20"
                                            id="blockImgID" name="blockImg" value="{{ ((isset($node->colors['blockImg'])) 
                                                ? $node->colors['blockImg'] : '') }}">
                                        <div class="disIn mL10 mR10"><label class="disIn">
                                            <input type="radio" name="blockImgType" id="blockImgTypeA" 
                                                value="w100" class="mR5" autocomplete="off" onClick="return checkPageBlock();"
                                                @if (!isset($node->colors["blockImgType"]) 
                                                    || $node->colors["blockImgType"] == 'w100') CHECKED @endif 
                                                    > Full-Width Image
                                        </label></div>
                                        <div class="disIn mL10 mR10"><label class="disIn">
                                            <input type="radio" name="blockImgType" id="blockImgTypeB" autocomplete="off" 
                                                value="tiles" class="mR5" onClick="return checkPageBlock();"
                                                @if (isset($node->colors["blockImgType"]) 
                                                    && $node->colors["blockImgType"] == 'tiles') CHECKED @endif 
                                                    > Tiled Image
                                        </label></div>
                                        <label class="disBlo mT10 mL10 mR10">
                                            <input type="checkbox" name="blockImgFix" id="blockImgFixID" autocomplete="off"
                                                value="Y" class="mR5" onClick="return checkPageBlock();"
                                                @if (isset($node->colors["blockImgFix"]) 
                                                    && $node->colors["blockImgFix"] == 'Y') CHECKED @endif 
                                                    > Fixed Position
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        <div id="hasPage" class=" @if ($node->isPage()) disBlo @else disNon @endif ">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="m0">Page Settings</h4>
                    </div>
                </div>
                <div class="panel-body">
                    @if ($GLOBALS['SL']->treeRow->TreeType == 'Page' && isset($GLOBALS['SL']->treeRow->TreeName))
                        <div class="row mB20">
                            <div class="col-md-3 fPerc133 slBlueDark">
                                <h4 class="mT5 mB0"><label for="pageTreeNameID">Page Title:</label></h4>
                            </div>
                            <div class="col-md-9 nFld m0 p0">
                                <input type="text" name="pageTreeName" id="pageTreeNameID" autocomplete="off" 
                                    class="form-control input-lg mT0 mB0" 
                                    value="{{ $GLOBALS['SL']->treeRow->TreeName }}" >
                            </div>
                        </div>
                    @endif
                    <div class="row mB20">
                        <div class="col-md-3 fPerc133 slBlueDark">
                            <h4 class="mT20 pT10"><label for="nodeSlugID">Page URL:</label></h4>
                        </div>
                        <div class="col-md-9 nFld m0 p0">
                            <div class="slGrey">
                            @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
                                @if ($GLOBALS['SL']->treeIsAdmin)
                                    {{ $GLOBALS['SL']->sysOpts["app-url"] }}/dash/
                                @else
                                    {{ $GLOBALS['SL']->sysOpts["app-url"] }}/
                                @endif
                            @else
                                @if (isset($GLOBALS['SL']->treeRow->TreeSlug))
                                    @if ($GLOBALS['SL']->treeIsAdmin)
                                        {{ $GLOBALS['SL']->sysOpts["app-url"] }}/dash/{{ 
                                            $GLOBALS['SL']->treeRow->TreeSlug }}/
                                    @else
                                        {{ $GLOBALS['SL']->sysOpts["app-url"] }}/u/{{ 
                                            $GLOBALS['SL']->treeRow->TreeSlug }}/
                                    @endif
                                @endif
                            @endif
                            </div>
                            <input type="text" name="nodeSlug" id="nodeSlugID" autocomplete="off" 
                                class="form-control input-lg mT0 mB0"
                            @if ($GLOBALS['SL']->treeRow->TreeType == 'Page' && isset($GLOBALS['SL']->treeRow->TreeSlug))
                                value="{{ $GLOBALS['SL']->treeRow->TreeSlug }}" >
                            @else
                                value="@if (isset($node->nodeRow->NodePromptNotes)){!! 
                                    $node->nodeRow->NodePromptNotes !!}@endif" >
                            @endif
                        </div>
                    </div>
                    <div class="nFld">
                        <label class="disIn pT5"><input type="text" name="pageFocusField" autocomplete="off" 
                            value="{{ $node->nodeRow->NodeCharLimit }}" class="disIn form-control mR10"
                            style="width: 40px;" > Focus Field 
                            <i class="fPerc80 slGrey">(0 is default, -1 overrides no focus, 
                                otherwise set this a Node ID)</i></label>
                    @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
                        <label class="disBlo mT20"><h4><input type="checkbox" name="homepage" id="homepageID" value="7" 
                            @if ($GLOBALS['SL']->treeRow->TreeOpts%7 == 0) CHECKED @endif autocomplete="off">
                            <i class="fa fa-star mL10" aria-hidden="true"></i> Website Home Page</h4></label>
                        <label class="disBlo mT20"><h4><input type="checkbox" name="adminPage" id="adminPageID" value="3" 
                            @if ($GLOBALS['SL']->treeRow->TreeOpts%3 == 0) CHECKED @endif autocomplete="off">
                            <i class="fa fa-key mL10" aria-hidden="true"></i> Admin-Only Page</h4></label>
                    @else
                        <label class="disBlo red mT20"><input type="checkbox" name="opts29" id="opts29ID" value="29" 
                            @if ($node->nodeRow->NodeOpts%29 == 0) CHECKED @endif autocomplete="off">
                            <i class="fa fa-sign-out mL10" aria-hidden="true"></i> Exit Page <i>(no Next button)</i></label>
                        <label class="disBlo mT20"><input type="checkbox" name="opts59" id="opts59ID" value="59" 
                            @if ($node->nodeRow->NodeOpts%59 == 0) CHECKED @endif autocomplete="off">
                            Hide Progress Bar</label>
                    @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div id="hasBranch" class=" @if ($node->isBranch()) disBlo @else disNon @endif ">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="m0">Branch Title</h3>
                    </div>
                </div>
                <div class="panel-body">
                    <label for="branchTitleID" class="w100 mT0">
                        <div class="nFld mT0"><input type="text" name="branchTitle" id="branchTitleID" 
                            class="form-control input-lg" autocomplete="off" 
                            value="@if (isset($node->nodeRow->NodePromptText)
                                ){!! strip_tags($node->nodeRow->NodePromptText) !!}@endif" ></div>
                    </label>
                    <small class="slGrey">For internal use only.
                    Branches are a great way to mark navigation areas, mark key conditions which greatly impact
                    user experience, associate data families, and/or just internally organize the the tree. 
                    </small>
                </div>
            </div>
        </div>
        
        <div id="hasLoop" class=" @if ($node->isLoopRoot()) disBlo @else disNon @endif ">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="m0">Data Set's Loop Options</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <label class="nPrompt pB20">
                        <input type="radio" name="stepLoop" id="stepLoopN" value="0" autocomplete="off" 
                        @if (!$node->isStepLoop()) CHECKED @endif 
                        > <h4 class="disIn mL5 fPerc133 bld">Standard Loop Behavior</h4>
                        <div class="slGrey f14">
                            From this root page, users can add records to the set until
                            they choose to move on or reach the loop's limits.
                        </div>
                    </label>
                    <label class="nPrompt pB20">
                        <input type="radio" name="stepLoop" id="stepLoopY" value="1" autocomplete="off" 
                        @if ($node->isStepLoop()) CHECKED @endif 
                        > <h4 class="disIn mL5 fPerc133 bld">Step-Through Behavior</h4>
                        <div class="slGrey f14">
                            All items in this data set are added elsewhere beforehand.
                            Then the user is stepped through them one by one.
                        </div>
                    </label>
                    
                    <div class="row mT20">
                        <div class="col-md-6">
                            <label class="nPrompt pB20">
                                <h4 class="disIn mT0">Root Page URL</h4> 
                                <span class="slGrey f12 mL20">
                                    @if (isset($GLOBALS['SL']->treeRow->TreeSlug))
                                        @if ($GLOBALS['SL']->treeIsAdmin)
                                            /dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}
                                        @else
                                            /u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}
                                        @endif
                                    @endif
                                </span>
                                <div class="nFld mT0"><input type="text" name="loopSlug" id="loopSlugID" 
                                    class="form-control input-lg" autocomplete="off" 
                                    value="@if (isset($node->nodeRow->NodePromptNotes)
                                        ){!! $node->nodeRow->NodePromptNotes !!}@endif" ></div>
                            </label>
                        </div>
                        <div class="col-md-6 slGreenDark">
                            <label class="nPrompt">
                                <h4 class="m0 mT0"><span class="slGreenDark">Loop Name</span></h4>
                                <div class="nFld mT0"><select name="nodeDataLoop" id="nodeDataLoopID" 
                                    class="form-control input-lg w100 slGreenDark" autocomplete="off" >
                                    <option value="" @if (!isset($node->nodeRow->NodeDataBranch) 
                                        || $node->nodeRow->NodeDataBranch == "") SELECTED @endif ></option>
                                    @forelse ($GLOBALS['SL']->dataLoops as $setPlural => $setInfo)
                                        <option @if (isset($node->nodeRow->NodeDataBranch) 
                                            && $node->nodeRow->NodeDataBranch == $setPlural) SELECTED @endif 
                                            value="{{ $setPlural }}" >{{ $setPlural }}</option>
                                    @empty
                                    @endforelse
                                </select></div>
                            </label>
                        </div>
                    </div>
                    
                    <label class="nPrompt pB20">
                        <h4 class="disIn mT0">Root Page Instructions</h4>
                        <small class="mL20 slGrey">(text/HTML)</small>
                        <div class="nFld mT0"><textarea name="nodeLoopInstruct" id="nodeLoopInstructID" 
                            class="form-control input-lg" style="height: 100px; font-family: Courier New;" 
                            autocomplete="off" >@if (isset($node->nodeRow->NodePromptText)
                                    ){!! $node->nodeRow->NodePromptText !!}@endif</textarea></div>
                    </label>
                    
                    <div id="stdLoopOpts" class="w100 pB20 @if (!$node->isStepLoop()) disBlo @else disNon @endif ">
                        <label class="nPrompt">
                            <h4>
                            <input type="checkbox" name="stdLoopAuto" id="stdLoopAutoID" value="1" autocomplete="off" 
                            @if (isset($node->nodeRow->NodeDataBranch) && trim($node->nodeRow->NodeDataBranch) != '' 
                                && isset($GLOBALS['SL']->dataLoops[$node->nodeRow->NodeDataBranch]) 
                                && isset($GLOBALS['SL']->dataLoops[$node->nodeRow->NodeDataBranch]->DataLoopAutoGen)
                                && $GLOBALS['SL']->dataLoops[$node->nodeRow->NodeDataBranch]->DataLoopAutoGen == 1) 
                                CHECKED
                            @endif > Auto-Generate new loop items when user clicks "Add" button</h4>
                        </label>
                    </div>
                    <div id="stepLoopOpts" class="w100 pB20 @if ($node->isStepLoop()) disBlo @else disNon @endif ">
                        <label class="nPrompt">
                            <h4>Field Marking A Finished Loop Item (Step)</h4>
                            <div class="nFld mT0"><select name="stepLoopDoneField" id="stepLoopDoneFieldID" 
                                class="form-control input-lg" autocomplete="off" >
                                @if ($node->isStepLoop())
                                    {!! $GLOBALS['SL']->fieldsDropdown(trim($GLOBALS['SL']
                                        ->dataLoops[$node->nodeRow->NodeDataBranch]->DataLoopDoneFld)) !!}
                                @else
                                    {!! $GLOBALS['SL']->fieldsDropdown() !!}
                                @endif
                            </select></div>
                        </label>
                    </div>
                            
                </div>
            </div>
        </div>
        
        <div id="hasCycle" class=" @if ($node->isLoopCycle()) disBlo @else disNon @endif ">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="m0">Data Loop Cycle's Options</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <label class="nPrompt">
                        <h4 class="m0 mB5"><span class="slGreenDark">Loop To Cycle Through</span></h4>
                        <div class="nFld mT0"><select name="nodeDataCycle" id="nodeDataCycleID" 
                            class="form-control input-lg w100 slGreenDark" autocomplete="off" >
                            <option value="" @if (!isset($node->nodeRow->NodeResponseSet) 
                                || $node->nodeRow->NodeResponseSet == "") SELECTED @endif ></option>
                            @forelse ($GLOBALS['SL']->dataLoops as $setPlural => $setInfo)
                                <option @if (isset($node->nodeRow->NodeResponseSet) 
                                    && $node->nodeRow->NodeResponseSet == 'LoopItems::' . $setPlural) SELECTED @endif 
                                    value="{{ $setPlural }}" >{{ $setPlural }}</option>
                            @empty
                            @endforelse
                        </select></div>
                    </label>
                </div>
            </div>
        </div>
        
        <div id="hasSort" class=" @if ($node->isLoopSort()) disBlo @else disNon @endif ">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="m0">Data Loop Sorting Options</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row slGreenDark">
                        <div class="col-md-6 pR20">
                            <label class="nPrompt">
                                <h4 class="m0 mB5"><span class="slGreenDark">Data Loop:</span></h4>
                                <div class="nFld mT0"><select name="nodeDataSort" id="nodeDataSortID" 
                                    class="form-control input-lg w100 slGreenDark" autocomplete="off" >
                                    <option value="" @if (!isset($node->nodeRow->NodeResponseSet) 
                                        || $node->nodeRow->NodeResponseSet == "") SELECTED @endif ></option>
                                    @forelse ($GLOBALS['SL']->dataLoops as $setPlural => $setInfo)
                                        <option @if (isset($node->nodeRow->NodeResponseSet) 
                                            && $node->nodeRow->NodeResponseSet == 'LoopItems::' . $setPlural) 
                                                SELECTED
                                            @endif value="{{ $setPlural }}" >{{ $setPlural }}</option>
                                    @empty
                                    @endforelse
                                </select></div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="nPrompt">
                                <h4 class="m0 mB5"><span class="slGreenDark">Loop Sorting Field:</span></h4>
                                <div class="nFld mT0"><select name="DataStoreSort" id="DataStoreSortID" 
                                    class="form-control input-lg" autocomplete="off" onClick="return checkData();" >
                                    {!! $GLOBALS['SL']->fieldsDropdown((isset($node->nodeRow->NodeDataStore)) 
                                        ? trim($node->nodeRow->NodeDataStore) : '') !!}
                                </select></div>
                                <i class="f12">*Must be integer field within Data Loop's Table.</i>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            
        <div id="hasDataManip" class=" @if ($node->isDataManip()) disBlo @else disNon @endif ">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="m0"><i class="fa fa-database"></i> Data Manipulation Tools</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <small class="slGrey">
                        Moving forward with this node conditionally visible, it will run one of these tasks. 
                        Children of this node link to it by setting their data subset to this helper table. 
                        New records are automatically linked to core record and/or loop's set item.
                    </small>
                    <div class="radio">
                        <label>
                            <input type="radio" name="dataManipType" id="dataManipTypeNew" value="New" 
                                onClick="return checkDataManipFlds();" autocomplete="off" 
                                @if (isset($node->nodeRow->NodeType) 
                                    && $node->nodeRow->NodeType == 'Data Manip: New') CHECKED @endif >
                                <h4 class="pL5 mTn10">Create New Record 
                                in <span class="slGreenDark">Data Family</span></h4>
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="dataManipType" id="dataManipTypeUpdate" value="Update" 
                                onClick="return checkDataManipFlds();" autocomplete="off" 
                                @if (isset($node->nodeRow->NodeType) 
                                    && $node->nodeRow->NodeType == 'Data Manip: Update') CHECKED @endif >
                                <h4 class="pL5 mTn10">Update Family Record 
                                in <span class="slGreenDark">Data Family</span></h4>
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="dataManipType" id="dataManipTypeWrap" value="Wrap" 
                                onClick="return checkDataManipFlds();" autocomplete="off" 
                                @if (isset($node->nodeRow->NodeType) 
                                    && $node->nodeRow->NodeType == 'Data Manip: Wrap') CHECKED @endif >
                                <h4 class="pL5 mTn10">Just Wrap Children 
                                in <span class="slGreenDark">Data Family</span></h4>
                        </label>
                    </div>
                    <div class="radio pT20">
                        <label>
                            <input type="radio" name="dataManipType" id="dataManipTypeCloseSess" value="Close Sess"
                                onClick="return checkDataManipFlds();" autocomplete="off" 
                                @if (isset($node->nodeRow->NodeType) 
                                    && $node->nodeRow->NodeType == 'Data Manip: Close Sess') CHECKED @endif >
                                <h4 class="pL5 mB0 mTn10 slGrey">End User Session for Form Tree</h4>
                                <div id="manipCloseSess" class=" @if (isset($node->nodeRow->NodeType) 
                                    && $node->nodeRow->NodeType == 'Data Manip: Close Sess') disBlo 
                                    @else disNon @endif "><select name="dataManipCloseSessTree" class="form-control"
                                        style="width: 250px;" autocomplete="off" >
                                        @forelse ($treeList as $t)
                                            <option value="{{ $t->TreeID }}" 
                                                @if ($t->TreeID == $node->nodeRow->NodeResponseSet) SELECTED @endif
                                                >{{ $t->TreeName }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                        </label>
                    </div>
                    <div id="dataNewRecord" class=" @if (isset($node->nodeRow->NodeType) 
                        && in_array($node->nodeRow->NodeType, ['Data Manip: Wrap', 'Data Manip: Close Sess'])) disNon 
                        @else disBlo @endif ">
                        <div class="row pT5">
                            <div class="col-md-5 f14">
                                <label class="w100">
                                    <h4 class="m0">Set Record Field</h4>
                                </label>
                            </div>
                            <div class="col-md-1 taC"></div>
                            <div class="col-md-3">
                                <h4 class="m0">Custom Value</h4>
                            </div>
                            <div class="col-md-3">
                                <h4 class="m0">Definitions</h4>
                            </div>
                        </div>
                        <div class="row pT5 pB10">
                            <div class="col-md-5 f14">
                                <label class="w100">
                                    <div class="nFld mT0">
                                        <select name="manipMoreStore" id="manipMoreStoreID"
                                        class="form-control input-lg" autocomplete="off" onClick="return checkData();" >
                                        {!! $GLOBALS['SL']->fieldsDropdown((isset($node->nodeRow->NodeDataStore)) 
                                            ? trim($node->nodeRow->NodeDataStore) : '') !!}
                                        </select>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-1 taC">
                                <div class="mTn20"><h1 class="m0 slGreenDark">=</h1></div>
                            </div>
                            <div class="col-md-2">
                                <div class="nFld mT0"><input type="text" name="manipMoreVal" 
                                    class="form-control input-lg" @if (isset($node->nodeRow->NodeDefault)) 
                                        value="{{ $node->nodeRow->NodeDefault }}" @endif >
                                </div>
                            </div>
                            <div class="col-md-1 taC">
                                <h4 class="mT10 slGreenDark">or</h4>
                            </div>
                            <div class="col-md-3">
                                <div class="nFld mT0"><select name="manipMoreSet" class="form-control input-lg" 
                                    autocomplete="off" >
                                    {!! $GLOBALS['SL']->allDefsDropdown((isset($node->nodeRow->NodeResponseSet)) 
                                        ? $node->nodeRow->NodeResponseSet : '') !!}
                                </select></div>
                            </div>
                        </div>
                        
                        @for ($i = 0; $i < $resLimit; $i++)
                            <div id="dataManipFld{{ $i }}" 
                                class=" @if (isset($node->dataManips[$i])) disBlo @else disNon @endif ">
                                <div class="row mT5 mB10">
                                    <div class="col-md-5">
                                        <div class="nFld mT0">
                                            <select name="manipMore{{ $i }}Store" id="manipMore{{ $i }}StoreID" 
                                                class="form-control input-lg" autocomplete="off" 
                                                onClick="return checkData();" >
                                            @if (isset($node->dataManips[$i]) && isset($node->dataManips[$i]->NodeDataStore))
                                                {!! $GLOBALS['SL']->fieldsDropdown($node->dataManips[$i]->NodeDataStore) !!}
                                            @else {!! $GLOBALS['SL']->fieldsDropdown() !!} @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-1 taC">
                                        <div class="mTn20"><h1 class="m0 slGreenDark">=</h1></div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="nFld mT0">
                                            <input type="text" name="manipMore{{ $i }}Val" 
                                                class="form-control input-lg" @if (isset($node->dataManips[$i]) 
                                                    && isset($node->dataManips[$i]->NodeDefault))
                                                    value="{!! $node->dataManips[$i]->NodeDefault !!}"
                                                @else value="" @endif >
                                        </div>
                                    </div>
                                    <div class="col-md-1 taC">
                                        <h4 class="mT10 slGreenDark">or</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="nFld mT0">
                                            <select name="manipMore{{ $i }}Set" 
                                                class="form-control input-lg" autocomplete="off" >
                                                @if (isset($node->dataManips[$i]) && isset($node->dataManips[$i]->NodeResponseSet))
                                                    {!! $GLOBALS['SL']->allDefsDropdown($node->dataManips[$i]->NodeResponseSet) !!}
                                                @else {!! $GLOBALS['SL']->allDefsDropdown() !!} @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
        
        <div id="hasBigButt" class=" @if ($node->isBigButt()) disBlo @else disNon @endif ">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="m0">Big Button Settings</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <h4 class="m0">Button Text</h4>
                    <div class="nFld m0 mB20">
                        <input type="text" name="bigBtnText" id="bigBtnTextID" class="form-control input-lg" 
                            @if (isset($node->nodeRow->NodeDefault)) value="{{ $node->nodeRow->NodeDefault }}" 
                            @endif onKeyUp="return previewBigBtn();" >
                    </div>
                    <h4 class="m0">Button On Click Javascript</h4>
                    <div class="nFld m0">
                        <input type="text" name="bigBtnJS" class="form-control input-lg" 
                            @if (isset($node->nodeRow->NodeDataStore)) value="{{ $node->nodeRow->NodeDataStore }}" 
                            @endif >
                    </div>
                    <div class="row mT20 mB20">
                        <div class="col-md-6">
                            <h4 class="m0">Button Style</h4>
                            <div class="nFld m0">
                                <select name="bigBtnStyle" id="bigBtnStyleID" class="form-control input-lg"
                                    onChange="return previewBigBtn();">
                                    <option value="Default" @if (!isset($node->nodeRow->NodeResponseSet) 
                                        || $node->nodeRow->NodeResponseSet == 'Default') SELECTED @endif 
                                        >Default Button</option>
                                    <option value="Primary" @if ($node->nodeRow->NodeResponseSet == 'Primary') 
                                        SELECTED @endif >Primary Button</option>
                                    <option value="Text" @if ($node->nodeRow->NodeResponseSet == 'Text') 
                                        SELECTED @endif >Text/HTML Link</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 pT20 taR">
                            <label class="mT20"><input type="checkbox" name="opts43" value="43" 
                                @if ($node->nodeRow->NodeOpts%43 == 0) CHECKED @endif > 
                                <h4 class="disIn">Toggle Child Nodes On Click</h4>
                            </label>
                        </div>
                    </div>
                    Preview:
                    <div id="buttonPreview" class="w100 m0"></div>
                </div>
                <div class="p20">
                    <i>Optionally, you can fill in the "Question or Prompt for User" section below, which can provide
                    information or instructions to the user before the Big Button is printed.</i>
                </div>
            </div>
        </div>
        
        <div id="hasPrompt" class=" @if ($node->isSpecial() || $node->isWidget() || $node->isLayout()) disNon 
            @else disBlo @endif ">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <label for="nodePromptTextID"><h4 class="m0 disIn mR20">Question or Prompt for User</h4> 
                            <small>(text/HTML)</small></label>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="nFld"><textarea name="nodePromptText" id="nodePromptTextID" class="form-control" 
                        style="height: 200px; font-family: Courier New;" autocomplete="off" 
                            >@if (isset($node->nodeRow->NodePromptText)
                                ){!! $node->nodeRow->NodePromptText !!}@endif</textarea></div>
                        
                    <div class="row mT20">
                        <div class="col-md-6">
                            <label class="w100">
                                <a id="extraSmallBtn" href="javascript:void(0)" class="f12"
                                    >+ Small Instructions or Side-Notes</a> 
                                <div id="extraSmall" class="w100 @if (isset($node->nodeRow->NodePromptNotes) 
                                    && trim($node->nodeRow->NodePromptNotes) != '') disBlo @else disNon @endif ">
                                    <div class="nFld mT0"><textarea name="nodePromptNotes" class="form-control" 
                                        style="width: 100%; height: 100px;" autocomplete="off" 
                                            >@if (isset($node->nodeRow->NodePromptNotes)
                                            ){!! $node->nodeRow->NodePromptNotes !!}@endif</textarea></div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="w100">
                                <a id="extraHTMLbtn" href="javascript:void(0)" class="f12"
                                    >+ HTML/JS/CSS Extras After Node Field</a> 
                                <div id="extraHTML" class="w100 @if (isset($node->nodeRow->NodePromptAfter) 
                                    && trim($node->nodeRow->NodePromptAfter) != '') disBlo @else disNon @endif ">
                                    <div class="nFld mT0"><textarea name="nodePromptAfter" class="form-control" 
                                        style="width: 100%; height: 100px;" autocomplete="off"
                                            >@if (isset($node->nodeRow->NodePromptAfter)
                                            ){!! $node->nodeRow->NodePromptAfter !!}@endif</textarea></div>
                                    <span class="slGrey f12">"[[nID]]" will be replaced with node ID</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                </div>
            </div>
            
        </div>
        
        <div id="hasResponse" class=" @if ($node->isSpecial() || $node->isWidget() || $node->isLayout()) disNon 
            @else disBlo @endif ">
        
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="m0">User Response Settings</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row mB20">
                        <div class="col-md-4">
                            <label class="w100">
                                <h4 class="m0 slBlueDark">User Response Type</h4>
                                <div class="nFld m0"><select name="nodeTypeQ" id="nodeTypeQID" 
                                    class="form-control input-lg slBlueDark w100" 
                                    onChange="return changeResponseType(this.value);" autocomplete="off" >
                                @foreach ($nodeTypes as $type)
                                    <option value="{{ $type }}" @if (isset($node->nodeRow->NodeType) 
                                        && $node->nodeRow->NodeType == $type) SELECTED @endif >{{ $type }}</option>
                                @endforeach
                                </select></div>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="w100">
                                <h4 class="m0 slGreenDark">Store User Response</h4>
                                <div class="nFld m0"><select name="nodeDataStore" class="form-control input-lg w100" 
                                    autocomplete="off" >
                                    {!! $GLOBALS['SL']->fieldsDropdown(isset($node->nodeRow->NodeDataStore) 
                                        ? trim($node->nodeRow->NodeDataStore) : '') !!}
                                </select></div>
                            </label>
                        </div>
                        <div class="col-md-4 pT20">
                            <label for="opts5ID" class="red fPerc133 mT20">
                                <input type="checkbox" name="opts5" id="opts5ID" value="5" autocomplete="off" 
                                    @if ($node->isRequired()) CHECKED @endif 
                                    onClick="return changeRequiredType();"> User Response Required
                            </label>
                        </div>
                    </div>
                    
                    <div class="row mB20">
                        <div class="col-md-4">
                            <label class="w100">
                                <h4 class="m0">Default Value</h4>
                                <div class="nFld w100 mT0"><input type="text" name="nodeDefault" id="nodeDefaultID" 
                                    class="form-control w50" autocomplete="off" 
                                    @if (isset($node->nodeRow->NodeDefault)) value="{{ $node->nodeRow->NodeDefault }}" 
                                    @else value="" @endif ></div>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <div id="resNotWrdCnt" class=" @if (isset($node->nodeRow->NodeType) && 
                                in_array($node->nodeRow->NodeType, ['Text', 'Long Text'])) disBlo 
                                @else disNon @endif ">
                                <label for="opts31ID" class="mB10"><h4 class="m0">
                                    <input type="checkbox" name="opts31" id="opts31ID" value="31" autocomplete="off" 
                                        @if ($node->nodeRow->NodeOpts%31 == 0) CHECKED @endif 
                                        > Show Word Count
                                </h4></label>
                                <label for="opts47ID"><h4 class="m0">
                                    <input type="checkbox" name="opts47" id="opts47ID" value="47" autocomplete="off" 
                                        @if ($node->nodeRow->NodeOpts%47 == 0) CHECKED @endif 
                                        onClick="return toggleWordCntLimit();" > Limit Word Count
                                </h4></label>
                                <div id="resWordLimit" class="mB20 @if ($node->nodeRow->NodeOpts%47 == 0) disBlo 
                                    @else disNon @endif ">
                                    <label class="w100">
                                        <div class="nFld mT0 mL20"><input name="nodeCharLimit" id="nodeCharLimitID" 
                                            type="number" class="form-control w50" autocomplete="off" 
                                            @if (isset($node->nodeRow->NodeCharLimit) 
                                                && intVal($node->nodeRow->NodeCharLimit) > 0) 
                                                value="{{ $node->nodeRow->NodeCharLimit }}" 
                                            @else value="" @endif ></div>
                                    </label>
                                </div>
                            </div>
                            <?php /*
                            <div id="resNotMulti" class="mB20 @if (isset($node->nodeRow->NodeType) && 
                                in_array($node->nodeRow->NodeType, ['Text', 'Long Text', 'Uploads'])) disBlo 
                                @else disNon @endif ">
                                <label class="w100">
                                    <h4 class="m0">Character/Upload Limit</h4>
                                    <div class="nFld m0"><input type="number" name="nodeCharLimit" id="nodeCharLimitID" 
                                        class="form-control disIn w50" autocomplete="off" 
                                        @if (isset($node->nodeRow->NodeCharLimit)) value="{{ $node->nodeRow->NodeCharLimit }}" 
                                        @else value="" @endif ></div>
                                </label>
                            </div> */ ?>
                        </div>
                        <div class="col-md-4">
                            <div id="resCanAuto" class=" @if (isset($node->nodeRow->NodeType) && 
                                in_array($node->nodeRow->NodeType, ['Text'])) disBlo 
                                @else disNon @endif ">
                                <label>
                                    <h4 class="m0">Autofill Suggestions</h4>
                                    <div class="nFld m0"><select name="nodeTextSuggest" id="nodeTextSuggestID" 
                                        class="form-control w100" autocomplete="off" >
                                        <option value="" @if (!isset($node->nodeRow->NodeTextSuggest) 
                                            || $node->nodeRow->NodeTextSuggest == '') SELECTED @endif ></option>
                                        @forelse ($defs as $def)
                                            <option value="{{ $def->DefSubset }}" @if (isset($node->nodeRow->NodeTextSuggest) 
                                                && $node->nodeRow->NodeTextSuggest == $def->DefSubset) SELECTED @endif 
                                                >{{ $def->DefSubset }}</option>
                                        @empty
                                        @endforelse
                                    </select></div>
                                </label>
                                <div class="mT10 mB10">
                                    <label for="opts41ID"><h4 class="m0">
                                        <input type="checkbox" name="opts41" id="opts41ID" value="41" autocomplete="off" 
                                            @if ($node->nodeRow->NodeOpts%41 == 0) CHECKED @endif 
                                            > Echo Response Edits To Div
                                    </h4></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="DateOpts" class=" @if (isset($node->nodeRow->NodeType) && 
                        in_array($node->nodeRow->NodeType, ['Date', 'Date Picker', 'Date Time'])) disBlo @else disNon @endif ">
                        <h4>Time Travelling Restriction</h4>
                        <label class="disIn">
                            <input type="radio" name="dateOptRestrict" value="0"
                                @if (!isset($node->nodeRow->NodeCharLimit) || intVal($node->nodeRow->NodeCharLimit) == 0) 
                                    CHECKED
                                @endif >
                                Any time is fine
                        </label>
                        <label class="disIn pL20">
                            <input type="radio" name="dateOptRestrict" value="-1"
                                @if (isset($node->nodeRow->NodeCharLimit) && intVal($node->nodeRow->NodeCharLimit) < 0) 
                                    CHECKED
                                @endif >
                                Must be in the past
                        </label>
                        <label class="disIn pL20">
                            <input type="radio" name="dateOptRestrict" value="1"
                                @if (isset($node->nodeRow->NodeCharLimit) && intVal($node->nodeRow->NodeCharLimit) > 0) 
                                    CHECKED
                                @endif >
                                Must be in the future
                        </label>
                    </div>
                    
                    <div id="resOpts" class=" @if (isset($node->nodeRow->NodeType) && in_array($node->nodeRow->NodeType, 
                        ['Radio', 'Checkbox', 'Drop Down', 'Other/Custom'])) disBlo @else disNon @endif ">
                        <h4>Response Options Provided To User:</h4>
                        <div class="row mB20">
                            <div class="col-md-6 nFld mT0">
                                <select name="responseListType" id="responseListTypeID" class="form-control input-lg"
                                    onChange="changeResponseListType();" autocomplete="off" >
                                    <option value="manual" @if ($currDefinition == '' && $currLoopItems == '' 
                                        && $currTblRecs == '') SELECTED @endif > Manually type options below</option>
                                    <option value="auto-def" @if ($currDefinition != '') SELECTED @endif
                                        > Pull from Definition Set</option>
                                    <option value="auto-loop" @if ($currLoopItems != '') SELECTED @endif
                                        > Pull from Entered Loop Items</option>
                                    <option value="auto-tbl" @if ($currTblRecs != '') SELECTED @endif
                                        > Pull from Entered Table Records</option>
                                <select>
                            </div>
                            <div class="col-md-6">
                                <div id="responseOptDefs" class="nFld mT0
                                    @if ($currDefinition != '') disBlo @else disNon @endif">
                                    <select name="responseDefinition" id="responseDefinitionID" 
                                        class="form-control input-lg" onChange="changeResponseListType();" 
                                        autocomplete="off">
                                        <option value="" @if ($currDefinition == '') SELECTED @endif 
                                            > Select Definition Set... </option>
                                        @forelse ($defs as $def)
                                            @if (trim($def->DefSubset) != '')
                                                <option value="{{ $def->DefSubset }}" 
                                                    @if ($currDefinition == $def->DefSubset) SELECTED @endif 
                                                    >{{ $def->DefSubset }}</option>
                                            @endif
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                                <div id="responseOptLoops" class="nFld mT0
                                    @if ($currLoopItems != '') disBlo @else disNon @endif">
                                    <select name="responseLoopItems" id="responseLoopItemsID" 
                                        class="form-control input-lg" onChange="changeResponseListType();" 
                                        autocomplete="off">
                                        <option value="" @if ($currLoopItems == '') SELECTED @endif 
                                            > Select Loop... </option>
                                        @forelse ($GLOBALS['SL']->dataLoops as $plural => $loop)
                                            <option value="{{ $plural }}"
                                                @if ($currLoopItems == $plural) SELECTED @endif 
                                                >{{ $plural }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                                <div id="responseOptTbls" class="nFld mT0
                                    @if ($currTblRecs != '') disBlo @else disNon @endif">
                                    <select name="responseTables" id="responseTablesID" 
                                        class="form-control input-lg" onChange="changeResponseListType();" 
                                        autocomplete="off">
                                        <option value="" @if ($currTblRecs == '') SELECTED @endif 
                                            > Select Data Table... </option>
                                        @forelse ($GLOBALS['SL']->tbl as $tID => $tblName)
                                            <option value="{{ $tblName }}"
                                                @if ($currTblRecs == $tblName) SELECTED @endif >
                                                @if ($GLOBALS['SL']->tblEng[$tID] != $tblName)
                                                    {{ $GLOBALS['SL']->tblEng[$tID] }} ({{ $tblName }})
                                                @else {{ $GLOBALS['SL']->tblEng[$tID] }} @endif
                                                </option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row pB10">
                            <div class="col-md-4">
                                <h4 class="m0">What User Will See</h4><div class="slGrey fPerc80">[Text/HTML]</div>
                            </div>
                            <div class="col-md-8">
                                <h4 class="m0">Value Stored In Database</h4>
                                <div class="disIn slGrey fPerc80">
                                    <i title="Children displayed only with certain responses"
                                        class="fa fa-code-fork fa-flip-vertical mR5"></i>
                                        Reveals Child Nodes</div>
                                <div class="disIn slGrey fPerc80 mL20">
                                    <i class="fa fa-circle-o mR0"></i><i class="fa fa-circle mL0 mR5"></i>
                                    Mutually Exclusive (De-selects other responses)
                                </div>
                            </div>
                        </div>
                        
                        @forelse ($node->responses as $r => $res)
                            <div id="r{{ $r }}" class="row pB20">
                                <div class="col-md-4">
                                    <div class="nFld m0"><textarea name="response{{ $r }}" id="response{{ $r }}ID" 
                                        type="text" class="form-control" style="height: 65px;" autocomplete="off" 
                                        onKeyUp="return checkRes();" @if ($currDefinition != '') DISABLED @endif 
                                        >{{ $res->NodeResEng }}</textarea></div>
                                </div>
                                <div class="col-md-8">
                                    <div class="nFld m0">
                                        <input name="response{{ $r }}Val" id="response{{ $r }}vID" 
                                            type="text" value="{{ $res->NodeResValue }}" onKeyUp="return checkRes();" 
                                            class="form-control" autocomplete="off" 
                                            @if ($currDefinition != '') DISABLED @endif >
                                    </div>
                                    <div class="row mT5">
                                        <div class="col-md-6">
                                            <label class="mL5">
                                                <input type="checkbox"  value="1" class="showKidBox" autocomplete="off"
                                                    name="response{{ $r }}ShowKids" id="r{{ $r }}showKID"
                                                    @if ($node->indexShowsKid($r)) CHECKED @endif >
                                                    <i title="Children displayed only with certain responses"
                                                    class="fa fa-code-fork fa-flip-vertical mL5 fPerc133"></i>
                                            </label>
                                            <div id="kidFork{{ $r }}" class="mL5 
                                                @if ($node->indexShowsKid($r)) disIn @else disNon @endif ">
                                                @if (isset($childNodes) && sizeof($childNodes) > 0)
                                                    @if (sizeof($childNodes) == 1)
                                                        #{{ $childNodes[0]->NodeID }}
                                                        <input type="hidden" name="kidForkSel{{ $r }}" 
                                                            value="{{ $childNodes[0]->NodeID }}">
                                                    @else
                                                        <select name="kidForkSel{{ $r }}" autocomplete="off"
                                                            class="form-control input-xs disIn" style="width: 70px;">
                                                        @foreach ($childNodes as $k => $kidNode)
                                                            <option value="{{ $kidNode->NodeID }}"
                                                                @if ($node->indexShowsKidNode($r) == $kidNode->NodeID) 
                                                                SELECTED @endif >#{{ $kidNode->NodeID }}</option>
                                                        @endforeach
                                                        </select>
                                                    @endif
                                                @else
                                                    <input type="hidden" name="kidForkSel{{ $r }}" value="1000000000">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label id="resMutEx{{ $r }}" class="mL5 @if (isset($node->nodeRow->NodeType)
                                                && $node->nodeRow->NodeType == 'Checkbox')) disBlo @else disNon @endif "
                                                ><nobr><input type="checkbox" name="response{{ $r }}MutEx" value="1" 
                                                    @if ($node->indexMutEx($r)) CHECKED @endif autocomplete="off" >
                                                    <i class="fa fa-circle-o mL10 mR0 fPerc133"></i> 
                                                    <i class="fa fa-circle mLn5 fPerc133"></i></nobr>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                        @endforelse
                        @if ($currDefinition == '')
                            @for ($r = sizeof($node->responses); $r < $resLimit; $r++)
                                <div id="r{{ $r }}" class="row pB20 
                                    @if ($r == sizeof($node->responses)) disBlo @else disNon @endif ">
                                    <div class="col-md-4">
                                        <div class="nFld m0">
                                            <textarea name="response{{ $r }}" id="response{{ $r }}ID" 
                                                type="text" class="form-control" style="height: 65px;" 
                                                autocomplete="off" onKeyUp="return checkRes();" 
                                                @if ($currDefinition != '') DISABLED @endif ></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="nFld m0">
                                            <input type="text" name="response{{ $r }}Val" id="response{{ $r }}vID" 
                                                value="" onKeyUp="return checkRes();" class="form-control mB5" 
                                                autocomplete="off" @if ($currDefinition != '') DISABLED @endif >
                                            <div class="row mT5">
                                                <div class="col-md-6">
                                                    <label class="disIn mR20"><input name="response{{ $r }}ShowKids" 
                                                        type="checkbox" autocomplete="off" value="1" >
                                                         <i title="Children displayed only with certain responses"
                                                        class="fa fa-code-fork fa-flip-vertical mL10"></i></label>
                                                </div>
                                                <div class="col-md-6">
                                                    <label id="resMutEx{{ $r }}" 
                                                        class=" @if (isset($node->nodeRow->NodeType) 
                                                        && $node->nodeRow->NodeType == 'Checkbox')) disIn @else disNon 
                                                        @endif "><nobr>
                                                        <input type="checkbox" name="response{{ $r }}MutEx" value="1" 
                                                            autocomplete="off" >
                                                            <i class="fa fa-circle-o mL10 mR0"></i> 
                                                            <i class="fa fa-circle mLn5"></i></nobr>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        @endif
                    </div> <!-- end resOpts -->
                    
                </div>
            </div> <!-- end Response Options panel -->
        
        </div> <!-- end hasResponse -->
        
        <div id="hasSurvWidget" class=" @if ($node->isWidget()) disBlo @else disNon @endif ">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="m0">SurvLoop Widget Options</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row mB20">
                        <div class="col-md-4">
                            <label class="nPrompt">
                                <h4 class="m0 mB5">Widget Type</h4>
                                <div class="nFld"><select name="nodeSurvWidgetType" id="nodeSurvWidgetTypeID"
                                    class="form-control input-lg w100" autocomplete="off" 
                                    onChange="return changeWidgetType();" >
                                    
                                    @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
                                    
                                        <option value="Search" 
                                            @if ($node->nodeType == 'Search') SELECTED @endif 
                                            >Search Bar</option>
                                        <option value="Search Results" 
                                            @if ($node->nodeType == 'Search Results') SELECTED @endif 
                                            >Search Results</option>
                                        <option value="Search Featured" 
                                            @if ($node->nodeType == 'Search Featured') SELECTED @endif 
                                            >Search Featured</option>
                                        <option value="Record Previews" 
                                            @if ($node->nodeType == 'Record Previews') SELECTED @endif 
                                            >Record Previews</option>
                                        <option value="Record Full" 
                                            @if ($node->nodeType == 'Record Full') SELECTED @endif 
                                            >Record Full</option>
                                        <option value="Incomplete Sess Check" 
                                            @if ($node->nodeType == 'Incomplete Sess Check') SELECTED @endif 
                                            >Incomplete Sessions Check</option>
                                        <option value="Member Profile Basics" 
                                            @if ($node->nodeType == 'Member Profile Basics') SELECTED @endif 
                                            >Member Profile Basics</option>
                                            
                                    @else
                                    
                                        <option value="Back Next Buttons" 
                                            @if ($node->nodeType == 'Back Next Buttons') SELECTED @endif 
                                            >Extra Back-Next Buttons</option>
                                    
                                    @endif
                                    
                                    <option value="Send Email" 
                                        @if ($node->nodeType == 'Send Email') SELECTED @endif 
                                        >Send Email</option>
                                
                                </select></div>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="nPrompt @if ($GLOBALS['SL']->treeRow->TreeType != 'Page') disNon @endif ">
                                <h4 class="m0 mB5">Related Tree</h4>
                                <div class="nFld"><select name="nodeSurvWidgetTree" id="nodeSurvWidgetTreeID"
                                    class="form-control input-lg w100" autocomplete="off" >
                                    {!! $GLOBALS["SL"]->sysTreesDrop($node->nodeRow->NodeResponseSet, 'forms', 'all') !!}
                                </select></div>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label id="widgetRecLimitID" class="nPrompt @if (in_array($node->nodeType, [
                                'Search Results', 'Search Featured', 'Record Previews'
                                ])) disBlo @else disNon @endif ">
                                <h4 class="m0 mB5">Record Limit</h4>
                                <div class="nFld"><input type="number" name="nodeSurvWidgetLimit" id="nodeSurvWidgetLimitID"
                                    class="form-control input-lg w100" autocomplete="off" 
                                    value="{!! intVal($node->nodeRow->NodeCharLimit) !!}" ></div>
                            </label>
                        </div>
                    </div>
                    <div id="widgetPrePost" class="row mT20 @if ($GLOBALS['SL']->treeRow->TreeType != 'Page'
                        || $node->nodeType == 'Send Email') disNon @else disBlo @endif ">
                        <div class="col-md-6">
                            <label class="nPrompt">
                                <h4 class="m0 mB5">Pre-Widget</h4>
                                <div class="nFld"><textarea name="nodeSurvWidgetPre" id="nodeSurvWidgetPreID" 
                                    class="form-control w100" style="height: 150px; font-family: Courier New;" autocomplete="off" 
                                    >@if (isset($node->nodeRow->NodePromptText) 
                                        ){!! $node->nodeRow->NodePromptText !!}@endif</textarea></div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="nPrompt">
                                <h4 class="m0 mB5">Post-Widget</h4>
                                <div class="nFld"><textarea name="nodeSurvWidgetPost" id="nodeSurvWidgetPostID" 
                                    class="form-control w100" style="height: 150px; font-family: Courier New;" autocomplete="off" 
                                    >@if (isset($node->nodeRow->NodePromptAfter) 
                                        ){!! $node->nodeRow->NodePromptAfter !!}@endif</textarea></div>
                            </label>
                        </div>
                    </div>
                    <div id="widgetEmail" class="mT20 @if ($node->nodeType == 'Send Email') disBlo @else disNon @endif">
                        {!! $widgetEmail !!}
                    </div>
                </div>
            </div>
        </div>
    
        <div id="hasHeroImg" class=" @if ($node->isHeroImg()) disBlo @else disNon @endif ">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="m0">Hero Image Settings</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row mB20">
                        <div class="col-md-4 pT5">
                            <label for="pageHeroImgID"><h4 class="m0 disIn">Image URL</h4> 
                                <span class="mL10">~2000x1000 pixels</span></label>
                        </div>
                        <div class="col-md-8 nFld mT0">
                            <input type="text" name="pageHeroImg" name="pageHeroImgID" autocomplete="off" 
                                class="form-control input-lg" value="{{ $node->nodeRow->NodeTextSuggest }}">
                        </div>
                    </div>
                    <div class="row mB20">
                        <div class="col-md-4 pT5">
                            <label for="pageHeroImgTxtID"><h4 class="m0">Text Over Image</h4></label>
                        </div>
                        <div class="col-md-8 nFld mT0">
                            <input type="text" name="pageHeroImgTxt" name="pageHeroImgTxtID" autocomplete="off" 
                                class="form-control input-lg" value="{{ $node->nodeRow->NodePromptAfter }}">
                        </div>
                    </div>
                    <div class="row mB20">
                        <div class="col-md-4 pT5">
                            <label for="pageHeroImgUrlID"><h4 class="m0">Action Button Text</h4></label>
                        </div>
                        <div class="col-md-8 nFld mT0">
                            <input type="text" name="pageHeroImgBtn" name="pageHeroImgBtnID" autocomplete="off" 
                                class="form-control input-lg" value="{{ $node->nodeRow->NodeDefault }}">
                        </div>
                    </div>
                    <div class="row mB20">
                        <div class="col-md-4 pT5">
                            <label for="pageHeroImgUrlID"><h4 class="m0">Action Button Link URL</h4></label>
                        </div>
                        <div class="col-md-8 nFld mT0">
                            <input type="text" name="pageHeroImgUrl" name="pageHeroImgUrlID" autocomplete="off" 
                                class="form-control input-lg" value="{{ $node->nodeRow->NodeResponseSet }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="hasLayout" class=" @if ($node->isLayout()) disBlo @else disNon @endif ">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="m0">Layout Options</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row mB20">
                        <div class="col-md-6">
                            <label class="nPrompt">
                                <h4 class="m0 mB5">Layout Node Type</h4>
                                <div class="nFld"><select name="nodeLayoutType" id="nodeLayoutTypeID"
                                    class="form-control input-lg w100" autocomplete="off" 
                                    onChange="return changeLayoutType();" >
                                    
                                    <option value="Page Block" 
                                        @if ($node->nodeType == 'Page Block' || trim($node->nodeType) == '') SELECTED @endif 
                                        >Just A Page Block</option>
                                    <option value="Layout Row" 
                                        @if ($node->nodeType == 'Layout Row') SELECTED @endif 
                                        >Layout Row (To Contain Multiple Columns)</option>
                                    <option value="Layout Column" 
                                        @if ($node->nodeType == 'Layout Column') SELECTED @endif 
                                        >Layout Column (Contained By Layout Row)</option>
                                    
                                </select></div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label id="layoutSizeRow" class="nPrompt @if ($node->nodeType == 'Layout Row') disBlo @else disNon @endif ">
                                <h4 class="m0 mB5"># of Columns in Row</h4>
                                <div class="nFld">
                                    <select name="nodeLayoutLimitRow" id="nodeLayoutLimitRowID" autocomplete="off"
                                    class="form-control input-lg w100" >
                                    @for ($i=1; $i<13; $i++)
                                        <option value="{{ $i }}" 
                                            @if ($i ==intVal($node->nodeRow->NodeCharLimit)) SELECTED @endif 
                                            >{{ $i }}</optioN>
                                    @endfor
                                    </select>
                                </div>
                            </label>
                            <label id="layoutSizeCol" class="nPrompt @if ($node->nodeType == 'Layout Column') disBlo @else disNon @endif ">
                                <h4 class="m0 mB5">Column Width (in 12<sup>th</sup>s)</h4>
                                <div class="nFld"><input type="number" name="nodeLayoutLimitCol" id="nodeLayoutLimitColID"
                                    class="form-control input-lg w100" autocomplete="off" 
                                    value="{!! intVal($node->nodeRow->NodeCharLimit) !!}" ></div>
                                (4 columns could have width 3, 2 columns could have 6 each, etc.)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <div class="col-md-4">
    
        <input type="submit" value="Save Node Changes" class="btn btn-xl btn-primary w100" 
            @if (!$canEditTree) DISABLED @endif >
        
        <h2 class="slBlueDark mB0"><i class="fa fa-cube mR5" aria-hidden="true"></i> Node Type</h2>
        <div class="nFld mT0">
            <select name="nodeType" id="nodeTypeID" class="form-control input-lg" 
                autocomplete="off" onChange="return changeNodeType(this.value);" {{ $nodeTypeSel }} >
            
            @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
            
                <option value="instruct" @if ($node->isInstruct()) SELECTED @endif >
                    Content Chunk: Using WYSIWYG Editor</option>
                <option value="instructRaw" @if ($node->isInstructRaw()) SELECTED @endif >
                    Content Chunk: Hard-code HTML, JS, CSS</option>
                <option value="question" @if (!$node->isSpecial()) SELECTED @endif > 
                    Question Prompting User Response</option>
                <option value="bigButt" @if ($node->isBigButt()) SELECTED @endif >
                    Big Button</option>
                <option value="heroImg" @if ($node->isHeroImg()) SELECTED @endif >
                    Hero Image</option>
                <option value="branch" @if ($node->isBranch()) SELECTED @endif >
                    Just A Branch Title</option>
                <option value="cycle" @if ($node->isLoopCycle()) SELECTED @endif >
                    SurvLoop: Root Node of one or more Nodes</option>
                <?php /* <option value="sort" @if ($node->isLoopSort()) SELECTED @endif >
                    Sort SurvLoop Responses</option> */ ?>
                @if ($node->parentID <= 0) 
                    <option value="page" @if ($node->isPage()) SELECTED @endif >
                        Page Wrapper</option>
                @endif
            
            @else 
            
                <option value="question" @if (!$node->isSpecial()) SELECTED @endif 
                    > Question Prompting User Response</option>
                <option value="instruct" @if ($node->isInstruct()) SELECTED @endif 
                    >Instruction (no response): Using WYSIWYG Editor</option>
                <option value="instructRaw" @if ($node->isInstructRaw()) SELECTED @endif 
                    >Instruction (no response): Hard-code HTML, JS, CSS</option>
                <option value="bigButt" @if ($node->isBigButt()) SELECTED @endif >
                    Big Button</option>
                <option value="page" @if ($node->isPage()) SELECTED @endif 
                    >Start of New Page</option>
                <option value="branch" @if ($node->isBranch()) SELECTED @endif 
                    >Just A Branch Title</option>
                <option value="loop" @if ($node->isLoopRoot()) SELECTED @endif 
                    >SurvLoop: Root Node for one or more Pages</option>
                <option value="cycle" @if ($node->isLoopCycle()) SELECTED @endif 
                    >SurvLoop: Root Node of one or more Node within one Page</option>
                <option value="sort" @if ($node->isLoopSort()) SELECTED @endif 
                    >Sort SurvLoop Responses</option>
                    
            @endif
            
            <option value="data" @if ($node->isDataManip()) SELECTED @endif 
                >Data Manipulation</option>
            <option value="survWidget" @if ($node->isWidget()) SELECTED @endif >
                SurvLoop Widget</option>
            <option value="layout" @if ($node->isLayout()) SELECTED @endif >
                Layout</option>
            
            </select>
        </div>
        <div class="slGreenDark pT20 pB20">
            <label>
            <h3 class="m0 slGreenDark"><i class="fa fa-database mR5"></i> Data Family
            @if ($node->nodeID == $GLOBALS['SL']->treeRow->TreeRoot)
                : @if ($GLOBALS['SL']->treeRow->TreeType == 'Page') Page's @else Tree's @endif Core Table
            @endif
            </h3>
            <div class="nFld mT0"><select name="nodeDataBranch" id="nodeDataBranchID" autocomplete="off" 
                class="form-control input-lg slGreenDark">
                {!! $dataBranchDrop !!}
            </select></div>
            All node's families' data storage fields can be related through this table.
            </label>
        </div>
            
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title">
                    <label for="nodeConditionsID"><h4 class="m0">Conditions To Include Node</h4></label>
                </div>
            </div>
            <div class="panel-body">
                
                @if ($node->conds && sizeof($node->conds) > 0)
                    @foreach ($node->conds as $i => $cond)
                        <input type="hidden" id="delCond{{ $i }}ID" name="delCond{{ $cond->CondID }}" value="N">
                        <div id="cond{{ $i }}wrap" class="round10 brd p5 f18 mB10 pL10">
                            <a id="cond{{ $i }}delBtn" href="javascript:void(0)" class="pull-right disBlo condDelBtn"
                                ><i class="fa fa-minus-circle" aria-hidden="true"></i></a> 
                            <div id="cond{{ $i }}delWrap" href="javascript:void(0)" 
                                class="pull-right disNon f10 pT5 pL10">
                                <i class="red">Deleted</i> 
                                <a id="cond{{ $i }}delUndo" href="javascript:void(0)" 
                                    class="condDelBtnUndo f10 mL20">Undo</a> 
                            </div>
                            {{ $cond->CondTag }}
                            <span class="f10 mL10">{!! view('vendor.survloop.admin.db.inc-describeCondition', [
                                "nID"  => $node->nodeID, "cond" => $cond, "i" => $i
                            ])->render() !!}</span>
                        </div>
                    @endforeach
                @endif
                
                {!! view('vendor.survloop.admin.db.inc-addCondition', [])->render() !!}
                
            </div>
        </div>
        
        <div id="hasResponseLayout" class=" @if ($node->isSpecial() || $node->isWidget() || $node->isLayout()) disNon 
            @else disBlo @endif ">
            
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4 class="m0">Node Layout Options</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="nFld mT0">
                        <select type="radio" name="changeResponseMobile" id="changeResponseMobileID" autocomplete="off"
                            onChange="changeResponseMobileType();" class="form-control input-lg" >
                            <option value="mobile" @if ($node->nodeRow->NodeOpts%2 > 0) SELECTED @endif 
                                > Mobile default</option>
                            <option value="desktop" @if ($node->nodeRow->NodeOpts%2 == 0) SELECTED @endif
                                > Customize</option>
                        </select>
                    </div>
                    <div id="changeResListType" class="disNon">
                        <i class="slGrey">&uarr; Please click "Save" below to apply these changes. &rarr;</i>
                    </div>

                    <div id="responseCheckOpts" 
                        class=" @if ($node->nodeRow->NodeOpts%2 == 0) disBlo @else disNon @endif ">
                        <div class="checkbox">
                            <label for="opts11ID">
                                <input type="checkbox" name="opts11" id="opts11ID" value="11" autocomplete="off" 
                                @if ($node->isOneLiner()) CHECKED @endif 
                                onClick="if (document.getElementById('opts11ID').checked) {
                                    document.getElementById('opts17ID').checked = true; }" 
                                > Node Q&A On One Line
                            </label>
                        </div>
                        <div class="checkbox">
                            <label for="opts17ID">
                                <input type="checkbox" name="opts17" id="opts17ID" value="17" autocomplete="off" 
                                    @if ($node->isOneLineResponses()) CHECKED @endif 
                                    onClick="if (!document.getElementById('opts17ID').checked) {
                                    document.getElementById('opts11ID').checked = false; }" 
                                    > Responses On One Line
                            </label>
                        </div>
                        <div class="checkbox">
                            <label for="opts61ID">
                                <input type="checkbox" name="opts61" id="opts61ID" value="61" autocomplete="off" 
                                    @if ($node->nodeRow->NodeOpts%61 == 0) CHECKED @endif 
                                    > Responses In Columns
                            </label>
                        </div>
                    </div>
                </div>
                
                <div id="taggerOpts" class="mB10 
                    @if ($node->nodeRow->NodeType == 'Drop Down') disBlo @else disNon @endif " >
                    <b>Empty/Non-Response Option:</b> 
                    <div class="nFld"><input type="text" name="dropDownSuggest" class="form-control" 
                        @if (isset($node->nodeRow->NodeTextSuggest)) 
                            value="{{ $node->nodeRow->NodeTextSuggest }}"
                        @endif ></div>
                    
                    <label for="opts53ID" class="mL10 mT20">
                        <input type="checkbox" name="opts53" id="opts53ID" value="53" autocomplete="off" 
                            @if ($node->isDropdownTagger()) CHECKED @endif 
                            > Selecting Responses Adds One Or More Tags
                    </label>
                </div>
                <div class="checkbox m10">
                    <label for="opts37ID">
                        <input type="checkbox" name="opts37" id="opts37ID" value="37" autocomplete="off" 
                            @if ($node->nodeRow->NodeOpts%37 == 0) CHECKED @endif class="mR10" > Wrap node in 
                            <a href="http://getbootstrap.com/examples/jumbotron-narrow/" target="_blank">jumbotron</a>
                    </label>
                </div>
                <div id="responseReqOpts" 
                    class="m10 @if ($node->isRequired()) disBlo @else disNon @endif ">
                    <div class="checkbox">
                        <label for="opts13ID">
                            <input type="checkbox" name="opts13" id="opts13ID" value="13" autocomplete="off" 
                            @if ($node->nodeRow->NodeOpts%13 == 0) CHECKED @endif 
                            > <span class="red">*Required</span> displayed on it's own separate line
                        </label>
                    </div>
                </div>
        
            </div> <!-- end Node Layout panel -->
            
        </div> <!-- end hasResponseLayout -->
        
        <label class="w100 pB20">
            <a id="internalNotesBtn" href="javascript:void(0)" class="f12">+ Internal Notes</a> 
            <div id="internalNotes" class=" @if (isset($node->nodeRow->NodeInternalNotes) 
                && trim($node->nodeRow->NodeInternalNotes) != '') disBlo @else disNon @endif ">
                <div class="nFld mT0"><textarea name="nodeInternalNotes" autocomplete="off" 
                    class="form-control slGrey" style="height: 100px;" 
                    >@if (isset($node->nodeRow->NodeInternalNotes)){!! 
                        $node->nodeRow->NodeInternalNotes !!}@endif</textarea></div>
            </div>
        </label>
        
        @if ($canEditTree)
            
            @if (isset($node->nodeRow->NodeID) && $node->nodeRow->NodeID > 0)
                <div class="fR m10">
                    <input type="checkbox" name="deleteNode" id="deleteNodeID" value="1" > 
                    <label for="deleteNodeID">Delete This Node</label>
                </div>
            @endif
            
            </form>
        @else
            <div class="p20 m20 f20"><center><i>
                Sorry, you do not have permissions to actually edit the tree.
            </i></center></div>
            <div class="p20 m20"></div>
        @endif
        
        <div id="emailPreviewStuff" class=" @if ($node->nodeType == 'Send Email') disBlo @else disNon @endif " >
            <h4 class="slBlueDark m0 mB5"><i>Template Preview:</i></h4>
            <div id="previewEmailDump1" class="
                @if (intVal($node->nodeRow->NodeDefault) == -69) disBlo @else disNon @endif ">
                <div class="w100 brdDash m5 p5">
                    Field Name:<br />User Response<br /><br />
                    Field Name:<br />User Response<br /><br />
                    Field Name:<br />User Response<br /><br />
                </div>
            </div>
            @forelse ($emailList as $i => $email)
                <div id="previewEmail{{ $email->EmailID }}" class="
                    @if ($email->EmailID == $node->nodeRow->NodeDefault) disBlo @else disNon @endif ">
                    <div class="w100 brdDash m5 p5">{!! $email->EmailBody !!}</div>
                </div>
            @empty
            @endforelse
            <a href="/dashboard/emails">Manage System Email Templates</a>
        </div>
        
    </div> <!-- end of right column -->
</div>
