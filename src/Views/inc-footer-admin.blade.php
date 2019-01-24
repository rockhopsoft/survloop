<!-- resources/views/vendor/survloop/inc-footer-admin.blade.php -->

@if (isset($GLOBALS['SL']->sysOpts['site-name']))
    <div id="admFootLegal">
        @if (trim($GLOBALS['SL']->sysOpts['app-license-img']) != '')
            <a href="{{ $GLOBALS['SL']->sysOpts['app-license-url'] }}" target="_blank" ><img height=33 border=0 
                align=left class="mT5 mL15 mR10" src="{{ $GLOBALS['SL']->sysOpts['app-license-img'] }}" alt="License"
                ></a>
        @endif
        All specifications for database designs and user experience (form tree map) are made available
        by <a href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" target="_blanK" 
            >{{ $GLOBALS['SL']->sysOpts['site-name'] }}</a> <br />under the
        <a href="{{ $GLOBALS['SL']->sysOpts['app-license-url'] }}" target="_blank" 
            >{{ $GLOBALS['SL']->sysOpts['app-license'] }}</a>, {{ date("Y") }}.
        <nobr>Database powered by <a href="https://SurvLoop.org" target="_blank">SurvLoop</a></nobr>
    </div>
@endif