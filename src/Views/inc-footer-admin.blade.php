<!-- resources/views/vendor/survloop/inc-footer-admin.blade.php -->

@if (isset($GLOBALS['SL']->sysOpts['site-name']))
    <a id="hidivBtnAdmFoot" class="hidivBtn" href="javascript:;"
        ><i class="fa fa-creative-commons" aria-hidden="true"></i> 
        @if (isset($GLOBALS["SL"]->sysOpts["app-license-snc"]) 
            && $GLOBALS["SL"]->sysOpts["app-license-snc"] != date("Y"))
            {{ $GLOBALS["SL"]->sysOpts["app-license-snc"] }}-{{ date("Y") }}
        @else {{ date("Y") }} 
        @endif </a>
    <div id="hidivAdmFoot" class="disNon">
        @if (trim($GLOBALS['SL']->sysOpts['app-license-img']) != '')
            <a href="{{ $GLOBALS['SL']->sysOpts['app-license-url'] }}" target="_blank" 
                ><img height=33 border=0 align=left class="mT5 mR10" alt="License"
                src="{{ $GLOBALS['SL']->sysOpts['app-license-img'] }}"></a>
        @endif
        All specifications for database designs and 
        user experience (form tree map) are made available
        by <a href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" target="_blanK" 
            >{{ $GLOBALS['SL']->sysOpts['site-name'] }}</a> <br />under the
        <a href="{{ $GLOBALS['SL']->sysOpts['app-license-url'] }}" target="_blank" 
            >{{ $GLOBALS['SL']->sysOpts['app-license'] }}</a>, 
            @if (isset($GLOBALS["SL"]->sysOpts["app-license-snc"]) 
                && $GLOBALS["SL"]->sysOpts["app-license-snc"] != date("Y"))
                {{ $GLOBALS["SL"]->sysOpts["app-license-snc"] }}-{{ date("Y") }}.
            @else {{ date("Y") }}. 
            @endif
        <nobr>Database powered by 
        <a href="https://Survloop.org" target="_blank">Survloop</a></nobr>
    </div>
    <div class="p15"></div>
@endif