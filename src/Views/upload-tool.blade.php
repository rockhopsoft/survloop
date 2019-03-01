<!-- Stored in resources/views/survloop/upload-tool.blade.php -->

<div id="uploadErrors" class="slRedDark"></div>
<div id="up{{ $nID }}Wrap" class="slCard">
    <div class="uploadTypes">
        <h2><i class="fa fa-cloud-upload"></i> New Upload</h2>
        @if (sizeof($uploadTypes) > 1)
            @foreach ($uploadTypes as $j => $ty)
                <label for="n{{ $nID }}fld{{ $j }}" id="n{{ $nID }}fld{{ $j }}lab" class="finger">
                    <div class="disIn mR5"><input id="n{{ $nID }}fld{{ $j }}" name="n{{ $nID }}fld" 
                        value="{{ $ty->DefID }}" type="radio" autocomplete="off" class="upTypeBtn"></div>    
                        {{ $ty->DefValue }}
                </label>
            @endforeach
        @else
            <input type="hidden" autocomplete="off" id="n{{ $nID }}fldID" name="n{{ $nID }}fld" 
                value="{{ $uploadTypes[0]->DefID }}">
        @endif
        
        <div id="up{{ $nID }}Info" class=" @if (sizeof($uploadTypes) > 1) disNon @else disBlo @endif ">
            <div class="nodeHalfGap"></div>
            <div id="up{{ $nID }}FormVideo" class="disNon">
                <div class="nPrompt"><label for="up{{ $nID }}VidID">
                    Video URL Link (e.g. <a href="https://www.youtube.com/" target="_blank">YouTube</a>, 
                        <a href="https://vimeo.com/" target="_blank">Vimeo</a>, or 
                        <a href="https://archive.org/details/opensource_movies" target="_blank">Internet Archive</a>)
                </label></div>
                <div class="nFld mT5">
                    <input type="text" id="up{{ $nID }}VidID" name="up{{ $nID }}Vid" 
                        class="form-control form-control-lg ntrStp slTab" {!! $GLOBALS["SL"]->tabInd() !!}>
                </div>
            </div>
            <div id="up{{ $nID }}FormFile" class="disBlo">
                <div class="nPrompt"><label for="up{{ $nID }}FileID">
                    Select Upload File ( .PNG .JPG .GIF .PDF )
                </label></div>
                <div class="nFld mT5">
                    <input type="file" name="up{{ $nID }}File" id="up{{ $nID }}FileID" {!! $GLOBALS["SL"]->tabInd() !!}
                        class="form-control ntrStp slTab" style="border: 1px #CCC solid;" >
                </div>
            </div>
            <div class="nodeHalfGap"></div>
            <div id="node100{{ $nID }}" class="nodeWrap">
                <div id="nLabel100{{ $nID }}" class="nPrompt"><label for="up{{ $nID }}TitleID">Title of Upload</label></div>
                <div class="nFld mT5">
                    <input type="text" id="up{{ $nID }}TitleID" name="up{{ $nID }}Title" value="" 
                        class="form-control form-control-lg ntrStp slTab" {!! $GLOBALS["SL"]->tabInd() !!}>
                </div>
            </div>
            <?php /* <div class="nodeHalfGap"></div>
            <div class="nPrompt"><label for="up{{ $nID }}DescID">Upload Description:</label></div>
            <div class="nFld">
                <input type="text" id="up{{ $nID }}DescID" name="up{{ $nID }}Desc" value="" 
                    class="form-control form-control-lg ntrStp slTab" {!! $GLOBALS["SL"]->tabInd() !!}>
            </div> */ ?>
            @if (isset($GLOBALS["SL"]->treeSettings["uploads-public"]) 
                && intVal($GLOBALS["SL"]->treeSettings["uploads-public"][0]) > 0)
                <div class="nodeHalfGap"></div>
                <div class="nPrompt">
                    <label for="up{{ $nID }}VidID">Privacy of Upload</label>
                </div> 
                <div class="nFld mT5">
                    <select name="up{{ $nID }}Privacy" id="up{{ $nID }}PrivacyID" 
                        class="form-control form-control-lg ntrStp slTab" {!! $GLOBALS["SL"]->tabInd() !!}>
                        <option value="Public" @if ($isPublic) CHECKED @endif >Public: Visible to whole world</option>
                        <option value="Private" @if (!$isPublic) CHECKED @endif >Private: Visible only to investigators</option>
                    </select>
                </div>
                @if (isset($uploadWarn) && trim($uploadWarn) != '')
                    <div class="alert alert-danger fade in alert-dismissible show" style="margin-top: 10px;">
                        {!! $uploadWarn !!}
                    </div>
                @endif
            @endif
            <div class="nodeHalfGap"></div>
            <a id="nFormUpload" href="javascript:;" class="btn btn-xl btn-primary w100">Upload</a>
        </div>
        
        <hr>
        
        {!! $getPrevUploads !!}
        
    </div>
</div>

