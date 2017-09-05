<!doctype html><html xmlns:fb="http://www.facebook.com/2008/fbml"><head>
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
    
    <meta name="twitter:card" content="summary"/>
    <meta name="twitter:title" content="{{ $GLOBALS['SL']->sysOpts['meta-title'] }}"/>
    <meta name="twitter:description" content="{{ $GLOBALS['SL']->sysOpts['meta-desc'] }}"/>
    <meta name="twitter:domain" content="{{ $GLOBALS['SL']->sysOpts['site-name'] }}"/>

@endif

@if (!$GLOBALS["SL"]->REQ->has("debug"))
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/sys-all.min.css" rel="stylesheet">
@else
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/survloop/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
    @if ((isset($needsJqUi) && $needsJqUi) || true)
    <link rel="stylesheet" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/jquery-ui-1.12.1/jquery-ui.min.css">
    @endif
    <link rel="stylesheet" type="text/css" href="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/sys.min.css?v={{ $GLOBALS['SL']->sysOpts['log-css-reload'] }}">
@endif

@if (!$GLOBALS["SL"]->REQ->has("debug"))
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/sys-all.min.js" type="text/javascript"></script>
@else 
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/jquery-3.2.1.min.js"></script>
    @if ((isset($needsJqUi) && $needsJqUi) || true)
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/survloop/jquery-ui-1.12.1/jquery-ui.min.js" type="text/javascript"></script>
    @endif
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/bootstrap/js/bootstrap.min.js"></script>
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/scripts-lib.js" type="text/javascript"></script>
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/sys.min.js?v={{ $GLOBALS['SL']->sysOpts['log-css-reload'] }}"></script>
@endif

@if (isset($needsWsyiwyg) && $needsWsyiwyg)
    <script src="https://cloud.tinymce.com/stable/tinymce.min.js?apiKey={{ env('TinyMCE_API_KEY') }}"></script>
    <?php /* <link rel="stylesheet" type="text/css" href="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/survloop/ContentTools-master/build/content-tools.min.css">
    <?php /* <script src="//cdn.ckeditor.com/4.7.1/standard/ckeditor.js"></script>
    <script src="//cdn.ckeditor.com/4.7.1/full/ckeditor.js"></script> */ ?>
@endif

@if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts['header-code']))
    {!! $GLOBALS['SL']->sysOpts['header-code'] !!}
@endif
@section('headCode')
@show
</head>
<body>
<a name="top"></a>
<div class="hidden"><a href="#maincontent">Skip to Main Content</a></div>
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
<?php /* @if (isset($needsWsyiwyg) && $needsWsyiwyg)
<!-- Core build with no theme, formatting, non-essential modules -->
<link href="//cdn.quilljs.com/1.2.3/quill.core.css" rel="stylesheet">
<script src="//cdn.quilljs.com/1.2.3/quill.core.js"></script>

<!-- Main Quill library -->
<script src="//cdn.quilljs.com/1.2.3/quill.js"></script>
<script src="//cdn.quilljs.com/1.2.3/quill.min.js"></script>

<!-- Theme included stylesheets -->
<link href="//cdn.quilljs.com/1.2.3/quill.snow.css" rel="stylesheet">
<link href="//cdn.quilljs.com/1.2.3/quill.bubble.css" rel="stylesheet">
@endif */ ?>
@if (isset($bodyTopCode))
    {!! $bodyTopCode !!}
@endif


@if (!isset($isPrint) || !$isPrint)

<div id="mySidenav">
    <ul id="mySideUL" class="nav nav-sidebar">
    @if (isset($navMenu) && sizeof($navMenu) > 0)
        @foreach ($navMenu as $i => $arr) <li><a href="{{ $arr[1] }}">{{ $arr[0] }}</a></li> @endforeach
    @endif
    @if (isset($sideNavLinks) && trim($sideNavLinks) != '') {!! $sideNavLinks !!} @endif
    </ul>
</div>
<div id="main">

<nav id="myNavBar" class="navbar navbar-inverse navbar-fixed-top">
    <div id="myNavBarIn" class="container-fluid">
        @if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts["logo-url"]))
            <a id="slLogo" class="pull-left" href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" 
                <?php /* @if (file_exists(substr($GLOBALS['SL']->sysOpts['logo-img-lrg'], 1))) */ ?>
                    ><img id="slLogoImg" src="{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}" class="disIn" border=0 
                    alt="{{ $GLOBALS['SL']->sysOpts['site-name'] }} Logo (link back home)" 
                    title="{{ $GLOBALS['SL']->sysOpts['site-name'] }} Logo (link back home)" >
                <?php /* @else style="margin-top: 0px;"><b class="slBlueLight">{{ $GLOBALS['SL']->sysOpts['site-name'] }}</b> 
                @endif */ ?> </a>
        @endif
        @if (isset($GLOBALS['SL']->sysOpts['show-logo-title']) 
            && trim($GLOBALS['SL']->sysOpts['show-logo-title']) == 'On')
            <a id="logoTxt" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}" class="pull-left"
                >{{ $GLOBALS['SL']->sysOpts['site-name'] }}</a>
        @endif
        <a id="navBurger" title="Show Navigation Menu" class="pull-right disBlo" onClick="toggleNav();"
            href="javascript:;" ><i class="fa fa-bars" aria-hidden="true"></i></a>
        <a id="navBurgerClose" class="pull-right disNon" onclick="closeNav()" href="javascript:;" 
            ><i class="fa fa-times" aria-hidden="true"></i></a>
    </div>
    <div id="progWrap"></div>
