<!-- resources/views/vendor/survloop/inc-footer-admin.blade.php -->

<div class="pB20">
    <div id="admFootLegal" class="f14">
        @if (trim($GLOBALS['SL']->sysOpts['app-license-img']) != '')
            <a href="{{ $GLOBALS['SL']->sysOpts['app-license-url'] }}" target="_blank" 
                ><img src="{{ $GLOBALS['SL']->sysOpts['app-license-img'] }}" height=37 border=0 align=left class="mT5 mR10" ></a>
        @endif
        All specifications for database designs and user experience (form tree map) are made available
        by <a href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" target="_blanK" 
            >{{ $GLOBALS['SL']->sysOpts['site-name'] }}</a> <br />under the
        <a href="{{ $GLOBALS['SL']->sysOpts['app-license-url'] }}" target="_blank" 
            >{{ $GLOBALS['SL']->sysOpts['app-license'] }}</a>, {{ date("Y") }}.
        <nobr>Powered by <a href="http://SurvLoop.org" target="_blank">SurvLoop</a></nobr>
    </div>
</div>