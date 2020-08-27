<!-- resources/views/vendor/survloop/admin/tree/node-edit-layout.blade.php -->
<div id="hasLayout" class=" @if ($node->isLayout()) disBlo @else disNon @endif ">
    <div class="slCard nodeWrap">
        <h4 class="mT0">Layout Options</h4>
        <div class="row mB20">
            <div class="col-md-6">
                <label class="nPrompt">
                    <h4 class="m0 mB5">Layout Node Type</h4>
                    <div class="nFld"><select name="nodeLayoutType" id="nodeLayoutTypeID"
                        class="form-control form-control-lg w100" autocomplete="off" 
                        onChange="return changeLayoutType();" >
                        <option value="Page Block" 
                            @if ($node->nodeType == 'Page Block' 
                                || trim($node->nodeType) == '')
                                SELECTED 
                            @endif >Just A Page Block</option>
                        <option value="Layout Row" 
                            @if ($node->nodeType == 'Layout Row') 
                                SELECTED
                            @endif >Layout Row (To Contain Multiple Columns)</option>
                        <option value="Layout Column" 
                            @if ($node->nodeType == 'Layout Column') 
                                SELECTED 
                            @endif >Layout Column (Contained by Layout Row)</option>
                        <option value="Gallery Slider" 
                            @if ($node->nodeType == 'Gallery Slider') 
                                SELECTED 
                            @endif >Gallery Slider (Scrolls Between Child Nodes)</option>
                    @if (isset($parentNode->node_type) 
                        && in_array($parentNode->node_type, ['Checkbox']))
                        <option value="Layout Sub-Response" 
                            @if ($node->nodeType == 'Layout Sub-Response') 
                                SELECTED 
                            @endif >Layout Sub-Response
                            (Children appear between parent's responses)</option>
                    @endif
                    </select></div>
                </label>
            </div>
            <div class="col-md-6">
                <label id="layoutSizeRow" class="nPrompt 
                    @if ($node->nodeType == 'Layout Row') disBlo @else disNon @endif ">
                    <h4 class="m0 mB5"># of Columns in Row</h4>
                    <div class="nFld">
                        <select name="nodeLayoutLimitRow" id="nodeLayoutLimitRowID" 
                            class="form-control form-control-lg w100" autocomplete="off">
                        @for ($i = 1; $i < 13; $i++)
                            <option value="{{ $i }}" @if (isset($node->nodeRow->node_char_limit) 
                                && $i ==intVal($node->nodeRow->node_char_limit)) SELECTED @endif 
                                >{{ $i }}</option>
                        @endfor
                        </select>
                    </div>
                </label>
                <label id="layoutSizeCol" class="nPrompt 
                    @if ($node->nodeType == 'Layout Column') disBlo @else disNon @endif ">
                    <h4 class="m0 mB5">Column Width (in 12<sup>th</sup>s)</h4>
                    <div class="nFld">
                        <input type="number" name="nodeLayoutLimitCol" id="nodeLayoutLimitColID"
                            class="form-control form-control-lg w100" autocomplete="off" 
                            value="{!! ((isset($node->nodeRow->node_char_limit)) 
                                ? intVal($node->nodeRow->node_char_limit) : '') !!}" >
                    </div>
                    (4 columns could have width 3, 2 columns could have 6 each, etc.)
                </label>
            </div>
        </div>
    </div>
</div>

<div id="hasDataPrint" class=" @if ($node->isDataPrint()) disBlo @else disNon @endif ">
    <div class="slCard nodeWrap">
        <h4 class="mT0">Data Printout Options</h4>
        <div class="row mB20">
            <div class="col-md-6">
                <label id="dataPrintPull" class="w100 
                    @if (in_array($node->nodeType, ['Data Print Block', 'Data Print Columns', 
                        'Print Vert Progress'])) disNon @else disBlo @endif ">
                    <h4 class="m0 slGreenDark">Pull User Response</h4>
                    <div class="nFld m0"><select name="nodeDataPull" class="form-control form-control-lg w100" 
                        autocomplete="off" >
                        {!! $GLOBALS['SL']->fieldsDropdown(isset($node->nodeRow->node_data_store) 
                            ? trim($node->nodeRow->node_data_store) : '') !!}
                    </select></div>
                </label>
                <div class="p10"></div>
                <hr><i>Data Block Example:</i>
                <div class="slReport">
                    <div class="reportBlock">
                        <div class="reportSectHead">Data Block Title</div>
                        <div class="row row2">
                            <div class="col-6"><span>Question</span></div>
                            <div class="col-6">Response</div>
                        </div><div class="row">
                            <div class="col-6"><span>Question</span></div>
                            <div class="col-6">Response</div>
                        </div><div class="row row2">
                            <div class="col-12"><span>Question:</span> Longer response, which is 
                                automatically get a row which better fit a paragaph of text.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <label id="dataPrintTitle" class="w100 
                    @if (in_array($node->nodeType, ['Data Print Block', 'Data Print Columns', 
                        'Print Vert Progress'])) disBlo @else disNon @endif ">
                    <h4 class="m0 slGreenDark">Title of Data Block</h4>
                    <div class="nFld m0"><input type="text" name="nodeDataBlcTitle" autocomplete="off" 
                        class="form-control form-control-lg w100" @if (isset($node->nodeRow->node_prompt_text)) 
                            value="{{ trim($node->nodeRow->node_prompt_text) }}" @endif >
                    </div>
                </label>
                <div id="dataPrintConds" class="
                    @if ($node->nodeType == 'Data Print Row') disBlo @else disNon @endif ">
                    <label class="w100">
                        <h4 class="m0 slGreenDark">Hide Row If Response Is</h4>
                        <div class="nFld m0">
                            <input type="text" name="nodeDataHideIf" autocomplete="off" class="
                                form-control form-control-lg w100" @if (isset($node->nodeRow->node_default)) 
                                value="{{ trim($node->nodeRow->node_default) }}" @endif >
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>