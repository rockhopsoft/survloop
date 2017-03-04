<!-- resources/views/vendor/survloop/admin/tree/node-edit.blade.php -->

<div class="p10 fC"></div>

@if ($canEditTree)
    <form name="nodeEditor" method="post" 
        @if (isset($node->nodeRow) && isset($node->nodeRow->NodeID))
            action="/dashboard/tree/map/node/{{ $node->nodeRow->NodeID }}"
        @else
            action="/dashboard/tree/map/node/-3"
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

<div class="panel panel-info">
    <div class="panel-heading">
        <div class="panel-title">
            <a class="btn btn-xs btn-default pull-right"
                @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
                    href="/dashboard/page/{{ $GLOBALS['SL']->treeID }}?refresh=1#n{{ $node->nodeRow->NodeID }}" 
                @else
                    href="/dashboard/tree/map?all=1#n{{ $node->nodeRow->NodeID }}" 
                @endif >Back to Form-Tree Map</a>
            @if (isset($node->nodeRow->NodeID) && $node->nodeRow->NodeID > 0) 
                <h1 class="disIn slBlueDark"><i class="fa fa-cube mR5" aria-hidden="true"></i> 
                    Editing Node #{{ $node->nodeRow->NodeID }}</h1>
            @else 
                <h1 class="disIn slBlueDark"><i class="fa fa-cube mR5" aria-hidden="true"></i> Adding Node</h1>
            @endif
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <h2 class="mT0 slBlueDark">Node Type</h2>
                <select name="nodeType" id="nodeTypeID" class="form-control" autocomplete="off" 
                    onChange="return changeNodeType(this.value);" {{ $nodeTypeSel }} >
                    
                    @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
                    
                        <option value="instruct" @if ($node->isInstruct()) SELECTED @endif >
                            Content Chunk: Using WYSIWYG Editor</option>
                        <option value="instructRaw" @if ($node->isInstructRaw()) SELECTED @endif >
                            Content Chunk: Hard-code HTML, JS, CSS</option>
                        <option value="question" @if (!$node->isSpecial()) SELECTED @endif > 
                            Question Prompting User Response</option>
                        <option value="branch" @if ($node->isBranch()) SELECTED @endif >
                            Just A Branch Title</option>
                        <option value="cycle" @if ($node->isLoopCycle()) SELECTED @endif >
                            SurvLoop: Root Node of one or more Nodes</option>
                        <?php /* <option value="sort" @if ($node->isLoopSort()) SELECTED @endif >
                            Sort SurvLoop Responses</option> */ ?>
                        <option value="data" @if ($node->isDataManip()) SELECTED @endif >
                            Data Manipulation</option>
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
                        <option value="data" @if ($node->isDataManip()) SELECTED @endif 
                            >Data Manipulation</option>
                            
                    @endif
                    
                </select>
            </div>
            <div class="col-md-6 slGreenDark">
                <label>
                <h2 class="mT0 slGreenDark"><i class="fa fa-database mR5"></i> Data Family
                @if ($node->nodeID == $GLOBALS['SL']->treeRow->TreeRoot)
                    : @if ($GLOBALS['SL']->treeRow->TreeType == 'Page') Page's @else Tree's @endif Core Table
                @endif
                </h2>
                <select name="nodeDataBranch" id="nodeDataBranchID" autocomplete="off" class="form-control slGreenDark">
                {!! $dataBranchDrop !!}
                </select>
                All of node's families' data storage fields can be related to this table's data.
                </label>
            </div>
        </div>
    </div>
</div>

<div id="hasInstruct" class=" @if ($node->isInstruct() || $node->isInstructRaw()) disBlo @else disNon @endif ">
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">
                <label for="nodeInstructID"><h3 class="m0 disIn mR20">
                    @if ($GLOBALS['SL']->treeRow->TreeType == 'Page') Content Chunk @else Instructions @endif
                    </h3><small>(text/HTML)</small></label>
            </div>
        </div>
        <div class="panel-body">
            <textarea name="nodeInstruct" 
                @if ($node->isInstruct()) id="summernote" class="form-control nPrompt" 
                @else id="nodeInstructID" class="form-control" @endif 
                style="height: 250px;" autocomplete="off" 
                >@if (isset($node->nodeRow->NodePromptText) ){!! $node->nodeRow->NodePromptText !!}@endif</textarea>
            <label class="w100 pT20">
                <a id="extraHTMLbtn2" href="javascript:void(0)" class="f12 fL"
                    >+ HTML/JS/CSS Extras</a> 
                
                <label for="opts37IDB" class="fR taR">
                    <input type="checkbox" name="opts37B" id="opts37IDB" value="37" class="mTn20" autocomplete="off" 
                        @if ($node->nodeRow->NodeOpts%37 == 0) CHECKED @endif 
                        > Wrap node in 
                        <a href="http://getbootstrap.com/examples/jumbotron-narrow/" target="_blank">jumbotron</a>
                </label>
                    
                <div id="extraHTML2" class="w100 fC @if (isset($node->nodeRow->NodePromptAfter) 
                    && trim($node->nodeRow->NodePromptAfter) != '') disBlo @else disNon @endif ">
                    <textarea name="instrPromptAfter" class="form-control" style="width: 100%; height: 60px;" 
                        autocomplete="off">@if (isset($node->nodeRow->NodePromptAfter)
                            ){!! $node->nodeRow->NodePromptAfter !!}@endif</textarea>
                    <span class="gry9 f12">"[[nID]]" will be replaced with node ID</span>
                </div>
            </label>

        </div>
    </div>
</div>
    
