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
    
    <link rel="stylesheet" href="/survloop/font-awesome/css/font-awesome.min.css">
    
    <link rel="stylesheet" href="/survloop/jquery-ui-1.11.4/jquery-ui.css">
    <script src="/survloop/jquery-2.1.4.min.js"></script>
    <script src="/survloop/jquery-ui-1.11.4/jquery-ui.min.js"></script>
    
    <link href="/survloop/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/survloop/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
    
    <link href="/survloop/nav-wizard.bootstrap.css" rel="stylesheet">
    
    <script type="text/javascript" src="/survloop/scripts-lib.js"></script>
    <script type="text/javascript" src="/survloop/scripts.js"></script>
    <link rel="stylesheet" type="text/css" href="/survloop/style.css">
    @if (file_exists(public_path().'/survloop/sys.css'))
    <link rel="stylesheet" type="text/css" href="/survloop/sys.css">
    @endif
    
    @section('headCode')
            
    @show
  </head>
<body><div class="hidden"><a name="#maincontent">Skip to Main Content</a></div>

@if (isset($hasFbWidget) && $hasFbWidget)
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5&appId=234775309892416";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
@endif

@if (isset($bodyTopCode))
    {!! $bodyTopCode !!}
@endif

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <i class="fa fa-bars wht f18" aria-hidden="true"></i>
            </button>
            @if (isset($GLOBALS["DB"]->sysOpts) && isset($GLOBALS["DB"]->sysOpts["logo-url"]))
                <a id="logoLrg" class="navbar-brand" href="{{ $GLOBALS['DB']->sysOpts['logo-url'] }}" 
                    @if (file_exists(substr($GLOBALS['DB']->sysOpts['logo-img-lrg'], 1))) 
                        ><img src="{{ $GLOBALS['DB']->sysOpts['logo-img-lrg'] }}" class="disIn" border=0 alt="Link back to main website" title="Link back to main website" >
                    @else style="margin-top: 0px;"><b class="slBlueLight">{{ $GLOBALS['DB']->sysOpts['site-name'] }}</b> 
                    @endif </a>
                <a id="logoMed" class="navbar-brand" href="{{ $GLOBALS['DB']->sysOpts['logo-url'] }}" 
                    @if (file_exists(substr($GLOBALS['DB']->sysOpts['logo-img-md'], 1))) 
                        ><img src="{{ $GLOBALS['DB']->sysOpts['logo-img-md'] }}" class="disIn" border=0 alt="Link back to main website" title="Link back to main website" >
                    @else style="margin-top: 0px;"><b class="slBlueLight">{{ $GLOBALS['DB']->sysOpts['site-name'] }}</b> 
                    @endif </a>
                <a id="logoSm" class="navbar-brand" href="{{ $GLOBALS['DB']->sysOpts['logo-url'] }}" 
                    @if (file_exists(substr($GLOBALS['DB']->sysOpts['logo-img-sm'], 1))) 
                        ><img src="{{ $GLOBALS['DB']->sysOpts['logo-img-sm'] }}" border=0 alt="Link back to main website" title="Link back to main website" >
                    @else style="margin-top: 0px;"><b class="slBlueLight">{{ $GLOBALS['DB']->sysOpts['site-name'] }}</b> 
                    @endif </a>
            @endif
            @if (isset($GLOBALS['DB']->sysOpts['show-logo-title']) && trim($GLOBALS['DB']->sysOpts['show-logo-title']) == 'On')
                <a id="logoTxt" href="{{ $GLOBALS['DB']->sysOpts['logo-url'] }}" class="navbar-brand"
                    >{{ $GLOBALS['DB']->sysOpts['site-name'] }}</a>
            @endif
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            @if (isset($GLOBALS["DB"]->sysOpts) && isset($GLOBALS["DB"]->sysOpts["nav-public"])) {!! $GLOBALS["DB"]->sysOpts["nav-public"] !!} @endif
        </div><!--/.nav-collapse -->
    </div>
</nav>
<div class="clearfix"></div>

<div id="headBar"></div>

<div id="bodyContain" class="container">

    @yield('complaintNav')
    
    @if (isset($content))
        {!! $content !!}
    @endif
    
    @yield('content')

</div>

@if (isset($footOver)) {!! $footOver !!}
@elseif (isset($GLOBALS["DB"]->sysOpts) && isset($GLOBALS["DB"]->sysOpts["footer-master"]))
    {!! $GLOBALS["DB"]->sysOpts["footer-master"] !!}
@endif

<div class="disNon"><iframe id="hidFrameID" name="hidFrame" src="" ></iframe></div>

<script src="/survloop/bootstrap/js/bootstrap.min.js"></script>

@if (isset($hasFbWidget) && $hasFbWidget)
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
@endif

<?php /* un-comment after testing
<script>
window.ga=function(){ga.q.push(arguments)};ga.q=[];ga.l=+new Date;
ga('create','UA-69502156-1','auto');ga('send','pageview')
</script>
<script src="https://www.google-analytics.com/analytics.js" async defer></script>
*/ ?>
</body>
</html>