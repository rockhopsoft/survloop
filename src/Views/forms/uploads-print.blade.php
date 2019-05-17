<!-- resources/views/survloop/forms/uploads-print.blade.php -->
@if (!$REQ->has('upDel') || intVal($REQ->upDel) != $upRow->UpID)
    <a name="up{{ $upRow->UpID }}"></a>
    @if (intVal($upRow->UpType) == $vidTypeID)
        @if ($canShow)
            @if (trim($upDeets["youtube"]) != '')
                <iframe id="ytplayer{{ $upRow->UpID }}" type="text/html" width="100%" 
                    height="{{ $height }}" class="mBn5 brdInfo" frameborder="0" allowfullscreen 
                    src="https://www.youtube.com/embed/{{ $upDeets['youtube'] }}?rel=0&color=white" 
                    ></iframe>
            @elseif (trim($upDeets["vimeo"]) != '')
                <iframe id="vimplayer{{ $upRow->UpID }}" width="100%" height="{{ $height }}" class="mBn5 brdInfo"
                    frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen
                    src="https://player.vimeo.com/video/{{ $upDeets['vimeo'] }}" 
                    ></iframe>
            @endif
        @else
            {!! view('vendor.survloop.forms.uploads-print-no-preview', [
                "canShow" => $canShow,
                "height"  => $height,
                "icon"    => '<i class="fa fa-video-camera" aria-hidden="true"></i>',
                "link"    => ''
            ])->render() !!}
        @endif
    @elseif (isset($upRow->UpUploadFile) && isset($upRow->UpStoredFile) && trim($upRow->UpUploadFile) != '' 
        && trim($upRow->UpStoredFile) != '')
        @if (in_array($upDeets["ext"], array("gif", "jpeg", "jpg", "png")))
            @if ($canShow)
                <div class="pT20 pB15"><div class="w100 disBlo brdInfo" style="height: {{ (2+$height) }}px; overflow: hidden;">
                    <a href="{{ $upDeets['filePub'] }}" target="_blank" class="disBlo w100" 
                        ><img src="{{ $upDeets['filePub'] }}" border=1 class="w100" 
                            alt="{{ ((isset($upRow->UpStoredFile)) ? $upRow->UpStoredFile : 'Uploaded Image') }}"></a>
                </div></div>
            @else
                {!! view('vendor.survloop.forms.uploads-print-no-preview', [
                    "canShow" => $canShow,
                    "height"  => $height,
                    "icon"    => '<i class="fa fa-file-image-o" aria-hidden="true"></i>',
                    "link"    => ''
                ])->render() !!}
            @endif
        @else
            {!! view('vendor.survloop.forms.uploads-print-no-preview', [
                "canShow" => $canShow,
                "height"  => $height,
                "icon"    => '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>',
                "link"    => $upDeets['filePub']
            ])->render() !!}
        @endif
    @endif
    <p>{{  $upRow->UpTitle }}
    @if ($isAdmin || $isOwner)
        <span class="mL10 slGrey"> @if ($upRow->UpPrivacy == 'Public') (Public) @else (Private) @endif </span>
        <div class="mTn10 fPerc66 slGrey">{!! $upRow->UpUploadFile !!}</div>
    @endif
    </p>
@endif