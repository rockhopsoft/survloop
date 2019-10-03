<?php
// Check for globals required to load this master template
if (!isset($GLOBALS["SL"])) {
    $GLOBALS["SL"] = new SurvLoop\Controllers\Globals\Globals(new Illuminate\Http\Request, 1, 1, 1);
}
$GLOBALS["SL"]->logSiteSessPage();
$isDashLayout = ((isset($admMenu) && trim($admMenu) != '') 
    || (isset($belowAdmMenu) && trim($belowAdmMenu) != ''));
$bodyBg = (isset($GLOBALS["SL"]->treeRow->TreeOpts) 
    && $GLOBALS["SL"]->treeRow->TreeOpts%67 == 0);

?><!DOCTYPE html><html lang="en" xmlns:fb="http://www.facebook.com/2008/fbml"><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {!! view('vendor.survloop.elements.inc-meta-seo')->render() !!}
@if (!isset($GLOBALS["SL"]) || !$GLOBALS["SL"]->REQ->has("debug"))
    <link href="/sys1.min.css" rel="stylesheet" type="text/css">
    <link href="/sys2.min.css" rel="stylesheet" type="text/css">
    <link href="/css/fork-awesome.min.css" rel="stylesheet" type="text/css">
    <script id="sysJs" src="/sys1.min.js" type="text/javascript"></script>
    <script id="sysJs2" src="/sys2.min.js" type="text/javascript"></script>
@else
    <link href="/sys1.css?v={{ $GLOBALS['SL']->sysOpts['log-css-reload'] }}" rel="stylesheet" type="text/css">
    <link href="/jquery-ui.min.css" rel="stylesheet" type="text/css">
    <link href="/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="/css/fork-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/sys2.css?v={{ $GLOBALS['SL']->sysOpts['log-css-reload'] 
        }}" rel="stylesheet" type="text/css">
    <script src="/jquery.min.js" type="text/javascript"></script>
    <script src="/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/bootstrap.min.js" type="text/javascript"></script>
    <!--- <script src="/survloop/parallax.min.js" type="text/javascript"></script> --->
    <script id="sysJs" src="/survloop/scripts-lib.js" type="text/javascript"></script>
    {!! $GLOBALS['SL']->debugPrintExtraFilesCSS() !!}
    <script id="sysJs2" src="/sys2.min.js?v={{ $GLOBALS['SL']->sysOpts['log-css-reload'] }}"></script>
@endif
@if (isset($needsWsyiwyg) && $needsWsyiwyg)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" 
        crossorigin="anonymous" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4">
        </script>
@endif
@if ((isset($needsCharts) && $needsCharts) 
    || (isset($GLOBALS["SL"]->x["needsCharts"]) && $GLOBALS["SL"]->x["needsCharts"]))
    <script src="/Chart.bundle.min.js"></script>
@endif
@if ((isset($needsPlots) && $needsPlots) 
    || (isset($GLOBALS["SL"]->x["needsPlots"]) && $GLOBALS["SL"]->x["needsPlots"]))
    <script src="/plotly.min.js"></script>
@endif
<?php /* @if (isset($needsWsyiwyg) && $needsWsyiwyg)
    <link rel="stylesheet" type="text/css" href="/content-tools.min.css">
@endif */ ?>
@if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts['header-code']))
    {!! $GLOBALS['SL']->sysOpts['header-code'] !!}
@endif
@section('headCode')
@show
@if (!isset($admMenu))
    {!! view('vendor.survloop.elements.inc-matomo-analytics')->render() !!}
@endif
</head>
<body @if ($isDashLayout) class="bodyDash" 
    @elseif ($bodyBg) class="bgFnt" 
    @endif {!! $GLOBALS['SL']->getBodyParams() !!} >
<a name="top"></a>
<div class="hidden">
    <a href="#maincontent">Skip to Main Content</a>
</div>
<div id="absDebug"></div>
<div id="dialogPop" title=""></div>
@if (isset($bodyTopCode)) {!! $bodyTopCode !!} @endif

@if ((!isset($isPrint) || !$isPrint) 
    && (!isset($isFrame) || !$isFrame)
    && (!isset($GLOBALS["SL"]->x["isPrintPDF"]) 
        || !$GLOBALS["SL"]->x["isPrintPDF"])
    && (!$GLOBALS["SL"]->REQ->has("frame") 
        || intVal($GLOBALS["SL"]->REQ->get("frame")) != 1))

