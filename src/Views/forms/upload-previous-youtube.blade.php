<!-- resources/views/survloop/forms/upload-previous-youtube.blade.php -->

@if (trim($upDeets[$i]["youtube"]) != '')
    <iframe id="ytplayer{{ $upRow->up_id }}" type="text/html" width="100%" 
        height="{{ $height }}" class="mBn5" frameborder="0" allowfullscreen 
        src="https://www.youtube.com/embed/{{ $upDeets[$i]['youtube'] }}?rel=0&color=white" 
        ></iframe>
@elseif (trim($upDeets[$i]["vimeo"]) != '')
    <iframe id="vimplayer{{ $upRow->up_id }}" width="100%" height="{{ $height }}" 
        class="mBn5" frameborder="0" webkitallowfullscreen mozallowfullscreen 
        src="https://player.vimeo.com/video/{{ $upDeets[$i]['vimeo'] }}" 
        allowfullscreen></iframe>
@elseif (trim($upDeets[$i]["archiveVid"]) != '')
    <iframe id="archplayer{{ $upRow->up_id }}" width="100%" 
        height="{{ $height }}" frameborder="0" 
        webkitallowfullscreen="true" mozallowfullscreen="true" 
        src="https://archive.org/embed/{{ $upDeets[$i]['archiveVid'] }}"
        allowfullscreen></iframe>
@elseif (trim($upDeets[$i]["instagram"]) != '')
    @if (trim($upDeets["thmbUrl"]) != '')
        <a href="{{ $upRow->up_video_link }}" target="_blank"
            ><img src="{{ $upDeets[$i]['thmbUrl'] }}" border="0" 
            width="100%" height="{{ $height }}"></a>
    @else
        <div class="mT10"><a href="{{ $upRow->up_video_link }}" target="_blank"
            >{{ $upRow->up_video_link }}</a></div>
    @endif
@endif