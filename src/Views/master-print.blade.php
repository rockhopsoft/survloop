<!-- Stored in resources/views/masterPrint.blade.php -->

<!doctype html><html xmlns:fb="http://www.facebook.com/2008/fbml"><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    @if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts['meta-title']))
        <title>{{ $GLOBALS['SL']->sysOpts['meta-title'] }}</title>
        <meta name="description" content="{{ $GLOBALS['SL']->sysOpts['meta-desc'] }}" />
        <meta name="keywords" content="{{ $GLOBALS['SL']->sysOpts['meta-keywords'] }}" />
        
        <meta property="og:locale" content="en_US" />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="{{ $GLOBALS['SL']->sysOpts['meta-title'] }}" />
        <meta property="og:description" content="{{ $GLOBALS['SL']->sysOpts['meta-desc'] }}" />
        <meta property="og:url" content="https://{{ $_SERVER['HTTP_HOST'] }}{{ $_SERVER['REQUEST_URI'] }}" />
        <meta property="og:site_name" content="{{ $GLOBALS['SL']->sysOpts['site-name'] }}" />
        
        <meta name="twitter:card" content="summary"/>
        <meta name="twitter:title" content="{{ $GLOBALS['SL']->sysOpts['meta-title'] }}"/>
        <meta name="twitter:description" content="{{ $GLOBALS['SL']->sysOpts['meta-desc'] }}"/>
        <meta name="twitter:domain" content="{{ $GLOBALS['SL']->sysOpts['site-name'] }}"/>
    
        <link rel="shortcut icon" href="{{ $GLOBALS['SL']->sysOpts['shortcut-icon'] }}" />
        <link rel="image_src" href="{{ $GLOBALS['SL']->sysOpts['meta-img'] }}">
    @endif
    
    <script src="https://use.typekit.net/exy7luu.js"></script>
    <script>try{Typekit.load();}catch(e){}</script>
    
    <link rel="stylesheet" href="/survloop/font-awesome/css/font-awesome.min.css">
    
    <link rel="stylesheet" href="/survloop/jquery-ui-1.11.4/jquery-ui.css">
    <script src="/survloop/jquery-2.1.4.min.js"></script>
    <script src="/survloop/jquery-ui-1.11.4/jquery-ui.min.js"></script>
    
    <link href="/survloop/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/survloop/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
    
    <script type="text/javascript" src="/survloop/scripts-lib.js"></script>
    <script type="text/javascript" src="/survloop/scripts.js"></script>
    <link rel="stylesheet" type="text/css" href="/survloop/style.css">
    <link rel="stylesheet" type="text/css" href="/{{ strtolower($GLOBALS['SL']->sysOpts['cust-abbr']) }}/style.css">
    @if (file_exists(public_path().'/survloop/sys.css'))
    <link rel="stylesheet" type="text/css" href="/survloop/sys.css">
    @endif
    
    @section('headCode')
            
    @show
  </head>
<body>
<script src="/survloop/bootstrap/js/bootstrap.min.js"></script>
@if (!isset($hideWrap) || !$hideWrap)
    <nav class="navbar navbar-inverse" style="-moz-border-radius: 0px; border-radius: 0px;">
        <div class="container">
            <center><a href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" id="headLogoLong"
                ><img src="{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}" border=0 ></a></center>
        </div>
    </nav>
@endif

<div id="printBodyWrap">

@yield('content')

@if (isset($content)) 
    {!! $content !!}
@endif

@if (!isset($hideWrap) || !$hideWrap)
    <br /><br /><br />
    {!! $GLOBALS['SL']->sysOpts["footer-master"] !!}
@endif
                     
</div> <!-- printBodyWrap -->

<div class="disNon"><iframe id="hidFrameID" name="hidFrame" src="" ></iframe></div>

<?php /* un-comment after testing
<script>
window.ga=function(){ga.q.push(arguments)};ga.q=[];ga.l=+new Date;
ga('create','UA-69502156-1','auto');ga('send','pageview')
</script>
<script src="https://www.google-analytics.com/analytics.js" async defer></script>
*/ ?>
</body>
</html>