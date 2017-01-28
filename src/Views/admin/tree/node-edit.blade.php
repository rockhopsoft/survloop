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
        @if ($REQ->has('parent') && intVal($REQ->input('parent')) > 0) 
            value="{{ $REQ->input('parent') }}"
        @else 
            value="{{ $node->parentID }}"
        @endif
        >
    <input type="hidden" name="childPlace" 
        @if ($REQ->has('start') && intVal($REQ->input('start')) > 0) 
            value="start"
        @else 
            @if ($REQ->has('end') && intVal($REQ->input('end')) > 0)
                value="end"
            @else
                value=""
            @endif
        @endif
        >
    <input type="hidden" name="orderBefore" 
        @if ($REQ->has('ordBefore') && intVal($REQ->ordBefore) > 0) 
            value="{{ $REQ->ordBefore }}"
        @else 
            value="-3"
        @endif
        >
    <input type="hidden" name="orderAfter" 
        @if ($REQ->has('ordAfter') && intVal($REQ->ordAfter) > 0) 
            value="{{ $REQ->ordAfter }}"
        @else 
            value="-3"
        @endif
        >
@endif

<div class="panel panel-info">
    <div class="panel-heading">
        <div class="panel-title">
            @if (isset($node->nodeRow->NodeID) && $node->nodeRow->NodeID > 0) 
                <a href="/dashboard/tree/map?all=1#n{{ $node->nodeRow->NodeID }}" class="btn btn-xs btn-default pull-right">Back to Form-Tree Map</a>
                <h2 class="disIn"><span class="slBlueDark fPerc125 mR20">#{{ $node->nodeRow->NodeID }}</span> Editing Node</h2>
            @else 
                <a href="/dashboard/tree/map?all=1" class="btn btn-xs btn-default pull-right">Back to Form-Tree Map</a>
                <h2 class="disIn">Adding Node</h2>
            @endif
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-1 taC pT5">
                <h3 class="m0 slBlueDark">Node Type</h3>
            </div>
            <div class="col-md-3">
                <div class="radio">
                    <label for="typeQuestion">
                        <input type="radio" name="nodeType" id="typeQuestion" value="question" autocomplete="off" 
                        @if (!$node->isSpecial()) CHECKED @endif 
                        onClick="return changeNodeType(this.value);"> Question Prompting User Response
                    </label>
                </div>
                <div class="radio">
                    <label for="typeInstruct">
                        <input type="radio" name="nodeType" id="typeInstruct" value="instruct" autocomplete="off" 
                        @if ($node->isInstruct()) CHECKED @endif 
                        onClick="return changeNodeType(this.value);"> Instruction Without Response
                    </label>
                </div>
                <div class="radio">
                    <label for="typePage">
                        <input type="radio" name="nodeType" id="typePage" value="page" autocomplete="off"
                        @if ($node->isPage()) CHECKED @endif 
                        onClick="return changeNodeType(this.value);"> Start of New Page<br />
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="radio">
                    <label for="typeLoop">
                        <input type="radio" name="nodeType" id="typeLoop" value="loop" autocomplete="off" 
                        @if ($node->isLoopRoot()) CHECKED @endif 
                        onClick="return changeNodeType(this.value);"> Root Node of a Data Loop
                    </label>
                </div>
                <div class="radio">
                    <label for="typeData">
                        <input type="radio" name="nodeType" id="typeData" value="data" autocomplete="off" 
                        @if ($node->isDataManip()) CHECKED @endif 
                        onClick="return changeNodeType(this.value);"> Data Manipulation
                    </label>
                </div>
                <div class="radio">
                    <label for="typeBranch">
                        <input type="radio" name="nodeType" id="typeBranch" value="branch" autocomplete="off" 
                        @if ($node->isBranch()) CHECKED @endif 
                        onClick="return changeNodeType(this.value);"> Just A Branch Title<br />
                    </label>
                </div>
            </div>
            
            <div class="col-md-5">
                <label>
                    <b class="fPerc125 slGreenDark"><i class="fa fa-database mR5"></i> Data Family</b>
                    <div><small class="slGreenDark opac50">All of node's families' data storage fields can be related to this table's data:</small></div>
                    <select name="nodeDataBranch" id="nodeDataBranchID" autocomplete="off" class="form-control slGreenDark" >
                    {!! $dataBranchDrop !!}
                    </select>
                </label>
            </div>
        </div>
    </div>