<div id="hasPage" class=" @if ($node->isPage()) disBlo @else disNon @endif ">
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">
                <label for="nodeSlugID"><h3 class="m0">Page URL:</h3></label>
            </div>
        </div>
        <div class="panel-body">
            <div class="fPerc125 gry9">
                @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
                    @if ($GLOBALS['SL']->treeIsAdmin)
                        {{ $GLOBALS['SL']->sysOpts["app-url"] }}/dash/
                    @else
                        {{ $GLOBALS['SL']->sysOpts["app-url"] }}/
                    @endif
                @else
                    @if (isset($GLOBALS['SL']->treeRow->TreeSlug))
                        @if ($GLOBALS['SL']->treeIsAdmin)
                            {{ $GLOBALS['SL']->sysOpts["app-url"] }}/dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/
                        @else
                            {{ $GLOBALS['SL']->sysOpts["app-url"] }}/u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}/
                        @endif
                    @endif
                @endif
            </div>
            <input type="text" name="nodeSlug" id="nodeSlugID" class="form-control mB20" autocomplete="off" 
            @if ($GLOBALS['SL']->treeRow->TreeType == 'Page' && isset($GLOBALS['SL']->treeRow->TreeSlug))
                value="{{ $GLOBALS['SL']->treeRow->TreeSlug }}" >
            @else
                value="@if (isset($node->nodeRow->NodePromptNotes)){!! $node->nodeRow->NodePromptNotes !!}@endif" >
            @endif
                
            <label class="disIn pT5"><input type="text" name="pageFocusField" autocomplete="off" style="width: 40px;" 
                value="{{ $node->nodeRow->NodeCharLimit }}" class="disIn" > Focus Field 
                <i class="fPerc80 gry9">(0 is default, -1 overrides no focus, otherwise set this a Node ID)</i></label>
            @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
                <label class="disBlo pT5"><h3><input type="checkbox" name="homepage" id="homepageID" value="7" 
                    @if ($GLOBALS['SL']->treeRow->TreeOpts%7 == 0) CHECKED @endif autocomplete="off">
                    <i class="fa fa-star mL10" aria-hidden="true"></i> Website Home Page</h3></label>
                <label class="disBlo pT5"><h3><input type="checkbox" name="adminPage" id="adminPageID" value="3" 
                    @if ($GLOBALS['SL']->treeRow->TreeOpts%3 == 0) CHECKED @endif autocomplete="off">
                    <i class="fa fa-key mL10" aria-hidden="true"></i> Admin-Only Page</h3></label>
            @else
                <label class="disBlo red pT5"><input type="checkbox" name="opts29" id="opts29ID" value="29" 
                    @if ($node->nodeRow->NodeOpts%29 == 0) CHECKED @endif autocomplete="off">
                    <i class="fa fa-sign-out mL10" aria-hidden="true"></i> Exit Page <i>(no Next button)</i></label>
            @endif
        </div>
    </div>
</div>

<div id="hasBranch" class=" @if ($node->isBranch()) disBlo @else disNon @endif ">
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">
                Branches are a great way to mark navigation areas, mark key conditions which greatly impact
                user experience, associate data families, and/or just internally organize the the tree. 
            </div>
        </div>
        <div class="panel-body">
            <label for="branchTitleID" class="w100">
                <h3 class="m0 disIn">Branch Title</h3> <small class="mL20">(for internal use only)</small>
                <input type="text" name="branchTitle" id="branchTitleID" class="form-control" autocomplete="off" 
                    value="@if (isset($node->nodeRow->NodePromptText)
                        ){!! strip_tags($node->nodeRow->NodePromptText) !!}@endif" >
            </label>
        </div>
    </div>
</div>

<div id="hasLoop" class=" @if ($node->isLoopRoot()) disBlo @else disNon @endif ">
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">
                <h3 class="m0">Data Set's Loop Options</h3>
            </div>
        </div>
        <div class="panel-body">
            <label class="nPrompt pB20">
                <input type="radio" name="stepLoop" id="stepLoopN" value="0" autocomplete="off" 
                @if (!$node->isStepLoop()) CHECKED @endif 
                > <h3 class="disIn mL5 fPerc125 bld">Standard Loop Behavior</h3>
                <div class="gry9 f14">
                    From this root page, users can add records to the set until
                    they choose to move on or reach the loop's limits.
                </div>
            </label>
            <label class="nPrompt pB20">
                <input type="radio" name="stepLoop" id="stepLoopY" value="1" autocomplete="off" 
                @if ($node->isStepLoop()) CHECKED @endif 
                > <h3 class="disIn mL5 fPerc125 bld">Step-Through Behavior</h3>
                <div class="gry9 f14">
                    All items in this data set are added elsewhere beforehand.
                    Then the user is stepped through them one by one.
                </div>
            </label>
            
            <div class="row mT20">
                <div class="col-md-6">
                    <label class="nPrompt pB20">
                        <h3 class="disIn mT0">Root Page URL</h3> 
                        <span class="gry9 f12 mL20">
                            @if (isset($GLOBALS['SL']->treeRow->TreeSlug))
                                @if ($GLOBALS['SL']->treeIsAdmin)
                                    /dash/{{ $GLOBALS['SL']->treeRow->TreeSlug }}
                                @else
                                    /u/{{ $GLOBALS['SL']->treeRow->TreeSlug }}
                                @endif
                            @endif
                        </span>
                        <input type="text" name="loopSlug" id="loopSlugID" class="form-control" autocomplete="off" 
                            value="@if (isset($node->nodeRow->NodePromptNotes)
                                ){!! $node->nodeRow->NodePromptNotes !!}@endif" >
                    </label>
                </div>
                <div class="col-md-6 slGreenDark">
                    <label class="nPrompt">
                        <h3 class="m0 mB5"><span class="slGreenDark">Loop Name</span></h3>
                        <select name="nodeDataLoop" id="nodeDataLoopID" class="form-control w100 slGreenDark" 
                            autocomplete="off" >
                            <option value="" @if (!isset($node->nodeRow->NodeDataBranch) 
                                || $node->nodeRow->NodeDataBranch == "") SELECTED @endif ></option>
                            @forelse ($GLOBALS['SL']->dataLoops as $setPlural => $setInfo)
                                <option @if (isset($node->nodeRow->NodeDataBranch) 
                                    && $node->nodeRow->NodeDataBranch == $setPlural) SELECTED @endif 
                                    value="{{ $setPlural }}" >{{ $setPlural }}</option>
                            @empty
                            @endforelse
                        </select>
                    </label>
                </div>
            </div>
            
            <label class="nPrompt pB20">
                <h3 class="disIn mT0">Root Page Instructions</h3>
                <small class="mL20 gry9">(text/HTML)</small>
                <textarea name="nodeLoopInstruct" id="nodeLoopInstructID" class="form-control" 
                    style="height: 100px;" autocomplete="off" >@if (isset($node->nodeRow->NodePromptText)
                        ){!! $node->nodeRow->NodePromptText !!}@endif</textarea>
            </label>
            
            <div id="stdLoopOpts" class="w100 pB20 @if (!$node->isStepLoop()) disBlo @else disNon @endif ">
                <label class="nPrompt">
                    <h3>
                    <input type="checkbox" name="stdLoopAuto" id="stdLoopAutoID" value="1" autocomplete="off" 
                    @if (isset($node->nodeRow->NodeDataBranch) && trim($node->nodeRow->NodeDataBranch) != '' 
                        && isset($GLOBALS['SL']->dataLoops[$node->nodeRow->NodeDataBranch]) 
                        && isset($GLOBALS['SL']->dataLoops[$node->nodeRow->NodeDataBranch]->DataLoopAutoGen)
                        && $GLOBALS['SL']->dataLoops[$node->nodeRow->NodeDataBranch]->DataLoopAutoGen == 1) 
                        CHECKED
                    @endif > Auto-Generate new loop items when user clicks "Add" button</h3>
                </label>
            </div>
            <div id="stepLoopOpts" class="w100 pB20 @if ($node->isStepLoop()) disBlo @else disNon @endif ">
                <label class="nPrompt">
                    <h3>Field Marking A Finished Loop Item (Step)</h3>
                    <select name="stepLoopDoneField" id="stepLoopDoneFieldID" class="form-control" autocomplete="off" >
                        @if ($node->isStepLoop())
                            {!! $GLOBALS['SL']->fieldsDropdown(trim($GLOBALS['SL']
                                ->dataLoops[$node->nodeRow->NodeDataBranch]->DataLoopDoneFld)) !!}
                        @else
                            {!! $GLOBALS['SL']->fieldsDropdown() !!}
                        @endif
                    </select>
                </label>
            </div>
                    
        </div>
    </div>
