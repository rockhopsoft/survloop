<!-- resources/views/vendor/survloop/inc-image-deet.blade.php -->

<input type="hidden" name="imgNode{{ $img->ImgID }}" id="imgNode{{ $img->ImgID }}ID" value="{{ $nID }}">
<input type="hidden" name="imgUrl{{ $img->ImgID }}" id="imgUrl{{ $img->ImgID }}ID" value="{{ $img->ImgFullFilename }}">
<div style="height: 1px; padding-top: 1px; overflow: hidden;">
    <input type="text" name="img{{ $img->ImgID }}File" id="img{{ $img->ImgID }}FileID" value="{{ $urlPrint }}">
</div>


<a href="javascript:;" id="imgChoose{{ $img->ImgID }}" class="btn btn-lg btn-primary w100 mT10 mB10 imgChoose"
    >Select This Image</a>
<div class="row">
    <div class="col-6">
    
        <a href="{{ $img->ImgFullFilename }}" target="_blank"
            ><img src="{{ $img->ImgFullFilename }}" class="w100 brd"><br />
            {!! str_replace('/', '/ ', $GLOBALS["SL"]->urlClean($urlPrint)) !!}<br />
            <span class="slGrey"><i class="fa fa-external-link" aria-hidden="true"></i> Open In New Window</span></a>
        <div class="pT10">
            <a href="javascript:;" onClick="copyClip('img{{ $img->ImgID }}FileID');"
                ><i class="fa fa-files-o" aria-hidden="true"></i> Copy Image URL To Clipboard</a> 
        </div>
        
        <div class="p10"></div>
        
        @if (isset($img->ImgType)) <span class="mR10">{{ strtoupper($img->ImgType) }}</span> @endif
        @if (isset($img->ImgWidth) && isset($img->ImgHeight)) 
            <span class="mR10">{{ $img->ImgWidth }}x{{ $img->ImgHeight }}</span>
        @endif
        @if (isset($img->ImgFileSize)) 
            <span class="mR10">{{ $GLOBALS["SL"]->humanFilesize($img->ImgFileSize, 1) }}</span>
        @endif
        <br />
        <span class="mR10">Uploaded {{ date("n/j/Y g:ia", strtotime($img->created_at)) }}</span>
        
        @if (trim($cleanOrig) != '' && $cleanOrig != $cleanCurr)
            <div class="mT10 fPerc66"><i>Original Filename: {{ $cleanOrig }}</i></div>
        @endif
        
    </div>
    <div class="col-6">
    
        <form id="formSaveImg{{ $img->ImgID }}ID" name="formSaveImg{{ $img->ImgID }}" method="post" 
            action="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/ajax/img-save" enctype="multipart/form-data">
        <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="ajax" value="1" >
        <input type="hidden" name="imgID" value="{{ $img->ImgID }}" >
        <input type="hidden" name="nID" value="{{ $nID }}" >
        
        <label>Image Name/Title:<br />
            <input type="text" name="img{{ $img->ImgID }}Name" id="img{{ $img->ImgID }}NameID"
                @if (isset($img->ImgTitle)) value="{{ $img->ImgTitle }}" @endif 
                class="form-control ntrStp slTab w100 mB10 imgSaveDeetFld" data-imgid="{{ $img->ImgID }}" >
        </label>
        <label>Image Attribution:<br />
            <input type="text" name="img{{ $img->ImgID }}Credit" id="img{{ $img->ImgID }}CreditID"
                @if (isset($img->ImgCredit)) value="{{ $img->ImgCredit }}" @endif 
                class="form-control ntrStp slTab w100 mB10 imgSaveDeetFld" data-imgid="{{ $img->ImgID }}" >
        </label>
        <label>Attribution URL:<br />
            <input type="text" name="img{{ $img->ImgID }}CreditUrl" id="img{{ $img->ImgID }}CreditUrlID" 
                @if (isset($img->ImgCreditUrl)) value="{{ $img->ImgCreditUrl }}" @endif 
                class="form-control ntrStp slTab w100 mB10 imgSaveDeetFld" data-imgid="{{ $img->ImgID }}" >
        </label>
        
        <input type="button" id="imgSave{{ $img->ImgID }}" class="btn btn-secondary w100 imgSaveDeet" 
            value="Save Changes">
        </form>
        <div id="img{{ $img->ImgID }}saveUpdate" class="w100 pT20 fPerc133 slBlueDark"></div>
    
    </div>
</div>