</div>

<div id="hasInstruct" class=" @if ($node->isInstruct()) disBlo @else disNon @endif ">
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">
                <label for="nodeInstructID"><h3 class="m0 disIn mR20">Instructions</h3> <small>(text/HTML)</small></label>
            </div>
        </div>
        <div class="panel-body">
            <textarea name="nodeInstruct" id="nodeInstructID" class="form-control" style="height: 100px;" autocomplete="off" 
                >@if (isset($node->nodeRow->NodePromptText)){!! $node->nodeRow->NodePromptText !!}@endif</textarea>
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
                @if (isset($GLOBALS["DB"]->treeRow->TreeRootURL))
                    {{ $GLOBALS["DB"]->treeRow->TreeRootURL }}/u/
                @endif
            </div>
            <input type="text" name="nodeSlug" id="nodeSlugID" class="form-control" autocomplete="off" 
                value="@if (isset($node->nodeRow->NodePromptNotes)){!! $node->nodeRow->NodePromptNotes !!}@endif" >
        </div>
    </div>
</div>

<div id="hasBranch" class=" @if ($node->isBranch()) disBlo @else disNon @endif ">
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">
                Branches are a great way to mark navigation areas, mark key conditions which greatly impact user experience, associate data families, and/or just internally organize the the tree. 
            </div>
        </div>
        <div class="panel-body">
            <label for="branchTitleID" class="w100">
                <h3 class="m0 disIn">Branch Title</h3> <small class="mL20">(for internal use only)</small>
                <input type="text" name="branchTitle" id="branchTitleID" class="form-control" autocomplete="off" 
                    value="@if (isset($node->nodeRow->NodePromptText)){!! strip_tags($node->nodeRow->NodePromptText) !!}@endif" >
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
            <div class="row f16">
                <div class="col-md-4">
                    <label>
                        <input type="radio" name="stepLoop" id="stepLoopN" value="0" autocomplete="off" 
                        @if (!$node->isStepLoop()) CHECKED @endif 
                        > <span class="mL5 bld">Standard Loop Behavior</span><br />
                    </label>
                    <div id="stdLoopOpts" class="pL20 @if (!$node->isStepLoop()) disBlo @else disNon @endif ">
                        <label for="stdLoopAutoID" class="w100">
                            <input type="checkbox" name="stdLoopAuto" id="stdLoopAutoID" value="1" autocomplete="off" 
                            @if (isset($node->nodeRow->NodeDataBranch) && trim($node->nodeRow->NodeDataBranch) != '' 
                                && isset($GLOBALS["DB"]->dataLoops[$node->nodeRow->NodeDataBranch]) 
                                && isset($GLOBALS["DB"]->dataLoops[$node->nodeRow->NodeDataBranch]->DataLoopAutoGen)
                                && $GLOBALS["DB"]->dataLoops[$node->nodeRow->NodeDataBranch]->DataLoopAutoGen == 1) CHECKED @endif 
                            > Auto-Generate <small class="gry6">New Loop Items<div class="pL20">When User Clicks "Add" Button</div></small>
                        </label>
                    </div>
                    <div class="pL20"><small class="gry9"><i>From this root page, users can add records to the set until they choose to move on or reach the loop's limits.</i></small></div>
                    <div class="p10"></div>
                    <label>
                        <input type="radio" name="stepLoop" id="stepLoopY" value="1" autocomplete="off" 
                        @if ($node->isStepLoop()) CHECKED @endif 
                        > <span class="mL5 bld">Step-Through Behavior</span><br />
                    </label>
                    <div id="stepLoopOpts" class="pL20 @if ($node->isStepLoop()) disBlo @else disNon @endif ">
                        Field Marking A Finished Loop Item (Step)<br />
                        <select name="stepLoopDoneField" id="stepLoopDoneFieldID" class="form-control" autocomplete="off" >
                            @if ($node->isStepLoop())
                                {!! $GLOBALS["DB"]->fieldsDropdown(trim($GLOBALS["DB"]->dataLoops[$node->nodeRow->NodeDataBranch]->DataLoopDoneFld)) !!}
                            @else
                                {!! $GLOBALS["DB"]->fieldsDropdown() !!}
                            @endif
                        </select>
                    </div>
                    <div class="pL20"><small class="gry9"><i>All items in this data set are added elsewhere beforehand. Then the user is stepped through them one by one.</i></small></div>
                </div>
                <div class="col-md-8">
                    <div class="row mB20">
                        <div class="col-md-5">
                            <div><label for="nodeDataLoopID"><b>Loop Name</b></label></div>
                            <select name="nodeDataLoop" id="nodeDataLoopID" class="form-control" autocomplete="off" >
                                <option value="" @if (!isset($node->nodeRow->NodeDataBranch) || $node->nodeRow->NodeDataBranch == "") SELECTED @endif ></option>
                                @forelse ($GLOBALS["DB"]->dataLoops as $setPlural => $setInfo)
                                    <option @if (isset($node->nodeRow->NodeDataBranch) && $node->nodeRow->NodeDataBranch == $setPlural) SELECTED @endif 
                                        value="{{ $setPlural }}" >{{ $setPlural }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-7">
                            <div>
                                <label for="loopSlugID"><b>Root Page URL</b></label> 
                                <span class="gry9 f12 mL20">
                                    @if (isset($GLOBALS["DB"]->treeRow->TreeRootURL))
                                        {{ $GLOBALS["DB"]->treeRow->TreeRootURL }}/u/
                                    @endif
                                </span>
                            </div>
                            <input type="text" name="loopSlug" id="loopSlugID" class="form-control" autocomplete="off" 
                                value="@if (isset($node->nodeRow->NodePromptNotes)){!! $node->nodeRow->NodePromptNotes !!}@endif" >
                        </div>
                    </div>
                    <label for="nodeLoopInstructID"><b>Root Page Instructions</b> <small class="mL20">(text/HTML)</small></label>
                    <textarea name="nodeLoopInstruct" id="nodeLoopInstructID" class="form-control" style="height: 100px;" autocomplete="off" 
                        >@if (isset($node->nodeRow->NodePromptText)){!! $node->nodeRow->NodePromptText !!}@endif</textarea>
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
                            <input type="radio" name="dataManipType" id="dataManipTypeNew" value="New" onClick="return checkDataManipFlds();"
                                @if (isset($node->nodeRow->NodeType) && $node->nodeRow->NodeType == 'Data Manip: New') CHECKED @endif
                                > <h3 class="pL10 mTn5 mB10">Create New <span class="slGreenDark">Data Family</span> Record</h3>
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="dataManipType" id="dataManipTypeUpdate" value="Update" onClick="return checkDataManipFlds();"
                                @if (isset($node->nodeRow->NodeType) && $node->nodeRow->NodeType == 'Data Manip: Update') CHECKED @endif
                                > <h3 class="pL10 mTn5 mB10">Update Family <span class="slGreenDark">Data Family</span> Record</h3>
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="dataManipType" id="dataManipTypeWrap" value="Wrap" onClick="return checkDataManipFlds();"
                                @if (isset($node->nodeRow->NodeType) && $node->nodeRow->NodeType == 'Data Manip: Wrap') CHECKED @endif
                                > <h3 class="pL10 mTn5 mB10">Just Wrap Children In <span class="slGreenDark">Data Family</span></h3>
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
            <div id="dataNewRecord" class=" @if (isset($node->nodeRow->NodeType) && $node->nodeRow->NodeType == 'Data Manip: Wrap') disNon @else disBlo @endif ">
                <div class="row pT5 pB10">
                    <div class="col-md-1 taR pT5">
                        <h3 class="slGreenDark"><i class="fa fa-database"></i></h3>
                    </div>
                    <div class="col-md-5 f14">
                        <label class="w100">
                            <b>Set Record Field</b>
                            <select name="manipMoreStore" id="manipMoreStoreID" class="form-control" autocomplete="off" onClick="return checkData();" >
                                {!! $GLOBALS["DB"]->fieldsDropdown((isset($node->nodeRow->NodeDataStore)) ? trim($node->nodeRow->NodeDataStore) : '') !!}
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
                            {!! $GLOBALS["DB"]->allDefsDropdown((isset($node->nodeRow->NodeResponseSet)) ? $node->nodeRow->NodeResponseSet : '') !!}
                        </select>
                    </div>
                </div>
                
                @for ($i = 0; $i < $resLimit; $i++)
                    <div id="dataManipFld{{ $i }}" class=" @if (isset($node->dataManips[$i])) disBlo @else disNon @endif ">
                        <div class="row pT5 pB10">
                            <div class="col-md-1 taR pT5">
                                <h3 class="m0 slGreenDark"><i class="fa fa-database"></i></h3>
                            </div>
                            <div class="col-md-5 f14">
                                <select name="manipMore{{ $i }}Store" id="manipMore{{ $i }}StoreID" class="form-control" autocomplete="off" onClick="return checkData();" >
                                    @if (isset($node->dataManips[$i]) && isset($node->dataManips[$i]->NodeDataStore))
                                        {!! $GLOBALS["DB"]->fieldsDropdown($node->dataManips[$i]->NodeDataStore) !!}
                                    @else
                                        {!! $GLOBALS["DB"]->fieldsDropdown() !!}
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
                                        {!! $GLOBALS["DB"]->allDefsDropdown($node->dataManips[$i]->NodeResponseSet) !!}
                                    @else
                                        {!! $GLOBALS["DB"]->allDefsDropdown() !!}
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
                <label for="nodePromptTextID"><h3 class="m0 disIn mR20">Question or Prompt for User</h3> <small>(text/HTML)</small></label>
            </div>
        </div>
        <div class="panel-body">
            <textarea name="nodePromptText" id="nodePromptTextID" class="form-control" style="height: 100px;" autocomplete="off" 
                >@if (isset($node->nodeRow->NodePromptText)){!! $node->nodeRow->NodePromptText !!}@endif</textarea>
                
            <div class="row mT20">
                <div class="col-md-6">
                    <label class="w100">
                        <a id="extraSmallBtn" href="javascript:void(0)" class="f12">+ Small Instructions or Side-Notes</a> 
                        <div id="extraSmall" class="w100 @if (isset($node->nodeRow->NodePromptNotes) && trim($node->nodeRow->NodePromptNotes) != '') disBlo @else disNon @endif ">
                            <textarea name="nodePromptNotes" class="form-control" style="width: 100%; height: 60px;" autocomplete="off" 
                                >@if (isset($node->nodeRow->NodePromptNotes)){!! $node->nodeRow->NodePromptNotes !!}@endif</textarea>
                        </div>
                    </label>
                </div>
                <div class="col-md-6">
                    <label class="w100">
                        <a id="extraHTMLbtn" href="javascript:void(0)" class="f12">+ HTML/JS/CSS Extras After Node Field</a> 
                        <div id="extraHTML" class="w100 @if (isset($node->nodeRow->NodePromptAfter) && trim($node->nodeRow->NodePromptAfter) != '') disBlo @else disNon @endif ">
                            <textarea name="nodePromptAfter" class="form-control" style="width: 100%; height: 60px;" autocomplete="off" 
                                >@if (isset($node->nodeRow->NodePromptAfter)){!! $node->nodeRow->NodePromptAfter !!}@endif</textarea>
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
                <h3 class="m0">User Response Options</h3>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4">
                    <label class="w100 f18">
                        Response Type:
                        <select name="nodeTypeQ" id="nodeTypeQID" class="form-control form-control-lg w100" 
                            onChange="return changeResponseType(this.value);" autocomplete="off" >
                        @foreach ($nodeTypes as $type)
                            <option value="{{ $type }}" @if (isset($node->nodeRow->NodeType) && $node->nodeRow->NodeType == $type) SELECTED @endif >{{ $type }}</option>
                        @endforeach
                        </select>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="w100 f18">
                        Store User Response Here: 
                        <select name="nodeDataStore" class="form-control form-control-lg w100" autocomplete="off" >
                            {!! $GLOBALS["DB"]->fieldsDropdown(isset($node->nodeRow->NodeDataStore) ? trim($node->nodeRow->NodeDataStore) : '') !!}
                        </select>
                    </label>
                </div>
                <div class="col-md-4">
                    <label for="opts5ID" class="red f14">
                        <input type="checkbox" name="opts5" id="opts5ID" value="5" autocomplete="off" @if ($node->isRequired()) CHECKED @endif 
                            > User Response Required
                    </label>
                    <div id="resNotMulti" class="gry9 @if (isset($node->nodeRow->NodeType) && in_array($node->nodeRow->NodeType, ['Text', 'Long Text', 'Uploads'])) disBlo @else disNon @endif ">
                        <label>
                            Character/Upload Limit: 
                            <input type="number" name="nodeCharLimit" id="nodeCharLimitID" class="form-control disIn" style="width: 100px;" autocomplete="off" 
                                @if (isset($node->nodeRow->NodeCharLimit)) value="{{ $node->nodeRow->NodeCharLimit }}" @else value="" @endif >
                        </label>
                        <label>
                            Autofill Suggestions: 
                            <select name="nodeTextSuggest" id="nodeTextSuggestID" class="form-control disIn" style="width: 200px;" autocomplete="off" >
                                <option value="" @if (!isset($node->nodeRow->NodeTextSuggest) || $node->nodeRow->NodeTextSuggest == '') SELECTED @endif ></option>
                                @forelse ($defs as $def)
                                    <option value="{{ $def->DefSubset }}" @if (isset($node->nodeRow->NodeTextSuggest) && $node->nodeRow->NodeTextSuggest == $def->DefSubset) SELECTED @endif >{{ $def->DefSubset }}</option>
                                @empty
                                @endforelse
                            </select>
                        </label>
                    </div>
                </div>
            </div>
            
            <div id="resOpts" class=" @if (isset($node->nodeRow->NodeType) && in_array($node->nodeRow->NodeType, ['Radio', 'Checkbox', 'Drop Down', 'Other/Custom'])) disBlo @else disNon @endif ">
                <div class="row">
                    <div class="col-md-6">
                        <h3>Response Options Provided To User:</h3>
                    </div>
                    <div class="col-md-6 taR pT10 pL20 f18">
                        <div class="jumbotron" style="padding: 10px;">
                            <div class="pull-left"><i>Pull Options From...</i></div>
                            <label>
                                <b>Definition Set:</b> 
                                <select name="responseDefinition" class="form-control disIn" style="width: 200px;" autocomplete="off" >
                                    <option value="" @if ($currDefinition == '') SELECTED @endif ></option>
                                    @forelse ($defs as $def)
                                        <option value="{{ $def->DefSubset }}" @if ($currDefinition == $def->DefSubset) SELECTED @endif >{{ $def->DefSubset }}</option>
                                    @empty
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                <b>Entered Loop Items:</b> 
                                <select name="responseLoopItems" class="form-control disIn" style="width: 200px;" 
                                    autocomplete="off" >
                                    <option value="" @if ($currLoopItems == '') SELECTED @endif ></option>
                                    @forelse ($GLOBALS["DB"]->dataLoops as $plural => $loop)
                                        <option value="{{ $plural }}" @if ($currLoopItems == $plural) SELECTED @endif 
                                            >{{ $plural }}</option>
                                    @empty
                                    @endforelse
                                </select>
                            </label>
                            <div class="gryA f12"><i>(or manually enter response options below)</i></div>
                        </div>
                    </div>
                </div>
            
                <div class="row pB10">
                    <div class="col-md-1"></div>
                    <div class="col-md-3"><b>Stored Value</b></div>
                    <div class="col-md-6"><b>Displayed Value [HTML]</b></div>
                    <div class="col-md-2 taC"><b>Reveals Child Nodes</b></div>
                </div>
                
                @forelse ($node->responses as $r => $res)
                    <div id="r{{ $r }}" class="row pB10">
                        <div class="col-md-1 pT5 slBlueDark f22"><i class="fa fa-chevron-right"></i></div>
                        <div class="col-md-3">
                            <input type="text" name="response{{ $r }}Val" id="response{{ $r }}vID" value="{{ $res->NodeResValue }}" 
                            onKeyUp="return checkRes();" class="form-control" autocomplete="off" @if ($currDefinition != '') DISABLED @endif >
                        </div>
                        <div class="col-md-6">
                            <textarea type="text" name="response{{ $r }}" id="response{{ $r }}ID" class="form-control mBn10" style="height: 35px;" 
                            onKeyUp="return checkRes();" autocomplete="off" @if ($currDefinition != '') DISABLED @endif >{{ $res->NodeResEng }}</textarea>
                        </div>
                        <div class="col-md-2 taC">
                            <input type="checkbox" name="response{{ $r }}ShowKids" value="1" @if ($node->indexShowsKid($r)) CHECKED @endif >
                        </div>
                    </div>
                @empty
                @endforelse
                @if ($currDefinition == '')
                    @for ($r = sizeof($node->responses); $r < $resLimit; $r++)
                        <div id="r{{ $r }}" class="row pB10 @if ($r == sizeof($node->responses)) disBlo @else disNon @endif ">
                            <div class="col-md-1 pT5 slBlueDark f22"><i class="fa fa-chevron-right"></i></div>
                            <div class="col-md-3">
                                <input type="text" name="response{{ $r }}Val" id="response{{ $r }}vID" value="" 
                                onKeyUp="return checkRes();" class="form-control" autocomplete="off" @if ($currDefinition != '') DISABLED @endif >
                            </div>
                            <div class="col-md-6">
                                <textarea type="text" name="response{{ $r }}" id="response{{ $r }}ID" class="form-control mBn10" style="height: 35px;" 
                                onKeyUp="return checkRes();" autocomplete="off" @if ($currDefinition != '') DISABLED @endif ></textarea>
                            </div>
                            <div class="col-md-2 taC">
                                <input type="checkbox" name="response{{ $r }}ShowKids" value="1" autocomplete="off" >
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
                <div class="col-md-4">
                    <div class="checkbox">
                        <label for="opts13ID">
                            <input type="checkbox" name="opts13" id="opts13ID" value="13" autocomplete="off" @if ($node->isOnPrevLine()) CHECKED @endif 
                            > Node Shares Previous Node's Line
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="checkbox">
                        <label for="opts11ID">
                            <input type="checkbox" name="opts11" id="opts11ID" value="11" autocomplete="off" @if ($node->isOneLiner()) CHECKED @endif 
                            onClick="if (document.getElementById('opts11ID').checked) document.getElementById('opts17ID').checked = false;" 
                            > Node Q&A On One Line
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div id="responseOneLine" class="checkbox @if (isset($node->nodeRow->NodeType) && in_array($node->nodeRow->NodeType, ['Radio', 'Checkbox'])) disBlo @else disNon @endif ">
                        <label for="opts17ID">
                            <input type="checkbox" name="opts17" id="opts17ID" value="17" autocomplete="off" @if ($node->isOneLineResponses()) CHECKED @endif 
                            onClick="if (document.getElementById('opts17ID').checked) document.getElementById('opts11ID').checked = false;" 
                            > Responses On One Line
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
                            <a id="cond{{ $i }}delBtn" href="javascript:void(0)" class="pull-right disBlo condDelBtn"><i class="fa fa-minus-circle" aria-hidden="true"></i></a> 
                            <div id="cond{{ $i }}delWrap" href="javascript:void(0)" class="pull-right disNon f10 pT5 pL10">
                                <i class="red">Deleted</i> 
                                <a id="cond{{ $i }}delUndo" href="javascript:void(0)" class="condDelBtnUndo f10 mL20">Undo</a> 
                            </div>
                            {{ $cond->CondTag }}
                            <span class="f10 mL10">{!! view('vendor.survloop.admin.db.inc-describeCondition', [ "cond" => $cond, "i" => $i ])->render() !!}</span>
                        </div>
                    @endforeach
                @endif
                
                {!! view('vendor.survloop.admin.db.inc-addCondition', [])->render() !!}
                
            </div>
        </div>
    </div>
    <div class="col-md-6 taC pT20">
        <input type="submit" value="Save Node Changes" class="btn btn-lg btn-primary f32" @if (!$canEditTree) DISABLED @endif >
    </div>
</div>

<label class="w100 pB20">
    <a id="internalNotesBtn" href="javascript:void(0)" class="f12">Internal Notes</a> 
    <div id="internalNotes" class=" @if (isset($node->nodeRow->NodeInternalNotes) && trim($node->nodeRow->NodeInternalNotes) != '') disBlo @else disNon @endif ">
        <textarea name="nodeInternalNotes" class="form-control" style="height: 40px;" autocomplete="off" 
            >@if (isset($node->nodeRow->NodeInternalNotes)){!! $node->nodeRow->NodeInternalNotes !!}@endif</textarea>
    </div>
</label>

@if ($canEditTree)
    
    @if (isset($node->nodeRow->NodeID) && $node->nodeRow->NodeID > 0)
        <br /><input type="checkbox" name="deleteNode" id="deleteNodeID" value="1" > <label for="deleteNodeID">Delete This Node</label><br />
    @endif
    
    </form>
@else
    <div class="p20 m20 f20"><center><i>Sorry, you do not have permissions to actually edit the tree.</i></center></div><div class="p20 m20"></div>
@endif

<script type="text/javascript">
$(document).ready(function(){
    $("#specialFuncsBtn").click(function(){ $("#specialFuncs").slideToggle("fast"); });
    $("#extraSmallBtn").click(function() { $("#extraSmall").slideToggle("fast"); });
    $("#extraHTMLbtn").click(function() { $("#extraHTML").slideToggle("fast"); });
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
    document.getElementById('hasBranch').style.display='none';
    document.getElementById('hasInstruct').style.display='none';
    document.getElementById('hasDataManip').style.display='none';
    if (newType == 'branch' || newType == 'data' || newType == 'loop' || newType == 'page' || newType == 'instruct') {
        document.getElementById('hasResponse').style.display='none';
        if  (newType == 'instruct') document.getElementById('hasInstruct').style.display='block';
        else if (newType == 'data') document.getElementById('hasDataManip').style.display='block';
        else if (newType == 'branch') document.getElementById('hasBranch').style.display='block';
        else if (newType == 'loop') document.getElementById('hasLoop').style.display='block';
        else if (newType == 'page') document.getElementById('hasPage').style.display='block';
    }
    else document.getElementById('hasResponse').style.display='block';
    return true;
}

function changeResponseType(newType) {
    if (newType == 'Radio' || newType == 'Checkbox' || newType == 'Drop Down' || newType == 'Other/Custom') {
        document.getElementById('resOpts').style.display='block'; document.getElementById('resNotMulti').style.display='none';
    } else {
        document.getElementById('resOpts').style.display='none'; document.getElementById('resNotMulti').style.display='block';
    }
    if (newType == 'Radio' || newType == 'Checkbox') {
        document.getElementById('responseOneLine').style.display='block';
    } else {
        document.getElementById('responseOneLine').style.display='none';
    }
    if (newType == 'Checkbox' || newType == 'Other/Custom') {
        document.getElementById('checkboxDataOpts').style.display='block';
    } else {
        document.getElementById('checkboxDataOpts').style.display='none';
    }
    return true;
}

var maxRes = 0; var i = 0;
function checkRes() {
    maxRes = 0;
    for (i=0; i < {{ $resLimit }}; i++) {
        if (document.getElementById('response'+i+'ID').value != '' || document.getElementById('response'+i+'vID').value != '') maxRes = i;
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
