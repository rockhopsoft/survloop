<!-- resources/views/vendor/survloop/inc-color-picker.blade.php -->
<div class="row">
    <div class="col-md-5">
        <input type="text" name="{{ $fldName }}" id="{{ $fldName }}ID" value="{!! $preSel !!}"
            class="colorPickFld form-control @if (isset($xtraClass)) {{ $xtraClass }} @endif " autocomplete="off"
            {!! $GLOBALS["SL"]->tabInd() !!}>
    </div>
    <div id="{{ $fldName }}ColorSwatch" class="col-md-7 round5 slBoxShd crsrPntr colorPickFldSwatch" 
        style="background: {!! $preSel !!};"><img src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/spacer.png" 
            border=0 height=35 width=1 style="background: none;" >
    </div>
</div>
<div id="colorPick{{ $fldName }}" class="p10"></div>
