<!-- resources/views/vendor/survloop/admin/tree/node-edit.blade.php -->
<div class="container">
@if ($canEditTree)
    <form name="mainPageForm" method="post" @if (isset($node->nodeRow) && isset($node->nodeRow->node_id))
        action="/dashboard/surv-{{ $treeID }}/map/node/{{ $node->nodeRow->node_id }}"
        @else action="/dashboard/surv-{{ $treeID }}/map/node/-3" @endif >
    <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="sub" value="1">
    <input type="hidden" name="treeID" value="{{ $treeID }}">
    <input type="hidden" name="nodeParentID" 
        @if ($GLOBALS['SL']->REQ->has('parent') 
            && intVal($GLOBALS['SL']->REQ->input('parent')) > 0) 
            value="{{ $GLOBALS['SL']->REQ->input('parent') }}" 
        @else value="{{ $node->parentID }}" 
        @endif >
    <input type="hidden" name="childPlace" 
        @if ($GLOBALS['SL']->REQ->has('start') 
            && intVal($GLOBALS['SL']->REQ->input('start')) > 0) 
            value="start"
        @else 
            @if ($GLOBALS['SL']->REQ->has('end') 
                && intVal($GLOBALS['SL']->REQ->input('end')) > 0) 
                value="end" 
            @else 
                value="" 
            @endif
        @endif >
    <input type="hidden" name="orderBefore" 
        @if ($GLOBALS['SL']->REQ->has('ordBefore') 
            && intVal($GLOBALS['SL']->REQ->ordBefore) > 0) 
            value="{{ $GLOBALS['SL']->REQ->ordBefore }}" 
        @else 
            value="-3" 
        @endif >
    <input type="hidden" name="orderAfter" 
        @if ($GLOBALS['SL']->REQ->has('ordAfter') 
            && intVal($GLOBALS['SL']->REQ->ordAfter) > 0) 
            value="{{ $GLOBALS['SL']->REQ->ordAfter }}" 
        @else 
            value="-3" 
        @endif >
@endif

