<!-- resources/views/vender/survloop/elements/inc-meta-seo.blade.php -->
@if (isset($GLOBALS["SL"]) 
    && isset($GLOBALS["SL"]->sysOpts) 
    && isset($GLOBALS["SL"]->sysOpts["meta-title"]))

    <title>{{ $GLOBALS["SL"]->sysOpts["meta-title"] }}</title>
    <meta name="description" content="{{ $GLOBALS['SL']->sysOpts['meta-desc'] }}" />
@if (!$GLOBALS["SL"]->isPdfView())
    <meta name="keywords" content="{{ $GLOBALS['SL']->sysOpts['meta-keywords'] }}" />
    
    <link rel="shortcut icon" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}{{ $GLOBALS['SL']->sysOpts['shortcut-icon'] }}" />
    <link rel="image_src" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}{{ 
        $GLOBALS['SL']->sysOpts['meta-img'] }}">
    
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ $GLOBALS['SL']->sysOpts['meta-title'] }}" />
    <meta property="og:description" content="{{ $GLOBALS['SL']->sysOpts['meta-desc'] }}" />
    <meta property="og:url" content="https://{{ $_SERVER['HTTP_HOST'] }}{!! 
        $_SERVER['REQUEST_URI'] !!}" />
    <meta property="og:site_name" content="{{ $GLOBALS['SL']->sysOpts['site-name'] }}" />
    <meta property="og:image" content="{{ $GLOBALS['SL']->sysOpts['app-url'] }}{{ 
        $GLOBALS['SL']->sysOpts['meta-img'] }}" />
    
    <meta name="twitter:card" content="summary_large_image">
    @if (isset($GLOBALS['SL']->sysOpts['twitter']) 
        && !in_array(trim($GLOBALS['SL']->sysOpts['twitter']), ['', '@']))
    <meta name="twitter:site" content="{{ $GLOBALS['SL']->sysOpts['twitter'] }}">
    <meta name="twitter:creator" content="{{ $GLOBALS['SL']->sysOpts['twitter'] }}">
    @endif
    <meta name="twitter:title" content="{{ $GLOBALS['SL']->sysOpts['meta-title'] }}"/>
    <meta name="twitter:description" content="{{ $GLOBALS['SL']->sysOpts['meta-desc'] }}"/>
    <meta name="twitter:domain" content="{{ $GLOBALS['SL']->sysOpts['site-name'] }}"/>
    <meta name="twitter:image" content="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}{{ $GLOBALS['SL']->sysOpts['meta-img'] }}">
@endif

@endif
