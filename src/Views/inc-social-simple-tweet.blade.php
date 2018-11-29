<!-- resources/views/vendor/survloop/inc-social-simple-tweet.blade.php -->
@if (isset($link) && trim($link) != '')
    @if (isset($class) && trim($class) != '')
        <a class="{{ $class }}" target="_blank" href="{{ $GLOBALS['SL']->getTwitShareLnk($link,
            ((isset($title)) ? $title : ''), ((isset($hashtags)) ? $hashtags : '')) }}" >
            <i class="fa fa-twitter" aria-hidden="true"></i> <span>
            @if (isset($btnText) && trim($btnText) != '') {!! $btnText !!} @else Tweet @endif </span></a>
    @else
        <a class="socialTwit" target="_blank" href="{{ $GLOBALS['SL']->getTwitShareLnk($link,
            ((isset($title)) ? $title : ''), ((isset($hashtags)) ? $hashtags : '')) }}" style="color: #FFF;">
            <div><img src="/survloop/uploads/spacer.gif"><i class="fa fa-twitter" aria-hidden="true"></i> <span>Tweet</span><div></a>
    @endif
@endif