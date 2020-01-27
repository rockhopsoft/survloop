<!-- resources/views/survloop/forms/upload-previous-youtube.blade.php -->
@if (trim($upDeets[$i]["youtube"]) != '')
    <iframe id="ytplayer{{ $upRow->up_id }}" type="text/html" width="100%" 
        height="{{ $height }}" class="mBn5" frameborder="0" allowfullscreen 
        src="https://www.youtube.com/embed/{{ $upDeets[$i]['youtube'] }}?rel=0&color=white" 
        ></iframe>
@elseif (trim($upDeets[$i]["vimeo"]) != '')
    <iframe id="vimplayer{{ $upRow->up_id }}" width="100%" height="{{ $height }}" class="mBn5"
        frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen
        src="https://player.vimeo.com/video/{{ $upDeets[$i]['vimeo'] }}" 
        ></iframe>
@endif