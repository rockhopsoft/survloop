<!DOCTYPE html><html lang="en" xmlns:fb="http://www.facebook.com/2008/fbml"><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
@if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts['meta-title']))
    <title>{{ $GLOBALS['SL']->sysOpts['meta-title'] }}</title>
    <meta name="description" content="{{ $GLOBALS['SL']->sysOpts['meta-desc'] }}" />
    <meta name="keywords" content="{{ $GLOBALS['SL']->sysOpts['meta-keywords'] }}" />
    
    <link rel="shortcut icon" href="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}{{ $GLOBALS['SL']->sysOpts['shortcut-icon'] }}" />
    <link rel="image_src" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}{{ $GLOBALS['SL']->sysOpts['meta-img'] }}">
    
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ $GLOBALS['SL']->sysOpts['meta-title'] }}" />
    <meta property="og:description" content="{{ $GLOBALS['SL']->sysOpts['meta-desc'] }}" />
    <meta property="og:url" content="https://{{ $_SERVER['HTTP_HOST'] }}{{ $_SERVER['REQUEST_URI'] }}" />
    <meta property="og:site_name" content="{{ $GLOBALS['SL']->sysOpts['site-name'] }}" />
    <meta property="og:image" content="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}{{ $GLOBALS['SL']->sysOpts['meta-img'] }}" />
    
    
    <meta name="twitter:card" content="summary_large_image">
    @if (isset($GLOBALS['SL']->sysOpts['twitter']) && !in_array(trim($GLOBALS['SL']->sysOpts['twitter']), ['', '@']))
    <meta name="twitter:site" content="{{ $GLOBALS['SL']->sysOpts['twitter'] }}">
    <meta name="twitter:creator" content="{{ $GLOBALS['SL']->sysOpts['twitter'] }}">
    @endif
    <meta name="twitter:title" content="{{ $GLOBALS['SL']->sysOpts['meta-title'] }}"/>
    <meta name="twitter:description" content="{{ $GLOBALS['SL']->sysOpts['meta-desc'] }}"/>
    <meta name="twitter:domain" content="{{ $GLOBALS['SL']->sysOpts['site-name'] }}"/>
    <meta name="twitter:image" content="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}{{ $GLOBALS['SL']->sysOpts['meta-img'] }}">
    
@endif

@if (!$GLOBALS["SL"]->REQ->has("debug"))
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/sys-all.min.css" rel="stylesheet">
@else
    <link rel="stylesheet" type="text/css" href="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/sys1.min.css?v={{ $GLOBALS['SL']->sysOpts['log-css-reload'] }}">
    <link rel="stylesheet" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/jquery-ui.min.css">
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/sys2.min.css?v={{ $GLOBALS['SL']->sysOpts['log-css-reload'] }}">
@endif

@if (isset($needsWsyiwyg) && $needsWsyiwyg)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" crossorigin="anonymous" 
        integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4"></script>
@endif

@if (!$GLOBALS["SL"]->REQ->has("debug"))
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/sys-all.min.js" type="text/javascript"></script>
@else 
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/sys1.min.js?v={{ $GLOBALS['SL']->sysOpts['log-css-reload'] }}"></script>
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/jquery.min.js"></script>
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/jquery-ui.min.js" type="text/javascript"></script>
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/bootstrap.min.js"></script>
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/scripts-lib.js" type="text/javascript"></script>
    {!! $GLOBALS['SL']->debugPrintExtraFilesCSS() !!}
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/sys2.min.js?v={{ 
        $GLOBALS['SL']->sysOpts['log-css-reload'] }}"></script>
@endif

<link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/css/fork-awesome.min.css" rel="stylesheet">

@if ((isset($needsCharts) && $needsCharts) 
    || (isset($GLOBALS["SL"]->x["needsCharts"]) && $GLOBALS["SL"]->x["needsCharts"]))
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/Chart.bundle.min.js"></script>
@endif
@if ((isset($needsPlots) && $needsPlots) 
    || (isset($GLOBALS["SL"]->x["needsPlots"]) && $GLOBALS["SL"]->x["needsPlots"]))
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/plotly.min.js"></script>
@endif
<?php /* @if (isset($needsWsyiwyg) && $needsWsyiwyg)
    <link rel="stylesheet" type="text/css" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/content-tools.min.css">
@endif */ ?>

@if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts['header-code']))
    {!! $GLOBALS['SL']->sysOpts['header-code'] !!}
@endif
@section('headCode')
@show
</head>
<body {!! $GLOBALS["SL"]->getBodyParams() !!} >
<a name="top"></a>
<div class="hidden"><a href="#maincontent">Skip to Main Content</a></div>
<div id="absDebug"></div>
<div id="dialogPop" title=""></div>
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
@if (isset($bodyTopCode)) {!! $bodyTopCode !!} @endif


