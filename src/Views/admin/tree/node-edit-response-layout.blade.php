<!-- resources/views/vendor/survloop/admin/tree/node-edit-response-layout.blade.php -->
<div id="hasResponseLayout" class=" 
    @if ($node->isSpecial() || $node->isWidget() || $node->isLayout()) disNon @else disBlo @endif ">
    <div class="slCard nodeWrap">
        <h4 class="mT0">Node Layout Options</h4>
        <div class="nFld mT0">
            <select type="radio" name="changeResponseMobile" id="changeResponseMobileID" autocomplete="off"
                onChange="changeResponseMobileType();" class="form-control form-control-lg" >
                <option value="mobile" @if ($node->nodeRow->node_opts%2 > 0) SELECTED @endif 
                    > Mobile default</option>
                <option value="desktop" @if ($node->nodeRow->node_opts%2 == 0) SELECTED @endif
                    > Customize</option>
            </select>
        </div>
        <div id="changeResListType" class="disNon">
            <i class="slGrey">&uarr; Please click "Save" below to apply these changes. &rarr;</i>
        </div>

        <div id="responseCheckOpts" 
            class=" @if ($node->nodeRow->node_opts%2 == 0) disBlo @else disNon @endif ">
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
                        @if ($node->nodeRow->node_opts%61 == 0) CHECKED @endif 
                        > Responses In Columns
                </label>
            </div>
        </div>
    
        <div id="taggerOpts" class="p10 mB10 
            @if (in_array($node->nodeRow->node_type, ['Drop Down', 'U.S. States'])) disBlo @else disNon @endif ">
            <b>Empty/Non-Response Option:</b> 
            <div class="nFld"><input type="text" name="dropDownSuggest" class="form-control" 
                @if (isset($node->nodeRow->node_text_suggest)) value="{{ $node->nodeRow->node_text_suggest }}"
                @endif ></div>
            
            <label for="opts53ID" class="mT20">
                <input type="checkbox" name="opts53" id="opts53ID" value="53" autocomplete="off" 
                    @if ($node->isDropdownTagger()) CHECKED @endif 
                    > Selecting Responses Adds One Or More Tags
            </label>
        </div>
        <div class="checkbox m10">
            <label for="opts37ID">
                <input type="checkbox" name="opts37" id="opts37ID" value="37" autocomplete="off" 
                    @if ($node->nodeRow->node_opts%37 == 0) CHECKED @endif class="mR10" > Wrap node in 
                    <a href="http://getbootstrap.com/examples/jumbotron-narrow/" target="_blank">jumbotron</a>
            </label>
        </div>
        <div id="responseReqOpts" 
            class="m10 @if ($node->isRequired()) disBlo @else disNon @endif ">
            <div class="checkbox">
                <label for="opts13ID">
                    <input type="checkbox" name="opts13" id="opts13ID" value="13" autocomplete="off" 
                    @if ($node->nodeRow->node_opts%13 == 0) CHECKED @endif 
                    > <span class="red">*Required</span> displayed on it's own separate line
                </label>
            </div>
        </div>
    </div>
</div> <!-- end hasResponseLayout -->