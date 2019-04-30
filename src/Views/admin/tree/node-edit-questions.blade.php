<!-- resources/views/vendor/survloop/admin/tree/node-edit-questions.blade.php -->
<div id="hasPrompt" class=" @if ($node->isSpecial() || $node->isWidget() || $node->isLayout()) disNon 
    @else disBlo @endif ">
    <div class="slCard nodeWrap">
        <label for="nodePromptTextID"><h4 class="m0 disIn mR20">Question or Prompt for User</h4> 
            <small>(text/HTML)</small></label>
        <div class="nFld">
            <textarea name="nodePromptText" id="nodePromptTextID" class="form-control" 
                style="height: 200px; font-family: Courier New;" autocomplete="off" 
                >@if(isset($node->nodeRow->NodePromptText)){!! $node->nodeRow->NodePromptText !!}@endif</textarea>
        </div>
        <div class="row mT20">
            <div class="col-md-6">
                <label class="w100">
                    <a id="extraSmallBtn" href="javascript:;" class="f12"
                        >+ Small Instructions or Side-Notes</a> 
                    <div id="extraSmall" class="w100 @if (isset($node->nodeRow->NodePromptNotes) 
                        && trim($node->nodeRow->NodePromptNotes) != '') disBlo @else disNon @endif ">
                        <div class="nFld mT0"><textarea name="nodePromptNotes" class="form-control" 
                            style="width: 100%; height: 100px;" autocomplete="off" 
                                >@if (isset($node->nodeRow->NodePromptNotes)
                                ){!! $node->nodeRow->NodePromptNotes !!}@endif</textarea></div>
                        <label class="m10">
                            <input type="checkbox" name="opts83" id="opts83ID" value="83" class="mR5"
                                @if ($node->nodeRow->NodeOpts%83 == 0) CHECKED @endif autocomplete="off" >
                            Show After Pressing Info Button: 
                            <a href="javascript:;"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </label>
                    </div>
                </label>
            </div>
            <div class="col-md-6">
                <label class="w100">
                    <a id="extraHTMLbtn" href="javascript:;" class="f12"
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