<div class="slCard nodeWrap">
    <div class="relDiv w100">
        <a class="btn btn-secondary btn-sm absDiv" style="right: 0px; top: -5px;" 
            @if ($GLOBALS['SL']->treeRow->tree_type == 'Page')
                href="/dashboard/page/{{ $treeID }}?all=1&alt=1&refresh=1#n{{ $node->nodeRow->node_id }}" 
            @elseif (isset($node->nodeRow) && isset($node->nodeRow->node_id))
                href="/dashboard/surv-{{ $treeID }}/map?all=1&alt=1&refresh=1#n{{ $node->nodeRow->node_id }}" 
            @else
                href="/dashboard/surv-{{ $treeID }}/map?all=1&alt=1&refresh=1" 
            @endif
            >Back to Tree Map</a>
    </div>

    @if (isset($node->nodeRow->node_id) && $node->nodeRow->node_id > 0) 
        <b>Editing <nobr>Node #{{ $node->nodeRow->node_id }}</nobr></b>
    @else
        <b>Adding Node</b>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="pR15">
                {!! view('vendor.survloop.admin.tree.node-edit-type', [
                    "node"        => $node,
                    "nodeTypes"   => $nodeTypes,
                    "parentNode"  => $parentNode,
                    "nodeTypeSel" => $nodeTypeSel
                ])->render() !!}
            </div>
        </div>
        <div class="col-lg-4 mBn10 slGreenDark">
            <label>
                <b><i class="fa fa-database mR5"></i> Data Family
                @if ($node->nodeID == $GLOBALS['SL']->treeRow->tree_root)
                    <nobr>(Core Table)</nobr>
                @endif </b>
                <div class="nFld mT0">
                    <select name="nodeDataBranch" id="nodeDataBranchID" 
                        class="form-control slGreenDark" autocomplete="off">
                        {!! $dataBranchDrop !!}
                    </select>
                </div>
                <div class="fPerc66 mBn5">node's whole family tree relates</div>
            </label>
        </div>
        <div class="col-lg-4">
            <input type="submit" value="Save Changes" class="btn btn-primary btn-block mT20" 
                @if (!$canEditTree) DISABLED @endif >
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mB10">
        
        {!! view(
            'vendor.survloop.admin.tree.node-edit-layout', 
            [
                "node"       => $node,
                "parentNode" => $parentNode
            ]
        )->render() !!}
    
        <div id="hasInstruct" class="mTn20 
            @if ($node->isInstruct() || $node->isInstructRaw()) disBlo @else disNon @endif ">
            <div class="slCard nodeWrap">
                <div class="nFld w100">
                    @if ($node->isInstruct()) 
                        <textarea name="nodeInstruct" id="nodeInstructID" 
                            class="form-control w100" autocomplete="off"
                            style="height: 350px;">@if (isset($node->nodeRow->node_prompt_text)){!! 
                                $node->nodeRow->node_prompt_text !!}@endif</textarea>
                    @else
                        <textarea name="nodeInstruct" id="nodeInstructID" 
                            class="form-control w100" autocomplete="off"
                            style="height: 350px; font-family: Courier New;"
                            >@if (isset($node->nodeRow->node_prompt_text)){!! 
                                $node->nodeRow->node_prompt_text !!}@endif</textarea>
                    @endif
                </div>
                <label class="w100 pT10 pB10">
                    <a id="extraHTMLbtn2" href="javascript:;" class="f12 fL">+ HTML/JS/CSS Extras</a> 
                    <div id="extraHTML2" class="w100 fC @if (isset($node->nodeRow->node_prompt_after) 
                        && trim($node->nodeRow->node_prompt_after) != '') disBlo @else disNon @endif ">
                        <div class="nFld mT0"><textarea name="instrPromptAfter" class="form-control" 
                            style="width: 100%; height: 100px;" autocomplete="off"
                            >@if (isset($node->nodeRow->node_prompt_after)
                                ){!! $node->nodeRow->node_prompt_after !!}@endif</textarea></div>
                        <span class="slGrey f12">"[[nID]]" will be replaced with node ID</span>
                    </div>
                </label>
            </div>
        </div>
        
        <div id="hasBranch" class=" @if ($node->isBranch()) disBlo @else disNon @endif ">
            <div class="slCard nodeWrap">
                <h3 class="m0">Branch Title</h3>
                <label for="branchTitleID" class="w100 mT0">
                    <div class="nFld mT0"><input type="text" name="branchTitle" id="branchTitleID" 
                        class="form-control" autocomplete="off" value="@if (isset($node->nodeRow->node_prompt_text)
                            ){!! strip_tags($node->nodeRow->node_prompt_text) !!}@endif" ></div>
                </label>
                <small class="slGrey">For internal use only.
                Branches are a great way to mark navigation areas, mark key conditions which greatly impact
                user experience, associate data families, and/or just internally organize the the tree. 
                </small>
            </div>
        </div>
        
        {!! view('vendor.survloop.admin.tree.node-edit-loops', [ "node" => $node ])->render() !!}
        
        {!! view(
            'vendor.survloop.admin.tree.node-edit-page', 
            [
                "node"     => $node,
                "currMeta" => $currMeta
            ]
        )->render() !!}
        
        {!! view(
            'vendor.survloop.admin.tree.node-edit-data-manip', 
            [
                "node"     => $node,
                "treeList" => $treeList,
                "resLimit" => $resLimit
            ]
        )->render() !!}
        
        {!! view('vendor.survloop.admin.tree.node-edit-widgets', [ "node" => $node ])->render() !!}
        
        <div id="hasSendEmail" class=" @if ($node->nodeType == 'Send Email') disBlo @else disNon @endif ">
            <div class="slCard nodeWrap">
                <h4 class="mT0">Email Sending Options</h4>
                {!! $widgetEmail !!}
            </div>
        </div>
        
        <div id="hasBigButt" class=" @if ($node->isBigButt()) disBlo @else disNon @endif ">
            <div class="slCard nodeWrap">
                <h4 class="mT0">Big Button Settings</h4>
                <h4>Button Text</h4>
                <div class="nFld m0 mB20">
                    <input type="text" name="bigBtnText" id="bigBtnTextID" class="form-control" 
                        @if (isset($node->nodeRow->node_default)) value="{{ $node->nodeRow->node_default }}" 
                        @endif onKeyUp="return previewBigBtn();" >
                </div>
                <h4 class="mT0">Button On Click JavaScript</h4>
                <div class="nFld m0">
                    <input type="text" name="bigBtnJS" class="form-control" 
                        @if (isset($node->nodeRow->node_data_store)) value="{{ $node->nodeRow->node_data_store }}" 
                        @endif >
                </div>
                <div class="row mT20 mB20">
                    <div class="col-md-6">
                        <h4 class="mT0">Button Style</h4>
                        <div class="nFld m0">
                            <select name="bigBtnStyle" id="bigBtnStyleID" class="form-control"
                                onChange="return previewBigBtn();">
                                <option value="Default" @if (!isset($node->nodeRow->node_response_set) 
                                    || $node->nodeRow->node_response_set == 'Default') SELECTED @endif 
                                    >Default Button</option>
                                <option value="Primary" @if ($node->nodeRow->node_response_set == 'Primary') 
                                    SELECTED @endif >Primary Button</option>
                                <option value="Text" @if ($node->nodeRow->node_response_set == 'Text') 
                                    SELECTED @endif >Text/HTML Link</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 pT20 taR">
                        <label class="mT20"><input type="checkbox" name="opts43" value="43" 
                            @if ($node->nodeRow->node_opts%43 == 0) CHECKED @endif > 
                            <h4 class="disIn">Toggle Child Nodes On Click</h4>
                        </label>
                    </div>
                </div>
                Preview:
                <div id="buttonPreview" class="w100 m0"></div>
                <div class="p20">
                    <i>Optionally, you can fill in the "Question or Prompt for User" section below, which can provide
                    information or instructions to the user before the Big Button is printed.</i>
                </div>
            </div>
        </div>
        
        {!! view(
            'vendor.survloop.admin.tree.node-edit-questions', 
            [
                "node"          => $node,
                "defs"          => $defs,
                "resLimit"      => $resLimit,
                "childNodes"    => $childNodes,
                "loopDrops"     => $loopDrops
            ]
        )->render() !!}
        
        {!! view('vendor.survloop.admin.tree.node-edit-block', [ "node" => $node ])->render() !!}
        
    </div> <!-- end of left column -->
    <div class="col-lg-4 mB10">

        <div id="pagePreview" class=" 
            @if ($node->nodeType == 'Page') disBlo @else disNon @endif ">
            <div class="slCard nodeWrap">
                <h4 class="mT0">Social Sharing Preview</h4>
                {!! view('vendor.survloop.admin.seo-meta-editor-preview', [])->render() !!}
            </div>
        </div>
    
        <div class="slCard nodeWrap">
            <label for="nodeConditionsID"><h4 class="mT0">Conditions To Include Node</h4></label>
            @if (sizeof($node->conds) > 0)
                @foreach ($node->conds as $i => $cond)
                    <input type="hidden" id="delCond{{ $i }}ID" name="delCond{{ $cond->cond_id }}" value="N">
                    <div id="cond{{ $i }}wrap" class="round10 brd p5 mB10 pL10">
                        <a id="cond{{ $i }}delBtn" class="float-right disBlo condDelBtn"
                            href="javascript:;"><i class="fa fa-trash-o" aria-hidden="true"></i></a> 
                        <div id="cond{{ $i }}delWrap" href="javascript:;" 
                            class="float-right disNon fPerc80 pT5 pL10">
                            <i class="red">Deleted</i> 
                            <a id="cond{{ $i }}delUndo" href="javascript:;" 
                                class="condDelBtnUndo fPerc80 mL20">Undo</a> 
                        </div>
                        @if (trim($cond->cond_operator) == 'AB TEST')
                            %AB: {{ $cond->cond_desc }}
                        @else
                            {{ $cond->CondTag }}
                            <span class="fPerc80 mL10">{!! view(
                                'vendor.survloop.admin.db.inc-describeCondition', 
                                [
                                    "nID"  => $node->nodeID,
                                    "cond" => $cond,
                                    "i"    => $i
                                ]
                            )->render() !!}</span>
                        @endif
                    </div>
                @endforeach
            @endif
            {!! view('vendor.survloop.admin.db.inc-addCondition')->render() !!}
            {!! view('vendor.survloop.admin.tree.inc-add-ab-test')->render() !!}
        </div>
        
        {!! view('vendor.survloop.admin.tree.node-edit-response-layout', [ "node" => $node ])->render() !!}
        
        <div class="slCard nodeWrap">
            <label class="w100 pB20">
                <a id="internalNotesBtn" href="javascript:;" class="f12">+ Internal Notes</a> 
                <div id="internalNotes" class=" @if (isset($node->nodeRow->node_internal_notes) 
                    && trim($node->nodeRow->node_internal_notes) != '') disBlo @else disNon @endif ">
                    <div class="nFld mT0"><textarea name="nodeInternalNotes" autocomplete="off" 
                        class="form-control slGrey" style="height: 100px;" 
                        >@if (isset($node->nodeRow->node_internal_notes)){!! 
                            $node->nodeRow->node_internal_notes !!}@endif</textarea></div>
                </div>
            </label>
        
            @if ($canEditTree)
                @if (isset($node->nodeRow->node_id) && $node->nodeRow->node_id > 0)
                    <div class="mT10 mB10">
                        <input type="checkbox" name="deleteNode" id="deleteNodeID" value="1" class="mR3" > 
                        <label for="deleteNodeID">Delete This Node</label>
                    </div>
                @endif
                </form>
            @else
                <div class="p20 m20"><center><i>
                    Sorry, you do not have permissions to actually edit the tree.
                </i></center></div>
                <div class="p20 m20"></div>
            @endif
        </div>
        
        <div id="emailPreviewStuff" 
            class=" @if ($node->nodeType == 'Send Email') disBlo @else disNon @endif " >
            <div class="slCard nodeWrap">
                <h4 class="slBlueDark m0 mB5"><i>Template Preview:</i></h4>
                <div id="previewEmailDump1" class="
                    @if (intVal($node->nodeRow->node_default) == -69) disBlo @else disNon @endif ">
                    <div class="w100 brdDsh m5 p5">
                        Field Name:<br />User Response<br /><br />
                        Field Name:<br />User Response<br /><br />
                        Field Name:<br />User Response<br /><br />
                    </div>
                </div>
                @forelse ($emailList as $i => $email)
                    <div id="previewEmail{{ $email->email_id }}" class="
                        @if ($email->email_id == $node->nodeRow->node_default) disBlo @else disNon @endif ">
                        <div class="w100 brdDsh m5 p5">{!! $email->email_body !!}</div>
                    </div>
                @empty
                @endforelse
                <a href="/dashboard/emails">Manage System Email Templates</a>
            </div>
        </div>
    
    
    </div>
</div>

</div>