@if ((!isset($isPrint) || !$isPrint) && (!isset($isFrame) || !$isFrame)
    && (!$GLOBALS["SL"]->REQ->has("frame") || intVal($GLOBALS["SL"]->REQ->get("frame")) != 1))

<div id="mySidenav">
    <div class="headGap">
        <img src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/uploads/spacer.gif" border=0 alt="spacer" >
    </div>
    <ul id="mySideUL" class="nav flex-column">
    @if (isset($navMenu) && sizeof($navMenu) > 0)
        @foreach ($navMenu as $i => $arr) <li class="nav-item"><a href="{{ $arr[1] }}">{{ $arr[0] }}</a></li> @endforeach
    @endif
    @if (isset($sideNavLinks) && trim($sideNavLinks) != '') {!! $sideNavLinks !!} @endif
    </ul>
</div>
<div id="main">

<div class="row flex-nowrap fixed-top clearfix">
    <div class="col-md-4 taL bgWht">
    @if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts["logo-url"]))
        <a id="slLogo" href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" 
            ><img id="slLogoImg" src="{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}" class="disIn" border=0 
            alt="{{ $GLOBALS['SL']->sysOpts['site-name'] }} Home" 
            title="{{ $GLOBALS['SL']->sysOpts['site-name'] }} Home" ></a>
    @endif
    @if (isset($GLOBALS['SL']->sysOpts['show-logo-title']) 
        && intVal($GLOBALS['SL']->sysOpts['show-logo-title']) == 1)
        <a id="logoTxt" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}" class="navbar-brand"
            >{{ $GLOBALS['SL']->sysOpts['site-name'] }}</a>
    @endif
    </div><div class="col-md-8" id="myNavBar">
        <a id="navBurger" title="Show Navigation Menu" class="float-right disBlo" onClick="toggleNav();"
            href="javascript:;" ><i class="fa fa-bars" aria-hidden="true"></i></a>
        <a id="navBurgerClose" class="float-right disNon" onclick="closeNav()" href="javascript:;" 
            ><i class="fa fa-times" aria-hidden="true"></i></a>
    </div>
</div>
<div id="headClear"></div>
<div class="headGap">
    <img src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/uploads/spacer.gif" border=0 alt="spacer" >
</div>
<div id="progWrap"></div>

<noscript><div class="alert alert-dismissible alert-warning">
    <b>Warning: It looks like you have Javascript disabled. {{ $GLOBALS['SL']->sysOpts['site-name'] }} 
    requires Javascript to give you the best possible experience.</b>
</div></noscript>

<!-- SessMsg -->

<div id="nondialog">
    
@endif <?php /* end not print */ ?>

@if ((!isset($isFrame) || !$isFrame) && (isset($admMenu) || isset($belowAdmMenu)))
    
    @if ((isset($isPrint) && $isPrint) || (isset($GLOBALS["SL"]->x["isPrintPDF"]) && $GLOBALS["SL"]->x["isPrintPDF"]))
    
        <br />
        <div class="container">
            <div class="p5 pL20">
                <img src="{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}" alt="Link back to main website" 
                    title="Link back to main website" height=75 border=0 >
            </div>
            @if (isset($content)) {!! $content !!} @endif
            @yield('content')
        </div>
        
    @else
        
        <table border=0 cellpadding=0 cellspacing=0 class="w100 h100"><tr>
        <td id="leftSide">
            <div id="leftSideWdth"></div>
            <div id="leftSideWrap">
                <div id="leftAdmMenu">
                    @if (isset($GLOBALS["SL"]->x["admMenuCustom"]) 
                        && trim($GLOBALS["SL"]->x["admMenuCustom"]) != '')
                        <div id="admMenuCustom" class="w100 h100 disBlo">
                            {!! $GLOBALS["SL"]->x["admMenuCustom"] !!}
                        </div>
                        <div id="admMenuNotCustom" class="w100 disNon">
                    @endif
                    <form id="dashSearchFrmID" name="dashSearchForm" action="{{ $GLOBALS['SL']->getSrchUrl() }}" 
                        method="get">
                        <nobr><input type="text" name="s" id="admSrchFld" class="form-control" placeholder="Search...">
                        <div id="dashSearchBtnID"><a onClick="document.dashSearchForm.submit();" href="javascript:;"
                            ><i class="fa fa-search" aria-hidden="true"></i></a></div></nobr>
                    </form>
                    <div class="admMenu row">
                        @if (isset($admMenu)) {!! $admMenu !!} @endif
                    </div>
                    <div id="adminMenuExtra">
                        @yield('belowAdmMenu')
                        @if (isset($belowAdmMenu)) {!! $belowAdmMenu !!} @endif
                    </div>
                    @if (isset($GLOBALS["SL"]->x["admMenuCustom"]) 
                        && trim($GLOBALS["SL"]->x["admMenuCustom"]) != '')
                        </div> <!-- end #admMenuNotCustom -->
                    @endif
                </div>
                <div id="menuColpsWrap">
                    <a id="menuColpsBtn" href="javascript:;"
                        ><nobr><i class="fa fa-caret-square-o-left" aria-hidden="true"></i> Collapse Menu</nobr></a>
                    <a id="menuUnColpsBtn" href="javascript:;"
                        ><i class="fa fa-caret-square-o-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </td><td id="mainBody" class="w100 h100">
            <div class="container-fluid">
                @if (isset($content)) {!! $content !!} @endif
                @yield('content')
                @if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts["footer-admin"]))
                    {!! $GLOBALS['SL']->sysOpts["footer-admin"] !!}
                @endif
            </div>
        </td>
    </tr></table>
    @endif

