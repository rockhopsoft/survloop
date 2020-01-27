<!-- system-one-style.blade.php -->
@if ($opt == 'font-main') 
    <div class="mB20 mT10 w100"><label class="w100">
        <b>{!! $val[1] !!}</b>
        @if (trim($val[0]) != '') <div class="fR slGrey mRn10">eg. "{!! $val[0] !!}"</div> @endif
        <textarea name="sty-{{ $opt }}" class="form-control w100 ntrStp slTab"
            style="height: 40px;" {!! $GLOBALS['SL']->tabInd() !!} 
            >@if (isset($sysStyles[$opt])){!! $sysStyles[$opt] !!}@endif</textarea>
    </label></div>
@else
    <div class="mB20 mT10">
        <div class="row"><div class="col-10">
            <label class="w100"><b>{!! $val[1] !!}</b>
            @if (trim($val[0]) != '') <div class="fR slGrey mRn10">eg. "{!! $val[0] !!}"</div> @endif
            </label>
        </div></div>
        {!! view(
            'vendor.survloop.forms.inc-color-picker', 
            [
                'fldName' => 'sty-' . $opt,
                'preSel'  => ((isset($sysStyles[$opt])) ? strtoupper($sysStyles[$opt]) : '')
            ]
        )->render() !!}
    </div>
@endif