</div>

<div id="hasCycle" class=" @if ($node->isLoopCycle()) disBlo @else disNon @endif ">
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">
                <h3 class="m0">Data Loop Cycle's Options</h3>
            </div>
        </div>
        <div class="panel-body">
            <label class="nPrompt">
                <h3 class="m0 mB5"><span class="slGreenDark">Loop To Cycle Through</span></h3>
                <select name="nodeDataCycle" id="nodeDataCycleID" class="form-control w100 slGreenDark" 
                    autocomplete="off" >
                    <option value="" @if (!isset($node->nodeRow->NodeResponseSet) 
                        || $node->nodeRow->NodeResponseSet == "") SELECTED @endif ></option>
                    @forelse ($GLOBALS['SL']->dataLoops as $setPlural => $setInfo)
                        <option @if (isset($node->nodeRow->NodeResponseSet) 
                            && $node->nodeRow->NodeResponseSet == 'LoopItems::' . $setPlural) SELECTED @endif 
                            value="{{ $setPlural }}" >{{ $setPlural }}</option>
                    @empty
                    @endforelse
                </select>
            </label>
        </div>
    </div>
</div>

<div id="hasSort" class=" @if ($node->isLoopSort()) disBlo @else disNon @endif ">
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">
                <h3 class="m0">Data Loop Sorting Options</h3>
            </div>
        </div>
        <div class="panel-body">
            <div class="row slGreenDark">
                <div class="col-md-6 pR20">
                    <label class="nPrompt">
                        <h3 class="m0 mB5"><span class="slGreenDark">Data Loop:</span></h3>
                        <select name="nodeDataSort" id="nodeDataSortID" class="form-control w100 slGreenDark" 
                            autocomplete="off" >
                            <option value="" @if (!isset($node->nodeRow->NodeResponseSet) 
                                || $node->nodeRow->NodeResponseSet == "") SELECTED @endif ></option>
                            @forelse ($GLOBALS['SL']->dataLoops as $setPlural => $setInfo)
                                <option @if (isset($node->nodeRow->NodeResponseSet) 
                                    && $node->nodeRow->NodeResponseSet == 'LoopItems::' . $setPlural) SELECTED @endif 
                                    value="{{ $setPlural }}" >{{ $setPlural }}</option>
                            @empty
                            @endforelse
                        </select>
                    </label>
                </div>
                <div class="col-md-6">
                    <label class="nPrompt">
                        <h3 class="m0 mB5"><span class="slGreenDark">Loop Sorting Field:</span></h3>
                        <select name="DataStoreSort" id="DataStoreSortID" class="form-control" autocomplete="off" 
                            onClick="return checkData();" >
                            {!! $GLOBALS['SL']->fieldsDropdown((isset($node->nodeRow->NodeDataStore)) 
                                ? trim($node->nodeRow->NodeDataStore) : '') !!}
                        </select>
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
                <h3 class="m0">Data Manipulation Tools</h3>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="radio">
                        <label>
                            <input type="radio" name="dataManipType" id="dataManipTypeNew" value="New" 
                                onClick="return checkDataManipFlds();" @if (isset($node->nodeRow->NodeType) 
                                    && $node->nodeRow->NodeType == 'Data Manip: New') CHECKED @endif >
                                <h3 class="pL10 mTn5 mB10">Create New 
                                <span class="slGreenDark">Data Family</span> Record</h3>
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="dataManipType" id="dataManipTypeUpdate" value="Update" 
                                onClick="return checkDataManipFlds();" @if (isset($node->nodeRow->NodeType) 
                                    && $node->nodeRow->NodeType == 'Data Manip: Update') CHECKED @endif >
                                <h3 class="pL10 mTn5 mB10">Update Family 
                                <span class="slGreenDark">Data Family</span> Record</h3>
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="dataManipType" id="dataManipTypeWrap" value="Wrap" 
                                onClick="return checkDataManipFlds();" @if (isset($node->nodeRow->NodeType) 
                                    && $node->nodeRow->NodeType == 'Data Manip: Wrap') CHECKED @endif >
                                <h3 class="pL10 mTn5 mB10">Just Wrap Children In 
                                <span class="slGreenDark">Data Family</span></h3>
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <small class="gryA">
                        Moving forward with this node conditionally visible, it will run one of these tasks. 
                        Children of this node link to it by setting their data subset to this helper table. 
                        New records are automatically linked to core record and/or loop's set item.
                    </small>
                </div>
            </div>
            <div id="dataNewRecord" class=" @if (isset($node->nodeRow->NodeType) 
                && $node->nodeRow->NodeType == 'Data Manip: Wrap') disNon @else disBlo @endif ">
                <div class="row pT5 pB10">
                    <div class="col-md-1 taR pT5">
                        <h3 class="slGreenDark"><i class="fa fa-database"></i></h3>
                    </div>
                    <div class="col-md-5 f14">
                        <label class="w100">
                            <b>Set Record Field</b>
                            <select name="manipMoreStore" id="manipMoreStoreID" class="form-control" autocomplete="off" 
                                onClick="return checkData();" >
                                {!! $GLOBALS['SL']->fieldsDropdown((isset($node->nodeRow->NodeDataStore)) 
                                    ? trim($node->nodeRow->NodeDataStore) : '') !!}
                            </select>
                        </label>
                    </div>
                    <div class="col-md-1 taR">
                        <h1 class="mT10 pT5 slGreenDark">=</h1>
                    </div>
                    <div class="col-md-2">
                        <b>To Value</b><br />
                        <input type="text" name="manipMoreVal" class="form-control"
                            @if (isset($node->nodeRow->NodeDefault)) value="{{ $node->nodeRow->NodeDefault }}" @endif >
                    </div>
                    <div class="col-md-3 relDiv">
                        <div class="absDiv" style="top: 25px; left: -7px;"><b>or</b></div>
                        <br />
                        <select name="manipMoreSet" class="form-control" autocomplete="off" >
                            {!! $GLOBALS['SL']->allDefsDropdown((isset($node->nodeRow->NodeResponseSet)) 
                                ? $node->nodeRow->NodeResponseSet : '') !!}
                        </select>
                    </div>
                </div>
                
                @for ($i = 0; $i < $resLimit; $i++)
                    <div id="dataManipFld{{ $i }}" 
                        class=" @if (isset($node->dataManips[$i])) disBlo @else disNon @endif ">
                        <div class="row pT5 pB10">
                            <div class="col-md-1 taR pT5">
                                <h3 class="m0 slGreenDark"><i class="fa fa-database"></i></h3>
                            </div>
                            <div class="col-md-5 f14">
                                <select name="manipMore{{ $i }}Store" id="manipMore{{ $i }}StoreID" class="form-control"
                                    autocomplete="off" onClick="return checkData();" >
                                    @if (isset($node->dataManips[$i]) && isset($node->dataManips[$i]->NodeDataStore))
                                        {!! $GLOBALS['SL']->fieldsDropdown($node->dataManips[$i]->NodeDataStore) !!}
                                    @else
                                        {!! $GLOBALS['SL']->fieldsDropdown() !!}
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-1 taR">
                                <h1 class="mTn5 slGreenDark">=</h1>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="manipMore{{ $i }}Val" class="form-control" 
                                    @if (isset($node->dataManips[$i]) && isset($node->dataManips[$i]->NodeDefault))
                                        value="{!! $node->dataManips[$i]->NodeDefault !!}"
                                    @else
                                        value=""
                                    @endif
                                >
                            </div>
                            <div class="col-md-3 relDiv">
                                <div class="absDiv" style="top: 5px; left: -7px;"><b>or</b></div>
                                <select name="manipMore{{ $i }}Set" class="form-control" autocomplete="off" >
                                    @if (isset($node->dataManips[$i]) && isset($node->dataManips[$i]->NodeResponseSet))
                                        {!! $GLOBALS['SL']->allDefsDropdown($node->dataManips[$i]->NodeResponseSet) !!}
                                    @else
                                        {!! $GLOBALS['SL']->allDefsDropdown() !!}
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>

