<!-- resources/views/vendor/survloop/inc-footer-master.blade.php -->
@if (isset($GLOBALS['SL']->sysOpts['site-name']))
    <div id="footerLinks">
        <center>
        <div class="w50"><hr></div>
        <nobr><a href="{{ $GLOBALS['SL']->sysOpts['logo-url'] }}" class="fPerc125"
            >{{ $GLOBALS['SL']->sysOpts['site-name'] }}</a></nobr>
        <div class="mT5 slGrey">is powered by the 
        <a href="https://SurvLoop.org" target="_blank">SurvLoop</a>
        open data engine.</div>
        <br /><br />
        </center>
    </div>
@endif