<a href="javascript:;" title="{{ $emo['verb'] }}" id="{{ $spot }}e{{ $emo['id'] }}" class="emojiTagBtn"
    >{!! $emo["html"] !!} @if ($cnt > 0) <span class="badge">{{ $cnt }}</span> @endif </a>
<?php
if ($isActive) $GLOBALS["SL"]->pageAJAX .= '$("#' . $spot . 'e' . $emo['id'] . 'Tag").addClass("active"); ';
else $GLOBALS["SL"]->pageAJAX .= '$("#' . $spot . 'e' . $emo['id'] . 'Tag").removeClass("active"); ';
?>