<div id="hasResponse" class=" @if ($node->isSpecial()) disNon @else disBlo @endif ">
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">
                <label for="nodePromptTextID"><h3 class="m0 disIn mR20">Question or Prompt for User</h3> 
                    <small>(text/HTML)</small></label>
            </div>
        </div>
        <div class="panel-body">
            <textarea name="nodePromptText" id="nodePromptTextID" class="form-control" style="height: 200px;" 
                autocomplete="off" >@if (isset($node->nodeRow->NodePromptText)
                    ){!! $node->nodeRow->NodePromptText !!}@endif</textarea>
                
            <div class="row mT20">
                <div class="col-md-6">
                    <label class="w100">
                        <a id="extraSmallBtn" href="javascript:void(0)" class="f12"
                            >+ Small Instructions or Side-Notes</a> 
                        <div id="extraSmall" class="w100 @if (isset($node->nodeRow->NodePromptNotes) 
                            && trim($node->nodeRow->NodePromptNotes) != '') disBlo @else disNon @endif ">
                            <textarea name="nodePromptNotes" class="form-control" style="width: 100%; height: 60px;" 
                                autocomplete="off" >@if (isset($node->nodeRow->NodePromptNotes)
                                    ){!! $node->nodeRow->NodePromptNotes !!}@endif</textarea>
                        </div>
                    </label>
                </div>
                <div class="col-md-6">
                    <label class="w100">
                        <a id="extraHTMLbtn" href="javascript:void(0)" class="f12"
                            >+ HTML/JS/CSS Extras After Node Field</a> 
                        <div id="extraHTML" class="w100 @if (isset($node->nodeRow->NodePromptAfter) 
                            && trim($node->nodeRow->NodePromptAfter) != '') disBlo @else disNon @endif ">
                            <textarea name="nodePromptAfter" class="form-control" style="width: 100%; height: 60px;" 
                                autocomplete="off">@if (isset($node->nodeRow->NodePromptAfter)
                                    ){!! $node->nodeRow->NodePromptAfter !!}@endif</textarea>
                            <span class="gry9 f12">"[[nID]]" will be replaced with node ID</span>
                        </div>
                    </label>
                </div>
            </div>
            
        </div>
    </div>

    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">
                <h3 class="m0">User Response Settings</h3>
            </div>
        </div>
        <div class="panel-body">
            <div class="row pB20">
                <div class="col-md-4">
                    <label class="w100">
                        <h3 class="slBlueDark">User Response Type:</h3>
                        <select name="nodeTypeQ" id="nodeTypeQID" 
                            class="form-control form-control-lg f22 slBlueDark w100" 
                            onChange="return changeResponseType(this.value);" autocomplete="off" >
                        @foreach ($nodeTypes as $type)
                            <option value="{{ $type }}" @if (isset($node->nodeRow->NodeType) 
                                && $node->nodeRow->NodeType == $type) SELECTED @endif >{{ $type }}</option>
                        @endforeach
                        </select>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="w100 f18">
                        <h3>Store User Response: </h3>
                        <select name="nodeDataStore" class="form-control form-control-lg f22 w100" autocomplete="off" >
                            {!! $GLOBALS['SL']->fieldsDropdown(isset($node->nodeRow->NodeDataStore) 
                                ? trim($node->nodeRow->NodeDataStore) : '') !!}
                        </select>
                    </label>
                </div>
                <div class="col-md-1">
                </div>
                <div class="col-md-3 pT20">
                    <label for="opts5ID" class="red fPerc125">
                        <input type="checkbox" name="opts5" id="opts5ID" value="5" autocomplete="off" 
                            @if ($node->isRequired()) CHECKED @endif 
                            onClick="return changeRequiredType();"> User Response Required
                    </label>
                    <div id="resNotWrdCnt" class="gry9 mT10 mB10 @if (isset($node->nodeRow->NodeType) && 
                        in_array($node->nodeRow->NodeType, ['Long Text'])) disBlo 
                        @else disNon @endif ">
                        <label for="opts31ID">
                            <input type="checkbox" name="opts31" id="opts31ID" value="31" autocomplete="off" 
                                @if ($node->nodeRow->NodeOpts%31 == 0) CHECKED @endif 
                                > Show Word Count
                        </label>
                    </div>
                    <div id="resNotMulti" class="gry9 @if (isset($node->nodeRow->NodeType) && 
                        in_array($node->nodeRow->NodeType, ['Text', 'Long Text', 'Uploads'])) disBlo 
                        @else disNon @endif ">
                        <label>
                            Character/Upload Limit: 
                            <input type="number" name="nodeCharLimit" id="nodeCharLimitID" class="form-control disIn" 
                                style="width: 100px;" autocomplete="off" 
                                @if (isset($node->nodeRow->NodeCharLimit)) value="{{ $node->nodeRow->NodeCharLimit }}" 
                                @else value="" @endif >
                        </label>
                    </div>
                    <div id="resCanAuto" class="gry9 @if (isset($node->nodeRow->NodeType) && 
                        in_array($node->nodeRow->NodeType, ['Text'])) disBlo 
                        @else disNon @endif ">
                        <label class="mT10 mB10">
                            <label for="opts41ID">
                                <input type="checkbox" name="opts41" id="opts41ID" value="41" autocomplete="off" 
                                    @if ($node->nodeRow->NodeOpts%41 == 0) CHECKED @endif 
                                    > Echo Response To Div
                            </label>
                        </label>
                        <label>
                            Autofill Suggestions: 
                            <select name="nodeTextSuggest" id="nodeTextSuggestID" class="form-control disIn" 
                                style="width: 200px;" autocomplete="off" >
                                <option value="" @if (!isset($node->nodeRow->NodeTextSuggest) 
                                    || $node->nodeRow->NodeTextSuggest == '') SELECTED @endif ></option>
                                @forelse ($defs as $def)
                                    <option value="{{ $def->DefSubset }}" @if (isset($node->nodeRow->NodeTextSuggest) 
                                        && $node->nodeRow->NodeTextSuggest == $def->DefSubset) SELECTED @endif 
                                        >{{ $def->DefSubset }}</option>
                                @empty
                                @endforelse
                            </select>
                        </label>
                    </div>
                </div>
            </div>
            
            <div id="BigBtnOpts" class=" @if (isset($node->nodeRow->NodeType) 
                && $node->nodeRow->NodeType == 'Big Button')) disBlo @else disNon @endif ">
                <h4>Button Text</h4>
                <input type="text" name="bigBtnText" class="form-control"
                    @if (isset($node->nodeRow->NodeDefault)) value="{{ $node->nodeRow->NodeDefault }}" @endif >
                <br /><br />
                <h4>Button On Click Javascript</h4>
                <input type="text" name="bigBtnJS" class="form-control"
                    @if (isset($node->nodeRow->NodeDataStore)) value="{{ $node->nodeRow->NodeDataStore }}" @endif >
                <label class="mT10"><input type="checkbox" name="opts43" value="43" 
                    @if ($node->nodeRow->NodeOpts%43 == 0) CHECKED @endif > Toggle Child Nodes
                </label>
                <label class="mT10"><input type="checkbox" name="opts47" value="47" 
                    @if ($node->nodeRow->NodeOpts%47 == 0) CHECKED @endif > Grey button, not blue
                </label>
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
                <h3>Response Options Provided To User:</h3>
                <div class="row">
                    <div class="col-md-4">
                        <h4><label class="disBlo mB10"><input type="radio" name="responseListType" value="manual" 
                            @if ($currDefinition == '' && $currLoopItems == '') CHECKED @endif
                            onClick="changeResponseListType();"
                            id="resListTypeManual"> Manually type options below</label>
                        <label class="disBlo"><input type="radio" name="responseListType" value="auto" 
                            @if ($currDefinition != '' || $currLoopItems != '') CHECKED @endif
                            onClick="changeResponseListType();"
                            id="resListTypeAuto"> Automatically pull from a system list</label></h4>
                        <label>
                        <div id="changeResListType" class="disNon">
                            <i class="gry9">&uarr; Please click "Save" below to apply these changes. &rarr;</i>
                        </div>
                    </div>
                    <div class="col-md-8">
                        
                        <div id="responseOptLists" class="row
                            @if ($currDefinition != '' || $currLoopItems != '') disBlo @else disNon @endif">
                            <div class="col-md-6">
                                <h4 class="m0">Definition Set:</h4>
                                <select name="responseDefinition" id="responseDefinitionID" class="form-control" 
                                    onChange="changeResponseListType();" autocomplete="off">
                                    <option value="" @if ($currDefinition == '') SELECTED @endif ></option>
                                    @forelse ($defs as $def)
                                        <option value="{{ $def->DefSubset }}" 
                                            @if ($currDefinition == $def->DefSubset) SELECTED @endif 
                                            >{{ $def->DefSubset }}</option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-6">
                                <h4 class="m0">Entered Loop Items:</h4>
                                <select name="responseLoopItems" id="responseLoopItemsID" class="form-control" 
                                    onChange="changeResponseListType();" autocomplete="off">
                                    <option value="" @if ($currLoopItems == '') SELECTED @endif ></option>
                                    @forelse ($GLOBALS['SL']->dataLoops as $plural => $loop)
                                        <option value="{{ $plural }}"
                                            @if ($currLoopItems == $plural) SELECTED @endif 
                                            >{{ $plural }}</option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="brdLgt round10 gry9 row mB20 mL10 mR10">
                    <div class="col-md-5 taC p10">
                        <i title="Children displayed only with certain responses"
                            class="fa fa-code-fork fa-flip-vertical mR5"></i>
                        If selected, reveals child nodes
                    </div>
                    <div class="col-md-7 taC p10">
                        <i class="fa fa-circle-o mR0"></i><i class="fa fa-circle mL0 mR5"></i>
                        If selected, de-selects other responses (mutually exclusive)
                    </div>
                </div>
                
                <div class="row pB10">
                    <div class="col-md-6"><h3 class="m0">What User Will See <span class="f12">[HTML]</span></h3></div>
                    <div class="col-md-4"><h3 class="m0">Value Stored In Database</h3></div>
                    <div class="col-md-2"></div>
                </div>
                
                @forelse ($node->responses as $r => $res)
                    <div id="r{{ $r }}" class="row pB20">
                        <div class="col-md-6">
                            <textarea type="text" name="response{{ $r }}" id="response{{ $r }}ID" 
                                class="form-control mBn10" style="height: 65px;" autocomplete="off" 
                                onKeyUp="return checkRes();" @if ($currDefinition != '') DISABLED @endif 
                                >{{ $res->NodeResEng }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="response{{ $r }}Val" id="response{{ $r }}vID" 
                                value="{{ $res->NodeResValue }}" onKeyUp="return checkRes();" class="form-control" 
                                autocomplete="off" @if ($currDefinition != '') DISABLED @endif >
                        </div>
                        <div class="col-md-1 taC">
                            <label><input type="checkbox" name="response{{ $r }}ShowKids" value="1" 
                                @if ($node->indexShowsKid($r)) CHECKED @endif >
                                 <i title="Children displayed only with certain responses"
                                class="fa fa-code-fork fa-flip-vertical mL10 fPerc125"></i></label>
                        </div>
                        <div class="col-md-1 taC checkboxOnlyField">
                            <label id="resMutEx{{ $r }}" class=" @if (isset($node->nodeRow->NodeType) 
                                && $node->nodeRow->NodeType == 'Checkbox')) disBlo @else disNon @endif "><nobr>
                                <input type="checkbox" name="response{{ $r }}MutEx" value="1" autocomplete="off" 
                                    @if ($node->indexMutEx($r)) CHECKED @endif >
                                    <i class="fa fa-circle-o mL10 mR0 fPerc125"></i> 
                                    <i class="fa fa-circle mLn5 fPerc125"></i></nobr>
                            </label>
                        </div>
                    </div>
                @empty
                @endforelse
                @if ($currDefinition == '')
                    @for ($r = sizeof($node->responses); $r < $resLimit; $r++)
                        <div id="r{{ $r }}" class="row pB10 
                            @if ($r == sizeof($node->responses)) disBlo @else disNon @endif ">
                            <div class="col-md-6">
                                <textarea type="text" name="response{{ $r }}" id="response{{ $r }}ID" 
                                    class="form-control mBn10" style="height: 35px;" onKeyUp="return checkRes();" 
                                    autocomplete="off" @if ($currDefinition != '') DISABLED @endif ></textarea>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="response{{ $r }}Val" id="response{{ $r }}vID" value="" 
                                    onKeyUp="return checkRes();" class="form-control" autocomplete="off" 
                                    @if ($currDefinition != '') DISABLED @endif >
                            </div>
                            <div class="col-md-1 taC">
                                <label><input type="checkbox" name="response{{ $r }}ShowKids" value="1" 
                                    autocomplete="off" >
                                     <i title="Children displayed only with certain responses"
                                    class="fa fa-code-fork fa-flip-vertical mL10 fPerc125"></i></label>
                            </div>
                            <div class="col-md-1 taC">
                                <label id="resMutEx{{ $r }}" class=" @if (isset($node->nodeRow->NodeType) 
                                    && $node->nodeRow->NodeType == 'Checkbox')) disBlo @else disNon @endif "><nobr>
                                    <input type="checkbox" name="response{{ $r }}MutEx" value="1" autocomplete="off" >
                                        <i class="fa fa-circle-o mL10 mR0 fPerc125"></i> 
                                        <i class="fa fa-circle mLn5 fPerc125"></i></nobr>
                                </label>
                            </div>
                        </div>
                    @endfor
                @endif
            </div> <!-- end resOpts -->
            
        </div>
    </div> <!-- end Response Options panel -->
    
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">
                <h3 class="m0">Node Layout Options</h3>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <h4><label class="disBlo mB10"><input type="radio" name="changeResponseMobile" value="mobile" 
                        @if ($node->nodeRow->NodeOpts%2 > 0) CHECKED @endif
                        onClick="changeResponseMobileType();" autocomplete="off" 
                        id="responseCheckTypeFat"> Mobile default</label>
                    <label class="disBlo"><input type="radio" name="changeResponseMobile" value="desktop" 
                        @if ($node->nodeRow->NodeOpts%2 == 0) CHECKED @endif
                        onClick="changeResponseMobileType();" autocomplete="off" 
                        id="responseCheckTypeDesk"> Desktop options</label></h4>
                    <label>
                    <div id="changeResListType" class="disNon">
                        <i class="gry9">&uarr; Please click "Save" below to apply these changes. &rarr;</i>
                    </div>
                </div>
                <div class="col-md-9">
                    <div id="responseCheckOpts" 
                        class="row @if ($node->nodeRow->NodeOpts%2 == 0) disBlo @else disNon @endif ">
                        <div class="col-md-6">
                            <div class="checkbox">
                                <label for="opts11ID">
                                    <input type="checkbox" name="opts11" id="opts11ID" value="11" autocomplete="off" 
                                    @if ($node->isOneLiner()) CHECKED @endif 
                                    onClick="if (document.getElementById('opts11ID').checked) {
                                        document.getElementById('opts17ID').checked = true; }" 
                                    > Node Q&A On One Line
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div id="responseOneLine" class="checkbox @if (isset($node->nodeRow->NodeType) && 
                                in_array($node->nodeRow->NodeType, ['Radio', 'Checkbox'])) disBlo @else disNon @endif ">
                                <label for="opts17ID">
                                    <input type="checkbox" name="opts17" id="opts17ID" value="17" autocomplete="off" 
                                        @if ($node->isOneLineResponses()) CHECKED @endif 
                                        onClick="if (!document.getElementById('opts17ID').checked) {
                                        document.getElementById('opts11ID').checked = false; }" 
                                        > Responses On One Line
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <label for="opts37ID" class="mL20">
                    <input type="checkbox" name="opts37" id="opts37ID" value="37" class="mTn20" autocomplete="off" 
                        @if ($node->nodeRow->NodeOpts%37 == 0) CHECKED @endif 
                        > Wrap node in 
                        <a href="http://getbootstrap.com/examples/jumbotron-narrow/" target="_blank">jumbotron</a>
                </label>
            </div>
            <div class="col-md-6">
                <div id="responseReqOpts" 
                    class="pL10 mTn20 @if ($node->isRequired()) disBlo @else disNon @endif ">
                    <div class="checkbox">
                        <label for="opts13ID">
                            <input type="checkbox" name="opts13" id="opts13ID" value="13" autocomplete="off" 
                            @if ($node->nodeRow->NodeOpts%13 == 0) CHECKED @endif 
                            > <span class="red">*Required</span> displayed on it's own separate line
                        </label>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- end Node Layout panel -->

