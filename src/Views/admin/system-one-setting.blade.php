<!-- system-one-setting.blade.php -->
<div class="mB20 mT10 w100"><label class="w100">
    <h4 class="fL m0">{!! $val[0] !!}</h4>
    @if (trim($val[1]) != '')
        <div class="fR pT5 slGrey">eg. "{!! $val[1] !!}"</div>
    @endif
    <div class="fC"></div>
    @if (in_array($opt, ['header-code', 'css-extra-files', 'spinner-code'])) 
        <textarea name="sys-{{ $opt }}" class="form-control w100 ntrStp slTab" {!! $GLOBALS["SL"]->tabInd() !!} 
            autocomplete="off" style="height: 100px; font-family: Courier New; "
            >@if (isset($GLOBALS["SL"]->sysOpts[$opt])){!! $GLOBALS["SL"]->sysOpts[$opt] !!}@endif</textarea>
    @else
        <input type="text" name="sys-{{ $opt }}" class="form-control w100 ntrStp slTab" {!! $GLOBALS["SL"]->tabInd() !!}
            autocomplete="off" @if (isset($GLOBALS["SL"]->sysOpts[$opt])) value="{!! $GLOBALS["SL"]->sysOpts[$opt] !!}"
            @endif >
    @endif
</label></div>