<!-- resources/views/vendor/survloop/forms/inc-color-picker.blade.php -->
<div class="row">
    <div class="col-5">
        <input type="text" name="{{ $fldName }}" id="{{ $fldName }}ID" value="{!! $preSel !!}"
            class="colorPickFld form-control @if (isset($xtraClass)) {{ $xtraClass }} @endif " autocomplete="off"
            {!! $GLOBALS["SL"]->tabInd() !!}>
    </div><div class="col-5">
        <div id="{{ $fldName }}ColorSwatch" class="round5 slBoxShd crsrPntr colorPickFldSwatch" 
            style="background: {!! $preSel !!};">
            <img src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/uploads/spacer.gif" 
                border=0 height=35 width=1 style="background: none;" alt="" >
        </div>
    </div><div class="col-2">
    </div>
</div>
<div id="colorPick{{ $fldName }}" class="p10"></div>
