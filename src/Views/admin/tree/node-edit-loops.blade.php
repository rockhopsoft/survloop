<!-- resources/views/vendor/survloop/admin/tree/node-edit-loops.blade.php -->
<div id="hasLoop" class=" @if ($node->isLoopRoot()) disBlo @else disNon @endif ">
    <div class="slCard nodeWrap">
        <h4 class="mT0">Data Set's Loop Options</h4>
        <label class="mB10"><div class="row">
            <div class="col-md-6 nPrompt" style="padding: 5px 0px 0px 15px;">
                <input type="radio" name="stepLoop" id="stepLoopN" 
                    value="0" autocomplete="off" 
                    @if (!$node->isStepLoop()) CHECKED @endif >
                    <h4 class="disIn mL5">Standard Loop Behavior</h4>
            </div><div class="col-md-6 slGrey">
                From this root page, users can add records to the set until
                they choose to move on or reach the loop's limits.
            </div>
        </div></label>
        <label class="mB10"><div class="row">
            <div class="col-md-6 nPrompt" style="padding: 5px 0px 0px 15px;">
                <input type="radio" name="stepLoop" id="stepLoopY" 
                    value="1" autocomplete="off" 
                    @if ($node->isStepLoop()) CHECKED @endif >
                    <h4 class="disIn mL5">Step-Through Behavior</h4>
            </div><div class="col-md-6 slGrey">
                All items in this data set are added elsewhere beforehand.
                Then the user is stepped through them one by one.
            </div>
        </div></label>
        
        <div class="row mT20">
            <div class="col-md-6 slGreenDark">
                <label class="nPrompt">
                    <h4 class="mT0"><span class="slGreenDark">Loop Name</span></h4>
                    <div class="nFld mT0">
                        <select name="nodeDataLoop" id="nodeDataLoopID" autocomplete="off" 
                            class="form-control form-control-lg w100 slGreenDark" >
                            <option value="" 
                                @if (!isset($node->nodeRow->node_default) 
                                    || $node->nodeRow->node_default == "") SELECTED
                                @endif ></option>
                            @forelse ($GLOBALS['SL']->dataLoops as $setPlural => $setInfo)
                                <option 
                                    @if (isset($node->nodeRow->node_default) 
                                        && $node->nodeRow->node_default == $setPlural) 
                                        SELECTED
                                    @endif 
                                    value="{{ $setPlural }}" >{{ $setPlural }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                </label>
            </div>
            <div class="col-md-6">
                <div id="stdLoopOpts" 
                    class="w100 @if (!$node->isStepLoop()) disBlo @else disNon @endif ">
                    <label class="nPrompt"><b>
                    <input type="checkbox" 
                        name="stdLoopAuto" id="stdLoopAutoID" value="1" 
                    @if (isset($node->nodeRow->node_default)
                        && trim($node->nodeRow->node_default) != '' 
                        && isset($GLOBALS['SL']->dataLoops[$node->nodeRow->node_default]) 
                        && isset($GLOBALS['SL']->dataLoops[$node->nodeRow->node_default]->data_loop_auto_gen)
                        && $GLOBALS['SL']->dataLoops[$node->nodeRow->node_default]->data_loop_auto_gen == 1) 
                        CHECKED
                    @endif autocomplete="off" > 
                    Auto-Generate new loop items when user clicks "Add" button
                    </b></label>
                </div>
                <div id="stepLoopOpts" 
                    class="w100 @if ($node->isStepLoop()) disBlo @else disNon @endif ">
                    <label class="nPrompt">
                        <h4 class="mT0">Field Marking A Finished Loop Item (Step)</h4>
                        <div class="nFld mT0"><select name="stepLoopDoneField" id="stepLoopDoneFieldID" 
                            class="form-control form-control-lg" autocomplete="off" >
                            @if ($node->isStepLoop() && isset($node->nodeRow->node_default))
                                {!! $GLOBALS['SL']->fieldsDropdown(trim(
                                    $GLOBALS['SL']->dataLoops[$node->nodeRow->node_default]
                                        ->data_loop_done_fld
                                )) !!}
                            @else
                                {!! $GLOBALS['SL']->fieldsDropdown() !!}
                            @endif
                        </select></div>
                    </label>
                </div>
            </div>
        </div>
        
        <label class="nPrompt mT20 mB20">
            <h4 class="disIn mT0">Root Page Instructions</h4>
            <small class="mL20 slGrey">(text/HTML)</small>
            <div class="nFld mT0">
                <textarea name="nodeLoopInstruct" id="nodeLoopInstructID" 
                    class="form-control form-control-lg" 
                    style="height: 100px; font-family: Courier New;" 
                    autocomplete="off" >@if (isset($node->nodeRow->node_prompt_text)){!! 
                        $node->nodeRow->node_prompt_text !!}@endif</textarea>
            </div>
        </label>
    </div>
</div>

<div id="hasCycle" class=" @if ($node->isLoopCycle()) disBlo @else disNon @endif ">
    <div class="slCard nodeWrap">
        <h4 class="mT0">Data Loop Cycle's Options</h4>
        <label class="nPrompt">
            <h4 class="m0 mB5"><span class="slGreenDark">Loop To Cycle Through</span></h4>
            <div class="nFld mT0"><select name="nodeDataCycle" id="nodeDataCycleID" 
                class="form-control form-control-lg w100 slGreenDark" autocomplete="off" >
                <option value="" @if (!isset($node->nodeRow->node_response_set) 
                    || $node->nodeRow->node_response_set == "") SELECTED @endif ></option>
                @forelse ($GLOBALS['SL']->dataLoops as $setPlural => $setInfo)
                    <option @if (isset($node->nodeRow->node_response_set) 
                        && $node->nodeRow->node_response_set == 'LoopItems::' . $setPlural) SELECTED @endif 
                        value="{{ $setPlural }}" >{{ $setPlural }}</option>
                @empty
                @endforelse
            </select></div>
        </label>
    </div>
</div>

<div id="hasSort" class=" @if ($node->isLoopSort()) disBlo @else disNon @endif ">
    <div class="slCard nodeWrap">
        <h4 class="mT0">Data Loop Sorting Options</h4>
        <div class="row slGreenDark">
            <div class="col-md-6 pR20">
                <label class="nPrompt">
                    <h4 class="m0 mB5"><span class="slGreenDark">Data Loop:</span></h4>
                    <div class="nFld mT0"><select name="nodeDataSort" id="nodeDataSortID" 
                        class="form-control form-control-lg w100 slGreenDark" autocomplete="off" >
                        <option value="" @if (!isset($node->nodeRow->node_response_set) 
                            || $node->nodeRow->node_response_set == "") SELECTED @endif ></option>
                        @forelse ($GLOBALS['SL']->dataLoops as $setPlural => $setInfo)
                            <option @if (isset($node->nodeRow->node_response_set) 
                                && $node->nodeRow->node_response_set == 'LoopItems::' . $setPlural) 
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
                        class="form-control form-control-lg" autocomplete="off" onClick="return checkData();" >
                        {!! $GLOBALS['SL']->fieldsDropdown((isset($node->nodeRow->node_data_store)) 
                            ? trim($node->nodeRow->node_data_store) : '') !!}
                    </select></div>
                    <i class="f12">*Must be integer field within Data Loop's Table.</i>
                </label>
            </div>
        </div>
    </div>
</div>