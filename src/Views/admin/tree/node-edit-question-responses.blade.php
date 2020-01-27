<!-- resources/views/vendor/survloop/admin/tree/node-edit-question-responses.blade.php -->
<div id="r{{ $r }}" 
    class=" @if ($r <= sizeof($node->responses)) disBlo @else disNon @endif ">
    <div class="row">
        <div class="col-2 pT5 slGrey">
            <nobr>On-Screen</nobr>
        </div>
        <div class="col-10">
            <div class="nFld m0">
                <textarea name="response{{ $r }}" id="response{{ $r }}ID" 
                    type="text" class="form-control" style="height: 65px;" 
                    autocomplete="off" onKeyUp="return checkRes();" 
                    @if ($node->hasDefSet()) DISABLED @endif 
                    >{{ $resEng }}</textarea>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-2 pT5 slGrey">
            <nobr>Stored As</nobr>
        </div>
        <div class="col-10">
            <div class="nFld m0">
                <input name="response{{ $r }}Val" id="response{{ $r }}vID" 
                    type="text" value="{{ $resVal }}" onKeyUp="return checkRes();" 
                    class="form-control" autocomplete="off" 
                    @if ($node->hasDefSet()) DISABLED @endif >
            </div>
            <div class="row mT5 pB20">
                <div class="col-6">
                    <label class="mL5">
                        <input type="checkbox" value="1" 
                            name="response{{ $r }}ShowKids" id="r{{ $r }}showKID"
                            class="showKidBox" autocomplete="off"
                            @if ($node->indexShowsKid($r)) CHECKED @endif >
                            <i title="Children displayed only with certain responses"
                            class="fa fa-code-fork fa-flip-vertical mL5 fPerc133"></i>
                    </label>
                    <div id="kidFork{{ $r }}" class="mL5 
                        @if ($node->indexShowsKid($r)) disIn @else disNon @endif ">
                        @if (isset($childNodes) && sizeof($childNodes) > 0)
                            @if (sizeof($childNodes) == 1)
                                #{{ $childNodes[0]->node_id }}
                                <input type="hidden" name="kidForkSel{{ $r }}" 
                                    value="{{ $childNodes[0]->node_id }}">
                            @else
                                <select name="kidForkSel{{ $r }}" autocomplete="off"
                                    class="form-control input-xs disIn" 
                                    style="width: 70px;">
                                @foreach ($childNodes as $k => $kidNode)
                                    <option value="{{ $kidNode->node_id }}"
                                    @if ($node->indexShowsKidNode($r) == $kidNode->node_id) 
                                    SELECTED @endif >#{{ $kidNode->node_id }}</option>
                                @endforeach
                                </select>
                            @endif
                        @else
                            <input type="hidden" name="kidForkSel{{ $r }}" value="1000000000">
                        @endif
                    </div>
                </div>
                <div class="col-6">
                    <label id="resMutEx{{ $r }}" class="mL5 
                        @if (isset($node->nodeRow->node_type) && $node->nodeRow->node_type == 'Checkbox')) disBlo 
                        @else disNon @endif "
                        ><nobr><input name="response{{ $r }}MutEx" 
                            type="checkbox" value="1" autocomplete="off" 
                            @if ($node->indexMutEx($r)) CHECKED @endif >
                            <i class="fa fa-circle-o mL10 mR0 fPerc133"></i> 
                            <i class="fa fa-circle mLn5 fPerc133"></i></nobr>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>