<!-- resources/views/vendor/survloop/forms/inc-color-picker-ajax.blade.php -->
<i>Set To System Color:</i>
<div class="row">
    <div class="col-6">
    @forelse ($sysColors as $i => $color)
        @if (floor(sizeof($sysColors)/2) == $i)
            </div><div class="col-6">
        @endif
        <div id="{{ $fldName }}ColorSwatch{!! str_replace('#', '', strtoupper($color)) !!}" 
            class="w100 round5 slBoxShd crsrPntr colorPickFldSwatchBtn" style="background: {!! $color !!};">
            <img src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/uploads/spacer.gif" alt=""
                border=0 height=35 width=1 style="background: none;" >
        </div>
    @empty
    @endforelse
    </div>
</div>
<div class="mT10"><i>Set To Custom Color:</i></div>
<div class="row mB20">
    <div class="col-6">
        <nobr><input type="text" name="{{ $fldName }}Custom" id="{{ $fldName }}CustomID" 
            class="form-control @if (isset($xtraClass)) {{ $xtraClass }} @endif disIn colorPickCustomFld" 
            autocomplete="off" style="width: 90px;" {!! $GLOBALS["SL"]->tabInd() !!}
            @if ($isCustom) value="{!! $preSel !!}" @endif >
        <a href="javascript:;" id="{{ $fldName }}SetCustomColor" 
            class="colorPickCustomBtn btn btn-sm btn-secondary">Set</a></nobr>
    </div>
    <div class="col-6">
        <div id="{{ $fldName }}CustomColor" class="w100 round5 slBoxShd" 
            @if ($isCustom) style="background: {!! $preSel !!};" @endif >
            <img src="{{ $GLOBALS['SL']->sysOpts['app-url'] }}/survloop/uploads/spacer.gif" alt="" 
                border=0 height=35 width=1 style="background: none;" >
        </div>
    </div>
</div>
