<!-- generated from resources/views/vendor/survloop/master-logo.blade.php -->
@if (isset($GLOBALS['SL']->sysOpts) 
    && isset($GLOBALS['SL']->sysOpts["logo-url"]))
    <div id="slLogoWrap">
    @if (isset($GLOBALS['SL']->sysOpts['logo-img-lrg'])
        && trim($GLOBALS['SL']->sysOpts['logo-img-lrg']) != '')
        <a id="slLogo" href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" 
            ><img id="slLogoImg" border=0 
                src="{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}" 
                alt="{{ $GLOBALS['SL']->sysOpts['site-name'] }} Home" 
                title="{{ $GLOBALS['SL']->sysOpts['site-name'] }} Home" >
         @if (isset($GLOBALS['SL']->sysOpts['logo-img-sm']) 
            && trim($GLOBALS['SL']->sysOpts['logo-img-sm']) != ''
            && trim($GLOBALS['SL']->sysOpts['logo-img-sm']) 
                != trim($GLOBALS['SL']->sysOpts['logo-img-lrg']))
            <img id="slLogoImgSm" border=0 
                src="{{ $GLOBALS['SL']->sysOpts['logo-img-sm'] }}" 
                alt="{{ $GLOBALS['SL']->sysOpts['site-name'] }} Home" 
                title="{{ $GLOBALS['SL']->sysOpts['site-name'] }} Home" >
         @endif
        </a>
        @if (isset($GLOBALS['SL']->sysOpts['show-logo-title']) 
            && intVal($GLOBALS['SL']->sysOpts['show-logo-title']) == 1)
            <a id="logoTxt" href="/" class="navbar-brand"
                >{{ $GLOBALS['SL']->sysOpts['site-name'] }}</a>
        @endif
    @else
        <a href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" 
            id="slLogo" class="navbar-brand" 
            >{{ $GLOBALS['SL']->sysOpts['site-name'] }}</a>
    @endif
    </div>
@endif
