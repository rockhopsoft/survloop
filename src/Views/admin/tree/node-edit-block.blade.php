<!-- resources/views/vendor/survloop/admin/tree/node-edit-block.blade.php -->
<div id="isPageBlock" class=" @if ($node->isPageBlock()) disBlo @else disNon @endif ">
    <div class="slCard nodeWrap">
        <h4 class="mT0">Style Options</h4>
        <div id="pageBlock" class="slBg slTxt p20">
            <div class="row">
                <div class="col-md-4">
                    <select name="blockAlign" id="blockAlignID" autocomplete="off"
                        class="form-control form-control-lg mB20">
                        <option value="left" @if (!isset($node->colors["blockAlign"]) 
                            || $node->colors["blockAlign"] == 'left') SELECTED @endif 
                            >Align Left</option>
                        <option value="center" @if (isset($node->colors["blockAlign"]) 
                            && $node->colors["blockAlign"] == 'center') SELECTED @endif 
                            >Align Center</option>
                        <option value="right" @if (isset($node->colors["blockAlign"]) 
                            && $node->colors["blockAlign"] == 'right') SELECTED @endif 
                            >Align Right</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="blockHeight" id="blockHeightID" autocomplete="off"
                        class="form-control form-control-lg mB20">
                        <option value="auto" @if (!isset($node->colors["blockHeight"]) 
                            || $node->colors["blockHeight"] == 'auto') SELECTED @endif 
                            >Auto Height, Default Padding</option>
                        <option value="h100" @if (isset($node->colors["blockHeight"]) 
                            && $node->colors["blockHeight"] == 'h100') SELECTED @endif 
                            >Full Screen Height</option>
                        <option value="h75" @if (isset($node->colors["blockHeight"]) 
                            && $node->colors["blockHeight"] == 'h75') SELECTED @endif 
                            >75% Screen Height</option>
                        <option value="h66" @if (isset($node->colors["blockHeight"]) 
                            && $node->colors["blockHeight"] == 'h66') SELECTED @endif 
                            >66% Screen Height</option>
                        <option value="h50" @if (isset($node->colors["blockHeight"]) 
                            && $node->colors["blockHeight"] == 'h50') SELECTED @endif 
                            >50% Screen Height</option>
                        <option value="h33" @if (isset($node->colors["blockHeight"]) 
                            && $node->colors["blockHeight"] == 'h33') SELECTED @endif 
                            >33% Screen Height</option>
                        <option value="h25" @if (isset($node->colors["blockHeight"]) 
                            && $node->colors["blockHeight"] == 'h25') SELECTED @endif 
                            >25% Screen Height</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class=" @if ($GLOBALS['SL']->treeRow->tree_type == 'Page' 
                        /* && $GLOBALS['SL']->REQ->has('parent') 
                        && $GLOBALS['SL']->REQ->get('parent') == $GLOBALS['SL']->treeRow->tree_root */) disBlo
                        @else disNon @endif ">
                        <select name="opts67" id="opts67ID" autocomplete="off"
                            class="form-control form-control-lg mB20">
                            <option value="1" 
                                @if ($node->nodeRow->node_opts%67 > 0) SELECTED @endif 
                                >Full Content Width</option>
                            <option value="67" 
                                @if ($node->nodeRow->node_opts%67 == 0) SELECTED @endif 
                                >Skinny Content Width</option>
                        </select>
                    </div>
                </div>
            </div>
            <label class="mT10 mB10">
                <input type="checkbox" name="opts71" id="opts71ID" value="71" 
                    autocomplete="off" onClick="return checkPageBlock();" 
                    @if ($node->nodeRow->node_opts%71 == 0) CHECKED @endif
                    > Background
                </label>
            <div id="pageBlockOpts" class="pT5 
                @if ($node->nodeRow->node_opts%71 == 0) disBlo @else disNon @endif ">
                <div class="row">
                    <div class="col-md-5">
                        <label for="blockBGID"><h4 class="mT0">Background Color</h4></label>
                        {!! view('vendor.survloop.forms.inc-color-picker', [
                            'fldName' => 'blockBG',
                            'preSel'  => ((isset($node->colors["blockBG"])) 
                                ? $node->colors["blockBG"] : '#000')
                        ])->render() !!}
                        <label for="blockTextID"><h4 class="mT0">Text Color</h4></label>
                        {!! view('vendor.survloop.forms.inc-color-picker', [
                            'fldName' => 'blockText',
                            'preSel'  => ((isset($node->colors["blockText"])) 
                                ? $node->colors["blockText"] : '#DDD')
                        ])->render() !!}
                        <label for="blockLinkID"><h4 id="blockLinkh4" class="m0 slTxt"
                            >Link Color</h4></label>
                        {!! view('vendor.survloop.forms.inc-color-picker', [
                            'fldName' => 'blockLink',
                            'preSel'  => ((isset($node->colors["blockLink"])) 
                                ? $node->colors["blockLink"] : '#FFF')
                        ])->render() !!}
                    </div>
                    <div class="col-md-1"></div>
                    <div class="col-md-6">
                        <label for="blockImgID"><h4 class="mT0">Background Image</h4></label>
                        <input type="text" class="form-control form-control-lg w100 mB20 mR20"
                            id="blockImgID" name="blockImg" value="{{ 
                            ((isset($node->colors['blockImg'])) ? $node->colors['blockImg'] : '')
                            }}">
                        <div class="disIn mL10 mR10"><label class="disIn">
                            <input type="radio" name="blockImgType" id="blockImgTypeA" 
                                value="w100" class="mR5" autocomplete="off" 
                                onClick="return checkPageBlock();"
                                @if (!isset($node->colors["blockImgType"]) 
                                    || $node->colors["blockImgType"] == 'w100') CHECKED @endif 
                                    > Full-Width Image
                        </label></div>
                        <div class="disIn mL10 mR10"><label class="disIn">
                            <input type="radio" name="blockImgType" id="blockImgTypeB" 
                                value="tiles" class="mR5" onClick="return checkPageBlock();"
                                @if (isset($node->colors["blockImgType"]) 
                                    && $node->colors["blockImgType"] == 'tiles') CHECKED @endif 
                                    autocomplete="off" > Tiled Image
                        </label></div>
                        <div class="p5"></div>
                        <div class="disIn mL10 mR10"><label class="disIn">
                            <input type="radio" name="blockImgFix" id="blockImgFixN" 
                                value="N" class="mR5" onClick="return checkPageBlock();"
                                @if (!isset($node->colors["blockImgFix"]) 
                                    || $node->colors["blockImgFix"] == 'N') CHECKED @endif 
                                    autocomplete="off" > Normal
                        </label></div>
                        <div class="disIn mL10 mR10"><label class="disIn">
                            <input type="radio" name="blockImgFix" id="blockImgFixY" 
                                value="Y" class="mR5" onClick="return checkPageBlock();"
                                @if (isset($node->colors["blockImgFix"]) 
                                    && $node->colors["blockImgFix"] == 'Y') CHECKED @endif 
                                    autocomplete="off" > Fixed Position
                        </label></div>
                        <div class="disIn mL10 mR10"><label class="disIn">
                            <input type="radio" name="blockImgFix" id="blockImgFixP" 
                                value="P" class="mR5" onClick="return checkPageBlock();"
                                @if (isset($node->colors["blockImgFix"]) 
                                    && $node->colors["blockImgFix"] == 'P') CHECKED @endif 
                                    autocomplete="off" > Parrallax Position
                        </label></div>
                    </div>
                </div>
            </div>
            <div>
                <label class="mT10 mB10"><input type="checkbox" 
                    name="opts89" id="opts89ID" value="89" 
                    autocomplete="off" onClick="return checkPageBlock();" 
                    @if ($node->nodeRow->node_opts%89 == 0) CHECKED @endif
                    > Card Wrapper
                </label>
            </div>
            <div>
                <label class="mT10 mB10">
                    <input type="checkbox" name="opts97" id="opts97ID" value="97" 
                        autocomplete="off" onClick="return checkPageBlock();" 
                        @if ($node->nodeRow->node_opts%97 == 0) CHECKED @endif
                        > Deferred Load of Node
                </label>
            </div>
            <div id="nodeCachingOpt" class="
                @if ($node->nodeRow->node_opts%97 == 0) disBlo @else disNon @endif ">
                <label class="mT10 mB10">
                    <input type="checkbox" name="opts103" id="opts103ID" value="103" 
                        autocomplete="off" onClick="return checkPageBlock();" 
                        @if ($node->nodeRow->node_opts%103 == 0) CHECKED @endif
                        > No Caching This Node
                </label>
            </div>
        </div>
    </div>
</div>