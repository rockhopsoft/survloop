<!-- resources/views/survloop/forms/uploads-print-youtube.blade.php -->
@if (trim($upDeets["youtube"]) != '')
    <iframe id="ytplayer{{ $upRow->up_id }}" type="text/html" width="100%" 
        height="{{ $height }}" class="mBn5 brdInfo" frameborder="0" allowfullscreen 
        src="https://www.youtube.com/embed/{{ $upDeets['youtube'] }}?rel=0&color=white" 
        ></iframe>
    @if ($GLOBALS['SL']->isPrintView())
        <div class="mBn10">
            {!! $GLOBALS['SL']->getYoutubeUrl($upDeets['youtube'], true, 'fPerc80') !!}
        </div>
    @endif
@elseif (trim($upDeets["vimeo"]) != '')
    <iframe id="vimplayer{{ $upRow->up_id }}" width="100%" height="{{ $height }}" 
        webkitallowfullscreen mozallowfullscreen allowfullscreen
        src="https://player.vimeo.com/video/{{ $upDeets['vimeo'] }}" 
        class="mBn5 brdInfo" frameborder="0"></iframe>
    @if ($GLOBALS['SL']->isPrintView())
        <div class="mBn10">
            {!! $GLOBALS['SL']->getYoutubeUrl($upDeets['vimeo'], true, 'fPerc80') !!}
        </div>
    @endif
@endif