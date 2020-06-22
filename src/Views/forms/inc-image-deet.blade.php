<!-- resources/views/vendor/survloop/forms/inc-image-deet.blade.php -->

<input type="hidden" name="imgNode{{ $img->img_id }}" 
    id="imgNode{{ $img->img_id }}ID" value="{{ $nID }}">
<input type="hidden" name="imgUrl{{ $img->img_id }}" 
    id="imgUrl{{ $img->img_id }}ID" value="{{ $img->img_full_filename }}">
<div style="height: 1px; padding-top: 1px; overflow: hidden;">
    <input type="text" name="img{{ $img->img_id }}File" 
        id="img{{ $img->img_id }}FileID" value="{{ $urlPrint }}">
</div>


<a href="javascript:;" id="imgChoose{{ $img->img_id }}" 
    class="btn btn-lg btn-primary btn-block mT10 mB10 imgChoose"
    >Select This Image</a>
    
<a href="{{ $img->img_full_filename }}" target="_blank"
    ><img src="{{ $img->img_full_filename }}" class="w100 brd" alt="{{ 
        ((isset($img->img_title)) ? $img->img_title : '') }}"><br />
    {!! str_replace('/', '/ ', str_replace($GLOBALS['SL']->sysOpts['logo-url'], 
        '/', 
        $GLOBALS["SL"]->urlClean($urlPrint))
    ) !!}<br />
    <span class="slGrey"><i class="fa fa-external-link" aria-hidden="true"></i> 
    Open In New Window</span></a>
<div class="pT10">
    <a href="javascript:;" onClick="copyClip('img{{ $img->img_id }}FileID');"
        ><i class="fa fa-files-o" aria-hidden="true"></i> Copy Image URL To Clipboard</a> 
</div>

<div class="p10"></div>
        
@if (isset($img->img_type))
    <span class="mR10">{{ strtoupper($img->img_type) }}</span>
@endif
@if (isset($img->img_width) && isset($img->img_height)) 
    <span class="mR10">{{ $img->img_width }}x{{ $img->img_height }}</span>
@endif
@if (isset($img->img_file_size)) 
    <span class="mR10">{{ $GLOBALS["SL"]->humanFilesize($img->img_file_size, 1) }}</span>
@endif
<br />
<span class="mR10">Uploaded {{ date("n/j/Y g:ia", strtotime($img->created_at)) }}</span>

@if (trim($cleanOrig) != '' && $cleanOrig != $cleanCurr)
    <div class="mT10 fPerc66"><i>Original Filename: {{ $cleanOrig }}</i></div>
@endif
        
<form id="formSaveImg{{ $img->img_id }}ID" name="formSaveImg{{ $img->img_id }}" method="post" 
    action="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/ajax/img-save" enctype="multipart/form-data">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="ajax" value="1" >
<input type="hidden" name="imgID" value="{{ $img->img_id }}" >
<input type="hidden" name="nID" value="{{ $nID }}" >

<label>Image Name/Title:<br />
    <input type="text" name="img{{ $img->img_id }}Name" id="img{{ $img->img_id }}NameID"
        @if (isset($img->img_title)) value="{{ $img->img_title }}" @endif 
        class="form-control ntrStp slTab w100 mB10 imgSaveDeetFld" data-imgid="{{ $img->img_id }}" >
</label>
<label>Image Attribution:<br />
    <input type="text" name="img{{ $img->img_id }}Credit" id="img{{ $img->img_id }}CreditID"
        @if (isset($img->img_credit)) value="{{ $img->img_credit }}" @endif 
        class="form-control ntrStp slTab w100 mB10 imgSaveDeetFld" data-imgid="{{ $img->img_id }}" >
</label>
<label>Attribution URL:<br />
    <input type="text" name="img{{ $img->img_id }}CreditUrl" id="img{{ $img->img_id }}CreditUrlID" 
        @if (isset($img->img_credit_url)) value="{{ $img->img_credit_url }}" @endif 
        class="form-control ntrStp slTab w100 mB10 imgSaveDeetFld" data-imgid="{{ $img->img_id }}" >
</label>

<input type="button" id="imgSave{{ $img->img_id }}" class="btn btn-secondary btn-block imgSaveDeet" 
    value="Save Changes">
</form>
<div id="img{{ $img->img_id }}saveUpdate" 
    class="w100 pT20 fPerc133 slBlueDark"></div>
