<!-- resources/views/vendor/survloop/elements/inc-sensitive-access-mfa-form.blade.php -->
<form method="post" name="mainPageForm" action="?sub=1">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
@if (trim($showLabel) != '') <h3 class="slBlueDark">{!! $showLabel !!}</h3> @endif
<input type="text" class="form-control form-control-lg slTab taC 
    @if ($GLOBALS['SL']->REQ->has('t2')) slBlk @else slGrey @endif " style="width: 190px;" 
    name="t2" id="t2ID" 
    @if ($GLOBALS['SL']->REQ->has('t2')) value="{{ $GLOBALS['SL']->REQ->get('t2') }}" 
    @else value="XXXX-XXXX-XXXX" @endif
    onFocus="if (this.value=='XXXX-XXXX-XXXX') { this.value=''; this.className='form-control form-control-lg slTab taC blk'; }"
    onBlur="if (this.value=='') { this.value='XXXX-XXXX-XXXX'; this.className='form-control form-control-lg slTab taC slGrey'; }"
    {!! $GLOBALS["SL"]->tabInd() !!}>
<input type="submit" value="{{ $btnText }}" class="btn btn{{ $btnSz }} btn-primary mT20 mB5" style="width: 190px;">
</form>