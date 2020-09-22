<!-- resources/views/vendor/survloop/admin/tree/node-edit-widgets.blade.php -->
<div id="hasSurvWidget" class=" @if ($node->isWidget()) disBlo @else disNon @endif ">
    <div class="slCard nodeWrap">
        <h4 class="mT0">Survloop Widget Options</h4>
        <div class="row mB20">
            <div class="col-md-6">
                <label class="nPrompt @if ($GLOBALS['SL']->treeRow->tree_type != 'Page') disNon @endif ">
                    <h4 class="m0 mB5">Related Tree</h4>
                    <div class="nFld"><select name="nodeSurvWidgetTree" id="nodeSurvWidgetTreeID"
                        class="form-control form-control-lg w100 switchTree" autocomplete="off" >
                    {!! $GLOBALS["SL"]->sysTreesDrop($node->nodeRow->node_response_set, 'forms', 'all') !!}
                    </select></div>
                </label>
            </div>
            <div class="col-md-6">
                <label id="widgetRecLimitID" class="nPrompt 
                    @if (in_array($node->nodeType, ['Search Results', 'Search Featured', 'Record Previews'])) 
                        disBlo
                    @else disNon @endif ">
                    <h4 class="m0 mB5">Record Limit</h4>
                    <div class="nFld"><input type="number" name="nodeSurvWidgetLimit" autocomplete="off"
                        id="nodeSurvWidgetLimitID" class="form-control form-control-lg w100" 
                        value="{!! intVal($node->nodeRow->node_char_limit) !!}" ></div>
                </label>
            </div>
        </div>
        <label class="w100"><h4 class="m0 mB5">Data Filter Conditions</h4>
        <input type="text" name="nodeWidgConds" id="nodeWidgCondsID" class="form-control form-control-lg"
            @if (isset($node->extraOpts["conds"])) value="{{ $node->extraOpts["conds"] }}" @endif ></label>
        <div id="widgetGraph" class="mT20 
            @if (in_array($node->nodeType, ['Plot Graph', 'Line Graph'])) disBlo @else disNon @endif ">
            <div class="row">
                <div class="col-md-4">
                    <label class="nPrompt">
                        <h4 class="m0 mB5">Y-Axis</h4>
                        <div class="nFld"><select name="nodeWidgGrphY" id="nodeWidgGrphYID"
                            class="form-control form-control-lg w100" autocomplete="off" >
                        </select></div>
                        <input type="hidden" name="nodeWidgGrphYpresel" id="nodeWidgGrphYIDpresel"
                            @if (isset($node->extraOpts["y-axis"])) 
                                value="{{ $node->extraOpts["y-axis"] }}" @endif >
                    </label>
                    <label class="nPrompt mT10">
                        Y-Axis Label
                        <div class="nFld"><input type="text" name="nodeWidgGrphYlab" id="nodeWidgGrphYlabID"
                            class="form-control form-control-lg w100" autocomplete="off"
                            @if (isset($node->extraOpts["y-axis-lab"])) 
                                value="{{ $node->extraOpts["y-axis-lab"] }}" @endif >
                        </select></div>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="nPrompt">
                        <h4 class="m0 mB5">X-Axis</h4>
                        <div class="nFld"><select name="nodeWidgGrphX" id="nodeWidgGrphXID"
                            class="form-control form-control-lg w100" autocomplete="off" >
                        </select></div>
                        <input type="hidden" name="nodeWidgGrphXpresel" id="nodeWidgGrphXIDpresel"
                            @if (isset($node->extraOpts["x-axis"])) 
                                value="{{ $node->extraOpts["x-axis"] }}" @endif >
                    </label>
                    <label class="nPrompt mT10">
                        X-Axis Label
                        <div class="nFld">
                            <input type="text" name="nodeWidgGrphXlab" id="nodeWidgGrphXlabID"
                                class="form-control form-control-lg w100" autocomplete="off"
                                @if (isset($node->extraOpts["x-axis-lab"])) 
                                    value="{{ $node->extraOpts["x-axis-lab"] }}" @endif >
                        </select></div>
                    </label>
                </div>
            </div>
        </div>
        <div id="widgetBarChart" class="mT20 
            @if (in_array($node->nodeType, ['Bar Graph'])) disBlo @else disNon @endif ">
            <div class="row">
                <div class="col-md-6">
                    <label class="nPrompt">
                        <h4 class="m0 mB5">Value</h4>
                        <div class="nFld"><select name="nodeWidgBarY" id="nodeWidgBarYID"
                            class="form-control form-control-lg w100" autocomplete="off" >
                        </select></div>
                        <input type="hidden" name="nodeWidgBarYpresel" id="nodeWidgBarYIDpresel"
                            @if (isset($node->extraOpts["y-axis"])) 
                                value="{{ $node->extraOpts["y-axis"] }}" @endif >
                    </label>
                    <label class="nPrompt mT10">
                        Value Label
                        <div class="nFld"><input type="text" name="nodeWidgBarYlab" id="nodeWidgBarYlabID"
                            class="form-control form-control-lg w100" autocomplete="off"
                            @if (isset($node->extraOpts["y-axis-lab"])) 
                                value="{{ $node->extraOpts["y-axis-lab"] }}" @endif >
                        </select></div>
                    </label>
                </div>
                <div class="col-md-6">
                    <label class="nPrompt">
                        <h4 class="m0 mB5">Label 1</h4>
                        <div class="nFld"><select name="nodeWidgBarL1" id="nodeWidgBarL1ID"
                            class="form-control form-control-lg w100" autocomplete="off" >
                        </select></div>
                        <input type="hidden" name="nodeWidgBarL1presel" id="nodeWidgBarL1IDpresel"
                            @if (isset($node->extraOpts["lab1"])) 
                                value="{{ $node->extraOpts["lab1"] }}" @endif >
                    </label>
                    <label class="nPrompt mT10">
                        <h4 class="m0 mB5">Label 2</h4>
                        <div class="nFld"><select name="nodeWidgBarL2" id="nodeWidgBarL2ID"
                            class="form-control form-control-lg w100" autocomplete="off" >
                        </select></div>
                        <input type="hidden" name="nodeWidgBarL2presel" id="nodeWidgBarL2IDpresel"
                            @if (isset($node->extraOpts["lab2"])) 
                                value="{{ $node->extraOpts["lab2"] }}" @endif >
                    </label>
                </div>
            </div>
            <div class="row mT20">
                <div class="col-md-6">
                    <label class="nPrompt w100"><h4 class="m0 mB5">Color Starting From Left</h4></label>
                    {!! view('vendor.survloop.forms.inc-color-picker', [
                        'fldName' => 'nodeWidgBarC1',
                        'preSel'  => ((isset($node->extraOpts["clr1"])) ? $node->extraOpts["clr1"] : '')
                    ])->render() !!}
                    <label class="nPrompt w100 mTn5"><h4 class="m0 mB5">Color Ending At Right</h4></label>
                    {!! view('vendor.survloop.forms.inc-color-picker', [
                        'fldName' => 'nodeWidgBarC2',
                        'preSel'  => ((isset($node->extraOpts["clr2"])) ? $node->extraOpts["clr2"] : '')
                    ])->render() !!}
                </div>
                <div class="col-md-1"></div>
                <div class="col-md-5">
                    <label class="nPrompt">
                        Opacity Starting From Left
                        <div class="nFld"><input type="text" name="nodeWidgBarO1" id="nodeWidgBarO1ID"
                            class="form-control w100" autocomplete="off"
                            @if (isset($node->extraOpts["opc1"])) 
                                value="{{ $node->extraOpts["opc1"] }}" @endif >
                        </select></div>
                    </label>
                    <label class="nPrompt mT10">
                        Opacity Ending At Right
                        <div class="nFld"><input type="text" name="nodeWidgBarO2" id="nodeWidgBarO2ID"
                            class="form-control w100" autocomplete="off"
                            @if (isset($node->extraOpts["opc2"])) 
                                value="{{ $node->extraOpts["opc2"] }}" @endif >
                        </select></div>
                    </label>
                </div>
            </div>
        </div>
        <div id="widgetPieChart" class="row mT20 
            @if (in_array($node->nodeType, ['Pie Chart'])) disBlo @else disNon @endif ">
            <div class="col-md-4">
                <label class="nPrompt">
                    <h4 class="m0 mB5">Value</h4>
                    <div class="nFld"><select name="nodeWidgPieY" id="nodeWidgPieYID"
                        class="form-control form-control-lg w100" autocomplete="off" >
                    </select></div>
                    <input type="hidden" name="nodeWidgPieYpresel" id="nodeWidgPieYIDpresel"
                        @if (isset($node->extraOpts["y-axis"])) 
                            value="{{ $node->extraOpts["y-axis"] }}" @endif >
                </label>
                <label class="nPrompt mT10">
                    Value Label
                    <div class="nFld"><input type="text" name="nodeWidgGrphYlab" id="nodeWidgGrphYlabID"
                        class="form-control form-control-lg w100" autocomplete="off"
                        @if (isset($node->extraOpts["y-axis-lab"])) 
                            value="{{ $node->extraOpts["y-axis-lab"] }}" @endif >
                    </select></div>
                </label>
            </div>
            <div class="col-md-4">
                
            </div>
            <div class="col-md-4">
                
            </div>
        </div>
        <div id="widgetMap" class="row mT20 @if (in_array($node->nodeType, ['Map'])) disBlo
            @else disNon @endif ">
            <div class="col-md-4">
            
            </div>
            <div class="col-md-4">
                
            </div>
            <div class="col-md-4">
                
            </div>
        </div>
        <label class="nPrompt">
            Graph/Map Height
            <div class="nFld"><input type="text" name="nodeWidgHgt" id="nodeWidgHgtID"
                class="form-control w33" autocomplete="off"
                @if (isset($node->extraOpts["hgt"])) 
                    value="{{ $node->extraOpts["hgt"] }}" @else value="420" @endif >
            </select></div>
        </label>
        <div id="widgetPrePost" class="row mT20 @if ($GLOBALS['SL']->treeRow->tree_type != 'Page'
            || $node->nodeType == 'Send Email') disNon @else disBlo @endif ">
            <div class="col-md-6">
                <label class="nPrompt">
                    <h4 class="m0 mB5">Pre-Widget</h4>
                    <div class="nFld"><textarea name="nodeSurvWidgetPre" id="nodeSurvWidgetPreID" 
                        class="form-control w100" style="height: 150px; font-family: Courier New;" autocomplete="off" 
                        >@if (isset($node->nodeRow->node_prompt_text) 
                            ){!! $node->nodeRow->node_prompt_text !!}@endif</textarea></div>
                </label>
            </div>
            <div class="col-md-6">
                <label class="nPrompt">
                    <h4 class="m0 mB5">Post-Widget</h4>
                    <div class="nFld"><textarea name="nodeSurvWidgetPost" id="nodeSurvWidgetPostID" 
                        class="form-control w100" style="height: 150px; font-family: Courier New;" autocomplete="off" 
                        >@if (isset($node->nodeRow->node_prompt_after) 
                            ){!! $node->nodeRow->node_prompt_after !!}@endif</textarea></div>
                </label>
            </div>
        </div>
    </div>
</div>