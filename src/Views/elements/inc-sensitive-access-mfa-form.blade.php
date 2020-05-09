<!-- resources/views/vendor/survloop/elements/inc-sensitive-access-mfa-form.blade.php -->

<form name="mainPageForm" method="post" action="?t2sub=1">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="tokenIn" id="tokenInID" value="">
@if (trim($showLabel) != '')
    <h3 class="slBlueDark">{!! $showLabel !!}</h3>
@endif
<input name="t2" id="t2ID" type="text" 
    class="form-control form-control-lg slTab taC 
    @if ($GLOBALS['SL']->REQ->has('t2')) slBlk 
    @else slGrey 
    @endif "
    @if ($GLOBALS['SL']->REQ->has('t2')) value="{{ $GLOBALS['SL']->REQ->get('t2') }}" 
    @else value="xxxx-xxxx-xxxx" 
    @endif
    onFocus="if (this.value=='xxxx-xxxx-xxxx') { this.value=''; this.className='form-control form-control-lg slTab taC blk'; }"
    onBlur="if (this.value=='') { this.value='xxxx-xxxx-xxxx'; this.className='form-control form-control-lg slTab taC slGrey'; }"
    {!! $GLOBALS["SL"]->tabInd() !!}>
<input type="submit" value="{{ $btnText }}" 
    class="btn btn{{ $btnSz }} btn-primary btn-block mT20 mB5">
</form>

<script type="text/javascript">
function loadTok() {
    var tok = getGetParam("tokenIn");
    if (document.getElementById("tokenInID") && tok !== null) {
        document.getElementById("tokenInID").value=tok;
    } else {
        setTimeout("loadTok()", 1000);
    }
}
setTimeout("loadTok()", 1);
</script>
