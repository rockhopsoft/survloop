<!-- resources/views/vendor/survloop/forms/inc-image-selecter.blade.php -->
<div class="container">
<div class="row">
    <div class="col-xl-4 h100 row2" style="margin: -15px 0px -15px 0px; padding: 15px;">
    
        <div class="nodeAnchor"><a name="imgFile{{ $nID }}anc"></a></div>
        <div class="slCard nodeWrap">
            <a id="hidivBtnImgUp{{ $nID }}" href="javascript:;" 
                class="btn btn-lg btn-secondary btn-block hidivBtnSelf 
                @if (isset($presel) && trim($presel) != '') disBlo @else disNon @endif " 
                style="margin: -10px 0px 20px 0px;">Upload New Image</a>
            <div id="hidivImgUp{{ $nID }}" 
                class=" @if (isset($presel) && trim($presel) != '') disNon @else disBlo @endif ">
                <form id="formUpImg{{ $nID }}ID" name="formUpImg{{ $nID }}" 
                    action="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/ajax/img-up" 
                    enctype="multipart/form-data" method="post">
                <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="ajax" value="1" >
                <input type="hidden" name="nID" value="{{ $nID }}" >
                <h3 class="m0 slBlueDark">Upload New Image:</h3>
                <div class="slGrey fPerc80">.PNG .JPG .GIF 4MB maximum</div>
                <input type="file" name="imgFile{{ $nID }}" id="imgFile{{ $nID }}ID"
                    class="form-control ntrStp slTab w100 mT5 openImgUpdate" 
                    style="padding: 0px 16px 0px 0px;" autocomplete="off"
                    {!! $GLOBALS["SL"]->tabInd() !!}>
                <input type="button" value="Upload" id="imgUp{{ $nID }}" 
                    class="btn btn-lg btn-primary mT10 fR imgUpBtn">
                </form>
                <div id="img{{ $nID }}fileUpdate" class="fC p10"></div>
            </div>
            
            <div id="imgDeetDiv{{ $nID }}" class="brdTop"></div>
            
            <div class="p20 m10"></div>
            <div class="brdTop pT20 slGrey">
                This Spot's Current Selection: 
            @if (isset($presel) && trim($presel) != '')
                <a href="{{ $presel }}" target="_blank" class="slBlueDark">{{ $presel }}<br />
                    <img src="{{ $presel }}" class="w100"></a>
            @else <i class="slBlueDark">None</i> @endif
            </div>
        </div>

    </div>
    <div class="col-xl-8">
        
        <div class="slCard nodeWrap ovrSho imgFileLibrary">
            <h3 class="mT0 slBlueDark">Select Image From Library:</h3>
            @if (isset($imgs) && is_array($imgs) && sizeof($imgs) > 0)
                <div class="row mB10">
                @foreach ($imgs as $i => $img)
                    @if ($i%6 == 0 && $i > 0) </div><div class="row mB10"> @endif
                    <div class="col-2">
                        <a id="selectImg{{ $nID }}sel{{ $img->img_id }}" 
                            class="openImgDetail wrdBrkAll" href="javascript:;" 
                            ><div class="prevImg brdFnt"><img src="{{ $img->img_full_filename }}" 
                                alt="{{ ((isset($img->img_title)) ? $img->img_title : '') }}" 
                                class="brd"></div>
                        @if (isset($img->img_title) && trim($img->img_title) != '') {{ $img->img_title }}
                        @elseif (strrpos($img->img_file_loc, '/') !== false) 
                            {{ str_replace('_', ' ', 
                                substr($img->img_file_loc, strrpos($img->img_file_loc, '/')+1)
                            ) }}
                        @else {{ str_replace('_', ' ', $img->img_file_loc) }} @endif
                        </a>
                    </div>
                @endforeach 
                </div>
            @else
                <div class="p20 slGrey"><i>No images found</i></div>
            @endif
        </div>
        
    </div>
</div>
</div>