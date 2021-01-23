<?php
// Check for globals required to load this master template
if (!isset($GLOBALS["SL"])) {
    $request = new Illuminate\Http\Request;
    $GLOBALS["SL"] = new RockHopSoft\Survloop\Controllers\Globals\Globals($request, 1, 1, 1);
}
$GLOBALS["SL"]->logSiteSessPage();
$isDashLayout = ((isset($admMenu) && trim($admMenu) != '') 
    || (isset($belowAdmMenu) && trim($belowAdmMenu) != ''));
$bodyBg = (isset($GLOBALS["SL"]->treeRow->tree_opts) 
    && $GLOBALS["SL"]->treeRow->tree_opts%67 == 0);

$isWsyiwyg = false;
if (isset($needsWsyiwyg) && $needsWsyiwyg) {
    $isWsyiwyg = true;
}
if (isset($GLOBALS["SL"]->x["needsWsyiwyg"]) 
    && $GLOBALS["SL"]->x["needsWsyiwyg"]) {
    $isWsyiwyg = true;
}

if (!$GLOBALS["SL"]->isPdfView()) {
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml"><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">'
    . view('vendor.survloop.elements.inc-meta-seo')->render();
} else {
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml"><head>';
}
?>
@if (!$GLOBALS["SL"]->isPdfView())
    {!! view(
        'vendor.survloop.inc-master-head',
        [ "isWsyiwyg" => $isWsyiwyg ]
    )->render() !!}

    @if (isset($GLOBALS['SL']->sysOpts) 
        && isset($GLOBALS['SL']->sysOpts['header-code']))
        {!! $GLOBALS['SL']->sysOpts['header-code'] !!}
    @endif

    @section('headCode')
    @show

    @if (!isset($admMenu))
        {!! view(
            'vendor.survloop.elements.inc-matomo-analytics'
        )->render() !!}
    @endif
@endif
</head>
<body {!! $GLOBALS['SL']->getBodyParams() !!} 
    @if ($isDashLayout) class="bodyDash" 
    @elseif ($bodyBg) class="bgFnt" 
    @endif >
@if (!$GLOBALS["SL"]->isPdfView())
    <div class="nodeAnchor"><a name="top"></a></div>
    <div class="hidden"><a href="#maincontent">Skip to Main Content</a></div>
    <div id="absDebug"></div>
    <div id="dialogPop" title=""></div>
@endif
@if (isset($bodyTopCode)) {!! $bodyTopCode !!} @endif

@if ((!isset($isPrint) || !$isPrint) 
    && (!isset($isFrame) || !$isFrame)
    && !$GLOBALS["SL"]->isPdfView()
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
        && (!isset($isPrint) || !$isPrint))

        <table border=0 cellpadding=0 cellspacing=0 class="w100 h100"><tr>
        <td id="leftSide" 
            @if ($GLOBALS['SL']->openAdmMenuOnLoad()) class="leftSide"
            @else class="leftSideCollapse"
            @endif >
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
                <div id="admBgAjax0"></div>
                <div id="admBgAjax1"></div>
                <div id="admBgAjax2"></div>
            </div>
        </td><td id="mainBody" 
            class="w100 h100 @if ($isDashLayout) mainBodyDash @endif ">
        
    @endif

    <div id="main" class="">

    <div id="mainNav">
        <div id="mainNavWrap">
            <div class="fL">
                {!! view(
                    'vendor.survloop.inc-master-logo'
                )->render() !!}
            </div>
            <a id="topNavSearchBtn" 
                class="fL slNavLnk mLn15" href="javascript:;"
                ><i class="fa fa-search mT3" aria-hidden="true"></i></a>
            <div class="fR taR">
                <div id="myNavBar"></div>
            </div>
            <div class="fC"></div>
        </div>
        <div id="mainNav2">
        @if (trim($GLOBALS["SL"]->pageNav2) != '')
            {!! $GLOBALS["SL"]->pageNav2 !!}
        @endif
        </div>
    </div>
    <div id="headClear"></div>
    <div class="headGap">
        <img src="/survloop/uploads/spacer.gif" border=0 alt="" >
    </div>
    {!! view(
        'vendor.survloop.master-search',
        [ "isDashLayout" => $isDashLayout ]
    )->render() !!}

    <noscript><div class="alert alert-dismissible alert-warning">
        <b>Warning: It looks like you have JavaScript disabled.
        @if (isset($GLOBALS['SL']->sysOpts['site-name'])) 
            {{ $GLOBALS['SL']->sysOpts['site-name'] }}
        @endif
        requires JavaScript to give you the best possible experience.</b>
    </div></noscript>

    <!-- SessMsg -->

    <div id="nondialog">
    
@else
    <div id="isPrint"></div>
@endif <?php /* end not print */ ?>

@if ((!isset($isFrame) || !$isFrame) && $isDashLayout)

    @if ((isset($isPrint) && $isPrint) || $GLOBALS["SL"]->isPdfView())
    
        <br />
        <div class="container">
            <div class="p5 pL20">
                <img src="{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}" 
                    alt="Link back to main website" height=75 border=0
                    title="Link back to main website" >
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
            @if (isset($GLOBALS['SL']->sysOpts) 
                && isset($GLOBALS['SL']->sysOpts["footer-admin"]))
                {!! $GLOBALS['SL']->sysOpts["footer-admin"] !!}
            @endif
        </div>
    @endif

@else

    @if (isset($content)) {!! $content !!} @endif
    @yield('content')
    
@endif

@if ((!isset($isPrint) || !$isPrint) && (!isset($isFrame) || !$isFrame) 
    && !$GLOBALS["SL"]->isPdfView()
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

    @if (((!isset($isFrame) || !$isFrame) && $isDashLayout) 
        && (!isset($isPrint) || !$isPrint) 
        && !$GLOBALS["SL"]->isPdfView())

            </td>
        </tr></table>

    @endif

@else

@endif <?php /* end not print or frame */ ?>

{!! view(
    'vendor.survloop.inc-master-foot-scripts',
    [
        "isWsyiwyg" => $isWsyiwyg,
        "admMenu"   => ((isset($admMenu)) ? $admMenu : null)
    ]
)->render() !!}

</body>
</html>