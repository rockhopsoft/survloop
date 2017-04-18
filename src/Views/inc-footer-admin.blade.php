<!-- resources/views/vendor/survloop/inc-footer-admin.blade.php -->

@if (isset($GLOBALS['SL']->sysOpts['site-name']))
    <div class="pB20">
        <div id="admFootLegal" class="f14 taR">
            @if (trim($GLOBALS['SL']->sysOpts['app-license-img']) != '')
                <a href="{{ $GLOBALS['SL']->sysOpts['app-license-url'] }}" target="_blank" ><img height=37 border=0 
                    align=right class="mT5 mL10" src="{{ $GLOBALS['SL']->sysOpts['app-license-img'] }}" ></a>
            @endif
            All specifications for database designs and user experience (form tree map) are made available
            by <a href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" target="_blanK" 
                >{{ $GLOBALS['SL']->sysOpts['site-name'] }}</a> <br />under the
            <a href="{{ $GLOBALS['SL']->sysOpts['app-license-url'] }}" target="_blank" 
                >{{ $GLOBALS['SL']->sysOpts['app-license'] }}</a>, {{ date("Y") }}.
            <nobr>Database powered by <a href="https://github.com/wikiworldorder/survloop" 
                target="_blank">SurvLoop</a></nobr>
        </div>
    </div>
@endif