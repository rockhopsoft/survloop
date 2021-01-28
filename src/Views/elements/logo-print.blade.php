<!-- resources/views/vendor/survloop/elements/logo-print.blade.php -->
@if (isset($GLOBALS['SL']->sysOpts) 
	&& isset($GLOBALS['SL']->sysOpts["logo-url"]))
	@if (isset($GLOBALS['SL']->sysOpts['logo-img-lrg'])
		&& trim($GLOBALS['SL']->sysOpts['logo-img-lrg']) != '')
	    <a href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" id="slLogo" 
	    	@if (isset($w100) && $w100) class="w100" @endif 
	        ><img id="slLogoImg" border=0 @if (isset($w100) && $w100) class="w100" @endif 
	            src="{{ $GLOBALS['SL']->sysOpts['logo-img-lrg'] }}" 
	            alt="{{ $GLOBALS['SL']->sysOpts['site-name'] }} Logo (link back home)" 
	            title="{{ $GLOBALS['SL']->sysOpts['site-name'] }} Logo (link back home)" ></a>
	@else
		<a href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" id="slLogo" 
			><h1>{{ $GLOBALS['SL']->sysOpts['site-name'] }}</h1></a>
	@endif
@endif