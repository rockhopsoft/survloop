<!-- resources/views/vendor/survloop/admin/tree/node-edit-page.blade.php -->

<div id="hasPage" class=" @if ($node->isPage() || $node->isLoopRoot()) disBlo @else disNon @endif ">
    <div class="slCard nodeWrap">
        <h4 class="mT0">Page Settings & SEO</h4>
        <div id="pageLoopDesc" class="mB20 @if ($node->isLoopRoot()) disBlo @else disNon @endif "><i>
            If a Loop repeats child Page(s) more than one, then the Loop has its own Page too.
            This root Page provides navigation for the multiple copies of the Loop's descendants.
        </i></div>
        
        {!! view('vendor.survloop.admin.seo-meta-editor', [ "currMeta" => $currMeta ])->render() !!}
        
        <div id="hasPageOpts" class="nFld @if ($node->isPage()) disBlo @else disNon @endif "><div class="row">
            <div class="col-md-4">
            
            @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
                <label class="disBlo mT20">
                    <input type="checkbox" name="adminPage" id="adminPageID" value="3" 
                    @if ($GLOBALS['SL']->treeRow->TreeOpts%3 == 0) CHECKED @endif autocomplete="off">
                    <i class="fa fa-eye mL10 mR5" aria-hidden="true"></i> Admin-Only Page
                </label>
                <label class="disBlo mT20">
                    <input type="checkbox" name="staffPage" id="staffPageID" value="43" 
                    @if ($GLOBALS['SL']->treeRow->TreeOpts%43 == 0) CHECKED @endif autocomplete="off">
                    <i class="fa fa-key mL10 mR5" aria-hidden="true"></i> Staff Page
                </label>
                @if ($GLOBALS["SL"]->sysHas('partners'))
                    <label class="disBlo mT20">
                        <input type="checkbox" name="partnPage" id="partnPageID" value="41" 
                        @if ($GLOBALS['SL']->treeRow->TreeOpts%41 == 0) CHECKED @endif autocomplete="off">
                        <i class="fa fa-university mL10 mR5" aria-hidden="true"></i> Partner Page
                    </label>
                @endif
                @if ($GLOBALS["SL"]->sysHas('volunteers'))
                    <label class="disBlo mT20">
                        <input type="checkbox" name="volunPage" id="volunPageID" value="17" 
                        @if ($GLOBALS['SL']->treeRow->TreeOpts%17 == 0) CHECKED @endif autocomplete="off">
                        <i class="fa fa-hand-rock-o mL10 mR5" aria-hidden="true"></i> Volunteer Page
                    </label>
                @endif
            @endif
            
            </div><div class="col-md-4">
            
            @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
                <label class="disBlo mT20">
                    <input type="checkbox" name="homepage" id="homepageID" value="7" 
                    @if ($GLOBALS['SL']->treeRow->TreeOpts%7 == 0) CHECKED @endif autocomplete="off">
                    <i class="fa fa-star mL10 mR5" aria-hidden="true"></i> Website Home Page
                </label>
                <label class="disBlo mT20">
                    <input type="checkbox" name="reportPage" id="reportPageID" value="13" 
                    @if ($GLOBALS['SL']->treeRow->TreeOpts%13 == 0) CHECKED @endif autocomplete="off"
                    onClick="if (this.checked) { 
                        document.getElementById('reportPageTreeID').style.display='block'; 
                    } else { document.getElementById('reportPageTreeID').style.display='none'; }">
                    <i class="fa fa-list-alt mL10 mR5" aria-hidden="true"></i> Report for Form Tree
                    <select name="reportPageTree" id="reportPageTreeID" class="form-control mT5" 
                        autocomplete="off">{!! $GLOBALS["SL"]->allTreeDropOpts(
                            (($GLOBALS["SL"]->treeRow->TreeOpts%13 == 0) 
                                ? $node->nodeRow->NodeResponseSet : -3)) !!}
                    </select>
                </label>
            @else
                <label class="disBlo red mT20">
                    <input type="checkbox" name="opts29" id="opts29ID" value="29" 
                        @if ($node->nodeRow->NodeOpts%29 == 0) CHECKED @endif autocomplete="off">
                    <i class="fa fa-sign-out mL10" aria-hidden="true"></i> Exit Page 
                    <div class="fPerc80 slGrey"><i>(no Next button)</i></div></label>
                <label class="disBlo mT20"><input type="checkbox" name="opts59" id="opts59ID" value="59" 
                    @if ($node->nodeRow->NodeOpts%59 == 0) CHECKED @endif autocomplete="off">
                    Hide Progress Bar</label>
            @endif
                <div class="nFld w100">
                    <label class="w100">
                        Focus Field: 
                        <input type="number" name="pageFocusField" autocomplete="off" 
                            value="{{ $node->nodeRow->NodeCharLimit }}" 
                            class="disIn form-control mR10" style="width: 60px;" ><br />
                        <i class="fPerc80 slGrey">
                        (0 is default, -1 overrides no focus, otherwise set this a Node ID)</i>
                    </label>
                </div>
                
            </div><div class="col-md-1">
            </div><div class="col-md-3">
            
            @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
                <label class="disBlo mT20">
                    <input type="checkbox" name="pageBg" id="pageBgID" value="67" class="mR5"
                        @if ($GLOBALS['SL']->treeRow->TreeOpts%67 == 0) CHECKED @endif autocomplete="off">
                    Whole page has alternate background
                </label>
            @endif
            
            </div>
        </div></div>
    </div>
</div>