</div> <!-- end hasResponse -->

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title">
                    <label for="nodeConditionsID"><h3 class="m0">Conditions To Include Node</h3></label>
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
                                "nID"  => $node->nodeID, 
                                "cond" => $cond,
                                "i" => $i
                            ])->render() !!}</span>
                        </div>
                    @endforeach
                @endif
                
                {!! view('vendor.survloop.admin.db.inc-addCondition', [])->render() !!}
                
            </div>
        </div>
    </div>
    <div class="col-md-6 taC pT20">
        <input type="submit" value="Save Node Changes" class="btn btn-lg btn-primary f32" 
            @if (!$canEditTree) DISABLED @endif >
    </div>
</div>

<label class="w100 pB20">
    <a id="internalNotesBtn" href="javascript:void(0)" class="f12">Internal Notes</a> 
    <div id="internalNotes" class=" @if (isset($node->nodeRow->NodeInternalNotes) 
        && trim($node->nodeRow->NodeInternalNotes) != '') disBlo @else disNon @endif ">
        <textarea name="nodeInternalNotes" class="form-control" style="height: 40px;" autocomplete="off" 
            >@if (isset($node->nodeRow->NodeInternalNotes)){!! $node->nodeRow->NodeInternalNotes !!}@endif</textarea>
    </div>
