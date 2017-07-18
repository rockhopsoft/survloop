<!-- resources/views/vendor/survloop/dbdesign-legal.blade.php -->

<div id="admFootLegal" class="taR">
    <a href="//creativecommons.org/licenses/by-sa/3.0/" target="_blank" 
        ><img src="/survloop/creative-commons-by-sa-88x31.png" border=0 align=right class="mT5 mL10" ></a>
    <i>All specifications for database designs and user experience (form tree map) are made available<br />
    @if (isset($sysOpts["parent-company"]) && trim($sysOpts["parent-company"]) != '')
        by <a href="{{ $sysOpts['parent-website'] }}" target="_blanK" >{{ $sysOpts["parent-company"] }}</a> 
    @else
        by <a href="{{ $sysOpts['logo-url'] }}" target="_blanK" >{{ $sysOpts["site-name"] }}</a> 
    @endif
    under the <a href="http://creativecommons.org/licenses/by-sa/3.0/" target="_blank" 
        >Creative Commons Attribution-ShareAlike License</a>, {{ date("Y") }}.</i>
</div>
