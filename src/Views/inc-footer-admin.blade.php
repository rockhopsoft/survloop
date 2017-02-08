<!-- resources/views/vendor/survloop/inc-footer-admin.blade.php -->

<div class="pL20">
    <div id="admFootLegal" class="f14">
        @if (trim($GLOBALS['DB']->sysOpts['app-license-img']) != '')
            <a href="{{ $GLOBALS['DB']->sysOpts['app-license-url'] }}" target="_blank" 
                ><img src="{{ $GLOBALS['DB']->sysOpts['app-license-img'] }}" height=37 border=0 align=left class="mT5 mR10" ></a>
        @endif
        <i>All specifications for database designs and user experience (form tree map) are made available
        by <a href="{{ $GLOBALS['DB']->sysOpts['logo-url'] }}" target="_blanK" 
            >{{ $GLOBALS['DB']->sysOpts['site-name'] }}</a> <br />under the
        <a href="{{ $GLOBALS['DB']->sysOpts['app-license-url'] }}" target="_blank" 
            >{{ $GLOBALS['DB']->sysOpts['app-license'] }}</a>, {{ date("Y") }}.
        <br /><nobr><span class="gry9 ">Powered by</span>
        <a href="https://databasingmodels.com/" target="_blank" class="f18">SurvLoop</a></nobr></i>
    </div>
</div>