</label>

@if ($canEditTree)
    
    @if (isset($node->nodeRow->NodeID) && $node->nodeRow->NodeID > 0)
        <br /><input type="checkbox" name="deleteNode" id="deleteNodeID" value="1" > 
        <label for="deleteNodeID">Delete This Node</label><br />
    @endif
    
    </form>
@else
    <div class="p20 m20 f20"><center><i>
        Sorry, you do not have permissions to actually edit the tree.
    </i></center></div>
    <div class="p20 m20"></div>
@endif

<script type="text/javascript">
$(document).ready(function(){
    $("#specialFuncsBtn").click(function(){ $("#specialFuncs").slideToggle("fast"); });
    $("#extraSmallBtn").click(function() { $("#extraSmall").slideToggle("fast"); });
    $("#extraHTMLbtn").click(function() { $("#extraHTML").slideToggle("fast"); });
    $("#extraHTMLbtn2").click(function() { $("#extraHTML2").slideToggle("fast"); });
    $("#internalNotesBtn").click(function() { $("#internalNotes").slideToggle("fast"); });
    $("#stepLoopN").click(function() { $("#stdLoopOpts").slideDown("fast"); $("#stepLoopOpts").slideUp("fast"); });
    $("#stepLoopY").click(function() { $("#stdLoopOpts").slideUp("fast"); $("#stepLoopOpts").slideDown("fast"); });
    
    $(document).on("click", "a.condDelBtn", function() {
        var cond = $(this).attr("id").replace("cond", "").replace("delBtn", "");
        document.getElementById("cond"+cond+"wrap").style.background='#DDDDDD';
        document.getElementById("cond"+cond+"delBtn").style.display="none";
        document.getElementById("cond"+cond+"delWrap").style.display="block";
        document.getElementById("delCond"+cond+"ID").value="Y";
    });
    $(document).on("click", "a.condDelBtnUndo", function() {
        var cond = $(this).attr("id").replace("cond", "").replace("delUndo", "");
        document.getElementById("cond"+cond+"wrap").style.background='#FFFFFF';
        document.getElementById("cond"+cond+"delBtn").style.display="block";
        document.getElementById("cond"+cond+"delWrap").style.display="none";
        document.getElementById("delCond"+cond+"ID").value="N";
    });
});

