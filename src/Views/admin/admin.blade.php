<!doctype html><html xmlns:fb="http://www.facebook.com/2008/fbml"><head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    @if (isset($GLOBALS["DB"]->sysOpts) && isset($GLOBALS["DB"]->sysOpts["meta-title"]))
		<title>{{ $GLOBALS["DB"]->sysOpts['meta-title'] }}</title>
		<meta name="description" content="{{ $GLOBALS['DB']->sysOpts['meta-desc'] }}" />
		<meta name="keywords" content="{{ $GLOBALS['DB']->sysOpts['meta-keywords'] }}" />
		
		<meta property="og:locale" content="en_US" />
		<meta property="og:type" content="website" />
		<meta property="og:title" content="{{ $GLOBALS['DB']->sysOpts['meta-title'] }}" />
		<meta property="og:description" content="{{ $GLOBALS['DB']->sysOpts['meta-desc'] }}" />
		<meta property="og:url" content="https://{{ $_SERVER['HTTP_HOST'] }}{{ $currPage }}" />
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
	<script type="text/javascript" src="/survloop/scripts-admin.js"></script>
	<link rel="stylesheet" type="text/css" href="/survloop/style.css">
	@if (file_exists(public_path().'/survloop/sys.css'))
    <link rel="stylesheet" type="text/css" href="/survloop/sys.css">
    @endif
    
	@section('headCode')
            
    @show
  </head>
<body><div class="hidden"><a name="#maincontent">Skip to Main Content</a></div>

@if (isset($isPrint) && $isPrint)
	<br />
	<div class="container">
		<div class="p5 pL20">
			<img src="{{ $GLOBALS['DB']->sysOpts['logo-img-lrg'] }}" alt="Link back to main website" title="Link back to main website" 
				height=75 border=0 >
		</div>
@else

	@if (isset($GLOBALS["DB"]->sysOpts))
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					@if (!isset($admTopMenu))
						<a id="logoLrg" class="navbar-brand" href="{{ $GLOBALS['DB']->sysOpts['logo-url'] }}"><img src="{{ $GLOBALS['DB']->sysOpts['logo-img-lrg'] }}" border=0 alt="Link back to main website" title="Link back to main website" ></a>
						<a id="logoMed" class="navbar-brand" href="{{ $GLOBALS['DB']->sysOpts['logo-url'] }}"><img src="{{ $GLOBALS['DB']->sysOpts['logo-img-md'] }}" border=0 alt="Link back to main website" title="Link back to main website" ></a>
					@else
						<a id="logoMed" style="display: block;" class="navbar-brand" href="{{ $GLOBALS['DB']->sysOpts['logo-url'] }}"><img src="{{ $GLOBALS['DB']->sysOpts['logo-img-md'] }}" border=0 alt="Link back to main website" title="Link back to main website" ></a>
					@endif
					<a id="logoSm" class="navbar-brand" href="{{ $GLOBALS['DB']->sysOpts['logo-url'] }}"><img src="{{ $GLOBALS['DB']->sysOpts['logo-img-sm'] }}" border=0 alt="Link back to main website" title="Link back to main website" ></a>
				</div>
				
				<div id="navbar" class="navbar-collapse collapse">
					@if (isset($GLOBALS["DB"]->sysOpts) && isset($GLOBALS["DB"]->sysOpts['nav-admin']))
						{!! $GLOBALS["DB"]->sysOpts["nav-admin"] !!}
					@endif
				</div>
			</div>
		</nav>
	@endif
	
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
	
	
	<div class="container-fluid" style="margin-top: 60px;">
		<div class="row">
			<div id="leftSide" class="col-md-2">
				<div id="leftSideWrap"
					@if (isset($admMenuHideable) && $admMenuHideable)
						class="pT20 mT5"
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
				
				@if (isset($GLOBALS["DB"]->sysOpts) && isset($GLOBALS["DB"]->sysOpts["footer-admin"]))
					{!! $GLOBALS["DB"]->sysOpts["footer-admin"] !!}
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