<div id="mySidenav">
    <div class="headGap">
        <img src="/survloop/uploads/spacer.gif" border=0 alt="" >
    </div>
    <ul id="mySideUL" class="nav flex-column">
    @if (isset($navMenu) && sizeof($navMenu) > 0)
        @foreach ($navMenu as $i => $arr)
            @if (trim($arr[0]) != '' && trim($arr[1]) != '')
                <li class="nav-item">
                    <a href="{{ $arr[1] }}">{{ $arr[0] }}</a>
                </li>
            @endif
        @endforeach
    @endif
    @if (isset($sideNavLinks) && trim($sideNavLinks) != '') 
        {!! $sideNavLinks !!}
    @endif
    </ul>
</div>


@if (((!isset($isFrame) || !$isFrame) && $isDashLayout) 
    && (!isset($isPrint) || !$isPrint) 
    && (!isset($GLOBALS["SL"]->x["isPrintPDF"]) 
        || !$GLOBALS["SL"]->x["isPrintPDF"]))

<table border=0 cellpadding=0 cellspacing=0 class="w100 h100"><tr>
<td id="leftSide" class="leftSide">
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
            <div class="admMenu w100">
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
    </div>
</td><td id="mainBody" 
    class="w100 h100 @if ($isDashLayout) mainBodyDash @endif ">
    
@endif

<div id="main" class="">

<div id="mainNav">
    <div class="fL">
    @if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts["logo-url"]))
        <div id="slLogoWrap"><a id="slLogo" href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" 
            ><img id="slLogoImg" src="{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}" border=0 
            alt="{{ $GLOBALS['SL']->sysOpts['site-name'] }} Home" 
            title="{{ $GLOBALS['SL']->sysOpts['site-name'] }} Home" >
         @if (isset($GLOBALS['SL']->sysOpts['logo-img-sm']) && trim($GLOBALS['SL']->sysOpts['logo-img-sm']) != ''
             && $GLOBALS['SL']->sysOpts['logo-img-sm'] != $GLOBALS['SL']->sysOpts['logo-img-lrg'])
            <img id="slLogoImgSm" src="{{ $GLOBALS['SL']->sysOpts['logo-img-sm'] }}" border=0 
                alt="{{ $GLOBALS['SL']->sysOpts['site-name'] }} Home" 
                title="{{ $GLOBALS['SL']->sysOpts['site-name'] }} Home" >
         @endif
         </a></div>
    @endif
    @if (isset($GLOBALS['SL']->sysOpts['show-logo-title']) 
        && intVal($GLOBALS['SL']->sysOpts['show-logo-title']) == 1)
        <a id="logoTxt" href="/" class="navbar-brand">{{ $GLOBALS['SL']->sysOpts['site-name'] }}</a>
    @endif
    </div>
    {!! view('vendor.survloop.master-search')->render() !!}
    <div class="fR taR">
        <div id="myNavBar"></div>
    </div>
</div>
<div id="headClear"></div>
<div class="headGap">
    <img src="/survloop/uploads/spacer.gif" border=0 alt="" >
</div>

<noscript><div class="alert alert-dismissible alert-warning">
    <b>Warning: It looks like you have JavaScript disabled.
    @if (isset($GLOBALS['SL']->sysOpts['site-name'])) {{ $GLOBALS['SL']->sysOpts['site-name'] }} @endif
    requires JavaScript to give you the best possible experience.</b>
</div></noscript>

<!-- SessMsg -->

<div id="nondialog">
    
@else
    <div id="isPrint"></div>
@endif <?php /* end not print */ ?>

@if ((!isset($isFrame) || !$isFrame) && $isDashLayout)

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
        
        @if ($isDashLayout && isset($admMenuTabs))
            <div id="adminMenuTopTabs">{!! $admMenuTabs !!}</div>
        @endif
        <div class="container-fluid">
            @if (isset($content)) {!! $content !!} @endif
            @yield('content')
            @if (isset($GLOBALS['SL']->sysOpts) && isset($GLOBALS['SL']->sysOpts["footer-admin"]))
                {!! $GLOBALS['SL']->sysOpts["footer-admin"] !!}
            @endif
        </div>
    @endif