function changeNodeType(newType) {
    document.getElementById('hasPage').style.display='none';
    document.getElementById('hasLoop').style.display='none';
    document.getElementById('hasCycle').style.display='none';
    document.getElementById('hasBranch').style.display='none';
    document.getElementById('hasInstruct').style.display='none';
    document.getElementById('hasDataManip').style.display='none';
    if (newType == 'branch' || newType == 'data' || newType == 'loop' || newType == 'sort' || newType == 'cycle' 
        || newType == 'page' || newType == 'instruct' || newType == 'instructRaw') {
        document.getElementById('hasResponse').style.display='none';
        if  (newType == 'instruct' || newType == 'instructRaw') {
            document.getElementById('hasInstruct').style.display='block';
        }
        else if (newType == 'data') document.getElementById('hasDataManip').style.display='block';
        else if (newType == 'branch') document.getElementById('hasBranch').style.display='block';
        else if (newType == 'loop') document.getElementById('hasLoop').style.display='block';
        else if (newType == 'cycle') document.getElementById('hasCycle').style.display='block';
        else if (newType == 'sort') document.getElementById('hasSort').style.display='block';
        else if (newType == 'page') document.getElementById('hasPage').style.display='block';
    }
    else document.getElementById('hasResponse').style.display='block';
    return true;
}

