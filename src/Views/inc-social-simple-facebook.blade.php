<!-- resources/views/vendor/survloop/inc-social-simple-facebook.blade.php -->
@if (isset($link) && trim($link) != '')
    <a class="socialFace" href="{{ $GLOBALS['SL']->getFacebookShareLnk($link) }}" target="_blank" style="color: #FFF;"
        ><img src="/survloop/uploads/spacer.gif">
        <i class="fa fa-facebook-square" aria-hidden="true"></i> <span>Share</span></a>
@endif