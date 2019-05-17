<!-- resources/views/vendor/survloop/elements/inc-social-simple-facebook.blade.php -->
@if (isset($link) && trim($link) != '')
    @if (isset($class) && trim($class) != '')
        <a class="{{ $class }}" target="_blank" href="{{ $GLOBALS['SL']->getFacebookShareLnk($link) }}" >
            <i class="fa fa-facebook-square" aria-hidden="true"></i> <span>
            @if (isset($btnText) && trim($btnText) != '') {!! $btnText !!} @else Share @endif </span></a>
    @else
<?php /*
        <a class="socialFace" href="{{ $GLOBALS['SL']->getFacebookShareLnk($link) }}" target="_blank" style="color: #FFF;"
            ><div><i class="fa fa-facebook-square" aria-hidden="true"></i> <span>Share</span></div>
            <img src="/survloop/uploads/spacer.gif" alt="Share on Facebook"></a>
*/ ?>
        <a class="socialFacePng" href="{{ $GLOBALS['SL']->getFacebookShareLnk($link) }}" target="_blank"
            ><img src="/survloop/uploads/facebook-share.png" alt="Share on Facebook"></a>
    @endif
@endif