function changeResponseType(newType) {
    if (newType == 'Radio' || newType == 'Checkbox' || newType == 'Drop Down' || newType == 'Other/Custom') {
        document.getElementById('resOpts').style.display='block';
        document.getElementById('resNotMulti').style.display='none';
    } else {
        document.getElementById('resOpts').style.display='none';
        if (newType == 'Text' || newType == 'Long Text') {
            document.getElementById('resNotMulti').style.display='block';
        }
    }
    if (newType == 'Text') {
        document.getElementById('resCanAuto').style.display='block';
    } else {
        document.getElementById('resCanAuto').style.display='none';
    }
    if (newType == 'Long Text') {
        document.getElementById('resNotWrdCnt').style.display='block';
    } else {
        document.getElementById('resNotWrdCnt').style.display='none';
    }
    if (newType == 'Big Button') {
        document.getElementById('BigBtnOpts').style.display='block';
    } else {
        document.getElementById('BigBtnOpts').style.display='none';
    }
    if (newType == 'Date' || newType == 'Date Picker' || newType == 'Date Time') {
        document.getElementById('DateOpts').style.display='block';
    } else {
        document.getElementById('DateOpts').style.display='none';
    }
    if (newType == 'Radio' || newType == 'Checkbox') {
        document.getElementById('responseOneLine').style.display='block';
    } else {
        document.getElementById('responseOneLine').style.display='none';
    }
    if (newType == 'Checkbox' || newType == 'Other/Custom') {
        for (var i=0; i < {{ $resLimit }}; i++) {
            if (document.getElementById('resMutEx'+i+'')) {
                document.getElementById('resMutEx'+i+'').style.display='block';
            }
        }
    } else {
        for (var i=0; i < {{ $resLimit }}; i++) {
            if (document.getElementById('resMutEx'+i+'')) {
                document.getElementById('resMutEx'+i+'').style.display='none';
            }
        }
    }
    return true;
}

function changeResponseListType() {
    if (!document.getElementById('responseOptLists')) return false;
    document.getElementById('changeResListType').style.display = 'block';
    if (document.getElementById('resListTypeManual') && document.getElementById('resListTypeManual').checked) {
        document.getElementById('responseOptLists').style.display = 'none';
        document.getElementById('responseDefinitionID').value='';
        document.getElementById('responseLoopItemsID').value='';
        return true;
    }
    document.getElementById('responseOptLists').style.display = 'block';
    return true;
}

function changeRequiredType() {
    if (!document.getElementById('responseReqOpts')) return false;
    if (document.getElementById('opts5ID') && document.getElementById('opts5ID').checked) {
        document.getElementById('responseReqOpts').style.display = 'block';
        return true;
    }
    document.getElementById('responseReqOpts').style.display = 'none';
    return true;
}

function changeResponseMobileType() {
    if (!document.getElementById('responseCheckOpts')) return false;
    if (document.getElementById('responseCheckTypeFat') && document.getElementById('responseCheckTypeFat').checked) {
        document.getElementById('responseCheckOpts').style.display = 'none';
        return true;
    }
    document.getElementById('responseCheckOpts').style.display = 'block';
    return true;
}

var maxRes = 0; var i = 0;
function checkRes() {
    maxRes = 0;
    for (i=0; i < {{ $resLimit }}; i++) {
        if (document.getElementById('response'+i+'ID').value != '' 
            || document.getElementById('response'+i+'vID').value != '') maxRes = i;
    }
    for (i=0; i <= (maxRes+1); i++) {
        if (document.getElementById('r'+i+'')) document.getElementById('r'+i+'').style.display = 'block';
    }
    for (i=(maxRes+2); i < {{ $resLimit }}; i++) {
        if (document.getElementById('r'+i+'')) document.getElementById('r'+i+'').style.display = 'none';
    }
    return true;
}

function checkDataManipFlds() {
    if (document.getElementById('dataManipTypeWrap').checked) {
        document.getElementById('dataNewRecord').style.display = 'none';
    }
    else {
        document.getElementById('dataNewRecord').style.display = 'block';
    }
    return true;
}

function checkData() {
    maxRes = 0;
    for (i=1; i < {{ $resLimit }}; i++) {
        if (document.getElementById('manipMore'+i+'StoreID').value != '') maxRes = i;
    }
    for (i=1; i <= (maxRes+1); i++) {
        if (document.getElementById('dataManipFld'+i+'')) document.getElementById('dataManipFld'+i+'').style.display = 'block';
    }
    for (i=(maxRes+2); i < {{ $resLimit }}; i++) {
        if (document.getElementById('dataManipFld'+i+'')) document.getElementById('dataManipFld'+i+'').style.display = 'none';
    }
    return true;
}
setTimeout("checkData()", 100);
</script>
