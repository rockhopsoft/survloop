<!-- resources/views/survloop/forms/uploads-print-youtube.blade.php -->
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