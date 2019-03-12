<!-- Stored in resources/views/survloop/forms/uploads-print.blade.php -->
@if (!$REQ->has('upDel') || intVal($REQ->upDel) != $upRow->id)
    <a name="up{{ $upRow->id }}"></a>
    @if (intVal($upRow->UpType) == $vidTypeID)
        @if (trim($upDeets["youtube"]) != '')
            <iframe id="ytplayer{{ $upRow->id }}" type="text/html" width="100%" 
                height="{{ $height }}" class="mBn5" frameborder="0" allowfullscreen 
                src="https://www.youtube.com/embed/{{ $upDeets['youtube'] }}?rel=0&color=white" 
                ></iframe>
        @elseif (trim($upDeets["vimeo"]) != '')
            <iframe id="vimplayer{{ $upRow->id }}" width="100%" height="{{ $height }}" class="mBn5"
                frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen
                src="https://player.vimeo.com/video/{{ $upDeets['vimeo'] }}" 
                ></iframe>
        @endif
    @elseif (isset($upRow->UpUploadFile) && isset($upRow->UpStoredFile) && trim($upRow->UpUploadFile) != '' 
        && trim($upRow->UpStoredFile) != '')
        @if (in_array($upDeets["ext"], array("gif", "jpeg", "jpg", "png")))
            <div class="w100 disBlo" style="height: {{ (2+$height) }}px; overflow: hidden;">
                <a href="{{ $upDeets['filePub'] }}" target="_blank" class="disBlo w100" 
                    ><img src="{{ $upDeets['filePub'] }}" border=1 class="w100" 
                        alt="{{ ((isset($upRow->UpStoredFile)) ? $upRow->UpStoredFile : 'Uploaded Image') }}"></a>
            </div>
        @else 
            <div class="w100 disBlo BGblueLight vaM" style="height: {{ (2+$height) }}px;">
                <a href="{{ $upDeets['filePub'] }}" target="_blank" 
                    class="disBlo w100 taL vaM fPerc133 wht" style="height: {{ $height }}px;"
                    ><div class="f60 wht mT20 mL20 disIn"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></div>
                    @if (strlen($upRow->UpUploadFile) > 40) <h4 class="disIn wht m0">{{ $upRow->UpUploadFile }}</h4>
                    @else <h3 class="disIn wht m0">{{ $upRow->UpUploadFile }}</h3>
                    @endif
                </a>
            </div>
        @endif
    @endif
    <p><span class="fPerc125">{{  $upRow->UpTitle }}</span>
    @if ($isAdmin || $isOwner)
        <span class="mL10 slGrey"> @if ($upRow->UpPrivacy == 'Public') (Public) @else (Private) @endif </span>
    @endif </p>
@endif