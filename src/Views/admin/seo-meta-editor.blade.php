<!-- resources/views/vendor/survloop/admin/seo-meta-editor.blade.php -->

<label class="w100">
    <div class="mB10">Page Title</div>
    <input type="text" name="pageTitle" id="npageTitleFldID" autocomplete="off" 
        class="form-control mT0 mB0" onBlur="slugOnBlur(this, 'nodeSlugID');"
        @if (isset($currMeta["title"]) && trim($currMeta["title"]) != '') 
            value="{{ $currMeta['title'] }}" 
        @endif
        onKeyUp="charCountKeyUp('pageTitle'); previewPage();" >
</label>
<div class="mB30">
    <div class="disIn pL5 mR5 row2 fPerc80">
        <div id="wordCntpageTitle" class="disIn">0</div> characters
    </div>
    <span class="fPerc80 slGrey">
        Most search engines preview the first 60 characters
    </span>
</div>

@if (isset($currMeta["slug"]) && $currMeta["slug"] !== false)
    <label class="w100 mB30">
        <div class="mB10">Page URL</div>
        @if (isset($currMeta["base"])) 
            <div class="disIn slGrey">{{ $currMeta["base"] }}</div>
        @endif
        <input type="text" name="nodeSlug" id="nodeSlugID" autocomplete="off" 
            class="form-control w40 disIn mT0 mB0" onKeyUp="previewPage();"
            @if (isset($currMeta["slug"]) && trim($currMeta["slug"]) != '') 
                value="{{ $currMeta['slug'] }}" 
            @endif >
    </label>
@endif

<label class="w100">
    <div class="mB10">Page Description</div>
    <textarea name="pageDesc" id="npageDescFldID" autocomplete="off" 
        class="form-control mT0 mB0 flexarea" 
        onKeyUp="charCountKeyUp('pageDesc'); previewPage();" 
            <?php /* flexAreaAdjust(this); */ ?>
        @if (isset($currMeta["desc"]) && trim($currMeta["desc"]) != '')
            >{{ $currMeta["desc"] }}</textarea> 
        @else ></textarea> @endif
</label>
<div class="mB30">
    <div class="disIn pL5 mR5 row2 fPerc80">
        <div id="wordCntpageDesc" class="disIn">0</div> characters
    </div>
    <span class="fPerc80 slGrey">
        Most search engines only display the first 160 characters of the description.
    </span>
</div>

<label class="w100">
    <div class="mB10">
        Page Keywords 
        <span class="fPerc80 slGrey mL5">(comma separated)</span>
    </div>
    <textarea name="pageKey" id="npageKeyFldID" autocomplete="off" 
        class="form-control mT0 mB0 flexarea" 
        onKeyUp="keywordCountKeyUp('pageKey');"
            <?php /* flexAreaAdjust(this); */ ?>
        @if (isset($currMeta["wrds"]) && trim($currMeta["wrds"]) != '') 
            >{{ $currMeta["wrds"] }}</textarea> 
        @else ></textarea> @endif
</label>
<div class="mB30">
    <div class="disIn pL5 mR5 row2 fPerc80">
        <div id="keywordCntpageKey" class="disIn">0</div> keywords
    </div>
    <span class="fPerc80 slGrey">
        Much less important, but fewer than 10 keywords is better. 
        <a href="https://trends.google.com/trends/" target="_blank"
            ><i class="fa fa-external-link" aria-hidden="true"></i> Search Trends</a>
    </span>
</div>

<label class="w100">
    <div class="mB10">
        Page Social Sharing Image 
        <span class="fPerc80 slGrey mL5">(ideally 800x418 pixels)</span>
    </div>
    <input type="text" name="pageImg" id="npageImgFldID" autocomplete="off"
        class="form-control form-control-lg mT0 mB0 openImgUpdate" onKeyUp="previewPage();"
        @if (isset($currMeta["img"]) && trim($currMeta["img"]) != '') value="{{ $currMeta['img'] }}" @endif >
</label>
<div class="row mT15 mB30">
    <div class="col-8">
        <div class="prevImg brd"><img id="npageImgSelImg" alt="Page Social Sharing Image" 
        @if (isset($node->extraOpts["meta-img"]) && trim($node->extraOpts["meta-img"]) != '')
            src="{{ $node->extraOpts["meta-img"] }}" 
        @elseif (isset($GLOBALS['SL']->sysOpts['meta-img']))
            src="{{ $GLOBALS['SL']->sysOpts['meta-img'] }}"
        @endif ></div>
    </div>
    <div class="col-4 pT10">
        <a href="javascript:;" id="imgResetpageImg"
            class="btn btn-sm btn-secondary btn-block mB10 openImgReset"
            ><i class="fa fa-trash-o" aria-hidden="true"></i> Reset to Default</a>
        <a href="javascript:;" id="imgSelectpageImg" 
            class="btn btn-secondary btn-block mB10 openImgSelect" 
            data-title="" data-presel="{{ $currMeta['img'] }}" 
            ><div><i class="fa fa-picture-o" aria-hidden="true"></i> 
            Select or</div><div>Upload Image</div></a><br />
    </div>
</div>

<div id="imgSelectpageImgTitle" class="disNon pB15">
    Select Social Sharing Image For The Page: {{ $currMeta["title"] }}
</div>

<script type="text/javascript">
function previewPage() {
    if (document.getElementById('pagePrev')) {
        var pTitle = "";
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
        if (pTitle.length > 60) pTitle = pTitle.substring(0, 60).trim()+"...";
        if (pDesc.length > 160) pDesc = pDesc.substring(0, 160).trim()+"...";
        document.getElementById('pagePrev').innerHTML = '<div class="prevImg"><img src="'+pImg+'"></div><div class="p10 mL5"><h4 class="mT0">'+pTitle+'</h4><div class="pB5">'+pDesc+'</div><a href="'+pUrl+'" target="_blank" class="fPerc80">'+pUrlS+'</a></div>';
    }
    return true;
}
function previewPageAuto() {
    previewPage();
    setTimeout("previewPageAuto()", 3000);
    return true;
}
function loadSeoEditor() {
    if (typeof charCountKeyUp === "function") { 
        previewPageAuto();
        charCountKeyUp('pageTitle');
        charCountKeyUp('pageDesc');
        //flexAreaAdjust(document.getElementById('npageDescFldID'));
        keywordCountKeyUp('pageKey');
        //flexAreaAdjust(document.getElementById('npageKeyFldID'));
    } else {
        setTimeout("loadSeoEditor()", 2000);
    }
    return true;
}
setTimeout("loadSeoEditor()", 2000);
</script>