</nav>
<div id="headClear" class="clearfix"></div>
<div id="headGap"><img src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/spacer.gif" border=0 ></div>

<noscript><div class="alert alert-dismissible alert-warning">
    <b>Warning: It looks like you have Javascript disabled. {{ $GLOBALS['SL']->sysOpts['site-name'] }} 
    requires Javascript to give you the best possible experience.</b>
</div></noscript>

<!-- SessMsg -->

<div id="nondialog">
    
@endif <?php /* end not print */ ?>

@if (isset($admMenu) || isset($belowAdmMenu))
    
    @if (isset($isPrint) && $isPrint)
        <br />
        <div class="container">
            <div class="p5 pL20">
                <img src="{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}" alt="Link back to main website" 
                    title="Link back to main website" height=75 border=0 >
            </div>
    @else
        
        @if (isset($admMenuHideable) && $admMenuHideable)
            <div id="admMenuBarsWrap" style="margin: -15px 0px 0px -3px; z-index: 100; position: fixed;">
                <a id="admMenuBars" class="btn btn-lg btn-primary f18" style="padding: 10px 10px 5px 10px;" 
                    href="javascript:void(0)"><i class="fa fa-bars"></i></a>
            </div>
            <script type="text/javascript">
                $(document).ready(function(){
                    $( document ).ready(function() {
                        $("#leftSide").removeClass('col-md-2');
                        $("#leftSide").addClass('disNon');
                        $("#mainBody").removeClass('col-md-10');
                        $("#mainBody").addClass('col-md-12');
                    });
                    $("#admMenuBars").click(function() {
                        if (document.getElementById('leftSide').className == 'disNon')
                        {
                            $("#mainBody").removeClass('col-md-12');
                            $("#mainBody").addClass('col-md-10');
                            $("#leftSide").removeClass('disNon');
                            $("#leftSide").addClass('col-md-2');
                        }
                        else 
                        {
                            $("#leftSide").removeClass('col-md-2');
                            $("#leftSide").addClass('disNon');
                            $("#mainBody").removeClass('col-md-10');
                            $("#mainBody").addClass('col-md-12');
                        }
                    });
                });
            </script>
        @endif
        
        <div class="container-fluid mT10">
            <div class="row">
                <div id="leftSide" class="col-md-2">
                    <div class="disNon"><form class="navbar-form navbar-right"></div>
                        <input type="text" class="form-control" placeholder="Search...">
                    <div class="disNon"></form></div>
                    <div id="leftSideWrap"
                        @if (isset($admMenuHideable) && $admMenuHideable) class="pT20 mT5" @else class="mT10" @endif >
                        <div id="adminMenu" class="row">
                            @if (isset($admMenu)) {!! $admMenu !!} @endif
                        </div>
                        <div id="adminMenuExtra">
                            @yield('belowAdmMenu')
                            @if (isset($belowAdmMenu)) {!! $belowAdmMenu !!} @endif
                        </div>
                    </div>
                </div>
                <div id="mainBody" class="col-md-10">
    @endif
                    <div class="mTn10 pL20">
                        @if (isset($content)) {!! $content !!} @endif
                        @yield('content')
                    </div>
                    
                    @if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts["footer-admin"]))
                        {!! $GLOBALS['SL']->sysOpts["footer-admin"] !!}
                    @endif
            
    @if (isset($isPrint) && $isPrint)    
        </div>
    @else
                </div>
                <div id="rightSide" class="disNon">
                    @yield('rightSide')
                    @if (isset($rightSide)) {!! $rightSide !!} @endif
                </div>
            </div>
        </div>
    @endif

@else

    @if (!isset($hasContain) || !$hasContain) <div id="bodyContain" class="container"> @endif
        @if (isset($content)) {!! $content !!} @endif
        @yield('content')
    @if (!isset($hasContain) || !$hasContain) </div> @endif
    
@endif

@if (!isset($isPrint) || !$isPrint)

</div> <!-- end nondialog -->
<div id="dialog">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h2 id="dialogTitle" class="panel-title"></h2>
            <a class="dialogClose btn btn-sm btn-default" href="javascript:;"
                ><i class="fa fa-times" aria-hidden="true"></i></a>
            <div class="fC"></div>
        </div>
        <div class="panel-body"><div id="dialogBody"></div></div>
    </div>
</div>

@endif <?php /* end not print */ ?>

@if (!isset($admMenu) && !isset($belowAdmMenu)) 
    @if (isset($footOver) && trim($footOver) != '') {!! $footOver !!} @endif
@endif

</div> <!-- end #main (non-offcanvas-menu) -->

<div class="disNon"><iframe id="hidFrameID" name="hidFrame" src="" height=1 width=1 ></iframe></div>

<link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/font-awesome/css/font-awesome.min.css" rel="stylesheet">

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
@if (isset($GLOBALS['SL']->pageAJAX) && trim($GLOBALS['SL']->pageAJAX) != '')
    $(document).ready(function(){ {!! $GLOBALS['SL']->pageAJAX !!} }); 
@endif
</script>

@if (isset($hasFbWidget) && $hasFbWidget)
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
@endif
@if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts["google-analytic"])
    && strpos($GLOBALS['SL']->sysOpts["app-url"], 'homestead.app') === false && !isset($admMenu))
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
      ga('create', '{!! $GLOBALS["SL"]->sysOpts["google-analytic"] !!}', 'auto');
      ga('send', 'pageview');
    </script>
@endif
</body>
</html>