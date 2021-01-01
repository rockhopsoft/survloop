<!-- resources/views/vendor/survloop/admin/tree/node-edit-page.blade.php -->

<div id="hasPage" class=" @if ($node->isPage() || $node->isLoopRoot()) disBlo @else disNon @endif ">
    <div class="slCard nodeWrap">
        <h4 class="mT0">Page Settings & SEO</h4>
        <div id="pageLoopDesc" class="mB20 
            @if ($node->isLoopRoot()) disBlo @else disNon @endif "><i>
            If a Loop repeats child Page(s) more than one, then the Loop has its own Page too.
            This root Page provides navigation for the multiple copies of the Loop's descendants.
        </i></div>
        
        {!! view('vendor.survloop.admin.seo-meta-editor', [
            "currMeta" => $currMeta
        ])->render() !!}
        
        <div id="hasPageOpts" class="nFld @if ($node->isPage()) disBlo @else disNon @endif ">
            <div class="row">
                <div class="col-md-6">
                
                @if ($GLOBALS['SL']->treeRow->tree_type == 'Page')
                    <label class="disBlo mT20">
                        <input type="checkbox" name="adminPage" id="adminPageID" value="3" 
                            @if ($GLOBALS['SL']->treeRow->tree_opts%3 == 0) CHECKED @endif
                            autocomplete="off" class="mR5">
                        <i class="fa fa-eye mR5" aria-hidden="true"></i> Admin-Only Page
                    </label>
                    <label class="disBlo mT20">
                        <input type="checkbox" name="staffPage" id="staffPageID" value="43" 
                            @if ($GLOBALS['SL']->treeRow->tree_opts%43 == 0) CHECKED @endif 
                            autocomplete="off" class="mR5">
                        <i class="fa fa-key mR5" aria-hidden="true"></i> Staff Page
                    </label>
                    @if ($GLOBALS["SL"]->sysHas('partners'))
                        <label class="disBlo mT20">
                            <input type="checkbox" name="partnPage" id="partnPageID" value="41" 
                                @if ($GLOBALS['SL']->treeRow->tree_opts%41 == 0) CHECKED @endif 
                                autocomplete="off" class="mR5">
                            <i class="fa fa-university mR5" aria-hidden="true"></i> Partner Page
                        </label>
                    @endif
                    @if ($GLOBALS["SL"]->sysHas('volunteers'))
                        <label class="disBlo mT20">
                            <input type="checkbox" name="volunPage" id="volunPageID" value="17" 
                                @if ($GLOBALS['SL']->treeRow->tree_opts%17 == 0) CHECKED @endif 
                                autocomplete="off" class="mR5">
                            <i class="fa fa-hand-rock-o mR5" aria-hidden="true"></i> Volunteer Page
                        </label>
                    @endif
                @endif
                
                </div>
                <div class="col-md-6">
                
                @if ($GLOBALS['SL']->treeRow->tree_type == 'Page')
                    <label class="disBlo mT20">
                        <input type="checkbox" name="homepage" id="homepageID" value="7" 
                            @if ($GLOBALS['SL']->treeRow->tree_opts%7 == 0) CHECKED @endif 
                            autocomplete="off" class="mR5">
                        <i class="fa fa-star mR5" aria-hidden="true"></i> Website Homepage
                    </label>
                    <label class="disBlo mT20">
                        <input type="checkbox" name="reportPage" id="reportPageID" value="13" 
                            @if ($GLOBALS['SL']->treeRow->tree_opts%13 == 0) CHECKED @endif 
                            autocomplete="off" class="mR5" onClick="chkPageForTree();">
                        <i class="fa fa-list-alt mR5" aria-hidden="true"></i> Survey Report
                    </label>
                    <label class="disBlo mT20">
                        <input type="checkbox" name="searchPage" id="searchPageID" value="31" 
                            @if ($GLOBALS['SL']->treeRow->tree_opts%31 == 0) CHECKED @endif 
                            autocomplete="off" class="mR5" onClick="chkPageForTree();">
                        <i class="fa fa-search mR5" aria-hidden="true"></i> Survey Search Results
                    </label>
                    <select name="reportPageTree" id="reportPageTreeID" 
                        class="form-control mT5" autocomplete="off">
                        {!! $GLOBALS["SL"]->allTreeDropOpts($node->nodeRow->node_response_set) !!}
                    </select>
                @else
                    <label class="disBlo red mT20">
                        <input type="checkbox" name="opts29" id="opts29ID" value="29" 
                            @if ($node->nodeRow->node_opts%29 == 0) CHECKED @endif 
                            autocomplete="off" class="mR5">
                        <i class="fa fa-sign-out mR5" aria-hidden="true"></i> Exit Page 
                        <div class="fPerc80 slGrey"><i>(no Next button)</i></div>
                    </label>
                    <label class="disBlo mT20">
                        <input type="checkbox" name="opts59" id="opts59ID" value="59" 
                            @if ($node->nodeRow->node_opts%59 == 0) CHECKED @endif 
                            autocomplete="off" class="mR5">
                        Hide Progress Bar
                    </label>
                @endif
                    
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                
                @if ($GLOBALS['SL']->treeRow->tree_type == 'Page')
                    <label class="disBlo mT20">
                        <input type="checkbox" name="noCache" id="noCacheID" value="29" 
                            @if ($GLOBALS['SL']->treeRow->tree_opts%29 == 0) CHECKED @endif 
                            autocomplete="off" class="mR5">
                        No Caching This Page
                    </label>
                    <label class="disBlo mT20">
                        <input type="checkbox" name="pageBg" id="pageBgID" value="67"
                            @if ($GLOBALS['SL']->treeRow->tree_opts%67 == 0) CHECKED @endif autocomplete="off" class="mR5">
                        Whole page has alternate background
                    </label>
                    <label class="disBlo mT20">
                        <input type="checkbox" name="pageFadeIn" id="pageFadeInID" value="71"
                            @if ($GLOBALS['SL']->treeRow->tree_opts%71 == 0) CHECKED @endif autocomplete="off" class="mR5">
                        Whole page fades in after load
                    </label>
                @endif

                    <div class="nFld w100">
                        <label class="w100">
                            Focus Field: 
                            <input type="number" name="pageFocusField" autocomplete="off" 
                                value="{{ $node->nodeRow->node_char_limit }}" 
                                class="disIn form-control mR10" style="width: 60px;" ><br />
                            <i class="fPerc80 slGrey">
                            (0 is default, -1 overrides no focus, otherwise set this a Node ID)</i>
                        </label>
                    </div>
                
                </div>
            </div>
        </div>
    </div>
</div>