<!-- resources/views/survloop/forms/uploads-print-youtube.blade.php -->
@if (trim($upDeets["youtube"]) != '')

    @if ($GLOBALS['SL']->isPrintView())
        <img src="{{ $GLOBALS['SL']->getYouTubeThumb($upDeets['youtube']) }}"
            width=100% height="{{ (2*$height) }}">
    @else
        <iframe id="ytplayer{{ $upRow->up_id }}" type="text/html" width="100%" 
            height="{{ $height }}" class="mBn5 brdInfo" frameborder="0" allowfullscreen 
            src="https://www.youtube.com/embed/{{ $upDeets['youtube'] }}?rel=0&color=white" 
            ></iframe>
    @endif
    <div class="mT10 mBn10">
    @if ($GLOBALS['SL']->isPrintView())
        {!! $GLOBALS['SL']->getYoutubeUrl($upDeets['youtube'], true, 'fPerc80') !!}
    @else
        <a href="{{ $GLOBALS['SL']->getYoutubeUrl($upDeets['youtube'], false) }}"
            target="_blank"><i class="fa fa-external-link" aria-hidden="true"></i> 
            YouTube</a>
    @endif
    </div>

@elseif (trim($upDeets["vimeo"]) != '')

    @if ($GLOBALS['SL']->isPrintView())
        <img src="{{ $GLOBALS['SL']->getVimeoThumb($upDeets['vimeo']) }}"
            width=100% height="{{ (2*$height) }}">
    @else
        <iframe id="vimplayer{{ $upRow->up_id }}" width="100%" height="{{ $height }}" 
            webkitallowfullscreen mozallowfullscreen allowfullscreen
            src="https://player.vimeo.com/video/{{ $upDeets['vimeo'] }}" 
            class="mBn5 brdInfo" frameborder="0"></iframe>
    @endif
    <div class="mT10 mBn10">
    @if ($GLOBALS['SL']->isPrintView())
        {!! $GLOBALS['SL']->getYoutubeUrl($upDeets['vimeo'], true, 'fPerc80') !!}
    @else
        <a href="{{ $GLOBALS['SL']->getVimeoUrl($upDeets['vimeo'], false) }}"
            target="_blank"><i class="fa fa-external-link" aria-hidden="true"></i> 
            Vimeo</a>
    @endif
    </div>

@elseif (trim($upDeets["archiveVid"]) != '')

    @if ($GLOBALS['SL']->isPrintView())
        <img src="{!! $GLOBALS['SL']->getArchiveOrgVidThumb($upDeets['archiveVid'], false) !!}"
            width=100% height="{{ (2*$height) }}">
    @else
        <iframe id="archplayer{{ $upRow->up_id }}" width="100%" height="{{ $height }}"
            class="mBn5 brdInfo" webkitallowfullscreen="true" mozallowfullscreen="true" 
            src="https://archive.org/embed/{{ $upDeets['archiveVid'] }}"
            frameborder="0" allowfullscreen></iframe>
    @endif
    <div class="mT10 mBn10">
    @if ($GLOBALS['SL']->isPrintView())
        {!! $GLOBALS['SL']->getArchiveOrgVidUrl($upDeets['archiveVid'], true, 'fPerc80') !!}
    @else
        <a href="{{ $GLOBALS['SL']->getArchiveOrgVidUrl($upDeets['archiveVid'], false) }}"
            target="_blank"><i class="fa fa-external-link" aria-hidden="true"></i> 
            Archive.org</a>
    @endif
    </div>

@elseif (trim($upDeets["instagram"]) != '')

    @if (trim($upDeets["thmbUrl"]) != '')
        <a href="{{ $upRow->up_video_link }}" target="_blank"
            ><img src="{{ $upDeets['thmbUrl'] }}" border="0" 
            width="100%" height="{{ $height }}"></a>
    @endif
    <div class="mT10 mBn10"><a href="{{ $upRow->up_video_link }}" target="_blank"
        >{{ $upRow->up_video_link }}</a></div>
    <?php /*
    <video controls style="width:100%; display: block !important;">
        <source src="{{ $upDeets['instagramShortLink'] }}" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <iframe src="{{ $upDeets['instagramShortLink'] }}embed/" width="100%" 
        height="{{ $height }}" frameborder="0" scrolling="no" 
        allowtransparency="true"></iframe>
    */ ?>

@elseif (isset($upDeets["otherLnk"]) && trim($upDeets["otherLnk"]) != '')

    <a href="{{ $upDeets['otherLnk'] }}"
        target="_blank"><i class="fa fa-external-link" aria-hidden="true"></i> 
        {{ $upDeets['otherLnk'] }}</a>

@endif