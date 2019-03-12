<!-- resources/views/vendor/survloop/elements/dbdesign-legal.blade.php -->

<div id="admFootLegal" class=" @if (isset($alignRight) && $alignRight) taR @endif ">
    <a href="//creativecommons.org/licenses/by-sa/3.0/" target="_blank" 
        ><img src="/survloop/uploads/creative-commons-by-sa-88x31.png" border=0 
        alt="Creative Commons Attribution-ShareAlike License"
        @if (isset($alignRight) && $alignRight) align=right class="mT5 mL10" @else align=left class="mT5 mR10" @endif
        ></a>
    <i>All specifications for database designs and user experience (form tree map) are made available<br />
    @if (isset($sysOpts["parent-company"]) && trim($sysOpts["parent-company"]) != '')
        by <a href="{{ $sysOpts['parent-website'] }}" target="_blanK" >{{ $sysOpts["parent-company"] }}</a> 
    @else
        by <a href="{{ $sysOpts['logo-url'] }}" target="_blanK" >{{ $sysOpts["site-name"] }}</a> 
    @endif
    under the <a href="http://creativecommons.org/licenses/by-sa/3.0/" target="_blank" 
        >Creative Commons Attribution-ShareAlike License</a>, {{ date("Y") }}.</i>
</div>
