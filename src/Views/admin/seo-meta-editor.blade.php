<!-- resources/views/vendor/survloop/admin/seo-meta-editor.blade.php -->
<div class="row mB20">
    <div class="col-3 slBlueDark">
        <h4 class="mT5 mB0"><label for="npageTitleFldID">Page Title:</label></h4>
    </div>
    <div class="col-9 nFld m0 p0">
        <input type="text" name="pageTitle" id="npageTitleFldID" autocomplete="off" 
            class="form-control mT0 mB0" onBlur="slugOnBlur(this, 'nodeSlugID');"
            @if (isset($currMeta["title"]) && trim($currMeta["title"]) != '') value="{{ $currMeta['title'] }}" @endif
            onKeyUp="charCountKeyUp('pageTitle'); previewPage();" >
        <div>
            <div class="disIn pL5 mR5 row2 fPerc80">
                <div id="wordCntpageTitle" class="disIn">0</div> characters
            </div>
            <span class="fPerc80 slGrey">
                Most search engines only display the first 60 characters of the title.
            </span>
        </div>
    </div>
</div>
@if (isset($currMeta["slug"]) && $currMeta["slug"] !== false)
    <div class="row mB20">
        <div class="col-3 slBlueDark">
            <h4 class="mT5 mB0"><label for="nodeSlugID">Page URL:</label></h4>
        </div>
        <div class="col-9 nFld m0 p0">
            @if (isset($currMeta["base"])) <div class="disIn slGrey">{{ $currMeta["base"] }}</div> @endif
            <input type="text" name="nodeSlug" id="nodeSlugID" autocomplete="off" 
                class="form-control w40 disIn mT0 mB0" onKeyUp="previewPage();"
                @if (isset($currMeta["slug"]) && trim($currMeta["slug"]) != '') value="{{ $currMeta['slug'] }}" @endif >
        </div>
    </div>
@endif
<div class="row mB20">
    <div class="col-3 slBlueDark">
        <h4 class="mT5 mB0"><label for="npageDescFldID">Page Description:</label></h4>
    </div>
    <div class="col-9 nFld m0 p0">
        <textarea name="pageDesc" id="npageDescFldID" autocomplete="off" class="form-control mT0 mB0 flexarea" 
            onKeyUp="charCountKeyUp('pageDesc'); flexAreaAdjust(this); previewPage();"
            @if (isset($currMeta["desc"]) && trim($currMeta["desc"]) != '') >{{ $currMeta["desc"] }}</textarea> 
            @else ></textarea> @endif
        <div>
            <div class="disIn pL5 mR5 row2 fPerc80">
                <div id="wordCntpageDesc" class="disIn">0</div> characters
            </div>
            <span class="fPerc80 slGrey">
                Most search engines only display the first 160 characters of the description.
            </span>
        </div>
    </div>
</div>
<div class="row mB20">
    <div class="col-3 slBlueDark">
        <h4 class="mT5 mB0"><label for="npageKeyFldID">Page Keywords:</label></h4>
        <span class="fPerc80 slGrey">(comma separated)</span>
    </div>
    <div class="col-9 nFld m0 p0">
        <textarea name="pageKey" id="npageKeyFldID" autocomplete="off" class="form-control mT0 mB0 flexarea" 
            onKeyUp="keywordCountKeyUp('pageKey'); flexAreaAdjust(this);"
            @if (isset($currMeta["wrds"]) && trim($currMeta["wrds"]) != '') >{{ $currMeta["wrds"] }}</textarea> 
            @else ></textarea> @endif
        <div>
            <div class="disIn pL5 mR5 row2 fPerc80">
                <div id="keywordCntpageKey" class="disIn">0</div> keywords
            </div>
            <span class="fPerc80 slGrey">
                Much less important, but fewer than 10 keywords is better. 
                <a href="https://trends.google.com/trends/" target="_blank"
                    ><i class="fa fa-external-link" aria-hidden="true"></i> Search Trends</a>
            </span>
        </div>
    </div>
