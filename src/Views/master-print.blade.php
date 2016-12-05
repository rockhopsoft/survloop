<!-- Stored in resources/views/masterPrint.blade.php -->

<!doctype html><html xmlns:fb="http://www.facebook.com/2008/fbml"><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    @if (isset($GLOBALS["DB"]->sysOpts) && isset($GLOBALS["DB"]->sysOpts['meta-title']))
        <title>{{ $GLOBALS["DB"]->sysOpts['meta-title'] }}</title>
        <meta name="description" content="{{ $GLOBALS['DB']->sysOpts['meta-desc'] }}" />
        <meta name="keywords" content="{{ $GLOBALS['DB']->sysOpts['meta-keywords'] }}" />
        
        <meta property="og:locale" content="en_US" />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="{{ $GLOBALS['DB']->sysOpts['meta-title'] }}" />
        <meta property="og:description" content="{{ $GLOBALS['DB']->sysOpts['meta-desc'] }}" />
        <meta property="og:url" content="https://{{ $_SERVER['HTTP_HOST'] }}{{ $_SERVER['REQUEST_URI'] }}" />
        <meta property="og:site_name" content="{{ $GLOBALS['DB']->sysOpts['site-name'] }}" />
        
        <meta name="twitter:card" content="summary"/>
        <meta name="twitter:title" content="{{ $GLOBALS['DB']->sysOpts['meta-title'] }}"/>
        <meta name="twitter:description" content="{{ $GLOBALS['DB']->sysOpts['meta-desc'] }}"/>
        <meta name="twitter:domain" content="{{ $GLOBALS['DB']->sysOpts['site-name'] }}"/>
    
        <link rel="shortcut icon" href="{{ $GLOBALS['DB']->sysOpts['shortcut-icon'] }}" />
        <link rel="image_src" href="{{ $GLOBALS['DB']->sysOpts['meta-img'] }}">
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
    @if (file_exists(public_path().'/survloop/sys.css'))
    <link rel="stylesheet" type="text/css" href="/survloop/sys.css">
    @endif
    
    @section('headCode')
            
    @show
  </head>
<body>
@if (!isset($hideWrap) || !$hideWrap)
    <div id="headBar"><center><a href="https://OpenPoliceComplaints.org" id="headLogoLong"><img src="/images/Flex_Open_1LineBox_v3.svg" border=0 ></a></center></div>
@endif

<div id="printBodyWrap">

@yield('content')

@if (isset($content)) 
    {!! $content !!}
@endif

@if (!isset($hideWrap) || !$hideWrap)
    <br /><br /><br />
    <center>
    <nobr><a href="javascript:void(0)" class="f18">Open Police Complaints</a></nobr>&nbsp;&nbsp;&nbsp;
    <nobr><span class="gry9 f14">powered by</span>&nbsp;&nbsp;&nbsp;
    <a href="https://www.flexyourrights.org/" target="_blank" class="f18">Flex Your Rights</a></nobr>
    <br /><br />
    </center>
@endif

</div> <!-- printBodyWrap -->

<div class="disNon"><iframe id="hidFrameID" name="hidFrame" src="" ></iframe></div>

<script src="/survloop/bootstrap/js/bootstrap.min.js"></script>
<?php /* un-comment after testing
<script>
window.ga=function(){ga.q.push(arguments)};ga.q=[];ga.l=+new Date;
ga('create','UA-69502156-1','auto');ga('send','pageview')
</script>
<script src="https://www.google-analytics.com/analytics.js" async defer></script>
*/ ?>
</body>
</html>