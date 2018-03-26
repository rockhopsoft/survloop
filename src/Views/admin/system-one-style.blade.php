<!-- system-one-style.blade.php -->
@if ($opt == 'font-main') 
    <div class="mB20 mT10 w100"><label class="w100">
        <h4 class="fL m0">{!! $val[1] !!}</h4>
        @if (trim($val[0]) != '') <div class="fR pT5 slGrey">eg. "{!! $val[0] !!}"</div> @endif
        <textarea name="sty-{{ $opt }}" class="form-control w100 ntrStp slTab" {!! $GLOBALS["SL"]->tabInd() !!} 
            style="height: 40px;">@if (isset($sysStyles[$opt])){!! $sysStyles[$opt] !!}@endif</textarea>
    </label></div>
@else
    <div class="mB20 mT10 w100">
        <label class="w100"><h4 class="fL m0">{!! $val[1] !!}</h4>
        @if (trim($val[0]) != '')
            <div class="fR pT5 slGrey">eg. "{!! $val[0] !!}"</div>
        @endif
        </label>
        {!! view('vendor.survloop.inc-color-picker', [
            'fldName' => 'sty-' . $opt,
            'preSel'  => strtoupper($sysStyles[$opt])
        ])->render() !!}
    </div>
@endif