</div>
<div class="row mB20">
    <div class="col-3 slBlueDark">
        <h4 class="mT5 mB0"><label for="npageImgFldID">Page Social Sharing Image:</label></h4>
        <span class="fPerc80 slGrey">(ideally 800x418 pixels)</span>
    </div>
    <div class="col-9 nFld m0 p0">
        <input type="text" name="pageImg" id="npageImgFldID" autocomplete="off"
            class="form-control form-control-lg mT0 mB0 openImgUpdate" onKeyUp="previewPage();"
            @if (isset($currMeta["img"]) && trim($currMeta["img"]) != '') value="{{ $currMeta['img'] }}" @endif >
        <div class="row mT5">
            <div class="col-8">
                <div class="prevImg brd"><img id="npageImgSelImg" alt="Page Social Sharing Image" 
                @if (isset($node->extraOpts["meta-img"]) && trim($node->extraOpts["meta-img"]) != '')
                    src="{{ $node->extraOpts["meta-img"] }}" 
                @elseif (isset($GLOBALS['SL']->sysOpts['meta-img']))
                    src="{{ $GLOBALS['SL']->sysOpts['meta-img'] }}"
                @endif ></div>
            </div>
            <div class="col-4 pT10">
                <a href="javascript:;" class="btn btn-sm btn-secondary w100 mB10 openImgReset" id="imgResetpageImg"
                    ><i class="fa fa-trash-o" aria-hidden="true"></i> Reset to Default</a>
                <a href="javascript:;" class="btn btn-secondary w100 mB10 openImgSelect" id="imgSelectpageImg" 
                    data-title="" data-presel="{{ $currMeta['img'] }}" 
                    ><div><i class="fa fa-picture-o" aria-hidden="true"></i> 
                    Select or</div><div>Upload Image</div></a><br />
            </div>
        </div>
    </div>
</div>
<div id="imgSelectpageImgTitle" class="disNon">
    Select Social Sharing Image For The Page: {{ $currMeta["title"] }}
</div>

<script type="text/javascript">
function previewPage() {
    if (document.getElementById('pagePrev')) {
        var pTitle = " - {{ ((isset($GLOBALS['SL']->sysOpts['meta-title'])) ? $GLOBALS['SL']->sysOpts['meta-title'] : '') }}";
        if (document.getElementById('npageTitleFldID')) {
            pTitle = document.getElementById('npageTitleFldID').value.trim() + pTitle;
        }
        var pDesc = "";
        if (document.getElementById("npageDescFldID")) pDesc = document.getElementById("npageDescFldID").value.trim();
        var pUrl = "{{ $GLOBALS['SL']->treeBaseUrl(true) }}";
        if (document.getElementById('nodeSlugID')) pUrl += document.getElementById('nodeSlugID').value.trim();
        var pUrlS = pUrl.replace("https://www.", "").replace("https://", "").replace("http://www.", "").replace("http://", "");
        var pImg = @if (isset($node->extraOpts["meta-img"]) && trim($node->extraOpts["meta-img"]) != "") "" 
        @elseif (isset($GLOBALS['SL']->sysOpts['meta-img'])) "{{ $GLOBALS['SL']->sysOpts['meta-img'] }}" @endif ;
        pImg = document.getElementById('npageImgFldID').value.trim();
        if (document.getElementById("metaImgID")) pImg = document.getElementById("metaImgID").value.trim();
        if (pTitle.length > 60) pTitle = pTitle.substring(0, 60)+"...";
        if (pDesc.length > 160) pDesc = pDesc.substring(0, 160)+"...";
        document.getElementById('pagePrev').innerHTML = '<div class="prevImg"><img src="'+pImg+'"></div><div class="p10 mL5"><h3 class="mT0">'+pTitle+'</h3><div class="pB5">'+pDesc+'</div><a href="'+pUrl+'" target="_blank" class="f10">'+pUrlS+'</a></div>';
    }
    return true;
}
function previewPageAuto() {
    previewPage();
    setTimeout("previewPageAuto()", 3000);
    return true;
}
function loadSeaEditor() {
    previewPageAuto();
    charCountKeyUp('pageTitle');
    charCountKeyUp('pageDesc');
    flexAreaAdjust(document.getElementById('npageDescFldID'));
    keywordCountKeyUp('pageKey');
    flexAreaAdjust(document.getElementById('npageKeyFldID'));
    return true;
}
setTimeout("loadSeaEditor()", 50);
</script>