@else

        @if (isset($content)) {!! $content !!} @endif
        @yield('content')
    
@endif

@if ((!isset($isPrint) || !$isPrint) && (!isset($isFrame) || !$isFrame) 
    && (!isset($GLOBALS["SL"]->x["isPrintPDF"]) || !$GLOBALS["SL"]->x["isPrintPDF"])
    && (!$GLOBALS["SL"]->REQ->has("frame") || intVal($GLOBALS["SL"]->REQ->get("frame")) != 1))
    
        @if (!isset($admMenu) && !isset($belowAdmMenu))
            @if (isset($footOver) && trim($footOver) != '') {!! $footOver !!}
            @elseif (isset($GLOBALS["SL"]->sysOpts["footer-master"])
                && trim($GLOBALS["SL"]->sysOpts["footer-master"]) != '')
                {!! $GLOBALS["SL"]->sysOpts["footer-master"] !!}
            @endif
        @endif
        
    </div> <!-- end nondialog -->
    <div id="dialog">
        <div class="card">
            <div class="card-header">
                <h2 id="dialogTitle"></h2>
                <a class="dialogClose btn btn-sm btn-secondary" href="javascript:;"
                    ><i class="fa fa-times" aria-hidden="true"></i></a>
                <div class="fC"></div>
            </div>
            <div class="card-body"><div id="dialogBody"></div></div>
        </div>
    </div>
    
@else
    </div> <!-- end nondialog -->
@endif <?php /* end not print or frame */ ?>

</div> <!-- end #main (non-offcanvas-menu) -->

<div class="disNon"><iframe id="hidFrameID" name="hidFrame" src="" height=1 width=1 ></iframe></div>
<div class="imgPreload">
@forelse ($GLOBALS["SL"]->listPreloadImgs() as $src) <img src="{{ $src }}" border=0 > @empty @endforelse
</div>

@if (isset($needsWsyiwyg) && $needsWsyiwyg)
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/summernote.css" rel="stylesheet">
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/summernote.min.js"></script>
@endif

<?php /* @if (isset($needsWsyiwyg) && $needsWsyiwyg)
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/survloop/ContentTools-master/build/content-tools.min.js"></script>
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/survloop/ContentTools-master/build/editor.js"></script>
@endif */ ?>

@if (isset($GLOBALS['SL']->pageSCRIPTS) && trim($GLOBALS['SL']->pageSCRIPTS) != '')
    {!! $GLOBALS['SL']->pageSCRIPTS !!}
@endif
<script id="dynamicJS" type="text/javascript">
@if (isset($GLOBALS['SL']->pageJAVA) && trim($GLOBALS['SL']->pageJAVA) != '')
    {!! $GLOBALS['SL']->pageJAVA !!}
@endif
{!! $GLOBALS['SL']->getXtraJs() !!}
@if ((isset($GLOBALS['SL']->pageAJAX) && trim($GLOBALS['SL']->pageAJAX) != ''))
    $(document).ready(function(){ {!! $GLOBALS['SL']->pageAJAX !!} }); 
@endif
@if (isset($GLOBALS["SL"]->x["pageView"]) && in_array($GLOBALS["SL"]->x["pageView"], ['pdf', 'full-pdf']))
    @if ($GLOBALS["SL"]->x["pageView"] != 'full-pdf')
        alert("Make sure you are logged in, so that the full complaint is visible here. Then use your browser's print tools to save this page as a PDF. For best results, use Chrome or Firefox.");
    @endif
    setTimeout("window.print()", 1000);
@endif
</script>

@if (isset($hasFbWidget) && $hasFbWidget)
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
@endif
@if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts["google-analytic"])
    && strpos($GLOBALS['SL']->sysOpts["app-url"], 'homestead.test') === false && !isset($admMenu))
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={!! $GLOBALS['SL']->sysOpts['google-analytic'] 
        !!}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{!! $GLOBALS["SL"]->sysOpts["google-analytic"] !!}');
    </script>
@endif
</body>
</html>