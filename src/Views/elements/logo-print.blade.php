<!-- resources/views/vendor/survloop/elements/logo-print.blade.php -->
@if (isset($sysOpts) && isset($sysOpts["logo-url"]))
    <a href="{{ $sysOpts['logo-url'] }}" id="slLogo" @if (isset($w100) && $w100) class="w100" @endif 
        ><img id="slLogoImg" border=0 @if (isset($w100) && $w100) class="w100" @endif 
            src="{{ $sysOpts['logo-img-lrg'] }}" 
            alt="{{ $sysOpts['site-name'] }} Logo (link back home)" 
            title="{{ $sysOpts['site-name'] }} Logo (link back home)" ></a>
@else
    <h1 class="slBlueDark m0">{{ $sysOpts['site-name'] }}</h1>
@endif