<div id="hasResponse" class=" @if ($node->isSpecial() || $node->isWidget() || $node->isLayout()) disNon 
    @else disBlo @endif ">
    <div class="slCard nodeWrap">
        <h4 class="mT0">User Response Settings</h4>
        <div id="storeResponseDiv" class="row mB10 @if ($node->isSpreadTbl()) disNon @else disBlo @endif ">
            <div class="col-md-6">
                <label class="w100">
                    <h4 class="m0 slGreenDark">Store User Response</h4>
                    <div class="nFld m0"><select name="nodeDataStore" class="form-control form-control-lg w100" 
                        autocomplete="off" >
                        {!! $GLOBALS['SL']->fieldsDropdown(isset($node->nodeRow->NodeDataStore) 
                            ? trim($node->nodeRow->NodeDataStore) : '') !!}
                    </select></div>
                </label>
            </div>
            <div class="col-md-6">
                <div class="nFld w100 mT0"><label class="w100">
                    <h4 class="mT0">Default Value:</h4> 
                    <input type="text" name="nodeDefault" id="nodeDefaultID" class="form-control form-control-lg"
                        @if (isset($node->nodeRow->NodeDefault)) value="{{ $node->nodeRow->NodeDefault }}" 
                        @else value="" @endif autocomplete="off" >
                </label></div>
            </div>
        </div>
        <div class="row mB10">
            <div class="col-md-6">
                <label for="opts5ID" class="red fPerc125 mB10">
                    <input type="checkbox" name="opts5" id="opts5ID" value="5" autocomplete="off" 
                        @if ($node->isRequired()) CHECKED @endif 
                        onClick="return changeRequiredType();"> User Response Required
                </label>
            </div>
            <div class="col-md-6">
                <div id="NumberOpts" class=" @if (isset($node->nodeRow->NodeType) && 
                    in_array($node->nodeRow->NodeType, ['Text:Number', 'Slider'])) disBlo @else disNon 
                    @endif ">
                    <div class="row mB10">
                        <label class="col-6">Minimum Allowed:
                            <input type="text" name="numOptMin" class="form-control" autocomplete="off"
                                @if (isset($node->extraOpts["minVal"]) 
                                    && $node->extraOpts["minVal"] !== false) 
                                    value="{{ $node->extraOpts["minVal"] }}" @endif ></label>
                        <label class="col-6">Maximum Allowed:
                            <input type="text" name="numOptMax" class="form-control" autocomplete="off"
                                @if (isset($node->extraOpts["maxVal"]) 
                                    && $node->extraOpts["maxVal"] !== false) 
                                    value="{{ $node->extraOpts["maxVal"] }}" @endif ></label>
                    </div>
                    <div class="row mB10">
                        <label class="col-6">Increment Size:
                            <input type="text" name="numIncr" class="form-control" autocomplete="off"
                                @if (isset($node->extraOpts["incr"]) && $node->extraOpts["incr"] !== false) 
                                    value="{{ $node->extraOpts["incr"] }}" @endif ></label>
                        <label class="col-6">Unit Label:
                            <input type="text" name="numUnit" class="form-control" autocomplete="off"
                                @if (isset($node->extraOpts["unit"]) && $node->extraOpts["unit"] !== false) 
                                    value="{{ $node->extraOpts["unit"] }}" @endif ></label>
                    </div>
                    <label class="m10">
                        <input type="checkbox" name="opts101" id="opts101ID" value="101" class="mR5"
                            @if ($node->nodeRow->NodeOpts%101 == 0) CHECKED @endif autocomplete="off" >
                        Provide Calculator to Sum 12 Months
                    </label>
                </div>
            </div>
            <div id="spreadTblOpts" class="p20 @if ($node->isSpreadTbl()) disBlo @else disNon @endif ">
                <label class="finger fPerc133">
                    <input type="radio" name="spreadTblTyp" id="spreadTblTypA" value="open"
                    @if (!isset($node->nodeRow->NodeDataStore) || trim($node->nodeRow->NodeDataStore) == '') 
                        CHECKED @endif class="sprdTblType" >
                    User Adds Rows As Needed
                </label>
                <label class="finger fPerc133">
                    <input type="radio" name="spreadTblTyp" id="spreadTblTypB" value="defs"
                    @if (isset($node->nodeRow->NodeDataStore) && trim($node->nodeRow->NodeDataStore) != '') 
                        CHECKED @endif class="sprdTblType" >
                    Rows Generated From List
                </label>
                <div id="spreadTblOpen" class="row mT10 
                    @if (!$node->hasResponseOpts()) disBlo @else disNon @endif ">
                    <label class="col-6">
                        <h4 class="mT0">Maximum Number of Table Rows:</h4> 
                        <div class="nFld"><input name="spreadTblMaxRows" id="spreadTblMaxRowsID" 
                            type="number" class="form-control form-control-lg" autocomplete="off" 
                            @if (isset($node->nodeRow->NodeCharLimit)) 
                                value="{{ $node->nodeRow->NodeCharLimit }}" @endif >
                        </div>
                    </label>
                    <label class="col-6">
                        <h4 class="mT0">Add & Edit Loop Rows:</h4> 
                        <div class="nFld">
                            <select name="spreadTblLoop" id="spreadTblLoopID" autocomplete="off"
                                class="form-control form-control-lg" >
                                <option @if (!isset($node->nodeRow->NodeResponseSet)
                                    || trim($node->nodeRow->NodeResponseSet) == '') SELECTED @endif
                                    value="" > Select Loop... </option>
                                @forelse ($GLOBALS['SL']->dataLoops as $plural => $loop)
                                    <option @if (isset($node->nodeRow->NodeResponseSet)
                                            && $node->nodeRow->NodeResponseSet == ('LoopItems::' . $plural))
                                            SELECTED @endif 
                                        value="{{ $plural }}" >{{ $plural }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                    </label>
                </div>
                <div id="spreadTblDefs" class="row mT10 
                    @if ($node->hasResponseOpts()) disBlo @else disNon @endif ">
                    <label class="col-6">
                        <h4 class="m0 slGreenDark">Store Row's List Item ID</h4>
                        <div class="nFld"><select name="nodeDataStoreSprd" 
                            autocomplete="off" class="form-control form-control-lg w100" >
                            {!! $GLOBALS['SL']->fieldsDropdown(isset($node->nodeRow->NodeDataStore) 
                                ? trim($node->nodeRow->NodeDataStore) : '') !!}
                        </select></div>
                    </label>
                    <label class="col-6">
                        <h4 class="mT0">If Row Is Left Empty:</h4> 
                        <div class="nFld"><select name="opts73" id="opts73ID" class="form-control form-control-lg" 
                            autocomplete="off" >
                            <option value="0" @if ($node->nodeRow->NodeOpts%73 > 0) SELECTED @endif 
                                > Delete empty rows' records</option>
                            <option value="73" @if ($node->nodeRow->NodeOpts%73 == 0) SELECTED @endif 
                                > Leave existing records as is</option>
                        </select></div>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="row mB20">
            <div class="col-6">
                <div id="resNotWrdCnt" class=" @if (isset($node->nodeRow->NodeType) && 
                    in_array($node->nodeRow->NodeType, ['Text', 'Long Text'])) disBlo 
                    @else disNon @endif ">
                    <label for="opts31ID" class="mB10"><h4 class="mT0">
                        <input type="checkbox" name="opts31" id="opts31ID" value="31" autocomplete="off" 
                            @if ($node->nodeRow->NodeOpts%31 == 0) CHECKED @endif 
                            > Show Word Count
                    </h4></label>
                    <label for="opts47ID"><h4 class="mT0">
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
                        <h4 class="mT0">Character/Upload Limit</h4>
                        <div class="nFld m0"><input type="number" name="nodeCharLimit" id="nodeCharLimitID" 
                            class="form-control disIn w50" autocomplete="off" 
                            @if (isset($node->nodeRow->NodeCharLimit)) 
                                value="{{ $node->nodeRow->NodeCharLimit }}" 
                            @else value="" @endif ></div>
                    </label>
                </div> */ ?>
            </div>
            <div class="col-6">
                <div id="resCanAuto" class=" @if (isset($node->nodeRow->NodeType) && 
                    in_array($node->nodeRow->NodeType, ['Text'])) disBlo 
                    @else disNon @endif ">
                    <label>
                        <h4 class="mT0">Autofill Suggestions</h4>
                        <div class="nFld m0"><select name="nodeTextSuggest" id="nodeTextSuggestID" 
                            class="form-control w100" autocomplete="off" >
                            <option value="" @if (!isset($node->nodeRow->NodeTextSuggest) 
                                || $node->nodeRow->NodeTextSuggest == '') SELECTED @endif ></option>
                            @forelse ($defs as $def)
                                <option value="{{ $def->DefSubset }}" 
                                @if (isset($node->nodeRow->NodeTextSuggest) 
                                    && $node->nodeRow->NodeTextSuggest == $def->DefSubset) SELECTED @endif 
                                    >{{ $def->DefSubset }}</option>
                            @empty
                            @endforelse
                        </select></div>
                    </label>
                    <div class="mT10 mB10">
                        <label for="opts41ID"><h4 class="mT0">
                            <input type="checkbox" name="opts41" id="opts41ID" value="41" autocomplete="off" 
                                @if ($node->nodeRow->NodeOpts%41 == 0) CHECKED @endif 
                                > Echo Response Edits To Div
                        </h4></label>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="DateOpts" class=" @if (isset($node->nodeRow->NodeType) && 
            in_array($node->nodeRow->NodeType, ['Date', 'Date Picker', 'Date Time'])) disBlo 
            @else disNon @endif ">
            <h4>Time Travelling Restriction</h4>
            <label class="disIn">
                <input type="radio" name="dateOptRestrict" value="0"
                    @if (!isset($node->nodeRow->NodeCharLimit) 
                        || intVal($node->nodeRow->NodeCharLimit) == 0) CHECKED @endif >
                    Any time is fine
            </label>
            <label class="disIn pL20">
                <input type="radio" name="dateOptRestrict" value="-1"
                    @if (isset($node->nodeRow->NodeCharLimit) && intVal($node->nodeRow->NodeCharLimit) < 0) 
                        CHECKED @endif >
                    Must be in the past
            </label>
            <label class="disIn pL20">
                <input type="radio" name="dateOptRestrict" value="1"
                    @if (isset($node->nodeRow->NodeCharLimit) && intVal($node->nodeRow->NodeCharLimit) > 0) 
                        CHECKED @endif >
                    Must be in the future
            </label>
        </div>
        
        <div id="resOpts" class=" @if ($node->hasResponseOpts()) disBlo @else disNon @endif ">
            <h4 id="resOptsLab" class=" @if ($node->isSpreadTbl()) disNon 
                @else disBlo @endif ">Response Options Provided To User:</h4>
            <h4 id="resOptsLabTbl" class=" @if (!$node->isSpreadTbl()) disNon 
                @else disBlo @endif ">Start Table Rows From:</h4>
            {!! $GLOBALS["SL"]->printLoopsDropdowns($node->nodeRow->NodeResponseSet, 'responseList') !!}
            
            <div id="nOptsRadio" class="mT20 @if ($node->nodeType == 'Radio') disBlo @else disNon @endif ">
                <label>
                    <input type="checkbox" name="opts79" id="opts79ID" value="79"
                        @if ($node->nodeRow->NodeOpts%79 == 0) CHECKED @endif autocomplete="off" >
                    After Response Selected, Hide Other Options
                </label>
            </div>
            
            <div class="row pB10 mT20">
                <div class="col-4">
                    <h4 class="mT0">What User Will See</h4><div class="slGrey fPerc80">[Text/HTML]</div>
                </div>
                <div class="col-8">
                    <h4 class="mT0">Value Stored In Database</h4>
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
                    <div class="col-4">
                        <div class="nFld m0"><textarea name="response{{ $r }}" id="response{{ $r }}ID" 
                            type="text" class="form-control" style="height: 65px;" autocomplete="off" 
                            onKeyUp="return checkRes();" @if ($node->hasDefSet()) DISABLED @endif 
                            >{{ $res->NodeResEng }}</textarea></div>
                    </div>
                    <div class="col-8">
                        <div class="nFld m0">
                            <input name="response{{ $r }}Val" id="response{{ $r }}vID" 
                                type="text" value="{{ $res->NodeResValue }}" onKeyUp="return checkRes();" 
                                class="form-control" autocomplete="off" 
                                @if ($node->hasDefSet()) DISABLED @endif >
                        </div>
                        <div class="row mT5">
                            <div class="col-6">
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
                            <div class="col-6">
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
            @if (!$node->hasDefSet())
                @for ($r = sizeof($node->responses); $r < $resLimit; $r++)
                    <div id="r{{ $r }}" class="row pB20 
                        @if ($r == sizeof($node->responses)) disBlo @else disNon @endif ">
                        <div class="col-4">
                            <div class="nFld m0">
                                <textarea name="response{{ $r }}" id="response{{ $r }}ID" 
                                    type="text" class="form-control" style="height: 65px;" 
                                    autocomplete="off" onKeyUp="return checkRes();" 
                                    @if ($node->hasDefSet()) DISABLED @endif ></textarea>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="nFld m0">
                                <input type="text" name="response{{ $r }}Val" id="response{{ $r }}vID" 
                                    value="" onKeyUp="return checkRes();" class="form-control mB5" 
                                    autocomplete="off" @if ($node->hasDefSet()) DISABLED @endif >
                                <div class="row mT5">
                                    <div class="col-6">
                                        <label class="disIn mR20"><input name="response{{ $r }}ShowKids" 
                                            type="checkbox" autocomplete="off" value="1" >
                                             <i title="Children displayed only with certain responses"
                                            class="fa fa-code-fork fa-flip-vertical mL10"></i></label>
                                    </div>
                                    <div class="col-6">
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
    </div> <!-- end Response Options panel -->
</div> <!-- end hasResponse -->