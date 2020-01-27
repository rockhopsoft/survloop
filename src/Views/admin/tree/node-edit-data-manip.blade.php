<!-- resources/views/vendor/survloop/admin/tree/node-edit-data-manip.blade.php -->
<div id="hasDataManip" class=" @if ($node->isDataManip()) disBlo @else disNon @endif ">
    <div class="slCard nodeWrap">
        <h4 class="mT0"><i class="fa fa-database"></i> Data Manipulation Tools</h4>
        <small class="slGrey">
            Moving forward with this node conditionally visible, it will run one of these tasks. 
            Children of this node link to it by setting their data subset to this helper table. 
            New records are automatically linked to core record and/or loop's set item.
        </small>
        <div class="radio">
            <label>
                <input type="radio" name="dataManipType" id="dataManipTypeNew" value="New" 
                    onClick="return checkDataManipFlds();" autocomplete="off" 
                    @if (isset($node->nodeRow->node_type) 
                        && $node->nodeRow->node_type == 'Data Manip: New') CHECKED @endif >
                    <h4 class="disIn pL5 mT0">Create New Record in 
                    <span class="slGreenDark">Data Family</span></h4>
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" name="dataManipType" id="dataManipTypeUpdate" value="Update" 
                    onClick="return checkDataManipFlds();" autocomplete="off" 
                    @if (isset($node->nodeRow->node_type) 
                        && $node->nodeRow->node_type == 'Data Manip: Update') CHECKED @endif >
                    <h4 class="disIn pL5 mT0">Update Family Record in 
                    <span class="slGreenDark">Data Family</span></h4>
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" name="dataManipType" id="dataManipTypeWrap" value="Wrap" 
                    onClick="return checkDataManipFlds();" autocomplete="off" 
                    @if (isset($node->nodeRow->node_type) 
                        && $node->nodeRow->node_type == 'Data Manip: Wrap') CHECKED @endif >
                    <h4 class="disIn pL5 mT0">Just Wrap Children in 
                    <span class="slGreenDark">Data Family</span></h4>
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" name="dataManipType" id="dataManipTypeCloseSess" value="Close Sess"
                    onClick="return checkDataManipFlds();" autocomplete="off" 
                    @if (isset($node->nodeRow->node_type) 
                        && $node->nodeRow->node_type == 'Data Manip: Close Sess') CHECKED @endif >
                    <h4 class="disIn pL5 mB0 mT0 slGrey">End User Session for Form Tree</h4>
                    <div id="manipCloseSess" class=" @if (isset($node->nodeRow->node_type) 
                        && $node->nodeRow->node_type == 'Data Manip: Close Sess') disBlo 
                        @else disNon @endif "><select name="dataManipCloseSessTree" class="form-control"
                            style="width: 250px;" autocomplete="off" >
                            @forelse ($treeList as $t)
                                <option value="{{ $t->tree_id }}" 
                                    @if ($t->tree_id == $node->nodeRow->node_response_set) SELECTED @endif
                                    >{{ $t->tree_name }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>
            </label>
        </div>
        <div id="dataNewRecord" class=" @if (isset($node->nodeRow->node_type) 
            && $node->nodeRow->node_type == 'Data Manip: Close Sess') disNon @else disBlo @endif ">
            <div class="row pT5">
                <div class="col-5">
                    <label class="w100"><h4 class="mT0">Set Record Field</h4></label>
                </div>
                <div class="col-1 taC"></div>
                <div class="col-3">
                    <h4 class="mT0">Custom Value</h4>
                </div>
                <div class="col-3">
                    <h4 class="mT0">Definitions</h4>
                </div>
            </div>
            <div class="row pT5 pB10">
                <div class="col-5">
                    <label class="w100">
                        <div class="nFld mT0">
                            <select name="manipMoreStore" id="manipMoreStoreID"
                            class="form-control form-control-lg" autocomplete="off" onClick="return checkData();" >
                            {!! $GLOBALS['SL']->fieldsDropdown((isset($node->nodeRow->node_data_store)) 
                                ? trim($node->nodeRow->node_data_store) : '') !!}
                            </select>
                        </div>
                    </label>
                </div>
                <div class="col-1 taC">
                    <div class="mTn20"><h1 class="m0 slGreenDark">=</h1></div>
                </div>
                <div class="col-2">
                    <div class="nFld mT0"><input type="text" name="manipMoreVal" 
                        class="form-control form-control-lg" @if (isset($node->nodeRow->node_default)) 
                            value="{{ $node->nodeRow->node_default }}" @endif >
                    </div>
                </div>
                <div class="col-1 taC">
                    <h4 class="mT10 slGreenDark">or</h4>
                </div>
                <div class="col-3">
                    <div class="nFld mT0"><select name="manipMoreSet" class="form-control form-control-lg" 
                        autocomplete="off" >
                        {!! $GLOBALS['SL']->allDefsDropdown((isset($node->nodeRow->node_response_set)) 
                            ? $node->nodeRow->node_response_set : '') !!}
                    </select></div>
                </div>
            </div>
            
            @for ($i = 0; $i < $resLimit; $i++)
                <div id="dataManipFld{{ $i }}" 
                    class=" @if (isset($node->dataManips[$i])) disBlo @else disNon @endif ">
                    <div class="row mT5 mB10">
                        <div class="col-5">
                            <div class="nFld mT0">
                                <select name="manipMore{{ $i }}Store" id="manipMore{{ $i }}StoreID" 
                                    class="form-control form-control-lg" autocomplete="off" 
                                    onClick="return checkData();" >
                                @if (isset($node->dataManips[$i]) && isset($node->dataManips[$i]->node_data_store))
                                    {!! $GLOBALS['SL']->fieldsDropdown($node->dataManips[$i]->node_data_store) !!}
                                @else {!! $GLOBALS['SL']->fieldsDropdown() !!} @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-1 taC">
                            <div class="mTn20"><h1 class="m0 slGreenDark">=</h1></div>
                        </div>
                        <div class="col-2">
                            <div class="nFld mT0">
                                <input type="text" name="manipMore{{ $i }}Val" 
                                    class="form-control form-control-lg" @if (isset($node->dataManips[$i]) 
                                        && isset($node->dataManips[$i]->node_default))
                                        value="{!! $node->dataManips[$i]->node_default !!}"
                                    @else value="" @endif >
                            </div>
                        </div>
                        <div class="col-1 taC">
                            <h4 class="mT10 slGreenDark">or</h4>
                        </div>
                        <div class="col-3">
                            <div class="nFld mT0">
                                <select name="manipMore{{ $i }}Set" 
                                    class="form-control form-control-lg" autocomplete="off" >
                                    @if (isset($node->dataManips[$i]) && isset($node->dataManips[$i]->node_response_set))
                                        {!! $GLOBALS['SL']->allDefsDropdown($node->dataManips[$i]->node_response_set) !!}
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