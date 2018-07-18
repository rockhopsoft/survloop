<!-- resources/views/vendor/survloop/inc-sensitive-access-mfa-form.blade.php -->
<form method="post" name="mainPageForm" action="?sub=1">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
@if ($showLabel) <h2 class="slBlueDark">Enter Key Code:</h2> @endif
<input type="text" class="form-control input-lg slTab taC 
    @if ($GLOBALS['SL']->REQ->has('t2')) slBlk @else slGrey @endif " style="width: 190px;" 
    name="t2" id="t2ID" 
    @if ($GLOBALS['SL']->REQ->has('t2')) value="{{ $GLOBALS['SL']->REQ->get('t2') }}" 
    @else value="XXXX-XXXX-XXXX" @endif
    onFocus="if (this.value=='XXXX-XXXX-XXXX') { this.value=''; this.className='form-control input-lg slTab taC blk'; }"
    onBlur="if (this.value=='') { this.value='XXXX-XXXX-XXXX'; this.className='form-control input-lg slTab taC slGrey'; }"
    {!! $GLOBALS["SL"]->tabInd() !!}>
<input type="submit" value="Access Full Details" class="btn btn-lg btn-primary mT20 mB5" style="width: 190px;">
</form>