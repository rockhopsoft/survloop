<!-- resources/views/vendor/survloop/elements/inc-social-simple-tweet.blade.php -->
@if (isset($link) && trim($link) != '')
    @if (isset($class) && trim($class) != '')
        <a class="{{ $class }}" target="_blank" href="{{ $GLOBALS['SL']->getTwitShareLnk($link,
            ((isset($title)) ? $title : ''), ((isset($hashtags)) ? $hashtags : '')) }}" >
            <i class="fa fa-twitter" aria-hidden="true"></i> <span>
            @if (isset($btnText) && trim($btnText) != '') {!! $btnText !!} @else Tweet @endif </span></a>
    @else
<?php /*
        <a class="socialTwit" target="_blank" href="{{ $GLOBALS['SL']->getTwitShareLnk($link,
            ((isset($title)) ? $title : ''), ((isset($hashtags)) ? $hashtags : '')) }}" style="color: #FFF;">
            <div><img src="/survloop/uploads/spacer.gif" alt=""><i class="fa fa-twitter" aria-hidden="true"></i> 
            <span>Tweet</span></div></a>
*/ ?>
        <a class="socialTwitPng" target="_blank" href="{{ $GLOBALS['SL']->getTwitShareLnk($link,
            ((isset($title)) ? $title : ''), ((isset($hashtags)) ? $hashtags : '')) }}"
            ><img src="/survloop/uploads/twitter-tweet.png" alt="Share on Twitter"></a>
    @endif
@endif