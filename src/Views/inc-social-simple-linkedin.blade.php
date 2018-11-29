<!-- resources/views/vendor/survloop/inc-social-simple-linkedin.blade.php -->
@if (isset($link) && trim($link) != '')
    @if (isset($class) && trim($class) != '')
        <a class="{{ $class }}" target="_blank" href="{{ $GLOBALS['SL']->getLinkedinShareLnk($link,
            ((isset($title)) ? $title : ''), ((isset($hashtags)) ? $hashtags : '')) }}" >
            @if (isset($btnText) && trim($btnText) != '')
                <i class="fa fa-linkedin" aria-hidden="true"></i> <span>{!! $btnText !!}</span>
            @else <span>Link</span> <i class="fa fa-linkedin" aria-hidden="true"></i> @endif </a>
    @else
        <a class="socialTwit" target="_blank" href="{{ $GLOBALS['SL']->getLinkedinShareLnk($link,
            ((isset($title)) ? $title : '')) }}" style="color: #FFF;">
            <div><img src="/survloop/uploads/spacer.gif"><i class="fa fa-linkedin" aria-hidden="true"></i> 
            <span>Link</span><div></a>
    @endif
@endif