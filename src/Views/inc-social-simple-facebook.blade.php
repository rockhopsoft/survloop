<!-- resources/views/vendor/survloop/inc-social-simple-facebook.blade.php -->
@if (isset($link) && trim($link) != '')
    @if (isset($class) && trim($class) != '')
        <a class="{{ $class }}" target="_blank" href="{{ $GLOBALS['SL']->getFacebookShareLnk($link) }}" >
            <i class="fa fa-facebook-square" aria-hidden="true"></i> <span>
            @if (isset($btnText) && trim($btnText) != '') {!! $btnText !!} @else Share @endif </span></a>
    @else
        <a class="socialFace" href="{{ $GLOBALS['SL']->getFacebookShareLnk($link) }}" target="_blank" style="color: #FFF;"
            ><div><img src="/survloop/uploads/spacer.gif" alt="">
            <i class="fa fa-facebook-square" aria-hidden="true"></i> <span>Share</span></div></a>
    @endif
@endif