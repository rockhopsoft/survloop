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
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/jquery-2.1.4.min.js"></script>
@if ((isset($needsJqUi) && $needsJqUi) || true)
    <link rel="stylesheet" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/jquery-ui-1.11.4/jquery-ui.css">
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/jquery-ui-1.11.4/jquery-ui.min.js"></script>
@endif
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
@if (isset($needsNavWizard) && $needsNavWizard)
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/nav-wizard.bootstrap.css" rel="stylesheet">
@endif
    <link rel="stylesheet" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/font-awesome/css/font-awesome.min.css">
@if (isset($needsWsyiwyg) && $needsWsyiwyg)
    <link href="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.2/summernote.css" rel="stylesheet">
    <script src="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.2/summernote.js"></script>
@endif
    <script type="text/javascript" src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/scripts-lib.js"></script>
@if (file_exists(public_path() . '/' . strtolower($GLOBALS['SL']->sysOpts['cust-abbr']) . '/sys.js'))
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/{{ 
        strtolower($GLOBALS['SL']->sysOpts['cust-abbr']) }}/sys.js"></script>
@else
    <script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/sys.js"></script>
@endif
@if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts['header-code']))
    {!! $GLOBALS['SL']->sysOpts['header-code'] !!}
@endif
@section('headCode')
@show
@if (file_exists(public_path() . '/' . strtolower($GLOBALS['SL']->sysOpts['cust-abbr']) . '/sys.css'))
    <link rel="stylesheet" type="text/css" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/{{ 
        strtolower($GLOBALS['SL']->sysOpts['cust-abbr']) }}/sys.css">
@else
    <link rel="stylesheet" type="text/css" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/sys.css">
@endif
</head>
<body>
<script src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/bootstrap/js/bootstrap.min.js"></script>
@if (isset($pageJStop) && trim($pageJStop) != '')
    <script type="text/javascript"> {!! $pageJStop !!} </script>
@endif
@if (isset($GLOBALS['SL']->pageJStop) && trim($GLOBALS['SL']->pageJStop) != '')
    <script type="text/javascript"> {!! $GLOBALS['SL']->pageJStop !!} setTimeout("printHeadBar(0)", 1); </script>
@endif
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
@if (isset($bodyTopCode))
    {!! $bodyTopCode !!}
@endif
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        @if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts["logo-url"]))
            <a id="logoLrg" class="pull-left" href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" 
                @if (file_exists(substr($GLOBALS['SL']->sysOpts['logo-img-lrg'], 1))) 
                    ><img src="{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}" class="disIn" border=0 
                    alt="Link back to main website" title="Link back to main website" >
                @else style="margin-top: 0px;"><b class="slBlueLight">{{ $GLOBALS['SL']->sysOpts['site-name'] }}</b> 
                @endif </a>
            <a id="logoMed" class="pull-left" href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" 
                @if (file_exists(substr($GLOBALS['SL']->sysOpts['logo-img-md'], 1))) 
                    ><img src="{{ $GLOBALS['SL']->sysOpts['logo-img-md'] }}" class="disIn" border=0 
                    alt="Link back to main website" title="Link back to main website" >
                @else style="margin-top: 0px;"><b class="slBlueLight">{{ $GLOBALS['SL']->sysOpts['site-name'] }}</b> 
                @endif </a>
            <a id="logoSm" class="pull-left" href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" 
                @if (file_exists(substr($GLOBALS['SL']->sysOpts['logo-img-sm'], 1))) 
                    ><img src="{{ $GLOBALS['SL']->sysOpts['logo-img-sm'] }}" border=0 
                    alt="Link back to main website" title="Link back to main website" >
                @else 
                    style="margin-top: 0px;"><b class="slBlueLight">{{ $GLOBALS['SL']->sysOpts['site-name'] }}</b> 
                @endif </a>
        @endif
        @if (isset($GLOBALS['SL']->sysOpts['show-logo-title']) 
            && trim($GLOBALS['SL']->sysOpts['show-logo-title']) == 'On')
            <a id="logoTxt" href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}" class="pull-left"
                >{{ $GLOBALS['SL']->sysOpts['site-name'] }}</a>
        @endif
        <div id="slNavLeft" class="pull-left"></div>
        <a id="navBurger" href="#top" title="Login to pick up where you left off." 
            class="pull-right"><i class="fa fa-bars" aria-hidden="true"></i></a>
        @if (isset($user) && isset($user->id) && $user->id > 0)
            <a class="pull-right slNavLnk" href="/logout">Logout, 
            @if (strpos($user->name, 'Session#') === false)
                {{ $user->name }}
            @else
                {{ substr($user->email, 0, strpos($user->email, '@')) }}
            @endif
            </a>
            @if ($user->hasRole('administrator'))
                <a class="pull-right slNavLnk" href="/dashboard" title="Admin Dashboard">Dashboard</a>
            @endif
        @else
            <a class="pull-right slNavLnk" href="/register" title="Sign up for much more!">Sign Up</a>
            <a class="pull-right slNavLnk" href="/login" title="Login to pick up where you left off.">Login</a>
        @endif
    </div>
    <div id="progWrap"></div>
</nav>
<div class="clearfix"></div>
<div id="headGap"><img src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/spacer.gif" border=0 ></div>
<div id="headBar">@if (isset($headBar) && trim($headBar) != '') {!! $headBar !!} @endif</div>

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
<div id="nondialog">

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
                $(function() {
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
                        @if (isset($admMenuHideable) && $admMenuHideable)
                            class="pT20 mT5"
                        @else
                            class="mT10"
                        @endif
                        >
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
                        @yield('content')
                        @if (isset($content))
                            {!! $content !!}
                        @endif
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

    <div id="bodyContain" class="container">
    
        @yield('complaintNav')
        
        @if (isset($content))
            {!! $content !!}
        @endif
        
        @yield('content')
    
    </div>
    
@endif

</div> <!-- end nondialog -->

@if (!isset($admMenu) && !isset($belowAdmMenu)) 
    @if (isset($footOver) && trim($footOver) != '') {!! $footOver !!}
    <?php /* moved to part of the form/page output process
    @elseif (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts["footer-master"]))
        {!! $GLOBALS['SL']->sysOpts["footer-master"] !!}
    */ ?>
    @endif
@endif

<div class="disNon"><iframe id="hidFrameID" name="hidFrame" src="" ></iframe></div>

<script type="text/javascript">
@if (isset($pageJSextra) && trim($pageJSextra) != '')
    {!! $pageJSextra !!}
@endif

@if (isset($needsWsyiwyg) && $needsWsyiwyg)
    $(document).ready(function() {
        $('#summernote').summernote({
            height: 400,                 // set editor height
            minHeight: null,             // set minimum height of editor
            maxHeight: null,             // set maximum height of editor
            focus: true                  // set focus to editable area after initializing summernote
        });
        $('.note-codable').on('blur', function() {
            if ($('#summernote').summernote('codeview.isActivated')) {
                $('#summernote').summernote('codeview.deactivate');
            }
        });
    });
@endif
</script>
@if (isset($hasFbWidget) && $hasFbWidget)
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
@endif
@if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts["google-analytic"])
    && strpos($GLOBALS['SL']->sysOpts["app-url"], 'homestead.app') === false)
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