@else

        @if (isset($content)) {!! $content !!} @endif
        @yield('content')
    
@endif

@if ((!isset($isPrint) || !$isPrint) && (!isset($isFrame) || !$isFrame) 
    && (!isset($GLOBALS["SL"]->x["isPrintPDF"]) || !$GLOBALS["SL"]->x["isPrintPDF"])
    && (!$GLOBALS["SL"]->REQ->has("frame") || intVal($GLOBALS["SL"]->REQ->get("frame")) != 1))
    
        @if (!$isDashLayout)
            @if (isset($footOver) && trim($footOver) != '')
                {!! $footOver !!}
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
                <a id="dialogCloseID" href="javascript:;"
                    class="dialogClose btn btn-sm btn-secondary" 
                    ><i class="fa fa-times" aria-hidden="true"></i></a>
                <div class="fC"></div>
            </div>
            <div class="card-body"><div id="dialogBody"></div></div>
        </div>
    </div>
</div> <!-- end #main (non-offcanvas-menu) -->

@else

@endif <?php /* end not print or frame */ ?>


@if (((!isset($isFrame) || !$isFrame) && $isDashLayout) && (!isset($isPrint) || !$isPrint) && (!isset($GLOBALS["SL"]->x["isPrintPDF"]) || !$GLOBALS["SL"]->x["isPrintPDF"]))

        </td>
    </tr></table>

@endif


<div class="disNon">
    <iframe id="hidFrameID" name="hidFrame" src="" height=1 width=1 ></iframe>
</div>
<div class="imgPreload">
@forelse ($GLOBALS["SL"]->listPreloadImgs() as $src)
    <img src="{{ $src }}" border=0 alt="" >
@empty
@endforelse
</div>
@if (isset($GLOBALS['SL']->pageSCRIPTS) && trim($GLOBALS['SL']->pageSCRIPTS) != '')
    {!! $GLOBALS['SL']->pageSCRIPTS !!}
@endif
@if ((isset($GLOBALS['SL']->pageJAVA) && trim($GLOBALS['SL']->pageJAVA) != '') || ((isset($GLOBALS['SL']->pageAJAX) && trim($GLOBALS['SL']->pageAJAX) != '')))
    <script id="dynamicJS" type="text/javascript" defer >
    @if (isset($GLOBALS['SL']->pageJAVA) && trim($GLOBALS['SL']->pageJAVA) != '')
        {!! $GLOBALS['SL']->pageJAVA !!}
    @endif
    @if ((isset($GLOBALS['SL']->pageAJAX) && trim($GLOBALS['SL']->pageAJAX) != ''))
        $(document).ready(function(){ {!! $GLOBALS['SL']->pageAJAX !!} }); 
    @endif
    </script>
@endif

@if ((isset($GLOBALS["SL"]->pageView) && in_array($GLOBALS["SL"]->pageView, ['pdf', 'full-pdf']))
    || (isset($GLOBALS["SL"]->x["isPrintPDF"]) && $GLOBALS["SL"]->x["isPrintPDF"]))
    <script id="dynamicJS" type="text/javascript" defer >
    @if ($GLOBALS["SL"]->pageView != 'full-pdf')
        alert("Make sure you are logged in, so that the full complaint is visible here. Then use your browser's print tools to save this page as a PDF. For best results, use Chrome or Firefox.");
    @endif
    setTimeout("window.print()", 1000);
    </script>
@endif


<?php /* @if (isset($needsWsyiwyg) && $needsWsyiwyg)
    <script defer src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/survloop/ContentTools-master/build/content-tools.min.js"></script>
    <script defer src="{{ $GLOBALS['SL']->sysOpts['app-url'] 
        }}/survloop/ContentTools-master/build/editor.js"></script>
@endif */ ?>
@if (isset($needsWsyiwyg) && $needsWsyiwyg)
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.11/summernote-bs4.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.11/summernote-bs4.js"></script>
    <?php /* <link href="/summernote.css" rel="stylesheet"> <script defer src="/summernote.min.js"></script> */ ?>
@endif
@if (!isset($admMenu))
    {!! view('vendor.survloop.elements.inc-google-analytics')->render() !!}
@endif
</body>
</html>