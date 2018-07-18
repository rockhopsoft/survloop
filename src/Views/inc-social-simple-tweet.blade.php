<!-- resources/views/vendor/survloop/inc-social-simple-tweet.blade.php -->
@if (isset($link) && trim($link) != '')
    <a class="socialTwit" href="{{ $GLOBALS['SL']->getTwitShareLnk($link, ((isset($title)) ? $title : '')) }}"
        target="_blank" style="color: #FFF;"><img src="/survloop/uploads/spacer.gif">
        <i class="fa fa-twitter" aria-hidden="true"></i> <span>Tweet</span></a>
@endif